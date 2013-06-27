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
                <a href="javascript:;" id="open-links-button" class="button-red" onclick="this.blur(); Links.show({ldelim} type: 4, typeId: {$lvData.page.id} {rdelim});"><em><b><i>{$lang.links}</i></b><span>{$lang.links}</span></em></a>
                <a href="javascript:;" id="view3D-button" class="button-red" onclick="this.blur(); ModelViewer.show({ldelim} type: 4, typeId: {$lvData.page.id}, equipList: {$lvData.view3D} {rdelim})"><em><b><i>{$lang.view3D}</i></b><span>{$lang.view3D}</span></em></a>
                <a href="javascript:;" class="button-red" onclick="this.blur(); su_addToSaved('{':'|implode:$lvData.compare.items}', {$lvData.compare.qty})"><em><b><i>{$lang.compare}</i></b><span>{$lang.compare}</span></em></a>
                <a href="{if $lvData.page.id > 0}http://old.wowhead.com/?{$query[0]}={$query[1]}{else}javascript:;{/if}" class="button-red{if $lvData.page.id < 0} button-red-disabled{/if}"><em><b><i>Wowhead</i></b><span>Wowhead</span></em></a>
                <h1>{$lvData.page.name}</h1>

{include file='bricks/article.tpl'}

{$lvData.page.description}

                <script type="text/javascript">//<![CDATA[
{section name=i loop=$lvData.pieces}
                    g_items.add({$lvData.pieces[i].id}, {ldelim}name_{$user.language}:'{$lvData.pieces[i].name|escape:'javascript'}', quality:{$lvData.pieces[i].quality}, icon:'{$lvData.pieces[i].icon}', jsonequip:{$lvData.pieces[i].json}{rdelim});
{/section}
                //]]></script>

                <table class="iconlist">
{section name=i loop=$lvData.pieces}
                    <tr><th align="right" id="iconlist-icon{$smarty.section.i.index + 1}"></th><td><span class="q{$lvData.pieces[i].quality}"><a href="?item={$lvData.pieces[i].id}">{$lvData.pieces[i].name}</a></span></td></tr>
{/section}
                </table>

                <script type="text/javascript">//<![CDATA[
{section name=i loop=$lvData.pieces}
                    ge('iconlist-icon{$smarty.section.i.index + 1}').appendChild(g_items.createIcon({$lvData.pieces[i].id}, 0, 0));
{/section}
                //]]></script>

{if $lvData.page.unavailable}
                <div class="pad"></div><b style="color: red">{$lang._unavailable}</b>
{/if}

                <h3>{$lang._setBonuses}{$lvData.page.bonusExt}</h3>

                {$lang._conveyBonus}
                <ul>
{section name=i loop=$lvData.spells}
                    <li><div>{$lvData.spells[i].bonus} {$lang._pieces}: <a href="?spell={$lvData.spells[i].id}">{$lvData.spells[i].desc}</a></div></li>
{/section}
                </ul>

                <h2 class="clear">Summary</h2>

                <div id="summary-generic"></div>
                <script type="text/javascript">//<![CDATA[
                    new Summary({ldelim} id: 'itemset', template: 'itemset', parent: 'summary-generic', groups: [[[{']],[['|implode:$lvData.compare.items}]]], level: {$lvData.compare.level}{rdelim});
                //]]></script>

                <h2 class="clear">{$lang.related}</h2>
            </div>

            <div id="tabs-generic"></div>
            <div id="listview-generic" class="listview"></div>
            <script type="text/javascript">//<![CDATA[
                var tabsRelated = new Tabs({ldelim}parent: ge('tabs-generic'){rdelim});
{if !empty($lvData.related)}            {include file='bricks/listviews/itemset.tpl' data=$lvData.related.data   params=$lvData.related.params} {/if}
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
