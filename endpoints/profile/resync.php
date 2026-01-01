<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ProfileResyncResponse extends TextResponse
{
    protected array $expectedGET = array(
        'id' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkIdList']]
    );

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);

        if (!Cfg::get('PROFILER_ENABLE'))
            $this->generate404();
    }

    /*  params
            id: <prId1,prId2,..,prIdN>
        return:
            1
    */
    protected function generate() : void
    {
        if ($chars = DB::Aowow()->select('SELECT `realm`, `realmGUID` FROM ?_profiler_profiles WHERE `id` IN (?a)', $this->_get['id']))
        {
            foreach ($chars as $c)
                Profiler::scheduleResync(Type::PROFILE, $c['realm'], $c['realmGUID']);
        }
        else
            trigger_error('ProfileResyncResponse - profiles '.implode(', ', $this->_get['id']).' not found in db', E_USER_ERROR);

        $this->result = 1;
    }
}

?>
