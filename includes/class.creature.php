<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CreatureList extends BaseType
{
    use spawnHelper;

    public    $tooltips   = [];

    protected $setupQuery = 'SELECT *, ct.entry AS ARRAY_KEY, ct.entry AS id FROM creature_template ct LEFT JOIN locales_creature lc ON lc.entry = ct.entry LEFT JOIN creature_template_addon cta on cta.entry = ct.entry WHERE [filter] [cond]';
    protected $matchQuery = 'SELECT COUNT(*) FROM creature_template ct WHERE [filter] [cond]';

    public static function getName($id)
    {
        $n = DB::Aowow()->SelectRow('
            SELECT
                name,
                name_loc2,
                name_loc3,
                name_loc6,
                name_loc8
            FROM
                creature_template ct
            LEFT JOIN
                locales_creature lc
            ON
                lc.entry = ct.entry
            WHERE
                ct.entry = ?d',
            $id
        );
        return Util::localizedString($n, 'name');
    }

    public function renderTooltip()
    {
        if (!$this->curTpl)
            return null;

        if (isset($this->tooltips[$this->id]))
            return $this->tooltips[$this->id];

        $level = '??';
        $type  = $this->curTpl['type'];
        $row3  = [Lang::$game['level']];
        $fam   = $this->curTpl['family'];
        // todo (low): rework, when factions are implemented
        $fac   = DB::Aowow()->selectRow('SELECT * FROM dbc.faction f JOIN dbc.factionTemplate ft ON f.id = ft.factionId WHERE ft.id = ?d AND NOT f.reputationFlags1 & 0x4 AND f.reputationIndex <> -1', $this->curTpl['faction_A']);

        if (!($this->curTpl['type_flags'] & 0x4))
        {
            $level = $this->curTpl['minlevel'];
            if ($level != $this->curTpl['maxlevel'])
                $level .= ' - '.$this->curTpl['maxlevel'];
        }
        $row3[] = $level;

        if ($type)
            $row3[] = Lang::$game['ct'][$type];

        $row3[] = '('.Lang::$npc['rank'][$this->curTpl['rank']].')';

        $x  = '<table>';
        $x .= '<tr><td><b class="q">'.$this->getField('name', true).'</b></td></tr>';

        if ($sn = $this->getField('subname', true))
            $x .= '<tr><td>'.$sn.'</td></tr>';

        $x .= '<tr><td>'.implode(' ', $row3).'</td></tr>';

        if ($type == 1 && $fam)                             // 1: Beast
            $x .= '<tr><td>'.Lang::$game['fa'][$fam].'</td></tr>';

        if ($fac)
            $x .= '<tr><td>'.Util::localizedString($fac, 'name').'</td></tr>';

        $x .= '</table>';

        $this->tooltips[$this->id] = $x;

        return $x;
    }

    public function getListviewData() { }
    public function addGlobalsToJScript(&$refs) { }
    public function addRewardsToJScript(&$refs) { }

}

?>
