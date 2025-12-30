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
        $now = new DateTime();

        foreach (CommunityContent::getCommentPreviews(['comments' => 1, 'replies' => 1], dateFmt: false, resultLimit: 100) as $comment)
        {
            if (empty($comment['commentid']))
                $url = Cfg::get('HOST_URL').'/?go-to-comment&amp;id='.$comment['id'];
            else
                $url = Cfg::get('HOST_URL').'/?go-to-reply&amp;id='.$comment['id'];

            // todo (low): preview should be html-formated
            $this->feedData[] = array(
                'title'       => [true,  [], Lang::typeName($comment['type']).Lang::main('colon').htmlentities($comment['subject'])],
                'link'        => [false, [], $url],
                'description' => [true,  [], htmlentities($comment['preview'])."<br /><br />".Lang::main('byUser', [$comment['user'], '']) . $now->formatDate($comment['date'], true)],
                'pubDate'     => [false, [], date(DATE_RSS, $comment['date'])],
                'guid'        => [false, [], $url]
             // 'domain'      => [false, [], null]
            );
        }

        $this->result = $this->generateRSS(Lang::main('utilities', 2), 'latest-comments');
    }
}

?>
