<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class CharClassList extends BaseType
{
    public static $type       = TYPE_CLASS;

    protected     $setupQuery = 'SELECT *, id AS ARRAY_KEY FROM ?_classes WHERE [cond] ORDER BY Id ASC';

    public function __construct($conditions = [])
    {
        parent::__construct($conditions);

        foreach ($this->iterate() as $k => &$_curTpl)
            if ($k == 6)                                    // todo (low): grr, move to db
                $_curTpl['hero'] = 1;
    }

    public function getListviewData()
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'id'     => $this->id,
                'name'   => $this->getField('name', true),
                'races'  => $this->curTpl['raceMask'],
                'roles'  => $this->curTpl['roles'],
                'weapon' => $this->curTpl['weaponTypeMask'],
                'armor'  => $this->curTpl['armorTypeMask'],
                'power'  => $this->curTpl['powerType'],
            );

            if ($this->curTpl['expansion'])
                $data[$this->id]['expansion'] = $this->curTpl['expansion'];
        }

        return $data;
    }

    public function addGlobalsToJscript(&$template, $addMask = 0)
    {
        foreach ($this->iterate() as $__)
            $template->extendGlobalData(self::$type, [$this->id => ['name' => $this->getField('name', true)]]);
    }

    public function addRewardsToJScript(&$ref) { }
    public function renderTooltip() { }
}

?>
