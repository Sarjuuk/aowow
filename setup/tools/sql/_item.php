<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ItemSetup extends ItemList
{
    private $cols = [];

    public function __construct($start, $end)               // i suggest steps of 5k at max (12 steps (0 - 60k)); otherwise eats your ram for breakfast
    {
        $this->cols = DB::Aowow()->selectCol('SELECT `COLUMN_NAME` FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`="world" AND `TABLE_NAME`="aowow_item_stats"');
        set_time_limit(300);

        $conditions = array(
            ['i.entry', $start, '>'],
            ['i.entry', $end, '<='],
            [
                'OR',
                ['class', 4],
                ['class', 2]
            ],
            0
        );

        parent::__construct($conditions);
    }

    public function writeStatsTable()
    {
        while ($this->iterate())
        {
            $this->extendJsonStats();
            $updateFields = [];

            foreach (@$this->json[$this->id] as $k => $v)
            {
                if (!in_array($k, $this->cols) || !$v)
                    continue;

                $updateFields[$k] = number_format($v, 2, ',', '');
            }

            if (isset($this->itemMods[$this->id]))
            {
                foreach ($this->itemMods[$this->id] as $k => $v)
                {
                    if (!$v)
                        continue;

                    if ($str = Util::$itemMods[$k])
                        $updateFields[$str] = number_format($v, 2, ',', '');

                }
            }

            if ($updateFields)
                DB::Aowow()->query('REPLACE INTO ?_item_stats (`itemid`, `'.implode('`, `', array_keys($updateFields)).'`) VALUES (?d, "'.implode('", "', $updateFields).'")', $this->id);
        }
    }
}

?>
