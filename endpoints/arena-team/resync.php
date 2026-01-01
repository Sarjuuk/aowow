<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ArenaTeamResyncResponse extends TextResponse
{
    protected array $expectedGET = array(
        'id'      => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkIdList']  ],
        'profile' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkEmptySet']]
    );

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);

        if (!Cfg::get('PROFILER_ENABLE'))
            $this->generate404();
    }

    /*  params
            id: <prId1,prId2,..,prIdN>
            user: <string> [optional, not used]
            profile: <empty> [optional, also get related chars]
        return: 1
    */
    protected function generate() : void
    {
        if (!$this->assertGET('id'))
            return;

        if ($teams = DB::Aowow()->select('SELECT `realm`, `realmGUID` FROM ?_profiler_arena_team WHERE `id` IN (?a)', $this->_get['id']))
            foreach ($teams as $t)
                Profiler::scheduleResync(Type::ARENA_TEAM, $t['realm'], $t['realmGUID']);

        if ($this->_get['profile'])
            if ($chars = DB::Aowow()->select('SELECT `realm`, `realmGUID` FROM ?_profiler_profiles p JOIN ?_profiler_arena_team_member atm ON atm.`profileId` = p.`id` WHERE atm.`arenaTeamId` IN (?a)', $this->_get['id']))
                foreach ($chars as $c)
                    Profiler::scheduleResync(Type::PROFILE, $c['realm'], $c['realmGUID']);

        $this->result = 1;                                  // as string?
    }
}

?>
