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

{include file='bricks/infobox.tpl' info=$lvData.infobox series=$lvData.series}

            <div class="text">
{include file='bricks/redButtons.tpl'}

                <div id="h1-icon-generic" class="h1-icon"></div>
                <script type="text/javascript">//<![CDATA[
                    $WH.ge('h1-icon-generic').appendChild(Icon.create('{$lvData.page.iconname|escape:"javascript"}', 1));
                //]]></script>

                <h1 class="h1-icon">{$lvData.page.name}</h1>

                {$lvData.page.description}

                {if !empty($lvData.page.criteria)}<h3>{$lang.criteria}{if $lvData.page.count} &ndash; <small><b>{$lang.requires} {$lvData.page.count} {$lang.outOf} {$lvData.page.nCriteria}</b></small>{/if}</h3>{/if}

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
{foreach from=$lvData.page.icons key=k item=ic}
                    $WH.ge('iconlist-icon{$ic.itr}').appendChild({$ic.type}.createIcon({$ic.id}, 0, {if isset($ic.count) && $ic.count > 0}{$ic.count}{else}0{/if}));
{/foreach}
                //]]></script>

                <div style="clear: left"></div>

                {if $lvData.page.itemReward}                {* for items *}
                    <h3>{$lang.rewards}</h3>
                    {$lang.itemReward}<table class="icontab">
                    <tr>
{foreach from=$lvData.page.itemReward item=i name=item key=id}
                        <th id="icontab-icon{$smarty.foreach.item.index}"></th><td><span class="q{$i.quality}"><a href="?item={$id}">{$i.name}</a></span></td>
{/foreach}
                    <script type="text/javascript">//<![CDATA[
{foreach from=$lvData.page.itemReward item=i name=item key=id}
                        $WH.ge('icontab-icon{$smarty.foreach.item.index}').appendChild(g_items.createIcon({$id}, 1, 1));
{/foreach}
                    //]]></script>
                    </tr>
                    </table>
                {/if}

                {if $lvData.page.titleReward}               {* for titles *}
                    <h3>{$lang.gains}</h3>
                    <ul>
{foreach from=$lvData.page.titleReward item=i}
                        <li><div>{$i}</div></li>
{/foreach}
                    </ul>
                {/if}
                
                {if !$lvData.page.titleReward && !$lvData.page.itemReward && $lvData.page.reward}
                    <h3>{$lang.rewards}</h3>
                    <ul>
                        <li><div>{$lvData.page.reward}</div></li>
                    </ul>
                {/if}
                

                <h2>{$lang.related}</h2>
            </div>

{include file='bricks/tabsRelated.tpl' tabs=$lvData.relTabs}

{include file='bricks/contribute.tpl'}

        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}
