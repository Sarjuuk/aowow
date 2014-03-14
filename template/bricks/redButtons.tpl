{if isset($redButtons[$smarty.const.BUTTON_WOWHEAD])}
    {assign var='b' value=$redButtons[$smarty.const.BUTTON_WOWHEAD]}
    {if $b}
        <a href="{$wowhead}" class="button-red"><em><b><i>Wowhead</i></b><span>Wowhead</span></em></a>
    {else}
        <a href="javascript:;" class="button-red button-red-disabled"><em><b><i>Wowhead</i></b><span>Wowhead</span></em></a>
    {/if}
{/if}
{if isset($redButtons[$smarty.const.BUTTON_LINKS])}
    {assign var='b' value=$redButtons[$smarty.const.BUTTON_LINKS]}
    {if $b}
        <a href="javascript:;" id="open-links-button" class="button-red" onclick="this.blur(); Links.show({ldelim} {if isset($b.color)}linkColor: '{$b.color}', {/if}{if isset($b.linkId)}linkId: '{$b.linkId}', {/if}{if isset($b.name)}linkName: '{$name|escape:"javascript"}', {/if}type: {$type}, typeId: {$typeId} {rdelim});"><em><b><i>{$lang.links}</i></b><span>{$lang.links}</span></em></a>
    {else}
        <a href="javascript:;" id="open-links-button" class="button-red button-red-disabled"><em><b><i>{$lang.links}</i></b><span>{$lang.links}</span></em></a>
    {/if}
{/if}
{if isset($redButtons[$smarty.const.BUTTON_VIEW3D])}
    {assign var='b' value=$redButtons[$smarty.const.BUTTON_VIEW3D]}
    {if $b}
        <a href="javascript:;" id="view3D-button" class="button-red" onclick="this.blur(); ModelViewer.show({ldelim} {foreach from=$b name=i key=k item=v}{$k}: {$v|@json_encode:$smarty.const.JSON_NUMERIC_CHECK}{if $smarty.foreach.i.last}{else}, {/if}{/foreach} {rdelim})"><em><b><i>{$lang.view3D}</i></b><span>{$lang.view3D}</span></em></a>
    {else}
        <a href="javascript:;" id="view3D-button" class="button-red button-red-disabled"><em><b><i>{$lang.view3D}</i></b><span>{$lang.view3D}</span></em></a>
    {/if}
{/if}
{if isset($redButtons[$smarty.const.BUTTON_COMPARE])}
    {assign var='b' value=$redButtons[$smarty.const.BUTTON_COMPARE]}
    {if $b}
        <a href="javascript:;" class="button-red" onclick="this.blur(); su_addToSaved('{if isset($b.eqList)}{$b.eqList}{else}{$typeId}{/if}', {if isset($b.qty)}{$b.qty}{else}1{/if})"><em><b><i>{$lang.compare}</i></b><span>{$lang.compare}</span></em></a>
    {else}
        <a href="javascript:;" class="button-red button-red-disabled"><em><b><i>{$lang.compare}</i></b><span>{$lang.compare}</span></em></a>
    {/if}
{/if}
{if isset($redButtons[$smarty.const.BUTTON_UPGRADE])}
    {assign var='b' value=$redButtons[$smarty.const.BUTTON_UPGRADE]}
    {if $b}
        <a href="javascript:;" class="button-red" onclick="this.blur(); pr_showClassPresetMenu(this, {$typeId}, {$b.class}, {$b.slot}, event);"><em><b><i>{$lang.findUpgrades}</i></b><span>{$lang.findUpgrades}</span></em></a>
    {else}
        <a href="javascript:;" class="button-red button-red-disabled"><em><b><i>{$lang.findUpgrades}</i></b><span>{$lang.findUpgrades}</span></em></a>
    {/if}
{/if}
{if isset($redButtons[$smarty.const.BUTTON_TALENT])}
    {assign var='b' value=$redButtons[$smarty.const.BUTTON_TALENT]}
    {if $b}
        <a href="{$b.href}" class="button-red"><em><b><i>{if $b.pet}{$lang.petCalc}{else}{$lang.talentCalc}{/if}</i></b><span>{if $b.pet}{$lang.petCalc}{else}{$lang.talentCalc}{/if}</span></em></a>
    {else}
        <a href="javascript:;" class="button-red button-red-disabled"><em><b><i>{if $b.pet}{$lang.petCalc}{else}{$lang.talentCalc}{/if}</i></b><span>{if $b.pet}{$lang.petCalc}{else}{$lang.talentCalc}{/if}</span></em></a>
    {/if}
{/if}
{if isset($redButtons[$smarty.const.BUTTON_FORUM])}
    {assign var='b' value=$redButtons[$smarty.const.BUTTON_FORUM]}
    {if $b}
        <a href="{$b.href}" class="button-red"><em><b><i>{$lang.forum}</i></b><span>{$lang.forum}</span></em></a>
    {else}
        <a href="javascript:;" class="button-red button-red-disabled"><em><b><i>{$lang.forum}</i></b><span>{$lang.forum}</span></em></a>
    {/if}
{/if}
{if isset($redButtons[$smarty.const.BUTTON_EQUIP])}
    <div id="equip-pinned-button"></div> {* content is added by jScript *}
{/if}
