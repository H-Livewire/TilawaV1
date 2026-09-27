<div class="flex min-h-screen">
    <x-auth.panel tagline="Return to your daily Tilawa." />
    <div class="relative flex grow items-center justify-center px-6 py-12">
        <x-ui.theme-selector variant="surface" class="absolute right-6 top-6" />
        <form wire:submit="resetPassword" class="flex w-full max-w-[400px] flex-col gap-6">
            <div><h1 class="mb-2 text-[30px] font-extrabold text-tilawa-ink">Choose a new password</h1>
                <p class="text-sm text-tilawa-sub">Enter and confirm your new password.</p></div>
            
            <x-ui.text-field label="Email address" name="email" type="email" wire:model="email" autocomplete="email" autofocus />
            <x-ui.text-field label="New password" name="password" type="password" wire:model="password" autocomplete="new-password" />
                <x-ui.text-field label="Confirm new password" name="password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" />
            <x-ui.button type="submit" variant="primary" wire:loading.attr="disabled">Reset password</x-ui.button>
            <a href="{{ route('login') }}" wire:navigate class="text-center text-sm font-semibold text-tilawa-teal-dark">Back to sign in</a>
        </form>
    </div>
</div>
