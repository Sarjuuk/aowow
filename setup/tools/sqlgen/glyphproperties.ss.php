<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("sql", new class extends SetupScript
{
    protected $info = array(
        'glyphproperties' => [[], CLISetup::ARGV_PARAM, 'Compiles supplemental data for type: Item & Spell from dbc.']
    );

    protected $dbcSourceFiles = ['glyphproperties', 'spellicon'];
    protected $setupAfter     = [['icons'], []];

    public function generate() : bool
    {
        DB::Aowow()->qry('TRUNCATE ::glyphproperties');
        DB::Aowow()->qry('INSERT INTO ::glyphproperties SELECT `id`, `spellId`, `typeFlags`, 0, `iconId` FROM dbc_glyphproperties');

        DB::Aowow()->qry('UPDATE ::glyphproperties gp, ::icons ic, dbc_spellicon si SET gp.`iconId` = ic.`id` WHERE gp.`iconIdBak` = si.`id` AND ic.`name_source` = LOWER(SUBSTRING_INDEX(si.`iconPath`, "\\", -1))');

        return true;
    }
});

?>
