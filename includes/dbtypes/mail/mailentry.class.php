<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class MailEntry extends DBTypeEntry
{
    public readonly int       $cuFlags;
    public readonly LocString $name;                        // alias of 'subject'
    public readonly LocString $subject;
    public readonly LocString $text;
    public readonly int       $attachment;


    public static int    $dbType    = Type::MAIL;
    public static string $brickFile = 'mail';
    public static string $dataTable = '::mails';

    public const /* string */ QUERY_BASE = 'SELECT m.*, m.`id` AS ARRAY_KEY FROM ::mails m';

    public function applyInitData(array $initData) : void
    {
        parent::applyInitData($initData);

        $nameBak = ['name_loc0' => Lang::mail('untitled', [$initData['id']])];
        $subject = new LocString($initData, 'subject', pruneFromSrc: true);
        if ($subject->isEmpty())
            $subject = new LocString($nameBak);

        $this->name = $this->subject = $subject;
        $this->text = new LocString($initData, 'text', pruneFromSrc: true);

        foreach ($initData as $k => $v)
        {
            switch ($k)
            {
                case 'id':                                  // id defined by parent
                    continue 2;
                default:
                    if (property_exists($this, $k))
                        $this->$k = $v;
            }
        }
    }

    public static function getName(int $id) : ?LocString
    {
        if ($n = DB::Aowow()->SelectRow('SELECT `subject_loc0`, `subject_loc2`, `subject_loc3`, `subject_loc4`, `subject_loc6`, `subject_loc8` FROM %n WHERE `id` = %i', self::$dataTable, $id))
            return new LocString($n, 'subject');
        return null;
    }

    public function getListviewRow(int $addInfoMask = 0x0) : array
    {
        return array(
            'id'          => $this->id,
            'subject'     => $this->subject,
            'body'        => Lang::trimTextClean(UIText::format($this->text)),
            'attachments' => [$this->attachment]
        );
    }

    public function getJSGlobal(int $addMask = 0) : array
    {
        if (!$this->attachment)
            return [];

        return [Type::ITEM => [$this->attachment =>
            $this->attachment
        ]];
    }

    public function renderTooltip() : ?string { return null; }
}

?>
