<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SignatureBaseResponse extends TemplateResponse
{
    protected bool   $requiresLogin  = true;
    protected string $template       = 'text-page-generic';
    protected string $pageName       = 'signature';

    protected array  $expectedGET    = array(
        'profile' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkProfile']] // optional - full profile string to build sig from
    );

    private int $id = 0;

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);

        if ($rawParam)
            $this->id = intVal($rawParam);
        else if ($this->assertGET('profile'))
            $this->id = $this->_get['profile'];
        else
            $this->generateError();
    }

    protected function generate() : void
    {
        // show editor

        parent::generate();
    }

    protected static function checkProfile(string $profile) : ?int
    {
        if (!preg_match('/^([a-z]+)\.([a-z_]+)\.(.+)$/i', $profile, $m))
            return null;

        [, $region, $realm, $char] = $m;

        $realms = Profiler::getRealms();
        if ($rId = array_find_key($realms, fn($x) => $x['region'] == $region && $x['name'] == $realm))
            return DB::Aowow()->selectCell('SELECT `id` FROM ?_profiler_profiles WHERE `realm` = ?d AND `custom` = 0 AND `name` = ?', $rId, urldecode($char)) ?: null;

        return null;
    }
}

?>
