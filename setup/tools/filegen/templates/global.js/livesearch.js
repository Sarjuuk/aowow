var LiveSearch = new function()
{
    var currentTextbox,
        lastSearch = {},
        lastDiv,
        timer,
        prepared,
        container,
        cancelNext,
        hasData,
        summary,
        selection,

        LIVESEARCH_DELAY = 500; // In milliseconds;

    function setText(textbox, txt)
    {
        textbox.value = txt;
        textbox.selectionStart = textbox.selectionEnd = txt.length;
    }

    function colorDiv(div, fromOver)
    {
        if (lastDiv)
            lastDiv.className = lastDiv.className.replace('live-search-selected', '');

        lastDiv = div;

        lastDiv.className += ' live-search-selected';

        selection = div.i;

        if (!fromOver)
        {
            show();

            setTimeout(setText.bind(0, currentTextbox, g_getTextContent(div.firstChild.firstChild.childNodes[1])), 1);
            cancelNext = 1;
        }
    }

    function aOver()
    {
        colorDiv(this.parentNode.parentNode, 1);
    }

    function isVisible()
    {
        if (!container)
            return false;

        return container.style.display != 'none';
    }

    function adjust(fromResize)
    {
        if (fromResize == 1 && !isVisible())
            return;

        if (currentTextbox == null)
            return;

        var c = $WH.ac(currentTextbox);
        container.style.left  = (c[0] - 2) + 'px';
        container.style.top   = (c[1] + currentTextbox.offsetHeight + 1) + 'px';
        container.style.width = currentTextbox.offsetWidth + 'px';
    }

    function prepare()
    {
        if (prepared)
            return;

        prepared = 1;

        container = $WH.ce('div');
        container.className = 'live-search';
        container.style.display = 'none';

        $WH.ae($WH.ge('layers'), container);

        $WH.aE(window, 'resize', adjust.bind(0, 1));
        $WH.aE(document, 'click', hide);
    }

    function show()
    {
        if (container && !isVisible())
        {
            adjust();

            // Same animation as in Menu.js
            $(container).css({ opacity: '0' })
            .show()
            .animate({ opacity: '1' }, 'fast', null, doneShowing);
        }
    }

    function doneShowing(a)
    {
        $(this).css('opacity', ''); // Remove opacity once animation is over to prevent a bug in IE8
    }

    function hide(e)
    {
        if (e && !g_isLeftClick(e))
            return;

        if (container)
            container.style.display = 'none';
    }

    function highlight(match, $1)
    {
        // $1 containts % in matches with %s, which we don't want to replace
        return ($1 ? '<b><u>' + match + '</u></b>' : match);
     // aowow - why was the ternary reversed? Also how can it not match .. we explicitly searched for it.
     // return ($1 ? match : '<b><u>' + match + '</u></b>');
    }

    function display(textbox, search, suggz, dataz)
    {
        prepare();
        show();

        lastA = null;
        hasData = 1;
        selection = null;

        while (container.firstChild)
            $WH.de(container.firstChild);

        search = search.replace(/[^a-z0-9\-]/gi, ' ');
        search = $WH.trim(search.replace(/\s+/g, ' '));

        var regex = g_createOrRegex(search, (search == 's' ? '[%]' : null));

        for (var i = 0, len = suggz.length; i < len; ++i)
        {
            var pos = suggz[i].lastIndexOf('(');
            if (pos != -1)
                suggz[i] = suggz[i].substr(0, pos - 1);

            var type   = dataz[i][0],
                typeId = dataz[i][1],
                param1 = dataz[i][2],
                param2 = dataz[i][3],

                a     = $WH.ce('a'),
                sp    = $WH.ce('i'),
                sp2   = $WH.ce('span'),
                div   = $WH.ce('div'),
                div2  = $WH.ce('div');
                div.i = i;

            a.onmouseover = aOver;
            a.href = '?' + g_types[type] + '=' + typeId;
         // aowow - so statistic is an actual dbtype..?
         // a.href = '/' + (type == 10 && param2 ? 'statistic' : g_types[type]) + '=' + typeId;

            if (textbox._append)
                a.rel += textbox._append;

            if (type == 1 && param1 != null)
                div.className += ' live-search-icon-boss';
            else if (type == 3 && param2 != null)
                a.className += ' q' + param2;
            else if (type == 4 && param1 != null)
                a.className += ' q' + param1;
            else if (type == 13)
                a.className += ' c' + typeId;

            if ((type == 3 || type == 6 || type == 9 || type == 10 || type == 13 || type == 14 || type == 15 || type == 17) && param1)
            {
                div.className += ' live-search-icon';
                div.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/small/' + param1.toLowerCase() + '.jpg)';
            }
            else if ((type == 5 || type == 11) && param1 >= 1 && param1 <= 2)
            {
                div.className += ' live-search-icon-quest-' + (param1 == 1 ? 'alliance' : 'horde');
            }

            $WH.ae(sp, $WH.ct(LANG.types[type][0]));
            $WH.ae(a, sp);

            var buffer = suggz[i];
            buffer = buffer.replace(regex, highlight);

            if (type == 11)
                buffer = buffer.replace('%s', '<span class="q0">&lt;'+ LANG.name + '&gt;</span>');

            sp2.innerHTML = buffer;
            $WH.ae(a, sp2);

            if (type == 6 && param2)
                $WH.ae(a, $WH.ct(' (' + param2 + ')'));

            $WH.ae(div2, a);

            $WH.ae(div, div2);

            $WH.ae(container, div);
        }
    }

    function receive(xhr, opt)
    {
        var text = xhr.responseText;
        if (text.charAt(0) != '[' || text.charAt(text.length - 1) != ']')
            return;

        var a = eval(text);

        var search = a[0];
        if (search == opt.search)
        {
            if (a.length == 8)
                display(opt.textbox, search, a[1], a[7]);
            else
                hide();
        }
    }

    function fetch(textbox, search)
    {
        var url = '?search=' + $WH.urlencode(search);

        if (textbox._type)
            url += '&json&type=' + textbox._type;
        else
            url += '&opensearch';

        new Ajax(url, {
            onSuccess: receive,
            textbox: textbox,
            search: search
        });
    }

    function preFetch(textbox, search)
    {
        if (cancelNext)
        {
            cancelNext = 0;
            return;
        }
        hasData = 0;

        if (timer > 0)
        {
            clearTimeout(timer);
            timer = 0;
        }

        timer = setTimeout(fetch.bind(0, textbox, search), LIVESEARCH_DELAY);
    }

    function cycle(dir)
    {
        if (!isVisible())
        {
            if (hasData)
                show();
            return;
        }

        var firstNode = (container.childNodes[0].nodeName == 'EM' ? container.childNodes[3] : container.firstChild);
        var bakDiv = dir ? firstNode : container.lastChild;

        if (lastDiv == null)
            colorDiv(bakDiv);
        else
        {
            var div = dir ? lastDiv.nextSibling : lastDiv.previousSibling;

            if (div)
            {
                if (div.nodeName == 'STRONG')
                    div = container.lastChild;

                colorDiv(div);
            }
            else
            {
                colorDiv(bakDiv);
            }
        }
    }

    function onKeyUp(e)
    {
        e = $WH.$E(e);

        var textbox = e._target;
        var search  = $WH.trim(textbox.value.replace(/\s+/g, ' '));

        if (search == lastSearch[textbox.id])
            return;

        lastSearch[textbox.id] = search;

        if (search.length)
            preFetch(textbox, search);
        else
            hide();
    }

    function onKeyDown(e)
    {
        e = $WH.$E(e);
        var textbox = e._target;

        switch (e.keyCode)
        {
            case 27: // Escape
                hide();
                break;

            case 38: // Up
                cycle(0);
                break;

            case 40: // Down
                cycle(1);
                break;
        }
    }

    function onFocus(e)
    {
        e = $WH.$E(e);
        var textbox = e._target;

        if (textbox != document)
            currentTextbox = textbox;
    }

    this.attach = function(textbox)
    {
        textbox = $(textbox);
        if (!textbox.length)
            return;

        textbox = textbox[0];

        if (textbox.getAttribute('autocomplete') == 'off')
            return;

        textbox.setAttribute('autocomplete', 'off');

        $WH.aE(textbox, 'focus', onFocus);
        $WH.aE(textbox, 'keyup', onKeyUp);
        $WH.aE(textbox, 'keydown', onKeyDown);
    };

    this.reset = function(textbox)
    {
        lastSearch[textbox.id] = null;
        textbox.value = '';
        hasData = 0;
        hide();
    };

    this.hide = function()
    {
        hide();
    };
};
