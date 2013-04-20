<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class QuestList extends BaseType
{
    public    $cat1       = 0;
    public    $cat2       = 0;

    protected $setupQuery = 'SELECT *, id AS ARRAY_KEY FROM quest_template a LEFT JOIN locales_quest b ON a.Id = b.entry WHERE [filter] [cond] ORDER BY Id ASC';
    protected $matchQuery = 'SELECT COUNT(1) FROM quest_template a LEFT JOIN locales_quest b ON a.Id = b.entry WHERE [filter] [cond]';

    // parent::__construct does the job

    public function iterate($qty = 1)
    {
        $r = parent::iterate($qty);

        if (!$this->id)
        {
            $this->cat1 = 0;
            $this->cat2 = 0;
        }
        else
        {
            $this->cat1 = $this->curTpl['ZoneOrSort'];  // should probably be in a method...
            foreach (Util::$questClasses as $k => $arr)
            {
                if (in_array($this->cat1, $arr))
                {
                    $this->cat2 = $k;
                    break;
                }
            }
        }

        return $r;
    }

    // static use START
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
    // static use END

    public function getSourceData()
    {
        $data = [];

        while ($this->iterate())
        {
            $data[$this->id] = array(
                "n"  => Util::localizedString($this->curTpl, 'Title'),
                "t"  => TYPE_QUEST,
                "ti" => $this->id,
                "c"  => $this->cat1,
                "c2" => $this->cat2
            );
        }

        return $data;
    }

    public function getListviewData()
    {
        $data = [];

        while ($this->iterate())
        {
            $data[$this->id] = array(
                'category'  => $this->cat1,
                'category2' => $this->cat2,
                'id'        => $this->id,
                'level'     => $this->curTpl['Level'],
                'reqlevel'  => $this->curTpl['MinLevel'],
                'name'      => Util::localizedString($this->curTpl, 'Title'),
                'side'      => Util::sideByRaceMask($this->curTpl['RequiredRaces'])
            );

            $rewards = [];
            for ($i = 1; $i < 5; $i++)
                if ($this->curTpl['RewardItemId'.$i])
                    $rewards[] = [$this->curTpl['RewardItemId'.$i], $this->curTpl['RewardItemCount'.$i]];

            $choices = [];
            for ($i = 1; $i < 7; $i++)
                if ($this->curTpl['RewardChoiceItemId'.$i])
                    $choices[] = [$this->curTpl['RewardChoiceItemId'.$i], $this->curTpl['RewardChoiceItemCount'.$i]];

            if ($rewards)
                $data[$this->id]['itemrewards'] = $rewards;

            if ($choices)
                $data[$this->id]['itemchoices'] = $choices;

            if ($this->curTpl['RewardTitleId'])
                $data[$this->id]['titlereward'] = $this->curTpl['RewardTitleId'];

            // todo reprewards .. accesses QuestFactionReward.dbc
        }

        return $data;
    }

    public function addRewardsToJscript(&$refs)
    {
        $items  = [];
        $spells = [];
        $titles = [];

        while ($this->iterate())
        {
            // items
            for ($i = 1; $i < 5; $i++)
                if ($this->curTpl['RewardItemId'.$i] > 0)
                    $items[] = $this->curTpl['RewardItemId'.$i];

            for ($i = 1; $i < 7; $i++)
                if ($this->curTpl['RewardChoiceItemId'.$i] > 0)
                    $items[] = $this->curTpl['RewardChoiceItemId'.$i];

            // spells
            if ($this->curTpl['RewardSpell'] > 0)
                $spells[] = $this->curTpl['RewardSpell'];

            if ($this->curTpl['RewardSpellCast'] > 0)
                $spells[] = $this->curTpl['RewardSpellCast'];

            // titles
            if ($this->curTpl['RewardTitleId'] > 0)
                $titles[] = $this->curTpl['RewardTitleId'];
        }

        if ($items)
            (new ItemList(array(['i.entry', $items])))->addGlobalsToJscript($refs);

        if ($spells)
            (new SpellList(array(['s.id', $spells])))->addGlobalsToJscript($refs);

        if ($titles)
            (new TitleList(array(['id', $titles])))->addGlobalsToJscript($refs);
    }

    public function renderTooltip()
    {
        // todo
    }

    public function addGlobalsToJScript(&$refs)
    {
        // todo
    }
}

?>
