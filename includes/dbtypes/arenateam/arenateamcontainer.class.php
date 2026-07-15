<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


abstract class ArenateamContainer extends DBTypeContainer implements IListview
{
    public static int $dbType = Type::ARENA_TEAM;

    public function __construct(?array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

    }

    /**
     * iterate over fetched sets
     *
     * @return \Generator<string, ArenateamEntry> key => arena team template
     */
    public function iterate() : \Generator
    {
        yield from parent::iterate();
    }

    /**
     * @return ?ArenateamEntry
     */
    public function getEntry(null|string|int $key = null) : ?ArenateamEntry
    {
        return parent::getEntry($key);
    }
}

class RemoteArenateamContainer extends ArenateamContainer
{
    public function initializeLocalEntries() : void
    {
        $profiles = [];
        // init members for tooltips
        foreach ($this->members as $realmId => $teams)
        {
            $gladiators = [];
            foreach ($teams as $team)
                $gladiators = array_merge($gladiators, array_keys($team));

            $profiles[$realmId] = new RemoteProfileContainer(array(['c.guid', $gladiators]), ['sv' => $realmId]);

            if (!$profiles[$realmId]->error)
                $profiles[$realmId]->initializeLocalEntries();
        }

        $data = [];
        foreach ($this->iterate() as $guid => $entry)
        {
            $data['realm'][$guid]     = $entry->realmId;
            $data['realmGUID'][$guid] = $entry->realmGUID; // arenaTeamId
            $data['name'][$guid]      = $entry->name;
            $data['nameUrl'][$guid]   = Profiler::urlize($entry->name);
            $data['type'][$guid]      = $entry->type;
            $data['rating'][$guid]    = $entry->rating;
            $data['stub'][$guid]      = 1;
        }

        // basic arena team data
        DB::Aowow()->qry('INSERT INTO ::profiler_arena_team %m ON DUPLICATE KEY UPDATE `id` = `id`', $data);

        // merge back local ids
        $localIds = DB::Aowow()->selectCol('SELECT CONCAT(`realm`, ":", `realmGUID`) AS ARRAY_KEY, `id` FROM ::profiler_arena_team WHERE `realm` IN %in AND `realmGUID` IN %in',
            $data['realm'], $data['realmGUID']
        );

        foreach ($this->iterate() as $guid => &$_curTpl)
            if (isset($localIds[$guid]))
                $_curTpl['id'] = $localIds[$guid];


        // profiler_arena_team_member requires profiles and arena teams to be filled
        foreach ($this->members as $realmId => $teams)
        {
            if (empty($profiles[$realmId]))
                continue;

            $memberData = [];
            foreach ($teams as $teamId => $team)
            {
                $clearMembers = [];
                foreach ($team as $memberId => $member)
                {
                    $clearMembers[] = $profiles[$realmId]->getEntry($realmId.':'.$memberId)->id;

                    $memberData['arenaTeamId'][] = $localIds[$realmId.':'.$teamId];
                    $memberData['profileId'][]   = $profiles[$realmId]->getEntry($realmId.':'.$memberId)->id;
                    $memberData['captain'][]     = $member[2];
                }

                // Delete members from other teams of the same type
                DB::Aowow()->qry(
                   'DELETE atm
                    FROM   ::profiler_arena_team_member atm
                    JOIN   ::profiler_arena_team at ON atm.`arenaTeamId` = at.`id` AND at.`type` = %i
                    WHERE  atm.`profileId` IN %in',
                    $data['type'][$realmId.':'.$teamId] ?? 0,
                    $clearMembers
                );
            }

            DB::Aowow()->qry('INSERT INTO ::profiler_arena_team_member %m ON DUPLICATE KEY UPDATE `profileId` = `profileId`', $memberData);
        }
    }
}

class LocalArenateamContainer extends ArenateamContainer
{

}

?>
