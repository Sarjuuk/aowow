/*
JS code for "user" pages
*/

function us_isOwnProfile()
{
    return (typeof g_pageInfo == 'object' && g_user.name == g_pageInfo.username);
}

function us_addDescription()
{
    var _ = $WH.ge('description');
    var ownProfile = us_isOwnProfile();
    var emptyDesc  = (_.childNodes.length == 0);

    if (emptyDesc)
    {
        if (ownProfile)
            $WH.ae(_, $WH.ct(LANG.user_nodescription2));
        else
            $WH.ae(_, $WH.ct(LANG.user_nodescription));
    }

    if (ownProfile)
    {
        var
            b = $WH.ce('button'),
            d = $WH.ce('div');

        d.className = 'pad';
        b.onclick = function() { location.href = '?account#community' };
        if (emptyDesc)
            $WH.ae(b, $WH.ct(LANG.user_composeone));
        else
            $WH.ae(b, $WH.ct(LANG.user_editdescription));

        $WH.ae(_, d);
        $WH.ae(_, b);
    }
}

function us_addCharactersTab(data)
{
    var ownProfile = (us_isOwnProfile() || g_user.roles & U_GROUP_MODERATOR);

    if (!ownProfile)
    {
        var temp = [];
        for (var i = 0, len = data.length; i < len; ++i)
        {
            data[i].pinned = false;
            if (data[i].published && !data[i].deleted)
                temp.push(data[i]);
        }
        data = temp;
    }

    if (data.length)
        new Listview({
            template:       'profile',
            id:             'characters',
            name:           LANG.tab_characters,
            tabs:           tabsRelated,
            parent:         'lv-generic',
            onBeforeCreate: Listview.funcBox.beforeUserCharacters,
            sort:           [-11],
            visibleCols:    ['race', 'classs', 'level', 'talents', 'gearscore', 'achievementpoints'],
            data:           data
        });
}

function us_addProfilesTab(data)
{
    var ownProfile = (us_isOwnProfile() || g_user.roles & U_GROUP_MODERATOR);

    if (!ownProfile)
    {
        var temp = [];
        for (var i = 0, len = data.length; i < len; ++i)
        {
            if (data[i].published && !data[i].deleted)
                temp.push(data[i]);
        }
        data = temp;
    }

    if (data.length)
        new Listview({
            template:       'profile',
            id:             'profiles',
            name:           LANG.tab_profiles,
            tabs:           tabsRelated,
            parent:         'lv-generic',
            onBeforeCreate: Listview.funcBox.beforeUserProfiles,
            sort:           [-11],
            visibleCols:    ['race', 'classs', 'level', 'talents', 'gearscore'],
            hiddenCols:     ['location', 'guild'],
            data:           data
        });
}

Listview.funcBox.beforeUserComments = function()
{
    if (g_user.roles & U_GROUP_COMMENTS_MODERATOR) // Admin, Bureau, Mod
    {
        this.mode = 1;
        this.createCbControls = function(d)
        {
            var i = $WH.ce('input');
            i.type = 'button';
            i.value = 'Delete';
            i.onclick = (function()
            {
                var rows = this.getCheckedRows();
                if (!rows.length)
                    alert('No comments selected.');
                else if (confirm('Are you sure that you want to delete ' + (rows.length == 1 ? 'this comment' : 'these ' + rows.length + ' comments') + '?'))
                {
                    var ids = "";
                    $WH.array_walk(rows, function(x)
                    {
                        if (!x.deleted)
                        {
                            x.deleted = 1;
                            if (x.__tr != null)
                                x.__tr.__status.innerHTML = LANG.lvcomment_deleted;
                            ids += x.id + ','
                        }
                    });
                    ids = $WH.rtrim(ids, ',');
                    if (ids != '')
                        $.post('?comment=delete', { id: ids, username: g_pageInfo.username });
                    (Listview.cbSelect.bind(this, false))();
                }
            }).bind(this);
            $WH.ae(d, i);

            var i = $WH.ce('input');
            i.type = 'button';
            i.value = 'Undelete';
            i.onclick = (function()
            {
                var rows = this.getCheckedRows();
                if (!rows.length)
                    alert('No comments selected.');
                else
                {
                    var ids = '';

                    $WH.array_walk(rows, function(x)
                    {
                        if (x.deleted)
                        {
                            x.deleted = 0;
                            if (x.__tr != null)
                                x.__tr.__status.innerHTML = '';
                            ids += x.id + ','
                        }
                    });
                    ids = $WH.rtrim(ids, ',');
                    if (ids != '')
                        $.post('?comment=undelete', { id: ids, username: g_pageInfo.username });
                    (Listview.cbSelect.bind(this, false))();
                }
            }).bind(this);
            $WH.ae(d, i);
        }
    };

    this.customFilter = function (comment, i)
    {
        // return (us_isOwnProfile() || (g_user.roles & U_GROUP_COMMENTS_MODERATOR) ? 1 : !(comment.deleted || comment.purged || comment.removed))
        return (g_user.roles & U_GROUP_COMMENTS_MODERATOR ? i < 250 : !(comment.deleted || comment.removed))
    };

    this.onAfterCreate = function()
    {
        if (this.nRowsVisible == 0)
        {
            if (this.tabs.tabs.length == 1)                 // Delete related section
                $("#related, #tabs-related, #lv-generic").remove()
            else if (!this.tabs.tabs[this.tabIndex].hidden)
                this.tabs.hide(this.tabIndex, 0);
        }
        else
            this.updateTabName()
    };
};

Listview.funcBox.beforeUserCharacters = function()
{
    var ownProfile = (us_isOwnProfile() || (g_user.roles & (U_GROUP_ADMIN|U_GROUP_BUREAU)));

    if (ownProfile)
    {
        this.mode = 1;
        this.createCbControls = function(d, topBar)
        {
            if (!topBar && this.data.length < 15)
                return;

            var i = $WH.ce('input');
            i.type = 'button';
            i.value = LANG.button_remove;
            i.onclick = (function()
            {
                var rows = this.getCheckedRows();
                if (!rows.length)
                    alert(LANG.message_nocharacterselected);
                else if (confirm(LANG.confirm_unlinkcharacter))
                {
                    var ids = '';

                    $WH.array_walk(rows, function(x)
                    {
                        ids += x.id + ',';
                    });
                    ids = $WH.rtrim(ids, ',');
                    if (ids != '')
                        new Ajax('?profile=unlink&id=' + ids + '&user=' + g_pageInfo.username);
                    this.deleteRows(rows);
                }
            }).bind(this);
            $WH.ae(d, i);

            var i = $WH.ce('input');
            i.type = 'button';
            i.value = LANG.button_makepub;
            i.onclick = (function()
            {
                var rows = this.getCheckedRows();
                if (!rows.length)
                    alert(LANG.message_noprofileselected);
                else if (confirm(LANG.confirm_publicprofile))
                {
                    var ids = '';

                    $WH.array_walk(rows, function(x)
                    {
                        if (!x.published)
                        {
                            x.published = 1;
                            if (x.__tr != null)
                                x.__tr.__status.innerHTML = '';
                            ids += x.id + ','
                        }
                    });
                    ids = $WH.rtrim(ids, ',');
                    if (ids != '')
                        new Ajax('?profile=public&id=' + ids + '&user=' + g_pageInfo.username + '&bookmarked');
                    (Listview.cbSelect.bind(this, false))();
                }
            }).bind(this);
            $WH.ae(d, i);

            var i = $WH.ce('input');
            i.type = 'button';
            i.value = LANG.button_makepriv;
            i.onclick = (function()
            {
                var rows = this.getCheckedRows();
                if (!rows.length)
                    alert(LANG.message_noprofileselected);
                else if (confirm(LANG.confirm_privateprofile))
                {
                    var ids = '';

                    $WH.array_walk(rows, function(x)
                    {
                        if (x.published)
                        {
                            x.published = 0;
                            if (x.__tr != null)
                                x.__tr.__status.innerHTML = LANG.privateprofile;
                            ids += x.id + ','
                        }
                    });
                    ids = $WH.rtrim(ids, ',');
                    if (ids != '')
                        new Ajax('?profile=private&id=' + ids + '&user=' + g_pageInfo.username + '&bookmarked');
                    (Listview.cbSelect.bind(this, false))();
                }
            }).bind(this);
            $WH.ae(d, i);

            var i = $WH.ce('input');
            i.type = 'button';
            i.value = LANG.button_pin;
            i.onclick = (function()
            {
                var rows = this.getCheckedRows();
                if (!rows.length)
                    alert(LANG.message_nocharacterselected);
                else if (rows.length > 1)
                    alert(LANG.message_toomanycharacters);
                else if (confirm(LANG.confirm_pincharacter))
                {
                    var ids = [];
                    $WH.array_walk(rows, function(x) { ids.push(x.id) });
                    $WH.array_walk(this.data, function(x)
                    {
                        x.pinned = ($WH.in_array(ids, x.id) != -1);
                        if (x.__tr != null)
                        {
                            var a = $WH.gE(x.__tr, 'a')[1];
                            a.className = (x.pinned ? 'icon-star-right' : '');
                        }
                    });
                    ids = ids.join(',');
                    if (ids != '')
                        new Ajax('?profile=pin&id=' + ids + '&user=' + g_pageInfo.username);
                    (Listview.cbSelect.bind(this, false))();
                }
            }).bind(this);
            $WH.ae(d, i);

            var i = $WH.ce('input');
            i.type = 'button';
            i.value = LANG.button_unpin;
            i.onclick = (function()
            {
                var rows = this.getCheckedRows();
                if (!rows.length)
                    alert(LANG.message_nocharacterselected);
                else if (confirm(LANG.confirm_unpincharacter))
                {
                    var ids = [];
                    $WH.array_walk(rows, function(x) { ids.push(x.id) });
                    $WH.array_walk(this.data, function(x)
                    {
                        x.pinned = ($WH.in_array(ids, x.id) == -1);
                        if (x.__tr != null)
                        {
                            var a = $WH.gE(x.__tr, 'a')[1];
                            a.className = (x.pinned ? 'icon-star-right' : '');
                        }
                    });
                    ids = ids.join(',');
                    if (ids != '')
                        new Ajax('?profile=unpin&id=' + ids + '&user=' + g_pageInfo.username);
                    (Listview.cbSelect.bind(this, false))();
                }
            }).bind(this);
            $WH.ae(d, i);

            if (g_user.roles & (U_GROUP_ADMIN|U_GROUP_BUREAU))
            {
                var i = $WH.ce('input');
                i.type = 'button';
                i.value = LANG.button_resync;
                i.onclick = (function()
                {
                    var rows = this.getCheckedRows();
                    if (!rows.length)
                        alert(LANG.message_nocharacterselected);
                    else
                    {
                        var ids = '';

                        $WH.array_walk(rows, function(x)
                        {
                            ids += x.id + ','
                        });
                        ids = $WH.rtrim(ids, ',');
                        if (ids != '')
                        {
                            var div = $WH.ge('roster-status');
                            div.innerHTML = LANG.pr_queue_addqueue;
                            div.style.display = '';

                            new Ajax(
                                '?profile=resync&id=' + ids,
                                {
                                    method: 'POST',
                                    onSuccess: function(xhr, opt)
                                    {
                                        var result = parseInt(xhr.responseText);

                                        if (isNaN(result))
                                            alert(LANG.message_resyncerror + result);
                                        else if (result < 0 && result != -102)
                                            alert(LANG.message_resyncerror + '#' + result);

                                        pr_updateStatus('profile', div, ids, true);
                                    }
                                }
                            );
                        }
                        (Listview.cbSelect.bind(this, false))();
                    }
                }).bind(this);
                $WH.ae(d, i);
            }
        }
    }
};

Listview.funcBox.beforeUserProfiles = function()
{
    if (us_isOwnProfile())
    {
        this.mode = 1;
        this.createCbControls = function(d, topBar)
        {
            if (!topBar && this.data.length < 15)
                return;

            var i = $WH.ce('input');
            i.type = 'button';
            i.value = LANG.button_new;
            i.onclick = function() { document.location.href = '?profile&new' };
            $WH.ae(d, i);

            var i = $WH.ce('input');
            i.type = 'button';
            i.value = LANG.button_delete;
            i.onclick = (function()
            {
                var rows = this.getCheckedRows();
                if (!rows.length)
                    alert(LANG.message_noprofileselected);
                else if (confirm(LANG.confirm_deleteprofile))
                {
                    var ids = '';

                    $WH.array_walk(rows, function(x)
                    {
                        ids += x.id + ',';
                    });
                    ids = $WH.rtrim(ids, ',');
                    if (ids != '')
                        new Ajax('?profile=delete&id=' + ids);
                    this.deleteRows(rows);
                }
            }).bind(this);
            $WH.ae(d, i);

            var i = $WH.ce('input');
            i.type = 'button';
            i.value = LANG.button_makepub;
            i.onclick = (function()
            {
                var rows = this.getCheckedRows();
                if (!rows.length)
                    alert(LANG.message_noprofileselected);
                else if (confirm(LANG.confirm_publicprofile))
                {
                    var ids = '';

                    $WH.array_walk(rows, function(x)
                    {
                        if (!x.published)
                        {
                            x.published = 1;
                            if (x.__tr != null)
                                x.__tr.__status.innerHTML = '';
                            ids += x.id + ','
                        }
                    });
                    ids = $WH.rtrim(ids, ',');
                    if (ids != '')
                        new Ajax('?profile=public&id=' + ids);
                    (Listview.cbSelect.bind(this, false))();
                }
            }).bind(this);
            $WH.ae(d, i);

            var i = $WH.ce('input');
            i.type = 'button';
            i.value = LANG.button_makepriv;
            i.onclick = (function()
            {
                var rows = this.getCheckedRows();
                if (!rows.length)
                    alert(LANG.message_noprofileselected);
                else if (confirm(LANG.confirm_privateprofile))
                {
                    var ids = '';

                    $WH.array_walk(rows, function(x)
                    {
                        if (x.published)
                        {
                            x.published = 0;
                            if (x.__tr != null)
                                x.__tr.__status.innerHTML = LANG.privateprofile;
                            ids += x.id + ','
                        }
                    });
                    ids = $WH.rtrim(ids, ',');
                    if (ids != '')
                        new Ajax('?profile=private&id=' + ids);
                    (Listview.cbSelect.bind(this, false))();
                }
            }).bind(this);
            $WH.ae(d, i);
        }
    }
};

Listview.funcBox.beforeUserSignatures = function()
{
    if (us_isOwnProfile())
    {
        this.mode = 1;
        this.createCbControls = function(d, topBar)
        {
            if (!topBar && this.data.length < 15)
                return;

            var i = $WH.ce('input');
            i.type = 'button';
            i.value = LANG.button_delete;
            i.onclick = (function()
            {
                var rows = this.getCheckedRows();
                if (!rows.length)
                    alert(LANG.message_nosignatureselected);
                else if (confirm(LANG.confirm_deletesignature))
                {
                    var ids = '';

                    $WH.array_walk(rows, function(x)
                    {
                        ids += x.id + ',';
                    });
                    ids = $WH.rtrim(ids, ',');
                    if (ids != '')
                        new Ajax('?signature=delete&id=' + ids);
                    this.deleteRows(rows);
                    this.resetCheckedRows();
                    this.refreshRows();
                }
            }).bind(this);
            $WH.ae(d, i);

            var i = $WH.ce('input');
            i.type = 'button';
            i.value = LANG.button_edit;
            i.onclick = (function()
            {
                var rows = this.getCheckedRows();
                if (!rows.length)
                    alert(LANG.message_nosignatureselected);
                else if (rows.length > 1)
                    alert(LANG.message_toomanysignatures);
                else
                    document.location.href = '?signature=' + rows[0].id;
            }).bind(this);
            $WH.ae(d, i);

            var i = $WH.ce('input');
            i.type = 'button';
            i.value = LANG.button_markup;
            i.onclick = (function()
            {
                var rows = this.getCheckedRows();
                if (!rows.length)
                    alert(LANG.message_nosignatureselected);
                else if (rows.length > 1)
                    alert(LANG.message_toomanysignatures);
                else
                    prompt(LANG.prompt_signaturemarkup, '[url=' + this.getItemLink(rows[0]) + '][sig=' + rows[0].id + '][/url]');
            }).bind(this);
            $WH.ae(d, i);

            var i = $WH.ce('input');
            i.type = 'button';
            i.value = LANG.button_link;
            i.onclick = (function()
            {
                var rows = this.getCheckedRows();
                if (!rows.length)
                    alert(LANG.message_nosignatureselected);
                else if (rows.length > 1)
                    alert(LANG.message_toomanysignatures);
                else
                    prompt(LANG.prompt_signaturedirect, 'http://' + location.host + '?signature=generate&id=' + rows[0].id + '.png');
            }).bind(this);
            $WH.ae(d, i);
        }
    }
};

Listview.extraCols.signature = {
    id: 'signature',
    name: LANG.signature,
    before: 'name',
    align: 'left',
    compute: function(sig, td, tr)
    {
        var a = $WH.ce('a');
        a.style.fontFamily = 'Verdana, sans-serif';
        a.href = this.getItemLink(sig);
        a.rel = 'np';
        $WH.ae(a, $WH.ce('img', { src: '?signature=generate&id=' + sig.id + '.png', height: 60, width: 468 }));
        $WH.ae(td, a);
    }
};
