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

    protected $type      = TYPE_PROFILE;

    protected $tabId     = 1;
    protected $path      = [1, 5, 1];
    protected $tpl       = 'profile';
    protected $js        = ['filters.js', 'TalentCalc.js', 'swfobject.js', 'profile_all.js', 'profile.js', 'Profiler.js'];
    protected $css       = array(
        ['path' => 'talentcalc.css'],
        ['path' => 'Profiler.css']
    );

    private   $isCustom  = false;
    private   $profile   = null;

    public function __construct($pageCall, $pageParam)
    {
        if (!CFG_PROFILER_ENABLE)
            $this->error();

        $params = array_map('urldecode', explode('.', $pageParam));
        if ($params[0])
            $params[0] = Profiler::urlize($params[0]);
        if (isset($params[1]))
            $params[1] = Profiler::urlize($params[1]);

        parent::__construct($pageCall, $pageParam);

        // temp locale
        if ($this->mode == CACHE_TYPE_TOOLTIP && isset($_GET['domain']))
            Util::powerUseLocale($_GET['domain']);

        if (count($params) == 1 && intval($params[0]))
        {
            // redundancy much?
            $this->subjectGUID = intval($params[0]);
            $this->profile     = intval($params[0]);

            $this->subject = new LocalProfileList(array(['id', intval($params[0])]));
            if ($this->subject->error)
                $this->notFound();

            if (!User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU))
            {
                if (!($this->subject->getField('cuFlags') & PROFILER_CU_PUBLISHED) && $this->subject->getField('user') != User::$id)
                    $this->notFound();

                if (($this->subject->getField('cuFlags') & PROFILER_CU_DELETED))
                    $this->notFound();
            }

            if ($this->subject->isCustom())
                $this->isCustom  = true;
            else
                header('Location: '.$this->subject->getProfileUrl(), true, 302);
        }
        else if (count($params) == 3)
        {
            $this->getSubjectFromUrl($pageParam);
            if (!$this->subjectName)
                $this->notFound();

            // names MUST be ucFirst. Since we don't expect partial matches, search this way
            $this->profile = $params;

            // 3 possibilities
            // 1) already synced to aowow
            if ($subject = DB::Aowow()->selectRow('SELECT id, realmGUID, cuFlags FROM ?_profiler_profiles WHERE realm = ?d AND name = ?', $this->realmId, Util::ucFirst($this->subjectName)))
            {
                if ($subject['cuFlags'] & PROFILER_CU_NEEDS_RESYNC)
                {
                    $this->handleIncompleteData($params, $subject['realmGUID']);
                    return;
                }

                $this->subjectGUID = $subject['id'];
                $this->subject     = new LocalProfileList(array(['id', $subject['id']]));
                if ($this->subject->error)
                    $this->notFound();
            }
            // 2) not yet synced but exists on realm (and not a gm character)
            else if ($char = DB::Characters($this->realmId)->selectRow('SELECT c.guid AS realmGUID, c.name, c.race, c.class, c.level, c.gender, g.guildid AS guildGUID, IFNULL(g.name, "") AS guildName, IFNULL(gm.rank, 0) AS guildRank FROM characters c LEFT JOIN guild_member gm ON gm.guid = c.guid LEFT JOIN guild g ON g.guildid = gm.guildid WHERE c.name = ? AND level <= ?d AND (extra_flags & ?d) = 0', Util::ucFirst($this->subjectName), MAX_LEVEL, Profiler::CHAR_GMFLAGS))
            {
                $char['realm']   = $this->realmId;
                $char['cuFlags'] = PROFILER_CU_NEEDS_RESYNC;

                if ($char['guildGUID'])
                {
                    // create empty guild if nessecary to satisfy foreign keys
                    $char['guild'] = DB::Aowow()->selectCell('SELECT id FROM ?_profiler_guild WHERE realm = ?d AND realmGUID = ?d', $this->realmId, $char['guildGUID']);
                    if (!$char['guild'])
                        $char['guild'] = DB::Aowow()->query('INSERT INTO ?_profiler_guild (realm, realmGUID, cuFlags, name) VALUES (?d, ?d, ?d, ?)', $this->realmId, $char['guildGUID'], PROFILER_CU_NEEDS_RESYNC, $char['guildName']);
                }

                unset($char['guildGUID']);
                unset($char['guildName']);

                // create entry from realm with enough basic info to disply tooltips
                DB::Aowow()->query('INSERT IGNORE INTO ?_profiler_profiles (?#) VALUES (?a)', array_keys($char), array_values($char));

                $this->handleIncompleteData($params, $char['realmGUID']);
            }
            // 3) does not exist at all
            else
                $this->notFound();
        }
        else if (($params && $params[0]) || !isset($_GET['new']))
            $this->notFound();
        else if (isset($_GET['new']))
            $this->mode = CACHE_TYPE_NONE;
    }

    protected function generateContent()
    {
        if ($this->doResync)
            return;

        // + .titles ?
        $this->addJS('?data=enchants.gems.glyphs.itemsets.pets.pet-talents.quick-excludes.realms.statistics.weight-presets.achievements&locale='.User::$localeId.'&t='.$_SESSION['dataKey']);

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
        // some events have no singular creature to point to .. create dummy entries
        $dummyNPCs = [TYPE_NPC => array(
            100001 => ['name_'.User::$localeString => Lang::profiler('dummyNPCs', 100001)], // Gunship Battle
            200001 => ['name_'.User::$localeString => Lang::profiler('dummyNPCs', 200001)], // Northrend Beasts
            200002 => ['name_'.User::$localeString => Lang::profiler('dummyNPCs', 200002)], // Faction Champions
            200003 => ['name_'.User::$localeString => Lang::profiler('dummyNPCs', 200003)], // Val'kyr Twins
            300001 => ['name_'.User::$localeString => Lang::profiler('dummyNPCs', 300001)], // The Four Horsemen
            400001 => ['name_'.User::$localeString => Lang::profiler('dummyNPCs', 400001)]  // Assembly of Iron
        )];

        $this->extendGlobalIds(TYPE_NPC, $bossIds);
        $this->extendGlobalData($dummyNPCs);
    }

    protected function generatePath()
    {

    }

    protected function generateTitle()
    {
        array_unshift($this->title, Util::ucFirst(Lang::game('profile')));
    }

    protected function generateTooltip($asError = false)
    {
        $id = $this->profile;
        if (!$this->isCustom)
            $id = "'".$this->profile[0].'.'.$this->profile[1].'.'.urlencode($this->profile[2])."'";

        $x = '$WowheadPower.registerProfile('.$id.', '.User::$localeId.', {';
        if ($asError)
            return $x."});";

        $name       = $this->subject->getField('name');
        $guild      = $this->subject->getField('guild');
        $guildRank  = $this->subject->getField('guildrank');
        $lvl        = $this->subject->getField('level');
        $ra         = $this->subject->getField('race');
        $cl         = $this->subject->getField('class');
        $gender     = $this->subject->getField('gender');
        $title      = '';
        if ($_ = $this->subject->getField('title'))
            $title = (new TitleList(array(['id', $_])))->getField($gender ? 'female' : 'male', true);

        if ($this->isCustom)
            $name .= Lang::profiler('customProfile');
        else if ($title)
            $name = sprintf($title, $name);

        $x .= "\n";
        $x .= "\tname_".User::$localeString.": '".Util::jsEscape($name)."',\n";
        $x .= "\ttooltip_".User::$localeString.": '".$this->subject->renderTooltip()."',\n";
        $x .= "\ticon: \$WH.g_getProfileIcon(".$ra.", ".$cl.", ".$gender.", ".$lvl.", '".$this->subject->getIcon()."'),\n";   // (race, class, gender, level, iconOrId, 'medium')
        $x .= "});";

        return $x;
    }

    public function display($override = '')
    {
        if ($this->mode != CACHE_TYPE_TOOLTIP)
            return parent::display($override);

        // do not cache profile tooltips
        header('Content-type: application/x-javascript; charset=utf-8');
        die($this->generateTooltip());
    }

    public function notFound($title = '', $msg = '')
    {
        if ($this->mode != CACHE_TYPE_TOOLTIP)
            return parent::notFound($title ?: Util::ucFirst(Lang::profiler('profiler')), $msg ?: Lang::profiler('notFound', 'profile'));

        header('Content-type: application/x-javascript; charset=utf-8');
        echo $this->generateTooltip(true);
        exit();
    }

    private function handleIncompleteData($params, $guid)
    {
        if ($this->mode == CACHE_TYPE_TOOLTIP)      // enable tooltip display with basic data we just added
        {
            $this->subject = new LocalProfileList(array(['name', Util::ucFirst($this->subjectName)]), ['sv' => $params[1]]);
            if ($this->subject->error)
                $this->notFound();

            $this->profile = $params;
        }
        else                                        // display empty page and queue status
        {
            $this->mode = CACHE_TYPE_NONE;

            // queue full fetch
            $newId = Profiler::scheduleResync(TYPE_PROFILE, $this->realmId, $guid);

            $this->doResync = ['profile', $newId];
            $this->initialSync();
        }
    }
}

?>
