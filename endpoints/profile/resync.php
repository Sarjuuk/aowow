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
        $this->result = 1;                                  // always

        if (!$this->assertGET('id'))
        {
            trigger_error('ProfileResyncResponse - invalid id received', E_USER_ERROR);
            return;
        }

        if ($chars = DB::Aowow()->selectAssoc('SELECT `id` AS ARRAY_KEY, `realm`, `realmGUID` FROM ::profiler_profiles WHERE `id` IN %in', $this->_get['id']))
            foreach ($chars as $c)
                Profiler::scheduleResync(Type::PROFILE, $c['realm'], $c['realmGUID']);

        if ($_ = array_diff(array_keys($chars ?: []), $this->_get['id']))
            trigger_error('ProfileResyncResponse - profiles '.implode(', ', $_).' not found in db', E_USER_ERROR);
    }
}

?>
