<form wire:submit="register" class="flex flex-col gap-5" x-init="$watch('open', value => { if (!value) $wire.clearPasswords() })">
    <x-auth.register-fields :drawer="true" />
</form>
