<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class ItemsetList extends BaseType
{
    private   $classes    = [];                             // used to build g_classes
    public    $pieces     = [];                             // used to build g_items and search
    public    $pieceToSet = [];

    protected $setupQuery = 'SELECT *, id AS ARRAY_KEY FROM ?_itemset WHERE [filter] [cond] ORDER BY maxlevel ASC';
    protected $matchQuery = 'SELECT COUNT(1) FROM ?_itemset WHERE [filter] [cond]';

    public function __construct($data)
    {
        parent::__construct($data);

        // post processing
        foreach ($this->templates as $this->curTpl)
        {
            $Id = $this->curTpl['id'];

            $this->templates[$Id]['classes'] = [];
            $this->templates[$Id]['pieces']  = [];

            for ($i = 1; $i < 12; $i++)
            {
                if ($this->curTpl['classMask'] & (1 << $i))
                {
                    $this->classes[] = $i + 1;
                    $this->templates[$Id]['classes'][] = $i + 1;
                }
            }

            for ($i = 1; $i < 10; $i++)
            {
                if ($piece = $this->curTpl['item'.$i])
                {
                    $this->pieces[] = $piece;
                    $this->templates[$Id]['pieces'][] = $piece;
                    $this->pieceToSet[$piece] = $this->id;
                }
            }
        }

        $this->classes    = array_unique($this->classes);
        $this->pieces     = array_unique($this->pieces);
        $this->pieceToSet = array_unique($this->pieceToSet);


        // AAARG TODO!
        $this->curTpl = reset($this->templates);            // restore 'iterator'
    }

    public function getListviewData()
    {
        $data = [];

        while ($this->iterate())
        {
            $data[$this->id] = array(
                'id'       => $this->id,
                'idbak'    => $this->curTpl['refSetId'],
                'name'     => $this->names[$this->id],
                'quality'  => 7 - $this->curTpl['quality'],
                'minlevel' => $this->curTpl['minLevel'],
                'maxlevel' => $this->curTpl['maxLevel'],
                'note'     => $this->curTpl['contentGroup'],
                'type'     => $this->curTpl['type'],
                'heroic'   => $this->curTpl['heroic'] == 1, // we want to be bool
                'reqclass' => $this->curTpl['classMask'],
                'classes'  => $this->curTpl['classes'],
                'pieces'   => $this->curTpl['pieces']
            );
        }

        return $data;
    }

    public function addGlobalsToJscript(&$refs)
    {
        if ($this->classes)
            (new CharClassList(array(['id', $this->classes])))->addGlobalsToJscript($refs);

        if ($this->pieces)
            (new ItemList(array(['i.entry', $this->pieces])))->addGlobalsToJscript($refs);
    }

    public function addRewardsToJScript(&$ref) { }
    public function renderTooltip() { }
}

?>
