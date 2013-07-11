<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');

/*
 * Page
 */

// TypeIds
define('TYPE_NPC',                          1);
define('TYPE_OBJECT',                       2);
define('TYPE_ITEM',                         3);
define('TYPE_ITEMSET',                      4);
define('TYPE_QUEST',                        5);
define('TYPE_SPELL',                        6);
define('TYPE_ZONE',                         7);
define('TYPE_FACTION',                      8);
define('TYPE_PET',                          9);
define('TYPE_ACHIEVEMENT',                  10);
define('TYPE_TITLE',                        11);
define('TYPE_WORLDEVENT',                   12);
define('TYPE_CLASS',                        13);
define('TYPE_RACE',                         14);
define('TYPE_SKILL',                        15);
define('TYPE_CURRENCY',                     17);

define('CACHETYPE_PAGE',                    0);
define('CACHETYPE_TOOLTIP',                 1);
define('CACHETYPE_BUFF',                    2);             // only used by spells obviously
define('CACHETYPE_SEARCH',                  3);

define('SEARCH_TYPE_REGULAR',               0x10000000);
define('SEARCH_TYPE_OPEN',                  0x20000000);
define('SEARCH_TYPE_JSON',                  0x40000000);
define('SEARCH_MASK_OPEN',                  0x007DC1FF);    // open search
define('SEARCH_MASK_ALL',                   0x07FFFFFF);    // normal search

// Databases
define('DB_AOWOW',                          0);
define('DB_WORLD',                          1);
define('DB_AUTH',                           2);
define('DB_CHARACTERS',                     3);

// Auth Result
define('AUTH_OK',                           0);
define('AUTH_WRONGPASS',                    1);
define('AUTH_TIMEDOUT',                     2);
define('AUTH_BANNED',                       3);
define('AUTH_IPBANNED',                     4);

// Cookie Names
define('COOKIE_AUTH',                       'aw_a');

// Times
define('MINUTE',                            60);
define('HOUR',                              60  * MINUTE);
define('DAY',                               24  * HOUR);
define('WEEK',                              7   * DAY);
define('MONTH',                             30  * DAY);
define('YEAR',                              365 * DAY);

// User Groups
define('U_GROUP_NONE',                      0x0000);
define('U_GROUP_TESTER',                    0x0001);
define('U_GROUP_ADMIN',                     0x0002);
define('U_GROUP_EDITOR',                    0x0004);
define('U_GROUP_MOD',                       0x0008);
define('U_GROUP_BUREAU',                    0x0010);
define('U_GROUP_DEV',                       0x0020);
define('U_GROUP_VIP',                       0x0040);
define('U_GROUP_BLOGGER',                   0x0080);
define('U_GROUP_PREMIUM',                   0x0100);
define('U_GROUP_LOCALIZER',                 0x0200);
define('U_GROUP_SALESAGENT',                0x0400);
define('U_GROUP_STAFF',                     (U_GROUP_ADMIN|U_GROUP_EDITOR|U_GROUP_MOD|U_GROUP_BUREAU|U_GROUP_DEV|U_GROUP_BLOGGER|U_GROUP_LOCALIZER|U_GROUP_SALESAGENT));
define('U_GROUP_EMPLOYEE',                  (U_GROUP_ADMIN|U_GROUP_BUREAU|U_GROUP_DEV));
define('U_GROUP_GREEN_TEXT',                (U_GROUP_MOD|U_GROUP_BUREAU|U_GROUP_DEV));
define('U_GROUP_MODERATOR',                 (U_GROUP_ADMIN|U_GROUP_MOD|U_GROUP_BUREAU));
define('U_GROUP_COMMENTS_MODERATOR',        (U_GROUP_MODERATOR|U_GROUP_LOCALIZER));
define('U_GROUP_PREMIUM_PERMISSIONS',       (U_GROUP_PREMIUM|U_GROUP_STAFF|U_GROUP_VIP));

// Locales
define('LOCALE_EN',                         0);
define('LOCALE_FR',                         2);
define('LOCALE_DE',                         3);
define('LOCALE_ES',                         6);
define('LOCALE_RU',                         8);

// conditional information in template
define('GLOBALINFO_SELF',                   0x1);           // id, name, icon
define('GLOBALINFO_RELATED',                0x2);           // spells used by pet, classes/races required by spell, ect
define('GLOBALINFO_REWARDS',                0x4);           // items rewarded by achievement/quest, ect
define('GLOBALINFO_ANY',                    0xF);

define('ITEMINFO_JSON',                     0x1);
define('ITEMINFO_SUBITEMS',                 0x2);
define('ITEMINFO_VENDOR',                   0x4);
define('ITEMINFO_LOOT',                     0x8);

define('NPCINFO_TAMEABLE',                  0x1);
define('NPCINFO_MODEL',                     0x2);

define('SPAWNINFO_ZONES',                   1);             // not a mask, mutually exclusive
define('SPAWNINFO_SHORT',                   2);
define('SPAWNINFO_FULL',                    3);

/*
 * Game
 */

// Custom Flags (shared)
define('CUSTOM_HAS_COMMENT',                0x01000000);
define('CUSTOM_HAS_SCREENSHOT',             0x02000000);
define('CUSTOM_HAS_VIDEO',                  0x04000000);
define('CUSTOM_DISABLED',                   0x08000000);
define('CUSTOM_SERVERSIDE',                 0x10000000);
define('CUSTOM_UNAVAILABLE',                0x20000000);

// Custom Flags (per type)
define('SPELL_CU_TALENT',                   0x0001);        // passive talent
define('SPELL_CU_TALENTSPELL',              0x0002);        // ability taught by talent
define('SPELL_CU_TRIGGERED',                0x0004);        // triggered by another spell
define('SPELL_CU_PET_TALENT_TYPE0',         0x0008);        // Ferocity
define('SPELL_CU_PET_TALENT_TYPE1',         0x0010);        // Tenacity
define('SPELL_CU_PET_TALENT_TYPE2',         0x0020);        // Cunning
define('SPELL_CU_GLYPH_MAJOR',              0x0040);
define('SPELL_CU_GLYPH_MINOR',              0x0080);
define('SPELL_CU_QUALITY_MASK',             0x0F00);        // set if spell creates an item: (7 - Quality) << 8
define('SPELL_CU_EXCLUDE_CATEGORY_SEARCH',  0x1000);        // only display, when searching for spells in general (!cat || cat = 0)
define('SPELL_CU_FIRST_RANK',               0x2000);        // used by filter
define('SPELL_CU_LAST_RANK',                0x4000);

define('OBJECT_CU_DESTRUCTABLE',            0x01);
define('OBJECT_CU_CHECK_LOS',               0x02);
define('OBJECT_CU_INTERACT_MOUNTED',        0x04);
define('OBJECT_CU_INTERACT_COMBAT',         0x08);
define('OBJECT_CU_APPLY_GROUP_LOOT',        0x10);
define('OBJECT_CU_STEALTHED',               0x20);
define('OBJECT_CU_CASTER_GROUPED',          0x40);
define('OBJECT_CU_NOT_PERSISTANT',          0x80);

define('MAX_LEVEL',                         80);

// Sides
define('SIDE_ALLIANCE',                     1);
define('SIDE_HORDE',                        2);
define('SIDE_BOTH',                         3);

// ClassMask
define('CLASS_WARRIOR',                     0x001);
define('CLASS_PALADIN',                     0x002);
define('CLASS_HUNTER',                      0x004);
define('CLASS_ROGUE',                       0x008);
define('CLASS_PRIEST',                      0x010);
define('CLASS_DEATHKNIGHT',                 0x020);
define('CLASS_SHAMAN',                      0x040);
define('CLASS_MAGE',                        0x080);
define('CLASS_WARLOCK',                     0x100);
define('CLASS_DRUID',                       0x400);
define('CLASS_MASK_ALL',                    0x5FF);

// RaceMask
define('RACE_HUMAN',                        0x001);
define('RACE_ORC',                          0x002);
define('RACE_DWARF',                        0x004);
define('RACE_NIGHTELF',                     0x008);
define('RACE_UNDEAD',                       0x010);
define('RACE_TAUREN',                       0x020);
define('RACE_GNOME',                        0x040);
define('RACE_TROLL',                        0x080);
define('RACE_BLOODELF',                     0x200);
define('RACE_DRAENEI',                      0x400);
define('RACE_MASK_ALLIANCE',                0x44D);
define('RACE_MASK_HORDE',                   0x2B2);
define('RACE_MASK_ALL',                     0x6FF);

// SpellFamilyNames
define('SPELLFAMILY_GENERIC',               0);
define('SPELLFAMILY_UNK1',                  1);             // events, holidays
define('SPELLFAMILY_MAGE',                  3);
define('SPELLFAMILY_WARRIOR',               4);
define('SPELLFAMILY_WARLOCK',               5);
define('SPELLFAMILY_PRIEST',                6);
define('SPELLFAMILY_DRUID',                 7);
define('SPELLFAMILY_ROGUE',                 8);
define('SPELLFAMILY_HUNTER',                9);
define('SPELLFAMILY_PALADIN',               10);
define('SPELLFAMILY_SHAMAN',                11);
define('SPELLFAMILY_UNK2',                  12);            // 2 spells (silence resistance)
define('SPELLFAMILY_POTION',                13);
define('SPELLFAMILY_DEATHKNIGHT',           15);
define('SPELLFAMILY_PET',                   17);

// Gender
define('GENDER_MALE',                       0);
define('GENDER_FEMALE',                     1);
define('GENDER_NONE',                       2);

// ReputationRank
define('REP_HATED',                         0);
define('REP_HOSTILE',                       1);
define('REP_UNFRIENDLY',                    2);
define('REP_NEUTRAL',                       3);
define('REP_FRIENDLY',                      4);
define('REP_HONORED',                       5);
define('REP_REVERED',                       6);
define('REP_EXALTED',                       7);

// Stats
define('STAT_STRENGTH',                     0);
define('STAT_AGILITY',                      1);
define('STAT_STAMINA',                      2);
define('STAT_INTELLECT',                    3);
define('STAT_SPIRIT',                       4);

// Powers
define('POWER_MANA',                        0);
define('POWER_RAGE',                        1);
define('POWER_FOCUS',                       2);
define('POWER_ENERGY',                      3);
define('POWER_HAPPINESS',                   4);
define('POWER_RUNE',                        5);
define('POWER_RUNIC_POWER',                 6);
define('POWER_HEALTH',                     -2);             // (-2 as signed value)

// SpellSchools
define('SPELL_SCHOOL_NORMAL',               0);
define('SPELL_SCHOOL_HOLY',                 1);
define('SPELL_SCHOOL_FIRE',                 2);
define('SPELL_SCHOOL_NATURE',               3);
define('SPELL_SCHOOL_FROST',                4);
define('SPELL_SCHOOL_SHADOW',               5);
define('SPELL_SCHOOL_ARCANE',               6);
define('SPELL_ALL_SCHOOLS',                 0x7F);

// CharacterSlot
define('SLOT_HEAD',                         0);
define('SLOT_NECK',                         1);
define('SLOT_SHOULDERS',                    2);
define('SLOT_SHIRT',                        3);
define('SLOT_CHEST',                        4);
define('SLOT_WAIST',                        5);
define('SLOT_LEGS',                         6);
define('SLOT_FEET',                         7);
define('SLOT_WRISTS',                       8);
define('SLOT_HANDS',                        9);
define('SLOT_FINGER1',                      10);
define('SLOT_FINGER2',                      11);
define('SLOT_TRINKET1',                     12);
define('SLOT_TRINKET2',                     13);
define('SLOT_BACK',                         14);
define('SLOT_MAIN_HAND',                    15);
define('SLOT_OFF_HAND',                     16);
define('SLOT_RANGED',                       17);
define('SLOT_TABARD',                       18);
define('SLOT_EMPTY',                        19);

// Language
define('LANG_UNIVERSAL',                    0);
define('LANG_ORCISH',                       1);
define('LANG_DARNASSIAN',                   2);
define('LANG_TAURAHE',                      3);
define('LANG_DWARVISH',                     6);
define('LANG_COMMON',                       7);
define('LANG_DEMONIC',                      8);
define('LANG_TITAN',                        9);
define('LANG_THALASSIAN',                   10);
define('LANG_DRACONIC',                     11);
define('LANG_KALIMAG',                      12);
define('LANG_GNOMISH',                      13);
define('LANG_TROLL',                        14);
define('LANG_GUTTERSPEAK',                  33);
define('LANG_DRAENEI',                      35);
define('LANG_ZOMBIE',                       36);
define('LANG_GNOMISH_BINARY',               37);
define('LANG_GOBLIN_BINARY',                38);

// TeamId
define('TEAM_ALLIANCE',                     0);
define('TEAM_HORDE',                        1);
define('TEAM_NEUTRAL',                      2);

// Lock-Properties (also categorizes GOs)
define('LOCK_PROPERTY_FOOTLOCKER',          1);
define('LOCK_PROPERTY_HERBALISM',           2);
define('LOCK_PROPERTY_MINING',              3);

// GameObject
define('OBJECT_DOOR',                       0);
define('OBJECT_BUTTON',                     1);
define('OBJECT_QUESTGIVER',                 2);
define('OBJECT_CHEST',                      3);
define('OBJECT_BINDER',                     4);
define('OBJECT_GENERIC',                    5);
define('OBJECT_TRAP',                       6);
define('OBJECT_CHAIR',                      7);
define('OBJECT_SPELL_FOCUS',                8);
define('OBJECT_TEXT',                       9);
define('OBJECT_GOOBER',                     10);
define('OBJECT_TRANSPORT',                  11);
define('OBJECT_AREADAMAGE',                 12);
define('OBJECT_CAMERA',                     13);
define('OBJECT_MAP_OBJECT',                 14);
define('OBJECT_MO_TRANSPORT',               15);
define('OBJECT_DUEL_ARBITER',               16);
define('OBJECT_FISHINGNODE',                17);
define('OBJECT_RITUAL',                     18);
define('OBJECT_MAILBOX',                    19);
define('OBJECT_AUCTIONHOUSE',               20);
define('OBJECT_GUARDPOST',                  21);
define('OBJECT_SPELLCASTER',                22);
define('OBJECT_MEETINGSTONE',               23);
define('OBJECT_FLAGSTAND',                  24);
define('OBJECT_FISHINGHOLE',                25);
define('OBJECT_FLAGDROP',                   26);
define('OBJECT_MINI_GAME',                  27);
define('OBJECT_LOTTERY_KIOSK',              28);
define('OBJECT_CAPTURE_POINT',              29);
define('OBJECT_AURA_GENERATOR',             30);
define('OBJECT_DUNGEON_DIFFICULTY',         31);
define('OBJECT_BARBER_CHAIR',               32);
define('OBJECT_DESTRUCTIBLE_BUILDING',      33);
define('OBJECT_GUILD_BANK',                 34);
define('OBJECT_TRAPDOOR',                   35);

// InventoryType
define('INVTYPE_NON_EQUIP',                 0);
define('INVTYPE_HEAD',                      1);
define('INVTYPE_NECK',                      2);
define('INVTYPE_SHOULDERS',                 3);
define('INVTYPE_BODY',                      4);
define('INVTYPE_CHEST',                     5);
define('INVTYPE_WAIST',                     6);
define('INVTYPE_LEGS',                      7);
define('INVTYPE_FEET',                      8);
define('INVTYPE_WRISTS',                    9);
define('INVTYPE_HANDS',                     10);
define('INVTYPE_FINGER',                    11);
define('INVTYPE_TRINKET',                   12);
define('INVTYPE_WEAPON',                    13);
define('INVTYPE_SHIELD',                    14);
define('INVTYPE_RANGED',                    15);
define('INVTYPE_CLOAK',                     16);
define('INVTYPE_2HWEAPON',                  17);
define('INVTYPE_BAG',                       18);
define('INVTYPE_TABARD',                    19);
define('INVTYPE_ROBE',                      20);
define('INVTYPE_WEAPONMAINHAND',            21);
define('INVTYPE_WEAPONOFFHAND',             22);
define('INVTYPE_HOLDABLE',                  23);
define('INVTYPE_AMMO',                      24);
define('INVTYPE_THROWN',                    25);
define('INVTYPE_RANGEDRIGHT',               26);
define('INVTYPE_QUIVER',                    27);
define('INVTYPE_RELIC',                     28);

// loot modes for creatures and gameobjects, bitmask!
define('LOOT_MODE_DEFAULT',                 1);
define('LOOT_MODE_HARD_MODE_1',             2);
define('LOOT_MODE_HARD_MODE_2',             4);
define('LOOT_MODE_HARD_MODE_3',             8);
define('LOOT_MODE_HARD_MODE_4',             16);

// ItemQualities
define('ITEM_QUALITY_POOR',                 0);             // GREY
define('ITEM_QUALITY_NORMAL',               1);             // WHITE
define('ITEM_QUALITY_UNCOMMON',             2);             // GREEN
define('ITEM_QUALITY_RARE',                 3);             // BLUE
define('ITEM_QUALITY_EPIC',                 4);             // PURPLE
define('ITEM_QUALITY_LEGENDARY',            5);             // ORANGE
define('ITEM_QUALITY_ARTIFACT',             6);             // LIGHT YELLOW
define('ITEM_QUALITY_HEIRLOOM',             7);             // GOLD

// ItemClass
define('ITEM_CLASS_WEAPON',                 2);
define('ITEM_CLASS_ARMOR',                  4);
define('ITEM_CLASS_AMMUNITION',             6);
define('ITEM_CLASS_RECIPE',                 9);

// ItemFlags
define('ITEM_FLAG_CONJURED',                0x0000002);
define('ITEM_FLAG_HEROIC',                  0x0000008);
define('ITEM_FLAG_DEPRECATED',              0x0000010);
define('ITEM_FLAG_PARTYLOOT',               0x0000800);
define('ITEM_FLAG_REFUNDABLE',              0x0001000);
define('ITEM_FLAG_UNIQUEEQUIPPED',          0x0080000);
define('ITEM_FLAG_ACCOUNTBOUND',            0x8000000);

// ItemMod  (differ slightly from client, see g_statToJson)
define('ITEM_MOD_WEAPON_DMG',               0);             // < custom
define('ITEM_MOD_MANA',                     1);
define('ITEM_MOD_HEALTH',                   2);
define('ITEM_MOD_AGILITY',                  3);             // stats v
define('ITEM_MOD_STRENGTH',                 4);
define('ITEM_MOD_INTELLECT',                5);
define('ITEM_MOD_SPIRIT',                   6);
define('ITEM_MOD_STAMINA',                  7);
define('ITEM_MOD_ENERGY' ,                  8);             // powers v
define('ITEM_MOD_RAGE' ,                    9);
define('ITEM_MOD_FOCUS' ,                   10);
define('ITEM_MOD_RUNIC_POWER' ,             11);
define('ITEM_MOD_DEFENSE_SKILL_RATING',     12);            // ratings v
define('ITEM_MOD_DODGE_RATING',             13);
define('ITEM_MOD_PARRY_RATING',             14);
define('ITEM_MOD_BLOCK_RATING',             15);
define('ITEM_MOD_HIT_MELEE_RATING',         16);
define('ITEM_MOD_HIT_RANGED_RATING',        17);
define('ITEM_MOD_HIT_SPELL_RATING',         18);
define('ITEM_MOD_CRIT_MELEE_RATING',        19);
define('ITEM_MOD_CRIT_RANGED_RATING',       20);
define('ITEM_MOD_CRIT_SPELL_RATING',        21);
define('ITEM_MOD_HIT_TAKEN_MELEE_RATING',   22);
define('ITEM_MOD_HIT_TAKEN_RANGED_RATING',  23);
define('ITEM_MOD_HIT_TAKEN_SPELL_RATING',   24);
define('ITEM_MOD_CRIT_TAKEN_MELEE_RATING',  25);
define('ITEM_MOD_CRIT_TAKEN_RANGED_RATING', 26);
define('ITEM_MOD_CRIT_TAKEN_SPELL_RATING',  27);
define('ITEM_MOD_HASTE_MELEE_RATING',       28);
define('ITEM_MOD_HASTE_RANGED_RATING',      29);
define('ITEM_MOD_HASTE_SPELL_RATING',       30);
define('ITEM_MOD_HIT_RATING',               31);
define('ITEM_MOD_CRIT_RATING',              32);
define('ITEM_MOD_HIT_TAKEN_RATING',         33);
define('ITEM_MOD_CRIT_TAKEN_RATING',        34);
define('ITEM_MOD_RESILIENCE_RATING',        35);
define('ITEM_MOD_HASTE_RATING',             36);
define('ITEM_MOD_EXPERTISE_RATING',         37);
define('ITEM_MOD_ATTACK_POWER',             38);
define('ITEM_MOD_RANGED_ATTACK_POWER',      39);
define('ITEM_MOD_FERAL_ATTACK_POWER',       40);
define('ITEM_MOD_SPELL_HEALING_DONE',       41);            // deprecated
define('ITEM_MOD_SPELL_DAMAGE_DONE',        42);            // deprecated
define('ITEM_MOD_MANA_REGENERATION',        43);
define('ITEM_MOD_ARMOR_PENETRATION_RATING', 44);
define('ITEM_MOD_SPELL_POWER',              45);
define('ITEM_MOD_HEALTH_REGEN',             46);
define('ITEM_MOD_SPELL_PENETRATION',        47);
define('ITEM_MOD_BLOCK_VALUE',              48);
define('ITEM_MOD_ARMOR',                    50);            // resistances v
define('ITEM_MOD_FIRE_RESISTANCE',          51);
define('ITEM_MOD_FROST_RESISTANCE',         52);
define('ITEM_MOD_HOLY_RESISTANCE',          53);
define('ITEM_MOD_SHADOW_RESISTANCE',        54);
define('ITEM_MOD_NATURE_RESISTANCE',        55);
define('ITEM_MOD_ARCANE_RESISTANCE',        56);            // custom v
define('ITEM_MOD_FIRE_POWER',               57);
define('ITEM_MOD_FROST_POWER',              58);
define('ITEM_MOD_HOLY_POWER',               59);
define('ITEM_MOD_SHADOW_POWER',             60);
define('ITEM_MOD_NATURE_POWER',             61);
define('ITEM_MOD_ARCANE_POWER',             62);

// AchievementCriteriaCondition
define('ACHIEVEMENT_CRITERIA_CONDITION_NO_DEATH',                       1);         // reset progress on death
define('ACHIEVEMENT_CRITERIA_CONDITION_BG_MAP',                         3);         // requires you to be on specific map, reset at change
define('ACHIEVEMENT_CRITERIA_CONDITION_NOT_IN_GROUP',                   10);        // requires the player not to be in group

// AchievementFlags
define('ACHIEVEMENT_FLAG_COUNTER',                                      0x0001);    // Just count statistic (never stop and complete)
define('ACHIEVEMENT_FLAG_HIDDEN',                                       0x0002);    // Not sent to client - internal use only
define('ACHIEVEMENT_FLAG_STORE_MAX_VALUE',                              0x0004);    // Store only max value? used only in "Reach level xx"
define('ACHIEVEMENT_FLAG_SUMM',                                         0x0008);    // Use summ criteria value from all reqirements (and calculate max value)
define('ACHIEVEMENT_FLAG_MAX_USED',                                     0x0010);    // Show max criteria (and calculate max value ??)
define('ACHIEVEMENT_FLAG_REQ_COUNT',                                    0x0020);    // Use not zero req count (and calculate max value)
define('ACHIEVEMENT_FLAG_AVERAGE',                                      0x0040);    // Show as average value (value / time_in_days) depend from other flag (by def use last criteria value)
define('ACHIEVEMENT_FLAG_BAR',                                          0x0080);    // Show as progress bar (value / max vale) depend from other flag (by def use last criteria value)

// AchievementCriteriaFlags
define('ACHIEVEMENT_CRITERIA_FLAG_SHOW_PROGRESS_BAR',                   0x0001);    // Show progress as bar
define('ACHIEVEMENT_CRITERIA_FLAG_HIDDEN',                              0x0002);    // Not show criteria in client
define('ACHIEVEMENT_CRITERIA_FLAG_MONEY_COUNTER',                       0x0020);    // Displays counter as money

// Commented ones solved generically
define('ACHIEVEMENT_CRITERIA_TYPE_KILL_CREATURE',                       0);
define('ACHIEVEMENT_CRITERIA_TYPE_WIN_BG',                              1);
define('ACHIEVEMENT_CRITERIA_TYPE_REACH_LEVEL',                         5);
define('ACHIEVEMENT_CRITERIA_TYPE_REACH_SKILL_LEVEL',                   7);
define('ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_ACHIEVEMENT',                8);
// define('ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_QUEST_COUNT',             9);
// define('ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_DAILY_QUEST_DAILY',       10);
define('ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_QUESTS_IN_ZONE',             11);
// define('ACHIEVEMENT_CRITERIA_TYPE_DAMAGE_DONE',                      13);
// define('ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_DAILY_QUEST',             14);
define('ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_BATTLEGROUND',               15);
define('ACHIEVEMENT_CRITERIA_TYPE_DEATH_AT_MAP',                        16);
// define('ACHIEVEMENT_CRITERIA_TYPE_DEATH',                            17);
// define('ACHIEVEMENT_CRITERIA_TYPE_DEATH_IN_DUNGEON',                 18);
// define('ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_RAID',                    19);
define('ACHIEVEMENT_CRITERIA_TYPE_KILLED_BY_CREATURE',                  20);
// define('ACHIEVEMENT_CRITERIA_TYPE_KILLED_BY_PLAYER',                 23);
// define('ACHIEVEMENT_CRITERIA_TYPE_FALL_WITHOUT_DYING',               24);
// define('ACHIEVEMENT_CRITERIA_TYPE_DEATHS_FROM',                      26);
define('ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_QUEST',                      27);
define('ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET',                     28);
define('ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL',                          29);
// define('ACHIEVEMENT_CRITERIA_TYPE_BG_OBJECTIVE_CAPTURE',             30);
define('ACHIEVEMENT_CRITERIA_TYPE_HONORABLE_KILL_AT_AREA',              31);
define('ACHIEVEMENT_CRITERIA_TYPE_WIN_ARENA',                           32);
define('ACHIEVEMENT_CRITERIA_TYPE_PLAY_ARENA',                          33);
define('ACHIEVEMENT_CRITERIA_TYPE_LEARN_SPELL',                         34);
// define('ACHIEVEMENT_CRITERIA_TYPE_HONORABLE_KILL',                   35);
define('ACHIEVEMENT_CRITERIA_TYPE_OWN_ITEM',                            36);
// define('ACHIEVEMENT_CRITERIA_TYPE_WIN_RATED_ARENA',                  37);
// define('ACHIEVEMENT_CRITERIA_TYPE_HIGHEST_TEAM_RATING',              38);
// define('ACHIEVEMENT_CRITERIA_TYPE_REACH_TEAM_RATING',                39);
define('ACHIEVEMENT_CRITERIA_TYPE_LEARN_SKILL_LEVEL',                   40);
define('ACHIEVEMENT_CRITERIA_TYPE_USE_ITEM',                            41);
define('ACHIEVEMENT_CRITERIA_TYPE_LOOT_ITEM',                           42);
define('ACHIEVEMENT_CRITERIA_TYPE_EXPLORE_AREA',                        43);
// define('ACHIEVEMENT_CRITERIA_TYPE_OWN_RANK',                         44);
// define('ACHIEVEMENT_CRITERIA_TYPE_BUY_BANK_SLOT',                    45);
define('ACHIEVEMENT_CRITERIA_TYPE_GAIN_REPUTATION',                     46);
// define('ACHIEVEMENT_CRITERIA_TYPE_GAIN_EXALTED_REPUTATION',          47);
// define('ACHIEVEMENT_CRITERIA_TYPE_VISIT_BARBER_SHOP',                48);
// define('ACHIEVEMENT_CRITERIA_TYPE_EQUIP_EPIC_ITEM',                  49);
// define('ACHIEVEMENT_CRITERIA_TYPE_ROLL_NEED_ON_LOOT',                50);
// define('ACHIEVEMENT_CRITERIA_TYPE_ROLL_GREED_ON_LOOT',               51);
define('ACHIEVEMENT_CRITERIA_TYPE_HK_CLASS',                            52);
define('ACHIEVEMENT_CRITERIA_TYPE_HK_RACE',                             53);
// define('ACHIEVEMENT_CRITERIA_TYPE_DO_EMOTE',                         54);
// define('ACHIEVEMENT_CRITERIA_TYPE_HEALING_DONE',                     55);
// define('ACHIEVEMENT_CRITERIA_TYPE_GET_KILLING_BLOWS',                56);
define('ACHIEVEMENT_CRITERIA_TYPE_EQUIP_ITEM',                          57);
// define('ACHIEVEMENT_CRITERIA_TYPE_MONEY_FROM_VENDORS',               59);
// define('ACHIEVEMENT_CRITERIA_TYPE_GOLD_SPENT_FOR_TALENTS',           60);
// define('ACHIEVEMENT_CRITERIA_TYPE_NUMBER_OF_TALENT_RESETS',          61);
// define('ACHIEVEMENT_CRITERIA_TYPE_MONEY_FROM_QUEST_REWARD',          62);
// define('ACHIEVEMENT_CRITERIA_TYPE_GOLD_SPENT_FOR_TRAVELLING',        63);
// define('ACHIEVEMENT_CRITERIA_TYPE_GOLD_SPENT_AT_BARBER',             65);
// define('ACHIEVEMENT_CRITERIA_TYPE_GOLD_SPENT_FOR_MAIL',              66);
// define('ACHIEVEMENT_CRITERIA_TYPE_LOOT_MONEY',                       67);
define('ACHIEVEMENT_CRITERIA_TYPE_USE_GAMEOBJECT',                      68);
define('ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET2',                    69);
// define('ACHIEVEMENT_CRITERIA_TYPE_SPECIAL_PVP_KILL',                 70);
define('ACHIEVEMENT_CRITERIA_TYPE_FISH_IN_GAMEOBJECT',                  72);
define('ACHIEVEMENT_CRITERIA_TYPE_EARNED_PVP_TITLE',                    74);
define('ACHIEVEMENT_CRITERIA_TYPE_LEARN_SKILLLINE_SPELLS',              75);
// define('ACHIEVEMENT_CRITERIA_TYPE_WIN_DUEL',                         76);
// define('ACHIEVEMENT_CRITERIA_TYPE_LOSE_DUEL',                        77);
define('ACHIEVEMENT_CRITERIA_TYPE_KILL_CREATURE_TYPE',                  78);
// define('ACHIEVEMENT_CRITERIA_TYPE_GOLD_EARNED_BY_AUCTIONS',          80);
// define('ACHIEVEMENT_CRITERIA_TYPE_CREATE_AUCTION',                   82);
// define('ACHIEVEMENT_CRITERIA_TYPE_HIGHEST_AUCTION_BID',              83);
// define('ACHIEVEMENT_CRITERIA_TYPE_WON_AUCTIONS',                     84);
// define('ACHIEVEMENT_CRITERIA_TYPE_HIGHEST_AUCTION_SOLD',             85);
// define('ACHIEVEMENT_CRITERIA_TYPE_HIGHEST_GOLD_VALUE_OWNED',         86);
// define('ACHIEVEMENT_CRITERIA_TYPE_GAIN_REVERED_REPUTATION',          87);
// define('ACHIEVEMENT_CRITERIA_TYPE_GAIN_HONORED_REPUTATION',          88);
// define('ACHIEVEMENT_CRITERIA_TYPE_KNOWN_FACTIONS',                   89);
// define('ACHIEVEMENT_CRITERIA_TYPE_LOOT_EPIC_ITEM',                   90);
// define('ACHIEVEMENT_CRITERIA_TYPE_RECEIVE_EPIC_ITEM',                91);
// define('ACHIEVEMENT_CRITERIA_TYPE_ROLL_NEED',                        93);
// define('ACHIEVEMENT_CRITERIA_TYPE_ROLL_GREED',                       94);
// define('ACHIEVEMENT_CRITERIA_TYPE_HIGHEST_HEALTH',                   95);
// define('ACHIEVEMENT_CRITERIA_TYPE_HIGHEST_POWER',                    96);
// define('ACHIEVEMENT_CRITERIA_TYPE_HIGHEST_STAT',                     97);
// define('ACHIEVEMENT_CRITERIA_TYPE_HIGHEST_SPELLPOWER',               98);
// define('ACHIEVEMENT_CRITERIA_TYPE_HIGHEST_ARMOR',                    99);
// define('ACHIEVEMENT_CRITERIA_TYPE_HIGHEST_RATING',                   100);
// define('ACHIEVEMENT_CRITERIA_TYPE_HIGHEST_HIT_DEALT',                101);
// define('ACHIEVEMENT_CRITERIA_TYPE_HIGHEST_HIT_RECEIVED',             102);
// define('ACHIEVEMENT_CRITERIA_TYPE_TOTAL_DAMAGE_RECEIVED',            103);
// define('ACHIEVEMENT_CRITERIA_TYPE_HIGHEST_HEAL_CASTED',              104);
// define('ACHIEVEMENT_CRITERIA_TYPE_TOTAL_HEALING_RECEIVED',           105);
// define('ACHIEVEMENT_CRITERIA_TYPE_HIGHEST_HEALING_RECEIVED',         106);
// define('ACHIEVEMENT_CRITERIA_TYPE_QUEST_ABANDONED',                  107);
// define('ACHIEVEMENT_CRITERIA_TYPE_FLIGHT_PATHS_TAKEN',               108);
// define('ACHIEVEMENT_CRITERIA_TYPE_LOOT_TYPE',                        109);
define('ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL2',                         110);
define('ACHIEVEMENT_CRITERIA_TYPE_LEARN_SKILL_LINE',                    112);
// define('ACHIEVEMENT_CRITERIA_TYPE_EARN_HONORABLE_KILL',              113);
// define('ACHIEVEMENT_CRITERIA_TYPE_ACCEPTED_SUMMONINGS',              114);
// define('ACHIEVEMENT_CRITERIA_TYPE_DISENCHANT_ROLLS',                 117);
// define('ACHIEVEMENT_CRITERIA_TYPE_USE_LFD_TO_GROUP_WITH_PLAYERS',    119);

?>
