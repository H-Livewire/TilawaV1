<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset as PasswordResetEvent;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.guest', ['title' => 'Tilawa — Choose a new password'])]
class ResetPassword extends Component
{
    #[Locked]
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $email = request()->query('email', '');
        $this->email = is_string($email) ? $email : '';
    }

    public function resetPassword(): void
    {
        $validated = $this->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);
        $key = 'password-reset:'.hash('sha256', request()->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', 'Too many requests. Please try again in a minute.');

            return;
        }
        RateLimiter::hit($key, 60);

        $status = Password::reset([
            ...$validated,
            'password_confirmation' => $this->password_confirmation,
            'token' => $this->token,
        ], function (User $user, string $password): void {
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            event(new PasswordResetEvent($user));
        });

        $this->reset(['password', 'password_confirmation']);

        if ($status !== Password::PasswordReset) {
            $this->addError('email', 'This reset link is invalid or has expired. Please request a new link.');

            return;
        }

        session()->flash('status', 'Your password has been reset. You can now sign in.');
        $this->redirectRoute('login', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.reset-password');
    }
}
