$WH.aE(window, 'load', function ()
{
    if (!(window.JSON && $WH.localStorage.isSupported()))
        return;

    var lv = $WH.localStorage.get('lvBrowse');
    if (!lv)
        return;

    lv = window.JSON.parse(lv);
    if (!lv)
        return;

    var pattern = /^\?[-a-z]+=\d+/i;
 // var path    = pattern.exec(location.pathname);
    var path    = pattern.exec(location.search);
    if (!path)
        return;

    path = path[0];

    var makeButton = function (text, url)
    {
        var a = $WH.ce('a');
        a.className = 'button-red' + (url ? ' button-red-disabled' : '');

        var em = $WH.ce('em');
        $WH.ae(a, em);

        var b = $WH.ce('b');
        $WH.ae(em, b);

        var i = $WH.ce('i');
        $WH.ae(b, i);
        $WH.st(i, text);

        var sp = $WH.ce('span');
        $WH.ae(em, sp);
        $WH.st(sp, text);

        return a;
    };

    for (var i = 0; i < lv.length; i++)
    {
        var urls = lv[i].urls;

        for (var j = 0; j < urls.length; j++)
        {
            if (urls[j] == path)
            {
                var prevUrl = j > 0 ? urls[j - 1] : false;
                var nextUrl = (j + 1) < urls.length ? urls[j + 1] : false;
                var upUrl   = lv[i].path + lv[i].hash;
                var el      = $WH.ge('topbar-browse');

                if (!el)
                    return;

                var button;

                button = makeButton('>', !nextUrl);
                if (nextUrl)
                    button.href = nextUrl;

                $WH.ae(el, button);

                button = makeButton(LANG.up, !upUrl);
                if (upUrl)
                    button.href = upUrl;

                $WH.ae(el, button);

                button = makeButton('<', !prevUrl);
                if (prevUrl)
                    button.href = prevUrl;

                $WH.ae(el, button);

                return;
            }
        }
    }
});
