<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 5: Profiler g_initPath()
//  tabId 1: Tools    g_initHeader()
class ProfilePage extends GenericPage
{
    use TrProfiler;

    protected $gDataKey  = true;
    protected $mode     = CACHE_TYPE_PAGE;

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
            else if ($char = DB::Characters($this->realmId)->selectRow('SELECT c.guid AS realmGUID, c.name, c.race, c.class, c.level, c.gender, IFNULL(g.name, "") AS guild, IFNULL(gm.rank, 0) AS guildRank FROM characters c LEFT JOIN guild_member gm ON gm.guid = c.guid LEFT JOIN guild g ON g.guildid = gm.guildid WHERE c.name = ? AND level <= ?d AND (extra_flags & ?d) = 0', Util::ucFirst($this->subjectName), MAX_LEVEL, Profiler::CHAR_GMFLAGS))
            {
                $char['realm']   = $this->realmId;
                $char['cuFlags'] = PROFILER_CU_NEEDS_RESYNC;

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
    }

    protected function generateContent()
    {
        if ($this->doResync)
            return;

        // + .titles ?
        $this->addJS('?data=enchants.gems.glyphs.itemsets.pets.pet-talents.quick-excludes.realms.statistics.weight-presets.achievements&locale='.User::$localeId.'&t='.$_SESSION['dataKey']);

        // as demanded by the raid activity tracker
        $bossIds = array(
/*          Halion                                                                                                                */
/* ruby */  39863,
/*          Valanar, Lana'thel, Saurfang, Festergut, Deathwisper, Marrowgar, Putricide, Rotface, Sindragosa, Valithria, Lich King */
/* icc  */  37970,   37955,     37813,    36626,     36855,       36612,     36678,     36627,   36853,      36789,     36597,
/*          Jaraxxus, Anub'arak                                                                                                   */
/* toc  */  34780,    34564,
/*          Onyxia                                                                                                                */
/* ony  */  10184
        );
        // some events have no singular creature to point to .. create dummy entries
        $dummyNPCs = [TYPE_NPC => array(
            100001 => ['name_'.User::$localeString => Lang::profiler('dummyNPCs', 100001)],
            200001 => ['name_'.User::$localeString => Lang::profiler('dummyNPCs', 200001)],
            200002 => ['name_'.User::$localeString => Lang::profiler('dummyNPCs', 200002)],
            200003 => ['name_'.User::$localeString => Lang::profiler('dummyNPCs', 200003)]
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
        // $desc       = $this->subject->getField('description');
        $title      = '';
        if ($_ = $this->subject->getField('chosenTitle'))
            $title = (new TitleList(array(['bitIdx', $_])))->getField($gender ? 'female' : 'male', true);

        if ($this->isCustom)
            $name .= ' (Custom Profile)';
        else if ($title)
            $name = sprintf($title, $name);

        $x .= "\n";
        $x .= "\tname_".User::$localeString.": '".Util::jsEscape($name)."',\n";
        $x .= "\ttooltip_".User::$localeString.": '".$this->subject->renderTooltip()."',\n";
        $x .= "\ticon: \$WH.g_getProfileIcon(".$ra.", ".$cl.", ".$gender.", ".$lvl."),\n";           // (race, class, gender, level, iconOrId, 'medium')
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
