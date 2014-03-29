<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CharRaceList extends BaseType
{
    public static $type      = TYPE_RACE;
    public static $brickFile = 'race';

    protected     $queryBase = 'SELECT *, id AS ARRAY_KEY FROM ?_races r';

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

    public function addGlobalsToJScript($addMask = 0)
    {
        foreach ($this->iterate() as $__)
            Util::$pageTemplate->extendGlobalData(self::$type, [$this->id => ['name' => $this->getField('name', true)]]);
    }

    public function addRewardsToJScript(&$ref) { }
    public function renderTooltip() { }
}

?>
