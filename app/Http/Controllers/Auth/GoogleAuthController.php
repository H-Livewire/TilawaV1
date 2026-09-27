<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        if (! $this->configured()) {
            return $this->unavailable();
        }

        $request->session()->put('google.remember', $request->boolean('remember', true));

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        if (! $this->configured()) {
            return $this->unavailable();
        }

        if ($request->has('error')) {
            $request->session()->forget(['state', 'google.remember']);

            return redirect()->route('login')->with('status', 'Google sign-in was cancelled. You can try again or continue reading as a guest.');
        }

        $remember = (bool) $request->session()->pull('google.remember', false);

        try {
            // Socialite validates and consumes the session state before exchanging the code.
            $google = Socialite::driver('google')->user();
            $id = $google->getId();
            $email = $google->getEmail();

            if (! is_string($id) || $id === '' || strlen($id) > 255
                || ! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255
                || ($google->user['email_verified'] ?? false) !== true) {
                return redirect()->route('login')->withErrors(['google' => 'Google could not verify your email address. Please use another sign-in method.']);
            }

            $user = DB::transaction(function () use ($google, $id, $email): ?User {
                $user = User::where('google_id', $id)->first();
                if ($user) {
                    return $user;
                }

                // An email match alone must never attach a new identity to an existing account.
                if (User::whereRaw('LOWER(email) = ?', [Str::lower($email)])->exists()) {
                    return null;
                }

                $user = new User;
                $user->forceFill([
                    'google_id' => $id,
                    'name' => Str::limit($google->getName() ?: 'Reader', 255, ''),
                    'email' => $email,
                    'email_verified_at' => now(),
                    'password' => Str::random(64),
                ])->save();

                return $user;
            });

            if (! $user) {
                return redirect()->route('login')->withErrors(['google' => 'An account already uses this email. Please sign in with its password, or use Forgot password.']);
            }

            Auth::login($user, $remember);
            $request->session()->regenerate();

            return redirect()->intended(route('home'));
        } catch (Throwable $exception) {
            // OAuth exceptions can contain tokens; log only the exception type.
            Log::warning('Google sign-in failed.', ['exception_type' => $exception::class]);
            $request->session()->forget('state');

            return redirect()->route('login')->withErrors(['google' => 'Google sign-in could not be completed. Please try again or continue as a guest.']);
        }
    }

    protected function configured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'));
    }

    protected function unavailable(): RedirectResponse
    {
        return redirect()->route('login')->withErrors(['google' => 'Google sign-in is not available yet. You can use email or continue reading as a guest.']);
    }
}
