<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GotoreplyBaseResponse extends TextResponse
{
    protected ?string $redirectTo  = '.';                   // go home, you're drunk
    protected  array  $expectedGET = array(
        'id' => ['filter' => FILTER_VALIDATE_INT]
    );

    protected function generate() : void
    {
        if (!$this->assertGET('id'))
        {
            trigger_error('GotoreplyBaseResponse - malformed request received', E_USER_ERROR);
            return;
        }

        // type = typeId = 0 AND replyTo <> 0 for replies
        $reply = DB::Aowow()->selectRow('SELECT c.`id`, r.`id` AS "reply", c.`type`, c.`typeId` FROM ::comments r JOIN ::comments c ON r.`replyTo` = c.`id` WHERE r.`id` = %i', $this->_get['id']);
        if (!$reply)
        {
            trigger_error('GotoreplyBaseResponse - reply #'.$this->_get['id'].' not found', E_USER_ERROR);
            return;
        }

        if (!Type::validateIds($reply['type'], $reply['typeId']))
        {
            trigger_error('GotoreplyBaseResponse - parent comment #'.$reply['id'].' belongs to nonexistent type/typeID combo '.$reply['type'].'/'.$reply['typeId'], E_USER_ERROR);
            return;
        }

        $this->redirectTo = sprintf('?%s=%d#comments:id=%d:reply=%d', Type::getFileString($reply['type']), $reply['typeId'], $reply['id'], $reply['reply']);
    }
}

?>
