<?php

namespace Aowow {
    // Exercise real mail rendering without sending email or exposing credentials.
    class ActivationMailFixture {
        public static array $messages = [];
        public static bool $success = true;
    }
    function mail(string $recipient, string $subject, string $body, string $headers) : bool {
        ActivationMailFixture::$messages[] = [$recipient, $subject, $body, $headers];
        return ActivationMailFixture::$success;
    }
}

namespace {
    use Aowow\AccountActivation;
    use Aowow\ActivationMailFixture;
    use Aowow\Cfg;
    use Aowow\User;

    if (!isset($db, $seed) || !defined('AOWOW_REVISION')) die('Run tests/security-activation.php');
    require __DIR__.'/../endpoints/account/activate.php';
    require __DIR__.'/../endpoints/account/resend.php';
    require __DIR__.'/../endpoints/account/signup.php';
    require __DIR__.'/../includes/components/pagetemplate.class.php';
    $seedPending = function (int $ttl = 3600, bool $remember = true) use ($seed, $db, $token) : void {
        $seed(ACC_STATUS_NEW, $ttl);
        $db->query('UPDATE ::account SET `userGroups` = %i WHERE `id` = 7', U_GROUP_PENDING | U_GROUP_VIP);
        $db->query('INSERT INTO ::account_sessions (`userId`, `sessionId`, `created`, `expires`, `touched`, `deviceInfo`, `ip`, `status`) VALUES (7, %s, UNIX_TIMESTAMP(), %i, UNIX_TIMESTAMP(), "fixture", "127.0.0.1", %i)', $token, $remember ? 0 : time() + 3600, SESSION_ACTIVE);
        ActivationMailFixture::$messages = [];
        ActivationMailFixture::$success = true;
        Cfg::$values = [];
    };
    $ban = fn() => $db->selectRow('SELECT * FROM ::account_bannedips WHERE `ip` = %s AND `type` = %i', User::$ip, IP_BAN_TYPE_REGISTRATION_ATTEMPT);
    $call = function (string $class, string $method, array $post) : array {
        $response = (new ReflectionClass($class))->newInstanceWithoutConstructor();
        (new ReflectionProperty($response, '_post'))->setValue($response, $post);
        return [(new ReflectionMethod($response, $method))->invoke($response), $response];
    };

    foreach ([-1, 0] as $ttl) {
        $seedPending($ttl); $before = $account();
        check(AccountActivation::activate($token, User::$ip)['status'] === AccountActivation::INVALID, 'Expired and boundary-expired activation tokens fail');
        check($account() === $before && !$ban(), 'Rejected activation leaves account/groups and registration budget unchanged');
    }
    foreach ([ACC_STATUS_NONE, ACC_STATUS_RECOVER_PASS, ACC_STATUS_CHANGE_PASS, ACC_STATUS_DELETED] as $status) {
        $seedPending(); $db->query('UPDATE ::account SET `status` = %i WHERE `id` = 7', $status); $before = $account();
        check(AccountActivation::activate($token, User::$ip)['status'] === AccountActivation::INVALID, 'Only NEW accounts may consume activation authority');
        check($account() === $before && !$ban(), 'Other statuses retain groups and registration budget');
    }
    $seedPending();
    check(AccountActivation::activate(str_repeat('X', 40), User::$ip)['status'] === AccountActivation::INVALID, 'Unknown activation tokens fail');
    check(AccountActivation::activate($token, null)['status'] === AccountActivation::FAILED, 'Activation requires a validated client IP');

    foreach ([true, false] as $remember) {
        $seedPending(3600, $remember);
        [$message, $response] = $call(Aowow\AccountActivateResponse::class, 'activate', ['key' => $token]);
        $row = $account();
        check((new ReflectionProperty($response, 'success'))->getValue($response) && str_contains($message, '?account=signin&key='.$token), 'Real activation response preserves the localized signin link');
        check($row['status'] === ACC_STATUS_NONE && $row['statusTimer'] === 0 && $row['token'] === '' && $row['updateValue'] === '', 'Activation consumes authority and clears pending state');
        check($row['userGroups'] === U_GROUP_VIP, 'Activation removes only PENDING and preserves other groups');
        check((int)$db->selectCell('SELECT COUNT(*) FROM ::account_sessions WHERE `sessionId` = %s', $token) === 0 && $active(7) === 2, 'Signup metadata is deleted without logging out real browser sessions');
        check($ban()['count'] === Cfg::get('ACC_FAILED_AUTH_COUNT') + 1, 'Successful activation applies the registration block once');
        $before = [$account(), $ban()];
        check(AccountActivation::activate($token, User::$ip)['status'] === AccountActivation::INVALID && [$account(), $ban()] === $before, 'Replay cannot reset groups or renew the registration block');
        check(empty($_SESSION['user']) && User::$id === 0, 'Activation and prefill do not authenticate a browser');
        check(AccountActivation::signinPrefill(str_repeat('X', 40)) === null, 'Another key cannot select the session prefill');
        check(AccountActivation::signinPrefill($token) === ['user7', $remember], 'Signin prefill retains username and remember-me choice');
        check(AccountActivation::signinPrefill($token) === null, 'Signin prefill is one-use');
    }
    foreach ([[], ['key' => $token, 'login' => 'user7', 'rememberMe' => true, 'expires' => time()], ['key' => [], 'login' => 'user7', 'rememberMe' => true, 'expires' => time() + 300]] as $prefill) {
        $_SESSION['activationSignin'] = $prefill;
        check(AccountActivation::signinPrefill($token) === null, 'Expired and malformed prefill never becomes authentication');
    }

    foreach (['UPDATE ::account SET', 'DELETE FROM ::account_sessions', 'REPLACE INTO ::account_bannedips', 'COMMIT', 'START TRANSACTION'] as $failure) {
        $seedPending(); $before = $account(); RecoveryConnection::$fail = $failure;
        check(AccountActivation::activate($token, User::$ip)['status'] === AccountActivation::FAILED, 'Activation write failure is rejected');
        RecoveryConnection::$fail = null;
        check($account() === $before && !$ban() && $active(7) === 3, 'Activation failure rolls back authority, groups, metadata and block');
    }
    $db->query("CREATE TRIGGER fixture_activation_failure BEFORE INSERT ON aowow_account_bannedips FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'fixture budget failure'");
    try {
        foreach (['activate', 'resend'] as $operation) {
            $seedPending(); $before = $account();
            $result = $operation === 'activate' ? AccountActivation::activate($token, User::$ip) : AccountActivation::resend('user7@example.test', User::$ip);
            check($result['status'] === AccountActivation::FAILED, 'Real SQL budget failure rejects '.$operation);
            check($account() === $before && !$ban() && $active(7) === 3, 'Real SQL failure restores account authority and signup metadata');
        }
    }
    finally { $db->query('DROP TRIGGER fixture_activation_failure'); }

    $seedPending(-1, false); $oldDeadline = $account()['statusTimer'];
    [$message, $response] = $call(Aowow\AccountResendResponse::class, 'resend', ['email' => 'user7@example.test']);
    $rotated = $account()['token'];
    check((new ReflectionProperty($response, 'success'))->getValue($response) && count(ActivationMailFixture::$messages) === 1, 'Real resend response renders one intercepted activation mail');
    check($rotated !== $token && preg_match('/^[a-zA-Z0-9]{40}$/D', $rotated) === 1, 'Resend rotates authority using the compatible CSPRNG format');
    check($account()['statusTimer'] > $oldDeadline && $account()['statusTimer'] > time() + Cfg::get('ACC_CREATE_SAVE_DECAY') - 10, 'Resend renews the activation deadline');
    check(str_contains(ActivationMailFixture::$messages[0][2], $rotated) && !str_contains(ActivationMailFixture::$messages[0][2], $token), 'Mail contains only the new activation key');
    check((int)$db->selectCell('SELECT COUNT(*) FROM ::account_sessions WHERE `sessionId` = %s', $token) === 0 && (int)$db->selectCell('SELECT COUNT(*) FROM ::account_sessions WHERE `sessionId` = %s', $rotated) === 1, 'Resend moves signup remember-me metadata to the new key');
    check(AccountActivation::activate($token, User::$ip)['status'] === AccountActivation::INVALID, 'A superseded activation key is unusable');
    $activation = AccountActivation::activate($rotated, User::$ip);
    check($activation['status'] === AccountActivation::OK && $activation['rememberMe'] === false, 'The renewed link activates and retains the original remember choice');

    $seedPending(); ActivationMailFixture::$success = false;
    [$message, $response] = $call(Aowow\AccountResendResponse::class, 'resend', ['email' => 'user7@example.test']);
    check(!(new ReflectionProperty($response, 'success'))->getValue($response), 'Mail failure is not reported as successful resend');
    check($account()['token'] !== $token && $ban() && count(ActivationMailFixture::$messages) === 1, 'Failed mail does not restore old authority or bypass the committed attempt budget');
    check(AccountActivation::activate($token, User::$ip)['status'] === AccountActivation::INVALID, 'Mail failure cannot revive a superseded key');

    $seedPending();
    [$message, $response] = $call(Aowow\AccountResendResponse::class, 'resend', ['email' => 'unknown@example.test']);
    check((new ReflectionProperty($response, 'success'))->getValue($response) && ActivationMailFixture::$messages === [] && !$ban(), 'Unknown email keeps the existing non-enumerating response without mail or mutations');
    foreach (['UPDATE ::account SET', 'UPDATE ::account_sessions', 'INSERT INTO ::account_bannedips', 'COMMIT', 'START TRANSACTION'] as $failure) {
        $seedPending(-1); $before = $account(); RecoveryConnection::$fail = $failure;
        [$message, $response] = $call(Aowow\AccountResendResponse::class, 'resend', ['email' => 'user7@example.test']);
        RecoveryConnection::$fail = null;
        check(!(new ReflectionProperty($response, 'success'))->getValue($response), 'Failed rotation does not report resend success');
        check($account() === $before && !$ban() && ActivationMailFixture::$messages === [], 'Rotation failure rolls back and sends no mail');
    }

    $seedPending();
    $db->query('INSERT INTO ::account_bannedips (`ip`, `type`, `count`, `unbanDate`) VALUES (%s, %i, %i, UNIX_TIMESTAMP() + 3600)', User::$ip, IP_BAN_TYPE_REGISTRATION_ATTEMPT, Cfg::get('ACC_FAILED_AUTH_COUNT') + 1);
    $before = [$account(), $ban()];
    check(AccountActivation::resend('user7@example.test', User::$ip)['status'] === AccountActivation::BLOCKED && [$account(), $ban()] === $before, 'Rotation observes the locked IP budget');
    check(AccountActivation::resend('user7@example.test', null)['status'] === AccountActivation::FAILED, 'Rotation rejects a missing peer');

    // Exercise the real signin rendering path with session-local prefill and no authentication POST.
    class ActivationSigninFixture extends Aowow\AccountSigninResponse {
        protected function generateMetadata(bool $useArticle = true) : void {}
    }
    $seedPending(); $call(Aowow\AccountActivateResponse::class, 'activate', ['key' => $token]);
    $signin = (new ReflectionClass(ActivationSigninFixture::class))->newInstanceWithoutConstructor();
    (new ReflectionProperty($signin, '_post'))->setValue($signin, ['username' => null, 'password' => null, 'remember_me' => null]);
    (new ReflectionProperty($signin, '_get'))->setValue($signin, ['key' => $token, 'next' => null]);
    (new ReflectionMethod($signin, 'generate'))->invoke($signin);
    check($signin->inputbox[1]['username'] === 'user7' && $signin->inputbox[1]['rememberMe'] === true && empty($_SESSION['user']), 'Real signin generation uses prefill without treating the key as authentication');

    $start = function (string $operation) use ($token) : array {
        $ready = tempnam(sys_get_temp_dir(), 'aowow-activation-race-');
        $command = [PHP_BINARY];
        foreach (array_filter(explode(',', getenv('AOWOW_TEST_PHP_EXTENSIONS') ?: '')) as $library)
            array_push($command, '-d', 'extension='.$library);
        array_push($command, __DIR__.'/security-password-recovery.php', '--worker', $token, $ready, $operation);
        $process = proc_open($command, [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes, dirname(__DIR__));
        if (!is_resource($process)) throw new RuntimeException('Cannot start activation race worker');
        fclose($pipes[0]); return [$process, $pipes, $ready];
    };
    $wait = function (array $worker) : void {
        $until = microtime(true) + 5;
        while (file_get_contents($worker[2]) !== 'ready' && microtime(true) < $until) usleep(10000);
        check(file_get_contents($worker[2]) === 'ready', 'Activation race worker reached the lock');
    };
    $finish = function (array $worker) : int {
        [$process, $pipes, $ready] = $worker;
        $output = stream_get_contents($pipes[1]); fclose($pipes[1]);
        $error = stream_get_contents($pipes[2]); fclose($pipes[2]);
        $exit = proc_close($process); unlink($ready);
        check($exit === 0 && ctype_digit($output) && $error === '', 'Activation race worker succeeds without errors: '.$error);
        return (int)$output;
    };
    $lock = function () use ($db) : void { $db->query('START TRANSACTION'); $db->query('SELECT `id` FROM ::account WHERE `id` = 7 FOR UPDATE'); };

    $seedPending(); $lock();
    $one = $start('activate'); $two = $start('activate'); $wait($one); $wait($two); $db->query('COMMIT');
    $results = [$finish($one), $finish($two)]; sort($results);
    check($results === [AccountActivation::OK, AccountActivation::INVALID], 'Concurrent activation has exactly one winner');
    check($account()['userGroups'] === U_GROUP_VIP && $ban()['count'] === Cfg::get('ACC_FAILED_AUTH_COUNT') + 1, 'Activation race preserves groups and applies the budget once');

    $seedPending(); $lock(); $worker = $start('activate'); $wait($worker);
    $db->query('UPDATE ::account SET `statusTimer` = UNIX_TIMESTAMP() - 1 WHERE `id` = 7'); $db->query('COMMIT');
    check($finish($worker) === AccountActivation::INVALID && !$ban(), 'Activation rechecks expiry after waiting for the lock');

    $seedPending(); $lock(); $worker = $start('activate'); $wait($worker);
    $newToken = str_repeat('N', 40);
    $db->query('UPDATE ::account SET `token` = %s, `statusTimer` = UNIX_TIMESTAMP() + 3600 WHERE `id` = 7', $newToken); $db->query('COMMIT');
    check($finish($worker) === AccountActivation::INVALID && $account()['token'] === $newToken, 'Activation cannot consume authority replaced while waiting for the lock');

    $seedPending(); $lock(); $one = $start('resend'); $two = $start('resend'); $wait($one); $wait($two); $db->query('COMMIT');
    $results = [$finish($one), $finish($two)]; sort($results);
    check($results === [AccountActivation::OK, AccountActivation::BLOCKED], 'Concurrent resends share one committed IP budget');
    check($account()['token'] !== $token && $ban()['count'] === Cfg::get('ACC_FAILED_AUTH_COUNT') + 1, 'Concurrent resend rotates once and retains the deadline/budget');

    $seedPending(); $lock(); $worker = $start('resend'); $wait($worker);
    $db->query('UPDATE ::account SET `status` = %i, `token` = "", `statusTimer` = 0 WHERE `id` = 7', ACC_STATUS_NONE); $db->query('COMMIT');
    check($finish($worker) === AccountActivation::INVALID && $account()['token'] === '' && !$ban(), 'A resend cannot rotate authority after activation wins');

    // Simulate the stale reclamation lookup seen by signup while the real DB row has been renewed.
    class ReclamationRaceConnection extends RecoveryConnection {
        public function selectRow(mixed ...$args) : ?array {
            if (str_contains($args[0], 'AS "expired"')) return ['id' => 7, 'username' => 'user7', 'expired' => 1];
            return parent::selectRow(...$args);
        }
    }
    $seedPending();
    $guarded = new ReclamationRaceConnection($options);
    (new ReflectionProperty(Aowow\DB::class, 'interfaceCache'))->setValue(null, [DB_AOWOW => $guarded]);
    try {
        [$message, $response] = $call(Aowow\AccountSignupResponse::class, 'doSignUp', ['email' => 'user7@example.test', 'username' => 'user7', 'password' => 'valid-password-long', 'c_password' => 'valid-password-long']);
        check($message === Aowow\Lang::account('nameInUse') && $account()['token'] === $token, 'Stale signup lookup cannot delete a renewed pending account');
        $db->query('UPDATE ::account SET `status` = %i, `token` = "", `statusTimer` = 0 WHERE `id` = 7', ACC_STATUS_NONE);
        [$message, $response] = $call(Aowow\AccountSignupResponse::class, 'doSignUp', ['email' => 'user7@example.test', 'username' => 'user7', 'password' => 'valid-password-long', 'c_password' => 'valid-password-long']);
        check($message === Aowow\Lang::account('nameInUse') && $account()['status'] === ACC_STATUS_NONE, 'Stale signup lookup cannot delete an activated account');
    }
    finally { (new ReflectionProperty(Aowow\DB::class, 'interfaceCache'))->setValue(null, [DB_AOWOW => $db]); }

    echo "PASS: $checks activation SQL/mail/prefill checks\n";
}
