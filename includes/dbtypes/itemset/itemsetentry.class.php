<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ItemsetEntry extends DBTypeEntry
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
    public        array  $pieceToSet = [];                  // used to build g_items and search


    public const /* string */ QUERY_BASE  = 'SELECT `set`.*, `set`.`id` AS ARRAY_KEY FROM ::itemset `set`';
    public const /* array  */ QUERY_OPTS  = array(
        'set' => ['o' => 'maxlevel DESC'],
        'e'   => ['j' => ['::events e ON `e`.`id` = `set`.`eventId`', true], 's' => ', e.`holidayId`'],
        'src' => ['j' => ['::source src ON `src`.`typeId` = `set`.`id` AND `src`.`type` = 4', true], 's' => ', `src1`, `src2`, `src3`, `src4`, `src5`, `src6`, `src7`, `src8`, `src9`, `src10`, `src11`, `src12`, `src13`, `src14`, `src15`, `src16`, `src17`, `src18`, `src19`, `src20`, `src21`, `src22`, `src23`, `src24`']
    );

    public function applyInitData(array $initData) : void
    {
        parent::applyInitData($initData);

        $this->initSources($initData);

        $this->name   = new LocString($initData, 'name', pruneFromSrc: true);

        $this->items  = [$initData['item1'],  $initData['item2'],  $initData['item3'],  $initData['item4'],  $initData['item5'],  $initData['item6'],  $initData['item7'],  $initData['item8'],  $initData['item9'],  $initData['item10']];
        $this->spells = [$initData['spell1'], $initData['spell2'], $initData['spell3'], $initData['spell4'], $initData['spell5'], $initData['spell6'], $initData['spell7'], $initData['spell8']];
        $this->boni   = [$initData['bonus1'], $initData['bonus2'], $initData['bonus3'], $initData['bonus4'], $initData['bonus5'], $initData['bonus6'], $initData['bonus7'], $initData['bonus8']];

        foreach ($initData as $k => $v)
        {
            switch ($k)
            {
                case 'id':                                  // id defined by parent
                    continue 2;
                case 'classMask':                           // prepare required classes
                    $this->$k = ($_ = $v & ChrClass::MASK_ALL) == ChrClass::MASK_ALL ? 0 : $_;
                    break;
                default:
                    if (property_exists($this, $k))
                        $this->$k = $v;
            }
        }

        $this->classes = ChrClass::fromMask($this->classMask);

        $_ = array_filter($this->items);
        $this->pieceToSet = array_combine($_, array_fill(0, count($_), $this->id));
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
            'pieces'   => array_filter($this->items),
            'heroic'   => $this->heroic
        );
    }

    public function getJSGlobal(int $addMask = GLOBALINFO_ANY) : array
    {
        $data = [];

        if ($this->classes && ($addMask & GLOBALINFO_RELATED))
            $data[Type::CHR_CLASS] = array_combine($this->classes, $this->classes);

        if ($this->pieceToSet && ($addMask & GLOBALINFO_SELF))
            $data[Type::ITEM] = array_combine(array_keys($this->pieceToSet), array_keys($this->pieceToSet));

        if ($addMask & GLOBALINFO_SELF)
            $data[Type::ITEMSET][$this->id] = ['name' => $this->name];

        return $data;
    }

    public function renderTooltip() : ?string
    {
        if (!$this->curTpl)
            return null;

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
