<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("sql", new class extends SetupScript
{
    use TrCustomData;

    protected $info = array(
        'search'   => [[   ], CLISetup::ARGV_PARAM,    'Normalize strings from creatures, items, objects, quests & spells for fulltext search.'],
/* 1 */ 'creature' => [['1'], CLISetup::ARGV_OPTIONAL, '...only for creatures.'],
/* 2 */ 'item'     => [['2'], CLISetup::ARGV_OPTIONAL, '...only for items.'],
/* 4 */ 'object'   => [['3'], CLISetup::ARGV_OPTIONAL, '...only for objects.'],
/* 8 */ 'spell'    => [['4'], CLISetup::ARGV_OPTIONAL, '...only for spells.'],
/*16 */ 'quest'    => [['5'], CLISetup::ARGV_OPTIONAL, '...only for quests.']
    );

    protected $setupAfter = [['creature', 'items', 'objects', 'spell', 'quests'], []];

    private const /* int */ OPT_NPCS    = (1 << 0);
    private const /* int */ OPT_ITEMS   = (1 << 1);
    private const /* int */ OPT_OBJECTS = (1 << 2);
    private const /* int */ OPT_SPELLS  = (1 << 3);
    private const /* int */ OPT_QUESTS  = (1 << 4);

    private array $spells  = [];
    private array $locales = [];

    public function generate() : bool
    {
        // find out what to do
        $opts = array_slice(array_keys($this->info), 1);
        $getO = CLISetup::getOpt(...$opts);
        $mask = null;

        // todo: have an extra search table with ngram fulltext indices
        $this->locales = array_filter(CLISetup::$locales, fn($x) => !$x->isLogographic());

        if ($getO['creature'])
            $mask |= self::OPT_NPCS;
        if ($getO['item'])
            $mask |= self::OPT_ITEMS;
        if ($getO['object'])
            $mask |= self::OPT_OBJECTS;
        if ($getO['spell'])
            $mask |= self::OPT_SPELLS;
        if ($getO['quest'])
            $mask |= self::OPT_QUESTS;

        $mask ??= (self::OPT_NPCS | self::OPT_ITEMS | self::OPT_OBJECTS | self::OPT_SPELLS | self::OPT_QUESTS);

        // do what needs doing
        DB::Aowow()->qry('SET SESSION innodb_ft_enable_stopword = OFF');

        if ($mask & self::OPT_NPCS)
            $this->normalizeCreatures();
        if ($mask & self::OPT_ITEMS)
            $this->normalizeItems();
        if ($mask & self::OPT_OBJECTS)
            $this->normalizeObjects();
        if ($mask & self::OPT_SPELLS)
            $this->normalizeSpells();
        if ($mask & self::OPT_QUESTS)
            $this->normalizeQuests();

        return true;
    }

    private function normalizeQuests() : void
    {
        CLI::write(' - creating indices for quest names, descriptions & details...');

        DB::Aowow()->qry('TRUNCATE ::quests_search');

        CLI::write('   * fetching', tmpRow: true);

        $rows = DB::Aowow()->selectAssoc(
           'SELECT `id`, 0 AS "locale", `name_loc0` AS "name", `objectives_loc0` AS "objectives", `details_loc0` AS "details" FROM ::quests UNION
            SELECT `id`, 2 AS "locale", `name_loc2` AS "name", `objectives_loc2` AS "objectives", `details_loc2` AS "details" FROM ::quests UNION
            SELECT `id`, 3 AS "locale", `name_loc3` AS "name", `objectives_loc3` AS "objectives", `details_loc3` AS "details" FROM ::quests UNION
            SELECT `id`, 6 AS "locale", `name_loc6` AS "name", `objectives_loc6` AS "objectives", `details_loc6` AS "details" FROM ::quests UNION
            SELECT `id`, 8 AS "locale", `name_loc8` AS "name", `objectives_loc8` AS "objectives", `details_loc8` AS "details" FROM ::quests'
        );

        CLI::write('   * normalizing', tmpRow: true);

        array_walk($rows, self::normalizeRow(...), ['name', 'objectives', 'details']);

        $rows = array_filter($rows, fn($x) => $x['name'] !== null);

        $n = ceil(count($rows) / CLISetup::SQL_BATCH);
        for ($i = 0; $i < $n; $i++)
        {
            $sub = array_slice($rows, $i * CLISetup::SQL_BATCH, CLISetup::SQL_BATCH);

            CLI::write('   * inserting batch '.($i + 1).' / '.$n, tmpRow: true);
            DB::Aowow()->qry('INSERT INTO ::quests_search %m', array(
                'id'          => array_column($sub, 'id'),
                'locale'      => array_column($sub, 'locale'),
                'nName'       => array_column($sub, 'name'),
                'nObjectives' => array_column($sub, 'objectives'),
                'nDetails'    => array_column($sub, 'details')
            ));
        }
    }

    private function normalizeObjects() : void
    {
        CLI::write(' - creating indices for object names...');

        DB::Aowow()->qry('TRUNCATE ::objects_search');

        CLI::write('   * fetching', tmpRow: true);

        $rows = DB::Aowow()->selectAssoc(
           'SELECT `id`, 0 AS "locale", `name_loc0` AS "name" FROM ::objects UNION
            SELECT `id`, 2 AS "locale", `name_loc2` AS "name" FROM ::objects UNION
            SELECT `id`, 3 AS "locale", `name_loc3` AS "name" FROM ::objects UNION
            SELECT `id`, 6 AS "locale", `name_loc6` AS "name" FROM ::objects UNION
            SELECT `id`, 8 AS "locale", `name_loc8` AS "name" FROM ::objects'
        );

        CLI::write('   * normalizing', tmpRow: true);

        array_walk($rows, self::normalizeRow(...), ['name']);

        $rows = array_filter($rows, fn($x) => $x['name'] !== null);

        CLI::write('   * inserting', tmpRow: true);

        DB::Aowow()->qry('INSERT INTO ::objects_search %m', array(
            'id'     => array_column($rows, 'id'),
            'locale' => array_column($rows, 'locale'),
            'nName'  => array_column($rows, 'name')
        ));
    }

    private function normalizeCreatures() : void
    {
        CLI::write(' - creating indices for creature names & subnames...');

        DB::Aowow()->qry('TRUNCATE ::creature_search');

        CLI::write('   * fetching', tmpRow: true);

        $rows = DB::Aowow()->selectAssoc(
           'SELECT `id`, 0 AS "locale", `name_loc0` AS "name", `subname_loc0` AS "subname" FROM ::creature UNION
            SELECT `id`, 2 AS "locale", `name_loc2` AS "name", `subname_loc2` AS "subname" FROM ::creature UNION
            SELECT `id`, 3 AS "locale", `name_loc3` AS "name", `subname_loc3` AS "subname" FROM ::creature UNION
            SELECT `id`, 6 AS "locale", `name_loc6` AS "name", `subname_loc6` AS "subname" FROM ::creature UNION
            SELECT `id`, 8 AS "locale", `name_loc8` AS "name", `subname_loc8` AS "subname" FROM ::creature'
        );

        CLI::write('   * normalizing', tmpRow: true);

        array_walk($rows, self::normalizeRow(...), ['name', 'subname']);

        $rows = array_filter($rows, fn($x) => $x['name'] !== null && $x['subname'] !== null);

        CLI::write('   * inserting', tmpRow: true);

        DB::Aowow()->qry('INSERT INTO ::creature_search %m', array(
            'id'       => array_column($rows, 'id'),
            'locale'   => array_column($rows, 'locale'),
            'nName'    => array_column($rows, 'name'),
            'nSubname' => array_column($rows, 'subname')
        ));
    }

    private function normalizeItems() : void
    {
        CLI::write(' - creating indices for item names, descriptions & spells...');

        DB::Aowow()->qry('TRUNCATE ::items_search');

        CLI::write('   * fetching', tmpRow: true);

        $result = [];
        $items  = DB::Aowow()->selectAssoc('SELECT `id` AS ARRAY_KEY, `name_loc0`, `name_loc2`, `name_loc3`, `name_loc6`, `name_loc8`, `description_loc0`, `description_loc2`, `description_loc3`, `description_loc6`, `description_loc8`, `spellId1`, `spellId2`, `spellId3`, `spellId4`, `spellId5` FROM ::items');
        $spells = new SpellList(array(['id',
            array_filter(array_column($items, 'spellId1')),
            array_filter(array_column($items, 'spellId2')),
            array_filter(array_column($items, 'spellId3')),
            array_filter(array_column($items, 'spellId4')),
            array_filter(array_column($items, 'spellId5'))
        ]), ['interactive' => SpellList::INTERACTIVE_NONE]);


        $n = count($items) * count($this->locales);
        $j = 0;

        foreach ($this->locales as $locId => $loc)
        {
            Lang::load($loc);

            foreach ($items as $id => $item)
            {
                CLI::write('   * normalizing '.++$j.' / '.$n.' ('.sprintf('%.2f%%', $j * 100 / $n).')', tmpRow: true);

                $name = $desc = $effects = null;

                // ui escape sequences not in default 335a, but undestood by client and may be custom
                if ($_ = Util::localizedString($item, 'name', true))
                    $name = self::normalize(Lang::unescapeUISequences($_, Lang::FMT_RAW));
                if ($_ = Util::localizedString($item, 'description', true))
                    $desc = self::normalize($_);

                for ($i = 1; $i < 6; $i++)
                {
                    $sId = $item['spellId'.$i];
                    if (!$sId)
                        continue;

                    if ($spells->getEntry($sId))
                        if ($_ = $spells->parseText('description')[0])
                            $effects .= str_replace('<br />', ' ', $_);
                }

                if (($effects = self::normalize($effects)) || $name || $desc)
                {
                    $result['id'][]           = $id;
                    $result['locale'][]       = $locId;
                    $result['nName'][]        = $name;
                    $result['nDescription'][] = $desc;
                    $result['nEffects'][]     = $effects;
                }
            }
        }

        $n = ceil(count(current($result)) / CLISetup::SQL_BATCH);
        for ($i = 0; $i < $n; $i++)
        {
            CLI::write('   * inserting batch '.($i + 1).' / '.$n, tmpRow: true);
            DB::Aowow()->qry('INSERT INTO ::items_search %m', array_map(fn($x) => array_slice($x, $i * CLISetup::SQL_BATCH, CLISetup::SQL_BATCH), $result));
        }
    }

    private function normalizeSpells() : void
    {
        CLI::write(' - creating indices for spell names, descriptions & buffs...');

        DB::Aowow()->qry('TRUNCATE ::spell_search');

        CLI::write('   * fetching', tmpRow: true);

        $splBuf = [];
        $result = [];
        $spells = DB::Aowow()->selectAssoc('SELECT `id` AS ARRAY_KEY, `name_loc0`, `name_loc2`, `name_loc3`, `name_loc6`, `name_loc8`, `buff_loc0`, `buff_loc2`, `buff_loc3`, `buff_loc6`, `buff_loc8`, `description_loc0`, `description_loc2`, `description_loc3`, `description_loc6`, `description_loc8` FROM ::spell');

        $n = count($spells) * count($this->locales);
        $j = 0;

        foreach ($this->locales as $locId => $loc)
        {
            Lang::load($loc);

            foreach ($spells as $id => $spell)
            {
                CLI::write('   * normalizing '.++$j.' / '.$n.' ('.sprintf('%.2f%%', $j * 100 / $n).')', tmpRow: true);

                $name = $desc = $buff = null;

                // initializing a Spell Object and parsing the tooltip is a lot of effort.
                // so don't do that unless we really really have to
                if (strpos($spell['description_loc'.$locId], '$') || strpos($spell['buff_loc'.$locId], '$'))
                {
                    $splBuf[$id] ??= new SpellList(array(['id', $id]), ['interactive' => SpellList::INTERACTIVE_NONE]);

                    if ($_ = $splBuf[$id]->parseText('description')[0])
                        $desc = self::normalize(str_replace('<br />', ' ', $_));
                    if ($_ = $splBuf[$id]->parseText('buff')[0])
                        $buff = self::normalize(str_replace('<br />', ' ', $_));
                }

                if ($_ = $desc ?: Util::localizedString($spell, 'description', true))
                    $desc = self::normalize($_);
                if ($_ = $buff ?: Util::localizedString($spell, 'buff', true))
                    $buff = self::normalize($_);
                if ($_ = Util::localizedString($spell, 'name', true))
                    $name = self::normalize($_);

                if ($buff || $name || $desc)
                {
                    $result['id'][]           = $id;
                    $result['locale'][]       = $locId;
                    $result['nName'][]        = $name;
                    $result['nDescription'][] = $desc;
                    $result['nBuff'][]        = $buff;
                }
            }
        }

        $n = ceil(count(current($result)) / CLISetup::SQL_BATCH);
        for ($i = 0; $i < $n; $i++)
        {
            CLI::write('   * inserting batch '.($i + 1).' / '.$n, tmpRow: true);
            DB::Aowow()->qry('INSERT INTO ::spell_search %m', array_map(fn($x) => array_slice($x, $i * CLISetup::SQL_BATCH, CLISetup::SQL_BATCH), $result));
        }
    }

    private static function normalizeRow(array &$row, int $idx, array $keys) : void
    {
        foreach ($keys as $key)
            $row[$key] = self::normalize($row[$key] ?? '');
    }

    // e.g. "Zul'Aman O'Reilly" => "Zul Aman ZulAman OReilly Reilly"
    private static function normalize(?string $words) : ?string
    {
        if (!$words)
            return null;

        $words  = array_filter(explode(' ', $words), fn($x) => mb_strlen($x) > 2);
        $result = [];

        foreach ($words as $word)
        {
            if (($new = trim(preg_replace(Filter::PATTERN_FT, ' ', $word, count: $n))) && $n)
            {
                if (!strpos($new, ' '))                     // caught trailing dots or something
                {
                    $result[] = $new;
                    continue;
                }

                if ($splitWords = array_filter(explode(' ', $new), fn($x) => mb_strlen($x) > 2))
                    $result = array_merge($result, $splitWords);

                $result[] = str_replace(' ', '', $new);

                continue;
            }

            $result[] = $word;
        }

        return $result ? implode(' ', array_unique($result)) : null;
    }
});

?>
