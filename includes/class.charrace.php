<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class CharRaceList extends BaseType
{
    protected $setupQuery = 'SELECT *, id AS ARRAY_KEY FROM ?_races WHERE [cond] ORDER BY Id ASC';
    protected $matchQuery = 'SELECT COUNT(1) FROM ?_races WHERE [cond]';

    public function getListviewData()
    {
        $data = [];

        while ($this->iterate())
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

    public function addGlobalsToJscript(&$refs)
    {
        if (!isset($refs['gRaces']))
            $refs['gRaces'] = [];

        while ($this->iterate())
            $refs['gRaces'][$this->id] = ['name' => $this->getField('name', true)];
    }

    public function addRewardsToJScript(&$ref) { }
    public function renderTooltip() { }
}

?>
