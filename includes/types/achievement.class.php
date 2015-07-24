<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AchievementList extends BaseType
{
    use listviewHelper;

    public static $type      = TYPE_ACHIEVEMENT;
    public static $brickFile = 'achievement';

    public        $criteria  = [];

    protected     $queryBase = 'SELECT `a`.*, `a`.`id` AS ARRAY_KEY FROM ?_achievement a';
    protected     $queryOpts = array(
                          'a' => [['si'], 'o' => 'orderInGroup ASC'],
                         'si' => ['j' => ['?_icons si ON si.id = a.iconId', true], 's' => ', si.iconString'],
                         'ac' => ['j' => ['?_achievementcriteria AS `ac` ON `ac`.`refAchievementId` = `a`.`id`', true], 'g' => '`a`.`id`']
                  );

    /*
        todo: evaluate TC custom-data-tables: a*_criteria_data should be merged on installation
    */

    public function __construct($conditions = [], $miscData = null)
    {
        parent::__construct($conditions, $miscData);

        if ($this->error)
            return;

        // post processing
        $rewards = DB::World()->select('
            SELECT ar.entry AS ARRAY_KEY, ar.*, lar.* FROM achievement_reward ar LEFT JOIN locales_achievement_reward lar ON lar.entry = ar.entry WHERE ar.entry IN (?a)',
            $this->getFoundIDs()
        );

        foreach ($this->iterate() as $_id => &$_curTpl)
        {
            $_curTpl['rewards'] = [];

            if (!empty($rewards[$_id]))
            {
                $_curTpl = array_merge($rewards[$_id], $_curTpl);

                if ($rewards[$_id]['mailTemplate'])
                {
                    // using class Loot creates an inifinite loop cirling between Loot, ItemList and SpellList or something
                    // $mailSrc = new Loot();
                    // $mailSrc->getByContainer(LOOT_MAIL, $rewards[$_id]['mailTemplate']);
                    // foreach ($mailSrc->iterate() as $loot)
                        // $_curTpl['rewards'][] = [TYPE_ITEM, $loot['id']];

                    // lets just assume for now, that mailRewards for achievements do not contain references
                    $mailRew = DB::World()->selectCol('SELECT Item FROM mail_loot_template WHERE Reference <= 0 AND entry = ?d', $rewards[$_id]['mailTemplate']);
                    foreach ($mailRew AS $mr)
                        $_curTpl['rewards'][] = [TYPE_ITEM, $mr];
                }
            }

            //"rewards":[[11,137],[3,138]]   [type, typeId]
            if (!empty($_curTpl['item']))
                $_curTpl['rewards'][] = [TYPE_ITEM, $_curTpl['item']];
            if (!empty($_curTpl['itemExtra']))
                $_curTpl['rewards'][] = [TYPE_ITEM, $_curTpl['itemExtra']];
            if (!empty($_curTpl['title_A']))
                $_curTpl['rewards'][] = [TYPE_TITLE, $_curTpl['title_A']];
            if (!empty($_curTpl['title_H']))
                if (empty($_curTpl['title_A']) || $_curTpl['title_A'] != $_curTpl['title_H'])
                    $_curTpl['rewards'][] = [TYPE_TITLE, $_curTpl['title_H']];

            // icon
            $_curTpl['iconString'] = $_curTpl['iconString'] ?: 'trade_engineering';
        }
    }

    public function getJSGlobals($addMask = GLOBALINFO_ANY)
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            if ($addMask & GLOBALINFO_SELF)
                $data[TYPE_ACHIEVEMENT][$this->id] = ['icon' => $this->curTpl['iconString'], 'name' => $this->getField('name', true)];

            if ($addMask & GLOBALINFO_REWARDS)
                foreach ($this->curTpl['rewards'] as $_)
                    $data[$_[0]][$_[1]] = $_[1];
        }

        return $data;
    }

    public function getListviewData($addInfoMask = 0x0)
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'id'          => $this->id,
                'name'        => $this->getField('name', true),
                'description' => $this->getField('description', true),
                'points'      => $this->curTpl['points'],
                'side'        => $this->curTpl['faction'],
                'category'    => $this->curTpl['category'],
                'parentcat'   => $this->curTpl['parentCat'],
            );

            if ($addInfoMask & ACHIEVEMENTINFO_PROFILE)
                $data[$this->id]['icon'] = $this->curTpl['iconString'];

            // going out on a limb here: type = 1 if in level 3 of statistics tree, so, IF (statistic AND parentCat NOT statistic (1)) i guess
            if ($this->curTpl['flags'] & ACHIEVEMENT_FLAG_COUNTER && $this->curTpl['parentCat'] != 1)
                $data[$this->id]['type'] = 1;

            if ($_ = $this->curTpl['rewards'])
                $data[$this->id]['rewards'] = $_;
            else if ($_ = $this->getField('reward', true))
                $data[$this->id]['reward'] = $_;
        }

        return $data;
    }

    // only for current template
    public function getCriteria()
    {
        if (isset($this->criteria[$this->id]))
            return $this->criteria[$this->id];

        $result = DB::Aowow()->Select('SELECT * FROM ?_achievementcriteria WHERE `refAchievementId` = ?d ORDER BY `order` ASC', $this->id);
        if (!$result)
            return [];

        $this->criteria[$this->id] = $result;

        return $this->criteria[$this->id];
    }

    public function renderTooltip()
    {
        $criteria = $this->getCriteria();
        $tmp  = [];
        $rows = [];
        $i    = 0;
        foreach ($criteria as $_row)
        {
            if ($i++ % 2)
                $tmp[] = $_row;
            else
                $rows[] = $_row;
        }
        if ($tmp)
            $rows = array_merge($rows, $tmp);

        $description = $this->getField('description', true);
        $name        = $this->getField('name', true);
        $criteria    = '';

        $i = 0;
        foreach ($rows as $crt)
        {
            $obj = (int)$crt['value1'];
            $qty = (int)$crt['value2'];

            // we could show them, but the tooltips are cluttered
            if (($crt['completionFlags'] & ACHIEVEMENT_CRITERIA_FLAG_HIDDEN) && User::$perms <= 0)
                continue;

            $crtName = Util::localizedString($crt, 'name');
            switch ($crt['type'])
            {
                // link to title - todo (low): crosslink
                case ACHIEVEMENT_CRITERIA_TYPE_EARNED_PVP_TITLE:
                    $crtName = Util::ucFirst(Lang::game('title')).Lang::main('colon').$crtName;
                    break;
                // link to quest
                case ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_QUEST:
                    if (!$crtName)
                        $crtName = QuestList::getName($obj);
                    break;
                // link to spell (/w icon)
                case ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET:
                case ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET2:
                case ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL:
                case ACHIEVEMENT_CRITERIA_TYPE_LEARN_SPELL:
                case ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL2:
                    if (!$crtName)
                        $crtName = SpellList::getName($obj);
                    break;
                // link to item (/w icon)
                case ACHIEVEMENT_CRITERIA_TYPE_OWN_ITEM:
                case ACHIEVEMENT_CRITERIA_TYPE_USE_ITEM:
                case ACHIEVEMENT_CRITERIA_TYPE_LOOT_ITEM:
                case ACHIEVEMENT_CRITERIA_TYPE_EQUIP_ITEM:
                    if (!$crtName)
                        $crtName = ItemList::getName($obj);
                    break;
                // link to faction (/w target reputation)
                case ACHIEVEMENT_CRITERIA_TYPE_GAIN_REPUTATION:
                    if (!$crtName)
                        $crtName = FactionList::getName($obj);
                    break;
            }

            $criteria .= '<!--cr'.$crt['id'].':'.$crt['type'].':'.$crt['value1'].'-->- '.Util::jsEscape($crtName);

            if ($crt['completionFlags'] & ACHIEVEMENT_CRITERIA_FLAG_MONEY_COUNTER)
                $criteria .= '&nbsp;<span class="moneygold">'.Lang::nf($crt['value2' ] / 10000).'</span>';

            $criteria .= '<br />';

            if (++$i == round(count($rows)/2))
                $criteria .= '</small></td><th class="q0" style="white-space: nowrap; text-align: left"><small>';
        }

        $x  = '<table><tr><td><b class="q">';
        $x .= Util::jsEscape($name);
        $x .= '</b></td></tr></table>';
        if ($description || $criteria)
            $x .= '<table><tr><td>';

        if ($description)
            $x .= '<br />'.Util::jsEscape($description).'<br />';

        if ($criteria)
        {
            $x .= '<br /><span class="q">'.Lang::achievement('criteria').':</span>';
            $x .= '<table width="100%"><tr><td class="q0" style="white-space: nowrap"><small>'.$criteria.'</small></th></tr></table>';
        }
        if ($description || $criteria)
            $x .= '</td></tr></table>';

        return $x;
    }

    public function getSourceData()
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                "n"  => $this->getField('name', true),
                "s"  => $this->curTpl['faction'],
                "t"  => TYPE_ACHIEVEMENT,
                "ti" => $this->id
            );
        }

        return $data;
    }
}


class AchievementListFilter extends Filter
{

    protected $enums         = array(
        11 => array(
              327 => 160,                                   // Lunar Festival
              335 => 187,                                   // Love is in the Air
              181 => 159,                                   // Noblegarden
              201 => 163,                                   // Children's Week
              341 => 161,                                   // Midsummer Fire Festival
              372 => 162,                                   // Brewfest
              324 => 158,                                   // Hallow's End
              404 => 14981,                                 // Pilgrim's Bounty
              141 => 156,                                   // Feast of Winter Veil
              409 => -3456,                                 // Day of the Dead
              398 => -3457,                                 // Pirates' Day
              FILTER_ENUM_ANY  => true,
              FILTER_ENUM_NONE => false,
              283 => -1,                                    // valid events without achievements
              285 => -1,   353 => -1,   420 => -1,
              400 => -1,   284 => -1,   374 => -1,
              321 => -1,   424 => -1,   301 => -1
        )
    );
    protected $genericFilter = array(                       // misc (bool): _NUMERIC => useFloat; _STRING => localized; _FLAG => match Value; _BOOLEAN => stringSet
         2 => [FILTER_CR_BOOLEAN,   'reward_loc0',    true                      ], // givesreward
         3 => [FILTER_CR_STRING,    'reward',         true                      ], // rewardtext
         7 => [FILTER_CR_BOOLEAN,   'chainId',                                  ], // partseries
         9 => [FILTER_CR_NUMERIC,   'id',             null,                 true], // id
        10 => [FILTER_CR_STRING,    'si.iconString',                            ], // icon
        18 => [FILTER_CR_STAFFFLAG, 'flags',                                    ], // flags
        14 => [FILTER_CR_FLAG,      'cuFlags',        CUSTOM_HAS_COMMENT        ], // hascomments
        15 => [FILTER_CR_FLAG,      'cuFlags',        CUSTOM_HAS_SCREENSHOT     ], // hasscreenshots
        16 => [FILTER_CR_FLAG,      'cuFlags',        CUSTOM_HAS_VIDEO          ], // hasvideos
    );

    protected function createSQLForCriterium(&$cr)
    {
        if (in_array($cr[0], array_keys($this->genericFilter)))
        {
            if ($genCr = $this->genericCriterion($cr))
                return $genCr;

            unset($cr);
            $this->error = true;
            return [1];
        }

        switch ($cr[0])
        {
            case 4:                                         // location [enum]
/* todo */      return [1];                                 // no plausible locations parsed yet
            case 5:                                         // first in series [yn]
                if ($this->int2Bool($cr[1]))
                    return $cr[1] ? ['AND', ['chainId', 0, '!'], ['cuFlags', ACHIEVEMENT_CU_FIRST_SERIES, '&']] : ['AND', ['chainId', 0, '!'], [['cuFlags', ACHIEVEMENT_CU_FIRST_SERIES, '&'], 0]];

                break;
            case 6:                                         // last in series [yn]
                if ($this->int2Bool($cr[1]))
                    return $cr[1] ? ['AND', ['chainId', 0, '!'], ['cuFlags', ACHIEVEMENT_CU_LAST_SERIES, '&']] : ['AND', ['chainId', 0, '!'], [['cuFlags', ACHIEVEMENT_CU_LAST_SERIES, '&'], 0]];

                break;
            case 11:                                        // Related Event [enum]
                $_ = isset($this->enums[$cr[0]][$cr[1]]) ? $this->enums[$cr[0]][$cr[1]] : null;
                if ($_ !== null)
                {
                    if (is_int($_))
                        return ($_ > 0) ? ['category', $_] : ['id', abs($_)];
                    else
                    {
                        $ids = array_filter($this->enums[$cr[0]], function($x) {
                            return is_int($x) && $x > 0;
                        });

                        return ['category', $ids, $_ ? null : '!'];
                    }
                }
                break;
        }

        unset($cr);
        $this->error = true;
        return [1];
    }

    protected function createSQLForValues()
    {
        $parts = [];
        $_v    = &$this->fiData['v'];

        // name ex: +description, +rewards
        if (isset($_v['na']))
        {
            $_ = [];
            if (isset($_v['ex']) && $_v['ex'] == 'on')
                $_ = $this->modularizeString(['name_loc'.User::$localeId, 'reward_loc'.User::$localeId, 'description_loc'.User::$localeId]);
            else
                $_ = $this->modularizeString(['name_loc'.User::$localeId]);

            if ($_)
                $parts[] = $_;
        }

        // points min
        if (isset($_v['minpt']))
        {
            if ($this->isSaneNumeric($_v['minpt']))
                $parts[] = ['points', $_v['minpt'],  '>='];
            else
                unset($_v['minpt']);
        }

        // points max
        if (isset($_v['maxpt']))
        {
            if ($this->isSaneNumeric($_v['maxpt']))
                $parts[] = ['points', $_v['maxpt'],  '<='];
            else
                unset($_v['maxpt']);
        }

        // faction (side)
        if (isset($_v['si']))
        {
            switch ($_v['si'])
            {
                case 3:                                     // both
                    $parts[] = ['faction', 0];
                    break;
                case -1:                                    // faction, exclusive both
                case -2:
                    $parts[] = ['faction', -$_v['si']];
                    break;
                case 1:                                     // faction, inclusive both
                case 2:
                    $parts[] = ['OR', ['faction', 0], ['faction', $_v['si']]];
                    break;
                default:
                    unset($_v['si']);
            }
        }

        return $parts;
    }
}

?>
