<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class LocString implements \JsonSerializable
{
    private \WeakMap $store;

    public function __construct(array &$data, string $key = 'name',  private ?\Closure $formatter = null, bool $pruneFromSrc = false)
    {
        $this->store = new \WeakMap();

        $this->formatter ??= self::nullFormat(...);

        // given that certain types have large initial data (250+ items), multiple strings (5+) and containers may hold 1k+ at a time this test has too much of a time cost
        // if (!array_filter($data, fn($k) => strstr($k, $key.'_loc'), ARRAY_FILTER_USE_KEY))
        //     trigger_error('LocString::__construct - data has no localized strings for key '.$key, E_USER_WARNING);

        foreach (Locale::cases() as $l)
        {
            if ($l->validate() && !empty($data[$key.'_loc'.$l->value]))
                $this->store[$l] = (string)$data[$key.'_loc'.$l->value];

            if ($pruneFromSrc)
                unset($data[$key.'_loc'.$l->value]);
        }
    }

    public function isEmpty() : bool
    {
        return count($this->store) === 0;
    }

    public function jsonSerialize() : string
    {
        return $this->__toString();
    }

    public function __invoke(Locale $l) : string
    {
        return ($this->formatter)($this->store[$l->value] ?? '');
    }

    public function __toString() : string
    {
        if ($out = ($this->store[Lang::getLocale()] ?? ''))
            return ($this->formatter)($out);

        foreach ($this->store as $l => $out)                // desired loc not set, use any other
            if ($out && $l->validate())
                return ($this->formatter)($out);

        return '';
    }

    public function __serialize() : array
    {
        $data = [];
        foreach (Locale::cases() as $l)
            if (isset($this->store[$l]))
                $data[$l->value] = $this->store[$l];

        $fmt = null;
        if ($this->formatter)
        {
            $rf = new \ReflectionFunction($this->formatter);
            if ($rf->isAnonymous())
                trigger_error('LocString::__serialize - anonymous formatting functions cannot be serialized', E_USER_WARNING);
            else if ($scope = $rf->getClosureScopeClass())
                $fmt = [$scope->name, $rf->name];
            else
                $fmt = $rf->name;
        }

        return ['store' => $data, 'fmt' => $fmt];
    }

    public function __unserialize(array $data) : void
    {
        $this->store = new \WeakMap();

        if (empty($data['store']))
            return;

        $this->formatter = isset($data['fmt']) ? \Closure::fromCallable($data['fmt']) : null;

        foreach ($data['store'] as $locId => $str)
            if (($l = Locale::tryFrom($locId))?->validate())
                $this->store[$l] = (string)$str;
    }

    private static function nullFormat(string $s) : string
    {
        return $s;
    }
}

?>
