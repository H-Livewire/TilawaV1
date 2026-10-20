{{-- Language + tafsir choice, shared by the toolbar menu and every ayah's tafsir panel. --}}
<div>
    <div class="flex flex-wrap items-center gap-2">
        <div class="relative">
            <select x-bind:value="$store.tafsir.lang" @change="$store.tafsir.setLanguage($event.target.value)" aria-label="Tafsir language"
                class="appearance-none rounded-lg border-none bg-tilawa-paper/70 py-2 pl-3 pr-8 text-sm font-semibold text-tilawa-ink focus:outline-none focus:ring-2 focus:ring-tilawa-teal-dark dark:bg-white/5 dark:text-[#f3ead9]">
                <template x-for="item in $store.tafsir.languages" :key="item.code">
                    <option x-bind:value="item.code" x-bind:disabled="!item.ready" x-bind:selected="item.code === $store.tafsir.lang"
                        x-text="item.ready ? item.label : item.label + ' (soon)'"></option>
                </template>
            </select>
            <x-ui.icon name="chevron-down" class="pointer-events-none absolute right-2.5 top-1/2 h-[13px] w-[13px] -translate-y-1/2 text-tilawa-sub" />
        </div>
        <template x-for="item in $store.tafsir.forLanguage" :key="item.id">
            <button type="button" @click="$store.tafsir.select(item.id)" x-text="item.name"
                x-bind:aria-pressed="$store.tafsir.id === item.id"
                x-bind:class="$store.tafsir.id === item.id ? 'bg-tilawa-teal text-white' : 'bg-tilawa-paper/70 text-tilawa-ink hover:bg-tilawa-line dark:bg-white/5 dark:text-[#f3ead9] dark:hover:bg-white/10'"
                class="rounded-full px-3.5 py-1.5 text-sm font-semibold transition"></button>
        </template>
    </div>
    <p class="mt-2 text-xs text-tilawa-sub dark:text-[#a9b9bc]">Kiswahili tafsir is not available yet.</p>
</div>
