<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class QuestPowerResponse extends TextResponse implements ICache
{
    use TrCache, TrTooltip;

    private const /* string */ POWER_TEMPLATE = '$WowheadPower.registerQuest(%d, %d, %s);';

    protected int   $type        = Type::QUEST;
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
        $quest = new QuestList(array(['id', $this->typeId]));
        if ($quest->error)
            $this->cacheType = CACHE_TYPE_NONE;
        else
            $opts = array(
                'name'    => Lang::unescapeUISequences($quest->getField('name', true), Lang::FMT_RAW),
                'tooltip' => $quest->renderTooltip(),
                'daily'   => $quest->isDaily() ? 1 : null
            );

        $this->result = new Tooltip(self::POWER_TEMPLATE, $this->typeId, $opts ?? []);
    }
}

?>
