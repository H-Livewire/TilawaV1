<div class="flex min-h-screen">
    <x-auth.panel tagline="Return to your daily Tilawa." />
    <div class="relative flex grow items-center justify-center px-6 py-12">
        <x-ui.theme-selector variant="surface" class="absolute right-6 top-6" />
        <form wire:submit="sendResetLink" class="flex w-full max-w-[400px] flex-col gap-6">
            <div><h1 class="mb-2 text-[30px] font-extrabold text-tilawa-ink">Reset password</h1>
                <p class="text-sm text-tilawa-sub">Enter your email address to receive a reset link.</p></div>
            @if ($status)<p role="status" class="text-sm text-tilawa-teal-dark">{{ $status }}</p>@endif
            <x-ui.text-field label="Email address" name="email" type="email" wire:model="email" autocomplete="email" autofocus />
            
            <x-ui.button type="submit" variant="primary" wire:loading.attr="disabled">Send reset link</x-ui.button>
            <a href="{{ route('login') }}" wire:navigate class="text-center text-sm font-semibold text-tilawa-teal-dark">Back to sign in</a>
        </form>
    </div>
</div>
