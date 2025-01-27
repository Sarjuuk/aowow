<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/* Example
    var mn_profiles = [
        ["us","US & Oceanic",,[
            ["bloodlust","Bloodlust",,[
                ["amanthul","Aman'Thul"],
                ["barthilas","Barthilas"]
            ]],
            ["cyclone","Cyclone",,[
                ["azjol-nerub","Azjol-Nerub"],
                ["bloodscalp","Bloodscalp"]
            ]]
        ]],
        ["eu","Europe",,[
            ["blackout","Blackout",,[
                ["agamaggan","Agamaggan"],
                ["aggramar","Aggramar"]
            ]],
            ["blutdurst","Blutdurst",,[
                ["aegwynn","Aegwynn"],
                ["destromath","Destromath"]
            ]]
        ]]
    ];
*/

CLISetup::registerSetup("build", new class extends SetupScript
{
    use TrTemplateFile;

    protected $info = array(
        'realmmenu' => [[], CLISetup::ARGV_PARAM, 'Generates \'profile_all.js\'-file that extends the profiler menus.']
    );

    protected $fileTemplateSrc  = ['profile_all.js.in'];
    protected $fileTemplateDest = ['static/js/profile_all.js'];
    protected $worldDependency  = ['realmlist'];

    private function realmMenu() : string
    {
        $subs = [];
        $set  = 0x0;
        $menu = [
            // skip usage of battlegroup
            // ['us', Lang::profiler('regions', 'us'), null,[[Profiler::urlize(Cfg::get('BATTLEGROUP')), Cfg::get('BATTLEGROUP'), null, &$subUS]]],
            // ['eu', Lang::profiler('regions', 'eu'), null,[[Profiler::urlize(Cfg::get('BATTLEGROUP')), Cfg::get('BATTLEGROUP'), null, &$subEU]]]
        ];

        foreach (Util::$regions as $idx => $n)
            $subs[$idx] = [];

        if (!DB::isConnectable(DB_AUTH))
            CLI::write('[realmmenu] Auth DB not set up .. realm menu will be empty', CLI::LOG_WARN);
        else
            foreach (Profiler::getRealms() as $row)
            {
                $idx = array_search($row['region'], Util::$regions);
                if ($idx === false)
                    continue;

                $set |= (1 << $idx);
                $subs[$idx][] = [Profiler::urlize($row['name'], true), $row['name'], null, null, $row['access'] ? ['requiredAccess' => $row['access']] : null];
            }

        if (!$set)
            CLI::write('[realmmenu] no viable realms found .. realm menu will be empty', CLI::LOG_WARN);

        // why is this file not localized!?
        Lang::load(Locale::EN);

        foreach (Util::$regions as $idx => $n)
            if ($set & (1 << $idx))
                $menu[] = [$n, Lang::profiler('regions', $n), null, &$subs[$idx]];

        return Util::toJSON($menu);
    }
});

?>
