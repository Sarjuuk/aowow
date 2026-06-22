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

    private GuideList $subject;

    protected function generate() : void
    {
        // main container should be tagged: <div class="text guide-changelog">

        if (!$this->assertGET('id'))
            $this->generateNotFound(Lang::game('guide'), Lang::guide('notFound'));

        $this->subject = new GuideList(array(['id', $this->_get['id']]));
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('guide'), Lang::guide('notFound'));

        if (!$this->subject->canBeViewed() && !$this->subject->userCanView())
            $this->forward('?guides='.$this->subject->getField('category'));


        $this->h1 = Lang::guide('clTitle', [$this->subject->id, $this->subject->getField('title') ?: $this->subject->getField('name')]);


        array_unshift($this->title, strip_tags($this->h1), Lang::game('guide'));


        $this->gPageInfo += ['name' => $this->subject->getField('name')];


        $this->breadcrumb[] = $this->subject->getField('category');


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

        $status = function(int $status) : string
        {
            $wrap = match($status)
            {
                GuideMgr::STATUS_APPROVED,
                GuideMgr::STATUS_REVIEW    => '<b class="q2">%s</b>',
                // other cases?
                default => '<b>%s</b>'
            };

            return sprintf($wrap, Lang::guide('clStatusSet', [Lang::guide('status', $status)]));
        };

        $logEntries = DB::Aowow()->selectAssoc('SELECT a.`username` AS `name`, gcl.`date`, gcl.`status`, gcl.`msg`, gcl.`rev` FROM ::guides_changelog gcl JOIN ::account a ON a.`id` = gcl.`userId` WHERE gcl.`id` = %i ORDER BY gcl.`date` DESC', $this->_get['id']);
        foreach ($logEntries as $log)
        {
            if ($log['status'] != GuideMgr::STATUS_NONE)
                $buff .= '<li class="guide-changelog-status-change">'.$inp($log['rev']).$status($log['status']).$now->formatDate($log['date'], true).'</li>'.PHP_EOL;
            else if ($log['msg'])
                $buff .= '<li>'.$inp($log['rev']).'<b>'.$now->formatDate($log['date'], true).Lang::main('colon').'</b>'.$log['msg'].' <i class="q0">'.Lang::main('byUser', [$log['name'], 'style="text-decoration:underline"']).'</i></li>'.PHP_EOL;
            else
                $buff .= '<li class="guide-changelog-minor-edit">'.$inp($log['rev']).'<b>'.$now->formatDate($log['date'], true).Lang::main('colon').'</b><i class="q3">'.Lang::guide('clMinorEdit').'</i> <i class="q0">'.Lang::main('byUser', [$log['name'], 'style="text-decoration:underline"']).'</i></li>'.PHP_EOL;
        }

        // append creation
        $buff .= '<li class="guide-changelog-created">'.$inp(0).'<b>'.Lang::guide('clCreated').'</b>'.$now->formatDate($this->subject->getField('date'), true).'</li>'.PHP_EOL.'</ul>'.PHP_EOL;

        if (User::isInGroup(U_GROUP_STAFF) && false)
            $buff .= '<input type="button" value="Compare" onclick="alert(\'NYI\');" style="margin-left: 40px;"/>';

        $this->extraHTML = $buff;
    }

    protected function generateMetadata(bool $useArticle = true) : void
    {
        $this->metaTags[] = ['property' => 'og:title', 'content' => implode(' - ', $this->title)];
        $this->metaTags[] = ['property' => 'og:type',  'content' => 'website'];

        array_unshift($this->metaTags, ['name' => 'keywords', 'content' => implode(', ', [...Lang::meta('tags', 'home'), ...Lang::meta('tags', 'generic')])]);

        $this->buildBasicMetadata(Lang::meta('description', 'changelog', [$this->subject->getField('title')]));
    }
}

?>
