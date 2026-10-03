<?php

// Real process runner and admin callers; replace only the build entrypoint with a synthetic CLI fixture.
namespace Aowow {
    function fwrite($stream, string $data) : int|false {
        return ($GLOBALS['writeCap'] ?? -1) === 0 ? 0 : \fwrite($stream, ($GLOBALS['writeCap'] ?? -1) > 0 ? substr($data,0,$GLOBALS['writeCap']) : $data);
    }
    function proc_open($command, $descriptors, &$pipes, $cwd = null, $env = null, $options = []) {
        $GLOBALS['launches'][] = [$command, $cwd, $options];
        if (isset($GLOBALS['buildStub']) && ($command[1] ?? '') === dirname(__DIR__).'/aowow')
            $command[1] = $GLOBALS['buildStub'];
        if (($GLOBALS['probeFailure'] ?? false) && ($command[1] ?? '') === '-r')
            $command[2] = 'exit(1);';
        return \proc_open($command, $descriptors, $pipes, $cwd, $env, $options);
    }
    class DB {
        public static int $writes = 0;
        public static function Aowow() : self { return new self; }
        public function qry(string $query, mixed ...$args) : bool { self::$writes++; return true; }
    }
    class Stat { public static function getWeightJson(string $name) : string { return $name; } }
}

namespace {
    use Aowow\BuildRunner;
    define('AOWOW_REVISION', 65);
    define('CLI', false);
    require __DIR__.'/../includes/defines.php';
    require __DIR__.'/../includes/utilities.php';
    require __DIR__.'/../includes/cfg.class.php';
    require __DIR__.'/../includes/components/buildrunner.class.php';
    require __DIR__.'/../includes/components/response/baseresponse.class.php';
    require __DIR__.'/../includes/components/response/textresponse.class.php';
    require __DIR__.'/../endpoints/admin/weight-presets_save.php';

    if (($argv[1] ?? '') === '--invalid-path') {
        define('AOWOW_PHP_CLI', $argv[2]);
        exit(BuildRunner::run(['globaljs']) ? 1 : 0);
    }
    $checks = 0;
    function check(bool $ok, string $label) : void {
        global $checks;
        ++$checks;
        if (!$ok) throw new RuntimeException($label);
    }
    class WeightFixture extends Aowow\AdminWeightpresetsActionSaveResponse {
        protected function assertPOST(string ...$keys) : bool { return true; } // request policy covered by A03
        public function save() : int {
            $this->_post = ['id'=>1, '__icon'=>'fixture', 'scale'=>'str:1'];
            $this->generate(); return (int)$this->result;
        }
    }
    $directory = sys_get_temp_dir().'/aowow-build-'.bin2hex(random_bytes(8)); mkdir($directory);
    $buildStub = $directory.'/build stub.php';
    file_put_contents($buildStub, <<<'PHP'
<?php
$mode = getenv('AOWOW_TEST_BUILD_MODE');
file_put_contents(getenv('AOWOW_TEST_BUILD_TRACE'), json_encode([$argv, getcwd(), PHP_SAPI]));
if ($mode === 'exit') exit(12);
if ($mode === 'stderr') fwrite(STDERR, 'ERR synthetic diagnostic');
if ($mode === 'split') { echo 'E'; usleep(50000); echo 'R'; usleep(50000); echo 'R'; }
if ($mode === 'bulk') for ($i=0;$i<200;$i++) fwrite(STDOUT, str_repeat('x', 65536));
if ($mode === 'sleep') sleep(5);
exit(0);
PHP);
    $trace = $directory.'/trace.json';
    putenv('AOWOW_TEST_BUILD_TRACE='.$trace);
    $oldCwd = getcwd(); $oldPath = getenv('PATH');
    $notes = [];
    $oldMask = umask(0022);
    set_error_handler(function($code, $message) use (&$notes) { $notes[] = $message; return true; });
    try {
        chdir($directory); putenv('PATH='.$directory); // PATH/current directory cannot select the interpreter/entrypoint
        $launches = [];
        check(BuildRunner::run(['globaljs', 'robots']), 'successful build');
        $record = json_decode(file_get_contents($trace), true);
        check($record[0][1] === '--build=globaljs,robots' && $record[1] === dirname(__DIR__) && $record[2] === 'cli', 'argv, checkout cwd and CLI verified');
        check($launches[1][0] === [realpath(PHP_BINDIR.'/php'), dirname(__DIR__).'/aowow', '--build=globaljs,robots'], 'absolute binary and entrypoint');
        check($launches[1][2] === ['bypass_shell'=>true], 'shell bypass explicit');
        foreach ([[], ['globaljs;id'], ['--help'], ['unknown'], [['weightPresets']], [1], array_fill(0, 11, 'globaljs')] as $scripts) {
            $before = count($launches);
            check(!BuildRunner::run($scripts) && count($launches) === $before, 'invalid datasets rejected before process launch');
        }
        foreach (['exit', 'stderr', 'split'] as $mode) {
            putenv('AOWOW_TEST_BUILD_MODE='.$mode);
            check(!BuildRunner::run(['weightPresets']), 'process/log failure '.$mode);
        }
        putenv('AOWOW_TEST_BUILD_MODE=bulk');
        check(BuildRunner::run(['globaljs']), 'output drains without deadlock or accumulating it');
        $probeFailure = true;
        $before = count($launches);
        check(!BuildRunner::run(['globaljs']) && count($launches) === $before+1, 'failed CLI verification prevents build');
        $probeFailure = false;

        $handle = new ReflectionMethod(Aowow\Cfg::class, 'handleFileBuild');
        $files = []; putenv('AOWOW_TEST_BUILD_MODE=');
        check($handle->invokeArgs(null, ['locales', &$files]) === '', 'actual config rebuild succeeds');
        putenv('AOWOW_TEST_BUILD_MODE=exit');
        check($handle->invokeArgs(null, ['locales', &$files]) !== '' && str_contains(end($notes), 'dataset build failed'), 'actual config rebuild reports failure');
        putenv('AOWOW_TEST_BUILD_MODE=stderr');
        $message = $handle->invokeArgs(null, ['locales', &$files]);
        check(!str_contains($message.implode('', $notes), 'synthetic diagnostic'), 'build output never reaches HTTP or warning text');
        $before = count($launches);
        check($handle->invokeArgs(null, ['unmapped', &$files]) === '' && count($launches) === $before, 'unmapped config triggers no process');
        $weight = (new ReflectionClass(WeightFixture::class))->newInstanceWithoutConstructor();
        putenv('AOWOW_TEST_BUILD_MODE=');
        check($weight->save() === 0 && Aowow\DB::$writes === 3, 'actual weight handler preserves successful response and DB writes');
        putenv('AOWOW_TEST_BUILD_MODE=exit');
        check($weight->save() === 2, 'actual weight handler propagates dataset failure');

        $execute = new ReflectionMethod(BuildRunner::class, 'execute');
        putenv('AOWOW_TEST_BUILD_MODE=sleep'); $start = microtime(true);
        check(!$execute->invoke(null, [PHP_BINARY, $buildStub], $directory, 1) && microtime(true)-$start < 3, 'timed-out child is terminated');

        foreach (['php', 'php;id', $directory.'/absent', $buildStub] as $path) {
            $process = proc_open([PHP_BINARY, __FILE__, '--invalid-path', $path], [['pipe','r'], ['pipe','w'], ['pipe','w']], $pipes);
            fclose($pipes[0]); $out = stream_get_contents($pipes[1]); $err = stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]);
        check(proc_close($process) === 0 && $out === '' && $err === '', 'invalid configured CLI path fails closed');
        }

        mkdir($directory.'/private', 0700);
        check(Aowow\Util::writeDir($directory.'/private') && (fileperms($directory.'/private') & 0777) === 0700, 'shared helper preserves private directory permissions');
        check(Aowow\Util::writeDir($directory.'/public/new') && (fileperms($directory.'/public/new') & 0777) === 0755, 'new public directories are not world writable');
        check(Aowow\Util::writeFile($directory.'/public/new/asset.js', 'first') && (fileperms($directory.'/public/new/asset.js') & 0777) === 0644, 'new generated assets are not executable/world writable');
        chmod($directory.'/public/new/asset.js', 0600); clearstatcache();
        check(Aowow\Util::writeFile($directory.'/public/new/asset.js', 'second') && (fileperms($directory.'/public/new/asset.js') & 0777) === 0600, 'existing generated asset retains operator mode');
        symlink($directory.'/public/new/asset.js', $directory.'/linked.js');
        check(!Aowow\Util::writeFile($directory.'/linked.js', 'evil') && file_get_contents($directory.'/public/new/asset.js') === 'second', 'shared writer rejects destination symlinks');
        check(Aowow\Util::writeFile($directory.'/private/config.php', 'synthetic config', 0640) && (fileperms($directory.'/private/config.php') & 0777) === 0640, 'new credential config excludes other users');
        chmod($directory.'/private/config.php', 0777); clearstatcache();
        check(Aowow\Util::writeFile($directory.'/private/config.php', 'new synthetic config', 0640) && (fileperms($directory.'/private/config.php') & 0777) === 0640, 'private caller narrows legacy permissive configuration');
        chmod($directory.'/private/config.php', 0600); clearstatcache();
        check(Aowow\Util::writeFile($directory.'/private/config.php', 'private synthetic config', 0640) && (fileperms($directory.'/private/config.php') & 0777) === 0600, 'private caller retains stricter owner-only configuration');
        $writeCap = 2;
        check(Aowow\Util::writeFile($directory.'/private/short-write', 'complete contents', 0640) && file_get_contents($directory.'/private/short-write') === 'complete contents', 'short writes complete before contents are exposed');
        $writeCap = 0;
        check(!Aowow\Util::writeFile($directory.'/private/failed-write', 'never publish', 0640) && (fileperms($directory.'/private/failed-write') & 0777) === 0600, 'incomplete new file remains owner-only and reports failure');
        unset($writeCap);
        mkdir($directory.'/frozen'); file_put_contents($directory.'/frozen/robots.txt', 'old'); chmod($directory.'/frozen', 0555);
        clearstatcache();
        if (!is_writable($directory.'/frozen')) {
            check(Aowow\Util::writeFile($directory.'/frozen/robots.txt', 'new') && file_get_contents($directory.'/frozen/robots.txt') === 'new', 'existing robots can rebuild inside immutable checkout parent');
            check(!Aowow\Util::writeFile($directory.'/frozen/new.php', 'forbidden') && !is_file($directory.'/frozen/new.php'), 'missing files cannot require relaxing immutable parent');
            check((fileperms($directory.'/frozen') & 0777) === 0555, 'immutable parent mode preserved');
        }
        chmod($directory.'/frozen', 0700);
        echo "PASS: $checks build process/admin integration checks\n";
    }
    finally {
        restore_error_handler(); chdir($oldCwd); putenv('PATH='.$oldPath); umask($oldMask);
        putenv('AOWOW_TEST_BUILD_TRACE'); putenv('AOWOW_TEST_BUILD_MODE');
        if (is_dir($directory.'/frozen')) chmod($directory.'/frozen', 0700);
        $paths = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($paths as $path) if ($path->isDir() && !$path->isLink()) rmdir($path->getPathname()); else unlink($path->getPathname());
        rmdir($directory);
    }
}
