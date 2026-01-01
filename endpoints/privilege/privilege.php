<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class PrivilegeBaseResponse extends TemplateResponse
{
    protected  string $template   = 'privilege';
    protected  string $pageName   = 'privilege';
    protected ?int    $activeTab  = parent::TAB_COMMUNITY;
    protected  array  $breadcrumb = [3, 10];

    protected  array  $validCats  = [1, 2, /* 4, */ 5, 9, 10, 11, 12, 13, 14, 15, 16, 17];

    public string $privReqPoints = '';

    private int   $repVal   = 0;
    private array $req2priv = array(
         1 => 'REP_REQ_COMMENT',                            // write comments
         2 => 'REP_REQ_EXT_LINKS',                          // post external links
    //   4 => 'REP_REQ_NO_CAPTCHA',                         // NYI no captcha
         5 => 'REP_REQ_SUPERVOTE',                          // votes count for more
         9 => 'REP_REQ_VOTEMORE_BASE',                      // more votes per day
        10 => 'REP_REQ_UPVOTE',                             // can upvote
        11 => 'REP_REQ_DOWNVOTE',                           // can downvote
        12 => 'REP_REQ_REPLY',                              // can reply
        13 => 'REP_REQ_BORDER_UNCOMMON',                    // uncommon avatar border
        14 => 'REP_REQ_BORDER_RARE',                        // rare avatar border
        15 => 'REP_REQ_BORDER_EPIC',                        // epic avatar border
        16 => 'REP_REQ_BORDER_LEGENDARY',                   // legendary avatar border
        17 => 'REP_REQ_PREMIUM'                             // premium status
    );

    public function __construct(string $rawParam)
    {
        $this->getCategoryFromUrl($rawParam);

        parent::__construct($rawParam);

        if (!$rawParam)
            $this->generateError();

        // apply actual values
        $this->repVal = Cfg::get($this->req2priv[$rawParam]);
    }

    protected function generate() : void
    {
        $this->h1 = Lang::privileges('_privileges', $this->category[0]);

        array_unshift($this->title, $this->h1);

        $this->breadcrumb[] = $this->category[0];

        $this->privReqPoints = Lang::privileges('reqPoints', [Lang::nf($this->repVal)]);

        parent::generate();

        $this->result->registerDisplayHook('article', [self::class, 'articleHook']);
    }

    public static function articleHook(Template\PageTemplate &$pt, Markup &$article) : void
    {
        $article->apply(Cfg::applyToString(...));
    }
}

?>
