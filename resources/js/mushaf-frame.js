import { PageFlip } from 'page-flip';
import { spreadStart, bookIndex, pageAtIndex, nearbyPages } from './mushaf-pagination';

const stage = document.querySelector('[data-stage]');
const host = document.querySelector('[data-book]');
const status = document.querySelector('[data-status]');
const errorBox = document.querySelector('[data-error]');
const input = document.querySelector('#mushaf-page');
const previous = document.querySelector('[data-previous]');
const next = document.querySelector('[data-next]');
const layout = document.querySelector('[data-layout]');
const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
const pending = new Map();
const cache = new Map();
const pages = Array.from({ length: 604 }, (_, index) => {
    const page = document.createElement('article');
    page.className = 'mushaf-sheet';
    page.dataset.page = 604 - index;
    page.setAttribute('aria-label', `Mushaf page ${604 - index}`);
    return page;
});
let book;
let current = 1;
let retryPage = 1;
let settings;
let single = false;
let busy = true;
let suppress = false;
let serial = 0;
let generation = 0;
let queued;
let resizeTimer;
let disposed = false;
const send = data => parent.postMessage(data, location.origin);
function request(type, data) {
    const id = ++serial;
    return new Promise((resolve, reject) => {
        const timeout = setTimeout(() => { pending.delete(id); reject(new Error('Reader request timed out')); }, 60000);
        pending.set(id, { resolve, reject, timeout });
        send({ type, id, ...data });
    });
}
function isSpread() { return !single && innerWidth >= 900; }
function lock(value) {
    busy = value;
    stage.classList.toggle('is-loading', value);
    stage.setAttribute('aria-busy', String(value));
    previous.disabled = value || (isSpread() ? spreadStart(current) === 1 : current === 1);
    next.disabled = value || (isSpread() ? spreadStart(current) === 603 : current === 604);
    document.querySelectorAll('.mushaf-controls input, .mushaf-controls button').forEach(el => {
        if (el !== previous && el !== next) el.disabled = value;
    });
}
function updateStatus() {
    input.value = current;
    status.textContent = isSpread() ? `Pages ${spreadStart(current)}–${spreadStart(current) + 1} / 604` : `Page ${current} / 604`;
    layout.textContent = single ? 'Two pages' : 'Single page';
    layout.setAttribute('aria-pressed', String(single));
    layout.hidden = innerWidth < 900;
    lock(busy);
}
function reportHeight() {
    send({ type: 'mushaf:height', height: document.querySelector('main').getBoundingClientRect().height + 20 });
}
async function load(page) {
    const wanted = nearbyPages(page);
    const missing = wanted.filter(n => !cache.has(n));
    const version = generation;
    if (missing.length) {
        const result = await request('mushaf:load', { pages: missing });
        if (version !== generation) throw new Error('Superseded reader configuration');
        for (const number of missing) {
            if (typeof result.pages?.[number] !== 'string' || !result.pages[number].includes('data-verse')) throw new Error('Incomplete Quran page');
            cache.set(number, result.pages[number]);
            pages[bookIndex(number)].innerHTML = result.pages[number];
        }
    }
    for (const number of cache.keys()) {
        if (!wanted.includes(number)) {
            cache.delete(number);
            pages[bookIndex(number)].replaceChildren();
        }
    }
    await document.fonts.ready;
}
function sizeBook() {
    const width = Math.floor(Math.min(isSpread() ? (stage.clientWidth - 2) / 2 : stage.clientWidth - 2, 400));
    const measure = document.createElement('div');
    measure.className = 'mushaf-measure';
    measure.style.width = `${width}px`;
    document.body.append(measure);
    let height = Math.round(width * 1.5);
    const visible = isSpread() ? [spreadStart(current), spreadStart(current) + 1] : [current];
    for (const number of visible) {
        measure.innerHTML = cache.get(number) ?? '';
        height = Math.max(height, Math.ceil(measure.scrollHeight) + 12);
    }
    measure.remove();
    stage.style.height = `${height}px`;
    host.style.width = `${isSpread() ? width * 2 : width}px`;
    host.style.height = `${height}px`;
    host.style.minWidth = `${width}px`;
    host.style.minHeight = `${height}px`;
    const dimensions = { width, height, minWidth: width, maxWidth: width, minHeight: height, maxHeight: height };
    suppress = true;
    if (!book) {
        book = new PageFlip(host, {
            ...dimensions, size: 'fixed', autoSize: false, showCover: false,
            usePortrait: true, startPage: bookIndex(current),
            flippingTime: 700, maxShadowOpacity: .22, mobileScrollSupport: true,
            useMouseEvents: !reducedMotion.matches, showPageCorners: !reducedMotion.matches,
            clickEventForward: false, disableFlipByClick: true,
        });
        book.loadFromHTML(pages);
        book.on('flip', event => {
            if (suppress) return;
            const page = pageAtIndex(event.data, isSpread());
            if (page >= 1 && page <= 604 && page !== current) commit(page);
        });
        book.on('changeState', event => {
            if (event.data !== 'read') lock(true);
            else if (!pending.size) lock(false);
        });
    } else {
        Object.assign(book.getSettings(), dimensions, { useMouseEvents: !reducedMotion.matches, showPageCorners: !reducedMotion.matches });
        book.update();
        book.turnToPage(bookIndex(current));
    }
    suppress = false;
    reportHeight();
}
async function show(page, persist = true) {
    retryPage = page;
    lock(true);
    errorBox.hidden = true;
    status.textContent = 'Loading pages…';
    const before = current;
    try {
        await load(page);
        if (persist) await request('mushaf:page', { page });
        current = page;
        sizeBook();
        updateStatus();
    } catch {
        current = before;
        errorBox.hidden = false;
        if (book) {
            suppress = true;
            book.turnToPage(bookIndex(current));
            suppress = false;
        }
        updateStatus();
    } finally {
        lock(false);
        reportHeight();
        if (queued) { const value = queued; queued = null; configure(value); }
    }
}
async function commit(page) { await show(page); }
function turn(direction) {
    if (busy || !book) return;
    const target = isSpread() ? spreadStart(current) + direction * 2 : current + direction;
    if (target < 1 || target > 604) return;
    if (reducedMotion.matches) { show(target); return; }
    lock(true);
    // Reversed physical page indices give an RTL book without mirroring Arabic.
    book.getSettings().disableFlipByClick = false;
    if (direction > 0) book.flipPrev('bottom');
    else book.flipNext('bottom');
    book.getSettings().disableFlipByClick = true;
    if (book.getState() === 'read') show(target);
}
async function configure(data) {
    // Follow the app's theme: white-on-dark pages in night mode, white paper otherwise.
    document.documentElement.classList.toggle('dark', data.dark === true);
    if (settings && settings.language === data.language && settings.tajweed === data.tajweed && current === data.page) return;
    if (busy && settings) { queued = data; return; }
    const changed = !settings || settings.language !== data.language || settings.tajweed !== data.tajweed;
    settings = data;
    if (changed) {
        generation++;
        cache.clear();
        pages.forEach(page => page.replaceChildren());
    }
    await show(data.page, false);
}
window.addEventListener('message', event => {
    if (event.origin !== location.origin || event.source !== parent) return;
    const data = event.data;
    if (data?.type === 'mushaf:configure') configure(data);
    else if (['mushaf:loaded', 'mushaf:saved'].includes(data?.type)) {
        const item = pending.get(data.id);
        if (!item) return;
        clearTimeout(item.timeout);
        pending.delete(data.id);
        if (data.error) item.reject(new Error('Unable to load pages'));
        else item.resolve(data);
    }
});
previous.addEventListener('click', () => turn(-1));
next.addEventListener('click', () => turn(1));
document.querySelector('[data-jump]').addEventListener('submit', event => {
    event.preventDefault();
    const page = Number(input.value);
    if (!busy && Number.isInteger(page) && page >= 1 && page <= 604) show(page);
});
layout.addEventListener('click', () => {
    if (busy) return;
    single = !single;
    sizeBook();
    updateStatus();
});
document.addEventListener('keydown', event => {
    if (event.target.closest('input, select, textarea') || event.altKey || event.metaKey || event.ctrlKey) return;
    if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
        event.preventDefault();
        turn(event.key === 'ArrowLeft' ? 1 : -1);
    }
});
let pointer;
stage.addEventListener('pointerdown', event => { pointer = { x: event.clientX, y: event.clientY }; }, true);
// StPageFlip's portrait corner guard also applies to its programmatic swipe.
// Permit only genuine quick swipes; ordinary word taps keep their annotations.
let swipeStart;
stage.addEventListener('touchstart', event => {
    const touch = event.changedTouches[0];
    swipeStart = touch && !busy ? { x: touch.clientX, y: touch.clientY, time: Date.now() } : null;
}, { capture: true, passive: true });
window.addEventListener('touchend', event => {
    const touch = event.changedTouches[0];
    const start = swipeStart;
    swipeStart = null;
    if (!book || !touch || !start || reducedMotion.matches) return;
    if (Math.abs(touch.clientX - start.x) > 30 && Math.abs(touch.clientY - start.y) < 60 && Date.now() - start.time < 250) {
        book.getSettings().disableFlipByClick = false;
        setTimeout(() => { if (!disposed) book.getSettings().disableFlipByClick = true; }, 0);
    }
}, { capture: true, passive: true });

stage.addEventListener('pointerup', event => {
    if (!reducedMotion.matches || !pointer || busy) return;
    const dx = event.clientX - pointer.x;
    if (Math.abs(dx) > 70 && Math.abs(event.clientY - pointer.y) < 60) turn(dx > 0 ? 1 : -1);
});
stage.addEventListener('click', event => {
    if (busy || !pointer || Math.abs(event.clientX - pointer.x) > 12 || Math.abs(event.clientY - pointer.y) > 12) return;
    const rule = event.target.closest('.tajweed-mark');
    const word = event.target.closest('[data-word]');
    if (rule) send({ type: 'mushaf:tajweed', rule: rule.dataset.tajweedRule });
    else if (word) send({ type: 'mushaf:word', word: JSON.parse(word.dataset.word) });
});
document.querySelector('[data-retry]').addEventListener('click', () => show(retryPage));
document.querySelector('[data-fullscreen]').addEventListener('click', async () => {
    try {
        if (document.fullscreenElement) await document.exitFullscreen();
        else {
            let timeout;
            try {
                await Promise.race([document.documentElement.requestFullscreen(), new Promise((_, reject) => { timeout = setTimeout(() => reject(new Error('Fullscreen unavailable')), 2000); })]);
                if (!document.fullscreenElement) throw new Error('Fullscreen unavailable');
            } finally { clearTimeout(timeout); }
        }
    } catch { status.textContent = 'Fullscreen is unavailable in this browser.'; }
});
document.addEventListener('fullscreenchange', () => {
    document.querySelector('[data-fullscreen]').textContent = document.fullscreenElement ? 'Exit fullscreen' : 'Fullscreen';
    sizeBook();
});
window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => { if (book && !busy && !disposed) { sizeBook(); updateStatus(); } }, 150);
});
window.addEventListener('pagehide', () => {
    disposed = true;
    clearTimeout(resizeTimer);
    pending.forEach(item => { clearTimeout(item.timeout); item.reject(new Error('Reader closed')); });
    pending.clear();
    cache.clear();
    book?.destroy();
}, { once: true });
send({ type: 'mushaf:ready' });
