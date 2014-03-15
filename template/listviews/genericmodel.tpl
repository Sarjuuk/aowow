{strip}
    new Listview({ldelim}
        template:'genericmodel',
        {if !isset($params.id)}id:'same-model-as',{/if}
        {if !isset($params.name)}name:LANG.tab_samemodelas,{/if}
        {if !isset($params.parent)}parent:'lv-generic',{/if}
        {* genericlinktype: 'item|object', *}
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
                        {if $name != 'id' && $name != 'name'}
                            {$name}:{$val|@json_encode:$smarty.const.JSON_NUMERIC_CHECK},
                        {/if}
                    {/foreach}
                    name:'{$curr.name|escape:"quotes"}',
                    id:{$curr.id}
                {rdelim}
                {if $smarty.foreach.i.last}{else},{/if}
            {/foreach}
        ]
    {rdelim});
{/strip}
