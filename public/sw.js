// Ayah of the Day: shared scheduling/notification code (also loaded by the page).
try { importScripts('/pwa/daily-ayah-core.js?v=2'); } catch { /* notifications simply stay page-driven */ }

const CACHE = 'tilawa-offline-v3';
const ASSETS = ['/offline.html', '/pwa/icon-v2-192.png', '/pwa/icon-v2-512.png', '/pwa/icon-v2-180.png'];

self.addEventListener('install', event => {
    // Apply a new worker right away so fixes (like notification handling) don't wait for every tab to close.
    if (self.skipWaiting) self.skipWaiting();
    event.waitUntil(caches.open(CACHE).then(cache => cache.addAll(ASSETS)));
});

self.addEventListener('activate', event => {
    event.waitUntil(caches.keys().then(keys => Promise.all(
        keys.filter(key => key.startsWith('tilawa-offline-') && key !== CACHE)
            .map(key => caches.delete(key))
    )).then(() => self.clients.claim()));
});

self.addEventListener('fetch', event => {
    const request = event.request;
    const url = new URL(request.url);
    if (request.method !== 'GET' || url.origin !== self.location.origin) return;

    // Account pages, OAuth responses, Livewire requests and Qur'an pages are never cached.
    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.match('/offline.html')));
    } else if (ASSETS.includes(url.pathname)) {
        event.respondWith(caches.match(request).then(cached => cached || fetch(request)));
    }
});

// Best-effort background delivery where the browser supports Periodic Background Sync.
self.addEventListener('periodicsync', event => {
    if (event.tag === 'tilawa-daily-ayah' && self.TilawaDaily) {
        event.waitUntil(self.TilawaDaily.deliver(self.registration));
    }
});

// Tapping the notification opens Tilawa at the ayah (same-origin links only).
self.addEventListener('notificationclick', event => {
    event.notification.close();
    let target = new URL('/daily-ayah', self.location.origin);
    try {
        const requested = new URL((event.notification.data && event.notification.data.url) || '/daily-ayah', self.location.origin);
        if (requested.origin === self.location.origin) target = requested;
    } catch { /* keep the fallback */ }

    event.waitUntil((async () => {
        // openWindow brings Tilawa to the front (a new tab in Chrome, the app window when installed) and
        // loads the ayah. Navigating a background tab, by contrast, can succeed without ever showing it.
        try {
            if (await self.clients.openWindow(target.href)) return;
        } catch { /* fall back to reusing an open window */ }
        const windows = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
        const open = windows.find(client => new URL(client.url).origin === self.location.origin);
        if (open) {
            try {
                const navigated = await open.navigate(target.href);
                await (navigated || open).focus();
            } catch { /* nothing more to try */ }
        }
    })());
});

// Server push (signed-in users): the payload carries the ready-made reminder.
self.addEventListener('push', event => {
    let data = {};
    try { data = event.data ? event.data.json() : {}; } catch { /* show the generic reminder */ }
    const built = self.TilawaDaily
        ? self.TilawaDaily.pushOptions(data)
        : { title: 'Ayah of the Day', options: { body: data.body || '', icon: '/pwa/notification-icon.png', badge: '/pwa/notification-badge.png', tag: 'tilawa-daily-ayah', data: { url: data.url || '/daily-ayah' } } };
    event.waitUntil(self.registration.showNotification(built.title, built.options));
});
