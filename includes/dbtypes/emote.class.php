<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class EmoteList extends DBTypeList
{
    public static int    $type      = Type::EMOTE;
    public static string $brickFile = 'emote';
    public static string $dataTable = '?_emotes';

    protected string $queryBase = 'SELECT e.*, e.`id` AS ARRAY_KEY FROM ?_emotes e';

    public function __construct(array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

        // post processing
        foreach ($this->iterate() as &$curTpl)
        {
            // remap for generic access
            $curTpl['name'] = $curTpl['cmd'];
        }
    }

    public static function getName(int $id) : ?LocString
    {
        if ($n = DB::Aowow()->SelectRow('SELECT `cmd` AS "name_loc0" FROM ?# WHERE `id` = ?d', self::$dataTable, $id))
            return new LocString($n);
        return null;
    }

    public function getListviewData() : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'id'      => $this->curTpl['id'],
                'name'    => $this->curTpl['cmd'],
                'preview' => Util::parseHtmlText($this->getField('meToExt', true) ?: $this->getField('meToNone', true) ?: $this->getField('extToMe', true) ?: $this->getField('extToExt', true) ?: $this->getField('extToNone', true), true)
            );
        }

        return $data;
    }

    public function getJSGlobals(int $addMask = GLOBALINFO_ANY) : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
            $data[Type::EMOTE][$this->id] = ['name' => $this->getField('cmd')];

        return $data;
    }

    public function renderTooltip() : ?string { return null; }
}

?>
