<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AreatriggersBaseResponse extends TemplateResponse implements ICache
{
    use TrListPage, TrCache;

    protected  int    $type              = Type::AREATRIGGER;
    protected  int    $cacheType         = CACHE_TYPE_LIST_PAGE;
    protected  int    $requiredUserGroup = U_GROUP_STAFF;

    protected  string $template          = 'areatriggers';
    protected  string $pageName          = 'areatriggers';
    protected ?int    $activeTab         = parent::TAB_DATABASE;
    protected  array  $breadcrumb        = [0, 102];

    protected  array  $scripts           = [[SC_JS_FILE, 'js/filters.js']];
    protected  array  $expectedGET       = ['filter' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => Filter::PATTERN_PARAM]]];
    protected  array  $validCats         = [0, 1, 2, 3, 4, 5];

    public function __construct(string $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);

        if (isset($this->category[0]))
            $this->forward('?areatriggers&filter=ty='.$this->category[0]);

        parent::__construct($pageParam);

        $this->filter = new AreaTriggerListFilter($this->_get['filter'] ?? '');
        if ($this->filter->shouldReload)
        {
            $_SESSION['error']['fi'] = $this->filter::class;
            $get = $this->filter->buildGETParam();
            $this->forward('?' . $this->pageName . ($get ? '&filter=' . $get : ''));
        }
        $this->filterError = $this->filter->error;
    }

    protected function generate() : void
    {
        $this->h1 = Util::ucFirst(Lang::game('areatriggers'));

        $fiForm = $this->filter->values;


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1);

        if (count($fiForm['ty']) == 1)
            array_unshift($this->title, Lang::areatrigger('types', $fiForm['ty'][0]));


        /*************/
        /* Menu Path */
        /*************/

        if (count($fiForm['ty']) == 1)
            $this->breadcrumb[] = $fiForm['ty'];


        /****************/
        /* Main Content */
        /****************/

        $this->redButtons[BUTTON_WOWHEAD] = false;

        $conditions = [Listview::DEFAULT_SIZE];
        if ($_ = $this->filter->getConditions())
            $conditions[] = $_;

        $tabData = [];
        $trigger = new AreaTriggerList($conditions, ['calcTotal' => true]);
        if (!$trigger->error)
        {
            $tabData['data'] = $trigger->getListviewData();

            // create note if search limit was exceeded; overwriting 'note' is intentional
            if ($trigger->getMatches() > Listview::DEFAULT_SIZE)
            {
                $tabData['note'] = sprintf(Util::$tryFilteringEntityString, $trigger->getMatches(), '"'.Lang::game('areatriggers').'"', Listview::DEFAULT_SIZE);
                $tabData['_truncated'] = 1;
            }
        }

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        $this->lvTabs->addListviewTab(new Listview($tabData, AreaTriggerList::$brickFile, 'areatrigger'));

        parent::generate();
    }
}

?>
