<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
 * waybackmachine has 50ish /profile=avatar calls archived and they 302 to STATIC_URL/images/armory/medium/default_orc_female.jpg etc.
 *
 * at the time the blizzard armory also had rendered portraits of characters
 * HOST_URL/profile=avatar&size=medium&id=77047584.jpg redirects to
 * STATIC_URL/images/armory/medium/077/047/584.jpg (yes, the profileId is always split like that)
 *
 * this came probably after the tiered default icons that we are currently using
 * since we can't generate custom avatars, references to g_getProfileIcon have been edited.
 */

class ProfileAvatarResponse extends TextResponse
{
    protected bool  $requiresLogin = true;

    protected array $expectedGET   = array(
        'id'   => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => '/^\d+\.jpg$/'] ],
        'size' => ['filter' => FILTER_CALLBACK,        'options' => [self::class, 'checkTextLine']]
    );

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);

        if (!Cfg::get('PROFILER_ENABLE'))
            $this->generate404();
    }

    /*  params
            id: <prId>
            size: <string> [optional]
        return:
            <redirect>
    */
    protected function generate() : void
    {
        if (!$this->assertGET('id'))
            $this->generate404();

        $profileId = substr($this->_get['id'], 0, -4);

        $charData = DB::Aowow()->selectRow('SELECT `race`, `gender` FROM ?_profiler_profiles WHERE id = ?d', $profileId);
        if (!$charData)
            $this->generate404();

        $gender = $charData['gender'] ? 'female' : 'male';
        $race   = ChrRace::tryFrom($charData['race'])?->json() ?? 'human';
        $size   = match($this->_get['size'])
        {
            'small',
            'medium',
            'large'  => $this->_get['size'],
            default  => 'medium'
        };

        $this->redirectTo = sprintf('%s/images/armory/%s/default_%s_%s.jpg', Cfg::get('STATIC_URL'), $size, $race, $gender);
    }
}

?>
