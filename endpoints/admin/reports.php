<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminReportsResponse extends TemplateResponse
{
    protected  int    $requiredUserGroup = U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_EDITOR | U_GROUP_MOD | U_GROUP_LOCALIZER | U_GROUP_SCREENSHOT | U_GROUP_VIDEO;

    protected  string $template          = 'admin/reports';
    protected  string $pageName          = 'reports';
    protected ?int    $activeTab         = parent::TAB_STAFF;
    protected  array  $breadcrumb        = [4, 5];          // Staff > Reports

    protected function generate() : void
    {
        $this->h1 = 'Reports';
        array_unshift($this->title, $this->h1);

        $this->extraHTML = 'NYI';

        parent::generate();
    }
}

?>
