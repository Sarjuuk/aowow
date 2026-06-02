<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ItemXmlResponse extends TextResponse implements ICache
{
    use TrCache;

    protected string $contentType = MIME_TYPE_XML;
    protected int    $type        = Type::ITEM;
    protected int    $typeId      = 0;
    protected int    $cacheType   = CACHE_TYPE_XML;

    protected array  $expectedGET = array(
        'domain' => ['filter' => FILTER_CALLBACK, 'options' => [Locale::class, 'tryFromDomain']]
    );

    private Item   $subject;
    private string $search = '';

    public function __construct(string $param)
    {
        parent::__construct($param);

        // temp locale
        if ($this->_get['domain'])
            Lang::load($this->_get['domain']);

        // allow lookup by name
        if (is_numeric($param))
            $this->typeId = intVal($param);
        else
            $this->search = urldecode($param);
    }

    protected function generate() : void
    {
        if ($this->search)
        {
            // DBType cant search for strings, so we have to be roundabout
            $container = new ItemContainer(array(['name_loc'.Lang::getLocale()->value, $this->search]));
            if ($container->error)
            {
                $this->handleError();
                return;
            }
            $this->subject = $container->getEntry($container->id);
            $this->typeId  = $this->subject->id;
        }
        else
        {
            $this->subject = new Item($this->typeId);
            if ($this->subject->error)
            {
                $this->handleError();
                return;
            }
        }

        $root = new SimpleXML('<aowow />');

        // item root
        $xml = $root->addChild('item');
        $xml->addAttribute('id', $this->typeId);

        // name
        $xml->addChild('name')->addCData($this->subject->name);
        // itemlevel
        $xml->addChild('level', $this->subject->itemLevel);
        // quality
        $xml->addChild('quality', Lang::item('quality', $this->subject->quality))->addAttribute('id', $this->subject->quality);
        // class
        $x = Lang::item('cat', $this->subject->class);
        $xml->addChild('class')->addCData(is_array($x) ? $x[0] : $x)->addAttribute('id', $this->subject->class);
        // subclass
        $xml->addChild('subclass')->addCData($this->getSubclass())->addAttribute('id', $this->subject->subClass);
        // icon + displayId
        $xml->addChild('icon', $this->subject->icon)->addAttribute('displayId', $this->subject->displayId);
        // inventorySlot
        $xml->addChild('inventorySlot', Lang::item('inventoryType', $this->subject->slot))->addAttribute('id', $this->subject->slot);
        // tooltip
        $xml->addChild('htmlTooltip')->addCData($this->subject->renderTooltip());

        $this->subject->extendJsonStats();

        // json
        $json = array_intersect_key(array_keys($this->subject->json), ['classs', 'displayid', 'dps', 'id', 'level', 'name', 'reqlevel', 'slot', 'slotbak', 'speed', 'subclass']);
        $json['name'] = (7 - $this->subject->quality).$json['name'];

        // itemsource
        // Sources
        if ([$s, $sm] = $this->subject->getSources())
        {
            $json['source'] = $s;
            if ($sm)
                $json['sourcemore'] = $sm;
        }

        $xml->addChild('json')->addCData(substr(json_encode($json), 1, -1));

        // jsonEquip missing: avgbuyout
        $json = [];
        if ($_ = $this->subject->sellPrice)                 // sellprice
            $json['sellprice'] = $_;

        if ($_ = $this->subject->requiredLevel)             // reqlevel
            $json['reqlevel'] = $_;

        if ($_ = $this->subject->requiredSkill)             // reqskill
            $json['reqskill'] = $_;

        if ($_ = $this->subject->requiredSkillRank)         // reqskillrank
            $json['reqskillrank'] = $_;

        if ($_ = $this->subject->cooldown)                  // cooldown
            $json['cooldown'] = $_ / 1000;

        Util::arraySumByKey($json, $this->subject->itemStats->toJson(Stat::FLAG_ITEM, false));

        foreach ($this->subject->json[$this->typeId] as $name => $qty)
            if ($idx = Stat::getIndexFrom(Stat::IDX_JSON_STR, $name))
                if (Stat::getFilterCriteriumId($idx))
                    $json[$name] = $qty;

        $xml->addChild('jsonEquip')->addCData(substr(json_encode($json), 1, -1));

        // jsonUse
        if ($onUse = $this->subject->getOnUseStats())
        {
            $j = '';
            foreach ($onUse->toJson(includeEmpty: false) as $key => $amt)
                $j .= ',"'.$key.'":'.$amt;

            $xml->addChild('jsonUse')->addCData(substr($j, 1));
        }

        // reagents
        $cnd = array(
            DB::OR,
            [DB::AND, ['effect1CreateItemId', $this->typeId], [DB::OR, ['effect1Id', Spell::EFFECTS_ITEM_CREATE], ['effect1AuraId', Spell::AURAS_ITEM_CREATE]]],
            [DB::AND, ['effect2CreateItemId', $this->typeId], [DB::OR, ['effect2Id', Spell::EFFECTS_ITEM_CREATE], ['effect2AuraId', Spell::AURAS_ITEM_CREATE]]],
            [DB::AND, ['effect3CreateItemId', $this->typeId], [DB::OR, ['effect3Id', Spell::EFFECTS_ITEM_CREATE], ['effect3AuraId', Spell::AURAS_ITEM_CREATE]]],
        );

        $spellSource = new SpellContainer($cnd);
        if (!$spellSource->error)
        {
            $cbNode = $xml->addChild('createdBy');

            foreach ($spellSource->iterate() as $sId => $spellEntry)
            {
                foreach ($spellEntry->canCreateItem() as $idx)
                {
                    if ($spellEntry->effectCreateItemId[$idx] != $this->typeId)
                        continue;

                    $splNode = $cbNode->addChild('spell');
                    $splNode->addAttribute('id', $sId);
                    $splNode->addAttribute('name', $spellEntry->name);
                    $splNode->addAttribute('icon', $this->subject->icon);
                    $splNode->addAttribute('minCount', $spellEntry->effectBasePoints[$idx] + 1);
                    $splNode->addAttribute('maxCount', $spellEntry->effectBasePoints[$idx] + $spellEntry->effectDieSides[$idx]);

                    $reagents = new ItemContainer(array(['id', array_keys($spellEntry->getReagents())]));

                    foreach ($spellEntry->getReagents() as [$rId, $qty])
                    {
                        if ($reagent = $reagents->getEntry($rId))
                        {
                            $rgtNode = $splNode->addChild('reagent');
                            $rgtNode->addAttribute('id', $rId);
                            $rgtNode->addAttribute('name', $reagent->name);
                            $rgtNode->addAttribute('quality', $reagent->quality);
                            $rgtNode->addAttribute('icon', $reagent->icon);
                            $rgtNode->addAttribute('count', $qty[1]);
                        }
                    }

                    break;
                }
            }
        }

        // link
        $xml->addChild('link', Cfg::get('HOST_URL').'?item='.$this->typeId);

        $this->result = $root->asXML();
    }

    private function getSubclass() : string
    {
        $c  = $this->subject->class;
        $sc = $this->subject->subClass;

        if ($c == ITEM_CLASS_WEAPON)
            $langRef = Lang::spell('weaponSubClass');
        else
            $langRef = Lang::item('cat', $c, 1);

        if (!is_array($langRef))
            return Lang::item('cat', $c);

        if (is_array($langRef[$sc]))
            return $langRef[$sc][0];

        return $langRef[$sc];
    }

    private function handleError() : void
    {
        $this->cacheType = CACHE_TYPE_NONE;
        header('HTTP/1.0 404 Not Found', true, 404);

        $root = new SimpleXML('<aowow />');
        $root->addChild('error', 'Item not found!');
        $this->result = $root->asXML();
    }

    public function getCacheKeyComponents() : array
    {
        return array(
            $this->type,                                    // DBType
            $this->typeId,                                  // DBTypeId/category
            -1,                                             // staff mask (content does not diff)
            ''                                              // misc (unused)
        );
    }
}

?>
