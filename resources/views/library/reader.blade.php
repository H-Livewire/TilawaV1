<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $book['title'] }} — {{ $book['language'] }} — Tilawa reader</title>
    <script>try { if (localStorage.getItem('tilawa-theme') === 'dark' || (!localStorage.getItem('tilawa-theme') && matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark'); } catch (e) {}</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="pdf-reader-body" data-pdf-reader data-pdf-url="{{ $book['pdf_url'] }}" data-book-title="{{ $book['title'] }}" data-wasm-url="{{ asset('vendor/pdfjs/wasm') }}/">
            <div class="reader-shell">
                <div class="reader-topbar">
                    <span data-page-status class="reader-page-count" role="status" aria-live="polite">Opening book…</span>
                    <span class="reader-book-title">{{ $book['title'] }} · {{ $book['language'] }}</span>
                    <button type="button" data-close class="reader-close reader-button" aria-label="Close reader"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg><span>Close</span></button>
                </div>
                <button type="button" data-prev class="reader-arrow reader-prev" aria-label="Previous page">‹</button>
                <button type="button" data-next class="reader-arrow reader-next" aria-label="Next page">›</button>
                <div class="reader-toolbar" aria-label="Reading controls">
                    <form data-jump-form class="reader-page-jump">
                        <label for="book-page">Page</label>
                        <input id="book-page" data-page-input type="number" min="1" max="{{ $book['pages'] }}" value="1" inputmode="numeric" required aria-label="Go to page">
                        <span class="reader-page-total">/ {{ $book['pages'] }}</span>
                        <button class="reader-jump-submit" type="submit" aria-label="Go to page" title="Go to page"><svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 12h14m-5-5 5 5-5 5"/></svg></button>
                    </form>
                    <div class="reader-control-group"><button type="button" data-zoom-out class="reader-button" aria-label="Zoom out">−</button><output data-zoom-label>100%</output><button type="button" data-zoom-in class="reader-button" aria-label="Zoom in">+</button><button type="button" data-fullscreen class="reader-button">Fullscreen</button></div>
                </div>
                <div class="reader-viewport" data-viewport tabindex="0" aria-label="Book pages. Use left and right arrow keys to turn pages."><div data-book-stage class="reader-stage"></div></div>
                <div data-reader-loading class="reader-loading" role="status"><span class="reader-spinner" aria-hidden="true"></span><span>Preparing your book…</span></div>
                <div data-reader-error class="reader-error" role="alert" hidden><p data-error-text></p><button type="button" data-retry class="reader-button">Try again</button><a href="{{ $book['pdf_url'] }}" target="_blank" rel="noopener" class="reader-button">Open original PDF</a></div>
                <p class="reader-hint sr-only" data-reader-hint>Drag a corner, swipe, or use the arrow keys. Zoom in for smaller text.</p>
            </div>
    <noscript><a href="{{ $book['pdf_url'] }}">Open the original PDF</a></noscript>
</body>
</html>
