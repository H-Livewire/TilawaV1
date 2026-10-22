<div>
    <x-ui.site-header />
    <main class="mx-auto max-w-6xl px-5 py-10 sm:px-8 lg:px-10" data-library-reader wire:ignore>
        <h1 class="text-3xl font-extrabold tracking-tight text-tilawa-ink">Islamic library</h1>
        <div data-book-list class="mt-7 grid gap-5 md:grid-cols-2">
            @foreach ($books as $book)
                <article data-book-card class="flex items-center gap-5 rounded-2xl border border-tilawa-line bg-tilawa-surface p-5">
                    <img src="{{ asset($book['cover']) }}" width="637" height="900" alt="{{ $book['title'] }} — {{ $book['language'] }} cover" loading="lazy" decoding="async" class="w-24 shrink-0 rounded border border-tilawa-line sm:w-28">
                    <div>
                        <h2 class="text-xl font-bold text-tilawa-ink">{{ $book['title'] }}</h2>
                        <p class="mt-2 text-sm text-tilawa-sub">{{ $book['language'] }} · {{ $book['pages'] }} pages</p>
                        <button type="button" data-open-book data-reader-src="{{ $book['reader_url'] }}" data-book-title="{{ $book['title'] }} · {{ $book['language'] }}" aria-label="Read {{ $book['title'] }} in {{ $book['language'] }}" class="mt-5 rounded-full bg-tilawa-amber px-5 py-2.5 text-sm font-bold text-[#12313a]">Read book</button>
                        <noscript><a href="{{ $book['pdf_url'] }}" class="underline">Open PDF</a></noscript>
                    </div>
                </article>
            @endforeach
        </div>
        <section data-book-dialog class="book-embed mt-7" aria-label="Book reader" hidden>
            <iframe data-reader-frame title="Interactive book reader" allow="fullscreen"></iframe>
        </section>
    </main>
    <x-ui.site-footer />
</div>
