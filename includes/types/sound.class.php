<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SoundList extends BaseType
{
    public static   $type      = TYPE_SOUND;
    public static   $brickFile = 'sound';

    protected       $queryBase = 'SELECT *, s.id AS ARRAY_KEY FROM ?_sounds s';
    protected       $queryOpts = array(
                        // 'sl' => [['si']],
                        // 'si' => ['j' => '?_icons si ON si.id = sl.iconId', 's' => ', si.iconString'],
                    );

    public function __construct($conditions = [])
    {
        parent::__construct($conditions);

        // // post processing
        // foreach ($this->iterate() as &$_curTpl)
        // {
            // $_ = &$_curTpl['specializations'];              // shorthand
            // if (!$_)
                // $_ = [0, 0, 0, 0, 0];
            // else
            // {
                // $_ = explode(' ', $_);
                // while (count($_) < 5)
                    // $_[] = 0;
            // }
        // }
    }

    public static function getName($id)
    {
        // $n = DB::Aowow()->SelectRow('SELECT name_loc0, name_loc2, name_loc3, name_loc6, name_loc8 FROM ?_skillline WHERE id = ?d', $id);
        return ""; // Util::localizedString($n, 'name');
    }

    public function getListviewData()
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                // "id":"11447",
                // "type":"0",
                // "name":"A_BLCKTMPLE_HiNethZer_Attack",
                // "files":[
                    // {"id":"600878","title":"a_blcktmple_hinethzer_attack","url":"\/\/wow.zamimg.com\/wowsounds\/600878","type":"audio\/ogg; codecs=\"vorbis\""},
                    // {"id":"600881","title":"a_blcktmple_hinethzer_attack","url":"\/\/wow.zamimg.com\/wowsounds\/600881","type":"audio\/ogg; codecs=\"vorbis\""},
                    // {"id":"600884","title":"a_blcktmple_hinethzer_attack","url":"\/\/wow.zamimg.com\/wowsounds\/600884","type":"audio\/ogg; codecs=\"vorbis\""},
                    // {"id":"600902","title":"a_blcktmple_hinethzer_attack","url":"\/\/wow.zamimg.com\/wowsounds\/600902","type":"audio\/ogg; codecs=\"vorbis\""},
                    // {"id":"600911","title":"a_blcktmple_hinethzer_attack","url":"\/\/wow.zamimg.com\/wowsounds\/600911","type":"audio\/ogg; codecs=\"vorbis\""},
                    // {"id":"600917","title":"a_blcktmple_hinethzer_attack","url":"\/\/wow.zamimg.com\/wowsounds\/600917","type":"audio\/ogg; codecs=\"vorbis\""},
                    // {"id":"600923","title":"a_blcktmple_hinethzer_attack","url":"\/\/wow.zamimg.com\/wowsounds\/600923","type":"audio\/ogg; codecs=\"vorbis\""}
                // ]
            );
        }

        return $data;
    }

    public function getJSGlobals($addMask = 0)
    {
        $data = [];

        // foreach ($this->iterate() as $__)
            // $data[self::$type][$this->id] = ['name' => Util::jsEscape($this->getField('name', true)), 'icon' => Util::jsEscape($this->curTpl['iconString'])];

        return $data;
    }

    public function renderTooltip() { }
}

?>
