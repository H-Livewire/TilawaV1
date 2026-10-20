@props(['title', 'triggerLabel', 'triggerIcon' => null, 'searchPlaceholder' => null])

{{-- A picker that opens right under its own button (same slot contract as x-ui.drawer). --}}
<div class="relative" x-data="{ open: false, query: '', pos: {}, place(max) { const r = this.$refs.trigger.getBoundingClientRect(); const width = Math.min(max, window.innerWidth - 32); const left = Math.max(16, Math.min(r.left, window.innerWidth - 16 - width)); this.pos = { left: (left - r.left) + 'px', width: width + 'px' }; } }" @click.outside="open = false; query = ''" @keydown.escape.window="open = false"
    x-init="$watch('open', value => { if (value) { place(352); $nextTick(() => $refs.dropdownSearch?.focus()) } else { query = '' } })">
    <button type="button" x-ref="trigger" @click="open = !open" x-bind:aria-expanded="open"
        class="flex items-center gap-2.5 rounded-xl border border-tilawa-line bg-tilawa-surface py-2.5 pl-4 pr-3.5 text-sm font-bold text-tilawa-ink shadow-sm transition hover:border-tilawa-teal dark:border-white/10 dark:bg-[#1c2b30] dark:text-[#f3ead9] dark:hover:border-tilawa-teal">
        @if ($triggerIcon)
            <x-ui.icon :name="$triggerIcon" class="h-4 w-4 shrink-0 text-tilawa-sub" />
        @endif
        <span class="truncate">{{ $triggerLabel }}</span>
        <x-ui.icon name="chevron-down" class="h-[14px] w-[14px] shrink-0 text-tilawa-sub transition-transform duration-200" x-bind:class="open ? 'rotate-180' : ''" />
    </button>

    <div x-show="open" x-cloak role="dialog" aria-label="{{ $title }}"
        x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
        x-bind:style="pos" class="absolute top-full z-40 mt-2 flex max-h-[70vh] origin-top-left flex-col overflow-hidden rounded-2xl border border-tilawa-line bg-tilawa-surface shadow-xl dark:border-white/10 dark:bg-[#1c2b30]">
        @if ($searchPlaceholder)
            <div class="border-b border-tilawa-line p-3 dark:border-white/10">
                <div class="relative">
                    <x-ui.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-tilawa-sub/60" />
                    <input type="text" x-model="query" x-ref="dropdownSearch" placeholder="{{ $searchPlaceholder }}"
                        class="w-full rounded-lg border-none bg-tilawa-paper/70 py-2.5 pl-9 pr-3 text-sm text-tilawa-ink placeholder:text-tilawa-sub focus:outline-none focus:ring-2 focus:ring-tilawa-teal-dark dark:bg-white/5 dark:text-[#f3ead9] dark:placeholder:text-[#a9b9bc] dark:focus:ring-tilawa-teal-light">
                </div>
            </div>
        @endif
        <div class="grow overflow-y-auto p-2">
            {{ $slot }}
        </div>
    </div>
</div>
