<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/*
 * Page
 */

define('E_AOWOW',                 E_ALL & ~(E_DEPRECATED | E_USER_DEPRECATED | E_STRICT));
define('JSON_AOWOW_POWER',        JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
define('FILTER_FLAG_STRIP_AOWOW', FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK);

// as of 01.01.2024     https://www.wowhead.com/wotlk/de/spell=40120/{seo}
//                      https://www.wowhead.com/wotlk/es/search=vuelo
define('WOWHEAD_LINK', 'https://www.wowhead.com/wotlk/%s/%s=%s');

define('MIME_TYPE_TEXT',       'Content-Type: text/plain; charset=utf-8');
define('MIME_TYPE_XML',        'Content-Type: text/xml; charset=utf-8');
define('MIME_TYPE_JSON',       'Content-Type: application/x-javascript; charset=utf-8');
define('MIME_TYPE_OPENSEARCH', 'Content-Type: application/x-suggestions+json; charset=utf-8');
define('MIME_TYPE_RSS',        'Content-Type: application/rss+xml; charset=utf-8');
define('MIME_TYPE_JPEG',       'Content-Type: image/jpeg');
define('MIME_TYPE_PNG',        'Content-Type: image/png');

// shared setup strings
define('ERR_CREATE_FILE',  'could not create file at destination %s');
define('ERR_WRITE_FILE',   'could not write to file at destination %s');
define('ERR_READ_FILE',    'file %s could not be read');
define('ERR_MISSING_FILE', 'file %s not found');
define('ERR_NONE',         'created file %s');
define('ERR_MISSING_INCL', 'required function %s() could not be found at %s');

define('CACHE_TYPE_NONE',                   0);             // page will not be cached
define('CACHE_TYPE_PAGE',                   1);
define('CACHE_TYPE_TOOLTIP',                2);
define('CACHE_TYPE_SEARCH',                 3);
define('CACHE_TYPE_XML',                    4);             // only used by items

define('CACHE_MODE_FILECACHE',              0x1);
define('CACHE_MODE_MEMCACHED',              0x2);

define ('SC_CSS_FILE',                      1);
define ('SC_CSS_STRING',                    2);
define ('SC_JS_FILE',                       3);
define ('SC_JS_STRING',                     4);
define ('SC_FLAG_PREFIX',                   0x01);
define ('SC_FLAG_NO_TIMESTAMP',             0x02);
define ('SC_FLAG_APPEND_LOCALE',            0x04);
define ('SC_FLAG_LOCALIZED',                0x08);


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
define('ACC_BAN_GUIDE',                     0x80);          // cannot write a guide

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
define('BUTTON_RESYNC',                     9);
define('BUTTON_GUIDE_REPORT',              10);
define('BUTTON_GUIDE_NEW',                 11);
define('BUTTON_GUIDE_EDIT',                12);
define('BUTTON_GUIDE_LOG',                 13);

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

define('PROFILEINFO_PROFILE',               0x1);
define('PROFILEINFO_CHARACTER',             0x2);
define('PROFILEINFO_GUILD',                 0x10);          // like &roster
define('PROFILEINFO_ARENA',                 0x20);
define('PROFILEINFO_USER',                  0x40);

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

define('CONTRIBUTE_NONE',                   0x0);
define('CONTRIBUTE_CO',                     0x1);
define('CONTRIBUTE_SS',                     0x2);
define('CONTRIBUTE_VI',                     0x4);
define('CONTRIBUTE_ANY',                    CONTRIBUTE_CO | CONTRIBUTE_SS | CONTRIBUTE_VI);

define('NUM_ANY',                           0);
define('NUM_CAST_INT',                      1);
define('NUM_CAST_FLOAT',                    2);
define('NUM_REQ_INT',                       3);
define('NUM_REQ_FLOAT',                     4);

define('STR_LOCALIZED',                     0x1);
define('STR_MATCH_EXACT',                   0x2);
define('STR_ALLOW_SHORT',                   0x4);

define('RATING_COMMENT',                    1);
define('RATING_GUIDE',                      2);

define('GUIDE_STATUS_NONE',                 0);
define('GUIDE_STATUS_DRAFT',                1);
define('GUIDE_STATUS_REVIEW',               2);
define('GUIDE_STATUS_APPROVED',             3);
define('GUIDE_STATUS_REJECTED',             4);
define('GUIDE_STATUS_ARCHIVED',             5);

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

define('EMOTE_CU_MISSING_CMD',              0x01);          // no alias in Globalstrings.lua and thus unusable

// as seen in wFlags
define('QUEST_CU_REPEATABLE',               0x01);
define('QUEST_CU_DAILY',                    0x02);
define('QUEST_CU_WEEKLY',                   0x04);
define('QUEST_CU_SEASONAL',                 0x08);
define('QUEST_CU_SKIP_LOG',                 0x10);
define('QUEST_CU_AUTO_ACCEPT',              0x20);
define('QUEST_CU_PVP_ENABLED',              0x40);

define('PROFILER_CU_PUBLISHED',             0x01);
define('PROFILER_CU_PINNED',                0x02);
define('PROFILER_CU_DELETED',               0x04);
define('PROFILER_CU_PROFILE',               0x08);
define('PROFILER_CU_NEEDS_RESYNC',          0x10);

define('GUIDE_CU_NO_QUICKFACTS',            0x100);         // merge with CC_FLAG_*
define('GUIDE_CU_NO_RATING',                0x200);

define('MAX_LEVEL',                         80);
define('MAX_SKILL',                         450);
define('MAX_LOCALES',                       16);            // technical limitation, 6 in use here
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

// Expansion
define('EXP_CLASSIC',                       0);
define('EXP_BC',                            1);
define('EXP_WOTLK',                         2);

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
define('SPELL_MAGIC_SCHOOLS',               0x7E);
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

// Lock Types
define('LOCK_TYPE_ITEM',                    1);
define('LOCK_TYPE_SKILL',                   2);

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

define('NPC_FLAG_GOSSIP',                   0x00000001);
define('NPC_FLAG_QUEST_GIVER',              0x00000002);
define('NPC_FLAG_TRAINER',                  0x00000010);
define('NPC_FLAG_CLASS_TRAINER',            0x00000020);
define('NPC_PROFESSION_TRAINER',            0x00000040);
define('NPC_FLAG_VENDOR',                   0x00000080);
define('NPC_FLAG_VENDOR_AMMO',              0x00000100);
define('NPC_FLAG_VENDOR_FOOD',              0x00000200);
define('NPC_FLAG_VENDOR_POISON',            0x00000400);
define('NPC_FLAG_VENDOR_REAGENT',           0x00000800);
define('NPC_FLAG_REPAIRER',                 0x00001000);
define('NPC_FLAG_FLIGHT_MASTER',            0x00002000);
define('NPC_FLAG_SPIRIT_HEALER',            0x00004000);    // civil
define('NPC_FLAG_SPIRIT_GUIDE',             0x00008000);    // battleground
define('NPC_FLAG_INNKEEPER',                0x00010000);
define('NPC_FLAG_BANKER',                   0x00020000);
define('NPC_FLAG_PETITIONER',               0x00040000);
define('NPC_FLAG_GUILD_MASTER',             0x00080000);
define('NPC_FLAG_BATTLEMASTER',             0x00100000);
define('NPC_FLAG_AUCTIONEER',               0x00200000);
define('NPC_FLAG_STABLE_MASTER',            0x00400000);
define('NPC_FLAG_GUILD_BANK',               0x00800000);
define('NPC_FLAG_SPELLCLICK',               0x01000000);
define('NPC_FLAG_MAILBOX',                  0x04000000);

define('UNIT_FLAG_SERVER_CONTROLLED',       0x00000001);    //
define('UNIT_FLAG_NON_ATTACKABLE',          0x00000002);    //
define('UNIT_FLAG_REMOVE_CLIENT_CONTROL',   0x00000004);    //
define('UNIT_FLAG_PVP_ATTACKABLE',          0x00000008);    // Allows to apply PvP rules to attackable state in addition to faction dependent state
define('UNIT_FLAG_RENAME',                  0x00000010);    //
define('UNIT_FLAG_PREPARATION',             0x00000020);    // Don't take reagents for spells with SPELL_ATTR_EX5_NO_REAGENT_WHILE_PREP
define('UNIT_FLAG_UNK_6',                   0x00000040);    // not sure what it does, but it is needed to cast nontriggered spells in smart_scripts
define('UNIT_FLAG_NOT_ATTACKABLE_1',        0x00000080);    // UNIT_FLAG_PVP_ATTACKABLE| UNIT_FLAG_NOT_ATTACKABLE_1 is NON_PVP_ATTACKABLE
define('UNIT_FLAG_IMMUNE_TO_PC',            0x00000100);    // disables combat/assistance with PlayerCharacters (PC)
define('UNIT_FLAG_IMMUNE_TO_NPC',           0x00000200);    // disables combat/assistance with NonPlayerCharacters (NPC)
define('UNIT_FLAG_LOOTING',                 0x00000400);    // Loot animation
define('UNIT_FLAG_PET_IN_COMBAT',           0x00000800);    // In combat? 2.0.8
define('UNIT_FLAG_PVP',                     0x00001000);    // Changed in 3.0.3
define('UNIT_FLAG_SILENCED',                0x00002000);    // Can't cast spells
define('UNIT_FLAG_CANNOT_SWIM',             0x00004000);    // 2.0.8
define('UNIT_FLAG_UNK_15',                  0x00008000);    // Only Swim ('OnlySwim' from UnitFlags.cs in WPP)
define('UNIT_FLAG_UNK_16',                  0x00010000);    // No Attack 2 ('NoAttack2' from UnitFlags.cs in WPP)
define('UNIT_FLAG_PACIFIED',                0x00020000);    // Creature will not attack
define('UNIT_FLAG_STUNNED',                 0x00040000);    // 3.0.3 ok
define('UNIT_FLAG_IN_COMBAT',               0x00080000);    // ('AffectingCombat' from UnitFlags.cs in WPP)
define('UNIT_FLAG_TAXI_FLIGHT',             0x00100000);    // Disable casting at client side spell not allowed by taxi flight (mounted?), probably used with   0x4 flag
define('UNIT_FLAG_DISARMED',                0x00200000);    // 3.0.3, disable melee spells casting..., "Required melee weapon" added to melee spells tooltip.
define('UNIT_FLAG_CONFUSED',                0x00400000);    // Confused.
define('UNIT_FLAG_FLEEING',                 0x00800000);    // ('Feared' from UnitFlags.cs in WPP)
define('UNIT_FLAG_PLAYER_CONTROLLED',       0x01000000);    // Used in spell Eyes of the Beast for pet... let attack by controlled creature. Also used by Vehicles (PCV).
define('UNIT_FLAG_NOT_SELECTABLE',          0x02000000);    // Can't be selected by mouse or with /target {name} command.
define('UNIT_FLAG_SKINNABLE',               0x04000000);    // Skinnable
define('UNIT_FLAG_MOUNT',                   0x08000000);    // The client seems to handle it perfectly. Also used when making custom mounts.
define('UNIT_FLAG_UNK_28',                  0x10000000);    // (PreventKneelingWhenLooting from UnitFlags.cs in WPP)
define('UNIT_FLAG_UNK_29',                  0x20000000);    // Used in Feign Death spell or NPC will play dead. (PreventEmotes)
define('UNIT_FLAG_SHEATHE',                 0x40000000);    //
define('UNIT_FLAG_UNK_31',                  0x80000000);    //

define('UNIT_FLAG2_FEIGN_DEATH',            0x00000001);    //
define('UNIT_FLAG2_UNK1',                   0x00000002);    // Hide unit model (show only player equip)
define('UNIT_FLAG2_IGNORE_REPUTATION',      0x00000004);    //
define('UNIT_FLAG2_COMPREHEND_LANG',        0x00000008);    //
define('UNIT_FLAG2_MIRROR_IMAGE',           0x00000010);    //
define('UNIT_FLAG2_INSTANTLY_APPEAR_MODEL', 0x00000020);    // Unit model instantly appears when summoned (does not fade in)
define('UNIT_FLAG2_FORCE_MOVEMENT',         0x00000040);    //
define('UNIT_FLAG2_DISARM_OFFHAND',         0x00000080);    //
define('UNIT_FLAG2_DISABLE_PRED_STATS',     0x00000100);    // Player has disabled predicted stats (Used by raid frames)
define('UNIT_FLAG2_DISARM_RANGED',          0x00000400);    // this does not disable ranged weapon display (maybe additional flag needed?)
define('UNIT_FLAG2_REGENERATE_POWER',       0x00000800);    //
define('UNIT_FLAG2_RESTRICT_PARTY_INTERACTION', 0x1000);    // Restrict interaction to party or raid
define('UNIT_FLAG2_PREVENT_SPELL_CLICK',    0x00002000);    // Prevent spellclick
define('UNIT_FLAG2_ALLOW_ENEMY_INTERACT',   0x00004000);    //
define('UNIT_FLAG2_DISABLE_TURN',           0x00008000);    //
define('UNIT_FLAG2_UNK2',                   0x00010000);    //
define('UNIT_FLAG2_PLAY_DEATH_ANIM',        0x00020000);    // Plays special death animation upon death
define('UNIT_FLAG2_ALLOW_CHEAT_SPELLS',     0x00040000);    // allows casting spells with AttributesEx7 & SPELL_ATTR7_IS_CHEAT_SPELL

// UNIT_FIELD_BYTES_1 - idx 0 (UnitStandStateType)
define('UNIT_STAND_STATE_STAND',            0);
define('UNIT_STAND_STATE_SIT',              1);
define('UNIT_STAND_STATE_SIT_CHAIR',        2);
define('UNIT_STAND_STATE_SLEEP',            3);
define('UNIT_STAND_STATE_SIT_LOW_CHAIR',    4);
define('UNIT_STAND_STATE_SIT_MEDIUM_CHAIR', 5);
define('UNIT_STAND_STATE_SIT_HIGH_CHAIR',   6);
define('UNIT_STAND_STATE_DEAD',             7);
define('UNIT_STAND_STATE_KNEEL',            8);
define('UNIT_STAND_STATE_SUBMERGED',        9);

// UNIT_FIELD_BYTES_1 - idx 2 (UnitStandFlags)
define('UNIT_STAND_FLAGS_UNK1',             0x01);
define('UNIT_STAND_FLAGS_CREEP',            0x02);
define('UNIT_STAND_FLAGS_UNTRACKABLE',      0x04);
define('UNIT_STAND_FLAGS_UNK4',             0x08);
define('UNIT_STAND_FLAGS_UNK5',             0x10);

// UNIT_FIELD_BYTES_1 - idx 3 (UnitBytes1_Flags)
define('UNIT_BYTE1_FLAG_ALWAYS_STAND',      0x01);
define('UNIT_BYTE1_FLAG_HOVER',             0x02);
define('UNIT_BYTE1_FLAG_UNK_3',             0x04);

define('UNIT_DYNFLAG_LOOTABLE',                  0x01);     //
define('UNIT_DYNFLAG_TRACK_UNIT',                0x02);     // Creature's location will be seen as a small dot in the minimap
define('UNIT_DYNFLAG_TAPPED',                    0x04);     // Makes creatures name appear grey (Lua_UnitIsTapped)
define('UNIT_DYNFLAG_TAPPED_BY_PLAYER',          0x08);     // Lua_UnitIsTappedByPlayer usually used by PCVs (Player Controlled Vehicles)
define('UNIT_DYNFLAG_SPECIALINFO',               0x10);     //
define('UNIT_DYNFLAG_DEAD',                      0x20);     // Makes the creature appear dead (this DOES NOT make the creature's name grey or not attack players).
define('UNIT_DYNFLAG_REFER_A_FRIEND',            0x40);     //
define('UNIT_DYNFLAG_TAPPED_BY_ALL_THREAT_LIST', 0x80);     // Lua_UnitIsTappedByAllThreatList

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

define('GO_FLAG_IN_USE',          0x0001);                  // Gameobject in use - Disables interaction while being animated
define('GO_FLAG_LOCKED',          0x0002);                  // Makes the Gameobject Locked. Requires a key, spell, or event to be opened. "Locked" appears in tooltip
define('GO_FLAG_INTERACT_COND',   0x0004);                  // Untargetable, cannot interact
define('GO_FLAG_TRANSPORT',       0x0008);                  // Gameobject can transport (boat, elevator, car)
define('GO_FLAG_NOT_SELECTABLE',  0x0010);                  // Not selectable (Not even in GM-mode)
define('GO_FLAG_NODESPAWN',       0x0020);                  // Never despawns. Typical for gameobjects with on/off state (doors for example)
define('GO_FLAG_TRIGGERED',       0x0040);                  // typically, summoned objects. Triggered by spell or other events
define('GO_FLAG_DAMAGED',         0x0200);                  // Gameobject has been siege damaged
define('GO_FLAG_DESTROYED',       0x0400);                  // Gameobject has been destroyed

define('GO_STATE_ACTIVE',             0);                   // show in world as used and not reset (closed door open)
define('GO_STATE_READY',              1);                   // show in world as ready (closed door close)
define('GO_STATE_ACTIVE_ALTERNATIVE', 2);                   // show in world as used in alt way and not reset (closed door open by cannon fire)

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
// define('ITEM_MOD_MASTERY_RATING',        49);
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

// Spell Effects and Auras
define('SPELL_EFFECT_NONE',                             0);
define('SPELL_EFFECT_INSTAKILL',                        1);
define('SPELL_EFFECT_SCHOOL_DAMAGE',                    2);
define('SPELL_EFFECT_DUMMY',                            3);
define('SPELL_EFFECT_PORTAL_TELEPORT',                  4);
define('SPELL_EFFECT_TELEPORT_UNITS',                   5);
define('SPELL_EFFECT_APPLY_AURA',                       6);
define('SPELL_EFFECT_ENVIRONMENTAL_DAMAGE',             7);
define('SPELL_EFFECT_POWER_DRAIN',                      8);
define('SPELL_EFFECT_HEALTH_LEECH',                     9);
define('SPELL_EFFECT_HEAL',                             10);
define('SPELL_EFFECT_BIND',                             11);
define('SPELL_EFFECT_PORTAL',                           12);
define('SPELL_EFFECT_RITUAL_BASE',                      13);
define('SPELL_EFFECT_RITUAL_SPECIALIZE',                14);
define('SPELL_EFFECT_RITUAL_ACTIVATE_PORTAL',           15);
define('SPELL_EFFECT_QUEST_COMPLETE',                   16);
define('SPELL_EFFECT_WEAPON_DAMAGE_NOSCHOOL',           17);
define('SPELL_EFFECT_RESURRECT',                        18);
define('SPELL_EFFECT_ADD_EXTRA_ATTACKS',                19);
define('SPELL_EFFECT_DODGE',                            20);
define('SPELL_EFFECT_EVADE',                            21);
define('SPELL_EFFECT_PARRY',                            22);
define('SPELL_EFFECT_BLOCK',                            23);
define('SPELL_EFFECT_CREATE_ITEM',                      24);
define('SPELL_EFFECT_WEAPON',                           25);
define('SPELL_EFFECT_DEFENSE',                          26);
define('SPELL_EFFECT_PERSISTENT_AREA_AURA',             27);
define('SPELL_EFFECT_SUMMON',                           28);
define('SPELL_EFFECT_LEAP',                             29);
define('SPELL_EFFECT_ENERGIZE',                         30);
define('SPELL_EFFECT_WEAPON_PERCENT_DAMAGE',            31);
define('SPELL_EFFECT_TRIGGER_MISSILE',                  32);
define('SPELL_EFFECT_OPEN_LOCK',                        33);
define('SPELL_EFFECT_SUMMON_CHANGE_ITEM',               34);
define('SPELL_EFFECT_APPLY_AREA_AURA_PARTY',            35);
define('SPELL_EFFECT_LEARN_SPELL',                      36);
define('SPELL_EFFECT_SPELL_DEFENSE',                    37);
define('SPELL_EFFECT_DISPEL',                           38);
define('SPELL_EFFECT_LANGUAGE',                         39);
define('SPELL_EFFECT_DUAL_WIELD',                       40);
define('SPELL_EFFECT_JUMP',                             41);
define('SPELL_EFFECT_JUMP_DEST',                        42);
define('SPELL_EFFECT_TELEPORT_UNITS_FACE_CASTER',       43);
define('SPELL_EFFECT_SKILL_STEP',                       44);
define('SPELL_EFFECT_ADD_HONOR',                        45);
define('SPELL_EFFECT_SPAWN',                            46);
define('SPELL_EFFECT_TRADE_SKILL',                      47);
define('SPELL_EFFECT_STEALTH',                          48);
define('SPELL_EFFECT_DETECT',                           49);
define('SPELL_EFFECT_TRANS_DOOR',                       50);
define('SPELL_EFFECT_FORCE_CRITICAL_HIT',               51);
define('SPELL_EFFECT_GUARANTEE_HIT',                    52);
define('SPELL_EFFECT_ENCHANT_ITEM',                     53);
define('SPELL_EFFECT_ENCHANT_ITEM_TEMPORARY',           54);
define('SPELL_EFFECT_TAMECREATURE',                     55);
define('SPELL_EFFECT_SUMMON_PET',                       56);
define('SPELL_EFFECT_LEARN_PET_SPELL',                  57);
define('SPELL_EFFECT_WEAPON_DAMAGE',                    58);
define('SPELL_EFFECT_CREATE_RANDOM_ITEM',               59);
define('SPELL_EFFECT_PROFICIENCY',                      60);
define('SPELL_EFFECT_SEND_EVENT',                       61);
define('SPELL_EFFECT_POWER_BURN',                       62);
define('SPELL_EFFECT_THREAT',                           63);
define('SPELL_EFFECT_TRIGGER_SPELL',                    64);
define('SPELL_EFFECT_APPLY_AREA_AURA_RAID',             65);
define('SPELL_EFFECT_CREATE_MANA_GEM',                  66);
define('SPELL_EFFECT_HEAL_MAX_HEALTH',                  67);
define('SPELL_EFFECT_INTERRUPT_CAST',                   68);
define('SPELL_EFFECT_DISTRACT',                         69);
define('SPELL_EFFECT_PULL',                             70);
define('SPELL_EFFECT_PICKPOCKET',                       71);
define('SPELL_EFFECT_ADD_FARSIGHT',                     72);
define('SPELL_EFFECT_UNTRAIN_TALENTS',                  73);
define('SPELL_EFFECT_APPLY_GLYPH',                      74);
define('SPELL_EFFECT_HEAL_MECHANICAL',                  75);
define('SPELL_EFFECT_SUMMON_OBJECT_WILD',               76);
define('SPELL_EFFECT_SCRIPT_EFFECT',                    77);
define('SPELL_EFFECT_ATTACK',                           78);
define('SPELL_EFFECT_SANCTUARY',                        79);
define('SPELL_EFFECT_ADD_COMBO_POINTS',                 80);
define('SPELL_EFFECT_CREATE_HOUSE',                     81);
define('SPELL_EFFECT_BIND_SIGHT',                       82);
define('SPELL_EFFECT_DUEL',                             83);
define('SPELL_EFFECT_STUCK',                            84);
define('SPELL_EFFECT_SUMMON_PLAYER',                    85);
define('SPELL_EFFECT_ACTIVATE_OBJECT',                  86);
define('SPELL_EFFECT_GAMEOBJECT_DAMAGE',                87);
define('SPELL_EFFECT_GAMEOBJECT_REPAIR',                88);
define('SPELL_EFFECT_GAMEOBJECT_SET_DESTRUCTION_STATE', 89);
define('SPELL_EFFECT_KILL_CREDIT',                      90);
define('SPELL_EFFECT_THREAT_ALL',                       91);
define('SPELL_EFFECT_ENCHANT_HELD_ITEM',                92);
define('SPELL_EFFECT_FORCE_DESELECT',                   93);
define('SPELL_EFFECT_SELF_RESURRECT',                   94);
define('SPELL_EFFECT_SKINNING',                         95);
define('SPELL_EFFECT_CHARGE',                           96);
define('SPELL_EFFECT_CAST_BUTTON',                      97);
define('SPELL_EFFECT_KNOCK_BACK',                       98);
define('SPELL_EFFECT_DISENCHANT',                       99);
define('SPELL_EFFECT_INEBRIATE',                        100);
define('SPELL_EFFECT_FEED_PET',                         101);
define('SPELL_EFFECT_DISMISS_PET',                      102);
define('SPELL_EFFECT_REPUTATION',                       103);
define('SPELL_EFFECT_SUMMON_OBJECT_SLOT1',              104);
define('SPELL_EFFECT_SUMMON_OBJECT_SLOT2',              105);
define('SPELL_EFFECT_SUMMON_OBJECT_SLOT3',              106);
define('SPELL_EFFECT_SUMMON_OBJECT_SLOT4',              107);
define('SPELL_EFFECT_DISPEL_MECHANIC',                  108);
define('SPELL_EFFECT_RESURRECT_PET',                    109);
define('SPELL_EFFECT_DESTROY_ALL_TOTEMS',               110);
define('SPELL_EFFECT_DURABILITY_DAMAGE',                111);
define('SPELL_EFFECT_SUMMON_DEMON',                     112);
define('SPELL_EFFECT_RESURRECT_NEW',                    113);
define('SPELL_EFFECT_ATTACK_ME',                        114);
define('SPELL_EFFECT_DURABILITY_DAMAGE_PCT',            115);
define('SPELL_EFFECT_SKIN_PLAYER_CORPSE',               116);
define('SPELL_EFFECT_SPIRIT_HEAL',                      117);
define('SPELL_EFFECT_SKILL',                            118);
define('SPELL_EFFECT_APPLY_AREA_AURA_PET',              119);
define('SPELL_EFFECT_TELEPORT_GRAVEYARD',               120);
define('SPELL_EFFECT_NORMALIZED_WEAPON_DMG',            121);
define('SPELL_EFFECT_122',                              122);
define('SPELL_EFFECT_SEND_TAXI',                        123);
define('SPELL_EFFECT_PULL_TOWARDS',                     124);
define('SPELL_EFFECT_MODIFY_THREAT_PERCENT',            125);
define('SPELL_EFFECT_STEAL_BENEFICIAL_BUFF',            126);
define('SPELL_EFFECT_PROSPECTING',                      127);
define('SPELL_EFFECT_APPLY_AREA_AURA_FRIEND',           128);
define('SPELL_EFFECT_APPLY_AREA_AURA_ENEMY',            129);
define('SPELL_EFFECT_REDIRECT_THREAT',                  130);
define('SPELL_EFFECT_PLAY_SOUND',                       131);
define('SPELL_EFFECT_PLAY_MUSIC',                       132);
define('SPELL_EFFECT_UNLEARN_SPECIALIZATION',           133);
define('SPELL_EFFECT_KILL_CREDIT2',                     134);
define('SPELL_EFFECT_CALL_PET',                         135);
define('SPELL_EFFECT_HEAL_PCT',                         136);
define('SPELL_EFFECT_ENERGIZE_PCT',                     137);
define('SPELL_EFFECT_LEAP_BACK',                        138);
define('SPELL_EFFECT_CLEAR_QUEST',                      139);
define('SPELL_EFFECT_FORCE_CAST',                       140);
define('SPELL_EFFECT_FORCE_CAST_WITH_VALUE',            141);
define('SPELL_EFFECT_TRIGGER_SPELL_WITH_VALUE',         142);
define('SPELL_EFFECT_APPLY_AREA_AURA_OWNER',            143);
define('SPELL_EFFECT_KNOCK_BACK_DEST',                  144);
define('SPELL_EFFECT_PULL_TOWARDS_DEST',                145);
define('SPELL_EFFECT_ACTIVATE_RUNE',                    146);
define('SPELL_EFFECT_QUEST_FAIL',                       147);
define('SPELL_EFFECT_TRIGGER_MISSILE_SPELL_WITH_VALUE', 148);
define('SPELL_EFFECT_CHARGE_DEST',                      149);
define('SPELL_EFFECT_QUEST_START',                      150);
define('SPELL_EFFECT_TRIGGER_SPELL_2',                  151);
define('SPELL_EFFECT_SUMMON_RAF_FRIEND',                152);
define('SPELL_EFFECT_CREATE_TAMED_PET',                 153);
define('SPELL_EFFECT_DISCOVER_TAXI',                    154);
define('SPELL_EFFECT_TITAN_GRIP',                       155);
define('SPELL_EFFECT_ENCHANT_ITEM_PRISMATIC',           156);
define('SPELL_EFFECT_CREATE_ITEM_2',                    157);
define('SPELL_EFFECT_MILLING',                          158);
define('SPELL_EFFECT_ALLOW_RENAME_PET',                 159);
define('SPELL_EFFECT_FORCE_CAST_2',                     160);
define('SPELL_EFFECT_TALENT_SPEC_COUNT',                161);
define('SPELL_EFFECT_TALENT_SPEC_SELECT',               162);
define('SPELL_EFFECT_163',                              163);
define('SPELL_EFFECT_REMOVE_AURA',                      164);

define('SPELL_AURA_NONE',                                      0);
define('SPELL_AURA_BIND_SIGHT',                                1);
define('SPELL_AURA_MOD_POSSESS',                               2);
define('SPELL_AURA_PERIODIC_DAMAGE',                           3);
define('SPELL_AURA_DUMMY',                                     4);
define('SPELL_AURA_MOD_CONFUSE',                               5);
define('SPELL_AURA_MOD_CHARM',                                 6);
define('SPELL_AURA_MOD_FEAR',                                  7);
define('SPELL_AURA_PERIODIC_HEAL',                             8);
define('SPELL_AURA_MOD_ATTACKSPEED',                           9);
define('SPELL_AURA_MOD_THREAT',                                10);
define('SPELL_AURA_MOD_TAUNT',                                 11);
define('SPELL_AURA_MOD_STUN',                                  12);
define('SPELL_AURA_MOD_DAMAGE_DONE',                           13);
define('SPELL_AURA_MOD_DAMAGE_TAKEN',                          14);
define('SPELL_AURA_DAMAGE_SHIELD',                             15);
define('SPELL_AURA_MOD_STEALTH',                               16);
define('SPELL_AURA_MOD_STEALTH_DETECT',                        17);
define('SPELL_AURA_MOD_INVISIBILITY',                          18);
define('SPELL_AURA_MOD_INVISIBILITY_DETECT',                   19);
define('SPELL_AURA_OBS_MOD_HEALTH',                            20);
define('SPELL_AURA_OBS_MOD_POWER',                             21);
define('SPELL_AURA_MOD_RESISTANCE',                            22);
define('SPELL_AURA_PERIODIC_TRIGGER_SPELL',                    23);
define('SPELL_AURA_PERIODIC_ENERGIZE',                         24);
define('SPELL_AURA_MOD_PACIFY',                                25);
define('SPELL_AURA_MOD_ROOT',                                  26);
define('SPELL_AURA_MOD_SILENCE',                               27);
define('SPELL_AURA_REFLECT_SPELLS',                            28);
define('SPELL_AURA_MOD_STAT',                                  29);
define('SPELL_AURA_MOD_SKILL',                                 30);
define('SPELL_AURA_MOD_INCREASE_SPEED',                        31);
define('SPELL_AURA_MOD_INCREASE_MOUNTED_SPEED',                32);
define('SPELL_AURA_MOD_DECREASE_SPEED',                        33);
define('SPELL_AURA_MOD_INCREASE_HEALTH',                       34);
define('SPELL_AURA_MOD_INCREASE_ENERGY',                       35);
define('SPELL_AURA_MOD_SHAPESHIFT',                            36);
define('SPELL_AURA_EFFECT_IMMUNITY',                           37);
define('SPELL_AURA_STATE_IMMUNITY',                            38);
define('SPELL_AURA_SCHOOL_IMMUNITY',                           39);
define('SPELL_AURA_DAMAGE_IMMUNITY',                           40);
define('SPELL_AURA_DISPEL_IMMUNITY',                           41);
define('SPELL_AURA_PROC_TRIGGER_SPELL',                        42);
define('SPELL_AURA_PROC_TRIGGER_DAMAGE',                       43);
define('SPELL_AURA_TRACK_CREATURES',                           44);
define('SPELL_AURA_TRACK_RESOURCES',                           45);
define('SPELL_AURA_46',                                        46);
define('SPELL_AURA_MOD_PARRY_PERCENT',                         47);
define('SPELL_AURA_PERIODIC_TRIGGER_SPELL_FROM_CLIENT',        48);
define('SPELL_AURA_MOD_DODGE_PERCENT',                         49);
define('SPELL_AURA_MOD_CRITICAL_HEALING_AMOUNT',               50);
define('SPELL_AURA_MOD_BLOCK_PERCENT',                         51);
define('SPELL_AURA_MOD_WEAPON_CRIT_PERCENT',                   52);
define('SPELL_AURA_PERIODIC_LEECH',                            53);
define('SPELL_AURA_MOD_HIT_CHANCE',                            54);
define('SPELL_AURA_MOD_SPELL_HIT_CHANCE',                      55);
define('SPELL_AURA_TRANSFORM',                                 56);
define('SPELL_AURA_MOD_SPELL_CRIT_CHANCE',                     57);
define('SPELL_AURA_MOD_INCREASE_SWIM_SPEED',                   58);
define('SPELL_AURA_MOD_DAMAGE_DONE_CREATURE',                  59);
define('SPELL_AURA_MOD_PACIFY_SILENCE',                        60);
define('SPELL_AURA_MOD_SCALE',                                 61);
define('SPELL_AURA_PERIODIC_HEALTH_FUNNEL',                    62);
define('SPELL_AURA_63',                                        63);
define('SPELL_AURA_PERIODIC_MANA_LEECH',                       64);
define('SPELL_AURA_MOD_CASTING_SPEED_NOT_STACK',               65);
define('SPELL_AURA_FEIGN_DEATH',                               66);
define('SPELL_AURA_MOD_DISARM',                                67);
define('SPELL_AURA_MOD_STALKED',                               68);
define('SPELL_AURA_SCHOOL_ABSORB',                             69);
define('SPELL_AURA_EXTRA_ATTACKS',                             70);
define('SPELL_AURA_MOD_SPELL_CRIT_CHANCE_SCHOOL',              71);
define('SPELL_AURA_MOD_POWER_COST_SCHOOL_PCT',                 72);
define('SPELL_AURA_MOD_POWER_COST_SCHOOL',                     73);
define('SPELL_AURA_REFLECT_SPELLS_SCHOOL',                     74);
define('SPELL_AURA_MOD_LANGUAGE',                              75);
define('SPELL_AURA_FAR_SIGHT',                                 76);
define('SPELL_AURA_MECHANIC_IMMUNITY',                         77);
define('SPELL_AURA_MOUNTED',                                   78);
define('SPELL_AURA_MOD_DAMAGE_PERCENT_DONE',                   79);
define('SPELL_AURA_MOD_PERCENT_STAT',                          80);
define('SPELL_AURA_SPLIT_DAMAGE_PCT',                          81);
define('SPELL_AURA_WATER_BREATHING',                           82);
define('SPELL_AURA_MOD_BASE_RESISTANCE',                       83);
define('SPELL_AURA_MOD_REGEN',                                 84);
define('SPELL_AURA_MOD_POWER_REGEN',                           85);
define('SPELL_AURA_CHANNEL_DEATH_ITEM',                        86);
define('SPELL_AURA_MOD_DAMAGE_PERCENT_TAKEN',                  87);
define('SPELL_AURA_MOD_HEALTH_REGEN_PERCENT',                  88);
define('SPELL_AURA_PERIODIC_DAMAGE_PERCENT',                   89);
define('SPELL_AURA_90',                                        90);
define('SPELL_AURA_MOD_DETECT_RANGE',                          91);
define('SPELL_AURA_PREVENTS_FLEEING',                          92);
define('SPELL_AURA_MOD_UNATTACKABLE',                          93);
define('SPELL_AURA_INTERRUPT_REGEN',                           94);
define('SPELL_AURA_GHOST',                                     95);
define('SPELL_AURA_SPELL_MAGNET',                              96);
define('SPELL_AURA_MANA_SHIELD',                               97);
define('SPELL_AURA_MOD_SKILL_TALENT',                          98);
define('SPELL_AURA_MOD_ATTACK_POWER',                          99);
define('SPELL_AURA_AURAS_VISIBLE',                             100);
define('SPELL_AURA_MOD_RESISTANCE_PCT',                        101);
define('SPELL_AURA_MOD_MELEE_ATTACK_POWER_VERSUS',             102);
define('SPELL_AURA_MOD_TOTAL_THREAT',                          103);
define('SPELL_AURA_WATER_WALK',                                104);
define('SPELL_AURA_FEATHER_FALL',                              105);
define('SPELL_AURA_HOVER',                                     106);
define('SPELL_AURA_ADD_FLAT_MODIFIER',                         107);
define('SPELL_AURA_ADD_PCT_MODIFIER',                          108);
define('SPELL_AURA_ADD_TARGET_TRIGGER',                        109);
define('SPELL_AURA_MOD_POWER_REGEN_PERCENT',                   110);
define('SPELL_AURA_ADD_CASTER_HIT_TRIGGER',                    111);
define('SPELL_AURA_OVERRIDE_CLASS_SCRIPTS',                    112);
define('SPELL_AURA_MOD_RANGED_DAMAGE_TAKEN',                   113);
define('SPELL_AURA_MOD_RANGED_DAMAGE_TAKEN_PCT',               114);
define('SPELL_AURA_MOD_HEALING',                               115);
define('SPELL_AURA_MOD_REGEN_DURING_COMBAT',                   116);
define('SPELL_AURA_MOD_MECHANIC_RESISTANCE',                   117);
define('SPELL_AURA_MOD_HEALING_PCT',                           118);
define('SPELL_AURA_119',                                       119);
define('SPELL_AURA_UNTRACKABLE',                               120);
define('SPELL_AURA_EMPATHY',                                   121);
define('SPELL_AURA_MOD_OFFHAND_DAMAGE_PCT',                    122);
define('SPELL_AURA_MOD_TARGET_RESISTANCE',                     123);
define('SPELL_AURA_MOD_RANGED_ATTACK_POWER',                   124);
define('SPELL_AURA_MOD_MELEE_DAMAGE_TAKEN',                    125);
define('SPELL_AURA_MOD_MELEE_DAMAGE_TAKEN_PCT',                126);
define('SPELL_AURA_RANGED_ATTACK_POWER_ATTACKER_BONUS',        127);
define('SPELL_AURA_MOD_POSSESS_PET',                           128);
define('SPELL_AURA_MOD_SPEED_ALWAYS',                          129);
define('SPELL_AURA_MOD_MOUNTED_SPEED_ALWAYS',                  130);
define('SPELL_AURA_MOD_RANGED_ATTACK_POWER_VERSUS',            131);
define('SPELL_AURA_MOD_INCREASE_ENERGY_PERCENT',               132);
define('SPELL_AURA_MOD_INCREASE_HEALTH_PERCENT',               133);
define('SPELL_AURA_MOD_MANA_REGEN_INTERRUPT',                  134);
define('SPELL_AURA_MOD_HEALING_DONE',                          135);
define('SPELL_AURA_MOD_HEALING_DONE_PERCENT',                  136);
define('SPELL_AURA_MOD_TOTAL_STAT_PERCENTAGE',                 137);
define('SPELL_AURA_MOD_MELEE_HASTE',                           138);
define('SPELL_AURA_FORCE_REACTION',                            139);
define('SPELL_AURA_MOD_RANGED_HASTE',                          140);
define('SPELL_AURA_MOD_RANGED_AMMO_HASTE',                     141);
define('SPELL_AURA_MOD_BASE_RESISTANCE_PCT',                   142);
define('SPELL_AURA_MOD_RESISTANCE_EXCLUSIVE',                  143);
define('SPELL_AURA_SAFE_FALL',                                 144);
define('SPELL_AURA_MOD_PET_TALENT_POINTS',                     145);
define('SPELL_AURA_ALLOW_TAME_PET_TYPE',                       146);
define('SPELL_AURA_MECHANIC_IMMUNITY_MASK',                    147);
define('SPELL_AURA_RETAIN_COMBO_POINTS',                       148);
define('SPELL_AURA_REDUCE_PUSHBACK',                           149);
define('SPELL_AURA_MOD_SHIELD_BLOCKVALUE_PCT',                 150);
define('SPELL_AURA_TRACK_STEALTHED',                           151);
define('SPELL_AURA_MOD_DETECTED_RANGE',                        152);
define('SPELL_AURA_SPLIT_DAMAGE_FLAT',                         153);
define('SPELL_AURA_MOD_STEALTH_LEVEL',                         154);
define('SPELL_AURA_MOD_WATER_BREATHING',                       155);
define('SPELL_AURA_MOD_REPUTATION_GAIN',                       156);
define('SPELL_AURA_PET_DAMAGE_MULTI',                          157);
define('SPELL_AURA_MOD_SHIELD_BLOCKVALUE',                     158);
define('SPELL_AURA_NO_PVP_CREDIT',                             159);
define('SPELL_AURA_MOD_AOE_AVOIDANCE',                         160);
define('SPELL_AURA_MOD_HEALTH_REGEN_IN_COMBAT',                161);
define('SPELL_AURA_POWER_BURN',                                162);
define('SPELL_AURA_MOD_CRIT_DAMAGE_BONUS',                     163);
define('SPELL_AURA_164',                                       164);
define('SPELL_AURA_MELEE_ATTACK_POWER_ATTACKER_BONUS',         165);
define('SPELL_AURA_MOD_ATTACK_POWER_PCT',                      166);
define('SPELL_AURA_MOD_RANGED_ATTACK_POWER_PCT',               167);
define('SPELL_AURA_MOD_DAMAGE_DONE_VERSUS',                    168);
define('SPELL_AURA_MOD_CRIT_PERCENT_VERSUS',                   169);
define('SPELL_AURA_DETECT_AMORE',                              170);
define('SPELL_AURA_MOD_SPEED_NOT_STACK',                       171);
define('SPELL_AURA_MOD_MOUNTED_SPEED_NOT_STACK',               172);
define('SPELL_AURA_173',                                       173);
define('SPELL_AURA_MOD_SPELL_DAMAGE_OF_STAT_PERCENT',          174);
define('SPELL_AURA_MOD_SPELL_HEALING_OF_STAT_PERCENT',         175);
define('SPELL_AURA_SPIRIT_OF_REDEMPTION',                      176);
define('SPELL_AURA_AOE_CHARM',                                 177);
define('SPELL_AURA_MOD_DEBUFF_RESISTANCE',                     178);
define('SPELL_AURA_MOD_ATTACKER_SPELL_CRIT_CHANCE',            179);
define('SPELL_AURA_MOD_FLAT_SPELL_DAMAGE_VERSUS',              180);
define('SPELL_AURA_181',                                       181);
define('SPELL_AURA_MOD_RESISTANCE_OF_STAT_PERCENT',            182);
define('SPELL_AURA_MOD_CRITICAL_THREAT',                       183);
define('SPELL_AURA_MOD_ATTACKER_MELEE_HIT_CHANCE',             184);
define('SPELL_AURA_MOD_ATTACKER_RANGED_HIT_CHANCE',            185);
define('SPELL_AURA_MOD_ATTACKER_SPELL_HIT_CHANCE',             186);
define('SPELL_AURA_MOD_ATTACKER_MELEE_CRIT_CHANCE',            187);
define('SPELL_AURA_MOD_ATTACKER_RANGED_CRIT_CHANCE',           188);
define('SPELL_AURA_MOD_RATING',                                189);
define('SPELL_AURA_MOD_FACTION_REPUTATION_GAIN',               190);
define('SPELL_AURA_USE_NORMAL_MOVEMENT_SPEED',                 191);
define('SPELL_AURA_MOD_MELEE_RANGED_HASTE',                    192);
define('SPELL_AURA_MELEE_SLOW',                                193);
define('SPELL_AURA_MOD_TARGET_ABSORB_SCHOOL',                  194);
define('SPELL_AURA_MOD_TARGET_ABILITY_ABSORB_SCHOOL',          195);
define('SPELL_AURA_MOD_COOLDOWN',                              196);
define('SPELL_AURA_MOD_ATTACKER_SPELL_AND_WEAPON_CRIT_CHANCE', 197);
define('SPELL_AURA_198',                                       198);
define('SPELL_AURA_MOD_INCREASES_SPELL_PCT_TO_HIT',            199);
define('SPELL_AURA_MOD_XP_PCT',                                200);
define('SPELL_AURA_FLY',                                       201);
define('SPELL_AURA_IGNORE_COMBAT_RESULT',                      202);
define('SPELL_AURA_MOD_ATTACKER_MELEE_CRIT_DAMAGE',            203);
define('SPELL_AURA_MOD_ATTACKER_RANGED_CRIT_DAMAGE',           204);
define('SPELL_AURA_MOD_SCHOOL_CRIT_DMG_TAKEN',                 205);
define('SPELL_AURA_MOD_INCREASE_VEHICLE_FLIGHT_SPEED',         206);
define('SPELL_AURA_MOD_INCREASE_MOUNTED_FLIGHT_SPEED',         207);
define('SPELL_AURA_MOD_INCREASE_FLIGHT_SPEED',                 208);
define('SPELL_AURA_MOD_MOUNTED_FLIGHT_SPEED_ALWAYS',           209);
define('SPELL_AURA_MOD_VEHICLE_SPEED_ALWAYS',                  210);
define('SPELL_AURA_MOD_FLIGHT_SPEED_NOT_STACK',                211);
define('SPELL_AURA_MOD_RANGED_ATTACK_POWER_OF_STAT_PERCENT',   212);
define('SPELL_AURA_MOD_RAGE_FROM_DAMAGE_DEALT',                213);
define('SPELL_AURA_214',                                       214);
define('SPELL_AURA_ARENA_PREPARATION',                         215);
define('SPELL_AURA_HASTE_SPELLS',                              216);
define('SPELL_AURA_MOD_MELEE_HASTE_2',                         217);
define('SPELL_AURA_HASTE_RANGED',                              218);
define('SPELL_AURA_MOD_MANA_REGEN_FROM_STAT',                  219);
define('SPELL_AURA_MOD_RATING_FROM_STAT',                      220);
define('SPELL_AURA_MOD_DETAUNT',                               221);
define('SPELL_AURA_222',                                       222);
define('SPELL_AURA_RAID_PROC_FROM_CHARGE',                     223);
define('SPELL_AURA_224',                                       224);
define('SPELL_AURA_RAID_PROC_FROM_CHARGE_WITH_VALUE',          225);
define('SPELL_AURA_PERIODIC_DUMMY',                            226);
define('SPELL_AURA_PERIODIC_TRIGGER_SPELL_WITH_VALUE',         227);
define('SPELL_AURA_DETECT_STEALTH',                            228);
define('SPELL_AURA_MOD_AOE_DAMAGE_AVOIDANCE',                  229);
define('SPELL_AURA_MOD_INCREASE_HEALTH_NONSTACK',              230);
define('SPELL_AURA_PROC_TRIGGER_SPELL_WITH_VALUE',             231);
define('SPELL_AURA_MECHANIC_DURATION_MOD',                     232);
define('SPELL_AURA_CHANGE_MODEL_FOR_ALL_HUMANOIDS',            233);
define('SPELL_AURA_MECHANIC_DURATION_MOD_NOT_STACK',           234);
define('SPELL_AURA_MOD_DISPEL_RESIST',                         235);
define('SPELL_AURA_CONTROL_VEHICLE',                           236);
define('SPELL_AURA_MOD_SPELL_DAMAGE_OF_ATTACK_POWER',          237);
define('SPELL_AURA_MOD_SPELL_HEALING_OF_ATTACK_POWER',         238);
define('SPELL_AURA_MOD_SCALE_2',                               239);
define('SPELL_AURA_MOD_EXPERTISE',                             240);
define('SPELL_AURA_FORCE_MOVE_FORWARD',                        241);
define('SPELL_AURA_MOD_SPELL_DAMAGE_FROM_HEALING',             242);
define('SPELL_AURA_MOD_FACTION',                               243);
define('SPELL_AURA_COMPREHEND_LANGUAGE',                       244);
define('SPELL_AURA_MOD_AURA_DURATION_BY_DISPEL',               245);
define('SPELL_AURA_MOD_AURA_DURATION_BY_DISPEL_NOT_STACK',     246);
define('SPELL_AURA_CLONE_CASTER',                              247);
define('SPELL_AURA_MOD_COMBAT_RESULT_CHANCE',                  248);
define('SPELL_AURA_CONVERT_RUNE',                              249);
define('SPELL_AURA_MOD_INCREASE_HEALTH_2',                     250);
define('SPELL_AURA_MOD_ENEMY_DODGE',                           251);
define('SPELL_AURA_MOD_SPEED_SLOW_ALL',                        252);
define('SPELL_AURA_MOD_BLOCK_CRIT_CHANCE',                     253);
define('SPELL_AURA_MOD_DISARM_OFFHAND',                        254);
define('SPELL_AURA_MOD_MECHANIC_DAMAGE_TAKEN_PERCENT',         255);
define('SPELL_AURA_NO_REAGENT_USE',                            256);
define('SPELL_AURA_MOD_TARGET_RESIST_BY_SPELL_CLASS',          257);
define('SPELL_AURA_258',                                       258);
define('SPELL_AURA_MOD_HOT_PCT',                               259);
define('SPELL_AURA_SCREEN_EFFECT',                             260);
define('SPELL_AURA_PHASE',                                     261);
define('SPELL_AURA_ABILITY_IGNORE_AURASTATE',                  262);
define('SPELL_AURA_ALLOW_ONLY_ABILITY',                        263);
define('SPELL_AURA_264',                                       264);
define('SPELL_AURA_265',                                       265);
define('SPELL_AURA_266',                                       266);
define('SPELL_AURA_MOD_IMMUNE_AURA_APPLY_SCHOOL',              267);
define('SPELL_AURA_MOD_ATTACK_POWER_OF_STAT_PERCENT',          268);
define('SPELL_AURA_MOD_IGNORE_TARGET_RESIST',                  269);
define('SPELL_AURA_MOD_ABILITY_IGNORE_TARGET_RESIST',          270);
define('SPELL_AURA_MOD_DAMAGE_FROM_CASTER',                    271);
define('SPELL_AURA_IGNORE_MELEE_RESET',                        272);
define('SPELL_AURA_X_RAY',                                     273);
define('SPELL_AURA_ABILITY_CONSUME_NO_AMMO',                   274);
define('SPELL_AURA_MOD_IGNORE_SHAPESHIFT',                     275);
define('SPELL_AURA_MOD_DAMAGE_DONE_FOR_MECHANIC',              276);
define('SPELL_AURA_MOD_MAX_AFFECTED_TARGETS',                  277);
define('SPELL_AURA_MOD_DISARM_RANGED',                         278);
define('SPELL_AURA_INITIALIZE_IMAGES',                         279);
define('SPELL_AURA_MOD_ARMOR_PENETRATION_PCT',                 280);
define('SPELL_AURA_MOD_HONOR_GAIN_PCT',                        281);
define('SPELL_AURA_MOD_BASE_HEALTH_PCT',                       282);
define('SPELL_AURA_MOD_HEALING_RECEIVED',                      283);
define('SPELL_AURA_LINKED',                                    284);
define('SPELL_AURA_MOD_ATTACK_POWER_OF_ARMOR',                 285);
define('SPELL_AURA_ABILITY_PERIODIC_CRIT',                     286);
define('SPELL_AURA_DEFLECT_SPELLS',                            287);
define('SPELL_AURA_IGNORE_HIT_DIRECTION',                      288);
define('SPELL_AURA_PREVENT_DURABILITY_LOSS',                   289);
define('SPELL_AURA_MOD_CRIT_PCT',                              290);
define('SPELL_AURA_MOD_XP_QUEST_PCT',                          291);
define('SPELL_AURA_OPEN_STABLE',                               292);
define('SPELL_AURA_OVERRIDE_SPELLS',                           293);
define('SPELL_AURA_PREVENT_REGENERATE_POWER',                  294);
define('SPELL_AURA_295',                                       295);
define('SPELL_AURA_SET_VEHICLE_ID',                            296);
define('SPELL_AURA_BLOCK_SPELL_FAMILY',                        297);
define('SPELL_AURA_STRANGULATE',                               298);
define('SPELL_AURA_299',                                       299);
define('SPELL_AURA_SHARE_DAMAGE_PCT',                          300);
define('SPELL_AURA_SCHOOL_HEAL_ABSORB',                        301);
define('SPELL_AURA_302',                                       302);
define('SPELL_AURA_MOD_DAMAGE_DONE_VERSUS_AURASTATE',          303);
define('SPELL_AURA_MOD_FAKE_INEBRIATE',                        304);
define('SPELL_AURA_MOD_MINIMUM_SPEED',                         305);
define('SPELL_AURA_306',                                       306);
define('SPELL_AURA_HEAL_ABSORB_TEST',                          307);
define('SPELL_AURA_MOD_CRIT_CHANCE_FOR_CASTER',                308);
define('SPELL_AURA_309',                                       309);
define('SPELL_AURA_MOD_CREATURE_AOE_DAMAGE_AVOIDANCE',         310);
define('SPELL_AURA_311',                                       311);
define('SPELL_AURA_312',                                       312);
define('SPELL_AURA_313',                                       313);
define('SPELL_AURA_PREVENT_RESURRECTION',                      314);
define('SPELL_AURA_UNDERWATER_WALKING',                        315);
define('SPELL_AURA_PERIODIC_HASTE',                            316);


// item trigger and recipe handling
define('SPELL_TRIGGER_USE',         0);
define('SPELL_TRIGGER_EQUIP',       1);
define('SPELL_TRIGGER_HIT',         2);
define('SPELL_TRIGGER_SOULSTONE',   4);
define('SPELL_TRIGGER_USE_NODELAY', 5);
define('SPELL_TRIGGER_LEARN',       6);

// learn trigger spells on items - 483: learn recipe; 55884: learn mount/pet
define('LEARN_SPELLS', [483, 55884]);

// Spell Attributes definitions
define('SPELL_ATTR0_CU_ENCHANT_PROC',                  0x00000001); //
define('SPELL_ATTR0_CU_CONE_BACK',                     0x00000002); //
define('SPELL_ATTR0_CU_CONE_LINE',                     0x00000004); //
define('SPELL_ATTR0_CU_SHARE_DAMAGE',                  0x00000008); //
define('SPELL_ATTR0_CU_NO_INITIAL_THREAT',             0x00000010); //
define('SPELL_ATTR0_CU_AURA_CC',                       0x00000020); //
define('SPELL_ATTR0_CU_DONT_BREAK_STEALTH',            0x00000040); //
define('SPELL_ATTR0_CU_CAN_CRIT',                      0x00000080); //
define('SPELL_ATTR0_CU_DIRECT_DAMAGE',                 0x00000100); //
define('SPELL_ATTR0_CU_CHARGE',                        0x00000200); //
define('SPELL_ATTR0_CU_PICKPOCKET',                    0x00000400); //
define('SPELL_ATTR0_CU_ROLLING_PERIODIC',              0x00000800); //
define('SPELL_ATTR0_CU_NEGATIVE_EFF0',                 0x00001000); //
define('SPELL_ATTR0_CU_NEGATIVE_EFF1',                 0x00002000); //
define('SPELL_ATTR0_CU_NEGATIVE_EFF2',                 0x00004000); //
define('SPELL_ATTR0_CU_IGNORE_ARMOR',                  0x00008000); //
define('SPELL_ATTR0_CU_REQ_TARGET_FACING_CASTER',      0x00010000); //
define('SPELL_ATTR0_CU_REQ_CASTER_BEHIND_TARGET',      0x00020000); //
define('SPELL_ATTR0_CU_ALLOW_INFLIGHT_TARGET',         0x00040000); //
define('SPELL_ATTR0_CU_NEEDS_AMMO_DATA',               0x00080000); //
define('SPELL_ATTR0_CU_BINARY_SPELL',                  0x00100000); //
define('SPELL_ATTR0_CU_SCHOOLMASK_NORMAL_WITH_MAGIC',  0x00200000); //
define('SPELL_ATTR0_CU_LIQUID_AURA',                   0x00400000); //
define('SPELL_ATTR0_CU_NEGATIVE',                      SPELL_ATTR0_CU_NEGATIVE_EFF0 | SPELL_ATTR0_CU_NEGATIVE_EFF1 | SPELL_ATTR0_CU_NEGATIVE_EFF2); //

define('SPELL_ATTR0_UNK0',                             0x00000001); // Unknown attribute 0@Attr0
define('SPELL_ATTR0_REQ_AMMO',                         0x00000002); // Treat as ranged attack DESCRIPTION Use ammo, ranged attack range modifiers, ranged haste, etc.
define('SPELL_ATTR0_ON_NEXT_SWING',                    0x00000004); // On next melee (type 1) DESCRIPTION Both "on next swing" attributes have identical handling in server & client
define('SPELL_ATTR0_IS_REPLENISHMENT',                 0x00000008); // Replenishment (client only)
define('SPELL_ATTR0_ABILITY',                          0x00000010); // Treat as ability DESCRIPTION Cannot be reflected, not affected by cast speed modifiers, etc.
define('SPELL_ATTR0_TRADESPELL',                       0x00000020); // Trade skill recipe DESCRIPTION Displayed in recipe list, not affected by cast speed modifiers
define('SPELL_ATTR0_PASSIVE',                          0x00000040); // Passive spell DESCRIPTION Spell is automatically cast on self by core
define('SPELL_ATTR0_HIDDEN_CLIENTSIDE',                0x00000080); // Hidden in UI (client only) DESCRIPTION Not visible in spellbook or aura bar
define('SPELL_ATTR0_HIDE_IN_COMBAT_LOG',               0x00000100); // Hidden in combat log (client only) DESCRIPTION Spell will not appear in combat logs
define('SPELL_ATTR0_TARGET_MAINHAND_ITEM',             0x00000200); // Auto-target mainhand item (client only) DESCRIPTION Client will automatically select main-hand item as cast target
define('SPELL_ATTR0_ON_NEXT_SWING_2',                  0x00000400); // On next melee (type 2) DESCRIPTION Both "on next swing" attributes have identical handling in server & client
define('SPELL_ATTR0_UNK11',                            0x00000800); // Unknown attribute 11@Attr0
define('SPELL_ATTR0_DAYTIME_ONLY',                     0x00001000); // Only usable during daytime (unused)
define('SPELL_ATTR0_NIGHT_ONLY',                       0x00002000); // Only usable during nighttime (unused)
define('SPELL_ATTR0_INDOORS_ONLY',                     0x00004000); // Only usable indoors
define('SPELL_ATTR0_OUTDOORS_ONLY',                    0x00008000); // Only usable outdoors
define('SPELL_ATTR0_NOT_SHAPESHIFT',                   0x00010000); // Not usable while shapeshifted
define('SPELL_ATTR0_ONLY_STEALTHED',                   0x00020000); // Only usable in stealth
define('SPELL_ATTR0_DONT_AFFECT_SHEATH_STATE',         0x00040000); // Don't shealthe weapons (client only)
define('SPELL_ATTR0_LEVEL_DAMAGE_CALCULATION',         0x00080000); // Scale with caster level DESCRIPTION For non-player casts, scale impact and power cost with caster's level
define('SPELL_ATTR0_STOP_ATTACK_TARGET',               0x00100000); // Stop attacking after cast DESCRIPTION After casting this, the current auto-attack will be interrupted
define('SPELL_ATTR0_IMPOSSIBLE_DODGE_PARRY_BLOCK',     0x00200000); // Prevent physical avoidance DESCRIPTION Spell cannot be dodged, parried or blocked
define('SPELL_ATTR0_CAST_TRACK_TARGET',                0x00400000); // Automatically face target during cast (client only)
define('SPELL_ATTR0_CASTABLE_WHILE_DEAD',              0x00800000); // Can be cast while dead DESCRIPTION Spells without this flag cannot be cast by dead units in non-triggered contexts
define('SPELL_ATTR0_CASTABLE_WHILE_MOUNTED',           0x01000000); // Can be cast while mounted
define('SPELL_ATTR0_DISABLED_WHILE_ACTIVE',            0x02000000); // Cooldown starts on expiry DESCRIPTION Spell is unusable while already active, and cooldown does not begin until the effects have worn off
define('SPELL_ATTR0_NEGATIVE_1',                       0x04000000); // Is negative spell DESCRIPTION Forces the spell to be treated as a negative spell
define('SPELL_ATTR0_CASTABLE_WHILE_SITTING',           0x08000000); // Can be cast while sitting
define('SPELL_ATTR0_CANT_USED_IN_COMBAT',              0x10000000); // Cannot be used in combat
define('SPELL_ATTR0_UNAFFECTED_BY_INVULNERABILITY',    0x20000000); // Pierce invulnerability DESCRIPTION Allows spell to pierce invulnerability, unless the invulnerability spell also has this attribute
define('SPELL_ATTR0_HEARTBEAT_RESIST_CHECK',           0x40000000); // Periodic resistance checks DESCRIPTION Periodically re-rolls against resistance to potentially expire aura early
define('SPELL_ATTR0_CANT_CANCEL',                      0x80000000); // Aura cannot be cancelled DESCRIPTION Prevents the player from voluntarily canceling a positive aura

define('SPELL_ATTR1_DISMISS_PET',                      0x00000001); // Dismiss Pet on cast DESCRIPTION Without this attribute, summoning spells will fail if caster already has a pet
define('SPELL_ATTR1_DRAIN_ALL_POWER',                  0x00000002); // Drain all power DESCRIPTION Ignores listed power cost and drains entire pool instead
define('SPELL_ATTR1_CHANNELED_1',                      0x00000004); // Channeled (type 1) DESCRIPTION Both "channeled" attributes have identical handling in server & client
define('SPELL_ATTR1_CANT_BE_REDIRECTED',               0x00000008); // Ignore redirection effects DESCRIPTION Spell will not be attracted by SPELL_MAGNET auras (Grounding Totem)
define('SPELL_ATTR1_UNK4',                             0x00000010); // Unknown attribute 4@Attr1
define('SPELL_ATTR1_NOT_BREAK_STEALTH',                0x00000020); // Does not break stealth
define('SPELL_ATTR1_CHANNELED_2',                      0x00000040); // Channeled (type 2) DESCRIPTION Both "channeled" attributes have identical handling in server & client
define('SPELL_ATTR1_CANT_BE_REFLECTED',                0x00000080); // Ignore reflection effects DESCRIPTION Spell will pierce through Spell Reflection and similar
define('SPELL_ATTR1_CANT_TARGET_IN_COMBAT',            0x00000100); // Target cannot be in combat
define('SPELL_ATTR1_MELEE_COMBAT_START',               0x00000200); // Starts auto-attack (client only) DESCRIPTION Caster will begin auto-attacking the target on cast
define('SPELL_ATTR1_NO_THREAT',                        0x00000400); // Does not generate threat DESCRIPTION Also does not cause target to engage
define('SPELL_ATTR1_UNK11',                            0x00000800); // Unknown attribute 11@Attr1 DESCRIPTION Aura?
define('SPELL_ATTR1_IS_PICKPOCKET',                    0x00001000); // Pickpocket (client only)
define('SPELL_ATTR1_FARSIGHT',                         0x00002000); // Farsight aura (client only)
define('SPELL_ATTR1_CHANNEL_TRACK_TARGET',             0x00004000); // Track target while channeling DESCRIPTION While channeling, adjust facing to face target
define('SPELL_ATTR1_DISPEL_AURAS_ON_IMMUNITY',         0x00008000); // Immunity cancels preapplied auras DESCRIPTION For immunity spells, cancel all auras that this spell would make you immune to when the spell is applied
define('SPELL_ATTR1_UNAFFECTED_BY_SCHOOL_IMMUNE',      0x00010000); // Unaffected by school immunities DESCRIPTION Will not pierce Divine Shield, Ice Block and other full invulnerabilities
define('SPELL_ATTR1_UNAUTOCASTABLE_BY_PET',            0x00020000); // Cannot be autocast by pet
define('SPELL_ATTR1_UNK18',                            0x00040000); // Unknown attribute 18@Attr1 DESCRIPTION Stun, Polymorph, Daze, Hex - CC?
define('SPELL_ATTR1_CANT_TARGET_SELF',                 0x00080000); // Cannot be self-cast
define('SPELL_ATTR1_REQ_COMBO_POINTS1',                0x00100000); // Requires combo points (type 1)
define('SPELL_ATTR1_UNK21',                            0x00200000); // Unknown attribute 21@Attr1
define('SPELL_ATTR1_REQ_COMBO_POINTS2',                0x00400000); // Requires combo points (type 2)
define('SPELL_ATTR1_UNK23',                            0x00800000); // Unknwon attribute 23@Attr1
define('SPELL_ATTR1_IS_FISHING',                       0x01000000); // Fishing (client only)
define('SPELL_ATTR1_UNK25',                            0x02000000); // Unknown attribute 25@Attr1
define('SPELL_ATTR1_UNK26',                            0x04000000); // Unknown attribute 26@Attr1 DESCRIPTION Related to [target=focus] and [target=mouseover] macros?
define('SPELL_ATTR1_UNK27',                            0x08000000); // Unknown attribute 27@Attr1 DESCRIPTION Melee spell?
define('SPELL_ATTR1_DONT_DISPLAY_IN_AURA_BAR',         0x10000000); // Hide in aura bar (client only)
define('SPELL_ATTR1_CHANNEL_DISPLAY_SPELL_NAME',       0x20000000); // Show spell name during channel (client only)
define('SPELL_ATTR1_ENABLE_AT_DODGE',                  0x40000000); // Enable at dodge
define('SPELL_ATTR1_UNK31',                            0x80000000); // Unknown attribute 31@Attr1

define('SPELL_ATTR2_CAN_TARGET_DEAD',                  0x00000001); // Can target dead players or corpses
define('SPELL_ATTR2_UNK1',                             0x00000002); // Unknown attribute 1@Attr2
define('SPELL_ATTR2_CAN_TARGET_NOT_IN_LOS',            0x00000004); // Ignore Line of Sight
define('SPELL_ATTR2_UNK3',                             0x00000008); // Ignore aura scaling
define('SPELL_ATTR2_DISPLAY_IN_STANCE_BAR',            0x00000010); // Show in stance bar (client only)
define('SPELL_ATTR2_AUTOREPEAT_FLAG',                  0x00000020); // Ranged auto-attack spell
define('SPELL_ATTR2_CANT_TARGET_TAPPED',               0x00000040); // Cannot target others' tapped units DESCRIPTION Can only target untapped units, or those tapped by caster
define('SPELL_ATTR2_UNK7',                             0x00000080); // Unknown attribute 7@Attr2
define('SPELL_ATTR2_UNK8',                             0x00000100); // Unknown attribute 8@Attr2
define('SPELL_ATTR2_UNK9',                             0x00000200); // Unknown attribute 9@Attr2
define('SPELL_ATTR2_UNK10',                            0x00000400); // Unknown attribute 10@Attr2 DESCRIPTION Related to taming?
define('SPELL_ATTR2_HEALTH_FUNNEL',                    0x00000800); // Health Funnel
define('SPELL_ATTR2_UNK12',                            0x00001000); // Unknown attribute 12@Attr2
define('SPELL_ATTR2_PRESERVE_ENCHANT_IN_ARENA',        0x00002000); // Enchant persists when entering arena
define('SPELL_ATTR2_UNK14',                            0x00004000); // Unknown attribute 14@Attr2
define('SPELL_ATTR2_UNK15',                            0x00008000); // Unknown attribute 15@Attr2
define('SPELL_ATTR2_TAME_BEAST',                       0x00010000); // Tame Beast
define('SPELL_ATTR2_NOT_RESET_AUTO_ACTIONS',           0x00020000); // Don't reset swing timer DESCRIPTION Does not reset melee/ranged autoattack timer on cast
define('SPELL_ATTR2_REQ_DEAD_PET',                     0x00040000); // Requires dead pet
define('SPELL_ATTR2_NOT_NEED_SHAPESHIFT',              0x00080000); // Also allow outside shapeshift DESCRIPTION Even if Stances are nonzero, allow spell to be cast outside of shapeshift (though not in a different shapeshift)
define('SPELL_ATTR2_UNK20',                            0x00100000); // Unknown attribute 20@Attr2
define('SPELL_ATTR2_DAMAGE_REDUCED_SHIELD',            0x00200000); // Damage reduction ability DESCRIPTION Causes BG flags to be dropped if combined with ATTR1_DISPEL_AURAS_ON_IMMUNITY
define('SPELL_ATTR2_UNK22',                            0x00400000); // Unknown attribute 22@Attr2
define('SPELL_ATTR2_IS_ARCANE_CONCENTRATION',          0x00800000); // Arcane Concentration
define('SPELL_ATTR2_UNK24',                            0x01000000); // Unknown attribute 24@Attr2
define('SPELL_ATTR2_UNK25',                            0x02000000); // Unknown attribute 25@Attr2
define('SPELL_ATTR2_UNAFFECTED_BY_AURA_SCHOOL_IMMUNE', 0x04000000); // Pierce aura application immunities DESCRIPTION Allow aura to be applied despite target being immune to new aura applications
define('SPELL_ATTR2_UNK27',                            0x08000000); // Unknown attribute 27@Attr2
define('SPELL_ATTR2_UNK28',                            0x10000000); // Unknown attribute 28@Attr2
define('SPELL_ATTR2_CANT_CRIT',                        0x20000000); // Cannot critically strike
define('SPELL_ATTR2_TRIGGERED_CAN_TRIGGER_PROC',       0x40000000); // Allow triggered spell to trigger (type 1) DESCRIPTION Without this attribute, any triggered spell will be unable to trigger other auras' procs
define('SPELL_ATTR2_FOOD_BUFF',                        0x80000000); // Food buff (client only)

define('SPELL_ATTR3_UNK0',                             0x00000001); // Unknown attribute 0@Attr3
define('SPELL_ATTR3_IGNORE_PROC_SUBCLASS_MASK',        0x00000002); // 1 Ignores subclass mask check when checking proc
define('SPELL_ATTR3_UNK2',                             0x00000004); // Unknown attribute 2@Attr3
define('SPELL_ATTR3_BLOCKABLE_SPELL',                  0x00000008); // Blockable spell
define('SPELL_ATTR3_IGNORE_RESURRECTION_TIMER',        0x00000010); // Ignore resurrection timer
define('SPELL_ATTR3_UNK5',                             0x00000020); // Unknown attribute 5@Attr3
define('SPELL_ATTR3_UNK6',                             0x00000040); // Unknown attribute 6@Attr3
define('SPELL_ATTR3_STACK_FOR_DIFF_CASTERS',           0x00000080); // Stack separately for each caster
define('SPELL_ATTR3_ONLY_TARGET_PLAYERS',              0x00000100); // Can only target players
define('SPELL_ATTR3_TRIGGERED_CAN_TRIGGER_PROC_2',     0x00000200); // Allow triggered spell to trigger (type 2) DESCRIPTION Without this attribute, any triggered spell will be unable to trigger other auras' procs
define('SPELL_ATTR3_MAIN_HAND',                        0x00000400); // Require main hand weapon
define('SPELL_ATTR3_BATTLEGROUND',                     0x00000800); // Can only be cast in battleground
define('SPELL_ATTR3_ONLY_TARGET_GHOSTS',               0x00001000); // Can only target ghost players
define('SPELL_ATTR3_DONT_DISPLAY_CHANNEL_BAR',         0x00002000); // Do not display channel bar (client only)
define('SPELL_ATTR3_IS_HONORLESS_TARGET',              0x00004000); // Honorless Target
define('SPELL_ATTR3_UNK15',                            0x00008000); // Unknown attribute 15@Attr3 DESCRIPTION Auto Shoot, Shoot, Throw - ranged normal attack attribute?
define('SPELL_ATTR3_CANT_TRIGGER_PROC',                0x00010000); // Cannot trigger procs
define('SPELL_ATTR3_NO_INITIAL_AGGRO',                 0x00020000); // No initial aggro
define('SPELL_ATTR3_IGNORE_HIT_RESULT',                0x00040000); // Ignore hit result DESCRIPTION Spell cannot miss, or be dodged/parried/blocked
define('SPELL_ATTR3_DISABLE_PROC',                     0x00080000); // Cannot trigger spells during aura proc
define('SPELL_ATTR3_DEATH_PERSISTENT',                 0x00100000); // Persists through death
define('SPELL_ATTR3_UNK21',                            0x00200000); // Unknown attribute 21@Attr3
define('SPELL_ATTR3_REQ_WAND',                         0x00400000); // Requires equipped Wand
define('SPELL_ATTR3_UNK23',                            0x00800000); // Unknown attribute 23@Attr3
define('SPELL_ATTR3_REQ_OFFHAND',                      0x01000000); // Requires offhand weapon
define('SPELL_ATTR3_TREAT_AS_PERIODIC',                0x02000000); // Treat as periodic effect
define('SPELL_ATTR3_CAN_PROC_WITH_TRIGGERED',          0x04000000); // Can trigger from triggered spells
define('SPELL_ATTR3_DRAIN_SOUL',                       0x08000000); // Drain Soul
define('SPELL_ATTR3_UNK28',                            0x10000000); // Unknown attribute 28@Attr3
define('SPELL_ATTR3_NO_DONE_BONUS',                    0x20000000); // Damage dealt is unaffected by modifiers
define('SPELL_ATTR3_DONT_DISPLAY_RANGE',               0x40000000); // Do not show range in tooltip (client only)
define('SPELL_ATTR3_UNK31',                            0x80000000); // Unknown attribute 31@Attr3

define('SPELL_ATTR4_IGNORE_RESISTANCES',               0x00000001); // Cannot be resisted
define('SPELL_ATTR4_PROC_ONLY_ON_CASTER',              0x00000002); // Only proc on self-cast
define('SPELL_ATTR4_FADES_WHILE_LOGGED_OUT',           0x00000004); // Buff expires while offline DESCRIPTION Debuffs (except Resurrection Sickness) will automatically do this
define('SPELL_ATTR4_UNK3',                             0x00000008); // Unknown attribute 3@Attr4
define('SPELL_ATTR4_UNK4',                             0x00000010); // Treat as delayed spell
define('SPELL_ATTR4_UNK5',                             0x00000020); // Unknown attribute 5@Attr4
define('SPELL_ATTR4_NOT_STEALABLE',                    0x00000040); // Aura cannot be stolen
define('SPELL_ATTR4_CAN_CAST_WHILE_CASTING',           0x00000080); // Can be cast while casting DESCRIPTION Ignores already in-progress cast and still casts
define('SPELL_ATTR4_FIXED_DAMAGE',                     0x00000100); // Deals fixed damage
define('SPELL_ATTR4_TRIGGER_ACTIVATE',                 0x00000200); // Spell is initially disabled (client only)
define('SPELL_ATTR4_SPELL_VS_EXTEND_COST',             0x00000400); // Attack speed modifies cost DESCRIPTION Adds 10 to power cost for each 1s of weapon speed
define('SPELL_ATTR4_UNK11',                            0x00000800); // Unknown attribute 11@Attr4
define('SPELL_ATTR4_UNK12',                            0x00001000); // Unknown attribute 12@Attr4
define('SPELL_ATTR4_UNK13',                            0x00002000); // Unknown attribute 13@Attr4
define('SPELL_ATTR4_DAMAGE_DOESNT_BREAK_AURAS',        0x00004000); // Damage does not break auras
define('SPELL_ATTR4_UNK15',                            0x00008000); // Unknown attribute 15@Attr4
define('SPELL_ATTR4_NOT_USABLE_IN_ARENA',              0x00010000); // Not usable in arena DESCRIPTION Makes spell unusable despite CD <= 10min
define('SPELL_ATTR4_USABLE_IN_ARENA',                  0x00020000); // Usable in arena DESCRIPTION Makes spell usable despite CD > 10min
define('SPELL_ATTR4_AREA_TARGET_CHAIN',                0x00040000); // Chain area targets DESCRIPTION [NYI] Hits area targets over time instead of all at once
define('SPELL_ATTR4_UNK19',                            0x00080000); // Unknown attribute 19@Attr4
define('SPELL_ATTR4_NOT_CHECK_SELFCAST_POWER',         0x00100000); // Allow self-cast to override stronger aura (client only)
define('SPELL_ATTR4_UNK21',                            0x00200000); // Keep when entering arena
define('SPELL_ATTR4_UNK22',                            0x00400000); // Unknown attribute 22@Attr4
define('SPELL_ATTR4_CANT_TRIGGER_ITEM_SPELLS',         0x00800000); // Cannot trigger item spells
define('SPELL_ATTR4_UNK24',                            0x01000000); // Unknown attribute 24@Attr4 DESCRIPTION Shoot-type spell?
define('SPELL_ATTR4_IS_PET_SCALING',                   0x02000000); // Pet Scaling aura
define('SPELL_ATTR4_CAST_ONLY_IN_OUTLAND',             0x04000000); // Only in Outland/Northrend
define('SPELL_ATTR4_INHERIT_CRIT_FROM_AURA',           0x08000000); // Inherit critical chance from triggering aura
define('SPELL_ATTR4_UNK28',                            0x10000000); // Unknown attribute 28@Attr4
define('SPELL_ATTR4_UNK29',                            0x20000000); // Unknown attribute 29@Attr4
define('SPELL_ATTR4_UNK30',                            0x40000000); // Unknown attribute 30@Attr4
define('SPELL_ATTR4_UNK31',                            0x80000000); // Unknown attribute 31@Attr4

define('SPELL_ATTR5_CAN_CHANNEL_WHEN_MOVING',          0x00000001); // Can be channeled while moving
define('SPELL_ATTR5_NO_REAGENT_WHILE_PREP',            0x00000002); // No reagents during arena preparation
define('SPELL_ATTR5_REMOVE_ON_ARENA_ENTER',            0x00000004); // Remove when entering arena DESCRIPTION Force this aura to be removed on entering arena, regardless of other properties
define('SPELL_ATTR5_USABLE_WHILE_STUNNED',             0x00000008); // Usable while stunned
define('SPELL_ATTR5_UNK4',                             0x00000010); // Unknown attribute 4@Attr5
define('SPELL_ATTR5_SINGLE_TARGET_SPELL',              0x00000020); // Single-target aura DESCRIPTION Remove previous application to another unit if applied
define('SPELL_ATTR5_UNK6',                             0x00000040); // Unknown attribute 6@Attr5
define('SPELL_ATTR5_UNK7',                             0x00000080); // Unknown attribute 7@Attr5
define('SPELL_ATTR5_UNK8',                             0x00000100); // Unknown attribute 8@Attr5
define('SPELL_ATTR5_START_PERIODIC_AT_APPLY',          0x00000200); // Immediately do periodic tick on apply
define('SPELL_ATTR5_HIDE_DURATION',                    0x00000400); // Do not send aura duration to client
define('SPELL_ATTR5_ALLOW_TARGET_OF_TARGET_AS_TARGET', 0x00000800); // Auto-target target of target (client only)
define('SPELL_ATTR5_UNK12',                            0x00001000); // Unknown attribute 12@Attr5 DESCRIPTION Cleave related?
define('SPELL_ATTR5_HASTE_AFFECT_DURATION',            0x00002000); // Duration scales with Haste Rating
define('SPELL_ATTR5_UNK14',                            0x00004000); // Unknown attribute 14@Attr5
define('SPELL_ATTR5_UNK15',                            0x00008000); // Unknown attribute 15@Attr5 DESCRIPTION Related to multi-target spells?
define('SPELL_ATTR5_UNK16',                            0x00010000); // Unknown attribute 16@Attr5
define('SPELL_ATTR5_USABLE_WHILE_FEARED',              0x00020000); // Usable while feared
define('SPELL_ATTR5_USABLE_WHILE_CONFUSED',            0x00040000); // Usable while confused
define('SPELL_ATTR5_DONT_TURN_DURING_CAST',            0x00080000); // Do not auto-turn while casting
define('SPELL_ATTR5_UNK20',                            0x00100000); // Unknown attribute 20@Attr5
define('SPELL_ATTR5_UNK21',                            0x00200000); // Unknown attribute 21@Attr5
define('SPELL_ATTR5_UNK22',                            0x00400000); // Unknown attribute 22@Attr5
define('SPELL_ATTR5_UNK23',                            0x00800000); // Unknown attribute 23@Attr5
define('SPELL_ATTR5_UNK24',                            0x01000000); // Unknown attribute 24@Attr5
define('SPELL_ATTR5_UNK25',                            0x02000000); // Unknown attribute 25@Attr5
define('SPELL_ATTR5_SKIP_CHECKCAST_LOS_CHECK',         0x04000000); // Ignore line of sight checks
define('SPELL_ATTR5_DONT_SHOW_AURA_IF_SELF_CAST',      0x08000000); // Don't show aura if self-cast (client only)
define('SPELL_ATTR5_DONT_SHOW_AURA_IF_NOT_SELF_CAST',  0x10000000); // Don't show aura unless self-cast (client only)
define('SPELL_ATTR5_UNK29',                            0x20000000); // Unknown attribute 29@Attr5
define('SPELL_ATTR5_UNK30',                            0x40000000); // Unknown attribute 30@Attr5
define('SPELL_ATTR5_UNK31',                            0x80000000); // Unknown attribute 31@Attr5 DESCRIPTION Forces nearby enemies to attack caster?

define('SPELL_ATTR6_DONT_DISPLAY_COOLDOWN',            0x00000001); // Don't display cooldown (client only)
define('SPELL_ATTR6_ONLY_IN_ARENA',                    0x00000002); // Only usable in arena
define('SPELL_ATTR6_IGNORE_CASTER_AURAS',              0x00000004); // Ignore all preventing caster auras
define('SPELL_ATTR6_ASSIST_IGNORE_IMMUNE_FLAG',        0x00000008); // Ignore immunity flags when assisting
define('SPELL_ATTR6_UNK4',                             0x00000010); // Unknown attribute 4@Attr6
define('SPELL_ATTR6_DONT_CONSUME_PROC_CHARGES',        0x00000020); // Don't consume proc charges
define('SPELL_ATTR6_USE_SPELL_CAST_EVENT',             0x00000040); // Generate spell_cast event instead of aura_start (client only)
define('SPELL_ATTR6_UNK7',                             0x00000080); // Unknown attribute 7@Attr6
define('SPELL_ATTR6_CANT_TARGET_CROWD_CONTROLLED',     0x00000100); // Do not implicitly target in CC DESCRIPTION Implicit targeting (chaining and area targeting) will not impact crowd controlled targets
define('SPELL_ATTR6_UNK9',                             0x00000200); // Unknown attribute 9@Attr6
define('SPELL_ATTR6_CAN_TARGET_POSSESSED_FRIENDS',     0x00000400); // Can target possessed friends DESCRIPTION [NYI]
define('SPELL_ATTR6_NOT_IN_RAID_INSTANCE',             0x00000800); // Unusable in raid instances
define('SPELL_ATTR6_CASTABLE_WHILE_ON_VEHICLE',        0x00001000); // Castable while caster is on vehicle
define('SPELL_ATTR6_CAN_TARGET_INVISIBLE',             0x00002000); // Can target invisible units
define('SPELL_ATTR6_UNK14',                            0x00004000); // Unknown attribute 14@Attr6
define('SPELL_ATTR6_UNK15',                            0x00008000); // Unknown attribute 15@Attr6
define('SPELL_ATTR6_UNK16',                            0x00010000); // Unknown attribute 16@Attr6
define('SPELL_ATTR6_UNK17',                            0x00020000); // Unknown attribute 17@Attr6 DESCRIPTION Mount related?
define('SPELL_ATTR6_CAST_BY_CHARMER',                  0x00040000); // Spell is cast by charmer DESCRIPTION Client will prevent casting if not possessed, charmer will be caster for all intents and purposes
define('SPELL_ATTR6_UNK19',                            0x00080000); // Unknown attribute 19@Attr6
define('SPELL_ATTR6_ONLY_VISIBLE_TO_CASTER',           0x00100000); // Only visible to caster (client only)
define('SPELL_ATTR6_CLIENT_UI_TARGET_EFFECTS',         0x00200000); // Client UI target effects (client only)
define('SPELL_ATTR6_UNK22',                            0x00400000); // Unknown attribute 22@Attr6
define('SPELL_ATTR6_UNK23',                            0x00800000); // Unknown attribute 23@Attr6
define('SPELL_ATTR6_CAN_TARGET_UNTARGETABLE',          0x01000000); // Can target untargetable units
define('SPELL_ATTR6_NOT_RESET_SWING_IF_INSTANT',       0x02000000); // Do not reset swing timer if cast time is instant
define('SPELL_ATTR6_UNK26',                            0x04000000); // Unknown attribute 26@Attr6 DESCRIPTION Player castable buff?
define('SPELL_ATTR6_LIMIT_PCT_HEALING_MODS',           0x08000000); // Limit applicable %healing modifiers DESCRIPTION This prevents certain healing modifiers from applying - see implementation if you really care about details
define('SPELL_ATTR6_UNK28',                            0x10000000); // Unknown attribute 28@Attr6 DESCRIPTION Death grip?
define('SPELL_ATTR6_LIMIT_PCT_DAMAGE_MODS',            0x20000000); // Limit applicable %damage modifiers DESCRIPTION This prevents certain damage modifiers from applying - see implementation if you really care about details
define('SPELL_ATTR6_UNK30',                            0x40000000); // Unknown attribute 30@Attr6
define('SPELL_ATTR6_IGNORE_CATEGORY_COOLDOWN_MODS',    0x80000000); // Ignore cooldown modifiers for category cooldown

define('SPELL_ATTR7_UNK0',                             0x00000001); // Unknown attribute 0@Attr7
define('SPELL_ATTR7_IGNORE_DURATION_MODS',             0x00000002); // Ignore duration modifiers
define('SPELL_ATTR7_REACTIVATE_AT_RESURRECT',          0x00000004); // Reactivate at resurrect (client only)
define('SPELL_ATTR7_IS_CHEAT_SPELL',                   0x00000008); // Is cheat spell DESCRIPTION Cannot cast if caster doesn't have UnitFlag2 & UNIT_FLAG2_ALLOW_CHEAT_SPELLS
define('SPELL_ATTR7_UNK4',                             0x00000010); // Unknown attribute 4@Attr7 DESCRIPTION Soulstone related?
define('SPELL_ATTR7_SUMMON_PLAYER_TOTEM',              0x00000020); // Summons player-owned totem
define('SPELL_ATTR7_NO_PUSHBACK_ON_DAMAGE',            0x00000040); // Damage dealt by this does not cause spell pushback
define('SPELL_ATTR7_UNK7',                             0x00000080); // Unknown attribute 7@Attr7
define('SPELL_ATTR7_HORDE_ONLY',                       0x00000100); // Horde only
define('SPELL_ATTR7_ALLIANCE_ONLY',                    0x00000200); // Alliance only
define('SPELL_ATTR7_DISPEL_CHARGES',                   0x00000400); // Dispel/Spellsteal remove individual charges
define('SPELL_ATTR7_INTERRUPT_ONLY_NONPLAYER',         0x00000800); // Only interrupt non-player casting
define('SPELL_ATTR7_UNK12',                            0x00001000); // Unknown attribute 12@Attr7
define('SPELL_ATTR7_UNK13',                            0x00002000); // Unknown attribute 13@Attr7
define('SPELL_ATTR7_UNK14',                            0x00004000); // Unknown attribute 14@Attr7
define('SPELL_ATTR7_UNK15',                            0x00008000); // Unknown attribute 15@Attr7 DESCRIPTION Exorcism - guaranteed crit vs families?
define('SPELL_ATTR7_CAN_RESTORE_SECONDARY_POWER',      0x00010000); // Can restore secondary power DESCRIPTION Only spells with this attribute can replenish a non-active power type
define('SPELL_ATTR7_UNK17',                            0x00020000); // Unknown attribute 17@Attr7
define('SPELL_ATTR7_HAS_CHARGE_EFFECT',                0x00040000); // Has charge effect
define('SPELL_ATTR7_ZONE_TELEPORT',                    0x00080000); // Is zone teleport
define('SPELL_ATTR7_UNK20',                            0x00100000); // Unknown attribute 20@Attr7 DESCRIPTION Invulnerability related?
define('SPELL_ATTR7_UNK21',                            0x00200000); // Unknown attribute 21@Attr7
define('SPELL_ATTR7_IGNORE_COLD_WEATHER_FLYING',       0x00400000); // Ignore cold weather flying restriction DESCRIPTION Set for loaner mounts, allows them to be used despite lacking required flight skill
define('SPELL_ATTR7_UNK23',                            0x00800000); // Unknown attribute 23@Attr7
define('SPELL_ATTR7_UNK24',                            0x01000000); // Unknown attribute 24@Attr7
define('SPELL_ATTR7_UNK25',                            0x02000000); // Unknown attribute 25@Attr7
define('SPELL_ATTR7_UNK26',                            0x04000000); // Unknown attribute 26@Attr7
define('SPELL_ATTR7_UNK27',                            0x08000000); // Unknown attribute 27@Attr7
define('SPELL_ATTR7_CONSOLIDATED_RAID_BUFF',           0x10000000); // Consolidate in raid buff frame (client only)
define('SPELL_ATTR7_UNK29',                            0x20000000); // Unknown attribute 29@Attr7
define('SPELL_ATTR7_UNK30',                            0x40000000); // Unknown attribute 30@Attr7
define('SPELL_ATTR7_CLIENT_INDICATOR',                 0x80000000); // Client indicator (client only)


// (some) Skill ids
define('SKILL_FIRST_AID',      129);
define('SKILL_BLACKSMITHING',  164);
define('SKILL_LEATHERWORKING', 165);
define('SKILL_ALCHEMY',        171);
define('SKILL_HERBALISM',      182);
define('SKILL_COOKING',        185);
define('SKILL_MINING',         186);
define('SKILL_TAILORING',      197);
define('SKILL_ENGINEERING',    202);
define('SKILL_ENCHANTING',     333);
define('SKILL_FISHING',        356);
define('SKILL_SKINNING',       393);
define('SKILL_LOCKPICKING',    633);
define('SKILL_JEWELCRAFTING',  755);
define('SKILL_RIDING',         762);
define('SKILL_INSCRIPTION',    773);

define('SKILLS_TRADE_PRIMARY',   [SKILL_BLACKSMITHING, SKILL_LEATHERWORKING, SKILL_ALCHEMY, SKILL_HERBALISM, SKILL_MINING, SKILL_TAILORING, SKILL_ENGINEERING, SKILL_ENCHANTING, SKILL_SKINNING, SKILL_JEWELCRAFTING, SKILL_INSCRIPTION]);
define('SKILLS_TRADE_SECONDARY', [SKILL_FIRST_AID, SKILL_COOKING, SKILL_FISHING, SKILL_RIDING]);


// AchievementCriteriaCondition
define('ACHIEVEMENT_CRITERIA_CONDITION_NO_DEATH',                       1);         // reset progress on death
define('ACHIEVEMENT_CRITERIA_CONDITION_BG_MAP',                         3);         // requires you to be on specific map, reset at change
define('ACHIEVEMENT_CRITERIA_CONDITION_NOT_IN_GROUP',                   10);        // requires the player not to be in group

// AchievementFlags
define('ACHIEVEMENT_FLAG_COUNTER',                                      0x0001);    // Just count statistic (never stop and complete)
define('ACHIEVEMENT_FLAG_HIDDEN',                                       0x0002);    // Not sent to client - internal use only
define('ACHIEVEMENT_FLAG_STORE_MAX_VALUE',                              0x0004);    // Store only max value? used only in "Reach level xx"
define('ACHIEVEMENT_FLAG_SUM',                                          0x0008);    // Use sum criteria value from all reqirements (and calculate max value)
define('ACHIEVEMENT_FLAG_MAX_USED',                                     0x0010);    // Show max criteria (and calculate max value ??)
define('ACHIEVEMENT_FLAG_REQ_COUNT',                                    0x0020);    // Use not zero req count (and calculate max value)
define('ACHIEVEMENT_FLAG_AVERAGE',                                      0x0040);    // Show as average value (value / time_in_days) depend from other flag (by def use last criteria value)
define('ACHIEVEMENT_FLAG_PROGRESS_BAR',                                 0x0080);    // Show as progress bar (value / max vale) depend from other flag (by def use last criteria value)
define('ACHIEVEMENT_FLAG_REALM_FIRST',                                  0x0100);    // first max race/class/profession
define('ACHIEVEMENT_FLAG_REALM_FIRST_KILL',                             0x0200);    // first boss kill

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

// TrinityCore - SmartAI
define('SAI_SRC_TYPE_CREATURE',    0);
define('SAI_SRC_TYPE_OBJECT',      1);
define('SAI_SRC_TYPE_AREATRIGGER', 2);
define('SAI_SRC_TYPE_ACTIONLIST',  9);

define('SAI_EVENT_FLAG_NO_REPEAT',     0x0001);
define('SAI_EVENT_FLAG_DIFFICULTY_0',  0x0002);
define('SAI_EVENT_FLAG_DIFFICULTY_1',  0x0004);
define('SAI_EVENT_FLAG_DIFFICULTY_2',  0x0008);
define('SAI_EVENT_FLAG_DIFFICULTY_3',  0x0010);
define('SAI_EVENT_FLAG_NO_RESET',      0x0100);
define('SAI_EVENT_FLAG_WHILE_CHARMED', 0x0200);

define('SAI_EVENT_UPDATE_IC',               0);             // In combat.
define('SAI_EVENT_UPDATE_OOC',              1);             // Out of combat.
define('SAI_EVENT_HEALTH_PCT',              2);             // Health Percentage
define('SAI_EVENT_MANA_PCT',                3);             // Mana Percentage
define('SAI_EVENT_AGGRO',                   4);             // On Creature Aggro
define('SAI_EVENT_KILL',                    5);             // On Creature Kill
define('SAI_EVENT_DEATH',                   6);             // On Creature Death
define('SAI_EVENT_EVADE',                   7);             // On Creature Evade Attack
define('SAI_EVENT_SPELLHIT',                8);             // On Creature/Gameobject Spell Hit
define('SAI_EVENT_RANGE',                   9);             // On Target In Range
define('SAI_EVENT_OOC_LOS',                 10);            // On Target In Distance Out of Combat
define('SAI_EVENT_RESPAWN',                 11);            // On Creature/Gameobject Respawn
define('SAI_EVENT_TARGET_HEALTH_PCT',       12);            // On Target Health Percentage
define('SAI_EVENT_VICTIM_CASTING',          13);            // On Target Casting Spell
define('SAI_EVENT_FRIENDLY_HEALTH',         14);            // On Friendly Health Deficit
define('SAI_EVENT_FRIENDLY_IS_CC',          15);            //
define('SAI_EVENT_FRIENDLY_MISSING_BUFF',   16);            // On Friendly Lost Buff
define('SAI_EVENT_SUMMONED_UNIT',           17);            // On Creature/Gameobject Summoned Unit
define('SAI_EVENT_TARGET_MANA_PCT',         18);            // On Target Mana Percentage
define('SAI_EVENT_ACCEPTED_QUEST',          19);            // On Target Accepted Quest
define('SAI_EVENT_REWARD_QUEST',            20);            // On Target Rewarded Quest
define('SAI_EVENT_REACHED_HOME',            21);            // On Creature Reached Home
define('SAI_EVENT_RECEIVE_EMOTE',           22);            // On Receive Emote.
define('SAI_EVENT_HAS_AURA',                23);            // On Creature Has Aura
define('SAI_EVENT_TARGET_BUFFED',           24);            // On Target Buffed With Spell
define('SAI_EVENT_RESET',                   25);            // After Combat, On Respawn or Spawn
define('SAI_EVENT_IC_LOS',                  26);            // On Target In Distance In Combat
define('SAI_EVENT_PASSENGER_BOARDED',       27);            //
define('SAI_EVENT_PASSENGER_REMOVED',       28);            //
define('SAI_EVENT_CHARMED',                 29);            // On Creature Charmed
define('SAI_EVENT_CHARMED_TARGET',          30);            // On Target Charmed
define('SAI_EVENT_SPELLHIT_TARGET',         31);            // On Target Spell Hit
define('SAI_EVENT_DAMAGED',                 32);            // On Creature Damaged
define('SAI_EVENT_DAMAGED_TARGET',          33);            // On Target Damaged
define('SAI_EVENT_MOVEMENTINFORM',          34);            // WAYPOINT_MOTION_TYPE = 2,  POINT_MOTION_TYPE = 8
define('SAI_EVENT_SUMMON_DESPAWNED',        35);            // On Summoned Unit Despawned
define('SAI_EVENT_CORPSE_REMOVED',          36);            // On Creature Corpse Removed
define('SAI_EVENT_AI_INIT',                 37);            //
define('SAI_EVENT_DATA_SET',                38);            // On Creature/Gameobject Data Set, Can be used with SMART_ACTION_SET_DATA
define('SAI_EVENT_WAYPOINT_START',          39);            // On Creature Waypoint ID Started
define('SAI_EVENT_WAYPOINT_REACHED',        40);            // On Creature Waypoint ID Reached
// define('SAI_EVENT_TRANSPORT_ADDPLAYER',     41);         //
// define('SAI_EVENT_TRANSPORT_ADDCREATURE',   42);         //
// define('SAI_EVENT_TRANSPORT_REMOVE_PLAYER', 43);         //
// define('SAI_EVENT_TRANSPORT_RELOCATE',      44);         //
// define('SAI_EVENT_INSTANCE_PLAYER_ENTER',   45);         //
define('SAI_EVENT_AREATRIGGER_ONTRIGGER',   46);            //
// define('SAI_EVENT_QUEST_ACCEPTED',          47);         // On Target Quest Accepted
// define('SAI_EVENT_QUEST_OBJ_COMPLETION',    48);         // On Target Quest Objective Completed
// define('SAI_EVENT_QUEST_COMPLETION',        49);         // On Target Quest Completed
// define('SAI_EVENT_QUEST_REWARDED',          50);         // On Target Quest Rewarded
// define('SAI_EVENT_QUEST_FAIL',              51);         // On Target Quest Field
define('SAI_EVENT_TEXT_OVER',               52);            // On TEXT_OVER Event Triggered After SMART_ACTION_TALK
define('SAI_EVENT_RECEIVE_HEAL',            53);            // On Creature Received Healing
define('SAI_EVENT_JUST_SUMMONED',           54);            // On Creature Just spawned
define('SAI_EVENT_WAYPOINT_PAUSED',         55);            // On Creature Paused at Waypoint ID
define('SAI_EVENT_WAYPOINT_RESUMED',        56);            // On Creature Resumed after Waypoint ID
define('SAI_EVENT_WAYPOINT_STOPPED',        57);            // On Creature Stopped On Waypoint ID
define('SAI_EVENT_WAYPOINT_ENDED',          58);            // On Creature Waypoint Path Ended
define('SAI_EVENT_TIMED_EVENT_TRIGGERED',   59);            //
define('SAI_EVENT_UPDATE',                  60);            //
define('SAI_EVENT_LINK',                    61);            // Used to link together multiple events as a chain of events.
define('SAI_EVENT_GOSSIP_SELECT',           62);            // On gossip clicked (gossip_menu_option335).
define('SAI_EVENT_JUST_CREATED',            63);            //
define('SAI_EVENT_GOSSIP_HELLO',            64);            // On Right-Click Creature/Gameobject that have gossip enabled.
define('SAI_EVENT_FOLLOW_COMPLETED',        65);            //
define('SAI_EVENT_EVENT_PHASE_CHANGE',      66);            // On event phase mask set
define('SAI_EVENT_IS_BEHIND_TARGET',        67);            // On Creature is behind target.
define('SAI_EVENT_GAME_EVENT_START',        68);            // On game_event started.
define('SAI_EVENT_GAME_EVENT_END',          69);            // On game_event ended.
define('SAI_EVENT_GO_STATE_CHANGED',        70);            //
define('SAI_EVENT_GO_EVENT_INFORM',         71);            //
define('SAI_EVENT_ACTION_DONE',             72);            //
define('SAI_EVENT_ON_SPELLCLICK',           73);            //
define('SAI_EVENT_FRIENDLY_HEALTH_PCT',     74);            //
define('SAI_EVENT_DISTANCE_CREATURE',       75);            // On creature guid OR any instance of creature entry is within distance.
define('SAI_EVENT_DISTANCE_GAMEOBJECT',     76);            // On gameobject guid OR any instance of gameobject entry is within distance.
define('SAI_EVENT_COUNTER_SET',             77);            // If the value of specified counterID is equal to a specified value
// define('SAI_EVENT_SCENE_START',             78);         // don't use on 3.3.5a
// define('SAI_EVENT_SCENE_TRIGGER',           79);         // don't use on 3.3.5a
// define('SAI_EVENT_SCENE_CANCEL',            80);         // don't use on 3.3.5a
// define('SAI_EVENT_SCENE_COMPLETE',          81);         // don't use on 3.3.5a
define('SAI_EVENT_SUMMONED_UNIT_DIES',      82);            // CreatureId(0 all), CooldownMin, CooldownMax

define('SAI_ACTION_NONE',                               0);   //  Do nothing
define('SAI_ACTION_TALK',                               1);   //  Param2 in Milliseconds.
define('SAI_ACTION_SET_FACTION',                        2);   //  Sets faction to creature.
define('SAI_ACTION_MORPH_TO_ENTRY_OR_MODEL',            3);   //  Take DisplayID of creature (param1) OR Turn to DisplayID (param2) OR Both = 0 for Demorph
define('SAI_ACTION_SOUND',                              4);   //  TextRange = 0 only sends sound to self, TextRange = 1 sends sound to everyone in visibility range
define('SAI_ACTION_PLAY_EMOTE',                         5);   //  Play Emote
define('SAI_ACTION_FAIL_QUEST',                         6);   //  Fail Quest of Target
define('SAI_ACTION_OFFER_QUEST',                        7);   //  Add Quest to Target
define('SAI_ACTION_SET_REACT_STATE',                    8);   //  React State. Can be Passive (0), Defensive (1), Aggressive (2), Assist (3).
define('SAI_ACTION_ACTIVATE_GOBJECT',                   9);   //  Activate Object
define('SAI_ACTION_RANDOM_EMOTE',                       10);  //  Play Random Emote
define('SAI_ACTION_CAST',                               11);  //  Cast Spell ID at Target
define('SAI_ACTION_SUMMON_CREATURE',                    12);  //  Summon Unit
define('SAI_ACTION_THREAT_SINGLE_PCT',                  13);  //  Change Threat Percentage for Single Target
define('SAI_ACTION_THREAT_ALL_PCT',                     14);  //  Change Threat Percentage for All Enemies
define('SAI_ACTION_CALL_AREAEXPLOREDOREVENTHAPPENS',    15);  //
// define('SAI_ACTION_SET_INGAME_PHASE_ID',             16);  //  For 4.3.4 + only
define('SAI_ACTION_SET_EMOTE_STATE',                    17);  //  Play Emote Continuously
define('SAI_ACTION_SET_UNIT_FLAG',                      18);  //  Can set Multi-able flags at once
define('SAI_ACTION_REMOVE_UNIT_FLAG',                   19);  //  Can Remove Multi-able flags at once
define('SAI_ACTION_AUTO_ATTACK',                        20);  //  Stop or Continue Automatic Attack.
define('SAI_ACTION_ALLOW_COMBAT_MOVEMENT',              21);  //  Allow or Disable Combat Movement
define('SAI_ACTION_SET_EVENT_PHASE',                    22);  //
define('SAI_ACTION_INC_EVENT_PHASE',                    23);  //  Set param1 OR param2 (not both). Value 0 has no effect.
define('SAI_ACTION_EVADE',                              24);  //  Evade Incoming Attack
define('SAI_ACTION_FLEE_FOR_ASSIST',                    25);  //  If you want the fleeing NPC to say '%s attempts to run away in fear' on flee, use 1 on param1. 0 for no message.
define('SAI_ACTION_CALL_GROUPEVENTHAPPENS',             26);  //
define('SAI_ACTION_COMBAT_STOP',                        27);  //
define('SAI_ACTION_REMOVEAURASFROMSPELL',               28);  //  0 removes all auras
define('SAI_ACTION_FOLLOW',                             29);  //  Follow Target
define('SAI_ACTION_RANDOM_PHASE',                       30);  //
define('SAI_ACTION_RANDOM_PHASE_RANGE',                 31);  //
define('SAI_ACTION_RESET_GOBJECT',                      32);  //  Reset Gameobject
define('SAI_ACTION_CALL_KILLEDMONSTER',                 33);  //  This is the ID from quest_template.RequiredNpcOrGo
define('SAI_ACTION_SET_INST_DATA',                      34);  //  Set Instance Data
// define('SAI_ACTION_SET_INST_DATA64',                 35);  //  Set Instance Data uint64
define('SAI_ACTION_UPDATE_TEMPLATE',                    36);  //  Updates creature_template to given entry
define('SAI_ACTION_DIE',                                37);  //  Kill Target
define('SAI_ACTION_SET_IN_COMBAT_WITH_ZONE',            38);  //
define('SAI_ACTION_CALL_FOR_HELP',                      39);  //  If you want the NPC to say '%s calls for help!'. Use 1 on param1, 0 for no message.
define('SAI_ACTION_SET_SHEATH',                         40);  //
define('SAI_ACTION_FORCE_DESPAWN',                      41);  //  Despawn Target after param1 in Milliseconds. If you want to set respawn time set param2 in seconds.
define('SAI_ACTION_SET_INVINCIBILITY_HP_LEVEL',         42);  //  If you use both params, only percent will be used.
define('SAI_ACTION_MOUNT_TO_ENTRY_OR_MODEL',            43);  //  Mount to Creature Entry (param1) OR Mount to Creature Display (param2) Or both = 0 for Unmount
define('SAI_ACTION_SET_INGAME_PHASE_MASK',              44);  //
define('SAI_ACTION_SET_DATA',                           45);  //  Set Data For Target, can be used with SMART_EVENT_DATA_SET
define('SAI_ACTION_ATTACK_STOP',                        46);  //
define('SAI_ACTION_SET_VISIBILITY',                     47);  //  Makes creature Visible = 1  or  Invisible = 0
define('SAI_ACTION_SET_ACTIVE',                         48);  //
define('SAI_ACTION_ATTACK_START',                       49);  //  Allows basic melee swings to creature.
define('SAI_ACTION_SUMMON_GO',                          50);  //  Spawns Gameobject, use target_type to set spawn position.
define('SAI_ACTION_KILL_UNIT',                          51);  //  Kills Creature.
define('SAI_ACTION_ACTIVATE_TAXI',                      52);  //  Sends player to flight path. You have to be close to Flight Master, which gives Taxi ID you need.
define('SAI_ACTION_WP_START',                           53);  //  Creature starts Waypoint Movement. Use waypoints table to create movement.
define('SAI_ACTION_WP_PAUSE',                           54);  //  Creature pauses its Waypoint Movement for given time.
define('SAI_ACTION_WP_STOP',                            55);  //  Creature stops its Waypoint Movement.
define('SAI_ACTION_ADD_ITEM',                           56);  //  Adds item(s) to player.
define('SAI_ACTION_REMOVE_ITEM',                        57);  //  Removes item(s) from player.
define('SAI_ACTION_INSTALL_AI_TEMPLATE',                58);  //
define('SAI_ACTION_SET_RUN',                            59);  //
define('SAI_ACTION_SET_DISABLE_GRAVITY',                60);  //  Only works for creatures with inhabit air.
define('SAI_ACTION_SET_SWIM',                           61);  //
define('SAI_ACTION_TELEPORT',                           62);  //  Continue this action with the TARGET_TYPE column. Use any target_type (except 0), and use target_x, target_y, target_z, target_o as the coordinates
define('SAI_ACTION_SET_COUNTER',                        63);  //
define('SAI_ACTION_STORE_TARGET_LIST',                  64);  //
define('SAI_ACTION_WP_RESUME',                          65);  //  Creature continues in its Waypoint Movement.
define('SAI_ACTION_SET_ORIENTATION',                    66);  //
define('SAI_ACTION_CREATE_TIMED_EVENT',                 67);  //
define('SAI_ACTION_PLAYMOVIE',                          68);  //
define('SAI_ACTION_MOVE_TO_POS',                        69);  //  PointId is called by SMART_EVENT_MOVEMENTINFORM. Continue this action with the TARGET_TYPE column. Use any target_type, and use target_x, target_y, target_z, target_o as the coordinates
define('SAI_ACTION_ENABLE_TEMP_GOBJ',                   70);  //  param1 = duration
define('SAI_ACTION_EQUIP',                              71);  //  only slots with mask set will be sent to client, bits are 1, 2, 4, leaving mask 0 is defaulted to mask 7 (send all), Slots1-3 are only used if no Param1 is set
define('SAI_ACTION_CLOSE_GOSSIP',                       72);  //  Closes gossip window.
define('SAI_ACTION_TRIGGER_TIMED_EVENT',                73);  //
define('SAI_ACTION_REMOVE_TIMED_EVENT',                 74);  //
define('SAI_ACTION_ADD_AURA',                           75);  //  Adds aura to player(s). Use target_type 17 to make AoE aura.
define('SAI_ACTION_OVERRIDE_SCRIPT_BASE_OBJECT',        76);  //  WARNING: CAN CRASH CORE, do not use if you dont know what you are doing
define('SAI_ACTION_RESET_SCRIPT_BASE_OBJECT',           77);  //
define('SAI_ACTION_CALL_SCRIPT_RESET',                  78);  //
define('SAI_ACTION_SET_RANGED_MOVEMENT',                79);  //  Sets movement to follow at a specific range to the target.
define('SAI_ACTION_CALL_TIMED_ACTIONLIST',              80);  //
define('SAI_ACTION_SET_NPC_FLAG',                       81);  //
define('SAI_ACTION_ADD_NPC_FLAG',                       82);  //
define('SAI_ACTION_REMOVE_NPC_FLAG',                    83);  //
define('SAI_ACTION_SIMPLE_TALK',                        84);  //  Makes a player say text. SMART_EVENT_TEXT_OVER is not triggered and whispers can not be used.
define('SAI_ACTION_SELF_CAST',                          85);  //  spellID, castFlags
define('SAI_ACTION_CROSS_CAST',                         86);  //  This action is used to make selected caster (in CasterTargetType) to cast spell. Actual target is entered in target_type as normally.
define('SAI_ACTION_CALL_RANDOM_TIMED_ACTIONLIST',       87);  //  Will select one entry from the ones provided. 0 is ignored.
define('SAI_ACTION_CALL_RANDOM_RANGE_TIMED_ACTIONLIST', 88);  //  0 is ignored.
define('SAI_ACTION_RANDOM_MOVE',                        89);  //  Creature moves to random position in given radius.
define('SAI_ACTION_SET_UNIT_FIELD_BYTES_1',             90);  //
define('SAI_ACTION_REMOVE_UNIT_FIELD_BYTES_1',          91);  //
define('SAI_ACTION_INTERRUPT_SPELL',                    92);  //  This action allows you to interrupt the current spell being cast. If you do not set the spellId, the core will find the current spell depending on the withDelay and the withInstant values.
define('SAI_ACTION_SEND_GO_CUSTOM_ANIM',                93);  //
define('SAI_ACTION_SET_DYNAMIC_FLAG',                   94);  //
define('SAI_ACTION_ADD_DYNAMIC_FLAG',                   95);  //
define('SAI_ACTION_REMOVE_DYNAMIC_FLAG',                96);  //
define('SAI_ACTION_JUMP_TO_POS',                        97);  //
define('SAI_ACTION_SEND_GOSSIP_MENU',                   98);  //  Can be used together with 'SMART_EVENT_GOSSIP_HELLO' to set custom gossip.
define('SAI_ACTION_GO_SET_LOOT_STATE',                  99);  //
define('SAI_ACTION_SEND_TARGET_TO_TARGET',              100); //  Send targets previously stored with SMART_ACTION_STORE_TARGET, to another npc/go, the other npc/go can then access them as if it was its own stored list
define('SAI_ACTION_SET_HOME_POS',                       101); //  Use with SMART_TARGET_SELF or SMART_TARGET_POSITION
define('SAI_ACTION_SET_HEALTH_REGEN',                   102); //  Sets the current creatures health regen on or off.
define('SAI_ACTION_SET_ROOT',                           103); //  Enables or disables creature movement
define('SAI_ACTION_SET_GO_FLAG',                        104); //  oldFlag = newFlag
define('SAI_ACTION_ADD_GO_FLAG',                        105); //  oldFlag |= newFlag
define('SAI_ACTION_REMOVE_GO_FLAG',                     106); //  oldFlag &= ~newFlag
define('SAI_ACTION_SUMMON_CREATURE_GROUP',              107); //  Use creature_summon_groups table. SAI target has no effect, use 0
define('SAI_ACTION_SET_POWER',                          108); //
define('SAI_ACTION_ADD_POWER',                          109); //
define('SAI_ACTION_REMOVE_POWER',                       110); //
define('SAI_ACTION_GAME_EVENT_STOP',                    111); //
define('SAI_ACTION_GAME_EVENT_START',                   112); //
define('SAI_ACTION_START_CLOSEST_WAYPOINT',             113); //  Make target follow closest waypoint to its location
define('SAI_ACTION_MOVE_OFFSET',                        114); //  Use  target_x,  target_y,  target_z With target_type=1
define('SAI_ACTION_RANDOM_SOUND',                       115); //
define('SAI_ACTION_SET_CORPSE_DELAY',                   116); //
define('SAI_ACTION_DISABLE_EVADE',                      117); //
define('SAI_ACTION_GO_SET_GO_STATE',                    118); //
define('SAI_ACTION_SET_CAN_FLY',                        119); //
define('SAI_ACTION_REMOVE_AURAS_BY_TYPE',               120); //
define('SAI_ACTION_SET_SIGHT_DIST',                     121); //
define('SAI_ACTION_FLEE',                               122); //
define('SAI_ACTION_ADD_THREAT',                         123); //
define('SAI_ACTION_LOAD_EQUIPMENT',                     124); //
define('SAI_ACTION_TRIGGER_RANDOM_TIMED_EVENT',         125); //
define('SAI_ACTION_REMOVE_ALL_GAMEOBJECTS',             126); //
define('SAI_ACTION_PAUSE_MOVEMENT',                     127); //  MovementSlot (default = 0, active = 1, controlled = 2), PauseTime (ms), Force
// define('SAI_ACTION_PLAY_ANIMKIT',                    128); //  don't use on 3.3.5a
// define('SAI_ACTION_SCENE_PLAY',                      129); //  don't use on 3.3.5a
// define('SAI_ACTION_SCENE_CANCEL',                    130); //  don't use on 3.3.5a
define('SAI_ACTION_SPAWN_SPAWNGROUP',                   131); //
define('SAI_ACTION_DESPAWN_SPAWNGROUP',                 132); //
define('SAI_ACTION_RESPAWN_BY_SPAWNID',                 133); //  type, typeGuid - Use to respawn npcs and gobs, the target in this case is always=1 and only a single unit could be a target via the spawnId (action_param1, action_param2)
define('SAI_ACTION_INVOKER_CAST',                       134); //  spellID, castFlags
define('SAI_ACTION_PLAY_CINEMATIC',                     135); //  cinematic
define('SAI_ACTION_SET_MOVEMENT_SPEED',                 136); //  movementType, speedInteger, speedFraction
define('SAI_ACTION_PLAY_SPELL_VISUAL_KIT',              137); //  spellVisualKitId (RESERVED, PENDING CHERRYPICK)
define('SAI_ACTION_OVERRIDE_LIGHT',                     138); //  zoneId, areaLightId, overrideLightID, transitionMilliseconds
define('SAI_ACTION_OVERRIDE_WEATHER',                   139); //  zoneId, weatherId, intensity

define('SAI_ACTION_ALL_SPELLCASTS',         [SAI_ACTION_CAST, SAI_ACTION_ADD_AURA, SAI_ACTION_INVOKER_CAST, SAI_ACTION_SELF_CAST, SAI_ACTION_CROSS_CAST]);
define('SAI_ACTION_ALL_TIMED_ACTION_LISTS', [SAI_ACTION_CALL_TIMED_ACTIONLIST, SAI_ACTION_CALL_RANDOM_TIMED_ACTIONLIST, SAI_ACTION_CALL_RANDOM_RANGE_TIMED_ACTIONLIST]);

define('SAI_CAST_FLAG_INTERRUPT_PREV', 0x01);
define('SAI_CAST_FLAG_TRIGGERED',      0x02);
// define('SAI_CAST_FORCE_CAST',          0x04);            // Forces cast even if creature is out of mana or out of range
// define('SAI_CAST_NO_MELEE_IF_OOM',     0x08);            // Prevents creature from entering melee if out of mana or out of range
// define('SAI_CAST_FORCE_TARGET_SELF',   0x10);            // the target to cast this spell on itself
define('SAI_CAST_FLAG_AURA_MISSING',   0x20);
define('SAI_CAST_FLAG_COMBAT_MOVE',    0x40);

define('SAI_REACT_PASSIVE',    0);
define('SAI_REACT_DEFENSIVE',  1);
define('SAI_REACT_AGGRESSIVE', 2);
define('SAI_REACT_ASSIST',     3);

define('SAI_SUMMON_TIMED_OR_DEAD_DESPAWN',   1);
define('SAI_SUMMON_TIMED_OR_CORPSE_DESPAWN', 2);
define('SAI_SUMMON_TIMED_DESPAWN',           3);
define('SAI_SUMMON_TIMED_DESPAWN_OOC',       4);
define('SAI_SUMMON_CORPSE_DESPAWN',          5);
define('SAI_SUMMON_CORPSE_TIMED_DESPAWN',    6);
define('SAI_SUMMON_DEAD_DESPAWN',            7);
define('SAI_SUMMON_MANUAL_DESPAWN',          8);

define('SAI_TARGET_NONE',                   0);             //  None.
define('SAI_TARGET_SELF',                   1);             //  Self cast.
define('SAI_TARGET_VICTIM',                 2);             //  Our current target. (ie: highest aggro)
define('SAI_TARGET_HOSTILE_SECOND_AGGRO',   3);             //  Second highest aggro.
define('SAI_TARGET_HOSTILE_LAST_AGGRO',     4);             //  Dead last on aggro.
define('SAI_TARGET_HOSTILE_RANDOM',         5);             //  Just any random target on our threat list.
define('SAI_TARGET_HOSTILE_RANDOM_NOT_TOP', 6);             //  Any random target except top threat.
define('SAI_TARGET_ACTION_INVOKER',         7);             //  Unit who caused this Event to occur.
define('SAI_TARGET_POSITION',               8);             //  Use xyz from event params.
define('SAI_TARGET_CREATURE_RANGE',         9);             //  (Random?) creature with specified ID within specified range.
define('SAI_TARGET_CREATURE_GUID',          10);            //  Creature with specified GUID.
define('SAI_TARGET_CREATURE_DISTANCE',      11);            //  Creature with specified ID within distance. (Different from #9?)
define('SAI_TARGET_STORED',                 12);            //  Uses pre-stored target(list)
define('SAI_TARGET_GAMEOBJECT_RANGE',       13);            //  (Random?) object with specified ID within specified range.
define('SAI_TARGET_GAMEOBJECT_GUID',        14);            //  Object with specified GUID.
define('SAI_TARGET_GAMEOBJECT_DISTANCE',    15);            //  Object with specified ID within distance. (Different from #13?)
define('SAI_TARGET_INVOKER_PARTY',          16);            //  Invoker's party members
define('SAI_TARGET_PLAYER_RANGE',           17);            //  (Random?) player within specified range.
define('SAI_TARGET_PLAYER_DISTANCE',        18);            //  (Random?) player within specified distance. (Different from #17?)
define('SAI_TARGET_CLOSEST_CREATURE',       19);            //  Closest creature with specified ID within specified range.
define('SAI_TARGET_CLOSEST_GAMEOBJECT',     20);            //  Closest object with specified ID within specified range.
define('SAI_TARGET_CLOSEST_PLAYER',         21);            //  Closest player within specified range.
define('SAI_TARGET_ACTION_INVOKER_VEHICLE', 22);            //  Unit's vehicle who caused this Event to occur
define('SAI_TARGET_OWNER_OR_SUMMONER',      23);            //  Unit's owner or summoner
define('SAI_TARGET_THREAT_LIST',            24);            //  All units on creature's threat list
define('SAI_TARGET_CLOSEST_ENEMY',          25);            //  Any attackable target (creature or player) within maxDist
define('SAI_TARGET_CLOSEST_FRIENDLY',       26);            //  Any friendly unit (creature, player or pet) within maxDist
define('SAI_TARGET_LOOT_RECIPIENTS',        27);            //  All tagging players
define('SAI_TARGET_FARTHEST',               28);            //  Farthest unit on the threat list
define('SAI_TARGET_VEHICLE_PASSENGER',      29);            //  Vehicle can target unit in given seat
define('SAI_TARGET_CLOSEST_UNSPAWNED_GO',   30);            //  entry(0any), maxDist

define('SAI_TEMPLATE_BASIC',          0);                   //
define('SAI_TEMPLATE_CASTER',         1);                   //  +JOIN: target_param1 as castFlag
define('SAI_TEMPLATE_TURRET',         2);                   //  +JOIN: target_param1 as castflag
define('SAI_TEMPLATE_PASSIVE',        3);                   //
define('SAI_TEMPLATE_CAGED_GO_PART',  4);                   //
define('SAI_TEMPLATE_CAGED_NPC_PART', 5);                   //

define('SAI_SPAWN_FLAG_NONE',           0x00);
define('SAI_SPAWN_FLAG_IGNORE_RESPAWN', 0x01);               // onSpawnIn - ignore & reset respawn timer
define('SAI_SPAWN_FLAG_FORCE_SPAWN',    0x02);               // onSpawnIn - force additional spawn if already in world
define('SAI_SPAWN_FLAG_NOSAVE_RESPAWN', 0x04);               // onDespawn - remove respawn time

// profiler queue interactions
define('PR_QUEUE_STATUS_ENDED',   0);
define('PR_QUEUE_STATUS_WAITING', 1);
define('PR_QUEUE_STATUS_WORKING', 2);
define('PR_QUEUE_STATUS_READY',   3);
define('PR_QUEUE_STATUS_ERROR',   4);
define('PR_QUEUE_ERROR_UNK',      0);
define('PR_QUEUE_ERROR_CHAR',     1);
define('PR_QUEUE_ERROR_ARMORY',   2);

// profiler completion manager
define('PR_EXCLUDE_GROUP_UNAVAILABLE',         0x001);
define('PR_EXCLUDE_GROUP_TCG',                 0x002);
define('PR_EXCLUDE_GROUP_COLLECTORS_EDITION',  0x004);
define('PR_EXCLUDE_GROUP_PROMOTION',           0x008);
define('PR_EXCLUDE_GROUP_WRONG_REGION',        0x010);
define('PR_EXCLUDE_GROUP_REQ_ALLIANCE',        0x020);
define('PR_EXCLUDE_GROUP_REQ_HORDE',           0x040);
define('PR_EXCLUDE_GROUP_OTHER_FACTION',       PR_EXCLUDE_GROUP_REQ_ALLIANCE | PR_EXCLUDE_GROUP_REQ_HORDE);
define('PR_EXCLUDE_GROUP_REQ_FISHING',         0x080);
define('PR_EXCLUDE_GROUP_REQ_ENGINEERING',     0x100);
define('PR_EXCLUDE_GROUP_REQ_TAILORING',       0x200);
define('PR_EXCLUDE_GROUP_WRONG_PROFESSION',    PR_EXCLUDE_GROUP_REQ_FISHING | PR_EXCLUDE_GROUP_REQ_ENGINEERING | PR_EXCLUDE_GROUP_REQ_TAILORING);
define('PR_EXCLUDE_GROUP_REQ_CANT_BE_EXALTED', 0x400);
define('PR_EXCLUDE_GROUP_ANY',                 0x7FF);

// Areatrigger types
define('AT_TYPE_NONE',      0);
define('AT_TYPE_TAVERN',    1);
define('AT_TYPE_TELEPORT',  2);
define('AT_TYPE_OBJECTIVE', 3);
define('AT_TYPE_SMART',     4);
define('AT_TYPE_SCRIPT',    5);

// Drop Sources
define('SRC_CRAFTED',         1);
define('SRC_DROP',            2);
define('SRC_PVP',             3);
define('SRC_QUEST',           4);
define('SRC_VENDOR',          5);
define('SRC_TRAINER',         6);
define('SRC_DISCOVERY',       7);
define('SRC_REDEMPTION',      8);                           // unused
define('SRC_TALENT',          9);
define('SRC_STARTER',        10);
define('SRC_EVENT',          11);                           // unused
define('SRC_ACHIEVEMENT',    12);
define('SRC_CUSTOM_STRING',  13);
// define('SRC_BLACK_MARKET',   14);                        // not in 3.3.5
define('SRC_DISENCHANTMENT', 15);
define('SRC_FISHING',        16);
define('SRC_GATHERING',      17);
define('SRC_MILLING',        18);
define('SRC_MINING',         19);
define('SRC_PROSPECTING',    20);
define('SRC_PICKPOCKETING',  21);
define('SRC_SALVAGING',      22);
define('SRC_SKINNING',       23);
// define('SRC_INGAME_STORE',   24);                        // not in 3.3.5

define('SRC_SUB_PVP_ARENA', 1);
define('SRC_SUB_PVP_BG',    2);
define('SRC_SUB_PVP_WORLD', 4);

define('SRC_FLAG_BOSSDROP',     0x01);
define('SRC_FLAG_COMMON',       0x02);
define('SRC_FLAG_DUNGEON_DROP', 0x10);
define('SRC_FLAG_RAID_DROP',    0x20);

// Map Types
define('MAP_TYPE_ZONE',          0);
define('MAP_TYPE_TRANSIT',       1);
define('MAP_TYPE_DUNGEON',       2);
define('MAP_TYPE_RAID',          3);
define('MAP_TYPE_BATTLEGROUND',  4);
define('MAP_TYPE_DUNGEON_HC',    5);
define('MAP_TYPE_ARENA',         6);
define('MAP_TYPE_MMODE_RAID',    7);
define('MAP_TYPE_MMODE_RAID_HC', 8);

define('EMOTE_FLAG_ONLY_STANDING',       0x0001);           // Only while standig
define('EMOTE_FLAG_USE_MOUNT',           0x0002);           // Emote applies to mount
define('EMOTE_FLAG_NOT_CHANNELING',      0x0004);           // Not while channeling
define('EMOTE_FLAG_ANIM_TALK',           0x0008);           // Talk anim - talk
define('EMOTE_FLAG_ANIM_QUESTION',       0x0010);           // Talk anim - question
define('EMOTE_FLAG_ANIM_EXCLAIM',        0x0020);           // Talk anim - exclamation
define('EMOTE_FLAG_ANIM_SHOUT',          0x0040);           // Talk anim - shout
define('EMOTE_FLAG_NOT_SWIMMING',        0x0080);           // Not while swimming
define('EMOTE_FLAG_ANIM_LAUGH',          0x0100);           // Talk anim - laugh
define('EMOTE_FLAG_CAN_LIE_ON_GROUND',   0x0200);           // Ok while sleeping or dead
define('EMOTE_FLAG_NOT_FROM_CLIENT',     0x0400);           // Disallow from client
define('EMOTE_FLAG_NOT_CASTING',         0x0800);           // Not while casting
define('EMOTE_FLAG_END_MOVEMENT',        0x1000);           // Movement ends
define('EMOTE_FLAG_INTERRUPT_ON_ATTACK', 0x2000);           // Interrupt on attack
define('EMOTE_FLAG_ONLY_STILL',          0x4000);           // Only while still
define('EMOTE_FLAG_NOT_FLYING',          0x8000);           // Not while flying
?>
