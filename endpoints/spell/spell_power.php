<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SpellPowerResponse extends TextResponse implements ICache
{
    use TrCache, TrTooltip;

    private const /* string */ POWER_TEMPLATE = '$WowheadPower.registerSpell(%d, %d, %s);';

    protected int   $type        = Type::SPELL;
    protected int   $typeId      = 0;
    protected int   $cacheType   = CACHE_TYPE_TOOLTIP;

    protected array $expectedGET = array(
        'domain' => ['filter' => FILTER_CALLBACK, 'options' => [Locale::class, 'tryFromDomain']]
    );

    public function __construct(string $param)
    {
        parent::__construct($param);

        // temp locale
        if ($this->_get['domain'])
            Lang::load($this->_get['domain']);

        $this->typeId = intVal($param);
    }

    protected function generate() : void
    {
        $spell = new SpellList([['id', $this->typeId]]);
        if ($spell->error)
            $this->cacheType = CACHE_TYPE_NONE;
        else
        {
            $tooltip = $spell->renderTooltip(ttSpells: $ttSpells);
            $buff    = $spell->renderBuff(buffSpells: $bfSpells);

            $opts = array(
                'name'       => $spell->getField('name', true),
                'icon'       => $spell->getField('iconString'),
                'tooltip'    => $tooltip,
                'spells'     => $ttSpells,
                'buff'       => $buff,
                'buffspells' => $bfSpells
            );
        }

        $this->result = new Tooltip(self::POWER_TEMPLATE, $this->typeId, $opts ?? []);
    }
}

?>
