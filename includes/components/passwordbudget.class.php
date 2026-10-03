<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/** Durable work reservations bound local password operations across sessions and worker processes. */
final class PasswordBudget
{
    public const int OK = 0;
    public const int BLOCKED = 1;
    public const int FAILED = 2;

    public static function reserve(?int $accountId = null) : int
    {
        if (!User::$ip || !filter_var(User::$ip, FILTER_VALIDATE_IP) || ($accountId !== null && $accountId <= 0))
            return self::FAILED;

        $limit = max(1, min(100, (int)Cfg::get('ACC_FAILED_AUTH_COUNT')));
        $window = max(1, min(DAY, (int)Cfg::get('ACC_FAILED_AUTH_BLOCK')));
        $keys = $accountId === null ? [] : [['account', (string)$accountId]];
        $keys[] = ['ip', hash('sha256', inet_pton(User::$ip))];
        $db = DB::Aowow();
        $committed = false;
        $started = false;

        try
        {
            // Indexed, bounded reclamation avoids retaining inactive subjects indefinitely.
            if (!is_int($db->qry('DELETE FROM ::account_password_budget WHERE `expires` <= UNIX_TIMESTAMP() ORDER BY `expires` LIMIT 100')) ||
                !is_int($db->qry('START TRANSACTION')))
                return self::FAILED;
            $started = true;

            foreach ($keys as [$scope, $subject])
            {
                // The unique key serializes even the first reservation; active windows never slide.
                if (!is_int($db->qry('INSERT INTO ::account_password_budget (`scope`, `subject`, `count`, `expires`) VALUES (%s, %s, 0, UNIX_TIMESTAMP() + %i) ON DUPLICATE KEY UPDATE `count` = IF(`expires` <= UNIX_TIMESTAMP(), 0, `count`), `expires` = IF(`expires` <= UNIX_TIMESTAMP(), UNIX_TIMESTAMP() + %i, `expires`)',
                    $scope, $subject, $window, $window)))
                    return self::FAILED;

                $changed = $db->qry('UPDATE ::account_password_budget SET `count` = `count` + 1 WHERE `scope` = %s AND `subject` = %s AND `count` < %i AND `expires` > UNIX_TIMESTAMP()', $scope, $subject, $limit);
                if ($changed !== 1)
                    return $changed === 0 ? self::BLOCKED : self::FAILED;
            }

            if (!is_int($db->qry('COMMIT')))
                return self::FAILED;
            $committed = true;
            return self::OK;
        }
        catch (\Throwable)
        {
            return self::FAILED;
        }
        finally
        {
            if ($started && !$committed)
                $db->qry('ROLLBACK');
        }
    }
}
