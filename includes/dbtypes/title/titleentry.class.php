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

    public static int    $dbType    = Type::TITLE;
    public static string $brickFile = 'title';
    public static string $dataTable = '::titles';

    public const /* string */ QUERY_BASE = 'SELECT t.*, t.`id` AS ARRAY_KEY FROM ::titles t';
    public const /* array  */ QUERY_OPTS = array(
        't'   => [['src']],                                 // 11: Type::TITLE
        'src' => ['j' => ['::source src ON `type` = 11 AND `typeId` = t.`id`', true], 's' => ', `src13`, `moreType`, `moreTypeId`, `moreZoneId`, `moreMask`']
    );

    public function applyInitData(array $initData) : void
    {
        parent::applyInitData($initData);

        $this->initSources($initData);

        $this->name   = new LocString($initData, 'male',   formatter: self::trim(...));
        $this->male   = new LocString($initData, 'male',   pruneFromSrc: true);
        $this->female = new LocString($initData, 'female', pruneFromSrc: true);

        // preparse sources - notice: under this system titles can't have more than one source (or two for achivements), which is enough for standard TC cases but may break custom cases
        if ($this->moreType == Type::ACHIEVEMENT)
            $this->sources[SRC_ACHIEVEMENT][] = $this->moreTypeId;
        if ($this->moreType == Type::QUEST)
            $this->sources[SRC_QUEST][] = $this->moreTypeId;

        // titles display up to two achievements at once
        if ($initData['src12Ext'])
            $this->sources[SRC_ACHIEVEMENT][] = $initData['src12Ext'];

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
        $data = [];

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
            $data[$this->id]['source'] = $this->source;

        return $data;
    }

    public function getJSGlobal(int $addMask = 0) : array
    {
        $data[Type::TITLE][$this->id]['name'] = $this->male;

        if (!$this->female->isEmpty())
            $data[Type::TITLE][$this->id]['namefemale'] = $this->female;

        return $data;
    }

    public function renderTooltip() : ?string { return null; }

    public function getHtmlizedName(int $gender = GENDER_MALE) : string
    {
        return sprintf($this->${$gender == GENDER_FEMALE ? 'female' : 'male'}, '<span class="q0">&lt;'.Util::ucFirst(Lang::main('name')).'&gt;</span>');
    }

    public static function getName(int $id) : ?LocString
    {
        if ($n = DB::Aowow()->SelectRow('SELECT `male_loc0`, `male_loc2`, `male_loc3`, `male_loc4`, `male_loc6`, `male_loc8` FROM %n WHERE `id` = %i', self::$dataTable, $id))
            return new LocString($n, 'male', self::trim(...));
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
