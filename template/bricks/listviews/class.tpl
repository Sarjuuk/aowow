{strip}
    new Listview({ldelim}
        template:'classs',
        {if !isset($params.id)}id:'classes',{/if}
        {if !isset($params.name)}name:LANG.tab_classes,{/if}
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
                    races:{$curr.races},
                    roles:{$curr.roles},
                    power:{$curr.power},
                    weapon:{$curr.weapon},
                    armor:{$curr.armor}
                    {if isset($curr.hero)}
                        ,hero:1
                    {/if}
                    {if isset($curr.expansion)}
                        ,expansion:{$curr.expansion}
                    {/if}
                {rdelim}
                {if $smarty.foreach.i.last}{else},{/if}
            {/foreach}
        ]
    {rdelim});
{/strip}
