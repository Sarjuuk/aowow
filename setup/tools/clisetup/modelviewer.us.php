<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/***************************/
/* build modelviewer files */
/***************************/

CLISetup::registerUtility(new class extends UtilityScript
{
    public $argvFlags   = CLISetup::ARGV_ARRAY | CLISetup::ARGV_OPTIONAL;
    public $optGroup    = CLISetup::OPT_GRP_UTIL;

    public const COMMAND     = 'modelviewer';
    public const DESCRIPTION = 'TBD Build Modelviewer files.';

    // sqlToDo, buildToDo, null, null // iinn
    public function run(&$args) : bool
    {
        // will probably require trial and error to see what col corresponds to what flag value
        $hgvd = DB::Aowow()->select('SELECT id AS ARRAY_KEY, x.* FROM dbc_helmetgeosetvisdata x');
        $helmetGeosetData = [];
        foreach (ChrRace::cases() as $ra)
        {
            if ($ra->getSide() == SIDE_NONE)
                continue;

            foreach ($hgvd as $id => $row)
            {
                $helmetGeosetData[$id][$ra->value] = 0;

                if ($row['hairFlags'] & $ra->toMask())
                    $helmetGeosetData[$id][$ra->value] |= 0x1;
                if ($row['face1Flags'] & $ra->toMask())
                    $helmetGeosetData[$id][$ra->value] |= 0x2;
                if ($row['face2Flags'] & $ra->toMask())
                    $helmetGeosetData[$id][$ra->value] |= 0x4;
                if ($row['face3Flags'] & $ra->toMask())
                    $helmetGeosetData[$id][$ra->value] |= 0x8;
                if ($row['earFlags'] & $ra->toMask())
                    $helmetGeosetData[$id][$ra->value] |= 0x10;
                if ($row['miscFlags'] & $ra->toMask())
                    $helmetGeosetData[$id][$ra->value] |= 0x20;
                if ($row['eyeFlags'] & $ra->toMask())
                    $helmetGeosetData[$id][$ra->value] |= 0x40;
            }
        }


        require_once 'includes/setup/modelviewer/shared.php';
        require_once 'includes/setup/modelviewer/datatypes/primitives.php';
        require_once 'includes/setup/modelviewer/datatypes/composites.php';

        $m2File = new M2File('setup/tmp-m2parsing/G_GongTroll01.m2');
        // $m2File = new M2File('setup/tmp-m2parsing/Stave_2H_OutlandRaid_D_05.m2');

        $m2File->writeMO3('setup/tmp-m2parsing/');
        // var_dump($m2File);

        return true;
    }
})

?>
