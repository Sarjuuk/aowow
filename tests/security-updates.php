<?php

// Guarded real-SQL suite: use only the dedicated disposable database documented in tests/README.md.
namespace Aowow {
    class Cfg {
        public static function get(string $key) : mixed {
            return $key === 'MAINTENANCE' ? (int)DB::Aowow()->query("SELECT value FROM ::config WHERE `key` = 'maintenance'")->fetchSingle() : 1;
        }
        public static function set(string $key, mixed $value) : string {
            // Match the existing nonthrowing config writer: CLISetup must independently verify persistence.
            return DB::Aowow()->qry("UPDATE ::config SET value = %s WHERE `key` = 'maintenance'", $value) === null ? 'internal error' : '';
        }
    }
}
namespace {
    use Aowow\{DB, DibiConnection, SqlUpdate};
    if (getenv('AOWOW_TEST_DATABASE') !== 'aowow_security_test_updates') {
        fwrite(STDERR, "Use AOWOW_TEST_DATABASE=aowow_security_test_updates on a disposable fixture.\n");
        exit(1);
    }
    $root = dirname(__DIR__);
    define('AOWOW_REVISION', 66);
    define('CLI', true);
    define('CLI_HAS_E', false);
    define('OS_WIN', false);
    require $root.'/includes/defines.php';
    require $root.'/includes/locale.class.php';
    $source = getenv('AOWOW_TEST_DIBI_DIR');
    if ($source) {
        foreach (['interfaces', 'exceptions', 'dibi'] as $file) require $source.'/src/Dibi/'.$file.'.php';
        spl_autoload_register(function ($class) use ($source) {
            if (str_starts_with($class, 'Dibi\\')) require $source.'/src/'.str_replace('\\', '/', $class).'.php';
        });
    }
    else require $root.'/includes/libs/autoload.php';
    require $root.'/includes/database.php';
    require $root.'/includes/setup/cli.class.php';
    require $root.'/includes/setup/sqlupdate.class.php';
    $options = ['driver' => 'mysqli', 'host' => getenv('AOWOW_TEST_DB_HOST') ?: '127.0.0.1', 'port' => (int)(getenv('AOWOW_TEST_DB_PORT') ?: 3306),
        'username' => 'root', 'password' => '', 'database' => 'aowow_security_test_updates', 'charset' => 'utf8mb4', 'substitutes' => ['' => 'aowow_']];
    $db = new DibiConnection($options);
    $db->query("SET SESSION sql_mode = ''");
    (new ReflectionProperty(DB::class, 'interfaceCache'))->setValue(null, [DB_AOWOW => $db, DB_WORLD => $db]);
    (new ReflectionProperty(DB::class, 'interfaceTimes'))->setValue(null, [DB_AOWOW => [time()+86400, 86400], DB_WORLD => [time()+86400, 86400]]);

    if (getenv('AOWOW_UPDATE_CHILD')) {
        if (getenv('AOWOW_UPDATE_MODE') === 'init-error') Aowow\CLI::write('Fixture initialization failure', Aowow\CLI::LOG_ERROR);
        if (getenv('AOWOW_UPDATE_MODE') === 'missing-world')
            (new ReflectionProperty(DB::class, 'interfaceCache'))->setValue(null, [DB_AOWOW => $db]);
        return;
    }                // fixture kernel continues into the real setup entrypoint
    $checks = 0;
    function check(bool $ok, string $label) : void {
        global $checks;
        $checks++;
        if (!$ok) throw new RuntimeException($label);
    }
    function resetDb() : void {
        global $db, $root;
        $db->onEvent = [];
        foreach ($db->query('SHOW TABLES')->fetchPairs() as $table)
            $db->query('DROP TABLE %n', $table);
        preg_match('/CREATE TABLE `aowow_dbversion` \(.*?\) ENGINE=.*?;/s', file_get_contents($root.'/setup/sql/01-db_structure.sql'), $m);
        $db->nativeQuery($m[0]);
        $db->query('INSERT INTO ::dbversion (`date`, `part`) VALUES (0, 0)');
        $db->query('CREATE TABLE ::config (`key` varchar(64) PRIMARY KEY, value text) ENGINE=InnoDB');
        $db->query("INSERT INTO ::config VALUES ('maintenance', '0')");
        $db->query('CREATE TABLE ::fixture (id int PRIMARY KEY, value text) ENGINE=InnoDB');
    }
    function failed(callable $work, string $label) : void {
        try { $work(); } catch (Throwable $e) {
            check(!str_contains($e->getMessage(), 'SECRET_SENTINEL'), $label.' safe error');
            return;
        }
        check(false, $label.' must fail');
    }
    function marker() : array {
        global $db;
        return array_map('intval', (array)$db->query('SELECT date, part FROM ::dbversion')->fetch());
    }
    $temp = sys_get_temp_dir().'/aowow-update-'.bin2hex(random_bytes(8));
    mkdir($temp, 0700);
    function files(array $contents) : string {
        global $temp;
        $dir = $temp.'/sql-'.bin2hex(random_bytes(4));
        mkdir($dir, 0700);
        foreach ($contents as $name => $sql) file_put_contents($dir.'/'.$name, $sql);
        return $dir;
    }
    function process(array $argv, string $cwd, array $extra = []) : array {
        $env = array_merge(getenv(), $extra);
        $process = proc_open($argv, [0 => ['pipe','r'], 1 => ['pipe','w'], 2 => ['pipe','w']], $pipes, $cwd, $env, ['bypass_shell' => true]);
        fclose($pipes[0]);
        $out = stream_get_contents($pipes[1]); fclose($pipes[1]);
        $err = stream_get_contents($pipes[2]); fclose($pipes[2]);
        return [proc_close($process), $out.$err];
    }
    function cleanup(string $dir) : void {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $file) {
            if ($file->isDir() && !$file->isLink()) rmdir($file->getPathname()); else unlink($file->getPathname());
        }
        rmdir($dir);
    }
    try {
        $quoted = <<<'SQL'
-- comment;
SELECT 'a;--b', "c;d", `e;f`; # comment
SELECT 'doubled'';quote'; /* ignored; */ SELECT 'escape\';x'; SELECT 100%3
SQL;
        $split = SqlUpdate::statements($quoted);
        check(count($split) === 4 && $split[0] === "SELECT 'a;--b', \"c;d\", `e;f`" && $split[1] === "SELECT 'doubled'';quote'" && $split[3] === 'SELECT 100%3', 'quoted/comment delimiters and final statement');
        check(SqlUpdate::statements("\xEF\xBB\xBF/*!40101 SET @a = 'x;y' */; SELECT 1--2;") === ["/*!40101 SET @a = 'x;y' */", 'SELECT 1--2'], 'BOM executable comments and arithmetic minus');
        foreach (["SELECT 'bad", 'SELECT 1 /* bad', 'COMMIT;', 'DELIMITER $$', '/*!40101 SET autocommit=0 */;', 'SET @@session.sql_mode="";'] as $sql)
            failed(fn() => SqlUpdate::statements($sql), 'preflight rejection');
        foreach (glob($root.'/setup/sql/updates/*.sql') as $file)
            check(count(SqlUpdate::statements(file_get_contents($file))) > 0, 'shipped migration parses: '.basename($file));

        resetDb();
        $db->query('UPDATE ::dbversion SET date=1700000000, part=0');
        $dir = files(['1700000000_00.sql' => 'INSERT INTO aowow_fixture VALUES (99, "must skip");', '1700000000_01.sql' => "CREATE TABLE aowow_created (id int); INSERT INTO aowow_fixture VALUES (1, 'semi;100%'); UPDATE aowow_fixture SET value='unused' WHERE id=999; SELECT 'ok';", '1700000000_02.sql' => "INSERT INTO aowow_fixture VALUES (2, 'EOF')", '1700000000_03.sql.extra.sql' => 'INVALID SECRET_SENTINEL']);
        SqlUpdate::apply($db, $dir);
        check(marker() === ['date'=>1700000000,'part'=>2], 'sorted same-date parts and invalid names');
        check((int)$db->query('SELECT COUNT(*) FROM ::fixture')->fetchSingle() === 2, 'raw percent semicolon EOF and zero-affected success');
        check((int)$db->query("SELECT COUNT(*) FROM ::sql_update_journal WHERE status='applied'")->fetchSingle() === 2, 'journal completion');
        check((int)$db->query('SELECT statements FROM ::sql_update_journal WHERE part=1')->fetchSingle() === 4, 'every successful statement counted');
        check($db->query('SELECT checksum FROM ::sql_update_journal WHERE part=1')->fetchSingle() === hash_file('sha256', $dir.'/1700000000_01.sql'), 'exact file checksum');
        SqlUpdate::apply($db, $dir);
        check((int)$db->query('SELECT COUNT(*) FROM ::fixture')->fetchSingle() === 2, 'completed updates skip without replay');
        $db->query('UPDATE ::dbversion SET part=0');
        failed(fn() => SqlUpdate::apply($db, $dir), 'marker rollback cannot replay applied journal');

        resetDb();
        $dir = files(['1700000000_01.sql'=>'INSERT INTO aowow_fixture VALUES (1, "good");', '1700000000_02.sql'=>'CREATE TABLE aowow_partial (id int); INSERT INTO aowow_fixture VALUES (2, "before"); SELECT SECRET_SENTINEL FROM missing_fixture; INSERT INTO aowow_fixture VALUES (3, "after");', '1700000000_03.sql'=>'INSERT INTO aowow_fixture VALUES (4, "next");']);
        failed(fn() => SqlUpdate::apply($db, $dir), 'SQL failure');
        check(marker() === ['date'=>1700000000,'part'=>1], 'last confirmed marker preserved');
        check((int)$db->query('SELECT COUNT(*) FROM ::fixture')->fetchSingle() === 2, 'later statement and later file skipped');
        check($db->query('SHOW TABLES LIKE %s', 'aowow_partial')->fetchSingle() === 'aowow_partial', 'partial DDL persists');
        check($db->query('SELECT status FROM ::sql_update_journal WHERE part=2')->fetchSingle() === 'running', 'partial journal persists');
        check((int)$db->query('SELECT statements FROM ::sql_update_journal WHERE part=2')->fetchSingle() === 2, 'only acknowledged progress recorded');
        failed(fn() => SqlUpdate::apply($db, $dir), 'dirty retry blocked');
        check((int)$db->query('SELECT COUNT(*) FROM ::fixture')->fetchSingle() === 2, 'blocked retry does not mutate');

        foreach (['version', 'journal', 'progress', 'journal-corrupt'] as $failure) {
            resetDb();
            $db->query(SqlUpdate::JOURNAL_DDL);
            $table = $failure === 'version' ? 'aowow_dbversion' : 'aowow_sql_update_journal';
            $when = $failure === 'journal' ? "IF NEW.status = 'applied' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SECRET_SENTINEL'; END IF;" : "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SECRET_SENTINEL';";
            if ($failure === 'journal-corrupt') $when = "IF NEW.status = 'applied' THEN SET NEW.status='bogus'; END IF;";
            $db->nativeQuery('CREATE TRIGGER fixture_fail BEFORE UPDATE ON '.$table.' FOR EACH ROW BEGIN '.$when.' END');
            $dir = files(['1700000000_01.sql'=>'INSERT INTO aowow_fixture VALUES (1, "durable");']);
            failed(fn() => SqlUpdate::apply($db, $dir), $failure.' failure');
            check(marker() === ['date'=>0,'part'=>0], $failure.' marker not advanced');
            check($db->query('SELECT status FROM ::sql_update_journal')->fetchSingle() === 'running', $failure.' journal remains running');
            check((int)$db->query('SELECT COUNT(*) FROM ::fixture')->fetchSingle() === 1, $failure.' previous SQL remains durable');
        }
        resetDb();
        $db->onEvent[] = static function (Dibi\Event $event) {
            if ($event->sql === 'COMMIT') throw new RuntimeException('SECRET_SENTINEL uncertain acknowledgement');
        };
        $dir = files(['1700000000_01.sql'=>'INSERT INTO aowow_fixture VALUES (1, "durable");']);
        failed(fn() => SqlUpdate::apply($db, $dir), 'commit acknowledgement');
        $db->onEvent=[];
        check(marker() === ['date'=>1700000000,'part'=>1] && $db->query('SELECT status FROM ::sql_update_journal')->fetchSingle() === 'applied', 'uncertain commit still has atomic metadata');
        SqlUpdate::apply($db, $dir);
        check((int)$db->query('SELECT COUNT(*) FROM ::fixture')->fetchSingle() === 1, 'acknowledged metadata prevents duplicate SQL');

        foreach (['missing', 'duplicate', 'myisam', 'bad-journal', 'null-journal', 'mode', 'autocommit', 'syntax', 'empty'] as $failure) {
            resetDb();
            $sql='INSERT INTO aowow_fixture VALUES (1, "should not run");';
            if ($failure === 'missing') $db->query('DELETE FROM ::dbversion');
            if ($failure === 'duplicate') $db->query('INSERT INTO ::dbversion (`date`,`part`) VALUES (0,0)');
            if ($failure === 'myisam') $db->query('ALTER TABLE ::dbversion ENGINE=MyISAM');
            if ($failure === 'bad-journal') $db->query('CREATE TABLE ::sql_update_journal (date int) ENGINE=InnoDB');
            if ($failure === 'null-journal') { $db->query(SqlUpdate::JOURNAL_DDL); $db->query("ALTER TABLE ::sql_update_journal MODIFY status varchar(16) NULL"); $db->query("INSERT INTO ::sql_update_journal VALUES (1,1,'x',NULL,0)"); }
            if ($failure === 'mode') $db->query("SET SESSION sql_mode='NO_BACKSLASH_ESCAPES'");
            if ($failure === 'autocommit') $db->query('SET autocommit=0');
            if ($failure === 'syntax') $sql .= " SELECT 'unfinished";
            if ($failure === 'empty') $sql='-- only comment';
            $dir=files(['1700000000_01.sql'=>$sql]);
            failed(fn() => SqlUpdate::apply($db,$dir), $failure.' preflight');
            check((int)$db->query('SELECT COUNT(*) FROM ::fixture')->fetchSingle() === 0, $failure.' rejected before migration SQL');
            $db->query('ROLLBACK'); $db->query('SET autocommit=1'); $db->query("SET sql_mode=''");
        }

        resetDb();
        $other=new DibiConnection($options);
        $lease=SqlUpdate::acquire($db);
        failed(fn()=>SqlUpdate::acquire($other), 'concurrent connection');
        $db->query('CREATE TABLE ::lock_commit (id int)');
        failed(fn()=>SqlUpdate::acquire($other), 'DDL does not release lock');
        $nested=SqlUpdate::acquire($db); SqlUpdate::release($db,$nested);
        failed(fn()=>SqlUpdate::acquire($other), 'nested release preserves parent lock');
        $connectionId=$db->query('SELECT CONNECTION_ID()')->fetchSingle();
        DB::holdConnection(DB_AOWOW);
        (new ReflectionProperty(DB::class,'interfaceTimes'))->setValue(null,[DB_AOWOW=>[0,86400],DB_WORLD=>[time()+86400,86400]]);
        check(DB::Aowow()->query('SELECT CONNECTION_ID()')->fetchSingle() === $connectionId, 'long build retains lock-owning connection');
        DB::releaseConnection(DB_AOWOW);
        (new ReflectionProperty(DB::class,'interfaceTimes'))->setValue(null,[DB_AOWOW=>[time()+86400,86400],DB_WORLD=>[time()+86400,86400]]);
        SqlUpdate::release($db,$lease);
        $lease=SqlUpdate::acquire($other); SqlUpdate::release($other,$lease);
        check($db->qry('SELECT SECRET_SENTINEL FROM missing_fixture') === null, 'legacy runtime query failure semantics preserved');

        // The fixture substitutes generators only; entrypoint, CLISetup, updater and sync are the real source.
        $cli=$temp.'/checkout'; mkdir($cli,0700);
        foreach (['includes', 'setup', 'setup/tools', 'setup/tools/clisetup', 'setup/sql', 'setup/sql/updates'] as $dir) mkdir($cli.'/'.$dir,0700);
        copy($root.'/aowow',$cli.'/aowow');
        file_put_contents($cli.'/includes/kernel.php', '<?php require '.var_export(__FILE__,true).';');
        symlink($root.'/includes/setup',$cli.'/includes/setup');
        symlink($root.'/setup/setup.php',$cli.'/setup/setup.php');
        foreach (['setupScript.class.php','utilityScript.class.php','CLISetup.class.php','dbcreader.class.php'] as $file) symlink($root.'/setup/tools/'.$file,$cli.'/setup/tools/'.$file);
        foreach (['update','sync'] as $name) symlink($root.'/setup/tools/clisetup/'.$name.'.us.php',$cli.'/setup/tools/clisetup/'.$name.'.us.php');
        $fake=<<<'CODE'
<?php
namespace Aowow;
CLISetup::registerUtility(new class('GENERATOR') extends UtilityScript {
    public int $optGroup = CLISetup::OPT_GRP_UTIL;
    public const string COMMAND='GENERATOR';
    public string $command;
    public function __construct($cmd) { $this->command=$cmd; }
    public function run(array &$args) : bool {
        $cmd=$this->command;
        $key=$cmd==='sql'?'doneSql':'doneBuild';
        $todo=$cmd==='sql'?'doSql':'doBuild';
        DB::Aowow()->query('INSERT INTO ::calls VALUES (%s)', $cmd);
        $mode=getenv('AOWOW_UPDATE_MODE');
        if ($mode === 'restore-ack-fail' && $cmd === 'build') DB::Aowow()->onEvent[] = static function (\Dibi\Event $event) {
            if (str_contains($event->sql, 'UPDATE') && str_contains($event->sql, 'config') && str_contains($event->sql, "'0'"))
                throw new \RuntimeException('SECRET_SENTINEL lost unlock acknowledgement');
        };
        if ($mode===$cmd.'-throw') throw new \RuntimeException('SECRET_SENTINEL');
        if ($mode===$cmd.'-error') { CLI::write('Fixture failure', CLI::LOG_ERROR); return true; }
        if ($mode===$cmd.'-fail') return false;
        if ($mode==='ack-fail' && $cmd==='sql') DB::Aowow()->nativeQuery("CREATE TRIGGER fixture_ack BEFORE UPDATE ON aowow_dbversion FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SECRET_SENTINEL'");
        $args[$key]=$mode===$cmd.'-partial'?array_slice($args[$todo],0,1):$args[$todo];
        if ($mode==='lock-probe') {
            $second=new DibiConnection(DB::Aowow()->getConfig());
            try { SqlUpdate::acquire($second); return false; } catch (\Throwable) { }
        }
        return true;
    }
    public function test(?array &$error = []) : bool { return getenv('AOWOW_UPDATE_MODE') !== $this->command.'-test-fail'; }
});
CODE;
        file_put_contents($cli.'/setup/tools/clisetup/fixture-sql.us.php', str_replace('GENERATOR', 'sql', $fake));
        file_put_contents($cli.'/setup/tools/clisetup/fixture-build.us.php', str_replace('GENERATOR', 'build', $fake));
        $php=[PHP_BINARY,'-d','extension='.getenv('AOWOW_TEST_MYSQLI_EXTENSION'),$cli.'/aowow'];
        if (!getenv('AOWOW_TEST_MYSQLI_EXTENSION')) $php=[PHP_BINARY,$cli.'/aowow'];
        foreach (['success','lock-probe','sql-fail','build-fail','sql-throw','sql-error','sql-partial','sql-test-fail','ack-fail','missing-world'] as $mode) {
            resetDb();
            $db->query('CREATE TABLE ::calls (name varchar(16)) ENGINE=InnoDB');
            file_put_contents($cli.'/setup/sql/updates/1700000000_01.sql', "INSERT INTO aowow_fixture VALUES (1, 'once'); UPDATE aowow_dbversion SET `sql`='one two', build='three';");
            [$code,$output]=process([...$php,'--update'],$cli,['AOWOW_UPDATE_CHILD'=>'1','AOWOW_UPDATE_MODE'=>$mode]);
            $ok=in_array($mode,['success','lock-probe']);
            check($code===($ok?0:1), $mode.' real CLI status: '.$output);
            check((int)$db->query("SELECT value FROM ::config WHERE `key`='maintenance'")->fetchSingle()===($ok?0:1), $mode.' maintenance outcome');
            check(marker()===['date'=>1700000000,'part'=>1],$mode.' migration completes independently of generation');
            check(!str_contains($output,'SECRET_SENTINEL'), $mode.' redacted exception');
            check((string)$db->query('SELECT `sql` FROM ::dbversion')->fetchSingle()===($ok || $mode==='build-fail'?'':'one two'), $mode.' pending SQL outcome');
            check((string)$db->query('SELECT build FROM ::dbversion')->fetchSingle()===($ok?'':'three'), $mode.' pending build outcome');
            check((int)$db->query("SELECT COUNT(*) FROM ::calls WHERE name='build'")->fetchSingle()===($ok || $mode==='build-fail'?1:0), $mode.' build skipped after SQL failure');
            if (!$ok) {
                if ($mode === 'ack-fail') $db->query('DROP TRIGGER fixture_ack');
                [$code,$output]=process([...$php,'--update'],$cli,['AOWOW_UPDATE_CHILD'=>'1','AOWOW_UPDATE_MODE'=>'success']);
                check($code===0 && (int)$db->query('SELECT COUNT(*) FROM ::fixture')->fetchSingle()===1, 'retry completes pending work without replaying migration');
                check((int)$db->query("SELECT value FROM ::config WHERE `key`='maintenance'")->fetchSingle()===1,'retry preserves already-restricted maintenance');
            }
        }
        resetDb(); $db->query('CREATE TABLE ::calls (name varchar(16))');
        [$code,$output]=process([...$php,'--update'],$cli,['AOWOW_UPDATE_CHILD'=>'1','AOWOW_UPDATE_MODE'=>'restore-ack-fail']);
        check($code===1 && !str_contains($output,'SECRET_SENTINEL'), 'lost unlock acknowledgement is safe and nonzero');
        check((int)$db->query("SELECT value FROM ::config WHERE `key`='maintenance'")->fetchSingle()===1, 'uncertain unlock re-enables maintenance');
        resetDb(); $db->query('CREATE TABLE ::calls (name varchar(16))');
        [$code,$output]=process([...$php,'--update'],$cli,['AOWOW_UPDATE_CHILD'=>'1','AOWOW_UPDATE_MODE'=>'init-error']);
        check($code===1 && marker()===['date'=>0,'part'=>0], 'initialization error prevents command mutations');
        resetDb(); $db->query('CREATE TABLE ::calls (name varchar(16))');
        file_put_contents($cli.'/setup/sql/updates/1700000000_01.sql', "CREATE TABLE aowow_partial (id int); SELECT SECRET_SENTINEL FROM missing_fixture; INSERT INTO aowow_fixture VALUES (1,'later');");
        [$code,$output]=process([...$php,'--update'],$cli,['AOWOW_UPDATE_CHILD'=>'1']);
        check($code===1 && !str_contains($output,'SECRET_SENTINEL') && str_contains($output,'1700000000_01.sql statement 2'),'real CLI failed statement metadata');
        check(marker()===['date'=>0,'part'=>0] && (int)$db->query('SELECT COUNT(*) FROM ::calls')->fetchSingle()===0,'real CLI failed migration skips sync');
        [$code,$output]=process([...$php,'--update'],$cli,['AOWOW_UPDATE_CHILD'=>'1']);
        check($code===1 && marker()===['date'=>0,'part'=>0], 'real CLI refuses partial DDL retry');
        [$code,$output]=process([...$php,'--update','--help'],$cli,['AOWOW_UPDATE_CHILD'=>'1']);
        check($code===0 && str_contains($output,'usage:'),'help succeeds without applying updates');
        $lease=SqlUpdate::acquire($db);
        [$code,$output]=process([...$php,'--update'],$cli,['AOWOW_UPDATE_CHILD'=>'1']);
        check($code===1,'concurrent real CLI fails');
        check((int)$db->query("SELECT value FROM ::config WHERE `key`='maintenance'")->fetchSingle()===1,'losing concurrent command preserves maintenance');
        SqlUpdate::release($db,$lease);
        resetDb(); $db->query('CREATE TABLE ::calls (name varchar(16))');
        $db->query("UPDATE ::dbversion SET `sql`='one two unrelated', build='three'");
        // Feed named requests through the actual sync utility without replacing the dispatcher.
        require $root.'/setup/tools/utilityScript.class.php';
        require $root.'/setup/tools/CLISetup.class.php';
        require $root.'/setup/tools/clisetup/sync.us.php';
        require $cli.'/setup/tools/clisetup/fixture-sql.us.php';
        require $cli.'/setup/tools/clisetup/fixture-build.us.php';
        $io=['doSql'=>['one'], 'doBuild'=>[]];
        check(Aowow\CLISetup::run('sync',$io), 'manual subset sync succeeds');
        check($db->query('SELECT `sql` FROM ::dbversion')->fetchSingle()==='two unrelated', 'manual sync preserves unrelated pending work');
        check($db->query('SELECT build FROM ::dbversion')->fetchSingle()==='three', 'manual sync preserves unrequested builds');
        $db->nativeQuery("CREATE TRIGGER fixture_mode BEFORE UPDATE ON aowow_config FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SECRET_SENTINEL'");
        [$code,$output]=process([...$php,'--update'],$cli,['AOWOW_UPDATE_CHILD'=>'1']);
        check($code===1 && !str_contains($output,'SECRET_SENTINEL'), 'maintenance persistence failure is safe and nonzero');
        check((int)$db->query('SELECT COUNT(*) FROM ::fixture')->fetchSingle()===0, 'maintenance failure prevents migrations');
        $db->query('DROP TRIGGER fixture_mode');
        resetDb(); $db->query('CREATE TABLE ::calls (name varchar(16))');
        file_put_contents($cli.'/setup/sql/updates/1700000000_01.sql', 'CREATE TABLE aowow_interrupted (id int); SELECT SLEEP(10); INSERT INTO aowow_fixture VALUES (1, "later");');
        $env=array_merge(getenv(),['AOWOW_UPDATE_CHILD'=>'1']);
        $worker=proc_open([...$php,'--update'],[0=>['pipe','r'],1=>['file',$temp.'/crash.log','w'],2=>['file',$temp.'/crash.log','a']],$pipes,$cli,$env,['bypass_shell'=>true]);
        fclose($pipes[0]);
        try {
            $ready=false;
            $deadline=microtime(true)+5;
            do {
                if ($db->query("SHOW TABLES LIKE 'aowow_sql_update_journal'")->fetchSingle())
                    $ready=(int)$db->query('SELECT statements FROM ::sql_update_journal')->fetchSingle()===1;
                if (!$ready) usleep(20000);
            } while (!$ready && microtime(true)<$deadline);
            check($ready,'independent worker reached durable DDL checkpoint');
        }
        finally { proc_terminate($worker,9); proc_close($worker); }
        // The terminated client's server statement may still be sleeping; its named lease remains held until disconnect.
        $deadline=microtime(true)+12;
        do {
            $free=(int)$db->query('SELECT IS_FREE_LOCK(%s)', 'aowow.update.'.substr(hash('sha256','aowow_security_test_updates|aowow_'),0,48))->fetchSingle()===1;
            if (!$free) usleep(20000);
        } while (!$free && microtime(true)<$deadline);
        check($free,'server releases dead worker lease');
        check($db->query('SELECT status FROM ::sql_update_journal')->fetchSingle()==='running' && marker()===['date'=>0,'part'=>0],'hard interruption preserves journal and version');
        [$code,$output]=process([...$php,'--update'],$cli,['AOWOW_UPDATE_CHILD'=>'1']);
        check($code===1 && (int)$db->query('SELECT COUNT(*) FROM ::fixture')->fetchSingle()===0,'hard-interrupted migration cannot replay');
        [$code,$output]=process($php,$temp,['AOWOW_UPDATE_CHILD'=>'1']);
        check($code===1,'wrong checkout directory is nonzero');
        file_put_contents($temp.'/missing-extension.php', '<?php namespace Aowow; function extension_loaded(string $name) : bool { return false; }');
        [$code,$output]=process([PHP_BINARY,'-d','auto_prepend_file='.$temp.'/missing-extension.php',$root.'/aowow','--update'],$root);
        check($code!==0,'missing runtime extensions are nonzero');
        echo "\n$checks update checks passed.\n";
    }
    finally { cleanup($temp); }
}
