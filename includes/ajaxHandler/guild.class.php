<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');

class AjaxGuild extends AjaxHandler
{
    protected $validParams = ['resync', 'status'];
    protected $_get        = array(
        'id'      => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxHandler::checkIdList'  ],
        'profile' => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxHandler::checkEmptySet'],
    );

    public function __construct(array $params)
    {
        parent::__construct($params);

        if (!$this->params)
            return;

        switch ($this->params[0])
        {
            case 'resync':
                $this->handler = 'handleResync';
                break;
            case 'status':
                $this->handler = 'handleStatus';
                break;
        }
    }

    /*  params
            id: <prId1,prId2,..,prIdN>
            user: <string> [optional, not used]
            profile: <empty> [optional, also get related chars]
        return: 1
    */
    protected function handleResync() : string
    {
        if ($guilds = DB::Aowow()->select('SELECT realm, realmGUID FROM ?_profiler_guild WHERE id IN (?a)', $this->_get['id']))
            foreach ($guilds as $g)
                Profiler::scheduleResync(Type::GUILD, $g['realm'], $g['realmGUID']);

        if ($this->_get['profile'])
            if ($chars = DB::Aowow()->select('SELECT realm, realmGUID FROM ?_profiler_profiles WHERE guild IN (?a)', $this->_get['id']))
                foreach ($chars as $c)
                    Profiler::scheduleResync(Type::PROFILE, $c['realm'], $c['realmGUID']);

        return '1';
    }

    /*  params
            id: <prId1,prId2,..,prIdN>
        return
            <status object>
            [
                nQueueProcesses,
                [statusCode, timeToRefresh, curQueuePos, errorCode, nResyncTries],
                [<anotherStatus>]
                ...
            ]

            not all fields are required, if zero they are omitted
            statusCode:
                0: end the request
                1: waiting
                2: working...
                3: ready; click to view
                4: error / retry
            errorCode:
                0: unk error
                1: char does not exist
                2: armory gone
    */
    protected function handleStatus() : string
    {
        $response = Profiler::resyncStatus(Type::GUILD, $this->_get['id']);
        return Util::toJSON($response);
    }
}

?>
