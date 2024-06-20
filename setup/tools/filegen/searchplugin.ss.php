<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("build", new class extends SetupScript
{
    use TrTemplateFile;

    protected $info = array(
        'searchplugin' => [[], CLISetup::ARGV_PARAM, 'Fills browser opensearch plugin (static/download/searchplugins/aowow.xml) with site variables.']
    );

    protected $fileTemplateSrc  = ['aowow.xml.in'];
    protected $fileTemplateDest = ['static/download/searchplugins/aowow.xml'];
    protected $requiredDirs     = ['static/download/searchplugins/'];
});

?>
