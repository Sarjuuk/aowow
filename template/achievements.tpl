{include file='header.tpl'}

    <div id="main">
        <div id="main-precontents"></div>
        <div id="main-contents" class="main-contents">

{if !empty($announcements)}
    {foreach from=$announcements item=item}
        {include file='bricks/announcement.tpl' an=$item}
    {/foreach}
{/if}

            <script type="text/javascript">
                g_initPath({$page.path}, {if empty($filter.query)} 0 {else} 1 {/if});
                {if isset($filter.query)}Menu.append(mn_database[1], '&filter={$filter.query}'); // todo: menu order varies per locale{/if}
            </script>

            <div id="fi" style="display:{if empty($filter.query)}none{else}block{/if};">
                <form action="?achievements{$page.subCat}&filter" method="post" name="fi" onsubmit="return fi_submit(this)" onreset="return fi_reset(this)">
                <table>
                    <tr>
                        <td>{$lang.name}: </td>
                        <td colspan="3">
                            <table><tr>
                                <td>&nbsp;<input type="text" name="na" size="30" {if isset($filter.na)}value="{$filter.na}"{/if}/></td>
                                <td>&nbsp; <input type="checkbox" name="ex" value="on" id="achievement-ex" {if isset($filter.ex)}checked="checked"{/if}/></td>
                                <td><label for="achievement-ex"><span class="tip" onmouseover="Tooltip.showAtCursor(event, LANG.tooltip_extendedachievementsearch, 0, 0, 'q')" onmousemove="Tooltip.cursorUpdate(event)" onmouseout="Tooltip.hide()">{$lang.extSearch}</span></label></td>
                            </tr></table>
                        </td>
                    </tr>
                    <tr>
                        <td class="padded">{$lang.side}: </td>
                        <td class="padded">&nbsp;<select name="si">
                            <option></option>
                            {foreach from=$lang.si key=i item=str}{if $str}
                                <option value="{$i}" {if isset($filter.si) && $filter.si == $i}selected{/if}>{$str}</option>
                            {/if}{/foreach}
                            </select>
                        </td>
                        <td class="padded"><table><tr>
                            <td>&nbsp;&nbsp;&nbsp;{$lang.points}: </td>
                            <td>&nbsp;<input type="text" name="minpt" maxlength="2" class="smalltextbox" {if isset($filter.minpt)}value="{$filter.minpt}"{/if}/> - <input type="text" name="maxpt" maxlength="2" class="smalltextbox" {if isset($filter.maxpt)}value="{$filter.maxpt}"{/if}/></td>
                        </tr></table></td>
                    </tr>
                </table>
                <div id="fi_criteria" class="padded criteria"><div></div></div>
                <div><a href="javascript:;" id="fi_addcriteria" onclick="fi_addCriterion(this); return false">{$lang.addFilter}</a></div>
                <div class="padded2">{$lang.match}: <input type="radio" name="ma" value="" id="ma-0" {if !isset($filter.ma)}checked="checked"{/if} /><label for="ma-0">{$lang.allFilter}</label><input type="radio" name="ma" value="1" id="ma-1" {if isset($filter.ma)}checked="checked"{/if} /><label for="ma-1">{$lang.oneFilter}</label></div>

                <div class="clear"></div>

                <div class="padded"></div>
                <input type="submit" value="{$lang.applyFilter}" />
                <input type="reset" value="{$lang.resetForm}" />
                <div style="float: right">{$lang.refineSearch}</div>
                </form>
                <div class="pad"></div>
            </div>

            <script type="text/javascript">//<![CDATA[
                fi_init('achievements');
                {if isset($filter.setCr)}{$filter.setCr}{/if}
            //]]></script>

            <div id="listview-generic" class="listview"></div>
            <script type="text/javascript">
                {include file='bricks/listviews/achievement.tpl' data=$lvData.data params=$lvData.params}
            </script>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}