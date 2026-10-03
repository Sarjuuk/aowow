<?php

// Real SQL regression suite. It intentionally rebuilds only the dedicated disposable fixture database.
namespace Aowow {
    class Cfg {
        public static int $mode = 0;
        public static array $values = [];
        public static function applyToString(string $value, bool $numeric = false) : string { return $value; }
        public static function get(string $key) : mixed {
            if (array_key_exists($key, self::$values)) return self::$values[$key];
            return match ($key) {
                'ACC_AUTH_MODE' => self::$mode,
                'LOCALES' => 1,
                'ACC_FAILED_AUTH_COUNT' => 100,
                'ACC_FAILED_AUTH_BLOCK', 'SESSION_TIMEOUT_DELAY' => 3600,
                'ACC_CREATE_SAVE_DECAY' => 3600,
                'NAME_SHORT', 'NAME' => 'Fixture',
                'HOST_URL', 'STATIC_URL' => 'https://aowow.example',
                'CONTACT_EMAIL' => 'fixture@example.test',
                default => 0
            };
        }
    }
}

namespace {
    use Aowow\Cfg;
    use Aowow\DB;
    use Aowow\DibiConnection;
    use Aowow\PasswordRecovery;
    use Aowow\User;
    use Aowow\Util;

    if (getenv('AOWOW_TEST_DATABASE') !== 'aowow_security_test_passwords') {
        fwrite(STDERR, "Set AOWOW_TEST_DATABASE=aowow_security_test_passwords on a disposable MySQL fixture; see tests/README.md.\n");
        exit(1);
    }

    define('AOWOW_REVISION', 58);
    define('CLI', true);
    require __DIR__.'/../includes/defines.php';
    require __DIR__.'/../includes/locale.class.php';
    require __DIR__.'/../localization/lang.class.php';
    require __DIR__.'/../localization/datetime.class.php';
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

    require __DIR__.'/../includes/database.php';
    require __DIR__.'/../includes/utilities.php';
    require __DIR__.'/../includes/user.class.php';
    require __DIR__.'/../includes/game/uitext.class.php';
    require __DIR__.'/../includes/components/csrf.class.php';
    require __DIR__.'/../includes/components/passwordbudget.class.php';
    require __DIR__.'/../includes/components/passwordrecovery.class.php';
    require __DIR__.'/../includes/components/accountactivation.class.php';
    require __DIR__.'/../includes/components/response/baseresponse.class.php';
    require __DIR__.'/../includes/components/response/textresponse.class.php';
    require __DIR__.'/../includes/components/response/templateresponse.class.php';
    require __DIR__.'/../includes/components/guidemgr.class.php';
    foreach (['SmartAI', 'SmartEvent', 'SmartAction', 'SmartTarget'] as $class)
        require __DIR__.'/../includes/components/SmartAI/'.$class.'.class.php';
    require __DIR__.'/../endpoints/account/reset-password.php';
    require __DIR__.'/../endpoints/account/confirm-password.php';
    require __DIR__.'/../endpoints/account/signin.php';
    Cfg::$mode = AUTH_MODE_SELF;
    Aowow\Lang::load(Aowow\Locale::EN);
    User::$preferedLoc = Aowow\Locale::EN;
    User::$ip = '127.0.0.1';
    User::$agent = 'fixture';
    putenv('REMOTE_ADDR=127.0.0.1');
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    session_start();

    class RecoveryConnection extends DibiConnection {
        public static ?string $ready = null;
        public static ?string $fail = null;
        public function selectRow(mixed ...$args) : ?array {
            if (self::$ready !== null && str_contains($args[0], 'FOR UPDATE')) file_put_contents(self::$ready, 'ready');
            return parent::selectRow(...$args);
        }
        public function qry(mixed ...$args) : ?int {
            if (self::$fail !== null && str_contains($args[0], self::$fail)) return null;
            return parent::qry(...$args);
        }
    }
    $options = [
        'driver' => 'mysqli', 'host' => getenv('AOWOW_TEST_DB_HOST') ?: '127.0.0.1', 'port' => (int)(getenv('AOWOW_TEST_DB_PORT') ?: 3306),
        'username' => getenv('AOWOW_TEST_DB_USER') ?: 'root', 'password' => getenv('AOWOW_TEST_DB_PASSWORD') ?: '',
        'database' => 'aowow_security_test_passwords', 'charset' => 'utf8mb4', 'substitutes' => ['' => 'aowow_']
    ];
    $db = new RecoveryConnection($options);
    $db->onEvent[] = function (Dibi\Event $event) : void {
        if ($event->result instanceof \Exception)
            throw new \Error('Unexpected fixture SQL failure: '.$event->result->getMessage());
    };
    $db->query("SET SESSION sql_mode = ''");
    (new ReflectionProperty(DB::class, 'interfaceCache'))->setValue(null, [DB_AOWOW => $db]);
    (new ReflectionProperty(DB::class, 'interfaceTimes'))->setValue(null, [DB_AOWOW => [time() + 3600, 3600]]);

    if (($argv[1] ?? '') === '--worker') {
        RecoveryConnection::$ready = $argv[3];
        if (($argv[4] ?? '') === 'budget') {
            User::$ip = $argv[5] ?? '127.0.0.1';
            Cfg::$values['ACC_FAILED_AUTH_COUNT'] = 1;
            echo Aowow\PasswordBudget::reserve(7);
            exit;
        }
        if (($argv[4] ?? '') === 'activate' || ($argv[4] ?? '') === 'resend') {
            $result = $argv[4] === 'activate' ? Aowow\AccountActivation::activate($argv[2], User::$ip) : Aowow\AccountActivation::resend('user7@example.test', User::$ip);
            echo $result['status'];
            exit;
        }
        echo ($argv[4] ?? '') === 'reset' ? PasswordRecovery::reset($argv[2], 'user7@example.test', 'raced-password-long') : PasswordRecovery::confirm($argv[2]);
        exit;
    }

    $checks = 0;
    function check(bool $condition, string $message) : void {
        global $checks;
        ++$checks;
        if (!$condition) throw new RuntimeException($message);
    }
    // Use the shipped InnoDB schema, not a hand-written approximation of account/session columns.
    $structure = file_get_contents(__DIR__.'/../setup/sql/01-db_structure.sql');
    foreach (['account_password_budget', 'account_sessions', 'account_banned', 'account_reputation', 'account_bannedips', 'account'] as $table)
        $db->query('DROP TABLE IF EXISTS [aowow_'.$table.']');
    foreach (['account', 'account_sessions', 'account_banned', 'account_reputation', 'account_bannedips', 'account_password_budget'] as $table) {
        preg_match('/CREATE TABLE `aowow_'.preg_quote($table, '/').'` \(.*?\) ENGINE=InnoDB[^;]*;/s', $structure, $m);
        $db->query($m[0]);
    }
    $oldHash = password_hash('old-password-long', PASSWORD_BCRYPT, ['cost' => 4]);
    $pendingHash = password_hash('confirmed-password', PASSWORD_BCRYPT, ['cost' => 4]);
    $token = str_repeat('K', 40);
    $seed = function (int $status = ACC_STATUS_RECOVER_PASS, int $ttl = 3600) use ($db, $oldHash, $pendingHash, $token) : void {
        $db->query('DELETE FROM ::account_password_budget');
        $db->query('DELETE FROM ::account_sessions');
        $db->query('DELETE FROM ::account_bannedips');
        $db->query('DELETE FROM ::account');
        foreach ([7, 8] as $id)
            $db->query('INSERT INTO ::account (`id`, `login`, `username`, `email`, `passHash`, `joinDate`, `curLogin`, `userGroups`, `status`, `statusTimer`, `token`, `updateValue`) VALUES (%i, %s, %s, %s, %s, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), %i, %i, UNIX_TIMESTAMP() + %i, %s, %s)',
                $id, 'user'.$id, 'user'.$id, 'user'.$id.'@example.test', $oldHash, U_GROUP_NONE, $status, $ttl, $id === 7 ? $token : str_repeat('U', 40), $pendingHash);
        foreach ([session_id() => 7, 'other-browser' => 7, 'unrelated-browser' => 8] as $session => $id)
            $db->query('INSERT INTO ::account_sessions (`userId`, `sessionId`, `created`, `expires`, `touched`, `deviceInfo`, `ip`, `status`) VALUES (%i, %s, UNIX_TIMESTAMP(), 0, UNIX_TIMESTAMP(), "fixture", "127.0.0.1", %i)', $id, $session, SESSION_ACTIVE);
        User::$id = 0;
        User::$groups = 0;
        $_SESSION = [];
    };
    $account = fn() => $db->selectRow('SELECT * FROM ::account WHERE `id` = 7');
    $active = fn(int $id) => (int)$db->selectCell('SELECT COUNT(*) FROM ::account_sessions WHERE `userId` = %i AND `status` = %i', $id, SESSION_ACTIVE);
    $clean = function () use ($account, $active) : void {
        $row = $account();
        check($row['status'] === ACC_STATUS_NONE && $row['statusTimer'] === 0 && $row['token'] === '' && $row['updateValue'] === '', 'Password completion clears all pending fields');
        check($active(7) === 0 && $active(8) === 1, 'All target sessions are revoked, unrelated sessions remain active');
    };
    $signin = function (string $password) : string {
        if (User::authenticate('user7', $password) !== AUTH_OK) return 'authentication rejected';
        $response = (new ReflectionClass(Aowow\AccountSigninResponse::class))->newInstanceWithoutConstructor();
        (new ReflectionProperty($response, '_post'))->setValue($response, ['remember_me' => true]);
        (new ReflectionProperty($response, '_get'))->setValue($response, ['key' => null]);
        return (new ReflectionMethod($response, 'onAuthSuccess'))->invoke($response);
    };

    if (in_array('--policy', $argv, true)) {
        require __DIR__.'/security-password-policy-checks.php';
        exit;
    }

    if (in_array('--activation', $argv, true)) {
        require __DIR__.'/security-activation-checks.php';
        exit;
    }

    foreach (['', 'short', "abc\tdef", "abc\ndef", "abc\0def"] as $password) {
        $seed(); $before = $account();
        check(PasswordRecovery::reset($token, 'user7@example.test', $password) === PasswordRecovery::INVALID_PASSWORD, 'Shared server password policy rejects invalid input');
        check($account() === $before && $active(7) === 2, 'Invalid input leaves password, token and sessions unchanged');
    }
    $response = (new ReflectionClass(Aowow\AccountresetpasswordResponse::class))->newInstanceWithoutConstructor();
    (new ReflectionProperty($response, '_post'))->setValue($response, ['key' => $token, 'email' => 'user7@example.test', 'password' => 'short', 'c_password' => 'short']);
    check((new ReflectionMethod($response, 'doResetPass'))->invoke($response) === Aowow\Lang::account('errPassLength'), 'Real reset handler rejects a short matching password with the existing localized error');
    $seed();
    check(PasswordRecovery::reset($token, 'user7@example.test', 'old-password-long') === PasswordRecovery::SAME_PASSWORD, 'Reset still rejects the existing password');
    check(PasswordRecovery::reset($token, 'wrong@example.test', 'valid-password-long') === PasswordRecovery::INVALID_TOKEN, 'Reset binds the token to its email');
    check(PasswordRecovery::confirm($token) === PasswordRecovery::INVALID_TOKEN, 'Reset token cannot confirm a password change');
    foreach ([-1, 0] as $ttl) {
        $seed(ACC_STATUS_RECOVER_PASS, $ttl);
        check(PasswordRecovery::reset($token, 'user7@example.test', 'valid-password-long') === PasswordRecovery::INVALID_TOKEN, 'Reset rejects expired and boundary-expired tokens');
        $seed(ACC_STATUS_CHANGE_PASS, $ttl);
        check(PasswordRecovery::confirm($token) === PasswordRecovery::INVALID_TOKEN, 'Confirmation rejects expired and boundary-expired tokens');
    }

    $seed(); User::$id = 7; $_SESSION['user'] = 7;
    $beforeSession = session_id();
    check(PasswordRecovery::reset($token, 'user7@example.test', 'new-password-long') === PasswordRecovery::OK, 'Valid reset succeeds');
    $clean();
    check(User::$id === 0 && empty($_SESSION['user']) && session_id() !== $beforeSession, 'Reset signs out and rotates the current authenticated browser');
    check(password_verify('new-password-long', $account()['passHash']) && !password_verify('old-password-long', $account()['passHash']), 'Only the new password works');
    check(PasswordRecovery::reset($token, 'user7@example.test', 'another-password') === PasswordRecovery::INVALID_TOKEN, 'Consumed reset token cannot replay');
    foreach ([$beforeSession, 'other-browser'] as $browser) {
        session_write_close(); session_id($browser); session_start();
        $_SESSION = ['user' => 7, 'passwordVersion' => hash('sha256', $oldHash)];
        check(!User::init() && empty($_SESSION['user']), 'Both previous browser sessions fail real restoration');
    }
    check($signin('old-password-long') !== '', 'Old password cannot sign in');
    $signinMessage = $signin('new-password-long');
    check($signinMessage === '' && User::$id === 7, 'New password signs in through the real signin response: '.$signinMessage);
    check(($_SESSION['passwordVersion'] ?? '') === hash('sha256', $account()['passHash']), 'Signin binds the verified password version');
    unset($_SESSION['passwordVersion']); User::$id = 0;
    check(!User::init() && empty($_SESSION['user']), 'Sessions issued before password binding fail closed');

    foreach ([AUTH_MODE_REALM, AUTH_MODE_EXTERNAL] as $mode) {
        $seed(); Cfg::$mode = $mode; $_SESSION['user'] = 7;
        check(User::init() && User::$id === 7, 'Provider sessions retain their existing restoration semantics');
        Cfg::$mode = AUTH_MODE_SELF;
    }

    foreach (['UPDATE ::account SET', 'UPDATE ::account_sessions', 'COMMIT', 'START TRANSACTION'] as $failure) {
        $seed(ACC_STATUS_CHANGE_PASS); $before = $account();
        RecoveryConnection::$fail = $failure;
        check(PasswordRecovery::confirm($token) === PasswordRecovery::FAILED, 'Injected transaction failure rejects completion: '.$failure);
        RecoveryConnection::$fail = null;
        check($account() === $before && $active(7) === 2, 'Failed completion rolls back both password and sessions');
    }
    $seed(ACC_STATUS_CHANGE_PASS); $before = $account();
    $db->query("CREATE TRIGGER fixture_revoke_failure BEFORE UPDATE ON aowow_account_sessions FOR EACH ROW BEGIN IF NEW.sessionId = 'other-browser' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'fixture revocation failure'; END IF; END");
    try {
        check(PasswordRecovery::confirm($token) === PasswordRecovery::FAILED, 'Real database revocation failure rejects completion');
        check($account() === $before && $active(7) === 2, 'Real SQL failure rolls back the new hash, pending fields and any earlier session updates');
    }
    finally { $db->query('DROP TRIGGER fixture_revoke_failure'); }
    $seed(ACC_STATUS_CHANGE_PASS);
    $db->query('UPDATE ::account SET `updateValue` = "" WHERE `id` = 7');
    check(PasswordRecovery::confirm($token) === PasswordRecovery::INVALID_TOKEN, 'Malformed pending hash cannot replace the password');
    $seed(ACC_STATUS_CHANGE_PASS); User::$id = 8; $_SESSION['user'] = 8;
    check(PasswordRecovery::confirm($token) === PasswordRecovery::OK, 'Valid email confirmation succeeds');
    $clean();
    check(User::$id === 8 && $_SESSION['user'] === 8, 'Confirming another account does not destroy the caller account');
    check(password_verify('confirmed-password', $account()['passHash']), 'Confirmation uses the stored bcrypt hash');
    check(PasswordRecovery::confirm($token) === PasswordRecovery::INVALID_TOKEN, 'Confirmation token cannot replay');
    $seed(ACC_STATUS_CHANGE_PASS); User::$id = 7; $_SESSION['user'] = 7;
    $beforeSession = session_id();
    $response = (new ReflectionClass(Aowow\AccountConfirmpasswordResponse::class))->newInstanceWithoutConstructor();
    (new ReflectionProperty($response, '_post'))->setValue($response, ['key' => $token]);
    check((new ReflectionMethod($response, 'confirm'))->invoke($response) === Aowow\Lang::account('inputbox', 'message', 'passChangeOk'), 'Real confirmation handler completes the current account change');
    check(User::$id === 0 && empty($_SESSION['user']) && session_id() !== $beforeSession, 'Confirmation always signs out the current account and rotates its session');

    $seed(ACC_STATUS_CHANGE_PASS);
    $db->query('UPDATE ::account_sessions SET `status` = %i WHERE `userId` = 7', SESSION_LOGOUT);
    check(PasswordRecovery::confirm($token) === PasswordRecovery::OK, 'Completion succeeds when there are no active target sessions');

    // Child connections expose the point immediately before FOR UPDATE, allowing deterministic lock races.
    $start = function (string $operation = 'confirm') use ($token) : array {
        $ready = tempnam(sys_get_temp_dir(), 'aowow-recovery-race-');
        $command = [PHP_BINARY];
        foreach (array_filter(explode(',', getenv('AOWOW_TEST_PHP_EXTENSIONS') ?: '')) as $library)
            array_push($command, '-d', 'extension='.$library);
        array_push($command, __FILE__, '--worker', $token, $ready, $operation);
        $process = proc_open($command, [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes, dirname(__DIR__));
        if (!is_resource($process)) throw new RuntimeException('Cannot start race worker');
        fclose($pipes[0]);
        return [$process, $pipes, $ready];
    };
    $wait = function (array $worker) : void {
        $until = microtime(true) + 5;
        while (file_get_contents($worker[2]) !== 'ready' && microtime(true) < $until) usleep(10000);
        check(file_get_contents($worker[2]) === 'ready', 'Race worker reached the account lock');
    };
    $finish = function (array $worker) : int {
        [$process, $pipes, $ready] = $worker;
        $output = stream_get_contents($pipes[1]); fclose($pipes[1]);
        $error = stream_get_contents($pipes[2]); fclose($pipes[2]);
        $exit = proc_close($process); unlink($ready);
        check($exit === 0 && ctype_digit($output) && $error === '', 'Race worker completes without errors: '.$error);
        return (int)$output;
    };

    $seed(ACC_STATUS_CHANGE_PASS);
    $db->query('START TRANSACTION');
    $db->query('SELECT `id` FROM ::account WHERE `id` = 7 FOR UPDATE');
    $one = $start(); $two = $start(); $wait($one); $wait($two);
    $db->query('COMMIT');
    $results = [$finish($one), $finish($two)]; sort($results);
    check($results === [PasswordRecovery::OK, PasswordRecovery::INVALID_TOKEN], 'Two simultaneous consumers produce exactly one success');
    $clean();

    $seed();
    $db->query('START TRANSACTION'); $db->query('SELECT `id` FROM ::account WHERE `id` = 7 FOR UPDATE');
    $one = $start('reset'); $two = $start('reset'); $wait($one); $wait($two);
    $db->query('COMMIT');
    $results = [$finish($one), $finish($two)]; sort($results);
    check($results === [PasswordRecovery::OK, PasswordRecovery::INVALID_TOKEN], 'Two simultaneous resets produce exactly one success');
    $clean();

    $seed(ACC_STATUS_CHANGE_PASS);
    $db->query('START TRANSACTION'); $db->query('SELECT `id` FROM ::account WHERE `id` = 7 FOR UPDATE');
    $worker = $start(); $wait($worker);
    $db->query('UPDATE ::account SET `statusTimer` = UNIX_TIMESTAMP() - 1 WHERE `id` = 7'); $db->query('COMMIT');
    check($finish($worker) === PasswordRecovery::INVALID_TOKEN && $active(7) === 2 && $account()['passHash'] === $oldHash, 'Expiry is checked after waiting for the lock');

    $seed(ACC_STATUS_CHANGE_PASS);
    check(User::authenticate('user7', 'old-password-long') === AUTH_OK, 'Signin verifies the old hash before the recovery race');
    $worker = $start(); $wait($worker); check($finish($worker) === PasswordRecovery::OK, 'Concurrent confirmation replaces the password');
    $response = (new ReflectionClass(Aowow\AccountSigninResponse::class))->newInstanceWithoutConstructor();
    (new ReflectionProperty($response, '_post'))->setValue($response, ['remember_me' => true]);
    (new ReflectionProperty($response, '_get'))->setValue($response, ['key' => null]);
    check((new ReflectionMethod($response, 'onAuthSuccess'))->invoke($response) !== '', 'Signin crossing a password change is rejected');
    check(User::$id === 0 && empty($_SESSION['user']) && $active(7) === 0, 'A late signin cannot mint an authorized session using the old password');

    echo "PASS: $checks password recovery SQL/session checks\n";
}
