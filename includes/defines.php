<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/*
 * Page
 */

define('E_AOWOW',                           E_ALL & ~(E_DEPRECATED | E_USER_DEPRECATED | E_STRICT));

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
define('TYPE_SOUND',                        19);
define('TYPE_ICON',                         29);
// internal types (not published to js)
define('TYPE_USER',                         500);
define('TYPE_EMOTE',                        501);
define('TYPE_ENCHANTMENT',                  502);
define('TYPE_AREATRIGGER',                  503);           // not for display, but indexing in ?_spawns-table

define('CACHE_TYPE_NONE',                   0);             // page will not be cached
define('CACHE_TYPE_PAGE',                   1);
define('CACHE_TYPE_TOOLTIP',                2);
define('CACHE_TYPE_SEARCH',                 3);
define('CACHE_TYPE_XML',                    4);             // only used by items

define('CACHE_MODE_FILECACHE',              0x1);
define('CACHE_MODE_MEMCACHED',              0x2);

define('SEARCH_TYPE_REGULAR',               0x10000000);
define('SEARCH_TYPE_OPEN',                  0x20000000);
define('SEARCH_TYPE_JSON',                  0x40000000);
define('SEARCH_MASK_OPEN',                  0x007DC1FF);    // open search
define('SEARCH_MASK_ALL',                   0x0FFFFFFF);    // normal search

// Databases
define('DB_AOWOW',                          0);
define('DB_WORLD',                          1);
define('DB_AUTH',                           2);
define('DB_CHARACTERS',                     3);

// Account Status
define('ACC_STATUS_OK',                     0);             // nothing special
define('ACC_STATUS_NEW',                    1);             // just created, awaiting confirmation
define('ACC_STATUS_RECOVER_USER',           2);             // currently recovering username
define('ACC_STATUS_RECOVER_PASS',           3);             // currently recovering password

define('ACC_BAN_NONE',                      0x00);          // all clear
define('ACC_BAN_TEMP',                      0x01);
define('ACC_BAN_PERM',                      0x02);
define('ACC_BAN_RATE',                      0x04);          // cannot rate community items (overrides site reputation)
define('ACC_BAN_COMMENT',                   0x08);          // cannot comment and reply
define('ACC_BAN_UPLOAD',                    0x10);          // cannot upload avatar / signature files [originally: ban from data upload]
define('ACC_BAN_SCREENSHOT',                0x20);          // cannot upload screenshots
define('ACC_BAN_VIDEO',                     0x40);          // cannot suggest videos
// define('ACC_BAN_FORUM',                  0x80);          // cannot use forums [not used here]

// Site Reputation/Privileges
define('SITEREP_ACTION_REGISTER',           1);             // Registered account
define('SITEREP_ACTION_DAILYVISIT',         2);             // Daily visit
define('SITEREP_ACTION_COMMENT',            3);             // Posted comment
define('SITEREP_ACTION_UPVOTED',            4);             // Your comment was upvoted
define('SITEREP_ACTION_DOWNVOTED',          5);             // Your comment was downvoted
define('SITEREP_ACTION_UPLOAD',             6);             // Submitted screenshot (suggested video)
                                                            // Cast vote
                                                            // Uploaded data
define('SITEREP_ACTION_GOOD_REPORT',        9);             // Report accepted
define('SITEREP_ACTION_BAD_REPORT',         10);            // Report declined
                                                            // Copper Achievement
                                                            // Silver Achievement
                                                            // Gold Achievement
                                                            // Test 1
                                                            // Test 2
define('SITEREP_ACTION_ARTICLE',            16);            // Guide approved (article approved)
define('SITEREP_ACTION_USER_WARNED',        17);            // Moderator Warning
define('SITEREP_ACTION_USER_SUSPENDED',     18);            // Moderator Suspension

// config flags
define('CON_FLAG_TYPE_INT',                 0x01);          // validate with intVal()
define('CON_FLAG_TYPE_FLOAT',               0x02);          // validate with floatVal()
define('CON_FLAG_TYPE_BOOL',                0x04);          // 0 || 1
define('CON_FLAG_TYPE_STRING',              0x08);          //
define('CON_FLAG_OPT_LIST',                 0x10);          // single option
define('CON_FLAG_BITMASK',                  0x20);          // multiple options
define('CON_FLAG_PHP',                      0x40);          // applied with ini_set() [restrictions apply!]
define('CON_FLAG_PERSISTENT',               0x80);          // can not be deleted

// Auth Result
define('AUTH_OK',                           0);
define('AUTH_WRONGUSER',                    1);
define('AUTH_WRONGPASS',                    2);
define('AUTH_BANNED',                       3);
define('AUTH_IPBANNED',                     4);
define('AUTH_ACC_INACTIVE',                 5);
define('AUTH_INTERNAL_ERR',                 6);

define('AUTH_MODE_SELF',                    0);             // uses ?_accounts
define('AUTH_MODE_REALM',                   1);             // uses given realm-table
define('AUTH_MODE_EXTERNAL',                2);             // uses external script

// Times
define('MINUTE',                            60);
define('HOUR',                              60  * MINUTE);
define('DAY',                               24  * HOUR);
define('WEEK',                              7   * DAY);
define('MONTH',                             30  * DAY);
define('YEAR',                              364 * DAY);

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
define('U_GROUP_SCREENSHOT',                0x0800);
define('U_GROUP_VIDEO',                     0x1000);
define('U_GROUP_APIONLY',                   0x2000);        // not used
define('U_GROUP_PENDING',                   0x4000);        // restricts usage of urls in comments

define('U_GROUP_STAFF',                     (U_GROUP_ADMIN|U_GROUP_EDITOR|U_GROUP_MOD|U_GROUP_BUREAU|U_GROUP_DEV|U_GROUP_BLOGGER|U_GROUP_LOCALIZER|U_GROUP_SALESAGENT));
define('U_GROUP_EMPLOYEE',                  (U_GROUP_ADMIN|U_GROUP_BUREAU|U_GROUP_DEV));
define('U_GROUP_GREEN_TEXT',                (U_GROUP_MOD|U_GROUP_BUREAU|U_GROUP_DEV));
define('U_GROUP_PREMIUMISH',                (U_GROUP_PREMIUM|U_GROUP_EDITOR));
define('U_GROUP_MODERATOR',                 (U_GROUP_ADMIN|U_GROUP_MOD|U_GROUP_BUREAU));
define('U_GROUP_COMMENTS_MODERATOR',        (U_GROUP_MODERATOR|U_GROUP_LOCALIZER));
define('U_GROUP_PREMIUM_PERMISSIONS',       (U_GROUP_PREMIUM|U_GROUP_STAFF|U_GROUP_VIP));

// Locales
define('LOCALE_EN',                         0);
define('LOCALE_FR',                         2);
define('LOCALE_DE',                         3);
define('LOCALE_CN',                         4);
define('LOCALE_ES',                         6);
define('LOCALE_RU',                         8);

// red buttons on the top of the page
define('BUTTON_WOWHEAD',                    0);
define('BUTTON_UPGRADE',                    1);
define('BUTTON_COMPARE',                    2);
define('BUTTON_VIEW3D',                     3);
define('BUTTON_LINKS',                      4);
define('BUTTON_FORUM',                      5);
define('BUTTON_TALENT',                     6);
define('BUTTON_EQUIP',                      7);
define('BUTTON_PLAYLIST',                   8);

// generic filter handler
define('FILTER_CR_BOOLEAN',                 1);
define('FILTER_CR_FLAG',                    2);
define('FILTER_CR_NUMERIC',                 3);
define('FILTER_CR_STRING',                  4);
define('FILTER_CR_ENUM',                    5);
define('FILTER_CR_STAFFFLAG',               6);
define('FILTER_CR_CALLBACK',                7);
define('FILTER_CR_NYI_PH',                  999);
define('FILTER_V_EQUAL',                    8);
define('FILTER_V_RANGE',                    9);
define('FILTER_V_LIST',                     10);
define('FILTER_V_CALLBACK',                 11);
define('FILTER_V_REGEX',                    12);

define('FILTER_ENUM_ANY',                   -2323);
define('FILTER_ENUM_NONE',                  -2324);

// conditional information in template
define('GLOBALINFO_SELF',                   0x1);           // id, name, icon
define('GLOBALINFO_RELATED',                0x2);           // spells used by pet, classes/races required by spell, ect
define('GLOBALINFO_REWARDS',                0x4);           // items rewarded by achievement/quest, ect
define('GLOBALINFO_EXTRA',                  0x8);           // items / spells .. sends exra tooltip info to template for js-manipulation
define('GLOBALINFO_ANY',                    0xF);

define('ITEMINFO_JSON',                     0x01);
define('ITEMINFO_SUBITEMS',                 0x02);
define('ITEMINFO_VENDOR',                   0x04);
// define('ITEMINFO_LOOT',                  0x08);          // get these infos from dedicatd loot function [count, stack, pctstack, modes]
define('ITEMINFO_GEM',                      0x10);
define('ITEMINFO_MODEL',                    0x20);

define('NPCINFO_TAMEABLE',                  0x1);
define('NPCINFO_MODEL',                     0x2);
define('NPCINFO_REP',                       0x4);

define('ACHIEVEMENTINFO_PROFILE',           0x1);

define('SPAWNINFO_ZONES',                   1);             // not a mask, mutually exclusive
define('SPAWNINFO_SHORT',                   2);
define('SPAWNINFO_FULL',                    3);
define('SPAWNINFO_QUEST',                   4);

// Community Content
define('CC_FLAG_STICKY',                    0x1);
define('CC_FLAG_DELETED',                   0x2);
define('CC_FLAG_OUTDATED',                  0x4);
define('CC_FLAG_APPROVED',                  0x8);

define('SOUND_TYPE_OGG',                    1);
define('SOUND_TYPE_MP3',                    2);

define('CONTRIBUTE_CO',                     0x1);
define('CONTRIBUTE_SS',                     0x2);
define('CONTRIBUTE_VI',                     0x4);
define('CONTRIBUTE_ANY',                    CONTRIBUTE_CO | CONTRIBUTE_SS | CONTRIBUTE_VI);

define('NUM_ANY',                           0);
define('NUM_CAST_INT',                      1);
define('NUM_CAST_FLOAT',                    2);
define('NUM_REQ_INT',                       3);
define('NUM_REQ_FLOAT',                     4);

/*
 * Game
 */

// Custom Flags (shared)
define('CUSTOM_HAS_COMMENT',                0x01000000);
define('CUSTOM_HAS_SCREENSHOT',             0x02000000);
define('CUSTOM_HAS_VIDEO',                  0x04000000);
define('CUSTOM_DISABLED',                   0x08000000);    // contained in world.disables
define('CUSTOM_SERVERSIDE',                 0x10000000);
define('CUSTOM_UNAVAILABLE',                0x20000000);    // no source for X or questFlag
define('CUSTOM_EXCLUDE_FOR_LISTVIEW',       0x40000000);    // will not show up in search or on listPage (override for staff)

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
define('SPELL_CU_FIRST_RANK',               0x1000);        // used by filter
define('SPELL_CU_LAST_RANK',                0x2000);

define('ACHIEVEMENT_CU_FIRST_SERIES',       0x01);
define('ACHIEVEMENT_CU_LAST_SERIES',        0x02);

define('OBJECT_CU_DESTRUCTABLE',            0x01);
define('OBJECT_CU_CHECK_LOS',               0x02);
define('OBJECT_CU_INTERACT_MOUNTED',        0x04);
define('OBJECT_CU_INTERACT_COMBAT',         0x08);
define('OBJECT_CU_APPLY_GROUP_LOOT',        0x10);
define('OBJECT_CU_STEALTHED',               0x20);
define('OBJECT_CU_CASTER_GROUPED',          0x40);
define('OBJECT_CU_NOT_PERSISTANT',          0x80);

define('NPC_CU_INSTANCE_BOSS',              0x01);
define('NPC_CU_DIFFICULTY_DUMMY',           0x02);

define('ITEM_CU_OT_ITEMLOOT',               0x01);          // there are no sourceTypes for these two cases
define('ITEM_CU_OT_OBJECTLOOT',             0x02);

// as seen in wFlags
define('QUEST_CU_REPEATABLE',               0x01);
define('QUEST_CU_DAILY',                    0x02);
define('QUEST_CU_WEEKLY',                   0x04);
define('QUEST_CU_SEASONAL',                 0x08);
define('QUEST_CU_SKIP_LOG',                 0x10);
define('QUEST_CU_AUTO_ACCEPT',              0x20);
define('QUEST_CU_PVP_ENABLED',              0x40);

define('MAX_LEVEL',                         80);
define('WOW_BUILD',                         12340);

// Loot handles
define('LOOT_FISHING',            'fishing_loot_template');
define('LOOT_CREATURE',          'creature_loot_template');
define('LOOT_GAMEOBJECT',      'gameobject_loot_template');
define('LOOT_ITEM',                  'item_loot_template');
define('LOOT_DISENCHANT',      'disenchant_loot_template');
define('LOOT_PROSPECTING',    'prospecting_loot_template');
define('LOOT_MILLING',            'milling_loot_template');
define('LOOT_PICKPOCKET',   'pickpocketing_loot_template');
define('LOOT_SKINNING',          'skinning_loot_template');
define('LOOT_MAIL',                  'mail_loot_template'); // used by achievements and quests
define('LOOT_SPELL',                'spell_loot_template');
define('LOOT_REFERENCE',        'reference_loot_template');

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

// Creature
define('NPC_TYPEFLAG_HERBLOOT',             0x0100);
define('NPC_TYPEFLAG_MININGLOOT',           0x0200);
define('NPC_TYPEFLAG_ENGINEERLOOT',         0x8000);
define('NPC_TYPEFLAG_SPECIALLOOT',          0x8300);

define('NPC_RANK_NORMAL',                   0);
define('NPC_RANK_ELITE',                    1);
define('NPC_RANK_RARE_ELITE',               2);
define('NPC_RANK_BOSS',                     3);
define('NPC_RANK_RARE',                     4);

define('NPC_FLAG_TRAINER',                  0x00000010);
define('NPC_FLAG_CLASS_TRAINER',            0x00000020);
define('NPC_FLAG_VENDOR',                   0x00000080);
define('NPC_FLAG_REPAIRER',                 0x00001000);
define('NPC_FLAG_FLIGHT_MASTER',            0x00002000);
define('NPC_FLAG_SPIRIT_HEALER',            0x00004000);    // civil
define('NPC_FLAG_SPIRIT_GUIDE',             0x00008000);    // battleground
define('NPC_FLAG_INNKEEPER',                0x00010000);
define('NPC_FLAG_BANKER',                   0x00020000);
define('NPC_FLAG_GUILD_MASTER',             0x00080000);
define('NPC_FLAG_BATTLEMASTER',             0x00100000);
define('NPC_FLAG_AUCTIONEER',               0x00200000);
define('NPC_FLAG_STABLE_MASTER',            0x00400000);

// quest
define('QUEST_FLAG_STAY_ALIVE',             0x00001);
define('QUEST_FLAG_PARTY_ACCEPT',           0x00002);
define('QUEST_FLAG_EXPLORATION',            0x00004);
define('QUEST_FLAG_SHARABLE',               0x00008);
define('QUEST_FLAG_AUTO_REWARDED',          0x00400);
define('QUEST_FLAG_DAILY',                  0x01000);
define('QUEST_FLAG_REPEATABLE',             0x02000);
define('QUEST_FLAG_UNAVAILABLE',            0x04000);
define('QUEST_FLAG_WEEKLY',                 0x08000);
define('QUEST_FLAG_AUTO_COMPLETE',          0x10000);
define('QUEST_FLAG_AUTO_ACCEPT',            0x80000);

define('QUEST_FLAG_SPECIAL_REPEATABLE',     0x01);
define('QUEST_FLAG_SPECIAL_EXT_COMPLETE',   0x02);
define('QUEST_FLAG_SPECIAL_AUTO_ACCEPT',    0x04);
define('QUEST_FLAG_SPECIAL_DUNGEON_FINDER', 0x08);
define('QUEST_FLAG_SPECIAL_MONTHLY',        0x10);
define('QUEST_FLAG_SPECIAL_SPELLCAST',      0x20);          // not documented in wiki! :[

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
define('ITEM_CLASS_CONSUMABLE',             0);
define('ITEM_CLASS_CONTAINER',              1);
define('ITEM_CLASS_WEAPON',                 2);
define('ITEM_CLASS_GEM',                    3);
define('ITEM_CLASS_ARMOR',                  4);
define('ITEM_CLASS_REAGENT',                5);
define('ITEM_CLASS_AMMUNITION',             6);
define('ITEM_CLASS_TRADEGOOD',              7);
define('ITEM_CLASS_GENERIC',                8);
define('ITEM_CLASS_RECIPE',                 9);
define('ITEM_CLASS_MONEY',                  10);
define('ITEM_CLASS_QUIVER',                 11);
define('ITEM_CLASS_QUEST',                  12);
define('ITEM_CLASS_KEY',                    13);
define('ITEM_CLASS_PERMANENT',              14);
define('ITEM_CLASS_MISC',                   15);
define('ITEM_CLASS_GLYPH',                  16);

// ItemFlags
define('ITEM_FLAG_CONJURED',                0x00000002);
define('ITEM_FLAG_OPENABLE',                0x00000004);
define('ITEM_FLAG_HEROIC',                  0x00000008);
define('ITEM_FLAG_DEPRECATED',              0x00000010);
define('ITEM_FLAG_INDESTRUCTIBLE',          0x00000020);
define('ITEM_FLAG_NO_EQUIPCD',              0x00000080);
define('ITEM_FLAG_PARTYLOOT',               0x00000800);
define('ITEM_FLAG_REFUNDABLE',              0x00001000);
define('ITEM_FLAG_PROSPECTABLE',            0x00040000);
define('ITEM_FLAG_UNIQUEEQUIPPED',          0x00080000);
define('ITEM_FLAG_USABLE_ARENA',            0x00200000);
define('ITEM_FLAG_USABLE_SHAPED',           0x00800000);
define('ITEM_FLAG_SMARTLOOT',               0x02000000);
define('ITEM_FLAG_ACCOUNTBOUND',            0x08000000);
define('ITEM_FLAG_MILLABLE',                0x20000000);

// ItemMod  (differ slightly from client, see g_statToJson)
define('ITEM_MOD_WEAPON_DMG',               0);             // < custom
define('ITEM_MOD_MANA',                     1);
define('ITEM_MOD_HEALTH',                   2);
define('ITEM_MOD_AGILITY',                  3);             // stats v
define('ITEM_MOD_STRENGTH',                 4);
define('ITEM_MOD_INTELLECT',                5);
define('ITEM_MOD_SPIRIT',                   6);
define('ITEM_MOD_STAMINA',                  7);
define('ITEM_MOD_ENERGY',                   8);             // powers v
define('ITEM_MOD_RAGE',                     9);
define('ITEM_MOD_FOCUS',                    10);
define('ITEM_MOD_RUNIC_POWER',              11);
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
// ITEM_MOD_MASTERY_RATING, 49
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
define('ACHIEVEMENT_CRITERIA_TYPE_DO_EMOTE',                            54);
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

// TrinityCore - Condition System
define('CND_SRC_CREATURE_LOOT_TEMPLATE',      1);
define('CND_SRC_DISENCHANT_LOOT_TEMPLATE',    2);
define('CND_SRC_FISHING_LOOT_TEMPLATE',       3);
define('CND_SRC_GAMEOBJECT_LOOT_TEMPLATE',    4);
define('CND_SRC_ITEM_LOOT_TEMPLATE',          5);
define('CND_SRC_MAIL_LOOT_TEMPLATE',          6);
define('CND_SRC_MILLING_LOOT_TEMPLATE',       7);
define('CND_SRC_PICKPOCKETING_LOOT_TEMPLATE', 8);
define('CND_SRC_PROSPECTING_LOOT_TEMPLATE',   9);
define('CND_SRC_REFERENCE_LOOT_TEMPLATE',     10);
define('CND_SRC_SKINNING_LOOT_TEMPLATE',      11);
define('CND_SRC_SPELL_LOOT_TEMPLATE',         12);
define('CND_SRC_SPELL_IMPLICIT_TARGET',       13);
define('CND_SRC_GOSSIP_MENU',                 14);
define('CND_SRC_GOSSIP_MENU_OPTION',          15);
define('CND_SRC_CREATURE_TEMPLATE_VEHICLE',   16);
define('CND_SRC_SPELL',                       17);
define('CND_SRC_SPELL_CLICK_EVENT',           18);
define('CND_SRC_QUEST_ACCEPT',                19);
define('CND_SRC_QUEST_SHOW_MARK',             20);
define('CND_SRC_VEHICLE_SPELL',               21);
define('CND_SRC_SMART_EVENT',                 22);
define('CND_SRC_NPC_VENDOR',                  23);
define('CND_SRC_SPELL_PROC',                  24);

define('CND_AURA',            1);                           // aura is applied:         spellId,        UNUSED,     NULL
define('CND_ITEM',            2);                           // owns item:               itemId,         count,      UNUSED
define('CND_ITEM_EQUIPPED',   3);                           // has item equipped:       itemId,         NULL,       NULL
define('CND_ZONEID',          4);                           // is in zone:              areaId,         NULL,       NULL
define('CND_REPUTATION_RANK', 5);                           // reputation status:       factionId,      rankMask,   NULL
define('CND_TEAM',            6);                           // is on team:              teamId,         NULL,       NULL
define('CND_SKILL',           7);                           // has skill:               skillId,        value,      NULL
define('CND_QUESTREWARDED',   8);                           // has finished quest:      questId,        NULL,       NULL
define('CND_QUESTTAKEN',      9);                           // has accepted quest:      questId,        NULL,       NULL
define('CND_DRUNKENSTATE',    10);                          // has drunken status:      stateId,        NULL,       NULL
define('CND_WORLD_STATE',     11);
define('CND_ACTIVE_EVENT',    12);                          // world event is active:   eventId,        NULL,       NULL
define('CND_INSTANCE_INFO',   13);
define('CND_QUEST_NONE',      14);                          // never seen quest:        questId,        NULL,       NULL
define('CND_CLASS',           15);                          // belongs to classes:      classMask,      NULL,       NULL
define('CND_RACE',            16);                          // belongs to races:        raceMask,       NULL,       NULL
define('CND_ACHIEVEMENT',     17);                          // obtained achievement:    achievementId,  NULL,       NULL
define('CND_TITLE',           18);                          // obtained title:          titleId,        NULL,       NULL
define('CND_SPAWNMASK',       19);
define('CND_GENDER',          20);                          // has gender:              genderId,       NULL,       NULL
define('CND_UNIT_STATE',      21);
define('CND_MAPID',           22);                          // is on map:               mapId,          NULL,       NULL
define('CND_AREAID',          23);                          // is in area:              areaId,         NULL,       NULL
define('CND_UNUSED_24',       24);
define('CND_SPELL',           25);                          // knows spell:             spellId,        NULL,       NULL
define('CND_PHASEMASK',       26);                          // is in phase:             phaseMask,      NULL,       NULL
define('CND_LEVEL',           27);                          // player level is..:       level,          operator,   NULL
define('CND_QUEST_COMPLETE',  28);                          // has completed quest:     questId,        NULL,       NULL
define('CND_NEAR_CREATURE',   29);                          // is near creature:        creatureId,     dist,       NULL
define('CND_NEAR_GAMEOBJECT', 30);                          // is near gameObject:      gameObjectId,   dist,       NULL
define('CND_OBJECT_ENTRY',    31);                          // target is ???:           objectType,     id,         NULL
define('CND_TYPE_MASK',       32);                          // target is type:          typeMask,       NULL,       NULL
define('CND_RELATION_TO',     33);
define('CND_REACTION_TO',     34);
define('CND_DISTANCE_TO',     35);                          // distance to target       targetType,     dist,       operator
define('CND_ALIVE',           36);                          // target is alive:         NULL,           NULL,       NULL
define('CND_HP_VAL',          37);                          // targets absolute health: amount,         operator,   NULL
define('CND_HP_PCT',          38);                          // targets relative health: amount,         operator,   NULL
?>
