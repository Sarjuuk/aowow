{if !empty($tab.showRep)}
var _ = [
    {ldelim}
        id: 'standing',
        after: 'reqlevel',
        name: LANG.standing,
        width: '12%',
        value: 'standing',
        type: 'text',
        getValue: function(item)
        {ldelim}
            return g_reputation_standings[item.standing];
        {rdelim},
        compute: function(item, td)
        {ldelim}
            return g_reputation_standings[item.standing];
        {rdelim}
    {rdelim}
];
{/if}

{strip}
    new Listview({ldelim}
        template:'item',
        {if !isset($params.id)}id:'items',{/if}
        {if !isset($params.name)}name:LANG.tab_items,{/if}
        {if !isset($params.parent)}parent:'lv-generic',{/if}
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
                    {foreach from=$curr  key='name' item=val}
                        {if $name != 'id' && $name != 'name'}
                            {$name}:{$val|@json_encode:$smarty.const.JSON_NUMERIC_CHECK},
                        {/if}
                    {/foreach}
                    name:'{$curr.name|escape:"quotes"}',
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
            pctstack:'{1: 50.0123, 2: 49.9877}'  /* {dropCount: relChanceForThisStack} */
        }
    ]
});
*}