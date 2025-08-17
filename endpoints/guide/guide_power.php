<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GuidePowerResponse extends TextResponse implements ICache
{
    use TrCache, TrTooltip;

    private const /* string */ POWER_TEMPLATE = '$WowheadPower.registerGuide(%s, %d, %s);';

    protected int   $type        = Type::GUIDE;
    protected int   $typeId      = 0;
    protected int   $cacheType   = CACHE_TYPE_TOOLTIP;

    protected array $expectedGET = array(
        'domain' => ['filter' => FILTER_CALLBACK, 'options' => [Locale::class, 'tryFromDomain']]
    );

    private string $url = '';

    public function __construct(string $idOrName)
    {
        parent::__construct($idOrName);

        // temp locale
        if ($this->_get['domain'])
            Lang::load($this->_get['domain']);

        if (Util::checkNumeric($idOrName, NUM_CAST_INT))
            $this->typeId = $idOrName;
        else if ($id = DB::Aowow()->selectCell('SELECT `id` FROM ?_guides WHERE `url` = ?', Util::lower($idOrName)))
        {
            $this->typeId = intVal($id);
            $this->url    = Util::lower($idOrName);
        }
    }

    protected function generate() : void
    {
        $opts = [];
        if ($this->typeId)
            if (!($guide = new GuideList(array(['id', $this->typeId])))->error)
                $opts = array(
                    'name'    => $guide->getField('name', true),
                    'tooltip' => $guide->renderTooltip()
                );

        if (!$opts)
            $this->cacheType = CACHE_TYPE_NONE;

        $this->result = new Tooltip(self::POWER_TEMPLATE, $this->url ?: $this->typeId, $opts);
    }
}

?>
