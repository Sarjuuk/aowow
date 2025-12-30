<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class Search
{
    public const /* int */ DEFAULT_MAX_RESULTS     = 500;
    public const /* int */ SUGGESTIONS_MAX_RESULTS = 10;

    public const /* int */ MOD_CLASS            = 0;
    public const /* int */ MOD_RACE             = 1;
    public const /* int */ MOD_TITLE            = 2;
    public const /* int */ MOD_WORLDEVENT       = 3;
    public const /* int */ MOD_CURRENCY         = 4;
    public const /* int */ MOD_ITEMSET          = 5;        // must come before MOD_ITEM as its pieces may also be returned in result set
    public const /* int */ MOD_ITEM             = 6;
    public const /* int */ MOD_ABILITY          = 7;
    public const /* int */ MOD_TALENT           = 8;
    public const /* int */ MOD_GLYPH            = 9;
    public const /* int */ MOD_PROFICIENCY      = 10;
    public const /* int */ MOD_PROFESSION       = 11;
    public const /* int */ MOD_COMPANION        = 12;
    public const /* int */ MOD_MOUNT            = 13;
    public const /* int */ MOD_CREATURE         = 14;
    public const /* int */ MOD_QUEST            = 15;
    public const /* int */ MOD_ACHIEVEMENT      = 16;
    public const /* int */ MOD_STATISTIC        = 17;
    public const /* int */ MOD_ZONE             = 18;
    public const /* int */ MOD_OBJECT           = 19;
    public const /* int */ MOD_FACTION          = 20;
    public const /* int */ MOD_SKILL            = 21;
    public const /* int */ MOD_PET              = 22;
    public const /* int */ MOD_CREATURE_ABILITY = 23;
    public const /* int */ MOD_SPELL            = 24;
    public const /* int */ MOD_EMOTE            = 25;
    public const /* int */ MOD_ENCHANTMENT      = 26;
    public const /* int */ MOD_SOUND            = 27;

    public const /* int */ TYPE_REGULAR = 0x10000000;
    public const /* int */ TYPE_OPEN    = 0x20000000;
    public const /* int */ TYPE_JSON    = 0x40000000;

    private const /* array */ MODULES = array(
        self::MOD_CLASS            => '_searchCharClass',
        self::MOD_RACE             => '_searchCharRace',
        self::MOD_TITLE            => '_searchTitle',
        self::MOD_WORLDEVENT       => '_searchWorldEvent',
        self::MOD_CURRENCY         => '_searchCurrency',
        self::MOD_ITEMSET          => '_searchItemset',
        self::MOD_ITEM             => '_searchItem',
        self::MOD_ABILITY          => '_searchAbility',
        self::MOD_TALENT           => '_searchTalent',
        self::MOD_GLYPH            => '_searchGlyph',
        self::MOD_PROFICIENCY      => '_searchProficiency',
        self::MOD_PROFESSION       => '_searchProfession',
        self::MOD_COMPANION        => '_searchCompanion',
        self::MOD_MOUNT            => '_searchMount',
        self::MOD_CREATURE         => '_searchCreature',
        self::MOD_QUEST            => '_searchQuest',
        self::MOD_ACHIEVEMENT      => '_searchAchievement',
        self::MOD_STATISTIC        => '_searchStatistic',
        self::MOD_ZONE             => '_searchZone',
        self::MOD_OBJECT           => '_searchObject',
        self::MOD_FACTION          => '_searchFaction',
        self::MOD_SKILL            => '_searchSkill',
        self::MOD_PET              => '_searchPet',
        self::MOD_CREATURE_ABILITY => '_searchCreatureAbility',
        self::MOD_SPELL            => '_searchSpell',
        self::MOD_EMOTE            => '_searchEmote',
        self::MOD_ENCHANTMENT      => '_searchEnchantment',
        self::MOD_SOUND            => '_searchSound'
    );

    private array $jsgStore    = [];
    private array $resultStore = [];
    private array $included    = [];
    private array $excluded    = [];
    private array $cndBase     = ['AND'];
    private bool  $idSearch    = false;

    public array $invalid = [];

    public function __construct(private string $query, private int $moduleMask = -1, private array $extraCnd = [], private array $extraOpts = [], private int $maxResults = self::DEFAULT_MAX_RESULTS)
    {
        $this->tokenizeQuery();

        $this->cndBase[] = $this->maxResults;

        // Exclude internal wow stuff
        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $this->cndBase[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];
    }

    private function tokenizeQuery() : void
    {
        if (!$this->query)
            return;

        if (Util::checkNumeric($this->query, NUM_CAST_INT))
        {
            $this->idSearch   = true;
            $this->included[] = $this->query;
            return;
        }

        foreach (explode(' ', $this->query) as $raw)
        {
            $clean = str_replace(['\\', '%'], '', $raw);

            if ($clean === '')
                continue;

            if ($clean[0] == '-')
            {
                if (mb_strlen($clean) < 4 && !Lang::getLocale()->isLogographic())
                    $this->invalid[] = mb_substr($raw, 1);
                else
                    $this->excluded[] = mb_substr(str_replace('_', '\\_', $clean), 1);
            }
            else
            {
                if (mb_strlen($clean) < 3 && !Lang::getLocale()->isLogographic())
                    $this->invalid[] = $raw;
                else
                    $this->included[] = str_replace('_', '\\_', $clean);
            }
        }
    }

    private function createLookup(array $fields = []) : array
    {
        if ($this->idSearch && $this->included)
            return ['id', $this->included];

        if (!$this->included && !$this->excluded)
            return [];

        // default to name-field
        if (!$fields)
            $fields[] = 'name_loc'.Lang::getLocale()->value;

        $qry = [];
        foreach ($fields as $f)
        {
            $sub = [];
            foreach ($this->included as $i)
                $sub[] = [$f, '%'.$i.'%'];

            foreach ($this->excluded as $x)
                $sub[] = [$f, '%'.$x.'%', '!'];

            // single cnd?
            if (count($sub) > 1)
                array_unshift($sub, 'AND');
            else
                $sub = $sub[0];

            $qry[] = $sub;
        }

        // single cnd?
        if (count($qry) > 1)
            array_unshift($qry, 'OR');
        else
            $qry = $qry[0];

        return $qry;
    }

    public function canPerform() : bool
    {
        // has valid search terms or weights and selected modules
        return (($this->included || $this->extraOpts)) && $this->moduleMask;
    }

    public function perform() : \Generator
    {
        $shared = [];
        foreach (self::MODULES as $idx => $ref)
        {
            if (!($this->moduleMask & (1 << $idx)))
                continue;

            $this->resultStore[$idx] ??= $this->$ref($shared);

            if (!$this->resultStore[$idx])
                continue;

            yield $idx => $this->resultStore[$idx];
        }
    }

    public function getJSGlobals() : array
    {
        return $this->jsgStore;
    }


    /******************/
    /* Search Modules */
    /******************/

    private function _searchCharClass() : ?array            // 0 Classes: $moduleMask & 0x00000001
    {
        $cnd     = array_merge($this->cndBase, [$this->createLookup()]);
        $classes = new CharClassList($cnd, ['calcTotal' => true]);

        $data = $classes->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            $lvData = ['data' => $data];

            if ($classes->getMatches() > $this->maxResults)
            {
                // $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_', $classes->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            return [$lvData, CharClassList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::CHR_CLASS, $classes->getMatches(), [], [], 'Class'];

            foreach ($classes->iterate() as $id => $__)
            {
                $result[$id]    = $classes->getField('name', true);
                $osInfo[2][$id] = 'class_'.strToLower($classes->getField('fileString'));
            }

            return [$result, ...$osInfo];
        }

        return null;
    }

    private function _searchCharRace() : ?array             // 1 Races: $moduleMask & 0x00000002
    {
        $cnd   = array_merge($this->cndBase, [$this->createLookup()]);
        $races = new CharRaceList($cnd, ['calcTotal' => true]);

        $data = $races->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            $lvData = ['data' => $data];

            if ($races->getMatches() > $this->maxResults)
            {
                // $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_', $races->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            return [$lvData, CharRaceList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::CHR_RACE, $races->getMatches(), [], [], 'Race'];

            foreach ($races->iterate() as $id => $__)
            {
                $result[$id]    = $races->getField('name', true);
                $osInfo[2][$id] = 'race_'.strToLower($races->getField('fileString')).'_male';
            }

            return [$result, ...$osInfo];
        }

        return null;
    }

    private function _searchTitle() : ?array                // 2 Titles: $moduleMask & 0x00000004
    {
        $cnd    = array_merge($this->cndBase, [$this->createLookup(['male_loc'.Lang::getLocale()->value, 'female_loc'.Lang::getLocale()->value])]);
        $titles = new TitleList($cnd, ['calcTotal' => true]);

        $data = $titles->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            $lvData = ['data' => $data];

            if ($titles->getMatches() > $this->maxResults)
            {
                // $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_', $titles->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            return [$lvData, TitleList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::TITLE, $titles->getMatches(), [], [], 'Title'];

            foreach ($titles->iterate() as $id => $__)
            {
                $result[$id]    = $titles->getField('male', true);
                $osInfo[2][$id] = $titles->getField('side');
            }

            return [$result, ...$osInfo];
        }

        return null;
    }

    private function _searchWorldEvent() : ?array           // 3 World Events: $moduleMask & 0x00000008
    {
        $cnd     = array_merge($this->cndBase, array(
            array(
                'OR',
                $this->createLookup(['h.name_loc'.Lang::getLocale()->value]),
                ['AND', $this->createLookup(['e.description']), ['e.holidayId', 0]]
            )
        ));
        $wEvents = new WorldEventList($cnd, ['calcTotal' => true]);

        $data = $wEvents->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            Util::mergeJsGlobals($this->jsgStore, $wEvents->getJSGlobals());

            // as allways: dates are updated in postCache-step
            $lvData = ['data' => $data];

            if ($wEvents->getMatches() > $this->maxResults)
            {
                // $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_', $wEvents->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            return [$lvData, WorldEventList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::WORLDEVENT, $wEvents->getMatches(), [], [], 'World Event'];

            foreach ($wEvents->iterate() as $id => $__)
                $result[$id]    = $wEvents->getField('name', true);

            return [$result, ...$osInfo];
        }

        return null;
    }

    private function _searchCurrency() : ?array             // 4 Currencies $moduleMask & 0x0000010
    {
        $cnd   = array_merge($this->cndBase, [$this->createLookup()]);
        $money = new CurrencyList($cnd, ['calcTotal' => true]);

        $data = $money->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            $lvData = ['data' => $data];

            if ($money->getMatches() > $this->maxResults)
            {
                $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_currenciesfound', $money->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            return [$lvData, CurrencyList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::CURRENCY, $money->getMatches(), [], [], 'Currency'];

            foreach ($money->iterate() as $id => $__)
            {
                $result[$id]    = $money->getField('name', true);
                $osInfo[2][$id] = $money->getField('iconString');
            }

            return [$result, ...$osInfo];
        }

        return null;
    }

    private function _searchItemset(array &$shared) : ?array// 5 Itemsets $moduleMask & 0x0000020
    {
        $cnd  = array_merge($this->cndBase, [$this->createLookup()]);
        $sets = new ItemsetList($cnd, ['calcTotal' => true]);

        $data = $sets->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            Util::mergeJsGlobals($this->jsgStore, $sets->getJSGlobals(GLOBALINFO_SELF));

            $lvData = ['data' => $data];

            if ($sets->getMatches() > $this->maxResults)
            {
                $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_itemsetsfound', $sets->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            if (isset($lvData['note']))
                $lvData['note'] .= ' + LANG.dash + $WH.sprintf(LANG.lvnote_filterresults, \'?itemsets&filter=na='.urlencode($this->query).'\')';
            else
                $lvData['note'] = '$$WH.sprintf(LANG.lvnote_filterresults, \'?itemsets&filter=na='.urlencode($this->query).'\')';

            return [$lvData, ItemsetList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::ITEMSET, $sets->getMatches(), [], [], 'Item Set'];

            foreach ($sets->iterate() as $id => $__)
            {
                $result[$id]    = $sets->getField('name', true);
                $osInfo[3][$id] = $sets->getField('quality');
            }

            return [$result, ...$osInfo];
        }

        if ($this->moduleMask & self::TYPE_JSON)
        {
            $shared['pcsToSet'] = $sets->pieceToSet;

            foreach ($data as &$d)
                unset($d['quality'], $d['heroic']);

            return array_values($data);
        }

        return null;
    }

    private function _searchItem(array &$shared) : ?array   // 6 Items $moduleMask & 0x0000040
    {
        $miscData = ['calcTotal' => true];
        $lookup   = $this->createLookup();

        if ($this->moduleMask & self::TYPE_JSON)
        {
            if (!empty($shared['pcsToSet']))
            {
                $cnd      = [['i.id', array_keys($shared['pcsToSet'])]];
                $miscData = ['pcsToSet' => $shared['pcsToSet']];
            }
            else
            {
                $cnd   = $this->cndBase;
                $cnd[] = ['i.class', [ITEM_CLASS_WEAPON, ITEM_CLASS_GEM, ITEM_CLASS_ARMOR]];
                $cnd[] = $lookup;

                if ($this->extraOpts)
                    $miscData['extraOpts'] = $this->extraOpts;
                if ($this->extraCnd)
                    $cnd = array_merge($cnd, $this->extraCnd);
            }
        }
        else
            $cnd = array_merge($this->cndBase, [$lookup]);

        $items = new ItemList($cnd, $miscData);

        $data = $items->getListviewData($this->moduleMask & self::TYPE_JSON ? (ITEMINFO_SUBITEMS | ITEMINFO_JSON) : 0);
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            Util::mergeJsGlobals($this->jsgStore, $items->getJSGlobals());

            $lvData = ['data' => $data];

            if ($items->getMatches() > $this->maxResults)
            {
                $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_itemsfound', $items->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            if (isset($lvData['note']))
                $lvData['note'] .= ' + LANG.dash + $WH.sprintf(LANG.lvnote_filterresults, \'?items&filter=na='.urlencode($this->query).'\')';
            else
                $lvData['note'] = '$$WH.sprintf(LANG.lvnote_filterresults, \'?items&filter=na='.urlencode($this->query).'\')';

            return [$lvData, ItemList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::ITEM, $items->getMatches(), [], [], 'Item'];

            foreach ($items->iterate() as $id => $__)
            {
                $result[$id]    = $items->getField('name', true);
                $osInfo[2][$id] = $items->getField('iconString');
                $osInfo[3][$id] = $items->getField('quality');
            }

            return [$result, ...$osInfo];
        }

        if ($this->moduleMask & self::TYPE_JSON)
        {
            foreach ($data as &$d)
                if (!empty($d['subitems']))
                    foreach ($d['subitems'] as &$si)
                        $si['enchantment'] = implode(', ', $si['enchantment']);

            return array_values($data);
        }

        return null;
    }

    private function _searchAbility() : ?array              // 7 Abilities (Player + Pet) $moduleMask & 0x0000080
    {
        $cnd       = array_merge($this->cndBase, array(           // hmm, inclued classMounts..?
            ['s.typeCat', [7, -2, -3, -4]],
            [['s.cuFlags', (SPELL_CU_TRIGGERED | SPELL_CU_TALENT), '&'], 0],
            [['s.attributes0', 0x80, '&'], 0],
            $this->createLookup()
        ));
        $abilities = new SpellList($cnd, ['calcTotal' => true]);

        $data = $abilities->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            Util::mergeJsGlobals($this->jsgStore, $abilities->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

            $vis = ['level', 'schools'];
            if ($abilities->hasSetFields('reagent1', 'reagent2', 'reagent3', 'reagent4', 'reagent5', 'reagent6', 'reagent7', 'reagent8'))
                $vis[] = 'reagents';

            if ($abilities->hasSetFields('reqclass'))
                $vis[] = 'classes';                         // i'd love to set 'singleclass', but do i want to walk through all abilities to see if each mask contains at most 1 class?

            $lvData = array(
                'data'        => $data,
                'id'          => 'abilities',
                'name'        => '$LANG.tab_abilities',
                'visibleCols' => $vis
            );

            if ($abilities->getMatches() > $this->maxResults)
            {
                $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_abilitiesfound', $abilities->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            if (isset($lvData['note']))
                $lvData['note'] .= ' + LANG.dash + $WH.sprintf(LANG.lvnote_filterresults, \'?spells=7&filter=na='.urlencode($this->query).'\')';
            else
                $lvData['note'] = '$$WH.sprintf(LANG.lvnote_filterresults, \'?spells=7&filter=na='.urlencode($this->query).'\')';

            return [$lvData, SpellList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::SPELL, $abilities->getMatches(), [], [], 'Ability'];

            foreach ($abilities->iterate() as $id => $__)
            {
                $result[$id]    = $abilities->getField('name', true);
                $osInfo[2][$id] = $abilities->getField('iconString');
                $osInfo[3][$id] = $abilities->ranks[$id];
            }

            return [$result, ...$osInfo];
        }

        return null;
    }

    private function _searchTalent() : ?array               // 8 Talents (Player + Pet) $moduleMask & 0x0000100
    {
        $cnd     = array_merge($this->cndBase, array(
            ['s.typeCat', [-7, -2]],
            $this->createLookup()
        ));
        $talents = new SpellList($cnd, ['calcTotal' => true]);

        $data = $talents->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            Util::mergeJsGlobals($this->jsgStore, $talents->getJSGlobals());

            $vis = ['level', 'singleclass', 'schools'];
            if ($talents->hasSetFields('reagent1', 'reagent2', 'reagent3', 'reagent4', 'reagent5', 'reagent6', 'reagent7', 'reagent8'))
                $vis[] = 'reagents';

            $lvData = array(
                'data'        => $data,
                'id'          => 'talents',
                'name'        => '$LANG.tab_talents',
                'visibleCols' => $vis
            );

            if ($talents->getMatches() > $this->maxResults)
            {
                $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_talentsfound', $talents->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            if (isset($lvData['note']))
                $lvData['note'] .= ' + LANG.dash + $WH.sprintf(LANG.lvnote_filterresults, \'?spells=-2&filter=na='.urlencode($this->query).'\')';
            else
                $lvData['note'] = '$$WH.sprintf(LANG.lvnote_filterresults, \'?spells=-2&filter=na='.urlencode($this->query).'\')';

            return [$lvData, SpellList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::SPELL, $talents->getMatches(), [], [], 'Talent'];

            foreach ($talents->iterate() as $id => $__)
            {
                $result[$id]    = $talents->getField('name', true);
                $osInfo[2][$id] = $talents->getField('iconString');
                $osInfo[3][$id] = $talents->ranks[$id];
            }

            return [$result, ...$osInfo];
        }

        return null;
    }

    private function _searchGlyph() : ?array                // 9 Glyphs $moduleMask & 0x0000200
    {
        $cnd    = array_merge($this->cndBase, array(
            ['s.typeCat', -13],
            $this->createLookup()
        ));
        $glyphs = new SpellList($cnd, ['calcTotal' => true]);

        $data = $glyphs->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            Util::mergeJsGlobals($this->jsgStore, $glyphs->getJSGlobals());

            $lvData = array(
                'data'        => $data,
                'id'          => 'glyphs',
                'name'        => '$LANG.tab_glyphs',
                'visibleCols' => ['singleclass', 'glyphtype']
            );

            if ($glyphs->getMatches() > $this->maxResults)
            {
                $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_glyphsfound', $glyphs->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            if (isset($lvData['note']))
                $lvData['note'] .= ' + LANG.dash + $WH.sprintf(LANG.lvnote_filterresults, \'?spells=-13&filter=na='.urlencode($this->query).'\')';
            else
                $lvData['note'] = '$$WH.sprintf(LANG.lvnote_filterresults, \'?spells=-13&filter=na='.urlencode($this->query).'\')';

            return [$lvData, SpellList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::SPELL, $glyphs->getMatches(), [], [], 'Glyph'];

            foreach ($glyphs->iterate() as $id => $__)
            {
                $result[$id]    = $glyphs->getField('name', true);
                $osInfo[2][$id] = $glyphs->getField('iconString');
            }

            return [$result, ...$osInfo];
        }

        return null;
    }

    private function _searchProficiency() : ?array          // 10 Proficiencies $moduleMask & 0x0000400
    {
        $cnd  = array_merge($this->cndBase, array(
            ['s.typeCat', -11],
            $this->createLookup()
        ));
        $prof = new SpellList($cnd, ['calcTotal' => true]);

        $data = $prof->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            Util::mergeJsGlobals($this->jsgStore, $prof->getJSGlobals());

            $lvData = array(
                'data'        => $data,
                'id'          => 'proficiencies',
                'name'        => '$LANG.tab_proficiencies',
                'visibleCols' => ['classes']
            );

            if ($prof->getMatches() > $this->maxResults)
            {
                $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_spellsfound', $prof->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            if (isset($lvData['note']))
                $lvData['note'] .= ' + LANG.dash + $WH.sprintf(LANG.lvnote_filterresults, \'?spells=-11&filter=na='.urlencode($this->query).'\')';
            else
                $lvData['note'] = '$$WH.sprintf(LANG.lvnote_filterresults, \'?spells=-11&filter=na='.urlencode($this->query).'\')';

            return [$lvData, SpellList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::SPELL, $prof->getMatches(), [], [], 'Proficiency'];

            foreach ($prof->iterate() as $id => $__)
            {
                $result[$id]    = $prof->getField('name', true);
                $osInfo[2][$id] = $prof->getField('iconString');
            }

            return [$result, ...$osInfo];
        }

        return null;
    }

    private function _searchProfession() : ?array           // 11 Professions (Primary + Secondary) $moduleMask & 0x0000800
    {
        $cnd  = array_merge($this->cndBase, array(
            ['s.typeCat', [9, 11]],
            $this->createLookup()
        ));
        $prof = new SpellList($cnd, ['calcTotal' => true]);

        $data = $prof->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            Util::mergeJsGlobals($this->jsgStore, $prof->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

            $lvData = array(
                'data'        => $data,
                'id'          => 'professions',
                'name'        => '$LANG.tab_professions',
                'visibleCols' => ['source', 'reagents']
            );

            if ($prof->getMatches() > $this->maxResults)
            {
                $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_professionfound', $prof->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            if (isset($lvData['note']))
                $lvData['note'] .= ' + LANG.dash + $WH.sprintf(LANG.lvnote_filterresults, \'?spells=11&filter=na='.urlencode($this->query).'\')';
            else
                $lvData['note'] = '$$WH.sprintf(LANG.lvnote_filterresults, \'?spells=11&filter=na='.urlencode($this->query).'\')';

            return [$lvData, SpellList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::SPELL, $prof->getMatches(), [], [], 'Profession'];

            foreach ($prof->iterate() as $id => $__)
            {
                $result[$id]    = $prof->getField('name', true);
                $osInfo[2][$id] = $prof->getField('iconString');
            }

            return [$result, ...$osInfo];
        }

        return null;
    }

    private function _searchCompanion() : ?array            // 12 Companions $moduleMask & 0x0001000
    {
        $cnd   = array_merge($this->cndBase, array(
            ['s.typeCat', -6],
            $this->createLookup()
        ));
        $vPets = new SpellList($cnd, ['calcTotal' => true]);

        $data = $vPets->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            Util::mergeJsGlobals($this->jsgStore, $vPets->getJSGlobals());

            $lvData = array(
                'data'        => $data,
                'id'          => 'companions',
                'name'        => '$LANG.tab_companions',
                'visibleCols' => ['reagents']
            );

            if ($vPets->getMatches() > $this->maxResults)
            {
                $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_companionsfound', $vPets->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            if (isset($lvData['note']))
                $lvData['note'] .= ' + LANG.dash + $WH.sprintf(LANG.lvnote_filterresults, \'?spells=-6&filter=na='.urlencode($this->query).'\')';
            else
                $lvData['note'] = '$$WH.sprintf(LANG.lvnote_filterresults, \'?spells=-6&filter=na='.urlencode($this->query).'\')';

            return [$lvData, SpellList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::SPELL, $vPets->getMatches(), [], [], 'Companion'];

            foreach ($vPets->iterate() as $id => $__)
            {
                $result[$id]    = $vPets->getField('name', true);
                $osInfo[2][$id] = $vPets->getField('iconString');
            }

            return [$result, ...$osInfo];
        }

        return null;
    }

    private function _searchMount() : ?array                // 13 Mounts $moduleMask & 0x0002000
    {
        $cnd    = array_merge($this->cndBase, array(
            ['s.typeCat', -5],
            $this->createLookup()
        ));
        $mounts = new SpellList($cnd, ['calcTotal' => true]);

        $data = $mounts->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            Util::mergeJsGlobals($this->jsgStore, $mounts->getJSGlobals());

            $lvData = array(
                'data' => $data,
                'id'   => 'mounts',
                'name' => '$LANG.tab_mounts',
            );

            if ($mounts->getMatches() > $this->maxResults)
            {
                $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_mountsfound', $mounts->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            if (isset($lvData['note']))
                $lvData['note'] .= ' + LANG.dash + $WH.sprintf(LANG.lvnote_filterresults, \'?spells=-5&filter=na='.urlencode($this->query).'\')';
            else
                $lvData['note'] = '$$WH.sprintf(LANG.lvnote_filterresults, \'?spells=-5&filter=na='.urlencode($this->query).'\')';

            return [$lvData, SpellList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::SPELL, $mounts->getMatches(), [], [], 'Mount'];

            foreach ($mounts->iterate() as $id => $__)
            {
                $result[$id]    = $mounts->getField('name', true);
                $osInfo[2][$id] = $mounts->getField('iconString');
            }

            return [$result, ...$osInfo];
        }

        return null;
    }

    private function _searchCreature() : ?array             // 14 NPCs $moduleMask & 0x0004000
    {
        $cnd  = array_merge($this->cndBase, array(
            [['flagsExtra', 0x80], 0],                      // exclude trigger creatures
            [['cuFlags', NPC_CU_DIFFICULTY_DUMMY, '&'], 0], // exclude difficulty entries
            $this->createLookup()
        ));
        $npcs = new CreatureList($cnd, ['calcTotal' => true]);

        $data = $npcs->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            $lvData = array(
                'data' => $data,
                'id'   => 'npcs',
                'name' => '$LANG.tab_npcs',
            );

            if ($npcs->getMatches() > $this->maxResults)
            {
                $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_npcsfound', $npcs->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            if (isset($lvData['note']))
                $lvData['note'] .= ' + LANG.dash + $WH.sprintf(LANG.lvnote_filterresults, \'?npcs&filter=na='.urlencode($this->query).'\')';
            else
                $lvData['note'] = '$$WH.sprintf(LANG.lvnote_filterresults, \'?npcs&filter=na='.urlencode($this->query).'\')';

            return [$lvData, CreatureList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::NPC, $npcs->getMatches(), [], [], 'NPC'];

            foreach ($npcs->iterate() as $id => $__)
            {
                $result[$id] = $npcs->getField('name', true);
                if ($npcs->isBoss())
                    $osInfo[2][$id] = 1;
            }

            return [$result, ...$osInfo];
        }

        return null;
    }

    private function _searchQuest() : ?array                // 15 Quests $moduleMask & 0x0008000
    {
        $cnd    = array_merge($this->cndBase, array(
            [['flags', CUSTOM_UNAVAILABLE | CUSTOM_DISABLED, '&'], 0],
            $this->createLookup()
        ));
        $quests = new QuestList($cnd, ['calcTotal' => true]);

        $data = $quests->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            Util::mergeJsGlobals($this->jsgStore, $quests->getJSGlobals());

            $lvData = ['data' => $data];

            if ($quests->getMatches() > $this->maxResults)
            {
                $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_questsfound', $quests->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            if (isset($lvData['note']))
                $lvData['note'] .= ' + LANG.dash + $WH.sprintf(LANG.lvnote_filterresults, \'?quests&filter=na='.urlencode($this->query).'\')';
            else
                $lvData['note'] = '$$WH.sprintf(LANG.lvnote_filterresults, \'?quests&filter=na='.urlencode($this->query).'\')';

            return [$lvData, QuestList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::QUEST, $quests->getMatches(), [], [], 'Quest'];

            foreach ($quests->iterate() as $id => $__)
            {
                $result[$id]    = $quests->getField('name', true);
                $osInfo[2][$id] = $data[$id]['side'];       // why recalculate if already set
            }

            return [$result, ...$osInfo];
        }

        return null;
    }

    private function _searchAchievement() : ?array          // 16 Achievements $moduleMask & 0x0010000
    {
        $cnd  = array_merge($this->cndBase, array(
            [['flags', ACHIEVEMENT_FLAG_COUNTER, '&'], 0],  // not a statistic
            $this->createLookup()
        ));
        $acvs = new AchievementList($cnd, ['calcTotal' => true]);

        $data = $acvs->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            Util::mergeJsGlobals($this->jsgStore, $acvs->getJSGlobals());

            $lvData = array(
                'data' => $data,
                'visibleCols' => ['category']
            );

            if ($acvs->getMatches() > $this->maxResults)
            {
                $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_achievementsfound', $acvs->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            if (isset($lvData['note']))
                $lvData['note'] .= ' + LANG.dash + $WH.sprintf(LANG.lvnote_filterresults, \'?achievements&filter=na='.urlencode($this->query).'\')';
            else
                $lvData['note'] = '$$WH.sprintf(LANG.lvnote_filterresults, \'?achievements&filter=na='.urlencode($this->query).'\')';

            return [$lvData, AchievementList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::ACHIEVEMENT, $acvs->getMatches(), [], [], 'Achievement'];

            foreach ($acvs->iterate() as $id => $__)
            {
                $result[$id]    = $acvs->getField('name', true);
                $osInfo[2][$id] = $acvs->getField('iconString');
            }

            return [$result, ...$osInfo];
        }

        return null;
    }

    private function _searchStatistic() : ?array            // 17 Statistics $moduleMask & 0x0020000
    {
        $cnd   = array_merge($this->cndBase, array(
            ['flags', ACHIEVEMENT_FLAG_COUNTER, '&'],       // is a statistic
            $this->createLookup()
        ));
        $stats = new AchievementList($cnd, ['calcTotal' => true]);

        $data = $stats->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            Util::mergeJsGlobals($this->jsgStore, $stats->getJSGlobals(GLOBALINFO_SELF));

            $lvData = array(
                'data'        => $data,
                'visibleCols' => ['category'],
                'hiddenCols'  => ['side', 'points', 'rewards'],
                'name'        => '$LANG.tab_statistics',
                'id'          => 'statistics'
            );

            if ($stats->getMatches() > $this->maxResults)
            {
                $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_statisticsfound', $stats->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            if (isset($lvData['note']))
                $lvData['note'] .= ' + LANG.dash + $WH.sprintf(LANG.lvnote_filterresults, \'?achievements=1&filter=na='.urlencode($this->query).'\')';
            else
                $lvData['note'] = '$$WH.sprintf(LANG.lvnote_filterresults, \'?achievements=1&filter=na='.urlencode($this->query).'\')';

            return [$lvData, AchievementList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::ACHIEVEMENT, $stats->getMatches(), [], [], 'Statistic'];

            foreach ($stats->iterate() as $id => $__)
            {
                $result[$id]    = $stats->getField('name', true);
                $osInfo[2][$id] = $stats->getField('iconString');
            }

            return [$result, ...$osInfo];
        }

        return null;
    }

    private function _searchZone() : ?array                 // 18 Zones $moduleMask & 0x0040000
    {
        $cnd    = array_merge($this->cndBase, [$this->createLookup()]);
        $zones  = new ZoneList($cnd, ['calcTotal' => true]);

        $data = $zones->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            Util::mergeJsGlobals($this->jsgStore, $zones->getJSGlobals());

            $lvData = ['data' => $data];

            if ($zones->getMatches() > $this->maxResults)
            {
                $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_zonesfound', $zones->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            if (isset($lvData['note']))
                $lvData['note'] .= ' + LANG.dash + $WH.sprintf(LANG.lvnote_filterresults, \'?achievements&filter=na='.urlencode($this->query).'\')';
            else
                $lvData['note'] = '$$WH.sprintf(LANG.lvnote_filterresults, \'?achievements&filter=na='.urlencode($this->query).'\')';

            return [$lvData, ZoneList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::ZONE, $zones->getMatches(), [], [], 'Zone'];

            foreach ($zones->iterate() as $id => $__)
                $result[$id] = $zones->getField('name', true);

            return [$result, ...$osInfo];
        }

        return null;
    }

    private function _searchObject() : ?array               // 19 Objects $moduleMask & 0x0080000
    {
        $cnd     = array_merge($this->cndBase, [$this->createLookup()]);
        $objects = new GameObjectList($cnd, ['calcTotal' => true]);

        $data = $objects->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            Util::mergeJsGlobals($this->jsgStore, $objects->getJSGlobals());

            $lvData = ['data' => $data];

            if ($objects->getMatches() > $this->maxResults)
            {
                $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_objectsfound', $objects->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            if (isset($lvData['note']))
                $lvData['note'] .= ' + LANG.dash + $WH.sprintf(LANG.lvnote_filterresults, \'?objects&filter=na='.urlencode($this->query).'\')';
            else
                $lvData['note'] = '$$WH.sprintf(LANG.lvnote_filterresults, \'?objects&filter=na='.urlencode($this->query).'\')';

            return [$lvData, GameObjectList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::OBJECT, $objects->getMatches(), [], [], 'Object'];

            foreach ($objects->iterate() as $id => $__)
                $result[$id] = $objects->getField('name', true);

            return [$result, ...$osInfo];
        }

        return null;
    }

    private function _searchFaction() : ?array              // 20 Factions $moduleMask & 0x0100000
    {
        $cnd      = array_merge($this->cndBase, [$this->createLookup()]);
        $factions = new FactionList($cnd, ['calcTotal' => true]);

        $data = $factions->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            $lvData = ['data' => $data];

            if ($factions->getMatches() > $this->maxResults)
            {
                $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_factionsfound', $factions->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            return [$lvData, FactionList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::FACTION, $factions->getMatches(), [], [], 'Faction'];

            foreach ($factions->iterate() as $id => $__)
                $result[$id] = $factions->getField('name', true);

            return [$result, ...$osInfo];
        }

        return null;
    }

    private function _searchSkill() : ?array                // 21 Skills $moduleMask & 0x0200000
    {
        $cnd    = array_merge($this->cndBase, [$this->createLookup()]);
        $skills = new SkillList($cnd, ['calcTotal' => true]);

        $data = $skills->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            $lvData = ['data' => $data];

            if ($skills->getMatches() > $this->maxResults)
            {
                $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_skillsfound', $skills->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            return [$lvData, SkillList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::SKILL, $skills->getMatches(), [], [], 'Skill'];

            foreach ($skills->iterate() as $id => $__)
            {
                $result[$id] = $skills->getField('name', true);
                $osInfo[2][$id] = $skills->getField('iconString');
            }

            return [$result, ...$osInfo];
        }

        return null;
    }

    private function _searchPet() : ?array                  // 22 Pets $moduleMask & 0x0400000
    {
        $cnd  = array_merge($this->cndBase, [$this->createLookup()]);
        $pets = new PetList($cnd, ['calcTotal' => true]);

        $data = $pets->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            $lvData = array(
                'data'            => $data,
                'computeDataFunc' => '$_'
            );

            if ($pets->getMatches() > $this->maxResults)
            {
                $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_petsfound', $pets->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            return [$lvData, PetList::$brickFile, 'petFoodCol'];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::PET, $pets->getMatches(), [], [], 'Pet'];

            foreach ($pets->iterate() as $id => $__)
            {
                $result[$id]    = $pets->getField('name', true);
                $osInfo[2][$id] = $pets->getField('iconString');
            }

            return [$result, ...$osInfo];
        }

        return null;
    }

    private function _searchCreatureAbility() : ?array      // 23 NPCAbilities $moduleMask & 0x0800000
    {
        $cnd          = array_merge($this->cndBase, array(
            ['s.typeCat', -8],
            $this->createLookup()
        ));
        $npcAbilities = new SpellList($cnd, ['calcTotal' => true]);

        $data = $npcAbilities->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            Util::mergeJsGlobals($this->jsgStore, $npcAbilities->getJSGlobals());

            $lvData = array(
                'data'        => $data,
                'id'          => 'npc-abilities',
                'name'        => '$LANG.tab_npcabilities',
                'visibleCols' => ['level'],
                'hiddenCols'  => ['skill']
            );

            if ($npcAbilities->getMatches() > $this->maxResults)
            {
                $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_spellsfound', $npcAbilities->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            if (isset($lvData['note']))
                $lvData['note'] .= ' + LANG.dash + $WH.sprintf(LANG.lvnote_filterresults, \'?spells=-8&filter=na='.urlencode($this->query).'\')';
            else
                $lvData['note'] = '$$WH.sprintf(LANG.lvnote_filterresults, \'?spells=-8&filter=na='.urlencode($this->query).'\')';

            return [$lvData, SpellList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::SPELL, $npcAbilities->getMatches(), [], [], 'Spell'];

            foreach ($npcAbilities->iterate() as $id => $__)
            {
                $result[$id]    = $npcAbilities->getField('name', true);
                $osInfo[2][$id] = $npcAbilities->getField('iconString');
            }

            return [$result, ...$osInfo];
        }

        return null;
    }

    private function _searchSpell() : ?array                // 24 Spells (Misc + GM + triggered abilities) $moduleMask & 0x1000000
    {
        $cnd  = array_merge($this->cndBase, array(
            ['s.typeCat', -8, '!'],
            [
                'OR',
                ['s.typeCat', [0, -9]],
                ['s.cuFlags', SPELL_CU_TRIGGERED, '&'],
                ['s.attributes0', 0x80, '&']
            ],
            $this->createLookup()
        ));
        $misc = new SpellList($cnd, ['calcTotal' => true]);

        $data = $misc->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            Util::mergeJsGlobals($this->jsgStore, $misc->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

            $lvData = array(
                'data'        => $data,
                'name'        => '$LANG.tab_uncategorizedspells',
                'visibleCols' => ['level'],
                'hiddenCols'  => ['skill']
            );

            if ($misc->getMatches() > $this->maxResults)
            {
                $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_spellsfound', $misc->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            if (isset($lvData['note']))
                $lvData['note'] .= ' + LANG.dash + $WH.sprintf(LANG.lvnote_filterresults, \'?spells=0&filter=na='.urlencode($this->query).'\')';
            else
                $lvData['note'] = '$$WH.sprintf(LANG.lvnote_filterresults, \'?spells=0&filter=na='.urlencode($this->query).'\')';

            return [$lvData, SpellList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::SPELL, $misc->getMatches(), [], [], 'Spell'];

            foreach ($misc->iterate() as $id => $__)
            {
                $result[$id]    = $misc->getField('name', true);
                $osInfo[2][$id] = $misc->getField('iconString');
            }

            return [$result, ...$osInfo];
        }

        return null;
    }

    private function _searchEmote() : ?array                // 25 Emotes $moduleMask & 0x2000000
    {
        $cnd   = array_merge($this->cndBase, [$this->createLookup(['cmd', 'meToExt_loc'.Lang::getLocale()->value, 'meToNone_loc'.Lang::getLocale()->value, 'extToMe_loc'.Lang::getLocale()->value, 'extToExt_loc'.Lang::getLocale()->value, 'extToNone_loc'.Lang::getLocale()->value])]);
        $emote = new EmoteList($cnd, ['calcTotal' => true]);

        $data = $emote->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            Util::mergeJsGlobals($this->jsgStore, $emote->getJSGlobals());

            $lvData = array(
                'data' => $data,
                'name' => Util::ucFirst(Lang::game('emotes'))
            );

            if ($emote->getMatches() > $this->maxResults)
            {
                // $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_emotesfound', $emote->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            return [$lvData, EmoteList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::EMOTE, $emote->getMatches(), [], [], 'Emote'];

            foreach ($emote->iterate() as $id => $__)
                $result[$id] = $emote->getField('name', true);

            return [$result, ...$osInfo];
        }

        return null;
    }

    private function _searchEnchantment() : ?array          // 26 Enchantments $moduleMask & 0x4000000
    {
        $cnd         = array_merge($this->cndBase, [$this->createLookup(['name_loc'.Lang::getLocale()->value])]);
        $enchantment = new EnchantmentList($cnd, ['calcTotal' => true]);

        $data = $enchantment->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            Util::mergeJsGlobals($this->jsgStore, $enchantment->getJSGlobals());

            $lvData = array(
                'data' => $data,
                'name' => Util::ucFirst(Lang::game('enchantments'))
            );

            if (array_filter(array_column($data, 'spells')))
                $lvData['visibleCols'] = ['trigger'];

            if (!$enchantment->hasSetFields('skillLine'))
                $lvData['hiddenCols'] = ['skill'];

            if ($enchantment->getMatches() > $this->maxResults)
            {
                $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_enchantmentsfound', $enchantment->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            return [$lvData, EnchantmentList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::ENCHANTMENT, $enchantment->getMatches(), [], [], 'Enchantment'];

            foreach ($enchantment->iterate() as $id => $__)
                $result[$id] = $enchantment->getField('name', true);

            return [$result, ...$osInfo];
        }

        return null;
    }

    private function _searchSound() : ?array                // 27 Sounds $moduleMask & 0x8000000
    {
        $cnd    = array_merge($this->cndBase, [$this->createLookup(['name'])]);
        $sounds = new SoundList($cnd, ['calcTotal' => true]);

        $data = $sounds->getListviewData();
        if (!$data)
            return [];

        if ($this->moduleMask & self::TYPE_REGULAR)
        {
            Util::mergeJsGlobals($this->jsgStore, $sounds->getJSGlobals());

            $lvData = array(
                'data' => $data,
            );

            if ($sounds->getMatches() > $this->maxResults)
            {
                $lvData['note'] = sprintf(Util::$tryNarrowingString, 'LANG.lvnote_soundsfound', $sounds->getMatches(), $this->maxResults);
                $lvData['_truncated'] = 1;
            }

            return [$lvData, SoundList::$brickFile];
        }

        if ($this->moduleMask & self::TYPE_OPEN)
        {
            $result = [];
            $osInfo = [Type::SOUND, $sounds->getMatches(), [], [], 'Sound'];

            foreach ($sounds->iterate() as $id => $__)
                $result[$id] = $sounds->getField('name', true);

            return [$result, ...$osInfo];
        }

        return null;
    }
}
