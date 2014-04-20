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
{*include file='bricks/headIcons.tpl'*}

{include file='bricks/redButtons.tpl'}

                <h1 class="h1-icon">{$name}</h1>

{include file='bricks/tooltip.tpl'}

{if $reagents[0]}
    {if $tools}<div style="float: left; margin-right: 75px">{/if}
{include file='bricks/reagentList.tpl' reagents=$reagents[1] enhanced=$reagents[0]}
    {if $tools}</div>{/if}
{/if}

{if $tools}
    {if $reagents[0]}<div style="float: left">{/if}
                        <h3>{$lang.tools}</h3>
                        <table class="iconlist">
{section name=i loop=$tools}
                            <tr><th align="right" id="iconlist-icon{$iconlist1++}"></th><td><span class="q1"><a href="{$tools[i].url}">{$tools[i].name}</a></span></td></tr>
{/section}
                        </table>
                        <script type="text/javascript">
{section name=i loop=$tools}{if isset($tools[i].itemId)}
                            $WH.ge('iconlist-icon{$iconlist2++}').appendChild(g_items.createIcon({$tools[i].itemId}, 0, 1));
{/if}{/section}
                        </script>
    {if $reagents[0]}</div>{/if}
{/if}

                <div class="clear"></div>

{include file='bricks/article.tpl'}

{*
if !empty($transfer)}
    <div class="pad"></div>
        {$lang._transfer|sprintf:$transfer.id:´´:$transfer.icon:$transfer.name:$transfer.facInt:$transfer.facName}
{/if}
*}

{if isset($unavailable)}
                <div class="pad"></div>
                <b style="color: red">{$lang._unavailable}</b>
{/if}

                <h3>{$lang._spellDetails}</h3>

                <table class="grid" id="spelldetails">
                    <colgroup>
                        <col width="8%" />
                        <col width="42%" />
                        <col width="50%" />
                    </colgroup>
                    <tr>
                        <td colspan="2" style="padding: 0; border: 0; height: 1px"></td>
                        <td rowspan="6" style="padding: 0; border-left: 3px solid #404040">
                            <table class="grid" style="border: 0">
                            <tr>
                                <td style="height: 0; padding: 0; border: 0" colspan="2"></td>
                            </tr>
                            <tr>
                                <th style="border-left: 0; border-top: 0">{$lang.duration}</th>
                                <td width="100%" style="border-top: 0">{if !empty($duration)}{$duration}{else}<span class="q0">{$lang.n_a}</span>{/if}</td>
                            </tr>
                            <tr>
                                <th style="border-left: 0">{$lang.school}</th>
                                <td>{$school}</td>
                            </tr>
                            <tr>
                                <th style="border-left: 0">{$lang.mechanic}</th>
                                <td width="100%" style="border-top: 0">{if $mechanic}{$mechanic}{else}<span class="q0">{$lang.n_a}</span>{/if}</td>
                            </tr>
                            <tr>
                                <th style="border-left: 0">{$lang.dispelType}</th>
                                <td width="100%" style="border-top: 0">{if $dispel}{$dispel}{else}<span class="q0">{$lang.n_a}</span>{/if}</td>
                            </tr>
                            <tr>
                                <th style="border-bottom: 0; border-left: 0">{$lang._gcdCategory}</th>
                                <td style="border-bottom: 0">{if $gcdCat}{$gcdCat}{else}<span class="q0">{$lang.n_a}</span>{/if}</td>
                            </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <th style="border-top: 0">{$lang._cost}</th>
                        <td style="border-top: 0">{if !empty($powerCost)}{$powerCost}{else}{$lang._none}{/if}</td>
                    </tr>
                    <tr>
                        <th>{$lang._range}</th>
                        <td>{$range} {$lang._distUnit} <small>({$rangeName})</small></td>
                    </tr>
                    <tr>
                        <th>{$lang._castTime}</th>
                        <td>{$castTime}</td>
                    </tr>
                    <tr>
                        <th>{$lang._cooldown}</th>
                        <td>{if !empty($cooldown)}{$cooldown}{else}<span class="q0">{$lang.n_a}</span>{/if}</td>
                    </tr>
                    <tr>
                        <th><dfn title="{$lang._globCD}">{$lang._gcd}</dfn></th>
                        <td>{$gcd}</td>
                    </tr>
{if !empty($scaling)}
                    <tr>
                        <th>{$lang._scaling}</th>
                        <td colspan="3">{$scaling}</td>
                    </tr>
{/if}
{if !empty($stances)}
                    <tr>
                        <th>{$lang._forms}</th>
                        <td colspan="3">{$stances}</td>
                    </tr>
{/if}
{if !empty($items)}
                    <tr>
                        <th>{$lang.requires2}</th>
                        <td colspan="3">{$items}</td>
                    </tr>
{/if}
{section name=i loop=$effect}
                    <tr>
                        <th>{$lang._effect} #{$smarty.section.i.index+1}</th>
                        <td colspan="3" style="line-height: 17px">
                            {$effect[i].name}

                            <small>
                            {if isset($effect[i].value)}<br>{$lang._value}{$lang.colon}{$effect[i].value}{/if}
                            {if isset($effect[i].radius)}<br>{$lang._radius}{$lang.colon}{$effect[i].radius} {$lang._distUnit}{/if}
                            {if isset($effect[i].interval)}<br>{$lang._interval}{$lang.colon}{$effect[i].interval}{/if}
                            {if isset($effect[i].mechanic)}<br>{$lang.mechanic}{$lang.colon}{$effect[i].mechanic}{/if}
                            {if isset($effect[i].procData)}<br>{if $effect[i].procData[0] < 0}{$lang.ppm|sprintf:$effect[i].procData[0]*-1}{else}{$lang.procChance}{$lang.colon}{$effect[i].procData[0]}%{/if}{if $effect[i].procData[1]} ({$lang.cooldown|sprintf:$effect[i].procData[1]}){/if}{/if}
                            </small>
{if isset($effect[i].icon)}
                            <table class="icontab">
                                <tr>
                                    <th id="icontab-icon{$smarty.section.i.index}"></th>
{if isset($effect[i].icon.quality)}
                                    <td><span class="q{$effect[i].icon.quality}"><a href="?item={$effect[i].icon.id}">{$effect[i].icon.name}</a></span></td>
{else}
                                    <td>{if !$effect[i].icon.name|strpos:"#"}<a href="?spell={$effect[i].icon.id}">{/if}{$effect[i].icon.name}{if !$effect[i].icon.name|strpos:"#"}</a>{/if}</td>
{/if}
                                    <th></th><td></td>
                                </tr>
                            </table>
                            <script type="text/javascript">
                                $WH.ge('icontab-icon{$smarty.section.i.index}').appendChild({if isset($effect[i].icon.quality)}g_items{else}g_spells{/if}.createIcon({$effect[i].icon.id}, 1, {$effect[i].icon.count}));
                            </script>
{/if}
                        </td>
                    </tr>
{/section}
                </table>

                <h2 class="clear">{$lang.related}</h2>
            </div>

{include file='bricks/tabsRelated.tpl' tabs=$lvData}

{include file='bricks/contribute.tpl'}

        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}
