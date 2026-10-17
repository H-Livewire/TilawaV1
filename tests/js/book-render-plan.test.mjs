import test from 'node:test';
import assert from 'node:assert/strict';
import { pageRenderPlan } from '../../resources/js/book-render-plan.js';

test('opening and final pages never request outside the PDF', () => {
    assert.deepEqual([...pageRenderPlan(0, 193, 400, 2, true).keys()], [0, 1, 2, 3, 4]);
    assert.deepEqual([...pageRenderPlan(192, 193, 400, 2, true).keys()], [192, 191, 190, 189]);
});
test('mobile zoom bounds memory and gives the visible page priority', () => {
    const plan = pageRenderPlan(90, 193, 1200, 3, true);
    assert.equal(plan.size, 8);
    assert.equal(plan.get(90), 1400);
    assert.equal([...plan.values()].filter(width => width > 640).length, 1);
});
test('desktop spread renders both visible pages at reading resolution', () => {
    const plan = pageRenderPlan(25, 193, 1000, 2, false);
    assert.equal(plan.get(25), 1800);
    assert.equal(plan.get(26), 1800);
    assert.equal(plan.get(24), 640);
});
