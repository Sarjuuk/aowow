<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SkillEntry extends DBTypeEntry
{
    public readonly int       $cuFlags;
    public readonly LocString $name;
    public readonly LocString $description;
    public readonly int       $parentFactionId;
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

    public const /* string */ QUERY_BASE = 'SELECT sl.*, sl.`id` AS ARRAY_KEY FROM ::skillline sl';
    public const /* array  */ QUERY_OPTS = array(
        'sl' => [['ic']],
        'ic' => ['j' => ['::icons ic ON ic.`id` = sl.`iconId`', true], 's' => ', ic.`name` AS "icon"'],
    );

    public function applyInitData(array $initData) : void
    {
        parent::applyInitData($initData);

        $this->name        = new LocString($initData, 'name',        pruneFromSrc: true);
        $this->description = new LocString($initData, 'description', pruneFromSrc: true);

        $this->expansion = EXP_CLASSIC;                     // todo: not yet set in DB

        foreach ($initData as $k => $v)
        {
            switch ($k)
            {
                case 'id':                                  // id defined by parent
                    continue 2;
                case 'icon':                                // fix missing icons
                    $this->$k = $v ?: DEFAULT_ICON;
                    break;
                case 'specializations':
                    $this->$k = array_pad(explode(' ', $v), 5, 0);
                    break;
                default:
                    if (property_exists($this, $k))
                        $this->$k = $v;
            }
        }
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

    public function getJSGlobal(int $addMask = 0) : array
    {
        return [self::$dbType => [$this->id => array(
            'name' => $this->name,
            'icon' => $this->icon
        )]];
    }

    public function renderTooltip() : ?string { return null; }
}

?>
