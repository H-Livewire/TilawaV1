@props(['drawer' => false])
            <div>
                <h2 id="login-form-title" tabindex="-1" class="mb-2 text-[30px] font-extrabold text-tilawa-ink">Get started with Tilawa</h2>
                <p class="text-sm text-tilawa-sub">Save your bookmarks and keep your place across devices.</p>
            </div>

            @if (session('status'))<p role="status" class="text-sm text-tilawa-teal-dark">{{ session('status') }}</p>@endif

            <a href="{{ route('home') }}" wire:navigate class="flex items-center justify-center gap-2 rounded-xl bg-tilawa-teal-light/25 px-5 py-3.5 text-sm font-bold text-tilawa-teal-dark dark:text-tilawa-teal-light">Continue as a guest <x-ui.icon name="arrow-right" class="h-4 w-4" /></a>
            <x-auth.google-button />
            <div class="flex items-center gap-3 text-xs text-tilawa-sub"><span class="h-px grow bg-tilawa-line"></span>or use your email<span class="h-px grow bg-tilawa-line"></span></div>
            <div class="flex flex-col gap-4">
                <x-ui.text-field label="Email address" name="email" type="email" icon="mail" wire:model="email"
                    placeholder="you@example.com" autocomplete="email" />
                <x-ui.text-field label="Password" name="password" type="password" icon="lock" wire:model="password"
                    placeholder="Enter your password" autocomplete="current-password" />
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2 text-tilawa-sub">
                    <input type="checkbox" wire:model="remember"
                        class="rounded border-tilawa-line text-tilawa-teal focus:ring-tilawa-teal">
                    Keep me signed in
                </label>
                @if ($drawer)
                    <button type="button" @click="screen = 'reset'; $wire.clearPassword(); $nextTick(() => document.getElementById('reset-form-title').focus())" class="font-semibold text-tilawa-teal-dark dark:text-tilawa-teal-light">Forgot password?</button>
                @else
                    <a href="{{ route('password.request') }}" wire:navigate class="font-semibold text-tilawa-teal-dark dark:text-tilawa-teal-light">Forgot password?</a>
                @endif
            </div>

            <x-ui.button type="submit" variant="primary" class="w-full">
                <span wire:loading.remove wire:target="login">Sign In</span>
                <span wire:loading wire:target="login">Signing in…</span>
                <x-ui.icon name="arrow-right" class="h-[18px] w-[18px]" />
            </x-ui.button>

            <p class="text-center text-sm text-tilawa-sub">
                Don't have an account?
                @if ($drawer)
                    <button type="button" @click="screen = 'register'; $wire.clearPassword(); $nextTick(() => document.getElementById('register-form-title').focus())" class="font-bold text-tilawa-teal-dark dark:text-tilawa-teal-light">Create one</button>
                @else
                    <a href="{{ route('register') }}" wire:navigate class="font-bold text-tilawa-purple">Create one</a>
                @endif
            </p>
