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

    private ItemList $subject;
    private string   $search = '';

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
            $conditions = [['name_loc'.Lang::getLocale()->value, $this->search]];
        else
            $conditions = [['i.id', $this->typeId]];

        $this->subject = new ItemList($conditions);
        if ($this->subject->error)
        {
            $this->cacheType = CACHE_TYPE_NONE;
            header('HTTP/1.0 404 Not Found', true, 404);

            $root = new SimpleXML('<aowow />');
            $root->addChild('error', 'Item not found!');
            $this->result = $root->asXML();

            return;
        }
        else
            $this->typeId = $this->subject->id;

        $root = new SimpleXML('<aowow />');

        // item root
        $xml = $root->addChild('item');
        $xml->addAttribute('id', $this->typeId);

        // name
        $xml->addChild('name')->addCData($this->subject->getField('name', true));
        // itemlevel
        $xml->addChild('level', $this->subject->getField('itemLevel'));
        // quality
        $xml->addChild('quality', Lang::item('quality', $this->subject->getField('quality')))->addAttribute('id', $this->subject->getField('quality'));
        // class
        $x = Lang::item('cat', $this->subject->getField('class'));
        $xml->addChild('class')->addCData(is_array($x) ? $x[0] : $x)->addAttribute('id', $this->subject->getField('class'));
        // subclass
        $xml->addChild('subclass')->addCData($this->getSubclass())->addAttribute('id', $this->subject->getField('subClass'));
        // icon + displayId
        $xml->addChild('icon', $this->subject->getField('iconString'))->addAttribute('displayId', $this->subject->getField('displayId'));
        // inventorySlot
        $xml->addChild('inventorySlot', Lang::item('inventoryType', $this->subject->getField('slot')))->addAttribute('id', $this->subject->getField('slot'));
        // tooltip
        $xml->addChild('htmlTooltip')->addCData($this->subject->renderTooltip());

        $this->subject->extendJsonStats();

        // json
        $fields = ['classs', 'displayid', 'dps', 'id', 'level', 'name', 'reqlevel', 'slot', 'slotbak', 'speed', 'subclass'];
        $json   = [];
        foreach ($fields as $f)
        {
            if (isset($this->subject->json[$this->typeId][$f]))
            {
                $_ = $this->subject->json[$this->typeId][$f];
                if ($f == 'name')
                    $_ = (7 - $this->subject->getField('quality')).$_;

                $json[$f] = $_;
            }
        }

        // itemsource
        if ($this->subject->getSources($s, $sm))
        {
            $json['source'] = $s;
            if ($sm)
                $json['sourcemore'] = $sm;
        }

        $xml->addChild('json')->addCData(substr(json_encode($json), 1, -1));

        // jsonEquip missing: avgbuyout
        $json = [];
        if ($_ = $this->subject->getField('sellPrice'))          // sellprice
            $json['sellprice'] = $_;

        if ($_ = $this->subject->getField('requiredLevel'))      // reqlevel
            $json['reqlevel'] = $_;

        if ($_ = $this->subject->getField('requiredSkill'))      // reqskill
            $json['reqskill'] = $_;

        if ($_ = $this->subject->getField('requiredSkillRank'))  // reqskillrank
            $json['reqskillrank'] = $_;

        if ($_ = $this->subject->getField('cooldown'))           // cooldown
            $json['cooldown'] = $_ / 1000;

        Util::arraySumByKey($json, $this->subject->jsonStats[$this->typeId] ?? []);

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
            [DB::AND, ['effect1CreateItemId', $this->typeId], [DB::OR, ['effect1Id', SpellList::EFFECTS_ITEM_CREATE], ['effect1AuraId', SpellList::AURAS_ITEM_CREATE]]],
            [DB::AND, ['effect2CreateItemId', $this->typeId], [DB::OR, ['effect2Id', SpellList::EFFECTS_ITEM_CREATE], ['effect2AuraId', SpellList::AURAS_ITEM_CREATE]]],
            [DB::AND, ['effect3CreateItemId', $this->typeId], [DB::OR, ['effect3Id', SpellList::EFFECTS_ITEM_CREATE], ['effect3AuraId', SpellList::AURAS_ITEM_CREATE]]],
        );

        $spellSource = new SpellList($cnd);
        if (!$spellSource->error)
        {
            $cbNode = $xml->addChild('createdBy');

            foreach ($spellSource->iterate() as $sId => $__)
            {
                foreach ($spellSource->canCreateItem() as $idx)
                {
                    if ($spellSource->getField('effect'.$idx.'CreateItemId') != $this->typeId)
                        continue;

                    $splNode = $cbNode->addChild('spell');
                    $splNode->addAttribute('id', $sId);
                    $splNode->addAttribute('name', $spellSource->getField('name', true));
                    $splNode->addAttribute('icon', $this->subject->getField('iconString'));
                    $splNode->addAttribute('minCount', $spellSource->getField('effect'.$idx.'BasePoints') + 1);
                    $splNode->addAttribute('maxCount', $spellSource->getField('effect'.$idx.'BasePoints') + $spellSource->getField('effect'.$idx.'DieSides'));

                    foreach ($spellSource->getReagentsForCurrent() as $rId => $qty)
                    {
                        if ($reagent = $spellSource->relItems->getEntry($rId))
                        {
                            $rgtNode = $splNode->addChild('reagent');
                            $rgtNode->addAttribute('id', $rId);
                            $rgtNode->addAttribute('name', Util::localizedString($reagent, 'name'));
                            $rgtNode->addAttribute('quality', $reagent['quality']);
                            $rgtNode->addAttribute('icon', $reagent['iconString']);
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
        $c  = $this->subject->getField('class');
        $sc = $this->subject->getField('subClass');

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
