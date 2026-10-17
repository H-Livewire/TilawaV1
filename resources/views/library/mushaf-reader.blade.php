<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mushaf — Tilawa</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-mushaf-frame>
    <main class="mushaf-shell">
        <div class="mushaf-controls" aria-label="Mushaf controls">
            <form data-jump class="reader-page-jump">
                <label for="mushaf-page">Page</label>
                <input id="mushaf-page" type="number" min="1" max="604" value="1" required inputmode="numeric">
                <span>/ 604</span>
                <button type="submit" aria-label="Go to page" class="reader-jump-submit">↵</button>
            </form>
            <button type="button" data-layout aria-pressed="false">Single page</button>
            <button type="button" data-fullscreen>Fullscreen</button>
        </div>
        <p data-status class="mushaf-status" role="status" aria-live="polite">Opening Mushaf…</p>
        <div class="mushaf-book-area">
            <button type="button" data-previous class="mushaf-side-arrow mushaf-side-previous" aria-label="Previous Mushaf page"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg></button>
            <button type="button" data-next class="mushaf-side-arrow mushaf-side-next" aria-label="Next Mushaf page"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 5-7 7 7 7"/></svg></button>
        <div class="mushaf-stage" data-stage tabindex="0" aria-label="Quran pages. Left arrow for next, right arrow for previous.">
            <div data-book></div>
        </div>
        </div>
        <div data-error hidden role="alert" class="mushaf-status">Unable to load these pages. <button type="button" data-retry>Try again</button></div>
        <p class="sr-only">Drag a page edge or use the arrows to turn pages.</p>
    </main>
</body>
</html>
