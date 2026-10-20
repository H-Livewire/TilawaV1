<?php

use App\Models\User;

test('browse pages render the installed-app tab bar', function () {
    $this->get(route('library'))
        ->assertOk()
        ->assertSee('class="app-tabbar"', false)
        ->assertSee('Ruqyah')
        ->assertSee('Bookmarks')
        ->assertSee('Account');
});

test('guests are sent to sign in from the bookmarks and account tabs', function () {
    $html = $this->get(route('library'))->getContent();

    expect($html)->toContain('href="'.route('login').'"')
        ->and($html)->not->toContain('href="'.route('profile').'"');
});

test('signed-in users get direct bookmark and account tabs', function () {
    $html = $this->actingAs(User::factory()->create())->get(route('library'))->getContent();

    expect($html)->toContain('href="'.route('bookmarks').'"')
        ->and($html)->toContain('href="'.route('profile').'"');
});

test('the landing page has no tab bar', function () {
    $this->get(route('landing'))->assertDontSee('class="app-tabbar"', false);
});
