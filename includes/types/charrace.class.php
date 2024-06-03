<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CharRaceList extends BaseType
{
    public static   $type      = Type::CHR_RACE;
    public static   $brickFile = 'race';
    public static   $dataTable = '?_races';

    protected       $queryBase = 'SELECT r.*, id AS ARRAY_KEY FROM ?_races r';

    public function getListviewData()
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'id'      => $this->id,
                'name'    => $this->getField('name', true),
                'classes' => $this->curTpl['classMask'],
                'faction' => $this->curTpl['factionId'],
                'leader'  => $this->curTpl['leader'],
                'zone'    => $this->curTpl['startAreaId'],
                'side'    => $this->curTpl['side']
            );

            if ($this->curTpl['expansion'])
                $data[$this->id]['expansion'] = $this->curTpl['expansion'];
        }

        return $data;
    }

    public function getJSGlobals($addMask = 0)
    {
        $data = [];

        foreach ($this->iterate() as $__)
            $data[Type::CHR_RACE][$this->id] = ['name' => $this->getField('name', true)];

        return $data;
    }

    public function renderTooltip() { }
}

?>
