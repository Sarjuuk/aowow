{include file='header.tpl'}

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

{if !empty($announcements)}
    {foreach from=$announcements item=item}
        {include file='bricks/announcement.tpl' an=$item}
    {/foreach}
{/if}

            <script type="text/javascript">
                g_initPath({$path}, {if empty($filter.query)}0{else}1{/if});
{if isset($filter.query)}
                Menu.modifyUrl(Menu.findItem(mn_database, [9]), {ldelim} filter: '+={$filter.query|escape:'quotes'}' {rdelim}, {ldelim} onAppendCollision: fi_mergeFilterParams, onAppendEmpty: fi_setFilterParams, menuUrl: Menu.getItemUrl(Menu.findItem(mn_database, [9])) {rdelim});
{/if}
            </script>

            <div id="fi" style="display: {if empty($filter.query)}none{else}block{/if};">
                <form action="?achievements{$subCat}&filter" method="post" name="fi" onsubmit="return fi_submit(this)" onreset="return fi_reset(this)">
                    <table>
                        <tr>
                            <td>{$lang.name|ucFirst}{$lang.colon}</td>
                            <td colspan="3">
                                <table><tr>
                                    <td>&nbsp;<input type="text" name="na" size="30" {if isset($filter.na)}value="{$filter.na}"{/if}/></td>
                                    <td>&nbsp; <input type="checkbox" name="ex" value="on" id="achievement-ex" {if isset($filter.ex)}checked="checked"{/if}/></td>
                                    <td><label for="achievement-ex"><span class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, LANG.tooltip_extendedachievementsearch, 0, 0, 'q')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">{$lang.extSearch}</span></label></td>
                                </tr></table>
                            </td>
                        </tr><tr>
                            <td class="padded">{$lang.side}{$lang.colon}</td>
                            <td class="padded">&nbsp;<select name="si">
                                <option></option>
{foreach from=$lang.si key=i item=str}{if $str}
                                    <option value="{$i}" {if isset($filter.si) && $filter.si == $i}selected{/if}>{$str}</option>
{/if}{/foreach}
                                </select>
                            </td>
                            <td class="padded"><table><tr>
                                <td>&nbsp;&nbsp;&nbsp;{$lang.points}{$lang.colon}</td>
                                <td>&nbsp;<input type="text" name="minpt" maxlength="2" class="smalltextbox" {if isset($filter.minpt)}value="{$filter.minpt}"{/if}/> - <input type="text" name="maxpt" maxlength="2" class="smalltextbox" {if isset($filter.maxpt)}value="{$filter.maxpt}"{/if}/></td>
                            </tr></table></td>
                        </tr>
                    </table>

                    <div id="fi_criteria" class="padded criteria"><div></div></div>
                    <div><a href="javascript:;" id="fi_addcriteria" onclick="fi_addCriterion(this); return false">{$lang.addFilter}</a></div>

                    <div class="padded2">
                        <div style="float: right">{$lang.refineSearch}</div>
                        {$lang.match}{$lang.colon}<input type="radio" name="ma" value="" id="ma-0" {if !isset($filter.ma)}checked="checked"{/if} /><label for="ma-0">{$lang.allFilter}</label><input type="radio" name="ma" value="1" id="ma-1" {if isset($filter.ma)}checked="checked"{/if} /><label for="ma-1">{$lang.oneFilter}</label>
                    </div>

                    <div class="clear"></div>

                    <div class="padded">
                        <input type="submit" value="{$lang.applyFilter}" />
                        <input type="reset" value="{$lang.resetForm}" />
                    </div>

                </form>
                <div class="pad"></div>
            </div>

            <script type="text/javascript">//<![CDATA[
                fi_init('achievements');
{foreach from=$filter.fi item=str}
    {$str}
{/foreach}
            //]]></script>

            <div id="lv-generic" class="listview"></div>
            <script type="text/javascript">//<![CDATA[
{include file='listviews/achievement.tpl' data=$lvData.data params=$lvData.params}
            //]]></script>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}
