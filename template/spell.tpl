{include file='header.tpl'}
{assign var="iconlist1" value="1"}
{assign var="iconlist2" value="1"}

    <div class="main" id="main">
        <div id="main-precontents" class="main-precontents"></div>
        <div id="main-contents" class="main-contents">

{if !empty($announcements)}
    {foreach from=$announcements item=item}
        {include file='bricks/announcement.tpl' an=$item}
    {/foreach}
{/if}

            <script type="text/javascript">//<![CDATA[
{include file='bricks/community.tpl'}
                var g_pageInfo = {ldelim}type: {$page.type}, typeId: {$page.typeId}, name: '{$lvData.page.name|escape:"javascript"}'{rdelim};
                g_initPath({$page.path});
            //]]></script>

{include file='bricks/infobox.tpl' info=$lvData.infobox}

            <div class="text">
                <a href="javascript:;" id="open-links-button" class="button-red" onclick="this.blur(); Links.show({ldelim} type: {$page.type}, typeId: {$page.typeId}, linkColor: 'ff71d5ff', linkId: 'spell:{$page.typeId}', linkName: '{$lvData.page.name}' {rdelim});"><em><b><i>{$lang.links}</i></b><span>{$lang.links}</span></em></a>
                <a href="javascript:;" id="view3D-button" class="button-red{if $lvData.view3D}" onclick="this.blur(); ModelViewer.show({ldelim} type: 1, displayId: {$lvData.view3D} {rdelim}){else} button-red-disabled{/if}"><em><b><i>View in 3D</i></b><span>View in 3D</span></em></a>
                <a href="{$wowhead}" class="button-red"><em><b><i>Wowhead</i></b><span>Wowhead</span></em></a>
                <h1>{$lvData.page.name}</h1>

{include file='bricks/tooltip.tpl'}

{if $lvData.page.reagents}{if $lvData.page.tools}<div style="float: left; margin-right: 75px">{/if}
                        <h3>{$lang.reagents}</h3>
                        <table class="iconlist">
{section name=i loop=$lvData.page.reagents}
                            <tr><th align="right" id="iconlist-icon{$iconlist1++}"></th><td><span class="q{$lvData.page.reagents[i].quality}"><a href="?item={$lvData.page.reagents[i].entry}">{$lvData.page.reagents[i].name}</a></span>{if $lvData.page.reagents[i].count > 1}&nbsp({$lvData.page.reagents[i].count}){/if}</td></tr>
{/section}
                        </table>
                        <script type="text/javascript">
{section name=i loop=$lvData.page.reagents}
                            $WH.ge('iconlist-icon{$iconlist2++}').appendChild(g_items.createIcon({$lvData.page.reagents[i].entry}, 0, {$lvData.page.reagents[i].count}));
{/section}
                        </script>
{if $lvData.page.tools}</div>{/if}{/if}
{if $lvData.page.tools}{if $lvData.page.reagents}<div style="float: left">{/if}
                        <h3>{$lang.tools}</h3>
                        <table class="iconlist">
{section name=i loop=$lvData.page.tools}
                            <tr><th align="right" id="iconlist-icon{$iconlist1++}"></th><td><span class="q1"><a href="{$lvData.page.tools[i].url}">{$lvData.page.tools[i].name}</a></span></td></tr>
{/section}
                        </table>
                        <script type="text/javascript">
{section name=i loop=$lvData.page.tools}{if isset($lvData.page.tools[i].itemId)}
                            $WH.ge('iconlist-icon{$iconlist2++}').appendChild(g_items.createIcon({$lvData.page.tools[i].itemId}, 0, 1));
{/if}{/section}
                        </script>
{if $lvData.page.reagents}</div>{/if}{/if}

                <div class="clear"></div>

{include file='bricks/article.tpl'}

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
                                <td width="100%" style="border-top: 0">{if !empty($lvData.page.duration)}{$lvData.page.duration}{else}<span class="q0">{$lang.n_a}</span>{/if}</td>
                            </tr>
                            <tr>
                                <th style="border-left: 0">{$lang.school}</th>
                                <td>{$lvData.page.school}</td>
                            </tr>
                            <tr>
                                <th style="border-left: 0">{$lang.mechanic}</th>
                                <td width="100%" style="border-top: 0">{if $lvData.page.mechanic}{$lvData.page.mechanic}{else}<span class="q0">{$lang.n_a}</span>{/if}</td>
                            </tr>
                            <tr>
                                <th style="border-left: 0">{$lang.dispelType}</th>
                                <td width="100%" style="border-top: 0">{if $lvData.page.dispel}{$lvData.page.dispel}{else}<span class="q0">{$lang.n_a}</span>{/if}</td>
                            </tr>
                            <tr>
                                <th style="border-bottom: 0; border-left: 0">{$lang._gcdCategory}</th>
                                <td style="border-bottom: 0">{$lvData.page.gcdCat}</td>
                            </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <th style="border-top: 0">{$lang._cost}</th>
                        <td style="border-top: 0">{if !empty($lvData.page.powerCost)}{$lvData.page.powerCost}{else}{$lang._none}{/if}</td>
                    </tr>
                    <tr>
                        <th>{$lang._range}</th>
                        <td>{$lvData.page.range} {$lang._distUnit} <small>({$lvData.page.rangeName})</small></td>
                    </tr>
                    <tr>
                        <th>{$lang._castTime}</th>
                        <td>{$lvData.page.castTime}</td>
                    </tr>
                    <tr>
                        <th>{$lang._cooldown}</th>
                        <td>{if !empty($lvData.page.cooldown)}{$lvData.page.cooldown}{else}<span class="q0">{$lang.n_a}</span>{/if}</td>
                    </tr>
                    <tr>
                        <th><dfn title="{$lang._globCD}">{$lang._gcd}</dfn></th>
                        <td>{$lvData.page.gcd}</td>
                    </tr>
{if !empty($lvData.page.stances)}
                    <tr>
                        <th>{$lang._forms}</th>
                        <td colspan="3">{$lvData.page.stances}</td>
                    </tr>
{/if}
{if !empty($lvData.page.items)}
                    <tr>
                        <th>{$lang.requires2}</th>
                        <td colspan="3">{$lvData.page.items}</td>
                    </tr>
{/if}
{section name=i loop=$lvData.page.effect}
                    <tr>
                        <th>{$lang._effect} #{$smarty.section.i.index+1}</th>
                        <td colspan="3" style="line-height: 17px">
                            {$lvData.page.effect[i].name}

                            <small>
                            {if isset($lvData.page.effect[i].value)}<br>{$lang._value}{$lang.colon}{$lvData.page.effect[i].value}{/if}
                            {if isset($lvData.page.effect[i].radius)}<br>{$lang._radius}{$lang.colon}{$lvData.page.effect[i].radius} {$lang._distUnit}{/if}
                            {if isset($lvData.page.effect[i].interval)}<br>{$lang._interval}{$lang.colon}{$lvData.page.effect[i].interval}{/if}
                            {if isset($lvData.page.effect[i].mechanic)}<br>{$lang.mechanic}{$lang.colon}{$lvData.page.effect[i].mechanic}{/if}
                            </small>
{if isset($lvData.page.effect[i].icon)}
                            <table class="icontab">
                                <tr>
                                    <th id="icontab-icon{$smarty.section.i.index}"></th>
{if isset($lvData.page.effect[i].icon.quality)}
                                    <td><span class="q{$lvData.page.effect[i].icon.quality}"><a href="?item={$lvData.page.effect[i].icon.id}">{$lvData.page.effect[i].icon.name}</a></span></td>
{else}
                                    <td><a href="?spell={$lvData.page.effect[i].icon.id}">{$lvData.page.effect[i].icon.name}</a></td>
{/if}
                                    <th></th><td></td>
                                </tr>
                            </table>
                            <script type="text/javascript">
                                $WH.ge('icontab-icon{$smarty.section.i.index}').appendChild({if isset($lvData.page.effect[i].icon.quality)}g_items{else}g_spells{/if}.createIcon({$lvData.page.effect[i].icon.id}, 1, {$lvData.page.effect[i].icon.count}));
                            </script>
{/if}
                        </td>
                    </tr>
{/section}
                </table>

                <h2>{$lang.related}</h2>
            </div>

{include file='bricks/tabsRelated.tpl' tabs=$lvData.relTabs}

{include file='bricks/contribute.tpl'}

        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}
