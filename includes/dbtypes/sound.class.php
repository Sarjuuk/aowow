<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SoundList extends DBTypeList
{
    use spawnHelper;

    public static int    $type       = Type::SOUND;
    public static string $brickFile  = 'sound';
    public static string $dataTable  = '?_sounds';
    public static int    $contribute = CONTRIBUTE_CO;

    protected string $queryBase = 'SELECT s.*, s.`id` AS ARRAY_KEY FROM ?_sounds s';

    private        array $fileBuffer = [];
    private static array $fileTypes  = [SOUND_TYPE_OGG => MIME_TYPE_OGG, SOUND_TYPE_MP3 => MIME_TYPE_MP3];

    public function __construct(array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

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
            $files = DB::Aowow()->select('SELECT `id` AS ARRAY_KEY, `id`, `file` AS "title", CAST(`type` AS UNSIGNED) AS "type", `path` FROM ?_sounds_files sf WHERE `id` IN (?a)', array_keys($this->fileBuffer));
            foreach ($files as $id => $data)
            {
                // 3.3.5 bandaid - need fullpath to play via wow API, remove for cata and later
                $data['path']  = str_replace('\\', '\\\\', $data['path'] ? $data['path'] . '\\' . $data['title'] : $data['title']);
                // skip file extension
                $data['title'] = substr($data['title'], 0, -4);
                // enum to string
                $data['type']  = self::$fileTypes[$data['type']];
                // get real url
                $data['url']   = Cfg::get('STATIC_URL') . '/wowsounds/' . $data['id'];
                // v push v
                $this->fileBuffer[$id] = $data;
            }
        }
    }

    public static function getName(int $id) : ?LocString
    {
        if ($n = DB::Aowow()->SelectRow('SELECT `name` AS "name_loc0" FROM ?# WHERE `id` = ?d', self::$dataTable, $id))
            return new LocString($n);
        return null;
    }

    public function getListviewData() : array
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

    public function getJSGlobals(int $addMask = 0) : array
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

    public function renderTooltip() : ?string { return null; }
}

class SoundListFilter extends Filter
{
    protected string $type        = 'sounds';
    protected static array $inputFields = array(
        'na' => [parent::V_NAME,  false,                                                        false], // name - only printable chars, no delimiter
        'ty' => [parent::V_LIST,  [[1, 4], 6, 9, 10, 12, 13, 14, 16, 17, [19, 31], 50, 52, 53], true ]  // type
    );

    protected function createSQLForValues() : array
    {
        $parts = [];
        $_v    = &$this->values;

        // name [str]
        if ($_v['na'])
            if ($_ = $this->buildLikeLookup(['na' => 'name']))
                $parts[] = $_;

        // type [list]
        if ($_v['ty'])
            $parts[] = ['cat', $_v['ty']];

        return $parts;
    }
}

?>
