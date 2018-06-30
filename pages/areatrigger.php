<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 102: Areatrigger g_initPath()
//  tabid   0: Database    g_initHeader()
class AreaTriggerPage extends GenericPage
{
    use TrDetailPage;

    protected $type          = TYPE_AREATRIGGER;
    protected $typeId        = 0;
    protected $tpl           = 'detail-page-generic';
    protected $path          = [0, 102];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $reqUGroup     = U_GROUP_STAFF;

    public function __construct($pageCall, $id)
    {
        $this->hasComContent = false;
        $this->contribute    = CONTRIBUTE_NONE;

        parent::__construct($pageCall, $id);

        $this->typeId = intVal($id);

        $this->subject = new AreaTriggerList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound(Util::ucFirst(Lang::game('areatrigger')), Lang::areatriger('notFound'));

        $this->name = $this->subject->getField('name') ?: 'AT #'.$this->typeId;
    }

    protected function generatePath()
    {
        $this->path[] = $this->subject->getField('type');
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name, Util::ucFirst(Lang::game('areatrigger')));
    }

    protected function generateContent()
    {
        $this->addJS('?data=zones&locale='.User::$localeId.'&t='.$_SESSION['dataKey']);

        $_type = $this->subject->getField('type');


        /****************/
        /* Main Content */
        /****************/

        // get spawns
        $map = null;
        if ($spawns = $this->subject->getSpawns(SPAWNINFO_FULL))
        {
            $ta = $this->subject->getField('teleportA');
            $tf = $this->subject->getField('teleportF');
            $tx = $this->subject->getField('teleportX');
            $ty = $this->subject->getField('teleportY');
            $to = $this->subject->getField('teleportO');

            // add teleport target
            if ($ta && $tx && $ty)
            {
                $o = Util::O2Deg($to);
                $endPoint = array($tx, $ty, array(
                    'type' => 4,
                    'tooltip' => array(
                        'Teleport Destination' => array(
                            'info' => ['Orientation'.Lang::main('colon').$o[0].'° ('.$o[1].')']
                        )
                    )
                ));

                if (isset($spawns[$ta][$tf]))
                    $spawns[$ta][$tf]['coords'][] = $endPoint;
                else
                    $spawns[$ta][$tf]['coords'] = [$endPoint];
            }

            $map = array(
                'data'       => ['parent' => 'mapper-generic'],
                'mapperData' => &$spawns
            );

            foreach ($spawns as $areaId => &$areaData)
                $map['extra'][$areaId] = ZoneList::getName($areaId);
        }

        $this->map = $map;
        $this->infobox   = false;
        $this->redButtons = array(
            BUTTON_LINKS   => false,
            BUTTON_WOWHEAD => false
        );


        /**************/
        /* Extra Tabs */
        /**************/

        if ($_type == AT_TYPE_OBJECTIVE)
        {
            $relQuest = new QuestList(array(['id', $this->subject->getField('quest')]));
            if (!$relQuest->error)
            {
                $this->extendGlobalData($relQuest->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_REWARDS));
                $this->lvTabs[] = ['quest', ['data' => array_values($relQuest->getListviewData())]];
            }
        }
        else if ($_type == AT_TYPE_TELEPORT)
        {
            $relZone = new ZoneList(array(['id', $this->subject->getField('teleportA')]));
            if (!$relZone->error)
            {
                $this->lvTabs[] = ['zone', ['data' => array_values($relZone->getListviewData())]];
            }
        }
        else if ($_type == AT_TYPE_SCRIPT)
        {
            $relTrigger = new AreaTriggerList(array(['id', $this->typeId, '!'], ['name', $this->subject->getField('name')]));
            if (!$relTrigger->error)
            {
                $this->lvTabs[] = ['areatrigger', ['data' => array_values($relTrigger->getListviewData()), 'name' => Util::ucFirst(Lang::game('areatrigger'))], 'areatrigger'];
            }
        }
        else if ($_type == AT_TYPE_SMART)
        {
            // sourceType:2 [Areatrigger] implies eventTypes: 46, 61 [onTrigger, Linked]
            $scripts = DB::World()->select('SELECT id, action_type, action_param1, action_param2, action_param3, action_param4, target_type, target_param1, target_param2 FROM smart_scripts WHERE entryorguid = ?d AND source_type = ?d ORDER BY id ASC', $this->typeId, 2);

            $tbl = '';

            foreach ($scripts as $sc)
            {
                $action = '';
                $resolveTarget = function ($type, $guid) {
                    switch ($type)
                    {
                        case  7:                                // invoker
                            return 'Invoker';
                        case 10:                                // creature guid <param1> entry <param2>
                            if ($id = DB::World()->selectCell('SELECT id FROM creature WHERE guid = ?d', $guid))
                            {
                                $this->extendGlobalIds(TYPE_NPC, $id);
                                return '[npc='.$id.'] (GUID: '.$guid.')';
                            }
                            else
                                return 'Unknown NPC with GUID: '.$guid;
                        case 14:                                // object guid <param1> entry <param2>
                            if ($id = DB::World()->selectCell('SELECT id FROM gameobject WHERE guid = ?d', $guid))
                            {
                                $this->extendGlobalIds(TYPE_OBJECT, $id);
                                return '[object='.$id.'] (GUID: '.$guid.')';
                            }
                            else
                                return 'Unknown GameObject with GUID: '.$guid;
                        default:
                            return 'Unhandled target #'.$type;
                    }
                };

                switch ($sc['action_type'])
                {
                    case 15:                                // complete quest <param1> for {target}
                        $this->extendGlobalIds(TYPE_QUEST, $sc['action_param1']);
                        $action = '[quest='.$sc['action_param1'].'] is completed for '.$resolveTarget($sc['target_type'], $sc['target_param1']).'.';
                        break;
                    case 33:                                // kill credit <param1> for {target}
                        $this->extendGlobalIds(TYPE_NPC, $sc['action_param1']);
                        $action = 'A kill of [npc='.$sc['action_param1'].'] is credited to '.$resolveTarget($sc['target_type'], $sc['target_param1']).'.';
                        break;
                    case 45:                                // set data <param2> in field <param1> in {target}
                        $action = '\"'.$sc['action_param2'].'\" ist stored in field '.$sc['action_param1'].' of '.$resolveTarget($sc['target_type'], $sc['target_param1']).'.';
                        break;
                    case 51:                                // kill {target}
                        $action = $resolveTarget($sc['target_type'], $sc['target_param1']).' dies!';
                        break;
                    case 62:                                // {target} is teleported to map <param1> [resolved coords already stored in areatrigger entry]
                        $this->extendGlobalIds(TYPE_ZONE, $this->subject->getField('teleportA'));
                        $action = $resolveTarget($sc['target_type'], $sc['target_param1']).' is teleported to [zone='.$this->subject->getField('teleportA').'].';
                        break;
                    case 64:                                // store {target} in <param1>
                        $action = 'Store '.$resolveTarget($sc['target_type'], $sc['target_param1']).' as target in \"'.$sc['action_param1'].'\".';
                        break;
                    case 70:                                // respawn GO for <param1> sec
                        $action = $resolveTarget($sc['target_type'], $sc['target_param1']).' is respawned for '.Util::formatTime($sc['action_param1'] * 1000).'.';
                        break;
                    case 85:                                // invoker cast spell <param1> with flags <param2>, <param3> at {target}
                        $this->extendGlobalIds(TYPE_SPELL, $sc['action_param1']);
                        $action = 'Invoker casts [spell='.$sc['action_param1'].'] at '.$resolveTarget($sc['target_type'], $sc['target_param1']).'.';
                        break;
                    case 86:                                // entity by TargetingBlock(param3, param4, param5, param6) cross cast spell <param1> at {target}
                        $this->extendGlobalIds(TYPE_SPELL, $sc['action_param1']);
                        $action = $resolveTarget($sc['action_param3'], $sc['action_param4']).' casts [spell='.$sc['action_param1'].'] at '.$resolveTarget($sc['target_type'], $sc['target_param1']).'.';
                        break;
                    case 100:                               // send targets stored in <param1> to entity {target}
                        $action = 'Send targets stored in \"'.$sc['action_param1'].'\" to '.$resolveTarget($sc['target_type'], $sc['target_param1']).'.';
                        break;
                    default:
                        $action = 'Unhandled action '.$sc['action_type'];
                }

                $tbl .= '[tr][td]'.$sc['id'].'[/td][td]'.$action.'[/td][/tr]';
            }

            $this->extraText = '[pad][h3]On Trigger: SmartAI[h3][table class=grid width=750px]'.$tbl.'[/table]';
        }
    }
}

?>
