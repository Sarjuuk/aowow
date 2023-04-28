<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 5: Profiler g_initPath()
//  tabId 1: Tools    g_initHeader()
class ProfilePage extends GenericPage
{
    use TrProfiler;

    protected $gDataKey  = true;
    protected $mode      = CACHE_TYPE_PAGE;

    protected $type      = Type::PROFILE;

    protected $tabId     = 1;
    protected $path      = [1, 5, 1];
    protected $tpl       = 'profile';
    protected $scripts   = array(
        [SC_JS_FILE,  'js/filters.js'],
        [SC_JS_FILE,  'js/TalentCalc.js'],
        [SC_JS_FILE,  'js/swfobject.js'],
        [SC_JS_FILE,  'js/profile_all.js'],
        [SC_JS_FILE,  'js/profile.js'],
        [SC_JS_FILE,  'js/Profiler.js'],
        [SC_CSS_FILE, 'css/talentcalc.css'],
        [SC_CSS_FILE, 'css/Profiler.css']
    );

    protected $_get      = array(
        'domain' => ['filter' => FILTER_CALLBACK, 'options' => 'GenericPage::checkDomain'],
        'new'    => ['filter' => FILTER_CALLBACK, 'options' => 'GenericPage::checkEmptySet']
    );

    private   $isCustom  = false;
    private   $profile   = null;
    private   $subject   = null;
    private   $rnItr     = 0;
    private   $powerTpl  = '$WowheadPower.registerProfile(%s, %d, %s);';

    public function __construct($pageCall, $pageParam)
    {
        parent::__construct($pageCall, $pageParam);

        if (!CFG_PROFILER_ENABLE)
            $this->error();

        $params = array_map('urldecode', explode('.', $pageParam));
        if ($params[0])
            $params[0] = Profiler::urlize($params[0]);
        if (isset($params[1]))
            $params[1] = Profiler::urlize($params[1], true);

        // temp locale
        if ($this->mode == CACHE_TYPE_TOOLTIP && $this->_get['domain'])
            Util::powerUseLocale($this->_get['domain']);

        if (count($params) == 1 && intval($params[0]))
        {
            // redundancy much?
            $this->subjectGUID = intval($params[0]);
            $this->profile     = intval($params[0]);
            $this->isCustom    = true;                      // until proven otherwise

            $this->subject = new LocalProfileList(array(['id', intval($params[0])]));
            if ($this->subject->error)
                $this->notFound();

            if (!$this->subject->isVisibleToUser())
                $this->notFound();

            if (!$this->subject->isCustom())
                header('Location: '.$this->subject->getProfileUrl(), true, 302);
        }
        else if (count($params) == 3)
        {
            $this->getSubjectFromUrl($pageParam);
            if (!$this->subjectName)
                $this->notFound();

            // names MUST be ucFirst. Since we don't expect partial matches, search this way
            $this->profile = $params;

            // pending rename
            if (preg_match('/([^\-]+)-(\d+)/i', $this->subjectName, $m))
            {
                $this->subjectName = $m[1];
                $this->rnItr = $m[2];
            }

            // 3 possibilities
            // 1) already synced to aowow
            if ($subject = DB::Aowow()->selectRow('SELECT id, realmGUID, cuFlags FROM ?_profiler_profiles WHERE realm = ?d AND realmGUID IS NOT NULL AND name = ? AND renameItr = ?d', $this->realmId, Util::ucFirst($this->subjectName), $this->rnItr))
            {
                $this->subjectGUID = $subject['id'];

                if ($subject['cuFlags'] & PROFILER_CU_NEEDS_RESYNC)
                {
                    $this->handleIncompleteData($params, $subject['realmGUID']);
                    return;
                }

                $this->subject = new LocalProfileList(array(['id', $subject['id']]));
                if ($this->subject->error)
                    $this->notFound();
            }
            // 2) not yet synced but exists on realm (and not a gm character)
            else if (!$this->rnItr && ($char = DB::Characters($this->realmId)->selectRow('SELECT c.guid AS realmGUID, c.name, c.race, c.class, c.level, c.gender, c.at_login, g.guildid AS guildGUID, IFNULL(g.name, "") AS guildName, IFNULL(gm.rank, 0) AS guildRank FROM characters c LEFT JOIN guild_member gm ON gm.guid = c.guid LEFT JOIN guild g ON g.guildid = gm.guildid WHERE c.name = ? AND level <= ?d AND (extra_flags & ?d) = 0', Util::ucFirst($this->subjectName), MAX_LEVEL, Profiler::CHAR_GMFLAGS)))
            {
                $char['realm']   = $this->realmId;
                $char['cuFlags'] = PROFILER_CU_NEEDS_RESYNC;

                if ($char['at_login'] & 0x1)
                    $char['renameItr'] = DB::Aowow()->selectCell('SELECT MAX(renameItr) FROM ?_profiler_profiles WHERE realm = ?d AND realmGUID IS NOT NULL AND name = ?', $this->realmId, $char['name']);

                if ($char['guildGUID'])
                {
                    // create empty guild if nessecary to satisfy foreign keys
                    $char['guild'] = DB::Aowow()->selectCell('SELECT id FROM ?_profiler_guild WHERE realm = ?d AND realmGUID = ?d', $this->realmId, $char['guildGUID']);
                    if (!$char['guild'])
                        $char['guild'] = DB::Aowow()->query('INSERT INTO ?_profiler_guild (realm, realmGUID, cuFlags, name) VALUES (?d, ?d, ?d, ?)', $this->realmId, $char['guildGUID'], PROFILER_CU_NEEDS_RESYNC, $char['guildName']);
                }

                unset($char['guildGUID']);
                unset($char['guildName']);
                unset($char['at_login']);

                // create entry from realm with enough basic info to disply tooltips
                DB::Aowow()->query('REPLACE INTO ?_profiler_profiles (?#) VALUES (?a)', array_keys($char), array_values($char));
                $this->subjectGUID = DB::Aowow()->selectCell('SELECT id FROM ?_profiler_profiles WHERE realm = ?d AND realmGUID = ?d', $this->realmId, $char['realmGUID']);

                $this->handleIncompleteData($params, $char['realmGUID']);
            }
            // 3) does not exist at all
            else
                $this->notFound();
        }
        else if (($params && $params[0]) || !$this->_get['new'])
            $this->notFound();
        else if ($this->_get['new'])
            $this->mode = CACHE_TYPE_NONE;
    }

    protected function generateContent()
    {
        if ($this->doResync)
            return;

        // + .titles ?
        $this->addScript([SC_JS_FILE, '?data=enchants.gems.glyphs.itemsets.pets.pet-talents.quick-excludes.realms.statistics.weight-presets.achievements']);

        // as demanded by the raid activity tracker
        $bossIds = array(
/*          Halion                                                                                                                            */
/* ruby */  39863,
/*          Valanar, Lana'thel, Saurfang, Festergut, Deathwisper, Marrowgar, Putricide, Rotface, Sindragosa, Valithria, Lich King             */
/* icc  */  37970,   37955,     37813,    36626,     36855,       36612,     36678,     36627,   36853,      36789,     36597,
/*          Jaraxxus, Anub'arak                                                                                                               */
/* toc  */  34780,    34564,
/*          Onyxia                                                                                                                            */
/* ony  */  10184,
/*          Flame Levi, Ignis, Razorscale, XT-002, Kologarn, Auriaya, Freya, Hodir, Mimiron, Thorim, Vezaxx, Yogg,  Algalon                   */
/* uld  */  33113,      33118, 33186,      33293,  32930,    33515,   32906, 32845, 33350,   32864,  33271,  33288, 32871,
/*          Anub,  Faerlina, Maexxna, Noth,  Heigan, Loatheb, Razuvious, Gothik, Patchwerk, Grobbulus, Gluth, Thaddius, Sapphiron, Kel'Thuzad */
/* nax  */  15956, 15953,    15952,   15954, 15936,  16011,   16061,     16060,  16028,     15931,     15932, 15928,    15989,     15990
        );
        $this->extendGlobalIds(Type::NPC, ...$bossIds);

        // dummy title from dungeon encounter
        foreach (Lang::profiler('encounterNames') as $id => $name)
            $this->extendGlobalData([Type::NPC => [$id => ['name_'.User::$localeString => $name]]]);
    }

    protected function generatePath()
    {

    }

    protected function generateTitle()
    {
        array_unshift($this->title, Util::ucFirst(Lang::game('profile')));
    }

    protected function generateTooltip()
    {
        $id = $this->profile;
        if (!$this->isCustom)
            $id = "'".$this->profile[0].'.'.urlencode($this->profile[1]).'.'.urlencode($this->profile[2])."'";

        $power = new StdClass();
        if ($this->subject && !$this->subject->error && $this->subject->isVisibleToUser())
        {
            $n = $this->subject->getField('name');
            $l = $this->subject->getField('level');
            $r = $this->subject->getField('race');
            $c = $this->subject->getField('class');
            $g = $this->subject->getField('gender');

            if ($this->isCustom)
                $n .= Lang::profiler('customProfile');
            else if ($_ = $this->subject->getField('title'))
                if ($title = (new TitleList(array(['id', $_])))->getField($g ? 'female' : 'male', true))
                    $n = sprintf($title, $n);

            $power->{'name_'.User::$localeString}    = $n;
            $power->{'tooltip_'.User::$localeString} = $this->subject->renderTooltip();
            $power->icon                             = '$$WH.g_getProfileIcon('.$r.', '.$c.', '.$g.', '.$l.', \''.$this->subject->getIcon().'\')';
        }

        return sprintf($this->powerTpl, $id, User::$localeId, Util::toJSON($power, JSON_AOWOW_POWER));
    }

    public function display(string $override = ''): void
    {
        if ($this->mode != CACHE_TYPE_TOOLTIP)
            parent::display($override);

        // do not cache profile tooltips
        header(MIME_TYPE_JSON);
        die($this->generateTooltip());
    }

    public function notFound(string $title = '', string $msg = '') : void
    {
        parent::notFound($title ?: Util::ucFirst(Lang::profiler('profiler')), $msg ?: Lang::profiler('notFound', 'profile'));
    }

    private function handleIncompleteData($params, $guid)
    {
        if ($this->mode == CACHE_TYPE_TOOLTIP)              // enable tooltip display with basic data we just added
        {
            $this->subject = new LocalProfileList(array(['id', $this->subjectGUID]), ['sv' => $params[1]]);
            if ($this->subject->error)
                $this->notFound();

            $this->profile = $params;
        }
        else                                                // display empty page and queue status
        {
            $this->mode = CACHE_TYPE_NONE;

            // queue full fetch
            if ($newId = Profiler::scheduleResync(Type::PROFILE, $this->realmId, $guid))
            {
                $this->doResync = ['profile', $newId];
                $this->initialSync();
            }
            else                                            // todo: base info should have been created in __construct .. why are we here..?
                header('Location: ?profiles='.$params[0].'.'.$params[1].'&filter=na='.Util::ucFirst($this->subjectName).';ex=on');
        }
    }
}

?>
