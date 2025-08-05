<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class LocString
{
    private \WeakMap $store;

    public function __construct(array $data, string $key = 'name', ?callable $callback = null)
    {
        $this->store = new \WeakMap();

        $callback ??= fn($x) => $x;

        if (!array_filter($data, fn($v, $k) => $v && strstr($k, $key.'_loc'), ARRAY_FILTER_USE_BOTH))
            trigger_error('LocString - is entrirely empty', E_USER_WARNING);

        foreach (Locale::cases() as $l)
            if ($l->validate())
                $this->store[$l] = (string)$callback($data[$key.'_loc'.$l->value] ?? '');
    }

    public function __toString() : string
    {
        if ($str = $this->store[Lang::getLocale()])
            return $str;

        foreach (Locale::cases() as $l)                // desired loc not set, use any other
            if (isset($this->store[$l]))
                return Cfg::get('DEBUG') ? '['.$this->store[$l].']' : $this->store[$l];

        return Cfg::get('DEBUG') ? '[LOCSTRING]' : '';
    }

    public function __serialize(): array
    {
        $data = [];
        foreach (Locale::cases() as $l)
            if (isset($this->store[$l]))
                $data[$l->value] = $this->store[$l];

        return ['store' => $data];
    }

    public function __unserialize(array $data): void
    {
        $this->store = new \WeakMap();

        if (empty($data['store']))
            return;

        foreach ($data['store'] as $locId => $str)
            if (($l = Locale::tryFrom($locId))?->validate())
                $this->store[$l] = (string)$str;
    }
}

?>
