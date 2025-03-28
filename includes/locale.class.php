<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


enum WoWLocale : int
{                                                           // unused by    TC WoW self
    case EN =  0;                                           // enGB, enUS
    case KR =  1;                                           // koKR                 ?
    case FR =  2;                                           // frFR
    case DE =  3;                                           // deDE
    case CN =  4;                                           // zhCN, enCN
    case TW =  5;                                           // zhTW, enTW           x
    case ES =  6;                                           // esES
    case MX =  7;                                           // esMX                 x
    case RU =  8;                                           // ruRU
    case JP =  9;                                           // jaJP         x   x   x
    case PT = 10;                                           // ptPT, ptBR   x       ?
    case IT = 11;                                           // itIT         x   x   x

    private const MASK_ALL = 0b000101011101;                // technically supported locales

    public function domain() : string                       // our subdomain / locale in web context
    {
        return match ($this)
        {
            self::EN => 'en',
            self::KR => 'kt',
            self::FR => 'fr',
            self::DE => 'de',
            self::CN => 'cn',
            self::TW => 'tw',
            self::ES => 'es',
            self::MX => 'mx',
            self::RU => 'ru',
            self::JP => 'jp',
            self::PT => 'pt',
            self::IT => 'it'
        };
    }

    public function json() : string                         // internal usage / json string
    {
        return match ($this)
        {
            self::EN => 'enus',
            self::KR => 'kokr',
            self::FR => 'frfr',
            self::DE => 'dede',
            self::CN => 'zhcn',
            self::TW => 'zhtw',
            self::ES => 'eses',
            self::MX => 'esmx',
            self::RU => 'ruru',
            self::JP => 'jajp',
            self::PT => 'ptpt',
            self::IT => 'itit'
        };
    }

    public function title() : string                        // localized language name
    {
        return match ($this)
        {
            self::EN => 'English',
            self::KR => '한국어',
            self::FR => 'Français',
            self::DE => 'Deutsch',
            self::CN => '简体中文',
            self::TW => '繁體中文',
            self::ES => 'Español',
            self::MX => 'Mexicano',
            self::RU => 'Русский',
            self::JP => '日本語',
            self::PT => 'Português',
            self::IT => 'Italiano'
        };
    }

    public function gameDirs() : array                      // setup data source / wow client locale code
    {
        return match ($this)
        {
            self::EN => ['enGB', 'enUS', ''],
            self::KR => ['koKR'],
            self::FR => ['frFR'],
            self::DE => ['deDE'],
            self::CN => ['zhCN', 'enCN'],
            self::TW => ['zhTW', 'enTW'],
            self::ES => ['esES'],
            self::MX => ['esMX'],
            self::RU => ['ruRU'],
            self::JP => ['jaJP'],
            self::PT => ['ptPT', 'ptBR'],
            self::IT => ['itIT']
        };
    }

    public function httpCode() : array                      // HTTP_ACCEPT_LANGUAGE
    {
        return match ($this)
        {
            self::EN => ['en', 'en-au', 'en-bz', 'en-ca', 'en-ie', 'en-jm', 'en-nz', 'en-ph', 'en-za', 'en-tt', 'en-gb', 'en-us', 'en-zw'],
            self::KR => ['ko', 'ko-kp', 'ko-kr'],
            self::FR => ['fr', 'fr-be', 'fr-ca', 'fr-fr', 'fr-lu', 'fr-mc', 'fr-ch'],
            self::DE => ['de', 'de-at', 'de-de', 'de-li', 'de-lu', 'de-ch'],
            self::CN => ['zh', 'zh-hk', 'zh-cn', 'zh-sg'],
            self::TW => ['tw', 'zh-tw'],
            self::ES => ['es', 'es-ar', 'es-bo', 'es-cl', 'es-co', 'es-cr', 'es-do', 'es-ec', 'es-sv', 'es-gt', 'es-hn', 'es-ni', 'es-pa', 'es-py', 'es-pe', 'es-pr', 'es-es', 'es-uy', 'es-ve'],
            self::MX => ['mx', 'es-mx'],
            self::RU => ['ru', 'ru-mo'],
            self::JP => ['ja'],
            self::PT => ['pt', 'pt-br'],
            self::IT => ['it', 'it-ch']
        };
    }

    public function isLogographic() : bool
    {
        return $this == WoWLocale::CN || $this == WoWLocale::TW || $this == WoWLocale::KR;
    }

    public function validate() : ?self
    {
        return ($this->maskBit() & self::MASK_ALL & (Cfg::get('LOCALES') ?: 0xFFFF)) ? $this : null;
    }

    public function maskBit() : int
    {
        return (1 << $this->value);
    }

    public static function tryFromDomain(string $str) : ?self
    {
        foreach (self::cases() as $l)
            if ($l->validate() && $str == $l->domain())
                return $l;

        return null;
    }

    public static function tryFromHttpAcceptLanguage(string $httpAccept) : ?self
    {
        if (!$httpAccept)
            return null;

        $available = [];

        // e.g.: de,de-DE;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6
        foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $loc)
            if (preg_match('/([a-z\-]+)(?:\s*;\s*q\s*=\s*([\.\d]+))?/ui', $loc, $m, PREG_UNMATCHED_AS_NULL))
                $available[Util::lower($m[1])] = floatVal($m[2] ?? 1); // no quality set: assume 100%

        arsort($available, SORT_NUMERIC);                   // highest quality on top

        foreach ($available as $code => $_)
            foreach (self::cases() as $l)
                if ($l->validate() && in_array($code, $l->httpCode()))
                    return $l;

        return null;
    }

    public static function getFallback() : self
    {
        foreach (WoWLocale::cases() as $l)
            if ($l->validate())
                return $l;

        // wow, you really fucked up your config mate!
        trigger_error('WoWLocale::getFallback - there are no valid locales', E_USER_ERROR);
        return self::EN;
    }
}


/* The shape of things to come */
class LocString
{
    private WeakMap $store;

    public function __construct(array $data, string $key = 'name', ?callable $callback = null)
    {
        $this->store = new WeakMap();

        $callback ??= fn($x) => $x;

        if (!array_filter($data, fn($v, $k) => $v && strstr($k, $key.'_loc'), ARRAY_FILTER_USE_BOTH))
            trigger_error('LocString - is entrirely empty', E_USER_WARNING);

        foreach (WoWLocale::cases() as $l)
            $this->store[$l] = (string)$callback($data[$key.'_loc'.$l->value] ?? '');
    }

    public function __toString() : string
    {
        if ($str = $this->store[Lang::getLocale()])
            return $str;

        foreach (WoWLocale::cases() as $l)                // desired loc not set, use any other
            if ($str = $this->store[$l])
                return Cfg::get('DEBUG') ? '['.$str.']' : $str;

        return Cfg::get('DEBUG') ? '[LOCSTRING]' : '';
    }
}

?>
