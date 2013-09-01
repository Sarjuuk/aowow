{strip}
    new Listview({ldelim}
        template: 'screenshot',
        id: 'screenshots',
        {if !isset($params.parent)}parent:'lv-generic',{/if}
        {foreach from=$params key=k item=v}
            {if $v[0] == '$'}
                {$k}:{$v|substr:1},
            {else if $v}
                {$k}:'{$v}',
            {/if}
        {/foreach}
        data: [
            {foreach name=i from=$data item=curr}
                {ldelim}
                    id:{$curr.id},
                    user:'{$curr.user|escape:"javascript"}',
                    date:'{$curr.date|date_format:"%Y/%m/%d %H:%M:%S"}',
                    caption:'{$curr.caption|escape:"javascript"}',
                    subject:'{$curr.subject|escape:"javascript"}',
                    width:{$curr.width},
                    height:{$curr.height},
                    type:{$curr.type},
                    typeId:{$curr.typeId}
                {rdelim}
                {if $smarty.foreach.i.last}{else},{/if}
            {/foreach}
        ]
    {rdelim});
{/strip}
