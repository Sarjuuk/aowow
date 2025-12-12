Listview.templates = {
    faction: {
        sort: [1],
        nItemsPerPage: -1,
        searchable: 1,
        filtrable: 1,

        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                value: 'name',
                compute: function(faction, td)
                {
                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(faction);
                    $WH.ae(a, $WH.ct(faction.name));
                    if (faction.expansion)
                    {
                        var sp = $WH.ce('span');
                        sp.className = g_GetExpansionClassName(faction.expansion);
                        $WH.ae(sp, a);
                        $WH.ae(td, sp);
                    }
                    else
                        $WH.ae(td, a);
                },
                getVisibleText: function(faction)
                {
                    var buff = faction.name + Listview.funcBox.getExpansionText(faction);

                    return buff;
                }
            },
            {
                id: 'side',
                name: LANG.side,
                type: 'text',
                compute: function(item, td)
                {
                    if (item.side && item.side != 3)
                    {
                        var sp = $WH.ce('span');
                        sp.className = (item.side == 1 ? 'icon-alliance' : 'icon-horde');
                        g_addTooltip(sp, g_sides[item.side]);

                        $WH.ae(td, sp);
                    }
                },
                getVisibleText: function(item)
                {
                    if (item.side)
                        return g_sides[item.side];
                },
                sortFunc: function(a, b, col)
                {
                    return $WH.strcmp(g_sides[a.side], g_sides[b.side]);
                }
            },
            {
                id: 'standing',
                name: LANG.reputation,
                value: 'standing',
                compute: function(faction, td)
                {
                    td.style.padding = 0;
                    $WH.ae(td, g_createReputationBar(faction.standing));
                },
                hidden: 1
            },
            {
                id: 'category',
                name: LANG.category,
                type: 'text',
                width: '16%',
                compute: function(faction, td)
                {
                    if (faction.category2 != null)
                    {
                        td.className = 'small q1';
                        var a = $WH.ce('a'),
                            href = '?factions=' + faction.category2;

                            if (faction.category)
                                href += '.' + faction.category;

                        a.href = href;
                        $WH.ae(a, $WH.ct(Listview.funcBox.getFactionCategory(faction.category, faction.category2)));
                        $WH.ae(td, a);
                    }
                },
                getVisibleText: function(faction)
                {
                    return Listview.funcBox.getFactionCategory(faction.category, faction.category2);
                },
                sortFunc: function(a, b, col)
                {
                    var _ = Listview.funcBox.getFactionCategory;
                    return $WH.strcmp(_(a.category, a.category2), _(b.category, b.category2));
                }
            }
        ],

        getItemLink: function(faction)
        {
            return '?faction=' + faction.id;
        }
    },

    guide: {
        sort: ['rating'],
        searchable: 1,
        filterable: 1,
        columns: [
            {
                id: 'title',
                name: LANG.types[11][0],
                type: 'text',
                align: 'left',
                value: 'title',
                compute: function(guide, td)
                {
                    var wrapper = $('<div>');
                    var title = $('<a>').text(guide.title).attr('href', guide.url + (typeof guide.rev != 'undefined' ? '&rev=' + guide.rev : ''));
                    if (guide.description)
                        title.mouseover(function(event) {$WH.Tooltip.showAtCursor(event, guide.description, 0, 0, 'q');}).mousemove(function(event) {$WH.Tooltip.cursorUpdate(event)}).mouseout(function() {$WH.Tooltip.hide()});
                    if (guide.sticky)
                        title.addClass('guide-sticky');
                    else if (guide.nvotes < 5)
                        title.addClass('guide-new');
                    $(wrapper).append(title);
                    $(td).append(wrapper);

                    // aowow - revision is custom
                    if (typeof guide.rev != 'undefined') {
                        wrapper.css('position', 'relative');
                        let revText = $('<div>', {
                            class: 'small',
                            css: { 'font-style': 'italic', position: 'absolute', right: '3px', bottom: '0px' }
                        }).text('rev.: ' + guide.rev);
                        $(wrapper).append(revText);
                    }

                    return;
                },
                getVisibleText: function(guide)
                {
                    return guide.title;
                }
            },
            {
                id: 'status',
                name: LANG.status,
                type: 'text',
                hidden: 1,
                compute: function(guide, td)
                {
                    var text = $('<span>').text(l_guide_states[guide.status]).css('color', l_guide_states_color[guide.status]);
                    $(td).append(text);
                },
                getVisibleText: function(guide)
                {
                    return l_guide_states[guide.status];
                },
                sortFunc: function(a, b)
                {
                    if (a.status == b.status)
                        return 0;

                    return a.status < b.status ? -1 : 1;
                }
            },
            {
                id: 'category',
                name: LANG.category,
                type: 'text',
                compute: function(guide, td)
                {
                    $(td).append($('<a>').attr('href', '?guides=' + guide.category).text(l_guide_categories[guide.category]).css('color', 'white'));

                    if (guide.classs && g_chr_specs[guide.classs])
                    {
                        $(td).append('&nbsp;' + LANG.dash + '&nbsp;').append($(Icon.create('class_' + g_file_classes[guide.classs], 0, null, '?class=' + guide.classs, null, null, false, null, true)));
                        let txt = $('<span>').attr('class', 'c' + guide.classs).text(' ' + g_chr_classes[guide.classs]);
                        if (guide.spec >= 0 && g_chr_specs[guide.classs][guide.spec])
                        {
                            $(td).append($(Icon.create(g_file_specs[guide.classs][guide.spec], 0, null, 'javascript:;', null, null, false, null, true)));
                            txt = $('<span>').attr('class', 'c' + guide.classs).text(' ' + g_chr_specs[guide.classs][guide.spec]);
                        }
                        $(td).append(txt);
                    }
                },
                getVisibleText: function(guide)
                {
                    let b = l_guide_categories[guide.category];
                    if (guide.classs)
                        b += ' ' + g_chr_classes[guide.classs];
                    if (guide.spec >= 0)
                        b += ' ' + g_chr_specs[guide.classs][guide.spec];

                    return b;
                },
                sortFunc: function(a, b)
                {
                    if (a.category == b.category)
                        return 0;

                    return a.category < b.category ? -1 : 1;
                }
            },
            {
                id: 'author',
                name: LANG.author,
                type: 'text',
                compute: function(guide, td)
                {
                    var userLink = $('<a>').attr('href', '?user=' + guide.author).text(guide.author);
                    var color = g_GetStaffColorFromRoles(guide.authorroles);

                    if (color != '')
                        userLink.addClass(color);
                    else
                        userLink.css('color', 'white');

                    $(td).append(userLink);
                },
                getVisibleText: function(guide)
                {
                    return guide.author;
                },
                sortFunc: function(a, b)
                {
                    return $WH.strcmp(a.author, b.author);
                }
            },
            {
                id: 'rating',
                name: LANG.rating,
                type: 'range',
                width: '200px',
                compute: function(guide, td)
                {
                    $(td).append(GetStars(guide.rating));

                    var voteText = $("<span>").css('font-size', '85%');

                    // aowow - localized
                    if (guide.rating < 0)
                        voteText.text($WH.sprintf((5 - guide.nvotes) == 1 ? LANG.needsvote_format : LANG.needsvotes_format, 5 - guide.nvotes));
                     // voteText.text($WH.sprintf(' (needs $1 more $2)', 5 - guide.nvotes, (5 - guide.nvotes) == 1 ? 'vote' : 'votes'));
                    else
                        voteText.text($WH.sprintf(guide.nvotes == 1 ? LANG.outofvote_format : LANG.outofvotes_format, GetN5(guide.nvotes)));
                     // voteText.text(' (out of ' + GetN5(guide.nvotes) + ' ' + (guide.nvotes == 1 ? 'vote' : 'votes') + ')');

                    $(td).append(voteText);
                },
                getMinValue: function(item)
                {
                    return guide.rating;
                },
                getMaxValue: function(item)
                {
                    return guide.rating;
                },
                sortFunc: function(a, b, col)
                {
                    if (a.sticky && !b.sticky)
                        return -1;
                    else if (!a.sticky && b.sticky)
                        return 1;

                    var isANew = a.rating == -1 && a.author != 'Wowhead';
                    var isBNew = b.rating == -1 && b.author != 'Wowhead';

                    if (isANew && !isBNew)
                        return -1;
                    else if (isBNew && !isANew)
                        return 1;

                    if (a.rating == b.rating)
                        return 0;

                    return a.rating < b.rating ? 1 : -1;
                }
            },
            {
                id: 'views',
                name: LANG.views,
                type: 'text',
                width: '120px',
                compute: function(guide, td)
                {
                    var span = $('<span>');
                    span.text(GetN5(guide.views));

                    if (guide.views >= 50000)
                    {
                        span.css('color', '#FF4040');
                        span.css('font-weight', 'bold');
                    }
                    else if (guide.views >= 25000)
                    {
                        span.css('color', '#FF8000');
                        span.css('font-weight', 'bold');
                    }
                    else if (guide.views >= 10000)
                        span.css('color', '#A335EE');
                    else if (guide.views >= 5000)
                        span.css('color', '#0070DD');
                    else if (guide.views >= 1000)
                        span.css('color', '#1EFF00');

                    $(td).append(span);
                },
                getVisibleText: function(guide)
                {
                    return guide.author;
                },
                sortFunc: function(a, b)
                {
                    if (a.views == b.views)
                        return 0;

                    return a.views < b.views ? -1 : 1;
                }
            },
            {
                id: 'comments',
                name: LANG.comments,
                type: 'text',
                width: '120px',
                compute: function(guide)
                {
                    return GetN5(guide.comments);
                },
                sortFunc: function(a, b)
                {
                    if (a.comments == b.comments)
                        return 0;

                    return a.comments < b.comments ? -1 : 1;
                }
            },
            {
                id: 'patch',
                name: LANG.patch,
                type: 'text',
                width: '120px',
                compute: function(guide, td)
                {
                    var span = $("<span>").text($WH.sprintf('$1.$2.$3', Math.floor(guide.patch / 100 / 100), Math.floor(guide.patch / 100) % 100, guide.patch % 100));
                    var matches = g_getPatchVersion.V[g_getPatchVersion.V.length - 2].match(/([0-9]+)\.([0-9]+)\.([0-9]+)/);
                    var currentPatch = matches[1] * 100 * 100 + matches[2] * 100;

                    if (parseInt(guide.patch / 100) * 100 >= currentPatch)
                        span.addClass('q2');

                    $(td).append(span);
                    return;
                },
                sortFunc: function(a, b)
                {
                    if (a.patch == b.patch)
                        return 0;

                    return a.patch < b.patch ? -1 : 1;
                }
            }
        ],

        getItemLink: function(guide)
        {
            return guide.url + (typeof guide.rev != 'undefined' ? '&rev=' + guide.rev : '');
        }
    },

    item: {
        sort: [-2],
        searchable: 1,
        filtrable: 1,
        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                span: 2,
                value: 'name',
                compute: function(item, td, tr) {
                    if (item.upgraded) {
                        tr.className = 'upgraded';
                    }

                    var i = $WH.ce('td');
                    i.style.width = '1px';
                    i.style.padding = '0';
                    i.style.borderRight = 'none';

                    var
                        num = null,
                        qty = null;

                    if (item.stack != null) {
                        num = Listview.funcBox.createTextRange(item.stack[0], item.stack[1]);
                    }
                    if (item.avail != null) {
                        qty = item.avail;
                    }

                    if (item.id) {
                        if (item.name.charAt(0) == '@')     // aowow - don't create link on icon for ref loot
                            $WH.ae(i, Icon.create(g_items.getIcon(item.id), this.iconSize ?? 1, null, null, num, qty));
                        else
                            $WH.ae(i, g_items.createIcon(item.id, (this.iconSize == null ? 1 : this.iconSize), num, qty));
                    }
                    $WH.ae(tr, i);
                    td.style.borderLeft = 'none';

                    var a = $WH.ce('a');
                    a.className = 'q' + (7 - parseInt(item.name.charAt(0)));
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(item);

                    if (item.rel) {
                        Icon.getLink(i.firstChild).rel = item.rel;
                        a.rel = item.rel;
                    }

                    $WH.ae(a, $WH.ct(item.name.substring(1)));

                    var wrapper = $WH.ce('div');
                    $WH.ae(wrapper, a);

                    if (item.reqclass) {
                        var d = $WH.ce('div');
                        d.className = 'small2';

                        var classes = Listview.funcBox.assocBinFlags(item.reqclass, g_chr_classes);

                        for (var i = 0, len = classes.length; i < len; ++i) {
                            if (i > 0) {
                                $WH.ae(d, $WH.ct(', '));
                            }
                            var a = $WH.ce('a');
                            a.href = '?class=' + classes[i];
                            a.className = 'c' + classes[i];
                            $WH.st(a, g_chr_classes[classes[i]]);
                            $WH.ae(d, a);
                        }

                        $WH.ae(wrapper, d);
                    }

                    if (typeof fi_nExtraCols == 'number' && fi_nExtraCols >= 5) {
                        if (item.source != null && item.source.length == 1) {
                            if (item.reqclass) {
                                $WH.ae(d, $WH.ct(LANG.dash));
                            }
                            else {
                                var d = $WH.ce('div');
                                d.className = 'small2';
                            }

                            var sm = (item.sourcemore ? item.sourcemore[0] : {});
                            var type = 0;

                            if (sm.t) {
                                type = sm.t;

                                var a = $WH.ce('a');
                                if (sm.q != null) {
                                    a.className = 'q' + sm.q;
                                }
                                else {
                                    a.className = 'q1';
                                }
                                a.href = '?' + g_types[sm.t] + '=' + sm.ti;

                                if (sm.n.length <= 30) {
                                    $WH.ae(a, $WH.ct(sm.n));
                                }
                                else {
                                    a.title = sm.n;
                                    $WH.ae(a, $WH.ct($WH.trim(sm.n.substr(0, 27)) + '...'));
                                }

                                $WH.ae(d, a);
                            }
                            else {
                                $WH.ae(d, $WH.ct(Listview.funcBox.getUpperSource(item.source[0], sm)));
                            }

                            var ls = Listview.funcBox.getLowerSource(item.source[0], sm, type);

                            if (ls != null) {
                                $WH.ae(d, $WH.ct(LANG.hyphen));

                                if (ls.pretext) {
                                    $WH.ae(d, $WH.ct(ls.pretext));
                                }

                                if (ls.url) {
                                    var a = $WH.ce('a');
                                    a.className = 'q1';
                                    a.href = ls.url;
                                    $WH.ae(a, $WH.ct(ls.text));
                                    $WH.ae(d, a);
                                }
                                else {
                                    $WH.ae(d, $WH.ct(ls.text));
                                }

                                if (ls.posttext) {
                                    $WH.ae(d, $WH.ct(ls.posttext));
                                }
                            }

                            $WH.ae(wrapper, d);
                        }
                    }

                    if (item.heroic || item.reqrace) {
                        wrapper.style.position = 'relative';
                        var d = $WH.ce('div');
                        d.className = 'small';
                        d.style.fontStyle = 'italic';
                        d.style.position = 'absolute';
                        d.style.right = d.style.bottom = '3px';

                        if (item.heroic) {
                            var s = $WH.ce('span');
                            s.className = 'q2';
                            $WH.ae(s, $WH.ct(LANG.lvitem_heroicitem));
                            $WH.ae(d, s);
                        }

                        if (item.reqrace) {
                            if ((item.reqrace & 1791) != 1101 && (item.reqrace & 1791) != 690) {
                                if (item.heroic) {
                                    $WH.ae(d, $WH.ce('br'));
                                    d.style.bottom = '-6px';
                                }

                                var races = Listview.funcBox.assocBinFlags(item.reqrace, g_chr_races);

                                for (var i = 0, len = races.length; i < len; ++i) {
                                    if (i > 0) {
                                        $WH.ae(d, $WH.ct(', '));
                                    }
                                    var a = $WH.ce('a');
                                    a.href = '?race=' + races[i];
                                    $WH.st(a, g_chr_races[races[i]]);
                                    $WH.ae(d, a);
                                }

                                d.className += ' q1';
                            }
                        }

                        $WH.ae(wrapper, d);
                    }

                    $WH.ae(td, wrapper);
                },
                getVisibleText: function(item) {
                    var buff = item.name.substring(1);

                    if (item.heroic) {
                        buff += ' ' + LANG.lvitem_heroicitem;
                    }

                    if (item.reqrace) {
                        buff += ' ' + Listview.funcBox.arrayText(Listview.funcBox.assocBinFlags(item.reqrace, g_chr_races), g_chr_races);
                    }

                    if (item.reqclass) {
                        buff += ' ' + Listview.funcBox.arrayText(Listview.funcBox.assocBinFlags(item.reqclass, g_chr_classes), g_chr_classes);
                    }

                    if (typeof fi_nExtraCols == 'number' && fi_nExtraCols >= 5) {
                        if (item.source != null && item.source.length == 1) {
                            var sm = (item.sourcemore ? item.sourcemore[0] : {});
                            var type = 0;

                            if (sm.t) {
                                type = sm.t;
                                buff += ' ' + sm.n;
                            }
                            else {
                                buff += ' ' + Listview.funcBox.getUpperSource(item.source[0], sm);
                            }

                            var ls = Listview.funcBox.getLowerSource(item.source[0], sm, type);

                            if (ls != null) {
                                if (ls.pretext) {
                                    buff += ' ' + ls.pretext;
                                }

                                buff += ' ' + ls.text;

                                if (ls.posttext) {
                                    buff += ' ' + ls.posttext;
                                }
                            }
                        }
                    }

                    return buff;
                }
            },
            {
                id: 'level',
                name: LANG.level,
                value: 'level',
                type: 'range',
                getMinValue: function(item) {
                    return item.minlevel ? item.minlevel : item.level;
                },
                getMaxValue: function(item) {
                    return item.maxlevel ? item.maxlevel : item.level;
                },
                compute: function(item, td) {
                    if (item.minlevel && item.maxlevel) {
                        if (item.minlevel != item.maxlevel) {
                            return item.minlevel + LANG.hyphen + item.maxlevel;
                        }
                        else {
                            return item.minlevel;
                        }
                    }
                    else {
                        return item.level;
                    }
                },
                sortFunc: function(a, b, col) {
                    if (col > 0) {
                        return $WH.strcmp(a.minlevel, b.minlevel) || $WH.strcmp(a.maxlevel, b.maxlevel) || $WH.strcmp(a.level, b.level);
                    }
                    else {
                        return $WH.strcmp(a.maxlevel, b.maxlevel) || $WH.strcmp(a.minlevel, b.minlevel) || $WH.strcmp(a.level, b.level);
                    }
                }
            },
            {
                id: 'reqlevel',
                name: LANG.req,
                tooltip: LANG.tooltip_reqlevel,
                value: 'reqlevel',
                compute: function(item, td) {
                    if (item.reqlevel > 1) {
                        return item.reqlevel;
                    }
                }
            },
            {
                id: 'side',
                name: LANG.side,
                type: 'text',
                compute: function(item, td) {
                    if (item.side && item.side != 3) {
                        var sp = $WH.ce('span');
                        sp.className = (item.side == 1 ? 'icon-alliance' : 'icon-horde');
                        g_addTooltip(sp, g_sides[item.side]);
                        $WH.ae(td, sp);
                    }
                },
                getVisibleText: function(item) {
                    if (item.side) {
                        return g_sides[item.side];
                    }
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_sides[a.side], g_sides[b.side]);
                }
            },
            {
                id: 'dps',
                name: LANG.dps,
                value: 'dps',
                compute: function(item, td) {
                    return (item.dps || 0).toFixed(1);
                },
                hidden: true
            },
            {
                id: 'speed',
                name: LANG.speed,
                value: 'speed',
                compute: function(item, td) {
                    return (item.speed || 0).toFixed(2);
                },
                hidden: true
            },
            {
                id: 'armor',
                name: LANG.armor,
                value: 'armor',
                compute: function(item, td) {
                    if (item.armor > 0) {
                        return item.armor;
                    }
                },
                hidden: true
            },
            {
                id: 'slot',
                name: LANG.slot,
                type: 'text',
                compute: function(item, td) {
                    $WH.nw(td);

                    return g_item_slots[item.slot];
                },
                getVisibleText: function(item) {
                    return g_item_slots[item.slot];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_item_slots[a.slot], g_item_slots[b.slot]);
                }
            },
            {
                id: 'slots',
                name: LANG.slots,
                value: 'nslots',
                hidden: true
            },
            {
                id: 'skill',
                name: LANG.skill,
                value: 'skill',
                hidden: true
            },
            {
                id: 'glyph',
                name: LANG.glyphtype,
                type: 'text',
                value: 'glyph',
                compute: function(item, td) {
                    if (item.glyph) {
                        return g_item_glyphs[item.glyph];
                    }
                },
                getVisibleText: function(item) {
                    return g_item_glyphs[item.glyph];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_item_glyphs[a.glyph], g_item_glyphs[b.glyph]);
                },
                hidden: true
            },
            {
                id: 'source',
                name: LANG.source,
                type: 'text',
                compute: function(item, td) {
                    if (this.iconSize == 0) {
                        td.className = 'small';
                    }
                    if (item.source != null) {
                        if (item.source.length == 1) {
                            $WH.nw(td);

                            var sm = (item.sourcemore ? item.sourcemore[0] : {});
                            var type = 0;

                            if (sm.t) {
                                type = sm.t;

                                var a = $WH.ce('a');
                                if (sm.q != null) {
                                    a.className = 'q' + sm.q;
                                }
                                else {
                                    a.className = 'q1';
                                }
                                a.href = '?' + g_types[sm.t] + '=' + sm.ti;
                                a.style.whiteSpace = 'nowrap';

                                if (sm.icon) {
                                    a.className += ' icontiny tinyspecial';
                                    a.style.backgroundImage = 'url("' + g_staticUrl + '/images/wow/icons/tiny/' + sm.icon.toLowerCase() + '.gif")';
                                }

                                $WH.ae(a, $WH.ct(sm.n));
                                $WH.ae(td, a);
                            }
                            else {
                                $WH.ae(td, $WH.ct(Listview.funcBox.getUpperSource(item.source[0], sm)));
                            }

                            var ls = Listview.funcBox.getLowerSource(item.source[0], sm, type);

                            if (this.iconSize != 0 && ls != null) {
                                var div = $WH.ce('div');
                                div.className = 'small2';

                                if (ls.pretext) {
                                    $WH.ae(div, $WH.ct(ls.pretext));
                                }

                                if (ls.url) {
                                    var a = $WH.ce('a');
                                    a.className = 'q1';
                                    a.href = ls.url;
                                    $WH.ae(a, $WH.ct(ls.text));
                                    $WH.ae(div, a);
                                }
                                else {
                                    $WH.ae(div, $WH.ct(ls.text));
                                }

                                if (ls.posttext) {
                                    $WH.ae(div, $WH.ct(ls.posttext));
                                }

                                $WH.ae(td, div);
                            }
                        }
                        else {
                            var buff = '';
                            for (var i = 0, len = item.source.length; i < len; ++i) {
                                if (i > 0) {
                                    buff += LANG.comma;
                                }
                                buff += g_sources[item.source[i]];
                            }
                            return buff;
                        }
                    }
                },
                getVisibleText: function(item) {
                    if (item.source != null) {
                        if (item.source.length == 1) {
                            var buff = '';

                            var sm = (item.sourcemore ? item.sourcemore[0] : {});
                            var type = 0;

                            if (sm.t) {
                                type = sm.t;
                                buff += ' ' + sm.n;
                            }
                            else {
                                buff += ' ' + Listview.funcBox.getUpperSource(item.source[0], sm);
                            }

                            var ls = Listview.funcBox.getLowerSource(item.source[0], sm, type);

                            if (ls != null) {
                                if (ls.pretext) {
                                    buff += ' ' + ls.pretext;
                                }

                                buff += ' ' + ls.text;

                                if (ls.posttext) {
                                    buff += ' ' + ls.posttext;
                                }
                            }

                            return buff;
                        }
                        else {
                            return Listview.funcBox.arrayText(item.source, g_sources);
                        }
                    }
                },
                sortFunc: function(a, b) {
                    var res = Listview.funcBox.assocArrCmp(a.source, b.source, g_sources);
                    if (res != 0) {
                        return res;
                    }

                    var
                        na = (a.sourcemore && a.source.length == 1 ? a.sourcemore[0].n : null),
                        nb = (b.sourcemore && b.source.length == 1 ? b.sourcemore[0].n : null);

                    return $WH.strcmp(na, nb);
                }
            },
            {
                id: 'type',
                name: LANG.type,
                type: 'text',
                compute: function(item, td) {
                    if (item.classs == null) {
                        return;
                    }

                    td.className = 'small q1';
                    $WH.nw(td);
                    var a = $WH.ce('a');

                    var it = Listview.funcBox.getItemType(item.classs, item.subclass, item.subsubclass);

                    a.href = it.url;
                    $WH.ae(a, $WH.ct(it.text));
                    $WH.ae(td, a);
                },
                getVisibleText: function(item) {
                    return Listview.funcBox.getItemType(item.classs, item.subclass, item.subsubclass).text;
                },
                sortFunc: function(a, b, col) {
                    var _ = Listview.funcBox.getItemType;
                    return $WH.strcmp(_(a.classs, a.subclass, a.subsubclass).text, _(b.classs, b.subclass, b.subsubclass).text);
                }
            },
            {
                id: 'completed', // Listview.COLUMN_ID_COMPLETION
                name: LANG.completion, // WH.TERMS.completion
                hidden: true,
                compute: function (item, td)
                {
                    var skip = !item.hasOwnProperty('classs') || !Listview.templates.item._validCompletionCategory(item.classs, item.subclass, item.quality);
                    $WH.addCompletionIcons(td, 3, item.id, skip);
                },
                sortFunc: function (a, b)
                {
                    // return $WH.stringCompare(
                    return $WH.strcmp(
                        $WH.getCompletionFlags(3, a.id),
                        $WH.getCompletionFlags(3, b.id)
                    );
                },
                getValue: function (item) {
                    var value = 0;
                    var completionData = g_user.completion?.hasOwnProperty(3) ? g_user.completion[3] : {};

                    if (!item.hasOwnProperty('classs') || !Listview.templates.item._validCompletionCategory(item.classs, item.subclass, item.quality))
                        return -1;

                    for (var i in g_user.characters)
                    {
                        var profile = g_user.characters[i];
                        if (!(profile.id in completionData))
                            continue;

                        if ($WH.in_array(completionData[profile.id], item.id) != -1)
                            value++;
                    }

                    return value;
                }
            }
        ],

        getItemLink: function(item) {
            return item.name.charAt(0) == '@' ? 'javascript:;' : '?item=' + item.id;
        },

        _validCompletionCategory: function (classs, subclass, quality) {
            return $WH.in_array(g_completion_categories[3], classs) != -1 ||
                   $WH.in_array(g_completion_categories[3], '' + classs + '-' + subclass) != -1 ||
                   $WH.in_array(g_completion_categories[3], '' + classs + 'q' + quality) != -1
        },

        onBeforeCreate: function() {
            var nComparable = false;

            for (var i = 0, len = this.data.length; i < len; ++i) {
                var item = this.data[i];

                if ((item.slot > 0 && item.slot != 18) || (item.classs == 3 && item.subclass != 7) || ($WH.in_array(ModelViewer.validSlots, item.slotbak) >= 0 && item.displayid > 0) || item.modelviewer) { // Equippable, and not a bag, or has a model
                    ++nComparable;
                }
                else {
                    item.__nochk = 1;
                }
            }

            if (nComparable > 0) {
                this.mode = Listview.MODE_CHECKBOX;
                this._nComparable = nComparable;
            }

            for (var i in this.columns)
            {
                if (this.columns[i].id == 'completed' && this.columns[i].hidden)
                {
                    if ($WH.isset('g_user') && 'characters' in g_user && $WH.in_array(this.hiddenCols, this.columns[i].id) == -1)
                    {
                        var n = 0;
                        for (var j in this.data)
                            if (this.data[j].hasOwnProperty('classs') && Listview.templates.item._validCompletionCategory(this.data[j].classs, this.data[j].subclass, this.data[j].quality))
                                n++;

                        if (n > this.data.length * 0.1)
                            this.visibility.push(parseInt(i));
                    }
                }
            }
        },

        createCbControls: function(div, topBar) {
            if (!topBar && this._nComparable < 15) {
                return;
            }

            if (this.mode != Listview.MODE_CHECKBOX) {
                return;
            }

            var
                iCompare  = $WH.ce('input'),
                iViewIn3d = $WH.ce('input'),
                iEquip    = $WH.ce('input'),
                iDeselect = $WH.ce('input'),
                pinnedChr = g_user.characters ? $WH.array_filter(g_user.characters, function(row) {
                    return row.pinned;
                }) : false;

            iCompare.type = iViewIn3d.type = iEquip.type = iDeselect.type = 'button';

            iCompare.value  = LANG.button_compare;
            iViewIn3d.value = LANG.button_viewin3d;
            iEquip.value    = LANG.button_equip;
            iDeselect.value = LANG.button_deselect;

            iCompare.onclick  = this.template.compareItems.bind(this);
            iViewIn3d.onclick = this.template.viewIn3d.bind(this);
            iDeselect.onclick = Listview.cbSelect.bind(this, false);

            if (this._nComparable == 0 || typeof this._nComparable == 'undefined') {
                iCompare.disabled  = 'disabled';
                iViewIn3d.disabled = 'disabled';
                iEquip.disabled    = 'disabled';
                iDeselect.disabled = 'disabled';
                pinnedChr = false;
            }

            $WH.ae(div, iCompare);
            $WH.ae(div, iViewIn3d);

            if (pinnedChr && pinnedChr.length) {
                iEquip.onclick = this.template.equipItems.bind(this, pinnedChr[0]);
                $WH.ae(div, iEquip);
            }

            $WH.ae(div, iDeselect);
        },

        compareItems: function() {
            var rows = this.getCheckedRows();
            if (!rows.length) {
                return;
            }

            var data = '';
            $WH.array_walk(rows, function(x) {
                if (x.slot == 0 || x.slot == 18) {
                    return;
                }

                data += x.id + ';';
            });
            su_addToSaved($WH.rtrim(data, ';'), rows.length);
        },

        viewIn3d: function() {
            var rows = this.getCheckedRows();
            if (!rows.length) {
                return;
            }

            var
                hasData = false,
                repeatData = false,
                badData = false;
            var data = {};
            var model = null;
            $WH.array_walk(rows, function(x) {
                if ($WH.in_array(ModelViewer.validSlots, x.slotbak) >= 0 && x.displayid > 0) {
                    var slot = ModelViewer.slotMap[x.slotbak];
                    if (data[slot]) {
                        repeatData = true;
                    }
                    data[slot] = x.displayid;
                    hasData = true;
                }
                else if (x.modelviewer) {
                    model = x.modelviewer;
                }
                else {
                    badData = true;
                }
            });

            var message = null;
            if (model) {
                if (hasData || badData) {
                    message = LANG.dialog_cantdisplay;
                }
                ModelViewer.show({
                    type: model.type,
                    displayId: model.displayid,
                    slot: model.slot,
                    message: message
                });
            }
            else {
                if (repeatData || badData) {
                    message = LANG.dialog_cantdisplay;
                }
                var equipList = [];
                for (var i in data) {
                    equipList.push(parseInt(i));
                    equipList.push(data[i]);
                }
                if (equipList.length > 0) {
                    ModelViewer.show({
                        type: 4,
                        equipList: equipList,
                        message: message
                    });
                }
                else {
                    alert(LANG.message_nothingtoviewin3d);
                }
            }
        },

        equipItems: function(character) {
            var rows = this.getCheckedRows();
            if (!rows.length) {
                return;
            }

            var data = '';
            $WH.array_walk(rows, function(x) {
                if (x.slot == 0 || x.slot == 18) {
                    return;
                }
                data += x.id + ':';
            });
            location.href = g_getProfileUrl(character) + '&items=' + $WH.rtrim(data, ':');
        }
    },

    itemset: {
        sort: [1],
        nItemsPerPage: 75,
        searchable: 1,
        filtrable: 1,
        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                value: 'name',
                compute: function(itemSet, td) {
                    var a = $WH.ce('a');
                    a.className = 'q' + (7 - parseInt(itemSet.name.charAt(0)));
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(itemSet);
                    $WH.ae(a, $WH.ct(itemSet.name.substring(1)));

                    var div = $WH.ce('div');
                    div.style.position = 'relative';
                    $WH.ae(div, a);

                    if (itemSet.heroic) {
                        var d = $WH.ce('div');
                        d.className = 'small q2';
                        d.style.fontStyle = 'italic';
                        d.style.position = 'absolute';
                        d.style.right = '3px';
                        d.style.bottom = '3px';
                        $WH.ae(d, $WH.ct(LANG.lvitem_heroicitem));
                        $WH.ae(div, d);
                    }

                    $WH.ae(td, div);

                    if (itemSet.note) {
                        var d = $WH.ce('div');
                        d.className = 'small';
                        $WH.ae(d, $WH.ct(g_itemset_notes[itemSet.note]));
                        $WH.ae(td, d);
                    }
                },
                getVisibleText: function(itemSet) {
                    var buff = itemSet.name.substring(1);
                    if (itemSet.note) {
                        buff += ' ' + g_itemset_notes[itemSet.note];
                    }
                    return buff;
                }
            },
            {
                id: 'level',
                name: LANG.level,
                type: 'range',
                getMinValue: function(itemSet) {
                    return itemSet.minlevel;
                },
                getMaxValue: function(itemSet) {
                    return itemSet.maxlevel;
                },
                compute: function(itemSet, td) {
                    if (itemSet.minlevel > 0 && itemSet.maxlevel > 0) {
                        if (itemSet.minlevel != itemSet.maxlevel) {
                            return itemSet.minlevel + LANG.hyphen + itemSet.maxlevel;
                        }
                        else {
                            return itemSet.minlevel;
                        }
                    }
                    else {
                        return -1;
                    }
                },
                sortFunc: function(a, b, col) {
                    if (col > 0) {
                        return $WH.strcmp(a.minlevel, b.minlevel) || $WH.strcmp(a.maxlevel, b.maxlevel);
                    }
                    else {
                        return $WH.strcmp(a.maxlevel, b.maxlevel) || $WH.strcmp(a.minlevel, b.minlevel);
                    }
                }
            },
            {
                id: 'pieces',
                name: LANG.pieces,
                getValue: function(itemSet) {
                    return itemSet.pieces.length;
                },
                compute: function(itemSet, td) {
                    td.style.padding = '0';
                    Listview.funcBox.createCenteredIcons(itemSet.pieces, td);
                },
                sortFunc: function(a, b) {
                    var lena = (a.pieces != null ? a.pieces.length : 0);
                    var lenb = (b.pieces != null ? b.pieces.length : 0);
                    return $WH.strcmp(lena, lenb);
                }
            },
            {
                id: 'type',
                name: LANG.type,
                type: 'text',
                compute: function(itemSet, td) {
                    return g_itemset_types[itemSet.type];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_itemset_types[a.type], g_itemset_types[b.type]);
                }
            },
            {
                id: 'classes',
                name: LANG.classes,
                type: 'text',
                width: '20%',
                getVisibleText: function(itemSet) {
                    var str = '';
                    if (itemSet.reqclass) {
                        var classes = Listview.funcBox.assocBinFlags(itemSet.reqclass, g_chr_classes);

                        for (var i = 0, len = classes.length; i < len; ++i) {
                            if (i > 0) {
                                str += LANG.comma;
                            }
                            str += g_chr_classes[classes[i]];
                        }
                    }
                    return str;
                },
                compute: function(itemSet, td) {
                    if (itemSet.reqclass) {
                        var classes = Listview.funcBox.assocBinFlags(itemSet.reqclass, g_chr_classes);

                        var d = $WH.ce('div');
                        d.style.width = (26 * classes.length) + 'px';
                        d.style.margin = '0 auto';

                        for (var i = 0, len = classes.length; i < len; ++i) {
                            var icon = Icon.create('class_' + g_file_classes[classes[i]], 0, null, '?class=' + classes[i]);
                            icon.style.cssFloat = icon.style.styleFloat = 'left';
                            g_addTooltip(icon, g_chr_classes[classes[i]], 'c' + classes[i]);

                            $WH.ae(d, icon);
                        }

                        $WH.ae(td, d);
                    }
                },
                sortFunc: function(a, b, col) {
                    return Listview.funcBox.assocArrCmp(Listview.funcBox.assocBinFlags(a.reqclass, g_chr_classes), Listview.funcBox.assocBinFlags(b.reqclass, g_chr_classes), g_chr_classes);
                }
            }
        ],

        getItemLink: function(itemSet) {
            return '?itemset=' + itemSet.id;
        }
    },

    npc: {
        sort: [1],
        nItemsPerPage: 100,
        searchable: 1,
        filtrable: 1,

        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                value: 'name',
                compute: function(npc, td) {
                    var wrapper = $WH.ce('div');

                    if (npc.boss) {
                        wrapper.className = 'icon-boss-padded';
                    }

                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(npc);
                    $WH.ae(a, $WH.ct(npc.name));

                    if (npc.hasQuests != null) {
                        a.className += " icontiny tinyspecial";
                        a.style.backgroundImage = "url(" + g_staticUrl + "/images/wow/icons/tiny/quest_start.gif)";
                    }

                    if (g_user.roles & U_GROUP_PREMIUM_PERMISSIONS)
                        a.rel = 'map';

                    $WH.ae(wrapper, a);

                    if (npc.tag != null) {
                        var d = $WH.ce('div');
                        d.className = 'small';
                        $WH.ae(d, $WH.ct('<' + npc.tag + '>'));
                        $WH.ae(wrapper, d);
                    }

                    if (npc.method != null) {
                        td.style.position = 'relative';

                        var d = $WH.ce('div');
                        d.className = 'small';
                        d.style.fontStyle = 'italic';
                        d.style.position = 'absolute';
                        d.style.right = '3px';
                        d.style.bottom = '3px';
                        d.style.textAlign = 'right';

                        $WH.ae(d, $WH.ct(npc.method ? LANG.added : LANG.lvquest_removed));
                        $WH.ae(wrapper, d);
                    }

                    $WH.ae(td, wrapper);
                },
                getVisibleText: function(npc) {
                    var buff = npc.name;
                    if (npc.tag) {
                        buff += ' <' + npc.tag + '>';
                    }
                    if (npc.boss) {
                        buff += ' boss skull';
                    }
                    return buff;
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(b.boss, a.boss) || $WH.strcmp(a.name, b.name) || $WH.strcmp(a.hasQuests, b.hasQuests);
                }
            },
            {
                id: 'level',
                name: LANG.level,
                type: 'range',
                width: '10%',
                getMinValue: function(npc) {
                    return npc.minlevel;
                },
                getMaxValue: function(npc) {
                    return npc.maxlevel;
                },
                compute: function(npc, td) {
                    if (npc.classification) {
                        var d = $WH.ce('div');
                        d.className = 'small';
                        $WH.ae(d, $WH.ct(g_npc_classifications[npc.classification]));
                        $WH.ae(td, d);
                    }

                    if (npc.classification == 3 || npc.maxlevel == 9999) {
                        return '??';
                    }

                    if (npc.minlevel > 0 && npc.maxlevel > 0) {
                        if (npc.minlevel != npc.maxlevel) {
                            return npc.minlevel + LANG.hyphen + npc.maxlevel;
                        }
                        else {
                            return npc.minlevel;
                        }
                    }

                    return -1;
                },
                getVisibleText: function(npc) {
                    var buff = '';

                    if (npc.classification) {
                        buff += ' ' + g_npc_classifications[npc.classification];
                    }

                    if (npc.minlevel > 0 && npc.maxlevel > 0) {
                        buff += ' ';
                        if (npc.maxlevel == 9999) {
                            buff += '??';
                        }
                        else if (npc.minlevel != npc.maxlevel) {
                            buff += npc.minlevel + LANG.hyphen + npc.maxlevel;
                        }
                        else {
                            buff += npc.minlevel;
                        }
                    }

                    return buff;
                },
                sortFunc: function(a, b, col) {
                    if (col > 0) {
                        return $WH.strcmp(a.minlevel, b.minlevel) || $WH.strcmp(a.maxlevel, b.maxlevel) || $WH.strcmp(a.classification, b.classification);
                    }
                    else {
                        return $WH.strcmp(a.maxlevel, b.maxlevel) || $WH.strcmp(a.minlevel, b.minlevel) || $WH.strcmp(a.classification, b.classification);
                    }
                }
            },
            {
                id: 'location',
                name: LANG.location,
                type: 'text',
                compute: function(npc, td) {
                    return Listview.funcBox.location(npc, td);
                },
                getVisibleText: function(npc) {
                    return Listview.funcBox.arrayText(npc.location, g_zones);
                },
                sortFunc: function(a, b, col) {
                    return Listview.funcBox.assocArrCmp(a.location, b.location, g_zones);
                }
            },
            {
                id: 'react',
                name: LANG.react,
                type: 'text',
                width: '10%',
                value: 'react',
                filtrable: 0,
                compute: function(npc, td) {
                    if (npc.react == null) {
                        return -1;
                    }

                    var sides = [LANG.lvnpc_alliance, LANG.lvnpc_horde];
                    var c = 0;
                    for (var k = 0; k < 2; ++k) {
                        if (npc.react[k] != null) {
                            if (c++ > 0) {
                                $WH.ae(td, $WH.ct(' '));
                            }
                            var sp = $WH.ce('span');

                            sp.className = (npc.react[k] < 0 ? 'q10': (npc.react[k] > 0 ? 'q2': 'q'));

                            $WH.ae(sp, $WH.ct(sides[k]));
                            $WH.ae(td, sp);
                        }
                    }
                }
            },
            {
                id: 'skin',
                name: LANG.skin,
                type: 'text',
                value: 'skin',
                compute: function(npc, td) {
                    if (npc.skin) {
                        var a = $WH.ce('a');
                        a.className = 'q1';
                        a.href = '?npcs&filter=cr=35;crs=0;crv=' + npc.skin;
                        $WH.ae(a, $WH.ct(npc.skin));
                        $WH.ae(td, a);
                    }
                },
                hidden: 1
            },
            {
                id: 'petfamily',
                name: LANG.petfamily,
                type: 'text',
                width: '12%',
                compute: function(npc, td) {
                    td.className = 'q1';

                    var a = $WH.ce('a');
                    a.href = '?pet=' + npc.family;
                    $WH.ae(a, $WH.ct(g_pet_families[npc.family]));
                    $WH.ae(td, a);
                },
                getVisibleText: function(npc) {
                    return g_pet_families[npc.family];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_pet_families[a.family], g_pet_families[b.family]);
                },
                hidden: 1
            },
            {
                id: 'type',
                name: LANG.type,
                type: 'text',
                width: '12%',
                compute: function(npc, td) {
                    td.className = 'small q1';

                    var a = $WH.ce('a');
                    a.href = '?npcs=' + npc.type;
                    $WH.ae(a, $WH.ct(g_npc_types[npc.type]));
                    $WH.ae(td, a);
                },
                getVisibleText: function(npc) {
                    return g_npc_types[npc.type];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_npc_types[a.type], g_npc_types[b.type]);
                }
            }
        ],

        getItemLink: function(npc) {
            return '?npc=' + npc.id;
        }
    },

    object: {
        sort: [1],
        nItemsPerPage: 100,
        searchable: 1,
        filtrable: 1,

        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                value: 'name',
                compute: function(object, td) {
                    var wrapper = $WH.ce('div');
                    var a = $WH.ce('a');

                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(object);
                    $WH.ae(a, $WH.ct(object.name));

                    if (object.hasQuests != null) {
                        a.className += " icontiny tinyspecial";
                        a.style.backgroundImage = "url(" + g_staticUrl + "/images/wow/icons/tiny/quest_start.gif)";
                    }

                    if (g_user.roles & U_GROUP_PREMIUM_PERMISSIONS)
                        a.rel = 'map';

                    $WH.ae(wrapper, a);

                    if (object.method != null) {
                        td.style.position = 'relative';

                        var d = $WH.ce('div');
                        d.className = 'small';
                        d.style.fontStyle = 'italic';
                        d.style.position = 'absolute';
                        d.style.right = '3px';
                        d.style.bottom = '3px';
                        d.style.textAlign = 'right';

                        $WH.ae(d, $WH.ct(object.method ? LANG.added : LANG.lvquest_removed));
                        $WH.ae(wrapper, d);
                    }

                    $WH.ae(td, wrapper);
                }
            },
            {
                id: 'location',
                name: LANG.location,
                type: 'text',
                compute: function(object, td) {
                    return Listview.funcBox.location(object, td);
                },
                getVisibleText: function(object) {
                    return Listview.funcBox.arrayText(object.location, g_zones);
                },
                sortFunc: function(a, b, col) {
                    return Listview.funcBox.assocArrCmp(a.location, b.location, g_zones);
                }
            },
            {
                id: 'skill',
                name: LANG.skill,
                width: '10%',
                value: 'skill',
                hidden: true
            },
            {
                id: 'type',
                name: LANG.type,
                type: 'text',
                width: '12%',
                compute: function(object, td) {
                    td.className = 'small q1';
                    var a = $WH.ce('a');
                    a.href = '?objects=' + object.type;
                    $WH.ae(a, $WH.ct(g_object_types[object.type]));
                    $WH.ae(td, a);
                },
                getVisibleText: function(object) {
                    return g_object_types[object.type];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_object_types[a.type], g_object_types[b.type]);
                }
            }
        ],

        getItemLink: function(object) {
            return '?object=' + object.id;
        }
    },

    quest: {
        sort: [1,2],
        nItemsPerPage: 100,
        searchable: 1,
        filtrable: 1,
        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                value: 'name',
                compute: function(quest, td) {
                    var wrapper = $WH.ce('div');
                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(quest);
                    $WH.ae(a, $WH.ct(quest.name));
                    $WH.ae(wrapper, a);

                    if (quest.reqclass) {
                        var d = $WH.ce('div');
                        d.className += ' small2';

                        var classes = Listview.funcBox.assocBinFlags(quest.reqclass, g_chr_classes);

                        for (var i = 0, len = classes.length; i < len; ++i) {
                            if (i > 0) {
                                $WH.ae(d, $WH.ct(LANG.comma));
                            }

                            var a = $WH.ce('a');
                            a.href = '?class=' + classes[i];
                            a.className += 'c' + classes[i];
                            $WH.st(a, g_chr_classes[classes[i]]);
                            $WH.ae(d, a);
                        }

                        $WH.ae(wrapper, d);
                    }

                    if (quest.wflags & 1 || (quest.wflags & 32) || (quest.reqrace && quest.reqrace != -1)) {
                        wrapper.style.position = 'relative';
                        var d = $WH.ce('div');
                        d.className = 'small';
                        d.style.fontStyle = 'italic';
                        d.style.position = 'absolute';
                        d.style.right = '3px';
                        d.style.bottom = '3px';
                        d.style.textAlign = 'right';
                        if (quest.wflags & 1) {
                            var s = $WH.ce('span');
                            s.style.color = 'red';
                            $WH.ae(s, $WH.ct(LANG.lvquest_disabled));
                            // $WH.ae(s, $WH.ct(LANG.lvquest_removed));
                            $WH.ae(d, s);
                        }

                        if (quest.wflags & 32) {
                            if (quest.wflags & 1) {
                                $WH.ae(d, $WH.ce('br'));
                                wrapper.style.height = '33px';
                            }

                            var
                                s = $WH.ce('span'),
                                o = LANG.lvquest_autoaccept;
                            if (quest.wflags & 64) {
                                s.style.color = 'red';
                                o += ' ' + LANG.lvquest_hostile;
                            }
                            $WH.ae(s, $WH.ct(o));
                            $WH.ae(d, s);
                        }

                        if (quest.reqrace && quest.reqrace != -1) {
                            var races = Listview.funcBox.assocBinFlags(quest.reqrace, g_chr_races);

                            if (races.length && (quest.wflags & 1 || (quest.wflags & 32))) {
                                $WH.ae(d, $WH.ce('br'));
                                wrapper.style.height = '33px';
                            }

                            for (var i = 0, len = races.length; i < len; ++i) {
                                if (i > 0) {
                                    $WH.ae(d, $WH.ct(LANG.comma));
                                }

                                var l = $WH.ce('a');
                                l.href = '?race=' + races[i];
                                l.className += 'q1';
                                $WH.st(l, g_chr_races[races[i]]);
                                $WH.ae(d, l);
                            }
                        }

                        $WH.ae(wrapper, d);
                    }

                    $WH.ae(td, wrapper);
                }
            },
            {
                id: 'level',
                name: LANG.level,
                value: 'level',
                compute: function(quest, td) {
                    if (quest.type || quest.daily || quest.weekly) {
                        var d = $WH.ce('div');
                        d.className = 'small';
                        $WH.nw(d);

                        if (quest.daily) {
                            if (quest.type) {
                                $WH.ae(d, $WH.ct($WH.sprintf(LANG.lvquest_daily, g_quest_types[quest.type])));
                            }
                            else {
                                $WH.ae(d, $WH.ct(LANG.daily));
                            }
                        }
                        else if (quest.weekly) {
                            if (quest.type) {
                                $WH.ae(d, $WH.ct($WH.sprintf(LANG.lvquest_weekly, g_quest_types[quest.type])));
                            }
                            else {
                                $WH.ae(d, $WH.ct(LANG.weekly));
                            }
                        }
                        else if (quest.type) {
                            $WH.ae(d, $WH.ct(g_quest_types[quest.type]));
                        }

                        $WH.ae(td, d);
                    }

                    return quest.level;
                },
                getVisibleText: function(quest) {
                    var buff = '';

                    if (quest.type) {
                        buff += ' ' + g_quest_types[quest.type];
                    }

                    if (quest.daily) {
                        buff += ' ' + LANG.daily;
                    }
                    else if (quest.weekly) {
                        buff += ' ' + LANG.weekly;
                    }

                    if (quest.level) {
                        buff += ' ' + quest.level;
                    }

                    return buff;
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(a.level, b.level) || $WH.strcmp(a.type, b.type);
                }
            },
            {
                id: 'reqlevel',
                name: LANG.req,
                tooltip: LANG.tooltip_reqlevel,
                value: 'reqlevel'
            },
            {
                id: 'side',
                name: LANG.side,
                type: 'text',
                compute: function(quest, td) {
                    if (quest.side && quest.side != 3) {
                        var sp = $WH.ce('span');
                        sp.className = (quest.side == 1 ? 'icon-alliance' : 'icon-horde');
                        g_addTooltip(sp, g_sides[quest.side]);

                        $WH.ae(td, sp);
                    }
                    else if (!quest.side) {
                        $WH.ae(td, $WH.ct('??'));
                    }
                },
                getVisibleText: function(quest) {
                    if (quest.side) {
                        return g_sides[quest.side];
                    }
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_sides[a.side], g_sides[b.side]);
                }
            },
            {
                id: 'rewards',
                name: LANG.rewards,
                compute: function(quest, td) {
                    var hasIcons = (quest.itemchoices != null || quest.itemrewards != null);
                    if (hasIcons) {
                        var
                            choiceText,
                            rewardText;
                        if (quest.itemchoices && quest.itemchoices.length > 1) {
                            choiceText = LANG.lvquest_pickone;
                            if (quest.itemrewards && quest.itemrewards.length > 0) {
                                rewardText = LANG.lvquest_alsoget;
                            }
                        }

                        Listview.funcBox.createCenteredIcons(quest.itemchoices, td, choiceText, 2);
                        Listview.funcBox.createCenteredIcons(quest.itemrewards, td, rewardText, 2);
                    }

                    if (quest.titlereward && g_titles[quest.titlereward]) {
                        var title = g_titles[quest.titlereward]['name_' + Locale.getName()];
                        title = title.replace('%s', '<span class="q0">&lt;' + LANG.name + '&gt;</span>');
                        var span = $WH.ce('a');
                        span.className = 'q1';
                        span.href = '?title=' + quest.titlereward;
                        span.innerHTML = title;
                        $WH.ae(td, span);
                        $WH.ae(td, $WH.ce('br'));
                    }
                },
                getVisibleText: function(quest) {
                    var buff = '';

                    if (quest.itemchoices && quest.itemchoices.length) {
                        buff += ' ' + LANG.lvquest_pickone;
                        if (quest.itemrewards && quest.itemrewards.length) {
                            buff += ' ' + LANG.lvquest_alsoget;
                        }
                    }

                    if (quest.titlereward && g_titles[quest.titlereward]) {
                        buff += ' ' + g_titles[quest.titlereward]['name_' + Locale.getName()];
                    }

                    return buff;
                },
                sortFunc: function(a, b, col) {
                    var lenA = (a.itemchoices != null ? a.itemchoices.length : 0) + (a.itemrewards != null ? a.itemrewards.length : 0);
                    var lenB = (b.itemchoices != null ? b.itemchoices.length : 0) + (b.itemrewards != null ? b.itemrewards.length : 0);
                    var titleA = (a.titlereward && g_titles[a.titlereward] ? g_titles[a.titlereward]['name_' + Locale.getName()] : '');
                    var titleB = (b.titlereward && g_titles[b.titlereward] ? g_titles[b.titlereward]['name_' + Locale.getName()] : '');
                    return $WH.strcmp(lenA, lenB) || $WH.strcmp(titleA, titleB);
                }
            },
            {
                id: 'experience',
                name: LANG.exp,
                value: 'xp'
            },
            {
                id: 'money',
                name: LANG.money,
                compute: function(quest, td) {
                    if (quest.money > 0 || quest.currencyrewards != null) {
                        if (quest.money > 0) {
                            Listview.funcBox.appendMoney(td, quest.money);
                            if (quest.currencyrewards != null) {
                                $WH.ae(td, $WH.ct(' + '));
                            }
                        }

                        if (quest.currencyrewards != null) {
                            Listview.funcBox.appendMoney(td, null, quest.side, null, quest.currencyrewards);
                        }
                    }
                },
                getVisibleText: function(quest) {
                    var buff = '';
                    for (var i = 0; quest.currencyrewards && i < quest.currencyrewards.length; ++i) {
                        if (g_gatheredcurrencies[quest.currencyrewards[i][0]]) {
                            buff += ' ' + g_gatheredcurrencies[quest.currencyrewards[i][0]]['name_' + Locale.getName()];
                        }
                    }
                    return buff;
                },
                sortFunc: function(a, b, col) {
                    var
                        lenA = 0,
                        lenB = 0;

                    if (a.currencyrewards && a.currencyrewards.length) {
                        for (i in a.currencyrewards) {
                            var c = (a.currencyrewards)[i];
                            lenA += c[1];
                        }
                    }
                    if (b.currencyrewards && b.currencyrewards.length) {
                        for (a in b.currencyrewards) {
                            var c = (b.currencyrewards)[i];
                            lenB += c[1];
                        }
                    }
                    return $WH.strcmp(lenA, lenB) || $WH.strcmp(a.money, b.money);
                }
            },
            {
                id: 'reputation',
                name: LANG.reputation,
                width: '14%',
                value: 'id',
                hidden: true
            },
            {
                id: 'category',
                name: LANG.category,
                type: 'text',
                compute: function(quest, td) {
                    if (quest.category != 0) {
                        td.className = 'small q1';
                        var a = $WH.ce('a');
                        a.href = '?quests=' + quest.category2 + '.' + quest.category;
                        $WH.ae(a, $WH.ct(Listview.funcBox.getQuestCategory(quest.category)));
                        $WH.ae(td, a);
                    }
                },
                getVisibleText: function(quest) {
                    return Listview.funcBox.getQuestCategory(quest.category);
                },
                sortFunc: function(a, b, col) {
                    var _ = Listview.funcBox.getQuestCategory;
                    return $WH.strcmp(_(a.category), _(b.category));
                }
            },
            {
                id: 'completed', // Listview.COLUMN_ID_COMPLETION
                name: LANG.completion, // WH.TERMS.completion
                hidden: true,
                compute: function (quest, td)
                {
                    if (quest.daily || quest.weekly)
                        return;

                    $WH.addCompletionIcons(td, 5, quest.id)
                },
                sortFunc: function (a, b)
                {
                    // return $WH.stringCompare(
                    return $WH.strcmp(
                        $WH.getCompletionFlags(5, a.id),
                        $WH.getCompletionFlags(5, b.id)
                    );
                },
                getValue: function (quest) {
                    var value = 0;
                    var completionData = g_user.completion?.hasOwnProperty(5) ? g_user.completion[5] : {};

                    if (quest.daily || quest.weekly)
                        return -1;

                    for (var i in g_user.characters)
                    {
                        var profile = g_user.characters[i];
                        if (!(profile.id in completionData))
                            continue;

                        if ($WH.in_array(completionData[profile.id], quest.id) != -1)
                            value++;
                    }

                    return value;
                }
            }
        ],

        getItemLink: function(quest) {
            return '?quest=' + quest.id;
        },

        onBeforeCreate: function () {
            for (var i in this.columns)
            {
                if (this.columns[i].id == 'completed' && this.columns[i].hidden)
                {
                    if ($WH.isset('g_user') && 'characters' in g_user && $WH.in_array(this.hiddenCols, this.columns[i].id) == -1)
                    {
                        var n = 0;
                        for (var j in this.data)
                            if (!this.data[j].daily && !this.data[j].weekly)
                                n++;

                        if (n > this.data.length * 0.1)
                            this.visibility.push(parseInt(i));
                    }
                }
            }
        }
    },

    icongallery: {
        sort: [1],
        mode: Listview.MODE_FLEXGRID,
        clickable: false,
        nItemsPerPage: 150,
        cellMinWidth: 85,
        poundable: 1,
        sortOptions: [
            {
                id: 'id',
                name: "ID",
                sortFunc: function(a, b) { return $WH.strcmp(a.id, b.id); }
            },
            {
                id: 'name',
                name: LANG.name,
                sortFunc: function(a, b) { return $WH.strcmp(a.name, b.name); }
            }
        ],
        columns: [],
        value: 'name',
        compute: function(_icon, td, tr) {
            var cell = $WH.ce('div');
            cell.className = 'icon-cell';
            $(cell).mouseenter(function() {
                setTimeout((function() { this.className += ' animate'; }).bind(this), 1);
            }).mouseleave(function() {
                this.className = this.className.replace(/ *animate\b/, '');
            });

            $WH.ae(cell, Icon.create(_icon.icon, 2, null, this.getItemLink(_icon)));

            var overlay = $WH.ce('div', { className: 'icon-cell-overlay' });
            $(overlay).mouseleave(function() {
                $('.fa-check', this).removeClass('fa-check').addClass('fa-clipboard');
            });
            $WH.ae(overlay, Icon.create(_icon.icon, 2, null, this.getItemLink(_icon)));

            var ovlName = $WH.ce('div', { className: 'icon-cell-overlay-name' });
            var o = function()
            {
                this.focus();
                this.select();
            };

            $WH.ae(ovlName, $WH.ce('input', {
                type: 'text',
                value: _icon.name,
                onclick: o,
                onfocus: o
            }));


            /* Aowow - we do not use FontAwesome
            $WH.ae(ovlName, $WH.g_createButton(null, null, {
                'class': 'fa fa-fw fa-clipboard',
                'float': false,
                'no-margin': true,
                click: function() {
                    var v = $(this);
                    var y = v.siblings('input[type="text"]');
                    y.focus().select();
                    var w = false;
                    try
                    {
                        if (!document.execCommand('copy'))
                            w = true;
                    }
                    catch (u) { w = true; }

                    y.blur();

                    if (w)
                    {
                        v.css({ 'pointer-events': 'none' })
                        .removeClass('fa-clipboard')
                        .addClass('fa-exclamation-triangle')
                        .blur()
                        .effect('shake', { distance: 5 });

                        setTimeout(function() { $('.icon-cell-overlay-name .btn').fadeOut(1000) }, 600);
                    }
                    else
                        v.removeClass('fa-clipboard').addClass('fa-check');
                }
            }));
            */

            $WH.ae(ovlName, $WH.ce('input', {
                type:      'button',
                className: 'button-copy',
                onclick:   function() {
                    var btn = $(this);
                    var iName = btn.siblings('input[type="text"]');
                    iName.focus().select();
                    var error = false;
                    try
                    {
                        if (!document.execCommand('copy'))
                            error = true;
                    }
                    catch (x) { error = true; }

                    iName.blur();

                    if (error)
                    {
                        btn.css({
                            'pointer-events': 'none',
                            'background-image': 'url(' + g_staticUrl + '/images/icons/report.png)'
                        }).blur();

                        setTimeout(function() { $('.icon-cell-overlay-name .button-copy').fadeOut(1000) }, 600);
                    }
                    else
                        btn.css('background-image', 'url(' + g_staticUrl + '/images/icons/tick.png)');
                }
            }));
            /* end replacement */

            $WH.ae(overlay, ovlName);
            var t = $WH.ce('div', { className: 'icon-cell-overlay-counts' });
            var types = [3, 6, 10, 17, 9, 13];
            var c = 0;
            for (var h = 0, m; m = types[h]; h++) {
                var p = g_types[m] + 'count';
                if (_icon[p]) {
                    c += _icon[p];
                    var g = g_types[m];
                    var s = _icon[p] == 1 ? LANG.types[m][1] : LANG.types[m][3];

                    $WH.ae(t, $WH.ce('div', null, $WH.ce('a', {
                        href: this.getItemLink(_icon) + '#used-by-' + g,
                        innerHTML: $WH.sprintf(LANG.entitycount, _icon[p], s)
                    })))
                }
            }

            if (!c)
                $WH.ae(t, $WH.ce('div', { innerHTML: LANG.unused }));

            $WH.ae(overlay, t);
            var k = $WH.ce('div', { className: 'icon-cell-overlay-placer' }, overlay);
            $WH.ae(cell, k);
            $WH.ae(td, cell);
        },
        sortFunc: function(a, b) {
            return $WH.strcmp(a.name, b.name);
        },
        getItemLink: function(icon) {
            return "?icon=" + icon.id;
        }
    },

    topusers: {
        sort: ['reputation'],
        searchable: 1,
        filtrable: 0,

        columns: [
            {
                id: 'username',
                name: LANG.username,
                type: 'text',
                align: 'left',
                compute: function(user, td) {
                    var a = $('<a>');
                    var color = g_GetStaffColorFromRoles(user.groups);
                    if (color != '')
                        a.addClass(color);
                    else
                        a.css('color', 'white');

                    a.text(user.username);
                    a.addClass('listview-cleartext');
                    a.attr('href', '?user=' + user.username);
                    $(td).append(a);
                    return;
                },
                getVisibleText: function(user) {
                    return user.username;
                },
                sortFunc: function(a, b) {
                    return $WH.strcmp(a.username, b.username);
                },
                getItemLink: function(user) {
                    return '?user=' + user.username;
                }
            },
            {
                id: 'reputation',
                name: LANG.reputation,
                type: 'text',
                compute: function(user, td) {
                    $(td).append($WH.number_format(user.reputation));
                    return;
                },
                sortFunc: function(a, b) {
                    if (b.reputation == a.reputation)
                        return 0;

                    return a.reputation < b.reputation ? 1 : -1;
                }
            },
            {
                id: 'achievements',
                name: LANG.achievements,
                type: 'text',
                compute: function(user, td) {
                    var sp = $('<span>').addClass('wsach-pts').css('font-size', 'inherit');
                    var buf = '';
                    if (user.gold)
                        buf += '<i>' + user.gold + '</i>&middot;';
                    if (user.silver)
                        buf += '<b>' + user.silver + '</b>&middot;';
                    buf += '<u>' + user.copper + '</u>';

                    sp.html(buf);
                    $(td).append(sp);

                    return;
                },
                sortFunc: function(a, b) {
                    var sumA = (a.gold * 1000 * 1000) + (a.silver * 1000) + a.copper;
                    var sumB = (b.gold * 1000 * 1000) + (b.silver * 1000) + b.copper;
                    if (sumA == sumB)
                        return 0;
                    return sumA < sumB ? 1 : -1;
                }
            },
            {
                id: 'comments',
                name: LANG.comments,
                type: 'text',
                compute: function(user, td) {
                    $(td).append($WH.number_format(user.comments));
                    return;
                },
                sortFunc: function(a, b) {
                    if (a.comments == b.comments)
                        return 0;
                    return a.comments < b.comments ? 1 : -1;
                }
            },
            {
                id: 'posts',
                name: LANG.posts,
                type: 'text',
                compute: function(user, td) {
                    $(td).append($WH.number_format(user.posts));
                    return;
                },
                sortFunc: function(a, b) {
                    if (a.posts == b.posts)
                        return 0;
                    return a.posts < b.posts ? 1 : -1;
                }
            },
            {
                id: 'screenshots',
                name: LANG.screenshots,
                type: 'text',
                compute: function(user, td) {
                    $(td).append($WH.number_format(user.screenshots));
                    return;
                },
                sortFunc: function(a, b) {
                    if (a.screenshots == b.screenshots)
                        return 0;
                    return a.screenshots < b.screenshots ? 1 : -1;
                }
            },
            {
                id: 'reports',
                name: LANG.reports,
                type: 'text',
                compute: function(user, td) {
                    $(td).append($WH.number_format(user.reports));
                    return;
                },
                sortFunc: function(a, b) {
                    if (a.reports == b.reports)
                        return 0;
                    return a.reports < b.reports ? 1 : -1;
                }
            },
            {
                id: 'votes',
                name: LANG.votes,
                type: 'text',
                compute: function(user, td) {
                    $(td).append($WH.number_format(user.votes));
                    return;
                },
                sortFunc: function(a, b) {
                    if (a.votes == b.votes)
                        return 0;
                    return a.votes < b.votes ? 1 : -1;
                }
            },
            {
                id: 'uploads',
                name: LANG.uploads,
                type: 'text',
                compute: function(user, c) {
                    $(c).append($WH.number_format(user.uploads));
                    return;
                },
                sortFunc: function(a, c) {
                    if (a.uploads == c.uploads)
                        return 0;
                    return a.uploads < c.uploads ? 1 : -1;
                }
            },
            {
                id: 'created',
                name: LANG.created,
                type: 'text',
                hidden: 1,
                compute: function(user, td) {
                    var date = new Date(user.creation),
                        diff = (g_serverTime - date) / 1000;

                    sp = $WH.ce('span');
                    g_formatDate(sp, diff, date);
                    $WH.ae(td, sp);
                },
                sortFunc: function(a, b) {
                    if (a.creation == b.creation)
                        return 0;
                    return a.creation < b.creation ? 1 : -1;
                }
            }
        ],

        getItemLink: function(user) {
            return '?user=' + user.username;
        }
    },

    reputationhistory: {
        sort: ['when'],
        searchable: 0,
        filterable: 0,

        columns: [
            {
                id: 'action',
                name: LANG.reputationaction,
                type: 'text',
                align: 'left',
                value: 'title',
                compute: function(rep, td) {
                    if (rep.param && rep.param > 0) {
                        var link = $('<a>').attr('href', '?go-to-comment&id=' + rep.param).text(l_reputation_names[rep.action]);

                        if (rep.action == 11 || rep.action == 12 || rep.action == 13) { // Achievements
                            link.attr('href', '?website-achievement=' + rep.param + '#user:' + g_user.name);
                        }

                        $(td).append(link);
                    }
                    else {
                        $(td).append(l_reputation_names[rep.action]);
                    }
                    return;
                },
                getVisibleText: function(rep) {
                    return l_reputation_names[rep.action];
                }
            },
            {
                id: 'amount',
                name: LANG.amount,
                type: 'text',
                width: '250px',
                compute: function(rep, td) {
                    var span = $('<span>').text(rep.amount);

                    if (rep.amount > 0) {
                        span.addClass('q2').prepend('+');
                    }
                    else if (rep.amount < 0) {
                        span.addClass('q10');
                    }

                    $(td).append(span);
                    return;
                },
                getVisibleText: function(rep) {
                    return rep.amount;
                },
                sortFunc: function(a, b) {
                    if (a.when == b.when) {
                        return 0;
                    }

                    return a.when < b.when ? 1 : -1;
                }
            },
            {
                id: 'when',
                name: LANG.date,
                type: 'text',
                width: '250px',
                compute: function(rep, td) {
                    var
                        postedOn = new Date(rep.when),
                        elapsed  = (g_serverTime - postedOn) / 1000;

                    a = $WH.ce('span');
                    g_formatDate(a, elapsed, postedOn);
                    $WH.ae(td, a);
                },
                sortFunc: function(a, b) {
                    if (a.when == b.when) {
                        return 0;
                    }

                    return a.when < b.when ? 1 : -1;
                }
            }
        ]
    },

    skill: {
        sort: [1],
        searchable: 1,
        filtrable: 1,

        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                value: 'name',
                span: 2,
                compute: function(skill, td, tr) {
                    var i = $WH.ce('td');
                    i.style.width = '1px';
                    i.style.padding = '0';
                    i.style.borderRight = 'none';

                    $WH.ae(i, Icon.create(skill.icon, 0, null, this.getItemLink(skill)));
                    $WH.ae(tr, i);
                    td.style.borderLeft = 'none';

                    var wrapper = $WH.ce('div');

                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(skill);
                    $WH.ae(a, $WH.ct(skill.name));

                    if (skill.expansion) {
                        var sp = $WH.ce('span');
                        sp.className = g_GetExpansionClassName(skill.expansion);
                        $WH.ae(sp, a);
                        $WH.ae(wrapper, sp);
                    }
                    else {
                        $WH.ae(wrapper, a);
                    }

                    $WH.ae(td, wrapper);
                },
                getVisibleText: function(skill) {
                    var buff = skill.name + Listview.funcBox.getExpansionText(skill);

                    return buff;
                }
            },
            {
                id: 'category',
                name: LANG.category,
                type: 'text',
                width: '16%',
                compute: function(skill, td) {
                    if (skill.category != 0) {
                        td.className = 'small q1';
                        var a = $WH.ce('a');
                        a.href = '?skills=' + skill.category;
                        $WH.ae(a, $WH.ct(g_skill_categories[skill.category]));
                        $WH.ae(td, a);
                    }
                },
                getVisibleText: function(skill) {
                    return g_skill_categories[skill.category];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_skill_categories[a.category], g_skill_categories[b.category]);
                }
            }
        ],

        getItemLink: function(skill) {
            return '?skill=' + skill.id;
        }
    },

    spell: {
        sort: ['name', 'skill', 'level'],
        searchable: 1,
        filtrable: 1,

        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                span: 2,
                value: 'name',
                compute: function(spell, td, tr) {
                    var
                        i = $WH.ce('td'),
                        _;

                    i.style.width = '44px';
                    i.style.padding = '0';
                    i.style.borderRight = 'none';

                    if (spell.creates != null) {
                        _ = g_items.createIcon(spell.creates[0], 1, Listview.funcBox.createTextRange(spell.creates[1], spell.creates[2]));
                    }
                    else {
                        _ = g_spells.createIcon(spell.id, 1);
                    }

                    _.style.cssFloat = _.style.styleFloat = 'left';
                    $WH.ae(i, _);

                    $WH.ae(tr, i);
                    td.style.borderLeft = 'none';

                    var wrapper = $WH.ce('div');

                    var a = $WH.ce('a');
                    var c = spell.name.charAt(0);
                    if (c != '@') {
                        a.className = 'q' + (7 - parseInt(c));
                    }
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(spell);

                    $WH.ae(a, $WH.ct(spell.name.substring(1)));

                    $WH.ae(wrapper, a);

                    if (spell.rank) {
                        var d = $WH.ce('div');
                        d.className = 'small2';

                        $WH.ae(d, $WH.ct(spell.rank));
                        $WH.ae(wrapper, d);
                    }

                    if (this.showRecipeClass && spell.reqclass) {
                        var d = $WH.ce('div');
                        d.className = 'small2';

                        var classes = Listview.funcBox.assocBinFlags(spell.reqclass, g_chr_classes);

                        for (var i = 0, len = classes.length; i < len; ++i) {
                            if (i > 0) {
                                $WH.ae(d, $WH.ct(', '));
                            }
                            var a = $WH.ce('a');
                            a.href = '?class=' + classes[i];
                            a.className = 'c' + classes[i];
                            $WH.st(a, g_chr_classes[classes[i]]);
                            $WH.ae(d, a);
                        }

                        $WH.ae(wrapper, d);
                    }

                    if (spell.reqrace) {
                        wrapper.style.position = 'relative';
                        var d = $WH.ce('div');
                        d.className = 'small';
                        d.style.fontStyle = 'italic';
                        d.style.position = 'absolute';
                        d.style.right = d.style.bottom = '3px';
                        if ((spell.reqrace & 1791) == 1101) {  // Alliance
                            $WH.ae(d, $WH.ct(g_sides[1]));
                        }
                        else if ((spell.reqrace & 1791) == 690) { // Horde
                            $WH.ae(d, $WH.ct(g_sides[2]));
                        }
                        else {
                            var races = Listview.funcBox.assocBinFlags(spell.reqrace, g_chr_races);
                            d.className += ' q1';

                            for (var i = 0, len = races.length; i < len; ++i) {
                                if (i > 0) {
                                    $WH.ae(d, $WH.ct(LANG.comma)) ;
                                }
                                var a = $WH.ce('a');
                                a.href = '?race=' + races[i];
                                $WH.st(a, g_chr_races[races[i]]);
                                $WH.ae(d, a);
                            }
                        }
                        $WH.ae(wrapper, d);
                    }
                    else if (spell.spellclick) {
                        td.style.position = 'relative';

                        [flags, who] = spell.spellclick;

                        let buff = 'onClick';
                        if (who == 1)                       // Friendly
                            buff += $WH.sprintf(LANG.qty, g_reputation_standings[4]);
                        else if (who == 2)                  // Raid
                            buff += $WH.sprintf(LANG.qty, g_quest_types[62]);
                        else if (who == 3)                  // Party
                            buff += $WH.sprintf(LANG.qty, g_quest_types[1]);

                        buff += LANG.colon + '<span class="breadcrumb-arrow">' + (flags & 0x1 ? g_world_object_types[4] : g_pageInfo.name) + '</span>';
                        buff +=                                                   flags & 0x2 ? g_world_object_types[4] : g_pageInfo.name;

                        $(td).append($('<div>', {class: 'small'})
                            .css('fontStyle', 'italic')
                            .css('position',  'absolute')
                            .css('right',     '3px')
                            .css('bottom',    '3px')
                            .html(buff)
                        );
                    }
                    $WH.ae(td, wrapper);
                },
                getVisibleText: function(spell) {
                    var buff = spell.name;

                    if (spell.rank) {
                        buff += ' ' + spell.rank;
                    }

                    if (spell.reqclass) {
                        var classes = Listview.funcBox.assocBinFlags(spell.reqclass, g_chr_classes);

                        for (var i = 0, len = classes.length; i < len; ++i) {
                            if (i > 0) {
                                buff += LANG.comma;
                            }
                            buff += g_chr_classes[classes[i]];
                        }
                    }

                    if (spell.reqrace) {
                        buff += ' ' + Listview.funcBox.arrayText(Listview.funcBox.assocBinFlags(spell.reqrace, g_chr_races), g_chr_races);
                    }

                    return buff;
                }
            },
            {
                id: 'tier',
                name: LANG.tier,
                width: '10%',
                value: 'level',
                compute: function(spell, td) {
                    if (spell.level > 0) {
                        var
                            baseLevel  = (!this._petTalents ? 10 : 20),
                            tierPoints = (!this._petTalents ?  5 : 12);

                        return Math.floor((spell.level - baseLevel) / tierPoints) + 1;  // + 3 ?
                    }
                    else {
                        return 1;
                    }
                },
                hidden: true
            },
            {
                id: 'level',
                name: LANG.level,
                width: '10%',
                value: 'level',
                compute: function(spell, td) {
                    if (spell.level > 0) {
                        return spell.level;
                    }
                },
                hidden: true
            },
            {
                id: 'trainingcost',
                name: LANG.cost,
                width: '10%',
                hidden: true,
                getValue: function(row) {
                    if (row.trainingcost) {
                        return row.trainingcost;
                    }
                },
                compute: function(row, td) {
                    if (row.trainingcost) {
                        Listview.funcBox.appendMoney(td, row.trainingcost);
                    }
                },
                sortFunc: function(a, b, col) {
                    if (a.trainingcost == null) {
                        return -1;
                    }
                    else if (b.trainingcost == null) {
                        return 1;
                    }

                    if (a.trainingcost < b.trainingcost) {
                        return -1;
                    }
                    else if (a.trainingcost > b.trainingcost) {
                        return 1;
                    }
                    return 0;
                }
            },
            {
                id: 'classes',
                name: LANG.classes,
                type: 'text',
                hidden: true,
                width: '20%',
                getVisibleText: function(spell) {
                    var str = '';
                    if (spell.reqclass) {
                        var classes = Listview.funcBox.assocBinFlags(spell.reqclass, g_chr_classes);

                        for (var i = 0, len = classes.length; i < len; ++i) {
                            if (i > 0) {
                                str += LANG.comma;
                            }
                            str += g_chr_classes[classes[i]];
                        }
                    }
                    return str;
                },
                compute: function(spell, td) {
                    if (spell.reqclass) {
                        var classes = Listview.funcBox.assocBinFlags(spell.reqclass, g_chr_classes);

                        var d = $WH.ce('div');
                        d.style.width = (26 * classes.length) + 'px';
                        d.style.margin = '0 auto';

                        for (var i = 0, len = classes.length; i < len; ++i) {
                            var icon = Icon.create('class_' + g_file_classes[classes[i]], 0, null, '?class=' + classes[i]);
                            icon.style.cssFloat = icon.style.styleFloat = 'left';

                            g_addTooltip(icon, g_chr_classes[classes[i]], 'c' + classes[i]);

                            $WH.ae(d, icon);
                        }

                        $WH.ae(td, d);
                    }
                },
                sortFunc: function(a, b, col) {
                    return Listview.funcBox.assocArrCmp(Listview.funcBox.assocBinFlags(a.reqclass, g_chr_classes), Listview.funcBox.assocBinFlags(b.reqclass, g_chr_classes), g_chr_classes);
                }
            },
            {
                /* NOTE: To be used only if one single class is to be displayed */
                id: 'singleclass',
                name: LANG.classs,
                type: 'text',
                hidden: true,
                width: '15%',
                compute: function(spell, td) {
                    if (spell.reqclass) {
                        var classes = Listview.funcBox.assocBinFlags(spell.reqclass, g_chr_classes);
                        var classId = classes[0];

                        var text = $WH.ce('a');
                        text.style.backgroundImage = 'url("' + g_staticUrl + '/images/wow/icons/tiny/class_' + g_file_classes[classId] + '.gif")';
                        text.className = 'icontiny tinyspecial c' + classId;
                        text.href = '?class=' + classId;
                        $WH.ae(text, $WH.ct(g_chr_classes[classId]));

                        $WH.ae(td, text);
                    }
                },
                sortFunc: function(a, b, col) {
                    return Listview.funcBox.assocArrCmp(Listview.funcBox.assocBinFlags(a.reqclass, g_chr_classes), Listview.funcBox.assocBinFlags(b.reqclass, g_chr_classes), g_chr_classes);
                }
            },
            {
                id: 'glyphtype',
                name: LANG.glyphtype,
                type: 'text',
                hidden: true,
                width: '10%',
                compute: function(spell, td) {
                    if (spell.glyphtype) {
                        return g_item_glyphs[spell.glyphtype];
                    }
                }
            },
            {
                id: 'schools',
                name: LANG.school,
                type: 'text',
                width: '10%',
                hidden: true,
                compute: function(spell, td) {
                    var text = '';
                    var schools = spell.schools ? spell.schools : spell.school;

                    for (var i = 0; i < 32; ++i) {
                        if (!(schools & (1 << i))) {
                            continue;
                        }

                        if (text != '') {
                            text += ', ';
                        }

                        text += g_spell_resistances[i];
                    }

                    return text;
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(this.compute(a), this.compute(b));
                }
            },
            {
                id: 'type',
                name: LANG.type,
                type: 'text',
                width: '14%',
                hidden: true,
                compute: function (spell, td) {
                    var d = $WH.ce('div');
                    d.className = 'small';
                    if ($WH.in_array([7, -2, -7, -12, -11, -13], spell.cat) != -1) {
                        var a1 = $WH.ce('a');

                        a1.className = 'q0';
                        a1.href = '?spells=' + spell.cat;

                        $WH.ae(a1, $WH.ct(g_spell_categories[spell.cat]));
                        $WH.ae(d, a1);
                        $WH.ae(d, $WH.ce('br'));

                        if (spell.talentspec) {
                            for (var i = 0, len = spell.talentspec.length; i< len; ++i) {
                                if (i > 0) {
                                    $WH.ae(d, $WH.ct(LANG.comma));
                                }

                                var a2 = $WH.ce('a');
                                a2.className = 'q1';
                                a2.href = '?spells=' + spell.cat + '.' + (1 + Math.log(spell.reqclass) / Math.LN2) + '.' + spell.talentspec[i];
                                $WH.ae(a2, $WH.ct(g_chr_specs[spell.talentspec[i]]));
                                $WH.ae(d, a2);
                            }
                        }
                        else if (g_spell_types[spell.cat]) {
                            if (spell.cat == -11 && (spell.type == 1 || spell.type == 2)) {
                                var a2 = $WH.ce('a');
                                a2.className = 'q1';
                                // a2.href = '?spells=' + spell.cat + '&filter=cr=22;crs=' + spell.type + ';crv=0';
                                a2.href = '?spells=' + spell.cat + '.' + (spell.type == 2 ? '8' : '6');
                                $WH.ae(a2, $WH.ct(g_spell_types[spell.cat][spell.type]));
                                $WH.ae(d, a2);
                            }
                            else {
                                $WH.ae(d, $WH.ct(g_spell_types[spell.cat][spell.type]));
                            }
                        }
                        else if (spell.reqclass) {
                            var a2 = $WH.ce('a');
                            a2.className = 'q1';
                            a2.href = '?spells=' + spell.cat + '.' + (1 + Math.log(spell.reqclass) / Math.LN2) + (g_item_glyphs[spell.glyphtype] ? '&filter=gl=' + spell.glyphtype : '');
                            $WH.ae(a2, $WH.ct((g_item_glyphs[spell.glyphtype] ? g_item_glyphs[spell.glyphtype] + ' ' : '') + g_chr_classes[(1 + Math.log(spell.reqclass) / Math.LN2)]));
                            $WH.ae(d, a2);
                        }
                    }
                    else if (g_spell_categories[spell.cat]) {
                        var a1 = $WH.ce('a');
                        a1.className = 'q1';
                        a1.href = '?spells=' + spell.cat;
                        $WH.ae(a1, $WH.ct(g_spell_categories[spell.cat]));
                        $WH.ae(d, a1)
                    }
                    $WH.ae(td, d);
                },
                getVisibleText: function (spell) {
                    var buff = g_spell_categories[spell.cat];

                    if ($WH.in_array([7, -2, -12, -11, -13], spell.cat) != -1) {
                        if (spell.talentspec) {
                            buff += ' ' + Listview.funcBox.arrayText(spell.talentspec, g_chr_specs);
                        }
                        else if (g_spell_types[spell.cat]) {
                            buff += ' ' + g_spell_types[spell.cat][spell.type];
                        }
                        else {
                            if (g_item_glyphs[spell.glyphtype]) {
                                buff += ' ' + g_item_glyphs[spell.glyphtype];
                            }

                            buff += ' ' + g_chr_classes[(1 + Math.log(spell.reqclass) / Math.LN2)];
                        }
                    }
                    return buff;
                },
                sortFunc: function(a, b, col) {
                    var
                        typeA = (g_spell_types[a.cat] ? g_spell_types[a.cat][a.type] : a.type),
                        typeB = (g_spell_types[b.cat] ? g_spell_types[b.cat][b.type] : a.type);

                    return $WH.strcmp(a.cat, b.cat) || $WH.strcmp(typeA, typeB);
                }
            },
            {
                id: 'reagents',
                name: LANG.reagents,
                width: '9%',
                hidden: true,
                getValue: function(spell) {
                    return (spell.reagents ? spell.reagents.length : 0);
                },
                compute: function(spell, td) {
                    var hasIcons = (spell.reagents != null);
                    if (hasIcons) {
                        td.style.padding = '0';

                        var d = $WH.ce('div');
                        var items = spell.reagents;

                        d.style.width = (44 * items.length) + 'px';
                        d.style.margin = '0 auto';

                        for (var i = 0, len = items.length; i < len; ++i) {
                            var id   = items[i][0];
                            var num  = items[i][1];
                            var icon = g_items.createIcon(id, 1, num);
                            icon.style.cssFloat = icon.style.styleFloat = 'left';
                            $WH.ae(d, icon);
                        }

                        $WH.ae(td, d);
                    }
                },
                sortFunc: function(a, b) {
                    var lena = (a.reagents != null ? a.reagents.length : 0);
                    var lenb = (b.reagents != null ? b.reagents.length : 0);
                    if (lena > 0 && lena == lenb) {
                        return $WH.strcmp(a.reagents.toString(), b.reagents.toString());
                    }
                    else {
                        return $WH.strcmp(lena, lenb);
                    }
                }
            },
            {
                id: 'tp',                                   // essentially unused, but if someone wants to backport classic
                name: LANG.tp,
                tooltip: LANG.tooltip_trainingpoints,
                width: '7%',
                hidden: true,
                value: 'tp',
                compute: function (spell, col) {
                    if (spell.tp > 0) {
                        return spell.tp;
                    }
                }
            },
            {
                id: 'source',
                name: LANG.source,
                type: 'text',
                width: '12%',
                hidden: true,
                compute: function(spell, td) {
                    if (spell.source != null) {
                        if (spell.source.length == 1) {
                            $WH.nw(td);

                            var sm = (spell.sourcemore ? spell.sourcemore[0] : {});
                            var type = 0;

                            if (sm.t) {
                                type = sm.t;

                                var a = $WH.ce('a');
                                if (sm.q != null) {
                                    a.className = 'q' + sm.q;
                                }
                                else {
                                    a.className = 'q1';
                                }
                                a.href = '?' + g_types[sm.t] + '=' + sm.ti;
                                a.style.whiteSpace = 'nowrap';

                                if (sm.icon) {
                                    a.className += ' icontiny tinyspecial';
                                    a.style.backgroundImage = 'url("' + g_staticUrl + '/images/wow/icons/tiny/' + sm.icon.toLowerCase() + '.gif")';
                                }

                                $WH.ae(a, $WH.ct(sm.n));
                                $WH.ae(td, a);
                            }
                            else {
                                $WH.ae(td, $WH.ct(Listview.funcBox.getUpperSource(spell.source[0], sm)));
                            }

                            var ls = Listview.funcBox.getLowerSource(spell.source[0], sm, type);

                            if (this.iconSize != 0 && ls != null) {
                                var div = $WH.ce('div');
                                div.className = 'small2';

                                if (ls.pretext) {
                                    $WH.ae(div, $WH.ct(ls.pretext));
                                }

                                if (ls.url) {
                                    var a = $WH.ce('a');
                                    a.className = 'q1';
                                    a.href = ls.url;
                                    $WH.ae(a, $WH.ct(ls.text));
                                    $WH.ae(div, a);
                                }
                                else {
                                    $WH.ae(div, $WH.ct(ls.text));
                                }

                                if (ls.posttext) {
                                    $WH.ae(div, $WH.ct(ls.posttext));
                                }

                                $WH.ae(td, div);
                            }
                        }
                        else {
                            var buff = '';
                            for (var i = 0, len = spell.source.length; i < len; ++i) {
                                if (i > 0) {
                                    buff += LANG.comma;
                                }
                                buff += g_sources[spell.source[i]];
                            }
                        }

                        return buff;
                    }
                },
                getVisibleText: function(spell) {
                    if (spell.source != null) {
                        if (spell.source.length == 1) {
                            var buff = '';

                            var sm = (spell.sourcemore ? spell.sourcemore[0] : {});
                            var type = 0;

                            if (sm.t) {
                                type = sm.t;
                                buff += ' ' + sm.n;
                            }
                            else {
                                buff += ' ' + Listview.funcBox.getUpperSource(spell.source[0], sm);
                            }

                            var ls = Listview.funcBox.getLowerSource(spell.source[0], sm, type);

                            if (ls != null) {
                                if (ls.pretext) {
                                    buff += ' ' + ls.pretext;
                                }

                                buff += ' ' + ls.text;

                                if (ls.posttext) {
                                    buff += ' ' + ls.posttext;
                                }
                            }

                            return buff;
                        }
                        else {
                            return Listview.funcBox.arrayText(spell.source, g_sources);
                        }
                    }
                },
                sortFunc: function(a, b, col) {
                    return Listview.funcBox.assocArrCmp(a.source, b.source, g_sources);
                }
            },
            {
                id: 'skill',
                name: LANG.skill,
                type: 'text',
                width: '16%',
                getValue: function(spell) {
                    return spell.learnedat;
                },
                compute: function(spell, td, tr, colNo) {
                    if (spell.skill != null) {
                        this.skillsColumn = colNo;
                        var div = $WH.ce('div');
                        div.className = 'small';

                        if (spell.cat == -7 && spell.pettype != null) {
                            spell.skill = [];
                            var petTalents = {
                                0: 410,
                                1: 409,
                                2: 411
                            };
                            for (var i = 0, len = spell.pettype.length; i < len; ++i) {
                                spell.skill.push(petTalents[spell.pettype[i]]);
                            }
                        }

                        for (var i = 0, len = spell.skill.length; i < len; ++i) {
                            if (i > 0) {
                                $WH.ae(div, $WH.ct(LANG.comma));
                            }

                            if (spell.skill[i] == -1) {
                                $WH.ae(div, $WH.ct(LANG.ellipsis));
                            }
                            else {
                                if ($WH.in_array([7, -2, -3, -5, -6, -7, 11, 9], spell.cat) != -1) {
                                    var a = $WH.ce('a');
                                    a.className = 'q1';
                                    if ($WH.in_array([-5, -6], spell.cat) != -1) {
                                        a.href = '?spells=' + spell.cat;
                                    }
                                    else {
                                        a.href = '?spells=' + spell.cat + '.' + ((spell.reqclass && (spell.cat == 7 || spell.cat == -2)) ? (1 + Math.log(spell.reqclass) / Math.LN2) + '.' : '') + spell.skill[i];
                                    }

                                    var get = $WH.g_getGets();
                                    var spellCat = (get.spells ? get.spells.split('.') : [false, false]);
                                    if (spell.reqclass && (spell.cat == 7 || spell.cat == -2)) {
                                        if (i < 1 && ((1 + Math.log(spell.reqclass) / Math.LN2) != spellCat[1])) {
                                            var a2 = $WH.ce('a');
                                            a2.className = 'q0';
                                            a2.href = '?spells=' + spell.cat + '.' + (1 + Math.log(spell.reqclass) / Math.LN2);
                                            $WH.ae(a2, $WH.ct(g_chr_classes[(1 + Math.log(spell.reqclass) / Math.LN2)]));
                                            $WH.ae(div, a2);
                                            $WH.ae(div, $WH.ce('br'));
                                        }
                                    }
                                    $WH.ae(a, $WH.ct(spell.cat == -7 && spell.pettype != null ? g_pet_types[spell.pettype[i]] : g_spell_skills[spell.skill[i]]));
                                    $WH.ae(div, a);
                                }
                                else {
                                    $WH.ae(div, $WH.ct(g_spell_skills[spell.skill[i]]));
                                }
                            }
                        }

                        if (spell.learnedat > 0) {
                            $WH.ae(div, $WH.ct(' ('));
                            var spLearnedAt = $WH.ce('span');
                            if (spell.learnedat == 9999) {
                                spLearnedAt.className = 'q0';
                                $WH.ae(spLearnedAt, $WH.ct('??'));
                            }
                            else if (spell.learnedat > 0) {
                                $WH.ae(spLearnedAt, $WH.ct(spell.learnedat));
                                spLearnedAt.style.fontWeight = 'bold';
                            }
                            $WH.ae(div, spLearnedAt);
                            $WH.ae(div, $WH.ct(')'));
                        }

                        $WH.ae(td, div);

                        if (spell.colors != null) {
                            this.template.columns[colNo].type = null;

                            var
                                colors = spell.colors,
                                nColors = 0;

                            for (var i = 0; i < colors.length; ++i) {
                                if (colors[i] > 0) {
                                    ++nColors;
                                    break;
                                }
                            }
                            if (nColors > 0) {
                                nColors = 0;
                                div = $WH.ce('div');
                                div.className = 'small';
                                div.style.fontWeight = 'bold';
                                for (var i = 0; i < colors.length; ++i) {
                                    if (colors[i] > 0) {
                                        if (nColors++ > 0) {
                                            $WH.ae(div, $WH.ct(' '));
                                        }
                                        var spColor = $WH.ce('span');
                                        spColor.className = 'r' + (i + 1);
                                        $WH.ae(spColor, $WH.ct(colors[i]));
                                        $WH.ae(div, spColor);
                                    }
                                }
                                $WH.ae(td, div);
                            }
                        }
                    }
                },
                getVisibleText: function(spell) {
                    var buff = Listview.funcBox.arrayText(spell.skill, g_spell_skills);

                    if (spell.learnedat > 0) {
                        buff += ' ' + (spell.learnedat == 9999 ? '??' : spell.learnedat);
                    }

                    return buff;
                },
                sortFunc: function(a, b) {
                /*  aowow: behaves unpredictable if reqclass not set or 0
                    if (a.reqclass && b.reqclass) {
                        var reqClass = $WH.strcmp(g_chr_classes[(1 + Math.log(a.reqclass) / Math.LN2)], g_chr_classes[(1 + Math.log(b.reqclass) / Math.LN2)]);
                        if (reqClass) {
                            return reqClass;
                        }
                    }
                */

                    var skill = [a.learnedat, b.learnedat];
                    for (var s = 0; s < 2; ++s) {
                        var sp = (s == 0 ? a : b);
                        if (skill[s] == 9999 && sp.colors != null) {
                            var i = 0;
                            while (sp.colors[i] == 0 && i < sp.colors.length) {
                                i++;
                            }
                            if (i < sp.colors.length) {
                                skill[s] = sp.colors[i];
                            }
                        }
                    }
                    var foo = $WH.strcmp(skill[0], skill[1]);
                    if (foo != 0) {
                        return foo;
                    }

                    if (a.colors != null && b.colors != null) {
                        for (var i = 0; i < 4; ++i) {
                            foo = $WH.strcmp(a.colors[i], b.colors[i]);
                            if (foo != 0) {
                                return foo;
                            }
                        }
                    }

                    if (a.pettype != null & b.pettype != null) {
                        return Listview.funcBox.assocArrCmp(a.pettype, b.pettype, g_pet_types);
                    }

                    return Listview.funcBox.assocArrCmp(a.skill, b.skill, g_spell_skills);
                }
            },
            /* AoWoW: custom start */
            {
                id: 'stackRules',
                name: LANG.asr_behaviour,
                type: 'text',
                width: '20%',
                hidden: true,
                compute: function(spell, td) {
                    if (!spell.stackRule) {
                        return;
                    }

                    var
                        buff  = '',
                        buff2 = '';

                    switch (spell.stackRule) {
                        case 3:
                            buff2 = LANG.asr_strongest_effect;
                        case 0:
                            buff  = LANG.asr_coexist;       // without condition
                            break;
                        case 2:
                        case 4:
                            buff2 = spell.stackRule == 2 ? LANG.asr_same_owner : LANG.asr_strongest_effect;
                        case 1:
                            buff  = LANG.asr_exclusive;     // without condition
                            break;
                    }

                    td.className = 'small';
                    td.style.lineHeight = '18px';

                    var span = $WH.ce('span');
                    span.className = !spell.stackRule || spell.stackRule == 3 ? 'q2' : 'q10';
                    $WH.ae(span, $WH.ct(buff));
                    $WH.ae(td, span);

                    if (buff2) {
                        var sp2 = $WH.ce('span');
                        sp2.style.whiteSpace = 'nowrap';
                        sp2.className = 'q0';
                        $WH.ae(td, $WH.ce('br'));
                        $WH.st(sp2, buff2);
                        $WH.ae(td, sp2);
                    }
                },
                getVisibleText: function (spell) {
                    if (!spell.stackRule) {
                        return;
                    }

                    var buff = '';
                    switch (spell.stackRule) {
                        case 3:
                            buff += LANG.asr_strongest_effect;
                        case 0:
                            buff += ' ' + LANG.asr_coexist;
                            break;
                        case 2:
                            buff += LANG.asr_same_owner;
                        case 1:
                            buff += ' ' + LANG.asr_exclusive;
                            break;
                    }

                    return buff;
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(a.stackRule, b.stackRule);
                }
            },
            {
                id: 'linkedTrigger',
                name: LANG.ls_trigger,
                type: 'text',
                width: '50%',
                hidden: true,
                compute: function(spell, td) {
                    if (!spell.linked) {
                        return;
                    }

                    var
                        trigger = spell.linked[0],
                        buff    = '';

                    switch (spell.linked[2]) {
                        case 0:
                            buff = trigger > 0 ? LANG.ls_onCast : LANG.ls_onAuraRemove;
                            break;
                        case 1:
                            buff = LANG.ls_onSpellHit;
                            break;
                        case 2:
                            buff = LANG.ls_onAuraApply;
                            break;
                    }

                    td.className = 'small';
                    td.style.lineHeight = '18px';

                    var a = $WH.ce('a');
                    a.style.whiteSpace = 'nowrap';
                    a.href = '?spell=' + Math.abs(trigger);
                    if (g_pageInfo.typeId == Math.abs(trigger)) { // ponts to self
                        a.className = 'q1';
                        $WH.st(a, LANG.ls_self);
                    }
                    else {
                        var item = g_spells[Math.abs(trigger)];
                        a.className = 'icontiny tinyspecial';
                        a.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + item['icon'] + '.gif)';
                        $WH.st(a, item['name_' + Locale.getName()]);
                    }
                    $WH.ae(td, a);

                    var span = $WH.ce('span');
                    span.className = 'q0';
                    $WH.st(span, buff);
                    $WH.ae(td, $WH.ce('br'));
                    $WH.ae(td, span);

                },
                getVisibleText: function (spell) {
                    if (!spell.linked) {
                        return;
                    }

                    var
                        trigger = spell.linked[0],
                        buff    = '';

                    if (g_pageInfo.typeId == Math.abs(trigger)) {
                        buff += LANG.ls_self;
                    }
                    else {
                        buff += g_spells[Math.abs(trigger)]['name_' + Locale.getName()];
                    }

                    switch (spell.linked[2]) {
                        case 0:
                            buff += trigger > 0 ? ' ' + LANG.ls_onCast : ' ' + LANG.ls_onAuraRemove;
                            break;
                        case 1:
                            buff += ' ' + LANG.ls_onSpellHit;
                            break;
                        case 2:
                            buff += ' ' + LANG.ls_onAuraApply;
                            break;
                    }

                    return buff;
                },
                sortFunc: function(a, b, col) {
                    var
                        trA = a.linked[0],
                        trB = b.linked[0];

                    if (trA > 0 && trB < 0) {
                        return -1;
                    }
                    else if (trA < 0 && trB > 0) {
                        return 1;
                    }

                    if (g_pageInfo.typeId == Math.abs(trA)) {
                        return 1;
                    }
                    else if (g_pageInfo.typeId == Math.abs(trB)) {
                        return -1;
                    }
                    else if (trA != trB) {
                        return $WH.strcmp(g_spells[Math.abs(trA)]['name_' + Locale.getName()],  g_spells[Math.abs(trB)]['name_' + Locale.getName()]);
                    }

                    return 0;
                }
            },
            {
                id: 'linkedEffect',
                name: LANG.ls_effects,
                type: 'text',
                width: '50%',
                hidden: true,
                compute: function(spell, td) {
                    if (!spell.linked) {
                        return;
                    }

                    var
                        effect = spell.linked[1],
                        buff   = '';

                    switch (spell.linked[2]) {
                        case 0:
                        case 1:
                            buff = effect > 0 ? LANG.ls_onTrigger : LANG.ls_onAuraRemove;
                            break;
                        case 2:
                            buff = effect > 0 ? LANG.ls_onAuraApply : LANG.ls_onImmune;
                            break;
                    }

                    td.className = 'small';
                    td.style.lineHeight = '18px';

                    var a = $WH.ce('a');
                    a.style.whiteSpace = 'nowrap';
                    a.href = '?spell=' + Math.abs(effect);
                    if (g_pageInfo.typeId == Math.abs(effect)) { // points to self
                        a.className = 'q1';
                        $WH.st(a, LANG.ls_self);
                    }
                    else {
                        var item = g_spells[Math.abs(effect)];
                        a.className = 'icontiny tinyspecial';
                        a.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + item['icon'] + '.gif)';
                        $WH.st(a, item['name_' + Locale.getName()]);
                    }
                    $WH.ae(td, a);

                    var span = $WH.ce('span');
                    span.className = 'q0';
                    $WH.st(span, buff);
                    $WH.ae(td, $WH.ce('br'));
                    $WH.ae(td, span);

                },
                getVisibleText: function (spell) {
                    if (!spell.linked) {
                        return;
                    }

                    var
                        effect = spell.linked[1],
                        buff   = '';

                    if (g_pageInfo.typeId == Math.abs(effect)) {
                        buff += LANG.ls_self;
                    }
                    else {
                        buff += g_spells[Math.abs(effect)]['name_' + Locale.getName()];
                    }

                    switch (spell.linked[2]) {
                        case 0:
                        case 1:
                            buff += effect > 0 ? ' ' + LANG.ls_onTrigger : ' ' + LANG.ls_onAuraRemove;
                            break;
                        case 2:
                            buff += effect > 0 ? ' ' + LANG.ls_onAuraApply : ' ' + LANG.ls_onImmune;
                            break;
                    }

                    return buff;
                },
                sortFunc: function(a, b, col) {
                    var
                        effA = a.linked[1],
                        effB = b.linked[1];

                    if (effA > 0 && effB < 0) {
                        return -1;
                    }
                    else if (effA < 0 && effB > 0) {
                        return 1;
                    }

                    if (g_pageInfo.typeId == Math.abs(effA)) {
                        return 1;
                    }
                    else if (g_pageInfo.typeId == Math.abs(effB)) {
                        return -1;
                    }
                    else if (effA != effB) {
                        return $WH.strcmp(g_spells[Math.abs(effA)]['name_' + Locale.getName()],  g_spells[Math.abs(effB)]['name_' + Locale.getName()]);
                    }

                    return 0;
                }
            },
            /* AoWoW: custom end */
            {
                id: 'completed', // Listview.COLUMN_ID_COMPLETION
                name: LANG.completion, // WH.TERMS.completion
                hidden: true,
                compute: function (spell, td)
                {
                    if (!spell.hasOwnProperty('cat') || !Listview.templates.spell._validCompletionCategory(spell.cat))
                        return;

                    $WH.addCompletionIcons(td, 6, spell.id)
                },
                sortFunc: function (a, b)
                {
                    // return $WH.stringCompare(
                    return $WH.strcmp(
                        $WH.getCompletionFlags(6, a.id),
                        $WH.getCompletionFlags(6, b.id)
                    );
                },
                getValue: function (spell) {
                    var value = 0;
                    var completionData = g_user.completion?.hasOwnProperty(6) ? g_user.completion[6] : {};

                    if (!spell.hasOwnProperty('cat') || !Listview.templates.spell._validCompletionCategory(spell.cat))
                        return -1;

                    for (var i in g_user.characters)
                    {
                        var profile = g_user.characters[i];
                        if (!(profile.id in completionData))
                            continue;

                        if ($WH.in_array(completionData[profile.id], spell.id) != -1)
                            value++;
                    }

                    return value;
                }
            }
        ],

        getItemLink: function(spell) {
            return '?spell=' + spell.id;
        },

        _validCompletionCategory: function (category) {
            return $WH.in_array(g_completion_categories[6], category) != -1;
        },

        onBeforeCreate: function () {
            for (var i in this.columns)
            {
                if (this.columns[i].id == 'completed' && this.columns[i].hidden)
                {
                    if ($WH.isset('g_user') && 'characters' in g_user && $WH.in_array(this.hiddenCols, this.columns[i].id) == -1)
                    {
                        var n = 0;
                        for (var j in this.data)
                            if (this.data[j].hasOwnProperty('cat') && Listview.templates.spell._validCompletionCategory(this.data[j].cat))
                                n++;

                        if (n > this.data.length * 0.1)
                            this.visibility.push(parseInt(i));
                    }
                }
            }
        }
    },

    zone: {
        sort: [1],
        nItemsPerPage: -1,
        searchable: 1,
        filtrable: 1,

        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                value: 'name',
                compute: function(zone, td) {
                    var
                        wrapper = $WH.ce('div'),
                        a = $WH.ce('a');

                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(zone);
                    $WH.ae(a, $WH.ct(zone.name));
                    if (zone.expansion) {
                        var sp = $WH.ce('span');
                            sp.className = g_GetExpansionClassName(zone.expansion);
                        $WH.ae(sp, a);
                        $WH.ae(wrapper, sp);
                    }
                    else {
                        $WH.ae(wrapper, a);
                    }

                    $WH.ae(td, wrapper);

                    if (zone.subzones) {
                        if (zone.subzones.length == 1 && zone.subzones[0] == zone.id)
                            return;

                        var nRows = parseInt(zone.subzones.length / 3);
                        if (nRows != (zone.subzones.length / 3))
                            nRows++;

                        wrapper.style.position = 'relative';
                        wrapper.style.minHeight = ((nRows * 12) + 19) + 'px';

                        var d = $WH.ce('div');
                        d.className       = 'small';
                        d.style.fontStyle = 'italic';
                        d.style.position  = 'absolute';
                        d.style.right     = '2px';
                        d.style.bottom    = '2px';
                        d.style.textAlign = 'right';

                        for (i in zone.subzones) {
                            if (!g_gatheredzones[zone.subzones[i]]) {
                                continue;
                            }

                            if (i > 0) {
                                $WH.ae(d, $WH.ct(LANG.comma));
                            }

                            var a = $WH.ce('a');

                            a.className = zone.subzones[i] == zone.id ? 'q1' : 'q0';
                            a.style.whiteSpace = 'nowrap';
                            a.href = '?zone=' + zone.subzones[i];
                            $WH.st(a, g_gatheredzones[zone.subzones[i]]['name_' + Locale.getName()]);
                            $WH.ae(d, a);
                        }

                        $WH.ae(wrapper, d);
                    }
                },
                getVisibleText: function(zone) {
                    var buff = zone.name + Listview.funcBox.getExpansionText(zone);

                    if (zone.instance == 5 || zone.instance == 8) {
                        buff += ' heroic';
                    }

                    return buff;
                }
            },
            {
                id: 'level',
                name: LANG.level,
                type: 'range',
                width: '10%',
                getMinValue: function(zone) {
                    return zone.minlevel;
                },
                getMaxValue: function(zone) {
                    return zone.maxlevel;
                },
                compute: function(zone, td) {
                    if (zone.minlevel > 0 && zone.maxlevel > 0) {
                        if (zone.minlevel != zone.maxlevel) {
                            return zone.minlevel + LANG.hyphen + zone.maxlevel;
                        }
                        else {
                            return zone.minlevel;
                        }
                    }
                },
                sortFunc: function(a, b, col) {
                    if (col > 0) {
                        return $WH.strcmp(a.minlevel, b.minlevel) || $WH.strcmp(a.maxlevel, b.maxlevel);
                    }
                    else {
                        return $WH.strcmp(a.maxlevel, b.maxlevel) || $WH.strcmp(a.minlevel, b.minlevel);
                    }
                }
            },
            {
                id: 'players',
                name: LANG.players,
                type: 'text',
                hidden: true,
                compute: function(zone, td) {
                    if (zone.instance > 0) {
                        var sp = $WH.ce('span');

                        if (zone.nplayers == -2) {
                            zone.nplayers = '10/25';
                        }

                        var buff = '';
                        if (zone.nplayers) {
                            if (zone.instance == 4) {
                                buff += $WH.sprintf(LANG.lvzone_xvx, zone.nplayers, zone.nplayers);
                            }
                            else {
                                buff += $WH.sprintf(LANG.lvzone_xman, zone.nplayers);
                            }
                        }

                        $WH.ae(sp, $WH.ct(buff));
                        $WH.ae(td, sp);
                    }
                },
                getVisibleText: function(zone) {
                    if (zone.instance > 0) {
                        if (zone.nplayers == -2) {
                            zone.nplayers = '10/25';
                        }

                        var buff = '';
                        if (zone.nplayers && ((zone.instance != 2 && zone.instance != 5) || zone.nplayers > 5)) {
                            if (zone.instance == 4) {
                                buff += $WH.sprintf(LANG.lvzone_xvx, zone.nplayers, zone.nplayers);
                            }
                            else {
                                buff += $WH.sprintf(LANG.lvzone_xman, zone.nplayers);
                            }
                        }
                        return buff;
                    }
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(a.nplayers, b.nplayers);
                }
            },
            {
                id: 'territory',
                name: LANG.territory,
                type: 'text',
                width: '13%',
                compute: function(zone, td) {
                    var sp = $WH.ce('span');
                    switch (zone.territory) {
                    case 0:
                        sp.className = 'icon-alliance';
                        break;
                    case 1:
                        sp.className = 'icon-horde';
                        break;
                    case 4:
                        sp.className = 'icon-ffa';
                        break;
                    }

                    $WH.ae(sp, $WH.ct(g_zone_territories[zone.territory]));
                    $WH.ae(td, sp);
                },
                getVisibleText: function(zone) {
                    return g_zone_territories[zone.territory];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_zone_territories[a.territory], g_zone_territories[b.territory]);
                }
            },
            {
                id: 'instancetype',
                name: LANG.instancetype,
                type: 'text',
                compute: function(zone, td) {
                    if (zone.instance > 0) {
                        var sp = $WH.ce('span');
                        if ((zone.instance >= 1 && zone.instance <= 5) || zone.instance == 7 || zone.instance == 8) {
                            sp.className = 'icon-instance' + zone.instance;
                        }

                        var buff = g_zone_instancetypes[zone.instance];

                        if (zone.heroicLevel) {
                            var skull = $WH.ce('span');
                            skull.className = 'icon-heroic';
                            g_addTooltip(skull, LANG.tooltip_heroicmodeavailable + LANG.qty.replace('$1', zone.heroicLevel));
                            $WH.ae(td, skull);
                        }

                        $WH.ae(sp, $WH.ct(buff));
                        $WH.ae(td, sp);
                    }
                },
                getVisibleText: function(zone) {
                    if (zone.instance > 0) {
                        var buff = g_zone_instancetypes[zone.instance];

                        if (zone.nplayers && ((zone.instance != 2 && zone.instance != 5) || zone.nplayers > 5)) {
                            if (zone.instance == 4) {
                                buff += ' ' + $WH.sprintf(LANG.lvzone_xvx, zone.nplayers, zone.nplayers);
                            }
                            else {
                                buff += ' ' + $WH.sprintf(LANG.lvzone_xman, zone.nplayers);
                            }
                        }
                        if (zone.instance == 5 || zone.instance == 8) {
                            buff += ' heroic';
                        }

                        return buff;
                    }
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_zone_instancetypes[a.instance], g_zone_instancetypes[b.instance]) || $WH.strcmp(a.instance, b.instance) || $WH.strcmp(a.nplayers, b.nplayers);
                }
            },
            {
                id: 'category',
                name: LANG.category,
                type: 'text',
                width: '15%',
                compute: function(zone, td) {
                    td.className = 'small q1';
                    var a = $WH.ce('a');
                    a.href = '?zones=' + zone.category;
                    $WH.ae(a, $WH.ct(g_zone_categories[zone.category]));
                    $WH.ae(td, a);
                },
                getVisibleText: function(zone) {
                    return g_zone_categories[zone.category];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_zone_categories[a.category], g_zone_categories[b.category]);
                }
            }
        ],

        getItemLink: function(zone) {
            return '?zone=' + zone.id;
        }
    },

    holiday: {
        sort: [2, 1],
        nItemsPerPage: -1,
        searchable: 1,
        filtrable: 1,

        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                value: 'name',
                span: 2,
                compute: function(holiday, td, tr) {
                    var i = $WH.ce('td');
                    i.style.width = '1px';
                    i.style.padding = '0';
                    i.style.borderRight = 'none';

                    $WH.ae(i, g_holidays.createIcon(holiday.id, 0));
                    $WH.ae(tr, i);
                    td.style.borderLeft = 'none';

                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(holiday);
                    $WH.ae(a, $WH.ct(holiday.name));
                    $WH.ae(td, a);
                },
                getVisibleText: function(holiday) {
                    return holiday.name;
                }
            },
            {
                id: 'date',
                name: LANG.date,
                type: 'text',
                width: '16%',
                allText: true,
                compute: function(holiday, td, tr) {
                    if (holiday.startDate && holiday.endDate) {
                        var dates = Listview.funcBox.getEventNextDates(holiday.startDate, holiday.endDate, holiday.rec || 0);

                        if (dates[0] && dates[1]) {
                            var
                                start = g_formatDateSimple(dates[0]),
                                end = g_formatDateSimple(dates[1]),
                                sp = $WH.ce('span');

                            if (start != end) {
                                $WH.st(sp, start + LANG.hyphen + end);
                            }
                            else {
                                $WH.st(sp, start);
                            }
                            $WH.ae(td, sp);

                            if (dates[0] <= g_serverTime && dates[1] >= g_serverTime) {
                                tr.className = 'checked';
                                sp.className = 'q2 tip';
                                g_addTooltip(sp, LANG.tooltip_activeholiday, 'q');
                            }
                        }
                    }
                },
                getVisibleText: function(holiday) {
                    if (holiday.startDate && holiday.endDate) {
                        var dates = Listview.funcBox.getEventNextDates(holiday.startDate, holiday.endDate, holiday.rec || 0);

                        if (dates[0] && dates[1]) {
                            var
                                start = g_formatDateSimple(dates[0]),
                                end = g_formatDateSimple(dates[1]);

                            if (start != end) {
                                return start + LANG.hyphen + end;
                            }
                            else {
                                return start;
                            }
                        }
                    }

                    return'';
                },
                sortFunc: function(a, b, col) {
                    if (a.startDate && b.startDate) {
                        var datesA = Listview.funcBox.getEventNextDates(a.startDate, a.endDate, a.rec || 0);
                        var datesB = Listview.funcBox.getEventNextDates(b.startDate, b.endDate, b.rec || 0);

                        if (datesA[0] && datesB[0]) {
                            return datesA[0] - datesB[0];
                        }
                    }
                    else if (a.startDate) {
                        return -1;
                    }
                    else if (b.startDate) {
                        return 1;
                    }

                    return 0;
                }
            },
            {
                id: 'category',
                name: LANG.category,
                type: 'text',
                width: '16%',
                compute: function(holiday, td) {
                    td.className = 'small q1';
                    var
                        a = $WH.ce('a'),
                        href = '?events=' + holiday.category;

                    a.href = href;
                    $WH.ae(a, $WH.ct(g_holiday_categories[holiday.category]));
                    $WH.ae(td, a);
                },
                getVisibleText: function(holiday) {
                    return g_holiday_categories[holiday.category];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_holiday_categories[a.category], g_holiday_categories[b.category]);
                }
            }
        ],

        getItemLink: function(holiday) {
            return '?event=' + holiday.id;
        }
    },

    holidaycal: {
        sort: [1],
        mode: 4, // Calendar
        startOnMonth: new Date(g_serverTime.getFullYear(), 0, 1),
        nMonthsToDisplay: 12,
        rowOffset: g_serverTime.getMonth(),
        poundable: 2, // Yes but w/o sort

        columns: [],

        compute: function(holiday, div, i) {
            if (!holiday.events || !holiday.events.length) {
                return;
            }

            for (var i = 0; i < holiday.events.length; ++i) {
                var icon = g_holidays.createIcon(holiday.events[i].id, 1);
                icon.onmouseover = Listview.funcBox.dateEventOver.bind(icon, holiday.date, holiday.events[i]);
                icon.onmousemove = $WH.Tooltip.cursorUpdate;
                icon.onmouseout  = $WH.Tooltip.hide;
                icon.style.cssFloat = icon.style.styleFloat = 'left';
                $WH.ae(div, icon);
            }
        },
        sortFunc: function(a, b) {
            if (a.startDate && b.startDate) {
                var datesA = Listview.funcBox.getEventNextDates(a.startDate, a.endDate, a.rec || 0);
                var datesB = Listview.funcBox.getEventNextDates(b.startDate, b.endDate, b.rec || 0);

                for (var i = 0; i < 2; ++i) {
                    var
                        dA = datesA[i],
                        dB = datesB[i];

                    if (dA.getFullYear() == dB.getFullYear() && dA.getMonth() == dB.getMonth() && dA.getDate() == dB.getDate()) {
                        return dA - dB;
                    }
                }
            }

            return $WH.strcmp(a.name, b.name);
        }
    },

    comment: {          // todo: reformat
        sort: [-5, 4, 6, -3, 2], // this is the default for coSortHighestRating so that a double-sort doesn't happen
        mode: 2, // Div mode
        nItemsPerPage: 40,
        poundable: 2, // Yes, but w/o sort

        columns: [
            { value: 'number' },
            { value: 'id' },
            { value: 'rating' },
            { value: 'deleted' },
            { value: 'sticky' },
            { value: 'outofdate' }
        ],

        compute: function(comment, div, i)
        {
            /** Variables **/
            var container = $('<div></div>');
            var hidden = (comment.__minPatch && g_getPatchVersion.T[comment.__minPatch] > new Date(comment.date));
            comment.locale = (this.id == 'english-comments' ? 'en' : '');

            /** Initialization.. Create base HTML skeleton */
            container.append('<table><tr><td class="vote-column">' +
                /* VOTE */ '<p class="upvote">+</p><div class="rating-container"></div><p class="downvote">-</p><p class="comment-sticky" title="' + LANG.stickycomment_tip + '">STICKY</p>' +
                '</td><td class="main-body">' +
                /* HEADER */ '<div class="comment-header"><table><tr><td class="comment-author"></td><td class="comment-controls"></td><td class="comment-notification"></td></tr></table></div>' +
                /* BODY */ '<div class="text comment-body"></div>' +
                /* REPLIES */ '<div class="comment-replies"></div>' +
                /* REPLIES CONTROL */ '<p class="comment-replies-control" style="display: none"></p>' +
                '</td></tr></table>');

            comment.colorClass = 'comment' + (i % 2);
            container.addClass('comment').addClass(comment.colorClass);
            if (comment.rating < -4) {
                container.addClass('comment-lowrated');
            }
            if (comment.deleted)
                container.addClass('comment-deleted');
            if (comment.outofdate)
                container.addClass('comment-outofdate');

            comment.container = container;
            comment.voteCell = container.find('.vote-column');
            comment.commentCell = container.find('.main-body');
            comment.repliesCell = container.find('.comment-replies');
            comment.repliesControl = container.find('.comment-replies-control');
            comment.headerCell = container.find('.comment-header');
            comment.commentBody = container.find('.comment-body');
            comment.commentAuthor = container.find('.comment-author');
            comment.commentControls = container.find('.comment-controls');

            this.template.updateVoteCell(comment, true);
            this.template.updateCommentCell(comment);
            this.template.updateStickyStatus(comment);
            this.template.updateReplies(comment);

            // headers for hidden comments
            if (comment.deleted || comment.rating < -4) {
                comment.headerCell.css('cursor', 'pointer');
                comment.headerCell.bind('click', function(e) {
                    if ($WH.$E(e)._target.nodeName == 'A') {
                        return;                             // aowow - custom: prevent toggle if using function buttons
                    }

                    comment.voteCell.toggle();
                    comment.repliesCell.toggle();
                    comment.commentBody.toggle();
                });
            }

            $(div).addClass('comment-wrapper').html(container);
            if (hidden)
                $(div).hide();
            else
                $(div).show();
        },

        applyAuthorTitle: function (container, title)
        {
            if (!title.label)
                return;

            let cssClass = ['comment-reply-author-label'].concat(title.classes);

            $WH.ae(container, $WH.ct(' '));                   // aowow - LANG.wordspace_punct

            if (title.url)
                $WH.ae(container, $WH.ce('a', { className: cssClass.join(' '), href: title.url }, $WH.ct(`<${ title.label }>`)));
            else
                $WH.ae(container, $WH.ce('span', { className: cssClass.join(' ') }, $WH.ct(`<${ title.label }>`)));
        },

        getAuthorTitle: function (author)
        {
            let title = {
                classes: [],
                label:   undefined,
                url:     undefined
            };

            if (g_pageInfo.author === author) {
                title.label = LANG.guideAuthor;
                return title;
            }

            let user = g_users[author];
            if (user) {
                // aowow - let roleColor = WH.User.getCommentTitleClass(_.roles, _.tierClass, user);
                let roleColor = g_GetStaffColorFromRoles(user.roles);
                if (roleColor) {
                    title.classes.push(roleColor);
                }
                // aowow - title.label = WH.User.getCommentRoleLabel(user.roles, user.title, user.tierTitle);
                title.label = g_GetCommentRoleLabel(user.roles, user.title);
                title.url = /* user.tierTitle && !user.title ? '/?premium' : */ ''; // aowow - tierTitle being the premium tier ("Rare|Epic|Legendary Premium User")
            }

            return title;
        },

        updateReplies: function(comment)
        {
            this.updateRepliesCell(comment);
            this.updateRepliesControl(comment);
            SetupReplies(comment.container, comment);

            /* Let's see if the user wants to see a specific reply; only if it's the first time loading the page. */
            if (!comment.alreadyLoadedBefore)
            {
                comment.alreadyLoadedBefore = true;
                var matches = location.hash.match('#comments:id=([0-9]+):reply=([0-9]+)');

                if (matches)
                {
                    var commentId = matches[1];
                    var replyId = matches[2];

                    if (comment.id == commentId)
                        this.highlightReply(comment, replyId);
                }
            }
        },

        highlightReply: function(comment, replyId, fetchedMoreReplies)
        {
            var reply = null;

            /* First let's try to find it */
            for (var idx in comment.replies)
            {
                if (comment.replies[idx].id == replyId)
                {
                    reply = comment.replies[idx];
                    break;
                }
            }

            if (!reply)
            {
                if (fetchedMoreReplies)
                    return; /* Doesn't exist */

                $.ajax({
                    type: 'GET',
                    url: '?comment=show-replies',
                    data: { id: comment.id },
                    success: function (replies) { comment.replies = replies; Listview.templates.comment.updateReplies(comment); Listview.templates.comment.highlightReply(comment, replyId, true); },
                    dataType: 'json'
                });

                return;
            }

            /* Ok, we have found the reply to highlight. */
            var replyRow    = comment.repliesCell.find('.comment-reply-row[data-replyid=' + replyId + ']');
            var normalColor = '#292929';

            if (comment.deleted) {
                normalColor = '#402424';
            } else if (comment.outofdate) {
                normalColor = '#322C1C';
            } else if (comment.colorClass == 'comment0') {
                normalColor = '#242424';
            }

            replyRow.css('background-color', '#581111');
            setTimeout(function() { replyRow.animate({ backgroundColor: normalColor }, 1500) }, 1500);
        },

        updateStickyStatus: function(comment)
        {
            if (comment.sticky)
                comment.voteCell.find('.comment-sticky').show();
            else
                comment.voteCell.find('.comment-sticky').hide();
        },

        updateRepliesCell: function(comment)
        {
            var container = comment.repliesCell;
            var table = $('<table></table>');

            container.html('');

            if (!comment.replies.length)
            {
                container.append(table).hide();
                return;
            }

            for (var i = 0; i < comment.replies.length; ++i)
            {
                var reply = comment.replies[i];
                let owner = g_pageInfo.author === reply.username;
                var row = $('<tr class="comment-reply-row"><td class="reply-controls"><p class="reply-upvote">upvote</p><p class="reply-rating"></p><p class="reply-downvote">downvote</p></td><td class="reply-text"></td></tr>');
                var replyBy = $('<p class="comment-reply-author"></p>');
                var replyByUserLink = $('<a></a>');
                var creationDate = new Date(reply.creationdate);
                var elapsed = (g_serverTime - creationDate) / 1000;
                var upvoteControl = row.find('.reply-upvote');
                var downvoteControl = row.find('.reply-downvote');
                var canDelete = (g_user.name == reply.username) || (g_user.roles & U_GROUP_COMMENTS_MODERATOR) != 0;
                var canEdit = canDelete;

                row.attr('data-replyid', reply.id);
                row.attr('data-idx', i);

                // aowow - let cssClass = WH.User.getCommentRoleClass(reply.roles, reply.username);
                let cssClass = g_GetStaffColorFromRoles(reply.roles);
                if (!['comment-blue', 'comment-green'].includes(cssClass) && owner) {
                    cssClass = 'comment-green';             // comment-guide-author
                }
                row.find('.reply-text').addClass(cssClass);

                var replyWhen = $('<a></a>');
                replyWhen.text(g_formatDate(null, elapsed, creationDate));
                replyWhen.attr('href', '#comments:id=' + comment.id + ':reply=' + reply.id);
                replyWhen.attr('id', 'comments:id=' + comment.id + ':reply=' + reply.id);
                replyWhen.addClass('when');

                replyByUserLink.attr('href', '?user=' + reply.username);
                replyByUserLink.text(reply.username);
                replyBy.append(replyByUserLink);
                this.applyAuthorTitle(replyBy[0], this.getAuthorTitle(reply.username))

                replyBy.append(' ').append(replyWhen).append(' ').append($WH.sprintf(LANG.lvcomment_patch, g_getPatchVersion(creationDate)));


                var replyHtml = Markup.toHtml(reply.body, {allow: Markup.CLASS_USER, mode: Markup.MODE_REPLY, roles: 0, locale: comment.locale});

                replyHtml = replyHtml.replace(/[^\s<>]{81,}/, function(text) {
                    if (text.substring(0, 4) == 'href' || text.substring(0, 3) == 'src')
                        return text;

                    var ret = '';
                    for (j = 0; j < text.length; j += 80)
                        ret += (text.substring(j, 80 + j) + " ");

                    return ret;
                });

                /* Flag */
                if (g_user.canUpvote && g_user.name != reply.username && !reply.reportedByUser)
                    replyHtml += ' <span class="reply-report" title="' + LANG.reportthisreply_stc + '">report</span>';

                if (canEdit)
                    replyHtml += ' <span class="reply-edit" title="' + LANG.editthisreply_stc + '">edit</span>';

                if (g_user.roles & U_GROUP_COMMENTS_MODERATOR)
                    replyHtml += ' <span class="reply-detach" title="' + LANG.lvcomment_detach + '">detach</span>';

                if (canDelete)
                    replyHtml += ' <span class="reply-delete" title="' + LANG.deletethisreply_stc + '">delete</span>';

                row.find('.reply-text').html(replyBy).append(replyHtml);

                row.find('.reply-rating').text(reply.rating);

                /* Upvote */
                if (reply.votedByUser)
                    upvoteControl.attr('data-hasvoted', 'true');
                if (g_user.canUpvote && g_user.name != reply.username && !reply.votedByUser && !reply.downvotedByUser)
                    upvoteControl.attr('data-canvote', 'true');

                /* Downvote */
                if (reply.downvotedByUser)
                    downvoteControl.attr('data-hasvoted', 'true');
                if (g_user.canDownvote && g_user.name != reply.username && !reply.votedByUser && !reply.downvotedByUser)
                    downvoteControl.attr('data-canvote', 'true');

                table.append(row);
            }

            container.html(table);

            if (comment.deleted || comment.rating < -4) {
                comment.repliesCell.hide();
            }
        },

        updateRepliesControl: function(comment)
        {
            var control = comment.repliesControl;
            control.html('').hide().unbind().attr('class', 'comment-replies-control');
            var extraReplies = Math.max(comment.nreplies - comment.replies.length, 0);

            if ((comment.deleted || comment.outofdate || comment.rating < -4) || (!g_user.canPostReplies && !extraReplies))
                return;

            if (g_user.canPostReplies)
            {
                if (!extraReplies)
                    control.text(LANG.addreply_stc).addClass('add-reply');
                else
                    control.text($WH.sprintf(extraReplies == 1 ? LANG.addshow1morereply_stc : LANG.addshowmorereplies_format, extraReplies)).addClass('show-more-replies');
            }
            else
                    control.text($WH.sprintf(extraReplies == 1 ? LANG.show1morereply_stc : LANG.showmorereplies_format, extraReplies)).addClass('show-more-replies');

            control.show();
        },

        updateCommentCell: function(comment)
        {
            comment.commentCell.find('.comment-edit').remove();

            this.updateCommentBody(comment, comment.commentBody);
            this.updateCommentAuthor(comment, comment.commentAuthor);
            this.updateCommentControls(comment, comment.commentControls);
        },

        updateCommentAuthor: function(comment, container)
        {
            var user = g_users[comment.user];
            var postedOn = new Date(comment.date);
            var elapsed = (g_serverTime - postedOn) / 1000;

            container.html('');

            container.append(LANG.lvcomment_by);
            container.append($WH.sprintf('<a href="?user=$1">$2</a>', comment.user, comment.user));
            // aowow - avatar recovered and transplanted from commentsv1 version
            if (user != null && user.avatar) {
                var icon = Icon.createUser(user.avatar, user.avatarmore, 0, null, (user.roles & U_GROUP_PREMIUM) ? user.border : Icon.STANDARD_BORDER, 0, Icon.getPrivilegeBorder(user.reputation));
                icon.style.marginRight = '3px';
                icon.style.cssFloat    = 'left';

                container.css('lineHeight', '25px');
                container.append(icon);
            }
            // aowow - end recover
            container.append(g_getReputationPlusAchievementText(user.gold, user.silver, user.copper, user.reputation));
            this.applyAuthorTitle(container[0], this.getAuthorTitle(comment.user));
            container.append($WH.sprintf(' <a class="q0" id="comments:id=$1" href="#comments:id=$2">$3</a>', comment.id, comment.id, g_formatDate(null, elapsed, postedOn)));
            container.append(' ');
            container.append($WH.sprintf(LANG.lvcomment_patch, g_getPatchVersion(postedOn)));
        },

        updateCommentControls: function(comment, container)
        {
            /** VARIABLES **/
            var controls = []; // List of objects: .name and .func

            var canEdit = (g_user.roles & U_GROUP_COMMENTS_MODERATOR) != 0 || (comment.user.toLowerCase() == g_user.name.toLowerCase() && !g_user.commentban);
            var canDelete = (g_user.roles & U_GROUP_COMMENTS_MODERATOR) != 0 || (comment.user.toLowerCase() == g_user.name.toLowerCase() && !g_user.commentban);
            var isAdmin = (g_user.roles & U_GROUP_COMMENTS_MODERATOR) != 0;

            /** PUT THE CONTROLS IN `controls` **/
            if (canEdit) {
                if (!comment.outofdate && !comment.deleted) {
                    controls.push({name: LANG.lvcomment_edit, func: Listview.funcBox.coEdit.bind(this, comment, 0, false)});
                }
            }

            if (canDelete && !comment.deleted) {
                controls.push({name: LANG.lvcomment_delete, func: Listview.funcBox.coDelete.bind(this, comment)});
            }

            if (isAdmin && comment.deleted) {
                controls.push({name: LANG.lvcomment_undelete, func: Listview.funcBox.coUndelete.bind(this, comment)});
            }

            if (isAdmin && comment.outofdate) {
                controls.push({name: LANG.lvcomment_uptodate, func: Listview.funcBox.coFlagUpOfDate.bind(this, comment)});
            }

            /** SpiffyJr, add Flag as out of date **/
            if (g_user.id != 0 && !comment.outofdate && !comment.deleted) {
                controls.push({name: LANG.lvcomment_outofdate, func: Listview.funcBox.coFlagOutOfDate.bind(this, comment)});
            }


            if (isAdmin && !comment.sticky && !comment.outofdate && !comment.deleted)
                controls.push({name: 'Sticky', func: this.sticky.bind(this, comment)});
            if (isAdmin && comment.sticky && !comment.outofdate && !comment.deleted)
                controls.push({name: 'Unsticky', func: this.sticky.bind(this, comment)});

            if (!comment.outofdate && !comment.deleted) {
                controls.push({extraClass: 'icon-report', tooltip: LANG.report_tooltip, name: LANG.lvcomment_report, func: ContactTool.show.bind(ContactTool, {mode: 1, comment: comment})});
            }

            /** WRITE THE CONTROLS **/
            container.html('');

            for (var idx in controls)
            {
                var data = controls[idx];
                var control = $('<a></a>');
                var insideContainer = $('<span></span>');

                if (idx != 0)
                    insideContainer.append(' | ');

                control.click(data.func);
                control.text(data.name);

                if (data.extraClass)
                    control.addClass(data.extraClass);

                if (data.tooltip)
                    control.mouseover(function(event) {$WH.Tooltip.showAtCursor(event, data.tooltip, 0, 0, 'q2');}).mousemove(function(event) {$WH.Tooltip.cursorUpdate(event)}).mouseout(function() {$WH.Tooltip.hide()});

                insideContainer.append(control);
                container.append(insideContainer);
            }
        },

        updateCommentBody: function(comment, commentBody)
        {
            var allowed = Markup.rolesToClass(comment.roles);

            /** BODY **/
            commentBody.removeClass('comment-green');
            commentBody.addClass(Listview.funcBox.coGetColor(comment));
            commentBody.html(Markup.toHtml(comment.body, {allow: allowed, mode: Markup.MODE_COMMENT, roles: comment.roles, locale: comment.locale}));

            /** ADMIN RESPONSE **/
            if (comment.response)
            {
                var adminResponse = $('<div></div>');
                adminResponse.addClass('text comment-body');
                adminResponse.html(Markup.toHtml('[div][/div][wowheadresponse=' + comment.responseuser + ' roles=' + comment.responseroles + ']' + comment.response + '[/wowheadresponse]', { allow: Markup.CLASS_STAFF, roles: comment.responseroles, uid: 'resp-' + comment.id }));
                commentBody.append(adminResponse);
            }

            /** LAST EDIT **/
            if (comment.lastEdit)
                this.addEditedByOrDeletedByText(commentBody, LANG.lvcomment_lastedit, comment.lastEdit[2], comment.lastEdit[0], comment.lastEdit[1]);

            /** DELETION **/
            if (comment.deletedInfo)
                this.addEditedByOrDeletedByText(commentBody, LANG.lvcomment_deletedby, comment.deletedInfo[1], comment.deletedInfo[0], null);

            /**  THIS IS USED FOR OBSOLETE CODE **/
            comment.divBody = commentBody[0];

            if (comment.deleted || comment.rating < -4) {
                commentBody.hide();
            }
        },

        /* Used for both last edit and deleted by messages since the only thing that changes is the `text`, given as argument.. */
        addEditedByOrDeletedByText: function(commentBody, text, username, when, count)
        {
            var editedOn = new Date(when);
            var elapsed = (g_serverTime - editedOn) / 1000;

            count = count > 1 ? LANG.dash + $WH.sprintf(LANG.lvcomment_nedits, count) : '';

            var lastEdit = $('<div></div>');
            lastEdit.addClass('comment-lastedit');
            lastEdit.html($WH.sprintf('$1 <a></a> $2 $3$4', text, g_formatDate(null, elapsed, editedOn), $WH.sprintf(LANG.lvcomment_patch, g_getPatchVersion(editedOn)), count));

            /* Fix up last editor name */
            lastEdit.find('a').text(username).attr('href', '?user=' + username);

            commentBody.append(lastEdit);
        },

        updateVoteCell: function(comment, onInit)
        {
            var upvoteControl = comment.voteCell.find('.upvote');
            var downvoteControl = comment.voteCell.find('.downvote');
            var ratingContainer = comment.voteCell.find('.rating-container');
            var ratingText = $('<p class="rating"></p>');
            var template = this;
            ratingContainer.html(ratingText);

            /* Set up the vote cell */
            if (comment.userRating > 0)
                upvoteControl.attr('data-hasvoted', 'true').attr('title', LANG.upvoted_tip);
            else
                upvoteControl.attr('data-hasvoted', 'false').attr('title', LANG.upvote_tip);

            if (comment.userRating < 0)
                downvoteControl.attr('data-hasvoted', 'true').attr('title', LANG.downvoted_tip);
            else
                downvoteControl.attr('data-hasvoted', 'false').attr('title', LANG.downvote_tip);


            g_addTooltip(upvoteControl[0], LANG.tooltip_uprate, 'q2');
            g_addTooltip(downvoteControl[0], LANG.tooltip_downrate, 'q10');


            /* Clean up (unbind click events; this function can be called several times) */
            ratingText.unbind();
            upvoteControl.unbind();
            downvoteControl.unbind();

            /* Bind events */
            ratingText.text(comment.rating);
            ratingText.click(function() { template.showVoteBreakdown(comment, ratingContainer); });

            upvoteControl.click(function() { template.vote(comment, 1); });
            downvoteControl.click(function() { template.vote(comment, -1); });

            if ((comment.deleted || comment.rating < -4) && onInit) {
                comment.voteCell.hide();
            }
        },

        vote: function(comment, rating)
        {
            /****************************/
            /*** BASIC ERROR CHECKING ***/
            /****************************/
            if (!rating)
                return;

            if (!g_user.id)
            {
                this.voteError(comment, LANG.votelogin_tip);
                return;
            }

            if (comment.deleted)
            {
                this.voteError(comment, LANG.votedeleted_tip);
                return;
            }

            if (g_user.name == comment.user)
            {
                this.voteError(comment, LANG.voteself_tip);
                return;
            }

            if (!g_user.canUpvote && rating > 0)
            {
                this.voteError(comment, $WH.sprintf(LANG.upvotenorep_tip, g_user.upvoteRep));
                return;
            }

            if (!g_user.canDownvote && rating < 0)
            {
                this.voteError(comment, $WH.sprintf(LANG.downvotenorep_tip, g_user.downvoteRep));
                return;
            }

            /**********************/
            /*** VARIABLE SETUP ***/
            /**********************/
            if (rating > 0)
                rating = g_user.superCommentVotes ? 2 : 1;
            else if (rating < 0)
                rating = g_user.superCommentVotes ? -2 : -1;
            else
                return;

            /*************************/
            /*** CHANGE RATING NOW ***/
            /*************************/
            var backupCommentRating = comment.rating;
            var backupUserRating = comment.userRating;
            var template = this;
            /* Case: Rescinding previous vote */
            if ((comment.userRating > 0 && rating > 0) || (comment.userRating < 0 && rating < 0))
            {
                comment.rating -= comment.userRating;
                comment.userRating = 0;
            }
            /* Case: "Inverting" vote (from downvote to upvote, from upvote to downvote) or simply casting a vote when there was none before */
            else
            {
                comment.rating -= comment.userRating;
                comment.rating += rating;
                comment.userRating = rating;
            }

            this.updateVoteCell(comment);

            /**********************/
            /*** SERVER REQUEST ***/
            /**********************/
            $.get('?comment=vote', { id: comment.id, rating: rating }, function(response) {
                if (response.error)
                {
                    /* We have to undo the change we have done above */
                    comment.rating = backupCommentRating;
                    comment.userRating = backupUserRating;
                    template.updateVoteCell(comment);
                }

                if (response.message)
                    template.voteError(comment, response.message);
            }, 'json');
        },

        voteError: function(comment, error)
        {
            MessageBox(comment.voteCell, error);
        },

        showVoteBreakdown: function(comment, ratingText)
        {
            /* Show ajax icon */
            ratingText.find('p').html('<img src="' + g_staticUrl + '/images/icons/ajax.gif" />');

            $.get('?comment=rating&id=' + comment.id, null, function(data) {
                if (data.success)
                    ratingText.html($WH.sprintf('<p class="rating-up">$1</p><div class="rating-separator"></div><p class="rating-down">$2</p>', (data.up ? ('+' + data.up) : 0), -data.down));
                else
                    MessageBox(ratingText, 'An error has occurred while fetching vote counts. Try refreshing the page.');
            }, 'json');
        },

        sticky: function(comment)
        {
            var notificationDiv = comment.commentCell.find('.comment-notification');

            comment.sticky = !comment.sticky;
            this.updateStickyStatus(comment);
            this.updateCommentCell(comment);

            if (comment.sticky)
                MessageBox(notificationDiv, 'This comment is now sticky.');
            else
                MessageBox(notificationDiv, 'This comment is no longer sticky.');

            $.post('?comment=sticky', { id: comment.id, sticky: comment.sticky ? 1 : 0 });
        },

        createNote: function(div) {
            var sm = $WH.ce('small');

            if (!g_user.commentban) {
                var a = $WH.ce('a');
                if (g_user.id > 0) {
                    a.href = 'javascript:;';
                    a.onclick = co_addYourComment;
                }
                else {
                    a.href = '?account=signin';
                }
                $WH.ae(a, $WH.ct(LANG.lvcomment_add));
                $WH.ae(sm, a);

                var sp = $WH.ce('span');
                sp.style.padding = '0 5px';
                sp.style.color = 'white';
                $WH.ae(sp, $WH.ct('|'));
                $WH.ae(sm, sp);
            }

            $WH.ae(sm, $WH.ct(LANG.sort + LANG.colon));

            var aSort1 = $WH.ce('a');
            aSort1.href = 'javascript:;';
            $WH.ae(aSort1, $WH.ct(LANG.newestfirst_stc));
            aSort1.onclick = Listview.funcBox.coSortNewestFirst.bind(this, aSort1);
            $WH.ae(sm, aSort1);

            $WH.ae(sm, $WH.ct(LANG.comma));

            var aSort2 = $WH.ce('a');
            aSort2.href = 'javascript:;';
            $WH.ae(aSort2, $WH.ct(LANG.oldestfirst_stc));
            aSort2.onclick = Listview.funcBox.coSortOldestFirst.bind(this, aSort2);
            $WH.ae(sm, aSort2);

            $WH.ae(sm, $WH.ct(LANG.comma));

            var aSort3 = $WH.ce('a');
            aSort3.href = 'javascript:;';
            $WH.ae(aSort3, $WH.ct(LANG.highestrated_stc));
            aSort3.onclick = Listview.funcBox.coSortHighestRatedFirst.bind(this, aSort3);
            $WH.ae(sm, aSort3)

            var sort = $WH.gc()['comments_sort'];

            switch (sort) {
                case '1':
                    aSort1.onclick();
                    break;
                case '2':
                    aSort2.onclick();
                    break;
                default:
                case '3':
                    aSort3.onclick();
                    break;
            }

            var sp = $WH.ce('span');
            sp.style.padding = '0 5px';
            sp.style.color = 'white';
            $WH.ae(sp, $WH.ct('|'));
            $WH.ae(sm, sp);

            var select = $WH.ce('select');
            var option = $WH.ce('option');
            option.value = 0;
            option.selected = 'selected';
            $WH.ae(select, option);

            var patchesSeen = {};
            for (var d = 0; d < this.data.length; ++d) {
                var postedOn = new Date(this.data[d].date).getTime();
                patchesSeen[g_getPatchVersionIndex(postedOn)] = true;
            }
            var patches = [];
            for (var p in patchesSeen) {
                patches.push(p);
            }
            patches.sort(function(a, b) {
                return b - a;
            });

            for (var p = 0; p < patches.length; ++p) {
                var option = $WH.ce('option');
                option.value = patches[p];
                $WH.ae(option, $WH.ct(g_getPatchVersion.V[patches[p]]));
                $WH.ae(select, option);
            }
            select.onchange = Listview.funcBox.coFilterByPatchVersion.bind(this, select);
            $WH.ae(sm, $WH.ct(LANG.lvcomment_patchfilter));
            $WH.ae(sm, select);

            $WH.ae(div, sm);

            if (this.tabClick) {
                $('a, select', sm).click(this.tabClick);
            }
        },

        onNoData: function(div) {
            var s = '<b>' + LANG.lvnodata_co1 + '</b><div class="pad2"></div>';

            if (g_user.id > 0) {
                var foo = LANG.lvnodata_co2;
                foo = foo.replace('<a>', '<a href="javascript:;" onclick="co_addYourComment()" onmousedown="return false">');
                s += foo;
            }
            else {
                var foo = LANG.lvnodata_co3;
                foo = foo.replace('<a>', '<a href="?account=signin">');
                foo = foo.replace('<a>', '<a href="?account=signup">');
                s += foo;
            }

            div.style.padding = '1.5em 0';
            div.innerHTML = s;
        },

        onBeforeCreate: function() {
            if (location.hash && location.hash.match(/:id=([0-9]+)/) != null) {
                var sort = $WH.gc()['comments_sort'];

                switch (sort) {
                    case '1':
                        this.setSort([-5, 4, -1, -2], false, false);
                        break;
                    case '2':
                        this.setSort([-5, 4, 1, 2], false, false);
                        break;
                    default:
                    case '3':
                        this.setSort([-5, 4, -3, 2], false, false);
                        break;
                }

                var rowNo = $WH.in_array(this.data, parseInt(RegExp.$1), function(x) {
                    return x.id;
                });
                this.rowOffset = this.getRowOffset(rowNo);
                return this.data[rowNo];
            }
        },

        onAfterCreate: function(result) {
            if (result != null) {
                var div = result.__div;
                this.tabs.__st = div;
            }
        }
    },

    commentpreview: {
        sort: [4],
        nItemsPerPage: 75,

        columns: [
            {
                id: 'subject',
                name: LANG.subject,
                align: 'left',
                value: 'subject',
                compute: function(comment, td) {
                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(comment);
                    $WH.ae(a, $WH.ct(comment.subject));
                    $WH.ae(td, a);

                    if (LANG.types[comment.type]) {
                        var d = $WH.ce('div');
                        d.className = 'small';
                        $WH.ae(d, $WH.ct(LANG.types[comment.type][0]));
                        $WH.ae(td, d);
                    }
                }
            },
            {
                id: 'preview',
                name: LANG.preview,
                align: 'left',
                width: '50%',
                value: 'preview',
                compute: function(comment, td, tr) {
                    var d = $WH.ce('div');
                    d.className = 'crop';
                    if (comment.rating >= 10) {
                        d.className += ' comment-green';
                    }

                    $WH.ae(d, $WH.ct(
                        Markup.removeTags(comment.preview, {mode: Markup.MODE_ARTICLE})
                    ));
                    $WH.ae(td, d);

                    if (comment.rating !== null || comment.deleted) {
                        d = $WH.ce('div');
                        d.className = 'small3';

                        if (comment.rating) {
                            $WH.ae(d, $WH.ct(LANG.lvcomment_rating + (comment.rating > 0 ? '+' : '') + comment.rating));
                        }

                        var
                            s   = $WH.ce('span'),
                            foo = '';

                        s.className = 'q10';
                        if (comment.deleted) {
                            foo = LANG.lvcomment_deleted;
                        }

                        $WH.ae(s, $WH.ct(foo));
                        $WH.ae(d, s);

                        tr.__status = s;

                        $WH.ae(td, d);
                    }
                }
            },
            {
                id: 'author',
                name: LANG.author,
                value: 'user',
                compute: function(comment, td) {
                    td.className = 'q1';

                    var a = $WH.ce('a');
                    a.href = '?user=' + comment.user;
                    $WH.ae(a, $WH.ct(comment.user));
                    $WH.ae(td, a);
                }
            },
            {
                id: 'posted',
                name: LANG.posted,
                width: '16%',
                value: 'elapsed',
                compute: function(comment, td) {
                    var
                        postedOn = new Date(comment.date),
                        elapsed  = (g_serverTime - postedOn) / 1000;

                        var s = $WH.ce('span');
                    g_formatDate(s, elapsed, postedOn, 0, 1);
                    $WH.ae(td, s);
                }
            }
        ],

        getItemLink: function(comment) {
            return '?' + g_types[comment.type] + '=' + comment.typeId + (comment.id != null ? '#comments:id=' + comment.id : '')
        }
    },

    replypreview: {
        sort: [4],
        nItemsPerPage: 75,

        columns: [
            {
                id: 'subject',
                name: LANG.subject,
                align: 'left',
                value: 'subject',
                compute: function(reply, td)
                {
                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(reply);
                    $WH.ae(a, $WH.ct(reply.subject));
                    $WH.ae(td, a);

                    if (LANG.types[reply.type])
                    {
                        var d = $WH.ce('div');
                        d.className = 'small';
                        $WH.ae(d, $WH.ct(LANG.types[reply.type][0]));
                        $WH.ae(td, d);
                    }
                }
            },
            {
                id: 'preview',
                name: LANG.preview,
                align: 'left',
                width: '50%',
                value: 'preview',
                compute: function(reply, td, tr)
                {
                    var d = $WH.ce('div');
                    d.className = 'crop';
                    if (reply.rating >= 10)
                        d.className += ' comment-green';

                    $WH.ae(d, $WH.ct(   Markup.removeTags(reply.preview, {mode: Markup.MODE_ARTICLE})  ));
                    $WH.ae(td, d);

                    if (reply.rating)
                    {
                        d = $WH.ce('div');
                        d.className = 'small3';

                        $WH.ae(d, $WH.ct(LANG.lvcomment_rating + (reply.rating > 0 ? '+' : '') + reply.rating));

                        var s = $WH.ce('span'), foo = '';
                        s.className = 'q10';
                        $WH.ae(s, $WH.ct(foo));
                        $WH.ae(d, s);

                        tr.__status = s;

                        $WH.ae(td, d);
                    }
                }
            },
            {
                id: 'author',
                name: LANG.author,
                value: 'user',
                compute: function(reply, td)
                {
                    td.className = 'q1';

                    var a = $WH.ce('a');
                    a.href = '?user=' + reply.user;
                    $WH.ae(a, $WH.ct(reply.user))
                    $WH.ae(td, a);
                }
            },
            {
                id: 'posted',
                name: LANG.posted,
                width: '16%',
                value: 'elapsed',
                compute: function(reply, td)
                {
                    var postedOn = new Date(reply.date),
                        elapsed  = (g_serverTime - postedOn) / 1000;

                    var s = $WH.ce('span');
                    g_formatDate(s, elapsed, postedOn, 0, 1);
                    $WH.ae(td, s);
                }
            }
        ],

        getItemLink: function(reply)
        {
            if (reply.url)
                return '?' + reply.url;

            if (!g_types[reply.type])
                return '?go-to-reply&id=' + reply.id;

            return $WH.sprintf('?$1=$2#comments:id=$3:reply=$4', g_types[reply.type], reply.typeId, reply.commentid, reply.id);
        }
    },

    screenshot: {
        sort: [],
        mode: 3, // Grid mode
        nItemsPerPage: 40,
        nItemsPerRow: 4,
        poundable: 2, // Yes but w/o sort

        columns: [],

        compute: function(screenshot, td, i) {
            var
                _,
                postedOn = new Date(screenshot.date),
                elapsed  = (g_serverTime - postedOn) / 1000;

            td.className = 'screenshot-cell';
            td.vAlign = 'bottom';

            var a = $WH.ce('a');
            a.href = '#screenshots:id=' + screenshot.id;
            a.onclick = $WH.rf2;

            var
                img = $WH.ce('img'),
                scale = Math.min(150 / screenshot.width, 150 / screenshot.height);

            img.src = g_staticUrl + '/uploads/screenshots/thumb/' + screenshot.id + '.jpg';
            //img.width  = Math.round(scale * screenshot.width);
            //img.height = Math.round(scale * screenshot.height);
            $WH.ae(a, img);

            $WH.ae(td, a);

            var d = $WH.ce('div');
            d.className = 'screenshot-cell-user';

            var hasUser = (screenshot.user != null && screenshot.user.length);

            if (hasUser) {
                a = $WH.ce('a');
                a.href = '?user=' + screenshot.user;
                $WH.ae(a, $WH.ct(screenshot.user));
                $WH.ae(d, $WH.ct(LANG.lvscreenshot_from));
                $WH.ae(d, a);
                $WH.ae(d, $WH.ct(' '));
            }

            var s = $WH.ce('span');
            if (hasUser) {
                g_formatDate(s, elapsed, postedOn);
            }
            else {
                g_formatDate(s, elapsed, postedOn, 0, 1);
            }
            $WH.ae(d, s);

            $WH.ae(d, $WH.ct(' ' + LANG.dash + ' '));
            var a = $WH.ce('a');
            a.href = 'javascript:;';
            a.onclick = ContactTool.show.bind(ContactTool, {
                mode: 3,
                screenshot: screenshot
            });

            a.className = 'icon-report';

            g_addTooltip(a, LANG.report_tooltip, 'q2');
            $WH.ae(a, $WH.ct(LANG.report));
            $WH.ae(d, a);

            $WH.ae(td, d);

            d = $WH.ce('div');
            d.style.position = 'relative';
            d.style.height = '1em';

            var hasCaption = (screenshot.caption != null && screenshot.caption.length);
            var hasSubject = (screenshot.subject != null && screenshot.subject.length);

            if (hasCaption || hasSubject) {
                var d2 = $WH.ce('div');
                d2.className = 'screenshot-caption';

                if (hasSubject) {
                    var foo1 = $WH.ce('small');
                    $WH.ae(foo1, $WH.ct(LANG.types[screenshot.type][0] + LANG.colon));
                    var foo2 = $WH.ce('a');
                    $WH.ae(foo2, $WH.ct(screenshot.subject));
                    foo2.href = '?' + g_types[screenshot.type] + '=' + screenshot.typeId;
                    $WH.ae(foo1, foo2);
                    $WH.ae(d2, foo1);

                    if (hasCaption && screenshot.caption.length) {
                        $WH.ae(foo1, $WH.ct(' (...)'));
                    }
                    $WH.ae(foo1, $WH.ce('br'));
                }

                if (hasCaption) {
                    $WH.aE(td, 'mouseover', Listview.funcBox.ssCellOver.bind(d2));
                    $WH.aE(td, 'mouseout', Listview.funcBox.ssCellOut.bind(d2));

                    var foo = $WH.ce('span');
                    foo.innerHTML = Markup.toHtml(screenshot.caption, {
                        mode: Markup.MODE_SIGNATURE
                    });
                    $WH.ae(d2, foo);
                }

                $WH.ae(d, d2);
            }

            $WH.aE(td, 'click', Listview.funcBox.ssCellClick.bind(this, i));

            $WH.ae(td, d);
        },

        createNote: function(div) {
            if (typeof g_pageInfo == 'object' && g_pageInfo.type > 0) {
                var sm = $WH.ce('small');

                var a = $WH.ce('a');
                if (g_user.id > 0) {
                    a.href = 'javascript:;';
                    a.onclick = ss_submitAScreenshot;
                }
                else {
                    a.href = '?account=signin';
                }
                $WH.ae(a, $WH.ct(LANG.lvscreenshot_submit));
                $WH.ae(sm, a);

                $WH.ae(div, sm);
            }
        },

        onNoData: function(div) {
            if (typeof g_pageInfo == 'object' && g_pageInfo.type > 0) {
                var s = '<b>' + LANG.lvnodata_ss1 + '</b><div class="pad2"></div>';

                if (g_user.id > 0) {
                    var foo = LANG.lvnodata_ss2;
                    foo = foo.replace('<a>', '<a href="javascript:;" onclick="ss_submitAScreenshot()" onmousedown="return false">');
                    s += foo;
                }
                else {
                    var foo = LANG.lvnodata_ss3;
                    foo = foo.replace('<a>', '<a href="?account=signin">');
                    foo = foo.replace('<a>', '<a href="?account=signup">');
                    s += foo;
                }

                div.style.padding = '1.5em 0';
                div.innerHTML = s;
            }
            else {
                return -1;
            }
        },

        onBeforeCreate: function() {
            if (location.hash && location.hash.match(/:id=([0-9]+)/) != null) {
                var rowNo = $WH.in_array(this.data, parseInt(RegExp.$1), function(x) {
                    return x.id;
                });
                this.rowOffset = this.getRowOffset(rowNo);
                return rowNo;
            }
        },

        onAfterCreate: function(rowNo) {
            if (rowNo != null) {
                setTimeout((function() {
                    ScreenshotViewer.show({
                        screenshots: this.data,
                        pos: rowNo
                    })
                }).bind(this), 1);
            }
        }
    },

    sound: {
        searchable: 1,
        filtrable: 1,
        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                value: 'name',
                compute: function (sound, td) {
                    var a = $WH.ce('a');
                    a.className = 'listview-cleartext';
                    a.href = '?sound=' + sound.id;
                    $WH.st(a, sound.name);
                    $WH.ae(td, a);
                }
            },
            {
                id: 'type',
                name: LANG.type,
                type: 'text',
                compute: function (sound, td) {
                    var type = '';
                    if (!this.hasOwnProperty('soundtypecache')) {
                        var cache = {};
                        for (var i in mn_sounds)
                            if (mn_sounds[i][0] < 1000)
                                cache[mn_sounds[i][0]] = mn_sounds[i][1];

                        this.soundtypecache = cache;
                    }

                    if (this.soundtypecache.hasOwnProperty(sound.type))
                        type = this.soundtypecache[sound.type];

                    $WH.st(td, type);
                },
                sortFunc: function (a, b) {
                    if (!this.hasOwnProperty('soundtypecache')) {
                        var cache = {};
                        for (var i in mn_sounds)
                            if (mn_sounds[i][0] < 1000)
                                cache[mn_sounds[i][0]] = mn_sounds[i][1];

                        this.soundtypecache = cache;
                    }

                    var aType = (this.soundtypecache.hasOwnProperty(a.type)) ? this.soundtypecache[a.type] : '';
                    var bType = (this.soundtypecache.hasOwnProperty(b.type)) ? this.soundtypecache[b.type] : '';

                    return $WH.strcmp(aType, bType) || $WH.strcmp(a.name, b.name);
                }
            },
            {
                id: 'activity',
                name: LANG.activity,
                hidden: true,
                type: 'text',
                compute: function (sound, td) {
                    if (!sound.hasOwnProperty('activity'))
                        return '';

                    if (LANG.sound_activities.hasOwnProperty(sound.activity))
                        return LANG.sound_activities[sound.activity];
                    else
                        return sound.activity;
                },
                sortFunc: function (a, b) {
                    return $WH.strcmp(this.compute(a), this.compute(b));
                }
            },
            {
                id: 'sound',
                name: LANG.types[19][2],
                span: 2,
                compute: function (sound, td, tr) {
                    var td2 = $WH.ce('td');
                    td2.className = 'nowrap';
                    $WH.ae(tr, td2);

                    td2.style.borderRight = 'none';
                    td.className = 'nowrap';
                    td.style.borderLeft = 'none';

                    var div = $WH.ce('div');
                    $WH.ae(td2, div);

                    (new AudioControls()).init(sound.files, div, { listview: this, trackdisplay: td });
                }
            }
        ],

        getItemLink: function (sound) {
            return '?sound=' + sound.id;
        }
    },

    video: {
        sort: [],
        mode: 3, // Grid mode
        nItemsPerPage: 40,
        nItemsPerRow: 4,
        poundable: 2, // Yes but w/o sort

        columns: [],

        compute: function(video, td, i) {
            var
                _,
                postedOn = new Date(video.date),
                elapsed = (g_serverTime - postedOn) / 1000;

                td.className = 'screenshot-cell';
            td.vAlign = 'bottom';

            var a = $WH.ce('a');
            a.href = '#videos:id=' + video.id;
            a.onclick = $WH.rf2;

            var img = $WH.ce('img');
            img.src = $WH.sprintf(vi_thumbnails[video.videoType], video.videoId);
            $WH.ae(a, img);

            $WH.ae(td, a);

            var d = $WH.ce('div');
            d.className = 'screenshot-cell-user';

            var hasUser = (video.user != null && video.user.length);

            if (hasUser) {
                a = $WH.ce('a');
                a.href = '?user=' + video.user;
                $WH.ae(a, $WH.ct(video.user));
                $WH.ae(d, $WH.ct(LANG.lvvideo_from));
                $WH.ae(d, a);
                $WH.ae(d, $WH.ct(' '));
            }

            var s = $WH.ce('span');
            if (hasUser) {
                g_formatDate(s, elapsed, postedOn);
            }
            else {
                g_formatDate(s, elapsed, postedOn, 0, 1);
            }
            $WH.ae(d, s);

            $WH.ae(d, $WH.ct(' ' + LANG.dash + ' '));
            var a = $WH.ce('a');
            a.href = 'javascript:;';
            a.onclick = ContactTool.show.bind(ContactTool, {mode: 5, video: video});
            a.className = 'icon-report';
            g_addTooltip(a, LANG.report_tooltip, 'q2');
            $WH.ae(a, $WH.ct(LANG.report));
            $WH.ae(d, a);

            $WH.ae(td, d);

            d = $WH.ce('div');
            d.style.position = 'relative';
            d.style.height = '1em';
            var hasCaption = (video.caption != null && video.caption.length);
            var hasSubject = (video.subject != null && video.subject.length);

            if (hasCaption || hasSubject) {
                var d2 = $WH.ce('div');
                d2.className = 'screenshot-caption';

                if (hasSubject) {
                    var foo1 = $WH.ce('small');
                    $WH.ae(foo1, $WH.ct(LANG.types[video.type][0] + LANG.colon));
                    var foo2 = $WH.ce('a');
                    $WH.ae(foo2, $WH.ct(video.subject));
                    foo2.href = '?' + g_types[video.type] + '=' + video.typeId;
                    $WH.ae(foo1, foo2);
                    $WH.ae(d2, foo1);

                    if (hasCaption && video.caption.length) {
                        $WH.ae(foo1, $WH.ct(' (...)'));
                    }
                    $WH.ae(foo1, $WH.ce('br'));
                }

                if (hasCaption) {
                    $WH.aE(td, 'mouseover', Listview.funcBox.ssCellOver.bind(d2));
                    $WH.aE(td, 'mouseout', Listview.funcBox.ssCellOut.bind(d2));

                    var foo = $WH.ce('span');
                    foo.innerHTML = Markup.toHtml(video.caption, {
                        mode: Markup.MODE_SIGNATURE
                    });
                    $WH.ae(d2, foo);
                }

                $WH.ae(d, d2);
            }

            $WH.aE(td, 'click', Listview.funcBox.viCellClick.bind(this, i));

            $WH.ae(td, d);
        },

        createNote: function(div) {
            if (g_user && g_user.roles & (U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO)) {
                if (typeof g_pageInfo == 'object' && g_pageInfo.type > 0) {
                    var sm = $WH.ce('small');

                    var a = $WH.ce('a');
                    if (g_user.id > 0) {
                        a.href = 'javascript:;';
                        a.onclick = vi_submitAVideo;
                    }
                    else {
                        a.href = '?account=signin';
                    }

                    $WH.ae(a, $WH.ct(LANG.lvvideo_suggest));
                    $WH.ae(sm, a);

                    $WH.ae(div, sm);
                }
            }
        },

        onNoData: function(div) {
            if (typeof g_pageInfo == 'object' && g_pageInfo.type > 0) {
                var s = '<b>' + LANG.lvnodata_vi1 + '</b><div class="pad2"></div>';

                if (g_user.id > 0) {
                    var foo = LANG.lvnodata_vi2;
                    foo = foo.replace('<a>', '<a href="javascript:;" onclick="vi_submitAVideo()" onmousedown="return false">');
                    s += foo;
                }
                else {
                    var foo = LANG.lvnodata_vi3;
                    foo = foo.replace('<a>', '<a href="?account=signin">');
                    foo = foo.replace('<a>', '<a href="?account=signup">');
                    s += foo;
                }

                div.style.padding = '1.5em 0';
                div.innerHTML = s;
            }
            else {
                return -1;
            }
        },

        onBeforeCreate: function() {
            if (location.hash && location.hash.match(/:id=([0-9]+)/) != null) {
                var rowNo = $WH.in_array(this.data, parseInt(RegExp.$1), function(x) {
                    return x.id;
                });
                this.rowOffset = this.getRowOffset(rowNo);
                return rowNo;
            }
        },

        onAfterCreate: function(rowNo) {
            if (rowNo != null) {
                setTimeout((function() {
                    VideoViewer.show({
                        videos: this.data,
                        pos: rowNo
                    })
                }).bind(this), 1);
            }
        }
    },

    pet: {
        sort: [1],
        nItemsPerPage: -1,
        searchable: 1,
        filtrable: 1,

        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                value: 'name',
                span: 2,
                compute: function(pet, td, tr) {
                    var i = $WH.ce('td');
                    i.style.width = '1px';
                    i.style.padding = '0';
                    i.style.borderRight = 'none';

                    $WH.ae(i, Icon.create(pet.icon, 0));
                    $WH.ae(tr, i);
                    td.style.borderLeft = 'none';

                    var wrapper = $WH.ce('div');

                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(pet);
                    $WH.ae(a, $WH.ct(pet.name));

                    if (pet.expansion) {
                        var sp = $WH.ce('span');
                        sp.className = g_GetExpansionClassName(pet.expansion);
                        $WH.ae(sp, a);
                        $WH.ae(wrapper, sp);
                    }
                    else {
                        $WH.ae(wrapper, a);
                    }

                    if (pet.exotic) {
                        wrapper.style.position = 'relative';
                        var d = $WH.ce('div');
                        d.className = 'small q1';
                        d.style.fontStyle = 'italic';
                        d.style.position = 'absolute';
                        d.style.right = '3px';
                        d.style.bottom = '0px';
                        var a = $WH.ce('a');
                        a.href = '?spell=53270';
                        $WH.ae(a, $WH.ct(LANG.lvpet_exotic));
                        $WH.ae(d, a);
                        $WH.ae(wrapper, d);
                    }
                    $WH.ae(td, wrapper);
                },
                getVisibleText: function(pet) {
                    var buff = pet.name + Listview.funcBox.getExpansionText(pet);

                    if (pet.exotic) {
                        buff += ' ' + LANG.lvpet_exotic;
                    }

                    return buff;
                }
            },
            {
                id: 'level',
                name: LANG.level,
                type: 'range',
                getMinValue: function(pet) {
                    return pet.minlevel;
                },
                getMaxValue: function(pet) {
                    return pet.maxlevel;
                },
                compute: function(pet, td) {
                    if (pet.minlevel > 0 && pet.maxlevel > 0) {
                        if (pet.minlevel != pet.maxlevel) {
                            return pet.minlevel + LANG.hyphen + pet.maxlevel;
                        }
                        else {
                            return pet.minlevel;
                        }
                    }
                    else {
                        return -1;
                    }
                },
                sortFunc: function(a, b, col) {
                    if (col > 0) {
                        return $WH.strcmp(a.minlevel, b.minlevel) || $WH.strcmp(a.maxlevel, b.maxlevel);
                    }
                    else {
                        return $WH.strcmp(a.maxlevel, b.maxlevel) || $WH.strcmp(a.minlevel, b.minlevel);
                    }
                }
            },
            {
                id: 'damage',                                   // essentially unused, but if someone wants to backport classic
                name: LANG.damage,
                value: 'damage',
                hidden: 1,
                compute: function (pet, col) {
                    $WH.ae(col, this.template.getStatPct(pet.damage))
                }
            },
            {
                id: 'armor',                                   // essentially unused, but if someone wants to backport classic
                name: LANG.armor,
                value: 'armor',
                hidden: 1,
                compute: function (pet, col) {
                    $WH.ae(col, this.template.getStatPct(pet.armor))
                }
            },
            {
                id: 'health',                                   // essentially unused, but if someone wants to backport classic
                name: LANG.health,
                value: 'health',
                hidden: 1,
                compute: function (pet, col) {
                    $WH.ae(col, this.template.getStatPct(pet.health))
                }
            },
            {
                id: 'abilities',
                name: LANG.abilities,
                type: 'text',
                getValue: function(pet) {
                    if (!pet.spells) {
                        return '';
                    }

                    if (pet.spells.length > 0) {
                        var spells = '';
                        for (var i = 0, len = pet.spells.length; i < len; ++i) {
                            if (pet.spells[i]) {
                                spells += g_spells[pet.spells[i]]['name_' + Locale.getName()];
                            }
                        }
                        return spells;
                    }
                },
                compute: function(pet, td) {
                    if (!pet.spells) {
                        return '';
                    }

                    if (pet.spells.length > 0) {
                        td.style.padding = '0';
                        Listview.funcBox.createCenteredIcons(pet.spells, td, '', 1);
                    }
                },
                sortFunc: function(a, b) {
                    if (!a.spells || !b.spells) {
                        return 0;
                    }

                    return $WH.strcmp(a.spellCount, b.spellCount) || $WH.strcmp(a.spells, b.spells);
                },
                hidden: true
            },
            {
                id: 'diet',
                name: LANG.diet,
                type: 'text',
                compute: function(pet, td) {
                    if (td) {
                        td.className = 'small';
                    }

                    var
                        i = 0,
                        foods = '';

                    for (var food in g_pet_foods) {
                        if (pet.diet & food) {
                            if (i++ > 0) {
                                foods += LANG.comma;
                            }
                            foods += g_pet_foods[food];
                        }
                    }
                    return foods;
                },
                sortFunc: function(a, b) {
                    return $WH.strcmp(b.foodCount, a.foodCount) || Listview.funcBox.assocArrCmp(a.diet, b.diet, g_pet_foods);
                }
            },
            {
                id: 'type',
                name: LANG.type,
                type: 'text',
                compute: function(pet, td) {
                    if (pet.type != null) {
                        td.className = 'small q1';
                        var a = $WH.ce('a');
                        a.href = '?pets=' + pet.type;
                        $WH.ae(a, $WH.ct(g_pet_types[pet.type]));
                        $WH.ae(td, a);
                    }
                },
                getVisibleText: function(pet) {
                    if (pet.type != null) {
                        return g_pet_types[pet.type];
                    }
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_pet_types[a.type], g_pet_types[b.type]);
                }
            }
        ],

        getItemLink: function(pet) {
            return '?pet=' + pet.id;
        },

        getStatPct: function(modifier) {
            var _ = $WH.ce('span');
            if (!isNaN(modifier) && modifier > 0) {
                _.className = 'q2';
                $WH.ae(_, $WH.ct('+' + modifier + '%'));
            }
            else if (!isNaN(modifier) && modifier < 0) {
                _.className = 'q10';
                $WH.ae(_, $WH.ct(modifier + '%'));
            }

            return _;
        }
    },

    achievement: {
        sort: [1, 2],
        nItemsPerPage: 100,
        searchable: 1,
        filtrable: 1,
        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                value: 'name',
                span: 2,
                compute: function(achievement, td, tr) {
                    var rel = null;
                    if (achievement.who && achievement.completed) {
                        rel = 'who=' + achievement.who + '&when=' + achievement.completed.getTime();
                    }

                    var i = $WH.ce('td');
                    i.style.width = '1px';
                    i.style.padding = '0';
                    i.style.borderRight = 'none';

                    $WH.ae(i, g_achievements.createIcon(achievement.id, 1));

                    Icon.getLink(i.firstChild).href = this.getItemLink(achievement);
                    Icon.getLink(i.firstChild).rel = rel;

                    $WH.ae(tr, i);
                    td.style.borderLeft = 'none';

                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(achievement);
                    a.rel = rel;
                    $WH.ae(a, $WH.ct(achievement.name));
                    $WH.ae(td, a);

                    if (achievement.description != null) {
                        var d = $WH.ce('div');
                        d.className = 'small';
                        $WH.ae(d, $WH.ct(achievement.description));
                        $WH.ae(td, d);
                    }
                },
                getVisibleText: function(achievement) {
                    var buff = achievement.name;
                    if (achievement.description) {
                        buff += ' ' + achievement.description;
                    }
                    return buff;
                }
            },
            {
                id: 'location',
                name: LANG.location,
                type: 'text',
                width: '15%',
                hidden: 1,
                compute: function (achievement, td) {
                    if (achievement.zone) {
                        var a = $WH.ce('a');
                        a.className = 'q1';
                        a.href = '?zones=' + achievement.zone;
                        $WH.ae(a, $WH.ct(g_zones[achievement.zone]));
                        $WH.ae(td, a);
                    }
                },
                getVisibleText: function (achievement) {
                    return Listview.funcBox.arrayText(achievement.zone, g_zones);
                },
                sortFunc: function (a, b, col) {
                    return Listview.funcBox.assocArrCmp(a.zone, b.zone, g_zones);
                }
            },
            {
                id: 'side',
                name: LANG.side,
                type: 'text',
                compute: function(achievement, td) {
                    if (achievement.side && achievement.side != 3) {
                        var sp = $WH.ce('span');
                        sp.className = (achievement.side == 1 ? 'icon-alliance' : 'icon-horde');
                        g_addTooltip(sp, g_sides[achievement.side]);

                        $WH.ae(td, sp);
                    }
                },
                getVisibleText: function(achievement) {
                    if (achievement.side) {
                        return g_sides[achievement.side];
                    }
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_sides[a.side], g_sides[b.side]);
                }
            },
            {
                id: 'points',
                name: LANG.points,
                type: 'number',
                width: '10%',
                value: 'points',
                compute: function(achievement, td) {
                    if (achievement.points) {
                        Listview.funcBox.appendMoney(td, 0, null, 0, 0, achievement.points);
                    }
                }
            },
            {
                id: 'rewards',
                name: LANG.rewards,
                type: 'text',
                width: '20%',
                compute: function(achievement, td) {
                    if (achievement.rewards) {
                        var itemrewards = [];
                        var spellrewards = []; // not used in 3.3.5
                        var titlerewards = [];
                        for (var i = 0; i < achievement.rewards.length; i++) {
                            if (achievement.rewards[i][0] == 11) {
                                titlerewards.push(achievement.rewards[i][1]);
                            }
                            else  if (achievement.rewards[i][0] == 3) {
                                itemrewards.push(achievement.rewards[i][1]);
                            }
                            else if (achievement.rewards[i][0] == 6) {
                                spellrewards.push(achievement.rewards[i][1]);
                            }
                        }
                        if (itemrewards.length > 0) {
                            for (var i = 0; i < itemrewards.length; i++) {
                                if (!g_items[itemrewards[i]]) {
                                    return;
                                }

                                var item = g_items[itemrewards[i]];
                                var a = $WH.ce('a');
                                a.href = '?item=' + itemrewards[i];
                                a.className = 'q' + item.quality + ' icontiny tinyspecial';
                                a.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + item.icon.toLowerCase() + '.gif)';
                                $WH.ae(a, $WH.ct(item['name_' + Locale.getName()]));
                                var span = $WH.ce('span');
                                $WH.ae(span, a);
                                $WH.ae(td, span);
                                $WH.ae(td, $WH.ce('br'));
                            }
                        }
                        if (spellrewards.length > 0) {
                            for (var i = 0; i < spellrewards.length; i++) {
                                if (!g_spells[spellrewards[i]]) {
                                    return;
                                }

                                var item = g_spells[spellrewards[i]];
                                var a = $WH.ce('a');
                                a.href = '?spell=' + spellrewards[i];
                                a.className = 'q8 icontiny tinyspecial';
                                a.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + item.icon.toLowerCase() + '.gif)';
                                $WH.ae(a, $WH.ct(item['name_' + Locale.getName()]));
                                var span = $WH.ce('span');
                                $WH.ae(span, a);
                                $WH.ae(td, span);
                                $WH.ae(td, $WH.ce('br'));
                            }
                        }
                        if (titlerewards.length > 0) {
                            for (var i = 0; i < titlerewards.length; i++) {
                                if (!g_titles[titlerewards[i]]) {
                                    return;
                                }

                                var title = g_titles[titlerewards[i]]['name_' + Locale.getName()];
                                title = title.replace('%s', '<span class="q0">&lt;' + LANG.name + '&gt;</span>');
                                var span = $WH.ce('a');
                                span.className = 'q1';
                                span.href = '?title=' + titlerewards[i];
                                span.innerHTML = title;
                                $WH.ae(td, span);
                                $WH.ae(td, $WH.ce('br'));
                            }
                        }
                    }
                    else if (achievement.reward) {
                        var span = $WH.ce('span');
                        span.className = 'q1';
                        $WH.ae(span, $WH.ct(achievement.reward));
                        $WH.ae(td, span);
                    }
                },
                getVisibleText: function(achievement) {
                    var buff = '';
                    if (achievement.rewards) {
                        for (var i = 0; i < achievement.rewards.length; i++) {
                            if (achievement.rewards[i][0] == 11) {
                                buff += ' ' + g_titles[achievement.rewards[i][1]]['name_' + Locale.getName()].replace('%s', '<' + LANG.name + '>');
                            }
                            else if (achievement.rewards[i][0] == 3) {
                                buff += ' ' + g_items[achievement.rewards[i][1]]['name_' + Locale.getName()];
                            }
                            else if (achievement.rewards[i][0] == 6) {
                                buff += ' ' + g_spells[achievement.rewards[i][1]]['name_' + Locale.getName()];
                            }
                        }
                    }
                    else if (achievement.reward) {
                        buff += ' ' + achievement.reward;
                    }

                    return buff;
                },
                sortFunc: function(a, b, col) {
                    var text1 = this.getVisibleText(a);
                    var text2 = this.getVisibleText(b);

                    if (text1 != '' && text2 == '') {
                        return -1;
                    }
                    if (text2 != '' && text1 == '') {
                        return 1;
                    }

                    return $WH.strcmp(text1, text2);
                }
            },
            {
                id: 'category',
                name: LANG.category,
                type: 'text',
                width: '15%',
                compute: function(achievement, td) {
                    statistic = achievement.type ? achievement.type + '.' : '';
                    td.className = 'small';
                    path = '?achievements=' + statistic;
                    if (achievement.category != -1 && achievement.parentcat != -1) {
                        var a2 = $WH.ce('a');
                        a2.className = 'q0';
                        a2.href = '?achievements=' + statistic + achievement.parentcat;
                        $WH.ae(a2, $WH.ct(g_achievement_categories[achievement.parentcat]));
                        $WH.ae(td, a2);
                        $WH.ae(td, $WH.ce('br'));
                        path = a2.href + '.';
                    }
                    var a = $WH.ce('a');
                    a.className = 'q1';
                    a.href = path + achievement.category;
                    $WH.ae(a, $WH.ct(g_achievement_categories[achievement.category]));
                    $WH.ae(td, a);
                },
                getVisibleText: function(achievement) {
                    return g_achievement_categories[achievement.category];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_achievement_categories[a.category], g_achievement_categories[b.category]);
                },
                hidden: true
            },
            {
                id: 'completed', // Listview.COLUMN_ID_COMPLETION
                name: LANG.completion, // WH.TERMS.completion
                hidden: true,
                compute: function (achievement, td)
                {
                    if (achievement.type)
                        return;

                    $WH.addCompletionIcons(td, 10, achievement.id)
                },
                sortFunc: function (a, b)
                {
                    // return $WH.stringCompare(
                    return $WH.strcmp(
                        $WH.getCompletionFlags(10, a.id),
                        $WH.getCompletionFlags(10, b.id)
                    );
                },
                getValue: function (achievement) {
                    var value = 0;
                    var completionData = g_user.completion?.hasOwnProperty(10) ? g_user.completion[10] : {};

                    if (achievement.type)
                        return -1;

                    for (var i in g_user.characters)
                    {
                        var profile = g_user.characters[i];
                        if (!(profile.id in completionData))
                            continue;

                        if ($WH.in_array(completionData[profile.id], achievement.id) != -1)
                            value++;
                    }

                    return value;
                }
            }
        ],

        getItemLink: function(achievement) {
            return '?achievement=' + achievement.id;
        },

        onBeforeCreate: function () {
            for (var i in this.columns)
            {
                if (this.columns[i].id == 'completed' && this.columns[i].hidden)
                {
                    if ($WH.isset('g_user') && 'characters' in g_user && $WH.in_array(this.hiddenCols, this.columns[i].id) == -1)
                    {
                        var n = 0;
                        for (var j in this.data)
                            if (!this.data[j].type)
                                n++;

                        if (n > this.data.length * 0.1)
                            this.visibility.push(parseInt(i));
                    }
                }
            }
        }
    },

    title: {
        sort: [1],
        nItemsPerPage: -1,
        searchable: 1,
        filtrable: 1,

        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                value: 'name',
                compute: function(title, td, tr) {
                    var
                        sp = $WH.ce('a'),
                        n = $WH.ce('span'),
                        t = $WH.ct($WH.str_replace(title.name, '%s', ''));

                    td.style.fontFamily = 'Verdana, sans-serif';

                    // $WH.nw(td)

                    sp.href = this.getItemLink(title);

                    if (title.who) {
                        $WH.ae(n, $WH.ct(title.who));
                    }
                    else {
                        $WH.ae(n, $WH.ct('<' + LANG.name + '>'));
                        n.className = 'q0';
                    }

                    if (title.name.indexOf('%s') > 0) { // Prefix title
                        $WH.ae(sp, t);
                        $WH.ae(sp, n);
                    }
                    else if (title.name.indexOf('%s') == 0) { // Suffix title
                        $WH.ae(sp, n);
                        $WH.ae(sp, t);
                    }

                    if (title.expansion) {
                        var i = $WH.ce('span');
                        i.className = g_GetExpansionClassName(title.expansion);
                        $WH.ae(i, sp);
                        $WH.ae(td, i);
                    }
                    else {
                        $WH.ae(td, sp);
                    }
                },
                sortFunc: function(a, b, col) {
                    var
                        aName = $WH.trim(a.name.replace('%s', '').replace(/^[\s,]*(,|the |of the |of )/i, ''));
                        bName = $WH.trim(b.name.replace('%s', '').replace(/^[\s,]*(,|the |of the |of )/i, ''));

                    return $WH.strcmp(aName, bName);
                },
                getVisibleText: function(title) {
                    var buff = title.name + Listview.funcBox.getExpansionText(title);

                    return buff;
                }
            },
            {
                id: 'gender',
                name: LANG.gender,
                type: 'text',
                value: 'gender',
                compute: function(title, td) {
                    if (title.gender && title.gender != 3) {
                        var gender = g_file_genders[title.gender - 1];
                        var sp = $WH.ce('span');
                        sp.className = 'icon-' + gender;
                        g_addTooltip(sp, LANG[gender]);

                        $WH.ae(td, sp);
                    }
                },
                getVisibleText: function(title) {
                    if (title.gender && title.gender != 3) {
                        return LANG[g_file_genders[title.gender - 1]];
                    }
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(a.gender, b.gender);
                }
            },
            {
                id: 'side',
                name: LANG.side,
                type: 'text',
                compute: function(title, td) {
                    if (title.side && title.side != 3) {
                        var sp = $WH.ce('span');
                        sp.className = (title.side == 1 ? 'icon-alliance' : 'icon-horde');
                        g_addTooltip(sp, g_sides[title.side]);

                        $WH.ae(td, sp);
                    }
                },
                getVisibleText: function(title) {
                    if (title.side) {
                        return g_sides[title.side];
                    }
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_sides[a.side], g_sides[b.side]);
                }
            },
            {
                id: 'source',
                name: LANG.source,
                type: 'text',
                compute: function(title, td) {
                    if (title.source) {
                        $WH.nw(td);
                        td.className = 'small';
                        td.style.lineHeight = '18px';
                        var n = 0;

                        for (var s in title.source) {
                            title.source[s].sort(function(a, b) {
                                return b.s - a.s;
                            });

                            for (var i = 0, len = title.source[s].length; i < len; ++i) {
                                var sm = title.source[s][i];
                                var type = 0;

                                if (title.faction && typeof sm != 'string' && sm.s !== undefined && sm.s != -1 && sm.s != 2 - title.faction) {
                                    continue;
                                }

                                if (n++ > 0) {
                                    $WH.ae(td, $WH.ce('br'));
                                }

                                if (typeof sm == 'string') {
                                    $WH.ae(td, $WH.ct(sm));
                                }
                                else  if (sm.t) {
                                    type = sm.t;
                                    var a = $WH.ce('a');
                                    // o.style.fontFamily = 'Verdana, sans-serif';
                                    a.href = '?' + g_types[sm.t] + '=' + sm.ti;
                                    a.className = 'q1';

                                    if (sm.s == 1) {
                                        a.className += ' icon-alliance'
                                    }
                                    if (sm.s == 0) {
                                        a.className += ' icon-horde'
                                    }
                                    if (sm.t == 5) { // Quests
                                        a.className += ' icontiny tinyspecial';
                                        a.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/quest_start.gif)';
                                    }

                                    $WH.ae(a, $WH.ct(sm.n));
                                    $WH.ae(td, a);
                                }
                            }
                        }
                    }
                },
                getVisibleText: function(title) {
                    var buff = '';

                    if (title.source) {
                        for (var s in title.source) {
                            for (var i = 0, len = title.source[s].length; i < len; ++i) {
                                var sm = title.source[s][i];
                                if (typeof sm == 'string') {
                                    buff += ' ' + sm;
                                }
                                else if (sm.t){
                                    buff += ' ' + sm.n;
                                }
                            }
                        }
                    }

                    return buff;
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(this.getVisibleText(a), this.getVisibleText(b));
                }
            },
            {
                id: 'category',
                name: LANG.category,
                type: 'text',
                width: '15%',
                compute: function(title, td) {
                    $WH.nw(td);
                    td.className = 'small q1';
                    var a = $WH.ce('a');
                    a.href = '?titles=' + title.category;
                    $WH.ae(a, $WH.ct(g_title_categories[title.category]));
                    $WH.ae(td, a);
                },
                getVisibleText: function(title) {
                    return g_title_categories[title.category];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_title_categories[a.category], g_title_categories[b.category]);
                },
                hidden: true
            },
            {
                id: 'completed', // Listview.COLUMN_ID_COMPLETION
                name: LANG.completion, // WH.TERMS.completion
                hidden: true,
                compute: function (title, td)
                {
                    $WH.addCompletionIcons(td, 11, title.id)
                },
                sortFunc: function (a, b)
                {
                    // return $WH.stringCompare(
                    return $WH.strcmp(
                        $WH.getCompletionFlags(11, a.id),
                        $WH.getCompletionFlags(11, b.id)
                    );
                },
                getValue: function (title) {
                    var value = 0;
                    var completionData = g_user.completion?.hasOwnProperty(11) ? g_user.completion[11] : {};

                    for (var i in g_user.characters)
                    {
                        var profile = g_user.characters[i];
                        if (!(profile.id in completionData))
                            continue;

                        if ($WH.in_array(completionData[profile.id], title.id) != -1)
                            value++;
                    }

                    return value;
                }
            }
        ],

        getItemLink: function(title) {
            return '?title=' + title.id;
        },

        onBeforeCreate: function () {
            for (var i in this.columns)
            {
                if (this.columns[i].id == 'completed' && this.columns[i].hidden)
                {
                    if ($WH.isset('g_user') && 'characters' in g_user && $WH.in_array(this.hiddenCols, this.columns[i].id) == -1)
                        this.visibility.push(parseInt(i));
                }
            }
        }
    },

    profile: {
        sort: [],
        nItemsPerPage: 50,
        searchable: 1,
        filtrable: 1,
        columns: [
            {
                id: 'name',
                name: LANG.name,
                value: 'name',
                type: 'text',
                align: 'left',
                span: 2,
                compute: function(profile, td, tr) {
                    if (profile.level) {
                        var i = $WH.ce('td');
                        i.style.width = '1px';
                        i.style.padding = '0';
                        i.style.borderRight = 'none';

                     // $WH.ae(i, Icon.create($WH.g_getProfileIcon(profile.race, profile.classs, profile.gender, profile.level, profile.icon ? profile.icon : profile.id, 'medium'), 1, null, this.getItemLink(profile)));
                     // aowow - see profile=avatar endpoint for explanation
                        var ic = Icon.create($WH.g_getProfileIcon(profile.race, profile.classs, profile.gender, profile.level, profile.icon ? profile.icon : 0, 'medium'), 1, null, this.getItemLink(profile));
                        // aowow - custom
                        if (profile.captain) {
                            tr.className = 'mergerow';
                            ic.className += ' ' + ic.className + '-gold';
                        }

                        $WH.ae(i, ic);
                        $WH.ae(tr, i);

                        td.style.borderLeft = 'none';
                    }
                    else {
                        td.colSpan = 2;
                    }

                    var wrapper = $WH.ce('div');
                    wrapper.style.position = 'relative';

                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(profile);
                    if (profile.pinned) {
                        a.className = 'icon-star-right';
                    }
                    $WH.ae(a, $WH.ct(profile.name));
                    $WH.ae(wrapper, a);

                    var d = $WH.ce('div');
                    d.className = 'small';
                    d.style.marginRight = '20px';

                    if (profile.guild && typeof(profile.guild) != 'number') {
                        var a = $WH.ce('a');
                        a.className = 'q1';
                        a.href = '?guild=' + profile.region + '.' + profile.realm + '.' + g_urlize(profile.guild, true);
                        $WH.ae(a, $WH.ct(profile.guild));
                        $WH.ae(d, $WH.ct('<'));
                        $WH.ae(d, a);
                        $WH.ae(d, $WH.ct('>'));
                    }
                    else if (profile.description) {
                        $WH.ae(d, $WH.ct(profile.description));
                    }

                    var
                        s = $WH.ce('span'),
                        foo = '';

                    s.className = 'q10';
                    if (profile.deleted) {
                        foo = LANG.lvcomment_deleted;
                    }
                    $WH.ae(s, $WH.ct(foo));
                    $WH.ae(d, s);
                    $WH.ae(wrapper, d);

                    var d = $WH.ce('div');
                    d.className = 'small';
                    d.style.fontStyle = 'italic';
                    d.style.position = 'absolute';
                    d.style.right = '3px';
                    d.style.bottom = '0px';
                    tr.__status = d;

                    if (profile.published === 0) {
                        $WH.ae(d, $WH.ct(LANG.privateprofile));
                    }

                    // aowow custom
                    if (profile.renameItr) {
                        $WH.ae(d, $WH.ce('span', { className: 'q0'}, $WH.ct(LANG.pr_note_pendingrename)));
                    }
                    // end aowow custom

                    $WH.ae(wrapper, d);
                    $WH.ae(td, wrapper);
                },
                getVisibleText: function(profile) {
                    var buff = profile.name;
                    if (profile.guild && typeof(profile.guild) != 'number') {
                        buff += ' ' + profile.guild;
                    }
                    return buff;
                }
            },
            {
                id: 'faction',
                name: LANG.faction,
                type: 'text',
                compute: function(profile, td) {
                    if (!profile.size && profile.members === undefined && !profile.level) {
                        return;
                    }

                    var
                        d = $WH.ce('div'),
                        d2 = $WH.ce('div'),
                        icon;

                    icon = Icon.create('faction_' + g_file_factions[parseInt(profile.faction) + 1], 0);
                    icon.style.cssFloat = icon.style.syleFloat = 'left';
                    g_addTooltip(icon, g_sides[parseInt(profile.faction) + 1]);

                    d.style.margin = '0 auto';
                    d.style.textAlign = 'left';
                    d.style.width = '26px';
                    d2.className = 'clear';

                    $WH.ae(d, icon);
                    $WH.ae(td, d);
                    $WH.ae(td, d2);
                },
                getVisibleText: function(profile) {
                    return g_sides[profile.faction + 1];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(this.getVisibleText(a), this.getVisibleText(b));
                }
            },
            {
                id: 'members',
                name: LANG.members,
                value: 'members',
                hidden: 1
            },
            {
                id: 'size',
                name: 'Size',
                value: 'size',
                hidden: 1
            },
            {
                id: 'rank',
                name: 'Rank',
                value: 'rank',
                hidden: 1
            },
            {
                id: 'race',
                name: LANG.race,
                type: 'text',
                compute: function(profile, td) {
                    if (profile.race) {
                        var
                            d = $WH.ce('div'),
                            d2 = $WH.ce('div'),
                            icon;

                        icon = Icon.create('race_' + g_file_races[profile.race] + '_' + g_file_genders[profile.gender], 0, null, '?race=' + profile.race);
                        icon.style.cssFloat = icon.style.syleFloat = 'left';
                        g_addTooltip(icon, g_chr_races[profile.race]);

                        d.style.margin = '0 auto';
                        d.style.textAlign = 'left';
                        d.style.width = '26px';
                        d2.className = 'clear';

                        $WH.ae(d, icon);
                        $WH.ae(td, d);
                        $WH.ae(td, d2);
                    }
                },
                getVisibleText: function(profile) {
                    return g_file_genders[profile.gender] + ' ' + g_chr_races[profile.race];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_chr_races[a.race], g_chr_races[b.race]);
                },
                hidden: 1
            },
            {
                id: 'classs',
                name: LANG.classs,
                type: 'text',
                compute: function(profile, td) {
                    if (profile.classs) {
                        var
                            d  = $WH.ce('div'),
                            d2 = $WH.ce('div'),
                            icon;

                        icon = Icon.create('class_' + g_file_classes[profile.classs], 0, null, '?class=' + profile.classs);
                        icon.style.cssFloat = icon.style.syleFloat = 'left';

                        g_addTooltip(icon, g_chr_classes[profile.classs], 'c' + profile.classs);

                        d.style.margin = '0 auto';
                        d.style.textAlign = 'left';
                        d.style.width = '26px';
                        d2.className = 'clear';

                        $WH.ae(d, icon);
                        $WH.ae(td, d);
                        $WH.ae(td, d2);
                    }
                    else {
                        return -1;
                    }
                },
                getVisibleText: function(profile) {
                    if (profile.classs) {
                        return g_chr_classes[profile.classs];
                    }
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(this.getVisibleText(a), this.getVisibleText(b));
                },
                hidden: 1
            },
            {
                id: 'level',
                name: LANG.level,
                value: 'level',
                hidden: 1
            },
            {
                id: 'talents',
                name: LANG.talents,
                type: 'text',
                compute: function(profile, td) {
                    if (!profile.level) {
                        return;
                    }

                    var spent = [profile.talenttree1, profile.talenttree2, profile.talenttree3];
                    var specData = pr_getSpecFromTalents(profile.classs, spent);
                    var a = $WH.ce('a');

                    a.className = 'icontiny tinyspecial tip q1';
                    // aowow - a.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + specData.icon.toLowerCase() + '.gif)';
                    a.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + (g_file_specs[profile.classs][specData.id - 1] ?? g_file_specs[specData.id]) + '.gif)';
                    a.rel = 'np';
                    a.href = this.getItemLink(profile) + '#talents';
                    g_addTooltip(a, specData.name);

                    $WH.ae(a, $WH.ct(profile.talenttree1 + ' / ' + profile.talenttree2 + ' / ' + profile.talenttree3));

                    $WH.ae(td, a);
                },
                getVisibleText: function(profile) {
                    if (profile.talenttree1 || profile.talenttree2 || profile.talenttree3) {
                        if (profile.talentspec > 0) {
                            return g_chr_specs[profile.classs][profile.talentspec - 1];
                        }
                        else {
                            return g_chr_specs[0];
                        }
                    }
                    else {
                        return g_chr_specs['-1'];
                    }
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(this.getVisibleText(a), this.getVisibleText(b));
                },
                hidden: 1
            },
            {
                id: 'gearscore',
                name: LANG.gearscore,
                tooltip: LANG.gearscore_real,
                value: 'gearscore',
                compute: function(profile, td)
                {
                    var level = (profile.level ? profile.level : (profile.members !== undefined ? 80 : 0));

                    if (isNaN(profile.gearscore) || !level)
                        return;

                    td.className = 'q' + pr_getGearScoreQuality(level, profile.gearscore, ($WH.in_array([2, 6, 7, 11], profile.classs) != -1));

                    return (profile.gearscore ? $WH.number_format(profile.gearscore) : 0);
                },
                hidden: 1
            },
            {
                id: 'achievementpoints',
                name: LANG.points,
                value: 'achievementpoints',
                tooltip: LANG.tooltip_achievementpoints,
                compute: function(profile, td) {
                    if (profile.achievementpoints) {
                        Listview.funcBox.appendMoney(td, 0, null, 0, 0, profile.achievementpoints);
                    }
                },
                hidden: 1
            },
            {
                id: 'wins',
                name: LANG.wins,
                value: 'wins',
                hidden: 1
            },
            {
                id: 'losses',
                name: LANG.losses,
                compute: function(profile, td) {
                    return profile.games - profile.wins;
                },
                sortFunc: function(a, b, col) {
                    var
                        lossA = a.games - a.wins,
                        lossB = b.games - b.wins;

                    if (lossA > lossB)
                        return 1;
                    if (lossA < lossB)
                        return -1;

                    return 0;
                },
                hidden: 1
            },
            {
                id: 'guildrank',
                name: LANG.guildrank,
                value: 'guildrank',
                compute: function(profile, td) {
                    // >>> aowow - real rank names >>>
                    if (typeof guild_ranks !== "undefined" && guild_ranks[profile.guildrank]) {
                        var sp = $WH.ce('span', null, $WH.ct(guild_ranks[profile.guildrank]));
                        g_addTooltip(sp, $WH.sprintf(LANG.rankno, profile.guildrank));
                        $WH.ae(td, sp);
                    }
                    // if (profile.guildrank > 0) {
                    // <<< aowow - real rank names <<<
                    else if (profile.guildrank > 0) {
                        return $WH.sprintf(LANG.rankno, profile.guildrank);
                    }
                    else if (profile.guildrank == 0) {
                        var b = $WH.ce('b');
                        $WH.ae(b, $WH.ct(LANG.guildleader));
                        $WH.ae(td, b);
                    }
                },
                getVisibleText: function(profile) {
                    if (profile.guildrank > 0) {
                        return $WH.sprintf(LANG.rankno, profile.guildrank);
                    }
                    else if (profile.guildrank == 0) {
                        return LANG.guildleader;
                    }
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp((a.guildrank >= 0 ? a.guildrank : 11), (b.guildrank >= 0 ? b.guildrank : 11));
                },
                hidden: 1
            },
            {
                id: 'rating',
                name: LANG.rating,
                value: 'rating',
                hidden: 1
            },
            {
                id: 'location',
                name: LANG.location,
                type: 'text',
                compute: function(profile, td) {
                    var a;

                    if (profile.region) {
                        if (profile.realm) {
                            a = $WH.ce('a');
                            a.className = 'q1';
                            a.href = '?profiles=' + profile.region + '.' + profile.realm;
                            $WH.ae(a, $WH.ct(profile.realmname));
                            $WH.ae(td, a);
                            $WH.ae(td, $WH.ce('br'));
                        }

                        var s = $WH.ce('small');
                        a = $WH.ce('a');
                        a.className = 'q1';
                        a.href = '?profiles=' + profile.region;
                        $WH.ae(a, $WH.ct(profile.region.toUpperCase()));
                        $WH.ae(s, a);

                        if (profile.battlegroup) {
                            $WH.ae(s, $WH.ct(LANG.hyphen));

                            a = $WH.ce('a');
                            a.className = 'q1';
                            a.href = '?profiles=' + profile.region + '.' + profile.battlegroup;
                            $WH.ae(a, $WH.ct(profile.battlegroupname));
                            $WH.ae(s, a);
                        }

                        $WH.ae(td, s);
                    }
                },
                getVisibleText: function(profile) {
                    var buff = '';
                    if (profile.region) {
                        buff += ' ' + profile.region;
                    }
                    if (profile.battlegroup) {
                        buff += ' ' + profile.battlegroup;
                    }
                    if (profile.realm) {
                        buff += ' ' + profile.realm;
                    }

                    return $WH.trim(buff);
                },
                sortFunc: function(a, b, col) {
                    if (a.region != b.region) {
                        return $WH.strcmp(a.region, b.region);
                    }
                    if (a.battlegroup != b.battlegroup) {
                        return $WH.strcmp(a.battlegroup, b.battlegroup);
                    }
                    return $WH.strcmp(a.realm, b.realm);
                }
            },
            {
                id: 'guild',
                name: LANG.guild,
                value: 'guild',
                type: 'text',
                compute: function(profile, td) {
                    if (!profile.region || !profile.realm || !profile.guild || typeof(profile.guild) == 'number') {
                        return;
                    }

                    var a = $WH.ce('a');
                    a.className = 'q1';
                    a.href = '?guild=' + profile.region + '.' + profile.realm + '.' + g_urlize(profile.guild, true);
                    $WH.ae(a, $WH.ct(profile.guild));
                    $WH.ae(td, a);
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(a.guild, b.guild);
                }
            }
        ],

        getItemLink: function(profile) {
            if (profile.size !== undefined) {
                return '?arena-team=' + profile.region + '.' + profile.realm + '.' + g_urlize(profile.name, true);
            }
            else if (profile.members !== undefined) {
                return '?guild=' + profile.region + '.' + profile.realm + '.' + g_urlize(profile.name, true);
            }
            else {
                return g_getProfileUrl(profile);
            }
        }
    },

    model: {
        sort: [],
        mode: 3, // Grid mode
        nItemsPerPage: 40,
        nItemsPerRow: 4,
        poundable: 2, // Yes but w/o sort

        columns: [],

        compute: function(model, td, i) {
            td.className = 'screenshot-cell';
            td.vAlign = 'bottom';

            var a = $WH.ce('a');
            a.href = 'javascript:;';
            // a.className = 'pet-zoom';   // aowow: keep as reference only
            a.onclick = this.template.modelShow.bind(this.template, model.npcId, model.displayId, false);

            var img = $WH.ce('img');
            img.src = g_staticUrl + '/modelviewer/thumbs/npc/' + model.displayId + '.png';
            $WH.ae(a, img);

            $WH.ae(td, a);

            var d = $WH.ce('div');
            d.className = 'screenshot-cell-user';

            a = $WH.ce('a');
            a.href = '?npcs=1&filter=' + (model.family ? 'fa=' + model.family + ';' : '') + 'minle=1;cr=35;crs=0;crv=' + model.skin;
            $WH.ae(a, $WH.ct(model.skin));
            $WH.ae(d, a);

            $WH.ae(d, $WH.ct(' (' + model.count + ')'));
            $WH.ae(td, d);

            d = $WH.ce('div');
            d.style.position = 'relative';
            d.style.height = '1em';

            var d2 = $WH.ce('div');
            d2.className = 'screenshot-caption';

            var s = $WH.ce('small');
            $WH.ae(s, $WH.ct(LANG.level + ': '));
            $WH.ae(s, $WH.ct((model.minLevel == 9999 ? '??' : model.minLevel) + (model.minLevel == model.maxLevel ? '' : LANG.hyphen + (model.maxLevel == 9999 ? '??' : model.maxLevel))));
            $WH.ae(s, $WH.ce('br'));
            $WH.ae(d2, s);
            $WH.ae(d, d2);
            $WH.ae(td, d);

            $WH.aE(td, 'click', this.template.modelShow.bind(this.template, model.npcId, model.displayId, true));
        },

        modelShow: function(npcId, displayId, sp, e) {
            if (sp) {
                e = $WH.$E(e);

                if (e.shiftKey || e.ctrlKey) {
                    return;
                }

                var
                    j = 0,
                    el = e._target;
                while (el && j < 3) {
                    if (el.nodeName == 'A') {
                        return;
                    }
                    if (el.nodeName == 'IMG') {
                        break;
                    }
                    el = el.parentNode;
                }
            }

            ModelViewer.show({type: 1, typeId: npcId, displayId: displayId, noPound: 1});
        }
    },

    genericmodel: {
        sort: [],
        mode: 3, // Grid mode
        nItemsPerPage: 40,
        nItemsPerRow: 4,
        poundable: 2, // Yes but w/o sort

        columns: [
            {
                value: "name",
                sortFunc: function (a, b) {
                    return Listview.templates.genericmodel.sortFunc(a, b);
                }
            }
        ],

        compute: function (model, td, i) {
            td.className = 'screenshot-cell';
            td.vAlign    = 'bottom';

            var
                displayId,
                type,
                linkType,
                slot,
                typeId;

            if ('modelviewer' in model) {
                displayId = model.modelviewer.displayid;
                typeId    = model.modelviewer.typeid;
                type      = model.modelviewer.type;
                slot      = model.slot;
            }
            else if ('npcmodel' in model) {
                displayId = model.npcmodel;
                typeId    = model.id;
                type      = 1;
            }
            else if ('displayid' in model) {
                displayId = model.displayid;
                typeId    = model.id;
                type      = 3;
                slot      = model.slotbak || model.slot;
            }

            switch (type) {
            case 1:
                linkType = 'npc';
                break;
            case 3:
                linkType = 'item';
                break;
            default:
                linkType = 'item';
                break;
            }

            if (displayId) {
                var a = $WH.ce('a');
                a.href = 'javascript:;';
                a.rel = this.genericlinktype + '=' + model.id + ' domain=' + Locale.get().domain ;
                a.onclick = this.template.modelShow.bind(this.template, type, typeId, displayId, slot, false);

                var img = $WH.ce('img');
                img.src = g_staticUrl + '/modelviewer/thumbs/' + linkType + '/' + displayId + '.png';
                $WH.ae(a, img);

                $WH.ae(td, a);
            }

            d = $WH.ce('div');
            d.style.position = 'relative';
            d.style.height = '1em';

            var d2 = $WH.ce('div');
            d2.className = 'screenshot-caption';

            var a = $WH.ce('a');
            a.className = 'q' + ((this.genericlinktype == 'item') ? g_items[model.id].quality : '');
            a.style.fontFamily = 'Verdana,sans-serif';
            a.href = g_host + '?' + this.genericlinktype + '=' + model.id;
            $WH.ae(a, $WH.ct(model.name.substr(1)));
            $WH.ae(d2, a);

            $WH.ae(d, d2);
            $WH.ae(td, d);

            if (displayId) {
                $WH.aE(td, 'click', this.template.modelShow.bind(this.template, type, typeId, displayId, slot, true));
            }
        },

        modelShow: function (type, typeId, displayId, slot, sp, e) {
            if (sp) {
                e = $WH.$E(e);

                if (e.shiftKey || e.ctrlKey) {
                    return;
                }

                var
                    j = 0,
                    el = e._target;

                while (el && j < 3) {
                    if (el.nodeName == 'A') {
                        return;
                    }
                    if (el.nodeName == 'IMG') {
                        break;
                    }
                    el = el.parentNode;
                }
            }

            var opt = {
                type:      type,
                typeId:    typeId,
                displayId: displayId,
                noPound:   1
            };

            if (typeof slot != "undefined") {
                opt.slot = slot;
            }

            ModelViewer.show(opt);
        },

        sortFunc: function (a, b, col) {
            return ($WH.strcmp(a.displayid, b.displayid) || $WH.strcmp(a.name, b.name) || -$WH.strcmp(a.level, b.level));
        }
    },

    currency: {
        sort: [1],
        searchable: 1,
        filtrable: 1,
        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                span: 2,
                value: 'name',
                compute: function(currency, td, tr) {
                    var i = $WH.ce('td');
                    i.style.width = '1px';
                    i.style.padding = '0';
                    i.style.borderRight = 'none';

                    $WH.ae(i, Icon.create(currency.icon, 0, null, this.getItemLink(currency)));
                    $WH.ae(tr, i);
                    td.style.borderLeft = 'none';

                    var wrapper = $WH.ce('div');

                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(currency);
                    $WH.ae(a, $WH.ct(currency.name));

                    $WH.ae(wrapper, a);

                    $WH.ae(td, wrapper);
                }
            },
            {
                id: 'category',
                name: LANG.category,
                type: 'text',
                width: '15%',
                compute: function(currency, td) {
                    td.className = 'small';

                    var a = $WH.ce('a');
                    a.className = 'q1';
                    a.href = '?currencies=' + currency.category;
                    $WH.ae(a, $WH.ct(g_currency_categories[currency.category]));
                    $WH.ae(td, a);
                },
                getVisibleText: function(currency) {
                    return g_currency_categories[currency.category];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_currency_categories[a.category], g_currency_categories[b.category]);
                }
            }
        ],

        getItemLink: function(currency) {
            return '?currency=' + currency.id;
        }
    },

    classs: {
        sort: [1],
        searchable: 1,
        filtrable: 1,
        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                span: 2,
                value: 'name',
                compute: function(classs, td, tr) {
                    var i = $WH.ce('td');
                    i.style.width = '1px';
                    i.style.padding = '0';
                    i.style.borderRight = 'none';

                    $WH.ae(i, Icon.create('class_' + g_file_classes[classs.id], 0, null, this.getItemLink(classs)));
                    $WH.ae(tr, i);
                    td.style.borderLeft = 'none';

                    // force minimum width to fix overlap display bug
                    td.style.width = '270px';

                    var wrapper = $WH.ce('div');

                    var a = $WH.ce('a');
                    a.className = 'c' + classs.id;
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(classs);
                    $WH.ae(a, $WH.ct(classs.name));

                    if (classs.expansion) {
                        var sp = $WH.ce('span');
                        sp.className = g_GetExpansionClassName(classs.expansion);
                        $WH.ae(sp, a);
                        $WH.ae(wrapper, sp);
                    }
                    else {
                        $WH.ae(wrapper, a);
                    }

                    if (classs.hero) {
                        wrapper.style.position = 'relative';
                        var d = $WH.ce('div');
                        d.className = 'small';
                        d.style.fontStyle = 'italic';
                        d.style.position = 'absolute';
                        d.style.right = '3px';
                        d.style.bottom = '0px';
                        $WH.ae(d, $WH.ct(LANG.lvclass_hero));
                        $WH.ae(wrapper, d);
                    }
                    $WH.ae(td, wrapper);
                }
            },
            {
                id: 'races',
                name: LANG.races,
                type: 'text',
                compute: function(classs, td) {
                    if (classs.races) {
                        var races = Listview.funcBox.assocBinFlags(classs.races, g_chr_races);

                        td.className = 'q1';
                        for (var i = 0, len = races.length; i < len; ++i) {
                            if (i > 0) {
                                $WH.ae(td, $WH.ct(LANG.comma));
                            }
                            var a = $WH.ce('a');
                            a.href = '?race=' + races[i];
                            $WH.ae(a, $WH.ct(g_chr_races[races[i]]));
                            $WH.ae(td, a);
                        }
                    }
                },
                getVisibleText: function(classs) {
                    if (classs.races) {
                        return Listview.funcBox.arrayText(Listview.funcBox.assocBinFlags(classs.races, g_chr_races), g_chr_races);
                    }
                },
                sortFunc: function(a, b, col) {
                    return Listview.funcBox.assocArrCmp(Listview.funcBox.assocBinFlags(a.races, g_chr_races), Listview.funcBox.assocBinFlags(b.races, g_chr_races), g_chr_races);
                }
            }
        ],

        getItemLink: function(classs) {
            return '?class=' + classs.id;
        }
    },

    race: {
        sort: [1],
        searchable: 1,
        filtrable: 1,
        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                span: 2,
                value: 'name',
                compute: function(race, td, tr) {
                    var
                        d = $WH.ce('div'),
                        icon;

                    d.style.margin = '0 auto';
                    d.style.textAlign = 'left';
                    d.style.width = '52px';

                    icon = Icon.create('race_' + g_file_races[race.id] + '_' + g_file_genders[0], 0, null, this.getItemLink(race));
                    icon.style.cssFloat = icon.style.styleFloat = 'left';
                    $WH.ae(d, icon);

                    icon = Icon.create('race_' + g_file_races[race.id] + '_' + g_file_genders[1], 0, null, this.getItemLink(race));
                    icon.style.cssFloat = icon.style.styleFloat = 'left';
                    $WH.ae(d, icon);

                    var i = $WH.ce('td');
                    i.style.width = '1px';
                    i.style.padding = '0';
                    i.style.borderRight = 'none';

                    $WH.ae(i, d);
                    $WH.ae(tr, i);
                    td.style.borderLeft = 'none';

                    var wrapper = $WH.ce('div');

                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(race);
                    $WH.ae(a, $WH.ct(race.name));

                    if (race.expansion) {
                        var sp = $WH.ce('span');
                        sp.className = g_GetExpansionClassName(race.expansion);
                        $WH.ae(sp, a);
                        $WH.ae(wrapper, sp);
                    }
                    else {
                        $WH.ae(wrapper, a);
                    }
                    $WH.ae(td, wrapper);
                }
            },
            {
                id: 'side',
                name: LANG.side,
                type: 'text',
                compute: function(race, td) {
                    if (race.side && race.side != 3) {
                        var sp = $WH.ce('span');
                        sp.className = (race.side == 1 ? 'icon-alliance' : 'icon-horde');
                        g_addTooltip(sp, g_sides[race.side]);

                        $WH.ae(td, sp);
                    }
                },
                getVisibleText: function(race) {
                    if (race.side) {
                        return g_sides[race.side];
                    }
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_sides[a.side], g_sides[b.side]);
                }
            },
            {
                id: 'classes',
                name: LANG.classes,
                type: 'text',
                compute: function(race, td) {
                    if (race.classes) {
                        var classes = Listview.funcBox.assocBinFlags(race.classes, g_chr_classes);

                        var d = $WH.ce('div');
                        d.style.width = (26 * classes.length) + 'px';
                        d.style.margin = '0 auto';

                        for (var i = 0, len = classes.length; i < len; ++i) {
                            var icon = Icon.create('class_' + g_file_classes[classes[i]], 0, null, '?class=' + classes[i]);

                            g_addTooltip(icon, g_chr_classes[classes[i]], 'c' + classes[i]);

                            icon.style.cssFloat = icon.style.styleFloat = 'left';
                            $WH.ae(d, icon);
                        }

                        $WH.ae(td, d);
                    }
                },
                getVisibleText: function(race) {
                    if (race.classes) {
                        return Listview.funcBox.arrayText(Listview.funcBox.assocBinFlags(race.classes, g_chr_classes), g_chr_classes);
                    }
                },
                sortFunc: function(a, b, col) {
                    return Listview.funcBox.assocArrCmp(Listview.funcBox.assocBinFlags(a.classes, g_chr_classes), Listview.funcBox.assocBinFlags(b.classes, g_chr_classes), g_chr_classes);
                }
            }
        ],

        getItemLink: function(race) {
            return '?race=' + race.id;
        }
    },

    changelog: {
        nItemsPerPage: 100,
        sortable: false,
        columns: [
            {
                id: "name",
                name: "Name",
                compute: function (changelog, td) {
                    var c = $(changelog.tooltip).find("b").first().text();
                    $(td).html(c);
                    td.className += " listview-cleartext";
                },
            },
            {
                id: "version",
                name: "Version (mouseover to see tooltip)",
                compute: function (changelog, td) {
                    var tt = changelog.tooltip;
                    $(td).mouseover(function (event, menu) { $WH.Tooltip.showAtCursor(menu, event, 0, 0); }.bind(td, tt));
                    $(td).mousemove(function (event) { $WH.Tooltip.cursorUpdate(event); })
                         .mouseout(function () { $WH.Tooltip.hide(); });
                    /* aowow - we dont do patches
                    var g = typeof g_hearthhead != "undefined" && g_hearthhead ? "hearthstone" : "wow";
                    if (!g_getPatchVersionObject.hasOwnProperty("parsed") || !g_getPatchVersionObject.parsed[g]) {
                        g_getPatchVersionObject();
                    }
                    var j = (changelog.build > 0 ? "" : "Before ") + "Build " + Math.abs(changelog.build);
                    if (changelog.version > 0) {
                        var f = parseInt(changelog.version / 10000) + "." + (parseInt(changelog.version / 100) % 100) + "." + (changelog.version % 100);
                        j = f + " " + j;
                        var c = false;
                        for (var b in g_gameversions[g]) {
                            if (g_gameversions[g][b].versionnum >= changelog.version && (!c || c.build > g_gameversions[g][b].build)) {
                                c = g_gameversions[g][b];
                            }
                        }
                        if (c) {
                            j = j.replace(f, f + " (" + new Date(c.timestamp).toDateString() + ")");
                        }
                    }
                    */
                    let j = changelog.version; // aowow - tmp
                    $(td).html(j);
                },
            },
        ],
    }
};
