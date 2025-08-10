<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AchievementPowerResponse extends TextResponse implements ICache
{
    use TrCache, TrTooltip;

    private const /* string */ POWER_TEMPLATE = '$WowheadPower.registerAchievement(%d, %d, %s);';

    protected int   $type        = Type::ACHIEVEMENT;
    protected int   $typeId      = 0;
    protected int   $cacheType   = CACHE_TYPE_TOOLTIP;

    protected array $expectedGET = array(
        'domain' => ['filter' => FILTER_CALLBACK, 'options' => [Locale::class, 'tryFromDomain']]
    );

    public function __construct($id)
    {
        parent::__construct($id);

        // temp locale
        if ($this->_get['domain'])
            Lang::load($this->_get['domain']);

        $this->typeId = intVal($id);
    }

    protected function generate() : void
    {
        $achievement = new AchievementList(array(['id', $this->typeId]));
        if ($achievement->error)
            $this->cacheType = CACHE_TYPE_NONE;
        else
            $opts = array(
                'name'    => $achievement->getField('name', true),
                'tooltip' => $achievement->renderTooltip(),
                'icon'    => $achievement->getField('iconString')
            );

        $this->result = new Tooltip(self::POWER_TEMPLATE, $this->typeId, $opts ?? []);
    }
}

?>
