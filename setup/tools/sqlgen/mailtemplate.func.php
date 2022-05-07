<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    protected $command            = 'mails';

    protected $tblDependencyTC    = ['achievement_reward', 'achievement_reward_locale', 'mail_loot_template'];
    protected $dbcSourceFiles     = ['mailtemplate'];

    public function generate(array $ids = []) : bool
    {
        DB::Aowow()->query('TRUNCATE aowow_mails');

        // copy data over from dbc
        DB::Aowow()->query('
            INSERT INTO ?_mails SELECT 
                id, 0, 
                subject_loc0, subject_loc2, subject_loc3, subject_loc4, subject_loc6, subject_loc8,
                text_loc0, text_loc2, text_loc3, text_loc4, text_loc6, text_loc8,
                0 
            FROM dbc_mailtemplate
        ');

        CLI::write('SqlGen::generate() - merging achievement_reward into aowow_mails');

        $acvMail = DB::World()->select('
            SELECT
                -ar.ID, 0,
                IFNULL(ar.Subject, "") AS s0, IFNULL(arl2.Subject, "") AS s2, IFNULL(arl3.Subject, "") AS s3, IFNULL(arl4.Subject, "") AS s4, IFNULL(arl6.Subject, "") AS s6, IFNULL(arl8.Subject, "") AS s8,
                IFNULL(ar.Body, "")    AS t0, IFNULL(arl2.Body, "")    AS t2, IFNULL(arl3.Body, "")    AS t3, IFNULL(arl4.Body, "")    AS t4, IFNULL(arl6.Body, "")    AS t6, IFNULL(arl8.Body, "")    AS t8,
                ItemID
            FROM
                achievement_reward ar
            LEFT JOIN
                achievement_reward_locale arl2 ON ar.ID = arl2.ID AND arl2.Locale = "frFR"
            LEFT JOIN
                achievement_reward_locale arl3 ON ar.ID = arl3.ID AND arl3.Locale = "deDE"
            LEFT JOIN
                achievement_reward_locale arl4 ON ar.ID = arl4.ID AND arl4.Locale = "zhCN"
            LEFT JOIN
                achievement_reward_locale arl6 ON ar.ID = arl6.ID AND arl6.Locale = "esES"
            LEFT JOIN
                achievement_reward_locale arl8 ON ar.ID = arl8.ID AND arl8.Locale = "ruRU"
            WHERE
                ar.MailTemplateID = 0 AND ar.Body <> ""
        ');

        DB::Aowow()->query('INSERT INTO aowow_mails VALUES (?a)', array_values($acvMail));

        CLI::write('SqlGen::generate() - merging mail_loot_template into aowow_mails');

        // assume mails to only contain one single item, wich works for an unmodded installation
        $mlt = DB::World()->selectCol('SELECT Entry AS ARRAY_KEY, Item FROM mail_loot_template');
        foreach ($mlt as $k => $v)
            DB::Aowow()->query('UPDATE ?_mails SET attachment = ?d WHERE id = ?d', $v, $k);

        return true;
    }
});

?>
