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

    public int  $type     = Type::PROFILE;
    public bool $gDataKey = true;

    private ?LocalProfileList $subject = null;

    public function __construct(string $idOrProfile)
    {
        parent::__construct($idOrProfile);

        if (!Cfg::get('PROFILER_ENABLE'))
            $this->generateError();

        if (!$idOrProfile)
            $this->generateError();

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
        if ($subject = DB::Aowow()->selectRow('SELECT `id`, `realmGUID`, `stub` FROM ::profiler_profiles WHERE `realm` = %i AND `custom` = 0 AND `name` = %s AND `renameItr` = %i', $this->realmId, Util::ucFirst($this->subjectName), $rnItr))
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
        $subjects = DB::Characters($this->realmId)->selectAssoc(
           'SELECT    c.`guid` AS "realmGUID", c.`name`, c.`race`, c.`class`, c.`level`, c.`gender`, c.`at_login`, g.`guildid` AS "guildGUID", IFNULL(g.`name`, "") AS "guildName", IFNULL(gm.`rank`, 0) AS "guildRank"
            FROM      characters c
            LEFT JOIN guild_member gm ON gm.`guid` = c.`guid`
            LEFT JOIN guild g ON g.`guildid` = gm.`guildid`
            WHERE     c.`name` = %s AND `level` <= %i AND (`extra_flags` & %i) = 0',
            Util::ucFirst($this->subjectName), MAX_LEVEL, Profiler::CHAR_GMFLAGS
        );
        if ($subject = array_find($subjects ?: [], fn($x) => Util::lower($x['name']) == Util::lower($this->subjectName)))
        {
            $subject['realm'] = $this->realmId;
            $subject['stub']  = 1;

            if ($subject['at_login'] & 0x1)
                $subject['renameItr'] = DB::Aowow()->selectCell('SELECT MAX(`renameItr`) FROM ::profiler_profiles WHERE `realm` = %i AND `custom` = 0 AND `name` = %s', $this->realmId, $subject['name']);

            if ($subject['guildGUID'])
            {
                // create empty guild if necessary to satisfy foreign keys
                $subject['guild'] = DB::Aowow()->selectCell('SELECT `id` FROM ::profiler_guild WHERE `realm` = %i AND `realmGUID` = %i', $this->realmId, $subject['guildGUID']);
                if (!$subject['guild'])
                    $subject['guild'] = DB::Aowow()->qry('INSERT INTO ::profiler_guild (`realm`, `realmGUID`, `stub`, `name`, `nameUrl`) VALUES (%i, %i, 1, %s, %s)', $this->realmId, $subject['guildGUID'], $subject['guildName'], Profiler::urlize($subject['guildName']));
            }

            unset($subject['guildGUID'], $subject['guildName'], $subject['at_login']);

            // create entry from realm with enough basic info to disply tooltips
            DB::Aowow()->qry('REPLACE INTO ::profiler_profiles %v', $subject);
            $this->typeId = DB::Aowow()->selectCell('SELECT `id` FROM ::profiler_profiles WHERE `realm` = %i AND `realmGUID` = %i', $this->realmId, $subject['realmGUID']);

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
            $this->subject = new LocalProfileList(array(['id', $this->typeId]));
            if ($this->subject->error)
                $this->notFound();

            if (!$this->subject->isVisibleToUser())
                $this->notFound();

            // character profile accessed by id
            if (!$this->subject->isCustom() && !$this->subjectName)
                $this->forward($this->subject->getProfileUrl());
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

    protected function generateMetadata(bool $useArticle = true) : void
    {
        if (!$this->typeId || !$this->subject)
        {
            parent::generateMetadata($useArticle);
            return;
        }

        $name   = $this->subject->getField('name');
        $lvl    = $this->subject->getField('level');
        $ra     = $this->subject->getField('race');
        $cl     = $this->subject->getField('class');
        $gender = $this->subject->getField('gender');
        $realm  = $this->subject->getField('realmName');
        $region = $this->subject->getField('region');

        $title = '';
        if ($_ = $this->subject->getField('chosenTitle'))
            $title = (new TitleList(array(['bitIdx', $_])))->getField($gender ? 'female' : 'male', true);

        if ($this->subject->isCustom())
            $name .= Lang::profiler('customProfile');
        else if ($title)
            $name = sprintf($title, $name);

        $this->metaTags[] = ['property' => 'og:title', 'content' => Lang::profiler('profiler') . Lang::main('colon') . $name];
        $this->metaTags[] = ['property' => 'og:type',  'content' => 'article'];

        $desc = '';
        if (!$this->subject->isCustom())
            $desc = sprintf(Lang::meta('description', 'profile'), $name, $lvl, Lang::game('ra', $ra), Lang::game('cl', $cl), $realm, strtoupper($region));
        else if ($_ = $this->subject->getField('description'))
            $desc = $_;

        array_unshift($this->metaTags, ['name' => 'keywords', 'content' => [$name, Lang::profiler('profiler'), 'Profiler', ...Lang::meta('tags', 'generic')]]);

        $this->buildBasicMetadata($desc, $this->subject->getIcon());
    }
}

?>
