<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("build", new class extends SetupScript
{
    use TrTemplateFile;

    protected array $info = array(
        'searchplugin' => [[], CLISetup::ARGV_PARAM, 'Fills browser opensearch plugin (static/download/searchplugins/aowow.xml) with site variables.']
    );

    protected array $fileTemplateSrc  = ['aowow.xml.in'];
    protected array $fileTemplateDest = ['static/download/searchplugins/aowow.xml'];
    protected array $requiredDirs     = ['static/download/searchplugins/'];
});

?>
