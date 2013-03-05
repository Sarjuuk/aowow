<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class CharClassList extends BaseType
{
    protected $setupQuery = 'SELECT *, Id AS ARRAY_KEY FROM ?_classes WHERE [cond] ORDER BY Id ASC';
    protected $matchQuery = 'SELECT COUNT(1) FROM ?_classes WHERE [cond]';

    public function getListviewData()
    {
        $data = [];

        while ($this->iterate())
        {
            $data[$this->Id] = array(
                'Id'     => $this->Id,
                'name'   => $this->names[$this->Id],
                'races'  => $this->curTpl['raceMask'],
                'roles'  => $this->curTpl['roles'],
                'weapon' => $this->curTpl['weaponTypeMask'],
                'armor'  => $this->curTpl['armorTypeMask'],
                'power'  => $this->curTpl['powerType'],
            );

            if ($this->curTpl['expansion'] == 2)            // todo: grr, move to db
                $data[$this->Id]['hero'] = 1;

            if ($this->curTpl['expansion'])
                $data[$this->Id]['expansion'] = $this->curTpl['expansion'];
        }

        return $data;
    }

    public function addGlobalsToJscript(&$refs)
    {
        if (!isset($refs['gClasses']))
            $refs['gClasses'] = [];

        while ($this->iterate())
            $refs['gClasses'][$this->Id] = Util::jsEscape($this->names[$this->Id]);
    }

    public function addRewardsToJScript(&$ref) { }
    public function renderTooltip() { }
}

?>
