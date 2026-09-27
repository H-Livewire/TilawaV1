<div class="flex min-h-screen">
    <x-auth.panel tagline="Your reading. Your own pace." />

    <div class="relative flex grow items-center justify-center px-6 py-12">
        <x-ui.theme-selector variant="surface" class="absolute right-6 top-6" />
        <form wire:submit="login" class="flex w-full max-w-[400px] flex-col gap-6">
            <x-auth.login-fields />
        </form>
    </div>
</div>
