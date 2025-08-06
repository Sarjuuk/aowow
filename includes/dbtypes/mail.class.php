<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class MailList extends DBTypeList
{
    public static int    $type      = Type::MAIL;
    public static string $brickFile = 'mail';
    public static string $dataTable = '?_mails';

    protected string $queryBase = 'SELECT m.*, m.`id` AS ARRAY_KEY FROM ?_mails m';
    protected array  $queryOpts = [];

    public function __construct(array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

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

    public static function getName(int $id) : ?LocString
    {
        if ($n = DB::Aowow()->SelectRow('SELECT `subject_loc0`, `subject_loc2`, `subject_loc3`, `subject_loc4`, `subject_loc6`, `subject_loc8` FROM ?# WHERE `id` = ?d', self::$dataTable, $id))
            return new LocString($n, 'subject');
        return null;
    }

    public function getListviewData() : array
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

    public function getJSGlobals(int $addMask = 0) : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
            if ($a = $this->curTpl['attachment'])
                $data[Type::ITEM][$a] = $a;

        return $data;
    }

    public function renderTooltip() : ?string { return null; }
}

?>
