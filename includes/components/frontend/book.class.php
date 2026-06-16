<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class Book implements \JsonSerializable
{
    private array $pages;

    public function __construct(
                 array  $pages,                             // js:array of html
        private  string $parent = 'book-generic',           // HTMLNode.id
        private ?int    $page = null)                       // start page; defaults to 1
    {
        if (!$parent)
            trigger_error(self::class.'::__construct - initialized without parent element', E_USER_WARNING);

        if (!$pages)
            trigger_error(self::class.'::__construct - initialized without content', E_USER_WARNING);
        else
            foreach ($pages as $p)
                $this->pages[] = UIText::format($p, Lang::FMT_HTML);
    }

    public function &iterate() : \Generator
    {
        reset($this->pages);

        foreach ($this->pages as $idx => &$page)
            yield $idx => $page;
    }

    public function jsonSerialize() : array
    {
        $result = [];

        foreach ($this as $prop => $val)
            if ($val !== null && $prop[0] != '_')
                $result[$prop] = $val;

        return $result;
    }

    public function __toString() : string
    {
        return "new Book(".Util::toJSON($this).");\n";
    }
}

?>
