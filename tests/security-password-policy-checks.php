<?php

namespace Aowow {
    class PasswordMailFixture {
        public static bool $success = true;
        public static array $messages = [];
    }
    function mail(string $recipient, string $subject, string $body, string $headers) : bool {
        PasswordMailFixture::$messages[] = [$recipient, $subject, $body];
        return PasswordMailFixture::$success;
    }
}

namespace {
    use Aowow\Cfg;
    use Aowow\DB;
    use Aowow\Lang;
    use Aowow\PasswordBudget;
    use Aowow\PasswordMailFixture;
    use Aowow\PasswordRecovery;
    use Aowow\User;
    use Aowow\Util;

    if (!isset($db, $seed)) die('Run tests/security-password-policy.php');
    require __DIR__.'/../endpoints/account/signup.php';
    require __DIR__.'/../endpoints/account/update-password.php';
    require __DIR__.'/../endpoints/account/update-email.php';
    $fresh = function (int $limit = 2) use ($seed) : void {
        $seed(ACC_STATUS_NONE);
        Cfg::$values = ['ACC_FAILED_AUTH_COUNT' => $limit, 'ACC_FAILED_AUTH_BLOCK' => 60];
        Cfg::$mode = AUTH_MODE_SELF;
        User::$ip = '127.0.0.1';
        RecoveryConnection::$fail = null;
        PasswordMailFixture::$success = true;
        PasswordMailFixture::$messages = [];
    };
    $call = function (string $class, string $method, array $post) : mixed {
        $response = (new ReflectionClass($class))->newInstanceWithoutConstructor();
        (new ReflectionProperty($response, '_post'))->setValue($response, $post);
        return (new ReflectionMethod($response, $method))->invoke($response);
    };
    $cases = json_decode(file_get_contents(__DIR__.'/security-password-vectors.json'), true, flags: JSON_THROW_ON_ERROR);
    $cases[] = [str_repeat('a', 15)."\xff", false];
    $fresh();
    foreach ($cases as [$password, $valid]) {
        check((Util::validatePassword($password, $error) !== '') === $valid, 'New-password character/byte/control policy');
        if ($valid) {
            check(Util::validatePassword($password) === $password, 'Policy preserves the exact raw credential');
        }
        else {
            try { User::hashCrypt($password); check(false, 'Invalid passwords must not reach bcrypt'); }
            catch (InvalidArgumentException) { check(true, 'Core hash creation rejects invalid passwords'); }
        }
    }
    check(Util::validatePasswordInput('sixsix') === 'sixsix', 'Existing short credentials remain eligible for verification');
    check(Util::validatePasswordInput(str_repeat('a', 4096)) !== '' && Util::validatePasswordInput(str_repeat('a', 4097)) === '', 'Credential verification has a bounded input size');
    $hash = User::hashCrypt(' a long password ');
    check(password_get_info($hash)['options']['cost'] === 12 && User::verifyCrypt(' a long password ', $hash) && !User::verifyCrypt('a long password', $hash), 'New bcrypt cost and significant spaces');
    $hash72 = User::hashCrypt(str_repeat('a', 72));
    check(User::verifyCrypt(str_repeat('a', 73), $hash72), 'Bounded legacy bcrypt verification retains the historical 72-byte equivalence');
    check(!User::verifyCrypt(str_repeat('a', 4097), $hash72) && !User::verifyCrypt("abc\0def", $hash72), 'Oversized and control credentials fail before bcrypt');
    foreach ([AUTH_MODE_REALM, AUTH_MODE_EXTERNAL] as $mode) {
        Cfg::$mode = $mode;
        check(Util::validatePassword('abc') === 'abc' && Util::validatePassword(str_repeat('x', 73)) !== '', 'Provider validators keep their own length semantics');
    }
    Cfg::$mode = AUTH_MODE_SELF;

    $migration = file_get_contents(__DIR__.'/../setup/sql/updates/1790899200_01.sql');
    $db->query('DROP TABLE ::account_password_budget');
    $db->query($migration);
    $firstDefinition = $db->selectRow('SHOW CREATE TABLE ::account_password_budget')['Create Table'];
    $db->query($migration);
    check($db->selectRow('SHOW CREATE TABLE ::account_password_budget')['Create Table'] === $firstDefinition, 'Migration creates an InnoDB budget table and can be safely repeated');

    // Fixed windows, shared account identities, canonical IPv6 and fail-closed writes.
    $fresh();
    check(PasswordBudget::reserve(7) === PasswordBudget::OK && PasswordBudget::reserve(7) === PasswordBudget::OK, 'Configured work slots are admitted');
    $before = $db->selectAssoc('SELECT * FROM ::account_password_budget ORDER BY `scope`, `subject`');
    check(PasswordBudget::reserve(7) === PasswordBudget::BLOCKED, 'Third attempt is blocked');
    check($db->selectAssoc('SELECT * FROM ::account_password_budget ORDER BY `scope`, `subject`') === $before, 'Blocked attempts neither extend the window nor increment counters');
    User::$ip = '192.0.2.11';
    check(PasswordBudget::reserve(7) === PasswordBudget::BLOCKED, 'A second IP cannot bypass an account budget');
    check((int)$db->selectCell('SELECT COUNT(*) FROM ::account_password_budget WHERE `scope` = "ip"') === 1, 'Rejected reservations roll back their other bucket');
    User::$ip = '127.0.0.1';
    check(PasswordBudget::reserve(8) === PasswordBudget::BLOCKED && !$db->selectCell('SELECT 1 FROM ::account_password_budget WHERE `scope` = "account" AND `subject` = "8"'), 'A blocked peer rolls back a newly inserted account bucket');
    $db->query('UPDATE ::account_password_budget SET `expires` = UNIX_TIMESTAMP()');
    check(PasswordBudget::reserve(7) === PasswordBudget::OK, 'Expired windows renew at the strict boundary');
    $fresh(1); User::$ip = '2001:db8::10';
    check(PasswordBudget::reserve() === PasswordBudget::OK, 'IPv6 peer receives one work slot');
    User::$ip = '2001:0DB8:0:0:0:0:0:10';
    check(PasswordBudget::reserve() === PasswordBudget::BLOCKED, 'Equivalent IPv6 spellings share a budget');
    $fresh(); User::$ip = null;
    check(PasswordBudget::reserve(7) === PasswordBudget::FAILED && !$db->selectCell('SELECT COUNT(*) FROM ::account_password_budget'), 'Missing peer creates no reservation');
    foreach (['DELETE FROM ::account_password_budget', 'START TRANSACTION', 'INSERT INTO ::account_password_budget', 'UPDATE ::account_password_budget', 'COMMIT'] as $failure) {
        $fresh(); RecoveryConnection::$fail = $failure;
        check(PasswordBudget::reserve(7) === PasswordBudget::FAILED, 'Failed budget write rejects expensive work');
        RecoveryConnection::$fail = null;
        check((int)$db->selectCell('SELECT COUNT(*) FROM ::account_password_budget') === 0, 'Failed reservation leaves no partial buckets');
    }
    $fresh();
    $db->query('CREATE TRIGGER fixture_budget_failure BEFORE INSERT ON ::account_password_budget FOR EACH ROW SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "fixture budget failure"');
    try { check(PasswordBudget::reserve(7) === PasswordBudget::FAILED, 'Real SQL failure rejects work'); }
    finally { $db->query('DROP TRIGGER fixture_budget_failure'); }
    check((int)$db->selectCell('SELECT COUNT(*) FROM ::account_password_budget') === 0, 'Real SQL failure rolls back');

    // Real authentication: legacy compatibility, alias aggregation, upward-only rehash and fresh session marker.
    $fresh(2);
    $legacy = password_hash('sixsix', PASSWORD_BCRYPT, ['cost' => 4]);
    $db->query('UPDATE ::account SET `passHash` = %s WHERE `id` = 7', $legacy);
    check(User::authenticate('user7', 'sixsix') === AUTH_OK && $account()['passHash'] === $legacy, 'Existing six-character passwords can sign in without being rewritten');
    check(User::authenticate('user7@example.test', 'sixsix') === AUTH_OK, 'Email and login alias authenticate the same account');
    User::$ip = '192.0.2.12';
    check(User::authenticate('user7', 'sixsix') === AUTH_IPBANNED, 'Successful signins and aliases consume the same account budget');
    $fresh();
    check(User::authenticate('user7', 'old-password-long') === AUTH_OK, 'Valid weaker hash can sign in');
    $upgraded = $account()['passHash'];
    check($upgraded !== $oldHash && password_get_info($upgraded)['options']['cost'] === 12, 'Eligible weaker hash upgrades to cost 12');
    check($_SESSION['passwordVersion'] === hash('sha256', $upgraded), 'Signin binds its session to the upgraded hash');
    $fresh();
    $strong = password_hash('sixsix', PASSWORD_BCRYPT, ['cost' => 15]);
    $db->query('UPDATE ::account SET `passHash` = %s WHERE `id` = 7', $strong);
    check(User::authenticate('user7', 'sixsix') === AUTH_OK && $account()['passHash'] === $strong, 'Existing cost 15 hashes remain compatible and are never downgraded');
    $fresh(); RecoveryConnection::$fail = 'UPDATE ::account SET `passHash`';
    check(User::authenticate('user7', 'old-password-long') === AUTH_INTERNAL_ERR && empty($_SESSION['user']), 'Failed rehash cannot create an authenticated session');
    RecoveryConnection::$fail = null;
    class RehashRaceConnection extends RecoveryConnection {
        public function qry(mixed ...$args) : ?int {
            if (str_contains($args[0], 'AND BINARY `passHash`'))
                $this->query('UPDATE ::account SET `passHash` = %s WHERE `id` = 7', password_hash('concurrent-password', PASSWORD_BCRYPT, ['cost' => 4]));
            return parent::qry(...$args);
        }
    }
    $fresh();
    $guarded = new RehashRaceConnection($options);
    (new ReflectionProperty(DB::class, 'interfaceCache'))->setValue(null, [DB_AOWOW => $guarded]);
    try {
        check(User::authenticate('user7', 'old-password-long') === AUTH_WRONGPASS && empty($_SESSION['user']), 'Password change winning a rehash race rejects the stale signin');
        check(User::verifyCrypt('concurrent-password', $account()['passHash']), 'Rehash compare-and-swap cannot overwrite a concurrent password change');
    }
    finally { (new ReflectionProperty(DB::class, 'interfaceCache'))->setValue(null, [DB_AOWOW => $db]); }
    $fresh(); $db->query('DROP TABLE ::account_password_budget');
    try { check(User::authenticate('user7', 'old-password-long') === AUTH_INTERNAL_ERR && empty($_SESSION['user']), 'Missing deployment migration fails closed before bcrypt'); }
    finally { $db->query($migration); }
    $fresh(1);
    check(User::authenticate('unknown', 'sixsix') === AUTH_WRONGUSER && User::authenticate('user7', 'sixsix') === AUTH_IPBANNED, 'Unknown-account attempts consume the peer budget');

    $fresh();
    $db->query('INSERT INTO ::account_bannedips (`ip`, `type`, `count`, `unbanDate`) VALUES (%s, %i, 1, UNIX_TIMESTAMP() - 1), (%s, %i, 2, UNIX_TIMESTAMP() + 60)', User::$ip, IP_BAN_TYPE_LOGIN_ATTEMPT, User::$ip, IP_BAN_TYPE_REGISTRATION_ATTEMPT);
    User::init();
    check($db->selectCell('SELECT `count` FROM ::account_bannedips WHERE `ip` = %s AND `type` = %i', User::$ip, IP_BAN_TYPE_REGISTRATION_ATTEMPT) === 2, 'Expired legacy signin bans cannot erase a registration cooldown');

    // Direct handler calls cannot bypass the new-password policy or password-work reservation.
    foreach ([str_repeat('a', 14), str_repeat('a', 73), str_repeat('😀', 19)] as $password) {
        $fresh(); $before = $account();
        check($call(Aowow\AccountSignupResponse::class, 'doSignUp', ['username' => 'newuser', 'email' => 'new@example.test', 'password' => $password, 'c_password' => $password]) === Lang::account('errPassLength'), 'Signup rejects the boundary before hashing');
        User::$id = 7;
        check($call(Aowow\AccountUpdatepasswordResponse::class, 'updatePassword', ['currentPassword' => 'old-password-long', 'newPassword' => $password, 'confirmPassword' => $password]) === Lang::account('errPassLength'), 'Change rejects the boundary before hashing');
        check(PasswordRecovery::reset($token, 'user7@example.test', $password) === PasswordRecovery::INVALID_PASSWORD, 'Reset rejects the same boundary');
        check($account() === $before && !PasswordMailFixture::$messages && !$db->selectCell('SELECT COUNT(*) FROM ::account_password_budget'), 'Invalid new passwords have no hash, mail or budget side effects');
    }
    $fresh(1); PasswordBudget::reserve(7); $before = $account(); User::$id = 7;
    check($call(Aowow\AccountUpdatepasswordResponse::class, 'updatePassword', ['currentPassword' => 'old-password-long', 'newPassword' => 'another-password-long', 'confirmPassword' => 'another-password-long']) === Lang::main('intError'), 'Password-change work is blocked before verification/hash');
    check($call(Aowow\AccountUpdateemailResponse::class, 'updateMail', ['currentPassword' => 'old-password-long', 'newemail' => 'changed@example.test']) === Lang::main('intError'), 'Email reauthentication shares the work budget');
    check($account() === $before && !PasswordMailFixture::$messages, 'Throttled changes leave account and mail unchanged');
    $fresh(1); $seed(); Cfg::$values['ACC_FAILED_AUTH_COUNT'] = 1;
    PasswordBudget::reserve(7); $before = $account();
    check(PasswordRecovery::reset($token, 'user7@example.test', 'another-password-long') === PasswordRecovery::FAILED && $account() === $before, 'Reset work is blocked before verification/hash and keeps its token');
    $fresh(); User::$id = 7;
    $call(Aowow\AccountUpdatepasswordResponse::class, 'updatePassword', ['currentPassword' => 'old-password-long', 'newPassword' => ' another password ', 'confirmPassword' => ' another password ', 'globalLogout' => false]);
    check(User::verifyCrypt(' another password ', $account()['updateValue']) && !User::verifyCrypt('another password', $account()['updateValue']), 'Password change stores significant spaces without trimming');
    foreach ([Aowow\AccountSigninResponse::class => ['password'], Aowow\AccountSignupResponse::class => ['password', 'c_password'], Aowow\AccountresetpasswordResponse::class => ['password', 'c_password'], Aowow\AccountUpdatepasswordResponse::class => ['currentPassword', 'newPassword', 'confirmPassword'], Aowow\AccountUpdateemailResponse::class => ['currentPassword']] as $class => $fields) {
        $filters = (new ReflectionClass($class))->getDefaultProperties()['expectedPOST'];
        foreach ($fields as $field) {
            check($filters[$field]['options'] === [Util::class, 'validatePasswordInput'], 'Password fields preserve raw credentials');
            check(call_user_func($filters[$field]['options'], ' another password ') === ' another password ', 'Real input callback preserves significant spaces');
        }
    }
    $fresh(1); PasswordMailFixture::$success = false;
    $post = ['username' => 'newuser', 'email' => 'new@example.test', 'password' => 'new-password-long', 'c_password' => 'new-password-long', 'remember_me' => false];
    $call(Aowow\AccountSignupResponse::class, 'doSignUp', $post);
    check(count(PasswordMailFixture::$messages) === 1 && $db->selectCell('SELECT `count` FROM ::account_bannedips WHERE `type` = %i', IP_BAN_TYPE_REGISTRATION_ATTEMPT) === 1, 'Failed signup mail still consumes registration work');
    $post['username'] = 'another'; $post['email'] = 'another@example.test';
    $call(Aowow\AccountSignupResponse::class, 'doSignUp', $post);
    check(count(PasswordMailFixture::$messages) === 1 && (int)$db->selectCell('SELECT COUNT(*) FROM ::account') === 3, 'Mail failure cannot permit a second expensive signup');

    // Independent processes race to create the first account bucket from different IPs.
    $fresh(1);
    $workers = [];
    foreach (['192.0.2.21', '192.0.2.22'] as $ip) {
        $extensions = array_filter(explode(',', getenv('AOWOW_TEST_PHP_EXTENSIONS') ?: ''));
        $command = [PHP_BINARY];
        foreach ($extensions as $extension) array_push($command, '-d', 'extension='.$extension);
        array_push($command, __DIR__.'/security-password-recovery.php', '--worker', 'unused', 'unused', 'budget', $ip);
        $process = proc_open($command, [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes);
        if (!is_resource($process)) throw new RuntimeException('Cannot start budget worker');
        fclose($pipes[0]); $workers[] = [$process, $pipes];
    }
    $results = [];
    foreach ($workers as [$process, $pipes]) {
        $output = stream_get_contents($pipes[1]); fclose($pipes[1]);
        $error = stream_get_contents($pipes[2]); fclose($pipes[2]);
        check(proc_close($process) === 0 && ctype_digit($output) && $error === '', 'Budget worker completes: '.$error);
        $results[] = (int)$output;
    }
    sort($results);
    check($results === [PasswordBudget::OK, PasswordBudget::BLOCKED], 'Concurrent first reservations admit exactly one account slot');
    check((int)$db->selectCell('SELECT COUNT(*) FROM ::account_password_budget') === 2, 'Losing concurrent reservation leaves no partial IP bucket');

    echo "PASS: $checks password policy/budget SQL checks\n";
}
