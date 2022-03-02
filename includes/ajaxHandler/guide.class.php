<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class AjaxGuide extends AjaxHandler
{
    protected $_post = array(
        'id'     => [FILTER_SANITIZE_NUMBER_INT, null],
        'rating' => [FILTER_SANITIZE_NUMBER_INT, null]
    );

    public function __construct(array $params)
    {
        parent::__construct($params);

        if (!$this->params || count($this->params) != 1)
            return;

        $this->contentType = MIME_TYPE_TEXT;

        // select handler
        if ($this->params[0] == 'vote')
            $this->handler = 'voteGuide';
    }

    protected function voteGuide() : string
    {
        if (!$this->_post['id'] || $this->_post['rating'] < 0 || $this->_post['rating'] > 5)
        {
            header('HTTP/1.0 404 Not Found', true, 404);
            return '';
        }
        else if (!User::canUpvote() || !User::canDownvote())     // same logic as comments?
        {
            header('HTTP/1.0 403 Forbidden', true, 403);
            return '';
        }
        // by id, not own, published
        if ($g = DB::Aowow()->selectRow('SELECT `userId`, `cuFlags` FROM ?_guides WHERE `id` = ?d AND (`status` = ?d OR `rev` > 0)', $this->_post['id'], GUIDE_STATUS_APPROVED))
        {
            if ($g['cuFlags'] & GUIDE_CU_NO_RATING || $g['userId'] == User::$id)
            {
                header('HTTP/1.0 403 Forbidden', true, 403);
                return '';
            }

            if (!$this->_post['rating'])
                DB::Aowow()->query('DELETE FROM ?_user_ratings WHERE `type` = ?d AND `entry` = ?d AND `userId` = ?d', RATING_GUIDE, $this->_post['id'], User::$id);
            else
                DB::Aowow()->query('REPLACE INTO ?_user_ratings VALUES (?d, ?d, ?d, ?d)', RATING_GUIDE, $this->_post['id'], User::$id, $this->_post['rating']);

            $res = DB::Aowow()->selectRow('SELECT IFNULL(SUM(`value`), 0) AS `t`, IFNULL(COUNT(*), 0) AS `n` FROM ?_user_ratings WHERE `type` = ?d AND `entry` = ?d', RATING_GUIDE, $this->_post['id']);
            return Util::toJSON($res['n'] ? ['rating' => $res['t'] / $res['n'], 'nvotes' => $res['n']] : ['rating' => 0, 'nvotes' => 0]);
        }

        return Util::toJSON(['rating' => 0, 'nvotes' => 0]);
    }
}

?>
