<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ProfileStatusResponse extends TextResponse
{
    protected array $expectedGET = array(
        'id'         => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkIdList']  ],
        'guild'      => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkEmptySet']],
        'arena-team' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkEmptySet']]
    );

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);

        if (!Cfg::get('PROFILER_ENABLE'))
            $this->generate404();
    }

    /*  params
            id: <prId1,prId2,..,prIdN>
        return
            <status object>
    */
    protected function generate() : void
    {
        // roster resync for this guild was requested -> get char list
        if ($this->_get['guild'])
            $ids = DB::Aowow()->selectCol('SELECT `id` FROM ?_profiler_profiles WHERE `guild` IN (?a)', $this->_get['id']);
        else if ($this->_get['arena-team'])
            $ids = DB::Aowow()->selectCol('SELECT `profileId` FROM ?_profiler_arena_team_member WHERE `arenaTeamId` IN (?a)', $this->_get['id']);
        else
            $ids = $this->_get['id'];

        if (!$ids)
        {
            trigger_error('ProfileStatusResponse - no profileIds to resync'.($this->_get['guild'] ? ' for guild #' : ($this->_get['arena-team'] ? ' for areana team #' : ' #')).Util::toString($this->_get['id']), E_USER_WARNING);
            $this->result = Util::toJSON([1, [PR_QUEUE_STATUS_ERROR, 0, 0, PR_QUEUE_ERROR_CHAR]]);
        }

        $this->result = Profiler::resyncStatus(Type::PROFILE, $ids);
    }
}

?>
