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

                <h1{if isset($expansion)} class="h1-icon"><span class="{$expansion}-icon-right">{$name}</span>{else}>{$name}{/if}</h1>

{include file='bricks/article.tpl'}

{$description}

                <script type="text/javascript">//<![CDATA[
{section name=i loop=$pieces}
                    g_items.add({$pieces[i].id}, {ldelim}name_{$user.language}:'{$pieces[i].name|escape:'javascript'}', quality:{$pieces[i].quality}, icon:'{$pieces[i].icon}', jsonequip:{$pieces[i].json}{rdelim});
{/section}
                //]]></script>

                <table class="iconlist">
{section name=i loop=$pieces}
                    <tr><th align="right" id="iconlist-icon{$smarty.section.i.index + 1}"></th><td><span class="q{$pieces[i].quality}"><a href="?item={$pieces[i].id}">{$pieces[i].name}</a></span></td></tr>
{/section}
                </table>

                <script type="text/javascript">//<![CDATA[
{section name=i loop=$pieces}
                    $WH.ge('iconlist-icon{$smarty.section.i.index + 1}').appendChild(g_items.createIcon({$pieces[i].id}, 0, 0));
{/section}
                //]]></script>

{if $unavailable}
                <div class="pad"></div>
                <b style="color: red">{$lang._unavailable}</b>
{/if}

                <h3>{$lang._setBonuses}{$bonusExt}</h3>

                {$lang._conveyBonus}
                <ul>
{section name=i loop=$spells}
                    <li><div>{$spells[i].bonus} {$lang._pieces}{$lang.colon}<a href="?spell={$spells[i].id}">{$spells[i].desc}</a></div></li>
{/section}
                </ul>

                <h2 class="clear">{$lang.summary}</h2>

                <div id="summary-generic"></div>
                <script type="text/javascript">//<![CDATA[
                    new Summary({ldelim} id: 'itemset', template: 'itemset', parent: 'summary-generic', groups: [[[{']],[['|implode:$compare.items}]]], level: {$compare.level}{rdelim});
                //]]></script>

                <h2 class="clear">{$lang.related}</h2>
            </div>

{include file='bricks/tabsRelated.tpl' tabs=$lvData}

{include file='bricks/contribute.tpl'}

        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}
