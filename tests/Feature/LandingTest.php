<?php

use App\Livewire\Landing;
use App\Models\User;
use Livewire\Livewire;

test('guests see one get started entry and can browse without signing in', function () {
    Livewire::test(Landing::class)
        ->assertOk()
        ->assertSee('Get started')
        ->assertSee('Browse all surahs')
        ->assertSee('Al-Fatihah')
        ->assertSee('Al-Mulk')
        ->assertDontSee('Sign in')
        ->assertDontSee('Sign up');
});

test('get started offers a login drawer and featured surahs open the reader', function () {
    Livewire::test(Landing::class)
        ->assertSeeHtml('aria-controls="login-drawer"')
        ->assertSeeHtml(route('home'))
        ->assertSeeHtml(route('surah.show', 1))
        ->assertSeeHtml(route('surah.show', 67));
});

test('signed-in readers can still view the public landing page', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Landing::class)
        ->assertOk()->assertSee('My account')->assertSee('Browse all surahs');
});
