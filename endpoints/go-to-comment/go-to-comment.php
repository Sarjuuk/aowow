<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GotocommentBaseResponse extends TextResponse
{
    protected ?string $redirectTo  = '.';                   // go home, you're drunk
    protected  array  $expectedGET = array(
        'id' => ['filter' => FILTER_VALIDATE_INT]
    );

    protected function generate() : void
    {
        if (!$this->assertGET('id'))
        {
            trigger_error('GotocommentBaseResponse - malformed request received', E_USER_ERROR);
            return;
        }

        // type <> 0 AND typeId <> 0 AND replyTo = 0 for comments
        $comment = DB::Aowow()->selectRow('SELECT `id`, `type`, `typeId` FROM ?_comments WHERE `replyTo` = 0 AND `id` = ?d', $this->_get['id']);
        if (!$comment)
        {
            trigger_error('GotocommentBaseResponse - comment #'.$this->_get['id'].' not found', E_USER_ERROR);
            return;
        }

        if (!Type::validateIds($comment['type'], $comment['typeId']))
        {
            trigger_error('GotocommentBaseResponse - comment #'.$this->_get['id'].' belongs to nonexistent type/typeID combo '.$comment['type'].'/'.$comment['typeId'], E_USER_ERROR);
            return;
        }

        $this->redirectTo = sprintf('?%s=%d#comments:id=%d', Type::getFileString($comment['type']), $comment['typeId'], $comment['id']);
    }
}

?>
