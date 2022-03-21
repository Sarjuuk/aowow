<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class FactionList extends BaseType
{
    public static   $type      = Type::FACTION;
    public static   $brickFile = 'faction';
    public static   $dataTable = '?_factions';

    protected       $queryBase = 'SELECT f.*, f.parentFactionId AS cat, f.id AS ARRAY_KEY FROM ?_factions f';
    protected       $queryOpts = array(
                        'f'  => [['f2']],
                        'f2' => ['j' => ['?_factions f2 ON f.parentFactionId = f2.id', true], 's' => ', IFNULL(f2.parentFactionId, 0) AS cat2'],
                        'ft' => ['j' => '?_factiontemplate ft ON ft.factionId = f.id']
                    );

    public function __construct($conditions = [])
    {
        parent::__construct($conditions);

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

    public static function getName($id)
    {
        $n = DB::Aowow()->SelectRow('SELECT name_loc0, name_loc2, name_loc3, name_loc4, name_loc6, name_loc8 FROM ?_factions WHERE id = ?d', $id);
        return Util::localizedString($n, 'name');
    }

    public function getListviewData()
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

    public function getJSGlobals($addMask = 0)
    {
        $data = [];

        foreach ($this->iterate() as $__)
            $data[Type::FACTION][$this->id] = ['name' => $this->getField('name', true)];

        return $data;
    }

    public function renderTooltip() { }

}

?>
