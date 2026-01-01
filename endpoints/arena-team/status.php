<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ArenaTeamStatusResponse extends TextResponse
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

    protected function generate() : void
    {
        $this->result = Profiler::resyncStatus(Type::ARENA_TEAM, $this->_get['id']);
    }
}

?>
