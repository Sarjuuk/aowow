<?php

// Synthetic Dibi transport only: the logger, DB event callbacks, config and notes are production code.
namespace Dibi {
    class Connection {}
    class Event {
        public mixed $result;
        public string $sql = '';
        public array $source = [];
    }
}

namespace {
    class dibi {
        public static string $sql = '';
        public static float $elapsedTime = 0.01;
    }
}

namespace Aowow {
    class User {
        public static int $groups = 0;
        public static function isInGroup(int $mask) : bool { return true; }
    }
    class TemplateResponse {
        public function generateError() : void { echo 'fixture error response'; }
    }
    class CLI {
        public const int LOG_ERROR = 1;
        public const int LOG_WARN = 2;
        public static function write(string $message, int $level) : void { fwrite(STDERR, $message."\n"); }
    }
}

namespace {
    use Aowow\Cfg;
    use Aowow\DB;
    use Aowow\ErrorLog;
    use Aowow\User;
    use Aowow\Util;

    $mode = $argv[2];
    $sink = $argv[3];
    define('AOWOW_REVISION', 57);
    define('CLI', $argv[4] === 'cli');
    require __DIR__.'/../includes/defines.php';
    require __DIR__.'/../includes/database.php';
    require __DIR__.'/../includes/cfg.class.php';
    require __DIR__.'/../includes/utilities.php';
    require __DIR__.'/../includes/components/errorlog.class.php';

    class LogConnection extends Aowow\DibiConnection {
        public static array $records = [];
        public static bool $fail = false;
        public static bool $connected = true;
        public static array $config = [];
        public function isConnected() : bool { return self::$connected; }
        public function selectAssoc(mixed ...$args) : ?array { return self::$config; }
        public function qry(mixed ...$args) : ?int {
            if (self::$fail) {
                trigger_error('SECRET_LOG_WRITE_FAILURE', E_USER_WARNING);
                throw new RuntimeException('SECRET_LOG_WRITE_FAILURE');
            }
            if (!str_contains($args[0], 'INSERT INTO ::errors')) throw new RuntimeException('Unexpected fixture write');
            self::$records[] = array_slice($args, 1);
            return 1;
        }
    }
    (new ReflectionProperty(DB::class, 'interfaceCache'))->setValue(null, [DB_AOWOW => new LogConnection]);
    (new ReflectionProperty(DB::class, 'interfaceTimes'))->setValue(null, [DB_AOWOW => [time() + 3600, 3600]]);
    (new ReflectionProperty(Cfg::class, 'store'))->setValue(null, ['debug' => [3, Cfg::FLAG_TYPE_INT, 0, 0, '']]);
    (new ReflectionProperty(Cfg::class, 'isLoaded'))->setValue(null, true);
    User::$groups = U_GROUP_ADMIN;

    $_GET = ['account' => $argv[5], 'key' => 'SECRET_GET_KEY', 'token' => 'SECRET_GET_TOKEN', 'next' => 'SECRET_REDIRECT'];
    $_POST = [
        'password' => 'SECRET_PASSWORD', 'c_password' => 'SECRET_CONFIRM',
        'currentPassword' => 'SECRET_CURRENT', 'newPassword' => 'SECRET_NEW', 'confirmPassword' => 'SECRET_NEW_CONFIRM',
        'key' => 'SECRET_POST_KEY', 'csrfToken' => 'SECRET_CSRF', 'sessionKey' => 'SECRET_SESSION',
        'text' => 'SECRET_PRIVATE_TEXT', 'unknown' => ['nested' => 'SECRET_UNKNOWN'],
        'SECRET_FIELD_NAME' => 'SECRET_UNKNOWN_VALUE'
    ];
    $_FILES = ['image' => ['name' => 'SECRET_UPLOAD_FILENAME']];
    $_COOKIE = ['session' => 'SECRET_COOKIE'];
    $_SERVER['QUERY_STRING'] = http_build_query($_GET);
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer SECRET_AUTHORIZATION';
    $_SERVER['REQUEST_URI'] = '/?'.$_SERVER['QUERY_STRING'];
    if ($mode === 'unknown-route')
        $_GET = ['SECRET_ROUTE_NAME' => 'SECRET_ROUTE_VALUE'];
    $inputs = [$_GET, $_POST, $_FILES, $_COOKIE, $_SERVER];

    // Production shutdown logging runs first; this observer records sinks without echoing private inputs.
    ErrorLog::configurePhp();
    ErrorLog::install();
    register_shutdown_function(function () use ($inputs, $sink) : void {
        file_put_contents($sink, json_encode([
            'records' => LogConnection::$records, 'notes' => Util::getNotes(), 'session' => $_SESSION ?? [],
            'unchanged' => $inputs === [$_GET, $_POST, $_FILES, $_COOKIE, $_SERVER],
            'ini' => [ini_get('display_errors'), ini_get('display_startup_errors'), ini_get('log_errors')]
        ], JSON_THROW_ON_ERROR));
    });

    switch ($mode) {
        case 'warning':
            trigger_error('SECRET_WARNING_MESSAGE '.http_build_query($_POST), E_USER_WARNING);
            break;
        case 'debug-off':
            (new ReflectionProperty(Cfg::class, 'store'))->setValue(null, ['debug' => [0, Cfg::FLAG_TYPE_INT, 0, 0, '']]);
            trigger_error('SECRET_WARNING_MESSAGE', E_USER_WARNING);
            break;
        case 'exception':
            function sensitiveCall(string $argument) : never { throw new RuntimeException('SECRET_EXCEPTION_MESSAGE '.$argument); }
            sensitiveCall($_POST['password']);
        case 'fatal':
            // Redeclaration is an actual E_ERROR, bypassing both error and exception handlers.
            require __DIR__.'/security-error-log-fatal.php';
            require __DIR__.'/security-error-log-fatal.php';
            break;
        case 'database':
            $event = new Dibi\Event;
            $event->result = new RuntimeException('SECRET_DATABASE_MESSAGE', 1062);
            $event->sql = "INSERT INTO accounts VALUES ('SECRET_SQL_PASSWORD')";
            $event->source = [__FILE__, __LINE__];
            DB::errorLogger($event);
            foreach (['SELECT', 'INSERT', 'UPDATE', 'DELETE', 'REPLACE', 'ALTER', 'CREATE', 'DROP', 'TRUNCATE', 'SHOW', 'SET', 'START', 'COMMIT', 'ROLLBACK'] as $operation) {
                dibi::$sql = $operation." 'SECRET_SQL_PASSWORD'";
                DB::profiler($event);
            }
            echo DB::getProfiles();
            break;
        case 'failed-log':
            LogConnection::$fail = true;
            trigger_error('SECRET_WARNING_MESSAGE', E_USER_WARNING);
            break;
        case 'no-database':
            LogConnection::$connected = false;
            trigger_error('SECRET_WARNING_MESSAGE', E_USER_WARNING);
            break;
        case 'unknown-route':
            trigger_error('SECRET_WARNING_MESSAGE', E_USER_WARNING);
            break;
        case 'config':
            foreach (['display_errors', 'display_startup_errors', 'log_errors'] as $key)
                LogConnection::$config[$key] = ['1', Cfg::FLAG_PHP | Cfg::FLAG_TYPE_BOOL, 0, 0, ''];
            LogConnection::$config['debug'] = ['3', Cfg::FLAG_TYPE_INT, 0, 0, ''];
            Cfg::load();
            foreach (['DISPLAY_ERRORS', 'display_startup_errors', 'log_errors'] as $key)
                echo Cfg::add($key, '1');
            // An existing config row must also be rejected by the update path.
            (new ReflectionProperty(Cfg::class, 'store'))->setValue(null, ['log_errors' => ['0', Cfg::FLAG_PHP, 0, 0, '']]);
            echo Cfg::set('LOG_ERRORS', '1');
            break;
        case 'ignored':
            trigger_error('SECRET_DEPRECATION', E_USER_DEPRECATED);
            // A suppressed native warning left in error_get_last must not become a fatal log at shutdown.
            restore_error_handler();
            @file_get_contents('/missing/SECRET_MISSING_FILE');
            break;
        case 'unsafe-source':
            ErrorLog::record(2, 'SECRET_ERROR_KIND', '/uploads/SECRET_SOURCE.php', 4, LOG_LEVEL_WARN, [
                ['file' => '/uploads/SECRET_TRACE.php', 'line' => 5, 'args' => ['SECRET_ARGUMENT']]
            ]);
            break;
        default:
            throw new RuntimeException('Unknown fixture mode');
    }
}
