// stupid little workaround so modes can be set without magic numbers in tag definitions
// now made moot by being unable to use variables as a key
var MARKUP_MODE_COMMENT    = 1,
    MARKUP_MODE_ARTICLE    = 2,
    MARKUP_MODE_QUICKFACTS = 3,
    MARKUP_MODE_SIGNATURE  = 4,
    MARKUP_MODE_REPLY      = 5,
    MARKUP_CLASS_ADMIN   = 40,
    MARKUP_CLASS_STAFF   = 30,
    MARKUP_CLASS_PREMIUM = 20,
    MARKUP_CLASS_USER    = 10,
    MARKUP_CLASS_PENDING = 1;

var MARKUP_SOURCE_LIVE = 1,
    MARKUP_SOURCE_PTR  = 2,
    MARKUP_SOURCE_BETA = 3;

var MarkupModeMap = {};
MarkupModeMap[MARKUP_MODE_COMMENT]    = 'comment';
MarkupModeMap[MARKUP_MODE_REPLY]      = 'reply';
MarkupModeMap[MARKUP_MODE_ARTICLE]    = 'article';
MarkupModeMap[MARKUP_MODE_QUICKFACTS] = 'quickfacts';
MarkupModeMap[MARKUP_MODE_SIGNATURE]  = 'signature';

var MarkupSourceMap = {};
MarkupSourceMap[MARKUP_SOURCE_LIVE] = 'live';
MarkupSourceMap[MARKUP_SOURCE_PTR]  = 'ptr';
MarkupSourceMap[MARKUP_SOURCE_BETA] = 'beta';

var MarkupDomainRegexMap = {
    betaPtrLang: /^(beta|legion|wod|mop|ptr|www|ko|fr|de|cn|es|ru|pt|it)$/,
    lang: /^(www|fr|de|cn|es|ru)$/                          // Aowowo - /^(www|ko|fr|de|cn|es|ru|pt|it)$/
};

var Markup = {
    MODE_COMMENT:    MARKUP_MODE_COMMENT,
    MODE_REPLY:         MARKUP_MODE_REPLY,
    MODE_ARTICLE:    MARKUP_MODE_ARTICLE,
    MODE_QUICKFACTS: MARKUP_MODE_QUICKFACTS,
    MODE_SIGNATURE:  MARKUP_MODE_SIGNATURE,

    SOURCE_LIVE: MARKUP_SOURCE_LIVE,
    SOURCE_PTR:  MARKUP_SOURCE_PTR,
    SOURCE_BETA: MARKUP_SOURCE_BETA,

    CLASS_ADMIN:   MARKUP_CLASS_ADMIN,
    CLASS_STAFF:   MARKUP_CLASS_STAFF,
    CLASS_PREMIUM: MARKUP_CLASS_PREMIUM,
    CLASS_USER:    MARKUP_CLASS_USER,
    CLASS_PENDING: MARKUP_CLASS_PENDING,

    /* aowow custom: first element */
    whitelistedWebsites: [new RegExp('(.*\\.)?' + location.hostname, 'i'), /(.*\.)?wowhead.com/i, /(.*\.)?thottbot.com/i, /(.*\.)?torhead.com/i, /(.*\.)?mmoui.com/i, /(.*\.)?tankspot.com/i, /(.*\.)?guildfans.com/i, /(.*\.)?allakhazam.com/i, /(.*\.)?zam.com/i, /(.*\.)?blizzard.com/i, /(.*\.)?worldofwarcraft.com/i, /(.*\.)?wow-europe.com/i, /(.*\.)?battle.net/i, /(.*\.)?sc2ranks.com/i, /(.*\.)?torchlightarmory.com/i, /(.*\.)?vindictusdb.com/i, /(.*\.)?wowinterface.com/i, /(.*\.)?vginterface.com/i, /(.*\.)?lotrointerface.com/i, /(.*\.)?eq2interface.com/i, /(.*\.)?eqinterface.com/i, /(.*\.)?mmo-champion.com/i, /(.*\.)?joystiq.com/i, /(.*\.)?wow-heroes.com/i, /(.*\.)?be-imba.hu/i, /(.*\.)?wowpedia.org/i, /(.*\.)?curse.com/i, /(.*\.)?elitistjerks.com/i, /(.*\.)?wowwiki.com/i, /(.*\.)?worldoflogs.com/i, /(.*\.)?wowinsider.com/i, /(.*\.)?guildwork.com/i],

    rolesToClass: function(roles)
    {
        if(roles & (U_GROUP_ADMIN|U_GROUP_VIP|U_GROUP_DEV))
            return Markup.CLASS_ADMIN;
        else if(roles & U_GROUP_STAFF)
            return Markup.CLASS_STAFF;
        else if(roles & U_GROUP_PREMIUM)
            return Markup.CLASS_PREMIUM;
        else if(roles & U_GROUP_PENDING)
            return Markup.CLASS_PENDING;
        else
            return Markup.CLASS_USER;
    },

    defaultSource: false,
    nameCol: 'name_enus',
    domainToLocale: {
        'www': 'enus',
        'ptr': 'ptr',
        'beta': 'beta',
        'mop': 'beta',
        'fr': 'frfr',
        'de': 'dede',
        'cn': 'zhcn',
        'es': 'eses',
        'ru': 'ruru',
        'pt': 'ptbr'
    },
    maps: [],
    firstTags: {},
    postTags: [],
    collectTags: {},
    excludeTags: {},
    tooltipTags: {},
    tooltipBare: {},
    attributes: {
        id: { req: false, valid: /^[a-z0-9_-]+$/i },
        title: { req: false, valid: /[\S ]+/ },
        'class': { req: false, valid: /\S+/ }
    },
    youtube: {
        players: [],
        APIIsReady: false,
        readyTimeout: undefined,
        APIReady: function () {
            var UNK_INITED = false;
            Markup.youtube.readyTimeout = undefined;
            Markup.youtube.APIIsReady = true;
            for (var i in Markup.youtube.players) {
                if (!Markup.youtube.players.hasOwnProperty(i))
                    continue;

                if (Markup.youtube.players[i].loaded)
                    continue;

                if ($WH.ge(Markup.youtube.players[i].containerId))
                {
                    Markup.youtube.players[i].loaded = true;
                    Markup.youtube.players[i].player = new YT.Player(Markup.youtube.players[i].containerId, Markup.youtube.players[i].yt);
                }
                else
                    UNK_INITED = true;
            }

            if (UNK_INITED)
                Markup.youtube.readyTimeout = window.setTimeout(Markup.youtube.APIReady, 500);
        }
    },
    IsLinkAllowed: function(link)
    {
        var matches = link.match('[a-z]+:\/\/([a-z0-9\.\-]+)');

        if(!matches)
            return true;

        var domain = matches[1];
        var allowed = false;

        for(var i in Markup.whitelistedWebsites)
        {
            var r = Markup.whitelistedWebsites[i];

            if(domain.search(r) == 0)
                allowed = true;
        }

        return allowed;
    },
    tags: {
        '<text>':
        {
            empty: true,
            noHelp: true,
            allowInReplies: true,
            toHtml: function(attr, extra)
            {
                extra = extra || $.noop;

                if(attr._text == ' ' && !extra.noNbsp)
                    attr._text = '&nbsp;';

                attr._text = attr._text.replace(/\\\[/g, '[');

                if(extra && extra.noLink)
                    return attr._text;
                else if(extra && extra.needsRaw)
                    return attr._rawText;
                else
                {
                    var link = [];
                    var text = Markup._preText(attr._rawText.replace(/(https?:\/\/|www\.)([\/_a-z0-9\%\?#@\-\+~&=;:']|\.[a-z0-9\-])+/gi, function(match) {
                        matchUrl = Markup._preText(match.replace(/^www/, 'http://www'));
                        match = Markup._preText(match);
                        var i = link.length;
                        link.push([matchUrl, match]);
                        return '$L' + i;
                    }));
                    text = text.replace(/\$L([\d+]) /gi, '$L$1&nbsp;');
                    for(var i in link)
                    {
                        text = text.replace('$L' + i, function(match) {
                            if(Markup.allow < Markup.CLASS_USER && !Markup.IsLinkAllowed(link[i][0]))
                                return $WH.sprintf('<span class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, LANG.linkremoved_tip, 0, 0, \'q\')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">[$1]</span>', LANG.linkremoved);
                            var url = '<a href="' + link[i][0] + '"';
                            if(Markup._isUrlExternal(link[i][0]))
                                url += ' target="_blank"';
                            url += '>' + link[i][1] + '</a>';
                            return url;
                        });
                    }
                    return text;
                }
            },
            toText: function(attr)
            {
                return attr._text;
            }
        },
        achievement:
        {
            empty: true,
            allowInReplies: true,
            attr:
            {
                unnamed:  { req: true,  valid: /^[0-9]+$/ },
                diff:     { req: false, valid: /^[0-9]+$/ },
                icon:     { req: false, valid: /^false$/ },
                domain:   { req: false, valid: MarkupDomainRegexMap.lang },
                site:     { req: false, valid: MarkupDomainRegexMap.lang },
                tempname: { req: false }

            },
            validate: function(attr)
            {
                if((attr.domain || attr.site) && Markup.dbpage)
                    return false;
                return true;
            },
            toHtml: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var url = domainInfo[0];
                var nameCol = domainInfo[1];
                var rel = [];
                var tempname = null;

                if(attr.diff)
                    rel.push('diff=' + attr.diff);
                if(attr.tempname)
                    tempname = attr.tempname;

                if(g_achievements[id] && g_achievements[id][nameCol])
                {
                    var ach = g_achievements[id];
                    return '<a href="' + url + '?achievement=' + id + '"' + (rel.length ? ' rel="' + rel.join('&') + '"' : '') + (!attr.icon ? ' class="icontiny"><img src="' + g_staticUrl + '/images/wow/icons/tiny/' + ach.icon.toLowerCase() + '.gif"' : '') + Markup._addGlobalAttributes(attr) + ' align="absmiddle" /> <span class="tinyicontxt">' + Markup._safeHtml(ach[nameCol]) + '</span></a>';
                }
                return '<a href="' + url + '?achievement=' + id + '"' + (rel.length ? ' rel="' + rel.join('&') + '"' : '') + Markup._addGlobalAttributes(attr) + '>' + (tempname ? tempname : ('(' + LANG.types[10][0] + ' #' + id + ')')) + '</a>';
            },
            toText: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var url = domainInfo[0];
                var nameCol = domainInfo[1];

                if(g_achievements[id] && g_achievements[id][nameCol])
                    return Markup._safeHtml(g_achievements[id][nameCol]);
                return LANG.types[10][0] + ' #' + id;
            }
        },
        achievementpoints:
        {
            empty: true,
            attr:
            {
                unnamed: {req: true, valid: /^[0-9]+$/ }
            },
            allowedClass: MARKUP_CLASS_STAFF,
            toHtml: function(attr)
            {
                var str = '<span class="moneyachievement tip" onmouseover="Listview.funcBox.moneyAchievementOver(event)" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"' + Markup._addGlobalAttributes(attr) + '>' + attr.unnamed + '</span>';
                return str;
            }
        },
        anchor:
        {
            empty: true,
            ltrim: true,
            rtrim: true,
            attr:
            {
                unnamed: { req: false, valid: /\S+/ }
            },
            validate: function(attr)
            {
                if(!attr.unnamed && !attr.id)
                    return false;
                return true;
            },
            toHtml: function(attr)
            {
                if(!attr.unnamed && attr.id)
                {
                    attr.unnamed = attr.id;
                    attr.id = null;
                }
                return '<a name="' + attr.unnamed + '"' + Markup._addGlobalAttributes(attr) + '></a>';
            }
        },
        acronym:
        {
            empty: false,
            attr:
            {
                unnamed: { req: false }
            },
            toHtml: function(attr)
            {
                return ['<span class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, \'' + Markup._safeHtml(attr.unnamed) + '\', 0, 0, \'q1\');" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()" ' + Markup._addGlobalAttributes(attr) + '>', '</span>'];
            }
        },
        b:
        {
            empty: false,
            allowInReplies: true,
            toHtml: function(attr)
            {
                return ['<b' + Markup._addGlobalAttributes(attr) + '>', '</b>'];
            },
            fromHtml: function(str)
            {
                return str.replace(/<(b|big|strong)\b[\s\S]*?>([\s\S]*?)<\/\1>/gi, '[b]$2[/b]');
            }
        },
        blip:
        {
            empty: true,
            attr:
            {
                unnamed: { req: true, valid: /\S+/ }
            },
            allowedClass: MARKUP_CLASS_STAFF,
            toHtml: function(attr)
            {
                var url = 'http://blip.tv/play/' + attr.unnamed;
                var width = 600;
                var height = 368;

                var html = '';
                // object tag causing issues in chrome?
                /*html += '<object width="' + width + '" height="' + height + '"' + Markup._addGlobalAttributes(attr) + '><param name="movie" value="' + url + '">';
                html += '<param name="allowfullscreen" value="true"></param>';
                html += '<param name="allowscriptaccess" value="always"></param>';
                html += '<param name="wmode" value="opaque"></param>';*/
                html += '<embed width="' + width + '" height="' + height + '" src="' + url + '" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" wmode="opaque"></embed>';
                //html += '</object>';

                return html;
            }
        },
        br:
        {
            empty: true,
            toHtml: function(attr)
            {
                return '<br />';
            },
            fromHtml: function(str)
            {
                return str.replace(/<br\b[\s\S]*?>/gi, "\n");
            }
        },
        'class':
        {
            empty: true,
            allowInReplies: true,
            attr:
            {
                unnamed: { req: true,  valid: /^[0-9]+$/ },
                icon:    { req: false, valid: /^false$/i },
                domain:  { req: false, valid: MarkupDomainRegexMap.lang },
                site:    { req: false, valid: MarkupDomainRegexMap.lang }
            },
            validate: function(attr)
            {
                if((attr.domain || attr.site) && Markup.dbpage)
                    return false;
                if(attr.unnamed >= 1 && attr.unnamed <= 11)
                    return true;
                return false;
            },
            toHtml: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var url = domainInfo[0];
                var nameCol = domainInfo[1];

                if(g_classes[id] && g_classes[id][nameCol])
                {
                    var cls = g_classes[id];
                    return '<a href="' + url + '?class=' + id + '"' + (!attr.icon ? ' class="icontiny c' + id + '"><img src="' + g_staticUrl + '/images/wow/icons/tiny/' + g_classes.getIcon(id) + '.gif"' : '') + Markup._addGlobalAttributes(attr) + ' align="absmiddle" /> <span class="tinyicontxt">' + Markup._safeHtml(cls[nameCol]) + '</span></a>';
                }
                return '<a href="' + url + '?class=' + id + '" class="c' + id + '"' + Markup._addGlobalAttributes(attr) + '>(' + LANG.types[13][0] + ' #' + id + ')</a>';
            },
            toText: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var nameCol = domainInfo[1];

                if(g_classes[id] && g_classes[id][nameCol])
                    return Markup._safeHtml(g_classes[id][nameCol]);
                return LANG.types[13][0] + ' #' + id;
            }
        },
        code:
        {
            block: true,
            empty: false,
            rtrim: true,
            itrim: true,
            helpText: true,
            allowedChildren: { '<text>': 1 },
            toHtml: function(attr)
            {
                var open = '<pre class="code';
                if(attr.first)
                    open += ' first';
                if(attr.last)
                    open += ' last';
                open += '"' + Markup._addGlobalAttributes(attr) + '>';
                return [open, '</pre>'];
            }
        },
        color:
        {
            empty: false,
            attr:
            {
                unnamed: { req: true, valid: /^.*/i }
            },
            allowedClass: MARKUP_CLASS_STAFF,
            /* Syntax: name: class */
            extraColors: {deathknight: 'c6', dk: 'c6', druid: 'c11', hunter: 'c3', mage: 'c8', paladin: 'c2', priest: 'c5', rogue: 'c4', shaman: 'c7', warlock: 'c9', warrior: 'c1', poor: 'q0', common: 'q1', uncommon: 'q2', rare: 'q3', epic: 'q4', legendary: 'q5', artifact: 'q6', heirloom: 'q7'},
            toHtml: function(attr)
            {
                var valid = /^(aqua|black|blue|fuchsia|gray|green|lime|maroon|navy|olive|purple|red|silver|teal|white|yellow|c\d+|r\d+|q\d*?|#[a-f0-9]{6})$/i;
                var str = '<span ';

                if(attr.unnamed.match(valid))
                {
                    if(attr.unnamed == '#00C0FF')
                        str += 'class="blizzard-blue"' + Markup._addGlobalAttributes(attr);
                    else
                    {
                        var firstChar = attr.unnamed.charAt(0);
                        str += ((firstChar == 'q' || firstChar == 'c' || (firstChar == 'r' && attr.unnamed != 'red')) ? 'class="' : 'style="color: ') + attr.unnamed + '"' + Markup._addGlobalAttributes(attr);
                    }
                }
                else if(Markup.tags.color.extraColors[attr.unnamed])
                {
                    str += 'class = "' + Markup.tags.color.extraColors[attr.unnamed] + '"';
                }
                str += '>';
                return [str, '</span>'];
            }
        },
        currency:
        {
            empty: true,
            allowInReplies: true,
            attr:
            {
                unnamed: { req: true,  valid: /^[0-9]+$/ },
                amount:  { req: false, valid: /^[0-9\:]+$/ },
                icon:    { req: false, valid: /^false$/i },
                domain:  { req: false, valid: MarkupDomainRegexMap.lang },
                site:    { req: false, valid: MarkupDomainRegexMap.lang }
            },
            allowedClass: MARKUP_CLASS_STAFF,
            validate: function(attr)
            {
                if((attr.domain || attr.site) && Markup.dbpage)
                    return false;
                return true;
            },
            toHtml: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var url = domainInfo[0];
                var nameCol = domainInfo[1];

                if(g_gatheredcurrencies[id] && g_gatheredcurrencies[id][nameCol])
                {
                    var curr = g_gatheredcurrencies[id];
                    if(attr.amount)
                        return '<a href="' + url + '?currency=' + id + '"' + (!attr.icon ? ' class="icontinyr tip q1" onmouseover="$WH.Tooltip.showAtCursor(event, \'' + Markup._safeHtml(curr[nameCol]) + '\', 0, 0, \'q1\');" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()" style="background-image:url(' + g_staticUrl + '/images/wow/icons/tiny/' + curr.icon[0].toLowerCase() + '.gif)' : '') + Markup._addGlobalAttributes(attr) + '"> <span class="tinyicontxt">' + attr.amount.split(':').join(' - ') + '</span></a>';
                    else
                        return '<a href="' + url + '?currency=' + id + '"' + (!attr.icon ? ' class="icontiny q1"><span><img src="' + g_staticUrl + '/images/wow/icons/tiny/' + curr.icon[0].toLowerCase() + '.gif"' : '') + Markup._addGlobalAttributes(attr) + ' align="absmiddle" /> <span class="tinyicontxt">' + Markup._safeHtml(curr[nameCol]) + '</a>';
                }

                return '<a href="' + url + '?currency=' + id + '"' + Markup._addGlobalAttributes(attr) + '>(' + LANG.types[17][0] + ' #' + id + ')</a>' + (attr.amount > 0 ? ' x' + attr.amount : '');
            },
            toText: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var nameCol = domainInfo[1];

                if(g_gatheredcurrencies[id] && g_gatheredcurrencies[id][nameCol])
                    return Markup._safeHtml(g_gatheredcurrencies[id][nameCol]);
                return LANG.types[17][0] + ' #' + id;
            }
        },
        db:
        {
            empty: true,
            attr:
            {
                unnamed: { req: true, valid: /^(live|ptr|beta|mop)$/ }
            },
            allowedClass: MARKUP_CLASS_STAFF,
            toHtml: function(attr)
            {
                if(attr.unnamed == 'live')
                    Markup.defaultSource = Markup.SOURCE_LIVE;
                else if(attr.unnamed == 'ptr')
                    Markup.defaultSource = Markup.SOURCE_PTR;
                else if(attr.unnamed == 'beta' || attr.unnamed == 'mop')
                    Markup.defaultSource = Markup.SOURCE_BETA;
                return '';
            },
            toText: function(attr)
            {
                if(attr.unnamed == 'live')
                    Markup.defaultSource = Markup.SOURCE_LIVE;
                else if(attr.unnamed == 'ptr')
                    Markup.defaultSource = Markup.SOURCE_PTR;
                else if(attr.unnamed == 'beta' || attr.unnamed == 'mop')
                    Markup.defaultSource = Markup.SOURCE_BETA;
                return '';
            }
        },
        del:
        {
            empty: false,
            attr:
            {
                copy: { req: false, valid: /^true$/ }
            },
            toHtml: function(attr)
            {
                var str = '<del class="diffmod"' + Markup._addGlobalAttributes(attr);
                if(!attr.copy)
                    str += ' unselectable="on"';
                str += '>';
                return [str, '</del>'];
            }
        },
        div:
        {
            empty: false,
            block: true,
            ltrim: true,
            rtrim: true,
            itrim: true,
            attr:
            {
                clear:   { req: false, valid: /^(left|right|both)$/i },
                unnamed: { req: false, valid: /^hidden$/i },
                'float': { req: false, valid: /^(left|right)$/i },
                align:   { req: false, valid: /^(left|right|center)$/i },
                margin:  { req: false, valid: /^\d+(px|em|\%)$/ },
                style:   { req: false, valid: /^[^"<>]+$/ },
                width:   { req: false, valid: /^[0-9]+(px|em|\%)$/ }
            },
            allowedClass: MARKUP_CLASS_STAFF,
            toHtml: function(attr)
            {
                var str = '<div' + Markup._addGlobalAttributes(attr);

                var styles = [];
                var classes = [];
                if(attr.clear)
                    styles.push('clear: ' + attr.clear);
                if(attr.unnamed)
                    styles.push('display: none');
                if(attr.width)
                    styles.push('width: ' + attr.width);
                if (attr['float'])
                    classes.push('markup-float-' + attr['float']);
                if(attr.align)
                    styles.push('text-align: ' + attr.align);
                if(attr.margin)
                    styles.push('margin: ' + attr.margin);
                if(attr.style && Markup.allow >= Markup.CLASS_STAFF)
                    styles.push(attr.style)
                if(attr.first)
                    classes.push('first');
                if(attr.last)
                    classes.push('last');

                if(styles.length > 0)
                    str += ' style="' + styles.join(';') + '"';
                if(classes.length > 0)
                    str += ' class="' + classes.join(' ') + '"';

                str += '>';
                return [str, '</div>'];
            },
            fromHtml: function(str, depth)
            {
                depth = depth || 0;

                var m;
                if(m = Markup.matchOuterTags(str, '<div\\b[\\s\\S]*?>', '</div>', 'g'))
                {
                    for(var i = 0; i < m.length; ++i)
                    {
                        var align = m[i][1].match(/float:\s*(left|right)"/i),
                            width = m[i][1].match(/width[:="]+\s*([0-9]+)/i);

                        str = str.replace(m[i][1] + m[i][0] + m[i][2], "\n" + Array(depth + 1).join("\t") + '[div' + (align ? ' float=' + align[1] : '') + (width ? ' width=' + width[1] : '') + ']' + Markup.tags.div.fromHtml(m[i][0], depth + 1) + '[/div]');
                    }
                }

                return str;
            }
        },
        emote:
        {
            empty: true,
            allowInReplies: true,
            attr:
            {
                unnamed: { req: true,  valid: /^[0-9]+$/ },
                domain:  { req: false, valid: MarkupDomainRegexMap.lang },
                site:    { req: false, valid: MarkupDomainRegexMap.lang }
            },
            validate: function(attr)
            {
                if((attr.domain || attr.site) && Markup.dbpage)
                    return false;
                return true;
            },
            toHtml: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var url = domainInfo[0];
                var nameCol = domainInfo[1];

                if(g_emotes[id] && g_emotes[id][nameCol])
                {
                    return '<a href="' + url + '?emote=' + id + '"' + Markup._addGlobalAttributes(attr) + '>' + Markup._safeHtml(g_emotes[id][nameCol]) + '</a>';
                }
                return '<a href="' + url + '?emote=' + id + '"' + Markup._addGlobalAttributes(attr) + '>(' + LANG.types[501][0] + ' #' + id + ')</a>';
            },
            toText: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var nameCol = domainInfo[1];

                if(g_emotes[id] && g_emotes[id][nameCol])
                    return Markup._safeHtml(g_emotes[id][nameCol]);
                return LANG.types[501][0] + ' #' + id;
            }
        },
        enchantment:
        {
            empty: true,
            allowInReplies: true,
            attr:
            {
                unnamed: { req: true,  valid: /^[0-9]+$/ },
                domain:  { req: false, valid: MarkupDomainRegexMap.lang },
                site:    { req: false, valid: MarkupDomainRegexMap.lang }
            },
            validate: function(attr)
            {
                if((attr.domain || attr.site) && Markup.dbpage)
                    return false;
                return true;
            },
            toHtml: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var url = domainInfo[0];
                var nameCol = domainInfo[1];

                if(g_enchantments[id] && g_enchantments[id][nameCol])
                {
                    return '<a href="' + url + '?enchantment=' + id + '"' + Markup._addGlobalAttributes(attr) + '>' + Markup._safeHtml(g_enchantments[id][nameCol]) + '</a>';
                }
                return '<a href="' + url + '?enchantment=' + id + '"' + Markup._addGlobalAttributes(attr) + '>(' + LANG.types[502][0] + ' #' + id + ')</a>';
            },
            toText: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var nameCol = domainInfo[1];

                if(g_enchantments[id] && g_enchantments[id][nameCol])
                    return Markup._safeHtml(g_enchantments[id][nameCol]);
                return LANG.types[502][0] + ' #' + id;
            }
        },
        event:
        {
            empty: true,
            allowInReplies: true,
            attr:
            {
                unnamed: { req: true,  valid: /^-?[0-9]+$/ },
                domain:  { req: false, valid: MarkupDomainRegexMap.lang },
                site:    { req: false, valid: MarkupDomainRegexMap.lang }
            },
            allowedClass: MARKUP_CLASS_STAFF,
            validate: function(attr)
            {
                if((attr.domain || attr.site) && Markup.dbpage)
                    return false;
                return true;
            },
            toHtml: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var url = domainInfo[0];
                var nameCol = domainInfo[1];

                if(g_holidays[id] && g_holidays[id][nameCol])
                {
                    var evt = g_holidays[id];
                    return '<a href="' + url + '?event=' + id + '"' + Markup._addGlobalAttributes(attr) + '>' + Markup._safeHtml(evt[nameCol]) + '</a>';
                }
                return '<a href="' + url + '?event=' + id + '"' + Markup._addGlobalAttributes(attr) + '>(' + LANG.types[12][0] + ' #' + id + ')</a>';
            },
            toText: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var nameCol = domainInfo[1];

                if(g_holidays[id] && g_holidays[id][nameCol])
                    return Markup._safeHtml(g_holidays[id][nameCol]);
                return LANG.types[12][0] + ' #' + id;
            }
        },
        faction:
        {
            empty: true,
            allowInReplies: true,
            attr:
            {
                unnamed: { req: true,  valid: /^[0-9]+$/ },
                domain:  { req: false, valid: MarkupDomainRegexMap.lang },
                site:    { req: false, valid: MarkupDomainRegexMap.lang }
            },
            validate: function(attr)
            {
                if((attr.domain || attr.site) && Markup.dbpage)
                    return false;
                return true;
            },
            toHtml: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var url = domainInfo[0];
                var nameCol = domainInfo[1];

                if(g_factions[id] && g_factions[id][nameCol])
                {
                    var fac = g_factions[id];
                    return '<a href="' + url + '?faction=' + id + '"' + Markup._addGlobalAttributes(attr) + '>' + Markup._safeHtml(fac[nameCol]) + '</a>';
                }
                return '<a href="' + url + '?faction=' + id + '"' + Markup._addGlobalAttributes(attr) + '>(' + LANG.types[8][0] + ' #' + id + ')</a>';
            },
            toText: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var nameCol = domainInfo[1];

                if(g_factions[id] && g_factions[id][nameCol])
                    return Markup._safeHtml(g_factions[id][nameCol]);
                return LANG.types[8][0] + ' #' + id;
            }
        },
        feedback:
        {
            empty: true,
            allowedClass: MARKUP_CLASS_STAFF,
            attr:
            {
                mailto: { req: false, valid: /^true$/i }
            },
            toHtml: function(attr)
            {
                return '<b><span class="icontiny" style="background-image: url(' + g_staticUrl + '/images/icons/email.gif)"><a href="' + (attr.mailto ? 'mailto:feedback@wowhead.com' : 'javascript:;" onclick="ContactTool.show();') + '">feedback@wowhead.com</a></span></b>';
            }
        },
        forumrules:
        {
            empty: true,
            allowedClass: MARKUP_CLASS_STAFF,
            toHtml: function(attr)
            {
                return '<b><span class="icontiny" style="background-image: url(' + g_staticUrl + '/images/icons/favicon.gif)"><a href="http://www.wowhead.com/forums&topic=2">forum rules</a></span></b>';
            }
        },
        hr:
        {
            empty: true,
            trim: true,
            allowedModes: { article: 1, quickfacts: 1, comment: 1 },
            toHtml: function(attr)
            {
                return '<hr />';
            },
            fromHtml: function(str)
            {
                return str.replace(/<hr\b[\s\S]*?>/gi, '[hr]');
            }
        },
        h2:
        {
            block: true,
            empty: false,
            ltrim: true,
            rtrim: true,
            itrim: true,
            allowedClass: MARKUP_CLASS_STAFF,
            attr:
            {
                unnamed: { req: false, valid: /^first$/i },
                clear:   { req: false, valid: /^(true|both|left|right)$/i },
                style:   { req: false, valid: /^[^"<>]+$/ },
                toc:     { req: false, valid: /^false$/i }
            },
            toHtml: function(attr)
            {
                if(!attr.id)
                    attr.id = g_urlize(attr._textContents);
                str = '<h2' + Markup._addGlobalAttributes(attr);
                var classes = [];
                if(attr.first || attr.unnamed)
                    classes.push('first');
                if(attr.last)
                    classes.push('last');
                if(classes.length > 0)
                    str += ' class="' + classes.join(' ') + '"';
                var styles = [];
                if(attr.clear)
                {
                    if(attr.clear == 'true' || attr.clear == 'both')
                        styles.push('clear: both');
                    else
                        styles.push('clear: ' + attr.clear);
                }
                if(attr.style && Markup.allow >= Markup.CLASS_STAFF)
                    styles.push(attr.style);
                if(styles.length)
                    str += ' styles="' + styles.join(';') + '"';
                return [str + '>', '</h2>'];
            },
            fromHtml: function(str)
            {
                return str.replace(/<h2\b[\s\S]*?>([\s\S]*?)<\/h2>/gi, "\n[h2]$1[/h2]");
            }
        },
        h3:
        {
            block: true,
            empty: false,
            ltrim: true,
            rtrim: true,
            itrim: true,
            attr:
            {
                unnamed: { req: false, valid: /^first$/i },
                style:   { req: false, valid: /^[^"<>]+$/ },
                toc:     { req: false, valid: /^false$/i }
            },
            allowedClass: MARKUP_CLASS_STAFF,
            toHtml: function(attr)
            {
                if(!attr.id)
                    attr.id = g_urlize(attr._textContents);
                var str = '<h3' + Markup._addGlobalAttributes(attr);
                var classes = [];
                if(attr.first || attr.unnamed)
                    classes.push('first');
                if(attr.last)
                    classes.push('last');
                if(classes.length > 0)
                    str += ' class="' + classes.join(' ') + '"';
                var styles = [];
                if(attr.style && Markup.allow >= Markup.CLASS_STAFF)
                    styles.push(attr.style);
                if(styles.length)
                    str += ' styles="' + styles.join(';') + '"';
                return [str + '>', '</h3>'];
            },
            fromHtml: function(str)
            {
                return str.replace(/<h3\b[\s\S]*?>([\s\S]*?)<\/h3>/gi, "\n[h3]$1[/h3]");
            }
        },
        html:
        {
            empty: false,
            allowedClass: MARKUP_CLASS_ADMIN,
            allowedChildren: { '<text>': 1 },
            rawText: true,
            taglessSkip: true,
            toHtml: function(attr)
            {
                return [attr._contents];
            }
        },
        i:
        {
            empty: false,
            allowInReplies: true,
            toHtml: function(attr)
            {
                return ['<i' + Markup._addGlobalAttributes(attr) + '>', '</i>'];
            },
            fromHtml: function(str)
            {
                return str.replace(/<(i|em)\b[\s\S]*?>([\s\S]*?)<\/\1>/gi, '[i]$1[/i]');
            }
        },
        icon:
        {
            empty: false,
            itrim: true,
            attr:
            {
                align:   { req: false, valid: /^right$/i },
                'float': { req: false, valid: /^(left|right)$/i },
                name:    { req: false, valid: /\S+/ },
                size:    { req: false, valid: /^(tiny|small|medium|large)$/ },
                unnamed: { req: false, valid: /^class$/i },
                url:     { req: false, valid: /\S+/ },
                preset : { req: false, valid: /\S+/ }
            },
            allowedClass: MARKUP_CLASS_STAFF,
            presets:
            {
                boss:   g_staticUrl + '/images/icons/boss.gif',
                heroic: g_staticUrl + '/images/icons/heroic.gif'
            },
            validate: function(attr)
            {
                if(!attr.name && !attr.url && !attr.preset)
                    return false;
                if(attr.preset && !Markup.tags.icon.presets[attr.preset])
                    return false;
                return true;
            },
            toHtml: function(attr)
            {
                var size = (attr.size ? attr.size : "tiny");
                if(!attr.name) attr.name = '';

                if(size == "tiny") {
                    var str = '<span' + Markup._addGlobalAttributes(attr) + ' class="';
                    if(attr.unnamed == undefined)
                    {
                        str += 'icontiny';
                        if(attr.align)
                            str += 'r';

                        var src = '';
                        if(attr.name)
                            src = g_staticUrl + '/images/wow/icons/tiny/' + attr.name.toLowerCase() + '.gif';
                        else if(attr.preset)
                            src = Markup.tags.icon.presets[attr.preset];
                        else if(attr.url && Markup._isUrlSafe(attr.url))
                            src = attr.url;
                        else
                            return '';

                        str += '" style="background-image: url(' + src + ')">';
                    }
                    else
                        str += attr.name + '">';
                    return [str, '</span>'];
                }
                else
                {
                    var str = '<div' + Markup._addGlobalAttributes(attr) + ' onclick="Icon.showIconName(this)" class="icon' + size + ( attr['float'] ? '" style="float: '+ attr['float'] +';">' : '">' );

                    var sizes = {'small': 0, 'medium': 1, 'large': 2};
                    var url = null;
                    if(attr.url && Markup._isUrlSafe(attr.url))
                        url = attr.url;
                    else if(attr._textContents && Markup._isUrlSafe(attr._textContents))
                        url = attr._textContents;

                    icon = Icon.create(attr.name.toLowerCase(), sizes[size], null, url);

                    str += icon.innerHTML + '</div>';
                    return [str];
                }
            }
        },
        icondb:
        {
            empty: true,
            allowInReplies: true,
            attr:
            {
                unnamed:  { req: true,  valid: /^[0-9]+$/ },
                block:    { req: false, valid: /^(true|false)$/ },
                size:     { req: false, valid: /^(tiny|small|medium|large)$/ },
                name:     { req: false, valid: /^true$/ },
                domain:   { req: false, valid: MarkupDomainRegexMap.lang },
                site:     { req: false, valid: MarkupDomainRegexMap.lang },
                diff:     { req: false, valid: /^[0-9]+$/ },
                tempname: { req: false }
            },
            validate: function(attr)
            {
                if ((attr.domain || attr.site) && Markup.dbpage)
                    return false;
                return true
            },
            toHtml: function(attr) {
                var size = attr.size ? attr.size : 'small';
                var hasName = attr.name == 'true';
                var id = attr.unnamed;
                var domain = Markup._getDatabaseDomainInfo(attr);
                var url = domain[0];
                var href = url + '?icon=' + id;
                var rel = [];
                var tempname = null;

                if (attr.diff);
                    rel.push('diff=' + attr.diff);
                if (attr.tempname)
                    tempname = attr.tempname;

                if (g_icons[id] && g_icons[id].name)
                {
                    // href += '/' + $WH.urlize(g_icons[id].name);         AoWoW  - not used
                    var icon = g_icons[id];
                    if (hasName)
                    {
                        if (size == 'tiny')
                            return '<a href="' + href + '"' + (rel.length ? ' rel="' + rel.join('&') + '"' : '') + (!attr.icon ? ' class="icontiny"><img src="' + g_staticUrl + '/images/wow/icons/tiny/' + icon.icon.toLowerCase() + '.gif" align="absmiddle"' : '') + Markup._addGlobalAttributes(attr) + '> ' + Markup._safeHtml(icon.name) + '</a>'
                        else
                        {
                            var a = $WH.ce('a', { href: href });
                            var div = $WH.ce('div', null, a);
                            $WH.ae(a, Icon.create(icon.name, Icon.sizeIds[size], null, false, null, null, null, null, true));
                            a.innerHTML += Markup._safeHtml(icon.name);

                            return div.innerHTML;
                        }
                    }
                    else
                    {
                        var div = $WH.ce('div');
                        $WH.ae(div, Icon.create(icon.name, Icon.sizeIds[size], null, href, null, null, null, null, attr.block != 'true'));

                        return div.innerHTML;
                    }
                }

                return '<a href="' + href + '"' + (rel.length ? ' rel="' + rel.join('&') + '"' : '') + Markup._addGlobalAttributes(attr) + '>' + (tempname ? tempname : ('(' + LANG.types[29][0] + ' #' + id + ')')) + '</a>';
            },
            toText: function(attr)
            {
                var id = attr.unnamed;

                if(g_icons[id] && g_icons[id].name)
                    return Markup._safeHtml(g_icons[id].name);
                return LANG.types[29][0] + ' #' + id;
            }
        },
        iconlist:
        {
            empty: false,
            block: true,
            ltrim: true,
            rtrim: true,
            attr:
            {
                domain: { req: false, valid: MarkupDomainRegexMap.lang }
            },
            taglessSkip: true,
            allowedClass: MARKUP_CLASS_STAFF,
            allowedChildren: { b: 1, achievement: 1, currency: 1, faction: 1, holiday: 1, item: 1, itemset: 1, npc: 1, object: 1, pet: 1, quest: 1, spell: 1, title: 1, zone: 1 },
            toHtml: function(attr)
            {
                var domain = Markup._getDatabaseDomainInfo(attr)[2];

                var str = '', m;
                for(var i = 0; i < attr._nodes.length; ++i)
                {
                    var node = $WH.dO(attr._nodes[i]);
                    node.attr.domain = domain;

                    var html = Markup.tags[node.name].toHtml(node.attr),
                        type = node.name,
                        href = '',
                        icon = '';

                    if(typeof html != 'string') // Bold open/close tags
                        html = html[0] + node.attr._contents + html[1];
                    else if(typeof html == 'string' && (m = html.match(/href="(.+?)".+?url\(\/images\/wow\/icons\/tiny\/(.+?)\.gif\)/)))
                    {
                        node.attr.icon = 'false';
                        html = Markup.tags[node.name].toHtml(node.attr);
                        href = m[1];
                        icon = m[2];
                    }

                    if(html)
                        str += '<tr><th>' + (icon ? Markup.toHtml('[icon name=' + icon + ' size=small url=' + href + ']', { skipReset: true }) : '<ul><li>&nbsp;</li></ul>') + '</th><td>' + html + '</td></tr>';
                }

                if(str)
                    str = '<div class="iconlist-col"><table class="iconlist">' + str + '</table></div>';

                return [str];
            }
        },
        img:
        {
            empty: true,
            attr:
            {
                src:     { req: false, valid: /\S+/ },
                icon:    { req: false, valid: /\S+/ },
                id:      { req: false, valid: /^[0-9]+$/ },
                blog:    { req: false, valid: /^[0-9]+$/ },
                size:    { req: false, valid: /^(thumb|resized|normal|large|medium|small|tiny)$/i },
                width:   { req: false, valid: /^[0-9]+$/ },
                height:  { req: false, valid: /^[0-9]+$/ },
                'float': { req: false, valid: /^(left|right|center)$/i },
                border:  { req: false, valid: /^[0-9]+$/ },
                shadow:  { req: false, valid: /^(true|false)$/ },
                margin:  { req: false, valid: /^[0-9]+$/ }
            },
            blogSize: /^(thumb|normal)$/i,
            idSize: /^(thumb|resized|normal)$/i,
            iconSize: /^(large|medium|small|tiny)$/i,
            allowedClass: MARKUP_CLASS_STAFF,
            validate: function(attr)
            {
                if(attr.src)
                    return true;
                else if(attr.id)
                    return (attr.size ? Markup.tags.img.idSize.test(attr.size) : true);
                else if(attr.icon)
                    return (attr.size ? Markup.tags.img.iconSize.test(attr.size) : true);
                else if(attr.blog)
                    return (attr.size ? Markup.tags.img.blogSize.test(attr.size) : true);
                return false;
            },
            toHtml: function(attr)
            {
                var str = '<img' + Markup._addGlobalAttributes(attr);
                var classes = [];
                var styles = [];
                var post = '';

                if(attr.src)
                    str += ' src="' + attr.src + '"';
                else if(attr.id)
                    str += ' src="' + g_staticUrl + '/uploads/screenshots/' + (attr.size ? attr.size : 'normal') + '/' + attr.id + '.jpg"';
                else if(attr.icon)
                    str += ' src="' + g_staticUrl + '/images/wow/icons/' + (attr.size ? attr.size : 'large') + '/' + attr.icon + '.jpg"';
                else if(attr.blog)
                {
                    if(g_blogimages[attr.blog])
                    {
                        var img = g_blogimages[attr.blog];
                        if(attr.size && attr.size == 'thumb')
                        {
                            var url = g_staticUrl + '/uploads/blog/images/' + attr.blog + (img.type == 3 ? '.png' : '.jpg');
                            str += ' src="' + g_staticUrl + '/uploads/blog/thumbs/' + attr.blog + (img.type == 3 ? '.png' : '.jpg') + '" alt="' + Markup._safeHtml(img.alt) + '" width="' + img.thumbwidth + '" height="' + img.thumbheight + '"';

                            if(!g_screenshots[Markup.uid])
                                g_screenshots[Markup.uid] = [];
                            str = '<a href="' + url + '" onclick="if(!g_isLeftClick(event)) return; ScreenshotViewer.show({screenshots: \'' + Markup.uid + '\', pos: ' + g_screenshots[Markup.uid].length + '}); return false;">' + str;
                            post = '</a>';
                            var screenshot = {
                                url: url,
                                caption: img.alt,
                                width: img.width,
                                height: img.height,
                                noMarkup: true
                            };
                            g_screenshots[Markup.uid].push(screenshot);
                        }
                        else
                            str += ' src="' + g_staticUrl + '/uploads/blog/images/' + attr.blog + (img.type == 3 ? '.png' : '.jpg') + '" alt="' + Markup._safeHtml(img.alt) + '" width="' + img.width + '" height="' + img.height + '"';
                    }
                    else
                        return ('Image #' + attr.blog);
                }

                if(attr.width)
                    str += ' width="' + attr.width + '"';
                if(attr.height)
                    str += ' height="' + attr.height + '"';
                if(attr['float'])
                {
                    if(attr['float'] == 'center')
                    {
                        str = '<div style="text-align: center">' + str + ' style="margin: 10px auto"';
                        post = '</div>';
                    }
                    else
                    {
                        styles.push('float: ' + attr['float']);
                        if(!attr.margin)
                            attr.margin = 10;
                        if(attr['float'] == 'left')
                            styles.push('margin: 0 ' + attr.margin + 'px ' + attr.margin + 'px 0');
                        else
                            styles.push('margin: 0 0 ' + attr.margin + 'px ' + attr.margin + 'px');
                    }
                }
                if(attr.border)
                {
                    if (attr.border == 0)
                        classes.push('no-border');
                    else
                    {
                        classes.push('border');
                        styles.push('border-width:' + attr.border + 'px');
                    }
                }
                else
                {
                    classes.push('content-image');
                    if (attr.shadow == 'true')
                        classes.push('content-image-shadowed');
                }
                if(attr.title)
                    str += ' alt="' + attr.title + '"';
                else
                    str += ' alt=""';
                if (classes.length)
                    str += ' class="' + classes.join(' ') + '"';
                if (styles.length)
                    str += ' style="' + styles.join(';') + '"';
                str += ' />' + post;
                return str;
            },
            fromHtml: function(str)
            {
                var m;
                if(m = str.match(/<img\b[\s\S]*?src="[\s\S]+?"[\s\S]*?>/gi))
                {
                    for(var i = 0; i < m.length; ++i)
                    {
                        var source = m[i].match(/src="([\s\S]+?)"/i),
                            width  = m[i].match(/width[:="]+\s*([0-9]+)/i),
                            height = m[i].match(/height[:="]+\s*([0-9]+)/i),
                            border = m[i].match(/border[:="]+\s*([0-9]+)/i);

                        str = str.replace(m[i], '[img src=' + source[1] + (width ? ' width=' + width[1] : '') + (height ? ' height=' + height[1] : '') + ' border=' + (border ? border[1] : 0) + ']');
                    }
                }
                return str;
            }
        },
        ins:
        {
            empty: false,
            toHtml: function(attr)
            {
                return ['<ins class="diffmod"' + Markup._addGlobalAttributes(attr) + '>', '</ins>'];
            }
        },
        item:
        {
            empty: true,
            allowInReplies: true,
            attr:
            {
                unnamed:  { req: true,  valid: /^[0-9]+$/ },
                icon:     { req: false, valid: /^false$/i },
                domain:   { req: false, valid: MarkupDomainRegexMap.lang },
                site:     { req: false, valid: MarkupDomainRegexMap.lang },
                tempname: { req: false }
            },
            validate: function(attr)
            {
                if((attr.domain || attr.site) && Markup.dbpage)
                    return false;
                return true;
            },
            toHtml: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var url = domainInfo[0];
                var nameCol = domainInfo[1];
                var tempname = null;

                if(attr.tempname)
                    tempname = attr.tempname;
                if(g_items[id] && g_items[id][nameCol])
                {
                    var item = g_items[id];
                    var str = '<a' + Markup._addGlobalAttributes(attr) + ' href="' + url + '?item=' + id + '" class="q' + item.quality + (!attr.icon ? ' icontiny"><img src="' + g_staticUrl + '/images/wow/icons/tiny/' + item.icon.toLowerCase() + '.gif"' : '') + ' align="absmiddle" /> <span class="tinyicontxt">';
                    str += Markup._safeHtml(item[nameCol]) + '</span></a>';
                    return str;
                }
                return '<a href="' + url + '?item=' + id + '"' + Markup._addGlobalAttributes(attr) + '>' + (tempname ? tempname : ('(' + LANG.types[3][0] + ' #' + id + ')')) + '</a>';
            },
            toText: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var nameCol = domainInfo[1];

                if(g_items[id] && g_items[id][nameCol])
                    return Markup._safeHtml(g_items[id][nameCol]);
                return LANG.types[3][0] + ' #' + id;
            }
        },
        itemset:
        {
            empty: true,
            allowInReplies: true,
            attr:
            {
                unnamed: { req: true, valid: /^-?[0-9]+$/ },
                domain:  { req: false, valid: MarkupDomainRegexMap.lang },
                site:    { req: false, valid: MarkupDomainRegexMap.lang }
            },
            validate: function(attr)
            {
                if((attr.domain || attr.site) && Markup.dbpage)
                    return false;
                return true;
            },
            toHtml: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var url = domainInfo[0];
                var nameCol = domainInfo[1];

                if(g_itemsets[id] && g_itemsets[id][nameCol])
                {
                    var set = g_itemsets[id];
                    return '<a href="' + url + '?itemset=' + id + '"' + Markup._addGlobalAttributes(attr) + '>' + Markup._safeHtml(set[nameCol]) + '</a>';
                }
                return '<a href="' + url + '?itemset=' + id + '"' + Markup._addGlobalAttributes(attr) + '>(' + LANG.types[4][0] + ' #' + id + ')</a>';
            },
            toText: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var nameCol = domainInfo[1];

                if(g_itemsets[id] && g_itemsets[id][nameCol])
                    return Markup._safeHtml(g_itemsets[id][nameCol]);
                return LANG.types[4][0] + ' #' + id;
            }
        },
        li:
        {
            empty: false,
            itrim: true,
            allowedParents: { ul: 1, ol: 1 },
            attr:
            {
                "class": { req: false, valid: /^[a-z0-9 _-]+$/i },
                style:   { req: false,  valid: /^[^<>]+$/ }
            },
            helpText: function()
            {
                var str = '';
                str += '[ul]';
                for(var i = 0; i < 3; ++i)
                    str += '\n[li]' + LANG.markup_li + '[/li]';
                str += '\n[/ul]\n\n';
                str += '[ol]';
                for(var i = 0; i < 3; ++i)
                    str += '\n[li]' + LANG.markup_li + '[/li]';
                str += '\n[/ol]\n';
                return str.toLowerCase();
            },
            toHtml: function(attr)
            {
                return ['<li' + Markup._addGlobalAttributes(attr) + '><div>', '</div></li>'];
            },
            fromHtml: function(str, depth)
            {
                depth = depth || 0;

                var m;
                if(m = Markup.matchOuterTags(str, '<li\\b[\\s\\S]*?>', '</li>', 'g'))
                {
                    for(var i = 0; i < m.length; ++i)
                        str = str.replace(m[i][1] + m[i][0] + m[i][2], "\n\t" + Array(depth + 1).join("\t") + '[li]' + Markup.tags.li.fromHtml(m[i][0], depth + 1) + '[/li]');
                }

                return str;
            }
        },
        lightbox:
        {
            empty: false,
            allowedClass: MARKUP_CLASS_STAFF,
            attr:
            {
                unnamed: { req: true,  valid: /^(map|model|screenshot)$/ },
                zone:    { req: false, valid: /^-?[0-9]+[a-z]?$/i },
                floor:   { req: false, valid: /^[0-9]+$/ },
                pins:    { req: false, valid: /^[0-9]+$/ }
            },
            validate: function(attr)
            {
                switch(attr.unnamed)
                {
                    case 'map':
                        if(attr.zone)
                            return true;
                        break;
                    case 'model':
                        break;
                    case 'screenshot':
                        break;
                }
                return false;
            },
            toHtml: function(attr)
            {
                var url = '';
                var onclick = '';
                switch(attr.unnamed)
                {
                    case 'map':
                        url = '?maps=' + attr.zone;
                        if(attr.floor)
                            url += '.' + attr.floor;
                        if(attr.pins)
                            url += ':' + attr.pins;
                        var link = url.substr(6);

                        onclick = 'if(!g_isLeftClick(event)) return; MapViewer.show({ link: \'' + link + '\' }); return false;';
                        break;
                }

                if(url && onclick)
                    return ['<a href="' + url + '" onclick="' + onclick + '"' + Markup._addGlobalAttributes(attr) + '>', '</a>'];
                return '';
            }
        },
        map:
        {
            empty: false,
            attr:
            {
                zone:   { req: true, valid: /^-?[0-9a-z\-_]+$/i },
                source: { req: false, valid: /\S+/ }
            },
            allowedClass: MARKUP_CLASS_STAFF,
            allowedChildren: { pin: 1 },
            toHtml: function(attr)
            {
                var coords = attr._contents;
                attr.id = 'dsgdfngjkfdg' + (Markup.maps.length);
                var str = '<div' + Markup._addGlobalAttributes(attr) + '></div><div style="clear: left"></div>';

                Markup.maps.push([attr.id, attr.zone, coords]);

                return [ str ];
            }
        },
        n5:
        {
            empty: true,
            attr:
            {
                unnamed: { req: true, valid: /^[0-9\.]+$/ }
            },
            toHtml: function(attr)
            {
                return GetN5(attr.unnamed);
            }
        },
        pin:
        {
            empty: false,
            attr:
            {
                url:  { req: false, valid: /\S+/ },
                type: { req: false, valid: /^[0-9]+$/ },
                x:    { req: true, valid: /^[0-9]{1,2}(\.[0-9])?$/ },
                y:    { req: true, valid: /^[0-9]{1,2}(\.[0-9])?$/ },
                path: { req: false, valid: /^([0-9]{1,2}(\.[0-9])?[,:]?)+$/ }
            },
            taglessSkip: true,
            allowedClass: MARKUP_CLASS_STAFF,
            allowedParents: { map: 1 },
            toHtml: function(attr)
            {
                if(attr.url && !Markup._isUrlSafe(attr.url))
                    attr.url = '';
                var label = attr._contents;
                if(attr.url && attr.url.indexOf('npc=') != -1)
                    label = '<b class="q">' + label + '</b><br /><span class="q2">Click to view this NPC</span>';

                var lines = null;
                if(attr.path)
                {
                    var coords = attr.path.split(':'), lines = [];
                    for(var i = 0, len = coords.length; i < len; ++i)
                    {
                        var parts = coords[i].split(',');
                        if(parts.length == 2)
                            lines.push([parseFloat(parts[0] || 0), parseFloat(parts[1] || 0)]);
                    }
                }

                return [ [parseFloat(attr.x || 0), parseFloat(attr.y || 0), { label: label, url: attr.url, type: attr.type, lines: lines }] ];
                /*var str = '[' + parseFloat(attr.x || 0) + ',' + parseFloat(attr.y || 0) + ',{label: \'' + Markup._safeJsString(label) + '\'';
                if(attr.url)
                    str += ', url: \'' + Markup._safeJsString(Markup._fixUrl(attr.url)) + '\'';
                if(attr.type)
                    str += ', type: \'' + Markup._safeJsString(attr.type) + '\'';
                str += '}]';
                return [ [str] ];*/
            }
        },
        markupdoc:
        {
            empty: true,
            attr:
            {
                tag:  { req: false, valid: /[a-z0-9]+/i },
                help: { req: false, valid: /^(admin|staff|premium|user|pending)$/ }
            },
            allowedClass: MARKUP_CLASS_STAFF,
            validate: function(attr)
            {
                if(attr.tag && !Markup.tags[attr.tag])
                    return false;
                return true;
            },
            toHtml: function(attr)
            {
                var str = '',
                    helpClass = (attr.help ? Markup['CLASS_' + attr.help.toUpperCase()] : false);

                if(helpClass)
                    str += LANG.markup_helpdoc + '<div class="pad3"></div><table class="comment comment-markupdoc"><tr><th>' + LANG.markup_help1 + '</th><th>' + LANG.markup_help2 + '</th></tr>';

                if(attr.tag)
                    str = Markup._generateTagDocs(attr.tag, helpClass);
                else
                {
                    for(var tag in Markup.tags)
                    {
                        if(!helpClass && str != '')
                            str += '<div class="pad3"></div>';
                        str += Markup._generateTagDocs(tag, helpClass);
                    }
                }

                return str + (helpClass ? '</table>' : '');
            }
        },
        menu:
        {
            empty: true,
            trim: true,
            ltrim: true,
            rtrim: true,
            attr:
            {
                tab:  { req: true, valid: /^[0-9]+$/ },
                path: { req: true, valid: /\S+/ }
            },
            allowedClass: MARKUP_CLASS_STAFF,
            toHtml: function(attr)
            {
                var path = attr.path.split(',');
                PageTemplate.set({activeTab: attr.tab, breadcrumb: path});
            }
        },
        minibox:
        {
            empty: false,
            rtrim: true,
            itrim: true,
            attr:
            {
                'float': { req: false, valid: /^(left|right)$/i }
            },
            allowedClass: MARKUP_CLASS_STAFF,
            toHtml: function(attr)
            {
                var str = '<div' + Markup._addGlobalAttributes(attr) + ' class="minibox';
                if(attr['float'] == 'left')
                    str += ' minibox-left';
                str += '">';
                return [str, '</div>'];
            }
        },
        model:
        {
            empty: false,
            attr:
            {
                item:     { req: false, valid: /^[0-9]+$/ },
                object:   { req: false, valid: /^[0-9]+$/ },
                npc:      { req: false, valid: /^[0-9]+$/ },
                itemset:  { req: false, valid: /^[0-9,]+$/ },
                slot:     { req: false, valid: /^[0-9]+$/ },
                humanoid: { req: false, valid: /^1$/ },
                'float':  { req: false, valid: /^(left|right)$/i },
                border:   { req: false, valid: /^[0-9]+$/ },
                shadow:   { req: false, valid: /^(true|false)$/ },
                img:      { req: false, valid: /\S+/ },
                link:     { req: false, valid: /\S+/ },
                label:    { req: false, valid: /[\S ]+/ }
            },
            allowedClass: MARKUP_CLASS_STAFF,
            skipSlots: { 4: 1, 5: 1, 6: 1, 7: 1, 8: 1, 9: 1, 10: 1, 16: 1, 19: 1, 20: 1 },
            toHtml: function(attr)
            {
                var str = '';
                var classes = [];
                var styles = [];

                if(attr['float'])
                    classes.push('markup-float-' + attr['float']);
                if(attr.border)
                {
                    if(attr.border == 0)
                        classes.push('no-border');
                    else
                    {
                        classes.push('border');
                        styles.push('border-width:' + attr.border + 'px');
                    }
                }
                else
                {
                    classes.push('content-image');
                    if(attr.shadow == 'true')
                        classes.push('content-image-shadowed');
                }
                if(attr.npc)
                {
                    str = '<a' + Markup._addGlobalAttributes(attr) + ' href="#modelviewer:1:' + attr.npc + ':' + (attr.humanoid ? '1' : '0') +
                        '" onclick="ModelViewer.show({ type: 1, displayId: ' + attr.npc + ', slot: ' + attr.slot + ', ' + (attr.humanoid ? 'humanoid: 1, ' : '') +
                        'displayAd: 1, fromTag: 1' + (attr.link ? ", link: '" + Markup._safeJsString(attr.link) + "'" : '') + (attr.label ? ", label: '" + Markup._safeJsString(attr.label) + "'" : '') +
                        ' });"><img alt="' + Markup._safeHtml(attr._contents) + '" title="' + Markup._safeHtml(attr._contents) + '" src="' +
                        (attr.img ? attr.img : g_staticUrl + '/modelviewer/thumbs/npc/' + attr.npc + '.png" width="150" height="150') + ' ';
                    if(classes.length)
                        str += 'class="' + classes.join(' ') + '"';
                    if(styles.length)
                        str += ' style="' + styles.join(';') + '"';
                    str += '/></a>';
                    return [ str ];
                }
                else if(attr.object)
                {
                    str = '<a' + Markup._addGlobalAttributes(attr) + ' href="#modelviewer:2:' + attr.object + '" onclick="ModelViewer.show({ type: 2, displayId: ' +
                        attr.object + ', displayAd: 1, fromTag: 1' + (attr.link ? ", link: '" + Markup._safeJsString(attr.link) + "'" : '') + (attr.label ? ", label: '" + Markup._safeJsString(attr.label) + "'" : '') +
                        ' });"><img alt="' + Markup._safeHtml(attr._contents) + '" title="' + Markup._safeHtml(attr._contents) + '" src="' +
                        (attr.img ? attr.img : g_staticUrl + '/modelviewer/thumbs/obj/' + attr.object + '.png" width="150" height="150') + ' ';
                    if(classes.length)
                        str += 'class="' + classes.join(' ') + '"';
                    if(styles.length)
                        str += ' style="' + styles.join(';') + '"';
                    str += '/></a>';
                    return [ str ];
                }
                else if(attr.item && attr.slot)
                {
                    str = '<a' + Markup._addGlobalAttributes(attr) + ' href="#modelviewer:3:' + attr.item + ':' + attr.slot +
                        '" onclick="ModelViewer.show({ type: 3, displayId: ' + attr.item + ', slot: ' + attr.slot + ', displayAd: 1, fromTag: 1' +
                        (attr.link ? ", link: '" + Markup._safeJsString(attr.link) + "'" : '') + (attr.label ? ", label: '" + Markup._safeJsString(attr.label) + "'" : '') +
                        ' });"><img alt="' + Markup._safeHtml(attr._contents) + '" title="' + Markup._safeHtml(attr._contents) + '" src="' +
                        (attr.img ? attr.img : g_staticUrl + '/modelviewer/thumbs/item/' + attr.item + '.png" width="150" height="150') + ' ';
                    if(classes.length)
                        str += 'class="' + classes.join(' ') + '"';
                    if(styles.length)
                        str += ' style="' + styles.join(';') + '"';
                    str += '/></a>';
                    return [ str ];
                }
                else if(attr.itemset)
                {
                    str = '<a' + Markup._addGlobalAttributes(attr) + ' href="javascript:;" onclick="ModelViewer.show({ type: 4, equipList: [' + attr.itemset +
                        '], displayAd: 1, fromTag: 1' + (attr.link ? ", link: '" + Markup._safeJsString(attr.link) + "'" : '') + (attr.label ? ", label: '" + Markup._safeJsString(attr.label) + "'" : '') + ' });">';
                }
                else
                    return ['[model]', '[/model]'];
                return [str, '</a>'];
            }
        },
        money:
        {
            empty: true,
            attr:
            {
                unnamed:     { req: false, valid: /^[0-9]+$/ },
                side:        { req: false, valid: /^(alliance|horde|both)$/i },
                items:       { req: false, valid: /^[0-9,]+$/ },
                currency:    { req: false, valid: /^[0-9,]+$/ },
                achievement: { req: false, valid: /\S+/ },
                arena:       { req: false, valid: /^[0-9]+$/ },
                honor:       { req: false, valid: /^[0-9]+$/ },
            },
            allowedClass: MARKUP_CLASS_STAFF,
            toHtml: function(attr)
            {
                var items = [],
                    currency = [];

                if(attr.items)
                {
                    var split = attr.items.split(',');
                    if(split.length >= 2)
                        for(var i = 0; i < split.length-1; i += 2)
                            items.push([split[i], split[i+1]]);
                }

                if(attr.currency)
                {
                    var split = attr.currency.split(',');
                    if(split.length >= 2)
                        for(var i = 0; i < split.length-1; i += 2)
                            currency.push([split[i], split[i+1]]);
                }

                // Backwards compatability
                if(attr.honor)
                    currency.push([104, attr.honor]);
                if(attr.arena)
                    currency.push([103, attr.arena]);

                return g_getMoneyHtml(attr.unnamed, attr.side, items, currency, attr.achievement);
            }
        },
        npc:
        {
            empty: true,
            allowInReplies: true,
            attr:
            {
                unnamed: { req: true,  valid: /^[0-9]+$/ },
                domain:  { req: false, valid: MarkupDomainRegexMap.lang },
                site:    { req: false, valid: MarkupDomainRegexMap.lang }
            },
            validate: function(attr)
            {
                if((attr.domain || attr.site) && Markup.dbpage)
                    return false;
                return true;
            },
            toHtml: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var url = domainInfo[0];
                var nameCol = domainInfo[1];

                if(g_npcs[id] && g_npcs[id][nameCol])
                {
                    var npc = g_npcs[id];
                    return '<a href="' + url + '?npc=' + id + '"' + Markup._addGlobalAttributes(attr) + '>' + Markup._safeHtml(npc[nameCol]) + '</a>';
                }
                return '<a href="' + url + '?npc=' + id + '"' + Markup._addGlobalAttributes(attr) + '>(' + LANG.types[1][0] + ' #' + id + ')</a>';
            },
            toText: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var nameCol = domainInfo[1];

                if(g_npcs[id] && g_npcs[id][nameCol])
                    return Markup._safeHtml(g_npcs[id][nameCol]);
                return LANG.types[1][0] + ' #' + id;
            }
        },
        object:
        {
            empty: true,
            allowInReplies: true,
            attr:
            {
                unnamed: { req: true, valid: /^[0-9]+$/ },
                domain:  { req: false, valid: MarkupDomainRegexMap.lang },
                site:    { req: false, valid: MarkupDomainRegexMap.lang }
            },
            validate: function(attr)
            {
                if((attr.domain || attr.site) && Markup.dbpage)
                    return false;
                return true;
            },
            toHtml: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var url = domainInfo[0];
                var nameCol = domainInfo[1];

                if(g_objects[id] && g_objects[id][nameCol])
                {
                    var obj = g_objects[id];
                    return '<a href="' + url + '?object=' + id + '"' + Markup._addGlobalAttributes(attr) + '>' + Markup._safeHtml(obj[nameCol]) + '</a>';
                }
                return '<a href="' + url + '?object=' + id + '"' + Markup._addGlobalAttributes(attr) + '>(' + LANG.types[2][0] + ' #' + id + ')</a>';
            },
            toText: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var nameCol = domainInfo[1];

                if(g_objects[id] && g_objects[id][nameCol])
                    return Markup._safeHtml(g_objects[id][nameCol]);
                return LANG.types[2][0] + ' #' + id;
            }
        },
        ol:
        {
            block: true,
            empty: false,
            ltrim: true,
            rtrim: true,
            itrim: true,
            allowedChildren: { li: 1 },
            toHtml: function(attr)
            {
                var open = '<ol';
                var classes = [];
                if(attr.first)
                    classes.push('first');
                if(attr.last)
                    classes.push('last');
                if(classes.length > 0)
                    open += ' class="' + classes.join(' ') + '"';
                open += Markup._addGlobalAttributes(attr) + '>';
                return [open, '</ol>'];
            },
            fromHtml: function(str, depth)
            {
                depth = depth || 0;

                var m;
                if(m = Markup.matchOuterTags(str, '<ol\\b[\\s\\S]*?>', '</ol>', 'g'))
                {
                    for(var i = 0; i < m.length; ++i)
                    {
                        str = str.replace(m[i][1] + m[i][0] + m[i][2], "\n" + Array(depth + 1).join("\t") + '[ol]' + Markup.tags.ol.fromHtml(m[i][0], depth + 1) + "\n" + Array(depth + 1).join("\t") + '[/ol]');
                    }
                }

                return str;
            }
        },
        p:
        {
            empty: false,
            ltrim: true,
            rtrim: true,
            itrim: true,
            allowedClass: MARKUP_CLASS_STAFF,
            toHtml: function(attr)
            {
                return ['<p style="line-height: 1.4em; margin: 1em 0px 0px 0px;"' + Markup._addGlobalAttributes(attr) + '>','</p>'];
            },
            fromHtml: function(str)
            {
                var m;
                if(m = str.match(/<p\b[\s\S]*?>[\s\S]*?<\/p>/gi))
                {
                    for(var i = 0; i < m.length; ++i)
                    {
                        var align = m[i].match(/^<p\b[\s\S]*?text-align:\s*(center|left|right)/i);
                        var inside = m[i].match(/<p\b[\s\S]*?>([\s\S]*?)<\/p>/i);

                        str = str.replace(m[i], '[pad][div' + (align ? ' align=' + align[1] : '') + ']' + (inside ? inside[1] : '') + '[/div][pad]');

                    }
                }
                return str;
            }
        },
        pad:
        {
            empty: true,
            block: true,
            trim: true,
            allowedClass: MARKUP_CLASS_STAFF,
            toHtml: function(attr)
            {
                var str = '<div class="pad';
                if(attr.first)
                    str += ' first';
                if(attr.last)
                    str += ' last';
                str += '"' + Markup._addGlobalAttributes(attr) + '></div>';
                return str;
            }
        },
        pet:
        {
            empty: true,
            attr:
            {
                unnamed: { req: true, valid: /^[0-9]+$/ },
                icon:    { req: false, valid: /^false$/ },
                domain:  { req: false, valid: MarkupDomainRegexMap.lang },
                site:    { req: false, valid: MarkupDomainRegexMap.lang }
            },
            validate: function(attr)
            {
                if((attr.domain || attr.site) && Markup.dbpage)
                    return false;
                return true;
            },
            toHtml: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var url = domainInfo[0];
                var nameCol = domainInfo[1];

                if(g_pet_families && g_pet_families[id] && g_pets && g_pets[id])
                {
                    var str = '<span' + (!attr.icon ? ' class="icontiny" style="background-image: url(' + g_staticUrl + '/images/wow/icons/tiny/' + g_pets[id]['icon'].toLowerCase() + '.gif)' : '') + '">';
                    str += '<a href="' + url + '?pet=' + id + '"' + Markup._addGlobalAttributes(attr) + '>' + Markup._safeHtml(g_pet_families[id]) + '</a></span>';
                    return str;
                }
                return '<a href="' + url + '?pet=' + id + '"' + Markup._addGlobalAttributes(attr) + '>(' + LANG.types[9][0] + ' #' + id + ')</a>';
            },
            toText: function(attr)
            {
                var id = attr.unnamed;

                if(g_pet_families && g_pet_families[id])
                    return Markup._safeHtml(g_pet_families[id]);
                return LANG.types[9][0] + ' #' + id;
            }
        },
        pre:
        {
            empty: false,
            block: true,
            rtrim: true,
            toHtml: function(attr)
            {
                var open = '<pre class="code';
                if(attr.first)
                    open += ' first';
                if(attr.last)
                    open += ' last';
                open += '"' + Markup._addGlobalAttributes(attr) + '>';
                return [open, '</pre>'];
            },
            fromHtml: function(str)
            {
                return str.replace(/<pre\b[\s\S]*?>([\s\S]*?)<\/pre>/gi, '[pre]$1[/pre]');
            }
        },
        quest:
        {
            empty: true,
            allowInReplies: true,
            attr:
            {
                unnamed: { req: true, valid: /^[0-9]+$/ },
                icon:    { req: false, valid: /^false$/ },
                domain:  { req: false, valid: MarkupDomainRegexMap.lang },
                site:    { req: false, valid: MarkupDomainRegexMap.lang }
            },
            validate: function(attr)
            {
                if((attr.domain || attr.site) && Markup.dbpage)
                    return false;
                return true;
            },
            toHtml: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var url = domainInfo[0];
                var nameCol = domainInfo[1];

                if(g_quests[id] && g_quests[id][nameCol])
                {
                    var quest = g_quests[id];
                    return '<a href="' + url + '?quest=' + id + '"' + (!attr.icon ? ' class="icontiny"><img src="' + g_staticUrl + '/images/wow/icons/tiny/' + (quest.daily ? 'quest_start_daily' : 'quest_start') + '.gif"' : '') + Markup._addGlobalAttributes(attr) + ' align="absmiddle" /> <span class="tinyicontxt">' + Markup._safeHtml(quest[nameCol]) + '</span></a>';
                }
                return '<a href="' + url + '?quest=' + id + '"' + Markup._addGlobalAttributes(attr) + '>(' + LANG.types[5][0] + ' #' + id + ')</a>';
            },
            toText: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var nameCol = domainInfo[1];

                if(g_quests[id] && g_quests[id][nameCol])
                    return Markup._safeHtml(g_quests[id][nameCol]);
                return LANG.types[5][0] + ' #' + id;
            }
        },
        quote:
        {
            block: true,
            empty: false,
            rtrim: true,
            ltrim: true,
            itrim: true,
            attr:
            {
                unnamed:  { req: false, valid: /[\S ]+/ },
                url:      { req: false, valid: /\S+/ },
                blizzard: { req: false, valid: /^true$/ },
                pname:    { req: false },
                wowhead:  { req: false, valid: /^true$/ },
                display:  { req: false, valid: /^block$/ },
                align:    { req: false, valid: /^(left|right|center)$/i },
                "float":  { req: false, valid: /^(left|right|none)$/i },
                clear:    { req: false, valid: /^(left|right|both)$/i },
                collapse: { req: false, valid: /^true$/ }
            },
            allowedModes: { article: 1, quickfacts: 1, comment: 1 },
            validate: function(attr) {
                if(attr.blizzard || attr.wowhead || attr.collapse || attr.url)
                {
                    if(Markup.allow < Markup.CLASS_STAFF)
                        return false;
                }
                return true;
            },
            toHtml: function(attr)
            {
                var str = '<div' + Markup._addGlobalAttributes(attr);
                var styles = [];
                var classes = ['quote'];

                if(attr.display)
                    styles.push('display: ' + attr.display);
                if(attr.align)
                    styles.push('text-align: ' + attr.align);
                if (attr.clear)
                    styles.push('clear: ' + attr.clear);
                if(styles.length)
                    str += ' style="' + styles.join('; ') + '" ';

                if(attr.first)
                    classes.push('first');
                if(attr.last)
                    classes.push('last');
                if (attr['float'])
                    classes.push('markup-float-' + attr['float']);
                if(classes.length)
                    str += ' class="' + classes.join(' ');
                if(attr.blizzard) {
                    if(attr.unnamed && attr.blizzard)
                    {
                        var url = Markup._fixUrl(attr.url);
                        var pname = "View Original";

                        if (url.indexOf('bluetracker') >= 0) pname = 'Blue Tracker';
                        // override with attr.pname if it exists
                        if(typeof(attr.pname) != 'undefined') pname = attr.pname;

                        var username = attr.unnamed.trim();
                        if(username.length <= 0)
                            return ['',''];
                        str = str.replace('class="quote', 'class="quote-blizz');
                        str += (attr.collapse ? ' collapse' : '') + '"><div class="quote-header">'

                        var matches = url.match(/https?:\/\/(us|eu)\.battle\.net\/wow\/en\/blog\/([0-9]+)/i) || url.match(/https?:\/\/(us|eu)\.battle\.net\/wow\/en\/forum\/topic\/([0-9]+)/i);

                        if(matches) {
                            str += 'Originally posted by <strong>Blizzard</strong> (<a href="' + url + '" target="_blank">Official Post</a>'

                            var topicId = matches[2];
                            str += ' | <a href="http://www.wowhead.com/bluetracker?topic=' + topicId + '">Blue Tracker</a>)' +
                            '</div><div class="quote-body"><hr /><h2>' + username + '</h2>';
                        }
                        else
                            str += (
                                attr.url && Markup._isUrlSafe(attr.url) ?
                                'Originally posted by <strong>Blizzard</strong> ' +
                                '(<a href="' + Markup._fixUrl(attr.url) + '" target="_blank">' + pname + '</a>)' +
                                '</div><div class="quote-body"><hr />'
                                :'<h2>' + username + '</h2>'
                        );

                        return [str, '</div></div>'];

                    }
                    return ['',''];
                }
                else if(attr.wowhead /*Markup.inBlog*/)
                {
                    str = str.replace('class="quote', 'class="quote-wh');
                    str += (attr.collapse ? ' collapse' : '') + '">';
                    str += '<div class="quote-body">';
                    return [str, '</div></div>'];
                }
                else
                {
                    str += '">';
                    if(attr.unnamed)
                    {
                        var username = attr.unnamed.trim();
                        if(username.length > 0)
                        {
                            str += '<small><b>';
                            if(attr.url && Markup._isUrlSafe(attr.url))
                                str += '<a href="' + Markup._fixUrl(attr.url) + '"' + (Markup._isUrlExternal(attr.url) ? ' target="_blank"' : '') + '>' + username + '</a>';
                            else if(g_isUsernameValid(username))
                                str += '<a href="?user=' + username + '">' + username + '</a>';
                            else
                                str += username;
                            str += '</b> '+ LANG.markup_said + '</small><div class="pad"></div>';
                        }
                    }
                    return [str, '</div>'];
                }
            }
        },
        race:
        {
            empty: true,
            allowInReplies: true,
            valid: { 1: true, 2: true, 3: true, 4: true, 5: true, 6: true, 7: true, 8: true, 9: true, 10: true, 11: true, 22: true, 24: true, 25: true },
            attr:
            {
                unnamed: { req: true,  valid: /^[0-9]+$/ },
                gender:  { req: false, valid: /^(0|1)$/ },
                icon:    { req: false, valid: /^false$/ },
                domain:  { req: false, valid: MarkupDomainRegexMap.lang },
                site:    { req: false, valid: MarkupDomainRegexMap.lang }
            },
            validate: function(attr)
            {
                if((attr.domain || attr.site) && Markup.dbpage)
                    return false;
                if(Markup.tags.race.valid[attr.unnamed])
                    return true;
                return false;
            },
            toHtml: function(attr)
            {
                var id = attr.unnamed;
                var gender = attr.gender | 0;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var url = domainInfo[0];
                var nameCol = domainInfo[1];

                if(g_races[id] && g_races[id][nameCol])
                {
                    var race = g_races[id];
                    return '<a href="' + url + '?race=' + id + '"' + (!attr.icon ? ' class="icontiny"><img src="' + g_staticUrl + '/images/wow/icons/tiny/' + g_races.getIcon(id, gender) + '.gif"' : '') + Markup._addGlobalAttributes(attr) + ' align="absmiddle" /> <span class="tinyicontxt">' + Markup._safeHtml(race[nameCol]) + '</span></a>';
                }
                return '<a href="' + url + '?race=' + id + '"' + Markup._addGlobalAttributes(attr) + '>(' + LANG.types[14][0] + ' #' + id + ')</a>';
            },
            toText: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var nameCol = domainInfo[1];

                if(g_races[id] && g_races[id][nameCol])
                    return Markup._safeHtml(g_races[id][nameCol]);
                return LANG.types[14][0] + ' #' + id;
            }
        },
        reveal:
        {
            empty: false,
            rtrim: true,
            ltrim: true,
            allowedClass: MARKUP_CLASS_STAFF,
            toHtml: function(attr)
            {
                if(!Markup.inBlog || Markup.inBlog > 1)
                    return ['', ''];

                return ['<span id="reveal-' + Markup.reveals + '" style="display: none">', '</span> <a id="revealtoggle-' + Markup.reveals + '" class="revealtoggle" href="javascript:;" onclick="Markup.toggleReveal(' + Markup.reveals + ');">(read more)</a>'];
                Markup.reveals++;
            }
        },
        s:
        {
            empty: false,
            allowInReplies: true,
            toHtml: function(attr)
            {
                return ['<del' + Markup._addGlobalAttributes(attr) + '>', '</del>'];
            },
            fromHtml: function(str)
            {
                return str.replace(/<del\b[\s\S]*?>([\s\S]*?)<\/del>/gi, '[s]$1[/s]');
            }
        },
        screenshot:
        {
            empty: false,
            attr:
            {
                id:      { req: false, valid: /^[0-9]+$/ },
                url:     { req: false, valid: /\S+/ },
                thumb:   { req: false, valid: /\S+/ },
                size:    { req: false, valid: /^(thumb|resized|normal)$/i },
                width:   { req: false, valid: /^[0-9]+$/ },
                height:  { req: false, valid: /^[0-9]+$/ },
                'float': { req: false, valid: /^(left|right)$/i },
                border:  { req: false, valid: /^[0-9]+$/ },
                shadow:  { req: false, valid: /^(true|false)$/ }
            },
            taglessSkip: true,
            allowedClass: MARKUP_CLASS_STAFF,
            validate: function(attr)
            {
                if(attr.url && !attr.thumb)
                    return false;
                else if(!attr.id && !attr.url)
                    return false;
                return true;
            },
            toHtml: function(attr)
            {
                var classes = [];
                var styles = [];
                var url = '';
                var thumb = '';

                if(attr.id)
                {
                    url = g_staticUrl + '/uploads/screenshots/normal/' + attr.id + '.jpg';

                    var thumbId = attr.id;
                    if(attr.thumb && attr.thumb.match(/^[0-9]+$/))
                    {
                        thumbId = attr.thumb;
                        attr.thumb = null;
                    }

                    thumb = g_staticUrl + '/uploads/screenshots/' + (attr.size ? attr.size : 'thumb') + '/' + thumbId + '.jpg';
                }
                else if(attr.url)
                    url = attr.url;

                if(attr.thumb)
                    thumb = attr.thumb;

                var caption = attr._contents.replace(/\n/g, '<br />');

                if(!g_screenshots[Markup.uid])
                    g_screenshots[Markup.uid] = [];
                var str = '<a href="' + url + '" onclick="if(!g_isLeftClick(event)) return; ScreenshotViewer.show({screenshots: \'' + Markup.uid + '\', pos: ' + g_screenshots[Markup.uid].length + '}); return false;"' + Markup._addGlobalAttributes(attr) + '>';


                str += '<img src="' + thumb + '" ';
                if(attr.size && attr.width)
                    str += ' width="' + attr.width + '"';
                if(attr.size && attr.height)
                    str += ' height="' + attr.height + '"';
                if(attr.border)
                {
                    if(attr.border == 0)
                        classes.push('no-border');
                    else
                    {
                        classes.push('border');
                        styles.push('border-width:' + attr.border + 'px');
                    }
                }
                else
                {
                    classes.push('content-image');
                    if(attr.shadow == 'true')
                        classes.push('content-image-shadowed');
                }
                if(attr['float'])
                    classes.push('markup-float-' + attr['float']);

                if(classes.length)
                    str += ' class="' + classes.join(' ') + '"';
                if(styles.length)
                    str += ' style="' + styles.join(';') + '"';

                str += 'alt="" ';

                var screenshot = {
                    caption: caption,
                    width: (attr.size ? null : attr.width),
                    height: (attr.size ? null : attr.height),
                    noMarkup: true
                };
                if(attr.id)
                    screenshot.id = attr.id;
                else
                    screenshot.url = attr.url;
                g_screenshots[Markup.uid].push(screenshot);

                return [str + '/></a>'];
            }
        },
        script:
        {
            ltrim: true,
            rtrim: true,
            empty: false,
            attr:
            {
                src: { req: false, valid: /^\S+$/ }
            },
            allowedClass: MARKUP_CLASS_ADMIN,
            allowedChildren: { '<text>': 1 },
            rawText: true,
            taglessSkip: true,
            toHtml: function(attr)
            {
                if(attr.src)
                {
                    $.getScript(attr.src, function() {
                        $.globalEval(attr._contents);
                    });
                }
                else
                    $.globalEval(attr._contents);
                return [''];
            }
        },
        section:
        {
            empty: false,
            ltrim: true,
            rtrim: true,
            trim: true,
            allowedClass: MARKUP_CLASS_STAFF,
            attr:
            {
            },
            toHtml: function(attr)
            {
                return ['<div class="secheader"><var></var>', '</div>'];
            }
        },
        skill:
        {
            empty: true,
            allowInReplies: true,
            attr:
            {
                unnamed: { req: true,  valid: /^[0-9]+$/ },
                icon:    { req: false, valid: /^false$/ },
                domain:  { req: false, valid: MarkupDomainRegexMap.lang },
                site:    { req: false, valid: MarkupDomainRegexMap.lang }
            },
            validate: function(attr)
            {
                if((attr.domain || attr.site) && Markup.dbpage)
                    return false;
                return true;
            },
            toHtml: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var url = domainInfo[0];
                var nameCol = domainInfo[1];

                if(g_skills[id] && g_skills[id][nameCol])
                {
                    var skill = g_skills[id];
                    return '<a href="' + url + '?skill=' + id + '"' + (!attr.icon ? ' class="icontiny"><img src="' + g_staticUrl + '/images/wow/icons/tiny/' + g_skills.getIcon(id) + '.gif"' : '') + Markup._addGlobalAttributes(attr) + ' align="absmiddle" /> <span class="tinyicontxt">' + Markup._safeHtml(skill[nameCol]) + '</span></a>';
                }
                return '<a href="' + url + '?skill=' + id + '"' + Markup._addGlobalAttributes(attr) + '>(' + LANG.types[15][0] + ' #' + id + ')</a>';
            },
            toText: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var nameCol = domainInfo[1];

                if(g_skills[id] && g_skills[id][nameCol])
                    return Markup._safeHtml(g_skills[id][nameCol]);
                return LANG.types[15][0] + ' #' + id;
            }
        },
        sig:
        {
            empty: true,
            attr:
            {
                unnamed: { req: true, valid: /^[0-9]+$/ }
            },
            allowedClass: MARKUP_CLASS_PREMIUM,
            allowedModes: { signature: 1 },
            toHtml: function(attr)
            {
                return;
                return '<img' + Markup._addGlobalAttributes(attr) + ' src="?signature=generate&id=' + attr.unnamed + '.png" alt="" />';
            }
        },
        small:
        {
            empty: false,
            toHtml: function(attr)
            {
                return ['<small' + Markup._addGlobalAttributes(attr) + '>', '</small>'];
            },
            fromHtml: function(str)
            {
                return str.replace(/<small\b[\s\S]*?>([\s\S]*?)<\/small>/gi, '[small]$1[/small]');
            }
        },
        sound:
        {
            empty: true,
            attr:
            {
                unnamed: { req: false, valid: /^[0-9]+$/ },
                src:     { req: false, valid: /\S+/ },
                title:   { req: false, valid: /\S+/ },
                type:    { req: false, valid: /\S+/ },
                src2:    { req: false, valid: /\S+/ },
                type2:   { req: false, valid: /\S+/ },
                domain:  { req: false, valid: MarkupDomainRegexMap.lang }
            },
            validate: function (attr)
            {
                if(attr.unnamed)
                    return true;

                if(!attr.src)
                    return false;

                return true;
            },
            toHtml: function (attr)
            {
                var
                    type,
                    src,
                    title,
                    href,
                    src2,
                    type2;

                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var url = domainInfo[0];
                if (attr.unnamed)
                {
                    if (!(attr.unnamed in g_sounds))
                        return '';

                    if (!g_sounds[attr.unnamed].hasOwnProperty("files"))
                        return '';

                    if (g_sounds[attr.unnamed].files.length == 0)
                        return '';

                    if (!g_sounds[attr.unnamed].files[0].hasOwnProperty('type'))
                        return '';

                    type  = g_sounds[attr.unnamed].files[0].type;
                    src   = g_sounds[attr.unnamed].files[0].url;
                    title = g_sounds[attr.unnamed].name ? g_sounds[attr.unnamed].name: g_sounds[attr.unnamed].files[0].title;
                    href  = url + '?sound=' + attr.unnamed;
                }
                else
                {
                    if (Markup.allow < MARKUP_CLASS_STAFF)
                        return '';

                    src = attr.src;
                    title = attr.title ? attr.title: '(Unknown)';
                    if (attr.hasOwnProperty('type'))
                        type = attr.type;
                    else
                    {
                        switch (attr.src.toLowerCase().substr(-4))
                        {
                            case '.mp3':
                                type = 'audio/mpeg';
                                break;
                            case '.ogg':
                                type = 'audio/ogg; codecs="vorbis"';
                                break;
                        }
                    }

                    if (attr.src2)
                    {
                        src2 = attr.src2;
                        if (attr.hasOwnProperty('type2'))
                            type2 = attr.type2;
                        else
                        {
                            switch (attr.src2.toLowerCase().substr(-4))
                            {
                                case '.mp3':
                                    type2 = 'audio/mpeg';
                                    break;
                                case '.ogg':
                                    type2 = 'audio/ogg; codecs="vorbis"';
                                    break;
                            }
                        }
                    }
                }

                var str = '<audio preload="none" controls="true"' + Markup._addGlobalAttributes(attr);
                if (attr.unnamed)
                    str += ' rel="sound=' + attr.unnamed + '"';
                str += ">";

                if (!(/^(https?:)?\/\//).test(src))
                    src = g_staticUrl + "/wowsounds" + src;

                str += '<source src="' + src + '"';
                if (type)
                    str += ' type="' + type.replace(/"/g, "&quot;") + '"';
                str += ">";

                if (src2)
                {
                    str += '<source src="' + src2 + '"';
                    if (type2)
                        str += ' type="' + type2.replace(/"/g, "&quot;") + '"';
                    str += ">";
                }

                str += "</audio>";

                if (href)
                    str = '<div class="audiomarkup">' + str + '<br><a href="' + href + '">' + title + "</a></div>";

                return str;
            },
            fromHtml: function (str)
            {
                var m = str.match(/<audio [^>]*\brel="sound=(\d+)/);
                if (m)
                    return "[sound=" + m[1] + "]";

                return str.replace(/<audio\b[\s\S]*?><source\b[\s\S]*\bsrc="([^"]*?)"[\s\S]*?<\/audio>/gi, '[sound src="$1"]');
            }
        },
        span:
        {
            empty: false,
            attr:
            {
                "class":  { req: false, valid: /^[a-z0-9 _-]+$/i },
                style:    { req: false, valid: /^[^<>]+$/ },
                unnamed:  { req: false, valid: /^(hidden|invisible)$/ },
                tooltip:  { req: false, valid: /\S+/ },
                tooltip2: { req: false, valid: /\S+/ }
            },
            allowedClass: MARKUP_CLASS_STAFF,
            toHtml: function(attr)
            {
                var str = '<span' + Markup._addGlobalAttributes(attr);

                var styles = [];
                if(attr.unnamed == 'hidden')
                    styles.push('display: none');
                else if(attr.unnamed == 'invisible')
                    styles.push('visibility: hidden');

                if(styles.length > 0)
                    str += ' style="' + styles.join(';') + '"';

                if(attr.tooltip && Markup.tooltipTags[attr.tooltip])
                    str += ' onmouseover="$WH.Tooltip.showAtCursor(event, Markup.tooltipTags[\'' + attr.tooltip + '\'], 0, 0, ' + (Markup.tooltipBare[attr.tooltip] ? 'null' : "'q'") + ', ' + (attr.tooltip2 && Markup.tooltipTags[attr.tooltip2] ? "Markup.tooltipTags['" + attr.tooltip2 + "']" : 'null') + ')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"';

                str += '>';
                return [str, '</span>'];
            }
        },
        spell:
        {
            empty: true,
            allowInReplies: true,
            attr:
            {
                unnamed:  { req: true, valid: /^[0-9]+$/ },
                diff:     { req: false, valid: /^[0-9]+$/ },
                icon:     { req: false, valid: /^false$/ },
                domain:   { req: false, valid: MarkupDomainRegexMap.lang },
                site:     { req: false, valid: MarkupDomainRegexMap.lang },
                buff:     { req: false, valid: /^true$/ },
                tempname: { req: false }
            },
            validate: function(attr)
            {
                if((attr.domain || attr.site) && Markup.dbpage)
                    return false;
                return true;
            },
            toHtml: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var url = domainInfo[0];
                var nameCol = domainInfo[1];
                var rel = [];
                var tempname = null;

                if(attr.buff)
                    rel.push('buff');
                if(attr.diff)
                    rel.push('diff=' + attr.diff);
                if(attr.tempname)
                    tempname = attr.tempname;

                if(g_spells[id] && g_spells[id][nameCol])
                {
                    var spell = g_spells[id];
                    return '<a href="' + url + '?spell=' + id + '"' + (rel.length ? ' rel="' + rel.join('&') + '"' : '') + (!attr.icon ? ' class="icontiny"><img src="' + g_staticUrl + '/images/wow/icons/tiny/' + spell.icon.toLowerCase() + '.gif"' : '') + Markup._addGlobalAttributes(attr) + ' align="absmiddle" /> <span class="tinyicontxt">' + Markup._safeHtml(spell[nameCol]) + '</span></a>';
                }

                return '<a href="' + url + '?spell=' + id + '"' + (rel.length ? ' rel="' + rel.join('&') + '"' : '') + '>' + (tempname ? tempname : ('(' + LANG.types[6][0] + ' #' + id + ')')) + '</a>';
            },
            toText: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var nameCol = domainInfo[1];

                if(g_spells[id] && g_spells[id][nameCol])
                    return Markup._safeHtml(g_spells[id][nameCol]);
                return LANG.types[6][0] + ' #' + id;
            }
        },
        spoiler:
        {
            block: true,
            empty: false,
            rtrim: true,
            ltrim: true,
            itrim: true,
            toHtml: function(attr)
            {
                return ['<div class="pad"></div><small><b>' + LANG.markup_spoil + '</b></small><div' + Markup._addGlobalAttributes(attr) + ' class="spoiler">', '</div>'];
            }
        },
        statistic:
        {
            empty: true,
            attr:
            {
                unnamed: { req: true,  valid: /^[0-9]+$/ },
                icon:    { req: false, valid: /^false$/ },
                domain:  { req: false, valid: MarkupDomainRegexMap.lang },
                site:    { req: false, valid: MarkupDomainRegexMap.lang }
            },
            validate: function(attr)
            {
                if((attr.domain || attr.site) && Markup.dbpage)
                    return false;
                return true;
            },
            toHtml: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var url = domainInfo[0];
                var nameCol = domainInfo[1];

                if(g_achievements[id] && g_achievements[id][nameCol])
                {
                    var ach = g_achievements[id];
                    return '<a href="' + url + '?achievement=' + id + '"' + (!attr.icon ? ' class="icontiny"><img src="' + g_staticUrl + '/images/wow/icons/tiny/' + ach.icon.toLowerCase() + '.gif"' : '') + Markup._addGlobalAttributes(attr) + ' align="absmiddle" /> <span class="tinyicontxt">' + Markup._safeHtml(ach[nameCol]) + '</span></a>';
                }
                return '<a href="' + url + '?achievement=' + id + '"' + Markup._addGlobalAttributes(attr) + '>(' + LANG.types[10][0] + ' #' + id + ')</a>';
            },
            toText: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var url = domainInfo[0];
                var nameCol = domainInfo[1];

                if(g_achievements[id] && g_achievements[id][nameCol])
                    return Markup._safeHtml(g_achievements[id][nameCol]);
                return LANG.types[10][0] + ' #' + id;
            }
        },
        style:
        {
            ltrim: true,
            rtrim: true,
            empty: false,
            allowedClass: MARKUP_CLASS_ADMIN,
            allowedChildren: { '<text>': 1 },
            rawText: true,
            taglessSkip: true,
            toHtml: function(attr)
            {
                g_addCss(attr._contents);
                return [''];
            }
        },
        sub:
        {
            empty: false,
            toHtml: function(attr)
            {
                return ['<sub' + Markup._addGlobalAttributes(attr) + '>', '</sub>'];
            },
            fromHtml: function(str)
            {
                return str.replace(/<sub\b[\s\S]*?>([\s\S]*?)<\/sub>/gi, '[sub]$1[/sub]');
            }
        },
        sup:
        {
            empty: false,
            toHtml: function(attr)
            {
                return ['<sup' + Markup._addGlobalAttributes(attr) + '>', '</sup>'];
            },
            fromHtml: function(str)
            {
                return str.replace(/<sup\b[\s\S]*?>([\s\S]*?)<\/sup>/gi, '[sup]$1[/sup]');
            }
        },
        tabs:
        {
            block: true,
            empty: false,
            ltrim: true,
            rtrim: true,
            itrim: true,
            allowedClass: MARKUP_CLASS_STAFF,
            allowedChildren: { tab: 1 },
            attr:
            {
                name:  { req: true, valid: /\S+/ },
                width: { req: false, valid: /^[0-9]+(px|em|\%)$/ }
            },
            toHtml: function(attr) {
                attr.id = g_urlize(attr.name);
                var x = Markup.preview;
                var str = '<div class="clear"></div><div id="' + 'dsf67g4d-' + attr.id + (x ? '-preview' : '' ) + '"></div>';
                str += '<div';
                if(attr.width)
                    str += ' style="width: ' + attr.width + '"';
                str += '>';
                str += '<div class="tabbed-contents">';

                var tabs = attr._contents;

                for(var i = 0; i < tabs.length; ++i)
                {
                    var tab = tabs[i];

                    str += '<div id="tab-' + attr.id + '-' + tab.id + '" style="display: none">';
                    str += tab.content;
                    str += '<div class="clear"></div>';
                    str += '</div>';
                }

                str += '</div>';
                str += '</div>';

                setTimeout(Markup.createTabs.bind(null, attr, tabs, (x ? 'preview' : '')), 100);
                return [str];
            }
        },
        tab:
        {
            block: true,
            empty: false,
            ltrim: true,
            rtrim: true,
            itrim: true,
            allowedClass: MARKUP_CLASS_STAFF,
            allowedParents: { tabs: 1 },
            attr:
            {
                name: { req:true, valid: /[\S ]+/ },
                icon: { req:false, valid: /\S+/ }
            },
            toHtml: function(attr)
            {
                attr.id = g_urlize(attr.name);
                attr.name = $WH.str_replace(attr.name, "_", ' ');
                if(typeof(attr['class']) != 'undefined')
                    attr['class'] = $WH.str_replace(attr['class'], "_", ' ');
                return [{content: attr._contents, id: attr.id, name: attr.name, icon: attr.icon, 'class': attr['class']}];
            }
        },
        table:
        {
            //block: true,
            empty: false,
            ltrim: true,
            rtrim: true,
            itrim: true,
            allowedChildren: { tr: 1 },
            attr:
            {
                border:      { req: false, valid: /^[0-9]+$/ },
                cellspacing: { req: false, valid: /^[0-9]+$/ },
                cellpadding: { req: false, valid: /^[0-9]+$/ },
                width:       { req: false, valid: /^[0-9]+(px|em|\%)$/ }
            },
            toHtml: function(attr)
            {
                var str = '<table' + Markup._addGlobalAttributes(attr);
                if(attr.border != undefined)
                    str += ' border="' + attr.border + '"';
                if(attr.cellspacing != undefined)
                    str += ' cellspacing="' + attr.cellspacing + '"';
                if(attr.cellpadding != undefined)
                    str += ' cellpadding="' + attr.cellpadding + '"';
                if(attr.width != undefined)
                    str += ' style="width: ' + attr.width + '"';
                str += '><tbody>';
                return [str, '</tbody></table>'];
            },
            fromHtml: function(str, depth)
            {
                depth = depth || 0;

                var m;
                if(m = Markup.matchOuterTags(str, '<table\\b[\\s\\S]*?>', '</table>', 'g'))
                {
                    for(var i = 0; i < m.length; ++i)
                    {
                        var border  = m[i][1].match(/border[:="]+\s*([0-9]+)/i),
                            width   = m[i][1].match(/width[:="]+\s*([0-9]+)/i),
                            spacing = m[i][1].match(/cellspacing="([\s\S]+?)"/i),
                            padding = m[i][1].match(/cellpadding="([\s\S]+?)"/i);

                        str = str.replace(m[i][1] + m[i][0] + m[i][2], "\n" + Array(depth + 1).join("\t") + '[table' + (border ? ' border=' + border[1] : '') + (width ? ' width=' + width[1] : '') + (spacing ? ' cellspacing=' + spacing[1] : '') + (padding ? ' cellpadding=' + padding[1] : '') + ']' + Markup.tags.table.fromHtml(m[i][0], depth + 1) + "\n" + Array(depth + 1).join("\t") + '[/table]');
                    }
                }

                return str;
            }
        },
        tr:
        {
            empty: false,
            itrim: true,
            allowedChildren: { td: 1 },
            allowedParents: { table: 1 },
            toHtml: function(attr)
            {
                return ['<tr' + Markup._addGlobalAttributes(attr) + '>', '</tr>'];
            },
            fromHtml: function(str, depth)
            {
                depth = depth || 0;

                var m;
                if(m = Markup.matchOuterTags(str, '<tr\\b[\\s\\S]*?>', '</tr>', 'g'))
                {
                    for(var i = 0; i < m.length; ++i)
                    {
                        str = str.replace(m[i][1] + m[i][0] + m[i][2], "\n\t" + Array(depth + 1).join("\t") + '[tr]' + Markup.tags.tr.fromHtml(m[i][0], depth + 1) + "\n" + Array(depth + 1).join("\t") + '[/tr]');
                    }
                }

                return str;
            }
        },
        td:
        {
            empty: false,
            itrim: true,
            allowedParents: { tr: 1 },
            attr:
            {
                unnamed: { req: false, valid: /^header$/ },
                align:   { req: false, valid: /^(right|left|center|justify)$/i },
                valign:  { req: false, valid: /^(top|middle|bottom|baseline)$/i },
                colspan: { req: false, valid: /^[0-9]+$/ },
                rowspan: { req: false, valid: /^[0-9]+$/ },
                width:   { req: false, valid: /^[0-9]+(px|em|\%)$/ }
            },
            toHtml: function(attr)
            {
                var str = '<' + (attr.unnamed ? 'th' : 'td') + Markup._addGlobalAttributes(attr);
                if(attr.align != undefined)
                    str += ' align="' + attr.align + '"';
                if(attr.valign != undefined)
                    str += ' valign="' + attr.valign + '"';
                if(attr.colspan != undefined)
                    str += ' colspan="' + attr.colspan + '"';
                if(attr.rowspan != undefined)
                    str += ' rowspan="' + attr.rowspan + '"';
                if(attr.width != undefined)
                    str += ' style="width: ' + attr.width + '"';
                str += '>';
                return [str, '</' + (attr.unnamed ? 'th' : 'td') + '>'];
            },
            fromHtml: function(str, depth)
            {
                depth = depth || 0;

                var t = ['td', 'th'], m;
                for(var j = 0; j < t.length; ++j)
                {
                    if(m = Markup.matchOuterTags(str, '<' + t[j] + '\\b[\\s\\S]*?>', '</' + t[j] + '>', 'g'))
                    {
                        for(var i = 0; i < m.length; ++i)
                        {
                            var width   = m[i][1].match(/width[:="]+\s*([0-9]+)/i),
                                align   = m[i][1].match(/align="([\s\S]+?)"/i),
                                valign  = m[i][1].match(/valign="([\s\S]+?)"/i),
                                colspan = m[i][1].match(/colspan="([\s\S]+?)"/i),
                                rowspan = m[i][1].match(/rowspan="([\s\S]+?)"/i);

                            str = str.replace(m[i][1] + m[i][0] + m[i][2], "\n\t\t" + Array(depth + 1).join("\t") + '[td' + (t[j] == 'th' ? '=header' : '') + (width ? ' width=' + width[1] : '') + (align ? ' align=' + align[1] : '') + (valign ? ' valign=' + valign[1] : '') + (colspan ? ' colspan=' + colspan[1] : '') + (rowspan ? ' rowspan=' + rowspan[1] : '') + ']' + Markup.tags.td.fromHtml(m[i][0], depth + 1) + '[/td]');
                        }
                    }
                }

                return str;
            }
        },
        time:
        {
            empty: true,
            count: 0,
            attr:
            {
                until:  { req: false, valid: /^\d+$/ },
                since:  { req: false, valid: /^\d+$/ },
                server: { req: false, valid: /^true$/ }
            },
            validate: function(attr)
            {
                if(!attr.until && !attr.since)
                    return false;
                return true;
            },
            toHtml: function(attr)
            {
                var id = Markup.tags.time.count++;
                var str = '<span title="' + (new Date((attr.until ? attr.until : attr.since)*1000)).toLocaleString() + '" id="markupTime' + id + '">' + Markup.tags.time.getTime(attr) + '</span>';
                setInterval(Markup.tags.time.updateTime.bind(null, id, attr), 5000);
                return str;
            },
            getTime: function(attr)
            {
                var now;
                if(attr.server)
                    now = g_serverTime.getTime() / 1000;
                else
                    now = (new Date()).getTime() / 1000;
                var delay = 0;
                if(attr.until)
                    delay = attr.until - now;
                else
                    delay = now - attr.since;

                if(delay > 0)
                    return g_formatTimeElapsed(delay);
                else
                    return '0 ' + LANG.timeunitspl[6];
            },
            updateTime: function(id, attr)
            {
                var span = $WH.ge('markupTime' + id);
                if(!span)
                    return;

                span.firstChild.nodeValue = Markup.tags.time.getTime(attr);
            }
        },
        toc:
        {
            block: true,
            post: true,
            trim: true,
            ltrim: true,
            rtrim: true,
            collect: { h2: 1, h3: 1 },
            exclude: { tabs: { h2: 1, h3: 1 }, minibox: { h2: 1, h3: 1 } },
            allowedClass: MARKUP_CLASS_STAFF,
            attr:
            {
                h3: { req: false, valid: /^false$/ }
            },
            postHtml: function(attr, nodes)
            {
                var str = '<h3';
                var classes = [];
                if(attr.first)
                    classes.push('first');
                if(attr.last)
                    classes.push('last');
                if(classes.length > 0)
                    str += ' class="' + classes.join(' ') + '"';
                str += Markup._addGlobalAttributes(attr) + '>' + LANG.markup_toc + '</h3><ul>';
                var lastNode = "";
                var indent = 1;
                var allowH3 = (attr.h3 != 'false');
                var myNodes = [];
                for(var node in nodes.h2)
                    myNodes.push(nodes.h2[node]);
                for(var node in nodes.h3)
                    myNodes.push(nodes.h3[node]);
                myNodes.sort(function(a, b) {
                  return a.offset - b.offset;
                });

                for(var i in myNodes)
                {
                    node = myNodes[i];
                    if(node.name == 'h2' && node.attr.toc != 'false')
                    {
                        if(lastNode == 'h3')
                        {
                            str += '</ul>';
                            indent--;
                        }
                        str += '<li><b><a href=\'#' + (node.attr.id ? g_urlize(node.attr.id) : g_urlize(node.attr._textContents)) + '\'>' + node.attr._textContents + '</a></b></li>';
                        lastNode = 'h2';
                    }
                    if(node.name == 'h3' && allowH3 && node.attr.toc != 'false' && (lastNode != '' || nodes.h2.length == 0))
                    {
                        if(lastNode == 'h2')
                        {
                            str += '<ul>';
                            indent++;
                        }
                        str += '<li><b><a href=\'#' + (node.attr.id ? g_urlize(node.attr.id) : g_urlize(node.attr._textContents)) + '\'>' + node.attr._textContents + '</a></b></li>';
                        lastNode = 'h3';
                    }
                }
                for(var i = 0; i < indent; i++)
                {
                    str += '</ul>';
                }
                return str;
            }
        },
        toggler:
        {
            empty: false,
            attr:
            {
                id: { req: true, valid: /^[a-z0-9_-]+$/i },
                unnamed: { req: false, valid: /^hidden$/i }
            },
            allowedClass: MARKUP_CLASS_STAFF,
            toHtml: function(attr)
            {
                var str = '<a href="javascript:;" class="disclosure-' + (attr.unnamed ? 'off' : 'on') + '" onclick="return g_disclose($WH.ge(\'' + attr.id + '\'), this)">';
                return [str, '</a>'];
            }
        },
        tooltip:
        {
            empty: false,
            attr:
            {
                unnamed: { req: false, valid: /\S+/ },
                name:    { req: false, valid: /\S+/ },
                bare:    { req: false, valid: /^true$/i },
                label:   { req: false, valid: /[\S ]+/ }
            },
            taglessSkip: true,
            allowedClass: MARKUP_CLASS_STAFF,
            validate: function(attr)
            {
                if(!attr.unnamed && !attr.name)
                    return false;
                return true;
            },
            toHtml: function(attr)
            {
                if(attr.unnamed)
                    return ['<span class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, LANG[\'' + attr.unnamed + '\'], 0, 0, \'q\')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">', '</span>'];
                else
                {
                    Markup.tooltipTags[attr.name] = (attr.label ? '<table><tr><td class="q0" style="width: 300px"><small>' + attr.label + '</small></td></tr></table>' : '') + attr._contents;
                    if(attr.bare)
                        Markup.tooltipBare[attr.name] = true;
                    return [''];
                }
            }
        },
        u:
        {
            empty: false,
            allowInReplies: true,
            toHtml: function(attr)
            {
                return ['<ins' + Markup._addGlobalAttributes(attr) + '>', '</ins>'];
            },
            fromHtml: function(str)
            {
                return str.replace(/<(ins|u)\b[\s\S]*?>([\s\S]*?)<\/\1>/gi, '[u]$2[/u]');
            }
        },
        ul:
        {
            block: true,
            empty: false,
            ltrim: true,
            rtrim: true,
            itrim: true,
            allowedChildren: { li: 1 },
            toHtml: function(attr)
            {
                var open = '<ul';
                var classes = [];
                if(attr.first)
                    classes.push('first');
                if(attr.last)
                    classes.push('last');
                if(classes.length > 0)
                    open += ' class="' + classes.join(' ') + '"';
                open += Markup._addGlobalAttributes(attr) + '>';
                return [open, '</ul>'];
            },
            fromHtml: function(str, depth)
            {
                depth = depth || 0;

                var m;
                if(m = Markup.matchOuterTags(str, '<ul\\b[\\s\\S]*?>', '</ul>', 'g'))
                {
                    for(var i = 0; i < m.length; ++i)
                    {
                        str = str.replace(m[i][1] + m[i][0] + m[i][2], "\n" + Array(depth + 1).join("\t") + '[ul]' + Markup.tags.ul.fromHtml(m[i][0], depth + 1) + "\n" + Array(depth + 1).join("\t") + '[/ul]');
                    }
                }

                return str;
            }
        },
        url:
        {
            allowedClass: MARKUP_CLASS_USER,
            allowInReplies: true,
            empty: false,
            helpText: '[url=http://www.google.com]' + LANG.markup_url + '[/url]',
            attr:
            {
                unnamed:   { req: false, valid: /\S+/ },
                rel:       { req: false, valid: /(item|quest|spell|achievement|event|npc|object|itemset|currency)=([0-9]+)/ },
                onclick:   { req: false, valid: /[\S ]+/ },
                style:     { req: false, valid: /^[^"<>]+$/ },
                tooltip:   { req: false, valid: /\S+/ },
                tooltip2:  { req: false, valid: /\S+/ },
                newwindow: { req: false, valid: /^true$/ }
            },
            validate: function(attr)
            {
                if(attr.onclick && Markup.allow < Markup.CLASS_ADMIN)
                    return false;

                if(attr.tooltip && Markup.allow < Markup.CLASS_STAFF)
                    return false;

                var target = '';
                if(attr.unnamed && /^(mailto:|irc:)/i.test(attr.unnamed.trim()) && Markup.allow < Markup.CLASS_STAFF)
                    return false;

                if(attr.unnamed && /^(javascript:)/i.test(attr.unnamed.trim()))
                    return false;

                return true;
            },
            toHtml: function(attr)
            {
                var target;
                if(attr.unnamed) // in the form [url=blah]
                {
                    target = attr.unnamed;
                    target = target.replace(/&amp;/, '&');
                    if(!target.match(/^([^:\\.\/]+):/i) && target.charAt(0) != '/' && target.charAt(0) != '#')
                        target = '/' + target;
                    if(Markup._isUrlSafe(target, true))
                    {
                        var pre = '<a' + Markup._addGlobalAttributes(attr) + ' href="' + Markup._fixUrl(target) + '"';
                        if(Markup._isUrlExternal(target) || attr.newwindow)
                            pre += ' target="_blank"';
                        if(attr.rel)
                            pre += ' rel="' + attr.rel + '"';
                        if(attr.onclick)
                            pre += ' onclick="' + attr.onclick + '"';
                        if(attr.tooltip && Markup.tooltipTags[attr.tooltip])
                            pre += ' onmouseover="$WH.Tooltip.showAtCursor(event, Markup.tooltipTags[\'' + attr.tooltip + '\'], 0, 0, ' + (Markup.tooltipBare[attr.tooltip] ? 'null' : "'q'") + ', ' + (attr.tooltip2 && Markup.tooltipTags[attr.tooltip2] ? "Markup.tooltipTags['" + attr.tooltip2 + "']" : 'null') + ')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"';
                        if(attr.style && Markup.allow >= Markup.CLASS_STAFF)
                            pre += ' style="' + attr.style + '"';
                        pre += '>';
                        return [pre, '</a>'];
                    }
                    else
                    {
                        return ['', ''];
                    }
                }
                else // [url]blah[/url]
                {
                    target = attr._textContents;
                    target = target.replace(/&amp;/, '&');
                    if(Markup._isUrlSafe(target))
                    {
                        var pre = '<a' + Markup._addGlobalAttributes(attr) + ' href="' + Markup._fixUrl(target) + '"';
                        if(Markup._isUrlExternal(target))
                            pre += ' target="_blank"';
                        if(attr.rel)
                            pre += ' rel="' + attr.rel + '"';
                        if(attr.onclick)
                            pre += ' onclick="' + attr.onclick + '"';
                        pre += '>';
                        return [pre + target + '</a>'];
                    }
                    else
                    {
                        return ['', ''];
                    }
                }
            },
            fromHtml: function(str)
            {
                return str.replace(/<a\b[\s\S]*?href=\"(.+?)\"[\s\S]*?>([\s\S]*?)<\/a>/gi, '[url=$1]$2[\/url]');
            }
        },
        video:
        {
            empty: true,
            attr:
            {
                id:      { req: true,  valid: /^[0-9]+$/ },
                unnamed: { req: false, valid: /^embed$/i },
                'float': { req: false, valid: /^(left|right)$/i }, // Thumbnail only
                shadow:  { req: false, valid: /^(true|false)$/ }, // Thumbnail only
                border:  { req: false, valid: /^[0-9]+$/ } // Thumbnail only
            },
            ltrim: true,
            rtrim: true,
            allowedClass: MARKUP_CLASS_STAFF,
            toHtml: function(attr)
            {
                if(g_videos[attr.id])
                {
                    var html = '', video = g_videos[attr.id];
                    var classes = [];
                    var styles = [];
                    if(attr.unnamed)
                    {
                        if(video.videoType == 1) // YouTube
                            html += Markup.toHtml('[youtube=' + video.videoId + ']', { skipReset: true });
                    }
                    else
                    {
                        if(!g_videos[Markup.uid])
                            g_videos[Markup.uid] = [];

                        html += '<div style="position: relative; display: -moz-inline-stack; display: inline-block; zoom: 1; *display: inline"><a href="' + $WH.sprintf(vi_siteurls[video.videoType], video.videoId) + '" onclick="if(!g_isLeftClick(event)) return; VideoViewer.show({videos: \'' + Markup.uid + '\', pos: ' + g_videos[Markup.uid].length + '}); return false;"' + Markup._addGlobalAttributes(attr) + '>';
                        html += '<img src="' + $WH.sprintf(vi_thumbnails[video.videoType], video.videoId) + '" ';

                        if(attr.border)
                        {
                            if(attr.border == 0)
                                classes.push('no-border');
                            else
                            {
                                classes.push('border');
                                styles.push('border-width:' + attr.border + 'px');
                            }
                        }
                        else
                        {
                            classes.push('content-image');
                            if(attr.shadow == 'true')
                                classes.push('content-image-shadowed');
                        }
                        if(attr['float'])
                            classes.push('markup-float-' + attr['float']);
                        if(classes.length)
                            html += 'class="' + classes.join(' ') + '" ';
                        if(styles.length)
                            html += ' style="' + styles.join(';') + '"';
                        if(video.hasCaption)
                            html += 'alt="' + Markup.removeTags(video.caption, { mode: Markup.MODE_SIGNATURE, skipReset: true }) + '" ';

                        html += '/><img src="' + g_staticUrl + '/images/icons/play-sm.png" style="opacity: 0.6; filter:alpha(opacity=60); position: absolute; width: 48px; height: 48px; top: 23px; left: 38px" />';
                        html += '</a></div>';

                        g_videos[Markup.uid].push($WH.dO(video));
                    }
                    return html;
                }
                return '<b>Video #' + attr.id + '</b>';
            }
        },
        visitedpage:
        {
            empty: false,
            attr:
            {
                unnamed: { req: true, valid: /^[0-9]+$/ }
            },
            allowedClass: MARKUP_CLASS_STAFF,
            toHtml: function(attr)
            {
                $.post('/visited-page', { id: attr.unnamed }, function() { AchievementCheck(); });
                return '';
            }
        },
        wowheadresponse:
        {
            block: true,
            empty: false,
            rtrim: true,
            ltrim: true,
            itrim: true,
            attr:
            {
                unnamed: { req: true, valid: /[\S ]+/ },
                roles:   { req: true, valid: /[0-9]+/ }
            },
            allowedModes: { article: 1, quickfacts: 1, comment: 1 },
            allowedClass: MARKUP_CLASS_STAFF,
            toHtml: function(attr)
            {
                var str = '<div' + Markup._addGlobalAttributes(attr);
                var styles = [];

                str += ' class="quote ';
                if(attr.first)
                    str += 'firstmargin ';
                if(attr.last)
                    str == 'last ';

                var username = attr.unnamed.trim();
                if(username.length <= 0)
                    return ['',''];

                var postColor = '';
                if(attr.roles & U_GROUP_ADMIN)
                    postColor = 'comment-blue';
                else
                    postColor = 'comment-green';

                if(g_customColors[username])
                    postColor = 'comment-' + g_customColors[username];

                str += postColor + '"><small class="icon-wowhead"><b class="' + postColor + '"><a href="?user=' + username + '">' + username + '</a></b> ' + LANG.markup_said + '</small><div class="pad"></div>';
                return [str, '</div>'];
            }
        },
        youtube:
        {
            empty: true,
            attr:
            {
                unnamed:  { req: true,  valid: /\S+/ },
                width:    { req: false, valid: /^[0-9]+$/ },
                height:   { req: false, valid: /^[0-9]+$/ },
                autoplay: { req: false, valid: /^true$/ }
            },
            allowedClass: MARKUP_CLASS_STAFF,
            toHtml: function(attr)
            {
                var width = attr.width ? attr.width : 640;
                var height = attr.height ? attr.height : 385;
                if (window.outerWidth && window.outerWidth - 20 < width) {
                    width = window.outerWidth - 20;
                    height = Math.floor(width * 0.6015625);
                }

                var ytVars = {
                    height:     height,
                    width:      width,
                    videoId:    attr.unnamed,
                    playerVars: { autoplay: (attr.autoplay ? 1 : 0) }
                };

                if (Markup.youtube.players.length == 0)
                {
                    var onYTReady;
                    if ($WH.isset('onYouTubeIframeAPIReady') && (typeof window.onYouTubeIframeAPIReady == 'function'))
                        onYTReady = window.onYouTubeIframeAPIReady;
                    else
                        onYTReady = function () {};

                    window.onYouTubeIframeAPIReady = function ()
                    {
                        Markup.youtube.APIReady();
                        onYTReady();
                    };

                    if (!$WH.isset('YT'))
                    {
                        window.YT = {};
                        $WH.g_ajaxIshRequest('https://www.youtube.com/iframe_api', true);
                    }
                }
                var idx = Markup.youtube.players.length;
                var player = {
                    id:          idx,
                    containerId: 'yt-markup-container-' + idx,
                    yt:          ytVars,
                    loaded:      false
                };

                Markup.youtube.players.push(player);
                if (Markup.youtube.APIIsReady || ($WH.isset('YT') && YT.hasOwnProperty('Player')))
                {
                    if (typeof Markup.youtube.readyTimeout == 'undefined')
                        Markup.youtube.readyTimeout = window.setTimeout(Markup.youtube.APIReady, 10);
                }

                var html = '<div class="yt-markup-container" id="yt-markup-container-' + idx + '" style="width: ' + width + "px; height: " + height + 'px;"></div>';

                return html;
            },
            fromHtml: function(str)
            {
                var m;
                if(m = str.match(/<iframe\b[\s\S]*?src="[\s\S]*?youtube\.com\/embed\/[\s\S]*?"[\s\S]*?><\/iframe>/gi))
                {
                    for(var i = 0; i < m.length; ++i)
                    {
                        var source = m[i].match(/src="[\s\S]*?youtube\.com\/embed\/([\s\S]*?)"/i),
                            width  = m[i].match(/width[:="]+\s*([0-9]+)/i),
                            height = m[i].match(/height[:="]+\s*([0-9]+)/i),
                            border = m[i].match(/border[:="]+\s*([0-9]+)/i);

                        str = str.replace(m[i], '[youtube=' + source[1] + (width ? ' width=' + width[1] : '') + (height ? ' height=' + height[1] : '') + ']');
                    }
                }
                return str;
            }
        },
        center:
        {
            empty: false,
            allowInReplies: false,
            allowedClass: MARKUP_CLASS_STAFF,
            toHtml: function(attr)
            {
                return ['<center>', '</center>'];
            },
            fromHtml: function(str)
            {
                return str.replace(/<center>([\s\S]+?)<\/center>/gi, '[pad][div align=center]$1[/div][pad]');
            }
        },
        title:
        {
            empty: true,
            allowInReplies: true,
            attr:
            {
                unnamed: { req: true,  valid: /^[0-9]+$/ },
                domain:  { req: false, valid: MarkupDomainRegexMap.lang },
                site:    { req: false, valid: MarkupDomainRegexMap.lang }
            },
            validate: function(attr)
            {
                if((attr.domain || attr.site) && Markup.dbpage)
                    return false;
                return true;
            },
            toHtml: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var url = domainInfo[0];
                var nameCol = domainInfo[1];

                if(g_titles[id] && g_titles[id][nameCol])
                {
                    return '<a href="' + url + '?title=' + id + '"' + Markup._addGlobalAttributes(attr) + '>' + Markup._safeHtml(g_titles[id][nameCol]) + '</a>';
                }
                return '<a href="' + url + '?title=' + id + '"' + Markup._addGlobalAttributes(attr) + '>(' + LANG.types[11][0] + ' #' + id + ')</a>';
            },
            toText: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var nameCol = domainInfo[1];

                if(g_titles[id] && g_titles[id][nameCol])
                    return Markup._safeHtml(g_titles[id][nameCol]);
                return LANG.types[11][0] + ' #' + id;
            }
        },
        zone:
        {
            empty: true,
            allowInReplies: true,
            attr:
            {
                unnamed: { req: true,  valid: /^[0-9]+$/ },
                domain:  { req: false, valid: MarkupDomainRegexMap.lang },
                site:    { req: false, valid: MarkupDomainRegexMap.lang }
            },
            validate: function(attr)
            {
                if((attr.domain || attr.site) && Markup.dbpage)
                    return false;
                return true;
            },
            toHtml: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var url = domainInfo[0];
                var nameCol = domainInfo[1];

                if(g_gatheredzones[id] && g_gatheredzones[id][nameCol])
                {
                    return '<a href="' + url + '?zone=' + id + '"' + Markup._addGlobalAttributes(attr) + '>' + Markup._safeHtml(g_gatheredzones[id][nameCol]) + '</a>';
                }
                return '<a href="' + url + '?zone=' + id + '"' + Markup._addGlobalAttributes(attr) + '>(' + LANG.types[7][0] + ' #' + id + ')</a>';
            },
            toText: function(attr)
            {
                var id = attr.unnamed;
                var domainInfo = Markup._getDatabaseDomainInfo(attr);
                var nameCol = domainInfo[1];

                if(g_gatheredzones[id] && g_gatheredzones[id][nameCol])
                    return Markup._safeHtml(g_gatheredzones[id][nameCol]);
                return LANG.types[7][0] + ' #' + id;
            }
        }
    },

    _addGlobalAttributes: function(attr)
    {
        var attribs = '';
        if(Markup.allow < Markup.CLASS_STAFF)
            return attribs;
        if(attr.id)
            attribs += ' id="' + attr.id + '"';
        if(attr.title)
            attribs += ' title="' + Markup._safeQuotes(attr.title) + '"';
        if(attr['class'])
            attribs += ' class="' + attr['class'] + '"';
        if(attr.style)
            attribs += ' style="' + attr.style + '"';
        if(attr['data-highlight'])
            attribs += ' data-highlight="' + attr['data-highlight'] + '"';

        return attribs;
    },

    _generateTagDocs: function(tagName, helpClass)
    {
        var tag = Markup.tags[tagName];
        if(!tag)
            return '';

        if(helpClass)
        {
            if((tag.allowedClass && tag.allowedClass > helpClass) || (!tag.helpText && (tag.empty || tag.allowedParents || tag.allowedChildren || !LANG['markup_' + tagName])))
                return '';

            if(tag.helpText && typeof tag.helpText == 'function')
                var str = tag.helpText();
            else if(tag.helpText && typeof tag.helpText == 'string')
                var str = tag.helpText;
            else
                var str = '[' + tagName + ']' + LANG['markup_' + tagName].toLowerCase() + '[/' + tagName + ']';

            return '<tr><td><pre>' + str + '</pre></td><td>' + Markup.toHtml(str, { skipReset: true }) + '</td></tr>';
        }

        var str = '<div><h3 class="first">Tag: [' + Markup._safeHtml(tagName) + ']</h3>';
        str += '<table class="grid">';

        if(tag.attr)
        {
            /*str += '<tr><td align="right" width="200">Attributes:</td><td><table>';
            for(var a in tag.attr)
            {
                str += '<tr><th style="background-color: #242424; font-weight: bolder" colspan="2">' + a + '</th></tr>';
                str += '<tr><td align="right">Required:</td><td>' + (tag.attr[a].req ? 'Yes' : 'No') + '</td></tr>';
                str += '<tr><td align="right">Valid:</td><td>' + (tag.attr[a].valid ? Markup._safeHtml(tag.attr[a].valid.toString()) : '--') + '</td></tr>';
            }
            str += '</table></td></tr>';*/
            str += '<tr><td align="right" width="200">Attributes:</td><td>';
            for(var a in tag.attr)
            {
                str += '<div style="margin: 5px; display: inline-block"><table><tr><th style="background-color: #242424; font-weight: bolder" colspan="2">';
                if(a == 'unnamed')
                    str += 'Self ([' + tagName + '=???])';
                else
                    str += a;
                str += '</th></tr>';
                str += '<tr><td align="right">Required:</td><td>' + (tag.attr[a].req ? 'Yes' : 'No') + '</td></tr>';
                str += '<tr><td align="right">Valid:</td><td>' + (tag.attr[a].valid ? Markup._safeHtml(tag.attr[a].valid.toString()) : '--') + '</td></tr></table></div>';
            }
            str += '</td></tr>';
        }

        str += '<tr><td align="right" width="200">Has closing tag:</td><td>' + (tag.empty ? 'No' : 'Yes') + '</td></tr>';

        str += '<tr><td align="right">Required group:</td><td>';
        if(tag.allowedClass == MARKUP_CLASS_ADMIN)
            str += 'Administrator';
        else if(tag.allowedClass == MARKUP_CLASS_STAFF)
            str += 'Staff';
        else if(tag.allowedClass == MARKUP_CLASS_PREMIUM)
            str += 'Premium';
        else if(tag.allowedClass && tag.allowedClass != MARKUP_CLASS_PENDING)
            str += 'Not pending';
        else
            str += 'None';
        str += '</td></tr>';

        if(tag.allowedChildren)
        {
            str += '<tr><td align="right">Allowed children:</td><td>';
            for(var t in tag.allowedChildren)
                str += Markup._safeHtml(t) + '<br />';
            str += '</td></tr>';
        }

        if(tag.allowedParents)
        {
            str += '<tr><td align="right">Allowed parents:</td><td>';
            for(var t in tag.allowedParents)
                str += Markup._safeHtml(t) + '<br />';
            str += '</td></tr>';
        }

        if(tag.presets)
        {
            str += '<tr><td align="right">Preset values:</td><td><table>';
            for(var p in tag.presets)
                str += '<tr><td align="right">' + p + '</td><td>' + Markup._safeHtml(tag.presets[p]) + '</td></tr>';
            str += '</table></td></tr>';
        }

        if(tag.trim)
        {
            str += '<tr><td colspan="2">Trim whitespace</td></tr>';
        }

        if(tag.ltrim)
        {
            str += '<tr><td colspan="2">Trim preceding whitespace</td></tr>';
        }

        if(tag.rtrim)
        {
            str += '<tr><td colspan="2">Trim following whitespace</td></tr>';
        }

        if(tag.itrim)
        {
            str += '<tr><td colspan="2">Trim whitespace around interior content</td></tr>';
        }

        if(tag.block)
        {
            str += '<tr><td colspan="2">Automatically remove top padding if not the first item</td></tr>';
        }

        str += '</table></div>';

        return str;
    },

    _init: function()
    {
        if(!this.inited)
        {
            var ltrimTags = [], rtrimTags = [], trimTags = [];
            for(var tag in Markup.tags)
            {
                if(Markup.tags[tag].block)
                    this.firstTags[tag] = true;
                if(Markup.tags[tag].exclude)
                {
                    for(var ex in Markup.tags[tag].exclude)
                    {
                        if(!this.excludeTags[ex])
                            this.excludeTags[ex] = {};
                        this.excludeTags[ex][tag] = Markup.tags[tag].exclude[ex];
                    }
                }
                if(Markup.tags[tag].post)
                    this.postTags.push(tag);
                if(Markup.tags[tag].trim)
                    trimTags.push(tag);
                if(Markup.tags[tag].ltrim)
                    ltrimTags.push(tag);
                if(Markup.tags[tag].rtrim)
                    rtrimTags.push(tag);
            }

            if(ltrimTags.length > 0)
                this.ltrimRegex = new RegExp('\\s*\\[(' + ltrimTags.join('|') + ')([^a-z0-9]+.*)?]', 'ig');
            if(rtrimTags.length > 0)
                this.rtrimRegex = new RegExp('\\[\/(' + rtrimTags.join('|') + ')\\]\\s*', 'ig');
            if(trimTags.length > 0)
                this.trimRegex = new RegExp('\\s*\\[(' + trimTags.join('|') + ')([^\\[]*)?\\]\\s*', 'ig');

            this.inited = true;

            $(function () {
                $(document).delegate('[data-highlight]', 'mouseenter', function() {
                    var _ = $(this).attr('data-highlight').split(':');

                    if(_.length != 2)
                        return;

                    var elem  = $('#' + _[0]).get(0),
                        start = parseInt(_[1]),
                        text  = $(elem).val();

                    if(!elem || !start || !text)
                        return;

                    var top = $(elem).val(text.substr(0, start))[0].scrollHeight;

                    $(elem).val(text).animate({ scrollTop: top }, 250);
                    elem.selectionStart = start;
                    elem.selectionEnd   = start;
                });
            });
        }
    },

    _safeJsString: function(str)
    {
        return str.replace(/'/g, '\'');
    },

    _safeQuotes: function(str)
    {
        return str.replace('"', '\"').replace("'", "\'");
    },

    _safeHtml: function(html)
    {
        var allowedEscapes = ['nbsp', 'ndash'];
        html = html.replace(/&/g, '&amp;');
        if(allowedEscapes.length > 0)
            html = html.replace(new RegExp('&amp;(' + allowedEscapes.join('|') + ');', 'g'), '&$1;');
        return html.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    },

    _preText: function(str)
    {
        str = Markup._safeHtml(str);
        str = str.replace(/\n/g, '<br />');
        return str;
    },

    _getDatabaseDomainInfo: function(attr)
    {
        var url = '';
        var nameCol = Markup.nameCol;

        var domain = false;
        if(attr.domain)
            domain = attr.domain;
        else if(attr.site)
            domain = attr.site;
        else if(Markup.defaultSource)
            domain = MarkupSourceMap[Markup.defaultSource];

        if(domain)
        {
            if(domain == 'beta')
                domain = 'mop';
            url = 'http://' + domain + '.wowhead.com';
            nameCol = 'name_' + Markup.domainToLocale[domain];
        }
        else if(location.href.indexOf('wowheadnews.com') != -1)
            url = 'http://www.wowhead.com';

        return [url, nameCol, domain];
    },

    _isUrlSafe: function(str, extras)
    {
        if(!str)
            return true;
        if(str == 'javascript:;')
            return true;

        var result = str.match(/^([^:\\./]+):/i);

        if(result && result[1])
        {
            var protocol = result[1];

            if(protocol == 'http' || protocol == 'https')
                return true;

            if(extras && (protocol == 'mailto' || protocol == 'irc'))
                return true;

            if(protocol != 'mailto' && str.indexOf('://') == -1)
                return true;

            return false;
        }

        return true;
    },

    _fixUrl: function(url)
    {
        if (!url)
            return '';

        // Make local URLs absolute
        var firstChar = url.charAt(0);
        if (firstChar == '/' || firstChar == '?')
        {
            url = url.replace(/^[\/\?]+/, '');

            // aowow custom
            // url = '/' + url;
            url = '?' + url;
        }

        return url;
    },

    _isUrlExternal: function(str)
    {
        if (!str)
            return false;

        // aowow custom
        // return (str.indexOf('wowhead.com') == -1 && str.match(/^([^:\\./]+):/i));
        return g_isExternalUrl(str);
    },

    _nodeSearch: function(node, name, depth)
    {
        if(!depth) depth = 0;
        if(depth >= 3) return;
        if(node.name == name)
            return true;
        else if(node.parent)
            return Markup._nodeSearch(node.parent, name, depth+1);
    },

    _parse: function(str, opts)
    {
        Markup.nameCol = 'name_' + Locale.getName();
        if(opts && opts.locale)
            Markup.nameCol = 'name_' + Markup.domainToLocale[opts.locale];
        else if($WH.isset('g_beta') && g_beta)
            Markup.nameCol = 'name_beta';
        else if($WH.isset('g_ptr') && g_ptr)
            Markup.nameCol = 'name_ptr';
        else if($WH.isset('g_old') && g_old)
            Markup.nameCol = 'name_old';

        if(!str)
            str = "";

        str = str.replace(/\r/g, '');

        if(!opts) opts = {};
        if(!opts.skipReset)
        {
            Markup.uid = opts.uid || 'abc';
            Markup.root = opts.root;
            Markup.preview = opts.preview || false;
            Markup.dbpage = opts.dbpage || false;
            Markup.defaultSource = false;

            if(Markup.uid != 'abc')
                g_screenshots[Markup.uid] = [];
        }

        if(opts.roles && (opts.roles & (U_GROUP_ADMIN|U_GROUP_EDITOR|U_GROUP_MOD|U_GROUP_BUREAU|U_GROUP_DEV|U_GROUP_BLOGGER)) && opts.mode != Markup.MODE_SIGNATURE)
            opts.mode = Markup.MODE_ARTICLE;
        Markup.mode = opts.mode || Markup.MODE_ARTICLE;
        Markup.allow = opts.allow || Markup.CLASS_STAFF;
        Markup.inBlog = opts.inBlog ? opts.inBlog : 0;

        if(opts.stopAtBreak)
        {
            var breakPos = str.indexOf('[break]');
            if(breakPos != -1)
                str = str.substring(0, breakPos);
        }
        else
            str = str.replace('[break]', '');

        var tree = new MarkupTree();
        str = str.trim();
        //str = Markup._safeHtml(str);

// Resetting this prop causes TOC not to work when
// there are multiple markup sections on a page.
//        this.collectTags = {};
        if(this.postTags.length)
        {
            for(var i in this.postTags)
            {
                var tag = this.postTags[i];
                if(str.indexOf('['+tag) != -1)
                    if(!(Markup.tags[tag].allowedModes && Markup.tags[tag].allowedModes[MarkupModeMap[opts.mode]] == undefined))
                        for(var collect in Markup.tags[tag].collect)
                            this.collectTags[collect] = true;
            }
        }

        //str = str.replace(this.ltrimRegex, function(match, $1, $2) { return '[' + $1 + ($2 ? $2 : '') + ']'; });
        //str = str.replace(this.rtrimRegex, function(match, $1)     { return '[/' + $1 + ']'; });
        //str = str.replace(this.trimRegex,  function(match, $1, $2) { return '[' + $1 + ($2 ? $2 : '') + ']'; });

        str = str.replace(/\n(\s*)\n/g, '\n\n');
        //str = str.replace(/\n/g, '<br />');

        var len = str.length;
        var textStart = 0, idx = 0, open = -1, close = -1, goodTag = true, isClose = false;
        var getValue = function(str)
        {
            var quote, space, value;
            if(str.charAt(0) == '"' || str.charAt(0) == "'")
            {
                quote = str.charAt(0);
                var end = str.indexOf(quote, 1);
                if(end > -1)
                {
                    value = str.substring(1, end);
                    str = str.substring(end+1).trim();
                    return { value: Markup._safeHtml(value), str: str };
                }
            }

            space = str.indexOf(' ');
            if(space > -1)
            {
                value = str.substring(0, space);
                str = str.substring(space+1).trim();
            }
            else
            {
                value = str;
                str = '';
            }

            return { value: value, str: str };
        };
        var unnamedRe = /^\s*[a-z0-9]+\s*=/;
        while(idx < len)
        {
            open = str.indexOf('[', idx);
            if(open > -1)
            {
                idx = open + 1;
                if(open > 0 && str.charAt(open-1) == '\\')
                {
                    goodTag = false;
                    open = -1;
                }
                else
                {
                    close = str.indexOf(']',idx);
                }
            }
            else
                idx = len;

            var tagName, attrs = {};

            if(opts.highlight && $(opts.highlight))
                attrs['data-highlight'] = opts.highlight + ':' + open;

            if(close > -1)
            {
                var tagContents = str.substring(open+1, close);
                if(tagContents.charAt(0) == '/')
                {
                    isClose = true;
                    tagName = tagContents.substr(1).trim().toLowerCase();
                }

                if(!isClose)
                {
                    var space = tagContents.indexOf(' '), assign = tagContents.indexOf('=');
                    var quote;
                    if((assign < space || space == -1) && assign > -1)
                    {
                        tagName = tagContents.substring(0, assign).toLowerCase();
                        tagContents = tagContents.substring(assign+1).trim();
                        var ret = getValue(tagContents);
                        tagContents = ret.str;
                        if(Markup.tags[tagName] == undefined || Markup.tags[tagName].attr == undefined || Markup.tags[tagName].attr.unnamed == undefined)
                        {
                            goodTag = false;
                        }
                        else
                            attrs.unnamed = ret.value;
                    }
                    else if(space > -1)
                    {
                        tagName = tagContents.substring(0, space).toLowerCase();
                        tagContents = tagContents.substring(space+1).trim();
                        if(tagContents.indexOf('=') == -1) // legacy support, [quote name]
                        {
                            if(Markup.tags[tagName] == undefined || Markup.tags[tagName].attr == undefined || Markup.tags[tagName].attr.unnamed == undefined)
                            {
                                goodTag = false;
                            }
                            else
                                attrs.unnamed = tagContents;
                            tagContents = '';
                        }
                    }
                    else
                    {
                        tagName = tagContents.toLowerCase();
                        tagContents = '';
                    }

                    if(Markup.tags[tagName] == undefined)
                    {
                        goodTag = false;
                    }
                    else if(goodTag)
                    {
                        var tag = Markup.tags[tagName];

                        while(tagContents != '')
                        {
                            var attr = '';
                            if(!unnamedRe.test(tagContents))
                            {
                                attr = 'unnamed';
                            }
                            else
                            {
                                assign = tagContents.indexOf('=');
                                if(assign == -1)
                                {
                                    goodTag = false;
                                    break;
                                }

                                attr = tagContents.substring(0, assign).trim().toLowerCase();
                                tagContents = tagContents.substring(assign+1).trim();
                            }

                            var ret = getValue(tagContents);
                            tagContents = ret.str;
                            if(tag.attr == undefined || tag.attr[attr] == undefined)
                            {
                                if(Markup.attributes[attr] == undefined || (Markup.attributes[attr].valid != undefined && !Markup.attributes[attr].valid.test(ret.value)))
                                {
                                    goodTag = false;
                                    break;
                                }
                            }
                            attrs[attr] = ret.value;
                        }

                        if(goodTag && tag.attr)
                        {
                            for(var a in tag.attr)
                            {
                                if(tag.attr[a].req && attrs[a] == undefined)
                                {
                                    goodTag = false;
                                    break;
                                }
                                else if(attrs[a] == undefined)
                                    continue;

                                if(tag.attr[a].valid != undefined && !tag.attr[a].valid.test(attrs[a]))
                                {
                                    goodTag = false;
                                    break;
                                }
                            }

                            if(goodTag && tag.validate != undefined) {
                                goodTag = tag.validate(attrs);
                            }
                        }
                    }
                }
                else if(Markup.tags[tagName] == undefined)
                    goodTag = false;
            }
            else
                goodTag = false;

            if(goodTag)
            {
                if(textStart != open)
                {
                    var s = str.substring(textStart, open).replace(/\\\[/g, '[');
                    var text = { _rawText: s };
                    tree.openTag('<text>', text);
                }
                if(isClose)
                    goodTag = tree.closeTag(tagName);
                else
                    goodTag = tree.openTag(tagName, attrs);

                if(goodTag)
                    textStart = idx = close + 1;
                else
                    textStart = open;
            }
            goodTag = true;
            isClose = false;
            open = close = -1;
        }

        if(textStart < len)
        {
            var s = str.substr(textStart).replace(/\\\[/g, '[');
            var text = { _rawText: s };
            tree.openTag('<text>', text);
        }

        return tree;
    },

    createMaps: function()
    {
        for(var i = 0; i < Markup.maps.length; ++i)
        {
            var m = Markup.maps[i];
            new Mapper({parent: m[0], zone: m[1], coords: m[2], unique: i});
        }
        Markup.maps = [];
    },

    toHtml: function(str, opts)
    {
        if(!opts)
            opts = {};
        if(!opts.allow)
        {
            if(opts.roles)
                opts.allow = Markup.rolesToClass(opts.roles);
            else
                opts.allow = Markup.CLASS_STAFF;
        }

        var tree = Markup._parse(str, opts);
        var html = tree.toHtml();
        if(opts.prepend)
            html = opts.prepend + html;
        if(opts.append)
            html += opts['append'];
        setTimeout(Markup.createMaps, 250);
        return html;
    },

    fromHtml: function(str, depth)
    {
        // Clean up the html source
        str = str.replace(/\n+/g, '');
        str = str.replace(/\s+/g, ' ');
        str = str.replace(/> </g, '><');
        //str = str.replace(/<br\b[\s\S]*?></gi, '<');
        str = str.replace(/&amp;/gi, '&');
        str = str.replace(/(&#160;|$nbsp;)/gi, ' ');

        // Replace html with markup
        for(var tag in Markup.tags)
        {
            if(Markup.tags[tag].fromHtml)
                str = Markup.tags[tag].fromHtml(str, depth);
        }

        // Strip remaining html
        str = str.replace(/<style\b[\s\S]*?>[\s\S]*?<\/style>/g, '');
        str = str.replace(/<\/?[a-z][a-z0-9]*\b[\s\S]*?>/g, ' ');
        str = str.replace(/<!--(.*?)-->/g, '');
        str = str.replace(/\n[\n]+/g, "\n\n");
        str = str.replace(/[ ]+/g, ' ');
        str = str.replace(/\t/g, '  ');

        return $WH.trim(str);
    },

    removeTags: function(str, opts)
    {
        var tree = Markup._parse(str, opts);
        return tree.tagless();
    },

    matchOuterTags: function(str, left, right, flags)
    {
        var    g = flags.indexOf('g') > -1,
            f = flags.replace(/g/g, ''),
            x = new RegExp(left + '|' + right, 'g' + f), // Open or close tag
            l = new RegExp(left, f), // Open tag only
            a = [],
            t, s, m, n;

        do
        {
            t = 0;
            while(m = x.exec(str))
            {
                if(l.test(m[0]))
                {
                    if (!t++)
                    {
                        s = x.lastIndex;
                        n = m;
                    }
                }
                else if (t)
                {
                    if(!--t)
                    {
                        a.push([str.slice(s, m.index), n[0], m[0]]);
                        if (!g) return a;
                    }
                }
            }
        } while(t && (x.lastIndex = s));

        return (a.length ? a : false);
    },

    getImageUploadIds: function(str, opts)
    {
        var tree = Markup._parse(str, opts);
        return tree.imageUploadIds();
    },

    printHtml: function(str, div, opts)
    {
        div = $WH.ge(div);
        var html = Markup.toHtml(str, opts);
        div.innerHTML = html;
        Markup.createMaps();
    },

    toggleReveal: function(id)
    {
        var span = $('#reveal-' + id);
        if(span.length == 0)
            return;

        var toggle = $('#revealtoggle-' + id);

        if(span.is(':visible'))
        {
            span.hide();
            toggle.text('(read more)');
        }
        else
        {
            span.show();
            toggle.text('(hide)');
        }
    },

    mapperPreview: function(id)
    {
        try {
            window.mapper = Markup.maps[id];
            var win = window.open('?edit=mapper-preview', 'mapperpreview', 'toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=no,resizable=no,width=800,height=540');
            win.focus();
        } catch(e) {}
    },

    createTabs: function(parent, tabs, preview)
    {
        var _ = new Tabs({parent: $WH.ge('dsf67g4d-' + parent.id + (preview ? '-preview' : '')), forum:1, noScroll: (preview ? true : false)});
        for(var i = 0; i < tabs.length; ++i)
        {
            var tab = tabs[i];
            _.add(tab.name, {id:parent.id + '-' + tab.id, icon: tab.icon, 'class': tab['class']});
        }
        _.flush();
    }
};

var MarkupUtil = {
    ltrimText: function(attr)
    {
        attr._rawText = attr._rawText.ltrim();
        return attr;
    },
    rtrimText: function(attr)
    {
        attr._rawText = attr._rawText.rtrim();
        return attr;
    },

    checkSiblingTrim: function(lastNode, node)
    {
        if(node.name == '<text>' && (Markup.tags[lastNode.name].rtrim || Markup.tags[lastNode.name].trim))
            node.attr = MarkupUtil.ltrimText(node.attr);
        else if(lastNode.name == '<text>' && (Markup.tags[node.name].ltrim || Markup.tags[node.name].trim))
            lastNode.attr = MarkupUtil.rtrimText(lastNode.attr);
        return [lastNode, node];
    }
};

var MarkupTree = function()
{
    this.nodes = [];
    this.currentNode = null;
};

MarkupTree.prototype = {
    openTag: function(tag, attrs)
    {
        // Allowed class defaults to pending users
        if(tag != '<text>' && Markup.tags[tag] && !Markup.tags[tag].allowedClass)
            Markup.tags[tag].allowedClass = MARKUP_CLASS_PENDING;

        if(!Markup.tags[tag])
        {
            //document.write('bad tag name: "' + tag + '"<br />');
            return false;
        }
        else if(Markup.tags[tag].allowedModes && Markup.tags[tag].allowedModes[MarkupModeMap[Markup.mode]] == undefined)
        {
            return false; // tag not allowed in this mode
        }
        else if(Markup.tags[tag].allowedClass && Markup.tags[tag].allowedClass > Markup.allow)
        {
            return false; // tag requires a higher user class
        }

        if(Markup.mode == MARKUP_MODE_REPLY && !Markup.tags[tag].allowInReplies)
            return false; // Not allowed in comment replies

        var node = { name: tag, attr: attrs, parent: null, nodes: [] };
        if(this.currentNode)
            node.parent = this.currentNode;

        if(Markup.tags[tag].allowedParents)
        {
            if(node.parent != null)
            {
                if(Markup.tags[tag].allowedParents[node.parent.name] === undefined)
                    return false;
            }
            else if(Markup.root == undefined || Markup.tags[tag].allowedParents[Markup.root] == undefined)
                return false;
        }
        if(node.parent && Markup.tags[node.parent.name].allowedChildren && Markup.tags[node.parent.name].allowedChildren[tag] == undefined)
            return false;

        if(this.currentNode)
        {
            if(this.currentNode.nodes.length == 0 && node.name == '<text>' && Markup.tags[this.currentNode.name].itrim)
                node.attr = MarkupUtil.ltrimText(node.attr);
            else if(this.currentNode.nodes.length > 0)
            {
                var lastNodeIndex = this.currentNode.nodes.length-1;
                var result = MarkupUtil.checkSiblingTrim(this.currentNode.nodes[lastNodeIndex], node);
                this.currentNode.nodes[lastNodeIndex] = result[0];
                node = result[1];
            }

            if(node.name == '<text>')
            {
                node.attr._text = Markup._preText(node.attr._rawText);
                if(node.attr._text.length > 0)
                    this.currentNode.nodes.push(node);
            }
            else
                this.currentNode.nodes.push(node);
        }
        else
        {
            if(this.nodes.length > 0)
            {
                var lastNodeIndex = this.nodes.length-1;
                var result = MarkupUtil.checkSiblingTrim(this.nodes[lastNodeIndex], node);
                this.nodes[lastNodeIndex] = result[0];
                node = result[1];
            }

            if(node.name == '<text>')
            {
                node.attr._text = Markup._preText(node.attr._rawText);
                if(node.attr._text.length > 0)
                    this.nodes.push(node);
            }
            else
                this.nodes.push(node);
        }

        if(!Markup.tags[tag].empty && !Markup.tags[tag].post)
            this.currentNode = node;

        return true;
    },

    closeTag: function(tag)
    {
        if(Markup.tags[tag].empty || Markup.tags[tag].post) // empty tag means no close tag
            return false;
        if(!this.currentNode) // no tag open, so how are we closing one?
            return false;
        else if(this.currentNode.name == tag) // valid close
        {
            if(this.currentNode.nodes.length > 0)
            {
                var lastNodeIndex = this.currentNode.nodes.length-1;
                if(Markup.tags[this.currentNode.name].itrim && this.currentNode.nodes[lastNodeIndex].name == '<text>')
                {
                    var node = this.currentNode.nodes[lastNodeIndex];
                    node.attr = MarkupUtil.rtrimText(node.attr);
                    node.attr._text = Markup._preText(node.attr._rawText);
                    this.currentNode.nodes[lastNodeIndex] = node;
                }
            }

            this.currentNode = this.currentNode.parent;
        }
        else
        { // a tag was closed, but doesnt match the current open tag, so probably invalid nesting. attempt to find the matching tag, then fail if we cant
            var findLastNode = function(name, nodes)
            {
                for(var i = nodes.length-1; i >= 0; --i)
                {
                    if(nodes[i].name == name)
                        return i;
                }
                return -1;
            };
            var idx;
            if(this.currentNode.parent)
                idx = findLastNode(tag, this.currentNode.parent.nodes);
            else
                idx = findLastNode(tag, this.nodes);

            if(idx == -1) // no matching tag
                return false;
        }

        return true;
    },

    toHtml: function()
    {
        var postNodes = [];

        var collection = {};
        for(var x in Markup.collectTags)
            collection[x] = [];
        this.tagless(true);
        var currentOffset = 0;
        var processNodes = function(nodes, depth, exclusions)
        {
            var str = '';
            for(var i = 0; i < nodes.length; ++i)
            {
                var node = nodes[i];

                if(depth == 0 && i == 0 && Markup.firstTags[node.name]) // first in text
                    node.attr.first = true;
                else if(depth > 0 && i == 0 && Markup.firstTags[node.parent.name]) // first in block
                    node.attr.first = true;
                if(i == nodes.length-1 && Markup.firstTags[node.name]) // last thing, block
                    node.attr.last = true;

                if(Markup.excludeTags[node.name])
                    exclusions[node.name] = (exclusions[node.name] ? exclusions[node.name] + 1 : 1);
                for(var ex in exclusions)
                {
                    for(var t in Markup.excludeTags[ex])
                    {
                        if(Markup.excludeTags[ex][t][node.name])
                            node.attr[t] = false;
                    }
                }

                if(Markup.collectTags[node.name])
                {
                    node.offset = currentOffset++;
                    collection[node.name].push(node);
                }
                if(Markup.tags[node.name].post)
                {
                    var comment = '<!--' + Math.random() + '-->';
                    str += comment;
                    postNodes.push([node, comment]);
                }
                else if(Markup.tags[node.name].empty)
                {
                    var html;
                    if(node.parent && Markup.tags[node.parent.name].rawText)
                        html = Markup.tags[node.name].toHtml(node.attr, { needsRaw: true });
                    else
                        html = Markup.tags[node.name].toHtml(node.attr);
                    if(typeof html == 'string')
                        str += html;
                    else if(html !== undefined)
                    {
                        if(str == '')
                            str = [];
                        str.push(html);
                    }
                }
                else
                {
                    var contents = arguments.callee(node.nodes, depth+1, exclusions);
                    node.attr._contents = contents;
                    node.attr._nodes = node.nodes;
                    var tags = Markup.tags[node.name].toHtml(node.attr);
                    if(tags.length == 2)
                        str += tags[0] + contents + tags[1];
                    else if(tags.length == 1)
                    {
                        if(typeof tags[0] == 'string')
                            str += tags[0];
                        else
                        {
                            if(str == '')
                                str = [];
                            str.push(tags[0]);
                        }
                    }
                }

                if(exclusions[node.name])
                {
                    exclusions[node.name]--;
                    if(exclusions[node.name] == 0)
                        delete exclusions[node.name];
                }
            }
            return str;
        };
        str = processNodes(this.nodes, 0, []);
        for(var i = 0; i < postNodes.length; ++i)
        {
            var node = postNodes[i][0];
            var replace = postNodes[i][1];
            var html = Markup.tags[node.name].postHtml(node.attr, collection);
            if(typeof html == 'string')
                str = str.replace(replace, html);
        }

        return str;
    },

    tagless: function(n)
    {
        var processNodes = function(nodes)
        {
            var str = '';
            for(var i = 0; i < nodes.length; ++i)
            {
                var node = nodes[i];
                var contents = arguments.callee(node.nodes);
                if(n) {
                    node.attr._textContents = contents;
                } else
                {
                    node.attr._contents = contents;
                }
                if(node.name == '<text>')
                    str += Markup.tags[node.name].toHtml(node.attr, { noLink: true, noNbsp: true });
                else if(Markup.tags[node.name].toText)
                    str += Markup.tags[node.name].toText(node.attr);
                if(!Markup.tags[node.name].taglessSkip)
                    str += contents;
            }
            return str;
        };
        if(n)
            processNodes(this.nodes);
        else {
            var str = processNodes(this.nodes);
            str = str.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"');
            return str;
        }
    },

    imageUploadIds: function()
    {
        var ids = [];

        var processNodes = function(nodes)
        {
            for(var i = 0; i < nodes.length; ++i)
            {
                var node = nodes[i];
                if(node.name == 'img' && node.attr.upload)
                    ids.push(node.attr.upload);

                arguments.callee(node.nodes);
            }
        };
        processNodes(this.nodes);
        return ids;
    }
};

Markup.tags.modelviewer = Markup.tags.model; // Backwards compatability
Markup.reveals = 0;

Markup._init();

$(document).ready(function() {
    $('.quote-header').each(function(i) {
        var $this = $(this);
        var sibs = $this.siblings();
        if(sibs.hasClass('quote-body'))
        {
            var a = $('<a/>', { href: 'javascript:;', 'class': 'toggle' });

            a.click(function(header) {
                var $head = $(header);
                var parent = $head.parent();

                parent.toggleClass('collapse');

                if(parent.hasClass('collapse'))
                    $(this).html('Expand');
                else
                    $(this).html('Collapse');
            }.bind(a, this));

            if($(this).parent().hasClass('collapse'))
                a.html('Expand');
            else
                a.html('Collapse');

            $this.append(a);
        }
    });

    $('.quote-wh').each(function(i) {
        var $this = $(this);
        var a = $('<a/>', { href: 'javascript:;', 'class': 'toggle' });

        a.click(function(thisObj) {
            var $this = $(thisObj);

            $this.toggleClass('collapse');

            if($this.hasClass('collapse'))
                $(this).html('Expand');
            else
                $(this).html('Collapse');
        }.bind(a, this));

        if($(this).hasClass('collapse'))
                a.html('Expand');
            else
                a.html('Collapse');

        $this.append(a);
    });
});
