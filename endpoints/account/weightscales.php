<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
 * accessed via ajax
 * returns scaleId if successful, 0 if not
 */

class AccountWeightscalesResponse extends TextResponse
{
    private const /* int */ MAX_SCALES = 5;                 // more or less hard-defined in LANG.message_weightscalesaveerror

    protected bool  $requiresLogin = true;
    protected mixed $result        = 0;                     // default to error

    protected array $expectedPOST  = array(
        'save'   => ['filter' => FILTER_VALIDATE_INT, 'options' => ['min_range' => 1, 'max_range' => 1]],
        'delete' => ['filter' => FILTER_VALIDATE_INT, 'options' => ['min_range' => 1, 'max_range' => 1]],
        'id'     => ['filter' => FILTER_VALIDATE_INT                                                   ],
        'name'   => ['filter' => FILTER_CALLBACK,     'options' => [self::class, 'checkName']          ],
        'scale'  => ['filter' => FILTER_CALLBACK,     'options' => [self::class, 'checkScale']         ]
    );

    protected function generate() : void
    {
        if (User::isBanned())
            return;

        if ($this->_post['save'] && $this->_post['id'])
            $this->updateWeights();

        else if ($this->_post['save'])
            $this->createWeights();

        else if ($this->_post['delete'])
            $this->deleteWeights();
    }

    private function createWeights() : void
    {
        if (!$this->assertPOST('name', 'scale'))
            return;

        $nScales = DB::Aowow()->selectCell('SELECT COUNT(`id`) FROM ?_account_weightscales WHERE `userId` = ?d', User::$id);
        if ($nScales >= self::MAX_SCALES)
            return;

        if ($id = DB::Aowow()->query('INSERT INTO ?_account_weightscales (`userId`, `name`) VALUES (?d, ?)', User::$id, $this->_post['name']))
            if ($this->storeScaleData($id))
                $this->result = $id;
    }

    private function updateWeights() : void
    {
        if (!$this->assertPOST('name', 'scale', 'id'))
            return;

        // not in DB or not owned by user
        if (!DB::Aowow()->selectCell('SELECT 1 FROM ?_account_weightscales WHERE `userId` = ?d AND `id` = ?d', User::$id, $this->_post['id']))
        {
            trigger_error('AccountWeightscalesResponse::updateWeights - scale #'.$this->_post['id'].' not in db or not owned by user #'.User::$id, E_USER_ERROR);
            return;
        }

        DB::Aowow()->query('UPDATE ?_account_weightscales SET `name` = ? WHERE `id` = ?d', $this->_post['name'], $this->_post['id']);
        $this->storeScaleData($this->_post['id']);

        // return edited id on success
        $this->result = $this->_post['id'];
    }

    private function deleteWeights() : void
    {
        if ($this->assertPOST('id'))
            DB::Aowow()->query('DELETE FROM ?_account_weightscales WHERE `id` = ?d AND `userId` = ?d', $this->_post['id'], User::$id);

        $this->result = '';
    }

    private function storeScaleData(int $scaleId) : bool
    {
        if (!is_int(DB::Aowow()->query('DELETE FROM ?_account_weightscale_data WHERE `id` = ?d', $scaleId)))
            return false;

        foreach ($this->_post['scale'] as [$k, $v])
            if (in_array($k, Util::$weightScales))          // $v is known to be a positive int due to regex check
                if (!is_int(DB::Aowow()->query('INSERT INTO ?_account_weightscale_data VALUES (?d, ?, ?d)', $scaleId, $k, $v)))
                    return false;

        return true;
    }


    /*************************************/
    /* additional request data callbacks */
    /*************************************/

    protected static function checkScale(string $val) : array
    {
        if (preg_match('/^((\w+:\d+)(,\w+:\d+)*)$/', $val))
            return array_map(fn($x) => explode(':', $x), explode(',', $val));

        return [];
    }

    protected static function checkName(string $val) : string
    {
        return mb_substr(preg_replace('/[^[:print:]]/', '', trim(urldecode($val))), 0, 32);
    }
}

?>
