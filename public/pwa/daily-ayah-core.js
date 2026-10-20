/*
 * Ayah of the Day — shared scheduling/notification core.
 *
 * A plain script on purpose: the page loads it with a <script> tag and the
 * service worker loads the very same file with importScripts(), so both build
 * and show the notification identically. Exposes `TilawaDaily` (or
 * module.exports under Node, for tests). The verse text always comes from the
 * server's /daily-ayah/today endpoint; nothing here writes or edits Qur'an text.
 */
(function (root, factory) {
    const api = factory(root);
    if (typeof module === 'object' && module.exports) module.exports = api;
    else root.TilawaDaily = api;
})(typeof self !== 'undefined' ? self : globalThis, function (root) {
    const TAG = 'tilawa-daily-ayah';
    const DB_NAME = 'tilawa-daily';
    const CATCH_UP_MS = 12 * 60 * 60 * 1000;
    const DEFAULTS = Object.freeze({
        enabled: false,
        time: '07:00',
        arabic: true,
        translation: true,
        reference: true,
        simple: false,
        optedOut: false, // the reader turned the reminder off on this device
        push: false, // true when the server sends this device's reminder (signed-in users)
        lang: 'en',
        lastShown: null,
    });

    const pad = n => String(n).padStart(2, '0');
    const dateKey = (date = new Date()) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
    const validTime = time => typeof time === 'string' && /^([01]\d|2[0-3]):[0-5]\d$/.test(time);

    function normalize(raw) {
        const prefs = { ...DEFAULTS, ...(raw && typeof raw === 'object' ? raw : {}) };
        prefs.enabled = prefs.enabled === true;
        prefs.push = prefs.push === true;
        prefs.optedOut = prefs.optedOut === true;
        if (!validTime(prefs.time)) prefs.time = DEFAULTS.time;
        for (const key of ['arabic', 'translation', 'reference', 'simple']) prefs[key] = prefs[key] === true;
        // Always have something to show: with nothing picked it is a simple reminder.
        if (!prefs.arabic && !prefs.translation && !prefs.reference) prefs.simple = true;
        if (!['en', 'ar', 'sw'].includes(prefs.lang)) prefs.lang = 'en';
        if (typeof prefs.lastShown !== 'string') prefs.lastShown = null;
        return prefs;
    }

    function atTime(day, time) {
        const [hours, minutes] = time.split(':').map(Number);
        const at = new Date(day.getFullYear(), day.getMonth(), day.getDate(), hours, minutes, 0, 0);
        return at;
    }

    /** Next moment the notification should fire: today at the chosen time, else tomorrow. */
    function nextFireTime(prefs, now = new Date()) {
        const today = atTime(now, prefs.time);
        if (today > now) return today;
        return atTime(new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1), prefs.time);
    }

    /** True when today's notification is owed: past its time, within the catch-up window, not shown yet. */
    function isDue(prefs, now = new Date()) {
        if (!prefs.enabled || prefs.lastShown === dateKey(now)) return false;
        const elapsed = now - atTime(now, prefs.time);
        return elapsed >= 0 && elapsed <= CATCH_UP_MS;
    }

    /** Title, body and deep link for the notification, honouring the user's content choices. */
    function compose(ayah, prefs) {
        const title = 'Ayah of the Day';
        if (!ayah) return { title, body: 'Take a moment to read today\'s ayah.', url: '/daily-ayah' };
        const url = ayah.url || '/daily-ayah';
        if (prefs.simple) return { title, body: 'Your daily ayah is ready. Take a moment to read it.', url };
        const lines = [];
        if (prefs.arabic) lines.push(ayah.arabic);
        if (prefs.translation) lines.push(prefs.arabic ? ayah.translation : `“${ayah.translation}”`);
        if (prefs.reference) lines.push(`— ${ayah.reference}`);
        return { title, body: lines.join('\n'), url };
    }

    function notificationOptions(ayah, prefs, tag = TAG) {
        const { body, url } = compose(ayah, prefs);
        return {
            body,
            icon: '/pwa/notification-icon.png',
            badge: '/pwa/notification-badge.png',
            tag,
            lang: prefs.arabic && !prefs.translation && !prefs.simple ? 'ar' : prefs.lang,
            dir: 'auto',
            data: { url, date: ayah ? ayah.date : dateKey() },
            actions: [{ action: 'read', title: 'Read ayah' }],
        };
    }

    // ---- storage: IndexedDB so the service worker can read the same settings ----
    let memory = null;
    function openDb() {
        return new Promise((resolve, reject) => {
            if (!root.indexedDB) return reject(new Error('IndexedDB unavailable'));
            const request = root.indexedDB.open(DB_NAME, 1);
            request.onupgradeneeded = () => request.result.createObjectStore('kv');
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }
    async function loadPrefs() {
        try {
            const db = await openDb();
            const value = await new Promise((resolve, reject) => {
                const get = db.transaction('kv').objectStore('kv').get('prefs');
                get.onsuccess = () => resolve(get.result);
                get.onerror = () => reject(get.error);
            });
            db.close();
            return normalize(value);
        } catch {
            return normalize(memory);
        }
    }
    async function savePrefs(prefs) {
        const clean = normalize(prefs);
        memory = clean;
        try {
            const db = await openDb();
            await new Promise((resolve, reject) => {
                const tx = db.transaction('kv', 'readwrite');
                tx.objectStore('kv').put(clean, 'prefs');
                tx.oncomplete = resolve;
                tx.onerror = () => reject(tx.error);
            });
            db.close();
        } catch { /* kept in memory for this session only */ }
        return clean;
    }

    async function fetchAyah(key, lang, fetcher = root.fetch.bind(root)) {
        try {
            const response = await fetcher(`/daily-ayah/today?date=${encodeURIComponent(key)}&lang=${encodeURIComponent(lang)}`, { headers: { Accept: 'application/json' } });
            if (!response.ok) return null;
            const data = await response.json();
            return data && typeof data.arabic === 'string' && typeof data.url === 'string' ? data : null;
        } catch {
            return null;
        }
    }

    /** Title and options for a notification that arrived as a server push payload. */
    function pushOptions(data) {
        const url = typeof data.url === 'string' && data.url.startsWith('/') ? data.url : '/daily-ayah';
        return {
            title: typeof data.title === 'string' ? data.title : 'Ayah of the Day',
            options: {
                body: typeof data.body === 'string' ? data.body : '',
                icon: '/pwa/notification-icon.png',
                badge: '/pwa/notification-badge.png',
                tag: TAG,
                lang: typeof data.lang === 'string' ? data.lang : 'en',
                dir: 'auto',
                data: { url },
                actions: [{ action: 'read', title: 'Read ayah' }],
            },
        };
    }

    function show(registration, ayah, prefs, tag = TAG) {
        const { title } = compose(ayah, prefs);
        return registration.showNotification(title, notificationOptions(ayah, prefs, tag));
    }

    /**
     * Show today's notification if it is due. Safe to call from the page (timer,
     * focus, startup) and from the service worker (periodic sync): lastShown
     * guarantees at most one per day.
     */
    async function deliver(registration, { now = new Date(), force = false, fetcher } = {}) {
        const prefs = await loadPrefs();
        if (!force && prefs.push) return 'push-managed'; // the server sends this device's reminder
        if (!force && !isDue(prefs, now)) return 'skipped';
        if (root.Notification && root.Notification.permission !== 'granted') return 'no-permission';
        const key = dateKey(now);
        const ayah = await fetchAyah(key, prefs.lang, fetcher);
        await show(registration, ayah, prefs);
        await savePrefs({ ...(await loadPrefs()), lastShown: key });
        return 'shown';
    }

    async function syncPeriodic(registration, enabled) {
        try {
            if (!registration || !registration.periodicSync) return false;
            if (enabled) await registration.periodicSync.register(TAG, { minInterval: 12 * 60 * 60 * 1000 });
            else await registration.periodicSync.unregister(TAG);
            return true;
        } catch {
            return false;
        }
    }

    return { TAG, DEFAULTS, CATCH_UP_MS, dateKey, normalize, atTime, nextFireTime, isDue, compose, notificationOptions, pushOptions, loadPrefs, savePrefs, fetchAyah, show, deliver, syncPeriodic };
});
