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

        $nScales = DB::Aowow()->selectCell('SELECT COUNT(`id`) FROM ::account_weightscales WHERE `userId` = %i', User::$id);
        if ($nScales >= self::MAX_SCALES)
            return;

        if ($id = DB::Aowow()->qry('INSERT INTO ::account_weightscales (`userId`, `name`) VALUES (%i, %s)', User::$id, $this->_post['name']))
            if ($this->storeScaleData($id))
                $this->result = $id;
    }

    private function updateWeights() : void
    {
        if (!$this->assertPOST('name', 'scale', 'id'))
            return;

        // not in DB or not owned by user
        if (!DB::Aowow()->selectCell('SELECT 1 FROM ::account_weightscales WHERE `userId` = %i AND `id` = %i', User::$id, $this->_post['id']))
        {
            trigger_error('AccountWeightscalesResponse::updateWeights - scale #'.$this->_post['id'].' not in db or not owned by user #'.User::$id, E_USER_ERROR);
            return;
        }

        DB::Aowow()->qry('UPDATE ::account_weightscales SET `name` = %s WHERE `id` = %i', $this->_post['name'], $this->_post['id']);
        $this->storeScaleData($this->_post['id']);

        // return edited id on success
        $this->result = $this->_post['id'];
    }

    private function deleteWeights() : void
    {
        if ($this->assertPOST('id'))
            DB::Aowow()->qry('DELETE FROM ::account_weightscales WHERE `id` = %i AND `userId` = %i', $this->_post['id'], User::$id);

        $this->result = '';
    }

    private function storeScaleData(int $scaleId) : bool
    {
        if (!is_int(DB::Aowow()->qry('DELETE FROM ::account_weightscale_data WHERE `id` = %i', $scaleId)))
            return false;

        // $x['val'] is known to be a positive int due to regex check
        $scaleData = array_filter($this->_post['scale'], fn($x) => in_array($x['field'], Util::$weightScales) && $x['val'] > 0);

        array_walk($scaleData, fn($x) => $x['id'] = $scaleId);

        foreach ($scaleData as $sd)
            if (is_null(DB::Aowow()->qry('INSERT INTO ::account_weightscale_data %v', $sd)))
                return false;

        return true;
    }


    /*************************************/
    /* additional request data callbacks */
    /*************************************/

    protected static function checkScale(string $val) : array
    {
        if (preg_match('/^((\w+:\d+)(,\w+:\d+)*)$/', $val))
            return array_map(fn($x) => array_combine(['field', 'val'], explode(':', $x)), explode(',', $val));

        return [];
    }

    protected static function checkName(string $val) : string
    {
        return mb_substr(preg_replace('/[^[:print:]]/', '', trim(urldecode($val))), 0, 32);
    }
}

?>
