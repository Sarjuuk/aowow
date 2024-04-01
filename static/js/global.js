// Needed for IE because it's dumb
'abbr article aside audio canvas details figcaption figure footer header hgroup mark menu meter nav output progress section summary time video'.replace(/\w+/g,function(n){document.createElement(n)})

/*********/
/* ROLES */
/*********/

var U_GROUP_TESTER     = 0x1;
var U_GROUP_ADMIN      = 0x2;
var U_GROUP_EDITOR     = 0x4;
var U_GROUP_MOD        = 0x8;
var U_GROUP_BUREAU     = 0x10;
var U_GROUP_DEV        = 0x20;
var U_GROUP_VIP        = 0x40;
var U_GROUP_BLOGGER    = 0x80;
var U_GROUP_PREMIUM    = 0x100;
var U_GROUP_LOCALIZER  = 0x200;
var U_GROUP_SALESAGENT = 0x400;
var U_GROUP_SCREENSHOT = 0x800;
var U_GROUP_VIDEO      = 0x1000;
var U_GROUP_APIONLY    = 0x2000;
var U_GROUP_PENDING    = 0x4000;

/******************/
/* ROLE SHORTCUTS */
/******************/

var U_GROUP_STAFF               = U_GROUP_ADMIN   | U_GROUP_EDITOR    | U_GROUP_MOD | U_GROUP_BUREAU | U_GROUP_DEV | U_GROUP_BLOGGER | U_GROUP_LOCALIZER | U_GROUP_SALESAGENT;
var U_GROUP_EMPLOYEE            = U_GROUP_ADMIN   | U_GROUP_BUREAU    | U_GROUP_DEV;
var U_GROUP_GREEN_TEXT          = U_GROUP_MOD     | U_GROUP_BUREAU    | U_GROUP_DEV;
var U_GROUP_PREMIUMISH          = U_GROUP_PREMIUM | U_GROUP_EDITOR;
var U_GROUP_MODERATOR           = U_GROUP_ADMIN   | U_GROUP_MOD       | U_GROUP_BUREAU;
var U_GROUP_COMMENTS_MODERATOR  = U_GROUP_BUREAU  | U_GROUP_MODERATOR | U_GROUP_LOCALIZER;
var U_GROUP_PREMIUM_PERMISSIONS = U_GROUP_PREMIUM | U_GROUP_STAFF     | U_GROUP_VIP;

var g_users = {};
var g_favorites = [];
var g_customColors = {};

function g_isUsernameValid(username) {
    return (username.match(/[^a-z0-9]/i) == null && username.length >= 4 && username.length <= 16);
}

var User = new function() {
    var self = this;

    /**********/
    /* PUBLIC */
    /**********/

    self.hasPermissions = function(roles) {
        if (!roles) {
            return true;
        }

        return !!(g_user.roles & roles);
    }

    /**********/
    /* PRIVATE */
    /**********/

};

function Ajax(url, opt) {
    if (!url) {
        return;
    }

    var _;

    try { _ = new XMLHttpRequest() }
    catch(e) {
        try { _ = new ActiveXObject("Msxml2.XMLHTTP") }
        catch(e) {
            try { _ = new ActiveXObject("Microsoft.XMLHTTP") }
            catch(e) {
                if (window.createRequest) {
                    _ = window.createRequest();
                }
                else {
                    alert(LANG.message_ajaxnotsupported);
                    return;
                }
            }
        }
    }

    this.request = _;

    $WH.cO(this, opt);
    this.method = this.method || (this.params && 'POST') || 'GET';

    _.open(this.method, url, this.async == null ? true : this.async);
    _.onreadystatechange = Ajax.onReadyStateChange.bind(this);

    if (this.method.toUpperCase() == 'POST') {
        _.setRequestHeader('Content-Type', (this.contentType || 'application/x-www-form-urlencoded') + '; charset=' + (this.encoding || 'UTF-8'));
    }

    _.send(this.params);
}

Ajax.onReadyStateChange = function() {
    if (this.request.readyState == 4) {
        if (this.request.status == 0 || (this.request.status >= 200 && this.request.status < 300)) {
            this.onSuccess != null && this.onSuccess(this.request, this);
        }
        else {
            this.onFailure != null && this.onFailure(this.request, this);
        }

        if (this.onComplete != null) {
            this.onComplete(this.request, this);
        }
    }
};

var DomContentLoaded = new function() {
    var _now = [];
    var _del = [];

    this.now = function() {
        $WH.array_apply(_now, function(f) {
            f();
        });
    };

    this.delayed = function() {
        $WH.array_apply(_del, function(f) {
            f();
        });

        DomContentLoaded = null;
    };

    this.addEvent = function(f) {
        _now.push(f);
    };

    this.addDelayedEvent = function(f) {
        _del.push(f);
    }
};


/*
Global functions related to DOM manipulation, events & forms that jQuery doesn't already provide
*/

function g_addCss(css) {
    var style = $WH.ce('style');
    style.type = 'text/css';

    if (style.styleSheet) { // ie
        style.styleSheet.cssText = css;
    }
    else {
        $WH.ae(style, $WH.ct(css));
    }

    var a = $WH.gE(document, 'head')[0];
    $WH.ae(a, style);
}

function g_setTextNodes(n, text) {
    if (n.nodeType == 3) {
        n.nodeValue = text;
    }
    else {
        for (var i = 0; i < n.childNodes.length; ++i) {
            g_setTextNodes(n.childNodes[i], text);
        }
    }
}

function g_setInnerHtml(n, text, nodeType) {
    if (n.nodeName.toLowerCase() == nodeType) {
        n.innerHTML = text;
    }
    else {
        for (var i = 0; i < n.childNodes.length; ++i) {
            g_setInnerHtml(n.childNodes[i], text, nodeType);
        }
    }
}

function g_getFirstTextContent(node) {
    for (var i = 0; i < node.childNodes.length; ++i) {
        if (node.childNodes[i].nodeName == '#text') {
            return node.childNodes[i].nodeValue;
        }

        var ret = g_getFirstTextContent(node.childNodes[i]);
        if (ret) {
            return ret;
        }
    }

    return false;
}

function g_getTextContent(el) {
    var txt = '';
    for (var i = 0; i < el.childNodes.length; ++i) {
        if (el.childNodes[i].nodeValue) {
            txt += el.childNodes[i].nodeValue;
        }
        else if (el.childNodes[i].nodeName == 'BR') {
            if ($WH.Browser.ie67) {
                txt += '\r';
            }
            else {
                txt += '\n';
            }
        }
        txt += g_getTextContent(el.childNodes[i]);
    }
    return txt;
}

function g_toggleDisplay(el) {
    el = $(el);
    el.toggle();
    if (el.is(':visible')) {
        return true;
    }

    return false;
}

function g_enableScroll(enabled) {
    if (!enabled) {
        $WH.aE(document, 'mousewheel',     g_enableScroll.F);
        $WH.aE(window,   'DOMMouseScroll', g_enableScroll.F)
    }
    else {
        $WH.dE(document, 'mousewheel',     g_enableScroll.F);
        $WH.dE(window,   'DOMMouseScroll', g_enableScroll.F)
    }
}
g_enableScroll.F = function(e) {
    if (e.stopPropagation) {
        e.stopPropagation();
    }
    if (e.preventDefault) {
        e.preventDefault();
    }

    e.returnValue = false;
    e.cancelBubble = true;

    return false;
};

// from http://blog.josh420.com/archives/2007/10/setting-cursor-position-in-a-textbox-or-textarea-with-javascript.aspx
function g_setCaretPosition(elem, caretPos) {
    if (!elem) {
        return;
    }

    if (elem.createTextRange) {
        var range = elem.createTextRange();
        range.move('character', caretPos);
        range.select();
    }
    else if (elem.selectionStart != undefined) {
        elem.focus();
        elem.setSelectionRange(caretPos, caretPos);
    }
    else {
        elem.focus();
    }
}

function g_insertTag(where, tagOpen, tagClose, repFunc) {
    var n = $WH.ge(where);

    n.focus();
    if (n.selectionStart != null) {
        var
            s = n.selectionStart,
            e = n.selectionEnd,
            sL = n.scrollLeft,
            sT = n.scrollTop;

            var selectedText = n.value.substring(s, e);
        if (typeof repFunc == 'function') {
            selectedText = repFunc(selectedText);
        }

        n.value = n.value.substr(0, s) + tagOpen + selectedText + tagClose + n.value.substr(e);
        n.selectionStart = n.selectionEnd = e + tagOpen.length;

        n.scrollLeft = sL;
        n.scrollTop  = sT;
    }
    else if (document.selection && document.selection.createRange) {
        var range = document.selection.createRange();

        if (range.parentElement() != n) {
            return;
        }

        var selectedText = range.text;
        if (typeof repFunc == 'function') {
            selectedText = repFunc(selectedText);
        }

        range.text = tagOpen + selectedText + tagClose;
/*
        range.moveEnd("character", -tagClose.length);
        range.moveStart("character", range.text.length);

        range.select();
*/
    }

    if (n.onkeyup) {
        n.onkeyup();
    }
}

function g_onAfterTyping(input, func, delay) {
    var timerId;
    var ldsgksdgnlk623 = function() {
        if (timerId) {
            clearTimeout(timerId);
            timerId = null;
        }
        timerId = setTimeout(func, delay);
    };
    input.onkeyup = ldsgksdgnlk623;
}

function g_onClick(el, func) {
    var firstEvent = 0;

    function rightClk(n) {
        if (firstEvent) {
            if (firstEvent != n) {
                return;
            }
        }
        else {
            firstEvent = n;
        }

        func(true);
    }

    el.onclick = function(e) {
        e = $WH.$E(e);

        if (e._button == 2) { // middle click
            return true;
        }

        return false;
    }

    el.oncontextmenu = function() {
        rightClk(1);

        return false;
    };

    el.onmouseup = function(e) {
        e = $WH.$E(e);

        if (e._button == 3 || e.shiftKey || e.ctrlKey) { // Right/Shift/Ctrl
            rightClk(2);
        }
        else if (e._button == 1) { // Left
            func(false);
        }

        return false;
    }
}

function g_isLeftClick(e) {
    e = $WH.$E(e);
    return (e && e._button == 1);
}

function g_preventEmptyFormSubmission() { // Used on the homepage and in the top bar
    if (!$.trim(this.elements[0].value)) {
        return false;
    }
}

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
        if(!options)
            return;

        var old = {};
        $.extend(old, opt);
        $.extend(opt, options);

        opt.activeTab = parseInt(opt.activeTab);

        if(inited) // Update UI on the fly if already created
        {
            if(opt.activeTab != old.activeTab)
            {
                updateTopTabs();
                updateTopBar();
            }

            if(opt.breadcrumb != old.breadcrumb)
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
        if($WH.Browser.ie6) $(document.documentElement).addClass('ie6 ie67 ie678');
        if($WH.Browser.ie7) $(document.documentElement).addClass('ie7 ie67 ie678');
        if($WH.Browser.ie8) $(document.documentElement).addClass('ie8 ie678');
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
        if(!$link.length)
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
        if(!g_user.premium)
        {
            premiumUpgrade = ['premium-upgrade', LANG.premiumupgrade, '?premium', null, {className: 'q7', checkedUrl: /premium/i}];
            menu.push(premiumUpgrade);
        }

        // Sign Out
        menu.push(['sign-out', LANG.signout,  '?account=signout']);

        Menu.add($link, menu);
        $link.addClass('hassubmenu');
    }

    function addCharactersMenu(userMenu)
    {
        if(!g_user.characters || !g_user.characters.length)
            return;

        // Menu will be generated on the fly
        var characters = ['characters', LANG.tab_characters, '?user=' + g_user.name + '#characters', null, {onBeforeShow: generateCharactersSubmenu} ];

        userMenu.push(characters);
    }

    function addProfilesMenu(userMenu)
    {
        if(!g_user.profiles || !g_user.profiles.length)
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
            if(!character.region || !character.realm || !character.name)
                return;

            // Group by realm+region
            var key = character.region + character.realm;
            if(key != lastKey)
            {
                var heading = [, character.realmname + ' (' + character.region.toUpperCase() + ')', g_getProfileRealmUrl(character)];
                submenu.push(heading);
                lastKey = key;
            }

            var menuItem = [character.id, character.name, g_getProfileUrl(character), null,
            {
                className: (character.pinned ? 'icon-star-right ' : '') + 'c' + character.classs,
             // tinyIcon: $WH.g_getProfileIcon(character.race, character.classs, character.gender, character.level, character.id, 'tiny')
             // aowow: profileId should not be nessecary here
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

            if(locale.id == localeId)
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
        if(!$topTabs.length)
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
        if(!$topBar.length)
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
        if(windowSize.w && windowSize.w < 1024)
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

        var
            _ = $WH.ge('main-precontents'),
            d = $WH.ce('div'),
            a = $WH.ce('a');

        d.className = 'path-right';
        a.href = 'javascript:;';
        a.id = 'fi_toggle';
        $WH.ns(a);
        a.onclick = fi_toggle;

        if (opt.filter) {
            a.className = 'disclosure-on';
            $WH.ae(a, $WH.ct(LANG.fihide));
        }
        else {
            a.className = 'disclosure-off';
            $WH.ae(a, $WH.ct(LANG.fishow));
        }

        $WH.ae(d, a);
        $WH.ae(_, d);
    }

    function updateTopTabs()
    {
        if(!$tabs)
            return;

        var $as = $('a', $tabs);

        $.each(mn_path, function(idx, menuItem)
        {
            var $a = $($as.get(idx));

            var isActiveTab = (menuItem[MENU_IDX_ID] == opt.activeTab);
            if(isActiveTab)
            {
                $a.addClass('active');
                Menu.remove($a);
            }
            else
            {
                $a.removeClass('active');
                if(menuItem[MENU_IDX_SUB])
                    Menu.add($a, menuItem[MENU_IDX_SUB]);
            }
        });
    }

    function updateTopBar()
    {
        var $buttons = $('#topbar div.topbar-buttons');
        if(!$buttons.length)
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
                var entries = [ [ 1, LANG.listguides, '?guides', mn_guides ], [ 2, LANG.createnewguide, '?guide=new' ] ];

                if(g_user.id)
                    entries.push([ 3, LANG.myguides, '?my-guides' ]);

                Menu.addButtons($buttons, entries);
                break;
        }
    }

    function updateBreadcrumb()
    {
        if(!opt.breadcrumb || !opt.breadcrumb.length) // Empty
        {
            $bread.hide();
            return;
        }

        $bread.empty();

        // Uncheck previously checked menu items, if any
        if(checkedMenuItems.length)
        {
            $.each(checkedMenuItems, function() {this.checked = false;Menu.updateItem(this)});
            checkedMenuItems = [];
        }

        var path = Menu.getFullPath(mn_path, opt.breadcrumb);
        if(!path.length)
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

            if(menuItem[MENU_IDX_URL])
            {
                $textHolder = $('<a/>', {
                    href: Menu.getItemUrl(menuItem)
                }).appendTo($span);
            }

            if(menuOpt.breadcrumb)
                $textHolder.text(menuOpt.breadcrumb);
            else
                $textHolder.text(menuItem[MENU_IDX_NAME]);

            Menu.add($textHolder, menuItem.parentMenu);

            $span.appendTo($bread);

            // Add ellipsis as appropriate
            if(idx == lastIdx && menuItem[MENU_IDX_SUB])
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
        $bread.children('span:last').addClass('breadcrumb-arrow');

        return $('<span/>').appendTo($bread);
    }

    // EVENTS

    function expandButtonClick()
    {
        $('#sidebar, #header-expandsite').remove();

        if($('#layout').hasClass('nosidebar'))
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


/*
Global functions related to UI elements and effects
*/

function g_getReputationPlusAchievementText(gold, silver, copper, reputation) {
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

function g_addTooltip(element, text, className) {
    if (!className && text.indexOf('<table>') == -1) {
        className = 'q';
    }

    $WH.Tooltip.simple(element, text, className);
}

function g_addStaticTooltip(icon, text, className) {
    if (!className && text.indexOf('<table>') == -1) {
        className = 'q';
    }

    icon.onmouseover = function(d) {
        $WH.Tooltip.show(icon, text, 0, 0, className);
    };

    icon.onmouseout = $WH.Tooltip.hide;
}

function g_formatTimeElapsed(delay) {
    function OMG(value, unit, abbrv) {
        if (abbrv && LANG.timeunitsab[unit] == '') {
            abbrv = 0;
        }

        if (abbrv) {
            return value + ' ' + LANG.timeunitsab[unit];
        }
        else {
            return value + ' ' + (value == 1 ? LANG.timeunitssg[unit] : LANG.timeunitspl[unit]);
        }
    }

    var
        range   = [31557600, 2629800, 604800, 86400, 3600, 60, 1],
        subunit = [1, 3, 3, -1, 5, -1, -1];

    delay = Math.max(delay, 1);

    for (var i = 3, len = range.length; i < len; ++i) {
        if (delay >= range[i]) {
            var i1 = i;
            var v1 = Math.floor(delay / range[i1]);

            if (subunit[i1] != -1) {
                var i2 = subunit[i1];
                delay %= range[i1];

                var v2 = Math.floor(delay / range[i2]);

                if (v2 > 0) {
                    return OMG(v1, i1, 1) + ' ' + OMG(v2, i2, 1);
                }
            }

            return OMG(v1, i1, 0);
        }
    }

    return '(n/a)';
}

function g_GetStaffColorFromRoles(roles) {
    if (roles & U_GROUP_ADMIN) {
        return 'comment-blue';
    }
    if (roles & U_GROUP_GREEN_TEXT) { // Mod, Bureau, Dev
        return 'comment-green';
    }
    if (roles & U_GROUP_VIP) { // VIP
        return 'comment-gold';
    }
    if (roles & U_GROUP_PREMIUMISH) { // Premium, Editor
        return 'comment-gold';
    }

    return '';
}

function g_formatDate(sp, elapsed, theDate, time, alone) {
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

    if (elapsed >= 2592000) { /* More than a month ago */
        txt = LANG.date_on + g_formatDateSimple(theDate, time);
    }
    else if (delta > 1) {
        txt = $WH.sprintf(LANG.ddaysago, delta);

        if (sp) {
            var _ = new Date();
            _.setTime(theDate.getTime() + (g_localTime - g_serverTime));
            sp.className += ' tip';
            sp.title = _.toLocaleString();
        }
    }
    else if (elapsed >= 43200) {
        if (today.getDay() == event_day.getDay()) {
            txt = LANG.today;
        }
        else {
            txt = LANG.yesterday;
        }

        txt = g_formatTimeSimple(event_day, txt);

        if (sp) {
            var _ = new Date();
            _.setTime(theDate.getTime() + (g_localTime - g_serverTime));
            sp.className += ' tip';
            sp.title = _.toLocaleString();
        }
    }
    else { /* Less than 12 hours ago */
        var txt = $WH.sprintf(LANG.date_ago, g_formatTimeElapsed(elapsed));

        if (sp) {
            var _ = new Date();
            _.setTime(theDate.getTime() + (g_localTime - g_serverTime));
            sp.className += ' tip';
            sp.title = _.toLocaleString();
        }
    }

    if (alone == 1) {
        txt = txt.substr(0, 1).toUpperCase() + txt.substr(1);
    }

    if (sp) {
        $WH.ae(sp, $WH.ct(txt));
    }
    else {
        return txt;
    }
}

function g_formatDateSimple(d, time) {
    function __twoDigits(n) {
        return (n < 10 ? '0' + n : n);
    }

    var
        b     = "",
        day   = d.getDate(),
        month = d.getMonth() + 1,
        year  = d.getFullYear();

    if (year <= 1970) {
        b += LANG.unknowndate_stc;
    }
    else {
        b += $WH.sprintf(LANG.date_simple, __twoDigits(day), __twoDigits(month), year);
    }

    if (time != null) {
        b = g_formatTimeSimple(d, b);
    }

    return b;
}

function g_formatTimeSimple(d, txt, noPrefix) {
    function __twoDigits(n) {
        return (n < 10 ? '0' + n : n);
    }

    var
        hours   = d.getHours(),
        minutes = d.getMinutes();

    if (txt == null) {
        txt = '';
    }

    txt += (noPrefix ? ' ' : LANG.date_at);

    if (hours == 12) {
        txt += LANG.noon;
    }
    else if (hours == 0) {
        txt += LANG.midnight;
    }
    else if (hours > 12) {
        txt += (hours - 12) + ':' + __twoDigits(minutes) + ' ' + LANG.pm;
    }
    else {
        txt += hours + ':' + __twoDigits(minutes) + ' ' + LANG.am;
    }

    return txt;
}

function g_createGlow(txt, cn) {
    var s = $WH.ce('span');

    for (var i = -1; i <= 1; ++i) {
        for (var j = -1; j <= 1; ++j) {
            var d = $WH.ce('div');
            d.style.position = 'absolute';
            d.style.whiteSpace = 'nowrap';
            d.style.left = i + 'px';
            d.style.top = j + 'px';

            if (i == 0 && j == 0) {
                d.style.zIndex = 4;
            }
            else {
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

function g_createProgressBar(opt) {
    if (opt == null) {
        opt = {};
    }

    if (typeof opt.text == 'undefined') {
        opt.text = ' ';
    }

    if (opt.color == null) {
        opt.color = 'rep0';

    }
    if (opt.width == null || opt.width > 100) {
        opt.width = 100;
    }

    var el, div;
    if (opt.hoverText) {
        el = $WH.ce('a');
        el.href = 'javascript:;';
    }
    else {
        el = $WH.ce('span');
    }

    el.className = 'progressbar';

    if (opt.text || opt.hoverText) {
        div = $WH.ce('div');
        div.className = 'progressbar-text';

        if (opt.text) {
            var del = $WH.ce('del');
            $WH.ae(del, $WH.ct(opt.text));
            $WH.ae(div, del);
        }

        if (opt.hoverText) {
            var ins = $WH.ce('ins');
            $WH.ae(ins, $WH.ct(opt.hoverText));
            $WH.ae(div, ins);
        }

        $WH.ae(el, div);
    }

    div = $WH.ce('div');
    div.className = 'progressbar-' + opt.color;
    div.style.width = opt.width + '%';
    if (opt.height) {
        div.style.height = opt.height;
    }

    $WH.ae(div, $WH.ct(String.fromCharCode(160)));
    $WH.ae(el, div);

    if (opt.text) {
        var div = $WH.ce('div');
        div.className = 'progressbar-text progressbar-hidden';
        $WH.ae(div, $WH.ct(opt.text));
        $WH.ae(el, div);
    }

    return el;
}

function g_createReputationBar(totalRep) {
    var P = g_createReputationBar.P;

    if (!totalRep) {
        totalRep = 0;
    }

    totalRep += 42000;
    if (totalRep < 0) {
        totalRep = 0;
    }
    else if (totalRep > 84999) {
        totalRep = 84999;
    }

    var
        currentRep = totalRep,
        maxRep,
        standing = 0;

    for (var i = 0, len = P.length; i < len; ++i) {
        if (P[i] > currentRep) {
            break;
        }

        if (i < len - 1) {
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

function g_createAchievementBar(points, outOf, overall, bonus) {
    if (!points) {
        points = 0;
    }

    var opt = {
        text: points + (bonus > 0 ? '(+' + bonus + ')' : '') + (outOf > 0 ? ' / ' + outOf : ''),
        color: (overall ? 'rep7' : 'ach' + (outOf > 0 ? 0 : 1)),
        width: (outOf > 0 ? parseInt(points / outOf * 100) : 100)
    };

    return g_createProgressBar(opt);
}

function g_getMoneyHtml(money, side, costItems, costCurrency, achievementPoints) {
    var
        ns   = 0,
        html = '';

    if (side == 1 || side == 'alliance') {
        side = 1;
    }
    else if (side == 2 || side == 'horde') {
        side = 2;
    }
    else {
        side = 3;
    }

    if (money >= 10000) {
        ns = 1;

        var display = Math.floor(money / 10000);
        html  += '<span class="moneygold">' + $WH.number_format(display) + '</span>';
        money %= 10000;
    }

    if (money >= 100) {
        if (ns) {
            html += ' ';
        }
        else {
            ns = 1;
        }

        var display = Math.floor(money / 100);
        html  += '<span class="moneysilver">' + display + '</span>';
        money %= 100;
    }

    if (money >= 1) {
        if (ns) {
            html += ' ';
        }
        else {
            ns = 1;
        }

        html += '<span class="moneycopper">' + money + '</span>';
    }

    if (costItems != null) {
        for (var i = 0; i < costItems.length; ++i) {
            if (ns) {
                html += ' ';
            }
            else {
                ns = 1;
            }

            var itemId = costItems[i][0];
            var count  = costItems[i][1];
            var icon   = (g_items[itemId] && g_items[itemId].icon ? g_items[itemId].icon : 'inv_misc_questionmark');

            html += '<a href="?item=' + itemId + '" class="moneyitem" style="background-image: url(' + g_staticUrl + '/images/wow/icons/tiny/' + icon.toLowerCase() + '.gif)">' + count + '</a>';
        }
    }

    if (costCurrency != null) {
        for (var i = 0; i < costCurrency.length; ++i) {
            if (ns) {
                html += ' ';
            }
            else {
                ns = 1;
            }

            var currencyId = costCurrency[i][0];
            var count      = costCurrency[i][1];
            var icon       = (g_gatheredcurrencies[currencyId] && g_gatheredcurrencies[currencyId].icon ? g_gatheredcurrencies[currencyId].icon : ['inv_misc_questionmark', 'inv_misc_questionmark']);

            if (side == 3 && icon[0] == icon[1]) {
                side = 1;
            }

            // aowow: custom start
            if (currencyId == 103) {                        // arena
                html += '<a href="?currency=' + currencyId + '" class="moneyarena tip" onmouseover="Listview.funcBox.moneyArenaOver(event)" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">' + $WH.number_format(count) + '</a>';
            }
            else if (currencyId == 104) {                   // honor
                html += '<a href="?currency=' + currencyId + '" class="money' + (side == 1 ? 'alliance' : 'horde') + ' tip" onmouseover="Listview.funcBox.moneyHonorOver(event)" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">' + (side == 3 ? '<span class="moneyalliance">' : '') + $WH.number_format(count) + (side == 3 ? '</span>' : '') + '</a>';
            }
            else {                                          // tokens
                html += '<a href="?currency=' + currencyId + '" class="icontinyr tip q1" onmouseover="Listview.funcBox.moneyCurrencyOver(' + currencyId + ', ' + count + ', event)" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()" style="background-image: url(' + g_staticUrl + '/images/wow/icons/tiny/' + icon[0].toLowerCase() + '.gif)">' +  count + '</a>';
            }
            // aowow: custom end
            // html += '<a href="?currency=' + currencyId + '" class="icontinyr tip q1" onmouseover="Listview.funcBox.moneyCurrencyOver(' + currencyId + ', ' + count + ', event)" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()" style="background-image: url(' + g_staticUrl + '/images/icons/tiny/' + icon[(side == 3 ? 1 : side - 1)].toLowerCase() + '.gif)">' + (side == 3 ? '<span class="icontinyr" style="background-image: url(' + g_staticUrl + '/images/icons/tiny/' + icon[0].toLowerCase() + '.gif)">' : '') + count + (side == 3 ? '</span>' : '') + '</a>';
        }
    }

    if (achievementPoints > 0) {
        if (ns) {
            html += ' ';
        }
        else {
            ns = 1;
        }

        html += '<span class="moneyachievement tip" onmouseover="Listview.funcBox.moneyAchievementOver(event)" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">' + $WH.number_format(achievementPoints) + '</span>';
    }

    return html;
}

function g_GetExpansionClassName(expansion) {
    switch (expansion) {
        case 0:
            return null;
        case 1:
            return "icon-bc-right";
        case 2:
            return "icon-wotlk-right";
    }

    return null;
}

function g_pickerWheel(evt) {
    evt = $WH.$E(evt);

    if (evt._wheelDelta < 0) {
        this.scrollTop += 27;
    }
    else {
        this.scrollTop -= 27;
    }
}

function g_setSelectedLink(n, group) {
    if (!g_setSelectedLink.groups) {
        g_setSelectedLink.groups = {};
    }

    var _ = g_setSelectedLink.groups;
    if (_[group]) {
        _[group].className = _[group].className.replace('selected', '');
    }

    n.className += ' selected';
    _[group] = n;
}

function g_setCheckedRow(n, group) {
    if (!g_setCheckedRow.groups) {
        g_setCheckedRow.groups = {};
    }

    var _ = g_setCheckedRow.groups;
    if (_[group]) {
        _[group].className = _[group].className.replace('checked', '');
    }

    n.className += ' checked';
    _[group] = n;
}

function g_addPages(d, opt) {
    function createPageSquare(n, customText) {
        var foo;
        if (n == opt.page) {
            foo = $WH.ce('span');
            foo.className = 'selected';
        }
        else {
            foo = $WH.ce('a');
            foo.href = (n > 1 ? opt.url + opt.sep + n + opt.pound : opt.url + opt.pound);
        }
        $WH.ae(foo, $WH.ct(customText != null ? customText : n));
        return foo;
    }

    if (!opt.pound) {
        opt.pound = '';
    }

    if (!opt.sep) {
        opt.sep = '.';
    }

    if (opt.allOrNothing && opt.nPages <= 1) {
        return;
    }

    var leftAligned = (opt.align && opt.align == 'left');

    var
        divPages = $WH.ce('div'),
        pagesNumbers,
        para = $WH.ce('var');

        divPages.className = 'pages';
    if (leftAligned) {
        divPages.className += ' pages-left';
    }

    // Pages
    if (opt.nPages > 1) {
        pagesNumbers = $WH.ce('div');
        pagesNumbers.className = 'pages-numbers';

        var minPage = Math.max(2, opt.page - 2);
        var maxPage = Math.min(opt.nPages - 1, opt.page + 2);

        var elements = []; // Temporarily stored in an array so the order can be reversed when left-aligned.

        if (opt.page != opt.nPages) {
            elements.push(createPageSquare(opt.page + 1, LANG.lvpage_next + String.fromCharCode(8250)));
        }

        elements.push(createPageSquare(opt.nPages));
        if (maxPage < opt.nPages - 1) {
            var sp = $WH.ce('span');
            $WH.ae(sp, $WH.ct('...'));
            elements.push(sp);
        }

        for (var i = maxPage; i >= minPage; --i) {
            elements.push(createPageSquare(i));
        }

        if (minPage > 2) {
            var sp = $WH.ce('span');
            $WH.ae(sp, $WH.ct('...'));
            elements.push(sp);
        }
        elements.push(createPageSquare(1));

        if (opt.page != 1) {
            elements.push(createPageSquare(opt.page - 1, String.fromCharCode(8249) + LANG.lvpage_previous));
        }

        if (leftAligned) {
            elements.reverse();
        }

        for (var i = 0, len = elements.length; i < len; ++i) {
            $WH.ae(pagesNumbers, elements[i]);
        }

        pagesNumbers.firstChild.style.marginRight = '0';
        pagesNumbers.lastChild.style.marginLeft = '0';
    }

    // Number of items
    var para = $WH.ce('var');
    $WH.ae(para, $WH.ct($WH.sprintf(LANG[opt.wording[opt.nItems == 1 ? 0 : 1]], opt.nItems)));

    if (opt.nPages > 1) { // Go to page
        var sp = $WH.ce('span');
        $WH.ae(sp, $WH.ct(String.fromCharCode(8211)));
        $WH.ae(para, sp);

        var pageIcon = $WH.ce('a');
        pageIcon.className = 'gotopage';
        pageIcon.href = 'javascript:;';
        $WH.ns(pageIcon);

        if ($WH.Browser.ie) {
            $WH.ae(pageIcon, $WH.ct(' '));
        }

        pageIcon.onclick = function() {
            var n = prompt($WH.sprintf(LANG.prompt_gotopage, 1, opt.nPages), opt.page);
            if (n != null) {
                n |= 0;
                if (n != opt.page && n >= 1 && n <= opt.nPages) {
                    document.location.href = (n > 1 ? opt.url + opt.sep + n + opt.pound : opt.url + opt.pound);
                }
            }
        };
        pageIcon.onmouseover = function(event) {
            $WH.Tooltip.showAtCursor(event, LANG.tooltip_gotopage, 0, 0, 'q2');
        };
        pageIcon.onmousemove = $WH.Tooltip.cursorUpdate;
        pageIcon.onmouseout = $WH.Tooltip.hide;
        $WH.ae(para, pageIcon);
    }

    if (leftAligned) {
        $WH.ae(divPages, para);
        if (pagesNumbers) {
            $WH.ae(divPages, pagesNumbers);
        }
    }
    else {
        if (pagesNumbers) {
            $WH.ae(divPages, pagesNumbers);
        }
        $WH.ae(divPages, para);
    }

    $WH.ae(d, divPages);
}

function g_disclose(el, _this) {
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

function g_setupChangeWarning(form, elements, warningMessage) {
    /* Still skip IE since it triggers this when anchor links are clicked. */
    if ($WH.Browser.ie) {
        return;
    }

    if (!form) {
        return;
    }

    function ShowWarning() { return warningMessage; }

    form.submit(function() { window.onbeforeunload = null; });

    var initialText = [];

    for (var idx in elements) {
        var text = elements[idx];

        if (!text) {
            continue;
        }

        initialText[idx] = text.val();
        text.keydown(function() {
            for (var idx in elements) {
                var text = elements[idx];

                if (!text) {
                    continue;
                }

                if (text.val() != initialText[idx]) {
                    window.onbeforeunload = ShowWarning;
                    return;
                }

                window.onbeforeunload = null;
            }
        });
    }
}

DomContentLoaded.addEvent(function () {
    $WH.array_apply($WH.gE(document, 'dfn'), function(x){
        var text = x.title;
        x.title = '';
        x.className += ' tip';

        if (text.indexOf('LANG.') == 0) {                   // custom for less redundant texts
            text = eval(text);
        }

        g_addTooltip(x, text, 'q');
    });

// if i understand this right, this code binds an onCopy eventHandler to every child node of class="text"-nodes with the attribute unselectable="on"
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



function SetupReplies(post, comment)
{
    SetupAddEditComment(post, comment, false);
    SetupShowMoreComments(post, comment);

    post.find('.comment-reply-row').each(function () { SetupRepliesControls($(this), comment); });
    post.find('.comment-reply-row').hover(function () { $(this).find('span').attr('data-hover', 'true'); }, function () { $(this).find('span').attr('data-hover', 'false'); });
}

function SetupAddEditComment(post, comment, edit)
{
    /* Variables that will be set by Initialize() */
    var Form = null;
    var Body = null;
    var AddButton = null;
    var TextCounter = null;
    var AjaxLoader = null;
    var FormContainer = null;
    var DialogTableRowContainer = null;

    /* Constants */
    var MIN_LENGTH = 15;
    var MAX_LENGTH = 600;

    /* State keeping booleans */
    var Initialized = false;
    var Active = false;
    var Flashing = false;
    var Submitting = false;

    /* Shortcuts */
    var CommentsTable = post.find('.comment-replies > table');
    var AddCommentLink = post.find('.add-reply');
    var CommentsCount = comment.replies.length;

    if(edit)
        Open();
    else
        AddCommentLink.click(function () { Open(); });

    function Initialize()
    {
        if (Initialized)
            return;

        Initialized = true;

        var row = $('<tr/>');

        if(edit)
            row.addClass('comment-reply-row').addClass('reply-edit-row');

        row.html('<td style="width: 0"></td>' +
            '<td class="comment-form"><form><table>' +
                '<form>' +
                  '<table><tr>' +
                     '<td style="width: 600px">' +
                       '<textarea required="required" name="body" cols="68" rows="3"></textarea>' +
                     '</td>' +
                     '<td>' +
                       '<input type="submit" value="' + (edit ? LANG.save : LANG.addreply) + '" />' +
                       '<img src="' + g_staticUrl + '/images/icons/ajax.gif" class="ajax-loader" />' +
                     '</td>' +
                  '</tr>' +
                  '<tr><td colspan="2">' +
                    '<span class="text-counter">Text counter placeholder</span>' +
                  '</td></tr></table>' +
                '</form>' +
              '</td>');

        /* Set up the various variables for the controls we just created */
        Body = row.find('.comment-form textarea');
        AddButton = row.find('.comment-form input[type=submit]');
        TextCounter = row.find('.comment-form span.text-counter');
        Form = row.find('.comment-form form');
        AjaxLoader = row.find('.comment-form .ajax-loader');
        FormContainer = row.find('.comment-form');

        /* Intercept submits */
        Form.submit(function () { Submit(); return false; });

        UpdateTextCounter();

        /* This is kinda a mess.. Every browser seems to implement keyup, keydown and keypress differently.
         * - keyup: We need to use keyup to update the text counter for the simple reason we want to update it only when the user stops typing.
         * - keydown: We need to use keydown to detect the ESC key because it's the only one that works in all browsers for ESC
         * - keypress: We need to use keypress to detect Enter because it's the only one that 1) Works 2) Allows us to prevent a new line from being entered in the textarea
         * I find it very funny that in each scenario there is only one of the 3 that works, and that that one is always different from the others.
         */

        Body.keyup(function (e) { UpdateTextCounter(); });
        Body.keydown(function (e) { if (e.keyCode == 27) { Close(); return false; } }); // ESC
        Body.keypress(function (e) { if (e.keyCode == 13) { Submit(); return false; } }); // ENTER

        if(edit)
        {
            post.after(row);
            post.hide();
            Form.find('textarea').text(comment.replies[post.attr('data-idx')].body);
        }
        else
            CommentsTable.append(row);

        DialogTableRowContainer = row;
        Form.find('textarea').focus();
    }

    function Open()
    {
        if (!Initialized)
            Initialize();

        Active = true;

        if(!edit)
        {
            AddCommentLink.hide();
            post.find('.comment-replies').show();
            FormContainer.show();
            FormContainer.find('textarea').focus();
        }
    }

    function Close()
    {
        Active = false;

        if(edit)
        {
            if(DialogTableRowContainer)
                DialogTableRowContainer.remove();
            post.show();
            return;
        }

        AddCommentLink.show();
        FormContainer.hide();

        if (CommentsCount == 0)
            post.find('.comment-replies').hide();
    }

    function Submit()
    {
        if (!Active || Submitting)
            return;

        if (Body.val().length < MIN_LENGTH || Body.val().length > MAX_LENGTH)
        {
            /* Flash the char counter to attract the attention of the user. */
            if (!Flashing)
            {
                Flashing = true;
                TextCounter.animate({ opacity: '0.0' }, 150);
                TextCounter.animate({ opacity: '1.0' }, 150, null, function() { Flashing = false; });
            }

            return false;
        }

        SetSubmitState();
        $.ajax({
            type: 'POST',
            url: edit ? '?comment=edit-reply' : '?comment=add-reply',
            data: { commentId: comment.id, replyId: (edit ? post.attr('data-replyid') : 0), body: Body.val() },
            success: function (newReplies) { OnSubmitSuccess(newReplies); },
            dataType: 'json',
            error: function (jqXHR) { OnSubmitFailure(jqXHR.responseText); }
        });
        return true;
    }

    function SetSubmitState()
    {
        Submitting = true;
        AjaxLoader.show();
        AddButton.attr('disabled', 'disabled');
        FormContainer.find('.message-box').remove();
    }

    function ClearSubmitState()
    {
        Submitting = false;
        AjaxLoader.hide();
        AddButton.removeAttr('disabled');
    }

    function OnSubmitSuccess(newReplies)
    {
        comment.replies = newReplies;
        Listview.templates.comment.updateReplies(comment);
    }

    function OnSubmitFailure(error)
    {
        ClearSubmitState();
        MessageBox(FormContainer, error);
    }

    function UpdateTextCounter()
    {
        var text = '(error)';
        var cssClass = 'q0';
        var chars = Body.val().replace(/(\s+)/g, ' ').replace(/^\s*/, '').replace(/\s*$/, '').length;
        var charsLeft = MAX_LENGTH - chars;

        if (chars == 0)
            text = $WH.sprintf(LANG.replylength1_format, MIN_LENGTH);
        else if (chars < MIN_LENGTH)
            text = $WH.sprintf(LANG.replylength2_format, MIN_LENGTH - chars);
        else
        {
            text = $WH.sprintf(charsLeft == 1 ? LANG.replylength4_format : LANG.replylength3_format, charsLeft);

            if (charsLeft < 120)
                cssClass = 'q10';
            else if (charsLeft < 240)
                cssClass = 'q5';
            else if (charsLeft < 360)
                cssClass = 'q11';
        }

        TextCounter.html(text).attr('class', cssClass);
    }
}

function SetupShowMoreComments(post, comment)
{
    var ShowMoreCommentsLink = post.find('.show-more-replies');
    var CommentCell = post.find('.comment-replies');

    ShowMoreCommentsLink.click(function () { ShowMoreComments(); });

    function ShowMoreComments()
    {
        /* Replace link with ajax loader */
        ShowMoreCommentsLink.hide();
        CommentCell.append(CreateAjaxLoader());

        $.ajax({
            type: 'GET',
            url: '?comment=show-replies',
            data: { id: comment.id },
            success: function (replies) { comment.replies = replies; Listview.templates.comment.updateReplies(comment); },
            dataType: 'json',
            error: function () { OnFetchFail(); }
        });
    }

    function OnFetchFail()
    {
        ShowMoreCommentsLink.show();
        CommentCell.find('.ajax-loader').remove();

        MessageBox(CommentCell, "There was an error fetching the comments. Try refreshing the page.");
    }
}

function SetupRepliesControls(post, comment)
{
    var CommentId = post.attr('data-replyid');
    var VoteUpControl = post.find('.reply-upvote');
    var VoteDownControl = post.find('.reply-downvote');
    var FlagControl = post.find('.reply-report');
    var CommentScoreText = post.find('.reply-rating');
    var CommentActions = post.find('.reply-controls');
    var DetachButton = post.find('.reply-detach');
    var DeleteButton = post.find('.reply-delete');
    var EditButton = post.find('.reply-edit');
    var Container = comment.repliesCell;
    var Voting = false;
    var Detaching = false;
    var Deleting = false;

    EditButton.click(function() {
        SetupAddEditComment(post, comment, true);
    });

    FlagControl.click(function ()
    {
        if (Voting || !confirm(LANG.replyreportwarning_tip))
            return;

        Voting = true;
        $.ajax({
            type: 'POST',
            url: '?comment=flag-reply',
            data: { id: CommentId },
            success: function () { OnFlagSuccessful(); },
            error: function (jqXHR) { OnError(jqXHR.responseText); }
        });
    });

    VoteUpControl.click(function ()
    {
        if (VoteUpControl.attr('data-hasvoted') == 'true' || VoteUpControl.attr('data-canvote') != 'true' || Voting)
            return;

        Voting = true;
        $.ajax({
            type: 'POST',
            url: '?comment=upvote-reply',
            data: { id: CommentId },
            success: function () { OnVoteSuccessful(1); },
            error: function (jqXHR) { OnError(jqXHR.responseText); }
        });
    });

    VoteDownControl.click(function ()
    {
        if (VoteDownControl.attr('data-hasvoted') == 'true' || VoteDownControl.attr('data-canvote') != 'true' || Voting)
            return;

        Voting = true;
        $.ajax({
            type: 'POST',
            url: '?comment=downvote-reply',
            data: { id: CommentId },
            success: function () { OnVoteSuccessful(-1); },
            error: function (jqXHR) { OnError(jqXHR.responseText); }
        });
    });

    DetachButton.click(function ()
    {
        if (Detaching) {
            MessageBox(CommentActions, LANG.message_cantdetachcomment);
            return;
        }

        if (!confirm(LANG.confirm_detachcomment)) {
            return;
        }

        Detaching = true;
        $.ajax({
            type: 'POST',
            url: '?comment=detach-reply',
            data: { id: CommentId },
            success: function () { OnDetachSuccessful(); },
            error: function (jqXHR) { OnError(jqXHR.responseText); }
        });
    });

    DeleteButton.click(function ()
    {
        if (Deleting)
            return;

        if (!confirm(LANG.deletereplyconfirmation_tip))
            return;

        Deleting = true;
        $.ajax({
            type: 'POST',
            url: '?comment=delete-reply',
            data: { id: CommentId },
            success: function () { OnDeleteSuccessful(); },
            error: function (jqXHR) { OnError(jqXHR.responseText); }
        });
    });

    function OnVoteSuccessful(ratingChange)
    {
        var rating = parseInt(CommentScoreText.text());

        rating += ratingChange;

        CommentScoreText.text(rating);

        if(ratingChange > 0)
            VoteUpControl.attr('data-hasvoted', 'true');
        else
            VoteDownControl.attr('data-hasvoted', 'true');

        VoteUpControl.attr('data-canvote', 'false');
        VoteDownControl.attr('data-canvote', 'false');

        if(ratingChange > 0)
            FlagControl.remove();
        Voting = false;
    }

    function OnFlagSuccessful()
    {
        Voting = false;
        FlagControl.remove();
    }

    function OnDetachSuccessful()
    {
        post.remove();
        MessageBox(Container, LANG.message_commentdetached);
        Detaching = false;
    }

    function OnDeleteSuccessful()
    {
        post.remove();
        Deleting = false;
    }

    function OnError(text)
    {
        Voting = false;
        Detaching = false;
        Deleting = false;

        if (!text)
            text = LANG.genericerror;

        MessageBox(CommentActions, text);
    }
}

/*
Global comment-related functions
*/

function co_addYourComment() {
    tabsContribute.focus(0);
    var ta = $WH.gE(document.forms['addcomment'], 'textarea')[0];
    ta.focus();
}

function co_validateForm(f) {
    var ta = $WH.gE(f, 'textarea')[0];

    // prevent locale comments on guide pages
    var locale = Locale.getId();
    // aowow - disabled
    // if(locale != LOCALE_ENUS && $(f).attr('action') && ($(f).attr('action').replace(/^.*type=([0-9]*).*$/i, '$1')) == 100)
    if (false)
    {
        alert(LANG.message_cantpostlcomment_tip);
        return false;
    }

    if (g_user.permissions & 1) {
        return true;
    }

    if (Listview.funcBox.coValidate(ta)) {
        return true;
    }

    return false;
}


/*
Global screenshot-related functions
*/
function ss_submitAScreenshot() {
    tabsContribute.focus(1);
}

function ss_validateForm(f) {
    if (!f.elements.screenshotfile.value.length) {
        alert(LANG.message_noscreenshot);
        return false;
    }

    return true;
}

function ss_appendSticky() {
    var _ = $WH.ge('infobox-sticky-ss');

    var type   = g_pageInfo.type;
    var typeId = g_pageInfo.typeId;

    var pos = $WH.in_array(lv_screenshots, 1, function(x) {
        return x.sticky;
    });

    if (pos != -1) {
        var screenshot = lv_screenshots[pos];

        var a = $WH.ce('a');
        a.href = '#screenshots:id=' + screenshot.id;
        a.onclick = function(a) {
            ScreenshotViewer.show({ screenshots: lv_screenshots, pos: pos });
            return $WH.rf2(a);
        };

        var size = (lv_videos && lv_videos.length ? [120, 90] : [150, 150]);
        var
            img   = $WH.ce('img'),
            scale = Math.min(size[0] / screenshot.width, size[1] / screenshot.height);

        img.src    = g_staticUrl + '/uploads/screenshots/thumb/' + screenshot.id + '.jpg';
        img.width  = Math.round(scale * screenshot.width);
        img.height = Math.round(scale * screenshot.height);
        img.className = 'border';
        $WH.ae(a, img);

        $WH.ae(_, a);

        var th = $WH.ge('infobox-screenshots');
        var a = $WH.ce('a');

        if (!th) {
            var sections = $('th', _.parentNode);
            th = sections[sections.length - (lv_videos && lv_videos.length ? 2 : 1)];
        }

        $WH.ae(a, $WH.ct(th.textContent + ' (' + lv_screenshots.length + ')'));
        a.href = '#screenshots'
        a.title = $WH.sprintf(LANG.infobox_showall, lv_screenshots.length);
        a.onclick = function() {
            tabsRelated.focus((lv_videos && lv_videos.length) || (g_user && g_user.roles & (U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO)) ? -2 : -1);
            return false;
        };
        $WH.ee(th);
        $WH.ae(th, a);
    }
    else {
        var a;

        if (g_user.id > 0) {
            a = '<a href="javascript:;" onclick="ss_submitAScreenshot(); return false">';
        }
        else {
            a = '<a href="?account=signin">';
        }
        _.innerHTML = $WH.sprintf(LANG.infobox_noneyet, a + LANG.infobox_submitone + '</a>')
    }
}

var g_screenshots = {};
var ScreenshotViewer = new function() {
    var
        screenshots,
        pos,
        imgWidth,
        imgHeight,
        scale,
        desiredScale,
        oldHash,
        mode = 0,
        collectionId,
        container,
        screen,
        imgDiv,
        aPrev,
        aNext,
        aCover,
        aOriginal,
        divFrom,
        divCaption,
        loadingImage,
        lightboxComponents;

    function computeDimensions(captionExtraHeight) {
        var screenshot = screenshots[pos];

        var availHeight = Math.max(50, Math.min(618, $WH.g_getWindowSize().h - 72 - captionExtraHeight));

        if (mode != 1 || screenshot.id || screenshot.resize) {
            desiredScale = Math.min(772 / screenshot.width, 618 / screenshot.height);
            scale        = Math.min(772 / screenshot.width, availHeight / screenshot.height);
        }
        else {
            desiredScale = scale = 1;
        }

        if (desiredScale > 1) {
            desiredScale = 1;
        }

        if (scale > 1) {
            scale = 1;
        }

        imgWidth = Math.round(scale * screenshot.width);
        imgHeight = Math.round(scale * screenshot.height);
        var lbWidth = Math.max(480, imgWidth);

        Lightbox.setSize(lbWidth + 20, imgHeight + 52 + captionExtraHeight);

        if ($WH.Browser.ie6) {
            screen.style.width = lbWidth + 'px';
            if (screenshots.length > 1) {
                aPrev.style.height = aNext.style.height = imgHeight + 'px'
            } else {
                aCover.style.height = imgHeight + 'px'
            }
        }
        if (captionExtraHeight) {
            imgDiv.firstChild.width = imgWidth;
            imgDiv.firstChild.height = imgHeight;
        }
    }

    function getPound(pos) {
        var
            screenshot = screenshots[pos],
            buff = '#screenshots:';

        if (mode == 0) {
            buff += 'id=' + screenshot.id;
        }
        else {
            buff += collectionId + ':' + (pos + 1);
        }
        return buff;
    }

    function render(resizing) {
        if (resizing && (scale == desiredScale) && $WH.g_getWindowSize().h > container.offsetHeight) {
            return;
        }
        container.style.visibility = 'hidden';
        var
            screenshot = screenshots[pos],
            resized = (screenshot.width > 772 || screenshot.height > 618);

        computeDimensions(0);

        var url = (screenshot.url ? screenshot.url : g_staticUrl + '/uploads/screenshots/' + (resized ? 'resized/': 'normal/') + screenshot.id + '.jpg');

        var html =
            '<img src="' + url + '"'
        + ' width="' + imgWidth + '"'
        + ' height="' + imgHeight + '"';
        if ($WH.Browser.ie6) {
            html += ' galleryimg="no"';
        }
        html += '>';

        imgDiv.innerHTML = html;

        if (!resizing) {
            g_trackEvent('Screenshots', 'Show', screenshot.id + ( (screenshot.caption && screenshot.caption.length) ? ' (' + screenshot.caption + ')' : ''));

            if (screenshot.url) {
                aOriginal.href = url;
            }
            else {
                aOriginal.href = g_staticUrl + '/uploads/screenshots/normal/' + screenshot.id + '.jpg';
            }
            if (!screenshot.user && typeof g_pageInfo == 'object') {
                screenshot.user = g_pageInfo.username;
            }

            var
                hasFrom1 = (screenshot.date && screenshot.user),
                hasFrom2 = (screenshots.length > 1);

            if (hasFrom1) {
                var
                    postedOn = new Date(screenshot.date),
                    elapsed = (g_serverTime - postedOn) / 1000;

                var a = divFrom.firstChild.childNodes[1];
                a.href = '?user=' + screenshot.user;
                a.innerHTML = screenshot.user;

                var s = divFrom.firstChild.childNodes[3];

                $WH.ee(s);
                g_formatDate(s, elapsed, postedOn);

                divFrom.firstChild.style.display = '';
            }
            else {
                divFrom.firstChild.style.display = 'none';
            }

            var s = divFrom.childNodes[1];
            $WH.ee(s);
            if (screenshot.user) {
                if (hasFrom1) {
                    $WH.ae(s, $WH.ct(' ' + LANG.dash + ' '));
                }
                var a = $WH.ce('a');
                a.href = 'javascript:;';
                a.onclick = ContactTool.show.bind(ContactTool, {
                    mode: 3,
                    screenshot: screenshot
                });
                a.className = 'icon-report'
                g_addTooltip(a, LANG.report_tooltip, 'q2');
                $WH.ae(a, $WH.ct(LANG.report));
                $WH.ae(s, a);
            }

            s = divFrom.childNodes[2];

            if (hasFrom2) {
                var buff = '';
                if (screenshot.user) {
                    buff = LANG.dash;
                }
                buff += (pos + 1) + LANG.lvpage_of + screenshots.length;

                s.innerHTML = buff;
                s.style.display = '';
            }
            else {
                s.style.display = 'none';
            }

            divFrom.style.display = (hasFrom1 || hasFrom2 ? '': 'none');

            var hasCaption = (screenshot.caption != null && screenshot.caption.length);
            var hasSubject = (screenshot.subject != null && screenshot.subject.length && screenshot.type && screenshot.typeId);

            if (hasCaption || hasSubject) {
                var html = '';

                if (hasSubject) {
                    html += LANG.types[screenshot.type][0] + LANG.colon;
                    html += '<a href="?' + g_types[screenshot.type] + '=' + screenshot.typeId + '">';
                    html += screenshot.subject;
                    html += '</a>';
                }

                if (hasCaption) {
                    if (hasSubject) {
                        html += LANG.dash;
                    }
                    html += (screenshot.noMarkup ? screenshot.caption : Markup.toHtml(screenshot.caption, { mode: Markup.MODE_SIGNATURE }));
                }

                divCaption.innerHTML = html;
                divCaption.style.display = '';
            }
            else {
                divCaption.style.display = 'none';
            }

            if (screenshots.length > 1) {
                aPrev.href = getPound(peekPos(-1));
                aNext.href = getPound(peekPos(1));

                aPrev.style.display = aNext.style.display = '';
                aCover.style.display = 'none';
            }
            else {
                aPrev.style.display = aNext.style.display = 'none';
                aCover.style.display = '';
            }

            location.replace(getPound(pos));
        }

        Lightbox.reveal();

        if (divCaption.offsetHeight > 18) {
            computeDimensions(divCaption.offsetHeight - 18);
        }
        container.style.visibility = 'visible';
    }

    function peekPos(change) {
        var foo = pos;
        foo += change;

        if (foo < 0) {
            foo = screenshots.length - 1;
        }
        else if (foo >= screenshots.length) {
            foo = 0;
        }

        return foo;
    }

    function prevScreenshot() {
        pos = peekPos(-1);
        onRender();

        return false;
    }

    function nextScreenshot() {
        pos = peekPos(1);
        onRender();

        return false;
    }

    function onKeyUp(e) {
        e = $WH.$E(e);
        switch (e.keyCode) {
            case 37: // Left
                prevScreenshot();
                break;
            case 39: // Right
                nextScreenshot();
                break;
        }
    }

    function onResize() {
        render(1);
    }

    function onHide() {
        cancelImageLoading();

        if (screenshots.length > 1) {
            $WH.dE(document, 'keyup', onKeyUp);
        }

        if (oldHash && mode == 0) {
            if (oldHash.indexOf(':id=') != -1) {
                oldHash = '#screenshots';
            }
            location.replace(oldHash);
        }
        else {
            location.replace('#.');
        }
    }

    function onShow(dest, first, opt) {
        if (typeof opt.screenshots == 'string') {
            screenshots = g_screenshots[opt.screenshots];
            mode = 1;
            collectionId = opt.screenshots;
        }
        else {
            screenshots = opt.screenshots;
            mode = 0;
            collectionId = null;
        }
        container = dest;

        pos = 0;
        if (opt.pos && opt.pos >= 0 && opt.pos < screenshots.length) {
            pos = opt.pos;
        }

        if (first) {
            dest.className = 'screenshotviewer';

            screen = $WH.ce('div');

            screen.className = 'screenshotviewer-screen';

            aPrev = $WH.ce('a');
            aNext = $WH.ce('a');
            aPrev.className = 'screenshotviewer-prev';
            aNext.className = 'screenshotviewer-next';
            aPrev.href = 'javascript:;';
            aNext.href = 'javascript:;';

            var foo = $WH.ce('span');
            $WH.ae(foo, $WH.ce('b'));
            // var b = $WH.ce('b');
            // $WH.ae(b, $WH.ct(LANG.previous));
            // $WH.ae(foo, b);
            $WH.ae(aPrev, foo);
            var foo = $WH.ce('span');
            $WH.ae(foo, $WH.ce('b'));
            // var b = $WH.ce('b');
            // $WH.ae(b, $WH.ct(LANG.next));
            // $WH.ae(foo, b);
            $WH.ae(aNext, foo);

            aPrev.onclick = prevScreenshot;
            aNext.onclick = nextScreenshot;

            aCover = $WH.ce('a');
            aCover.className = 'screenshotviewer-cover';
            aCover.href = 'javascript:;';
            aCover.onclick = Lightbox.hide;
            var foo = $WH.ce('span');
            $WH.ae(foo, $WH.ce('b'));
            // var b = $WH.ce('b');
            // $WH.ae(b, $WH.ct(LANG.close));
            // $WH.ae(foo, b);
            $WH.ae(aCover, foo);
            if ($WH.Browser.ie6) {
                $WH.ns(aPrev);
                $WH.ns(aNext);
                aPrev.onmouseover = aNext.onmouseover = aCover.onmouseover = function() {
                    this.firstChild.style.display = 'block';
                };
                aPrev.onmouseout = aNext.onmouseout = aCover.onmouseout = function() {
                    this.firstChild.style.display = '';
                };

            }
            $WH.ae(screen, aPrev);
            $WH.ae(screen, aNext);
            $WH.ae(screen, aCover);

            imgDiv = $WH.ce('div');
            $WH.ae(screen, imgDiv);

            $WH.ae(dest, screen);

            var aClose = $WH.ce('a');
            aClose.className = 'screenshotviewer-close';
            // aClose.className = 'dialog-x';
            aClose.href = 'javascript:;';
            aClose.onclick = Lightbox.hide;
            $WH.ae(aClose, $WH.ce('span'));
            // $WH.ae(aClose, $WH.ct(LANG.close));
            $WH.ae(dest, aClose);

            aOriginal = $WH.ce('a');
            aOriginal.className = 'screenshotviewer-original';
            // aOriginal.className = 'dialog-arrow';
            aOriginal.href = 'javascript:;';
            aOriginal.target = '_blank';
            $WH.ae(aOriginal, $WH.ce('span'));
            // $WH.ae(aOriginal, $WH.ct(LANG.original));
            $WH.ae(dest, aOriginal);

            divFrom = $WH.ce('div');
            divFrom.className = 'screenshotviewer-from';
            var sp = $WH.ce('span');
            $WH.ae(sp, $WH.ct(LANG.lvscreenshot_from));
            $WH.ae(sp, $WH.ce('a'));
            $WH.ae(sp, $WH.ct(' '));
            $WH.ae(sp, $WH.ce('span'));
            $WH.ae(divFrom, sp);
            $WH.ae(divFrom, $WH.ce('span'));
            $WH.ae(divFrom, $WH.ce('span'));
            $WH.ae(dest, divFrom);

            divCaption = $WH.ce('div');
            divCaption.className = 'screenshotviewer-caption';
            $WH.ae(dest, divCaption);
            var d = $WH.ce('div');
            d.className = 'clear';
            $WH.ae(dest, d);
        }

        oldHash = location.hash;

        if (screenshots.length > 1) {
            $WH.aE(document, 'keyup', onKeyUp);
        }

        onRender();
    }

    function onRender() {
        var screenshot = screenshots[pos];
        if (!screenshot.width || !screenshot.height) {
            if (loadingImage) {
                loadingImage.onload = null;
                loadingImage.onerror = null;
            }
            else {
                container.className = '';
                lightboxComponents = [];
                while (container.firstChild) {
                    lightboxComponents.push(container.firstChild);
                    $WH.de(container.firstChild);
                }
            }

            var lightboxTimer = setTimeout(function() {
                screenshot.width = 126;
                screenshot.height = 22;
                computeDimensions(0);
                screenshot.width = null;
                screenshot.height = null;

                var div = $WH.ce('div');
                div.style.margin = '0 auto';
                div.style.width = '126px';
                var img = $WH.ce('img');
                img.src = g_staticUrl + '/images/ui/misc/progress-anim.gif';
                img.width = 126;
                img.height = 22;
                $WH.ae(div, img);
                $WH.ae(container, div);

                Lightbox.reveal();
                container.style.visiblity = 'visible';
            }, 150);

            loadingImage = new Image();
            loadingImage.onload = (function(screen, timer) {
                clearTimeout(timer);
                screen.width = this.width;
                screen.height = this.height;
                loadingImage = null;
                restoreLightbox();
                render();
            }).bind(loadingImage, screenshot, lightboxTimer);
            loadingImage.onerror = (function(timer) {
                clearTimeout(timer);
                loadingImage = null;
                Lightbox.hide();
                restoreLightbox();
            }).bind(loadingImage, lightboxTimer);
            loadingImage.src = (screenshot.url ? screenshot.url : g_staticUrl + '/uploads/screenshots/normal/' + screenshot.id + '.jpg');
        }
        else {
            render();
        }
    }

    function cancelImageLoading() {
        if (!loadingImage) {
            return;
        }

        loadingImage.onload = null;
        loadingImage.onerror = null;
        loadingImage = null;

        restoreLightbox();
    }

    function restoreLightbox() {
        if (!lightboxComponents) {
            return;
        }

        $WH.ee(container);
        container.className = 'screenshotviewer';
        for (var i = 0; i < lightboxComponents.length; ++i)
            $WH.ae(container, lightboxComponents[i]);
        lightboxComponents = null;
    }

    this.checkPound = function() {
        if (location.hash && location.hash.indexOf('#screenshots') == 0) {
            if (!g_listviews['screenshots']) { // Standalone screenshot viewer
                var parts = location.hash.split(':');
                if (parts.length == 3) {
                    var
                        collection = g_screenshots[parts[1]],
                        p = parseInt(parts[2]);

                    if (collection && p >= 1 && p <= collection.length) {
                        ScreenshotViewer.show({
                            screenshots: parts[1],
                            pos: p - 1
                        });
                    }
                }
            }
        }
    }

    this.show = function(opt) {
        Lightbox.show('screenshotviewer', {
            onShow: onShow,
            onHide: onHide,
            onResize: onResize
        }, opt);
    }

    DomContentLoaded.addEvent(this.checkPound)
};


/*
Global video-related functions
*/

var vi_thumbnails = {
    1: 'https://i3.ytimg.com/vi/$1/default.jpg' // YouTube
};

var vi_siteurls = {
    1: 'https://www.youtube.com/watch?v=$1' // YouTube
};

var vi_sitevalidation = {
    1: /^https?:\/\/www\.youtube\.com\/watch\?v=([^& ]{11})/ // YouTube
};

function vi_submitAVideo() {
    tabsContribute.focus(2);
}

function vi_validateForm(f) {
    if (!f.elements['videourl'].value.length) {
        alert(LANG.message_novideo);
        return false;
    }

    var urlmatch = false;
    for (var i in vi_sitevalidation) {
        if (f.elements['videourl'].value.match(vi_sitevalidation[i])) {
            urlmatch = true;
            break;
        }
    }

    if (!urlmatch) {
        alert(LANG.message_novideo);
        return false;
    }

    return true;
}

function vi_appendSticky() {
    var _ = $WH.ge('infobox-sticky-vi');

    var type   = g_pageInfo.type;
    var typeId = g_pageInfo.typeId;

    var pos = $WH.in_array(lv_videos, 1, function(x) {
        return x.sticky;
    });

    if (pos != -1) {
        var video = lv_videos[pos];

        var a = $WH.ce('a');
        a.href = '#videos:id=' + video.id;
        a.onclick = function(e) {
            VideoViewer.show({ videos: lv_videos, pos: pos });
            return $WH.rf2(e);
        };

        var img = $WH.ce('img');
        img.src = $WH.sprintf(vi_thumbnails[video.videoType], video.videoId);
        img.className = 'border';
        $WH.ae(a, img);

        $WH.ae(_, a);

        var th = $WH.ge('infobox-videos');
        var a = $WH.ce('a');

        if (!th) {
            var sections = $('th', _.parentNode);
            th = sections[sections.length - (lv_videos && lv_videos.length ? 2 : 1)];
        }

        $WH.ae(a, $WH.ct(th.textContent + ' (' + lv_videos.length + ')'));
        a.href = '#videos'
        a.title = $WH.sprintf(LANG.infobox_showall, lv_videos.length);
        a.onclick = function() {
            tabsRelated.focus(-1);
            return false;
        };
        $WH.ee(th);
        $WH.ae(th, a);
    }
    else {
        var a;

        if (g_user.id > 0) {
            a = '<a href="javascript:;" onclick="vi_submitAVideo(); return false">'
        }
        else {
            a = '<a href="?account=signin">'
        }

        _.innerHTML = $WH.sprintf(LANG.infobox_noneyet, a + LANG.infobox_suggestone + '</a>')
    }
}

var g_videos = [];
var VideoViewer = new function() {
    var
        videos,
        pos,
        imgWidth,
        imgHeight,
        scale,
        oldHash,
        mode = 0,
        collectionId,
        pageTitle, // IE flash embed fix
        container,
        screen,
        imgDiv,
        aPrev,
        aNext,
        aCover,
        aOriginal,
        divFrom,
        divCaption;

    function computeDimensions() {
        var video = videos[pos];

        var
            captionExtraHeight = Math.max(divCaption.offsetHeight - 18, 0),
            availHeight = Math.max(50, Math.min(520, $WH.g_getWindowSize().h - 72 - captionExtraHeight)),
            scale = Math.min(1, availHeight / 520);

        imgWidth = Math.round(scale * 880);
        imgHeight = Math.round(scale * 520);

        aPrev.style.height = aNext.style.height = aCover.style.height = (imgHeight - 95) + 'px';
        Lightbox.setSize(Math.max(480, imgWidth) + 20, imgHeight + 52 + captionExtraHeight);
    }

    function getPound(pos) {
        var
            video = videos[pos],
            buff = '#videos:';

        if (mode == 0) {
            buff += 'id=' + video.id;
        }
        else {
            buff += collectionId + ':' + (pos + 1);
        }

        return buff;
    }

    function render(resizing) {
        if (resizing && (scale == 1) && $WH.g_getWindowSize().h > container.offsetHeight) {
            return;
        }

        container.style.visibility = 'hidden';

        var video = videos[pos];

        computeDimensions();

        if (!resizing) {
            g_trackEvent('Videos', 'Show', video.id + (video.caption.length ? ' (' + video.caption + ')' : ''));

            if (video.videoType == 1) {
                imgDiv.innerHTML = Markup.toHtml('[youtube=' + video.videoId + ' width=' + imgWidth + ' height=' + imgHeight + ' autoplay=true]', {mode:Markup.MODE_ARTICLE});
            }

            aOriginal.href = $WH.sprintf(vi_siteurls[video.videoType], video.videoId);

            if (!video.user && typeof g_pageInfo == 'object') {
                video.user = g_pageInfo.username;
            }

            var
                hasFrom1 = (video.date && video.user),
                hasFrom2 = (videos.length > 1);

            if (hasFrom1) {
                var
                    postedOn = new Date(video.date),
                    elapsed = (g_serverTime - postedOn) / 1000;

                var a = divFrom.firstChild.childNodes[1];
                a.href = '?user=' + video.user;
                a.innerHTML = video.user;

                var s = divFrom.firstChild.childNodes[3];

                $WH.ee(s);
                g_formatDate(s, elapsed, postedOn);

                divFrom.firstChild.style.display = '';
            }
            else {
                divFrom.firstChild.style.display = 'none';
            }

            var s = divFrom.childNodes[1];
            $WH.ee(s);

            if (video.user)
            {
                if (hasFrom1) {
                    $WH.ae(s, $WH.ct(' ' + LANG.dash + ' '));
                }
                var a = $WH.ce('a');
                a.href = 'javascript:;';
                a.onclick = ContactTool.show.bind(ContactTool, { mode: 5, video: video });
                a.className = 'icon-report';
                g_addTooltip(a, LANG.report_tooltip, 'q2');
                $WH.ae(a, $WH.ct(LANG.report));
                $WH.ae(s, a);
            }

            s = divFrom.childNodes[2];

            if (hasFrom2) {
                var buff = '';
                if (video.user) {
                    buff = LANG.dash;
                }
                buff += (pos + 1) + LANG.lvpage_of + videos.length;

                s.innerHTML = buff;
                s.style.display = '';
            }
            else {
                s.style.display = 'none';
            }

            divFrom.style.display = (hasFrom1 || hasFrom2 ? '': 'none');

            var hasCaption = (video.caption != null && video.caption.length);
            var hasSubject = (video.subject != null && video.subject.length && video.type && video.typeId);

            if (hasCaption || hasSubject) {
                var html = '';

                if (hasSubject) {
                    html += LANG.types[video.type][0] + LANG.colon;
                    html += '<a href="?' + g_types[video.type] + '=' + video.typeId + '">';
                    html += video.subject;
                    html += '</a>';
                }

                if (hasCaption) {
                    if (hasSubject) {
                        html += LANG.dash;
                    }

                    html += (video.noMarkup ? video.caption : Markup.toHtml(video.caption, {
                        mode: Markup.MODE_SIGNATURE
                    }));
                }

                divCaption.innerHTML = html;
                divCaption.style.display = '';
            }
            else {
                divCaption.style.display = 'none';
            }

            if (videos.length > 1) {
                aPrev.href = getPound(peekPos(-1));
                aNext.href = getPound(peekPos(1));
                aPrev.style.display = aNext.style.display = '';
                aCover.style.display = 'none';
            }
            else {
                aPrev.style.display = aNext.style.display = 'none';
                aCover.style.display = '';
            }

            location.replace(getPound(pos));
        }

        Lightbox.reveal();

        container.style.visibility = 'visible';

        setTimeout(fixTitle, 1);
    }

    function peekPos(change) {
        var foo = pos;
        foo += change;

        if (foo < 0) {
            foo = videos.length - 1;
        }
        else if (foo >= videos.length) {
            foo = 0;
        }

        return foo;
    }

    function prevVideo() {
        pos = peekPos(-1);
        render();

        return false;
    }

    function nextVideo() {
        pos = peekPos(1);
        render();

        return false;
    }

    function fixTitle() {
        if (pageTitle) {
            document.title = pageTitle;
        }
    }

    function onKeyUp(e) {
        e = $WH.$E(e);

        switch (e.keyCode) {
        case 37: // Left
            prevVideo();
            break;
        case 39: // Right
            nextVideo();
            break;
        }
    }

    function onResize() {
        render(1);
    }

    function onHide() {
        $WH.ee(imgDiv);

        if (videos.length > 1) {
            $WH.dE(document, 'keyup', onKeyUp) ;
        }

        if (oldHash && mode == 0) {
            if (oldHash.indexOf(':id=') != -1) {
                oldHash = '#videos';
            }
            location.replace(oldHash);
        }
        else {
            location.replace('#.');
        }

        fixTitle();
    }

    function onShow(dest, first, opt) {
        if (typeof opt.videos == 'string') {
            videos = g_videos[opt.videos];
            mode = 1;
            collectionId = opt.videos;
        }
        else {
            videos = opt.videos;
            mode = 0;
            collectionId = null;
        }
        container = dest;

        pos = 0;
        if (opt.pos && opt.pos >= 0 && opt.pos < videos.length) {
            pos = opt.pos;
        }

        if (first) {
            dest.className = 'screenshotviewer';
            screen = $WH.ce('div');
            screen.className = 'screenshotviewer-screen';

            aPrev = $WH.ce('a');
            aNext = $WH.ce('a');
            aPrev.className = 'screenshotviewer-prev';
            aNext.className = 'screenshotviewer-next';
            aPrev.href = 'javascript:;';
            aNext.href = 'javascript:;';

            var foo = $WH.ce('span');
            var b = $WH.ce('b');
            // $WH.ae(b, $WH.ct(LANG.previous));
            $WH.ae(foo, b);
            $WH.ae(aPrev, foo);
            var foo = $WH.ce('span');
            var b = $WH.ce('b');
            // $WH.ae(b, $WH.ct(LANG.next));
            $WH.ae(foo, b);
            $WH.ae(aNext, foo);

            aPrev.onclick = prevVideo;
            aNext.onclick = nextVideo;

            aCover = $WH.ce('a');
            aCover.className = 'screenshotviewer-cover';
            aCover.href = 'javascript:;';
            aCover.onclick = Lightbox.hide;
            var foo = $WH.ce('span');
            var b = $WH.ce('b');
            $WH.ae(b, $WH.ct(LANG.close));
            $WH.ae(foo, b);
            $WH.ae(aCover, foo);

            $WH.ae(screen, aPrev);
            $WH.ae(screen, aNext);
            $WH.ae(screen, aCover);

            imgDiv = $WH.ce('div');
            $WH.ae(screen, imgDiv);

            $WH.ae(dest, screen);

            var aClose = $WH.ce('a');
            // aClose.className = 'dialog-x';
            aClose.className = 'screenshotviewer-close';
            aClose.href = 'javascript:;';
            aClose.onclick = Lightbox.hide;
            // $WH.ae(aClose, $WH.ct(LANG.close));
            $WH.ae(aClose, $WH.ce('span'));
            $WH.ae(dest, aClose);

            aOriginal = $WH.ce('a');
            // aOriginal.className = 'dialog-arrow';
            aOriginal.className = 'screenshotviewer-original';
            aOriginal.href = 'javascript:;';
            aOriginal.target = '_blank';
            // $WH.ae(aOriginal, $WH.ct(LANG.original));
            $WH.ae(aOriginal, $WH.ce('span'));
            $WH.ae(dest, aOriginal);

            divFrom = $WH.ce('div');
            divFrom.className = 'screenshotviewer-from';
            var sp = $WH.ce('span');
            $WH.ae(sp, $WH.ct(LANG.lvscreenshot_from));
            $WH.ae(sp, $WH.ce('a'));
            $WH.ae(sp, $WH.ct(' '));
            $WH.ae(sp, $WH.ce('span'));
            $WH.ae(divFrom, sp);
            $WH.ae(divFrom, $WH.ce('span'));
            $WH.ae(divFrom, $WH.ce('span'));
            $WH.ae(dest, divFrom);

            divCaption = $WH.ce('div');
            divCaption.className = 'screenshotviewer-caption';
            $WH.ae(dest, divCaption);

            var d = $WH.ce('div');
            d.className = 'clear';
            $WH.ae(dest, d);
        }

        oldHash = location.hash;

        if (videos.length > 1) {
            $WH.aE(document, 'keyup', onKeyUp);
        }
        render();
    }

    this.checkPound = function() {
        pageTitle = $WH.gE(document, 'title').innerHTML;
        if (location.hash && location.hash.indexOf('#videos') == 0) {
            if (!g_listviews['videos']) { // Standalone video viewer
                var parts = location.hash.split(':');
                if (parts.length == 3) {
                    var collection = g_videos[parts[1]],
                    p = parseInt(parts[2]);

                    if (collection && p >= 1 && p <= collection.length) {
                        VideoViewer.show({
                            videos: parts[1],
                            pos: p - 1
                        });
                    }
                }
            }
        }
    }

    this.show = function(opt) {
        Lightbox.show('videoviewer', {
            onShow: onShow,
            onHide: onHide,
            onResize: onResize
        },opt);
        return false;
    }

    DomContentLoaded.addEvent(this.checkPound)
};


var Dialog = function() {
var
    _self = this,
    _template,
    _onSubmit = null,
    _templateName,

    _funcs = {},
    _data,

    _inited = false,
    _form = $WH.ce('form'),
    _elements = {};

    _form.onsubmit = function() {
        _processForm();
        return false
    };

    this.show = function(template, opt) {
        if (template) {
            _templateName = template;
            _template = Dialog.templates[_templateName];
            _self.template = _template;
        }
        else {
            return;
        }

        if (_template.onInit && !_inited) {
            (_template.onInit.bind(_self, _form, opt))();
        }

        if (opt.onBeforeShow) {
            _funcs.onBeforeShow = opt.onBeforeShow.bind(_self, _form);
        }

        if (_template.onBeforeShow) {
            _template.onBeforeShow = _template.onBeforeShow.bind(_self, _form);
        }

        if (opt.onShow) {
            _funcs.onShow = opt.onShow.bind(_self, _form);
        }

        if (_template.onShow) {
            _template.onShow = _template.onShow.bind(_self, _form);
        }

        if (opt.onHide) {
            _funcs.onHide = opt.onHide.bind(_self, _form);
        }

        if (_template.onHide) {
            _template.onHide = _template.onHide.bind(_self, _form);
        }

        if (opt.onSubmit) {
            _funcs.onSubmit = opt.onSubmit;
        }

        if (_template.onSubmit)
            _onSubmit = _template.onSubmit.bind(_self, _form);

        if (opt.data) {
            _inited = false;
            _data = {};
            $WH.cO(_data, opt.data);
        }
        _self.data = _data;

        Lightbox.show('dialog-' + _templateName, {
            onShow: _onShow,
            onHide: _onHide
        });
    };

    this.getValue = function(id) {
        return _getValue(id);
    };

    this.setValue = function(id, value) {
        _setValue(id, value);
    };

    this.getSelectedValue = function(id) {
        return _getSelectedValue(id);
    };

    this.getCheckedValue = function(id) {
        return _getCheckedValue(id);
    };

    function _onShow(dest, first) {
        if (first || !_inited) {
            _initForm(dest);
        }

        if (_template.onBeforeShow) {
            _template.onBeforeShow();
        }

        if (_funcs.onBeforeShow) {
            _funcs.onBeforeShow();
        }

        Lightbox.setSize(_template.width, _template.height);
        dest.className = 'dialog';

        _updateForm();

        if (_template.onShow) {
            _template.onShow();
        }

        if (_funcs.onShow) {
            _funcs.onShow();
        }
    }

    function _initForm(dest) {
        $WH.ee(dest);
        $WH.ee(_form);

        var container = $WH.ce('div');
        container.className = 'text';
        $WH.ae(dest, container);
        $WH.ae(container, _form);
        if (_template.title) {
            var h = $WH.ce('h1');
            $WH.ae(h, $WH.ct(_template.title));
            $WH.ae(_form, h);
        }

        var
            t         = $WH.ce('table'),
            tb        = $WH.ce('tbody'),
            mergeCell = false;

        $WH.ae(t, tb);
        $WH.ae(_form, t);

        for (var i = 0, len = _template.fields.length; i < len; ++i) {
            var
                field = _template.fields[i],
                element;

            if (!mergeCell) {
                tr = $WH.ce('tr');
                th = $WH.ce('th');
                td = $WH.ce('td');
            }

            field.__tr = tr;

            if (_data[field.id] == null) {
                _data[field.id] = (field.value ? field.value : '');
            }

            var options;
            if (field.options) {
                options = [];

                if (field.optorder) {
                    $WH.cO(options, field.optorder);
                }
                else {
                    for (var j in field.options) {
                        options.push(j);
                    }
                }

                if (field.sort) {
                    options.sort(function(a, b) {
                        return field.sort * $WH.strcmp(field.options[a], field.options[b]);
                    });
                }
            }

            switch (field.type) {
                case 'caption':
                    th.colSpan = 2;
                    th.style.textAlign = 'left';
                    th.style.padding = 0;

                    if (field.compute) {
                        (field.compute.bind(_self, null, _data[field.id], _form, th, tr))();
                    }
                    else if (field.label) {
                        $WH.ae(th, $WH.ct(field.label));
                    }

                    $WH.ae(tr, th);
                    $WH.ae(tb, tr);

                    continue;
                    break;
                case 'textarea':
                    var f = element = $WH.ce('textarea');

                    f.name = field.id;

                    if (field.disabled) {
                        f.disabled = true;
                    }

                    f.rows = field.size[0];
                    f.cols = field.size[1];
                    td.colSpan = 2;

                    if (field.label) {
                        th.colSpan = 2;
                        th.style.textAlign = 'left';
                        th.style.padding = 0;
                        td.style.padding = 0;

                        $WH.ae(th, $WH.ct(field.label));
                        $WH.ae(tr, th);
                        $WH.ae(tb, tr);

                        tr = $WH.ce('tr');
                    }
                    $WH.ae(td, f);

                    break;
                case 'select':

                    var f = element = $WH.ce('select');

                    f.name = field.id;

                    if (field.size) {
                        f.size = field.size;
                    }

                    if (field.disabled) {
                        f.disabled = true;
                    }

                    if (field.multiple) {
                        f.multiple = true;
                    }

                    for (var j = 0, len2 = options.length; j < len2; ++j) {
                        var o = $WH.ce('option');

                        o.value = options[j];

                        $WH.ae(o, $WH.ct(field.options[options[j]]));
                        $WH.ae(f, o)
                    }

                    $WH.ae(td, f);

                    break;
                case 'dynamic':
                    td.colSpan = 2;
                    td.style.textAlign = 'left';
                    td.style.padding = 0;

                    if (field.compute)
                        (field.compute.bind(_self, null, _data[field.id], _form, td, tr))();

                    $WH.ae(tr, td);
                    $WH.ae(tb, tr);

                    element = td;

                    break;
                case 'checkbox':
                case 'radio':
                    var k = 0;
                    element = [];
                    for (var j = 0, len2 = options.length; j < len2; ++j) {
                        var
                            s = $WH.ce('span'),
                            f,
                            l,
                            uniqueId = 'sdfler46' + field.id + '-' + options[j];

                        if (j > 0 && !field.noInputBr) {
                            $WH.ae(td, $WH.ce('br'));
                        }
                        if ($WH.Browser.ie6 && field.type == 'radio') {
                            l = $WH.ce("<label for='' + uniqueId + '' onselectstart='return false' />");
                            f = $WH.ce("<input type='' + field.type + '' name='' + field.id + '' />");
                        }
                        else {
                            l = $WH.ce('label');
                            l.setAttribute('for', uniqueId);
                            l.onmousedown = $WH.rf;

                            f = $WH.ce('input');
                            f.setAttribute('type', field.type);
                            f.name = field.id;
                        }
                        f.value = options[j];
                        f.id = uniqueId;

                        if (field.disabled) {
                            f.disabled = true;
                        }
                        if (field.submitOnDblClick) {
                            l.ondblclick = f.ondblclick = function(e) {
                                _processForm();
                            };
                        }

                        if (field.compute) {
                            (field.compute.bind(_self, f, _data[field.id], _form, td, tr))();
                        }

                        $WH.ae(l, f);
                        $WH.ae(l, $WH.ct(field.options[options[j]]));
                        $WH.ae(td, l);
                        element.push(f);
                    }
                    break;
                default: // Textbox
                    var f = element = $WH.ce('input');
                    f.name = field.id;

                    if (field.size) {
                        f.size = field.size;
                    }

                    if (field.disabled) {
                        f.disabled = true ;
                    }

                    if (field.submitOnEnter) {
                        f.onkeypress = function(e) {
                            e = $WH.$E(e);
                            if (e.keyCode == 13) {
                                _processForm();
                            }
                        }
                    }
                    f.setAttribute('type', field.type);
                    $WH.ae(td, f);
                    break;
            }

            if (field.label) {
                if (field.type == 'textarea') {
                    if (field.labelAlign) {
                        td.style.textAlign = field.labelAlign;
                    }
                    td.colSpan = 2;
                }
                else {
                    if (field.labelAlign) {
                        th.style.textAlign = field.labelAlign;
                    }
                    $WH.ae(th, $WH.ct(field.label));
                    $WH.ae(tr, th);
                }
            }

            if (field.placeholder) {
                f.placeholder = field.placeholder;
            }

            if (field.type != 'checkbox' && field.type != 'radio') {
                if (field.width) {
                    f.style.width = field.width;
                }

                if (field.compute  && field.type != 'caption' && field.type != 'dynamic') {
                    (field.compute.bind(_self, f, _data[field.id], _form, td, tr))();
                }
            }

            if (field.caption) {
                var s = $WH.ce('small');
                if (field.type != 'textarea')
                    s.style.paddingLeft = '2px';
                s.className = 'q0'; // commented in 5.0?
                $WH.ae(s, $WH.ct(field.caption));
                $WH.ae(td, s);
            }

            $WH.ae(tr, td);
            $WH.ae(tb, tr);

            mergeCell = field.mergeCell;
            _elements[field.id] = element;
        }

        for (var i = _template.buttons.length; i > 0; --i) {
            var
                button = _template.buttons[i - 1],
                a      = $WH.ce('a');

            a.href = 'javascript:;';
            a.onclick = _processForm.bind(a, button[0]);
            a.className = 'dialog-' + button[0];
            $WH.ae(a, $WH.ct(button[1]));
            $WH.ae(dest, a);
        }

        var _ = $WH.ce('div');
        _.className = 'clear';
        $WH.ae(dest, _);

        _inited = true;
    }

    function _updateForm() {
        for (var i = 0, len = _template.fields.length; i < len; ++i) {
            var
                field = _template.fields[i],
                f     = _elements[field.id];

            switch (field.type) {
                case 'caption': // Do nothing
                    break;
                case 'select':
                    for (var j = 0, len2 = f.options.length; j < len2; j++) {
                        f.options[j].selected = (f.options[j].value == _data[field.id] || $WH.in_array(_data[field.id], f.options[j].value) != -1);
                    }
                    break;
                case 'checkbox':
                case 'radio':
                    for (var j = 0, len2 = f.length; j < len2; j++) {
                        f[j].checked = (f[j].value == _data[field.id] || $WH.in_array(_data[field.id], f[j].value) != -1);
                    }
                    break;

                default:
                    f.value = _data[field.id];
                    break;
            }

            if (field.update) {
                (field.update.bind(_self, null, _data[field.id], _form, f))();
            }
        }
    }

    function _onHide() {
        if (_template.onHide) {
            _template.onHide();
        }
        if (_funcs.onHide) {
            _funcs.onHide();
        }
    }

    function _processForm(button) {
        // if (button == 'x') { // Special case
        if (button == 'cancel') { // Special case
            return Lightbox.hide();
        }

        for (var i = 0, len = _template.fields.length; i < len; ++i) {
            var
                field = _template.fields[i],
                newValue;

            switch (field.type) {
                case 'caption': // Do nothing
                    continue;
                case 'select':
                    newValue = _getSelectedValue(field.id);
                    break;
                case 'checkbox':
                case 'radio':
                    newValue = _getCheckedValue(field.id);
                    break;
                case 'dynamic':
                    if (field.getValue) {
                        newValue = field.getValue(field, _data, _form);
                        break;
                    }
                default:
                    newValue = _getValue(field.id);
                    break;
            }
            if (field.validate) {
                if (!field.validate(newValue, _data, _form)) {
                    return;
                }
            }

            if (newValue && typeof newValue == 'string') {
                newValue = $WH.trim(newValue);
            }
            _data[field.id] = newValue;
        }

        _submitData(button);
    }

    function _submitData(button) {
        var ret;

        if (_onSubmit) {
            ret = _onSubmit(_data, button, _form);
        }

        if (_funcs.onSubmit) {
            ret = _funcs.onSubmit(_data, button, _form);
        }

        if (ret === undefined || ret)
            Lightbox.hide();

            return false;
    }

    function _getValue(id) {
        return _elements[id].value;
    }

    function _setValue(id, value) {
        _elements[id].value = value;
    }

    function _getSelectedValue(id) {
        var
            result = [],
            f = _elements[id];

            for (var i = 0, len = f.options.length; i < len; i++) {
            if (f.options[i].selected) {
                result.push(parseInt(f.options[i].value) == f.options[i].value ? parseInt(f.options[i].value) : f.options[i].value);
            }
        }
        if (result.length == 1) {
            result = result[0];
        }
        return result;
    }

    function _getCheckedValue(id) {
        var
            result = [],
            f      = _elements[id];

        for (var i = 0, len = f.length; i < len; i++) {
            if (f[i].checked) {
                result.push(parseInt(f[i].value) == f[i].value ? parseInt(f[i].value) : f[i].value);
            }
        }

        return result;
    }
};
Dialog.templates = {};

var Slider = new function() {
    var
        start,
        handleObj,
        timer;

    function onMouseDown(e) {
        e = $WH.$E(e);

        handleObj = this;
        start     = $WH.g_getCursorPos(e);

        $WH.aE(document, 'mousemove', onMouseMove);
        $WH.aE(document, 'mouseup', onMouseUp);

        return false;
    }

    function onMouseMove(e) {
        e = $WH.$E(e);

        if (!start || !handleObj) {
            return;
        }

        var
            cursor  = $WH.g_getCursorPos(e),
            delta   = cursor[handleObj._dir] - start[handleObj._dir],
            outside = setPosition(handleObj, handleObj._pos + delta),
            current = getCurrentPosition(handleObj);

        if (current && handleObj.input) {
            handleObj.input.value = current.value;
        }

        if (!outside) {
            start = cursor;
        }

        if (handleObj.onMove) {
            handleObj.onMove(e, handleObj, current);
        }
    }

    function onMouseUp(e) {
        e = $WH.$E(e);

        if (handleObj.onMove) {
            handleObj.onMove(e, handleObj, getCurrentPosition(handleObj));
        }

        handleObj = null;
        start     = null;

        $WH.dE(document, 'mousemove', onMouseMove);
        $WH.dE(document, 'mouseup', onMouseUp);

        return false;
    }

    function onClick(obj, e) {
        e = $WH.$E(e);

        handleObj = obj;
        start     = $WH.g_getCursorPos(e);

        var
            offset = $WH.ac(handleObj.parentNode),
            center = Math.floor(getHandleWidth(handleObj) / 2);

        setPosition(handleObj, start[handleObj._dir] - offset[handleObj._dir] - center);

        var current = getCurrentPosition(handleObj);

        if (current && handleObj.input) {
            handleObj.input.value = current.value;
        }

        if (handleObj.onMove) {
            handleObj.onMove(e, handleObj, current);
        }

        $WH.aE(document, 'mousemove', onMouseMove);
        $WH.aE(document, 'mouseup', onMouseUp);

        return false;
    }

    function onKeyPress(obj, e) {
        if (timer) {
            clearTimeout(timer);
        }

        if (e.type == 'change' || e.type == 'keypress' && e.which == 13) {
            onInput(obj, e);
        }
        else {
            timer = setTimeout(onInput.bind(0, obj, e), 1000);
        }
    }

    function onInput(obj, e) {
        var
            value   = obj.input.value,
            current = getCurrentPosition(obj);

        if (isNaN(value)) {
            value = current.value;
        }
        if (value > obj._max) {
            value = obj._max;
        }
        if (value < obj._min) {
            value = obj._min;
        }

        Slider.setValue(obj, value);

        if (obj.onMove) {
            obj.onMove(e, obj, getCurrentPosition(obj));
        }
    }

    function setPosition(obj, offset) {
        var outside = false;

        if (offset < 0) {
            offset  = 0;
            outside = true;
        }
        else if (offset > getMaxPosition(obj)) {
            offset  = getMaxPosition(obj);
            outside = true;
        }

        obj.style[(obj._dir == 'y' ? 'top' : 'left')] = offset + 'px';
        obj._pos = offset;

        return outside;
    }

    function getMaxPosition(obj) {
        return getTrackerWidth(obj) - getHandleWidth(obj) + 2;
    }

    function getCurrentPosition(obj) {
        var
            percent = obj._pos / getMaxPosition(obj),
            value   = Math.round((percent * (obj._max - obj._min)) + obj._min),
            result  = [percent, value];

        result.percent = percent;
        result.value   = value;
        return result;
    }

    function getTrackerWidth(obj) {
        if (obj._tsz > 0) {
            return obj._tsz;
        }

        if (obj._dir == 'y') {
            return obj.parentNode.offsetHeight;
        }

        return obj.parentNode.offsetWidth;
    }

    function getHandleWidth(obj) {
        if (obj._hsz > 0) {
            return obj._hsz;
        }

        if (obj._dir == 'y') {
            return obj.offsetHeight;
        }

        return obj.offsetWidth;
    }

    this.setPercent = function(obj, percent) {
        setPosition(obj, parseInt(percent * getMaxPosition(obj)));
    };

    this.setValue = function(obj, value) {
        if (value < obj._min) {
            value = obj._min;
        }
        else if (value > obj._max) {
            value = obj._max;
        }

        if (obj.input) {
            obj.input.value = value;
        }

        this.setPercent(obj, (value - obj._min) / (obj._max - obj._min));
    };

    this.setSize = function(obj, length) {
        var
            current = getCurrentPosition(obj),
            resized = getMaxPosition(obj);

        obj.parentNode.style[(obj._dir == 'y' ? 'height' : 'width')] = length + 'px';

        if (resized != getMaxPosition(obj)) {
            this.setValue(obj, current.value);
        }
    };

    this.init = function(container, opt) {
        var obj = $WH.ce('a');
        obj.href = 'javascript:;';
        obj.onmousedown = onMouseDown;
        obj.className = 'handle';

        var track = $WH.ce('a');
        track.href = 'javascript:;';
        track.onmousedown = onClick.bind(0, obj);
        track.className = 'track';

        $WH.ae(container, $WH.ce('span'));
        $WH.ae(container, track);
        $WH.ae(container, obj);

        obj._dir = 'x';
        obj._min = 1;
        obj._max = 100;
        obj._pos = 0;
        obj._tsz = 0;
        obj._hsz = 0;

        if (opt != null) {
            // Orientation
            if (opt.direction == 'y') {
                obj._dir = 'y';
            }

            // Values
            if (opt.minValue) {
                obj._min = opt.minValue;
            }
            if (opt.maxValue) {
                obj._max = opt.maxValue;
            }

            // Functions
            if (opt.onMove) {
                obj.onMove = opt.onMove;
            }

            if (opt.trackSize) {
                obj._tsz = opt.trackSize;
            }

            if (opt.handleSize) {
                obj._hsz = opt.handleSize;
            }

            // Labels
            if (opt.showLabels !== false) {
                var label = $WH.ce('div');

                label.innerHTML = obj._min;
                label.className = 'label min';
                $WH.ae(container, label);

                label = $WH.ce('div');
                label.innerHTML = obj._max;
                label.className = 'label max';
                $WH.ae(container, label);

                obj.input = $WH.ce('input');
                $(obj.input).attr({ value: obj._max, type: 'text' }
                ).bind('click', function () { this.select(); }
                ).keypress(function (e) {
                    var allowed = [];
                    var usedKey = e.which;
                    for (i = 48; i < 58; i++) {
                        allowed.push(i);
                    }
                    if (!($WH.in_array(allowed, usedKey) >= 0) && usedKey != 13) {
                        e.preventDefault();
                    }
                }).bind('keyup keydown change', onKeyPress.bind(0, obj));

                obj.input.className = 'input';
                $WH.ae(container, obj.input);
            }
        }

        container.className = 'slider-' + obj._dir + (opt == null || opt.showLabels !== false ? ' has-labels' : '');

        return obj;
    }
};

/*
Global functions related to the Summary class (item comparison, item set summary)
*/

var suDialog;

function su_addToSaved(items, nItems, newWindow, level) {
    if (!items) {
        return;
    }

    if (!suDialog) {
        suDialog = new Dialog();
    }

    var doCompare = function(data) {
        var saved = g_getWowheadCookie('compare_groups'),
        url = '?compare';

        if (data.action > 1) { // Save
            if (saved) {
                items = saved + ';' + items;
            }

            g_setWowheadCookie('compare_groups', items, true);

            if (level) {
                g_setWowheadCookie('compare_level', level, true);
            }
        }
        else { // Don't save
            url += '=' + items + (level ? '&l=' + level : '');
        }

        if (data.action < 3) { // View now
            if (newWindow) {
                window.open(url);
            }
            else {
                location.href = url;
            }
        }
    };

    suDialog.show('docompare', {
        data: { selecteditems: nItems, action: 1 },
        onSubmit: doCompare
    });
}

Dialog.templates.docompare = {

    title: LANG.dialog_compare,
    width: 400,
    buttons: [['okay', LANG.ok], ['cancel', LANG.cancel]],

    fields: [
        {
            id: 'selecteditems',
            type: 'caption',
            compute: function(field, value, form, td) {
                td.innerHTML = $WH.sprintf((value == 1 ? LANG.dialog_selecteditem : LANG.dialog_selecteditems), value);
            }
        },
        {
            id: 'action',
            type: 'radio',
            label: '',
            value: 3,
            submitOnDblClick: 1,
            options: {
                1 : LANG.dialog_nosaveandview,
                2 : LANG.dialog_saveandview,
                3 : LANG.dialog_saveforlater
            }
        }
    ]
};

var Favorites = new function() {
    var _type    = null;
    var _typeId  = null;
    var _favIcon = null;

    this.pageInit = function(h1, type, typeId) {
        if (typeof h1 == 'string') {
            if (!document.querySelector)
                return;

            h1 = document.querySelector(h1);
        }

        if (!h1 || typeof type != 'number' || typeof typeId != 'number')
            return;

        _type   = type;
        _typeId = typeId;

        createIcon(h1);
    };

    function initFavIcon() {
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

    this.hasFavorites = function() {
        return !!g_favorites.length
    };

    this.getMenu = function() {
        var favMenu  = [];
        var nGroups  = 0;
        var nEntries = 0;

        for (var i = 0, favGroup; favGroup = g_favorites[i]; i++) {
            if (!favGroup.entities.length)
                continue;

            nGroups++;
            var subMenu = [];
            for (var j = 0, favEntry; favEntry = favGroup.entities[j]; j++) {
                subMenu.push([favEntry[0], favEntry[1], '?' + g_types[favGroup.id] + '=' + favEntry[0]]);
                nEntries++
            }

            Menu.sort(subMenu);
            favMenu.push([favGroup.id, LANG.types[favGroup.id][2], , subMenu])
        }

        Menu.sort(favMenu);

        // display short favorites as 1-dim list
        if ((nGroups == 1 && nEntries <= 45) || (nGroups == 2 && nGroups + nEntries <= 30) || (nGroups > 2 && nGroups + nEntries <= 15)) {
            var list = [];

            for (var i = 0; subMenu = favMenu[i]; i++) {
                list.push([, subMenu[MENU_IDX_NAME]]);

                for (var j = 0, subEntry; subEntry = subMenu[MENU_IDX_SUB][j]; j++) {
                    var listEntry = [subEntry[MENU_IDX_ID], subEntry[MENU_IDX_NAME], subEntry[MENU_IDX_URL]];

                    if (subEntry[MENU_IDX_OPT])
                        listEntry[MENU_IDX_OPT] = subEntry[MENU_IDX_OPT];

                    list.push(listEntry);
                }
            }

            favMenu = list;
        }

        return favMenu;
    };

    this.refreshMenu = function() {
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
    };

    function createIcon(heading) {
        _favIcon = $('<span/>', {
            'class': 'fav-star',
            mouseout: $WH.Tooltip.hide
        }).appendTo(heading);

        if (g_user.id) {
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
        else {
            _favIcon.addClass('fa-star-0').click(function() {
                location.href = "?account=signin";
                $WH.Tooltip.hide();
            }).mouseover(function(event) {
                $WH.Tooltip.show(this, LANG.favorites_login + "<div class='q2' style='margin-top:10px'>" + LANG.clicktologin + '</span>');
            });
        }
    }

    function updateIcon(type, typeId) {
        if (_favIcon) {
            var rmv = 'fav-star-0';
            var add = 'fav-star-1';
            if (!isFaved(type, typeId)) {
                rmv = 'fav-star-1';
                add = 'fav-star-0';
            }

            _favIcon.removeClass(rmv).addClass(add);
        }
    }

    function isFaved(type, typeId) {
        var idx = getIndex(type);
        if (idx == -1)
            return false;

        for (var i = 0, j; j = g_favorites[idx].entities[i]; i++)
            if (j[0] == typeId)
                return true;

        return false;
    }

    function toggleEntry(type, typeId, name) {
        if (isFaved(type, typeId))
            removeEntry(type, typeId);
        else
            addEntry(type, typeId, name);
    }

    function addEntry(type, typeId, name) {
        var idx = getIndex(type, true);
        if (idx == -1)
            return;

        for (var i = 0, j; j = g_favorites[idx].entities[i]; i++) {
            if (j[0] == typeId) {
                alert(LANG.favorites_duplicate.replace('%s', LANG.types[type][1]));
                return;
            }
        }

        sendUpdate('add', type, typeId);
        g_favorites[idx].entities.push([typeId, name]);
        Favorites.refreshMenu();
    }

    function removeEntry(type, typeId) {
        var idx = getIndex(type);
        if (idx == -1)
            return;

        for (var i = 0, j; j = g_favorites[idx].entities[i]; i++) {
            if (j[0] == typeId) {
                sendUpdate('remove', type, typeId);
                g_favorites[idx].entities.splice(i, 1);
                if (!g_favorites[idx].entities.length)
                    g_favorites.splice(idx, 1);

                Favorites.refreshMenu();
                return;
            }
        }
    }

    function getIndex(type, createNew) {
        if (!LANG.types[type])
            return -1;

        for (var i = 0, j; j = g_favorites[i]; i++)
            if (j.id == type)
                return i;

        if (!createNew)
            return -1;

        g_favorites.push({ id: type, entities: [] });

        g_favorites.sort(function(a, b) { return $WH.stringCompare(LANG.types[a.id], LANG.types[b.id]) });

        for (i = 0; j = g_favorites[i]; i++)
            if (j.id == type)
                return i;

        return -1;
    }

    function sendUpdate(method, type, typeId) {
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

function Tabs(opt) {
    $WH.cO(this, opt);

    if (this.parent) {
        this.parent = $WH.ge(this.parent);
    }
    else {
        return;
    }

    this.selectedTab = -1;

    this.uls = [];

    this.tabs = [];
    this.nShows = 0;
    if (this.poundable == null) {
        this.poundable = 1;
    }
    this.poundedTab = null;

    if (this.onLoad == null) {
        this.onLoad = Tabs.onLoad.bind(this);
    }

    if (this.onShow == null) {
        this.onShow = Tabs.onShow.bind(this);
    }

    if (this.onHide) {
        this.onHide = this.onHide.bind(this);
    }

    this.trackClick = Tabs.trackClick.bind(this);
}

Tabs.prototype = {
    add: function(caption, opt) {
        var
            _,
            index = this.tabs.length;

        _ = {
            caption: caption,
            index: index,
            owner: this
        };
        $WH.cO(_, opt);

        this.tabs.push(_);

        return index;
    },

    hide: function(index, visible) {
        if (this.tabs[index]) {
            var selectedTab = this.selectedTab;

            if (index == 0 && selectedTab == -1) {
                this.poundedTab = this.selectedTab = selectedTab = 0;
            }

            if (index != this.poundedTab) {
                this.selectedTab = -1;
            }

            this.tabs[index].hidden = !visible;
            this.flush();

            if (!visible && index == selectedTab) {
                this.selectedTab = selectedTab;
                for (var i = 0, len = this.tabs.length; i < len; ++i) {
                    if (i != index && !this.tabs[i].hidden) {
                        return this.show(i, 1);
                    }
                }
            }
        }
    },

    unlock: function(index, locked) {
        if (this.tabs[index]) {
            this.tabs[index].locked = locked;
            _ = $WH.gE(this.uls[0], 'a');

            $('.icon-lock', _[index]).remove();

            if (locked) {
                $('div, b', _[index]).prepend('<span class="icon-lock" />');
            }

            var _ = location.hash.substr(1).split(':')[0];
            if (this.tabs[index].id == _) {
                this.show(index, 1);
            }
        }
    },

    focus: function(index) {
        if (index < 0) {
            index = this.tabs.length + index;
        }
        this.forceScroll = 1;
        $WH.gE(this.uls[0], 'a')[index].onclick({}, true);
        this.forceScroll = null;
    },

    show: function(index, forceClick) {
        var _;

        if (isNaN(index) || index < 0) {
            index = 0;
        }
        else if (index >= this.tabs.length) {
            index = this.tabs.length - 1;
        }

        if ((forceClick == null && index == this.selectedTab) || this.tabs[index].hidden) {
            return;
        }

        if (this.tabs[index].locked) {
            return this.onShow(this.tabs[index], this.tabs[this.selectedTab]);
        }

        if (this.selectedTab != -1) {
            _ = this.tabs[this.selectedTab];

            if (this.onHide && !this.onHide(_)) {
                return;
            }

            if (_.onHide && !_.onHide()) {
                return;
            }
        }

        ++this.nShows;

        _ = $WH.gE(this.uls[0], 'a');
        if (this.selectedTab != -1) {
            _[this.selectedTab].className = '';
        }
        _[index].className = 'selected';

        _ = this.tabs[index];
        if (_.onLoad) {
            _.onLoad();
            _.onLoad = null;
        }

        this.onShow(this.tabs[index], this.tabs[this.selectedTab]);

        if (_.onShow) {
            _.onShow(this.tabs[this.selectedTab]);
        }

        this.selectedTab = index;
    },

    flush: function(defaultTab) {
        var _, l, a, b, d, d2;

        var container = $WH.ce('div');
        container.className = 'tabs-container';

        this.uls[0] = $WH.ce('ul');
        this.uls[0].className = 'tabs';

        d = $WH.ce('div');
        d.className = 'tabs-levels';

        $WH.ae(container, this.uls[0]);

        for (var i = 0; i < this.tabs.length; ++i) {
            var tab = this.tabs[i];

            l = $WH.ce('li');
            a = $WH.ce('a');
            b = $WH.ce('b');

            if (tab.hidden) {
                l.style.display = 'none';
            }

            if (this.poundable) {
                a.href = '#' + tab.id;
            }
            else {
                a.href = 'javascript:;';
            }

            $WH.ns(a);
            a.onclick = Tabs.onClick.bind(tab, a);

            d = $WH.ce('div');

            if (tab.locked) {
                s = $WH.ce('span');
                s.className = 'icon-lock';
                $WH.ae(d, s);
            }
            else if (tab.icon) {
                s = $WH.ce('span');
                s.className = 'icontiny';
                s.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + tab.icon.toLowerCase() + '.gif)';
                $WH.ae(d, s);
            }

            if (tab.tooltip) {
                a.onmouseover = (function(tooltip, e) { $WH.Tooltip.showAtCursor(e, tooltip, 0, 0, 'q'); }).bind(a, tab.tooltip);
                a.onmousemove = $WH.Tooltip.cursorUpdate;
                a.onmouseout  = $WH.Tooltip.hide;
            }

            if (tab['class']) {
                d.className = tab['class'];
            }

            $WH.ae(d, $WH.ct(tab.caption));
            $WH.ae(a, d);

            if (tab.locked) {
                s = $WH.ce('span');
                s.className = 'icon-lock';
                $WH.ae(b, s);
            }
            else if (tab.icon) {
                s = $WH.ce('span');
                s.className = 'icontiny';
                s.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + tab.icon.toLowerCase() + '.gif)';
                $WH.ae(b, s);
            }

            $WH.ae(b, $WH.ct(tab.caption));
            $WH.ae(a, b);
            $WH.ae(l, a);
            $WH.ae(this.uls[0], l);
        }

        $WH.ee(this.parent);
        $WH.ae(this.parent, container);

        if (this.onLoad) {
            _ = this.onLoad();
            if (_ != null) {
                this.poundedTab = defaultTab = _;
            }
        }

        this.show(defaultTab);
    },

    setTabName: function(index, name) {
        this.tabs[index].caption = name;

        var _ = $WH.gE(this.uls[0], 'a');
        g_setTextNodes(_[index], name);
    },

    setTabPound: function(index, pound) {
        if (!this.poundable) {
            return;
        }

        var _ = $WH.gE(this.uls[0], 'a');
        _[index].href = '#' + this.tabs[index].id + (pound ? ':' + pound : '');
    },

    setTabTooltip: function(index, text) {
        this.tabs[index].tooltip = text;

        var _ = $WH.gE(this.uls[0], 'a');
        if (text == null) {
            _[index].onmouseover = _[index].onmousemove = _[index].onmouseout = null;
        }
        else {
            _[index].onmouseover = function(e) { $WH.Tooltip.showAtCursor(e, text, 0, 0, 'q2'); };
            _[index].onmousemove = $WH.Tooltip.cursorUpdate;
            _[index].onmouseout  = $WH.Tooltip.hide;
        }
    },

    getSelectedTab: function() {
        return this.selectedTab;
    }
};

Tabs.onClick = function(a, e, forceClick) {
    if (forceClick == null && this.index == this.owner.selectedTab) {
        return;
    }

    var res = $WH.rf2(e);
    if (res == null) {
        return;
    }

    this.owner.show(this.index, forceClick);

    if (this.owner.poundable && !this.locked) {
        var _ = a.href.indexOf('#');
        _ != -1 && location.replace(a.href.substr(_));
    }

    return res;
};

Tabs.onLoad = function() {
    if (!this.poundable || !location.hash.length) {
        return;
    }

    var _ = location.hash.substr(1).split(':')[0];
    if (_) {
        return $WH.in_array(this.tabs, _, function(x) {
            if (!x.locked) {
                return x.id;
            }
        });
    }
};

Tabs.onShow = function(newTab, oldTab) {
    var _;

    if (newTab.hidden || newTab.locked) {
        return;
    }

    if (oldTab) {
        $WH.ge('tab-' + oldTab.id).style.display = 'none';
    }

    if (this.poundedTab != null || oldTab) {
        this.trackClick(newTab);
    }

    _ = $WH.ge('tab-' + newTab.id);
    _.style.display = '';

    if (((this.nShows == 1 && this.poundedTab != null && this.poundedTab >= 0) || this.forceScroll) && !this.noScroll) {
        var el, padd;
        if (this.__st) {
            el = this.__st;
            padd = 15;
        }
        else {
            el = _;
            padd = this.parent.offsetHeight + 15;
        }

        setTimeout($WH.g_scrollTo.bind(null, el, padd), 10);
    }
};

Tabs.trackClick = function(tab)
{
    if (!this.trackable || tab.tracked) {
        return;
    }

    g_trackEvent('Tabs', 'Show', this.trackable + ': ' + tab.id);
    tab.tracked = 1;
}

/*
TODO: Create "Tracking" class
*/

function g_trackPageview(tag)
{
    function track()
    {
        if (typeof ga == 'function')
            ga('send', 'pageview', tag);
    };

    $(document).ready(track);
}

function g_trackEvent(category, action, label, value)
{
    function track()
    {
        if (typeof ga == 'function')
            ga('send', 'event', category, action, label, value);
    };

    $(document).ready(track);
}

function g_attachTracking(node, category, action, label, value)
{
    var $node = $(node);

    $node.click(function() { g_trackEvent(category, action, label, value); });
}

function g_addAnalytics()
{
    var objs = {
        'home-logo': {
            'category': 'Homepage Logo',
            'actions': {
                'Click image': function(node) { return true; }
            }
        },
        'home-featuredbox': {
            'category': 'Featured Box',
            'actions': {
                'Follow link': function(node) { return (node.parentNode.className != 'home-featuredbox-links'); },
                'Click image': function(node) { return (node.parentNode.className == 'home-featuredbox-links'); }
            }
        },
        'home-oneliner': {
            'category': 'Oneliner',
            'actions': {
                'Follow link': function(node) { return true; }
            }
        },
        'sidebar-container': {
            'category': 'Page sidebar',
            'actions': {
                'Click image': function(node) { return true; }
            }
        },
        'toptabs-promo': {
            'category': 'Page header',
            'actions': {
                'Click image': function(node) { return true; }
            }
        }
    };

    for (var i in objs)
    {
        var e = $WH.ge(i);
        if (e)
            g_addAnalyticsToNode(e, objs[i]);
    }
}

function g_getNodeTextId(node)
{
    var
        id   = null,
        text = g_getFirstTextContent(node);

    if (text)
        id = g_urlize(text);
    else if (node.title)
        id = g_urlize(node.title);
    else if (node.id)
        id = g_urlize(node.id);

    return id;
}

function g_addAnalyticsToNode(node, opts, labelPrefix)
{
    if (!opts || !opts.actions || !opts.category)
    {
        if ($WH.isset('g_dev') && g_dev)
        {
            console.log('Tried to add analytics event without appropriate parameters.');
            console.log(node);
            console.log(opts);
        }

        return;
    }

    var category = opts.category;
    var tags = $WH.gE(node, 'a');
    for (var i = 0; i < tags.length; ++i)
    {
        var node = tags[i];
        var action = 'Follow link';
        for (var a in opts.actions)
        {
            if (opts.actions[a] && opts.actions[a](node))
            {
                action = a;
                break;
            }
        }
        var label = (labelPrefix ? labelPrefix + '-' : '') + g_getNodeTextId(node);

        g_attachTracking(node, category, action, label);
    }
}

$(document).ready(g_addAnalytics);

var g_listviews = {};

function Listview(opt) {
    $WH.cO(this, opt);

    if (this.id) {
        var divId = (this.tabs ? 'tab-' : 'lv-') + this.id;
        if (this.parent) {
            var d = $WH.ce('div');
            d.id = divId;
            $WH.ae($WH.ge(this.parent), d);
            this.container = d;
        }
        else {
            this.container = $WH.ge(divId);
        }
    }
    else {
        return;
    }

    var get = $WH.g_getGets();
    if ((get.debug != null || g_user.debug) && g_user.roles & U_GROUP_MODERATOR) {
        this.debug = true;
    }

    if (this.template && Listview.templates[this.template]) {
        this.template = Listview.templates[this.template];
    }
    else {
        return;
    }

    g_listviews[this.id] = this;

    if (this.data == null) {
        this.data = [];
    }

    if (this.poundable == null) {
        if (this.template.poundable != null) {
            this.poundable = this.template.poundable;
        }
        else {
            this.poundable = true;
        }
    }

    if (this.searchable == null) {
        if (this.template.searchable != null) {
            this.searchable = this.template.searchable;
        }
        else {
            this.searchable = false;
        }
    }

    if (this.filtrable == null) {
        if (this.template.filtrable != null) {
            this.filtrable = this.template.filtrable;
        }
        else {
            this.filtrable = false;
        }
    }

    if (this.sortable == null) {
        if (this.template.sortable != null) {
            this.sortable = this.template.sortable;
        }
        else {
            this.sortable = true;
        }
    }

    if (this.customPound == null) {
        if (this.template.customPound != null) {
            this.customPound = this.template.customPound;
        }
        else {
            this.customPound = false;
        }
    }

    if (this.data.length == 1) {
        this.filtrable = false;
        this.searchable = false;
    }

    if (this.searchable && this.searchDelay == null) {
        if (this.template.searchDelay != null) {
            this.searchDelay = this.template.searchDelay;
        }
        else {
            this.searchDelay = 333;
        }
    }

    if (this.clickable == null) {
        if (this.template.clickable != null) {
            this.clickable = this.template.clickable;
        }
        else {
            this.clickable = true;
        }
    }

    if (this.hideBands == null) {
        this.hideBands = this.template.hideBands;
    }

    if (this.hideNav == null) {
        this.hideNav = this.template.hideNav;
    }

    if (this.hideHeader == null) {
        this.hideHeader = this.template.hideHeader;
    }

    if (this.hideCount == null) {
        this.hideCount = this.template.hideCount;
    }

    if (this.computeDataFunc == null && this.template.computeDataFunc != null) {
        this.computeDataFunc = this.template.computeDataFunc;
    }

    if (this.createCbControls == null && this.template.createCbControls != null) {
        this.createCbControls = this.template.createCbControls;
    }

    if (this.template.onBeforeCreate != null) {
        if (this.onBeforeCreate == null) {
            this.onBeforeCreate = this.template.onBeforeCreate;
        }
        else {
            this.onBeforeCreate = [this.template.onBeforeCreate, this.onBeforeCreate];
        }
    }

    if (this.onAfterCreate == null && this.template.onAfterCreate != null) {
        this.onAfterCreate = this.template.onAfterCreate;
    }

    if (this.onNoData == null && this.template.onNoData != null) {
        this.onNoData = this.template.onNoData;
    }

    if (this.createNote == null && this.template.createNote != null) {
        this.createNote = this.template.createNote;
    }

    if (this.sortOptions == null && this.template.sortOptions != null) {
        this.sortOptions = this.template.sortOptions;
    }

    if (this.customFilter == null && this.template.customFilter != null) {
        this.customFilter = this.template.customFilter;
    }

    if (this.onSearchSubmit == null && this.template.onSearchSubmit != null) {
        this.onSearchSubmit = this.template.onSearchSubmit;
    }

    if (this.getItemLink == null && this.template.getItemLink != null) {
        this.getItemLink = this.template.getItemLink;
    }

    if (this.clip == null && this.template.clip != null) {
        this.clip = this.template.clip;
    }

    if (this.clip || this.template.compute || this.id == 'topics' || this.id == 'recipes') {
        this.debug = false; // Don't add columns to picker windows
    }

    if (this.mode == null) {
        this.mode = this.template.mode;
    }

    if (this.template.noStyle != null) {
        this.noStyle = this.template.noStyle;
    }

    if (this.nItemsPerPage == null) {
        if (this.template.nItemsPerPage != null) {
            this.nItemsPerPage = this.template.nItemsPerPage;
        }
        else {
            this.nItemsPerPage = 50;
        }
    }
    this.nItemsPerPage |= 0;
    if (this.nItemsPerPage <= 0) {
        this.nItemsPerPage = 0;
    }

    this.nFilters = 0;
    this.resetRowVisibility();

    if (this.mode == Listview.MODE_TILED) {
        if (this.nItemsPerRow == null) {
            var ipr = this.template.nItemsPerRow;
            this.nItemsPerRow = (ipr != null ? ipr : 4);
        }
        this.nItemsPerRow |= 0;
        if (this.nItemsPerRow <= 1) {
            this.nItemsPerRow = 1;
        }
    }
    else if (this.mode == Listview.MODE_CALENDAR) {
        this.dates = [];
        this.nItemsPerRow  = 7; // Days per row
        this.nItemsPerPage = 1; // Months per page
        this.nDaysPerMonth = [];

        if (this.template.startOnMonth != null) {
            this.startOnMonth = this.template.startOnMonth;
        }
        else {
            this.startOnMonth = new Date();
        }

        this.startOnMonth.setDate(1);
        this.startOnMonth.setHours(0, 0, 0, 0);

        if (this.nMonthsToDisplay == null) {
            if (this.template.nMonthsToDisplay != null) {
                this.nMonthsToDisplay = this.template.nMonthsToDisplay;
            }
            else {
                this.nMonthsToDisplay = 1;
            }
        }

        var
            y = this.startOnMonth.getFullYear(),
            m = this.startOnMonth.getMonth();

        for (var j = 0; j < this.nMonthsToDisplay; ++j) {
            var date = new Date(y, m + j, 32);
            this.nDaysPerMonth[j] = 32 - date.getDate();
            for (var i = 1; i <= this.nDaysPerMonth[j]; ++i) {
                this.dates.push({ date: new Date(y, m + j, i) });
            }
        }

        if (this.template.rowOffset != null) {
            this.rowOffset = this.template.rowOffset;
        }
    }
    else {
        this.nItemsPerRow = 1;
    }

    this.columns = [];
    for (var i = 0, len = this.template.columns.length; i < len; ++i) {
        var
            ori = this.template.columns[i],
            cpy = {};

            $WH.cO(cpy, ori);

        this.columns.push(cpy);
    }

    // ************************
    // Extra Columns

    if (this.extraCols != null) {
        for (var i = 0, len = this.extraCols.length; i < len; ++i) {
            var pos = null;
            var col = this.extraCols[i];

            if (col.after || col.before) {
                var index = $WH.in_array(this.columns, (col.after ? col.after : col.before), function(x) {
                    return x.id;
                });

                if (index != -1) {
                    pos = (col.after ? index + 1 : index);
                }
            }

            if (pos == null) {
                pos = this.columns.length;
            }

            if (col.id == 'debug-id') {
                this.columns.splice(0, 0, col);
            }
            else {
                this.columns.splice(pos, 0, col);
            }
        }
    }

    // ************************
    // Visibility

    this.visibility = [];

    var
        visibleCols = [],
        hiddenCols  = [];

    if (this.visibleCols != null) {
        $WH.array_walk(this.visibleCols, function(x) {
            visibleCols[x] = 1;
        });
    }

    if (this.hiddenCols != null) {
        $WH.array_walk(this.hiddenCols, function(x) {
            hiddenCols[x] = 1;
        });
    }

    if ($.isArray(this.sortOptions)) {
        for (var i = 0, len = this.sortOptions.length; i < len; ++i) {
            var sortOpt = this.sortOptions[i];
            if (visibleCols[sortOpt.id] != null || (!sortOpt.hidden && hiddenCols[sortOpt.id] == null)) {
                this.visibility.push(i);
            }
        }
    }
    else {
        for (var i = 0, len = this.columns.length; i < len; ++i) {
            var col = this.columns[i];
            if (visibleCols[col.id] != null || (!col.hidden && hiddenCols[col.id] == null)) {
                this.visibility.push(i);
            }
        }
    }


    // ************************
    // Sort

    if (this.sort == null && this.template.sort) {
        this.sort = this.template.sort.slice(0);
    }

    if (this.sort != null) {
        var sortParam = this.sort;
        this.sort = [];
        for (var i = 0, len = sortParam.length; i < len; ++i) {
            var col = parseInt(sortParam[i]);
            if (isNaN(col)) {
                var desc = 0;
                if (sortParam[i].charAt(0) == '-') {
                    desc = 1;
                    sortParam[i] = sortParam[i].substring(1);
                }
                var index = $WH.in_array(this.columns, sortParam[i], function(x) {
                    return x.id;
                });
                if (index != -1) {
                    if (desc) {
                        this.sort.push( - (index + 1));
                    }
                    else {
                        this.sort.push(index + 1);
                    }
                }
            }
            else {
                this.sort.push(col);
            }
        }
    }
    else {
        this.sort = [];
    }

    if (this.debug || g_user.debug) {
        this.columns.splice(0, 0, {
            id: 'debug-id',
            compute: function(data, td) {
                if (data.id) {
                    $WH.ae(td, $WH.ct(data.id));
                }
            },
            getVisibleText: function(data) {
                if (data.id) {
                    return data.id;
                }
                else {
                    return '';
                }
            },
            getValue: function(data) {
                if (data.id) {
                    return data.id;
                }
                else {
                    return 0;
                }
            },
            sortFunc: function(a, b, col) {
                if (a.id == null) {
                    return -1;
                }
                else if (b.id == null) {
                    return 1;
                }

                return $WH.strcmp(a.id, b.id);
            },
            name: 'ID',
            width: '5%',
            tooltip: 'ID'
        });
        this.visibility.splice(0, 0, -1);

        for (var i = 0, len = this.visibility.length; i < len; ++i) {
            this.visibility[i] = this.visibility[i] + 1;
        }

        for (var i = 0, len = this.sort.length; i < len; ++i) {
            if (this.sort[i] < 0) {
                this.sort[i] = this.sort[i] - 1;
            }
            else {
                this.sort[i] = this.sort[i] + 1;
            }
        }
    }

    if (this.tabs) {
        this.tabIndex = this.tabs.add(this.getTabName(), {
            id: this.id,
            onLoad: this.initialize.bind(this)
        });

        this.tabClick = Tabs.trackClick.bind(this.tabs, this.tabs.tabs[this.tabIndex]);
    }
    else {
        this.initialize();
    }
}

Listview.MODE_DEFAULT  = 0;
Listview.MODE_CHECKBOX = 1;
Listview.MODE_DIV      = 2;
Listview.MODE_TILED    = 3;
Listview.MODE_CALENDAR = 4;
Listview.MODE_FLEXGRID = 5;

Listview.prototype = {
    initialize: function() {
        if (this.data.length) {
            if (this.computeDataFunc != null) {
                for (var i = 0, len = this.data.length; i < len; ++i) {
                    this.computeDataFunc(this.data[i]);
                }
            }
        }

        if (this.tabs) {
            this.pounded = (this.tabs.poundedTab == this.tabIndex);
            if (this.pounded) {
                this.readPound();
            }
        }
        else {
            this.readPound();
        }

        this.applySort();

        var obcResult;
        if (this.onBeforeCreate != null) {
            if (typeof this.onBeforeCreate == 'function') {
                obcResult = this.onBeforeCreate();
            }
            else {
                for (var i = 0; i < this.onBeforeCreate.length; ++i) {
                    (this.onBeforeCreate[i].bind(this))();
                }
            }
        }

        this.noData = $WH.ce('div');
        this.noData.className = 'listview-nodata text';

        if (this.mode == Listview.MODE_DIV) {
            this.mainContainer = this.mainDiv = $WH.ce('div');
            if (!this.noStyle) {
                this.mainContainer.className = 'listview-mode-div';
            }
        }
        else if (this.mode == Listview.MODE_FLEXGRID) {
            /* iconDB todo evaluate */
            this.mainContainer = this.mainDiv = $WH.ce('div', { className: 'listview-mode-flexgrid' });
            this.mainContainer.setAttribute('data-cell-min-width', this.template.cellMinWidth);
            if (this.clickable)
                this.mainContainer.className += ' clickable';

            var layout = $('.layout');
            var totalWidth = parseInt(layout.css('max-width')) - (parseInt(layout.css('padding-left')) || 0) - (parseInt(layout.css('padding-right')) || 0);
            var slots = Math.floor(totalWidth / this.template.cellMinWidth);
            var extraStyle = '.listview-mode-flexgrid[data-cell-min-width="' + this.template.cellMinWidth + '"] > div {min-width:' + this.template.cellMinWidth + "px;width:" + (100 / slots) + "%}";
            while (slots--)
            {
                if (slots)
                {
                    extraStyle += "\n@media screen and (max-width: " + (((slots + 1) * this.template.cellMinWidth) - 1 + 40) + "px) {";
                    extraStyle += '\n    .listview-mode-flexgrid[data-cell-min-width="' + this.template.cellMinWidth + '"] > div {width:' + (100 / slots) + "%}";
                    extraStyle += "\n}"
                }
            }

            $("<style/>").text(extraStyle).appendTo(document.head)
        }
        else {
            this.mainContainer = this.table = $WH.ce('table');
            this.thead = $WH.ce('thead');
            this.tbody = $WH.ce('tbody');

            if (this.clickable) {
                this.tbody.className = 'clickable';
            }

            if (this.mode == Listview.MODE_TILED || this.mode == Listview.MODE_CALENDAR) {
                if (!this.noStyle)
                    this.table.className = 'listview-mode-' + (this.mode == Listview.MODE_TILED ? 'tiled' : 'calendar');

                var
                    width = (100 / this.nItemsPerRow) + '%',
                    colGroup = $WH.ce('colgroup'),
                    col;

                for (var i = 0; i < this.nItemsPerRow; ++i) {
                    col = $WH.ce('col');
                    col.style.width = width;
                    $WH.ae(colGroup, col);
                }

                $WH.ae(this.mainContainer, colGroup);

                if (this.sortOptions)
                    setTimeout((function() { this.updateSortArrow() }).bind(this), 0);
            }
            else {
                if (!this.noStyle) {
                    this.table.className = 'listview-mode-default';
                }

                this.createHeader();
                this.updateSortArrow();
            }

            $WH.ae(this.table, this.thead);
            $WH.ae(this.table, this.tbody);
        }

        this.createBands();

        if (this.customFilter != null) {
            this.updateFilters();
        }

        this.updateNav();
        this.refreshRows();
        this.updateBrowseSession();

        if (this.onAfterCreate != null) {
            this.onAfterCreate(obcResult);
        }
    },

    createHeader: function() {
        var tr = $WH.ce('tr');

        if (this.mode == Listview.MODE_CHECKBOX) {
            var
                th  = $WH.ce('th'),
                div = $WH.ce('div'),
                a   = $WH.ce('a');

            th.style.width = '33px';

            a.href = 'javascript:;';
            a.className = 'listview-cb';
            $WH.ns(a);
            $WH.ae(a, $WH.ct(String.fromCharCode(160)));

            $WH.ae(div, a);
            $WH.ae(th, div);
            $WH.ae(tr, th);
        }

        for (var i = 0, len = this.visibility.length; i < len; ++i) {
            var
                reali = this.visibility[i],
                col = this.columns[reali],
                th = $WH.ce('th');

            div = $WH.ce('div'),
            a = $WH.ce('a'),
            outerSpan = $WH.ce('span'),
            innerSpan = $WH.ce('span');

            col.__th = th;

            if (this.filtrable && (col.filtrable == null || col.filtrable)) {
                a.onmouseup = Listview.headerClick.bind(this, col, reali);
                a.onclick = a.oncontextmenu = $WH.rf;
            }
            else if (this.sortable) {
                a.href = 'javascript:;';
                a.onclick = this.sortBy.bind(this, reali + 1);
            }

            if (a.onclick) {
                a.onmouseover = Listview.headerOver.bind(this, a, col);
                a.onmouseout = $WH.Tooltip.hide;
                $WH.ns(a);
            }
            else {
                a.className = 'static';
            }

            if (col.width != null) {
                th.style.width = col.width;
            }

            if (col.align != null) {
                th.style.textAlign = col.align;
            }

            if (col.span != null) {
                th.colSpan = col.span;
            }

            $WH.ae(innerSpan, $WH.ct(col.name));
            $WH.ae(outerSpan, innerSpan);

            $WH.ae(a, outerSpan);
            $WH.ae(div, a);
            $WH.ae(th, div);

            $WH.ae(tr, th);
        }

        if (this.hideHeader) {
            this.thead.style.display = 'none';
        }

        $WH.ae(this.thead, tr);
    },

    createSortOptions: function(parent) {
        if (!$.isArray(this.sortOptions))
            return;

        var div = $WH.ce('div');
        div.className = 'listview-sort-options';
        div.innerHTML = LANG.lvnote_sort;
        var sp = $WH.ce('span');
        sp.className = 'listview-sort-options-choices';
        var activeSort = null;
        if ($.isArray(this.sort))
            activeSort = this.sort[0];

        var a;
        var sorts = [];
        for (var i = 0; i < this.sortOptions.length; i++)
        {
            if (this.sortOptions[i].hidden)
                continue;

            a = $WH.ce('a');
            a.href = 'javascript:;';
            a.innerHTML = this.sortOptions[i].name;
            a.onclick = this.sortGallery.bind(this, a, i + 1);
            if (activeSort === i + 1)
                a.className = 'active';

            sorts.push(a)
        }

        for (i = 0; i < sorts.length; i++)
            $WH.ae(sp, sorts[i]);


        $WH.ae(div, sp);
        $WH.aef(parent, div);
    },

    sortGallery: function(el, colNo) {
        var btn = $(el);
        btn.siblings('a').removeClass('active');
        btn.addClass('active');
        this.sortBy(colNo);
    },

    createBands: function() {
        var
            bandTop = $WH.ce('div'),
            bandBot = $WH.ce('div'),
            noteTop = $WH.ce('div'),
            noteBot = $WH.ce('div');

        this.bandTop = bandTop;
        this.bandBot = bandBot;
        this.noteTop = noteTop;
        this.noteBot = noteBot;

        bandTop.className = 'listview-band-top';
        bandBot.className = 'listview-band-bottom';

        this.navTop = this.createNav(true);
        this.navBot = this.createNav(false);

        noteTop.className = noteBot.className = 'listview-note';

        if (this.note) {
            noteTop.innerHTML = this.note;
            var e = $WH.g_getGets();
            if (this.note.indexOf('fi_toggle()') > -1 && !e.filter) {
                fi_toggle();
            }
        }
        else if (this.createNote) {
            this.createNote(noteTop, noteBot);
        }

        this.createSortOptions(noteTop);

        if (this.debug) {
            $WH.ae(noteTop, $WH.ct(" ("));
            var ids = $WH.ce('a');
            ids.onclick = this.getList.bind(this);
            $WH.ae(ids, $WH.ct("CSV"));
            $WH.ae(noteTop, ids);
            $WH.ae(noteTop, $WH.ct(")"));
        }

        if (this._errors) {
            var
                sp = $WH.ce('small'),
                b  = $WH.ce('b');

            b.className = 'q10 icon-report';
            if (noteTop.innerHTML) {
                b.style.marginLeft = '10px';
            }

            g_addTooltip(sp, LANG.lvnote_witherrors, 'q');

            $WH.st(b, LANG.error);
            $WH.ae(sp, b);
            $WH.ae(noteTop, sp);
        }

        if (!noteTop.firstChild && !(this.createCbControls || this.mode == Listview.MODE_CHECKBOX)) {
            $WH.ae(noteTop, $WH.ct(String.fromCharCode(160)));
        }
        if (!(this.createCbControls || this.mode == Listview.MODE_CHECKBOX)) {
            $WH.ae(noteBot, $WH.ct(String.fromCharCode(160)));
        }

        $WH.ae(bandTop, this.navTop);
        if (this.searchable) {
            var
                FI_FUNC = this.updateFilters.bind(this, true),
                FI_PH   = (this._truncated ? LANG.lvsearchdisplayedresults : LANG.lvsearchresults),
                sp      = $WH.ce('span'),
                em      = $WH.ce('em'),
                a       = $WH.ce('a'),
                input   = $WH.ce('input');

            sp.className = 'listview-quicksearch';

            if (this.tabClick) {
                $(sp).click(this.tabClick);
            }

            $WH.ae(sp, em);

            a.href = 'javascript:;';
            a.onclick = function() {
                var foo = this.nextSibling;
                foo.value = '';
                foo.placeholder = FI_PH;
                FI_FUNC();
            };
            a.style.display = 'none';
            $WH.ae(a, $WH.ce('span'));
            $WH.ae(sp, a);
            $WH.ns(a);

            input.setAttribute('type', 'text');
            input.placeholder = FI_PH;
            input.style.width = (this._truncated ? '19em': '15em');
            g_onAfterTyping(input, FI_FUNC, this.searchDelay);

            input.onmouseover = function() {
                if ($WH.trim(this.value) != '') {
                    this.className = '';
                }
            };

            input.onfocus = function() {
                this.className = '';
            };

            input.onblur = function() {
                if ($WH.trim(this.value) == '') {
                    this.value = '';
                }
            };

            input.onkeypress = this.submitSearch.bind(this);

            $WH.ae(sp, input);

            this.quickSearchBox = input;
            this.quickSearchGlass = em;
            this.quickSearchClear = a;

            $WH.ae(bandTop, sp);
        }
        $WH.ae(bandTop, noteTop);

        $WH.ae(bandBot, this.navBot);
        $WH.ae(bandBot, noteBot);

        if (this.createCbControls || this.mode == Listview.MODE_CHECKBOX) {
            if (this.note) {
                noteTop.style.paddingBottom = '5px';
            }

            this.cbBarTop = this.createCbBar(true);
            this.cbBarBot = this.createCbBar(false);

            $WH.ae(bandTop, this.cbBarTop);
            $WH.ae(bandBot, this.cbBarBot);

            if (!this.noteTop.firstChild && !this.cbBarTop.firstChild) {
                this.noteTop.innerHTML = '&nbsp;';
            }

            if (!this.noteBot.firstChild && !this.cbBarBot.firstChild) {
                this.noteBot.innerHTML = '&nbsp;';
            }

            if (this.noteTop.firstChild && this.cbBarTop.firstChild) {
                this.noteTop.style.paddingBottom = '6px';
            }

            if (this.noteBot.firstChild && this.cbBarBot.firstChild) {
                this.noteBot.style.paddingBottom = '6px';
            }
        }

        if (this.hideBands & 1) {
            bandTop.style.display = 'none';
        }
        if (this.hideBands & 2) {
            bandBot.style.display = 'none';
        }

        $WH.ae(this.container, this.bandTop);
        if (this.clip) {
            var clipDiv = $WH.ce('div');
            clipDiv.className = 'listview-clip';
            clipDiv.style.width = this.clip.w + 'px';
            clipDiv.style.height = this.clip.h + 'px';
            this.clipDiv = clipDiv;

            $WH.ae(clipDiv, this.mainContainer);
            $WH.ae(clipDiv, this.noData);
            $WH.ae(this.container, clipDiv);
        }
        else {
            $WH.ae(this.container, this.mainContainer);
            $WH.ae(this.container, this.noData);
        }
        $WH.ae(this.container, this.bandBot);
    },

    createNav: function(top) {
        var
            div = $WH.ce('div'),
            a1 = $WH.ce('a'),
            a2 = $WH.ce('a'),
            a3 = $WH.ce('a'),
            a4 = $WH.ce('a'),
            span = $WH.ce('span'),
            b1 = $WH.ce('b'),
            b2 = $WH.ce('b'),
            b3 = $WH.ce('b');

        div.className = 'listview-nav';

        a1.href = a2.href = a3.href = a4.href = 'javascript:;';

        $WH.ae(a1, $WH.ct(String.fromCharCode(171) + LANG.lvpage_first));
        $WH.ae(a2, $WH.ct(String.fromCharCode(8249) + LANG.lvpage_previous));
        $WH.ae(a3, $WH.ct(LANG.lvpage_next + String.fromCharCode(8250)));
        $WH.ae(a4, $WH.ct(LANG.lvpage_last + String.fromCharCode(187)));

        $WH.ns(a1);
        $WH.ns(a2);
        $WH.ns(a3);
        $WH.ns(a4);

        a1.onclick = this.firstPage.bind(this);
        a2.onclick = this.previousPage.bind(this);
        a3.onclick = this.nextPage.bind(this);
        a4.onclick = this.lastPage.bind(this);

        if (this.mode == Listview.MODE_CALENDAR) {
            $WH.ae(b1, $WH.ct('a'));
            $WH.ae(span, b1);
        }
        else {
            $WH.ae(b1, $WH.ct('a'));
            $WH.ae(b2, $WH.ct('a'));
            $WH.ae(b3, $WH.ct('a'));
            $WH.ae(span, b1);
            $WH.ae(span, $WH.ct(LANG.hyphen));
            $WH.ae(span, b2);
            $WH.ae(span, $WH.ct(LANG.lvpage_of));
            $WH.ae(span, b3);
        }

        $WH.ae(div, a1);
        $WH.ae(div, a2);
        $WH.ae(div, span);
        $WH.ae(div, a3);
        $WH.ae(div, a4);

        if (top) {
            if (this.hideNav & 1) {
                div.style.display = 'none';
            }
        }
        else {
            if (this.hideNav & 2) {
                div.style.display = 'none';
            }
        }

        if (this.tabClick) {
            $('a', div).click(this.tabClick);
        }

        return div;
    },

    createCbBar: function(topBar) {
        var    div = $WH.ce('div');

        if (this.createCbControls) {
            this.createCbControls(div, topBar);
        }

        if (div.firstChild) { // Not empty
            div.className = 'listview-withselected' + (topBar ? '' : '2');
        }

        return div;
    },

    refreshRows: function() {
        var target = null;
        switch (this.mode) {
            case Listview.MODE_DIV:
                target = this.mainContainer;
                break;
            case Listview.MODE_FLEXGRID:
                target = this.mainDiv;
                break;
            default:
                target = this.tbody
        }
        if (!target)
            return;

        $WH.ee(target);

        if (this.nRowsVisible == 0) {
            if (!this.filtered) {
                this.bandTop.style.display = this.bandBot.style.display = 'none';

                this.mainContainer.style.display = 'none';
            }

            this.noData.style.display = '';
            this.showNoData();

            return;
        }

        var
            starti,
            endi,
            func;

        if (! (this.hideBands & 1)) {
            this.bandTop.style.display = '';
        }
        if (! (this.hideBands & 2)) {
            this.bandBot.style.display = '';
        }

        if (this.nDaysPerMonth && this.nDaysPerMonth.length) {
            starti = 0;
            for (var i = 0; i < this.rowOffset; ++i) {
                starti += this.nDaysPerMonth[i];
            }
            endi = starti + this.nDaysPerMonth[i];
        }
        else if (this.nItemsPerPage > 0) {
            starti = this.rowOffset;
            endi = Math.min(starti + this.nRowsVisible, starti + this.nItemsPerPage);

            if (this.filtered && this.rowOffset > 0) { // Adjusts start and end position when listview is filtered
                for (var i = 0, count = 0; i < this.data.length && count < this.rowOffset; ++i) {
                    var row = this.data[i];

                    if (row.__hidden || row.__deleted) {
                        ++starti;
                    }
                    else {
                        ++count;
                    }
                }
                endi += (starti - this.rowOffset);
            }
        }
        else {
            starti = 0;
            endi = this.nRowsVisible;
        }

        var nItemsToDisplay = endi - starti;

        if (this.mode == Listview.MODE_DIV) {
            for (var j = 0; j < nItemsToDisplay; ++j) {
                var
                    i = starti + j,
                    row = this.data[i];

                if (!row) {
                    break;
                }

                if (row.__hidden || row.__deleted) {
                    ++nItemsToDisplay;
                    continue;
                }

                $WH.ae(this.mainDiv, this.getDiv(i));
            }
        }
        else if (this.mode == Listview.MODE_FLEXGRID) {
            for (var j = 0; j < nItemsToDisplay; ++j) {
                var
                    i = starti + j,
                    row = this.data[i];

                if (!row) {
                    break;
                }

                if (row.__hidden || row.__deleted) {
                    ++nItemsToDisplay;
                    continue;
                }

                $WH.ae(this.mainDiv, this.getDiv(i));
            }
        }
        else if (this.mode == Listview.MODE_TILED) {
            var
                k  = 0,
                tr = $WH.ce('tr');

            for (var j = 0; j < nItemsToDisplay; ++j) {
                var
                    i = starti + j,
                row = this.data[i];

                if (!row) {
                    break;
                }

                if (row.__hidden || row.__deleted) {
                    ++nItemsToDisplay;
                    continue;
                }

                $WH.ae(tr, this.getCell(i));

                if (++k == this.nItemsPerRow) {
                    $WH.ae(this.tbody, tr);
                    if (j + 1 < nItemsToDisplay) {
                        tr = $WH.ce('tr');
                    }
                    k = 0;
                }
            }

            if (k != 0) {
                for (; k < 4; ++k) {
                    var foo = $WH.ce('td');
                    foo.className = 'empty-cell';
                    $WH.ae(tr, foo);
                }
                $WH.ae(this.tbody, tr);
            }
        }
        else if (this.mode == Listview.MODE_CALENDAR) {
            var tr = $WH.ce('tr');

            for (var i = 0; i < 7; ++i) {
                var th = $WH.ce('th');
                $WH.st(th, LANG.date_days[i]);
                $WH.ae(tr, th);
            }

            $WH.ae(this.tbody, tr);
            tr = $WH.ce('tr');

            for (var k = 0; k < this.dates[starti].date.getDay(); ++k) {
                var foo = $WH.ce('td');
                foo.className = 'empty-cell';
                $WH.ae(tr, foo);
            }

            for (var j = starti; j < endi; ++j) {
                $WH.ae(tr, this.getEvent(j));

                if (++k == 7) {
                    $WH.ae(this.tbody, tr);
                    tr = $WH.ce('tr');
                    k  = 0;
                }
            }

            if (k != 0) {
                for (; k < 7; ++k) {
                    var foo = $WH.ce('td');
                    foo.className = 'empty-cell';
                    $WH.ae(tr, foo);
                }
                $WH.ae(this.tbody, tr);
            }
        }
        else { // DEFAULT || CHECKBOX
            for (var j = 0; j < nItemsToDisplay; ++j) {
                var
                i = starti + j,
                row = this.data[i];

                if (!row) {
                    break;
                }

                if (row.__hidden || row.__deleted) {
                    ++nItemsToDisplay;
                    continue;
                }

                $WH.ae(this.tbody, this.getRow(i));
            }
        }

        this.mainContainer.style.display = '';
        this.noData.style.display = 'none';
    },

    showNoData: function() {
        var div = this.noData;
        $WH.ee(div);

        var result = -1;
        if (this.onNoData) {
            result = (this.onNoData.bind(this, div))();
        }

        if (result == -1) {
            $WH.ae(this.noData, $WH.ct(this.filtered ? LANG.lvnodata2 : LANG.lvnodata));
        }
    },

    getDiv: function(i) {
        var row = this.data[i];

        if (row.__div == null || this.minPatchVersion != row.__minPatch) {
            this.createDiv(row, i);
        }

        return row.__div;
    },

    createDiv: function(row, i) {
        var div = $WH.ce('div');
        row.__div = div;
        if (this.minPatchVersion) {
            row.__minPatch = this.minPatchVersion;
        }

        (this.template.compute.bind(this, row, div, i))();
    },

    getCell: function(i) {
        var row = this.data[i];

        if (row.__td == null) {
            this.createCell(row, i);
        }

        return row.__td;
    },

    createCell: function(row, i) {
        var td = $WH.ce('td');
        row.__td = td;

        (this.template.compute.bind(this, row, td, i))();
    },

    getEvent: function(i) {
        var row = this.dates[i];

        if (row.__td == null) {
            this.createEvent(row, i);
        }

        return row.__td;
    },

    createEvent: function(row, i) {
        row.events = $WH.array_filter(this.data, function(holiday) {
            if (holiday.__hidden || holiday.__deleted) {
                return false;
            }

            var dates = Listview.funcBox.getEventNextDates(holiday.startDate, holiday.endDate, holiday.rec || 0, row.date);
            if (dates[0] && dates[1]) {
                dates[0].setHours(0, 0, 0, 0);
                dates[1].setHours(0, 0, 0, 0);
                return dates[0] <= row.date && dates[1] >= row.date;
            }

            return false;
        });

        var td = $WH.ce('td');
        row.__td = td;

        if (row.date.getFullYear() == g_serverTime.getFullYear() && row.date.getMonth() == g_serverTime.getMonth() && row.date.getDate() == g_serverTime.getDate()) {
            td.className = 'calendar-today';
        }

        var div = $WH.ce('div');
        div.className = 'calendar-date';
        $WH.st(div, row.date.getDate());
        $WH.ae(td, div);

        div = $WH.ce('div');
        div.className = 'calendar-event';
        $WH.ae(td, div);

        (this.template.compute.bind(this, row, div, i))();

        if (this.getItemLink) {
            td.onclick = this.itemClick.bind(this, row);
        }
    },

    getRow: function(i) {
        var row = this.data[i];

        if (row.__tr == null) {
            this.createRow(row);
        }

        return row.__tr;
    },

    setRow: function(newRow) {
        if (this.data[newRow.pos]) {
            this.data[newRow.pos] = newRow;
            this.data[newRow.pos].__tr = newRow.__tr;
            this.createRow(this.data[newRow.pos]);
            this.refreshRows();
        }
    },

    createRow: function(row) {
        var tr = $WH.ce('tr');
        row.__tr = tr;

        if (this.mode == Listview.MODE_CHECKBOX) {
            var td = $WH.ce('td');

            if (!row.__nochk) {
                td.className = 'listview-cb';
                td.onclick = Listview.cbCellClick;

                var cb = $WH.ce('input');
                $WH.ns(cb);
                cb.type = 'checkbox';
                cb.onclick = Listview.cbClick;

                if (row.__chk) {
                    cb.checked = true;
                }

                row.__cb = cb;

                $WH.ae(td, cb);
            }

            $WH.ae(tr, td);
        }

        for (var i = 0, len = this.visibility.length; i < len; ++i) {
            var
                reali = this.visibility[i],
                col = this.columns[reali],
                td = $WH.ce('td'),
                result;

            if (col.align != null) {
                td.style.textAlign = col.align;
            }

            if (col.compute) {
                result = (col.compute.bind(this, row, td, tr, reali))();
            }
            else {
                if (row[col.value] != null) {
                    result = row[col.value];
                }
                else {
                    result = -1;
                }
            }

            if (result != -1 && result != null) {
                td.insertBefore($WH.ct(result), td.firstChild);
            }

            $WH.ae(tr, td);
        }

        if (this.mode == Listview.MODE_CHECKBOX && row.__chk) {
            tr.className = 'checked';
        }

        if (row.frommerge == 1) {
            tr.className += ' mergerow';
        }

        if (this.getItemLink) {
            tr.onclick = this.itemClick.bind(this, row);
        }
    },

    itemClick: function(row, e) {
        e = $WH.$E(e);

        var
            i = 0,
            el = e._target;

        while (el && i < 3) {
            if (el.nodeName == 'A') {
                return;
            }
            el = el.parentNode;
        }

        location.href = this.getItemLink(row);
    },

    submitSearch: function(e) {
        e = $WH.$E(e);

        if (!this.onSearchSubmit || e.keyCode != 13) {
            return;
        }

        for (var i = 0, len = this.data.length; i < len; ++i) {
            if (this.data[i].__hidden) {
                continue;
            }

            (this.onSearchSubmit.bind(this, this.data[i]))();
        }
    },

    validatePage: function() {
        var
            rpp = this.nItemsPerPage,
            ro  = this.rowOffset,
            len = this.nRowsVisible;

        if (ro < 0) {
            this.rowOffset = 0;
        }
        else if (this.mode == Listview.MODE_CALENDAR) {
            this.rowOffset = Math.min(ro, this.nDaysPerMonth.length - 1);
        }
        else {
            this.rowOffset = this.getRowOffset(ro + rpp > len ? len - 1 : ro);
        }
    },

    getRowOffset: function(rowNo) {
        var rpp = this.nItemsPerPage;

        return (rpp > 0 && rowNo > 0 ? Math.floor(rowNo / rpp) * rpp : 0);
    },

    resetRowVisibility: function() {
        for (var i = 0, len = this.data.length; i < len; ++i) {
            this.data[i].__hidden = false;
        }

        this.filtered = false;
        this.rowOffset = 0;
        this.nRowsVisible = this.data.length;
    },

    getColText: function(row, col) {
        var text = '';
        if (this.template.getVisibleText) {
            text = $WH.trim(this.template.getVisibleText(row) + ' ');
        }

        if (col.getVisibleText) {
            return text + col.getVisibleText(row)
        }

        if (col.getValue) {
            return text + col.getValue(row)
        }

        if (col.value) {
            return text + row[col.value]
        }

        if (col.compute) {
            return text + col.compute(row, $WH.ce('td'), $WH.ce('tr'));
        }

        return ""
    },

    resetFilters: function() {
        for (var j = 0, len2 = this.visibility.length; j < len2; ++j) {
            var realj = this.visibility[j];
            var col = this.columns[realj];

            if (col.__filter) {
                col.__th.firstChild.firstChild.className = '';
                col.__filter = null;
                --(this.nFilters);
            }
        }
    },

    updateFilters: function(refresh) {
        $WH.Tooltip.hide();
        this.resetRowVisibility();

        var
            searchText,
            parts,
            nParts;

        if (this.searchable) {
            this.quickSearchBox.parentNode.style.display = '';

            searchText = $WH.trim(this.quickSearchBox.value);

            if (searchText) {
                this.quickSearchGlass.style.display = 'none';
                this.quickSearchClear.style.display = '';

                searchText = searchText.toLowerCase().replace(/\s+/g, ' ');

                parts = searchText.split(' ');
                nParts = parts.length;
            }
            else {
                this.quickSearchGlass.style.display = '';
                this.quickSearchClear.style.display = 'none';
            }
        }
        else if (this.quickSearchBox) {
            this.quickSearchBox.parentNode.style.display = 'none';
        }

        if (!searchText && this.nFilters == 0 && this.customFilter == null) {
            if (refresh) {
                this.updateNav();
                this.refreshRows();
                this.updateBrowseSession();
            }

            return;
        }

        // Numerical
        var filterFuncs = {
            1: function(x, y) {
                return x > y;
            },
            2: function(x, y) {
                return x == y;
            },
            3: function(x, y) {
                return x < y;
            },
            4: function(x, y) {
                return x >= y;
            },
            5: function(x, y) {
                return x <= y;
            },
            6: function(x, y, z) {
                return y <= x && x <= z;
            }
        };

        // Range
        var filterFuncs2 = {
            1: function(min, max, y) {
                return max > y;
            },
            2: function(min, max, y) {
                return min <= y && y <= max;
            },
            3: function(min, max, y) {
                return min < y;
            },
            4: function(min, max, y) {
                return max >= y;
            },
            5: function(min, max, y) {
                return min <= y;
            },
            6: function(min, max, y, z) {
                return y <= max && min <= z;
            }
        };

        var nRowsVisible = 0;

        for (var i = 0, len = this.data.length; i < len; ++i) {
            var
                row = this.data[i],
                nFilterMatches = 0,
                nSearchMatches = 0,
                matches = [];

            row.__hidden = true;

            if (this.customFilter && !this.customFilter(row, i)) {
                continue;
            }

            for (var j = 0, len2 = this.visibility.length; j < len2; ++j) {
                var realj = this.visibility[j];
                var col = this.columns[realj];

                if (col.__filter) {
                    var
                        filter = col.__filter,
                        result = false;

                    if (col.type != null && col.type == 'range') {
                        var
                            minValue = col.getMinValue(row),
                            maxValue = col.getMaxValue(row);

                        result = (filterFuncs2[filter.type])(minValue, maxValue, filter.value, filter.value2);
                    }
                    else if (col.type == null || col.type == 'num' || filter.type > 0) {
                        var value = null;

                        if (col.getValue) {
                            value = col.getValue(row);
                        }
                        else if (col.value) {
                            value = parseFloat(row[col.value]);
                        }

                        if (!value) {
                            value = 0;
                        }

                        result = (filterFuncs[filter.type])(value, filter.value, filter.value2);
                    }
                    else {
                        var text = this.getColText(row, col);

                        if (text) {
                            text = text.toString().toLowerCase();

                            if (filter.invert) {
                                result = text.match(filter.regex) != null;
                            }
                            else {
                                var foo = 0;

                                for (var k = 0, len3 = filter.words.length; k < len3; ++k) {
                                    if (text.indexOf(filter.words[k]) != -1) {
                                        ++foo;
                                    }
                                    else {
                                        break;
                                    }
                                }

                                result = (foo == filter.words.length);
                            }
                        }
                    }

                    if (filter.invert) {
                        result = !result;
                    }

                    if (result) {
                        ++nFilterMatches;
                    }
                    else {
                        break;
                    }
                }

                if (searchText) {
                    var text = this.getColText(row, col);
                    if (text) {
                        text = text.toString().toLowerCase();

                        for (var k = 0, len3 = parts.length; k < len3; ++k) {
                            if (!matches[k]) {
                                if (text.indexOf(parts[k]) != -1) {
                                    matches[k] = 1;
                                    ++nSearchMatches;
                                }
                            }
                        }
                    }
                }
            }

            if (row.__alwaysvisible ||
               ((this.nFilters == 0 || nFilterMatches == this.nFilters) &&
               (!searchText || nSearchMatches == nParts))) {
                row.__hidden = false;
                ++nRowsVisible;
            }
        }

        this.filtered = (nRowsVisible < this.data.length);
        this.nRowsVisible = nRowsVisible;

        if (refresh) {
            this.updateNav();
            this.refreshRows();
            this.updateBrowseSession();
        }
    },

    changePage: function() {
        this.validatePage();

        this.refreshRows();
        this.updateNav();
        this.updatePound();
        this.updateBrowseSession();

        var
            scroll = $WH.g_getScroll(),
            c = $WH.ac(this.container);

        if (scroll.y > c[1]) {
            scrollTo(scroll.x, c[1]);
        }
    },

    firstPage: function() {
        this.rowOffset = 0;
        this.changePage();

        return false;
    },

    previousPage: function() {
        this.rowOffset -= this.nItemsPerPage;
        this.changePage();

        return false;
    },

    nextPage: function() {
        this.rowOffset += this.nItemsPerPage;
        this.changePage();

        return false;
    },

    lastPage: function() {
        this.rowOffset = 99999999;
        this.changePage();

        return false;
    },

    addSort: function(arr, colNo) {
        var i = $WH.in_array(arr, Math.abs(colNo), function(x) {
            return Math.abs(x);
        });

        if (i != -1) {
            colNo = arr[i];
            arr.splice(i, 1);
        }

        arr.splice(0, 0, colNo);
    },

    sortBy: function(colNo) {
        var sorts = this.sortOptions || this.columns;
        if (colNo <= 0 || colNo > sorts.length) {
            return;
        }

        if (Math.abs(this.sort[0]) == colNo) {
            this.sort[0] = -this.sort[0];
        }
        else {
            var defaultSort = -1;
            if (sorts[colNo-1].type == 'text') {
                defaultSort = 1;
            }

            this.addSort(this.sort, defaultSort * colNo);
        }

        this.applySort();
        this.refreshRows();
        this.updateSortArrow();
        this.updatePound();
    },

    applySort: function() {
        if (this.sort.length == 0) {
            return;
        }

        Listview.sort = this.sort;
        Listview.columns = this.columns;
        Listview.sortOptions = this.sortOptions;

        if (this.indexCreated) {
            this.data.sort(Listview.sortIndexedRows.bind(this));
        }
        else {
            this.data.sort(Listview.sortRows.bind(this));
        }

        this.updateSortIndex();
        this.updateBrowseSession();
    },

    setSort: function(sort, refresh, updatePound) {
        if (this.sort.toString() != sort.toString()) {
            this.sort = sort;
            this.applySort();

            if (refresh) {
                this.refreshRows();
            }

            if (updatePound) {
                this.updatePound();
            }
        }
    },

    readPound: function() {
        if (!this.poundable || !location.hash.length) {
            return false;
        }

        var _ = location.hash.substr(1);
        if (this.tabs) {
            var n = _.lastIndexOf(':');

            if (n == -1) {
                return false;
            }

            _ = _.substr(n + 1);
        }

        var num = parseInt(_);
        if (!isNaN(num)) {
            this.rowOffset = num;
            this.validatePage();

            if (this.poundable != 2) {
                var sort = [];
                var matches= _.match(/(\+|\-)[0-9]+/g);
                if (matches != null) {
                    var sorts = this.sortOptions || this.columns;
                    for (var i = matches.length - 1; i >= 0; --i) {
                        var colNo = parseInt(matches[i]) | 0;
                        var _ = Math.abs(colNo);
                        if (_ <= 0 || _ > sorts.length) {
                            break;
                        }
                        this.addSort(sort, colNo);
                    }

                    this.setSort(sort, false, false);
                }
            }

            if (this.tabs) {
                this.tabs.setTabPound(this.tabIndex, this.getTabPound());
            }
        }
    },

    updateBrowseSession: function () {
        if ((!window.JSON) || (!$WH.localStorage.isSupported())) {
            return;
        }

        // if ((typeof fi_filters == 'undefined') && (location.pathname != '/search')) {
        if ((typeof fi_filters == 'undefined') && !(/\?search/.test(location.search))) {
            return;
        }

        if (this.data.length < 3) {
            return;
        }

        if (typeof this.getItemLink != 'function') {
            return;
        }

        var max    = 5;
        var path   = location.pathname + location.search;
        var expire = (new Date(g_serverTime.getTime() - 1000 * 60 * 60 * 24 * 3)).getTime();
        var lv     = $WH.localStorage.get('lvBrowse');

        if (lv) {
            lv = window.JSON.parse(lv);
            for (var i = 0; i < lv.length; i++) {
                if ((lv[i].path == path) || (lv[i].when < expire)) {
                    lv.splice(i--, 1);
                }
            }

            if (lv.length >= max) {
                lv.splice(max - 1, lv.length - max - 1);
            }
        }
        else {
            lv = [];
        }

        var
            urls = [],
            url,
            pattern = /^\?[-a-z]+=\d+/i;

        for (var i = 0; i < this.data.length; i++) {
            if (url = pattern.exec(this.getItemLink(this.data[i]))) {
                urls.push(url[0]);
            }
        }

        if (urls.length < 3) {
            return;
        }

        lv.unshift({
            path: path,
            hash: location.hash,
            when: g_serverTime.getTime(),
            urls: urls
        });

        $WH.localStorage.set('lvBrowse', window.JSON.stringify(lv))
    },

    updateSortArrow: function() {
        if (!this.sort.length || !this.thead || this.mode == Listview.MODE_CALENDAR /* || this.searchSort */) {
            return;
        }

        var i = $WH.in_array(this.visibility, Math.abs(this.sort[0]) - 1);

        if (i == -1) {
            return;
        }

        if (this.mode == Listview.MODE_TILED) {
            if (!this.sortOptions)
                return;

            var a = $('.listview-sort-options a', this.noteTop).get(i);
            if (this.lsa && this.lsa != a)
                this.lsa.className = '';

            a.className = this.sort[0] < 0 ? 'active sortdesc' : 'active sortasc';
            this.lsa = a;
            return;
        }

        if (this.mode == Listview.MODE_CHECKBOX && i < this.thead.firstChild.childNodes.length - 1) {
            i += 1;
        }

        var span = this.thead.firstChild.childNodes[i].firstChild.firstChild.firstChild;

        if (this.lsa && this.lsa != span) { // lastSortArrow
            this.lsa.className = '';
        }

        span.className = (this.sort[0] < 0 ? 'sortdesc': 'sortasc');
        this.lsa = span;
    },

    updateSortIndex: function() {
        var _ = this.data;

        for (var i = 0, len = _.length; i < len; ++i) {
            _[i].__si = i; // sortIndex
        }

        this.indexCreated = true;
    },

    updateTabName: function() {
        if (this.tabs && this.tabIndex != null) {
            this.tabs.setTabName(this.tabIndex, this.getTabName());
        }
    },

    updatePound: function(useCurrentSort) {
        if (!this.poundable) {
            return;
        }

        var
            _  = '',
            id = '';

        if (useCurrentSort) {
            if (location.hash.length && this.tabs) {
                var n = location.hash.lastIndexOf(':');
                if (n != -1 && !isNaN(parseInt(location.hash.substr(n + 1)))) {
                    _ = location.hash.substr(n + 1);
                }
            }
        }
        else {
            _ = this.getTabPound();
        }

        if (this.customPound) {
            id = this.customPound;
        }
        else if (this.tabs) {
            id = this.id;
        }

        if (_ && this.tabs) {
            this.tabs.setTabPound(this.tabIndex, _);
        }

        location.replace('#' + id + (id && _ ? ':' : '') + _);
    },

    updateNav: function() {
        var
            arr      = [this.navTop, this.navBot],
            _        = this.nItemsPerPage,
            __       = this.rowOffset,
            ___      = this.nRowsVisible,
            first    = 0,
            previous = 0,
            next     = 0,
            last     = 0,
            date     = new Date();

        if (___ > 0) {
            if (! (this.hideNav & 1)) {
                arr[0].style.display = '';
            }
            if (! (this.hideNav & 2)) {
                arr[1].style.display = '';
            }
        }
        else {
            arr[0].style.display = arr[1].style.display = 'none';
        }

        if (this.mode == Listview.MODE_CALENDAR) {
            for (var i = 0; i < this.nDaysPerMonth.length; ++i) {
                if (i == __)  { // Selected month
                    if (i > 0)
                        previous = 1;
                    if (i > 1)
                        first = 1;
                    if (i < this.nDaysPerMonth.length - 1)
                        next = 1;
                    if (i < this.nDaysPerMonth.length - 2)
                        last = 1;
                }
            }

            date.setTime(this.startOnMonth.valueOf());
            date.setMonth(date.getMonth() + __);
        }
        else {
            if (_) {
                if (__ > 0) {
                    previous = 1;
                    if (__ >= _ + _) {
                        first = 1;
                    }
                }
                if (__ + _ < ___) {
                    next = 1;
                    if (__ + _ + _ < ___) {
                        last = 1;
                    }
                }
            }
        }

        for (var i = 0; i < 2; ++i) {
            var childs = arr[i].childNodes;

            childs[0].style.display = (first ? '': 'none');
            childs[1].style.display = (previous ? '': 'none');
            childs[3].style.display = (next ? '': 'none');
            childs[4].style.display = (last ? '': 'none');
            childs = childs[2].childNodes;

            if (this.mode == Listview.MODE_CALENDAR) {
                childs[0].firstChild.nodeValue = LANG.date_months[date.getMonth()] + ' ' + date.getFullYear();
            }
            else {
                childs[0].firstChild.nodeValue = __ + 1;
                childs[2].firstChild.nodeValue = _ ? Math.min(__ + _, ___) : ___;
                childs[4].firstChild.nodeValue = ___;
            }
        }
    },

    getTabName: function() {
        var
            name = this.name,
            n = this.data.length;

        for (var i = 0, len = this.data.length; i < len; ++i) {
            if (this.data[i].__hidden || this.data[i].__deleted) {
                --n;
            }
        }

        if (n > 0 && !this.hideCount) {
            name += $WH.sprintf(LANG.qty, n);
        }

        return name;
    },

    getTabPound: function() {
        var buffer = '';

        buffer += this.rowOffset;

        if (this.poundable != 2 && this.sort.length) {
            buffer += ('+' + this.sort.join('+')).replace(/\+\-/g, '-');
        }

        return buffer;
    },

    getCheckedRows: function() {
        var checkedRows = [];

        for (var i = 0, len = this.data.length; i < len; ++i) {
            var _ = this.data[i];

            if ((_.__cb && _.__cb.checked) || (!_.__cb && _.__chk)) {
                checkedRows.push(_);
            }
        }

        return checkedRows;
    },

    resetCheckedRows: function() {
        for (var i = 0, len = this.data.length; i < len; ++i) {
            var _ = this.data[i];

            if (_.__cb) {
                _.__cb.checked = false;
            }
            else if (_.__chk) {
                _.__chk = null;
            }

            if (_.__tr) {
                _.__tr.className = _.__tr.className.replace('checked', '');
            }
        }
    },

    deleteRows: function(rows) {
        if (!rows || !rows.length) {
            return;
        }

        for (var i = 0, len = rows.length; i < len; ++i) {
            var row = rows[i];

            if (!row.__hidden && !row.__hidden) {
                this.nRowsVisible -= 1;
            }

            row.__deleted = true;
        }

        this.updateTabName();

        if (this.rowOffset >= this.nRowsVisible) {
            this.previousPage();
        }
        else {
            this.refreshRows();
            this.updateNav();
            this.updateBrowseSession();
        }
    },

    setData: function(data) {
        this.data = data;
        this.indexCreated = false;

        this.resetCheckedRows();
        this.resetRowVisibility();

        if (this.tabs) {
            this.pounded = (this.tabs.poundedTab == this.tabIndex);
            if (this.pounded) {
                this.readPound();
            }
        }
        else {
            this.readPound();
        }

        this.applySort();
        this.updateSortArrow();

        if (this.customFilter != null) {
            this.updateFilters();
        }

        this.updateNav();
        this.refreshRows();
        this.updateBrowseSession();
    },

    getClipDiv: function() {
        return this.clipDiv;
    },

    getNoteTopDiv: function() {
        return this.noteTop;
    },

    focusSearch: function() {
        this.quickSearchBox.focus();
    },

    clearSearch: function() {
        this.quickSearchBox.value = '';
    },

    getList: function() {
        if (!this.debug) {
            return;
        }
        var str = '';
        for (var i = 0; i < this.data.length; i++) {
            if (!this.data[i].__hidden) {
                str += this.data[i].id + ', ';
            }
        }

        listviewIdList.show(str);
    },

    createIndicator: function(v, f, c) {
        if (!this.noteIndicators) {
            this.noteIndicators = $WH.ce('div');
            this.noteIndicators.className = 'listview-indicators';
            $(this.noteIndicators).insertBefore($(this.noteTop));
        }

        var t = this.tabClick;
        $(this.noteIndicators).append(
            $('<span class="indicator"></span>')
            .html(v)
            .append(!f ? '' :
                $('<a class="indicator-x" style="outline: none">[x]</a>')
                .attr('href', (typeof f == 'function' ? 'javascript:;' : f))
                .click(function () { if (t) t(); if (typeof f == 'function') f(); })
            )
            .css('cursor', (typeof c == 'function' ? 'pointer' : null))
            .click(function () { if (t) t(); if (typeof c == 'function') c(); })
        );

        $(this.noteTop).css('padding-top', '7px');
    },

    removeIndicators: function() {
        if (this.noteIndicators) {
            $(this.noteIndicators).remove();
            this.noteIndicators = null;
        }

        $(this.noteTop).css('padding-top', '');
    }
};

Listview.sortRows = function(a, b) {
    var
        sort = Listview.sort,
        cols = Listview.columns;

    for (var i = 0, len = sort.length; i < len; ++i) {
        var
            res,
            _ = cols[Math.abs(sort[i]) - 1];

        if (!_) {
            _ = this.template;
        }

        if (_.sortFunc) {
            res = _.sortFunc(a, b, sort[i]);
        }
        else {
            res = $WH.strcmp(a[_.value], b[_.value]);
        }

        if (res != 0) {
            return res * sort[i];
        }
    }

    return 0;
},

Listview.sortIndexedRows = function(a, b) {
    var
        sort = Listview.sort,
        cols = Listview.sortOptions || Listview.columns,
        res;

    for (var idx in sort) {
        _ = cols[Math.abs(sort[idx]) - 1];

        if (!_) {
            _ = this.template;
        }

        if (_.sortFunc) {
            res = _.sortFunc(a, b, sort[0]);
        }
        else {
            res = $WH.strcmp(a[_.value], b[_.value]);
        }

        if (res != 0) {
            return res * sort[idx];
        }
    }

    return (a.__si - b.__si);
},

Listview.cbSelect = function(v) {
    for (var i = 0, len = this.data.length; i < len; ++i) {
        var _ = this.data[i];
        var v2 = v;

        if (_.__hidden) {
            continue;
        }

        if (!_.__nochk && _.__cb) {
            var
                cb = _.__cb,
                tr = cb.parentNode.parentNode;

            if (v2 == null) {
                v2 = !cb.checked;
            }

            if (cb.checked != v2) {
                cb.checked = v2;
                tr.className = (cb.checked ? tr.className + ' checked' : tr.className.replace('checked', ''));
            }
        }
        else if (v2 == null) {
            v2 = true;
        }

        _.__chk = v2;
    }
};

Listview.cbClick = function(e) {
    setTimeout(Listview.cbUpdate.bind(0, 0, this, this.parentNode.parentNode), 1);
    $WH.sp(e);
};

Listview.cbCellClick = function(e) {
    setTimeout(Listview.cbUpdate.bind(0, 1, this.firstChild, this.parentNode), 1);
    $WH.sp(e);
};

Listview.cbUpdate = function(toggle, cb, tr) {
    if (toggle) {
        cb.checked = !cb.checked;
    }

    tr.className = (cb.checked ? tr.className + ' checked' : tr.className.replace('checked', ''));
};

Listview.headerClick = function(col, i, e) {
    e = $WH.$E(e);

    if (this.tabClick) {
        this.tabClick();
    }

    if (e._button == 3 || e.shiftKey || e.ctrlKey) {
        $WH.Tooltip.hide();
        setTimeout(Listview.headerFilter.bind(this, col, null), 1);
    }
    else {
        this.sortBy(i + 1);
    }

    return false;
};

Listview.headerFilter = function(col, res) {
    var prefilled = '';
    if (col.__filter) {
        if (col.__filter.invert) {
            prefilled += '!';
        }

        prefilled += col.__filter.text;
    }

    if (res == null) {
        var res = prompt($WH.sprintf(LANG.prompt_colfilter1 + (col.type == 'text' ? LANG.prompt_colfilter2 : LANG.prompt_colfilter3), col.name), prefilled);
    }

    if (res != null) {
        var filter = {text: '', type: -1};

        res = $WH.trim(res.replace(/\s+/g, ' '));

        if (!res && this.onEmptyFilter) {
            this.onEmptyFilter(col);
        }
        else if (res) {
            if (res.charAt(0) == '!' || res.charAt(0) == '-') {
                filter.invert = 1;
                res = res.substr(1);
            }

            if (col.type == 'text') {
                filter.type = 0;
                filter.text = res;

                if (filter.invert) {
                    filter.regex = g_createOrRegex(res);
                }
                else {
                    filter.words = res.toLowerCase().split(' ');
                }
            }
            var
                value,
                value2;

            if (res.match(/(>|=|<|>=|<=)\s*([0-9\.]+)/)) {
                value = parseFloat(RegExp.$2);
                if (!isNaN(value)) {
                    switch (RegExp.$1) {
                    case '>':
                        filter.type = 1;
                        break;
                    case '=':
                        filter.type = 2;
                        break;
                    case '<':
                        filter.type = 3;
                        break;
                    case '>=':
                        filter.type = 4;
                        break;
                    case '<=':
                        filter.type = 5;
                        break;
                    }
                    filter.value = value;
                    filter.text = RegExp.$1 + ' ' + value;
                }
            }
            else if (res.match(/([0-9\.]+)\s*\-\s*([0-9\.]+)/)) {
                value = parseFloat(RegExp.$1);
                value2 = parseFloat(RegExp.$2);
                if (!isNaN(value) && !isNaN(value2)) {
                    if (value > value2) {
                        var foo = value;
                        value = value2;
                        value2 = foo;
                    }

                    if (value == value2) {
                        filter.type = 2;
                        filter.value = value;
                        filter.text = '= ' + value;
                    }
                    else {
                        filter.type = 6;
                        filter.value = value;
                        filter.value2 = value2;
                        filter.text = value + ' - ' + value2;
                    }
                }
            }
            else {
                var parts = res.toLowerCase().split(' ');
                if (!col.allText && parts.length == 1 && !isNaN(value = parseFloat(parts[0]))) {
                    filter.type = 2;
                    filter.value = value;
                    filter.text = '= ' + value;
                }
                else if (col.type == 'text') {
                    filter.type = 0;
                    filter.text = res;

                    if (filter.invert) {
                        filter.regex = g_createOrRegex(res);
                    }
                    else {
                        filter.words = parts;
                    }
                }
            }

            if (filter.type == -1) {
                alert(LANG.message_invalidfilter);
                return;
            }
        }

        if (!col.__filter || filter.text != col.__filter.text || filter.invert != col.__filter.invert) {
            var a = col.__th.firstChild.firstChild;

            if (res && filter.text) {
                if (!col.__filter) {
                    a.className = 'q5';
                    ++(this.nFilters);
                }

                col.__filter = filter;
            }
            else {
                if (col.__filter) {
                    a.className = '';
                    --(this.nFilters);
                }

                col.__filter = null;
            }

            this.updateFilters(1);
        }
    }
};

Listview.headerOver = function(a, col, e) {
    var buffer = '';

    buffer += '<b class="q1">' + (col.tooltip ? col.tooltip : col.name) + '</b>';

    if (col.__filter) {
        buffer += '<br />' + $WH.sprintf((col.__filter.invert ? LANG.tooltip_colfilter2 : LANG.tooltip_colfilter1), col.__filter.text);
    }

    buffer += '<br /><span class="q2">' + LANG.tooltip_lvheader1 + '</span>';
    if (this.filtrable && (col.filtrable == null || col.filtrable)) {
        buffer += '<br /><span class="q2">' + ($WH.Browser.opera ? LANG.tooltip_lvheader3 : LANG.tooltip_lvheader2) + '</span>';
    }

    $WH.Tooltip.show(a, buffer, 0, 0, 'q');
};

Listview.extraCols = {
    id: {
        id: 'id',
        name: 'ID',
        width: '5%',
        value: 'id',
        compute: function(data, td) {
            if (data.id) {
                $WH.ae(td, $WH.ct(data.id));
            }
        }
    },

    date: {
        id: 'obj-date',
        name: LANG.added,
        compute: function(data, td) {
            if (data.date) {
                if (data.date <= 86400) {
                    $WH.ae(td, $WH.ct('???'));
                }
                else {
                    var added   = new Date(data.date * 1000);
                    var elapsed = (g_serverTime - added) / 1000;

                    return g_formatDate(td, elapsed, added, null, true);
                }
            }
        },
        sortFunc: function(a, b, col) {
            if (a.date == b.date) {
                return 0;
            }
            else if (a.date < b.date) {
                return -1;
            }
            else {
                return 1;
            }
        }
    },

    cost: {
        id: 'cost',
        name: LANG.cost,
        getValue: function(row) {
            if (row.cost) {
                return (row.cost[2] && row.cost[2][0] ? row.cost[2][0][1] : 0) || (row.cost[1] && row.cost[1][0] ? row.cost[1][0][1] : 0) || row.cost[0];
            }
        },
        compute: function(row, td) {
            if (row.cost) {
                var money = row.cost[0];
                var side = null;
                var items = row.cost[2];
                var currency = row.cost[1];

                if (row.side != null) {
                    side = row.side;
                }
                else  if (row.react != null) {
                    if (row.react[0] == 1 && row.react[1] == -1) { // Alliance only
                        side = 1;
                    }
                    else if (row.react[0] == -1 && row.react[1] == 1) { // Horde only
                        side = 2;
                    }
                }

                Listview.funcBox.appendMoney(td, money, side, items, currency);
            }
        },
        sortFunc: function(a, b, col) {
            if (a.cost == null) {
                return -1;
            }
            else if (b.cost == null) {
                return 1;
            }

            var
                lena = 0,
                lenb = 0,
                lenc = 0,
                lend = 0;

            if (a.cost[2] != null) {
                $WH.array_walk(a.cost[2], function(x, _, __, i) {
                    lena += Math.pow(10, i) + x[1];
                });
            }
            if (b.cost[2] != null) {
                $WH.array_walk(b.cost[2], function(x, _, __, i) {
                    lenb += Math.pow(10, i) + x[1];
                });
            }
            if (a.cost[1] != null) {
                $WH.array_walk(a.cost[1], function(x, _, __, i) {
                    lenc += Math.pow(10, i) + x[1];
                });
            }
            if (b.cost[1] != null) {
                $WH.array_walk(b.cost[1], function(x, _, __, i) {
                    lend += Math.pow(10, i) + x[1];
                });
            }

            return $WH.strcmp(lena, lenb) || $WH.strcmp(lenc, lend) || $WH.strcmp(a.cost[0], b.cost[0]);
        }
    },

    count: {
        id: 'count',
        name: LANG.count,
        value: 'count',
        compute: function(row, td) {
            if (!(this._totalCount > 0 || row.outof > 0)) {
                return;
            }

            if (row.outof) {
                var d = $WH.ce('div');
                d.className = 'small q0';
                $WH.ae(d, $WH.ct($WH.sprintf(LANG.lvdrop_outof, row.outof)));
                $WH.ae(td, d);
            }
            return row.count;
        },
        getVisibleText: function(row) {
            var buff = row.count;
            if (row.outof) {
                buff += ' ' + row.outof;
            }

            return buff;
        },
        sortFunc: function(a, b, col) {
            if (a.count == null) {
                return -1;
            }
            else if (b.count == null) {
                return 1;
            }

            return $WH.strcmp(a.count, b.count);
        }
    },

    percent: {
        id: 'percent',
        name: '%',
        value: 'percent',
        compute: function(row, td) {
            if (row.count <= 0) {
                return '??';
            }

            if (row.pctstack) {
                var text = '';
                var data = eval('(' + row.pctstack + ')');

                for (var amt in data) {
                    var pct = (data[amt] * row.percent) / 100;

                    if (pct >= 1.95) {
                        pct = parseFloat(pct.toFixed(0));
                    }
                    else if (pct >= 0.195) {
                        pct = parseFloat(pct.toFixed(1));
                    }
                    else {
                        pct = parseFloat(pct.toFixed(2));
                    }

                    text += $WH.sprintf(LANG.stackof_format, amt, pct) + '<br />';
                }

                td.className += ' tip';
                g_addTooltip(td, text);
            }

            // var value = parseFloat(row.percent.toFixed(row.percent >= 1.95 ? 0 : (row.percent >= 0.195 ? 1 : 2)));
            var value = parseFloat(row.percent.toFixed(row.percent >= 1.95 ? 1 : 2)); // aowow: doesn't look as nice but i prefer accuracy

            if (row.pctstack) {
                var sp = $WH.ce('span');
                sp.className += ' tip';
                $WH.ae(sp, $WH.ct(value));
                $WH.ae(td, sp);
            }
            else {
                return value;
            }
        },
        getVisibleText: function(row) {
            if (row.count <= 0) {
                return '??';
            }

            if (row.percent >= 1.95) {
                // return row.percent.toFixed(0);
                return row.percent.toFixed(1);
            }
            // else if (row.percent >= 0.195) {
                // return parseFloat(row.percent.toFixed(1));
            // }
            else {
                return parseFloat(row.percent.toFixed(2));
            }
        },
        sortFunc: function(a, b, col) {
            if (a.count == null) {
                return -1;
            }
            else if (b.count == null) {
                return 1;
            }

            if (a.percent >= 1.95) {
                var acmp = a.percent.toFixed(1);
                // var acmp = a.percent.toFixed(0);
            }
            // else if (a.percent >= 0.195) {
                // acmp = parseFloat(a.percent.toFixed(1));
            // }
            else {
                acmp = parseFloat(a.percent.toFixed(2));
            }

            if (b.percent >= 1.95) {
                var bcmp = b.percent.toFixed(1);
                // var bcmp = b.percent.toFixed(0);
            }
            // else if (b.percent >= 0.195) {
                // bcmp = parseFloat(b.percent.toFixed(1));
            // }
            else {
                bcmp = parseFloat(b.percent.toFixed(2));
            }

            return $WH.strcmp(acmp, bcmp);
        }
    },

    stock: {
        id: 'stock',
        name: LANG.stock,
        width: '10%',
        value: 'stock',
        compute: function(row, td) {
            if (row.stock > 0) {
                return row.stock;
            }
            else {
                td.style.fontFamily = 'Verdana, sans-serif';
                return String.fromCharCode(8734);
            }
        },
        getVisibleText: function(row) {
            if (row.stock > 0) {
                return row.stock;
            }
            else {
                return String.fromCharCode(8734) + ' infinity';
            }
        }
    },

    currency: {
        id: 'currency',
        name: LANG.currency,
        getValue: function(row) {
            if (row.currency) {
                return (row.currency[0] ? row.currency[0][1] : 0);
            }
        },
        compute: function(row, td) {
            if (row.currency) {
                var side = null;
                if (row.side != null) {
                    side = row.side;
                }
                else if (row.react != null) {
                    if (row.react[0] == 1 && row.react[1] == -1) { // Alliance only
                        side = 1;
                    }
                    else if (row.react[0] == -1 && row.react[1] == 1) { // Horde only
                        side = 2;
                    }
                }

                Listview.funcBox.appendMoney(td, null, side, null, row.currency);
            }
        },
        sortFunc: function(a, b, col) {
            if (a.currency == null) {
                return -1;
            }
            else if (b.currency == null) {
                return 1;
            }

            var
                lena = 0,
                lenb = 0;

            $WH.array_walk(a.currency, function(x, _, __, i) {
                lena += Math.pow(10, i) + x[1];
            });
            $WH.array_walk(b.currency, function(x, _, __, i) {
                lenb += Math.pow(10, i) + x[1];
            });

            return $WH.strcmp(lena, lenb);
        }
    },

    mode: {
        id: 'mode',
        name: 'Mode',
        after: 'name',
        type: 'text',
        compute: function(row, td) {
            if (row.modes && row.modes.mode) {
                if ((row.modes.mode & 120) == 120 || (row.modes.mode & 3) == 3) {
                    return LANG.pr_note_all;
                }

                return Listview.extraCols.mode.getVisibleText(row);
            }
        },
        getVisibleText: function(row) {
            // TODO: Remove magic numbers.
            var modeNormal = !!(row.modes.mode & 26);
            var modeHeroic = !!(row.modes.mode & 97);
            var player10 = !!(row.modes.mode & 40);
            var player25 = !!(row.modes.mode & 80);

            var specificPlayers;
            if (player10 && !player25) {
                specificPlayers = 10;
            }
            else if (player25 && !player10) {
                specificPlayers = 25;
            }

            var specificMode;
            if (modeNormal && !modeHeroic) {
                specificMode = 'normal';
            }
            else if (modeHeroic && !modeNormal) {
                specificMode = 'heroic';
            }

            if (specificMode) {
                if (specificPlayers) {
                    return $WH.sprintf(LANG['tab_' + specificMode + 'X'], specificPlayers); // e.g. "Heroic 25"
                }
                else {
                    return LANG['tab_' + specificMode]; // e.g. "Heroic"
                }
            }

            if (specificPlayers) {
                return $WH.sprintf(LANG.lvzone_xman, specificPlayers); // e.g. "25-player"
            }

            return LANG.pr_note_all;
        },
        sortFunc: function(a, b, col) {
            if (a.modes && b.modes) {
                return -$WH.strcmp(a.modes.mode, b.modes.mode);
            }
        }
    },

    requires: {
        id: 'requires',
        name: LANG.requires,
        type: 'text',
        compute: function(item, td) {
            if (item.achievement && g_achievements[item.achievement]) {
                $WH.nw(td);
                td.className = 'small';
                td.style.lineHeight = '18px';

                var a = $WH.ce('a');
                a.href = '?achievement=' + item.achievement;
                a.className = 'icontiny tinyspecial';
                a.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + g_achievements[item.achievement].icon.toLowerCase() + '.gif)';
                a.style.whiteSpace = 'nowrap';

                $WH.st(a, g_achievements[item.achievement]['name_' + Locale.getName()]);
                $WH.ae(td, a);
            }
        },
        getVisibleText: function(item) {
            if (item.achievement && g_achievements[item.achievement]) {
                return g_achievements[item.achievement].name;
            }
        },
        sortFunc: function(a, b, col) {
            return $WH.strcmp(this.getVisibleText(a), this.getVisibleText(b));
        }
    },

    reqskill: {
        id: 'reqskill',
        name: LANG.skill,
        width: '10%',
        value: 'reqskill',
        before: 'yield'
    },

    yield: {
        id: 'yield',
        name: LANG.yields,
        type: 'text',
        align: 'left',
        span: 2,
        value: 'name',
        compute: function(row, td, tr) {
            if (row.yield && g_items[row.yield]) {
                var i = $WH.ce('td');
                i.style.width = '1px';
                i.style.padding = '0';
                i.style.borderRight = 'none';

                $WH.ae(i, g_items.createIcon(row.yield, 1));
                $WH.ae(tr, i);
                td.style.borderLeft = 'none';

                var wrapper = $WH.ce('div');

                var a = $WH.ce('a');
                a.style.fontFamily = 'Verdana, sans-serif';
                a.href = '?item=' + row.yield;
                a.className = 'q' + g_items[row.yield].quality;
                $WH.ae(a, $WH.ct(g_items[row.yield]['name_' + Locale.getName()]));
                $WH.ae(wrapper, a);
                $WH.ae(td, wrapper);
            }
        },
        getVisibleText: function(row) {
            if (row.yield && g_items[row.yield]) {
                return g_items[row.yield]['name_' + Locale.getName()];
            }
        },
        sortFunc: function(a, b, col) {
            if (!a.yield || !g_items[a.yield] || !b.yield || !g_items[b.yield]) {
                return (a.yield && g_items[a.yield] ? 1 : (b.yield && g_items[b.yield] ? -1 : 0));
            }
            return -$WH.strcmp(g_items[a.yield].quality, g_items[b.yield].quality) ||
                    $WH.strcmp(g_items[a.yield]['name_' + Locale.getName()], g_items[b.yield]['name_' + Locale.getName()]);
        }
    },

    condition: {
        id: 'condition',
        name: LANG.tab_conditions,
        type: 'text',
        width: '25%',
        compute: function(row, td) {
            if (!row.condition)
                return;

            td.className = 'small';
            td.style.lineHeight = '18px';
            td.style.textAlign  = 'left';

            // tiny links are hard to hit, hmkey?
            td.onclick = (e) => $WH.sp(e);

            var mText = ConditionList.createCell(row.condition);
            Markup.printHtml(mText, td);

            return;
        },
        getVisibleText: function(row) {
            var
                buff = '',
                mText;

            if (!row.condition)
                return buff;

            mText = ConditionList.createCell(row.condition);

            return Markup.removeTags(mText);
        },
        sortFunc: function(a, b, col) {
            var
                text1 = this.getVisibleText(a),
                text2 = this.getVisibleText(b);

            if (text1 != '' && text2 == '')
                return -1;

            if (text2 != '' && text1 == '')
                return 1;

            return $WH.strcmp(text1, text2);
        }
    },
};

Listview.funcBox = {
    createSimpleCol: function(i, n, w, v) {
        return {
            id:    i,
            name:  (LANG[n] !== undefined ? LANG[n] : n),
            width: w,
            value: v
        };
    },

    initLootTable: function(row) {
        var divider;

        if (this._totalCount != null) {
            divider = this._totalCount;
        }
        else {
            divider = row.outof;
        }

        if (divider == 0) {
            if (row.count != -1) {
                row.percent = row.count;
            }
            else {
                row.percent = 0;
            }
        }
        else {
            row.percent = row.count / divider * 100;
        }

        (Listview.funcBox.initModeFilter.bind(this, row))();
    },

// // subtabs here //

    initModeFilter: function(row)
    {
        if(this._lootModes == null)
            this._lootModes = { 99: 0 };

        if(this._distinctModes == null)
            this._distinctModes = { 99: 0 };

        if((!row.modes || row.modes.mode == 4) && row.classs != 12 && row.commondrop)
        {
            this._lootModes[99]++; // Trash
            this._distinctModes[99]++;
        }
        else if(row.modes)
        {
            for(var i = -2; i <= 5; ++i)
            {
                if(this._lootModes[i] == null)
                    this._lootModes[i] = 0;

                if(row.modes.mode & 1 << parseInt(i) + 2)
                    this._lootModes[i]++;
            }

            if(this._distinctModes[row.modes.mode] == null)
                this._distinctModes[row.modes.mode] = 0;
            this._distinctModes[row.modes.mode]++;
        }
    },

    addModeIndicator: function()
    {
        var nModes = 0;
        for(var i in this._distinctModes)
        {
            if(this._distinctModes[i])
                nModes++;
        }

        if(nModes < 2)
            return;

        var    pm      = location.hash.match(/:mode=([^:]+)/),
            order   = [0,-1,-2,1,3,2,4,5,99],
            langref = {
                "-2": LANG.tab_heroic,
                "-1": LANG.tab_normal,
                   0: LANG.tab_noteworthy,
                   1: $WH.sprintf(LANG.tab_normalX, 10),
                   2: $WH.sprintf(LANG.tab_normalX, 25),
                   3: $WH.sprintf(LANG.tab_heroicX, 10),
                   4: $WH.sprintf(LANG.tab_heroicX, 25),
                   5: LANG.tab_raidfinder,
                  99: '' // No indicator for trash
            };

        var f = function(mode, dm, updatePound)
        {
            g_setSelectedLink(this, 'lootmode');

            lv.customPound  = lv.id + (dm != null ? ':mode=' + g_urlize(langref[dm].replace(' ', '')) : '');
            lv.customFilter = function(item) { return Listview.funcBox.filterMode(item, lv._totalCount, mode) };

            lv.updateFilters(1);
            lv.applySort();
            lv.refreshRows();
            if(updatePound)
                lv.updatePound(1);
        };

        var lv = this,
            modes = [],
            a;

        a = $('<a><span>' + LANG.pr_note_all + '</span></a>');
        a[0].f = f.bind(a[0], null, null, 1);
        a.click(a[0].f);
        var firstCallback = f.bind(a[0], null, null, 0);
        firstCallback();

        modes.push($('<span class="indicator-mode"></span>').append(a).append($('<b>' + LANG.pr_note_all + '</b>')));

        for(var j = 0, len = order.length; j < len; ++j)
        {
            var i = order[j];
            if(!this._lootModes[i])
                continue;

            a = $('<a><span>' + langref[i] + '</span> (' + this._lootModes[i] + ')</a>');
            a[0].f = f.bind(a[0], 1 << i + 2, i, 1);
            a.click(a[0].f);

            if(i == 0)
                firstCallback = f.bind(a[0], 1 << i + 2, i, 0);

            if((i < -1 || i > 2) && i != 5)
                a.addClass('icon-heroic');

            modes.push($('<span class="indicator-mode"></span>').append(a).append($('<b' + (i < -1 || i > 2 ? ' class="icon-heroic"' : '') + '>' + langref[i] + ' (' + this._lootModes[i] + ')</b>'))); // jQuery is dumb

            if(pm && pm[1] == g_urlize(langref[i].replace(' ', '')))
                (a[0].f)();
        }

        var showNoteworthy = false;
        for(var i = 0, len = modes.length; i < len; ++i)
        {
            a = $('a', modes[i]);
            if(!$('span', a).html() && modes.length == 3)
                showNoteworthy = true;
            else
                this.createIndicator(modes[i], null, a[0].f);
        }

        if(showNoteworthy)
            firstCallback();

        $(this.noteTop).append($('<div class="clear"></div>'));
    },

    filterMode: function(row, total, mode)
    {
        if(total != null && row.count != null)
        {
            if(row._count == null)
                row._count = row.count;

            var count = row._count;

            if(mode != null && row.modes[mode])
            {
                count = row.modes[mode].count;
                total = row.modes[mode].outof;
            }

            row.__tr    = null;
            row.count   = count;
            row.outof   = total;
            if(total)
                row.percent = count / total * 100;
            else
                row.percent = count;
        }

        return (mode != null ? ((!row.modes || row.modes.mode == 4) && row.classs != 12 && row.commondrop ? (mode == 32) : (row.modes && (row.modes.mode & mode))) : true);
    },

    initSubclassFilter: function(row)
    {
        var i = row.classs || 0;
        if(this._itemClasses == null)
            this._itemClasses = {};
        if(this._itemClasses[i] == null)
            this._itemClasses[i] = 0;
        this._itemClasses[i]++;
    },

    addSubclassIndicator: function()
    {
        var it = location.hash.match(/:type=([^:]+)/),
            order = [];

        for(var i in g_item_classes)
            order.push({ i: i, n: g_item_classes[i] });
        order.sort(function(a, b) { return $WH.strcmp(a.n, b.n) });

        var f = function(itemClass, updatePound)
        {
            g_setSelectedLink(this, 'itemclass');

            lv.customPound  = lv.id + (itemClass != null ? ':type=' + itemClass : '');
            lv.customFilter = function(item) { return itemClass == null || itemClass == item.classs };

            lv.updateFilters(1);
            lv.applySort();
            lv.refreshRows();
            if(updatePound)
                lv.updatePound(1);
        }

        var lv = this,
            classes = [],
            a;

        a = $('<a><span>' + LANG.pr_note_all + '</span></a>');
        a[0].f = f.bind(a[0], null, 1);
        a.click(a[0].f);
        var firstCallback = f.bind(a[0], null, 0);
        firstCallback();

        classes.push($('<span class="indicator-mode"></span>').append(a).append($('<b>' + LANG.pr_note_all + '</b>')));

        for(var j = 0, len = order.length; j < len; ++j)
        {
            var i = order[j].i;
            if(!this._itemClasses[i])
                continue;

            a = $('<a><span>' + g_item_classes[i] + '</span> (' + this._itemClasses[i] + ')</a>');
            a[0].f = f.bind(a[0], i, 1);
            a.click(a[0].f);

            classes.push($('<span class="indicator-mode"></span>').append(a).append($('<b>' + g_item_classes[i] + ' (' + this._itemClasses[i] + ')</b>')));

            if(it && it[1] == g_urlize(i))
                (a[0].f)();
        }

        if(classes.length > 2)
        {
            for(var i = 0, len = classes.length; i < len; ++i)
                this.createIndicator(classes[i], null, $('a', classes[i])[0].f);

            $(this.noteTop).css('padding-bottom', '12px');
            $(this.noteIndicators).append($('<div class="clear"></div>')).insertAfter($(this.navTop));
        }
    },

    initSpellFilter: function (row) {
        if (this._spellTypes == null) {
            this._spellTypes = {};
        }

        if (this._spellTypes[row.cat] == null) {
            this._spellTypes[row.cat] = 0;
        }

        this._spellTypes[row.cat]++;
    },

    addSpellIndicator: function () {
        var it = location.hash.match(/:type=([^:]+)/);

        var f = function (spellCat, updatePound) {
            g_setSelectedLink(this, "spellType");

            lv.customPound = lv.id + (spellCat != null ? ":type=" + spellCat : "");
            lv.customFilter = function (spell) {
                return spellCat == null || spell.cat == spellCat;
            };
            lv.updateFilters(1);
            lv.applySort();
            lv.refreshRows();
            if (updatePound) {
                lv.updatePound(1)
            }
        };

        var
            lv = this,
            categories = [],
            a;

        a = $("<a><span>" + LANG.pr_note_all + "</span></a>");
        a[0].f = f.bind(a[0], null, 1);
        a.click(a[0].f);
        var firstCallback = f.bind(a[0], null, 0);
        firstCallback();
        categories.push($('<span class="indicator-mode"></span>').append(a).append($("<b>" + LANG.pr_note_all + "</b>")));
        for (var i in g_spell_categories) {
            if (!this._spellTypes[i]) {
                continue;
            }
            a = $("<a><span>" + g_spell_categories[i] + "</span> (" + this._spellTypes[i] + ")</a>");
            a[0].f = f.bind(a[0], i, 1);
            a.click(a[0].f);

            categories.push($('<span class="indicator-mode"></span>').append(a).append($("<b>" + g_spell_categories[i] + " (" + this._spellTypes[i] + ")</b>")));

            if (it && it[1] == i) {
                (a[0].f)();
            }
        }
        if (categories.length > 2) {
            for (var i = 0, len = categories.length; i < len; ++i) {
                this.createIndicator(categories[i], null, $("a", categories[i])[0].f)
            }
            $(this.noteTop).css("padding-bottom", "12px");
            $(this.noteIndicators).append($('<div class="clear"></div>')).insertAfter($(this.navTop))
        }
    },

    initStatisticFilter: function(row)
    {
        if(this._achievTypes == null)
            this._achievTypes = {};
        if(this._achievTypes[row.type] == null)
            this._achievTypes[row.type] = 0;
        this._achievTypes[row.type]++;
    },

    addStatisticIndicator: function()
    {
        var it = location.hash.match(/:type=([^:]+)/),
            order = [];

        for(var i in g_achievement_types)
            order.push({ i: i, n: g_achievement_types[i] });
        order.sort(function(a, b) { return $WH.strcmp(a.n, b.n) });

        var f = function(achievType, updatePound)
        {
            g_setSelectedLink(this, 'achievType');

            lv.customPound  = lv.id + (achievType != null ? ':type=' + achievType : '');
            lv.customFilter = function(achievement) { return achievType == null || achievType == achievement.type };

            lv.updateFilters(1);
            lv.applySort();
            lv.refreshRows();
            if(updatePound)
                lv.updatePound(1);
        };

        var lv = this,
            types = [],
            a;

        a = $('<a><span>' + LANG.pr_note_all + '</span></a>');
        a[0].f = f.bind(a[0], null, 1);
        a.click(a[0].f);
        var firstCallback = f.bind(a[0], null, 0);
        firstCallback();

        types.push($('<span class="indicator-mode"></span>').append(a).append($('<b>' + LANG.pr_note_all + '</b>')));

        for(var j = 0, len = order.length; j < len; ++j)
        {
            var i = order[j].i;
            if(!this._achievTypes[i])
                continue;

            a = $('<a><span>' + g_achievement_types[i] + '</span> (' + this._achievTypes[i] + ')</a>');
            a[0].f = f.bind(a[0], i, 1);
            a.click(a[0].f);

            types.push($('<span class="indicator-mode"></span>').append(a).append($('<b>' + g_achievement_types[i] + ' (' + this._achievTypes[i] + ')</b>')));

            if(it && it[1] == i)
                (a[0].f)();
        }

        if(types.length > 2)
        {
            for(var i = 0, len = types.length; i < len; ++i)
                this.createIndicator(types[i], null, $('a', types[i])[0].f);

            $(this.noteTop).append($('<div class="clear"></div>'));
        }
    },

    initQuestFilter: function(row)
    {
        if(this._questTypes == null)
            this._questTypes = {};

        for(var i = 1; i <= 4; ++i)
        {
            if(this._questTypes[i] == null)
                this._questTypes[i] = 0;

            if(row._type && (row._type & 1 << i - 1))
                this._questTypes[i]++;
        }
    },

    addQuestIndicator: function()
    {
        var it = location.hash.match(/:type=([^:]+)/);

        var f = function(questType, updatePound)
        {
            g_setSelectedLink(this, 'questType');

            lv.customPound  = lv.id + (questType != null ? ':type=' + questType : '');
            lv.customFilter = function(quest) { return questType == null || (quest._type & 1 << questType - 1) };

            lv.updateFilters(1);
            lv.applySort();
            lv.refreshRows();
            if(updatePound)
                lv.updatePound(1);
        };

        var lv = this,
            types = [],
            a;

        a = $('<a><span>' + LANG.pr_note_all + '</span></a>');
        a[0].f = f.bind(a[0], null, 1);
        a.click(a[0].f);
        var firstCallback = f.bind(a[0], null, 0);
        firstCallback();

        types.push($('<span class="indicator-mode"></span>').append(a).append($('<b>' + LANG.pr_note_all + '</b>')));

        for(var i = 1; i <= 4; ++i)
        {
            if(!this._questTypes[i])
                continue;

            a = $('<a><span>' + g_quest_indicators[i] + '</span> (' + this._questTypes[i] + ')</a>');
            a[0].f = f.bind(a[0], i, 1);
            a.click(a[0].f);

            types.push($('<span class="indicator-mode"></span>').append(a).append($('<b>' + g_quest_indicators[i] + ' (' + this._questTypes[i] + ')</b>')));

            if(it && it[1] == i)
                (a[0].f)();
        }

        if(types.length > 2)
        {
            for(var i = 0, len = types.length; i < len; ++i)
                this.createIndicator(types[i], null, $('a', types[i])[0].f);

            $(this.noteTop).css('padding-bottom', '12px');
            $(this.noteIndicators).append($('<div class="clear"></div>')).insertAfter($(this.navTop));
        }
    },

// \\ subtabs here \\

    assocArrCmp: function(a, b, arr) {
        if (a == null) {
            return -1;
        }
        else if (b == null) {
            return 1;
        }

        var n = Math.max(a.length, b.length);
        for (var i = 0; i < n; ++i) {
            if (a[i] == null) {
                return -1;
            }
            else if (b[i] == null) {
                return 1;
            }

            var res = $WH.strcmp(arr[a[i]], arr[b[i]]);
            if (res != 0) {
                return res;
            }
        }

        return 0
    },

    assocBinFlags: function(f, arr) {
        var res = [];
        for (var i in arr) {
            if (!isNaN(i) && (f & 1 << i - 1)) {
                res.push(i);
            }
        }
        res.sort(function(a, b) {
            return $WH.strcmp(arr[a], arr[b]);
        });

        return res;
    },

    location: function(row, td) {
        if (row.location == null) {
            return -1;
        }

        for (var i = 0, len = row.location.length; i < len; ++i) {
            if (i > 0) {
                $WH.ae(td, $WH.ct(LANG.comma));
            }

            var zoneId = row.location[i];
            if (zoneId == -1) {
                $WH.ae(td, $WH.ct(LANG.ellipsis));
            }
            else {
                var a = $WH.ce('a');
                a.className = 'q1';
                a.href = '?zone=' + zoneId;
                $WH.ae(a, $WH.ct(g_zones[zoneId]));
                $WH.ae(td, a);
            }
        }
    },

    arrayText: function(arr, lookup) {
        if (arr == null) {
            return;
        }
        else if (!$WH.is_array(arr)) {
            return lookup[arr];
        }
        var buff = '';
        for (var i = 0, len = arr.length; i < len; ++i) {
            if (i > 0) {
                buff += ' ';
            }
            if (!lookup[arr[i]]) {
                continue;
            }
            buff += lookup[arr[i]];
        }
        return buff;
    },

    createCenteredIcons: function(arr, td, text, type) {
        if (arr != null) {
            var
                d = $WH.ce('div'),
                d2 = $WH.ce('div');

            $WH.ae(document.body, d);

            if (text && (arr.length != 1 || type != 2)) {
                var bibi = $WH.ce('div');
                bibi.style.position = 'relative';
                bibi.style.width = '1px';
                var bibi2 = $WH.ce('div');
                bibi2.className = 'q0';
                bibi2.style.position = 'absolute';
                bibi2.style.right = '2px';
                bibi2.style.lineHeight = '26px';
                bibi2.style.fontSize = '11px';
                bibi2.style.whiteSpace = 'nowrap';
                $WH.ae(bibi2, $WH.ct(text));
                $WH.ae(bibi, bibi2);
                $WH.ae(d, bibi);

                d.style.paddingLeft = bibi2.offsetWidth + 'px';
            }

            var iconPool = g_items;
            if (type == 1) {
                iconPool = g_spells;
            }

            for (var i = 0, len = arr.length; i < len; ++i) {
                var icon;
                if (arr[i] == null) {
                    icon = $WH.ce('div');
                    icon.style.width = icon.style.height = '26px';
                }
                else {
                    var
                        id,
                        num;

                        if (typeof arr[i] == 'object') {
                        id = arr[i][0];
                        num = arr[i][1];
                    }
                    else {
                        id = arr[i];
                    }

                    if (id) {
                        icon = iconPool.createIcon(id, 0, num);
                    }
                    else {
                        icon = Icon.create('inventoryslot_empty', 0, null, 'javascript:;');
                    }
                }

                if (arr.length == 1 && type == 2) { // Tiny text display
                    if (id && g_items[id]) {
                        $WH.ee(d);
                        var
                            item = g_items[id],
                            a = $WH.ce('a'),
                            sp = $WH.ce('span');

                            sp.style.paddingTop = '4px';

                        a.href = '?item=' + id;
                        a.className = 'q' + item.quality + ' icontiny tinyspecial';
                        a.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + item.icon.toLowerCase() + '.gif)';
                        a.style.whiteSpace = 'nowrap';

                        $WH.st(a, item['name_' + Locale.getName()]);
                        $WH.ae(sp, a);

                        if (num > 1) {
                            $WH.ae(sp, $WH.ct(' (' + num + ')'));
                        }

                        if (text) {
                            var bibi = $WH.ce('span');
                            bibi.className = 'q0';
                            bibi.style.fontSize = '11px';
                            bibi.style.whiteSpace = 'nowrap';
                            $WH.ae(bibi, $WH.ct(text));
                            $WH.ae(d, bibi);
                            if ($(bibi2).length > 0) {
                                sp.style.paddingLeft = $(bibi2).width() + 'px';
                            }
                        }

                        $WH.ae(d, sp);
                    }
                }
                else {
                    icon.style.cssFloat = icon.style.styleFloat = 'left';
                    $WH.ae(d, icon);

                    d.style.margin = '0 auto';
                    d.style.textAlign = 'left';

                    d.style.width = (26 * arr.length) + 'px';
                }
            }

            d2.className = 'clear';

            $WH.ae(td, d);
            $WH.ae(td, d2);

            return true;
        }
    },

    createSocketedIcons: function(sockets, td, gems, match, text) {
        var
            nMatch = 0,
            d      = $WH.ce('div'),
            d2     = $WH.ce('div');

        for (var i = 0, len = sockets.length; i < len; ++i) {
            var
                icon,
                gemId = gems[i];

                if (g_items && g_items[gemId]) {
                    icon = g_items.createIcon(gemId, 0);
                }
                else if ($WH.isset('g_gems') && g_gems && g_gems[gemId]) {
                    icon = Icon.create(g_gems[gemId].icon, 0, null, '?item=' + gemId);
                }
                else {
                    icon = Icon.create(null, 0, null, 'javascript:;');
                }

            icon.className += ' iconsmall-socket-' + g_file_gems[sockets[i]] + (!gems || !gemId ? '-empty': '');
            icon.style.cssFloat = icon.style.styleFloat = 'left';

            if (match && match[i]) {
                icon.insertBefore($WH.ce('var'), icon.childNodes[1]);
                ++nMatch;
            }

            $WH.ae(d, icon);
        }

        d.style.margin = '0 auto';
        d.style.textAlign = 'left';

        d.style.width = (26 * sockets.length) + 'px';
        d2.className = 'clear';

        $WH.ae(td, d);
        $WH.ae(td, d2);

        if (text && nMatch == sockets.length) {
            d = $WH.ce('div');
            d.style.paddingTop = '4px';
            $WH.ae(d, $WH.ct(text));
            $WH.ae(td, d);
        }
    },

    getItemType: function(itemClass, itemSubclass, itemSubsubclass) {
        if (itemSubsubclass != null && g_item_subsubclasses[itemClass] != null && g_item_subsubclasses[itemClass][itemSubclass] != null) {
            return {
                url: '?items=' + itemClass + '.' + itemSubclass + '.' + itemSubsubclass,
                text: g_item_subsubclasses[itemClass][itemSubclass][itemSubsubclass]
            };
        }
        else if (itemSubclass != null &&g_item_subclasses[itemClass] != null) {
            return {
                url: '?items=' + itemClass + '.' + itemSubclass,
                text: g_item_subclasses[itemClass][itemSubclass]
            };
        }
        else {
            return {
                url: '?items=' + itemClass,
                text: g_item_classes[itemClass]
            };
        }
    },

    getQuestCategory: function(category) {
        return g_quest_sorts[category];
    },

    getQuestReputation: function(faction, quest) {
        if (quest.reprewards) {
            for (var c = 0, a = quest.reprewards.length; c < a; ++c) {
                if (quest.reprewards[c][0] == faction) {
                    return quest.reprewards[c][1];
                }
            }
        }
    },

    getFactionCategory: function(category, category2) {
        if (category) {
            return g_faction_categories[category];
        }
        else {
            return g_faction_categories[category2];
        }
    },

    getEventNextDates: function(startDate, endDate, recurrence, fromWhen) {
        if (typeof startDate != 'string' || typeof endDate != 'string') {
            return [null, null];
        }

        startDate = new Date(startDate.replace(/-/g, '/'));
        endDate   = new Date(endDate.replace(/-/g, '/'));

        if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
            return [null, null];
        }

        if (fromWhen == null) {
            fromWhen = g_serverTime;
        }

        var offset = 0;
        if (recurrence == -1) { // Once by month using day of the week of startDate
            var nextEvent = new Date(fromWhen.getFullYear(), fromWhen.getMonth(), 1, startDate.getHours(), startDate.getMinutes(), startDate.getSeconds()); // counts as next until it ends
            for (var i = 0; i < 2; ++i) {
                nextEvent.setDate(1);
                nextEvent.setMonth(nextEvent.getMonth() + i); // + 0 = try current month, else try next month
                var day = nextEvent.getDay();
                var tolerance = 1;
                if (nextEvent.getYear() == 2009) {
                    tolerance = 0;
                }
                if (day > tolerance) {
                    nextEvent.setDate(nextEvent.getDate() + (7 - day)); // first sunday
                }

                var eventEnd = new Date(nextEvent);
                eventEnd.setDate(eventEnd.getDate() + (7 - tolerance)); // 2010, length of 6. tolerance is 1 if it isnt 2009
                if (fromWhen.getTime() < eventEnd.getTime()) {
                    break; // event hasnt ended yet, so this is still the current one
                }
            }

            offset = nextEvent.getTime() - startDate.getTime();
        }
        else if (recurrence > 0) {
            recurrence *= 1000; // sec -> ms
            offset = Math.ceil((fromWhen.getTime() - endDate.getTime()) / recurrence) * recurrence;
        }

        startDate.setTime(startDate.getTime() + offset);
        endDate.setTime(endDate.getTime() + offset);

        return [startDate, endDate];
    },

    createTextRange: function(min, max) {
        min |= 0;
        max |= 0;
        if (min > 1 || max > 1) {
            if (min != max && max > 0) {
                return min + '-' + max;
            }
            else {
                return min + '';
            }
        }

        return null;
    },

    coGetColor: function(comment, mode, blog)
    {
        if(comment.user && g_customColors[comment.user])
            return ' comment-' + g_customColors[comment.user];

        switch(mode)
        {
            case -1: // Edit forum post
                var foo = null;
                if(!blog)
                    foo = comment.divPost.childNodes[1].className.match(/comment-([a-z]+)/);
                else
                    foo = comment.divBody[0].className.match(/comment-([a-z]+)/);
                if(foo != null)
                    return ' comment-' + foo[1];
                break;

            case 3: // Post reply (forums)
                if (comment.roles & U_GROUP_PREMIUMISH)
                    return ' comment-gold';
            case 4: // Signature (account settings)
                if(comment.roles & U_GROUP_ADMIN)
                    return ' comment-blue';
                if(comment.roles & U_GROUP_GREEN_TEXT) // Mod, Bureau, Dev
                    return ' comment-green';
                if(comment.roles & U_GROUP_VIP) // VIP
                    return ' comment-gold';
                if (comment.roles & U_GROUP_PREMIUMISH) // Premium, Editor
                    return ' comment-gold';
                break;
        }

        if(comment.roles & U_GROUP_ADMIN)
            return ' comment-blue';
        else if((comment.commentv2 && comment.sticky) || (comment.roles & U_GROUP_GREEN_TEXT) || (comment.rating >= 10))
            return ' comment-green';
        else if(comment.rating < -2)
            return ' comment-bt';
        else if(comment.roles & U_GROUP_PREMIUMISH)
            return ' comment-gold';

        return '';
    },

    coFlagUpOfDate: function(comment)
    {
        var notificationDiv = comment.commentCell.find('.comment-notification');
        var god = comment.user == g_user.name || ((g_user.roles & (U_GROUP_ADMIN|U_GROUP_MOD)) != 0);

        if (!god) {
            return;
        }

        if (confirm('Mark comment ' + comment.id + ' as up to date?')) {
            comment.container.removeClass('comment-outofdate');
            $.post('?comment=out-of-date', { id: comment.id, remove: 1 }, function(data) {
                    if (data == 'ok') {
                        comment.outofdate = false;
                        Listview.templates.comment.updateCommentCell(comment);
                        MessageBox(notificationDiv, LANG.lvcomment_uptodateresponse);
                    } else {
                        MessageBox(notificationDiv, 'Error: ' + data);
                    }
                }
            );
        }
        return;
    },

    coFlagOutOfDate: function(comment)
    {
        var notificationDiv = comment.commentCell.find('.comment-notification');
        var god = comment.user == g_user.name || ((g_user.roles & (U_GROUP_ADMIN|U_GROUP_BUREAU)) != 0);

        // SpiffyJr, God users don't require a reason
        if (god) {
            if (confirm('Mark comment ' + comment.id + ' as out of date?')) {
                comment.container.addClass('comment-outofdate');
                $.post('?comment=out-of-date', { id: comment.id }, function(data) {
                        if (data == 'ok') {
                            comment.outofdate = true;
                            Listview.templates.comment.updateCommentCell(comment);
                            MessageBox(notificationDiv, LANG.lvcomment_outofdateresponse);
                        } else {
                            MessageBox(notificationDiv, 'Error: ' + data);
                        }
                    }
                );
            }
            return;
        }

        var reason = null;

        while(true) {
            reason = prompt(LANG.lvcomment_outofdate_tip);

            // null value indicates that the confirm box was cancelled
            if (reason == null || reason == false) {
                return;
            } else if (reason.toString().length > 0) {
                break;
            }

            alert(LANG.youmustprovideareason_stc);
        }

        $.post('?comment=out-of-date', { id: comment.id, reason: reason }, function(data) {
                if (data == 'ok') {
                    comment.outofdate = true;
                    Listview.templates.comment.updateCommentCell(comment);
                    MessageBox(notificationDiv, LANG.lvcomment_outofdateresponsequeued);
                } else {
                    MessageBox(notificationDiv, 'Error: ' + data);
                }
            }
        );
    },

    coDelete: function(comment)
    {
        var god = comment.user == g_user.name || ((g_user.roles & (U_GROUP_ADMIN|U_GROUP_MOD|U_GROUP_BUREAU)) != 0);
        var notificationDiv = comment.commentCell.find('.comment-notification');

        if(god)
        {
            if(!confirm(LANG.confirm_deletecomment))
                return;
            $.post('?comment=delete', { id: comment.id });

            if(!comment.commentv2)
            {
                this.deleteRows([comment]);
                return;
            }

            comment.container.addClass('comment-deleted');
            MessageBox(notificationDiv, LANG.commentdeleted_tip);
            comment.deletedInfo = [g_serverTime, g_user.name];
            Listview.templates.comment.updateCommentCell(comment);
            comment.deleted = true;

            // aowow: custom start
            comment.voteCell.hide();
            comment.repliesCell.hide();
            comment.commentBody.hide();
            comment.repliesControl.hide();
            Listview.templates.comment.updateRepliesControl(comment);
            Listview.templates.comment.updateCommentControls(comment, comment.commentControls);
            if (comment.rating >= -4 && !comment.headerCell.data('events')) {
                comment.headerCell.css('cursor', 'pointer');
                comment.headerCell.bind('click', function(e) {
                    if ($WH.$E(e)._target.nodeName == 'A') {
                        return;
                    }

                    comment.voteCell.toggle();
                    comment.repliesCell.toggle();
                    comment.commentBody.toggle();
                    comment.repliesControl.toggle();
                });
            }
            // aowow: custom end

            return;
        }
    },

    coUndelete: function(comment)
    {
        var god = comment.user == g_user.name || ((g_user.roles & (U_GROUP_ADMIN|U_GROUP_MOD)) != 0);
        var notificationDiv = comment.commentCell.find('.comment-notification');

        if(confirm(LANG.votetoundelete_tip))
        {
            $.post('?comment=undelete', { id: comment.id });

            if(god)
            {
                MessageBox(notificationDiv, 'This comment has been restored.');

                if(comment.commentv2)
                {
                    comment.container.removeClass('comment-deleted');
                    comment.deletedInfo = null;
                    comment.deleted = false;
                    Listview.templates.comment.updateCommentCell(comment);

                    // aowow: custom start
                    comment.voteCell.show();
                    comment.repliesCell.show();
                    comment.commentBody.show();
                    comment.repliesControl.show();
                    Listview.templates.comment.updateRepliesControl(comment);
                    if (comment.rating >= -4) {
                        comment.headerCell.css('cursor', 'auto');
                        comment.headerCell.unbind('click');
                    }
                    // aowow custom end
                }
            }
            else
                MessageBox(notificationDiv, LANG.votedtodelete_tip);
        }
    },

    coEdit: function(comment, mode, blog)
    {
        // aowow: custom
        if (comment.commentCell.find('.comment-edit')[0]) {
            return;
        }

        if(blog) {
            comment.divBody.hide();
            comment.divResponse.hide();
        }

        var divEdit = $('<div/>');
        divEdit.addClass('comment-edit');
        comment.divEdit = divEdit[0];

        if(mode == -1) // edit post (forums)
        {
            if(g_users[comment.user] != null)
                comment.roles = g_users[comment.user].roles;
        }

        var ta = Listview.funcBox.coEditAppend(divEdit, comment, mode, blog);

        var divButtons = $('<div/>');
        divButtons.addClass('comment-edit-buttons');

        var ip = $('<button/>', { text: LANG.compose_save });
        ip.click(Listview.funcBox.coEditButton.bind(ip[0], comment, true, mode, blog));
        divButtons.append(ip);

        divButtons.append($WH.ct(' '));

        ip = $('<button/>', { text: LANG.compose_cancel });
        ip.click(Listview.funcBox.coEditButton.bind(ip[0], comment, false, mode, blog));
        divButtons.append(ip);
        divEdit.append(divButtons);

        divEdit.insertAfter(comment.divBody);

        ta.focus();
    },

    coEditAppend: function(div, comment, mode, blog, noRestrictedMarkup)
    {
        var charLimit = Listview.funcBox.coGetCharLimit(mode);

        // Modes:
        // -1: Edit forum post
        //  0: Edit comment
        //  1: Post your comment
        //  2: Public description (Account settings)
        //  3: Post reply (forums)
        //  4: Signature (Account settings)

        if(mode == 1 || mode == 3 || mode == 4) // new comment/forum post/sig
        {
            comment.user = g_user.name;
            comment.roles = g_user.roles;
            comment.rating = 1;
        }
        else if(mode == 2) // public description
        {
            comment.roles = g_user.roles;
            comment.rating = 1;
        }

        if(noRestrictedMarkup)
            comment.roles &= ~U_GROUP_PENDING;

        if(mode == -1 || mode == 0)
        {
            var divMode = $('<div/>', { text: LANG.compose_mode });
            divMode.addClass('comment-edit-modes');

            var aEdit = $('<a/>', { href: 'javascript:;', text: LANG.compose_edit });
            aEdit.click(Listview.funcBox.coModeLink.bind(aEdit[0], 1, mode, comment));
            aEdit.addClass('selected');
            divMode.append(aEdit);

            divMode.append($WH.ct('|'));

            var aPreview = $('<a/>', { href: 'javascript:;', text: LANG.compose_preview });
            aPreview.click(Listview.funcBox.coModeLink.bind(aPreview[0], 2, mode, comment));
            divMode.append(aPreview);

            div.append(divMode);
        }

        var divPreview = $('<div/>', { css: { display: 'none' } });
        divPreview.addClass('text comment-body' + Listview.funcBox.coGetColor(comment, mode, blog));

        var divBody = $('<div/>');
        divBody.addClass('comment-edit-body');

        var tb = $('<div style="float: left" />');
        tb.addClass('toolbar');

        var tm = $('<div style="float: left" />');
        tm.addClass('menu-buttons');

        var ta = $('<textarea/>', { val: comment.body, rows: 10, css: { clear: 'left' } });
        ta.addClass('comment-editbox');
        switch(mode)
        {
            case 1:
                ta.attr('name', 'commentbody');
                // ta.focus(g_revealCaptcha.bind(null, 'klrbetkjerbt46', false, false, 'Listview commentbody'));
                break;

            case 2:
                ta.attr({ name: 'desc', originalValue: comment.body });
                break;

            case 3:
                ta.attr('name', 'body');
                // ta.focus(g_revealCaptcha.bind(null, 'klrbetkjerbt46', false, false, 'Listview body'));
                break;

            case 4:
                ta.attr({ name: 'sig', originalValue: comment.body, rows: 3 });
                ta.css('height', 'auto');
                break;
        }

        if(mode != -1 && mode != 0)
        {
            var h3 = $('<h3/>'),
                ah3 = $('<a/>'),
                preview = $('<div/>'),
                pad = $('<div/>'),
                mobile = screen.availWidth <= 480;

            var func = Listview.funcBox.coLivePreview.bind(ta[0], comment, mode, preview[0]);

            ah3.addClass('disclosure-' + (mobile ? 'off' : 'on'));

            ah3.text(LANG.compose_livepreview);
            h3.append(ah3);
            ah3.attr('href', 'javascript:;');
            ah3.click(function() { func(1); var on = g_toggleDisplay(preview); ah3.toggleClass('disclosure-on', on); ah3.toggleClass('disclosure-off', !on); });

            h3.addClass('first');
            pad.addClass('pad');

            divPreview.append(h3);
            divPreview.append(preview);
            divPreview.append(pad);

            g_onAfterTyping(ta[0], func, 50);

            ta.focus(function() { func(); divPreview.css('display', (mobile ? 'none' : '')); if(mode != 4) { ta.css('height', '22em'); } });
        }
        else if(mode != 4)
            ta.focus(function() { ta.css('height', '22em'); });

        var buttons = [
            {id: 'b',     title: LANG.markup_b,     pre: '[b]',        post: '[/b]'},
            {id: 'i',     title: LANG.markup_i,     pre: '[i]',        post: '[/i]'},
            {id: 'u',     title: LANG.markup_u,     pre: '[u]',        post: '[/u]'},
            {id: 's',     title: LANG.markup_s,     pre: '[s]',        post: '[/s]'},
            {id: 'small', title: LANG.markup_small, pre: '[small]',    post: '[/small]'},
            {id: 'url',   title: LANG.markup_url,   nopending: true, onclick: function() {var url = prompt(LANG.prompt_linkurl, 'http://');if(url) g_insertTag(ta[0], '[url=' + url + ']', '[/url]')}},
            {id: 'quote', title: LANG.markup_quote, pre: '[quote]',    post: '[/quote]'},
            {id: 'code',  title: LANG.markup_code,  pre: '[code]',     post: '[/code]'},
            {id: 'ul',    title: LANG.markup_ul,    pre: '[ul]\n[li]', post: '[/li]\n[/ul]', rep: function(txt) {return txt.replace(/\n/g, '[/li]\n[li]')}},
            {id: 'ol',    title: LANG.markup_ol,    pre: '[ol]\n[li]', post: '[/li]\n[/ol]', rep: function(txt) {return txt.replace(/\n/g, '[/li]\n[li]')}},
            {id: 'li',    title: LANG.markup_li,    pre: '[li]',       post: '[/li]'}
        ];

        if(!blog)
        {
            for(var i = 0, len = buttons.length; i < len; ++i)
            {
                var button = buttons[i];
                if(mode == 4 && button.id == 'quote')
                    break;
                if((g_user.roles & U_GROUP_PENDING) && button.nopending)
                    continue;

                var but = $('<button/>', { click: function(button, event) { event.preventDefault(); (button.onclick != null ? button.onclick : g_insertTag.bind(0, ta[0], button.pre, button.post, button.rep))(); }.bind(null, button) });
                but[0].setAttribute('type', 'button');
                var img = $('<img/>');
                but.attr('title', button.title);

                img.attr('src', g_staticUrl + '/images/deprecated/pixel.gif');
                img.addClass('toolbar-' + button.id);

                but.append(img);
                tb.append(but);
            }
        }
        else
        {
            for(var i = 0, len = buttons.length; i < len; ++i)
            {
                var button = buttons[i];
                if((g_user.rolls & U_GROUP_PENDING) && button.nopending)
                    continue;

                var buttonClass = 'tb-' + button.id;
                var but = $('<button/>', { click: function(button, event) { event.preventDefault(); (button.onclick != null ? button.onclick : g_insertTag.bind(0, ta[0], button.pre, button.post, button.rep))(); }.bind(null, button), 'class': buttonClass, title: button.title });
                but[0].setAttribute('type', 'button');
                but.append('<ins/>');

                tb.append(but);
            }

            tb.addClass('formatting button sm');
        }

        var promptId = function(name, tag)
        {
            var id = prompt($WH.sprintf(LANG.markup_prompt, name), '');
            if(id != null)
                g_insertTag(ta[0], '[' + tag + '=' + (parseInt(id) || 0) + ']', '');
        };

        var menu = [
            [0, LANG.markup_links,, [
                [ 9, LANG.types[10][0] + '...', promptId.bind(null, LANG.types[10][1], 'achievement')],
                [11, LANG.types[13][0] + '...', promptId.bind(null, LANG.types[13][1], 'class')],
                [ 7, LANG.types[8][0]  + '...', promptId.bind(null, LANG.types[8][1],  'faction')],
                [ 0, LANG.types[3][0]  + '...', promptId.bind(null, LANG.types[3][1],  'item')],
                [ 1, LANG.types[4][0]  + '...', promptId.bind(null, LANG.types[4][1],  'itemset')],
                [ 2, LANG.types[1][0]  + '...', promptId.bind(null, LANG.types[1][1],  'npc')],
                [ 3, LANG.types[2][0]  + '...', promptId.bind(null, LANG.types[2][1],  'object')],
                [ 8, LANG.types[9][0]  + '...', promptId.bind(null, LANG.types[9][1],  'pet')],
                [ 4, LANG.types[5][0]  + '...', promptId.bind(null, LANG.types[5][1],  'quest')],
                [12, LANG.types[14][0] + '...', promptId.bind(null, LANG.types[14][1], 'race')],
                [13, LANG.types[15][0] + '...', promptId.bind(null, LANG.types[15][1], 'skill')],
                [ 5, LANG.types[6][0]  + '...', promptId.bind(null, LANG.types[6][1],  'spell')],
                [ 6, LANG.types[7][0]  + '...', promptId.bind(null, LANG.types[7][1],  'zone')]
            ]]
        ];

        divBody.append(tb);
        divBody.append(tm);
        divBody.append($('<div style="clear: left" />'));
        divBody.append(ta);
        divBody.append($('<br/>'));

        Menu.addButtons(tm[0], menu);

        if(mode == 4)
            divBody.append($WH.ct($WH.sprintf(LANG.compose_limit2, charLimit, 3)));
        else
            divBody.append($WH.ct($WH.sprintf(LANG.compose_limit, charLimit)));

        var span = $('<span class="comment-remaining"> ' + $WH.sprintf(LANG.compose_remaining, charLimit - comment.body.length) + '</span>');
        divBody.append(span);

        ta.keyup(Listview.funcBox.coUpdateCharLimit.bind(0, ta, span, charLimit));
        ta.keydown(Listview.funcBox.coUpdateCharLimit.bind(0, ta, span, charLimit));

        if((mode == -1 || mode == 0) && g_user.roles & U_GROUP_MODERATOR)
        {
            var spaceDiv = $('<div/>', { 'class': 'pad' });
            var responseDiv = $('<div/>', { text: (g_user.roles & U_GROUP_ADMIN ? 'Admin' : 'Moderator') + ' response' });
            var response = $('<textarea/>', { val: comment.response, rows: 3, css: { height: '6em' } });
            divBody.append(spaceDiv);
            divBody.append(responseDiv);
            divBody.append(response);
        }

        div.append(divBody);
        div.append($('<br/>'));
        div.append(divPreview);

        $('<div/>')
            .append('<div class="pad"/>')
            .append(
                $('<h3 class="first"/>')
                    .append(
                        $('<a class="disclosure-off"/>')
                            .text(LANG.compose_formattinghelp)
                            .click(function() { g_disclose(this.parentNode.nextSibling, this) })
                    )
            )
            .append(
                $('<div style="display: none"/>')
                    .append(Markup.toHtml('[markupdoc help=user]'))
            )
            .insertAfter(div.parent());

        return ta;
    },

    coLivePreview: function(comment, mode, div, force)
    {
        if(force != 1 && div.style.display == 'none')
            return;

        var ta        = this,
            charLimit = Listview.funcBox.coGetCharLimit(mode),
            str       = (ta.value.length > charLimit ? ta.value.substring(0, charLimit) : ta.value);

        if(mode == 4)
        {
            var foo;
            if((foo = str.indexOf('\n')) != -1 && (foo = str.indexOf('\n', foo + 1)) != -1 && (foo = str.indexOf('\n', foo + 1)) != -1)
                str = str.substring(0, foo);
        }

        var allowed = Markup.rolesToClass(comment.roles);
        var html = Markup.toHtml(str, {allow: allowed, mode: Markup.MODE_COMMENT, roles: comment.roles});
        if(html)
            div.innerHTML = html;
        else
            div.innerHTML = '<span class="q6">...</span>';
    },

    coEditButton: function(comment, update, mode, blog)
    {
        if(update)
        {
            var tas = $WH.gE(comment.divEdit, 'textarea');
            var ta = tas[0];
            if(!Listview.funcBox.coValidate(ta, mode))
                return;

            if(ta.value != comment.body || (tas[1] && tas[1].value != comment.response))
            {
                var nEdits = 0;
                if(comment.lastEdit != null)
                    nEdits = comment.lastEdit[1];
                ++nEdits;
                comment.lastEdit = [g_serverTime, nEdits, g_user.name];

                if(!comment.commentv2)
                    Listview.funcBox.coUpdateLastEdit(comment);

                var charLimit = Listview.funcBox.coGetCharLimit(mode);

                var allowed = Markup.rolesToClass(comment.roles);
                var bodyHtml = Markup.toHtml((ta.value.length > charLimit ? ta.value.substring(0, charLimit) : ta.value), {allow: allowed, mode: Markup.MODE_COMMENT, roles: comment.roles});
                var respHtml = ((tas[1] && tas[1].value.length > 0) ? Markup.toHtml('[div][/div][wowheadresponse=' + g_user.name + ' roles=' + g_user.roles + ']' + tas[1].value + '[/wowheadresponse]', { allow: Markup.CLASS_STAFF, mode: Markup.MODE_COMMENT, roles: g_user.roles }) : '');

                if(comment.commentv2)
                {
                    comment.body = ta.value;

                    if(g_user.roles & U_GROUP_MODERATOR && tas[1])
                    {
                        comment.response      = tas[1].value;
                        comment.responseuser  = g_user.name;
                        comment.responseroles = g_user.roles;
                    }

                    Listview.templates.comment.updateCommentCell(comment);
                }
                else
                {
                    if(!blog) {
                        comment.divBody.innerHTML = bodyHtml;
                        comment.divResponse.innerHTML = respHtml;
                    }
                    else {
                        comment.divBody.html(bodyHtml);
                        comment.divResponse.html(respHtml);
                    }
                    comment.body = ta.value;

                    if(g_user.roles & U_GROUP_MODERATOR && tas[1])
                        comment.response = tas[1].value;
                }

                var params = 'body=' + $WH.urlencode(comment.body);
                if(comment.response !== undefined)
                    params += '&response=' + $WH.urlencode(comment.response);

                if(mode == -1)
                    new Ajax('?forums=editpost&id=' + comment.id, {method: 'POST', params: params});
                else
                    new Ajax('?comment=edit&id=' + comment.id, {method: 'POST', params: params});
            }
        }

        if(comment.commentv2)
        {
            Listview.templates.comment.updateCommentCell(comment);
        }
        else if(!blog) {
            comment.divBody.style.display = '';
            comment.divResponse.style.display = '';
            comment.divLinks.firstChild.style.display = '';
        }
        else {
            comment.divBody.show();
            comment.divResponse.show();
        }

        if(!comment.commentv2)
        {
            $WH.de(comment.divEdit);
            comment.divEdit = null;
        }
    },

    coGetCharLimit: function(mode)
    {
        if(mode == 2) // Public description (Account settings)
            return 7500;

        if(mode == 4) // Signature (Account settings)
            return 250;

        if(g_user.roles & U_GROUP_STAFF)
            return 16000000;

        var multiplier = 1;

        if(g_user.premium)
            multiplier = 3;

        switch(mode)
        {
            case 0: // Edit comment
            case 1: // Post comment
                return 7500 * multiplier;

            case -1: // Edit forum post
            case 3: // Post forum reply
                return 15000 * multiplier;
        }
    },

    coUpdateCharLimit: function(ta, span, charLimit)
    {
        var text = $(ta).val();
        if(text.length > charLimit)
            $(ta).val(text.substring(0, charLimit));
        else
        {
            $(span).html(' ' + $WH.sprintf(LANG.compose_remaining, charLimit - text.length)).removeClass('q10');
            if(text.length == charLimit)
                $(span).addClass('q10');
        }
    },

    coModeLink: function(mode, m, comment)
    {
        var charLimit = Listview.funcBox.coGetCharLimit(m);
        var markupMode = Markup.MODE_COMMENT;

        $WH.array_walk($WH.gE(this.parentNode, 'a'), function(x) {x.className = ''});
        this.className = 'selected';

        var
            tas = $WH.gE(this.parentNode.parentNode, 'textarea'),
            ta = tas[0],
            taContainer = ta.parentNode,
            preview = $('.comment-body', taContainer.parentNode)[0];

        if(m == 4)
            markupMode = Markup.MODE_SIGNATURE;

        switch(mode)
        {
            case 1:
                taContainer.style.display = '';
                preview.style.display = 'none';
                taContainer.firstChild.focus();
                break;

            case 2:
                taContainer.style.display = 'none';

                var str = (ta.value.length > charLimit ? ta.value.substring(0, charLimit) : ta.value);
                if(m == 4)
                {
                    var foo;
                    if((foo = str.indexOf('\n')) != -1 && (foo = str.indexOf('\n', foo + 1)) != -1 && (foo = str.indexOf('\n', foo + 1)) != -1)
                        str = str.substring(0, foo);
                }

                var allowed = Markup.rolesToClass(comment.roles);
                var html = Markup.toHtml(str, {allow: allowed, mode: markupMode, roles: comment.roles});
                if(tas[1] && tas[1].value.length > 0)
                    html += Markup.toHtml('[div][/div][wowheadresponse=' + g_user.name + ' roles=' + g_user.roles + ']' + tas[1].value + '[/wowheadresponse]', { allow: Markup.CLASS_STAFF, mode: markupMode, roles: g_user.roles });
                preview.innerHTML = html;
                preview.style.display = '';
                break;
        }
    },

    coValidate: function(ta, mode)
    {
        mode |= 0;

        if(mode == 1 || mode == -1)
        {
            if($WH.trim(ta.value).length < 10)
            {
                alert(LANG.message_forumposttooshort);
                return false;
            }
        }
        else
        {
            if($WH.trim(ta.value).length < 10)
            {
                alert(LANG.message_commenttooshort);
                return false;
            }
        }

        var charLimit = Listview.funcBox.coGetCharLimit(mode);

        if(ta.value.length >= charLimit)
        {
            if(!confirm(
                $WH.sprintf(mode == 1 ? LANG.confirm_forumposttoolong : LANG.confirm_commenttoolong, charLimit, ta.value.substring(charLimit - 30, charLimit)))
            )
                return false;
        }

        return true;
    },

    coSortNewestFirst: function(me)
    {
        // $WH.sc('comments_sort', 1000, '1', '/', '.wowhead.com');
        $WH.sc('comments_sort', 1000, '1', '/', location.hostname);

        $(me).parent().find('a.selected').removeClass('selected');
        me.className = 'selected';
        this.mainDiv.className += ' listview-aci';
        this.setSort([-5, 4, 6, -1, -2], true, false);
    },

    coSortOldestFirst: function(me)
    {
        // $WH.sc('comments_sort', 1000, '2', '/', '.wowhead.com');
        $WH.sc('comments_sort', 1000, '2', '/', location.hostname);

        $(me).parent().find('a.selected').removeClass('selected');
        me.className = 'selected';
        this.mainDiv.className += ' listview-aci';
        this.setSort([-5, 4, 6, 1, 2], true, false);
    },

    coSortHighestRatedFirst: function(me)
    {
        // $WH.sc('comments_sort', 1000, '3', '/', '.wowhead.com');
        $WH.sc('comments_sort', 1000, '3', '/', location.hostname);

        $(me).parent().find('a.selected').removeClass('selected');
        me.className = 'selected';
        this.mainDiv.className = this.mainDiv.className.replace('listview-aci', '');
        this.setSort([-5, 4, 6, -3, 2], true, false);
    },

    coFilterByPatchVersion: function(select)
    {
        this.minPatchVersion = select.value;
        this.refreshRows();
    },

    coUpdateLastEdit: function(comment)
    {
        var _ = comment.divLastEdit;
        if(!_) return;

        if(comment.lastEdit != null)
        {
            var __ = comment.lastEdit;
            _.childNodes[1].firstChild.nodeValue = __[2];
            _.childNodes[1].href = '?user=' + __[2];

            var editedOn = new Date(__[0]);
            var elapsed = (g_serverTime - editedOn) / 1000;

            if(_.childNodes[3].firstChild)
                $WH.de(_.childNodes[3].firstChild);

            g_formatDate(_.childNodes[3], elapsed, editedOn);

            var extraTxt = '';

            if(comment.rating != null) // Comments only
                extraTxt += $WH.sprintf(LANG.lvcomment_patch, g_getPatchVersion(editedOn));

            if(__[1] > 1)
                extraTxt += LANG.dash + $WH.sprintf(LANG.lvcomment_nedits, __[1]);

            _.childNodes[4].nodeValue = extraTxt;

            _.style.display = '';
        }
        else
            _.style.display = 'none';
    },

    coFormatFileSize: function(size)
    {
        var i = -1;
        var units = 'KMGTPEZY';

        while(size >= 1024 && i < 7)
        {
            size /= 1024;
            ++i;
        }

        if(i < 0)
            return size + ' byte' + (size > 1? 's': '');
        else
            return size.toFixed(1) + ' ' + units[i] + 'B';
    },

    dateEventOver: function(date, event, e) {
        var dates = Listview.funcBox.getEventNextDates(event.startDate, event.endDate, event.rec || 0, date),
            buff  = '';

        if (dates[0] && dates[1]) {
            var
                t1 = new Date(event.startDate.replace(/-/g, '/')),
                t2 = new Date(event.endDate.replace(/-/g, '/')),
                first,
                last;

            t1.setFullYear(date.getFullYear(), date.getMonth(), date.getDate());
            t2.setFullYear(date.getFullYear(), date.getMonth(), date.getDate());

            if (date.getFullYear() == dates[0].getFullYear() && date.getMonth() == dates[0].getMonth() && date.getDate() == dates[0].getDate()) {
                first = true;
            }

            if (date.getFullYear() == dates[1].getFullYear() && date.getMonth() == dates[1].getMonth() && date.getDate() == dates[1].getDate()) {
                last = true;
            }

            if (first && last)
                buff = g_formatTimeSimple(t1, LANG.lvscreenshot_from, 1) + ' ' + g_formatTimeSimple(t2, LANG.date_to, 1);
            else if (first)
                buff = g_formatTimeSimple(t1, LANG.tab_starts);
            else if (last)
                buff = g_formatTimeSimple(t2, LANG.tab_ends);
            else
                buff = LANG.allday;
        }

        $WH.Tooltip.showAtCursor(e, '<span class="q1">' + event.name + '</span><br />' + buff, 0, 0, 'q');
    },

    ssCellOver: function() {
        this.className = 'screenshot-caption-over';
    },

    ssCellOut: function() {
        this.className = 'screenshot-caption';
    },

    ssCellClick: function(i, e) {
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

        ScreenshotViewer.show({
            screenshots: this.data,
            pos: i
        });
    },

    ssCreateCb: function(td, row) {
        if (row.__nochk) {
            return;
        }

        var div = $WH.ce('div');
        div.className = 'listview-cb';
        div.onclick   = Listview.cbCellClick;

        var cb = $WH.ce('input');
        cb.type = 'checkbox';
        cb.onclick = Listview.cbClick;
        $WH.ns(cb);

        if (row.__chk) {
            cb.checked = true;
        }

        row.__cb = cb;

        $WH.ae(div, cb);
        $WH.ae(td, div);
    },

    viCellClick: function(i, e) {
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

        VideoViewer.show({
            videos: this.data,
            pos: i
        })
    },

    moneyHonorOver: function(e) {
        $WH.Tooltip.showAtCursor(e, '<b>' + LANG.tooltip_honorpoints + '</b>', 0, 0, 'q1');
    },

    moneyArenaOver: function(e) {
        $WH.Tooltip.showAtCursor(e, '<b>' + LANG.tooltip_arenapoints + '</b>', 0, 0, 'q1');
    },

    moneyAchievementOver: function(e) {
        $WH.Tooltip.showAtCursor(e, '<b>' + LANG.tooltip_achievementpoints + '</b>', 0, 0, 'q1');
    },

    moneyCurrencyOver: function(currencyId, count, e) {
        var buff = g_gatheredcurrencies[currencyId]['name_' + Locale.getName()];

        // aowow: justice / valor points handling removed

        $WH.Tooltip.showAtCursor(e, buff, 0, 0, 'q1');
    },

    appendMoney: function(d, money, side, costItems, costCurrency, achievementPoints) {
        var
            _,
            __,
            ns = 0;

        if (side == 1 || side == 'alliance') {
            side = 1;
        }
        else if (side == 2 || side == 'horde') {
            side = 2;
        }
        else {
            side = 3;
        }

        if (money >= 10000) {
            ns = 1;

            _ = $WH.ce('span');
            _.className = 'moneygold';
            $WH.ae(_, $WH.ct($WH.number_format(Math.floor(money / 10000))));
            $WH.ae(d, _);
            money %= 10000;
        }

        if (money >= 100) {
            if (ns) {
                $WH.ae(d, $WH.ct(' '));
            }
            else {
                ns = 1;
            }

            _ = $WH.ce('span');
            _.className = 'moneysilver';
            $WH.ae(_, $WH.ct(Math.floor(money / 100)));
            $WH.ae(d, _);
            money %= 100;
        }

        if (money >= 1) {
            if (ns) {
                $WH.ae(d, $WH.ct(' '));
            }
            else {
                ns = 1;
            }

            _ = $WH.ce('span');
            _.className = 'moneycopper';
            $WH.ae(_, $WH.ct(money));
            $WH.ae(d, _);
        }

        if (costItems != null) {
            for (var i = 0; i < costItems.length; ++i) {
                if (ns) {
                    $WH.ae(d, $WH.ct(' '));
                }
                else {
                    ns = 1;
                }

                var itemId = costItems[i][0];
                var count = costItems[i][1];
                var icon = g_items.getIcon(itemId);

                _ = $WH.ce('a');
                _.href = '?item=' + itemId;
                _.className = 'moneyitem';
                _.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + icon.toLowerCase() + '.gif)';
                $WH.ae(_, $WH.ct(count));
                $WH.ae(d, _);
            }
        }

        if (costCurrency != null) {
            for (var i = 0; i < costCurrency.length; ++i) {
                if (ns) {
                    $WH.ae(d, $WH.ct(' '));
                }
                else {
                    ns = 1;
                }

                var currencyId = costCurrency[i][0];
                var count = costCurrency[i][1];
                var icon = ['inv_misc_questionmark', 'inv_misc_questionmark'];
                if (g_gatheredcurrencies[currencyId]) {
                    icon = g_gatheredcurrencies[currencyId].icon;
                }

//  aowow: replacement
                _ = $WH.ce('a');
                _.href = '?currency=' + currencyId;
                _.onmousemove = $WH.Tooltip.cursorUpdate;
                _.onmouseout = $WH.Tooltip.hide;
                if (currencyId == 103) {                    // arena
                    _.className = 'moneyarena tip';
                    _.onmouseover = Listview.funcBox.moneyArenaOver;
                    $WH.ae(_, $WH.ct($WH.number_format(count)));
                }
                else if (currencyId == 104) {               // honor
                    if (side == 3 && icon[0] == icon[1]) {
                        side = 1;
                    }
                    _.className = 'money' + (side == 1 ? 'alliance' : 'horde') + ' tip';
                    _.onmouseover = Listview.funcBox.moneyHonorOver;

                    if (side == 3) {
                        __ = $WH.ce('span');
                        __.className = 'moneyalliance';
                        $WH.ae(__, $WH.ct($WH.number_format(count)));
                        $WH.ae(_, __);
                    }
                    else {
                        $WH.ae(_, $WH.ct($WH.number_format(count)));
                    }
                }
                else {                                      // tokens
                    _.className = 'icontinyr tip q1';
                    _.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + icon[0].toLowerCase() + '.gif)';
                    _.onmouseover = Listview.funcBox.moneyCurrencyOver.bind(_, currencyId, count);
                    $WH.ae(_, $WH.ct($WH.number_format(count)));
                }
/*  aowow: original
                if (side == 3 && icon[0] == icon[1]) {
                    side = 1;
                }

                _ = $WH.ce('a');
                _.href = '?currency=' + currencyId;
                _.className = 'icontinyr tip q1';
                _.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + icon[(side == 3 ? 1 : side - 1)].toLowerCase() + '.gif)';
                _.onmouseover = Listview.funcBox.moneyCurrencyOver.bind(_, currencyId, count);
                _.onmousemove = $WH.Tooltip.cursorUpdate;
                _.onmouseout = $WH.Tooltip.hide;
                $WH.ae(d, _);

                if (side == 3) {
                    __ = $WH.ce('span');
                    __.className = 'icontinyr';
                    __.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + icon[0].toLowerCase() + '.gif)';
                    $WH.ae(_, __);
                    _ = __;
                }
*/
                $WH.ae(d, _);
            }
        }

        // aowow: changed because legitemately passing zero APs from the profiler is a thing
        // (achievementPoints > 0) {
        if (typeof achievementPoints == 'number') {
            if (ns) {
                $WH.ae(d, $WH.ct(' '));
            }
            else {
                ns = 1;
            }

            _ = $WH.ce('span');
            _.className = 'moneyachievement tip';
            _.onmouseover = Listview.funcBox.moneyAchievementOver;
            _.onmousemove = $WH.Tooltip.cursorUpdate;
            _.onmouseout = $WH.Tooltip.hide;
            $WH.ae(_, $WH.ct($WH.number_format(achievementPoints)));
            $WH.ae(d, _);
        }
    },

    getUpperSource: function(source, sm) {
        switch (source) {
            case 2: // Drop
                if (sm.bd) {
                    return LANG.source_bossdrop;
                }
                if (sm.z) {
                    return LANG.source_zonedrop;
                }
                break;
            case 4: // Quest
                return LANG.source_quests;
            case 5: // Vendor
                return LANG.source_vendors;
        }

        return g_sources[source];
    },

    getLowerSource: function(source, sm, type) {
        switch (source) {
            case 3: // PvP
                if (sm.p && g_sources_pvp[sm.p]) {
                    return { text: g_sources_pvp[sm.p] };
                }
                break;
        }
        switch (type) {
            case 0: // None
            case 1: // NPC
            case 2: // Object
                if (sm.z) {
                    var res = {
                        url: '?zone=' + sm.z,
                        text: g_zones[sm.z]
                    };

                    if (sm.t && source == 5) {
                        res.pretext = LANG.lvitem_vendorin;
                    }

                    if (sm.dd && sm.dd != 99) {
                        if (sm.dd < 0) { // Dungeon
                            res.posttext = $WH.sprintf(LANG.lvitem_dd, "", (sm.dd < -1 ? LANG.lvitem_heroic : LANG.lvitem_normal));
                        }
                        else { // Raid
                            res.posttext = $WH.sprintf(LANG.lvitem_dd, (sm.dd & 1 ? LANG.lvitem_raid10 : LANG.lvitem_raid25), (sm.dd > 2 ? LANG.lvitem_heroic : LANG.lvitem_normal));
                        }
                    }

                    return res;
                }
                break;
            case 5: // Quest
                return {
                    url: '?quests=' + sm.c2 + '.' + sm.c,
                    text: Listview.funcBox.getQuestCategory(sm.c)
                };
                break;
            case 6: // Spell
                if (sm.c && sm.s) {
                    return {
                        url: '?spells=' + sm.c + '.' + sm.s,
                        text: g_spell_skills[sm.s]
                    };
                }
                else {
                    return {
                        url: '?spells=0',
                        text: '??'
                    };
                }
                break;
        }
    },

    getExpansionText: function(line) {
        var str = '';

        if (line.expansion == 1) {
            str += ' bc';
        }
        else if (line.expansion == 2) {
            str += ' wotlk wrath';
        }

        return str;
    }
};

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
                compute: function(faction, td) {
                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(faction);
                    $WH.ae(a, $WH.ct(faction.name));
                    if (faction.expansion) {
                        var sp = $WH.ce('span');
                        sp.className = g_GetExpansionClassName(faction.expansion);
                        $WH.ae(sp, a);
                        $WH.ae(td, sp);
                    }
                    else {
                        $WH.ae(td, a);
                    }
                },
                getVisibleText: function(faction) {
                    var buff = faction.name + Listview.funcBox.getExpansionText(faction);

                    return buff;
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
                id: 'standing',
                name: LANG.reputation,
                value: 'standing',
                compute: function(faction, td) {
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
                compute: function(faction, td) {
                    if (faction.category2 != null) {
                        td.className = 'small q1';
                        var
                            a = $WH.ce('a'),
                            href = '?factions=' + faction.category2;

                            if (faction.category) {
                            href += '.' + faction.category;
                        }
                        a.href = href;
                        $WH.ae(a, $WH.ct(Listview.funcBox.getFactionCategory(faction.category, faction.category2)));
                        $WH.ae(td, a);
                    }
                },
                getVisibleText: function(faction) {
                    return Listview.funcBox.getFactionCategory(faction.category, faction.category2);
                },
                sortFunc: function(a, b, col) {
                    var _ = Listview.funcBox.getFactionCategory;
                    return $WH.strcmp(_(a.category, a.category2), _(b.category, b.category2));
                }
            }
        ],

        getItemLink: function(faction) {
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
                    var wrapper = $('<div>')
                    var title = $('<a>').text(guide.title).attr('href', guide.url + (typeof guide.rev != 'undefined' ? '&rev=' + guide.rev : ''));
                    if(guide.description)
                        title.mouseover(function(event) {$WH.Tooltip.showAtCursor(event, guide.description, 0, 0, 'q');}).mousemove(function(event) {$WH.Tooltip.cursorUpdate(event)}).mouseout(function() {$WH.Tooltip.hide()});
                    if(guide.sticky)
                        title.addClass('guide-sticky');
                    else if(guide.nvotes < 5)
                        title.addClass('guide-new');
                    $(wrapper).append(title);
                    $(td).append(wrapper);

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
                    if(a.status == b.status)
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
                    if(a.category == b.category)
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

                    if(color != '')
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
                    if(guide.rating < 0)
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
                    if(a.sticky && !b.sticky)
                        return -1;
                    else if(!a.sticky && b.sticky)
                        return 1;

                    var isANew = a.rating == -1 && a.author != 'Wowhead';
                    var isBNew = b.rating == -1 && b.author != 'Wowhead';

                    if(isANew && !isBNew)
                        return -1;
                    else if(isBNew && !isANew)
                        return 1;

                    if(a.rating == b.rating)
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

                    if(guide.views >= 50000)
                    {
                        span.css('color', '#FF4040');
                        span.css('font-weight', 'bold');
                    }
                    else if(guide.views >= 25000)
                    {
                        span.css('color', '#FF8000');
                        span.css('font-weight', 'bold');
                    }
                    else if(guide.views >= 10000)
                        span.css('color', '#A335EE');
                    else if(guide.views >= 5000)
                        span.css('color', '#0070DD');
                    else if(guide.views >= 1000)
                        span.css('color', '#1EFF00');

                    $(td).append(span);
                },
                getVisibleText: function(guide)
                {
                    return guide.author;
                },
                sortFunc: function(a, b)
                {
                    if(a.views == b.views)
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
                    if(a.comments == b.comments)
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

                    if(parseInt(guide.patch / 100) * 100 >= currentPatch)
                        span.addClass('q2');

                    $(td).append(span);
                    return;
                },
                sortFunc: function(a, b)
                {
                    if(a.patch == b.patch)
                        return 0;

                    return a.patch < b.patch ? -1 : 1;
                }
            }
        ],

        getItemLink: function(guide) {return guide.url + (typeof guide.rev != 'undefined' ? '&rev=' + guide.rev : '')}
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
            }
        ],

        getItemLink: function(item) {
            return item.name.charAt(0) == '@' ? 'javascript:;' : '?item=' + item.id;
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
                        return - 1;
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
            }
        ],

        getItemLink: function(quest) {
            return '?quest=' + quest.id;
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

    icongallery: {
        sort: [1],
        mode: Listview.MODE_FLEXGRID,
        clickable: false,
        nItemsPerPage: 150,
        cellMinWidth: 85,
        poundable: 1,
        sortOptions: [{
            id: 'name',
            name: LANG.name,
            sortFunc: function(f, c) {
                return $WH.stringCompare(f.name, c.name)
            }
        }],
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
            return $WH.stringCompare(a.name, b.name);
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
                    return $WH.stringCompare(a.username, b.username);
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
            }
    /* AoWoW: custom end */
        ],

        getItemLink: function(spell) {
            return '?spell=' + spell.id;
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
            comment.locale = (this.id == 'english-comments' ? 'www' : '');

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
            if(comment.rating < -4) {
                container.addClass('comment-lowrated');
            }
            if(comment.deleted)
                container.addClass('comment-deleted');
            if(comment.outofdate)
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
            if(hidden)
                $(div).hide();
            else
                $(div).show();
        },

        updateReplies: function(comment)
        {
            this.updateRepliesCell(comment);
            this.updateRepliesControl(comment);
            SetupReplies(comment.container, comment);

            /* Let's see if the user wants to see a specific reply; only if it's the first time loading the page. */
            if(!comment.alreadyLoadedBefore)
            {
                comment.alreadyLoadedBefore = true;
                var matches = location.hash.match('#comments:id=([0-9]+):reply=([0-9]+)');

                if(matches)
                {
                    var commentId = matches[1];
                    var replyId = matches[2];

                    if(comment.id == commentId)
                        this.highlightReply(comment, replyId);
                }
            }
        },

        highlightReply: function(comment, replyId, fetchedMoreReplies)
        {
            var reply = null;

            /* First let's try to find it */
            for(var idx in comment.replies)
            {
                if(comment.replies[idx].id == replyId)
                {
                    reply = comment.replies[idx];
                    break;
                }
            }

            if(!reply)
            {
                if(fetchedMoreReplies)
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
            if(comment.sticky)
                comment.voteCell.find('.comment-sticky').show();
            else
                comment.voteCell.find('.comment-sticky').hide();
        },

        updateRepliesCell: function(comment)
        {
            var container = comment.repliesCell;
            var table = $('<table></table>');

            container.html('');

            if(!comment.replies.length)
            {
                container.append(table).hide();
                return;
            }

            for(var i = 0; i < comment.replies.length; ++i)
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
                row.find('.reply-text').addClass(g_GetStaffColorFromRoles(reply.roles));

                var replyWhen = $('<a></a>');
                replyWhen.text(g_formatDate(null, elapsed, creationDate));
                replyWhen.attr('href', '#comments:id=' + comment.id + ':reply=' + reply.id);
                replyWhen.attr('id', 'comments:id=' + comment.id + ':reply=' + reply.id);
                replyWhen.addClass('when');

                replyByUserLink.attr('href', '?user=' + reply.username);
                replyByUserLink.text(reply.username);
                replyBy.append(replyByUserLink);

                if (owner)
                {
                    $WH.ae(replyBy[0], $WH.ct(' '));
                    $WH.ae(replyBy[0], $WH.ce('span', { className: 'comment-reply-author-label' }, $WH.ct('<' + LANG.guideAuthor + '>')));
                }

                replyBy.append(' ').append(replyWhen).append(' ').append($WH.sprintf(LANG.lvcomment_patch, g_getPatchVersion(creationDate)));


                var replyHtml = Markup.toHtml(reply.body, {allow: Markup.CLASS_USER, mode: Markup.MODE_REPLY, roles: 0, locale: comment.locale});

                replyHtml = replyHtml.replace(/[^\s<>]{81,}/, function(text) {
                    if(text.substring(0, 4) == 'href' || text.substring(0, 3) == 'src')
                        return text;

                    var ret = '';
                    for(j = 0; j < text.length; j += 80)
                        ret += (text.substring(j, 80 + j) + " ");

                    return ret;
                });

                /* Flag */
                if(g_user.canUpvote && g_user.name != reply.username && !reply.reportedByUser)
                    replyHtml += ' <span class="reply-report" title="' + LANG.reportthisreply_stc + '">report</span>';

                if (canEdit)
                    replyHtml += ' <span class="reply-edit" title="' + LANG.editthisreply_stc + '">edit</span>';

                if (g_user.roles & U_GROUP_COMMENTS_MODERATOR)
                    replyHtml += ' <span class="reply-detach" title="' + LANG.lvcomment_detach + '">detach</span>';

                if(canDelete)
                    replyHtml += ' <span class="reply-delete" title="' + LANG.deletethisreply_stc + '">delete</span>';

                row.find('.reply-text').html(replyBy).append(replyHtml);

                row.find('.reply-rating').text(reply.rating);

                /* Upvote */
                if(reply.votedByUser)
                    upvoteControl.attr('data-hasvoted', 'true');
                if(g_user.canUpvote && g_user.name != reply.username && !reply.votedByUser && !reply.downvotedByUser)
                    upvoteControl.attr('data-canvote', 'true');

                /* Downvote */
                if(reply.downvotedByUser)
                    downvoteControl.attr('data-hasvoted', 'true');
                if(g_user.canDownvote && g_user.name != reply.username && !reply.votedByUser && !reply.downvotedByUser)
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

            if((comment.deleted || comment.outofdate || comment.rating < -4) || (!g_user.canPostReplies && !extraReplies))
                return;

            if(g_user.canPostReplies)
            {
                if(!extraReplies)
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
            let owner = g_pageInfo.author === comment.user;
            var postedOn = new Date(comment.date);
            var elapsed = (g_serverTime - postedOn) / 1000;

            container.html('');

            container.append(LANG.lvcomment_by);
            container.append($WH.sprintf('<a href="?user=$1">$2</a>', comment.user, comment.user));
            if (owner)
            {
                $WH.ae(container[0], $WH.ct(' '));
                $WH.ae(container[0], $WH.ce('span', { className: 'comment-reply-author-label' }, $WH.ct('<' + LANG.guideAuthor + '>')));
            }
            container.append(g_getReputationPlusAchievementText(user.gold, user.silver, user.copper, user.reputation));
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
            if(canEdit) {
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


            if(isAdmin && !comment.sticky && !comment.outofdate && !comment.deleted)
                controls.push({name: 'Sticky', func: this.sticky.bind(this, comment)});
            if(isAdmin && comment.sticky && !comment.outofdate && !comment.deleted)
                controls.push({name: 'Unsticky', func: this.sticky.bind(this, comment)});

            if (!comment.outofdate && !comment.deleted) {
                controls.push({extraClass: 'icon-report', tooltip: LANG.report_tooltip, name: LANG.lvcomment_report, func: ContactTool.show.bind(ContactTool, {mode: 1, comment: comment})});
            }

            /** WRITE THE CONTROLS **/
            container.html('');

            for(var idx in controls)
            {
                var data = controls[idx];
                var control = $('<a></a>');
                var insideContainer = $('<span></span>');

                if(idx != 0)
                    insideContainer.append(' | ');

                control.click(data.func);
                control.text(data.name);

                if(data.extraClass)
                    control.addClass(data.extraClass);

                if(data.tooltip)
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
            if(comment.response)
            {
                var adminResponse = $('<div></div>');
                adminResponse.addClass('text comment-body');
                adminResponse.html(Markup.toHtml('[div][/div][wowheadresponse=' + comment.responseuser + ' roles=' + comment.responseroles + ']' + comment.response + '[/wowheadresponse]', { allow: Markup.CLASS_STAFF, roles: comment.responseroles, uid: 'resp-' + comment.id }));
                commentBody.append(adminResponse);
            }

            /** LAST EDIT **/
            if(comment.lastEdit)
                this.addEditedByOrDeletedByText(commentBody, LANG.lvcomment_lastedit, comment.lastEdit[2], comment.lastEdit[0], comment.lastEdit[1]);

            /** DELETION **/
            if(comment.deletedInfo)
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
            if(comment.userRating > 0)
                upvoteControl.attr('data-hasvoted', 'true').attr('title', LANG.upvoted_tip);
            else
                upvoteControl.attr('data-hasvoted', 'false').attr('title', LANG.upvote_tip);

            if(comment.userRating < 0)
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
            if(!rating)
                return;

            if(!g_user.id)
            {
                this.voteError(comment, LANG.votelogin_tip);
                return;
            }

            if(comment.deleted)
            {
                this.voteError(comment, LANG.votedeleted_tip);
                return;
            }

            if(g_user.name == comment.user)
            {
                this.voteError(comment, LANG.voteself_tip);
                return;
            }

            if(!g_user.canUpvote && rating > 0)
            {
                this.voteError(comment, $WH.sprintf(LANG.upvotenorep_tip, g_user.upvoteRep));
                return;
            }

            if(!g_user.canDownvote && rating < 0)
            {
                this.voteError(comment, $WH.sprintf(LANG.downvotenorep_tip, g_user.downvoteRep));
                return;
            }

            /**********************/
            /*** VARIABLE SETUP ***/
            /**********************/
            if(rating > 0)
                rating = g_user.superCommentVotes ? 2 : 1;
            else if(rating < 0)
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
            if((comment.userRating > 0 && rating > 0) || (comment.userRating < 0 && rating < 0))
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
                if(response.error)
                {
                    /* We have to undo the change we have done above */
                    comment.rating = backupCommentRating;
                    comment.userRating = backupUserRating;
                    template.updateVoteCell(comment);
                }

                if(response.message)
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
                if(data.success)
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

            if(comment.sticky)
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

                    if(LANG.types[reply.type])
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
                    if(reply.rating >= 10)
                        d.className += ' comment-green';

                    $WH.ae(d, $WH.ct(   Markup.removeTags(reply.preview, {mode: Markup.MODE_ARTICLE})  ));
                    $WH.ae(td, d);

                    if(reply.rating)
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
        /* aowow: g_getCommentDomain returned a whole domain to ptr., old., ect.
            if(reply.url)
                return g_getCommentDomain(reply.domain) + '/' + reply.url;
            if(!g_types[reply.type])
                return g_getCommentDomain(reply.domain) + '/go-to-reply&id=' + reply.id;

            return $WH.sprintf('$1?$2=$3#comments:id=$4:reply=$5', g_getCommentDomain(reply.domain), g_types[reply.type], reply.typeId, reply.commentid, reply.id);
        */
            if(reply.url)
                return '?' + reply.url;

            if(!g_types[reply.type])
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
            }
        ],

        getItemLink: function(achievement) {
            return '?achievement=' + achievement.id;
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
            }
        ],

        getItemLink: function(title) {
            return '?title=' + title.id;
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
                     // aowow . i dont know .. i dont know... char icon requests are strange
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
                    a.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + specData.icon.toLowerCase() + '.gif)';
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

        // Hide ads that intersect with the menu
        // var menuRect = $WH.g_createRect(pos.x, pos.y, $div.width(), $div.height());
        // Ads.intersect(menuRect, true);

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
    };

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

    // aowow - why wasn't this sorted already..?
    Menu.sortSubmenus(mn_quests, [[0], [1], [2], [3], [4], [5], [6], [7], [8], [9], [10]]);
});

function MessageBox(parent, text) {
    parent.find(".message-box").remove();
    var box = $("<div></div>");
    box.hide();
    box.addClass("message-box");
    box.html('<p class="message">' + text + '</p><p class="close">(Click on this box to close it)</p>');

    setTimeout(function() {                                 // aowow - custom: popups that never vanish are just insane
        box.fadeOut();
    }, 5000);

    box.click(function (e) {
        $WH.sp(e);                                          // aowow - custom: without this, the comment-header would also register the click
        $(this).fadeOut();
    });

    parent.append(box[0]);
    box.fadeIn();
}

var g_dev = false;
var g_localTime = new Date();
var g_user = {
    id: 0,
    name: "",
    roles: 0
};

/*
Global Profiler-related functions
*/

function g_cleanCharacterName(name) {
    return (name.match && name.match(/^[A-Z]/) ? name.charAt(0).toLowerCase() + name.substr(1) : name);
}

function g_getProfileUrl(profile) {
    if (profile.region) { // Armory character
        // return '?profile=' + profile.region + '.' + profile.realm + '.' + g_cleanCharacterName(profile.name);  // aowow custom
        return '?profile=' + profile.region + '.' + profile.realm + '.' + g_cleanCharacterName(profile.name) + (profile.renameItr ? '-' + profile.renameItr : '');
    }
    else { // Custom profile
        return '?profile=' + profile.id;
    }
}

function g_getProfileRealmUrl(profile) {
    return '?profiles=' + profile.region + '.' + profile.realm;
}

var ProgressBar = function(opt)
{
    this.opts = {
        text: '',
        hoverText: '',
        color: 'rep6',
        width: 0,
        progress: 0
    };

    this.elements = {
        text: null,
        hoverText: null,
        textContainer: null,
        progress: null,
        container: null
    };

    $WH.cO(this.opts, opt);

    this.build();
};

ProgressBar.prototype.build = function()
{
    var el = $('<a/>', { 'class': 'progressbar', href: 'javascript:;' });
    if(this.opts.width > 0)
        el.css('width', this.opts.width + 'px');
    else
        el.css('width', 'auto');

    var textDiv = $('<div/>', { 'class': 'progressbar-text' });
    if(this.opts.text)
    {
        this.elements.text = $('<del/>', { text: this.opts.text });
        textDiv.append(this.elements.text);
    }
    if(this.opts.hoverText)
    {
        this.elements.hoverText = $('<ins/>', { text: this.opts.hoverText });
        textDiv.append(this.elements.hoverText);
    }
    el.append(textDiv);

    var div = $('<div/>', { 'class': 'progressbar-' + this.opts.color, css: { width: this.opts.progress + '%' }, text: String.fromCharCode(160) });
    el.append(div);

    if(this.opts.text)
        textDiv.append($('<div/>', { 'class': 'progressbar-text progressbar-hidden', text: this.opts.text }));

    this.elements.container = el;
    this.elements.progress = div;
    this.elements.textContainer = textDiv;

    return el;
};

ProgressBar.prototype.setText = function(text)
{
    this.opts.text = text;

    if(this.elements.text)
        this.elements.text.text(this.opts.text);
    else
    {
        this.elements.text = $('<del/>', { text: this.opts.text });
        if(this.opts.hoverText)
            this.opts.hoverText.before(this.elements.text);
        else
            this.elements.textContainer.append(this.elements.text);
    }
};

ProgressBar.prototype.setHoverText = function(text)
{
    this.opts.hoverText = text;

    if(this.elements.hoverText)
        this.elements.hoverText.text(this.opts.hoverText);
    else
    {
        this.elements.hoverText = $('<ins/>', { text: this.opts.hoverText });
        this.elements.textContainer.append(this.elements.hoverText);
    }
};

ProgressBar.prototype.setProgress = function(percent)
{
    this.opts.progress = percent;

    this.elements.progress.css('width', this.opts.progress + '%');
};

ProgressBar.prototype.setWidth = function(width)
{
    this.opts.width = width;

    if(this.opts.width > 0)
        this.elements.container.css('width', this.opts.width + 'px');
    else
        this.elements.container.css('width', 'auto');
};

ProgressBar.prototype.getText = function()
{
    return this.opts.text;
};

ProgressBar.prototype.getHoverText = function()
{
    return this.opts.hoverText;
};

ProgressBar.prototype.getWidth = function()
{
    return this.opts.width;
};

ProgressBar.prototype.getContainer = function()
{
    return this.elements.container;
};


/* This function is to get the stars for the vote control for the guides. */

function GetStars(stars, ratable, userRating, guideId)
{
    var STARS_MAX = 5;
    var averageRating = stars;

    if(userRating)
        stars = userRating;

    stars = Math.round(stars*2)/2;
    var starsRounded = Math.round(stars);
    var ret = $("<span>").addClass('stars').addClass('max-' + STARS_MAX).addClass('stars-' + starsRounded);

    if(!g_user.id)
        ratable = false;

    if(ratable)
        ret.addClass('ratable');

    if(userRating)
        ret.addClass('rated');

    /* This is kinda lame but oh well */
    var contents = '<span>';

    // var wbr = $.browser.msie ? '' : '&#8203;';           // aowow - jQuery v1.9 deprecated
    var wbr = $WH.Browser.ie ? '' : '&#8203;';
    var tmp = stars;
    for(var i = 1; i <= STARS_MAX; ++i)
    {
        if(tmp < 1 && tmp > 0)
            contents += '<b class="half">';
        else
            contents += '<b>';
        --tmp;

        contents += '<i class="clickable">' + wbr + '<i></i></i>';
    }

    for(var i = 1; i <= STARS_MAX; ++i)
        contents += '</b>';

    contents += '</span>';

    ret.append(contents);

    if(ratable)
    {
        var starNumber = 0;
        ret.find('i.clickable').each(function() { var starId = ++starNumber; $(this).click(function() { VoteGuide(guideId, averageRating, starId); }); })
    }

    if(userRating)
    {
        var clear = $("<span>").addClass('clear').click(function() { VoteGuide(guideId, averageRating, 0); });
        ret.append(clear);
    }

    if(stars >= 0)
        ret.mouseover(function(event) {$WH.Tooltip.showAtCursor(event, 'Rating:&nbsp;' + stars + '&nbsp;/&nbsp;' + STARS_MAX, 0, 0, 'q');}).mousemove(function(event) {$WH.Tooltip.cursorUpdate(event)}).mouseout(function() {$WH.Tooltip.hide()});

    return ret;
}

function VoteGuide(guideId, oldRating, newRating)
{
    // Update stars display
    $('#guiderating').html(GetStars(oldRating, true, newRating, guideId));

    // Vote
    $.ajax({cache: false, url: '?guide=vote', type: 'POST',
        error: function() {
            $('#guiderating').html(GetStars(oldRating, true, 0, guideId));
            alert('Voting failed. Try again later.');
        },
        success: function(json) {
            var data = eval('(' + json + ')');
            $('#guiderating-value').text(data.rating);
            $('#guiderating-votes').text(GetN5(data.nvotes));
        },
        data: { id: guideId, rating: newRating }
    });
}


var Icon = {
    sizes: ['small', 'medium', 'large'],
    sizes2: [18, 36, 56],
    sizeIds: {
        small:  0,
        medium: 1,
        large:  2
    },
    premiumOffsets: [[-56, -36], [-56, 0], [0, 0]],
    premiumBorderClasses: ['-premium', '-gold', '', '-premiumred', '-red'],
    STANDARD_BORDER: 2,
    privilegeBorderClasses: {
        uncommon: '-q2',
        rare: '-q3',
        epic: '-q4',
        legendary: '-q5'
    },
    create: function(name, size, UNUSED, url, num, qty, noBorder, rel, span) {
        var
            icon  = $WH.ce(span ? 'span' : 'div'),
            image = $WH.ce('ins'),
            tile  = $WH.ce('del');

        if (size == null) {
            size = 1;
        }

        icon.className = 'icon' + Icon.sizes[size];

        $WH.ae(icon, image);

        if (!noBorder) {
            $WH.ae(icon, tile);
        }

        Icon.setTexture(icon, size, name);

        if (url) {
            var a = $WH.ce('a');
            a.href = url;
            if (url.indexOf('wowhead.com') == -1 && url.substr(0, 5) == 'http:') {
                a.target = "_blank";
            }
            $WH.ae(icon, a);
        }
        else if (name) {
            var _ = icon.firstChild.style;
            var avatarIcon = (_.backgroundImage.indexOf('/avatars/') != -1);

            if (!avatarIcon) {
                icon.onclick = Icon.onClick;

                if (url !== false) {
                    var a = $WH.ce('a');
                    a.href = "javascript:;";
                    $WH.ae(icon, a);
                }
            }
        }

        if (rel && typeof a != 'undefined') {
            a.rel = rel
        }
        Icon.setNumQty(icon, num, qty);

        return icon;
    },

    createUser: function(avatar, avatarMore, size, url, premiumLevel, noBorder, reputationLevel) {
        if (avatar == 2) {
            avatarMore = g_staticUrl + '/uploads/avatars/' + avatarMore + '.jpg';
        }

        var icon = Icon.create(avatarMore, size, null, url, null, null, noBorder);

        if ((premiumLevel != Icon.STANDARD_BORDER) && Icon.premiumBorderClasses[premiumLevel]) {
            icon.className += ' ' + icon.className + Icon.premiumBorderClasses[premiumLevel];
        }
        else if (reputationLevel && Icon.privilegeBorderClasses.hasOwnProperty(reputationLevel)) {
            icon.className += ' ' + icon.className + Icon.privilegeBorderClasses[reputationLevel];
        }

        if (avatar == 2) {
            Icon.moveTexture(icon, size, Icon.premiumOffsets[size][0], Icon.premiumOffsets[size][1], true);
        }

        return icon;
    },

    getPrivilegeBorder: function(reputation) {
        var buff = false;
        if (reputation >= 5000) {
            buff = 'uncommon';
        }
        if (reputation >= 10000) {
            buff = 'rare';
        }
        if (reputation >= 15000) {
            buff = 'epic';
        }
        if (reputation >= 25000) {
            buff = 'legendary';
        }

        return buff;
    },

    setTexture: function(icon, size, name) {
        if (!name) {
            return;
        }

        var _ = icon.firstChild.style;

        if (name.indexOf('/') != -1 || name.indexOf('?') != -1) {
            _.backgroundImage = 'url(' + name + ')';
        }
        else {
            _.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/' + Icon.sizes[size] + '/' + escape(name.toLowerCase()) + '.jpg)';
        }

        Icon.moveTexture(icon, size, 0, 0);
    },

    moveTexture: function(icon, size, x, y, exact) {
        var _ = icon.firstChild.style;

        if (x || y) {
            if (exact) {
                _.backgroundPosition = x + 'px ' + y + 'px';
            }
            else {
                _.backgroundPosition = (-x * Icon.sizes2[size]) + 'px ' + ( -y * Icon.sizes2[size]) + 'px';
            }
        }
        else if (_.backgroundPosition) {
            _.backgroundPosition = '';
        }
    },

    setNumQty: function(icon, num, qty) {
        var _ = $WH.gE(icon, 'span');

        for (var i = 0, len = _.length; i < len; ++i) {
            if (_[i]) {
                $WH.de(_[i]);
            }
        }
        if (num != null && ((num > 1 && num < 2147483647) ||  (num.length && num != '0' && num != '1'))) {
            _ = g_createGlow(num, 'q1');
            _.style.right = '0';
            _.style.bottom = '0';
            _.style.position = 'absolute';
            $WH.ae(icon, _);
        }

        if (qty != null && qty > 0) {
            _ = g_createGlow('(' + qty + ')', 'q');
            _.style.left = '0';
            _.style.top = '0';
            _.style.position = 'absolute';
            $WH.ae(icon, _);
        }
    },

    getLink: function(icon) {
        return $WH.gE(icon, 'a')[0];
    },

    showIconName: function(x) {
        if (x.firstChild) {
            var _ = x.firstChild.style;
            if (_.backgroundImage.length && (_.backgroundImage.indexOf(g_staticUrl) >= 4 || g_staticUrl == '')) {
                var
                    start = _.backgroundImage.lastIndexOf('/'),
                    end   = _.backgroundImage.indexOf('.jpg');

                if (start != -1 && end != -1) {
                    Icon.displayIcon(_.backgroundImage.substring(start + 1, end));
                }
            }
        }
    },

    onClick: function() {
        Icon.showIconName(this);
    },

    displayIcon: function(icon) {
        if (!Dialog.templates.icondisplay) {
            var w = 364;
            switch (Locale.getId()) {
                case LOCALE_ESES:
                    w = 380;
                    break;
                case LOCALE_RURU:
                    w = 384;
                    break;
            }

            Dialog.templates.icondisplay = {
                title: LANG.icon,
                width: w,
                buttons: [['arrow', LANG.original], ['cancel', LANG.close]],
                fields:
                [
                    {
                        id: 'icon',
                        label: LANG.dialog_imagename,
                        required: 1,
                        type: 'text',
                        labelAlign: 'left',
                        compute: function(field, value, form, td) {
                            var wrapper = $WH.ce('div');
                            td.style.width = '300px';
                            wrapper.style.position = 'relative';
                            wrapper.style.cssFloat = 'left';
                            wrapper.style.paddingRight = '6px';
                            field.style.width = '200px';

                            var divIcon = this.iconDiv = $WH.ce('div');
                            divIcon.style.position = 'absolute';
                            divIcon.style.top = '-12px';
                            divIcon.style.right = '-70px';

                            divIcon.update = function() {
                                setTimeout(function() {
                                    field.focus();
                                    field.select();
                                }, 10);
                                $WH.ee(divIcon);
                                $WH.ae(divIcon, Icon.create(field.value, 2));
                            };

                            $WH.ae(divIcon, Icon.create(value, 2));
                            $WH.ae(wrapper, divIcon);
                            $WH.ae(wrapper, field);
                            $WH.ae(td, wrapper);
                        }
                    },
                    {
                        id: 'location',
                        label: " ",
                        required: 1,
                        type: 'caption',
                        compute: function(field, value, form, th, tr) {
                            $WH.ee(th);
                            th.style.padding = '3px 3px 0 3px';
                            th.style.lineHeight = '17px';
                            th.style.whiteSpace = 'normal';
                            var wrapper = $WH.ce('div');
                            wrapper.style.position = 'relative';
                            wrapper.style.width = '250px';

                            var span = $WH.ce('span');

                            var text = LANG.dialog_seeallusingicon;
                            text = text.replace('$1', '<a href="?items&filter=cr=142;crs=0;crv=' + this.data.icon + '">' + LANG.types[3][3] + '</a>');
                            text = text.replace('$2', '<a href="?spells&filter=cr=15;crs=0;crv=' + this.data.icon + '">' + LANG.types[6][3] + '</a>');
                            text = text.replace('$3', '<a href="?achievements&filter=cr=10;crs=0;crv=' + this.data.icon + '">' + LANG.types[10][3] + '</a>');

                            span.innerHTML = text;
                            $WH.ae(wrapper, span);
                            $WH.ae(th, wrapper);
                        }
                    }
                ],

                onInit: function(form) {
                    this.updateIcon = this.template.updateIcon.bind(this, form);
                },

                onShow: function(form) {
                    this.updateIcon();
                    if (location.hash && location.hash.indexOf('#icon') == -1) {
                        this.oldHash = location.hash;
                    }
                    else {
                        this.oldHash = '';
                    }

                    var hash = '#icon';

                    // Add icon name on all pages but item, spell and achievement pages (where the name is already available).
                    var nameDisabled = ($WH.isset('g_pageInfo') && g_pageInfo.type && $WH.in_array([3, 6, 10], g_pageInfo.type) == -1);
                    if (!nameDisabled) {
                        hash += ':' + this.data.icon;
                    }

                    location.hash = hash;
                },

                onHide: function(form) {
                    if (this.oldHash) {
                        location.hash = this.oldHash;
                    }
                    else {
                        location.hash = '#.';
                    }
                },

                updateIcon: function(form) {
                    this.iconDiv.update();
                },

                onSubmit: function(unused, data, button, form) {
                    if (button == 'arrow') {
                        var win = window.open(g_staticUrl + '/images/wow/icons/large/' + data.icon.toLowerCase() + '.jpg', '_blank');
                        win.focus();
                        return false;
                    }

                    return true;
                }
            };
        }

        if (!Icon.icDialog) {
            Icon.icDialog = new Dialog();
        }

        Icon.icDialog.show('icondisplay', {data: {icon: icon}});
    },

    checkPound: function() {
        if (location.hash && location.hash.indexOf('#icon') == 0) {
            var parts = location.hash.split(':');
            var icon = false;
            if (parts.length == 2) {
                icon = parts[1];
            }
            else if (parts.length == 1 && $WH.isset('g_pageInfo')) {
                switch (g_pageInfo.type) {
                    case 3: // Item
                        icon = g_items[g_pageInfo.typeId].icon.toLowerCase();
                        break;
                    case 6: // Spell
                        icon = g_spells[g_pageInfo.typeId].icon.toLowerCase();
                        break;
                    case 10: // Achievement
                        icon = g_achievements[g_pageInfo.typeId].icon.toLowerCase();
                        break;
                }
            }

            if (icon) {
                Icon.displayIcon(icon);
            }
        }
    }
};
DomContentLoaded.addEvent(Icon.checkPound);

function Rectangle(left, top, width, height) {
    this.l = left;
    this.t = top;
    this.r = left + width;
    this.b = top + height;
}

function g_getViewport()
{
    var win = $(window);

    return new Rectangle(win.scrollLeft(), win.scrollTop(), win.width(), win.height());
}

Rectangle.prototype = {

    intersectWith: function(rect) {
        var result = !(
            this.l >= rect.r || rect.l >= this.r ||
            this.t >= rect.b || rect.t >= this.b
        );

        return result;
    },

    contains: function(rect) {
        var result = (
            this.l <= rect.l && this.t <= rect.t &&
            this.r >= rect.r &&    this.b >= rect.b
        );

        return result;
    },

    containedIn: function(rect) {
        return rect.contains(this);
    }

};

var RedButton = {
    create: function(text, enabled, func) {
        var
            a    = $WH.ce('a'),
            em   = $WH.ce('em'),
            b    = $WH.ce('b'),
            i    = $WH.ce('i'),
            span = $WH.ce('span');

        a.href = 'javascript:;';
        a.className = 'button-red';

        $WH.ae(b, i);
        $WH.ae(em, b);
        $WH.ae(em, span);
        $WH.ae(a, em);

        RedButton.setText(a, text);
        RedButton.enable(a, enabled);
        RedButton.setFunc(a, func);

        return a;
    },

    setText: function(button, text) {
        $WH.st(button.firstChild.childNodes[0].firstChild, text); // em, b, i
        $WH.st(button.firstChild.childNodes[1], text); // em, span
    },

    enable: function(button, enabled) {
        if (enabled || enabled == null) {
            button.className = button.className.replace('button-red-disabled', '');
        }
        else if (button.className.indexOf('button-red-disabled') == -1) {
            button.className += ' button-red-disabled';
        }
    },

    setFunc: function(button, func) {
        button.onclick = (func ? func : null);
    }
};

var LiveSearch = new function() {
    var
        currentTextbox,
        lastSearch = {},
        lastDiv,
        timer,
        prepared,
        container,
        cancelNext,
        hasData,
        summary,
        selection,
        LIVESEARCH_DELAY = 500;

    function setText(textbox, txt) {
        textbox.value = txt;
        textbox.selectionStart = textbox.selectionEnd = txt.length;
    }

    function colorDiv(div, fromOver) {
        if (lastDiv) {
            lastDiv.className = lastDiv.className.replace('live-search-selected', '');
        }

        lastDiv = div;
        lastDiv.className += ' live-search-selected';
        selection = div.i;

        if (!fromOver) {
            show();

            setTimeout(setText.bind(0, currentTextbox, g_getTextContent(div.firstChild.firstChild.childNodes[1])), 1);
            cancelNext = 1;
        }
    }

    function aOver() {
        colorDiv(this.parentNode.parentNode, 1);
    }

    function isVisible() {
        if (!container) {
            return false;
        }

        return container.style.display != 'none';
    }

    function adjust(fromResize) {
        if (fromResize == 1 && !isVisible()) {
            return;
        }

        if (currentTextbox == null) {
            return;
        }

        var c = $WH.ac(currentTextbox);
        container.style.left  = (c[0] - 2) + "px";
        container.style.top   = (c[1] + currentTextbox.offsetHeight + 1) + "px";
        container.style.width = currentTextbox.offsetWidth + "px";
    }

    function prepare() {
        if (prepared) {
            return;
        }

        prepared = 1;

        container = $WH.ce('div');
        container.className = 'live-search';
        container.style.display = 'none';

        $WH.ae($WH.ge('layers'), container);

        $WH.aE(window, 'resize', adjust.bind(0, 1));
        $WH.aE(document, 'click', hide);
    }

    function show() {
        if (container && !isVisible()) {
            adjust();

            // Same animation as in Menu.js
            $(container).css({ opacity: '0' })
            .show()
            .animate({ opacity: '1' }, 'fast', null, doneShowing);
        }
    }

    function doneShowing(a) {
        $(this).css('opacity', ''); // Remove opacity once animation is over to prevent a bug in IE8
    }

    function hide(e) {
        if (e && !g_isLeftClick(e)) {
            return;
        }

        if (container) {
            container.style.display = 'none';
        }
    }

    function highlight(match, $1) {
        // $1 containts % in matches with %s, which we don't want to replace
        return ($1 ? match : '<b><u>' + match + '</u></b>');
    }

    function display(textbox, search, suggz, dataz) {
        prepare();
        show();

        lastA = null;
        hasData = 1;
        selection = null;

        while (container.firstChild) {
            $WH.de(container.firstChild);
        }

        search = search.replace(/[^a-z0-9\-]/gi, ' ');
        search = $WH.trim(search.replace(/\s+/g, ' '));

        var regex = g_createOrRegex(search, (search == 's' ? '[%]' : null));

        for (var i = 0, len = suggz.length; i < len; ++i) {
            var pos = suggz[i].lastIndexOf('(');
            if (pos != -1) {
                suggz[i] = suggz[i].substr(0, pos - 1);
            }

            var
                type   = dataz[i][0],
                typeId = dataz[i][1],
                param1 = dataz[i][2],
                param2 = dataz[i][3],

                a      = $WH.ce('a'),
                sp     = $WH.ce('i'),
                sp2    = $WH.ce('span'),
                div    = $WH.ce('div'),
                div2   = $WH.ce('div');
                div.i  = i;

            a.onmouseover = aOver;
            a.href = '?' + g_types[type] + '=' + typeId;

            if (textbox._append) {
                a.rel += textbox._append;
            }

            if (type == 1 && param1 != null) {
                div.className += ' live-search-icon-boss';
            }
            if (type == 3 && param2 != null) {
                a.className += ' q' + param2;
            }
            else if (type == 4 && param1 != null) {
                a.className += ' q' + param1;
            }
            else if (type == 13) {
                a.className += ' c' + typeId;
            }

            if ((type == 3 || type == 6 || type == 9 || type == 10 || type == 13 || type == 14 || type == 15 || type == 17) && param1) {
                div.className += ' live-search-icon';
                div.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/small/' + param1.toLowerCase() + '.jpg)';
            }
            else if ((type == 5 || type == 11) && param1 >= 1 && param1 <= 2) {
                div.className += ' live-search-icon-quest-' + (param1 == 1 ? 'alliance' : 'horde');
            }

            $WH.ae(sp, $WH.ct(LANG.types[type][0]));
            $WH.ae(a, sp);

            var buffer = suggz[i];
            buffer = buffer.replace(regex, highlight);

            if (type == 11) {
                buffer = buffer.replace('%s', '<span class="q0">&lt;'+ LANG.name + '&gt;</span>');
            }

            sp2.innerHTML = buffer;
            $WH.ae(a, sp2);

            if (type == 6 && param2) {
                $WH.ae(a, $WH.ct(' (' + param2 + ')'));
            }

            $WH.ae(div2, a);
            $WH.ae(div, div2);
            $WH.ae(container, div);
        }
    }

    function receive(xhr, opt) {
        var text = xhr.responseText;
        if (text.charAt(0) != '[' || text.charAt(text.length - 1) != ']') {
            return;
        }

        var a = eval(text);

        var search = a[0];
        if (search == opt.search) {
            if (a.length == 8) {
                display(opt.textbox, search, a[1], a[7]);
            }
            else {
                hide();
            }
        }
    }

    function fetch(textbox, search) {
        var url = '?search=' + $WH.urlencode(search);

        if (textbox._type) {
            url += '&json&type=' + textbox._type;
        }
        else {
            url += '&opensearch';
        }

        new Ajax(url, {
            onSuccess: receive,
            textbox: textbox,
            search: search
        })
    }

    function preFetch(textbox, search) {
        if (cancelNext) {
            cancelNext = 0;
            return;
        }
        hasData = 0;
        if (timer > 0) {
            clearTimeout(timer);
            timer = 0;
        }
        timer = setTimeout(fetch.bind(0, textbox, search), LIVESEARCH_DELAY);
    }

    function cycle(dir) {
        if (!isVisible()) {
            if (hasData) {
                show();
            }
            return;
        }

        var firstNode = (container.childNodes[0].nodeName == "EM" ? container.childNodes[3] : container.firstChild);
        var bakDiv = dir ? firstNode : container.lastChild;

        if (lastDiv == null) {
            colorDiv(bakDiv);
        }
        else {
            var div = dir ? lastDiv.nextSibling : lastDiv.previousSibling;
            if (div) {
                if (div.nodeName == "STRONG") {
                    div = container.lastChild;
                }
                colorDiv(div);
            }
            else {
                colorDiv(bakDiv);
            }
        }
    }

    function onKeyUp(e) {
        e = $WH.$E(e);
        var textbox = e._target;

        switch (e.keyCode) {
            case 48:
            case 96:
            case 107:
            case 109:
                if ($WH.Browser.firefox && e.ctrlKey) {
                    adjust(textbox);
                    break;
                }
                break;
        }

        var search = $WH.trim(textbox.value.replace(/\s+/g, ' '));
        if (search == lastSearch[textbox.id]) {
            return;
        }

        lastSearch[textbox.id] = search;
        if (search.length) {
            preFetch(textbox, search);
        }
        else {
            hide();
        }
    }

    function onKeyDown(e) {
        e = $WH.$E(e);
        var textbox = e._target;

        switch (e.keyCode) {
            case 27: // Escape
                hide();
                break;
            case 38: // Up
                cycle(0);
                break;
            case 40: // Down
                cycle(1);
                break
        }
    }

    function onFocus(e) {
        e = $WH.$E(e);
        var textbox = e._target;

        if (textbox != document) {
            currentTextbox = textbox;
        }
    }

    this.attach = function(textbox) {
        textbox = $(textbox);
        if (!textbox.length) {
            return;
        }

        textbox = textbox[0];

        if (textbox.getAttribute('autocomplete') == 'off') {
            return;
        }

        textbox.setAttribute('autocomplete', 'off');

        $WH.aE(textbox, 'focus', onFocus);
        $WH.aE(textbox, 'keyup', onKeyUp);
        $WH.aE(textbox, 'keydown', onKeyDown);
    };

    this.reset = function(textbox) {
        lastSearch[textbox.id] = null;
        textbox.value = '';
        hasData = 0;
        hide();
    };

    this.hide = function() {
        hide();
    }
};

var Lightbox = new function() {
    var
        overlay,
        outer,
        inner,
        divs = {},

        funcs = {},

        prepared,
        lastId;

    function hookEvents() {
        $WH.aE(overlay, 'click', hide);
        $WH.aE(document, 'keydown', onKeyDown);
        $WH.aE(window, 'resize', onResize);
    }

    function unhookEvents() {
        $WH.dE(overlay, 'click', hide);
        $WH.dE(document, 'keydown', onKeyDown);
        $WH.dE(window, 'resize', onResize);
    }

    function prepare() {
        if (prepared) {
            return;
        }

        prepared = 1;

        var dest = document.body;

        overlay = $WH.ce('div');
        overlay.className = 'lightbox-overlay';

        outer = $WH.ce('div');
        outer.className = 'lightbox-outer';

        inner = $WH.ce('div');
        inner.className = 'lightbox-inner';

        overlay.style.display = outer.style.display = 'none';

        $WH.ae(dest, overlay);
        $WH.ae(outer, inner);
        $WH.ae(dest, outer);
    }

    function onKeyDown(e) {
        e = $WH.$E(e);
        switch (e.keyCode) {
        case 27: // Escape
            hide();
            break;
        }
    }

    function onResize(fake) {
        if (fake != 1234) {
            if (funcs.onResize) {
                funcs.onResize();
            }
        }

        overlay.style.height = document.body.offsetHeight + 'px';
    }

    function hide() {
        if (!prepared) {
            return;
        }

        unhookEvents();

        if (funcs.onHide) {
            funcs.onHide();
        }

        overlay.style.display = outer.style.display = 'none';

        g_enableScroll(true);
    }

    function reveal() {
        overlay.style.display = outer.style.display = divs[lastId].style.display = '';
        Lightbox.setSize(inner.offsetWidth, inner.offsetHeight, 1);
    }

    this.setSize = function(w, h, auto) {
        if (!$WH.Browser.ie) {
            inner.style.visibility = 'hidden';
        }

        if (!auto) {
            inner.style.width = w + 'px';
            if (h) {
                inner.style.height = h + 'px';
            }
        }

        inner.style.left = -parseInt(w / 2) + 'px';
        if (h) {
            inner.style.top    = -parseInt(h / 2) + 'px';
        }
        inner.style.visibility = 'visible';
    };

    this.show = function(id, _funcs, opt) {
        funcs = _funcs || {};

        prepare();

        hookEvents();

        if (lastId != id && divs[lastId] != null) {
            divs[lastId].style.display = 'none';
        }

        lastId = id;

        var
            first = 0,
            d;

        if (divs[id] == null) {
            first = 1;
            d = $WH.ce('div');
            $WH.ae(inner, d);
            divs[id] = d;
        }
        else {
            d = divs[id];
        }

        if (funcs.onShow) {
            funcs.onShow(d, first, opt);
        }

        onResize(1234);
        reveal();

        g_enableScroll(false);
    };

    this.reveal = function() {
        reveal();
    };

    this.hide = function() {
        hide();
    };

    this.isVisible = function() {
        return (overlay && overlay.style.display != 'none');
    }
};

/*
Locale class moved to own file
*/

function Mapper(opt, noScroll) {
    $WH.cO(this, opt);

    if(this.parent && !this.parent.nodeName) {
        this.parent = $WH.ge(this.parent);
    }
    else if(!this.parent) {
        return;
    }

    var _;

    this.mouseX = this.mouseY = 0;

    this.editable = this.editable || false;
    this.overlay  = this.overlay || false;

    if(this.editable) {
        this.zoomable = this.toggle = false;
        this.show     = this.mouse  = true;
    }
    else {
        this.zoomable = (this.zoomable == null ? true  : this.zoomable);
        this.toggle   = (this.toggle   == null ? true  : this.toggle);
        this.show     = (this.show     == null ? true  : this.show);
        this.mouse    = (this.mouse    == null ? false : this.mouse);
    }

    this.buttons = (this.buttons == null ? true : this.buttons);

    this.zoneLink = (this.zoneLink == null ? true : this.zoneLink);
    if(location.href.indexOf('zone=') != -1) {
        this.zoneLink = false;
    }

    this.zoom = (this.zoom == null ? 0 : this.zoom);
    this.zone = (this.zone == null ? 0 : this.zone);
    this.level = (this.level == null ? (Mapper.zoneDefaultLevel[this.zone] ? Mapper.zoneDefaultLevel[this.zone] : 0) : this.level);

    this.pins = [];
    this.nCoords = 0;

    this.tempWidth = null;
    this.tempHeight = null;

    this.parent.className = 'mapper';
    this.parent.appendChild(this.span = $WH.ce('span'));

    _ = this.span.style;
    _.display  = 'block';
    _.position = 'relative';

    $WH.ns(this.span);

    this.overlaySpan = _ = $WH.ce('div');
    _.style.display = 'block';
    _.style.width = '100%';
    _.style.height = '100%';
    this.span.appendChild(_);

    this.buttonDiv = _ = $WH.ce('div');
    _.style.position = 'absolute';
    _.style.top = _.style.right = '3px';

    if(this.buttons)
        this.parent.appendChild(_);

    if(this.editable) {
        this.span.onmouseup = this.addPin.bind(this);

        _ = g_createGlow(LANG.mapper_tippin);
        _.style.fontSize = '11px';
        _.style.position = 'absolute';
        _.style.bottom = _.style.right = '0';

        $WH.ns(_);
        this.parent.appendChild(_);
    }
    else {
        this.sToggle = _ = RedButton.create(LANG.mapper_hidepins, true, this.toggleShow.bind(this));

        _.style['float'] = 'right';

        _.style.display = 'none';

        $WH.ns(_);
        this.buttonDiv.appendChild(_);
    }

    if(this.zoomable) {
        this.span.onclick = this.toggleZoom.bind(this);
        this.span.id = 'sjdhfkljawelis' + (this.unique !== undefined ? this.unique : '');

        this.sZoom = _ = g_createGlow(LANG.mapper_tipzoom);
        _.style.fontSize = '11px';
        _.style.position = 'absolute';
        _.style.bottom = _.style.right = '0';

        $WH.ns(_);
        this.span.appendChild(_);
    }

    this.sZoneLink = _ = g_createGlow('');

    _.style.display = 'none';
    _.style.position = 'absolute';
    _.style.top = _.style.left = '0';

    this.parent.appendChild(_);

    if(this.mouse) {
        this.parent.onmouseout  = (function() {
            this.timeout = setTimeout((function() {
                this.sMouse.style.display = 'none';
            }).bind(this), 1)
        }).bind(this);
        this.parent.onmouseover = (function() {
            clearTimeout(this.timeout);
            this.sMouse.style.display = '';
        }).bind(this);

        this.span.onmousemove = this.span.onmousedown = this.getMousePos.bind(this);

        this.sMouse = _ = g_createGlow('(0.0, 0.0)');

        _.style.display = 'none';
        _.style.position = 'absolute';
        _.style.bottom = _.style.left = '0';

        _.onmouseup = $WH.sp;

        $WH.ns(_);
        this.span.appendChild(_);
    }

    this.floorPins = {};

    if(opt.coords != null) {
        this.setCoords(opt.coords);
    }
    else if(opt.link != null) {
        this.setLink(opt.link);
    }

    if(opt.objectives)
        this.setObjectives(opt.objectives);
    if(opt.zoneparent && opt.zones)
        this.setZones(opt.zoneparent, opt.zones);

    this.updateMap(noScroll);
};

Mapper.sizes = [
    [ 488, 325, 'normal'],
    [ 772, 515, 'zoom'],
    [1002, 668, 'original'],
    [ 224, 149, 'small']
];

Mapper.onlyOneFloor = {
    4265: true, // Nexus
    4264: true, // Halls of Stone
    4416: true, // Gundrak
    4415: true, // Violet Hold
    4493: true, // Obsidian Sanctum
    4500: true, // Eye of Eternity
    4603: true, // Vault of Archavon
    4723: true, // Trial of the Champion
    4809: true, // The Forge of Souls
    4813: true, // Pit of Saron
    4820: true  // Halls of Reflection
};

Mapper.zoneLevelOffset = {
    4273: 0 // Ulduar
};

Mapper.zoneDefaultLevel = {
    3456: 4, // Naxxramas
    4812: 4  // Icecrown Citadel
};

Mapper.remappedLevels = {
    4273: { 6: 5 }
};

Mapper.multiLevelZones = {};

Mapper.prototype = {
    getMap: function() { return this.parent },

    update: function(opt, noScroll) {
        if(opt.zoom != null) {
            this.zoom = opt.zoom;
        }

        if(opt.zone != null) {
            this.zone = opt.zone;
        }

        if(opt.show != null) {
            this.show = opt.show;
        }

        this.pins = [];
        this.nCoords = 0;
        for(var i in this.floorPins)
            if(this.floorPins[i].parentNode)
                $WH.de(this.floorPins[i]);
        this.floorPins = {};
        if(this.floorButton)
        {
            $WH.de(this.floorButton);
            this.floorButton = null;
        }

        var level = (opt.level === undefined ? 0 : this.fixLevel(parseInt(opt.level)));
        if(!opt.preservelevel)
            this.level = 0;
        else
            level = this.level;
        var mapperData = false;
        if($WH.isset('g_mapperData'))
            mapperData = g_mapperData;
        else if($WH.isset('g_mapper_data'))
            mapperData = g_mapper_data;
        if(mapperData && mapperData[this.zone] && !opt.coords)
        {
            var zone = mapperData[this.zone];
            var maxCount = -1;
            for(var i in zone)
            {
                i = parseInt(i);
                var iLevel = this.fixLevel(i);

                if(opt.level === undefined && zone[i].count > maxCount)
                {
                    level = parseInt(iLevel);
                    maxCount = zone[i].count;
                }

                if(zone[i].coords)
                    this.setCoords(zone[i].coords, iLevel);
            }
            this.level = level;
            if(this.floorPins[this.level])
                $WH.ae(this.span, this.floorPins[this.level]);
        }
        else if(opt.coords != null)
        {
            var lowestLevel = 999;
            for(var i in opt.coords)
            {
                i = parseInt(i);
                var iLevel = this.fixLevel(i);
                this.setCoords(opt.coords[i], iLevel);
                if(iLevel < lowestLevel)
                    lowestLevel = iLevel;
            }

            if(lowestLevel != 999 && !opt.preservelevel)
                this.level = lowestLevel;

            if(this.floorPins[this.level])
                $WH.ae(this.span, this.floorPins[this.level]);
        }
        //    this.setCoords(opt.coords);
        else if(opt.link != null)
            this.setLink(opt.link);

        this.updateMap(noScroll);
    },

    fixLevel: function(level)
    {
        if(Mapper.zoneLevelOffset[this.zone] !== undefined)
            level += Mapper.zoneLevelOffset[this.zone];
        else if(Mapper.multiLevelZones[this.zone] && level > 0)
            level += -1;
        else if(Mapper.multiLevelZones[this.zone] == undefined)
            level = 0;

        if(Mapper.remappedLevels[this.zone] && Mapper.remappedLevels[this.zone][level] !== undefined)
            level = Mapper.remappedLevels[this.zone][level];

        return level;
    },

    getZone: function() {
        return this.zone;
    },

    setZone: function(zone, level, noScroll) {
        this.pins = [];
        this.nCoords = 0;
        if(this.floorPins[this.level])
            $WH.de(this.floorPins[this.level]);
        this.floorPins = {};
        if(this.floorButton)
        {
            $WH.de(this.floorButton);
            this.floorButton = null;
        }

        this.zone = zone;
        this.level = level | 0;
        this.updateMap(noScroll);

        return true;
    },

    showFloors: function(event)
    {
        if(!Mapper.multiLevelZones[this.zone])
            return;

        var menu = [];
        var _ = Mapper.multiLevelZones[this.zone];
        var src = g_zone_areas;

        for(var i = 0; i < _.length; ++i)
        {
            var menuItem;
            if(!src[this.zone])
                menuItem = [i, '[Level ' + (i + 1) + ']', this.setMap.bind(this, _[i], i, true)];
            else
                menuItem = [i, src[this.zone][i], this.setMap.bind(this, _[i], i, true)];

            if(i == this.level || (this.level === undefined && i == 0))
                menuItem.checked = true;

            menu.push(menuItem);
        }

        Menu.showAtCursor(menu, event);
    },

    setMap: function(map, level, forceUpdate)
    {
        if(level != this.level)
        {
            if(this.floorPins[this.level])
                $WH.de(this.floorPins[this.level]);
            if(this.floorPins[level])
                $WH.ae(this.span, this.floorPins[level]);
            this.level = level;
        }

        var type = Locale.getName();

        if($WH.isset('g_ptr') && g_ptr)
            type = 'ptr';
        else if($WH.isset('g_beta') && g_beta)
            type = 'beta';
        else if($WH.isset('g_old') && g_old)
            type = 'old';

        this.span.style.background = 'url(' + g_staticUrl + '/images/wow/maps/' + type + '/' + Mapper.sizes[this.zoom][2] + '/' + map + '.jpg)';

        if(this.overlay)
            this.overlaySpan.style.background = 'url(' + g_staticUrl + '/images/wow/maps/overlay/' + Mapper.sizes[this.zoom][2] + '/' + map + '.png)';

        if(this.sZoneLink)
        {
            var zoneText = '';
            var zoneId = parseInt(this.zone);
            var found = g_zones[zoneId] != null;
            var src = g_zone_areas;
            if(found)
            {
                if(this.zoneLink)
                    zoneText += '<a href="?zone=' + zoneId + '">' + g_zones[zoneId] + '</a>';
                if(Mapper.multiLevelZones[zoneId])
                {
                    if(this.zoneLink)
                        zoneText += ': ';
                    zoneText += (src[zoneId] ? src[zoneId][this.level] : 'Level ' + (this.level+1));
                }
                g_setInnerHtml(this.sZoneLink, zoneText, 'div');
                if(this.zoneLink)
                {
                    for(var i = 0; i < 9; ++i)
                    {
                        if(i == 4) continue;
                        this.sZoneLink.childNodes[i].firstChild.style.color = 'black';
                    }
                }
            }
            this.sZoneLink.style.display = found ? '' : 'none';
        }

        if(forceUpdate)
            this.onMapUpdate && this.onMapUpdate(this);
    },

    setObjectives: function(obj)
    {
        var startEnd = { start: 1, end: 1, startend: 1, sourcestart: 1, sourceend: 1 };
        for(var z in obj)
        {
            var zone = obj[z];
            if(g_mapperData[z] === undefined)
                g_mapperData[z] = {};
            var objectiveIndex = {};
            var nextIndex = 0;
            for(var l in zone.levels)
            {
                var level = zone.levels[l];
                var results = ShowOnMap.combinePins(level);
                var pins = results[0];
                g_mapperData[z][l] = { count: pins.length, coords: [] };
                for(var i = 0; i < pins.length; ++i)
                {
                    var tooltip = ShowOnMap.buildTooltip(pins[i].list);
                    g_mapperData[z][l].coords.push([pins[i].coord[0], pins[i].coord[1], { type: tooltip[1], url: tooltip[2], menu: tooltip[3], label: tooltip[0] }]);
                }
            }
        }
    },

    setZones: function(div, zones)
    {
        div = $WH.ge(div);
        if(!div || !zones || zones.length == 0 || !this.objectives)
            return;

        var zoneLinks = function(self, container, zoneList, zoneTypes)
        {
            var maxIdx = [false, -1];
            for(var i = 0; i < zoneList.length; ++i)
            {
                if(i > 0) $WH.ae(span, $WH.ct(i == zoneList.length-1 ? LANG.and : LANG.comma));
                var entry = null;
                if(self.objectives[zoneList[i][0]].mappable > 0)
                {
                    entry = $WH.ce('a');
                    entry.href = 'javascript:;';
                    $WH.ae(entry, $WH.ct(self.objectives[zoneList[i][0]].zone));
                    entry.onclick = function(link, zone) {
                        self.update({ zone: zone });
                        g_setSelectedLink(link, 'mapper');
                    }.bind(self, entry, zoneList[i][0]);
                    entry.isLink = true;
                }
                else
                {
                    entry = $WH.ce('a');
                    entry.href = '?zone=' + zoneList[i][0];
                    $WH.ae(entry, $WH.ct(self.objectives[zoneList[i][0]].zone));
                    g_addTooltip(entry, LANG.tooltip_zonelink);
                }

                if(zones.length > 1)
                {
                    var types = zoneTypes[zoneList[i][0]];
                    if(types.start && types.end)
                    {
                        entry.className += ' icontiny';
                        entry.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/quest_startend.gif)';
                        entry.style.paddingLeft  = '20px';
                    }
                    else if(types.start)
                    {
                        entry.className += ' icontiny';
                        entry.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/quest_start.gif)';
                        entry.style.paddingLeft  = '14px';
                    }
                    else if(types.end)
                    {
                        entry.className += ' icontiny';
                        entry.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/quest_end.gif)';
                        entry.style.paddingLeft  = '16px';
                    }
                }
                $WH.ae(container, entry);

                if(zoneList[i][1] > maxIdx[1])
                    maxIdx = [entry, zoneList[i][1]];
            }
            return maxIdx[0];
        };

        var getZoneList = function(zoneList, zoneTypes, type)
        {
            var ret = [];
            for(var i = 0; i < zoneList.length; ++i)
            {
                if(zoneTypes[zoneList[i][0]][type])
                    ret.push(zoneList[i]);
            }
            return ret;
        };

        var typesByZone = {};
        var types = { start: [], end: [], objective: [] };

        for(var zoneId in this.objectives)
        {
            if(typesByZone[zoneId] === undefined)
                typesByZone[zoneId] = {};
            var zone = this.objectives[zoneId];
            for(var levelNum in zone.levels)
            {
                var level = zone.levels[levelNum];
                for(var i = 0; i < level.length; ++i)
                {
                    if(level[i].point == 'start' || level[i].point == 'sourcestart')
                    {
                        types.start.push(zoneId);
                        typesByZone[zoneId].start = true;
                    }
                    else if(level[i].point == 'end' || level[i].point == 'sourceend')
                    {
                        types.end.push(zoneId);
                        typesByZone[zoneId].end = true;
                    }
                    else if(level[i].point == 'requirement' || level[i].point == 'sourcerequirement')
                    {
                        types.objective.push(zoneId);
                        typesByZone[zoneId].objective = true;
                    }
                }
            }
        }

        var h3 = $WH.ce('h3');
        $WH.ae(h3, $WH.ct(LANG.mapper_relevantlocs));
        $WH.ae(div, h3);
        if(zones.length == 1 && this.missing == 0)
        {
            var span = $WH.ce('span');
            span.innerHTML = LANG.mapper_entiretyinzone.replace('$$', '<b>' + this.objectives[zones[0][0]].zone + '</b>.');
            $WH.ae(div, span);
            this.update({ zone: zones[0][0] });
        }
        else if(this.missing > 0)
        {
            var span = $WH.ce('span');
            var primaryLink = false, secondaryLink = false, tertiaryLink = false;
            types.objective = $WH.array_unique(types.objective);
            types.start = $WH.array_unique(types.start);
            types.end = $WH.array_unique(types.end);
            var startEnd = types.start.length > 0 && $WH.array_compare(types.start, types.end);
            var startObj = types.start.length > 0 && $WH.array_compare(types.start, types.objective);
            var endObj = types.end.length > 0 && $WH.array_compare(types.end, types.objective);

            var objZones = getZoneList(zones, typesByZone, 'objective');
            var startZones = getZoneList(zones, typesByZone, 'start');
            var endZones = getZoneList(zones, typesByZone, 'end');

            if(startEnd && startObj) // everything in the same zones
            {
                var parts = LANG.mapper_happensin.split('$$');
                $WH.ae(span, $WH.ct(parts[0]));
                primaryLink = zoneLinks(this, span, zones, typesByZone);
                $WH.ae(span, $WH.ct(parts[1]));
            }
            else if(startEnd && types.objective.length == 0) // starts and ends in x
            {
                var parts = LANG.mapper_objectives.sex.split('$$');
                $WH.ae(span, $WH.ct(parts[0]));
                primaryLink = zoneLinks(this, span, zones, typesByZone);
                $WH.ae(span, $WH.ct(parts[1]));
            }
            else if(startEnd) // objectives in x, starts and ends in y
            {
                var parts = LANG.mapper_objectives.ox_sey.split('$$');
                $WH.ae(span, $WH.ct(parts[0]));
                primaryLink = zoneLinks(this, span, startZones, typesByZone);
                $WH.ae(span, $WH.ct(parts[1]));
                secondaryLink = zoneLinks(this, span, objZones, typesByZone);
                $WH.ae(span, $WH.ct(parts[2]));
            }
            else if(startObj && types.end.length == 0) // objectives and starts in x
            {
                var parts = LANG.mapper_objectives.osx.split('$$');
                $WH.ae(span, $WH.ct(parts[0]));
                primaryLink = zoneLinks(this, span, zones, typesByZone);
                $WH.ae(span, $WH.ct(parts[1]));
            }
            else if(startObj) // objectives and starts in x, ends in y
            {
                var parts = LANG.mapper_objectives.osx_ey.split('$$');
                $WH.ae(span, $WH.ct(parts[0]));
                primaryLink = zoneLinks(this, span, objZones, typesByZone);
                $WH.ae(span, $WH.ct(parts[1]));
                secondaryLink = zoneLinks(this, span, endZones, typesByZone);
                $WH.ae(span, $WH.ct(parts[2]));
            }
            else if(endObj && types.start.length == 0) // objectives and ends in x
            {
                var parts = LANG.mapper_objectives.oex.split('$$');
                $WH.ae(span, $WH.ct(parts[0]));
                primaryLink = zoneLinks(this, span, zones, typesByZone);
                $WH.ae(span, $WH.ct(parts[1]));
            }
            else if(endObj) // objectives and ends in x, starts in y
            {
                var parts = LANG.mapper_objectives.oex_sy.split('$$');
                $WH.ae(span, $WH.ct(parts[0]));
                secondaryLink = zoneLinks(this, span, objZones, typesByZone);
                $WH.ae(span, $WH.ct(parts[1]));
                primaryLink = zoneLinks(this, span, startZones, typesByZone);
                $WH.ae(span, $WH.ct(parts[2]));
            }
            else if(types.start.length > 0 && types.end.length > 0 && types.objective.length > 0) // objectives in x, starts in y, ends in z
            {
                var parts = LANG.mapper_objectives.ox_sy_ez.split('$$');
                $WH.ae(span, $WH.ct(parts[0]));
                primaryLink = zoneLinks(this, span, startZones, typesByZone);
                $WH.ae(span, $WH.ct(parts[1]));
                secondaryLink = zoneLinks(this, span, objZones, typesByZone);
                $WH.ae(span, $WH.ct(parts[2]));
                tertiaryLink = zoneLinks(this, span, endZones, typesByZone);
                $WH.ae(span, $WH.ct(parts[3]));
            }
            else if(types.start.length > 0 && types.end.length > 0) // starts in x, ends in y
            {
                var parts = LANG.mapper_objectives.sx_ey.split('$$');
                $WH.ae(span, $WH.ct(parts[0]));
                primaryLink = zoneLinks(this, span, startZones, typesByZone);
                $WH.ae(span, $WH.ct(parts[1]));
                secondaryLink = zoneLinks(this, span, endZones, typesByZone);
                $WH.ae(span, $WH.ct(parts[2]));
            }
            else if(types.start.length > 0 && types.objective.length > 0) // objectives in x, starts in y
            {
                var parts = LANG.mapper_objectives.ox_sy.split('$$');
                $WH.ae(span, $WH.ct(parts[0]));
                primaryLink = zoneLinks(this, span, startZones, typesByZone);
                $WH.ae(span, $WH.ct(parts[1]));
                secondaryLink = zoneLinks(this, span, objZones, typesByZone);
                $WH.ae(span, $WH.ct(parts[2]));
            }
            else if(types.end.length > 0 && types.objective.length > 0) // objectives in x, ends in y
            {
                var parts = LANG.mapper_objectives.ox_ey.split('$$');
                $WH.ae(span, $WH.ct(parts[0]));
                primaryLink = zoneLinks(this, span, objZones, typesByZone);
                $WH.ae(span, $WH.ct(parts[1]));
                secondaryLink = zoneLinks(this, span, endZones, typesByZone);
                $WH.ae(span, $WH.ct(parts[2]));
            }
            else if(types.start.length > 0) // starts in x
            {
                var parts = LANG.mapper_objectives.sx.split('$$');
                $WH.ae(span, $WH.ct(parts[0]));
                primaryLink = zoneLinks(this, span, zones, typesByZone);
                $WH.ae(span, $WH.ct(parts[1]));
            }
            else if(types.end.length > 0) // ends in x
            {
                var parts = LANG.mapper_objectives.ex.split('$$');
                $WH.ae(span, $WH.ct(parts[0]));
                primaryLink = zoneLinks(this, span, zones, typesByZone);
                $WH.ae(span, $WH.ct(parts[1]));
            }
            else if(types.objective.length > 0) // objectives in x
            {
                var parts = LANG.mapper_objectives.ox.split('$$');
                $WH.ae(span, $WH.ct(parts[0]));
                primaryLink = zoneLinks(this, span, zones, typesByZone);
                $WH.ae(span, $WH.ct(parts[1]));
            }
            else // wat?
            {
                var parts = LANG.mapper_happensin.split('$$');
                $WH.ae(span, $WH.ct(parts[0]));
                primaryLink = zoneLinks(this, span, zones, typesByZone);
                $WH.ae(span, $WH.ct(parts[1]));
            }
            $WH.ae(div, span);

            if(primaryLink && primaryLink.isLink)
                primaryLink.click();
            else if(secondaryLink && secondaryLink.isLink)
                secondaryLink.click();
            else if(tertiaryLink && tertiaryLink.isLink)
                tertiaryLink.click();
        }
        else
        {
            var parts = LANG.mapper_happensin.split('$$');
            var span = $WH.ce('span');
            $WH.ae(span, $WH.ct(parts[0]));
            var primaryLink = zoneLinks(this, span, zones, typesByZone);
            $WH.ae(span, parts[1]);
            $WH.ae(div, span);

            if(primaryLink && primaryLink.isLink)
                primaryLink.click();
        }
    },

    setSize: function(w, h) { this.tempWidth = w; this.tempHeight = h; this.updateMap(true); },

    getZoom: function() { return this.zoom; },
    setZoom: function(zoom, noScroll) { this.zoom = zoom; this.tempWidth = this.tempHeight = null; this.updateMap(noScroll); },

    toggleZoom: function(e)
    {
        this.zoom = 1 - this.zoom;
        this.updateMap(true);

        if(e)
            this.getMousePos(e);

        if(this.sZoom)
        {
            if(this.sZoom.style.display == 'none')
                this.sZoom.style.display = '';
            else
                this.sZoom.style.display = 'none';
        }

        /*  aowow: check for e is custom as it should only affect the lightbox */
        if(this.zoom && e === undefined)
            MapViewer.show({ mapper: this });
    },

    getShow: function() { return this.show; },
    setShow: function(show)
    {
        this.show = show;

        var d = this.show ? '' : 'none';

        for(var i in this.floorPins)
            this.floorPins[i].style.display = d;
        RedButton.setText(this.sToggle, (this.show ? LANG.mapper_hidepins : LANG.mapper_showpins));
    },

    toggleShow: function()
    {
        this.setShow(!this.show);
    },

    getCoords: function()
    {
        var a = [];

        for(var i in this.pins)
            if(!this.pins[i].free)
                a.push([this.pins[i].x, this.pins[i].y]);

        return a;
    },

    clearPins: function()
    {
        for(var i in this.pins)
        {
            this.pins[i].style.display = 'none';
            this.pins[i].free = true;
        }
    },

    setCoords: function(coords, coordsLevel)
    {
        var _;

        var level, noLevel;
        if(coordsLevel === undefined)
        {
            this.clearPins();

            if(coords.length) // Backward compatibility
            {
                noLevel = true;
                level = 0;
            }
            else
            {
                for(var i in coords)
                {
                    level = i;
                    break;
                }

                if(level == null) return;

                coords = coords[level];
            }

            level = parseInt(level);

            if(!noLevel)
            {
                level = this.fixLevel(level);
                this.level = level;
            }
        }
        else
        {
            level = coordsLevel;
        }

        this.nCoords = coords.length;

        for(var i in coords)
        {
            var
                coord = coords[i],
                opt   = coord[2];

            if(coord[0] === undefined || coord[1] === undefined)
                continue;

            _ = this.getPin(level);
            _.x = coord[0];
            _.y = coord[1];
            _.style.left = _.x + '%';
            _.style.top = _.y + '%';

            if(this.editable)
                _.a.onmouseup = this.delPin.bind(this, _);
            else if(opt && opt.url)
            {
                _.a.href = Markup._fixUrl(opt.url);
                _.a.rel = "np";
                _.a.style.cursor = 'pointer';
            }

            if(opt && opt.tooltip)
            {
                _.a.tt = '';
                var printedFooter = false;
                for(var name in opt.tooltip)
                {
                    if(_.a.tt != '')
                        _.a.tt += '<br />';
                    _.a.tt += '<b class="q">' + name + '</b> ($)<br />';
                    for(var line in opt.tooltip[name].info)
                        _.a.tt += '<div>' + opt.tooltip[name].info[line] + '</div>';
                    if(!printedFooter && opt.tooltip[name].footer)
                    {
                        _.a.tt += opt.tooltip[name].footer + '<br />';
                        printedFooter = true;
                    }
                }
            }
            else if(opt && opt.label)
                _.a.tt = opt.label;
            else
                _.a.tt = '$';

            if(opt && opt.menu)
            {
                _.a.menu = opt.menu;
                Menu.add(_.a, _.a.menu, { showAtCursor: true });
                // _.a.onclick = function() {
                    // (Menu.show.bind(this))();
                    // $WH.Tooltip.hide();
                    // this.onmouseout = function() {
                        // Menu.hide();
                        // this.onmouseout = $WH.Tooltip.hide();
                    // }.bind(this);
                    // $WH.sp();
                    // return false;
                // }.bind(_.a);
            }

            if(opt && opt.type)
                _.className += ' pin-' + opt.type;

            _.a.tt = $WH.str_replace(_.a.tt, '$', _.x.toFixed(1) + ', ' + _.y.toFixed(1));

            if(opt && opt.lines)
            {
                for(var p = 0, nPaths = opt.lines.length; p < nPaths; ++p)
                {
                    if(coord[0] == opt.lines[p][0] && coord[1] == opt.lines[p][1])
                        continue;

                    for(var s = 0, nSizes = Mapper.sizes.length; s < nSizes; ++s)
                    {
                        var size = Mapper.sizes[s];
                        _ = Line(coord[0] * size[0] / 100, coord[1] * size[1] / 100, opt.lines[p][0] * size[0] / 100, opt.lines[p][1] * size[1] / 100, opt.type);
                        _.className += ' ' + size[2];
                        $WH.ae(this.floorPins[level], _);
                    }
                }
            }
        }

        this.onPinUpdate && this.onPinUpdate(this);
    },

    getLink: function()
    {
        var s = '';

        for(var i in this.pins)
            if(!this.pins[i].free && this.pins[i].floor == this.level)
                s += (this.pins[i].x < 10 ? '0' : '') + (this.pins[i].x < 1 ? '0' : '') + (this.pins[i].x * 10).toFixed(0) + (this.pins[i].y < 10 ? '0' : '') + (this.pins[i].y < 1 ? '0' : '') + (this.pins[i].y * 10).toFixed(0);

        return (this.zone ? this.zone : '') + (Mapper.multiLevelZones[this.zone] && this.level != 0 ? '.' + this.level : '') + (s ? ':' + s : '');
    },

    setLink: function(link, noScroll)
    {
        var a = [];

        link = link.split(':');

        var zone = link[0];
        var level = 0;
        if(zone.indexOf('.') != -1)
        {
            var zoneLevel = zone.split('.');
            zone = zoneLevel[0];
            level = parseInt(zoneLevel[1]);
        }

        if(!this.setZone(zone, level, noScroll))
            return false;

        if(link.length == 2)
        {
            for(var i = 0; i < link[1].length; i += 6)
            {
                var x = link[1].substr(i, 3) / 10;
                var y = link[1].substr(i + 3, 3) / 10;
                if(isNaN(x) || isNaN(y)) break;
                a.push([x, y]);
            }
        }

        this.setCoords(a, level);

        return true;
    },

    updateMap: function(noScroll)
    {
        this.parent.style.width  = this.span.style.width  = (this.tempWidth ? this.tempWidth : Mapper.sizes[this.zoom][0]) + 'px';
        this.parent.style.height = this.span.style.height = (this.tempHeight ? this.tempHeight : Mapper.sizes[this.zoom][1]) + 'px';
        if(!this.editable)
            this.parent.style.cssFloat = this.parent.style.styleFloat = 'left';

        if(this.zone == '0')
            this.span.style.background = 'black';
        else
        {
            var level = this.level;

            if(level == 1 && Mapper.onlyOneFloor[this.zone])
                level = 0;

            var map = this.zone + (level ? '-' + level : '');
            if(Mapper.multiLevelZones[this.zone])
            {
                map = Mapper.multiLevelZones[this.zone][level];
            }
            this.setMap(map, level);

            if(!this.floorButton && Mapper.multiLevelZones[this.zone])
            {
                this.floorButton = _ = RedButton.create(LANG.mapper_floor, true, this.showFloors.bind(this));

                _.style['float'] = 'right';

                $WH.ns(_);
                this.buttonDiv.appendChild(_);
            }
            else if(this.floorButton)
                this.floorButton.style.display = Mapper.multiLevelZones[this.zone] ? '' : 'none';
        }

        if(this.sToggle)
            this.sToggle.style.display = (this.toggle && this.nCoords ? '' : 'none');

        if(!noScroll)
            $WH.g_scrollTo(this.parent, 3);

        // replacement start
        // $('.line', this.floorPins[level]).hide();
        // $('.line.' + Mapper.sizes[this.zoom][2], this.floorPins[level]).show();
        if (this.floorPins[level]) {
            var lines = this.floorPins[level].getElementsByClassName('line');
            for (i in lines) {
                if (i < lines.length) {
                    lines[i].style.display = 'none';
                }
            }

            lines = this.floorPins[level].getElementsByClassName('line ' + Mapper.sizes[this.zoom][2]);
            for (i in lines) {
                if (i < lines.length) {
                    lines[i].style.display = 'block';
                }
            }
        }
        // end of replacement

        this.onMapUpdate && this.onMapUpdate(this);
    },

    cleanPin: function(i, floor)
    {
        var _ = this.pins[i];
        _.style.display = '';
        _.free = false;
        _.className = 'pin';

        _.a.onmousedown = $WH.rf;
        _.a.onmouseup = $WH.rf;
        _.a.href = 'javascript:;';
        _.a.style.cursor = 'default';
        _.floor = floor;

        return _;
    },

    getPin: function(floor)
    {
        for(var i = 0; i < this.pins.length; ++i)
            if(this.pins[i].free)
                return this.cleanPin(i, floor);

        var _ = $WH.ce('div'), a = $WH.ce('a');
        _.className = 'pin';
        _.appendChild(a);
        _.a = a;
        _.floor = floor;
        a.onmouseover = this.pinOver;
        a.onmouseout = $WH.Tooltip.hide;
        a.onclick = $WH.sp;

        this.pins.push(_);
        this.cleanPin(this.pins.length - 1, floor);

        if(!this.floorPins[floor])
        {
            this.floorPins[floor] = $WH.ce('div');
            this.floorPins[floor].style.display = this.show ? '' : 'none';
            if(floor == this.level)
                $WH.ae(this.span, this.floorPins[floor]);
        }
        $WH.ae(this.floorPins[floor], _);
        return _;
    },

    addPin: function(e)
    {
        e = $WH.$E(e);
        if(e._button >= 2) return;

        this.getMousePos(e);
        var p = this.getPin(this.level);

        p.x = this.mouseX;
        p.y = this.mouseY;

        p.style.left = p.x.toFixed(1) + '%';
        p.style.top = p.y.toFixed(1) + '%';

        p.a.onmouseup = this.delPin.bind(this, p);
        p.a.tt = p.x.toFixed(1) + ', ' + p.y.toFixed(1);

        this.onPinUpdate && this.onPinUpdate(this);

        return false;
    },

    delPin: function(pin, e)
    {
        e = $WH.$E(e);

        pin.style.display = 'none';
        pin.free = true;
        $WH.sp(e);

        this.onPinUpdate && this.onPinUpdate(this);

        return;
    },

    pinOver: function()
    {
        $WH.Tooltip.show(this, this.tt, 4, 0);
    },

    getMousePos: function(e)
    {
        e = $WH.$E(e);

        var c = $WH.ac(this.parent);

        var scroll = $WH.g_getScroll();

        this.mouseX = Math.floor((e.clientX + scroll.x - c[0] - 3) / Mapper.sizes[this.zoom][0] * 1000) / 10;
        this.mouseY = Math.floor((e.clientY + scroll.y - c[1] - 3) / Mapper.sizes[this.zoom][1] * 1000) / 10;

        if(this.mouseX < 0) this.mouseX = 0;
        else if(this.mouseX > 100) this.mouseX = 100;

        if(this.mouseY < 0) this.mouseY = 0;
        else if(this.mouseY > 100) this.mouseY = 100;

        if(this.mouse)
            g_setTextNodes(this.sMouse, '(' + this.mouseX.toFixed(1) + ', ' + this.mouseY.toFixed(1) + ')');
    }
};

/* aowow: already defined in locale_xx instead of being fetched later
var g_zone_areas = {};
*/

var MapViewer = new function()
{
    var imgWidth,
        imgHeight,
        scale,
        desiredScale,

        mapper,
        oldOnClick,
        oldOnUpdate,
        oldParent,
        oldSibling,
        tempParent,
        placeholder,

        container,
        screen,
        imgDiv,
        aCover;

    function computeDimensions()
    {
        var availHeight = Math.max(50, Math.min(618, $WH.g_getWindowSize().h - 72));

        desiredScale = 1;
        scale        = 1;//Math.min(1, availHeight / 515);
        // no scaling because it doesnt work with background images

        if(desiredScale > 1)
            desiredScale = 1;
        if(scale > 1)
            scale = 1;

        imgWidth  = Math.round(scale * 772);
        imgHeight = Math.round(scale * 515);

        var lbWidth = Math.max(480, imgWidth);

        Lightbox.setSize(lbWidth + 20, imgHeight + 52);
    }

    function getPound(extra)
    {
        var extraBits = function(map, s)
        {
            s += ':' + map.zone;
            if(map.level)
                s += '.' + map.level;
            return s;
        };
        var buff = '#map';

        if(tempParent)
            buff += '=' + mapper.getLink();
        else if(Mapper.zoneDefaultLevel[mapper.zone])
        {
            if(Mapper.zoneDefaultLevel[mapper.zone] != mapper.level)
                buff = extraBits(mapper, buff);
        }
        else if(mapper.level != 0)
            buff = extraBits(mapper, buff);
        else if((!$WH.isset('g_mapperData') || !g_mapperData[mapper.zone]) && (!$WH.isset('g_mapper_data') || !g_mapper_data[mapper.zone]))
            buff = extraBits(mapper, buff);

        return buff;
    }

    function onUpdate()
    {
        if(oldOnUpdate)
            oldOnUpdate(mapper);
        location.replace(getPound(true));
    }

    function render(resizing)
    {
        if(resizing && (scale == desiredScale) && $WH.g_getWindowSize().h > container.offsetHeight)
            return;

        container.style.visibility = 'hidden';

        computeDimensions(0);

        if(!resizing)
        {
            if(!placeholder)
            {
                placeholder = $WH.ce('div');
                placeholder.style.height = '325px';
                placeholder.style.padding = '3px';
                placeholder.style.marginTop = '10px';
            }

            mapper.parent.style.borderWidth = '0px';
            mapper.parent.style.marginTop = '0px';
            mapper.span.style.cursor = 'pointer';
            if(mapper.span.onclick)
                oldOnClick = mapper.span.onclick;
            mapper.span.onclick = Lightbox.hide;
            mapper.span.onmouseover = function() { aCover.style.display = 'block'; };
            mapper.span.onmouseout = function() { setTimeout(function() { if(!aCover.hasMouse) aCover.style.display = 'none'; }, 10); };
            if(mapper.onMapUpdate)
                oldOnUpdate = mapper.onMapUpdate;
            mapper.onMapUpdate = onUpdate;

            if(!tempParent)
            {
                oldParent = mapper.parent.parentNode;
                oldSibling = mapper.parent.nextSibling;
                oldParent.insertBefore(placeholder, mapper.parent);
                $WH.de(mapper.parent);
                $WH.ae(mapDiv, mapper.parent);
            }
            else
            {
                $WH.de(tempParent);
                $WH.ae(mapDiv, tempParent);
            }

            if(location.hash.indexOf('#show') == -1)
                location.replace(getPound(false));
            else if($WH.isset('mapShower'))
                mapShower.onExpand();
        }

        Lightbox.reveal();

        container.style.visibility = 'visible';
    }

    function onResize()
    {
        render(1);
    }

    function onHide()
    {
        if(oldOnClick)
            mapper.span.onclick = oldOnClick;
        else
            mapper.span.onclick = null;
        oldOnClick = null;
        if(oldOnUpdate)
            mapper.onMapUpdate = oldOnUpdate
        else
            mapper.onMapUpdate = null;
        oldOnUpdate = null;
        mapper.span.style.cursor = '';

        mapper.span.onmouseover = null;
        mapper.span.onmouseout = null;

        if(!tempParent)
        {
            $WH.de(placeholder);
            $WH.de(mapper.parent);
            mapper.parent.style.borderWidth = '';
            mapper.parent.style.marginTop = '';
            if(oldSibling)
                oldParent.insertBefore(mapper.parent, oldSibling);
            else
                $WH.ae(oldParent, mapper.parent);
            oldParent = oldSibling = null;
        }
        else
        {
            $WH.de(tempParent);
            tempParent = null;
        }

        mapper.toggleZoom();

        if(location.hash.indexOf('#show') == -1)
            location.replace('#.');
        else if($WH.isset('mapShower'))
            mapShower.onCollapse();
    }

    function onShow(dest, first, opt)
    {
        mapper = opt.mapper;
        container = dest;

        if(first)
        {
            dest.className = 'mapviewer';

            screen = $WH.ce('div');
            screen.style.width = '772px';
            screen.style.height = '515px';

            screen.className = 'mapviewer-screen';

            aCover = $WH.ce('a');
            aCover.className = 'mapviewer-cover';
            aCover.href = 'javascript:;';
            aCover.onclick = Lightbox.hide;
            aCover.onmouseover = function() { aCover.hasMouse = true; };
            aCover.onmouseout = function() { aCover.hasMouse = false; };
            var foo = $WH.ce('span');
            var b = $WH.ce('b');
            $WH.ae(b, $WH.ct(LANG.close));
            $WH.ae(foo, b);
            $WH.ae(aCover, foo);
            $WH.ae(screen, aCover);

            mapDiv = $WH.ce('div');
            $WH.ae(screen, mapDiv);

            $WH.ae(dest, screen);

            var aClose = $WH.ce('a');
            // aClose.className = 'dialog-x';
            aClose.className = 'dialog-cancel';
            aClose.href = 'javascript:;';
            aClose.onclick = Lightbox.hide;
            $WH.ae(aClose, $WH.ct(LANG.close));
            $WH.ae(dest, aClose);

            var d = $WH.ce('div');
            d.className = 'clear';
            $WH.ae(dest, d);
        }

        onRender();
    }

    function onRender()
    {
        render();
    }

    this.checkPound = function()
    {
        if(location.hash && location.hash.indexOf('#map') == 0)
        {
            var parts = location.hash.split('=');
            if(parts.length == 2)
            {
                var link = parts[1];

                if(link)
                {
                    /*tempParent = $WH.ce('div');
                    tempParent.id = 'fewuiojfdksl';
                    $WH.ae(document.body, tempParent);
                    var map = new Mapper({ parent: tempParent.id });
                    map.setLink(link, true);
                    map.toggleZoom();*/
                    MapViewer.show({ link: link });
                }
            }
            else
            {
                parts = location.hash.split(':');

                var map = $WH.ge('sjdhfkljawelis');
                if(map)
                    map.onclick();

                if(parts.length == 2)
                {
                    if(!map)
                        MapViewer.show({ link: parts[1]});
                    var subparts = parts[1].split('.');
                    var opts = { zone: subparts[0] };
                    if(subparts.length == 2)
                        opts.level = parseInt(subparts[1])+1;
                    mapper.update(opts);
                    //if(Mapper.multiLevelZones[mapper.zone])
                    //    mapper.setMap(Mapper.multiLevelZones[mapper.zone][floor], floor, true);
                }
            }
        }
    }

    this.show = function(opt)
    {
        if(opt.link)
        {
            tempParent = $WH.ce('div');
            tempParent.id = 'fewuiojfdksl';
            $WH.aef(document.body, tempParent);             // aowow - aef() insteead of ae() - rather scroll page to top instead of bottom
            var map = new Mapper({ parent: tempParent.id });
            map.setLink(opt.link, true);
            map.toggleZoom();
        }
        else
            Lightbox.show('mapviewer', { onShow: onShow, onHide: onHide, onResize: onResize }, opt);
    }

    $(document).ready(this.checkPound);
};

var ModelViewer = new function() {
    this.validSlots = [1,3,4,5,6,7,8,9,10,13,14,15,16,17,19,20,21,22,23,25,26];
    this.slotMap = {1: 1, 3: 3, 4: 4, 5: 5, 6: 6, 7: 7, 8: 8, 9: 9, 10: 10, 13: 21, 14: 22, 15: 22, 16: 16, 17: 21, 19: 19, 20: 5, 21: 21, 22: 22, 23: 22, 25: 21, 26: 21};
    var
        model,
        modelType,
        equipList = [],
        optBak,
        modelDiv,
        raceSel1,
        raceSel2,
        sexSel,
        oldHash,
        mode,
        readExtraPound,
        animsLoaded = false,

    races = [
        {id: 10, name: g_chr_races[10], model: 'bloodelf' },
        {id: 11, name: g_chr_races[11], model: 'draenei' },
        {id: 3,  name: g_chr_races[3],  model: 'dwarf' },
        {id: 7,  name: g_chr_races[7],  model: 'gnome' },
        {id: 1,  name: g_chr_races[1],  model: 'human' },
        {id: 4,  name: g_chr_races[4],  model: 'nightelf' },
        {id: 2,  name: g_chr_races[2],  model: 'orc' },
        {id: 6,  name: g_chr_races[6],  model: 'tauren' },
        {id: 8,  name: g_chr_races[8],  model: 'troll' },
        {id: 5,  name: g_chr_races[5],  model: 'scourge' }
    ],

    sexes = [
        {id: 0, name: LANG.male,   model: 'male' },
        {id: 1, name: LANG.female, model: 'female' }
    ];

    function clear() { }

    function getRaceSex() {
        var
            race,
            sex;

        if (raceSel1.is(':visible'))
            race = (raceSel1[0].selectedIndex >= 0 ? raceSel1.val() : '');
        else
            race = (raceSel2[0].selectedIndex >= 0 ? raceSel2.val() : '');

        sex = (sexSel[0].selectedIndex >= 0 ? sexSel.val() : 0);

        return { r: race, s: sex };
    }

    function isRaceSexValid(race, sex) {
        return (!isNaN(race) && race > 0 && $WH.in_array(races, race, function(x) {
            return x.id;
        }) != -1 && !isNaN(sex) && sex >= 0 && sex <= 1);
    }

    function render() {
        var flashVars = {
            model: model,
            modelType: modelType,
            // contentPath: 'http://static.wowhead.com/modelviewer/'
            contentPath: g_staticUrl + '/modelviewer/'
        };

        var params = {
            quality: 'high',
            allowscriptaccess: 'always',
            allowfullscreen: true,
            menu: false,
            bgcolor: '#181818',
            wmode: 'direct'
        };

        var attributes = { };

        if (modelType == 16 && equipList.length) {
            flashVars.equipList = equipList.join(',');
        }

        // swfobject.embedSWF('http://static.wowhead.com/modelviewer/ZAMviewerfp11.swf', 'modelviewer-generic', '600', '400', "11.0.0", 'http://static.wowhead.com/modelviewer/expressInstall.swf', flashVars, params, attributes);
        swfobject.embedSWF(g_staticUrl + '/modelviewer/ZAMviewerfp11.swf', 'modelviewer-generic', '600', '400', "11.0.0", g_staticUrl + '/modelviewer/expressInstall.swf', flashVars, params, attributes);

        var
            foo  = getRaceSex(),
            race = foo.r,
            sex  = foo.s;

        if (!optBak.noPound) {
            var url = '#modelviewer';
            var foo = $WH.ge('view3D-button');
            if (!foo) {
                switch (optBak.type) {
                    case 1: // npc
                        url += ':1:' + optBak.displayId + ':' + (optBak.humanoid | 0);
                        break;
                    case 2: // object
                        url += ':2:' + optBak.displayId;
                        break;
                    case 3: // item
                        url += ':3:' + optBak.displayId + ':' + (optBak.slot | 0);
                        break;
                    case 4: // item set
                        url += ':4:' + equipList.join(';');
                        break;
                }
            }
            if (race && sex) {
                url += ':' + race + '+' + sex;
            }
            else {
                url += ':';
            }

            if (optBak.extraPound != null) {
                url += ':' + optBak.extraPound;
            }

            animsLoaded = false,

            location.replace($WH.rtrim(url, ':'));
        }
    }

    function onSelChange() {
        var
            foo  = getRaceSex(),
            race = foo.r,
            sex  = foo.s;

        if (!race) {
            if (!sexSel.is(':visible'))
                return;

            sexSel.hide();

            model = equipList[1];
            switch (optBak.slot) {
                case 1:
                    modelType = 2; // Helm
                    break;
                case 3:
                    modelType = 4; // Shoulder
                    break;
                default:
                    modelType = 1; // Item
            }
        }
        else {
            if (!sexSel.is(':visible'))
                sexSel.show();

            var foo = function(x) { return x.id; };
            var raceIndex = $WH.in_array(races, race, foo);
            var sexIndex = $WH.in_array(sexes, sex,   foo);

            if (raceIndex != -1 && sexIndex != -1) {
                model = races[raceIndex].model + sexes[sexIndex].model;
                modelType = 16;
            }

            g_setWowheadCookie('temp_default_3dmodel', race + ',' + sex);
        }

        clear();
        render();
    }

    function onAnimationChange() {
        var viewer = $('#modelviewer-generic');
        if (viewer.length == 0)
            return;
        viewer = viewer[0];

        var animList = $('select', animDiv);
        if (animList.val() && viewer.isLoaded && viewer.isLoaded())
            viewer.setAnimation(animList.val());
    }

    function onAnimationMouseover() {
        if (animsLoaded)
            return;

        var viewer = $('#modelviewer-generic');
        if (viewer.length == 0)
            return;
        viewer = viewer[0];

        var animList = $('select', animDiv);
        animList.empty();
        if (!viewer.isLoaded || !viewer.isLoaded()) {
            animList.append($('<option/>', { text: LANG.tooltip_loading, val: 0 }));
            return;
        }

        var anims = {};
        var numAnims = viewer.getNumAnimations();
        for(var i = 0; i < numAnims; ++i) {
            var a = viewer.getAnimation(i);
            if(a && a != 'EmoteUseStanding')
                anims[a] = 1;
        }

        var animArray = [];
        for (var a in anims)
            animArray.push(a);
        animArray.sort();

        for (var i = 0; i < animArray.length; ++i)
            animList.append($('<option/>', { text: animArray[i], val: animArray[i] }));

        animsLoaded = true;
    }

    function initRaceSex(allowNoRace, opt) {
        var
            race = -1,
            sex  = -1,

            sel,
            offset;

        if (opt.race != null && opt.sex != null) {
            race = opt.race;
            sex = opt.sex;

            modelDiv.hide();
            allowNoRace = 0;
        }
        else {
            modelDiv.show();
        }

        if (race == -1 && sex == -1) {
            if (location.hash) {
                var matches = location.hash.match(/modelviewer:.*?([0-9]+)\+([0-9]+)/);
                if (matches != null) {
                    if (isRaceSexValid(matches[1], matches[2])) {
                        race = matches[1];
                        sex  = matches[2];
                        sexSel.show();
                    }
                }
            }
        }

        if (allowNoRace) {
            sel    = raceSel1;
            offset = 1;

            raceSel1.show();
            raceSel1[0].selectedIndex = -1;
            raceSel2.hide();
            if (sex == -1)
                sexSel.hide();
        }
        else {
            if (race == -1 && sex == -1) {
                var
                    cooRace = 1,
                    cooSex  = 0;

                if (g_user && g_user.cookies['default_3dmodel']) {
                    var sp = g_user.cookies['default_3dmodel'].split(',');
                    if (sp.length == 2) {
                        cooRace = sp[0];
                        cooSex  = sp[1] - 1;
                    }
                }
                else {
                    var cookie = g_getWowheadCookie('temp_default_3dmodel');
                    if (cookie) {
                        var sp = cookie.split(',');
                        if (sp.length == 2) {
                            cooRace = sp[0];
                            cooSex  = sp[1];
                        }
                    }
                }

                if (isRaceSexValid(cooRace, cooSex)) {
                    race = cooRace;
                    sex  = cooSex;
                }
                else {
                    // Default
                    race = 1; // Human
                    sex  = 0; // Male
                }
            }

            sel    = raceSel2;
            offset = 0;

            raceSel1.hide();
            raceSel2.show();
            sexSel.show();
        }

        if (sex != -1) {
            sexSel[0].selectedIndex = sex;
        }

        if (race != -1 && sex != -1) {
            var foo = function(x) { return x.id; };
            var raceIndex = $WH.in_array(races, race, foo);
            var sexIndex  = $WH.in_array(sexes, sex,  foo);

            if (raceIndex != -1 && sexIndex != -1) {
                model = races[raceIndex].model + sexes[sexIndex].model;
                modelType = 16;

                raceIndex += offset;

                sel[0].selectedIndex = raceIndex;
                sexSel[0].selectedIndex = sexIndex;
            }
        }
    }

    function onHide() {
        if (!optBak.noPound) {
            if (!optBak.fromTag && oldHash && oldHash.indexOf('modelviewer') == -1) {
                location.replace(oldHash);
            }
            else {
                location.replace('#.');
            }
        }

        if (optBak.onHide) {
            optBak.onHide();
        }
    }

    function onShow(dest, first, opt) {
        var
            G,
            E;

        if (!opt.displayAd || g_user.premium) {
            Lightbox.setSize(620, 452);
        }
        else {
            Lightbox.setSize(749, 546);
        }

        if (first) {
            dest.className = 'modelviewer';
            var screen = $WH.ce('div');
            var flashDiv = $WH.ce('div');
            flashDiv.id = 'modelviewer-generic';
            $WH.ae(screen, flashDiv);
            screen.className = 'modelviewer-screen';
            var screenbg = $WH.ce('div');
            screenbg.style.backgroundColor = '#181818';
            screenbg.style.margin = '0';
            $WH.ae(screenbg, screen);
            $WH.ae(dest, screenbg);

            dest = $(dest);

            var leftDiv = $('<div/>', { css: { 'float': 'left' } });

            animDiv = $('<div/>', { 'class': 'modelviewer-animation' });
            var v = $('<var/>', { text: LANG.animation });
            animDiv.append(v);

            var select = $('<select/>', { change: onAnimationChange, mouseenter: onAnimationMouseover });
            select.append($('<option/>', { text: LANG.dialog_mouseovertoload }));
            animDiv.append(select);

            dest.append(animDiv);

            var a1 = $('<a/>', { 'class': 'modelviewer-help', href: '?help=modelviewer', target: '_blank' }),
                a2 = $('<a/>', { 'class': 'modelviewer-close', href: 'javascript:;', click: Lightbox.hide });

            a1.append($('<span/>'));
            a2.append($('<span/>'));

            modelDiv = $('<div/>', { 'class': 'modelviewer-model' });

            var foo = function(a, b) { return $WH.strcmp(a.name, b.name); };
            races.sort(foo);
            sexes.sort(foo);

            raceSel1 = $('<select/>', { change: onSelChange });
            raceSel2 = $('<select/>', { change: onSelChange });
            sexSel   = $('<select/>', { change: onSelChange });

            raceSel1.append($('<option/>'));
            for(var i = 0, len = races.length; i < len; ++i)
            {
                var o = $('<option/>', { val: races[i].id, text: races[i].name });
                raceSel1.append(o);
            }
            for(var i = 0, len = races.length; i < len; ++i)
            {
                var o = $('<option/>', { val: races[i].id, text: races[i].name });
                raceSel2.append(o);
            }

            for(var i = 0, len = sexes.length; i < len; ++i)
            {
                var o = $('<option/>', { val: sexes[i].id, text: sexes[i].name });
                sexSel.append(o);
            }
            sexSel.hide();

            modelDiv.append($('<div/>'));
            modelDiv.append(raceSel1);
            modelDiv.append(raceSel2);
            modelDiv.append(sexSel);

            leftDiv.append(modelDiv);

            var sp = $('<span/>');
            sp.append('<small>Drag to rotate<br />Control (Windows) / Cmd (Mac) + drag to pan</small>');
            leftDiv.append(sp);

            dest.append(leftDiv);
            dest.append(a2);
            dest.append(a1);

            d = $('<div/>', { 'class': 'clear' });
            dest.append(d);
        }

        switch (opt.type) {
            case 1: // NPC
                modelDiv.hide();
                if (opt.humanoid) {
                    modelType = 32; // Humanoid NPC
                }
                else {
                    modelType = 8; // NPC
                }
                model = opt.displayId;
                break;
            case 2: // Object
                modelDiv.hide();
                modelType = 64; // Object
                model = opt.displayId;
                break;
            case 3: // Item
                equipList = [opt.slot, opt.displayId];
                if ($WH.in_array([4, 5, 6, 7, 8, 9, 10, 16, 19, 20], opt.slot) != -1) {
                    initRaceSex(0, opt)
                }
                else {
                    switch (opt.slot) {
                    case 1:
                        modelType = 2; // Helm
                        break;
                    case 3:
                        modelType = 4; // Shoulder
                        break;
                    default:
                        modelType = 1; // Item
                    }

                    model = opt.displayId;

                    initRaceSex(1, opt);
                }
                break;
            case 4: // Item Set
                equipList = opt.equipList;
                initRaceSex(0, opt)
        }

        clear();
        setTimeout(render, 1);

        var trackCode = '';
        if (opt.fromTag)
        {
            trackCode += 'Custom ';
            switch (opt.type)
            {
                case 1: // npc
                    trackCode += 'NPC ' + opt.displayId + (opt.humanoid ? ' humanoid' : ''); break;
                case 2: // object
                    trackCode += 'Object ' + opt.displayId; break;
                case 3: // item
                    trackCode += 'Item ' + opt.displayId + ' Slot ' + (opt.slot | 0); break;
                case 4: // item set
                    trackCode += 'Item set ' + equipList.join('.'); break;
            }
        }
        else
        {
            switch (opt.type)
            {
                case 1: // npc
                    trackCode += 'NPC ' + (opt.typeId ? opt.typeId : ' DisplayID ' + opt.displayId); break;
                case 2: // object
                    trackCode += 'Object ' + opt.typeId; break;
                case 3: // item
                    trackCode += 'Item ' + opt.typeId; break;
                case 4: // item set
                    trackCode += 'Item set ' + equipList.join('.'); break;
            }
        }

        g_trackEvent('Model Viewer', 'Show', g_urlize(trackCode));

        oldHash = location.hash;
    }

    this.checkPound = function() {
        if (location.hash && location.hash.indexOf('#modelviewer') == 0) {
            var parts = location.hash.split(':');
            if (parts.length >= 3) {
                parts.shift(); // - #modelviewer
                var type = parseInt(parts.shift());
                var opt = { type: type };
                switch (type) {
                    case 1: // npc
                        opt.displayId = parseInt(parts.shift());
                        var humanoid = parseInt(parts.shift());
                        if (humanoid == 1) {
                            opt.humanoid = 1;
                        }
                        break;
                    case 2: // object
                        opt.displayId = parseInt(parts.shift());
                        break;
                    case 3: // item
                        opt.displayId = parseInt(parts.shift());
                        opt.slot = parseInt(parts.shift());
                        break;
                    case 4: // item set
                        var list = parts.shift();
                        opt.equipList = list.split(';');
                        break;
                }
                if (opt.displayId || opt.equipList) {
                    ModelViewer.show(opt);
                }

                if (readExtraPound != null) {
                    if (parts.length > 0 && parts[parts.length - 1]) {
                        readExtraPound(parts[parts.length - 1]);
                    }
                }
            }
            else if (readExtraPound != null && parts.length == 2 && parts[1]) {
                readExtraPound(parts[1]);
            }
            else {
                var foo = $WH.ge('view3D-button');
                if (foo) {
                    foo.onclick();
                }
            }
        }
    };

    this.addExtraPound = function(func) {
        readExtraPound = func;
    };

    this.show = function(opt) {
        optBak = opt;

        Lightbox.show('modelviewer', {
            onShow: onShow,
            onHide: onHide
        }, opt);
    };

    DomContentLoaded.addEvent(this.checkPound)
};

// TODO: Create a "Cookies" object

function g_cookiesEnabled() {
    document.cookie = 'enabledTest';
    return (document.cookie.indexOf("enabledTest") != -1) ? true : false;
}

function g_getWowheadCookie(name) {
    if (g_user.id > 0) {
        return g_user.cookies[name]; // no point checking if it exists, as undefined tests as false anyways
    }
    else {
        return $WH.gc(name); // plus gc does the same thing..
    }
}

function g_setWowheadCookie(name, data, browser) {
    var temp = name.substr(0, 5) == 'temp_';
    if (!browser && g_user.id > 0 && !temp) {
        new Ajax('?cookie=' + name + '&' + name + '=' + $WH.urlencode(data), {
            method: 'get',
            onSuccess: function(xhr) {
                if (xhr.responseText == 0) {
                    g_user.cookies[name] = data;
                }
            }
        });
    }
    else if (browser || g_user.id == 0) {
        $WH.sc(name, 14, data, null, location.hostname);
    }
}

// Display a warning if a user attempts to leave the page and he has started writing a message
$(document).ready(function() {
    g_setupChangeWarning($("form[name=addcomment]"), [$("textarea[name=commentbody]")], LANG.message_startedpost);
});

var ContactTool = new function() {
    this.general    = 0;
    this.comment    = 1;
    this.post       = 2;
    this.screenshot = 3;
    this.character  = 4;
    this.video      = 5;
    this.guide      = 6;

    var _dialog;

    var contexts = {
        0: [ // general
            [1, true], // General feedback
            [2, true], // Bug report
            [8, true], // Article misinformation
            [3, true], // Typo/mistranslation
            [4, true], // Advertise with us
            [5, true], // Partnership opportunities
            [6, true], // Press inquiry
            [7, true]  // Other
        ],
        1: [ // comment
            [15, function(post) { return ((post.roles & U_GROUP_MODERATOR) == 0); }], // Advertising
            [16, true],                                                               // Inaccurate
            [17, true],                                                               // Out of date
            [18, function(post) { return ((post.roles & U_GROUP_MODERATOR) == 0); }], // Spam
            [19, function(post) { return ((post.roles & U_GROUP_MODERATOR) == 0); }], // Vulgar/inappropriate
            [20, function(post) { return ((post.roles & U_GROUP_MODERATOR) == 0); }]  // Other
        ],
        2: [ // forum post
            [30, function(post) { return (g_users && g_users[post.user] && (g_users[post.user].roles & U_GROUP_MODERATOR) == 0); }],                                                                            // Advertising
            [37, function(post) { return (g_users && g_users[post.user] && (g_users[post.user].roles & U_GROUP_MODERATOR) == 0 && (post.roles & U_GROUP_MODERATOR) == 0 && g_users[post.user].avatar == 2); }], // Avatar
            [31, true],                                                                                                                                                                                         // Inaccurate
            [32, true],                                                                                                                                                                                         // Out of date
            [33, function(post) { return (g_users && g_users[post.user] && (g_users[post.user].roles & U_GROUP_MODERATOR) == 0); }],                                                                            // Spam
            [34, function(post) { return (g_users && g_users[post.user] && (g_users[post.user].roles & U_GROUP_MODERATOR) == 0 && post.op && !post.sticky); }],                                                 // Sticky request
            [35, function(post) { return (g_users && g_users[post.user] && (g_users[post.user].roles & U_GROUP_MODERATOR) == 0); }],                                                                            // Vulgar/inappropriate
            [36, function(post) { return (g_users && g_users[post.user] && (g_users[post.user].roles & U_GROUP_MODERATOR) == 0);}]                                                                              // Other
        ],
        3: [ // screenshot
            [45, true], // Inaccurate,
            [46, true], // Out of date,
            [47, function(screen) { return (g_users && g_users[screen.user] && (g_users[screen.user].roles & U_GROUP_MODERATOR) == 0); }], // Vulgar/inappropriate
            [48, function(screen) { return (g_users && g_users[screen.user] && (g_users[screen.user].roles & U_GROUP_MODERATOR) == 0); }]  // Other
        ],
        4: [ // character
            [60, true], // Inaccurate completion data
            [61, true]  // Other
        ],
        5: [ // video
            [45, true], // Inaccurate,
            [46, true], // Out of date,
            [47, function(video) { return (g_users && g_users[video.user] && (g_users[video.user].roles & U_GROUP_MODERATOR) == 0); }], // Vulgar/inappropriate
            [48, function(video) { return (g_users && g_users[video.user] && (g_users[video.user].roles & U_GROUP_MODERATOR) == 0); }]  // Other
        ],
        6: [ // guide
            [45, true], // Inaccurate,
            [46, true], // Out of date,
            [48, true]  // Other
        ]
    };

    var errors = {
        1 : LANG.ct_resp_error1,
        2 : LANG.ct_resp_error2,
        3 : LANG.ct_resp_error3,
        7 : LANG.ct_resp_error7
    };

    var oldHash = null;

    this.displayError = function(field, message) {
        alert(message);
    };

    this.onShow = function() {
        if (location.hash && location.hash != '#contact') {
            oldHash = location.hash;
        }
        if (this.data.mode == 0) {
            location.replace('#contact');
        }
    };

    this.onHide = function() {
        if (oldHash && (oldHash.indexOf('screenshots:') == -1 || oldHash.indexOf('videos:') == -1)) {
            location.replace(oldHash);
        }
        else {
            location.replace('#.');
        }
    };

    this.onSubmit = function(data, button, form) {
        if (data.submitting) {
            return false;
        }

        for (var i = 0; i < form.elements.length; ++i) {
            form.elements[i].disabled = true;
        }

        var params = [
            'contact=1',
            'mode='    + $WH.urlencode(data.mode),
            'reason='  + $WH.urlencode(data.reason),
            'desc='    + $WH.urlencode(data.description),
            'ua='      + $WH.urlencode(navigator.userAgent),
            'appname=' + $WH.urlencode(navigator.appName),
            'page='    + $WH.urlencode(data.currenturl)
        ];

        if (data.mode == 0) { // contact us
            if (data.relatedurl) {
                params.push('relatedurl=' + $WH.urlencode(data.relatedurl));
            }
            if (data.email) {
                params.push('email=' + $WH.urlencode(data.email));
            }
        }
        else if (data.mode == 1) { // comment
            params.push('id=' + $WH.urlencode(data.comment.id));
        }
        else if (data.mode == 2) { // forum post
            params.push('id=' + $WH.urlencode(data.post.id));
        }
        else if (data.mode == 3) { // screenshot
            params.push('id=' + $WH.urlencode(data.screenshot.id));
        }
        else if (data.mode == 4) { // character
            params.push('id=' + $WH.urlencode(data.profile.source));
        }
        else if (data.mode == 5) { // video
            params.push('id=' + $WH.urlencode(data.video.id));
        }
        else if (data.mode == 6) { // guide
            params.push('id=' + $WH.urlencode(data.guide.id));
        }

        data.submitting = true;
        var url = '?contactus';
        new Ajax(url, {
            method: 'POST',
            params: params.join('&'),
            onSuccess: function(xhr, opt) {
                var resp = xhr.responseText;
                if (resp == 0) {
                    if (g_user.name)
                        alert($WH.sprintf(LANG.ct_dialog_thanks_user, g_user.name));
                    else
                        alert(LANG.ct_dialog_thanks);

                    Lightbox.hide();
                }
                else {
                    if (errors[resp])
                        alert(errors[resp]);
                    else
                        alert('Error: ' + resp);
                }
            },
            onFailure: function(xhr, opt) {
                alert('Failure submitting contact request: ' + xhr.statusText);
            },
            onComplete: function(xhr, opt) {
                for (var i = 0; i < form.elements.length; ++i)
                    form.elements[i].disabled = false;

                data.submitting = false;
            }
        });
        return false;
    };

    this.show = function(opt) {
        if (!opt) {
            opt = {};
        }
        var data = { mode: 0 };
        $WH.cO(data, opt);
        data.reasons = contexts[data.mode];
        if (location.href.indexOf('#contact') != -1) {
            data.currenturl = location.href.substr(0, location.href.indexOf('#contact'));
        }
        else {
            data.currenturl = location.href;
        }
        var form = 'contactus';
        if (data.mode != 0) {
            form = 'reportform';
        }

        if (!_dialog) {
            this.init();
        }

        _dialog.show(form, {
            data: data,
            onShow: this.onShow,
            onHide: this.onHide,
            onSubmit: this.onSubmit
        })
    };

    this.checkPound = function() {
        if (location.hash && location.hash == '#contact') {
            ContactTool.show();
        }
    };

    var dialog_contacttitle = LANG.ct_dialog_contactwowhead;

    this.init = function() {
        _dialog = new Dialog();

        Dialog.templates.contactus = {
            title: dialog_contacttitle,
            width: 550,
            buttons: [['okay', LANG.ok], ['cancel', LANG.cancel]],

            fields: [
                {
                    id: 'reason',
                    type: 'select',
                    label: LANG.ct_dialog_reason,
                    required: 1,
                    options: [],
                    compute: function(field, value, form, td) {
                        $WH.ee(field);

                        for (var i = 0; i < this.data.reasons.length; ++i) {
                            var id = this.data.reasons[i][0];
                            var check = this.data.reasons[i][1];
                            var valid = false;
                            if (typeof check == 'function') {
                                valid = check(this.extra);
                            }
                            else {
                                valid = check;
                            }

                            if (!valid) {
                                continue;
                            }

                            var o = $WH.ce('option');
                            o.value = id;
                            if (value && value == id) {
                                o.selected = true;
                            }
                            $WH.ae(o, $WH.ct(g_contact_reasons[id]));
                            $WH.ae(field, o);
                        }

                        field.onchange = function() {
                            if (this.value == 1 || this.value == 2 || this.value == 3) {
                                form.currenturl.parentNode.parentNode.style.display = '';
                                form.relatedurl.parentNode.parentNode.style.display = '';
                            }
                            else {
                                form.currenturl.parentNode.parentNode.style.display = 'none';
                                form.relatedurl.parentNode.parentNode.style.display = 'none';
                            }
                        }.bind(field);

                        td.style.width = '98%';
                    },
                    validate: function(newValue, data, form) {
                        var error = '';
                        if (!newValue || newValue.length == 0) {
                            error = LANG.ct_dialog_error_reason;
                        }

                        if (error == '') {
                            return true;
                        }

                        ContactTool.displayError(form.reason, error);
                        form.reason.focus();
                        return false;
                    }
                },
                {
                    id: 'currenturl',
                    type: 'text',
                    disabled: true,
                    label: LANG.ct_dialog_currenturl,
                    size: 40
                },
                {
                    id: 'relatedurl',
                    type: 'text',
                    label: LANG.ct_dialog_relatedurl,
                    caption: LANG.ct_dialog_optional,
                    size: 40,
                    validate: function(newValue, data, form) {
                        var error = '';
                        var urlRe = /^(http(s?)\:\/\/|\/)?([\w]+:\w+@)?([a-zA-Z]{1}([\w\-]+\.)+([\w]{2,5}))(:[\d]{1,5})?((\/?\w+\/)+|\/?)(\w+\.[\w]{3,4})?((\?\w+=\w+)?(&\w+=\w+)*)?/;
                        newValue = newValue.trim();
                        if (newValue.length >= 250) {
                            error = LANG.ct_dialog_error_relatedurl;
                        }
                        else if (newValue.length > 0 && !urlRe.test(newValue)) {
                            error = LANG.ct_dialog_error_invalidurl;
                        }

                        if (error == '') {
                            return true;
                        }

                        ContactTool.displayError(form.relatedurl, error);
                        form.relatedurl.focus();
                        return false;
                    }
                },
                {
                    id: 'email',
                    type: 'text',
                    label: LANG.ct_dialog_email,
                    caption: LANG.ct_dialog_email_caption,
                    compute: function(field, value, form, td, tr) {
                        if (g_user.email) {
                            this.data.email = g_user.email;
                            tr.style.display = 'none';
                        }
                        else {
                            var func = function() {
                                $WH.ge('contact-emailwarn').style.display = g_isEmailValid($WH.ge(form.email).value) ? 'none' : '';
                                Lightbox.reveal();
                            };

                            $WH.ge(field).onkeyup = func;
                            $WH.ge(field).onblur = func;
                        }
                    },
                    validate: function(newValue, data, form) {
                        var error = '';
                        newValue = newValue.trim();
                        if (newValue.length >= 100) {
                            error = LANG.ct_dialog_error_emaillen;
                        }
                        else if (newValue.length > 0 && !g_isEmailValid(newValue)) {
                            error = LANG.ct_dialog_error_email;
                        }

                        if (error == '') {
                            return true;
                        }

                        ContactTool.displayError(form.email, error);
                        form.email.focus();
                        return false;
                    }
                },
                {
                    id: 'description',
                    type: 'textarea',
                    caption: LANG.ct_dialog_desc_caption,
                    width: '98%',
                    required: 1,
                    size: [10, 30],
                    validate: function(newValue, data, form) {
                        var error = '';
                        newValue = newValue.trim();
                        if (newValue.length == 0 || newValue.length > 10000) {
                            error = LANG.ct_dialog_error_desc;
                        }

                        if (error == '') {
                            return true;
                        }

                        ContactTool.displayError(form.description, error);
                        form.description.focus();
                        return false;
                    }
                },
                {
                    id: 'noemailwarning',
                    type: 'caption',
                    compute: function(field, value, form, td) {
                        var td = $WH.ge(td);
                        td.innerHTML = '<span id="contact-emailwarn" class="q10"' + (g_user.email ? ' style="display: none"' : '') + '>' + LANG.ct_dialog_noemailwarning + '</span>';
                        td.style.whiteSpace = 'normal';
                        td.style.padding = '0 4px';
                    }
                }
            ],

            onInit: function(form) { },

            onShow: function(form) {
                if (this.data.focus && form[this.data.focus]) {
                    setTimeout(g_setCaretPosition.bind(null, form[this.data.focus], form[this.data.focus].value.length), 100);
                }
                else if (form['reason'] && !form.reason.value) {
                    setTimeout($WH.bindfunc(form.reason.focus, form.reason), 10);
                }
                else if (form['relatedurl'] && !form.relatedurl.value) {
                    setTimeout($WH.bindfunc(form.relatedurl.focus, form.relatedurl), 10);
                }
                else if (form['email'] && !form.email.value) {
                    setTimeout($WH.bindfunc(form.email.focus, form.email), 10);
                }
                else if (form['description'] && !form.description.value) {
                    setTimeout($WH.bindfunc(form.description.focus, form.description), 10);
                }

                setTimeout(Lightbox.reveal, 250);
            }
        };

        Dialog.templates.reportform = {
            title: LANG.ct_dialog_report,
            width: 550,
            // height: 360,
            buttons: [['okay', LANG.ok], ['cancel', LANG.cancel]],
            fields: [
                {
                    id: 'reason',
                    type: 'select',
                    label: LANG.ct_dialog_reason,
                    options: [],
                    compute: function(field, value, form, td) {
                        switch (this.data.mode) {
                            case 1: // comment
                                form.firstChild.innerHTML = $WH.sprintf(LANG.ct_dialog_reportcomment, '<a href="?user=' + this.data.comment.user + '">' + this.data.comment.user + '</a>');
                                break;
                            case 2: // forum post
                                var rep = '<a href="?user=' + this.data.post.user + '">' + this.data.post.user + '</a>';
                                if (this.data.post.op) {
                                    form.firstChild.innerHTML = $WH.sprintf(LANG.ct_dialog_reporttopic, rep);
                                }
                                else {
                                    form.firstChild.innerHTML = $WH.sprintf(LANG.ct_dialog_reportpost, rep);
                                }
                                break;
                            case 3: // screenshot
                                form.firstChild.innerHTML = $WH.sprintf(LANG.ct_dialog_reportscreen, '<a href="?user=' + this.data.screenshot.user + '">' + this.data.screenshot.user + '</a>');
                                break;
                            case 4: // character
                                $WH.ee(form.firstChild);
                                $WH.ae(form.firstChild, $WH.ct(LANG.ct_dialog_reportchar));
                                break;
                            case 5: // video
                                form.firstChild.innerHTML = $WH.sprintf(LANG.ct_dialog_reportvideo, '<a href="?user=' + this.data.video.user + '">' + this.data.video.user + '</a>');
                                break;
                            case 6: // guide
                                form.firstChild.innerHTML = 'Report guide';
                                break;
                        }
                        form.firstChild.setAttribute('style', '');

                        $WH.ee(field);

                        var extra;
                        if (this.data.mode == 1) {
                            extra = this.data.comment;
                        }
                        else if (this.data.mode == 2) {
                            extra = this.data.post;
                        }
                        else if (this.data.mode == 3) {
                            extra = this.data.screenshot;
                        }
                        else if (this.data.mode == 4) {
                            extra = this.data.profile;
                        }
                        else if (this.data.mode == 5) {
                            extra = this.data.video;
                        }
                        else if (this.data.mode == 6) {
                            extra = this.data.guide;
                        }

                        $WH.ae(field, $WH.ce('option', { selected: (!value), value: -1 }));

                        for (var i = 0; i < this.data.reasons.length; ++i) {
                            var id = this.data.reasons[i][0];
                            var check = this.data.reasons[i][1];
                            var valid = false;
                            if (typeof check == 'function') {
                                valid = check(extra);
                            }
                            else {
                                valid = check;
                            }

                            if (!valid) {
                                continue;
                            }

                            var o = $WH.ce('option');
                            o.value = id;
                            if (value && value == id) {
                                o.selected = true;
                            }

                            $WH.ae(o, $WH.ct(g_contact_reasons[id]));
                            $WH.ae(field, o);
                        }

                        td.style.width = '98%';
                    },
                    validate: function(newValue, data, form) {
                        var error = '';
                        if (!newValue || newValue == -1 || newValue.length == 0) {
                            error = LANG.ct_dialog_error_reason;
                        }

                        if (error == '') {
                            return true;
                        }

                        ContactTool.displayError(form.reason, error);
                        form.reason.focus();
                        return false;
                    }
                },
                {
                    id: 'description',
                    type: 'textarea',
                    caption: LANG.ct_dialog_desc_caption,
                    width: '98%',
                    required: 1,
                    size: [10, 30],
                    validate: function(newValue, data, form) {
                        var error = '';
                        newValue = newValue.trim();
                        if (newValue.length == 0 || newValue.length > 10000) {
                            error = LANG.ct_dialog_error_desc;
                        }

                        if (error == '') {
                            return true;
                        }

                        ContactTool.displayError(form.description, error);
                        form.description.focus();
                        return false;
                    }
                }
            ],

            onInit: function(form) {},

            onShow: function(form) {
                /* Work-around for IE7 */
                var reason = $WH.gE(form, 'select')[0];
                var description = $WH.gE(form, 'textarea')[0];

                if (this.data.focus && form[this.data.focus]) {
                    setTimeout(g_setCaretPosition.bind(null, form[this.data.focus], form[this.data.focus].value.length), 100);
                }
                else if (!reason.value) {
                    setTimeout($WH.bindfunc(reason.focus, reason), 10);
                }
                else if (!description.value) {
                    setTimeout($WH.bindfunc(description.focus, description), 10);
                }
            }
        }
    };

    DomContentLoaded.addEvent(this.checkPound);
};

function Line(x1, y1, x2, y2, type) {
    var left   = Math.min(x1, x2),
        right  = Math.max(x1, x2),
        top    = Math.min(y1, y2),
        bottom = Math.max(y1, y2),

        width  = (right - left),
        height = (bottom - top),
        length = Math.sqrt(Math.pow(width, 2) + Math.pow(height, 2)),

        radian   = Math.atan2(height, width),
        sinTheta = Math.sin(radian),
        cosTheta = Math.cos(radian);

    var $line = $WH.ce('span');
    $line.className = 'line';
    $line.style.top    = top.toFixed(2) + 'px';
    $line.style.left   = left.toFixed(2) + 'px';
    $line.style.width  = width.toFixed(2) + 'px';
    $line.style.height = height.toFixed(2) + 'px';

    var v = $WH.ce('var');
    v.style.width = length.toFixed(2) + 'px';
    v.style.OTransform = 'rotate(' + radian + 'rad)';
    v.style.MozTransform = 'rotate(' + radian + 'rad)';
    v.style.webkitTransform = 'rotate(' + radian + 'rad)';
    v.style.filter = "progid:DXImageTransform.Microsoft.Matrix(sizingMethod='auto expand', M11=" + cosTheta + ', M12=' + (-1 * sinTheta) + ', M21=' + sinTheta + ', M22=' + cosTheta + ')';
    $WH.ae($line, v);

    if (!(x1 == left && y1 == top) && !(x2 == left && y2 == top)) {
        $line.className += ' flipped';
    }

    if (type != null) {
        $line.className += ' line-' + type;
    }

    return $line;
}

var Links = new function() {
    var dialog  = null;
    var oldHash = null;

    var validArmoryTypes = {
        item: 1
    };

    var extraTypes = {
        29: 'icondb'
    };

    this.onShow = function() {
        if (location.hash && location.hash != '#links') {
            oldHash = location.hash;
        }

        location.replace('#links');
    }

    this.onHide = function() {
        if (oldHash && (oldHash.indexOf('screenshots:') == -1 || oldHash.indexOf('videos:') == -1)) {
            location.replace(oldHash);
        }
        else {
            location.replace('#.');
        }
    }

    this.show = function(opt) {
        if (!opt || !opt.type || !opt.typeId) {
            return;
        }

        var type = g_types[opt.type];

        if (!dialog) {
            this.init();
        }

        if (validArmoryTypes[type] && Dialog.templates.links.fields[1].id != 'armoryurl') {
            Dialog.templates.links.fields.splice(1, 0, {
                id: 'armoryurl',
                type: 'text',
                label: 'Armory URL',
                size: 40
            });
        }

        var link = '';
        if (opt.linkColor && opt.linkId && opt.linkName) {
            link = g_getIngameLink(opt.linkColor, opt.linkId, opt.linkName);
        }

        if (opt.sound)
            link = '/script PlaySoundFile("' + opt.sound + '", "master")';
            // link = '/script PlaySoundKitID(' + opt.sound + ')'; Aowow: not available in 3.3.5

        if (link) {
            if (Dialog.templates.links.fields[Dialog.templates.links.fields.length - 2].id != 'ingamelink') {
                Dialog.templates.links.fields.splice(Dialog.templates.links.fields.length - 1, 0, {
                    id: 'ingamelink',
                    type: 'text',
                    label: 'Ingame Link',
                    size: 40
                });
            }
        }

        var data = {
            'wowheadurl': g_host +'/?' + type + '=' + opt.typeId,
            'armoryurl': 'http://us.battle.net/wow/en/' + type + '/' + opt.typeId,
            'ingamelink': link,
            'markuptag': '[' + (extraTypes[opt.type] || type) + '=' + opt.typeId + ']'
        };

        dialog.show('links', {
            data: data,
            onShow: this.onShow,
            onHide: this.onHide,
            onSubmit: function() {
                return false;
            }
        });
    }

    this.checkPound = function() {
        if (location.hash && location.hash == '#links') {
            $WH.ge('open-links-button').click();
        }
    }

    this.init = function() {
        dialog = new Dialog();

        Dialog.templates.links = {
            title: LANG.pr_menu_links || 'Links',
            width: 425,
            buttons: [['cancel', LANG.close]],

            fields:
                [
                    {
                        id:    'wowheadurl',
                        type:  'text',
                        label: 'Aowow URL',
                        size:  40
                    },
                    {
                        id:    'markuptag',
                        type:  'text',
                        label: 'Markup Tag',
                        size:  40
                    }
                ],

            onInit: function(form) {

            },

            onShow: function(form) {
                setTimeout(function() {
                    $(form.ingamelink).select();
                }, 50);
                setTimeout(Lightbox.reveal, 100);
            }
        };
    };

    DomContentLoaded.addEvent(this.checkPound)
};

var Announcement = function(opt) {
    if (!opt) {
        opt = {};
    }

    $WH.cO(this, opt);

    if (this.parent) {
        this.parentDiv = $WH.ge(this.parent);
    }
    else {
        return;
    }

    if (g_user.id > 0 && (!g_cookiesEnabled() || g_getWowheadCookie('announcement-' + this.id) == 'closed')) {
        return;
    }

    this.initialize();
};

Announcement.prototype = {
    initialize: function() {
        // this.parentDiv.style.display = 'none';
        this.parentDiv.style.opacity = '0';
        // this.parentDiv.style.height = '0px';
        /* replaced with..*/

        if (this.mode === undefined || this.mode == 1)
            this.parentDiv.className = 'announcement announcement-contenttop';
        else
            this.parentDiv.className = 'announcement announcement-pagetop';

        var div = this.innerDiv = $WH.ce('div');
        div.className = 'announcement-inner text';
        this.setStyle(this.style);

        var a = null;
        var id = parseInt(this.id);

        if (g_user && (g_user.roles & (U_GROUP_ADMIN|U_GROUP_BUREAU)) > 0 && Math.abs(id) > 0) {
            if (id < 0) {
                a = $WH.ce('a');
                a.style.cssFloat = a.style.styleFloat = 'right';
                a.href = '?admin=announcements&id=' + Math.abs(id) + '&status=2';
                a.onclick = function() {
                    return confirm('Are you sure you want to delete ' + this.name + '?');
                };
                $WH.ae(a, $WH.ct('Delete'));
                var small = $WH.ce('small');
                $WH.ae(small, a);
                $WH.ae(div, small);

                a = $WH.ce('a');
                a.style.cssFloat = a.style.styleFloat = 'right';
                a.style.marginRight = '10px';
                a.href = '?admin=announcements&id=' + Math.abs(id) + '&status=' + (this.status == 1 ? 0 : 1);
                a.onclick = function() {
                    return confirm('Are you sure you want to delete ' + this.name + '?');
                };
                $WH.ae(a, $WH.ct((this.status == 1 ? 'Disable' : 'Enable')));
                var small = $WH.ce('small');
                $WH.ae(small, a);
                $WH.ae(div, small);
            }

            a = $WH.ce('a');
            a.style.cssFloat = a.style.styleFloat = 'right';
            a.style.marginRight = '22px';
            a.href = '?admin=announcements&id=' + Math.abs(id) + '&edit';
            $WH.ae(a, $WH.ct('Edit announcement'));
            var small = $WH.ce('small');
            $WH.ae(small, a);
            $WH.ae(div, small);
        }

        var markupDiv = $WH.ce('div');
        markupDiv.id = this.parent + '-markup';
        $WH.ae(div, markupDiv);

        if (id >= 0) {
            a = $WH.ce('a');

            a.id = 'closeannouncement';
            a.href = 'javascript:;';
            a.className = 'announcement-close';
            if (this.nocookie) {
                a.onclick = this.hide.bind(this);
            }
            else {
                a.onclick = this.markRead.bind(this);
            }
            $WH.ae(div, a);
            g_addTooltip(a, LANG.close);
        }

        $WH.ae(div, $WH.ce('div', { style: { clear: 'both' } }));

        $WH.ae(this.parentDiv, div);

        this.setText(this.text);

        setTimeout(this.show.bind(this), 500); // Delay to avoid visual lag
    },

    show: function() {
        // $(this.parentDiv).animate({
            // opacity: 'show',
            // height: 'show'
        // },{
            // duration: 333
        // });
        // this.parentDiv.style.display = 'block';

        // todo: iron out the quirks
        this.parentDiv.style.opacity = '100';
        this.parentDiv.style.height = (this.parentDiv.offsetHeight + 10) + 'px'; // + margin-bottom of child
        g_trackEvent('Announcements', 'Show', '' + this.name);
    },

    hide: function() {
        // $(this.parentDiv).animate({
            // opacity: 'hide',
            // height: 'hide'
        // },{
            // duration: 200
        // });
        // this.parentDiv.style.display = 'none';

        // todo: iron out the quirks
        this.parentDiv.style.opacity = '0';
        this.parentDiv.style.height = '0px';
        setTimeout(function() {
            this.parentDiv.style.display = 'none';
        }.bind(this), 400);
    },

    markRead: function() {
        g_trackEvent('Announcements', 'Close', '' + this.name);
        g_setWowheadCookie('announcement-' + this.id, 'closed');
        // $WH.sc('announcement-' + this.id, 20, 'closed', "/", location.hostname);
        this.hide();
    },

    setStyle: function(style) {
        this.style = style;
        this.innerDiv.setAttribute('style', style);
    },

    setText: function(text) {
        this.text = text;
        Markup.printHtml(this.text, this.parent + '-markup');
        g_addAnalyticsToNode($WH.ge(this.parent + '-markup'), {
            'category': 'Announcements',
            'actions': {
                'Follow link': function(node) { return true; }
            }
        }, this.id);
    }
};

var g_audiocontrols = {
    __windowloaded: false,
};
var g_audioplaylist = {};

if (!window.JSON) {
    window.JSON = {
        parse: function (sJSON) {
            return eval("(" + sJSON + ")");
        },

        stringify: function (obj) {
            if (obj instanceof Object) {
                var str = '';
                if (obj.constructor === Array) {
                    for (var i = 0; i < obj.length; str += this.stringify(obj[i]) + ',', i++) {}
                    return '[' + str.substr(0, str.length - 1) + ']';
                }
                if (obj.toString !== Object.prototype.toString)
                    return '"' + obj.toString().replace(/"/g, '\\$&') + '"';

                for (var e in obj)
                    str += '"' + e.replace(/"/g, '\\$&') + '":' + this.stringify(obj[e]) + ',';

                return '{' + str.substr(0, str.length - 1) + '}';
            }

            return typeof obj === 'string' ? '"' + obj.replace(/"/g, '\\$&') + '"' : String(obj);
        }
    }
}

AudioControls = function () {
    var fileIdx    = -1;
    var canPlay    = false;
    var looping    = false;
    var fullPlayer = false;
    var autoStart  = false;
    var controls   = {};
    var playlist   = [];
    var url        = '';

    function updatePlayer(_self, itr, doPlay) {
        var elAudio = $WH.ce('audio');
        elAudio.preload = 'none';
        elAudio.controls = 'true';
        $(elAudio).click(function (s) { s.stopPropagation() });
        elAudio.style.marginTop = '5px';
        controls.audio.parentNode.replaceChild(elAudio, controls.audio);
        controls.audio = elAudio;
        $WH.aE(controls.audio, 'ended', setNextTrack.bind(_self));

        if (doPlay) {
            elAudio.preload = 'auto';
            autoStart = true;
            $WH.aE(controls.audio, 'canplaythrough', autoplay.bind(this));
        }

        if (!canPlay)
            controls.table.style.visibility = 'visible';

        var file;
        do {
            fileIdx += itr;
            if (fileIdx > playlist.length - 1) {
                fileIdx = 0;
                if (!canPlay) {
                    var div = $WH.ce('div');
                    // div.className = 'minibox'; Aowow custom
                    div.className = 'minibox minibox-left';
                    $WH.st(div, $WH.sprintf(LANG.message_browsernoaudio, file.type));
                    controls.table.parentNode.replaceChild(div, controls.table);
                    return
                }
            }

            if (fileIdx < 0)
                fileIdx = playlist.length - 1;

            file = playlist[fileIdx];
        }
        while (controls.audio.canPlayType(file.type) == '');

        var elSource = $WH.ce('source');
        elSource.src = file.url;
        elSource.type = file.type;
        $WH.ae(controls.audio, elSource);
        if (controls.hasOwnProperty('title')) {
            if (url) {
                $WH.ee(controls.title);
                var a = $WH.ce('a');
                a.href = url;
                $WH.st(a, '"' + file.title + '"');
                $WH.ae(controls.title, a);
            }
            else
                $WH.st(controls.title, '"' + file.title + '"');
        }

        if (controls.hasOwnProperty('trackdisplay'))
            $WH.st(controls.trackdisplay, '' + (fileIdx + 1) + ' / ' + playlist.length);

        if (!canPlay) {
            canPlay = true;
            for (var i = fileIdx + 1; i <= playlist.length - 1; i++) {
                if (controls.audio.canPlayType(playlist[i].type)) {
                    $(controls.controlsdiv).children('a').removeClass('button-red-disabled');
                    break;
                }
            }
        }

        if (controls.hasOwnProperty('addbutton')) {
            $(controls.addbutton).removeClass('button-red-disabled');
            // $WH.st(controls.addbutton, LANG.add);           Aowow: doesnt work with RedButtons
            RedButton.setText(controls.addbutton, LANG.add);
        }
    }

    function autoplay() {
        if (!autoStart)
            return;

        autoStart = false;
        controls.audio.play();
    }

    this.init = function (files, parent, opt) {
        if (!$WH.is_array(files))
            return;

        if (files.length == 0)
            return;

        if ((parent.id == '') || g_audiocontrols.hasOwnProperty(parent.id)) {
            var i = 0;
            while (g_audiocontrols.hasOwnProperty('auto-audiocontrols-' + (++i))) {}
            parent.id = 'auto-audiocontrols-' + i;
        }

        g_audiocontrols[parent.id] = this;

        if (typeof opt == 'undefined')
            opt = {};

        looping = !!opt.loop;
        if (opt.hasOwnProperty('url'))
            url = opt.url;

        playlist = files;
        controls.div = parent;

        if (!opt.listview) {
            var tbl = $WH.ce('table', { className: 'audio-controls' });
            controls.table = tbl;
            controls.table.style.visibility = 'hidden';
            $WH.ae(controls.div, tbl);

            var tr = $WH.ce('tr');
            $WH.ae(tbl, tr);

            var td = $WH.ce('td');
            $WH.ae(tr, td);

            controls.audio = $WH.ce('div');
            $WH.ae(td, controls.audio);

            controls.title = $WH.ce('div', { className: 'audio-controls-title' });
            $WH.ae(td, controls.title);

            controls.controlsdiv = $WH.ce('div', { className: 'audio-controls-pagination' });
            $WH.ae(td, controls.controlsdiv);

            var prevBtn = createButton(LANG.previous, true);
            $WH.ae(controls.controlsdiv, prevBtn);
            $WH.aE(prevBtn, 'click', this.btnPrevTrack.bind(this));


            controls.trackdisplay = $WH.ce('div', { className: 'audio-controls-pagination-track' });
            $WH.ae(controls.controlsdiv, controls.trackdisplay);

            var nextBtn = createButton(LANG.next, true);
            $WH.ae(controls.controlsdiv, nextBtn);
            $WH.aE(nextBtn, 'click', this.btnNextTrack.bind(this))
        }
        else {
            fullPlayer = true;
            var div = $WH.ce('div');
            controls.table = div;
            $WH.ae(controls.div, div);

            controls.audio = $WH.ce('div');
            $WH.ae(div, controls.audio);

            controls.trackdisplay = opt.trackdisplay;
            controls.controlsdiv = $WH.ce('span');
            $WH.ae(div, controls.controlsdiv);
        }

        if (g_audioplaylist.isEnabled() && !opt.fromplaylist) {
            var addBtn = createButton(LANG.add);
            $WH.ae(controls.controlsdiv, addBtn);
            $WH.aE(addBtn, 'click', this.btnAddToPlaylist.bind(this, addBtn));
            controls.addbutton = addBtn;
            if (fullPlayer)
                addBtn.style.verticalAlign = '50%';
        }

        if (g_audiocontrols.__windowloaded)
            this.btnNextTrack();
    };

    function setNextTrack() {
        updatePlayer(this, 1, (looping || (fileIdx < (playlist.length - 1))));
    }

    this.btnNextTrack = function () {
        updatePlayer(this, 1, (canPlay && (controls.audio.readyState > 1) && (!controls.audio.paused)));
    };

    this.btnPrevTrack = function () {
        updatePlayer(this, -1, (canPlay && (controls.audio.readyState > 1) && (!controls.audio.paused)));
    };

    this.btnAddToPlaylist = function (_self) {
        if (fullPlayer) {
            for (var i = 0; i < playlist.length; i++)
                g_audioplaylist.addSound(playlist[i]);
        }
        else
            g_audioplaylist.addSound(playlist[fileIdx]);

        _self.className += ' button-red-disabled';
        // $WH.st(_self, LANG.added);                       // Aowow doesn't work with RedButtons
        RedButton.setText(_self, LANG.added);
    };

    this.isPlaying = function () {
        return !controls.audio.paused;
    };

    this.removeSelf = function () {
        controls.table.parentNode.removeChild(controls.table);
        delete g_audiocontrols[controls.div];
    };

    function createButton(text, disabled) {
        return $WH.g_createButton(text, null, {
            disabled: disabled,
            // 'float': false,                              Aowow - adapted style
            // style: 'margin:0 12px; display:inline-block'
            style: 'margin:0 12px; display:inline-block; float:inherit; '
        });
    }
};

$WH.aE(window, 'load', function () {
    g_audiocontrols.__windowloaded = true;
    for (var i in g_audiocontrols)
        if (i.substr(0, 2) != '__')
            g_audiocontrols[i].btnNextTrack();
});

AudioPlaylist = function () {
    var enabled  = false;
    var playlist = [];
    var player, container;

    this.init = function () {
        if (!$WH.localStorage.isSupported())
            return;

        enabled = true;

        var tracks;
        if (tracks = $WH.localStorage.get('AudioPlaylist'))
            playlist = JSON.parse(tracks);
    };

    this.savePlaylist = function () {
        if (!enabled)
            return false;

        $WH.localStorage.set('AudioPlaylist', JSON.stringify(playlist));
    };

    this.isEnabled = function () {
        return enabled;
    };

    this.addSound = function (track) {
        if (!enabled)
            return false;

        this.init();
        playlist.push(track);
        this.savePlaylist();
    };

    this.deleteSound = function (idx) {
        if (idx < 0)
            playlist = [];
        else
            playlist.splice(idx, 1);

        this.savePlaylist();

        if (!player.isPlaying()) {
            player.removeSelf();
            this.setAudioControls(container);
        }

        if (playlist.length == 0)
            $WH.Tooltip.hide();
    };

    this.getList = function () {
        var buf = [];
        for (var i = 0; i < playlist.length; i++)
            buf.push(playlist[i].title);

        return buf;
    };

    this.setAudioControls = function (parent) {
        if (!enabled)
            return false;

        container = parent;
        player = new AudioControls();
        player.init(playlist, container, { loop: true, fromplaylist: true });
    };
};

g_audioplaylist = (new AudioPlaylist);
g_audioplaylist.init();

$WH.aE(window, 'load', function () {
    if (!(window.JSON && $WH.localStorage.isSupported())) {
        return;
    }

    var lv = $WH.localStorage.get('lvBrowse');
    if (!lv) {
        return;
    }

    lv = window.JSON.parse(lv);
    if (!lv) {
        return;
    }

    var pattern = /^\?[-a-z]+=\d+/i;
    // var path    = pattern.exec(location.pathname);
    var path    = pattern.exec(location.search);
    if (!path) {
        return;
    }

    path = path[0];

    var makeButton = function (text, url) {
        var a = $WH.ce('a');
        a.className = 'button-red' + (url ? ' button-red-disabled' : '');

        var em = $WH.ce('em');
        $WH.ae(a, em);

        var b = $WH.ce('b');
        $WH.ae(em, b);

        var i = $WH.ce('i');
        $WH.ae(b, i);
        $WH.st(i, text);

        var sp = $WH.ce('span');
        $WH.ae(em, sp);
        $WH.st(sp, text);

        return a;
    };

    for (var i = 0; i < lv.length; i++) {
        var urls = lv[i].urls;

        for (var j = 0; j < urls.length; j++) {
            if (urls[j] == path) {
                var prevUrl = j > 0 ? urls[j - 1] : false;
                var nextUrl = (j + 1) < urls.length ? urls[j + 1] : false;
                var upUrl   = lv[i].path + lv[i].hash;
                var el      = $WH.ge('topbar-browse');

                if (!el) {
                    return;
                }

                var button;

                button = makeButton('>', !nextUrl);
                if (nextUrl) {
                    button.href = nextUrl;
                }
                $WH.ae(el, button);

                button = makeButton(LANG.up, !upUrl);
                if (upUrl) {
                    button.href = upUrl;
                }
                $WH.ae(el, button);

                button = makeButton('<', !prevUrl);
                if (prevUrl) {
                    button.href = prevUrl;
                }
                $WH.ae(el, button);

                return;
            }
        }
    }
});

$(document).ready((function () {
    $('input').each((function () {
        var maxLen = $(this).attr('maxlength');
        var elemId = $(this).attr('data-charwarning');
        var container = elemId ? $('#' + elemId) : null;
        if (!maxLen || !container)
            return;

        var _hide = function () { container.hide() };
        var _show = function (len) {
            var chars  = maxLen - len;
            var usePct = len / maxLen;

            var r = parseInt(usePct >= 0.5 ? 255 :        usePct        * 2 * 255).toString(16);
            var g = parseInt(usePct <  0.5 ? 255 : 255 - (usePct - 0.5) * 2 * 255).toString(16);

            if (r.length == 1)
                r = '0' + r;
            if (g.length == 1)
                g = '0' + g;

            container.text($WH.sprintf(LANG.compose_remaining, chars));
            container.show();
            container.css('color', '#' + r + g + '00')
        };

        $(this).focus((function () { _show($(this).val().length)}))
               .blur((function () { _hide() }))
               .keyup((function () { _show($(this).val().length) }))
    }))
}));

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

function CreateAjaxLoader() {
    return $('<img>').attr('alt', '').attr('src', g_staticUrl + '/images/icons/ajax.gif').addClass('ajax-loader');
}


/*
Global utility functions related to arrays, format validation, regular expressions, and strings
*/

function g_createRange(min, max) {
    range = {};

    for (var i = min; i <= max; ++i) {
        range[i] = i;
    }

    return range;
}

function g_sortIdArray(arr, reference, prop) {
    arr.sort(
        prop ?
            function(a, b) { return $WH.strcmp(reference[a][prop], reference[b][prop]) }
        :
            function(a, b) { return $WH.strcmp(reference[a], reference[b]) }
    );
}

function g_sortJsonArray(src, reference, sortFunc, filterFunc) {
    var result = [];

    for (var i in src) {
        if (reference[i] && (filterFunc == null || filterFunc(reference[i]))) {
            result.push(i);
        }
    }

    if (sortFunc != null) {
        result.sort(sortFunc);
    }
    else {
        g_sortIdArray(result, reference);
    }

    return result;
}

function g_urlize(str, allowLocales, profile) {
    var ta = $WH.ce('textarea');
    ta.innerHTML = str.replace(/</g,"&lt;").replace(/>/g,"&gt;");
    str = ta.value;

    str = $WH.str_replace(str, ' / ', '-');
    str = $WH.str_replace(str, "'", '');

    if (profile) {
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
            "Œ": "Oe"
        };
        for (var character in accents) {
            str = str.replace(new RegExp(character, "g"), accents[character]);
        }
    }

    str = $WH.trim(str);
    if (allowLocales) {
        str = $WH.str_replace(str, ' ', '-');
    }
    else {
        str = str.replace(/[^a-z0-9]/ig, '-');
    }

    str = $WH.str_replace(str, '--', '-');
    str = $WH.str_replace(str, '--', '-');
    str = $WH.rtrim(str, '-');
    str = str.replace(/[A-Z]/g, function(x) {
        return x.toLowerCase();
    });

    return str;
}

function g_isDateValid(date) {
    var match = /^(20[0-2]\d)-([01]\d)-([0-3]\d) ([0-2]\d):([0-5]\d):([0-5]\d)$/.exec(date);
    return match;
}

function g_isIpAddress(str) {
    return /[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+/.test(str);
}

function g_isEmailValid(email) {
    return email.match(/^([a-z0-9._-]+)(\+[a-z0-9._-]+)?(@[a-z0-9.-]+\.[a-z]{2,4})$/i) != null;
}

function g_getCurrentDomain() {
    if (g_getCurrentDomain.CACHE) {
        return g_getCurrentDomain.CACHE;
    }

    var hostname = location.hostname;

    if (!g_isIpAddress(hostname)) {
        // Only keep the last 2 parts
        var parts = hostname.split('.');
        if (parts.length > 2) {
            parts.splice(0, parts.length - 2);
        }
        hostname = parts.join('.');
    }

    g_getCurrentDomain.CACHE = hostname;

    return hostname;
}

function g_isExternalUrl(url) {
    if (!url) {
        return false;
    }

    if (url.indexOf('http') != 0 && url.indexOf('//') != 0) {
        return false;
    }
    else if (url.indexOf(g_getCurrentDomain()) != -1) {
        return false;
    }

    return true;
}

function g_createOrRegex(search, negativeGroup) {
    search = search.replace(/(\(|\)|\|\+|\*|\?|\$|\^)/g, '\\$1');
    var
        parts = search.split(' '),
        strRegex= '';

    for (var j = 0, len = parts.length; j < len; ++j) {
        if (j > 0) {
            strRegex += '|';
        }
        strRegex += parts[j];
    }

    // The additional group is necessary so we dont replace %s
    return new RegExp((negativeGroup != null ? '(' + negativeGroup + ')?' : '') + '(' + strRegex + ')', 'gi');
}

function g_getHash() {
    return '#' + decodeURIComponent(location.href.split('#')[1] || '');
}

// Lets you add/remove/edit the query parameters in the passed URL
function g_modifyUrl(url, params, opt) {
    if (!opt) {
        opt = $.noop;
    }

    // Preserve existing hash
    var hash = '';
    if (url.match(/(#.+)$/)) {
        hash = RegExp.$1;
        url = url.replace(hash, '');
    }

    $.each(params, function(paramName, newValue) {
        var needle;
        var paramPrefix;
        var paramValue;

        var matches = url.match(new RegExp('(&|\\?)?' + paramName + '=?([^&]+)?'));
        if (matches != null) {
            needle      = matches[0];
            paramPrefix = matches[1];
            paramValue  = decodeURIComponent(matches[2]);
        }

        // Remove
        if (newValue == null) {
            if (!needle) {
                return; // If param wasn't there, no need to remove anything
            }
            paramValue = null;
        }
        // Append
        else if (newValue.substr(0, 2) == '+=') {
            if (paramValue && opt.onAppendCollision) {
                 paramValue = opt.onAppendCollision(paramValue, newValue.substr(2), opt.menuUrl);
            }
            else if (!paramValue && opt.onAppendEmpty) {
                 paramValue = opt.onAppendEmpty(newValue.substr(2), opt.menuUrl);
            }
            else {
                if (!paramValue) {
                    paramValue = '';
                }
                paramValue += $.trim(newValue.substr(2));
            }
        }
        // Set
        else {
            paramValue = newValue;
        }

        // Replace existing param
        if (needle) {
            var replacement = '';
            if (paramPrefix) { // Preserve existing prefix
                replacement += paramPrefix;
            }
            if (paramValue != null) {
                replacement += paramName;
                if (paramValue) {
                    replacement += '=' + $WH.urlencode2(paramValue);
                }
            }

            url = url.replace(needle, replacement);
        }
        // Add new param
        else if (paramValue || newValue == null || newValue.substr(0,2) != '+=') {
            url += (url.indexOf('?') == -1 ? '?' : '&') + paramName;
            if (paramValue) {
                url += '=' + $WH.urlencode2(paramValue);
            }
        }
    });

    // Polish
    url = url.replace('?&', '?');
    url = url.replace(/&&/g, '&');
    url = url.replace(/\/\?/g, '?');
    url = url.replace(/(&|\?)+$/, ''); // Remove trailing & and ? characters

    return url + hash;
}

function g_enhanceTextarea (ta, opt) {
    if (!(ta instanceof jQuery)) {
        ta = $(ta);
    }

    if (ta.data("wh-enhanced") || ta.prop("tagName") != "TEXTAREA") {
        return;
    }

    if (typeof opt != "object") {
        opt = {};
    }

    var canResize = (function(el) {
        if (!el.dynamicResizeOption)
            return true;

        if ($WH.localStorage.get("dynamic-textarea-resizing") === "true")
            return true;

        if ($WH.localStorage.get("dynamic-textarea-resizing") === "false")
            return false;

        return !el.hasOwnProperty("dynamicSizing") || el.dynamicSizing;
    }).bind(null, opt);

    var height  = ta.height() || 500;
    var wrapper = $("<div/>", { "class": "enhanced-textarea-wrapper" }).insertBefore(ta).append(ta);

    if (!opt.hasOwnProperty("color"))
        wrapper.addClass("enhanced-textarea-dark")
    else if (opt.color)
        wrapper.addClass("enhanced-textarea-" + opt.color)

    if (!opt.hasOwnProperty("dynamicSizing") || opt.dynamicSizing || opt.dynamicResizeOption) {
        var expander = $("<div/>", { "class": "enhanced-textarea-expander" }).prependTo(wrapper);
        var n = function(E, D, F) {
            if (!F())
                return;

            // E.css("height", E.siblings(".enhanced-textarea-expander").html($WH.htmlentities(E.val()).replace(/\n/g, "<br>") + "<br>").height() + (D ? 14 : 34) + "px")
            E.css("height", E.siblings(".enhanced-textarea-expander").html($WH.htmlentities(E.val()) + "<br>").height() + (D ? 14 : 34) + "px")
        };

        ta.bind("keydown keyup change", n.bind(this, ta, opt.exactLineHeights, canResize));
        n(ta, opt.exactLineHeights, canResize);
        var setWidth = function(D) {
            D.css("width", D.parent().width() + "px")
        };
        setWidth(expander);
        setTimeout(setWidth.bind(null, expander), 1);
        if (!opt.dynamicResizeOption || (opt.dynamicResizeOption && canResize())) {
            wrapper.addClass("enhanced-textarea-dynamic-sizing")
        }
    }
    if (!opt.hasOwnProperty("focusChanges") || opt.focusChanges) {
        wrapper.addClass("enhanced-textarea-focus-changes")
    }
    if (opt.markup) {
        var w = $("<div/>", { "class": "enhanced-textarea-markup-wrapper" }).prependTo(wrapper);
        var y = $("<div/>", { "class": "enhanced-textarea-markup" }).appendTo(w);
        var z = $("<div/>", { "class": "enhanced-textarea-markup-segment" }).appendTo(y);
        var k = $("<div/>", { "class": "enhanced-textarea-markup-segment" }).appendTo(y);

        if (opt.markup == "inline")
            ar_AddInlineToolbar(ta.get(0), z.get(0), k.get(0));
        else
            ar_AddToolbar(ta.get(0), z.get(0), k.get(0));

        if (opt.dynamicResizeOption) {
            var t = $("<div/>", { "class": "enhanced-textarea-markup-segment" }).appendTo(y);
            var C = $("<label/>").appendTo(t);
            var A = $("<input/>", { type: "checkbox", checked: canResize() }).appendTo(C);
            A.change((function(E, D, H, J, F, G) {
                var I = this.is(":checked");
                $WH.localStorage.set("dynamic-textarea-resizing", JSON.stringify(I));
                if (I) {
                    D.addClass("enhanced-textarea-dynamic-sizing");
                    G(H, E.exactLineHeights, J);
                }
                else {
                    D.removeClass("enhanced-textarea-dynamic-sizing");
                    H.css("height", F + "px");
                }
            }).bind(A, opt, wrapper, ta, canResize, height, n));

            $("<span/>", { text: LANG.autoresizetextbox }).appendTo(C);
        }

        if (opt.scrollingMarkup) {
            if (g_enhanceTextarea.scrollerCount)
                g_enhanceTextarea.scrollerCount++;
            else
                g_enhanceTextarea.scrollerCount = 1;

            var B = "fixable-markup-controls-" + g_enhanceTextarea.scrollerCount;
            var o = "fixed-markup-controls-"   + g_enhanceTextarea.scrollerCount;
            var setBGColor = function(el) {
                var color = el.css("backgroundColor");
                if (color == "rgba(0, 0, 0, 0)" || color == "transparent")
                    return setBGColor(el.parent());
                else
                    return color;
            };
            var r = setBGColor(y);
            for (var m, l = 0;
                (m = window.document.styleSheets[l]) && m.href; l++) {}
            if (!m) {
                window.document.head.appendChild(document.createElement("style"));
                m = window.document.styleSheets[l];
            }
            m.insertRule("." + o + " ." + B + " .enhanced-textarea-markup {background:" + r + ";padding-bottom:5px;padding-top:10px;position:fixed;top:0;z-index:3}", m.cssRules.length);
            m.insertRule(".touch-device ." + o + " ." + B + " .enhanced-textarea-markup {padding-top:50px}", m.cssRules.length);
            w.addClass(B);
            var s = function(F, D, cssClass, offset) {
                var H = this.scrollY || this.pageYOffset || 0;
                if (H > F.offset().top - 10 - offset && H < D.offset().top + D.height() - 100 - offset)
                    $("body").addClass(cssClass);
                else
                    $("body").removeClass(cssClass);
            };

            $(window).scroll(s.bind(window, w, wrapper, o, 0));
            s.call(window, w, wrapper, o, 0);
            var setSize = (function(D, E) {
                E.css("width",  D.width()  + "px");
                D.css("height", E.height() + "px");
            }).bind(null, w, y);
            setSize();
            $(window).on("resize", setSize);
            $(function() { setTimeout(setSize, 2000) })
        }
    }

    ta.data("wh-enhanced", true);
};

$WH.createOptionsMenuWidget = function (id, txt, opt) {
    var chevron = $WH.createOptionsMenuWidget.chevron;
    if (opt.noChevron)
        chevron = '';

    var container = $WH.ce('span');
    container.id = 'options-menu-widget-' + id;
    container.className = 'options-menu-widget ' + container.id;
    container.innerHTML = txt + chevron;

    if (opt.id)
        container.id = opt.id;

    if (opt.className)
        container.className += ' ' + opt.className;

  if (opt.options instanceof Array) {
        var c = [];
        for (var itr = 0, d; d = opt.options[itr]; itr++) {
            var menu = [itr, d[MENU_IDX_NAME]];
            if ((typeof opt.selected == 'number' || typeof opt.selected == 'string') && opt.selected == d[MENU_IDX_ID]) {
                container.innerHTML = d[MENU_IDX_NAME] + chevron;
                if (d[MENU_IDX_SUB]) {
                    switch (typeof d[MENU_IDX_SUB].className) {
                        case 'string':
                            $.data(container, 'options-menu-widget-class', d[MENU_IDX_SUB].className);
                            container.className += ' ' + d[MENU_IDX_SUB].className;
                            break;
                        case 'function':
                            $.data(container, 'options-menu-widget-class', d[MENU_IDX_SUB].className(d, true));
                            container.className += ' ' + d[MENU_IDX_SUB].className(d, true);
                            break
                    }
                }
            }
            if (d[MENU_IDX_URL]) {
                menu.push(function (chevron, container, i, opt) {
                    switch (typeof i[MENU_IDX_URL]) {
                        case 'string':
                            window.location = i[MENU_IDX_URL];
                            break;
                        case 'function':
                            if (typeof opt.updateWidgetText == 'undefined' || opt.updateWidgetText) {
                                container.innerHTML = i[MENU_IDX_NAME] + chevron;
                                var o = $.data(container, 'options-menu-widget-class');
                                if (o)
                                    container.className = container.className.replace(new RegExp(' *\\b' + o + '\\b'), '');

                                if (i[MENU_IDX_SUB]) {
                                    switch (typeof i[MENU_IDX_SUB].className) {
                                        case 'string':
                                            $.data(container, 'options-menu-widget-class', i[MENU_IDX_SUB].className);
                                            container.className += ' ' + i[MENU_IDX_SUB].className;
                                            break;
                                        case 'function':
                                            $.data(container, 'options-menu-widget-class', i[MENU_IDX_SUB].className(i, true));
                                            container.className += ' ' + i[MENU_IDX_SUB].className(i, true);
                                            break;
                                    }
                                }
                            }

                            i[MENU_IDX_URL](container, i);
                            break;
                    }
                }.bind(null, chevron, Menu.add, d, opt))
            }
            else if (!d[MENU_IDX_SUB] || !d[MENU_IDX_SUB].menu)
                menu[0] = null;

            menu[MENU_IDX_OPT] = {};
            if (d[MENU_IDX_SUB]) {
                switch (typeof d[MENU_IDX_SUB].className) {
                    case 'string':
                        menu[MENU_IDX_OPT].className = d[MENU_IDX_SUB].className;
                        break;
                    case 'function':
                        menu[MENU_IDX_OPT].className = d[MENU_IDX_SUB].className.bind(null, d, false);
                        break;
                }
                switch (typeof d[MENU_IDX_SUB].column) {
                    case 'number':
                    case 'string':
                        menu[MENU_IDX_OPT].column = d[MENU_IDX_SUB].column;
                        break;
                    case 'function':
                        menu[MENU_IDX_OPT].column = d[MENU_IDX_SUB].column.bind(null, d);
                        break;
                }
                switch (typeof d[MENU_IDX_SUB].tinyIcon) {
                    case 'string':
                        menu[MENU_IDX_OPT].tinyIcon = d[MENU_IDX_SUB].tinyIcon;
                        break;
                    case 'function':
                        menu[MENU_IDX_OPT].tinyIcon = d[MENU_IDX_SUB].tinyIcon.bind(null, d);
                        break;
                }
                switch (typeof d[MENU_IDX_SUB].fontIcon) {
                    case 'string':
                        menu[MENU_IDX_OPT].fontIcon = d[MENU_IDX_SUB].fontIcon;
                        break;
                    case 'function':
                        menu[MENU_IDX_OPT].fontIcon = d[MENU_IDX_SUB].fontIcon.bind(null, d);
                        break;
                }

                if (typeof d[MENU_IDX_SUB].isChecked == 'function')
                    menu[MENU_IDX_OPT].checkedFunc = d[MENU_IDX_SUB].isChecked.bind(null, d);

                if (typeof d[MENU_IDX_SUB].menu == 'object' && d[MENU_IDX_SUB].menu instanceof Array)
                    Menu.setSubmenu(menu, d[MENU_IDX_SUB].menu); // <-- n.d. !

            }
            c.push(menu);
        }

        container.menu = c;
        if (opt.menuOnClick) {
            container.onmousedown = $WH.rf;
            Menu.add(container, c, { showAtElement: true });
        }
        else
            Menu.add(container, c);

  }

  if (opt.target)
        $(opt.target).append(container);
  else
        return container;
};
$WH.createOptionsMenuWidget.chevron = ' <i class="fa fa-chevron-down fa-color-gray">~</i>';

/*
Global WoW data
*/

var g_file_races = {
     1: 'human',
     2: 'orc',
     3: 'dwarf',
     4: 'nightelf',
     5: 'scourge',
     6: 'tauren',
     7: 'gnome',
     8: 'troll',
    10: 'bloodelf',
    11: 'draenei'
};

var g_file_classes = {
    1: 'warrior',
    2: 'paladin',
    3: 'hunter',
    4: 'rogue',
    5: 'priest',
    6: 'deathknight',
    7: 'shaman',
    8: 'mage',
    9: 'warlock',
   11: 'druid'
};

var g_file_specs = {
    "-1":  'inv_misc_questionmark',
       0:  'spell_nature_elementalabsorption',
       6: ['spell_deathknight_bloodpresence', 'spell_deathknight_frostpresence', 'spell_deathknight_unholypresence' ],
      11: ['spell_nature_starfall',           'ability_racial_bearform',         'spell_nature_healingtouch'        ],
       3: ['ability_hunter_beasttaming',      'ability_marksmanship',            'ability_hunter_swiftstrike'       ],
       8: ['spell_holy_magicalsentry',        'spell_fire_firebolt02',           'spell_frost_frostbolt02'          ],
       2: ['spell_holy_holybolt',             'spell_holy_devotionaura',         'spell_holy_auraoflight'           ],
       5: ['spell_holy_wordfortitude',        'spell_holy_holybolt',             'spell_shadow_shadowwordpain'      ],
       4: ['ability_rogue_eviscerate',        'ability_backstab',                'ability_stealth'                  ],
       7: ['spell_nature_lightning',          'spell_nature_lightningshield',    'spell_nature_magicimmunity'       ],
       9: ['spell_shadow_deathcoil',          'spell_shadow_metamorphosis',      'spell_shadow_rainoffire'          ],
       1: ['ability_rogue_eviscerate',        'ability_warrior_innerrage',       'ability_warrior_defensivestance'  ]
};

var g_file_genders = {
    0: 'male',
    1: 'female'
};

var g_file_factions = {
    1: 'alliance',
    2: 'horde'
};

var g_file_gems = {
     1: 'meta',
     2: 'red',
     4: 'yellow',
     6: 'orange',
     8: 'blue',
    10: 'purple',
    12: 'green',
    14: 'prismatic'
};

/*
Source:
http://www.wowwiki.com/Patches
http://www.wowwiki.com/Patches/1.x
*/

function g_getPatchVersionIndex(timestamp) {
    var _ = g_getPatchVersion;
    var l = 0, u = _.T.length - 2, m;

    while (u > l) {
        m = Math.floor((u + l) / 2);

        if (timestamp >= _.T[m] && timestamp < _.T[m + 1]) {
            return m;
        }

        if (timestamp >= _.T[m]) {
            l = m + 1;
        }
        else {
            u = m - 1;
        }
    }
    m = Math.ceil((u + l) / 2);

    return m;
}

function g_getPatchVersion(timestamp) {
    var m = g_getPatchVersionIndex(timestamp);
    return g_getPatchVersion.V[m];
}
g_getPatchVersion.V = [
    '1.12.0',
    '1.12.1',
    '1.12.2',

    '2.0.1',
    '2.0.3',
    '2.0.4',
    '2.0.5',
    '2.0.6',
    '2.0.7',
    '2.0.8',
    '2.0.10',
    '2.0.12',

    '2.1.0',
    '2.1.1',
    '2.1.2',
    '2.1.3',

    '2.2.0',
    '2.2.2',
    '2.2.3',

    '2.3.0',
    '2.3.2',
    '2.3.3',

    '2.4.0',
    '2.4.1',
    '2.4.2',
    '2.4.3',

    '3.0.2',
    '3.0.3',
    '3.0.8',
    '3.0.9',

    '3.1.0',
    '3.1.1',
    '3.1.2',
    '3.1.3',

    '3.2.0',
    '3.2.2',

    '3.3.0',
    '3.3.2',
    '3.3.3',
    '3.3.5',

    '?????'
];

g_getPatchVersion.T = [
    // 1.12: Drums of War
    1153540800000, // 1.12.0    22 August 2006
    1159243200000, // 1.12.1    26 September 2006
    1160712000000, // 1.12.2    13 October 2006

    // 2.0: Before the Storm (The Burning Crusade)
    1165294800000, // 2.0.1     5 December 2006
    1168318800000, // 2.0.3     9 January 2007
    1168578000000, // 2.0.4     12 January 2007
    1168750800000, // 2.0.5     14 January 2007
    1169528400000, // 2.0.6     23 January 2007
    1171342800000, // 2.0.7     13 February 2007
    1171602000000, // 2.0.8     16 February 2007
    1173157200000, // 2.0.10    6 March 2007
    1175572800000, // 2.0.12    3 April 2007

    // 2.1: The Black Temple
    1179806400000, // 2.1.0     22 May 2007
    1181016000000, // 2.1.1     5 June 2007
    1182225600000, // 2.1.2     19 June 2007
    1184040000000, // 2.1.3     10 July 2007

    // 2.2: Voice Chat!
    1190692800000, // 2.2.0     25 September 2007
    1191297600000, // 2.2.2     2 October 2007
    1191902400000, // 2.2.3     9 October 2007

    // 2.3: The Gods of Zul'Aman
    1194930000000, // 2.3.0     13 November 2007
    1199768400000, // 2.3.2     08 January 2008
    1200978000000, // 2.3.3     22 January 2008

    // 2.4: Fury of the Sunwell
    1206417600000, // 2.4.0     25 March 2008
    1207022400000, // 2.4.1     1 April 2008
    1210651200000, // 2.4.2     13 May 2008
    1216094400000, // 2.4.3     15 July 2008

    // 3.0: Echoes of Doom
    1223956800000, // 3.0.2     October 14 2008
    1225774800000, // 3.0.3     November 4 2008
    1232427600000, // 3.0.8     January 20 2009
    1234242000000, // 3.0.9     February 10 2009

    // 3.1: Secrets of Ulduar
    1239681600000, // 3.1.0     April 14 2009
    1240286400000, // 3.1.1     April 21 2009
    1242705600000, // 3.1.2     19 May 2009
    1243915200000, // 3.1.3     2 June 2009

    // 3.2: Call of the Crusader
    1249358400000, // 3.2.0     4 August 2009
    1253595600000, // 3.2.2     22 September 2009

    // 3.3: Fall of the Lich King
    1260266400000, // 3.3.0     8 December 2009
    1265104800000, // 3.3.2     2 February 2010
    1269320400000, // 3.3.3     23 March 2010
    1277182800000, // 3.3.5     22 June 2010

    9999999999999
];

/*
Global stuff related to WoW database entries
*/

var
    g_npcs               = {},
    g_objects            = {},
    g_items              = {},
    g_itemsets           = {},
    g_quests             = {},
    g_spells             = {},
    g_gatheredzones      = {},
    g_factions           = {},
    g_pets               = {},
    g_achievements       = {},
    g_titles             = {},
    g_holidays           = {},
    g_classes            = {},
    g_races              = {},
    g_skills             = {},
    g_gatheredcurrencies = {},
    g_sounds             = {},
    g_icons              = {},
    g_enchantments       = {},
    g_emotes             = {};

var g_types = {
      1: 'npc',
      2: 'object',
      3: 'item',
      4: 'itemset',
      5: 'quest',
      6: 'spell',
      7: 'zone',
      8: 'faction',
      9: 'pet',
     10: 'achievement',
     11: 'title',
     12: 'event',
     13: 'class',
     14: 'race',
     15: 'skill',
     17: 'currency',
     19: 'sound',
     29: 'icon',
    501: 'emote',
    502: 'enchantment',
    503: 'areatrigger',
    504: 'mail'
};

// Items
$WH.cO(g_items, {
    add: function(id, json) {
        if (g_items[id] != null) {
            $WH.cO(g_items[id], json);
        }
        else {
            g_items[id] = json;
        }
    },
    getIcon: function(id) {
        if (g_items[id] != null && g_items[id].icon) {
            return g_items[id].icon;
        }
        else {
            return 'inv_misc_questionmark';
        }
    },
    createIcon: function(id, size, num, qty) {
        return Icon.create(g_items.getIcon(id), size, null, '?item=' + id, num, qty);
    }
});

// Spells
$WH.cO(g_spells, {
    add: function(id, json) {
        if (g_spells[id] != null) {
            $WH.cO(g_spells[id], json);
        }
        else {
            g_spells[id] = json;
        }
    },
    getIcon: function(id) {
        if (g_spells[id] != null && g_spells[id].icon) {
            return g_spells[id].icon;
        }
        else {
            return 'inv_misc_questionmark';
        }
    },
    createIcon: function(id, size, num, qty) {
        return Icon.create(g_spells.getIcon(id), size, null, '?spell=' + id, num, qty);
    }
});

// Achievements
$WH.cO(g_achievements, {
    getIcon: function(id) {
        if (g_achievements[id] != null && g_achievements[id].icon) {
            return g_achievements[id].icon;
        }
        else {
            return 'inv_misc_questionmark';
        }
    },
    createIcon: function(id, size, num, qty) {
        return Icon.create(g_achievements.getIcon(id), size, null, '?achievement=' + id, num, qty);
    }
});

// Classes
$WH.cO(g_classes, {
    getIcon: function(id) {
        if (g_file_classes[id]) {
            return 'class_' + g_file_classes[id];
        }
        else {
            return 'inv_misc_questionmark';
        }
    },
    createIcon: function(id, size, num, qty) {
        return Icon.create(g_classes.getIcon(id), size, null, '?class=' + id, num, qty);
    }
});

// Races
$WH.cO(g_races, {
    getIcon: function(id, gender) {
        if (gender === undefined) {
            gender = 0;
        }
        if (g_file_races[id] && g_file_genders[gender]) {
            return 'race_' + g_file_races[id] + '_' + g_file_genders[gender];
        }
        else {
            return 'inv_misc_questionmark';
        }
    },
    createIcon: function(id, size, num, qty) {
        return Icon.create(g_races.getIcon(id), size, null, '?race=' + id, num, qty);
    }
});

// Skills
$WH.cO(g_skills, {
    getIcon: function(id) {
        if (g_skills[id] != null && g_skills[id].icon) {
            return g_skills[id].icon;
        }
        else {
            return 'inv_misc_questionmark';
        }
    },
    createIcon: function(id, size, num, qty) {
        return Icon.create(g_skills.getIcon(id), size, null, '?skill=' + id, num, qty);
    }
});

// Currencies
$WH.cO(g_gatheredcurrencies, {
    getIcon: function(id, side) {
        if (g_gatheredcurrencies[id] != null && g_gatheredcurrencies[id].icon) {
            if ($WH.is_array(g_gatheredcurrencies[id].icon) && !isNaN(side)) {
                return g_gatheredcurrencies[id].icon[side];
            }
            return g_gatheredcurrencies[id].icon;
        }
        else
            return 'inv_misc_questionmark';
    },
    createIcon: function(id, size, num, qty) {
        return Icon.create(g_gatheredcurrencies.getIcon(id, (num > 0 ? 0 : 1)), size, null, null, Math.abs(num), qty);
    }
});

// Holidays
$WH.cO(g_holidays, {
    getIcon: function(id) {
        if (g_holidays[id] != null && g_holidays[id].icon) {
            return g_holidays[id].icon;
        }
        else {
            return 'inv_misc_questionmark';
        }
    },
    createIcon: function(id, size, num, qty) {
        return Icon.create(g_holidays.getIcon(id), size, null, '?event=' + id, num, qty);
    }
});

function g_getIngameLink(color, id, name) {
    // prompt(LANG.prompt_ingamelink, '/script DEFAULT_CHAT_FRAME:AddMessage("\\124c' + a + "\\124H" + c + "\\124h[" + b + ']\\124h\\124r");')
    return '/script DEFAULT_CHAT_FRAME:AddMessage("\\124c' + color + '\\124H' + id + '\\124h[' + name + ']\\124h\\124r");';
}

/*
 * Wowhead Site Achievements (WSA)
 * which i intend to ignore
*/

/* aowow - custom */
var ConditionList = new function() {
    var
        self = this,
        _conditions = null;

    self.createCell = function(conditions) {
        if (!conditions)
            return null;

        _conditions = conditions;

        return _createCell();
    };
    self.createTab = function(conditions) {
        if (!conditions)
            return null;

        _conditions = conditions;

        return _createTab();
    };

    function _makeList(mask, src, tpl) {
        var
            arr  = Listview.funcBox.assocBinFlags(mask, src).sort(),
            buff = '';

        for (var i = 0, len = arr.length; i < len; ++i) {
            if (len > 1 && i == len - 1)
                buff += LANG.or;
            else if (i > 0)
                buff += LANG.comma;

            buff += $WH.sprintf(tpl, arr[i], src[arr[i]]);
        }

        return buff;
    }

    function _parseEntry(entry, targets, target) {
        var
            str    = '',
            negate = false,
            strIdx = 0,
            param  = [];

        [strIdx, ...param]  = entry;

        negate = strIdx < 0;
        strIdx = Math.abs(strIdx);

        if (!g_conditions[strIdx])
            return 'unknown condition index #' + strIdx;

        switch (strIdx) {
            case  5:
                var standings = {};
                for (let i in g_reputation_standings)
                    standings[i * 1 + 1] = g_reputation_standings[i];

                param[1] = _makeList(entry[2], standings, '$2');
                break;
            case  6:
                if (entry[1] == 1)
                    param[0] = $WH.sprintf('[span class=icon-alliance]$1[/span]', g_sides[1]);
                else if (entry[1] == 2)
                    param[0] = $WH.sprintf('[span class=icon-horde]$1[/span]', g_sides[2]);
                else
                    param[0] = $WH.sprintf('[span class=icon-alliance]$1[/span]$2[span class=icon-horde]$3[/span]', g_sides[1], LANG.or, g_sides[2]);
                break;
            case 10:
                param[0] = g_drunk_states[entry[1]] ?? 'UNK DRUNK STATE';
                break;
            case 13:
                param[2] = g_instance_info[entry[3]] ?? 'UNK INSTANCE INFO';
                break;
            case 15:
                param[0] = _makeList(entry[1], g_chr_classes, '[class=$1]');
                break;
            case 16:
                param[0] = _makeList(entry[1], g_chr_races, '[race=$1]');
                break;
            case 20:
                if (entry[1] == 0)
                    param[0] = $WH.sprintf('[span class=icon-$1]$2[/span]', g_file_genders[0], LANG.male);
                else if (entry[1] == 1)
                    param[0] = $WH.sprintf('[span class=icon-$1]$2[/span]', g_file_genders[1], LANG.female);
                else
                    param[0] = g_npc_types[10];             // not specified
                break;
            case 21:
                var states = {};
                for (let i in g_unit_states)
                    states[i * 1 + 1] = g_unit_states[i];

                param[0] = _makeList(entry[1], states, '$2');
                break;
            case 22:
                if (entry[2])
                    param[0] = '[zone=' + entry[2] + ']';
                else
                    param[0] = g_zone_categories[entry[1]] ?? 'UNK ZONE';
                break;
            case 24:
                param[0] = g_npc_types[entry[1]] ?? 'UNK NPC TYPE';
                break;
            case 26:
                var idx = 0, buff = [];
                while (entry[1] >= (1 << idx)) {
                    if (!(entry[1] & (1 << idx++)))
                        continue;

                    buff.push(idx);
                }
                param[0] = buff ? buff.join(LANG.comma) : '';
                break;
            case 27:
            case 37:
            case 38:
                param[1] = g_operators[entry[2]];
                break;
            case 31:
                if (entry[2] && entry[1] == 3)
                    param[0] = '[npc=' + entry[2] + ']';
                else if (entry[2] && entry[1] == 5)
                    param[0] = '[object=' + entry[2] + ']';
                else
                    param[0] = g_world_object_types[entry[1]] ?? 'UNK TYPEID';
                break;
            case 32:
                var objectTypes = {};
                for (let i in g_world_object_types)
                    objectTypes[i * 1 + 1] = g_world_object_types[i];

                param[0] = _makeList(entry[1], objectTypes, '$2');
                break;
            case 33:
                param[0] = targets[entry[1]];
                param[1] = g_relation_types[entry[2]] ?? 'UNK RELATION';
                param[2] = targets[target];
                break;
            case 34:
                param[0] = targets[entry[1]];

                var standings = {};
                for (let i in g_reputation_standings)
                    standings[i * 1 + 1] = g_reputation_standings[i];
                param[1] = _makeList(entry[2], standings, '$2');
                break;
            case 35:
                param[0] = targets[entry[1]];
                param[2] = g_operators[entry[3]];
                break;
            case 42:
                if (!entry[1])
                    param[0] = g_stand_states[entry[2]] ?? 'UNK STAND_STATE';
                else if (entry[1] == 1)
                    param[0] = g_stand_states[entry[2] ? 1 : 0];
                else
                    param[0] = '';
                break;
            case 47:
                var quest_states = {};
                for (let i in g_quest_states)
                    quest_states[i * 1 + 1] = g_quest_states[i];

                param[1] = _makeList(entry[2], quest_states, '$2');
                break;
        }

        str = g_conditions[strIdx];

        // fill in params
        str = $WH.sprintfa(str, param[0], param[1], param[2]);

        // resolve NegativeCondition
        str = str.replace(/\$N([^:]*):([^;]*);/g, '$' + (negate > 0 ? 2 : 1));

        // resolve vars
        return str.replace(/\$C(\d+)([^:]*):([^;]*);/g, (_, i, y, n) => (i > 0 ? y : n));
    }

    function _createTab() {
        var
            buff     = '';

        // tabs for conditionsTypes
        for (g in _conditions) {
            if (!g_condition_sources[g])
                continue;

            let k = 0;
            for (h in _conditions[g]) {
                var
                    srcGroup, srcEntry, srcId, target,
                    targets, desc,
                    nGroups  = Object.keys(_conditions[g][h]).length,
                    curGroup = 1;

                [srcGroup, srcEntry, srcId, target] = h.split(':').map((x) => parseInt(x));
                [targets, desc] = g_condition_sources[g];

                // resolve targeting
                let src  = desc.replace(/\$T([^:]*):([^;]*);/, (_, t1, t2) => (target ? t2 : t1).replace('%', targets[target]));
                let rand = $WH.rs();

                buff += '[h3][toggler' + (k ? '=hidden' : '') + ' id=' + rand + ']' + $WH.sprintfa(src, srcGroup, srcEntry, srcId) + '[/toggler][/h3][div' + (k++ ? '=hidden' : '') + ' id=' + rand + ']';

                if (nGroups > 1) {
                    buff += LANG.note_condition_group + '[br][br]';
                    buff += '[table class=grid]';
                }

                // table for elseGroups
                for (i in _conditions[g][h]) {
                    var
                        group    = _conditions[g][h][i],
                        nEntries = Object.keys(_conditions[g][h][i]).length;

                    if (nGroups <= 1 && nEntries > 1)
                        buff += '[div style="padding-left:15px"]' + LANG.note_condition + '[/div]';
                    if (nGroups > 1)
                        buff += '[tr][td width=70px valign=middle align=center]' + LANG.group + ' ' + (curGroup++) + LANG.colon + '[/td][td]';

                    // individual conditions
                    buff += '[ol]';
                    for (j in group)
                        buff += '[li]' + _parseEntry(group[j], targets, target) + '[/li]';
                    buff += '[/ol]';

                    if (nGroups > 1)
                        buff += '[/td][/tr]';
                }

                if (nGroups > 1)
                    buff += '[/tr][/table]';

                buff += '[/div]';
            }
        }

        return buff;
    }

    function _createCell() {
        var rows = [];

        // tabs for conditionsTypes
        for (let g in _conditions) {
            if (!g_condition_sources[g])
                continue;

            for (let h in _conditions[g]) {
                var
                    target, targets,

                [, , , target] = h.split(':').map((x) => parseInt(x));
                [targets, ] = g_condition_sources[g];

                let nElseGroups = Object.keys(_conditions[g][h]).length

                // table for elseGroups
                for (let i in _conditions[g][h]) {
                    let
                        subGroup = [],
                        group    = _conditions[g][h][i],
                        nEntries = Object.keys(_conditions[g][h][i]).length
                        buff     = '';

                    if (nElseGroups > 1)
                    {
                        let rand = $WH.rs();
                        buff += '[toggler' + (i > 0 ? '=hidden' : '') + ' id=cell-' + rand + ']' + (i > 0 ? LANG.cnd_or : LANG.cnd_either) + '[/toggler][div' + (i > 0 ? '=hidden' : '') + ' id=cell-' + rand + ']';
                    }

                    // individual conditions
                    for (let j in group) {
                        subGroup.push(_parseEntry(group[j], targets, target));
                    }

                    for (j in subGroup) {
                        if (nEntries > 1 && j > 0 && j == subGroup.length - 1)
                            buff += LANG.and + '[br]';
                        else if (nEntries > 1 && j > 0)
                            buff += ',[br]';

                        buff += subGroup[j];
                    }

                    if (nElseGroups > 1)
                        buff += '[/div]';

                    rows.push(buff);
                }
            }
        }

        return rows.length > 1 ? rows.join('[br]') : rows[0];
    }

}
/* end custom */
