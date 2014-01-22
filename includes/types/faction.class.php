<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class FactionList extends BaseType
{
    public static $type      = TYPE_FACTION;

    protected     $queryBase = 'SELECT f1.*, f1.id AS ARRAY_KEY, f1.parentFactionId AS cat FROM ?_factions f1';
    protected     $queryOpts = array(
                      'f1' => [['f2']],
                      'f2' => ['j' => ['?_factions f2 ON f1.parentFactionId = f2.id', true], 's' => ', IFNULL(f2.parentFactionId, 0) AS cat2']
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
            if ($_curTpl['templateIds'])
                $_curTpl['templateIds'] = explode(' ', $_curTpl['templateIds']);

            // prepare quartermaster
            if ($_curTpl['qmNpcIds'])
                $_curTpl['qmNpcIds'] = explode(' ', $_curTpl['qmNpcIds']);
        }
    }

    public static function getName($id)
    {
        $n = DB::Aowow()->SelectRow('
            SELECT
                name_loc0,
                name_loc2,
                name_loc3,
                name_loc6,
                name_loc8
            FROM
                ?_factions
            WHERE
                id = ?d',
            $id
        );
        return Util::localizedString($n, 'name');
    }

    public function getListviewData()
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'category'  => $this->curTpl['cat'],
                'category2' => $this->curTpl['cat2'],
                'expansion' => $this->curTpl['expansion'],
                'id'        => $this->id,
                'side'      => $this->curTpl['side'],
                'name'      => $this->getField('name', true)
            );
        }

        return $data;
    }

    public function addGlobalsToJScript(&$template, $addMask = 0)
    {
        foreach ($this->iterate() as $__)
            $template->extendGlobalData(self::$type, [$this->id => ['name' => $this->getField('name', true)]]);
    }

    public function renderTooltip() { }

}

?>
