<div class="flex min-h-screen">
    <x-auth.panel tagline="Make your reading feel like home." />

    <div class="relative flex grow items-center justify-center px-6 py-12">
        <x-ui.theme-selector variant="surface" class="absolute right-6 top-6" />
        <form wire:submit="register" class="flex w-full max-w-[400px] flex-col gap-5">
            <x-auth.register-fields />
        </form>
    </div>
</div>
