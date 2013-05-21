{strip}
    new Listview({ldelim}
        template:'quest',
        {if !isset($params.id)}id:'quests',{/if}
        {if !isset($params.name)}name:LANG.tab_quests,{/if}
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
                    id:'{$curr.id}',
                    name:'{$curr.name|escape:"quotes"}',
                    level:'{$curr.level}',
                    {if ($curr.reqlevel)}
                        reqlevel:{$curr.reqlevel},
                    {/if}
                    side:'{$curr.side}'
                    {if isset($curr.itemrewards)}
                        ,itemrewards:[
                            {section name=j loop=$curr.itemrewards}
                                [{$curr.itemrewards[j][0]},{$curr.itemrewards[j][1]}]
                                {if $smarty.section.j.last}{else},{/if}
                            {/section}
                        ]
                    {/if}
                    {if isset($curr.itemchoices)}
                        ,itemchoices:[
                            {section name=j loop=$curr.itemchoices}
                                [{$curr.itemchoices[j][0]},{$curr.itemchoices[j][1]}]
                                {if $smarty.section.j.last}{else},{/if}
                            {/section}
                        ]
                    {/if}
                    {if isset($curr.xp)}
                        ,xp:{$curr.xp}
                    {/if}
                    {if isset($curr.titlereward)}
                        ,titlereward:{$curr.titlereward}
                    {/if}
                    {if isset($curr.money)}
                        ,money:{$curr.money}
                    {/if}
                    {if isset($curr.category)}
                        ,category:{$curr.category}
                    {/if}
                    {if isset($curr.category2)}
                        ,category2:{$curr.category2}
                    {/if}
                    {if isset($curr.type)}
                        ,type:{$curr.type}
                    {/if}
                    {if isset($curr.Daily)}
                        ,daily:1
                    {/if}
                    {if isset($curr.flags)}
                        ,wflags:$curr.flags          {* wflags: &1: disabled/historical; &32: AutoAccept; &64: Hostile(?) *}
                    {/if}
                {rdelim}
                {if $smarty.foreach.i.last}{else},{/if}
            {/foreach}
        ]
    {rdelim});
{/strip}
