<?php
// Real cURL/TLS against a loopback worker; only the fixed remote origin is mapped to this disposable fixture.
namespace Aowow {
    class VideoFixture {
        public static bool $curl = true;
        public static bool $dns = true;
        public static bool $badCert = false;
        public static array $options = [];
    }
    class Lang {
        public static function main(mixed ...$args) : string { return 'internal'; }
        public static function video(mixed ...$args) : string { return end($args); }
    }
    class User { public static bool $allowed = true; public static function canSuggestVideo() : bool { return self::$allowed; } }
    class ContributionBudget {
        public static bool $allowed = true;
        public static function reserve(string $action) : bool { return self::$allowed; }
        public static function error() : string { return 'limit'; }
    }
    class VideoMgr {
        public static array $saved = [];
        public static function saveSuggestion(\stdClass $info, int $type, int $typeId, ?string &$hash) : bool {
            self::$saved[]=$info; $hash=str_repeat('a',16); return true;
        }
    }
    class TextResponse {
        protected array $_post=[];
        protected function assertPOST(string ...$args) : bool { return isset($this->_post['videourl']); }
    }
    function extension_loaded(string $name) : bool { return $name === 'curl' ? VideoFixture::$curl : \extension_loaded($name); }
    function curl_version() : array {
        $version=\curl_version(); if (!VideoFixture::$dns) $version['features'] &= ~CURL_VERSION_ASYNCHDNS; return $version;
    }
    function curl_init(string $url) : \CurlHandle|false {
        if (!str_starts_with($url,'https://www.youtube.com/oembed?')) throw new \RuntimeException('Unexpected outbound origin');
        return \curl_init(str_replace('https://www.youtube.com', 'https://localhost:'.getenv('AOWOW_TEST_VIDEO_PORT'), $url));
    }
    function curl_setopt_array(\CurlHandle $curl, array $options) : bool {
        VideoFixture::$options=$options;
        $options[CURLOPT_CAINFO]=VideoFixture::$badCert ? '/nonexistent-fixture-ca.pem' : getenv('AOWOW_TEST_VIDEO_CA');
        $options[CURLOPT_PROXY]='';
        return \curl_setopt_array($curl,$options);
    }
}
namespace {
    define('AOWOW_REVISION',67);
    require __DIR__.'/../includes/components/youtube.class.php';
    require __DIR__.'/../endpoints/video/add.php';
    if (($argv[1] ?? '') === '--server') {
        $context=stream_context_create(['ssl'=>['local_cert'=>$argv[2], 'local_pk'=>$argv[3], 'verify_peer'=>false]]);
        $listener=stream_socket_server('tls://127.0.0.1:0',$error,$message,STREAM_SERVER_BIND|STREAM_SERVER_LISTEN,$context);
        if (!$listener) exit(1);
        file_put_contents($argv[4],substr(strrchr(stream_socket_get_name($listener,false),':'),1));
        while (true) {
            $client=@stream_socket_accept($listener,15);
            if (!$client) continue;
            stream_set_timeout($client,2);
            $request=fgets($client,8192);
            while (($line=fgets($client,8192)) !== false && trim($line)!=='') {}
            preg_match('/watch%3Fv%3D([a-zA-Z0-9_-]{11})|watch\?v=([a-zA-Z0-9_-]{11})/', $request,$m);
            $id=$m[1] ?: ($m[2] ?? 'v1234567890');
            $info=['type'=>'video','provider_name'=>'YouTube','title'=>'Normal title','thumbnail_url'=>'https://i.ytimg.com/vi/'.$id.'/hqdefault.jpg','thumbnail_width'=>480,'thumbnail_height'=>360];
            $status=200; $extra=''; $body=null;
            switch ($id[0]) {
                case 'p': $status=401; break;
                case 'n': $status=404; break;
                case 'r': $status=302; $extra="Location: https://localhost/SECRET_SENTINEL\r\n"; break;
                case 'm': $body='{invalid'; break;
                case 'a': $body='[]'; break;
                case 't': $info['title']="newline\ninjection"; break;
                case 'u': $info['thumbnail_url']='http://127.0.0.1/private'; break;
                case 'h': $info['thumbnail_height']='360'; break;
                case 'w': $info['thumbnail_width']=999999; break;
                case 'e': unset($info['thumbnail_url']); break;
                case 'l': $info['title']=str_repeat('Ж',100); break;
                case 'o': $body=str_repeat('x',65537); break;
                case 'b': $extra='X-Huge: '.str_repeat('x',17000)."\r\n"; break;
                case 's': sleep(7); break;
            }
            $body ??= json_encode($info,JSON_UNESCAPED_SLASHES);
            @fwrite($client,"HTTP/1.1 $status Fixture\r\nContent-Type: application/json\r\nContent-Length: ".strlen($body)."\r\nConnection: close\r\n".$extra."\r\n".$body);
            fclose($client);
        }
    }
    error_reporting(E_ALL & ~E_DEPRECATED);
    $checks=0;
    function check(bool $ok,string $label) : void { global $checks; $checks++; if (!$ok) throw new RuntimeException($label); }
    $root=sys_get_temp_dir().'/aowow-video-'.bin2hex(random_bytes(8)); mkdir($root,0700);
    $server=null;
    try {
        $key=openssl_pkey_new(['private_key_bits'=>2048]);
        $csr=openssl_csr_new(['commonName'=>'localhost'],$key,['digest_alg'=>'sha256']);
        $certificate=openssl_csr_sign($csr,null,$key,1,['digest_alg'=>'sha256']);
        openssl_x509_export_to_file($certificate,$root.'/ca.pem'); openssl_pkey_export_to_file($key,$root.'/key.pem');
        $server=proc_open([PHP_BINARY,__FILE__,'--server',$root.'/ca.pem',$root.'/key.pem',$root.'/ready'],[0=>['pipe','r'],1=>['file',$root.'/server.log','a'],2=>['file',$root.'/server.log','a']],$pipes);
        fclose($pipes[0]);
        for($i=0;$i<200&&!is_file($root.'/ready');$i++) usleep(10000);
        check(is_file($root.'/ready'),'TLS worker ready');
        putenv('AOWOW_TEST_VIDEO_PORT='.trim(file_get_contents($root.'/ready'))); putenv('AOWOW_TEST_VIDEO_CA='.$root.'/ca.pem');
        $id='v1234567890';
        $info=Aowow\Youtube::fetch($id,$status);
        check($status===200 && $info?->id===$id && $info->title==='Normal title','real TLS/cURL valid metadata');
        $options=Aowow\VideoFixture::$options;
        check($options[CURLOPT_FOLLOWLOCATION]===false && $options[CURLOPT_PROTOCOLS]===CURLPROTO_HTTPS,'HTTPS-only redirects disabled');
        check($options[CURLOPT_SSL_VERIFYPEER]===true && $options[CURLOPT_SSL_VERIFYHOST]===2,'TLS verification remains mandatory');
        check($options[CURLOPT_CONNECTTIMEOUT_MS]===2000 && $options[CURLOPT_TIMEOUT_MS]===5000,'connect and total deadlines');
        foreach (['m','a','t','u','h','w','e','o','b'] as $mode)
            check(Aowow\Youtube::fetch($mode.'1234567890',$status)===null,'invalid/oversized '.$mode.' rejected');
        foreach (['p'=>401,'n'=>404,'r'=>302] as $mode=>$expected) {
            check(Aowow\Youtube::fetch($mode.'1234567890',$status)===null && $status===$expected,'HTTP status '.$expected.' preserves failure semantics');
        }
        check(mb_strlen(Aowow\Youtube::fetch('l1234567890',$status)->title)===64,'long Unicode title normalized to schema size');
        Aowow\VideoFixture::$badCert=true;
        check(Aowow\Youtube::fetch($id,$status)===null,'untrusted TLS fails'); Aowow\VideoFixture::$badCert=false;
        Aowow\VideoFixture::$curl=false;
        check(Aowow\Youtube::fetch($id,$status)===null,'missing curl fails'); Aowow\VideoFixture::$curl=true;
        Aowow\VideoFixture::$dns=false;
        check(Aowow\Youtube::fetch($id,$status)===null,'unbounded synchronous DNS fails closed'); Aowow\VideoFixture::$dns=true;
        foreach (['','too-long-video-id','../../private','v123456789!'] as $bad)
            check(Aowow\Youtube::fetch($bad,$status)===null && $status===0,'invalid ID fails before transfer');
        $method=new ReflectionMethod(Aowow\VideoAddResponse::class,'handleAdd');
        foreach (['https://www.youtube.com/watch?v='.$id,'https://youtu.be/'.$id,'http://youtu.be/'.$id.'?t=30'] as $url) {
            $response=(new ReflectionClass(Aowow\VideoAddResponse::class))->newInstanceWithoutConstructor();
            (new ReflectionProperty($response,'_post'))->setValue($response,['videourl'=>$url]);
            check($method->invoke($response),'compatible URL submits metadata');
        }
        Aowow\ContributionBudget::$allowed=false;
        $count=count(Aowow\VideoMgr::$saved);
        check(!$method->invoke($response) && $_SESSION['error']['vi']==='limit' && count(Aowow\VideoMgr::$saved)===$count,'quota stops endpoint before suggestion');
        Aowow\ContributionBudget::$allowed=true;
        foreach (['https://youtu.be/'.$id.'evil','file:///private','https://youtube.com.evil/watch?v='.$id,'https://youtu.be/'] as $url) {
            (new ReflectionProperty($response,'_post'))->setValue($response,['videourl'=>$url]);
            check(!$method->invoke($response),'malformed caller URL rejected');
        }
        $start=microtime(true);
        check(Aowow\Youtube::fetch('s1234567890',$status)===null,'slow upstream aborted');
        check(microtime(true)-$start < 6.5,'slow upstream releases worker at total deadline');
        echo "PASS: $checks video HTTP/TLS/schema checks\n";
    }
    finally {
        if(is_resource($server)) { proc_terminate($server); proc_close($server); }
        foreach(glob($root.'/*') as $file) unlink($file); rmdir($root);
    }
}
