<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("sql", new class extends SetupScript
{
    protected $info = array(
        'declinedwords' => [[], CLISetup::ARGV_PARAM, 'Compiles data for type: Enchantment from dbc and world db.']
    );

    protected $dbcSourceFiles  = ['declinedword', 'declinedwordcases'];

    public function generate() : bool
    {
        CLI::write('[declinedwords] - copying declinedword.dbc into aowow_declinedword');
        DB::Aowow()->qry('TRUNCATE ::declinedword');
        DB::Aowow()->qry('INSERT INTO ::declinedword SELECT * FROM dbc_declinedword');

        CLI::write('[declinedwords] - copying declinedwordcases.dbc into aowow_declinedwordcases');
        DB::Aowow()->qry('TRUNCATE ::declinedwordcases');
        DB::Aowow()->qry('INSERT INTO ::declinedwordcases SELECT `wordId`, `caseIdx`, `word` FROM dbc_declinedwordcases');

        return true;
    }
});

?>
