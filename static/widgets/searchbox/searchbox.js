var SearchBox = new function () {
    function bindEvents() {
        $("form.search").submit(onSubmit);
        $("form.search a").attr("href", "javascript:;").click(onClick);
    }

    function onSubmit() {
        var val = this.elements.search.value;
        if (!$.trim(val)) {
            return false;
        }
    }

    function onClick() {
        $("form.search").submit();
        return false;
    }

    $(document).ready(bindEvents);
};

if (!Function.prototype.bind) {
    Function.prototype.bind = function () {
        var
            c = this,
            a = $WH.$A(arguments),
            b = a.shift();

        return function () {
            return c.apply(b, a.concat($WH.$A(arguments)))
        };
    }
}

function isset(a) {
    return typeof window[a] != "undefined";
}
