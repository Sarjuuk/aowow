var _ = g_titles;
{strip}
{foreach from=$data key=id item=item}
    _[{$id}]={ldelim}
        name_{$user.language}:'{$item.name|escape:"javascript"}'
        {if isset($item.namefemale)}, namefemale_{$user.language}:'{$item.namefemale|escape:"javascript"}'{/if}
    {rdelim};
{/foreach}
{/strip}
