// Exercise the real moderation list with a minimal DOM fixture; no site or accounts are accessed.
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';

const element = name => ({ nodeName: name.toUpperCase(), childNodes: [], style: {} });
const table = element('table');
table.childNodes.push(element('tr'));
const context = {
    window: {},
    g_staticUrl: 'https://static.example/assets',
    g_types: { 1: 'npc' },
    g_formatTimeElapsed: () => '1 minute',
    $WH: {
        aE() {},
        ge: () => table,
        ce: element,
        ct: text => ({ textContent: String(text) }),
        ae: (parent, child) => parent.childNodes.push(child)
    }
};
runInNewContext(readFileSync(new URL('../static/js/screenshot.js', import.meta.url), 'utf8'), context);
context.ssm_screenshotData = [
    { id: 7, pending: 1, status: 0 },
    { id: 8, pending: 0, status: 999 },
    { id: 9, pending: 0, status: 100 },
    { id: 10, pending: 0, status: 105 }
].map(row => ({ ...row, type: 1, typeId: 1, user: 'fixture', date: '2026-10-02' }));
context.ssm_UpdateList(false);
let checks = 0;
for (let i = 0; i < context.ssm_screenshotData.length; ++i) {
    const row = context.ssm_screenshotData[i];
    const anchor = table.childNodes[i + 1].childNodes[0].childNodes[0];
    const privateUrl = '?upload=preview&kind=pending&id=' + row.id;
    assert.equal(anchor.href, i < 2 ? privateUrl : context.g_staticUrl + '/uploads/screenshots/normal/' + row.id + '.jpg');
    assert.equal(anchor.childNodes[0].src, i < 2 ? privateUrl : context.g_staticUrl + '/uploads/screenshots/thumb/' + row.id + '.jpg');
    checks += 2;
}
console.log(`PASS: ${checks} moderation list URL checks`);
