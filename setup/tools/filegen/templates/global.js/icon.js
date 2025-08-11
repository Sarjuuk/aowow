var Icon = {
    sizes:  ['small', 'medium', 'large'],
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
    idLookupCache: {},
    create: function(name, size, DELETEME, url, num, qty, noBorder, rel, span)
    {
        var
            icon  = $WH.ce(span ? 'span' : 'div'),
            image = $WH.ce('ins'),
            tile  = $WH.ce('del');

        if (size == null)
            size = 1;

        icon.className = 'icon' + Icon.sizes[size];

        $WH.ae(icon, image);

        if (!noBorder)
            $WH.ae(icon, tile);

        Icon.setTexture(icon, size, name);

        if (url)
        {
            var a = $WH.ce('a');
            a.href = url;
            if (url.indexOf('wowhead.com') == -1 && url.substr(0, 5) == 'http:')
                a.target = "_blank";

            $WH.ae(icon, a);
        }
        else if (name)
        {
            var _ = icon.firstChild.style;
            var avatarIcon = (_.backgroundImage.indexOf('/avatars/') != -1);

            if (!avatarIcon)
            {
                icon.onclick = Icon.onClick;

                if (url !== false)
                {
                    var a = $WH.ce('a');
                    a.href = "javascript:;";
                    $WH.ae(icon, a);
                }
            }
        }

        if (rel && typeof a != 'undefined')
            a.rel = rel;

        Icon.setNumQty(icon, num, qty);

        return icon;
    },

    createUser: function(avatar, avatarMore, size, url, premiumLevel, noBorder, reputationLevel)
    {
        if (avatar == 2)
            avatarMore = g_staticUrl + '/uploads/avatars/' + avatarMore + '.jpg';

        var icon = Icon.create(avatarMore, size, null, url, null, null, noBorder);

        if ((premiumLevel != Icon.STANDARD_BORDER) && Icon.premiumBorderClasses[premiumLevel])
            icon.className += ' ' + icon.className + Icon.premiumBorderClasses[premiumLevel];
        else if (reputationLevel && Icon.privilegeBorderClasses.hasOwnProperty(reputationLevel))
            icon.className += ' ' + icon.className + Icon.privilegeBorderClasses[reputationLevel];

        if (avatar == 2)
            Icon.moveTexture(icon, size, Icon.premiumOffsets[size][0], Icon.premiumOffsets[size][1], true);

        return icon;
    },

    getIdFromName: function (name, fn)
    {
        if (Icon.idLookupCache.hasOwnProperty(name))
        {
            window.requestAnimationFrame((function () {
                fn(Icon.idLookupCache[name] || undefined)
            }));

            return;
        }

        $.ajax({
            url: '?icon=get-id-from-name',
            data: { name: name },
            dataType: 'json',
            success: function (json) {
                Icon.idLookupCache[name] = json;
                fn(json || undefined);
            }
        })
    },

    getPrivilegeBorder: function(reputation)
    {
        var buff = false;
        if (reputation >= CFG_REP_REQ_BORDER_UNCOMMON)
            buff = 'uncommon';
        if (reputation >= CFG_REP_REQ_BORDER_RARE)
            buff = 'rare';
        if (reputation >= CFG_REP_REQ_BORDER_EPIC)
            buff = 'epic';
        if (reputation >= CFG_REP_REQ_BORDER_LEGENDARY)
            buff = 'legendary';

        return buff;
    },

    setTexture: function(icon, size, name)
    {
        if (!name)
            return;

        var _ = icon.firstChild.style;

        if (name.indexOf('/') != -1 || name.indexOf('?') != -1)
            _.backgroundImage = 'url(' + name + ')';
        else
        {
            _.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/' + Icon.sizes[size] + '/' + name.toLowerCase() + '.jpg)';
        }

        Icon.moveTexture(icon, size, 0, 0);
    },

    moveTexture: function(icon, size, x, y, exact)
    {
        var _ = icon.firstChild.style;

        if (x || y)
        {
            if (exact)
                _.backgroundPosition = x + 'px ' + y + 'px';
            else
                _.backgroundPosition = (-x * Icon.sizes2[size]) + 'px ' + (-y * Icon.sizes2[size]) + 'px';
        }
        else if (_.backgroundPosition)
            _.backgroundPosition = '';
    },

    setNumQty: function(icon, num, qty)
    {
        var _ = $WH.gE(icon, 'span');

        for (var i = 0, len = _.length; i < len; ++i)
            if (_[i])
                $WH.de(_[i]);

        if (num != null && ((num > 1 && num < 2147483647) || (num.length && num != '0' && num != '1')))
        {
            _ = g_createGlow(num, 'q1');
            _.style.right = '0';
            _.style.bottom = '0';
            _.style.position = 'absolute';
            $WH.ae(icon, _);
        }

        if (qty != null && qty > 0)
        {
            _ = g_createGlow('(' + qty + ')', 'q');
            _.style.left = '0';
            _.style.top = '0';
            _.style.position = 'absolute';
            $WH.ae(icon, _);
        }
    },

    getLink: function(icon)
    {
        return $WH.gE(icon, 'a')[0];
    },

    showIconName: function(x)
    {
        if (x.firstChild)
        {
            var _ = x.firstChild.style;

            if (_.backgroundImage.length && (_.backgroundImage.indexOf(g_staticUrl) >= 4 || g_staticUrl == ''))
            {
                var
                    start = _.backgroundImage.lastIndexOf('/'),
                    end   = _.backgroundImage.indexOf('.jpg');

                if (start != -1 && end != -1)
                    Icon.displayIcon(_.backgroundImage.substring(start + 1, end));
            }
        }
    },

    onClick: function()
    {
        Icon.showIconName(this);
    },

    displayIcon: function(icon)
    {
        if (!Dialog.templates.icondisplay)
        {
            var w = 364;
            switch (Locale.getId())
            {
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
                        compute: function(field, value, form, td)
                        {
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
                        id: 'iconId',
                        label: 'ID' + LANG.colon,
                        type: 'text',
                        labelAlign: 'left',
                        compute: function (field, value, form, td)
                        {
                            // td.classList.add('icon-dialog-content'); // aowow - style not yet defined
                            field.style.width = '200px';                // replace with hard value
                            field.value = '';
                            this.iconIdField = field;
                        }
                    },
                    {
                        id: 'location',
                        label: " ",
                        required: 1,
                        type: 'caption',
                        compute: function(field, value, form, th, tr)
                        {
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

                onInit: function(form)
                {
                    this.updateIcon = this.template.updateIcon.bind(this, form);
                },

                onShow: function(form)
                {
                    this.updateIcon();
                    if (location.hash && location.hash.indexOf('#icon') == -1)
                        this.oldHash = location.hash;
                    else
                        this.oldHash = '';

                    var hash = '#icon';

                    // Add icon name on all pages but item, spell and achievement pages (where the name is already available).
                    var nameDisabled = ($WH.isset('g_pageInfo') && g_pageInfo.type && $WH.in_array([3, 6, 10], g_pageInfo.type) == -1);
                    if (!nameDisabled)
                        hash += ':' + this.data.icon;

                    location.hash = hash;
                },

                onHide: function(form)
                {
                    if (this.oldHash)
                        location.hash = this.oldHash;
                    else
                        location.hash = '#.';
                },

                updateIcon: function(form)
                {
                    this.iconDiv.update();
                    var i = this.iconIdField;
                    Icon.getIdFromName(form.icon.value, (function (x) { i.value = x || ''; }));
                },

                onSubmit: function(unused, data, button, form)
                {
                    if (button == 'arrow')
                    {
                        var win = window.open(g_staticUrl + '/images/wow/icons/large/' + data.icon.toLowerCase() + '.jpg', '_blank');
                        win.focus();
                        return false;
                    }

                    return true;
                }
            };
        }

        if (!Icon.icDialog)
            Icon.icDialog = new Dialog();

        Icon.icDialog.show('icondisplay', { data: { icon: icon } });
    },

    checkPound: function()
    {
        if (location.hash && location.hash.indexOf('#icon') == 0)
        {
            var parts = location.hash.split(':');
            var icon = false;
            if (parts.length == 2)
            {
                icon = parts[1];
            }
            else if (parts.length == 1 && $WH.isset('g_pageInfo'))
            {
                switch (g_pageInfo.type)
                {
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

            if (icon)
                Icon.displayIcon(icon);
        }
    }
};

$(document).ready(Icon.checkPound);
