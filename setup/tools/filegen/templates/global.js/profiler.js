/*
Global Profiler-related functions
*/

function g_cleanCharacterName(name) {
    return (name.match && name.match(/^[A-Z]/) ? name.charAt(0).toLowerCase() + name.substr(1) : name);
}

function g_getProfileUrl(profile) {
    if (profile.region) // Armory character
        return '?profile=' + profile.region + '.' + profile.realm + '.' + g_cleanCharacterName(profile.name) + (profile.renameItr ? '-' + profile.renameItr : '');
     // return '?profile=' + profile.region + '.' + profile.realm + '.' + g_cleanCharacterName(profile.name);  // aowow custom
    else // Custom profile
        return '?profile=' + profile.id;
}

function g_getProfileRealmUrl(profile) {
    return '?profiles=' + profile.region + '.' + profile.realm;
}

$WH.prepInfobox = function () {
    $('.infobox').each(function ()
    {
        var row = $(this);
        if (row.data('infobox-completion-info-added') !== true && g_user.characters &&
            typeof g_pageInfo == 'object' && typeof g_pageInfo.type == 'number' && typeof g_pageInfo.typeId == 'number')
        {
            var wardrobe = $('.compact-completion-display', row);
            if (wardrobe.length)
            {
                var completionIcon = $('span', wardrobe);
                if (completionIcon.length)
                {
                    var i = 0;
                    completionIcon.each(function ()
                    {
                        var e = $(this);
                        var t = e.html();

                        try { t = JSON.parse(t) }
                        catch (e) { return }

                        var a;
                        for (var n = 0, s; s = g_user.characters[n]; n++)
                        {
                            if (s.id == t.id)
                            {
                                a = s;
                                break
                            }
                        }

                        if (a)
                        {
                            e.parent().append($WH.createCompletionIcon(a, t.completed ? 1 : 0, t.rel));
                            e.remove();
                            i++
                        }
                    });

                    if (i)
                        wardrobe.parent().parent().show()
                }
                else if (wardrobe.is(':empty') && $WH.addCompletionIcons(wardrobe.get(0), g_pageInfo.type, g_pageInfo.typeId))
                    wardrobe.parent().parent().show()
            }

            row.data('infobox-completion-info-added', true)
        }
    });
};

$WH.addCompletionIcons = function (parent, type, typeIdOrData, skipIncomplete) {
    var nComplete = 0;

    for (var i in g_user.characters)
    {
        if (!g_user.characters.hasOwnProperty(i))
            continue;

        let profile = g_user.characters[i];

        let completion = 0;
        let completionData = g_user.completion?.hasOwnProperty(type) ? g_user.completion[type] : {};
        if (!completionData.hasOwnProperty(profile.id))
            continue;

        completion = completionData[profile.id].includes(typeIdOrData) ? 1 : 0;

        if (skipIncomplete && !completion)
            continue;

        $WH.ae(parent, $WH.createCompletionIcon(profile, completion));
        nComplete++;
    }

    return nComplete;
};

$WH.createCompletionIcon = function (profile, completePct, rel) {
    var icon = $WH.ce('a');
    icon.href = '?profile=' + profile.region + '.' + profile.realm + '.' + profile.name;

    // aowow - so the generic tooltips dont override our completion tooltip
    icon.setAttribute('data-disable-wowhead-tooltip', true);

    icon.className = 'progress-icon progress-' + (completePct ? Math.max(1, Math.floor(completePct * 8)) : 0);
    $WH.Tooltip.simple(icon, $WH.getCompletionTooltip(profile, completePct), null, true);

    if (rel)
        icon.rel = rel;

    return icon;
};

$WH.getCompletionTooltip = function (profile, completePct) {
    let tooltip = $WH.ce('div');
    $WH.ae(tooltip, $WH.ce('span', { className: 'q' }, $WH.ct((completePct >= 1 ? LANG.complete : LANG.incomplete) + LANG.colon)));
    $WH.ae(tooltip, $WH.ce('br'));

    let charRow = $WH.ce('span', { style: { whiteSpace: 'nowrap' } });
    $WH.ae(tooltip, charRow);
    $WH.ae(charRow, $WH.ce('b', { className: 'c' + profile.classs }, $WH.ct(profile.name)));

    let server = [' ', profile.realmname];

    if (profile.hasOwnProperty('region'))
        server.push(profile.region.toUpperCase());

    $WH.ae(charRow, $WH.ce('span', { className: 'q0' }, $WH.ct(server.join(' '))));

    if (completePct > 0 && completePct < 1)
        $WH.ae(charRow, $WH.ct( $WH.sprintf(LANG.parens_format, '', Math.round(completePct * 100) + '%') ));

    return tooltip.innerHTML;
};

$WH.getCompletionFlags = function (type, typeId) {
    var flags = 0;
    var profiles = g_user.characters || [];
    let completionData = g_user.completion?.hasOwnProperty(type) ? g_user.completion[type] : {};

    for (var i = profiles.length - 1; i >= 0; i--)
    {
        var profile = profiles[i];
        if (!(profile.id in completionData))
            continue

        flags = flags << 1 | (completionData[profile.id].includes(typeId) ? 1 : 0)
    }

    return flags;
};
