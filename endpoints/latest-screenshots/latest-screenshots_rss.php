<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class LatestscreenshotsRssResponse extends TextResponse
{
    use TrRss;

    protected string $contentType = MIME_TYPE_RSS;

    protected function generate() : void
    {
        $now = new DateTime();

        foreach (CommunityContent::getScreenshots(dateFmt: false, resultLimit: 100) as $screenshot)
        {
            $desc = '<a href="'.Cfg::get('HOST_URL').'/?'.Type::getFileString($screenshot['type']).'='.$screenshot['typeId'].'#screenshots:id='.$screenshot['id'].'"><img src="'.Cfg::get('STATIC_URL').'/uploads/screenshots/thumb/'.$screenshot['id'].'.jpg" alt="" /></a>';
            if ($screenshot['caption'])
                $desc .= '<br />'.$screenshot['caption'];
            $desc .= "<br /><br />".Lang::main('byUser', [$screenshot['user'], '']) . $now->formatDate($screenshot['date'], true);

            // enclosure/length => filesize('static/uploads/screenshots/thumb/'.$screenshot['id'].'.jpg') .. always set to this placeholder value though
            $this->feedData[] = array(
                'title'       => [true,  [], Lang::typeName($screenshot['type']).Lang::main('colon').htmlentities($screenshot['subject'])],
                'link'        => [false, [], Cfg::get('HOST_URL').'/?'.Type::getFileString($screenshot['type']).'='.$screenshot['typeId'].'#screenshots:id='.$screenshot['id']],
                'description' => [true,  [], $desc],
                'pubDate'     => [false, [], date(DATE_RSS, $screenshot['date'])],
                'enclosure'   => [false, ['url' => Cfg::get('STATIC_URL').'/uploads/screenshots/thumb/'.$screenshot['id'].'.jpg', 'length' => 12345, 'type' => 'image/jpeg'], null],
                'guid'        => [false, [], Cfg::get('HOST_URL').'/?'.Type::getFileString($screenshot['type']).'='.$screenshot['typeId'].'#screenshots:id='.$screenshot['id']],
             // 'domain'      => [false, [], live|ptr]
            );
        }

        $this->result = $this->generateRSS(Lang::main('utilities', 3), 'latest-screenshots');
    }
}

?>
