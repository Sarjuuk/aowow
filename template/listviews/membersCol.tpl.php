Listview.extraCols.members = {
    id: 'members',
    name: LANG.members,
    after: 'name',
    sortable: false,
    type: 'text',
    compute: function(profile, td) {
        if (profile.members) {
            var
                mbs = profile.members,
                d   = $WH.ce('div');

            d.style.width = (26 * mbs.length) + 'px';
            d.style.margin = '0 auto';

            for (var i = 0, len = mbs.length; i < len; ++i) {
                var icon = Icon.create('class_' + g_file_classes[mbs[i][1]], 0, null, '?profile=' + profile.region + '.' + profile.realm + '.' + g_urlize(mbs[i][0], true));

                if (mbs[i][2])
                    icon.className += ' iconsmall-gold';

                icon.style.cssFloat = icon.style.styleFloat = 'left';
                $WH.ae(d, icon);
            }

            $WH.ae(td, d);
        }
    },
    getVisibleText: function(profile) {
        if (profile.members) {
            var
                buff = '',
                mbs = profile.members;

            for (var i = 0, len = mbs.length; i < len; ++i)
                buff += mbs[i][0] + ' ' + g_chr_classes[mbs[i][1]] + ' ';

            return buff.rtrim();
        }
    }
};
