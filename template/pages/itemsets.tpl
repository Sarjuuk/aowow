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
                g_initPath({$path}, {if empty($filter.query)} 0 {else} 1 {/if});
{if !empty($filter.query)}
                Menu.modifyUrl(Menu.findItem(mn_database, [2]), {ldelim} filter: '+={$filter.query|escape:'quotes'}' {rdelim}, {ldelim} onAppendCollision: fi_mergeFilterParams, onAppendEmpty: fi_setFilterParams, menuUrl: Menu.getItemUrl(Menu.findItem(mn_database, [2])) {rdelim});
{/if}
            </script>

            <div id="fi" style="display:{if empty($filter.query)}none{else}block{/if};">
                <form action="?itemsets&filter" method="post" name="fi" onsubmit="return fi_submit(this)" onreset="return fi_reset(this)">
                    <div class="rightpanel">
                        <div style="float: left">{$lang._quality}{$lang.colon}</div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['qu[]'].selectedIndex = -1; return false" onmousedown="return false">{$lang.clear}</a></small>
                        <div class="clear"></div>
                        <select name="qu[]" size="7" multiple="multiple" class="rightselect" style="background-color: #181818">
{foreach from=$lang.quality key=i item=str}{if $str}
                            <option value="{$i}" class="q{$i}" {if isset($filter.qu) && in_array($i, (array)$filter.qu)}selected{/if}>{$str}</option>
{/if}{/foreach}
                        </select>
                    </div>

                    <div class="rightpanel2">
                        <div style="float: left">{$lang.type}{$lang.colon}</div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['ty[]'].selectedIndex = -1; return false" onmousedown="return false">{$lang.clear}</a></small>
                        <div class="clear"></div>
                        <select name="ty[]" size="7" multiple="multiple" class="rightselect">
{foreach from=$lang.types key=i item=str}{if $str}
                            <option value="{$i}" {if isset($filter.ty) && in_array($i, (array)$filter.ty)}selected{/if}>{$str}</option>
{/if}{/foreach}
                        </select>
                    </div>

                    <table>
                        <tr>
                            <td>{$lang.name|ucFirst}{$lang.colon}</td>
                            <td colspan="3">&nbsp;<input type="text" name="na" size="30" {if isset($filter.na)}value="{$filter.na}" {/if}/></td>
                        </tr><tr>
                            <td class="padded">{$lang.level}{$lang.colon}</td>
                            <td class="padded">&nbsp;<input type="text" name="minle" maxlength="3" class="smalltextbox2"{if isset($filter.minle)} value="{$filter.minle}"{/if} /> - <input type="text" name="maxle" maxlength="3" class="smalltextbox2"{if isset($filter.maxle)} value="{$filter.maxle}"{/if} /></td>
                            <td class="padded" width="100%">
                                <table><tr>
                                    <td>&nbsp;&nbsp;&nbsp;{$lang._reqLevel}{$lang.colon}</td>
                                    <td>&nbsp;<input type="text" name="minrl" maxlength="2" class="smalltextbox"{if isset($filter.minrl)} value="{$filter.minrl}"{/if} /> - <input type="text" name="maxrl" maxlength="2" class="smalltextbox"{if isset($filter.maxrl)} value="{$filter.maxrl}"{/if} /></td>
                                </tr></table>
                            </td>
                        </tr><tr>
                            <td class="padded">{$lang.class|ucfirst}{$lang.colon}</td>
                            <td class="padded">&nbsp;<select name="cl">
                                <option></option>
{foreach from=$lang.cl key=i item=str}{if $str}
                                <option value="{$i}" {if isset($filter.cl) && $filter.cl == $i}selected{/if}>{$str}</option>
{/if}{/foreach}
                            </select></td>
                            <td class="padded">
                                <table><tr>
                                    <td>&nbsp;&nbsp;&nbsp;{$lang._tag}{$lang.colon}</td>
                                    <td>&nbsp;<select name="ta">
                                        <option></option>
{foreach from=$lang.notes key=i item=str}{if $str}
                                        <option value="{$i}" {if isset($filter.ta) && $filter.ta == $i}selected{/if}>{$str}</option>
{/if}{/foreach}
                                    </select></td>
                                </tr></table>
                            </td>
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
                fi_init('itemsets');
{foreach from=$filter.fi item=str}
    {$str}
{/foreach}
            //]]></script>

            <div id="lv-generic" class="listview"></div>
            <script type="text/javascript">//<![CDATA[
                {include file='listviews/itemset.tpl' data=$lvData.data params=$lvData.params}
            //]]></script>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}
