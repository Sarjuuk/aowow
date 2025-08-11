var PageTemplate = new function()
{
    var self = this;

    /**********/
    /* PUBLIC */
    /**********/

    self.init = function()
    {
        // Top links
        initFavorites();
        initUserMenu();
        initFeedbackLink();
        initLanguageMenu();

        initFilterDisclosure();                             // aowow: custom (visibility toggle for filters was removed at some point)

        // UI before page contents
        initFloatingStuff();
        initTopTabs();
        initTopBar();
        initBreadcrumb();

        inited = true;
    }

    self.get = function(name)
    {
        return opt[name];
    }

    self.set = function(options)
    {
        if (!options)
            return;

        var old = {};
        $.extend(old, opt);
        $.extend(opt, options);

        opt.activeTab = parseInt(opt.activeTab);

        if (inited) // Update UI on the fly if already created
        {
            if (opt.activeTab != old.activeTab)
            {
                updateTopTabs();
                updateTopBar();
            }

            if (opt.breadcrumb != old.breadcrumb)
            {
                updateBreadcrumb();
            }
        }
    }

    self.getBreadcrumb = function()
    {
        return $bread;
    }

    self.updateBreadcrumb = function()
    {
        updateBreadcrumb();
    }

    self.expandBreadcrumb = function()
    {
        return expandBreadcrumb();
    }

    /***********/
    /* PRIVATE */
    /***********/

    var inited = false;
    var opt = {};
    var $bread;
    var $tabs;
    var checkedMenuItems = [];

    function construct()
    {
        createDomElements(); // Create nodes for which getters are available
        addBrowserClasses();
    }

    function createDomElements()
    {
        $bread = $('<div class="breadcrumb"></div>');
    }

    function addBrowserClasses()
    {
        // This is done before the <body> tag is loaded, so class names are added to the <html> tag instead
    }

    function initFavorites()
    {
        var favMenu = $('#toplinks-favorites > a');

        favMenu.text(LANG.favorites);
        if (!Favorites.hasFavorites())
            favMenu.parent().hide();

        Favorites.refreshMenu()
    }

    function initUserMenu()
    {
        var $link = $('#toplinks-user');
        if (!$link.length)
            return;

        $link.attr('href', '?user=' + g_user.name);

        var menu = [];

        // User Page
        var userPage = ['user-page', LANG.userpage, '?user=' + g_user.name, null, {checkedUrl: new RegExp('user=' + g_user.name + '$', 'i')}];
        menu.push(userPage);

        // Settings
     // var settings = ['settings', LANG.settings, 'http://' + window.location.hostname + '?account', null, {icon: g_staticUrl + '/images/icons/cog.gif', checkedUrl: /account/i}];
        var settings = ['settings', LANG.settings, '?account', null, {icon: g_staticUrl + '/images/icons/cog.gif', checkedUrl: /account/i}];
        menu.push(settings);

        // Reputation
        var reputation = ['reputation', LANG.reputation, '?reputation'];
        menu.push(reputation);

        // Guides
     // var guides = ['guides', 'My Guides', '?my-guides'];
        var guides = ['guides', LANG.myguides, '?my-guides', null, {onBeforeShow: generateGuidesSubmenu} ];
        menu.push(guides);

        addCharactersMenu(menu);
        addProfilesMenu(menu);

        // Premium Upgrade
        var premiumUpgrade;
        if (!g_user.premium)
        {
            premiumUpgrade = ['premium-upgrade', LANG.premiumupgrade, '?premium', null, {className: 'q7', checkedUrl: /premium/i}];
            menu.push(premiumUpgrade);
        }

        // Sign Out
        menu.push(['sign-out', LANG.signout, '?account=signout']);
        menu.push(['sign-out-global', LANG.logOutEverywhere, '?account=signout&global']);

        Menu.add($link, menu);
        $link.addClass('hassubmenu');
    }

    function addCharactersMenu(userMenu)
    {
        if (!g_user.characters || !g_user.characters.length)
            return;

        // Menu will be generated on the fly
        var characters = ['characters', LANG.tab_characters, '?user=' + g_user.name + '#characters', null, {onBeforeShow: generateCharactersSubmenu} ];

        userMenu.push(characters);
    }

    function addProfilesMenu(userMenu)
    {
        if (!g_user.profiles || !g_user.profiles.length)
            return;

        // Menu will be generated on the fly
        var profiles = ['profiles', LANG.tab_profiles, '?user=' + g_user.name + '#profiles', null, {onBeforeShow: generateProfilesSubmenu} ];

        userMenu.push(profiles);
    }

    function generateCharactersSubmenu(menuItem)
    {
        var submenu = [];

        // Sort by realm, region, name
        g_user.characters.sort(function(a, b)
        {
            return $WH.strcmp(a.realmname, b.realmname) || $WH.strcmp(a.region, b.region) || $WH.strcmp(a.name, b.name);
        });

        var lastKey;
        $.each(g_user.characters, function(idx, character)
        {
            if (!character.region || !character.realm || !character.name)
                return;

            // Group by realm+region
            var key = character.region + character.realm;
            if (key != lastKey)
            {
                var heading = [, character.realmname + ' (' + character.region.toUpperCase() + ')', g_getProfileRealmUrl(character)];
                submenu.push(heading);
                lastKey = key;
            }

            var menuItem = [character.id, character.name, g_getProfileUrl(character), null,
            {
                className: (character.pinned ? 'icon-star-right ' : '') + 'c' + character.classs,
             // tinyIcon: $WH.g_getProfileIcon(character.race, character.classs, character.gender, character.level, character.id, 'tiny')
             // aowow - see profile=avatar endpoint for explanation
                tinyIcon: $WH.g_getProfileIcon(character.race, character.classs, character.gender, character.level, 0, 'tiny')
            }];

            submenu.push(menuItem);
        });

        menuItem[MENU_IDX_SUB] = submenu;
    }

    function generateProfilesSubmenu(menuItem)
    {
        var submenu = [];

        // Sort by name
        g_user.profiles.sort(function(a, b)
        {
            return $WH.strcmp(a.name, b.name);
        });

        $.each(g_user.profiles, function(idx, profile)
        {
            var menuItem = [profile.id, profile.name, g_getProfileUrl(profile), null,
            {
                className: 'c' + profile.classs,
                tinyIcon: $WH.g_getProfileIcon(profile.race, profile.classs, profile.gender, profile.level, profile.icon, 'tiny')
            }];

            submenu.push(menuItem);
        });

        submenu.push([0, LANG.menu_newprofile, '?profile&new', null, {tinyIcon: 'inv_misc_questionmark'}]);

        menuItem[MENU_IDX_SUB] = submenu;
    }

    // aowow- custom
    function generateGuidesSubmenu(menuItem)
    {
        var submenu = [];

        if (!g_user.guides || !g_user.guides.length)
            return;

        // Sort by name
        g_user.guides.sort(function(a, b)
        {
            return $WH.strcmp(a.title, b.title);
        });

        $.each(g_user.guides, function(idx, guide)
        {
            var menuItem = [guide.id, guide.title, guide.url, [[guide.id, LANG.button_edit, '?guide=edit&id=' + guide.id]]];

            submenu.push(menuItem);
        });

        if (g_user.id)
            submenu.push([0, LANG.createnewguide, '?guide=new']);

        menuItem[MENU_IDX_SUB] = submenu;
    }

    function initFeedbackLink()
    {
        $('#toplinks-feedback')
        .attr('href', 'javascript:;')
        .click(function()
        {
            ContactTool.show();
        });
    }

    function initLanguageMenu()
    {
        var linkBefore = 'http://';
        var linkAfter  = location.pathname + location.search + location.hash;
        var localeId   = Locale.getId();

        var menu = [];
        var currentLocale;

        $.each(Locale.getAllByName(), function(idx, locale)
        {
            var menuItem = [
                locale.id,
                locale.description,
                g_host + '/?locale=' + locale.id,                           // aowow: edited for unsupported subdomains # linkBefore + locale.domain + linkAfter
                null,                                                       // more custom
                null                                                        // also custom
            ];

            if (typeof g_pageInfo != 'undefined' && g_pageInfo.type && g_types[g_pageInfo.type])
                menuItem[4] = {rel: g_types[g_pageInfo.type] + '=' + g_pageInfo.typeId + " domain=" + locale.domain};

            if (locale.id == localeId)
            {
                menuItem.checked = true;
                currentLocale = locale;
            }

            menu.push(menuItem);
        });

        setLanguageMenu($('#toplinks-language'), menu, currentLocale);

        $(document).ready(function()
        {
            setLanguageMenu($('#footer-links-language'), menu, currentLocale);
        });
    }

    function setLanguageMenu($node, menu, locale)
    {
        $node.attr('href', 'javascript:;');
        // $node.text(locale.description);

        $node.addClass('hassubmenu');
        Menu.add($node, menu);
    }

    function initFloatingStuff()
    {
        // Expand button
        $('#header-expandsite')
        .attr('href', 'javascript:;')
        .click(expandButtonClick);
    }

    function initTopTabs()
    {
        var $topTabs = $('#toptabs');
        if (!$topTabs.length)
            return;

        $tabs = $('<dl/>');

        $.each(mn_path, function(idx, menuItem)
        {
            var $dt = $('<dt><a><ins><big>' + menuItem[MENU_IDX_NAME].charAt(0) + '</big>' + menuItem[MENU_IDX_NAME].substr(1) + '</ins><span></span></a></dt>');
            var $a = $dt.children('a');

            Menu.linkifyItem(menuItem, $a);

            $dt.appendTo($tabs);
        });

        updateTopTabs();

        $tabs.appendTo($topTabs);
    }

    function initTopBar()
    {
        var $topBar = $('#topbar');
        if (!$topBar.length)
            return;

        // Search
        var $search = $('div.topbar-search', $topBar);

        // aowow: custom start
        $('#livesearch-generic').attr('placeholder', LANG.searchdb);
        // aowow: custom end

        // Icon
        var $icon = $('<a></a>').attr('href', 'javascript:;');

        $icon.click(searchIconClick)
        .appendTo($search);

        $('form', $search).submit(g_preventEmptyFormSubmission);
        LiveSearch.attach($('input', $search));

        var windowSize = $WH.g_getWindowSize();
        if (windowSize.w && windowSize.w < 1024)
            $('div.topbar-search input').css('width', '180px');

        updateTopBar();
    }

    function initBreadcrumb()
    {
        // (Already created)

        updateBreadcrumb();

        $bread.appendTo($('#main-precontents'));
    }

    function initFilterDisclosure()
    {
        if (opt.filter == null)
            return;

        var _ = $WH.ge('main-precontents'),
            d = $WH.ce('div'),
            a = $WH.ce('a');

        d.className = 'path-right';
        a.href = 'javascript:;';
        a.id = 'fi_toggle';
        $WH.ns(a);
        a.onclick = fi_toggle;

        if (opt.filter)
        {
            a.className = 'disclosure-on';
            $WH.ae(a, $WH.ct(LANG.fihide));
        }
        else
        {
            a.className = 'disclosure-off';
            $WH.ae(a, $WH.ct(LANG.fishow));
        }

        $WH.ae(d, a);
        $WH.ae(_, d);
    }

    function updateTopTabs()
    {
        if (!$tabs)
            return;

        var $as = $('a', $tabs);

        $.each(mn_path, function(idx, menuItem)
        {
            var $a = $($as.get(idx));

            var isActiveTab = (menuItem[MENU_IDX_ID] == opt.activeTab);
            if (isActiveTab)
            {
                $a.addClass('active');
                Menu.remove($a);
            }
            else
            {
                $a.removeClass('active');
                if (menuItem[MENU_IDX_SUB])
                    Menu.add($a, menuItem[MENU_IDX_SUB]);
            }
        });
    }

    function updateTopBar()
    {
        var $buttons = $('#topbar div.topbar-buttons');
        if (!$buttons.length)
            return;

        $buttons.empty();

        switch(opt.activeTab)
        {
            case 0: // Database
                Menu.addButtons($buttons,[
                    [0, LANG.menu_browse, null, mn_database], // Browse
                    Menu.findItem(mn_tools, [8]),             // Utilities
                    Menu.findItem(mn_tools, [8, 4])           // Random Page
                ]);
                break;

            case 1: // Tools
                // Create "Calculators" group to decrease width used
                var calculators = [
                    [, LANG.calculators],
                    Menu.findItem(mn_tools, [0]), // Talent Calculator
                    Menu.findItem(mn_tools, [2]), // Hunter Pet Calculator
                    Menu.findItem(mn_tools, [3])  // Item Comparison
                ];

                // MoP link
                if (mn_tools[2][0] == 10) {
                    var tmp = calculators[3];
                    calculators[3] = Menu.findItem(mn_tools, [10]);
                    calculators[4] = tmp;

                    Menu.addButtons($buttons, Menu.implode(calculators));
                    Menu.addButtons($buttons, Menu.implode(mn_tools.slice(4))); // Skip first 4 menu items
                } else {
                    Menu.addButtons($buttons, Menu.implode(calculators));
                    Menu.addButtons($buttons, Menu.implode(mn_tools.slice(3))); // Skip first 4 menu items
                }

                break;

            case 2: // More
                Menu.addButtons($buttons, Menu.implode(mn_more));
                break;

            case 3: // Community
                Menu.addButtons($buttons, Menu.implode(mn_community));
                Menu.addButtons($buttons, [
                    Menu.findItem(mn_tools, [8]) // Utilities
                ]);
                break;

            case 4: // Staff
                Menu.addButtons($buttons, Menu.implode(mn_staff));
                break;

            case 5: // News
                Menu.addButtons($buttons, Menu.implode(mn_news));
                break;

            case 6: // Guides // aowow - localization added
             // var entries = [ [ 1, 'List of guides', '/guides' ], [ 2, 'Write new guide', '/guide=new' ] ];
                var entries = [ [ 1, LANG.listguides, '?guides', mn_guides ], [ 2, LANG.createnewguide, '?guide=new' ] ];

                if (g_user.id)
                    entries.push([ 3, LANG.myguides, '?my-guides' ]);
                 // entries.push([ 3, 'My guides', '/my-guides' ]);

                Menu.addButtons($buttons, entries);
                break;
        }
    }

    function updateBreadcrumb()
    {
        if (!opt.breadcrumb || !opt.breadcrumb.length) // Empty
        {
            $bread.hide();
            return;
        }

        $bread.empty();

        // Uncheck previously checked menu items, if any
        if (checkedMenuItems.length)
        {
            $.each(checkedMenuItems, function() {this.checked = false;Menu.updateItem(this)});
            checkedMenuItems = [];
        }

        var path = Menu.getFullPath(mn_path, opt.breadcrumb);
        if (!path.length)
            return;

        var lastIdx = (path.length - 1);

        $.each(path, function(idx, menuItem)
        {
            var menuOpt = Menu.getItemOpt(menuItem);

            menuItem.checked = true;
            checkedMenuItems.push(menuItem);
            Menu.updateItem(menuItem);

            var $span = expandBreadcrumb();
            var $textHolder = $span;

            if (menuItem[MENU_IDX_URL])
            {
                $textHolder = $('<a/>', {
                    href: Menu.getItemUrl(menuItem)
                }).appendTo($span);
            }

            if (menuOpt.breadcrumb)
                $textHolder.text(menuOpt.breadcrumb);
            else
                $textHolder.text(menuItem[MENU_IDX_NAME]);

            Menu.add($textHolder, menuItem.parentMenu);

            $span.appendTo($bread);

            // Add ellipsis as appropriate
            if (idx == lastIdx && menuItem[MENU_IDX_SUB])
            {
                $span.addClass('breadcrumb-arrow');

                var $ellipsis = $('<span class="breadcrumb-ellipsis">...</span>');
                Menu.add($ellipsis, menuItem[MENU_IDX_SUB]);

                $ellipsis.appendTo($bread);
            }
        });

        $bread.trigger('update'); // Some features rely on this event to add stuff to the breadcrumb
        $bread.show();
    }

    function expandBreadcrumb()
    {
        $bread.children('span').last().addClass('breadcrumb-arrow');

        return $('<span/>').appendTo($bread);
    }

    // EVENTS

    function expandButtonClick()
    {
        $('#sidebar, #header-expandsite').remove();

        if ($('#layout').hasClass('nosidebar'))
            return;

        // Free room allocated for the sidebar
        $('#wrapper').animate(
            {'margin-right': '10px'},
            333,
            null,
            function()
            {
                $('#wrapper').css('margin-right', '0px');
                $('#layout').addClass('nosidebar');
            }
        );
    }

    function searchIconClick()
    {
        $(this).prev('form').submit().children('input').focus();
    }

    construct();
}
