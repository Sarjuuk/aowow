<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("build", new class extends SetupScript
{
    use TrTemplateFile;

    protected $info = array(
        'itemscaling' => [[], CLISetup::ARGV_PARAM, 'Compiles item scaling data to file to make heirloom tooltips interactive.']
    );

    protected $fileTemplateDest = ['datasets/item-scaling'];
    protected $fileTemplateSrc  = ['item-scaling.in'];

    protected $dbcSourceFiles   = ['scalingstatdistribution', 'scalingstatvalues', 'gtoctclasscombatratingscalar', 'gtcombatratings'];

    private function debugify(array $data) : string
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

    private function itemScalingRB() : string
    {
        // data and format observed via wayback machine. Not entirely sure about the redunacy within the combat ratings though.
        $ratings = array(
            Stat::DEFENSE_RTG           => CR_DEFENSE_SKILL,
            Stat::DODGE_RTG             => CR_DODGE,
            Stat::PARRY_RTG             => CR_PARRY,
            Stat::BLOCK_RTG             => CR_BLOCK,
            Stat::MELEE_HIT_RTG         => CR_HIT_MELEE,
            Stat::RANGED_HIT_RTG        => CR_HIT_RANGED,
            Stat::SPELL_HIT_RTG         => CR_HIT_SPELL,
            Stat::MELEE_CRIT_RTG        => CR_CRIT_MELEE,
            Stat::RANGED_CRIT_RTG       => CR_CRIT_RANGED,
            Stat::SPELL_CRIT_RTG        => CR_CRIT_SPELL,
            Stat::MELEE_HIT_TAKEN_RTG   => CR_HIT_TAKEN_MELEE,
            Stat::RANGED_HIT_TAKEN_RTG  => CR_HIT_TAKEN_RANGED,
            Stat::SPELL_HIT_TAKEN_RTG   => CR_HIT_TAKEN_SPELL,
            Stat::MELEE_CRIT_TAKEN_RTG  => CR_CRIT_TAKEN_MELEE,     // may be forced 0
            Stat::RANGED_CRIT_TAKEN_RTG => CR_CRIT_TAKEN_RANGED,    // may be forced 0
            Stat::SPELL_CRIT_TAKEN_RTG  => CR_CRIT_TAKEN_SPELL,     // may be forced 0
            Stat::MELEE_HASTE_RTG       => CR_HASTE_MELEE,
            Stat::RANGED_HASTE_RTG      => CR_HASTE_RANGED,
            Stat::SPELL_HASTE_RTG       => CR_HASTE_SPELL,
            Stat::HIT_RTG               => CR_HIT_MELEE,
            Stat::CRIT_RTG              => CR_CRIT_MELEE,
            Stat::HIT_TAKEN_RTG         => CR_HIT_TAKEN_MELEE,      // may be forced 0
            Stat::CRIT_TAKEN_RTG        => CR_CRIT_TAKEN_MELEE,     // may be forced 0
            Stat::RESILIENCE_RTG        => CR_CRIT_TAKEN_MELEE,
            Stat::HASTE_RTG             => CR_HASTE_MELEE,
            Stat::EXPERTISE_RTG         => CR_EXPERTISE,
            Stat::ARMOR_PENETRATION_RTG => CR_ARMOR_PENETRATION
        );

        $data = $ratings;

        $offsets = array_map(function ($v) {                // LookupEntry(cr*GT_MAX_LEVEL+level-1)
            return $v * 100 + 60 - 1;                       // combat rating where introduced during the transition vanilla > burnig crusade. So at level 60 (at the time) the rating on the item was equal to 1% effect and is still the baseline in 3.3.5a.
        }, $ratings);
        $base = DB::Aowow()->selectCol('SELECT CAST((idx + 1 - 60) / 100 AS UNSIGNED) AS ARRAY_KEY, ratio FROM dbc_gtcombatratings WHERE idx IN (?a)', $offsets);

        /*  non-1 scaler in 3.3.5.12340
            | ratingId | classId | ratio |
            |       17 |       2 |   1.3 |
            |       17 |       6 |   1.3 |
            |       17 |       7 |   1.3 |
            |       17 |      11 |   1.3 |
            |       24 | < all > |   1.1 |
        */

        $offsets = array_map(function ($v) {                // LookupEntry((getClass()-1)*GT_MAX_RATING+cr+1)
            return (ChrClass::WARRIOR->value - 1) * 32 + $v + 1; // should this be dynamic per pinned character? ITEM_MOD HASTE has a worse scaler for a subset of classes (see table)
        }, $ratings);
        $mods = DB::Aowow()->selectCol('SELECT idx - 1 AS ARRAY_KEY, ratio FROM dbc_gtoctclasscombatratingscalar WHERE idx IN (?a)', $offsets);

        foreach ($data as &$val)
            $val = Cfg::get('DEBUG') ? $base[$val].' / '.$mods[$val] : $base[$val] / $mods[$val];

        if (!Cfg::get('DEBUG'))
            return Util::toJSON($data);

        $buff = [];
        foreach ($data as $k => $v)
            $buff[] = $k.': '.$v;

        return "{\r\n    ".implode(",\r\n    ", $buff)."\r\n}";
    }

    private function itemScalingSV() : string
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

        $data = DB::Aowow()->select('SELECT id AS ARRAY_KEY, '.implode(', ', $fields).' FROM dbc_scalingstatvalues');
        foreach ($data as &$d)
            $d = array_values($d);                          // strip indizes

        return Cfg::get('DEBUG') ? $this->debugify($data) : Util::toJSON($data);
    }

    private function itemScalingSD() : string
    {
        $data = DB::Aowow()->select('SELECT *, id AS ARRAY_KEY FROM dbc_scalingstatdistribution');
        foreach ($data as &$row)
        {
            $row = array_values($row);
            array_splice($row, 0, 1);
        }

        return Cfg::get('DEBUG') ? $this->debugify($data) : Util::toJSON($data);
    }
})

?>
