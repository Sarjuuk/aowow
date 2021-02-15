function ma_Init() {
    ma_AddOptions($WH.ge('maps-ek'), [1, 3, 4, 8, 10, 11, 12, 28, 33, 36, 38, 40, 41, 44, 45, 46, 47, 51, 85, 130, 139, 267, 1497, 1519, 1537, 3430, 3433, 3487, 4080, 4298]);
    ma_AddOptions($WH.ge('maps-kalimdor'), [14, 15, 16, 17, 141, 148, 215, 331, 357, 361, 400, 405, 406, 440, 490, 493, 618, 1377, 1637, 1638, 1657, 3524, 3525, 3557]);
    ma_AddOptions($WH.ge('maps-outland'), [3483, 3518, 3519, 3520, 3521, 3522, 3523, 3703]);
    ma_AddOptions($WH.ge('maps-northrend'), [65, 66, 67, 210, 394, 495, 2817, 3537, 3711, 4197, 4395, 4742]);
    ma_AddOptions($WH.ge('maps-battlegrounds'), [2597, 3277, 4384, 3358, 3820, 4710]);
    ma_AddOptions($WH.ge('maps-raids'), [
    //  1977, 2677, 2717, 3428, 3429, // Classic: Zul'Gurub, Blackwing Lair, Molten Core, Ahn'Qiraj, Ruins of Ahn'Qiraj
    //  3457, 3606, 3607, 3805, 3836, // BC:      Karazhan, Hyjal Summit, Serpentshrine Cavern, Zul'Aman, Magtheridon's Lair
    //  3845, 3923, 3959, 4075,       // BC:      Tempest Keep, Gruul's Lair, Black Temple, Sunwell Plateau
    /*2159,*/ 3456, 4273, 4493, 4500, // WotLK:   Onyxia's Lair, Naxxramas, Ulduar, The Obsidian Sanctum, The Eye of Eternity
        4603, 4722, 4812, 4987        // WotlK:   Vault of Archavon, Trial of the Crusader, Icecrown Citadel, The Ruby Sanctum
    ]);
    ma_AddOptions($WH.ge('maps-dungeons'), [
    //   209,  491,  717,  718,  719, // Classic: Shadowfang Keep, Razorfen Kraul, The Stockade, Wailing Caverns, Blackfathom Deeps
    //   721,  722,  796, 1176, 1337, // Classic: Gnomeregan, Razorfen Downs, Scarlet Monastery, Zul'Farrak, Uldaman
    //  1477, 1581, 1583, 1584, 2017, // Classic: The Temple of Atal'Hakkar, The Deadmines, Blackrock Spire, Blackrock Depths, Stratholme
    //  2057, 2100, 2437, 2557,       // Classic: Scholomance, Maraudon, Ragefire Chasm, Dire Maul
    //  2366, 2367, 3562, 3713, 3714, // BC:      The Black Morass, Old Hillsbrad Foothills, Hellfire Ramparts, The Blood Furnace, The Shattered Halls
    //  3715, 3716, 3717, 3789, 3790, // BC:      The Steamvault, The Underbog, The Slave Pens, Shadow Labyrinth, Auchenai Crypts
    //  3791, 3792, 3847, 3848, 3849, // BC:      Sethekk Halls, Mana-Tombs, The Botanica, The Arcatraz, The Mechanar
    //  4131,                         // BC:      Magisters' Terrace
         206, 1196, 4100, 4196, 4228, // WotlK:   Utgarde Keep, Utgarde Pinnacle, The Culling of Stratholme, Drak'Tharon Keep, The Oculus
        4264, 4265, 4272, 4277, 4415, // WotlK:   Halls of Stone, The Nexus, Halls of Lightning, Azjol-Nerub, The Violet Hold
        4416, 4494, 4723, 4809, 4813, // WotlK:   Gundrak, Ahn'kahet: The Old Kingdom, Trial of the Champion, The Forge of Souls, Pit of Saron
        4820,                         // WotlK:   Halls of Reflection
    ]);

    myMapper = new Mapper({
        parent: 'mapper-generic',
        editable: true,
        zoom: 1,
        onPinUpdate: ma_UpdateLink,
        onMapUpdate: ma_UpdateLink
    });

    var _ = location.href.indexOf('maps=');
    if (_ != -1) {
        _ = location.href.substr(_ + 5);
        if (myMapper.setLink(_)) {
            $WH.ge('mapper').style.display = '';
        }
    }
}

function ma_AddOptions(s, a) {
    a.sort(ma_Sort);

    $WH.array_apply(a, function (x) {
        var o = $WH.ce('option');
        o.value = x
        $WH.ae(o, $WH.ct(g_zones[typeof x == 'string' ? parseInt(x) : x]));
        $WH.ae(s, o);
    });
}

function ma_Sort(a, b) {
    if (typeof a == 'string') {
        a = parseInt(a);
    }

    if (typeof b == 'string') {
        b = parseInt(b);
    }

    return $WH.strcmp(g_zones[a], g_zones[b]);
}

function ma_ChooseZone(s) {
    if (s.value && s.value != '0') {
        if (myMapper.getZone() == 0) {
            $WH.ge('mapper').style.display = '';
        }

        myMapper.setZone(s.value);
    }

    s.selectedIndex = 0;
}

function ma_UpdateLink(_) {
    var
        b = '?maps',
        l = _.getLink();

    if (l) {
        b += '=' + l;
    }

    $WH.ge('link-to-this-map').href = b;
};
