<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class ItemsetList extends BaseType
{
    use ListviewHelper;

    public static $type       = TYPE_ITEMSET;

    public        $pieceToSet = [];                             // used to build g_items and search
    private       $classes    = [];                             // used to build g_classes

    protected     $setupQuery = 'SELECT *, id AS ARRAY_KEY FROM ?_itemset WHERE [filter] [cond] ORDER BY maxlevel DESC';

    public function __construct($data, $applyFilter = false)
    {
        parent::__construct($data, $applyFilter);

        // post processing
        foreach ($this->iterate() as &$_curTpl)
        {
            $_curTpl['classes'] = [];
            $_curTpl['pieces']  = [];
            for ($i = 1; $i < 12; $i++)
            {
                if ($_curTpl['classMask'] & (1 << ($i - 1)))
                {
                    $this->classes[] = $i;
                    $_curTpl['classes'][] = $i;
                }
            }

            for ($i = 1; $i < 10; $i++)
            {
                if ($piece = $_curTpl['item'.$i])
                {
                    $_curTpl['pieces'][] = $piece;
                    $this->pieceToSet[$piece] = $this->id;
                }
            }
        }
        $this->classes = array_unique($this->classes);
    }

    public function getListviewData()
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'id'       => $this->id,
                'idbak'    => $this->curTpl['refSetId'],
                'name'     => $this->getField('name', true),
                'quality'  => 7 - $this->curTpl['quality'],
                'minlevel' => $this->curTpl['minLevel'],
                'maxlevel' => $this->curTpl['maxLevel'],
                'note'     => $this->curTpl['contentGroup'],
                'type'     => $this->curTpl['type'],
                'heroic'   => $this->curTpl['heroic'] == 1, // we want to be bool
                'reqclass' => $this->curTpl['classMask'],
                'classes'  => $this->curTpl['classes'],
                'pieces'   => $this->curTpl['pieces'],
                'heroic'   => $this->curTpl['heroic']
            );
        }

        return $data;
    }

    public function addGlobalsToJscript(&$template, $addMask = GLOBALINFO_ANY)
    {
        if ($this->classes && ($addMask & GLOBALINFO_RELATED))
            $template->extendGlobalIds(TYPE_CLASS, $this->classes);

        if ($this->pieceToSet && ($addMask & GLOBALINFO_SELF))
            $template->extendGlobalIds(TYPE_ITEM, array_keys($this->pieceToSet));
    }

    public function renderTooltip() { }
}

?>
