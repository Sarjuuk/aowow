<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("build", new class extends SetupScript
{
    use TrTemplateFile;

    protected $info = array(
        'demo' => [[], CLISetup::ARGV_PARAM, 'Fills powered tooltip demo page (static/widgets/power/demo.html) with site variables.']
    );

    protected $fileTemplateSrc  = ['demo.html.in'];
    protected $fileTemplateDest = ['static/widgets/power/demo.html'];
});

?>
