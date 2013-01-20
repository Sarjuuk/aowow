{strip}
    new Listview({ldelim}
        template:'title',
        {if !isset($params.id)}id:'titles',{/if}
        {if isset($params.note)}_truncated: 1,{/if}
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
                    category:{$curr.category},
                    expansion:{$curr.expansion},
                    gender:{$curr.gender},
                    id:{$curr.id},
                    side:{$curr.side},
                    name:'{$curr.name|escape:"javascript"}'
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