{strip}
    new Listview({ldelim}
        template:'race',
        {if !isset($params.id)}id:'races',{/if}
        {if !isset($params.name)}name:LANG.tab_races,{/if}
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
                    id:{$curr.id},
                    name:'{$curr.name|escape:"javascript"}',
                    classes:{$curr.classes},
                    faction:{$curr.faction},
                    leader:{$curr.leader},
                    zone:{$curr.zone},
                    side:{$curr.side}
                    {if isset($curr.expansion)}
                        ,expansion:{$curr.expansion}
                    {/if}
                {rdelim}
                {if $smarty.foreach.i.last}{else},{/if}
            {/foreach}
        ]
    {rdelim});
{/strip}
