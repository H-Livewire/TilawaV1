import { pageRenderPlan } from './book-render-plan';
import { PageFlip } from 'page-flip';
import { getDocument, GlobalWorkerOptions } from 'pdfjs-dist';
import workerUrl from 'pdfjs-dist/build/pdf.worker.min.mjs?url';

GlobalWorkerOptions.workerSrc = workerUrl;
const root = document.querySelector('[data-pdf-reader]');
const find = name => root.querySelector(`[data-${name}]`);
const stage = find('book-stage');
const viewport = find('viewport');
const loading = find('reader-loading');
const error = find('reader-error');
const events = new AbortController();
const cache = new Map();
let pdf, task, flip, pages = [], rendering, queueRunning = false, disposed = false;
let current = 0, zoom = 1, pageWidth = 400, pageHeight = 566, resizeTimer;
let desired = new Map();
const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;

function on(element, event, handler) {
    element.addEventListener(event, handler, { signal: events.signal });
}
function fail(message) {
    if (disposed) return;
    loading.hidden = true;
    find('error-text').textContent = message;
    error.hidden = false;
}
function controls() {
    if (!flip) return;
    current = flip.getCurrentPageIndex();
    stage.style.left = current === 0 && flip.getOrientation() === 'landscape' ? `${-pageWidth / 2}px` : '0px';
    const spread = flip.getOrientation() === 'landscape' && current > 0 && current < pdf.numPages - 1;
    const end = spread ? Math.min(current + 2, pdf.numPages) : current + 1;
    find('page-status').textContent = `${current + 1}${spread ? `–${end}` : ''} / ${pdf.numPages}`;
    find('page-input').value = current + 1;
    find('prev').disabled = current === 0;
    find('next').disabled = end >= pdf.numPages;
    find('zoom-out').disabled = zoom <= 1;
    find('zoom-in').disabled = zoom >= 2.5;
    find('zoom-label').textContent = `${Math.round(zoom * 100)}%`;
}
function requestPages() {
    if (!pdf || disposed) return;
    // Visible spread plus neighbours; at most eight decoded images, never 193 canvases.
    desired = pageRenderPlan(current, pdf.numPages, pageWidth, devicePixelRatio, viewport.clientWidth < 792);
    for (const [index, entry] of cache) {
        if (!desired.has(index)) {
            pages[index].querySelector('img')?.remove();
            URL.revokeObjectURL(entry.url);
            cache.delete(index);
        }
    }
    void renderQueue();
}
async function renderQueue() {
    if (queueRunning || disposed) return;
    queueRunning = true;
    try {
        while (!disposed) {
            const index = [...desired.keys()].find(n => !cache.has(n) || cache.get(n).width !== desired.get(n));
            if (index === undefined) break;
            const targetWidth = desired.get(index);
            const page = await pdf.getPage(index + 1);
            if (disposed) break;
            const original = page.getViewport({ scale: 1 });
            const view = page.getViewport({ scale: targetWidth / original.width });
            const canvas = document.createElement('canvas');
            canvas.width = Math.ceil(view.width);
            canvas.height = Math.ceil(view.height);
            try {
                rendering = page.render({ canvasContext: canvas.getContext('2d'), viewport: view });
                await rendering.promise;
                if (disposed) break;
                const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/png'));
                if (!blob) throw new Error('Page image unavailable');
                if (disposed || !desired.has(index)) continue;
                const url = URL.createObjectURL(blob);
                const img = new Image();
                img.alt = `Hisnul Muslim, original PDF page ${index + 1}`;
                img.draggable = false;
                img.src = url;
                await img.decode();
                if (disposed || !desired.has(index)) { URL.revokeObjectURL(url); continue; }
                const old = cache.get(index);
                pages[index].querySelector('img')?.remove();
                pages[index].prepend(img);
                cache.set(index, { url, width: targetWidth });
                if (old) URL.revokeObjectURL(old.url);
                if (index === current) loading.hidden = true;
            } finally {
                rendering = undefined;
                canvas.width = canvas.height = 0;
                page.cleanup();
            }
        }
    } catch (exception) {
        if (!disposed && exception.name !== 'RenderingCancelledException') fail('A page could not be rendered. Please try opening the book again.');
    } finally {
        queueRunning = false;
    }
}
function dimensions(ratio) {
    const available = Math.max(250, viewport.clientWidth - 96);
    const wide = available >= 760;
    const height = Math.max(280, viewport.clientHeight - 40);
    pageWidth = Math.floor(Math.min(available / (wide ? 2 : 1), height * ratio, 560) * zoom);
    pageHeight = Math.round(pageWidth / ratio);
    stage.style.width = `${pageWidth * (wide ? 2 : 1)}px`;
    stage.style.height = `${pageHeight}px`;
    return wide;
}
let ratio = 595.32 / 842.04;
function resizeBook() {
    if (!flip || disposed) return;
    dimensions(ratio);
    const settings = flip.getSettings();
    settings.width = pageWidth;
    settings.height = pageHeight;
    const host = stage.querySelector('[data-flip-host]');
    host.style.minWidth = `${pageWidth}px`;
    host.style.minHeight = `${pageHeight}px`;
    host.style.pointerEvents = zoom > 1 ? 'none' : '';
    find('reader-hint').textContent = zoom > 1 ? 'Scroll to explore the enlarged page. Use the arrows to turn pages.' : 'Drag a corner, swipe, or use the arrow keys. Zoom in for smaller text.';
    flip.update();
    controls();
    requestPages();
}
function move(direction) {
    if (!flip || flip.getState() !== 'read') return;
    if (reducedMotion) direction > 0 ? flip.turnToNextPage() : flip.turnToPrevPage();
    else direction > 0 ? flip.flipNext('bottom') : flip.flipPrev('bottom');
}
function destroy() {
    if (disposed) return;
    disposed = true;
    clearTimeout(resizeTimer);
    events.abort();
    rendering?.cancel();
    flip?.destroy();
    for (const entry of cache.values()) URL.revokeObjectURL(entry.url);
    cache.clear();
    void task?.destroy();
    // The containing iframe is removed on close, releasing StPageFlip's animation loop too.
}
function close() { find('close').click(); }

on(find('retry'), 'click', () => location.reload());
on(find('prev'), 'click', () => move(-1));
on(find('next'), 'click', () => move(1));
on(find('zoom-in'), 'click', () => { zoom = Math.min(2.5, zoom + .25); resizeBook(); });
on(find('zoom-out'), 'click', () => { zoom = Math.max(1, zoom - .25); resizeBook(); });
on(find('jump-form'), 'submit', event => {
    event.preventDefault();
    const index = Number(find('page-input').value) - 1;
    if (flip && Number.isInteger(index) && index >= 0 && index < pdf.numPages) {
        flip.turnToPage(index);
        viewport.scrollTop = 0;
        controls();
        requestPages();
    }
});
on(document, 'keydown', event => {
    if (event.key === 'Escape' && !document.fullscreenElement) { close(); return; }
    if (event.target.closest('input, textarea, select, a') || event.altKey || event.ctrlKey || event.metaKey) return;
    if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') {
        event.preventDefault();
        move(event.key === 'ArrowRight' ? 1 : -1);
    }
});
on(find('fullscreen'), 'click', async () => {
    try {
        if (document.fullscreenElement) await document.exitFullscreen();
        else {
            let timeout;
            try {
                await Promise.race([
                    document.documentElement.requestFullscreen(),
                    new Promise((_, reject) => { timeout = setTimeout(() => reject(new Error('Fullscreen unavailable')), 2000); }),
                ]);
                if (!document.fullscreenElement) throw new Error('Fullscreen unavailable');
            } finally { clearTimeout(timeout); }
        }
    } catch {
        find('reader-hint').textContent = 'Fullscreen is unavailable in this browser. The reader already fills the available window.';
    }
});
on(document, 'fullscreenchange', () => {
    find('fullscreen').textContent = document.fullscreenElement ? 'Exit fullscreen' : 'Fullscreen';
    resizeBook();
});
find('fullscreen').hidden = !document.fullscreenEnabled;
on(window, 'resize', () => { clearTimeout(resizeTimer); resizeTimer = setTimeout(resizeBook, 150); });
on(window, 'pagehide', destroy);

async function start() {
    try {
        task = getDocument({ url: root.dataset.pdfUrl, wasmUrl: root.dataset.wasmUrl, isEvalSupported: false });
        pdf = await task.promise;
        if (disposed) return;
        const first = await pdf.getPage(1);
        const size = first.getViewport({ scale: 1 });
        ratio = size.width / size.height;
        first.cleanup();
        find('page-input').max = pdf.numPages;
        dimensions(ratio);
        const host = document.createElement('div');
        host.dataset.flipHost = '';
        stage.append(host);
        pages = Array.from({ length: pdf.numPages }, (_, index) => {
            const page = document.createElement('div');
            page.className = 'pdf-page';
            page.dataset.page = index + 1;
            const placeholder = document.createElement('span');
            placeholder.className = 'pdf-page-placeholder';
            placeholder.textContent = `Loading page ${index + 1}…`;
            page.append(placeholder);
            host.append(page);
            return page;
        });
        flip = new PageFlip(host, {
            width: pageWidth, height: pageHeight, size: 'fixed', showCover: true,
            usePortrait: true, autoSize: false, maxShadowOpacity: .22,
            flippingTime: reducedMotion ? 1 : 650, mobileScrollSupport: false,
            swipeDistance: 30, showPageCorners: !reducedMotion,
        });
        flip.on('flip', () => { controls(); requestPages(); });
        flip.on('changeOrientation', () => { controls(); requestPages(); });
        flip.loadFromHTML(pages);
        controls();
        requestPages();
        viewport.focus();
    } catch {
        fail('The book could not be opened. Check your connection and try again.');
    }
}
void start();
