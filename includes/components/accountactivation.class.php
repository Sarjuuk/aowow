<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/** Activation authority is consumed once; signin prefill is separate, session-local display data. */
final class AccountActivation
{
    public const int OK = 0;
    public const int INVALID = 1;
    public const int BLOCKED = 2;
    public const int FAILED = 3;

    public static function activate(string $token, ?string $ip) : array
    {
        if (!$ip)
            return ['status' => self::FAILED];

        $db = DB::Aowow();
        $account = $db->selectRow('SELECT `id` FROM ::account WHERE `token` = %s AND `status` = %i AND `statusTimer` > UNIX_TIMESTAMP()', $token, ACC_STATUS_NEW);
        if (!$account)
            return ['status' => self::INVALID];

        if (!is_int($db->qry('START TRANSACTION')))
            return ['status' => self::FAILED];

        $committed = false;
        try
        {
            $account = $db->selectRow('SELECT `id`, `login` FROM ::account WHERE `id` = %i FOR UPDATE', $account['id']);
            if (!$account)
                return ['status' => self::INVALID];

            // Check expiry after any lock wait; remove only PENDING, preserving other account groups.
            $changed = $db->qry('UPDATE ::account SET `status` = %i, `statusTimer` = 0, `token` = "", `updateValue` = "", `userGroups` = `userGroups` & %i WHERE `id` = %i AND `token` = %s AND `status` = %i AND `statusTimer` > UNIX_TIMESTAMP()',
                ACC_STATUS_NONE, 0xFFFF ^ U_GROUP_PENDING, $account['id'], $token, ACC_STATUS_NEW
            );
            if ($changed !== 1)
                return ['status' => $changed === 0 ? self::INVALID : self::FAILED];

            $temporary = $db->selectRow('SELECT `expires` FROM ::account_sessions WHERE `userId` = %i AND `sessionId` = %s', $account['id'], $token);
            if (!is_int($db->qry('DELETE FROM ::account_sessions WHERE `userId` = %i AND `sessionId` = %s', $account['id'], $token)) ||
                !is_int($db->qry('REPLACE INTO ::account_bannedips (`ip`, `type`, `count`, `unbanDate`) VALUES (%s, %i, %i, UNIX_TIMESTAMP() + %i)',
                    $ip, IP_BAN_TYPE_REGISTRATION_ATTEMPT, Cfg::get('ACC_FAILED_AUTH_COUNT') + 1, Cfg::get('ACC_FAILED_AUTH_BLOCK'))) ||
                !is_int($db->qry('COMMIT')))
                return ['status' => self::FAILED];

            $committed = true;
        }
        catch (\Throwable)
        {
            return ['status' => self::FAILED];
        }
        finally
        {
            if (!$committed)
                $db->qry('ROLLBACK');
        }

        return ['status' => self::OK, 'login' => $account['login'], 'rememberMe' => $temporary && !$temporary['expires']];
    }

    /** Rotate pending authority and its deadline with the attempt budget; mail is sent only after commit. */
    public static function resend(string $email, ?string $ip) : array
    {
        if (!$ip || ($decay = (int)Cfg::get('ACC_CREATE_SAVE_DECAY')) <= 0)
            return ['status' => self::FAILED];

        $db = DB::Aowow();
        $account = $db->selectRow('SELECT `id` FROM ::account WHERE `email` = %s AND `status` = %i', $email, ACC_STATUS_NEW);
        if (!$account)
            return ['status' => self::INVALID];

        $token = Util::createHash();
        if (!is_int($db->qry('START TRANSACTION')))
            return ['status' => self::FAILED];

        $committed = false;
        try
        {
            $account = $db->selectRow('SELECT `id`, `token` FROM ::account WHERE `id` = %i FOR UPDATE', $account['id']);
            if (!$account)
                return ['status' => self::INVALID];

            $budget = $db->selectRow('SELECT `count`, `unbanDate` > UNIX_TIMESTAMP() AS "active" FROM ::account_bannedips WHERE `ip` = %s AND `type` = %i FOR UPDATE', $ip, IP_BAN_TYPE_REGISTRATION_ATTEMPT);
            if ($budget && $budget['active'] && $budget['count'] > Cfg::get('ACC_FAILED_AUTH_COUNT'))
                return ['status' => self::BLOCKED];

            $changed = $db->qry('UPDATE ::account SET `token` = %s, `statusTimer` = UNIX_TIMESTAMP() + %i WHERE `id` = %i AND `email` = %s AND `status` = %i', $token, $decay, $account['id'], $email, ACC_STATUS_NEW);
            if ($changed !== 1)
                return ['status' => $changed === 0 ? self::INVALID : self::FAILED];

            // Move only the signup metadata row; real browser sessions remain unchanged.
            if (!is_int($db->qry('UPDATE ::account_sessions SET `sessionId` = %s WHERE `userId` = %i AND `sessionId` = %s', $token, $account['id'], $account['token'])) ||
                !is_int($db->qry('INSERT INTO ::account_bannedips (`ip`, `type`, `count`, `unbanDate`) VALUES (%s, %i, %i, UNIX_TIMESTAMP() + %i) ON DUPLICATE KEY UPDATE `count` = `count` + 1, `unbanDate` = UNIX_TIMESTAMP() + %i',
                    $ip, IP_BAN_TYPE_REGISTRATION_ATTEMPT, Cfg::get('ACC_FAILED_AUTH_COUNT') + 1, Cfg::get('ACC_FAILED_AUTH_BLOCK'), Cfg::get('ACC_FAILED_AUTH_BLOCK'))) ||
                !is_int($db->qry('COMMIT')))
                return ['status' => self::FAILED];

            $committed = true;
        }
        catch (\Throwable)
        {
            return ['status' => self::FAILED];
        }
        finally
        {
            if (!$committed)
                $db->qry('ROLLBACK');
        }

        return ['status' => self::OK, 'token' => $token];
    }

    /** A consumed activation key only selects this short-lived prefill; it never authenticates a browser. */
    public static function signinPrefill(?string $key) : ?array
    {
        $prefill = $_SESSION['activationSignin'] ?? null;
        if (!$prefill)
            return null;

        if (!is_array($prefill) || !is_string($prefill['key'] ?? null) || !is_string($prefill['login'] ?? null) ||
            !is_bool($prefill['rememberMe'] ?? null) || !is_int($prefill['expires'] ?? null) || $prefill['expires'] <= time())
        {
            unset($_SESSION['activationSignin']);
            return null;
        }

        if (!is_string($key) || !hash_equals($prefill['key'], $key))
            return null;

        unset($_SESSION['activationSignin']);
        return [$prefill['login'], $prefill['rememberMe']];
    }
}
