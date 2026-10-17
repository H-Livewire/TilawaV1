import { test } from 'node:test';
import assert from 'node:assert/strict';
import { spreadStart, bookIndex, pageAtIndex, nearbyPages } from '../../resources/js/mushaf-pagination.js';

test('RTL openings pair every physical page once, with odd pages on the right', () => {
    const seen = [];
    for (let right = 1; right <= 603; right += 2) {
        const left = right + 1;
        assert.equal(bookIndex(left) + 1, bookIndex(right));
        assert.equal(pageAtIndex(bookIndex(left), true), right);
        assert.equal(spreadStart(left), right);
        seen.push(right, left);
    }
    assert.deepEqual(seen, Array.from({ length: 604 }, (_, i) => i + 1));
});
test('single-page jumps are reversible at every page including 1 and 604', () => {
    for (let page = 1; page <= 604; page++) assert.equal(pageAtIndex(bookIndex(page), false), page);
});
test('the cache window contains the visible spread and neighbours within Quran bounds', () => {
    for (let page = 1; page <= 604; page++) {
        const window = nearbyPages(page);
        assert.ok(window.length <= 6);
        assert.ok(window.every(n => n >= 1 && n <= 604));
        assert.ok(window.includes(page));
        assert.ok(window.includes(spreadStart(page)));
        assert.ok(window.includes(spreadStart(page) + 1));
    }
});
