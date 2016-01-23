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
    private $_formats = array(                                  // locales block for copy pasta: sxssxxsxsxxxxxxxx | xxxxxxxxxxxxxxxxx
        'achievement'                   => 'niiisxssxxsxsxxxxxxxxsxssxxsxsxxxxxxxxiiiiisxssxxsxsxxxxxxxxii',
        'achievement_category'          => 'nisxssxxsxsxxxxxxxxx',
        'achievement_criteria'          => 'niiiiiiiisxssxxsxsxxxxxxxxiixii',
        'areatable'                     => 'niixixxxxxxsxssxxsxsxxxxxxxxixxxxxxx',
        'battlemasterlist'              => 'niixxxxxxixxxxxxxxxxxxxxxxxxixii',
        'charbaseinfo'                  => 'bb',
        'charstartoutfit'               => 'nbbbXiiiiiiiiiiiiiiiiiiiixxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'chartitles'                    => 'nxsxssxxsxsxxxxxxxxsxssxxsxsxxxxxxxxi',
        'chrclasses'                    => 'nxixsxssxxsxsxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxsxixi',
        'chrraces'                      => 'niixxxxixxxsxisxssxxsxsxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxi',
        'creaturedisplayinfo'           => 'nixixxssssxxxxxx',
        'creaturedisplayinfoextra'      => 'nxxxxxxxxxxxxxxxxxxxs',
        'creaturefamily'                => 'nxxxxixiiisxssxxsxsxxxxxxxxs',
        'currencytypes'                 => 'niix',
        'dungeonmap'                    => 'niiffffi',
        'durabilitycosts'               => 'niiiiiiiiixiiiiiiiiiiixiiiixix',
        'durabilityquality'             => 'nf',
        'emotes'                        => 'nxixxxx',
        'emotestext'                    => 'nsiixxxixixxxxxxxxx',
        'emotestextdata'                => 'nsxssxxsxsxxxxxxxx',
        'faction'                       => 'nixxxxxxxxxxxxixxxiffixsxssxxsxsxxxxxxxxxxxxxxxxxxxxxxxxx',
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
        'holidaydescriptions'           => 'nsxssxxsxsxxxxxxxx',
        'holidaynames'                  => 'nsxssxxsxsxxxxxxxx',
        'itemdisplayinfo'               => 'nssxxsxxxxxxxxxxxxxxxxxxx',
        'itemextendedcost'              => 'niiiiiiiiiiiiiix',
        'itemlimitcategory'             => 'nsxssxxsxsxxxxxxxxii',
        'itemrandomproperties'          => 'nsiiiiisxssxxsxsxxxxxxxx',
        'itemrandomsuffix'              => 'nsxssxxsxsxxxxxxxxsiiiiiiiiii',
        'itemset'                       => 'nsxssxxsxsxxxxxxxxxxxxxxxxxxxxxxxxxiiiiiiiiiiiiiiiiii',
        'lfgdungeons'                   => 'nsxssxxsxsxxxxxxxxiiiiiiixiixixixxxxxxxxxxxxxxxxx',
        'lock'                          => 'niiiiixxxiiiiixxxiiiiixxxxxxxxxxx',
        'mailtemplate'                  => 'nsxssxxsxsxxxxxxxxsxssxxsxsxxxxxxxx',
        'map'                           => 'nsixisxssxxsxsxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxiffxixi',
        'mapdifficulty'                 => 'niixxxxxxxxxxxxxxxxxxis',
        'powerdisplay'                  => 'nisbbb',
        'questfactionreward'            => 'niiiiiiiiii',
        'questxp'                       => 'niiiiiiiiii',
        'randproppoints'                => 'niiiiiiiiiiiiiii',
        'scalingstatdistribution'       => 'niiiiiiiiiiiiiiiiiiiii',
        'scalingstatvalues'             => 'xniiiiiiiiiiiiiiiiiiiiii',
        'skillline'                     => 'nixsxssxxsxsxxxxxxxxsxssxxsxsxxxxxxxxixxxxxxxxxxxxxxxxxx',
        'skilllineability'              => 'niiiixxixiiixx',
        'skillraceclassinfo'            => 'niiiiixx',
        'spell'                         => 'niiiuuuuuuuuixixxxixxxxxxxxxiiixxxxiiiiiiiiiiiixxiiiiiiiiiiiiiiiiiiiiiiiiiiiifffiiiiiiiiiiiiiiiiiiiiifffiiiiiiiiiiiiiiifffiiiiiiiiixxiixsxssxxsxsxxxxxxxxsxssxxsxsxxxxxxxxsxssxxsxsxxxxxxxxsxssxxsxsxxxxxxxxiiiiiiiiiixxfffxxxiixiixifffii',
        'spellcasttimes'                => 'nixx',
        'spelldescriptionvariables'     => 'ns',
        'spelldifficulty'               => 'xiiii',
        'spellduration'                 => 'nixx',
        'spellfocusobject'              => 'nsxssxxsxsxxxxxxxx',
        'spellicon'                     => 'ns',
        'spellitemenchantment'          => 'niiiiiiixxxiiisxssxxsxsxxxxxxxxxxxiiii',
        'spellitemenchantmentcondition' => 'nbbbbbxxxxxbbbbbbbbbbiiiiiXXXXX',
        'spellradius'                   => 'nfxf',
        'spellrange'                    => 'nffffisxssxxsxsxxxxxxxxxxxxxxxxxxxxxxxxx',
        'spellrunecost'                 => 'niiii',
        'spellshapeshiftform'           => 'nxsxssxxsxsxxxxxxxxiixxiixxiiiiiiii',
        'talent'                        => 'niiiiiiiixxxxixxixxixii',
        'talenttab'                     => 'nsxssxxsxsxxxxxxxxiiiiis',
        'taxinodes'                     => 'niffxsxssxxsxsxxxxxxxxxx',
        'taxipath'                      => 'niix',
        'taxipathnode'                  => 'niiiffxxxxx',
        'totemcategory'                 => 'nsxssxxsxsxxxxxxxxiu',
        'worldmaparea'                  => 'niisffffxix',         // 4.x - niisffffxixxxx
        'worldmapoverlay'               => 'niixxxxxsiiiixxxx',   // 4.x - niixxxsiiiixxxx
        'worldmaptransforms'            => 'niffffiffi',
    );


    private $_fields = array(
        'achievement'                   => 'Id,faction,map,previous,name_loc0,name_loc2,name_loc3,name_loc6,name_loc8,description_loc0,description_loc2,description_loc3,description_loc6,description_loc8,category,points,orderInGroup,flags,iconId,reward_loc0,reward_loc2,reward_loc3,reward_loc6,reward_loc8,reqCriteriaCount,refAchievement',
        'achievement_category'          => 'Id,parentCategory,name_loc0,name_loc2,name_loc3,name_loc6,name_loc8',
        'achievement_criteria'          => 'Id,refAchievementId,type,value1,value2,value3,value4,value5,value6,name_loc0,name_loc2,name_loc3,name_loc6,name_loc8,completionFlags,groupFlags,timeLimit,order',
        'areatable'                     => 'Id,mapId,areaTable,flags,name_loc0,name_loc2,name_loc3,name_loc6,name_loc8,factionGroupMask',
        'battlemasterlist'              => 'Id,mapId,moreMapId,areaType,maxPlayers,minLevel,maxLevel',
        'charbaseinfo'                  => 'raceId,classId',
        'charstartoutfit'               => 'Id,raceId,classId,gender,item1,item2,item3,item4,item5,item6,item7,item8,item9,item10,item11,item12,item13,item14,item15,item16,item17,item18,item19,item20',
        'chartitles'                    => 'Id,male_loc0,male_loc2,male_loc3,male_loc6,male_loc8,female_loc0,female_loc2,female_loc3,female_loc6,female_loc8,bitIdx',
        'chrclasses'                    => 'Id,powerType,name_loc0,name_loc2,name_loc3,name_loc6,name_loc8,fileString,flags,expansion',
        'chrraces'                      => 'Id,flags,factionId,baseLanguage,fileString,side,name_loc0,name_loc2,name_loc3,name_loc6,name_loc8,expansion',
        'creaturedisplayinfo'           => 'Id,modelid,extraInfoId,skin1,skin2,skin3,iconString',
        'creaturedisplayinfoextra'      => 'Id,textureString',
        'creaturefamily'                => 'Id,skillLine1,petFoodMask,petTalentType,categoryEnumID,name_loc0,name_loc2,name_loc3,name_lo6,name_loc8,iconString',
        'currencytypes'                 => 'Id,itemId,category',
        'dungeonmap'                    => 'Id,mapId,floor,minY,maxY,minX,maxX,areaId',
        'durabilitycosts'               => 'Id,w0,w1,w2,w3,w4,w5,w6,w7,w8,w10,w11,w12,w13,w14,w15,w16,w17,w18,w19,w20,a1,a2,a3,a4,a6',
        'durabilityquality'             => 'Id,mod',
        'emotes'                        => 'Id,animationId',
        'emotestext'                    => 'Id,command,emoteId,targetId,noTargetId,selfId',
        'emotestextdata'                => 'Id,text_loc0,text_loc2,text_loc3,text_loc6,text_loc8',
        'faction'                       => 'Id,repIdx,repFlags1,parentFaction,spilloverRateIn,spilloverRateOut,spilloverMaxRank,name_loc0,name_loc2,name_loc3,name_loc6,name_loc8',
        'factiontemplate'               => 'Id,factionId,ourMask,friendlyMask,hostileMask,enemyFactionId1,enemyFactionId2,enemyFactionId3,enemyFactionId4,friendFactionId1,friendFactionId2,friendFactionId3,friendFactionId4',
        'gemproperties'                 => 'Id,enchantmentId,colorMask',
        'glyphproperties'               => 'Id,spellId,typeFlags,iconId',
        'gtchancetomeleecrit'           => 'chance',
        'gtchancetomeleecritbase'       => 'chance',
        'gtchancetospellcrit'           => 'chance',
        'gtchancetospellcritbase'       => 'chance',
        'gtcombatratings'               => 'ratio',
        'gtoctclasscombatratingscalar'  => 'idx,ratio',
        'gtoctregenhp'                  => 'ratio',
        'gtregenmpperspt'               => 'ratio',
        'gtregenhpperspt'               => 'ratio',
        'holidays'                      => 'Id,looping,nameId,descriptionId,textureString,scheduleType',
        'holidaydescriptions'           => 'Id,description_loc0,description_loc2,description_loc3,description_loc6,description_loc8',
        'holidaynames'                  => 'Id,name_loc0,name_loc2,name_loc3,name_loc6,name_loc8',
        'itemdisplayinfo'               => 'Id,leftModelName,rightModelName,inventoryIcon1',
        'itemextendedcost'              => 'Id,reqHonorPoints,reqArenaPoints,reqArenaSlot,reqItemId1,reqItemId2,reqItemId3,reqItemId4,reqItemId5,itemCount1,itemCount2,itemCount3,itemCount4,itemCount5,reqPersonalRating',
        'itemlimitcategory'             => 'Id,name_loc0,name_loc2,name_loc3,name_loc6,name_loc8,count,isGem',
        'itemrandomproperties'          => 'Id,nameINT,enchantId1,enchantId2,enchantId3,enchantId4,enchantId5,name_loc0,name_loc2,name_loc3,name_loc6,name_loc8',
        'itemrandomsuffix'              => 'Id,name_loc0,name_loc2,name_loc3,name_loc6,name_loc8,nameINT,enchantId1,enchantId2,enchantId3,enchantId4,enchantId5,allocationPct1,allocationPct2,allocationPct3,allocationPct4,allocationPct5',
        'itemset'                       => 'Id,name_loc0,name_loc2,name_loc3,name_loc6,name_loc8,spellId1,spellId2,spellId3,spellId4,spellId5,spellId6,spellId7,spellId8,itemCount1,itemCount2,itemCount3,itemCount4,itemCount5,itemCount6,itemCount7,itemCount8,reqSkillId,reqSkillLevel',
        'lfgdungeons'                   => 'Id,name_loc0,name_loc2,name_loc3,name_loc6,name_loc8,levelMin,levelMax,targetLevel,targetLevelMin,targetLevelMax,mapId,difficulty,type,faction,expansion,groupId',
        'lock'                          => 'Id,type1,type2,type3,type4,type5,properties1,properties2,properties3,properties4,properties5,reqSkill1,reqSkill2,reqSkill3,reqSkill4,reqSkill5',
        'mailtemplate'                  => 'Id,subject_loc0,subject_loc2,subject_loc3,subject_loc6,subject_loc8,text_loc0,text_loc2,text_loc3,text_loc6,text_loc8',
        'map'                           => 'Id,nameINT,areaType,isBG,name_loc0,name_loc2,name_loc3,name_loc6,name_loc8,parentMapId,parentX,parentY,expansion,maxPlayers',
        'mapdifficulty'                 => 'Id,mapId,difficulty,nPlayer,nPlayerString',
        'powerdisplay'                  => 'Id,realType,globalString,r,g,b',
        'questfactionreward'            => 'Id,field1,field2,field3,field4,field5,field6,field7,field8,field9,field10',
        'questxp'                       => 'Id,field1,field2,field3,field4,field5,field6,field7,field8,field9,field10',
        'randproppoints'                => 'Id,epic1,epic2,epic3,epic4,epic5,rare1,rare2,rare3,rare4,rare5,uncommon1,uncommon2,uncommon3,uncommon4,uncommon5',
        'scalingstatdistribution'       => 'Id,statMod1,statMod2,statMod3,statMod4,statMod5,statMod6,statMod7,statMod8,statMod9,statMod10,modifier1,modifier2,modifier3,modifier4,modifier5,modifier6,modifier7,modifier8,modifier9,modifier10,maxLevel',
        'scalingstatvalues'             => 'Id,shoulderMultiplier,trinketMultiplier,weaponMultiplier,rangedMultiplier,clothShoulderArmor,leatherShoulderArmor,mailShoulderArmor,plateShoulderArmor,weaponDPS1H,weaponDPS2H,casterDPS1H,casterDPS2H,rangedDPS,wandDPS,spellPower,primBudged,tertBudged,clothCloakArmor,clothChestArmor,leatherChestArmor,mailChestArmor,plateChestArmor',
        'skillline'                     => 'Id,categoryId,name_loc0,name_loc2,name_loc3,name_loc6,name_loc8,description_loc0,description_loc2,description_loc3,description_loc6,description_loc8,iconId',
        'skilllineability'              => 'Id,skillLineId,spellId,reqRaceMask,reqClassMask,reqSkillLevel,acquireMethod,skillLevelGrey,skillLevelYellow',
        'skillraceclassinfo'            => 'Id,skillLine,raceMask,classMask,flags,reqLevel',
        'spell'                         => 'Id,category,dispelType,mechanic,attributes0,attributes1,attributes2,attributes3,attributes4,attributes5,attributes6,attributes7,stanceMask,stanceMaskNot,spellFocus,castTimeId,recoveryTime,recoveryTimeCategory,procChance,procCharges,maxLevel,baseLevel,spellLevel,durationId,powerType,powerCost,powerCostPerLevel,powerPerSecond,powerPerSecondPerLevel,rangeId,stackAmount,tool1,tool2,reagent1,reagent2,reagent3,reagent4,reagent5,reagent6,reagent7,reagent8,reagentCount1,reagentCount2,reagentCount3,reagentCount4,reagentCount5,reagentCount6,reagentCount7,reagentCount8,equippedItemClass,equippedItemSubClassMask,equippedItemInventoryTypeMask,effect1Id,effect2Id,effect3Id,effect1DieSides,effect2DieSides,effect3DieSides,effect1RealPointsPerLevel,effect2RealPointsPerLevel,effect3RealPointsPerLevel,effect1BasePoints,effect2BasePoints,effect3BasePoints,effect1Mechanic,effect2Mechanic,effect3Mechanic,effect1ImplicitTargetA,effect2ImplicitTargetA,effect3ImplicitTargetA,effect1ImplicitTargetB,effect2ImplicitTargetB,effect3ImplicitTargetB,effect1RadiusId,effect2RadiusId,effect3RadiusId,effect1AuraId,effect2AuraId,effect3AuraId,effect1Periode,effect2Periode,effect3Periode,effect1ValueMultiplier,effect2ValueMultiplier,effect3ValueMultiplier,effect1ChainTarget,effect2ChainTarget,effect3ChainTarget,effect1CreateItemId,effect2CreateItemId,effect3CreateItemId,effect1MiscValue,effect2MiscValue,effect3MiscValue,effect1MiscValueB,effect2MiscValueB,effect3MiscValueB,effect1TriggerSpell,effect2TriggerSpell,effect3TriggerSpell,effect1PointsPerComboPoint,effect2PointsPerComboPoint,effect3PointsPerComboPoint,effect1SpellClassMaskA,effect2SpellClassMaskA,effect3SpellClassMaskA,effect1SpellClassMaskB,effect2SpellClassMaskB,effect3SpellClassMaskB,effect1SpellClassMaskC,effect2SpellClassMaskC,effect3SpellClassMaskC,iconId,iconIdActive,name_loc0,name_loc2,name_loc3,name_loc6,name_loc8,rank_loc0,rank_loc2,rank_loc3,rank_loc6,rank_loc8,description_loc0,description_loc2,description_loc3,description_loc6,description_loc8,buff_loc0,buff_loc2,buff_loc3,buff_loc6,buff_loc8,powerCostPercent,startRecoveryCategory,startRecoveryTime,maxTargetLevel,spellFamilyId,spellFamilyFlags1,spellFamilyFlags2,spellFamilyFlags3,maxAffectedTargets,damageClass,effect1DamageMultiplier,effect2DamageMultiplier,effect3DamageMultiplier,toolCategory1,toolCategory2,schoolMask,runeCostId,powerDisplayId,effect1BonusMultiplier,effect2BonusMultiplier,effect3BonusMultiplier,spellDescriptionVariable,spellDifficulty',
        'spellcasttimes'                => 'Id,baseTime',
        'spelldescriptionvariables'     => 'Id,vars',
        'spellduration'                 => 'Id,baseTime',
        'spelldifficulty'               => 'normal10,normal25,heroic10,heroic25',
        'spellfocusobject'              => 'Id,name_loc0,name_loc2,name_loc3,name_loc6,name_loc8',
        'spellicon'                     => 'Id,iconPath',
        'spellitemenchantment'          => 'Id,charges,type1,type2,type3,amount1,amount2,amount3,object1,object2,object3,name_loc0,name_loc2,name_loc3,name_loc6,name_loc8,conditionId,skillLine,skillLevel,requiredLevel',
        'spellitemenchantmentcondition' => 'Id,color1,color2,color3,color4,color5,comparator1,comparator2,comparator3,comparator4,comparator5,cmpColor1,cmpColor2,cmpColor3,cmpColor4,cmpColor5,value1,value2,value3,value4,value5',
        'spellradius'                   => 'Id,radiusMin,radiusMax',
        'spellrange'                    => 'Id,rangeMinHostile,rangeMinFriend,rangeMaxHostile,rangeMaxFriend,rangeType,name_loc0,name_loc2,name_loc3,name_loc6,name_loc8',
        'spellrunecost'                 => 'Id,costBlood,costUnholy,costFrost,runicPowerGain',
        'spellshapeshiftform'           => 'Id,name_loc0,name_loc2,name_loc3,name_loc6,name_loc8,flags,creatureType,displayIdA,displayIdH,spellId1,spellId2,spellId3,spellId4,spellId5,spellId6,spellId7,spellId8',
        'talent'                        => 'Id,tabId,row,column,rank1,rank2,rank3,rank4,rank5,reqTalent,reqRank,talentSpell,petCategory1,petCategory2',
        'talenttab'                     => 'Id,name_loc0,name_loc2,name_loc3,name_loc6,name_loc8,iconId,raceMask,classMask,creatureFamilyMask,tabNumber,textureFile',
        'taxinodes'                     => 'Id,mapId,posX,posY,name_loc0,name_loc2,name_loc3,name_loc6,name_loc8',
        'taxipath'                      => 'Id,startNodeId,endNodeId',
        'taxipathnode'                  => 'Id,pathId,nodeIdx,mapId,posX,posY',
        'totemcategory'                 => 'Id,name_loc0,name_loc2,name_loc3,name_loc6,name_loc8,category,categoryMask',
        'worldmaparea'                  => 'Id,mapId,areaId,nameINT,left,right,top,bottom,defaultDungeonMapId',
        'worldmapoverlay'               => 'Id,worldMapAreaId,areaTableId,textureString,w,h,x,y',
        'worldmaptransforms'            => 'Id,sourceMapId,minX,minY,maxX,maxY,targetMapId,offsetX,offsetY,dungeonMapId',
    );

    private $isGameTable = false;
    private $localized   = false;
    private $tempTable   = true;

    public $error  = true;
    public $result = [];
    public $fields = [];
    public $format = '';
    public $file   = '';

    public function __construct($file, $tmpTbl = null)
    {
        $file = strtolower($file);
        if (empty($this->_fields[$file]) || empty($this->_formats[$file]))
        {
            CLISetup::log('no structure known for '.$file.'.dbc, aborting.', CLISetup::LOG_ERROR);
            return;
        }

        $this->fields    = explode(',', $this->_fields[$file]);
        $this->format    = $this->_formats[$file];
        $this->file      = $file;
        $this->localized = !!strstr($this->format, 'sxssxxsxsxxxxxxxx');

        if (is_bool($tmpTbl))
            $this->tempTable = $tmpTbl;

        if (count($this->fields) != strlen(str_ireplace('x', '', $this->format)))
        {
            CLISetup::log('known field types ['.count($this->fields).'] and names ['.strlen(str_ireplace('x', '', $this->format)).'] do not match for '.$file.'.dbc, aborting.', CLISetup::LOG_ERROR);
            return;
        }

        // gameTable-DBCs don't have an index and are accessed through value order
        // allas, you cannot do this with mysql, so we add a 'virtual' index
        $this->isGameTable = $this->format == 'f' && substr($file, 0, 2) == 'gt';
        $this->error = false;
    }

    public function writeToDB()
    {
        if (!$this->result || $this->error)
            return false;

        $n     = 0;
        $pKey  = '';
        $query = 'CREATE '.($this->tempTable ? 'TEMPORARY' : '').' TABLE `dbc_'.$this->file.'` (';

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

        $query .=  ') COLLATE=\'utf8_general_ci\' ENGINE=MyISAM';

        DB::Aowow()->query('DROP TABLE IF EXISTS ?#', 'dbc_'.$this->file);
        DB::Aowow()->query($query);

        // make inserts more manageable
        $offset = 0;
        $limit  = 1000;
        $fields = $this->fields;

        if ($this->isGameTable)
            array_unshift($fields, 'idx');

        while (($offset * $limit) < count($this->result))
            DB::Aowow()->query('INSERT INTO ?# (?#) VALUES (?a)', 'dbc_'.$this->file, $fields, array_slice($this->result, $offset++ * $limit, $limit));

        return true;
    }

    public function readFiltered(Closure $filterFunc = null, $doSave = true)
    {
        $result = $this->readArbitrary($doSave);

        if (is_object($filterFunc))
            foreach ($result as $key => &$val)
                if (!$filterFunc($val, $key))
                    unset($result[$key]);

        return $result;
    }

    public function readArbitrary($doSave = true)
    {
        if ($this->error)
            return [];

        // try DB first
        if (!$this->result)
            $this->readFromDB();

        // try file second
        if (!$this->result)
            if ($this->readFromFile() && $doSave)
                $this->writeToDB();

        return $this->getIndexed();
    }

    public function readFromDB()
    {
        if ($this->error)
            return [];

        if (!DB::Aowow()->selectCell('SHOW TABLES LIKE ?', 'dbc_'.$this->file))
            return [];

        $key = strstr($this->format, 'n') ? $this->fields[strpos($this->format, 'n')] : '';

        $this->result = DB::Aowow()->select('SELECT '.($key ? 'tbl.`'.$key.'` AS ARRAY_KEY, ' : '').'tbl.* FROM ?# tbl', 'dbc_'.$this->file);

        return $this->result;
    }

    public function readFromFile()
    {
        if (!$this->file || $this->error)
            return [];

        $foundMask = 0x0;
        foreach (CLISetup::$expectedPaths as $locStr => $locId)
        {
            if (!in_array($locId, CLISetup::$localeIds))
                continue;

            if ($foundMask & (1 << $locId))
                continue;

            $fullpath = CLISetup::$srcDir.($locStr ? $locStr.'/' : '').'DBFilesClient/'.$this->file.'.dbc';
            if (!CLISetup::fileExists($fullpath))
                continue;

            CLISetup::log(' - reading '.($this->localized ? 'and merging ' : '').'data from '.$fullpath);

            if (!$this->read($fullpath))
                CLISetup::log(' - DBC::read() returned with error', CLISetup::LOG_ERROR);
            else
                $foundMask |= (1 << $locId);

            if (!$this->localized)                          // one match is enough
                break;
        }

        return $this->getIndexed();
    }

    private function read($filename)
    {
        $file = fopen($filename, 'rb');

        if (!$file)
        {
            CLISetup::log('cannot open file '.$filename, CLISetup::LOG_ERROR);
            return false;
        }

        $filesize = filesize($filename);
        if ($filesize < 20)
        {
            CLISetup::log('file '.$filename.' is too small for a DBC file', CLISetup::LOG_ERROR);
            return false;
        }

        if (fread($file, 4) != 'WDBC')
        {
            CLISetup::log('file '.$filename.' has incorrect magic bytes', CLISetup::LOG_ERROR);
            return false;
        }

        $header = unpack('VrecordCount/VfieldCount/VrecordSize/VstringSize', fread($file, 16));

        // Different debug checks to be sure, that file was opened correctly
        $debugStr = '(recordCount='.$header['recordCount'].
                    ' fieldCount=' .$header['fieldCount'] .
                    ' recordSize=' .$header['recordSize'] .
                    ' stringSize=' .$header['stringSize'] .')';

        if ($header['recordCount'] * $header['recordSize'] + $header['stringSize'] + 20 != $filesize)
        {
            CLISetup::log('file '.$filename.' has incorrect size '.$filesize.': '.$debugStr, CLISetup::LOG_ERROR);
            return false;
        }

        if ($header['fieldCount'] != strlen($this->format))
        {
            CLISetup::log('incorrect format string ('.$this->format.') specified for file '.$filename.' fieldCount='.$header['fieldCount'], CLISetup::LOG_ERROR);
            return false;
        }

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
                CLISetup::log('unknown format parameter \''.$ch.'\' in format string', CLISetup::LOG_ERROR);
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

        // The last debug check (most of the code in this function is for debug checks)
        if ($recSize != $header['recordSize'])
        {
            CLISetup::log('format string size ('.$recSize.') for file '.$filename.' does not match actual size ('.$header['recordSize'].') '.$debugStr, CLISetup::LOG_ERROR);
            return false;
        }

        // And, finally, extract the records
        $strings = [];
        $rSize   = $header['recordSize'];
        $rCount  = $header['recordCount'];
        $fCount  = strlen($this->format);

        for ($i = 0; $i < $rCount; $i++)
        {
            $row = [];
            $idx = $i;
            $rec = unpack($unpackStr, fread($file, $header['recordSize']));

            // add 'virtual' enumerator for gt*-dbcs
            if ($this->isGameTable)
                $row[] = $i;

            for ($j = 0; $j < $fCount; $j++)
            {
                if (!isset($rec['f'.$j]))
                    continue;

                switch ($this->format[$j])
                {
                    case 's':
                        $val = intVal($rec['f'.$j]);
                        if (isset($strings[$val]))
                            $strings[$val] = '';

                        $row[] = &$strings[$val];
                        continue 2;
                    case 'f':
                        $row[] = round($rec['f'.$j], 8);
                        break;
                    case 'n':                               // DO NOT BREAK!
                        $idx = $rec['f'.$j];
                    default:                                // nothing special .. 'i', 'u' and the likes
                        $row[] = $rec['f'.$j];
                }
            }

            if (!$this->localized || empty($this->result[$idx]))
                $this->result[$idx] = $row;
            else
            {
                $n = 0;
                for ($j = 0; $j < $fCount; $j++)
                {
                    if ($this->format[$j] == 's')
                        if (!$this->result[$idx][$n])
                            $this->result[$idx][$n] = &$row[$n];

                    if ($this->format[$j] != 'x')
                        $n++;
                }
            }
        }

        // apply strings
        $strBlock = fread($file, $header['stringSize']);
        foreach ($strings as $offset => &$str)
        {
            $_   = substr($strBlock, $offset);
            $str = substr($_, 0, strpos($_, "\000"));
        }
        fclose($file);

        return !empty($this->result);
    }

    private function getIndexed()
    {
        $result = $this->result;
        $fields = $this->fields;
        if ($this->isGameTable)
            array_unshift($fields, 'idx');

        foreach ($result as &$row)
            $row = array_combine($fields, $row);

        return $result;
    }
}

?>
