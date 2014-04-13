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
                var temp_path = {$path}; {*kill with pageTemplate*}
{*
PageTemplate.set({pageName: 'profiles', activeTab: 1, breadcrumb: [1,5,0,'eu']});
PageTemplate.init();
*}
            </script>

            <div id="fi" style="display:{if empty($filter.query)}none{else}block{/if};"><form   {* for some arcane reason a linebreak means, the first childNode is a text instead of the form *}
                action="?profiles&filter" method="post" name="fi" onsubmit="return fi_submit(this)" onreset="return fi_reset(this)">
                    <div class="rightpanel">
                        <div style="float: left">{$lang.class|ucfirst}{$lang.colon}</div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['cl[]'].selectedIndex = -1; return false" onmousedown="return false">{$lang.clear}</a></small>
                        <div class="clear"></div>
                        <select name="cl[]" size="7" multiple="multiple" class="rightselect" style="background-color: #181818">
{foreach from=$lang.cl key=i item=str}{if $str}
                            <option classvalue="{$i}"{if isset($filter.cl) && ($filter.cl == $i || @in_array($i, $filter.cl))} selected{/if} class="c{$i}">{$str}</option>
{/if}{/foreach}
                        </select>
                    </div>

                    <div class="rightpanel2">
                        <div style="float: left">{$lang.race|ucfirst}{$lang.colon}</div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['ra[]'].selectedIndex = -1; pr_onChangeRace(); return false" onmousedown="return false">{$lang.clear}</a></small>
                        <div class="clear"></div>
                        <select name="ra[]" size="7" multiple="multiple" class="rightselect" onchange="pr_onChangeRace()">
{foreach from=$lang.ra key=i item=str}{if $str}{if $i > 0}
                            <option value="{$i}"{if isset($filter.ra) && $filter.ra == $i} selected{/if}>{$str}</option>
{/if}{/if}{/foreach}
                        </select>
                    </div>

                    <table>
                        <tr>
                            <td>Name{$lang.colon}</td>
                            <td colspan="3">
                                <table><tr>
                                    <td>&nbsp;<input type="text" name="na" size="30" {if isset($filter.na)}value="{$filter.na|escape:'html'}"{/if}/></td>
                                    <td>&nbsp; <input type="checkbox" name="ex" value="on" id="profile-ex" {if isset($filter.ex)}checked="checked"{/if}/></td>
                                    <td><label for="profile-ex"><span class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, LANG.tooltip_exactprofilesearch, 0, 0, 'q')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">{$lang.exactMatch}</span></label></td>
                                </tr></table>
                            </td>
                        </tr>
                        <tr>
                            <td class="padded">Region{$lang.colon}</td>
                            <td class="padded">&nbsp;<select name="rg" onchange="pr_onChangeRegion(this.form, null, null)">
                            <option></option>
                            <option value="us">Americas</option>
                            <option value="eu" selected="selected">Europe</option>
                            </select>&nbsp;
                            </td>

                            <td class="padded">&nbsp;&nbsp;&nbsp;Realm{$lang.colon}</td>
                            <td class="padded">&nbsp;<select name="sv"><option></option></select><input type="hidden" name="bg" value="" /></td>
                        </tr>
                        <tr>
                            <td class="padded">Side{$lang.colon}</td>
                            <td class="padded">&nbsp;<select name="si">
                            <option></option>
                            <option value="1">Alliance</option>
                            <option value="2">Horde</option>
                            </select>
                            </td>
                            <td class="padded">&nbsp;&nbsp;&nbsp;{$lang.level}{$lang.colon}</td>
                            <td class="padded">&nbsp;<input type="text" name="minle" maxlength="2" class="smalltextbox" {if isset($filter.minle)}value="{$filter.minle}"{/if}/> - <input type="text" name="maxle" maxlength="2" class="smalltextbox" {if isset($filter.maxle)}value="{$filter.maxle}"{/if}/></td>
                        </tr>
                    </table>

                    <div id="fi_criteria" class="padded criteria"><div></div></div>
                    <div><a href="javascript:;" id="fi_addcriteria" onclick="fi_addCriterion(this); return false">{$lang.addFilter}</a></div>

                    <div class="padded2">
                        {$lang.match}{$lang.colon}<input type="radio" name="ma" value="" id="ma-0" checked="checked" /><label for="ma-0">{$lang.allFilter}</label><input type="radio" name="ma" value="1" id="ma-1" /><label for="ma-1">{$lang.oneFilter}</label>
                    </div>

                    <div class="clear"></div>

                    <div class="padded"></div>
                    <input type="submit" value="Apply filter" />

                </form>
                <div class="pad"></div>
            </div>

            <script type="text/javascript">//<![CDATA[
                pr_setRegionRealm($WH.ge('fi').firstChild, '{$region}', '{$realm}');
                pr_onChangeRace();
                fi_init('profiles');
                {if isset($filter.setCr)}{$filter.setCr}{/if}
            //]]></script>

            <div id="lv-generic" class="listview"></div>
            <script type="text/javascript">//<![CDATA[
                {include file='listviews/profile.tpl' data=$lvData.data params=$lvData.params}
            //]]></script>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}
