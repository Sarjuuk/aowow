<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class Achievement extends BaseType
{
    public    $criteria   = [];
    public    $tooltip    = '';

    protected $setupQuery = "SELECT * FROM ?_achievement WHERE `Id` = ?";

    public function __construct($data)
    {
        parent::__construct($data);

        // post processing
        if (!$this->template['iconString'])
            $this->template['iconString'] = 'INV_Misc_QuestionMark';
    }

    public function getListviewData()
    {
        return array(
            'id'            => $this->Id,
            'name'          => Util::localizedString($this->template, 'name'),
            'description'   => Util::localizedString($this->template, 'description'),
            'points'        => $this->template['points'],
            'faction'       => $this->template['faction'] + 1,
            'category'      => $this->template['category'],
            'parentCat'     => $this->template['parentCat'],
            'rewards'       => empty($this->template['rewards']) ? NULL : $this->template['rewards'],
            'reward'        => empty($this->template['reward_loc'.User::$localeId]) ? NULL : Util::localizedString($this->template, 'reward'),
        );
    }

    // hmm, really needed? .. probably .. needs rename? .. also probably
    public function getDetailedData()
    {
       return array(
            'id'            => $this->Id,
            'name'          => Util::localizedString($this->template, 'name'),
            'description'   => Util::localizedString($this->template, 'description'),
            'points'        => $this->template['points'],
            'iconname'      => $this->template['iconString'],
            'count'         => $this->template['reqCriteriaCount'],
            'reward'        => empty($this->template['reward_loc'.User::$localeId]) ? NULL : Util::localizedString($this->template, 'reward'),
        );
    }

    public function getCriteria($idx = -1)
    {
        if (empty($this->criteria))
        {
            $result = DB::Aowow()->Select('SELECT * FROM ?_achievementcriteria WHERE `refAchievement` = ? ORDER BY `order` ASC', $this->Id);
            if (!$result)
                return [];

            if (is_array($result[0]))
                $this->criteria = $result;
            else
                $this->criteria[] = $result;
        }

        if ($idx < 0)
            return $this->criteria;
        else
            return $this->criteria[$idx];
    }

    public function addGlobalsToJScript(&$gAchievements)
    {
        $gAchievements[$this->Id] = array(
            'icon' => $this->template['iconString'],
            'name' => Util::localizedString($this->template, 'name'),
        );
    }

    public function addRewardsToJscript(&$gItems, &$gTitles)
    {
        $rewards = explode(" ", $this->template['rewardIds']);

        $lookup = [];
        foreach ($rewards as $reward)
        {
            if ($reward > 0)
                $lookup['item'][] = $reward;
            else if ($reward < 0)
                $lookup['title'][] = -$reward;
        }

        if (isset($lookup['item']))
        {
            $rewItems = new ItemList(array(['i.entry', $lookup['item']]));
            $rewItems->addGlobalsToJScript($gItems);
        }

        if (isset($lookup['title']))
        {
            $rewTitles = new TitleList(array(['Id', $lookup['title']]));
            $rewTitles->addGlobalsToJScript($gTitles);
        }
    }

    public function createTooltip()
    {
        if (!empty($this->tooltip))
            return $this->tooltip;

        $criteria = $this->getCriteria();
        $tmp = [];
        $rows = [];
        $i = 0;
        foreach ($criteria as $_row)
        {
            if($i++ % 2)
                $tmp[] = $_row;
            else
                $rows[] = $_row;
        }
        if ($tmp)
            $rows = array_merge($rows, $tmp);

        $description = Util::localizedString($this->template, 'description');
        $name = Util::localizedString($this->template, 'name');
        $criteria = '';

        $i = 0;
        foreach ($rows as $crt)
        {
            // we could show them, but the tooltips are cluttered
            if (($crt['complete_flags'] & ACHIEVEMENT_CRITERIA_FLAG_HIDDEN) && User::$perms <= 0)
                continue;

            $crtName = Util::jsEscape(Util::localizedString($crt, 'name'));
            switch ($crt['type'])
            {
                case ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET:
                case ACHIEVEMENT_CRITERIA_TYPE_BE_SPELL_TARGET2:
                case ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL:
                case ACHIEVEMENT_CRITERIA_TYPE_LEARN_SPELL:
                case ACHIEVEMENT_CRITERIA_TYPE_CAST_SPELL2:
                    if (!$crtName)
                        $crtName = Spell::getName($crt['value1']);
                    break;
                case ACHIEVEMENT_CRITERIA_TYPE_OWN_ITEM:
                case ACHIEVEMENT_CRITERIA_TYPE_USE_ITEM:
                case ACHIEVEMENT_CRITERIA_TYPE_LOOT_ITEM:
                case ACHIEVEMENT_CRITERIA_TYPE_EQUIP_ITEM:
                    if (!$crtName)
                        $crtName = Item::getName($crt['value1']);
                    break;
                case ACHIEVEMENT_CRITERIA_TYPE_GAIN_REPUTATION:
                    if (!$crtName)
                        $crtName = Faction::getName($crt['value1']);
                    $crtName .= ' ('.Lang::getReputationLevelForPoints($crt['value2']).')';
                    break;
            }

            if ($crt['complete_flags'] & ACHIEVEMENT_CRITERIA_FLAG_MONEY_COUNTER)
                $criteria .= '- '.Util::jsEscape(htmlspecialchars($crtName)).' <span class="moneygold">'.number_format($crt['value2' ] / 10000).'</span><br />';
            else
                $criteria .= '- '.Util::jsEscape(htmlspecialchars($crtName)).'<br />';

            if (++$i == round(count($rows)/2))
                $criteria .= '</small></td><th class="q0" style="white-space: nowrap; text-align: left"><small>';
        }

        $x  = '<table><tr><td><b class="q">';
        $x .= Util::jsEscape(htmlspecialchars($name));
        $x .= '</b></td></tr></table>';
        if ($description || $criteria)
            $x .= '<table><tr><td>';

        if ($description)
            $x .= '<br />'.Util::jsEscape(htmlspecialchars($description)).'<br />';

        if ($criteria)
        {
            $x .= '<br /><span class="q">'.Lang::$achievement['criteria'].':</span>';
            $x .= '<table width="100%"><tr><td class="q0" style="white-space: nowrap"><small>'.$criteria.'</small></th></tr></table>';
        }
        if ($description || $criteria)
            $x .= '</td></tr></table>';

        // Completed
        $this->tooltip = $x;

        return $this->tooltip;
    }

    public function getSourceData()
    {
        return array(
            "n"  => Util::localizedString($this->template, 'name'),
            "s"  => $this->template['faction'],
            "t"  => TYPE_ACHIEVEMENT,
            "ti" => $this->Id
        );
    }
}



class AchievementList extends BaseTypeList
{
    protected $setupQuery = 'SELECT *, Id AS ARRAY_KEY FROM ?_achievement WHERE [filter] [cond] GROUP BY Id ORDER BY `orderInGroup` ASC';

    public function __construct($conditions)
    {
        // may be called without filtering
        if (class_exists('AchievementFilter'))
        {
            $this->filter = new AchievementFilter();
            if (($fiData = $this->filter->init()) === false)
                return;
        }

        parent::__construct($conditions);

        // post processing
        foreach ($this->container as $k => $acv)
        {
            //"rewards":[[11,137],[3,138]]   [what, entry] 3:item; 11:title, 6:spell(unused)
            if (!empty($acv->template['rewardIds']))
            {
                $rewards = [];
                $rewIds = explode(" ", $acv->template['rewardIds']);
                foreach ($rewIds as $rewId)
                    $rewards[] = ($rewId > 0 ? "[3,".$rewId."]" :  ($rewId < 0 ? "[11,".-$rewId."]" : NULL));

                $this->container[$k]->template['rewards'] = "[".implode(",",$rewards)."]";
            }
        }
    }

    public function addRewardsToJScript(&$gItems, &$gTitles)
    {
        // collect Ids to execute in single query
        $lookup = [];

        foreach ($this->container as $id => $data)
        {
            $rewards = explode(" ", $data->template['rewardIds']);

            foreach ($rewards as $reward)
            {
                if ($reward > 0)
                    $lookup['item'][] = $reward;
                else if ($reward < 0)
                    $lookup['title'][] = -$reward;
            }
        }

        if (isset($lookup['item']))
        {
            $rewItems = new ItemList(array(['i.entry', array_unique($lookup['item'])]));
            $rewItems->addGlobalsToJScript($gItems);
        }

        if (isset($lookup['title']))
        {
            $rewTitles = new TitleList(array(['Id', array_unique($lookup['title'])]));
            $rewTitles->addGlobalsToJScript($gTitles);
        }
    }

    // run once
    public function setupAchievements()
    {
        set_time_limit(120);

        // add serverside achievements
        DB::Aowow()->Query(
            "INSERT IGNORE INTO
                ?_achievement
            SELECT
                ID,
                requiredFaction,
                mapID,
                0,
                0,
                0,
                points,
                0,
                0,
                '',
                flags,
                count,
                refAchievement,
                '',
                0x10,
                CONCAT('SERVERSIDE (', ID, ')'),
                CONCAT('SERVERSIDE (', ID, ')'),
                CONCAT('SERVERSIDE (', ID, ')'),
                CONCAT('SERVERSIDE (', ID, ')'),
                CONCAT('SERVERSIDE (', ID, ')'),
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            FROM
                world.achievement_dbc"
        );

        foreach ($this->container as $acv)
        {
            // set iconString
            $icon = DB::Aowow()->SelectCell('SELECT iconname FROM ?_spellicons WHERE id = ?d', $acv->template['iconId']);

            // set parentCat
            $parentCat = DB::Aowow()->SelectCell('SELECT parentCategory FROM ?_achievementcategory WHERE Id = ?d', $acv->template['category']);

            // series parent(16) << child(16)
            $series = $acv->template['parent'] << 16;
            $series |= DB::Aowow()->SelectCell('SELECT Id FROM ?_achievement WHERE parent = ?d', $acv->Id);

            // set rewards
            $rewardIds = [];
            if ($rStr = $acv->template['reward_loc0'])
            {

                // i can haz title?
                if (stristr($rStr, 'title reward:') || stristr($rStr, 'title:'))
                {
                    $rStr = explode(':', $rStr);            // head-b-gone
                    $rStr = str_replace('The Grand' ,'Grand', $rStr);
                    $rStr = explode('.', $rStr[1]);         // Crusader + Crap
                    $rStr = explode('/', $rStr[0]);         // Matron & Patron
                    $rStr = explode(' or ', $rStr[0]);      // Alliance & Horde

                    $rewardIds[] = DB::Aowow()->SelectCell('SELECT -Id FROM ?_titles WHERE name_loc0 LIKE ?s', '%'.trim($rStr[0]).'%');
                    if (isset($rStr[1]))
                        $rewardIds[] = DB::Aowow()->SelectCell('SELECT -Id FROM ?_titles WHERE name_loc0 LIKE ?s', '%'.trim($rStr[1]).'%');
                }
                else if (stristr($rStr, 'reward:'))         // i haz item
                {
                    if (in_array($acv->Id, [3656, 3478]))   // Pilgrim
                    {
                        $rewardIds[] = -168;
                        $rewardIds[] = 44810;
                    }
                    else if (in_array($acv->Id, [1681, 1682]))  // Loremaster
                    {
                        $rewardIds[] = -125;
                        $rewardIds[] = 43300;
                    }
                    else
                    {
                        $rStr = explode(':', $rStr)[1];     // head-b-gone
                        $rewardIds[] = DB::Aowow()->SelectCell('SELECT entry FROM item_template WHERE name LIKE ?s', '%'.Util::sqlEscape(trim($rStr)));

                        if ($acv->Id == 1956)               // higher learning
                            $rewardIds[] = 44738;           // pet not in description
                    }
                }

            }
            else
                continue;

            DB::Aowow()->Query(
                'UPDATE
                    ?_achievement
                SET
                    rewardIds = ?s,
                    series = ?s,
                    parentCat = ?d,
                    iconString = ?s
                WHERE
                    Id = ?d',
                $series,
                isset($rewardIds) ? implode(' ', $rewardIds) : '',
                $parentCat,
                $icon,
                $acv->Id
            );
        }
    }
}
?>