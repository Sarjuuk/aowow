<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ProfilePage extends GenericPage
{
    protected $type         = 0;
    protected $typeId       = 0;
    protected $path         = [1, 5, 1];
    protected $tabId        = 1;
    protected $tpl          = 'profile';
    protected $gDataKey     = true;
    protected $js           = ['filters.js', 'TalentCalc.js', 'swfobject.js', 'profile_all.js', 'profile.js', 'Profiler.js'];
    protected $css          = array(
        ['path' => 'talentcalc.css'],
        ['path' => 'Profiler.css']
    );
    protected $profileId = 0;

    private   $isCustom  = false;
    private   $profile   = null;

    public function __construct($pageCall, $pageParam)
    {
        $_ = $pageParam ? explode('.', $pageParam) : null;
        $this->getCategoryFromUrl($pageParam);

        $this->typeId &= $this->profileId;

        parent::__construct($pageCall, $pageParam);

        // temp locale
        if ($this->mode == CACHE_TYPE_TOOLTIP && isset($_GET['domain']))
            Util::powerUseLocale($_GET['domain']);

        if (count($_) == 1 && intVal($_[0]))
        {
            // todo: some query to validate existence of char
            if ($foo = DB::Aowow()->selectCell('SELECT 2161862'))
                $this->profileId = $foo;
            else
                $this->notFound();

            $this->isCustom  = true;
            $this->profile = intVal($_[0]);
        }
        else if (count($_) == 3)
        {
            // todo: some query to validate existence of char
            if ($foo = DB::Aowow()->selectCell('SELECT 2161862'))
                $this->profileId = $foo;
            else
                $this->notFound();

            $this->profile = $_;
        }
        else if ($_ || !isset($_GET['new']))
            $this->notFound();

        $this->subject = new ProfileList(/*stuff*/);
    }

    protected function generateContent()
    {
        $this->addJS('?data=enchants.gems.glyphs.itemsets.pets.pet-talents.quick-excludes.realms.statistics.weight-presets&locale='.User::$localeId.'&t='.$_SESSION['dataKey']);

        $this->region  = 'eu';
        $this->realm   = 'Realm Name';
    }

    protected function generatePath()
    {

    }

    protected function generateTitle()
    {
        array_unshift($this->title, Util::ucFirst(Lang::game('profile')));
    }

    protected function generateTooltip($asError = false)
    {
        $x = '$WowheadPower.registerProfile('.($this->isCustom ? $this->profile : "'".implode('.', $this->profile)."'").', '.User::$localeId.', {';
        if ($asError)
            return $x."});";

        @include('datasets/ProfilerExampleChar');           // tmp char data

        $name       = $character['name'];
        $guild      = $character['guild'];
        $gRankName  = $character['guildrank'];
        $lvl        = $character['level'];
        $ra         = $character['race'];
        $cl         = $character['classs'];
        $gender     = $character['gender'];
        $desc       = $character['description'];
        $title      = (new TitleList(array(['id', $character['title']])))->getField($gender ? 'female' : 'male', true);

        if ($this->isCustom)
            $name .= ' (Custom Profile)';
        else if ($title)
            $name = sprintf($title, $name);

        $x .= "\n";
        $x .= "\tname_".User::$localeString.": '".Util::jsEscape($name)."',\n";
        $x .= "\ttooltip_".User::$localeString.": '".$this->subject->renderTooltip()."',\n";
        $x .= "\ticon: \$WH.g_getProfileIcon(".$ra.", ".$cl.", ".$gender.", ".$lvl."),\n";           // (race, class, gender, level, iconOrId, 'medium')
        $x .= "});";

        return $x;
    }

    public function display($override = '')
    {
        if ($this->mode != CACHE_TYPE_TOOLTIP)
            return parent::display($override);

        // do not cache profile tooltips
        header('Content-type: application/x-javascript; charset=utf-8');
        die($this->generateTooltip());
    }

    public function notFound($title = '', $msg = '')
    {
        if ($this->mode != CACHE_TYPE_TOOLTIP)
            return parent::notFound($title ?: Util::ucFirst(Lang::game('profile')), $msg ?: '[NNF]profile or char doesn\'t exist');

        header('Content-type: application/x-javascript; charset=utf-8');
        echo $this->generateTooltip(true);
        exit();
    }
}

?>
