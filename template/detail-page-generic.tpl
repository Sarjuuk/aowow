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
            //]]></script>

{include file='bricks/infobox.tpl'}

            <div class="text">
{include file='bricks/headIcons.tpl'}

{include file='bricks/redButtons.tpl'}

                <h1{if isset($expansion)} class="h1-icon"><span class="icon-{$expansion}-right">{$name}</span>{else}>{$name}{/if}</h1>

{include file='bricks/article.tpl'}

{include file='bricks/mapper.tpl'}

{if isset($extraText)}
    <div id="text-generic" class="left"></div>
    <script type="text/javascript">//<![CDATA[
        Markup.printHtml("{$extraText}", "text-generic", {strip}{ldelim}
            allow: Markup.CLASS_ADMIN,
            dbpage: true
        {rdelim}{/strip});
    //]]></script>

    <div class="pad2"></div>
{/if}

{if isset($unavailable)}
                <div class="pad"></div>
                <b style="color: red">{$lang._unavailable}</b>
{/if}

                <h2 class="clear">{$lang.related}</h2>
            </div>

{include file='bricks/tabsRelated.tpl' tabs=$lvData}

{include file='bricks/contribute.tpl'}

        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}
