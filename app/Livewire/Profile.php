<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Tilawa — Profile & Settings'])]
class Profile extends Component
{
    public string $name = '';

    public string $email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $confirmingDeletion = false;

    public string $delete_password = '';

    /** Accounts created with Google have no known password — they confirm deletion by typing their email instead. */
    public string $delete_confirmation = '';

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    public function updateProfile(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update($validated);

        session()->flash('profile-updated');
    }

    public function updatePassword(): void
    {
        $user = Auth::user();

        $rules = ['password' => ['required', 'confirmed', Password::defaults()]];

        // Google-created accounts never had a password to confirm, so they set their first one directly.
        if ($user->has_password) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $validated = $this->validate($rules);

        $user->forceFill([
            'password' => $validated['password'],
            'has_password' => true,
        ])->save();

        $this->reset(['current_password', 'password', 'password_confirmation']);

        session()->flash('password-updated');
    }

    public function confirmDeletion(): void
    {
        $this->confirmingDeletion = true;
    }

    public function cancelDeletion(): void
    {
        $this->confirmingDeletion = false;
        $this->delete_password = '';
        $this->delete_confirmation = '';
        $this->resetErrorBag(['delete_password', 'delete_confirmation']);
    }

    public function deleteAccount(): void
    {
        $user = Auth::user();

        if ($user->has_password) {
            $this->validate([
                'delete_password' => ['required', 'current_password'],
            ]);
        } else {
            $this->validate([
                'delete_confirmation' => ['required', 'string', function (string $attribute, mixed $value, \Closure $fail) use ($user): void {
                    if (mb_strtolower(trim((string) $value)) !== mb_strtolower($user->email)) {
                        $fail('Type your account email address exactly to confirm.');
                    }
                }],
            ]);
        }

        Auth::logout();

        $user->delete();

        session()->invalidate();
        session()->regenerateToken();

        $this->redirectRoute('landing', navigate: true);
    }

    public function render()
    {
        // Guarded: after deleteAccount() calls Auth::logout(), render() may still
        // run once more before the redirect takes effect, with no user left to query.
        $user = Auth::user();

        return view('livewire.profile', [
            'bookmarkCount' => $user ? $user->bookmarks()->count() : 0,
            'hasPassword' => (bool) $user?->has_password,
        ]);
    }
}
