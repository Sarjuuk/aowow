<?php

// Standalone policy checks; --http also tests real handlers through PHP's local server.
namespace Aowow {
    class Cfg {
        public const string PATTERN_CONF_KEY_FULL = '/^[a-z0-9_\.\-]+$/i';
        public static function get(string $key) : mixed {
            return match ($key) {
                'HOST_URL' => PHP_SAPI === 'cli-server' ? 'http://'.$_SERVER['HTTP_HOST'] : 'https://aowow.example',
                'STATIC_URL', 'NAME' => '',
                'ACC_AUTH_MODE' => AUTH_MODE_SELF,
                'ACC_ALLOW_REGISTER' => 1,
                default => 0
            };
        }
        public static function set(string $key, string $value) : int { ++$_SESSION['writes']; return 0; }
    }
    class User {
        public static int $id = 1;
        public static int $groups = 0;
        public static string $username = 'fixture';
        public static ?string $ip = '127.0.0.1';
        public static function isInGroup(int $group) : bool { return true; }
        public static function isLoggedIn() : bool { return ($_GET['account'] ?? '') !== 'activate'; }
        public static function isBanned() : bool { return false; }
        public static function getUserGlobal() : array { return []; }
        public static function getFavorites() : array { return []; }
        public static function verifyCrypt(string $password, string $hash) : bool { return password_verify($password, $hash); }
        public static function destroy() : void { self::$id = 0; unset($_SESSION['user']); }
    }
    class Lang {
        public static function getLocale() : Locale { return Locale::EN; }
        public static function __callStatic(string $method, array $args) : string { return implode(':', array_filter($args, 'is_string')); }
    }
    class DB {
        public static function Aowow() : self { return new self; }
        public function qry(string $query, mixed ...$args) : int {
            // Count protected account mutations separately from the A09 work-reservation writes.
            if (!str_contains($query, '::account_password_budget') && !in_array($query, ['START TRANSACTION', 'COMMIT', 'ROLLBACK'], true)) ++$_SESSION['writes'];
            return 1;
        }
        public function selectCell(string $query, mixed ...$args) : mixed {
            if (str_contains($query, '`passHash`')) return password_hash('fixture-password', PASSWORD_BCRYPT);
            if (str_contains($query, 'SELECT `email`')) return 'current@example.test';
            if (str_contains($query, '`token`')) return in_array(str_repeat('K', 40), $args, true) ? 1 : 0;
            return 0;
        }
        public function selectRow(string $query, mixed ...$args) : array {
            if (str_contains($query, 'FOR UPDATE')) return ['id' => 1, 'login' => 'fixture', 'passHash' => password_hash('old-password', PASSWORD_BCRYPT), 'updateValue' => password_hash('new-password', PASSWORD_BCRYPT)];
            if (str_contains($query, 'SELECT `expires`')) return ['expires' => 0];
            if (!str_contains($query, '::account') || !in_array(str_repeat('K', 40), $args, true)) return [];
            return ['id' => 1, 'updateValue' => 'value', 'status' => ($_GET['account'] ?? '') === 'confirm-password' ? ACC_STATUS_CHANGE_PASS : ACC_STATUS_CHANGE_EMAIL, 'statusTimer' => time() + 3600];
        }
    }
}

namespace {
    use Aowow\Csrf;

    define('AOWOW_REVISION', 55);
    define('AOWOW_OPERATOR_IPS', ['127.0.0.1', '::1']);       // explicit synthetic operator fixture only
    require __DIR__.'/../includes/components/csrf.class.php';
    if (PHP_SAPI === 'cli' && in_array('--browser', $argv, true)) {
        require __DIR__.'/security-csrf-browser.php';
        exit;
    }
    $checks = 0;
    function check(bool $condition, string $message) : void {
        global $checks;
        ++$checks;
        if (!$condition) throw new RuntimeException($message);
    }

    if (PHP_SAPI === 'cli-server') {
        require __DIR__.'/../includes/defines.php';
        require __DIR__.'/../includes/locale.class.php';
        require __DIR__.'/../includes/utilities.php';
        require __DIR__.'/../includes/components/passwordbudget.class.php';
        require __DIR__.'/../includes/components/operatoraccess.class.php';
        require __DIR__.'/../includes/components/passwordrecovery.class.php';
        require __DIR__.'/../includes/components/accountactivation.class.php';
        require __DIR__.'/../includes/components/response/baseresponse.class.php';
        require __DIR__.'/../includes/components/response/textresponse.class.php';
        require __DIR__.'/../includes/components/response/templateresponse.class.php';
        require __DIR__.'/../includes/components/pagetemplate.class.php';
        session_set_cookie_params(['path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
        session_start();
        Csrf::assertRequest();
        $_SESSION['writes'] ??= 0;
        $form = '';
        if (isset($_GET['fixture'])) {
            if ($_GET['fixture'] !== 'token') Csrf::assertRequest(true);
            if ($_GET['fixture'] === 'login') $_SESSION['user'] = 1;
            if ($_GET['fixture'] === 'logout') unset($_SESSION['user']);
            if ($_GET['fixture'] === 'rotate') session_regenerate_id(true);
        }
        else if (($_GET['admin'] ?? '') === 'siteconfig') {
            require __DIR__.'/../endpoints/admin/siteconfig_update.php';
            $response = new Aowow\AdminSiteconfigActionUpdateResponse('siteconfig');
            (new ReflectionMethod($response, 'generate'))->invoke($response);
        }
        else if (($_GET['account'] ?? '') === 'favorites') {
            require __DIR__.'/../endpoints/account/favorites.php';
            $response = new Aowow\AccountFavoritesResponse('favorites');
            (new ReflectionMethod($response, 'generate'))->invoke($response);
        }
        else if (($_GET['account'] ?? '') === 'update-email') {
            require __DIR__.'/../endpoints/account/update-email.php';
            $response = new Aowow\AccountUpdateemailResponse('update-email');
            $message = (new ReflectionMethod($response, 'updateMail'))->invoke($response);
        }
        else {
            $command = $_GET['account'];
            // Run real confirmation generation, omitting only unrelated metadata.
            foreach (['activate', 'confirm-email-address', 'confirm-password', 'revert-email-address'] as $file)
                require __DIR__.'/../endpoints/account/'.$file.'.php';
            trait FixtureMetadata {
                protected function generateMetadata(bool $useArticle = true) : void {}
            }
            class ActivationFixture extends Aowow\AccountActivateResponse { use FixtureMetadata; }
            class EmailFixture extends Aowow\AccountConfirmemailaddressResponse { use FixtureMetadata; }
            class PasswordFixture extends Aowow\AccountConfirmpasswordResponse { use FixtureMetadata; }
            class RevertFixture extends Aowow\AccountRevertemailaddressResponse { use FixtureMetadata; }
            $fixtureClass = match ($command) {
                'activate' => ActivationFixture::class,
                'confirm-email-address' => EmailFixture::class,
                'confirm-password' => PasswordFixture::class,
                'revert-email-address' => RevertFixture::class
            };
            $response = new $fixtureClass();
            (new ReflectionMethod($response, 'generate'))->invoke($response);
            if (($response->inputbox[0] ?? '') === 'inputbox-form-confirm') {
                $template = (new ReflectionClass(Aowow\Template\PageTemplate::class))->newInstanceWithoutConstructor();
                $render = (function (array $vars) : string {
                    extract($vars);
                    ob_start();
                    try { include __DIR__.'/../template/bricks/inputbox-form-confirm.tpl.php'; return ob_get_contents(); }
                    finally { ob_end_clean(); }
                })->bindTo($template, Aowow\Template\PageTemplate::class);
                $form = $render($response->inputbox[1]);
            }
        }
        header('Content-Type: application/json');
        echo json_encode(['token' => Csrf::token(), 'writes' => $_SESSION['writes'], 'form' => $form, 'message' => $message ?? ''], JSON_THROW_ON_ERROR);
        exit;
    }

    $_GET = $_POST = $_SESSION = [];
    $_SERVER = ['REQUEST_METHOD' => 'POST'];
    $token = Csrf::token();
    check(strlen($token) === 64 && ctype_xdigit($token), 'Token contains 256 random bits');
    check(Csrf::token() === $token, 'Same session token is stable across tabs');
    $_SESSION['user'] = 1;
    $loggedInToken = Csrf::token();
    check($loggedInToken !== $token, 'Login rotates token');
    unset($_SESSION['user']);
    check(Csrf::token() !== $loggedInToken, 'Logout rotates token');
    $token = Csrf::token();
    foreach ([null, '', str_repeat('0', 64), [$token], str_repeat('x', 10000)] as $bad) {
        $_POST['csrfToken'] = $bad;
        check(Csrf::requestStatus() === 403, 'Missing, wrong, or malformed tokens fail closed');
    }
    $_POST = ['csrfToken' => $token];
    check(Csrf::requestStatus() === 0, 'Native form token accepted');
    $_POST = [];
    $_SERVER['HTTP_X_CSRF_TOKEN'] = $token;
    check(Csrf::requestStatus() === 0, 'AJAX/raw upload header accepted');
    foreach (['https://evil.example', 'null', 'https://aowow.example.evil.example', 'http://aowow.example', 'https://aowow.example:8443', 'https://aowow.example/path', 'https://aowow.example?query', 'https://aowow.example#fragment'] as $origin) {
        $_SERVER['HTTP_ORIGIN'] = $origin;
        check(Csrf::requestStatus() === 403, 'Wrong or malformed origin rejected');
    }
    $_SERVER['HTTP_ORIGIN'] = 'https://AOWOW.example:443';
    check(Csrf::requestStatus() === 0, 'Canonical same-origin comparison accepts default port');
    unset($_SERVER['HTTP_ORIGIN']);
    $_SERVER['HTTP_REFERER'] = 'https://aowow.example/path?query=1';
    check(Csrf::requestStatus() === 0, 'Same-origin Referer fallback accepted');
    $_SERVER['HTTP_REFERER'] = 'https://evil.example/path';
    check(Csrf::requestStatus() === 403, 'Cross-origin Referer rejected');
    unset($_SERVER['HTTP_REFERER']);
    foreach (['cross-site', 'same-site'] as $site) {
        $_SERVER['HTTP_SEC_FETCH_SITE'] = $site;
        check(Csrf::requestStatus() === 403, 'Cross-origin Fetch Metadata rejected');
    }
    unset($_SERVER['HTTP_SEC_FETCH_SITE']);
    unset($_SERVER['HTTP_X_CSRF_TOKEN']);
    $_GET = ['csrfToken' => $token];
    check(Csrf::requestStatus() === 403, 'URL tokens never authorize POST');
    $routes = [];
    foreach (Csrf::POST_ROUTES as $route => $commands)
        foreach ($commands as $command) $routes[] = [$route => $command === '*' ? '' : $command];
    foreach (Csrf::ADMIN_ACTIONS as $command => $actions)
        foreach ($actions as $action) $routes[] = ['admin' => $command, 'action' => $action];
    $routes[] = ['admin' => 'announcements', 'status' => '0'];
    foreach ($routes as $query) {
        $_GET = $query;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        check(Csrf::requestStatus() === 405, 'Mutating GET rejected');
        $_SERVER['REQUEST_METHOD'] = 'HEAD';
        check(Csrf::requestStatus() === 405, 'Mutating HEAD rejected');
        $_SERVER['REQUEST_METHOD'] = 'POST';
        check(Csrf::requestStatus() === 403, 'POST command needs a token');
        $_POST = ['csrfToken' => $token];
        check(Csrf::requestStatus() === 0, 'Protected POST preserves command selectors');
        $_POST = [];
    }
    foreach ([['search' => 'text'], ['guide' => 'edit', 'id' => '1'], ['comment' => 'rating'], ['admin' => 'screenshots', 'action' => 'list'], ['account' => 'confirm-email-address', 'key' => 'key']] as $query) {
        $_GET = $query;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        check(Csrf::requestStatus() === 0, 'Read-only pages and confirmation links remain GET');
    }
    $_GET = []; $_POST = ['csrfToken' => $token];
    foreach (['PUT', 'PATCH', 'DELETE', 'OPTIONS', 'TRACE'] as $method) {
        $_SERVER['REQUEST_METHOD'] = $method;
        check(Csrf::requestStatus() === 405, 'Unsupported methods cannot mutate');
    }
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SESSION['user'] = 2;
    check(Csrf::requestStatus() === 403, 'A prior identity token is invalid before rendering another page');
    require __DIR__.'/../includes/components/pagetemplate.class.php';
    $cachedTemplate = (new ReflectionClass(Aowow\Template\PageTemplate::class))->newInstanceWithoutConstructor();
    $field = new ReflectionMethod($cachedTemplate, 'csrfField');
    $firstField = $field->invoke($cachedTemplate);
    $_SESSION = [];
    check($field->invoke($cachedTemplate) !== $firstField, 'Reused template renders a fresh token for another session');
    if (in_array('--http', $argv, true)) require __DIR__.'/security-csrf-http.php';
    echo 'PASS: '.$checks.' CSRF checks'.PHP_EOL;
}
