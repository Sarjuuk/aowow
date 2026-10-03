<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/** Resolve private JPEG previews from current account/session authority, never a caller-supplied path. */
final class PrivateUpload
{
    private const int PREVIEW_LIFETIME = DAY;
    private const int MAX_PREVIEWS = 100;

    public static function registerTemp(string $kind, string $key, string $path) : bool
    {
        if (!User::isLoggedIn() || !self::validKey($key) || !self::confinedPath($path, $kind))
            return false;

        $previews = $_SESSION['uploadPreviews'] ?? [];
        if (!is_array($previews))
            $previews = [];
        foreach ($previews as $id => $entry)
            if (!is_array($entry) || ($entry['owner'] ?? null) !== User::$id || ($entry['expires'] ?? 0) <= time())
                unset($previews[$id]);

        unset($previews[$kind.':'.$key]);
        while (count($previews) >= self::MAX_PREVIEWS)
            array_shift($previews);

        $previews[$kind.':'.$key] = ['owner' => User::$id, 'expires' => time() + self::PREVIEW_LIFETIME, 'path' => $path];
        $_SESSION['uploadPreviews'] = $previews;
        return true;
    }

    public static function tempUrl(string $kind, string $key) : string
    {
        if (!self::resolve($kind, $key))
            return '';

        // Session cookies belong to the application host, even when public images use a separate static host.
        return Cfg::get('HOST_URL').'/?upload=preview&kind='.$kind.'&key='.$key;
    }

    /** Bind crop/completion to the exact destination and original registered in this browser session. */
    public static function screenshotStage(string $key, int $type, int $typeId) : ?array
    {
        if (!User::canUploadScreenshot() || !($preview = self::resolve('screenshot', $key)))
            return null;

        $base = User::$username.'-'.$type.'-'.$typeId.'-'.$key;
        $expected = sprintf(ScreenshotMgr::PATH_TEMP, $base);
        $original = sprintf(ScreenshotMgr::PATH_TEMP, $base.'_original');
        if (is_link($expected) || realpath($expected) !== $preview || is_link($original) || !is_file($original))
            return null;
        $original = realpath($original);
        if (!$original || dirname($original) !== dirname($preview))
            return null;

        return ['preview' => $preview, 'original' => $original, 'expires' => $_SESSION['uploadPreviews']['screenshot:'.$key]['expires']];
    }

    /** Only committed completion consumes session authority and staging; replay remains blocked in SQL. */
    public static function consumeScreenshot(string $key, array $stage) : void
    {
        unset($_SESSION['uploadPreviews']['screenshot:'.$key]);
        foreach (['preview', 'original'] as $file)
            if (is_file($stage[$file]) && !unlink($stage[$file]))
                trigger_error('PrivateUpload::consumeScreenshot - staging cleanup failed', E_USER_WARNING);
    }

    public static function resolve(string $kind, ?string $key = null, ?int $id = null) : ?string
    {
        if (!User::isLoggedIn() || User::isBanned())
            return null;

        if ($kind === 'pending')
        {
            if (!$id || $id < 1)
                return null;
            $row = DB::Aowow()->selectRow('SELECT `userIdOwner`, `status` FROM ::screenshots WHERE `id` = %i', $id);
            if (!$row || (($row['status'] & CC_FLAG_APPROVED) && !($row['status'] & CC_FLAG_DELETED)))
                return null;

            $moderator = User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_SCREENSHOT);
            if (!$moderator && ((int)$row['userIdOwner'] !== User::$id || ($row['status'] & CC_FLAG_DELETED)))
                return null;

            return self::confinedPath(sprintf(ScreenshotMgr::PATH_PENDING, $id), $kind);
        }

        if (!in_array($kind, ['screenshot', 'avatar'], true) || !self::validKey($key))
            return null;
        $entry = $_SESSION['uploadPreviews'][$kind.':'.$key] ?? null;
        if (!is_array($entry) || ($entry['owner'] ?? null) !== User::$id ||
            !is_int($entry['expires'] ?? null) || $entry['expires'] <= time() || !is_string($entry['path'] ?? null))
            return null;

        return self::confinedPath($entry['path'], $kind);
    }

    private static function validKey(?string $key) : bool
    {
        return $key !== null && preg_match('/^[a-zA-Z0-9]{16}$/D', $key) === 1;
    }

    /** Reject symlinks and paths outside the fixed staging directory, including malformed session state. */
    private static function confinedPath(string $path, string $kind) : ?string
    {
        $pattern = match ($kind)
        {
            'pending' => ScreenshotMgr::PATH_PENDING,
            'screenshot' => ScreenshotMgr::PATH_TEMP,
            'avatar' => AvatarMgr::PATH_TEMP,
            default => null
        };
        if (!$pattern || !str_ends_with($path, '.jpg') || is_link($path) || !is_file($path))
            return null;
        $directory = realpath(dirname($pattern));
        $resolved = realpath($path);
        if (!$directory || !$resolved || dirname($resolved) !== $directory || str_ends_with($resolved, '_original.jpg'))
            return null;

        return $resolved;
    }
}
