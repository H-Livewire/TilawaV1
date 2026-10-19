@props(['surah', 'total', 'name'])

<div x-data="audioPlayer(@js(['surah' => $surah, 'total' => $total, 'name' => $name]))"
    x-on:audio-play.window="play($event.detail)"
    x-on:audio-toggle.window="toggle()"
    x-on:keydown.escape.window="menu = null">
    {{-- Keeps the last ayah clear of the fixed bar --}}
    <div x-cloak x-show="open" class="h-24"></div>

    <div x-cloak x-show="open" x-transition.opacity.duration.150ms role="region" aria-label="Recitation player"
        class="fixed inset-x-0 bottom-0 z-50 border-t border-tilawa-line bg-tilawa-surface shadow-[0_-8px_24px_rgba(0,0,0,.12)]">
        <p x-cloak x-show="error || loading" class="px-4 pt-2 text-center text-xs font-semibold sm:hidden" x-bind:class="error ? 'text-red-500' : 'text-tilawa-sub'" x-text="statusText"></p>
        <div class="relative mx-auto flex max-w-3xl items-center justify-between gap-2 px-4 py-3">
            {{-- Options menu --}}
            <div class="relative" x-on:click.outside="menu = null">
                <button type="button" aria-label="More options" x-bind:aria-expanded="menu !== null"
                    @click="menu = menu ? null : 'main'"
                    class="flex h-11 w-11 items-center justify-center rounded-full text-tilawa-ink transition hover:bg-tilawa-line">
                    <x-ui.icon name="dots" class="h-[22px] w-[22px]" />
                </button>

                <div x-cloak x-show="menu" x-transition.opacity.duration.120ms
                    class="absolute bottom-full left-0 mb-3 w-72 max-w-[calc(100vw-2rem)] rounded-2xl border border-tilawa-line bg-tilawa-surface p-2 text-sm text-tilawa-ink shadow-xl">
                    {{-- Main --}}
                    <div x-show="menu === 'main'">
                        <button type="button" @click="download()"
                            class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left transition hover:bg-tilawa-line">
                            <x-ui.icon name="download" class="h-[18px] w-[18px]" /> Download
                        </button>
                        <button type="button" @click="menu = 'repeat'"
                            class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left transition hover:bg-tilawa-line">
                            <x-ui.icon name="repeat" class="h-[18px] w-[18px]" /> Manage repeat settings
                        </button>
                        <div class="my-1.5 border-t border-tilawa-line"></div>
                        <button type="button" @click="menu = 'experience'"
                            class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left transition hover:bg-tilawa-line">
                            <x-ui.icon name="bolt" class="h-[18px] w-[18px]" /> <span class="grow">Experience</span>
                            <x-ui.icon name="chevron-right" class="h-[16px] w-[16px] text-tilawa-sub" />
                        </button>
                        <button type="button" @click="menu = 'speed'"
                            class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left transition hover:bg-tilawa-line">
                            <span class="w-[18px] text-center text-xs font-bold" x-text="speed"></span> <span class="grow">Speed</span>
                            <x-ui.icon name="chevron-right" class="h-[16px] w-[16px] text-tilawa-sub" />
                        </button>
                        <button type="button" @click="menu = 'reciter'"
                            class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left transition hover:bg-tilawa-line">
                            <x-ui.icon name="user" class="h-[18px] w-[18px]" /> <span class="grow">Reciter</span>
                            <x-ui.icon name="chevron-right" class="h-[16px] w-[16px] text-tilawa-sub" />
                        </button>
                    </div>

                    {{-- Sub menus share one header --}}
                    <div x-show="menu && menu !== 'main'">
                        <button type="button" @click="menu = 'main'"
                            class="mb-1 flex w-full items-center gap-2 border-b border-tilawa-line px-2 pb-2.5 pt-1 text-left font-semibold">
                            <x-ui.icon name="chevron-left" class="h-[18px] w-[18px]" />
                            <span x-text="{ repeat: 'Repeat settings', experience: 'Experience', speed: 'Speed', reciter: 'Select Reciter' }[menu]"></span>
                        </button>

                        <div x-show="menu === 'repeat'">
                            <template x-for="item in repeats" :key="item.value">
                                <button type="button" @click="choose('repeat', item.value)"
                                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left transition hover:bg-tilawa-line">
                                    <span class="w-[18px]"><x-ui.icon name="check" class="h-[16px] w-[16px] text-tilawa-teal-dark" x-show="repeat === item.value" /></span>
                                    <span x-text="item.label"></span>
                                </button>
                            </template>
                        </div>

                        <div x-show="menu === 'experience'">
                            <template x-for="item in experiences" :key="item.value">
                                <button type="button" @click="choose('experience', item.value)"
                                    class="flex w-full items-start gap-3 rounded-xl px-3 py-2.5 text-left transition hover:bg-tilawa-line">
                                    <span class="mt-0.5 w-[18px]"><x-ui.icon name="check" class="h-[16px] w-[16px] text-tilawa-teal-dark" x-show="experience === item.value" /></span>
                                    <span><span class="block" x-text="item.label"></span><span class="block text-xs text-tilawa-sub" x-text="item.hint"></span></span>
                                </button>
                            </template>
                        </div>

                        <div x-show="menu === 'speed'">
                            <template x-for="value in speeds" :key="value">
                                <button type="button" @click="choose('speed', value)"
                                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left transition hover:bg-tilawa-line">
                                    <span class="w-[18px]"><x-ui.icon name="check" class="h-[16px] w-[16px] text-tilawa-teal-dark" x-show="speed === value" /></span>
                                    <span x-text="value"></span>
                                </button>
                            </template>
                        </div>

                        <div x-show="menu === 'reciter'" class="max-h-[50vh] overflow-y-auto overscroll-contain">
                            <template x-for="item in reciters" :key="item.id">
                                <button type="button" @click="choose('reciter', item.id)" x-bind:disabled="!item.qdc"
                                    class="flex w-full items-start gap-3 rounded-xl px-3 py-2.5 text-left transition hover:bg-tilawa-line disabled:cursor-default disabled:opacity-45 disabled:hover:bg-transparent">
                                    <span class="mt-0.5 w-[18px] shrink-0"><x-ui.icon name="check" class="h-[16px] w-[16px]" x-show="reciter === item.id" /></span>
                                    <span><span x-text="item.name"></span> <span class="text-tilawa-sub">- <span x-text="item.style"></span></span></span>
                                    <span x-show="!item.qdc" class="ml-auto shrink-0 text-[11px] font-semibold text-tilawa-sub">Soon</span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Transport --}}
            <div class="flex items-center gap-1 sm:gap-3">
                <button type="button" aria-label="Volume" @click="toggleMute()"
                    class="flex h-11 w-11 items-center justify-center rounded-full text-tilawa-ink transition hover:bg-tilawa-line">
                    <x-ui.icon name="volume" class="h-[22px] w-[22px]" x-show="!muted" />
                    <x-ui.icon name="volume-off" class="h-[22px] w-[22px]" x-cloak x-show="muted" />
                </button>
                <button type="button" aria-label="Previous ayah" @click="step(-1)" x-bind:disabled="ayah <= 1"
                    class="flex h-11 w-11 items-center justify-center rounded-full text-tilawa-ink transition hover:bg-tilawa-line disabled:opacity-35">
                    <x-ui.icon name="skip-prev" class="h-[24px] w-[24px]" />
                </button>
                <button type="button" x-bind:aria-label="playing ? 'Pause' : 'Play'" @click="toggle()"
                    class="flex h-12 w-12 items-center justify-center rounded-full bg-tilawa-teal text-white transition hover:opacity-90">
                    <x-ui.icon name="play" class="h-[22px] w-[22px]" x-show="!playing" />
                    <x-ui.icon name="pause" class="h-[22px] w-[22px]" x-cloak x-show="playing" />
                </button>
                <button type="button" aria-label="Next ayah" @click="step(1)" x-bind:disabled="ayah >= total"
                    class="flex h-11 w-11 items-center justify-center rounded-full text-tilawa-ink transition hover:bg-tilawa-line disabled:opacity-35">
                    <x-ui.icon name="skip-next" class="h-[24px] w-[24px]" />
                </button>
            </div>

            <div class="flex items-center gap-2">
                <span class="hidden max-w-[11rem] truncate text-xs font-semibold sm:inline" x-bind:class="error ? 'text-red-500' : 'text-tilawa-sub'" x-text="statusText"></span>
                <button type="button" aria-label="Close player" @click="close()"
                    class="flex h-11 w-11 items-center justify-center rounded-full text-tilawa-ink transition hover:bg-tilawa-line">
                    <x-ui.icon name="close" class="h-[20px] w-[20px]" />
                </button>
            </div>
        </div>
    </div>
</div>
