<?php

use App\Models\PushSubscription;
use App\Models\User;
use App\Services\AyahOfTheDay;
use App\Services\PushSender;
use App\Support\DailyAyahMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

function pushPayload(array $overrides = []): array
{
    return array_merge([
        'enabled' => true, 'time' => '07:00', 'arabic' => true, 'translation' => true, 'reference' => true, 'simple' => false,
        'timezone' => 'Africa/Dar_es_Salaam',
        'subscription' => ['endpoint' => 'https://push.example.com/abc', 'keys' => ['p256dh' => 'pk', 'auth' => 'au']],
    ], $overrides);
}

function sampleAyah(): array
{
    return [
        'date' => '2026-10-20', 'surah' => 2, 'ayah' => 255, 'surahName' => 'Al-Baqarah', 'language' => 'en',
        'arabic' => 'ARABIC', 'translation' => 'TRANSLATION', 'reference' => 'Al-Baqarah 2:255', 'url' => '/surah/2?ayah=255#ayah-255',
    ];
}

afterEach(fn () => Carbon::setTestNow());

test('guests cannot save a server reminder', function () {
    $this->putJson(route('daily-ayah.reminder.update'), pushPayload())->assertUnauthorized();
});

test('a signed-in user saves reminder choices and registers the device', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->putJson(route('daily-ayah.reminder.update'), pushPayload(['time' => '06:30', 'translation' => false]))
        ->assertOk();

    $user->refresh();
    expect($user->daily_ayah_enabled)->toBeTrue()
        ->and($user->daily_ayah_time)->toBe('06:30')
        ->and($user->daily_ayah_translation)->toBeFalse()
        ->and($user->timezone)->toBe('Africa/Dar_es_Salaam');
    expect($user->pushSubscriptions)->toHaveCount(1);
    expect($user->pushSubscriptions->first()->endpoint)->toBe('https://push.example.com/abc');
});

test('the same device is stored once and moves to the latest account', function () {
    [$first, $second] = User::factory()->count(2)->create();

    $this->actingAs($first)->putJson(route('daily-ayah.reminder.update'), pushPayload())->assertOk();
    $this->actingAs($second)->putJson(route('daily-ayah.reminder.update'), pushPayload())->assertOk();

    expect(PushSubscription::count())->toBe(1)
        ->and(PushSubscription::first()->user_id)->toBe($second->id);
});

test('reminder input is validated', function () {
    $user = User::factory()->create();

    foreach ([['time' => '7am'], ['timezone' => 'Mars/Base'], ['subscription' => ['endpoint' => 'http://insecure.example', 'keys' => ['p256dh' => 'a', 'auth' => 'b']]]] as $bad) {
        $this->actingAs($user)->putJson(route('daily-ayah.reminder.update'), pushPayload($bad))->assertStatus(422);
    }
});

test('a device can be removed', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->putJson(route('daily-ayah.reminder.update'), pushPayload())->assertOk();

    $this->actingAs($user)->deleteJson(route('daily-ayah.reminder.destroy'), ['endpoint' => 'https://push.example.com/abc'])->assertOk();

    expect($user->pushSubscriptions()->count())->toBe(0);
});

test('message content follows the user choices', function () {
    $all = ['arabic' => true, 'translation' => true, 'reference' => true, 'simple' => false];

    $full = DailyAyahMessage::compose(sampleAyah(), $all);
    expect($full['title'])->toBe('Ayah of the Day · Al-Baqarah 2:255')
        ->and($full['body'])->toBe("TRANSLATION\nARABIC")
        ->and($full['url'])->toBe('/surah/2?ayah=255#ayah-255');

    $arabicOnly = DailyAyahMessage::compose(sampleAyah(), [...$all, 'translation' => false, 'reference' => false]);
    expect($arabicOnly['body'])->toBe('ARABIC')->and($arabicOnly['title'])->toBe('Ayah of the Day');

    expect(DailyAyahMessage::compose(sampleAyah(), [...$all, 'arabic' => false, 'reference' => false])['body'])->toBe('TRANSLATION')
        ->and(DailyAyahMessage::compose(sampleAyah(), [...$all, 'simple' => true])['body'])->not->toContain('ARABIC')
        ->and(DailyAyahMessage::compose(null, $all)['url'])->toBe('/daily-ayah');
});

function enabledUser(array $attributes = []): User
{
    $user = User::factory()->create(array_merge(['timezone' => 'Africa/Dar_es_Salaam'], $attributes));
    $user->forceFill(['daily_ayah_enabled' => true, 'daily_ayah_time' => '07:00'])->save();
    $user->pushSubscriptions()->create(['endpoint_hash' => PushSubscription::hashFor('https://push.example.com/'.$user->id), 'endpoint' => 'https://push.example.com/'.$user->id, 'public_key' => 'pk', 'auth_token' => 'au']);

    return $user;
}

function fakeSender(array $gone = []): ArrayObject
{
    $sent = new ArrayObject;
    $sender = Mockery::mock(PushSender::class);
    $sender->shouldReceive('isConfigured')->andReturn(true);
    $sender->shouldReceive('send')->andReturnUsing(function ($subscriptions, $payload) use ($sent, $gone) {
        $sent->append(['count' => $subscriptions->count(), 'payload' => $payload]);

        return $gone;
    });
    app()->instance(PushSender::class, $sender);
    app()->instance(AyahOfTheDay::class, new class extends AyahOfTheDay
    {
        public function __construct() {}

        public function forDate($date = null, string $language = 'en'): array
        {
            return sampleAyah();
        }
    });

    return $sent;
}

test('the scheduler command sends at the user\'s local time, once a day', function () {
    $sent = fakeSender();
    $user = enabledUser();

    Carbon::setTestNow(Carbon::parse('2026-10-20 03:59:00', 'UTC')); // 06:59 in Dar es Salaam
    $this->artisan('ayah:send-daily')->assertSuccessful();
    expect($sent)->toHaveCount(0);

    Carbon::setTestNow(Carbon::parse('2026-10-20 04:00:00', 'UTC')); // 07:00
    $this->artisan('ayah:send-daily')->assertSuccessful();
    expect($sent)->toHaveCount(1)
        ->and($sent[0]['payload']['url'])->toBe('/surah/2?ayah=255#ayah-255')
        ->and($sent[0]['payload']['body'])->toContain('TRANSLATION')
        ->and($sent[0]['payload']['body'])->not->toContain('ARABIC');

    Carbon::setTestNow(Carbon::parse('2026-10-20 04:05:00', 'UTC'));
    $this->artisan('ayah:send-daily')->assertSuccessful();
    expect($sent)->toHaveCount(1);

    Carbon::setTestNow(Carbon::parse('2026-10-21 04:00:00', 'UTC'));
    $this->artisan('ayah:send-daily')->assertSuccessful();
    expect($sent)->toHaveCount(2);
});

test('the command skips disabled users and users without a device, and stops catching up after two hours', function () {
    $sent = fakeSender();
    $disabled = enabledUser();
    $disabled->forceFill(['daily_ayah_enabled' => false])->save();
    $noDevice = enabledUser();
    $noDevice->pushSubscriptions()->delete();
    $late = enabledUser();

    Carbon::setTestNow(Carbon::parse('2026-10-20 06:30:00', 'UTC')); // 09:30 local, 2.5 h late
    $this->artisan('ayah:send-daily')->assertSuccessful();

    expect($sent)->toHaveCount(0);
});

test('expired devices reported by the push service are deleted', function () {
    $user = enabledUser();
    fakeSender(['https://push.example.com/'.$user->id]);

    Carbon::setTestNow(Carbon::parse('2026-10-20 04:00:00', 'UTC'));
    $this->artisan('ayah:send-daily')->assertSuccessful();

    expect($user->pushSubscriptions()->count())->toBe(0);
});

test('the command does nothing when VAPID keys are missing', function () {
    $sender = Mockery::mock(PushSender::class);
    $sender->shouldReceive('isConfigured')->andReturn(false);
    $sender->shouldNotReceive('send');
    app()->instance(PushSender::class, $sender);

    $this->artisan('ayah:send-daily')->assertSuccessful();
});

test('signed-in users see the reminder card on their account page, and guests do not', function () {
    Http::fake(fn () => Http::response('down', 500));
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('profile'))->assertOk()->assertSee('Ayah of the Day reminder')->assertSee('Allow');
    $this->actingAs($user)->get(route('home'))->assertOk();
});

test('the reminder card has no time or content options, only Allow', function () {
    Http::fake(fn () => Http::response('down', 500));

    $this->get(route('daily-ayah'))->assertOk()
        ->assertDontSee('Arabic verse')
        ->assertDontSee('Surah & ayah reference')
        ->assertDontSee('Send a test notification');
});
