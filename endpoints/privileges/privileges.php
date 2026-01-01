<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class PrivilegesBaseResponse extends TemplateResponse
{
    protected  string $template   = 'privileges';
    protected  string $pageName   = 'privileges';
    protected ?int    $activeTab  = parent::TAB_COMMUNITY;
    protected  array  $breadcrumb = [3, 10, 0];

    public array $privileges = [];

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
        parent::__construct($rawParam);

        if ($rawParam)
            $this->generateError();

        // apply actual values and order by requirement ASC
        foreach ($this->req2priv as &$var)
            $var = Cfg::get($var);

        asort($this->req2priv);
    }

    protected function generate() : void
    {
        $this->h1 = Lang::main('moreTitles', $this->pageName);

        array_unshift($this->title, $this->h1);

        foreach (array_filter($this->req2priv) as $id => $val)
            $this->privileges[$id] = array(
                User::getReputation() >= $val,
                Lang::privileges('_privileges', $id),
                $val
            );

        parent::generate();
    }
}

?>
