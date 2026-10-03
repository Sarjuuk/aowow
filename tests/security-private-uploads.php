<?php

// Synthetic identities/DB rows, real JPEG writer, preview authorization and loopback HTTP delivery.
namespace Aowow {
    class ContributionBudget {
        public static bool $allow = true;
        public static function reserve(string $action, int $bytes = 0) : bool { return self::$allow; }
        public static function error() : string { return 'Contribution limit reached'; }
    }
    class Cfg {
        public static function get(string $key) : mixed {
            return match ($key) {
                'HOST_URL' => 'https://app.example/db', 'STATIC_URL' => 'https://static.example/assets',
                'SCREENSHOT_MIN_SIZE' => 200, default => 0
            };
        }
    }
    class Type {
        public static function getClassAttrib(int $type, string $attribute) : mixed { return null; }
    }
    class DB {
        public static array $rows = [];
        public static function Aowow() : self { return new self; }
        public function selectRow(string $query, mixed ...$args) : ?array { return self::$rows[$args[0]] ?? null; }
        public function selectAssoc(string $query, mixed ...$args) : array {
            return array_filter(self::$rows, fn(array $row) => in_array($row['id'], $args[1], true) && !($row['status'] & CC_FLAG_APPROVED));
        }
        public function qry(string $query, mixed ...$args) : int {
            if (str_starts_with($query, 'UPDATE ::screenshots')) {
                self::$rows[$args[2]]['status'] = $args[0];
                return 1;
            }
            if (str_starts_with($query, 'INSERT IGNORE INTO ::account_reputation')) return 1;
            throw new \RuntimeException('Unexpected fixture mutation');
        }
    }
}

namespace {
    use Aowow\DB;
    use Aowow\PrivateUpload;
    use Aowow\ScreenshotMgr;
    use Aowow\AvatarMgr;
    use Aowow\User;

    if (!extension_loaded('gd')) {
        fwrite(STDERR, "GD with JPEG support is required; see tests/README.md.\n"); exit(1);
    }
    // Existing image scaling passes fractional dimensions to GD; its deprecations are outside A10.
    error_reporting(E_ALL & ~E_DEPRECATED);
    define('AOWOW_REVISION', 62);
    require __DIR__.'/../includes/components/errorlog.class.php';
    Aowow\ErrorLog::configurePhp();                         // Match the application's native-output boundary.
    require __DIR__.'/../includes/defines.php';
    require __DIR__.'/../includes/utilities.php';
    require __DIR__.'/../includes/user.class.php';
    require __DIR__.'/../includes/components/csrf.class.php';
    require __DIR__.'/../includes/components/imageupload.class.php';
    require __DIR__.'/../includes/components/screenshotmgr.class.php';
    require __DIR__.'/../includes/components/avatarmgr.class.php';
    require __DIR__.'/../includes/components/privateupload.class.php';
    require __DIR__.'/../includes/components/response/baseresponse.class.php';
    require __DIR__.'/../includes/components/response/textresponse.class.php';
    require __DIR__.'/../includes/components/response/templateresponse.class.php';
    require __DIR__.'/../endpoints/screenshot/add.php';
    require __DIR__.'/../endpoints/upload/image-crop.php';
    require __DIR__.'/../endpoints/upload/preview.php';
    require __DIR__.'/../endpoints/admin/screenshots_approve.php';
    $fixtureRows = function () : void {
        foreach ([7 => 0, 8 => CC_FLAG_DELETED | CC_FLAG_APPROVED, 9 => CC_FLAG_APPROVED] as $id => $status)
            DB::$rows[$id] = ['id' => $id, 'userIdOwner' => 7, 'status' => $status, 'date' => time(), 'type' => 1, 'typeId' => 1];
    };
    $image = function (int $width = 1024, int $height = 768) : void {
        $img = imagecreatetruecolor($width, $height);
        imagefill($img, 0, 0, imagecolorallocate($img, 40, 60, 80));
        (new ReflectionProperty(Aowow\ImageUpload::class, 'img'))->setValue(null, $img);
    };
    if (PHP_SAPI === 'cli-server') {
        $root = getenv('AOWOW_TEST_UPLOAD_ROOT');
        if (!$root || !str_starts_with($root, sys_get_temp_dir().'/aowow-private-uploads-')) die('Fixture root required');
        chdir($root); session_start(); $fixtureRows();
        User::$id = $_SESSION['fixtureUser'] ?? 0;
        User::$username = 'owner';
        User::$groups = $_SESSION['fixtureGroups'] ?? 0;
        User::$banStatus = $_SESSION['fixtureBan'] ?? 0;
        if (isset($_GET['fixture'])) {
            if ($_GET['fixture'] === 'identity') {
                $_SESSION['fixtureUser'] = (int)($_GET['user'] ?? 0);
                $_SESSION['fixtureGroups'] = (int)($_GET['groups'] ?? 0);
                $_SESSION['fixtureBan'] = (int)($_GET['ban'] ?? 0);
                echo 'fixture';
            }
            else if ($_GET['fixture'] === 'stage') {
                $image(); $key = null;
                if (!ScreenshotMgr::tempSaveUpload([1, 1], $key)) throw new RuntimeException('Staging failed');
                echo $key;
            }
            exit;
        }
        (new Aowow\UploadPreviewResponse('preview'))->process();
        exit;
    }

    $root = tempnam(sys_get_temp_dir(), 'aowow-private-uploads-'); unlink($root); mkdir($root);
    $originalDir = getcwd(); chdir($root);
    foreach (['screenshots/temp', 'screenshots/pending', 'screenshots/normal', 'screenshots/resized', 'screenshots/thumb', 'temp', 'avatars'] as $directory)
        mkdir('static/uploads/'.$directory, 0777, true);
    session_start();
    $_FILES = [];
    $checks = 0;
    function check(bool $condition, string $message) : void {
        global $checks; ++$checks;
        if (!$condition) throw new RuntimeException($message);
    }
    $identity = function (int $id, int $groups = 0, int $bans = 0) : void {
        User::$id = $id; User::$username = 'owner'; User::$groups = $groups; User::$banStatus = $bans;
    };
    $fixtureRows(); $identity(7);
    Aowow\ContributionBudget::$allow = false;
    $screenshotAdd = (new ReflectionClass(Aowow\ScreenshotAddResponse::class))->newInstanceWithoutConstructor();
    check(!(new ReflectionMethod($screenshotAdd, 'handleAdd'))->invoke($screenshotAdd) && $_SESSION['error']['ss'] === 'Contribution limit reached', 'Screenshot quota denies before image initialization or decoding');
    $avatarAdd = (new ReflectionClass(Aowow\UploadImagecropResponse::class))->newInstanceWithoutConstructor();
    check((new ReflectionMethod($avatarAdd, 'handleUpload'))->invoke($avatarAdd) === 'Contribution limit reached', 'Avatar quota denies before image initialization or decoding');
    check(glob('static/uploads/temp/*') === [] && glob('static/uploads/screenshots/temp/*') === [], 'Denied image contributions create no staging files');
    Aowow\ContributionBudget::$allow = true;
    $image(); $screenshotKey = null;
    check(ScreenshotMgr::tempSaveUpload([1, 1], $screenshotKey), 'Real screenshot JPEG staging registers a preview');
    $screenPath = PrivateUpload::resolve('screenshot', $screenshotKey);
    check($screenPath !== null && getimagesize($screenPath)[0] <= 488, 'Private preview is the actual resized JPEG');
    check(is_file(sprintf(ScreenshotMgr::PATH_TEMP, 'owner-1-1-'.$screenshotKey.'_original')), 'Original remains available to the cropper');
    check(str_starts_with(PrivateUpload::tempUrl('screenshot', $screenshotKey), 'https://app.example/db/?upload=preview&') && !str_contains(PrivateUpload::tempUrl('screenshot', $screenshotKey), 'static.example'), 'Private crop URL uses the cookie-bearing application host');
    $image(); $avatarKey = null;
    check(AvatarMgr::tempSaveUpload(['avatar', 12], $avatarKey), 'Real avatar staging registers a preview');
    check(PrivateUpload::resolve('avatar', $avatarKey) !== null && PrivateUpload::resolve('screenshot', $avatarKey) === null, 'Avatar preview is scoped to its upload kind');
    check(PrivateUpload::resolve('temp', $avatarKey) === null && PrivateUpload::resolve('video', $avatarKey) === null, 'Metadata and arbitrary temp reads have no public route');
    $image(); ScreenshotMgr::writeImage(ScreenshotMgr::PATH_PENDING, '7');
    ScreenshotMgr::writeImage(ScreenshotMgr::PATH_PENDING, '8');
    ScreenshotMgr::writeImage(ScreenshotMgr::PATH_PENDING, '9');
    check(PrivateUpload::resolve('pending', id: 7) !== null, 'Owner can preview their pending screenshot');
    check(PrivateUpload::resolve('pending', id: 8) === null && PrivateUpload::resolve('pending', id: 9) === null, 'Owner cannot use this route for deleted or approved content');
    foreach ([0, 8] as $id) {
        $identity($id);
        check(PrivateUpload::resolve('pending', id: 7) === null && PrivateUpload::resolve('screenshot', $screenshotKey) === null && PrivateUpload::resolve('avatar', $avatarKey) === null, 'Anonymous/unrelated account cannot read another uploader image');
    }
    foreach ([U_GROUP_ADMIN, U_GROUP_BUREAU, U_GROUP_SCREENSHOT] as $group) {
        $identity(10, $group);
        check(PrivateUpload::resolve('pending', id: 7) !== null && PrivateUpload::resolve('pending', id: 8) !== null, 'Each screenshot moderation role can preview pending/deleted images');
        check(PrivateUpload::resolve('screenshot', $screenshotKey) === null, 'Moderation roles do not grant access to browser staging state');
    }
    foreach ([U_GROUP_MOD, U_GROUP_EDITOR, U_GROUP_VIDEO, U_GROUP_PREMIUM] as $group) {
        $identity(10, $group);
        check(PrivateUpload::resolve('pending', id: 7) === null, 'Unrelated staff/premium roles have no screenshot moderation access');
    }
    $identity(7, U_GROUP_ADMIN, ACC_BAN_TEMP);
    check(PrivateUpload::resolve('pending', id: 7) === null && PrivateUpload::resolve('screenshot', $screenshotKey) === null, 'Banned identities cannot read private previews');
    $identity(7);
    foreach (['', str_repeat('x', 15), str_repeat('x', 17), '../etc/passwd', 'a%2Fb', $screenshotKey.'_original'] as $key)
        check(PrivateUpload::resolve('screenshot', $key) === null, 'Malformed selectors cannot escape the preview registry');
    $entry = $_SESSION['uploadPreviews']['screenshot:'.$screenshotKey];
    foreach ([[], ['owner' => 8] + $entry, ['expires' => time()] + $entry, ['expires' => 'future'] + $entry, ['path' => __FILE__] + $entry, ['path' => sprintf(ScreenshotMgr::PATH_TEMP, 'owner-1-1-'.$screenshotKey.'_original')] + $entry] as $bad) {
        $_SESSION['uploadPreviews']['screenshot:'.$screenshotKey] = $bad;
        check(PrivateUpload::resolve('screenshot', $screenshotKey) === null, 'Invalid/expired/foreign/path-escaping/original session records fail closed');
    }
    $_SESSION['uploadPreviews']['screenshot:'.$screenshotKey] = $entry;
    $outside = $root.'/outside.jpg'; copy($screenPath, $outside);
    $link = 'static/uploads/screenshots/temp/link.jpg'; symlink($outside, $link);
    $_SESSION['uploadPreviews']['screenshot:'.$screenshotKey]['path'] = $link;
    check(PrivateUpload::resolve('screenshot', $screenshotKey) === null, 'Symlinked previews cannot escape their directory');
    $_SESSION['uploadPreviews']['screenshot:'.$screenshotKey] = $entry;
    $ownedSession = $_SESSION; $_SESSION = [];
    check(PrivateUpload::resolve('screenshot', $screenshotKey) === null, 'The same account in another browser cannot replay a copied crop key');
    $_SESSION = $ownedSession;
    for ($i = 0; $i < 110; ++$i) PrivateUpload::registerTemp('screenshot', str_pad((string)$i, 16, '0', STR_PAD_LEFT), $screenPath);
    check(count($_SESSION['uploadPreviews']) <= 100, 'Session preview metadata stays bounded');
    check(!PrivateUpload::registerTemp('video', str_repeat('V', 16), $screenPath), 'Only supported JPEG preview kinds can be registered');

    // Actual crop and moderation methods keep their original paths and publish approved derivatives.
    $identity(7);
    check(ScreenshotMgr::loadFile(ScreenshotMgr::PATH_TEMP, 'owner-1-1-'.$screenshotKey.'_original') && ScreenshotMgr::cropImg(0, 0, 0.8, 0.8) && ScreenshotMgr::writeImage(ScreenshotMgr::PATH_PENDING, '7'), 'Author can still crop the original into pending storage');
    $identity(10, U_GROUP_SCREENSHOT);
    $approval = (new ReflectionClass(Aowow\AdminScreenshotsActionApproveResponse::class))->newInstanceWithoutConstructor();
    (new ReflectionProperty($approval, '_get'))->setValue($approval, ['id' => [7]]);
    (new ReflectionMethod($approval, 'generate'))->invoke($approval);
    check(DB::$rows[7]['status'] === CC_FLAG_APPROVED && is_file(sprintf(ScreenshotMgr::PATH_NORMAL, 7)) && is_file(sprintf(ScreenshotMgr::PATH_THUMB, 7)) && is_file(sprintf(ScreenshotMgr::PATH_RESIZED, 7)), 'Real moderator approval publishes normal/thumb/resized JPEGs');
    check(!is_file(sprintf(ScreenshotMgr::PATH_PENDING, 7)) && PrivateUpload::resolve('pending', id: 7) === null, 'Published screenshots no longer expose a pending file');
    copy(sprintf(ScreenshotMgr::PATH_NORMAL, 7), sprintf(ScreenshotMgr::PATH_PENDING, 7));
    $fixtureRows();

    $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
    $address = stream_socket_get_name($socket, false); fclose($socket);
    $log = $root.'/http.log';
    $command = [PHP_BINARY];
    foreach (array_filter(explode(',', getenv('AOWOW_TEST_PHP_EXTENSIONS') ?: '')) as $extension) array_push($command, '-d', 'extension='.$extension);
    array_push($command, '-S', $address, __FILE__);
    $environment = getenv(); $environment['AOWOW_TEST_UPLOAD_ROOT'] = $root;
    $server = proc_open($command, [['pipe', 'r'], ['file', $log, 'a'], ['file', $log, 'a']], $pipes, $originalDir, $environment);
    if (!is_resource($server)) throw new RuntimeException('Cannot start preview HTTP fixture');
    fclose($pipes[0]);
    $request = function (string $query, string &$cookie, string $method = 'GET', array $headers = []) use ($address) : array {
        if ($cookie) $headers[] = 'Cookie: '.$cookie;
        $context = stream_context_create(['http' => ['method' => $method, 'header' => implode("\r\n", $headers), 'ignore_errors' => true, 'timeout' => 5, 'follow_location' => 0]]);
        $body = file_get_contents('http://'.$address.'/?'.$query, false, $context);
        $responseHeaders = http_get_last_response_headers();
        preg_match('/ (\d{3}) /', $responseHeaders[0], $status);
        foreach ($responseHeaders as $header)
            if (preg_match('/^Set-Cookie: ([^;]+)/i', $header, $m)) $cookie = $m[1];
        return [(int)$status[1], $body, strtolower(implode("\n", $responseHeaders))];
    };
    try {
        for ($i = 0; $i < 100; ++$i) {
            $ready = @stream_socket_client('tcp://'.$address, $errno, $error, 0.1);
            if ($ready) { fclose($ready); break; } usleep(10000);
        }
        $cookie = '';
        [$status, $body, $headers] = $request('upload=preview&kind=pending&id=7', $cookie);
        check($status === 403 && !str_starts_with($body, "\xff\xd8\xff"), 'Anonymous HTTP request cannot read pending bytes');
        check(str_contains($headers, 'cache-control: private, no-store') && str_contains($headers, 'x-content-type-options: nosniff'), 'Denied responses cannot populate a shared image cache');
        $request('fixture=identity&user=7', $cookie);
        [$status, $body, $headers] = $request('upload=preview&kind=pending&id=7', $cookie);
        check($status === 200 && getimagesizefromstring($body)[2] === IMAGETYPE_JPEG, 'Owner HTTP request streams the actual pending JPEG');
        check(str_contains($headers, 'content-type: image/jpeg') && str_contains($headers, 'cache-control: private, no-store') && str_contains($headers, 'vary: cookie') && str_contains($headers, 'cross-origin-resource-policy: same-origin'), 'Successful private image carries JPEG/private/cookie/origin headers');
        [$status, $body] = $request('upload=preview&kind=pending&id=7', $cookie, 'HEAD');
        check($status === 200 && $body === '', 'HEAD checks authority without sending image bytes');
        [$status] = $request('upload=preview&kind=pending&id=7', $cookie, 'GET', ['If-None-Match: *', 'If-Modified-Since: Thu, 01 Jan 2099 00:00:00 GMT']);
        check($status === 200, 'Conditional requests never bypass authorization with a shared 304');
        $key = $request('fixture=stage', $cookie)[1];
        [$status, $body] = $request('upload=preview&kind=screenshot&key='.$key, $cookie);
        check($status === 200 && getimagesizefromstring($body)[0] <= 488, 'Submitting browser can fetch its newly staged resized crop image');
        $otherCookie = ''; $request('fixture=identity&user=7', $otherCookie);
        check($request('upload=preview&kind=screenshot&key='.$key, $otherCookie)[0] === 404, 'Copied crop URL fails in another browser, even for the same account');
        $request('fixture=identity&user=8', $otherCookie);
        check($request('upload=preview&kind=pending&id=7', $otherCookie)[0] === 404, 'Unrelated logged-in account cannot enumerate pending JPEGs');
        foreach ([U_GROUP_ADMIN, U_GROUP_BUREAU, U_GROUP_SCREENSHOT] as $group) {
            $request('fixture=identity&user=10&groups='.$group, $otherCookie);
            check($request('upload=preview&kind=pending&id=7', $otherCookie)[0] === 200 && $request('upload=preview&kind=pending&id=8', $otherCookie)[0] === 200, 'Moderator HTTP previews retain pending and deleted images');
        }
        foreach (['kind=video&key='.$key, 'kind=avatar&key='.$key, 'kind=pending&id=-1', 'kind=pending&id[]=7', 'kind=screenshot&key[]=value', 'kind[]=pending&id=7', 'kind=screenshot&key=..%2F..%2Fetc%2Fpasswd', 'kind=pending&id=9', 'kind=pending&id=123456'] as $query)
            check($request('upload=preview&'.$query, $cookie)[0] === 404, 'Malformed/cross-kind/public/nonexistent HTTP selectors fail closed');
        file_put_contents('static/uploads/screenshots/pending/7.jpg', '<html>private-metadata</html>');
        [$status, $body] = $request('upload=preview&kind=pending&id=7', $cookie);
        check($status === 404 && !str_contains($body, 'private-metadata'), 'Unexpected content is not streamed as an image');
        unlink('static/uploads/screenshots/pending/7.jpg');
        check($request('upload=preview&kind=pending&id=7', $cookie)[0] === 404, 'Missing image fails without private bytes');
    }
    finally {
        proc_terminate($server); proc_close($server);
        chdir($originalDir);
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) $file->isDir() && !$file->isLink() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        rmdir($root);
    }
    echo "PASS: $checks private upload JPEG/policy/HTTP checks\n";
}
