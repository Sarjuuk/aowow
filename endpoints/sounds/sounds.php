<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SoundsBaseResponse extends TemplateResponse implements ICache
{
    use TrListPage, TrCache;

    protected  int    $type        = Type::SOUND;
    protected  int    $cacheType   = CACHE_TYPE_LIST_PAGE;

    protected  string $template    = 'sounds';
    protected  string $pageName    = 'sounds';
    protected ?int    $activeTab   = parent::TAB_DATABASE;
    protected  array  $breadcrumb  = [0, 19];

    protected  array  $scripts     = [[SC_JS_FILE, 'js/filters.js']];
    protected  array  $expectedGET = array(
        'filter' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => Filter::PATTERN_PARAM]]
    );
    protected  array  $validCats   = [1, 2, 3, 4, 6, 9, 10, 12, 13, 14, 16, 17, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 50, 52, 53];

    public function __construct(string $rawParam)
    {
        $this->getCategoryFromUrl($rawParam);
        if ($this->category)
            $this->forward('?sounds&filter=ty='.$this->category[0]);

        parent::__construct($rawParam);

        if ($this->category)
            $this->subCat = '='.implode('.', $this->category);

        $this->filter = new SoundListFilter($this->_get['filter'] ?? '', ['parentCats' => $this->category]);
        if ($this->filter->shouldReload)
        {
            $_SESSION['error']['fi'] = $this->filter::class;
            $get = $this->filter->buildGETParam();
            $this->forward('?' . $this->pageName . $this->subCat . ($get ? '&filter=' . $get : ''));
        }
        $this->filterError = $this->filter->error;
    }

    protected function generate() : void
    {
        $this->h1 = Util::ucFirst(Lang::game('sounds'));

        $conditions = [Listview::DEFAULT_SIZE];
        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($_ = $this->filter->getConditions())
            $conditions[] = $_;


        /**************/
        /* Page Title */
        /**************/

        $fiForm = $this->filter->values;

        array_unshift($this->title, $this->h1);
        if (count($fiForm['ty']) == 1)
            array_unshift($this->title, Lang::sound('cat', $fiForm['ty'][0]));


        /*************/
        /* Menu Path */
        /*************/

        if (count($fiForm['ty']) == 1)
            $this->breadcrumb[] = $fiForm['ty'][0];


        /****************/
        /* Main Content */
        /****************/

        $this->redButtons = array(
            BUTTON_WOWHEAD  => true,
            BUTTON_PLAYLIST => true
        );
        if ($fiQuery = $this->filter->buildGETParam())
            $this->wowheadLink .= '&filter='.$fiQuery;

        $tabData = [];
        $sounds  = new SoundList($conditions, ['calcTotal' => true]);
        if (!$sounds->error)
        {
            $tabData['data'] = $sounds->getListviewData();

            // create note if search limit was exceeded; overwriting 'note' is intentional
            if ($sounds->getMatches() > Listview::DEFAULT_SIZE)
            {
                $tabData['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_soundsfound', $sounds->getMatches(), Listview::DEFAULT_SIZE);
                $tabData['_truncated'] = 1;
            }
        }

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        $this->lvTabs->addListviewTab(new Listview($tabData, SoundList::$brickFile));

        parent::generate();

        $this->setOnCacheLoaded([self::class, 'onBeforeDisplay']);
    }

    public static function onBeforeDisplay()
    {
        // sort for dropdown-menus in filter
        Lang::sort('sound', 'cat');
    }
}

?>
