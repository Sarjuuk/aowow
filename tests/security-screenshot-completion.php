<?php

// Real InnoDB uniqueness/rollback, JPEG writers and completion handlers; synthetic accounts and HTTP sessions.
namespace Aowow {
    class Cfg {
        public static function get(string $key) : mixed {
            return match ($key) {
                'SCREENSHOT_MIN_SIZE' => 200, 'HOST_URL', 'STATIC_URL' => 'https://app.example', default => 0
            };
        }
    }
    class Type {
        public static function checkClassAttrib(int $type, string $attribute, int $flag) : bool { return $type === 1; }
        public static function validateIds(int $type, int $id) : bool { return $type === 1 && in_array($id, [1, 2], true); }
        public static function getFileString(int $type) : string { return 'npc'; }
    }
    class Lang {
        public static function getLocale() : Locale { return Locale::EN; }
        public static function screenshot(mixed ...$args) : string { return 'fixture'; }
        public static function main(mixed ...$args) : string { return 'fixture'; }
    }
}

namespace {
    use Aowow\DB;
    use Aowow\DibiConnection;
    use Aowow\PrivateUpload;
    use Aowow\ScreenshotMgr;
    use Aowow\User;

    set_exception_handler(function (Throwable $error) : never {
        file_put_contents('php://stderr', $error::class.': '.$error->getMessage().' at '.$error->getFile().':'.$error->getLine()."\n");
        exit(1);
    });

    if (getenv('AOWOW_TEST_DATABASE') !== 'aowow_security_test_screenshots' || !extension_loaded('gd')) {
        fwrite(STDERR, "Use AOWOW_TEST_DATABASE=aowow_security_test_screenshots on a disposable MySQL fixture with GD/JPEG; see tests/README.md.\n");
        exit(1);
    }
    define('AOWOW_REVISION', 63);
    require __DIR__.'/../includes/components/errorlog.class.php';
    Aowow\ErrorLog::configurePhp();
    error_reporting(E_ALL & ~E_DEPRECATED);                // Existing fractional GD scaling is outside A11.
    require __DIR__.'/../includes/defines.php';
    require __DIR__.'/../includes/locale.class.php';
    $dibiSource = getenv('AOWOW_TEST_DIBI_DIR');
    if ($dibiSource) {
        require $dibiSource.'/src/Dibi/interfaces.php';
        require $dibiSource.'/src/Dibi/exceptions.php';
        require $dibiSource.'/src/Dibi/dibi.php';
        spl_autoload_register(function (string $class) use ($dibiSource) : void {
            if (str_starts_with($class, 'Dibi\\')) {
                $path = $dibiSource.'/src/'.str_replace('\\', '/', $class).'.php';
                if (is_file($path)) require $path;
            }
        });
    }
    else require __DIR__.'/../includes/libs/autoload.php';
    foreach (['database.php', 'utilities.php', 'user.class.php', 'components/csrf.class.php',
        'components/imageupload.class.php', 'components/screenshotmgr.class.php', 'components/avatarmgr.class.php',
        'components/privateupload.class.php', 'components/response/baseresponse.class.php', 'components/response/textresponse.class.php',
        'components/response/templateresponse.class.php'] as $file)
        require __DIR__.'/../includes/'.$file;
    require __DIR__.'/../endpoints/screenshot/complete.php';
    require __DIR__.'/../endpoints/screenshot/crop.php';

    class ScreenshotConnection extends DibiConnection {
        public static ?string $fail = null;
        public static ?string $ready = null;
        public static ?string $partial = null;
        public function qry(mixed ...$args) : ?int {
            if (self::$ready && str_starts_with($args[0], 'INSERT INTO ::screenshot_uploads')) file_put_contents(self::$ready, 'ready');
            if (self::$fail === $args[0] || (self::$fail === 'insert' && str_starts_with($args[0], 'INSERT INTO ::screenshots')) ||
                (self::$fail === 'claim' && str_starts_with($args[0], 'INSERT INTO ::screenshot_uploads'))) return null;
            $result = parent::qry(...$args);
            if (self::$fail === 'write' && str_starts_with($args[0], 'INSERT INTO ::screenshots') && $result > 0) {
                self::$partial = sprintf(ScreenshotMgr::PATH_PENDING, $result);
                file_put_contents(self::$partial, 'PARTIAL-FIXTURE'); chmod(self::$partial, 0444);
            }
            if (self::$fail === 'uncertain' && $args[0] === 'COMMIT') return null;
            if (self::$fail === 'cleanup' && $args[0] === 'COMMIT') chmod(dirname(ScreenshotMgr::PATH_TEMP), 0555);
            return $result;
        }
    }
    $options = [
        'driver' => 'mysqli', 'host' => getenv('AOWOW_TEST_DB_HOST') ?: '127.0.0.1', 'port' => (int)(getenv('AOWOW_TEST_DB_PORT') ?: 3306),
        'username' => getenv('AOWOW_TEST_DB_USER') ?: 'root', 'password' => getenv('AOWOW_TEST_DB_PASSWORD') ?: '',
        'database' => 'aowow_security_test_screenshots', 'charset' => 'utf8mb4', 'substitutes' => ['' => 'aowow_']
    ];
    $db = new ScreenshotConnection($options);
    $db->query("SET SESSION sql_mode = ''");
    (new ReflectionProperty(DB::class, 'interfaceCache'))->setValue(null, [DB_AOWOW => $db]);
    (new ReflectionProperty(DB::class, 'interfaceTimes'))->setValue(null, [DB_AOWOW => [time() + 3600, 3600]]);
    $identity = function (int $id = 7, int $groups = 0, int $bans = 0) : void {
        User::$id = $id; User::$username = 'user'.$id; User::$groups = $groups; User::$banStatus = $bans;
    };
    $image = function () : void {
        $img = imagecreatetruecolor(1024, 768);
        imagefill($img, 0, 0, imagecolorallocate($img, 30, 50, 80));
        (new ReflectionProperty(Aowow\ImageUpload::class, 'img'))->setValue(null, $img);
    };
    $stage = function () use ($image) : string {
        $image(); $key = null;
        if (!ScreenshotMgr::tempSaveUpload([1, 1], $key)) throw new RuntimeException('Staging failed');
        return $key;
    };
    $complete = function (string $key, int $typeId = 1, string $coords = '0.000,0.000,0.800,0.800') : bool {
        $response = (new ReflectionClass(Aowow\ScreenshotCompleteResponse::class))->newInstanceWithoutConstructor();
        foreach (['destType' => 1, 'destTypeId' => $typeId, 'imgHash' => $key,
            '_post' => ['coords' => (new ReflectionMethod($response, 'checkCoords'))->invoke(null, $coords), 'screenshotalt' => '  Synthetic   caption  ']] as $property => $value)
            (new ReflectionProperty($response, $property))->setValue($response, $value);
        return (new ReflectionMethod($response, 'handleComplete'))->invoke($response);
    };
    $fixtureRoot = getenv('AOWOW_TEST_SCREENSHOT_ROOT');
    if (PHP_SAPI === 'cli-server' || ($argv[1] ?? '') === '--worker') {
        if (!$fixtureRoot || !str_starts_with($fixtureRoot, sys_get_temp_dir().'/aowow-screenshot-completion-')) throw new RuntimeException('Fixture root required');
        chdir($fixtureRoot); session_start(); $_FILES = [];
        if (($argv[1] ?? '') === '--worker') {
            $_SESSION = json_decode(file_get_contents($argv[2]), true, flags: JSON_THROW_ON_ERROR);
            $identity(); ScreenshotConnection::$ready = $argv[3];
            echo $complete($argv[4]) ? '1' : '0'; exit;
        }
        $identity($_SESSION['fixtureUser'] ?? 0, $_SESSION['fixtureGroups'] ?? 0, $_SESSION['fixtureBans'] ?? 0);
        if (isset($_GET['fixture'])) {
            if ($_GET['fixture'] === 'identity') {
                $_SESSION['fixtureUser'] = (int)($_GET['user'] ?? 0);
                $_SESSION['fixtureGroups'] = (int)($_GET['groups'] ?? 0);
                $_SESSION['fixtureBans'] = (int)($_GET['bans'] ?? 0);
                echo Aowow\Csrf::token();
            }
            else if ($_GET['fixture'] === 'stage') echo $stage();
            exit;
        }
        if (str_starts_with($_SERVER['QUERY_STRING'], 'screenshot=crop&'))
            (new Aowow\ScreenshotCropResponse('crop'))->process();
        else (new Aowow\ScreenshotCompleteResponse('complete'))->process();
        exit;
    }

    $root = tempnam(sys_get_temp_dir(), 'aowow-screenshot-completion-'); unlink($root); mkdir($root);
    $originalDir = getcwd(); chdir($root); session_start(); $_FILES = [];
    foreach (['temp', 'pending', 'normal', 'resized', 'thumb'] as $directory) mkdir('static/uploads/screenshots/'.$directory, 0777, true);
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $checks = 0; $server = null;
    function check(bool $condition, string $message) : void {
        global $checks; ++$checks;
        if (!$condition) throw new RuntimeException($message);
    }
    $command = function (array $arguments) : array {
        $cmd = [PHP_BINARY];
        foreach (array_filter(explode(',', getenv('AOWOW_TEST_PHP_EXTENSIONS') ?: '')) as $extension) array_push($cmd, '-d', 'extension='.$extension);
        return array_merge($cmd, $arguments);
    };
    $environment = getenv(); $environment['AOWOW_TEST_SCREENSHOT_ROOT'] = $root;
    try {
        $structure = file_get_contents(__DIR__.'/../setup/sql/01-db_structure.sql');
        foreach (['screenshots', 'screenshot_uploads', 'account'] as $table) $db->query('DROP TABLE IF EXISTS [aowow_'.$table.']');
        foreach (['account', 'screenshots', 'screenshot_uploads'] as $table) {
            preg_match('/CREATE TABLE `aowow_'.preg_quote($table, '/').'` \(.*?\) ENGINE=InnoDB[^;]*;/s', $structure, $m);
            if (!$m) throw new RuntimeException('Shipped schema not found');
            $db->query($m[0]);
        }
        foreach ([7, 8] as $id) $db->query('INSERT INTO ::account (`id`, `login`, `username`, `email`, `passHash`, `joinDate`, `curLogin`, `userGroups`, `status`, `statusTimer`, `token`, `updateValue`) VALUES (%i, %s, %s, %s, "fixture", 0, 0, 0, 0, 0, "", "")', $id, 'user'.$id, 'user'.$id, 'user'.$id.'@example.test');
        $db->query('DROP TABLE ::screenshot_uploads');
        $db->query('INSERT INTO ::screenshots (`type`, `typeId`, `userIdOwner`, `date`, `width`, `height`, `caption`, `status`) VALUES (1, 1, 7, 0, 1024, 768, "historical", 0), (1, 1, 8, 0, 1024, 768, "historical", 0)');
        $db->query(file_get_contents(__DIR__.'/../setup/sql/updates/1790985600_01.sql'));
        $db->query(file_get_contents(__DIR__.'/../setup/sql/updates/1790985600_01.sql'));
        check((int)$db->selectCell('SELECT COUNT(*) FROM ::screenshots') === 2, 'Migration/replay preserve historical screenshots');
        $index = $db->selectRow('SHOW INDEX FROM ::screenshot_uploads WHERE Key_name = "PRIMARY"');
        check($index['Non_unique'] === 0 && $index['Column_name'] === 'uploadKey', 'Shipped migration creates the exact unique upload key');
        $column = $db->selectRow('SHOW FULL COLUMNS FROM ::screenshot_uploads WHERE Field = "uploadKey"');
        check($column['Null'] === 'NO' && $column['Collation'] === 'ascii_bin', 'Claim key is required and case-sensitive');
        $identity(); $_SESSION = [];
        $reset = function () use ($db, $identity) : void {
            ScreenshotConnection::$fail = null; ScreenshotConnection::$ready = null;
            chmod(dirname(ScreenshotMgr::PATH_TEMP), 0777);
            $db->query('DELETE FROM ::screenshots');
            $db->query('DELETE FROM ::screenshot_uploads');
            foreach (glob(dirname(ScreenshotMgr::PATH_PENDING).'/*') as $file) if (is_file($file)) unlink($file);
            $identity(); $_SESSION = [];
        };
        $count = fn() => (int)$db->selectCell('SELECT COUNT(*) FROM ::screenshots');
        $reset(); $key = $stage(); $files = PrivateUpload::screenshotStage($key, 1, 1); $snapshot = $_SESSION;
        check($files !== null && PrivateUpload::screenshotStage($key, 1, 2) === null, 'Session staging is bound to the exact destination');
        check($complete($key), 'Real completion succeeds');
        $row = $db->selectRow('SELECT * FROM ::screenshots');
        check($count() === 1 && $row['status'] === 0 && $row['userIdOwner'] === 7 && $row['width'] === 819 && $row['height'] === 614 && $row['caption'] === 'Synthetic caption', 'Exactly one compatible pending record with cropped dimensions/caption');
        $pending = sprintf(ScreenshotMgr::PATH_PENDING, $row['id']);
        check(is_file($pending) && getimagesize($pending)[0] === 819, 'Committed row has its real JPEG');
        check(!is_file($files['preview']) && !is_file($files['original']) && PrivateUpload::resolve('screenshot', $key) === null, 'Success consumes both files and session authority');
        check(!$complete($key) && $count() === 1, 'Ordinary replay creates no second screenshot');
        // Restore stale state/files to model cleanup failure or a worker crash after commit.
        copy($pending, $files['preview']); copy($pending, $files['original']); $_SESSION = $snapshot;
        check(!$complete($key) && $count() === 1 && is_file($pending), 'SQL uniqueness rejects replay even if session and originals survive');
        $db->query('DELETE FROM ::screenshots');
        check(!$complete($key) && $count() === 0, 'Permanent moderation deletion cannot remove the independent replay barrier');

        foreach ([ACC_BAN_SCREENSHOT, ACC_BAN_TEMP, ACC_BAN_PERM] as $ban) {
            $reset(); $key = $stage(); User::$banStatus = $ban;
            check(!$complete($key) && $count() === 0 && PrivateUpload::screenshotStage($key, 1, 1) === null, 'Post-staging upload/global ban rejects crop and completion');
        }
        foreach ([U_GROUP_PENDING, 0] as $groups) {
            $reset(); $key = $stage(); $identity($groups ? 7 : 0, $groups);
            check(!$complete($key) && $count() === 0, 'Pending or logged-out account cannot complete staged upload');
        }
        $reset(); $key = $stage(); $identity(8);
        check(!$complete($key) && $count() === 0, 'Another account cannot complete a copied key');
        $reset(); $key = $stage(); $_SESSION = [];
        check(!$complete($key) && $count() === 0, 'Same account in a different browser lacks staging authority');
        $reset(); $key = $stage();
        check(!$complete($key, 2) && $count() === 0, 'Target substitution fails before submission');
        $_SESSION['uploadPreviews']['screenshot:'.$key]['expires'] = time();
        check(!$complete($key) && $count() === 0, 'Expired stage cannot complete');
        $reset(); $key = $stage(); $files = PrivateUpload::screenshotStage($key, 1, 1);
        rename($files['original'], $root.'/outside.jpg'); symlink($root.'/outside.jpg', $files['original']);
        check(!$complete($key) && $count() === 0, 'Symlinked original cannot be completed'); unlink($files['original']);
        $reset(); $key = $stage();
        foreach (['0.000,0.000,0.000,1.000', '0.000,0.000,1.999,1.000', '0.800,0.000,0.800,1.000', '1.000,0.000,0.100,1.000', "0.000,0.000,1.000,1.000\n", 'malformed'] as $coords)
            check(!$complete($key, coords: $coords) && $count() === 0, 'Invalid coordinates cannot mutate submission state');
        check(!$complete($key, coords: '0.000,0.000,0.001,0.001') && $count() === 0, 'Zero-pixel GD crop fails safely');
        check($complete($key, coords: '0.333,0.000,0.668,1.000'), 'Cropper rounding at the right edge remains compatible');

        foreach (['START TRANSACTION', 'claim', 'insert', 'write'] as $failure) {
            $reset(); $key = $stage(); ScreenshotConnection::$fail = $failure;
            check(!$complete($key) && $count() === 0, 'Start/insert/JPEG failure leaves no committed row');
            check((int)$db->selectCell('SELECT COUNT(*) FROM ::screenshot_uploads') === 0, 'Pre-commit failure rolls back its completion claim');
            ScreenshotConnection::$fail = null;
            check(PrivateUpload::screenshotStage($key, 1, 1) !== null, 'Pre-commit failure preserves retryable stage');
            if ($failure === 'write') check(!is_file(ScreenshotConnection::$partial), 'Failed partial JPEG is removed after rollback');
            check($complete($key) && $count() === 1, 'Retry after a confirmed pre-commit failure succeeds once');
        }
        $reset(); $key = $stage(); ScreenshotConnection::$fail = 'COMMIT';
        check(!$complete($key) && $count() === 0 && PrivateUpload::screenshotStage($key, 1, 1) !== null, 'Unacknowledged commit rolls back but conservatively preserves stage and pending bytes');
        ScreenshotConnection::$fail = null;
        check($complete($key) && $count() === 1, 'A rolled-back commit can be retried');
        $reset(); $key = $stage(); ScreenshotConnection::$fail = 'uncertain';
        check(!$complete($key) && $count() === 1, 'Lost commit acknowledgment cannot report confirmed success');
        $row = $db->selectRow('SELECT * FROM ::screenshots'); $pending = sprintf(ScreenshotMgr::PATH_PENDING, $row['id']);
        check(is_file($pending) && PrivateUpload::screenshotStage($key, 1, 1) !== null, 'Potentially committed JPEG and original are preserved');
        ScreenshotConnection::$fail = null;
        check(!$complete($key) && $count() === 1 && is_file($pending), 'Retry after uncertain actual commit cannot duplicate or delete committed image');
        $reset(); $key = $stage(); $files = PrivateUpload::screenshotStage($key, 1, 1); ScreenshotConnection::$fail = 'cleanup';
        check($complete($key) && $count() === 1 && !isset($_SESSION['uploadPreviews']['screenshot:'.$key]), 'Cleanup failure does not reverse committed submission/session consumption');
        chmod(dirname(ScreenshotMgr::PATH_TEMP), 0777); ScreenshotConnection::$fail = null;
        check(is_file($files['original']) && !$complete($key) && $count() === 1, 'Retained files after cleanup failure provide no completion authority');

        // Two independent processes use stale copies of one legitimate stage. A held unique key ensures overlap.
        $reset(); $key = $stage(); $state = $root.'/worker-state.json'; file_put_contents($state, json_encode($_SESSION, JSON_THROW_ON_ERROR));
        $lock = new DibiConnection($options); $lock->query('START TRANSACTION');
        $lock->query('INSERT INTO ::screenshot_uploads (`uploadKey`, `userIdOwner`, `expires`) VALUES (%s, 7, %i)', hash('sha256', '7:'.$key), time() + DAY);
        $workers = [];
        for ($i = 0; $i < 2; ++$i) {
            $ready = $root.'/ready-'.$i;
            $process = proc_open($command([__FILE__, '--worker', $state, $ready, $key]), [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes, $root, $environment);
            if (!is_resource($process)) throw new RuntimeException('Cannot start worker');
            fclose($pipes[0]); $workers[] = [$process, $pipes, $ready];
        }
        $deadline = microtime(true) + 10;
        while ((!is_file($workers[0][2]) || !is_file($workers[1][2])) && microtime(true) < $deadline) usleep(10000);
        $bothReady = is_file($workers[0][2]) && is_file($workers[1][2]); $lock->query('ROLLBACK');
        check($bothReady, 'Both completion workers reach the held unique key');
        $results = [];
        foreach ($workers as [$process, $pipes]) {
            $output = stream_get_contents($pipes[1]); fclose($pipes[1]); $error = stream_get_contents($pipes[2]); fclose($pipes[2]);
            check(proc_close($process) === 0 && in_array($output, ['0', '1'], true) && $error === '', 'Concurrent completion worker exits cleanly');
            $results[] = $output;
        }
        sort($results);
        check($results === ['0', '1'] && $count() === 1 && count(glob(dirname(ScreenshotMgr::PATH_PENDING).'/*.jpg')) === 1, 'Concurrent completion yields exactly one row and one pending image');
        check((int)$db->selectCell('SELECT COUNT(*) FROM ::screenshot_uploads') === 1, 'Concurrent completion leaves exactly one durable claim');

        $reset(); $key = $stage();
        $_SESSION['uploadPreviews']['screenshot:'.$key]['expires'] = time() + 2;
        $expires = $_SESSION['uploadPreviews']['screenshot:'.$key]['expires'];
        file_put_contents($state, json_encode($_SESSION, JSON_THROW_ON_ERROR));
        $ready = $root.'/ready-expiry';
        $lock->query('START TRANSACTION');
        $lock->query('INSERT INTO ::screenshot_uploads VALUES (%s, 7, %i)', hash('sha256', '7:'.$key), $expires);
        $process = proc_open($command([__FILE__, '--worker', $state, $ready, $key]), [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes, $root, $environment);
        if (!is_resource($process)) throw new RuntimeException('Cannot start expiry worker'); fclose($pipes[0]);
        $deadline = microtime(true) + 10;
        while (!is_file($ready) && microtime(true) < $deadline) usleep(10000);
        $wasReady = is_file($ready);
        while (time() <= $expires) usleep(10000);
        $lock->query('ROLLBACK');
        check($wasReady, 'Completion reaches the claim lock while its stage is still valid');
        $output = stream_get_contents($pipes[1]); fclose($pipes[1]); $error = stream_get_contents($pipes[2]); fclose($pipes[2]);
        check(proc_close($process) === 0 && $output === '0' && $error === '', 'Stage expiring during lock wait is denied');
        check($count() === 0 && (int)$db->selectCell('SELECT COUNT(*) FROM ::screenshot_uploads') === 0, 'Expiry after lock wait rolls back both submission and claim');

        $reset(); $key = $stage();
        for ($i = 0; $i < 105; ++$i) $db->query('INSERT INTO ::screenshot_uploads VALUES (%s, 7, 0)', hash('sha256', 'expired-'.$i));
        check($complete($key) && (int)$db->selectCell('SELECT COUNT(*) FROM ::screenshot_uploads WHERE `expires` <= %i', time()) === 5, 'Indexed expired-claim cleanup removes at most 100 per completion');
        $reset(); $key = $stage(); $db->query('DROP TABLE ::screenshot_uploads');
        check(!$complete($key) && $count() === 0 && PrivateUpload::screenshotStage($key, 1, 1) !== null, 'Missing deployment migration fails closed without consuming stage');
        $db->query(file_get_contents(__DIR__.'/../setup/sql/updates/1790985600_01.sql'));

        // Real HTTP filtering, CSRF and response behavior with synthetic identity/session transport.
        $reset();
        $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $error); $address = stream_socket_get_name($socket, false); fclose($socket);
        $server = proc_open($command(['-S', $address, __FILE__]), [['pipe', 'r'], ['file', $root.'/http.log', 'a'], ['file', $root.'/http.log', 'a']], $serverPipes, $root, $environment);
        if (!is_resource($server)) throw new RuntimeException('Cannot start HTTP fixture'); fclose($serverPipes[0]);
        $deadline = microtime(true) + 5;
        do { $socket = @stream_socket_client('tcp://'.$address, $errno, $error, 0.1); if (!$socket) usleep(10000); } while (!$socket && microtime(true) < $deadline);
        if (!$socket) throw new RuntimeException('HTTP fixture failed to start'); fclose($socket);
        $request = function (string $url, string &$cookie, string $method = 'GET', array $post = []) use ($address) : array {
            $headers = ['Cookie: '.$cookie, 'Content-Type: application/x-www-form-urlencoded'];
            $context = stream_context_create(['http' => ['method' => $method, 'header' => implode("\r\n", $headers), 'content' => http_build_query($post), 'ignore_errors' => true, 'follow_location' => 0, 'timeout' => 5]]);
            $body = file_get_contents('http://'.$address.'/?'.$url, false, $context);
            $response = http_get_last_response_headers();
            foreach ($response as $header) if (preg_match('/^Set-Cookie: ([^;]+)/i', $header, $m)) $cookie = $m[1];
            preg_match('/^HTTP\/\S+ (\d+)/', $response[0], $m);
            return [(int)$m[1], $body, $response];
        };
        $cookie = ''; [, $csrf] = $request('fixture=identity&user=7', $cookie); [, $key] = $request('fixture=stage', $cookie);
        $url = 'screenshot=complete&1.1.'.$key;
        check($request($url, $cookie)[0] === 405 && $count() === 0, 'GET completion is denied');
        check($request($url, $cookie, 'POST', ['coords' => '0.000,0.000,1.000,1.000'])[0] === 403 && $count() === 0, 'Missing CSRF is denied');
        $post = ['csrfToken' => $csrf, 'coords' => '0.000,0.000,0.800,0.800', 'screenshotalt' => 'HTTP fixture'];
        $other = ''; $request('fixture=identity&user=7', $other);
        check($request($url, $other, 'POST', $post)[0] === 403 && $count() === 0, 'Other browser cannot reuse the uploading CSRF token');
        [, $otherCsrf] = $request('fixture=identity&user=7', $other);
        check($request($url, $other, 'POST', ['csrfToken' => $otherCsrf] + $post)[0] === 404 && $count() === 0, 'Other browser with its own CSRF still lacks stage authority');
        check($request($url, $cookie, 'POST', ['coords' => ['bad']] + $post)[0] === 404 && $count() === 0, 'Array coordinate selector is rejected by real HTTP filters');
        $result = $request($url, $cookie, 'POST', $post);
        check($result[0] === 302 && in_array('Location: ?screenshot=thankyou&1.1', $result[2], true) && $count() === 1, 'Authorized POST retains the compatible thank-you redirect');
        check($request($url, $cookie, 'POST', $post)[0] === 404 && $count() === 1, 'HTTP completion replay is rejected');
        [, $key] = $request('fixture=stage', $cookie);
        [, $csrf] = $request('fixture=identity&user=7&bans='.ACC_BAN_SCREENSHOT, $cookie);
        check($request('screenshot=complete&1.1.'.$key, $cookie, 'POST', ['csrfToken' => $csrf] + $post)[0] === 404 && $count() === 1, 'HTTP completion rechecks a post-staging screenshot ban');
        $result = $request('screenshot=crop&1.1.'.$key, $cookie);
        check($result[0] === 302 && in_array('Location: ?npc=1#submit-a-screenshot', $result[2], true), 'Real crop response rechecks the post-staging ban before rendering');
        echo "PASS: $checks screenshot completion SQL/JPEG/HTTP checks\n";
    }
    finally {
        if (is_resource($server)) { proc_terminate($server); proc_close($server); }
        chmod(dirname(ScreenshotMgr::PATH_TEMP), 0777); chdir($originalDir);
        $tree = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($tree as $file) $file->isDir() && !$file->isLink() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        rmdir($root);
    }
}
