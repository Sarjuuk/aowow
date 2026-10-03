<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/** CLI-only expiry cleanup. Published uploads, articles, moderation records and permanent budgets are never aged out. */
final class Retention
{
    public const int LOG_DAYS = 30;
    public const int STAGING_DAYS = 2;
    public const int CACHE_DAYS = 7;
    public const int BATCH = 1000;

    public static function database(bool $apply) : array
    {
        if (!CLI) throw new \RuntimeException('Retention requires CLI.');
        $result = [];
        foreach (['errors' => '`date` < UNIX_TIMESTAMP() - '.(self::LOG_DAYS * DAY),
            'account_password_budget' => '`expires` <= UNIX_TIMESTAMP()',
            'screenshot_uploads' => '`expires` <= UNIX_TIMESTAMP()',
            'contribution_budget' => '`expires` > 0 AND `expires` <= UNIX_TIMESTAMP()'] as $table => $where)
        {
            $db = DB::Aowow();
            $order = $table === 'errors' ? 'date' : 'expires';
            if ($apply)
            {
                $db->query('DELETE FROM %n WHERE %SQL ORDER BY %n LIMIT %i', '::'.$table, $where, $order, self::BATCH);
                $result[$table] = $db->getAffectedRows();
            }
            else
                $result[$table] = (int)$db->query('SELECT COUNT(*) FROM (SELECT 1 FROM %n WHERE %SQL LIMIT %i) eligible', '::'.$table, $where, self::BATCH)->fetchSingle();
        }
        return $result;
    }

    /** Stream directories, resume after bounded batches, reject links and recheck identity/age immediately before unlink. */
    public static function files(bool $apply, string $cacheRoot, array &$cursor = []) : array
    {
        if (!CLI) throw new \RuntimeException('Retention requires CLI.');
        $result = [];
        $roots = ['staging' => 'static/uploads/temp', 'screenshots' => 'static/uploads/screenshots/temp', 'cache' => rtrim($cacheRoot, '/') ?: '/'];
        foreach ($roots as $kind => $root)
        {
            $result[$kind] = ['scanned' => 0, 'eligible' => 0, 'removed' => 0, 'more' => false];
            if (!is_dir($root)) { $cursor[$kind] = 0; continue; }
            if (is_link($root) || ($kind === 'cache' && !self::safeCacheRoot($root)))
                throw new \RuntimeException('Unsafe retention directory.');
            $offset = max(0, (int)($cursor[$kind] ?? 0));
            $seen = 0;
            $removed = 0;
            $files = $kind === 'cache'
                ? new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST)
                : new \DirectoryIterator($root);
            foreach ($files as $file)
            {
                if (in_array($file->getFilename(), ['.', '..'], true)) continue;
                if ($seen++ < $offset) continue;
                if ($result[$kind]['scanned'] >= self::BATCH)
                {
                    $result[$kind]['more'] = true;
                    break;
                }
                $result[$kind]['scanned']++;
                $path = $file->getPathname();
                $relative = substr($path, strlen($root) + 1);
                if (!self::candidate($kind, $relative) || is_link($path) || !$file->isFile()) continue;
                // Refuse a symlink in any intermediate cache directory as well as in the leaf itself.
                if (realpath(dirname($path)) !== realpath($root).($kind === 'cache' ? '/'.dirname($relative) : '')) continue;
                $before = lstat($path);
                $cutoff = time() - ($kind === 'cache' ? self::CACHE_DAYS : self::STAGING_DAYS) * DAY;
                if (!$before || $before['mtime'] > $cutoff || $before['nlink'] !== 1) continue;
                $result[$kind]['eligible']++;
                if (!$apply) continue;
                clearstatcache(true, $path);
                $now = lstat($path);
                if (!$now || $now['dev'] !== $before['dev'] || $now['ino'] !== $before['ino'] || $now['mtime'] !== $before['mtime']) continue;
                if (!unlink($path)) throw new \RuntimeException('Retention removal failed.');
                $result[$kind]['removed']++;
                $removed++;
            }
            $cursor[$kind] = $result[$kind]['more'] ? max(0, $offset + $result[$kind]['scanned'] - $removed) : 0;
        }
        return $result;
    }

    private static function safeCacheRoot(string $root) : bool
    {
        $cache = realpath($root);
        foreach (['static', 'config', 'includes', 'endpoints', 'setup', 'localization', 'template', '.git'] as $protected)
        {
            $path = realpath($protected);
            if ($path && ($cache === $path || str_starts_with(rtrim($cache, '/').'/', rtrim($path, '/').'/') ||
                str_starts_with(rtrim($path, '/').'/', rtrim($cache, '/').'/'))) return false;
        }
        return true;
    }

    private static function candidate(string $kind, string $path) : bool
    {
        if ($kind === 'cache')
            return (bool)preg_match('~^(?:bounded/[a-f0-9]{2}/[a-f0-9]|[a-f0-9]{2}/[a-f0-9]{2}/[^./][^/]*|(?:bounded/[a-f0-9]{2}|[a-f0-9]{2}/[a-f0-9]{2})/\.cache-[a-f0-9]{24})$~D', $path);
        return (bool)preg_match('~^(?:[^./][^/]*-(?:avatar-\d+|-?\d+--?\d+)-[a-zA-Z0-9]{16}(?:_original)?(?:\.jpg)?|guide-[a-zA-Z0-9]{24}\.(?:jpg|jpeg|png))$~D', $path);
    }
}
