<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class NpcPowerResponse extends TextResponse implements ICache
{
    use TrCache, TrTooltip;

    private const /* string */ POWER_TEMPLATE = '$WowheadPower.registerNpc(%d, %d, %s);';

    protected int   $type        = Type::NPC;
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
        $creature = new CreatureList(array(['id', $this->typeId]));
        if ($creature->error)
            $this->cacheType = CACHE_TYPE_NONE;
        else
            $opts = array(
                'name'    => $creature->getField('name', true),
                'tooltip' => $creature->renderTooltip(),
                'map'     => $creature->getSpawns(SPAWNINFO_SHORT)
            );

        $this->result = new Tooltip(self::POWER_TEMPLATE, $this->typeId, $opts ?? []);
    }
}

?>
