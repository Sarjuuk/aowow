<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CurrencyList extends BaseType
{
    public static $type      = TYPE_CURRENCY;
    public static $brickFile = 'currency';

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

    public function getJSGlobals($addMask = 0)
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            // todo (low): find out, why i did this in the first place
            if ($this->id == 104)                           // in case of honor commit sebbuku
                $icon = ['inv_bannerpvp_02', 'inv_bannerpvp_01']; // ['alliance', 'horde'];
            else if ($this->id == 103)                      // also arena-icon diffs from item-icon
                $icon = ['money_arena', 'money_arena'];
            else
                $icon = [$this->curTpl['iconString'], $this->curTpl['iconString']];

            $data[TYPE_CURRENCY][$this->id] = ['name' => $this->getField('name', true), 'icon' => $icon];
        }

        return $data;
    }

    public function renderTooltip() { }
}

?>
