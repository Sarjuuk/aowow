<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/** Reservations are durable and conservative: failed/uncertain work is charged, never automatically refunded. */
final class ContributionBudget
{
    public const int OK = 0;
    public const int BLOCKED = 1;
    public const int FAILED = 2;
    private static int $last = self::FAILED;

    // action => daily requests, conservative retained-byte charge, shared storage pool
    public const array POLICY = [
        'comment' => [100, 4096, 'text'], 'reply' => [200, 4096, 'text'], 'guide' => [60, 8192, 'text'],
        'screenshot' => [50, 67108864, 'upload'], 'avatar' => [10, 33554432, 'upload'],
        'guide-image' => [50, 10485760, 'upload'], 'video' => [50, 8192, 'text'], 'video-complete' => [50, 8192, 'text']
    ];
    private const array CAPACITY = ['text' => [268435456, 4294967296], 'upload' => [4294967296, 34359738368]];

    public static function error() : string
    {
        return Lang::main(self::$last === self::BLOCKED ? 'contributionLimit' : 'intError');
    }

    /** Reserve global/account daily work and cumulative retained bytes atomically across sessions/workers. */
    public static function reserve(string $action, int $bytes = 0) : bool
    {
        self::$last = self::FAILED;
        if (!isset(self::POLICY[$action]) || User::$id <= 0 || $bytes < 0 || $bytes > 1048576) return false;
        [$limit, $charge, $pool] = self::POLICY[$action];
        $charge += $bytes;
        $db = DB::Aowow();
        $started = false;
        $committed = false;
        try
        {
            $started = true;
            $db->query('START TRANSACTION');
            foreach ([0 => 10000, User::$id => $limit] as $owner => $maximum)
            {
                $db->query('INSERT INTO ::contribution_budget (`owner`, `bucket`, `used`, `expires`) VALUES (%i, %s, 0, UNIX_TIMESTAMP() + %i) ON DUPLICATE KEY UPDATE `used` = IF(`expires` <= UNIX_TIMESTAMP(), 0, `used`), `expires` = IF(`expires` <= UNIX_TIMESTAMP(), UNIX_TIMESTAMP() + %i, `expires`)', $owner, $action, DAY, DAY);
                $db->query('UPDATE ::contribution_budget SET `used` = `used` + 1 WHERE `owner` = %i AND `bucket` = %s AND `used` < %i', $owner, $action, $maximum);
                if ($db->getAffectedRows() !== 1) { self::$last = self::BLOCKED; return false; }
            }
            foreach ([0 => self::CAPACITY[$pool][1], User::$id => self::CAPACITY[$pool][0]] as $owner => $maximum)
            {
                $db->query('INSERT INTO ::contribution_budget (`owner`, `bucket`, `used`, `expires`) VALUES (%i, %s, 0, 0) ON DUPLICATE KEY UPDATE `owner` = `owner`', $owner, 'bytes-'.$pool);
                $db->query('UPDATE ::contribution_budget SET `used` = `used` + %i WHERE `owner` = %i AND `bucket` = %s AND `expires` = 0 AND `used` <= %i', $charge, $owner, 'bytes-'.$pool, $maximum - $charge);
                if ($db->getAffectedRows() !== 1) { self::$last = self::BLOCKED; return false; }
            }
            $db->query('COMMIT');
            $committed = true;
            self::$last = self::OK;
            return true;
        }
        catch (\Throwable) { self::$last = self::FAILED; return false; }
        finally
        {
            if ($started && !$committed)
                try { $db->query('ROLLBACK'); } catch (\Throwable) { }
        }
    }
}
