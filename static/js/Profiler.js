var $WowheadProfiler;

function Profiler() {
    $WowheadProfiler = this;

    var
        STATUS_NONE        = 0,
        STATUS_WAITING     = 1,
        STATUS_SYNCING     = 2,
        STATUS_READY       = 3,
        STATUS_ERROR       = 4,
        STATUS_LOWPRIORITY = 5,

        _versionBuild = 14,

        _self = this,

        _profile  = {},
        _filter   = {},
        _items    = [],
        _activity = 0,

        _pageTitle,

        _container,
        _divHeader,
            _btnCharacter,
            _btnProfile,
            _icoProfile,
            _divLine1,
            _divLine2,
            _divLine3,
            _lnkEdit,
        _divMessage,
        _divTabLinks,
            _lnkFeedback,
            _lnkHelp,
            _lnkArmory,
        _divTabs,
        _divInventory,
            _btnCompare,
            _btnUpgrades,
            _tblInfobox,
                _ulQuickFacts,
                    _spGearScore,
                    _spAverageLevel,
                    _divSpec,
                    _spSkills,
                _trRaidProgress,
                    _tdRaidActivity,
                    _tdRaidCompletion,
                    _tdRaidGearMeter,
            _divStatistics,
            // _divAuras,
        _divTalents,
        _divPets,
        _divReputation,
        _divAchievements,
        _divTitles,
        _divQuests,
        _divCompanions,
        _divMounts,
        _divRecipes,

        _dialog,
        _tabs,
        _inventory,
        _statistics,
        _talents,
        _pets,
        _reputation,
        _achievements,
        _titles,
        _quests,
        _companions,
        _mounts,
        _recipes,

        _replaceJson = {
            atkpwr:          ['mleatkpwr',          'rgdatkpwr'],
            critstrkrtng:    ['mlecritstrkrtng',    'rgdcritstrkrtng',    'arcsplcritstrkrtng', 'firsplcritstrkrtng', 'frosplcritstrkrtng', 'holsplcritstrkrtng', 'natsplcritstrkrtng', 'shasplcritstrkrtng'],
            hastertng:       ['mlehastertng',       'rgdhastertng',       'splhastertng'],
            hitrtng:         ['mlehitrtng',         'rgdhitrtng',         'splhitrtng'],
            splpwr:          ['splheal',            'arcspldmg',          'firspldmg',          'frospldmg',          'holspldmg',          'natspldmg',           'shaspldmg'],
            spldmg:          ['arcspldmg',          'firspldmg',          'frospldmg',          'holspldmg',          'natspldmg',          'shaspldmg'],
            splcritstrkpct:  ['arcsplcritstrkpct',  'firsplcritstrkpct',  'frosplcritstrkpct',  'holsplcritstrkpct',  'natsplcritstrkpct',  'shasplcritstrkpct'],
            splcritstrkrtng: ['arcsplcritstrkrtng', 'firsplcritstrkrtng', 'frosplcritstrkrtng', 'holsplcritstrkrtng', 'natsplcritstrkrtng', 'shasplcritstrkrtng'],
            arcsplpwr:       ['arcspldmg'],
            firsplpwr:       ['firspldmg'],
            frosplpwr:       ['frospldmg'],
            holsplpwr:       ['holspldmg'],
            natsplpwr:       ['natspldmg'],
            shasplpwr:       ['shaspldmg']
        },

        _progress = [                                       // aowow: don't forget to enable tracking in includes/profiler.class.php
            { level: 284, instance: 5, heroic: 1, name: g_zones[4987] + $WH.sprintf(LANG.lvitem_dd, LANG.lvitem_raid25, ''), icon: 'spell_shadow_twilight',                    achievs: [4816], kills: [[4823, 39863]] },  // Ruby Sanctum 25 hc
            { level: 277, instance: 5, heroic: 1, name: g_zones[4812] + $WH.sprintf(LANG.lvitem_dd, LANG.lvitem_raid25, ''), icon: 'achievement_dungeon_icecrown_frostmourne', achievs: [4637], kills: [[4673, 37970], [4682, 37955], [4664, 37813], [4667, 36626], [4661, 847], [4656, 36855], [4642, 36612], [4679, 36678], [4670, 36627], [4685, 36853], [4676, 36789], [4688, 36597]] }, // Icecrown Citadel 25 hc
            { level: 271, instance: 5,            name: g_zones[4987] + $WH.sprintf(LANG.lvitem_dd, LANG.lvitem_raid25, ''), icon: 'spell_shadow_twilight',                    achievs: [4815], kills: [[4820, 39863]] }, // Ruby Sanctum 25 nh
            { level: 271, instance: 3, heroic: 1, name: g_zones[4987] + $WH.sprintf(LANG.lvitem_dd, LANG.lvitem_raid10, ''), icon: 'spell_shadow_twilight',                    achievs: [4818], kills: [[4822, 39863]] }, // Ruby Sanctum 10 hc
            { level: 264, instance: 5,            name: g_zones[4812] + $WH.sprintf(LANG.lvitem_dd, LANG.lvitem_raid25, ''), icon: 'achievement_dungeon_icecrown_frostmourne', achievs: [4608], kills: [[4672, 37970], [4681, 37955], [4663, 37813], [4666, 36626], [4660, 847], [4655, 36855], [4641, 36612], [4678, 36678], [4669, 36627], [4683, 36853], [4675, 36789], [4687, 36597]] }, // Icecrown Citadel 25 nh
            { level: 264, instance: 3, heroic: 1, name: g_zones[4812] + $WH.sprintf(LANG.lvitem_dd, LANG.lvitem_raid10, ''), icon: 'achievement_dungeon_icecrown_frostmourne', achievs: [4636], kills: [[4671, 37970], [4680, 37955], [4662, 37813], [4665, 36626], [4659, 847], [4654, 36855], [4640, 36612], [4677, 36678], [4668, 36627], [4684, 36853], [4674, 36789], [4686, 36597]] }, // Icecrown Citadel 10 hc
            { level: 258, instance: 3,            name: g_zones[4987] + $WH.sprintf(LANG.lvitem_dd, LANG.lvitem_raid10, ''), icon: 'spell_shadow_twilight',                    achievs: [4817], kills: [[4821, 39863]] }, // Ruby Sanctum 10 nh
            { level: 258, instance: 5, heroic: 1, name: g_zones[4722] + $WH.sprintf(LANG.lvitem_dd, LANG.lvitem_raid25, ''), icon: 'achievement_reputation_argentchampion',    achievs: [3812], kills: [[4031, 629],   [4034, 34780], [4038, 637],   [4042, 641],   [4046, 34564]] }, // Trial of the Crusader 25 hc
            { level: 251, instance: 3,            name: g_zones[4812] + $WH.sprintf(LANG.lvitem_dd, LANG.lvitem_raid10, ''), icon: 'achievement_dungeon_icecrown_frostmourne', achievs: [4532], kills: [[4648, 37970], [4651, 37955], [4645, 37813], [4646, 36626], [4644, 847], [4643, 36855], [4639, 36612], [4650, 36678], [4647, 36627], [4652, 36853], [4649, 36789], [4653, 36597]] }, // Icecrown Citadel 10 nh
            { level: 245, instance: 5,            name: g_zones[4722] + $WH.sprintf(LANG.lvitem_dd, LANG.lvitem_raid25, ''), icon: 'achievement_reputation_argentchampion',    achievs: [3916], kills: [[4029, 629],   [4035, 34780], [4039, 637],   [4043, 641],   [4047, 34564]] }, // Trial of the Crusader 25 nh
            { level: 245, instance: 3, heroic: 1, name: g_zones[4722] + $WH.sprintf(LANG.lvitem_dd, LANG.lvitem_raid10, ''), icon: 'achievement_reputation_argentchampion',    achievs: [3918], kills: [[4030, 629],   [4033, 34780], [4037, 637],   [4041, 641],   [4045, 34564]] }, // Trial of the Crusader 10 hc
            { level: 245, instance: 5,            name: g_zones[2159] + $WH.sprintf(LANG.lvitem_dd, LANG.lvitem_raid25, ''), icon: 'achievement_boss_onyxia',                  achievs: [4397], kills: [[1756, 10184]] }, // Onyxia's Lair 25
            { level: 232, instance: 3,            name: g_zones[4722] + $WH.sprintf(LANG.lvitem_dd, LANG.lvitem_raid10, ''), icon: 'achievement_reputation_argentchampion',    achievs: [3917], kills: [[4028, 629],   [4032, 34780], [4036, 637],   [4040, 641],   [4044, 34564]] }, // Trial of the Crusader 10 nh
            { level: 232, instance: 3,            name: g_zones[2159] + $WH.sprintf(LANG.lvitem_dd, LANG.lvitem_raid10, ''), icon: 'achievement_boss_onyxia',                  achievs: [4396], kills: [[1098, 10184]] }, // Onyxia's Lair 10
         // { level: 226, instance: 5,            zone: 4273, icon: 'achievement_dungeon_ulduarraid_misc_01',   achievs: [2895], kills: [[2872, 33113], [2873, 33186], [2874, 33118], [2884, 33293], [2875, 32930], [2879, 62932], [2880, 33271], [2882, 33515], [2883, 33288], [3236, 33993], [3257, 64985], [3256, 64899], [3258, 65074], [2881, 65184], [2885, 748]] }, // 25-man ulduar
         // { level: 219, instance: 3,            zone: 4273, icon: 'achievement_dungeon_ulduarraid_misc_01',   achievs: [2894], kills: [[2856, 33113], [2857, 33186], [2858, 33118], [2859, 33293], [2861, 32930], [2865, 62932], [2866, 33271], [2868, 33515], [2869, 33288], [2870, 33993], [2863, 64985], [2864, 65074], [2862, 64899], [2867, 65184], [2860, 748]] }, // 10-man ulduar
         // { level: 213, instance: 5,            zone: 3456, icon: 'achievement_dungeon_naxxramas_10man',      achievs: [577],  kills: [[1367, 16028], [1368, 15956], [1378, 15932], [1379, 16060], [1380, 15953], [1381, 15931], [1382, 15936], [1383, 692],   [1384, 16061], [1385, 16011], [1386, 15952], [1387, 15954], [1388, 15928], [1389, 15989], [1390, 15990]] }, // 25-man naxxramas
         // { level: 200, instance: 3,            zone: 3456, icon: 'achievement_dungeon_naxxramas_normal',     achievs: [576],  kills: [[1361, 15956], [1362, 15953], [1363, 15952], [1365, 15954], [1366, 16060], [1364, 16028], [1369, 15936], [1370, 16011], [1371, 15931], [1372, 15932], [1373, 15928], [1374, 16061], [1375, 692],   [1376, 15989], [1377, 15990]] }, // 10-man naxxramas
         // { level: 187, instance: 2, heroic: 1, name: LANG.pr_dungeons, icon: 'ability_rogue_feigndeath',     achievs: [489,490,491,492,493,494,495,496,497,498,499,500,4297,4298,4519,4520,4521], kills: [[1506, 29120], [1509, 31134], [1510, 29306], [1507, 29311], [1504, 23980], [1505, 26723], [1514, 26861], [1513, 27656], [1512, 28923]] }, // some lvl 80 Dungeons heroic
         // { level: 150, instance: 2,            name: LANG.pr_dungeons, icon: 'spell_holy_championsbond',     achievs: [477,478,479,480,481,482,483,484,485,486,487,488,3778,4296,4516,4517,4518], kills: [[1232, 29120], [1235, 31134], [1236, 29306], [1233, 29311], [1242, 23980], [1231, 26723], [1240, 26861], [1239, 27656], [1238, 28923]] }, // some lvl 80 Dungeons normal
        ];


    // *************************************************************
    // *************************************************************
    //                        Function Hooks
    // *************************************************************
    // *************************************************************


    g_items.add = function (id, json) {
        if (json.jsonequip) {
            _fixJson(json.jsonequip);

            if (json.jsonequip.socketbonusstat) {
                _fixJson(json.jsonequip.socketbonusstat);
            }

            if (json.jsonequip.subitems) {
                for (var i in json.jsonequip.subitems) {
                    if (json.jsonequip.subitems[i].jsonequip) {
                        _fixJson(json.jsonequip.subitems[i].jsonequip);
                    }
                }
            }
        }

        if (g_items[id] != null) {
            $WH.cO(g_items[id], json);
        }
        else {
            g_items[id] = json;
        }
    };

    // *************************************************************
    // *************************************************************
    //                         Public Methods
    // *************************************************************
    // *************************************************************

    this.initialize = function (container, opt) {
        _container = $WH.ge(container);
        if (!_container) {
            return;
        }

        _initData();
        _initHeader();
        _initProfiler();
        _initInfobox();

        if (opt && opt.id) {
            _requestProfile(opt.id);
        }
        else {
            _registerProfile({
                id: 0,
                name: LANG.pr_header_noname,
                region: ['', ''],
                battlegroup: ['', ''],
                realm: ['', ''],
                level: 80,
                classs: 1,
                race: 1,
                faction: 0,
                gender: 0,
                source: 0,
                sourcename: '',
                user: g_user.id,
                username: g_user.name,
                published: 1,
                nomodel: 0,
                talents: {},
                pets: [],
                skills: {},
                reputation: {},
                achievements: {},
                statistics: {},
                activity: {},
                titles: {},
                quests: {},
                spells: {},
                glyphs: {}
            });

            _talents.resetAll();
            _pets.resetAll();
            _lnkEdit.onclick();
        }
    };

    this.requestProfile = function (id) {
        _requestProfile(id);
    };

    this.registerProfile = function (data) {
        _registerProfile(data);
    };

    this.isArmoryProfile = function (allowCustoms) {
        return _isArmoryProfile(allowCustoms);
    };

    this.showGearScoreTooltip = function (total, e) {
        _showGearScoreTooltip(total, e);
    };

    this.updateInfoboxSpec = function () {
        _updateInfoboxSpec();
    };

    this.setPlayedTime = function (time) {
        _profile.playedtime = time;
        _updateInfobox();
    };

    this.setGlyphs = function (glyphs, who, when) {
        _profile.glyphs = $WH.dO(glyphs);

        _talents.setGlyphData(_profile.glyphs, who, when);
    };

    this.resetExclusions = function () {
        if (!confirm(LANG.confirm_resetexclusions)) {
            return;
        }

        g_user.excludegroups = 1;
        g_user.excludes = {};
        g_user.includes = {};

        $.post('?account=exclude', { reset: 1 });

        _mounts.updateExclusions();
        _companions.updateExclusions();
        _recipes.updateExclusions();
        _quests.updateExclusions();
        _achievements.updateExclusions();
        _titles.updateExclusions();
        _reputation.updateExclusions();

        Lightbox.hide();
    };

    this.loadOnDemand = function (wut, catg) {
        _mounts.onLoad(wut, catg);
        _companions.onLoad(wut, catg);
        _recipes.onLoad(wut, catg);
        _quests.onLoad(wut, catg);
        _achievements.onLoad(wut, catg);
        _titles.onLoad(wut, catg);
        _reputation.onLoad(wut, catg);
    };

    this.resync = function () {
        if (!_isArmoryProfile()) {
            return;
        }

        _divMessage.innerHTML = LANG.pr_queue_addqueue;
        _divMessage.style.display = '';

        new Ajax('?profile=resync&id=' + _profile.source, {
            method: 'POST',
            onSuccess: function (xhr, opt) {
                var result = parseInt(xhr.responseText);

                if (isNaN(result)) {
                    alert(LANG.message_resyncerror + ' ' + result);
                }
                else if (result < 0 && result != -102) {
                    alert(LANG.message_resyncerror + '#' + result);
                }

                pr_updateStatus('profile', _divMessage, _profile.source, true);
            }
        });
    };

    this.link = function () {
        if (!_isArmoryProfile() || !g_user.id) {
            return;
        }

        $WH.Tooltip.hide();
        if (confirm(LANG.confirm_linkcharacter)) {
            _profile.bookmarks.push(g_user.id);

            new Ajax('?profile=link&id=' + _profile.source);

            if (confirm(LANG.confirm_linkedcharacter)) {
                window.open('?user=' + g_user.name + '#characters');
            }
        }
    };

    this.unlink = function () {
        if (!_isArmoryProfile() || !g_user.id) {
            return;
        }

        $WH.Tooltip.hide();
        if (confirm(LANG.confirm_unlinkcharacter)) {
            $.each(g_user.characters, function (index, character) {
                if (character.id == _profile.source) {
                    character.pinned = false;
                }
            });

            _profile.bookmarks = $WH.array_filter(_profile.bookmarks, function (x) {
                return x != g_user.id
            });

            new Ajax('?profile=unlink&id=' + _profile.source);
        }
    };

    this.save = function () {
        if (_profile.user != g_user.id) {
            return;
        }

        if (_profile.id == 0) {
            if (!confirm(LANG.confirm_newprofile)) {
                return;
            }
        }

        var buildData = _talents.getTalentBuilds();

        // @TODO@ Update save format
        var params = [
                    'name=' + $WH.urlencode(_profile.name),
                   'level=' + $WH.urlencode(_profile.level),
                   'class=' + $WH.urlencode(_profile.classs),
                    'race=' + $WH.urlencode(_profile.race),
                  'gender=' + $WH.urlencode(_profile.gender),
                 'nomodel=' + $WH.urlencode(_profile.nomodel),
             'talenttree1=' + $WH.urlencode(buildData.spent[0]),
             'talenttree2=' + $WH.urlencode(buildData.spent[1]),
             'talenttree3=' + $WH.urlencode(buildData.spent[2]),
              'activespec=' + $WH.urlencode(buildData.active),
            'talentbuild1=' + $WH.urlencode(buildData.talents[0]),
                 'glyphs1=' + $WH.urlencode(buildData.glyphs[0]),
            'talentbuild2=' + $WH.urlencode(buildData.talents[1]),
                 'glyphs2=' + $WH.urlencode(buildData.glyphs[1]),
               'gearscore=' + $WH.urlencode(_profile.gearscore),
                    'icon=' + $WH.urlencode(_profile.icon),
                  'public=' + $WH.urlencode(_profile.published)
        ];

        if (_profile.description) {
            params.push('description=' + $WH.urlencode(_profile.description));
        }

        if (_profile.source) {
            params.push('source=' + _profile.source);
        }

        if (_profile.copy) {
            params.push('copy=' + _profile.copy);
        }

        var inventory = _inventory.getInventory();

        for (var i in inventory) {
            params.push('inv[]=' + i + ',' + inventory[i].join(','));
        }

        new Ajax('?profile=save&id=' + _profile.id, {
            method: 'POST',
            params: params.join('&'),
            onSuccess: function (xhr, opt) {
                var result = parseInt(xhr.responseText);

                if (isNaN(result)) {
                    alert(LANG.message_saveerror);
                }
                else {
                    if (result < 0) {
                        alert(LANG.message_saveerror);
                    }
                    else if (result != _profile.id) {
                        _updateSavedChanges(1);

                        location.href = '?profile=' + result;
                    }
                    else {
                        _profile.lastupdated = g_serverTime;

                        _updateSavedChanges(1);
                        _updateInfobox();

                        alert(LANG.message_saveok);
                    }
                }
            }
        });
    };

    this.saveAs = function () {
        var name = prompt(LANG.prompt_nameprofile, _profile.name);

        if (!name) {
            return;
        }
        else if (name.match(/`~!@#$%^&*()_\-+={[}\]|:;"'<,>.?\/]/)) {
            return alert(LANG.message_saveasinvalidname);
        }

        _editProfile({ id: 0, name: name, copy: _profile.id });

        _self.save();
    };

    // Named "remove" instead of "delete" to avoid syntax issues
    this.remove = function () {
        if (_profile.user != g_user.id) {
            return;
        }

        if (!confirm(LANG.confirm_deleteprofile2)) {
            return;
        }

        new Ajax('?profile=delete&id=' + _profile.id, {
            method: 'POST',
            onSuccess: function (xhr, opt) {
                location.href = '?user=' + g_user.name + '#profiles';
            }
        });
    };

    this.signature = function () {
        if (!_isArmoryProfile() || !g_user.id || !g_user.premium) {
            return;
        }

        window.open('?signature&profile=' + $WH.g_getGets().profile);
    };

    this.pin = function () {
        if (!_isArmoryProfile() || !g_user.id || !$WH.array_index(_profile.bookmarks, g_user.id) || _profile.pinned) {
            return;
        }

        $WH.Tooltip.hide();
        if (!confirm(LANG.confirm_pincharacter)) {
            return;
        }

        $.each(g_user.characters, function (index, character) {
            character.pinned = (character.id == _profile.source);
        });

        _profile.pinned = true;

        new Ajax('?profile=pin&id=' + _profile.source);
    };

    this.unpin = function () {
        if (!_isArmoryProfile() || !g_user.id || !$WH.array_index(_profile.bookmarks, g_user.id) || !_profile.pinned) {
            return;
        }

        $WH.Tooltip.hide();
        if (!confirm(LANG.confirm_unpincharacter)) {
            return;
        }

        $.each(g_user.characters, function (index, character) {
            character.pinned = false;
        });

        _profile.pinned = false;

        new Ajax('?profile=unpin&id=' + _profile.source);
    };

    this.updateSavedChanges = function (saved) {
        return _updateSavedChanges(saved);
    };

    this.updateGearScore = function () {
        return _updateGearScore();
    };

    this.equipItem = function (itemId, itemSlotId) {
        return _inventory.equipItem(itemId, itemSlotId);
    };

    this.equipSubitem = function (subitemId, slotId) {
        return _inventory.equipSubitem(subitemId, slotId);
    };

    this.socketItem = function (gemId, socketId, slotId) {
        return _inventory.socketItem(gemId, socketId, slotId);
    };

    this.enchantItem = function (enchantId, slotId) {
        return _inventory.enchantItem(enchantId, slotId);
    };

    this.selectPet = function (petId) {
        Lightbox.hide();
        return _selectPet(petId);
    };

    this.updateMenu = function (slotId, a) {
        return _inventory.updateMenu(slotId, a);
    };

    this.onMouseUpSlot = function (slotId, a, e) {
        return _inventory.onMouseUpSlot(slotId, a, e);
    };

    this.fixJson = function (json, scale) {
        _fixJson(json, scale);
    };

    this.unfixJson = function (json) {
        _unfixJson(json);
    };

    this.getVars = function () {
        return {
            profile:    _profile,
            statistics: _statistics,
            inventory:  _inventory,
            talents:    _talents,
            tabs:       _tabs,
            dialog:     _dialog
        };
    };


    // *************************************************************
    // *************************************************************
    //                         Private Methods
    // *************************************************************
    // *************************************************************


    function _initData() {
        for (var i in g_itemsets) {
            for (var j in g_itemsets[i].setbonus) {
                _fixJson(g_itemsets[i].setbonus[j]);
            }
        }

        for (var i in g_gems) {
            _fixJson(g_gems[i].jsonequip);
        }

        for (var i in g_enchants) {
            _fixJson(g_enchants[i].jsonequip);
        }
    }

    function _fixJson(json, scale) {
        for (var i in _replaceJson) {
            var
                value = json[i],
                replace = _replaceJson[i];

            if (value && replace.length) {
                for (var j = 0, len = replace.length; j < len; ++j) {
                    json[replace[j]] = (typeof value == 'object' ? (value.length ? value.slice(0) : $WH.dO(value)) : value);
                }


                delete json[i];

                if (scale) { // First value only
                    break;
                }
            }
        }
    }

    function _unfixJson(json) {
        var value;

        for (var i in _replaceJson) {
            var replace = _replaceJson[i];

            for (var j = 0, len = replace.length; j < len; ++j) {
                if (value = json[replace[j]]) {
                    json[i] = (typeof value == 'object' ? (value.length ? value.slice(0) : $WH.dO(value)) : value);
                }

                delete json[replace[j]];
            }
        }
    }

    function _initProfiler() {
        var
            _,
            __,
            a,
            tabIndex;


        _divTabs = _ = $WH.ce('div');
        _.className = 'profiler-tabs';
        $WH.ae(_container, _);

        _tabs = new Tabs({ parent: _, onShow: _onShowTabs });
        _statistics = new ProfilerStatistics(_self);
        _talents = new ProfilerTalents(_self);

        _ = $WH.ce('div');
        _.id = 'tab-inventory';
        _.style.display = 'none';
        _divInventory = __ = $WH.ce('div');
        __.style.display = 'none';
        $WH.ae(_, _divInventory);
        $WH.ae(_container, _);

        _inventory = new ProfilerInventory(_self);
        tabIndex = _tabs.add(LANG.tab_character, { id: 'inventory' });
        _inventory.initialize(_divInventory, tabIndex);

        _divStatistics = $WH.ce('div');
        _divInventory.insertBefore(_divStatistics, _divInventory.childNodes[3]);

        _statistics.initialize(_divStatistics, tabIndex);

/* custom auras */

        // var tbl = $WH.ce('table');

        // _divAuras = $WH.ce('div');
        // _divAuras.id = 'aura-container';
        // _divAuras.style.cssFloat = 'left';
        // _divAuras.style.marginLeft = '390px';
        // _divAuras.style.width = '325px';
        // _divAuras.style.display = 'none';

        // _ = $WH.ce('h3');
        // $WH.ae(_, $WH.ct('[Auras]'));
        // $WH.ae(_divAuras, _);

        // tbl.className = 'iconlist';

        // $WH.ae(_divAuras, tbl);
        // $WH.aef(_divInventory, _divAuras);

/* end custom */

        _ = $WH.ce('div');
        _.id = 'tab-talents';
        _.style.display = 'none';
        _divTalents = __ = $WH.ce('div');
        __.style.display = 'none';
        $WH.ae(_, _divTalents);
        $WH.ae(_container, _);

        tabIndex = _tabs.add(LANG.tab_talents, { id: 'talents' });
        _talents.initialize(_divTalents, tabIndex);

        _ = $WH.ce('div');
        _.id = 'tab-pets';
        _.style.display = 'none';
        _divPets = __ = $WH.ce('div');
        __.style.display = 'none';
        $WH.ae(_, _divPets);
        $WH.ae(_container, _);

        _pets = new ProfilerTalents(_self);
        tabIndex = _tabs.add(LANG.tab_pets, { id: 'pets', hidden: 1 });
        _pets.initialize(_divPets, tabIndex);

        _ = $WH.ce('div');
        _.id = 'tab-mounts';
        _.style.display = 'none';
        _divMounts = __ = $WH.ce('div');
        __.style.display = 'none';
        $WH.ae(_, _divMounts);
        $WH.ae(_container, _);

        _mounts = new ProfilerCompletion(_self);
        tabIndex = _tabs.add(LANG.tab_mounts, { id: 'mounts' });
        _mounts.initialize(_divMounts, tabIndex, {
            template: 'gallery',
            noAd: 1,
            onDemand: 1,
            source: 'g_spells',
            catgid: 'skill',
            subcat: 'cat',
            typeid: 6,
            filter: function (spell) {
                return (spell.cat == -5 || spell.cat == 7);
            },
            models: 1
        });

        _ = $WH.ce('div');
        _.id = 'tab-companions';
        _.style.display = 'none';
        _divCompanions = __ = $WH.ce('div');
        __.style.display = 'none';
        $WH.ae(_, _divCompanions);
        $WH.ae(_container, _);

        _companions = new ProfilerCompletion(_self);
        tabIndex = _tabs.add(LANG.tab_companions, { id: 'companions' });
        _companions.initialize(_divCompanions, tabIndex, {
            template: 'gallery',
            noAd: 1,
            onDemand: 1,
            source: 'g_spells',
            catgid: 'skill',
            subcat: 'cat',
            typeid: 6,
            filter: function (spell) {
                return spell.cat == -6;
            },
            models: 1
        });

        _ = $WH.ce('div');
        _.id = 'tab-recipes';
        _.style.display = 'none';
        _divRecipes = __ = $WH.ce('div');
        __.style.display = 'none';
        $WH.ae(_, _divRecipes);
        $WH.ae(_container, _);

        _recipes = new ProfilerCompletion(_self);
        tabIndex = _tabs.add(LANG.tab_recipes, { id: 'recipes' });
        _recipes.initialize(_divRecipes, tabIndex, {
            template: 'spell',
            onDemand: 1,
            dataArgs: function (x) {
                var _ = [185, 129, 356];

                for (var i in x.skills) {
                    _.push(i);
                }

                return '&skill=' + _.join(',');
            },
            source: 'g_spells',
            order: 'g_skill_order',
            catgs: 'g_spell_skills',
            catgid: 'skill',
            subcat: 'cat',
            typeid: 6,
            filter: function (spell) {
                return spell.cat == 11 || spell.cat == 9;
            },
            reqcatg: 1,
            noempty: 1
        });

        _ = $WH.ce('div');
        _.id = 'tab-quests';
        _.style.display = 'none';
        _divQuests = __ = $WH.ce('div');
        __.style.display = 'none';
        $WH.ae(_, _divQuests);
        $WH.ae(_container, _);

        _quests = new ProfilerCompletion(_self);
        tabIndex = _tabs.add(LANG.tab_quests, { id: 'quests' });
        _quests.initialize(_divQuests, tabIndex, {
            template: 'quest',
            onDemand: 1,
            partial: 1,
            dataArgs: function (x) {
                return '&partial';
            },
            source: 'g_quests',
            order: 'g_quest_catorder',
            catgs: 'g_quest_categories',
            catgid: 'category2',
            subcat: 'category',
            typeid: 5,
            overall: -1,
            reqcatg: 1,
            nosubcatg: [4],
            subname: function (c) {
                return Listview.funcBox.getQuestCategory(c)
            }
        });

        _ = $WH.ce('div');
        _.id = 'tab-achievements';
        _.style.display = 'none';
        _divAchievements = __ = $WH.ce('div');
        __.style.display = 'none';
        $WH.ae(_, _divAchievements);
        $WH.ae(_container, _);

        _achievements = new ProfilerCompletion(_self);
        tabIndex = _tabs.add(LANG.tab_achievements, { id: 'achievements' });
        _achievements.initialize(_divAchievements, tabIndex, {
            template: 'achievement',
            source: 'g_achievements',
            order: 'g_achievement_catorder',
            catgs: 'g_achievement_categories',
            catgid: 'parentcat',
            subcat: 'category',
            typeid: 10,
            nototal: [81],
            points: 1,
            overall: 0,
            compute: function (achievement, x) {
                if (achievement.parentcat == -1) {
                    achievement.parentcat = achievement.category;
                }

                return x;
            },
            subname: function (c) {
                return g_achievement_categories[c];
            }
        });

        _ = $WH.ce('div');
        _.id = 'tab-titles';
        _.style.display = 'none';
        _divTitles = __ = $WH.ce('div');
        __.style.display = 'none';
        $WH.ae(_, _divTitles);
        $WH.ae(_container, _);

        _titles = new ProfilerCompletion(_self);
        tabIndex = _tabs.add(LANG.tab_titles, { id: 'titles' });
        _titles.initialize(_divTitles, tabIndex, {
            template: 'title',
            noAd: 1,
            source: 'g_titles',
            catgid: 'category',
            typeid: 11
        });

        _ = $WH.ce('div');
        _.id = 'tab-reputation';
        _.style.display = 'none';
        _divReputation = __ = $WH.ce('div');
        __.style.display = 'none';
        $WH.ae(_, _divReputation);
        $WH.ae(_container, _);

        _reputation = new ProfilerCompletion(_self);
        tabIndex = _tabs.add(LANG.tab_reputation, { id: 'reputation' });
        _reputation.initialize(_divReputation, tabIndex, {
            template: 'faction',
            onDemand: 'factions',
            source: 'g_factions',
            order: 'g_faction_order',
            catgs: 'g_faction_categories',
            catgid: 'category2',
            subcat: 'category',
            typeid: 8,
            compute: function (faction, x) {
                faction.standing = x | 0;
                return x >= 42000 || _achievements.isComplete(faction.achievement);
            }
        });

        _tabs.flush();
    }

    function _updateSavedChanges(saved) {
        if (saved) {
            _divMessage.style.display = 'none';
            _divMessage.style.backgroundImage = '';
        }
        else {
            _divMessage.innerHTML = $WH.sprintf(LANG.message_profilenotsaved, '<span style="color: red">', '</span>');
            _divMessage.style.display = '';
            _divMessage.style.backgroundImage = 'none';
        }
        // can't have javascript:; hrefs all over the place with an onbeforeunload on IE
        if (!$WH.Browser.ie) {
            window.onbeforeunload = (saved ? null : function (e) {
                return LANG.message_savebeforeexit;
            });
        }
    }

    function _updateDefaultIcon() {
        var icon = $WH.g_getProfileIcon(_profile.race, _profile.classs, _profile.gender, _profile.level, 0, 'large');
        // aowow - and another bugged icon request
        // var icon = $WH.g_getProfileIcon(_profile.race, _profile.classs, _profile.gender, _profile.level, _profile.source, 'large');

        if (!_profile.icon) {
            _profile.icon = icon;
        }

        _profile.defaulticon = (_profile.icon == icon);
    }

    function _initHeader() {
        var
            _,
            __,
            a;

        _dialog = new Dialog();

        _divHeader = _ = $WH.ce('div');
        _.className = 'profiler-header';
        _btnProfile = RedButton.create(LANG.button_customprofile, true, _showProfileMenu);
        _btnCharacter = RedButton.create(LANG.button_armorychar, true, _showCharacterMenu);

        $WH.ae(_, _btnCharacter);
        $WH.ae(_, _btnProfile);

        _icoProfile = __ = Icon.create(null, 2);
        $WH.ae(_, __);

        _divLine1 = __ = $WH.ce('div');
        __.className = 'profiler-header-line1';
        $WH.ae(_, __);

        _divLine2 = __ = $WH.ce('div');
        __.className = 'profiler-header-line2';
        $WH.ae(_, __);

        _divLine3 = __ = $WH.ce('div');
        __.className = 'profiler-header-line3';
        $WH.ae(__, $WH.ce('span'));
        _lnkEdit = a = $WH.ce('a');
        a.className = 'profiler-header-editlink icon-edit';
        a.href = 'javascript:;';
        a.onclick = _dialog.show.bind(null, 'profileredit', { data: _profile, onSubmit: _editProfile });
        $WH.ae(a, $WH.ct(LANG.pr_header_edit));
        $WH.ae(__, a);
        $WH.ae(_, __);

        $WH.ae(_container, _);

        _divMessage = _ = $WH.ce('div');
        _.className = 'profiler-message';
        _.style.display = 'none';
        $WH.ae(_container, _);

        _divTabLinks = _ = $WH.ce('div');
        _.className = 'profiler-tablinks';
        $WH.ae(_container, _);

        _lnkHelp = _ = $WH.ce('a');
        _.className = 'profiler-tablinks-help';
        _.href = '?help=profiler';
        _.target = '_blank';
        $WH.ae(_, $WH.ct(LANG.pr_header_help));
        $WH.ae(_divTabLinks, _);

        _lnkArmory = _ = $WH.ce('a');
        _.className = 'profiler-tablinks-armory';
        _.href = '#';
        _.style.display = 'none';
        $WH.ae(_, $WH.ct(LANG.pr_header_armory));
        $WH.ae(_divTabLinks, _);
    }

    function _updateHeader() {
        var _;

        // ************************
        // Page Title

        if (_pageTitle == null) {
            _pageTitle = document.title;
        }

        var buff = _profile.name;
        if (_isArmoryProfile()) {
            buff += ' (';
            buff += _profile.realm[1] + LANG.hyphen + _profile.region[1];
            buff += ')';
        }
        buff += LANG.hyphen + _pageTitle;
        document.title = buff;

        // ************************
        // Breadcrumb

        if (_isArmoryProfile(_profile.genuine)) {
            PageTemplate.set({ breadcrumb: [1, 5, 0, _profile.region[0], _profile.battlegroup[0], _profile.realm[0]] });
        }
        else {
            PageTemplate.set({ breadcrumb: [1, 5, 1] });
        }

        // ************************
        // Buttons

        if (!_isArmoryProfile()) {
            _btnCharacter.style.display = 'none';
        }

        // ************************
        // Header

        // Icon
        Icon.setTexture(_icoProfile, 2, _profile.icon);

        // Line 1
        $WH.ee(_divLine1);

        if (_isArmoryProfile() && _profile.title && g_titles[_profile.title]) {
            var
                title = g_titles[_profile.title].name,
                affix = $WH.str_replace(title, '%s', '');

            var n = $WH.ce('b');
            $WH.st(n, _profile.name);

            if (!_tabs.tabs[8].hidden) {
                var sp = $WH.ce('a');
                sp.href = '#titles';
                sp.onclick = Tabs.onClick.bind(_tabs.tabs[8], sp);
                $WH.ns(sp);
            }
            else {
                sp = $WH.ce('span');
            }

            $WH.st(sp, $WH.trim(affix));

            if (title.indexOf('%s') > 0) { // Prefix title
                $WH.ae(_divLine1, sp);
                if ($WH.trim(affix) != affix) {
                    $WH.ae(_divLine1, $WH.ct(' '));
                }
                $WH.ae(_divLine1, n);
            }
            else if (title.indexOf('%s') == 0) { // Suffix title
                $WH.ae(_divLine1, n);
                if ($WH.trim(affix) != affix) {
                    $WH.ae(_divLine1, $WH.ct(' '));
                }
                $WH.ae(_divLine1, sp);
            }
        }
        else {
            _ = $WH.ce('b');
            $WH.ae(_, $WH.ct(_profile.name));
            $WH.ae(_divLine1, _);
        }

        // Line 2
        $WH.ee(_divLine2);

        if (_isArmoryProfile(_profile.genuine) && !_profile.description) {
            if (_profile.guild) {
                _ = $WH.ce('var');
                $WH.ae(_, $WH.ct('<'));
                $WH.ae(_divLine2, _);

                _ = $WH.ce('a');
                _.href = '?guild=' + _profile.region[0] + '.' + _profile.realm[0] + '.' + g_urlize(_profile.guild, true);
                $WH.ae(_, $WH.ct(_profile.guild));
                $WH.ae(_divLine2, _);

                _ = $WH.ce('var');
                $WH.ae(_, $WH.ct('>'));
                $WH.ae(_divLine2, _);
            }
        }
        else if (_profile.description) {
            $WH.ae(_divLine2, $WH.ct(_profile.description));
        }

        // Line 3
        _divLine3.firstChild.innerHTML = $WH.sprintfa(LANG.pr_header_character, _profile.level, g_chr_races[_profile.race], g_chr_classes[_profile.classs], _profile.race, _profile.classs);

        // Armory Link
        // Aowow custom
        // if (_isArmoryProfile(_profile.genuine)) {
            // _lnkArmory.href = 'http://' + _profile.region[0] + '.battle.net/wow/' + Locale.getName().substr(0, 2) + '/character/' + _profile.realm[0] + '/' + g_cleanCharacterName(_profile.name) + '/';
            // _lnkArmory.style.display = '';
            // _lnkArmory.target = '_blank';
        // }
        // else {
            _lnkArmory.style.display = 'none';
        // }
    }

/* Aowow custom
    function _updateAuras() {
        var
            show = false,
            tbl  = $WH.gE(_divAuras, 'table')[0];

        for (j in _profile.auras) {
            if (!g_spells[ _profile.auras[j]]) {
                continue;
            }

            spl = g_spells[_profile.auras[j]];

            // can use jsonstats for flat modifiers
            _statistics.addModifiers('spell', spl.id, spl.modifier || 0);

            var
                 tr = $WH.ce('tr'),
                 th = $WH.ce('th'),
                 td = $WH.ce('td'),
                  a = $WH.ce('a')

            var toggleAura = function (spell, td, e) {
                e = $WH.$E(e);

                if (e._button != 3 && !e.shiftKey && !e.ctrlKey)
                    return true;

                if (td.className == 'q1') {
                    td.className = 'q0';
                    td.previousSibling.style.opacity = 0.5;
                    _statistics.removeModifiers('spell', spell.id)
                }
                else {
                    td.className = 'q1';
                    td.previousSibling.style.opacity = 1;
                    _statistics.addModifiers('spell', spell.id, spell.modifier || 0);
                }

                return false;
            };

            th.align = 'right';
            th.appendChild(g_spells.createIcon(spl.id, 0, 0));
            $WH.ae(tr, th);

            $WH.ae(a, $WH.ct(spl['name']));
            a.href = '?spell=' + spl.id;
            a.rel = 'buff';
            $WH.ae(td, a);

            td.className = 'q1';
            td.onmouseup = toggleAura.bind(null, spl, td);
            td.oncontextmenu = $WH.rf;
            td.onclick = function (e) {
                e = $WH.$E(e);
                if (e.shiftKey || e.ctrlKey) {
                    return false;
                }
            };

            $WH.ae(tr, td);
            $WH.ae(tbl, tr);
            show = true;
        }

        _divAuras.style.display = show ? 'block' : 'none';
    }
*/
    function _showCharacterMenu(e) {
        if (!_isArmoryProfile()) {
            return;
        }

        e = $WH.$E(e);

        var menu = [[0, LANG.button_resync, _self.resync]];

        if (g_user.id) {
            if (!$WH.array_index(_profile.bookmarks, g_user.id)) {
                menu.push([1, LANG.button_claimchar, _self.link]);
            }
            else {
                menu.push([1, LANG.button_remove, _self.unlink]);

                if (_profile.pinned) {
                    menu.push([2, LANG.button_unpin, _self.unpin]);
                }
                else {
                    menu.push([2, LANG.button_pin, _self.pin]);
                }

                menu.push([3, LANG.button_signature, _self.signature]);
            }
        }

        var pos = $WH.g_getCursorPos(e);
        Menu.showAtXY(menu, pos.x, pos.y);
    }

    function _showProfileMenu(e) {
        e = $WH.$E(e);

        var menu = [[0, LANG.button_new, '?profile&new']];

        if (g_user.id) {
            if (_profile.id > 0 && g_user.id == _profile.user) { // Own profile
                menu.push([1, LANG.button_save, _self.save]);
            }
            menu.push([2, LANG.button_saveas, _self.saveAs]);
            if (_profile.id > 0 && g_user.id == _profile.user) { // Own profile
                menu.push([3, LANG.button_delete, _self.remove]);
            }
        }

        var pos = $WH.g_getCursorPos(e);
        Menu.showAtXY(menu, pos.x, pos.y);
    }

    function _isArmoryProfile(allowCustoms) {
        return (
            _profile.region && _profile.region[0] &&
            _profile.battlegroup && _profile.battlegroup[0] &&
            _profile.realm && _profile.realm[0] &&
            (allowCustoms || !_profile.user)
        );
    }

    function _initInfobox() {
        var
            tb = $WH.ce('tbody'),
            tr  = $WH.ce('tr'),
            th  = $WH.ce('th'),
            td  = $WH.ce('td'),
            div = $WH.ce('div'),
            ul  = $WH.ce('ul');

        _tblInfobox = $WH.ce('table');
        _tblInfobox.className = 'infobox';

        th.colSpan = 3;
        td.colSpan = 3;

        $WH.ae(th, $WH.ct(LANG.pr_qf_quickfacts));
        $WH.ae(tr, th);
        $WH.ae(tb, tr);

        tr = $WH.ce('tr');

        div.className = 'infobox-spacer';
        $WH.ae(td, div);

        _ulQuickFacts = ul;
        $WH.ae(td, ul);

        $WH.ae(tr, td);
        $WH.ae(tb, tr);

        _trRaidProgress = tr = $WH.ce('tr');
        $WH.ae(tb, tr);

        var a = $WH.ce('a');
        a.className = 'tip';
        a.onclick = function (e) {
            $WH.Tooltip.hide();
            _activity = 1 - _activity;
            _updateInfoboxActivity();
        };
        a.onmouseover = function (e) {
            $WH.Tooltip.showAtCursor(e, LANG['pr_qf_activitytip' + (_activity ? 1 : 2)], 0, 0, 'q');
        };
        a.onmousemove = $WH.Tooltip.cursorUpdate;
        a.onmouseout = $WH.Tooltip.hide;

        th = $WH.ce('th');
        th.style.paddingLeft = '6px';
        th.style.paddingRight = 0;

        $WH.ae(th, a);
        $WH.ae(tr, th);

        th = $WH.ce('th');
        th.colSpan = 2;
        th.style.paddingLeft = 0;
        th.style.paddingRight = '6px';

        $WH.st(th, LANG.pr_qf_gearmeter);
        $WH.ae(tr, th);

        tr = $WH.ce('tr');
        $WH.ae(tb, tr);

        _tdRaidActivity = $WH.ce('td');
        _tdRaidActivity.className = 'profiler-infobox-raids';
        _tdRaidActivity.style.padding = '0 0 0 6px';
        $WH.ae(tr, _tdRaidActivity);

        _tdRaidCompletion = $WH.ce('td');
        _tdRaidCompletion.className = 'profiler-infobox-gear';
        _tdRaidCompletion.style.padding = 0;
        $WH.ae(tr, _tdRaidCompletion);

        _tdRaidGearMeter = $WH.ce('td');
        _tdRaidGearMeter.className = 'profiler-infobox-gear';
        _tdRaidGearMeter.style.padding = '2px 6px 0 0';
        $WH.ae(tr, _tdRaidGearMeter);

        $WH.ae(_tblInfobox, tb);

        $WH.aef(_divInventory, _tblInfobox);
    }

    function _updateInfobox() {
        var
            li,
            div,
            sp,
            a;

        // ************************
        // Quick Facts

        $WH.ee(_ulQuickFacts);

        // Updated
        if (_profile.lastupdated) {
            var elapsed = (g_serverTime - _profile.lastupdated) / 1000;

            li  = $WH.ce('li');
            div = $WH.ce('div');
            sp  = $WH.ce('span');

            $WH.ae(div, $WH.ct((_isArmoryProfile() ? LANG.pr_qf_resynced : LANG.pr_qf_updated)));
            g_formatDate(sp, elapsed, new Date(_profile.lastupdated));

            if (elapsed > (60 * 60 * 24)) { // 1 Day
                sp.style.color = 'red';
                sp.style.fontWeight = 'bold';
            }

            $WH.ae(div, sp);
            $WH.ae(li, div);
            $WH.ae(_ulQuickFacts, li);
        }

        // Owner
        if (_profile.user && _profile.username) {
            li = $WH.ce('li');
            div = $WH.ce('div');
            a = $WH.ce('a');

            a.href = '?user=' + _profile.username;

            $WH.st(a, _profile.username);
            $WH.ae(div, $WH.ct(LANG.pr_qf_owner));
            $WH.ae(div, a);
            $WH.ae(li, div);
            $WH.ae(_ulQuickFacts, li);
        }

        // Source character
        if (_profile.user && _profile.source) {
            li  = $WH.ce('li');
            div = $WH.ce('div');
            a   = $WH.ce('a');

            a.href = g_getProfileUrl({
                id:     _profile.id,
                region: _profile.region[0],
                realm:  _profile.realm[0],
                name:   _profile.sourcename
            });

            $WH.st(a, _profile.sourcename + ' (' + _profile.realm[1] + ' - ' + _profile.region[0].toUpperCase() + ')');
            $WH.ae(div, $WH.ct(LANG.pr_qf_character));
            $WH.ae(div, a);
            $WH.ae(li, div);
            $WH.ae(_ulQuickFacts, li);
        }

        // Played time
        if (_profile.playedtime) {
            li = $WH.ce('li');
            div = $WH.ce('div');

            $WH.st(div, LANG.pr_qf_playedtime + g_formatTimeElapsed(_profile.playedtime));
            $WH.ae(li, div);
            $WH.ae(_ulQuickFacts, li);
        }

        // Custom profiles
        if (_isArmoryProfile() && _profile.customs)
        {
            var
                t   = $WH.ce('table'),
                tb  = $WH.ce('tbody'),
                li  = $WH.ce('li'),
                div = $WH.ce('div'),
                k   = 0;

            t.className = 'iconlist';

            $WH.ae(t, tb);
            $WH.ae(div, $WH.ct(LANG.pr_qf_profiles));
            $WH.ae(div, t);
            $WH.ae(li, div);

            // Sorting
            var temp = []
            for (var i in _profile.customs)
                temp.push([i, _profile.customs[i][0]]);
            temp.sort(function(a, b) { return $WH.strcmp(a[1], b[1]) });

            for (var j = 0, len = temp.length; j < len; ++j)
            {
                i = temp[j][0];

                if (_profile.customs[i][1] && _profile.customs[i][1] != g_user.id)
                    continue;

                var
                    tr = $WH.ce('tr'),
                    th = $WH.ce('th'),
                    td = $WH.ce('td'),
                    a  = $WH.ce('a');

                td.style.lineHeight = '1em';
                td.style.padding = '0px';

                $WH.ae(th, Icon.create((_profile.customs[i].length == 3 && _profile.customs[i][2] ? _profile.customs[i][2] : 'inv_misc_questionmark'), 0, null, '?profile=' + i));
                $WH.ae(tr, th);

                a.href = '?profile=' + i;

                $WH.st(a, _profile.customs[i][0]);
                $WH.ae(td, a);
                $WH.ae(tr, td);
                $WH.ae(tb, tr);

                k++;
            }

            if (k > 0)
                $WH.ae(_ulQuickFacts, li);
        }

        // Gear Score
        li = $WH.ce('li');
        div = $WH.ce('div');
        sp = $WH.ce('a');
        sp.href = '#gear-summary';

        $WH.ae(div, $WH.ct(LANG.pr_qf_gearscore));

        _spGearScore = sp;

        $WH.ae(div, sp);
        $WH.ae(li, div);
        $WH.ae(_ulQuickFacts, li);

        // Average Level
        li = $WH.ce('li');
        div = $WH.ce('div');
        sp = $WH.ce('a');
        sp.href = '#gear-summary';

        $WH.ae(div, $WH.ct(LANG.pr_qf_itemlevel));

        _spAverageLevel = sp;

        $WH.ae(div, sp);
        $WH.ae(li, div);
        $WH.ae(_ulQuickFacts, li);

        _updateGearScore();

        // Spec
        li = $WH.ce('li');
        _divSpec = div = $WH.ce('div');
        $WH.ae(div, $WH.ct(LANG.pr_qf_talents));

        sp = $WH.ce('a');
        sp.href = '#talents';
        sp.onclick = Tabs.onClick.bind(_tabs.tabs[1], sp);
        sp.className = 'icontinyl q1 tip';
        sp.onmousemove = $WH.Tooltip.cursorUpdate;
        sp.onmouseout = $WH.Tooltip.hide;
        $WH.ae(div, sp);

        $WH.ae(li, div);
        $WH.ae(_ulQuickFacts, li);

        _updateInfoboxSpec();

        // Achievement Points
        if (_profile.achievementpoints) {
            li = $WH.ce('li');
            div = $WH.ce('div');

            sp = $WH.ce('a');
            sp.href = '#achievements';
            sp.style.textDecoration = 'none';
            $WH.ns(sp);
            sp.onclick = Tabs.onClick.bind(_tabs.tabs[7], sp);

            $WH.ae(div, $WH.ct(LANG.pr_qf_achievements));
            Listview.funcBox.appendMoney(sp, 0, null, 0, 0, _profile.achievementpoints);
            $WH.ae(div, sp);

            $WH.ae(li, div);
            $WH.ae(_ulQuickFacts, li);
        }

        // Skills
        _spSkills = [];
        if (_profile.skills) {
            var skills = g_sortJsonArray(_profile.skills, g_spell_skills);
            for (var i = 0, len = skills.length; i < len; ++i) {
                var
                    skillId = skills[i],
                    skill   = _profile.skills[skillId];

                if (!skill[1]) {
                    skill[1] = 525;
                }

                var opt = {
                    text:      g_spell_skills[skillId],
                    hoverText: skill[0] + ' / ' + skill[1],
                    color:     'rep' + Math.min(6, Math.ceil(skill[1] / 75)),
                    width:     Math.min(100, (skill[0] / skill[1]) * 100)
                };

                li = $WH.ce('li');
                div = $WH.ce('div');

                li.className = 'profiler-infobox-skill';

                var sp = g_createProgressBar(opt);
                sp.skill = skillId;
                sp.rel = 'np';
                $WH.ns(sp);

                _spSkills.push(sp);

                $WH.ae(div, sp);
                $WH.ae(li, div);
                $WH.ae(_ulQuickFacts, li);
            }
        }

        _updateInfoboxSkills();

        // Arena Teams
        if (_profile.arenateams) {
            for (var i in _profile.arenateams) {
                i = parseInt(i);
                if (_profile.arenateams[i]) {
                    li = $WH.ce('li');
                    div = $WH.ce('div');

                    $WH.ae(div, $WH.ct($WH.sprintfa(LANG.pr_qf_xvxteam, i)));
                    a = $WH.ce('a');
                    a.href = '?arena-team=' + _profile.region[0] + '.' + _profile.realm[0] + '.' + g_urlize(_profile.arenateams[i], true);
                    $WH.ae(a, $WH.ct(_profile.arenateams[i]));
                    $WH.ae(div, a);

                    $WH.ae(li, div);
                    $WH.ae(_ulQuickFacts, li);
                }
            }
        }

        _updateInfoboxActivity();
    }

    function _updateGearScore() {
        var
            sp  = _spGearScore,
            sp2 = _spAverageLevel;

        if (!sp || !sp2) {
            return;
        }

        // Calculate Gear Score
        var
            total = _inventory.getGearScore(),
            avg   = _inventory.getAverageLevel();

        _profile.gearscore    = total.item + total.ench + total.gems;
        _profile.gearsubtotal = total;

        sp.className = sp2.className = 'q' + pr_getGearScoreQuality(_profile.level, _profile.gearscore, $WH.array_index([2, 6, 7, 11], _profile.classs));

        if (_profile.gearsubtotal) {
            sp.className  += ' tip';
            sp.onmouseover = _showGearScoreTooltip.bind(0, _profile.gearsubtotal);
            sp.onmousemove = $WH.Tooltip.cursorUpdate;
            sp.onmouseout  = $WH.Tooltip.hide;
        }
        else {
            sp.onmouseover = sp.onmousemove = sp.onmouseout = null;
        }

        $WH.st(sp, $WH.number_format(_profile.gearscore));
        $WH.st(sp2, $WH.number_format(avg));

        _updateInfoboxActivity();
    }

    function _updateInfoboxSpec() {
        if (!_divSpec) {
            return;
        }

        var
            buildData = _talents.getTalentBuilds(),
            specData  = pr_getSpecFromTalents(_profile.classs, buildData.spent),
            spSpec    = _divSpec.childNodes[1];

        spSpec.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + specData.icon.toLowerCase() + '.gif)';
        $WH.st(spSpec, (buildData.spent ? buildData.spent.join('/') : '0/0/0'));

        spSpec.onmouseover = function (e) {
            $WH.Tooltip.showAtCursor(e, specData.name, 0, 0, 'q');
        };
    }

    function _updateInfoboxSkills() {
        for (var i = 0, len = _spSkills.length; i < len; ++i) {
            var sp = _spSkills[i];

            if (_tabs.tabs[5].hidden) {
                sp.className = sp.className.replace('tip', '');
                sp.href      = null;
                sp.onclick   = $WH.rf;
            }
            else {
                sp.className += ' tip';
                sp.href       = '#recipes';
                sp.onclick    = function (e) {
                    (Tabs.onClick.bind(_tabs.tabs[5], this, e))();
                    _recipes.filterData(0, this.skill);
                };
            }
        }
    }

    function _updateInfoboxActivity() {
        _trRaidProgress.style.display = 'none';
        $WH.ee(_tdRaidActivity);
        $WH.ee(_tdRaidCompletion);
        $WH.ee(_tdRaidGearMeter);

        if (!_isArmoryProfile(_profile.genuine) || _profile.level != 80 || !_progress.length) {
            return;
        }

        _trRaidProgress.style.display = '';

        var a = $WH.gE(_trRaidProgress, 'a')[0];
        $WH.st(a, LANG['pr_qf_raidactivity' + (_activity ? 1 : 2)]);

        var
            avgLevel  = _inventory.getAverageLevel(),
            tierZones = {},
            activity  = 0,
            previous  = null;

        if (!_activity) { // Recent Activity
            for (var i in _profile.activity) {
                activity += _profile.activity[i];
            }
        }

        for (var i = _progress.length - 1; i >= 0; --i) {
            var raid = _progress[i];
            if (!tierZones[raid.level]) {
                tierZones[raid.level] = 0;
            }
            tierZones[raid.level]++;

            if (_activity) { // All Activity
                for (var j = 0, len = raid.kills.length; j < len; ++j) {
                    activity += _profile.statistics[raid.kills[j][0]] | 0;
                }
            }
        }

        for (var i = _progress.length - 1; i >= 0; --i) {
            var
                raid = _progress[i],
                hasMeta = true,
                nKills = 0,
                nRecent = 0,
                nBosses = 0;

            for (var j = 0, len = raid.achievs.length; j < len; ++j) {
                hasMeta = hasMeta && (_profile.achievements[raid.achievs[j]]);
            }

            for (var j = 0, len = raid.kills.length; j < len; ++j) {
                nKills  += _profile.statistics[raid.kills[j][0]] | 0;
                nRecent += _profile.activity[raid.kills[j][0]] | 0;
                nBosses += _profile.statistics[raid.kills[j][0]] ? 1 : 0;
            }

            if (hasMeta) {
                nBosses = raid.kills.length;
            }

            if (_activity) {
                nRecent = nKills;
            }

            var gearLevel = (avgLevel >= raid.level ?
                (avgLevel - 13 >= raid.level ? 0 : 1) :
                (avgLevel + 27 < raid.level ? 5 :
                (avgLevel + 18 < raid.level ? 4 :
                (avgLevel + 9 < raid.level ? 3 : 2))));

// Aowow custom start
            let optText = (raid.zone ? g_zones[raid.zone] : raid.name);
            if (optText.length > 30)
                optText = optText.substring(0, 25) + '…' + optText.substring(optText.length - 5, optText.length);
// Aowow custom end

            // Raid Activity
            var opt = {
                // text: (raid.zone ? g_zones[raid.zone] : raid.name),
                text: optText,
                hoverText: $WH.sprintf(LANG['pr_qf_activitypct' + (_activity ? 1 : 2)], (activity ? Math.round((nRecent / activity) * 100) : 0)),
                color: 'ach0',
                width: (activity ? Math.round((nRecent / activity) * 100) : 0)
            };

            var sp = g_createProgressBar(opt);
            sp.className += (raid.heroic ? ' icon-heroic' : ' icon-instance' + raid.instance);
            sp.onmouseover = _showActivityTooltip.bind(sp, raid, nBosses);
            sp.onmousemove = $WH.Tooltip.cursorUpdate;
            sp.onmouseout = $WH.Tooltip.hide;
            $WH.ae(_tdRaidActivity, sp);

            // Completion
            var sp = $WH.ce('span');
            sp.style.backgroundImage = 'url(' + g_staticUrl + '/images/icons/' + (hasMeta ? 'tick.png' : (nKills ? 'cog.gif' : 'delete.gif')) + ')';
            sp.className = 'completion';
            sp.onmouseover = _showActivityTooltip.bind(sp, raid, nBosses);
            sp.onmousemove = $WH.Tooltip.cursorUpdate;
            sp.onmouseout = $WH.Tooltip.hide;
            $WH.ae(_tdRaidCompletion, sp);

            // Gear
            if (tierZones[raid.level]) {
                var opt = {
                    text: '',
                    color: (gearLevel < 2 ? 'ach' + (1 - gearLevel) : 'rep' + (10 - (2 * gearLevel)))
                };

                var sp = g_createProgressBar(opt);
                sp.style.height = ((22 * tierZones[raid.level]) - (previous ? 1 : 2)) + 'px';
                sp.onmouseover = (function (g, e) {
                    $WH.Tooltip.showAtCursor(e, LANG['pr_qf_gear' + g], 0, 0, 'q');
                }).bind(sp, gearLevel);
                sp.onmousemove = $WH.Tooltip.cursorUpdate;
                sp.onmouseout = $WH.Tooltip.hide;
                $WH.ae(_tdRaidGearMeter, sp);

                if (!previous) {
                    sp.className += ' progressbar-first';
                }
                if (i == 0) {
                    sp.className += ' progressbar-last';
                }

                tierZones[raid.level] = null;
                previous = true;
            }
        }
    }

    function _showActivityTooltip(raid, nBosses) {
        var html = '';

        html += '<b class="q' + (raid.heroic ? ' icon-heroic' : '') + '">';
        html += (raid.heroic ? LANG.pr_print_heroic : LANG.pr_print_normal) + ' ';
        html += (raid.zone ? g_zones[raid.zone] : raid.name) + '</b><br />';

        html += $WH.sprintf(LANG.pr_qf_raidcomplete, Math.round((nBosses / raid.kills.length) * 100));
        html += ' (' + nBosses + '/' + raid.kills.length + ')<br />';

        for (var i = 0, len = raid.kills.length; i < len; ++i) {
            var
                statisticId = raid.kills[i][0],
                nameId = raid.kills[i][1],
                nKills = (_profile.statistics[statisticId] | 0),
                // name = (raid.zone ? g_npcs[nameId]['name_' + Locale.getName()] : g_zones[nameId]); // aowow - original
                name = (g_zones[nameId] ? g_zones[nameId] : g_npcs[nameId]['name_' + Locale.getName()]);

            if (!name) {
                continue;
            }

            html += '<span class="q' + (nKills ? 1 : 0) + '" style="padding-left: 6px">' + nKills + ' &times; ' + name + '</span><br />';
        }

        $WH.Tooltip.setIcon(raid.icon);
        $WH.Tooltip.show(this, html);
    }

    function _requestProfile(id) {
        if (!id) {
            return;
        }

        var
            url = '?profile=load&id=' + id + '&' + (new Date().getTime()),
            get = $WH.g_getGets();

            if (get.items) {
            _items = get.items.split(':');
            url += '&items=' + _items.join(':');
        }

        $WH.g_ajaxIshRequest(url);
    }

    function _registerProfile(data) {
        $WH.eO(_profile);
        $WH.cO(_profile, data);

        _profile.genuine = (!_profile.sourcename && !_profile.user && !_profile.username);

        _profile.pinned = false;
        if (_isArmoryProfile() && g_user && g_user.characters && g_user.characters.length) {
            $.each(g_user.characters, function (index, character) {
                if (character.id == _profile.source && character.pinned) {
                    _profile.pinned = true;
                }
            });
        }

        _updateDefaultIcon();
        _updateHeader();

        if (_profile.talents.builds && _profile.talents.builds.length) {
            _talents.setTalents(_profile.talents.active, _profile.talents.builds);
        }

        if (_profile.pets && _profile.pets.length) {
            _tabs.hide(2, 1); // Pets
            _pets.setTalents(0, _profile.pets);
        }

        _inventory.setInventory(_profile.inventory);
        _inventory.addItems(_items);

        for (var i in g_spells) {
            _fixJson(g_spells[i].modifier || 0);
        }
        // _updateAuras();

        _statistics.updateModifiers();

        _updateInfobox();

        if (_isArmoryProfile()) {
            _tabs.hide(3, 1); // Mounts
            _mounts.setData(_profile.spells);

            _tabs.hide(4, 1); // Companions
            _companions.setData(_profile.spells);

            _tabs.hide(5, 1); // Recipes
            _recipes.setData(_profile.spells);

            _tabs.hide(6, 1); // Quests
            _quests.setData(_profile.quests);

            _tabs.hide(7, 1); // Achievements
            _achievements.setData(_profile.achievements);

            _tabs.hide(8, 1); // Titles
            _titles.setData(_profile.titles);

            _tabs.hide(9, 1); // Reputation
            _reputation.setData(_profile.reputation);
        }
        else {
            _tabs.hide(2, _profile.classs == 3); // Pets
            _tabs.hide(3); // Mounts
            _tabs.hide(4); // Companions
            _tabs.hide(5); // Recipes
            _tabs.hide(6); // Quests
            _tabs.hide(7); // Achievements
            _tabs.hide(8); // Titles
            _tabs.hide(9)  // Reputation
        }

        if ((g_serverTime - data.lastupdated) >= 3600000) { // Auto-resync armory characters older than 1 hour
            setTimeout(_self.resync, 1000);
        }

        _updateSavedChanges(1);

        _onTabChange();
    }

    function _editProfile(data) {
        var
            charName = _profile.name,
            raceId   = _profile.race,
            genderId = _profile.gender,
            classId  = _profile.classs,
            level    = _profile.level,
            armory   = _isArmoryProfile();

        if (data.description) {
            data.description = $WH.str_replace(data.description, "\n", ' ');
        }

        $WH.cO(_profile, data);

        _profile.gender = parseInt(_profile.gender);
        _profile.published = parseInt(_profile.published);

        if (!_profile.user || _profile.user != g_user.id) {
            _profile.sourcename = (armory ? charName : '');
            _profile.username = g_user.name;
            _profile.user = g_user.id;
            _profile.id = 0;

            delete _profile.lastupdated;
        }

        delete _profile.customs;

        if (_profile.classs != classId) {
            _talents.resetAll();
            _pets.resetAll();
            _inventory.updateAllIcons();
        }

        if (_profile.level != level) {
            _inventory.updateAllHeirlooms();
        }

        _talents.updateAll();
        _pets.updateAll();

        _updateDefaultIcon();
        _updateHeader();
        _updateInfobox();
        _updateSavedChanges();

        if (_profile.race != raceId || _profile.gender != genderId) {
            _inventory.updateModel(null, 1);
        }

        if (_profile.classs != classId || _profile.race != raceId || _profile.level != level) {
            _statistics.updateModifiers();
        }
    }

    function _showGearScoreTooltip(total, e) {
        var
            html = '',
            sum = parseInt(total.item + total.gems + total.ench);

        html += '<table>';
        html += '<tr><td class="q">' + LANG.pr_tt_items    + '</td><th>' + total.item + '</th><th><small class="q0">' + (!total.item ? 0 : Math.round(total.item / sum * 100)) + '%</small></th></tr>';
        html += '<tr><td class="q">' + LANG.pr_tt_gems     + '</td><th>' + total.gems + '</th><th><small class="q0">' + (!total.gems ? 0 : Math.round(total.gems / sum * 100)) + '%</small></th></tr>';
        html += '<tr><td class="q">' + LANG.pr_tt_enchants + '</td><th>' + total.ench + '</th><th><small class="q0">' + (!total.ench ? 0 : Math.round(total.ench / sum * 100)) + '%</small></th></tr>';
        html += '</table>';

        $WH.Tooltip.showAtCursor(e, html);
    }

    function _onShowTabs(newTab, oldTab) {
        Tabs.onShow(newTab, oldTab);

        if (oldTab) {
            _onTabChange(newTab.index);
        }
    }

    function _onTabChange(currentTab) {
        if (currentTab == null) {
            currentTab = _tabs.getSelectedTab();
        }

        _inventory.showModel(currentTab == 0);

        switch (currentTab) {
            case 0: // Inventory
                _divInventory.style.display = '';
                break;
            case 1: // Talents
                _talents.onShow();
                _divTalents.style.display = '';
                break;
            case 2: // Pets
                _pets.onShow();
                _divPets.style.display = '';
                break;
            case 3: // Mounts
                _mounts.onShow();
                _divMounts.style.display = '';
                break;
            case 4: // Companions
                _companions.onShow();
                _divCompanions.style.display = '';
                break;
            case 5: // Recipes
                _recipes.onShow();
                _divRecipes.style.display = '';
                break;
            case 6: // Quests
                _quests.onShow();
                _divQuests.style.display = '';
                break;
            case 7: // Achievements
                _achievements.onShow();
                _divAchievements.style.display = '';
                break;
            case 8: // Titles
                _titles.onShow();
                _divTitles.style.display = '';
                break;
            case 9: // Reputation
                _reputation.onShow();
                _divReputation.style.display = '';
                break;
        }
    }
}

function ProfilerTalents(_parent) {
    var
        _self = this,

        _talentCalc,
        _parentVars,
        _profile,
        _tabs,

        _tabIndex,

        _active,
        _builds,
        _glyphs,

        _inited,
        _updated,
        _displayed,
        _loading,

        _container,
        _divBuilds,
            _spButtons,
        _divTalentCalc,
        _divGlyphs,

        _mode,
        _export;

    this.initialize = function (container, tabIndex) {
        _container = $WH.ge(container);
        if (!_container) {
            return;
        }

        _parentVars = _parent.getVars();
        _profile    = _parentVars.profile;
        _statistics = _parentVars.statistics;
        _inventory  = _parentVars.inventory;
        _talents    = _parentVars.talents;
        _tabs       = _parentVars.tabs;

        _tabIndex = tabIndex;

        if (!_tabs.tabs[_tabIndex]) {
            return;
        }

        _mode = _tabs.tabs[_tabIndex].id;

        _self.resetAll();
    };

    this.setTalents = function (active, builds) {
        _setTalents(active, builds);
    };

    this.setGlyphData = function (data, who, when) {
        if (data) {
            _divGlyphs.parentNode.style.display = '';
        }

        _glyphs.setData(data, who, when);
    };

    this.resetAll = function () {
        _active = 0;
        _builds = [];

        for (var i = 0, len = (_mode == 'pets' ? 25 : 2); i < len; ++i) {
            var _ = { talents: '' };

            if (_mode == 'pets') {
                _.spent = 0;
                _.name = '';
                _.family = 0;
                _.npcId = 0;
                _.displayId = 0;
            }
            else {
                _.name = LANG.pr_queue_unknown;
                _.glyphs = '';
                _.spent = [0, 0, 0];
                _.spec = -1;
                _.scale = {};
                _.filter = '';
            }

            _builds.push(_);
        }

        _updated = false;
        _displayed = false;

        if (_tabs.getSelectedTab() == _tabIndex) {
            _self.onShow();
        }
    };

    this.updateAll = function () {
        _updateBuilds();
        _updateTalents();
    };

    this.getTalentBuilds = function () {
        if (!_export) {
            _getWeightScale(true);

            _export = $WH.dO(_builds[_active]);
            _export.active = _active;
            _export.talents = [];

            if (_mode != 'pets') {
                _export.glyphs = [];
            }

            for (var i = 0, len = _builds.length; i < len; ++i) {
                _export.talents.push(_builds[i].talents);

                if (_mode != 'pets') {
                    _export.glyphs.push(_builds[i].glyphs);
                }
            }
        }

        return _export;
    };

    this.getTalentRanks = function (talent) {
        if (!_inited) {
            return;
        }

        return _talentCalc.getTalentRanks(talent);
    };

    this.hasTitansGrip = function () {
        return _talents.getTalentRanks(1867)
    };

    this.hasBeastMastery = function () {
        return _talents.getTalentRanks(2139)
    };

    this.getWeightScale = function (refresh) {
        return _getWeightScale(refresh);
    };

    this.onShow = function (noShow) {
        if (!_inited) {
            _initBuilds();
            _initTalents();
            _initGlyphs();

            _inited = true;
        }

        if (!_updated) {
            _updateBuilds();
            _updateTalents();

            _updated = true;
        }
    };

    function _setTalents(active, builds) {
        _active = active;
        _builds = $WH.dO(builds);
        _updated = false;
        _self.onShow();
    }

    function _getBuildClass(build) {
        if (_mode != 'pets') {
            return _profile.classs;
        }

        if (build == null) {
            build = _active;
        }

        if (_builds[build].npc && g_pets[_builds[build].npc]) {
            return g_pets[_builds[build].npc].family;
        }
    }

    function _initBuilds() {
        _divBuilds = $WH.ce('div');
        _divBuilds.className = 'profiler-talents-specs';

        _spButtons = [];
        for (var i = 0, len = _builds.length; i < len; ++i) {
            var a = $WH.ce('a');
            a.className = 'profiler-' + _mode + '-button';
            a.onclick = _setActiveBuild.bind(0, i);
            a.href = 'javascript:;';

            _spButtons[i] = $WH.ce('span');

            if (_mode == 'pets') {
                var icon = Icon.create('inv_misc_questionmark', 0);
                icon.style.position = 'absolute';
                $WH.ae(a, icon);

                _spButtons[i].icon = icon;
            }

            $WH.ae(a, _spButtons[i]);
            $WH.ae(_divBuilds, a);
        }

        var div = $WH.ce('div');
        div.className = 'clear';
        $WH.ae(_divBuilds, div);
        $WH.ae(_container, _divBuilds);
    }

    function _updateBuilds() {
        if (!_inited) {
            return;
        }

        g_setSelectedLink(_divBuilds.childNodes[_active], 'active' + _mode);

        for (var i = 0, len = _builds.length; i < len; ++i) {
            _updateBuild(i);
        }
    }

    function _updateBuild(build) {
        if (build == null) {
            build = _active;
        }

        if (!_builds[build]) {
            return;
        }

        _builds[build].spent = _talentCalc.getSpentFromBlizzBuild(_builds[build].talents, _profile.classs);

        var
            sp = _spButtons[build],
            sm = $WH.ce('small');

        if (sp) {
            $WH.ee(sp);
        }

        if (_mode == 'pets') {
            var family = _getBuildClass(build);
            Icon.setTexture(sp.icon, 0, (family ? g_pet_icons[family] : 'inv_misc_questionmark'));
            $WH.ae(sp, $WH.ct(family ? _builds[build].name : LANG.pr_nonepet));
        }
        else {
            var
                basePoints = Math.max(0, _profile.level - 9),
                spentPoints = _builds[build].spent[0] + _builds[build].spent[1] + _builds[build].spent[2],
                reduce = spentPoints - basePoints;

            for (var i = 2; i >= 0; --i) {
                if (reduce <= 0) {
                    break;
                }
                else if (_builds[build].spent[i] >= reduce) {
                    _builds[build].spent[i] -= reduce;
                    reduce = 0;
                }
                else if (_builds[build].spent[i] > 0) {
                    reduce -= _builds[build].spent[i];
                    _builds[build].spent[i] = 0;
                }
            }

            var specData = pr_getSpecFromTalents(_profile.classs, _builds[build].spent);

            if (sp) {
                sp.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + specData.icon.toLowerCase() + '.gif)';

                $WH.ae(sp, $WH.ct(specData.name + ' '));
                $WH.st(sm, '(' + _builds[build].spent.join('/') + ')');
                $WH.ae(sp, sm);
            }

            return specData;
        }
    }

    function _setActiveBuild(active) {
        _active = active;
        _updated = false;

        if (_tabs.getSelectedTab() == _tabIndex) {
            _self.onShow();
        }
    }

    function _initTalents() {
        _divTalentCalc = $WH.ce('div');
        _divTalentCalc.style.display = 'none';
        $WH.ae(_container, _divTalentCalc);

        _talentCalc = new TalentCalc();
        _talentCalc.initialize(_divTalentCalc, {
            onChange: _onChangeTalents,
            noAd: 1,
            profiler: 1,
            mode: (_mode == 'pets' ? TalentCalc.MODE_PET : TalentCalc.MODE_DEFAULT)
        });
    }

    function _updateTalents() {
        if (!_inited) {
            return;
        }

        var classId = _getBuildClass();

        if (classId) {
            _loading = _active;
            _active = 2;

            _builds[_active] = $WH.dO(_builds[_loading]);

            _talentCalc.setClass(classId);

            if (_builds[_loading].talents) {
                _talentCalc.setBlizzBuild(classId, _builds[_loading].talents);
            }
            else {
                _talentCalc.resetBuild();
            }

            if (_builds[_loading].glyphs) {
                _talentCalc.setBlizzGlyphs(_builds[_loading].glyphs);
            }
            else {
                _talentCalc.resetGlyphs();
            }

            if (_mode == 'pets') {
                _talentCalc.setPetModel(_builds[_loading].npc);
            }
            else {
                _builds[_loading].name = LANG.pr_queue_unknown;
                _builds[_loading].spec = -1;
                _builds[_loading].scale = {};
                _builds[_loading].filter = '';
            }

            if (_displayed) {
                _talentCalc.setLevelCap(_profile.level);
            }

            _active = _loading;
            _loading = null;
        }
    }

    function _onChangeTalents(tc, info, data) {
        if (_loading === null) {
            _builds[_active].talents = _talentCalc.getBlizzBuild();
            _builds[_active].spent = info.pointsSpent;

            if (_mode != 'pets') {
                _builds[_active].glyphs = _talentCalc.getBlizzGlyphs();
            }

            if (_displayed) {
                _parent.updateSavedChanges();
            }
        }

        if (!_displayed) {
            // aowow: causes infinite loop and it works fine without this
            // _talentCalc.setLevelCap(_profile.level);
            if (_mode != 'pets') {
                _updateBuilds();
            }
            _displayed = true;

            _divTalentCalc.style.display = '';
        }

        if (_mode != 'pets') {
            _parent.updateInfoboxSpec();

            var
                specData    = _updateBuild(),
                changedSpec = (_builds[_active].spec != specData.id),
                weightScale = _getWeightScale(changedSpec);

            _parent.updateGearScore();

            var t = tc.getTalents();

            _statistics.removeAllModifiers('talent');
            for (var i = 0, len = t.length; i < len; ++i) {
                if (t[i].j) {
                    _statistics.addModifiers('talent', t[i].s[t[i].k - 1], t[i].j[t[i].k - 1]);
                }
            }

            _inventory.updateInventory();
        }
        else if (_talents.hasBeastMastery() && !_talentCalc.getBonusPoints()) {
            _talentCalc.setBonusPoints();
        }

        _export = null;
    }

    function _getWeightScale(refresh) {
        if (!wt_presets[_profile.classs] || !_builds[_active]) {
            return;
        }

        var
            specData = pr_getSpecFromTalents(_profile.classs, _builds[_active].spent),
            spec     = specData.id,
            pve      = wt_presets[_profile.classs].pve,
            t        = spec - 1;

        if (_profile.classs == 11 && spec == 2) { // Feral Druid (2 specs)
            // Protector of the Pack, Natural Reaction
            var tankTalents = [2241, 2242];
            t += 2;
            for (var i = 0, len = tankTalents.length; i < len; ++i) {
                if (!_self.getTalentRanks(tankTalents[i])) {
                    continue;
                }

                t -= 2;
                break;
            }
        }

        var
            scale = pr_getScaleFromSpec(_profile.classs, t),
            name  = pr_getScaleFromSpec(_profile.classs, t, 1);

        if (refresh) {
            _builds[_active].name = (name ? name : LANG.pr_queue_unknown);
            _builds[_active].spec = specData.id;
            _builds[_active].scale = scale;
            _builds[_active].filter = pr_getScaleFilter(scale);
        }

        _parent.fixJson(scale);

        return scale;
    }

    function _initGlyphs() {
        var
            _,
            __;

        _ = $WH.ce('div');
        _.className = 'clear pad';
        $WH.ae(_container, _);

        _ = $WH.ce('div');
        _.className = 'text';
        _.style.display = 'none';
        __ = $WH.ce('h2');
        __.className = 'clear';
        $WH.st(__, LANG.tc_glyphs);
        $WH.ae(_, __);
        $WH.ae(_container, _);

        _divGlyphs = __ = $WH.ce('div');
        $WH.ae(_, __);

        _glyphs = new ProfilerCompletion(_parent);
        _glyphs.initialize(_divGlyphs, _tabIndex, {
            template: 'item',
            noAd: 1,
            source: 'g_glyph_items',
            typeid: 3,
            order: 'g_glyph_order',
            catgs: 'g_item_glyphs',
            catgid: 'glyph',
            visibleCols: ['glyph'],
            hiddenCols: ['slot', 'type']
        });
    }
}

function ProfilerStatistics(_parent) {
    var
        _self = this,

        _parentVars,
        _profile,
        _inventory,

        _timer,

        _divStatBars,
        _divStatResists,
        _tblStatBoxes,

        _magicSchools = ['arc', 'fir', 'fro', 'hol', 'nat', 'sha'],

        _modifiers = {},
        _statistics = {
            // ***** Base Attributes *****
            str: {
                getTooltip: function () {
                    return LANG['pr_statstt_str' + (_profile.classs == 1 || _profile.classs == 2 || _profile.classs == 7 ? 2 : '')];
                },
                tooltipModifiers: ['mleatkpwr', 'block'],
                addModifiers: function () {
                    var classData = g_statistics.classs[_profile.classs];

                    return {
                        mleatkpwr: [classData[0][1], 'percentOf', ['str', (classData[0][1] ? (classData[0][0] / (classData[0][2] ? 2 : 1)) : 0)]],
                        rgdatkpwr: [classData[1][1], 'percentOf', ['str', (classData[1][1] ? (classData[1][0] / (classData[1][2] ? 2 : 1)) : 0)]],
                        block: [0.5, 'percentOf', ['str', -10]]
                    };
                }
            },

            agi: {
                // tooltipModifiers: ['mleatkpwr', 'mlecritstrkpct', 'armor'], - aowow replacement
                tooltipModifiers: ['mleatkpwr', 'mlecritstrkpct', 'fullarmor'],
                addModifiers: function () {
                    var
                        raceData  = g_statistics.race[_profile.race],
                        classData = g_statistics.classs[_profile.classs],
                        levelData = g_statistics.combo[_profile.classs][_profile.level];

                    return {
                        mleatkpwr: [classData[0][2], 'percentOf', ['agi', (classData[0][2] ? (classData[0][0] / (classData[0][1] ? 2 : 1)) : 0)]],
                        rgdatkpwr: [classData[1][2], 'percentOf', ['agi', (classData[1][2] ? (classData[1][0] / (classData[1][1] ? 2 : 1)) : 0)]],
                        mlecritstrkpct: [levelData[7], 'percentOf', ['agi', classData[2][0]]],
                        rgdcritstrkpct: [levelData[7], 'percentOf', ['agi', classData[2][0]]],
                        // armor: [2, 'percentOf', 'agi'], - aowow replacement
                        fullarmor: [2, 'percentOf', 'agi'],
                        dodgepct: [levelData[9], 'percentOf', ['agi', -levelData[9] * ((raceData ? raceData[1] : 0) + levelData[1])]]
                    };
                }
            },

            sta: {
                getTooltip: function () {
                    return LANG['pr_statstt_sta' + (_profile.classs == 3 || _profile.classs == 9 ? 2 : '')];
                },
                tooltipModifiers: ['health', 'petsta'],
                addModifiers: function () {
                    return {
                        health: [10 + ((Math.max(_profile.level, 80) - 80) * 0.8), 'percentOf', ['sta', -180]],
                        petsta: [0.3, 'percentOf', 'sta']
                    };
                }
            },

            'int': {
                getTooltip: function () {
                    return LANG['pr_statstt_int' + (_profile.classs == 9 ? 2 : '')];
                },
                tooltipModifiers: ['mana', 'arcsplcritstrkpct', 'petint'],
                addModifiers: function () {
                    if (_profile.classs == 1 || _profile.classs == 4 || _profile.classs == 6) {
                        return {};
                    }

                    var
                        classData = g_statistics.classs[_profile.classs],
                        levelData = g_statistics.combo[_profile.classs][_profile.level];

                    return {
                        mana: [15, 'percentOf', ['int', -280]],
                        arcsplcritstrkpct: [levelData[8], 'percentOf', ['int', classData[2][1]]],
                        firsplcritstrkpct: [levelData[8], 'percentOf', ['int', classData[2][1]]],
                        frosplcritstrkpct: [levelData[8], 'percentOf', ['int', classData[2][1]]],
                        holsplcritstrkpct: [levelData[8], 'percentOf', ['int', classData[2][1]]],
                        natsplcritstrkpct: [levelData[8], 'percentOf', ['int', classData[2][1]]],
                        shasplcritstrkpct: [levelData[8], 'percentOf', ['int', classData[2][1]]],
                        petint: (_profile.classs == 9 ? [0.3, 'percentOf', 'int'] : 0)
                    };
                }
            },

            spi: {
                tooltipModifiers: ['healthrgn', 'spimanargn'],
                addModifiers: function () {
                    return {
                        healthrgn: [1, 'functionOf', function () {
                            // taken from TrinityCore//Player::OCTRegenHPPerSpirit(), which is taken from PaperDollFrame script
                            var
                                spiBase = _statistics.spi.subtotal[0],
                                spiMore = _statistics.spi.subtotal[1]

                            if (spiBase > 50) {
                                spiMore += spiBase - 50;
                                spiBase  = 50;
                            }

                            return Math.round(
                                (spiBase * g_statistics.combo[_profile.classs][_profile.level][10]) +
                                (spiMore * g_statistics.combo[_profile.classs][_profile.level][11]));
                        }],
                        spimanargn: [5, 'functionOf', function () {
                            return (0.001 + Math.sqrt(_statistics['int'].total) * _statistics.spi.total * g_statistics.level[_profile.level]);
                        }]
                    };
                }
            },

            health: {},

            mana: {},

            rage: {},

            energy: {},

            runic: {},

            // ********** Melee **********
            mledps: {
                decimals: 1
            },

            mleatkpwr: {
                tooltipModifiers: ['mledps'],
                addModifiers: function () {
                    return {
                        mledps: [1 / 14, 'percentOf', 'mleatkpwr']
                    };
                }
            },

            mlecritstrkpct: {
                decimals: 2,
                rating: { id: 19, stat: 'mlecritstrkrtng' }
            },

            mlehastepct: {
                decimals: 2,
                rating: { id: 28, stat: 'mlehastertng' }
            },

            mlehitpct: {
                decimals: 2,
                rating: { id: 16, stat: 'mlehitrtng' }
            },

            armorpenpct: {
                rating: { id: 44, stat: 'armorpenrtng' },
                decimals: 2
            },

            'exp': {
                rating: { id: 37, stat: 'exprtng' },
                tooltipCompute: function (total) {
                    return [(0.25 * total).toFixed(2)];
                }
            },

            // ********** Ranged *********
            rgddps: {
                decimals: 1
            },

            rgdatkpwr: {
                getTooltip: function () {
                    return LANG['pr_statstt_rgdatkpwr' + (_profile.classs == 3 ? 2 : '')];
                },
                tooltipModifiers: ['rgddps', 'petatkpwr', 'petspldmg'],
                addModifiers: function () {
                    return {
                        rgddps: [1 / 14, 'percentOf', 'rgdatkpwr'],
                        petatkpwr: (_profile.classs == 3 ? [0.22, 'percentOf', 'rgdatkpwr'] : 0),
                        petspldmg: (_profile.classs == 3 ? [0.1287, 'percentOf', 'rgdatkpwr'] : 0)
                    };
                }
            },

            rgdhitpct: {
                decimals: 2,
                rating: { id: 17, stat: 'rgdhitrtng' }
            },

            rgdcritstrkpct: {
                decimals: 2,
                rating: { id: 20, stat: 'rgdcritstrkrtng' }
            },

            rgdhastepct: {
                decimals: 2,
                rating: { id: 29, stat: 'rgdhastertng' }
            },


            // ********** Spell **********
            splheal: {},

            spldmg: {
                magic: 1,
                getTooltip: function () {
                    return LANG['pr_statstt_spldmg' + (_profile.classs == 9 ? 2 : '')];
                },
                tooltipModifiers: ['spldmg', 'petatkpwr', 'petspldmg'],
                addModifiers: function () {
                    var maxSpellDmg = function () {
                        var result = _statistics[_magicSchools[0] + 'spldmg'].total;

                        for (var i = 1, len = _magicSchools.length; i < len; ++i) {
                            if (_statistics[_magicSchools[i] + 'spldmg'].total > result) {
                                result = _statistics[_magicSchools[i]];
                            }
                        }

                        return result;
                    };

                    return {
                        petatkpwr: (_profile.classs == 9 ? [0.57, 'functionOf', maxSpellDmg] : 0),
                        petspldmg: (_profile.classs == 9 ? [0.15, 'functionOf', maxSpellDmg] : 0)
                    };
                }
            },

            splhitpct: {
                decimals: 2,
                rating: { id: 18, stat: 'splhitrtng' }
            },

            splcritstrkpct: {
                decimals: 2,
                rating: { id: 21, stat: 'splcritstrkrtng' },
                magic: 1
            },

            splhastepct: {
                decimals: 2,
                rating: { id: 30, stat: 'splhastertng' }
            },

            splpen: {},

            healthrgn: {},

            manargn: {
                addModifiers: function () {
                    return {
                        oocmanargn: [1, 'percentOf', 'manargn'],
                        icmanargn: [1, 'percentOf', 'manargn']
                    };
                }
            },

            spimanargn: {
                addModifiers: function () {
                    return {
                        oocmanargn: [1, 'percentOf', 'spimanargn']
                    };
                }
            },

            oocmanargn: {
                tooltipCompute: function (total) {
                    // return [total, _statistics.manargn.total]; // aowow -- everything with icmanargn is custom. was apparently not handled before
                    return [total, _statistics.icmanargn.total];
                }
            },

            icmanargn: {},

            // ********* Defenses ********
            // armor: { - aowow replacement
            fullarmor: {
                label: 'pr_stats_armor', // - aowow added
                getTooltip: function () {
                    return LANG['pr_statstt_armor' + (_profile.classs == 3 || _profile.classs == 9 ? 2 : '')];
                },
                excludeModifiers: ['armorbonus'],
                tooltipModifiers: ['petarmor'],
                tooltipCompute: function (total) {
                    var
                        levelMod = (_profile.level > 59 ? _profile.level + (4.5 * (_profile.level - 59)) : _profile.level),
                        pct      = (0.1 * total) / ((8.5 * levelMod) + 40);

                    pct /= 1 + pct;
                    return [Math.min(75, pct * 100).toFixed(2)];
                },
                addModifiers: function () {
                    return {
                        // petarmor: [0.35, 'percentOf', 'armor'] - aowow replacement
                        petarmor: [0.35, 'percentOf', 'fullarmor']
                    }
                }
            },

            armor: { // aowow - added this so we can correctly calc talents that only modify equipped armor values
                addModifiers: function () {
                    return {
                        fullarmor: [1, 'percentOf', 'armor']
                    }
                }
            },

            def: {
                rating: { id: 12, stat: 'defrtng' },
                tooltipModifiers: ['parrypct', 'dodgepct'],
                addModifiers: function () {
                    var diminishedPct = function () {
                        var
                            classData = g_statistics.classs[_profile.classs],
                            missCap   = 16.0,                  // TrinityCore says its the same for all classes
                            toPercent = 0.04,
                            actualDef = _statistics.def.total - (_profile.level * 5);

                        return (actualDef * missCap * toPercent) / ((missCap * classData[3]) + (actualDef * toPercent));
                    };
                    return {
                        dodgepct: [1, 'functionOf', diminishedPct],
                        parrypct: [1, 'functionOf', diminishedPct],
                        blockpct: [1, 'functionOf', diminishedPct]
                    }
                }
            },

            /*
                baseDodge = from g_statistics[classs][4]
                baseAgi = raceAgi + levelAgi (e.g. for a 80 tauren druid = 16 + 61)
                dodgePerAgi = gathered from the client->database
                H = rating to % modifier calculated with g_convertRatingToPercent

                @dodgepct modifiers:
                0
                    diminish: true
                    percent: H
                    percentOf: dodgerating
                    subtotal: dodgerating*H
                1
                    diminish: true
                    percent: dodgePerAgi
                    percentBase: -dodge%FromBaseAgi (-dodgePerAgi*baseAgi)
                    percentOf: agi
                    subtotal: dodgePerAgi*totalAgi - dodge%FromBaseAgi
                2
                    diminish: false
                    add: baseDodge + baseAgi*dodgePerAgi   (!!!!!missing dodgeFromTalents!!!!!)
                    subtotal: add

                @final dodgepct = diminish(0 + 1) + 2
            */
            dodgepct: {
                decimals: 2,
                rating: { id: 13, stat: 'dodgertng' },
                computeDiminished: function (subtotal) {
                    var classData = g_statistics.classs[_profile.classs];
                    return subtotal * classData[5] / ((classData[5] * classData[3]) + subtotal);
                }
            },

            parrypct: {
                decimals: 2,
                rating: { id: 14, stat: 'parryrtng' },
                computeDiminished: function (subtotal) {
                    var classData = g_statistics.classs[_profile.classs];
                    return (classData[7] ? subtotal * classData[7] / ((classData[7] * classData[3]) + subtotal) : 0);
                }
            },

            blockpct: {
                decimals: 2,
                rating: { id: 15, stat: 'blockrtng'},
                tooltipCompute: function (total) {
                    return [_statistics.block.total];
                }
            },

            block: {},

            resipct: {
                decimals: 2,
                rating: { id: 35, stat: 'resirtng' },
                tooltipCompute: function (total) {
                    return [total, (2.2 * total).toFixed(2), (2 * total).toFixed(2)];
                }
            },

            // ********* Resistances ********
            arcres: {
                icon: 'spell_arcane_blast',
                getTooltip: function () {
                    return $WH.sprintf(LANG['pr_statstt_resist'], LANG['pr_statstt_arc']);
                },
                tooltipCompute: function (total) {
                    return [Math.min(75, (total / (_profile.level * 5)) * 75).toFixed(2)];
                },
                addModifiers: function () {
                    return {
                        petarcres: [0.4, 'percentOf', 'arcres']
                    }
                }
            },

            firres: {
                icon: 'spell_fire_burnout',
                getTooltip: function () {
                    return $WH.sprintf(LANG['pr_statstt_resist'], LANG['pr_statstt_fir']);
                },
                tooltipCompute: function (total) {
                    return [Math.min(75, (total / (_profile.level * 5)) * 75).toFixed(2)];
                },
                addModifiers: function () {
                    return {
                        petfirres: [0.4, 'percentOf', 'firres']
                    }
                }
            },

            frores: {
                icon: 'spell_frost_arcticwinds',
                getTooltip: function () {
                    return $WH.sprintf(LANG['pr_statstt_resist'], LANG['pr_statstt_fro']);
                },
                tooltipCompute: function(total) {
                    return [Math.min(75, (total / (_profile.level * 5)) * 75).toFixed(2)];
                },
                addModifiers: function () {
                    return {
                        petfrores: [0.4, 'percentOf', 'frores']
                    }
                }
            },

            holres: {
                icon: 'spell_holy_powerwordbarrier',
                getTooltip: function() {
                    return $WH.sprintf(LANG['pr_statstt_resist'], LANG['pr_statstt_hol']);
                },
                tooltipCompute: function(total) {
                    return [Math.min(75, (total / (_profile.level * 5)) * 75).toFixed(2)];
                },
                addModifiers: function () {
                    return {
                        petholres: [0.4, 'percentOf', 'holres']
                    }
                }
            },

            natres: {
                icon: 'ability_druid_flourish',
                getTooltip: function() {
                    return $WH.sprintf(LANG['pr_statstt_resist'], LANG['pr_statstt_nat']);
                },
                tooltipCompute: function(total) {
                    return [Math.min(75, (total / (_profile.level * 5)) * 75).toFixed(2)];
                },
                addModifiers: function () {
                    return {
                        petnatres: [0.4, 'percentOf', 'natres']
                    }
                }
            },

            shares: {
                icon: 'spell_shadow_shadowfury',
                getTooltip: function() {
                    return $WH.sprintf(LANG['pr_statstt_resist'], LANG['pr_statstt_sha']);
                },
                tooltipCompute: function(total) {
                    return [Math.min(75, (total / (_profile.level * 5)) * 75).toFixed(2)];
                },
                addModifiers: function () {
                    return {
                        petshares: [0.4, 'percentOf', 'shares']
                    }
                }
            },

            // ********** Pet Stats *********

            petsta: {},

            petint: {},

            petatkpwr: {
                dependent: { rgdatkpwr: 1, spldmg: 1 },
                ndependent: 2
            },

            petspldmg: {
                dependent: { rgdatkpwr: 1, spldmg: 1 },
                ndependent: 2
            },

            petarmor: {},

            petarcres: {},

            petfirres: {},

            petfrores: {},

            petholres: {},

            petnatres: {},

            petshares: {}
        },

    _statBoxes = [
        // Base
        [
            { id: 'base', type: 'title' },
            { id: 'health', type: 'bar' },
            { id: 'mana', type: 'bar' },
            { id: 'rage', type: 'bar' },
            { id: 'energy', type: 'bar' },
            { id: 'runic', type: 'bar' },
            { id: 'str' },
            { id: 'agi' },
            { id: 'sta' },
            { id: 'int' },
            { id: 'spi' }
        ],
        // Melee
        [
            {id: 'melee', type: 'title'},
            { id: 'mleatkpwr'},
            { id: 'mlehitpct' },
            { id: 'mlecritstrkpct' },
            { id: 'mlehastepct' },
            { id: 'armorpenpct' },
            { id: 'exp' }
        ],
        // Ranged
        [
            {id: 'ranged', type: 'title' },
            { id: 'rgdatkpwr' },
            { id: 'rgdhitpct' },
            { id: 'rgdcritstrkpct' },
            { id: 'rgdhastepct' },
            { id: 'armorpenpct' }
        ],
        // Spell
        [
            {id: 'spell', type: 'title' },
            { id: 'spldmg' },
            { id: 'splheal' },
            { id: 'splhitpct' },
            { id: 'splcritstrkpct' },
            { id: 'splhastepct' },
            { id: 'splpen' },
            { id: 'oocmanargn' }
        ],
        // Defenses
        [
            {id: 'defenses', type: 'title' },
            // { id: 'armor' }, // - aowow replacement
            { id: 'fullarmor' },
            { id: 'def' },
            { id: 'dodgepct' },
            { id: 'parrypct' },
            { id: 'blockpct' },
            { id: 'resipct' },

        // Resistances
            { id: 'arcres', type: 'resist' },
            { id: 'firres', type: 'resist' },
            { id: 'frores', type: 'resist' },
            { id: 'natres', type: 'resist' },
            { id: 'shares', type: 'resist' }
        ]
    ];

    this.initialize = function (container, tabIndex) {
        _container = $WH.ge(container);
        if (!_container) {
            return;
        }

        _parentVars = _parent.getVars();
        _profile    = _parentVars.profile;
        _inventory  = _parentVars.inventory;

        _initStatistics();
        _initStatBoxes();
    };

    this.addModifiers = function (source, sourceId, json) {
        _addModifiers(source, sourceId, json);
    };

    this.removeModifiers = function (source, sourceId) {
        _removeModifiers(source, sourceId);
    };

    this.removeAllModifiers = function (source) {
        _removeAllModifiers(source);
    };

    this.updateModifiers = function () {
        _updateModifiers();
    };

    function _checkPowerType(stat) {
        if ($WH.in_array(['rage', 'energy', 'runic', 'mana'], stat) != -1) {
            return (stat == _checkPowerType());
        }

        switch (_profile.classs) {
            case  1: return 'rage';
            case  4: return 'energy';
            case  6: return 'runic';
            default: return 'mana';
        }
    }

    function _addModifiers(source, sourceId, json) {
        for (var i in json) { // Check to make sure the json has some property
            var modifiers = $WH.dO(json);

            modifiers.source   = source;
            modifiers.sourceId = sourceId;

            if (source != 'class' && source != 'talent') {
                modifiers.diminish = true;
            }

            _modifiers.push(modifiers);

            _prepareStatistics();

            break; // Don't actually loop
        }
    }

    function _getModifiers(source, sourceId, statId) {
        var
            stat   = _statistics[statId],
            result = 0;

        for (var i = 0, len = stat.modifiers.length; i < len; ++i) {
            var
                mod      = stat.modifiers[i],
                multiple = (stat.slotMultiple[mod.slot] || 0) + stat.multiple;

            if (mod.source == source && mod.sourceId == sourceId) {
                result += _calcModifiers(mod, multiple);
            }
        }

        if (!stat.decimals) {
            result = Math.floor(result);
        }

        return result.toFixed(stat.decimals);
    }

    function _calcModifiers(mod, multiple) {
        if (mod.subtotal != null) {
            return mod.subtotal;
        }

        mod.subtotal = 0;

        if (mod.functionOf) {
            mod.subtotal += multiple * mod.percent * mod.functionOf(_profile, _statistics);
        }

        if (mod.percentOf) {
            mod.subtotal += multiple * mod.percent * (mod.percentOf == 'level' ? _profile.level : _statistics[mod.percentOf].total) + mod.percentBase;
        }

        if (mod.add) {
            mod.subtotal += multiple * (mod.add - mod.exclude) + mod.exclude;
        }

        return mod.subtotal;
    }

    function _removeModifiers(source, sourceId) {
        var result = [];

        for (var i = 0, len = _modifiers.length; i < len; ++i) {
            if (_modifiers[i].source != source || _modifiers[i].sourceId != sourceId) {
                result.push(_modifiers[i]);
            }
        }

        _modifiers = result;

        _prepareStatistics();
    }

    function _removeAllModifiers(source) {
        var result = [];

        for (var i = 0, len = _modifiers.length; i < len; ++i) {
            if (_modifiers[i].source != source) {
                result.push(_modifiers[i]);
            }
        }

        _modifiers = result;

        _prepareStatistics();
    }

    function _refreshModifiers(statId) {
        var stat = _statistics[statId];

        stat.modifiers = [];

        for (var i = 0, len = _modifiers.length; i < len; ++i) {
            if (_modifiers[i][statId] != null) {
                stat.modifiers.push(_parseModifiers(_modifiers[i], statId));
            }
        }

        stat.modifiers.sort(function (a, b) {
            if (a.multiple != b.multiple) {
                return - $WH.strcmp(a.multiple, b.multiple);
            }
            if (a.percentOf != b.percentOf) {
                return - $WH.strcmp(a.percentOf, b.percentOf);
            }
            return $WH.strcmp(a.slot, b.slot);
        });
    }

    function _parseModifiers(json, statId) {
        var
            mod      = json[statId],
            source   = json.source,
            sourceId = json.sourceId,
            stat     = _statistics[statId],
            result   = {};

        if (typeof mod == 'object' && mod.length) {
            for (var i = 1, len = mod.length; i < len; i += 2) {
                result[mod[i]] = mod[i + 1];
            }

            if (result.percentOf != null) {
                result.percentBase = 0;

                if (typeof result.percentOf == 'object' && result.percentOf.length) {
                    result.percentBase = result.percentOf[1];
                    result.percentOf = result.percentOf[0];
                }

                if (result.percentOf == statId) {
                    result.multiple = mod[0];

                    if (result.percentBase) {
                        result.add = result.percentBase;
                    }

                    delete result.percentOf;
                    delete result.percentBase;
                }
                else {
                    result.percent = mod[0];
                }
            }

            if (result.functionOf != null) {
                result.percent = mod[0];
            }

            if (result.percentOf && result.percentOf != 'level' && stat.dependent[result.percentOf] == null) {
                stat.dependent[result.percentOf] = 1;
                stat.ndependent++;
            }

            if (result.forSlots != null) {
                result.slotMultiple = {};

                for (var i = 0, len = result.forSlots.length; i < len; ++i) {
                    result.slotMultiple[result.forSlots[i]] = mod[0];
                }

                delete result.forSlots;
            }

            // Check if character has buff
            if (result.forStance != null) {
                return {};
            }

            // Check if character has item class
            if (result.forClass != null) {
                if (!_inventory.hasItemClassQty(result.forClass[0], result.forClass[1], result.forClass[2])) {
                    return {};
                }
            }

            if (!result.percent && !result.multiple && !result.forSlots) {
                result.add = mod[0];
            }
        }
        else if (!isNaN(mod)) {
            result = { add: mod };
        }

        result.source   = source;
        result.sourceId = sourceId;

        if (source != 'class' && source != 'talent') {
            result.diminish = true;
        }

        if (source == 'item' && sourceId != null) {
            result.slot = sourceId;
        }

        result.exclude = 0;
        if (stat.excludeModifiers) {
            for (var i = 0, len = stat.excludeModifiers.length; i < len; ++i) {
                result.exclude += json[stat.excludeModifiers[i]] || 0;
            }
        }

        return result;
    }

    function _updateModifiers() {
        // Modifiers from stats
        _removeAllModifiers('stat');

        for (var statId in _statistics) {
            var stat = _statistics[statId];

            if (stat.addModifiers) {
                _addModifiers('stat', statId, stat.addModifiers());
            }

            if (stat.rating) {
                var
                    rating    = _statistics[stat.rating.stat],
                    ratingMod = {};

                ratingMod[statId] = [$WH.g_convertRatingToPercent(_profile.level, stat.rating.id, 1, _profile.classs), 'percentOf', stat.rating.stat];

                _addModifiers('stat', stat.rating.stat, ratingMod);
            }
        }

        // Modifiers from race
        _removeAllModifiers('race');

        var
            raceData = g_statistics.race[_profile.race],
            baseAttribs = {
                str: (raceData ? raceData[0] : 0),
                agi: (raceData ? raceData[1] : 0),
                sta: (raceData ? raceData[2] : 0),
                'int': (raceData ? raceData[3] : 0),
                spi: (raceData ? raceData[4] : 0)
            };

        _addModifiers('race', _profile.race, baseAttribs);
        _addModifiers('race', _profile.race, (raceData ? raceData[5] : 0));
        _addModifiers('race', _profile.race, (raceData ? raceData[6] : 0));

        // Modifiers from class
        _removeAllModifiers('class');

        var
            classData = g_statistics.classs[_profile.classs],
            levelData = g_statistics.combo[_profile.classs][_profile.level],
            baseAttribs = {
                str:       levelData[0],
                agi:       levelData[1],
                sta:       levelData[2],
                'int':     levelData[3],
                spi:       levelData[4],
                health:    levelData[5],
                mleatkpwr: [classData[0][3], 'percentOf', 'level'],
                rgdatkpwr: [classData[1][3], 'percentOf', 'level'],
                dodgepct:  classData[4] + levelData[9] * ((raceData ? raceData[1] : 0) + levelData[1]),
                parrypct:  classData[6],
                blockpct:  classData[8],
                def:       _profile.level * 5
            };

        baseAttribs[_checkPowerType()] = levelData[6];

        _addModifiers('class', _profile.classs, baseAttribs);
        _addModifiers('class', _profile.classs, classData[9]);
        _addModifiers('class', _profile.classs, classData[10]);

        // Modifiers from tradeskills
        _removeAllModifiers('skill');

        for (var i in _profile.skills) {
            var skillRank = Math.max(1, Math.floor(_profile.skills[i][0] / 75) * 75);

            if (g_statistics.skills[i] && g_statistics.skills[i][skillRank]) {
                _parent.fixJson(g_statistics.skills[i][skillRank]);
                _addModifiers('skill', i, g_statistics.skills[i][skillRank]);
            }
        }
    }

    function _initStatistics() {
        var statistics = {};

        for (var statId in _statistics) {
            var stat = _statistics[statId];

            stat.total = 0;
            stat.subtotal = [0, 0];
            stat.modifiers = [];

            if (stat.decimals == null) {
                stat.decimals = 0;
            }

            if (stat.multiple == null) {
                stat.multiple = 1;
            }

            if (stat.slotMultiple == null) {
                stat.slotMultiple = {};
            }

            if (stat.dependent == null) {
                stat.dependent = {};
                stat.ndependent = 0;
            }

            if (stat.magic) {
                for (var i = 0, len = _magicSchools.length; i < len; ++i) {
                    var school = {
                        total: 0,
                        subtotal: [0, 0],
                        modifiers: [],
                        dependent: {},
                        ndependent: 0,
                        decimals: stat.decimals,
                        multiple: stat.multiple,
                        slotMultiple: {}
                    };

                    $WH.cO(school.slotMultiple, stat.slotMultiple);

                    if (stat.rating) {
                        var rating = {
                            total: 0,
                            subtotal: [0, 0],
                            modifiers: [],
                            dependent: {},
                            ndependent: 0,
                            decimals: 0,
                            multiple: 1,
                            slotMultiple: {}
                        };

                        school.rating = {
                            id: stat.rating.id,
                            stat: _magicSchools[i] + stat.rating.stat
                        };

                        school.tooltipRatings = [school.rating.stat];

                        statistics[school.rating.stat] = rating;
                        school.dependent[school.rating.stat] = 1;
                        school.ndependent++;
                    }

                    statistics[_magicSchools[i] + statId] = school;
                    stat.dependent[_magicSchools[i] + statId] = 1;
                    stat.ndependent++;
                }

                delete stat.rating;
            }
            else if (stat.rating) {
                var rating = {
                    total: 0,
                    subtotal: [0, 0],
                    modifiers: [],
                    dependent: {},
                    ndependent: 0,
                    decimals: 0,
                    multiple: 1,
                    slotMultiple: {}
                };

                if (!stat.tooltipRatings) {
                    stat.tooltipRatings = [stat.rating.stat];
                }

                statistics[stat.rating.stat] = rating;
                stat.dependent[stat.rating.stat] = 1;
                stat.ndependent++;
            }
        }

        $WH.cO(_statistics, statistics);
    }

    function _prepareStatistics() {
        if (_timer > 0) {
            clearTimeout(_timer);
            _timer = 0;
        }

        _timer = setTimeout(_updateStatistics, 750);
    }

    function _updateStatistics() {
        var
            statOrder = [],
            statArray = [],
            statProcd = {};

        for (var statId in _statistics) {
            var stat = _statistics[statId];

            stat.multiple = 1;
            stat.slotMultiple = {};

            if (!stat.magic) { // Don't get modifiers
                _refreshModifiers(statId);
            }

            statArray.push(statId);
            statProcd[statId] = 0;
        }

        var sortStatistics = function (statList) {
            var tryAgain = [];

            for (var i = 0, len = statList.length; i < len; ++i) {
                var
                    statId = statList[i],
                    stat = _statistics[statId],
                    nProc = 0;

                for (var j in stat.dependent) {
                    if (statProcd[j]) {
                        nProc++;
                    }
                }

                if (nProc == stat.ndependent) {
                    statOrder.push(statId);
                    statProcd[statId] = 1;
                }
                else {
                    tryAgain.push(statId);
                }
            }

            if (tryAgain.length && tryAgain.length != statList.length) {
                sortStatistics(tryAgain);
            }
        };

        sortStatistics(statArray);

        for (var i = 0, len = statOrder.length; i < len; ++i) {
            var
                statId     = statOrder[i],
                stat       = _statistics[statId],
                subtotalDR = [0, 0];

            stat.total    = 0;
            stat.subtotal = [0, 0];

            if (stat.magic) {
                stat.subtotal[0] = _statistics[_magicSchools[0] + statId].total;

                for (var j = 1, len2 = _magicSchools.length; j < len2; ++j) {
                    var school = _statistics[_magicSchools[j] + statId];
                    if (school.total < stat.subtotal[0]) {
                        stat.subtotal[0] = school.total;
                    }
                    subtotalDR[0] = stat.subtotal[0];
                }
            }
            else {
                for (var j = 0, len2 = stat.modifiers.length; j < len2; ++j) {
                    var mod = stat.modifiers[j];

                    if (mod.multiple) {
                        stat.multiple += mod.multiple;
                    }
                    else if (mod.slotMultiple) {
                        for (var slot in mod.slotMultiple) {
                            if (stat.slotMultiple[slot] == null) {
                                stat.slotMultiple[slot] = 0;
                            }
                            stat.slotMultiple[slot] += mod.slotMultiple[slot];
                        }
                    }
                    else {
                        var
                            multiple = (stat.slotMultiple[mod.slot] || 0) + stat.multiple,
                            subType  = (mod.source == 'race' || mod.source == 'class' || mod.source == 'stat' || (mod.source == 'talent' && statId != 'manargn') || (mod.source == 'item' && statId == 'armor') ? 0 : 1),
                            useDR    = (stat.computeDiminished && mod.diminish ? 1 : 0);

                        stat.subtotal[subType] += _calcModifiers(mod, multiple);
                        subtotalDR[useDR]      += _calcModifiers(mod, multiple);
                    }
                }
            }

            if (!stat.decimals) {
                for (var j = 0; j < 2; ++j) {
                    stat.subtotal[j] = Math.floor(stat.subtotal[j]);
                    subtotalDR[j] = Math.floor(subtotalDR[j]);
                }
            }

            if (stat.computeDiminished) {
                subtotalDR[1] = stat.computeDiminished(subtotalDR[1]);
                stat.total = parseFloat(subtotalDR[0]) + parseFloat(subtotalDR[1]);
            }
            else {
                stat.total = parseFloat(stat.subtotal[0]) + parseFloat(stat.subtotal[1]);
            }

            stat.total = stat.total.toFixed(stat.decimals);
        }

        _updateStatBoxes();
    }

    function _initStatBoxes() {
        var
            _,
            __,
            t = $WH.ce('table'),
            tb = $WH.ce('tbody'),
            tr = $WH.ce('tr'),
            td = $WH.ce('td'),
            a,
            p,
            h3;

        t.className = 'profiler-inventory-stats';
        _tblStatBoxes = t;

        _ = $WH.ce('div');
        _.className = 'text';
        __ = $WH.ce('h2');
        __.className = 'clear';
        __.id = 'statistics';
        a = $WH.ce('a');
        a.className = 'header-link';
        a.href = '#statistics';
        $WH.ae(a, $WH.ct(LANG.pr_stats_title));
        $WH.ae(__, a);
        $WH.ae(_, __);
        $WH.ae(_container, _);

        $WH.ae(__, $WH.ct(String.fromCharCode(160)));

        p = $WH.ce('small');
        p.className = "q0";
        $WH.ae(p, $WH.ct(LANG.pr_stats_beta));
        $WH.ae(__, p);

        _divStatBars = _ = $WH.ce('div');
        _.className = 'profiler-inventory-bars';
        $WH.ae(_container, _divStatBars);

        _divStatResists = _ = $WH.ce('div');
        _.className = 'profiler-inventory-resists';
        $WH.ae(_container, _divStatResists);

        _ = $WH.ce('div');
        _.className = 'clear pad';
        $WH.ae(_container, _);

        for (var i = 0, len = _statBoxes.length; i < len; ++i) {
            td = $WH.ce('td');
            p  = $WH.ce('p');
            h3 = $WH.ce('h3');

            td.style.width = (100 / len) + '%';

            if (i == len - 1) {
                p.style.marginRight = '0';
            }

            $WH.ae(p, h3);

            var boxContent = _statBoxes[i];
            for (var j = 0, len2 = boxContent.length; j < len2; ++j) {
                var
                    boxStat = boxContent[j],
                    statId  = boxStat.id,
                    stat    = _statistics[statId],
                    a,
                    div,
                    sp1,
                    sp2;

                switch (boxStat.type) {
                    case 'title':
                        a = $WH.ce('a');
                        a.className = 'disclosure-off';
                        a.href = 'javascript:;';
                        $WH.ns(a);

                        boxContent.disclosure = a;

                        a.onclick = _expandStatBox.bind(0, boxContent);

                        $WH.st(a, _getStatBoxLabel(statId, boxStat));
                        $WH.ae(h3, a);

                        break;
                    case 'bar':
                        var bar = g_createProgressBar({ color: statId });

                        bar.style.display = 'none';

                        boxStat.bar = bar;

                        $WH.ae(_divStatBars, bar);

                        break;
                    case 'resist':
                        var icon = Icon.create(stat.icon, 1, null, 'javascript:;');

                        icon.style.cssFloat = icon.style.styleFloat = 'left';

                        a = Icon.getLink(icon);
                        a.onmouseover = _showStatBoxTooltip.bind(sp2, statId, boxStat);
                        a.onmouseout = $WH.Tooltip.hide;

                        boxStat.icon = icon;

                        $WH.ae(_divStatResists, icon);

                        break;
                    default:
                        a = $WH.ce('a');
                        a.href = 'javascript:;';
                        a.onmouseover = _showStatBoxTooltip.bind(sp2, statId, boxStat);
                        a.onmousemove = $WH.Tooltip.cursorUpdate;
                        a.onmouseout = $WH.Tooltip.hide;

                        sp2 = $WH.ce('var');
                        $WH.ae(a, sp2);

                        boxStat.span = sp2;

                        sp1 = $WH.ce('em');
                        $WH.ae(sp1, $WH.ct(_getStatBoxLabel(statId)));
                        $WH.ae(a, sp1);

                        div = $WH.ce('div');
                        div.className = 'clear';
                        $WH.ae(a, div);

                        $WH.ae(p, a);

                        div = $WH.ce('small');
                        div.className = 'q2';
                        div.style.display = 'none';

                        boxStat.detail = div;

                        a.onclick = _toggleStatBoxDetail.bind(div, statId, boxStat, null, boxContent);

                        $WH.ae(p, div);

                        break;
                }
            }

            $WH.ae(td, p);
            $WH.ae(tr, td);
        }

        $WH.ae(tb, tr);
        $WH.ae(t, tb);
        $WH.ae(_container, _tblStatBoxes);

        _ = $WH.ce('div');
        _.className = 'pad';
        $WH.ae(_container, _);

        div = $WH.ce('div');
        div.className = 'text';
        $WH.ae(div, $WH.ct(LANG.pr_stats_warning));
        $WH.ae(_container, div);
    }

    function _updateStatBoxes() {
        for (var i = 0, len = _statBoxes.length; i < len; ++i) {
            var boxContent = _statBoxes[i];
            for (var j = 0, len2 = boxContent.length; j < len2; ++j) {
                var
                    boxStat = boxContent[j],
                    statId  = boxStat.id,
                    stat    = _statistics[statId],
                    value;

                switch (boxStat.type) {
                    case 'title':
                        break;
                    case 'bar':
                        if (_checkPowerType(statId)) {
                            value = _getStatBoxValue(statId);
                            $WH.st(boxStat.bar.firstChild.firstChild, _getStatBoxLabel(statId) + ' ' + value);
                            boxStat.bar.style.display = (value > 0 ? '' : 'none');
                        }
                        else
                            boxStat.bar.style.display = 'none';

                        break;
                    case 'resist':
                        value = [_getStatBoxValue(statId)];
                        Icon.setNumQty(boxStat.icon, value);

                        break;
                    default:
                        value = _getStatBoxValue(statId);
                        $WH.st(boxStat.span, value);
                        boxStat.span.className = (stat.subtotal[1] > 0 ? 'q2' : (stat.subtotal[1] < 0 ? 'q10' : ''));
                        boxStat.detail.innerHTML = _getStatBoxTooltip(statId, boxStat, true);

                        break;
                }
            }
        }
    }

    function _expandStatBox(boxContent) {
        var display = (boxContent.disclosure.className == 'disclosure-off');

        for (var j = 0, len2 = boxContent.length; j < len2; ++j) {
            var
                boxStat = boxContent[j],
                statId = boxStat.id;

            if (boxStat.type == 'title' || boxStat.type == 'bar' || boxStat.type == 'resist') {
                continue;
            }

            (_toggleStatBoxDetail.bind(div, statId, boxStat, display, boxContent))();
        }

        boxContent.disclosure.className = 'disclosure-' + (display ? 'on' : 'off');
    }

    function _getStatBoxValue(statId) {
        var stat = _statistics[statId];
        return (stat.total < 0 ? 0 : stat.total) + (stat.decimals > 0 && !stat.nopct ? '%' : '');
    }

    function _getStatBoxLabel(statId, boxStat) {
        var stat = (boxStat ? boxStat : _statistics[statId]);

        if (stat.label != null && LANG[stat.label] != null) {
            return LANG[stat.label];
        }

        if (LANG['pr_stats_' + statId] != null) {
            return LANG['pr_stats_' + statId];
        }

        return statId;
    }

    function _getStatBoxTooltip(statId, boxStat, noLabel) {
        var
            stat    = _statistics[statId],
            tooltip = (stat.getTooltip ? stat.getTooltip() : LANG['pr_statstt_' + statId])
            buff    = '';

        if (!noLabel) {
            buff += '<b>' + _getStatBoxLabel(statId);

            buff += ' ' + (stat.total < 0 ? 0 : stat.total) + (stat.decimals > 0 ? '%' : '');

            if (stat.subtotal[1] > 0 || stat.subtotal[1] < 0) {
                buff += ' (' + stat.subtotal[0].toFixed(stat.decimals) + '<span class="' + (stat.subtotal[1] < 0 ? 'q10">' : 'q2">+') + stat.subtotal[1].toFixed(stat.decimals) + '</span>)';
            }

            buff += '</b>';
        }

        if (tooltip != null) {
            var args = [tooltip];

            if (stat.magic) {
                var
                    ttSchools = '<table width="100%">',
                    ttRating = [];

                for (var i = 0, len = _magicSchools.length; i < len; ++i) {
                    var
                        schoolId = _magicSchools[i] + statId,
                        school = _statistics[schoolId];

                    ttSchools += '<tr><td class="q"><span class="moneyschool' + _magicSchools[i] + '">' + LANG['pr_statstt_' + _magicSchools[i]] + '</span></td>' +
                                 '<td class="q" align="right">' + school.total + (stat.decimals > 0 ? '%' : '') + '</td></tr>';

                    if (school.tooltipRatings && i == 0) {
                        for (var j = 0, len2 = school.tooltipRatings.length; j < len2; ++j) {
                            ttRating.push(_statistics[school.tooltipRatings[j]].total);
                            ttRating.push(_getModifiers('stat', school.tooltipRatings[j], schoolId));
                        }
                    }
                }

                ttSchools += '</table>';

                args.push(ttSchools);
                args = args.concat(ttRating);
            }

            if (stat.tooltipRatings) {
                for (var i = 0, len = stat.tooltipRatings.length; i < len; ++i) {
                    args.push(_statistics[stat.tooltipRatings[i]].total);
                    args.push(_getModifiers('stat', stat.tooltipRatings[i], statId));
                }
            }

            if (stat.tooltipModifiers) {
                for (var i = 0, len = stat.tooltipModifiers.length; i < len; ++i) {
                    args.push(_getModifiers('stat', statId, stat.tooltipModifiers[i]));
                }
            }

            if (stat.tooltipCompute) {
                args = args.concat(stat.tooltipCompute(stat.total));
            }

            args.push(stat.total);
            args.push(_profile.level);

            if (!noLabel) {
                buff += '<br /><span class="q">' + $WH.sprintfo(args) + '</span>';
            }
            else {
                buff = $WH.str_replace($WH.str_replace('- ' + $WH.str_replace($WH.sprintfo(args), '<br />', '<!--br />- '), '<!--br />', '<br />'), '- <table', '<table');
            }
        }

        return buff;
    }

    function _showStatBoxTooltip(statId, boxStat, e) {
        if (boxStat.type == 'resist') {
            $WH.Tooltip.show(boxStat.icon, _getStatBoxTooltip(statId, boxStat));
        }
        else {
            $WH.Tooltip.showAtCursor(e, _getStatBoxTooltip(statId, boxStat));
        }
    }

    function _toggleStatBoxDetail(statId, boxStat, display, boxContent) {
        if (display == null) {
            display = (boxStat.detail.style.display == 'none');
        }

        boxStat.detail.style.display = (display ? 'block' : 'none');

        for (var i = 0, len = boxContent.length; i < len; ++i) {
            if (!boxContent[i].detail) {
                continue;
            }

            if (boxContent[i].detail.style.display == 'none') {
                boxContent.disclosure.className = 'disclosure-off';
                return;
            }
        }

        boxContent.disclosure.className = 'disclosure-on';
    }
}

function ProfilerInventory(_parent) {
    var
        _self = this,

        _parentVars,
        _profile,
        _statistics,
        _tabs,

        _timer,
        _inited,

        _data = [],
        _itemType,
        _enchantSource,
        _enchantStat,
        _gemSource,
        _gemColor,

        _mvInited,
        _shiftClick = ($WH.Browser.opera),
        _cursorPos = { x: 0, y: 0 },

        _container,
        _divModel,
        _swfModel,
        _divInventory,
        _lnkPrint,
        _divTipRclk,
        _spSortWeighted,

        _listview,
        _lvItems,
        _spSearchName,
        _searchMsg,
        _lvSubitems,
        _lvEnchants,
        _lvGems,

        _currentSlot = -1,
        _currentSocket = -1,
        _currentColor = -1,
        _currentScale = {},

        _currentSearch = '',
        _searchResults,

        _slots = [
            // The ID matches up to g_item_slots for localization.

            // Slots with nomodel = 1 will not be displayed in the modelviewer by default.
            // Slots with nomodel = 2 will not be displayed in the modelviewer at all.

            // Slots with enchant = 1 have valid enchants available for those items (ditto sockets).
            // Slots with enchant = 2 only have enchants available for specific skills (ditto sockets).

            // The enchant slot mask is (1 << (itemSlot - 1)) -- not all itemslots (such as off hands) have enchants.

            { id:  1, name: 'head',     itemslots: [1],              enchant: 1, enchantlevel: 50 },
            { id:  2, name: 'neck',     itemslots: [2],                          nomodel: 2 },
            { id:  3, name: 'shoulder', itemslots: [3],              enchant: 1, enchantlevel: 50 },
            { id: 15, name: 'chest',    itemslots: [16],             enchant: 1 },
            { id:  5, name: 'chest',    itemslots: [5, 20],          enchant: 1, upgradeslots: [5] },
            { id:  4, name: 'shirt',    itemslots: [4],                          noupgrade: 1 },
            { id: 19, name: 'tabard',   itemslots: [19],                         noupgrade: 1 },
            { id:  9, name: 'wrists',   itemslots: [9],              enchant: 1, socket: 2, socketskill: 164 },
            { id: 10, name: 'hands',    itemslots: [10],             enchant: 1, socket: 2, socketskill: 164 },
            { id:  6, name: 'waist',    itemslots: [6],              enchant: 2, socket: 1, socketlevel: 70, enchantskill: 202 },
            { id:  7, name: 'legs',     itemslots: [7],              enchant: 1 },
            { id:  8, name: 'feet',     itemslots: [8],              enchant: 1 },
            { id: 11, name: 'finger',   itemslots: [11],             enchant: 2, nomodel: 2, enchantskill: 333 },
            { id: 12, name: 'finger',   itemslots: [11],             enchant: 2, nomodel: 2, enchantskill: 333 },
            { id: 13, name: 'trinket',  itemslots: [12],                         nomodel: 2 },
            { id: 14, name: 'trinket',  itemslots: [12],                         nomodel: 2 },
            { id: 16, name: 'mainhand', itemslots: [21, 13, 17],     enchant: 1, weapon: 1 },
            { id: 17, name: 'offhand',  itemslots: [22, 13, 23, 14], enchant: 3, weapon: 2, noenchantclasses: [-5] },
            { id: 18, name: 'ranged',   itemslots: [15, 25],         enchant: 3, weapon: 1, classes: [3, 8, 5, 4, 9, 1], nomodel: 1, noenchantclasses: [16, 19] },
            { id: 18, name: 'relic',    itemslots: [28],                         classes: [6, 11, 2, 7], nomodel: 2 } // Special case
        ];

        _proficiencies = {
             1: { 2: [0, 1, 2, 3, 4, 5, 6, 7, 8, 10, 13, 15, 16, 18], 4: [6, 1, 2, 3, 4]    },
             2: { 2: [0, 1, 4, 5, 6, 7, 8],                           4: [6, 7, 1, 2, 3, 4] },
             3: { 2: [0, 1, 2, 3, 6, 7, 8, 10, 13, 15, 16, 18],       4: [1, 2, 3]          },
             4: { 2: [2, 3, 4, 7, 13, 15, 16, 18],                    4: [1, 2]             },
             5: { 2: [4, 10, 15, 19],                                 4: [1]                },
             6: { 2: [0, 1, 4, 5, 6, 7, 8],                           4: [10, 1, 2, 3, 4]   },
             7: { 2: [0, 1, 4, 5, 10, 13, 15],                        4: [6, 9, 1, 2, 3]    },
             8: { 2: [7, 10, 15, 19],                                 4: [1]                },
             9: { 2: [7, 10, 15, 19],                                 4: [1]                },
            11: { 2: [4, 5, 6, 10, 13, 15],                           4: [8, 1, 2]          }
        },

    jsonEquipCopy = {
        id: 1,
        name: 1,
        level: 1,
        slot: 1,
        source: 1,
        sourcemore: 1,
        nsockets: 1,
        socket1: 1,
        socket2: 1,
        socket3: 1
    };

    this.initialize = function (container, tabIndex) {
        _container = $WH.ge(container);
        if (!_container) {
            return;
        }

        _parentVars = _parent.getVars();
        _profile    = _parentVars.profile;
        _statistics = _parentVars.statistics;
        _talents    = _parentVars.talents;
        _tabs       = _parentVars.tabs;

        _initModel();
        _initCharacter();
        _initListview(tabIndex);
    };

    this.setInventory = function (inventory) {
        _setInventory(inventory);
    };

    this.getInventory = function () {
        return _getInventory();
    };

    this.updateInventory = function () {
        _updateInventory();
    };

    this.addItems = function (items) {
        var slots = {};

        for (var i = 0, len = items.length; i < len; ++i) {
            var item = g_items[items[i]];

            if (item) {
                var slotId = _getValidSlot(item.jsonequip.slotbak, 0, 1);

                if (slotId == -1) {
                    continue;
                }

                if (slots[slotId] && (slotId == 12 || slotId == 14 || slotId == 16)) {
                    slotId++;
                }

                slots[slotId] = items[i];

                _equipItem(items[i], slotId);
            }
        }

        _updateModel(-1, 1);
    };

    this.equipItem = function (itemId, itemSlotId) {
        var slotId = _getValidSlot(itemSlotId, 0, 1);

        Lightbox.hide();
        _equipItem(itemId, slotId);
    };

    this.equipSubitem = function (subitemId, slotId) {
        var slotId = _getValidSlot(slotId, 1);

        Lightbox.hide();
        _equipSubitem(subitemId, slotId);
    };

    this.socketItem = function (gemId, socketId, slotId) {
        var
            slotId = _getValidSlot(slotId, 1),
            socketId = _getValidSocket(socketId, 1);

            Lightbox.hide();
        _socketItem(gemId, socketId, slotId);
    };

    this.enchantItem = function (enchantId, slotId) {
        var slotId = _getValidSlot(slotId, 1);

        Lightbox.hide();
        _enchantItem(enchantId, slotId);
    };

    this.updateAllIcons = function () {
        _updateAllIcons();
    };

    this.updateAllHeirlooms = function () {
        _updateAllHeirlooms();
    };

    this.updateAllMenus = function () {
        _updateAllMenus();
    };

    this.updateMenu = function (slotId, a) {
        _updateMenu(slotId, a);
    };

    this.onMouseUpSlot = function (slotId, a, e) {
        _onMouseUpSlot(slotId, a, e);
    };

    this.updateListview = function () {
        _updateListview();
    };

    this.printSummary = function () {
        _printSummary();
    };

    this.showModel = function (visible) {
        _divModel.style.left = (visible ? '' : '-2323px');
    };

    this.updateModel = function (slotId, refresh) {
        _updateModel(slotId, refresh);
    };

    this.matchGemSocket = function (gemColor, socketColor) {
        return _matchGemSocket(gemColor, socketColor);
    };

    this.getGearScore = function (slotId) {
        return _getGearScore(slotId);
    };

    this.getAverageLevel = function () {
        return _getAverageLevel();
    };

    this.hasItemClassQty = function (classId, subclassMask, qty) {
        return _hasItemClassQty(classId, subclassMask, qty);
    };

    function _isSlotNeeded(slotId, checkWeapons) {
        var equip = true;

        equip = equip && (!_data[slotId].classes || (_profile.classs != -1 && $WH.array_index(_data[slotId].classes, _profile.classs)));

        if (checkWeapons && slotId == 17 && !_talents.hasTitansGrip()) { // Special check for OH items
            var itemId = _getSlotItem(16)[0];

            equip = equip && (!itemId || !g_items[itemId] || g_items[itemId].jsonequip.slotbak != 17);
        }

        return equip;
    }

    function _setInventory(inventory) {
        _inited = false;

        if (inventory) {
            for (var i = 0, len = _data.length; i < len; ++i) {
                var item = _getSlotItem(i);

                _data[i].history = [];

                if (inventory[_data[i].id] != null && inventory[_data[i].id].length == 8) {
                    var item = inventory[_slots[i].id];

                    _equipItem(item[0], i, 1);
                    _equipSubitem(item[1], i);
                    _enchantItem(item[2], i);

                    for (var j = 4; j < 8; ++j) {
                        _socketItem(item[j], j, i);
                    }
                }
            }
        }
        else {
            for (var i = 0, len = _data.length; i < len; ++i) {
                _unequipItem(i);
            }
        }

        _inited = true;

        _updateInventory();
    }

    function _updateInventory() {
        for (var i = 0, len = _data.length; i < len; ++i) {
            $WH.cO(_data[i], _slots[i]);

            if (_slots[i].nomodel != 2) {
                _data[i].nomodel = (_profile.nomodel & 1 << i ? 1 : 0);
            }
        }

        // Add two-handed items to off-hand for Fury warriors
        if (_talents.hasTitansGrip()) {
            _data[17].itemslots.push(17);
        }

        // Display ranged weapons for hunters by default
        if (_profile.classs == 3) {
            _data[16].nomodel = _data[17].nomodel = 1;
            _data[18].nomodel = 0;
        }
        else {
            _data[18].nomodel = 1;
        }

        // Hide ugly guild tabard by default
        if (_getSlotItem(6)[0] == 5976 || _getSlotItem(6)[0] == 69209 || _getSlotItem(6)[0] == 69210) {
            _data[6].nomodel = 1;
        }

        _updateModel();
        _updateAllIcons();
        _updateAllMenus();
        _updateListview();
    }

    function _getInventory() {
        var inventory = {};

        for (var i = 0, len = _data.length; i < len; ++i) {
            if (_isSlotNeeded(i)) {
                inventory[_data[i].id] = _getSlotItem(i);
            }
        }

        return inventory;
    }

    function _getGearScore(slotId) {
        var
            slotId = _getValidSlot(slotId),
            buildData = _talents.getTalentBuilds(),
            gearscore = {},
            mh = 1,
            oh = 1,
            mainHand,
            offHand;

        for (var i = 0, len = _data.length; i < len; ++i) {
            var item = _getSlotItem(i);

            gearscore[i] = { item: 0, ench: 0, gems: 0 };

            if (item[0] && g_items[item[0]]) {
                if (i == 16) {
                    mainHand = g_items[item[0]].jsonequip;
                }
                else if (i == 17) {
                    offHand = g_items[item[0]].jsonequip;
                }
                else {
                    gearscore[i].item += Math.round(g_items[item[0]].jsonequip.gearscore);
                }
            }

            var bonus = 0;
            if (item[2] && g_enchants[item[2]]) {
                bonus = Math.round(g_enchants[item[2]].gearscore);
            }
            if (_data[i].id == 18 && _profile.classs != 3) {
                gearscore[i].bonus = bonus = Math.max(bonus, 20);
            }

            if (item[2] && g_enchants[item[2]]) {
                gearscore[i].ench += bonus;
            }
            // else {                                       // aowow - adds 20 even if the slot is empty .. what?
            else if (item[0]) {
                gearscore[i].item += bonus;
            }

            for (var j = 0; j < 4; ++j) {
                if (item[j + 4] && g_gems[item[j + 4]]) {
                    gearscore[i].gems += Math.round(g_gems[item[j + 4]].gearscore);
                }
            }
        }

        if (mainHand) { // Main Hand Equipped
            if (offHand) { // Off Hand Equipped
                if (mainHand.slotbak == 21 || mainHand.slotbak == 13) { // Main Hand, One Hand
                    if (offHand.slotbak == 22 || offHand.slotbak == 13) { // Off Hand, One Hand
                        if (_profile.classs == 6 || _profile.classs == 3 || _profile.classs == 4 || // Death Knight, Hunter, Rogue
                           (_profile.classs == 7 && buildData.spent[1] > 30 && buildData.spec == 2) || // Enhancement Shaman Over 39
                           (_profile.classs == 1 && buildData.spent[1] < 51 && buildData.spec == 2)) // Fury Warrior Under 60
                        {
                            mh = 64 / 27;
                            oh = 64 / 27;
                        }
                    }
                    else if (offHand.slotbak == 23 || offHand.slotbak == 14) { // Held in Off Hand, Shield
                        if (_profile.classs == 5 || _profile.classs == 9 || _profile.classs == 8 || // Priest, Warlock, Mage
                           (_profile.classs == 11 && (buildData.spec == 1 || buildData.spec == 3)) || // Balance Druid, Restoration Druid
                           (_profile.classs == 7 && (buildData.spec == 1 || buildData.spec == 3)) || // Elemental Shaman, Restoration Shaman
                           (_profile.classs == 2 && (buildData.spec == 1 || buildData.spec == 2)) || // Holy Paladin, Protection Paladin
                           (_profile.classs == 1 && buildData.spec == 3))  // Protection Warrior
                        {
                            mh = 64 / 27;
                            oh = 16 / 9;
                        }
                    }
                }
            }
            else if (mainHand.slotbak == 17) {  // Two Handed
                if (_profile.classs == 5 || _profile.classs == 9 || _profile.classs == 8 || // Priest, Warlock, Mage
                    _profile.classs == 11 || _profile.classs == 3 || _profile.classs == 6 || // Druid, Hunter, Death Knight
                   (_profile.classs == 7 && buildData.spent[1] < 31 && buildData.spec == 2) || // Enhancement Shaman Under 40
                   (_profile.classs == 2 && buildData.spec == 3) || // Retribution Paladin
                   (_profile.classs == 1 && buildData.spec == 1)) // Arms Warrior
                {
                    mh = 2;
                    oh = 0;
                }
            }
        }

        if (mainHand) {
            gearscore[16].item += Math.round(mainHand.gearscore * mh);
        }

        if (offHand) {
            gearscore[17].item += Math.round(offHand.gearscore * oh);
        }

        if (slotId != -1) {
            return gearscore[slotId];
        }

        var total = { item: 0, ench: 0, gems: 0 };
        for (var i in gearscore) {
            if (_isSlotNeeded(i)) {
                total.item += gearscore[i].item;
                total.ench += gearscore[i].ench;
                total.gems += gearscore[i].gems;
            }
        }

        return total;
    }

    function _getAverageLevel() {
        var
            level = 0,
            n = 0;

        for (var i = 0, len = _data.length; i < len; ++i) {
            if (i == 5 || i == 6) {
                continue;
            }

            var item = _getSlotItem(i);
            if (item[0] && g_items[item[0]]) {
                level += g_items[item[0]].jsonequip.level;
                n++;
            }
            else if (_isSlotNeeded(i, 1)) {
                n++;
            }
        }

        return Math.floor(level / n);
    }

    function _showUpgradesMenu(e) {
        e = $WH.$E(e);

        var inventory = _getInventory(),
        buildData = _talents.getTalentBuilds(),
        items = [],
        menu = [];

        for (var i in inventory) {
            if (inventory[i][0]) {
                items.push(inventory[i][0]);
            }
        }

        var url = '?items&filter=maxrl=' + _profile.level + ';si=' + (_profile.faction + 1) + ';ub=' + _profile.classs + ';cr=161;crs=1;crv=0$1;gb=1;upg=' + items.join(':');

        menu.push([0, buildData.name, $WH.sprintf(url, buildData.filter), null, { newWindow: true }]);

        if (g_user.weightscales && g_user.weightscales.length) {
            for (var i = 0, len = g_user.weightscales.length; i < len; ++i) {
                var scale = g_user.weightscales[i];
                menu.push([0, scale.name, $WH.sprintf(url, pr_getScaleFilter(scale)), null, { newWindow: true }]);
            }
        }

        var pos = $WH.g_getCursorPos(e);
        Menu.showAtXY(menu, pos.x, pos.y);
    }

    function _compare() {
        var
            inventory = _getInventory(),
            items = [];

        for (var i in inventory) {
            if (inventory[i][0]) {
                items.push(inventory[i].join('.').replace(/(\.0)+$/, ""));
            }
        }

        su_addToSaved(items.join(':'), items.length, true, _profile.level);
    }

    function _initModel() {
        var
            _  = $WH.ce('div'),
            __ = _divModel = $WH.ce('div');

        _.className = 'profiler-model-outer';
        __.className = 'profiler-model';

        _swfModel = $WH.ce('div');
        _swfModel.id = 'hsae8y8hjidj';

        $WH.ae(__, _swfModel);
        $WH.ae(_, __);

        _container.parentNode.parentNode.insertBefore(_, _container.parentNode);

        // custom: hide the swfObject, when displaying tooltips only static tooltips with .show are needed
        (function (){
            var
                oldHide = $WH.Tooltip.hide;
                oldShow = $WH.Tooltip.show;

            $WH.Tooltip.hide = function (){
                oldHide.apply(this);

                var a = $WH.ge(_swfModel.id);
                if (a) {
                    a.style.visibility = "visible"
                }
            }

            $WH.Tooltip.show = function (_this, text, paddX, paddY, spanClass, text2) {
                oldShow.apply(this, [_this, text, paddX, paddY, spanClass, text2]);

                if ($WH.Tooltip.tooltip.style.visibility != "visible") {
                    return;
                }

                var w = $WH.ge(_swfModel.id);
                if (!w) {
                    return
                }

                try {
                    var v = w.getBoundingClientRect();
                    var u = $WH.Tooltip.tooltip.getBoundingClientRect()
                }
                catch (y) {
                    return
                }

                var a = true;
                a &= (u.bottom > v.top && u.top < v.bottom);
                a &= (u.right > v.left && u.left < v.right);

                var i = a ? "hidden" : "visible";
                if (i != w.style.visibility) {
                    w.style.visibility = i
                }
            }
        })(window || {});
        // !custom
    }

    function _updateModel(slotId, refresh) {
        if (g_user.cookies && g_user.cookies['profiler3d'] == false) {
            if (!_mvInited) {
                $WH.ee(_swfModel);

                var btn = RedButton.create(LANG.button_viewin3d, true);
                btn.onclick = function () {
                    g_user.cookies['profiler3d'] = 1;
                    _updateModel(-1, 1);
                };
                $WH.ae(_swfModel, btn);

                _mvInited = true;
            }

            return;
        }

        var
            origSlotId = slotId,
            slotId = _getValidSlot(slotId),
            equipList = [],
            emptySlots = [];

        for (var i = 0, len = _data.length; i < len; ++i) {
            if (slotId == -1 || i == slotId) {
                var itemId = _getSlotItem(i)[0];

                if (!_data[i].nomodel && g_items[itemId]) {
                    equipList.push((((i == 16 || i == 17) && g_items[itemId].jsonequip.slotbak != 14) ? _data[i].itemslots[0] : g_items[itemId].jsonequip.slotbak));
                    equipList.push(g_items[itemId].jsonequip.displayid);
                }
                else {
                    emptySlots.push(_data[i].itemslots[0]);
                }
            }
        }

        if (!_mvInited || refresh) {
            var flashVars = {
                model: g_file_races[_profile.race] + g_file_genders[_profile.gender],
                modelType: 16,
                equipList: equipList,
                sk: _profile.skincolor,
                ha: _profile.hairstyle,
                hc: _profile.haircolor,
                fa: _profile.facetype,
                fh: _profile.features,
                fc: _profile.haircolor,
                mode: 3,
                // contentPath: 'http://static.wowhead.com/modelviewer/'
                contentPath: g_staticUrl + '/modelviewer/'
            };

            var params = {
                wmode: 'direct',                            // aowow: was 'opaque'; doesn't draw with this
                quality: 'high',
                allowscriptaccess: 'always',
                allowfullscreen: true,
                menu: false,
                bgcolor: '#141414'
            };

            var attributes = {
                style: 'outline: none'
            };

            swfobject.embedSWF(g_staticUrl + '/modelviewer/ZAMviewerfp11.swf', _swfModel.id, '100%', '100%', '10.0.0', g_staticUrl + '/modelviewer/expressInstall.swf', flashVars, params, attributes);
            // swfobject.embedSWF('http://static.wowhead.com/modelviewer/ZAMviewerfp11.swf', _swfModel.id, '100%', '100%', '10.0.0', 'http://static.wowhead.com/modelviewer/expressInstall.swf', flashVars, params, attributes);

            _mvInited = true;
        }
        /*
            aowow: the idea of this is to directly access the swf.
            though ZAMviewerfp11.swf is unpredictable af

            custom: try/catch; check for empty equipList
        */
        else {
            _swfModel = $WH.ge(_swfModel.id);

            if (_swfModel.clearSlots) {
                _swfModel.setAppearance(_profile.hairstyle, _profile.haircolor, _profile.facetype, _profile.skincolor, _profile.features, _profile.haircolor);

                try { _swfModel.clearSlots(emptySlots); } catch (x) { }
                if (equipList.length)
                    _swfModel.attachList(equipList);
            }
        }
    }

    function _toggleDisplay(slotId) {
        var slotId = _getValidSlot(slotId, 1);

        if (_data[slotId].nomodel == 2) {
            return;
        }

        _data[slotId].nomodel = !_data[slotId].nomodel;
        _profile.nomodel ^= 1 << slotId;

        if (!_data[slotId].nomodel) {
            if ((slotId == 16 || slotId == 17) && _data[18].nomodel != 2) {
                _data[18].nomodel = 1;

                _updateModel(18);
                _updateMenu(18);
            }
            else if (slotId == 18) {
                _data[16].nomodel = _data[17].nomodel = 1;

                _updateModel(16);
                _updateMenu(16);

                _updateModel(17);
                _updateMenu(17);
            }
        }

        _updateModel(slotId);
        _updateIcon(slotId);
        _updateMenu(slotId);
    }

    function _initCharacter() {
        var
            _,
            __,
            a,
            divLeft = $WH.ce('div'),
            divRight = $WH.ce('div'),
            divBottom = $WH.ce('div'),
            divButton = $WH.ce('div');

        _divInventory = _ = $WH.ce('div');
        _.className = 'profiler-inventory';

        __ = $WH.ce('div');
        __.className = 'profiler-inventory-inner';

        divLeft.className = 'profiler-inventory-left';
        divRight.className = 'profiler-inventory-right';
        divBottom.className = 'profiler-inventory-bottom';
        divButton.className = 'profiler-inventory-buttons';

        _btnCompare = RedButton.create(LANG.button_compare, true, _compare);
        _btnUpgrades = RedButton.create(LANG.button_upgrades, true, _showUpgradesMenu);

        $WH.ae(divButton, _btnCompare);
        $WH.ae(divButton, _btnUpgrades);

        $WH.ae(__, divLeft);
        $WH.ae(__, divRight);
        $WH.ae(__, divBottom);
        $WH.ae(__, divButton);

        $WH.ae(_divInventory, __);
        $WH.ae(_container, _divInventory);

        for (var i = 0, len = _slots.length; i < len; ++i) {
            _data[i] = $WH.dO(_slots[i]);
            _data[i].history = [[0, 0, 0, 0, 0, 0, 0, 0]]; // id, subid, perm enchant, temp enchant, gem1, gem2, gem3, gem4
        }

        for (var i = 0, len = _data.length; i < len; ++i) {
            _data[i].icon = Icon.create('inventoryslot_' + _data[i].name, 1, null, 'javascript:;');

            $WH.ae((i < 8 ? divLeft : (i < 16 ? divRight : divBottom)), _data[i].icon);

            a = Icon.getLink(_data[i].icon);
            a.oncontextmenu = a.onclick = $WH.rf;
            a.onmouseup = _onMouseUpSlot.bind(0, i, a);
        }

        _ = $WH.ce('div');
        _.style.clear = 'left';
        $WH.ae(_container, _);

        div = $WH.ce('div');
        div.className = 'text';
        div.style.width = '290px';
        div.style.marginTop = '25px';
        $WH.ae(div, $WH.ct(_shiftClick ? LANG.pr_tip_sclkopt : LANG.pr_tip_rclkopt));
        $WH.ae(_container, div);
    }

    function _getValidSlot(slotId, useCurrentSlot, isItemSlot) {
        var validId = -1;

        if (useCurrentSlot && _currentSlot != -1) {
            validId = _currentSlot;
        }
        else if (typeof slotId == 'number') {
            validId = slotId;
        }
        else if (typeof slotId == 'string' && !isNaN(parseInt(slotId))) {
            validId = parseInt(slotId);
        }

        if (validId != -1 && isItemSlot) {
            for (var i = 0, len = _data.length; i < len; ++i) {
                if ($WH.array_index(_data[i].itemslots, validId)) {
                    return i;
                }
            }
        }

        if (isItemSlot || validId >= _data.length) {
            validId = -1;
        }

        return validId;
    }

    function _getValidSocket(socketId, useCurrentSocket) {
        var validId = -1;

        if (useCurrentSocket && _currentSocket != -1) {
            validId = _currentSocket;
        }

        else if (typeof socketId == 'number') {
            validId = socketId;
        }

        else if (typeof socketId == 'string' && !isNaN(parseInt(socketId))) {
            validId = parseInt(socketId);
        }

        if (validId < 4 || validId > 8) {
            validId = -1;
        }

        return validId;
    }

    function _getSlotItem(slotId) {
        var
            slotId = _getValidSlot(slotId),
            _      = _data[slotId];

        if (slotId == -1 || !_.history.length) {
            return [0, 0, 0, 0, 0, 0, 0, 0];
        }

        return _.history[_.history.length - 1];
    }

    function _isSlotEmpty(slotId) {
        var itemId = _getSlotItem(slotId)[0];
        return ! itemId || !g_items[itemId];
    }

    function _hasEnhancements(slotId) {
        var item = _getSlotItem(slotId);
        for (var i = 1; i < 9; ++i) {
            if (item[i] != 0) {
                return true;
            }
        }
        return false;
    }

    function _clearEnhancements(slotId) {
        _unsocketAllItem(slotId);
        _unenchantItem(slotId);
    }

    function _equipItem(itemId, slotId, ignoreSubitem) {
        var
            origSlotId = slotId,
            slotId = _getValidSlot(slotId, 1),
            curItem = _getSlotItem(slotId);

        if (itemId == curItem[0]) {
            return;
        }

        if (!itemId) {
            return _unequipItem(slotId);
        }

        if (!curItem[0]) {
            _data[slotId].history.splice(-1, 1);
        }

        _clearEnhancements(slotId);

        _data[slotId].history.push([itemId, 0, 0, 0, 0, 0, 0, 0]);

        if (curItem[2]) {
            _enchantItem(curItem[2], slotId);
        }

        for (var j = 4; j < 8; ++j) {
            if (curItem[j]) {
                _socketItem(curItem[j], j, slotId);
            }
        }

        _parent.updateSavedChanges();

        _updateModel(origSlotId);
        _updateAllIcons();
        _updateHeirloom(slotId);
        _updateMenu(slotId);
        _updateListview();
        _updateItemSets();

        _statistics.removeModifiers('item', slotId);

        if (_isSlotNeeded(slotId) && g_items[itemId]) {
            _statistics.addModifiers('item', slotId, g_items[itemId].jsonequip);
        }

        if (g_items[itemId] && g_items[itemId].jsonequip.subitems && !ignoreSubitem) {
            _openSubitemPicker(slotId);
        }
    }

    function _unequipItem(slotId) {
        var slotId = _getValidSlot(slotId, 1);

        if (_isSlotEmpty(slotId)) {
            return;
        }

        _clearEnhancements(slotId);

        _data[slotId].history.push([0, 0, 0, 0, 0, 0, 0, 0]);

        _parent.updateSavedChanges();

        _updateModel(slotId);
        _updateAllIcons();
        _updateMenu(slotId);
        _updateListview();
        _updateItemSets();

        _statistics.removeModifiers('item', slotId);
    }

    function _updateItemSets() {
        // Can't actually equip or remove an entire item set at once
        // But the set bonuses need to be updated after modifying items
        var
            itemsetpcs = {},
            itemsetbak = {};

        for (var i = 0, len = _data.length; i < len; ++i) {
            if (_isSlotNeeded(i)) {
                var p = _getSlotItem(i)[0];
                if (g_items[p]) {
                    var
                        s = g_items[p].jsonequip.itemset,
                        _ = g_itemsets[s];

                    if (_) {
                        // Use the real set id for set bonus tally
                        s = _.idbak;
                        if (itemsetpcs[s] == null) {
                            itemsetpcs[s] = {};
                        }
                        itemsetpcs[s][p] = 1;
                        itemsetbak[s] = _;
                    }
                }
            }
        }

        _statistics.removeAllModifiers('itemset');

        for (var s in itemsetpcs) {
            var
                itemset = itemsetbak[s],
                nItems  = 0;

            if (!itemset.setbonus) {
                continue;
            }

            for (var p in itemsetpcs[s]) {
                nItems++;
            }

            for (var n in itemset.setbonus) {
                if (n > nItems) {
                    break;
                }
                _statistics.addModifiers('itemset', (s * 10) + n, itemset.setbonus[n]);
            }
        }
    }

    function _getItemClassQty(itemClass, subclassMask) {
        var qty = 0;

        for (var i = 0, len = _data.length; i < len; ++i) {
            var itemId = _getSlotItem(i)[0];

            if (g_items[itemId]) {
                if (g_items[itemId].jsonequip.classs == itemClass && (1 << g_items[itemId].jsonequip.subclass) & subclassMask) {
                    qty++;
                }
            }
        }

        return qty;
    }

    function _hasItemClassQty(itemClass, subclassMask, qty) {
        return _getItemClassQty(itemClass, subclassMask) >= qty;
    }

    function _equipSubitem(subitemId, slotId) {
        var
            item = _getSlotItem(slotId),
            curSubitemId = item[1];

        if (subitemId == curSubitemId || !g_items[item[0]].jsonequip.subitems || !g_items[item[0]].jsonequip.subitems[subitemId]) {
            return;
        }

        if (!subitemId) {
            return _unequipSubitem(slotId);
        }

        item = item.slice(0); // Make a copy
        item[1] = subitemId;
        _data[slotId].history.push(item);

        _parent.updateSavedChanges();

        _updateIcon(slotId);
        _updateMenu(slotId);
        _updateListview();

        _statistics.removeModifiers('subitem', slotId);

        if (_isSlotNeeded(slotId)) {
            _statistics.addModifiers('subitem', slotId, g_items[item[0]].jsonequip.subitems[subitemId].jsonequip);
        }
    }

    function _unequipSubitem(slotId) {
        var item = _getSlotItem(slotId);

        if (_isSlotEmpty(slotId) || !item[1]) {
            return;
        }

        item = item.slice(0); // Make a copy
        item[1] = 0;
        _data[slotId].history.push(item);

        _parent.updateSavedChanges();

        _updateIcon(slotId);
        _updateMenu(slotId);
        _updateListview();

        _statistics.removeModifiers('subitem', slotId);
    }

    function _socketItem(gemId, socketId, slotId) {
        var
            item     = _getSlotItem(slotId),
            curGemId = item[socketId];

        if (gemId == curGemId) {
            return;
        }

        if (!gemId || (socketId >= _getExtraSocketPos(item[0]) + (_canBeSocketed(slotId) ? 1 : 0))) {
            return _unsocketItem(socketId, slotId, true);
        }

        item = item.slice(0); // Make a copy
        item[socketId] = gemId;
        _data[slotId].history.push(item);

        _parent.updateSavedChanges();

        _updateIcon(slotId);
        _updateMenu(slotId);
        _updateListview();

        _statistics.removeModifiers('gem', (slotId * 10) + (socketId - 4));

        if (!g_gems[gemId]) {
            return;
        }

        if (_isSlotNeeded(slotId)) {
            _statistics.addModifiers('gem', (slotId * 10) + (socketId - 4), g_gems[gemId].jsonequip);
        }

        _statistics.removeModifiers('socket', slotId);

        if (_isSlotNeeded(slotId) && _hasSocketBonus(slotId)) {
            _statistics.addModifiers('socket', slotId, g_items[item[0]].jsonequip.socketbonusstat);
        }
    }

    function _unsocketItem(socketId, slotId, none) {
        var item = _getSlotItem(slotId);

        if (_isSlotEmpty(slotId) || !item[socketId]) {
            return;
        }

        var pos = _getExtraSocketPos(item[0]);

        item = item.slice(0); // Make a copy
        item[socketId] = (socketId == pos && none ? -1 : 0);
        _data[slotId].history.push(item);

        _parent.updateSavedChanges();

        _updateIcon(slotId);
        _updateMenu(slotId);
        _updateListview();

        _statistics.removeModifiers('gem', (slotId * 10) + (socketId - 4));

        _statistics.removeModifiers('socket', slotId);

        if (_isSlotNeeded(slotId) && _hasSocketBonus(slotId)) {
            _statistics.addModifiers('socket', slotId, g_items[item[0]].jsonequip.socketbonusstat);
        }
    }

    function _unsocketAllItem(slotId) {
        for (var i = 4; i < 8; ++i) {
            _unsocketItem(i, slotId, false);
        }

        if (_hasExtraSocket(slotId)) {
            _toggleExtraSocket(slotId);
        }
    }

    function _toggleExtraSocket(slotId) {
        var
            item = _getSlotItem(slotId),
            pos  = _getExtraSocketPos(item[0]);

        if (_hasExtraSocket(slotId)) {
            _unsocketItem(pos, slotId, false);
        }
        else {
            _socketItem(-1, pos, slotId);
        }
    }

    function _getExtraSocketPos(itemId) {
        if (!itemId || !g_items[itemId]) {
            return 4;
        }

        return 4 + (g_items[itemId].jsonequip.nsockets | 0);
    }

    function _hasExtraSocket(slotId) {
        var
            item = _getSlotItem(slotId),
            pos  = _getExtraSocketPos(item[0]);

        return (_canBeSocketed(slotId) && item[pos] ? 1 : 0);
    }

    function _canBeSocketed(slotId) {
        var
            slotId = _getValidSlot(slotId),
            itemId = _getSlotItem(slotId)[0];

        return (_data[slotId].socket == 1 && (_data[slotId].socketlevel == null || _data[slotId].socketlevel <= _profile.level) ||
               (_data[slotId].socket == 2 && (_profile.user || (_profile.skills && _profile.skills[_data[slotId].socketskill] && _profile.skills[_data[slotId].socketskill][0] >= 400))));
    }

    function _getMetaCondition() {
        // Supports multiple meta gems in multiple item slots
        // Just in case ;)

        var condition = {t:0};

        for (var i = 0, len = _data.length; i < len; ++i) {
            var item = _getSlotItem(i);

            if (g_items[item[0]]) { // Item must exist to have gems in it!
                for (var j = 0; j < 4; ++j) {
                    var gemId = item[j + 4];

                    if (gemId && g_gems[gemId] && g_gems[gemId].condition) {
                        for (var k in g_gems[gemId].condition) {
                            if (!condition[k]) {
                                condition[k] = 0;
                            }
                            condition[k] += g_gems[gemId].condition[k];
                            condition.t += g_gems[gemId].condition[k];
                        }
                    }
                }
            }
        }

        return condition;
    }

    function _enchantItem(enchantId, slotId) {
        var
            item = _getSlotItem(slotId),
            curEnchantId = item[2];

        if (enchantId == curEnchantId) {
            return;
        }

        if (!enchantId) {
            return _unenchantItem(slotId);
        }

        if (!g_enchants[enchantId]) {
            return;
        }

        item = item.slice(0); // Make a copy
        item[2] = enchantId;
        _data[slotId].history.push(item);

        _parent.updateSavedChanges();

        _updateIcon(slotId);
        _updateMenu(slotId);
        _updateListview();

        _statistics.removeModifiers('enchant', slotId);

        if (_isSlotNeeded(slotId)) {
            _statistics.addModifiers('enchant', slotId, g_enchants[enchantId].jsonequip);
        }
    }

    function _unenchantItem(slotId) {
        var item = _getSlotItem(slotId);

        if (_isSlotEmpty(slotId) || !item[2]) {
            return;
        }

        item = item.slice(0); // Make a copy
        item[2] = 0;
        _data[slotId].history.push(item);

        _parent.updateSavedChanges();

        _updateIcon(slotId);
        _updateMenu(slotId);
        _updateListview();

        _statistics.removeModifiers('enchant', slotId);
    }

    function _canBeEnchanted(slotId) {
        var
            slotId = _getValidSlot(slotId),
            itemId = _getSlotItem(slotId)[0];

        return (_data[slotId].enchant == 1 && (_data[slotId].enchantlevel == null || _data[slotId].enchantlevel <= _profile.level) ||
               (_data[slotId].enchant == 2 && (_profile.user || (_profile.skills && _profile.skills[_data[slotId].enchantskill] && _profile.skills[_data[slotId].enchantskill][0] >= 360))) ||
               (_data[slotId].enchant == 3 && g_items[itemId] && !$WH.array_index(_data[slotId].noenchantclasses, g_items[itemId].jsonequip.subclass)));
    }

    function _onMouseUpSlot(slotId, a, e) {
        e = $WH.$E(e);

        _currentSlot = slotId;

        if (e._button == 3 || e.shiftKey || e.ctrlKey) {
            var pos = $WH.g_getCursorPos(e);
            setTimeout(Menu.showAtXY.bind(null, a.menu, pos.x, pos.y), 1);// Timeout needed for the context menu to be disabled

            $WH.Tooltip.hide();
        }

        return false;
    }

    function _onKeyUpSearch(delay) {
        var search = $WH.trim(_spSearchName.value.replace(/\s+/g, ' '));

        if (search == _currentSearch && isNaN(delay)) {
            return;
        }
        _currentSearch = search;

        _prepareSearch(search, delay);
    }

    function _onKeyDownSearch(e) {
        e = $WH.$E(e);
        var textbox = e._target;

        switch (e.keyCode) {
            case 13: // Enter
                _lvItems.submitSearch(e);
                break;
            case 27: // Escape
                hide();
                break;
            case 38: // Up
                cycle(0);
                break;
            case 40: // Down
                cycle(1);
                break;
        }
    }

    function _updateAllMenus() {
        for (var i = 0, len = _data.length; i < len; ++i) {
            _updateMenu(i);
        }
    }

    function _updateMenu(slotId, a) {
        if (!_inited) {
            return;
        }

        var
            slotId      = _getValidSlot(slotId),
            item        = _getSlotItem(slotId),
            itemId      = item[0],
            itemLink    = item[0] + '',
            extraSocket = _hasExtraSocket(slotId),
            buildData   = _talents.getTalentBuilds();

        if (a == null) {
            a = Icon.getLink(_data[slotId].icon);
        }

        a.menu = [
            // Slot Name
            [, g_item_slots[_data[slotId].itemslots[0]]],

            // Equip
            [0, (item[0] ? LANG.pr_menu_replace : LANG.pr_menu_equip), _openItemPicker.bind(this, slotId)]
        ];

        if (!_isSlotEmpty(slotId)) {
            // Unequip
            a.menu.push([0, LANG.pr_menu_unequip, _unequipItem.bind(this, slotId)]);

            // Random Enchant
            if (item[1] || g_items[itemId].jsonequip.subitems != null) {
                a.menu.push([0, (item[1] ? LANG.pr_menu_repsubitem : LANG.pr_menu_addsubitem), _openSubitemPicker.bind(this, slotId)]);
            }
            itemLink += '.' + (item[1] || '0');

            // Enchant
            if (item[2] || _canBeEnchanted(slotId)) {
                var _ = [0, (item[2] ? LANG.pr_menu_repenchant : LANG.pr_menu_addenchant), _openEnchantPicker.bind(this, slotId), null, {}];

                if (item[2] && g_enchants[item[2]]) {
                    _[4].tinyIcon = g_enchants[item[2]].icon;
                }

                a.menu.push(_);
            }
            itemLink += '.' + (item[2] || '0');

            // temporaty Enchant
            itemLink += '.' + (item[3] || '0');

            // Gems
            if (g_items[itemId].jsonequip.nsockets || extraSocket) {
                for (var i = 0, len = (g_items[itemId].jsonequip.nsockets | 0) + extraSocket; i < len; ++i) {
                    var
                        gemId = (item[i + 4] > 0 ? item[i + 4] : 0),
                        c     = (extraSocket && i == len - 1 ? 14 : g_items[itemId].jsonequip['socket' + (i + 1)]),
                        _     = [0, (gemId ? LANG.pr_menu_repgem : LANG.pr_menu_addgem), _openGemPicker.bind(this, c, 4 + i, slotId), null, {}];

                    if (g_gems[gemId]) {
                        _[4].tinyIcon = g_gems[gemId].icon;
                    }
                    else {
                        _[4].socketColor = c;
                    }

                    a.menu.push(_);
                    itemLink += '.' + (gemId || ((extraSocket && i == len - 1) ? -1 : 0));
                }
            }

            // Extra Socket
            if (extraSocket || _canBeSocketed(slotId)) {
                var _ = [0, LANG.pr_menu_extrasock, _toggleExtraSocket.bind(this, slotId)];
                _.checked = extraSocket;

                a.menu.push(_);
            }
            if (_hasEnhancements(slotId)) {
                a.menu.push([0, LANG.pr_menu_clearenh, _clearEnhancements.bind(this, slotId)]);
            }

            // Display On Character
            if (_data[slotId].nomodel != 2) {
                var _ = [0, LANG.pr_menu_display, _toggleDisplay.bind(this, slotId)];
                _.checked = !_data[slotId].nomodel;
                a.menu.push(_);
            }

            a.menu.push([, LANG.pr_menu_links]);

            a.menu.push([0, LANG.pr_menu_compare, su_addToSaved.bind(this, itemLink.replace(/(\.0)+$/,""), 1, true, _profile.level)]);
        }

        if (_data[slotId].noupgrade != 1) {
            if (_isSlotEmpty(slotId)) {
                a.menu.push([, LANG.pr_menu_links]);
            }

            var url = '?items' + (_data[slotId].weapon ? (_data[slotId].weapon == 1 ? '=2' : '') : '=4') + '&filter=' + (_data[slotId].noupgrade && g_items[itemId] ? 'sl=' + _data[slotId].itemslots.join(':') + ';minle=' + g_items[itemId].jsonequip.level + ';' : '') + 'maxrl=' + _profile.level + ';si=' + (_profile.faction + 1) + ';ub=' + _profile.classs + ';cr=161;crs=1;crv=0' + (_data[slotId].noupgrade ? '' : '$1' + (_data[slotId].weapon ? ';gb=1' : ';gb=3') + (itemId ? ';upg=' + itemId + (_data[slotId].weapon && g_items[itemId] ? '#' + g_urlize(g_item_slots[g_items[itemId].jsonequip.slot]) : '') : '')),
                  _ = [0, LANG.pr_menu_upgrades, null, []];

            _[3].push([0, buildData.name, $WH.sprintf(url, buildData.filter), null, { newWindow: true }]);

            if (g_user.weightscales && g_user.weightscales.length) {
                for (var i = 0, len = g_user.weightscales.length; i < len; ++i) {
                    var scale = g_user.weightscales[i];
                    _[3].push([0, scale.name, $WH.sprintf(url, pr_getScaleFilter(scale)), null, { newWindow: true }]);
                }
            }

            a.menu.push(_);
        }

        if (!_isSlotEmpty(slotId)) {
            var _ = [0, LANG.pr_menu_whowears, '?profiles&filter=cr=21;crs=0;crv=' + itemId + '&sort=2', null, { newWindow: true }];
            a.menu.push(_);
        }
    }

    function _updateAllHeirlooms() {
        for (var i = 0, len = _data.length; i < len; ++i) {
            _updateHeirloom(i);
        }
    }

    function _updateHeirloom(slotId) {
        var
            slotId = _getValidSlot(slotId),
            itemId = _getSlotItem(slotId)[0];

        if (g_items[itemId] && g_items[itemId].quality == 7) { // Heirloom only
            $WH.g_setJsonItemLevel(g_items[itemId].jsonequip, _profile.level);
            g_items.add(itemId, g_items[itemId]);

            _statistics.removeModifiers('item', slotId);

            if (_isSlotNeeded(slotId)) {
                _statistics.addModifiers('item', slotId, g_items[itemId].jsonequip);
            }
        }
    }

    function _updateAllIcons() {
        for (var i = 0, len = _data.length; i < len; ++i) {
            _updateIcon(i);
        }
    }

    function _updateIcon(slotId) {
        var
            slotId = _getValidSlot(slotId),
            item   = _getSlotItem(slotId),
            itemId = item[0],
            icon   = _data[slotId].icon,
            a      = Icon.getLink(icon);

        icon.className = icon.className.replace(/ iconmedium-q[0-9]+/, '');

        if (_isSlotEmpty(slotId)) {
            Icon.setTexture(icon, 1, 'inventoryslot_' + _data[slotId].name);

            a.onclick     = $WH.rf;
            a.onmouseover = _showEmptyItemTooltip.bind(a, slotId);
            a.onmouseout  = $WH.Tooltip.hide;
            a._fixTooltip = null;
            a.href        = 'javascript:;';
            a.className   = '';
            a.rel         = '';
        }
        else {
            Icon.setTexture(icon, 1, g_items[itemId].icon.toLowerCase());

            a.onclick     = ($WH.Browser.opera || $WH.OS.mac ? function (e) {
                e = $WH.$E(e);
                if (e.shiftKey || e.ctrlKey) {
                    return false;
                }
            } : null);
            a.onmouseover = null;
            a.onmouseout  = null;
            a._fixTooltip = _fixItemTooltip.bind(a, slotId);
            a.href        = '?item=' + itemId;
            a.rel         = _getItemRel(slotId);

            icon.className += ' iconmedium-' + _getItemHilite(slotId);
        }

        icon.style.display = _isSlotNeeded(slotId) ? '' : 'none';
    }

    function _updateWeightedSort(lv) {
        if (!_spSortWeighted) {
            var sm = $WH.ce('small');
            sm.style.display = 'none';

            var sp = $WH.ce('span');
            sp.style.padding = '0 8px';
            sp.style.color = 'white';
            $WH.ae(sp, $WH.ct('|'));
            $WH.ae(sm, sp);

            $WH.ae(sm, $WH.ct(LANG.pr_note_sort + ' '));

            var s = $WH.ce('select');
            $WH.ae(sm, s);

            _spSortWeighted = sm;
        }

        var
            s = $('select', _spSortWeighted)[0],
            o = $WH.ce('option');

        $WH.ee(s);
        $WH.ae(s, o);

        var buildData = _talents.getTalentBuilds();

        if (buildData.scale) {
            o = $WH.ce('option');
            o.scale = buildData.scale;
            o.selected = (o.scale == _currentScale);
            $WH.st(o, buildData.name);
            $WH.ae(s, o);
        }

        if (g_user.weightscales && g_user.weightscales.length) {
            for (var i = 0, len = g_user.weightscales.length; i < len; ++i) {
                o = $WH.ce('option');
                o.scale = g_user.weightscales[i];
                o.selected = (o.scale == _currentScale);
                $WH.st(o, g_user.weightscales[i].name);
                $WH.ae(s, o);
            }
        }

        _spSortWeighted.style.display = s.childNodes.length > 1 ? '' : 'none';

        if (lv) {
            $WH.ae($WH.ce('div'), _spSortWeighted); // Remove from current parent
            $('span', _spSortWeighted)[0].style.display = ($('small', lv.getNoteTopDiv()).length ? '' : 'none');

            $WH.ae(lv.getNoteTopDiv(), _spSortWeighted);
            s.onchange = _sortPickerWindow.bind(this, lv, 1);
            s.onchange();
        }
    }

    function _showEmptyItemTooltip(slotId) {
        $WH.Tooltip.show(this, '<b>' + g_item_slots[_data[slotId].itemslots[0]] + '</b>', 0, 0, 'q');
    }

    function _showEmptyGemTooltip(color) {
        $WH.Tooltip.show(this, '<b>' + g_socket_names[color] + '</b>', 0, 0, 'q');
    }

    function _fixItemTooltip(slotId, html) {
        var item = _getSlotItem(slotId),
        pcs = [];

        if (!g_items[item[0]]) {
            return;
        }

        // Enchant requirements
        var e = g_enchants[item[2]];
        if (e && e.jsonequip.reqlevel > g_items[item[0]].jsonequip.level) {
            html = html.replace(new RegExp('<span class="q2"><!--ee-->(.+?)<\/span>', 'g'), '<span class="q10">$1<br \/>' + $WH.sprintf(LANG.pr_tt_enchreq, e.jsonequip.reqlevel) + '<\/span>');
        }

        // Gem requirements
        var s = html.match(new RegExp('<(a|span)( href=.+?)? class="[^"]*socket.+?</(a|span)>', 'g'));
        if (s != null) {
            for (var j = 0; j < 4; ++j) {
                if (s[j] && item[j + 4] && g_gems[item[j + 4]] && g_gems[item[j + 4]].socketlevel > g_items[item[0]].jsonequip.level) {
                    html = html.replace(s[j], s[j].replace(new RegExp('q[0-9]+', 'g'), 'q10'));
                }
            }
        }

        // Item Set Pieces
        if (g_items[item[0]].jsonequip.itemset) {
            for (var i = 0, len = _data.length; i < len; ++i) {
                var p = _getSlotItem(i)[0];
                if (g_items[p] && g_items[p].jsonequip.itemset == g_items[item[0]].jsonequip.itemset) {
                    pcs.push(p);
                }
            }
        }

        var
            row = _getItemErrors(slotId),
        errors = row.errors;

        if (errors['level'] || errors['ench'] || errors['gems'] || errors['sock']) {
            html += '<br /><span class="q10">';
        }

        if (errors['level']) {
            html += $WH.sprintf(LANG.pr_inv_item, LANG.pr_inv_lowlevel.replace(': ', ''));
        }

        if (errors['ench'] || errors['gems'] || errors['sock']) {
            if (errors['level']) {
                html += LANG.dash;
            }

            html += LANG.pr_inv_missing;

            if (errors['gems']) {
                html += $WH.sprintf(errors['gems'] == 1 ? LANG.pr_inv_gem : LANG.pr_inv_gems, errors['gems']);
            }

            if (errors['sock'] && errors['gems']) {
                html += LANG.comma;
            }

            if (errors['sock']) {
                html += $WH.sprintf(errors['sock'] == 1 ? LANG.pr_inv_socket : LANG.pr_inv_sockets, errors['sock']);
            }

            if (errors['ench'] && (errors['gems'] || errors['sock'])) {
                html += LANG.comma;
            }

            if (errors['ench']) {
                html += $WH.sprintf(errors['ench'] == 1 ? LANG.pr_inv_enchant : LANG.pr_inv_enchants, errors['ench']);
            }
        }

        if (errors['level'] || errors['ench'] || errors['gems'] || errors['sock']) {
            html += '</span>';
        }

        return html;
    }

    function _getItemRel(slotId) {
        var item = _getSlotItem(slotId),
        extraSocket = _hasExtraSocket(slotId),
        rel = [],
        gems = [],
        pcs = [];

        if (!g_items[item[0]]) {
            return;
        }

        if (item[1]) {
            rel.push('rand=' + item[1]);
        }

        if (item[2]) {
            rel.push('ench=' + item[2]);
        }

        for (var i = 0, len = (g_items[item[0]].jsonequip.nsockets | 0) + extraSocket; i < len; ++i) {
            gems.push(item[4 + i] > 0 ? item[4 + i] : 0);
        }

        if (gems.length) {
            rel.push('gems=' + gems.join(':'));
        }

        if (extraSocket) {
            rel.push('sock');
        }

        if (g_items[item[0]].jsonequip.itemset) {
            for (var i = 0, len = _data.length; i < len; ++i) {
                var p = _getSlotItem(i)[0];
                if (g_items[p] && g_items[p].jsonequip.itemset) {
                    pcs.push(p);
                }
            }
            rel.push('pcs=' + pcs.join(':'));
        }

        if (_profile.level) {
            rel.push('lvl=' + _profile.level);
        }

        if (_profile.classs) {
            rel.push('c=' + _profile.classs);
        }

        var result = rel.join('&');

        if (result) {
            result = '&' + result;
        }

        return result;
    }

    function _getItemHilite(slotId) {
        var itemId = _getSlotItem(slotId)[0];

        if (!g_items[itemId]) {
            return;
        }

        var item = g_items[itemId].jsonequip;

        if ((item.reqlevel > _profile.level) ||
            (item.reqclass && !(item.reqclass & 1 << _profile.classs - 1)) ||
            (item.subclass > 0 && item.subclass != 14 && item.subclass != 20 && _proficiencies[_profile.classs][item.classs] && !$WH.array_index(_proficiencies[_profile.classs][item.classs], item.subclass)))
        {
            return 'q10'; // Invalid item
        }

        return 'q' + g_items[itemId].quality;
    }

    function _getItemErrors(slotId, metaCondition) {
        var item = _getSlotItem(slotId);

        if (!item[0] || !g_items[item[0]] || !_isSlotNeeded(slotId)) {
            return;
        }

        var
            targetItemLvl = 0,
            row           = {},
            inventory     = _getInventory(),
            jsonequip     = g_items[item[0]].jsonequip,
            extraSocket   = _hasExtraSocket(slotId),
            enchantId     = item[2];

        if (_profile.level >= 70) {
            targetItemLvl = ((_profile.level - 70) * 9.5) + 105;
        }
        else if (_profile.level >= 60) {
            targetItemLvl = ((_profile.level - 60) * 4.5) + 60;
        }
        else {
            targetItemLvl = _profile.level;
        }

        for (var k in jsonEquipCopy) {
            row[k] = jsonequip[k];
        }

        if (item[1] && g_items[item[0]].jsonequip.subitems != null && g_items[item[0]].jsonequip.subitems[item[1]] != null) {
            row.name += ' ' + g_items[item[0]].jsonequip.subitems[item[1]].name;
        }

        row.profileslot = slotId;
        row.rel = _getItemRel(slotId);
        row.score = _getGearScore(slotId);
        row.use2h = (jsonequip.slot == 17 && !inventory[17][0]);
        row.gems = [];
        row.matchSockets = {};
        row.errors = {};

        if (jsonequip.level < targetItemLvl && (slotId != 5 && slotId != 6 && slotId != 20) && (!jsonequip.scadist || !jsonequip.scaflags)) {
            row.errors['level'] = 1;
        }

        // Gems
        for (var j = 0, len2 = (jsonequip.nsockets | 0) + extraSocket; j < len2; ++j) {
            var
                gemId       = (item[j + 4] > 0 ? item[j + 4] : 0),
                socketColor = (extraSocket && j == len2 - 1 ? 14 : jsonequip['socket' + (j + 1)]);

            row.matchSockets[j] = (gemId && g_gems[gemId] && _matchGemSocket(g_gems[gemId].colors, socketColor));
            row.gems.push(gemId);

            for (var k in metaCondition) {
                if (k == 't' || !(metaCondition[k] > 0)) {
                    continue;
                }

                if (gemId && g_gems[gemId] && _matchGemSocket(g_gems[gemId].colors, k)) {
                    metaCondition[k]--;
                    metaCondition.t--;
                }
            }

            if (gemId <= 0) {
                row.errors['gems'] = (row.errors['gems'] | 0) + 1;
            }
        }

        // Sockets
        if (_canBeSocketed(slotId) && !extraSocket && (_data[slotId].socket != 2 || _parent.isArmoryProfile())) {// Always show warnings for armory profiles
            row.errors['sock'] = 1;
        }

        // Enchant
        if (enchantId || _canBeEnchanted(slotId)) {
            row.enchant = 0;

            if (enchantId && g_enchants[enchantId]) {
                var
                    enchant  = g_enchants[enchantId],
                    slotMask = 1 << (jsonequip.slot - 1);

                if (typeof enchant.name == 'string') {
                    if (enchant.slots & slotMask) {
                        row.enchant = enchant.source;
                        row.enchantid = enchantId;
                    }
                }
                else {
                    for (var j = 0, len2 = enchant.slots.length; j < len2; ++j) {
                        if (enchant.slots[j] & slotMask) {
                            row.enchant = enchant.source[j];
                            row.enchantid = enchantId;
                            break;
                        }
                    }
                }

                if (row.enchant) {
                    var
                        ref   = (row.enchant < 0 ? g_items : g_spells),
                        refId = Math.abs(row.enchant);

                    if (ref[refId] == null) {
                        ref[refId] = { icon: enchant.icon };
                    }
                }
            }

            if (!row.enchant) {
                if (_data[slotId].enchant != 2 || _parent.isArmoryProfile()) { // Always show warnings for armory profiles
                    row.errors['ench'] = 1;
                }
                else {
                    delete row.enchant;
                }
            }
        }

        return row;
    }

    function _initListview(tabIndex) {
        var
            _,
            __,
            a;

        _ = $WH.ce('div');
        _.className = 'text';

        __ = $WH.ce('h2');
        __.id = 'gear-summary';

        _lnkPrint = $WH.ce('a');
        _lnkPrint.className = 'profiler-tablinks-print';
        _lnkPrint.href = 'javascript:;';
        _lnkPrint.onclick = _printSummary.bind(0);
        $WH.ae(_lnkPrint, $WH.ct(LANG.pr_header_print));
        $WH.ae(__, _lnkPrint);

        a = $WH.ce('a');
        a.className = 'header-link';
        a.href = '#gear-summary';
        $WH.ae(a, $WH.ct(LANG.pr_inv_title));
        $WH.ae(__, a);
        $WH.ae(_, __);
        $WH.ae(_container, _);

        _ = $WH.ce('div');
        _.className = 'listview';
        $WH.ae(_container, _);

        var extraCols = [
            {
                id: 'score',
                name: LANG.score,
                compute: function (item, td) {
                    if (!item.score) {
                        return;
                    }

                    var
                        sum = item.score.item + item.score.ench + item.score.gems,
                        sp = $WH.ce('span');

                    td.className = 'q' + pr_getGearScoreQuality(_profile.level, sum - (item.score.bonus | 0), null, _data[item.profileslot].id, item.use2h);

                    if (sum > 0) {
                        sp.className   = 'tip';
                        sp.onmouseover = _parent.showGearScoreTooltip.bind(0, item.score);
                        sp.onmousemove = $WH.Tooltip.cursorUpdate;
                        sp.onmouseout  = $WH.Tooltip.hide;
                    }

                    $WH.ae(sp, $WH.ct($WH.number_format(sum)));
                    $WH.ae(td, sp);
                },
                getVisibleText: function (item) {
                    if (!item.score) {
                        return;
                    }

                    return item.score.item + item.score.ench + item.score.gems;
                },
                sortFunc: function (a, b, col) {
                    var
                        ascore = (a.score ? a.score.item + a.score.ench + a.score.gems : 0),
                        bscore = (b.score ? b.score.item + b.score.ench + b.score.gems : 0);

                    return $WH.strcmp(ascore, bscore);
                }
            },
            {
                id: 'gems',
                name: LANG.gems,
                compute: function (item, td) {
                    if (item.errors['gems'] || item.errors['sock']) {
                        td.style.backgroundColor = '#8C0C0C';
                    }

                    if (!item.gems.length) {
                        return;
                    }

                    td.style.padding = '0';

                    var
                        sockets = [],
                        i;

                    for (i = 0; i < item.nsockets; ++i) {
                        sockets.push(item['socket' + (i + 1)]);
                    }

                    if (i < item.gems.length) {
                        sockets.push(14);
                    }

                    Listview.funcBox.createSocketedIcons(sockets, td, item.gems, item.matchSockets);

                    var d = td.firstChild,
                    buildData = _talents.getTalentBuilds();

                    for (var i = 0, len = sockets.length; i < len; ++i) {
                        var a = Icon.getLink(d.childNodes[i]);

                        a.oncontextmenu = $WH.rf;
                        a.onclick = ($WH.Browser.opera || $WH.OS.mac ? function (e) {
                            e = $WH.$E(e);
                            if (e.shiftKey || e.ctrlKey) {
                                return false;
                            }
                        } : null);
                        a.onmouseup       = _onMouseUpSlot.bind(0, item.profileslot, a);

                        if (!(item.gems[i] > 0)) {
                            a.onclick     = $WH.rf;
                            a.onmouseover = _showEmptyGemTooltip.bind(a, sockets[i]);
                            a.onmouseout  = $WH.Tooltip.hide;
                        }

                        a.menu = [
                            [, g_socket_names[sockets[i]]],
                            [0, (item.gems[i] > 0 ? LANG.pr_menu_replace : LANG.pr_menu_add), _openGemPicker.bind(this, sockets[i], i + 4, item.profileslot)]
                        ];

                        if (item.gems[i] > 0) {
                            a.menu.push([0, LANG.pr_menu_remove, _unsocketItem.bind(this, 4 + i, item.profileslot, false)]);
                        }

                        a.menu.push([, LANG.pr_menu_links]);

                        var
                            url = '?items=3&filter=maxrl=' + _profile.level + ';ub=' + _profile.classs + (sockets[i] == 1 ? ';cr=81;crs=1;crv=0' : (sockets[i] == 32 ? ';cr=81;crs=6;crv=0': ';cr=81:81:81;crs=2:3:4;crv=0:0:0;ma=1')) + '$1' + (item.gems[i] ? ';gb=1;upg=' + item.gems[i] : ''),
                            _ = [0, LANG.pr_menu_upgrades, null, []];

                        _[3].push([0, buildData.name, $WH.sprintf(url, buildData.filter), null, { newWindow: true }]);

                        if (g_user.weightscales && g_user.weightscales.length) {
                            for (var j = 0, len2 = g_user.weightscales.length; j < len2; ++j) {
                                var scale = g_user.weightscales[j];
                                _[3].push([0, scale.name, $WH.sprintf(url, pr_getScaleFilter(scale)), null, { newWindow: true }]);
                            }
                        }

                        a.menu.push(_);
                    }
                },
                getVisibleText: function (item) {
                    if (!item.gems.length) {
                        return;
                    }

                    var buff = '';

                    for (var i = 0, len = item.gems.length; i < len; ++i) {
                        var gemId = item.gems[i];

                        if (gemId > 0) {
                            if (i > 0) {
                                buff += ' ';
                            }

                            buff += g_gems[item.gems[i]].name;
                        }
                    }

                    return buff;
                },
                getValue: function (item) {
                    return item.gems.length;
                },
                sortFunc: function (a, b, col) {
                    var
                        suma = 0,
                        sumb = 0;

                    $.each(a.gems, function (k) {
                        suma += a['socket' + (k + 1)];
                    });
                    $.each(b.gems, function (k) {
                        sumb += b['socket' + (k + 1)];
                    });

                    return $WH.strcmp(a.gems.length, b.gems.length) || -$WH.strcmp(suma, sumb);
                }
            },
            {
                id: 'enchant',
                name: LANG.enchant,
                compute: function (item, td) {
                    if (item.errors['ench']) {
                        td.style.backgroundColor = '#8C0C0C';
                    }

                    if (item.enchant == null) {
                        return;
                    }

                    td.style.padding = '0';

                    Listview.funcBox.createCenteredIcons([Math.abs(item.enchant)], td, null, (item.enchant > 0 ? 1 : null));

                    var a = Icon.getLink(td.firstChild.childNodes[0]),
                    buildData = _talents.getTalentBuilds();

                    a.oncontextmenu = $WH.rf;
                    a.onclick = ($WH.Browser.opera || $WH.OS.mac ? function (e) {
                        e = $WH.$E(e);
                        if (e.shiftKey || e.ctrlKey) {
                            return false ;
                        }
                    } : null);
                    a.onmouseup = _onMouseUpSlot.bind(0, item.profileslot, a);
                    $WH.ns(a);

                    if (!item.enchant) {
                        a.onclick     = $WH.rf;
                        a.onmouseover = _showEmptyItemTooltip.bind(a, item.profileslot);
                        a.onmouseout  = $WH.Tooltip.hide;
                    }

                    a.menu = [
                        [, g_item_slots[item.slot]],
                        [0, (item.enchant ? LANG.pr_menu_replace : LANG.pr_menu_add), _openEnchantPicker.bind(this, item.profileslot)]
                    ];

                    if (item.enchant) {
                        a.menu.push([0, LANG.pr_menu_remove, _unenchantItem.bind(this, item.profileslot)]);
                    }

                    a.menu.push([, LANG.pr_menu_links]);

                    var url = '?items=0.6&filter=sl=' + item.slot + ';maxrl=' + _profile.level + ';ub=' + _profile.classs + '$1' + (item.enchant < 0 ? ';gb=1;upg=' + Math.abs(item.enchant) : ''),
                    _ = [0, LANG.pr_menu_upgrades, null, []];

                    _[3].push([0, buildData.name, $WH.sprintf(url, buildData.filter), null, { newWindow: true }]);

                    if (g_user.weightscales && g_user.weightscales.length) {
                        for (var i = 0, len = g_user.weightscales.length; i < len; ++i) {
                            var scale = g_user.weightscales[i];
                            _[3].push([0, scale.name, $WH.sprintf(url, pr_getScaleFilter(scale)), null, { newWindow: true }]);
                        }
                    }

                    a.menu.push(_);
                },
                getVisibleText: function (item) {
                    if (item.enchant == null) {
                        return;
                    }

                    return g_enchants[item.enchantid].name;
                },
                getValue: function (item) {
                    return (item.enchant == null ? 0 : 1);
                },
                sortFunc: function (a, b, col) {
                    if (g_enchants[a.enchantid] && g_enchants[b.enchantid]) {
                        return $WH.strcmp(g_enchants[a.enchantid].gearscore, g_enchants[b.enchantid].gearscore);
                    }
                    return $WH.strcmp(a.enchant, b.enchant);
                }
            }
        ];

        _listview = new Listview({
            template: 'inventory',
            id:       'inventory',
            parent:    _,
            extraCols: extraCols
        });

        if (tabIndex != null) {
            _listview.tabs     = _tabs;
            _listview.tabIndex = tabIndex;
        }

        _ = $WH.ce('div');
        _.className = 'pad';
        $WH.ae(_container, _);

        _divTipRclk = _ = $WH.ce('div');
        _.className = 'text';
        _.style.display = 'none';
        $WH.ae(_, $WH.ct(_shiftClick ? LANG.pr_tip_sclkopt2 : LANG.pr_tip_rclkopt2));
        $WH.ae(_container, _);
    }

    function _updateListview() {
        if (!_inited) {
            return;
        }

        var
            dataz           = [],
            metaCondition   = _getMetaCondition(),
            targetItemLvl   = 0,
            lowLevelItems   = 0,
            missingItems    = 0,
            missingGems     = 0,
            missingEnchants = 0,
            missingSockets  = 0;

        _parent.updateGearScore();

        // Data
        for (var i = 0, len = _data.length; i < len; ++i) {
            var
                slotId = i,
                item   = _getSlotItem(slotId);

            if (_isSlotNeeded(slotId, 1) && !item[0] && (slotId != 5 && slotId != 6 && slotId != 20) && (
                (_profile.level > 55) ||
                (_profile.level > 25 && (slotId != 14 || slotId != 15 || slotId != 19)) || // Trinkets or Relics
                (_profile.level > 20 && (slotId != 0 || slotId != 1)) || // Head or Neck
                (slotId != 2 || slotId != 12 || slotId != 13))) // Shoulder or Trinkets
            {
                missingItems++;
            }

            if (!item[0] || !g_items[item[0]] || !_isSlotNeeded(slotId)) {
                continue;
            }

            var row = _getItemErrors(slotId, metaCondition);

            if (row.errors['level']) {
                lowLevelItems++;
            }

            if (row.errors['gems']) {
                missingGems += row.errors['gems'];
            }

            if (row.errors['sock']) {
                missingSockets++;
            }

            if (row.errors['ench']) {
                missingEnchants++;
            }

            dataz.push(row);
        }

        // Note
        var
            note = _listview.getNoteTopDiv().firstChild,
            buff = '';

        if (lowLevelItems) {
            buff += LANG.pr_inv_lowlevel + $WH.sprintf(lowLevelItems == 1 ? LANG.pr_inv_item : LANG.pr_inv_items, lowLevelItems);

            if (missingItems || missingEnchants || missingGems || missingSockets || metaCondition.t > 0) {
                buff += LANG.dash;
            }
        }

        if (missingItems || missingEnchants || missingGems || missingSockets) {
            buff += LANG.pr_inv_missing;

            if (missingItems) {
                buff += $WH.sprintf(missingItems == 1 ? LANG.pr_inv_item : LANG.pr_inv_items, missingItems);
            }

            if (missingGems && missingItems) {
                buff += LANG.comma;
            }

            if (missingGems) {
                buff += $WH.sprintf(missingGems == 1 ? LANG.pr_inv_gem : LANG.pr_inv_gems, missingGems);
            }

            if (missingSockets && (missingItems || missingGems)) {
                buff += LANG.comma;
            }

            if (missingSockets) {
                buff += $WH.sprintf(missingSockets == 1 ? LANG.pr_inv_socket : LANG.pr_inv_sockets, missingSockets);
            }

            if (missingEnchants && (missingItems || missingGems || missingSockets)) {
                buff += LANG.comma;
            }

            if (missingEnchants) {
                buff += $WH.sprintf(missingEnchants == 1 ? LANG.pr_inv_enchant : LANG.pr_inv_enchants, missingEnchants);
            }

            if (metaCondition.t > 0) {
                buff += LANG.dash;
            }
        }

        if (metaCondition.t > 0) {
            buff += LANG.pr_inv_metareq;

            var i = 0;
            for (var k in metaCondition) {
                if (k == 't' || !(metaCondition[k] > 0)) {
                    continue;
                }

                if (i++>0) {
                    buff += LANG.comma;
                }

                buff += $WH.sprintf(metaCondition[k] == 1 ? LANG.pr_inv_gem : LANG.pr_inv_gems, metaCondition[k] + ' ' + g_gem_colors[k]);
            }
        }

        $WH.st(note, (buff.length ? buff : String.fromCharCode(160)));

        _listview.setData(dataz);
        _listview.updateFilters(true);

        _divTipRclk.style.display = (dataz.length ? '' : 'none');
    }

    function _printSummary() {
        var
            bg   = window.open('?profile=summary', '', 'toolbar=no,menubar=yes,status=yes,scrollbars=yes,resizable=yes'),
            buff = '<html><head><title>' + document.title + '</title></head><body style="font-family: Arial, sans-serif; font-size: 13px">';

        bg.document.open();

        buff += '<h2>' + _profile.name + ' ' + LANG.pr_inv_title + '</h2>';

        for (var i = 0, len = _slots.length; i < len; ++i) {
            var
                slotId = i,
                item   = _getSlotItem(slotId);

            if (!item[0] || !g_items[item[0]] || !_isSlotNeeded(slotId)) {
                continue;
            }

            var
                row         = {},
                jsonequip   = g_items[item[0]].jsonequip,
                subitems    = g_items[item[0]].jsonequip.subitems,
                extraSocket = _hasExtraSocket(slotId);

            buff += g_item_slots[_slots[slotId].itemslots[0]] + ': <b>' + jsonequip.name.substring(1);

            if (item[1] && subitems != null && subitems[item[1]] != null) {
                buff += ' ' + subitems[item[1]].name;
            }

            buff += '</b>';
            buff += '<ul>';

            // Source
            if (jsonequip.source) {
                buff += '<li><b>' + LANG.source + '</b> - ';

                for (var j = 0, len2 = jsonequip.source.length; j < len2; ++j) {
                    var
                        sm = (jsonequip.sourcemore && jsonequip.sourcemore[j] ? jsonequip.sourcemore[j] : {}),
                        ls = Listview.funcBox.getLowerSource(jsonequip.source[j], sm, (sm.t | 0));

                    if (j > 0) {
                        buff += ', ';
                    }

                    if (sm.t) {
                        buff += sm.n;
                    }
                    else {
                        buff += Listview.funcBox.getUpperSource(jsonequip.source[j], sm);
                    }

                    buff += ' (' + g_sources[jsonequip.source[j]];

                    if (ls != null) {
                        buff += '; ' + ls.text;

                        if (sm.dd) {
                            if (sm.dd < 0) { // Dungeon
                                buff += ' ' + (sm.dd < -1 ? LANG.pr_print_heroic : LANG.pr_print_normal);
                            }
                            else { // 10 or 25 Player Raid
                                buff += ' ' + (sm.dd & 1 ? LANG.lvitem_raid10 : LANG.lvitem_raid25) + ' ' + (sm.dd > 2 ? LANG.pr_print_heroic : LANG.pr_print_normal);
                            }
                        }
                    }

                    buff += ')';
                }

                buff += '</li>';
            }

            // Enchant
            if (item[2] || _canBeEnchanted(slotId)) {
                buff += '<li><b>' + LANG.enchant + '</b> - ';

                if (item[2] && g_enchants[item[2]]) {
                    var
                        enchant  = g_enchants[item[2]],
                        slotMask = 1 << (jsonequip.slot - 1);

                    if (typeof enchant.name == 'string') {
                        if (enchant.slots & slotMask) {
                            buff += enchant.name;
                        }
                    }
                    else {
                        for (var j = 0, len2 = enchant.slots.length; j < len2; ++j) {
                            if (enchant.slots[j] & slotMask) {
                                buff += enchant.name[j];
                                break;
                            }
                        }
                    }
                }
                else {
                    buff += '<i>' + LANG.pr_print_none + '</i>';
                }

                buff += '</li>';
            }

            // Extra Socket
            if (_canBeSocketed(slotId)) {
                buff += '<li><b>' + LANG.pr_menu_extrasock + '</b> - ' + (extraSocket ? LANG.pr_print_yes : '<i>' + LANG.pr_print_no + '</i>') + '</li>';
            }

            // Gems
            if (jsonequip.nsockets || extraSocket) {
                buff += '<li><b>' + LANG.gems + '</b> - ';

                for (var j = 0, len2 = (jsonequip.nsockets | 0) + extraSocket; j < len2; ++j) {
                    if (j > 0) {
                        buff += ', ';
                    }

                    if (item[j + 4] && g_gems[item[j + 4]]) {
                        buff += g_gems[item[j + 4]].name;
                    }
                    else {
                        buff += '<i>' + LANG.pr_print_none + '</i>';
                    }

                    buff += ' (' + g_socket_names[(extraSocket && j == len2 - 1 ? 14 : jsonequip['socket' + (j + 1)])] + ')';
                }

                buff += '</li>';
            }

            buff += '</ul>';
        }

        buff += '</body></html>';

        bg.document.write(buff);
        bg.document.close()
    }

    function _prepareSearch(search, delay) {
        if (isNaN(delay)) {
            delay = 1000;
        }

        if (_timer > 0) {
            clearTimeout(_timer);
            _timer = 0;
        }

        if (search) {
            $WH.st(_searchMsg, $WH.sprintf(LANG['pr_searching'], search));
        }
        _searchMsg.className = '';

        _timer = setTimeout(_searchItems.bind(0, search, _currentSlot), delay);
    }

    function _searchItems(search, slotId) {
        var
            lv = g_listviews.items;

        _searchResults = [{ none: 1, __alwaysvisible: 1 }];

        lv.searchable = false;
        lv.setData(_searchResults);
        lv.clearSearch();
        lv.updateFilters(true);
        _searchMsg.className = '';

        if (!search && !_currentScale) {
            $WH.st(_searchMsg, LANG['pr_specifyitem']);
            return;
        }
        $WH.st(_searchMsg, $WH.sprintf(LANG['pr_searching'], search));

        new Ajax('?search=' + $WH.urlencode(search) + '&json&slots=' + _data[slotId].itemslots.join(':') + pr_getScaleFilter(_currentScale, 1), {
            method: 'POST',
            search: search,
            onSuccess: function (xhr, opt) {
                var text = xhr.responseText;
                if (text.charAt(0) != '[' || text.charAt(text.length - 1) != ']') {
                    $WH.st(_searchMsg, $WH.sprintf(LANG.su_noresults, opt.search));
                    _searchMsg.className = 'q10';
                    return;
                }

                var a = eval(text);
                if (search == opt.search && a.length == 3 && a[1].length) {
                    for (var i = 0, len = a[1].length; i < len; ++i) {
                        var row       = {};
                        row.id        = a[1][i].id;
                        row.name      = row['name_' + Locale.getName()] = a[1][i].name.substring(1);
                        row.quality   = 7 - a[1][i].name.charAt(0);
                        row.icon      = a[1][i].icon;
                        row.level     = a[1][i].level;
                        row.classs    = a[1][i].classs;
                        row.subclass  = a[1][i].subclass;
                        row.gearscore = a[1][i].gearscore;
                        row.jsonequip = $WH.dO(a[1][i]);

                        g_items.add(a[1][i].id, row);
                        _searchResults.push(row);
                    }
                    $WH.ee(_searchMsg);
                }
                else {
                    $WH.st(_searchMsg, $WH.sprintf(LANG.pr_noresults, opt.search));
                    _searchMsg.className = 'q10';
                }

                lv.setData(_searchResults);
                lv.clearSearch();
                lv.updateFilters(true);

                _sortPickerWindow(lv);
            }
        });
    }

    function _isValidItem(item) {
        if (item.none) {
            return true;
        }

        var itemTypes = _proficiencies[_profile.classs][item.classs];

        return (
            // Item Class
            (
                _itemType == null || !itemTypes || item.subclass < 1 || item.subclass == 14 ||
                ((_itemType == 1 || item.classs == 2 || item.subclass > 4) && $WH.array_index(itemTypes, item.subclass)) ||
                (_itemType == 2 && item.subclass == itemTypes[itemTypes.length - 1])
            )
        );
    }

    function _filterItems(wut, value) {
        switch (wut) {
        case 0:
            _itemType = value;
            break;
        default:
            return;
        }

        if (this && this.nodeName == 'A') {
            g_setSelectedLink(this, 'items' + wut);
        }

        _lvItems.updateFilters(true);

        return false;
    }

    function _createItemsNote(div) {
        var
            sm = $WH.ce('small'),
            sp = $WH.ce('span'),
            a;

        $WH.ae(sm, $WH.ct(LANG.pr_note_type));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = _filterItems.bind(a, 0, null);
        $WH.ae(a, $WH.ct(LANG.pr_note_all));

        $WH.ae(sm, a);
        $WH.ae(sm, $WH.ct(LANG.comma));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = _filterItems.bind(a, 0, 1);
        $WH.ae(a, $WH.ct(LANG.pr_note_usable));

        $WH.ae(sm, a);
        $WH.ae(sm, sp);
        $WH.ae(sp, $WH.ct(LANG.comma));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = _filterItems.bind(a, 0, 2);

        _itemType = 2;
        g_setSelectedLink(a, 'items0');

        $WH.ae(sp, a);

        sp = $WH.ce('span');
        sp.style.padding = '0 8px';
        sp.style.color = 'white';
        $WH.ae(sp, $WH.ct('|'));
        $WH.ae(sm, sp);

        $WH.ae(div, sm);

        sm = $WH.ce('small');
        $WH.ae(sm, $WH.ct(LANG.pr_note_name));

        _spSearchName = $WH.ce('input');
        _spSearchName.type = 'text';
        _spSearchName.value = _currentSearch;

        $WH.aE(_spSearchName, 'keyup', _onKeyUpSearch.bind(0, 333));
        $WH.aE(_spSearchName, 'keydown', _onKeyDownSearch);

        $WH.ae(sm, _spSearchName);

        _searchMsg = $WH.ce('span');
        _searchMsg.style.fontWeight = 'bold';
        $WH.ae(sm, _searchMsg);

        $WH.ae(div, sm);
    }

    function _isValidEnchant(enchant) {
        if (enchant.none) {
            return true;
        }

        var
            slotMask  = 1 << (g_items[_getSlotItem(_currentSlot)[0]].jsonequip.slot - 1),
            classMask = 1 << (_profile.classs - 1);

        return (
            // Class
            (enchant.classes <= 0 || enchant.classes & classMask) &&

            // Slot
            (enchant.slots & slotMask) &&

            // Source
            (
                _enchantSource == null ||
                (_enchantSource == 1 && enchant.source < 0) ||
                (_enchantSource == 2 && enchant.source > 0)
            ) &&

            // StatType
            (
                _enchantStat == null ||
                (
                    _enchantStat == -1 &&
                    !enchant.jsonequip.hasOwnProperty('sta') &&
                    !enchant.jsonequip.hasOwnProperty('str') &&
                    !enchant.jsonequip.hasOwnProperty('agi') &&
                    !enchant.jsonequip.hasOwnProperty('int') &&
                    !enchant.jsonequip.hasOwnProperty('spi')
                ) ||
                (
                    enchant.jsonequip.hasOwnProperty(_enchantStat)
                )
            )
        );
    }

    function _filterEnchants(wut, value) {
        switch (wut) {
        case 0:
            _enchantSource = value;
            break;
        case 1:
            _enchantStat = value;
            break;
        default:
            return;
        }

        if (this && this.nodeName == 'A') {
            g_setSelectedLink(this, 'enchants' + wut);
        }

        _lvEnchants.updateFilters(true);

        return false;
    }

    function _createEnchantsNote(div) {
        var
            sm = $WH.ce('small'),
            a;

        $WH.ae(sm, $WH.ct(LANG.pr_note_source));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = _filterEnchants.bind(a, 0, null);
        $WH.ae(a, $WH.ct(LANG.pr_note_all));

        g_setSelectedLink(a, 'enchants0');

        $WH.ae(sm, a);
        $WH.ae(sm, $WH.ct(LANG.comma));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = _filterEnchants.bind(a, 0, 1);
        $WH.ae(a, $WH.ct(LANG.pr_note_items));

        $WH.ae(sm, a);
        $WH.ae(sm, $WH.ct(LANG.comma));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = _filterEnchants.bind(a, 0, 2);
        $WH.ae(a, $WH.ct(LANG.pr_note_profs));

        $WH.ae(sm, a);
        $WH.ae(div, sm);

        sm = $WH.ce('small');
        var sp = $WH.ce('span');
        sp.style.padding = '0 8px';
        sp.style.color = 'white';
        $WH.ae(sp, $WH.ct('|'));
        $WH.ae(sm, sp);
        $WH.ae(sm, $WH.ct(LANG.su_note_stats + ': '));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = _filterEnchants.bind(a, 1, null);
        $WH.ae(a, $WH.ct(LANG.pr_note_all));

        g_setSelectedLink(a, 'enchants1');

        $WH.ae(sm, a);
        $WH.ae(sm, $WH.ct(LANG.comma))

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = _filterEnchants.bind(a, 1, 'sta');
        $WH.ae(a, $WH.ct(LANG.traits.sta[0]));
        $WH.ae(sm, a);
        $WH.ae(sm, $WH.ct(LANG.comma));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = _filterEnchants.bind(a, 1, 'str')
        $WH.ae(a, $WH.ct(LANG.traits.str[0]));
        $WH.ae(sm, a);
        $WH.ae(sm, $WH.ct(LANG.comma));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = _filterEnchants.bind(a, 1, 'agi');
        $WH.ae(a, $WH.ct(LANG.traits.agi[0]))
        $WH.ae(sm, a);
        $WH.ae(sm, $WH.ct(LANG.comma));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = _filterEnchants.bind(a, 1, 'int');
        $WH.ae(a, $WH.ct(LANG.traits['int'][0]));
        $WH.ae(sm, a);
        $WH.ae(sm, $WH.ct(LANG.comma));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = _filterEnchants.bind(a, 1, 'spi');
        $WH.ae(a, $WH.ct(LANG.traits['spi'][0]));
        $WH.ae(sm, a);
        $WH.ae(sm, $WH.ct(LANG.comma));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = _filterEnchants.bind(a, 1, -1);
        $WH.ae(a, $WH.ct(LANG.su_note_other));

        _enchantStat = null;
        $WH.ae(sm, a);
        $WH.ae(div, sm);
    }

    function _hasSocketBonus(slotId) {
        var
            item     = _getSlotItem(slotId),
            nMatches = 0;

        if (!g_items[item[0]] || !g_items[item[0]].jsonequip.nsockets) {
            return false;
        }

        for (var j = 0; j < 3; ++j) {
            if (item[j + 4] && g_gems[item[j + 4]]) {
                if (_matchGemSocket(g_gems[item[j + 4]].colors, g_items[item[0]].jsonequip['socket' + (j + 1)])) {
                    ++nMatches;
                }
            }
        }

        return (nMatches == g_items[item[0]].jsonequip.nsockets);
    }

    function _matchGemSocket(gemColor, socketColor) {
        for (var i = 1; i <= 32; i *= 2) {
            if ((socketColor & i) && (gemColor & i)) {
                return true;
            }
        }

        return false;
    }

    function _isValidGem(gem) {
        if (gem.none) {
            return true;
        }

        return (
            // Source
            (_gemSource == null || gem.expansion == _gemSource) &&

            // Color
            (
                ((_currentColor !=  1 && gem.colors !=  1) && // Always match meta gems and sockets
                 _gemColor == null) ||
                 _matchGemSocket(gem.colors, _currentColor)
             )
         );
    }

    function _filterGems(wut, value) {
        switch (wut) {
            case 0:
                _gemSource = value;
                break;
            case 1:
                _gemColor = value;
                break;
            default:
                return;
        }

        if (this && this.nodeName == 'A') {
            g_setSelectedLink(this, 'gems' + wut);
        }

        _lvGems.updateFilters(true);

        return false;
    }

    function _createGemsNote(div) {
        var
            sm = $WH.ce('small'),
            a;

        $WH.ae(sm, $WH.ct(LANG.pr_note_source));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = _filterGems.bind(a, 0, null);
        $WH.ae(a, $WH.ct(LANG.pr_note_all));

        $WH.ae(sm, a);
        $WH.ae(sm, $WH.ct(LANG.comma));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = _filterGems.bind(a, 0, 1);
        $WH.ae(a, $WH.ct(LANG.pr_note_bc));

        $WH.ae(sm, a);
        $WH.ae(sm, $WH.ct(LANG.comma));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = _filterGems.bind(a, 0, 2);
        $WH.ae(a, $WH.ct(LANG.pr_note_wotlk));

        _gemSource = 2;
        g_setSelectedLink(a, 'gems0');

        $WH.ae(sm, a);
        $WH.ae(div, sm);

        sm = $WH.ce('small');

        var sp = $WH.ce('span');
        sp.style.padding = '0 8px';
        sp.style.color = 'white';
        $WH.ae(sp, $WH.ct('|'));
        $WH.ae(sm, sp);

        $WH.ae(sm, $WH.ct(LANG.pr_note_color));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = _filterGems.bind(a, 1, null);
        $WH.ae(a, $WH.ct(LANG.pr_note_all));

        $WH.ae(sm, a);
        $WH.ae(sm, $WH.ct(LANG.comma));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = _filterGems.bind(a, 1, 1);
        $WH.ae(a, $WH.ct(LANG.pr_note_match));

        _gemColor = 1;
        g_setSelectedLink(a, 'gems1');

        $WH.ae(sm, a);
        $WH.ae(div, sm);
    }

    function _calcScores(data, scale) {
        for (var i = 0, len = data.length; i < len; ++i) {
            var row = data[i];

            row.__tr  = null;
            row.score = (scale ? 0 : null);

            if (row.jsonequip && scale) {
                var
                    f = 0,
                    json = $WH.dO(row.jsonequip);
                    _parent.unfixJson(json);

                for (var w in scale) {
                    if (!LANG.traits[w]) {
                        continue;
                    }

                    f += scale[w];
                    if (!isNaN(json[w])) {
                        row.score += json[w] * scale[w];
                    }
                }

                row.score = (row.score / f).toFixed(2);
            }
        }

        return data;
    }

    function _sortPickerWindow(lv, search) {
        if (!_spSortWeighted) {
            return;
        }

        if ($('option:selected', _spSortWeighted)[0]) {
            _currentScale = $('option:selected', _spSortWeighted)[0].scale;
        }

        lv.setSort([1], true, false);
        lv.setData(_calcScores(lv.data, _currentScale));

        if (_currentScale) {
            lv.setSort([2], true, false);
        }

        if (search && lv.id == 'items') {
            _onKeyUpSearch(0);
        }
    }

    function _openItemPicker(slotId) {
        _currentSlot = _getValidSlot(slotId);

        Lightbox.show('itempicker', { onShow: _onShowItemPicker });
    }

    function _openSubitemPicker(slotId) {
        _currentSlot = _getValidSlot(slotId);

        Lightbox.show('subitempicker', { onShow: _onShowSubitemPicker });
    }

    function _openEnchantPicker(slotId) {
        _currentSlot = _getValidSlot(slotId);

        Lightbox.show('enchantpicker', { onShow: _onShowEnchantPicker });
    }

    function _openGemPicker(socketColor, socketId, slotId) {
        _currentSlot   = _getValidSlot(slotId);
        _currentSocket = socketId;
        _currentColor  = socketColor;

        Lightbox.show('gempicker', { onShow: _onShowGemPicker });
    }

    function _onShowItemPicker(dest, first, opt) {
        Lightbox.setSize(800, 564);

        var lv;

        if (first) {
            dest.className = 'profiler-picker listview';

            var
                d     = $WH.ce('div'),
                a     = $WH.ce('a'),
                clear = $WH.ce('div');

            d.className = 'listview';
            $WH.ae(dest, d);

            a = $WH.ce('a');
            a.className = 'screenshotviewer-close';
            a.href = 'javascript:;';
            a.onclick = Lightbox.hide;
            $WH.ae(a, $WH.ce('span'));
            $WH.ae(dest, a);

            clear.className = 'clear';
            $WH.ae(dest, clear);

            _lvItems = lv = new Listview({
                template:    'itempicker',
                id:          'items',
                parent:      d,
                data:        [],
                clip:        { w: 780, h: 478 },
                createNote:   _createItemsNote,
                customFilter: _isValidItem
            });

            if ($WH.Browser.firefox) {
                $WH.aE(lv.getClipDiv(), 'DOMMouseScroll', g_pickerWheel);
            }
            else {
                lv.getClipDiv().onmousewheel = g_pickerWheel;
            }
        }
        else {
            lv = g_listviews.items;
        }

        var
            div = lv.getNoteTopDiv(),
            sms = $WH.gE(div, 'small');

        if (!$WH.array_index([0, 2, 4, 7, 8, 9, 10, 11, 16, 17, 18, 19], _currentSlot)) { // Not armor or weapons
            sms[0].style.display = 'none'; // Hide 'Type' filter
        }
        else {
            var
                ss = $WH.gE(div, 'span'),
                as = $WH.gE(div, 'a');

            sms[0].style.display = ''; // Show 'Type' filter

            if (_currentSlot > 15) { // Weapons
                ss[0].style.display = 'none';
                if (_itemType == 2) {
                    as[1].onclick();
                }
            }
            else { // Armor
                var itemTypes = _proficiencies[_profile.classs][4];

                ss[0].style.display = '';
                $WH.st(as[2], g_itemset_types[itemTypes[itemTypes.length - 1]]); // Set best armor type
            }
        }

        _updateWeightedSort(lv);

        setTimeout(function () {
            _spSearchName.focus();
        }, 1);
    }

    function _onShowSubitemPicker(dest, first, opt) {
        Lightbox.setSize(800, 564);

        var
            lv,
            itemId = _getSlotItem(_currentSlot)[0],
            dataz  = [];

        for (var subitemId in g_items[itemId].jsonequip.subitems) {
            var subitem = g_items[itemId].jsonequip.subitems[subitemId];

            subitem.id = subitemId;
            subitem.item = itemId;

            dataz.push(subitem);
        }

        if (first) {
            dest.className = 'profiler-picker listview';

            var
                d     = $WH.ce('div'),
                a     = $WH.ce('a'),
                clear = $WH.ce('div');

            d.className = 'listview';
            $WH.ae(dest, d);

            a = $WH.ce('a');
            a.className = 'screenshotviewer-close';
            a.href = 'javascript:;';
            a.onclick = Lightbox.hide;
            $WH.ae(a, $WH.ce('span'));
            $WH.ae(dest, a);

            clear.className = 'clear';
            $WH.ae(dest, clear);

            _lvSubitems = lv = new Listview({
                template: 'subitempicker',
                id:       'subitems',
                parent:   d,
                data:     dataz
            });

            if ($WH.Browser.firefox) {
                $WH.aE(lv.getClipDiv(), 'DOMMouseScroll', g_pickerWheel);
            }
            else {
                lv.getClipDiv().onmousewheel = g_pickerWheel;
            }
        }
        else {
            lv = g_listviews.subitems;

            lv.setData(dataz);
            lv.clearSearch();
            lv.updateFilters(true);
        }

        _updateWeightedSort(lv);

        setTimeout(function () {
            lv.focusSearch();
        }, 1);
    }

    function _onShowEnchantPicker(dest, first, opt) {
        Lightbox.setSize(800, 564);

        var
            lv,
            dataz = [];

        dataz.push({ none: 1, __alwaysvisible: 1 });

        for (var enchantId in g_enchants) {
            var enchant = $WH.dO(g_enchants[enchantId]);
            enchant.id  = enchantId;

            if (typeof enchant.name == 'string') {
                dataz.push(enchant);
            }
            else {
                for (var i = 0, len = g_enchants[enchantId].name.length; i < len; ++i) {
                    var row = $WH.dO(enchant);

                    row.name   = enchant.name[i];
                    row.source = enchant.source[i];
                    row.slots  = enchant.slots[i];

                    dataz.push(row);
                }
            }
        }

        if (first) {
            dest.className = 'profiler-picker listview';

            var
                d     = $WH.ce('div'),
                a     = $WH.ce('a'),
                clear = $WH.ce('div');

            d.className = 'listview';
            $WH.ae(dest, d);

            a = $WH.ce('a');
            a.className = 'screenshotviewer-close';
            a.href = 'javascript:;';
            a.onclick = Lightbox.hide;
            $WH.ae(a, $WH.ce('span'));
            $WH.ae(dest, a);

            clear.className = 'clear';
            $WH.ae(dest, clear);

            _lvEnchants = lv = new Listview({
                template:     'enchantpicker',
                id:           'enchants',
                parent:       d,
                data:         dataz,
                createNote:   _createEnchantsNote,
                customFilter: _isValidEnchant
            });

            if ($WH.Browser.firefox) {
                $WH.aE(lv.getClipDiv(), 'DOMMouseScroll', g_pickerWheel);
            }
            else {
                lv.getClipDiv().onmousewheel = g_pickerWheel;
            }
        }
        else {
            lv = g_listviews.enchants;

            lv.setData(dataz);
            lv.clearSearch();
            lv.updateFilters(true);
        }

        _updateWeightedSort(lv);

        setTimeout(function () {
            lv.focusSearch();
        }, 1);
    }

    function _onShowGemPicker(dest, first, opt) {
        Lightbox.setSize(800, 564);

        var lv;

        if (first) {
            dest.className = 'profiler-picker listview';

            var
                dataz = [],
                d     = $WH.ce('div'),
                a     = $WH.ce('a'),
                clear = $WH.ce('div');

            dataz.push({ none: 1, __alwaysvisible: 1 });

            for (var gemId in g_gems) {
                var gem = g_gems[gemId];

                gem.id = gemId;

                dataz.push(gem);
            }

            d.className = 'listview';
            $WH.ae(dest, d);

            a = $WH.ce('a');
            a.className = 'screenshotviewer-close';
            a.href = 'javascript:;';
            a.onclick = Lightbox.hide;
            $WH.ae(a, $WH.ce('span'));
            $WH.ae(dest, a);

            clear.className = 'clear';
            $WH.ae(dest, clear);

            _lvGems = lv = new Listview({
                template:     'gempicker',
                id:           'gems',
                parent:       d,
                data:         dataz,
                createNote:   _createGemsNote,
                customFilter: _isValidGem
            });

            if ($WH.Browser.firefox) {
                $WH.aE(lv.getClipDiv(), 'DOMMouseScroll', g_pickerWheel);
            }
            else {
                lv.getClipDiv().onmousewheel = g_pickerWheel;
            }
        }
        else {
            lv = g_listviews.gems;

            lv.clearSearch();
            lv.updateFilters(true);
        }

        var
            div = lv.getNoteTopDiv(),
            sms = $WH.gE(div, 'small'),
            as  = $WH.gE(div, 'a');

        if (_currentColor == 1 || _currentColor == 14) { // Meta, Prismatic
            sms[1].style.display = 'none'; // Hide 'Color' filter
        }
        else {
            sms[1].style.display = ''; // Show 'Color' filter
        }

        _updateWeightedSort(lv);

        setTimeout(function () {
            lv.focusSearch();
        }, 1);
    }
}

function ProfilerCompletion(_parent) {
    var
        _self = this,

        _parentVars,
        _profile,
        _tabs,

        _dialog,

        _tabIndex,

        _who,
        _when,
        _data,

        _total,
        _subtotal,

        _summary,
        _validation,

        _category,
        _subcategory,
        _complete,

        _rawdata,
        _loading,
        _timer,

        _loaded,
        _inited,
        _updated,
        _displayed,

        _container,
        _imgLoading,
        _divSource,
        _divSummary,
            _divPoints,
            _divTotals,
        _divTipQuests,
        _divTabs,
        _divSubcatgs,
        _divListview,
        _divExcluded,

        _tabsListview,

        _subcategories,
        _listview,
        _excluded,

        _mode,
        _opt = {},

        extraCols = [
            {
                id: 'completed',
                name: LANG.completed,
                type: 'text',
                width: '16%',
                value: 'completed',
                compute: function (row, td) {
                    var img = $WH.ce('img');
                    img.src = (row.completed ? g_staticUrl + '/images/icons/' + (!row._included() ? 'star' : 'tick') + '.png' : g_staticUrl + '/images/icons/delete.gif');
                    img.onmouseover = function (e) {
                        $WH.Tooltip.showAtCursor(e, LANG[(row.completed ? (row._included() ? 'pr_note_complete' : 'pr_tt_excldone') : 'pr_note_incomplete')], 0, 0, 'q');
                    };
                    img.onmousemove = $WH.Tooltip.cursorUpdate;
                    img.onmouseout = $WH.Tooltip.hide;
                    img.style.border = 'none';
                    $WH.ae(td, img);

                    if (row.completed && row.completed.getTime) {
                        $WH.ae(td, $WH.ct(' ' + g_formatDateSimple(row.completed)));
                    }
                    else if (_opt.showQuantity && row.completed) {
                        $WH.ae(td, $WH.ct(' ' + row.completed));
                    }
                },
                getVisibleText: function (row) {
                    var buff = LANG[(row.completed ? 'pr_note_complete' : 'pr_note_incomplete')];
                    if (row.completed && row.completed.getTime) {
                        buff += ' ' + g_formatDateSimple(row.completed);
                    }
                    return buff;
                },
                sortFunc: function (a, b, col) {
                    return (
                        $WH.strcmp(a.completed, b.completed) ||
                        $WH.strcmp(a._included(), b._included())
                    );
                }
            }
        ];

    this.initialize = function (container, tabIndex, opt) {
        _container = $WH.ge(container);
        if (!_container) {
            return;
        }

        _parentVars = _parent.getVars();
        _profile    = _parentVars.profile;
        _tabs       = _parentVars.tabs;
        _dialog     = _parentVars.dialog;

        _tabIndex = tabIndex;

        if (!_tabs.tabs[_tabIndex]) {
            return;
        }

        _mode = _tabs.tabs[_tabIndex].id;

        $WH.cO(_opt, opt);

        _imgLoading = $WH.ce('img');
        _imgLoading.src = g_staticUrl + '/images/ui/misc/progress-anim.gif';
        _imgLoading.style.clear = 'both';
        _imgLoading.style.display = (_opt.onDemand ? 'block' : 'none');
        _imgLoading.style.margin = '0 auto';
        $WH.ae(_container, _imgLoading);

        _divTipQuests = $WH.ce('div');

        if (!_opt.onDemand) {
            _loaded = true;
        }
        else if (typeof _opt.onDemand != 'string') {
            _opt.onDemand = _mode;
        }

        if (!_opt.reqcatg) {
            _opt.partial = false;
        }
    };

    this.updateExclusions = function () {
        _updateExclusions();
    };

    this.isComplete = function (id) {
        if (id) {
            for (var i = 0, len = _data.length; i < len; ++i) {
                var row = _data[i];
                if (row.id == id) {
                    return row.completed;
                }
            }
        }
        return false;
    };

    this.setData = function (data, who, when) {
        _setData(data, who, when);
    };

    this.filterData = function (wut, value) {
        if (!_timer) {
            _timer = {};
        }

        if (_timer[wut] > 0) {
            clearTimeout(_timer[wut]);
            _timer[wut] = 0;
        }

        if (!_data || _loading) { // Filter load on demand
            return _timer[wut] = setTimeout(_self.filterData.bind(_self, wut, value), 750);
        }

        if (!_divTotals)                                    // aowow: prevent error on matching available data against empty known data
            return;

        var _ = 0;

        if (wut === 0) { // Simulate category click
            var a = $WH.gE(_divTotals, 'a');

            for (var i = 0, len = a.length; i < len; ++i) {
                if (a[i].category == value) {
                    _ = a[i];
                    break;
                }
            }
        }

        (_filterData.bind(_, wut, value))();
    };

    this.purgeData = function () {
        if (_who.toLowerCase() != g_user.name.toLowerCase()) {
            return;
        }

        if (!confirm($WH.sprintf(LANG.confirm_purgedata, LANG['tab_' + _mode].toLowerCase()))) {
            return;
        }

        new Ajax('?profile=purge&id=' + _profile.source + '&data=' + _mode, {
            method: 'POST',
            onSuccess: function (xhr, opt) {
                location.href = g_getProfileUrl({
                    id:     _profile.id,
                    region: _profile.region[0],
                    realm:  _profile.realm[0],
                    name:   _profile.name
                });
            }
        });
    };

    this.onLoad = function (wut, catg) {
        if (wut == _opt.onDemand) {
            var a = _loading;

            _loading = null;
            _imgLoading.style.display = 'none';
            _divTipQuests.style.display = (_mode == 'quests' ? '' : 'none');

            _setData(_rawdata, _who, _when, 1);

            if (catg !== null) {
                _loaded[catg] = 1;
                _displayed = false;
                _self.filterData(0, catg);
            }
        }
    };

    this.onShow = function () {
        if (_tabs.tabs[_tabIndex].locked) {
            return window.open('?client');
        }

        if (_opt.onDemand && !_loading && !_loaded) {
            $WH.g_ajaxIshRequest(g_host + '/?data=' + _opt.onDemand + (_opt.dataArgs ? _opt.dataArgs(_profile) : '') + '&locale=' + Locale.getId() + '&t=' + g_dataKey + '&callback=$WowheadProfiler.loadOnDemand&' + (new Date().getTime()));
            _imgLoading.style.display = 'block';
            _divTipQuests.style.display = 'none';
            _loading = this;
            _loaded = {};
        }

        if (!_data || _loading) {
            return;
        }

        if (!_inited) {
            _initSource();
            _initSummary();
            _initDataTabs();
            _initSubcategories();
            _initListview();
            _initExcluded();

            _inited = true;
        }

        if (!_updated) {
            _category    = null;
            _subcategory = null;
            _complete    = (window[_opt.order] ? null : true);
            _calcDataTotal();

            _updateSource();
            _updateSummary();
            _updateDataTabs();
            _updateSubcategories();
            _updateExcluded();
            _updateListview();

            _updated = true;
        }
    };

    function _setData(data, who, when) {
        _data = [];
        _who = who;
        _when = when;
        _rawdata = data;

        var hasData = false;

        if (window[_opt.source] && !$WH[_opt.source]) {
            $WH[_opt.source] = window[_opt.source];
        }

        for (var i in window[_opt.source]) {
            var row = $WH.dO(window[_opt.source][i]);
            row.id = parseInt(i);

            if (_opt.filter && !_opt.filter(row)) {
                continue;
            }

            if ((!_opt.compute || _opt.compute(row, data[i])) && data[i]) {
                // row.completed = (data[i].getTime ? data[i] : (_opt.showQuantity ? data[i] : 1));                     // aowow: idk a way to generate a jScript-TimeObject in php (there probably isn't), but one is expected here
                row.completed = (data[i] > 946681200 ? new Date(data[i] * 1000) : (_opt.showQuantity ? data[i] : 1));   // so we create a dateObject from data[i] if it is a unixtime past the 1.1.2000 - 0:00 (wich probably isn't used as a sensible int anyway)
                hasData = true;
            }
            else {
                row.completed = null;
            }

            row.who       = _profile.name;
            row.faction   = _profile.faction + 1;
            row._included = _isIncludedData.bind(0, row, 1);

            if ( (!row.side || (row.side & (_profile.faction + 1))) &&
                 (!row.reqclass || row.reqclass & 1 << _profile.classs - 1) &&
                 (!row.reqrace || row.reqrace & 1 << _profile.race - 1) &&
                 (!row.gender || row.gender & 1 << _profile.gender) &&
                 (!row.wflags || !(row.wflags & 31)) && // Quest flags - 1 repeatable, 2 daily, 4 weekly, 8 seasonal, 16 skiplog
                 (!row.historical || (row.historical && row.completed)) &&  // Only show completed historical quests
                 (!_opt.models || (row.npcId && row.displayId && row.displayName))) {
                _data.push(row);
            }
        }

        // Attempt to pre-filter data without source info
        if (!hasData && _opt.onDemand) {
            for (var i in data) {
                if (typeof data[i] == 'object' && data[i].length) {
                    var row = {};
                    row.id = parseInt(i);

                    if (_opt.catgid) {
                        row[_opt.catgid] = data[i][1];
                    }
                    if (_opt.subcat) {
                        row[_opt.subcat] = data[i][0];
                    }

                    if (_opt.filter && !_opt.filter(row)) {
                        continue;
                    }
                }

                hasData = true;
                break;
            }
        }

        _updated   = false;
        _displayed = false;

        if (!hasData) {
            return;
        }

        _tabs.setTabTooltip(_tabIndex);
        _tabs.setTabPound(_tabIndex);
        _tabs.unlock(_tabIndex);

        if (_tabs.getSelectedTab() == _tabIndex) {
            _self.onShow();
        }
    }

    function _calcDataTotal(subtotal) {
        var
            result = {
                all: {},
                complete: {},
                incomplete: {},
                bonus: {},
                excluded: {}
            },
            overall = (subtotal == null ? _opt.overall : subtotal),

            complete    = _complete,
            category    = _category,
            subcategory = _subcategory;

        _complete    = null;
        _category    = (subtotal != null ? subtotal : null);
        _subcategory = null;
        _summary     = true;

        result.all[overall]        = 0;
        result.complete[overall]   = 0;
        result.incomplete[overall] = 0;
        result.bonus[overall]      = 0;
        result.excluded[overall]   = 0;

        for (var i = 0, len = _data.length; i < len; ++i) {
            var
                row = _data[i],
                catg = (subtotal == null ? _getTopCategory(row) : _getSubCategory(row));

            if (catg == subtotal) {
                catg = 'current';
            }
            else if (catg == overall) {
                continue;
            }

            if (result.all[catg] == null) {
                result.all[catg] = 0;
            }
            if (result.complete[catg] == null) {
                result.complete[catg] = 0;
            }
            if (result.incomplete[catg] == null) {
                result.incomplete[catg] = 0;
            }
            if (result.bonus[catg] == null) {
                result.bonus[catg] = 0;
            }
            if (result.excluded[catg] == null) {
                result.excluded[catg] = 0;
            }

            if (_isValidData(row)) {
                if (_isIncludedData(row)) {
                    if (!_isIncludedData(row, 1)) {
                        result.bonus[catg]++;
                        result.bonus[overall]++;
                        result.excluded[catg]++;
                        result.excluded[overall]++;
                        continue;
                    }

                    if (!$WH.array_index(_opt.nototal, catg)) {
                        result.all[catg]++;
                        result.all[overall]++;

                        if (row.completed) {
                            result.complete[overall]++;
                        }
                        else {
                            result.incomplete[overall]++;
                        }
                    }
                    else {
                        result.all[catg]++;
                    }

                    if (row.completed != null) {
                        result.complete[catg]++;
                    }
                    else {
                        result.incomplete[catg]++;
                    }
                }
                else {
                    result.excluded[catg]++;
                    result.excluded[overall]++;
                }
            }
        }

        // aowow: added for &partial init
        if (subtotal == null && window[_opt.order] && window[_opt.cattotal]) {
            let sumtotal = 0;

            for (var i = 0, len = window[_opt.order].length; i < len; ++i) {
                let cat = window[_opt.order][i];
                result.all[cat] = window[_opt.cattotal][cat][_profile.race][_profile.classs];
                sumtotal += window[_opt.cattotal][cat][_profile.race][_profile.classs];
            }
            result.all[overall] = sumtotal;
        }

        if (_opt.noempty) {
            for (var catg in result.complete) {
                if (catg == overall) {
                    continue;
                }

                if (!result.complete[catg] && !$WH.array_index(_opt.nototal, catg)) {
                    result.all[overall] -= result.all[catg];
                }
            }
        }

        _complete    = complete;
        _category    = category;
        _subcategory = subcategory;
        _summary     = false;

        if (subtotal != null) {
            _subtotal[subtotal] = result;
        }
        else {
            _total = result;
        }
    }

    function _initSource() {
        _divSource = _ = $WH.ce('div');
        _.className = 'text profiler-achievements-source';
        $WH.ae(_container, _);
    }

    function _updateSource() {
        $WH.ee(_divSource);

        if (_who && _when.getDate) {
            var
                uploadedOn = new Date(_when),
                elapsed = (g_serverTime - uploadedOn) / 1000,
                s = $WH.ce('span'),
                _ = $WH.ce('span');

            $WH.ae(_, s);
            g_formatDate(s, elapsed, uploadedOn);
            _divSource.innerHTML = '<small>' + $WH.sprintfa(LANG.pr_datasource, _who, _.innerHTML) + '</small>';

            if (_who.toLowerCase() == g_user.name.toLowerCase()) {
                var a = $WH.ce('a');
                a.href = 'javascript:;';
                a.onclick = _self.purgeData;
                a.className = 'delete-icon';
                a.style.marginLeft = '10px';
                g_addTooltip(a, LANG.pr_purgedata, 'q2');
                $WH.ae(a, $WH.ct(LANG.purge));
                $WH.ae(_divSource.firstChild, a);
            }

            var a = $WH.ce('a');
            a.href = 'javascript:;';
            a.onclick = ContactTool.show.bind(ContactTool, { mode: 4, profile: _profile });
            a.className = 'icon-report';
            a.style.marginLeft = '10px';
            g_addTooltip(a, LANG.report_tooltip, 'q2');
            $WH.ae(a, $WH.ct(LANG.report));
            $WH.ae(_divSource.firstChild, a);
        }
    }

    function _initSummary() {
        var
            _,
            __;

        _divSummary = _ = $WH.ce('div');
        _.className = 'profiler-achievements-summary';

        if (_opt.points) {
            _divPoints = __ = $WH.ce('div');
            __.className = 'profiler-achievements-summary-points';
            $WH.ae(_, __);
        }

        _divTotals = __ = $WH.ce('div');
        __.className = 'profiler-achievements-summary-inner';
        $WH.ae(_, __);

        $WH.ae(_container, _);
        $WH.ae(_container, _imgLoading);

        __ = _divTipQuests;
        __.className = 'text clear';
        __.innerHTML = LANG.pr_tip_quests;
        __.style.display = (_mode == 'quests' && _loaded ? '' : 'none');

        _ = $WH.gE(__, 'span')[0];
        _.onmouseover = function () {
            $WH.Tooltip.show(this, LANG.pr_tt_questtip, 0, 0, 'q');
        };
        _.onmousemove = $WH.Tooltip.cursorUpdate;
        _.onmouseout = $WH.Tooltip.hide;

        _ = $WH.ce('div');
        _.className = 'pad';

        $WH.ae(__, _);
        $WH.ae(_container, __);
    }

    function _updateSummary() {
        var _;

        if (_opt.points) {
            $WH.ee(_divPoints);
            Listview.funcBox.appendMoney(_divPoints, 0, null, 0, 0, _profile.achievementpoints);
            $WH.ae(_divPoints.firstChild, $WH.ct(' / ' + $WH.number_format(g_achievement_points[0])));
        }

        $WH.ee(_divTotals);

        $WH.ae(_divTotals, _createCategoryLink(_opt.overall, 0)); // Overall Progress

        if (window[_opt.order]) {
            var j = 1;
            for (var i = 0, len = window[_opt.order].length; i < len; ++i) {
                if (!_total.all[window[_opt.order][i]] && !_total.excluded[window[_opt.order][i]]) {
                    continue;
                }

                if (_opt.noempty && !(_total.complete[window[_opt.order][i]] || _total.excluded[window[_opt.order][i]])) {
                    continue;
                }

                $WH.ae(_divTotals, _createCategoryLink(window[_opt.order][i], j));
                ++j;
            }
        }

        _ = $WH.ce('div');
        _.className = 'clear';
        $WH.ae(_divTotals, _);
    }

    function _createCategoryLink(category, idx) {
        var
        a  = $WH.ce('a'),
        v  = $WH.ce('var'),
        em = $WH.ce('em');

        if (idx > 0) {
            a.className = (idx % 3 != 0 ? 'profiler-achievements-total' : 'profiler-achievements-total-right');
        }

        a.category = category;
        a.onclick = _filterData.bind(a, 0, (category != _opt.overall ? category : null));

        if (idx == 0) {
            $WH.ae(em, $WH.ct(LANG.pr_ach_overall));
        }
        else {
            $WH.ae(em, $WH.ct(window[_opt.catgs][category]));
        }

        $WH.ae(v, em);
        $WH.ae(v, g_createAchievementBar(_total.complete[category], (!$WH.array_index(_opt.nototal, category) ? _total.all[category] : -1), (category == _opt.overall), _total.bonus[category]));

        $WH.ae(a, v);

        if (category == _category || (idx == 0 && _category == null)) {
            g_setSelectedLink(a, _mode + '0');
        }

        return a;
    }

    function _initDataTabs() {
        _divTabs = $WH.ce('div');
        _divTabs.className = 'clear';
        $WH.ae(_container, _divTabs);

        _tabsListview = new Tabs({ parent: _divTabs, onShow: _onShowDataTab });
        _tabsListview.add(LANG.pr_note_subcategories, { id: _mode });
        _tabsListview.add(LANG.pr_note_all, { id: _mode });
        _tabsListview.add(LANG.pr_note_complete, { id: _mode });
        _tabsListview.add(LANG.pr_note_incomplete, { id: _mode });
        _tabsListview.add(LANG.pr_note_excluded, { id: _mode });
        _tabsListview.flush();
    }

    function _updateDataTabs() {
        var catg = (_category === null ? _opt.overall : _category);

        _divTabs.style.display = (_category !== null || !_opt.reqcatg ? '' : 'none');
        _tabsListview.setTabName(1, LANG.pr_note_all + ' (' + _total.all[catg] + ')');
        _tabsListview.setTabName(2, LANG.pr_note_complete + ' (' + _total.complete[catg] + (_total.bonus[catg] ? '+' + _total.bonus[catg] : '') + ')');
        _tabsListview.setTabName(3, LANG.pr_note_incomplete + ' (' + _total.incomplete[catg] + ')');
        _tabsListview.setTabName(4, LANG.pr_note_excluded + ' (' + _total.excluded[catg] + ')');
    }

    function _onShowDataTab(newTab, oldTab) {
        if (!_inited) {
            return;
        }

        if (_loading) {
            _divSubcatgs.style.display = 'none';
            _divListview.style.display = 'none';
            _divExcluded.style.display = 'none';
        }
        else {
            _validation = false;
            switch (newTab.index) {
            case 0: // Subcategory
                _divSubcatgs.style.display = '';
                _divListview.style.display = 'none';
                _divExcluded.style.display = 'none';
                break;
            case 1: // All
                _filterData(1, null);
                _divSubcatgs.style.display = 'none';
                _divListview.style.display = '';
                break;
            case 2: // Complete
                _filterData(1, true);
                _divSubcatgs.style.display = 'none';
                _divListview.style.display = '';
                _divExcluded.style.display = 'none';
                break;
            case 3: // Incomplete
                _filterData(1, false);
                _divSubcatgs.style.display = 'none';
                _divListview.style.display = '';
                _divExcluded.style.display = 'none';
                break;
            case 4: // Excluded
                _validation = true;
                _updateExcluded();
                _divSubcatgs.style.display = 'none';
                _divListview.style.display = 'none';
                _divExcluded.style.display = '';
                break;
            }
        }
    }

    function _initSubcategories() {
        _divSubcatgs = $WH.ce('div');
        _divSubcatgs.className = 'listview';

        _subcategories = new Listview({
            template:   'completion',
            id:         _mode + '-subcatgs',
            parent:     _divSubcatgs,
            onNoData:   _onNoData,
            onClickRow: _filterData
        });

        $WH.ae(_container, _divSubcatgs);
    }

    function _updateSubcategories(fixed) {
        _filterData(2, null);

        if (_subtotal == null) {
            _subtotal = {};
        }

        var hasData = false;

        var dataz = [];
        if (_category !== null && _opt.subname) {
            if (_subtotal[_category] == null) {
                _calcDataTotal(_category);
            }

            for (var i in _subtotal[_category].all) {
                // Hide same-category children
                if (i == _category) {
                    continue;
                }

                // Hide empty subcategories
                if (!_subtotal[_category].all[i] && !_subtotal[_category].excluded[i]) {
                    continue;
                }

                if (i !== 'current') {
                    hasData = true;
                }

                dataz.push({
                    id:        i,
                    name:     (i == 'current' ? _opt.subname(_category) : _opt.subname(i)),
                    current:  (i == 'current'),
                    total:    _subtotal[_category].all[i],
                    complete: _subtotal[_category].complete[i]
                });
            }
        }

        _subcategories.setData(dataz);

        if (!fixed) {
            _tabsListview.hide(0, hasData);
            _tabsListview.show((hasData ? 0 : 2), 1);
        }
    }

    function _initListview() {
        _divListview = $WH.ce('div');
        _divListview.className = 'listview';
        _divListview.style.display = 'none';

        var
            sort = null,
            hiddenCols = ['side', 'location', 'rewards', 'reagents'],
            visibleCols = ['category', 'standing', 'source'];

        switch (_mode) {
        case 'achievements':
            sort = [ -6];
            break;
        case 'recipes':
            sort = [ -7];
            break;
        case 'quests':
            hiddenCols.shift();
            break;
        }

        if (_opt.visibleCols) {
            for (var i = 0, len = _opt.visibleCols.length; i < len; ++i) {
                visibleCols.push(_opt.visibleCols[i]);
            }
        }

        if (_opt.hiddenCols) {
            for (var i = 0, len = _opt.hiddenCols.length; i < len; ++i) {
                hiddenCols.push(_opt.hiddenCols[i]);
            }
        }

        _listview = new Listview({
            template:       _opt.template,
            id:             _mode + '-includes',
            parent:         _divListview,
            visibleCols:    visibleCols,
            hiddenCols:     hiddenCols,
            extraCols:      extraCols,
            sort:           sort,
            customFilter:   _isValidData,
            onBeforeCreate: _onBeforeCreate,
            onNoData:       _onNoData,
            onEmptyFilter:  _onEmptyFilter
        });

        $WH.ae(_container, _divListview);
    }

    function _updateListview() {
        if (_opt.reqcatg && _category === null) {
            return;
        }

        _listview.setData($WH.array_filter(_data, function (x) {
            return _isIncludedData(x);
        }));
        _listview.updateFilters(true);

        _displayed = true;
    }

    function _initExcluded() {
        _divExcluded = $WH.ce('div');
        _divExcluded.className = 'listview';
        _divExcluded.style.display = 'none';

        var
            sort = null,
            hiddenCols = ['side', 'location', 'rewards', 'reagents'];

        switch (_mode) {
        case 'achievements':
            sort = [ -6];
            break;
        case 'recipes':
            sort = [ -7];
            break;
        case 'quests':
            hiddenCols.shift();
            break;
        }

        _excluded = new Listview({
            template:       _opt.template,
            id:             _mode + '-excludes',
            parent:         _divExcluded,
            visibleCols:    ['category', 'standing', 'source'],
            hiddenCols:     hiddenCols,
            extraCols:      extraCols,
            sort:           sort,
            customFilter:   _isValidData,
            onBeforeCreate: _onBeforeCreate,
            include:        1,
            onNoData:       _onNoData,
            onEmptyFilter:  _onEmptyFilter
        });

        $WH.ae(_container, _divExcluded);
    }

    function _updateExcluded() {
        if (_opt.reqcatg && _category === null) {
            return;
        }

        _excluded.setData($WH.array_filter(_data, function (x) {
            return ! _isIncludedData(x) || !_isIncludedData(x, 1);
        }));

        _excluded.updateFilters();
        _excluded.refreshRows();
    }

    function _onNoData(div) {
        // Its filtered!
        if ((! (_opt.reqcatg && _category == null) && this.filtered) || (!this.include && (_category !== null || _subcategory !== null))) {
            return -1;
        }

        if (this.quickSearchBox.value != '') {
            $WH.ae(div, $WH.ct(LANG.lvnodata2));
            return;
        }

        this.bandTop.style.display = this.bandBot.style.display = this.mainContainer.style.display = (_category !== null || _subcategory !== null ? '' : 'none');
        $WH.ae(div, $WH.ct(this.include ? LANG.lvnodata4 : LANG.lvnodata3));
    }

    function _onEmptyFilter(col) {
        if (col.id == 'category') {
            this.removeIndicators();
        }
    }

    function _onBeforeCreate() {
        if (!g_user) {
            g_user = {};
        }
        if (!g_user.excludes) {
            g_user.excludes = {};
        }
        if (!g_user.includes) {
            g_user.includes = {};
        }
        if (!g_user.excludes[_opt.typeid]) {
            g_user.excludes[_opt.typeid] = [];
        }
        if (!g_user.includes[_opt.typeid]) {
            g_user.includes[_opt.typeid] = [];
        }

        this.poundable = 0;
        this.createCbControls = function (d, topBar) {
            var f = function (mode) {
                var rows = this.getCheckedRows();

                if (!rows.length) {
                    alert($WH.sprintf(LANG.message_norowselected, mode));
                }
                else {
                    var ids = [];
                    $WH.array_walk(rows, function (x) {
                        g_user.includes[_opt.typeid] = $WH.array_filter(g_user.includes[_opt.typeid], function (z) {
                            return z != x.id;
                        });
                        g_user.excludes[_opt.typeid] = $WH.array_filter(g_user.excludes[_opt.typeid], function (z) {
                            return z != x.id;
                        });
                        g_user[mode + 's'][_opt.typeid].push(x.id);
                        x.__tr = null; // Force row to recompute
                        ids.push(x.id);
                    });

                    if (ids.length) {
                        $.post('?account=exclude', {
                            type: _opt.typeid,
                            id:   ids.join(','),
                            mode: 1
                        });
                        _updateExclusions();
                    }
                }
            };

            var
                iSelectAll = $WH.ce('input'),
                iDeselect = $WH.ce('input'),
                iExclude = $WH.ce('input'),
                iInclude = $WH.ce('input'),
                iManage = $WH.ce('input');

            iSelectAll.type = iDeselect.type = iExclude.type = iInclude.type = iManage.type = 'button';

            iSelectAll.value = LANG.button_selectall;
            iDeselect.value = LANG.button_deselect;
            iExclude.value = LANG.button_exclude;
            iInclude.value = LANG.button_include;
            iManage.value = LANG.button_quickexclude;

            iSelectAll.onclick = Listview.cbSelect.bind(this, true);
            iDeselect.onclick = Listview.cbSelect.bind(this, false);
            iExclude.onclick = f.bind(this, 'exclude');
            iInclude.onclick = f.bind(this, 'include');
            iManage.onclick = _dialog.show.bind(null, 'quickexclude', { data: { empty: _isEmptyExcludeGroup }, onSubmit: _updateExcludeGroups });

            iExclude.onmouseover = function (e) {
                $WH.Tooltip.showAtCursor(e, LANG.pr_tt_exclude);
            };
            iInclude.onmouseover = function (e) {
                $WH.Tooltip.showAtCursor(e, LANG.pr_tt_include);
            };

            iExclude.onmousemove = iInclude.onmousemove = $WH.Tooltip.cursorUpdate;
            iExclude.onmouseout = iInclude.onmouseout = $WH.Tooltip.hide;

            iExclude.style.display = (this.include ? 'none' : '');
            iInclude.style.display = (this.include ? '' : 'none');

            $WH.ae(d, iSelectAll);
            $WH.ae(d, iExclude);
            $WH.ae(d, iInclude);
            $WH.ae(d, iDeselect);
            $WH.ae(d, $WH.ct('| '));

            $WH.ae(d, iManage);
        };

        if (!_opt.models) {
            this.mode = 1;
        }
    }

    function _isEmptyExcludeGroup(mask) {
        if (mask == null) {
            var empty = true;

            for (var i in g_excludes[_opt.typeid]) {
                empty = (empty && _isEmptyExcludeGroup(1 << i - 1));
            }

            return empty;
        }

        for (var i in g_excludes[_opt.typeid]) {
            if (!(mask & 1 << i - 1)) {
                continue;
            }

            for (var j = 0, len = _data.length; j < len; ++j) {
                if ($WH.array_index(g_excludes[_opt.typeid][i], _data[j].id)) {
                    return false;
                }
            }
        }

        return true;
    }

    function _updateExcludeGroups(groups) {
        var g = (g_user.settings ? g_user.excludegroups | 0 : 1);

        for (var i in groups) {
            if (isNaN(i)) {
                continue;
            }

            var action = groups[i] = parseInt(groups[i]);

            if (action == 1 && (g & i)) { // Include
                g -= parseInt(i);
            }
            if (action == 2 && !(g & i)) { // Exclude
                g += parseInt(i);
            }
        }

        g_user.excludegroups = g;

        $.post('?account=exclude', { groups: g });
        _updateExclusions();
    }

    function _updateExclusions() {
        if (!_updated || !_displayed) {
            return;
        }

        _subtotal = null;

        _calcDataTotal();

        _updateSummary();
        _updateDataTabs();
        _updateSubcategories(1);
        _updateExcluded();
        _updateListview();
    }

    function _isIncludedData(row, strict) {
        // Include complete stuff
        if (!strict && row.completed) {
            return true;
        }

        // Nothing is excluded
        if (!g_excludes[_opt.typeid] && (!g_user.excludes || !g_user.excludes[_opt.typeid])) {
            return true;
        }

        // Explicitly included
        if (g_user.includes && g_user.includes[_opt.typeid] && $WH.array_index(g_user.includes[_opt.typeid], row.id)) {
            return true;
        }

        // Explicitly excluded
        if (g_user.excludes && g_user.excludes[_opt.typeid] && $WH.array_index(g_user.excludes[_opt.typeid], row.id)) {
            return false;
        }

        var mask = (g_user.settings ? g_user.excludegroups | 0 : 1);

        for (var i in g_excludes[_opt.typeid]) {
            if (!$WH.array_index(g_excludes[_opt.typeid][i], row.id)) {
                continue;
            }

            if (! (mask & 1 << i - 1)) {
                continue;
            }

            if (i < 6 || i > 10) {
                return false;
            }
            if (i == 6 && !((_profile.faction + 1) & 1)) { // Alliance favored
                return false;
            }
            if (i == 7 && !((_profile.faction + 1) & 2)) { // Horde favored
                return false;
            }
            if (i == 8 && !_profile.skills[356]) { // Fishing bop disabled
                continue;
            }
            if (i == 9 && !_profile.skills[202]) { // Engineer bop
                return false;
            }
            if (i == 10 && !_profile.skills[197]) { // Tailor bop
                return false;
            }
        }

        return true;
    }

    function _getTopCategory(row) {
        var id = row[_opt.catgid];
        if (typeof id == 'object' && id.length) {
            id = id[0];
        }
        return id;
    }

    function _getSubCategory(row) {
        var id = row[_opt.subcat];
        if (typeof id == 'object' && id.length) {
            id = id[0];
        }
        return ($WH.array_index(_opt.nosubcatg, _getTopCategory(row)) ? _getTopCategory(row) : id);
    }

    function _isValidData(row) {
        if (!row.id || (_category === null && _opt.reqcatg && !_summary)) {
            return;
        }

        return (
            // Included
            (_validation || _summary || _isIncludedData(row, !_complete)) &&

            // Category
            ((_category === null && (!$WH.array_index(_opt.nototal, _getTopCategory(row)) || _summary)) || _category == _getTopCategory(row)) &&

            // Subcategory
            (!_summary || (_subcategory === null || _subcategory == _getSubCategory(row))) &&

            // Complete
            (
                _validation ||
                _complete == null ||
              ( _complete && row.completed) ||
              (!_complete && row.completed == null)
            )
        );
    }

    function _filterData(wut, value) {
        switch (wut) {
            case 0:
                var _ = _category;
                _category = value;

                if (_category !== null) {
                    if (_opt.onDemand && _opt.partial && !_loaded[_category] && !_loading) {
                        $WH.g_ajaxIshRequest(g_host + '/?data=' + _opt.onDemand + '&locale=' + Locale.getId() + '&catg=' + _category + '&t=' + g_dataKey + '&callback=$WowheadProfiler.loadOnDemand&' + (new Date().getTime()));
                        _imgLoading.style.display = 'block';
                        _divTipQuests.style.display = 'none';
                        _loading = this;
                        _category = null;
                    }
                    else if (!_displayed) {
                        _updateListview();
                        _updateExcluded();
                    }
                }

                _listview.resetFilters();
                _listview.clearSearch();
                _listview.quickSearchBox.onblur();
                break;
            case 1:
                _complete = value;

                var btns = $WH.gE(_listview.cbBarTop, 'input');
                for (var i = 0, len = btns.length; i < len; ++i) {
                    if (btns[i].value == LANG.button_include) {
                        btns[i].style.display = (_complete === false ? 'none' : '');
                        break;
                    }
                }
                break;
            case 2:
                _subcategory = (value == 'current' ? _category : value);
                _listview.removeIndicators();
                _excluded.removeIndicators();
                break;
            default:
                return;
        }

        _listview.updateFilters(true);
        _excluded.updateFilters(true);

        if (this && this.nodeName == 'A') {
            g_setSelectedLink(this, _mode + wut);
        }

        if (wut == 0) { // Category change
            _updateSubcategories();
            _updateDataTabs();
        }

        if (wut == 2) { // Subcategory change
            if (!_subcategory && this && this.nodeName == 'TR') {
                this.className = '';
            }

            _updateDataTabs();

            if (_subcategory) {
                if (_subtotal[_category] == null) {
                    _calcDataTotal(_category);
                }

                _tabsListview.show((_subtotal[_category].complete[_subcategory] ? 2 : 3));

                if (_opt.subname) {
                    _listview.createIndicator($WH.sprintf(LANG['lvnote_' + _mode + 'ind'], _category, _subcategory, _opt.subname(_subcategory)), Listview.headerFilter.bind(_listview, _listview.columns[_listview.columns.length - 2], ''));
                    _excluded.createIndicator($WH.sprintf(LANG['lvnote_' + _mode + 'ind'], _category, _subcategory, _opt.subname(_subcategory)), Listview.headerFilter.bind(_excluded, _listview.columns[_listview.columns.length - 2], ''));
                    setTimeout(Listview.headerFilter.bind(_listview, _listview.columns[_listview.columns.length - 2], _opt.subname(_subcategory)), 1);
                    setTimeout(Listview.headerFilter.bind(_excluded, _excluded.columns[_excluded.columns.length - 2], _opt.subname(_subcategory)), 1);
                }
            }
        }

        return false;
    }
}

Listview.templates.inventory = {
    searchable: 1,
    filtrable: 1,

    createNote: function (div) {
        var
            sp = $WH.ce('span');

            sp.style.color = 'red';
        sp.style.fontWeight = 'bold';

        $WH.st(sp, String.fromCharCode(160));

        $WH.ae(div, sp);
    },

    columns: [
        {
            id: 'name',
            name: LANG.name,
            type: 'text',
            align: 'left',
            span: 2,
            value: 'name',
            compute: function (item, td, tr) {
                var
                    iconTd = $WH.ce('td'),
                    icon   = g_items.createIcon(item.id, 0, null, 'javascript:;'),
                    a      = Icon.getLink(icon);

                a.oncontextmenu = $WH.rf;
                a.onclick = ($WH.Browser.opera || $WH.OS.mac ? function (e) {
                    e = $WH.$E(e);
                    if (e.shiftKey || e.ctrlKey) {
                        return false;
                    }
                } : null);
                a.onmouseup = $WowheadProfiler.onMouseUpSlot.bind(0, item.profileslot, a);
                a.href = '?item=' + item.id;

                icon.className += ' iconsmall-q' + (7 - parseInt(item.name.charAt(0)));// Don't use _getItemHilite because it is confusing

                $WowheadProfiler.updateMenu(item.profileslot, a);

                iconTd.style.width = '1px';
                iconTd.style.padding = '0';
                iconTd.style.borderRight = 'none';

                $WH.ae(iconTd, icon);
                $WH.ae(tr, iconTd);

                td.style.borderLeft = 'none';

                a = $WH.ce('a');
                a.oncontextmenu = $WH.rf;
                a.onclick = ($WH.Browser.opera || $WH.OS.mac ? function(e) {
                    e = $WH.$E(e);
                    if (e.shiftKey || e.ctrlKey) {
                        return false;
                    }
                } : null);
                a.onmouseup = $WowheadProfiler.onMouseUpSlot.bind(0, item.profileslot, a);
                a.className = 'q' + (7 - parseInt(item.name.charAt(0)));
                a.style.fontFamily = 'Verdana, sans-serif';
                a.href = '?item=' + item.id;

                $WowheadProfiler.updateMenu(item.profileslot, a);

                if (item.rel) {
                    Icon.getLink(icon).rel = item.rel;
                    a.rel = item.rel;
                }

                $WH.ae(a, $WH.ct(item.name.substring(1)));

                $WH.ae(td, a);
            },
            getVisibleText: function (item) {
                return item.name.substring(1);
            }
        },
        {
            id: 'level',
            name: LANG.level,
            value: 'level',
            compute: function (item, td, tr) {
                if (item.errors['level']) {
                    td.style.color = 'red';
                    td.style.fontWeight = 'bold';
                }

                return item.level;
            }
        },
        {
            id: 'slot',
            name: LANG.slot,
            type: 'text',
            compute: function (item, td) {
                $WH.nw(td);

                return g_item_slots[item.slot];
            },
            getVisibleText: function (item) {
                return g_item_slots[item.slot];
            },
            sortFunc: function (a, b, col) {
                return $WH.strcmp(g_item_slots[a.slot], g_item_slots[b.slot]);
            }
        },
        {
            id: 'source',
            name: LANG.source,
            type: 'text',
            compute: function (item, td) {
                td.className = 'small';

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

                            if (sm.t == 5) {
                                a.className += ' icontinyl';
                                a.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/quest_start.gif)';
                            }

                            a.href = '?' + g_types[sm.t] + '=' + sm.ti;

                            $WH.ae(a, $WH.ct(sm.n));
                            $WH.ae(td, a);
                        }
                        else if (sm.z) {
                            if (item.source[0] == 5) {
                                $WH.ae(td, $WH.ct(LANG.pr_vendorsin));
                            }

                            var a = $WH.ce('a');
                            a.className = 'q1';
                            a.href = '?zone=' + sm.z;
                            $WH.ae(a, $WH.ct(g_zones[sm.z]));
                            $WH.ae(td, a);
                        }
                        else {
                            $WH.ae(td, $WH.ct(Listview.funcBox.getUpperSource(item.source[0], sm)));
                        }

                        if (sm.dd) {
                            if (sm.dd < 0) { // Dungeon
                                $WH.ae(td, $WH.ct($WH.sprintf(LANG.lvitem_dd, '', (sm.dd < -1 ? LANG.lvitem_heroic : LANG.lvitem_normal))));
                            }
                            else { // 10 or 25 Player Raid
                                $WH.ae(td, $WH.ct($WH.sprintf(LANG.lvitem_dd, (sm.dd & 1 ? LANG.lvitem_raid10 : LANG.lvitem_raid25), (sm.dd > 2 ? LANG.lvitem_heroic : LANG.lvitem_normal))));
                            }
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
            getVisibleText: function (item) {
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

                        ls = Listview.funcBox.getLowerSource(item.source[0], sm, type);

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
            sortFunc: function (a, b, col) {
                var res = Listview.funcBox.assocArrCmp(a.source, b.source, g_sources);

                if (res != 0) {
                    return res;
                }

                var
                    na = (a.sourcemore && a.source.length == 1 ? a.sourcemore[0].n : null),
                    nb = (b.sourcemore && b.source.length == 1 ? b.sourcemore[0].n : null);

                return $WH.strcmp(na, nb);
            }
        }
    ],

    getItemLink: function (item) {
        return '?item=' + item.id;
    }
};

Listview.templates.completion = {
    sort: [1],
    nItemsPerPage: -1,
    searchable: 1,
    poundable: 0,

    createNote: function (div) {
        var sm = $WH.ce('small');
        $WH.st(sm, LANG.lvnote_clicksubcatg);
        $WH.ae(div, sm);
    },

    columns: [
        {
            id: 'name',
            name: LANG.name,
            type: 'text',
            align: 'left',
            value: 'name',
            compute: function (row, td, tr) {
                var img = $WH.ce('img');
                img.style.margin = '0 6px -3px 0';
                img.src = (row.complete == row.total ? g_staticUrl + '/images/icons/tick.png' : g_staticUrl + '/images/icons/delete.gif');
                img.onmouseover = function (e) {
                    $WH.Tooltip.showAtCursor(e, LANG[(row.complete == row.total ? 'pr_note_complete' : 'pr_note_incomplete')], 0, 0, 'q');
                };
                img.onmousemove = $WH.Tooltip.cursorUpdate;
                img.onmouseout = $WH.Tooltip.hide;
                img.style.border = 'none';
                $WH.ae(td, img);

                if (row.current) {
                    var b = $WH.ce('b');
                    $WH.st(b, row.name);
                    $WH.ae(td, b);
                }
                else {
                    $WH.ae(td, $WH.ct(row.name));
                }
            },
            sortFunc: function (a, b, col) {
                return -$WH.strcmp(a.current, b.current) || $WH.strcmp(a.name, b.name);
            }
        },
        {
            id: 'progress',
            name: LANG.progress,
            width: '250px',
            compute: function (row, td, tr) {
                td.style.padding = 0;
                $WH.ae(td, g_createAchievementBar(row.complete, row.total));
                tr.onclick = this.onClickRow.bind(tr, 2, row.id);
            },
            sortFunc: function (a, b, col) {
                var pctA = Math.floor((a.complete / a.total) * 1000);
                var pctB = Math.floor((b.complete / b.total) * 1000);

                if (pctA == pctB) {
                    return a.total - b.total;
                }
                return pctA - pctB;
            }
        }
    ]
};

Listview.templates.gallery = {
    sort: [1],
    mode: 3, // Grid mode
    nItemsPerPage: -1,
    nItemsPerRow: 8,
    searchable: 1,
    poundable: 2, // Yes but w/o sort

    columns: [{ value: 'displayName' }],

    compute: function (spell, td, i) {
        td.className = 'screenshot-cell';
        td.vAlign = 'top';

        Listview.funcBox.ssCreateCb(td, spell);

        var a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = this.template.modelShow.bind(this.template, spell.npcId, spell.displayId, false);
        a.style.position = 'relative';

        var img = $WH.ce('img');
        img.src = (spell.completed ? g_staticUrl + '/images/icons/' + (!spell._included() ? 'star' : 'tick') + '.png' : g_staticUrl + '/images/icons/delete.gif');
        img.onmouseover = function (e) {
            $WH.Tooltip.showAtCursor(e, LANG[(spell.completed ? (spell._included() ? 'pr_note_complete' : 'pr_tt_excldone') : 'pr_note_incomplete')], 0, 0, 'q');
        };
        img.onmousemove = $WH.Tooltip.cursorUpdate;
        img.onmouseout = $WH.Tooltip.hide;
        img.style.border = 'none';
        img.style.position = 'absolute';
        img.style.right = '6px';
        img.style.bottom = '9px';
        $WH.ae(a, img);

        img = $WH.ce('img');
        img.src = g_staticUrl + '/modelviewer/thumbs/npc/' + spell.displayId + '.png';
        img.height = img.width = 75;
        $WH.ae(a, img);

        $WH.ae(td, a);

        d = $WH.ce('div');
        d.className = 'screenshot-cell-user';

        a = $WH.ce('a');
        a.href = '?spell=' + spell.id;
        $WH.ae(a, $WH.ct(spell.displayName));
        $WH.ae(d, a);

        $WH.ae(td, d);

        var item = spell.item;

        if (item && item.source) {
            d = $WH.ce('div');
            d.style.position = 'relative';
            d.style.height = '1em';

            var d2 = $WH.ce('div');
            d2.className = 'screenshot-caption';

            var s = $WH.ce('small');

            if (item.source.length == 1) {
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

                    if (sm.t == 5) {
                        a.className += ' icontinyl';
                        a.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/quest_start.gif)';
                    }

                    a.href = '?' + g_types[sm.t] + '=' + sm.ti;

                    $WH.ae(a, $WH.ct(sm.n));
                    $WH.ae(s, a);
                }
                else if (sm.z) {
                    if (item.source[0] == 5) {
                        $WH.ae(s, $WH.ct(LANG.pr_vendorsin));
                    }

                    var a = $WH.ce('a');
                    a.className = 'q1';
                    a.href = '?zone=' + sm.z;
                    $WH.ae(a, $WH.ct(g_zones[sm.z]));
                    $WH.ae(s, a);
                }
                else {
                    $WH.ae(s, $WH.ct(Listview.funcBox.getUpperSource(item.source[0], sm)));
                }

                if (sm.dd) {
                    if (sm.dd < 0) { // Dungeon
                        $WH.ae(s, $WH.ct($WH.sprintf(LANG.lvitem_dd, '', (sm.dd < -1 ? LANG.lvitem_heroic : LANG.lvitem_normal))));
                    }
                    else { // 10 or 25 Player Raid
                        $WH.ae(s, $WH.ct($WH.sprintf(LANG.lvitem_dd, (sm.dd & 1 ? LANG.lvitem_raid10 : LANG.lvitem_raid25), (sm.dd > 2 ? LANG.lvitem_heroic : LANG.lvitem_normal))));
                    }
                }
            }
            else {
                for (var i = 0, len = item.source.length; i < len; ++i) {
                    if (i > 0) {
                        $WH.ae(s, $WH.ct(LANG.comma));
                    }

                    $WH.ae(s, $WH.ct(g_sources[item.source[i]]));
                }
            }

            $WH.ae(s, $WH.ce('br'));
            $WH.ae(d2, s);
            $WH.ae(d, d2);
            $WH.ae(td, d);
        }

        $WH.aE(td, 'click', this.template.modelShow.bind(this.template, spell.npcId, spell.displayId, true));
    },

    getVisibleText: function (spell) {
        var buff = spell.displayName;

        var item = spell.item;

        if (item && item.source != null) {
            if (item.source.length == 1) {
                var sm = (item.sourcemore ? item.sourcemore[0] : {});
                var type = 0;

                if (sm.t) {
                    type = sm.t;
                    buff += ' ' + sm.n;
                }
                else if (sm.z) {
                    buff += ' ' + g_zones[sm.z];
                }

                buff += ' ' + Listview.funcBox.getUpperSource(item.source[0], sm);

                if (sm.dd) {
                    buff += ' ' + (sm.dd < -1 || sm.dd > 2 ? LANG.pr_print_heroic : LANG.pr_print_normal);
                }
            }
            else {
                for (var i = 0, len = item.source.length; i < len; ++i) {
                    buff += ' ' + g_sources[item.source[i]];
                }
            }
        }

        return buff;
    },

    modelShow: function (npcId, displayId, sp, e) {
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

        ModelViewer.show({ type: 1, typeId: npcId, displayId: displayId, noPound: 1 });
    }
};

Listview.templates.itempicker = {
    sort: [1],
    nItemsPerPage: -1,
    hideBands: 2,
    hideNav: 1 | 2,
    hideHeader: 1,
    searchable: 1,
    searchDelay: 100,
    poundable: 0,
    filtrable: 0,
    clip: {
        w: 780,
        h: 486
    },

    onBeforeCreate: function () {},

    onSearchSubmit: function (item) {
        if (this.nRowsVisible != 2 || !item.id) { // None
            return;
        }

        $WowheadProfiler.equipItem(item.id);
    },

    columns: [
        {
            id: 'item',
            type: 'text',
            align: 'left',
            value: 'name',
            span: 2,
            compute: function (item, td, tr) {
                if (item.none) {
                    return;
                }

                var i = $WH.ce('td');
                i.style.width = '1px';
                i.style.padding = '0';
                i.style.borderRight = 'none';

                var
                    icon = Icon.create(item.icon, 0, null, '?item=' + item.id),
                    link = Icon.getLink(icon);

                $WH.ae(i, icon);
                $WH.ae(tr, i);
                td.style.borderLeft = 'none';

                link.onclick = $WH.rf;

                var a = $WH.ce('a');
                a.className = 'q' + item.quality;
                a.style.fontFamily = 'Verdana, sans-serif';
                a.href = '?item=' + item.id;

                $WH.ae(a, $WH.ct(item.name));

                $WH.nw(td);
                $WH.ae(td, a);

                $(tr).click(function (e) {
                    if (e.which != 2 || e.target != a) {
                        e.preventDefault();
                        ($WowheadProfiler.equipItem.bind(this, item.id, item.slot))();
                    }
                });
            },
            sortFunc: function (a, b, col) {
                if (a.none) {
                    return -1;
                }
                if (b.none) {
                    return 1;
                }

                return (
                    -$WH.strcmp(a.gearscore, b.gearscore) ||
                    -$WH.strcmp(a.level, b.level) ||
                    -$WH.strcmp(a.quality, b.quality) ||
                    $WH.strcmp(a.name, b.name)
                );
            }
        },
        {
            id: 'level',
            name: LANG.level,
            value: 'level',
            sortFunc: function (a, b, col) {
                if (a.none) {
                    return -1;
                }
                if (b.none) {
                    return 1;
                }

                return (
                    -$WH.strcmp(a.score, b.score) ||
                    $WH.strcmp(a.name, b.name)
                );
            }
        },
        {
            id: 'type',
            name: LANG.type,
            type: 'text',
            compute: function (item, td, tr) {
                if (item.none) {
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
            getVisibleText: function (item) {
                return Listview.funcBox.getItemType(item.classs, item.subclass, item.subsubclass).text;
            }
        },
        {
            id: 'score',
            align: 'center',
            compute: function (item, td, tr) {
                if (item.none) {
                    $WH.ee(tr);

                    $(tr).click($WowheadProfiler.equipItem.bind(this, 0, item.slot));
                    td.colSpan = 5;
                    td.style.fontWeight = 'bold';
                    td.style.textAlign = 'center';

                    return LANG.dash + LANG.pr_noneitem + LANG.dash;
                }

                if (item.score != null) {
                    var
                        n = parseFloat(item.score),
                        s = $WH.ce('div');

                    s.className = 'small q' + (n ? (n > 0 ? 2 : 10) : 0);
                    $WH.st(s, (n ? (n > 0 ? '+' : '-') + item.score: 0));
                    $WH.ae(td, s);
                }
            }
        }
    ]
};

Listview.templates.subitempicker = {
    sort: [1],
    nItemsPerPage: -1,
    hideBands: 2,
    hideNav: 1 | 2,
    hideHeader: 1,
    searchable: 1,
    searchDelay: 100,
    poundable: 0,
    filtrable: 0,
    clip: {
        w: 780,
        h: 486
    },

    onBeforeCreate: function () {},

    onSearchSubmit: function (subitem) {
        if (this.nRowsVisible != 2 || !subitem.id) { // None
            return;
        }

        $WowheadProfiler.equipSubitem(subitem.id);
    },

    columns: [
        {
            id: 'subitem',
            type: 'text',
            align: 'left',
            value: 'name',
            span: 2,
            compute: function (subitem, td, tr) {
                if (subitem.none) {
                    return;
                }

                var
                    url  = '?item=' + subitem.item,
                    item = g_items[subitem.item];

                var i = $WH.ce('td');
                i.style.width = '1px';
                i.style.padding = '0';
                i.style.borderRight = 'none';

                var
                    icon = Icon.create(item.icon, 0, null, url),
                    link = Icon.getLink(icon);

                $WH.ae(i, icon);
                $WH.ae(tr, i);
                td.style.borderLeft = 'none';

                link.onclick = $WH.rf;
                link.rel = 'rand=' + subitem.id;

                var a = $WH.ce('a');

                if (subitem.quality != -1) {
                    a.className = 'q' + item.quality;
                }

                a.style.fontFamily = 'Verdana, sans-serif';
                a.href = url;
                a.rel = 'rand=' + subitem.id;

                $WH.ae(a, $WH.ct(item['name_' + Locale.getName()] + ' ' + subitem.name));

                $WH.nw(td);
                $WH.ae(td, a);

                $(tr).click(function (e) {
                    if (e.which != 2 || e.target != a) {
                        e.preventDefault();
                        ($WowheadProfiler.equipSubitem.bind(this, subitem.id))();
                    }
                });
            }
        },
        {
            id: 'enchantment',
            type: 'text',
            align: 'left',
            value: 'enchantment',
            compute: function (subitem, td, tr) {
                if (subitem.none) {
                    return;
                }

                var d = $WH.ce('div');
                d.className = 'small crop';
                td.title = subitem.enchantment;
                $WH.ae(d, $WH.ct(subitem.enchantment));
                $WH.ae(td, d);
            },
            sortFunc: function (a, b, col) {
                if (a.none) {
                    return -1;
                }
                if (b.none) {
                    return 1;
                }

                return (
                    -$WH.strcmp(a.score, b.score) ||
                    $WH.strcmp(a.name, b.name)
                );
            }
        },
        {
            id: 'score',
            align: 'center',
            compute: function (subitem, td, tr) {
                if (subitem.none) {
                    return;
                }

                if (subitem.score != null) {
                    var
                        n = parseFloat(subitem.score),
                        s = $WH.ce('div');

                    s.className = 'small q' + (n ? (n > 0 ? 2 : 10) : 0);
                    $WH.st(s, (n ? (n > 0 ? '+' : '-') + subitem.score: 0));
                    $WH.ae(td, s);
                }
            }
        }
    ]
};

Listview.templates.gempicker = {
    sort: [1],
    nItemsPerPage: -1,
    hideBands: 2,
    hideNav: 1 | 2,
    hideHeader: 1,
    searchable: 1,
    searchDelay: 100,
    poundable: 0,
    filtrable: 0,
    clip: {
        w: 780,
        h: 486
    },

    onBeforeCreate: function () {},

    onSearchSubmit: function (gem) {
        if (this.nRowsVisible != 2 || !gem.id) { // None
            return;
        }

        $WowheadProfiler.socketItem(gem.id);
    },

    columns: [
        {
            id: 'gem',
            type: 'text',
            align: 'left',
            value: 'name',
            span: 2,
            compute: function (gem, td, tr) {
                if (gem.none) {
                    return;
                }

                var i = $WH.ce('td');
                i.style.width = '1px';
                i.style.padding = '0';
                i.style.borderRight = 'none';

                var
                    icon = Icon.create(gem.icon, 0, null, '?item=' + gem.id),
                    link = Icon.getLink(icon);

                    $WH.ae(i, icon);
                $WH.ae(tr, i);
                td.style.borderLeft = 'none';

                link.onclick = $WH.rf;

                var a = $WH.ce('a');
                a.className = 'q' + gem.quality;
                a.style.fontFamily = 'Verdana, sans-serif';
                a.href = '?item=' + gem.id;

                $WH.ae(a, $WH.ct(gem.name));

                $WH.nw(td);
                $WH.ae(td, a);

                $(tr).click(function (e) {
                    if (e.which != 2 || e.target != a) {
                        e.preventDefault();
                        ($WowheadProfiler.socketItem.bind(this, gem.id, gem.socket, gem.slot))();
                    }
                });
            },
            sortFunc: function (a, b, col) {
                if (a.none) {
                    return -1;
                }
                if (b.none) {
                    return 1;
                }

                return (
                    -$WH.strcmp(a.gearscore, b.gearscore) ||
                    -$WH.strcmp(a.quality, b.quality) ||
                    $WH.strcmp(a.colors, b.colors) ||
                    $WH.strcmp(a.icon, b.icon) ||
                    $WH.strcmp(a.name, b.name)
                );
            }
        },
        {
            id: 'enchantment',
            type: 'text',
            align: 'left',
            value: 'enchantment',
            compute: function (gem, td, tr) {
                if (gem.none) {
                    return;
                }

                var d = $WH.ce('div');
                d.className = 'small crop';
                td.title = gem.enchantment;
                $WH.ae(d, $WH.ct(gem.enchantment));
                $WH.ae(td, d);
            },
            sortFunc: function (a, b, col) {
                if (a.none) {
                    return -1;
                }
                if (b.none) {
                    return 1;
                }

                return (
                    -$WH.strcmp(a.score, b.score) ||
                    $WH.strcmp(a.name, b.name)
                );
            }
        },
        {
            id: 'colors',
            compute: function (gem, td, tr) {
                if (gem.none) {
                    return;
                }

                td.className = 'small gem' + gem.colors;
                $WH.nw(td);

                return g_gem_colors[gem.colors];
            },
            getVisibleText: function (gem) {
                if (gem.none) {
                    return;
                }

                return g_gem_colors[gem.colors];
            }
        },
        {
            id: 'score',
            align: 'center',
            compute: function (gem, td, tr) {
                if (gem.none) {
                    $WH.ee(tr);

                    $(tr).click($WowheadProfiler.socketItem.bind(this, 0, gem.socket, gem.slot));
                    td.colSpan = 5;
                    td.style.fontWeight = 'bold';
                    td.style.textAlign = 'center';

                    return LANG.dash + LANG.pr_nonegem + LANG.dash;
                }

                if (gem.score != null) {
                    var
                        n = parseFloat(gem.score),
                        s = $WH.ce('div');

                    s.className = 'small q' + (n ? (n > 0 ? 2 : 10) : 0);
                    $WH.st(s, (n ? (n > 0 ? '+' : '-') + gem.score: 0));
                    $WH.ae(td, s);
                }
            }
        }
    ]
};

Listview.templates.enchantpicker = {
    sort: [1],
    nItemsPerPage: -1,
    hideBands: 2,
    hideNav: 1 | 2,
    hideHeader: 1,
    searchable: 1,
    searchDelay: 100,
    poundable: 0,
    filtrable: 0,
    clip: {
        w: 780,
        h: 486
    },

    onBeforeCreate: function () {},

    onSearchSubmit: function (enchant, n) {
        if (this.nRowsVisible != 2 || !enchant.id) { // None
            return;
        }

        $WowheadProfiler.enchantItem(enchant.id);
    },

    columns: [
        {
            id: 'enchant',
            type: 'text',
            align: 'left',
            value: 'name',
            span: 2,
            compute: function (enchant, td, tr) {
                if (enchant.none) {
                    return;
                }

                var url = (enchant.source > 0 ? '?spell=' : '?item=') + Math.abs(enchant.source);

                var i = $WH.ce('td');
                i.style.width = '1px';
                i.style.padding = '0';
                i.style.borderRight = 'none';

                var
                    icon = Icon.create(enchant.icon, 0, null, url),
                    link = Icon.getLink(icon);

                $WH.ae(i, icon);
                $WH.ae(tr, i);
                td.style.borderLeft = 'none';

                link.onclick = $WH.rf;

                var a = $WH.ce('a');

                if (enchant.quality != -1) {
                    a.className = 'q' + enchant.quality;
                }

                a.style.fontFamily = 'Verdana, sans-serif';
                a.href = url;

                $WH.ae(a, $WH.ct(enchant.name));

                $WH.nw(td);
                $WH.ae(td, a);

                $(tr).click(function (e) {
                    if (e.which != 2 || e.target != a) {
                        e.preventDefault();
                        ($WowheadProfiler.enchantItem.bind(this, enchant.id))();
                    }
                });
            },
            sortFunc: function (a, b, col) {
                if (a.none) {
                    return -1;
                }
                if (b.none) {
                    return 1;
                }

                return (
                    -$WH.strcmp(a.gearscore, b.gearscore) ||
                    $WH.strcmp(a.name, b.name)
                );
            }
        },
        {
            id: 'enchantment',
            type: 'text',
            align: 'left',
            value: 'enchantment',
            compute: function (enchant, td, tr) {
                if (enchant.none) {
                    return;
                }

                var d = $WH.ce('div');
                d.className = 'small crop';
                td.title = enchant.enchantment;
                $WH.ae(d, $WH.ct(enchant.enchantment));
                $WH.ae(td, d);
            },
            sortFunc: function (a, b, col) {
                if (a.none) {
                    return -1;
                }
                if (b.none) {
                    return 1;
                }

                return (
                    -$WH.strcmp(a.score, b.score) ||
                    $WH.strcmp(a.name, b.name)
                );
            }
        },
        {
            id: 'skill',
            type: 'text',
            compute: function (enchant, td, tr) {
                if (enchant.none) {
                    return;
                }

                td.className = 'small q0';
                $WH.nw(td);

                if (enchant.skill > 0) {
                    return g_spell_skills[enchant.skill];
                }
                else {
                    return LANG.types[3][0];
                }
            },
            getVisibleText: function (enchant) {
                if (enchant.none) {
                    return;
                }

                if (enchant.skill > 0) {
                    return g_spell_skills[enchant.skill];
                }
                else {
                    return LANG.types[3][0];
                }
            }
        },
        {
            id: 'score',
            align: 'center',
            compute: function (enchant, td, tr) {
                if (enchant.none) {
                    $WH.ee(tr);

                    $(tr).click($WowheadProfiler.enchantItem.bind(this, 0));
                    td.colSpan = 5;
                    td.style.fontWeight = 'bold';
                    td.style.textAlign = 'center';
                    return LANG.dash + LANG.pr_noneenchant + LANG.dash;
                }

                if (enchant.score != null) {
                    var
                        n = parseFloat(enchant.score),
                        s = $WH.ce('div');

                    s.className = 'small q' + (n ? (n > 0 ? 2 : 10) : 0);
                    $WH.st(s, (n ? (n > 0 ? '+' : '-') + enchant.score: 0));
                    $WH.ae(td, s);
                }
            }
        }
    ]
};

Listview.templates.petpicker = {
    sort: [1],
    nItemsPerPage: -1,
    hideBands: 2,
    hideNav: 1 | 2,
    hideHeader: 1,
    searchable: 1,
    searchDelay: 100,
    poundable: 0,
    filtrable: 0,
    clip: {
        w: 780,
        h: 486
    },

    onBeforeCreate: function () {},

    onSearchSubmit: function (pet, n) {
        if (this.nRowsVisible != 2 || !pet.id) { // None
            return;
        }

        $WowheadProfiler.selectPet(pet.id);
    },

    columns: [
        {
            id: 'pet',
            type: 'text',
            align: 'left',
            value: 'name',
            span: 2,
            compute: function (pet, td, tr) {
                if (pet.none) {
                    return;
                }

                var url = '?npc=' + pet.id;

                var i = $WH.ce('td');
                i.style.width = '1px';
                i.style.padding = '0';
                i.style.borderRight = 'none';

                var
                    icon = Icon.create(pet.icon, 0, null, url),
                    link = Icon.getLink(icon);

                $WH.ae(i, icon);
                $WH.ae(tr, i);
                td.style.borderLeft = 'none';

                link.onclick = $WH.rf;

                var a = $WH.ce('a');
                a.style.fontFamily = 'Verdana, sans-serif';
                a.href = url;

                $WH.ae(a, $WH.ct(pet.name));

                $WH.nw(td);
                $WH.ae(td, a);

                $(tr).click(function (e) {
                    if (e.which != 2 || e.target != a) {
                        e.preventDefault();
                        ($WowheadProfiler.selectPet.bind(this, pet.id))();
                    }
                });
            },
            sortFunc: function (a, b, col) {
                if (a.none) {
                    return -1;
                }

                if (b.none) {
                    return 1;
                }

                if (a.family != b.family) {
                    return $WH.strcmp(g_pet_families[a.family], g_pet_families[b.family]);
                }

                if (a.skin != b.skin) {
                    return $WH.strcmp(a.skin, b.skin);
                }

                return $WH.strcmp(a.name, b.name);
            }
        },
        {
            id: 'skin',
            type: 'text',
            value: 'skin',
            compute: function (pet, td, tr) {
                if (pet.none) {
                    return;
                }

                var d = $WH.ce('div');
                d.className = 'small crop';
                td.title = pet.skin;
                $WH.ae(d, $WH.ct(pet.skin));
                $WH.ae(td, d);
            }
        },
        {
            id: 'level',
            type: 'range',
            getMinValue: function (pet) {
                return pet.minlevel;
            },
            getMaxValue: function (pet) {
                return pet.maxlevel;
            },
            compute: function (pet, td, tr) {
                if (pet.none) {
                    $WH.ee(tr);

                    $(tr).click($WowheadProfiler.selectPet.bind(this, 0));
                    td.colSpan = 4;
                    td.style.fontWeight = 'bold';
                    td.style.textAlign = 'center';
                    return LANG.dash + LANG.pr_nonepet + LANG.dash;
                }

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
            }
        }
    ]
};

Dialog.templates.profileredit = {

    title: LANG.pr_dialog_chardetails,
    width: 400,
    buttons: [['okay', LANG.ok], ['cancel', LANG.cancel]],

    fields:
    [
        {
            id: 'savewarning',
            type: 'caption',
                compute: function(field, value, form, td) {
                if (this.data.region && this.data.region[0] &&
                    this.data.battlegroup && this.data.battlegroup[0] &&
                    this.data.realm && this.data.realm[0] &&
                    !this.data.user)
                {
                    td.style.whiteSpace = 'normal';
                    td.style.lineHeight = 'normal';
                    td.style.paddingBottom = '10px';
                    $WH.st(td, LANG.dialog_losechanges);
                }
            }
        },
        {
            id: 'name',
            type: 'text',
            label: LANG.pr_dialog_name,
            size: 30,
            required: 1,
            validate: function (value, data) {
                if (value.match(/`~!@#$%^&*()_\-+={[}\]|:;"'<,>.?\/]/)) {
                    alert(LANG.message_saveasinvalidname);
                    return false;
                }
                return true;
            }
        },
        {
            id: 'level',
            type: 'select',
            label: LANG.pr_dialog_level,
            options: g_createRange(10, 80),
            value: 80,
            sort: -1,
            compute: function(field, value, form) {
                field.onchange = this.updateIcon;
            }
        },
        {
            id: 'race',
            type: 'select',
            label: LANG.pr_dialog_race,
            options: g_chr_races,
            sort: 1,
            compute: function(field, value, form) {
                field.onchange = this.updateIcon;
            }
        },
        {
            id: 'classs',
            type: 'select',
            label: LANG.pr_dialog_class,
            options: g_chr_classes,
            sort: 1,
                compute: function(field, value, form) {
                    field.onchange = this.updateIcon;
                },
            validate: function (value, data) {
                if (value != 6 || data.level >= 55) { // Death Knight
                    return true;
                }
                else {
                    alert(LANG.message_invalidlevel);

                    return false;
                }
            }
        },
        {
            id: 'gender',
            type: 'radio',
            label: LANG.pr_dialog_gender,
            options: { 0 : LANG.male, 1 : LANG.female },
            compute: function(field, value, form) {
                field.onclick = this.updateIcon;
            }
        },
        {
            id: 'icon',
            type: 'text',
            label: LANG.pr_dialog_icon,
            caption: LANG.pr_dialog_iconeg,
            size: 30,
            required: 1,
                compute: function(field, value, form, td) {
                var wrapper = $WH.ce('div');
                wrapper.style.position = 'relative';

                var divIcon = $WH.ce('div');
                divIcon.style.position = 'absolute';
                divIcon.style.left = '151px';
                divIcon.style.top = '-71px';

                var btn = this.btnPreview = $WH.ce('input');
                btn.type = 'button';
                btn.value = LANG.preview;
                btn.onclick = function () {
                    field.value = $WH.trim(field.value);

                    $WH.ee(divIcon);
                    $WH.ae(divIcon, Icon.create(field.value, 2));
                };

                var _self = this;
                field.onchange = function () {
                    _self.data.defaulticon = false;
                };

                $WH.ae(wrapper, field);
                $WH.ae(wrapper, btn);
                $WH.ae(wrapper, divIcon);
                $WH.ae(td, wrapper);
            }
        },
        {
            id: 'description',
            type: 'textarea',
            label: LANG.pr_dialog_description,
            width: '98%',
            size: [5, 30]
        },
        {
            id: 'published',
            type: 'radio',
            label: LANG.pr_dialog_public,
            options: { 0 : LANG.privateprofile, 1 : LANG.publicprofile }
        }
    ],

    onInit: function(form) {
        this.updateIcon = this.template.updateIcon.bind(this, form);
    },

    onShow: function() {
        this.updateIcon();
    },

    updateIcon: function(form) {
        if (this.data.defaulticon) {
            var
                r = this.getSelectedValue('race'),
                c = this.getSelectedValue('classs'),
                g = this.getCheckedValue('gender'),
                l = this.getSelectedValue('level');

            this.setValue('icon', $WH.g_getProfileIcon(r, c, g, l));
        }

        this.btnPreview.onclick();
    }
};

Dialog.templates.quickexclude = {

    title: LANG.dialog_manageexclusions,
    width: 480,
    buttons: [['okay', LANG.ok], ['cancel', LANG.cancel]],

    fields:
    [
        {
            id: 'excludeexplain',
            type: 'caption',
            compute: function (field, value, form, td, tr) {
                td.style.whiteSpace = 'normal';
                td.style.lineHeight = '1.4em';

                td.innerHTML = LANG.dialog_exclude;

                var d = $WH.ce('div');
                d.className = 'pad';
                $WH.ae(td, d);
            }
        },
        {
            id: 'miniheader',
            type: 'caption',
            compute: function (field, value, form, td, tr) {
                $WH.ae(tr, $WH.ce('td'));
                td.setAttribute('colSpan', null);

                var
                    l = $WH.ce('label'),
                    b = $WH.ce('b');

                $WH.st(b, LANG.button_exclude);
                $WH.ae(l, b);
                $WH.ae(td, l);

                l = $WH.ce('label');
                b = $WH.ce('b');

                $WH.st(b, LANG.button_include);
                $WH.ae(l, b);
                $WH.ae(td, l);
            }
        },
        {
            id: 'noquickexcl',
            type: 'caption',
                compute: function (field, value, form, td, tr) {
                td.style.whiteSpace = 'normal';
                td.style.lineHeight = '1.4em';

                td.innerHTML = LANG.pr_tt_noqexcl;

                var d = $WH.ce('div');
                d.className = 'pad';
                $WH.ae(td, d);
            }
        },
        {
            id: 1,
            label: LANG.dialog_notavail
        },
        {
            id: 2,
            label: LANG.dialog_tcg
        },
        {
            id: 4,
            label: LANG.dialog_collector
        },
        {
            id: 8,
            label: LANG.dialog_promo
        },
        {
            id: 16,
            label: LANG.dialog_nonus
        },
        {
            id: 96,
            label: LANG.dialog_faction
        },
        {
            id: 896,
            label: LANG.dialog_profession
        },
        {
            id: 1024,
            label: LANG.dialog_noexalted
        },
        {
            id: 'resetall',
            type: 'caption',
            compute: function(field, value, form, td, tr) {
                td.style.textAlign = 'center';

                var d = $WH.ce('div');
                d.className = 'pad';
                $WH.ae(td, d);

                var a = $WH.ce('a');
                a.href = 'javascript:;';
                a.onclick = $WowheadProfiler.resetExclusions;
                $WH.st(a, LANG.dialog_resetexclusions);
                $WH.ae(td, a);
            }
        }
    ],

    onInit: function (form, opt) {
        var
            fields = [],
            temp = [];

        for (var i = 0, len = this.template.fields.length; i < len; ++i) {
            var field = this.template.fields[i];

            if (isNaN(field.id)) { // Caption
                fields = fields.concat(temp.sort(function (a, b) {
                    return $WH.strcmp(a.label, b.label);
                }));
                temp = [];

                fields.push(field);
            }
            else {
                $WH.cO(field, {
                    type: 'radio',
                    noInputBr: 1,
                    options: { 2 : '', 1 : '' },
                    optorder: [2, 1]
                });

                temp.push(field);
            }
        }

        fields = fields.concat(temp.sort(function (a, b) {
            return $WH.strcmp(a.label, b.label);
        }));

        this.template.fields = fields;
    },

    onBeforeShow: function (form) {
        $WH.gE(form, 'table')[0].className = 'profiler-exclusion';

        var
            j = 0,
            ngroups = 0;
        for (var i = 0, len = this.template.fields.length; i < len; ++i) {
            var field = this.template.fields[i];

            if (!isNaN(field.id)) {
                this.data[field.id] = (g_user.excludegroups & field.id ? 2 : 1);

                field.__tr.firstChild.style.textAlign = 'left';
                field.__tr.style.display = (this.data.empty(field.id) ? 'none' : '');

                if (!this.data.empty(field.id)) {
                    if (j++ % 2) {
                        field.__tr.style.backgroundColor = '#202020';
                    }
                    ngroups++;
                }
            }
            else {
                j = 0;
            }
        }

        this.template.fields[1].__tr.style.display = (ngroups ? '' : 'none');
        this.template.fields[2].__tr.style.display = (ngroups ? 'none' : '');
    }
};
