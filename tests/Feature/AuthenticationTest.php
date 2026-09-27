<?php

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

test('repeated failed sign-ins are blocked until the cooldown expires', function () {
    User::factory()->create(['email' => 'reader@example.com', 'password' => 'correct-password']);
    $login = Livewire::test(Login::class)->set('email', 'reader@example.com')->set('password', 'wrong-password');

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $login->call('login')->assertHasErrors('email');
    }

    $login->set('password', 'correct-password')->call('login')->assertSee('Too many sign-in attempts');
    expect(Auth::check())->toBeFalse();

    $this->travel(61)->seconds();
    $login->call('login')->assertRedirect(route('home'));
    expect(Auth::check())->toBeTrue();
});

test('a successful sign-in clears earlier failed attempts', function () {
    User::factory()->create(['email' => 'reader@example.com', 'password' => 'correct-password']);
    $login = Livewire::test(Login::class)->set('email', 'reader@example.com')->set('password', 'wrong-password');
    $login->call('login');
    $login->set('password', 'correct-password')->call('login')->assertRedirect(route('home'));
    Auth::logout();

    $login = Livewire::test(Login::class)->set('email', 'reader@example.com')->set('password', 'wrong-password');
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $login->call('login')->assertDontSee('Too many sign-in attempts');
    }
});
