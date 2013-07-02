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

{include file='bricks/infobox.tpl'}

            <div class="text">
                <a href="javascript:;" id="open-links-button" class="button-red" onclick="this.blur(); Links.show({ldelim} type: 9, typeId: {$lvData.page.id} {rdelim});"><em><b><i>{$lang.links}</i></b><span>{$lang.links}</span></em></a>
                <a href="?petcalc#{$lvData.page.petCalc}" class="button-red"><em><b><i>{$lang.petCalc}</i></b><span>{$lang.petCalc}</span></em></a>
                <a href="http://old.wowhead.com/?{$query[0]}={$query[1]}" class="button-red"><em><b><i>Wowhead</i></b><span>Wowhead</span></em></a>
                <div id="h1-icon-generic" class="h1-icon"></div>

                <script type="text/javascript">//<![CDATA[
                    ge('h1-icon-generic').appendChild(Icon.create('{$lvData.page.icon}', 1));
                //]]></script>

                <h1 class="h1-icon">{if isset($lvData.page.expansion)}<span class="{$lvData.page.expansion}-icon-right">{$lvData.page.name}</span>{else}{$lvData.page.name}{/if}</h1>

{include file='bricks/article.tpl'}

                <h2 class="clear">{$lang.related}</h2>
            </div>

            <div id="tabs-generic"></div>
            <div id="listview-generic" class="listview"></div>
            <script type="text/javascript">//<![CDATA[
                var tabsRelated = new Tabs({ldelim}parent: ge('tabs-generic'){rdelim});
{if isset($lvData.gallery)}   {include file='bricks/listviews/model.tpl'    data=$lvData.gallery.data   params=$lvData.gallery.params  } {/if}
{if isset($lvData.tameable)}  {include file='bricks/listviews/creature.tpl' data=$lvData.tameable.data  params=$lvData.tameable.params } {/if}
{if isset($lvData.abilities)} {include file='bricks/listviews/spell.tpl'    data=$lvData.abilities.data params=$lvData.abilities.params} {/if}
{if isset($lvData.talents)}   {include file='bricks/listviews/spell.tpl'    data=$lvData.talents.data   params=$lvData.talents.params  } {/if}
{if isset($lvData.diet)}      {include file='bricks/listviews/item.tpl'     data=$lvData.diet.data      params=$lvData.diet.params     } {/if}
                new Listview({ldelim}template: 'comment', id: 'comments', name: LANG.tab_comments, tabs: tabsRelated, parent: 'listview-generic', data: lv_comments{rdelim});
                new Listview({ldelim}template: 'screenshot', id: 'screenshots', name: LANG.tab_screenshots, tabs: tabsRelated, parent: 'listview-generic', data: lv_screenshots{rdelim});
                if (lv_videos.length || (g_user && g_user.roles & (U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO)))
                    new Listview({ldelim}template: 'video', id: 'videos', name: LANG.tab_videos, tabs: tabsRelated, parent: 'listview-generic', data: lv_videos{rdelim});
                tabsRelated.flush();
            //]]></script>

{include file='bricks/contribute.tpl'}

        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}
