<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class EmoteList extends BaseType
{
    use ListviewHelper;

    public static $type      = TYPE_EMOTE;
    public static $brickFile = 'emote';

    protected     $queryBase = 'SELECT *, e.id AS ARRAY_KEY FROM ?_emotes e';

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
        return [];
    }

    public function renderTooltip() { }
}

?>
