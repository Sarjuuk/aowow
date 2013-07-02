<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CreatureList extends BaseType
{
    use spawnHelper;

    public    $tooltips   = [];

    protected $setupQuery = 'SELECT ct.*, ct.id AS ARRAY_KEY, ft.A, ft.H, ft.factionId FROM ?_creature ct LEFT JOIN ?_factiontemplate ft ON ft.id = ct.faction_A WHERE [filter] [cond]';
    protected $matchQuery = 'SELECT COUNT(*) FROM ?_creature ct WHERE [filter] [cond]';

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
        $data = [];

        for ($i = 1; $i < 5; $i++)
            if ($_ = $this->curTpl['modelid'.$i])
                $data[] = $_;

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
                    'type'     => $this->curTpl['type']
                );

                if ($addInfoMask & NPCINFO_TAMEABLE)
                {
                    // only first skin of first model ... we're omitting potentially 11 skins here .. but the lv accepts only one .. w/e
                    $data[$this->id]['skin'] = $this->curTpl['textureString'];

                    $data[$this->id]['react'] = '['.$this->curTpl['A'].', '.$this->curTpl['H'].']';
                }
            }
        }

        ksort($data);
        return $data;
    }

    public function addGlobalsToJScript(&$refs)
    {
        if (!isset($refs['gCreatures']))
            $refs['gCreatures'] = [];

        while ($this->iterate())
            $refs['gCreatures'][$this->id] = ['name' => $this->getField('name', true)];
    }

    public function addRewardsToJScript(&$refs) { }

}

?>
