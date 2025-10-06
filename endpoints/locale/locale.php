<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class LocaleBaseResponse extends TextResponse
{
    protected array $expectedGET = array(
        'locale' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkLocale']]
    );

    protected function generate() : void
    {
        if ($this->_get['locale']?->validate())
        {
            User::$preferedLoc = $this->_get['locale'];
            User::save(true);
        }

        $this->redirectTo = $_SERVER['HTTP_REFERER'] ?? '.';
    }
}

?>
