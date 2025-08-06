<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CurrencyList extends DBTypeList
{
    public static int    $type      = Type::CURRENCY;
    public static string $brickFile = 'currency';
    public static string $dataTable = '?_currencies';

    protected string $queryBase = 'SELECT c.*, c.`id` AS ARRAY_KEY FROM ?_currencies c';
    protected array  $queryOpts = array(
                        'c'  => [['ic']],
                        'ic' => ['j' => ['?_icons ic ON ic.`id` = c.`iconId`', true], 's' => ', ic.`name` AS "iconString"']
                    );

    public function __construct(array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

        foreach ($this->iterate() as &$_curTpl)
            $_curTpl['iconString'] = $_curTpl['iconString'] ?: DEFAULT_ICON;
    }


    public function getListviewData() : array
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

    public function getJSGlobals(int $addMask = 0) : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            // todo (low): un-hardcode icon strings
            $icon = match ($this->id)
            {
                CURRENCY_HONOR_POINTS => ['pvp-currency-alliance',    'pvp-currency-horde'        ],
                CURRENCY_ARENA_POINTS => ['pvp-arenapoints-icon',     'pvp-arenapoints-icon'      ],
                default               => [$this->curTpl['iconString'], $this->curTpl['iconString']]
            };

            $data[Type::CURRENCY][$this->id] = ['name' => $this->getField('name', true), 'icon' => $icon];
        }

        return $data;
    }

    public function renderTooltip() : ?string
    {
        if (!$this->curTpl)
            return null;

        $x  = '<table><tr><td>';
        $x .= '<b>'.$this->getField('name', true).'</b><br />';

        // cata+ (or go fill it by hand)
        if ($_ = $this->getField('description', true))
            $x .= '<div style="max-width: 300px" class="q">'.$_.'</div>';

        if ($_ = $this->getField('cap'))
            $x .= '<br /><span class="q">'.Lang::currency('cap').Lang::main('colon').'</span>'.Lang::nf($_).'<br />';

        $x .= '</td></tr></table>';

        return $x;
    }
}

?>
