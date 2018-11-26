<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class AjaxContactus extends AjaxHandler
{
    protected $_post = array(
        'mode'       => [FILTER_SANITIZE_NUMBER_INT, null                 ],
        'reason'     => [FILTER_SANITIZE_NUMBER_INT, null                 ],
        'ua'         => [FILTER_SANITIZE_STRING,     FILTER_FLAG_STRIP_LOW],
        'appname'    => [FILTER_SANITIZE_STRING,     FILTER_FLAG_STRIP_LOW],
        'page'       => [FILTER_SANITIZE_URL,        null                 ],
        'desc'       => [FILTER_SANITIZE_STRING,     FILTER_FLAG_STRIP_LOW],
        'id'         => [FILTER_SANITIZE_NUMBER_INT, null                 ],
        'relatedurl' => [FILTER_SANITIZE_URL,        null                 ],
        'email'      => [FILTER_SANITIZE_EMAIL,      null                 ]
    );

    public function __construct(array $params)
    {
        parent::__construct($params);

        // always this one
        $this->handler = 'handleContactUs';
    }

    /* responses
        0: success
        1: captcha invalid
        2: description too long
        3: reason missing
        7: already reported
        $: prints response
    */
    protected function handleContactUs() : string
    {
        $mode = $this->_post['mode'];
        $rsn  = $this->_post['reason'];
        $ua   = $this->_post['ua'];
        $app  = $this->_post['appname'];
        $url  = $this->_post['page'];
        $desc = $this->_post['desc'];
        $subj = $this->_post['id'];

        $contexts = array(
            [1, 2, 3, 4, 5, 6, 7, 8],
            [15, 16, 17, 18, 19, 20],
            [30, 31, 32, 33, 34, 35, 36, 37],
            [45, 46, 47, 48],
            [60, 61],
            [45, 46, 47, 48],
            [45, 46, 48]
        );

        if ($mode === null || $rsn === null || $ua === null || $app === null || $url === null)
        {
            trigger_error('AjaxContactus::handleContactUs - malformed contact request received', E_USER_ERROR);
            return Lang::main('intError');
        }

        if (!isset($contexts[$mode]) || !in_array($rsn, $contexts[$mode]))
        {
            trigger_error('AjaxContactus::handleContactUs - report has invalid context (mode:'.$mode.' / reason:'.$rsn.')', E_USER_ERROR);
            return Lang::main('intError');
        }

        if (!$desc)
            return 3;

        if (mb_strlen($desc) > 500)
            return 2;

        if (!User::$id && !User::$ip)
        {
            trigger_error('AjaxContactus::handleContactUs - could not determine IP for anonymous user', E_USER_ERROR);
            return Lang::main('intError');
        }

        // check already reported
        $field = User::$id ? 'userId' : 'ip';
        if (DB::Aowow()->selectCell('SELECT 1 FROM ?_reports WHERE `mode` = ?d AND `reason`= ?d AND `subject` = ?d AND ?# = ?', $mode, $rsn, $subj, $field, User::$id ?: User::$ip))
            return 7;

        if (Util::createReport($mode, $rsn, $subj, $desc, $ua, $app, $url, $this->_post['relatedurl'], $this->_post['email']))
            return 0;

        trigger_error('AjaxContactus::handleContactUs - write to db failed', E_USER_ERROR);
        return Lang::main('intError');
    }
}

?>
