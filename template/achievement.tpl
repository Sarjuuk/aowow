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
                var g_pageInfo = {ldelim}type: {$page.type}, typeId: {$page.typeId}, name: '{$lvData.page.name|escape:"quotes"}'{rdelim}; // username:XXX in profiles
                g_initPath({$page.path});
            //]]></script>

{* include file='bricks/infobox.tpl' *}

            <table class="infobox">
            <tr><th>{$lang.quickFacts}</th></tr>
            <tr><td><div class="infobox-spacer"></div>
                <ul>
                    {if $lvData.page.points}<li><div>{$lang.points}: <span class="moneyachievement tip" onmouseover="Listview.funcBox.moneyAchievementOver(event)" onmousemove="Tooltip.cursorUpdate(event)" onmouseout="Tooltip.hide()">{$lvData.page.points}</span></div></li>{/if}
{foreach from=$lvData.infoBox item=info}
                    <li><div>{$info}</div></li>
{/foreach}
                    {*<li><div>Location: {$lvData.page.location}</div></li> todo: need to be parsed first *}
                </ul>
            </td></tr>
        {strip}{*************** CHAIN ​​OF ACHIEVEMENTS ***************}
            {if isset($lvData.page.series)}
            <tr><th>{$lang.series}</th></tr>
            <tr><td><div class="infobox-spacer"></div>
                    <table class="series">
{section name=i loop=$lvData.page.series}
                        <tr>
                            <th>{$smarty.section.i.index+1}.</th>
                            <td>
                                {if ($lvData.page.series[i].id == $lvData.page.id)}
                                    <b>{$lvData.page.series[i].name}</b>
                                {else}
                                    <div><a href="?achievement={$lvData.page.series[i].id}">{$lvData.page.series[i].name}</a></div>
                                {/if}
                            </td>
                        </tr>
{/section}
                    </table>
                </td>
            </tr>
            {/if}
         {/strip}{*************** / CHAIN ​​OF ACHIEVEMENTS ***************}
            <tr><th id="infobox-screenshots">{$lang.screenshots}</th></tr>
            <tr><td><div class="infobox-spacer"></div><div id="infobox-sticky-ss"></div></td></tr>
            <tr><th id="infobox-videos">{$lang.videos}</th></tr>
            <tr><td><div class="infobox-spacer"></div><div id="infobox-sticky-vi"></div></td></tr>
            </table>
            <script type="text/javascript">ss_appendSticky()</script>
            <script type="text/javascript">vi_appendSticky()</script>

            <div class="text">

                <div id="h1-icon-generic" class="h1-icon"></div>

                <script type="text/javascript">//<![CDATA[
                    ge('h1-icon-generic').appendChild(Icon.create('{$lvData.page.iconname|escape:"javascript"}', 1));
                //]]></script>

                <a href="javascript:;" id="open-links-button" class="button-red" onclick="this.blur(); Links.show({ldelim} type: 10, typeId: {$lvData.page.id}, linkColor: 'ffffff00', linkId: '{$lvData.page.id}:&quot;..UnitGUID(&quot;player&quot;)..&quot;:0:0:0:0:0:0:0:0', linkName: '{$lvData.page.name|escape:'javascript'}' {rdelim});"><em><b><i>{$lang.links}</i></b><span>{$lang.links}</span></em></a>
                <a href="http://old.wowhead.com/?{$query[0]}={$query[1]}" class="button-red"><em><b><i>Wowhead</i></b><span>Wowhead</span></em></a>
                <h1 class="h1-icon">{$lvData.page.name}</h1>

                {$lvData.page.description}

                {if !empty($lvData.page.criteria)}<h3>{$lang.criteria}{if $lvData.page.count} &ndash; <small><b>{$lang.requires} {$lvData.page.count} {$lang.outOf} {$lvData.page.total_criteria}</b></small>{/if}</h3>{/if}

                <div style="float: left; margin-right: 25px">
                <table class="iconlist">
                {strip}
{foreach from=$lvData.page.criteria item=cr name=criteria}
                    <tr>
                        <th{if isset($cr.icon)} align="right" id="iconlist-icon{$cr.icon}"{/if}>
                        {* for reference and standard entries *}
                        {if !isset($cr.icon) && (isset($cr.link) || $cr.standard)}
                            <ul><li><var>&nbsp;</var></li></ul>
                        {/if}
                        </th>
                        <td>
                            {if isset($cr.link)}<a href="{$cr.link.href}"{if isset($cr.link.quality)} class="q{$cr.link.quality}"{/if}>{$cr.link.text|escape:"html"}</a>{if isset($cr.link.count) && $cr.link.count > 1} ({$cr.link.count}){/if}{/if}

                            {* STANDARD TEXT *}
                            {if isset($cr.extra_text)} {$cr.extra_text}{/if}
                            {if $user.roles > 0} <small title="{$lang.criteriaType} {$cr.type}" class="q0">[{$cr.id}]</small>{/if}
                        </td>
                    </tr>
                    {* If the first column is over (it may be a greater element) *}
                    {if $smarty.foreach.criteria.index+1 == round(count($lvData.page.criteria) / 2)}
                        </table>
                        </div>
                        <div style="float: left">
                        <table class="iconlist">
                    {/if}
{/foreach}
                {/strip}
                </table>
                </div>

                <script type="text/javascript">//<![CDATA[
{foreach from=$lvData.page.icons item=ic}
                    ge('iconlist-icon{$ic.itr}').appendChild({$ic.type}.createIcon({$ic.id}, 0, {if isset($ic.count) && $ic.count > 0}{$ic.count}{else}0{/if}));
{/foreach}
                //]]></script>

                <div style="clear: left"></div>

            {* for items *}
                {if $lvData.page.itemReward}
                    <h3>{$lang.rewards}</h3>
                    {$lang.itemReward}<table class="icontab">
                    <tr>
{foreach from=$lvData.page.itemReward item=i name=item key=id}
                        <th id="icontab-icon{$smarty.foreach.item.index}"></th><td><span class="q{$i.quality}"><a href="?item={$id}">{$i.name}</a></span></td>
{/foreach}
                    <script type="text/javascript">//<![CDATA[
{foreach from=$lvData.page.itemReward item=i name=item key=id}
                        ge('icontab-icon{$smarty.foreach.item.index}').appendChild(g_items.createIcon({$id}, 1, 1));
{/foreach}
                    //]]></script>
                    </tr>
                    </table>
                {/if}

            {* for titles *}
                {if $lvData.page.titleReward}
                    <h3>{$lang.gains}</h3>
                    <ul>
{foreach from=$lvData.page.titleReward item=i}
                        <li><div>{$i}</div></li>
{/foreach}
                    </ul>
                {/if}

                <h2>{$lang.related}</h2>

            </div>

            <div id="tabs-generic"></div>
            <div id="listview-generic" class="listview"></div>
            <script type="text/javascript">//<![CDATA[
                var tabsRelated = new Tabs({ldelim}parent: ge('tabs-generic'){rdelim});
{if $lvData.page.saData}          {include   file='bricks/listviews/achievement.tpl' data=$lvData.page.saData  params=$lvData.page.saParams}{/if}
{if isset($lvData.page.coData)}   {include   file='bricks/listviews/achievement.tpl' data=$lvData.page.coData  params=$lvData.page.coParams}{/if}
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
