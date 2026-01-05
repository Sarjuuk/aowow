<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class PetList extends DBTypeList
{
    use ListviewHelper;

    public static int    $type      = Type::PET;
    public static string $brickFile = 'pet';
    public static string $dataTable = '::pet';

    protected string $queryBase = 'SELECT p.*, p.`id` AS ARRAY_KEY FROM ::pet p';
    protected array  $queryOpts = array(
                        'p'  => [['ic']],
                        'ic' => ['j' => ['::icons ic ON p.`iconId` = ic.`id`', true], 's' => ', ic.`name` AS "iconString"'],
                    );

    public function getListviewData() : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'armor'    => $this->curTpl['armor'],
                'damage'   => $this->curTpl['damage'],
                'health'   => $this->curTpl['health'],
                'diet'     => $this->curTpl['foodMask'],
                'icon'     => $this->curTpl['iconString'],
                'id'       => $this->id,
                'maxlevel' => $this->curTpl['maxLevel'],
                'minlevel' => $this->curTpl['minLevel'],
                'name'     => $this->getField('name', true),
                'type'     => $this->curTpl['type'],
                'exotic'   => $this->curTpl['exotic'],
                'spells'   => []
            );

            if ($this->curTpl['expansion'] > 0)
                $data[$this->id]['expansion'] = $this->curTpl['expansion'];

            for ($i = 1; $i <= 4; $i++)
                if ($this->curTpl['spellId'.$i] > 0)
                    $data[$this->id]['spells'][] = $this->curTpl['spellId'.$i];
        }

        return $data;
    }

    public function getJSGlobals(int $addMask = GLOBALINFO_ANY) : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            if ($addMask & GLOBALINFO_RELATED)
                for ($i = 1; $i <= 4; $i++)
                    if ($this->curTpl['spellId'.$i] > 0)
                        $data[Type::SPELL][$this->curTpl['spellId'.$i]] = $this->curTpl['spellId'.$i];

            if ($addMask & GLOBALINFO_SELF)
                $data[Type::PET][$this->id] = ['icon' => $this->curTpl['iconString']];
        }

        return $data;
    }

    public function renderTooltip() : ?string { return null; }
}

?>
