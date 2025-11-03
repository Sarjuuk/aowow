<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class InfoboxMarkup extends Markup
{
    public function __construct(private array $items, array $opts, string $parent = '', private int $completionRowType = 0)
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
        if ($_ = $this->prepare())
            $this->replace($_);

        return parent::append($text);
    }

    public function __toString() : string
    {
        // inject before output to avoid adding it to cache
        if ($this->completionRowType && User::getCharacters())
            $this->items[] = [Lang::profiler('completion') . '[span class="compact-completion-display"][/span]', ['style' => 'display:none']];

        if ($_ = $this->prepare())
            $this->replace($_);

        return parent::__toString();
    }

    public function getJsGlobals() : array
    {
        if ($_ = $this->prepare())
            $this->replace($_);

        return parent::getJsGlobals();
    }

    private function prepare() : string
    {
        if (!$this->items || $this->__text)
            return '';

        $buff = '';
        foreach ($this->items as $row)
        {
            if (is_array($row))
                $buff .= '[li'.Util::nodeAttributes($row[1]).']' . $row[0] . '[/li]';
            else if (is_string($row))
                $buff .= '[li]' . $row . '[/li]';
        }

        return $buff ? '[ul]'.$buff.'[/ul]' : '';
    }
}

?>
