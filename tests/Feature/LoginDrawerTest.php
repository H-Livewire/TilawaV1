<?php

use App\Livewire\Auth\ForgotPasswordDrawer;
use App\Livewire\Auth\LoginDrawer;
use App\Livewire\Auth\RegisterDrawer;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('the guest homepage includes a login dialog with every sign-in option', function () {
    $this->get(route('landing'))->assertOk()->assertSeeHtml('id="login-drawer"')
        ->assertSee('Continue with Google')->assertSee('Keep me signed in')
        ->assertSee('Forgot password?')->assertSee('Send reset link')->assertSee('Create your account');
});

test('the drawer signs in and redirects to the intended reading destination', function () {
    $user = User::factory()->create(['password' => 'correct-password']);
    session(['url.intended' => route('surah.show', 18)]);
    Livewire::test(LoginDrawer::class)->set('email', $user->email)
        ->set('password', 'correct-password')->call('login')->assertRedirect(route('surah.show', 18));
    $this->assertAuthenticatedAs($user);
});

test('failed drawer sign-in stays in place and closing clears the password', function () {
    Livewire::test(LoginDrawer::class)->set('email', 'missing@example.com')
        ->set('password', 'wrong-password')->call('login')->assertHasErrors('email')
        ->assertNoRedirect()->call('clearPassword')->assertSet('password', '')->assertHasNoErrors();
    $this->assertGuest();
});

test('signed-in visitors do not receive a login drawer', function () {
    $this->actingAs(User::factory()->create())->get(route('landing'))
        ->assertOk()->assertDontSeeHtml('id="login-drawer"');
});

test('the reset drawer sends a reset notification without navigating away', function () {
    Notification::fake();
    $user = User::factory()->create();
    Livewire::test(ForgotPasswordDrawer::class)
        ->set('email', $user->email)->call('sendResetLink')->assertHasNoErrors()
        ->assertNoRedirect()->assertSee('Please check your inbox.');
    Notification::assertSentTo($user, ResetPassword::class);
});

test('the registration drawer creates an account and signs the reader in', function () {
    Livewire::test(RegisterDrawer::class)
        ->set('name', 'New Reader')->set('email', 'new-reader@example.com')
        ->set('password', 'safe-password-123')->set('password_confirmation', 'safe-password-123')
        ->call('register')->assertHasNoErrors()->assertRedirect(route('home'));
    $this->assertAuthenticatedAs(User::where('email', 'new-reader@example.com')->firstOrFail());
});

test('registration drawer validates confirmation and clears passwords when dismissed', function () {
    Livewire::test(RegisterDrawer::class)
        ->set('name', 'New Reader')->set('email', 'new-reader@example.com')
        ->set('password', 'safe-password-123')->set('password_confirmation', 'different-password')
        ->call('register')->assertHasErrors('password')->assertNoRedirect()
        ->call('clearPasswords')->assertSet('password', '')->assertSet('password_confirmation', '')
        ->assertHasNoErrors();
    $this->assertGuest();
});
