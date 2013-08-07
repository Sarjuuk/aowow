{strip}
    new Listview({ldelim}
        template:'skill',
        {if !isset($params.id)}id:'skills',{/if}
        {if !isset($params.name)}name:LANG.tab_skills,{/if}
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
                    category:{$curr.category},
                    categorybak:{$curr.categorybak},
                    id:{$curr.id},
                    name:'{$curr.name|escape:"javascript"}',
                    profession:{$curr.profession},
                    recipeSubclass:{$curr.recipeSubclass},
                    specializations:{$curr.specializations},
                    icon:'{$curr.icon|escape:"javascript"}'
                {rdelim}
                {if $smarty.foreach.i.last}{else},{/if}
            {/foreach}
        ]
    {rdelim});
{/strip}
