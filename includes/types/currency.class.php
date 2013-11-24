<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CurrencyList extends BaseType
{
    public static $type      = TYPE_CURRENCY;

    protected     $queryBase = 'SELECT *, id AS ARRAY_KEY FROM ?_currencies c';

    public function getListviewData()
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'id'       => $this->id,
                'category' => $this->curTpl['category'],
                'name'     => $this->getField('name', true),
                'icon'     => $this->curTpl['iconString']
            );
        }

        return $data;
    }

    public function addGlobalsToJscript(&$template, $addMask = 0)
    {
        foreach ($this->iterate() as $__)
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
