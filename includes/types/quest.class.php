<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class QuestList extends BaseType
{
    public static   $type      = TYPE_QUEST;

    protected       $queryBase = 'SELECT *, id AS ARRAY_KEY FROM quest_template qt';
    protected       $queryOpts = array(
                        'qt' => [['lq']],
                        'lq' => ['j' => ['locales_quest lq ON qt.id = lq.entry', true]]
                    );

    public function __construct($conditions = [])
    {
        parent::__construct($conditions);

        // post processing
        foreach ($this->iterate() as &$_curTpl)
        {
            $_curTpl['cat1'] = $_curTpl['ZoneOrSort'];      // should probably be in a method...
            $_curTpl['cat2'] = 0;

            foreach (Util::$questClasses as $k => $arr)
            {
                if (in_array($_curTpl['cat1'], $arr))
                {
                    $_curTpl['cat2'] = $k;
                    break;
                }
            }
        }
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

    public static function getXPReward($questLevel, $xpId)
    {
        if (!is_numeric($xpId) || $xpId < 0 || $xpId > 8)
            return 0;

        if ($xp = DB::Aowow()->selectCell('SELECT Field?d FROM ?_questxp WHERE Id = ?d', $xpId, $questLevel))
            return $xp;
        else
            return 0;
    }
    // static use END

    public function getSourceData()
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                "n"  => $this->getField('Title', true),
                "t"  => TYPE_QUEST,
                "ti" => $this->id,
                "c"  => $this->curTpl['cat1'],
                "c2" => $this->curTpl['cat2']
            );
        }

        return $data;
    }

    public function getListviewData()
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'category'  => $this->curTpl['cat1'],
                'category2' => $this->curTpl['cat2'],
                'id'        => $this->id,
                'level'     => $this->curTpl['Level'],
                'reqlevel'  => $this->curTpl['MinLevel'],
                'name'      => $this->getField('Title', true),
                'side'      => Util::sideByRaceMask($this->curTpl['RequiredRaces']),
                'wflags'    => 0x0,
                'xp'        => self::getXPReward($this->curTpl['Level'], $this->curTpl['RewardXPId'])
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

            if ($_ = $this->curTpl['RewardTitleId'])
                $data[$this->id]['titlereward'] = $_;

            if ($_ = $this->curTpl['Type'])
                $data[$this->id]['type'] = $_;

            if ($_ = $this->curTpl['RequiredClasses'])
                $data[$this->id]['reqclass'] = $_;

            if ($_ = ($this->curTpl['RequiredRaces'] & RACE_MASK_ALL))
                if ((($_ & RACE_MASK_ALLIANCE) != RACE_MASK_ALLIANCE) && (($_ & RACE_MASK_HORDE) != RACE_MASK_HORDE))
                    $data[$this->id]['reqrace'] = $_;

            if ($_ = $this->curTpl['RewardOrRequiredMoney'])
                if ($_ > 0)
                    $data[$this->id]['money'] = $_;

            if ($this->curTpl['Flags'] & 0x1000)
                $data[$this->id]['daily'] = true;

            if ($this->curTpl['Flags'] & 0x8000)
                $data[$this->id]['weekly'] = true;

            // flags & 64: Hostile - there are quests, that flag the player for pvp when taken .. where is that set..?
            if ($this->curTpl['Flags'] & 0x4000)            // Unavailable (todo (med): get disables)
            {
                $data[$this->id]['historical'] = true;      // post 5.0
                $data[$this->id]['wflags']    |= 0x1;       // pre 5.0
            }

            if ($this->curTpl['Flags'] & 0x80000)           // Auto Accept
                $data[$this->id]['wflags'] |= 0x20;

            // todo reprewards .. accesses QuestFactionReward.dbc
        }

        return $data;
    }

    private function parseText($type = 'Objectives')
    {
        $text = $this->getField($type, true);
        if (!$text)
            return '';

        $text = Util::parseHtmlText($text);

        return Util::jsEscape($text);
    }

    public function renderTooltip()
    {
        if (!$this->curTpl)
            return null;

        if (isset($this->tooltips[$this->id]))
            return $this->tooltips[$this->id];

        $title = Util::jsEscape($this->getField('Title', true));
        $level = $this->curTpl['Level'];
        if ($level < 0)
            $level = 0;

        $x = '';
        if ($level)
        {
            $level = sprintf(Lang::$quest['level'], $level);

            if ($this->curTpl['Flags'] & 0x1000)                // daily
                $level .= ' '.Lang::$quest['daily'];

            $x .= '<table><tr><td><table width="100%"><tr><td><b class="q">'.$title.'</b></td><th><b class="q0">'.$level.'</b></th></tr></table></td></tr></table>';
        }
        else
            $x .= '<table><tr><td><b class="q">'.$title.'</b></td></tr></table>';


        $x .= '<table><tr><td><br />'.$this->parseText('Objectives').'<br /><br /><span class="q">'.Lang::$quest['requirements'].Lang::$colon.'</span>';


        for ($i = 1; $i < 5; $i++)
        {
            $ot     = $this->getField('ObjectiveText'.$i, true);
            $rng    = $this->curTpl['RequiredNpcOrGo'.$i];
            $rngQty = $this->curTpl['RequiredNpcOrGoCount'.$i];

            if ($rngQty < 1 && (!$rng || $ot))
                continue;

            if ($ot)
                $name = $ot;
            else
                $name = $rng > 0 ? CreatureList::getName($rng) : GameObjectList::getName(-$rng);

            $x .= '<br /> - '.Util::jsEscape($name).($rngQty > 1 ? ' x '.$rngQty : null);
        }

        for ($i = 1; $i < 7; $i++)
        {
            $ri    = $this->curTpl['RequiredItemId'.$i];
            $riQty = $this->curTpl['RequiredItemCount'.$i];

            if (!$ri || $riQty < 1)
                continue;

            $x .= '<br /> - '.Util::jsEscape(ItemList::getName($ri)).($riQty > 1 ? ' x '.$riQty : null);
        }

        if ($et = $this->getField('EndText', true))
            $x .= '<br /> - '.$et;

        $x .= '</td></tr></table>';

        $this->tooltips[$this->id] = $x;

        return $x;
    }

    public function addGlobalsToJScript(&$template, $addMask = GLOBALINFO_ANY)
    {
        foreach ($this->iterate() as $__)
        {
            if ($addMask & GLOBALINFO_REWARDS)
            {
                // items
                for ($i = 1; $i < 5; $i++)
                    if ($this->curTpl['RewardItemId'.$i] > 0)
                        $template->extendGlobalIds(TYPE_ITEM, $this->curTpl['RewardItemId'.$i]);

                for ($i = 1; $i < 7; $i++)
                    if ($this->curTpl['RewardChoiceItemId'.$i] > 0)
                        $template->extendGlobalIds(TYPE_ITEM, $this->curTpl['RewardChoiceItemId'.$i]);

                // spells
                if ($this->curTpl['RewardSpell'] > 0)
                    $template->extendGlobalIds(TYPE_SPELL, $this->curTpl['RewardSpell']);

                if ($this->curTpl['RewardSpellCast'] > 0)
                    $template->extendGlobalIds(TYPE_SPELL, $this->curTpl['RewardSpellCast']);

                // titles
                if ($this->curTpl['RewardTitleId'] > 0)
                    $template->extendGlobalIds(TYPE_TITLE, $this->curTpl['RewardTitleId']);
            }

            if ($addMask & GLOBALINFO_SELF)
                $template->extendGlobalData(self::$type, [$this->id => ['name' => $this->getField('Title', true)]]);
        }
    }
}

?>
