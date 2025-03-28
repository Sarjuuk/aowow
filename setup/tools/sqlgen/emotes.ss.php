<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("sql", new class extends SetupScript
{
    protected $info = array(
        'emotes' => [[], CLISetup::ARGV_PARAM, 'Compiles data for type: Emote from dbc and GlobalStrings.lua.']
    );

    protected $useGlobalStrings = true;
    protected $dbcSourceFiles   = ['emotes', 'emotestext', 'emotestextdata'];

    private $textData = [];

    public function generate(array $ids = []) : bool
    {
        $globStrPath = CLISetup::$srcDir.'%sInterface/FrameXML/GlobalStrings.lua';
        $allOK       = true;
        $locPath     = CLISetup::filesInPathLocalized($globStrPath, $allOK);

        if ($x = array_diff_key(CLISetup::$locales, $locPath))
        {
            $locs = array_intersect_key(CLISetup::$locales, $x);
            CLI::write('[emotes] '.sprintf($globStrPath, '[' . Lang::concat($locs, callback: fn($x) => CLI::bold(implode('/, ', $x->gameDirs()) . '/')) . ']') . ' not found!', CLI::LOG_WARN);
            CLI::write('         Emote aliasses can not be generated for affected locales!', CLI::LOG_WARN);
        }

        DB::Aowow()->query('TRUNCATE ?_emotes');
        DB::Aowow()->query('TRUNCATE ?_emotes_aliasses');


        /*********************/
        /* Player controlled */
        /*********************/

        /*  EmotesText Data offsets
                gender  seenBy      hasTarget   mergedWith      example
            0   male    others      yes         8               %s raises <his/her> fist in anger at %s.
            1   male    self        yes         9               %s raises <his/her> fist in anger at you.
            2   self    self        yes                         You raise your fist in anger at %s.
            4   male    others      no          12              %s raises <his/her> fist in anger.
            6   self    self        no                          You raise your fist in anger.
            8   female  others      yes         0               -
            9   female  self        yes         1               -
            12  female  others      no          4               -
        */

        $this->textData = DB::Aowow()->select('SELECT `id` AS ARRAY_KEY, `text_loc0` AS "0", `text_loc2` AS "2", `text_loc3` AS "3", `text_loc4` AS "4", `text_loc6` AS "6", `text_loc8` AS "8" FROM dbc_emotestextdata');

        $texts = DB::Aowow()->select('SELECT et.`id` AS ARRAY_KEY, LOWER(`command`) AS "cmd", IF(e.`animationId`, 1, 0) AS "anim", -`emoteId` AS "parent", e.`soundId`, `etd0`, `etd1`, `etd2`, `etd4`, `etd6`, `etd8`, `etd9`, `etd12` FROM dbc_emotestext et LEFT JOIN dbc_emotes e ON e.`id` = et.`emoteId`');
        foreach ($texts AS $id => $t)
        {
            DB::Aowow()->query(
               'INSERT INTO ?_emotes (
                    `id`, `cmd`, `isAnimated`, `parentEmote`, `soundId`,
                    `extToExt_loc0`, `extToMe_loc0`, `meToExt_loc0`, `extToNone_loc0`, `meToNone_loc0`,
                    `extToExt_loc2`, `extToMe_loc2`, `meToExt_loc2`, `extToNone_loc2`, `meToNone_loc2`,
                    `extToExt_loc3`, `extToMe_loc3`, `meToExt_loc3`, `extToNone_loc3`, `meToNone_loc3`,
                    `extToExt_loc4`, `extToMe_loc4`, `meToExt_loc4`, `extToNone_loc4`, `meToNone_loc4`,
                    `extToExt_loc6`, `extToMe_loc6`, `meToExt_loc6`, `extToNone_loc6`, `meToNone_loc6`,
                    `extToExt_loc8`, `extToMe_loc8`, `meToExt_loc8`, `extToNone_loc8`, `meToNone_loc8`)
                VALUES
                    (?d, ?, ?d, ?d, ?d, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                $id, $t['cmd'], $t['anim'], $t['parent'], $t['soundId'],
                $this->mergeGenderedStrings($t['etd0'], $t['etd8'], WoWLocale::EN), $this->mergeGenderedStrings($t['etd1'], $t['etd9'], WoWLocale::EN), $this->mergeGenderedStrings($t['etd4'], $t['etd12'], WoWLocale::EN), $this->textData[$t['etd2']][WoWLocale::EN->value] ?? '', $this->textData[$t['etd6']][WoWLocale::EN->value] ?? '',
                $this->mergeGenderedStrings($t['etd0'], $t['etd8'], WoWLocale::FR), $this->mergeGenderedStrings($t['etd1'], $t['etd9'], WoWLocale::FR), $this->mergeGenderedStrings($t['etd4'], $t['etd12'], WoWLocale::FR), $this->textData[$t['etd2']][WoWLocale::FR->value] ?? '', $this->textData[$t['etd6']][WoWLocale::FR->value] ?? '',
                $this->mergeGenderedStrings($t['etd0'], $t['etd8'], WoWLocale::DE), $this->mergeGenderedStrings($t['etd1'], $t['etd9'], WoWLocale::DE), $this->mergeGenderedStrings($t['etd4'], $t['etd12'], WoWLocale::DE), $this->textData[$t['etd2']][WoWLocale::DE->value] ?? '', $this->textData[$t['etd6']][WoWLocale::DE->value] ?? '',
                $this->mergeGenderedStrings($t['etd0'], $t['etd8'], WoWLocale::CN), $this->mergeGenderedStrings($t['etd1'], $t['etd9'], WoWLocale::CN), $this->mergeGenderedStrings($t['etd4'], $t['etd12'], WoWLocale::CN), $this->textData[$t['etd2']][WoWLocale::CN->value] ?? '', $this->textData[$t['etd6']][WoWLocale::CN->value] ?? '',
                $this->mergeGenderedStrings($t['etd0'], $t['etd8'], WoWLocale::ES), $this->mergeGenderedStrings($t['etd1'], $t['etd9'], WoWLocale::ES), $this->mergeGenderedStrings($t['etd4'], $t['etd12'], WoWLocale::ES), $this->textData[$t['etd2']][WoWLocale::ES->value] ?? '', $this->textData[$t['etd6']][WoWLocale::ES->value] ?? '',
                $this->mergeGenderedStrings($t['etd0'], $t['etd8'], WoWLocale::RU), $this->mergeGenderedStrings($t['etd1'], $t['etd9'], WoWLocale::RU), $this->mergeGenderedStrings($t['etd4'], $t['etd12'], WoWLocale::RU), $this->textData[$t['etd2']][WoWLocale::RU->value] ?? '', $this->textData[$t['etd6']][WoWLocale::RU->value] ?? ''
            );
        }

        // i have no idea, how the indexing in this file works.
        // sometimes the \d+ after EMOTE is the emoteTextId, but not nearly often enough
        $aliasses = [];
        foreach (CLISetup::searchGlobalStrings('/^EMOTE(\d+)_CMD\d+\s=\s\"\/([^"]+)\";$/') as $lId => $match)
            $aliasses[$match[1]][] = [$lId, $match[2]];

        $emotes = DB::Aowow()->selectCol('SELECT id AS ARRAY_KEY, cmd FROM ?_emotes');

        foreach($emotes as $eId => $cmd)
        {
            foreach ($aliasses as $data)
            {
                if (!in_array($cmd, array_column($data, 1)))
                    continue;

                foreach ($data as $d)
                    DB::Aowow()->query('INSERT IGNORE INTO ?_emotes_aliasses VALUES (?d, ?d, ?) ON DUPLICATE KEY UPDATE locales = locales | ?d', $eId, (1 << $d[0]), strtolower($d[1]), (1 << $d[0]));

                continue 2;
            }

            DB::Aowow()->query('UPDATE ?_emotes SET `cuFlags` = `cuFlags` | ?d WHERE `id` = ?d', CUSTOM_EXCLUDE_FOR_LISTVIEW | EMOTE_CU_MISSING_CMD, $eId);
        }


        /*********************/
        /* Server controlled */
        /*********************/

        DB::Aowow()->query('INSERT INTO ?_emotes (`id`, `cmd`, `flags`, `isAnimated`, `parentEmote`, `soundId`, `state`, `stateParam`, `cuFlags`) SELECT -`id`, `name`, `flags`, IF(`animationId`, 1, 0), 0, `soundId`, `state`, `stateParam`, ?d FROM dbc_emotes WHERE 1', CUSTOM_EXCLUDE_FOR_LISTVIEW);


        $this->reapplyCCFlags('emotes', Type::EMOTE);

        return $allOK;
    }

    private function mergeGenderedStrings(int $maleTextId, int $femaleTextId, WoWLocale $loc) : string
    {
        $maleText   = $this->textData[$maleTextId][$loc->value]   ?? '';
        $femaleText = $this->textData[$femaleTextId][$loc->value] ?? '';

        if (!$maleText && !$femaleText)
            return '';
        else if (!$maleText)
            return $femaleText;
        else if (!$femaleText)
            return $maleText;
        else if ($maleText == $femaleText)
            return $maleText;

        $front = $back = [];

        $m = explode(' ', $maleText);
        $f = explode(' ', $femaleText);
        $n = max(count($m), count($f));

        // advance from left until diff -> store common string
        // advance from right until diff -> store common string
        // merge leftovers as gendered switch and recombine string

        $i = 0;
        for (; $i < $n; $i++)
        {
            if (!isset($m[$i]) || !isset($f[$i]))
                break;
            else if ($m[$i] != $f[$i])
                break;
            else
                $front[] = $m[$i];
        }

        if ($i + 1 == $n)                                   // loop completed all elements
            return implode(' ', $m);

        $m = array_reverse($m);
        $f = array_reverse($f);

        $j = 0;
        for (; $j < $n - $i; $j++)
        {
            if (!isset($m[$j]) || !isset($f[$j]))
                break;
            else if ($m[$j] != $f[$j])
                break;
            else
                $back[] = $m[$j];
        }

        $m = array_reverse($m);
        $f = array_reverse($f);

        $midM = array_slice($m, $i, count($m) - $i - $j);
        $midF = array_slice($f, $i, count($f) - $i - $j);

        $mid = '$g' . implode(' ', $midM ?? []) . ':' . implode(' ', $midF ?? []) . ';';

        return implode(' ', array_merge($front, [$mid], array_reverse($back)));
    }
});

?>
