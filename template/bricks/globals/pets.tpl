var _ = g_pets;
{strip}
{foreach from=$data key=id item=item}
    _[{$id}]={ldelim}
        icon:'{$item.icon|escape:"javascript"}'
    {rdelim};
{/foreach}
{/strip}
