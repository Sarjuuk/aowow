<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class AchievementList extends BaseType
{
    public    $criteria   = [];
    public    $tooltip    = [];

    protected $setupQuery = 'SELECT *, id AS ARRAY_KEY FROM ?_achievement WHERE [filter] [cond] GROUP BY Id ORDER BY `orderInGroup` ASC';
    protected $matchQuery = 'SELECT COUNT(1) FROM ?_achievement WHERE [filter] [cond]';

    public function __construct($conditions)
    {
        parent::__construct($conditions);

        // post processing
        while ($this->iterate())
        {
            if (!$this->curTpl['iconString'])
                $this->templates[$this->id]['iconString'] = 'INV_Misc_QuestionMark';

            //"rewards":[[11,137],[3,138]]   [type, typeId]
            if (!empty($this->curTpl['rewardIds']))
            {
                $rewards = [];
                $rewIds  = explode(" ", $this->curTpl['rewardIds']);
                foreach ($rewIds as $rewId)
                    $rewards[] = ($rewId > 0 ? [TYPE_ITEM => $rewId] : ($rewId < 0 ? [TYPE_TITLE => -$rewId] : NULL));

                $this->templates[$this->id]['rewards'] = $rewards;
            }
        }

        $this->reset();                                     // restore 'iterator'
    }

    public function addRewardsToJScript(&$refs)
    {
        // collect Ids to execute in single query
        $lookup = [];

        while ($this->iterate())
        {
            $rewards = explode(" ", $this->curTpl['rewardIds']);

            foreach ($rewards as $reward)
            {
                if ($reward > 0)
                    $lookup['item'][] = $reward;
                else if ($reward < 0)
                    $lookup['title'][] = -$reward;
            }
        }

        if (isset($lookup['item']))
            (new ItemList(array(['i.entry', array_unique($lookup['item'])])))->addGlobalsToJscript($refs);

        if (isset($lookup['title']))
            (new TitleList(array(['id', array_unique($lookup['title'])])))->addGlobalsToJscript($refs);
    }

    public function addGlobalsToJscript(&$refs)
    {
        if (!isset($refs['gAchievements']))
            $refs['gAchievements'] = [];

        while ($this->iterate())
        {
            $refs['gAchievements'][$this->id] = array(
                'icon' => $this->curTpl['iconString'],
                'name' => Util::localizedString($this->curTpl, 'name')
            );
        }
    }

    public function getListviewData()
    {
        $data = [];

        while ($this->iterate())
        {
            $data[$this->id] = array(
                'id'            => $this->id,
                'name'          => Util::localizedString($this->curTpl, 'name'),
                'description'   => Util::localizedString($this->curTpl, 'description'),
                'points'        => $this->curTpl['points'],
                'faction'       => $this->curTpl['faction'] + 1,
                'category'      => $this->curTpl['category'],
                'parentCat'     => $this->curTpl['parentCat'],
            );

            if (!empty($this->curTpl['rewards']))
            {
                $rewards = [];

                foreach ($this->curTpl['rewards'] as $pair)
                    $rewards[] = '['.key($pair).','.current($pair).']';

                $data[$this->id]['rewards'] = '['.implode(',', $rewards).']';
            }
            else if (!empty ($this->curTpl['reward']))
                $data[$this->id]['reward'] = Util::localizedString($this->curTpl, 'reward');

        }

        return $data;
    }

    // hmm, really needed? .. probably .. needs rename? .. also probably
    public function getDetailedData()
    {
       $data = [];

        while ($this->iterate())
        {
            $data[$this->id] = array(
                'id'            => $this->id,
                'name'          => Util::localizedString($this->curTpl, 'name'),
                'description'   => Util::localizedString($this->curTpl, 'description'),
                'points'        => $this->curTpl['points'],
                'iconname'      => $this->curTpl['iconString'],
                'count'         => $this->curTpl['reqCriteriaCount'],
                'reward'        => empty($this->curTpl['reward_loc'.User::$localeId]) ? NULL : Util::localizedString($this->curTpl, 'reward')
            );
        }

        return $data;
    }

    // only for current template
    public function getCriteria($idx = -1)
    {
        if (empty($this->criteria))
        {
            $result = DB::Aowow()->Select('SELECT * FROM ?_achievementcriteria WHERE `refAchievement` = ? ORDER BY `order` ASC', $this->id);
            if (!$result)
                return [];

            if (is_array($result[0]))
                $this->criteria[$this->id] = $result;
            else
                $this->criteria[$this->id][] = $result;
        }

        if ($idx < 0)
            return $this->criteria[$this->id];
        else
            return $this->criteria[$this->id][$idx];
    }

    public function renderTooltip()
    {
        if (!empty($this->tooltip[$this->id]))
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

        $description = Util::localizedString($this->curTpl, 'description');
        $name        = Util::localizedString($this->curTpl, 'name');
        $criteria    = '';

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
        $this->tooltip[$this->id] = $x;

        return $this->tooltip[$this->id];
    }

    public function getSourceData()
    {
        $data = [];

        while ($this->iterate())
        {
            $data[$this->id] = array(
                "n"  => Util::localizedString($this->curTpl, 'name'),
                "s"  => $this->curTpl['faction'],
                "t"  => TYPE_ACHIEVEMENT,
                "ti" => $this->id
            );
        }

        return $data;
    }

    // run once .. should this even be here..?
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

        while ($this->iterate())
        {
            // set iconString
            $icon = DB::Aowow()->SelectCell('SELECT iconname FROM ?_spellicons WHERE id = ?d', $this->curTpl['iconId']);

            // set parentCat
            $parentCat = DB::Aowow()->SelectCell('SELECT parentCategory FROM ?_achievementcategory WHERE Id = ?d', $this->curTpl['category']);

            // series parent(16) << child(16)
            $series = $this->curTpl['parent'] << 16;
            $series |= DB::Aowow()->SelectCell('SELECT Id FROM ?_achievement WHERE parent = ?d', $acv->id);

            // set rewards
            $rewardIds = [];
            if ($rStr = $this->curTpl['reward_loc0'])
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
                    if (in_array($acv->id, [3656, 3478]))   // Pilgrim
                    {
                        $rewardIds[] = -168;
                        $rewardIds[] = 44810;
                    }
                    else if (in_array($acv->id, [1681, 1682]))  // Loremaster
                    {
                        $rewardIds[] = -125;
                        $rewardIds[] = 43300;
                    }
                    else
                    {
                        $rStr = explode(':', $rStr)[1];     // head-b-gone
                        $rewardIds[] = DB::Aowow()->SelectCell('SELECT entry FROM item_template WHERE name LIKE ?s', '%'.Util::sqlEscape(trim($rStr)));

                        if ($acv->id == 1956)               // higher learning
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
                $acv->id
            );
        }
    }
}

?>
