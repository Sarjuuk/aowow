var
    tc_loaded     = false,
    tc_object,
    tc_classId    = -1,
    tc_classIcons = {},
    tc_build      = '',
    tc_glyphs     = '';

function tc_init() {
    var
        _,
        clear;

    // PageTemplate.set({ breadcrumb: [1, 0] });

    $WH.ge('tc-classes').className = 'choose';

    var classes = g_sortJsonArray(g_chr_classes, g_chr_classes);

    _ = $WH.ge('tc-classes-inner');
    for (var i = 0, len = classes.length; i < len; ++i) {
        var
            classId = classes[i],
            icon    = Icon.create('class_' + g_file_classes[classId], 1, null, 'javascript:;'),
            link    = Icon.getLink(icon);

        tc_classIcons[classId] = icon;

        if ($WH.Browser.ie6) {
            link.onfocus = tb;
        }

        link.onclick     = tc_classClick.bind(link, classId);
        link.onmouseover = tc_classOver.bind(link, classId);
        link.onmouseout  = $WH.Tooltip.hide;

        $WH.ae(_, icon);
    }
    clear = $WH.ce('div');
    clear.className = 'clear';
    $WH.ae(_, clear);

    tc_object = new TalentCalc();
    tc_object.initialize('tc-itself', {
        onChange: tc_onChange,
        mode: TalentCalc.MODE_DEFAULT
    });

    $WH.ge('tc-itself').className += ' choose';

    tc_readPound();
    setInterval(tc_readPound, 1000);
}

function tc_classClick(classId) {
    if (tc_object.setClass(classId)) {
        $WH.Tooltip.hide();
    }

    return false;
}

function tc_classOver(classId) {
    $WH.Tooltip.show(this, '<b>' + g_chr_classes[classId] + '</b>', 0, 0, 'c' + classId);
}

function tc_onChange(tc, info, data) {
    var _;

    if (info.classId != tc_classId) { // Class change
        if (!tc_loaded) {
            _ = $WH.ge('tc-itself');
            _.className = _.className.replace('choose', '');

            _ = $WH.ge('tc-classes');
            $WH.de($WH.gE(_, 'p')[0]); // Removes 'Choose a class:'
            _.className = '';

            tc_loaded = true;
        }

        if (tc_classId != -1) {
            _ = tc_classIcons[tc_classId];
            _.className = tc_classIcons[tc_classId].className.replace('iconmedium-gold-selected', '');
        }
        tc_classId = info.classId;

        _ = tc_classIcons[tc_classId];
        _.className += ' iconmedium-gold-selected';

        PageTemplate.set({ breadcrumb: [1, 0, tc_classId] });
    }

    tc_build  = tc.getWhBuild();
    tc_glyphs = tc.getWhGlyphs();

    var url = '#' + tc_build;
    if (tc_glyphs != '') {
        url += ':' + tc_glyphs
    }
    location.replace(url);

    var buff = document.title;
    if (buff.indexOf('/') != -1) {
        var pos = buff.indexOf('- ');
        if (pos != -1) {
            buff = buff.substring(pos + 2);
        }
    }
    document.title = g_chr_classes[tc_classId] + ' (' + data[0].k + '/' + data[1].k + '/' + data[2].k + ') - ' + buff;
}

function tc_readPound() {
    if (location.hash) {
        if (location.hash.indexOf('-') != -1) { // Blizz-Build
            var
                pounds  = location.hash.substr(1).split('-'),
                classs  = pounds[0] || '',
                build   = pounds[1] || '',
                classId = -1;

            for (var i in g_file_classes) {
                if (g_file_classes[i] == classs) {
                    classId = i;
                    break;
                }
            }

            if (classId != -1) {
                tc_object.setBlizzBuild(classId, build);
            }
        }
        else { // WH-Build
            var
                pounds = location.hash.substr(1).split(':'),
                build  = pounds[0] || '',
                glyphs = pounds[1] || '';

            if (tc_build != build) {
                tc_build = build;
                tc_object.setWhBuild(tc_build);
            }

            if (tc_glyphs != glyphs) {
                tc_glyphs = glyphs;
                tc_object.setWhGlyphs(tc_glyphs);
            }
        }
    }
};
