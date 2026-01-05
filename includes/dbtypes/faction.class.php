<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class FactionList extends DBTypeList
{
    public static int    $type      = Type::FACTION;
    public static string $brickFile = 'faction';
    public static string $dataTable = '::factions';

    protected string $queryBase = 'SELECT f.*, f.`parentFactionId` AS "cat", f.`id` AS ARRAY_KEY FROM ::factions f';
    protected array  $queryOpts = array(
                        'f'  => [['f2']],
                        'f2' => ['j' => ['::factions f2 ON f.`parentFactionId` = f2.`id`', true], 's' => ', IFNULL(f2.`parentFactionId`, 0) AS "cat2"'],
                        'ft' => ['j' => '::factiontemplate ft ON ft.`factionId` = f.`id`']
                    );

    public function __construct(array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

        if ($this->error)
            return;

        // post processing
        foreach ($this->iterate() as &$_curTpl)
        {
            // prepare factionTemplates
            $_curTpl['templateIds'] = $_curTpl['templateIds'] ? explode(' ', $_curTpl['templateIds']) : [];

            // prepare quartermaster
            $_curTpl['qmNpcIds'] = $_curTpl['qmNpcIds'] ? explode(' ', $_curTpl['qmNpcIds']) : [];
        }
    }

    public function getListviewData() : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'expansion' => $this->curTpl['expansion'],
                'id'        => $this->id,
                'side'      => $this->curTpl['side'],
                'name'      => $this->getField('name', true)
            );

            if ($this->curTpl['cat2'])
            {
                $data[$this->id]['category']  = $this->curTpl['cat'];
                $data[$this->id]['category2'] = $this->curTpl['cat2'];
            }
            else
            {
                $data[$this->id]['category']  = $this->curTpl['cat2'];
                $data[$this->id]['category2'] = $this->curTpl['cat'];
            }

        }

        return $data;
    }

    public function getJSGlobals(int $addMask = 0) : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
            $data[Type::FACTION][$this->id] = ['name' => $this->getField('name', true)];

        return $data;
    }

    public function renderTooltip() : ?string { return null; }

}

?>
