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
                Menu.modifyUrl(Menu.findItem(mn_database, [4]), {ldelim} filter: '+={$filter.query|escape:'quotes'}' {rdelim}, {ldelim} onAppendCollision: fi_mergeFilterParams, onAppendEmpty: fi_setFilterParams, menuUrl: Menu.getItemUrl(Menu.findItem(mn_database, [4])) {rdelim});
{/if}
            </script>

            <div id="fi" style="display: {if empty($filter.query)}none{else}block{/if};">
                <form action="?npcs{$subCat}&filter" method="post" name="fi" onsubmit="return fi_submit(this)" onreset="return fi_reset(this)">
                    <div class="rightpanel">
                        <div style="float: left">{$lang.classification}{$lang.colon}</div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['cl[]'].selectedIndex = -1; return false" onmousedown="return false">{$lang.clear}</a></small>
                        <div class="clear"></div>
                        <select name="cl[]" size="5" multiple="multiple" class="rightselect" style="width: 9.5em">
{foreach from=$lang.rank key=i item=str}{if $str}
                            <option value="{$i}" {if isset($filter.cl) && in_array($i, (array)$filter.cl)}selected{/if}>{$str}</option>
{/if}{/foreach}
                        </select>
                    </div>
{if $petFamPanel}
                    <div class="rightpanel2">
                        <div style="float: left">{$lang.petFamily}{$lang.colon}</div><small><a href="javascript:;" onclick="document.forms['fi'].elements['fa[]'].selectedIndex = -1; return false" onmousedown="return false">{$lang.clear}</a></small>
                        <div class="clear"></div>
                        <select name="fa[]" size="7" multiple="multiple" class="rightselect">
{foreach from=$lang.fa key=i item=str}{if $str}
                            <option value="{$i}" {if isset($filter.fa) && in_array($i, (array)$filter.fa)}selected{/if}>{$str}</option>
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
                                    <td>&nbsp; <input type="checkbox" name="ex" value="on" id="npc-ex" {if isset($filter.ex)}checked="checked" {/if}/></td>
                                    <td><label for="npc-ex"><span class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, LANG.tooltip_extendednpcsearch, 0, 0, 'q')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">{$lang.extSearch}</span></label></td>
                                </tr></table>
                            </td>
                        </tr><tr>
                            <td class="padded">{$lang.level}{$lang.colon}</td>
                            <td class="padded">&nbsp;<input type="text" name="minle" maxlength="2" class="smalltextbox" {if isset($filter.minle)}value="{$filter.minle}" {/if}/> - <input type="text" name="maxle" maxlength="2" class="smalltextbox" {if isset($filter.maxle)}value="{$filter.maxle}" {/if}/></td>
                            <td class="padded" width="100%">
                                <table><tr>
                                    <td>&nbsp;&nbsp;&nbsp;{$lang.react}{$lang.colon}</td>
                                    <td>&nbsp;<select name="ra" onchange="fi_dropdownSync(this)" onkeyup="fi_dropdownSync(this)" style="background-color: #181818" {if isset($filter.ra)}class="q{if $filter.ra == 1}2{elseif $filter.ra == -1}10{/if}"{/if}>
                                        <option></option>
                                        <option value="1" class="q2"{if isset($filter.ra) && $filter.ra == 1} selected{/if}>A</option>
                                        <option value="0" class="q"{if isset($filter.ra) && $filter.ra == 0} selected{/if}>A</option>
                                        <option value="-1" class="q10"{if isset($filter.ra) && $filter.ra == -1} selected{/if}>A</option>
                                    </select>
                                    <select name="rh" onchange="fi_dropdownSync(this)" onkeyup="fi_dropdownSync(this)" style="background-color: #181818" {if isset($filter.rh)}class="q{if $filter.rh == 1}2{elseif $filter.rh == -1}10{/if}"{/if}>
                                        <option></option>
                                        <option value="1" class="q2"{if isset($filter.rh) && $filter.rh == 1} selected{/if}>H</option>
                                        <option value="0" class="q"{if isset($filter.rh) && $filter.rh == 0} selected{/if}>H</option>
                                        <option value="-1" class="q10"{if isset($filter.rh) && $filter.rh == -1} selected{/if}>H</option>
                                    </select>
                                    </td>
                                </tr></table>
                            </td>
                        </tr>
                    </table>

                    <div id="fi_criteria" class="padded criteria"><div></div></div>
                    <div><a href="javascript:;" id="fi_addcriteria" onclick="fi_addCriterion(this); return false">{$lang.addFilter}</a></div>

                    <div class="padded2 clear">
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
                fi_init('npcs');
{foreach from=$filter.fi item=str}
    {$str}
{/foreach}
            //]]></script>

            <div id="lv-generic" class="listview"></div>
            <script type="text/javascript">//<![CDATA[
                {include file='listviews/creature.tpl' data=$lvData.data params=$lvData.params}
            //]]></script>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}
