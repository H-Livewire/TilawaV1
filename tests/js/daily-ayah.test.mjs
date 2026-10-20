import test from 'node:test';
import assert from 'node:assert/strict';
import vm from 'node:vm';
import fs from 'node:fs';

const ayah = {
    date: '2026-10-20', surah: 2, ayah: 255, surahName: 'Al-Baqarah', reference: 'Al-Baqarah 2:255',
    arabic: 'ٱللَّهُ لَآ إِلَٰهَ إِلَّا هُوَ', translation: 'Allah - there is no deity except Him.', url: '/surah/2?ayah=255#ayah-255',
};

function load(permission = 'granted') {
    const module = { exports: {} };
    const root = { Notification: { permission }, fetch: async () => ({ ok: true, json: async () => ayah }) };
    vm.runInNewContext(fs.readFileSync('public/pwa/daily-ayah-core.js', 'utf8'), { module, self: root, globalThis: root, Date, Promise, encodeURIComponent, Intl });
    return module.exports;
}

const at = (h, m = 0, day = 20) => new Date(2026, 9, day, h, m, 0, 0);
const registration = () => {
    const shown = [];
    return { shown, showNotification: async (title, options) => shown.push({ title, options }) };
};

test('defaults: disabled, 7:00 AM, all content shown', () => {
    const D = load();
    const prefs = D.normalize(undefined);
    assert.equal(prefs.enabled, false);
    assert.equal(prefs.time, '07:00');
    assert.ok(!prefs.arabic && prefs.translation && prefs.reference && !prefs.simple);
});

test('invalid saved values fall back safely; empty content becomes a simple reminder', () => {
    const D = load();
    assert.equal(D.normalize({ time: '25:99' }).time, '07:00');
    assert.equal(D.normalize({ arabic: false, translation: false, reference: false }).simple, true);
});

test('next fire time is today before the chosen time and tomorrow after it', () => {
    const D = load();
    const prefs = D.normalize({ time: '07:00' });
    assert.deepEqual(D.nextFireTime(prefs, at(6, 59)), at(7));
    assert.deepEqual(D.nextFireTime(prefs, at(7)), at(7, 0, 21));
    assert.deepEqual(D.nextFireTime(prefs, at(23, 30)), at(7, 0, 21));
});

test('due only when enabled, past the time, inside the catch-up window and not yet shown today', () => {
    const D = load();
    const prefs = D.normalize({ enabled: true, time: '07:00' });
    assert.equal(D.isDue(prefs, at(6, 59)), false);
    assert.equal(D.isDue(prefs, at(7, 1)), true);
    assert.equal(D.isDue(prefs, at(19, 1)), false);
    assert.equal(D.isDue({ ...prefs, lastShown: '2026-10-20' }, at(8)), false);
    assert.equal(D.isDue({ ...prefs, enabled: false }, at(8)), false);
});

test('content choices decide what the notification says', () => {
    const D = load();
    const all = D.compose(ayah, D.normalize({ arabic: true }));
    assert.equal(all.title, `Ayah of the Day · ${ayah.reference}`);
    assert.equal(all.body, `${ayah.translation}\n${ayah.arabic}`);
    assert.equal(all.url, '/surah/2?ayah=255#ayah-255');

    const arabicOnly = D.compose(ayah, D.normalize({ arabic: true, translation: false, reference: false }));
    assert.equal(arabicOnly.body, ayah.arabic);
    assert.equal(arabicOnly.title, 'Ayah of the Day');
    assert.equal(D.compose(ayah, D.normalize({ arabic: false, reference: false })).body, ayah.translation);
    const referenceOnly = D.compose(ayah, D.normalize({ arabic: false, translation: false }));
    assert.equal(referenceOnly.title, `Ayah of the Day · ${ayah.reference}`);
    assert.ok(referenceOnly.body.length > 0);

    const simple = D.compose(ayah, D.normalize({ simple: true }));
    assert.ok(!simple.body.includes(ayah.arabic) && !simple.title.includes(ayah.reference));
    assert.equal(simple.url, '/surah/2?ayah=255#ayah-255');
});

test('without ayah data (offline) the notification is a simple reminder that opens the daily page', () => {
    const D = load();
    const composed = D.compose(null, D.normalize({}));
    assert.equal(composed.url, '/daily-ayah');
    assert.ok(composed.body.length > 0);
});

test('notification uses the Tilawa logo icon and carries the deep link', () => {
    const D = load();
    const options = D.notificationOptions(ayah, D.normalize({}));
    assert.equal(options.icon, '/pwa/notification-icon.png');
    assert.equal(options.badge, '/pwa/notification-badge.png');
    assert.equal(options.data.url, '/surah/2?ayah=255#ayah-255');
    assert.equal(options.tag, 'tilawa-daily-ayah');
});

test('Android shows only the small badge on the left; other platforms keep the large logo', () => {
    const module = { exports: {} };
    const root = { navigator: { userAgent: 'Mozilla/5.0 (Linux; Android 14; SM-A556E) Chrome/120' }, Notification: { permission: 'granted' } };
    vm.runInNewContext(fs.readFileSync('public/pwa/daily-ayah-core.js', 'utf8'), { module, self: root, globalThis: root, Date, Promise, encodeURIComponent, Intl });
    const D = module.exports;
    assert.equal(D.notificationOptions(ayah, D.normalize({})).icon, '/pwa/notification-blank.png');
    assert.equal(D.notificationOptions(ayah, D.normalize({})).badge, '/pwa/notification-badge.png');
    assert.equal(D.pushOptions({ title: 'x', body: 'y', url: '/a' }).options.icon, '/pwa/notification-blank.png');
    assert.equal(load().notificationOptions(ayah, load().normalize({})).icon, '/pwa/notification-icon.png');
});

test('deliver shows one notification per day', async () => {
    const D = load();
    await D.savePrefs({ enabled: true, time: '07:00' });
    const reg = registration();
    assert.equal(await D.deliver(reg, { now: at(7, 5) }), 'shown');
    assert.equal(await D.deliver(reg, { now: at(7, 6) }), 'skipped');
    assert.equal(reg.shown.length, 1);
    assert.equal(reg.shown[0].title, `Ayah of the Day · ${ayah.reference}`);
    assert.equal((await D.loadPrefs()).lastShown, '2026-10-20');
    assert.equal(await D.deliver(reg, { now: at(7, 5, 21) }), 'shown');
});

test('deliver does nothing before the time, when disabled, or without permission', async () => {
    let D = load();
    await D.savePrefs({ enabled: true, time: '07:00' });
    assert.equal(await D.deliver(registration(), { now: at(6) }), 'skipped');

    D = load();
    await D.savePrefs({ enabled: false });
    assert.equal(await D.deliver(registration(), { now: at(8) }), 'skipped');

    D = load('denied');
    await D.savePrefs({ enabled: true, time: '07:00' });
    const reg = registration();
    assert.equal(await D.deliver(reg, { now: at(8) }), 'no-permission');
    assert.equal(reg.shown.length, 0);
});

test('a failed fetch still delivers a simple reminder', async () => {
    const D = load();
    await D.savePrefs({ enabled: true, time: '07:00' });
    const reg = registration();
    await D.deliver(reg, { now: at(8), fetcher: async () => { throw new Error('offline'); } });
    assert.equal(reg.shown.length, 1);
    assert.equal(reg.shown[0].options.data.url, '/daily-ayah');
});

test('the endpoint is asked for the device\'s local date and language', async () => {
    const D = load();
    let requested;
    await D.fetchAyah('2026-10-20', 'sw', async url => { requested = url; return { ok: true, json: async () => ayah }; });
    assert.equal(requested, '/daily-ayah/today?date=2026-10-20&lang=sw');
});

// ---- service worker wiring ----
function worker({ clients = [], core = true, opened = {} } = {}) {
    const handlers = {};
    const calls = { opened: [], delivered: 0 };
    const self = {
        location: { origin: 'https://tilawa.test' },
        addEventListener: (name, fn) => { handlers[name] = fn; },
        registration: { id: 'reg' },
        clients: {
            matchAll: async () => clients,
            openWindow: async url => { calls.opened.push(url); return opened; },
        },
        TilawaDaily: core ? { deliver: async () => { calls.delivered++; } } : undefined,
    };
    vm.runInNewContext(fs.readFileSync('public/sw.js', 'utf8'), { self, URL, Promise, caches: {}, fetch: async () => {}, importScripts: () => {} });
    return { handlers, calls, self };
}

const click = (handlers, url) => {
    let closed = false, work;
    handlers.notificationclick({ notification: { data: url === undefined ? undefined : { url }, close: () => { closed = true; } }, waitUntil: p => { work = p; } });
    return { closed: () => closed, work };
};

test('tapping the notification opens the ayah in a new window when Tilawa is closed', async () => {
    const { handlers, calls } = worker();
    const tap = click(handlers, '/surah/2?ayah=255');
    await tap.work;
    assert.ok(tap.closed());
    assert.deepEqual(calls.opened, ['https://tilawa.test/surah/2?ayah=255']);
});

test('tapping the notification brings Tilawa to the front even when a tab is already open', async () => {
    let navigated = false;
    const client = { url: 'https://tilawa.test/home', navigate: async () => { navigated = true; return client; }, focus: async () => {} };
    const { handlers, calls } = worker({ clients: [client] });
    await click(handlers, '/surah/36?ayah=1').work;
    assert.deepEqual(calls.opened, ['https://tilawa.test/surah/36?ayah=1']);
    assert.equal(navigated, false);
});

test('if a new window is refused, the open Tilawa window is navigated and focused instead', async () => {
    let navigatedTo, focused = false;
    const client = { url: 'https://tilawa.test/home', navigate: async url => { navigatedTo = url; return client; }, focus: async () => { focused = true; } };
    const { handlers } = worker({ clients: [client], opened: null });
    await click(handlers, '/surah/36?ayah=1').work;
    assert.equal(navigatedTo, 'https://tilawa.test/surah/36?ayah=1');
    assert.ok(focused);
});

test('notification links to other sites are ignored', async () => {
    const { handlers, calls } = worker();
    await click(handlers, 'https://evil.example/phish').work;
    assert.deepEqual(calls.opened, ['https://tilawa.test/daily-ayah']);
});

test('periodic background sync delivers the daily ayah', async () => {
    const { handlers, calls } = worker();
    let work;
    handlers.periodicsync({ tag: 'tilawa-daily-ayah', waitUntil: p => { work = p; } });
    await work;
    assert.equal(calls.delivered, 1);
    handlers.periodicsync({ tag: 'other', waitUntil: () => assert.fail('wrong tag') });
});

// ---- server push (signed-in users) ----
test('devices whose reminder is sent by the server do not also fire a local one', async () => {
    const D = load();
    await D.savePrefs({ enabled: true, time: '07:00', push: true });
    const reg = registration();
    assert.equal(await D.deliver(reg, { now: at(8) }), 'push-managed');
    assert.equal(reg.shown.length, 0);
});

test('a push payload becomes a Tilawa-branded notification with a same-site link only', () => {
    const D = load();
    const built = D.pushOptions({ title: 'Ayah of the Day', body: 'x', url: '/surah/2?ayah=255', lang: 'en' });
    assert.equal(built.options.icon, '/pwa/notification-icon.png');
    assert.equal(built.options.data.url, '/surah/2?ayah=255');
    assert.equal(D.pushOptions({ url: 'https://evil.example' }).options.data.url, '/daily-ayah');
    assert.equal(D.pushOptions({}).title, 'Ayah of the Day');
});

test('the service worker shows a notification when a push arrives', async () => {
    const shown = [];
    const handlers = {};
    const core = load();
    const self = {
        location: { origin: 'https://tilawa.test' }, addEventListener: (n, fn) => { handlers[n] = fn; },
        registration: { showNotification: async (title, options) => shown.push({ title, options }) },
        clients: {}, TilawaDaily: core,
    };
    vm.runInNewContext(fs.readFileSync('public/sw.js', 'utf8'), { self, URL, Promise, caches: {}, fetch: async () => {}, importScripts: () => {} });
    let work;
    handlers.push({ data: { json: () => ({ title: 'Ayah of the Day', body: 'ARABIC', url: '/surah/2?ayah=255' }) }, waitUntil: p => { work = p; } });
    await work;
    assert.equal(shown.length, 1);
    assert.equal(shown[0].options.body, 'ARABIC');
    assert.equal(shown[0].options.data.url, '/surah/2?ayah=255');

    // Even a payload that can't be read still shows the reminder (browsers require a notification per push).
    handlers.push({ data: { json: () => { throw new Error('bad'); } }, waitUntil: p => { work = p; } });
    await work;
    assert.equal(shown.length, 2);
});
