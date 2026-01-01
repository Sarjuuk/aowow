<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class MostcommentsRssResponse extends TextResponse
{
    use TrRss;

    protected string $contentType = MIME_TYPE_RSS;

    private   array  $validCats   = [1, 7, 30];

    public function __construct($rawParam)
    {
        parent::__construct($rawParam);

        if ($this->params && !in_array($this->params[0], $this->validCats))
            $this->forward('?most-comments=1&rss');
    }

    protected function generate() : void
    {
        foreach (Type::getClassesFor() as $type => $classStr)
        {
            $comments = DB::Aowow()->selectCol(
               'SELECT   `typeId` AS ARRAY_KEY, COUNT(1) FROM ?_comments
                WHERE    `replyTo` = 0 AND (`flags` & ?d) = 0 AND `type`= ?d AND `date` > (UNIX_TIMESTAMP() - ?d)
                GROUP BY `type`, `typeId`
                LIMIT    100',
                CC_FLAG_DELETED,
                $type,
                ($this->category[0] ?? 1) * DAY
            );
            if (!$comments)
                continue;

            $typeClass = new $classStr(array(['id', array_keys($comments)]));
            if ($typeClass->error)
                continue;

            $data = $typeClass->getListviewData();

            foreach ($data as $typeId => &$d)
            {
                $this->feedData[] = array(
                    'title'       => [true,  [], htmlentities(Type::getFileString($type) == 'item' ? mb_substr($d['name'], 1) : $d['name'])],
                    'type'        => [false, [], Type::getFileString($type)],
                    'link'        => [false, [], Cfg::get('HOST_URL').'/?'.Type::getFileString($type).'='.$d['id']],
                    'ncomments'   => [false, [], $comments[$typeId]]
                );
            }

        }

        $this->result = $this->generateRSS(Lang::main('utilities', 12), 'most-comments' . ($this->params ? '='.$this->params[0] : ''));
    }
}

?>
