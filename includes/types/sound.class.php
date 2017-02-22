<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SoundList extends BaseType
{
    public static   $type      = TYPE_SOUND;
    public static   $brickFile = 'sound';

    protected       $queryBase = 'SELECT *, s.id AS ARRAY_KEY FROM ?_sounds s';

    private         $fileBuffer = [];
    private static  $fileTypes  = array(
                        SOUND_TYPE_OGG => 'audio/ogg; codecs="vorbis"',
                        SOUND_TYPE_MP3 => 'audio/mpeg'
                    );

    public function __construct($conditions = [])
    {
        parent::__construct($conditions);

        // post processing
        foreach ($this->iterate() as $id => &$_curTpl)
        {
            $_curTpl['files'] = [];
            for ($i = 1; $i < 11; $i++)
            {
                if ($_curTpl['soundFile'.$i])
                {
                    $this->fileBuffer[$_curTpl['soundFile'.$i]] = null;
                    $_curTpl['files'][] = &$this->fileBuffer[$_curTpl['soundFile'.$i]];
                }

                unset($_curTpl['soundFile'.$i]);
            }
        }

        if ($this->fileBuffer)
        {
            $files = DB::Aowow()->select('SELECT id AS ARRAY_KEY, `id`, `file` AS title, `type` FROM ?_sounds_files sf WHERE id IN (?a)', array_keys($this->fileBuffer));
            foreach ($files as $id => $data)
            {
                // skipp file extension
                $data['title'] = substr($data['title'], 0, -4);
                // enum to string
                $data['type']  = self::$fileTypes[$data['type']];
                // get real url
                $data['url']   = STATIC_URL . '/wowsounds/' . $data['id'];
                // v push v
                $this->fileBuffer[$id] = $data;
            }
        }
    }

    public static function getName($id)
    {
        $this->getEntry($id);

        return $this->getField('name');
    }

    public function getListviewData()
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'id'    => $this->id,
                'type'  => $this->getField('cat'),
                'name'  => $this->getField('name'),
                'files' => array_values(array_filter($this->getField('files')))
            );

            /* if creature movement
                $data[$this->id]['activity'] = <string>; // e.g. exertion, finch, where are those templated anyway...?
            */
        }

        return $data;
    }

    public function getJSGlobals($addMask = 0)
    {
        $data = [];

        foreach ($this->iterate() as $__)
            $data[self::$type][$this->id] = array(
                'name'  => Util::jsEscape($this->getField('name', true)),
                'type'  => $this->getField('cat'),
                'files' => array_values(array_filter($this->getField('files')))
            );

        return $data;
    }

    public function renderTooltip() { }
}

class SoundListFilter extends Filter
{
    // we have no criteria for this one...
    protected function createSQLForCriterium(&$cr)
    {
        unset($cr);
        $this->error = true;
        return [1];
    }

    protected function createSQLForValues()
    {
        $parts = [];
        $_v    = &$this->fiData['v'];

        // name [str]
        if (isset($_v['na']))
            if ($_ = $this->modularizeString(['name']))
                $parts[] = $_;

        // type [list]
        if (isset($_v['ty']))
        {
            if ($_ = array_intersect((array)$_v['ty'], [1, 2, 3, 4, 6, 9, 10, 12, 14, 16, 17, 19, 20, 21, 22, 23, 25, 26, 27, 28, 29, 30, 31, 50, 52, 53]))
                $parts[] = ['cat', $_];
            else
            {
                $this->error = true;
                unset($_v['ty']);
            }
        }

        return $parts;
    }
}

?>
