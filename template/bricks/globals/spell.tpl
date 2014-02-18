var _ = g_spells;
{strip}
{foreach from=$data key=id item=item}
    _[{$id}]={ldelim}
        name_{$user.language}:'{$item.name|escape:"javascript"}',
        icon:'{$item.icon|escape:"javascript"}'
    {rdelim};
{/foreach}

{if $extra}
    _[{$extra.id}].tooltip_{$user.language}    = '{$extra.tooltip}';
    _[{$extra.id}].buff_{$user.language}       = '{$extra.buff}';
    _[{$extra.id}].spells_{$user.language}     = {$extra.spells|@json_encode:$smarty.const.JSON_NUMERIC_CHECK};
    _[{$extra.id}].buffspells_{$user.language} = {$extra.buffspells|@json_encode:$smarty.const.JSON_NUMERIC_CHECK};
{/if}

{/strip}
