<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/** Authenticate cache bytes and their key/expiry before decompression or object restoration. */
final class CacheEnvelope
{
    public const int MAX_BYTES = 33554432;
    public const int FILE_MAX_BYTES = 131072;
    private const int MAX_PLAIN_BYTES = 67108864;

    private static function key() : ?string
    {
        if (!defined('AOWOW_CACHE_KEY') || !is_string(AOWOW_CACHE_KEY) ||
            !preg_match('/^[a-fA-F0-9]{64}$/D', AOWOW_CACHE_KEY))
            return null;
        return hex2bin(AOWOW_CACHE_KEY);
    }

    public static function enabled() : bool
    {
        return self::key() !== null;
    }

    // Public-asset directory helpers may relax modes; private cache paths must retain their permissions.
    public static function writeDirectory(string $directory) : bool
    {
        return !is_link($directory) && (is_dir($directory) || @mkdir($directory, 0700, true)) && is_writable($directory);
    }

    /** 4096 replaceable slots cap new file-cache payloads at 512 MiB, regardless of query-key cardinality. */
    public static function filePath(string $root, string $key) : string
    {
        $slot = substr(hash('sha256', $key), 0, 3);
        return rtrim($root, '/').'/bounded/'.substr($slot, 0, 2).'/'.$slot[2];
    }

    public static function writeBoundedFile(string $root, string $key, string $data) : bool
    {
        if (strlen($data) > self::FILE_MAX_BYTES) return false;
        $lock = self::fileLock($root);
        if (!$lock) return false;
        try
        {
            $path = self::filePath($root, $key);
            return self::writeDirectory(rtrim($root, '/').'/bounded') && self::writeDirectory(dirname($path)) && self::writeFile($path, $data);
        }
        finally { flock($lock, LOCK_UN); fclose($lock); }
    }

    // Collision entries authenticate against their original cache key and simply become misses.
    public static function clearFiles(string $root) : void
    {
        $lock = self::fileLock($root);
        if (!$lock) return;
        try
        {
            // Dependencies can invalidate several variants; clear the finite slot set conservatively.
            foreach (glob(rtrim($root, '/').'/bounded/[0-9a-f][0-9a-f]/[0-9a-f]') ?: [] as $path)
                if (!is_link(dirname($path)) && !is_link(rtrim($root, '/').'/bounded')) @unlink($path);
        }
        finally { flock($lock, LOCK_UN); fclose($lock); }
    }

    private static function fileLock(string $root)
    {
        if (!self::writeDirectory(rtrim($root, '/'))) return false;
        $path = rtrim($root, '/').'/.bounded.lock';
        if (is_link($path)) return false;
        $mask = umask(0077);
        try { $stream = @fopen($path, 'c+b'); }
        finally { umask($mask); }
        if (!$stream) return false;
        if (!flock($stream, LOCK_EX | LOCK_NB)) { fclose($stream); return false; }
        return $stream;
    }

    /** Publish a complete entry without following an existing destination symlink. */
    public static function writeFile(string $path, string $data) : bool
    {
        $temporary = dirname($path).'/.cache-'.bin2hex(random_bytes(12));
        $stream = @fopen($temporary, 'xb');
        if (!$stream)
            return false;
        try
        {
            if (!chmod($temporary, 0600))
                return false;
            $offset = 0;
            while ($offset < strlen($data))
            {
                $written = fwrite($stream, substr($data, $offset, 65536));
                if ($written === false || $written === 0)
                    return false;
                $offset += $written;
            }
            if (!fflush($stream))
                return false;
            fclose($stream);
            $stream = null;
            return rename($temporary, $path);
        }
        finally
        {
            if (is_resource($stream))
                fclose($stream);
            if (file_exists($temporary) || is_link($temporary))
                unlink($temporary);
        }
    }

    public static function memcachedKey(string $key) : string
    {
        return 'aowow:'.substr(hash('sha256', self::key() ?? ''), 0, 12).':'.$key;
    }

    public static function seal(string $cacheKey, string|object $result, array $callback, int $lifetime) : ?string
    {
        if (!($key = self::key()) || $lifetime <= 0)
            return null;

        $plain = serialize([$result, $callback]);
        if (strlen($plain) > self::MAX_PLAIN_BYTES)
            return null;
        $compressed = gzcompress($plain, 9);
        if ($compressed === false)
            return null;
        $body = 'AOWOW-CACHE-1 '.time().' '.$lifetime.' '.AOWOW_REVISION."\n".$compressed;
        $envelope = hash_hmac('sha256', $cacheKey."\0".$body, $key)."\n".$body;
        return strlen($envelope) <= self::MAX_BYTES ? $envelope : null;
    }

    public static function open(string $cacheKey, mixed $envelope) : ?array
    {
        if (!($key = self::key()) || !is_string($envelope) || strlen($envelope) > self::MAX_BYTES ||
            strlen($envelope) < 66 || $envelope[64] !== "\n")
            return null;

        $body = substr($envelope, 65);
        if (!hash_equals(hash_hmac('sha256', $cacheKey."\0".$body, $key), substr($envelope, 0, 64)))
            return null;

        $parts = explode("\n", $body, 2);
        if (count($parts) !== 2 || !preg_match('/^AOWOW-CACHE-1 ([0-9]{1,10}) ([0-9]{1,10}) ([0-9]+)$/D', $parts[0], $m))
            return null;

        $timestamp = (int)$m[1];
        $lifetime = (int)$m[2];
        if ($timestamp > time() || $lifetime <= 0 || $timestamp + $lifetime <= time() || (int)$m[3] !== AOWOW_REVISION)
            return null;

        $plain = @gzuncompress($parts[1], self::MAX_PLAIN_BYTES);
        if ($plain === false)
            return null;

        try
        {
            // Templates intentionally contain frontend objects, locale enums and source-registered hooks.
            // The private key, not the writable cache, authorizes this object graph and its callbacks.
            $data = @unserialize($plain, ['allowed_classes' => true, 'max_depth' => 128]);
            if (!is_array($data) || array_keys($data) !== [0, 1] ||
                (!is_string($data[0]) && !$data[0] instanceof Template\PageTemplate) ||
                !is_array($data[1]) || array_keys($data[1]) !== [0, 1] ||
                ($data[1][0] !== null && !is_string($data[1][0]) && !is_array($data[1][0])))
                return null;
        }
        catch (\Throwable)
        {
            return null;
        }
        return [$data[0], $data[1], $timestamp, $lifetime];
    }

    /** PECL get() decodes server-supplied serialization flags before returning; read only raw strings. */
    public static function fetchMemcached(string $cacheKey) : ?string
    {
        if (!function_exists('stream_socket_client') || !preg_match('/^[a-zA-Z0-9:_-]{1,250}$/D', $cacheKey))
            return null;
        $socket = @stream_socket_client('tcp://localhost:11211', $errno, $error, 0.5);
        if (!$socket)
            return null;
        try
        {
            stream_set_blocking($socket, false);
            $deadline = microtime(true) + 2;
            $request = 'get '.$cacheKey."\r\n";
            if (fwrite($socket, $request) !== strlen($request))
                return null;
            $header = '';
            while (strlen($header) < 512 && !str_ends_with($header, "\n"))
            {
                $byte = self::readBytes($socket, 1, $deadline);
                if ($byte === null)
                    return null;
                $header .= $byte;
            }
            if (!preg_match('/^VALUE '.preg_quote($cacheKey, '/').' 0 ([0-9]{1,8})\r\n$/D', $header, $m))
                return null;
            $length = (int)$m[1];
            if ($length > self::MAX_BYTES)
                return null;
            $data = self::readBytes($socket, $length + 7, $deadline);
            if ($data === null || substr($data, $length) !== "\r\nEND\r\n")
                return null;
            return substr($data, 0, $length);
        }
        finally
        {
            fclose($socket);
        }
    }

    private static function readBytes($socket, int $length, float $deadline) : ?string
    {
        $data = '';
        while (strlen($data) < $length)
        {
            if (microtime(true) >= $deadline)
                return null;
            $chunk = fread($socket, min(65536, $length - strlen($data)));
            if ($chunk === false || ($chunk === '' && feof($socket)))
                return null;
            if ($chunk === '')
                usleep(1000);
            else
                $data .= $chunk;
        }
        return $data;
    }
}
