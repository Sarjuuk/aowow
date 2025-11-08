<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ProfileBaseResponse extends TemplateResponse
{
    use TrProfilerDetail;

    protected  string $template   = 'profile';
    protected  string $pageName   = 'profile';
    protected ?int    $activeTab  = parent::TAB_TOOLS;
    protected  array  $breadcrumb = [1, 5, 1];              // Tools > Profiler > New

    protected  array  $dataLoader = ['enchants', 'gems', 'glyphs', 'itemsets', 'pets', 'pet-talents', 'quick-excludes', 'realms', 'statistics', 'weight-presets', 'achievements'];
    protected  array  $scripts    = array(
        [SC_JS_FILE,  'js/filters.js'],
        [SC_JS_FILE,  'js/TalentCalc.js'],
        [SC_JS_FILE,  'js/profile_all.js'],
        [SC_JS_FILE,  'js/profile.js'],
        [SC_JS_FILE,  'js/Profiler.js'],
        [SC_CSS_FILE, 'css/talentcalc.css'],
        [SC_CSS_FILE, 'css/Profiler.css']
    );
    protected array $expectedGET      = array(
        'new' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkEmptySet']]
    );

    public int  $type     = Type::PROFILE;
    public bool $gDataKey = true;

    public function __construct(string $idOrProfile)
    {
        parent::__construct($idOrProfile);

        if (!Cfg::get('PROFILER_ENABLE'))
            $this->generateError();

        // neither param nor &new > error
        if (!$idOrProfile && !$this->_get['new'])
            $this->generateError();

        // display empty/new profile editor > ok
        if (!$idOrProfile && $this->_get['new'])
            return;

        $this->getSubjectFromUrl($idOrProfile);

        // we have an ID > ok
        if ($this->typeId)
            return;

        // param was incomplete profile > error
        if (!$this->subjectName)
            $this->notFound();

        $rnItr = 0;
        // pending rename
        if (preg_match('/^([^\-]+)-(\d+)$/i', $this->subjectName, $m))
        {
            $this->subjectName = $m[1];
            $rnItr = $m[2];
        }

        // 3 possibilities
        // 1) already synced to aowow
        if ($subject = DB::Aowow()->selectRow('SELECT `id`, `realmGUID`, `stub` FROM ?_profiler_profiles WHERE `realm` = ?d AND `custom` = 0 AND `name` = ? AND `renameItr` = ?d', $this->realmId, Util::ucFirst($this->subjectName), $rnItr))
        {
            $this->typeId = $subject['id'];

            if ($subject['stub'])
                $this->handleIncompleteData(Type::PROFILE, $subject['realmGUID']);

            return;
        }

        // can not be used to look up char on realm
        if ($rnItr)
            $this->notFound();

        // 2) not yet synced but exists on realm (and not a gm character)
        $subjects = DB::Characters($this->realmId)->select(
           'SELECT    c.`guid` AS "realmGUID", c.`name`, c.`race`, c.`class`, c.`level`, c.`gender`, c.`at_login`, g.`guildid` AS "guildGUID", IFNULL(g.`name`, "") AS "guildName", IFNULL(gm.`rank`, 0) AS "guildRank"
            FROM      characters c
            LEFT JOIN guild_member gm ON gm.`guid` = c.`guid`
            LEFT JOIN guild g ON g.`guildid` = gm.`guildid`
            WHERE     c.`name` = ? AND `level` <= ?d AND (`extra_flags` & ?d) = 0',
            Util::ucFirst($this->subjectName), MAX_LEVEL, Profiler::CHAR_GMFLAGS
        );
        if ($subject = array_filter($subjects, fn($x) => Util::lower($x['name']) == Util::lower($this->subjectName)))
        {
            $subject = $subject[0];
            $subject['realm'] = $this->realmId;
            $subject['stub']  = 1;

            if ($subject['at_login'] & 0x1)
                $subject['renameItr'] = DB::Aowow()->selectCell('SELECT MAX(`renameItr`) FROM ?_profiler_profiles WHERE `realm` = ?d AND `custom` = 0 AND `name` = ?', $this->realmId, $subject['name']);

            if ($subject['guildGUID'])
            {
                // create empty guild if necessary to satisfy foreign keys
                $subject['guild'] = DB::Aowow()->selectCell('SELECT `id` FROM ?_profiler_guild WHERE `realm` = ?d AND `realmGUID` = ?d', $this->realmId, $subject['guildGUID']);
                if (!$subject['guild'])
                    $subject['guild'] = DB::Aowow()->query('INSERT INTO ?_profiler_guild (`realm`, `realmGUID`, `stub`, `name`, `nameUrl`) VALUES (?d, ?d, 1, ?, ?)', $this->realmId, $subject['guildGUID'], $subject['guildName'], Profiler::urlize($subject['guildName']));
            }

            unset($subject['guildGUID'], $subject['guildName'], $subject['at_login']);

            // create entry from realm with enough basic info to disply tooltips
            DB::Aowow()->query('REPLACE INTO ?_profiler_profiles (?#) VALUES (?a)', array_keys($subject), array_values($subject));
            $this->typeId = DB::Aowow()->selectCell('SELECT `id` FROM ?_profiler_profiles WHERE `realm` = ?d AND `realmGUID` = ?d', $this->realmId, $subject['realmGUID']);

            $this->handleIncompleteData(Type::PROFILE, $subject['realmGUID']);
            return;
        }

        // 3) does not exist at all
        $this->notFound();
    }

    protected function generate() : void
    {
        if ($this->doResync)
        {
            parent::generate();
            return;
        }

        if ($this->typeId)
        {
            $subject = new LocalProfileList(array(['id', $this->typeId]));
            if ($subject->error)
                $this->notFound();

            if (!$subject->isVisibleToUser())
                $this->notFound();

            // character profile accessed by id
            if (!$subject->isCustom() && !$this->subjectName)
                $this->forward($subject->getProfileUrl());
        }

        parent::generate();

        array_unshift($this->title, Util::ucFirst(Lang::game('profile')));


        // as demanded by the raid activity tracker
        $bossIds = array(
            // ruby: Halion
            39863,
            // icc: Valanar, Lana'thel, Saurfang, Festergut, Deathwisper, Marrowgar, Putricide, Rotface, Sindragosa, Valithria, Lich King
            37970, 37955, 37813, 36626, 36855, 36612, 36678, 36627, 36853, 36789, 36597,
            // toc: Jaraxxus, Anub'arak
            34780, 34564,
            // ony: Onyxia
            10184,
            // uld: Flame Levi, Ignis, Razorscale, XT-002, Kologarn, Auriaya, Freya, Hodir, Mimiron, Thorim, Vezaxx, Yogg, Algalon
            33113, 33118, 33186, 33293, 32930, 33515, 32906, 32845, 33350, 32864, 33271, 33288, 32871,
            // nax: Anub, Faerlina, Maexxna, Noth, Heigan, Loatheb, Razuvious, Gothik, Patchwerk, Grobbulus, Gluth, Thaddius, Sapphiron, Kel'Thuzad
            15956, 15953, 15952, 15954, 15936, 16011, 16061, 16060, 16028, 15931, 15932, 15928, 15989, 15990
        );
        $this->extendGlobalIds(Type::NPC, ...$bossIds);

        // dummy title from dungeon encounter
        foreach (Lang::profiler('encounterNames') as $id => $name)
            $this->extendGlobalData([Type::NPC => [$id => ['name_'.Lang::getLocale()->json() => $name]]]);
    }

    private function notFound() : never
    {
        if ($this->subjectName && $this->realm)
            $head = Lang::profiler('firstUseTitle', [Util::ucFirst($this->subjectName), $this->realm]);
        else
            $head = Lang::profiler('profiler');

        // unsetting typeId to prevent it from being added to the title string in the input-box is jank galore
        // but it isn't needed for the not-found case anyway, right...?
        unset($this->typeId);

        parent::generateNotFound($head, Lang::profiler('notFound', 'profile'));
    }
}

?>
