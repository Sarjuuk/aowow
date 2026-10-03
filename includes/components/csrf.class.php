<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

final class Csrf
{
    // Shared with the browser: query-string selectors stay compatible, but these
    // commands may only execute through a protected POST. Mixed pages remain GET.
    public const array POST_ROUTES = [
        'account' => ['delete-icon', 'exclude', 'favorites', 'forum-avatar', 'premium-border', 'rename-icon', 'signout', 'update-community-settings', 'update-email', 'update-general-settings', 'update-password', 'update-username', 'weightscales'],
        'admin' => ['comment', 'guide', 'spawn-override'],
        'comment' => ['add', 'add-reply', 'delete', 'delete-reply', 'detach-reply', 'downvote-reply', 'edit', 'edit-reply', 'flag-reply', 'out-of-date', 'sticky', 'undelete', 'upvote-reply', 'vote'],
        'cookie' => ['*'],
        'locale' => ['*'],
        'profile' => ['delete', 'link', 'pin', 'private', 'public', 'purge', 'resync', 'save', 'unlink', 'unpin'],
        'guild' => ['resync'],
        'arena-team' => ['resync'],
        'signature' => ['delete'],
        'screenshot' => ['add', 'complete'],
        'upload' => ['image-crop', 'image-complete'],
        'edit' => ['image'],
        'video' => ['add', 'complete'],
        'guide' => ['vote']
    ];
    public const array ADMIN_ACTIONS = [
        'siteconfig' => ['add', 'remove', 'update'],
        'screenshots' => ['approve', 'delete', 'editalt', 'relocate', 'sticky'],
        'videos' => ['approve', 'delete', 'edittitle', 'order', 'relocate', 'sticky'],
        'weight-presets' => ['save']
    ];

    /** Issue a CSPRNG token per session identity; login/logout and ID changes rotate it. */
    public static function token() : string
    {
        if (!is_string($_SESSION['csrfToken'] ?? null) || !preg_match('/^[a-f0-9]{64}$/D', $_SESSION['csrfToken']) || ($_SESSION['csrfIdentity'] ?? null) !== self::identity())
        {
            $_SESSION['csrfToken'] = bin2hex(random_bytes(32));
            $_SESSION['csrfIdentity'] = self::identity();
        }
        return $_SESSION['csrfToken'];
    }

    private static function identity() : string
    {
        return session_id().':'.(int)($_SESSION['user'] ?? 0);
    }

    public static function isPostOnly(array $query) : bool
    {
        $route = array_key_first($query);
        $command = $query[$route] ?? null;
        if (!is_string($command))
            return false;
        if (in_array($command, self::POST_ROUTES[$route] ?? [], true) || in_array('*', self::POST_ROUTES[$route] ?? [], true))
            return true;
        return $route === 'admin' && (in_array($query['action'] ?? '', self::ADMIN_ACTIONS[$command] ?? [], true) || ($command === 'announcements' && isset($query['status'])));
    }

    /** Validate before any handler side effect; tokens are never accepted from URLs. */
    public static function requestStatus(bool $postOnly = false) : int
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array($method, ['GET', 'HEAD'], true))
            return $postOnly || self::isPostOnly($_GET) ? 405 : 0;
        if ($method !== 'POST')
            return 405;

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrfToken'] ?? null;
        $expected = $_SESSION['csrfToken'] ?? null;
        if (!is_string($token) || strlen($token) !== 64 || !is_string($expected) || ($_SESSION['csrfIdentity'] ?? null) !== self::identity() || !hash_equals($expected, $token))
            return 403;

        if (isset($_SERVER['HTTP_SEC_FETCH_SITE']) && !in_array($_SERVER['HTTP_SEC_FETCH_SITE'], ['same-origin', 'none'], true))
            return 403;
        $source = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? null;
        if ($source !== null)
        {
            $origin = self::origin($source);
            if (!$origin || $origin !== self::origin(Cfg::get('HOST_URL')))
                return 403;
            if (isset($_SERVER['HTTP_ORIGIN']))
            {
                $parts = parse_url($source);
                if (!in_array($parts['path'] ?? null, [null, ''], true) || isset($parts['query']) || isset($parts['fragment']))
                    return 403;
            }
        }
        return 0;
    }

    private static function origin(string $url) : ?string
    {
        $parts = parse_url($url);
        if (!$parts || !isset($parts['scheme'], $parts['host']) || isset($parts['user']) || isset($parts['pass']))
            return null;
        $scheme = strtolower($parts['scheme']);
        if (!in_array($scheme, ['http', 'https'], true))
            return null;
        return $scheme.'://'.strtolower($parts['host']).':'.($parts['port'] ?? ($scheme === 'https' ? 443 : 80));
    }

    public static function assertRequest(bool $postOnly = false) : void
    {
        if (!$status = self::requestStatus($postOnly))
            return;
        http_response_code($status);
        header('Content-Type: text/plain; charset=UTF-8');
        header('Cache-Control: no-store');
        if ($status === 405)
            header('Allow: POST');
        exit('Request rejected. Reload the page and try again.');
    }
}
