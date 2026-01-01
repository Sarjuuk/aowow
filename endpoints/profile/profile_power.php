<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ProfilePowerResponse extends TextResponse implements ICache
{
    use TrProfilerDetail, TrCache, TrTooltip;

    private const /* string */ POWER_TEMPLATE = '$WowheadPower.registerProfile(%s, %d, %s);';

    protected int   $type        = Type::PROFILE;
    protected int   $cacheType   = CACHE_TYPE_TOOLTIP;

    protected array $expectedGET = array(
        'domain' => ['filter' => FILTER_CALLBACK, 'options' => [Locale::class, 'tryFromDomain']]
    );

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);

        if (!Cfg::get('PROFILER_ENABLE'))
            $this->generate404();

        // temp locale
        if ($this->_get['domain'])
            Lang::load($this->_get['domain']);

        $this->getSubjectFromUrl($rawParam);

        if ($this->subjectName)                             // rawParam is fully defined profiler string
        {
            // pending rename
            if (preg_match('/([^\-]+)-(\d+)/i', $this->subjectName, $m))
                [, $this->subjectName, $renameItr] = $m;

            if ($x = DB::Aowow()->selectCell('SELECT `id` FROM ?_profiler_profiles WHERE `realm` = ?d AND `custom` = 0 AND `name` = ? AND `renameItr` = ?d', $this->realmId, Util::ucWords($this->subjectName), $renameItr ?? 0))
                $this->typeId = $x;
        }

        if (!$this->typeId)
            $this->generate404();
    }

    protected function generate() : void
    {
        $profile = new LocalProfileList(array(['id', $this->typeId]));
        if ($profile->error || !$profile->isVisibleToUser())
            $this->cacheType = CACHE_TYPE_NONE;
        else
        {
            $n = $profile->getField('name');
            $r = $profile->getField('race');
            $c = $profile->getField('class');
            $g = $profile->getField('gender');
            $l = $profile->getField('level');

            if (!$this->subjectName)                        // implicit isCustom
                $n .= Lang::profiler('customProfile');
            else if ($_ = $profile->getField('title'))
                if ($title = (new TitleList(array(['id', $_])))?->getField($g ? 'female' : 'male', true))
                    $n = sprintf($title, $n);

            $opts = array(
                'name'    => $n,
                'tooltip' => $profile->renderTooltip(),
                'icon'    => '$$WH.g_getProfileIcon('.$r.', '.$c.', '.$g.', '.$l.', \''.$profile->getIcon().'\')'
            );
        }

        if ($this->subjectName)
            $id = implode('.', [$this->region, Profiler::urlize($this->realm, true), urlencode($this->subjectName)]);
        else
            $id = $this->typeId;

        $this->result = new Tooltip(self::POWER_TEMPLATE, $id, $opts ?? []);
    }
}

?>
