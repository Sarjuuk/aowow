{strip}
    new Listview({ldelim}
        template:'title',
        {if !isset($params.id)}id:'titles',{/if}
        {if !isset($params.name)}name:LANG.tab_titles,{/if}
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
                    category:{$curr.category},
                    expansion:{$curr.expansion},
                    gender:{$curr.gender},
                    side:{$curr.side}
                    {if isset($curr.namefemale)}
                        , namefemale:'{$curr.namefemale|escape:"javascript"}'
                    {/if}
                    {if isset($curr.source)}
                        , source:{$curr.source}
                    {/if}
                {rdelim}
                {if $smarty.foreach.i.last}{else},{/if}
            {/foreach}
        ]
    {rdelim});
{/strip}
