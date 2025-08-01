<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


abstract class Type
{
    public const NPC =                          1;
    public const OBJECT =                       2;
    public const ITEM =                         3;
    public const ITEMSET =                      4;
    public const QUEST =                        5;
    public const SPELL =                        6;
    public const ZONE =                         7;
    public const FACTION =                      8;
    public const PET =                          9;
    public const ACHIEVEMENT =                 10;
    public const TITLE =                       11;
    public const WORLDEVENT =                  12;
    public const CHR_CLASS =                   13;
    public const CHR_RACE =                    14;
    public const SKILL =                       15;
    public const STATISTIC =                   16;
    public const CURRENCY =                    17;
    //           PROJECT =                     18;
    public const SOUND =                       19;
    //           BUILDING =                    20;
    //           FOLLOWER =                    21;
    //           MISSION_ABILITY =             22;
    //           MISSION =                     23;
    //           SHIP   =                      25;
    //           THREAT =                      26;
    //           RESOURCE =                    27;
    //           CHAMPION =                    28;
    public const ICON =                        29;
    //           ORDER_ADVANCEMENT =           30;
    //           FOLLOWER_ALLIANCE =           31;
    //           FOLLOWER_HORDE =              32;
    //           SHIP_ALLIANCE =               33;
    //           SHIP_HORDE =                  34;
    //           CHAMPION_ALLIANCE =           35;
    //           CHAMPION_HORDE =              36;
    //           TRANSMOG_ITEM =               37;
    //           BFA_CHAMPION =                38;
    //           BFA_CHAMPION_ALLIANCE =       39;
    //           AFFIX =                       40;
    //           BFA_CHAMPION_HORDE =          41;
    //           AZERITE_ESSENCE_POWER =       42;
    //           AZERITE_ESSENCE =             43;
    //           STORYLINE =                   44;
    //           ADVENTURE_COMBATANT_ABILITY = 46;
    //           ENCOUNTER =                   47;
    //           COVENANT =                    48;
    //           SOULBIND =                    49;
    //           DI_ITEM =                     50;
    //           GATHERER_SCREENSHOT =         91;
    //           GATHERER_GUIDE_IMAGE =        98;
    public const PROFILE =                    100;
    // our own things
    public const GUILD =                      101;
    //           TRANSMOG_SET =               101;          // future conflict inc.
    public const ARENA_TEAM =                 102;
    //           OUTFIT =                     110;
    //           GEAR_SET =                   111;
    //           GATHERER_LISTVIEW =          158;
    //           GATHERER_SURVEY_COVENANTS =  161;
    //           NEWS_POST =                  162;
    //           BATTLE_PET_ABILITY =         200;
    public const GUIDE =                      300;          // should have been 100, but conflicts with old version of Profile/List
    public const USER =                       500;
    public const EMOTE =                      501;
    public const ENCHANTMENT =                502;
    public const AREATRIGGER =                503;
    public const MAIL =                       504;
    // Blizzard API things
    //           MOUNT =                    -1000;
    //           RECIPE =                   -1001;
    //           BATTLE_PET =               -1002;

    public const FLAG_NONE              = 0x0;
    public const FLAG_RANDOM_SEARCHABLE = 0x1;
    public const FLAG_FILTRABLE         = 0x2;
    public const FLAG_DB_TYPE           = 0x4;
    public const FLAG_HAS_ICON          = 0x8;

    public const IDX_LIST_OBJ = 0;
    public const IDX_FILE_STR = 1;
    public const IDX_JSG_TPL  = 2;
    public const IDX_FLAGS    = 3;

    private static array $data = array(
        self::NPC         => [__NAMESPACE__ . '\CreatureList',    'npc',         'g_npcs',               self::FLAG_RANDOM_SEARCHABLE | self::FLAG_FILTRABLE | self::FLAG_DB_TYPE],
        self::OBJECT      => [__NAMESPACE__ . '\GameObjectList',  'object',      'g_objects',            self::FLAG_RANDOM_SEARCHABLE | self::FLAG_FILTRABLE | self::FLAG_DB_TYPE],
        self::ITEM        => [__NAMESPACE__ . '\ItemList',        'item',        'g_items',              self::FLAG_RANDOM_SEARCHABLE | self::FLAG_FILTRABLE | self::FLAG_DB_TYPE | self::FLAG_HAS_ICON],
        self::ITEMSET     => [__NAMESPACE__ . '\ItemsetList',     'itemset',     'g_itemsets',           self::FLAG_RANDOM_SEARCHABLE | self::FLAG_FILTRABLE | self::FLAG_DB_TYPE],
        self::QUEST       => [__NAMESPACE__ . '\QuestList',       'quest',       'g_quests',             self::FLAG_RANDOM_SEARCHABLE | self::FLAG_FILTRABLE | self::FLAG_DB_TYPE],
        self::SPELL       => [__NAMESPACE__ . '\SpellList',       'spell',       'g_spells',             self::FLAG_RANDOM_SEARCHABLE | self::FLAG_FILTRABLE | self::FLAG_DB_TYPE | self::FLAG_HAS_ICON],
        self::ZONE        => [__NAMESPACE__ . '\ZoneList',        'zone',        'g_gatheredzones',      self::FLAG_RANDOM_SEARCHABLE | self::FLAG_DB_TYPE],
        self::FACTION     => [__NAMESPACE__ . '\FactionList',     'faction',     'g_factions',           self::FLAG_RANDOM_SEARCHABLE | self::FLAG_DB_TYPE],
        self::PET         => [__NAMESPACE__ . '\PetList',         'pet',         'g_pets',               self::FLAG_RANDOM_SEARCHABLE | self::FLAG_DB_TYPE | self::FLAG_HAS_ICON],
        self::ACHIEVEMENT => [__NAMESPACE__ . '\AchievementList', 'achievement', 'g_achievements',       self::FLAG_RANDOM_SEARCHABLE | self::FLAG_FILTRABLE | self::FLAG_DB_TYPE | self::FLAG_HAS_ICON],
        self::TITLE       => [__NAMESPACE__ . '\TitleList',       'title',       'g_titles',             self::FLAG_RANDOM_SEARCHABLE | self::FLAG_DB_TYPE],
        self::WORLDEVENT  => [__NAMESPACE__ . '\WorldEventList',  'event',       'g_holidays',           self::FLAG_RANDOM_SEARCHABLE | self::FLAG_DB_TYPE | self::FLAG_HAS_ICON],
        self::CHR_CLASS   => [__NAMESPACE__ . '\CharClassList',   'class',       'g_classes',            self::FLAG_RANDOM_SEARCHABLE | self::FLAG_DB_TYPE],
        self::CHR_RACE    => [__NAMESPACE__ . '\CharRaceList',    'race',        'g_races',              self::FLAG_RANDOM_SEARCHABLE | self::FLAG_DB_TYPE],
        self::SKILL       => [__NAMESPACE__ . '\SkillList',       'skill',       'g_skills',             self::FLAG_RANDOM_SEARCHABLE | self::FLAG_DB_TYPE | self::FLAG_HAS_ICON],
        self::STATISTIC   => [__NAMESPACE__ . '\AchievementList', 'achievement', 'g_achievements',       self::FLAG_NONE], // alias for achievements; exists only for Markup
        self::CURRENCY    => [__NAMESPACE__ . '\CurrencyList',    'currency',    'g_gatheredcurrencies', self::FLAG_RANDOM_SEARCHABLE | self::FLAG_DB_TYPE | self::FLAG_HAS_ICON],
        self::SOUND       => [__NAMESPACE__ . '\SoundList',       'sound',       'g_sounds',             self::FLAG_RANDOM_SEARCHABLE | self::FLAG_FILTRABLE | self::FLAG_DB_TYPE],
        self::ICON        => [__NAMESPACE__ . '\IconList',        'icon',        'g_icons',              self::FLAG_RANDOM_SEARCHABLE | self::FLAG_FILTRABLE | self::FLAG_DB_TYPE | self::FLAG_HAS_ICON],
        self::GUIDE       => [__NAMESPACE__ . '\GuideList',       'guide',       '',                     self::FLAG_NONE],
        self::PROFILE     => [__NAMESPACE__ . '\ProfileList',     'profile',     '',                     self::FLAG_FILTRABLE], // x - not known in javascript
        self::GUILD       => [__NAMESPACE__ . '\GuildList',       'guild',       '',                     self::FLAG_FILTRABLE], // x
        self::ARENA_TEAM  => [__NAMESPACE__ . '\ArenaTeamList',   'arena-team',  '',                     self::FLAG_FILTRABLE], // x
        self::USER        => [__NAMESPACE__ . '\UserList',        'user',        'g_users',              self::FLAG_NONE],      // x
        self::EMOTE       => [__NAMESPACE__ . '\EmoteList',       'emote',       'g_emotes',             self::FLAG_RANDOM_SEARCHABLE | self::FLAG_DB_TYPE],
        self::ENCHANTMENT => [__NAMESPACE__ . '\EnchantmentList', 'enchantment', 'g_enchantments',       self::FLAG_RANDOM_SEARCHABLE | self::FLAG_FILTRABLE | self::FLAG_DB_TYPE],
        self::AREATRIGGER => [__NAMESPACE__ . '\AreatriggerList', 'areatrigger', '',                     self::FLAG_FILTRABLE | self::FLAG_DB_TYPE],
        self::MAIL        => [__NAMESPACE__ . '\MailList',        'mail',        '',                     self::FLAG_RANDOM_SEARCHABLE | self::FLAG_DB_TYPE]
    );


    /********************/
    /* Field Operations */
    /********************/

    public static function newList(int $type, array $conditions = []) : ?BaseType
    {
        if (!self::exists($type))
            return null;

        return new (self::$data[$type][self::IDX_LIST_OBJ])($conditions);
    }

    public static function newFilter(string $fileStr, array|string $data, array $opts = []) : ?Filter
    {
        $x = self::getFileStringsFor(self::FLAG_FILTRABLE);
        if ($type = array_search($fileStr, $x))
            return new (self::$data[$type][self::IDX_LIST_OBJ].'Filter')($data, $opts);

        return null;
    }

    public static function validateIds(int $type, int|array $ids) : array
    {
        if (!self::exists($type))
            return [];

        if (!(self::$data[$type][self::IDX_FLAGS] & self::FLAG_DB_TYPE))
            return [];

        return DB::Aowow()->selectCol('SELECT `id` FROM ?# WHERE `id` IN (?a)', self::$data[$type][self::IDX_LIST_OBJ]::$dataTable, (array)$ids);
    }

    public static function hasIcon(int $type) : bool
    {
        return self::exists($type) && self::$data[$type][self::IDX_FLAGS] & self::FLAG_HAS_ICON;
    }

    public static function isRandomSearchable(int $type) : bool
    {
        return self::exists($type) && self::$data[$type][self::IDX_FLAGS] & self::FLAG_RANDOM_SEARCHABLE;
    }

    public static function getFileString(int $type) : string
    {
        if (!self::exists($type))
            return '';

        return self::$data[$type][self::IDX_FILE_STR];
    }

    public static function getJSGlobalString(int $type) : string
    {
        if (!self::exists($type))
            return '';

        return self::$data[$type][self::IDX_JSG_TPL];
    }

    public static function getJSGlobalTemplate(int $type) : array
    {
        if (!self::exists($type) || !self::$data[$type][self::IDX_JSG_TPL])
            return [];

        // [key, [data], [extraData]]
        return [self::$data[$type][self::IDX_JSG_TPL], [], []];
    }

    public static function checkClassAttrib(int $type, string $attr, ?int $attrVal = null) : bool
    {
        if (!self::exists($type))
            return false;

        return isset((self::$data[$type][self::IDX_LIST_OBJ])::$$attr) && ($attrVal === null || ((self::$data[$type][self::IDX_LIST_OBJ])::$$attr & $attrVal));
    }

    public static function getClassAttrib(int $type, string $attr) : mixed
    {
        if (!self::exists($type))
            return null;

        return (self::$data[$type][self::IDX_LIST_OBJ])::$$attr ?? null;
    }

    public static function exists(int $type) : ?int
    {
        return !empty(self::$data[$type]) ? $type : null;
    }

    public static function getIndexFrom(int $idx, string $match) : int
    {
        $i = array_search($match, array_column(self::$data, $idx));
        if ($i === false)
            return 0;

        return array_keys(self::$data)[$i];
    }


    /*********************/
    /* Column Operations */
    /*********************/

    public static function getClassesFor(int $flags = 0x0, string $attr = '', ?int $attrVal = null) : array
    {
        $x = [];
        foreach (self::$data as $k => [$o, , , $f])
            if ($o && (!$flags || $flags & $f))
                if (!$attr || self::checkClassAttrib($k, $attr, $attrVal))
                    $x[$k] = $o;

        return $x;
    }

    public static function getFileStringsFor(int $flags = 0x0) : array
    {
        $x = [];
        foreach (self::$data as $k => [, $s, , $f])
            if ($s && (!$flags || $flags & $f))
                $x[$k] = $s;

        return $x;
    }

    public static function getJSGTemplatesFor(int $flags = 0x0) : array
    {
        $x = [];
        foreach (self::$data as $k => [, , $a, $f])
            if ($a && (!$flags || $flags & $f))
                $x[$k] = $a;

        return $x;
    }
}

?>
