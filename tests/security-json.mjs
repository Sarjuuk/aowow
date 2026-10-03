// Feed fixtures from `php tests/security-json.php --fixtures` into this script.
import assert from 'node:assert/strict';
import { runInNewContext } from 'node:vm';

let input = '';
for await (const chunk of process.stdin) input += chunk;
const { scripts, payload, htmlPayload } = JSON.parse(input);
function createContext(recordings) {
    const { listviews, tabs, markups, summaries, tooltips } = recordings;
    function Listview(options) { listviews.push(options); }
    Listview.extraCols = { date: { id: 'date' } };
    Listview.funcBox = { addModeIndicator() {} };
    function Tabs(options) { this.options = options; this.added = []; tabs.push(this); }
    Tabs.prototype.add = function (name, options) { this.added.push({ name, options }); };
    return {
        aowowSecurityMarker: 0,
        LANG: { tab_items: 'Items' },
        LOCALE_ENUS: 0,
        Listview, Tabs,
        Summary: function (options) { summaries.push(options); },
        Markup: { MODE_COMMENT: 1, CLASS_USER: 10, printHtml: (...args) => markups.push(args) },
        $WH: { ge: id => ({ id }), sprintf: (...args) => args, g_getProfileIcon: () => 'profile-icon' },
        $WowheadPower: { registerProfile: (...args) => tooltips.push(args) },
        g_statistics: { combo: { 1: { 1: [0, 0, 0, 0, 0, 200] } } },
        PageTemplate: { init() {} }
    };
}
function verify(context, recordings, payload, htmlPayload, equal) {
    const { listviews, tabs, markups, summaries, tooltips } = recordings;
    const Listview = context.Listview;
    equal(context.mixed.label, 'Items', 'Trusted locale reference');
    equal(context.mixed.callback(), 7, 'Trusted function expression');
    equal(context.mixed.data, payload, 'Dollar-prefixed text');
    equal(context.mixed.talents, '0000123', 'Leading-zero string');
    equal(context.mixed.guild, htmlPayload, 'Literal escaping preserves text');
    equal([...context.callResult], ['Items', htmlPayload, payload], 'Function call arguments remain data');
    equal(listviews[0].name, 'Items', 'Default listview locale');
    equal(listviews[0].data[0].name, payload, 'User listview name');
    equal(listviews[0].data[0].title, htmlPayload, 'User listview title');
    equal(listviews[0].extraCols[0], Listview.extraCols.date, 'Column object reference');
    equal(listviews[0].onAfterCreate, Listview.funcBox.addModeIndicator, 'Callback reference');
    equal(listviews[1].name, payload, 'Custom tab name remains data');
    equal(listviews[1].note, htmlPayload, 'Custom note remains data');
    equal(tabs[0].options.parent.id, 'tabs-generic', 'Tabs DOM reference');
    equal(listviews[2].tabs, tabs[0], 'Listview uses the actual Tabs instance');
    equal(tabs[0].added[0].name, payload, 'Data-tab title remains data');
    equal(markups[0][0], payload, 'Markup body remains data');
    equal(markups[0][2].mode, 1, 'Markup mode reference');
    equal(markups[0][2].allow, 10, 'Markup permission reference');
    equal(markups[0][2].prepend, payload, 'Markup prepend remains data');
    equal(markups[0][2].append, htmlPayload, 'Markup append remains data');
    equal(summaries[0].groups[0].name, payload, 'Summary group labels remain data');
    equal(tooltips[0][0], 'profile', 'Tooltip subject');
    equal(tooltips[0][2].name_enus, payload, 'Tooltip name remains data');
    equal(tooltips[0][2].tooltip_enus, htmlPayload, 'Tooltip HTML remains data');
    equal(tooltips[0][2].icon, 'profile-icon', 'Tooltip icon call');
    equal(context.profile.guild, '123', 'Numeric guild name remains a string');
    equal(context.profile.talents, '000123', 'Profiler talents remain a string');
    equal(context.profile.achievements[1].getTime(), 1000, 'Profiler Date object');
    equal(context.profile.health[2]({ classs: 1, level: 1 }), 200, 'Profiler scaling function');
    equal(context.locales[0].id, 0, 'Generated locale constant');
    equal(context.lv_comments[0].body, payload, 'Real rendered community comment remains data');
    equal(context.lv_comments[0].replies[0].body, htmlPayload, 'Real rendered reply remains data');
    equal(context.lv_comments[0].user, '123', 'Community username remains a string');
    equal(context.lv_screenshots[0].caption, payload, 'Screenshot caption remains data');
    equal(context.lv_videos[0].caption, htmlPayload, 'Video caption remains data');
    equal(context.templateData.title, payload, 'PageTemplate guide title remains data');
    equal(context.templateData.map.name, htmlPayload, 'PageTemplate map label remains data');
    equal(context.templateData.icon, 'Items', 'PageTemplate explicit expression');
}
const recordings = { listviews: [], tabs: [], markups: [], summaries: [], tooltips: [] };
let checks = 0;
function equal(actual, expected, message) { ++checks; assert.deepEqual(actual, expected, message); }
if (process.argv.includes('--browser')) {
    const jsData = value => JSON.stringify(value).replaceAll('<', '\\u003c').replaceAll('>', '\\u003e');
    const bootstrap = `${createContext.toString()}
${verify.toString()}
` +
        `const recordings = { listviews: [], tabs: [], markups: [], summaries: [], tooltips: [] };
` +
        `Object.assign(window, createContext(recordings));
` +
        `const payload = ${jsData(payload)}, htmlPayload = ${jsData(htmlPayload)};
` +
        `let checks = 0; function equal(a, b, message) { ++checks; if (a !== b && !(a && b && typeof a === 'object' && typeof b === 'object' && JSON.stringify(a) === JSON.stringify(b))) throw new Error(message); }
` +
        `window.onerror = message => { document.getElementById('result').textContent = 'FAIL: ' + message; };
`;
    let html = '<!doctype html><html><body><pre id="result">PENDING</pre><script>' + bootstrap + '</script>';
    for (const [name, script] of Object.entries(scripts)) {
        assert(!/<\/script|<!--/i.test(script), `${name}: unsafe HTML script delimiter`);
        html += `<script>${script}
equal(window.aowowSecurityMarker, 0, ${jsData(name)});</script>`;
    }
    html += `<script>verify(window, recordings, payload, htmlPayload, equal);
` +
        `document.getElementById('result').textContent = 'PASS: ' + checks + ' browser checks';</script></body></html>`;
    process.stdout.write(html);
} else {
    const context = createContext(recordings);
    for (const [name, script] of Object.entries(scripts)) {
        runInNewContext(script, context, { timeout: 1000 });
        equal(context.aowowSecurityMarker, 0, `${name}: data must not execute`);
    }
    verify(context, recordings, payload, htmlPayload, equal);
    process.stdout.write(`PASS: ${checks} JavaScript security and compatibility checks.\n`);
}
