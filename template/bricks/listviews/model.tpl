{strip}
    new Listview({ldelim}
        template:'model',
        {if !isset($params.id)}id:'gallery',{/if}
        {if !isset($params.name)}name:LANG.tab_gallery,{/if}
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
                    family:{$curr.family},
                    modelId:{$curr.modelId},
                    displayId:{$curr.displayId},
                    skin:'{$curr.skin|escape:"javascript"}',
                    count:{$curr.count},
                    minLevel:{$curr.minlevel},
                    maxLevel:{$curr.maxlevel}
                {rdelim}
                {if $smarty.foreach.i.last}{else},{/if}
            {/foreach}
        ]
    {rdelim});
{/strip}
