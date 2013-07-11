<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CreatureList extends BaseType
{
    use spawnHelper;

    public static $type       = TYPE_NPC;

    public        $tooltips   = [];

    protected     $setupQuery = 'SELECT ct.*, ct.id AS ARRAY_KEY, ft.A, ft.H, ft.factionId FROM ?_creature ct LEFT JOIN ?_factiontemplate ft ON ft.id = ct.faction_A WHERE [filter] [cond]';
    protected     $matchQuery = 'SELECT COUNT(*) FROM ?_creature ct WHERE [filter] [cond]';

    public static function getName($id)
    {
        $n = DB::Aowow()->SelectRow('
            SELECT
                name_loc0,
                name_loc2,
                name_loc3,
                name_loc6,
                name_loc8
            FROM
                ?_creature
            WHERE
                id = ?d',
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

    public function getRandomModelId()
    {
        // totems use hardcoded models, tauren model is base
        $totems = array(                                    // tauren => [orc, dwarf(?!), troll, tauren, draenei]
            4589 => [30758, 30754, 30762, 4589, 19074],     // fire
            4588 => [30757, 30753, 30761, 4588, 19073],     // earth
            4587 => [30759, 30755, 30763, 4587, 19075],     // water
            4590 => [30756, 30736, 30760, 4590, 19071],     // air
        );

        $data = [];

        for ($i = 1; $i < 5; $i++)
            if ($_ = $this->curTpl['displayId'.$i])
                $data[] = $_;

        if (count($data) == 1 && in_array($data[0], array_keys($totems)))
            $data = $totems[$data[0]];

        return !$data ? 0 : $data[array_rand($data)];
    }

    public function getListviewData($addInfoMask = 0x0)
    {
        /* looks like this data differs per occasion
        *
        * NPCINFO_TAMEABLE (0x1): include texture & react
        * NPCINFO_MODEL (0x2):
        */

        $data = [];

        while ($this->iterate())
        {
            if ($addInfoMask & NPCINFO_MODEL)
            {
                $texStr = strtolower($this->curTpl['textureString']);

                if (isset($data[$texStr]))
                {
                    if ($data[$texStr]['minlevel'] > $this->curTpl['minlevel'])
                        $data[$texStr]['minlevel'] = $this->curTpl['minlevel'];

                    if ($data[$texStr]['maxlevel'] < $this->curTpl['maxlevel'])
                        $data[$texStr]['maxlevel'] = $this->curTpl['maxlevel'];

                    $data[$texStr]['count']++;
                }
                else
                    $data[$texStr] = array(
                        'family'    => $this->curTpl['family'],
                        'minlevel'  => $this->curTpl['minlevel'],
                        'maxlevel'  => $this->curTpl['maxlevel'],
                        'modelId'   => $this->curTpl['modelId'],
                        'displayId' => $this->curTpl['displayId1'],
                        'skin'      => $texStr,
                        'count'     => 1
                    );
            }
            else
            {
                $data[$this->id] = array(
                    'family'   => $this->curTpl['family'],
                    'minlevel' => $this->curTpl['minlevel'],
                    'maxlevel' => $this->curTpl['maxlevel'],
                    'id'       => $this->id,
                    'boss'     => $this->curTpl['type_flags'] & 0x4,
                    'rank'     => $this->curTpl['rank'],
                    'location' => json_encode($this->getSpawns(SPAWNINFO_ZONES), JSON_NUMERIC_CHECK),
                    'name'     => $this->getField('name', true),
                    'tag'      => $this->getField('subname', true),
                    'type'     => $this->curTpl['type'],
                    'react'    => '['.$this->curTpl['A'].', '.$this->curTpl['H'].']'
                );

                if ($addInfoMask & NPCINFO_TAMEABLE)        // only first skin of first model ... we're omitting potentially 11 skins here .. but the lv accepts only one .. w/e
                    $data[$this->id]['skin'] = $this->curTpl['textureString'];
            }
        }

        ksort($data);
        return $data;
    }

    public function addGlobalsToJScript(&$template, $addMask = 0)
    {
        while ($this->iterate())
            $template->extendGlobalData(TYPE_NPC, [$this->id => ['name' => $this->getField('name', true)]]);
    }

    public function addRewardsToJScript(&$refs) { }

}

?>
