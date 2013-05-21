{strip}
    new Listview({ldelim}
        template:'itemset',
        {if !isset($params.id)}id:'itemsets',{/if}
        {if !isset($params.name)}name:LANG.tab_itemsets,{/if}
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
                    name:'{$curr.quality}{$curr.name|escape:"quotes"}',
                    {if $curr.minlevel}minlevel:{$curr.minlevel},{/if}
                    {if $curr.maxlevel}maxlevel:{$curr.maxlevel},{/if}
                    {if $curr.classes}classes:[{section name=j loop=$curr.classes}{$curr.classes[j]}{if $smarty.section.j.last}{else},{/if}{/section}],{/if}
                    {if $curr.reqclass}reqclass:{$curr.reqclass},{/if}
                    {if $curr.note}note:{$curr.note},{/if}
                    {if $curr.pieces}pieces:[{section name=j loop=$curr.pieces}{$curr.pieces[j]}{if $smarty.section.j.last}{else},{/if}{/section}],{/if}
                    {if isset($curr.type)}type:{$curr.type},{/if}
                    heroic:{if isset($curr.heroic) && $curr.heroic == 1}true{else}false{/if},
                    id:{$curr.id}
                {rdelim}
                {if $smarty.foreach.i.last}{else},{/if}
            {/foreach}
        ]
    {rdelim});
{/strip}
