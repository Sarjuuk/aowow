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
                var g_pageInfo = {ldelim}type: {$page.type}, typeId: {$page.typeId}, name: '{$lvData.page.name|escape:"quotes"}'{rdelim};
                g_initPath({$page.path});
            //]]></script>

{include file='bricks/infobox.tpl' info=$lvData.infobox}

            <div class="text">

{include file='bricks/redButtons.tpl'}

                <h1>{$lvData.page.name}</h1>

{include file='bricks/article.tpl'}

{if $lvData.spillover}
    <div id="spillover" class="left"></div>
    <script type="text/javascript">//<![CDATA[
        Markup.printHtml("{$lvData.spillover}", "spillover", {strip}{ldelim}
            allow: Markup.CLASS_ADMIN,
            dbpage: true
        {rdelim}{/strip});
    //]]></script>

    <div class="pad2"></div>
{/if}

                <h2 class="clear">{$lang.related}</h2>
            </div>

{include file='bricks/tabsRelated.tpl' tabs=$lvData.relTabs}

{include file='bricks/contribute.tpl'}

        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}
