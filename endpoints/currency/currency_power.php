<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CurrencyPowerResponse extends TextResponse implements ICache
{
    use TrTooltip, TrCache;

    private const /* string */ POWER_TEMPLATE = '$WowheadPower.registerCurrency(%d, %d, %s);';

    protected int   $type        = Type::CURRENCY;
    protected int   $typeId      = 0;
    protected int   $cacheType   = CACHE_TYPE_TOOLTIP;

    protected array $expectedGET = array(
        'domain' => ['filter' => FILTER_CALLBACK, 'options' => [Locale::class, 'tryFromDomain']]
    );

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
        $currency = new CurrencyList(array(['id', $this->typeId]));
        if ($currency->error)
            $this->cacheType = CACHE_TYPE_NONE;
        else
            $opts = array(
                'name'    => $currency->getField('name', true),
                'tooltip' => $currency->renderTooltip(),
                'icon'    => $currency->getField('iconString')
            );

        $this->result = new Tooltip(self::POWER_TEMPLATE, $this->typeId, $opts ?? []);
    }
}

?>
