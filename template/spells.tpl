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
                Menu.modifyUrl(Menu.findItem(mn_database, [1]), {ldelim} filter: '+={$filter.query|escape:'quotes'}' {rdelim}, {ldelim} onAppendCollision: fi_mergeFilterParams, onAppendEmpty: fi_setFilterParams, menuUrl: Menu.getItemUrl(Menu.findItem(mn_database, [1])) {rdelim});
{/if}
            </script>

            <div id="fi" style="display: {if empty($filter.query)}none{else}block{/if};">
                <form action="?spells{$subCat}&filter" method="post" name="fi" onsubmit="return fi_submit(this)" onreset="return fi_reset(this)">
                    <div class="rightpanel">
                        <div style="float: left">{$lang.school}{$lang.colon}</div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['sc[]'].selectedIndex = -1; return false" onmousedown="return false">{$lang.clear}</a></small>
                        <div class="clear"></div>
                        <select name="sc[]" size="7" multiple="multiple" class="rightselect" style="width: 8em">
{foreach from=$lang.sc key=i item=str}{if $str}
                            <option value="{$i}" {if isset($filter.sc) && in_array($i, (array)$filter.sc)}selected{/if}>{$str}</option>
{/if}{/foreach}
                        </select>
                    </div>
{if $filter.classPanel}
                    <div class="rightpanel2">
                        <div style="float: left">{$lang.class|ucfirst}{$lang.colon}</div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['cl[]'].selectedIndex = -1; return false" onmousedown="return false">{$lang.clear}</a></small>
                        <div class="clear"></div>
                        <select name="cl[]" size="8" multiple="multiple" class="rightselect" style="width: 8em; background-color: #181818">
{foreach from=$lang.cl key=i item=str}{if $str}
                            <option value="{$i}"{if isset($filter.cl) && in_array($i, (array)$filter.cl)} selected{/if} class="c{$i}">{$str}</option>
{/if}{/foreach}
                        </select>
                    </div>
{/if}
{if $filter.glyphPanel}
                    <div class="rightpanel2">
                        <div style="float: left">{$lang.glyphType|ucfirst}{$lang.colon}</div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['gl[]'].selectedIndex = -1; return false" onmousedown="return false">{$lang.clear}</a></small>
                        <div class="clear"></div>
                        <select name="gl[]" size="2" multiple="multiple" class="rightselect" style="width: 8em">
{foreach from=$lang.gl key=i item=str}{if $str}
                            <option value="{$i}"{if isset($filter.gl) && in_array($i, (array)$filter.gl)} selected{/if}>{$str}</option>
{/if}{/foreach}
                        </select>
                    </div>
{/if}
                    <table>
                        <tr>
                            <td>{$lang.name|ucFirst}{$lang.colon}</td>
                            <td colspan="2">
                                <table><tr>
                                    <td>&nbsp;<input type="text" name="na" size="30" {if isset($filter.na)}value="{$filter.na|escape:'html'}" {/if}/></td>
                                    <td>&nbsp; <input type="checkbox" name="ex" value="on" id="spell-ex" {if isset($filter.ex)}checked="checked" {/if}/></td>
                                    <td><label for="spell-ex"><span class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, LANG.tooltip_extendedspellsearch, 0, 0, 'q')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">{$lang.extSearch}</span></label></td>
                                </tr></table>
                            </td>
                        </tr><tr>
                            <td class="padded">{$lang.level}{$lang.colon}</td>
                            <td class="padded">&nbsp;<input type="text" name="minle" maxlength="2" class="smalltextbox" {if isset($filter.minle)}value="{$filter.minle}" {/if}/> - <input type="text" name="maxle" maxlength="2" class="smalltextbox" {if isset($filter.maxle)}value="{$filter.maxle}" {/if}/></td>
                            <td class="padded">
                                <table cellpadding="0" cellspacing="0" border="0"><tr>
                                    <td>&nbsp;&nbsp;&nbsp;{$lang.reqSkillLevel}{$lang.colon}</td>
                                    <td>&nbsp;<input type="text" name="minrs" maxlength="3" class="smalltextbox2" {if isset($filter.minrs)}value="{$filter.minrs}" {/if}/> - <input type="text" name="maxrs" maxlength="3" class="smalltextbox2" {if isset($filter.maxrs)}value="{$filter.maxrs}" {/if}/></td>
                                </tr></table>
                            </td>
                        </tr><tr>
                            <td class="padded">{$lang.race|ucfirst}{$lang.colon}</td>
                            <td class="padded">&nbsp;<select name="ra">
                                <option></option>
{foreach from=$lang.ra key=i item=str}{if $str}{if $i > 0}
                                <option value="{$i}"{if isset($filter.ra) && $filter.ra == $i} selected{/if}>{$str}</option>
{/if}{/if}{/foreach}
                            </select></td>
                            <td class="padded"></td>
                        </tr><tr>
                            <td class="padded">{$lang.mechAbbr}{$lang.colon}</td>
                            <td class="padded">&nbsp;<select name="me">
                                <option></option>
{foreach from=$lang.me key=i item=str}{if $str}
                                <option value="{$i}"{if isset($filter.me) && $filter.me == $i} selected{/if}>{$str}</option>
{/if}{/foreach}
                            </select></td>
                            <td>
                                <table cellpadding="0" cellspacing="0" border="0"><tr>
                                    <td>&nbsp;&nbsp;&nbsp;{$lang.dispelType}{$lang.colon}</td>
                                    <td>&nbsp;<select name="dt">
                                        <option></option>
{foreach from=$lang.dt key=i item=str}{if $str}
                                        <option value="{$i}"{if isset($filter.dt) && $filter.dt == $i} selected{/if}>{$str}</option>
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
                        {$lang.match}{$lang.colon}<input type="radio" name="ma" value="" id="ma-0" {if !isset($filter.ma)}checked="checked" {/if}/><label for="ma-0">{$lang.allFilter}</label><input type="radio" name="ma" value="1" id="ma-1" {if isset($filter.ma)}checked="checked" {/if}/><label for="ma-1">{$lang.oneFilter}</label>
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
                fi_init('spells');
{foreach from=$filter.fi item=str}
    {$str}
{/foreach}
            //]]></script>

            <div id="lv-generic" class="listview"></div>
            <script type="text/javascript">//<![CDATA[
                {include file='bricks/listviews/spell.tpl' data=$lvData.data params=$lvData.params}
            //]]></script>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}
