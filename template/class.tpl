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
                <div id="headicon-generic" class="h1-icon"></div>
                <script type="text/javascript">
                    $WH.ge('headicon-generic').appendChild(Icon.create('{$lvData.page.icon}', 1));
                </script>
                <a href="http://old.wowhead.com/?{$query[0]}={$query[1]}" class="button-red"><em><b><i>Wowhead</i></b><span>Wowhead</span></em></a>
                <a href="javascript:;" id="open-links-button" class="button-red" onclick="this.blur(); Links.show({ldelim} type: 13, typeId: {$page.typeId} {rdelim});"><em><b><i>{$lang.links}</i></b><span>{$lang.links}</span></em></a>
                <a href="?talent#{$lvData.page.talentCalc}" class="button-red"><em><b><i>{$lang.talentCalc}</i></b><span>{$lang.talentCalc}</span></em></a>
                <a href="{if !empty($page.boardUrl)}{$page.boardUrl}{else}javascript:;{/if}" class="button-red{if empty($page.boardUrl)} button-red-disabled{/if}"><em><b><i>{$lang.forum}</i></b><span>{$lang.forum}</span></em></a>
                <h1 class="h1-icon">{$lvData.page.name}</h1>

{include file='bricks/article.tpl'}

                <h2 class="clear">{$lang.related}</h2>
            </div>

{include file='bricks/tabsRelated.tpl' tabs=$lvData.relTabs}

{include file='bricks/contribute.tpl'}

        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}
