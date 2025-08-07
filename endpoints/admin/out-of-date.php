<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminOutofdateResponse extends TemplateResponse
{
    protected  int    $requiredUserGroup = U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_MOD;

    protected  string $template          = 'list-page-generic';
    protected  string $pageName          = 'out-of-date';
    protected ?int    $activeTab         = parent::TAB_STAFF;
    protected  array  $breadcrumb        = [4, 1, 23];      // Staff > Content > Out of Date Comments

    protected function generate() : void
    {
        $this->h1 = 'Out of Date Comments';
        array_unshift($this->title, $this->h1);

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        parent::generate();

        $this->lvTabs->addListviewTab(new Listview(array(
            'data'      => CommunityContent::getCommentPreviews(['flags' => CC_FLAG_OUTDATED]),
            'extraCols' => '$_'
        ), 'commentpreview', 'commentAdminCol'));
    }
}

?>
