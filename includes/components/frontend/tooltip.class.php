<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class Tooltip implements \JsonSerializable
{
    private ?string    $name       = null;
    private ?string    $tooltip    = null;
    private ?string    $tooltip2   = null;
    private ?\StdClass $map        = null;                  // secondary tooltip
    private ?string    $icon       = null;
    private ?int       $quality    = null;                  // icon border color coded
    private ?bool      $daily      = null;
    private ?array     $spells     = null;
    private ?string    $buff       = null;
    private ?array     $buffspells = null;

    public function __construct(private string $__powerTpl, private int|string $__subject, array $opts = [])
    {
        if (!is_int($this->__subject))
            $this->__subject = Util::toJSON($this->__subject, JSON_UNESCAPED_UNICODE);

        foreach ($opts as $k => $v)
        {
            if (property_exists($this, $k))
                $this->$k = $v;
            else
                trigger_error(self::class.'::__construct - unrecognized option: ' . $k);
        }
    }

    public function jsonSerialize() : array
    {
        $out = [];

        $locString = Lang::getLocale()->json();

        foreach ($this as $k => $v)
        {
            if ($v === null || $k[0] == '_')
                continue;

            if ($k == 'icon')
                $out[$k] = rawurldecode($v);
            else if ($k == 'quality' || $k == 'map' || $k == 'daily')
                $out[$k] = $v;
            else
                $out[$k . '_' . $locString] = $v;
        }

        return $out;
    }

    public function __toString() : string
    {
        return sprintf($this->__powerTpl, $this->__subject, Lang::getLocale()->value, Util::toJSON($this, JSON_AOWOW_POWER))."\n";
    }
}

?>
