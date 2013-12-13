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
                var g_pageInfo = {ldelim}type: {$page.type}, typeId: {$page.typeId}, name: '$lvData.name|escape:"quotes"}'{rdelim};
                g_initPath({$page.path});
            //]]></script>

{include file='bricks/infobox.tpl' info=$lvData.infobox}

            <div class="text">

{include file='bricks/redButtons.tpl'}

                <h1>{$lvData.page.name}</h1>

{include file='bricks/tooltip.tpl'}

{if !empty($lvData.page.disabled)}
	<div class="pad"></div>
    <b style="color: red">{$lang._unavailable}</b>
{/if}
{if !empty($lvData.page.transfer)}
	<div class="pad"></div>
    {$lang._transfer|sprintf:$lvData.page.transfer.id:$lvData.page.transfer.quality:$lvData.page.transfer.icon:$lvData.page.transfer.name:$lvData.page.transfer.facInt:$lvData.page.transfer.facName}
{/if}
{if !empty($lvData.page.subItems)}
                <div class="clear"></div>
                <h3>{$lang._rndEnchants}</h3>

                <div class="random-enchantments" style="margin-right: 25px">
                    <ul>
        {foreach from=$lvData.page.subItems item=i key=k}{if $k < (count($lvData.page.subItems) / 2)}
                        <li><div>
                            <span class="q{$lvData.page.quality}">...{$i.name}</span>
                            <small class="q0">{$lang._chance|@sprintf:$i.chance}</small>
                            <br />{$i.enchantment}
                        </div></li>
        {/if}{/foreach}
                    </ul>
                </div>

    {if count($lvData.page.subItems) > 1}
                <div class="random-enchantments" style="margin-right: 25px">
                    <ul>
        {foreach from=$lvData.page.subItems item=i key=k}{if $k >= (count($lvData.page.subItems) / 2)}
                        <li><div>
                            <span class="q{$lvData.page.quality}">...{$i.name}</span>
                            <small class="q0">{$lang._chance|@sprintf:$i.chance}</small>
                            <br />{$i.enchantment}
                        </div></li>
        {/if}{/foreach}
                    </ul>
                </div>
    {/if}
{/if}
{if !empty($lvData.pageText)}
                <div class="clear"></div>
                <h3>{$lang.content}</h3>

                <div id="book-generic"></div>
                <script>//<![CDATA[
                    {strip}new Book({ldelim} parent: 'book-generic', pages: [
    {foreach from=$lvData.pageText item=page name=j}
                        '{$page|escape:"javascript"}'
                        {if $smarty.foreach.j.last}{else},{/if}
    {/foreach}
                    ]{rdelim}){/strip}
                //]]></script>

{/if}

                <div style="clear: left"></div>
                <h2>{$lang.related}</h2>
            </div>

{include file='bricks/tabsRelated.tpl' tabs=$lvData.relTabs}

{include file='bricks/contribute.tpl'}

        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}
