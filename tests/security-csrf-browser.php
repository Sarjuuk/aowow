<?php

// Real DOM, jQuery, legacy Ajax and CSRF transport; network/form sends are recorded.
echo '<!doctype html><meta charset="utf-8"><meta name="csrf-token" content="'.str_repeat('a', 64).'">';
echo '<title>CSRF compatibility checks</title><pre id="result">RUNNING</pre>';
echo '<script>var g_csrfRoutes = '.json_encode(Aowow\Csrf::POST_ROUTES, JSON_HEX_TAG).';';
echo 'var g_csrfAdminActions = '.json_encode(Aowow\Csrf::ADMIN_ACTIONS, JSON_HEX_TAG).';';
echo <<<'JS'
const xhrSends = [], formSends = [];
class RecordingXHR {
    constructor() { this.headers = {}; this.withCredentials = false; this.upload = {}; }
    open(method, url) { this.method = method; this.url = url; }
    setRequestHeader(name, value) { this.headers[name.toLowerCase()] = value; }
    send(body) { xhrSends.push({method: this.method, url: this.url, headers: this.headers, body}); }
    abort() {}
}
window.XMLHttpRequest = RecordingXHR;
window.$WH = {cO: Object.assign};
HTMLFormElement.prototype.submit = function () {
    formSends.push({method: this.method, action: this.action, target: this.target, fields: Array.from(this.elements, e => [e.name, e.value]), enctype: this.enctype});
};
</script>
JS;
foreach (['static/js/csrf.js', 'static/js/jquery-3.7.0.min.js', 'setup/tools/filegen/templates/global.js/ajax.js'] as $file)
    echo '<script>'.file_get_contents(__DIR__.'/../'.$file).'</script>';
echo <<<'JS'
<form id="original" method="post"><input name="first" value="first"><input name="second" value="second"></form>
<script>
window.addEventListener('load', function () {
    let checks = 0;
    const token = 'a'.repeat(64);
    const check = (condition, message) => { ++checks; if (!condition) throw new Error(message); };
    const last = array => array[array.length - 1];
    try {
        for (const [route, commands] of Object.entries(g_csrfRoutes)) {
            for (const command of commands) {
                const xhr = new XMLHttpRequest();
                xhr.open('GET', '?' + route + '=' + (command === '*' ? '' : command));
                xhr.send();
                check(last(xhrSends).method === 'POST', 'Legacy GET command converts to POST');
                check(last(xhrSends).headers['x-csrf-token'] === token, 'Command carries session header');
            }
        }
        for (const [command, actions] of Object.entries(g_csrfAdminActions)) {
            for (const action of actions) {
                new Ajax('?admin=' + command + '&action=' + action + '&id=1');
                check(last(xhrSends).method === 'POST', 'Legacy moderation Ajax converts to POST');
                check(last(xhrSends).headers['x-csrf-token'] === token, 'Moderation carries session header');
            }
        }
        for (const url of ['?search=text', '?comment=rating&id=1', '?admin=videos&action=list', '?guide=edit&id=1', '?account=confirm-email-address&key=key']) {
            new Ajax(url);
            check(last(xhrSends).method === 'GET' && !last(xhrSends).headers['x-csrf-token'], 'Read-only Ajax stays GET');
        }
        $.get('?comment=vote', {id: 1, rating: 1});
        check(last(xhrSends).method === 'POST' && last(xhrSends).headers['x-csrf-token'] === token, 'Real jQuery vote is protected');
        check(last(xhrSends).url.includes('id=1') && last(xhrSends).url.includes('rating=1'), 'jQuery query selectors are preserved');
        $.ajax({url: '?account=favorites', method: 'POST', data: {add: 3, id: 1}});
        check(last(xhrSends).headers['x-csrf-token'] === token && last(xhrSends).body.includes('add=3'), 'jQuery POST retains body and token');
        new Ajax('?comment=edit&id=1', {params: 'commentbody=markup'});
        check(last(xhrSends).body === 'commentbody=markup' && last(xhrSends).headers['x-csrf-token'] === token, 'Legacy POST retains body');
        const raw = new XMLHttpRequest(), file = new Blob(['image fixture']);
        raw.open('POST', '?edit=image&guide=1&qqfile=fixture.png');
        raw.setRequestHeader('Content-Type', 'application/octet-stream');
        raw.send(file);
        check(last(xhrSends).body === file && last(xhrSends).headers['x-csrf-token'] === token, 'Raw upload carries header without changing body');
        for (const method of ['GET', 'POST']) {
            const external = new XMLHttpRequest();
            external.open(method, 'https://external.example/?account=favorites');
            external.send('body');
            check(last(xhrSends).method === method && !last(xhrSends).headers['x-csrf-token'], 'External requests never receive session tokens');
        }
        const original = document.getElementById('original');
        check(original.elements[0].name === 'first' && original.elements[1].name === 'second', 'Existing numeric form indices are preserved');
        check(original.querySelector('[name=csrfToken]').value === token, 'Initial POST forms receive token');
        original.submit();
        check(last(formSends).fields.some(([name, value]) => name === 'csrfToken' && value === token), 'Programmatic form submit is protected');
        const multipart = document.createElement('form');
        multipart.action = '?edit=image'; multipart.method = 'POST'; multipart.enctype = 'multipart/form-data';
        multipart.innerHTML = '<input type="file" name="qqfile">';
        document.body.appendChild(multipart); multipart.submit();
        check(last(formSends).enctype === 'multipart/form-data' && multipart.elements[0].name === 'qqfile', 'Iframe upload keeps its encoding and control order');
        check(last(formSends).fields.some(([name, value]) => name === 'csrfToken' && value === token), 'Iframe upload receives form token');
        const externalForm = document.createElement('form');
        externalForm.action = 'https://external.example/'; externalForm.method = 'POST'; externalForm.submit();
        check(!last(formSends).fields.some(([name]) => name === 'csrfToken'), 'External forms never receive tokens');
        const native = document.createElement('form'); native.method = 'POST'; document.body.appendChild(native);
        native.addEventListener('submit', event => event.preventDefault());
        native.requestSubmit();
        check(native.querySelector('[name=csrfToken]').value === token, 'Native submit event receives token');
        const link = document.createElement('a'); link.href = '?admin=screenshots&action=delete&id=1'; document.body.appendChild(link);
        const before = formSends.length;
        link.onclick = () => false; link.click();
        check(formSends.length === before, 'Cancelled moderation confirmation does not submit');
        link.onclick = null; link.click();
        check(formSends.length === before + 1 && last(formSends).method === 'post', 'Confirmed mutation link submits protected POST');
        check(last(formSends).fields.some(([name, value]) => name === 'csrfToken' && value === token), 'Mutation link token stays in body');
        document.querySelector('meta[name="csrf-token"]').content = 'b'.repeat(64);
        new Ajax('?cookie=fixture&fixture=value');
        check(last(xhrSends).headers['x-csrf-token'] === 'b'.repeat(64), 'Transport reads the latest rendered token');
        document.getElementById('result').textContent = `PASS: ${checks} CSRF browser checks`;
    } catch (error) {
        document.getElementById('result').textContent = 'FAIL: ' + error.message;
    }
});
</script>
JS;
