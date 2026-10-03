<?php
// Only synthetic disposable files; no runtime configuration or repository uploads are read.
namespace Aowow {
    class DB {
        public static int $writes = 0;
        public static function isConnected(int $database) : bool { return true; }
        public static function Aowow() : self { return new self; }
        public function qry(string $sql, mixed ...$args) : int { self::$writes++; return 1; }
    }
    class User { public static int $groups = 0; public static string $username = 'owner'; }
    class Cfg { public static function get(string $key) : int { return 0; } }
}
namespace {
    use Aowow\{CacheEnvelope,Retention,VideoMgr,ErrorLog,DB};
    define('AOWOW_REVISION',67); define('CLI',true);
    define('AOWOW_CACHE_KEY',str_repeat('a',64));
    $project=dirname(__DIR__);
    require $project.'/includes/defines.php'; require $project.'/includes/utilities.php';
    foreach(['cacheenvelope','retention','videomgr','errorlog'] as $class)require $project.'/includes/components/'.$class.'.class.php';
    $checks=0;
    function check(bool $ok,string $label):void { global $checks; $checks++; if(!$ok)throw new RuntimeException($label); }
    function put(string $path,int $days=0):void {
        if(!is_dir(dirname($path)))mkdir(dirname($path),0700,true);
        file_put_contents($path,'fixture'); touch($path,time()-$days*DAY);
    }
    function removeTree(string $path):void {
        if(is_link($path)||!is_dir($path)){unlink($path);return;}
        foreach(new DirectoryIterator($path) as $entry)if(!$entry->isDot())removeTree($entry->getPathname());
        rmdir($path);
    }
    $root=sys_get_temp_dir().'/aowow-retention-'.bin2hex(random_bytes(8)); mkdir($root,0700); chdir($root);
    try {
        foreach(['static/uploads/temp','static/uploads/screenshots/temp','cache/template','config','includes'] as $dir)mkdir($dir,0700,true);
        $old=[
            'static/uploads/temp/owner-avatar-12-'.str_repeat('a',16).'.jpg',
            'static/uploads/temp/owner-avatar-12-'.str_repeat('a',16).'_original.jpg',
            'static/uploads/temp/owner-1-1-'.str_repeat('b',16),
            'static/uploads/temp/guide-'.str_repeat('c',24).'.png',
            'static/uploads/screenshots/temp/owner-1-1-'.str_repeat('d',16),
            'static/uploads/screenshots/temp/owner-1-1-'.str_repeat('d',16).'_original',
            'cache/template/bounded/ab/c', 'cache/template/ab/cd/legacy-fixture',
            'cache/template/bounded/ab/.cache-'.str_repeat('f',24)
        ];
        foreach($old as $path)put($path,8);
        $preserved=[
            'static/uploads/temp/owner-1-1-'.str_repeat('e',16),
            'static/uploads/temp/unrecognized.txt',
            'static/uploads/screenshots/normal/123.jpg',
            'static/uploads/screenshots/pending/123.jpg',
            'static/uploads/avatars/123.jpg',
            'static/uploads/guides/123.png',
            'cache/template/bounded/ab/d', 'cache/template/metadata.json',
            'config/config.php', 'cache/template/.bounded.lock'
        ];
        foreach($preserved as $path)put($path,str_contains($path,str_repeat('e',16))||str_ends_with($path,'/d')?0:8);
        put('outside',8);
        symlink($root.'/outside','cache/template/bounded/ab/e');
        link('outside','cache/template/bounded/ab/f');
        mkdir('foreign',0700);put('foreign/0',8);symlink($root.'/foreign','cache/template/bounded/aa');
        $cursor=[];$dry=Retention::files(false,'cache/template',$cursor);
        check($dry['staging']['eligible']===4 && $dry['screenshots']['eligible']===2 && $dry['cache']['eligible']===3,'exact dry-run file eligibility');
        foreach($old as $path)check(is_file($path),'dry-run preserves eligible file');
        $apply=Retention::files(true,'cache/template',$cursor);
        check($apply['staging']['removed']===4 && $apply['screenshots']['removed']===2 && $apply['cache']['removed']===3,'apply removes disposable files only');
        foreach($old as $path)check(!file_exists($path),'eligible file removed');
        foreach($preserved as $path)check(is_file($path),'published/fresh/unrecognized/control file preserved');
        check(is_link('cache/template/bounded/ab/e') && file_get_contents('outside')==='fixture','leaf symlink and hard-linked target preserved');
        check(is_link('cache/template/bounded/aa') && is_file('foreign/0'),'ancestor symlink never followed');
        foreach(['.','/','static','static/uploads/temp','config','includes'] as $unsafe) {
            try { Retention::files(true,$unsafe);check(false,'unsafe cache root must fail'); }
            catch(RuntimeException $e) {check($e->getMessage()==='Unsafe retention directory.','protected root rejected');}
        }
        symlink($root.'/cache/template','cache/link');
        try{Retention::files(true,'cache/link');check(false,'linked cache root');}catch(RuntimeException){check(true,'linked cache root rejected');}
        // An ineligible prefix does not starve expired files beyond the first batch.
        for($i=0;$i<1200;$i++)put('static/uploads/temp/unknown-'.$i);
        for($i=0;$i<1100;$i++)put('static/uploads/temp/owner-1-1-'.str_pad((string)$i,16,'0',STR_PAD_LEFT),3);
        $cursor=[];$removed=0;
        for($i=0;$i<8;$i++) {
            $batch=Retention::files(true,'cache/template',$cursor);
            check($batch['staging']['scanned']<=1000,'bounded streaming scan');
            $removed+=$batch['staging']['removed'];
            if(!$batch['staging']['more'])break;
        }
        check($removed===1100,'cursor eventually reaches every expired item beyond fresh/unrecognized prefix');
        check(count(glob('static/uploads/temp/unknown-*'))===1200,'unknown prefix remains untouched');

        // Hash collisions only replace cache entries and remain misses for the displaced key.
        check(CacheEnvelope::FILE_MAX_BYTES*4096===536870912,'fixed payload capacity');
        check(!CacheEnvelope::writeBoundedFile('cache/bounded-test','too-large',str_repeat('x',CacheEnvelope::FILE_MAX_BYTES+1)),'oversized entry rejected before publication');
        $seen=[];$collision=null;
        for($i=0;$i<5000;$i++) {
            $key='fixture-'.$i;$path=CacheEnvelope::filePath('cache/bounded-test',$key);
            check((bool)preg_match('~^cache/bounded-test/bounded/[0-9a-f]{2}/[0-9a-f]$~D',$path),'finite slot path');
            if(isset($seen[$path]) && !$collision)$collision=[$seen[$path],$key,$path];
            $seen[$path]=$key;
        }
        check(count($seen)<=4096 && $collision!==null,'arbitrary key cardinality produces at most 4096 payload paths');
        [$first,$second,$path]=$collision;
        foreach([$first,$second] as $key)check(CacheEnvelope::writeBoundedFile('cache/bounded-test',$key,CacheEnvelope::seal($key,'fixture',[null,null],3600)),'collision write succeeds');
        check(CacheEnvelope::open($first,file_get_contents($path))===null && CacheEnvelope::open($second,file_get_contents($path))[0]==='fixture','displaced entry is a miss, never another key result');
        check((fileperms($path)&0777)===0600 && (fileperms(dirname($path))&0777)===0700,'bounded cache remains private');
        $lock=fopen('cache/bounded-test/.bounded.lock','c+b');flock($lock,LOCK_EX);
        check(!CacheEnvelope::writeBoundedFile('cache/bounded-test','locked','fixture'),'contended writer releases request without waiting');
        flock($lock,LOCK_UN);fclose($lock);
        CacheEnvelope::clearFiles('cache/bounded-test');check(!file_exists($path),'finite invalidation removes the published slot');

        $video=(object)['id'=>'abcdefghijk','title'=>'Fixture video','thumbnail_url'=>'https://i.ytimg.com/vi/abcdefghijk/hqdefault.jpg','thumbnail_height'=>360,'thumbnail_width'=>480];
        $uid=null;check(VideoMgr::saveSuggestion($video,1,1,$uid),'legacy video staging works');
        $videoPath='static/uploads/temp/owner-1-1-'.$uid;
        check((fileperms($videoPath)&0777)===0600,'video staging is private');
        $loaded=null;check(VideoMgr::loadSuggestion($loaded,1,1,$uid) && $loaded==$video,'five-field confirmation round trip');
        touch($videoPath,time()-DAY-1);clearstatcache();check(!VideoMgr::loadSuggestion($loaded,1,1,$uid),'expired video confirmation denied');
        touch($videoPath,time());file_put_contents($videoPath,"abcdefghijk\nTitle\nhttps://evil.example/a\n360\n480\n");clearstatcache();
        check(!VideoMgr::loadSuggestion($loaded,1,1,$uid),'malformed staged response denied');
        VideoMgr::dropTempFile();check(!is_file($videoPath),'video staging still has explicit completion cleanup');

        for($i=0;$i<100;$i++)ErrorLog::record($i,'PHP_ERROR',__FILE__,$i,LOG_LEVEL_WARN);
        check(DB::$writes===20,'one request cannot emit more than twenty diagnostics');
        echo "PASS: $checks file retention/cache capacity/video staging/log budget checks\n";
    }
    finally { chdir($project);removeTree($root); }
}
