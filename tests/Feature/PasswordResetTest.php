<?php

use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\ResetPassword;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword as ResetNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;

test('password recovery screens are accessible to guests', function () {
    $this->get(route('password.request'))->assertOk();
    $this->get(route('password.reset', ['token' => 'example', 'email' => 'reader@example.com']))->assertOk();
    $this->get(route('login'))->assertSee(route('password.request'));
});

test('a reader receives a reset link and can use it only once', function () {
    Notification::fake();
    $user = User::factory()->create();
    $oldRememberToken = $user->remember_token;
    Livewire::test(ForgotPassword::class)->set('email', $user->email)->call('sendResetLink')->assertHasNoErrors();

    $token = null;
    Notification::assertSentTo($user, ResetNotification::class, function ($notification) use (&$token) {
        $token = $notification->token;

        return true;
    });

    Livewire::test(ResetPassword::class, ['token' => $token])
        ->set('email', $user->email)->set('password', 'a-new-password-123')
        ->set('password_confirmation', 'a-new-password-123')->call('resetPassword')
        ->assertRedirect(route('login'));
    expect(Hash::check('a-new-password-123', $user->fresh()->password))->toBeTrue();
    expect($user->fresh()->remember_token)->not->toBe($oldRememberToken);

    Livewire::test(ResetPassword::class, ['token' => $token])
        ->set('email', $user->email)->set('password', 'another-password-123')
        ->set('password_confirmation', 'another-password-123')->call('resetPassword')
        ->assertHasErrors('email');
    expect(Hash::check('a-new-password-123', $user->fresh()->password))->toBeTrue();
});

test('expired tokens cannot change a password', function () {
    $user = User::factory()->create(['password' => 'original-password']);
    $token = Password::createToken($user);
    $this->travel(61)->minutes();
    Livewire::test(ResetPassword::class, ['token' => $token])->set('email', $user->email)
        ->set('password', 'a-new-password-123')->set('password_confirmation', 'a-new-password-123')
        ->call('resetPassword')->assertHasErrors('email');
    expect(Hash::check('original-password', $user->fresh()->password))->toBeTrue();
});

test('reset requests do not reveal whether an email is registered', function () {
    Notification::fake();
    $user = User::factory()->create();
    $existing = Livewire::test(ForgotPassword::class)->set('email', $user->email)->call('sendResetLink');
    $unknown = Livewire::test(ForgotPassword::class)->set('email', 'unknown@example.com')->call('sendResetLink');
    expect($unknown->get('status'))->toBe($existing->get('status'));
    Notification::assertCount(1);
});

test('reset link requests are limited per IP address', function () {
    Notification::fake();
    $component = Livewire::test(ForgotPassword::class)->set('email', 'unknown@example.com');
    for ($i = 0; $i < 5; $i++) {
        $component->call('sendResetLink')->assertHasNoErrors();
    }
    $component->call('sendResetLink')->assertHasErrors('email');
    Notification::assertNothingSent();
});
