<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/** Referers are untrusted. Return only bounded origin-relative URLs within the configured application path. */
final class ReturnTarget
{
    public static function fromReferer(string $fallback = '.') : string
    {
        return self::local($_SERVER['HTTP_REFERER'] ?? null, $fallback);
    }

    // Fallbacks are fixed caller-owned routes, never another request value.
    public static function local(mixed $url, string $fallback = '.') : string
    {
        $base = self::parts(Cfg::get('HOST_URL'));
        $target = self::parts($url);
        if (!$base || !$target || self::origin($base) !== self::origin($target)) return $fallback;
        $prefix = rtrim($base['path'] ?? '/', '/');
        $path = $target['path'] ?? '/';
        if ($prefix !== '' && $path !== $prefix && !str_starts_with($path, $prefix.'/')) return $fallback;
        return $path.(isset($target['query']) ? '?'.$target['query'] : '').(isset($target['fragment']) ? '#'.$target['fragment'] : '');
    }

    private static function origin(array $parts) : string
    {
        $host = strtolower($parts['host']);
        if (str_starts_with($host, '['))
            $host = '['.inet_ntop(inet_pton(substr($host, 1, -1))).']';
        return strtolower($parts['scheme']).'://'.$host.':'.($parts['port'] ?? (strtolower($parts['scheme']) === 'https' ? 443 : 80));
    }

    /** Reject browser/parser ambiguities before parse_url can normalize invalid bytes or obscure authority/path boundaries. */
    private static function parts(mixed $url) : ?array
    {
        if (!is_string($url) || strlen($url) > 4096 || !preg_match('~^https?://~i', $url) ||
            preg_match('~[\x00-\x20\x7f\\\\]|%(?![a-f0-9]{2})|%(?:0[0-9a-f]|1[0-9a-f]|7f|5c)~i', $url)) return null;
        try { $parts = parse_url($url); }
        catch (\ValueError) { return null; }
        if (!$parts || !isset($parts['scheme'], $parts['host']) || isset($parts['user']) || isset($parts['pass'])) return null;
        $host = $parts['host'];
        if (str_starts_with($host, '[') && str_ends_with($host, ']'))
        {
            if (!filter_var(substr($host, 1, -1), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) return null;
        }
        else if (!preg_match('/^[a-z0-9](?:[a-z0-9.-]*[a-z0-9])?$/iD', $host)) return null;
        if (isset($parts['port']) && ($parts['port'] < 1 || $parts['port'] > 65535)) return null;
        $path = $parts['path'] ?? '/';
        if (!str_starts_with($path, '/') || str_starts_with($path, '//') || preg_match('/%(?:2f|5c|25)/i', $path)) return null;
        foreach (explode('/', rawurldecode($path)) as $segment)
            if ($segment === '.' || $segment === '..') return null;
        return $parts;
    }
}
