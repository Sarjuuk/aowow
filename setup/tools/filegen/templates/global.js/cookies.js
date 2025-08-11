// TODO: Create a "Cookies" object

function g_cookiesEnabled()
{
    document.cookie = 'enabledTest';
    return (document.cookie.indexOf("enabledTest") != -1) ? true : false;
}

function g_getWowheadCookie(name)
{
    if (g_user.id > 0)
    {
        return g_user.cookies[name]; // no point checking if it exists, as undefined tests as false anyways
    }
    else
    {
        return $WH.gc(name); // plus gc does the same thing..
    }
}

function g_setWowheadCookie(name, data, browser)
{
    var temp = name.substr(0, 5) == 'temp_';
    if (!browser && g_user.id > 0 && !temp) {
        new Ajax('?cookie=' + name + '&' + name + '=' + $WH.urlencode(data), {
            method: 'get',
            onSuccess: function(xhr) {
                if (xhr.responseText == 0)
                    g_user.cookies[name] = data;
            }
        });
    }
    else if (browser || g_user.id == 0)
    {
        $WH.sc(name, 14, data, null, location.hostname);
    }
}
