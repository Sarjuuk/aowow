<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class CurrencyList extends BaseType
{
    protected $setupQuery = 'SELECT *, id AS ARRAY_KEY FROM ?_currencies WHERE [cond] ORDER BY Id ASC';
    protected $matchQuery = 'SELECT COUNT(1) FROM ?_currencies WHERE [cond]';

    public function getListviewData()
    {
        $data = [];

        while ($this->iterate())
        {
            $data[$this->id] = array(
                'id'        => $this->id,
                'category'  => $this->curTpl['category'],
                'name'      => $this->names[$this->id],
                'icon'      => $this->curTpl['iconString']
            );
        }

        return $data;
    }

    public function addGlobalsToJscript(&$refs)
    {
        if (!isset($refs['gCurrencies']))
            $refs['gCurrencies'] = [];

        $data = [];

        while ($this->iterate())
        {
            $refs['gCurrencies'][$this->id] = array(
                'name_'.User::$localeString => Util::jsEscape($this->names[$this->id]),
                'icon'                      => $this->curTpl['iconString']
            );
        }

        return $data;
    }

    public function addRewardsToJScript(&$ref) { }
    public function renderTooltip() { }
}

?>
