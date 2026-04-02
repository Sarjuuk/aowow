<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SkillEntry extends DBTypeEntry
{
    public readonly int       $cuFlags;
    public readonly LocString $name;
    public readonly LocString $description;
    public readonly int       $typeCat;
    public readonly int       $categoryId;
    public readonly int       $iconId;
    public readonly string    $icon;
 // public readonly int       $iconIdBak;
 // public readonly string    $iconBak;
    public readonly int       $professionMask;
    public readonly int       $recipeSubClass;
    /** @var int[] $specializations */
    public readonly array     $specializations;
    public readonly int       $expansion;

    public static int    $dbType    = Type::SKILL;
    public static string $brickFile = 'skill';
    public static string $dataTable = '::skillline';

    public const string QUERY_BASE = 'SELECT sl.*, sl.`id` AS ARRAY_KEY FROM ::skillline sl';
    public const array  QUERY_OPTS = array(
        'sl' => [['ic']],
        'ic' => ['j' => ['::icons ic ON ic.`id` = sl.`iconId`', true], 's' => ', ic.`name` AS "icon"'],
    );

    public function applyInitData(array $initData, array $opts) : void
    {
        parent::applyInitData($initData, $opts);

        $this->cuFlags         = $initData['cuFlags'];
        $this->name            = new LocString($initData, 'name');
        $this->description     = new LocString($initData, 'description');
        $this->typeCat         = $initData['typeCat'];
        $this->categoryId      = $initData['categoryId'];
        $this->iconId          = $initData['iconId'];
        $this->icon            = $initData['icon'] ?: DEFAULT_ICON;;
        $this->professionMask  = $initData['professionMask'];
        $this->recipeSubClass  = $initData['recipeSubClass'];
        $this->specializations = array_pad(explode(' ', $initData['specializations']), 5, 0);
        $this->expansion       = EXP_CLASSIC;               // todo: not yet set in DB
    }

    public function getListviewRow(int $addInfoMask = 0x0) : array
    {
        $data = array(
            'category'        => $this->typeCat,
            'categorybak'     => $this->categoryId,
            'id'              => $this->id,
            'name'            => $this->name,
            'profession'      => $this->professionMask,
            'recipeSubclass'  => $this->recipeSubClass,
            'specializations' => $this->specializations,    // included but unused by Listview. Maybe some column WH removed but forgot to remove the data for..?
            'icon'            => $this->icon
        );

        if ($this->expansion)
            $data['expansion'] = $this->expansion;

        return $data;
    }

    public function getJSGlobal(int $addMask = GLOBALINFO_SELF) : array
    {
        return [self::$dbType => [$this->id => array(
            'name' => $this->name,
            'icon' => $this->icon
        )]];
    }
}

?>
