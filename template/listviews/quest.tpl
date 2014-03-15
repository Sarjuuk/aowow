{if !empty($tab.showRep)}
var _ = [
    {ldelim}
        id: 'reputation',
        after: 'rewards',
        name: LANG.rep,
        tooltip: LANG.tooltip_repgain,
        width: '8%',
        value: 'reputation'
    {rdelim}
];
{/if}

{strip}
    new Listview({ldelim}
        template:'quest',
        {if !isset($params.id)}id:'quests',{/if}
        {if !isset($params.name)}name:LANG.tab_quests,{/if}
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
                        {if $name != 'id'}
                            {$name}:{$val|@json_encode:$smarty.const.JSON_NUMERIC_CHECK},
                        {/if}
                    {/foreach}
                    id:{$curr.id}
                {rdelim}
                {if $smarty.foreach.i.last}{else},{/if}
            {/foreach}
        ]
    {rdelim});
{/strip}
