var
    pc_loaded     = false,
    pc_object,
    pc_classId    = -1,
    pc_classIcons = {},
    pc_build      = '';

function pc_init() {
    var
        _,
        clear;

    // PageTemplate.set({ breadcrumb: [1, 2] });

    $WH.ge('pc-classes').className = 'choose';

    var
        families = g_sortJsonArray(g_pet_families, g_pet_families),
        div = $WH.ce('div');

    div.className = 'pc-classes-inner-family';

    _ = $WH.ge('pc-classes-inner');
    for (var i = 0, len = families.length; i < len; ++i) {
        var
            classId = families[i],
            icon    = Icon.create(g_pet_icons[classId], 1, null, 'javascript:;'),
            link    = Icon.getLink(icon);

        pc_classIcons[classId] = icon;

        if ($WH.Browser.ie6) {
            link.onfocus = tb;
        }

        link.onclick     = pc_classClick.bind(link, classId);
        link.onmouseover = pc_classOver.bind(link, classId);
        link.onmouseout  = $WH.Tooltip.hide;

        $WH.ae(div, icon);
    }
    $WH.ae(_, div);

    clear = $WH.ce('div');
    clear.className = 'clear';
    $WH.ae(div, clear);

    clear = $WH.ce('div');
    clear.className = 'clear';
    $WH.ae(_, clear);

    pc_object = new TalentCalc();
    pc_object.initialize('pc-itself', {
        onChange: pc_onChange,
        mode: TalentCalc.MODE_PET
    });

    $WH.ge('pc-itself').className += ' choose';

    pc_readPound();
    setInterval(pc_readPound, 1000);
}

function pc_classClick(classId) {
    if (pc_object.setClass(classId)) {
        $WH.Tooltip.hide();
    }

    return false;
}

function pc_classOver(classId) {
    $WH.Tooltip.show(this, '<b>' + g_pet_families[classId] + '</b>');
}

function pc_onChange(tc, info, data) {
    var _;

    if (info.classId != pc_classId) { // Class change
        if (!pc_loaded) {
            _ = $WH.ge('pc-itself');
            _.className = _.className.replace('choose', '');

            _ = $WH.ge('pc-classes');
            $WH.de($WH.gE(_, 'p')[0]); // Removes 'Choose a pet family:'
            _.className = '';

            pc_loaded = true;
        }

        if (pc_classId != -1) {
            _ = pc_classIcons[pc_classId];
            _.className = pc_classIcons[pc_classId].className.replace('iconmedium-gold-selected', '');
        }
        pc_classId = info.classId;

        _ = pc_classIcons[pc_classId];
        _.className += ' iconmedium-gold-selected';

        PageTemplate.set({ breadcrumb: [1, 2, pc_classId] });
    }

    pc_build = tc.getWhBuild();

    location.replace('?petcalc#' + pc_build);

    var buff = document.title;
    if (buff.indexOf(' (') != -1) {
        var pos = buff.indexOf("- ");
        if (pos != -1) {
            buff = buff.substring(pos + 2);
        }
    }
    document.title = g_pet_families[pc_classId] + ' (' + data.k + ') - ' + buff;
}

function pc_readPound() {
    if (location.hash) {
        var build = location.hash.substr(1);

        if (pc_build != build) {
            pc_build = build;
            pc_object.setWhBuild(pc_build);
        }
    }
};
