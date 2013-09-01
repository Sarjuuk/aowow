{strip}
    new Listview({ldelim}
        template: 'video',
        id: 'videos',
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
                    id:{$vi.id},
                    user:'{$vi.user}',
                    date:'{$vi.date|date_format:"%Y/%m/%d %H:%M:%S"}',
                    videoType:1, {* there is only youtube *}
                    videoId:'{$vi.videoId}',
                    type:{$page.type},
                    typeId:{$page.typeId},
                    {if isset($vi.sticky)}
                        sticky:{$vi.sticky},
                    {/if}
                {rdelim}
                {if $smarty.foreach.i.last}{else},{/if}
            {/foreach}
        ]
    {rdelim});
{/strip}

