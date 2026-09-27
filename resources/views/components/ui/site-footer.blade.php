<footer class="bg-[#12313a] text-white">
    <div class="mx-auto max-w-6xl px-6 pb-7 pt-14 sm:px-8 lg:px-10">
        <div class="grid gap-10 pb-12 sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr]">
            <div>
                <a href="{{ route('landing') }}" wire:navigate class="inline-flex items-center gap-3 text-2xl font-extrabold tracking-tight">
                    <x-ui.logo inverse />
                </a>
                <p class="mt-5 max-w-xs text-sm leading-7 text-white/70">A little space in your day for the Qur'an. Read, reflect, and return whenever you need.</p>
                <p class="mt-4 text-xs font-semibold text-tilawa-amber">Free to read. Open to everyone.</p>
            </div>
            <div>
                <h2 class="text-xs font-bold uppercase tracking-[0.16em] text-tilawa-amber">Explore</h2>
                <nav aria-label="Reading links" class="mt-5 flex flex-col gap-4 text-sm text-white/80">
                    <a href="{{ route('home') }}" wire:navigate class="hover:text-white">All surahs</a>
                    <a href="{{ route('surah.show', 1) }}" wire:navigate class="hover:text-white">Start with Al-Fatihah</a>
                    <a href="{{ route('juz.show', 1) }}" wire:navigate class="hover:text-white">Read by juz</a>
                    <a href="{{ route('page.show', 1) }}" wire:navigate class="hover:text-white">Read by page</a>
                </nav>
            </div>
            <div>
                <h2 class="text-xs font-bold uppercase tracking-[0.16em] text-tilawa-amber">Tilawa</h2>
                <nav aria-label="Information links" class="mt-5 flex flex-col gap-4 text-sm text-white/80">
                    <button type="button" data-install-tilawa hidden class="text-left hover:text-white">Install Tilawa app</button>
                    <a href="{{ route('landing') }}#about" class="hover:text-white">About the experience</a>
                    <a href="{{ route('privacy') }}" wire:navigate class="hover:text-white">Privacy Policy</a>
                    <a href="{{ route('terms') }}" wire:navigate class="hover:text-white">Terms of Service</a>
                    @guest
                        <button type="button" x-data @click="$dispatch('open-login')" aria-haspopup="dialog" aria-controls="login-drawer" class="text-left hover:text-white">Get started</button>
                    @else
                        <a href="{{ route('profile') }}" wire:navigate class="hover:text-white">My account</a>
                    @endguest
                </nav>
            </div>
        </div>
        <div class="flex flex-col justify-between gap-4 border-t border-white/15 pt-6 text-xs leading-6 text-white/60 md:flex-row">
            <p>&copy; {{ now()->year }} Tilawa. All rights reserved.</p>
            <p>Qur'an content by <a href="https://alquran.cloud" target="_blank" rel="noopener noreferrer" class="underline underline-offset-4 hover:text-white">Al Quran Cloud</a>. Word details &amp; Tajweed by <a href="https://quran.com" target="_blank" rel="noopener noreferrer" class="underline underline-offset-4 hover:text-white">Quran.com</a>.</p>
        </div>
    </div>
</footer>
