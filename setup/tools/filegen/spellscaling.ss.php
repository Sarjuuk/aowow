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
        'spellscaling' => [[], CLISetup::ARGV_PARAM, 'Compiles spell scaling data to file for spells with attribute SPELL_ATTR0_LEVEL_DAMAGE_CALCULATION.']
    );

    protected $fileTemplateDest = ['datasets/spell-scaling'];
    protected $fileTemplateSrc  = ['spell-scaling.in'];

    protected $dbcSourceFiles   = ['gtnpcmanacostscaler'];

    private function debugify(array $data) : string
    {
        $buff = [];
        foreach ($data as $id => $row)
            $buff[] = str_pad($id, 7, " ", STR_PAD_LEFT).": ".$row;

        return "{\r\n".implode(",\r\n", $buff)."\r\n}";
    }

    private function spellScalingSV() : string
    {
        $data = DB::Aowow()->selectCol('SELECT `idx` + 1 AS ARRAY_KEY, `factor` FROM dbc_gtnpcmanacostscaler');

        return Cfg::get('DEBUG') ? $this->debugify($data) : Util::toJSON($data);
    }
})

?>
