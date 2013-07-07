{include file='header.tpl'}
{assign var="iconlist1" value="1"}
{assign var="iconlist2" value="1"}
    <div id="main">
        <div id="main-precontents" class="main-precontents"></div>
        <div id="main-contents" class="main-contents">

{if !empty($announcements)}
    {foreach from=$announcements item=item}
        {include file='bricks/announcement.tpl' an=$item}
    {/foreach}
{/if}

            <script type="text/javascript">
{include file='bricks/community.tpl'}
                var g_pageInfo = {ldelim}type: {$page.type}, typeId: {$page.typeId}, name: '{$lvData.page.name|escape:"javascript"}'{rdelim};
                g_initPath({$page.path});
            </script>

{include file='bricks/infobox.tpl'}

            </table>

        <div class="text">
            <a href="javascript:;" id="open-links-button" class="button-red" onclick="this.blur(); Links.show({ldelim} type: 6, typeId: {$lvData.page.id}, linkColor: 'ff71d5ff', linkId: 'spell:{$lvData.page.id}', linkName: '{$lvData.page.name}' {rdelim});"><em><b><i>{$lang.links}</i></b><span>{$lang.links}</span></em></a>
            <a href="javascript:;" id="view3D-button" class="button-red{if $lvData.view3D}" onclick="this.blur(); ModelViewer.show({ldelim} type: 1, displayId: {$lvData.view3D} {rdelim}){else} button-red-disabled{/if}"><em><b><i>View in 3D</i></b><span>View in 3D</span></em></a>
            <a href="http://old.wowhead.com/?{$query[0]}={$query[1]}" class="button-red"><em><b><i>Wowhead</i></b><span>Wowhead</span></em></a>
            <h1>{$lvData.page.name}</h1>

                <div id="headicon-generic" style="float: left"></div>
                <div id="tooltip{$lvData.page.id}-generic" class="tooltip" style="float: left; padding-top: 1px">
                    <table><tr><td>{$lvData.page.info}</td><th style="background-position: top right"></th></tr><tr><th style="background-position: bottom left"></th><th style="background-position: bottom right"></th></tr></table>
                </div>
                <div style="clear: left"></div>

                <script type="text/javascript">
                    ge('headicon-generic').appendChild(Icon.create('{$lvData.page.icon}', 2, 0, 0, {$lvData.page.stack}));
                    Tooltip.fix(ge('tooltip{$lvData.page.id}-generic'), 1, 1);
                </script>

                {if !empty($lvData.page.buff)}
                <h3>{$lang._aura}</h3>
                <div id="btt{$lvData.page.id}" class="tooltip">
                    <table><tr><td>{$lvData.page.buff}</td><th style="background-position: top right"></th></tr><tr><th style="background-position: bottom left"></th><th style="background-position: bottom right"></th></tr></table>
                </div>
                <script type="text/javascript">
                    Tooltip.fixSafe(ge('btt{$lvData.page.id}'), 1, 1)
                </script>
                {/if}

{if $lvData.page.reagents}{if $lvData.page.tools}<div style="float: left; margin-right: 75px">{/if}
                        <h3>{$lang.reagents}</h3>
                        <table class="iconlist">
{section name=i loop=$lvData.page.reagents}
                            <tr><th align="right" id="iconlist-icon{$iconlist1++}"></th><td><span class="q{$lvData.page.reagents[i].quality}"><a href="?item={$lvData.page.reagents[i].entry}">{$lvData.page.reagents[i].name}</a></span>{if $lvData.page.reagents[i].count > 1}&nbsp({$lvData.page.reagents[i].count}){/if}</td></tr>
{/section}
                        </table>
                        <script type="text/javascript">
{section name=i loop=$lvData.page.reagents}
                            ge('iconlist-icon{$iconlist2++}').appendChild(g_items.createIcon({$lvData.page.reagents[i].entry}, 0, {$lvData.page.reagents[i].count}));
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
                            ge('iconlist-icon{$iconlist2++}').appendChild(g_items.createIcon({$lvData.page.tools[i].itemId}, 0, 1));
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
                                <td width="100%" style="border-top: 0">{$lvData.page.duration}</td>
                            </tr>
                            <tr>
                                <th style="border-left: 0">{$lang.school}</th>
                                <td>{$lvData.page.school}</td>
                            </tr>
                            <tr>
                                <th style="border-left: 0">{$lang.mechanic}</th>
                                <td>{$lvData.page.mechanic}</td>
                            </tr>
                            <tr>
                                <th style="border-left: 0">{$lang.dispelType}</th>
                                <td>{$lvData.page.dispel}</td>
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
                        <td>{$lvData.page.cooldown}</td>
                    </tr>
                    <tr>
                        <th><dfn title="{$lang._globCD}">{$lang._gcd}</dfn></th>
                        <td>{$lvData.page.gcd}</td>
                    </tr>
{if $lvData.page.stances}
                    <tr>
                        <th>{$lang._forms}</th>
                        <td colspan="3">{$lvData.page.stances}</td>
                    </tr>
{/if}
{if $lvData.page.items}
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
                            {if isset($lvData.page.effect[i].interval)}<br>{$lang._interval}{$lang.colon}{$lvData.page.effect[i].interval} {$lang.seconds}{/if}
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
                                ge('icontab-icon{$smarty.section.i.index}').appendChild({if isset($lvData.page.effect[i].icon.quality)}g_items{else}g_spells{/if}.createIcon({$lvData.page.effect[i].icon.id}, 1, {$lvData.page.effect[i].icon.count}));
                            </script>
{/if}
                        </td>
                    </tr>
{/section}
                </table>

                <h2>{$lang.related}</h2>

            </div>

            <div id="tabs-generic"></div>
            <div id="listview-generic" class="listview"></div>
            <script type="text/javascript">//<![CDATA[
                var tabsRelated = new Tabs({ldelim}parent: ge('tabs-generic'){rdelim});
{if isset($lvData.modifiedBy)}    {include file='bricks/listviews/spell.tpl'       data=$lvData.modifiedBy.data    params=$lvData.modifiedBy.params   } {/if}
{if isset($lvData.modifies)}      {include file='bricks/listviews/spell.tpl'       data=$lvData.modifies.data      params=$lvData.modifies.params     } {/if}
{if isset($lvData.seeAlso)}       {include file='bricks/listviews/spell.tpl'       data=$lvData.seeAlso.data       params=$lvData.seeAlso.params      } {/if}
{if isset($lvData.usedByItem)}    {include file='bricks/listviews/item.tpl'        data=$lvData.usedByItem.data    params=$lvData.usedByItem.params   } {/if}
{if isset($lvData.usedByItemset)} {include file='bricks/listviews/itemset.tpl'     data=$lvData.usedByItemset.data params=$lvData.usedByItemset.params} {/if}
{if isset($lvData.criteriaOf)}    {include file='bricks/listviews/achievement.tpl' data=$lvData.criteriaOf.data    params=$lvData.criteriaOf.params   } {/if}
{if isset($lvData.contains)}      {include file='bricks/listviews/item.tpl'        data=$lvData.contains.data      params=$lvData.contains.params     } {/if}

{if isset($lvData.taughtbynpc)}   {include file='bricks/listviews/creature.tpl'    data=$lvData.taughtbynpc.data   params=$lvData.taughtbynpc.params  } {/if}
{if isset($lvData.taughtbyitem)}  {include file='bricks/listviews/item.tpl'        data=$lvData.taughtbyitem.data  params=$lvData.taughtbyitem.params } {/if}
{if isset($lvData.taughtbyquest)} {include file='bricks/listviews/quest.tpl'       data=$lvData.taughtbyquest.data params=$lvData.taughtbyquest.params} {/if}
{if isset($lvData.questreward)}   {include file='bricks/listviews/quest.tpl'       data=$lvData.questreward.data   params=$lvData.questreward.params  } {/if}
{if isset($lvData.usedbynpc)}     {include file='bricks/listviews/creature.tpl'    data=$lvData.usedbynpc.data     params=$lvData.usedbynpc.params    } {/if}
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
