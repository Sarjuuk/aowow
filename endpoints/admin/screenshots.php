<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminScreenshotsResponse extends TemplateResponse
{
    protected  int $requiredUserGroup = U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_SCREENSHOT;

    protected  string $template       = 'admin/screenshots';
    protected  string $pageName       = 'screenshots';
    protected ?int    $activeTab      = parent::TAB_STAFF;
    protected  array  $breadcrumb     = [4, 1, 5];           // Staff > Content > Screenshots

    protected  array  $scripts        = array(
        [SC_JS_FILE,    'js/screenshot.js'],
        [SC_CSS_STRING, '.layout {margin: 0px 25px; max-width: inherit; min-width: 1200px; }'],
        [SC_CSS_STRING, '#highlightedRow { background-color: #322C1C; }']
    );
    protected  array  $expectedGET    = array(
        'action' => ['filter' => FILTER_CALLBACK,    'options' => [self::class, 'checkTextLine']],
        'all'    => ['filter' => FILTER_CALLBACK,    'options' => [self::class, 'checkEmptySet']],
        'type'   => ['filter' => FILTER_VALIDATE_INT                                            ],
        'typeid' => ['filter' => FILTER_VALIDATE_INT                                            ],
        'user'   => ['filter' => FILTER_CALLBACK,    'options' => 'urldecode'                   ]
    );

    public ?bool  $getAll    = null;
    public  array $ssPages   = [];
    public  array $ssData    = [];
    public  int   $ssNFound  = 0;
    public  array $pageTypes = [];

    protected function generate() : void
    {
        $this->h1 = 'Screenshot Manager';

        // types that can have screenshots
        foreach (Type::getClassesFor(0, 'contribute', CONTRIBUTE_SS) as $type => $obj)
            $this->pageTypes[$type] = Util::ucWords(Lang::game(Type::getFileString($type)));

        $ssGetAll = $this->_get['all'];
        $ssPages  = [];
        $ssData   = [];
        $nMatches = 0;

        if ($this->_get['type'] && $this->_get['typeid'])
            $ssData = ScreenshotMgr::getScreenshots($this->_get['type'], $this->_get['typeid'], nFound: $nMatches);
        else if ($this->_get['user'])
        {
            if (mb_strlen($this->_get['user']) >= 3)
                if ($uId = DB::Aowow()->selectCell('SELECT `id` FROM ?_account WHERE LOWER(`username`) = LOWER(?)', $this->_get['user']))
                    $ssData = ScreenshotMgr::getScreenshots(userId: $uId, nFound: $nMatches);
        }
        else
            $ssPages = ScreenshotMgr::getPages($ssGetAll, $nMatches);

        $this->getAll   = $ssGetAll;
        $this->ssPages  = $ssPages;
        $this->ssData   = $ssData;
        $this->ssNFound = $nMatches;                        // ssm_numPagesFound

        parent::generate();
    }
}
