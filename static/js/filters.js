var fi_type = null;
var fi_weights = null;
var fi_weightsFactor;
var fi_nExtraCols;
var fi_gemScores;

var fi_filters = {

    items: [
        { id: 1,   name: 'sepgeneral' },
        { id: 2,   name: 'bindonpickup',        type: 'yn' },
        { id: 3,   name: 'bindonequip',         type: 'yn' },
        { id: 4,   name: 'bindonuse',           type: 'yn' },
        { id: 133, name: 'bindtoaccount',       type: 'yn' },
        { id: 146, name: 'heroicitem',          type: 'yn' },
        { id: 107, name: 'effecttext',          type: 'str' },
        { id: 81,  name: 'fitsgemslot',         type: 'gem' },
        { id: 132, name: 'glyphtype',           type: 'glyphtype' },
        { id: 80,  name: 'hassockets',          type: 'gem' },
        { id: 151, name: 'id',                  type: 'num', before: 'name', noweights: 1 },
        { id: 100, name: 'nsockets',            type: 'num', noweights: 1 },
        { id: 124, name: 'randomenchants',      type: 'str' },
        { id: 125, name: 'reqarenartng',        type: 'num', noweights: 1 },
        { id: 111, name: 'reqskillrank',        type: 'num', noweights: 1 },
        { id: 99,  name: 'requiresprof',        type: 'profession' },
        { id: 66,  name: 'requiresprofspec',    type: 'profession' },
        { id: 17,  name: 'requiresrepwith',     type: 'faction-any+none' },
        { id: 169, name: 'requiresevent',       type: 'event-any+none' },
        { id: 160, name: 'relatedevent',        type: 'event-any+none' },
        { id: 168, name: 'teachesspell',        type: 'yn' },
        { id: 15,  name: 'unique',              type: 'yn' },
        { id: 83,  name: 'uniqueequipped',      type: 'yn' },
        { id: 152, name: 'classspecific',       type: 'classs' },
        { id: 153, name: 'racespecific',        type: 'race' },

        { id: 19,  name: 'sepbasestats' },
        { id: 21,  name: 'agi',                 type: 'num' },
        { id: 23,  name: 'int',                 type: 'num' },
        { id: 22,  name: 'sta',                 type: 'num' },
        { id: 24,  name: 'spi',                 type: 'num' },
        { id: 20,  name: 'str',                 type: 'num' },
        { id: 115, name: 'health',              type: 'num' },
        { id: 116, name: 'mana',                type: 'num' },
        { id: 60,  name: 'healthrgn',           type: 'num' },
        { id: 61,  name: 'manargn',             type: 'num' },

        { id: 120, name: 'sepdefensivestats' },
        { id: 41,  name: 'armor',               type: 'num' },
        { id: 44,  name: 'blockrtng',           type: 'num' },
        { id: 43,  name: 'block',               type: 'num' },
        { id: 42,  name: 'defrtng',             type: 'num' },
        { id: 45,  name: 'dodgertng',           type: 'num' },
        { id: 46,  name: 'parryrtng',           type: 'num' },
        { id: 79,  name: 'resirtng',            type: 'num' },

        { id: 31,  name: 'sepoffensivestats' },
        { id: 77,  name: 'atkpwr',              type: 'num' },
        { id: 97,  name: 'feratkpwr',           type: 'num', indent: 1 },
        { id: 114, name: 'armorpenrtng',        type: 'num' },
        { id: 96,  name: 'critstrkrtng',        type: 'num' },
        { id: 117, name: 'exprtng',             type: 'num' },
        { id: 103, name: 'hastertng',           type: 'num' },
        { id: 119, name: 'hitrtng',             type: 'num' },
        { id: 94,  name: 'splpen',              type: 'num' },
        { id: 123, name: 'splpwr',              type: 'num' },
        { id: 52,  name: 'arcsplpwr',           type: 'num', indent: 1 },
        { id: 53,  name: 'firsplpwr',           type: 'num', indent: 1 },
        { id: 54,  name: 'frosplpwr',           type: 'num', indent: 1 },
        { id: 55,  name: 'holsplpwr',           type: 'num', indent: 1 },
        { id: 56,  name: 'natsplpwr',           type: 'num', indent: 1 },
        { id: 57,  name: 'shasplpwr',           type: 'num', indent: 1 },

        { id: 122, name: 'sepweaponstats' },
        { id: 32,  name: 'dps',                 type: 'num' },
        { id: 35,  name: 'damagetype',          type: 'resistance' },
        { id: 33,  name: 'dmgmin1',             type: 'num' },
        { id: 34,  name: 'dmgmax1',             type: 'num' },
        { id: 36,  name: 'speed',               type: 'num' },
        { id: 134, name: 'mledps',              type: 'num' },
        { id: 135, name: 'mledmgmin',           type: 'num' },
        { id: 136, name: 'mledmgmax',           type: 'num' },
        { id: 137, name: 'mlespeed',            type: 'num' },
        { id: 138, name: 'rgddps',              type: 'num' },
        { id: 139, name: 'rgddmgmin',           type: 'num' },
        { id: 140, name: 'rgddmgmax',           type: 'num' },
        { id: 141, name: 'rgdspeed',            type: 'num' },

        { id: 121, name: 'sepresistances' },
        { id: 25,  name: 'arcres',              type: 'num' },
        { id: 26,  name: 'firres',              type: 'num' },
        { id: 28,  name: 'frores',              type: 'num' },
        { id: 30,  name: 'holres',              type: 'num' },
        { id: 27,  name: 'natres',              type: 'num' },
        { id: 29,  name: 'shares',              type: 'num' },

        { id: 67,  name: 'sepsource' },
        { id: 86,  name: 'craftedprof',             type: 'profession' },
        { id: 16,  name: 'dropsin',                 type: 'zone' },
        { id: 105, name: 'dropsinnormal',           type: 'heroicdungeon-any' },
        { id: 106, name: 'dropsinheroic',           type: 'heroicdungeon-any' },
        { id: 147, name: 'dropsinnormal10',         type: 'multimoderaid-any' },
        { id: 148, name: 'dropsinnormal25',         type: 'multimoderaid-any' },
        { id: 149, name: 'dropsinheroic10',         type: 'heroicraid-any' },
        { id: 150, name: 'dropsinheroic25',         type: 'heroicraid-any' },
        { id: 68,  name: 'otdisenchanting',         type: 'yn' },
        { id: 69,  name: 'otfishing',               type: 'yn' },
        { id: 70,  name: 'otherbgathering',         type: 'yn' },
        { id: 71,  name: 'otitemopening',           type: 'yn' },
        { id: 72,  name: 'otlooting',               type: 'yn' },
        { id: 143, name: 'otmilling',               type: 'yn' },
        { id: 73,  name: 'otmining',                type: 'yn' },
        { id: 74,  name: 'otobjectopening',         type: 'yn' },
        { id: 75,  name: 'otpickpocketing',         type: 'yn' },
        { id: 88,  name: 'otprospecting',           type: 'yn' },
        { id: 93,  name: 'otpvp',                   type: 'pvp' },
        { id: 171, name: 'otredemption',            type: 'yn' },
        { id: 76,  name: 'otskinning',              type: 'yn' },
        { id: 158, name: 'purchasablewithcurrency', type: 'currency-any' },
        { id: 118, name: 'purchasablewithitem',     type: 'itemcurrency-any' },
        { id: 144, name: 'purchasablewithhonor',    type: 'yn' },
        { id: 145, name: 'purchasablewitharena',    type: 'yn' },
        { id: 18,  name: 'rewardedbyfactionquest',  type: 'side' },
        { id: 126, name: 'rewardedbyquestin',       type: 'zone-any' },
        { id: 172, name: 'rewardedbyachievement',   type: 'yn' },
        { id: 92,  name: 'soldbyvendor',            type: 'yn' },
        { id: 129, name: 'soldbynpc',               type: 'str-small' },
        { id: 128, name: 'sepsource',               type: 'itemsource' },

        { id: 47,  name: 'sepindividualstats' },
        { id: 37,  name: 'mleatkpwr',           type: 'num' },
        { id: 84,  name: 'mlecritstrkrtng',     type: 'num' },
        { id: 78,  name: 'mlehastertng',        type: 'num' },
        { id: 95,  name: 'mlehitrtng',          type: 'num' },
        { id: 38,  name: 'rgdatkpwr',           type: 'num' },
        { id: 40,  name: 'rgdcritstrkrtng',     type: 'num' },
        { id: 101, name: 'rgdhastertng',        type: 'num' },
        { id: 39,  name: 'rgdhitrtng',          type: 'num' },
        { id: 49,  name: 'splcritstrkrtng',     type: 'num' },
        { id: 102, name: 'splhastertng',        type: 'num' },
        { id: 48,  name: 'splhitrtng',          type: 'num' },
        { id: 51,  name: 'spldmg',              type: 'num' },
        { id: 50,  name: 'splheal',             type: 'num' },

        { id: 58,  name: 'sepmisc' },
        { id: 109, name: 'armorbonus',              type: 'num' },
        { id: 161, name: 'availabletoplayers',      type: 'yn' },
        { id: 90,  name: 'avgbuyout',               type: 'num', noweights: 1 },
        { id: 65,  name: 'avgmoney',                type: 'num', noweights: 1 },
        { id: 9,   name: 'conjureditem',            type: 'yn' },
        { id: 62,  name: 'cooldown',                type: 'num', noweights: 1 },
        { id: 162, name: 'deprecated',              type: 'yn' },
        { id: 8,   name: 'disenchantable',          type: 'yn' },
        { id: 163, name: 'disenchantsinto',         type: 'disenchanting' },
        { id: 59,  name: 'dura',                    type: 'num', noweights: 1 },
        { id: 104, name: 'flavortext',              type: 'str' },
        { id: 7,   name: 'hasflavortext',           type: 'yn' },
        { id: 142, name: 'icon',                    type: 'str' },
        { id: 10,  name: 'locked',                  type: 'yn' },
        { id: 159, name: 'millable',                type: 'yn' },
     // { id: 127, name: 'notavailable',            type: 'yn' },       redundant with 161
        { id: 85,  name: 'objectivequest',          type: 'side' },
        { id: 11,  name: 'openable',                type: 'yn' },
        { id: 12,  name: 'partofset',               type: 'yn' },
        { id: 98,  name: 'partyloot',               type: 'yn' },
        { id: 89,  name: 'prospectable',            type: 'yn' },
        { id: 5,   name: 'questitem',               type: 'yn' },
        { id: 13,  name: 'randomlyenchanted',       type: 'yn' },
        { id: 14,  name: 'readable',                type: 'yn' },
        { id: 87,  name: 'reagentforability',       type: 'profession' },
        { id: 63,  name: 'buyprice',                type: 'num', noweights: 1 },
        { id: 154, name: 'refundable',              type: 'yn' },
        { id: 165, name: 'repaircost',              type: 'num' },
        { id: 64,  name: 'sellprice',               type: 'num', noweights: 1 },
        { id: 157, name: 'smartloot',               type: 'yn' },
        { id: 6,   name: 'startsquest',             type: 'side' },
        { id: 91,  name: 'tool',                    type: 'totemcategory' },
        { id: 155, name: 'usableinarenas',          type: 'yn' },
        { id: 156, name: 'usablewhenshapeshifted',  type: 'yn' },

        { id: 112, name: 'sepcommunity' },
        { id: 130, name: 'hascomments',         type: 'yn' },
        { id: 113, name: 'hasscreenshots',      type: 'yn' },
        { id: 167, name: 'hasvideos',           type: 'yn' },

        { id: 1,   name: 'sepstaffonly',                       staffonly: true },
        { id: 176, name: 'flags',               type: 'flags', staffonly: true },
        { id: 177, name: 'flags2',              type: 'flags', staffonly: true }
    ],

    itemsets: [
        { id: 9999,name: 'sepgeneral' },
        { id: 12,  name: 'availabletoplayers',  type: 'yn' },
        { id: 3,   name: 'pieces',              type: 'num' },
        { id: 4,   name: 'bonustext',           type: 'str' },
        { id: 5,   name: 'heroic',              type: 'yn' },
        { id: 6,   name: 'relatedevent',        type: 'event-any+none' },
        { id: 2,   name: 'id',                  type: 'num', before: 'name' },

        { id: 9999,name: 'sepcommunity' },
        { id:  8,  name: 'hascomments',         type: 'yn' },
        { id:  9,  name: 'hasscreenshots',      type: 'yn' },
        { id: 10,  name: 'hasvideos',           type: 'yn' }
    ],

    npcs: [
        { id: 4,   name: 'sepgeneral' },
        { id: 5,   name: 'canrepair',           type: 'yn' },
        { id: 3,   name: 'faction',             type: 'faction' },
        { id: 6,   name: 'foundin',             type: 'zone' },
        { id: 1,   name: 'health',              type: 'num' },
        { id: 2,   name: 'mana',                type: 'num' },
        { id: 32,  name: 'instanceboss',        type: 'yn' },
        { id: 38,  name: 'relatedevent',        type: 'event-any+none' },
        { id: 7,   name: 'startsquest',         type: 'side' },
        { id: 8,   name: 'endsquest',           type: 'side' },
        { id: 34,  name: 'usemodel',            type: 'str-small' },
        { id: 35,  name: 'useskin',             type: 'str' },
        { id: 37,  name: 'id',                  type: 'num', before: 'name' },

        { id: 14,  name: 'seploot' },
        { id: 12,  name: 'averagemoneydropped', type: 'num' },
        { id: 43,  name: 'decreasesrepwith',    type: 'faction' },
        { id: 42,  name: 'increasesrepwith',    type: 'faction' },
        { id: 15,  name: 'gatherable',          type: 'yn' },
        { id: 44,  name: 'salvageable',         type: 'yn' },
        { id: 9,   name: 'lootable',            type: 'yn' },
        { id: 16,  name: 'minable',             type: 'yn' },
        { id: 11,  name: 'pickpocketable',      type: 'yn' },
        { id: 10,  name: 'skinnable',           type: 'yn' },

        { id: 17,  name: 'sepgossipoptions' },
        { id: 18,  name: 'auctioneer',          type: 'yn' },
        { id: 19,  name: 'banker',              type: 'yn' },
        { id: 20,  name: 'battlemaster',        type: 'yn' },
        { id: 21,  name: 'flightmaster',        type: 'yn' },
        { id: 22,  name: 'guildmaster',         type: 'yn' },
        { id: 23,  name: 'innkeeper',           type: 'yn' },
        { id: 24,  name: 'talentunlearner',     type: 'yn' },
        { id: 25,  name: 'tabardvendor',        type: 'yn' },
        { id: 27,  name: 'stablemaster',        type: 'yn' },
        { id: 28,  name: 'trainer',             type: 'yn' },
        { id: 29,  name: 'vendor',              type: 'yn' },

        { id: 30,  name: 'sepcommunity' },
        { id: 33,  name: 'hascomments',         type: 'yn' },
        { id: 31,  name: 'hasscreenshots',      type: 'yn' },
        { id: 40,  name: 'hasvideos',           type: 'yn' },

        { id: 999, name: 'sepstaffonly',                    staffonly: true },
        { id: 41,  name: 'haslocation',         type: 'yn', staffonly: true }
    ],

    objects: [
        { id: 8,   name: 'sepgeneral' },
        { id: 1,   name: 'foundin',             type: 'zone' },
        { id: 15,  name: 'id',                  type: 'num', before: 'name' },
        { id: 16,  name: 'relatedevent',        type: 'event-any+none' },
        { id: 7,   name: 'requiredskilllevel',  type: 'num' },
        { id: 2,   name: 'startsquest',         type: 'side' },
        { id: 3,   name: 'endsquest',           type: 'side' },
        { id: 50,  name: 'spellfocus',          type: 'spellfocus-any+none' },

        { id: 9,   name: 'seploot' },
        { id: 5,   name: 'averagemoneycontained',   type: 'num' },
        { id: 4,   name: 'openable',                type: 'yn' },

        { id: 10,  name: 'sepcommunity' },
        { id: 13,  name: 'hascomments',         type: 'yn' },
        { id: 11,  name: 'hasscreenshots',      type: 'yn' },
        { id: 18,  name: 'hasvideos',           type: 'yn' }
    ],

    quests: [
        { id: 12,  name: 'sepgeneral' },
        { id: 34,  name: 'availabletoplayers',      type: 'yn' },
        { id: 37,  name: 'classspecific',           type: 'classs' },
        { id: 38,  name: 'racespecific',            type: 'race' },
        { id: 27,  name: 'daily',                   type: 'yn' },
        { id: 28,  name: 'weekly',                  type: 'yn' },
        { id: 29,  name: 'repeatable',              type: 'yn' },
        { id: 30,  name: 'id',                      type: 'num', before: 'name' },
        { id: 44,  name: 'countsforloremaster_stc', type: 'yn' },
        { id: 9,   name: 'objectiveearnrepwith',    type: 'faction-any+none' },
        { id: 33,  name: 'relatedevent',            type: 'event-any+none' },
        { id: 5,   name: 'sharable',                type: 'yn' },
        { id: 19,  name: 'startsfrom',              type: 'queststart' },
        { id: 21,  name: 'endsat',                  type: 'questend' },
        { id: 11,  name: 'suggestedplayers',        type: 'num' },
        { id: 6,   name: 'timer',                   type: 'num' },

        { id: 999, name: 'sepstaffonly',                        staffonly: true },
        { id: 42,  name: 'flags',               type: 'flags',  staffonly: true },

        { id: 13,  name: 'sepgainsrewards' },
        { id: 2,   name: 'experiencegained',    type: 'num' },
        { id: 43,  name: 'currencyrewarded',    type: 'currency' },
        { id: 45,  name: 'titlerewarded',       type: 'yn' },
        { id: 23,  name: 'itemchoices',         type: 'num' },
        { id: 22,  name: 'itemrewards',         type: 'num' },
        { id: 3,   name: 'moneyrewarded',       type: 'num' },
        { id: 4,   name: 'spellrewarded',       type: 'yn' },
        { id: 1,   name: 'increasesrepwith',    type: 'faction' },
        { id: 10,  name: 'decreasesrepwith',    type: 'faction' },

        { id: 14,  name: 'sepseries' },
        { id: 7,   name: 'firstquestseries',    type: 'yn' },
        { id: 15,  name: 'lastquestseries',     type: 'yn' },
        { id: 16,  name: 'partseries',          type: 'yn' },

        { id: 17,  name: 'sepcommunity' },
        { id: 25,  name: 'hascomments',         type: 'yn' },
        { id: 18,  name: 'hasscreenshots',      type: 'yn' },
        { id: 36,  name: 'hasvideos',           type: 'yn' },

        { id: 9999,name: 'sepmisc' },
        { id: 24,  name: 'lacksstartend',       type: 'yn'}
    ],

    spells: [
        { id: 9999, name: 'sepgeneral'                                                     },
        { id: 2,    name: 'prcntbasemanarequired',  type: 'num'                            },
        { id: 116,  name: 'onGlobalCooldown',       type: 'yn'                             },
        { id: 28,   name: 'casttime',               type: 'num'                            },
        { id: 27,   name: 'channeled',              type: 'yn'                             },
        { id: 40,   name: 'damagetype',             type: 'damagetype'                     },
        { id: 109,  name: 'effecttype',             type: 'effecttype'                     },
        { id: 29,   name: 'appliesaura',            type: 'effectauranames'                },
        { id: 15,   name: 'icon',                   type: 'str'                            },
        { id: 10,   name: 'firstrank',              type: 'yn'                             },
        { id: 20,   name: 'hasreagents',            type: 'yn'                             },
        { id: 14,   name: 'id',                     type: 'num',            before: 'name' },
        { id: 12,   name: 'lastrank',               type: 'yn'                             },
     // { id: 22,   name: 'proficiencytype',        type: 'proficiencytype'                }, // aowow - not used, pointless in wotlk
        { id: 13,   name: 'rankno',                 type: 'num'                            },
        { id: 3,    name: 'requiresnearbyobject',   type: 'yn'                             },
        { id: 5,    name: 'requiresprofspec',       type: 'yn'                             },
        { id: 114,  name: 'requiresfaction',        type: 'side'                           },
        { id: 1,    name: 'manaenergyragecost',     type: 'num'                            },
        { id: 45,   name: 'resourcetype',           type: 'resourcetype'                   },
        { id: 25,   name: 'rewardsskillups',        type: 'yn'                             },
     // { id: 110,  name: 'scalingap',              type: 'yn'                             }, // aowow - too complex for now
        { id: 19,   name: 'scaling',                type: 'yn'                             },
     // { id: 111,  name: 'scalingsp',              type: 'yn'                             }, // aowow - too complex for now
        { id: 9,    name: 'source',                 type: 'spellsource'                    },
        { id: 4,    name: 'trainingcost',           type: 'num'                            },
        { id: 44,   name: 'usableinarenas',         type: 'yn'                             },
     // { id: 31,   name: 'usablewhenshapeshifted', type: 'yn'                             }, // aowow - not used, pointless in wotlk

        { id: 9999, name: 'sepattributes' },
        { id: 69,   name: 'harmful',                    type: 'yn' },
        { id: 57,   name: 'uncancellableaura',          type: 'yn' },
        { id: 51,   name: 'hiddenaura',                 type: 'yn' },
        { id: 95,   name: 'bandagespell',               type: 'yn' },
        { id: 61,   name: 'usabledead',                 type: 'yn' },
        { id: 62,   name: 'usablemounted',              type: 'yn' },
        { id: 64,   name: 'usablesitting',              type: 'yn' },
        { id: 53,   name: 'daytimeonly',                type: 'yn' },
        { id: 54,   name: 'nighttimeonly',              type: 'yn' },
        { id: 55,   name: 'indoorsonly',                type: 'yn' },
        { id: 56,   name: 'outdoorsonly',               type: 'yn' },
        { id: 79,   name: 'targetonlyplayer',           type: 'yn' },
        { id: 60,   name: 'cannotavoid',                type: 'yn' },
        { id: 67,   name: 'cannotreflect',              type: 'yn' },
        { id: 91,   name: 'notinraid',                  type: 'yn' },
        { id: 33,   name: 'combatcastable',             type: 'yn' },
        { id: 34,   name: 'chancetocrit',               type: 'yn' },
        { id: 35,   name: 'chancetomiss',               type: 'yn' },
        { id: 66,   name: 'channeled',                  type: 'yn' },
        { id: 85,   name: 'auratickswhileloggedout',    type: 'yn' },
        { id: 84,   name: 'nolog',                      type: 'yn' },
        { id: 68,   name: 'usablestealthed',            type: 'yn' },
        { id: 81,   name: 'doesntengagetarget',         type: 'yn' },
        { id: 77,   name: 'doesntreqshapeshift',        type: 'yn' },
     // { id: 46,   name: 'disregardimmunity',          type: 'yn' }, // aowow - unsure what to make of it
        { id: 47,   name: 'disregardschoolimmunity',    type: 'yn' },
        { id: 78,   name: 'foodbuff',                   type: 'yn' },
        { id: 71,   name: 'nothreat',                   type: 'yn' },
        { id: 52,   name: 'onnextswingnpcs',            type: 'yn' },
        { id: 49,   name: 'onnextswingplayers',         type: 'yn' },
        { id: 90,   name: 'onlyarena',                  type: 'yn' },
        { id: 92,   name: 'paladinaura',                type: 'yn' },
        { id: 50,   name: 'passivespell',               type: 'yn' },
        { id: 36,   name: 'persiststhroughdeath',       type: 'yn' },
        { id: 72,   name: 'pickpocket',                 type: 'yn' },
        { id: 73,   name: 'dispelauraonimmunity',       type: 'yn' },
        { id: 48,   name: 'reqrangedweapon',            type: 'yn' },
        { id: 82,   name: 'reqwand',                    type: 'yn' },
        { id: 83,   name: 'reqoffhand',                 type: 'yn' },
        { id: 74,   name: 'reqfishingpole',             type: 'yn' },
        { id: 41,   name: 'requiresmetamorphosis',      type: 'yn' },
        { id: 80,   name: 'reqmainhand',                type: 'yn' },
        { id: 38,   name: 'requiresstealth',            type: 'yn' },
        { id: 75,   name: 'requntappedtarget',          type: 'yn' },
        { id: 58,   name: 'damagedependsonlevel',       type: 'yn' },
        { id: 39,   name: 'spellstealable',             type: 'yn' },
        { id: 63,   name: 'delayedrecoverystarttime',   type: 'yn' },
        { id: 87,   name: 'startstickingatapplication', type: 'yn' },
        { id: 59,   name: 'stopsautoattack',            type: 'yn' },
     // { id: 76,   name: 'targetownitem',              type: 'yn' }, // aowow - e.g. DK weapon enchantments ... this flag for this has to be somewhere....
        { id: 70,   name: 'targetnotincombat',          type: 'yn' },
        { id: 93,   name: 'totemspell',                 type: 'yn' },
        { id: 42,   name: 'usablewhenstunned',          type: 'yn' },
        { id: 88,   name: 'usableconfused',             type: 'yn' },
        { id: 89,   name: 'usablefeared',               type: 'yn' },
        { id: 65,   name: 'usesallpower',               type: 'yn' },

        { id: 9999, name: 'sepcommunity'               },
        { id: 11,   name: 'hascomments',    type: 'yn' },
        { id: 8,    name: 'hasscreenshots', type: 'yn' },
        { id: 17,   name: 'hasvideos',      type: 'yn' },

        { id: 9999, name: 'sepstaffonly',                staffOnly: true },
        { id: 96,   name: 'flags1',       type: 'flags', staffOnly: true },
        { id: 97,   name: 'flags2',       type: 'flags', staffOnly: true },
        { id: 98,   name: 'flags3',       type: 'flags', staffOnly: true },
        { id: 99,   name: 'flags4',       type: 'flags', staffOnly: true },
        { id: 100,  name: 'flags5',       type: 'flags', staffOnly: true },
        { id: 101,  name: 'flags6',       type: 'flags', staffOnly: true },
        { id: 102,  name: 'flags7',       type: 'flags', staffOnly: true },
        { id: 103,  name: 'flags8',       type: 'flags', staffOnly: true },
        { id: 104,  name: 'flags9',       type: 'flags', staffOnly: true },
        { id: 105,  name: 'flags10',      type: 'flags', staffOnly: true },
        { id: 106,  name: 'flags11',      type: 'flags', staffOnly: true },
        { id: 107,  name: 'flags12',      type: 'flags', staffOnly: true },
        { id: 108,  name: 'flags13',      type: 'flags', staffOnly: true }
    ],

    achievements: [
        { id: 1,   name: 'sepgeneral' },
        { id: 2,   name: 'givesreward',         type: 'yn' },
        { id: 3,   name: 'rewardtext',          type: 'str' },
        { id: 4,   name: 'location',            type: 'zone' },
        { id: 10,  name: 'icon',                type: 'str' },
        { id: 9,   name: 'id',                  type: 'num', before: 'name' },
        { id: 11,  name: 'relatedevent',        type: 'event-any+none' },

        { id: 8,   name: 'sepseries' },
        { id: 5,   name: 'firstseries',         type: 'yn' },
        { id: 6,   name: 'lastseries',          type: 'yn' },
        { id: 7,   name: 'partseries',          type: 'yn' },

        { id: 9999,name: 'sepcommunity' },
        { id: 14,  name: 'hascomments',         type: 'yn' },
        { id: 15,  name: 'hasscreenshots',      type: 'yn' },
        { id: 16,  name: 'hasvideos',           type: 'yn' },

        { id: 9999,name: 'sepstaffonly',                        staffonly: true },
        { id: 18,  name: 'flags',               type: 'flags',  staffonly: true }
    ],

    profiles: [
        { id: 1,   name: 'sepgeneral' },
        { id: 2,   name: 'gearscore',               type: 'num' },
        { id: 3,   name: 'achievementpoints',       type: 'num' },
        { id: 21,  name: 'wearingitem',             type: 'str-small' },
        { id: 23,  name: 'completedachievement',    type: 'str-small' },

        { id: 24,  name: 'sepprofession' },
        { id: 25,  name: 'alchemy',             type: 'num' },
        { id: 26,  name: 'blacksmithing',       type: 'num' },
        { id: 27,  name: 'enchanting',          type: 'num' },
        { id: 28,  name: 'engineering',         type: 'num' },
        { id: 29,  name: 'herbalism',           type: 'num' },
        { id: 30,  name: 'inscription',         type: 'num' },
        { id: 31,  name: 'jewelcrafting',       type: 'num' },
        { id: 32,  name: 'leatherworking',      type: 'num' },
        { id: 33,  name: 'mining',              type: 'num' },
        { id: 34,  name: 'skinning',            type: 'num' },
        { id: 35,  name: 'tailoring',           type: 'num' },

        { id: 4,   name: 'septalent' },
        { id: 5,   name: 'talenttree1',         type: 'num' },
        { id: 6,   name: 'talenttree2',         type: 'num' },
        { id: 7,   name: 'talenttree3',         type: 'num' },

        { id: 8,   name: 'sepguild' },
        { id: 36,  name: 'hasguild',            type: 'yn' },
        { id: 9,   name: 'guildname',           type: 'str' },
        { id: 10,  name: 'guildrank',           type: 'num' },

        { id: 11,  name: 'separenateam' },
        { id: 12,  name: 'teamname2v2',         type: 'str' },
        { id: 13,  name: 'teamrtng2v2',         type: 'num' },
        { id: 14,  name: 'teamcontrib2v2',      type: 'num' },
        { id: 15,  name: 'teamname3v3',         type: 'str' },
        { id: 16,  name: 'teamrtng3v3',         type: 'num' },
        { id: 17,  name: 'teamcontrib3v3',      type: 'num' },
        { id: 18,  name: 'teamname5v5',         type: 'str' },
        { id: 19,  name: 'teamrtng5v5',         type: 'num' },
        { id: 20,  name: 'teamcontrib5v5',      type: 'num' }
    ],

    icons: [
        { id: 9999, name: 'sepuses' },
        { id: 13,   name: 'used',                   type: 'num' },
        { id: 1,    name: 'items',                  type: 'num' },
        { id: 2,    name: 'spells',                 type: 'num' },
        { id: 3,    name: 'achievements',           type: 'num' },
     // { id: 4,    name: 'battlepets',             type: 'num' },
     // { id: 5,    name: 'battlepetabilities',     type: 'num' },
        { id: 6,    name: 'currencies',             type: 'num' },
     // { id: 7,    name: 'garrisonabilities',      type: 'num' },
     // { id: 8,    name: 'garrisonbuildings',      type: 'num' },
        { id: 9,    name: 'hunterpets',             type: 'num' },
     // { id: 10,   name: 'garrisonmissionthreats', type: 'num' },
        { id: 11,   name: 'classes',                type: 'num' }
    ],

    // aowow custom
    enchantments: [
        { id: 1,   name: 'sepgeneral' },
        { id: 2,   name: 'id',                  type: 'num', before: 'name' },
        { id: 3,   name: 'requiresprof',        type: 'profession' },
        { id: 4,   name: 'reqskillrank',        type: 'num' },
        { id: 5,   name: 'hascondition',        type: 'yn' },

        { id: 19,  name: 'sepbasestats' },
        { id: 21,  name: 'agi',                 type: 'num' },
        { id: 23,  name: 'int',                 type: 'num' },
        { id: 22,  name: 'sta',                 type: 'num' },
        { id: 24,  name: 'spi',                 type: 'num' },
        { id: 20,  name: 'str',                 type: 'num' },
        { id: 115, name: 'health',              type: 'num' },
        { id: 116, name: 'mana',                type: 'num' },
        { id: 60,  name: 'healthrgn',           type: 'num' },
        { id: 61,  name: 'manargn',             type: 'num' },

        { id: 120, name: 'sepdefensivestats' },
        { id: 41,  name: 'armor',               type: 'num' },
        { id: 44,  name: 'blockrtng',           type: 'num' },
        { id: 43,  name: 'block',               type: 'num' },
        { id: 42,  name: 'defrtng',             type: 'num' },
        { id: 45,  name: 'dodgertng',           type: 'num' },
        { id: 46,  name: 'parryrtng',           type: 'num' },
        { id: 79,  name: 'resirtng',            type: 'num' },

        { id: 31,  name: 'sepoffensivestats' },
        { id: 32,  name: 'dps',                 type: 'num' },
        { id: 34,  name: 'dmg',                 type: 'num' },
        { id: 77,  name: 'atkpwr',              type: 'num' },
        { id: 97,  name: 'feratkpwr',           type: 'num', indent: 1 },
        { id: 114, name: 'armorpenrtng',        type: 'num' },
        { id: 96,  name: 'critstrkrtng',        type: 'num' },
        { id: 117, name: 'exprtng',             type: 'num' },
        { id: 103, name: 'hastertng',           type: 'num' },
        { id: 119, name: 'hitrtng',             type: 'num' },
        { id: 94,  name: 'splpen',              type: 'num' },
        { id: 123, name: 'splpwr',              type: 'num' },
        { id: 52,  name: 'arcsplpwr',           type: 'num', indent: 1 },
        { id: 53,  name: 'firsplpwr',           type: 'num', indent: 1 },
        { id: 54,  name: 'frosplpwr',           type: 'num', indent: 1 },
        { id: 55,  name: 'holsplpwr',           type: 'num', indent: 1 },
        { id: 56,  name: 'natsplpwr',           type: 'num', indent: 1 },
        { id: 57,  name: 'shasplpwr',           type: 'num', indent: 1 },

        { id: 121, name: 'sepresistances' },
        { id: 25,  name: 'arcres',              type: 'num' },
        { id: 26,  name: 'firres',              type: 'num' },
        { id: 28,  name: 'frores',              type: 'num' },
        { id: 30,  name: 'holres',              type: 'num' },
        { id: 27,  name: 'natres',              type: 'num' },
        { id: 29,  name: 'shares',              type: 'num' },

        { id: 47,  name: 'sepindividualstats' },
        { id: 37,  name: 'mleatkpwr',           type: 'num' },
        { id: 84,  name: 'mlecritstrkrtng',     type: 'num' },
        { id: 78,  name: 'mlehastertng',        type: 'num' },
        { id: 95,  name: 'mlehitrtng',          type: 'num' },
        { id: 38,  name: 'rgdatkpwr',           type: 'num' },
        { id: 40,  name: 'rgdcritstrkrtng',     type: 'num' },
        { id: 101, name: 'rgdhastertng',        type: 'num' },
        { id: 39,  name: 'rgdhitrtng',          type: 'num' },
        { id: 49,  name: 'splcritstrkrtng',     type: 'num' },
        { id: 102, name: 'splhastertng',        type: 'num' },
        { id: 48,  name: 'splhitrtng',          type: 'num' },
        { id: 51,  name: 'spldmg',              type: 'num' },
        { id: 50,  name: 'splheal',             type: 'num' },

        { id: 9999,name: 'sepcommunity' },
        { id: 10,  name: 'hascomments',         type: 'yn' },
        { id: 11,  name: 'hasscreenshots',      type: 'yn' },
        { id: 12,  name: 'hasvideos',           type: 'yn' }
    ],

    areatrigger: [
        { id: 1, name: 'sepgeneral'             },
        { id: 2, name: 'id',        type: 'num' }
    ]

    // end aowow custom
};

function fi_toggle() {
    var c = $WH.ge('fi');
    var b = g_toggleDisplay(c);
    var d = $WH.ge('fi_toggle');

    if (b) {
        // Set focus on first textbox
        d.firstChild.nodeValue = LANG.fihide;
        c = (c.parentNode.tagName == 'FORM' ? c.parentNode : $WH.gE(c, 'form')[0]);
        c = c.elements.na ? c.elements.na: c.elements.ti;
        c.focus();
        c.select();
    }
    else {
        d.firstChild.nodeValue = LANG.fishow;
    }
    d.className = 'disclosure-' + (b ? 'on': 'off');

    return false;
}

function fi_submit(_this) {
    var sum = 0;

    var _ = _this.elements;
    for (var i = 0; i < _.length; ++i) {
        switch (_[i].nodeName) {
            case 'INPUT':
                switch (_[i].type) {
                    case 'text':
                        if ($WH.trim(_[i].value).length > 0) {
                            ++sum;
                        }
                        break;
                    case 'checkbox':
                        if ((_[i].value == 'ja' || _[i].name == 'gb') && _[i].checked) {
                            ++sum;
                        }
                    case 'radio':
                        if(_[i].name != 'ma' && _[i].checked && _[i].value)
                            ++sum;

                    break;
                }
                break;

            case 'SELECT':
                if (_[i].name != 'cr[]' && _[i].name != 'gm' && _[i].selectedIndex != -1 && _[i].options[_[i].selectedIndex].value) {
                    ++sum;
                }

                break;
        }
    }

    var g = $WH.g_getGets();
    if (sum == 0 && !g.filter) {
        alert(LANG.message_fillsomecriteria);
        return false;
    }

    return true;
}

function fi_initWeightedListview() {
    this._scoreMode = (this._upgradeIds ? 2 : 0); // Normalized or percent for upgrades

    if (this.sort[0] == -this.columns.length) {
        this.applySort();
    }

    if (this._upgradeIds && this._minScore) {
        this._maxScore = this._minScore; // Makes percentages relative to upgraded item
    }
}

function fi_filterUpgradeListview(item, i) {
    var nResults = 25;

    if(!this._upgradeIds) {
        return i < nResults;
    }

    if(this._upgradeRow == null) {
        this._upgradeRow = nResults - 1;
    }

    if($WH.in_array(this._upgradeIds, item.id) != -1) {
        if(i < nResults) {
            this._upgradeRow++;
        }

        return true;
    }

    return i < this._upgradeRow;
}

function fi_addUpgradeIndicator() {
    if(this._upgradeIds) {
        var upgradeUrl = fi_filterParamToJson($WH.g_parseQueryString(location.href.replace(/^.*?filter=/, 'filter=')).filter).upg;
        for(var i = 0; i < this.data.length; ++i) {
            var
                item = this.data[i],
                newUpgrade = upgradeUrl.replace(item.id, '').replace(/(^:*|:*$)/g, '').replace('::', ':');

            if($WH.in_array(this._upgradeIds, item.id) != -1) {
                this.createIndicator($WH.sprintf(LANG.lvnote_upgradesfor, item.id, (7 - parseInt(item.name.charAt(0))), item.name.substr(1)), location.href.replace(';upg=' + upgradeUrl, (newUpgrade ? ';upg=' + newUpgrade : '')));
            }
        }
    }
}

function fi_reset(_this) {
    fi_resetCriterion($WH.ge('fi_criteria'));
    fi_resetCriterion($WH.ge('fi_weight'));

    // custom start (originally a click on reset would reload the page with empty filters)
    var bc = PageTemplate.get('breadcrumb');
    if (typeof bc[1] !== 'undefined')
        Menu.modifyUrl(Menu.findItem(mn_database, [bc[1]]), {}, {});
    // custom end

    var _ = $WH.ge('sdkgnsdkn436');
    if (_) {
        _.parentNode.style.display = 'none';
        while (_.firstChild) {
            $WH.de(_.firstChild);
        }

        $WH.ae(_, $WH.ce('option'));
    }

    _ = _this.elements;
    for (var i = 0; i < _.length; ++i) {
        switch (_[i].nodeName) {
            case 'INPUT':
                if (_[i].type == 'text') {
                    _[i].value = '';
                }
                else if (_[i].type == 'checkbox') {
                    _[i].checked = false;
                }
                else if (_[i].type == 'radio' && _[i].value.length == 0) {
                    _[i].checked = true;
                }
            break;

            case 'SELECT':
            _[i].selectedIndex = _[i].multiple ? -1 : 0;
            if (_[i].i) {
                _[i].i = _[i].selectedIndex;
            }
            break;
        }
    }

    return false;
}

function fi_resetCriterion(_this) {
    if (_this != null) {
        var d;
        while (_this.childNodes.length > 1) {
            d = _this.childNodes[1];

            while (d.childNodes.length > 1) {
                d.removeChild(d.childNodes[1]);
            }

            _this.removeChild(d);
        }

        d = _this.childNodes[0];

        while (d.childNodes.length > 1) {
            d.removeChild(d.childNodes[1]);
        }

        d.firstChild.i = null;
        d.firstChild.selectedIndex = 0;

        if (_this.nextSibling.firstChild) {
            _this.nextSibling.firstChild.style.display = _this.style.display;
        }
    }
}

function fi_addCriterion(_this, cr) {
    var _ = $WH.ge(_this.id.replace('add', ''));

    if (_.childNodes.length >= 19 || (_this.id.indexOf('criteria') > 0 && _.childNodes.length >= 4)) {
        _this.style.display = 'none';
    }

    var a = _.childNodes[0].lastChild;
    if (a.nodeName != 'A') {
        fi_appendRemoveLink(_.childNodes[0]);
    }
    else {
        a.firstChild.nodeValue = LANG.firemove;
        a.onmouseup = fi_removeCriterion;
    }

    var
        d = $WH.ce('div'),
        c = _.childNodes[0].childNodes[0].cloneNode(true);

    c.onchange = c.onkeyup = fi_criterionChange.bind(0, c);
    c.i = null;

    if (cr != null) {
        var opts = c.getElementsByTagName('option');
        for (var i = 0; i < opts.length; ++i) {
            if (opts[i].value == cr) {
                opts[i].selected = true;
                break;
            }
        }
    }
    else {
        c.firstChild.selected = true;
    }

    d.appendChild(c);
    fi_appendRemoveLink(d);
    _.appendChild(d);

    return c;
}

function fi_removeCriterion() {
    var
        _,
        d = this.parentNode,
        c = d.parentNode,
        n = (d.firstChild.name == 'wt[]');

    c.removeChild(d);

    if (c.childNodes.length == 1) {
        _ = c.firstChild;
        if (_.firstChild.selectedIndex > 0) {
            var a = _.lastChild;
            a.firstChild.nodeValue = LANG.ficlear;
            a.onmouseup = fi_clearCriterion;
        }
        else {
            _.removeChild(_.lastChild);
            _.removeChild(_.lastChild);
        }
    }

    if (c.nextSibling.firstChild) {
        c.nextSibling.firstChild.style.display = '';
    }

    if (n) {
        _ = $WH.ge('sdkgnsdkn436');
        _.selectedIndex = 0;
        _.i = 0;
        fi_presetMatch();
    }
}

function fi_clearCriterion() {
    var d = this.parentNode;
    d.firstChild.selectedIndex = 0;
    fi_criterionChange(d.firstChild);
}

function fi_appendRemoveLink(d) {
    d.appendChild($WH.ct(String.fromCharCode(160, 160)));
    var a = $WH.ce('a');
    a.href = 'javascript:;';
    a.appendChild($WH.ct(LANG.firemove));
    a.onmouseup = fi_removeCriterion;
    a.onmousedown = a.onclick = $WH.rf;
    d.appendChild(a);
}

function fi_appendClearLink(d) {
    d.appendChild($WH.ct(String.fromCharCode(160, 160)));
    var a = $WH.ce('a');
    a.href = 'javascript:;';
    a.appendChild($WH.ct(LANG.ficlear));
    a.onmouseup = fi_clearCriterion;
    a.onmousedown = a.onclick = $WH.rf;
    d.appendChild(a);
}

function fi_Lookup(value, type) {
    var c;

    if (type == null) {
        type = fi_type;
    }

    if (fi_Lookup.cache == null) {
        fi_Lookup.cache = {};
    }

    if (fi_Lookup.cache[type] == null) {
        c = {};

        for (var i = 0, len = fi_filters[type].length; i < len; ++i) {
            if(!(g_user.roles & U_GROUP_EMPLOYEE) && fi_filters[type][i].staffonly)
                continue;

            var f = fi_filters[type][i];

            c[f.id]   = f;
            c[f.name] = f;
        }

        fi_Lookup.cache[type] = c;
    }
    else {
        c = fi_Lookup.cache[type];
    }

    if (value && typeof value == 'string') {
        var firstChar = value.charCodeAt(0);
        if (firstChar >= '0'.charCodeAt(0) && firstChar <= '9'.charCodeAt(0)) {
            value = parseInt(value);
        }
    }

    return c[value];
}

function fi_criterionChange(_this, crs, crv) {
    var _;

    if (_this.selectedIndex != _this.i) {
        var
            o = _this.options[_this.selectedIndex],
            d = _this.parentNode;

        if (d.childNodes.length > 1) {
            if (_this.selectedIndex > 0 && _this.i > 0) {
                var a = fi_Lookup(o.value);
                var b = fi_Lookup(_this.options[_this.i].value);

                if (a.type == b.type) {
                    return;
                }
            }

            while (d.childNodes.length > 1) {
                d.removeChild(d.childNodes[1]);
            }
        }

        if (_this.selectedIndex > 0) {
            var p = fi_Lookup(o.value);
            var parts = p.type.split('-');
            var criteriaName = parts[0];
            var criteriaParams = parts[1] || '';

            if (LANG.fidropdowns[criteriaName] != null) {
                if (_this.name == 'cr[]') {
                    var _c = LANG.fidropdowns[criteriaName];
                    _ = $WH.ce('select');
                    _.name = 'crs[]';

                    var group = _;

                    if (criteriaParams.indexOf('any') != -1) {
                        var o2 = $WH.ce('option');
                        o2.value = '-2323';
                        o2.appendChild($WH.ct(LANG.fiany));
                        $WH.ae(group, o2);
                        if (crs != null && crs == '-2323') {
                            o2.selected = true;
                        }
                    }

                    for (var i = 0; i < _c.length; ++i) {
                        if (_c[i][0] !== null) {
                            var o2 = $WH.ce('option');
                            o2.value = _c[i][0];
                            o2.appendChild($WH.ct(_c[i][1]));
                            $WH.ae(group, o2);
                            if (crs != null && crs == _c[i][0]) {
                                o2.selected = true;
                            }
                        }
                        else {
                            var group = $WH.ce('optgroup');
                            group.label = _c[i][1];
                            $WH.ae(_, group);
                        }
                    }

                    if (criteriaParams.indexOf('none') != -1) {
                        var o2 = $WH.ce('option');
                        o2.value = '-2324';
                        o2.appendChild($WH.ct(LANG.finone));
                        $WH.ae(group, o2);
                        if (crs != null && crs == '-2324') {
                            o2.selected = true;
                        }
                    }

                    d.appendChild($WH.ct(' '));
                    d.appendChild(_);
                }

                var n = (criteriaName == 'num');

                if (n) {
                    d.appendChild($WH.ct(' '));
                }

                _ = $WH.ce('input');
                _.type = 'text';

                if (crv != null) {
                    _.value = crv.toString();
                }
                else {
                    _.value = '0';
                }

                if (_this.name == 'cr[]') {
                    _.name = 'crv[]';
                }
                else {
                    _.name = 'wtv[]';
                    _.onchange = fi_changeWeight.bind(0, _);
                }

                if (n) {
                    _.maxLength = 8;
                    _.style.textAlign = 'center';
                    _.style.width = '5.0em';
                }
                else {
                    _.type = 'hidden';
                }

                _.setAttribute('autocomplete', 'off');

                d.appendChild(_);
                if (_this.name == 'wt[]') {
                    fi_sortWeight(_);
                }
            }
            else if (criteriaName == 'str') {
                _ = $WH.ce('input');
                _.name = 'crs[]';
                _.type = 'hidden';
                _.value = '0';
                d.appendChild(_);

                _ = $WH.ce('input');
                _.type = 'text';
                if (criteriaParams.indexOf('small') != -1) {
                    _.maxLength = 8;
                    _.style.textAlign = 'center';
                    _.style.width = '5.0em';
                }
                else {
                    _.maxLength = 50;
                    _.style.width = '9em';
                }
                _.name = 'crv[]';

                if (crv != null) {
                    _.value = crv;
                }

                d.appendChild($WH.ct(' '));
                d.appendChild(_);
            }
        }

        if (d.parentNode.childNodes.length == 1) {
            if (_this.selectedIndex > 0) {
                fi_appendClearLink(d);
            }
        }
        else if (d.parentNode.childNodes.length > 1) {
            fi_appendRemoveLink(d);
        }

        _this.i = _this.selectedIndex;
    }
}

function fi_setCriteria(cr, crs, crv) {
    var _ = $WH.ge('fi_criteria');

    var
        i,
        c = _.childNodes[0].childNodes[0];

    _ = c.getElementsByTagName('option');
    for (i = 0; i < _.length; ++i) {
        if (_[i].value == cr[0]) {
            _[i].selected = true;

            if (fi_Lookup(cr[0])) {
                g_trackEvent('Filters', fi_type, fi_Lookup(cr[0]).name);
            }

            break;
        }
    }
    fi_criterionChange(c, crs[0], crv[0]);

    var a = $WH.ge('fi_addcriteria');
    for (i = 1; i < cr.length && i < 5; ++i) {
        fi_criterionChange(fi_addCriterion(a, cr[i]), crs[i], crv[i]);

        if (fi_Lookup(cr[i])) {
            g_trackEvent('Filters', fi_type, fi_Lookup(cr[i]).name);
        }
    }
}

function fi_setWeights(weights, nt, ids, stealth) {
    if (ids) {
        var
            wt = weights[0],
            wtv = weights[1],
            weights = {};

        for (var i = 0; i < wt.length; ++i) {
            var a = fi_Lookup(wt[i]);
            if (a && a.type == 'num' && !a.noweights && LANG.traits[a.name]) {
                weights[a.name] = wtv[i];
            }
        }
    }

    var _ = $WH.ge('fi_weight');

    if (fi_weights == null) {
        fi_weights = {};
        $WH.cO(fi_weights, weights);
    }

    var
        a = $WH.ge('fi_addweight'),
        c = _.childNodes[0].childNodes[0];

    var i = 0;
    for (var w in weights) {
        if (!LANG.traits[w]) {
            continue;
        }

        if (i++>0) {
            c = fi_addCriterion(a, w);
        }

        var opts = c.getElementsByTagName('option');

        for (var j = 0; j < opts.length; ++j) {
            if (opts[j].value && w == fi_Lookup(opts[j].value).name) {
                opts[j].selected = true;
                break;
            }
        }

        fi_criterionChange(c, 0, weights[w]);
    }

    fi_weightsFactor = fi_convertWeights(weights, true);

    $WH.ge('fi_weight_toggle').className = 'disclosure-on';
    $WH.ge('fi_weight').parentNode.style.display = '';

    if (!nt) {
        if (!fi_presetMatch(weights, stealth)) {
            fi_presetDetails();
        }
    }
}

function fi_changeWeight(_this) {
    var _ = $WH.ge('sdkgnsdkn436');
    _.selectedIndex = 0;
    _.i = 0;
    fi_sortWeight(_this);
    fi_presetMatch();
}

function fi_sortWeight(_this) {
    var
        i,
        _ = $WH.ge('fi_weight'),
        v = Number(_this.value);

    _this = _this.parentNode;

    n = 0;
    for (i = 0; i < _.childNodes.length; ++i) {
        var m = _.childNodes[i];
        if (m.childNodes.length == 5) {
            n++;
            if (m.childNodes[2].nodeName == "INPUT" && v > Number(m.childNodes[2].value)) {
                _.insertBefore(_this, m);
                return;
            }
        }
    }
    _.insertBefore(_this, _.childNodes[n]);
}

function fi_convertWeights(weights, getf) {
    var
        f = 0,
        m = 0;

    for (var w in weights) {
        if (!LANG.traits[w]) {
            continue;
        }
        f += Math.abs(weights[w]);
        if (Number(weights[w]) > m) {
            m = Number(weights[w]);
        }
    }

    if (getf) {
        return f;
    }

    var result = {};
    for (var w in weights) {
        result[w] = (LANG.traits[w] ? Math.round(1000 * weights[w] / f) / 1000 : weights[w]);
    }

    return result;
}

function fi_convertScore(score, mode, maxScore) {
    if (mode == 1) { // Raw
        return parseInt(score * fi_weightsFactor);
    }
    else if (mode == 2) { // Percent
        return ((score / maxScore) * 100).toFixed(1) + '%';
    }
    else { // Normalized
            return score.toFixed(2);
    }
}

function fi_updateScores() {
    if (++this._scoreMode > 2) {
        this._scoreMode = 0;
    }

    for (var i = 0; i < this.data.length; ++i) {
        if (this.data[i].__tr) {
            var cell = this.data[i].__tr.lastChild;
            cell.firstChild.firstChild.nodeValue = fi_convertScore(this.data[i].score, this._scoreMode, this._maxScore);
        }
    }
}

function fi_presetClass(_this, stealth) {
    if (_this.selectedIndex != _this.i) {
        var
            i,
            _group,
            c = _this.options[_this.selectedIndex],
            d = _this.parentNode,
            langref = LANG.presets;

        fi_resetCriterion(_);

        var _ = $WH.ge('sdkgnsdkn436');
        _.parentNode.style.display = 'none';

        var oldValue = 1000;
        if (_this.i) {
            oldValue = $($('#fi_presets select option')[_this.i]).attr('value');
        }

        if(!stealth && (_this.form.ub.selectedIndex == 0 || _this.form.ub.value == oldValue)) {
            $('select[name=ub] option[value="' + _this.value + '"]').attr('selected', 'selected');
        }

        while (_.firstChild) {
            $WH.de(_.firstChild);
        }
        $WH.ae(_, $WH.ce('option'));

        if (_this.selectedIndex > 0) {
            for (_group in c._presets) {
                var weights = c._presets[_group];

                if(langref[_group] != null) {
                    var group = $WH.ce('optgroup');
                    group.label = langref[_group];
                }
                else
                    group = _;

                for (var p in weights) {
                    var o = $WH.ce('option');
                    o.value = p;
                    o._weights = weights[p];
                    $WH.ae(o, $WH.ct(weights[p].name ? weights[p].name : langref[p]));
                    $WH.ae(group, o);
                }

                if (langref[_group] != null && group && group.childNodes.length > 0) {
                    $WH.ae(_, group);
                }
            }

            if (_.childNodes.length > 1) {
                _.parentNode.style.display = '';
            }
        }

        fi_presetChange(_);
        _this.i = _this.selectedIndex;
    }
}

function fi_presetChange(_this) {
    if (_this.selectedIndex != _this.i) {
        fi_resetCriterion($WH.ge('fi_weight'));

        var o = _this.options[_this.selectedIndex];
        if (_this.selectedIndex > 0) {
            // Default gem color
            if (_this.form.elements.gm.selectedIndex == 0) {
                _this.form.elements.gm.selectedIndex = 2; // Rare
            }

            fi_resetCriterion($WH.ge('fi_weight'));
            fi_setWeights(o._weights, 1, 0);
        }

        _this.i = _this.selectedIndex;

        if (g_user.id > 0) {
            var a = $WH.ge('fi_remscale');
            a.style.display = (o._weights && o._weights.name ? '' : 'none');
        }
    }
}

function fi_presetDetails() {
    var
        _     = $WH.ge('fi_weight'),
        _this = $WH.ge('fi_detail'),
        n     = $WH.ge('fi_addweight');

    var a = g_toggleDisplay(_);

    n.style.display = 'none';
    if (a) {
        _this.firstChild.nodeValue = LANG.fihidedetails;
        if (_.childNodes.length < 19) {
            n.style.display = '';
        }
    }
    else {
        _this.firstChild.nodeValue = LANG.fishowdetails;
    }

    return false;
}

function fi_presetMatch(weights, stealth) {
    if (!weights) {
        weights = {};
        var _ = $WH.ge('fi_weight');

        for (var i = 0; i < _.childNodes.length; ++i) {
            if (_.childNodes[i].childNodes.length == 5) {
                if (_.childNodes[i].childNodes[0].nodeName == "SELECT" && _.childNodes[i].childNodes[2].nodeName == "INPUT") {
                    var node = _.childNodes[i].childNodes[0].options[_.childNodes[i].childNodes[0].selectedIndex];

                    if (node.value) {
                        weights[fi_Lookup(node.value)] = Number(_.childNodes[i].childNodes[2].value);
                    }
                }
            }
        }
    }

    var _ = $WH.ge('fi_presets');
    var s = _.getElementsByTagName('select');
    if (s.length != 2) {
        return false;
    }

    var n = fi_convertWeights(weights);
    for (var l in wt_presets) {
        for (var k in wt_presets[l]) {
            for (var v in wt_presets[l][k]) {
                if (Object.keys(wt_presets[l][k][v]).length == 1) {
                    continue;
                }

                p = fi_convertWeights(wt_presets[l][k][v]);

                var match = true;
                for (var w in p) {
                    if (!LANG.traits[w]) {
                        continue;
                    }
                    if (!n[w] || p[w] != n[w]) {
                        match = false;
                        break;
                    }
                }

                if (match) {
                    for (var i = 0; i < s[0].options.length; ++i) {
                        if (l == s[0].options[i].value) {
                            s[0].options[i].selected = true;
                            fi_presetClass(s[0], stealth);
                            break;
                        }
                    }

                    for (var j = 0; j < s[1].options.length; ++j) {
                        if (v == s[1].options[j].value) {
                            s[1].options[j].selected = true;
                            fi_presetChange(s[1]);
                            break;
                        }
                    }
                    return true;
                }
            }
        }
    }

    return false;
}


function fi_presetSave() {
    if (!g_user.id) {
        return;
    }

    var
        o     = $('#sdkgnsdkn436 option:selected').get(0),
        name  = '',
        id    = ((o._weights ? o._weights.id : 0) | 0),
        scale = { id: id, name: name },
        _     = $WH.ge('fi_weight'),
        n     = 0;

    for (i = 0; i < _.childNodes.length; ++i) {
        var
            w = fi_Lookup($('[name="wt[]"]', _.childNodes[i]).val()),
            v = $('[name="wtv[]"]', _.childNodes[i]).val();

        if (w && v != 0) {
            scale[w.name] = v;
            ++n;
        }
    }

    if (n < 1 || (!(o._weights && o._weights.id) && g_user.weightscales && g_user.weightscales.length >= 5)) {
        return alert(LANG.message_weightscalesaveerror);
    }

    if (name = prompt(LANG.prompt_nameweightscale, $(o).text())) {
        var data = { save: 1,   name: $WH.urlencode(name), scale: '' };

        if (id) {
            data.id = id;
        }

        scale.name = name;
        n = 0;

        for (var w in scale) {
            if (!LANG.traits[w]) {
                continue;
            }
            if (n++ > 0) {
                data.scale += ',';
            }
            data.scale += w + ':' + scale[w];
        }

        $.post('?account=weightscales', data, function(response) {
            if (response > 0) {
                if (g_user.weightscales == null) {
                    g_user.weightscales = [];
                }

                if (scale.id) {
                    g_user.weightscales = $WH.array_filter(g_user.weightscales, function(x) {
                        return x.id != scale.id
                    });
                }

                scale.id = response;
                g_user.weightscales.push(scale);

                var
                    s = $('#fi_presets select').get(0),
                    c = $('option[value=-1]', s).get(0);

                if (!c) {
                    c = $WH.ce('option');
                    c.value = -1;
                    $WH.ae(c, $WH.ct(LANG.ficustom));
                    $WH.aef(s, c);
                    $WH.aef(s, $('option', s).get(1));
                }

                c._presets = { custom: {} };
                for (var i = 0, len = g_user.weightscales.length; i < len; ++i) {
                    c._presets.custom[g_user.weightscales[i].id] = g_user.weightscales[i];
                }

                c.selected = true;
                c.parentNode.onchange();

                var o = $('#sdkgnsdkn436 option[value=' + scale.id + ']').get(0);
                if (o) {
                    o.text = scale.name;
                    o.selected = true;
                    o.parentNode.onchange();
                }

                alert(LANG.message_saveok);
            }
            else {
                alert(LANG.message_weightscalesaveerror);
            }
        });
    }
}

function fi_presetDelete() {
    if(!g_user.id) {
        return;
    }

    var
        s = $('#fi_presets select').get(0),
        c = $('option:selected', s).get(0);

    if(c.value == -1) {
        var o = $('#sdkgnsdkn436 option:selected').get(0);
        if (o.value && confirm(LANG.confirm_deleteweightscale)) {
            $.post('?account=weightscales', { 'delete': 1, id: o.value });

            g_user.weightscales = $WH.array_filter(g_user.weightscales, function(x) {
                return x.id != o.value;
            });

            if (g_user.weightscales.length) {
                c._presets = { custom: {} };
                for (var i = 0, len = g_user.weightscales.length; i < len; ++i) {
                    c._presets.custom[g_user.weightscales[i].id] = g_user.weightscales[i];
                }
            }
            else {
                $WH.de(c);
            }

            $WH.de(o);

            $('#fi_presets select').change();
        }
    }
}

function fi_scoreSockets(item) {
    if (fi_gemScores != null) {
        var match = 0,  best = 0,   sock = 0;
        var mGems = [], bGems = [];
        var mUniq = [], bUniq = [];
        var mSock = {}, bSock = {};
        var noMatch = false;

        for (var i = 1; i <= 3; ++i) {
            var
                n = item['socket' + i],
                s = (n == 1 ? 1 : 0);

            if (n) {
                if (fi_gemScores[n]) {
                    for (var j = 0; j < fi_gemScores[n].length; ++j) {
                        var t = fi_gemScores[n][j];
                        if (t.socketLevel <= item.level && (t.uniqEquip == 0 || $WH.in_array(mUniq, t.id) < 0)) {
                            match += t.score;
                            mGems.push(t.id);

                            mSock[i - 1] = 1;

                            if (t.uniqEquip == 1) {
                                mUniq.push(t.id);
                            }

                            break;
                        }
                    }
                }
                else {
                    noMatch = true;
                }

                if (fi_gemScores[s]) {
                    for (var j = 0; j < fi_gemScores[s].length; ++j) {
                        var t = fi_gemScores[s][j];
                        if (t.socketLevel <= item.level && (t.uniqEquip == 0 || $WH.in_array(bUniq, t.id) < 0)) {
                            best += t.score;
                            bGems.push(t.id);

                            bSock[i - 1] = (t.colors & n);

                            if (t.uniqEquip == 1) {
                                bUniq.push(t.id);
                            }

                            break;
                        }
                    }
                }
            }
        }

        if (item.socketbonusstat && fi_weights && fi_weightsFactor != 0) {
            for (var i in fi_weights) {
                if (item.socketbonusstat[i]) {
                    sock += item.socketbonusstat[i] * fi_weights[i] / fi_weightsFactor;
                }
            }
        }

        item.scoreBest = best;
        item.scoreMatch = match + sock;
        item.scoreSocket = sock;

        if (item.scoreMatch >= item.scoreBest && !noMatch) {
            item.matchSockets = mSock;
            item.gemGain = item.scoreMatch;
            item.gems = mGems;
        }
        else {
            item.matchSockets = bSock;
            item.gemGain = item.scoreBest;
            item.gems = bGems;
        }

        item.score += item.gemGain;
    }

    if (item.score > this._maxScore || !this._maxScore) {
        this._maxScore = item.score;
    }

    if (this._upgradeIds && $WH.in_array(this._upgradeIds, item.id) != -1) {
        this._minScore = item.score;
        item.upgraded = 1;
    }
}

function fi_dropdownSync(_this) {
    if (_this.selectedIndex >= 0) {
        _this.className = _this.options[_this.selectedIndex].className;
    }
}

function fi_init(type) {
    fi_type = type;

    var
        s = $WH.ge('fi_subcat'),
        m = Menu.findItem(mn_path, PageTemplate.get('breadcrumb'));

    if (s && m[3]) {
        Menu.add(s, m[3]);
    }
    else if (s) {
        $WH.de(s.parentNode);
    }

    fi_initCriterion($WH.ge('fi_criteria'), 'cr[]', type);
    if (type == 'items') {
        var foo = $WH.ge('fi_presets');
        if (foo) {
            fi_initPresets($WH.ge('fi_presets'));
            fi_initCriterion($WH.ge('fi_weight'), 'wt[]', type);
        }
    }

    var ma = $WH.ge('ma-0');
    if (ma.getAttribute('checked')) {
        ma.checked = true;
    }
}

function fi_initCriterion(_this, sname, type) {
    var div = _this.firstChild;

    var s = $WH.ce('select');
    s.name = sname;
    s.onchange = s.onkeyup = fi_criterionChange.bind(0, s);
    $WH.ae(s, $WH.ce('option'));

    var group = null;
    var langref = LANG['fi' + type];

    for (var i = 0, len = fi_filters[type].length; i < len; ++i) {
        if (!(g_user.roles & U_GROUP_EMPLOYEE) && fi_filters[type][i].staffonly) {
            continue;
        }

        var p = fi_filters[type][i];

        if (!p.type) {
            if (group && group.childNodes.length > 0) {
                $WH.ae(s, group);
            }
            group = $WH.ce('optgroup');
            group.label = (LANG.traits[p.name] ? LANG.traits[p.name] : langref[p.name]);
        }
        else if (sname != 'wt[]' || (p.type == 'num' && !p.noweights)) {
            var o = $WH.ce('option');
            o.value = p.id;

            var txt = LANG.traits[p.name] ? LANG.traits[p.name][0] : langref[p.name];
            if (p.indent) {
                txt = '- ' + txt;
            }

            $WH.ae(o, $WH.ct(txt));
            $WH.ae(group, o);
        }
    }
    if (group && group.childNodes.length > 0) {
        $WH.ae(s, group);
    }

    $WH.ae(div, s);
}

function fi_initPresets(_this) {
    var
        _class,
        s = $WH.ce('select');

    s.onchange = s.onkeyup = fi_presetClass.bind(0, s, 0);

    $WH.ae(s, $WH.ce('option'));

    if (g_user.weightscales != null && g_user.weightscales.length) {
        var o = $WH.ce('option');
        o.value = -1;
        o._presets = { custom: {} };
        $WH.ae(o, $WH.ct(LANG.ficustom));
        $WH.ae(s, o);

        for (var i = 0, len = g_user.weightscales.length; i < len; ++i) {
            o._presets.custom[g_user.weightscales[i].id] = g_user.weightscales[i];
        }
    }

    var temp = [];
    for (var c in wt_presets) {
        temp.push(c);
    }

    temp.sort(function (a, b) {
        return $WH.strcmp(g_chr_classes[a], g_chr_classes[b]);
    });

    for (var i = 0, len = temp.length; i < len; ++i) {
        var
            c = temp[i],
            o = $WH.ce('option');

        o.value = c;
        o._presets = wt_presets[c];
        $WH.ae(o, $WH.ct(g_chr_classes[c]));
        $WH.ae(s, o);
    }

    $WH.ae(_this, s);

    var _ = $WH.ce('span');
    _.style.display = 'none';

    var s = $WH.ce('select');
    s.id = 'sdkgnsdkn436';
    s.onchange = s.onkeyup = fi_presetChange.bind(0, s);
    $WH.ae(s, $WH.ce('option'));

    $WH.ae(_, $WH.ct(' '));
    $WH.ae(_, s);
    $WH.ae(_this, _);
    $WH.ae(_this, $WH.ct(String.fromCharCode(160, 160)));

    var a = $WH.ce('a');
    a.href = 'javascript:;';
    a.id = 'fi_detail';
    a.appendChild($WH.ct(LANG.fishowdetails));
    a.onclick = fi_presetDetails;
    a.onmousedown = $WH.rf;
    $WH.ae(_this, a);

    if(g_user.id > 0) {
        $WH.ae(_this, $WH.ct(String.fromCharCode(160, 160)));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.className = 'icon-save';
        a.appendChild($WH.ct(LANG.fisavescale));
        a.onclick = fi_presetSave;
        a.onmousedown = $WH.rf;
        $WH.ae(_this, a);

        $WH.ae(_this, $WH.ct(String.fromCharCode(160, 160)));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.id = 'fi_remscale';
        a.className = 'icon-delete';
        a.style.display = 'none';
        a.appendChild($WH.ct(LANG.fideletescale));
        a.onclick = fi_presetDelete;
        a.onmousedown = $WH.rf;
        $WH.ae(_this, a);
    }

    var helpLink = $WH.ge('statweight-help');
    if (helpLink) {
        g_addTooltip(helpLink, LANG.tooltip_statweighting, 'q');
    }
}

function fi_getExtraCols(wt, gm, pu) {
    if (!wt.length) {
        return;
    }

    var
        res = [],
        langref = LANG.fiitems;

    var nColsMax = 10;
    if (fi_weightsFactor) {
        nColsMax--;
    }
    if (gm) {
        nColsMax--;
    }
    if (pu) {
        nColsMax--;
    }

    for (var i = 0; i < wt.length && i < nColsMax; ++i) {
        var a = fi_Lookup(wt[i]);
        if (a && a.name && a.type == 'num' && a.name != 'buyprice') {
            var b = {
                id:      a.name,
                value:   a.name,
                name:    (LANG.traits[a.name] ? LANG.traits[a.name][2] : langref[a.name]),
                tooltip: (LANG.traits[a.name] ? LANG.traits[a.name][0] : langref[a.name]),
                before:  (a.before ? a.before: 'source')
            };

            // Fix display of decimal columns
            if (a.name == 'speed') {
                b.compute = function (item, td) {
                    return (item.speed || 0).toFixed(2);
                }
            }

            if (/dps/i.test(a.name)) {                      // mledps|rngdps|dps
                b.compute = function (item, td) {
                    return (item.dps || 0).toFixed(1);
                }
            }

            // Fix display of money columns
            if (/money|price|cost/i.test(a.name)) {
                b.compute = function (item, td) {
                    td.innerHTML = g_getMoneyHtml(item[a.name] || 0);
                }
            }

            if (a.name == 'id') {
                b.width = '5%';
            }

            res.push(b);
        }
    }

    if (fi_weightsFactor) {
        if (gm) {
            res.push({
                id: 'gems',
                name: LANG.gems,
                getValue: function (item) {
                    return item.gems.length;
                },
                compute: function (item, td) {
                    if (!item.nsockets || !item.gems) {
                        return;
                    }

                    var sockets = [];
                    for (var i = 0; i < item.nsockets; i++) {
                        sockets.push(item['socket' + (i + 1)]);
                    }

                    var
                        bonusText = '',
                        i = 0;

                    for (var s in item.socketbonusstat) {
                        if (LANG.traits[s]) {
                            if (i++ > 0) {
                                bonusText += ', ';
                            }
                            bonusText += '+' + item.socketbonusstat[s] + ' ' + LANG.traits[s][2];
                        }
                    }

                    Listview.funcBox.createSocketedIcons(sockets, td, item.gems, item.matchSockets, bonusText);
                },
                sortFunc: function (a, b, col) {
                    return $WH.strcmp((a.gems ? a.gems.length: 0), (b.gems ? b.gems.length: 0));
                }
            });
        }

        res.push({
            id: 'score',
            name: LANG.score,
            width: '7%',
            value: 'score',
            compute: function (item, td) {
                var a = $WH.ce('a');
                a.href = 'javascript:;';
                a.onclick = fi_updateScores.bind(this);
                a.className = (item.gemGain > 0 ? 'q2': 'q1');

                $WH.ae(a, $WH.ct(fi_convertScore(item.score, this._scoreMode, this._maxScore)));

                $WH.ae(td, a);
            }
        });
    }

    if (pu) {
        res.push(Listview.extraCols.cost);
    }

    fi_nExtraCols = res.length;

    return res;
}

function fi_getReputationCols(factions) {
    var res = [];

    for (var i = 0, len = factions.length; i < len; ++i) {
        var name = factions[i][1];

        if (name.length > 15) {
            var words = factions[i][1].split(' ');
            for (var j = 0, len2 = words.length; j < len2; ++j) {
                if (words[j].length > 3) {
                    name = (words[j].length > 15 ? words[j].substring(0, 12) + '...': words[j]);
                    break;
                }
            }
        }

        var col = {
            id:      'faction-' + factions[i][0],
            name:    name,
            tooltip: factions[i][1],
            type:    'num',
            before:  'category'
        };

        eval("col.getValue = function(quest) { return Listview.funcBox.getQuestReputation(" + factions[i][0] + ", quest) }");
        eval("col.compute = function(quest, td) { return Listview.funcBox.getQuestReputation(" + factions[i][0] + ", quest) }");
        eval("col.sortFunc = function(a, b, col) { var _ = Listview.funcBox.getQuestReputation; return $WH.strcmp(_(" + factions[i][0] + ", a), _(" + factions[i][0] + ", b)) }");

        res.push(col);
    }

    return res;
}

function fi_setFilterParams(newParam, menuUrl) {
    return fi_mergeFilterParams(null, newParam, menuUrl);
}

// This is used when propagating the active filter throughout the menu/breadcrumb.
// If the item already had filter params, they will be combined.
function fi_mergeFilterParams(oldParam, newParam, menuUrl) {
    var oldJson = fi_filterParamToJson(oldParam);
    var newJson = fi_filterParamToJson(newParam);

    if(menuUrl && menuUrl.match('filter=')) { // Don't propegate child menu criteria
        menuJson = fi_filterParamToJson($WH.g_parseQueryString(menuUrl.replace(/^.*?filter=/, 'filter=')).filter);
        newJson  = fi_removeMenuCriteria(newJson, menuJson);
    }

    var combinedJson = $.extend(newJson, oldJson); // Existing filter params have priority over new ones

    return fi_filterJsonToParam(combinedJson);
}

function fi_removeMenuCriteria(json, menu) {
    if(menu.cr && json.cr && json.crs && json.crv) {
        var
            parts   = menu.cr.split(':'),
            filters = {
                cr:  json.cr.split(':'),
                crs: json.crs.split(':'),
                crv: json.crv.split(':')
            };

        $WH.array_walk(parts, function(part) {
            var pos = $WH.in_array(filters.cr, part);
            if (pos != -1) {
                filters.cr.splice(pos, 1);
                filters.crs.splice(pos, 1);
                filters.crv.splice(pos, 1);
            }
        });

        json.cr  = filters.cr.join(':');
        json.crs = filters.crs.join(':');
        json.crv = filters.crv.join(':');
    }
    else if (menu.fa && json.fa) {
        delete json.fa;
    }

    return json;
}

function fi_filterParamToJson(filters) {
    var result = {};

    if(filters) {
        var parts = filters.split(';');

        $.each(parts, function(idx, part) {
            $WH.g_splitQueryParam(part, result);
        });
    }

    return result;
}

function fi_filterJsonToParam(json) {
    var result = '';

    var i = 0;

    $.each(json, function(name, value) {
        if (value !== '') {
            if (i++ > 0) {
                result += ';';
            }
            result += name + '=' + value;
        }
    });

    return result;
}
