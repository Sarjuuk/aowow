<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/*  seems to be unused, was located in the same file as g_realms
    var g_regions = {
        us => 'www.wowarmory.com',
        eu => 'eu.wowarmory.com'
    };
*/

/* Examples
    1 => {
        name:'Eldre\'Thalas',
        battlegroup:'Reckoning',
        region:'us'
    },
*/

CLISetup::registerSetup("build", new class extends SetupScript
{
    protected $info = array(
        'realms' => [[], CLISetup::ARGV_PARAM, 'Generates \'realms\'-file to be referenced by the profiler tool.']
    );

    protected $worldDependency = ['realmlist'];
    protected $requiredDirs    = ['datasets/'];

    public function generate() : bool
    {
        $realms = [];

        if (!DB::isConnectable(DB_AUTH))
            CLI::write('[realms] Auth DB not set up .. static data g_realms will be empty', CLI::LOG_WARN);
        else if (!($realms = Profiler::getRealms()))
            CLI::write('[realms] no viable realms found .. static data g_realms will be empty', CLI::LOG_WARN);
        // else
            // foreach ($realms as &$r)
                // $r['battlegroup'] = Cfg::get('BATTLEGROUP');

        // remove access column
        array_walk($realms, function (&$x) { unset($x['access']); });

        $toFile = "var g_realms = ".Util::toJSON($realms).";";
        $file   = 'datasets/realms';

        return CLISetup::writeFile($file, $toFile);
    }
});

?>
