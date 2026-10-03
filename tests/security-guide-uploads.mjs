// Actual uploader validation and guide callbacks with DOM/transport fixtures; no account or site access.
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';

const read = name => readFileSync(new URL('../static/js/' + name, import.meta.url), 'utf8');
const container = { tag: 'div', children: [] };
class Element {
    constructor(tag) { this[0] = { tag, children: [], attrs: {}, text: '' }; }
    attr(key, value) { Object.assign(this[0].attrs, typeof key === 'object' ? key : { [key]: value }); return this; }
    addClass(value) { this[0].className = value; return this; }
    text(value) { this[0].text = String(value); return this; }
    append(value) { this[0].children.push(value instanceof Element ? value[0] : value); return this; }
    val(value) { this[0].value = value; return this; }
    focus(callback) { this[0].focus = callback; return this; }
    find(selector) {
        const id = selector.replace('input#', '');
        const visit = node => node?.tag === 'input' && String(node.attrs.id) === id ? node : node?.children?.map(visit).find(Boolean);
        const result = new Element('input'); result[0] = visit(this[0]);
        assert.ok(result[0], 'Real callback selects its newly created input'); return result;
    }
}
const result = new Element('div'); result[0] = container;
const context = {
    document: { createElement: () => ({ firstChild: {} }) }, window: {}, navigator: {}, g_staticUrl: 'https://static.example/assets',
    $: selector => {
        if (selector === context.document) return { ready: callback => callback() };
        if (selector === '#upload-result') return result;
        if (selector === '#image-upload') return new Element('div');
        if (/^<\w+>$/.test(selector)) return new Element(selector.slice(1, -1));
        throw new Error('Unexpected DOM selector: ' + selector);
    }
};
runInNewContext(read('fileuploader.js'), context);
let options;
context.qq.FileUploader = function (value) { options = value; };
runInNewContext(read('guide-editing.js'), context);
let checks = 0;
function check(actual, expected, message) { ++checks; assert.equal(actual, expected, message); }
assert.deepEqual(Array.from(options.allowedExtensions), ['jpg', 'jpeg', 'png']); ++checks;
check(options.sizeLimit, 10485760, 'Client uses the server 10 MiB cap');
const validation = Object.create(context.qq.FileUploaderBasic.prototype);
validation._options = options; validation._error = () => {};
for (const [name, size, valid] of [['ok.JPG', 10485760, true], ['ok.png', 10485761, false], ['bad.svg', 10, false], ['empty.jpeg', 0, false]])
    check(validation._validateFile({ name, size }), valid, 'Actual uploader file validation');
const iframeParser = Object.create(context.qq.UploadHandlerForm.prototype);
const payload = JSON.stringify({ success: true, id: 42, type: 3, name: '<img src=x>&filename.png' });
for (const body of [
    { innerHTML: '<pre style="white-space: pre-wrap">escaped HTML</pre>', textContent: payload },
    { innerHTML: '<pre>escaped HTML</pre>', innerText: payload }
]) {
    const parsed = iframeParser._getIframeContentJSON({ contentDocument: { body } });
    check(parsed.id, 42, 'Actual iframe parser reads JSON text through browser PRE wrappers');
    check(parsed.name, '<img src=x>&filename.png', 'Iframe entity/filename text round-trips');
}
check(Object.keys(iframeParser._getIframeContentJSON({ contentDocument: { body: { textContent: '(globalThis.unsafe = true, {})' } } })).length, 0, 'Iframe rejects executable response expressions');
check(context.unsafe, undefined, 'Malformed iframe JSON does not execute');
const xhrParser = Object.create(context.qq.UploadHandlerXhr.prototype);
let xhrResult;
Object.assign(xhrParser, {
    _files: [{}], _xhrs: [{}], getName: () => 'fixture.png', getSize: () => 1,
    log() {}, _dequeue() {},
    _options: { onProgress() {}, onComplete: (id, name, value) => { xhrResult = value; } }
});
xhrParser._onComplete(0, { status: 200, responseText: payload });
check(xhrResult.id, 42, 'Actual XHR completion parses JSON');
check(xhrResult.name, '<img src=x>&filename.png', 'XHR name remains literal data');
xhrParser._files[0] = {};
xhrParser._onComplete(0, { status: 200, responseText: '(globalThis.unsafe = true, {})' });
check(Object.keys(xhrResult).length, 0, 'XHR rejects executable response expressions');
check(context.unsafe, undefined, 'Malformed XHR JSON does not execute');
for (const type of [2, 3]) {
    const name = '<img src=x onerror=attack()>.png';
    options.onComplete(type, 'ignored', { success: true, id: 9007199254740991, type, name });
    const node = container.children.at(-1);
    check(node.children[0].tag, 'b', 'Filename uses a text-bearing element');
    check(node.children[0].text, name, 'Hostile filename stays literal text');
    const input = node.children.find(child => child?.tag === 'input');
    check(input.value, '[img src=https://static.example/assets/uploads/guide/images/9007199254740991.' + (type === 3 ? 'png' : 'jpg') + ']', 'Guide markup URL retains exact safe numeric ID and type');
    check(typeof input.focus, 'function', 'Copy/select behavior is retained');
}
const error = '<img src=x onerror=attack()>';
options.onComplete(7, 'ignored', { error });
check(container.children.at(-1).text, 'Upload failed (' + error + ')', 'Errors are rendered as literal text');
check(container.children.at(-1).children.length, 0, 'Error strings create no markup children');
console.log(`PASS: ${checks} guide uploader JavaScript checks`);
