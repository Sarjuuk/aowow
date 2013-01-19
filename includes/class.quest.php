<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class Quest extends BaseType
{
    public    $cat1       = 0;
    public    $cat2       = 0;

    protected $setupQuery = 'SELECT * FROM quest_template a LEFT JOIN locales_quest b ON a.Id = b.entry WHERE a.Id = ?';

    public function __construct($data)
    {
        parent::__construct($data);

        // post process
        $this->cat1 = $this->template['ZoneOrSort'];  // should probably be in a method...
        foreach (Util::$questClasses as $k => $arr)
        {
            if (in_array($this->cat1, $arr))
            {
                $this->cat2 = $k;
                break;
            }
        }
    }

    public static function getName($id)
    {
        $n = DB::Aowow()->SelectRow('
            SELECT
                title,
                title_loc2,
                title_loc3,
                title_loc6,
                title_loc8
            FROM
                quest_template q,
                locales_quest l
            WHERE
                q.id = l.entry AND
                q.id = ?d',
            $id
        );
        return Util::localizedString($n, 'title');
    }

    public static function RewardXP($QuestLevel, $XPId)
    {
        if ($xp = DB::Aowow()->SelectCell('SELECT Field?d FROM ?_questxp WHERE Id = ?d', $XPId, $QuestLevel)) {
            return $xp;
        }
        else
            return 0;
    }

    public function getSourceData()
    {
        return array(
            "n"  => Util::localizedString($this->template, 'Title'),
            "t"  => TYPEID_QUEST,
            "ti" => $this->Id,
            "c"  => $this->cat1,
            "c2" => $this->cat2
        );
    }

    public function getListviewData()
    {
        $data = array(
            'category'  => $this->cat1,
            'category2' => $this->cat2,
            'id'        => $this->Id,
            'level'     => $this->template['Level'],
            'reqlevel'  => $this->template['MinLevel'],
            'name'      => Util::localizedString($this->template, 'Title'),
            'side'      => Util::sideByRaceMask($this->template['RequiredRaces'])
        );

        $rewards = [];
        for ($i = 1; $i < 5; $i++)
            if ($this->template['RewardItemId'.$i])
                $rewards[] = [$this->template['RewardItemId'.$i], $this->template['RewardItemCount'.$i]];

        $choices = [];
        for ($i = 1; $i < 7; $i++)
            if ($this->template['RewardChoiceItemId'.$i])
                $choices[] = [$this->template['RewardChoiceItemId'.$i], $this->template['RewardChoiceItemCount'.$i]];

        if (!empty($rewards))
            $data['itemrewards'] = $rewards;

        if (!empty($choices))
            $data['itemchoices'] = $choices;

        if ($this->template['RewardTitleId'])
            $data['titlereward'] = $this->template['RewardTitleId'];

        // todo reprewards .. accesses QuestFactionReward.dbc

        return $data;
    }

    public function addRewardsToJscript(&$gItems, &$gSpells, &$gTitles)
    {
        // items
        $items = [];
        for ($i = 1; $i < 5; $i++)
            if ($this->template['RewardItemId'.$i])
                $items[] = $this->template['RewardItemId'.$i];

        for ($i = 1; $i < 7; $i++)
            if ($this->template['RewardChoiceItemId'.$i])
                $items[] = $this->template['RewardChoiceItemId'.$i];

        if (!empty($items))
        {
            $items = new ItemList(array(['entry', $items]));
            $items->addSelfToJScipt($gItems);
        }

        // spells
        $spells = [];
        if ($this->template['RewardSpell'])
            $spells[] = $this->template['RewardSpell'];

        if ($this->template['RewardSpellCast'])
            $spells[] = $this->template['RewardSpellCast'];

        if (!empty($spells))
        {
            $spells = new SpellList(array(['id', $spells]));
            $spells->addSelfToJScipt($gSpells);
        }

        // titles
        if ($tId = $this->template['RewardTitleId'])
        {
            $title = new Title($tId);
            $title->addSelfToJScript($gTitles);
        }
    }
}



class QuestList extends BaseTypeList
{
    protected $setupQuery = 'SELECT *, Id AS ARRAY_KEY FROM quest_template a LEFT JOIN locales_quest b ON a.Id = b.entry WHERE [filter] [cond] ORDER BY Id ASC';

    public function __construct($conditions)
    {
        // may be called without filtering
        if (class_exists('QuestFilter'))
        {
            $this->filter = new QuestFilter();
            if (($fiData = $this->filter->init()) === false)
                return;
        }

        parent::__construct($conditions);
    }

    public function addRewardsToJscript(&$gItems, &$gSpells, &$gTitles)
    {
        $items  = [];
        $spells = [];
        $titles = [];

        foreach ($this->container as $quest)
        {
            // items
            for ($i = 1; $i < 5; $i++)
                if ($quest->template['RewardItemId'.$i])
                    $items[] = $quest->template['RewardItemId'.$i];

            for ($i = 1; $i < 7; $i++)
                if ($quest->template['RewardChoiceItemId'.$i])
                    $items[] = $quest->template['RewardChoiceItemId'.$i];

            // spells
            if ($quest->template['RewardSpell'])
                $spells[] = $quest->template['RewardSpell'];

            if ($quest->template['RewardSpellCast'])
                $spells[] = $quest->template['RewardSpellCast'];

            // titles
            if ($quest->template['RewardTitleId'])
                $titles[] = $quest->template['RewardTitleId'];
        }

        if (!empty($items))
        {
            $items = new ItemList(array(['i.entry', $items]));
            $items->addSelfToJScript($gItems);
        }

        if (!empty($spells))
        {
            $spells = new SpellList(array(['id', $spells]));
            $spells->addSelfToJScript($gSpells);
        }

        if (!empty($titles))
        {
            $titles = new TitleList(array(['id', $titles]));
            $titles->addSelfToJScript($gTitles);
        }
    }
}

?>
