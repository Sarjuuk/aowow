<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GotocommentBaseResponse extends TextResponse
{
    protected array $expectedGET = array(
        'id' => ['filter' => FILTER_VALIDATE_INT]
    );

    protected function generate() : void
    {
        if (!$this->assertGET('id'))
        {
            trigger_error('AdminGotocommentResponse - malformed request received', E_USER_ERROR);
            $this->redirectTo = '.';                        // go home, you're drunk
            return;
        }

        // type <> NULL AND typeId <> NULL AND replyTo = NULL for comments
        $reply = DB::Aowow()->selectRow('SELECT `id`, `type`, `typeId` FROM ?_comments WHERE `replyTo` IS NULL AND `id` = ?d', $this->_get['id']);
        if (!$reply)
        {
            trigger_error('AdminGotocommentResponse - comment #'.$this->_get['id'].' not found', E_USER_ERROR);
            $this->redirectTo = '.';
            return;
        }

        if (!Type::validateIds($reply['type'], $reply['typeId']))
        {
            trigger_error('AdminGotocommentResponse - comment #'.$this->_get['id'].' belongs to nonexistent type/typeID combo '.$reply['type'].'/'.$reply['typeId'], E_USER_ERROR);
            $this->redirectTo = '.';
            return;
        }

        $this->redirectTo = sprintf('?%s=%d#comments:id=%d', Type::getFileString($reply['type']), $reply['typeId'], $reply['id']);
    }
}

?>
