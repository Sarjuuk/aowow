<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GuideChangelogResponse extends TemplateResponse
{
    protected  string $template    = 'text-page-generic';
    protected  string $pageName    = 'guide=changelog';
    protected ?int    $activeTab   = parent::TAB_GUIDES;
    protected  array  $breadcrumb  = [6];

    // why is this here: is there a mediawiki like diff function for staff?
    protected  array  $scripts     = [[SC_CSS_STRING, 'li input[type="radio"] { margin:0px 5px 0px 0px; }']];
    protected  array  $expectedGET = array(
        'id'  => ['filter' => FILTER_VALIDATE_INT]
    );

    protected function generate() : void
    {
        // main container should be tagged: <div class="text guide-changelog">

        if (!$this->assertGET('id'))
            $this->generateNotFound(Lang::game('guide'), Lang::guide('notFound'));

        $guide = new GuideList(array(['id', $this->_get['id']]));
        if ($guide->error)
            $this->generateNotFound(Lang::game('guide'), Lang::guide('notFound'));

        if (!$guide->canBeViewed() && !$guide->userCanView())
            $this->forward('?guides='.$guide->getField('category'));

        $this->h1 = Lang::guide('clTitle', [$this->_get['id'], $guide->getField('title')]);
        if (!$this->h1)
            $this->h1 = $guide->getField('name');

        $this->gPageInfo += ['name' => $guide->getField('name')];


        $this->breadcrumb[] = $guide->getField('category');


        parent::generate();

        /* - NYI (see "&& false")
        $this->addScript([SC_JS_STRING,

        <<<JS
            $(document).ready(function() {
                var radios = $("input[type=radio]");
                function limit(col, val) {
                    radios.each(function(i, e) {
                        if (col == e.name)
                            return;

                        if (col == "b")
                            e.disabled = (val <= parseInt(e.value));
                        else if (col == "a")
                            e.disabled = (val >= parseInt(e.value));
                    });

                };

                radios.each(function (i, e) {
                    e.onchange = limit.bind(this, e.name, parseInt(e.value));

                    if (i < 2 && e.name == "b") // first pair
                        $(e).trigger("click");
                    else if (e.value == 0 && e.name == "a") // last pair
                        $(e).trigger("click");
                });
            });
    JS
        ]);
        */

        $buff = '<ul>';
        $inp  = fn($rev) => User::isInGroup(U_GROUP_STAFF) && false ? ($rev !== null ? '<input name="a" value="'.$rev.'" type="radio"/><input name="b" value="'.$rev.'" type="radio"/><b>' : '<b style="margin-left:38px;">') : '';
        $now  = new DateTime();

        $logEntries = DB::Aowow()->select('SELECT a.`username` AS `name`, gcl.`date`, gcl.`status`, gcl.`msg`, gcl.`rev` FROM ?_guides_changelog gcl JOIN ?_account a ON a.`id` = gcl.`userId` WHERE gcl.`id` = ?d ORDER BY gcl.`date` DESC', $this->_get['id']);
        foreach ($logEntries as $log)
        {
            if ($log['status'] != GuideMgr::STATUS_NONE)
                $buff .= '<li class="guide-changelog-status-change">'.$inp($log['rev']).'<b>'.Lang::guide('clStatusSet', [Lang::guide('status', $log['status'])]).'</b>'.$now->formatDate($log['date'], true)."</li>\n";
            else if ($log['msg'])
                $buff .= '<li>'.$inp($log['rev']).'<b>'.$now->formatDate($log['date'], true).Lang::main('colon').'</b>'.$log['msg'].' <i class="q0">'.Lang::main('byUser', [$log['name'], 'style="text-decoration:underline"'])."</i></li>\n";
            else
                $buff .= '<li class="guide-changelog-minor-edit">'.$inp($log['rev']).'<b>'.$now->formatDate($log['date'], true).Lang::main('colon').'</b><i>'.Lang::guide('clMinorEdit').'</i> <i class="q0">'.Lang::main('byUser', [$log['name'], 'style="text-decoration:underline"'])."</i></li>\n";
        }

        // append creation
        $buff .= '<li class="guide-changelog-created">'.$inp(0).'<b>'.Lang::guide('clCreated').'</b>'.$now->formatDate($guide->getField('date'), true)."</li>\n</ul>\n";

        if (User::isInGroup(U_GROUP_STAFF) && false)
            $buff .= '<input type="button" value="Compare" onclick="alert(\'NYI\');" style="margin-left: 40px;"/>';

        $this->extraHTML = $buff;
    }
}

?>
