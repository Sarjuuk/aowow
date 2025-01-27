<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


// Create 'locale.js'-file in static/js

/*
        0: { // English
            id: LOCALE_ENUS,
            name: 'enus',
            domain: 'en',
            description: 'English'
        },
        2: { // French
            id: LOCALE_FRFR,
            name: 'frfr',
            domain: 'fr',
            description: 'Fran' + String.fromCharCode(231) + 'ais'
        },
        3: { // German
            id: LOCALE_DEDE,
            name: 'dede',
            domain: 'de',
            description: 'Deutsch'
        },
        4:{ // Chinese
            id: LOCALE_ZHCN,
            name: 'zhcn',
            domain: 'cn',
            description: String.fromCharCode(31616, 20307, 20013, 25991)
        },
        6: { // Spanish
            id: LOCALE_ESES,
            name: 'eses',
            domain: 'es',
            description: 'Espa' + String.fromCharCode(241) + 'ol'
        },
        8: { // Russian
            id: LOCALE_RURU,
            name: 'ruru',
            domain: 'ru',
            description: String.fromCharCode(1056, 1091, 1089, 1089, 1082, 1080, 1081)
        }
*/

CLISetup::registerSetup("build", new class extends SetupScript
{
    use TrTemplateFile;

    protected $info = array(
        'locales' => [[], CLISetup::ARGV_PARAM, 'Compiles the Locale Object (static/js/locale.js) with available languages.']
    );

    protected $fileTemplateDest = ['static/js/locale.js'];
    protected $fileTemplateSrc  = ['locale.js.in'];

    private function locales() : string
    {
        $result = [];

        foreach (CLISetup::$locales as $loc)
            $result[$loc->value] = array(
                'id'          => '$LOCALE_' . strtoupper($loc->json()),
                'name'        => $loc->json(),
                'domain'      => $loc->domain(),
                'description' => $loc->title()
            );

        return Util::toJSON($result);
    }
});

?>
