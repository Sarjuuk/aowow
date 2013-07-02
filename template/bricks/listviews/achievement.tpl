{strip}
    new Listview({ldelim}
        template:'achievement',
        {if !isset($params.id)}id:'achievements',{/if}
        {if !isset($params.name)}name:LANG.tab_achievements,{/if}
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
                    description:'{$curr.description|escape:"javascript"}',
                    side:{$curr.faction},
                    points:{$curr.points},
                    category:{$curr.category},
                    parentcat:{$curr.parentCat}
                    {if isset($curr.type)}, type:{$curr.type}{/if}
                    {if isset($curr.rewards)}, rewards:{$curr.rewards}{/if}
                    {if isset($curr.reward)}, reward:'{$curr.reward|escape:"javascript"}'{/if}
                {rdelim}
                {if $smarty.foreach.i.last}{else},{/if}
            {/foreach}
        ]
    {rdelim});
{/strip}
