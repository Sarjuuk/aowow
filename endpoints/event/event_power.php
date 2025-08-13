<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class EventPowerResponse extends TextResponse implements ICache
{
    use TrCache, TrTooltip;

    private const /* string */ POWER_TEMPLATE = '$WowheadPower.registerHoliday(%d, %d, %s);';

    protected int   $type        = Type::WORLDEVENT;
    protected int   $typeId      = 0;
    protected int   $cacheType   = CACHE_TYPE_TOOLTIP;

    protected array $expectedGET = array(
        'domain' => ['filter' => FILTER_CALLBACK, 'options' => [Locale::class, 'tryFromDomain']]
    );

    private array $dates = [];

    public function __construct(string $id)
    {
        parent::__construct($id);

        // temp locale
        if ($this->_get['domain'])
            Lang::load($this->_get['domain']);

        $this->typeId = intVal($id);
    }

    protected function generate() : void
    {
        $worldevent = new WorldEventList(array(['id', $this->typeId]));
        if ($worldevent->error)
            $this->cacheType = CACHE_TYPE_NONE;
        else
        {
            $icon = $worldevent->getField('iconString');
            if ($icon == 'trade_engineering')
                $icon = null;

            $opts = array(
                'name'    => $worldevent->getField('name', true),
                'tooltip' => $worldevent->renderTooltip(),
                'icon'    => $icon
            );

            $this->dates = array(
                'firstDate' => $worldevent->getField('startTime'),
                'lastDate'  => $worldevent->getField('endTime'),
                'length'    => $worldevent->getField('length'),
                'rec'       => $worldevent->getField('occurence')
            );

            $this->setOnCacheLoaded([self::class, 'onBeforeDisplay'], $this->dates);
        }

        $this->result = new Tooltip(self::POWER_TEMPLATE, $this->typeId, $opts ?? []);
    }

    public static function onBeforeDisplay(string $tooltip, array $dates) : string
    {
        // update dates to now()
        WorldEventList::updateDates($dates, $start, $end);

        return sprintf(
            $tooltip,
            $start ? date(Lang::main('dateFmtLong'), $start) : null,
            $end   ? date(Lang::main('dateFmtLong'), $end)   : null
        );
    }
}

?>
