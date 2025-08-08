<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ItemPowerResponse extends TextResponse implements ICache
{
    use TrCache, TrTooltip;

    private const /* string */ POWER_TEMPLATE = '$WowheadPower.registerItem(%s, %d, %s);';

    protected int   $type        = Type::ITEM;
    protected int   $typeId      = 0;
    protected int   $cacheType   = CACHE_TYPE_TOOLTIP;

    protected array $expectedGET = array(
        'domain' => ['filter' => FILTER_CALLBACK, 'options' => [Locale::class, 'tryFromDomain']],
        'rand'   => ['filter' => FILTER_VALIDATE_INT                                           ],
        'ench'   => ['filter' => FILTER_VALIDATE_INT                                           ],
        'gems'   => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkIntArray']  ],
        'sock'   => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkEmptySet']  ]
    );

    public function __construct($param)
    {
        parent::__construct($param);

        // temp locale
        if ($this->_get['domain'])
            Lang::load($this->_get['domain']);

        $this->typeId = intVal($param);

        if ($this->_get['rand'])
            $this->enhancedTT['r'] = $this->_get['rand'];
        if ($this->_get['ench'])
            $this->enhancedTT['e'] = $this->_get['ench'];
        if ($this->_get['gems'])
            $this->enhancedTT['g'] = $this->_get['gems'];
        if ($this->_get['sock'])
            $this->enhancedTT['s'] = '';
    }

    protected function generate() : void
    {
        $item = new ItemList(array(['i.id', $this->typeId]));
        if ($item->error)
            $this->cacheType = CACHE_TYPE_NONE;
        else
        {
            $itemString = $this->typeId;
            foreach ($this->enhancedTT as $k => $val)
                $itemString .= $k.(is_array($val) ? implode(',', $val) : $val);

            $opts = array(
                'name'    => Lang::unescapeUISequences($item->getField('name', true, false, $this->enhancedTT), Lang::FMT_RAW),
                'tooltip' => $item->renderTooltip(enhance: $this->enhancedTT),
                'icon'    => $item->getField('iconString'),
                'quality' => $item->getField('quality')
            );
        }

        $this->result = new Tooltip(self::POWER_TEMPLATE, $itemString ?? $this->typeId, $opts ?? []);
    }
}

?>
