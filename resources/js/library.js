let teardown;

function mountLibrary() {
    teardown?.();
    const root = document.querySelector('[data-library-reader]');
    if (!root) return;
    const dialog = root.querySelector('[data-book-dialog]');
    const frame = root.querySelector('[data-reader-frame]');
    const buttons = root.querySelectorAll('[data-open-book]');
    const list = root.querySelector('[data-book-list]');
    let opener;
    const events = new AbortController();
    const close = () => {
        dialog.hidden = true;
        list.hidden = false;
        frame.removeAttribute('src');

        opener?.focus();
    };
    buttons.forEach(button => button.addEventListener('click', () => {
        opener = button;
        frame.title = button.dataset.bookTitle;
        frame.src = button.dataset.readerSrc;
        dialog.hidden = false;
        list.hidden = true;
        dialog.scrollIntoView({ block: 'start', behavior: 'smooth' });

    }, { signal: events.signal }));
    dialog.addEventListener('cancel', event => { event.preventDefault(); close(); }, { signal: events.signal });
    window.addEventListener('message', event => {
        if (event.origin === location.origin && event.source === frame.contentWindow && event.data === 'tilawa:close-book') close();
    }, { signal: events.signal });
    teardown = () => {
        events.abort();
        frame.removeAttribute('src');
        dialog.hidden = true;
        list.hidden = false;

        teardown = undefined;
    };
}

document.addEventListener('livewire:navigating', () => teardown?.());
document.addEventListener('livewire:navigated', mountLibrary);
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', mountLibrary, { once: true });
else mountLibrary();

if (document.querySelector('[data-pdf-reader]')) {
    document.querySelector('[data-close]').addEventListener('click', () => {
        if (window.parent !== window) window.parent.postMessage('tilawa:close-book', location.origin);
        else location.assign('/library');
    });
    import('./pdf-reader.js').catch(() => {
        document.querySelector('[data-reader-loading]').hidden = true;
        document.querySelector('[data-reader-error]').hidden = false;
        document.querySelector('[data-error-text]').textContent = 'The reader could not load. Check your connection and try again.';
        document.querySelector('[data-retry]').onclick = () => location.reload();
    });
}
