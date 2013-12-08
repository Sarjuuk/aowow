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
            if ($this->id == 104)                           // in case of honor commit sebbuku
                $icon = ['alliance-icon', 'horde-icon'];
            else if ($this->id == 103)                      // also arena-icon diffs from item-icon
                $icon = ['money_arena', 'money_arena'];
            else
                $icon = [$this->curTpl['iconString'], $this->curTpl['iconString']];

            $template->extendGlobalData(self::$type, [$this->id => array(
                'name' => $this->getField('name', true),
                'icon' => $icon
            )]);
        }
    }

    public function renderTooltip() { }
}

?>
