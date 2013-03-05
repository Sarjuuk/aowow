var _ = g_items;
{strip}
{foreach from=$data key=id item=item}
	_[{$id}]={ldelim}
		icon:'{$item.icon|escape:"javascript"}',
		name_{$user.language}:'{$item.name|escape:"javascript"}' 
		{if isset($item.quality)}, quality:'{$item.quality}'{/if}
	{rdelim};
{/foreach}
{/strip}
