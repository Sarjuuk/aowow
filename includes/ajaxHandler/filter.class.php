<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AjaxFilter extends AjaxHandler
{
    public    $doRedirect = true;

    private   $cat        = [];
    private   $page       = '';
    private   $filter     = null;

    public function __construct(array $params)
    {
        if (!$params)
            return;

        $p = explode('=', $params[0]);

        $this->page = $p[0];

        if (isset($p[1]))
            $this->cat[] = $p[1];

        if (count($params) > 1)
            for ($i = 1; $i < count($params); $i++)
                $this->cat[] = $params[$i];

        $opts = ['parentCats' => $this->cat];

        switch ($p[0])
        {
            case 'achievements':
                $this->filter = (new AchievementListFilter(true, $opts));
                break;
            case 'areatriggers':
                $this->filter = (new AreaTriggerListFilter(true, $opts));
                break;
            case 'enchantments':
                $this->filter = (new EnchantmentListFilter(true, $opts));
                break;
            case 'icons':
                $this->filter = (new IconListFilter(true, $opts));
                break;
            case 'items':
                $this->filter = (new ItemListFilter(true, $opts));
                break;
            case 'itemsets':
                $this->filter = (new ItemsetListFilter(true, $opts));
                break;
            case 'npcs':
                $this->filter = (new CreatureListFilter(true, $opts));
                break;
            case 'objects':
                $this->filter = (new GameObjectListFilter(true, $opts));
                break;
            case 'quests':
                $this->filter = (new QuestListFilter(true, $opts));
                break;
            case 'sounds':
                $this->filter = (new SoundListFilter(true, $opts));
                break;
            case 'spells':
                $this->filter = (new SpellListFilter(true, $opts));
                break;
            case 'profiles':
                $this->filter = (new ProfileListFilter(true, $opts));
                break;
            case 'guilds':
                $this->filter = (new GuildListFilter(true, $opts));
                break;
            case 'arena-teams':
                $this->filter = (new ArenaTeamListFilter(true, $opts));
                break;
            default:
                return;
        }

        parent::__construct($params);

        // always this one
        $this->handler = 'handleFilter';
    }

    protected function handleFilter() : string
    {
        $url = '?'.$this->page;

        $this->filter->mergeCat($this->cat);

        if ($this->cat)
            $url .= '='.implode('.', $this->cat);

        $fi = [];
        if ($x = $this->filter->getFilterString())
            $url .= '&filter='.$x;

        if ($this->filter->error)
            $_SESSION['fiError'] = get_class($this->filter);

        if ($fi)
            $url .= '&filter='.implode(';', $fi);

        // do get request
        return $url;
    }
}

?>
