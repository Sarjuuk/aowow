var mn_content = [
//  [22, 'Achievements',             '?admin=achievements',                   null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_DEV | U_GROUP_BUREAU}],
    [3,  'Announcements',            '?admin=announcements',                  null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU}],
    [25, 'Guides Awaiting Approval', '?admin=guides',                         null,           {requiredAccess: U_GROUP_STAFF}],
//  [20, 'Global Images & Headers',  '?admin=headers',                        null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU}],
//  [21, 'Modelviewer',              '?admin=modelviewer',                    null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU}],
    [23, 'Out of Date Comments',     '?admin=out-of-date',                    null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_MOD}],
    [5,  'Screenshots',              '?admin=screenshots',                    null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_SCREENSHOT}],
//  [18, 'Upload Image',             '?npc=15384#submit-a-screenshot',        null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_EDITOR, rel: 'np'}],
    [17, 'Videos',                   '?admin=videos',                         null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO}],

    [,   'Homepage'],
    [13, 'Featured Box',             '?admin=home-featuredbox',               null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU, breadcrumb: 'Homepage Featured Box'}],
    [14, 'Oneliners',                '?admin=home-oneliners',                 null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU, breadcrumb: 'Homepage Oneliners'}],
//  [15, 'Skins',                    '?admin=home-skins',                     null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_SALESAGENT, breadcrumb: 'Homepage Skins'}],
    [16, 'Titles',                   '?admin=home-titles',                    null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU, breadcrumb: 'Homepage Titles'}],

    [,   'Articles'],
    [8,  'List',                     '?admin=articles',                       null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_DEV | U_GROUP_EDITOR | U_GROUP_LOCALIZER, breadcrumb: 'List of Articles'}],
//  [9,  'Editors\' Lounge',         '?admin=editors-lounge',                 null,           {requiredAccess: U_GROUP_EMPLOYEE | U_GROUP_EDITOR | U_GROUP_LOCALIZER}],
//  [23, 'Related Links',            '?admin=related-links',                  null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU}],

//  [,   'News'],
//  [10, 'New Post',                 '?edit=news',                            null,           {requiredAccess: U_GROUP_EMPLOYEE | U_GROUP_BLOGGER, breadcrumb: 'News Post'}],
//  [11, 'Content Corner',           '?admin=content-corner',                 null,           {requiredAccess: U_GROUP_EMPLOYEE | U_GROUP_BLOGGER}],
//  [12, 'Tags',                     '?admin=newstag',                        null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_DEV | U_GROUP_BLOGGER, breadcrumb: 'News Tags'}],
//  [24, 'Patch Updates',            '?admin=patch-updates',                  null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU}],
    [26, 'Featured Guides',          '?admin=featuredguides',                 null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU, breadcrumb: 'Featured Guides'}],

//  [,   'Community'],
//  [4,  'Contests',                 '?admin=contests',                       null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_SALESAGENT}],
//  [27, 'Top User Contest',         '?admin=topuser-contest',                null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_SALESAGENT}],
//  [19, 'Forums',                   '?admin=forums',                         null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU}],
//  [6,  'Profanity Filter',         '?admin=profanity',                      null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU}],

//  [,   'Other'],
//  [7,  'Holiday Gift Guide',       '?admin=holidaygift',                    null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU}]
];

var mn_dev = [
//  [17, 'Cookies',                  '?admin=cookies',                        null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_DEV}],
    [21, 'PHP Information',          '?admin=phpinfo',                        null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_DEV}],
    [18, 'Site Configuration',       '?admin=siteconfig',                     null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_DEV}],
    [16, 'Weight Presets',           '?admin=weight-presets',                 null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_DEV}],
//  [22, 'API Keys',                 '?admin=apikey',                         null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_DEV}],

//  [,   'Cache'],
//  [2,  'Create Folders',           '?admin=cache-folder',                   null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_DEV, breadcrumb: 'Create Cache Folders'}],
//  [3,  'Expire Range',             '?admin=cache-expire',                   null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_DEV, breadcrumb: 'Expire Cache Range'}],
//  [1,  'Manage',                   '?admin=cache-manage',                   null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_DEV, breadcrumb: 'Manage Cache'}],
//  [20, 'Memcached',                '?admin=memcached',                      null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_DEV, breadcrumb: 'Manage Memcached'}],

//  [, 'Database'],
//  [8,  'Add Fake Item',            '?admin=fakeitem',                       null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_DEV}],
//  [10, 'Add Fake NPC',             '?admin=fakenpc',                        null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_DEV}],
//  [19, 'Check Consistency',        '?admin=db-check-consistency',           null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_DEV, breadcrumb: 'Check Database Consistency'}],
//  [4,  'Execute SQL',              '?admin=sql',                            null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_DEV}],
//  [9,  'Export Fake Item',         '?admin=luaitem',                        null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_DEV}],
//  [26, 'Denormalized Fields Fix',  '?admin=denormalized-fix',               null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_DEV}],
//  [11, 'Minimum & Maximum Values', '?admin=minmax',                         null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_DEV}],
//  [7,  'SQL Find & Replace',       '?admin=sql-replace',                    null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_DEV}],
//  [25, 'Switch Active Database',   '?admin=active-db',                      null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_DEV}],
//  [5,  'Updates',                  '?admin=db-update',                      null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_DEV, breadcrumb: 'Database Updates'}],

//  [,   'Generators'],
//  [12, 'Talent Calculator Icons',  '?admin=talentcalc-icons',               null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_DEV}]
];

var mn_localization = [
    [1, 'Generate Files',            '?admin=locale-export',                  null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_DEV, breadcrumb: 'Generate Localization Files'}],

    [,  'Terms'],
    [4, 'Check Integrity',           '?admin=locale-integrity',               null,           {requiredAccess: U_GROUP_EMPLOYEE | U_GROUP_LOCALIZER, breadcrumb: 'Check Term Integrity'}],
    [2, 'Manage',                    '?admin=locale-search',                  null,           {requiredAccess: U_GROUP_EMPLOYEE | U_GROUP_LOCALIZER, breadcrumb: 'Manage Terms'}],

    [,  'Deprecated'],
    [6, 'Create Template',           '?admin=locale-template',                null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_DEV, breadcrumb: 'Create Localization Template'}],
    [5, 'Import Old Localized File', '?admin=locale-import',                  null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_DEV}],
    [7, 'Upload Global Strings',     '?admin=locale-upload',                  null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_DEV}]
];

var mn_statistics = [
    [1, 'Comments',                  '?admin=stats&table=comments'],
    [2, 'Comment Votes',             '?admin=stats&table=commentratings'],
    [3, 'Forum Posts',               '?admin=stats&table=forumposts'],
    [5, 'Registrations',             '?admin=stats&table=registrations'],
    [4, 'Screenshots',               '?admin=stats&table=screenshots'],
    [7, 'Uploads',                   '?admin=stats&table=uploads'],
    [6, 'Videos',                    '?admin=stats&table=videos'],
    [8, 'Vists by Registered Users', '?admin=stats&table=visits-by-registered']
];

var mn_users = [
    [2, 'Action Log',                '?admin=log',                            null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU}],
    [3, 'Banned IPs',                '?admin=bannedip',                       null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU}],
    [1, 'Manage',                    '?admin=finduser',                       null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_MODERATOR, breadcrumb: 'Manage Users'}],
    [5, 'Roles',                     '?admin=staff',                          null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_MODERATOR}],

    [, 'Deprecated'],
    [4, 'Get Registration Email',    '?admin=getregistrationemail',           null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU}]
];

var mn_staff = [
    [1,   'Content',                 null,                                    mn_content],
    [2,   'Development',             null,                                    mn_dev],
//  [3,   'Localization',            null,                                    mn_localization],
//  [7,   'Statistics',              null,                                    mn_statistics,  {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_DEV}],
//  [4,   'Users',                   null,                                    mn_users],
    [5,   'View Reports',            '?admin=reports',                        null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_EDITOR | U_GROUP_MOD | U_GROUP_LOCALIZER | U_GROUP_SCREENSHOT | U_GROUP_VIDEO} ],

    [,    'Page'],
    [102, 'Validate',                'http://validator.w3.org/check/referer', null,           {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_DEV | U_GROUP_TESTER}]];

mn_path.push([4, 'Staff', null, mn_staff]);

$(document).ready(function () {
    var footer = $('div.stafffooter');
    if (footer.length > 0) {
        var totalHeight = $(window).height();
        if (footer.offset().top < (totalHeight - 100)) {
            var offset = footer.offset();
            offset.top = totalHeight - 100;
            footer.offset(offset);
        }
    }
    var articleAccess = U_GROUP_EMPLOYEE | U_GROUP_EDITOR | (Locale.getId() != LOCALE_ENUS ? U_GROUP_LOCALIZER : 0);
    var urlParams = $WH.g_getGets();
    var buff;
    var refresh = {};
    var subMenu = null;
    if (urlParams.refresh != null) {
        buff = 'See Cached';
        refresh.refresh = null
    }
    else {
        var mCached = {};
        var fiCache = {};
        buff = 'Refresh';
        if (PageTemplate.get('pageName') == 'home') {
            refresh.home = '';
            mCached.home = '';
            fiCache.home = ''
        }
        refresh.refresh = '';
        mCached.refresh = 'memcached';
        fiCache.refresh = 'filecache';
        subMenu = [
            [1, 'Memcached',  g_modifyUrl(location.href, mCached), null, {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_DEV}],
            [2, 'File cache', g_modifyUrl(location.href, fiCache), null, {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_DEV}]
        ]
    }
    mn_staff.push([100, buff, g_modifyUrl(location.href, refresh), subMenu, {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_DEV}]);
    if (location.href.match(/website-achievement=([0-9]+)(\/.*)?/i)) {
        mn_staff.push([, 'Achievement']);
        mn_staff.push([200, 'Manage', '?admin=achievements&action=edit&id=' + RegExp.$1, null, {requiredAccess: U_GROUP_ADMIN | U_GROUP_DEV | U_GROUP_BUREAU}])
    }
    if (location.href.match(/website-achievements(\/.*)?/i)) {
        mn_staff.push([, 'Achievements']);
        mn_staff.push([200, 'Manage', '?admin=achievements', null, {requiredAccess: U_GROUP_ADMIN | U_GROUP_DEV | U_GROUP_BUREAU}])
    }
    if (location.href.match(/news=([0-9]+)(\/.*)/i)) {
        mn_staff.push([, 'News Post']);
        mn_staff.push([200, 'Edit', '?edit=news&id=' + RegExp.$1, null, {requiredAccess: U_GROUP_EMPLOYEE | U_GROUP_BLOGGER}]);
        mn_staff.push([203, 'View forum topic', '/forums&topic=' + RegExp.$1, null, {requiredAccess: U_GROUP_EMPLOYEE | U_GROUP_BLOGGER}])
    }
    if (location.href.match(/user=([a-z0-9]+)/i)) {
        mn_staff.push([, 'User']);
        mn_staff.push([201, 'Manage', '?admin=manageuser&name=' + RegExp.$1, null, {requiredAccess: U_GROUP_MODERATOR}])
    }
    if ($WH.isset('g_pageInfo')) {
        if (g_pageInfo.type && g_pageInfo.typeId) {
            mn_staff.push([, 'DB Entry']);
            mn_staff.push([1001, 'Edit Article', '?edit=article&type=' + g_pageInfo.type + '&typeid=' + g_pageInfo.typeId, null, {requiredAccess: articleAccess}]);
            mn_staff.push([1000, 'Manage Screenshots', '?admin=screenshots&type=' + g_pageInfo.type + '&typeid=' + g_pageInfo.typeId, null, {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_SCREENSHOT}]);
            mn_staff.push([1000, 'Manage Videos', '?admin=videos&type=' + g_pageInfo.type + '&typeid=' + g_pageInfo.typeId, null, {requiredAccess: U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO}])
        }
        if (g_pageInfo.articleUrl) {
            mn_staff.push([, 'Article']);
            mn_staff.push([1002, 'Edit', '?edit=article&' + g_pageInfo.articleUrl, null, {requiredAccess: (g_pageInfo.editAccess ? g_pageInfo.editAccess : articleAccess)}]);
            mn_staff.push([1003, 'Options', '?edit=article-options&url=' + g_pageInfo.articleUrl, null, {requiredAccess: articleAccess}])
        }
    }
    Menu.sort(mn_staff)
});

var staff_deleteCacheKey = function (b, key) {
    var div = $('#purge-memcache' + b);
    var url = '?purgekey=' + key;
    var data = {};

    if ($.isArray(key)) {
        url = '?purgekey';
        data.keys = key;
    }

    $.ajax({
        method: 'GET',
        url: url,
        data: data,
        success: function (x) {
            if (x == 0) {
                this.replaceWith('<span class="q2">Key successfully deleted!</span>');
            }
            else {
                this.replaceWith('<span style="color: red">Key deletion failed: ' + x + '</span>');
            }
        }.bind(div)
    })
};

$(document).ready(function () {
    $('#save-blog-form').submit(function () {
        var form = $(this);
        var blogId = form.find('input[name=blog-id]').val();
        form.find('.spinning-circle').show();
        form.find('input[type=submit]').attr('disabled', 'disabled').val('Saving..');
        form.find('#save-status').html('');
        leavePage(1);
        $.ajax({
            url: '?edit=news&id=' + blogId,
            async: true,
            cache: false,
            data: $(this).serialize(),
            type: 'POST',
            error: function () {
                alert('An error has occured. The news was not saved.')
            },
            success: function (data) {
                var response = eval(data);
                if (!response.success) {
                    alert('An error has occured, the news was not saved: ' + response.message);
                    return
                }
                form.find('input[name=blog-id]').val(response.entry);
                form.find('#save-status').html('<span class="q2">Your news has been saved!</span> [<a target="_blank" href="' + response.url + '">link</a>]')
            },
            complete: function () {
                form.find('.spinning-circle').hide();
                form.find('input[type=submit]').attr('disabled', '').val('Save')
            }
        });
        return false
    })
});

var listviewIdList = new function () {
    function onShow(container, i, str) {
        Lightbox.setSize(950, 590);

        if (i) {
            container.className = 'modelviewer';

            var div = $WH.ce('div');
            var pre = $WH.ce('pre');
            container.debug = pre;
            pre.className = 'code';
            $WH.ae(div, pre);
            $WH.ae(container, div);

            var a = $WH.ce('a');
            a.className = 'modelviewer-close';
            a.href = 'javascript:;';
            a.onclick = Lightbox.hide;
            $WH.ae(a, $WH.ce('span'));
            $WH.ae(container, a);

            clear = $WH.ce('div');
            clear.className = 'clear';
            $WH.ae(container, clear);
        }

        var ta = $('<textarea>').css('height', '100px').css('width', '100%').text(str);

        $WH.ee(container.debug);
        $WH.ae(container.debug, ta[0]);
    }

    this.show = function (str) {
        Lightbox.show('debuginfo', { onShow: onShow }, str);
    }
};

var TwigProfiler = new function () {
    this.show = function (rows) {
        if (!rows) {
            rows = [];
        }

        var len = rows.length;
        $('#footer-twig-count').text(len);

        var tbl = $('#footer-twig-table');
        for (var i = 0; i < len; ++i) {
            tbl.append($('<tr/>')
                .append($('<td/>', {css: {'white-space': 'nowrap', 'text-align': 'right'}, 'class': 'q2', text: rows[i].action} ))
                .append($('<td/>', {css: {'white-space': 'nowrap'}} ).append($('<small/>', {text: rows[i].name} )))
                .append($('<td/>', {css: {'white-space': 'nowrap'}} ).append($('<small/>', {text: rows[i].time} )))
            );
        }
    }
};
