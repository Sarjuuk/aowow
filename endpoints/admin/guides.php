<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminGuidesResponse extends TemplateResponse
{
    protected  int    $requiredUserGroup = U_GROUP_STAFF;

    protected  string $template          = 'list-page-generic';
    protected  string $pageName          = 'guides';
    protected ?int    $activeTab         = parent::TAB_STAFF;
    protected  array  $breadcrumb        = [4, 1, 25];      // Staff > Content > Guides Awaiting Approval

    protected function generate() : void
    {
        $this->h1 = 'Pending Guides';
        array_unshift($this->title, $this->h1);

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        parent::generate();

        $pending = new GuideList([['status', GuideMgr::STATUS_REVIEW]]);
        if ($pending->error)
            $data = [];
        else
        {
            $data   = $pending->getListviewData();
            $latest = DB::Aowow()->selectCol('SELECT `typeId` AS ARRAY_KEY, MAX(`rev`) FROM ?_articles WHERE `type` = ?d AND `typeId` IN (?a) GROUP BY `rev`', Type::GUIDE, $pending->getFoundIDs());
            foreach ($latest as $id => $rev)
                $data[$id]['rev'] = $rev;
        }

        $this->lvTabs->addListviewTab(new Listview(array(
            'data'       => array_values($data),
            'hiddenCols' => ['patch', 'comments', 'views', 'rating'],
            'extraCols'  => '$_'
        ), GuideList::$brickFile, 'guideAdminCol'));
    }
}

?>
