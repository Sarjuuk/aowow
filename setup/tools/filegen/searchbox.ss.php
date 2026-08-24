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
        'searchbox' => [[], CLISetup::ARGV_PARAM, 'Fills search widget files (static/widgets/searchbox*) with site variables.']
    );

    protected array $fileTemplateSrc  = ['searchbox.js.in',             'searchbox.html.in'];
    protected array $fileTemplateDest = ['static/widgets/searchbox.js', 'static/widgets/searchbox/searchbox.html'];
});

?>
