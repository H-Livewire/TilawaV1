<?php

use App\Livewire\Profile;
use App\Models\Bookmark;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('guests are redirected away from the profile page', function () {
    $response = $this->get(route('profile'));

    $response->assertRedirect(route('login'));
});

test('the profile form is pre-filled with the current user', function () {
    $user = User::factory()->create(['name' => 'Amani Juma', 'email' => 'amani@example.com']);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->assertSet('name', 'Amani Juma')
        ->assertSet('email', 'amani@example.com');
});

test('a user can update their name and email', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('name', 'New Name')
        ->set('email', 'new-email@example.com')
        ->call('updateProfile')
        ->assertHasNoErrors();

    expect($user->fresh())
        ->name->toBe('New Name')
        ->email->toBe('new-email@example.com');
});

test('a user cannot update their email to one already taken', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('email', 'taken@example.com')
        ->call('updateProfile')
        ->assertHasErrors('email');
});

test('updating the profile with the same email the user already has is allowed', function () {
    $user = User::factory()->create(['email' => 'me@example.com']);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('name', 'Updated Name')
        ->set('email', 'me@example.com')
        ->call('updateProfile')
        ->assertHasNoErrors();
});

test('a user can change their password with the correct current password', function () {
    $user = User::factory()->create(['password' => 'old-password-123']);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('current_password', 'old-password-123')
        ->set('password', 'new-password-456')
        ->set('password_confirmation', 'new-password-456')
        ->call('updatePassword')
        ->assertHasNoErrors();

    expect(Hash::check('new-password-456', $user->fresh()->password))->toBeTrue();
});

test('changing the password fails with the wrong current password', function () {
    $user = User::factory()->create(['password' => 'old-password-123']);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('current_password', 'totally-wrong')
        ->set('password', 'new-password-456')
        ->set('password_confirmation', 'new-password-456')
        ->call('updatePassword')
        ->assertHasErrors('current_password');

    expect(Hash::check('old-password-123', $user->fresh()->password))->toBeTrue();
});

test('changing the password fails when the confirmation does not match', function () {
    $user = User::factory()->create(['password' => 'old-password-123']);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('current_password', 'old-password-123')
        ->set('password', 'new-password-456')
        ->set('password_confirmation', 'does-not-match')
        ->call('updatePassword')
        ->assertHasErrors('password');
});

test('deleting the account requires the correct password', function () {
    $user = User::factory()->create(['password' => 'my-password-123']);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('confirmingDeletion', true)
        ->set('delete_password', 'wrong-password')
        ->call('deleteAccount')
        ->assertHasErrors('delete_password');

    expect(User::find($user->id))->not->toBeNull();
});

test('a user can permanently delete their account, cascading their bookmarks', function () {
    $user = User::factory()->create(['password' => 'my-password-123']);
    $user->bookmarks()->create([
        'surah_number' => 2,
        'surah_name' => 'Al-Baqarah',
        'ayah_number' => 1,
        'ayah_text' => 'الم',
    ]);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('confirmingDeletion', true)
        ->set('delete_password', 'my-password-123')
        ->call('deleteAccount')
        ->assertRedirect(route('landing'));

    expect(User::find($user->id))->toBeNull();
    expect(Bookmark::where('user_id', $user->id)->count())->toBe(0);
});

test('a Google-created account can set its first password without a current one', function () {
    $user = User::factory()->create(['google_id' => 'google-1']);
    $user->forceFill(['has_password' => false])->save();

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->assertSee('Set a password')
        ->assertDontSee('Current password')
        ->set('password', 'new-password-456')
        ->set('password_confirmation', 'new-password-456')
        ->call('updatePassword')
        ->assertHasNoErrors();

    $user->refresh();
    expect($user->has_password)->toBeTrue();
    expect(Hash::check('new-password-456', $user->password))->toBeTrue();
});

test('once a password is set, changing it again requires the current one', function () {
    $user = User::factory()->create(['password' => 'my-password-123']);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('password', 'new-password-456')
        ->set('password_confirmation', 'new-password-456')
        ->call('updatePassword')
        ->assertHasErrors('current_password');
});

test('a Google-created account confirms deletion by typing its email', function () {
    $user = User::factory()->create(['google_id' => 'google-2', 'email' => 'reader@gmail.com']);
    $user->forceFill(['has_password' => false])->save();

    $component = Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('confirmingDeletion', true)
        ->assertSee('Type your email address to confirm')
        ->set('delete_confirmation', 'someone-else@gmail.com')
        ->call('deleteAccount')
        ->assertHasErrors('delete_confirmation');

    expect(User::find($user->id))->not->toBeNull();

    $component->set('delete_confirmation', ' Reader@Gmail.com ')
        ->call('deleteAccount')
        ->assertRedirect(route('landing'));

    expect(User::find($user->id))->toBeNull();
});
