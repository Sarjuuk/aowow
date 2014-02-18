<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class QuestList extends BaseType
{
    public static   $type      = TYPE_QUEST;
    public static   $brickFile = 'quest';

    public          $requires  = [];
    public          $rewards   = [];

    protected       $queryBase = 'SELECT *, qt.id AS ARRAY_KEY FROM quest_template qt';
    protected       $queryOpts = array(
                        'qt'        => [['lq', 'xp']],
                        'lq'        => ['j' => ['locales_quest lq ON qt.id = lq.id', true]],
                        'xp'        => ['j' => ['?_questxp xp ON qt.level = xp.id', true], 's' => ', xp.*'],
                        'goStart'   => ['j' => 'gameobject_questrelation goStart ON goStart.quest = qt.id'], // started by GO
                        'goEnd'     => ['j' => 'gameobject_involvedrelation goEnd ON goEnd.quest = qt.id'],  // ends at GO
                        'npcStart'  => ['j' => 'creature_questrelation npcStart ON npcStart.quest = qt.id'], // started by NPC
                        'npcEnd'    => ['j' => 'creature_involvedrelation npcEnd ON npcEnd.quest = qt.id'],  // ends at NPC
                        'itemStart' => ['j' => ['?_items itemStart ON itemStart.startQuest = qt.id', true], 'g' => 'qt.id']  // started by item .. grouping required, as the same quest may have multiple starter
                    );

    public function __construct($conditions = [], $applyFilter = false)
    {
        parent::__construct($conditions, $applyFilter);

        // post processing
        foreach ($this->iterate() as $id => &$_curTpl)
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

            // set xp
            $_curTpl['xp'] = $_curTpl['Field'.$_curTpl['RewardXPId']];
            for ($i = 0; $i < 9; $i++)
                unset($_curTpl['Field'.$i]);

            // todo (med): extend for reward case
            $data = [];
            for ($i = 1; $i < 7; $i++)
            {
                if ($_ = $_curTpl['RequiredItemId'.$i])
                    $data[TYPE_ITEM][] = $_;

                if ($i > 4)
                    continue;

                if ($_curTpl['RequiredNpcOrGo'.$i] > 0)
                    $data[TYPE_NPC][] = $_curTpl['RequiredNpcOrGo'.$i];
                else if ($_curTpl['RequiredNpcOrGo'.$i] < 0)
                    $data[TYPE_OBJECT][] = -$_curTpl['RequiredNpcOrGo'.$i];

                if ($_ = $_curTpl['RequiredSourceItemId'.$i])
                    $data[TYPE_ITEM][] = $_;
            }

            if ($data)
                $this->requires[$id] = $data;
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
                q.id = l.id AND
                q.id = ?d',
            $id
        );
        return Util::localizedString($n, 'title');
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

    public function getListviewData($extraFactionId = 0)    // i should formulate a propper parameter..
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
                'xp'        => $this->curTpl['xp']
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
            // wflags: &1: disabled/historical; &32: AutoAccept; &64: Hostile(?)
            if ($this->curTpl['Flags'] & 0x4000)            // Unavailable (todo (med): get disables)
            {
                $data[$this->id]['historical'] = true;      // post 5.0
                $data[$this->id]['wflags']    |= 0x1;       // pre 5.0
            }

            if ($this->curTpl['Flags'] & 0x80000)           // Auto Accept
                $data[$this->id]['wflags'] |= 0x20;

            $data[$this->id]['reprewards'] = [];
            for ($i = 1; $i < 6; $i++)
            {
                $foo = $this->curTpl['RewardFactionId'.$i];
                $bar = $this->curTpl['RewardFactionValueIdOverride'.$i] / 100;

                if (!$bar && ($_ = $this->curTpl['RewardFactionValueId'.$i]))
                    $bar = Util::$questFactionReward[abs($_)] * ($_ < 0 ? -1 : 1);

                if ($foo && $bar)
                {
                    $data[$this->id]['reprewards'][] = [$foo, $bar];

                    if ($extraFactionId == $foo)
                        $data[$this->id]['reputation'] = $bar;
                }
            }
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
            $level = sprintf(Lang::$quest['questLevel'], $level);

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


class QuestListFilter extends Filter
{
    protected $enums         = array();
    protected $genericFilter = array();

/*
        { id: 34,  name: 'availabletoplayers',      type: 'yn' },
        { id: 37,  name: 'classspecific',           type: 'classs' },
        { id: 38,  name: 'racespecific',            type: 'race' },
        { id: 27,  name: 'daily',                   type: 'yn' },
        { id: 28,  name: 'weekly',                  type: 'yn' },
        { id: 29,  name: 'repeatable',              type: 'yn' },
        { id: 30,  name: 'id',                      type: 'num', before: 'name' },
        { id: 44,  name: 'countsforloremaster_stc', type: 'yn' },
        { id: 9,   name: 'objectiveearnrepwith',    type: 'faction-any+none' },
        { id: 33,  name: 'relatedevent',            type: 'event-any+none' },
        { id: 5,   name: 'sharable',                type: 'yn' },
        { id: 11,  name: 'suggestedplayers',        type: 'num' },
        { id: 6,   name: 'timer',                   type: 'num' },
        { id: 42,  name: 'flags',               type: 'flags',  staffonly: true },
        { id: 2,   name: 'experiencegained',    type: 'num' },
        { id: 43,  name: 'currencyrewarded',    type: 'currency' },
        { id: 45,  name: 'titlerewarded',       type: 'yn' },
        { id: 23,  name: 'itemchoices',         type: 'num' },
        { id: 22,  name: 'itemrewards',         type: 'num' },
        { id: 3,   name: 'moneyrewarded',       type: 'num' },
        { id: 4,   name: 'spellrewarded',       type: 'yn' },
        { id: 1,   name: 'increasesrepwith',    type: 'faction' },
        { id: 10,  name: 'decreasesrepwith',    type: 'faction' },
        { id: 7,   name: 'firstquestseries',    type: 'yn' },
        { id: 15,  name: 'lastquestseries',     type: 'yn' },
        { id: 16,  name: 'partseries',          type: 'yn' },
        { id: 25,  name: 'hascomments',         type: 'yn' },
        { id: 18,  name: 'hasscreenshots',      type: 'yn' },
        { id: 36,  name: 'hasvideos',           type: 'yn' },

*/

    protected function createSQLForCriterium(&$cr)
    {
        if (in_array($cr[0], array_keys($this->genericFilter)))
        {
            if ($genCR = $this->genericCriterion($cr))
                return $genCR;

            unset($cr);
            $this->error = true;
            return [1];
        }

        switch ($cr[0])
        {
            case 19:                                        // startsfrom [enum]
                switch ($cr[1])
                {
                    case 1:                                 // npc
                        return ['npcStart.id', null, '!'];
                        break;
                    case 2:                                 // object
                        return ['goStart.id', null, '!'];
                        break;
                    case 3:                                 // item
                        return ['itemStart.id', null, '!'];
                }
                break;
            case 21:                                        // endsat [enum]
                switch ($cr[1])
                {
                    case 1:                                 // npc
                        return ['npcEnd.id', null, '!'];
                        break;
                    case 2:                                 // object
                        return ['goEnd.id', null, '!'];
                        break;
                }
                break;
            // case 24:                                        // lacksstartend [bool] cost an impossible amount of resources
                // if ($this->int2Bool($cr[1]))
                // {
                    // if ($cr[1])
                        // return ['OR', ['AND', ['npcStart.id', null], ['goStart.id', null], ['itemStart.id', null]], ['AND', ['npcEnd.id', null], ['goEnd.id', null]]];
                    // else
                        // return ['AND', ['OR', ['npcStart.id', null, '!'], ['goStart.id', null, '!'], ['itemStart.id', null, '!']], ['OR', ['npcEnd.id', null, '!'], ['goEnd.id', null, '!']]];
                // }
                // break;
            default:
                break;
        }

        unset($cr);
        $this->error = 1;
        return [1];
    }

    protected function createSQLForValues()
    {
        $parts = [];
        $_v    = $this->fiData['v'];

        // name
        if (isset($_v['na']))
        {
            $name        = User::$localeId ? 'title_loc'.User::$localeId      : 'title';
            $objectives  = User::$localeId ? 'objectives_loc'.User::$localeId : 'objectives';
            $description = User::$localeId ? 'details_loc'.User::$localeId    : 'details';

            if (isset($_v['ex']) && $_v['ex'] == 'on')
                $parts[] = ['OR', [$name, $_v['na']], [$objectives, $_v['na']], [$description, $_v['na']]];
            else
                $parts[] = [$name, $_v['na']];
        }

        // level min
        if (isset($_v['minle']))
        {
            if (is_int($_v['minle']) && $_v['minle'] > 0)
                $parts[] = ['level', $_v['minle'], '>='];   // not considering quests that are always at player level (-1)
            else
                unset($_v['minle']);
        }

        // level max
        if (isset($_v['maxle']))
        {
            if (is_int($_v['maxle']) && $_v['maxle'] > 0)
                $parts[] = ['level', $_v['maxle'], '<='];
            else
                unset($_v['maxle']);
        }

        // reqLevel min
        if (isset($_v['minrl']))
        {
            if (is_int($_v['minrl']) && $_v['minrl'] > 0)
                $parts[] = ['minLevel', $_v['minrl'], '>='];// ignoring maxLevel
            else
                unset($_v['minrl']);
        }

        // reqLevel max
        if (isset($_v['maxrl']))
        {
            if (is_int($_v['maxrl']) && $_v['maxrl'] > 0)
                $parts[] = ['minLevel', $_v['maxrl'], '<='];// ignoring maxLevel
            else
                unset($_v['maxrl']);
        }

        // side
        if (isset($_v['si']))
        {
            $ex    = [['requiredRaces', RACE_MASK_ALL, '&'], RACE_MASK_ALL, '!'];
            $notEx = ['OR', ['requiredRaces', 0], [['requiredRaces', RACE_MASK_ALL, '&'], RACE_MASK_ALL]];

            switch ($_v['si'])
            {
                case  3:
                    $parts[] = $notEx;
                    break;
                case  2:
                    $parts[] = ['OR', $notEx, ['requiredRaces', RACE_MASK_HORDE, '&']];
                    break;
                case -2:
                    $parts[] = ['AND', $ex,   ['requiredRaces', RACE_MASK_HORDE, '&']];
                    break;
                case  1:
                    $parts[] = ['OR', $notEx, ['requiredRaces', RACE_MASK_ALLIANCE, '&']];
                    break;
                case -1:
                    $parts[] = ['AND', $ex,   ['requiredRaces', RACE_MASK_ALLIANCE, '&']];
                    break;
                default:
                    unset($_v['si']);
            }
        }

        return $parts;
    }
}


?>
