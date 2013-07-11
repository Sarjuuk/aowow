<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class CurrencyList extends BaseType
{
    public static $type       = TYPE_CURRENCY;

    protected     $setupQuery = 'SELECT *, id AS ARRAY_KEY FROM ?_currencies WHERE [cond] ORDER BY Id ASC';
    protected     $matchQuery = 'SELECT COUNT(1) FROM ?_currencies WHERE [cond]';

    public function getListviewData()
    {
        $data = [];

        while ($this->iterate())
        {
            $data[$this->id] = array(
                'id'        => $this->id,
                'category'  => $this->curTpl['category'],
                'name'      => $this->getField('name', true),
                'icon'      => $this->curTpl['iconString']
            );
        }

        return $data;
    }

    public function addGlobalsToJscript(&$template, $addMask = 0)
    {
        while ($this->iterate())
        {
            $template->extendGlobalData(self::$type, [$this->id => array(
                'name' => $this->getField('name', true),
                'icon' => $this->curTpl['iconString']
            )]);
        }
    }

    public function renderTooltip() { }
}

?>
