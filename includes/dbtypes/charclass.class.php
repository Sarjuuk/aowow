<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CharClassList extends DBTypeList
{
    public static int    $type      = Type::CHR_CLASS;
    public static string $brickFile = 'class';
    public static string $dataTable = '?_classes';

    protected string $queryBase = 'SELECT c.*, c.`id` AS ARRAY_KEY FROM ?_classes c';
    protected array  $queryOpts = array(
                        'c'  => [['ic']],
                        'ic' => ['j' => ['?_icons ic ON ic.`id` = c.`iconId`', true], 's' => ', ic.`name` AS "iconString"']
                    );

    public function __construct($conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

        foreach ($this->iterate() as $k => &$_curTpl)
            $_curTpl['skills'] = explode(' ', $_curTpl['skills']);
    }

    public function getListviewData() : array
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

            if ($this->curTpl['flags'] & 0x40)
                $data[$this->id]['hero'] = 1;

            if ($this->curTpl['expansion'])
                $data[$this->id]['expansion'] = $this->curTpl['expansion'];
        }

        return $data;
    }

    public function getJSGlobals(int $addMask = 0) : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
            $data[self::$type][$this->id] = ['name' => $this->getField('name', true)];

        return $data;
    }

    public function renderTooltip() : ?string { return null; }
}

?>
