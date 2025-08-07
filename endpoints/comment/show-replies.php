<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CommentShowrepliesResponse extends TextResponse
{
    protected array $expectedGET = array(
        'id' => ['filter' => FILTER_VALIDATE_INT]
    );

    protected function generate() : void
    {
        if (!$this->assertGET('id'))
            $this->result = Util::toJSON([]);
        else
            $this->result = Util::toJSON(CommunityContent::getCommentReplies($this->_get['id']));
    }
}

?>
