<?php

// Run with PHP >= 8.4. All data and DB writes are synthetic; each case gets its own real PHP handlers.
$checks = 0;
function check(bool $condition, string $message) : void {
    global $checks;
    ++$checks;
    if (!$condition) throw new RuntimeException($message);
}

$modes = ['warning', 'debug-off', 'exception', 'fatal', 'database', 'failed-log', 'no-database', 'unknown-route', 'config', 'ignored', 'unsafe-source'];
foreach (['web', 'cli'] as $context)
    foreach ($modes as $mode)
        foreach (['signup', 'update-password', 'reset-password', 'update-email'] as $command) {
            $sink = tempnam(sys_get_temp_dir(), 'aowow-error-sink-');
            try {
                $process = proc_open([PHP_BINARY, '-d', 'display_errors=1', '-d', 'display_startup_errors=1', '-d', 'log_errors=1', '-d', 'zend.exception_ignore_args=0',
                    __DIR__.'/security-error-log-fixture.php', '--fixture', $mode, $sink, $context, $command],
                    [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes, dirname(__DIR__));
                if (!is_resource($process)) throw new RuntimeException('Cannot start fixture');
                fclose($pipes[0]);
                $output = stream_get_contents($pipes[1]); fclose($pipes[1]);
                $error = stream_get_contents($pipes[2]); fclose($pipes[2]);
                $exit = proc_close($process);
                $encoded = file_get_contents($sink);
                $result = json_decode($encoded, true, flags: JSON_THROW_ON_ERROR);
                $label = "$context $mode $command";
                check($exit === ($mode === 'fatal' ? 255 : ($context === 'cli' && $mode === 'exception' ? 1 : 0)), "$label: completes with expected exit status ($exit): $error");
                check(!str_contains($encoded.$output.$error, 'SECRET_'), "$label: no secrets in DB, notes, session, stdout or stderr");
                check($result['unchanged'], "$label: logging leaves all request globals unchanged");
                check($result['ini'] === ['0', '0', '0'], "$label: native output cannot bypass the handlers");
                $expectedCount = in_array($mode, ['failed-log', 'no-database', 'ignored'], true) ? 0 : ($mode === 'config' ? ($context === 'web' ? 3 : 0) : 1);
                check(count($result['records']) === $expectedCount, "$label: each error is recorded once, including at DEBUG=0");
                if ($mode === 'debug-off') check($result['notes'][0] === [] && empty($result['session']['notes'][0][0]), "$label: DEBUG=0 suppresses staff notes");
                if (in_array($mode, ['failed-log', 'no-database'], true)) check(str_contains($error, 'PHP_ERROR'), "$label: safe fallback preserves a diagnostic");
                foreach ($result['records'] as $record) {
                    check($record[0] === 57 && $record[6] > 0, "$label: revision and role metadata remain available");
                    check($record[4] === ($context === 'cli' ? 'CLI' : ($mode === 'unknown-route' ? 'route=unknown' : "route=account&command=$command")), "$label: only known routes enter query column");
                    check($record[5] === 'method=POST&postFields=11&uploads=1', "$label: body column retains counts only");
                    check(str_starts_with($record[2], 'tests/') || $record[2] === 'includes/cfg.class.php' || $record[2] === '[external]', "$label: source is a safe relative path");
                }
                if ($mode === 'exception') check(!str_contains($encoded, 'args') && str_contains($encoded, 'EXCEPTION'), "$label: exception trace has no arguments");
                if ($mode === 'fatal') check(str_contains($encoded, 'FATAL_ERROR'), "$label: shutdown records the real fatal");
                if ($mode === 'database') {
                    check(str_contains($encoded, 'DATABASE_ERROR') && str_contains($encoded, '1062'), "$label: SQL error retains numeric code");
                    check(str_contains($output, 'SELECT') && str_contains($output, 'UPDATE') && str_contains($output, 'ms'), "$label: profiler retains operations and timing");
                }
                if ($mode === 'config') check(substr_count($output, 'safe error logger') === 4, "$label: runtime INI additions and changes are rejected");
            }
            finally { unlink($sink); }
        }
echo "PASS: $checks error logging checks\n";
