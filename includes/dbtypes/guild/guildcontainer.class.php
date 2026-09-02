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
    public function __construct(?array $conditions = [], array $miscData = [])
    {
        if (!$targetDBs = Profiler::getRealmDBs($miscData['rg'] ?? null, $miscData['sv'] ?? null))
        {
            trigger_error(__METHOD__.' - cannot access any realm.', E_USER_WARNING);
            return;
        }

        parent::__construct($conditions, $miscData, $targetDBs);

        // collecting
        foreach ($this->iterate() as $profile)
        {
        }

        // processing

        // applying
        foreach ($this->iterate() as $guid => $profile)
        {
        }
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

     // foreach ($this->iterate() as $guid => $entry)
     //     if (isset($localIds[$guid]))
     //         $entry->id = $localIds[$guid];
    }

    public static function entityObj() : string
    {
        return RemoteGuildEntry::class;
    }
}

class LocalGuildContainer extends GuildContainer
{
    public function __construct(?array $conditions = [], array $miscData = [], array $targetDBs = ['Aowow'])
    {
        $realms = Profiler::getRealms();

        // graft realm selection from miscData onto conditions
        $realmIds = [];
        if (isset($miscData['sv']) && isset($realms[$miscData['sv']]))
            $realmIds = [$miscData['sv']];
        if (isset($miscData['rg']))
            $realmIds = array_merge($realmIds, array_keys(array_filter($realms, fn($x) => $x['region'] == $miscData['rg'])));

        if ($conditions && $realmIds)
            $conditions = [DB::AND, ['realm', $realmIds], $conditions];
        else if ($realmIds)
            $conditions = [['realm', $realmIds]];

        parent::__construct($conditions, $miscData, $targetDBs);
    }

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
