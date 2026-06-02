<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CurrencyEntry extends DBTypeEntry
{
    public readonly int       $cuFlags;
    public readonly LocString $name;
    public readonly LocString $description;
    public readonly int       $category;
    public readonly int       $cap;
    public readonly int       $itemId;
    public readonly int       $iconId;
    public readonly string    $icon;

    public static int    $dbType    = Type::CURRENCY;
    public static string $brickFile = 'currency';
    public static string $dataTable = '::currencies';

    public const /* string */ QUERY_BASE = 'SELECT c.*, c.`id` AS ARRAY_KEY FROM ::currencies c';
    public const /* array  */ QUERY_OPTS = array(
        'c'  => [['ic']],
        'ic' => ['j' => ['::icons ic ON ic.`id` = c.`iconId`', true], 's' => ', ic.`name` AS "icon"']
    );

    public function applyInitData(array $initData) : void
    {
        parent::applyInitData($initData);

        $this->name        = new LocString($initData, 'name',        pruneFromSrc: true);
        $this->description = new LocString($initData, 'description', pruneFromSrc: true);

        foreach ($initData as $k => $v)
        {
            switch ($k)
            {
                case 'id':                                  // id defined by parent
                    continue 2;
                case 'icon':                                // fix missing icons
                    $this->$k = $v ?: DEFAULT_ICON;
                    break;
                default:
                    if (property_exists($this, $k))
                        $this->$k = $v;
            }
        }
    }

    public function getListviewRow(int $addInfoMask = 0x0) : array
    {
        return array(
            'id'       => $this->id,
            'category' => $this->category,
            'name'     => $this->name,
            'icon'     => $this->icon
        );
    }

    public function getJSGlobal(int $addMask = 0) : array
    {
        // todo (low): un-hardcode icon strings
        $icon = match ($this->id)
        {
            CURRENCY_HONOR_POINTS => ['pvp-currency-alliance', 'pvp-currency-horde'  ],
            CURRENCY_ARENA_POINTS => ['pvp-arenapoints-icon',  'pvp-arenapoints-icon'],
            default               => [$this->icon,             $this->icon           ]
        };

        return [self::$dbType => [$this->id => array(
            'name' => $this->name,
            'icon' => $icon
        )]];
    }

    public function renderTooltip() : ?string
    {
        if (!$this->curTpl)
            return null;

        $x  = '<table><tr><td>';
        $x .= '<b>'.$this->name.'</b><br />';

        // cata+ (or go fill it by hand)
        if (!$this->description->isEmpty())
            $x .= '<div style="max-width: 300px" class="q">'.$this->description.'</div>';

        if ($_ = $this->cap)
            $x .= '<br /><span class="q">'.Lang::currency('cap').'</span>'.Lang::nf($_).'<br />';

        $x .= '</td></tr></table>';

        return $x;
    }
}

?>
