<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');

abstract class BaseType
{
    public    $names      = [];
    public    $Id         = 0;
    public    $matches    = 0;                              // total matches unaffected by sqlLimit in config
    public    $error      = true;

    protected $templates  = [];
    protected $curTpl     = [];                             // lets iterate!
    protected $filter     = null;

    protected $setupQuery = '';
    protected $matchQuery = '';

    /*
    *   condition as array [field, value, operator]
    *       field:    must match fieldname; 1: select everything
    *       value:    str   - operator defaults to: LIKE %<val>%
    *                 int   - operator defaults to: = <val>
    *                 array - operator defaults to: IN (<val>)
    *       operator: modifies/overrides default
    *                 ! - negated default value (NOT LIKE; <>; NOT IN)
    *   condition as str
    *       defines linking (AND || OR)
    *   condition as int
    *       defines LIMIT
    *
    *   example:
    *       array(['id', 45], ['name', 'test', '!'], 'OR', 5)
    *   results in
    *       WHERE id = 45 OR name NOT LIKE %test% LIMIT 5;
    */
    public function __construct($conditions = [], $applyFilter = false)
    {
        global    $AoWoWconf;                                   // yes i hate myself..

        $sql       = [];
        $linking   = ' AND ';
        $limit     = ' LIMIT '.$AoWoWconf['sqlLimit'];
        $className = get_class($this);

        if (!$this->setupQuery || !$this->matchQuery)
            return;

        // may be called without filtering
        if ($applyFilter && class_exists($className.'Filter'))
        {
            $fiName = $className.'Filter';
            $this->filter = new $fiName();
            if ($this->filter->init() === false)
                return;
        }

        foreach ($conditions as $c)
        {
            $field = '';
            $op    = '';
            $val   = '';

            if (is_array($c))
            {
                if ($c[0] == '1')
                {
                    $sql[] = '1';
                    continue;
                }
                else if ($c[0])
                {
                    $field = '`'.implode('`.`', explode('.', Util::sqlEscape($c[0]))).'`';
                }
                else
                    continue;

                if (is_array($c[1]))
                {
                    $val = implode(',', Util::sqlEscape($c[1]));
                    if (!$val)
                        continue;

                    $op  = (isset($c[2]) && $c[2] == '!') ? 'NOT IN' : 'IN';
                    $val = '('.$val.')';
                }
                else if (is_string($c[1]))
                {
                    $val = Util::sqlEscape($c[1]);
                    if (!$val)
                        continue;

                    $op  = (isset($c[2]) && $c[2] == '!') ? 'NOT LIKE' : 'LIKE';
                    $val = '"%'.$val.'%"';
                }
                else if (is_int($c[1]))
                {
                    $op  = (isset($c[2]) && $c[2] == '!') ? '<>' : '=';
                    $val = Util::sqlEscape($c[1]);
                }
                else                                        // null for example
                    continue;

                if (isset($c[2]) && $c[2] != '!')
                    $op = $c[2];

                $sql[] = $field.' '.$op.' '.$val;
            }
            else if (is_string($c))
                $linking = $c == 'AND' ? ' AND ' : ' OR ';
            else if (is_int($c))
                $limit   = $c > 0      ? ' LIMIT '.$c : '';
            else
                continue;                                   // ignore other possibilities
        }

        // todo: add strings propperly without them being escaped by simpleDB..?
        $this->setupQuery  = str_replace('[filter]', $this->filter && $this->filter->buildQuery() ? $this->filter->getQuery().' AND ' : NULL, $this->setupQuery);
        $this->setupQuery  = str_replace('[cond]',   empty($sql) ? '1' : '('.implode($linking, $sql).')',                                     $this->setupQuery);
        $this->setupQuery .= $limit;

        $this->matchQuery  = str_replace('[filter]', $this->filter && $this->filter->buildQuery() ? $this->filter->getQuery().' AND ' : NULL, $this->matchQuery);
        $this->matchQuery  = str_replace('[cond]',   empty($sql) ? '1' : '('.implode($linking, $sql).')',                                     $this->matchQuery);

        $rows = DB::Aowow()->Select($this->setupQuery);
        if (!$rows)
            return;

        $this->matches = DB::Aowow()->SelectCell($this->matchQuery);

        foreach ($rows as $k => $tpl)
        {
            $this->names[$k]     = Util::localizedString($tpl, Util::getNameFieldName($tpl));
            $this->templates[$k] = $tpl;
        }

        $this->reset();

        $this->error = false;
    }

    public function iterate($qty = 1)
    {
        if (!$this->curTpl)                                 // exceeded end of line .. array .. in last iteration
            reset($this->templates);

        $this->curTpl = current($this->templates);
        $field        = $this->curTpl ? Util::getIdFieldName($this->curTpl) : null;
        $this->id     = $this->curTpl ? $this->curTpl[$field] : 0;

        while ($qty--)
            next($this->templates);

        return $this->id;
    }

    public function reset()
    {
        $this->curTpl = reset($this->templates);
        $this->id     = $this->curTpl[Util::getIdFieldName($this->curTpl)];
    }

    // read-access to templates
    public function getField($field)
    {
        if (!$this->curTpl || !isset($this->curTpl[$field]))
            return null;

        return $this->curTpl[$field];
    }

    public function filterGetSetCriteria()
    {
        if ($this->filter)
            return $this->filter->getSetCriteria();
        else
            return null;
    }

    public function filterGetForm()
    {
        if ($this->filter)
            return $this->filter->getForm();
        else
            return [];
    }

    public function filterGetError()
    {
        if ($this->filter)
            return $this->filter->error;
        else
            return false;
    }

    // should return data required to display a listview of any kind
    // this is a rudimentary example, that will not suffice for most Types
    abstract public function getListviewData();

    // should return data to extend global js variables for a certain type (e.g. g_items)
    abstract public function addGlobalsToJScript(&$ref);

    // should return data to extend global js variables for the rewards provided by this type (e.g. g_titles)
    // rewards will not always be required and only by Achievement and Quest .. but yeah.. maybe it should be merged with addGlobalsToJScript
    abstract public function addRewardsToJScript(&$ref);

    // NPC, GO, Item, Quest, Spell, Achievement, Profile would require this
    abstract public function renderTooltip();
}

trait listviewHelper
{
    public function hasDiffCategories()
    {
        $this->reset();
        $curCat = $this->getField('category');
        if ($curCat === null)
            return false;

        while ($this->iterate())
            if ($curCat != $this->getField('category'))
                return true;

        return false;
    }

    public function hasAnySource()
    {
        if (!isset($this->sources))
            return false;

        foreach ($this->sources as $src)
        {
            if (!is_array($src))
                continue;

            if (!empty($src))
                return true;
        }

        return false;
    }

}

class Lang
{
    public static $main;
    public static $search;
    public static $game;
    public static $error;

    public static $account;
    public static $achievement;
    public static $compare;
    public static $currency;
    public static $event;
    public static $item;
    public static $itemset;
    public static $pet;
    public static $maps;
    public static $spell;
    public static $talent;
    public static $title;
    public static $zone;

    public static function load($loc)
    {
        if (@(require 'localization/locale_'.$loc.'.php') !== 1)
            die('File for localization '.$loc.' not found.');

        foreach ($lang as $k => $v)
            self::$$k = $v;
    }

    // todo: expand
    public static function getInfoBoxForFlags($flags)
    {
        $tmp = [];

        if ($flags & CUSTOM_DISABLED)
            $tmp[] = '<span class="tip" onmouseover="Tooltip.showAtCursor(event, \''.self::$main['disabledHint'].'\', 0, 0, \'q\')" onmousemove="Tooltip.cursorUpdate(event)" onmouseout="Tooltip.hide()">'.self::$main['disabled'].'</span>';

        if ($flags & CUSTOM_SERVERSIDE)
            $tmp[] = '<span class="tip" onmouseover="Tooltip.showAtCursor(event, \''.self::$main['serversideHint'].'\', 0, 0, \'q\')" onmousemove="Tooltip.cursorUpdate(event)" onmouseout="Tooltip.hide()">'.self::$main['serverside'].'</span>';

        return $tmp;
    }

    public static function getReputationLevelForPoints($pts)
    {
        if ($pts >= 41999)
            return self::$game['rep'][REP_EXALTED];
        else if ($pts >= 20999)
            return self::$game['rep'][REP_REVERED];
        else if ($pts >= 8999)
            return self::$game['rep'][REP_HONORED];
        else if ($pts >= 2999)
            return self::$game['rep'][REP_FRIENDLY];
        else /* if ($pts >= 1) */
            return self::$game['rep'][REP_NEUTRAL];
    }

    public static function getStances($stanceMask)
    {
        $stanceMask &= 0x1F84F213E;                         // clamp to available stances/forms..

        $tmp = [];
        $i   = 1;

        while ($stanceMask)
        {
            if ($stanceMask & (1 << ($i - 1)))
            {
                $tmp[] = self::$game['st'][$i];
                $stanceMask &= ~(1 << ($i - 1));
            }
            $i++;
        }

        return implode(', ', $tmp);
    }

    public static function getMagicSchools($schoolMask)
    {
        $schoolMask &= SPELL_ALL_SCHOOLS;                   // clamp to available schools..

        $tmp = [];
        $i   = 1;

        while ($schoolMask)
        {
            if ($schoolMask & (1 << ($i - 1)))
            {
                $tmp[] = self::$game['sc'][$i];
                $schoolMask &= ~(1 << ($i - 1));
            }
            $i++;
        }

        return implode(', ', $tmp);
    }

    public static function getClassString($classMask)
    {
        $classMask &= CLASS_MASK_ALL;                       // clamp to available classes..

        if ($classMask == CLASS_MASK_ALL)                   // available to all classes
            return false;

        if (!$classMask)                                    // no restrictions left
            return false;

        $tmp = [];
        $i   = 1;

        while ($classMask)
        {
            if ($classMask & (1 << ($i - 1)))
            {
                $tmp[] = '<a href="?class='.$i.'" class="c'.$i.'">'.self::$game['cl'][$i].'</a>';
                $classMask &= ~(1 << ($i - 1));
            }
            $i++;
        }

        return implode(', ', $tmp);
    }

    public static function getRaceString($raceMask)
    {
        $raceMask &= RACE_MASK_ALL;                         // clamp to available races..

        if ($raceMask == RACE_MASK_ALL)                     // available to all races (we don't display 'both factions')
            return false;

        if (!$raceMask)                                     // no restrictions left (we don't display 'both factions')
            return false;

        $tmp  = [];
        $side = 0;
        $i    = 1;

        if (!$raceMask)
            return array('side' => 3, 'name' => self::$game['ra'][0]);

        if ($raceMask == RACE_MASK_HORDE)
            return array('side' => 2, 'name' => self::$game['ra'][-2]);

        if ($raceMask == RACE_MASK_ALLIANCE)
            return array('side' => 1, 'name' => self::$game['ra'][-1]);

        if ($raceMask & RACE_MASK_HORDE)
            $side |= 2;

        if ($raceMask & RACE_MASK_ALLIANCE)
            $side |= 1;

        while ($raceMask)
        {
            if ($raceMask & (1 << ($i - 1)))
            {
                $tmp[] = '<a href="?race='.$i.'" class="q1">'.self::$game['ra'][$i].'</a>';
                $raceMask &= ~(1 << ($i - 1));
            }
            $i++;
        }

        return array ('side' => $side, 'name' => implode(', ', $tmp));
    }
}

class SmartyAoWoW extends Smarty
{
    private $config = [];

    public function __construct($config)
    {
        $cwd = str_replace("\\", "/", getcwd());

        $this->Smarty();
        $this->config           = $config;
        $this->template_dir     = $cwd.'/template/';
        $this->compile_dir      = $cwd.'/cache/template/';
        $this->config_dir       = $cwd.'/configs/';
        $this->cache_dir        = $cwd.'/cache/';
        $this->debugging        = $config['debug'];
        $this->left_delimiter   = '{';
        $this->right_delimiter  = '}';
        $this->caching          = false;                    // Total Cache, this site does not work
        $this->assign('appName', $config['page']['name']);
        $this->assign('AOWOW_REVISION', AOWOW_REVISION);
        $this->_tpl_vars['page'] = array(
            'reqJS'      => [],                             // <[string]> path to required JSFile
            'reqCSS'     => [],                             // <[string,string]> path to required CSSFile, IE condition
            'title'      => null,                           // [string] page title
            'tab'        => null,                           // [int] # of tab to highlight in the menu
            'type'       => null,                           // [int] numCode for spell, npx, object, ect
            'typeId'     => null,                           // [int] entry to display
            'path'       => '[]',                           // [string] (js:array) path to preselect in the menu
            'gStaticUrl' => substr('http://'.$_SERVER['SERVER_NAME'].strtr($_SERVER['SCRIPT_NAME'], ['index.php' => '']), 0, -1)
        );
    }

    // using Smarty::assign would overwrite every pair and result in undefined indizes
    public function updatePageVars($pageVars)
    {
        if (!is_array($pageVars))
            return;

        foreach ($pageVars as $var => $val)
            $this->_tpl_vars['page'][$var] = $val;
    }

    public function display($tpl)
    {
        // since it's the same for every page, except index..
        if ($this->_tpl_vars['query'][0] && !preg_match('/[^a-z]/i', $this->_tpl_vars['query'][0]))
        {
            $ann = DB::Aowow()->Select('SELECT * FROM ?_announcements WHERE flags & 0x10 AND (page = ?s OR page = "*")', $this->_tpl_vars['query'][0]);
            foreach ($ann as $k => $v)
                $ann[$k]['text'] = Util::localizedString($v, 'text');

            $this->_tpl_vars['announcements'] = $ann;
        }

        parent::display($tpl);
    }

    public function notFound($subject)
    {
        $this->updatePageVars(array(
            'subject'  => ucfirst($subject),
            'id'       => intVal($this->_tpl_vars['query'][1]),
            'notFound' => sprintf(Lang::$main['pageNotFound'], $subject),
        ));

        $this->assign('lang', Lang::$main);

        $this->display('404.tpl');
        exit();
    }

    public function error()
    {
        $this->assign('lang', array_merge(Lang::$main, Lang::$error));
        $this->assign('mysql', DB::Aowow()->getStatistics());

        $this->display('error.tpl');
        exit();
    }

    // creates the cache file
    public function saveCache($key, $data)
    {
        if ($this->debugging)
            return;

        $file = $this->cache_dir.'data/'.$key;

        $cache_data = time()." ".AOWOW_REVISION."\n";
        $cache_data .= serialize($data);

        file_put_contents($file, $cache_data);
    }

    // loads and evaluates the cache file
    public function loadCache($key, &$data)
    {
        if ($this->debugging)
            return false;

        $cache = @file_get_contents($this->cache_dir.'data/'.$key);
        if (!$cache)
            return false;

        $cache = explode("\n", $cache);

        @list($time, $rev) = explode(' ', $cache[0]);
        $expireTime = $time + $this->config['page']['cacheTimer'];
        if ($expireTime <= time() || $rev < AOWOW_REVISION)
            return false;

        $data = unserialize($cache[1]);

        return true;
    }
}

class Util
{
    public static $resistanceFields         = array(
        null,           'holy_res',     'fire_res',     'nature_res',   'frost_res',    'shadow_res',   'arcane_res'
    );

    private static $rarityColorStings       = array(        // zero-indexed
        '9d9d9d',       'ffffff',       '1eff00',       '0070dd',       'a335ee',       'ff8000',       'e5cc80',       'e6cc80'
    );

    public static $localeStrings            = array(        // zero-indexed
        'enus',         null,           'frfr',         'dede',         null,           null,           'eses',         null,           'ruru'
    );

    public static $typeStrings              = array(        // zero-indexed
        null,           'npc',          'object',       'item',         'itemset',      'quest',        'spell',        'zone',         'faction',
        'pet',          'achievement',  'title',        'event',        'class',        'race',         'skill',        null,           'currency'
    );

    public static $combatRatingToItemMod    = array(        // zero-indexed
        null,           12,             13,             14,             15,             16,             17,             18,             19,
        20,             21,             null,           null,           null,           null,           null,           null,           28,
        29,             30,             null,           null,           null,           37,             44
    );

    public static $gtCombatRatings          = array(
        12 => 1.5,      13 => 12,       14 => 15,       15 => 5,        16 => 10,       17 => 10,       18 => 8,        19 => 14,       20 => 14,
        21 => 14,       22 => 10,       23 => 10,       24 => 0,        25 => 0,        26 => 0,        27 => 0,        28 => 10,       29 => 10,
        30 => 10,       31 => 10,       32 => 14,       33 => 0,        34 => 0,        35 => 25,       36 => 10,       37 => 2.5,      44 => 3.756097412109376
    );

    public static $lvlIndepRating           = array(        // rating doesn't scale with level
        ITEM_MOD_MANA,                  ITEM_MOD_HEALTH,                ITEM_MOD_ATTACK_POWER,          ITEM_MOD_MANA_REGENERATION,     ITEM_MOD_SPELL_POWER,
        ITEM_MOD_HEALTH_REGEN,          ITEM_MOD_SPELL_PENETRATION,     ITEM_MOD_BLOCK_VALUE
    );

    public static $questClasses             = array(        // taken from old aowow: 0,1,2,3,8,10 may point to pointless mini-areas
        -2 =>  [    0],
         0 =>  [    1,     3,     4,     8,    10,    11,    12,    25,   28,   33,   36,   38,   40,   41,   44,   45,   46,   47,   51,   85,  130,  139,  267,  279, 1497, 1519, 1537, 2257, 3430, 3433, 3487, 4080],
         1 =>  [   14,    15,    16,    17,   141,   148,   215,   331,  357,  361,  400,  405,  406,  440,  490,  493,  618, 1216, 1377, 1637, 1638, 1657, 3524, 3525, 3557],
         2 =>  [  133,   206,   209,   491,   717,   718,   719,   722,  796,  978, 1196, 1337, 1417, 1581, 1583, 1584, 1941, 2017, 2057, 2100, 2366, 2367, 2437, 2557, 3477, 3562, 3713, 3714, 3715, 3716, 3717, 3789, 3790, 3791, 3792, 3845, 3846, 3847, 3849, 3905, 4095, 4100, 4120, 4196, 4228, 4264, 4272, 4375, 4415, 4494, 4723],
         3 =>  [   19,  2159,  2562,  2677,  2717,  3428,  3429,  3456, 3606, 3805, 3836, 3840, 3842, 4273, 4500, 4722, 4812],
         4 =>  [ -372,  -263,  -262,  -261,  -162,  -161,  -141,   -82,  -81,  -61],
         5 =>  [ -373,  -371,  -324,  -304,  -264,  -201,  -182,  -181, -121, -101,  -24],
         6 =>  [  -25,  2597,  3277,  3358,  3820,  4384,  4710],
         7 =>  [ -368,  -367,  -365,  -344,  -241,    -1],
         8 =>  [ 3483,  3518,  3519,  3520,  3521,  3522,  3523,  3679, 3703],
         9 =>  [-1008, -1007, -1006, -1005, -1004, -1003, -1002, -1001, -375, -374, -370, -369, -366, -364, -284,  -41,  -22],
        10 =>  [   65,    66,    67,   210,   394,   495,  3537,  3711, 4024, 4197, 4395]
    );

    public static $sockets                  = array(        // jsStyle Strings
        'meta',                         'red',                          'yellow',                       'blue'
    );

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

    public static $ssdMaskFields            = array(
        'shoulderMultiplier',           'trinketMultiplier',            'weaponMultiplier',             'primBudged',
        'rangedMultiplier',             'clothShoulderArmor',           'leatherShoulderArmor',         'mailShoulderArmor',
        'plateShoulderArmor',           'weaponDPS1H',                  'weaponDPS2H',                  'casterDPS1H',
        'casterDPS2H',                  'rangedDPS',                    'wandDPS',                      'spellPower',
        null,                           null,                           'tertBudged',                   'clothCloakArmor',
        'clothChestArmor',              'leatherChestArmor',            'mailChestArmor',               'plateChestArmor'
    );

    public static $dateFormatShort          = "Y/m/d";
    public static $dateFormatLong           = "Y/m/d H:i:s";

    public static $changeLevelString        = '<a href="javascript:;" onmousedown="return false" class="tip" style="color: white; cursor: pointer" onclick="$WH.g_staticTooltipLevelClick(this, null, 0)" onmouseover="$WH.Tooltip.showAtCursor(event, \'<span class=\\\'q2\\\'>\' + LANG.tooltip_changelevel + \'</span>\')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"><!--lvl-->%s</a>';

    public static $filterResultString       = 'sprintf(%s, %s, %s) + LANG.dash + LANG.lvnote_tryfiltering.replace(\'<a>\', \'<a href="javascript:;" onclick="fi_toggle()">\')';
    public static $narrowResultString       = 'sprintf(%s, %s, %s) + LANG.dash + LANG.lvnote_trynarrowing';
    public static $setCriteriaString        = "fi_setCriteria(%s, %s, %s);\n";

    public static $expansionString          = array(        // 3 & 4 unused .. obviously
        null,           'bc',           'wotlk',            'cata',                'mop'
    );

    public static $spellEffectStrings       = array(
        0 => 'None',
        1 => 'Instakill',
        2 => 'School Damage',
        3 => 'Dummy',
        4 => 'Portal Teleport',
        5 => 'Teleport Units',
        6 => 'Apply Aura',
        7 => 'Environmental Damage',
        8 => 'Power Drain',
        9 => 'Health Leech',
        10 => 'Heal',
        11 => 'Bind',
        12 => 'Portal',
        13 => 'Ritual Base',
        14 => 'Ritual Specialize',
        15 => 'Ritual Activate Portal',
        16 => 'Quest Complete',
        17 => 'Weapon Damage NoSchool',
        18 => 'Resurrect',
        19 => 'Add Extra Attacks',
        20 => 'Dodge',
        21 => 'Evade',
        22 => 'Parry',
        23 => 'Block',
        24 => 'Create Item',
        25 => 'Weapon',
        26 => 'Defense',
        27 => 'Persistent Area Aura',
        28 => 'Summon',
        29 => 'Leap',
        30 => 'Energize',
        31 => 'Weapon Percent Damage',
        32 => 'Trigger Missile',
        33 => 'Open Lock',
        34 => 'Summon Change Item',
        35 => 'Apply Area Aura Party',
        36 => 'Learn Spell',
        37 => 'Spell Defense',
        38 => 'Dispel',
        39 => 'Language',
        40 => 'Dual Wield',
        41 => 'Jump',
        42 => 'Jump Dest',
        43 => 'Teleport Units Face Caster',
        44 => 'Skill Step',
        45 => 'Add Honor',
        46 => 'Spawn',
        47 => 'Trade Skill',
        48 => 'Stealth',
        49 => 'Detect',
        50 => 'Trans Door',
        51 => 'Force Critical Hit',
        52 => 'Guarantee Hit',
        53 => 'Enchant Item Permanent',
        54 => 'Enchant Item Temporary',
        55 => 'Tame Creature',
        56 => 'Summon Pet',
        57 => 'Learn Pet Spell',
        58 => 'Weapon Damage',
        59 => 'Create Random Item',
        60 => 'Proficiency',
        61 => 'Send Event',
        62 => 'Power Burn',
        63 => 'Threat',
        64 => 'Trigger Spell',
        65 => 'Apply Area Aura Raid',
        66 => 'Create Mana Gem',
        67 => 'Heal Max Health',
        68 => 'Interrupt Cast',
        69 => 'Distract',
        70 => 'Pull',
        71 => 'Pickpocket',
        72 => 'Add Farsight',
        73 => 'Untrain Talents',
        74 => 'Apply Glyph',
        75 => 'Heal Mechanical',
        76 => 'Summon Object Wild',
        77 => 'Script Effect',
        78 => 'Attack',
        79 => 'Sanctuary',
        80 => 'Add Combo Points',
        81 => 'Create House',
        82 => 'Bind Sight',
        83 => 'Duel',
        84 => 'Stuck',
        85 => 'Summon Player',
        86 => 'Activate Object',
        87 => 'WMO Damage',
        88 => 'WMO Repair',
        89 => 'WMO Change',
        90 => 'Kill Credit',
        91 => 'Threat All',
        92 => 'Enchant Held Item',
        93 => 'Force Deselect',
        94 => 'Self Resurrect',
        95 => 'Skinning',
        96 => 'Charge',
        97 => 'Cast Button',
        98 => 'Knock Back',
        99 => 'Disenchant',
        100 => 'Inebriate',
        101 => 'Feed Pet',
        102 => 'Dismiss Pet',
        103 => 'Reputation',
        104 => 'Summon Object Slot1',
        105 => 'Summon Object Slot2',
        106 => 'Summon Object Slot3',
        107 => 'Summon Object Slot4',
        108 => 'Dispel Mechanic',
        109 => 'Summon Dead Pet',
        110 => 'Destroy All Totems',
        111 => 'Durability Damage',
        112 => 'Summon Demon',
        113 => 'Resurrect New',
        114 => 'Attack Me',
        115 => 'Durability Damage Percent',
        116 => 'Skin Player Corpse',
        117 => 'Spirit Heal',
        118 => 'Skill',
        119 => 'Apply Area Aura Pet',
        120 => 'Teleport Graveyard',
        121 => 'Normalized Weapon Dmg',
        122 => 'Unknown Effect',
        123 => 'Send Taxi',
        124 => 'Pull Towards',
        125 => 'Modify Threat Percent',
        126 => 'Steal Beneficial Buff',
        127 => 'Prospecting',
        128 => 'Apply Area Aura Friend',
        129 => 'Apply Area Aura Enemy',
        130 => 'Redirect Threat',
        131 => 'Unknown Effect',
        132 => 'Play Music',
        133 => 'Unlearn Specialization',
        134 => 'Kill Credit2',
        135 => 'Call Pet',
        136 => 'Heal Percent',
        137 => 'Energize Percent',
        138 => 'Leap Back',
        139 => 'Clear Quest',
        140 => 'Force Cast',
        141 => 'Force Cast With Value',
        142 => 'Trigger Spell With Value',
        143 => 'Apply Area Aura Owner',
        144 => 'Knock Back Dest',
        145 => 'Pull Towards Dest',
        146 => 'Activate Rune',
        147 => 'Quest Fail',
        148 => 'Unknown Effect',
        149 => 'Charge Dest',
        150 => 'Quest Start',
        151 => 'Trigger Spell 2',
        152 => 'Unknown Effect',
        153 => 'Create Tamed Pet',
        154 => 'Discover Taxi',
        155 => 'Titan Grip',
        156 => 'Enchant Item Prismatic',
        157 => 'Create Item 2',
        158 => 'Milling',
        159 => 'Allow Rename Pet',
        160 => 'Unknown Effect',
        161 => 'Talent Spec Count',
        162 => 'Talent Spec Select',
        163 => 'Unknown Effect',
        164 => 'Remove Aura'
    );

    public static $spellAuraStrings         = array(
        0 => 'None',
        1 => 'Bind Sight',
        2 => 'Mod Possess',
        3 => 'Periodic Damage',
        4 => 'Dummy',
        5 => 'Mod Confuse',
        6 => 'Mod Charm',
        7 => 'Mod Fear',
        8 => 'Periodic Heal',
        9 => 'Mod Attack Speed',
        10 => 'Mod Threat',
        11 => 'Taunt',
        12 => 'Stun',
        13 => 'Mod Damage Done',
        14 => 'Mod Damage Taken',
        15 => 'Damage Shield',
        16 => 'Mod Stealth',
        17 => 'Mod Stealth Detection',
        18 => 'Mod Invisibility',
        19 => 'Mod Invisibility Detection',
        20 => 'Obsolete Mod Health',
        21 => 'Obsolete Mod Power',
        22 => 'Mod Resistance',
        23 => 'Periodic Trigger Spell',
        24 => 'Periodic Energize',
        25 => 'Pacify',
        26 => 'Root',
        27 => 'Silence',
        28 => 'Reflect Spells',
        29 => 'Mod Stat',
        30 => 'Mod Skill',
        31 => 'Mod Increase Speed',
        32 => 'Mod Increase Mounted Speed',
        33 => 'Mod Decrease Speed',
        34 => 'Mod Increase Health',
        35 => 'Mod Increase Energy',
        36 => 'Shapeshift',
        37 => 'Effect Immunity',
        38 => 'State Immunity',
        39 => 'School Immunity',
        40 => 'Damage Immunity',
        41 => 'Dispel Immunity',
        42 => 'Proc Trigger Spell',
        43 => 'Proc Trigger Damage',
        44 => 'Track Creatures',
        45 => 'Track Resources',
        46 => 'Mod Parry Skill',
        47 => 'Mod Parry Percent',
        48 => 'Mod Dodge Skill',
        49 => 'Mod Dodge Percent',
        50 => 'Mod Critical Healing Amount',
        51 => 'Mod Block Percent',
        52 => 'Mod Weapon Crit Percent',
        53 => 'Periodic Leech',
        54 => 'Mod Hit Chance',
        55 => 'Mod Spell Hit Chance',
        56 => 'Transform',
        57 => 'Mod Spell Crit Chance',
        58 => 'Mod Increase Swim Speed',
        59 => 'Mod Damage Done Creature',
        60 => 'Pacify Silence',
        61 => 'Mod Scale',
        62 => 'Periodic Health Funnel',
        63 => 'Periodic Mana Funnel',
        64 => 'Periodic Mana Leech',
        65 => 'Mod Casting Speed (not stacking)',
        66 => 'Feign Death',
        67 => 'Disarm',
        68 => 'Stalked',
        69 => 'School Absorb',
        70 => 'Extra Attacks',
        71 => 'Mod Spell Crit Chance School',
        72 => 'Mod Power Cost School Percent',
        73 => 'Mod Power Cost School',
        74 => 'Reflect Spells School',
        75 => 'Language',
        76 => 'Far Sight',
        77 => 'Mechanic Immunity',
        78 => 'Mounted',
        79 => 'Mod Damage Percent Done',
        80 => 'Mod Percent Stat',
        81 => 'Split Damage Percent',
        82 => 'Water Breathing',
        83 => 'Mod Base Resistance',
        84 => 'Mod Health Regeneration',
        85 => 'Mod Power Regeneration',
        86 => 'Channel Death Item',
        87 => 'Mod Damage Percent Taken',
        88 => 'Mod Health Regeneration Percent',
        89 => 'Periodic Damage Percent',
        90 => 'Mod Resist Chance',
        91 => 'Mod Detect Range',
        92 => 'Prevent Fleeing',
        93 => 'Unattackable',
        94 => 'Interrupt Regeneration',
        95 => 'Ghost',
        96 => 'Spell Magnet',
        97 => 'Mana Shield',
        98 => 'Mod Skill Talent',
        99 => 'Mod Attack Power',
        100 => 'Auras Visible',
        101 => 'Mod Resistance Percent',
        102 => 'Mod Melee Attack Power Versus',
        103 => 'Mod Total Threat',
        104 => 'Water Walk',
        105 => 'Feather Fall',
        106 => 'Hover',
        107 => 'Add Flat Modifier',
        108 => 'Add Percent Modifier',
        109 => 'Add Target Trigger',
        110 => 'Mod Power Regeneration Percent',
        111 => 'Add Caster Hit Trigger',
        112 => 'Override Class Scripts',
        113 => 'Mod Ranged Damage Taken',
        114 => 'Mod Ranged Damage Taken Percent',
        115 => 'Mod Healing',
        116 => 'Mod Regeneration During Combat',
        117 => 'Mod Mechanic Resistance',
        118 => 'Mod Healing Percent',
        119 => 'Share Pet Tracking',
        120 => 'Untrackable',
        121 => 'Empathy',
        122 => 'Mod Offhand Damage Percent',
        123 => 'Mod Target Resistance',
        124 => 'Mod Ranged Attack Power',
        125 => 'Mod Melee Damage Taken',
        126 => 'Mod Melee Damage Taken Percent',
        127 => 'Ranged Attack Power Attacker Bonus',
        128 => 'Possess Pet',
        129 => 'Mod Speed Always',
        130 => 'Mod Mounted Speed Always',
        131 => 'Mod Ranged Attack Power Versus',
        132 => 'Mod Increase Energy Percent',
        133 => 'Mod Increase Health Percent',
        134 => 'Mod Mana Regeneration Interrupt',
        135 => 'Mod Healing Done',
        136 => 'Mod Healing Done Percent',
        137 => 'Mod Total Stat Percentage',
        138 => 'Mod Melee Haste',
        139 => 'Force Reaction',
        140 => 'Mod Ranged Haste',
        141 => 'Mod Ranged Ammo Haste',
        142 => 'Mod Base Resistance Percent',
        143 => 'Mod Resistance Exclusive',
        144 => 'Safe Fall',
        145 => 'Mod Pet Talent Points',
        146 => 'Allow Tame Pet Type',
        147 => 'Mechanic Immunity Mask',
        148 => 'Retain Combo Points',
        149 => 'Reduce Pushback',
        150 => 'Mod Shield Blockvalue Percent',
        151 => 'Track Stealthed',
        152 => 'Mod Detected Range',
        153 => 'Split Damage Flat',
        154 => 'Mod Stealth Level',
        155 => 'Mod Water Breathing',
        156 => 'Mod Reputation Gain',
        157 => 'Pet Damage Multi',
        158 => 'Mod Shield Blockvalue',
        159 => 'No PvP Credit',
        160 => 'Mod AoE Avoidance',
        161 => 'Mod Health Regeneration In Combat',
        162 => 'Power Burn Mana',
        163 => 'Mod Crit Damage Bonus',
        164 => 'Unknown Aura',
        165 => 'Melee Attack Power Attacker Bonus',
        166 => 'Mod Attack Power Percent',
        167 => 'Mod Ranged Attack Power Percent',
        168 => 'Mod Damage Done Versus',
        169 => 'Mod Crit Percent Versus',
        170 => 'Detect Amore',
        171 => 'Mod Speed (not stacking)',
        172 => 'Mod Mounted Speed (not stacking)',
        173 => 'Unknown Aura',
        174 => 'Mod Spell Damage Of Stat Percent',
        175 => 'Mod Spell Healing Of Stat Percent',
        176 => 'Spirit Of Redemption',
        177 => 'AoE Charm',
        178 => 'Mod Debuff Resistance',
        179 => 'Mod Attacker Spell Crit Chance',
        180 => 'Mod Flat Spell Damage Versus',
        181 => 'Unknown Aura',
        182 => 'Mod Resistance Of Stat Percent',
        183 => 'Mod Critical Threat',
        184 => 'Mod Attacker Melee Hit Chance',
        185 => 'Mod Attacker Ranged Hit Chance',
        186 => 'Mod Attacker Spell Hit Chance',
        187 => 'Mod Attacker Melee Crit Chance',
        188 => 'Mod Attacker Ranged Crit Chance',
        189 => 'Mod Rating',
        190 => 'Mod Faction Reputation Gain',
        191 => 'Use Normal Movement Speed',
        192 => 'Mod Melee Ranged Haste',
        193 => 'Melee Slow',
        194 => 'Mod Target Absorb School',
        195 => 'Mod Target Ability Absorb School',
        196 => 'Mod Cooldown',
        197 => 'Mod Attacker Spell And Weapon Crit Chance',
        198 => 'Unknown Aura',
        199 => 'Mod Increases Spell Percent to Hit',
        200 => 'Mod XP Percent',
        201 => 'Fly',
        202 => 'Ignore Combat Result',
        203 => 'Mod Attacker Melee Crit Damage',
        204 => 'Mod Attacker Ranged Crit Damage',
        205 => 'Mod School Crit Dmg Taken',
        206 => 'Mod Increase Vehicle Flight Speed',
        207 => 'Mod Increase Mounted Flight Speed',
        208 => 'Mod Increase Flight Speed',
        209 => 'Mod Mounted Flight Speed Always',
        210 => 'Mod Vehicle Speed Always',
        211 => 'Mod Flight Speed (not stacking)',
        212 => 'Mod Ranged Attack Power Of Stat Percent',
        213 => 'Mod Rage from Damage Dealt',
        214 => 'Unknown Aura',
        215 => 'Arena Preparation',
        216 => 'Haste Spells',
        217 => 'Unknown Aura',
        218 => 'Haste Ranged',
        219 => 'Mod Mana Regeneration from Stat',
        220 => 'Mod Rating from Stat',
        221 => 'Detaunt',
        222 => 'Unknown Aura',
        223 => 'Raid Proc from Charge',
        224 => 'Unknown Aura',
        225 => 'Raid Proc from Charge With Value',
        226 => 'Periodic Dummy',
        227 => 'Periodic Trigger Spell With Value',
        228 => 'Detect Stealth',
        229 => 'Mod AoE Damage Avoidance',
        230 => 'Unknown Aura',
        231 => 'Proc Trigger Spell With Value',
        232 => 'Mechanic Duration Mod',
        233 => 'Unknown Aura',
        234 => 'Mechanic Duration Mod (not stacking)',
        235 => 'Mod Dispel Resist',
        236 => 'Control Vehicle',
        237 => 'Mod Spell Damage Of Attack Power',
        238 => 'Mod Spell Healing Of Attack Power',
        239 => 'Mod Scale 2',
        240 => 'Mod Expertise',
        241 => 'Force Move Forward',
        242 => 'Mod Spell Damage from Healing',
        243 => 'Mod Faction',
        244 => 'Comprehend Language',
        245 => 'Mod Aura Duration By Dispel',
        246 => 'Mod Aura Duration By Dispel (not stacking)',
        247 => 'Clone Caster',
        248 => 'Mod Combat Result Chance',
        249 => 'Convert Rune',
        250 => 'Mod Increase Health 2',
        251 => 'Mod Enemy Dodge',
        252 => 'Mod Speed Slow All',
        253 => 'Mod Block Crit Chance',
        254 => 'Mod Disarm Offhand',
        255 => 'Mod Mechanic Damage Taken Percent',
        256 => 'No Reagent Use',
        257 => 'Mod Target Resist By Spell Class',
        258 => 'Unknown Aura',
        259 => 'Mod HoT Percent',
        260 => 'Screen Effect',
        261 => 'Phase',
        262 => 'Ability Ignore Aurastate',
        263 => 'Allow Only Ability',
        264 => 'Unknown Aura',
        265 => 'Unknown Aura',
        266 => 'Unknown Aura',
        267 => 'Mod Immune Aura Apply School',
        268 => 'Mod Attack Power Of Stat Percent',
        269 => 'Mod Ignore Target Resist',
        270 => 'Mod Ability Ignore Target Resist',
        271 => 'Mod Damage from Caster',
        272 => 'Ignore Melee Reset',
        273 => 'X Ray',
        274 => 'Ability Consume No Ammo',
        275 => 'Mod Ignore Shapeshift',
        276 => 'Unknown Aura',
        277 => 'Mod Max Affected Targets',
        278 => 'Mod Disarm Ranged',
        279 => 'Initialize Images',
        280 => 'Mod Armor Penetration Percent',
        281 => 'Mod Honor Gain Percent',
        282 => 'Mod Base Health Percent',
        283 => 'Mod Healing Received',
        284 => 'Linked',
        285 => 'Mod Attack Power Of Armor',
        286 => 'Ability Periodic Crit',
        287 => 'Deflect Spells',
        288 => 'Ignore Hit Direction',
        289 => 'Unknown Aura',
        290 => 'Mod Crit Percent',
        291 => 'Mod XP Quest Percent',
        292 => 'Open Stable',
        293 => 'Override Spells',
        294 => 'Prevent Power Regeneration',
        295 => 'Unknown Aura',
        296 => 'Set Vehicle Id',
        297 => 'Block Spell Family',
        298 => 'Strangulate',
        299 => 'Unknown Aura',
        300 => 'Share Damage Percent',
        301 => 'School Heal Absorb',
        302 => 'Unknown Aura',
        303 => 'Mod Damage Done Versus Aurastate',
        304 => 'Mod Fake Inebriate',
        305 => 'Mod Minimum Speed',
        306 => 'Unknown Aura',
        307 => 'Heal Absorb Test',
        308 => 'Unknown Aura',
        309 => 'Unknown Aura',
        310 => 'Mod Creature AoE Damage Avoidance',
        311 => 'Unknown Aura',
        312 => 'Unknown Aura',
        313 => 'Unknown Aura',
        314 => 'Prevent Ressurection',
        315 => 'Underwater Walking',
        316 => 'Periodic Haste'
    );

    public static $bgImagePath              = array (
        'tiny'   => 'style="background-image: url(images/icons/tiny/%s.gif)"',
        'small'  => 'style="background-image: url(images/icons/small/%s.jpg)"',
        'medium' => 'style="background-image: url(images/icons/medium/%s.jpg)"',
        'large'  => 'style="background-image: url(images/icons/large/%s.jpg)"',
    );

    private static $execTime = 0.0;

    public static function execTime($set = false)
    {
        if ($set)
        {
            self::$execTime = microTime(true);
            return;
        }

        if (!self::$execTime)
            return;

        $newTime        = microTime(true);
        $tDiff          = $newTime - self::$execTime;
        self::$execTime = $newTime;

        return self::formatTime($tDiff * 1000, true);
    }

    public static function colorByRarity($idx)
    {
        if (!isset(self::$rarityColorStings))
            $idx = 1;

        return self::$rarityColorStings($idx);
    }

    public static function formatMoney($qty)
    {
        $money = '';

        if ($qty >= 10000)
        {
            $g = floor($qty / 10000);
            $money .= '<span class="moneygold">'.$g.'</span> ';
            $qty -= $g * 10000;
        }

        if ($qty >= 100)
        {
            $s = floor($qty / 100);
            $money .= '<span class="moneysilver">'.$s.'</span> ';
            $qty -= $s * 100;
        }

        if ($qty > 0)
            $money .= '<span class="moneycopper">'.$qty.'</span>';

        return $money;
    }

    public static function parseTime($sec)
    {
        $time = [];

        if ($sec >= 3600 * 24)
        {
            $time['d'] = floor($sec / 3600 / 24);
            $sec -= $time['d'] * 3600 * 24;
        }

        if ($sec >= 3600)
        {
            $time['h'] = floor($sec / 3600);
            $sec -= $time['h'] * 3600;
        }

        if ($sec >= 60)
        {
            $time['m'] = floor($sec / 60);
            $sec -= $time['m'] * 60;
        }

        if ($sec > 0)
        {
            $time['s'] = (int)$sec;
            $sec -= $time['s'];
        }

        if (($sec * 1000) % 1000)
            $time['ms'] = (int)($sec * 1000);

        return $time;
    }

    public static function formatTime($base, $short = false)
    {
        $s = self::parseTime($base / 1000);
        $fmt = [];

        if ($short)
        {
            if (isset($s['d']))
                return round($s['d'])." ".Lang::$main['daysAbbr'];
            if (isset($s['h']))
                return round($s['h'])." ".Lang::$main['hoursAbbr'];
            if (isset($s['m']))
                return round($s['m'])." ".Lang::$main['minutesAbbr'];
            if (isset($s['s']))
                return round($s['s'] + @$s['ms'] / 1000, 2)." ".Lang::$main['secondsAbbr'];
            if (isset($s['ms']))
                return $s['ms']." ".Lang::$main['millisecsAbbr'];
        }
        else
        {
            if (isset($s['d']))
                $fmt[] = $s['d']." ".Lang::$main['days'];
            if (isset($s['h']))
                $fmt[] = $s['h']." ".Lang::$main['hours'];
            if (isset($s['m']))
                $fmt[] = $s['m']." ".Lang::$main['minutes'];
            if (isset($s['s']))
                $fmt[] = $s['s']." ".Lang::$main['seconds'];
            if (isset($s['ms']))
                $fmt[] = $s['ms']." ".Lang::$main['millisecs'];
        }

        return implode(' ', $fmt);
    }

    public static function sideByRaceMask($race)
    {
        if (!$race || $race == RACE_MASK_ALL)               // Any
            return 3;

        if ($race & RACE_MASK_HORDE)                        // Horde
            return 2;

        if ($race & RACE_MASK_ALLIANCE)                     // Alliance
            return 1;

        return 0;
    }

    public static function sqlEscape($data)
    {
        if (!is_array($data))
            return mysql_real_escape_string(trim($data));

        array_walk($data, function(&$item, $key) { $item = Util::sqlEscape($item); });

        return $data;
    }

    public static function jsEscape($string)
    {
        return strtr(trim($string), array(
            '\\' => '\\\\',
            "'"  => "\\'",
            // '"'  => '\\"',
            "\r" => '\\r',
            "\n" => '\\n',
            // '</' => '<\/',
        ));
    }

    public static function localizedString($data, $field)
    {
        $sqlLocales = ['EN', 2 => 'FR', 3 => 'DE', 6 => 'ES', 8 => 'RU'];

        // default back to enUS if localization unavailable

        // default case: selected locale available
        if (!empty($data[$field.'_loc'.User::$localeId]))
            return $data[$field.'_loc'.User::$localeId];

        // dbc-case
        else if (!empty($data[$field.$sqlLocales[User::$localeId]]))
            return $data[$field.$sqlLocales[User::$localeId]];

        // locale not enUS; aowow-type localization available; add brackets
        else if (User::$localeId != LOCALE_EN && isset($data[$field.'_loc0']) && !empty($data[$field.'_loc0']))
            return  '['.$data[$field.'_loc0'].']';

        // dbc-case
        else if (User::$localeId != LOCALE_EN && isset($data[$field.$sqlLocales[0]]) && !empty($data[$field.$sqlLocales[0]]))
            return  '['.$data[$field.$sqlLocales[0]].']';

        // locale not enUS; TC localization; add brackets
        else if (User::$localeId != LOCALE_EN && isset($data[$field]) && !empty($data[$field]))
            return '['.$data[$field].']';

        // locale enUS; TC localization; return normal
        else if (User::$localeId == LOCALE_EN && isset($data[$field]) && !empty($data[$field]))
            return $data[$field];

        // nothing to find; be empty
        else
            return '';
    }

    public static function extractURLParams($str)
    {
        $arr = explode('.', $str);

        foreach ($arr as $i => $a)
            if (!is_numeric($a))
                $arr[$i] = null;

        return $arr;
    }

    // for item and spells
    public static function setRatingLevel($level, $type, $val)
    {
        if (in_array($type, array(ITEM_MOD_DEFENSE_SKILL_RATING, ITEM_MOD_PARRY_RATING, ITEM_MOD_BLOCK_RATING)) && $level < 34)
            $level = 34;

        if (!isset(Util::$gtCombatRatings[$type]))
            $result = 0;

        else if ($level > 70)
            $c = 82 / 52 * pow(131 / 63, ($level - 70) / 10);
        else if ($level > 60)
            $c = 82 / (262 - 3 * $level);
        else if ($level > 10)
            $c = ($level - 8) / 52;
        else
            $c = 2 / 52;

        $result = number_format($val / Util::$gtCombatRatings[$type] / $c, 2);

        if (!in_array($type, array(ITEM_MOD_DEFENSE_SKILL_RATING, ITEM_MOD_EXPERTISE_RATING)))
            $result .= '%';

        return sprintf(Lang::$item['ratingString'], '<!--rtg%'.$type.'-->' . $result, '<!--lvl-->' . $level);
    }

    public static function powerUseLocale($domain = 'www')
    {
        foreach (Util::$localeStrings as $k => $v)
        {
            if (strstr($v, $domain))
            {
                User::useLocale($k);
                Lang::load(User::$localeString);
                return;
            }
        }

        if ($domain == 'www')
        {
            User::useLocale(LOCALE_EN);
            Lang::load(User::$localeString);
        }
    }

    // EnchantmentTypes
    // 0 => (dnd stuff; ignore)
    // 1 => proc spell from ObjectX (amountX == procChance?; ignore)
    // 2 => +AmountX damage
    // 3 => Spells form ObjectX (amountX == procChance?)
    // 4 => +AmountX resistance for ObjectX School
    // 5 => +AmountX for Statistic by type of ObjectX
    // 6 => Rockbiter AmountX as Damage (ignore)
    // 7 => Engineering gadgets
    // 8 => Extra Sockets AmountX as socketCount (ignore)
    public static function parseItemEnchantment($enchant, $amountOverride = null)
    {
        if (!$enchant || empty($enchant))
            return false;

        $jsonStats = [];
        for ($h = 1; $h <= 3; $h++)
        {
            if (isset($amountOverride))                     // itemSuffixes have dynamic amount
                $enchant['amount'.$h] = $amountOverride;

            switch ($enchant['type'.$h])
            {
                case 2:
                    @$jsonStats[2] += $enchant['amount'.$h];
                    break;
                case 3:
                case 7:
                    $spl   = new SpellList(array(['id', (int)$enchant['object'.$h]]));
                    $gains = $spl->getStatGain();

                    foreach ($gains as $gain)
                        foreach ($gain as $k => $v)         // array_merge screws up somehow...
                            @$jsonStats[$k] += $v;
                    break;
                case 4:
                    switch ($enchant['object'.$h])
                    {
                        case 0:                             // Physical
                            @$jsonStats[50] += $enchant['amount'.$h];
                            break;
                        case 1:                             // Holy
                            @$jsonStats[53] += $enchant['amount'.$h];
                            break;
                        case 2:                             // Fire
                            @$jsonStats[51] += $enchant['amount'.$h];
                            break;
                        case 3:                             // Nature
                            @$jsonStats[55] += $enchant['amount'.$h];
                            break;
                        case 4:                             // Frost
                            @$jsonStats[52] += $enchant['amount'.$h];
                            break;
                        case 5:                             // Shadow
                            @$jsonStats[54] += $enchant['amount'.$h];
                            break;
                        case 6:                             // Arcane
                            @$jsonStats[56] += $enchant['amount'.$h];
                            break;
                    }
                    break;
                case 5:
                    @$jsonStats[$enchant['object'.$h]] += $enchant['amount'.$h];
                    break;
            }
        }

        // check if we use these mods
        $return = [];
        foreach ($jsonStats as $k => $v)
        {
            if ($str = Util::$itemMods[$k])
                $return[$str] = $v;
        }

        return $return;
    }

    // BaseType::_construct craaap!
    // todo: unify names
    public static function getNameFieldName($tpl)
    {
        if (isset($tpl['name']) || isset($tpl['name_loc0']))
            return 'name';
        else if (isset($tpl['title']) || isset($tpl['title_loc0']))
            return 'title';
        else if (isset($tpl['male']) || isset($tpl['male_loc']))
            return 'male';
        else
            return null;
    }

    // BaseType::iterate craaaaaaaaap!!!
    // todo: unify indizes
    public static function getIdFieldName($tpl)
    {
        if (isset($tpl['entry']))
            return 'entry';
        else if (isset($tpl['Id']))
            return 'Id';
        else if (isset($tpl['id']))
            return 'id';
        else if (isset($tpl['ID']))
            return 'ID';
        else
            return null;
    }
}

?>
