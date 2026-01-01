<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');



/* ugh .. badly documented standards...
    so, Opensearch 1.1 _mentions_ support for results returned as json, but does not describe a format
    GPT-4.1 described it as
    [
        'searchTerm',
        ['text match 1', .., 'text match N'],
        ['description 1', .., ' description N'],
        ['url 1', .., 'url N']
    ]
    but was unable to provide sources (or rather the sources it linked 404ed or where unhelpful)
    though https://en.wikipedia.org/w/api.php?action=opensearch&namespace=0&search=term supports this claim

    Firefox today only evaluates index 1
    Edge/Chrome do not support suggestions from manual installs and refuse auto-discovery (would require policy or plugin)
     - for pre-installed search engines (like wikipedia) Edge/Chrome also only evaluates index 1

    - original useage by WH
    => suggestions when typing into searchboxes
    array:[
        str,        // search
        str[10],    // found
        [],         // unused - description for found result?
        str[10],    // url to found result
        [],         // unused
        [],         // unused
        [],         // unused
        str[10][4]  // type, typeId, param1 (4:quality, 3,6,9,10,17:icon, 5,11:faction), param2 (3:quality, 6:rank)
    ]

    WH walked away from this hybrid approach and has separate endpoints for internal search suggestions and opensearch, with the latter only providing found text (index 1)

    we move the appendix of ' (TypeName)' on found text to descriptions as it fucks over Firefox users when they apply the suggestion
*/


class SearchOpenResponse extends TextResponse implements ICache
{
    use TrCache, TrSearch;

    private const /* int */ SEARCH_MODS_OPEN =
        1 << Search::MOD_CLASS    | 1 << Search::MOD_RACE     | 1 << Search::MOD_TITLE   | 1 << Search::MOD_WORLDEVENT  |
        1 << Search::MOD_CURRENCY | 1 << Search::MOD_ITEMSET  | 1 << Search::MOD_ITEM    | 1 << Search::MOD_ABILITY     |
        1 << Search::MOD_TALENT   | 1 << Search::MOD_CREATURE | 1 << Search::MOD_QUEST   | 1 << Search::MOD_ACHIEVEMENT |
        1 << Search::MOD_ZONE     | 1 << Search::MOD_OBJECT   | 1 << Search::MOD_FACTION | 1 << Search::MOD_SKILL       |
        1 << Search::MOD_PET;

    private int $maxResults = Search::SUGGESTIONS_MAX_RESULTS;

    protected string $contentType = MIME_TYPE_OPENSEARCH;
    protected int    $cacheType   = CACHE_TYPE_SEARCH;

    protected array  $expectedGET = array(
        'search' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTextLine']]
    );

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);                    // just to set g_user and g_locale

        $this->query = $this->_get['search'];               // technically rawParam, but prepared

        $this->searchMask = Search::TYPE_OPEN | self::SEARCH_MODS_OPEN;

        $this->searchObj = new Search($this->query, $this->searchMask, maxResults: $this->maxResults);
    }

    protected function generate() : void
    {
        // this one is funny: we want 10 results, ideally equally distributed over each type
        $foundTotal = 0;
        $result     = array(                                // 0:query, 1:[names], 3:[links]; 7:[extraInfo]
            $this->query,
            [], [], [], [], [], [], []
        );

        // invalid conditions: not enough characters to search OR no types to search
        if (!$this->searchObj->canPerform())
            $this->generate404($this->query);

        foreach ($this->searchObj->perform() as [, , $nMatches, , , ])
            $foundTotal += $nMatches;

        foreach ($this->searchObj->perform() as [$data, $type, $nMatches, $param1, $param2, $desc])
        {
            $max = max(1, intVal($this->maxResults * $nMatches / $foundTotal));

            $i = 0;
            foreach ($data as $id => $name)
            {
                if (++$i > $max)
                    break;

                if (count($result[1]) >= $this->maxResults)
                    break 2;

                $result[1][] = $name;                       // originally - $name . ' ('.$desc.')'
                $result[2][] = $desc;                       // .. and here empty
                $result[3][] = Cfg::get('HOST_URL').'/?'.Type::getFileString($type).'='.$id;

                $extra = [$type, $id];                      // type, typeId
                if (isset($param1[$id]))
                    $extra[] = $param1[$id];                // param1
                if (isset($param2[$id]))
                    $extra[] = $param2[$id];                // param2

                $result[7][] = $extra;
            }
        }

        $this->result = Util::toJSON($result);
    }

    public function generate404(?string $search = null) : never
    {
        parent::generate404(Util::toJSON([$search, [], [], [], [], [], [], []]));
    }
}

?>
