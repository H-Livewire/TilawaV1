<div class="relative" x-data="{ open: false, pos: {}, place(max) { const r = this.$refs.trigger.getBoundingClientRect(); const width = Math.min(max, window.innerWidth - 32); const left = Math.max(16, Math.min(r.left, window.innerWidth - 16 - width)); this.pos = { left: (left - r.left) + 'px', width: width + 'px' }; } }" x-effect="if (open) place(384)" @click.outside="open = false" @keydown.escape.window="open = false">
    <button type="button" x-ref="trigger" @click="open = !open" x-bind:aria-expanded="open"
        class="flex items-center gap-2.5 rounded-xl border border-tilawa-line bg-tilawa-surface py-2.5 pl-4 pr-3.5 text-sm font-bold text-tilawa-ink shadow-sm transition hover:border-tilawa-teal dark:border-white/10 dark:bg-[#1c2b30] dark:text-[#f3ead9] dark:hover:border-tilawa-teal">
        <x-ui.icon name="book" class="h-4 w-4 shrink-0 text-tilawa-sub" />
        <span>Tafsir</span>
        <x-ui.icon name="chevron-down" class="h-[14px] w-[14px] shrink-0 text-tilawa-sub transition-transform duration-200" x-bind:class="open ? 'rotate-180' : ''" />
    </button>

    <div x-show="open" x-cloak role="dialog" aria-label="Choose a tafsir"
        x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
        x-bind:style="pos" class="absolute top-full z-40 mt-2 origin-top-left rounded-2xl border border-tilawa-line bg-tilawa-surface p-4 shadow-xl dark:border-white/10 dark:bg-[#1c2b30]">
        <x-ui.tafsir-picker />
        <p class="mt-4 text-xs leading-relaxed text-tilawa-sub dark:text-[#a9b9bc]">Open the book icon under any ayah to read its tafsir.</p>
    </div>
</div>
