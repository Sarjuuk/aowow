<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("build", new class extends SetupScript
{
    protected array $info = array(
        'soundfiles' => [[], CLISetup::ARGV_PARAM, 'Links converted sound files to database and moves them to destination.']
    );

    protected array $requiredDirs = ['static/wowsounds/'];
    protected array $setupAfter   = [['sounds'], []];

    private function safeCopy(string $src, string $dst) : bool
    {
        $tmp = $dst . '.tmp.' . getmypid() . '.' . uniqid('', true);
        if (!@copy($src, $tmp))
        {
            @unlink($tmp);
            return false;
        }

        if (!@rename($tmp, $dst))
        {
            // Windows cannot rename over an existing file
            if (!@unlink($dst) || !@rename($tmp, $dst))
            {
                @unlink($tmp);
                return false;
            }
        }

        return true;
    }

    private function linkOrCopy(string $relTarget, string $absTarget, string $linkPath) : bool
    {
        $tmp = $linkPath . '.tmp.' . getmypid() . '.' . uniqid('', true);

        if (!@symlink($relTarget, $tmp) && !$this->safeCopy($absTarget, $tmp))
        {
            @unlink($tmp);
            return false;
        }

        if (@rename($tmp, $linkPath))
            return true;

        // Windows cannot rename over an existing file
        if (!@unlink($linkPath) || !@rename($tmp, $linkPath))
        {
            @unlink($tmp);
            return false;
        }

        return true;
    }

    public function generate() : bool
    {
        $commonDir = 'static/wowsounds/common';
        if (!CLISetup::writeDir($commonDir))
            return false;

        // EN is always searched and generated as the fallback source, but a partial run must not rewrite its audio
        $targetLocales = CLISetup::$locales;
        $searchLocales = $targetLocales;
        if (!isset($searchLocales[Locale::EN->value]))
            $searchLocales[Locale::EN->value] = Locale::EN;
        $requestedKeys = array_map(fn(Locale $l) => $l->json(), $targetLocales);

        // URLs are built for every config-enabled locale, even when --locales limits which sources are built
        $outputLocales = [];
        foreach (Locale::cases() as $loc)
            if ($loc->validate())
                $outputLocales[$loc->value] = $loc;
        foreach ($searchLocales as $loc)
            $outputLocales[$loc->value] = $loc;

        foreach ($outputLocales as $loc)
        {
            if (!CLISetup::writeDir('static/wowsounds/' . $loc->json()))
                return false;
        }

        // remove legacy flat files from older single-locale builds
        foreach (glob('static/wowsounds/*') ?: [] as $legacy)
        {
            if (is_file($legacy) && ctype_digit(basename($legacy)))
                @unlink($legacy);
        }

        // a zero mask means "all supported locales" - same as Locale::validate()
        $cfgMask       = (int)Cfg::get('LOCALES');
        $targetMask    = array_reduce($targetLocales, fn(int $m, Locale $l) => $m | $l->maskBit(), 0);
        $supportedMask = array_reduce(array_filter(Locale::cases(), fn(Locale $l) => $l->validate()), fn(int $m, Locale $l) => $m | $l->maskBit(), 0);
        $effectiveMask = ($cfgMask ?: $supportedMask) & $supportedMask;
        $isFullRun     = ($effectiveMask & $targetMask) === $effectiveMask;

        // ALL files
        $files  = DB::Aowow()->selectCol('SELECT ABS(`id`) AS ARRAY_KEY, CONCAT(`path`, "/", `file`) FROM ::sounds_files');
        $nFiles = count($files);
        $qtLen  = strlen($nFiles);
        $sum    = 0;
        $time   = new Timer(500);

        foreach ($files as $fileId => $filePath)
        {
            $sum++;
            if ($time->update())
            {
                CLI::write(sprintf('[soundfiles] * %'.$qtLen.'d / %d (%4.1f%%)', $sum,  $nFiles, round(100 * $sum / $nFiles, 1)), CLI::LOG_BLANK, true, true);
                DB::Aowow()->selectCell('SELECT 1');        // keep mysql busy or it may go away
            }

            // expect converted files as file.wav_ or file.mp3_
            $filePath .= '_';

            // locate the source audio file for each locale
            $foundByLoc = [];
            foreach ($searchLocales as $loc)
            {
                $locKey = $loc->json();
                foreach ($loc->gameDirs() as $dir)
                {
                    $p = CLI::nicePath($filePath, CLISetup::$srcDir, $dir);

                    if (CLISetup::fileExists($p))
                    {
                        $foundByLoc[$locKey] = $p;
                        break;
                    }
                }
            }

            if (!$foundByLoc)
            {
                CLI::write('[soundfiles]  - did not find file: '.CLI::bold(CLI::nicePath($filePath, CLISetup::$srcDir, '['.implode(',', array_map(fn($x) => $x->json(), CLISetup::$locales)).']')), CLI::LOG_WARN);
                $time->reset();
                // flag as unusable in DB
                DB::Aowow()->qry('UPDATE ::sounds_files SET `id` = %i WHERE ABS(`id`) = %i', -$fileId, $fileId);
                continue;
            }

            $fileOk        = true;
            $uniqueSources = array_values($foundByLoc);

            // identical audio may ship in several locale directories; deduplicate by content on full runs
            if ($isFullRun && count($uniqueSources) > 1)
            {
                $byContent = [];
                foreach ($uniqueSources as $source)
                {
                    $size = @filesize($source);
                    $hash = $size !== false ? @hash_file('sha256', $source) : false;
                    $byContent[$hash !== false ? $size . ':' . $hash : $source] = $source;
                }

                $uniqueSources = array_values($byContent);
            }

            // Case 1: language-neutral sound (deduplicate into common/ on full locale builds only)
            if (count($uniqueSources) === 1 && $isFullRun)
            {
                $srcFile    = reset($uniqueSources);
                $commonFile = $commonDir . '/' . $fileId;

                if (!$this->safeCopy($srcFile, $commonFile))
                {
                    $this->success = false;
                    $fileOk        = false;
                    CLI::write('[soundfiles]  - could not copy '.CLI::bold($srcFile).' into '.CLI::bold($commonFile), CLI::LOG_ERROR);
                    $time->reset();
                }
                else
                {
                    foreach ($outputLocales as $loc)
                    {
                        $linkPath = 'static/wowsounds/'.$loc->json().'/'.$fileId;
                        if (!$this->linkOrCopy('../common/'.$fileId, $commonFile, $linkPath))
                        {
                            $this->success = false;
                            $fileOk        = false;
                            CLI::write('[soundfiles]  - could not link '.CLI::bold($commonFile).' into '.CLI::bold($linkPath), CLI::LOG_ERROR);
                            $time->reset();
                        }
                    }
                }
            }
            // Case 2: localized voice-overs (different files exist per locale)
            else
            {
                $fallbackKey = isset($foundByLoc['enus']) ? 'enus' : array_key_first($foundByLoc);
                $fallbackAbs = 'static/wowsounds/'.$fallbackKey.'/'.$fileId;

                // a partial rebuild keeps audio that was not requested, including the force-added EN fallback
                $keepFallback = !in_array($fallbackKey, $requestedKeys, true) && (file_exists($fallbackAbs) || is_link($fallbackAbs));

                // copy the fallback first, so locales without their own file can link to it
                if (!$keepFallback && !$this->safeCopy($foundByLoc[$fallbackKey], $fallbackAbs))
                {
                    $this->success = false;
                    $fileOk        = false;
                    CLI::write('[soundfiles]  - could not copy '.CLI::bold($foundByLoc[$fallbackKey]).' into '.CLI::bold($fallbackAbs), CLI::LOG_ERROR);
                    $time->reset();
                }
                else
                {
                    foreach ($outputLocales as $loc)
                    {
                        $locKey  = $loc->json();
                        $dstFile = 'static/wowsounds/'.$locKey.'/'.$fileId;

                        if ($locKey === $fallbackKey)
                            continue;

                        // partial rebuilds must not replace existing audio in locales that were not requested
                        if (!in_array($locKey, $requestedKeys, true) && (file_exists($dstFile) || is_link($dstFile)))
                            continue;

                        if (isset($foundByLoc[$locKey]))
                        {
                            if (!$this->safeCopy($foundByLoc[$locKey], $dstFile))
                            {
                                $this->success = false;
                                $fileOk        = false;
                                CLI::write('[soundfiles]  - could not copy '.CLI::bold($foundByLoc[$locKey]).' into '.CLI::bold($dstFile), CLI::LOG_ERROR);
                                $time->reset();
                            }
                        }
                        else
                        {
                            // this line has no localized version -> fall back to the English/first found file
                            if (!$this->linkOrCopy('../'.$fallbackKey.'/'.$fileId, $fallbackAbs, $dstFile))
                            {
                                $this->success = false;
                                $fileOk        = false;
                                CLI::write('[soundfiles]  - could not link '.CLI::bold($fallbackAbs).' into '.CLI::bold($dstFile), CLI::LOG_ERROR);
                                $time->reset();
                            }
                        }
                    }
                }
            }

            // keep the DB flag in sync with the result of this run
            if ($fileOk)
                DB::Aowow()->qry('UPDATE ::sounds_files SET `id` = %i WHERE `id` = %i', $fileId, -$fileId);
            else
                DB::Aowow()->qry('UPDATE ::sounds_files SET `id` = %i WHERE `id` = %i', -$fileId, $fileId);
        }

        return $this->success;
    }
});

?>
