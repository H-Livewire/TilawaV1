import test from 'node:test';
import assert from 'node:assert/strict';
import vm from 'node:vm';
import fs from 'node:fs';

function worker(fetcher) {
    const handlers = {};
    const writes = [];
    const context = {
        URL, Promise,
        self: {location: {origin: 'https://tilawa.test'}, addEventListener: (name, fn) => handlers[name] = fn},
        fetch: fetcher,
        caches: {match: async () => 'offline page', open: async () => ({addAll: async assets => writes.push(...assets)})},
    };
    vm.runInNewContext(fs.readFileSync('public/sw.js', 'utf8'), context);
    return {handlers, writes};
}

test('online OAuth navigation is fetched and not cached', async () => {
    const {handlers, writes} = worker(async () => 'live callback');
    let response;
    handlers.fetch({request: {url: 'https://tilawa.test/auth/google/callback?code=test', method: 'GET', mode: 'navigate'}, respondWith: value => response = value});
    assert.equal(await response, 'live callback');
    assert.deepEqual(writes, []);
});

test('failed navigation returns the offline page', async () => {
    const {handlers} = worker(async () => { throw new Error('offline'); });
    let response;
    handlers.fetch({request: {url: 'https://tilawa.test/home', method: 'GET', mode: 'navigate'}, respondWith: value => response = value});
    assert.equal(await response, 'offline page');
});

test('Livewire POST requests and external requests are untouched', () => {
    const {handlers} = worker(() => assert.fail('unexpected fetch'));
    for (const [url, method] of [['https://tilawa.test/livewire/update', 'POST'], ['https://accounts.google.com/', 'GET']]) {
        handlers.fetch({request: {url, method}, respondWith: () => assert.fail('intercepted request')});
    }
});

test('manifest icons exist and advertise the required dimensions', () => {
    const manifest = JSON.parse(fs.readFileSync('public/manifest.webmanifest', 'utf8'));
    for (const size of [192, 512]) {
        const icon = manifest.icons.find(icon => icon.sizes === `${size}x${size}`);
        const png = fs.readFileSync(`public${icon.src}`);
        assert.equal(png.readUInt32BE(16), size);
        assert.equal(png.readUInt32BE(20), size);
    }
});
