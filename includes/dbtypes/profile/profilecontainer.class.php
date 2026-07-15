<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


abstract class ProfileContainer extends DBTypeContainer implements IListview
{
    public static int $dbType = Type::PROFILE;

    public function __construct(?array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);
    }
}

class RemoteProfileContainer extends ProfileContainer
{
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
    public function getEntry(null|string|int $key = null) : ?RemoteProfileEntry
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

    private function whatsitsnameagain()
    {
        if ($this->error)
            return;

        $realms       = Profiler::getRealms();
        $talentSpells = [];
        $talentLookup = [];
        $distrib      = [];

        // post processing
        foreach ($this->iterate() as $guid => &$curTpl)
        {
            // battlegroup
            $curTpl['battlegroup'] = Cfg::get('BATTLEGROUP');

            // realm
            [$r, $g] = explode(':', $guid);
            if (!empty($realms[$r]))
            {
                $curTpl['realm']     = $r;
                $curTpl['realmName'] = $realms[$r]['name'];
                $curTpl['region']    = $realms[$r]['region'];
            }
            else
            {
                trigger_error('char #'.$guid.' belongs to nonexistent realm #'.$r, E_USER_WARNING);
                unset($this->templates[$guid]);
                continue;
            }

            // empty name
            if (!$curTpl['name'])
            {
                trigger_error('char #'.$guid.' on realm #'.$r.' has empty name.', E_USER_WARNING);
                unset($this->templates[$guid]);
                continue;
            }

            // temp id
            $curTpl['id'] = 0;

            // talent points pre
            $talentLookup[$r][$g] = [];
            $talentSpells[] = $curTpl['class'];
            $curTpl['activespec'] = $curTpl['activeTalentGroup'];

            // equalize distribution
            if (empty($distrib[$curTpl['realm']]))
                $distrib[$curTpl['realm']] = 1;
            else
                $distrib[$curTpl['realm']]++;

            // char is pending rename
            if ($curTpl['at_login'] & 0x1)
            {
                $this->rnItr[$curTpl['name']] ??= DB::Aowow()->selectCell('SELECT MAX(`renameItr`) FROM ::profiler_profiles WHERE `realm` = %i AND `custom` = 0 AND `name` = %s', $r, $curTpl['name']) ?: 0;

                // already saved as "pending rename"
                if ($rnItr = DB::Aowow()->selectCell('SELECT `renameItr` FROM ::profiler_profiles WHERE `realm` = %i AND `realmGUID` = %i', $r, $g))
                    $curTpl['renameItr'] = $rnItr;
                // not yet recognized: get max itr
                else
                    $curTpl['renameItr'] = ++$this->rnItr[$curTpl['name']];
            }
            else
                $curTpl['renameItr'] = 0;

            $curTpl['cuFlags'] = 0;
        }

        foreach ($talentLookup as $realm => $chars)
            $talentLookup[$realm] = DB::Characters($realm)->selectCol('SELECT `guid` AS ARRAY_KEY, `spell` AS ARRAY_KEY2, `talentGroup` FROM character_talent ct WHERE `guid` IN %in', array_keys($chars));

        $talentSpells = DB::Aowow()->selectAssoc('SELECT `spell` AS ARRAY_KEY, `tab`, `rank` FROM ::talents WHERE `class` IN %in', array_unique($talentSpells));

        // equalize subject distribution across realms
        $limit = 0;
        foreach ($conditions as $c)
            if (is_numeric($c))
                $limit = max(0, (int)$c);

        if (!$limit)                                        // int:0 means unlimited, so skip process
            $distrib = [];

        $total = array_sum($distrib);
        foreach ($distrib as &$d)
            $d = ceil($limit * $d / $total);

        foreach ($this->iterate() as $guid => &$curTpl)
        {
            if ($distrib)
            {
                if ($limit <= 0 || $distrib[$curTpl['realm']] <= 0)
                {
                    unset($this->templates[$guid]);
                    continue;
                }

                $distrib[$curTpl['realm']]--;
                $limit--;
            }

            [$r, $g] = explode(':', $guid);

            // talent points post
            $curTpl['talenttree1'] = 0;
            $curTpl['talenttree2'] = 0;
            $curTpl['talenttree3'] = 0;
            if (!empty($talentLookup[$r][$g]))
            {
                $talents = array_filter($talentLookup[$r][$g], function($v) use ($curTpl) { return $curTpl['activespec'] == $v; } );
                foreach (array_intersect_key($talentSpells, $talents) as $spell => $data)
                    $curTpl['talenttree'.($data['tab'] + 1)] += $data['rank'];
            }
        }
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

            foreach ($this->iterate() as $guid => $entry)
            {
                [$realm, $realmGUID] = $entry->unpackId($entry->id);
                if (!isset($localData[$realm][$realmGUID]))
                    trigger_error('RemoteProfileContainer::initializeLocalEntries - local entry not generated for char with realm #'.$realm.' realmGUID #'.$realmGUID, E_USER_WARNING);

                // still call this fn or the readonly properties remain uninitialized
                $entry->amendLocalData($localData[$realm][$realmGUID] ?? []);
            }
        }
    }
}

class LocalProfileContainer extends ProfileContainer
{
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
    public function getEntry(null|string|int $key = null) : ?LocalProfileEntry
    {
        return parent::getEntry($key);
    }

}

?>

?>
