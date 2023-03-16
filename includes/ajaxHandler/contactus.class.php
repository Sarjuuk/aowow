<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class AjaxContactus extends AjaxHandler
{
    protected $_post = array(
        'mode'       => ['filter' => FILTER_SANITIZE_NUMBER_INT                                ],
        'reason'     => ['filter' => FILTER_SANITIZE_NUMBER_INT                                ],
        'ua'         => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxHandler::checkTextLine'],
        'appname'    => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxHandler::checkTextLine'],
        'page'       => ['filter' => FILTER_SANITIZE_URL                                       ],
        'desc'       => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxHandler::checkTextBlob'],
        'id'         => ['filter' => FILTER_SANITIZE_NUMBER_INT                                ],
        'relatedurl' => ['filter' => FILTER_SANITIZE_URL                                       ],
        'email'      => ['filter' => FILTER_SANITIZE_EMAIL                                     ]
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
        $report = new Report($this->_post['mode'], $this->_post['reason'], $this->_post['id']);
        if ($report->create($this->_post['desc'], $this->_post['ua'], $this->_post['appname'], $this->_post['page'], $this->_post['relatedurl'], $this->_post['email']))
            return 0;
        else if ($report->errorCode > 0)
            return $report->errorCode;
        else
            return Lang::main('intError');
    }
}

?>
