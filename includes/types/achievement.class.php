<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AchievementList extends BaseType
{
    use listviewHelper;

    public static $type      = TYPE_ACHIEVEMENT;
    public static $brickFile = 'achievement';

    public        $criteria  = [];
    public        $tooltip   = [];

    protected     $queryBase = 'SELECT `a`.*, `a`.`id` AS ARRAY_KEY FROM ?_achievement a';
    protected     $queryOpts = array(
                        'a'  => ['o' => 'orderInGroup ASC'],
                        'ac' => ['j' => ['?_achievementcriteria AS `ac` ON `ac`.`refAchievementId` = `a`.`id`', true], 'g' => '`a`.`id`']
                  );

    /*
        todo: evaluate TC custom-data-tables: a*_criteria_data should be merged on installation, a*_reward linked with mail_loot_template and achievement
    */

    public function __construct($conditions = [], $miscData = null)
    {
        parent::__construct($conditions, $miscData);

        // post processing
        foreach ($this->iterate() as &$_curTpl)
        {
            //"rewards":[[11,137],[3,138]]   [type, typeId]
            $_curTpl['rewards'] = [TYPE_ITEM => [], TYPE_TITLE => []];
            if (!empty($_curTpl['rewardIds']))
            {
                $rewIds  = explode(" ", $_curTpl['rewardIds']);
                foreach ($rewIds as $rewId)
                {
                    if ($rewId > 0)
                        $_curTpl['rewards'][TYPE_ITEM][]  = $rewId;
                    else if ($rewId < 0)
                        $_curTpl['rewards'][TYPE_TITLE][] = -$rewId;
                }
            }
        }
    }

    public function addGlobalsToJScript($addMask = GLOBALINFO_ANY)
    {
        foreach ($this->iterate() as $__)
        {
            if ($addMask & GLOBALINFO_SELF)
                Util::$pageTemplate->extendGlobalData(self::$type, [$this->id => array(
                    'icon' => $this->curTpl['iconString'],
                    'name' => $this->getField('name', true)
                )]);

            if ($addMask & GLOBALINFO_REWARDS)
            {
                foreach ($this->curTpl['rewards'][TYPE_ITEM] as $_)
                    Util::$pageTemplate->extendGlobalIds(TYPE_ITEM, $_);

                foreach ($this->curTpl['rewards'][TYPE_TITLE] as $_)
                    Util::$pageTemplate->extendGlobalIds(TYPE_TITLE, $_);
            }
        }
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
                'faction'     => $this->curTpl['faction'],
                'category'    => $this->curTpl['category'],
                'parentcat'   => $this->curTpl['parentCat'],
            );

            if ($addInfoMask & ACHIEVEMENTINFO_PROFILE)
                $data[$this->id]['icon'] = $this->curTpl['iconString'];

            // going out on a limb here: type = 1 if in level 3 of statistics tree, so, IF (statistic AND parentCat NOT statistic (1)) i guess
            if  ($this->curTpl['flags'] & ACHIEVEMENT_FLAG_COUNTER && $this->curTpl['parentCat'] != 1)
                $data[$this->id]['type'] = 1;

            $rewards = [];
            foreach ($this->curTpl['rewards'] as $type => $rIds)
                foreach ($rIds as $rId)
                    $rewards[] = [$type, $rId];

            if ($rewards)
                $data[$this->id]['rewards'] = json_encode($rewards, JSON_NUMERIC_CHECK);
            else if (!empty($this->curTpl['reward']))
                $data[$this->id]['reward'] = $this->getField('reward', true);
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
        if (isset($this->tooltip[$this->id]))
            return $this->tooltip[$this->id];

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
            // we could show them, but the tooltips are cluttered
            if (($crt['completionFlags'] & ACHIEVEMENT_CRITERIA_FLAG_HIDDEN) && User::$perms <= 0)
                continue;

            $crtName = Util::localizedString($crt, 'name');
            switch ($crt['type'])
            {
                case ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET:
                case ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET2:
                case ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL:
                case ACHIEVEMENT_CRITERIA_TYPE_LEARN_SPELL:
                case ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL2:
                    if (!$crtName)
                        $crtName = SpellList::getName($crt['value1']);
                    break;
                case ACHIEVEMENT_CRITERIA_TYPE_OWN_ITEM:
                case ACHIEVEMENT_CRITERIA_TYPE_USE_ITEM:
                case ACHIEVEMENT_CRITERIA_TYPE_LOOT_ITEM:
                case ACHIEVEMENT_CRITERIA_TYPE_EQUIP_ITEM:
                    if (!$crtName)
                        $crtName = ItemList::getName($crt['value1']);
                    break;
                case ACHIEVEMENT_CRITERIA_TYPE_GAIN_REPUTATION:
                    if (!$crtName)
                        $crtName = FactionList::getName($crt['value1']);
                    $crtName .= ' ('.Lang::getReputationLevelForPoints($crt['value2']).')';
                    break;
            }

            if ($crt['completionFlags'] & ACHIEVEMENT_CRITERIA_FLAG_MONEY_COUNTER)
                $criteria .= '- '.Util::jsEscape($crtName).' <span class="moneygold">'.number_format($crt['value2' ] / 10000).'</span><br />';
            else
                $criteria .= '- '.Util::jsEscape($crtName).'<br />';

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
            $x .= '<br /><span class="q">'.Lang::$achievement['criteria'].':</span>';
            $x .= '<table width="100%"><tr><td class="q0" style="white-space: nowrap"><small>'.$criteria.'</small></th></tr></table>';
        }
        if ($description || $criteria)
            $x .= '</td></tr></table>';

        // Completed
        $this->tooltip[$this->id] = $x;

        return $this->tooltip[$this->id];
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
    // cr => [type, field, misc, extraCol]
    protected $genericFilter = array(                       // misc (bool): _NUMERIC => useFloat; _STRING => localized; _FLAG => match Value; _BOOLEAN => stringSet
         2 => [FILTER_CR_BOOLEAN,   'reward_loc0', true      ], // givesreward
         3 => [FILTER_CR_STRING,    'reward',      true      ], // rewardtext
         7 => [FILTER_CR_BOOLEAN,   'series',                ], // givesreward
         9 => [FILTER_CR_NUMERIC,   'id',          null, true], // prcntbasemanarequired
        10 => [FILTER_CR_STRING,    'iconString',            ], // icon
        18 => [FILTER_CR_STAFFFLAG, 'flags',                 ], // lastrank
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
                return $this->int2Bool($cr[1]) ? ['AND', ['series', 0, '!'], [['series', 0xFFFF0000, '&'], 0]] : [['series', 0xFFFF0000, '&'], 0, '!'];
            case 6:                                         // last in series [yn]
                return $this->int2Bool($cr[1]) ? ['AND', ['series', 0, '!'], [['series', 0xFFFF, '&'], 0]] : [['series', 0xFFFF, '&'], 0, '!'];
            case 11:                                        // Related Event [enum]
/* todo */      return [1];                                 // >0:holidayId; -2323:any; -2324:none .. not quite like the subcategories
            case 14:                                        // hascomments [yn]
/* todo */      return [1];
            case 15:                                        // hasscreenshots [yn]
/* todo */      return [1];
            case 16:                                        // hasvideos [yn]
/* todo */      return [1];
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
