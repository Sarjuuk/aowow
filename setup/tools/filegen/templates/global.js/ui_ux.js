/*
Global functions related to UI elements and effects
*/

function g_getReputationPlusAchievementText(gold, silver, copper, reputation)
{
 // aowow - WSA not in use
 // var achievements = g_getAchievementText(gold, silver, copper, true);
    var repText = $('<span>').addClass('wsach-pts');

    repText.mouseover(function(event) {
        $WH.Tooltip.showAtCursor(event, LANG.reputationtip, 0, 0, 'q');
    }).mousemove(function(event) {
        $WH.Tooltip.cursorUpdate(event);
    }).mouseout(function() {
        $WH.Tooltip.hide();
    });

    repText.css('color', 'white');
    repText.text($WH.number_format(reputation));

    var ret = $('<span>');

    ret.append(' (');
    ret.append(repText);
 // ret.append(' &ndash; ');
 // ret.append(achievements);
    ret.append(')');

    return ret;
}

function g_getAchievementText(gold, silver, copper, forumPost)
{
	var ret = $('<span>').addClass('wsach-pts');

	ret.mouseover(function(event) {$WH.Tooltip.showAtCursor(event, LANG.userachcount_tip, 0, 0, 'q');}).mousemove(function(event) {$WH.Tooltip.cursorUpdate(event)}).mouseout(function() {$WH.Tooltip.hide()});
	var html = ' ';
	if (!forumPost)
		html += ' (';

	if (gold)
		html += '<i>' + gold + '</i>' + '&middot;';
	if (silver)
		html += '<b>' + silver + '</b>' + '&middot;';
	if (!copper)
		copper = 0;

	html += '<u>' + copper + '</u>';

	if (!forumPost)
		html += ')';

	ret.html(html);

	return ret;
}

function g_addTooltip(element, text, className)
{
    if (!className && text.indexOf('<table>') == -1)
        className = 'q';

    $WH.Tooltip.simple(element, text, className);
}

function g_addStaticTooltip(icon, text, className)
{
    if (!className && text.indexOf('<table>') == -1)
        className = 'q';

    icon.onmouseover = function(event)
    {
        $WH.Tooltip.show(icon, text, 0, 0, className);
    };

    icon.onmouseout = $WH.Tooltip.hide;
}

/* If maxDelay is set, and delay > maxDelay, the full date will be shown instead. */

function g_formatTimeElapsed(delay)
{
    function OMG(value, unit, abbrv)
    {
        if (abbrv && LANG.timeunitsab[unit] == '')
            abbrv = 0;

        if (abbrv)
            return value + ' ' + LANG.timeunitsab[unit];
        else
            return value + ' ' + (value == 1 ? LANG.timeunitssg[unit] : LANG.timeunitspl[unit]);
    }

    var range = [31557600, 2629800, 604800, 86400, 3600, 60, 1];

    var subunit = [1, 3, 3, -1, 5, -1, -1];

    delay = Math.max(delay, 1);

    for (var i = 3, len = range.length; i < len; ++i)
    {
        if (delay >= range[i])
        {
            var i1 = i;
            var v1 = Math.floor(delay / range[i1]);

            if (subunit[i1] != -1)
            {
                var i2 = subunit[i1];
                delay %= range[i1];

                var v2 = Math.floor(delay / range[i2]);

                if (v2 > 0)
                    return OMG(v1, i1, 1) + ' ' + OMG(v2, i2, 1);
            }

            return OMG(v1, i1, 0);
        }
    }

    return '(n/a)';
}

function g_GetStaffColorFromRoles(roles)
{
    if (roles & U_GROUP_ADMIN)
        return 'comment-blue';
    if (roles & U_GROUP_GREEN_TEXT) // Mod, Bureau, Dev
        return 'comment-green';
    if (roles & U_GROUP_VIP) // VIP
        return 'comment-gold';
    if (roles & U_GROUP_PREMIUMISH) // Premium, Editor
        return 'comment-gold';

    return '';
}

// aowow - stand in for WH.User.getCommentRoleLabel
function g_GetCommentRoleLabel(roles, title)
{
    if (title)
        return title;

    if (roles & U_GROUP_ADMIN)
        return g_user_roles[2];                             // LANG.administrator_abbrev
    if (roles & U_GROUP_MOD)
        return g_user_roles[4];                             // LANG.moderator
    if (roles & U_GROUP_PREMIUMISH)
        return LANG.premiumuser;

    return null;
};

function g_formatDate(sp, elapsed, theDate, time, alone)
{
    var today = new Date();
    var event_day = new Date();
    event_day.setTime(today.getTime() - (1000 * elapsed));
    var txt;
    var event_day_midnight = new Date(event_day.getYear(), event_day.getMonth(), event_day.getDate());
    var today_midnight = new Date(today.getYear(), today.getMonth(), today.getDate());
    var delta = (today_midnight.getTime() - event_day_midnight.getTime());
    delta /= 1000;
    delta /= 86400;
    delta = Math.round(delta);

    if (elapsed >= 2592000) /* More than a month ago */
    {
        txt = LANG.date_on + g_formatDateSimple(theDate, time);
    }
    else if (delta > 1)
    {
        txt = $WH.sprintf(LANG.ddaysago, delta);

        if (sp)
        {
            var _ = new Date();
            _.setTime(theDate.getTime() + (g_localTime - g_serverTime));
            sp.className += ' tip';
            sp.title = _.toLocaleString();
        }
    }
    else if (elapsed >= 43200)
    {
        if (today.getDay() == event_day.getDay())
            txt = LANG.today;
        else
            txt = LANG.yesterday;

        txt = g_formatTimeSimple(event_day, txt);

        if (sp)
        {
            var _ = new Date();
            _.setTime(theDate.getTime() + (g_localTime - g_serverTime));
            sp.className += ' tip';
            sp.title = _.toLocaleString();
        }
    }
    else /* Less than 12 hours ago */
    {
        var txt = $WH.sprintf(LANG.date_ago, g_formatTimeElapsed(elapsed));

        if (sp)
        {
            var _ = new Date();
            _.setTime(theDate.getTime() + (g_localTime - g_serverTime));
            sp.className += ' tip';
            sp.title = _.toLocaleString();
        }
    }

    if (alone == 1)
        txt = txt.substr(0, 1).toUpperCase() + txt.substr(1);

    if (sp)
        $WH.ae(sp, $WH.ct(txt));
    else
        return txt;
}

function g_formatDateSimple(d, time)
{
    function __twoDigits(n)
    {
        return (n < 10 ? '0' + n : n);
    }

    var b     = '',
        day   = d.getDate(),
        month = d.getMonth() + 1,
        year  = d.getFullYear();

    if (year <= 1970)
        b += LANG.unknowndate_stc;
    else
        b += $WH.sprintf(LANG.date_simple, __twoDigits(day), __twoDigits(month), year);

    if (time != null)
        b = g_formatTimeSimple(d, b);

    return b;
}

function g_formatTimeSimple(d, txt, noPrefix)
{
    function __twoDigits(n)
    {
        return (n < 10 ? '0' + n : n);
    }

    var hours   = d.getHours(),
        minutes = d.getMinutes();

    if (txt == null)
        txt = '';

    txt += (noPrefix ? ' ' : LANG.date_at);

    if (hours == 12)
        txt += LANG.noon;
    else if (hours == 0)
        txt += LANG.midnight;
    else if (hours > 12)
        txt += (hours - 12) + ':' + __twoDigits(minutes) + ' ' + LANG.pm;
    else
        txt += hours + ':' + __twoDigits(minutes) + ' ' + LANG.am;

    return txt;
}

function g_createGlow(txt, cn)
{
    var s = $WH.ce('span');

    for (var i = -1; i <= 1; ++i)
    {
        for (var j = -1; j <= 1; ++j)
        {
            var d = $WH.ce('div');
            d.style.position = 'absolute';
            d.style.whiteSpace = 'nowrap';
            d.style.left = i + 'px';
            d.style.top = j + 'px';

            if (i == 0 && j == 0)
                d.style.zIndex = 4;
            else
            {
                d.style.color = 'black';
                d.style.zIndex = 2;
            }
            //$WH.ae(d, $WH.ct(txt));
            d.innerHTML = txt;
            $WH.ae(s, d);
        }
    }

    s.style.position = 'relative';
    s.className = 'glow' + (cn != null ? ' ' + cn : '');

    var ph = $WH.ce('span');
    ph.style.visibility = 'hidden';
    $WH.ae(ph, $WH.ct(txt));
    $WH.ae(s, ph);

    return s;
}

function g_createProgressBar(opt)
{
    if (opt == null)
        opt = {};

    if (typeof opt.text == 'undefined')
        opt.text = ' ';

    if (opt.color == null)
        opt.color = 'rep0';

    if (opt.width == null || opt.width > 100)
        opt.width = 100;

    var el, div;
    if (opt.hoverText)
    {
        el = $WH.ce('a');
        el.href = 'javascript:;';
    }
    else
        el = $WH.ce('span');

    el.className = 'progressbar';

    if (opt.text || opt.hoverText)
    {
        div = $WH.ce('div');
        div.className = 'progressbar-text';

        if (opt.text)
        {
            var del = $WH.ce('del');
            $WH.ae(del, $WH.ct(opt.text));
            $WH.ae(div, del);
        }

        if (opt.hoverText)
        {
            var ins = $WH.ce('ins');
            $WH.ae(ins, $WH.ct(opt.hoverText));
            $WH.ae(div, ins);
        }

        $WH.ae(el, div);
    }

    div = $WH.ce('div');
    div.className = 'progressbar-' + opt.color;
    div.style.width = opt.width + '%';
    if (opt.height)
        div.style.height = opt.height;

    $WH.ae(div, $WH.ct(String.fromCharCode(160)));
    $WH.ae(el, div);

    if (opt.text)
    {
        var div = $WH.ce('div');
        div.className = 'progressbar-text progressbar-hidden';
        $WH.ae(div, $WH.ct(opt.text));
        $WH.ae(el, div);
    }

    return el;
}

function g_createReputationBar(totalRep)
{
    var P = g_createReputationBar.P;

    if (!totalRep)
        totalRep = 0;

    totalRep += 42000;
    if (totalRep < 0)
        totalRep = 0;
    else if (totalRep > 84999)
        totalRep = 84999;

    var currentRep = totalRep,
        maxRep,
        standing = 0;

    for (var i = 0, len = P.length; i < len; ++i)
    {
        if (P[i] > currentRep)
            break;

        if (i < len - 1)
        {
            currentRep -= P[i];
            standing = i + 1;
        }
    }

    maxRep = P[standing];

    var opt = {
        text:      g_reputation_standings[standing],
        hoverText: currentRep + ' / ' + maxRep,
        color:     'rep' + standing,
        width:     parseInt(currentRep / maxRep * 100)
    };

    return g_createProgressBar(opt);
}
g_createReputationBar.P = [36000, 3000, 3000, 3000, 6000, 12000, 21000, 999];

function g_createAchievementBar(points, outOf, overall, bonus)
{
    if (!points)
        points = 0;

    var opt = {
        text:  points + (bonus > 0 ? '(+' + bonus + ')' : '') + (outOf > 0 ? ' / ' + outOf : ''),
        color: (overall ? 'rep7' : 'ach' + (outOf > 0 ? 0 : 1)),
        width: (outOf > 0 ? parseInt(points / outOf * 100) : 100)
    };

    return g_createProgressBar(opt);
}

function g_getMoneyHtml(money, side, costItems, costCurrency, achievementPoints)
{
    var ns   = 0,
        html = '';

    if (side == 1 || side == 'alliance')
        side = 1;
    else if (side == 2 || side == 'horde')
        side = 2;
    else
        side = 3;

    if (money >= 10000)
    {
        ns = 1;

        var display = Math.floor(money / 10000);
        html  += '<span class="moneygold">' + $WH.number_format(display) + '</span>';
        money %= 10000;
    }

    if (money >= 100)
    {
        if (ns)
            html += ' ';
        else
            ns = 1;

        var display = Math.floor(money / 100);
        html  += '<span class="moneysilver">' + display + '</span>';
        money %= 100;
    }

    if (money >= 1)
    {
        if (ns)
            html += ' ';
        else
            ns = 1;

        html += '<span class="moneycopper">' + money + '</span>';
    }

    if (costItems != null)
    {
        for (var i = 0; i < costItems.length; ++i)
        {
            if (ns)
                html += ' ';
            else
                ns = 1;

            var itemId = costItems[i][0];
            var count  = costItems[i][1];
            var icon   = (g_items[itemId] && g_items[itemId].icon ? g_items[itemId].icon : 'inv_misc_questionmark');

            html += '<a href="?item=' + itemId + '" class="moneyitem" style="background-image: url(' + g_staticUrl + '/images/wow/icons/tiny/' + icon.toLowerCase() + '.gif)">' + count + '</a>';
        }
    }

    if (costCurrency != null)
    {
        for (var i = 0; i < costCurrency.length; ++i)
        {
            if (ns)
                html += ' ';
            else
                ns = 1;

            var currencyId = costCurrency[i][0];
            var count      = costCurrency[i][1];
            var icon       = (g_gatheredcurrencies[currencyId] && g_gatheredcurrencies[currencyId].icon ? g_gatheredcurrencies[currencyId].icon : ['inv_misc_questionmark', 'inv_misc_questionmark']);

            if (side == 3 && icon[0] == icon[1])
                side = 1;

            // aowow: custom start
            if (currencyId == 103)                          // arena
                html += '<a href="?currency=' + currencyId + '" class="moneyarena tip" onmouseover="Listview.funcBox.moneyArenaOver(event)" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">' + $WH.number_format(count) + '</a>';
            else if (currencyId == 104)                     // honor
                html += '<a href="?currency=' + currencyId + '" class="money' + (side == 1 ? 'alliance' : 'horde') + ' tip" onmouseover="Listview.funcBox.moneyHonorOver(event)" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">' + (side == 3 ? '<span class="moneyalliance">' : '') + $WH.number_format(count) + (side == 3 ? '</span>' : '') + '</a>';
            else                                            // tokens
                html += '<a href="?currency=' + currencyId + '" class="icontinyr tip q1" onmouseover="Listview.funcBox.moneyCurrencyOver(' + currencyId + ', ' + count + ', event)" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()" style="background-image: url(' + g_staticUrl + '/images/wow/icons/tiny/' + icon[0].toLowerCase() + '.gif)">' +  count + '</a>';
            // aowow: custom end
            // html += '<a href="?currency=' + currencyId + '" class="icontinyr tip q1" onmouseover="Listview.funcBox.moneyCurrencyOver(' + currencyId + ', ' + count + ', event)" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()" style="background-image: url(' + g_staticUrl + '/images/icons/tiny/' + icon[(side == 3 ? 1 : side - 1)].toLowerCase() + '.gif)">' + (side == 3 ? '<span class="icontinyr" style="background-image: url(' + g_staticUrl + '/images/icons/tiny/' + icon[0].toLowerCase() + '.gif)">' : '') + count + (side == 3 ? '</span>' : '') + '</a>';
        }
    }

    if (achievementPoints > 0)
    {
        if (ns)
            html += ' ';
        else
            ns = 1;

        html += '<span class="moneyachievement tip" onmouseover="Listview.funcBox.moneyAchievementOver(event)" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">' + $WH.number_format(achievementPoints) + '</span>';
    }

    return html;
}

function g_pickerWheel(e)
{
    e = $WH.$E(e);

    if (e._wheelDelta < 0)
        this.scrollTop += 27;
    else
        this.scrollTop -= 27;
}

function g_setSelectedLink(n, group)
{
    if (!g_setSelectedLink.groups)
        g_setSelectedLink.groups = {};

    var _ = g_setSelectedLink.groups;

    if (_[group])
        _[group].className = _[group].className.replace('selected', '');

    n.className += ' selected';
    _[group] = n;
}

function g_setCheckedRow(n, group)
{
    if (!g_setCheckedRow.groups)
        g_setCheckedRow.groups = {};

    var _ = g_setCheckedRow.groups;

    if (_[group])
        _[group].className = _[group].className.replace('checked', '');

    n.className += ' checked';
    _[group] = n;
}

function g_addPages(d, opt)
{
    function createPageSquare(n, customText)
    {
        var foo;
        if (n == opt.page)
        {
            foo = $WH.ce('span');
            foo.className = 'selected';
        }
        else
        {
            foo = $WH.ce('a');
            foo.href = (n > 1 ? opt.url + opt.sep + n + opt.pound : opt.url + opt.pound);
        }
        $WH.ae(foo, $WH.ct(customText != null ? customText : n));
        return foo;
    }

    if (!opt.pound)
        opt.pound = '';

    if (!opt.sep)
        opt.sep = '.';

    if (opt.allOrNothing && opt.nPages <= 1)
        return;

    var leftAligned = (opt.align && opt.align == 'left');

    var divPages = $WH.ce('div'),
        pagesNumbers,
        para = $WH.ce('var');

        divPages.className = 'pages';
    if (leftAligned)
        divPages.className += ' pages-left';

    // Pages
    if (opt.nPages > 1)
    {
        pagesNumbers = $WH.ce('div');
        pagesNumbers.className = 'pages-numbers';

        var minPage = Math.max(2, opt.page - 2);
        var maxPage = Math.min(opt.nPages - 1, opt.page + 2);

        var elements = []; // Temporarily stored in an array so the order can be reversed when left-aligned.

        if (opt.page != opt.nPages)
            elements.push(createPageSquare(opt.page + 1, LANG.lvpage_next + String.fromCharCode(8250)));

        elements.push(createPageSquare(opt.nPages));
        if (maxPage < opt.nPages - 1)
        {
            var sp = $WH.ce('span');
            $WH.ae(sp, $WH.ct('...'));
            elements.push(sp);
        }

        for (var i = maxPage; i >= minPage; --i)
            elements.push(createPageSquare(i));

        if (minPage > 2)
        {
            var sp = $WH.ce('span');
            $WH.ae(sp, $WH.ct('...'));
            elements.push(sp);
        }
        elements.push(createPageSquare(1));

        if (opt.page != 1)
            elements.push(createPageSquare(opt.page - 1, String.fromCharCode(8249) + LANG.lvpage_previous));

        if (leftAligned)
            elements.reverse();

        for (var i = 0, len = elements.length; i < len; ++i)
            $WH.ae(pagesNumbers, elements[i]);

        pagesNumbers.firstChild.style.marginRight = '0';
        pagesNumbers.lastChild.style.marginLeft   = '0';
    }

    // Number of items
    var para = $WH.ce('var');
    $WH.ae(para, $WH.ct($WH.sprintf(LANG[opt.wording[opt.nItems == 1 ? 0 : 1]], opt.nItems)));

    if (opt.nPages > 1) // Go to page
    {
        var sp = $WH.ce('span');
        $WH.ae(sp, $WH.ct(String.fromCharCode(8211)));
        $WH.ae(para, sp);

        var pageIcon = $WH.ce('a');
        pageIcon.className = 'gotopage';
        pageIcon.href = 'javascript:;';
        $WH.ns(pageIcon);

        pageIcon.onclick = function()
        {
            var n = prompt($WH.sprintf(LANG.prompt_gotopage, 1, opt.nPages), opt.page);
            if (n != null)
            {
                n |= 0;
                if (n != opt.page && n >= 1 && n <= opt.nPages)
                    document.location.href = (n > 1 ? opt.url + opt.sep + n + opt.pound : opt.url + opt.pound);
            }
        };
        pageIcon.onmouseover = function(event)
        {
            $WH.Tooltip.showAtCursor(event, LANG.tooltip_gotopage, 0, 0, 'q2');
        };
        pageIcon.onmousemove = $WH.Tooltip.cursorUpdate;
        pageIcon.onmouseout  = $WH.Tooltip.hide;
        $WH.ae(para, pageIcon);
    }

    if (leftAligned)
    {
        $WH.ae(divPages, para);
        if (pagesNumbers)
            $WH.ae(divPages, pagesNumbers);
    }
    else
    {
        if (pagesNumbers)
            $WH.ae(divPages, pagesNumbers);

        $WH.ae(divPages, para);
    }

    $WH.ae(d, divPages);
}

function g_disclose(el, _this)
{
    _this.className = 'disclosure-' + (g_toggleDisplay(el) ? 'on' : 'off');

    return false;
}

/* Displays a warning when the user attempts to leave the page if some elements are modified, unless the user leaves the page
 * by submitting the form.
 *
 * The jQuery form object must be passed as first argument; the second argument is an array of jQuery objects of fields that
 * are being watched for changes. The third argument is the warning message shown.
 *
 * This function must be called in the on ready event.
 */

function g_setupChangeWarning(form, elements, warningMessage)
{
    if (!form)
        return;

    function ShowWarning() { return warningMessage; }

    form.submit(function() { window.onbeforeunload = null; });

    var initialText = [];

    for (var idx in elements)
    {
        var text = elements[idx];

        if (!text)
            continue;

        initialText[idx] = text.val();
        text.keydown(function()
        {
            for (var idx in elements)
            {
                var text = elements[idx];

                if (!text)
                    continue;

                if (text.val() != initialText[idx])
                {
                    window.onbeforeunload = ShowWarning;
                    return;
                }

                window.onbeforeunload = null;
            }
        });
    }
}

$(document).ready(function() {
    $WH.array_apply($WH.gE(document, 'dfn'), function(x) {
        var text = x.title;
        x.title = '';
        x.className += ' tip';

        if (text.indexOf('LANG.') === 0)                    // aowow - custom for less redundant texts
            text = eval(text);

        g_addTooltip(x, text, 'q');
    });

// aowow - if i understand this right, this code binds an onCopy eventHandler to every child node of class="text"-nodes with the attribute unselectable="on"
// causing the text to disappear for 1ms, causing the empty node to be copied ...  w/e, i'm not going to use this
/*
    $('.text').bind('copy', function() {
        $('*[unselectable]', this).each(function(i, v) {
            var txt = $(v).text();
            $(v).text('');
            setTimeout(function() { $(v).text(txt) }, 1);
        });
    });
*/
});

function g_GetExpansionClassName(expansion)
{
    switch (expansion)
    {
        case 0:
            return null;
        case 1:
            return 'icon-bc-right';
        case 2:
            return 'icon-wotlk-right';
    }

    return null;
}

function UpdateProgressBar(element_id, new_pct)
{
    if (!progress || new_pct < 0 || new_pct > 100)
        return;

    var progress = $(element_id);
    progress.find('b').text(new_pct + '%');
    progress.find('img').css('background-position', (-120 + Math.floor(new_pct * 1.2)) + 'px 50%');
}

/* %d chars left warning for inputs. Works automatically if you add data-charwarning="id-of-element-to-put-the-warning-in". */

$(document).ready(function() {
    $('input').each(function() {
        var maxChars = $(this).attr('maxlength');
        var warningTextId = $(this).attr('data-charwarning');
        var warningText = warningTextId ? $('#' + warningTextId) : null;

        if (!maxChars || !warningText)
            return;

        hide = function() {
            warningText.hide();
        };

        display = function(length) {
            var charsRemaining = maxChars - length;
            var pctFilled = length / maxChars;
            var colorRed   = parseInt((pctFilled >= 0.5) ? 0xFF : (pctFilled                 * 2 * 0xFF )).toString(16);
            var colorGreen = parseInt((pctFilled <  0.5) ? 0xFF : (0xFF - ((pctFilled - 0.5) * 2 * 0xFF))).toString(16);

            /* Is there a better way to do this? */
            if (colorRed.length == 1)
                colorRed = '0' + colorRed;
            if (colorGreen.length == 1)
                colorGreen = '0' + colorGreen;

            warningText.text($WH.sprintf(LANG.charactersremaining_format, charsRemaining));
            warningText.show();
            warningText.css('color', '#' + colorRed + colorGreen + '00');
        };

        $(this).focus(function() { display($(this).val().length); })
               .blur(function() { hide(); })
               .keyup(function() { display($(this).val().length); });
    });
});

function GetN5(num)
{
    var absNum = Math.abs(num);

    if (absNum < 10000) // 1234 = 1,234
        return $WH.number_format(num);

    if (absNum < 100000) // 12345 = 12.3k
        return (Math.round(num / 100) / 10) + 'k';

    if (absNum < 1000000) // 123456 = 123k
        return Math.round(num / 1000) + 'k';

    if (absNum < 10000000) // 1234567 = 1.23m
        return (Math.round(num / 1000 / 10) / 100) + 'm';

    if (absNum < 100000000) // 12345678 = 12.3m
        return (Math.round(num / 1000 / 100) / 10) + 'm';

    if (absNum < 1000000000) // 123456789 = 123m
        return Math.round(num / 1000 / 1000) + 'm';

    if (absNum < 10000000000) // 1234567890 = 1,234,567,890 = 1.23b
        return (Math.round(num / 1000 / 1000 / 10) / 100) + 'b';

    if (absNum < 10000000000) // 1234567890 = 1,234,567,890 = 1.23b
        return (Math.round(num / 1000 / 1000 / 100) / 10) + 'b';

    return Math.round(num / 1000 / 1000 / 1000) + 'b';
}

function CreateAjaxLoader()
{
    return $('<img>').attr('alt', '').attr('src', g_staticUrl + '/images/icons/ajax.gif').addClass('ajax-loader');
}
