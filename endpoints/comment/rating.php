<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// up/down - distribution
class CommentRatingResponse extends TextResponse
{
    protected array $expectedGET = array(
        'id' => ['filter' => FILTER_VALIDATE_INT]
    );

    protected function generate() : void
    {
        if (!$this->assertGET('id'))
        {
            $this->result = Util::toJSON(['success' => 0]);
            return;
        }

        if ($votes = DB::Aowow()->selectRow('SELECT 1 AS "success", SUM(IF(`value` > 0, `value`, 0)) AS "up", SUM(IF(`value` < 0, -`value`, 0)) AS "down" FROM ::user_ratings WHERE `type` = %i AND `entry` = %i AND `userId` <> 0 GROUP BY `entry`', RATING_COMMENT, $this->_get['id']))
            $this->result = Util::toJSON($votes);
        else
            $this->result = Util::toJSON(['success' => 1, 'up' => 0, 'down' => 0]);
    }
}

?>
