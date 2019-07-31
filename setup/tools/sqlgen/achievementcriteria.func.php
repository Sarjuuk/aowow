<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    use TrDBCcopy;

    protected $command         = 'achievementcriteria';
    protected $dbcSourceFiles  = ['achievement_criteria'];
});

?>
