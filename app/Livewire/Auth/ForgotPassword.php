<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest', ['title' => 'Tilawa — Reset password'])]
class ForgotPassword extends Component
{
    public string $email = '';

    public string $status = '';

    public function sendResetLink(): void
    {
        $this->validate(['email' => ['required', 'email', 'max:255']]);
        $key = 'password-link:'.hash('sha256', request()->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', 'Too many requests. Please try again in a minute.');

            return;
        }

        RateLimiter::hit($key, 60);
        Password::sendResetLink(['email' => $this->email]);
        $this->status = 'If an account uses that email address, a password reset link will be sent. Please check your inbox.';
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
