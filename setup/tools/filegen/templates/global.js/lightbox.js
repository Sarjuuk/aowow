var Lightbox = new function()
{
    var overlay,
        outer,
        inner,
        divs = {},

        funcs = {},

        prepared,
        lastId;

    function hookEvents()
    {
        $WH.aE(overlay, 'click', hide);
        $WH.aE(document, 'keydown', onKeyDown);
        $WH.aE(window, 'resize', onResize);
    }

    function unhookEvents()
    {
        $WH.dE(overlay, 'click', hide);
        $WH.dE(document, 'keydown', onKeyDown);
        $WH.dE(window, 'resize', onResize);
    }

    function prepare()
    {
        if (prepared)
            return;

        prepared = 1;

        var dest = document.body;

        overlay = $WH.ce('div');
        overlay.className = 'lightbox-overlay';

        outer = $WH.ce('div');
        outer.className = 'lightbox-outer';

        inner = $WH.ce('div');
        inner.className = 'lightbox-inner';

        overlay.style.display = outer.style.display = 'none';

        $WH.ae(dest, overlay);
        $WH.ae(outer, inner);
        $WH.ae(dest, outer);
    }

    function onKeyDown(e)
    {
        e = $WH.$E(e);
        switch (e.keyCode)
        {
            case 27: // Escape
                hide();
                break;
        }
    }

    function onResize(fake)
    {
        if (fake != 1234)
        {
            if (funcs.onResize)
                funcs.onResize();
        }

        overlay.style.height = document.body.offsetHeight + 'px';
    }

    function hide()
    {
        if (!prepared)
            return;

        unhookEvents();

        if (funcs.onHide)
            funcs.onHide();

        overlay.style.display = outer.style.display = 'none';

        g_enableScroll(true);
    }

    function reveal()
    {
        overlay.style.display = outer.style.display = divs[lastId].style.display = '';
        Lightbox.setSize(inner.offsetWidth, inner.offsetHeight, 1);
    }

    this.setSize = function(w, h, auto)
    {
        inner.style.visibility = 'hidden';

        if (!auto)
        {
            inner.style.width = w + 'px';
            if (h)
                inner.style.height = h + 'px';
        }

        inner.style.left = -parseInt(w / 2) + 'px';
        if (h)
            inner.style.top    = -parseInt(h / 2) + 'px';

        inner.style.visibility = 'visible';
    }

    this.show = function(id, _funcs, opt) {
        funcs = _funcs || {};

        prepare();

        hookEvents();

        if (lastId != id && divs[lastId] != null)
            divs[lastId].style.display = 'none';

        lastId = id;

        var first = 0,
            d;

        if (divs[id] == null)
        {
            first = 1;
            d = $WH.ce('div');
            $WH.ae(inner, d);
            divs[id] = d;
        }
        else
            d = divs[id];

        if (funcs.onShow)
            funcs.onShow(d, first, opt);

        onResize(1234);
        reveal();

        g_enableScroll(false);
    }

    this.reveal = function()
    {
        reveal();
    }

    this.hide = function()
    {
        hide();
    }

    this.isVisible = function()
    {
        return (overlay && overlay.style.display != 'none');
    }
};
