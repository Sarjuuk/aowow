<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ItemsetEntry extends DBTypeEntry implements ITooltip
{
    use TrSourceHelper;

    public readonly  int            $cuFlags;
    public readonly  LocString      $name;
    public readonly  LocString      $bonusText;
    public readonly  int            $refSetId;
    public readonly  int            $npieces;
    public readonly  int            $minLevel;
    public readonly  int            $maxLevel;
    public readonly  int            $minReqLevel;
    public readonly  int            $maxReqLevel;
    public readonly  int            $classMask;
    public readonly  array          $classes;
    public readonly  bool           $heroic;
    public readonly  int            $quality;
    public readonly  int            $type;
    public readonly  int            $contentGroup;          // or 'tag'
    public readonly  int            $eventId;
    public readonly  int            $holidayId;
    public readonly  int            $skillId;
    public readonly  int            $skillLevel;
    public readonly  int            $expansion;
    public readonly  int            $side;
    /** @var int[] $items - length: 10 */
    public readonly  array          $items;
    /** @var int[] $spells - length: 8 */
    public readonly  array          $spells;
    /** @var int[] $boni - length: 8 */
    public readonly  array          $boni;

    public static int    $dbType     = Type::ITEMSET;
    public static string $brickFile  = 'itemset';
    public static string $dataTable  = '::itemset';
    public        array  $pieceToSet = [];                  // used in search


    public const string QUERY_BASE  = 'SELECT `set`.*, `set`.`id` AS ARRAY_KEY FROM ::itemset `set`';
    public const array  QUERY_OPTS  = array(
        'set' => [['src'], 'o' => 'maxlevel DESC'],
        'e'   => ['j' => ['::events e ON `e`.`id` = `set`.`eventId`', true], 's' => ', e.`holidayId`'],
        'src' => ['j' => ['::source src ON `src`.`typeId` = `set`.`id` AND `src`.`type` = 4', true], 's' => ', `moreType`, `moreTypeId`, `moreZoneId`, `moreMask`, `src1`, `src2`, `src3`, `src4`, `src5`, `src6`, `src7`, `src8`, `src9`, `src10`, `src11`, `src12`, `src13`, `src14`, `src15`, `src16`, `src17`, `src18`, `src19`, `src20`, `src21`, `src22`, `src23`, `src24`']
    );

    public function applyInitData(array $initData, array $opts) : void
    {
        parent::applyInitData($initData, $opts);

        $this->cuFlags      = $initData['cuFlags'];
        $this->name         = new LocString($initData, 'name');
        $this->bonusText    = new LocString($initData, 'bonusText');
        $this->refSetId     = $initData['refSetId'];
        $this->npieces      = $initData['npieces'];
        $this->minLevel     = $initData['minLevel'];
        $this->maxLevel     = $initData['maxLevel'];
        $this->minReqLevel  = $initData['minReqLevel'];
        $this->maxReqLevel  = $initData['maxReqLevel'];
        $this->classMask    = ($_ = $initData['classMask'] & ChrClass::MASK_ALL) == ChrClass::MASK_ALL ? 0 : $_;
        $this->classes      = ChrClass::fromMask($initData['classMask']);;
        $this->heroic       = $initData['heroic'];
        $this->quality      = $initData['quality'];
        $this->type         = $initData['type'];
        $this->contentGroup = $initData['contentGroup'];
        $this->eventId      = $initData['eventId'];
        $this->holidayId    = $initData['holidayId'] ?? 0;
        $this->skillId      = $initData['skillId'];
        $this->skillLevel   = $initData['skillLevel'];
        $this->expansion    = $initData['expansion'];
        $this->side         = $initData['side'];

        $this->items  = array_filter([$initData['item1'],  $initData['item2'],  $initData['item3'],  $initData['item4'],  $initData['item5'],  $initData['item6'],  $initData['item7'],  $initData['item8'],  $initData['item9'],  $initData['item10']]);
        $this->spells = [$initData['spell1'], $initData['spell2'], $initData['spell3'], $initData['spell4'], $initData['spell5'], $initData['spell6'], $initData['spell7'], $initData['spell8']];
        $this->boni   = [$initData['bonus1'], $initData['bonus2'], $initData['bonus3'], $initData['bonus4'], $initData['bonus5'], $initData['bonus6'], $initData['bonus7'], $initData['bonus8']];

        $this->pieceToSet = array_combine($this->items, array_fill(0, count($this->items), $this->id));

        $this->initSources($initData);
    }

    public function getListviewRow(int $addInfoMask = 0x0) : array
    {
        return array(
            'id'       => $this->id,
            'idbak'    => $this->refSetId,
            'name'     => (7 - $this->quality).$this->name,
            'minlevel' => $this->minLevel,
            'maxlevel' => $this->maxLevel,
            'note'     => $this->contentGroup,
            'type'     => $this->type,
            'reqclass' => $this->classMask,
            'classes'  => $this->classes,
            'pieces'   => $this->items,
            'heroic'   => $this->heroic
        );
    }

    /**
     * @param int $addMask mask of data to be exposed to javascript
     *  - always add itemset and contained items to @return
     *  - GLOBALINFO_RELATED - add required classes to @return
     *
     * @return array<int, array<int, int|array>[]>[] [type => [typeId => placeholder or data]]
     */
    public function getJSGlobal(int $addMask = GLOBALINFO_SELF) : array
    {
        $data[self::$dbType][$this->id] = ['name' => $this->name];

        if ($this->items)
            $data[Type::ITEM] = array_combine($this->items, $this->items);

        if ($this->classes && ($addMask & GLOBALINFO_RELATED))
            $data[Type::CHR_CLASS] = array_combine($this->classes, $this->classes);

        return $data;
    }

    public function renderTooltip() : ?string
    {
        $x  = '<table><tr><td>';
        $x .= '<span class="q'.$this->quality.'">'.$this->name.'</span><br />';

        if ($_ = $this->classMask)
        {
            $jsg = [];
            $cl  = Lang::getClassString($_, $jsg);
            $t   = count($jsg) == 1 ? Lang::game('class') : Lang::game('classes');
            $x  .= Util::ucFirst($t).Lang::main('colon').$cl.'<br />';
        }

        if ($_ = $this->contentGroup)
            $x .= Lang::itemset('notes', $_).($this->heroic ? ' <i class="q2">('.Lang::item('heroic').')</i>' : '').'<br />';
        else if ($this->type)
            $x.= Lang::itemset('types', $this->type).'<br />';

        if ($bonuses = $this->getBonuses())
        {
            $x .= '<span>';

            foreach ($bonuses as [$nItems, , $text])
                $x .= '<br /><span class="q13">'.Lang::itemset('_pieces', [$nItems]).'</span>'.$text;

            $x .= '</span>';
        }

        $x .= '</td></tr></table>';

        return $x;
    }

    public function getBonuses() : array
    {
        if (!($spellIds = array_filter($this->spells)))
            return [];

        $result    = [];
        $setSpells = new SpellContainer(array(['id', $spellIds]));

        foreach ($spellIds as $i => $id)
        {
            if ($entry = $setSpells->getEntry($id))
                $txt = $entry->renderText('description', $this->maxReqLevel ?: MAX_LEVEL)[0];
            else
                $txt = Lang::spell('unkAura', [$id]);

            $result[] = [$this->boni[$i], $id, $txt];
        }

        // sort by required pieces ASC
        usort($result, fn(array $a, array $b) => $a[0] <=> $b[0]);

        return $result;
    }
}

?>
