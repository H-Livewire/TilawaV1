<form wire:submit="sendResetLink" class="flex flex-col gap-6">
    <div>
        <h2 id="reset-form-title" tabindex="-1" class="mb-2 text-[30px] font-extrabold text-tilawa-ink">Reset password</h2>
        <p class="text-sm leading-6 text-tilawa-sub">Enter your email address to receive a password reset link.</p>
    </div>
    @if ($status)<p role="status" class="text-sm text-tilawa-teal-dark dark:text-tilawa-teal-light">{{ $status }}</p>@endif
    <x-ui.text-field label="Email address" name="email" id="reset-email" type="email" icon="mail" wire:model="email" autocomplete="email" placeholder="you@example.com" />
    <x-ui.button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="sendResetLink">
        <span wire:loading.remove wire:target="sendResetLink">Send reset link</span>
        <span wire:loading wire:target="sendResetLink">Sending…</span>
    </x-ui.button>
    <button type="button" @click="screen = 'login'; $nextTick(() => document.getElementById('login-form-title').focus())" class="text-center text-sm font-semibold text-tilawa-teal-dark dark:text-tilawa-teal-light">Back to sign in</button>
</form>
