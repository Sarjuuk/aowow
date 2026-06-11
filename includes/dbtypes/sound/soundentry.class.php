<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SoundEntry extends DBTypeEntry
{
    public readonly int    $cuFlags;
    public readonly string $name;
    public readonly int    $cat;
    public readonly int    $flags;
    /** @var int[] $fileIds - length: 10  */
    public readonly array  $fileIds;
    /** @var mixed[] $files - length: 10 [path, title, type, url] */
    public readonly array  $files;

    public static int    $dbType     = Type::SOUND;
    public static string $brickFile  = 'sound';
    public static string $dataTable  = '::sounds';
    public static int    $contribute = CONTRIBUTE_CO;

    public const /* string */ QUERY_BASE = 'SELECT s.*, s.`id` AS ARRAY_KEY FROM ::sounds s';

    private const /* array */ FILE_TYPES = [SOUND_TYPE_OGG => MIME_TYPE_OGG, SOUND_TYPE_MP3 => MIME_TYPE_MP3];

    public function __construct(int|array $initData, array $extraOpts = [])
    {
        parent::__construct($initData, $extraOpts);

        // not provided by batch operation
        if (is_int($initData))
            $this->setSoundFiles(self::fetchSoundFiles(...$this->fileIds));
    }

    public function applyInitData(array $initData) : void
    {
        parent::applyInitData($initData);

        $fileIds = [];
        for ($i = 1; $i < 11; $i++)
        {
            if ($initData['soundFile'.$i])
                $fileIds[] = $initData['soundFile'.$i];

            unset($initData['soundFile'.$i]);
        }
        $this->fileIds = $fileIds;

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

    public function getListviewRow(int $addInfoMask = 0x0) : array
    {
        return array(
            'id'    => $this->id,
            'type'  => $this->cat,
            'name'  => $this->name,
            'files' => $this->files
        );
    }

    public function getJSGlobal(int $addMask = 0) : array
    {
        return [self::$dbType => [$this->id => array(
            'name'  => $this->name,
            'type'  => $this->cat,
            'files' => $this->files
        )]];
    }

    public function renderTooltip() : ?string { return null; }

    public function setSoundFiles(array $soundData) : void
    {
        if ($_ = array_intersect_key($soundData, array_flip($this->fileIds)))
            $this->files = array_values($_);
    }

    public static function getName(int $id) : ?LocString
    {
        if ($n = DB::Aowow()->SelectRow('SELECT `name` AS "name_loc%i" FROM %n WHERE `id` = %i', Lang::getLocale()->value, self::$dataTable, $id))
            return new LocString($n);
        return null;
    }

    public static function fetchSoundFiles(int ...$ids) : array
    {
        if (!($data = DB::Aowow()->selectAssoc('SELECT `id` AS ARRAY_KEY, `id`, `file` AS "title", CAST(`type` AS UNSIGNED) AS "type", `path` FROM ::sounds_files WHERE `id` IN %in', $ids)))
            return [];

        foreach ($data as &$d)
        {
            // 3.3.5 bandaid - need fullpath to play via wow API, remove for cata and later
            $d['path']  = str_replace('\\', '\\\\', $d['path'] ? $d['path'] . '\\' . $d['title'] : $d['title']);
            // skip file extension
            $d['title'] = substr($d['title'], 0, -4);
            // enum to string
            $d['type']  = self::FILE_TYPES[$d['type']];
            // get real url
            $d['url']   = Cfg::get('STATIC_URL') . '/wowsounds/' . $d['id'];
        }

        return $data;
    }
}

?>
