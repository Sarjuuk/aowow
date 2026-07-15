<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


abstract class GuildContainer extends DBTypeContainer implements IListview
{
    public static int $dbType = Type::GUILD;

    public function __construct(?array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

    }

    /**
     * iterate over fetched sets
     *
     * @return \Generator<string, GuildEntry> key => arena team template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?GuildEntry
     */
    public function getEntry(null|string|int $key = null) : ?GuildEntry
    {
        return parent::getEntry($key);
    }
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
}

class LocalGuildContainer extends GuildContainer
{

}

?>
