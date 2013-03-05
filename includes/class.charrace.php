<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class CharRaceList extends BaseType
{
    protected $setupQuery = 'SELECT *, Id AS ARRAY_KEY FROM ?_races WHERE [cond] ORDER BY Id ASC';
    protected $matchQuery = 'SELECT COUNT(1) FROM ?_races WHERE [cond]';

    public function getListviewData()
    {
        $data = [];

        while ($this->iterate())
        {
            $data[$this->Id] = array(
                'Id'      => $this->Id,
                'name'    => $this->names[$this->Id],
                'classes' => $this->curTpl['classMask'],
                'faction' => $this->curTpl['factionId'],
                'leader'  => $this->curTpl['leader'],
                'zone'    => $this->curTpl['startAreaId'],
                'side'    => $this->curTpl['side']
            );

            if ($this->curTpl['expansion'])
                $data[$this->Id]['expansion'] = $this->curTpl['expansion'];
        }

        return $data;
    }

    public function addGlobalsToJscript(&$refs)
    {
        if (!isset($refs['gRaces']))
            $refs['gRaces'] = [];

        $refs['gRaces'][$this->Id] = Util::jsEscape($this->names[$this->Id]);
    }

    public function addRewardsToJScript(&$ref) { }
    public function renderTooltip() { }
}

?>
