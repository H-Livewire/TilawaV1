@php
    // Reading pages keep their own bottom recitation bar, and the landing page is a marketing page.
    $hidden = request()->routeIs('landing', 'surah.show', 'page.show', 'juz.show', 'login', 'register', 'password.*');

    $tabs = [
        ['Ruqyah', route('ruqyah'), request()->routeIs('ruqyah'),
            '<path class="fill" d="M12 3l7 2.8v5.4c0 4.4-3 8-7 9.8-4-1.8-7-5.4-7-9.8V5.8z"/><path d="M9 12.2l2.2 2.2L15.3 10"/>'],
        ['Library', route('library'), request()->routeIs('library*'),
            '<rect class="fill" x="4" y="4" width="4.5" height="16" rx="1"/><rect class="fill" x="10" y="4" width="4.5" height="16" rx="1"/><path d="M14.8 5.6l3.6-1 3 14.6-3.6 1z"/>'],
        ['Quran', route('home'), request()->routeIs('home', 'daily-ayah'),
            '<path class="fill" d="M12 6.5C10 5 7 4.5 4 5v13c3-.5 6 0 8 1.5 2-1.5 5-2 8-1.5V5c-3-.5-6 0-8 1.5z"/><path d="M12 6.5V19.5"/>'],
        ['Bookmarks', auth()->check() ? route('bookmarks') : route('login'), request()->routeIs('bookmarks'),
            '<path class="fill" d="M7 4h10a1 1 0 0 1 1 1v15l-6-4-6 4V5a1 1 0 0 1 1-1z"/>'],
        ['Account', auth()->check() ? route('profile') : route('login'), request()->routeIs('profile'),
            '<circle class="fill" cx="12" cy="8.5" r="3.8"/><path d="M4.5 20.5c.5-4 3.6-6 7.5-6s7 2 7.5 6"/>'],
    ];

    $center = 2; // the middle tab (Quran) always sits in a site-coloured circle
@endphp

@unless ($hidden)
    {{-- App-style tab bar: only shown when Tilawa runs as an installed app (see /pwa/tabbar.css). --}}
    <nav class="app-tabbar" aria-label="Main">
        @foreach ($tabs as $i => [$label, $url, $active, $icon])
            <a href="{{ $url }}" wire:navigate @if ($active) aria-current="page" @endif class="app-tab {{ $active ? 'is-active' : '' }} {{ $i === $center ? 'is-center' : '' }}">
                <span class="app-tab-icon"><svg viewBox="0 0 24 24" aria-hidden="true">{!! $icon !!}</svg></span>
                <span class="app-tab-label">{{ $label }}</span>
            </a>
        @endforeach
    </nav>
@endunless
