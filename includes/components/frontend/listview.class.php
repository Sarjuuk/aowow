<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class Listview implements \JsonSerializable
{
    public const /* int */ MODE_DEFAULT  = 0;
    public const /* int */ MODE_CHECKBOX = 1;
    public const /* int */ MODE_DIV      = 2;
    public const /* int */ MODE_TILED    = 3;
    public const /* int */ MODE_CALENDAR = 4;
    public const /* int */ MODE_FLEXGRID = 5;

    public const /* int */ DEFAULT_SIZE  = 300;

    private const TEMPLATES = array(
        'achievement'       => ['template' => 'achievement',       'id' => 'achievements',    'name' => '$LANG.tab_achievements'  ],
        'areatrigger'       => ['template' => 'areatrigger',       'id' => 'areatrigger',                                         ],
        'calendar'          => ['template' => 'holidaycal',        'id' => 'calendar',        'name' => '$LANG.tab_calendar'      ],
        'class'             => ['template' => 'classs',            'id' => 'classes',         'name' => '$LANG.tab_classes'       ],
        'commentpreview'    => ['template' => 'commentpreview',    'id' => 'comments',        'name' => '$LANG.tab_comments'      ],
        'npc'               => ['template' => 'npc',               'id' => 'npcs',            'name' => '$LANG.tab_npcs'          ],
        'currency'          => ['template' => 'currency',          'id' => 'currencies',      'name' => '$LANG.tab_currencies'    ],
        'emote'             => ['template' => 'emote',             'id' => 'emotes',                                              ],
        'enchantment'       => ['template' => 'enchantment',       'id' => 'enchantments',                                        ],
        'event'             => ['template' => 'holiday',           'id' => 'holidays',        'name' => '$LANG.tab_holidays'      ],
        'faction'           => ['template' => 'faction',           'id' => 'factions',        'name' => '$LANG.tab_factions'      ],
        'genericmodel'      => ['template' => 'genericmodel',      'id' => 'same-model-as',   'name' => '$LANG.tab_samemodelas'   ],
        'icongallery'       => ['template' => 'icongallery',       'id' => 'icons',                                               ],
        'item'              => ['template' => 'item',              'id' => 'items',           'name' => '$LANG.tab_items'         ],
        'itemset'           => ['template' => 'itemset',           'id' => 'itemsets',        'name' => '$LANG.tab_itemsets'      ],
        'mail'              => ['template' => 'mail',              'id' => 'mails',                                               ],
        'model'             => ['template' => 'model',             'id' => 'gallery',         'name' => '$LANG.tab_gallery'       ],
        'object'            => ['template' => 'object',            'id' => 'objects',         'name' => '$LANG.tab_objects'       ],
        'pet'               => ['template' => 'pet',               'id' => 'hunter-pets',     'name' => '$LANG.tab_pets'          ],
        'profile'           => ['template' => 'profile',           'id' => 'profiles',        'name' => '$LANG.tab_profiles'      ],
        'quest'             => ['template' => 'quest',             'id' => 'quests',          'name' => '$LANG.tab_quests'        ],
        'race'              => ['template' => 'race',              'id' => 'races',           'name' => '$LANG.tab_races'         ],
        'replypreview'      => ['template' => 'replypreview',      'id' => 'comment-replies', 'name' => '$LANG.tab_commentreplies'],
        'reputationhistory' => ['template' => 'reputationhistory', 'id' => 'reputation',      'name' => '$LANG.tab_reputation'    ],
        'screenshot'        => ['template' => 'screenshot',        'id' => 'screenshots',     'name' => '$LANG.tab_screenshots'   ],
        'skill'             => ['template' => 'skill',             'id' => 'skills',          'name' => '$LANG.tab_skills'        ],
        'sound'             => ['template' => 'sound',             'id' => 'sounds',          'name' => '$LANG.types[19][2]'      ],
        'spell'             => ['template' => 'spell',             'id' => 'spells',          'name' => '$LANG.tab_spells'        ],
        'title'             => ['template' => 'title',             'id' => 'titles',          'name' => '$LANG.tab_titles'        ],
        'topusers'          => ['template' => 'topusers',          'id' => 'topusers',        'name' => '$LANG.topusers'          ],
        'video'             => ['template' => 'video',             'id' => 'videos',          'name' => '$LANG.tab_videos'        ],
        'zone'              => ['template' => 'zone',              'id' => 'zones',           'name' => '$LANG.tab_zones'         ],
        'guide'             => ['template' => 'guide',             'id' => 'guides',                                              ]
    );

    private  string $id       = '';
    private ?string $name     = null;
    private ?array  $data     = null;                       // js:array of object <RowDefinitions>
    private ?string $tabs     = null;                       // js:Object; instance of "Tabs"
    private ?string $parent   = 'lv-generic';               // HTMLNode.id; can be null but is pretty much always 'lv-generic'
    private ?string $template = null;
    private ?int    $mode     = null;                       // js:int; defaults to MODE_DEFAULT
    private ?string $note     = null;                       // text in top band

    private ?int $poundable   = null;                       // 0 (no); 1 (always); 2 (yes, w/o sorting); defaults to 1
    private ?int $searchable  = null;                       // js:bool; defaults to FALSE
    private ?int $filtrable   = null;                       // js:bool; defaults to FALSE
    private ?int $sortable    = null;                       // js:bool; defaults to FALSE
    private ?int $searchDelay = null;                       // in ms; defalts to 333
    private ?int $clickable   = null;                       // js:bool; defaults to TRUE
    private ?int $hideBands   = null;                       // js:int; 1:top, 2:bottom, 3:both;
    private ?int $hideNav     = null;                       // js:int; 1:top, 2:bottom, 3:both;
    private ?int $hideHeader  = null;                       // js:bool
    private ?int $hideCount   = null;                       // js:bool
    private ?int $debug       = null;                       // js:bool
    private ?int $_truncated  = null;                       // js:bool; adds predefined note to top band, because there was too much data to display
    private ?int $_errors     = null;                       // js:bool; adds predefined note to top band, because there was an error
    private ?int $_petTalents = null;                       // js:bool; applies modifier for talent levels

    private ?int    $nItemsPerPage   = null;                // js:int; defaults to 50
    private ?int    $_totalCount     = null;                // js:int; used by loot and comments
    private ?array  $clip            = null;                // js:array of int {w:<width>, h:<height>}
    private ?string $customPound     = null;
    private ?string $genericlinktype = null;                // sometimes set when expecting to display model
    private ?array  $_upgradeIds     = null;                // js:array of int (itemIds)

    private null|array|string $extraCols   = null;          // js:callable or js:array of object <ColumnDefinition>
    private null|array|string $visibleCols = null;          // js:callable or js:array of string <colIds>
    private null|array|string $hiddenCols  = null;          // js:callable or js:array of string <colIds>
    private null|array|string $sort        = null;          // js:callable or js:array of colIndizes

    private ?string $onBeforeCreate   = null;               // js:callable
    private ?string $onAfterCreate    = null;               // js:callable
    private ?string $onNoData         = null;               // js:callable
    private ?string $computeDataFunc  = null;               // js:callable
    private ?string $onSearchSubmit   = null;               // js:callable
    private ?string $createNote       = null;               // js:callable
    private ?string $createCbControls = null;               // js:callable
    private ?string $customFilter     = null;               // js:callable
    private ?string $getItemLink      = null;               // js:callable
    private ?array  $sortOptions      = null;               // js:array of object {id:<colId>, name:<name>, hidden:<bool>, type:"text", sortFunc:<callable>}

    private string $__addIn = '';

    public function __construct(array $opts, string $template = '', string $addIn = '')
    {
        if ($template && isset(self::TEMPLATES[$template]))
            foreach (self::TEMPLATES[$template] as $k => $v)
                $this->$k = $v;

        foreach ($opts as $k => $v)
        {
            if (property_exists($this, $k))
            {
                // reindex arrays to force json_encode to treat them as arrays
                if (is_array($v)) // in_array($k, ['data', 'extraCols', 'visibleCols', 'hiddenCols', 'sort', 'sortOptions']))
                    $v = array_values($v);
                $this->$k = $v;
            }
            else
                trigger_error(self::class.'::__construct - unrecognized option: ' . $k);
        }

        if ($addIn && !Template\PageTemplate::test('listviews/', $addIn.'.tpl'))
            trigger_error('Nonexistent Listview addin requested: template/listviews/'.$addIn.'.tpl', E_USER_ERROR);
        else if ($addIn)
            $this->__addIn = 'template/listviews/'.$addIn.'.tpl';
    }

    /**
     * @return \Generator<int, array> rowIndex => dataRow
     */
    public function &iterate() : \Generator
    {
        reset($this->data);

        foreach ($this->data as $idx => &$row)
            yield $idx => $row;
    }

    public function appendData(array $moreData) : void
    {
        foreach ($moreData as $md)
            $this->data[] = $md;
    }

    public function getTemplate() : string
    {
        return $this->template;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function setTabs(string $tabVar) : void
    {
        if ($tabVar[0] !== '$')                             // expects a jsVar, which we denote with a prefixed $
            $tabVar = '$' . $tabVar;

        $this->tabs = $tabVar;
    }

    public function setError(bool $enable) : void
    {
        $this->_errors = $enable ? 1 : null;
    }

    public function jsonSerialize() : array
    {
        $result = [];

        foreach ($this as $prop => $val)
            if ($val !== null && substr($prop, 0, 2) != '__')
                $result[$prop] = $val;

        return $result;
    }

    public function __toString() : string
    {
        $addIn = '';
        if ($this->__addIn)
            $addIn = file_get_contents($this->__addIn).PHP_EOL;

        return $addIn.'new Listview('.Util::toJSON($this).');'.PHP_EOL;
    }
}

?>
