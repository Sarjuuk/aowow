<?php

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

    public function generate(array $ids = []) : bool
    {
        CLI::write('[declinedwords] - copying declinedword.dbc into aowow_declinedword');
        DB::Aowow()->query('TRUNCATE ?_declinedword');
        DB::Aowow()->query('INSERT INTO ?_declinedword SELECT * FROM dbc_declinedword');

        CLI::write('[declinedwords] - copying declinedwordcases.dbc into aowow_declinedwordcases');
        DB::Aowow()->query('TRUNCATE ?_declinedwordcases');
        DB::Aowow()->query('INSERT INTO ?_declinedwordcases SELECT `wordId`, `caseIdx`, `word` FROM dbc_declinedwordcases');

        return true;
    }
});

?>
