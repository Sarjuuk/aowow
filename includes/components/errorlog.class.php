<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/** Error diagnostics deliberately contain metadata, never request values, SQL, or exception arguments. */
final class ErrorLog
{
    private static bool $writing = false;
    private static int $records = 0;

    /** Native fatal output precedes shutdown handling, so disable it before loading application code. */
    public static function configurePhp() : void
    {
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        ini_set('log_errors', '0');
    }

    public static function install() : void
    {
        set_error_handler(function (int $type, string $message, string $file, int $line) : bool
        {
            // Preserve the existing connection-test and deprecation exclusions.
            if (($type == E_WARNING && str_contains($message, 'mysqli_connect')) ||
                str_contains($file, 'xdebug://') || ($type & (E_DEPRECATED | E_USER_DEPRECATED)))
                return true;

            $level = match ($type)
            {
                E_WARNING, E_USER_WARNING => LOG_LEVEL_WARN,
                E_NOTICE, E_USER_NOTICE   => LOG_LEVEL_INFO,
                default                  => LOG_LEVEL_ERROR
            };
            self::record($type, 'PHP_ERROR', $file, $line, $level);
            return true;
        }, E_ALL);

        set_exception_handler(function (\Throwable $e) : void
        {
            self::record((int)$e->getCode(), 'EXCEPTION', $e->getFile(), $e->getLine(), LOG_LEVEL_ERROR, $e->getTrace());
            if (CLI)
                exit(1);
            (new TemplateResponse())->generateError();
        });

        register_shutdown_function(function () : void
        {
            // error_get_last also returns ignored warnings; only unhandled fatal errors belong here.
            if (($e = error_get_last()) && ($e['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR)))
                self::record($e['type'], 'FATAL_ERROR', $e['file'], $e['line'], LOG_LEVEL_ERROR);

            if (!CLI && ($n = Util::getNotes()))
                $_SESSION['notes'][] = [$n[0], $n[1], 'Deferred issues from previous request'];
        });
    }

    /** Keep useful routing context only when it names a shipped endpoint, excluding all arbitrary values. */
    private static function requestContext() : array
    {
        $route = 'unknown';
        $command = '';
        $page = array_key_first($_GET);
        $root = dirname(__DIR__, 2);
        if ($page === null)
            $route = 'home';
        else if (is_string($page) && preg_match('/^[a-z][a-z-]*$/D', $page) && is_file($root.'/endpoints/'.$page.'/'.$page.'.php'))
        {
            $route = $page;
            $value = $_GET[$page];
            if (is_string($value) && $value !== $page && preg_match('/^[a-z][a-z-]*$/D', $value) &&
                is_file($root.'/endpoints/'.$page.'/'.$value.'.php'))
                $command = $value;
        }

        $method = $_SERVER['REQUEST_METHOD'] ?? '';
        if (!in_array($method, ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], true))
            $method = CLI ? 'CLI' : 'unknown';

        return [
            CLI ? 'CLI' : http_build_query(['route' => $route] + ($command !== '' ? ['command' => $command] : [])),
            http_build_query(['method' => $method, 'postFields' => count($_POST), 'uploads' => count($_FILES)])
        ];
    }

    /** Paths outside shipped PHP sources (including eval text and uploaded filenames) are not diagnostics. */
    private static function source(string $file) : string
    {
        $root = dirname(__DIR__, 2).DIRECTORY_SEPARATOR;
        $path = realpath($file);
        if ($path === false || !str_starts_with($path, $root))
            return '[external]';

        $path = str_replace(DIRECTORY_SEPARATOR, '/', substr($path, strlen($root)));
        if (in_array($path, ['index.php', 'aowow', 'prQueue'], true) ||
            preg_match('~^(includes|endpoints|setup|template|localization|tests)/[a-zA-Z0-9_./-]+\.php$~D', $path))
            return $path;

        return '[external]';
    }

    /** Preserve the errors-table schema; failed logging cannot recurse or change application inputs. */
    public static function record(int $code, string $kind, string $file, int $line, int $level, array $trace = []) : void
    {
        if (self::$writing || self::$records >= 20)
            return;

        self::$records++;
        self::$writing = true;
        try
        {
            $file = self::source($file);
            $line = max(0, $line);
            $kind = in_array($kind, ['PHP_ERROR', 'EXCEPTION', 'FATAL_ERROR', 'DATABASE_ERROR'], true) ? $kind : 'ERROR';
            $message = $kind.' (code '.$code.'; message omitted)';
            foreach (array_slice($trace, 0, 20) as $frame)
                if (isset($frame['file'], $frame['line']))
                    $message .= "\n  ".self::source($frame['file']).':'.(int)$frame['line'];

            [$query, $post] = self::requestContext();
            $stored = false;
            try
            {
                if (DB::isConnected(DB_AOWOW))
                    $stored = DB::Aowow()->qry('INSERT INTO ::errors (`date`, `version`, `phpError`, `file`, `line`, `query`, `post`, `userGroups`, `message`) VALUES (UNIX_TIMESTAMP(), %i, %i, %s, %i, %s, %s, %i, %s) ON DUPLICATE KEY UPDATE `date` = UNIX_TIMESTAMP()',
                        AOWOW_REVISION, $code, $file, $line, $query, $post, User::$groups, $message
                    ) !== null;
            }
            catch (\Throwable) {}                         // the fallback must never expose a logger failure's message

            $diagnostic = $message.' @ '.$file.':'.$line;
            if (CLI)
                fwrite(STDERR, $diagnostic.PHP_EOL);
            else
            {
                if (!$stored)
                    error_log($diagnostic);                // explicit safe logging works with native log_errors disabled
                if (Cfg::get('DEBUG') >= $level)
                    Util::addNote($diagnostic, U_GROUP_EMPLOYEE, $level);
            }
        }
        finally
        {
            self::$writing = false;
        }
    }

    /** SQL literals can contain secrets even in successful queries; keep only a fixed operation label. */
    public static function sqlOperation(string $sql) : string
    {
        return preg_match('/^\s*(SELECT|INSERT|UPDATE|DELETE|REPLACE|ALTER|CREATE|DROP|TRUNCATE|SHOW|SET|START|COMMIT|ROLLBACK)\b/i', $sql, $m)
            ? strtoupper($m[1]) : 'SQL';
    }
}
