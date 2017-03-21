<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class AjaxContactus extends AjaxHandler
{
    protected $_post = array(
        'mode'       => [FILTER_SANITIZE_NUMBER_INT, null],
        'reason'     => [FILTER_SANITIZE_NUMBER_INT, null],
        'ua'         => [FILTER_SANITIZE_STRING,     FILTER_FLAG_STRIP_LOW],
        'appname'    => [FILTER_SANITIZE_STRING,     FILTER_FLAG_STRIP_LOW],
        'page'       => [FILTER_SANITIZE_URL,        null],
        'desc'       => [FILTER_SANITIZE_STRING,     FILTER_FLAG_STRIP_LOW],
        'id'         => [FILTER_SANITIZE_NUMBER_INT, null],
        'relatedurl' => [FILTER_SANITIZE_URL,        null],
        'email'      => [FILTER_SANITIZE_EMAIL,      null]
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
    protected function handleContactUs()
    {
        $mode = $this->_post['mode'];
        $rsn  = $this->_post['reason'];
        $ua   = $this->_post['ua'];
        $app  = $this->_post['appname'];
        $url  = $this->_post['page'];
        $desc = $this->_post['desc'];

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
            return 'required field missing';

        if (!isset($contexts[$mode]) || !in_array($rsn, $contexts[$mode]))
            return 'mode invalid';

        if (!$desc)
            return 3;

        if (mb_strlen($desc) > 500)
            return 2;

        if (!User::$id && !User::$ip)
            return 'your ip could not be determined';

        // check already reported
        $field = User::$id ? 'userId' : 'ip';
        if (DB::Aowow()->selectCell('SELECT 1 FROM ?_reports WHERE `mode` = ?d AND `reason`= ?d AND `subject` = ?d AND ?# = ?', $mode, $rsn, $this->_post['id'], $field, User::$id ?: User::$ip))
            return 7;

        $update = array(
            'userId'      => User::$id,
            'mode'        => $mode,
            'reason'      => $rsn,
            'ip'          => User::$ip,
            'description' => $desc,
            'userAgent'   => $ua,
            'appName'     => $app,
            'url'         => $url
        );

        if ($_ = $this->_post['id'])
            $update['subject'] = $_;

        if ($_ = $this->_post['relatedurl'])
            $update['relatedurl'] = $_;

        if ($_ = $this->_post['email'])
            $update['email'] = $_;

        if (DB::Aowow()->query('INSERT INTO ?_reports (?#) VALUES (?a)', array_keys($update), array_values($update)))
            return 0;

        return 'save to db unsuccessful';
    }
}