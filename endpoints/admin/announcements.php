<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminAnnouncementsResponse extends TemplateResponse
{
    protected  int    $requiredUserGroup = U_GROUP_ADMIN | U_GROUP_BUREAU;

    protected  string $template          = 'text-page-generic';
    protected  string $pageName          = 'announcements';
    protected ?int    $activeTab         = parent::TAB_STAFF;
    protected  array  $breadcrumb        = [4, 1, 3];       // Staff > Content > Announcements

    protected  array  $expectedGET       = array(
        'id'     => ['filter' => FILTER_VALIDATE_INT                                                   ],
        'edit'   => ['filter' => FILTER_CALLBACK,     'options' => [self::class, 'checkEmptySet']      ],
        'status' => ['filter' => FILTER_VALIDATE_INT, 'options' => ['min_range' => 0, 'max_range' => 2]]
    );

    protected function generate() : void
    {
        if ($this->_get['id'] && isset($this->_get['status']))
        {
            $this->updateStatus();
            $this->forward($_SERVER['HTTP_REFERER'] ?? '.');
        }
        else if ($this->_get['edit'])
            $this->displayEditor();
        else
            $this->displayListing();

        parent::generate();
    }

    private function updateStatus() : void
    {
        if (!$this->assertGET('status', 'id'))
        {
            trigger_error('AdminAnnouncementsResponse::updateStatus - error in _GET id/status');
            return;
        }

        if (!DB::Aowow()->selectCell('SELECT 1 FROM ?_announcements WHERE `id` = ?d', $this->_get['id']))
        {
            trigger_error('AdminAnnouncementsResponse::updateStatus - announcement does not exist');
            return;
        }

        DB::Aowow()->query('UPDATE ?_announcements SET `status` = ?d WHERE `id` = ?d', $this->_get['status'], $this->_get['id']);
    }

    private function displayEditor() : void
    {
        // TBD
        $this->extraHTML = 'TODO - editor';
    }

    private function displayListing() : void
    {
        // TBD
        // some form of listview with [NEW] button somewhere near the head i guess
        $this->extraHTML = 'TODO - announcements listing';
    }
}
