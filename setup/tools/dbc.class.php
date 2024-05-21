<?php

/*
    DBC::read - PHP function for loading DBC file into array
    This file is a part of AoWoW project.
    Copyright (C) 2009-2010  Mix <ru-mangos.ru>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/*
    Supported format characters:
      x   - not used/unknown, 4 bytes
      X   - not used/unknown, 1 byte
      s   - char*
      f   - float, 4 bytes (rounded to 4 digits after comma)
      u   - unsigned int, 4 bytes
      i   - signed int, 4 bytes
      b   - unsigned char, 1 byte
      d   - sorted by this field, not included in array
      n   - same, but field included in array
*/
class DBC
{
    private $_formats = array(                                  // locales block for copy pasta: sxsssxsxsxxxxxxxx | xxxxxxxxxxxxxxxxx
        'achievement'                   => 'niiisxsssxsxsxxxxxxxxsxsssxsxsxxxxxxxxiiiiisxsssxsxsxxxxxxxxii',
        'achievement_category'          => 'nixxxxxxxxxxxxxxxxxx',
        'achievement_criteria'          => 'niiiiiiiisxsssxsxsxxxxxxxxiixii',
        'areatable'                     => 'niixixxiiixsxsssxsxsxxxxxxxxixxxxxxx',
        'areatrigger'                   => 'niffxxxxxf',
        'battlemasterlist'              => 'niixxxxxxixxxxxxxxxxxxxxxxxxixii',
        'charbaseinfo'                  => 'bb',
        'charstartoutfit'               => 'nbbbXiiiiiiiiiiiiiiiiiiiixxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'chartitles'                    => 'nxsxsssxsxsxxxxxxxxsxsssxsxsxxxxxxxxi',
        'chrclasses'                    => 'nxixsxsssxsxsxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxsxixi',
        'chrraces'                      => 'niixxxxixxxsxisxsssxsxsxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxi',
        'creaturedisplayinfo'           => 'niiixxssssxxixxx',
        'creaturedisplayinfoextra'      => 'nxxxxxxxxxxxxxxxxxxxs',
        'creaturefamily'                => 'nxxxxixiiisxsssxsxsxxxxxxxxs',
        'creaturemodeldata'             => 'nxxxxxxxxxxxxixxxxxxxxxxxxxx',
        'creaturesounddata'             => 'niiiixiiiiiiiiixxxxixxxxixiiiiixxiiiix',
        'currencytypes'                 => 'niix',
        'declinedword'                  => 'ns',
        'declinedwordcases'             => 'niis',
        'dungeonmap'                    => 'niiffffi',
        'durabilitycosts'               => 'niiiiiiiiixiiiiiiiiiiixiiiixix',
        'durabilityquality'             => 'nf',
        'dungeonencounter'              => 'niiiisxsssxsxsxxxxxxxxx',
        'emotes'                        => 'nsiiiii',
        'emotestext'                    => 'nsiiiixixixiixxixxx',
        'emotestextdata'                => 'nsxsssxsxsxxxxxxxx',
        'emotestextsound'               => 'niiii',
        'faction'                       => 'niiiiiiiiiiiiiixxxiffixsxsssxsxsxxxxxxxxxxxxxxxxxxxxxxxxx',
        'factiontemplate'               => 'nixiiiiiiiiiii',
        'gemproperties'                 => 'nixxi',
        'glyphproperties'               => 'niii',
        'gtchancetomeleecrit'           => 'f',
        'gtchancetomeleecritbase'       => 'f',
        'gtchancetospellcrit'           => 'f',
        'gtchancetospellcritbase'       => 'f',
        'gtcombatratings'               => 'f',
        'gtoctclasscombatratingscalar'  => 'nf',
        'gtoctregenhp'                  => 'f',
        'gtregenmpperspt'               => 'f',
        'gtregenhpperspt'               => 'f',
        'holidays'                      => 'nxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxixxxxxxxxxxiisxix',
        'holidaydescriptions'           => 'nsxsssxsxsxxxxxxxx',
        'holidaynames'                  => 'nsxsssxsxsxxxxxxxx',
        'item'                          => 'niiiiiii',
        'itemdisplayinfo'               => 'nssxxsxxxxxiixxxxxxxxxxxx',
        'itemgroupsounds'               => 'niixx',
        'itemextendedcost'              => 'niiiiiiiiiiiiiix',
        'itemlimitcategory'             => 'nsxsssxsxsxxxxxxxxii',
        'itemrandomproperties'          => 'nsiiiiisxsssxsxsxxxxxxxx',
        'itemrandomsuffix'              => 'nsxsssxsxsxxxxxxxxsiiiiiiiiii',
        'itemset'                       => 'nsxsssxsxsxxxxxxxxxxxxxxxxxxxxxxxxxiiiiiiiiiiiiiiiiii',
        'itemsubclass'                  => 'iixxxxxxxixxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'lfgdungeons'                   => 'nsxsssxsxsxxxxxxxxiiiiiiixiixixixxxxxxxxxxxxxxxxx',
        'lock'                          => 'niiiiixxxiiiiixxxiiiiixxxxxxxxxxx',
        'locktype'                      => 'nsxsssxsxsxxxxxxxxsxsssxsxsxxxxxxxxsxsssxsxsxxxxxxxxs',
        'mailtemplate'                  => 'nsxsssxsxsxxxxxxxxsxsssxsxsxxxxxxxx',
        'map'                           => 'nsixisxsssxsxsxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxiffxixi',
        'mapdifficulty'                 => 'niixxxxxxxxxxxxxxxxxxis',
        'material'                      => 'nxxii',
        'npcsounds'                     => 'niiix',
        'overridespelldata'             => 'niiiixixxxxx',
        'powerdisplay'                  => 'nisbbb',
        'questfactionreward'            => 'niiiiiiiiii',
        'questsort'                     => 'nsxsssxsxsxxxxxxxx',
        'questxp'                       => 'niiiiiiiiii',
        'randproppoints'                => 'niiiiiiiiiiiiiii',
        'scalingstatdistribution'       => 'niiiiiiiiiiiiiiiiiiiii',
        'scalingstatvalues'             => 'xniiiiiiiiiiiiiiiiiiiiii',
        'screeneffect'                  => 'nsxxxxxxii',
        'skillline'                     => 'nixsxsssxsxsxxxxxxxxsxsssxsxsxxxxxxxxixxxxxxxxxxxxxxxxxx',
        'skilllineability'              => 'niiiixxixiiixx',
        'skilllinecategory'             => 'nsxsssxsxsxxxxxxxxi',
        'skillraceclassinfo'            => 'niiiiixx',
        'soundambience'                 => 'nii',
        'soundemitters'                 => 'nffxxxxiix',
        'soundentries'                  => 'nisssssssssssxxxxxxxxxxsxixxxx',
        'spell'                         => 'niiiuuuuuuuuixixixixxxxxxxxxiiixxxxiiiiiiiiiiiixxiiiiiiiiiiiiiiiiiiiiiiiiiiiifffiiiiiiiiiiiiiiiiiiiiifffiiiiiiiiiiiiiiifffiiiiiiiiiiiiixsxsssxsxsxxxxxxxxsxsssxsxsxxxxxxxxsxsssxsxsxxxxxxxxsxsssxsxsxxxxxxxxiiiiiiiiiixxfffxxxiixiixifffii',
        'spellcasttimes'                => 'nixx',
        'spelldescriptionvariables'     => 'ns',
        'spelldifficulty'               => 'xiiii',
        'spellduration'                 => 'nixx',
        'spellfocusobject'              => 'nsxsssxsxsxxxxxxxx',
        'spellicon'                     => 'ns',
        'spellitemenchantment'          => 'niiiiiiixxxiiisxsssxsxsxxxxxxxxxxxiiii',
        'spellitemenchantmentcondition' => 'nbbbbbxxxxxbbbbbbbbbbiiiiiXXXXX',
        'spellradius'                   => 'nfxf',
        'spellrange'                    => 'nffffisxsssxsxsxxxxxxxxxxxxxxxxxxxxxxxxx',
        'spellrunecost'                 => 'niiii',
        'spellshapeshiftform'           => 'nxsxsssxsxsxxxxxxxxiixxiixxiiiiiiii',
        'spellvisual'                   => 'niiiiiixxxxiixiixxxxxxiiiixxxxxx',
        'spellvisualkit'                => 'nxxxxxxxxxxxxxxixxxxxxxxxxxxxxxxxxxxxx',
        'talent'                        => 'niiiiiiiixxxxixxixxixii',
        'talenttab'                     => 'nsxsssxsxsxxxxxxxxiiiiis',
        'taxinodes'                     => 'niffxsxsssxsxsxxxxxxxxxx',
        'taxipath'                      => 'niix',
        'taxipathnode'                  => 'niiiffxxxxx',
        'totemcategory'                 => 'nsxsssxsxsxxxxxxxxiu',
        'vocaluisounds'                 => 'nxiiixx',
        'weaponimpactsounds'            => 'nixiiiiiiiiiiiiiiiiiiii',
        'weaponswingsounds2'            => 'nixi',
        'worldmaparea'                  => 'niisffffxix',         // 4.x - niisffffxixxxx
        'worldmapoverlay'               => 'niixxxxxsiiiixxxx',   // 4.x - niixxxsiiiixxxx
        'worldmaptransforms'            => 'niffffiffi',
        'worldstatezonesounds'          => 'iiiiiiix',
        'zoneintromusictable'           => 'nxixx',
        'zonemusic'                     => 'nxxxxxii'
    );

    private $_fields = array(
        'achievement'                   => 'id,faction,map,previous,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8,description_loc0,description_loc2,description_loc3,description_loc4,description_loc6,description_loc8,category,points,orderInGroup,flags,iconId,reward_loc0,reward_loc2,reward_loc3,reward_loc4,reward_loc6,reward_loc8,reqCriteriaCount,refAchievement',
        'achievement_category'          => 'id,parentCategory',
        'achievement_criteria'          => 'id,refAchievementId,type,value1,value2,value3,value4,value5,value6,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8,completionFlags,groupFlags,timeLimit,order',
        'areatable'                     => 'id,mapId,areaTable,flags,soundAmbience,zoneMusic,zoneIntroMusic,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8,factionGroupMask',
        'areatrigger'                   => 'id,mapId,posY,posX,orientation',
        'battlemasterlist'              => 'id,mapId,moreMapId,areaType,maxPlayers,minLevel,maxLevel',
        'charbaseinfo'                  => 'raceId,classId',
        'charstartoutfit'               => 'id,raceId,classId,gender,item1,item2,item3,item4,item5,item6,item7,item8,item9,item10,item11,item12,item13,item14,item15,item16,item17,item18,item19,item20',
        'chartitles'                    => 'id,male_loc0,male_loc2,male_loc3,male_loc4,male_loc6,male_loc8,female_loc0,female_loc2,female_loc3,female_loc4,female_loc6,female_loc8,bitIdx',
        'chrclasses'                    => 'id,powerType,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8,fileString,flags,expansion',
        'chrraces'                      => 'id,flags,factionId,baseLanguage,fileString,side,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8,expansion',
        'creaturedisplayinfo'           => 'id,modelId,creatureSoundId,extraInfoId,skin1,skin2,skin3,iconString,npcSoundId',
        'creaturedisplayinfoextra'      => 'id,textureString',
        'creaturefamily'                => 'id,skillLine1,petFoodMask,petTalentType,categoryEnumID,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8,iconString',
        'creaturemodeldata'             => 'id,creatureSoundId',
        'creaturesounddata'             => 'id,exertion,exertionCritical,injury,injuryCritical,death,stun,stand,footstepTerrainId,aggro,wingFlap,wingGlide,alert,fidget,customAttack,loop,jumpStart,jumpEnd,petAttack,petOrder,petDismiss,birth,spellcast,submerge,submerged',
        'currencytypes'                 => 'id,itemId,category',
        'declinedword'                  => 'id,word',
        'declinedwordcases'             => 'id,wordId,caseIdx,word',
        'dungeonmap'                    => 'id,mapId,floor,minY,maxY,minX,maxX,areaId',
        'durabilitycosts'               => 'id,w0,w1,w2,w3,w4,w5,w6,w7,w8,w10,w11,w12,w13,w14,w15,w16,w17,w18,w19,w20,a1,a2,a3,a4,a6',
        'durabilityquality'             => 'id,mod',
        'dungeonencounter'              => 'id,map,mode,order,bit,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8',
        'emotes'                        => 'id,name,animationId,flags,state,stateParam,soundId',
        'emotestext'                    => 'id,command,emoteId,etd0,etd1,etd2,etd4,etd6,etd8,etd9,etd12',
        'emotestextsound'               => 'id,emotesTextId,raceId,gender,soundId',
        'emotestextdata'                => 'id,text_loc0,text_loc2,text_loc3,text_loc4,text_loc6,text_loc8',
        'faction'                       => 'id,repIdx,baseRepRaceMask1,baseRepRaceMask2,baseRepRaceMask3,baseRepRaceMask4,baseRepClassMask1,baseRepClassMask2,baseRepClassMask3,baseRepClassMask4,baseRepValue1,baseRepValue2,baseRepValue3,baseRepValue4,repFlags1,parentFaction,spilloverRateIn,spilloverRateOut,spilloverMaxRank,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8',
        'factiontemplate'               => 'id,factionId,ourMask,friendlyMask,hostileMask,enemyFactionId1,enemyFactionId2,enemyFactionId3,enemyFactionId4,friendFactionId1,friendFactionId2,friendFactionId3,friendFactionId4',
        'gemproperties'                 => 'id,enchantmentId,colorMask',
        'glyphproperties'               => 'id,spellId,typeFlags,iconId',
        'gtchancetomeleecrit'           => 'chance',
        'gtchancetomeleecritbase'       => 'chance',
        'gtchancetospellcrit'           => 'chance',
        'gtchancetospellcritbase'       => 'chance',
        'gtcombatratings'               => 'ratio',
        'gtoctclasscombatratingscalar'  => 'idx,ratio',
        'gtoctregenhp'                  => 'ratio',
        'gtregenmpperspt'               => 'ratio',
        'gtregenhpperspt'               => 'ratio',
        'holidays'                      => 'id,looping,nameId,descriptionId,textureString,scheduleType',
        'holidaydescriptions'           => 'id,description_loc0,description_loc2,description_loc3,description_loc4,description_loc6,description_loc8',
        'holidaynames'                  => 'id,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8',
        'item'                          => 'id,classId,subClassId,soundOverride,material,displayInfoId,inventoryType,sheatheType',
        'itemdisplayinfo'               => 'id,leftModelName,rightModelName,inventoryIcon1,spellVisualId,groupSoundId',
        'itemgroupsounds'               => 'id,pickUpSoundId,dropDownSoundId',
        'itemextendedcost'              => 'id,reqHonorPoints,reqArenaPoints,reqArenaSlot,reqItemId1,reqItemId2,reqItemId3,reqItemId4,reqItemId5,itemCount1,itemCount2,itemCount3,itemCount4,itemCount5,reqPersonalRating',
        'itemlimitcategory'             => 'id,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8,count,isGem',
        'itemrandomproperties'          => 'id,nameINT,enchantId1,enchantId2,enchantId3,enchantId4,enchantId5,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8',
        'itemrandomsuffix'              => 'id,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8,nameINT,enchantId1,enchantId2,enchantId3,enchantId4,enchantId5,allocationPct1,allocationPct2,allocationPct3,allocationPct4,allocationPct5',
        'itemset'                       => 'id,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8,spellId1,spellId2,spellId3,spellId4,spellId5,spellId6,spellId7,spellId8,itemCount1,itemCount2,itemCount3,itemCount4,itemCount5,itemCount6,itemCount7,itemCount8,reqSkillId,reqSkillLevel',
        'itemsubclass'                  => 'class,subClass,weaponSize',
        'lfgdungeons'                   => 'id,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8,levelMin,levelMax,targetLevel,targetLevelMin,targetLevelMax,mapId,difficulty,type,faction,expansion,groupId',
        'lock'                          => 'id,type1,type2,type3,type4,type5,properties1,properties2,properties3,properties4,properties5,reqSkill1,reqSkill2,reqSkill3,reqSkill4,reqSkill5',
        'locktype'                      => 'id,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8,state_loc0,state_loc2,state_loc3,state_loc4,state_loc6,state_loc8,process_loc0,process_loc2,process_loc3,process_loc4,process_loc6,process_loc8,strref',
        'mailtemplate'                  => 'id,subject_loc0,subject_loc2,subject_loc3,subject_loc4,subject_loc6,subject_loc8,text_loc0,text_loc2,text_loc3,text_loc4,text_loc6,text_loc8',
        'map'                           => 'id,nameINT,areaType,isBG,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8,parentMapId,parentX,parentY,expansion,maxPlayers',
        'mapdifficulty'                 => 'id,mapId,difficulty,nPlayer,nPlayerString',
        'material'                      => 'id,sheatheSoundId,unsheatheSoundId',
        'npcsounds'                     => 'id,greetSoundId,byeSoundId,angrySoundId',
        'overridespelldata'             => 'id,spellId1,spellId2,spellId3,spellId4,spellId5',
        'powerdisplay'                  => 'id,realType,globalString,r,g,b',
        'questfactionreward'            => 'id,field1,field2,field3,field4,field5,field6,field7,field8,field9,field10',
        'questsort'                     => 'id,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8',
        'questxp'                       => 'id,field1,field2,field3,field4,field5,field6,field7,field8,field9,field10',
        'randproppoints'                => 'id,epic1,epic2,epic3,epic4,epic5,rare1,rare2,rare3,rare4,rare5,uncommon1,uncommon2,uncommon3,uncommon4,uncommon5',
        'scalingstatdistribution'       => 'id,statMod1,statMod2,statMod3,statMod4,statMod5,statMod6,statMod7,statMod8,statMod9,statMod10,modifier1,modifier2,modifier3,modifier4,modifier5,modifier6,modifier7,modifier8,modifier9,modifier10,maxLevel',
        'scalingstatvalues'             => 'id,shoulderMultiplier,trinketMultiplier,weaponMultiplier,rangedMultiplier,clothShoulderArmor,leatherShoulderArmor,mailShoulderArmor,plateShoulderArmor,weaponDPS1H,weaponDPS2H,casterDPS1H,casterDPS2H,rangedDPS,wandDPS,spellPower,primBudged,tertBudged,clothCloakArmor,clothChestArmor,leatherChestArmor,mailChestArmor,plateChestArmor',
        'screeneffect'                  => 'id,name,soundAmbienceId,zoneMusicId',
        'skillline'                     => 'id,categoryId,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8,description_loc0,description_loc2,description_loc3,description_loc4,description_loc6,description_loc8,iconId',
        'skilllineability'              => 'id,skillLineId,spellId,reqRaceMask,reqClassMask,reqSkillLevel,acquireMethod,skillLevelGrey,skillLevelYellow',
        'skilllinecategory'             => 'id,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8,index',
        'skillraceclassinfo'            => 'id,skillLine,raceMask,classMask,flags,reqLevel',
        'soundambience'                 => 'id,soundIdDay,soundIdNight',
        'soundemitters'                 => 'id,posY,posX,soundId,mapId',
        'soundentries'                  => 'id,type,name,file1,file2,file3,file4,file5,file6,file7,file8,file9,file10,path,flags',
        'spell'                         => 'id,category,dispelType,mechanic,attributes0,attributes1,attributes2,attributes3,attributes4,attributes5,attributes6,attributes7,stanceMask,stanceMaskNot,targets,spellFocus,castTimeId,recoveryTime,recoveryTimeCategory,procChance,procCharges,maxLevel,baseLevel,spellLevel,durationId,powerType,powerCost,powerCostPerLevel,powerPerSecond,powerPerSecondPerLevel,rangeId,stackAmount,tool1,tool2,reagent1,reagent2,reagent3,reagent4,reagent5,reagent6,reagent7,reagent8,reagentCount1,reagentCount2,reagentCount3,reagentCount4,reagentCount5,reagentCount6,reagentCount7,reagentCount8,equippedItemClass,equippedItemSubClassMask,equippedItemInventoryTypeMask,effect1Id,effect2Id,effect3Id,effect1DieSides,effect2DieSides,effect3DieSides,effect1RealPointsPerLevel,effect2RealPointsPerLevel,effect3RealPointsPerLevel,effect1BasePoints,effect2BasePoints,effect3BasePoints,effect1Mechanic,effect2Mechanic,effect3Mechanic,effect1ImplicitTargetA,effect2ImplicitTargetA,effect3ImplicitTargetA,effect1ImplicitTargetB,effect2ImplicitTargetB,effect3ImplicitTargetB,effect1RadiusId,effect2RadiusId,effect3RadiusId,effect1AuraId,effect2AuraId,effect3AuraId,effect1Periode,effect2Periode,effect3Periode,effect1ValueMultiplier,effect2ValueMultiplier,effect3ValueMultiplier,effect1ChainTarget,effect2ChainTarget,effect3ChainTarget,effect1CreateItemId,effect2CreateItemId,effect3CreateItemId,effect1MiscValue,effect2MiscValue,effect3MiscValue,effect1MiscValueB,effect2MiscValueB,effect3MiscValueB,effect1TriggerSpell,effect2TriggerSpell,effect3TriggerSpell,effect1PointsPerComboPoint,effect2PointsPerComboPoint,effect3PointsPerComboPoint,effect1SpellClassMaskA,effect2SpellClassMaskA,effect3SpellClassMaskA,effect1SpellClassMaskB,effect2SpellClassMaskB,effect3SpellClassMaskB,effect1SpellClassMaskC,effect2SpellClassMaskC,effect3SpellClassMaskC,spellVisualId1,spellVisualId2,iconId,iconIdActive,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8,rank_loc0,rank_loc2,rank_loc3,rank_loc4,rank_loc6,rank_loc8,description_loc0,description_loc2,description_loc3,description_loc4,description_loc6,description_loc8,buff_loc0,buff_loc2,buff_loc3,buff_loc4,buff_loc6,buff_loc8,powerCostPercent,startRecoveryCategory,startRecoveryTime,maxTargetLevel,spellFamilyId,spellFamilyFlags1,spellFamilyFlags2,spellFamilyFlags3,maxAffectedTargets,damageClass,effect1DamageMultiplier,effect2DamageMultiplier,effect3DamageMultiplier,toolCategory1,toolCategory2,schoolMask,runeCostId,powerDisplayId,effect1BonusMultiplier,effect2BonusMultiplier,effect3BonusMultiplier,spellDescriptionVariable,spellDifficulty',
        'spellcasttimes'                => 'id,baseTime',
        'spelldescriptionvariables'     => 'id,vars',
        'spellduration'                 => 'id,baseTime',
        'spelldifficulty'               => 'normal10,normal25,heroic10,heroic25',
        'spellfocusobject'              => 'id,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8',
        'spellicon'                     => 'id,iconPath',
        'spellitemenchantment'          => 'id,charges,type1,type2,type3,amount1,amount2,amount3,object1,object2,object3,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8,conditionId,skillLine,skillLevel,requiredLevel',
        'spellitemenchantmentcondition' => 'id,color1,color2,color3,color4,color5,comparator1,comparator2,comparator3,comparator4,comparator5,cmpColor1,cmpColor2,cmpColor3,cmpColor4,cmpColor5,value1,value2,value3,value4,value5',
        'spellradius'                   => 'id,radiusMin,radiusMax',
        'spellrange'                    => 'id,rangeMinHostile,rangeMinFriend,rangeMaxHostile,rangeMaxFriend,rangeType,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8',
        'spellrunecost'                 => 'id,costBlood,costUnholy,costFrost,runicPowerGain',
        'spellshapeshiftform'           => 'id,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8,flags,creatureType,displayIdA,displayIdH,spellId1,spellId2,spellId3,spellId4,spellId5,spellId6,spellId7,spellId8',
        'spellvisual'                   => 'id,precastKitId,castKitId,impactKitId,stateKitId,statedoneKitId,channelKitId,missileSoundId,animationSoundId,casterImpactKitId,targetImpactKitId,missileTargetingKitId,instantAreaKitId,impactAreaKitId,persistentAreaKitId',
        'spellvisualkit'                => 'id,soundId',
        'talent'                        => 'id,tabId,row,column,rank1,rank2,rank3,rank4,rank5,reqTalent,reqRank,talentSpell,petCategory1,petCategory2',
        'talenttab'                     => 'id,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8,iconId,raceMask,classMask,creatureFamilyMask,tabNumber,textureFile',
        'taxinodes'                     => 'id,mapId,posX,posY,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8',
        'taxipath'                      => 'id,startNodeId,endNodeId',
        'taxipathnode'                  => 'id,pathId,nodeIdx,mapId,posX,posY',
        'totemcategory'                 => 'id,name_loc0,name_loc2,name_loc3,name_loc4,name_loc6,name_loc8,category,categoryMask',
        'vocaluisounds'                 => 'id,raceId,soundIdMale,soundIdFemale',
        'weaponimpactsounds'            => 'id,subClass,hit1,hit2,hit3,hit4,hit5,hit6,hit7,hit8,hit9,hit10,crit1,crit2,crit3,crit4,crit5,crit6,crit7,crit8,crit9,crit10',
        'weaponswingsounds2'            => 'id,weaponSize,soundId',
        'worldmaparea'                  => 'id,mapId,areaId,nameINT,left,right,top,bottom,defaultDungeonMapId',
        'worldmapoverlay'               => 'id,worldMapAreaId,areaTableId,textureString,w,h,x,y',
        'worldmaptransforms'            => 'id,sourceMapId,minX,minY,maxX,maxY,targetMapId,offsetX,offsetY,dungeonMapId',
        'worldstatezonesounds'          => 'stateId,value,areaId,wmoAreaId,zoneIntroMusicId,zoneMusicId,soundAmbienceId',
        'zoneintromusictable'           => 'id,soundId',
        'zonemusic'                     => 'id,soundIdDay,soundIdNight'
    );

    private $isGameTable = false;
    private $localized   = false;
    private $tempTable   = true;
    private $tableName   = '';

    private $dataBuffer  = [];
    private $bufferSize  = 500;

    private $fileRefs    = [];

    public $error  = true;
    public $fields = [];
    public $format = '';
    public $file   = '';


    public function __construct($file, $opts = [])
    {
        $file = strtolower($file);
        if (empty($this->_fields[$file]) || empty($this->_formats[$file]))
        {
            CLI::write('no structure known for '.$file.'.dbc, aborting.', CLI::LOG_ERROR);
            CLI::write();
            return;
        }

        if (!DB::isConnected(DB_AOWOW))
        {
            CLI::write('not connected to db, aborting.', CLI::LOG_ERROR);
            CLI::write();
            return;
        }

        $this->fields    = explode(',', $this->_fields[$file]);
        $this->format    = $this->_formats[$file];
        $this->file      = $file;
        $this->localized = !!strstr($this->format, 'sxsssxsxsxxxxxxxx');

        if (count($this->fields) != strlen(str_ireplace('x', '', $this->format)))
        {
            CLI::write('known field types ['.count($this->fields).'] and names ['.strlen(str_ireplace('x', '', $this->format)).'] do not match for '.$file.'.dbc, aborting.', CLI::LOG_ERROR);
            CLI::write();
            return;
        }

        if (is_bool($opts['temporary']))
            $this->tempTable = $opts['temporary'];

        if (!empty($opts['tableName']))
            $this->tableName = $opts['tableName'];
        else
            $this->tableName = 'dbc_'.$file;

        // gameTable-DBCs don't have an index and are accessed through value order
        // allas, you cannot do this with mysql, so we add a 'virtual' index
        $this->isGameTable = $this->format == 'f' && substr($file, 0, 2) == 'gt';

        $foundMask = 0x0;
        foreach (CLISetup::$expectedPaths as $locStr => $locId)
        {
            if (!in_array($locId, CLISetup::$localeIds))
                continue;

            if ($foundMask & (1 << $locId))
                continue;

            $fullPath = CLI::nicePath($this->file.'.dbc', CLISetup::$srcDir, $locStr, 'DBFilesClient');
            if (!CLISetup::fileExists($fullPath))
                continue;

            $this->curFile = $fullPath;
            if ($this->validateFile($locId))
                $foundMask |= (1 << $locId);
        }

        if (!$this->fileRefs)
        {
            CLI::write('no suitable files found for '.$file.'.dbc, aborting.', CLI::LOG_ERROR);
            CLI::write();
            return;
        }

        // check if DBCs are identical
        $headers = array_column($this->fileRefs, 2);
        $x = array_unique(array_column($headers, 'recordCount'));
        if (count($x) != 1)
        {
            CLI::write('some DBCs have differenct record counts ('.implode(', ', $x).' respectively). cannot merge!', CLI::LOG_ERROR);
            CLI::write();
            return;
        }
        $x = array_unique(array_column($headers, 'fieldCount'));
        if (count($x) != 1)
        {
            CLI::write('some DBCs have differenct field counts ('.implode(', ', $x).' respectively). cannot merge!', CLI::LOG_ERROR);
            CLI::write();
            return;
        }
        $x = array_unique(array_column($headers, 'recordSize'));
        if (count($x) != 1)
        {
            CLI::write('some DBCs have differenct record sizes ('.implode(', ', $x).' respectively). cannot merge!', CLI::LOG_ERROR);
            CLI::write();
            return;
        }

        $this->error = false;
    }

    public function readFile()
    {
        if (!$this->file || $this->error)
            return [];

        $this->createTable();

        if ($this->localized)
            CLI::write(' - reading and merging '.$this->file.'.dbc for locales '.implode(', ', array_keys($this->fileRefs)));
        else
            CLI::write(' - reading '.$this->file.'.dbc');

        if (!$this->read())
        {
            CLI::write(' - DBC::read() returned with error', CLI::LOG_ERROR);
            return false;
        }

        return true;
    }

    private function endClean()
    {
        foreach ($this->fileRefs as &$ref)
            fclose($ref[0]);

        $this->dataBuffer = null;
    }

    private function readHeader(&$handle = null)
    {
        if (!is_resource($handle))
            $handle = fopen($this->curFile, 'rb');

        if (!$handle)
            return false;

        if (fread($handle, 4) != 'WDBC')
        {
            CLI::write('file '.$this->curFile.' has incorrect magic bytes', CLI::LOG_ERROR);
            fclose($handle);
            return false;
        }

        return unpack('VrecordCount/VfieldCount/VrecordSize/VstringSize', fread($handle, 16));
    }

    private function validateFile($locId)
    {
        $filesize = filesize($this->curFile);
        if ($filesize < 20)
        {
            CLI::write('file '.$this->curFile.' is too small for a DBC file', CLI::LOG_ERROR);
            return false;
        }

        $header = $this->readHeader($handle);
        if (!$header)
        {
            CLI::write('cannot open file '.$this->curFile, CLI::LOG_ERROR);
            return false;
        }

        // Different debug checks to be sure, that file was opened correctly
        $debugStr = '(recordCount='.$header['recordCount'].
                    ' fieldCount=' .$header['fieldCount'] .
                    ' recordSize=' .$header['recordSize'] .
                    ' stringSize=' .$header['stringSize'] .')';

        if ($header['recordCount'] * $header['recordSize'] + $header['stringSize'] + 20 != $filesize)
        {
            CLI::write('file '.$this->curFile.' has incorrect size '.$filesize.': '.$debugStr, CLI::LOG_ERROR);
            fclose($handle);
            return false;
        }

        if ($header['fieldCount'] != strlen($this->format))
        {
            CLI::write('incorrect format string ('.$this->format.') specified for file '.$this->curFile.' fieldCount='.$header['fieldCount'], CLI::LOG_ERROR);
            fclose($handle);
            return false;
        }

        $this->fileRefs[$locId] = [$handle, $this->curFile, $header];

        return true;
    }

    private function createTable()
    {
        if ($this->error)
            return;

        $n     = 0;
        $pKey  = '';
        $query = 'CREATE '.($this->tempTable ? 'TEMPORARY' : '').' TABLE `'.$this->tableName.'` (';

        if ($this->isGameTable)
        {
            $query .= '`idx` BIGINT(20) NOT NULL, ';
            $pKey   = 'idx';
        }

        foreach (str_split($this->format) as $idx => $f)
        {
            switch ($f)
            {
                case 'f':
                    $query .= '`'.$this->fields[$n].'` FLOAT NOT NULL, ';
                    break;
                case 's':
                    $query .= '`'.$this->fields[$n].'` TEXT NOT NULL, ';
                    break;
                case 'i':
                case 'n':
                case 'b':
                case 'u':
                    $query .= '`'.$this->fields[$n].'` BIGINT(20) NOT NULL, ';
                    break;
                default:                                    // 'x', 'X', 'd'
                    continue 2;
            }

            if ($f == 'n')
                $pKey = $this->fields[$n];

            $n++;
        }

        if ($pKey)
            $query .= 'PRIMARY KEY (`'.$pKey.'`) ';
        else
            $query = substr($query, 0, -2);

        $query .=  ') COLLATE=\'utf8mb4_unicode_ci\' ENGINE=MyISAM';

        DB::Aowow()->query('DROP TABLE IF EXISTS ?#', $this->tableName);
        DB::Aowow()->query($query);
    }

    private function writeToDB()
    {
        if (!$this->dataBuffer || $this->error)
            return;

        // make inserts more manageable
        $fields = $this->fields;

        if ($this->isGameTable)
            array_unshift($fields, 'idx');

        DB::Aowow()->query('INSERT INTO ?# (?#) VALUES (?a)', $this->tableName, $fields, $this->dataBuffer);
        $this->dataBuffer = [];
    }

    private function read()
    {
        // l -   signed long (always 32 bit, machine byte order)
        // V - unsigned long (always 32 bit, little endian byte order)
        $unpackStr = '';
        $unpackFmt = array(
            'x' => 'x/x/x/x',
            'X' => 'x',
            's' => 'V',
            'f' => 'f',
            'i' => 'l',                                     // not sure if 'l' or 'V' should be used here
            'u' => 'V',
            'b' => 'C',
            'd' => 'x4',
            'n' => 'V'
        );

        // Check that record size also matches
        $recSize = 0;
        for ($i = 0; $i < strlen($this->format); $i++)
        {
            $ch = $this->format[$i];
            if ($ch == 'X' || $ch == 'b')
                $recSize += 1;
            else
                $recSize += 4;

            if (!isset($unpackFmt[$ch]))
            {
                CLI::write('unknown format parameter \''.$ch.'\' in format string', CLI::LOG_ERROR);
                return false;
            }

            $unpackStr .= '/'.$unpackFmt[$ch];

            if ($ch != 'X' && $ch != 'x')
                $unpackStr .= 'f'.$i;
        }

        $unpackStr = substr($unpackStr, 1);

        // Optimizing unpack string: 'x/x/x/x/x/x' => 'x6'
        while (preg_match('/(x\/)+x/', $unpackStr, $r))
            $unpackStr = substr_replace($unpackStr, 'x'.((strlen($r[0]) + 1) / 2), strpos($unpackStr, $r[0]), strlen($r[0]));


        // we asserted all DBCs to be identical in structure. pick first header for checks
        $header = reset($this->fileRefs)[2];

        if ($recSize != $header['recordSize'])
        {
            CLI::write('format string size ('.$recSize.') for file '.$this->file.' does not match actual size ('.$header['recordSize'].')', CLI::LOG_ERROR);
            return false;
        }

        // And, finally, extract the records
        $strings  = [];
        $rSize    = $header['recordSize'];
        $rCount   = $header['recordCount'];
        $fCount   = strlen($this->format);
        $strBlock = 4 + 16 + $header['recordSize'] * $header['recordCount'];

        for ($i = 0; $i < $rCount; $i++)
        {
            $row = [];
            $idx = $i;

            // add 'virtual' enumerator for gt*-dbcs
            if ($this->isGameTable)
                $row[-1] = $i;

            foreach ($this->fileRefs as $locId => [$handle, $fullPath, $header])
            {
                $rec = unpack($unpackStr, fread($handle, $header['recordSize']));

                $n = -1;
                for ($j = 0; $j < $fCount; $j++)
                {
                    if (!isset($rec['f'.$j]))
                        continue;

                    if (!empty($row[$j]))
                        continue;

                    $n++;

                    switch ($this->format[$j])
                    {
                        case 's':
                            $curPos = ftell($handle);
                            fseek($handle, $strBlock + $rec['f'.$j]);

                            $str = $chr = '';
                            do
                            {
                                $str .= $chr;
                                $chr = fread($handle, 1);
                            }
                            while ($chr != "\000");

                            fseek($handle, $curPos);
                            $row[$j] = $str;
                            break;
                        case 'f':
                            $row[$j] = round($rec['f'.$j], 8);
                            break;
                        case 'n':                               // DO NOT BREAK!
                            $idx = $rec['f'.$j];
                        default:                                // nothing special .. 'i', 'u' and the likes
                            $row[$j] = $rec['f'.$j];
                    }
                }

                if (!$this->localized)                          // one match is enough
                    break;
            }

            $this->dataBuffer[$idx] = array_values($row);

            if (count($this->dataBuffer) >= $this->bufferSize)
                $this->writeToDB();
        }

        $this->writeToDB();

        $this->endCLean();

        return true;
    }
}

?>
