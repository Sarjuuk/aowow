function pr_setRegionRealm(f, region, server)
{
    if(!f) return;

    var
        r = f.elements['rg'] || f.elements[0],
        s = f.elements['sv'] || f.elements[1];

    if(!r || !s) return;

    for(var i = 0, len = r.options.length; i < len; ++i)
    {
        if(r.options[i].value == region)
        {
            r.options[i].selected = true;
            break;
        }
    }

    pr_onChangeRegion(f, region, server);

    if(s.onchange)
        s.onchange();
}



function pr_onChangeRegion(f, region, server)
{
    if(!f) return;

    var
        r = f.elements['rg'] || f.elements[0],
        s = f.elements['sv'] || f.elements[1];

    if(!r || !s) return;

    var o = r.options[0];

    o.value = '';
    o.style.color = '#999999';

    $WH.ee(s);

    o = $WH.ce('option');
    o.value = '';
    o.style.color = '#999999';
    $WH.ae(s, o);

    if(s.onchange == null)
        s.onchange = function() { this.className = (this.selectedIndex ? '' : 'search-character'); };

    if(region == null)
        region = r.options[r.selectedIndex].value;

    if(!region)
    {
        r.className = 'search-character';
        s.className = 'search-character';

        var o = $WH.ce('option');
        o.disabled = true;
        $WH.ae(o, $WH.ct(LANG.pr_selectregion));
        $WH.ae(s, o);
    }
    else
    {
        var    realms;

        r.className = '';
        s.className = 'search-character';

        realms = pr_getRealms(region);

        for(var i = 0, len = realms.length; i < len; ++i)
        {
            var
                realmId = realms[i],
                realm   = g_realms[realmId];

            o = $WH.ce('option');

            o.value = g_urlize(realm.name, true, true);
            $WH.ae(o, $WH.ct(realm.name));

            if(server != null && realm.name == server)
            {
                s.className = '';
                o.selected = true;
            }

            $WH.ae(s, o);
        }
    }
}
pr_onChangeRegion.C = {};



function pr_onChangeRace()
{
    if($('select[name="ra[]"] option:selected').length)
        $('select[name=si]').attr('disabled', true).val('');
    else
        $('select[name=si]').attr('disabled', false);
}



function pr_suggestRealms(region, realm, autoComplete)
{
    autoComplete.empty();
    if(realm.val().length >= 2)
    {
        var realms = pr_getRealms(region.val(), realm.val());
        if(realms.length)
        {
            $.each(realms, function(i, realmId) {
                autoComplete.append('<span>' + g_realms[realmId].name + '</span>');
            });
            realm.removeClass('oops');
            autoComplete.show();
        }
        else
        {
            realm.addClass('oops');
            autoComplete.hide();
        }
    }
    else
    {
        realm.removeClass('oops');
        autoComplete.hide();
    }
}



function pr_highlightRealm(selectedRealm, autoComplete, realm)
{
    var spans  = $('span', autoComplete),
        active = $(spans.get(selectedRealm - 1));
    spans.removeClass('active');
    active.addClass('active');
    realm.val(active.text()).focus();
}



function pr_getRealms(region, text)
{
    if(pr_onChangeRegion.C[region] != null)
        realms = pr_onChangeRegion.C[region];
    else
    {
        realms = pr_onChangeRegion.C[region] = g_sortJsonArray(
            g_realms,
            g_realms,
            function(a, b) { return $WH.strcmp(g_realms[a].name, g_realms[b].name) },
            function(x) { return x.region == region }
        );
    }

    if(text != null)
        realms = $WH.array_filter(realms, function(x) { return g_realms[x].name.toLowerCase().match(text.toLowerCase()) });

    return realms;
}



function pr_updateStatus(page, div, id, request, tryAgain)
{
    if (tryAgain == null) {
        tryAgain = function(noUpdate) {
            new Ajax('?' + page + '=resync&id=' + id, {
                method: 'POST',
                onSuccess: function(xhr, opt) {
                    if (!noUpdate) {
                        pr_updateStatus(page, div, id, request, null);
                    }
                }
            });
        };
    }

    new Ajax('?' + page + '=status&id=' + id + '&t=' + (new Date().getTime()), {
        onSuccess: function(xhr, opt) {
            var text = xhr.responseText;

            if (text.charAt(0) != '[' || text.charAt(text.length - 1) != ']') {
                return;
            }

            var
                a           = eval(text),
                processes   = a[0],
                profile     = (location.search || location.pathname).substr(1),
                working     = false,
                action      = false,
                statusTotal = { 1: 0, 2: 0, 3: 0, 4: 0 },
                duration;

            $WH.ee(div);

            for (var i = 1; i < a.length; ++i) {
                var
                    status    = a[i][0],
                    refresh   = a[i][1],
                    count     = a[i][2],
                    errcode   = a[i][3],
                    nresyncs  = a[i][4];

                if (!statusTotal[status]) {
                    statusTotal[status] = 0;
                }

                statusTotal[status]++;

                if (status == 3 && !nresyncs) {
                    request = true;
                }

                if (status && (status != 3 || request)) {
                    if (a.length == 2) { // single status
                        duration = (count && processes ? g_formatTimeElapsed((count * 15 / processes) + 2) : LANG.pr_queue_unknown);
                        count    = (!isNaN(count) ? $WH.number_format(count) : LANG.pr_queue_unknown);

                        div.innerHTML = LANG.pr_queue_resyncreq;

                        if (!processes) {
                            div.innerHTML += ' ' + LANG.pr_queue_noprocess;
                        }

                        div.innerHTML += '<a id="close-profiler-notification" class="announcement-close" href="javascript:;" onclick="$(\'.profiler-message\').hide(); return false;">';
                        div.innerHTML += '<br />' + (status < 3 && !refresh && !nresyncs ? LANG.pr_queue_addqueue : (status > 2 ? $WH.sprintf(LANG['pr_queue_status' + status], LANG['pr_error_armory' + errcode], profile) : $WH.sprintf(LANG['pr_queue_status' + status], count, duration, profile)));
                        div.style.backgroundImage = '';

                        if (status == 4) {
                            var _ = $WH.gE(div, 'a')[1];
                            _.onclick = tryAgain.bind(null, false);
                        }
                    }

                    if (status == 4) {
                        $('a', div).click(tryAgain);
                    }

                    else if (refresh && !action) {
                        setTimeout(pr_updateStatus.bind(null, page, div, id, request, tryAgain), refresh);
                        working = true;
                        action  = true;
                    }
                }
            }

            if (!div.childNodes) {
                div.style.display = 'none';
            }

            if (!working) {
                div.style.backgroundImage = 'none';
            }

            if (a.length > 2) { // Multiple status returned
                div.innerHTML  = '<a id="close-profiler-notification" class="announcement-close" href="javascript:;" onclick="$(\'.profiler-message\').hide(); return false;">';
                div.innerHTML += LANG.pr_queue_resyncreq + (!processes ? ' ' + LANG.pr_queue_noprocess : '') + '<br />' + $WH.sprintf(LANG['pr_queue_batch'], statusTotal[1], statusTotal[2], statusTotal[3], statusTotal[4]);
            }
        }
    });
}



function pr_resyncRoster(id, mode)
{
    var div = $WH.ge('roster-status');
    div.innerHTML = LANG.pr_queue_addqueue;
    div.style.display = '';

    new Ajax(
        '?' + mode + '=resync&id=' + id + '&profile',
        {
            method: 'POST',
            onSuccess: function(xhr, opt)
            {
                var result = parseInt(xhr.responseText);

                if(isNaN(result))
                    alert(LANG.message_resyncerror + result);
                else if(result < 0 && result != -102)
                    alert(LANG.message_resyncerror + '#' + result);

                pr_updateStatus('profile', div, id + '&' + mode, true);
            }
        }
    );
}

// imported from deprecated version
function pr_initRosterListview() {
    this.applySort();
    if (g_user.roles & (U_GROUP_ADMIN | U_GROUP_BUREAU)) {
        this.mode = 1;
        this.createCbControls = function (c, b) {
            if (!b && this.data.length < 15) {
                return;
            }

            var
                iSelectAll = $WH.ce('input'),
                iDeselect  = $WH.ce('input'),
                iResync    = $WH.ce('input');

            iSelectAll.type = iDeselect.type = iResync.type = 'button';

            iSelectAll.value = LANG.button_selectall;
            iDeselect.value  = LANG.button_deselect;
            iResync.value    = LANG.button_resync;

            iSelectAll.onclick = Listview.cbSelect.bind(this, true);
            iDeselect.onclick  = Listview.cbSelect.bind(this, false);
            iResync.onclick    = (function () {
                var rows = this.getCheckedRows();
                if (!rows.length) {
                    alert(LANG.message_nocharacterselected);
                }
                else {
                    var buff = '';
                    $WH.array_walk(rows, function (x) {
                        buff += x.id + ',';
                    });

                    buff = $WH.rtrim(buff, ',');
                    if (buff != '') {
                        new Ajax('?profile=resync&id=' + buff);
                    }

                    (Listview.cbSelect.bind(this, false))();
                    alert(LANG.message_characterresync);
                }
            }).bind(this);

            $WH.ae(c, iSelectAll);
            $WH.ae(c, iDeselect);
            $WH.ae(c, iResync);
        }
    }
}

function pr_getGearScoreQuality(level, gearScore, relics, slotId, use2h)
{
    var baseScore, totalMod, quality;

    if(!gearScore)
        return 0;

    if(slotId == null)
    {
        totalMod = 12.25390625;

        if(level < 55)
        {
            if(relics)
                totalMod -= 81/256; // Relic

            totalMod -= 18/16; // Trinkets x2
            if(level < 25)
            {
                totalMod -= 25/16; // Head and Neck
                if(level < 20)
                    totalMod -= 30/16; // Shoulder and Rings x2
            }
        }
    }
    else
    {
        switch(slotId)
        {
            case 1:  // Head
            case 5:  // Chest
            case 7:  // Legs
            case 16: // Main Hand (Modified)
            case 17: // Off Hand (Modified)
                totalMod = 1;
                break;

            case 3:  // Shoulder
            case 10: // Hands
            case 6:  // Waist
            case 8:  // Feet
                totalMod = 3/4;
                break;

            case 9:  // Wrists
            case 2:  // Neck
            case 15: // Back
            case 11: // Finger #1
            case 12: // Finger #2
            case 13: // Trinket #1
            case 14: // Trinket #2
                totalMod = 9/16;
                break;

            case 18: // Ranged, Relic
                totalMod = 81/256;
                break;
        }

        if(use2h)
            totalMod *= 2;
    }

    if(level > 70)
        baseScore = ((level - 70) *  9.5) + 105;
    else if(level > 60)
        baseScore = ((level - 60) *  4.5) +  60;
    else
        baseScore = level + 5;

    if(gearScore >= baseScore * totalMod * 1.25)
        quality = 5; // Legendary
    else
    {
        quality = 4; // Epic
        while(gearScore < baseScore * totalMod && quality > 0)
        {
            baseScore -= (level > 80 ? (5 - quality) * 12 : 0.6 * baseScore);
            quality--;
        }
    }

    return quality;
}



function pr_getSpecFromTalents(classs, spent)
{
    var spec = 0; // Hybrid

    if(spent == null || (spent[0] == 0 && spent[1] == 0 && spent[2] == 0))
        spec = -1; // Untalented
    else if(spent[0] - spent[1] > 5 && spent[0] - spent[2] > 5)
        spec = 1;
    else if(spent[1] - spent[0] > 5 && spent[1] - spent[2] > 5)
        spec = 2;
    else if(spent[2] - spent[0] > 5 && spent[2] - spent[1] > 5)
        spec = 3;

    var    specName;
    var specIcon;

    if(spec <= 0)
    {
        specName = g_chr_specs[spec];

        if(spec == -1)
            specIcon = 'spell_shadow_sacrificialshield'; // Untalented
        else
            specIcon = 'spell_nature_elementalabsorption'; // Hybrid
    }
    else
    {
        specName = g_chr_specs[classs][spec - 1];

        if(wt_presets && wt_presets[classs] && wt_presets[classs].pve)
        {
            var j = 1;
            for(var p in wt_presets[classs].pve)
            {
                if(j++ == spec)
                {
                    specIcon = wt_presets[classs].pve[p].__icon;
                    break;
                }
            }
        }
    }

    return {
        id:   spec,
        name: specName || '',
        icon: specIcon || ''
    };
}



function pr_getScaleFromSpec(classs, spec, getName)
{
    var pve  = wt_presets[classs].pve,
        temp = [];

    for(var p in pve)
        temp.push(getName ? LANG.presets[p] : pve[p]);

    return (getName? temp[spec] : $WH.dO(temp[spec]));
}



function pr_getScaleFilter(scale, noFilter) {
    var temp = [];

    if (scale) {
        for (var i = 0, len = fi_filters.items.length; i < len; ++i) {
            var f = fi_filters.items[i];

            if (LANG.traits[f.name] && scale[f.name]) {
                temp.push([f.id, scale[f.name]]);
            }
        }
    }

    temp.sort(function(a, b) {
        return -$WH.strcmp(a[1], b[1]);
    });

    var
        wt  = [],
        wtv = [];

    for (var i = 0, len = temp.length; i < len; ++i) {
        wt.push(temp[i][0]);
        wtv.push(temp[i][1]);
    }

    if (wt.length && wtv.length) {
        return (noFilter ? '&' : ';gm=3;') + 'wt=' + wt.join(':') + (noFilter ? '&' : ';') + 'wtv=' + wtv.join(':');
    }

    return '';
}



function pr_showClassPresetMenu(a, itemId, itemClass, itemSlot, event)
{
    var menu = [[, LANG.menu_chooseclassspec]];
    if(itemId && itemClass && (itemClass != 2 || itemSlot))
    {
        if(g_user.weightscales && g_user.weightscales.length)
        {
            var __ = [0, LANG.ficustom,, []];
            menu.push(__);

            for(var i = 0, len = g_user.weightscales.length; i < len; ++i)
            {
                var scale = g_user.weightscales[i];
                __[3].push([scale.id, scale.name, '?items=' + itemClass + '&filter=cr=161;crs=1;crv=0' + pr_getScaleFilter(scale) + (itemClass == 2 ? ';gb=1' : ';gb=3') + ';upg=' + itemId + (itemClass == 2 && g_item_slots[itemSlot] ? '#' + g_urlize(g_item_slots[itemSlot]) : '')]);
            }
        }

        for(var c in wt_presets)
        {
            var __ = [c, g_chr_classes[c],, [], {className:'c'+c,tinyIcon:'class_'+g_file_classes[c]}];
            menu.push(__);

            var j = 0;
            for(var p in wt_presets[c].pve)
            {
                __[3].push([j, LANG.presets[p], '?items=' + itemClass + '&filter=ub=' + c + ';cr=161;crs=1;crv=0' + pr_getScaleFilter(wt_presets[c].pve[p]) + (itemClass == 2 ? ';gb=1' : ';gb=3') + ';upg=' + itemId + (itemClass == 2 && g_item_slots[itemSlot] ? '#' + g_urlize(g_item_slots[itemSlot]) : ''),, {tinyIcon:wt_presets[c].pve[p].__icon}]);
                j++;
            }
        }

        var pos = $WH.g_getCursorPos(event);

        Menu.sort(menu);
        Menu.showAtXY(menu, pos.x, pos.y);
    }
}



function pr_addEquipButton(id, itemId)
{
    $.each(g_user.characters, function(idx, character)
    {
        if(character.pinned)
        {
            var button = RedButton.create(LANG.button_equip, true, function() {
                location.href = g_getProfileUrl(character) + '&items=' + itemId;
            });

            g_addTooltip(button, LANG.tooltip_equip, 'q1');

            $('#' + id).append(button);

            return;
        }
    });
}



function pr_onBreadcrumbUpdate() // Add character lookup textbox to the breadcrumb
{
    var breadcrumb = PageTemplate.get('breadcrumb');
    if(!breadcrumb || breadcrumb.length != 6) // Realm is required
        return;

    var path = Menu.getFullPath(mn_path, breadcrumb);
    var menuItem = path[path.length - 1];
    var span = PageTemplate.expandBreadcrumb()[0];

    var f = $WH.ce('form'),
        i = $WH.ce('input'),
        a = $WH.ce('a');

    span.className = 'profiler-charlookup';

    f.onsubmit = pr_DirectLookup.bind(0, f, false);

    i.name = 'na';
    i.className = 'search-character';
    i.type = 'text';

    i.placeholder = LANG.tab_character + LANG.ellipsis;     // aowow - use placeholder text instead of background texture

    i.onfocus = function()
    {
        this.className = '';
    };

    i.onblur = function()
    {
        if($WH.trim(this.value) == '')
        {
            this.className = 'search-character';
            this.value = '';
        }
    };

    a.className = 'profiler-charlookup-go';
    a.href = 'javascript:;';
    a.onclick = f.onsubmit.bind(f);

    $WH.ae(f, i);
    $WH.ae(f, a);
    $WH.ae(span, f);
}

function pr_DirectLookup(form, browse)
{
    var
        region  = $('*[name="rg"]', form),
        server  = $('*[name="sv"]', form),
        name    = $('*[name="na"]', form),
        usePath;

    if(region.length > 1) // Radio
        region = $('*[name="rg"]:checked', form);

    if(browse)
    {
        if(region.val() || server.val() || name.val())
            location.href = '?profiles' + (region.val() ? '=' + region.val() + (server.val() ? '.' + g_urlize(server.val(), true, true) : '') : '') + (name.val() ? '&filter=na=' + name.val() + ';ex=on' : '');
        return false;
    }

    var breadcrumb = PageTemplate.get('breadcrumb');
    if(usePath = (breadcrumb && breadcrumb.length == 6)) // Realm is required
    {
        var path = Menu.getFullPath(mn_path, breadcrumb),
            menuItem = path[path.length - 1];
    }

    if(!name.val())
    {
        alert(LANG.message_missingcharacter);
        name.focus().addClass('oops');
        return false;
    }

    if(!region.val() && !usePath)
    {
        alert(LANG.message_missingregion);
        region.focus().addClass('oops');
        return false;
    }

    if(!server.val() && !usePath)
    {
        alert(LANG.message_missingrealm);
        server.focus().addClass('oops');
        return false;
    }

    if(region.val() && server.val())
        usePath = false;

    location.href = (usePath ? menuItem[2].replace('profiles', 'profile') : '?profile=' + region.val() + '.' + g_urlize(server.val(), true, true)) + '.' + g_cleanCharacterName(name.val());
    return false;
}



function pr_lookupOrSearch(forceLookup)
{
    if(!forceLookup)
    {
        if($('input[name="sv"]').val())
        {
            var realmName = $('input[name="sv"]').val().toLowerCase(),
                valid = false;

            $.each(g_realms, function(i, realm) {
                if(realm.name.toLowerCase() == realmName)
                    valid = true;
            });

            if(!valid)
            {
                alert(LANG.message_missingrealm);
                $('input[name="sv"]').focus().addClass('oops');
                return;
            }
        }

        forceLookup = ($('input[name="na"]').val() && $('input[name="sv"]').val());
    }

    pr_DirectLookup($('.profiler-home'), !forceLookup);
}



function pr_initProfilerHome()
{
    $('input[name="na"]')
        .keyup(function(e) {
            $(this).removeClass('oops');
            if(e.keyCode == 13) // Enter
                pr_lookupOrSearch(false);
        })
        .focus();

    $('input[type="radio"]')
        .change(function() {
            $('label').removeClass('selected');
            $('label[for="' + $(this).attr('id') + '"]').addClass('selected');
            pr_suggestRealms($('input[name="rg"]:checked'), $('input[name="sv"]'), $('.profiler-autocomplete'));
            $('input[name="sv"]').focus();
        });

    $('input[name="sv"]')
        .keyup(function(e) {
            switch(e.keyCode)
            {
                case 13: // Enter
                    $('.profiler-autocomplete').hide();
                    pr_lookupOrSearch(false);
                    break;

                case 27: // Escape
                    $('.profiler-autocomplete').hide();
                    break;

                case 38: // Up
                    selectedRealm--;
                    if(selectedRealm < 0)
                        selectedRealm = $('.profiler-autocomplete span').length - 1;
                    pr_highlightRealm(selectedRealm, $('.profiler-autocomplete'), $('input[name="sv"]'));
                    break;

                case 40: // Down
                    selectedRealm++;
                    if(selectedRealm >= $('.profiler-autocomplete span').length)
                        selectedRealm = 0;
                    pr_highlightRealm(selectedRealm, $('.profiler-autocomplete'), $('input[name="sv"]'));
                    break;

                default:
                    pr_suggestRealms($('input[name="rg"]:checked'), $('input[name="sv"]'), $('.profiler-autocomplete'));
                    selectedRealm = 0;
                    break;
            }
        });

    $('.profiler-autocomplete')
        .delegate('span', 'mouseover', function() {
            $('.profiler-autocomplete span').removeClass('active');
        })
        .delegate('span', 'click', function() {
            $('input[name="sv"]').val($(this).text()).focus();
            $('.profiler-autocomplete').hide();
        });

    $('#profiler-lookup, #profiler-search')
        .click(function(e) {
            pr_lookupOrSearch(this.id == 'profiler-lookup');
        });

    $('body')
        .click(function() {
            $('.profiler-autocomplete').hide();
        });
}



if($WH.isset('mn_profiles'))
{
    // Add submenu to "Tools > Profiler"
    Menu.findItem(mn_path, [1,5,0])[MENU_IDX_SUB] = mn_profiles;
    Menu.findItem(mn_path, [1,5,2])[MENU_IDX_SUB] = mn_guilds;
    Menu.findItem(mn_path, [1,5,3])[MENU_IDX_SUB] = mn_arenateams;

    PageTemplate.getBreadcrumb().bind('update', pr_onBreadcrumbUpdate);
}
