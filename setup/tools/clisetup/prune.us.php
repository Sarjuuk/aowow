<?php

namespace Aowow;

if (!defined('AOWOW_REVISION') || !CLI)
    die('illegal access');

CLISetup::registerUtility(new class extends UtilityScript
{
    public int $argvFlags = CLISetup::ARGV_OPTIONAL;
    public int $optGroup = CLISetup::OPT_GRP_UTIL;
    public const string COMMAND = 'prune';
    public const string DESCRIPTION = 'Inspect expired staging/cache/logs; use --prune=apply for bounded deletion.';
    public const array REQUIRED_DB = [DB_AOWOW];

    public function run(array &$args) : bool
    {
        $mode = CLISetup::getOpt('prune');
        if ($mode !== true && $mode !== 'apply') return false;
        $apply = $mode === 'apply';
        $db = DB::holdConnection(DB_AOWOW);
        $lease = null;
        $lock = null;
        try
        {
            $lease = SqlUpdate::acquire($db);                // avoid update/schema races without changing site maintenance
            if (!CacheEnvelope::writeDirectory('cache/maintenance')) return false;
            if (is_link('cache/maintenance') || is_link('cache/maintenance/cursor.json') || is_link('cache/maintenance/lock')) return false;
            $mask = umask(0077);
            try { $lock = fopen('cache/maintenance/lock', 'c+b'); }
            finally { umask($mask); }
            if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) return false;
            $cursor = [];
            if (is_file('cache/maintenance/cursor.json'))
            {
                $saved = file_get_contents('cache/maintenance/cursor.json', false, null, 0, 1024);
                $cursor = json_decode($saved, true, 8, JSON_THROW_ON_ERROR);
                if (!is_array($cursor)) return false;
            }
            $cacheRoot = Cfg::get('CACHE_DIR') ?: 'cache/template';
            foreach (Retention::database($apply) as $table => $count)
                CLI::write('[prune] '.$table.': '.$count.($apply ? ' removed' : ' eligible (maximum batch)'));
            foreach (Retention::files($apply, $cacheRoot, $cursor) as $kind => $counts)
                CLI::write('[prune] '.$kind.': '.$counts['eligible'].' eligible, '.$counts['removed'].' removed; '.($counts['more'] ? 'more remain' : 'scan complete'));
            if ($apply && !Util::writeFile('cache/maintenance/cursor.json', json_encode($cursor, JSON_THROW_ON_ERROR), 0600)) return false;
            return true;
        }
        finally
        {
            if (is_resource($lock)) { flock($lock, LOCK_UN); fclose($lock); }
            if ($lease !== null) SqlUpdate::release($db, $lease);
            DB::releaseConnection(DB_AOWOW);
        }
    }
});
