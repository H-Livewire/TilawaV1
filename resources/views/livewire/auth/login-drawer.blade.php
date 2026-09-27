<div x-data="{ open: false, screen: 'login' }" @open-login.window="screen = 'login'; open = true" @keydown.escape.window="if (open) open = false" x-init="$watch('open', value => { if (!value) $wire.clearPassword() })">
    <div x-show="open" x-cloak x-transition.opacity.duration.300ms class="fixed inset-0 z-[60] bg-black/50" @click="open = false" aria-hidden="true"></div>
    <section id="login-drawer" role="dialog" aria-modal="true" :aria-labelledby="screen === 'login' ? 'login-form-title' : (screen === 'register' ? 'register-form-title' : 'reset-form-title')"
        x-show="open" x-cloak x-trap.inert.noscroll="open"
        x-transition:enter="transition-transform ease-out duration-500 motion-reduce:transition-none"
        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition-transform ease-in-out duration-300 motion-reduce:transition-none"
        x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 z-[70] w-full max-w-lg overflow-y-auto overscroll-contain bg-tilawa-surface p-6 shadow-2xl sm:p-8">
        <div class="mb-6 flex items-center justify-between">
            <x-ui.logo />
            <button type="button" @click="open = false" aria-label="Close login" class="flex h-10 w-10 items-center justify-center rounded-full text-tilawa-ink hover:bg-tilawa-line focus-visible:outline-2 focus-visible:outline-tilawa-teal-dark"><x-ui.icon name="close" class="h-5 w-5" /></button>
        </div>
        <form x-show="screen === 'login'" wire:submit="login" class="flex flex-col gap-6">
            <x-auth.login-fields :drawer="true" />
        </form>
        <div x-show="screen === 'reset'" x-cloak>
            <livewire:auth.forgot-password-drawer />
        </div>
        <div x-show="screen === 'register'" x-cloak>
            <livewire:auth.register-drawer />
        </div>
    </section>
</div>
