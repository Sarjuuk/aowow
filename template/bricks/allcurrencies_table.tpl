{strip}

var _ = g_gatheredcurrencies;
{foreach from=$data key=id item=item}
	_[{$id}]={ldelim}icon:'{$item.icon|escape:"javascript"}',name_{$user.language}:'{$item.name|escape:"javascript"}'{rdelim};
{/foreach}

{/strip}