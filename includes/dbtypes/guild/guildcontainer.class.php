<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


abstract class GuildContainer extends DBTypeContainer implements IProfiler
{
    public static int $dbType = Type::GUILD;

    abstract public static function entityObj() : string;
}

class RemoteGuildContainer extends GuildContainer
{
    public function initializeLocalEntries() : void
    {
        $data = [];
        foreach ($this->iterate() as $guid => $entry)
        {
            $data['realm'][$guid]     = $entry->realmName;
            $data['realmGUID'][$guid] = $entry->realmGUID;
            $data['name'][$guid]      = $entry->name;
            $data['nameUrl'][$guid]   = Profiler::urlize($entry->name);
            $data['stub'][$guid]      = 1;
        }

        // basic guild data
        DB::Aowow()->qry('INSERT INTO ::profiler_guild %m ON DUPLICATE KEY UPDATE `id` = `id`', $data);

        // merge back local ids
        $localIds = DB::Aowow()->selectCol('SELECT CONCAT(`realm`, ":", `realmGUID`) AS ARRAY_KEY, `id` FROM ::profiler_guild WHERE `realm` IN %in AND `realmGUID` IN %in',
            $data['realm'], $data['realmGUID']
        );

        foreach ($this->iterate() as $guid => &$_curTpl)
            if (isset($localIds[$guid]))
                $_curTpl['id'] = $localIds[$guid];
    }

    /**
     * iterate over fetched sets
     *
     * @return \Generator<string, RemoteGuildEntry> key => guild template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?RemoteGuildEntry
     */
    public function getEntry(?int $key = null) : ?RemoteGuildEntry
    {
        return parent::getEntry($key);
    }

    public function import(DBTypeEntry ...$entries) : void
    {
        foreach (array_filter($entries, fn($x) => !$x->error) as $e)
            if (is_a($e, RemoteGuildEntry::class))
                $this->sets[$e->subjectGUID] = $e;

        $this->reset();
    }

    public static function entityObj() : string
    {
        return RemoteGuildEntry::class;
    }
}

class LocalGuildContainer extends GuildContainer
{
    /**
     * iterate over fetched sets
     *
     * @return \Generator<string, LocalGuildEntry> key => guild template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?LocalGuildEntry
     */
    public function getEntry(?int $key = null) : ?LocalGuildEntry
    {
        return parent::getEntry($key);
    }

    public static function entityObj() : string
    {
        return LocalGuildEntry::class;
    }
}

?>
