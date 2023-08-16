<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    protected $command         = 'declinedwords';
    protected $dbcSourceFiles  = ['declinedword', 'declinedwordcases'];

    public function generate() : bool
    {
        CLI::write('SqlGen::generate() - copying declinedword.dbc into aowow_declinedword');
        DB::Aowow()->query('TRUNCATE ?_declinedword');
        DB::Aowow()->query('INSERT INTO ?_declinedword SELECT * FROM dbc_declinedword');

        CLI::write('SqlGen::generate() - copying declinedwordcases.dbc into aowow_declinedwordcases');
        DB::Aowow()->query('TRUNCATE ?_declinedwordcases');
        DB::Aowow()->query('INSERT INTO ?_declinedwordcases SELECT `wordId`, `caseIdx`, `word` FROM dbc_declinedwordcases');

        return true;
    }
});

?>
