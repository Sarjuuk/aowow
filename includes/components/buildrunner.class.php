<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/** Run only the datasets used by web administration, with argv and an immutable checkout cwd. */
final class BuildRunner
{
    private const array SCRIPTS = ['globaljs', 'realms', 'realmMenu', 'searchplugin', 'searchboxBody',
        'searchboxScript', 'demo', 'power', 'robots', 'weightPresets'];

    public static function run(array $scripts) : bool
    {
        if (!$scripts || count($scripts) > count(self::SCRIPTS) || !function_exists('proc_open'))
            return false;
        foreach ($scripts as $script)
            if (!is_string($script) || !in_array($script, self::SCRIPTS, true))
                return false;

        $configured = defined('AOWOW_PHP_CLI') ? AOWOW_PHP_CLI : PHP_BINDIR.'/php'.(PHP_OS_FAMILY === 'Windows' ? '.exe' : '');
        if (!is_string($configured) || !preg_match('~^(?:/|[A-Za-z]:[\\\\/])~', $configured))
            return false;
        $binary = realpath($configured);
        $root = dirname(__DIR__, 2);
        if (!$binary || !is_file($binary) || !is_executable($binary) || !is_file($root.'/aowow'))
            return false;

        // PHP_BINARY is php-fpm under FPM; probe the configured absolute CLI path instead.
        if (!self::execute([$binary, '-r', 'exit(PHP_SAPI === "cli" && PHP_VERSION_ID >= 80400 ? 0 : 1);'], $root, 5))
            return false;
        return self::execute([$binary, $root.'/aowow', '--build='.implode(',', $scripts)], $root, 1800);
    }

    private static function execute(array $argv, string $cwd, int $timeout) : bool
    {
        $process = @proc_open($argv, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['redirect', 1]], $pipes,
            $cwd, null, ['bypass_shell' => true]);
        if (!is_resource($process))
            return false;

        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        $deadline = microtime(true) + $timeout;
        $failed = false;
        $tail = '';
        $exit = -1;
        try
        {
            do
            {
                $chunk = fread($pipes[1], 65536);
                if ($chunk === false)
                {
                    $failed = true;
                    break;
                }
                // Preserve the CLI's historical ERR detection without returning build output to HTTP.
                $text = $tail.$chunk;
                $failed = $failed || str_contains($text, 'ERR');
                $tail = substr($text, -2);
                $status = proc_get_status($process);
                if (!$status['running'] && feof($pipes[1]))
                {
                    $exit = $status['exitcode'];
                    break;
                }
                if (microtime(true) >= $deadline)
                {
                    $failed = true;
                    proc_terminate($process, 9);
                    break;
                }
                if ($chunk === '')
                    usleep(10000);
            } while (true);
        }
        finally
        {
            fclose($pipes[1]);
            $closed = proc_close($process);
        }
        return !$failed && ($exit === 0 || ($exit === -1 && $closed === 0));
    }
}
