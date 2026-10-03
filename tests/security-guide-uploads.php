<?php

// Real GD, upload transports, HTTP filters/CSRF and exclusive publication; synthetic users/files only.
namespace Aowow {
    class ContributionBudget {
        public static bool $allow = true;
        public static function reserve(string $action, int $bytes = 0) : bool { return self::$allow; }
        public static function error() : string { return 'Contribution limit reached'; }
    }
    class Cfg { public static function get(string $key) : mixed { return 0; } }
    class Lang {
        public static function main(mixed ...$args) : string { return 'Internal error'; }
        public static function screenshot(mixed ...$args) : string { return 'Unknown image format'; }
    }
    class UploadFixture {
        public static array $ids = [];
        public static bool $failCopy = false;
        public static ?string $ready = null;
        public static ?string $gate = null;
    }
    // Force ID collisions only in the publisher; token generation still uses the actual CSPRNG.
    function random_int(int $min, int $max) : int {
        if ($min === 1 && $max > 1000 && UploadFixture::$ids) {
            if (UploadFixture::$ready) {
                file_put_contents(UploadFixture::$ready, 'ready'); UploadFixture::$ready = null;
                $deadline = microtime(true) + 10;
                while (!is_file(UploadFixture::$gate) && microtime(true) < $deadline) usleep(10000);
                if (!is_file(UploadFixture::$gate)) throw new \RuntimeException('Publication gate timed out');
            }
            return array_shift(UploadFixture::$ids);
        }
        return \random_int($min, $max);
    }
    function stream_copy_to_stream($input, $output, ?int $length = null) : int|false {
        if (UploadFixture::$failCopy) { fwrite($output, 'PARTIAL'); return 7; }
        return \stream_copy_to_stream($input, $output, $length);
    }
}

namespace {
    use Aowow\GuideMgr;
    use Aowow\UploadFixture;
    use Aowow\User;
    set_exception_handler(function (Throwable $error) : never {
        file_put_contents('php://stderr', $error::class.': '.$error->getMessage().' at '.$error->getFile().':'.$error->getLine()."\n"); exit(1);
    });
    if (!extension_loaded('gd') || !extension_loaded('mbstring')) {
        fwrite(STDERR, "Use PHP with GD/JPEG/PNG and mbstring; see tests/README.md.\n"); exit(1);
    }
    define('AOWOW_REVISION', 64);
    require __DIR__.'/../includes/components/errorlog.class.php';
    Aowow\ErrorLog::configurePhp();
    foreach (['defines.php', 'utilities.php', 'user.class.php', 'components/csrf.class.php', 'components/guidemgr.class.php',
        'components/response/baseresponse.class.php', 'components/response/textresponse.class.php', 'libs/qqFileUploader.class.php'] as $file)
        require __DIR__.'/../includes/'.$file;
    require __DIR__.'/../endpoints/edit/image.php';

    if (($argv[1] ?? '') === '--settings') {
        echo json_encode((new qqFileUploader(['png'], GuideMgr::IMG_MAX_BYTES))->handleUpload('unused'), JSON_THROW_ON_ERROR);
        exit;
    }

    $fixtureRoot = getenv('AOWOW_TEST_GUIDE_UPLOAD_ROOT');
    if (PHP_SAPI === 'cli-server' || ($argv[1] ?? '') === '--worker') {
        if (!$fixtureRoot || !str_starts_with($fixtureRoot, sys_get_temp_dir().'/aowow-guide-uploads-')) throw new RuntimeException('Fixture root required');
        chdir($fixtureRoot);
        if (($argv[1] ?? '') === '--worker') {
            UploadFixture::$ids = [77, 78, 79]; UploadFixture::$ready = $argv[2]; UploadFixture::$gate = $argv[3];
            $image = imagecreatetruecolor(8, 8); imagefill($image, 0, 0, (int)$argv[4]);
            echo (new ReflectionMethod(GuideMgr::class, 'saveImage'))->invoke(null, $image, IMAGETYPE_PNG); exit;
        }
        session_start();
        User::$id = $_SESSION['fixtureUser'] ?? 0; User::$username = 'fixture';
        User::$banStatus = $_SESSION['fixtureBan'] ?? 0; User::$groups = $_SESSION['fixtureGroups'] ?? 0;
        Aowow\ContributionBudget::$allow = $_SESSION['fixtureBudget'] ?? true;
        if (isset($_GET['fixture'])) {
            $_SESSION['fixtureUser'] = (int)($_GET['user'] ?? 0);
            $_SESSION['fixtureBan'] = (int)($_GET['ban'] ?? 0);
            $_SESSION['fixtureGroups'] = (int)($_GET['groups'] ?? 0);
            $_SESSION['fixtureBudget'] = !isset($_GET['denyBudget']);
            echo Aowow\Csrf::token(); exit;
        }
        (new Aowow\EditImageResponse('image'))->process(); exit;
    }

    $root = tempnam(sys_get_temp_dir(), 'aowow-guide-uploads-'); unlink($root); mkdir($root);
    $originalDir = getcwd(); chdir($root);
    foreach (['temp', 'guide/images'] as $directory) mkdir('static/uploads/'.$directory, 0777, true);
    symlink(dirname(__DIR__).'/includes', $root.'/includes');
    $checks = 0; $server = null; $workers = [];
    function check(bool $condition, string $message) : void {
        global $checks; ++$checks; if (!$condition) throw new RuntimeException($message);
    }
    $destination = $root.'/static/uploads/guide/images/';
    $temporary = $root.'/static/uploads/temp/';
    $publisher = new ReflectionMethod(GuideMgr::class, 'saveImage');
    $command = function (array $arguments) : array {
        $cmd = [PHP_BINARY];
        foreach (array_filter(explode(',', getenv('AOWOW_TEST_PHP_EXTENSIONS') ?: '')) as $extension) array_push($cmd, '-d', 'extension='.$extension);
        return array_merge($cmd, $arguments);
    };
    $environment = getenv(); $environment['AOWOW_TEST_GUIDE_UPLOAD_ROOT'] = $root;
    $pngChunk = fn(string $type, string $bytes) => pack('N', strlen($bytes)).$type.$bytes.pack('N', crc32($type.$bytes));
    $pngHeader = fn(int $width, int $height) => "\x89PNG\r\n\x1a\n".$pngChunk('IHDR', pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0)).$pngChunk('IEND', '');
    $imageBytes = function (string $type = 'png', int $width = 24, int $height = 16, bool $alpha = false) : string {
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false); imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 50, 70, 90, $alpha ? 63 : 0));
        ob_start(); $type === 'png' ? imagepng($image) : imagejpeg($image, null, 90); return ob_get_clean();
    };
    try {
        $image = imagecreatetruecolor(8, 8); imagefill($image, 0, 0, 0x234567);
        file_put_contents($destination.'5.png', 'HISTORICAL-PNG'); file_put_contents($destination.'4299.jpg', 'HISTORICAL-JPEG');
        file_put_contents($destination.'non-numeric-name', 'OTHER-FILE');
        file_put_contents($root.'/outside', 'SYMLINK-TARGET'); symlink($root.'/outside', $destination.'42.png');
        UploadFixture::$ids = [5, 42, 43];
        check($publisher->invoke(null, $image, IMAGETYPE_PNG) === 43, 'Exclusive publication retries existing files and symlinks');
        check(file_get_contents($destination.'5.png') === 'HISTORICAL-PNG' && file_get_contents($destination.'4299.jpg') === 'HISTORICAL-JPEG' && file_get_contents($root.'/outside') === 'SYMLINK-TARGET', 'No previous image or symlink target is overwritten');
        UploadFixture::$ids = array_fill(0, 16, 5);
        check($publisher->invoke(null, $image, IMAGETYPE_PNG) === null, 'Repeated ID collisions fail after bounded retries');
        UploadFixture::$ids = [44]; UploadFixture::$failCopy = true;
        check($publisher->invoke(null, $image, IMAGETYPE_PNG) === null && !file_exists($destination.'44.png'), 'Partial output failure removes only its own exclusive file');
        UploadFixture::$failCopy = false;
        $rows = '';
        for ($i = 0; $i < 2048; ++$i) $rows .= "\0".random_bytes(2048 * 3);
        $noiseBytes = "\x89PNG\r\n\x1a\n".$pngChunk('IHDR', pack('NNCCCCC', 2048, 2048, 8, 2, 0, 0, 0)).$pngChunk('IDAT', gzcompress($rows, 1)).$pngChunk('IEND', '');
        $noise = imagecreatefromstring($noiseBytes); unset($rows, $noiseBytes);
        $before = count(glob($destination.'*'));
        check($publisher->invoke(null, $noise, IMAGETYPE_PNG) === null && count(glob($destination.'*')) === $before, 'Encoded output over 10 MiB is rejected before public file creation'); unset($noise);
        $uploader = new qqFileUploader(['png'], GuideMgr::IMG_MAX_BYTES);
        $quantity = new ReflectionMethod($uploader, 'toBytes');
        foreach (['10M' => 10485760, '10485760' => 10485760, '1G' => 1073741824, '0' => 0] as $input => $expected)
            check($quantity->invoke($uploader, (string)$input) === $expected, 'PHP INI quantity units and plain bytes agree');
        foreach ([['abc', 3, 4, true], ['abc', 2, 4, false], ['abc', 4, 4, false], ['abcde', 3, 4, false], ['abc', 0, 4, false]] as [$bytes, $declared, $limit, $success]) {
            $input = tmpfile(); fwrite($input, $bytes); rewind($input); $path = $temporary.'stream-test';
            check(qqUploadedFileXhr::saveStream($input, $path, $declared, $limit) === $success && is_file($path) === $success, 'Actual-byte bounds and length mismatch clean staging');
            fclose($input); if (is_file($path)) unlink($path);
        }
        file_put_contents($temporary.'occupied', 'EXISTING'); $input = tmpfile(); fwrite($input, 'abc'); rewind($input);
        check(!qqUploadedFileXhr::saveStream($input, $temporary.'occupied', 3, 4) && file_get_contents($temporary.'occupied') === 'EXISTING', 'Transport exclusive creation cannot clobber another staging file'); fclose($input); unlink($temporary.'occupied');
        $process = proc_open($command(['-d', 'upload_max_filesize=2M', '-d', 'post_max_size=8M', __FILE__, '--settings']), [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes, $root, $environment);
        if (!is_resource($process)) throw new RuntimeException('Cannot start settings fixture'); fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]); fclose($pipes[1]); $error = stream_get_contents($pipes[2]); fclose($pipes[2]);
        check(proc_close($process) === 0 && $error === '' && isset(json_decode($output, true, flags: JSON_THROW_ON_ERROR)['error']), 'Insufficient PHP upload settings return a normal JSON error without dying');

        // Force two independent publishers to choose the same ID after encoding, then release them together.
        $gate = $root.'/gate';
        foreach ([0x112233, 0x445566] as $i => $color) {
            $ready = $root.'/ready-'.$i;
            $process = proc_open($command([__FILE__, '--worker', $ready, $gate, (string)$color]), [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes, $root, $environment);
            if (!is_resource($process)) throw new RuntimeException('Cannot start publisher'); fclose($pipes[0]); $workers[] = [$process, $pipes, $ready, $color];
        }
        $deadline = microtime(true) + 10;
        while ((!is_file($workers[0][2]) || !is_file($workers[1][2])) && microtime(true) < $deadline) usleep(10000);
        $ready = is_file($workers[0][2]) && is_file($workers[1][2]); file_put_contents($gate, 'go');
        check($ready, 'Both publishers reach the same candidate ID concurrently');
        $ids = [];
        foreach ($workers as [$process, $pipes, , $color]) {
            $output = stream_get_contents($pipes[1]); fclose($pipes[1]); $error = stream_get_contents($pipes[2]); fclose($pipes[2]);
            check(proc_close($process) === 0 && ctype_digit($output) && $error === '', 'Publication worker completes cleanly');
            $ids[] = (int)$output; $published = imagecreatefrompng($destination.$output.'.png');
            check(imagecolorat($published, 0, 0) === $color, 'Each concurrent URL retains the correct uploader pixels');
        }
        $workers = []; sort($ids); check($ids === [77, 78], 'Concurrent collision publishes two distinct compatible numeric IDs');

        $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $error); $address = stream_socket_get_name($socket, false); fclose($socket);
        $server = proc_open($command(['-d', 'upload_max_filesize=10M', '-d', 'post_max_size=12M', '-S', $address, __FILE__]), [['pipe', 'r'], ['file', $root.'/http.log', 'a'], ['file', $root.'/http.log', 'a']], $serverPipes, $root, $environment);
        if (!is_resource($server)) throw new RuntimeException('Cannot start HTTP fixture'); fclose($serverPipes[0]);
        $deadline = microtime(true) + 5;
        do { $socket = @stream_socket_client('tcp://'.$address, $errno, $error, 0.1); if (!$socket) usleep(10000); } while (!$socket && microtime(true) < $deadline);
        if (!$socket) throw new RuntimeException('HTTP fixture failed to start'); fclose($socket);
        $request = function (string $query, string &$cookie, string $method = 'GET', string $body = '', string $contentType = 'application/octet-stream', ?string $token = null) use ($address) : array {
            $headers = ['Cookie: '.$cookie, 'Content-Type: '.$contentType]; if ($token !== null) $headers[] = 'X-CSRF-Token: '.$token;
            $context = stream_context_create(['http' => ['method' => $method, 'header' => implode("\r\n", $headers), 'content' => $body, 'ignore_errors' => true, 'follow_location' => 0, 'timeout' => 10]]);
            $body = file_get_contents('http://'.$address.'/?'.$query, false, $context); $response = http_get_last_response_headers();
            foreach ($response as $header) if (preg_match('/^Set-Cookie: ([^;]+)/i', $header, $m)) $cookie = $m[1];
            preg_match('/^HTTP\/\S+ (\d+)/', $response[0], $m); return [(int)$m[1], $body, $response];
        };
        $cookie = ''; [, $csrf] = $request('fixture=identity&user=7', $cookie);
        $upload = function (string $bytes, string $name, bool $multipart = false, bool $queryName = true) use ($request, &$cookie, &$csrf, $temporary) : array {
            $query = 'edit=image&guide=1'.($queryName ? '&qqfile='.rawurlencode($name) : '');
            if ($multipart) {
                $boundary = 'AowowFixtureBoundary';
                $body = '--'.$boundary."\r\nContent-Disposition: form-data; name=\"csrfToken\"\r\n\r\n".$csrf."\r\n--".$boundary."\r\nContent-Disposition: form-data; name=\"qqfile\"; filename=\"".$name."\"\r\nContent-Type: application/octet-stream\r\n\r\n".$bytes."\r\n--".$boundary."--\r\n";
                [$status, $result] = $request($query, $cookie, 'POST', $body, 'multipart/form-data; boundary='.$boundary);
            }
            else [$status, $result] = $request($query, $cookie, 'POST', $bytes, token: $csrf);
            check($status === 200, 'Upload returns its compatible JSON result');
            $result = json_decode($result, true, flags: JSON_THROW_ON_ERROR);
            check(glob($temporary.'*') === [], 'Successful or rejected HTTP upload leaves no staging files');
            return $result;
        };
        [, $csrf] = $request('fixture=identity&user=7&denyBudget=1', $cookie);
        $before = count(glob($destination.'*'));
        foreach ([false, true] as $multipart)
            check(($upload($imageBytes(), 'limited.png', $multipart)['error'] ?? '') === 'Contribution limit reached' && count(glob($destination.'*')) === $before, 'Quota denial precedes either transport and publishes no image');
        [, $csrf] = $request('fixture=identity&user=7', $cookie);
        $jpeg = $imageBytes('jpg'); $png = $imageBytes(alpha: true); $marker = 'UPLOAD-SECRET-METADATA-TRAILER';
        $jpeg = substr($jpeg, 0, 2)."\xff\xfe".pack('n', strlen($marker) + 2).$marker.substr($jpeg, 2).$marker;
        $png = substr($png, 0, 33).$pngChunk('tEXt', 'Secret' . "\0" . $marker).substr($png, 33).$marker;
        foreach ([false, true] as $multipart) foreach ([['jpeg', $jpeg, 2], ['PNG', $png, 3]] as [$extension, $bytes, $type]) {
            $result = $upload($bytes, 'fixture.'.$extension, $multipart);
            check(($result['success'] ?? false) && $result['type'] === $type && is_int($result['id']) && $result['id'] > 0 && $result['id'] <= 9007199254740991 && $result['name'] === 'fixture.'.$extension, 'Raw/multipart JPEG/PNG retain the numeric id/type/name contract');
            $path = $destination.$result['id'].($type === 3 ? '.png' : '.jpg');
            check(is_file($path) && !str_contains(file_get_contents($path), $marker) && getimagesize($path)[0] === 24, 'Only decoded/re-encoded pixels reach public storage');
            if ($type === 3) check(((imagecolorat(imagecreatefrompng($path), 0, 0) >> 24) & 127) === 63, 'PNG transparency survives re-encoding');
        }
        $result = $upload($imageBytes(), 'legacy.png', multipart: true, queryName: false);
        check(($result['success'] ?? false) && $result['name'] === 'legacy.png', 'Legacy iframe multipart without query filename works');
        check(($upload($imageBytes(), 'wrong-extension.jpeg')['type'] ?? null) === 3, 'Stored extension/type follow actual PNG bytes');
        foreach ([['<?php echo "bad"; ?>', 'fake.png'], ['GIF89a'.str_repeat("\0", 24), 'gif.png'], [$pngHeader(5000, 1), 'wide.png'], [$pngHeader(4096, 4096), 'pixels.png'], [$pngHeader(24, 16), 'corrupt.png'], ['', 'empty.png'], [$imageBytes(), 'bad.svg']] as [$bytes, $name]) {
            foreach ([false, true] as $multipart) {
                $before = count(glob($destination.'*'));
                check(isset($upload($bytes, $name, $multipart)['error']) && count(glob($destination.'*')) === $before, 'Fake/corrupt/oversized/empty/unsupported uploads create no public file');
            }
        }
        $result = $upload($imageBytes(width: 4096, height: 1), 'boundary.png');
        check(($result['success'] ?? false) && getimagesize($destination.$result['id'].'.png')[0] === 4096, 'Axis boundary is accepted');
        $result = $upload($imageBytes(width: 4000, height: 3000), 'pixels-boundary.png');
        check(($result['success'] ?? false) && getimagesize($destination.$result['id'].'.png')[1] === 3000, 'Pixel boundary is accepted');
        $atLimit = $imageBytes(); $atLimit .= str_repeat('T', GuideMgr::IMG_MAX_BYTES - strlen($atLimit));
        foreach ([false, true] as $multipart) {
            check(($upload($atLimit, 'limit.png', $multipart)['success'] ?? false), 'Exact 10 MiB input accepted on both transports');
            check(isset($upload($atLimit.'T', 'over-limit.png', $multipart)['error']), '10 MiB plus one input rejected on both transports');
        }
        unset($atLimit);
        chmod($destination, 0555);
        check(isset($upload($imageBytes(), 'write-failure.png')['error']), 'Publication permission failure cleans input'); chmod($destination, 0777);
        chmod($temporary, 0555);
        check(isset($upload($imageBytes(), 'stage-failure.png')['error']), 'Staging permission failure creates no file'); chmod($temporary, 0777);
        $query = 'edit=image&guide=1&qqfile=fixture.png';
        check($request($query, $cookie)[0] === 405, 'GET upload is denied');
        check($request($query, $cookie, 'POST', $imageBytes())[0] === 403, 'Missing CSRF denies upload before file writes');
        [, $body, $headers] = $request('edit=image&guide=1&qqfile[]=bad.png', $cookie, 'POST', $imageBytes(), token: $csrf);
        check(isset(json_decode($body, true, flags: JSON_THROW_ON_ERROR)['error']) && glob($temporary.'*') === [], 'Malformed filename selectors fail without files');
        check(in_array('Content-Type: text/plain; charset=utf-8', $headers, true), 'XHR and iframe receive plain JSON text');
        [, $csrf] = $request('fixture=identity&user=7&ban='.ACC_BAN_GUIDE, $cookie);
        check(!($upload($imageBytes(), 'banned.png')['success'] ?? false), 'Guide ban blocks actual upload handler');
        [, $csrf] = $request('fixture=identity&user=7&groups='.U_GROUP_PENDING, $cookie);
        check(!($upload($imageBytes(), 'pending.png')['success'] ?? false), 'Pending account blocks actual upload handler');
        [, $csrf] = $request('fixture=identity&user=7', $cookie);
        $result = $upload($imageBytes(), '../<tag>.png');
        check(($result['success'] ?? false) && $result['name'] === '../<tag>.png' && is_file($destination.$result['id'].'.png'), 'Caller filename is display data while the output path uses the server numeric ID');
        echo "PASS: $checks guide upload GD/transport/concurrency/HTTP checks\n";
    }
    finally {
        if (is_resource($server)) { proc_terminate($server); proc_close($server); }
        foreach ($workers as [$process]) if (is_resource($process)) { proc_terminate($process); proc_close($process); }
        chmod($destination, 0777); chmod($temporary, 0777); chdir($originalDir);
        unlink($root.'/includes');
        $tree = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($tree as $file) $file->isDir() && !$file->isLink() ? rmdir($file->getPathname()) : unlink($file->getPathname()); rmdir($root);
    }
}
