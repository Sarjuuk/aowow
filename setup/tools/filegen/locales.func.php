<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


    // Create 'locale.js'-file in static/js
    // available locales have to be set in aowow.aowow_config

    function locales()
    {
        $result    = [];
        $available = array(
            LOCALE_EN => "        0: { // English\r\n" .
                         "            id: LOCALE_ENUS,\r\n" .
                         "            name: 'enus',\r\n" .
                         "            domain: 'www',\r\n" .
                         "            description: 'English'\r\n" .
                         "        }",
            LOCALE_FR => "        2: { // French\r\n" .
                         "            id: LOCALE_FRFR,\r\n" .
                         "            name: 'frfr',\r\n" .
                         "            domain: 'fr',\r\n" .
                         "            description: 'Fran' + String.fromCharCode(231) + 'ais'\r\n" .
                         "        }",
            LOCALE_DE => "        3: { // German\r\n" .
                         "            id: LOCALE_DEDE,\r\n" .
                         "            name: 'dede',\r\n" .
                         "            domain: 'de',\r\n" .
                         "            description: 'Deutsch'\r\n" .
                         "        }",
            LOCALE_CN => "        4:{ // Chinese\r\n" .
                         "            id: LOCALE_ZHCN,\r\n" .
                         "            name: 'zhcn',\r\n" .
                         "            domain: 'cn',\r\n" .
                         "            description: String.fromCharCode(31616, 20307, 20013, 25991)\r\n" .
                         "        }",
            LOCALE_ES => "        6: { // Spanish\r\n" .
                         "            id: LOCALE_ESES,\r\n" .
                         "            name: 'eses',\r\n" .
                         "            domain: 'es',\r\n" .
                         "            description: 'Espa' + String.fromCharCode(241) + 'ol'\r\n" .
                         "        }",
            LOCALE_RU => "        8: { // Russian\r\n" .
                         "            id: LOCALE_RURU,\r\n" .
                         "            name: 'ruru',\r\n" .
                         "            domain: 'ru',\r\n" .
                         "            description: String.fromCharCode(1056, 1091, 1089, 1089, 1082, 1080, 1081)\r\n" .
                         "        }",
        );

        foreach (CLISetup::$localeIds as $l)
            if (isset($available[$l]))
                $result[] = $available[$l];

        return implode(",\r\n", $result);
    }

?>
