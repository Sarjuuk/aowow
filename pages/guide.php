<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId ?: Category g_initPath()
//  tabid 6: Guides   g_initHeader()
class GuidePage extends GenericPage
{
    use TrDetailPage;

    const SHOW_NEW       = 1;
    const SHOW_EDITOR    = 2;
    const SHOW_GUIDE     = 3;
    const SHOW_CHANGELOG = 4;

    const VALID_URL      = '/^[a-z0-9=_&\.\/\-]{2,64}$/i';

    protected /* array */  $guideRating  = [];
    protected /* ?string */$extraHTML    = null;

    protected /* int */    $type          = Type::GUIDE;
    protected /* int */    $typeId        = 0;
    protected /* int */    $guideRevision = -1;
    protected /* string */ $tpl           = 'detail-page-generic';
    protected /* array */  $path          = [6];
    protected /* int */    $tabId         = 6;
    protected /* int */    $mode          = CACHE_TYPE_PAGE;
    protected /* string */ $author        = '';
    protected /* array */  $gPageInfo     = [];
    protected /* int */    $show          = self::SHOW_GUIDE;
    protected /* int */    $articleUrl    = '';

    private   /* array */  $validCats     = [1, 2, 3, 4, 5, 6, 7, 8, 9];
    private   /* string */ $extra         = '';
    private   /* string */ $powerTpl      = '$WowheadPower.registerGuide(%s, %d, %s);';
    private   /* array */  $editorFields  = [];
    private   /* bool */   $save          = false;

    protected /* array */ $_get = array(
        'id'  => ['filter' => FILTER_CALLBACK, 'options' => 'GenericPage::checkInt'],
        'rev' => ['filter' => FILTER_CALLBACK, 'options' => 'GenericPage::checkInt']
    );

    protected /* array */ $_post = array(
        'save'        => ['filter' => FILTER_CALLBACK, 'options' => 'GenericPage::checkEmptySet'],
        'submit'      => ['filter' => FILTER_CALLBACK, 'options' => 'GenericPage::checkEmptySet'],
        'title'       => ['filter' => FILTER_CALLBACK, 'options' => 'GenericPage::checkTextLine'],
        'name'        => ['filter' => FILTER_CALLBACK, 'options' => 'GenericPage::checkTextLine'],
        'description' => ['filter' => FILTER_CALLBACK, 'options' => 'GuidePage::checkDescription'],
        'changelog'   => ['filter' => FILTER_CALLBACK, 'options' => 'GenericPage::checkTextBlob'],
        'body'        => ['filter' => FILTER_CALLBACK, 'options' => 'GenericPage::checkTextBlob'],
        'locale'      => ['filter' => FILTER_CALLBACK, 'options' => 'GenericPage::checkInt'],
        'category'    => ['filter' => FILTER_CALLBACK, 'options' => 'GenericPage::checkInt'],
        'specId'      => ['filter' => FILTER_CALLBACK, 'options' => 'GenericPage::checkInt'],
        'classId'     => ['filter' => FILTER_CALLBACK, 'options' => 'GenericPage::checkInt']
    );

    public function __construct($pageCall, $pageParam)
    {
        $this->contribute = CONTRIBUTE_CO;

        $guide = explode( "&", $pageParam, 2);

        parent::__construct($pageCall, $pageParam);

        if (isset($guide[1]) && preg_match(self::VALID_URL, $guide[1]))
            $this->extra = $guide[1];


        /**********************/
        /* get mode + guideId */
        /**********************/

        if (Util::checkNumeric($guide[0], NUM_CAST_INT))
            $this->typeId = $guide[0];
        else if (preg_match(self::VALID_URL, $guide[0]))
        {
            switch ($guide[0])
            {
                case 'changelog':
                    if (!$this->_get['id'])
                        break;

                    $this->show    = self::SHOW_CHANGELOG;
                    $this->tpl     = 'text-page-generic';
                    $this->article = false;                 // do not include article from db

                    // main container should be tagged: <div class="text guide-changelog">
                    // why is this here: is there a mediawiki like diff function for staff?
                    $this->addScript([SC_CSS_STRING, 'li input[type="radio"] {margin:0}']);

                    $this->typeId = $this->_get['id'];      // just to display sensible not-found msg
                    if ($id = DB::Aowow()->selectCell('SELECT `id` FROM ?_guides WHERE `id` = ?d', $this->typeId))
                        $this->typeId = intVal($id);

                    break;
                case 'new':
                    if (User::canWriteGuide())
                    {
                        $this->show          = self::SHOW_NEW;
                        $this->guideRevision = null;

                        $this->initNew();
                        return;                             // do not create new GuideList
                    }
                    break;
                case 'edit':
                    if (User::canWriteGuide())
                    {
                        if (!$this->initEdit())
                            $this->notFound(Lang::game('guide'), Lang::guide('notFound'));

                        $this->show = self::SHOW_EDITOR;
                    }
                    break;
                default:
                    if ($id = DB::Aowow()->selectCell('SELECT `id` FROM ?_guides WHERE `url` = ?', Util::lower($guide[0])))
                    {
                        $this->typeId        = intVal($id);
                        $this->guideRevision = null;
                        $this->articleUrl    = Util::lower($guide[0]);
                    }
            }
        }


        /*********************/
        /* load actual guide */
        /*********************/

        $this->subject = new GuideList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound(Lang::game('guide'), Lang::guide('notFound'));

        if (!$this->subject->canBeViewed() && !$this->subject->userCanView())
            header('Location: ?guides='.$this->subject->getField('category'), true, 302);

        if ($this->show == self::SHOW_GUIDE && $this->_get['rev'] !== null && !$this->articleUrl && $this->subject->userCanView())
            $this->guideRevision = $this->_get['rev'];
        else if ($this->show == self::SHOW_GUIDE && !$this->articleUrl)
            $this->guideRevision = $this->subject->getField('rev');
        else
            $this->guideRevision = null;

        if (!$this->name)
            $this->name = $this->subject->getField('name');
    }

    protected function generateContent() : void
    {
        /*
        match ($this->show)
        {
            self::SHOW_NEW       => $this->displayNew(),
            self::SHOW_EDITOR    => $this->displayEditor(),
            self::SHOW_GUIDE     => $this->displayGuide(),
            self::SHOW_CHANGELOG => $this->displayChangelog(),
            default              => trigger_error('GuidePage::generateContent - what content!?')
        };
        */
        switch ($this->show)
        {
            case self::SHOW_NEW:
                $this->displayNew();
                break;
            case self::SHOW_EDITOR:
                $this->displayEditor();
                break;
            case self::SHOW_GUIDE:
                $this->displayGuide();
                break;
            case self::SHOW_CHANGELOG:
                $this->displayChangelog();
                break;
            default:
                trigger_error('GuidePage::generateContent - what content!?');
        }
    }

    private function displayNew() : void
    {
        // init required template vars
        $this->editorFields = array(
            'locale' => User::$localeId,
            'status' => GUIDE_STATUS_DRAFT
        );
    }

    private function displayEditor() : void
    {
        // can't check in init as subject is unknown
        if ($this->subject->getField('status') == GUIDE_STATUS_ARCHIVED)
            $this->notFound(Lang::game('guide'), Lang::guide('notFound'));

        $status    = GUIDE_STATUS_NONE;
        $rev       = DB::Aowow()->selectCell('SELECT `rev` FROM ?_articles WHERE `type` = ?d AND `typeId` = ?d ORDER BY `rev` DESC LIMIT 1', Type::GUIDE, $this->typeId);
        $curStatus = DB::Aowow()->selectCell('SELECT `status` FROM ?_guides WHERE `id` = ?d ', $this->typeId);
        if ($rev === null)
            $rev = 0;

        if ($this->save)
        {
            $rev++;

            // insert Article
            DB::Aowow()->query('INSERT INTO ?_articles (`type`, `typeId`, `locale`, `rev`, `editAccess`, `article`) VALUES (?d, ?d, ?d, ?d, ?d, ?)',
                Type::GUIDE, $this->typeId, $this->_post['locale'], $rev, User::$groups & U_GROUP_STAFF ? User::$groups : User::$groups | U_GROUP_BLOGGER, $this->_post['body']);

            // link to Guide
            $guideData = array(
                'category'    => $this->_post['category'],
                'classId'     => $this->_post['classId'],
                'specId'      => $this->_post['specId'],
                'title'       => $this->_post['title'],
                'name'        => $this->_post['name'],
                'description' => $this->_post['description'] ?: Lang::trimTextClean((new Markup($this->_post['body']))->stripTags(), 120),
                'locale'      => $this->_post['locale'],
                'roles'       => User::$groups,
                'status'      => GUIDE_STATUS_DRAFT
            );

            DB::Aowow()->query('UPDATE ?_guides SET ?a WHERE `id` = ?d', $guideData, $this->typeId);

            // new guide -> reload editor
            if ($this->_get['id'] === 0)
                header('Location: ?guide=edit&id='.$this->typeId, true, 302);
            else
                DB::Aowow()->query('INSERT INTO ?_guides_changelog (`id`, `rev`, `date`, `userId`, `msg`) VALUES (?d, ?d, ?d, ?d, ?)', $this->typeId, $rev, time(), User::$id, $this->_post['changelog']);

            if ($this->_post['submit'])
            {
                $status = GUIDE_STATUS_REVIEW;
                if ($curStatus != GUIDE_STATUS_REVIEW)
                {
                    DB::Aowow()->query('UPDATE ?_guides SET `status` = ?d WHERE `id` = ?d', GUIDE_STATUS_REVIEW, $this->typeId);
                    DB::Aowow()->query('INSERT INTO ?_guides_changelog (`id`, `date`, `userId`, `status`) VALUES (?d, ?d, ?d, ?d)', $this->typeId, time(), User::$id, GUIDE_STATUS_REVIEW);
                }
            }
        }

        // init required template vars
        $this->editorFields = array(
            'category'    => $this->_post['category']    ?? $this->subject->getField('category'),
            'title'       => $this->_post['title']       ?? $this->subject->getField('title'),
            'name'        => $this->_post['name']        ?? $this->subject->getField('name'),
            'description' => $this->_post['description'] ?? $this->subject->getField('description'),
            'text'        => $this->_post['body']        ?? $this->subject->getArticle(),
            'status'      => $status                     ?: $this->subject->getField('status'),
            'classId'     => $this->_post['classId']     ?? $this->subject->getField('classId'),
            'specId'      => $this->_post['specId']      ?? $this->subject->getField('specId'),
            'locale'      => $this->_post['locale']      ?? $this->subject->getField('locale'),
            'rev'         => $rev
        );

        $this->extendGlobalData($this->subject->getJSGlobals());
    }

    private function displayGuide() : void
    {
        if (!($this->subject->getField('cuFlags') & GUIDE_CU_NO_QUICKFACTS))
        {
            $qf = [];
            if ($this->subject->getField('cuFlags') & CC_FLAG_STICKY)
                $qf[] = '[span class=guide-sticky]'.Lang::guide('sticky').'[/span]';

            $qf[] = Lang::guide('author').Lang::main('colon').'[url=?user='.$this->subject->getField('author').']'.$this->subject->getField('author').'[/url]';

            if ($this->subject->getField('category') == 1)
            {
                $c = $this->subject->getField('classId');
                $s = $this->subject->getField('specId');
                if ($c > 0)
                {
                    $this->extendGlobalIds(Type::CHR_CLASS, $c);
                    $qf[] = Util::ucFirst(Lang::game('class')).Lang::main('colon').'[class='.$c.']';
                }
                if ($s > -1)
                    $qf[] = Lang::guide('spec').Lang::main('colon').'[icon class="c'.$c.' icontiny" name='.Game::$specIconStrings[$c][$s].']'.Lang::game('classSpecs', $c, $s).'[/icon]';
            }

         // $qf[] = Lang::guide('patch').Lang::main('colon').'3.3.5'; // replace with date
            $qf[] = Lang::guide('added').Lang::main('colon').'[tooltip name=added]'.date('l, G:i:s', $this->subject->getField('date')).'[/tooltip][span class=tip tooltip=added]'.date(Lang::main('dateFmtShort'), $this->subject->getField('date')).'[/span]';

            switch ($this->subject->getField('status'))
            {
                case GUIDE_STATUS_APPROVED:
                    $qf[] = Lang::guide('views').Lang::main('colon').'[n5='.$this->subject->getField('views').']';

                    if (!($this->subject->getField('cuFlags') & GUIDE_CU_NO_RATING))
                    {
                        $this->guideRating = array(
                            $this->subject->getField('rating'),         // avg rating
                            User::canUpvote() && User::canDownvote() ? 'true' : 'false',
                            $this->subject->getField('_self'),          // my rating amt; 0 = no vote
                            $this->typeId                               // guide Id
                        );

                        if ($this->subject->getField('nvotes') < 5)
                            $qf[] = Lang::guide('rating').Lang::main('colon').Lang::guide('noVotes');
                        else
                            $qf[] = Lang::guide('rating').Lang::main('colon').Lang::guide('votes', [round($this->subject->getField('rating'), 1), $this->subject->getField('nvotes')]);
                    }
                    break;
                case GUIDE_STATUS_ARCHIVED:
                    $qf[] = Lang::guide('status', GUIDE_STATUS_ARCHIVED);
                    break;
                }

            $qf = '[ul][li]'.implode('[/li][li]', $qf).'[/li][/ul]';

            if ($this->subject->getField('status') == GUIDE_STATUS_REVIEW && User::isInGroup(U_GROUP_STAFF) && $this->_get['rev'])
            {
                $this->addScript([SC_JS_STRING, '
                    DomContentLoaded.addEvent(function() {
                        let send = function (status)
                        {
                            let message = "";
                            let id = $WH.g_getGets().guide;
                            if (status == 4) // rejected
                            {
                                while (message === "")
                                    message = prompt("Please provide your reasoning.");

                                if (message === null)
                                    return false;
                            }

                            $.ajax({cache: false, url: "?admin=guide", type: "POST",
                                error: function() {
                                    alert("Operation failed.");
                                },
                                success: function(json) {
                                    if (json != 1)
                                        alert("Operation failed.");
                                    else
                                        window.location.href = "?admin=guides";
                                },
                                data: { id: id, status: status, msg: message }
                            })

                            return true;
                        };

                        $WH.ge("btn-accept").onclick = send.bind(null, 3);
                        $WH.ge("btn-reject").onclick = send.bind(null, 4);
                    });
                ']);

                $qf .= '[h3 style="text-align:center"]Admin[/h3]';

                $qf .= '[div style="text-align:center"][url=# id="btn-accept" class=icon-tick]Approve[/url][url=# style="margin-left:20px" id="btn-reject" class=icon-delete]Reject[/url][/div]';
            }
        }

        $this->redButtons[BUTTON_GUIDE_LOG]    = true;
        $this->redButtons[BUTTON_GUIDE_REPORT] = $this->subject->canBeReported();

        $this->infobox     = $qf ?? '';
        $this->author      = $this->subject->getField('author'); // add to g_pageInfo in GenericPage:prepareContent()

        if ($this->subject->userCanView())
            $this->redButtons[BUTTON_GUIDE_EDIT] = User::canWriteGuide() && $this->subject->getField('status') != GUIDE_STATUS_ARCHIVED;

        // the article text itself is added by GenericPage::addArticle()
    }

    private function displayChangelog() : void
    {
        $this->addScript([SC_JS_STRING, '
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
        ']);

        $buff = '<ul>';
        $inp  = fn($rev) => User::isInGroup(U_GROUP_STAFF) ? ($rev !== null ? '<input name="a" value="'.$rev.'" type="radio"/><input name="b" value="'.$rev.'" type="radio"/><b>' : '<b style="margin-left:28px;">') : '';

        $logEntries = DB::Aowow()->select('SELECT a.`displayName` AS `name`, gcl.`date`, gcl.`status`, gcl.`msg`, gcl.`rev` FROM ?_guides_changelog gcl JOIN ?_account a ON a.`id` = gcl.`userId` WHERE gcl.`id` = ?d ORDER BY gcl.`date` DESC', $this->typeId);
        foreach ($logEntries as $log)
        {
            if ($log['status'] != GUIDE_STATUS_NONE)
                $buff .= '<li class="guide-changelog-status-change">'.$inp($log['rev']).Lang::guide('clStatusSet', [Lang::guide('status', $log['status'])]).Lang::main('colon').'</b>'.Util::formatTimeDiff($log['date'])."</li>\n";
            else if ($log['msg'])
                $buff .= '<li>'.$inp($log['rev']).Util::formatTimeDiff($log['date']).Lang::main('colon').'</b>'.$log['msg'].' <i class="q0">'.Lang::main('byUser', [$log['name'], 'style="text-decoration:underline"'])."</i></li>\n";
            else
                $buff .= '<li class="guide-changelog-minor-edit">'.$inp($log['rev']).Util::formatTimeDiff($log['date']).Lang::main('colon').'</b><i>'.Lang::guide('clMinorEdit').'</i> <i class="q0">'.Lang::main('byUser', [$log['name'], 'style="text-decoration:underline"'])."</i></li>\n";
        }

        // append creation
        $buff .= '<li class="guide-changelog-created">'.$inp(0).'<b>'.Lang::guide('clCreated').Lang::main('colon').'</b>'.Util::formatTimeDiff($this->subject->getField('date'))."</li>\n</ul>\n";


        if (User::isInGroup(U_GROUP_STAFF))
            $buff .= '<input type="button" value="Compare" onclick="alert(\'NYI\');"/>';

        $this->name = lang::guide('clTitle', [$this->typeId, $this->subject->getField('title')]);
        $this->extraHTML = $buff;
    }

    private function initNew() : void
    {
        $this->addScript(
            [SC_JS_FILE,    'js/article-description.js'],
            [SC_JS_FILE,    'js/article-editing.js'],
            [SC_JS_FILE,    'js/guide-editing.js'],
            [SC_JS_FILE,    'js/fileuploader.js'],
            [SC_JS_FILE,    'js/toolbar.js'],
            [SC_JS_FILE,    'js/AdjacentPreview.js'],
            [SC_CSS_FILE,   'css/article-editing.css'],
            [SC_CSS_FILE,   'css/fileuploader.css'],
            [SC_CSS_FILE,   'css/guide-edit.css'],
            [SC_CSS_FILE,   'css/AdjacentPreview.css'],

            [SC_CSS_STRING, '#upload-result input[type=text] { padding: 0px 2px; font-size: 12px; }'],
            [SC_CSS_STRING, '#upload-result > span { display:block; height: 22px; }'],
            [SC_CSS_STRING, '#upload-result { display: inline-block; text-align:right; }'],
            [SC_CSS_STRING, '#upload-progress { display: inline-block; margin-right:8px; }']
        );

        $this->articleUrl = 'new';
        $this->tpl        = 'guide-edit';
        $this->name       = Lang::guide('newTitle');

        Lang::sort('guide', 'category');

        $this->typeId = 0;                          // signals 'edit' to create new guide
    }

    private function initEdit() : bool
    {
        $this->addScript(
            [SC_JS_FILE,   'js/article-description.js'],
            [SC_JS_FILE,   'js/article-editing.js'],
            [SC_JS_FILE,   'js/guide-editing.js'],
            [SC_JS_FILE,   'js/fileuploader.js'],
            [SC_JS_FILE,   'js/toolbar.js'],
            [SC_JS_FILE,   'js/AdjacentPreview.js'],
            [SC_CSS_FILE,  'css/article-editing.css'],
            [SC_CSS_FILE,  'css/fileuploader.css'],
            [SC_CSS_FILE,  'css/guide-edit.css'],
            [SC_CSS_FILE,  'css/AdjacentPreview.css'],

            [SC_CSS_STRING, '#upload-result input[type=text] { padding: 0px 2px; font-size: 12px; }'],
            [SC_CSS_STRING, '#upload-result > span { display:block; height: 22px; }'],
            [SC_CSS_STRING, '#upload-result { display: inline-block; text-align:right; }'],
            [SC_CSS_STRING, '#upload-progress { display: inline-block; margin-right:8px; }']
        );

        $this->articleUrl = 'edit';
        $this->tpl        = 'guide-edit';
        $this->name       = Lang::guide('editTitle');
        $this->save       = $this->_post['save'] || $this->_post['submit'];

        // reject inconsistent guide data
        if ($this->save)
        {
            // req: set data
            if (!$this->_post['title'] || !$this->_post['name'] || !$this->_post['body'] || $this->_post['locale'] === null)
                return false;

            // req: valid data
            if (!in_array($this->_post['category'], $this->validCats) || !(CFG_LOCALES & (1 << $this->_post['locale'])))
                return false;

            // sanitize: spec / class
            if ($this->_post['category'] == 1)              // Classes
            {
                if ($this->_post['classId'] && !((1 << $this->_post['classId']) & CLASS_MASK_ALL))
                    $this->_post['classId'] = 0;

                if (!in_array($this->_post['specId'], [-1, 0, 1, 2]))
                    $this->_post['specId'] = -1;
                if ($this->_post['specId'] > -1 && !$this->_post['classId'])
                    $this->_post['specId'] = -1;
            }
            else
            {
                $this->_post['classId'] = 0;
                $this->_post['specId']  = -1;
            }
        }

        if ($this->_get['id'])                              // edit existing guide
        {
            $this->typeId = $this->_get['id'];              // just to display sensible not-found msg
            if ($id = DB::Aowow()->selectCell('SELECT `id` FROM ?_guides WHERE `id` = ?d AND `status` <> ?d {AND `userId`  = ?d}', $this->typeId, GUIDE_STATUS_ARCHIVED, User::isInGroup(U_GROUP_STAFF) ? DBSIMPLE_SKIP : User::$id))
                $this->typeId = intVal($id);
        }
        else if ($this->_get['id'] === 0)                 // create new guide and load in editor
            $this->typeId = DB::Aowow()->query('INSERT INTO ?_guides (`userId`, `date`, `status`) VALUES (?d, ?d, ?d)', User::$id, time(), GUIDE_STATUS_DRAFT);

        return $this->typeId > 0;
    }

    protected function editorFields(string $field, bool $asInt = false) : string|int
    {
        return $this->editorFields[$field] ?? ($asInt ? 0 : '');
    }

    protected function generateTooltip()
    {
        $power = new StdClass();
        if (!$this->subject->error)
        {
            $power->{'name_'.User::$localeString}    = strip_tags($this->name);
            $power->{'tooltip_'.User::$localeString} = $this->subject->renderTooltip();
        }

        return sprintf($this->powerTpl, Util::toJSON($this->articleUrl ?: $this->typeId), User::$localeId, Util::toJSON($power, JSON_AOWOW_POWER));
    }

    protected function generatePath() : void
    {
        if ($x = $this->subject?->getField('category'))
            $this->path[] = $x;
    }

    protected function generateTitle() : void
    {
        if ($this->show == self::SHOW_EDITOR)
            array_unshift($this->title, Lang::guide('editTitle').Lang::main('colon').$this->subject->getField('title'), Lang::game('guides'));
        if ($this->show == self::SHOW_NEW)
            array_unshift($this->title, Lang::guide('newTitle'), Lang::game('guides'));
        else
            array_unshift($this->title, $this->subject->getField('title'), Lang::game('guides'));
    }

    protected function postCache() : void
    {
        // increment views of published guide; ignore caching
        if ($this->subject?->getField('status') == GUIDE_STATUS_APPROVED)
            DB::Aowow()->query('UPDATE ?_guides SET `views` = `views` + 1 WHERE `id` = ?d', $this->typeId);
    }

    protected static function checkDescription(string $str) : string
    {
        // run checkTextBlob and also replace \n => \s and \s+ => \s
        $str = preg_replace(parent::$PATTERN_TEXT_BLOB, '', $str);

        $str = strtr($str, ["\n" => ' ', "\r" => ' ']);

        return preg_replace('/\s+/', ' ', trim($str));
    }
}

?>
