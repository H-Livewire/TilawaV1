<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest', ['title' => 'Tilawa — Sign in'])]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = true;

    public function login(): void
    {
        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $key = 'login:'.hash('sha256', Str::lower(trim($this->email)).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', 'Too many sign-in attempts. Try again in '.RateLimiter::availableIn($key).' seconds.');

            return;
        }

        if (! Auth::attempt($credentials, $this->remember)) {
            RateLimiter::hit($key, 60);
            $this->addError('email', 'These credentials do not match our records.');

            return;
        }

        RateLimiter::clear($key);
        session()->regenerate();

        $this->redirectIntended(route('home'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
