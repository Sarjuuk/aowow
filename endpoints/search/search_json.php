<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
    => search by compare or profiler (only items + itemsets)
    array:[
        searchString,
        [itemData],
        [itemsetData]
    ]
*/


class SearchJsonResponse extends TextResponse implements ICache
{
    use TrCache, TrSearch;

    protected int   $cacheType   = CACHE_TYPE_SEARCH;

    protected array $expectedGET = array(
        'search' => ['filter' => FILTER_CALLBACK,     'options' => [self::class, 'checkTextLine']                           ],
        'wt'     => ['filter' => FILTER_CALLBACK,     'options' => [self::class, 'checkIntArray']                           ],
        'wtv'    => ['filter' => FILTER_CALLBACK,     'options' => [self::class, 'checkIntArray']                           ],
        'slots'  => ['filter' => FILTER_CALLBACK,     'options' => [self::class, 'checkIntArray']                           ],
        'type'   => ['filter' => FILTER_VALIDATE_INT, 'options' => ['min_range' => Type::ITEM, 'max_range' => Type::ITEMSET]]
    );

    private array $extraOpts = [];                          // for weighted search
    private array $extraCnd  = [];                          // for weighted search

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);

        $this->query = $this->_get['search'];               // technically rawParam, but prepared

        if ($this->_get['wt'] && $this->_get['wtv'])        // slots and type should get ignored
        {
            $itemFilter = new ItemListFilter($this->_get);
            if ($_ = $itemFilter->createConditionsForWeights())
            {
                $this->extraOpts  = $itemFilter->extraOpts;
                $this->extraCnd[] = $_;
            }
        }

        if ($_ = array_filter($this->_get['slots'] ?? []))
            $this->extraCnd[] = ['slot', $_];

        $this->searchMask = Search::TYPE_JSON;
        if ($this->_get['slots'] || $this->_get['type'] == Type::ITEM)
            $this->searchMask |= 1 << Search::MOD_ITEM;
        else if ($this->_get['type'] == Type::ITEMSET)
            $this->searchMask |= 1 << Search::MOD_ITEM | 1 << Search::MOD_ITEMSET;

        $this->searchObj = new Search($this->query, $this->searchMask, $this->extraCnd, $this->extraOpts);
    }

    // !note! dear reader, if you ever try to generate a string, that is to be evaled by JS, NEVER EVER terminate with a \n   .....   $totalHoursWasted +=2;
    protected function generate() : void
    {
        $outItems = [];
        $outSets  = [];

        // invalid conditions: not enough characters to search OR no types to search
        if (!$this->searchObj->canPerform())
            $this->generate404($this->query);

        foreach ($this->searchObj->perform() as $modId => $data)
        {
            if ($modId == Search::MOD_ITEM)
                $outItems = $data;
            else if ($modId == Search::MOD_ITEMSET)
                $outSets = $data;
        }

        $this->result = Util::toJSON([$this->query, $outItems, $outSets]);
    }

    public function generate404(?string $search = ''): never
    {
        parent::generate404(Util::toJSON([$search, [], []]));
    }
}

?>
