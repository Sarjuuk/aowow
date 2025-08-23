<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class InfoboxMarkup extends Markup
{
    public function __construct(private array $items, array $opts, string $parent = '')
    {
        parent::__construct('', $opts, $parent);
    }

    public function addItem(string $item, ?int $pos = null) : void
    {
        if (is_null($pos) || $pos >= count($this->items))
            $this->items[] = $item;
        else
            array_splice($this->items, $pos, 0, $item);
    }

    public function append(string $text) : self
    {
        if ($this->items && !$this->__text)
            $this->replace('[ul][li]' . implode('[/li][li]', $this->items) . '[/li][/ul]');

        return parent::append($text);
    }

    public function __toString() : string
    {
        if ($this->items && !$this->__text)
            $this->replace('[ul][li]' . implode('[/li][li]', $this->items) . '[/li][/ul]');

        return parent::__toString();
    }

    public function getJsGlobals() : array
    {
        if ($this->items && !$this->__text)
            $this->replace('[ul][li]' . implode('[/li][li]', $this->items) . '[/li][/ul]');

        return parent::getJsGlobals();
    }
}

?>
