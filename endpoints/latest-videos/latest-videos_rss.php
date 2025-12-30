<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class LatestvideosRssResponse extends TextResponse
{
    use TrRss;

    protected string $contentType = MIME_TYPE_RSS;

    protected function generate() : void
    {
        $now = new DateTime();

        foreach (CommunityContent::getvideos(dateFmt: false, resultLimit: 100) as $video)
        {
            $desc = '<a href="'.Cfg::get('HOST_URL').'/?'.Type::getFileString($video['type']).'='.$video['typeId'].'#videos:id='.$video['id'].'"><img src="//i3.ytimg.com/vi/'.$video['videoId'].'/default.jpg" alt="" /></a>';
            if ($video['caption'])
                $desc .= '<br />'.$video['caption'];
            $desc .= "<br /><br />".Lang::main('byUser', [$video['user'], '']) . $now->formatDate($video['date'], true);

            // is enclosure/length .. is this even relevant..?
            $this->feedData[] = array(
                'title'       => [true,  [], Lang::typeName($video['type']).Lang::main('colon').htmlentities($video['subject'])],
                'link'        => [false, [], Cfg::get('HOST_URL').'/?'.Type::getFileString($video['type']).'='.$video['typeId'].'#videos:id='.$video['id']],
                'description' => [true,  [], $desc],
                'pubDate'     => [false, [], date(DATE_RSS, $video['date'])],
                'enclosure'   => [false, ['url' => '//i3.ytimg.com/vi/'.$video['videoId'].'/default.jpg', 'length' => 12345, 'type' => 'image/jpeg'], null],
                'guid'        => [false, [], Cfg::get('HOST_URL').'/?'.Type::getFileString($video['type']).'='.$video['typeId'].'#videos:id='.$video['id']],
             // 'domain'      => [false, [], live|ptr]
            );
        }

        $this->result = $this->generateRSS(Lang::main('utilities', 11), 'latest-videos');
    }
}

?>
