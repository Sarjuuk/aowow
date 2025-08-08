<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class LatestcommentsRssResponse extends TextResponse
{
    use TrRss;

    protected string $contentType = MIME_TYPE_RSS;

    protected function generate() : void
    {
        foreach (CommunityContent::getCommentPreviews(dateFmt: false) as $comment)
        {
            // todo (low): preview should be html-formated
            $this->feedData[] = array(
                'title'       => [true,  [], Lang::typeName($comment['type']).Lang::main('colon').htmlentities($comment['subject'])],
                'link'        => [false, [], Cfg::get('HOST_URL').'/?go-to-comment&amp;id='.$comment['id']],
                'description' => [true,  [], htmlentities($comment['preview'])."<br /><br />".Lang::main('byUser', [$comment['user'], '']) . Util::formatTimeDiff($comment['date'])],
                'pubDate'     => [false, [], date(DATE_RSS, $comment['date'])],
                'guid'        => [false, [], Cfg::get('HOST_URL').'/?go-to-comment&amp;id='.$comment['id']]
             // 'domain'      => [false, [], null]
            );
        }

        $this->result = $this->generateRSS(Lang::main('utilities', 2), 'latest-comments');
    }
}

?>
