<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class EmoteList extends BaseType
{
    public static   $type      = TYPE_EMOTE;
    public static   $brickFile = 'emote';
    public static   $dataTable = '?_emotes';

    protected       $queryBase = 'SELECT *, e.id AS ARRAY_KEY FROM ?_emotes e';

    public function __construct($conditions = [])
    {
        parent::__construct($conditions);

        // post processing
        foreach ($this->iterate() as &$curTpl)
        {
            // remap for generic access
            $curTpl['name'] = $curTpl['cmd'];
        }
    }

    public function getListviewData()
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'id'      => $this->curTpl['id'],
                'name'    => $this->curTpl['cmd'],
                'preview' => $this->getField('self', true) ?: ($this->getField('noTarget', true) ?: $this->getField('target', true))
            );

            // [nyi] sounds
        }

        return $data;
    }

    public function getJSGlobals($addMask = GLOBALINFO_ANY)
    {
        $data = [];

        foreach ($this->iterate() as $__)
            $data[TYPE_EMOTE][$this->id] = ['name' => $this->getField('cmd')];

        return $data;
    }

    public function renderTooltip() { }
}

?>
