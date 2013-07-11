<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class PetList extends BaseType
{
    use ListviewHelper;

    public    static $type = TYPE_PET;

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

    public function addGlobalsToJscript(&$template, $addMask = GLOBALINFO_ANY)
    {
        while ($this->iterate())
        {
            if ($addMask & GLOBALINFO_RELATED)
                for ($i = 1; $i <= 4; $i++)
                    if ($this->curTpl['spellId'.$i] > 0)
                        $template->extendGlobalIds(TYPE_SPELL, $this->curTpl['spellId'.$i]);

            if ($addMask & GLOBALINFO_SELF)
                $template->extendGlobalData(self::$type, [$this->id => ['icon' => $this->curTpl['iconString']]]);
        }
    }

    public function renderTooltip() { }
}

?>
