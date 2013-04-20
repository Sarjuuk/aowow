{strip}
    new Listview({ldelim}
        template:'currency',
        {if !isset($params.id)}id:'currencies',{/if}
        {if !isset($params.tabs)}tabs:'listview-generic',{/if}
        {if !isset($params.name)}name:LANG.tab_currencies,{/if}
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
                    category:{$curr.category},
                    name:'{$curr.name|escape:"javascript"}',
                    icon:'{$curr.icon}',
                {rdelim}
                {if $smarty.foreach.i.last}{else},{/if}
            {/foreach}
        ]
    {rdelim});
{/strip}