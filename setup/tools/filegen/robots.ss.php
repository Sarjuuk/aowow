<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("build", new class extends SetupScript
{
    use TrTemplateFile;

    protected $info = array(
        'robots' => [[], CLISetup::ARGV_PARAM, 'Fills robots.txt with site variables.']
    );

    protected $fileTemplateSrc  = ['robots.txt.in'];
    protected $fileTemplateDest = ['robots.txt'];           // aowow root
});

?>
