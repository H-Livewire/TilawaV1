@props(['label', 'icon' => null, 'type' => 'text', 'name', 'id' => null])

<label class="flex flex-col gap-1.5">
    <span class="text-sm font-semibold text-tilawa-ink">{{ $label }}</span>
    <div class="flex items-center gap-2.5 rounded-xl border border-tilawa-line bg-tilawa-surface px-3.5 py-3 focus-within:border-tilawa-teal-dark focus-within:ring-2 focus-within:ring-tilawa-teal-dark dark:focus-within:border-tilawa-teal-light dark:focus-within:ring-tilawa-teal-light">
        @if ($icon)
            <x-ui.icon :name="$icon" class="h-[18px] w-[18px] shrink-0 text-tilawa-sub/70" />
        @endif
        <input type="{{ $type }}" name="{{ $name }}" id="{{ $id ?? $name }}"
            {{ $attributes->merge(['class' => 'min-w-0 grow bg-transparent border-none p-0 text-sm text-tilawa-ink placeholder:text-tilawa-sub focus:outline-none focus:ring-0']) }}>
        {{ $slot ?? '' }}
    </div>
    @error($name)
        <span class="text-xs font-medium text-red-500">{{ $message }}</span>
    @enderror
</label>
