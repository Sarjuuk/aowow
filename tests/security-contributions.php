<?php
// Dedicated real-SQL fixture only. Never loads runtime configuration, account credentials or world data.
namespace Aowow {
    class User {
        public static int $id=7;
        public static int $groups=0;
        public static function isInGroup(int $mask) : bool { return (self::$groups & $mask)!==0; }
    }
    class Lang { public static function main(string $key) : string { return $key; } }
    class Markup { public static function parseTags(string $body) : array { return []; } }
    class Cfg { public static function get(string $key) : mixed { return $key==='CACHE_DIR' ? 'cache/template' : 0; } }
}
namespace {
    use Aowow\{DB,DibiConnection,ContributionBudget,CommunityContent,Retention,User};
    if(getenv('AOWOW_TEST_DATABASE')!=='aowow_security_test_resources') { fwrite(STDERR,"Use only the disposable aowow_security_test_resources fixture; see tests/README.md.\n"); exit(1); }
    define('AOWOW_REVISION',67); define('CLI',true); define('CLI_HAS_E',false); define('OS_WIN',false);
    $root=dirname(__DIR__);
    require $root.'/includes/defines.php';
    $source=getenv('AOWOW_TEST_DIBI_DIR');
    if($source) {
        foreach(['interfaces','exceptions','dibi'] as $file) require $source.'/src/Dibi/'.$file.'.php';
        spl_autoload_register(function($class)use($source){ if(str_starts_with($class,'Dibi\\'))require $source.'/src/'.str_replace('\\','/',$class).'.php'; });
    } else require $root.'/includes/libs/autoload.php';
    foreach(['database.php','type.class.php','utilities.php','components/contributionbudget.class.php','components/communitycontent.class.php','components/report.class.php','components/retention.class.php'] as $file) require $root.'/includes/'.$file;
    $options=['driver'=>'mysqli','host'=>getenv('AOWOW_TEST_DB_HOST')?:'127.0.0.1','port'=>(int)(getenv('AOWOW_TEST_DB_PORT')?:3306),'username'=>'root','password'=>'','database'=>'aowow_security_test_resources','charset'=>'utf8mb4','substitutes'=>[''=>'aowow_']];
    $db=new DibiConnection($options);
    $db->query("SET SESSION sql_mode = ''");
    (new ReflectionProperty(DB::class,'interfaceCache'))->setValue(null,[DB_AOWOW=>$db]);
    (new ReflectionProperty(DB::class,'interfaceTimes'))->setValue(null,[DB_AOWOW=>[time()+86400,86400]]);
    if(getenv('AOWOW_RESOURCE_CHILD')) {
        require $root.'/includes/locale.class.php';
        require $root.'/includes/components/cacheenvelope.class.php';
        return;
    } // Continue into the real CLI entrypoint with synthetic configuration only.
    if(($argv[1]??'')==='--worker') { User::$id=(int)$argv[2]; echo ContributionBudget::reserve($argv[3],1)?'yes':'no'; exit; }
    $checks=0;
    function check(bool $ok,string $label):void { global $checks; $checks++; if(!$ok)throw new RuntimeException($label); }
    function resetBudgets():void { global $db; $db->onEvent=[]; $db->query('DELETE FROM ::contribution_budget'); }
    function used(int $owner,string $bucket):int { global $db; return (int)$db->query('SELECT used FROM ::contribution_budget WHERE owner=%i AND bucket=%s',$owner,$bucket)->fetchSingle(); }
    function schema(string $name):void {
        global $db,$root;
        preg_match('/CREATE TABLE (?:IF NOT EXISTS )?`aowow_'.preg_quote($name,'/').'` \(.*?\) ENGINE=.*?;/s',file_get_contents($root.'/setup/sql/01-db_structure.sql'),$match);
        if(!$match)throw new RuntimeException('Fixture schema missing'); $db->nativeQuery($match[0]);
    }
    $db->query('SET FOREIGN_KEY_CHECKS=0');
    foreach($db->query('SHOW TABLES')->fetchPairs() as $table)$db->query('DROP TABLE %n',$table);
    $db->query('SET FOREIGN_KEY_CHECKS=1');
    schema('contribution_budget');
    foreach(ContributionBudget::POLICY as $action=>[$limit,$charge,$pool]) {
        resetBudgets(); User::$id=7;
        for($i=0;$i<$limit;$i++)check(ContributionBudget::reserve($action,1),'per-account reservation '.$action);
        check(!ContributionBudget::reserve($action,1) && ContributionBudget::error()==='contributionLimit','daily boundary '.$action);
        check(used(7,$action)===$limit && used(0,$action)===$limit,'blocked request rolls back global work '.$action);
        check(used(7,'bytes-'.$pool)===($charge+1)*$limit,'byte reservations '.$action);
    }
    resetBudgets();
    $db->query("INSERT INTO ::contribution_budget VALUES (0,'comment',10000,UNIX_TIMESTAMP()+3600)");
    check(!ContributionBudget::reserve('comment',1),'global daily cap');
    check(used(7,'comment')===0,'global rejection creates no account reservation');
    resetBudgets();
    $db->query("INSERT INTO ::contribution_budget VALUES (7,'bytes-text',268435456,0)");
    check(!ContributionBudget::reserve('comment',1),'account lifetime capacity');
    check(used(0,'comment')===0 && used(0,'bytes-text')===0,'capacity rejection rolls back work/global bytes');
    resetBudgets();
    $db->query("INSERT INTO ::contribution_budget VALUES (0,'bytes-upload',34359738368,0)");
    check(!ContributionBudget::reserve('screenshot'),'global retained upload capacity');
    check(used(7,'screenshot')===0,'upload capacity blocks before work');
    resetBudgets();
    $db->query("INSERT INTO ::contribution_budget VALUES (7,'comment',100,UNIX_TIMESTAMP()-1), (0,'comment',10000,UNIX_TIMESTAMP()-1)");
    check(ContributionBudget::reserve('comment',1) && used(7,'comment')===1 && used(0,'comment')===1,'expired daily windows reset atomically');
    foreach([['unknown',1],['comment',-1],['guide',1048577]] as [$action,$bytes])check(!ContributionBudget::reserve($action,$bytes),'malformed budget rejected');
    User::$id=0; check(!ContributionBudget::reserve('video'),'anonymous contribution rejected'); User::$id=7;
    resetBudgets();
    $db->nativeQuery("CREATE TRIGGER budget_fail BEFORE UPDATE ON aowow_contribution_budget FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SECRET_SENTINEL'");
    check(!ContributionBudget::reserve('guide',1) && ContributionBudget::error()==='intError','SQL failure denies work');
    check((int)$db->query('SELECT COUNT(*) FROM ::contribution_budget')->fetchSingle()===0,'SQL failure rolls back reservations');
    $db->query('DROP TRIGGER budget_fail');
    $db->onEvent[]=static function(Dibi\Event $event){ if($event->sql==='COMMIT')throw new RuntimeException('lost acknowledgement'); };
    check(!ContributionBudget::reserve('video'),'uncertain commit denies work'); $db->onEvent=[];
    check(used(7,'video')===1 && used(0,'video')===1,'uncertain commit conservatively retains atomic charges');

    resetBudgets();
    $db->query("INSERT INTO ::contribution_budget VALUES (7,'comment',99,UNIX_TIMESTAMP()+3600)");
    $workers=[];
    for($i=0;$i<10;$i++) {
        $cmd=[PHP_BINARY]; if($extension=getenv('AOWOW_TEST_MYSQLI_EXTENSION'))array_push($cmd,'-d','extension='.$extension);
        array_push($cmd,__FILE__,'--worker','7','comment');
        $process=proc_open($cmd,[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes); fclose($pipes[0]); $workers[]=[$process,$pipes];
    }
    $wins=0;
    foreach($workers as [$process,$pipes]) { $out=stream_get_contents($pipes[1]);fclose($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[2]);check(proc_close($process)===0,'independent worker completed: '.$error);$wins+=(int)($out==='yes'); }
    check($wins===1 && used(7,'comment')===100 && used(0,'comment')===1,'one concurrent reservation at the final slot');

    // Actual bounded comment readers with real joins, stable dates, visibility and focus/offset pagination.
    $db->query('CREATE TABLE ::account (id int unsigned PRIMARY KEY, username varchar(20)) ENGINE=InnoDB');
    $db->query("INSERT INTO ::account VALUES (7,'author'),(8,'other')"); schema('comments');
    $db->query('CREATE TABLE ::user_ratings (entry int, type int, userId int, value int) ENGINE=InnoDB');
    $db->query('CREATE TABLE ::reports (id int, subject int, mode int, userId int) ENGINE=InnoDB');
    for($i=1;$i<=250;$i++)$db->query('INSERT INTO ::comments (id,type,typeId,userId,roles,body,date) VALUES (%i,1,1,7,0,%s,123)', $i,'Body '.$i);
    for($i=251;$i<=500;$i++)$db->query('INSERT INTO ::comments (id,userId,roles,body,date,replyTo) VALUES (%i,7,0,%s,123,1)', $i,'Reply '.$i);
    $page=1;$total=0;$comments=CommunityContent::getComments(1,1,$page,$total);
    check(count($comments)===100 && $comments[0]['id']===1 && $comments[99]['id']===100 && $total===250,'first bounded comment page');
    check(count($comments[0]['replies'])===5 && $comments[0]['nreplies']===250,'initial thread still previews five replies');
    $page=2;$comments=CommunityContent::getComments(1,1,$page,$total);
    check(count($comments)===100 && $comments[0]['id']===101 && $comments[99]['id']===200,'second stable page with tied timestamps');
    $page=999999;$comments=CommunityContent::getComments(1,1,$page,$total);
    check($page===3 && count($comments)===50 && $comments[49]['id']===250,'out-of-range page clamps without hiding tail');
    check(CommunityContent::commentPage(201)===3 && CommunityContent::commentPage(100)===1,'legacy anchors resolve their page');
    $replies=CommunityContent::getCommentReplies(1,PHP_INT_MAX,$total,5);
    check(count($replies)===100 && $replies[0]['id']===256 && $total===250 && $replies[0]['totalReplies']===250,'bounded reply offset/total even with unlimited caller limit');
    $replies=CommunityContent::getCommentReplies(1,100,$total,0,499);
    check(count($replies)===50 && $replies[0]['id']===451 && $replies[49]['id']===500,'focused old/new reply remains reachable');
    $db->query('UPDATE ::comments SET flags=%i,userId=8 WHERE id IN (100,101,499)',CC_FLAG_DELETED);
    $page=1;$comments=CommunityContent::getComments(1,1,$page,$total);
    check($total===248 && $comments[99]['id']===102,'deleted comments excluded before pagination');
    check(CommunityContent::commentPage(201)===2,'anchor rank uses viewer visibility');
    check(count(CommunityContent::getCommentReplies(1,100,$total,0,499))===100 && $total===249,'hidden focus cannot reveal deleted body');
    User::$id=8;$page=1;$comments=CommunityContent::getComments(1,1,$page,$total);
    check($total===250 && $comments[99]['id']===100,'owner retains existing deleted-comment visibility');
    User::$id=7;User::$groups=U_GROUP_COMMENTS_MODERATOR;$page=2;$comments=CommunityContent::getComments(1,1,$page,$total);
    check($total===250 && $comments[0]['id']===101,'moderator visibility retained'); User::$groups=0;
    $_GET=['item'=>'1','coPage'=>9,'lang'=>'fr']; check(CommunityContent::commentPageUrl(2)==='?item=1&coPage=2&lang=fr#comments','page URL preserves route/locale');

    schema('errors');schema('account_password_budget');schema('screenshot_uploads');
    $db->query("INSERT INTO ::errors (date,version,phpError,file,line,query,post,userGroups) VALUES (UNIX_TIMESTAMP()-2592001,67,1,'old',1,'','',0),(UNIX_TIMESTAMP(),67,1,'recent',1,'','',0)");
    $db->query("INSERT INTO ::account_password_budget VALUES ('ip','fixture',1,UNIX_TIMESTAMP()-1),('ip','live',1,UNIX_TIMESTAMP()+100)");
    $db->query("INSERT INTO ::screenshot_uploads (uploadKey,userIdOwner,expires) VALUES (%s,7,UNIX_TIMESTAMP()-1),(%s,7,UNIX_TIMESTAMP()+100)",str_repeat('a',64),str_repeat('b',64));
    resetBudgets(); $db->query("INSERT INTO ::contribution_budget VALUES (7,'comment',100,UNIX_TIMESTAMP()-1),(7,'reply',1,UNIX_TIMESTAMP()+100),(7,'bytes-text',12345,0)");
    $counts=Retention::database(false);
    check($counts===['errors'=>1,'account_password_budget'=>1,'screenshot_uploads'=>1,'contribution_budget'=>1],'dry-run selects only expired disposable records');
    check((int)$db->query('SELECT COUNT(*) FROM ::errors')->fetchSingle()===2,'dry-run makes no database deletion');
    check(Retention::database(true)===$counts,'apply performs the previewed DB expiry');
    check(used(7,'bytes-text')===12345 && used(7,'reply')===1,'permanent capacity and live window retained');
    check((int)$db->query('SELECT COUNT(*) FROM ::comments')->fetchSingle()===500,'published comments never age-deleted');
    // Apply the actual update to the preceding schema, then exercise the real CLI preview/apply dispatcher.
    require $root.'/includes/setup/cli.class.php';
    require $root.'/includes/setup/sqlupdate.class.php';
    $temp=sys_get_temp_dir().'/aowow-resources-'.bin2hex(random_bytes(8));mkdir($temp,0700);
    function cleanup(string $path):void {
        if(is_link($path)||!is_dir($path)){unlink($path);return;}
        foreach(new DirectoryIterator($path) as $entry)if(!$entry->isDot())cleanup($entry->getPathname());
        rmdir($path);
    }
    function runCli(array $arguments,string $cwd):array {
        $cmd=[PHP_BINARY]; if($extension=getenv('AOWOW_TEST_MYSQLI_EXTENSION'))array_push($cmd,'-d','extension='.$extension);
        array_push($cmd,$cwd.'/aowow',...$arguments);
        $env=getenv();$env['AOWOW_RESOURCE_CHILD']='1';
        $process=proc_open($cmd,[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,$cwd,$env);fclose($pipes[0]);
        $out=stream_get_contents($pipes[1]);fclose($pipes[1]);$out.=stream_get_contents($pipes[2]);fclose($pipes[2]);
        return [proc_close($process),$out];
    }
    try {
        $db->query('DROP TABLE ::contribution_budget');
        $db->query('ALTER TABLE ::errors DROP INDEX retention_date');
        $db->query('ALTER TABLE ::comments DROP INDEX comment_page, DROP INDEX reply_page');
        schema('dbversion');$db->query('INSERT INTO ::dbversion (`date`,part) VALUES (1790000000,1)');
        mkdir($temp.'/updates');copy($root.'/setup/sql/updates/1791000000_01.sql',$temp.'/updates/1791000000_01.sql');
        $version=Aowow\SqlUpdate::apply($db,$temp.'/updates');
        check((int)$version['date']===1791000000 && trim($version['build'])==='globaljs','actual migration advances metadata and requests the matching JS build');
        check($db->query('SELECT status FROM ::sql_update_journal')->fetchSingle()==='applied' && (int)$db->query('SELECT statements FROM ::sql_update_journal')->fetchSingle()===4,'actual four-statement migration is acknowledged');
        check(ContributionBudget::reserve('video'),'newly migrated budget supports runtime writes');
        check(Aowow\SqlUpdate::apply($db,$temp.'/updates')['build']===$version['build'],'completed migration never repeats indexes/build prompt');
        $cli=$temp.'/checkout';mkdir($cli,0700);
        foreach(['includes','setup','setup/tools','setup/tools/clisetup','static','static/uploads','static/uploads/temp','cache','cache/template'] as $dir)mkdir($cli.'/'.$dir,0700);
        copy($root.'/aowow',$cli.'/aowow');
        file_put_contents($cli.'/includes/kernel.php','<?php require '.var_export(__FILE__,true).';');
        symlink($root.'/includes/setup',$cli.'/includes/setup');symlink($root.'/setup/setup.php',$cli.'/setup/setup.php');
        foreach(['setupScript.class.php','utilityScript.class.php','CLISetup.class.php','dbcreader.class.php'] as $file)symlink($root.'/setup/tools/'.$file,$cli.'/setup/tools/'.$file);
        symlink($root.'/setup/tools/clisetup/prune.us.php',$cli.'/setup/tools/clisetup/prune.us.php');
        $file=$cli.'/static/uploads/temp/owner-1-1-'.str_repeat('a',16);file_put_contents($file,'fixture');touch($file,time()-3*DAY);
        $db->query("INSERT INTO ::errors (date,version,phpError,file,line,query,post,userGroups) VALUES (UNIX_TIMESTAMP()-2592001,67,1,'expired-cli',1,'','',0)");
        [$code,$out]=runCli(['--prune'],$cli);
        check($code===0 && str_contains($out,'eligible') && is_file($file),'real CLI defaults to preview and preserves staging');
        check((int)$db->query('SELECT COUNT(*) FROM ::errors')->fetchSingle()===2 && !is_file($cli.'/cache/maintenance/cursor.json'),'preview does not mutate data or persistent cursor');
        [$code,$out]=runCli(['--prune=apply'],$cli);
        check($code===0 && !is_file($file) && (int)$db->query('SELECT COUNT(*) FROM ::errors')->fetchSingle()===1,'real CLI applies database and file retention');
        check((fileperms($cli.'/cache/maintenance')&0777)===0700 && (fileperms($cli.'/cache/maintenance/cursor.json')&0777)===0600,'real CLI control state stays private');
        [$code,$out]=runCli(['--prune=wrong'],$cli);check($code===1,'unknown cleanup mode is nonzero');
        $lease=Aowow\SqlUpdate::acquire($db);
        try {[$code,$out]=runCli(['--prune=apply'],$cli);check($code===1,'cleanup cannot race an active migration lease');}
        finally {Aowow\SqlUpdate::release($db,$lease);}
        $lock=fopen($cli.'/cache/maintenance/lock','c+b');flock($lock,LOCK_EX);
        try {[$code,$out]=runCli(['--prune=apply'],$cli);check($code===1,'cleanup cannot race another filesystem cleanup');}
        finally {flock($lock,LOCK_UN);fclose($lock);}
        check((int)$db->query('SELECT COUNT(*) FROM ::comments')->fetchSingle()===500,'real CLI preserves all published comment rows');
        preg_match('/INSERT INTO `aowow_dbversion` VALUES .*?;/',file_get_contents($root.'/setup/sql/02-db_initial_data.sql'),$initial);
        $db->query('DELETE FROM ::dbversion');$db->nativeQuery($initial[0]);
        $fresh=Aowow\SqlUpdate::apply($db,$temp.'/updates');
        check((int)$fresh['date']===1791000000 && $fresh['build']==='globaljs','fresh schema marker skips duplicate-index migration and retains JS build');
        check((int)$db->query('SELECT COUNT(*) FROM ::sql_update_journal')->fetchSingle()===1,'fresh marker does not replay the new migration');
    }
    finally {cleanup($temp);}
    $db->query('DROP TABLE ::contribution_budget');
    check(!ContributionBudget::reserve('video') && ContributionBudget::error()==='intError','missing migration fails closed');
    echo "PASS: $checks contribution SQL/concurrency/pagination/retention checks\n";
}
