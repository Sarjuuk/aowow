<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/** Completes local password changes without separating token consumption from session revocation. */
final class PasswordRecovery
{
    public const int OK = 0;
    public const int INVALID_PASSWORD = 1;
    public const int INVALID_TOKEN = 2;
    public const int SAME_PASSWORD = 3;
    public const int FAILED = 4;

    public static function reset(string $token, string $email, #[\SensitiveParameter] string $password) : int
    {
        if (!Util::validatePassword($password))
            return self::INVALID_PASSWORD;

        $db = DB::Aowow();
        $account = $db->selectRow('SELECT `id`, `passHash` FROM ::account WHERE `token` = %s AND `email` = %s AND `status` = %i AND `statusTimer` > UNIX_TIMESTAMP()', $token, $email, ACC_STATUS_RECOVER_PASS);
        if (!$account)
            return self::INVALID_TOKEN;

        if (PasswordBudget::reserve((int)$account['id']) !== PasswordBudget::OK)
            return self::FAILED;

        if (User::verifyCrypt($password, $account['passHash']))
            return self::SAME_PASSWORD;

        // The expensive hash precedes the lock; consume() rechecks both credentials and expiry afterward.
        return self::consume((int)$account['id'], $token, ACC_STATUS_RECOVER_PASS, $email, User::hashCrypt($password), $account['passHash']);
    }

    public static function confirm(string $token) : int
    {
        $account = DB::Aowow()->selectRow('SELECT `id` FROM ::account WHERE `token` = %s AND `status` = %i AND `statusTimer` > UNIX_TIMESTAMP()', $token, ACC_STATUS_CHANGE_PASS);
        if (!$account)
            return self::INVALID_TOKEN;

        return self::consume((int)$account['id'], $token, ACC_STATUS_CHANGE_PASS);
    }

    /** Lock one account; any failed write rolls back its new hash, cleared pending state, and revoked sessions. */
    private static function consume(int $id, string $token, int $status, ?string $email = null, ?string $newHash = null, ?string $oldHash = null) : int
    {
        $db = DB::Aowow();
        if (!is_int($db->qry('START TRANSACTION')))
            return self::FAILED;

        $committed = false;
        try
        {
            $account = $db->selectRow('SELECT `passHash`, `updateValue` FROM ::account WHERE `id` = %i FOR UPDATE', $id);
            if (!$account)
                return self::FAILED;

            if ($oldHash !== null && !hash_equals($oldHash, $account['passHash']))
                return self::INVALID_TOKEN;

            $newHash ??= $account['updateValue'];
            if (!is_string($newHash) || (password_get_info($newHash)['algoName'] ?? '') !== 'bcrypt')
                return self::INVALID_TOKEN;

            // Expiry and bearer authority are tested at the write, including when the lock had to wait.
            $changed = $db->qry('UPDATE ::account SET `passHash` = %s, `status` = %i, `statusTimer` = 0, `token` = "", `updateValue` = "" WHERE `id` = %i AND `token` = %s AND `status` = %i AND `statusTimer` > UNIX_TIMESTAMP() %if AND `email` = %s %end',
                $newHash, ACC_STATUS_NONE, $id, $token, $status, $email !== null, $email
            );
            if ($changed !== 1)
                return $changed === 0 ? self::INVALID_TOKEN : self::FAILED;

            // Zero active sessions is valid; null means the revocation failed and must roll back the password.
            if (!is_int($db->qry('UPDATE ::account_sessions SET `status` = %i, `touched` = UNIX_TIMESTAMP() WHERE `userId` = %i AND `status` = %i', SESSION_FORCED_LOGOUT, $id, SESSION_ACTIVE)))
                return self::FAILED;

            if (!is_int($db->qry('COMMIT')))
                return self::FAILED;

            $committed = true;
        }
        catch (\Throwable)
        {
            return self::FAILED;
        }
        finally
        {
            if (!$committed)
                $db->qry('ROLLBACK');
        }

        // Confirmation/reset never keeps an old authenticated browser, even when it submitted the request.
        if (User::$id === $id)
            User::destroy();

        return self::OK;
    }
}
