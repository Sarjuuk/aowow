{strip}
    new Listview({ldelim}
        template:'classs',
        {if !isset($params.id)}id:'classes',{/if}
        {if !isset($params.name)}name:LANG.tab_classes,{/if}
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
