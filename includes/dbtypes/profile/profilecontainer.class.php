<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


abstract class ProfileContainer extends DBTypeContainer implements IProfiler
{
    public static int $dbType = Type::PROFILE;

    /**
     * @param int $addMask exclusive switch what data to expose to javascript
     *  - GLOBALINFO_PROFILE - returns custom profile data
     *  - GLOBALINFO_CHARACTER - returns character data
     *
     * @return array<int, array<int, int|array>[]>[] [type => [typeId => placeholder or data]]
     */
    public function getJSGlobals(int $addMask = GLOBALINFO_SELF) : array
    {
        return parent::getJSGlobals($addMask);
    }

    abstract public static function entityObj() : string;
}

class RemoteProfileContainer extends ProfileContainer
{
    public function __construct(?array $conditions = [], array $miscData = [])
    {
        if (!$targetDBs = Profiler::getRealmDBs($miscData['rg'] ?? null, $miscData['sv'] ?? null))
        {
            trigger_error(__METHOD__.' - cannot access any realm.', E_USER_WARNING);
            return;
        }

        parent::__construct($conditions, $miscData, $targetDBs);

        $talentSpells = $talentLookup = $rnItr = [];

        // post processing
        foreach ($this->iterate() as $profile)
        {
            // talent points pre
            $talentLookup[$profile->realmId][$profile->realmGUID] = [];
            $talentSpells[] = $profile->class;

            // char is pending rename
            if ($profile->renamePending)
                $rnItr[$profile->realmId][$profile->name] = null;
        }

        foreach ($talentLookup as $realm => $chars)
            $talentLookup[$realm] = DB::Characters($realm)->selectCol('SELECT `guid` AS ARRAY_KEY, `spell` AS ARRAY_KEY2, `talentGroup` FROM character_talent ct WHERE `guid` IN %in', array_keys($chars));

        $talentSpells = DB::Aowow()->selectAssoc('SELECT `spell` AS ARRAY_KEY, `tab` + 1 AS "0", `rank` AS "1" FROM ::talents WHERE `class` IN %in', array_unique($talentSpells));

        foreach ($rnItr as $realmId => $names)
            $rnItr[$realmId] = RemoteProfileEntry::fetchRenameItrs($realmId, ...array_keys($names));

        foreach ($this->iterate() as $guid => $profile)
        {
            // talent points post
            $tree1 = $tree2 = $tree3 = 0;
            $talents = array_filter($talentLookup[$profile->realmId][$profile->realmGUID] ?? [], fn($x) => $profile->activespec == $x);
            foreach (array_intersect_key($talentSpells, $talents) as [$tab, $rank])
                ${'tree' . $tab} += $rank;

            $profile->setTalentDistribution($tree1, $tree2, $tree3);
            $profile->setRenameItr($rnItr[$profile->realmId]);
        }
    }

    /**
     * iterate over fetched sets
     *
     * @return \Generator<string, RemoteProfileEntry> key => character template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?RemoteProfileEntry
     */
    public function getEntry(?int $key = null) : ?RemoteProfileEntry
    {
        return parent::getEntry($key);
    }

    /**
     * @param int $addInfoMask
     * * `0x0100 - LISTVIEWINFO_PROFILE`: only include custom profiles
     * * `0x0200 - LISTVIEWINFO_CHARACTER`: only include genuine characters
     * * `0x0800 - LISTVIEWINFO_ARENA`: additional arena stats
     * * `0x1000 - LISTVIEWINFO_USER`: incuded published state
     */
    public function getListviewData(int $addInfoMask = 0, array $reqCols = []) : array
    {
        $data = [];

        foreach ($this->iterate() as $id => $entry)
            if ($row = $entry->getListviewRow($addInfoMask, $reqCols))
            {
                // not wanted on server list
                unset($row['published']);
                $data[$id] = $row;
            }

        return $data;
    }

    public function initializeLocalEntries() : void
    {
        $baseData = $guildData = [];
        foreach ($this->iterate() as $guid => $entry)
        {
            $realmId   = $entry->realmId;
            $guildGUID = $entry->guild;

            $baseData['realm'][$guid]     = $realmId;
            $baseData['realmGUID'][$guid] = $entry->realmGUID;
            $baseData['name'][$guid]      = $entry->name;
            $baseData['renameItr'][$guid] = $entry->renameItr;
            $baseData['race'][$guid]      = $entry->race;
            $baseData['class'][$guid]     = $entry->class;
            $baseData['level'][$guid]     = $entry->level;
            $baseData['gender'][$guid]    = $entry->gender;
            $baseData['guild'][$guid]     = $guildGUID ?: null;
            $baseData['guildrank'][$guid] = $guildGUID ? $entry->guildrank : null;
            $baseData['stub'][$guid]      = 1;

            if ($guildGUID)
            {
                $guildData['realm'][$realmId.'-'.$guildGUID]     = $realmId;
                $guildData['realmGUID'][$realmId.'-'.$guildGUID] = $guildGUID;
                $guildData['name'][$realmId.'-'.$guildGUID]      = $entry->guildname;
                $guildData['nameUrl'][$realmId.'-'.$guildGUID]   = Profiler::urlize($entry->guildname);
                $guildData['stub'][$realmId.'-'.$guildGUID]      = 1;
            }
        }

        // basic guild data (satisfying table constraints)
        if ($guildData)
        {
            DB::Aowow()->qry('INSERT INTO ::profiler_guild %m ON DUPLICATE KEY UPDATE `id` = `id`', $guildData);

            // merge back local ids
            $localGuilds = DB::Aowow()->selectCol('SELECT `realm` AS ARRAY_KEY, `realmGUID` AS ARRAY_KEY2, `id` FROM ::profiler_guild WHERE `realm` IN %in AND `realmGUID` IN %in',
                $guildData['realm'], $guildData['realmGUID']
            );

            foreach ($baseData['guild'] as $i => &$g)
                $g = $localGuilds[$baseData['realm'][$i]][$baseData['guild'][$i]] ?? null;
        }

        // basic char data (enough for tooltips)
        if ($baseData)
        {
            // this could have been an INSERT ON DUPLICATE KEY UPDATE if MariaDB and MySQL would behave for once!
            $insertOrUpdate = $baseData;
            $existing = DB::Aowow()->selectAssoc('SELECT `realm` AS ARRAY_KEY, `realmGUID` AS ARRAY_KEY2, 1 FROM ::profiler_profiles WHERE `realm` IN %in AND `realmGUID` IN %in', $insertOrUpdate['realm'], $insertOrUpdate['realmGUID']);
            foreach ($insertOrUpdate['realm'] as $guid => $_)
            {
                if (!isset($existing[$insertOrUpdate['realm'][$guid]][$insertOrUpdate['realmGUID'][$guid]]))
                    continue;

                // ... ON DUPLICATE KEY UPDATE
                DB::Aowow()->qry('UPDATE ::profiler_profiles SET `name` = %s, `renameItr` = %i WHERE `realm` = %i AND `realmGUID` = %i', $insertOrUpdate['name'][$guid], $insertOrUpdate['renameItr'][$guid], $insertOrUpdate['realm'][$guid], $insertOrUpdate['realmGUID'][$guid]);
                foreach($insertOrUpdate as $col => $__)
                    unset($insertOrUpdate[$col][$guid]);
            }

            // INSERT ...
            if (current($insertOrUpdate))
                DB::Aowow()->qry('INSERT INTO ::profiler_profiles %m', $insertOrUpdate);

            // merge back local ids
            $localData = DB::Aowow()->selectAssoc('SELECT `realm` AS ARRAY_KEY, `realmGUID` AS ARRAY_KEY2, `id`, `gearscore` FROM ::profiler_profiles WHERE `custom` = 0 AND `realm` IN %in AND `realmGUID` IN %in',
                $baseData['realm'], $baseData['realmGUID']
            );

            foreach ($this->iterate() as $guid => $profile)
            {
                if (!isset($localData[$profile->realmId][$profile->realmGUID]))
                    trigger_error(__METHOD__.' - local entry not generated for char with realm #'.$profile->realmId.' realmGUID #'.$profile->realmGUID, E_USER_WARNING);

                // still call this fn or the readonly properties remain uninitialized
                // $profile->amendLocalData($localData[$profile->realmId][$profile->realmGUID] ?? []);
            }
        }
    }

    public function import(DBTypeEntry ...$entries) : void
    {
        foreach (array_filter($entries, fn($x) => !$x->error) as $e)
            if (is_a($e, RemoteProfileEntry::class))
                $this->sets[$e->subjectGUID] = $e;

        $this->reset();
    }

    public static function entityObj() : string
    {
        return RemoteProfileEntry::class;
    }
}

class LocalProfileContainer extends ProfileContainer
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
     * @return \Generator<string, LocalProfileEntry> key => character template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?LocalProfileEntry
     */
    public function getEntry(?int $key = null) : ?LocalProfileEntry
    {
        return parent::getEntry($key);
    }

    public static function entityObj() : string
    {
        return LocalProfileEntry::class;
    }
}

?>
