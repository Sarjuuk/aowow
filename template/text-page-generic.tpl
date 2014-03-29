{include file='header.tpl'}

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

{if !empty($announcements)}
    {foreach from=$announcements item=item}
        {include file='bricks/announcement.tpl' an=$item}
    {/foreach}
{/if}

{if isset($notFound)}
            <div class="pad3"></div>

            <div class="inputbox">
                <h1>{$typeStr} #{$typeId}</h1>
                <div id="inputbox-error">{$notFound}</div>
{else}
            <script type="text/javascript">//<![CDATA[
                var g_pageInfo = {ldelim}type: {$type}, typeId: {$typeId}, name: '{$name|escape:"quotes"}'{rdelim};
    {if isset($path)}
                g_initPath({$path});
    {/if}
            //]]></script>

            <div class="text">
                <h1>{$name}</h1>

    {include file='bricks/article.tpl'}

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
{/if}
            </div>
        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}
