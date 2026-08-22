<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/******************/
/* MPQ Extraction */
/******************/

CLISetup::registerUtility(new class extends UtilityScript
{
    public $argvOpts = ['e'];
    public $optGroup = CLISetup::OPT_GRP_SETUP;

    public const COMMAND       = 'extract';
    public const DESCRIPTION   = 'Extract (audio) files from client archives.';
    public const APPENDIX      = 'mpqFile extractPattern [mpqFile2 extractPettern2 [...]]';
    public const NOTE_START    = '[extract] begin extraction of:';
    public const NOTE_END_OK   = 'successfully finished extracting files';
    public const NOTE_END_FAIL = 'finished mpq extraction with errors';

    public const USE_CLI_ARGS = true;

    // ordered oldest > newset
    private const /* array */  LOCALE_MPQS = ['locale-%1$s.mpq', 'expansion-locale-%1$s.mpq', 'lichking-locale-%1$s.mpq',
                                              'speech-%1$s.mpq', 'expansion-speech-%1$s.mpq', 'lichking-speech-%1$s.mpq'];
    private const /* array */  COMMON_MPQS = ['common.mpq',      'expansion.mpq',             'lichking.mpq',           ]; // 'common-2.mpq'?
    private const /* string */ PATCH_FMT   = 'patch%1$s%2$s.mpq';

    private const /* array */ GLOBS = array(
        // 'sound\*',
        // localized
        'dbfilesclient\*.dbc',
        'interface\worldmap\*.blp',
        'interface\framexml\globalstrings.lua',
        // agnositic
        'interface\talentframe\*.blp',
        'interface\icons\*.blp',
        'interface\spellbook\*.blp',
        'interface\paperdoll\*.blp',
        'interface\pictures\*.blp',
        'interface\pvprankbadges\*.blp',
        'interface\flavorimages\*.blp',
        'interface\glues\charactercreate\*.blp',
        'interface\calendar\holidays\*.blp',
        'interface\pvpframe\*.blp',
        // agnostic goodies
        'interface\glues\loadingscreens\*.blp',
        'interface\glues\credits\*.blp'
    );

    private $fields = array(
        'mpqArchive'  => ['Path to mpq archive',     false],
        'filePattern' => ['Glob pattern to extract', false]
    );

    // args: mpqArchive, filePattern, null, null // iinn
    public function run(&$args) : bool
    {
        $mpqArchive  = [$args[0]] ?? [];
        $filePattern = [$args[1]] ?? [];

        if ($mpqArchive)
            unset($this->fields['mpqArchive']);
        if ($filePattern)
            unset($this->fields['filePattern']);

/*
        if ($this->fields && CLI::read($this->fields, $uiExtract) && $uiExtract)
        {
            CLI::write();

            $mpqArchive  = [$uiExtract['mpqArchive']]  ?: [];
            $filePattern = [$uiExtract['filePattern']] ?: [];
        }
        else if ($this->fields)
        {
            CLI::write();
            CLI::write("[extract] file extraction aborted", CLI::LOG_INFO);
            CLI::write();
            return true;
        }

        if (!$mpqArchive || !$filePattern || count($mpqArchive) != count($filePattern))
            return false;
 */

        CLI::write('creating localized archive handles..', CLI::LOG_BLANK, tmpRow: true);

        $localeMPQ = [];
        foreach (CLISetup::$locales as $locId => $loc)
        {
            foreach (self::LOCALE_MPQS as $mpqName)
            {
                foreach (array_filter($loc->gameDirs()) as $subDir)
                {
                    if (!($rp = realpath(CLI::nicePath(sprintf($mpqName, $subDir), CLISetup::$mpqDir, $subDir))))
                        continue;

                    if (!isset($localeMPQ[$locId]))
                        $localeMPQ[$locId] = MPQArchive::open($rp, 'includes/setup/StormLib/StormLib.dll');
                    else
                        $localeMPQ[$locId]->applyPatch($rp);
                    break;
                }
            }

            self::applyPatches($localeMPQ[$locId], $loc);
        }

        CLI::write('creating localized archive handles.. done!', CLI::LOG_BLANK);

        CLI::write('creating common archive handles..', CLI::LOG_BLANK, tmpRow: true);

        $commonMPQ = null;
        foreach (self::COMMON_MPQS as $mpqName)
        {
            if (!($rp = realpath(CLI::nicePath($mpqName, CLISetup::$mpqDir))))
                continue;

            if (!isset($commonMPQ))
                $commonMPQ = MPQArchive::open($rp, 'includes/setup/StormLib/StormLib.dll');
            else
                $commonMPQ->applyPatch($rp);
        }

        self::applyPatches($commonMPQ);

        CLI::write('creating common archive handles.. done!', CLI::LOG_BLANK);

        foreach (self::GLOBS as $search)
        {
            foreach (CLISetup::$locales as $locId => $loc)
            {
                $foundFiles = $localeMPQ[$locId]->search($search);
                $nFound     = count($foundFiles);
                CLI::write(CLI::bold($search).' - Results: '.$nFound);

                foreach ($foundFiles as $i => $file)
                {
                    CLI::write('extracting '.str_pad($i, strlen($nFound), ' ', STR_PAD_LEFT).'/'.$nFound, tmpRow: true);
                    $localeMPQ[$locId]->readFile($file, CLISetup::$srcDir);
                }
            }
        }

        CLI::write();

        return true;
    }

    public function writeCLIHelp() : bool
    {
        CLI::write('  usage: php aowow --extract mpqFile extractPattern [--datasrc: --mpqsrc: --locales:]', -1, false);
        CLI::write();
        CLI::write('  [TBD] extracts files matching '.CLI::bold('extractPattern').' from --mpqsrc to --datasrc', -1, false);
        CLI::write();

        return true;
    }

    private static function applyPatches(MPQArchive $mpq, ?Locale $loc = null) : void
    {
        foreach(str_split('023456789ABCDEFGHIJKLMNOPQRSTUVWXYZ') as $i)
            foreach ($loc ? $loc->gameDirs() : [''] as $subDir)
                if ($rp = realpath(CLI::nicePath(sprintf(self::PATCH_FMT, $subDir ? '-'.$subDir : '', $i ? '-'.$i : ''), CLISetup::$mpqDir, $subDir)))
                    $mpq->applyPatch($rp);
    }
});

?>
