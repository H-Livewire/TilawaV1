<div class="bg-tilawa-surface">
    <x-ui.site-header />
    <main>
        <section class="border-b border-tilawa-line bg-tilawa-paper">
            <div class="mx-auto grid max-w-6xl items-center gap-8 px-6 py-12 sm:px-8 lg:grid-cols-2 lg:px-10">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-tilawa-teal-dark dark:text-tilawa-teal-light">Read with reflection</p>
                    <h1 class="mt-4 text-4xl font-extrabold tracking-tight text-tilawa-ink">Ruqyah reading</h1>
                    <p class="mt-5 text-base leading-8 text-tilawa-sub">Turn to Allah through Qur'an recitation and supplication, seeking His protection and healing. Begin with Al-Fatihah, Ayat al-Kursi, and the surahs below.</p>
                    <a href="#passages" class="mt-6 inline-flex rounded-full bg-tilawa-amber px-6 py-3 text-sm font-bold text-[#12313a]">Explore the collection ↓</a>
                </div>
                <img src="{{ asset('ruqraimage/ChatGPT Image Sep 25, 2026, 05_19_18 PM.png') }}" alt="An open Qur'an on a wooden stand" width="1397" height="1124" class="h-auto w-full object-contain">
            </div>
        </section>
        <section id="passages" class="mx-auto max-w-6xl px-6 py-12 sm:px-8 lg:px-10">
            <h2 class="text-2xl font-extrabold text-tilawa-ink">Surahs and ayahs to read</h2>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-tilawa-sub">A selected collection for Ruqyah and protection, with references for each passage. Collections differ; this is not an exhaustive or prescribed sequence. Each reading link opens the full surah at the indicated starting ayah.</p>
            @if ($apiUnavailable)
                <p role="status" class="mt-5 rounded-xl border border-tilawa-line bg-tilawa-paper p-4 text-sm text-tilawa-sub">Arabic surah names are temporarily unavailable. You can still open the reading links below.</p>
            @endif
            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($passages as $passage)
                    <article class="flex flex-col rounded-2xl border border-tilawa-line p-5">
                        <div class="flex items-center justify-between gap-3">
                            <x-ui.number-badge :number="$passage['surah']" class="h-12 w-12" />
                            @if ($passage['name'])<span lang="ar" dir="rtl" class="font-arabic text-2xl text-tilawa-teal-dark dark:text-tilawa-teal-light">{{ $passage['name'] }}</span>@endif
                        </div>
                        <h3 class="mt-4 text-base font-bold text-tilawa-ink">{{ $passage['title'] }}</h3>
                        <p class="mt-2 text-xs text-tilawa-sub">{{ $passage['range'] }}</p>
                        <a href="https://sunnah.com/bukhari:{{ $passage['source'] }}" target="_blank" rel="noopener noreferrer" class="mt-4 text-xs text-tilawa-sub underline underline-offset-4">Reference: Sahih al-Bukhari {{ $passage['source'] }}</a>
                        <div class="mt-auto pt-6"><a href="{{ route('surah.show', ['number' => $passage['surah'], 'ayah' => $passage['ayah']]) }}" wire:navigate aria-label="Read {{ $passage['title'] }}" class="inline-flex items-center gap-3 rounded-full bg-tilawa-amber px-5 py-2.5 text-xs font-bold text-[#12313a]">Start reading <x-ui.icon name="arrow-right" class="h-4 w-4" /></a></div>
                    </article>
                @endforeach
            </div>
        </section>
    </main>
    <x-ui.site-footer />
</div>
