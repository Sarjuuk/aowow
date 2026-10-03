<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CommentShowrepliesResponse extends TextResponse
{
    protected array $expectedGET = array(
        'id' => ['filter' => FILTER_VALIDATE_INT],
        'offset' => ['filter' => FILTER_VALIDATE_INT, 'options' => ['min_range' => 0, 'max_range' => 100000000]],
        'focus' => ['filter' => FILTER_VALIDATE_INT, 'options' => ['min_range' => 1]]
    );

    protected function generate() : void
    {
        if (!$this->assertGET('id'))
            $this->result = Util::toJSON([]);
        else
        {
            $total = 0;
            $this->result = Util::toJSON(CommunityContent::getCommentReplies($this->_get['id'], CommunityContent::COMMENT_PAGE_SIZE, $total, $this->_get['offset'] ?: 0, $this->_get['focus'] ?: 0));
        }
    }
}

?>
