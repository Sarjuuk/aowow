var _ = g_npcs;
{strip}
{foreach from=$data key=id item=item}
    _[{$id}]={ldelim}
        name_{$user.language}:'{$item.name|escape:"javascript"}'
    {rdelim};
{/foreach}
{/strip}
