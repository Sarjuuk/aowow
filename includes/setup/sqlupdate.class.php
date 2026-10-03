<?php

namespace Aowow;

if (!defined('AOWOW_REVISION') || !CLI)
    die('illegal access');

/** SQL is trusted deployment input. The journal prevents automatic replay after partial DDL. */
final class SqlUpdate
{
    public const string JOURNAL_DDL = 'CREATE TABLE IF NOT EXISTS ::sql_update_journal (
        `date` int unsigned NOT NULL, `part` tinyint unsigned NOT NULL,
        `checksum` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
        `status` varchar(16) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
        `statements` int unsigned NOT NULL DEFAULT 0,
        PRIMARY KEY (`date`, `part`)
    ) ENGINE=InnoDB';

    private static function lockName(DibiConnection $db) : string
    {
        return 'aowow.update.'.substr(hash('sha256', $db->query('SELECT DATABASE()')->fetchSingle().'|'.($db->getConfig('substitutes')[''] ?? '')), 0, 48);
    }

    // Connection-scoped locks survive DDL commits; callers hold the lease through maintenance restoration.
    public static function acquire(DibiConnection $db) : string
    {
        $name = self::lockName($db);
        if ((int)$db->query('SELECT GET_LOCK(%s, 0)', $name)->fetchSingle() !== 1)
            throw new \RuntimeException('Another setup/update command holds the database lock.');
        return $name;
    }

    public static function release(DibiConnection $db, string $name) : void
    {
        if ((int)$db->query('SELECT RELEASE_LOCK(%s)', $name)->fetchSingle() !== 1)
            throw new \RuntimeException('Database update lock could not be released.');
    }

    /** Split ordinary MySQL statements, preserving quoted semicolons and executable comments. */
    public static function statements(string $sql) : array
    {
        $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql);
        $out = [];
        $buffer = '';
        $quote = '';
        $length = strlen($sql);
        for ($i = 0; $i < $length; $i++)
        {
            $c = $sql[$i];
            if ($quote)
            {
                $buffer .= $c;
                if ($c === '\\' && $quote !== '`' && $i + 1 < $length)
                    $buffer .= $sql[++$i];
                else if ($c === $quote)
                {
                    if ($i + 1 < $length && $sql[$i + 1] === $quote)
                        $buffer .= $sql[++$i];
                    else
                        $quote = '';
                }
                continue;
            }
            if ($c === "'" || $c === '"' || $c === '`')
            {
                $quote = $c;
                $buffer .= $c;
            }
            else if ($c === '#' || ($c === '-' && substr($sql, $i, 2) === '--' && ($i + 2 === $length || ord($sql[$i + 2]) <= 32)))
            {
                while ($i < $length && $sql[$i] !== "\n") $i++;
                $buffer .= "\n";
            }
            else if (substr($sql, $i, 2) === '/*')
            {
                $end = strpos($sql, '*/', $i + 2);
                if ($end === false) throw new \RuntimeException('Unterminated SQL comment.');
                $buffer .= substr($sql, $i, 3) === '/*!' || substr($sql, $i, 4) === '/*M!' ? substr($sql, $i, $end + 2 - $i) : ' ';
                $i = $end + 1;
            }
            else if ($c === ';')
            {
                if (trim($buffer) !== '') $out[] = trim($buffer);
                $buffer = '';
            }
            else
                $buffer .= $c;
        }
        if ($quote) throw new \RuntimeException('Unterminated SQL quote.');
        if (trim($buffer) !== '') $out[] = trim($buffer);

        foreach ($out as $statement)
        {
            // Reject session/transaction controls that could undermine durable accounting or lexer assumptions.
            $plain = preg_replace('~/\*(?:!|M!)\d*\s*(.*?)\*/~s', '$1', $statement);
            if (preg_match('/^(?:DELIMITER|BEGIN|START\s+TRANSACTION|COMMIT|ROLLBACK|SAVEPOINT|RELEASE\s+SAVEPOINT|XA|LOCK\s+TABLES|UNLOCK\s+TABLES)\b/i', $plain) ||
                (preg_match('/^SET\b/i', $plain) && preg_match('/\b(?:autocommit|sql_mode|transaction)\b/i', $plain)))
                throw new \RuntimeException('Unsupported SQL session/transaction control.');
        }
        return $out;
    }

    private static function version(DibiConnection $db, bool $forUpdate = false) : array
    {
        $rows = $db->query('SELECT `date`, `part`, `sql`, `build` FROM ::dbversion'.($forUpdate ? ' FOR UPDATE' : ''))->fetchAll();
        if (count($rows) !== 1 || !ctype_digit((string)$rows[0]->date) || !ctype_digit((string)$rows[0]->part))
            throw new \RuntimeException('Expected exactly one valid database version row.');
        return (array)$rows[0];
    }

    private static function checkMetadata(DibiConnection $db) : void
    {
        foreach (['dbversion', 'sql_update_journal'] as $table)
        {
            $name = ($db->getConfig('substitutes')[''] ?? '').$table;
            if (strtoupper((string)$db->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s', $name)->fetchSingle()) !== 'INNODB')
                throw new \RuntimeException('Update metadata must use InnoDB.');
        }
        $columns = $db->query('SHOW COLUMNS FROM ::sql_update_journal')->fetchAssoc('Field');
        foreach (['date' => 'int unsigned', 'part' => 'tinyint unsigned', 'checksum' => 'char(64)', 'status' => 'varchar(16)', 'statements' => 'int unsigned'] as $name => $type)
            if (!isset($columns[$name]) || $columns[$name]->Null !== 'NO' || preg_replace('/^(tinyint|int)\(\d+\)/', '$1', $columns[$name]->Type) !== $type)
                throw new \RuntimeException('Invalid update journal columns.');
        $keys = $db->query("SHOW INDEX FROM ::sql_update_journal WHERE Key_name = 'PRIMARY'")->fetchAll();
        if (array_map(fn($row) => $row->Column_name, $keys) !== ['date', 'part'])
            throw new \RuntimeException('Invalid update journal primary key.');
        if ((int)$db->query("SELECT COUNT(*) FROM ::sql_update_journal WHERE `status` IS NULL OR `status` <> 'applied'")->fetchSingle())
            throw new \RuntimeException('Unfinished migration journal: restore or reconcile before updating.');
    }

    /** Only the final metadata transaction rolls back; earlier DDL/DML may already be durable. */
    public static function apply(DibiConnection $db, string $directory = 'setup/sql/updates') : array
    {
        $lease = self::acquire($db);
        $file = 'preflight';
        $index = 0;
        $transaction = false;
        try
        {
            if ((int)$db->query('SELECT @@autocommit')->fetchSingle() !== 1 ||
                preg_match('/\b(?:NO_BACKSLASH_ESCAPES|ANSI_QUOTES)\b/', (string)$db->query('SELECT @@sql_mode')->fetchSingle()))
                throw new \RuntimeException('Unsupported SQL session configuration.');
            $version = self::version($db);
            if (!is_dir($directory)) throw new \RuntimeException('SQL update directory is missing.');
            $journal = ($db->getConfig('substitutes')[''] ?? '').'sql_update_journal';
            if (!(int)$db->query('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s', $journal)->fetchSingle())
                $db->query(self::JOURNAL_DDL);                // schema owners may pre-provision it without granting CLI CREATE
            self::checkMetadata($db);
            $files = glob($directory.'/*.sql');
            if ($files === false) throw new \RuntimeException('Cannot enumerate SQL updates.');
            sort($files, SORT_STRING);
            foreach ($files as $path)
            {
                $name = basename($path);
                $index = 0;
                if (!preg_match('/^(\d{10})_(\d{2})\.sql$/D', $name, $match)) continue;
                $file = $name;
                [$date, $part] = [(int)$match[1], (int)$match[2]];
                if ([$date, $part] <= [(int)$version['date'], (int)$version['part']]) continue;
                $contents = file_get_contents($path);
                if ($contents === false) throw new \RuntimeException('Cannot read SQL update.');
                $statements = self::statements($contents);
                if (!$statements) throw new \RuntimeException('Empty SQL update.');
                $checksum = hash('sha256', $contents);
                $db->query("INSERT INTO ::sql_update_journal (`date`, `part`, `checksum`, `status`) VALUES (%i, %i, %s, 'running')", $date, $part, $checksum);
                if ($db->getAffectedRows() !== 1) throw new \RuntimeException('Journal start was not recorded.');
                foreach ($statements as $statement)
                {
                    $index++;
                    $db->nativeQuery($statement);            // raw SQL: percent signs are not Dibi placeholders
                    $db->query("UPDATE ::sql_update_journal SET `statements` = %i WHERE `date` = %i AND `part` = %i AND `status` = 'running'", $index, $date, $part);
                    if ($db->getAffectedRows() !== 1) throw new \RuntimeException('Journal progress was not recorded.');
                }
                $db->query('START TRANSACTION');
                $transaction = true;
                $before = self::version($db, true);
                if ([$before['date'], $before['part']] != [$version['date'], $version['part']])
                    throw new \RuntimeException('Database version changed during migration.');
                $db->query('UPDATE ::dbversion SET `date` = %i, `part` = %i', $date, $part);
                if ($db->getAffectedRows() !== 1) throw new \RuntimeException('Database version was not advanced.');
                $version = self::version($db);
                if ([(int)$version['date'], (int)$version['part']] !== [$date, $part])
                    throw new \RuntimeException('Database version verification failed.');
                $db->query("UPDATE ::sql_update_journal SET `status` = 'applied' WHERE `date` = %i AND `part` = %i AND `status` = 'running' AND `statements` = %i", $date, $part, $index);
                if ($db->getAffectedRows() !== 1) throw new \RuntimeException('Journal completion was not recorded.');
                $entry = $db->query('SELECT `status`, `statements`, `checksum` FROM ::sql_update_journal WHERE `date` = %i AND `part` = %i', $date, $part)->fetch();
                if (!$entry || $entry->status !== 'applied' || (int)$entry->statements !== $index || $entry->checksum !== $checksum)
                    throw new \RuntimeException('Journal completion verification failed.');
                $db->query('COMMIT');
                $transaction = false;
                CLI::write('[update] '.$file.': '.$index.' statements applied', CLI::LOG_OK);
            }
            return self::version($db);
        }
        catch (\Throwable $e)
        {
            if ($transaction)
                try { $db->query('ROLLBACK'); } catch (\Throwable) { }
            // Never expose SQL, exception messages or database values in process output.
            throw new \RuntimeException('[update] failed at '.$file.' statement '.$index.' (code '.(int)$e->getCode().'); inspect the version/journal and restore or reconcile before retrying.', 0, $e);
        }
        finally
        {
            self::release($db, $lease);
        }
    }
}
