<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SoundList extends BaseType
{
    use spawnHelper;

    public static   $type       = Type::SOUND;
    public static   $brickFile  = 'sound';
    public static   $dataTable  = '?_sounds';
    public static   $contribute = CONTRIBUTE_CO;

    protected       $queryBase  = 'SELECT s.*, s.id AS ARRAY_KEY FROM ?_sounds s';

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
            $files = DB::Aowow()->select('SELECT id AS ARRAY_KEY, `id`, `file` AS title, `type`, `path` FROM ?_sounds_files sf WHERE id IN (?a)', array_keys($this->fileBuffer));
            foreach ($files as $id => $data)
            {
                // 3.3.5 bandaid - need fullpath to play via wow API, remove for cata and later
                $data['path']  = str_replace('\\', '\\\\', $data['path'] ? $data['path'] . '\\' . $data['title'] : $data['title']);
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
        }

        return $data;
    }

    public function getJSGlobals($addMask = 0)
    {
        $data = [];

        foreach ($this->iterate() as $__)
            $data[self::$type][$this->id] = array(
                'name'  => $this->getField('name', true),
                'type'  => $this->getField('cat'),
                'files' => array_values(array_filter($this->getField('files')))
            );

        return $data;
    }

    public function renderTooltip() { }
}

class SoundListFilter extends Filter
{
    protected $inputFields = array(
        'na' => [FILTER_V_REGEX, parent::PATTERN_NAME,                                                   false], // name - only printable chars, no delimiter
        'ty' => [FILTER_V_LIST,  [[1, 4], 6, 9, 10, 12, 13, 14, 16, 17, [19, 23], [25, 31], 50, 52, 53], true ]  // type
    );

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
            $parts[] = ['cat', $_v['ty']];

        return $parts;
    }
}

?>
