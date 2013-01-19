var _ = g_achievements;
{strip}
{foreach from=$data key=id item=item}
	_[{$id}]={ldelim}icon:'{$item.icon|escape:"javascript"}',name_{$user.language}:'{$item.name|escape:"javascript"}'{rdelim};
{/foreach}
{/strip}
