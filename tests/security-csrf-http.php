<?php

// Real HTTP filtering and handler generation, with session-backed mutation counters.
$socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
if (!$socket) throw new RuntimeException($error);
$address = stream_socket_get_name($socket, false);
fclose($socket);
$log = tempnam(sys_get_temp_dir(), 'aowow-csrf-http-');
$server = proc_open([PHP_BINARY, '-S', $address, __DIR__.'/security-csrf.php'], [['pipe', 'r'], ['file', $log, 'a'], ['file', $log, 'a']], $pipes, dirname(__DIR__));
if (!is_resource($server)) throw new RuntimeException('HTTP fixture server did not start');
$cookie = '';
$request = function (string $query, string $method = 'GET', array $body = [], array $headers = []) use ($address, &$cookie) : array {
    if ($cookie) $headers[] = 'Cookie: '.$cookie;
    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    $context = stream_context_create(['http' => ['method' => $method, 'header' => implode("\r\n", $headers), 'content' => http_build_query($body), 'ignore_errors' => true, 'follow_location' => 0, 'timeout' => 5]]);
    $text = file_get_contents('http://'.$address.'/?'.$query, false, $context);
    $responseHeaders = http_get_last_response_headers();
    preg_match('/\s(\d{3})\s/', $responseHeaders[0], $match);
    foreach ($responseHeaders as $header)
        if (preg_match('/^Set-Cookie:\s*([^;]+)/i', $header, $m)) $cookie = $m[1];
    return ['status' => (int)$match[1], 'body' => $text, 'headers' => $responseHeaders, 'json' => json_decode($text, true)];
};
try {
    $ready = false;
    for ($attempt = 0; $attempt < 100; ++$attempt) {
        $connection = @stream_socket_client('tcp://'.$address, $errno, $error, 0.1);
        if ($connection) { fclose($connection); $ready = true; break; }
        usleep(10000);
    }
    check($ready, 'HTTP fixture server started');
    $initial = $request('fixture=token');
    $token = $initial['json']['token'];
    check(str_contains(implode('\n', $initial['headers']), 'SameSite=Lax'), 'Session cookie fixture uses explicit SameSite');
    $writeUrl = 'admin=siteconfig&action=update&key=TEST_SETTING&val=fixture';
    foreach ([['GET', [], []], ['HEAD', [], []], ['POST', [], []], ['POST', ['csrfToken' => 'wrong'], []], ['POST', ['csrfToken' => [$token]], []], ['POST', ['csrfToken' => $token], ['Origin: http://evil.example']], ['POST', ['csrfToken' => $token], ['Sec-Fetch-Site: same-site']]] as [$method, $body, $headers]) {
        $response = $request($writeUrl, $method, $body, $headers);
        check($response['status'] === (in_array($method, ['GET', 'HEAD']) ? 405 : 403), 'HTTP request rejected before configuration handler');
        check($request('fixture=token')['json']['writes'] === 0, 'Rejected request cannot change fixture state');
    }
    $valid = $request($writeUrl, 'POST', [], ['X-CSRF-Token: '.$token, 'Origin: http://'.$address]);
    check($valid['status'] === 200 && $valid['json']['writes'] === 1, 'Valid AJAX request reaches real configuration handler');
    $favorite = $request('account=favorites', 'POST', ['csrfToken' => $token, 'remove' => 3, 'id' => 1]);
    check($favorite['status'] === 200 && ($favorite['json']['writes'] ?? null) === 2, 'Valid form request reaches real favorites handler: '.$favorite['body']);
    foreach (['activate', 'confirm-email-address', 'confirm-password', 'revert-email-address'] as $command) {
        $before = $request('fixture=token')['json']['writes'];
        $view = $request('account='.$command.'&key='.str_repeat('K', 40));
        check($view['status'] === 200 && $view['json']['writes'] === $before, 'Opening confirmation link does not mutate');
        check(str_contains($view['json']['form'], 'name="csrfToken"') && str_contains($view['json']['form'], 'method="post"'), 'GET renders real protected confirmation form');
        $denied = $request('account='.$command, 'POST', ['key' => str_repeat('K', 40)]);
        check($denied['status'] === 403, 'Confirmation submission without session token rejected');
        $confirmed = $request('account='.$command, 'POST', ['csrfToken' => $token, 'key' => str_repeat('K', 40)]);
        check($confirmed['status'] === 200 && $confirmed['json']['writes'] > $before, 'Protected confirmation invokes real mutation');
    }
    $before = $request('fixture=token')['json']['writes'];
    foreach (['', 'wrong-password'] as $password) {
        $response = $request('account=update-email', 'POST', ['csrfToken' => $token, 'newemail' => 'current@example.test', 'currentPassword' => $password]);
        check($response['status'] === 200 && $response['json']['message'] === 'wrongPass' && $response['json']['writes'] === $before, 'Email change requires password reauthentication');
    }
    $response = $request('account=update-email', 'POST', ['csrfToken' => $token, 'newemail' => 'current@example.test', 'currentPassword' => 'fixture-password']);
    check($response['json']['message'] === 'newMailDiff', 'Correct password continues to normal email validation');
    $login = $request('fixture=login', 'POST', ['csrfToken' => $token]);
    check($login['json']['token'] !== $token, 'HTTP identity change rotates token');
    check($request($writeUrl, 'POST', ['csrfToken' => $token])['status'] === 403, 'Old token cannot mutate after login');
    $loggedOut = $request('fixture=logout', 'POST', ['csrfToken' => $login['json']['token']]);
    check($loggedOut['json']['token'] !== $login['json']['token'], 'HTTP logout rotates token');
    check($request($writeUrl, 'POST', ['csrfToken' => $login['json']['token']])['status'] === 403, 'Old token cannot mutate after logout');
    $rotated = $request('fixture=rotate', 'POST', ['csrfToken' => $loggedOut['json']['token']]);
    check($rotated['json']['token'] !== $loggedOut['json']['token'], 'Session ID regeneration rotates token');
    check($request($writeUrl, 'POST', ['csrfToken' => $loggedOut['json']['token']])['status'] === 403, 'Old token cannot mutate after ID regeneration');
}
finally {
    proc_terminate($server);
    proc_close($server);
    unlink($log);
}
