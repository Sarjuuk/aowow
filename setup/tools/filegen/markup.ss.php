<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("build", new class extends SetupScript
{
    use TrTemplateFile;

    protected $info = array(
        'markup' => [[], CLISetup::ARGV_PARAM, 'Fills the markup parser (static/js/Markup.js) with site variables.']
    );

    protected $fileTemplateSrc  = ['Markup.js.in'];
    protected $fileTemplateDest = ['static/js/Markup.js'];
});

?>
