{strip}
    new Listview({ldelim}
        template:'model',
        {if !isset($params.id)}id:'gallery',{/if}
        {if !isset($params.name)}name:LANG.tab_gallery,{/if}
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
                        {if $name != 'count'}
                            {$name}:{$val|@json_encode:$smarty.const.JSON_NUMERIC_CHECK},
                        {/if}
                    {/foreach}
                    count:{$curr.count}
                {rdelim}
                {if $smarty.foreach.i.last}{else},{/if}
            {/foreach}
        ]
    {rdelim});
{/strip}
