/*
Menu class
*/

var MENU_IDX_ID   = 0; //      ID: A number or string; null makes the menu item a separator
var MENU_IDX_NAME = 1; //    Name: A string
var MENU_IDX_URL  = 2; //     URL: A string for the URL, or a function to call when the menu item is clicked
var MENU_IDX_SUB  = 3; // Submenu: Child menu
var MENU_IDX_OPT  = 4; // Options: JSON array with additional options

var Menu = new function()
{
    var self = this;

    /***********/
    /* PUBLIC */
    /**********/

    self.add = function(node, menu, opt) // Add a menu to a DOM element
    {
        if(!opt) opt = $.noop;

        var $node = $(node);

        $node.data('menu', menu);

        if(opt.showAtCursor)
        {
            $node.click(rootNodeClick);
        }
        else
        {
            $node
            .mouseover(rootNodeOver)
            .mouseout(rootNodeOut);
        }
    };

    self.remove = function(node) // Remove menu from DOM element
    {
        $(node)
        .data('menu', null)
        .unbind('click',     rootNodeClick)
        .unbind('mouseover', rootNodeOver)
        .unbind('mouseout',  rootNodeOut);
    };

    self.show = function(menu, node)
    {
        var $node = $(node);

        show(menu, $node);
    }

    self.showAtCursor = function(menu, event) // {event} must have been normalized by jQuery
    {
        showAtXY(menu, event.pageX, event.pageY);
    }

    self.showAtXY = function(menu, x, y)
    {
        showAtXY(menu, x, y);
    }

    self.hide = function()
    {
        rootNodeOut();
    }

    self.addButtons = function(dest, menu) // Create a set of menu buttons (as seen on the homepage or in the top bar)
    {
        var $dest = $(dest);
        if(!$dest.length)
            return;

        var $buttons = $('<span class="menu-buttons"></span>');

        $.each(menu, function(idx, menuItem)
        {
            if(isSeparator(menuItem)) return;

            var $a = $('<a></a>')
            var $span = $('<span></span>', {
                text: menuItem[MENU_IDX_NAME]
            }).appendTo($a);

            self.linkifyItem(menuItem, $a);

            if(hasSubmenu(menuItem))
            {
                $span.addClass('hassubmenu');
                self.add($a, menuItem[MENU_IDX_SUB]);
            }

            $buttons.append($a);
        });

        $dest.append($buttons);
    };

    self.linkifyItem = function(menuItem, $a) // Also used by PageTemplate when generating the top tabs
    {
        var opt = self.getItemOpt(menuItem);

        if(!menuItem[MENU_IDX_URL]) // Unlinked
        {
            $a.attr('href', 'javascript:;');
            $a.addClass('unlinked');

            return;
        }

        if(typeof menuItem[MENU_IDX_URL] == 'function') // Function
        {
            $a.attr('href', 'javascript:;');
            $a.click(hide);
            $a.click(menuItem[MENU_IDX_URL]);
        }
        else // URL
        {
            var url = self.getItemUrl(menuItem);
            $a.attr('href', url);

            if(opt.newWindow || g_isExternalUrl(url))
                $a.attr('target', '_blank');

            if(opt.rel)
                $a.attr('rel', opt.rel);
        }
        if(typeof menuItem[MENU_IDX_OPT] == 'object' && menuItem[MENU_IDX_OPT].className)
            $a.addClass(menuItem[MENU_IDX_OPT].className);
    };

    self.updateItem = function(menuItem) // Also used by PageTemplate when the breadcrumb changes
    {
        var $a = menuItem.$a;
        if(!$a)
            return;

        var opt = self.getItemOpt(menuItem);

        $a.removeClass('checked tinyicon icon');
        $a.css('background-image', '');

        if(menuItem.checked)
        {
            $a.addClass('checked');
        }
        else if(opt.tinyIcon)
        {
            $a.addClass('tinyicon');
            $a.css('background-image', 'url(' + (opt.tinyIcon.indexOf('/') != -1 ? opt.tinyIcon : g_staticUrl + '/images/wow/icons/tiny/' + opt.tinyIcon.toLowerCase() + '.gif') + ')');
        }
        else if(opt.icon)
        {
            $a.addClass('icon');
            $a.css('background-image', 'url(' + opt.icon + ')');
        }
        else if(opt.socketColor && g_file_gems[opt.socketColor])
        {
            $a.addClass('socket-' + g_file_gems[opt.socketColor]);
        }

        var className = (opt['class'] || opt.className);
        if(className)
            $a.addClass(className);
    };

    // MENU UTILITY FUNCTIONS

    self.hasMenu = function(node)
    {
        var $node = $(node);

        return $node.data('menu') != null;
    }

    self.modifyUrl = function(menuItem, params, opt)
    {
        var json = { params: params, opt: opt };

        traverseMenuItem(menuItem, function(x)
        {
            x.modifyUrl = json;
        });

        PageTemplate.updateBreadcrumb();
    };

    self.fixUrls = function(menu, url, opt)
    {
        opt = opt || {};

        opt.hash = (opt.hash ? '#' + opt.hash : '');

        fixUrls(menu, url, opt, 0);
    };

    self.sort = function(menu) // Sort a specific menu
    {
        if(hasSeparators(menu))
            sortSeparatedMenu(menu);
        else
            sortMenu(menu);
    };

    self.sortSubmenus = function(menu, paths) // Sort the submenus of {menu} specified by {paths}
    {
        $.each(paths, function(idx, path)
        {
            var submenu = self.findItem(menu, path);
            if(submenu && submenu[MENU_IDX_SUB])
                self.sort(submenu[MENU_IDX_SUB]);
        });
    };

    self.implode = function(menu, opt) // Return a new menu where items preceded by a heading are merged into a single item w/ a submenu
    {
        if(!opt) opt = $.noop;

        var result = [];
        var groupedMenu;

        if(opt.createHeadinglessGroup)
        {
            groupedMenu = [];
            result.push([0, '', null, groupedMenu]);
        }

        $.each(menu, function(idx, menuItem)
        {
            if(isSeparator(menuItem))
            {
                groupedMenu = [];
                result.push([0, menuItem[MENU_IDX_NAME], null, groupedMenu]);
            }
            else
            {
                if(groupedMenu)
                    groupedMenu.push(menuItem);
                else
                    result.push(menuItem);
            }
        });

        return result;
    };

    self.findItem = function(menu, path) // Return the menu item specified by {path} in {menu}
    {
        return self.getFullPath(menu, path).pop();
    };

    self.getFullPath = function(menu, path) // Return an array with all the menu items specified by {path} in {menu}
    {
        var result = [];

        for(var i = 0; i < path.length; ++i)
        {
            var pos = findItemPosById(menu, path[i]);

            if(pos != -1)
            {
                var menuItem = menu[pos];
                menuItem.parentMenu = menu;
                menu = menuItem[MENU_IDX_SUB];

                result.push(menuItem);
            }
        }

        return result;
    };

    self.getItemUrl = function(menuItem)
    {
        var url = menuItem[MENU_IDX_URL];
        if(!url)
            return null;

        var opt = self.getItemOpt(menuItem);

        if(menuItem.modifyUrl)
            url = g_modifyUrl(url, menuItem.modifyUrl.params, menuItem.modifyUrl.opt);

        return url;
    };

    self.getItemOpt = function(menuItem)
    {
        if(!menuItem[MENU_IDX_OPT])
            menuItem[MENU_IDX_OPT] = {};

        return menuItem[MENU_IDX_OPT];
    };

    self.removeItemById = function(menu, id)
    {
        var pos = findItemPosById(menu, id);
        if(pos != -1)
            menu.splice(pos, 1);
    };

    self.setSubmenu = function (menuItem, submenu)
    {
        menuItem[MENU_IDX_SUB] = submenu;
    };


    /***********/
    /* PRIVATE */
    /***********/

    var DELAY_BEFORESHOW  = 25;
    var DELAY_BEFOREHIDE  = 333;

    var MAX_COLUMNS = 4;

    // Should match the styles used in Menu.css
    var SIZE_SHADOW_WIDTH    = 6;
    var SIZE_SHADOW_HEIGHT   = 6;
    var SIZE_BORDER_HEIGHT   = 3;
    var SIZE_MENUITEM_HEIGHT = 26;

    var inited = false;
    var timer;
    var $rootNode;
    var visibleMenus = {};
    var openLinks = {};
    var divCache = {};
    var menuItemsCache = {};
    var nextUniqueId = 0;

    function init()
    {
        if(inited) return;
        inited = true;

        // Run a test to get the height of a menu item (in case it's different in some browser/OS combos)
        var $div = $('<div class="menu"><a href="#"><span>ohai</span></a></div>').css({left: '-1000px', top: '-1000px'}).appendTo(document.body);
        var height = $div.children('a').outerHeight();
        $div.remove();
        if(height > 15) // Safety
            SIZE_MENUITEM_HEIGHT = height;
    }

    function show(menu, $node)
    {
        if($rootNode)
            $rootNode.removeClass('open');

        $rootNode = $node;
        $rootNode.addClass('open');

        displayFirstMenu(menu);
    }

    function hide()
    {
        if($rootNode)
        {
            $rootNode.removeClass('open');
            $rootNode = null;
        }

        hideMenus(0);
    }

    function showAtXY(menu, x, y)
    {
        clearTimeout(timer);

        displayFirstMenu(menu, x, y);
    }

    function displayFirstMenu(menu, x, y)
    {
        closeLinks(0);
        displayMenu(menu, 0, x, y);
        hideMenus(1);
    }

    function displayMenu(menu, depth, x, y)
    {
        init();

        beforeShowMenu(menu);

        var $div       = createDiv(depth);
        var $menuItems = createMenuItems(menu);
        var $menu      = createMenu($menuItems, depth);

        $div.append($menu);

        var animated = !isMenuVisible(depth);
        visibleMenus[depth] = $div;

        var pos = getMenuPosition($div, depth, x, y);
        $div.css({
            left: pos.x + 'px',
            top:  pos.y + 'px'
        });

        revealDiv($div, animated);
    }

    function createDiv(depth)
    {
        if(divCache[depth])
        {
            var $div = divCache[depth];
            $div.children().detach(); // Similar to $div.empty(), except custom data is preserved.
            return $div;
        }

        var $div = $('<div class="menu"></div>')
        .mouseover(menuDivOver)
        .mouseleave(menuDivOut)
        .delegate('a', 'mouseenter', { depth: depth }, menuItemOver)
        .delegate('a', 'click', menuItemClick);

        $div.appendTo(document.body);

        divCache[depth] = $div;
        return $div;
    }

    function createMenuItems(menu)
    {
        var uid = getUniqueId(menu);

        if(menuItemsCache[uid])
            return menuItemsCache[uid];

        var nextSeparator; // Used to make sure a separator is always followed by a visible item
        var menuItems = [];

        $.each(menu, function(idx, menuItem)
        {
            if(!isItemVisible(menuItem)) return;

            $a = createMenuItem(menuItem);

            if(isSeparator(menuItem))
            {
                nextSeparator = $a;
                return;
            }

            if(nextSeparator)
            {
                menuItems.push(nextSeparator);
                nextSeparator = null;
            }

            menuItems.push($a);
        });

        var $menuItems = $(menuItems);

        menuItemsCache[menu] = $menuItems;
        return $menuItems;
    }

    function createMenuItem(menuItem)
    {
        beforeCreateMenuItem(menuItem);

        var $a = $('<a></a>');

        menuItem.$a = $a;
        $a.data('menuItem', menuItem);

        self.linkifyItem(menuItem, $a);
        self.updateItem(menuItem);

        // Separator
        if(isSeparator(menuItem))
        {
            $a.addClass('separator');
            $a.text(menuItem[MENU_IDX_NAME]);

            return $a;
        }

        var $span = $('<span></span>');
        $span.text(menuItem[MENU_IDX_NAME]);
        $span.appendTo($a);

        // Submenu
        if(hasSubmenu(menuItem))
            $span.addClass('hassubmenu');

        return $a;
    }

    function createMenu($menuItems, depth)
    {
        var $a = $rootNode;
        var $w = $(window);
        var nItems = $menuItems.length;
        var availableHeight = $w.height() - (SIZE_BORDER_HEIGHT * 2) - SIZE_SHADOW_HEIGHT;
        var nItemsThatCanFit = Math.floor(Math.max(0, availableHeight) / SIZE_MENUITEM_HEIGHT);

        // 1 column
        if(nItemsThatCanFit >= nItems)
        {
            var $outerDiv = $('<div class="menu-outer"></div>');
            var $innerDiv = $('<div class="menu-inner"></div>');

            $menuItems.each(function () { $innerDiv.append(this) });
            $outerDiv.append($innerDiv);

            $outerDiv.contextmenu($WH.rf);                  // aowow custom: prevent browser context menu when right clicking to get our context menu as it placed under the mouse cursor

            return $outerDiv;
        }

        // Multiple columns
        var nColumns = Math.min(MAX_COLUMNS, Math.ceil(nItems / nItemsThatCanFit));
        var nItemsPerColumn = Math.ceil(nItems / nColumns);
        var nItemsAdded = 0;
        var nItemsRemaining = nItems;
        var $holder = $('<div></div>');

        while(nItemsRemaining > 0)
        {
            var $outerDiv = $('<div class="menu-outer"></div>');
            var $innerDiv = $('<div class="menu-inner"></div>');

            var nItemsToAdd = Math.min(nItemsRemaining, nItemsPerColumn);
            var start = nItemsAdded;
            var end   = start + nItemsToAdd;

            $menuItems.slice(start, end).each(function() { $innerDiv.append(this) });
            $outerDiv.append($innerDiv);
            $holder.append($outerDiv);

            nItemsAdded     += nItemsToAdd;
            nItemsRemaining -= nItemsToAdd;
        }

        return $holder;
    }

    function getMenuPosition($div, depth, x, y)
    {
        if(depth == 0)
            return getFirstMenuPosition($div, depth, x, y);

        return getSubmenuPosition($div, depth);
    }

    function getFirstMenuPosition($div, depth, x, y)
    {
        var viewport   = g_getViewport();
        var menuWidth  = $div.width();
        var menuHeight = $div.height();
        var menuWidthShadow  = menuWidth  + SIZE_SHADOW_WIDTH;
        var menuHeightShadow = menuHeight + SIZE_SHADOW_HEIGHT;

        var showingAtCursor = (x != null && y != null);
        if(showingAtCursor)
        {
            if(y + menuHeightShadow > viewport.b) // Make sure menu doesn't overflow vertically
                y = Math.max(viewport.t, viewport.b - menuHeightShadow);  // Place along the bottom edge if it does
        }
        else
        {
            var $a = $rootNode;
            var offset = $a.offset();

            // Place underneath the root node by default
            x = offset.left
            y = offset.top + $a.outerHeight();

            // Show menu upwards if it's going to be outside the viewport otherwise (only if there's room)
            if(y + menuHeightShadow > viewport.b && offset.top >= menuHeightShadow)
                y = offset.top - menuHeightShadow;
        }

        if(x + menuWidthShadow > viewport.r) // Make sure menu doesn't overflow horizontally
            x = Math.max(viewport.l, viewport.r - menuWidthShadow); // Place along the right edge if it does

        return {x: x, y: y};
    }

    function getSubmenuPosition($div, depth)
    {
        var viewport   = g_getViewport();
        var menuWidth  = $div.width();
        var menuHeight = $div.height();
        var menuWidthShadow  = menuWidth  + SIZE_SHADOW_WIDTH;
        var menuHeightShadow = menuHeight + SIZE_SHADOW_HEIGHT;

        var $a = openLinks[depth - 1];
        var offset = $a.offset();
        var openToTheLeft = false;

        // Show to the right of the menu item
        x = offset.left + $a.outerWidth() - 5;
        y = offset.top - 2;

        if(x + menuWidthShadow > viewport.r) // Make sure menu doesn't overflow horizontally
            openToTheLeft = true;

        if(openToTheLeft)
            x = Math.max(viewport.l, offset.left - menuWidth);

        if(y + menuHeightShadow > viewport.b) // Make sure menu doesn't overflow vertically
            y = Math.max(viewport.t, viewport.b - menuHeightShadow);  // Place along the bottom edge if it does

        return {x: x, y: y};
    }

    function revealDiv($div, animated)
    {
        if(animated)
        {
            $div.css({
                opacity: '0'
            })
            .show()
            .animate({
                opacity: '1'
            }, 'fast', null, doneRevealing);
        }
        else
            $div.show();
    }

    function doneRevealing(a)
    {
        $(this).css('opacity', ''); // Remove opacity once animation is over to prevent a bug in IE8
    }

    function hideMenus(depth)
    {
        while(visibleMenus[depth])
        {
            visibleMenus[depth].stop().hide();
            visibleMenus[depth] = null;

            ++depth;
        }
    }

    function closeLinks(depth)
    {
        while(openLinks[depth])
        {
            openLinks[depth].removeClass('open');
            openLinks[depth] = null;

            ++depth;
        }
    }

    function isMenuVisible(depth)
    {
        return visibleMenus[depth || 0] != null;
    }

    // MENU UTILITY FUNCTIONS

    function getItemId(menuItem)
    {
        return menuItem[MENU_IDX_ID];
    }

    function isSeparator(menuItem)
    {
        return menuItem[MENU_IDX_ID] == null; // Separators don't have an ID
    }

    function hasSeparators(menu)
    {
        return $WH.in_array(menu, true, isSeparator) != -1;
    }

    function hasSubmenu(menuItem)
    {
        return menuItem[MENU_IDX_SUB] != null;
    }

    function findItemPosById(menu, id)
    {
        return $WH.in_array(menu, id, getItemId);
    }

    function hasPermissions(menuItem)
    {
        var opt = self.getItemOpt(menuItem);

        if(opt.requiredAccess && !User.hasPermissions(opt.requiredAccess))
            return false;

        return true;
    }

    function isItemVisible(menuItem)
    {
        if(!hasPermissions(menuItem))
            return false;

        if(hasSubmenu(menuItem))
        {
            if(!hasVisibleItems(menuItem[MENU_IDX_SUB]))
                return false;
        }

        return true;
    }

    function hasVisibleItems(menu)
    {
        return $WH.in_array(menu, true, isSubitemVisible) != -1;
    }

    function isSubitemVisible(menuItem)
    {
        return !isSeparator(menuItem) && hasPermissions(menuItem);
    }

    function getUniqueId(menu)
    {
        if(menu.uniqueId == null)
            menu.uniqueId = nextUniqueId++;

        return menu.uniqueId;
    }

    function traverseMenu(menu, func)
    {
        $.each(menu, function(idx, menuItem)
        {
            traverseMenuItem(menuItem, func);
        });
    }

    function traverseMenuItem(menuItem, func)
    {
        func(menuItem);
        if(hasSubmenu(menuItem))
            traverseMenu(menuItem[MENU_IDX_SUB], func);
    }

    function fixUrls(menu, url, opt, depth)
    {
        $.each(menu, function(idx, menuItem)
        {
            if(menuItem === undefined) return;
            if(isSeparator(menuItem)) return;

            if(menuItem[MENU_IDX_URL] == null) // Don't override if already set
                menuItem[MENU_IDX_URL] = url + menuItem[MENU_IDX_ID] + opt.hash;

            if(hasSubmenu(menuItem))
            {
                var accumulate = true;

                if(opt.useSimpleIds)
                    accumulate = false;
                else if(opt.useSimpleIdsAfter != null && depth >= opt.useSimpleIdsAfter)
                    accumulate = false;

                var nextUrl = url;
                if(accumulate)
                    nextUrl += menuItem[MENU_IDX_ID] + '.';
                fixUrls(menuItem[MENU_IDX_SUB], nextUrl, opt, depth + 1);
            }
        });
    }

    function sortMenu(menu)
    {
        menu.sort(function(a, b)
        {
            return $WH.strcmp(a[MENU_IDX_NAME], b[MENU_IDX_NAME]);
        });
    }

    function sortSeparatedMenu(menu)
    {
        // Sort each group separately so headings stay where they are.

        var implodedMenu = self.implode(menu, { createHeadinglessGroup: true });

        $.each(implodedMenu, function(idx, submenu)
        {
            sortMenu(submenu[MENU_IDX_SUB]);
        });

        explodeInto(menu, implodedMenu);
    }

    function explodeInto(menu, implodedMenu) // Reverse of implode
    {
        menu.splice(0, menu.length); // Clear menu

        $.each(implodedMenu, function(idx, menuItem)
        {
            if(menuItem[MENU_IDX_NAME])
                menu.push([, menuItem[MENU_IDX_NAME]]); // Heading

            $.each(menuItem[MENU_IDX_SUB], function(idx, menuItem)
            {
                menu.push(menuItem);
            });
        });
    }

    // EVENTS

    function beforeCreateMenuItem(menuItem)
    {
        var opt = self.getItemOpt(menuItem);

        if(opt.checkedUrl && location.href.match(opt.checkedUrl))
            menuItem.checked = true;
    }

    function beforeShowMenu(menu)
    {
        if(menu.onBeforeShow)
            menu.onBeforeShow(menu);

        $.each(menu, function(idx, menuItem)
        {
            var opt = self.getItemOpt(menuItem);

            if(opt.onBeforeShow)
                opt.onBeforeShow(menuItem);
        });
    }

    function rootNodeOver(event)
    {
        clearTimeout(timer);

        var $node = $(this);

        if(!isMenuVisible())
        {
            // Use a small delay the 1st time to prevent undesired appearances
            timer = setTimeout(show.bind(null, $node.data('menu'), $node), DELAY_BEFORESHOW);
            return;
        }

        show($node.data('menu'), $node);
    }

    function rootNodeOut(event)
    {
        clearTimeout(timer);

        if(isMenuVisible())
            timer = setTimeout(hide, DELAY_BEFOREHIDE);
    }

    function rootNodeClick(event)
    {
        clearTimeout(timer);

        self.showAtCursor($(this).data('menu'), event);
    }

    function menuDivOver(event)
    {
        clearTimeout(timer);
    }

    function menuDivOut(event)
    {
        clearTimeout(timer);
        timer = setTimeout(hide, DELAY_BEFOREHIDE);
    }

    function menuItemOver(event)
    {
        clearTimeout(timer);

        var $a = $(this);
        var depth = event.data.depth;

        closeLinks(depth);

        var menuItem = $a.data('menuItem');

        var deepestDepth = depth;
        if(menuItem && hasSubmenu(menuItem))
        {
            $a.addClass('open');
            openLinks[depth] = $a;

            displayMenu(menuItem[MENU_IDX_SUB], depth + 1);

            ++deepestDepth;
        }

        hideMenus(deepestDepth + 1);
    }

    function menuItemClick(event)
    {
        var $a = $(this);
        var menuItem = $a.data('menuItem');

        if(!menuItem)
            return;

        var opt = self.getItemOpt(menuItem);

        if(opt.onClick)
            opt.onClick();
    }
};

Menu.fixUrls(mn_achievements, '?achievements=');
Menu.fixUrls(mn_classes, '?class=');
Menu.fixUrls(mn_currencies, '?currencies=');
Menu.fixUrls(mn_factions, '?factions=');
Menu.fixUrls(mn_items, '?items=');
Menu.fixUrls(mn_itemSets, '?itemsets&filter=cl=', { hash: '0-2+1' });
Menu.fixUrls(mn_npcs, '?npcs=');
Menu.fixUrls(mn_objects, '?objects=');
Menu.fixUrls(mn_petCalc,  '?petcalc=');
Menu.fixUrls(mn_pets, '?pets=');
Menu.fixUrls(mn_quests, '?quests=');
Menu.fixUrls(mn_races, '?race=');
Menu.fixUrls(mn_spells, '?spells=');
Menu.fixUrls(mn_titles, '?titles=');
Menu.fixUrls(mn_zones, '?zones=');

$(document).ready(function() // Locale is only known later
{
    // if(Locale.getId() == LOCALE_ENUS)
        // return;

    Menu.sort(mn_classes);
    Menu.sort(mn_database);
    Menu.sortSubmenus(mn_items,
    [
        [4, 1], // Armor > Cloth
        [4, 2], // Armor > Leather
        [4, 3], // Armor > Mail
        [4, 4], // Armor > Plate
        [1],    // Containers
        [0],    // Consumables
        [16],   // Glyphs
        [7],    // Trade Goods
        [6],    // Projectiles
        [9]     // Recipes
    ]);
    Menu.sort(mn_itemSets);
    Menu.sort(mn_npcs);
    Menu.sort(mn_objects);
    Menu.sort(mn_talentCalc);
    Menu.sort(mn_petCalc);
    Menu.sort(mn_pets);
    Menu.sort(mn_races);
    Menu.sort(mn_skills);
    Menu.sortSubmenus(mn_spells,
    [
        [7],  // Abilities
        [-2], // Talents
        [-3], // Pet Abilities
        [11], // Professions
        [9]   // Secondary Skills
    ]);

    // aowow - why wasn't this already sorted..?
    Menu.sortSubmenus(mn_quests,
    [
        [0],
        [1],
        [2],
        [3],
        [4],
        [5],
        [6],
        [7],
        [8],
        [9],
        [10]
    ]);
});
