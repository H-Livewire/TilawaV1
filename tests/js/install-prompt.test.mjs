import test from 'node:test';
import assert from 'node:assert/strict';
import vm from 'node:vm';
import fs from 'node:fs';

function setup({ installed = false, dismissed = 0 } = {}) {
    const listeners = {};
    const classes = new Set();
    const banner = {hidden: true, classList: {add: name => classes.add(name), remove: name => classes.delete(name)}};
    const button = {hidden: true};
    const storage = new Map([['tilawa-install-dismissed-until', String(dismissed)]]);
    class Element { constructor(selector) { this.selector = selector; } closest(selector) { return selector === this.selector ? this : null; } }
    const context = {
        navigator: {serviceWorker: {register: async () => {}}},
        window: {isSecureContext: true, matchMedia: () => ({matches: installed}), addEventListener: (name, fn) => listeners[name] = fn},
        document: {getElementById: () => banner, querySelectorAll: () => [button], addEventListener: (name, fn) => listeners[name] = fn},
        localStorage: {getItem: key => storage.get(key), setItem: (key, value) => storage.set(key, value)},
        Element, Date, Number, String,
        requestAnimationFrame: fn => { fn(); return 1; }, cancelAnimationFrame() {},
        setTimeout: fn => { fn(); return 1; }, clearTimeout() {},
    };
    vm.runInNewContext(fs.readFileSync('public/pwa/register.js', 'utf8'), context);
    return {listeners, banner, button, classes, storage, click: selector => listeners.click({target: new Element(selector)})};
}

test('banner waits for install eligibility, and calls the browser prompt only on click', async () => {
    const state = setup();
    assert.equal(state.banner.hidden, true);
    let calls = 0;
    state.listeners.beforeinstallprompt({preventDefault() {}, prompt: async () => calls++, userChoice: Promise.resolve({outcome: 'accepted'})});
    assert.equal(state.banner.hidden, false);
    assert.ok(state.classes.has('is-visible'));
    assert.equal(calls, 0);
    await state.click('[data-install-tilawa]');
    assert.equal(calls, 1);
    assert.equal(state.banner.hidden, true);
});

test('dismissal lasts through navigation and keeps manual install available', async () => {
    const state = setup();
    state.listeners.beforeinstallprompt({preventDefault() {}});
    await state.click('[data-dismiss-tilawa]');
    state.listeners['livewire:navigated']();
    assert.equal(state.banner.hidden, true);
    assert.equal(state.button.hidden, false);
    assert.ok(Number(state.storage.get('tilawa-install-dismissed-until')) > Date.now());
});

test('installed apps and previously dismissed banners do not pop up', () => {
    for (const options of [{installed: true}, {dismissed: Date.now() + 60000}]) {
        const state = setup(options);
        state.listeners.beforeinstallprompt({preventDefault() {}});
        assert.equal(state.banner.hidden, true);
    }
});
