{strip}
    new Listview({ldelim}
        template:'item',
        {if !isset($params.id)}id:'items',{/if}
        {if !isset($params.name)}name:LANG.tab_items,{/if}
        {if !isset($params.parent)}parent:'listview-generic',{/if}
        {foreach from=$params key=k item=v}
            {if $v[0] == '$'}
                {$k}:{$v|substr:1},
            {else if $v}
                {$k}:'{$v}',
            {/if}
        {/foreach}
        data:[
            {foreach name=i from=$data item=curr}
                {ldelim}
                    name:'{$curr.name|escape:"quotes"}',
                    {if isset($curr.level)}
                        level:{$curr.level},
                    {/if}
                    {if isset($curr.reqlevel)}
                        reqlevel:{$curr.reqlevel},
                    {/if}
                        classs:{$curr.classs},
                        subclass:{$curr.subclass},
                    {if isset($curr.maxcount)}
                        {if $curr.maxcount>1}
                            stack:[{$curr.mincount},{$curr.maxcount}],
                        {/if}
                    {/if}
                    {if isset($curr.percent)}
                        percent:{$curr.percent},
                    {/if}
                    {if isset($curr.condition)}
                        condition:{$curr.condition},
                    {/if}
                    {if isset($curr.group) and isset($curr.groupcount)}
                        group:'({$curr.group}){if $curr.groupcount!=1} x{$curr.groupcount}{/if}',
                    {/if}
                    {if isset($curr.cost.money) || isset($curr.cost.honor) ||isset($curr.cost.arena) || isset($curr.cost.items)}
                        stock:-1,
                        cost:[
                            {if isset($curr.cost.money)}{$curr.cost.money}{/if}
                            {if isset($curr.cost.honor) or isset($curr.cost.arena) or isset($curr.cost.items)}
                                ,{if isset($curr.cost.honor)}{$curr.cost.honor}{/if}
                                {if isset($curr.cost.arena) or isset($curr.cost.items)}
                                    ,{if isset($curr.cost.arena)}{$curr.cost.arena}{/if}
                                    {if isset($curr.cost.items)}
                                        ,[
                                        {foreach from=$curr.cost.items item=curitem name=c}
                                            [{$curitem.item},{$curitem.count}]
                                            {if $smarty.foreach.c.last}{else},{/if}
                                        {/foreach}
                                        ]
                                    {/if}
                                {/if}
                            {/if}
                            ],
                    {/if}
                    {if isset($curr.displayid)}
                        displayid:{$curr.displayid},
                    {/if}
                    {if isset($curr.nslots)}
                        nslots:{$curr.nslots},
                    {/if}
                    {if isset($curr.dps)}
                        dps:{$curr.dps},
                        speed:{$curr.speed},
                    {/if}
                    {if isset($curr.armor)}
                        armor:{$curr.armor},
                    {/if}
                    {if isset($curr.slot)}
                        slot:{$curr.slot},
                        slotbak:{$curr.slotbak},
                    {/if}
                    id:{$curr.id}
                {rdelim}
                {if $smarty.foreach.i.last}{else},{/if}
            {/foreach}
        ]
    {rdelim});
{/strip}

{*  4.3 loot-example

    template: 'item',
    id: 'drops',
    name: LANG.tab_drops,
    tabs: tabsRelated,
    parent: 'lkljbjkb574',
    extraCols: [Listview.extraCols.count, Listview.extraCols.percent],
    sort:['-percent', 'name'],
    _totalCount: 448092, /* total # creature killed/looted */
    computeDataFunc: Listview.funcBox.initLootTable,
    onAfterCreate: Listview.funcBox.addModeIndicator,
    data: [
        {
            "classs":15,        /* Tab Type */
            "commondrop":true,  /* loot filtered as "not noteworthy" */
            "id":25445,
            "level":1,
            "name":"7Wretched Ichor",
            "slot":0,
            "source":[2],   /* 1: crafted; 2:zonedrop; 3:pvp; 4:quest; 5: Vendors; 6:Trainer; 7:Discovery; 8:Redemption; 9: Talent; 10:Starter; 11: Event; 12:Achievement; */
            "sourcemore":[{"z":3520}],  /* z: zone... */
            "subclass":0,   /* Tab:Type */
            modes:{
                "mode":4,   /* &1: heroic; &4: noteworthy(?); &8: reg10; &16: reg25; &32: hc10; &64: hc25; &128: RaidFinder */
                "4":{"count":363318,"outof":448092} /* calculate pct chance */
            },
            count:363318,
            stack:[1,1], /* [min, max] */
            pctstack:'{1: 50.0123,2: 49.9877}'  /* {dropCount: relChanceForThisStack} */
        }
    ]
});
*}