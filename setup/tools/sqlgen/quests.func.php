<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/* deps:
 * quest_template
 * quest_template_addon
 * locales_quest
 * game_event
 * game_event_seasonal_questrelation
*/


$customData = array(
);
$reqDBC = ['questxp', 'questfactionreward'];

function quests(array $ids = [])
{
    $baseQuery = '
        SELECT
            q.ID,
            QuestType AS Method,
            QuestLevel,
            MinLevel,
            MaxLevel,
            QuestSortID,
            QuestSortID AS zoneOrSortBak,                   -- ZoneOrSortBak
            QuestInfoID AS QuestType,
            SuggestedGroupNum,
            LimitTime,
            IFNULL(gesqr.eventEntry, 0) AS eventId,
            PrevQuestId,
            NextQuestId,
            ExclusiveGroup,
            NextQuestIdChain,
            Flags,
            SpecialFlags,
            0 AS cuFlags,                                   -- cuFlags
            AllowableClasses AS RequiredClasses,
            RequiredRaces,
            RequiredSkillId,            RequiredSkillPoints,
            RequiredFactionId1,         RequiredFactionId2,
            RequiredFactionValue1,      RequiredFactionValue2,
            RequiredMinRepFaction,      RequiredMaxRepFaction,
            RequiredMinRepValue,        RequiredMaxRepValue,
            RequiredPlayerKills,
            SourceItemId,
            ProvidedItemCount AS SourceItemCount,
            SourceSpellId,
            RewardXPId,                                     -- QuestXP.dbc x level
            RewardOrRequiredMoney,
            RewardMoneyMaxLevel,
            RewardSpell,                RewardSpellCast,
            RewardHonor * 124 * RewardHonorMultiplier,      -- alt calculation in QuestDef.cpp -> Quest::CalculateHonorGain(playerLevel)
            RewardMailTemplateId,       RewardMailDelay,
            RewardTitle,
            RewardTalents,
            RewardArenaPoints,
            RewardItem1,                RewardItem2,                RewardItem3,                RewardItem4,
            RewardAmount1,              RewardAmount2,              RewardAmount3,              RewardAmount4,
            RewardChoiceItemID1,        RewardChoiceItemID2,        RewardChoiceItemID3,        RewardChoiceItemID4,        RewardChoiceItemID5,        RewardChoiceItemID6,
            RewardChoiceItemQuantity1,  RewardChoiceItemQuantity2,  RewardChoiceItemQuantity3,  RewardChoiceItemQuantity4,  RewardChoiceItemQuantity5,  RewardChoiceItemQuantity6,
            RewardFactionID1,           RewardFactionID2,           RewardFactionID3,           RewardFactionID4,           RewardFactionID5,
            IF (RewardFactionOverride1 <> 0, RewardFactionOverride1 / 100, RewardFactionValue1),
            IF (RewardFactionOverride2 <> 0, RewardFactionOverride2 / 100, RewardFactionValue2),
            IF (RewardFactionOverride3 <> 0, RewardFactionOverride3 / 100, RewardFactionValue3),
            IF (RewardFactionOverride4 <> 0, RewardFactionOverride4 / 100, RewardFactionValue4),
            IF (RewardFactionOverride5 <> 0, RewardFactionOverride5 / 100, RewardFactionValue5),
            LogTitle,                   Title_loc2,                 Title_loc3,                 Title_loc6,                 Title_loc8,
            LogDescription,             Objectives_loc2,            Objectives_loc3,            Objectives_loc6,            Objectives_loc8,
            QuestDescription,           Details_loc2,               Details_loc3,               Details_loc6,               Details_loc8,
            EndText,                    EndText_loc2,               EndText_loc3,               EndText_loc6,               EndText_loc8,
            qor.RewardText AS OfferRewardText,            OfferRewardText_loc2,       OfferRewardText_loc3,       OfferRewardText_loc6,       OfferRewardText_loc8,
            qri.CompletionText AS RequestItemsText,           RequestItemsText_loc2,      RequestItemsText_loc3,      RequestItemsText_loc6,      RequestItemsText_loc8,
            QuestCompletionLog,         CompletedText_loc2,         CompletedText_loc3,         CompletedText_loc6,         CompletedText_loc8,
            RequiredNpcOrGo1,           RequiredNpcOrGo2,           RequiredNpcOrGo3,           RequiredNpcOrGo4,
            RequiredNpcOrGoCount1,      RequiredNpcOrGoCount2,      RequiredNpcOrGoCount3,      RequiredNpcOrGoCount4,
            RequiredSourceItemId1,      RequiredSourceItemId2,      RequiredSourceItemId3,      RequiredSourceItemId4,
            RequiredSourceItemCount1,   RequiredSourceItemCount2,   RequiredSourceItemCount3,   RequiredSourceItemCount4,
            RequiredItemId1,            RequiredItemId2,            RequiredItemId3,            RequiredItemId4,            RequiredItemId5,            RequiredItemId6,
            RequiredItemCount1,         RequiredItemCount2,         RequiredItemCount3,         RequiredItemCount4,         RequiredItemCount5,         RequiredItemCount6,
            ObjectiveText1,             ObjectiveText1_loc2,        ObjectiveText1_loc3,        ObjectiveText1_loc6,        ObjectiveText1_loc8,
            ObjectiveText2,             ObjectiveText2_loc2,        ObjectiveText2_loc3,        ObjectiveText2_loc6,        ObjectiveText2_loc8,
            ObjectiveText3,             ObjectiveText3_loc2,        ObjectiveText3_loc3,        ObjectiveText3_loc6,        ObjectiveText3_loc8,
            ObjectiveText4,             ObjectiveText4_loc2,        ObjectiveText4_loc3,        ObjectiveText4_loc6,        ObjectiveText4_loc8
        FROM
            quest_template q
        LEFT JOIN
            quest_template_addon qa ON q.ID = qa.ID
        LEFT JOIN
            quest_offer_reward qor ON q.ID = qor.ID
        LEFT JOIN
            quest_request_items qri ON q.ID = qri.ID
        LEFT JOIN
            locales_quest lq ON q.ID = lq.Id
        LEFT JOIN
            game_event_seasonal_questrelation gesqr ON gesqr.questId = q.ID
        {
        WHERE
            q.ID IN (?a)
        }
        LIMIT
            ?d, ?d';

    $xpQuery = '
        UPDATE
            ?_quests q,
            dbc_questxp xp
        SET
            rewardXP = (CASE rewardXP
                WHEN 0 THEN xp.Field1   WHEN 1 THEN xp.Field2   WHEN 2 THEN xp.Field3   WHEN 3 THEN xp.Field4   WHEN 4 THEN xp.Field5
                WHEN 5 THEN xp.Field6   WHEN 6 THEN xp.Field7   WHEN 7 THEN xp.Field8   WHEN 8 THEN xp.Field9   WHEN 9 THEN xp.Field10
                ELSE 0
            END)
        WHERE
            xp.id = q.level { AND
            q.id IN(?a)
            }';

    $repQuery = '
        UPDATE
            ?_quests q
        LEFT JOIN
            dbc_questfactionreward rep ON rep.Id = IF(rewardFactionValue?d > 0, 1, 2)
        SET
            rewardFactionValue?d = (CASE ABS(rewardFactionValue?d)
                WHEN 0 THEN rep.Field1   WHEN 1 THEN rep.Field2   WHEN 2 THEN rep.Field3   WHEN 3 THEN rep.Field4   WHEN 4 THEN rep.Field5
                WHEN 5 THEN rep.Field6   WHEN 6 THEN rep.Field7   WHEN 7 THEN rep.Field8   WHEN 8 THEN rep.Field9   WHEN 9 THEN rep.Field10
                ELSE 0
            END)
        WHERE
            ABS(rewardFactionValue?d) BETWEEN 1 AND 10 { AND
            q.id IN(?a)
            }';


    $offset = 0;
    while ($quests = DB::World()->select($baseQuery, $ids ?: DBSIMPLE_SKIP, $offset, SqlGen::$stepSize))
    {
        CLISetup::log(' * sets '.($offset + 1).' - '.($offset + count($quests)));

        $offset += SqlGen::$stepSize;

        foreach ($quests as $q)
            DB::Aowow()->query('REPLACE INTO ?_quests VALUES (?a)', array_values($q));
    }

    /*
        just some random thoughts here ..
        quest-custom-flags are derived from flags and specialFlags
        since they are not used further than being sent to JS as wFlags this is fine..
        should they be saved to db anyway..?
        same with QUEST_FLAG_UNAVAILABLE => CUSTOM_EXCLUDE_FOR_LISTVIEW
    */

    // unpack XP-reward
    DB::Aowow()->query($xpQuery, $ids ?: DBSIMPLE_SKIP);

    // unpack Rep-rewards
    for ($i = 1; $i < 6; $i++)
        DB::Aowow()->query($repQuery, $i, $i, $i, $i, $ids ?: DBSIMPLE_SKIP);

    // update zoneOrSort/QuestSortID .. well .. now "not documenting" bites me in the ass .. ~700 quests were changed, i don't know by what method
    $eventSet = DB::World()->selectCol('SELECT holiday AS ARRAY_KEY, eventEntry FROM game_event WHERE holiday <> 0');
    $holidaySorts   = array(
        141 => -1001,       181 => -374,        201 => -1002,
        301 => -101,        321 => -1005,       324 => -1003,
        327 => -366,        341 => -369,        372 => -370,
        374 => -364,        376 => -364,        404 => -375,
        409 => -41,         423 => -376,        424 => -101
    );
    foreach ($holidaySorts as $hId => $sort)
        if (!empty($eventSet[$hId]))
            DB::Aowow()->query('UPDATE ?_quests SET zoneOrSort = ?d WHERE eventId = ?d{ AND id IN (?a)}', $sort, $eventSet[$hId], $ids ?: DBSIMPLE_SKIP);

/*
    zoneorsort for quests will need updating
    points non-instanced area with identic name for instance quests

    SELECT
        DISTINCT CONCAT('[',q.zoneorsort,',"',a.name_loc0,'"],')
    FROM
        dbc_map m,
        ?_quests q,
        dbc_areatable a
    WHERE
        a.id = q.zoneOrSort AND
        q.zoneOrSort > 0 AND
        a.mapId = m.id AND
        m.areaType = 1
    ORDER BY
        a.name_loc0
    ASC;
*/

    // 'special' special cases
    // fishing quests to stranglethorn extravaganza
    DB::Aowow()->query('UPDATE ?_quests SET zoneOrSort = ?d WHERE id IN (?a){ AND id IN (?a)}',  -101, [8228, 8229], $ids ?: DBSIMPLE_SKIP);
    // dungeon quests to Misc/Dungeon Finder
    DB::Aowow()->query('UPDATE ?_quests SET zoneOrSort = ?d WHERE (specialFlags & ?d OR id IN (?a)){ AND id IN (?a)}', -1010, QUEST_FLAG_SPECIAL_DUNGEON_FINDER, [24789, 24791, 24923], $ids ?: DBSIMPLE_SKIP);

    return true;
}

?>
