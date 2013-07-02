{strip}
    new Listview({ldelim}
        template:'zone',
        {if !isset($params.id)}id:'zones',{/if}
        {if !isset($params.name)}name:LANG.tab_zones,{/if}
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
                    category:{$curr.category},
                    territory:{$curr.territory},
                    minlevel:{$curr.minlevel},
                    maxlevel:{$curr.maxlevel},
                    name:'{$curr.name|escape:"quotes"}'
                    {if isset($curr.expansion)},expansion:{$curr.expansion}{/if}
                    {if isset($curr.instance)},instance:{$curr.instance}{/if}
                    {if isset($curr.nplayers)},nplayers:{$curr.nplayers}{/if}
                    {if isset($curr.reqlevel)},reqlevel:{$curr.reqlevel}{/if}
                    {if isset($curr.lfgReqLevel)},lfgReqLevel:{$curr.lfgReqLevel}{/if}
                    {if isset($curr.heroicLevel)},heroicLevel:{$curr.heroicLevel}{/if}
{*                    {if isset($curr.percent)},percent:{$curr.percent}{/if}   what was that originaly for..?     *}
                {rdelim}
                {if $smarty.foreach.i.last}{else},{/if}
            {/foreach}
        ]
    {rdelim});
{/strip}
