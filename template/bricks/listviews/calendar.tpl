{strip}
    new Listview({ldelim}
        template:'holidaycal',
        {if !isset($params.id)}id:'calendar',{/if}
        {if !isset($params.name)}name:LANG.tab_calendar,{/if}
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
                    {if $curr.rec}
                        rec:{$curr.rec},
                    {/if}
                    category:{$curr.category},
                    id:{$curr.id},
                    name:'{$curr.name|escape:"javascript"}',
                    startDate:'{$curr.startDate}',
                    endDate:'{$curr.endDate}'
                {rdelim}
                {if $smarty.foreach.i.last}{else},{/if}
            {/foreach}
        ]
    {rdelim});
{/strip}
