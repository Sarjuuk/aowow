<?php

// Real User::init/authenticate and loopback HTTP requests; all database reads/writes are fixtures.
namespace Aowow {
    class Cfg {
        public static function get(string $key) : mixed {
            return match ($key) {
                'ACC_AUTH_MODE' => AUTH_MODE_SELF,
                'LOCALES' => 1,
                'ACC_FAILED_AUTH_COUNT' => 5,
                'ACC_FAILED_AUTH_BLOCK' => 60,
                default => 0
            };
        }
    }
    class DB {
        public static array $queries = [];
        public static function Aowow() : self { return new self; }
        public function selectRow(string $query, mixed ...$args) : array { self::$queries[] = [$query, $args]; return []; }
        public function qry(string $query, mixed ...$args) : int { self::$queries[] = [$query, $args]; return 1; }
    }
}

namespace {
    use Aowow\DB;
    use Aowow\User;
    define('AOWOW_REVISION', 59);
    require __DIR__.'/../includes/defines.php';
    require __DIR__.'/../includes/locale.class.php';
    require __DIR__.'/../includes/utilities.php';
    require __DIR__.'/../includes/user.class.php';
    require __DIR__.'/../includes/components/passwordbudget.class.php';

    $headers = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED'];
    $probe = function () : array {
        DB::$queries = [];
        $_SESSION = ['locale' => Aowow\Locale::EN, 'dataKey' => 'fixture'];
        User::init();
        $result = User::authenticate('fixture-account', 'fixture-password');
        return ['ip' => User::$ip, 'auth' => $result, 'queries' => DB::$queries];
    };
    if (PHP_SAPI === 'cli-server') {
        // A process environment value must not replace the connection metadata supplied by the SAPI.
        putenv('REMOTE_ADDR=198.51.100.99');
        header('Content-Type: application/json');
        echo json_encode($probe(), JSON_THROW_ON_ERROR);
        exit;
    }

    $checks = 0;
    function check(bool $condition, string $message) : void {
        global $checks;
        ++$checks;
        if (!$condition) throw new RuntimeException($message);
    }
    $assertPeer = function (array $result, ?string $peer) : void {
        check($result['ip'] === $peer, 'Only a validated SAPI peer controls the client IP');
        check($result['auth'] === ($peer === null ? AUTH_INTERNAL_ERR : AUTH_WRONGUSER), 'Missing/invalid peers cannot authenticate through a header fallback');
        if ($peer === null) check($result['queries'] === [], 'Invalid peers cannot create an abuse-limit key');
        else {
            $ipQueries = array_values(array_filter($result['queries'], fn(array $query) => str_contains($query[0], '::account_bannedips')));
            check(count($ipQueries) === 1, 'Real User initialization reads the peer ban');
            foreach ($ipQueries as [$query, $args])
                check(in_array($peer, $args, true) && !in_array('198.51.100.99', $args, true), 'Ban keys use the peer');
            $budgetQueries = array_values(array_filter($result['queries'], fn(array $query) => str_contains($query[0], '::account_password_budget') && !str_starts_with($query[0], 'DELETE')));
            check(count($budgetQueries) === 2, 'Real local authentication reserves a durable work budget');
            foreach ($budgetQueries as [$query, $args])
                check(in_array(hash('sha256', inet_pton($peer)), $args, true) && !in_array(hash('sha256', inet_pton('198.51.100.99')), $args, true), 'Password-work keys use the canonical peer');
        }
    };

    $savedEnvironment = [];
    foreach (array_merge($headers, ['REMOTE_ADDR']) as $name) {
        $savedEnvironment[$name] = getenv($name);
        putenv($name.'=198.51.100.99');
    }
    try {
        foreach (['192.0.2.10', '127.0.0.1', '::1', '2001:db8::10', '2001:0DB8:0:0:0:0:0:10', '::ffff:192.0.2.10'] as $peer) {
            $_SERVER = ['REMOTE_ADDR' => $peer];
            $assertPeer($probe(), $peer);
            foreach ($headers as $header)
                foreach (['198.51.100.99', '203.0.113.12, 198.51.100.99', '2001:db8::99', 'for=198.51.100.99;proto=https', '', ['198.51.100.99']] as $value) {
                    $_SERVER = ['REMOTE_ADDR' => $peer, $header => $value];
                    $assertPeer($probe(), $peer);
                }
            $_SERVER = ['REMOTE_ADDR' => $peer] + array_fill_keys($headers, '198.51.100.99');
            $assertPeer($probe(), $peer);
        }
        foreach ([null, '', false, 123, [], ['192.0.2.10'], 'not-an-ip', '192.0.2.10:1234', '[::1]', 'fe80::1%eth0', '192.0.2.10, 198.51.100.99', "192.0.2.10\n"] as $peer) {
            $_SERVER = array_fill_keys($headers, '198.51.100.99');
            if ($peer !== null) $_SERVER['REMOTE_ADDR'] = $peer;
            $assertPeer($probe(), null);
        }
    }
    finally {
        foreach ($savedEnvironment as $name => $value)
            putenv($value === false ? $name : $name.'='.$value);
    }

    foreach (['127.0.0.1', '::1'] as $peer) {
        $host = str_contains($peer, ':') ? '['.$peer.']' : $peer;
        $socket = @stream_socket_server('tcp://'.$host.':0', $errno, $error);
        if (!$socket && $peer === '::1') { echo "SKIP: IPv6 loopback is unavailable; IPv6 policy checks still ran\n"; continue; }
        if (!$socket) throw new RuntimeException($error);
        $address = stream_socket_get_name($socket, false); fclose($socket);
        $log = tempnam(sys_get_temp_dir(), 'aowow-client-ip-http-');
        $server = proc_open([PHP_BINARY, '-S', $address, __FILE__], [['pipe', 'r'], ['file', $log, 'a'], ['file', $log, 'a']], $pipes, dirname(__DIR__));
        if (!is_resource($server)) throw new RuntimeException('Cannot start HTTP fixture');
        fclose($pipes[0]);
        try {
            for ($attempt = 0; $attempt < 100; ++$attempt) {
                $ready = @stream_socket_client('tcp://'.$address, $errno, $error, 0.1);
                if ($ready) { fclose($ready); break; }
                usleep(10000);
            }
            check(isset($ready) && $ready !== false, 'HTTP fixture starts');
            $sets = [[]];
            foreach (['Client-IP', 'X-Forwarded-For', 'X-Forwarded', 'Forwarded-For', 'Forwarded'] as $header)
                foreach (['198.51.100.99', '203.0.113.12, 198.51.100.99', '2001:db8::99', 'for=198.51.100.99;proto=https'] as $value)
                    $sets[] = [$header.': '.$value];
            $sets[] = ['Client-IP: 198.51.100.99', 'X-Forwarded-For: 203.0.113.12', 'Forwarded: for=2001:db8::99'];
            foreach ($sets as $headerSet) {
                $context = stream_context_create(['http' => ['method' => 'POST', 'header' => implode("\r\n", $headerSet), 'content' => '', 'timeout' => 5]]);
                $body = file_get_contents('http://'.$address.'/', false, $context);
                $assertPeer(json_decode($body, true, flags: JSON_THROW_ON_ERROR), $peer);
            }
        }
        finally {
            proc_terminate($server); proc_close($server); unlink($log);
        }
    }
    echo "PASS: $checks client IP policy/HTTP checks\n";
}
