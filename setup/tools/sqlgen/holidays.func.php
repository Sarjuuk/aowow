<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');



SqlGen::register(new class extends SetupScript
{
    use TrCustomData;

    protected $command = 'holidays';

    protected $dbcSourceFiles = ['holidays', 'holidaydescriptions', 'holidaynames'];

    private $customData = array(
         62 => ['iconString' => 'inv_misc_missilelarge_red'                                                                 ],
        141 => ['iconString' => 'calendar_winterveilstart',                                    'achievementCatOrId' => 156  ],
        181 => ['iconString' => 'calendar_noblegardenstart',                                   'achievementCatOrId' => 159  ],
        201 => ['iconString' => 'calendar_childrensweekstart',                                 'achievementCatOrId' => 163  ],
        283 => ['iconString' => 'inv_jewelry_necklace_21'                                                                   ],
        284 => ['iconString' => 'inv_misc_rune_07'                                                                          ],
        285 => ['iconString' => 'inv_jewelry_amulet_07'                                                                     ],
        301 => ['iconString' => 'calendar_fishingextravaganzastart'                                                         ],
        321 => ['iconString' => 'calendar_harvestfestivalstart'                                                             ],
        324 => ['iconString' => 'calendar_hallowsendstart',           'bossCreature' => 23682, 'achievementCatOrId' => 158  ],
        327 => ['iconString' => 'calendar_lunarfestivalstart',        'bossCreature' => 15467, 'achievementCatOrId' => 160  ],
        335 => ['iconString' => 'calendar_loveintheairstart'                                                                ],
        341 => ['iconString' => 'calendar_midsummerstart',            'bossCreature' => 25740, 'achievementCatOrId' => 161  ],
        353 => ['iconString' => 'spell_nature_eyeofthestorm'                                                                ],
        372 => ['iconString' => 'calendar_brewfeststart',             'bossCreature' => 23872, 'achievementCatOrId' => 162  ],
        374 => ['iconString' => 'calendar_darkmoonfaireelwynnstart'                                                         ],
        375 => ['iconString' => 'calendar_darkmoonfairemulgorestart'                                                        ],
        376 => ['iconString' => 'calendar_darkmoonfaireterokkarstart'                                                       ],
        398 => ['iconString' => 'calendar_piratesdaystart',                                    'achievementCatOrId' => -3457],
        400 => ['iconString' => 'achievement_bg_winsoa'                                                                     ],
        404 => ['iconString' => 'calendar_harvestfestivalstart',                               'achievementCatOrId' => 14981],
        406 => ['iconString' => 'achievement_boss_lichking'                                                                 ],
        409 => ['iconString' => 'calendar_dayofthedeadstart',                                  'achievementCatOrId' => -3456],
        420 => ['iconString' => 'achievement_bg_winwsg'                                                                     ],
        423 => ['iconString' => 'calendar_loveintheairstart',         'bossCreature' => 36296, 'achievementCatOrId' => 187  ],
        424 => ['iconString' => 'calendar_fishingextravaganzastart'                                                         ],
    );

    public function generate(array $ids = []) : bool
    {
        $query = '
            REPLACE INTO
                ?_holidays (id, name_loc0, name_loc2, name_loc3, name_loc4, name_loc6, name_loc8, description_loc0, description_loc2, description_loc3, description_loc4, description_loc6, description_loc8, looping, scheduleType, textureString)
            SELECT
                h.id, n.name_loc0, n.name_loc2, n.name_loc3, n.name_loc4, n.name_loc6, n.name_loc8, d.description_loc0, d.description_loc2, d.description_loc3, d.description_loc4, d.description_loc6, d.description_loc8, h.looping, h.scheduleType, h.textureString
            FROM
                dbc_holidays h
            LEFT JOIN
                dbc_holidaynames n ON n.id = h.nameId
            LEFT JOIN
                dbc_holidaydescriptions d ON d.id = h.descriptionId';

        DB::Aowow()->query($query);

        return true;
    }
});

?>
