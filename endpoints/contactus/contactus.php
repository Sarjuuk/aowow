<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ContactusBaseResponse extends TextResponse
{
    protected array $expectedPOST = array(
        'mode'       => ['filter' => FILTER_VALIDATE_INT                                         ],
        'reason'     => ['filter' => FILTER_VALIDATE_INT                                         ],
        'ua'         => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTextLine']],
        'appname'    => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTextLine']],
        'page'       => ['filter' => FILTER_SANITIZE_URL                                         ],
        'desc'       => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTextBlob']],
        'id'         => ['filter' => FILTER_VALIDATE_INT                                         ],
        'relatedurl' => ['filter' => FILTER_SANITIZE_URL                                         ],
        'email'      => ['filter' => FILTER_SANITIZE_EMAIL                                       ]
    );

    /* responses
        0: success
        1: captcha invalid
        2: description too long
        3: reason missing
        7: already reported
        $: prints response
    */
    protected function generate() : void
    {
        if (!$this->assertPOST('mode', 'reason'))
        {
            $this->result = 4;
            return;
        }

        $report = new Report($this->_post['mode'], $this->_post['reason'], $this->_post['id']);
        if ($report->create($this->_post['desc'], $this->_post['ua'], $this->_post['appname'], $this->_post['page'], $this->_post['relatedurl'], $this->_post['email']))
            $this->result = 0;
        else if (($e = $report->getError()) > 0)
            $this->result = $e;
        else
            $this->result = Lang::main('intError');
    }
}

?>
