<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class FactionsBaseResponse extends TemplateResponse implements ICache
{
    use TrListPage, TrCache;

    protected  int    $type       = Type::FACTION;
    protected  int    $cacheType  = CACHE_TYPE_LIST_PAGE;

    protected  string $template   = 'list-page-generic';
    protected  string $pageName   = 'factions';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 7];

    protected  array  $validCats  = array(
        1118 => [469, 891, 67, 892, 169],
        980  => [936],
        1097 => [1037, 1052, 1117],
        0    => true
    );

    public function __construct(string $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);

        parent::__construct($pageParam);
    }

    protected function generate() : void
    {
        $this->h1 = Util::ucFirst(Lang::game('factions'));


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1);
        if ($this->category)
        {
            switch (count($this->category))
            {
                case 1:
                    $t = Lang::faction('cat', $this->category[0]);
                    array_unshift($this->title, is_array($t) ? $t[0] : $t);
                    break;
                case 2:
                    array_unshift($this->title, Lang::faction('cat', $this->category[0], $this->category[1]));
                    break;
            }
        }


        /*************/
        /* Menu Path */
        /*************/

        foreach ($this->category as $c)
            $this->breadcrumb[] = $c;


        /****************/
        /* Main Content */
        /****************/

        $this->redButtons[BUTTON_WOWHEAD] = true;

        $conditions = [Listview::DEFAULT_SIZE];

        if (!User::isInGroup(U_GROUP_EMPLOYEE))             // unlisted factions
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if (isset($this->category[1]))
            $conditions[] = ['parentFactionId', $this->category[1]];
        else if (isset($this->category[0]))
        {
            if ($this->category[0])
                $subs = DB::Aowow()->selectCol('SELECT `id` FROM ?_factions WHERE `parentFactionId` = ?d', $this->category[0]);
            else
                $subs = [0];

            $conditions[] = ['OR', ['parentFactionId', $subs], ['id', $subs]];
        }

        $data = [];
        $factions = new FactionList($conditions);
        if (!$factions->error)
            $data = $factions->getListviewData();

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        $this->lvTabs->addListviewTab(new Listview(['data' => $data], FactionList::$brickFile));

        parent::generate();
    }
}

?>
