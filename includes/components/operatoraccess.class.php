<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/** A private deployment-owned exact-IP allowlist adds a boundary to sensitive role-gated administration. */
final class OperatorAccess
{
    public static function allowed() : bool
    {
        if (!defined('AOWOW_OPERATOR_IPS')) return false;
        return self::matches($_SERVER['REMOTE_ADDR'] ?? null, AOWOW_OPERATOR_IPS);
    }

    // Use only SAPI connection metadata. Never trust request headers, environment variables or database configuration.
    public static function matches(mixed $peer, mixed $allowlist) : bool
    {
        if (!is_array($allowlist) || !$allowlist || !array_is_list($allowlist) || count($allowlist) > 64 || !($address = self::address($peer))) return false;
        $found = false;
        foreach ($allowlist as $ip)
        {
            $candidate = self::address($ip);
            if ($candidate === null) return false;           // one malformed entry invalidates the policy, including wildcards/CIDRs
            $found = $found || $address === $candidate;
        }
        return $found;
    }

    private static function address(mixed $ip) : ?string
    {
        if (!is_string($ip) || !preg_match('/^[a-f0-9:.]+$/iD', $ip) || filter_var($ip, FILTER_VALIDATE_IP) === false) return null;
        return inet_pton($ip) ?: null;
    }

    public static function assertRequest() : void
    {
        // Authorized responses also contain privileged information and must never enter a shared cache.
        header('Cache-Control: no-store');
        if (self::allowed()) return;
        http_response_code(403);
        header('Content-Type: text/plain; charset=UTF-8');
        exit('Access denied.');
    }
}
