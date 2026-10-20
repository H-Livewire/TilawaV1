@props(['mode', 'language', 'languages', 'credits', 'tajweed', 'sample' => null])

{{-- Gear button + right-hand drawer with the reader settings. Quran state goes through $wire (Reading.php);
     sizes and show/hide choices live in the reader store (resources/js/reader-prefs.js). --}}
<div x-data="{ open: false, reciter: null, reciters: [],
        sync(event) { this.reciter = event.detail.reciter; this.reciters = event.detail.list; } }"
    x-on:audio-reciter.window="sync($event)"
    x-init="$watch('open', value => { if (value) window.dispatchEvent(new CustomEvent('audio-reciter-request')) })"
    @keydown.escape.window="open = false">
    <button type="button" @click="open = true" aria-label="Reader settings" title="Reader settings"
        class="flex h-10 w-10 items-center justify-center rounded-xl border border-tilawa-line bg-tilawa-surface text-tilawa-sub shadow-sm transition hover:border-tilawa-teal hover:text-tilawa-teal-dark dark:border-white/10 dark:bg-[#1c2b30] dark:text-[#a9b9bc]">
        <x-ui.icon name="settings" class="h-[19px] w-[19px]" />
    </button>

    <div x-show="open" x-cloak x-transition.opacity.duration.200ms @click="open = false"
        class="fixed inset-0 z-40 bg-tilawa-ink/40 dark:bg-black/60"></div>

    <div x-show="open" x-cloak role="dialog" aria-label="Reader settings"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 z-50 flex w-full max-w-sm flex-col bg-tilawa-surface shadow-2xl dark:bg-[#1c2b30]">
        <div class="flex items-center justify-between border-b border-tilawa-line px-5 py-4 dark:border-white/10">
            <h2 class="text-base font-bold text-tilawa-ink dark:text-[#f3ead9]">Reader settings</h2>
            <button type="button" @click="open = false" aria-label="Close"
                class="flex h-8 w-8 items-center justify-center rounded-full text-tilawa-sub transition hover:bg-tilawa-line hover:text-tilawa-ink dark:text-[#a9b9bc] dark:hover:bg-white/10">
                <x-ui.icon name="close" class="h-4 w-4" />
            </button>
        </div>

        @php
            $label = 'mb-1.5 block text-sm font-bold text-tilawa-ink dark:text-[#f3ead9]';
            $select = 'w-full rounded-xl border border-tilawa-line bg-tilawa-paper/60 py-2.5 pl-3 pr-8 text-sm font-semibold text-tilawa-ink focus:outline-none focus:ring-2 focus:ring-tilawa-teal-dark dark:border-white/10 dark:bg-white/5 dark:text-[#f3ead9]';
            $row = 'flex items-center justify-between gap-4 py-3';
        @endphp

        <div class="grow space-y-1 overflow-y-auto px-5 py-4">
            {{-- Translation language --}}
            <div class="pb-3">
                <label for="reader-language" class="{{ $label }}">Translation Language</label>
                <select id="reader-language" class="{{ $select }}" @change="$wire.setTranslationLanguage($event.target.value)">
                    @foreach ($languages as $code => $name)
                        <option value="{{ $code }}" @selected($language === $code)>{{ $name }}@if (! empty($credits[$code])) · {{ $credits[$code] }}@endif</option>
                    @endforeach
                </select>
                <p class="mt-1.5 text-xs text-tilawa-sub dark:text-[#a9b9bc]">{{ $credits[$language] ?? '' }}</p>
            </div>

            @if ($mode === 'ayat')
                <div class="pb-2">
                    <label for="reader-arabic-size" class="{{ $label }}">Font Size</label>
                    <input id="reader-arabic-size" type="range" min="0" max="4" step="1" class="w-full accent-tilawa-teal-dark"
                        x-bind:value="$store.reader.arabicLevel" @input="$store.reader.arabicLevel = +$event.target.value; $store.reader.save()">
                </div>
                <div class="pb-2">
                    <label for="reader-translation-size" class="{{ $label }}">Translation Font Size</label>
                    <input id="reader-translation-size" type="range" min="0" max="4" step="1" class="w-full accent-tilawa-teal-dark"
                        x-bind:value="$store.reader.translationLevel" @input="$store.reader.translationLevel = +$event.target.value; $store.reader.save()">
                </div>

                @foreach ([['translation', 'Enable Translation'], ['transliteration', 'Enable Transliteration']] as [$key, $text])
                    <div class="{{ $row }}">
                        <span class="text-sm font-bold text-tilawa-ink dark:text-[#f3ead9]">{{ $text }}</span>
                        <button type="button" role="switch" x-bind:aria-checked="$store.reader.{{ $key }}" aria-label="{{ $text }}"
                            @click="$store.reader.{{ $key }} = ! $store.reader.{{ $key }}; $store.reader.save()"
                            x-bind:class="$store.reader.{{ $key }} ? 'bg-tilawa-teal-dark' : 'bg-tilawa-line dark:bg-white/20'"
                            class="relative h-6 w-11 shrink-0 rounded-full transition-colors">
                            <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform"
                                x-bind:class="$store.reader.{{ $key }} ? 'translate-x-5' : ''"></span>
                        </button>
                    </div>
                @endforeach
            @endif

            <div class="{{ $row }}">
                <span class="text-sm font-bold text-tilawa-ink dark:text-[#f3ead9]">Mushaf mode</span>
                <button type="button" role="switch" aria-checked="{{ $mode === 'mushaf' ? 'true' : 'false' }}" aria-label="Mushaf mode"
                    wire:click="setMode('{{ $mode === 'mushaf' ? 'ayat' : 'mushaf' }}')" @click="open = false"
                    class="relative h-6 w-11 shrink-0 rounded-full transition-colors {{ $mode === 'mushaf' ? 'bg-tilawa-teal-dark' : 'bg-tilawa-line dark:bg-white/20' }}">
                    <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform {{ $mode === 'mushaf' ? 'translate-x-5' : '' }}"></span>
                </button>
            </div>

            <div class="{{ $row }}">
                <span class="text-sm font-bold text-tilawa-ink dark:text-[#f3ead9]">Tajweed colours</span>
                <button type="button" role="switch" aria-checked="{{ $tajweed ? 'true' : 'false' }}" aria-label="Tajweed colours"
                    wire:click="toggleTajweed"
                    class="relative h-6 w-11 shrink-0 rounded-full transition-colors {{ $tajweed ? 'bg-tilawa-teal-dark' : 'bg-tilawa-line dark:bg-white/20' }}">
                    <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform {{ $tajweed ? 'translate-x-5' : '' }}"></span>
                </button>
            </div>

            {{-- Qari (the recitation player owns the choice; this talks to it by events) --}}
            <div class="pt-2" x-show="reciters.length">
                <label for="reader-qari" class="{{ $label }}">Qari</label>
                <select id="reader-qari" class="{{ $select }}" @change="window.dispatchEvent(new CustomEvent('audio-set-reciter', { detail: $event.target.value }))">
                    <template x-for="item in reciters" :key="item.id">
                        <option x-bind:value="item.id" x-text="item.name" x-bind:selected="item.id === reciter"></option>
                    </template>
                </select>
            </div>

            @if ($mode === 'ayat' && $sample)
                <div class="mt-4 rounded-2xl border border-tilawa-line p-4 dark:border-white/10">
                    <p class="font-arabic text-right leading-[2.2] text-tilawa-ink dark:text-[#f3ead9]" dir="rtl"
                        x-bind:style="{ fontSize: $store.reader.arabicSize }">{{ $sample['arabic'] }}</p>
                    <p class="mt-2 leading-relaxed text-tilawa-ink dark:text-[#f3ead9]" x-show="$store.reader.translation"
                        x-bind:style="{ fontSize: $store.reader.translationSize }">{{ $sample['translation'] }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
