<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class QuestList extends BaseType
{
    public static $type       = TYPE_QUEST;

    public        $cat1       = 0;
    public        $cat2       = 0;

    protected     $setupQuery = 'SELECT *, id AS ARRAY_KEY FROM quest_template qt LEFT JOIN locales_quest lq ON qt.Id = lq.entry WHERE [filter] [cond] ORDER BY Id ASC';
    protected     $matchQuery = 'SELECT COUNT(1) FROM quest_template WHERE [filter] [cond]';

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
                "n"  => $this->getField('Title', true),
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
                'name'      => $this->getField('Title', true),
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

    private function parseText($type = 'Objectives')
    {
        $replace = array(
            '$c' => '&lt;'.Util::ucFirst(Lang::$game['class']).'&gt;',
            '$C' => '&lt;'.Util::ucFirst(Lang::$game['class']).'&gt;',
            '$r' => '&lt;'.Util::ucFirst(Lang::$game['race']).'&gt;',
            '$R' => '&lt;'.Util::ucFirst(Lang::$game['race']).'&gt;',
            '$n' => '&lt;'.Util::ucFirst(Lang::$main['name']).'&gt;',
            '$N' => '&lt;'.Util::ucFirst(Lang::$main['name']).'&gt;',
            '$b' => '<br />',
            '$B' => '<br />'
        );

        $text = $this->getField($type, true);
        if (!$text)
            return '';

        $text = strtr($text, $replace);

        // gender switch
        $text = preg_replace('/$g([^:;]+):([^:;]+);/ui', '&lt;\1/\2&lt;', $text);

        // nonesense, that the client apparently ignores
        $text = preg_replace('/$t([^;]+);/ui', '', $text);

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
        while ($this->iterate())
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
