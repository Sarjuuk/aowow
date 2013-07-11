{strip}
    new Listview({ldelim}
        template:'npc',
        {if !isset($params.id)}id:'npcs',{/if}
        {if !isset($params.name)}name:LANG.tab_npcs,{/if}
        {if !isset($params.parent)}parent:'listview-generic',{/if}
        {foreach name=params from=$params key=k item=v}
            {if $v[0] == '$'}
                {$k}:{$v|substr:1},
            {else if $v}
                {$k}:'{$v}',
            {/if}
        {/foreach}
        data:[
            {foreach name=i from=$data item=curr}
                {ldelim}
                    name:'{$curr.name|escape:"quotes"}',
                    {if $curr.tag}
                        tag:'{$curr.tag|escape:"quotes"}',
                    {/if}
                    minlevel:{$curr.minlevel},
                    maxlevel:{$curr.maxlevel},
                    type:{$curr.type},
                    classification:{$curr.rank},
                    {if $curr.boss}
                        boss: 1,
                    {/if}
                    react:{$curr.react},
                    location:{$curr.location},
                    {if isset($curr.skin)}
                        skin: '{$curr.skin}',
                    {/if}
                    {if isset($curr.percent)}
                        percent:{$curr.percent},
                    {/if}
                    {if isset($curr.cost)}
                        stock:{$curr.stock},
                        {if isset($curr.stack)}
                            stack:{$curr.stack},
                        {/if}
                        cost:[
                            {if isset($curr.cost.money)}{$curr.cost.money}{/if}
                            {if isset($curr.cost.honor) or isset($curr.cost.arena) or isset($curr.cost.items)}
                                ,{if isset($curr.cost.honor)}{$curr.cost.honor}{/if}
                                {if isset($curr.cost.arena) or isset($curr.cost.items)}
                                    ,{if isset($curr.cost.arena)}{$curr.cost.arena}{/if}
                                    {if isset($curr.cost.items)}
                                        ,[
                                        {foreach from=$curr.cost.items item=curitem name=c}
                                            [{$curitem.item},{$curitem.count}]
                                            {if $smarty.foreach.c.last}{else},{/if}
                                        {/foreach}
                                        ]
                                    {/if}
                                {/if}
                            {/if}
                        ],
                    {/if}
                    id:{$curr.id}
                {rdelim}
                {if $smarty.foreach.i.last}{else},{/if}
            {/foreach}
        ]{rdelim}
    );
{/strip}
