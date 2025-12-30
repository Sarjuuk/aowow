<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("sql", new class extends SetupScript
{
    use TrCustomData;                                       // import custom data from DB

    protected $info = array(
        'quests' => [[], CLISetup::ARGV_PARAM, 'Compiles data for type: Quest from dbc and world db.']
    );

    protected $dbcSourceFiles  = ['questxp', 'questfactionreward'];
    protected $worldDependency = ['quest_template', 'quest_template_addon', 'quest_template_locale', 'game_event', 'game_event_seasonal_questrelation', 'quest_request_items', 'quest_request_items_locale', 'quest_offer_reward', 'quest_offer_reward_locale',  'disables'];

    public function generate(array $ids = []) : bool
    {
        $baseQuery =
           'SELECT    q.ID,
                      QuestType,
                      QuestLevel,
                      MinLevel,
                      IFNULL(qa.MaxLevel, 0),
                      QuestSortID,
                      QuestSortID AS questSortIdBak,
                      QuestInfoID,
                      SuggestedGroupNum,
                      TimeAllowed,
                      IFNULL(gesqr.eventEntry, 0) AS eventId,
                      IFNULL(qa.PrevQuestId, 0),
                      IFNULL(qa.NextQuestId, 0),
                      IFNULL(qa.BreadcrumbForQuestId, 0),
                      IFNULL(qa.ExclusiveGroup, 0),
                      RewardNextQuest,
                      q.Flags,
                      IFNULL(qa.SpecialFlags, 0),
                      (
                          IF(d.entry IS NULL, 0, 134217728) +         -- disabled
                          IF(q.Flags & 16384, 536870912, 0)           -- unavailable
                      ) AS cuFlags,
                      IFNULL(qa.AllowableClasses, 0),
                      AllowableRaces,
                      IFNULL(qa.RequiredSkillId, 0),      IFNULL(qa.RequiredSkillPoints, 0),
                      RequiredFactionId1,                 RequiredFactionId2,
                      RequiredFactionValue1,              RequiredFactionValue2,
                      IFNULL(qa.RequiredMinRepFaction, 0),IFNULL(qa.RequiredMaxRepFaction, 0),
                      IFNULL(qa.RequiredMinRepValue, 0),  IFNULL(qa.RequiredMaxRepValue, 0),
                      RequiredPlayerKills,
                      StartItem,
                      IFNULL(qa.ProvidedItemCount, 0),
                      IFNULL(qa.SourceSpellId, 0),
                      RewardXPDifficulty,                             -- QuestXP.dbc x level
                      RewardMoney,
                      RewardBonusMoney,
                      RewardDisplaySpell,                 RewardSpell,
                      RewardHonor * 124 * RewardKillHonor,            -- alt calculation in QuestDef.cpp -> Quest::CalculateHonorGain(playerLevel)
                      IFNULL(qa.RewardMailTemplateId, 0), IFNULL(qa.RewardMailDelay, 0),
                      RewardTitle,
                      RewardTalents,
                      RewardArenaPoints,
                      RewardItem1,                        RewardItem2,                         RewardItem3,                         RewardItem4,
                      RewardAmount1,                      RewardAmount2,                       RewardAmount3,                       RewardAmount4,
                      RewardChoiceItemID1,                RewardChoiceItemID2,                 RewardChoiceItemID3,                 RewardChoiceItemID4,                 RewardChoiceItemID5,                  RewardChoiceItemID6,
                      RewardChoiceItemQuantity1,          RewardChoiceItemQuantity2,           RewardChoiceItemQuantity3,           RewardChoiceItemQuantity4,           RewardChoiceItemQuantity5,            RewardChoiceItemQuantity6,
                      RewardFactionID1,                   RewardFactionID2,                    RewardFactionID3,                    RewardFactionID4,                    RewardFactionID5,
                      IF (RewardFactionOverride1 <> 0, RewardFactionOverride1 / 100, RewardFactionValue1),
                      IF (RewardFactionOverride2 <> 0, RewardFactionOverride2 / 100, RewardFactionValue2),
                      IF (RewardFactionOverride3 <> 0, RewardFactionOverride3 / 100, RewardFactionValue3),
                      IF (RewardFactionOverride4 <> 0, RewardFactionOverride4 / 100, RewardFactionValue4),
                      IF (RewardFactionOverride5 <> 0, RewardFactionOverride5 / 100, RewardFactionValue5),
                      q.LogTitle,                         IFNULL(qtl2.Title, ""),              IFNULL(qtl3.Title, ""),              IFNULL(qtl4.Title, ""),              IFNULL(qtl6.Title, ""),              IFNULL(qtl8.Title, ""),
                      q.LogDescription,                   IFNULL(qtl2.Objectives, ""),         IFNULL(qtl3.Objectives, ""),         IFNULL(qtl4.Objectives, ""),         IFNULL(qtl6.Objectives, ""),         IFNULL(qtl8.Objectives, ""),
                      q.QuestDescription,                 IFNULL(qtl2.Details, ""),            IFNULL(qtl3.Details, ""),            IFNULL(qtl4.Details, ""),            IFNULL(qtl6.Details, ""),            IFNULL(qtl8.Details, ""),
                      q.AreaDescription,                  IFNULL(qtl2.EndText, ""),            IFNULL(qtl3.EndText, ""),            IFNULL(qtl4.EndText, ""),            IFNULL(qtl6.EndText, ""),            IFNULL(qtl8.EndText, ""),
                      IFNULL(qor.RewardText, ""),         IFNULL(qorl2.RewardText, ""),        IFNULL(qorl3.RewardText, ""),        IFNULL(qorl4.RewardText, ""),        IFNULL(qorl6.RewardText, ""),        IFNULL(qorl8.RewardText, ""),
                      IFNULL(qri.CompletionText, ""),     IFNULL(qril2.CompletionText, ""),    IFNULL(qril3.CompletionText, ""),    IFNULL(qril4.CompletionText, ""),    IFNULL(qril6.CompletionText, ""),    IFNULL(qril8.CompletionText, ""),
                      q.QuestCompletionLog,               IFNULL(qtl2.CompletedText, ""),      IFNULL(qtl3.CompletedText, ""),      IFNULL(qtl4.CompletedText, ""),      IFNULL(qtl6.CompletedText, ""),      IFNULL(qtl8.CompletedText, ""),
                      RequiredNpcOrGo1,                   RequiredNpcOrGo2,                    RequiredNpcOrGo3,                    RequiredNpcOrGo4,
                      RequiredNpcOrGoCount1,              RequiredNpcOrGoCount2,               RequiredNpcOrGoCount3,               RequiredNpcOrGoCount4,
                      ItemDrop1,                          ItemDrop2,                           ItemDrop3,                           ItemDrop4,
                      ItemDropQuantity1,                  ItemDropQuantity2,                   ItemDropQuantity3,                   ItemDropQuantity4,
                      RequiredItemId1,                    RequiredItemId2,                     RequiredItemId3,                     RequiredItemId4,                     RequiredItemId5,                      RequiredItemId6,
                      RequiredItemCount1,                 RequiredItemCount2,                  RequiredItemCount3,                  RequiredItemCount4,                  RequiredItemCount5,                   RequiredItemCount6,
                      q.ObjectiveText1,                   IFNULL(qtl2.ObjectiveText1, ""),     IFNULL(qtl3.ObjectiveText1, ""),     IFNULL(qtl4.ObjectiveText1, ""),     IFNULL(qtl6.ObjectiveText1, ""),     IFNULL(qtl8.ObjectiveText1, ""),
                      q.ObjectiveText2,                   IFNULL(qtl2.ObjectiveText2, ""),     IFNULL(qtl3.ObjectiveText2, ""),     IFNULL(qtl4.ObjectiveText2, ""),     IFNULL(qtl6.ObjectiveText2, ""),     IFNULL(qtl8.ObjectiveText2, ""),
                      q.ObjectiveText3,                   IFNULL(qtl2.ObjectiveText3, ""),     IFNULL(qtl3.ObjectiveText3, ""),     IFNULL(qtl4.ObjectiveText3, ""),     IFNULL(qtl6.ObjectiveText3, ""),     IFNULL(qtl8.ObjectiveText3, ""),
                      q.ObjectiveText4,                   IFNULL(qtl2.ObjectiveText4, ""),     IFNULL(qtl3.ObjectiveText4, ""),     IFNULL(qtl4.ObjectiveText4, ""),     IFNULL(qtl6.ObjectiveText4, ""),     IFNULL(qtl8.ObjectiveText4, "")
            FROM      quest_template q
            LEFT JOIN quest_template_locale qtl2 ON q.ID = qtl2.ID AND qtl2.locale = "frFR"
            LEFT JOIN quest_template_locale qtl3 ON q.ID = qtl3.ID AND qtl3.locale = "deDE"
            LEFT JOIN quest_template_locale qtl4 ON q.ID = qtl4.ID AND qtl4.locale = "zhCN"
            LEFT JOIN quest_template_locale qtl6 ON q.ID = qtl6.ID AND qtl6.locale = "esES"
            LEFT JOIN quest_template_locale qtl8 ON q.ID = qtl8.ID AND qtl8.locale = "ruRU"
            LEFT JOIN quest_offer_reward qor ON q.ID = qor.ID
            LEFT JOIN quest_offer_reward_locale qorl2 ON q.ID = qorl2.ID AND qorl2.locale = "frFR"
            LEFT JOIN quest_offer_reward_locale qorl3 ON q.ID = qorl3.ID AND qorl3.locale = "deDE"
            LEFT JOIN quest_offer_reward_locale qorl4 ON q.ID = qorl4.ID AND qorl4.locale = "zhCN"
            LEFT JOIN quest_offer_reward_locale qorl6 ON q.ID = qorl6.ID AND qorl6.locale = "esES"
            LEFT JOIN quest_offer_reward_locale qorl8 ON q.ID = qorl8.ID AND qorl8.locale = "ruRU"
            LEFT JOIN quest_request_items qri ON q.ID = qri.ID
            LEFT JOIN quest_request_items_locale qril2 ON q.ID = qril2.ID AND qril2.locale = "frFR"
            LEFT JOIN quest_request_items_locale qril3 ON q.ID = qril3.ID AND qril3.locale = "deDE"
            LEFT JOIN quest_request_items_locale qril4 ON q.ID = qril4.ID AND qril4.locale = "zhCN"
            LEFT JOIN quest_request_items_locale qril6 ON q.ID = qril6.ID AND qril6.locale = "esES"
            LEFT JOIN quest_request_items_locale qril8 ON q.ID = qril8.ID AND qril8.locale = "ruRU"
            LEFT JOIN quest_template_addon qa ON q.ID = qa.ID
            LEFT JOIN game_event_seasonal_questrelation gesqr ON gesqr.questId = q.ID
            LEFT JOIN disables d ON d.entry = q.ID AND d.sourceType = 1
          { WHERE     q.Id IN (?a) }
            LIMIT     ?d, ?d';

        $i = 0;
        DB::Aowow()->query('TRUNCATE ?_quests');
        while ($quests = DB::World()->select($baseQuery, $ids ?: DBSIMPLE_SKIP, CLISetup::SQL_BATCH * $i, CLISetup::SQL_BATCH))
        {
            CLI::write(' * batch #' . ++$i . ' (' . count($quests) . ')', CLI::LOG_BLANK, true, true);

            foreach ($quests as $quest)
                DB::Aowow()->query('INSERT INTO ?_quests VALUES (?a)', array_values($quest));
        }


        /*
            just some random thoughts here ..
            quest-custom-flags are derived from flags and specialFlags
            since they are not used further than being sent to JS as wFlags this is fine..
            should they be saved to db anyway..?
            same with QUEST_FLAG_UNAVAILABLE => CUSTOM_EXCLUDE_FOR_LISTVIEW
        */


        CLI::write(' * unpacking XP rewards (1/4)', CLI::LOG_BLANK, true, true);

        // unpack XP-reward
        DB::Aowow()->query(
           'UPDATE ?_quests q, dbc_questxp xp
            SET    `rewardXP` = (CASE `rewardXP`
                       WHEN 0 THEN xp.`Field1` WHEN 1 THEN xp.`Field2` WHEN 2 THEN xp.`Field3` WHEN 3 THEN xp.`Field4` WHEN 4 THEN xp.`Field5`
                       WHEN 5 THEN xp.`Field6` WHEN 6 THEN xp.`Field7` WHEN 7 THEN xp.`Field8` WHEN 8 THEN xp.`Field9` WHEN 9 THEN xp.`Field10`
                       ELSE 0 END)
            WHERE  xp.`id` = q.`level` { AND q.`id` IN (?a) }',
            $ids ?: DBSIMPLE_SKIP
        );


        CLI::write(' * unpacking reputation gains (2/4)', CLI::LOG_BLANK, true, true);

        // unpack Rep-rewards
        for ($i = 1; $i < 6; $i++)
            DB::Aowow()->query(
               'UPDATE    ?_quests q
                LEFT JOIN dbc_questfactionreward rep ON rep.`id` = IF(rewardFactionValue?d > 0, 1, 2)
                SET       rewardFactionValue?d = (CASE ABS(rewardFactionValue?d)
                              WHEN 0 THEN rep.`Field1` WHEN 1 THEN rep.`Field2` WHEN 2 THEN rep.`Field3` WHEN 3 THEN rep.`Field4` WHEN 4 THEN rep.`Field5`
                              WHEN 5 THEN rep.`Field6` WHEN 6 THEN rep.`Field7` WHEN 7 THEN rep.`Field8` WHEN 8 THEN rep.`Field9` WHEN 9 THEN rep.`Field10`
                              ELSE 0 END)
                WHERE     ABS(rewardFactionValue?d) BETWEEN 1 AND 10 { AND q.`id` IN (?a) }',
                $i, $i, $i, $i,
                $ids ?: DBSIMPLE_SKIP
            );


        CLI::write(' * flagging quests series (3/4)', CLI::LOG_BLANK, true, true);

        // set series flags
        DB::Aowow()->query(
           'UPDATE    ?_quests q
            LEFT JOIN ?_quests q2 ON q2.`NextQuestIdChain` = q.id
            SET       q.`cuFlags` = q.`cuFlags` | ?d
            WHERE     q.`NextQuestIdChain` > 0 AND q2.`id` IS NULL',
            QUEST_CU_FIRST_SERIES | QUEST_CU_PART_OF_SERIES
        );

        DB::Aowow()->query(
           'UPDATE ?_quests q
            JOIN   ?_quests q2 ON q2.`NextQuestIdChain` = q.id
            SET    q.`cuFlags` = q.`cuFlags` | ?d
            WHERE  q.`NextQuestIdChain` = 0',
            QUEST_CU_LAST_SERIES | QUEST_CU_PART_OF_SERIES
        );

        DB::Aowow()->query('UPDATE ?_quests SET `cuFlags` = `cuFlags` | ?d WHERE `NextQuestIdChain` > 0', QUEST_CU_PART_OF_SERIES);


        CLI::write(' * applying fixes (4/4)', CLI::LOG_BLANK, true, true);

        // fix questSorts for instance quests
        foreach (Game::$questSortFix as $child => $parent)
            DB::Aowow()->query('UPDATE ?_quests SET `questSortId` = ?d WHERE `questSortIdBak` = ?d', $parent, $child);


        // move quests linked to holidays into appropirate quests-sorts. create dummy sorts as needed
        $eventSet = DB::World()->selectCol('SELECT `holiday` AS ARRAY_KEY, `eventEntry` FROM game_event WHERE `holiday` <> 0');
        $holidaySorts = array(
            141 => -1001,   181 => -374,    201 => -1002,   // Winter Veil      Noblegarden     Childrens Week
            321 => -1005,   324 => -1003,   404 => -375,    // Harvest Fest.    Hallows End     Pilgrims Bounty
            327 => -366,    341 => -369,    372 => -370,    // Lunar Fest.      Midsummer       Brewfest
            423 => -376                                     // Love is in the Air
        );

        foreach ($holidaySorts as $hId => $sort)
            if (!empty($eventSet[$hId]))
                DB::Aowow()->query('UPDATE ?_quests SET `questSortId` = ?d WHERE `eventId` = ?d{ AND `id` IN (?a)}', $sort, $eventSet[$hId], $ids ?: DBSIMPLE_SKIP);


        // 'special' special cases
        // fishing quests to stranglethorn extravaganza
        DB::Aowow()->query('UPDATE ?_quests SET `questSortId` = ?d WHERE `id` IN (?a){ AND `id` IN (?a)}',  -101, [8228, 8229], $ids ?: DBSIMPLE_SKIP);
        // dungeon quests to Misc/Dungeon Finder
        DB::Aowow()->query('UPDATE ?_quests SET `questSortId` = ?d WHERE (`specialFlags` & ?d OR `id` IN (?a)){ AND `id` IN (?a)}', -1010, QUEST_FLAG_SPECIAL_DUNGEON_FINDER, [24789, 24791, 24923], $ids ?: DBSIMPLE_SKIP);


        // flag internal/unsued quests as unsearchable
        DB::Aowow()->query(
           'UPDATE ?_quests
            SET    `cuFlags` = `cuFlags` | ?d
            WHERE  `name_loc0` LIKE "%<%" OR `name_loc0` LIKE "%[%" OR
                   `name_loc0` LIKE "%deprecated%" OR
                   `name_loc0` LIKE "%unused%" OR
                   `name_loc0` LIKE "temp %"',
            CUSTOM_EXCLUDE_FOR_LISTVIEW
        );

        $this->reapplyCCFlags('quests', Type::QUEST);

        return true;
    }
});

?>
