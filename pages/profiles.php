<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');

// !do not cache!
/* older version
new Listview({
    template: 'profile',
    id: 'characters',
    name: LANG.tab_characters,
    parent: 'lkljbjkb574',
    visibleCols: ['race','classs','level','talents','gearscore','achievementpoints','rating'],
    sort: [-15],
    hiddenCols: ['arenateam','guild','location'],
    onBeforeCreate: pr_initRosterListview,
    data: [
        {id:30577430,name:'Çircus',achievementpoints:0,guild:'swaggin',guildrank:5,arenateam:{2:{name:'the bird knows the word',rating:1845}},realm:'maiev',realmname:'Maiev',battlegroup:'whirlwind',battlegroupname:'Whirlwind',region:'us',roster:2,row:1},
        {id:10602015,name:'Gremiss',achievementpoints:3130,guild:'Team Discovery Channel',guildrank:3,arenateam:{2:{name:'the bird knows the word',rating:1376}},realm:'maiev',realmname:'Maiev',battlegroup:'whirlwind',battlegroupname:'Whirlwind',region:'us',level:80,race:5,gender:1,classs:9,faction:1,gearscore:2838,talenttree1:54,talenttree2:17,talenttree3:0,talentspec:1,roster:2,row:2}
    ]
});
*/

// menuId 5: Profiler g_initPath()
//  tabId 1: Tools    g_initHeader()
class ProfilesPage extends GenericPage
{
    protected $tpl    = 'profiles';
    protected $js     = ['filters.js', 'profile_all.js', 'profile.js'];
    protected $css    = [['path' => 'profiler.css']];
    protected $tabId  = 1;
    protected $path   = [1, 5, 0];
    protected $realm  = '';                                 // not sure about the use
    protected $region = '';                                 // seconded..

    public function __construct($pageCall, $pageParam)
    {
        @include('datasets/ProfilerExampleChar');           // tmp char data
        $this->character = $character;

        // soo ..
        // we require a list and filter-handler for profiles

        parent::__construct($pageCall, $pageParam);
    }

    protected function generateTitle()
    {
        $this->title[] = Util::ucFirst(Lang::game('profiles'));
    }

    protected function generatePath()
    {
        $this->path[] = $this->character['region'][0];
        $this->path[] = $this->character['battlegroup'][0];
        $this->path[] = $this->character['realm'][0];
    }

    protected function generateContent()
    {
        $this->addJS('?data=weight-presets.realms&locale='.User::$localeId.'&t='.$_SESSION['dataKey']);


        $tDistrib = $this->getTalentDistribution($this->character['talents']['builds'][$this->character['talents']['active']]['talents']);
        $exampleRow = array(
            'id'                => 0,
            'name'              => $this->character['name'],
            'achievementpoints' => $this->character['achievementpoints'],
            'guild'             => $this->character['guild'], // 0 if none
            'guildRank'         => -1,
            'realm'             => $this->character['realm'][0],
            'realmname'         => $this->character['realm'][1],
            'battlegroup'       => $this->character['battlegroup'][0],
            'battlegroupname'   => $this->character['battlegroup'][0],
            'region'            => $this->character['region'][0],
            'level'             => $this->character['level'],
            'race'              => $this->character['race'],
            'gender'            => $this->character['gender'],
            'classs'            => $this->character['classs'],
            'faction'           => $this->character['faction'],
            'talenttree1'       => $tDistrib[0],
            'talenttree2'       => $tDistrib[1],
            'talenttree3'       => $tDistrib[2],
            'talentspec'        => $this->character['talents']['active']
        );

        // description:'{$curr.description|escape:"javascript"}',
        // icon:'{$curr.icon|escape:"javascript"}',
        // published:1,
        // pinned:1,
        // deleted:1,

        // dont send ID for real chars unless they have some kind of custom avatar
        // on second thought .. ids are required for resync, but the function that generates the icon is faulty

        $this->lvTabs[] = ['profile', array(
            'data'           => [$exampleRow],
            'id'             => 'characters',
            'name'           => '$LANG.tab_characters',
            'hideCount'      => 1,
            '_truncated'     => 1,
            'roster'         => 3,
            'visibleCols'    => ['race','classs','level','talents','achievementpoints'],
            'note'           => '$$WH.sprintf(LANG.lvnote_charactersfound, \'20,592,390\', 200) + LANG.dash + LANG.lvnote_tryfiltering.replace(\'<a>\', \'<a href="javascript:;" onclick="fi_toggle()">\')',
            'onBeforeCreate' => '$pr_initRosterListview'        // $_GET['roster'] = 1|2|3|4 .. 2,3,4 arenateam-size (4 => 5-man), 1 guild .. it puts a resync button on the lv...
        )];



        $this->filter = ['query' => 1, 'fi' => []];


        Lang::sort('game', 'cl');
        Lang::sort('game', 'ra');
    }

    private function getTalentDistribution($tString)
    {
        $classMask = 1 << ($this->character['classs'] - 1);
        $distrib   = DB::Aowow()->selectCol('SELECT COUNT(t.id) FROM dbc_talent t JOIN dbc_talenttab tt ON t.tabId = tt.id WHERE tt.classMask & ?d GROUP BY tt.id ORDER BY tt.tabNumber ASC', $classMask);
        $result    = [0, 0, 0];

        $start = 0;
        foreach ($distrib as $idx => $len)
        {
            $result[$idx] = array_sum(str_split(substr($tString, $start, $len)));
            $start += $len;
        }

        return $result;
    }
}

?>
