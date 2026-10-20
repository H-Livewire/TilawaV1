// Ayah of the Day — page side: runs the in-app scheduler on every page and
// powers the settings card. Shared logic lives in public/pwa/daily-ayah-core.js
// (also imported by the service worker).

const core = () => window.TilawaDaily;
const supported = () => 'Notification' in window && 'serviceWorker' in navigator && window.isSecureContext;

async function registration() {
    const timeout = new Promise(resolve => setTimeout(() => resolve(null), 4000));
    try {
        return await Promise.race([navigator.serviceWorker.ready, timeout]);
    } catch {
        return null;
    }
}

let timer;
async function schedule() {
    clearTimeout(timer);
    if (!core() || !supported() || Notification.permission !== 'granted') return;
    const prefs = await core().loadPrefs();
    if (!prefs.enabled) return;

    const reg = await registration();
    if (reg) await core().deliver(reg).catch(() => {});

    const delay = core().nextFireTime(prefs, new Date()) - new Date();
    timer = setTimeout(schedule, Math.min(Math.max(delay, 1000), 2 ** 31 - 1));
}

// TEMPORARY testing aid (DAILY_AYAH_TEST_EVERY_MINUTE=true): this device notifies every minute while Tilawa is open.
let testTimer;
window.addEventListener('tilawa-daily-test-mode', event => {
    clearInterval(testTimer);
    if (!event.detail || !core() || !supported()) return;
    testTimer = setInterval(async () => {
        const prefs = await core().loadPrefs();
        if (!prefs.enabled || prefs.push || Notification.permission !== 'granted') return;
        const reg = await registration();
        if (reg) core().deliver(reg, { force: true }).catch(() => {});
    }, 60 * 1000);
});

document.addEventListener('DOMContentLoaded', schedule);
document.addEventListener('visibilitychange', () => { if (!document.hidden) schedule(); });
window.addEventListener('focus', schedule);
window.addEventListener('tilawa-daily-changed', schedule);

document.addEventListener('alpine:init', () => {
    // The reminder is ON by default (7:00 AM, Arabic + translation + reference). The only thing a reader
    // does is allow notifications; "Turn off" is the opt-out.
    Alpine.data('dailyReminder', (ayah, reminder) => ({
        supported: false,
        permission: 'default',
        ready: false,
        busy: false,
        problem: '',
        prefs: { enabled: false, time: '07:00', arabic: false, translation: true, reference: true, simple: false, push: false, optedOut: false, lang: 'en', lastShown: null },
        ayah,
        reminder,

        get active() {
            return this.prefs.enabled && this.permission === 'granted';
        },

        get time() {
            const [h, m] = this.prefs.time.split(':').map(Number);
            return new Date(2000, 0, 1, h, m).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
        },

        async init() {
            this.supported = supported();
            this.permission = this.supported ? Notification.permission : 'unsupported';
            const prefs = await core().loadPrefs();
            Object.assign(prefs, { arabic: false, translation: true, reference: true, simple: false, lang: ayah?.language ?? 'en' });
            if (this.reminder?.saved?.time) prefs.time = this.reminder.saved.time;
            this.prefs = prefs;

            if (this.permission === 'granted' && !prefs.optedOut) {
                // Already allowed on this device: the reminder is simply on.
                this.prefs.enabled = true;
                const existing = this.reminder?.authenticated ? await this.subscription(false) : null;
                const sub = this.reminder?.authenticated ? existing || await this.subscription(true) : null;
                this.prefs.push = !!sub;
                // Only talk to the server when this device is new to it or the account isn't switched on yet.
                if (this.reminder?.authenticated && (!existing || !this.reminder.saved?.enabled)) await this.persist();
                else this.prefs = await core().savePrefs(this.prefs);
                await core().syncPeriodic(await registration(), !this.prefs.push);
            } else {
                this.prefs.enabled = false;
                this.prefs.push = false;
                this.prefs = await core().savePrefs(this.prefs);
            }
            this.ready = true;
            window.dispatchEvent(new CustomEvent('tilawa-daily-test-mode', { detail: !!this.reminder?.testEveryMinute }));
        },

        async persist() {
            this.prefs = await core().savePrefs(this.prefs);
            if (this.reminder?.authenticated) await this.syncServer();
            window.dispatchEvent(new CustomEvent('tilawa-daily-changed'));
        },

        /** This device's push subscription, created on request. Null when push isn't available. */
        async subscription(create) {
            const key = this.reminder?.vapidPublicKey;
            if (!key || !this.supported || Notification.permission !== 'granted') return null;
            const reg = await registration();
            if (!reg?.pushManager) return null;
            try {
                let sub = await reg.pushManager.getSubscription();
                if (!sub && create) {
                    const raw = atob(key.replace(/-/g, '+').replace(/_/g, '/'));
                    sub = await reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: Uint8Array.from(raw, c => c.charCodeAt(0)) });
                }
                return sub;
            } catch {
                return null;
            }
        },

        async api(method, url, body) {
            const response = await fetch(url, {
                method,
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': this.reminder.csrf, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify(body),
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
        },

        /** Save the reminder on the account, with this device's push subscription when it is on. */
        async syncServer() {
            try {
                const sub = this.prefs.enabled ? await this.subscription(false) : null;
                const { enabled, time, arabic, translation, reference, simple } = this.prefs;
                await this.api('PUT', this.reminder.updateUrl, {
                    enabled, time, arabic, translation, reference, simple,
                    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC',
                    subscription: sub ? sub.toJSON() : null,
                });
            } catch {
                this.problem = 'We couldn\'t save your reminder to your account just now. Please try again.';
            }
        },

        async askPermission() {
            if (!this.supported) return 'unsupported';
            if (Notification.permission === 'default') {
                try {
                    // Older Safari only supports the callback form.
                    await new Promise(resolve => {
                        const result = Notification.requestPermission(resolve);
                        if (result && typeof result.then === 'function') result.then(resolve);
                    });
                } catch { /* read the resulting permission below */ }
            }
            this.permission = Notification.permission;
            return this.permission;
        },

        async allow() {
            this.problem = '';
            this.busy = true;
            try {
                if (!this.supported) {
                    this.problem = 'This browser can\'t show notifications here. On iPhone or iPad, add Tilawa to your Home Screen and open it from there.';
                    return;
                }
                if ((await this.askPermission()) !== 'granted') {
                    this.problem = this.permission === 'denied'
                        ? 'Notifications are blocked for Tilawa. Allow them in your browser or device settings, then try again.'
                        : 'No problem — you can allow notifications whenever you like.';
                    return;
                }
                this.prefs.optedOut = false;
                this.prefs.enabled = true;
                const now = new Date();
                // Past today's slot: the first scheduled reminder is tomorrow's.
                this.prefs.lastShown = core().atTime(now, this.prefs.time) <= now ? core().dateKey(now) : null;
                const sub = this.reminder?.authenticated ? await this.subscription(true) : null;
                this.prefs.push = !!sub;
                await this.persist();
                const reg = await registration();
                await core().syncPeriodic(reg, !sub);
                // A first notification right away, so the reader sees what to expect.
                if (reg) await core().show(reg, this.ayah, this.prefs, 'tilawa-daily-ayah-welcome').catch(() => {});
            } finally {
                this.busy = false;
            }
        },

        /** The reader picked a new time. If today's slot has already passed, the first reminder is tomorrow's. */
        async changeTime() {
            this.problem = '';
            if (!/^([01]\d|2[0-3]):[0-5]\d$/.test(this.prefs.time)) this.prefs.time = '07:00';
            const now = new Date();
            this.prefs.lastShown = core().atTime(now, this.prefs.time) <= now ? core().dateKey(now) : null;
            await this.persist();
        },

        async turnOff() {
            this.problem = '';
            this.busy = true;
            try {
                this.prefs.enabled = false;
                this.prefs.push = false;
                this.prefs.optedOut = true;
                await this.persist();
                await core().syncPeriodic(await registration(), false);
                if (this.reminder?.authenticated) {
                    const sub = await this.subscription(false);
                    if (sub) {
                        await this.api('DELETE', this.reminder.removeUrl, { endpoint: sub.endpoint }).catch(() => {});
                        await sub.unsubscribe().catch(() => {});
                    }
                }
            } finally {
                this.busy = false;
            }
        },
    }));
});
