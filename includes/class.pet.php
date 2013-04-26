<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class PetList extends BaseType
{
    use ListviewHelper;

    protected $setupQuery = 'SELECT *, id AS ARRAY_KEY FROM ?_pet WHERE [cond] ORDER BY Id ASC';
    protected $matchQuery = 'SELECT COUNT(1) FROM ?_pet WHERE [cond]';

    public function getListviewData()
    {
        $data = [];

        while ($this->iterate())
        {
            $data[$this->id] = array(
                'armor'     => $this->curTpl['armor'],
                'damage'    => $this->curTpl['damage'],
                'health'    => $this->curTpl['health'],
                'diet'      => $this->curTpl['foodMask'],
                'icon'      => $this->curTpl['iconString'],
                'id'        => $this->id,
                'maxlevel'  => $this->curTpl['maxLevel'],
                'minlevel'  => $this->curTpl['minLevel'],
                'name'      => $this->getField('name', true),
                'type'      => $this->curTpl['type'],
                'exotic'    => $this->curTpl['exotic'],
                'spells'    => []
            );

            if ($this->curTpl['expansion'] > 0)
                $data[$this->id]['expansion'] = $this->curTpl['expansion'];

            for ($i = 1; $i <= 4; $i++)
                if ($this->curTpl['spellId'.$i] > 0)
                    $data[$this->id]['spells'][] = $this->curTpl['spellId'.$i];

            $data[$this->id]['spells'] = json_encode($data[$this->id]['spells'], JSON_NUMERIC_CHECK);
        }

        return $data;
    }

    public function addGlobalsToJscript(&$refs)
    {
        $gathered = [];
        while ($this->iterate())
            for ($i = 1; $i <= 4; $i++)
                if ($this->curTpl['spellId'.$i] > 0)
                    $gathered[] = (int)$this->curTpl['spellId'.$i];

        if ($gathered)
            (new SpellList(array(['s.id', $gathered])))->addGlobalsToJscript($refs);
    }

    public function addRewardsToJScript(&$ref) { }
    public function renderTooltip() { }
}

?>
