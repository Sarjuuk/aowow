<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("build", new class extends SetupScript
{
    use TrTemplateFile;

    protected $info = array(
        'tooltips' => [[], CLISetup::ARGV_PARAM, 'Fills powered tooltips (static/widgets/power.js) with site variables.']
    );

    protected $fileTemplateSrc  = ['power.js.in'];
    protected $fileTemplateDest = ['static/widgets/power.js'];

    public function generate() : bool
    {
        $this->templateFill();
        return $this->success;
    }
});

?>
