<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GuideBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache;

    protected  int    $cacheType   = CACHE_TYPE_DETAIL_PAGE;

    protected  string $template    = 'detail-page-generic';
    protected  string $pageName    = 'guide';
    protected ?int    $activeTab   = parent::TAB_GUIDES;
    protected  array  $breadcrumb  = [6];

    protected  array  $expectedGET = array(
        'id'  => ['filter' => FILTER_VALIDATE_INT],
        'rev' => ['filter' => FILTER_VALIDATE_INT]
    );

    public  int   $type          = Type::GUIDE;
    public  int   $typeId        = 0;
    public  int   $guideStatus   = 0;
    public  array $guideRating   = [];
    public ?int   $guideRevision = null;

    private GuideList $subject;

    public function __construct(string $nameOrId)
    {
        parent::__construct($nameOrId);

        /**********************/
        /* get mode + guideId */
        /**********************/

        if (Util::checkNumeric($nameOrId, NUM_CAST_INT))
            $this->typeId = $nameOrId;
        else if (preg_match(GuideMgr::VALID_URL, $nameOrId))
        {
            if ($id = DB::Aowow()->selectCell('SELECT `id` FROM ?_guides WHERE `url` = ?', Util::lower($nameOrId)))
            {
                $this->typeId     = intVal($id);
                $this->articleUrl = Util::lower($nameOrId);
            }
        }

        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new GuideList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('guide'), Lang::guide('notFound'));

        if (!$this->subject->canBeViewed() && !$this->subject->userCanView())
            $this->forward('?guides='.$this->subject->getField('category'));

        $this->guideStatus = $this->subject->getField('status');
        if ($this->guideStatus != GuideMgr::STATUS_APPROVED && $this->guideStatus != GuideMgr::STATUS_ARCHIVED)
        {
            $this->cacheType  = CACHE_TYPE_NONE;
            $this->contribute = CONTRIBUTE_NONE;
        }

        // owner or staff and manual rev passed
        if ($this->subject->userCanView() && $this->_get['rev'])
            $this->guideRevision = $this->_get['rev'];
        // has publicly viewable version
        else if ($this->subject->canBeViewed())
            $this->guideRevision = $this->subject->getField('rev');

        $this->h1 = $this->subject->getField('name');

        $this->gPageInfo += array(
            'name'   => $this->h1,
            'author' => $this->subject->getField('author')
        );


        /*************/
        /* Menu Path */
        /*************/

        if ($x = $this->subject?->getField('category'))
            $this->breadcrumb[] = $x;


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->subject->getField('title'), Lang::game('guides'));


        /***********/
        /* Infobox */
        /***********/

        if (!($this->subject->getField('cuFlags') & GUIDE_CU_NO_QUICKFACTS))
            $this->generateInfobox();

        // needs post-cache updating
        if (!($this->subject->getField('cuFlags') & GUIDE_CU_NO_RATING))
            $this->guideRating = array(
                $this->subject->getField('rating'),         // avg rating
                User::canUpvote() && User::canDownvote() ? 'true' : 'false',
                $this->subject->getField('_self'),          // my rating amt; 0 = no vote
                $this->typeId                               // guide Id
            );


        /****************/
        /* Main Content */
        /****************/

        if ($this->subject->userCanView())
            $this->redButtons[BUTTON_GUIDE_EDIT] = User::canWriteGuide() && $this->guideStatus != GuideMgr::STATUS_ARCHIVED;

        $this->redButtons[BUTTON_GUIDE_LOG]    = true;
        $this->redButtons[BUTTON_GUIDE_REPORT] = $this->subject->canBeReported();

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], __forceTabs: true);

        // the article text itself is added by TemplateResponse::addArticle()
        parent::generate();

        $this->result->registerDisplayHook('infobox', [self::class, 'infoboxHook']);
        if ($this->guideRating)
            $this->result->registerDisplayHook('guideRating', [self::class, 'starsHook']);
    }

    private function generateInfobox() : void
    {
        $infobox = [];

        if ($this->subject->getField('cuFlags') & CC_FLAG_STICKY)
            $infobox[] = '[span class=guide-sticky]'.Lang::guide('sticky').'[/span]';

        $infobox[] = Lang::guide('author').'[url=?user='.$this->subject->getField('author').']'.$this->subject->getField('author').'[/url]';

        if ($this->subject->getField('category') == 1)
        {
            $c = $this->subject->getField('classId');
            $s = $this->subject->getField('specId');
            if ($c > 0)
            {
                $this->extendGlobalIds(Type::CHR_CLASS, $c);
                $infobox[] = Util::ucFirst(Lang::game('class')).Lang::main('colon').'[class='.$c.']';
            }
            if ($s > -1)
                $infobox[] = Lang::guide('spec').'[icon class="c'.$c.' icontiny" name='.Game::$specIconStrings[$c][$s].']'.Lang::game('classSpecs', $c, $s).'[/icon]';
        }

        // $infobox[] = Lang::guide('patch').Lang::main('colon').'3.3.5'; // replace with date
        $infobox[] = Lang::guide('added').'[tooltip name=added]'.date('l, G:i:s', $this->subject->getField('date')).'[/tooltip][span class=tip tooltip=added]'.date(Lang::main('dateFmtShort'), $this->subject->getField('date')).'[/span]';

        if ($this->guideStatus == GuideMgr::STATUS_ARCHIVED)
            $infobox[] = Lang::guide('status', GuideMgr::STATUS_ARCHIVED);

        $this->infobox = new InfoboxMarkup($infobox, ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0');

        if ($this->guideStatus == GuideMgr::STATUS_REVIEW && User::isInGroup(U_GROUP_STAFF) && $this->_get['rev'])
        {
            $this->addScript([SC_JS_STRING, <<<JS

                    $(document).ready(function() {
                        let send = function (status)
                        {
                            let message = "";
                            let id = \$WH.g_getGets().guide;
                            if (status == 4) // rejected
                            {
                                while (message === "")
                                    message = prompt("Please provide your reasoning.");

                                if (message === null)
                                    return false;
                            }

                            $.ajax({cache: false, url: "?admin=guide", type: "POST",
                                error: function() { alert("Operation failed."); },
                                success: function(json)
                                {
                                    if (json)
                                        alert("Operation failed.");
                                    else
                                        window.location.href = "?admin=guides";
                                },
                                data: { id: id, status: status, msg: message }
                            });

                            return true;
                        };

                        \$WH.ge("btn-accept").onclick = send.bind(null, 3);
                        \$WH.ge("btn-reject").onclick = send.bind(null, 4);
                    });

            JS]);

            $this->infobox->append('[h3 style="text-align:center"]Admin[/h3]');
            $this->infobox->append('[div style="text-align:center"][url=# id="btn-accept" class=icon-tick]Approve[/url][url=# style="margin-left:20px" id="btn-reject" class=icon-delete]Reject[/url][/div]');
        }
    }

    public static function infoboxHook(Template\PageTemplate &$pt, ?InfoboxMarkup &$infobox) : void
    {
        if ($pt->guideStatus != GuideMgr::STATUS_APPROVED)
            return;

        // increment and display views
        DB::Aowow()->query('UPDATE ?_guides SET `views` = `views` + 1 WHERE `id` = ?d', $pt->typeId);

        $nViews = DB::Aowow()->selectCell('SELECT `views` FROM ?_guides WHERE `id` = ?d', $pt->typeId);

        $infobox->addItem(Lang::guide('views').'[n5='.$nViews.']');

        // should we have a rating item in the lv?
        if (!$pt->guideRating)
            return;

        $rating = GuideMgr::getRatings([$pt->typeId]);
        if ($rating[$pt->typeId]['nvotes'] < 5)
            $infobox->addItem(Lang::guide('rating').Lang::guide('noVotes'));
        else
            $infobox->addItem(Lang::guide('rating').Lang::guide('votes', [round($rating[$pt->typeId]['rating'], 1), $rating[$pt->typeId]['nvotes']]));
    }

    public static function starsHook(Template\PageTemplate &$pt, ?array &$guideRating) : void
    {
        if ($pt->guideStatus != GuideMgr::STATUS_APPROVED)
            return;

        $rating = GuideMgr::getRatings([$pt->typeId]);
        $guideRating = array(
            $rating[$pt->typeId]['rating'],
            User::canUpvote() && User::canDownvote() ? 'true' : 'false',
            $rating[$pt->typeId]['_self'] ?? 0,
            $pt->typeId
        );
    }
}

?>
