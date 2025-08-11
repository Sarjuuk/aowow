var Favorites = new function()
{
    var _type    = null;
    var _typeId  = null;
    var _favIcon = null;

    this.pageInit = function(h1, type, typeId)
    {
        if (typeof h1 == 'string')
        {
            if (!document.querySelector)
                return;

            h1 = document.querySelector(h1);
        }

        if (!h1 || typeof type != 'number' || typeof typeId != 'number')
            return;

        _type   = type;
        _typeId = typeId;

        createIcon(h1);
    }

    function initFavIcon()
    {
        var h1 = typeof g_pageInfo == 'object' && typeof g_pageInfo.type == 'number' && typeof g_pageInfo.typeId == 'number' ? document.querySelector('#main-contents h1') : null;
        if (!h1) {
            if (document.readyState !== 'complete')
                setTimeout(initFavIcon, 9);

            return;
        }

        _type   = g_pageInfo.type;
        _typeId = g_pageInfo.typeId;

        createIcon(h1);
    }

    this.hasFavorites = function()
    {
        return !!g_favorites.length
    }

    this.getMenu = function()
    {
        var favMenu  = [];
        var nGroups  = 0;
        var nEntries = 0;

        for (var i = 0, favGroup; favGroup = g_favorites[i]; i++)
        {
            if (!favGroup.entities.length)
                continue;

            nGroups++;
            var subMenu = [];
            for (var j = 0, favEntry; favEntry = favGroup.entities[j]; j++)
            {
                subMenu.push([favEntry[0], favEntry[1], '?' + g_types[favGroup.id] + '=' + favEntry[0]]);
                nEntries++
            }

            Menu.sort(subMenu);
            favMenu.push([favGroup.id, LANG.types[favGroup.id][2], , subMenu])
        }

        Menu.sort(favMenu);

        // display short favorites as 1-dim list
        if ((nGroups == 1 && nEntries <= 45) || (nGroups == 2 && nGroups + nEntries <= 30) || (nGroups > 2 && nGroups + nEntries <= 15))
        {
            var list = [];

            for (var i = 0; subMenu = favMenu[i]; i++)
            {
                list.push([, subMenu[MENU_IDX_NAME]]);

                for (var j = 0, subEntry; subEntry = subMenu[MENU_IDX_SUB][j]; j++)
                {
                    var listEntry = [subEntry[MENU_IDX_ID], subEntry[MENU_IDX_NAME], subEntry[MENU_IDX_URL]];

                    if (subEntry[MENU_IDX_OPT])
                        listEntry[MENU_IDX_OPT] = subEntry[MENU_IDX_OPT];

                    list.push(listEntry);
                }
            }

            favMenu = list;
        }

        return favMenu;
    }

    this.refreshMenu = function()
    {
        var menuRoot = $('#toplinks-favorites');
        if (!menuRoot.length)
            return;

        var favMenu = Favorites.getMenu();
        if (!favMenu.length) {
            menuRoot.hide();
            return;
        }

        Menu.add(menuRoot, favMenu);
        menuRoot.show();
    }

    function createIcon(heading)
    {
        _favIcon = $('<span/>', {
            'class': 'fav-star',
            mouseout: $WH.Tooltip.hide
        }).appendTo(heading);

        if (g_user.id)
        {
            _favIcon.addClass('fav-star' + (isFaved(_type, _typeId) ? '-1' : '-0')).click((function(type, typeId, name) {
                toggleEntry(type, typeId, name);
                updateIcon(type, typeId);
                $WH.Tooltip.hide();
            }).bind(null, _type, _typeId, heading.textContent.trim().replace(/(.+)<.*/, '$1')));

            _favIcon.mouseover(function(event) {
                var tt = this.className.match(/\bfav-star-0\b/) ? LANG.addtofavorites : LANG.removefromfavorites;
                $WH.Tooltip.show(this, tt, false, false, 'q2');
            });

        }
        else
        {
            _favIcon.addClass('fav-star-0').click(function() {
                location.href = "?account=signin";
                $WH.Tooltip.hide();
            }).mouseover(function(event) {
                $WH.Tooltip.show(this, LANG.favorites_login + '<div class="q2" style="margin-top:10px">' + LANG.clicktologin + '</span>');
            });
        }
    }

    function updateIcon(type, typeId)
    {
        if (_favIcon)
        {
            var rmv = 'fav-star-0';
            var add = 'fav-star-1';
            if (!isFaved(type, typeId))
            {
                rmv = 'fav-star-1';
                add = 'fav-star-0';
            }

            _favIcon.removeClass(rmv).addClass(add);
        }
    }

    function isFaved(type, typeId)
    {
        var idx = getIndex(type);
        if (idx == -1)
            return false;

        for (var i = 0, j; j = g_favorites[idx].entities[i]; i++)
            if (j[0] == typeId)
                return true;

        return false;
    }

    function toggleEntry(type, typeId, name)
    {
        if (isFaved(type, typeId))
            removeEntry(type, typeId);
        else
            addEntry(type, typeId, name);
    }

    function addEntry(type, typeId, name)
    {
        var idx = getIndex(type, true);
        if (idx == -1)
        {
         /* $WH. */ console.error("Invalid type when adding entity to favorites! Type was:", type);
            return;
        }

        for (var i = 0, j; j = g_favorites[idx].entities[i]; i++)
        {
            if (j[0] == typeId)
            {
                alert(LANG.favorites_duplicate.replace('%s', LANG.types[type][1]));
                return;
            }
        }

        sendUpdate('add', type, typeId);
        g_favorites[idx].entities.push([typeId, name]);
        Favorites.refreshMenu();
    }

    function removeEntry(type, typeId)
    {
        var idx = getIndex(type);
        if (idx == -1)
            return;

        for (var i = 0, j; j = g_favorites[idx].entities[i]; i++)
        {
            if (j[0] == typeId)
            {
                sendUpdate('remove', type, typeId);
                g_favorites[idx].entities.splice(i, 1);
                if (!g_favorites[idx].entities.length)
                    g_favorites.splice(idx, 1);

                Favorites.refreshMenu();
                return;
            }
        }
    }

    function getIndex(type, createNew)
    {
        if (!LANG.types[type])
            return -1;

        for (var i = 0, j; j = g_favorites[i]; i++)
            if (j.id == type)
                return i;

        if (!createNew)
            return -1;

        g_favorites.push({ id: type, entities: [] });

        g_favorites.sort(function(a, b) { return $WH.strcmp(LANG.types[a.id], LANG.types[b.id]) });

        for (i = 0; j = g_favorites[i]; i++)
            if (j.id == type)
                return i;

        return -1;
    }

    function sendUpdate(method, type, typeId)
    {
        var data = {
            id: typeId,
            // sessionKey: g_user.sessionKey
        };
        data[method] = type;
        $.post('?account=favorites', data);
    }

    if (document.querySelector && $WH.localStorage.isSupported())
        initFavIcon();
};
