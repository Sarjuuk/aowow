<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class Game
{
    public static $resistanceFields         = array(
        null,           'resHoly',      'resFire',      'resNature',    'resFrost',     'resShadow',    'resArcane'
    );

    public static $rarityColorStings        = array(        // zero-indexed
        '9d9d9d',       'ffffff',       '1eff00',       '0070dd',       'a335ee',       'ff8000',       'e5cc80',       'e6cc80'
    );

    private static $combatRatingToItemMod    = array(        // zero-indexed idx:CR; val:Mod
        null,           12,             13,             14,             15,             16,             17,             18,             19,
        20,             21,             22,             23,             24,             25,             26,             27,             28,
        29,             30,             null,           null,           null,           37,             44
    );

    public static $lvlIndepRating           = array(        // rating doesn't scale with level
        ITEM_MOD_MANA,                  ITEM_MOD_HEALTH,                ITEM_MOD_ATTACK_POWER,          ITEM_MOD_MANA_REGENERATION,     ITEM_MOD_SPELL_POWER,
        ITEM_MOD_HEALTH_REGEN,          ITEM_MOD_SPELL_PENETRATION,     ITEM_MOD_BLOCK_VALUE
    );

    public static $questClasses             = array(
        -2 =>  [    0],
         0 =>  [    1,     3,     4,     8,     9,    10,    11,    12,    25,    28,    33,    36,    38,    40,    41,    44,    45,    46,    47,    51,    85,   130,   132,   139,   154,   267,  1497,  1519,  1537,  2257,  3430,  3431,  3433,  3487,  4080,  4298],
         1 =>  [   14,    15,    16,    17,   141,   148,   188,   215,   220,   331,   357,   361,   363,   400,   405,   406,   440,   490,   493,   618,  1377,  1637,  1638,  1657,  1769,  3524,  3525,  3526,  3557],
         2 =>  [  206,   209,   491,   717,   718,   719,   721,   722,   796,  1176,  1196,  1337,  1417,  1581,  1583,  1584,  1941,  2017,  2057,  2100,  2366,  2367,  2437,  2557,  3535,  3562,  3688,  3713,  3714,  3715,  3716,  3717,  3789,  3790,  3791,  3792,  3842,  3847,  3848,  3849,  3905,  4100,  4131,  4196,  4228,  4264,  4265,  4272,  4277,  4415,  4416,  4494,  4522,  4723,  4809,  4813,  4820],
         3 =>  [ 1977,  2159,  2677,  2717,  3428,  3429,  3456,  3457,  3606,  3607,  3805,  3836,  3845,  3923,  3959,  4075,  4273,  4493,  4500,  4603,  4722,  4812,  4987],
         4 =>  [ -372,  -263,  -262,  -261,  -162,  -161,  -141,   -82,   -81,   -61],
         5 =>  [ -373,  -371,  -324,  -304,  -264,  -201,  -182,  -181,  -121,  -101,   -24],
         6 =>  [  -25,  2597,  3277,  3358,  3820,  4384,  4710],
         7 =>  [-1010,  -368,  -367,  -365,  -344,  -241,    -1],
         8 =>  [ 3483,  3518,  3519,  3520,  3521,  3522,  3523,  3679,  3703],
         9 =>  [-1005, -1003, -1002, -1001,  -376,  -375,  -374,  -370,  -369,  -366,  -364,   -41,   -22],  // 22: seasonal
        10 =>  [   65,    66,    67,   210,   394,   495,  2817,  3537,  3711,  4024,  4197,  4395,  4742]
    );

    /*  why:
        Because petSkills (and ranged weapon skills) are the only ones with more than two skillLines attached. Because Left Joining ?_spell with ?_skillLineability  causes more trouble than it has uses.
        Because this is more or less the only reaonable way to fit all that information into one database field, so..
        .. the indizes of this array are bits of skillLine2OrMask in ?_spell if skillLineId1 is negative
    */
    public static $skillLineMask            = array(        // idx => [familyId, skillLineId]
        -1 => array(                                        // Pets (Hunter)
            [ 1, 208],          [ 2, 209],          [ 3, 203],          [ 4, 210],          [ 5, 211],          [ 6, 212],          [ 7, 213],  // Wolf,       Cat,          Spider,       Bear,        Boar,      Crocolisk,    Carrion Bird
            [ 8, 214],          [ 9, 215],          [11, 217],          [12, 218],          [20, 236],          [21, 251],          [24, 653],  // Crab,       Gorilla,      Raptor,       Tallstrider, Scorpid,   Turtle,       Bat
            [25, 654],          [26, 655],          [27, 656],          [30, 763],          [31, 767],          [32, 766],          [33, 765],  // Hyena,      Bird of Prey, Wind Serpent, Dragonhawk,  Ravager,   Warp Stalker, Sporebat
            [34, 764],          [35, 768],          [37, 775],          [38, 780],          [39, 781],          [41, 783],          [42, 784],  // Nether Ray, Serpent,      Moth,         Chimaera,    Devilsaur, Silithid,     Worm
            [43, 786],          [44, 785],          [45, 787],          [46, 788]                                                               // Rhino,      Wasp,         Core Hound,   Spirit Beast
        ),
        -2 => array(                                        // Pets (Warlock)
            [15, 189],          [16, 204],          [17, 205],          [19, 207],          [23, 188],          [29, 761]                       // Felhunter,  Voidwalker,   Succubus,     Doomguard,   Imp,       Felguard
        ),
        -3 => array(                                        // Ranged Weapons
            [null, 45],         [null, 46],         [null, 226]                                                                                 // Bow,         Gun,         Crossbow
        )
    );

    public static $sockets                  = array(        // jsStyle Strings
        'meta',                         'red',                          'yellow',                       'blue'
    );

    // 'replicates' $WH.g_statToJson
    public static $itemMods                 = array(        // zero-indexed; "mastrtng": unused mastery; _[a-z] => taken mods..
        'dmg',              'mana',             'health',           'agi',              'str',              'int',              'spi',
        'sta',              'energy',           'rage',             'focus',            'runicpwr',         'defrtng',          'dodgertng',
        'parryrtng',        'blockrtng',        'mlehitrtng',       'rgdhitrtng',       'splhitrtng',       'mlecritstrkrtng',  'rgdcritstrkrtng',
        'splcritstrkrtng',  '_mlehitrtng',      '_rgdhitrtng',      '_splhitrtng',      '_mlecritstrkrtng', '_rgdcritstrkrtng', '_splcritstrkrtng',
        'mlehastertng',     'rgdhastertng',     'splhastertng',     'hitrtng',          'critstrkrtng',     '_hitrtng',         '_critstrkrtng',
        'resirtng',         'hastertng',        'exprtng',          'atkpwr',           'rgdatkpwr',        'feratkpwr',        'splheal',
        'spldmg',           'manargn',          'armorpenrtng',     'splpwr',           'healthrgn',        'splpen',           'block',                                          // ITEM_MOD_BLOCK_VALUE
        'mastrtng',         'armor',            'firres',           'frores',           'holres',           'shares',           'natres',
        'arcres',           'firsplpwr',        'frosplpwr',        'holsplpwr',        'shasplpwr',        'natsplpwr',        'arcsplpwr'
    );

    public static $class2SpellFamily        = array(
    //  null    Warrior Paladin Hunter  Rogue   Priest  DK      Shaman  Mage    Warlock null    Druid
        null,   4,      10,     9,      8,      6,      15,     11,     3,      5,      null,   7
    );

    public static $areaFloors               = array(
         206 => 3,  209 => 7,  719 => 3,  721 => 4,  796 => 4, 1196 => 2, 1337 => 2,  1581 => 2, 1583 => 7, 1584 => 2,
        2017 => 2, 2057 => 4, 2100 => 2, 2557 => 6, 2677 => 4, 3428 => 3, 3457 => 17, 3790 => 2, 3791 => 2, 3959 => 8,
        3456 => 6, 3715 => 2, 3848 => 3, 3849 => 2, 4075 => 2, 4100 => 2, 4131 => 2,  4196 => 2, 4228 => 4, 4272 => 2,
        4273 => 6, 4277 => 3, 4395 => 2, 4494 => 2, 4722 => 2, 4812 => 8
    );

    public static function itemModByRatingMask($mask)
    {
        if (($mask & 0x1C000) == 0x1C000)                   // special case resilience
            return ITEM_MOD_RESILIENCE_RATING;

        if (($mask & 0x00E0) == 0x00E0)                     // hit rating - all subcats (mle, rgd, spl)
            return ITEM_MOD_HIT_RATING;

        if (($mask & 0x0700) == 0x0700)                     // crit rating - all subcats (mle, rgd, spl)
            return ITEM_MOD_CRIT_RATING;

        for ($j = 0; $j < count(self::$combatRatingToItemMod); $j++)
        {
            if (!self::$combatRatingToItemMod[$j])
                continue;

            if (!($mask & (1 << $j)))
                continue;

            return self::$combatRatingToItemMod[$j];
        }

        return 0;
    }

    public static function sideByRaceMask($race)
    {
        // Any
        if (!$race || ($race & RACE_MASK_ALL) == RACE_MASK_ALL)
            return SIDE_BOTH;

        // Horde
        if ($race & RACE_MASK_HORDE && !($race & RACE_MASK_ALLIANCE))
            return SIDE_HORDE;

        // Alliance
        if ($race & RACE_MASK_ALLIANCE && !($race & RACE_MASK_HORDE))
            return SIDE_ALLIANCE;

        return SIDE_BOTH;
    }

    public static function getReputationLevelForPoints($pts)
    {
        if ($pts >= 41999)
            return REP_EXALTED;
        else if ($pts >= 20999)
            return REP_REVERED;
        else if ($pts >= 8999)
            return REP_HONORED;
        else if ($pts >= 2999)
            return REP_FRIENDLY;
        else if ($pts >= 0)
            return REP_NEUTRAL;
        else if ($pts >= -3000)
            return REP_UNFRIENDLY;
        else if ($pts >= -6000)
            return REP_HOSTILE;
        else
            return REP_HATED;
    }

    public static function getTaughtSpells(&$spell)
    {
        $extraIds = [-1];                                    // init with -1 to prevent empty-array errors
        $lookup   = [-1];
        switch (gettype($spell))
        {
            case 'object':
                if (get_class($spell) != 'SpellList')
                    return [];

                $lookup[] = $spell->id;
                foreach ($spell->canTeachSpell() as $idx)
                    $extraIds[] = $spell->getField('effect'.$idx.'TriggerSpell');

                break;
            case 'integer':
                $lookup[] = $spell;
                break;
            case 'array':
                $lookup = $spell;
                break;
            default:
                return [];
        }

        // note: omits required spell and chance in skill_discovery_template
        $data = array_merge(
            DB::World()->selectCol('SELECT spellId FROM spell_learn_spell WHERE entry IN (?a)', $lookup),
            DB::World()->selectCol('SELECT spellId FROM skill_discovery_template WHERE reqSpell IN (?a)', $lookup),
            $extraIds
        );

        // return list of integers, not strings
        $data = array_map('intVal', $data);

        return $data;
    }

    public static function getPageText($ptId)
    {
        $pages = [];
        while ($ptId)
        {
            if ($row = DB::World()->selectRow('SELECT ptl.Text AS Text_loc?d, pt.* FROM page_text pt LEFT JOIN page_text_locale ptl ON pt.ID = ptl.ID AND locale = ? WHERE pt.ID = ?d', User::$localeId, User::$localeString, $ptId))
            {
                $ptId = $row['NextPageID'];
                $pages[] = Util::parseHtmlText(Util::localizedString($row, 'Text'));
            }
            else
            {
                trigger_error('Referenced PageTextId #'.$ptId.' is not in DB', E_USER_WARNING);
                break;
            }
        }

        return $pages;
    }

    public static function getWorldPosForGUID(int $type, int ...$guids) : array
    {
        $result = [];

        switch ($type)
        {
            case TYPE_NPC:
                $result = DB::World()->select('SELECT `guid` AS ARRAY_KEY, `id`, `map` AS `mapId`, `position_y` AS `posX`, `position_x` AS `posY` FROM creature WHERE `guid` IN (?a)', $guids);
                break;
            case TYPE_OBJECT:
                $result = DB::World()->select('SELECT `guid` AS ARRAY_KEY, `id`, `map` AS `mapId`, `position_y` AS `posX`, `position_x` AS `posY` FROM gameobject WHERE `guid` IN (?a)', $guids);
                break;
            case TYPE_SOUND:
                $result = DB::AoWoW()->select('SELECT `soundId` AS ARRAY_KEY, `soundId` AS `id`, `mapId`, `posX`, `posY` FROM dbc_soundemitters WHERE `soundId` IN (?a)', $guids);
                break;
            case TYPE_AREATRIGGER:
                $result = DB::AoWoW()->select('SELECT `id` AS ARRAY_KEY, `id`, `mapId`, `posX`, `posY` FROM dbc_areatrigger WHERE `id` IN (?a)', $guids);
                break;
            default:
                trigger_error('Game::getWorldPosForGUID - instanced with unsupported TYPE '.$type, E_USER_WARNING);
        }

        return $result;
    }

    public static function worldPosToZonePos(int $mapId, float $posX, float $posY, int $areaId = 0, int $floor = -1) : array
    {
        if (!$mapId < 0)
            return [];

        $query = 'SELECT
                    dm.id,
                    wma.areaId,
                    IFNULL(dm.floor, 0) AS floor,
                    100 - ROUND(IF(dm.id IS NOT NULL, (?f - dm.minY) * 100 / (dm.maxY - dm.minY), (?f - wma.right)  * 100 / (wma.left - wma.right)), 1) AS `posX`,
                    100 - ROUND(IF(dm.id IS NOT NULL, (?f - dm.minX) * 100 / (dm.maxX - dm.minX), (?f - wma.bottom) * 100 / (wma.top - wma.bottom)), 1) AS `posY`,
                    SQRT(POWER(abs(IF(dm.id IS NOT NULL, (?f - dm.minY) * 100 / (dm.maxY - dm.minY), (?f - wma.right)  * 100 / (wma.left - wma.right)) - 50), 2) +
                         POWER(abs(IF(dm.id IS NOT NULL, (?f - dm.minX) * 100 / (dm.maxX - dm.minX), (?f - wma.bottom) * 100 / (wma.top - wma.bottom)) - 50), 2)) AS `dist`
                FROM
                    dbc_worldmaparea wma
                LEFT JOIN
                    dbc_dungeonmap dm ON dm.mapId = IF(?d AND (wma.mapId NOT IN (0, 1, 530, 571) OR wma.areaId = 4395), wma.mapId, -1)
                WHERE
                    wma.mapId = ?d AND IF(?d, wma.areaId = ?d, wma.areaId <> 0){ AND IF(dm.floor IS NULL, 1, dm.floor = ?d)}
                HAVING
                    (`posX` BETWEEN 0.1 AND 99.9 AND `posY` BETWEEN 0.1 AND 99.9)
                ORDER BY
                    `dist` ASC';

        // dist BETWEEN 0 (center) AND 70.7 (corner)
        $points = DB::Aowow()->select($query, $posX, $posX, $posY, $posY, $posX, $posX, $posY, $posY, 1, $mapId, $areaId, $areaId, $floor < 0 ? DBSIMPLE_SKIP : $floor);
        if (!$points)                                       // retry: TC counts pre-instance subareas as instance-maps .. which have no map file
            $points = DB::Aowow()->select($query, $posX, $posX, $posY, $posY, $posX, $posX, $posY, $posY, 0, $mapId, 0, 0, DBSIMPLE_SKIP);

        if (!is_array($points))
        {
            trigger_error('Game::worldPosToZonePos - dbc query failed', E_USER_ERROR);
            return [];
        }

        return $points;
    }

    public static function getQuotesForCreature(int $creatureId, bool $asHTML = false, string $talkSource = '') : array
    {
        $nQuotes  = 0;
        $quotes   = [];
        $soundIds = [];

        $quoteSrc = DB::World()->select('
            SELECT
                ct.GroupID AS ARRAY_KEY, ct.ID as ARRAY_KEY2,
                ct.`Type` AS `talkType`,
                ct.TextRange AS `range`,
                IFNULL(bct.`LanguageID`, ct.`Language`) AS lang,
                IFNULL(NULLIF(bct.Text, ""), IFNULL(NULLIF(bct.Text1, ""), IFNULL(ct.`Text`, ""))) AS text_loc0,
               {IFNULL(NULLIF(bctl.Text, ""), IFNULL(NULLIF(bctl.Text1, ""), IFNULL(ctl.Text, ""))) AS text_loc?d,}
                IF(bct.SoundEntriesID > 0, bct.SoundEntriesID, ct.Sound) AS soundId
            FROM
                creature_text ct
           {LEFT JOIN
                creature_text_locale ctl ON ct.CreatureID = ctl.CreatureID AND ct.GroupID = ctl.GroupID AND ct.ID = ctl.ID AND ctl.Locale = ?}
            LEFT JOIN
                broadcast_text bct ON ct.BroadcastTextId = bct.ID
           {LEFT JOIN
                broadcast_text_locale bctl ON ct.BroadcastTextId = bctl.ID AND bctl.locale = ?}
            WHERE
                ct.CreatureID = ?d',
            User::$localeId ?: DBSIMPLE_SKIP,
            User::$localeId ? Util::$localeStrings[User::$localeId] : DBSIMPLE_SKIP,
            User::$localeId ? Util::$localeStrings[User::$localeId] : DBSIMPLE_SKIP,
            $creatureId
        );

        foreach ($quoteSrc as $grp => $text)
        {
            $group = [];
            foreach ($text as $t)
            {
                if ($t['soundId'])
                    $soundIds[] = $t['soundId'];

                $msg = Util::localizedString($t, 'text');
                if (!$msg)
                    continue;

                // fixup .. either set %s for emotes or dont >.<
                if (in_array($t['talkType'], [2, 16]) && strpos($msg, '%s') === false)
                    $msg = '%s '.$msg;

                // fixup: bad case-insensivity
                $msg = Util::parseHtmlText(str_replace('%S', '%s', htmlentities($msg)), !$asHTML);

                if ($talkSource)
                    $msg = sprintf($msg, $talkSource);

                // make type css compatible
                switch ($t['talkType'])
                {
                    case  1:                                // yell:
                    case 14: $t['talkType'] = 1; break;     // - dark red
                    case  2:                                // emote:
                    case 16:                                // "
                    case  3:                                // boss emote:
                    case 41: $t['talkType'] = 4; break;     // - orange
                    case  4:                                // whisper:
                    case 15:                                // "
                    case  5:                                // boss whisper:
                    case 42: $t['talkType'] = 3; break;     // - pink-ish
                    default: $t['talkType'] = 2;            // [type: 0, 12] say: yellow-ish

                }

                // prefix
                $pre = '';
                if ($t['talkType'] != 4)
                    $pre = ($talkSource ?: '%s').' '.Lang::npc('textTypes', $t['talkType']).Lang::main('colon').($t['lang'] ? '['.Lang::game('languages', $t['lang']).'] ' : null);

                if ($asHTML)
                    $msg = '<div><span class="s'.$t['talkType'].'">%s'.($t['range'] ? sprintf(Util::$dfnString, Lang::npc('textRanges', $t['range']), $msg) : $msg).'</span></div>';
                else
                    $msg = '[div][span class=s'.$t['talkType'].']%s'.html_entity_decode($msg).'[/span][/div]';

                $line = array(
                    'range'  => $t['range'],
                    'text'   => $msg,
                    'prefix' => $pre
                );


                $nQuotes++;
                $group[] = $line;
            }

            if ($group)
                $quotes[$grp] = $group;
        }

        return [$quotes, $nQuotes, $soundIds];
    }

    public static function getBreakpointsForSkill(int $skillId, int $reqLevel) : array
    {
        switch ($skillId)
        {
            case SKILL_HERBALISM:
            case SKILL_LOCKPICKING:
            case SKILL_JEWELCRAFTING:
            case SKILL_INSCRIPTION:
            case SKILL_SKINNING:
            case SKILL_MINING:
                $points = [$reqLevel];                              // red/orange

                if ($reqLevel + 25 <= MAX_SKILL)                    // orange/yellow
                    $points[] = $reqLevel + 25;

                if ($reqLevel + 50 <= MAX_SKILL)                    // yellow/green
                    $points[] = $reqLevel + 50;

                if ($reqLevel + 100 <= MAX_SKILL)                   // green/grey
                    $points[] = $reqLevel + 100;

                return $points;
            default:
                return [$reqLevel];
        }
    }
}

?>
