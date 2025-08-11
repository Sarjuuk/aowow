<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class IconGetidfromnameResponse extends TextResponse
{
    protected array $expectedGET = array(
        'name' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => '/^[\w_-]+$/']]
    );

    protected function generate() : void
    {
        if (!$this->assertGET('name'))
        {
            $this->result = 'null';
            return;
        }

        $this->result = 0;
        if ($id = DB::Aowow()->selectCell('SELECT `id` FROM ?_icons WHERE `name` = ?', $this->_get['name']))
            $this->result = $id;
    }
}

?>
