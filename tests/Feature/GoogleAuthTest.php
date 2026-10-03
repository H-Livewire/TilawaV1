<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;

beforeEach(function () {
    config(['services.google.client_id' => 'test-client', 'services.google.client_secret' => 'test-secret', 'services.google.redirect' => 'http://localhost/auth/google/callback']);
});

function googleReader(string $id = 'google-reader-123', string $email = 'reader@gmail.com', bool $verified = true): GoogleUser
{
    return (new GoogleUser)->setRaw(['email_verified' => $verified])->map(['id' => $id, 'name' => 'Quran Reader', 'email' => $email]);
}

test('Google authorization uses session state and remembers the selected preference', function () {
    $response = $this->get(route('auth.google.redirect', ['remember' => 0]));
    $response->assertRedirect()->assertSessionHas('state')->assertSessionHas('google.remember', false);
    $location = $response->headers->get('Location');
    expect($location)->toStartWith('https://accounts.google.com/');
    parse_str(parse_url($location, PHP_URL_QUERY), $query);
    expect($query['state'])->toBe(session('state'));
    expect($query['redirect_uri'])->toBe('http://localhost/auth/google/callback');
});

test('a verified Google reader gets an account and a remembered session', function () {
    Socialite::fake('google', googleReader());
    $response = $this->withSession(['google.remember' => true])->get(route('auth.google.callback', ['code' => 'test-code']));
    $response->assertRedirect(route('home'))->assertCookie(Auth::guard()->getRecallerName());
    $user = User::where('google_id', 'google-reader-123')->firstOrFail();
    $this->assertAuthenticatedAs($user);
    expect($user->email_verified_at)->not->toBeNull();
    expect($user->toArray())->not->toHaveKey('google_id');
});

test('a returning Google reader signs into the same account', function () {
    $user = User::factory()->create(['google_id' => 'google-reader-123']);
    Socialite::fake('google', googleReader());
    $this->withSession(['google.remember' => false, 'url.intended' => route('surah.show', 18)])
        ->get(route('auth.google.callback'))->assertRedirect(route('surah.show', 18))->assertCookieMissing(Auth::guard()->getRecallerName());
    $this->assertAuthenticatedAs($user);
    expect(User::count())->toBe(1);
});

test('Google does not take over an existing password account by matching email', function () {
    $existing = User::factory()->create(['email' => 'reader@gmail.com']);
    Socialite::fake('google', googleReader());
    $this->get(route('auth.google.callback'))->assertRedirect(route('login'))->assertSessionHasErrors('google');
    $this->assertGuest();
    expect($existing->fresh()->google_id)->toBeNull();
    expect(User::count())->toBe(1);
});

test('Google accounts with unverified email are rejected', function () {
    Socialite::fake('google', googleReader(verified: false));
    $this->get(route('auth.google.callback'))->assertRedirect(route('login'))->assertSessionHasErrors('google');
    $this->assertGuest();
    expect(User::count())->toBe(0);
});

test('a callback with invalid state cannot log a reader in', function () {
    $this->withSession(['state' => 'expected-state'])
        ->get(route('auth.google.callback', ['state' => 'wrong-state', 'code' => 'test-code']))
        ->assertRedirect(route('login'))->assertSessionHasErrors('google');
    $this->assertGuest();
    expect(User::count())->toBe(0);
});

test('cancelling Google sign-in returns to the reading choices', function () {
    $this->withSession(['state' => 'old-state', 'google.remember' => true])
        ->get(route('auth.google.callback', ['error' => 'access_denied']))
        ->assertRedirect(route('login'))->assertSessionHas('status')->assertSessionMissing('state');
    $this->assertGuest();
});

test('Google sign-in stays unavailable until credentials are configured', function () {
    config(['services.google.client_id' => null, 'services.google.client_secret' => null]);
    $this->get(route('auth.google.redirect'))->assertRedirect(route('login'))->assertSessionHasErrors('google');
    $this->get(route('login'))->assertOk()->assertSee('Continue as a guest')->assertSee('Google sign-in will be available soon');
});

test('a new Google account is marked as having no password of its own', function () {
    Socialite::fake('google', googleReader('google-no-password', 'nopass@gmail.com'));
    $this->withSession(['google.remember' => false])->get(route('auth.google.callback', ['code' => 'test-code']));

    expect(User::where('google_id', 'google-no-password')->first()->has_password)->toBeFalse();
});
