<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


    // Create 'item-scaling'-file in datasets;
    $reqDBC =  ['scalingstatdistribution', 'scalingstatvalues', 'gtoctclasscombatratingscalar', 'gtcombatratings'];

    function debugify($data)
    {
        $buff = [];
        foreach ($data as $id => $row)
        {
            foreach ($row as &$r)
                $r = str_pad($r, 5, " ", STR_PAD_LEFT);

            $buff[] = str_pad($id, 7, " ", STR_PAD_LEFT).": [".implode(', ', $row)."]";
        }

        return "{\r\n".implode(",\r\n", $buff)."\r\n}";
    }

    function itemScalingRB()
    {
        $ratings = array(
            12 =>  1,                                       // ITEM_MOD_DEFENSE_SKILL_RATING     => CR_DEFENSE_SKILL
            13 =>  2,                                       // ITEM_MOD_DODGE_RATING             => CR_DODGE
            14 =>  3,                                       // ITEM_MOD_PARRY_RATING             => CR_PARRY
            15 =>  4,                                       // ITEM_MOD_BLOCK_RATING             => CR_BLOCK
            16 =>  5,                                       // ITEM_MOD_HIT_MELEE_RATING         => CR_HIT_MELEE
            17 =>  6,                                       // ITEM_MOD_HIT_RANGED_RATING        => CR_HIT_RANGED
            18 =>  7,                                       // ITEM_MOD_HIT_SPELL_RATING         => CR_HIT_SPELL
            19 =>  8,                                       // ITEM_MOD_CRIT_MELEE_RATING        => CR_CRIT_MELEE
            20 =>  9,                                       // ITEM_MOD_CRIT_RANGED_RATING       => CR_CRIT_RANGED
            21 => 10,                                       // ITEM_MOD_CRIT_SPELL_RATING        => CR_CRIT_SPELL
            22 => 11,                                       // ITEM_MOD_HIT_TAKEN_MELEE_RATING   => CR_HIT_TAKEN_MELEE
            23 => 12,                                       // ITEM_MOD_HIT_TAKEN_RANGED_RATING  => CR_HIT_TAKEN_RANGED
            24 => 13,                                       // ITEM_MOD_HIT_TAKEN_SPELL_RATING   => CR_HIT_TAKEN_SPELL
            25 => 14,                                       // ITEM_MOD_CRIT_TAKEN_MELEE_RATING  => CR_CRIT_TAKEN_MELEE         [may be forced 0]
            26 => 15,                                       // ITEM_MOD_CRIT_TAKEN_RANGED_RATING => CR_CRIT_TAKEN_RANGED        [may be forced 0]
            27 => 16,                                       // ITEM_MOD_CRIT_TAKEN_SPELL_RATING  => CR_CRIT_TAKEN_SPELL         [may be forced 0]
            28 => 17,                                       // ITEM_MOD_HASTE_MELEE_RATING       => CR_HASTE_MELEE
            29 => 18,                                       // ITEM_MOD_HASTE_RANGED_RATING      => CR_HASTE_RANGED
            30 => 19,                                       // ITEM_MOD_HASTE_SPELL_RATING       => CR_HASTE_SPELL
            31 => 5,                                        // ITEM_MOD_HIT_RATING               => [backRef]
            32 => 8,                                        // ITEM_MOD_CRIT_RATING              => [backRef]
            33 => 11,                                       // ITEM_MOD_HIT_TAKEN_RATING         => [backRef]                   [may be forced 0]
            34 => 14,                                       // ITEM_MOD_CRIT_TAKEN_RATING        => [backRef]                   [may be forced 0]
            35 => 14,                                       // ITEM_MOD_RESILIENCE_RATING        => [backRef]
            36 => 17,                                       // ITEM_MOD_HASTE_RATING             => [backRef]
            37 => 23,                                       // ITEM_MOD_EXPERTISE_RATING         => CR_EXPERTISE
            44 => 24                                        // ITEM_MOD_ARMOR_PENETRATION_RATING => CR_ARMOR_PENETRATION
        );

        $data = $ratings;

        $offsets = array_map(function ($v) {                // LookupEntry(cr*GT_MAX_LEVEL+level-1)
            return $v * 100 + 60 - 1;
        }, $ratings);
        $base = DB::Aowow()->selectCol('SELECT CAST((idx + 1 - 60) / 100 AS UNSIGNED) AS ARRAY_KEY, ratio FROM dbc_gtcombatratings WHERE idx IN (?a)', $offsets);

        $offsets = array_map(function ($v) {                // LookupEntry((getClass()-1)*GT_MAX_RATING+cr+1)
            return (CLASS_WARRIOR - 1) * 32 + $v + 1;
        }, $ratings);
        $mods = DB::Aowow()->selectCol('SELECT idx - 1 AS ARRAY_KEY, ratio FROM dbc_gtoctclasscombatratingscalar WHERE idx IN (?a)', $offsets);

        foreach ($data as $itemMod => &$val)
            $val = Cfg::get('DEBUG') ? $base[$val].' / '.$mods[$val] : $base[$val] / $mods[$val];

        if (!Cfg::get('DEBUG'))
            return Util::toJSON($data);

        $buff = [];
        foreach ($data as $k => $v)
            $buff[] = $k.': '.$v;

        return "{\r\n    ".implode(",\r\n    ", $buff)."\r\n}";
    }

    function itemScalingSV()
    {
        /* so the javascript expects a slightly different structure, than the dbc provides .. f*** it
           e.g.
           dbc      - 80:    97   97   56        41  210  395  878  570  120  156   86  112  108  220  343        131   73  140  280  527 1171 2093
           expected - 80:    97   97   56  131   41  210  395  878 1570  120  156   86  112  108  220  343     0    0   73  140  280  527 1171 2093
        */
        $fields = Util::$ssdMaskFields;
        array_walk($fields, function(&$v, $k) {
            $v = $v ?: '0 AS idx'.$k;                       // NULL => 0 (plus some index so we can have 2x 0)
        });

        $data = DB::Aowow()->select('SELECT id AS ARRAY_KEY, '.implode(', ', $fields).'  FROM dbc_scalingstatvalues');
        foreach ($data as &$d)
            $d = array_values($d);                          // strip indizes

        return Cfg::get('DEBUG') ? debugify($data) : Util::toJSON($data);
    }

    function itemScalingSD()
    {
        $data = DB::Aowow()->select('SELECT *, id AS ARRAY_KEY FROM dbc_scalingstatdistribution');
        foreach ($data as &$row)
        {
            $row = array_values($row);
            array_splice($row, 0, 1);
        }

        return Cfg::get('DEBUG') ? debugify($data) : Util::toJSON($data);
    }
?>
