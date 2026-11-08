<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class FactionEntry extends DBTypeEntry
{
    public readonly int       $cuFlags;
    public readonly LocString $name;
    public readonly int       $parentFactionId;
    public readonly int       $category;
    public readonly int       $category2;
    public readonly int       $reputationIndex;
    public readonly int       $expansion;
    public readonly int       $side;
    public readonly float     $spilloverRateIn;
    public readonly float     $spilloverRateOut;
    public readonly int       $spilloverMaxRank;
    /** @var int[] $templateIds faction template ids */
    public readonly array     $templateIds;
    /** @var int[] $qmNpcIds quarter master creature ids */
    public readonly array     $qmNpcIds;

    public static int    $dbType    = Type::FACTION;
    public static string $brickFile = 'faction';
    public static string $dataTable = '::factions';

    public const string QUERY_BASE = 'SELECT f.*, f.`parentFactionId` AS "category", f.`id` AS ARRAY_KEY FROM ::factions f';
    public const array  QUERY_OPTS = array(
        'f'  => [['f2']],
        'f2' => ['j' => ['::factions f2 ON f.`parentFactionId` = f2.`id`', true], 's' => ', IFNULL(f2.`parentFactionId`, 0) AS "category2"'],
        'ft' => ['j' => '::factiontemplate ft ON ft.`factionId` = f.`id`']
    );

    public function applyInitData(\Dibi\Row $dbRow, array $opts) : bool
    {
        parent::applyInitData($dbRow, $opts);

        $this->cuFlags         = $dbRow->cuFlags;
        $this->name            = new LocString($dbRow, 'name');
        $this->parentFactionId = $dbRow->parentFactionId;
        $this->reputationIndex = $dbRow->repIdx;
        $this->expansion       = $dbRow->expansion;
        $this->side            = $dbRow->side;

        // why.? who the fuck knows at this point!
        $this->category  = $dbRow->category2 ? $dbRow->category  : 0;
        $this->category2 = $dbRow->category2 ? $dbRow->category2 : $dbRow->category;

        $this->spilloverRateIn  = $dbRow->spilloverRateIn;
        $this->spilloverRateOut = $dbRow->spilloverRateOut;
        $this->spilloverMaxRank = $dbRow->spilloverMaxRank;

        $this->templateIds = explode(' ', $dbRow->templateIds);
        $this->qmNpcIds    = explode(' ', $dbRow->qmNpcIds);

        return true;
    }

    public function getListviewRow(int $addInfoMask = 0x0) : array
    {
        return array(
            'expansion' => $this->expansion,
            'id'        => $this->id,
            'side'      => $this->side,
            'name'      => $this->name,
            'category'  => $this->category,
            'category2' => $this->category2
        );
    }

    public function getJSGlobal(int $addMask = GLOBALINFO_SELF) : array
    {
        return [self::$dbType => [$this->id => array(
            'name' => $this->name
        )]];
    }
}

?>
