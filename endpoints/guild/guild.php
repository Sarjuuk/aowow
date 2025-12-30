<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GuildBaseResponse extends TemplateResponse
{
    use TrProfilerDetail;

    protected  string $template   = 'roster';
    protected  string $pageName   = 'guild';
    protected ?int    $activeTab  = parent::TAB_TOOLS;
    protected  array  $breadcrumb = [1, 5, 2];              // Tools > Profiler > Guilds

    protected  array  $dataLoader = ['realms', 'weight-presets'];
    protected  array  $scripts    = array(
        [SC_JS_FILE,  'js/profile_all.js'],
        [SC_JS_FILE,  'js/profile.js'],
        [SC_CSS_FILE, 'css/Profiler.css']
    );

    public int $type = Type::GUILD;

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
            $this->generateError();

        // 3 possibilities
        // 1) already synced to aowow
        if ($subject = DB::Aowow()->selectRow('SELECT `id`, `realmGUID`, `stub` FROM ?_profiler_guild WHERE `realm` = ?d AND `nameUrl` = ?', $this->realmId, Profiler::urlize($this->subjectName)))
        {
            $this->typeId = $subject['id'];

            if ($subject['stub'])
                $this->handleIncompleteData(Type::GUILD, $subject['realmGUID']);

            return;
        }

        // 2) not yet synced but exists on realm (wont work if we get passed an urlized name, but there is nothing we can do about it)
        $subjects = DB::Characters($this->realmId)->select('SELECT `guildid` AS "realmGUID", `name` FROM guild WHERE `name` = ?', $this->subjectName);
        if ($subject = array_filter($subjects, fn($x) => Util::lower($x['name']) === Util::lower($this->subjectName)))
        {
            $subject = array_pop($subject);
            $subject['realm']   = $this->realmId;
            $subject['stub']    = 1;
            $subject['nameUrl'] = Profiler::urlize($subject['name']);

            // create entry from realm with basic info
            DB::Aowow()->query('INSERT IGNORE INTO ?_profiler_guild (?#) VALUES (?a)', array_keys($subject), array_values($subject));

            $this->handleIncompleteData(Type::GUILD, $subject['realmGUID']);
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

        $subject = new LocalGuildList(array(['id', $this->typeId]));
        if ($subject->error)
            $this->notFound();

        // guild accessed by id
        if (!$this->subjectName)
            $this->forward($subject->getProfileUrl());

        $this->h1 = Lang::profiler('guildRoster', [$subject->getField('name')]);


        /*************/
        /* Menu Path */
        /*************/

        $this->followBreadcrumbPath();


        /**************/
        /* Page Title */
        /**************/

        array_unshift(
            $this->title,
            $subject->getField('name').' ('.$this->realm.' - '.Lang::profiler('regions', $this->region).')',
            Util::ucFirst(Lang::profiler('profiler'))
        );


        /****************/
        /* Main Content */
        /****************/

        $this->redButtons[BUTTON_RESYNC] = [$this->typeId, 'guild'];

        // statistic calculations here

        // smuggle the guild ranks into the html
        if ($ranks = DB::Aowow()->selectCol('SELECT `rank` AS ARRAY_KEY, `name` FROM ?_profiler_guild_rank WHERE `guildId` = ?d', $this->typeId))
            $this->extraHTML = '<script type="text/javascript">var guild_ranks = '.Util::toJSON($ranks).';</script>';


        /**************/
        /* Extra Tabs */
        /**************/

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated');

        // tab: members
        $member = new LocalProfileList(array(['p.guild', $this->typeId]));
        $this->lvTabs->addListviewTab(new Listview(array(
            'data'        => $member->getListviewData(PROFILEINFO_CHARACTER | PROFILEINFO_GUILD),
            'sort'        => [-15],
            'visibleCols' => ['race', 'classs', 'level', 'talents', 'gearscore', 'achievementpoints', 'guildrank'],
            'hiddenCols'  => ['guild', 'location']
        ), ProfileList::$brickFile));

        parent::generate();
    }

    public function notFound() : never
    {
        parent::generateNotFound(Lang::game('guild'), Lang::profiler('notFound', 'guild'));
    }

}

?>
