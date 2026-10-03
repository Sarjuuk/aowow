<?php

// Real signed cache/TrCache/template components with a loopback raw Memcached-protocol fixture.
namespace Aowow {
    class Cfg {
        public static int $mode = 1;
        public static string $directory = '';
        public static function get(string $key) : mixed {
            return match ($key) { 'CACHE_MODE' => self::$mode, 'CACHE_DECAY' => 3600,
                'CACHE_DIR' => self::$directory, 'LOCALES' => 0x15D, default => '' };
        }
    }
    class Lang { public static function getLocale() : Locale { return Locale::EN; } }
    class User {
        public static function isInGroup(int $group) : bool { return false; }
        public static function getUserGlobal() : array { return ['id' => 1]; }
        public static function getFavorites() : array { return []; }
    }
    // Only used as PageTemplate's declared nullable context type; no application boot or DB needed.
    class TemplateResponse {}
}

namespace {
    use Aowow\CacheEnvelope;
    define('AOWOW_REVISION', 65);
    if (($argv[1] ?? '') !== '--missing-key')
        define('AOWOW_CACHE_KEY', ($argv[1] ?? '') === '--invalid-key' ? 'invalid' : str_repeat('11', 32)); // synthetic fixture only
    require __DIR__.'/../includes/defines.php';
    require __DIR__.'/../includes/locale.class.php';
    require __DIR__.'/../includes/type.class.php';
    require __DIR__.'/../includes/utilities.php';
    require __DIR__.'/../includes/components/jsexpression.class.php';
    require __DIR__.'/../includes/components/locstring.class.php';
    require __DIR__.'/../includes/components/frontend/listview.class.php';
    require __DIR__.'/../includes/components/frontend/tabs.class.php';
    require __DIR__.'/../includes/components/frontend/markup.class.php';
    require __DIR__.'/../includes/components/frontend/infoboxmarkup.class.php';
    require __DIR__.'/../includes/components/response/baseresponse.class.php';
    require __DIR__.'/../includes/components/pagetemplate.class.php';
    require __DIR__.'/../includes/components/cacheenvelope.class.php';

    $checks = 0;
    function check(bool $ok, string $label) : void {
        global $checks;
        ++$checks;
        if (!$ok) throw new RuntimeException($label);
    }
    class CacheFixture {
        use Aowow\TrCache;
        protected int $cacheType = CACHE_TYPE_PAGE;
        public function getCacheKeyComponents() : array { return [3, 1, 0, 'fixture']; }
        public static function hook(mixed &$data, mixed $params) : mixed { return is_string($data) ? $data.$params : $data; }
        public static function displayHook(Aowow\Template\PageTemplate $template, mixed &$data, mixed $params) : void { $data .= $params; }
        protected static function protectedHook(mixed $data) : mixed { return $data.' protected'; }
        public function protect() : void { $this->setOnCacheLoaded([self::class, 'protectedHook']); }
    }
    class WakeupProbe {
        public static int $calls = 0;
        public function __wakeup() : void { self::$calls++; }
    }
    // Synthetic PECL writer: persist raw strings for the independent text-protocol server.
    class Memcached {
        public const int OPT_COMPRESSION = 1;
        public static array $options = [];
        public function setOption(int $key, mixed $value) : bool { self::$options[$key] = $value; return true; }
        public function addServer(string $host, int $port) : bool { return true; }
        public function set(string $key, string $data, int $expiry) : bool {
            $state = json_decode(file_get_contents($GLOBALS['stateFile']), true);
            $state[$key] = ['flags' => 0, 'data' => base64_encode($data), 'expiry' => $expiry];
            file_put_contents($GLOBALS['stateFile'], json_encode($state)); return true;
        }
        public function get(string $key) : mixed { throw new RuntimeException('PECL deserialization must never be called'); }
        public function getAllKeys() : array { return array_keys(json_decode(file_get_contents($GLOBALS['stateFile']), true)); }
        public function delete(string $key) : bool {
            $state = json_decode(file_get_contents($GLOBALS['stateFile']), true); unset($state[$key]);
            file_put_contents($GLOBALS['stateFile'], json_encode($state)); return true;
        }
    }

    if (($argv[1] ?? '') === '--server') {
        $server = stream_socket_server('tcp://127.0.0.1:11211', $errno, $error);
        if (!$server) throw new RuntimeException('Cannot start isolated loopback cache fixture');
        file_put_contents($argv[3], 'ready');
        while ($socket = stream_socket_accept($server, 10)) {
            $request = trim(fgets($socket, 512));
            $key = substr($request, 4);
            $state = json_decode(file_get_contents($argv[2]), true);
            if (!isset($state[$key])) fwrite($socket, "END\r\n");
            else {
                $entry = $state[$key]; $data = base64_decode($entry['data']);
                $response = 'VALUE '.$key.' '.$entry['flags'].' '.($entry['length'] ?? strlen($data))."\r\n".$data.($entry['suffix'] ?? "\r\nEND\r\n");
                for ($i = 0; $i < strlen($response); $i += 113) fwrite($socket, substr($response, $i, 113));
            }
            fclose($socket);
        }
        exit;
    }
    if (in_array($argv[1] ?? '', ['--missing-key', '--invalid-key'], true)) {
        check(!CacheEnvelope::enabled(), 'missing key disables cache');
        check(CacheEnvelope::seal('key', 'text', [null, null], 60) === null, 'missing key cannot write cache');
        check(CacheEnvelope::open('key', 'forged') === null, 'missing key cannot read cache');
        echo "PASS: $checks unconfigured cache checks\n"; exit;
    }

    function signed(string $body, string $key = 'key') : string {
        return hash_hmac('sha256', $key."\0".$body, hex2bin(AOWOW_CACHE_KEY))."\n".$body;
    }
    function child(array $arguments) : array {
        $process = proc_open([PHP_BINARY, __FILE__, ...$arguments], [['pipe','r'], ['pipe','w'], ['pipe','w']], $pipes);
        fclose($pipes[0]); $out = stream_get_contents($pipes[1]); $error = stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]); return [proc_close($process), $out, $error];
    }

    $directory = sys_get_temp_dir().'/aowow-cache-'.bin2hex(random_bytes(8));
    mkdir($directory); Aowow\Cfg::$directory = $directory.'/cache/';
    $stateFile = $directory.'/state.json'; file_put_contents($stateFile, '{}');
    $ready = $directory.'/ready'; $server = null;
    try {
        check(CacheEnvelope::enabled(), 'valid deployment key enables response cache');
        $envelope = CacheEnvelope::seal('key', "0\nstring", [CacheFixture::class.'::hook', "\nparams"], 3600);
        $opened = CacheEnvelope::open('key', $envelope);
        check($opened[0] === "0\nstring" && $opened[1][1] === "\nparams", 'string and multiline callback params round trip');
        foreach (['', '0'] as $value)
            check(CacheEnvelope::open('key', CacheEnvelope::seal('key', $value, [null, null], 60))[0] === $value, 'falsey string remains valid');
        for ($offset = 0; $offset < strlen($envelope); $offset++) {
            $tampered = $envelope; $tampered[$offset] = chr(ord($tampered[$offset]) ^ 1);
            check(CacheEnvelope::open('key', $tampered) === null, 'every envelope byte is authenticated');
        }
        check(CacheEnvelope::open('other-key', $envelope) === null, 'entry bound to cache key');
        $rotated = hash_hmac('sha256', "key\0".substr($envelope,65), hex2bin(str_repeat('22',32)))."\n".substr($envelope,65);
        check(CacheEnvelope::open('key', $rotated) === null, 'different installation/rotated key cannot authenticate existing entry');
        check(CacheEnvelope::open('key', ['data' => $envelope]) === null, 'legacy Memcached arrays rejected');
        check(CacheEnvelope::open('key', "0 60 64 0\n\n".gzcompress(serialize(new WakeupProbe))) === null && WakeupProbe::$calls === 0, 'unsigned legacy object never wakes');
        foreach ([[(time()-61), 60, 65], [time(), 0, 65], [time(), 60, 64], [time()+60, 60, 65]] as [$stamp,$life,$revision]) {
            $body = "AOWOW-CACHE-1 $stamp $life $revision\n".gzcompress(serialize([new WakeupProbe, [null,null]]));
            check(CacheEnvelope::open('key', signed($body)) === null && WakeupProbe::$calls === 0, 'expiry/revision checked before object restoration');
        }
        check(CacheEnvelope::open('key', signed('bad-header')) === null, 'malformed authenticated header is a miss');
        check(CacheEnvelope::open('key', signed('AOWOW-CACHE-1 '.time().' 60 65'."\nbad-zlib")) === null, 'malformed authenticated compression is a miss');
        foreach ([['not-callback'], ['text', [1, null]], [false, [null,null]], ['text', [null]]] as $payload)
            check(CacheEnvelope::open('key', signed('AOWOW-CACHE-1 '.time().' 60 65'."\n".gzcompress(serialize($payload)))) === null, 'malformed authenticated shape rejected');

        $data = ['name_loc0' => 'Synthetic name', 'name_loc2' => 'Nom'];
        $localized = new Aowow\LocString($data);
        $template = new Aowow\Template\PageTemplate('spell');
        $tabs = new Aowow\Tabs([]);
        $list = new Aowow\Listview(['data' => [['id' => 1, 'name' => $localized]]], 'spell');
        $tabs->addListviewTab($list);
        $template->lvTabs = $tabs;
        $template->article = new Aowow\Markup('Synthetic [b]markup[/b]', [], 'fixture');
        $template->infobox = new Aowow\InfoboxMarkup(['Synthetic info'], [], 'fixture');
        $template->registerDisplayHook('title', [CacheFixture::class, 'displayHook'], ' hook-param');
        // A live context normally supplies rawData; set it directly for this DB-free template fixture.
        $raw = new ReflectionProperty(Aowow\Template\PageTemplate::class, 'rawData');
        $raw->setValue($template, ['lvTabs' => $tabs, 'article' => $template->article, 'infobox' => $template->infobox, 'localized' => $localized, 'title' => 'Title']);
        $packed = CacheEnvelope::seal('template', $template, [[CacheFixture::class, 'hook'], 'params'], 60);
        $restored = CacheEnvelope::open('template', $packed);
        check($restored[0] instanceof Aowow\Template\PageTemplate, 'actual PageTemplate restored');
        check($restored[0]->lvTabs instanceof Aowow\Tabs && count($restored[0]->lvTabs) === 1, 'Tabs/Listview object graph survives');
        check($restored[0]->article instanceof Aowow\Markup && $restored[0]->infobox instanceof Aowow\InfoboxMarkup, 'markup components survive');
        check((string)$restored[0]->localized === 'Synthetic name', 'LocString enum/store/formatter survive');
        check($restored[1] === [[CacheFixture::class, 'hook'], 'params'], 'post-cache callback survives');
        check($restored[0]->title === 'Title hook-param', 'registered template display hook runs after restoration');

        $fixture = new CacheFixture; $fixture->setOnCacheLoaded([CacheFixture::class, 'hook'], ' suffix');
        $fixture->saveCache('cached'); $result = null;
        check((new CacheFixture)->loadCache($result) && $result === 'cached', 'actual file-cache hit');
        $loader = new CacheFixture; $loader->loadCache($result);
        check($loader->applyOnCacheLoaded($result) === 'cached suffix', 'actual restored callback executes');
        $protected = new CacheFixture; $protected->protect(); $protected->saveCache('cached');
        $loader = new CacheFixture;
        check($loader->loadCache($result) && $loader->applyOnCacheLoaded($result) === 'cached protected', 'protected responder callback remains callable');
        $files = glob($directory.'/cache/*/*/*'); check(count($files) === 1, 'expected private cache layout');
        $file = $files[0]; $validFile = file_get_contents($file);
        check((fileperms($file) & 0777) === 0600 && (fileperms(dirname($file)) & 0777) === 0700, 'new cache files and directories are private');
        chmod(dirname($file), 0700); clearstatcache(); (new CacheFixture)->saveCache('cached'); clearstatcache();
        check((fileperms(dirname($file)) & 0777) === 0700, 'cache helpers do not relax existing directory modes');
        file_put_contents($file, 'unsigned-forgery');
        check(!(new CacheFixture)->loadCache($result), 'forged file rejected');
        unlink($file); file_put_contents($directory.'/outside', $validFile); symlink($directory.'/outside', $file);
        check(!(new CacheFixture)->loadCache($result), 'symlink file rejected');
        (new CacheFixture)->saveCache('replacement');
        check(!is_link($file) && file_get_contents($directory.'/outside') === $validFile, 'atomic publication replaces link without changing its target');
        check(glob(dirname($file).'/.cache-*') === [], 'atomic publication leaves no temporary files');
        file_put_contents($file, $validFile); (new CacheFixture)->deleteCache(CACHE_MODE_FILECACHE);
        check(!is_file($file), 'refresh deletes cache under configured directory');
        file_put_contents($file, $validFile);

        $server = proc_open([PHP_BINARY, __FILE__, '--server', $stateFile, $ready], [['pipe','r'], ['file',$directory.'/server-out','w'], ['file',$directory.'/server-error','w']], $pipes);
        if (!is_resource($server)) throw new RuntimeException('Cannot start raw protocol worker'); fclose($pipes[0]);
        for ($i=0; $i<100 && !is_file($ready); $i++) usleep(10000);
        check(is_file($ready), 'raw protocol worker ready');
        Aowow\Cfg::$mode = CACHE_MODE_MEMCACHED | CACHE_MODE_FILECACHE;
        (new CacheFixture)->saveCache('memcached');
        $state = json_decode(file_get_contents($stateFile), true); $memKey = array_key_first($state);
        check(Memcached::$options[Memcached::OPT_COMPRESSION] === false, 'writer disables pre-authentication compression');
        check($state[$memKey]['expiry'] > time(), 'writer uses actual server expiry');
        check((new CacheFixture)->loadCache($result) && $result === 'memcached' && CacheFixture::$cacheStats[0] === CACHE_MODE_MEMCACHED, 'actual trait raw Memcached hit');
        foreach ([['flags' => 4, 'data' => base64_encode(serialize(new WakeupProbe))], ['flags' => 0, 'data' => base64_encode('forged')],
            ['flags' => 0, 'data' => base64_encode('truncated'), 'length' => 100], ['flags' => 0, 'data' => '', 'length' => CacheEnvelope::MAX_BYTES+1],
            ['flags' => 0, 'data' => base64_encode($validFile), 'suffix' => "\r\nBAD\r\n"]] as $entry) {
            file_put_contents($stateFile, json_encode([$memKey => $entry]));
            check((new CacheFixture)->loadCache($result) && $result === 'memcached' && CacheFixture::$cacheStats[0] === CACHE_MODE_FILECACHE && WakeupProbe::$calls === 0, 'bad raw value falls back to signed file without unserializing flags');
        }
        foreach (['expired' => 'AOWOW-CACHE-1 '.(time()-61).' 60 65', 'revision' => 'AOWOW-CACHE-1 '.time().' 60 64'] as $label => $head) {
            $cacheKey = substr($memKey, strlen(CacheEnvelope::memcachedKey('')));
            $bad = signed($head."\n".gzcompress(serialize([new WakeupProbe, [null,null]])), $cacheKey);
            file_put_contents($stateFile, json_encode([$memKey => ['flags'=>0,'data'=>base64_encode($bad)]]));
            check((new CacheFixture)->loadCache($result) && CacheFixture::$cacheStats[0] === CACHE_MODE_FILECACHE && WakeupProbe::$calls === 0, 'stale authenticated Memcached entry cannot wake objects');
        }
        file_put_contents($stateFile, json_encode([$memKey => ['flags'=>0,'data'=>base64_encode($validFile)]]));
        (new CacheFixture)->deleteCache(CACHE_MODE_MEMCACHED);
        check(json_decode(file_get_contents($stateFile), true) === [], 'refresh uses installation-specific Memcached prefix');
        foreach (['--missing-key', '--invalid-key'] as $mode) {
            [$exit, $out, $error] = child([$mode]);
            check($exit === 0 && str_contains($out, 'PASS: 3') && $error === '', 'unconfigured/invalid key installation fails closed');
        }
        echo "PASS: $checks signed cache/template/raw-protocol checks\n";
    }
    finally {
        if (is_resource($server)) { proc_terminate($server); proc_close($server); }
        $paths = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($paths as $path) if ($path->isDir() && !$path->isLink()) rmdir($path->getPathname()); else unlink($path->getPathname());
        rmdir($directory);
    }
}
