<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class MailList extends BaseType
{
    public static   $type      = TYPE_MAIL;
    public static   $brickFile = 'mail';
    public static   $dataTable = '?_mails';

    protected       $queryBase = 'SELECT m.*, m.id AS ARRAY_KEY FROM ?_mails m';
    protected       $queryOpts = [];

    public function __construct($conditions = [])
    {
        parent::__construct($conditions);

        if ($this->error)
            return;

        // post processing
        foreach ($this->iterate() as $_id => &$_curTpl)
        {
            $_curTpl['name'] = Util::localizedString($_curTpl, 'subject', true);
            if (!$_curTpl['name'])
            {
                $_curTpl['name'] = sprintf(Lang::mail('untitled'), $_id);
                $_curTpl['subject_loc0'] = $_curTpl['name'];
            }
        }
    }

    public static function getName($id)
    {
        $n = DB::Aowow()->SelectRow('SELECT subject_loc0, subject_loc2, subject_loc3, subject_loc4, subject_loc6, subject_loc8 FROM ?_mails WHERE id = ?d', $id);
        return Util::localizedString($n, 'subject');
    }

    public function getListviewData()
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $body = str_replace('[br]', ' ', Util::parseHtmlText($this->getField('text', true), true));

            $data[$this->id] = array(
                'id'              => $this->id,
                'subject'         => $this->getField('subject', true),
                'body'            => Lang::trimTextClean($body),
                'attachments'     => [$this->getField('attachment')]
            );
        }

        return $data;
    }

    public function getJSGlobals($addMask = 0)
    {
        $data = [];

        foreach ($this->iterate() as $__)
            if ($a = $this->curTpl['attachment'])
                $data[TYPE_ITEM][$a] = $a;

        return $data;
    }

    public function renderTooltip() { }
}

?>
