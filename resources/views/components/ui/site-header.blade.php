<header class="relative z-30 border-b border-tilawa-line bg-tilawa-surface" x-data="{ menuOpen: false }" @keydown.escape.window="menuOpen = false">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-2 px-5 py-5 sm:px-8 lg:px-10">
        <a href="{{ route('landing') }}" wire:navigate aria-label="Tilawa home" class="flex shrink-0 items-center gap-2.5 text-tilawa-ink">
            <x-ui.logo />
        </a>
        <nav aria-label="Main navigation" class="hidden items-center gap-5 text-sm font-semibold text-tilawa-sub lg:flex">
            <a href="{{ route('home') }}" wire:navigate @if (request()->routeIs('home')) aria-current="page" @endif class="transition hover:text-tilawa-teal-dark">Browse Qur'an</a>
            <a href="{{ route('ruqyah') }}" wire:navigate @if (request()->routeIs('ruqyah')) aria-current="page" @endif class="transition hover:text-tilawa-teal-dark">Ruqyah read</a>
            <a href="{{ route('landing') }}#about" class="transition hover:text-tilawa-teal-dark">About Tilawa</a>
            @auth
                <a href="{{ route('bookmarks') }}" wire:navigate class="transition hover:text-tilawa-teal-dark">Bookmarks</a>
            @endauth
        </nav>
        <div class="flex items-center gap-2 sm:gap-3">
            <div class="rounded-full bg-tilawa-teal-dark"><x-ui.theme-selector /></div>
            @auth
                <form method="POST" action="{{ route('logout') }}" class="hidden lg:block">@csrf<button type="submit" class="text-xs font-semibold text-tilawa-sub hover:text-tilawa-ink">Sign out</button></form>
                <a href="{{ route('profile') }}" wire:navigate class="whitespace-nowrap rounded-full bg-tilawa-amber px-3 py-2.5 text-xs font-bold text-[#12313a] sm:text-sm">My account</a>
            @else
                <button type="button" @click="$dispatch('open-login')" aria-haspopup="dialog" aria-controls="login-drawer" class="whitespace-nowrap rounded-full bg-tilawa-amber px-3 py-2.5 text-xs font-bold text-[#12313a] transition hover:brightness-105 sm:px-5 sm:text-sm">Get started</button>
            @endauth
            <button type="button" @click="menuOpen = !menuOpen" :aria-expanded="menuOpen" aria-controls="mobile-navigation" aria-label="Toggle navigation" class="flex h-8 w-8 items-center justify-center rounded-lg text-tilawa-ink lg:hidden">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" /></svg>
            </button>
        </div>
    </div>
    <nav id="mobile-navigation" aria-label="Mobile navigation" x-show="menuOpen" x-cloak class="flex flex-col gap-4 border-t border-tilawa-line px-5 py-5 text-sm font-semibold text-tilawa-ink lg:hidden">
        <a href="{{ route('home') }}" wire:navigate>Browse Qur'an</a>
        <a href="{{ route('ruqyah') }}" wire:navigate>Ruqyah read</a>
        <a href="{{ route('landing') }}#about" @click="menuOpen = false">About Tilawa</a>
        @auth
            <a href="{{ route('bookmarks') }}" wire:navigate>Bookmarks</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Sign out</button></form>
        @endauth
    </nav>
</header>
