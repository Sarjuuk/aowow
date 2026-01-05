<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminVideosResponse extends TemplateResponse
{
    protected  int    $requiredUserGroup = U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO;

    protected  string $template          = 'admin/videos';
    protected  string $pageName          = 'videos';
    protected ?int    $activeTab         = parent::TAB_STAFF;
    protected  array  $breadcrumb        = [4, 1, 17];      // Staff > Content > Videos

    protected  array  $scripts           = array(
        [SC_JS_FILE,    'js/video.js'],
        [SC_CSS_STRING, '.layout {margin: 0px 25px; max-width: inherit; min-width: 1200px; }'],
        [SC_CSS_STRING, '#highlightedRow { background-color: #322C1C; }']
    );
    protected  array  $expectedGET       = array(
        'action' => ['filter' => FILTER_CALLBACK,    'options' => [self::class, 'checkTextLine']],
        'all'    => ['filter' => FILTER_CALLBACK,    'options' => [self::class, 'checkEmptySet']],
        'type'   => ['filter' => FILTER_VALIDATE_INT                                            ],
        'typeid' => ['filter' => FILTER_VALIDATE_INT                                            ],
        'user'   => ['filter' => FILTER_CALLBACK,    'options' => 'urldecode'                   ]
    );

    public ?bool  $getAll    = null;
    public  array $viPages   = [];
    public  array $viData    = [];
    public  int   $viNFound  = 0;
    public  array $pageTypes = [];

    protected function generate() : void
    {
        $this->h1 = 'Video Manager';

        // types that can have videos
        foreach (Type::getClassesFor(0, 'contribute', CONTRIBUTE_SS) as $type => $obj)
            $this->pageTypes[$type] = Util::ucWords(Lang::game(Type::getFileString($type)));

        $viGetAll = $this->_get['all'];
        $viPages  = [];
        $viData   = [];
        $nMatches = 0;

        if ($this->_get['type'] && $this->_get['typeid'])
            $viData = VideoMgr::getVideos($this->_get['type'], $this->_get['typeid'], nFound: $nMatches);
        else if ($this->_get['user'])
        {
            if (mb_strlen($this->_get['user']) >= 3)
                if ($uId = DB::Aowow()->selectCell('SELECT `id` FROM ::account WHERE LOWER(`username`) = LOWER(%s)', $this->_get['user']))
                    $viData = VideoMgr::getVideos(userId: $uId, nFound: $nMatches);
        }
        else
            $viPages = VideoMgr::getPages($viGetAll, $nMatches);

        $this->getAll   = $viGetAll;
        $this->viPages  = $viPages;
        $this->viData   = $viData;
        $this->viNFound = $nMatches;                        // ssm_numPagesFound

        parent::generate();
    }
}
