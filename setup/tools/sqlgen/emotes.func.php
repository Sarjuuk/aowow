<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    protected $command = 'emotes';

    protected $dbcSourceFiles = ['emotes', 'emotestext', 'emotestextdata'];

    public function generate(array $ids = []) : bool
    {
        /**********/
        /* Basics */
        /**********/

        $globStrPath  = CLISetup::$srcDir.'%sInterface/FrameXML/GlobalStrings.lua';
        $allOK        = true;
        $locPath      = [];

        DB::Aowow()->query('TRUNCATE ?_emotes_aliasses');

        foreach (CLISetup::$localeIds as $lId)
        {
            foreach (CLISetup::$expectedPaths as $xp => $locId)
            {
                if ($lId != $locId)
                    continue;

                if ($xp)                                        // if in subDir add trailing slash
                    $xp .= '/';

                $path = sprintf($globStrPath, $xp);
                if (CLISetup::fileExists($path))
                {
                    $locPath[$lId] = $path;
                    continue 2;
                }
            }

            CLI::write('GlobalStrings.lua not found for selected locale '.CLI::bold(Util::$localeStrings[$lId]), CLI::LOG_WARN);
            $allOK = false;
        }

        $_= DB::Aowow()->query('REPLACE INTO ?_emotes SELECT
                et.id,
                LOWER(et.command),
                IF(e.animationId, 1, 0),
                0,                                              -- cuFlags
                etdT.text_loc0,  etdT.text_loc2,  etdT.text_loc3,  etdT.text_loc4,  etdT.text_loc6,  etdT.text_loc8,
                etdNT.text_loc0, etdNT.text_loc2, etdNT.text_loc3, etdNT.text_loc4, etdNT.text_loc6, etdNT.text_loc8,
                etdS.text_loc0,  etdS.text_loc2,  etdS.text_loc3,  etdS.text_loc4,  etdS.text_loc6,  etdS.text_loc8
            FROM
                dbc_emotestext et
            LEFT JOIN
                dbc_emotes e ON e.id = et.emoteId
            LEFT JOIN
                dbc_emotestextdata etdT  ON etdT.id  = et.targetId
            LEFT JOIN
                dbc_emotestextdata etdNT ON etdNT.id = et.noTargetId
            LEFT JOIN
                dbc_emotestextdata etdS  ON etdS.id  = et.selfId'
        );

        if (!$_)
            $allOK = false;

        // i have no idea, how the indexing in this file works.
        // sometimes the \d+ after EMOTE is the emoteTextId, but not nearly often enough
        $aliasses = [];
        foreach ($locPath as $lId => $path)
            foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line)
                if (preg_match('/^EMOTE(\d+)_CMD\d+\s=\s\"\/([^"]+)\";$/', $line, $m))
                    $aliasses[$m[1]][] = [$lId, $m[2]];


        $emotes = DB::Aowow()->selectCol('SELECT id AS ARRAY_KEY, cmd FROM ?_emotes');

        foreach($emotes as $eId => $cmd)
        {
            foreach ($aliasses as $gsId => $data)
            {
                if (in_array($cmd, array_column($data, 1)))
                {
                    foreach ($data as $d)
                        DB::Aowow()->query('INSERT IGNORE INTO ?_emotes_aliasses VALUES (?d, ?d, ?) ON DUPLICATE KEY UPDATE locales = locales | ?d', $eId, (1 << $d[0]), strtolower($d[1]), (1 << $d[0]));

                    break;
                }
            }
        }

        return $allOK;
    }
});

?>
