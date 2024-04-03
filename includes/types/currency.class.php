<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CurrencyList extends BaseType
{
    public static   $type      = Type::CURRENCY;
    public static   $brickFile = 'currency';
    public static   $dataTable = '?_currencies';

    protected       $queryBase = 'SELECT c.*, c.id AS ARRAY_KEY FROM ?_currencies c';
    protected       $queryOpts = array(
                        'c' => [['ic']],
                        'ic' => ['j' => ['?_icons ic ON ic.id = c.iconId', true], 's' => ', ic.name AS iconString']
                    );

    public function __construct($conditions = [])
    {
        parent::__construct($conditions);

        foreach ($this->iterate() as &$_curTpl)
            $_curTpl['iconString'] = $_curTpl['iconString'] ?: DEFAULT_ICON;
    }


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
                $icon = ['pvp-currency-alliance', 'pvp-currency-horde'];
            else if ($this->id == 103)                      // also arena-icon diffs from item-icon
                $icon = ['pvp-arenapoints-icon', 'pvp-arenapoints-icon'];
            else
                $icon = [$this->curTpl['iconString'], $this->curTpl['iconString']];

            $data[Type::CURRENCY][$this->id] = ['name' => $this->getField('name', true), 'icon' => $icon];
        }

        return $data;
    }

    public function renderTooltip()
    {
        if (!$this->curTpl)
            return array();

        $x  = '<table><tr><td>';
        $x .= '<b>'.$this->getField('name', true).'</b><br>';

        // cata+ (or go fill it by hand)
        if ($_ = $this->getField('description', true))
            $x .= '<div style="max-width: 300px" class="q">'.$_.'</div>';

        if ($_ = $this->getField('cap'))
            $x .= '<br><span class="q">'.Lang::currency('cap').Lang::main('colon').'</span>'.Lang::nf($_).'<br>';

        $x .= '</td></tr></table>';

        return $x;
    }
}

?>
