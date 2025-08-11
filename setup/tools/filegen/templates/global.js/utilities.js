/*
Global utility functions related to arrays, format validation, regular expressions, and strings
*/

function g_createRange(min, max)
{
    range = {};

    for (var i = min; i <= max; ++i)
        range[i] = i;

    return range;
}

function g_sortIdArray(arr, reference, prop)
{
    arr.sort(
        prop ?
            function(a, b) { return $WH.strcmp(reference[a][prop], reference[b][prop]) }
        :
            function(a, b) { return $WH.strcmp(reference[a], reference[b]) }
    );
}

function g_sortJsonArray(src, reference, sortFunc, filterFunc)
{
    var result = [];

    for (var i in src)
    {
        if (reference[i] && (filterFunc == null || filterFunc(reference[i])))
            result.push(i);
    }

    if (sortFunc != null)
        result.sort(sortFunc);
    else
        g_sortIdArray(result, reference);

    return result;
}

function g_urlize(str, allowLocales, profile)
{
    var ta = $WH.ce('textarea');
    ta.innerHTML = str.replace(/</g,"&lt;").replace(/>/g,"&gt;");
    str = ta.value;

    str = $WH.str_replace(str, ' / ', '-');
    str = $WH.str_replace(str, "'", '');

    if (profile)
    {
        str = $WH.str_replace(str, '(', '');
        str = $WH.str_replace(str, ')', '');
        var accents = {
            "ß": "ss",
            "á": "a", "ä": "a", "à": "a", "â": "a",
            "è": "e", "ê": "e", "é": "e", "ë": "e",
            "í": "i", "î": "i", "ì": "i", "ï": "i",
            "ñ": "n",
            "ò": "o", "ó": "o", "ö": "o", "ô": "o",
            "ú": "u", "ü": "u", "û": "u", "ù": "u",
            "œ": "oe",
            "Á": "A", "Ä": "A", "À": "A", "Â": "A",
            "È": "E", "Ê": "E", "É": "E", "Ë": "E",
            "Í": "I", "Î": "I", "Ì": "I", "Ï": "I",
            "Ñ": "N",
            "Ò": "O", "Ó": "O", "Ö": "O", "Ô": "O",
            "Ú": "U", "Ü": "U", "Û": "U", "Ù": "U",
            "œ": "Oe"
        };
        for (var character in accents)
            str = str.replace(new RegExp(character, "g"), accents[character]);
    }

    str = $WH.trim(str);
    if (allowLocales)
        str = $WH.str_replace(str, ' ', '-');
    else
        str = str.replace(/[^a-z0-9]/ig, '-');
    str = $WH.str_replace(str, '--', '-');
    str = $WH.str_replace(str, '--', '-');
    str = $WH.rtrim(str, '-');
    str = str.replace(/[A-Z]/g, function(x) { return x.toLowerCase() });
    return str;
}

function g_isDateValid(date)
{
    var match = /^(20[0-2]\d)-([01]\d)-([0-3]\d) ([0-2]\d):([0-5]\d):([0-5]\d)$/.exec(date);
    return match;
}

function g_isIpAddress(str)
{
    return /[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+/.test(str);
}

function g_isEmailValid(email)
{
    return email.match(/^([a-z0-9._-]+)(\+[a-z0-9._-]+)?(@[a-z0-9.-]+\.[a-z]{2,4})$/i) != null;
}

function g_getCurrentDomain()
{
    if (g_getCurrentDomain.CACHE)
        return g_getCurrentDomain.CACHE;

    var hostname = location.hostname;

    if (!g_isIpAddress(hostname))
    {
        // Only keep the last 2 parts
        var parts = hostname.split('.');
        if (parts.length > 2)
        {
            parts.splice(0, parts.length - 2);
        }
        hostname = parts.join('.');
    }

    g_getCurrentDomain.CACHE = hostname;

    return hostname;
}

function g_isExternalUrl(url)
{
    if (!url)
        return false;

    if (url.indexOf('http') != 0 && url.indexOf('//') != 0)
        return false;
    else if (url.indexOf(g_getCurrentDomain()) != -1)
        return false;

    return true;
}

function g_createOrRegex(search, negativeGroup)
{
    search = search.replace(/(\(|\)|\|\+|\*|\?|\$|\^)/g, '\\$1');
    var parts = search.split(' '),
        strRegex = '';

    for (var j = 0, len = parts.length; j < len; ++j)
    {
        if (j > 0)
            strRegex += '|';
        strRegex += parts[j];
    }

    // The additional group is necessary so we dont replace %s
    return new RegExp((negativeGroup != null ? '(' + negativeGroup + ')?' : '') + '(' + strRegex + ')', 'gi');
}

function g_getHash()
{
    return '#' + decodeURIComponent(location.href.split('#')[1] || '');
}

// Lets you add/remove/edit the query parameters in the passed URL
function g_modifyUrl(url, params, opt)
{
    if (!opt)
        opt = $.noop;

    // Preserve existing hash
    var hash = '';
    if (url.match(/(#.+)$/))
    {
        hash = RegExp.$1;
        url = url.replace(hash, '');
    }

    $.each(params, function(paramName, newValue)
    {
        var needle;
        var paramPrefix;
        var paramValue;

        var matches = url.match(new RegExp('(&|\\?)?' + paramName + '=?([^&]+)?'));
        if (matches != null)
        {
            needle      = matches[0];
            paramPrefix = matches[1];
            paramValue  = decodeURIComponent(matches[2]);
        }

        // Remove
        if (newValue == null)
        {
            if (!needle) // If param wasn't there, no need to remove anything
                return;

            paramValue = null;
        }
        // Append
        else if (newValue.substr(0, 2) == '+=')
        {
            if (paramValue && opt.onAppendCollision)
            {
                 paramValue = opt.onAppendCollision(paramValue, newValue.substr(2), opt.menuUrl);
            }
            else if (!paramValue && opt.onAppendEmpty)
            {
                 paramValue = opt.onAppendEmpty(newValue.substr(2), opt.menuUrl);
            }
            else
            {
                if (!paramValue)
                    paramValue = '';
                paramValue += $.trim(newValue.substr(2));
            }
        }
        // Set
        else
        {
            paramValue = newValue;
        }

        // Replace existing param
        if (needle)
        {
            var replacement = '';
            if (paramPrefix) // Preserve existing prefix
                replacement += paramPrefix;
            if (paramValue != null)
            {
                replacement += paramName;
                if (paramValue)
                    replacement += '=' + $WH.urlencode2(paramValue);
            }

            url = url.replace(needle, replacement);
        }
        // Add new param
        else if (paramValue || newValue == null || newValue.substr(0,2) != '+=')
        {
            url += (url.indexOf('?') == -1 ? '?' : '&') + paramName;
            if (paramValue)
                url += '=' + $WH.urlencode2(paramValue);
        }
    });

    // Polish
    url = url.replace('?&', '?');
    url = url.replace(/&&/g, '&');
    url = url.replace(/\/\?/g, '?');
    url = url.replace(/(&|\?)+$/, ''); // Remove trailing & and ? characters

    return url + hash;
}
