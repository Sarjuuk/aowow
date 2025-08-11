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
    "-1":  'spell_shadow_sacrificialshield',
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

function g_getPatchVersionIndex(timestamp)
{
    var _ = g_getPatchVersion;
    var l = 0, u = _.T.length - 2, m;

    while (u > l)
    {
        m = Math.floor((u + l) / 2);

        if (timestamp >= _.T[m] && timestamp < _.T[m + 1])
            return m;

        if (timestamp >= _.T[m])
            l = m + 1;
        else
            u = m - 1;
    }
    m = Math.ceil((u + l) / 2);

    return m;
}

function g_getPatchVersion(timestamp)
{
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

// javascript:void(prompt('', new Date('Aug 30 2011').getTime()))

g_getPatchVersion.T = [
    // 1.12: Drums of War
    1153540800000, // 1.12.0    22 August 2006
    1159243200000, // 1.12.1    26 September 2006
    1160712000000, // 1.12.2    13 October 2006

    // 2.0: Before the Storm (The Burning Crusade)
    1165294800000, // 2.0.1        5 December 2006
    1168318800000, // 2.0.3        9 January 2007
    1168578000000, // 2.0.4        12 January 2007
    1168750800000, // 2.0.5        14 January 2007
    1169528400000, // 2.0.6        23 January 2007
    1171342800000, // 2.0.7        13 February 2007
    1171602000000, // 2.0.8        16 February 2007
    1173157200000, // 2.0.10    6 March 2007
    1175572800000, // 2.0.12    3 April 2007

    // 2.1: The Black Temple
    1179806400000, // 2.1.0        22 May 2007
    1181016000000, // 2.1.1        5 June 2007
    1182225600000, // 2.1.2        19 June 2007
    1184040000000, // 2.1.3        10 July 2007

    // 2.2: Voice Chat!
    1190692800000, // 2.2.0        25 September 2007
    1191297600000, // 2.2.2        2 October 2007
    1191902400000, // 2.2.3        9 October 2007

    // 2.3: The Gods of Zul'Aman
    1194930000000, // 2.3.0        13 November 2007
    1199768400000, // 2.3.2        08 January 2008
    1200978000000, // 2.3.3        22 January 2008

    // 2.4: Fury of the Sunwell
    1206417600000, // 2.4.0        25 March 2008
    1207022400000, // 2.4.1        1 April 2008
    1210651200000, // 2.4.2        13 May 2008
    1216094400000, // 2.4.3        15 July 2008

    // 3.0: Echoes of Doom
    1223956800000, // 3.0.2        October 14 2008
    1225774800000, // 3.0.3        November 4 2008
    1232427600000, // 3.0.8        January 20 2009
    1234242000000, // 3.0.9        February 10 2009

    // 3.1: Secrets of Ulduar
    1239681600000, // 3.1.0        April 14 2009
    1240286400000, // 3.1.1        April 21 2009
    1242705600000, // 3.1.2        19 May 2009
    1243915200000, // 3.1.3        2 June 2009

    // 3.2: Call of the Crusader
    1249358400000, // 3.2.0        4 August 2009
    1253595600000, // 3.2.2        22 September 2009

    // 3.3: Fall of the Lich King
    1260266400000, // 3.3.0        8 December 2009
    1265104800000, // 3.3.2        2 February 2010
    1269320400000, // 3.3.3        23 March 2010
    1277182800000, // 3.3.5        22 June 2010

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
   300: 'guide',
   501: 'emote',
   502: 'enchantment',
   503: 'areatrigger',
   504: 'mail'
};

// Items
$WH.cO(g_items, {
    add: function(id, json)
    {
        if (g_items[id] != null)
            $WH.cO(g_items[id], json);
        else
            g_items[id] = json;
    },
    getIcon: function(id)
    {
        if (g_items[id] != null && g_items[id].icon)
            return g_items[id].icon;
        else
            return 'inv_misc_questionmark';
    },
    createIcon: function(id, size, num, qty)
    {
        return Icon.create(g_items.getIcon(id), size, null, '?item=' + id, num, qty);
    }
});

// Spells
$WH.cO(g_spells, {
    add: function(id, json)
    {
        if (g_spells[id] != null)
            $WH.cO(g_spells[id], json);
        else
            g_spells[id] = json;
    },
    getIcon: function(id)
    {
        if (g_spells[id] != null && g_spells[id].icon)
            return g_spells[id].icon;
        else
            return 'inv_misc_questionmark';
    },
    createIcon: function(id, size, num, qty)
    {
        return Icon.create(g_spells.getIcon(id), size, null, '?spell=' + id, num, qty);
    }
});

// Achievements
$WH.cO(g_achievements, {
    getIcon: function(id)
    {
        if (g_achievements[id] != null && g_achievements[id].icon)
            return g_achievements[id].icon;
        else
            return 'inv_misc_questionmark';
    },
    createIcon: function(id, size, num, qty)
    {
        return Icon.create(g_achievements.getIcon(id), size, null, '?achievement=' + id, num, qty);
    }
});

// Classes
$WH.cO(g_classes, {
    getIcon: function(id)
    {
        if (g_file_classes[id])
            return 'class_' + g_file_classes[id];
        else
            return 'inv_misc_questionmark';
    },
    createIcon: function(id, size, num, qty)
    {
        return Icon.create(g_classes.getIcon(id), size, null, '?class=' + id, num, qty);
    }
});

// Races
$WH.cO(g_races, {
    getIcon: function(id, gender)
    {
        if (gender === undefined)
            gender = 0
        if (g_file_races[id] && g_file_genders[gender])
            return 'race_' + g_file_races[id] + '_' + g_file_genders[gender];
        else
            return 'inv_misc_questionmark';
    },
    createIcon: function(id, size, num, qty)
    {
        return Icon.create(g_races.getIcon(id), size, null, '?race=' + id, num, qty);
    }
});

// Skills
$WH.cO(g_skills, {
    getIcon: function(id)
    {
        if (g_skills[id] != null && g_skills[id].icon)
            return g_skills[id].icon;
        else
            return 'inv_misc_questionmark';
    },
    createIcon: function(id, size, num, qty)
    {
        return Icon.create(g_skills.getIcon(id), size, null, '?skill=' + id, num, qty);
    }
});

// Currencies
$WH.cO(g_gatheredcurrencies, {
    getIcon: function(id, side)
    {
        if (g_gatheredcurrencies[id] != null && g_gatheredcurrencies[id].icon)
        {
            if ($WH.is_array(g_gatheredcurrencies[id].icon) && !isNaN(side))
                return g_gatheredcurrencies[id].icon[side];
            return g_gatheredcurrencies[id].icon;
        }
        else
            return 'inv_misc_questionmark';
    },
    createIcon: function(id, size, num, qty)
    {
        return Icon.create(g_gatheredcurrencies.getIcon(id, (num > 0 ? 0 : 1)), size, null, null, Math.abs(num), qty);
    }
});

// Holidays
$WH.cO(g_holidays, {
    getIcon: function(id)
    {
        if (g_holidays[id] != null && g_holidays[id].icon)
            return g_holidays[id].icon;
        else
            return 'inv_misc_questionmark';
    },
    createIcon: function(id, size, num, qty)
    {
        return Icon.create(g_holidays.getIcon(id), size, null, '?event=' + id, num, qty);
    }
});

// TODO: Move to g_items
function g_getIngameLink(color, id, name)
{
    //prompt(LANG.prompt_ingamelink, '/script DEFAULT_CHAT_FRAME:AddMessage("\\124c' + color + '\\124H' + id + '\\124h[' + name + ']\\124h\\124r");');
    return '/script DEFAULT_CHAT_FRAME:AddMessage("\\124c' + color + '\\124H' + id + '\\124h[' + name + ']\\124h\\124r");';
}
