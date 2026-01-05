<?php

namespace Aowow;

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

    public function generate() : bool
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

        DB::Aowow()->qry('TRUNCATE ::emotes');
        DB::Aowow()->qry('TRUNCATE ::emotes_aliasses');


        /*********************/
        /* Player controlled */
        /*********************/

        /*  EmotesText Data offsets
                gender  seenBy      hasTarget   effective       mergedWith      example
            0   male    others      yes         ext -> ext      8               %s raises <his/her> fist in anger at %s.
            1   male    self        yes         ext -> me       9               %s raises <his/her> fist in anger at you.
            2   self    self        yes         me  -> ext                      You raise your fist in anger at %s.
            4   male    others      no          ext -> none     12              %s raises <his/her> fist in anger.
            6   self    self        no          me  -> none                     You raise your fist in anger.
            8   female  others      yes         ext -> ext      0               -
            9   female  self        yes         ext -> me       1               -
            12  female  others      no          ext -> none     4               -
        */

        $this->textData = DB::Aowow()->selectAssoc('SELECT `id` AS ARRAY_KEY, `text_loc0` AS "0", `text_loc2` AS "2", `text_loc3` AS "3", `text_loc4` AS "4", `text_loc6` AS "6", `text_loc8` AS "8" FROM dbc_emotestextdata');

        $texts = DB::Aowow()->selectAssoc('SELECT et.`id` AS ARRAY_KEY, LOWER(`command`) AS "cmd", IF(e.`animationId`, 1, 0) AS "anim", -`emoteId` AS "parent", e.`soundId`, `etd0`, `etd1`, `etd2`, `etd4`, `etd6`, `etd8`, `etd9`, `etd12` FROM dbc_emotestext et LEFT JOIN dbc_emotes e ON e.`id` = et.`emoteId`');
        foreach ($texts AS $id => $t)
        {
            DB::Aowow()->qry(
               'INSERT INTO ::emotes (
                    `id`, `cmd`, `isAnimated`, `parentEmote`, `soundId`,
                    `extToExt_loc0`, `extToMe_loc0`, `meToExt_loc0`, `extToNone_loc0`, `meToNone_loc0`,
                    `extToExt_loc2`, `extToMe_loc2`, `meToExt_loc2`, `extToNone_loc2`, `meToNone_loc2`,
                    `extToExt_loc3`, `extToMe_loc3`, `meToExt_loc3`, `extToNone_loc3`, `meToNone_loc3`,
                    `extToExt_loc4`, `extToMe_loc4`, `meToExt_loc4`, `extToNone_loc4`, `meToNone_loc4`,
                    `extToExt_loc6`, `extToMe_loc6`, `meToExt_loc6`, `extToNone_loc6`, `meToNone_loc6`,
                    `extToExt_loc8`, `extToMe_loc8`, `meToExt_loc8`, `extToNone_loc8`, `meToNone_loc8`)
                VALUES
                    (%i, %s, %i, %i, %i, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)',
                $id, $t['cmd'], $t['anim'], $t['parent'], $t['soundId'],
                $this->mergeGenderedStrings($t['etd0'], $t['etd8'], Locale::EN), $this->mergeGenderedStrings($t['etd1'], $t['etd9'], Locale::EN), $this->textData[$t['etd2']][Locale::EN->value] ?? '', $this->mergeGenderedStrings($t['etd4'], $t['etd12'], Locale::EN), $this->textData[$t['etd6']][Locale::EN->value] ?? '',
                $this->mergeGenderedStrings($t['etd0'], $t['etd8'], Locale::FR), $this->mergeGenderedStrings($t['etd1'], $t['etd9'], Locale::FR), $this->textData[$t['etd2']][Locale::FR->value] ?? '', $this->mergeGenderedStrings($t['etd4'], $t['etd12'], Locale::FR), $this->textData[$t['etd6']][Locale::FR->value] ?? '',
                $this->mergeGenderedStrings($t['etd0'], $t['etd8'], Locale::DE), $this->mergeGenderedStrings($t['etd1'], $t['etd9'], Locale::DE), $this->textData[$t['etd2']][Locale::DE->value] ?? '', $this->mergeGenderedStrings($t['etd4'], $t['etd12'], Locale::DE), $this->textData[$t['etd6']][Locale::DE->value] ?? '',
                $this->mergeGenderedStrings($t['etd0'], $t['etd8'], Locale::CN), $this->mergeGenderedStrings($t['etd1'], $t['etd9'], Locale::CN), $this->textData[$t['etd2']][Locale::CN->value] ?? '', $this->mergeGenderedStrings($t['etd4'], $t['etd12'], Locale::CN), $this->textData[$t['etd6']][Locale::CN->value] ?? '',
                $this->mergeGenderedStrings($t['etd0'], $t['etd8'], Locale::ES), $this->mergeGenderedStrings($t['etd1'], $t['etd9'], Locale::ES), $this->textData[$t['etd2']][Locale::ES->value] ?? '', $this->mergeGenderedStrings($t['etd4'], $t['etd12'], Locale::ES), $this->textData[$t['etd6']][Locale::ES->value] ?? '',
                $this->mergeGenderedStrings($t['etd0'], $t['etd8'], Locale::RU), $this->mergeGenderedStrings($t['etd1'], $t['etd9'], Locale::RU), $this->textData[$t['etd2']][Locale::RU->value] ?? '', $this->mergeGenderedStrings($t['etd4'], $t['etd12'], Locale::RU), $this->textData[$t['etd6']][Locale::RU->value] ?? ''
            );
        }

        // i have no idea, how the indexing in this file works.
        // sometimes the \d+ after EMOTE is the emoteTextId, but not nearly often enough
        $aliasses = $voiceAliases = [];
        foreach (CLISetup::searchGlobalStrings('/^EMOTE(\d+)_CMD\d+ = \"\/([^"]+)\";$/') as $locId => [, $etID, $cmd])
            $aliasses[$etID][] = [$locId, $cmd];

        foreach (CLISetup::searchGlobalStrings('/^VOICEMACRO_LABEL_([A-Z]+)\d+ = \"([^"]+)\";$/') as $locId => [, $cmd, $alias])
            $voiceAliases[$cmd][] = [$locId, $alias];

        $emotes = DB::Aowow()->selectCol('SELECT id AS ARRAY_KEY, cmd FROM ::emotes');

        foreach($emotes as $eId => $cmd)
        {
            foreach ($voiceAliases[strtoupper($cmd)] ?? [] as [$locId, $alias])
                DB::Aowow()->qry('INSERT IGNORE INTO ::emotes_aliasses VALUES (%i, %i, %s) ON DUPLICATE KEY UPDATE `locales` = `locales` | %i', $eId, (1 << $locId), mb_strtolower($alias), (1 << $locId));

            foreach ($aliasses as $data)
            {
                if (!in_array($cmd, array_column($data, 1)))
                    continue;

                foreach ($data as [$locId, $alias])
                    DB::Aowow()->qry('INSERT IGNORE INTO ::emotes_aliasses VALUES (%i, %i, %s) ON DUPLICATE KEY UPDATE `locales` = `locales` | %i', $eId, (1 << $locId), mb_strtolower($alias), (1 << $locId));

                break;
            }
        }

        DB::Aowow()->qry('UPDATE ::emotes e LEFT JOIN ::emotes_aliasses ea ON ea.`id` = e.`id` SET e.`cuFlags` = e.`cuFlags` | %i WHERE ea.`id` IS NULL', CUSTOM_EXCLUDE_FOR_LISTVIEW | EMOTE_CU_MISSING_CMD);


        /*********************/
        /* Server controlled */
        /*********************/

        DB::Aowow()->qry('INSERT INTO ::emotes (`id`, `cmd`, `flags`, `isAnimated`, `parentEmote`, `soundId`, `state`, `stateParam`, `cuFlags`) SELECT -`id`, `name`, `flags`, IF(`animationId`, 1, 0), 0, `soundId`, `state`, `stateParam`, %i FROM dbc_emotes WHERE 1', CUSTOM_EXCLUDE_FOR_LISTVIEW);


        $this->reapplyCCFlags('emotes', Type::EMOTE);

        return $allOK;
    }

    private function mergeGenderedStrings(int $maleTextId, int $femaleTextId, Locale $loc) : string
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
