// Protect legacy Ajax, jQuery, raw uploads, and native forms at their shared
// transport boundaries. Never attach the session token to an external origin.
(function () {
    'use strict';

    function token() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    function localUrl(url) {
        try {
            var parsed = new URL(url, document.baseURI);
            return parsed.origin === location.origin ? parsed : null;
        } catch (error) {
            return null;
        }
    }

    function mutation(url) {
        var parsed = localUrl(url);
        if (!parsed) return false;
        var first = parsed.searchParams.entries().next().value;
        if (!first) return false;
        var route = first[0], command = first[1];
        var commands = g_csrfRoutes[route] || [];
        if (commands.indexOf(command) !== -1 || commands.indexOf('*') !== -1) return true;
        return route === 'admin' && ((g_csrfAdminActions[command] || []).indexOf(parsed.searchParams.get('action')) !== -1 ||
            (command === 'announcements' && parsed.searchParams.has('status')));
    }

    function prepare(form) {
        if (!localUrl(form.action)) return;
        if (mutation(form.action)) form.method = 'post';
        if (form.method.toLowerCase() !== 'post') return;
        var field = form.querySelector('input[name="csrfToken"]');
        if (!field) {
            field = document.createElement('input');
            field.type = 'hidden';
            field.name = 'csrfToken';
            form.appendChild(field);
        }
        field.value = token();
    }

    var open = XMLHttpRequest.prototype.open;
    var send = XMLHttpRequest.prototype.send;
    XMLHttpRequest.prototype.open = function (method, url) {
        var args = Array.prototype.slice.call(arguments);
        if (method.toUpperCase() === 'GET' && mutation(url)) args[0] = 'POST';
        this.aowowCsrfPost = args[0].toUpperCase() === 'POST' && !!localUrl(url);
        return open.apply(this, args);
    };
    XMLHttpRequest.prototype.send = function () {
        if (this.aowowCsrfPost) this.setRequestHeader('X-CSRF-Token', token());
        return send.apply(this, arguments);
    };

    // Programmatic submissions do not fire submit events (including iframe uploads).
    var submit = HTMLFormElement.prototype.submit;
    HTMLFormElement.prototype.submit = function () {
        prepare(this);
        return submit.apply(this, arguments);
    };
    document.addEventListener('submit', function (event) { prepare(event.target); }, true);
    document.addEventListener('DOMContentLoaded', function () {
        Array.prototype.forEach.call(document.forms, prepare);
    });

    // Existing confirmation handlers run first; cancelled mass operations stay cancelled.
    document.addEventListener('click', function (event) {
        if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        var link = event.target.closest('a[href]');
        if (!link || !mutation(link.href)) return;
        event.preventDefault();
        var form = document.createElement('form');
        form.action = link.href;
        form.method = 'post';
        form.target = link.target;
        form.hidden = true;
        document.body.appendChild(form);
        form.submit();
        form.remove();
    });
})();
