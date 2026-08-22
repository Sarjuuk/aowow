<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("build", new class extends SetupScript
{
    protected $info = array(
        'clientfiles' => [[], CLISetup::ARGV_PARAM, 'Extracts files from MPQ archives. (DBC tables need merging and OGG sounds need conversion)']
    );

    private const IDX_LOCALIZED = 0;
    private const IDX_COMMON    = 1;

    // ordered oldest > newset
    private const /* array */  MPQ_FILES = array(
        self::IDX_LOCALIZED => array (
            'locale-%1$s.mpq', 'expansion-locale-%1$s.mpq', 'lichking-locale-%1$s.mpq',
            'speech-%1$s.mpq', 'expansion-speech-%1$s.mpq', 'lichking-speech-%1$s.mpq'
        ),
        self::IDX_COMMON => array(
            'common.mpq',      'expansion.mpq',             'lichking.mpq' // 'common-2.mpq'?
        )
    );
    private const /* string */ PATCH_FMT   = 'patch%1$s%2$s.mpq';

    /** @var array{string, int, int}[] - [glob, locTypeMask, localeMask] */
    private array $globs = array(
        ['sound\*',                              0x3, 0],
        ['dbfilesclient\*.dbc',                  0x2, 0],
        ['interface\framexml\globalstrings.lua', 0x2, 0]
    );

    public function __construct()
    {
        if (OS_WIN && ($rp = realpath('includes/libs/bin/StormLib.dll')))
            CLISetup::$stormLib = $rp;
        else if (!OS_WIN && ($rp = realpath(CLI::nicePath('~/lib/libStormLib.so'))))
            CLISetup::$stormLib = $rp;
        else if (!OS_WIN && ($rp = realpath(CLI::nicePath('~/lib/local/libStormLib.so'))))
            CLISetup::$stormLib = $rp;
        else if (CLI::read(['WHERE\'s MAH LIB!?'], $uiLibPath) && $uiLibPath)
            CLISetup::$stormLib = $uiLibPath[0];
    }

    public function generate() : bool
    {
        foreach ($this->globs as $i => [$glob, &$locTypeMask, &$localeMask])
        {
            // unset locTypeMask bit if completely found

            // set localeMask bits of locales needing extraction

            // unset entire glob if complete

            // $done = true;
            // if ($locTypeMask & (1 << self::IDX_COMMON) && false /* common test failed */)
            //     $done = false;
            // if ($locTypeMask & (1 << self::IDX_LOCALIZED) && false /* common test failed */)
            //     $done = false;


        }

        if (!CLISetup::$stormLib && $this->globs)
        {
            CLI::write('[clientfiles] client files are incomplete and StormLib was not found!', CLI::LOG_ERROR);
            return false;
        }

        $mpqArchive = null;
        foreach (CLISetup::$locales as $loc)
        {
            $logString = 'loading archive ';
            foreach (self::MPQ_FILES[self::IDX_LOCALIZED] as $mpqName)
            {
                foreach (array_filter($loc->gameDirs()) as $subDir)
                {
                    if (!($rp = realpath(CLI::nicePath(sprintf($mpqName, $subDir), CLISetup::$mpqDir, $subDir))))
                        continue;

                    if (!isset($mpqArchive))
                    {
                        $mpqArchive = MPQArchive::open($rp, CLISetup::$stormLib);
                        CLI::write(($logString .= CLI::bold(sprintf($mpqName, $subDir))), tmpRow: true);
                    }
                    else
                    {
                        $mpqArchive->applyPatch($rp);
                        CLI::write(($logString .= ' << '.sprintf($mpqName, $subDir)), tmpRow: true);
                    }
                    break;
                }
            }

            if (!$mpqArchive)
            {
                CLI::write('[clientfiles] failed to open archives '.implode(', ', array_map(fn($x) => sprintf($x, $loc->gameDirs()[0]), self::MPQ_FILES[self::IDX_LOCALIZED])));
                return false;
            }

            self::applyPatches($mpqArchive, $logString, $loc);

            foreach (array_filter($this->globs, fn($x) => $x[1] & self::IDX_LOCALIZED) as [$glob, , $locMask])
            {
                // extract per glob and locMask
            }

            $mpqArchive = null;                             // this does trigger __destroy(), right?
        }

/*
        CLI::write('creating common archive handles..', CLI::LOG_BLANK, tmpRow: true);

        $commonMPQ = null;
        foreach (self::MPQ_FILES[self::IDX_COMMON] as $mpqName)
        {
            if (!($rp = realpath(CLI::nicePath($mpqName, CLISetup::$mpqDir))))
                continue;

            if (!isset($commonMPQ))
                $commonMPQ = MPQArchive::open($rp, 'includes/setup/StormLib/StormLib.dll');
            else
                $commonMPQ->applyPatch($rp);
        }

        self::applyPatches($commonMPQ, $logString);

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
 */
        CLI::write();

        return $this->success;
    }

    private static function applyPatches(MPQArchive $mpq, string $logString, ?Locale $loc = null) : void
    {
        foreach(str_split('023456789ABCDEFGHIJKLMNOPQRSTUVWXYZ') as $i)
        {
            foreach ($loc ? array_filter($loc->gameDirs()) : [''] as $subDir)
            {
                if ($rp = realpath(CLI::nicePath(sprintf(self::PATCH_FMT, $subDir ? '-'.$subDir : '', $i ? '-'.$i : ''), CLISetup::$mpqDir, $subDir)))
                {
                    $mpq->applyPatch($rp);
                    CLI::write(($logString .= ' << '.sprintf(self::PATCH_FMT, $subDir ? '-'.$subDir : '', $i ? '-'.$i : '')), tmpRow: true);
                }
            }
        }

        // write permanent
        CLI::write($logString);
    }
});

?>
