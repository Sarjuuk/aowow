{include file='header.tpl'}

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

{if !empty($announcements)}
    {foreach from=$announcements item=item}
        {include file='bricks/announcement.tpl' an=$item}
    {/foreach}
{/if}

            <script type="text/javascript">//<![CDATA[
{include file='bricks/community.tpl'}
                var g_pageInfo = {ldelim}type: {$type}, typeId: {$typeId}, name: '{$name|escape:"quotes"}'{rdelim};
                g_initPath({$path});
{if $user.id && $redButtons[$smarty.const.BUTTON_EQUIP]}
                DomContentLoaded.addEvent(function() {ldelim} pr_addEquipButton('equip-pinned-button', {$typeId}); {rdelim});
{/if}
            //]]></script>

{include file='bricks/infobox.tpl'}

            <div class="text">
{include file='bricks/redButtons.tpl'}

                <h1{if isset($expansion)} class="h1-icon"><span class="{$expansion}-icon-right">{$name}</span>{else}>{$name}{/if}</h1>

{include file='bricks/tooltip.tpl'}

{include file='bricks/article.tpl'}

{if !empty($disabled)}
	<div class="pad"></div>
    <b style="color: red">{$lang._unavailable}</b>
{/if}
{if !empty($transfer)}
	<div class="pad"></div>
    {$transfer}
{/if}
{if !empty($subItems)}
                <div class="clear"></div>
                <h3>{$lang._rndEnchants}</h3>

                <div class="random-enchantments" style="margin-right: 25px">
                    <ul>
        {foreach from=$subItems item=i key=k}{if $k < (count($subItems) / 2)}
                        <li><div>
                            <span class="q{$quality}">...{$i.name}</span>
                            <small class="q0">{$lang._chance|@sprintf:$i.chance}</small>
                            <br />{$i.enchantment}
                        </div></li>
        {/if}{/foreach}
                    </ul>
                </div>

    {if count($subItems) > 1}
                <div class="random-enchantments" style="margin-right: 25px">
                    <ul>
        {foreach from=$subItems item=i key=k}{if $k >= (count($subItems) / 2)}
                        <li><div>
                            <span class="q{$quality}">...{$i.name}</span>
                            <small class="q0">{$lang._chance|@sprintf:$i.chance}</small>
                            <br />{$i.enchantment}
                        </div></li>
        {/if}{/foreach}
                    </ul>
                </div>
    {/if}
{/if}
{include file='bricks/book.tpl'}

                <h2 class="clear">{$lang.related}</h2>
            </div>

{include file='bricks/tabsRelated.tpl' tabs=$lvData}

{include file='bricks/contribute.tpl'}

        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}
