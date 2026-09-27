@props(['drawer' => false])
            <div>
                <h2 id="register-form-title" tabindex="-1" class="mb-2 text-[30px] font-extrabold text-tilawa-ink">Create your account</h2>
                <p class="text-sm text-tilawa-sub">Start your daily reading habit today.</p>
            </div>

            <a href="{{ route('home') }}" wire:navigate class="text-center text-sm font-semibold text-tilawa-teal-dark dark:text-tilawa-teal-light">Continue reading as a guest</a>
            <x-auth.google-button />
            <div class="flex items-center gap-3 text-xs text-tilawa-sub"><span class="h-px grow bg-tilawa-line"></span>or create an account with email<span class="h-px grow bg-tilawa-line"></span></div>
            <div class="flex flex-col gap-4">
                <x-ui.text-field label="Full name" name="name" id="register-name" autocomplete="name" type="text" icon="user" wire:model="name"
                    placeholder="Ahmad Yahya" />
                <x-ui.text-field label="Email address" name="email" id="register-email" autocomplete="email" type="email" icon="mail" wire:model="email"
                    placeholder="you@example.com" />
                <x-ui.text-field label="Password" name="password" id="register-password" autocomplete="new-password" type="password" icon="lock" wire:model="password"
                    placeholder="Create a password" />
                <x-ui.text-field label="Confirm password" name="password_confirmation" id="register-password_confirmation" autocomplete="new-password" type="password" icon="lock"
                    wire:model="password_confirmation" placeholder="Repeat your password" />
            </div>

            <label class="flex items-center gap-2 text-sm text-tilawa-sub"><input type="checkbox" wire:model="remember" class="rounded border-tilawa-line text-tilawa-teal"> Keep me signed in</label>
            <x-ui.button type="submit" variant="primary" class="mt-1 w-full">
                <span wire:loading.remove wire:target="register">Create Account</span>
                <span wire:loading wire:target="register">Creating account…</span>
                <x-ui.icon name="arrow-right" class="h-[18px] w-[18px]" />
            </x-ui.button>

            <p class="text-center text-sm text-tilawa-sub">
                Already have an account?
                @if ($drawer)
                    <button type="button" @click="screen = 'login'; $wire.clearPasswords(); $nextTick(() => document.getElementById('login-form-title').focus())" class="font-bold text-tilawa-teal-dark dark:text-tilawa-teal-light">Sign in</button>
                @else
                    <a href="{{ route('login') }}" wire:navigate class="font-bold text-tilawa-purple">Sign in</a>
                @endif
            </p>
