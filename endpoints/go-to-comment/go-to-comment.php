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

        // the reputation-history listview only creates go-to-comment links. So either upvoting replies does not grant reputation, or.... bug.?

        $comment = DB::Aowow()->selectRow('SELECT IFNULL(c2.`id`, c1.`id`) AS "id", IFNULL(c2.`type`, c1.`type`) AS "type", IFNULL(c2.`typeId`, c1.`typeId`) AS "typeId" FROM ?_comments c1 LEFT JOIN ?_comments c2 ON c1.`replyTo` = c2.`id` WHERE c1.`id` = ?d', $this->_get['id']);
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
        if ($comment['id'] != $this->_get['id'])     // i am reply
            $this->redirectTo .= ':reply='.$this->_get['id'];
    }
}

?>
