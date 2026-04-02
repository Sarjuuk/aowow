<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class TitleEntry extends DBTypeEntry
{
    use TrSourceHelper;

    public readonly int       $cuFlags;
    public readonly LocString $name;
    public readonly LocString $male;
    public readonly LocString $female;
    public readonly int       $category;
    public readonly int       $gender;
    public readonly int       $side;
    public readonly int       $expansion;
    public readonly int       $eventId;
    public readonly int       $bitIdx;
    public readonly int       $src12Ext;

    public static int    $dbType    = Type::TITLE;
    public static string $brickFile = 'title';
    public static string $dataTable = '::titles';

    public ?array $source = null;                           // set by bulk operation

    public const string QUERY_BASE = 'SELECT t.*, t.`id` AS ARRAY_KEY FROM ::titles t';
    public const array  QUERY_OPTS = array(
        't'   => [['src']],                                 // 11: Type::TITLE
        'src' => ['j' => ['::source src ON `type` = 11 AND `typeId` = t.`id`', true], 's' => ', `src4`, `src12`, `src13`, `moreType`, `moreTypeId`, `moreZoneId`, `moreMask`']
    );

    public function applyInitData(array $initData, array $opts) : void
    {
        parent::applyInitData($initData, $opts);

        $this->cuFlags   = $initData['cuFlags'];
        $this->name      = new LocString($initData, 'male', formatter: self::trim(...));
        $this->male      = new LocString($initData, 'male');
        $this->female    = new LocString($initData, 'female');
        $this->category  = $initData['category'];
        $this->gender    = $initData['gender'];
        $this->side      = $initData['side'];
        $this->expansion = $initData['expansion'];
        $this->eventId   = $initData['eventId'];
        $this->bitIdx    = $initData['bitIdx'];
        $this->src12Ext  = $initData['src12Ext'];

        // preparse sources - notice: under this system titles can't have more than one source (or two for achivements), which is enough for standard TC cases but may break custom cases
        $this->initSources($initData, $initData['src12Ext'] ?: 0);
    }

    public function getListviewRow(int $addInfoMask = 0x0) : array
    {
        $data = array(
            'id'         => $this->id,
            'name'       => $this->male,
            'namefemale' => $this->female,
            'side'       => $this->side,
            'gender'     => $this->gender,
            'expansion'  => $this->expansion,
            'category'   => $this->category
        );

        if ($this->source)
            $data['source'] = $this->source;

        return $data;
    }

    public function getJSGlobal(int $addMask = GLOBALINFO_SELF) : array
    {
        $data[Type::TITLE][$this->id]['name'] = $this->male;

        if (!$this->female->isEmpty())
            $data[Type::TITLE][$this->id]['namefemale'] = $this->female;

        return $data;
    }

    public function getHtmlizedName(int $gender = GENDER_MALE) : string
    {
        return sprintf($this->{$gender == GENDER_FEMALE ? 'female' : 'male'}, '<span class="q0">&lt;'.Util::ucFirst(Lang::main('name')).'&gt;</span>');
    }

    public static function getName(int $id, int $gender = GENDER_MALE) : ?LocString
    {
        $fields = [];
        foreach (Locale::cases() as $loc)
            if ($loc->validate())
                $fields[] = ($gender ? 'female_loc' : 'male_loc') . $loc->value;

        if ($n = DB::Aowow()->selectRow('SELECT %n FROM %n WHERE `id` = %i', $fields, self::$dataTable, $id))
            return new LocString($n, $gender ? 'female' : 'male', self::trim(...));
        return null;
    }

    /**
     * removes name placeholder and separating comma from title
     * @param string    $title  full title string from dbc
     * @return string the pruned title
     */
    public static function trim(string $title) : string
    {
        return trim(strtr($title, ['%s' => '', ',' => '']));
    }
}

?>
