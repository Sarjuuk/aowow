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
                Menu.modifyUrl(Menu.findItem(mn_database, [0]), {ldelim} filter: '+={$filter.query|escape:'quotes'}' {rdelim}, {ldelim} onAppendCollision: fi_mergeFilterParams, onAppendEmpty: fi_setFilterParams, menuUrl: Menu.getItemUrl(Menu.findItem(mn_database, [0])) {rdelim});
{/if}
            </script>

            <div id="fi" style="display: {if empty($filter.query)}none{else}block{/if};">
                <form action="?items{$subCat}&filter" method="post" name="fi" onsubmit="return fi_submit(this)" onreset="return fi_reset(this)">

                    <div class="rightpanel">
                        <div style="float: left">{$lang._quality}{$lang.colon}</div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['qu[]'].selectedIndex = -1; return false" onmousedown="return false">{$lang.clear}</a></small>
                        <div class="clear"></div>
                        <select name="qu[]" size="7" multiple="multiple" class="rightselect" style="background-color: #181818">
{foreach from=$lang.quality key=k item=str}
                            <option value="{$k}" class="q{$k}"{if isset($filter.qu) && in_array($k, (array)$filter.qu)} selected{/if}>{$str}</option>
{/foreach}
                        </select>
                    </div>

{if !empty($filter.slot)}
                    <div class="rightpanel2">
                        <div style="float: left">{$lang.slot}{$lang.colon}</div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['sl[]'].selectedIndex = -1; return false" onmousedown="return false">{$lang.clear}</a></small>
                        <div class="clear"></div>
                        <select name="sl[]" size="{if $filter.slot|@count < 7}{$filter.slot|@count}{else}7{/if}" multiple="multiple" class="rightselect">
    {foreach from=$filter.slot key=k item=str}
                            <option value="{$k}"{if isset($filter.sl) && in_array($k, (array)$filter.sl)} selected{/if}>{$str}</option>
    {/foreach}
                        </select>
                    </div>
{/if}

{if !empty($filter.type)}
                    <div class="rightpanel2">
                        <div style="float: left">{$lang.type}{$lang.colon}</div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['ty[]'].selectedIndex = -1; return false" onmousedown="return false">{$lang.clear}</a></small>
                        <div class="clear"></div>
                        <select name="ty[]" size="{if $filter.type|@count < 7}{$filter.type|@count}{else}7{/if}" multiple="multiple" class="rightselect">
    {foreach from=$filter.type key=k item=str}
                            <option value="{$k}"{if isset($filter.ty) && in_array($k, (array)$filter.ty)} selected{/if}>{$str}</option>
    {/foreach}
                        </select>
                    </div>
{/if}

                    <table>
                        <tr>
                            <td>{$lang.name|ucFirst}{$lang.colon}</td>
                            <td colspan="2">&nbsp;<input type="text" name="na" size="30" {if isset($filter.na)}value="{$filter.na|escape:'html'}" {/if}/></td>
                            <td></td>
                        </tr><tr>
                            <td class="padded">{$lang.level}{$lang.colon}</td>
                            <td class="padded">&nbsp;<input type="text" name="minle" maxlength="3" class="smalltextbox2" {if isset($filter.minle)}value="{$filter.minle}" {/if}/> - <input type="text" name="maxle" maxlength="3" class="smalltextbox2" {if isset($filter.maxle)}value="{$filter.maxle}" {/if}/></td>
                            <td class="padded">
                                <table>
                                    <tr>
                                        <td>&nbsp;&nbsp;&nbsp;{$lang._reqLevel}{$lang.colon}</td>
                                        <td>&nbsp;<input type="text" name="minrl" maxlength="2" class="smalltextbox" {if isset($filter.minrl)}value="{$filter.minrl}" {/if}/> - <input type="text" name="maxrl" maxlength="2" class="smalltextbox" {if isset($filter.maxrl)}value="{$filter.maxrl}" {/if}/></td>
                                    </tr>
                                </table>
                            </td>
                            <td></td>
                        </tr><tr>
                            <td class="padded">{$lang.usableBy}{$lang.colon}</td>
                            <td class="padded">&nbsp;<select name="si" style="margin-right: 0.5em">
                                <option></option>
{foreach from=$lang.si key=k item=str}
                                <option value="{$k}"{if isset($filter.si) && $k == $filter.si} selected{/if}>{$str}</option>
{/foreach}
                            </select></td>
                            <td class="padded">
                                &nbsp;<select name="ub">
                                    <option></option>
{foreach from=$lang.cl key=k item=str}{if $str}
                                    <option value="{$k}"{if isset($filter.ub) && $k == $filter.ub} selected{/if}>{$str}</option>
{/if}{/foreach}
                                </select></td>
                            </td>
                        </tr>
                    </table>

                    <div id="fi_criteria" class="padded criteria"><div></div></div>
                    <div><a href="javascript:;" id="fi_addcriteria" onclick="fi_addCriterion(this); return false">{$lang.addFilter}</a></div>

                    <div class="padded2">
                        <div style="float: right">{$lang.refineSearch}</div>
                        {$lang.match}{$lang.colon}<input type="radio" name="ma" value="" id="ma-0" {if !isset($filter.ma)}checked="checked" {/if}/><label for="ma-0">{$lang.allFilter}</label><input type="radio" name="ma" value="1" id="ma-1" {if isset($filter.ma)}checked="checked" {/if}/><label for="ma-1">{$lang.oneFilter}</label>
                    </div>

                    <div class="pad3"></div>

                    <div class="text">
                        <h3 class="first"><a id="fi_weight_toggle" href="javascript:;" class="disclosure-off" onclick="return g_disclose($WH.ge('statweight-disclosure'), this)">{$lang.createWS}</a></h3>
                    </div>

                    <div id="statweight-disclosure" style="display: none">
                        <div id="statweight-help">
                            <div><a href="?help=stat-weighting" target="_blank" id="statweight-help" class="icon-help" style="font-size: 13px; font-weight: normal; background: url(template/images/help.gif) no-repeat left center; padding-left: 20px">{$lang.help}</a></div>
                        </div>

                        <table>
                            <tr>
                                <td>{$lang.preset}{$lang.colon}</td>
                                <td id="fi_presets"></td>
                            </tr>
                            <tr>
                                <td class="padded">{$lang.gems}{$lang.colon}</td>
                                <td class="padded">
                                    <select name="gm">
                                        <option {if !isset($filter.gm)}selected{/if}></option>
                                        <option value="2" {if isset($filter.gm) && $filter.gm == 2}selected{/if}>{$lang.quality[2]}</option>
                                        <option value="3" {if isset($filter.gm) && $filter.gm == 3}selected{/if}>{$lang.quality[3]}</option>
                                        <option value="4" {if isset($filter.gm) && $filter.gm == 4}selected{/if}>{$lang.quality[4]}</option>
                                    </select>
                                    &nbsp; <input type="checkbox" name="jc" value="1" id="jc" /><label for="jc">{$lang.jcGemsOnly|sprintf:' class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, LANG.tooltip_jconlygems, 0, 0, \'q\')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"'}</label>
                                </td>
                            </tr>
                        </table>

                        <div id="fi_weight" class="criteria" style="display: none"><div></div></div>
                        <div><a href="javascript:;" id="fi_addweight" onclick="fi_addCriterion(this); return false" style="display: none">{$lang.addWeight}</a></div>
                        <div class="pad2"></div>
                        <small>{$lang.cappedHint}</small>

                    </div>

                    <div class="clear"></div>
                    <div class="padded">{$lang.groupBy}{$lang.colon}
{foreach from=$lang.gb key=k item=str}
    {if $k == 0}
                        <input type="radio" name="gb" value="" id="gb-{$str[1]}" {if empty($filter.gb)}checked="checked" {/if}/><label for="gb-{$str[1]}">{$str[0]}</label>
    {else}
                        <input type="radio" name="gb" value="{$k}" id="gb-{$str[1]}" {if !empty($filter.gb) && $filter.gb == $k}checked="checked" {/if}/><label for="gb-{$str[1]}">{$str[0]}</label>
    {/if}
{/foreach}
                    </div>

                    <div class="clear"></div>

                    <div class="padded">
                        <input type="submit" value="{$lang.applyFilter}" />
                        <input type="reset" value="{$lang.resetForm}" />
                    </div>

                    <input type="hidden" name="upg"{if !empty($filter.upg)} value="{$filter.upg}"{/if}/>

                    <div class="pad"></div>

                </form>
                <div class="pad"></div>
            </div>

            <script type="text/javascript">//<![CDATA[
                fi_init('items');
{foreach from=$filter.fi item=str}
    {$str}
{/foreach}
            //]]></script>

{if isset($lvData.data[0].params)}
            <div id="tabs-generic"></div>
{/if}
            <div id="lv-generic" class="listview"></div>
            <script type="text/javascript">//<![CDATA[
{if !empty($gemScores)}var fi_gemScores = {$gemScores};{/if}

{if isset($lvData.data[0].params)}
                var tabsRelated = new Tabs({ldelim}parent: $WH.ge('tabs-generic'){rdelim});
    {foreach from=$tabs item="tab"}
        {if !empty($tab.data)}
            {include file="bricks/listviews/item.tpl" data=$tab.data params=$tab.params}
        {/if}
    {/foreach}
                tabsRelated.flush();
{else}
    {include file='bricks/listviews/item.tpl' data=$lvData.data params=$lvData.params}
{/if}
            //]]></script>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}
