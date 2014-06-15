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
				g_initPath({$page.path}, {if empty($filter.query)} 0 {else} 1 {/if});           _// activeTab: 1, breadcrumb: [1,5,2,'eu','blackout','dragonblight']});
                {if isset($filter.query)}Menu.append(mn_database[1], '&filter={$filter.query|escape:'quotes'}'); // todo: menu order varies per locale{/if}
			</script>

            <div id="fi">
                <form action="/web/20120205222627/http://www.wowhead.com/filter=guilds" method="post" name="fi" onsubmit="return fi_submit(this)" onreset="return fi_reset(this)">
                <table>
                    <tr>
                        <td>{$lang.name}{$lang.colon}</td>
                        <td colspan="3">
                            <table><tr>
                                <td>&nbsp;<input type="text" name="na" size="30" /></td>
                                <td>&nbsp; <input type="checkbox" name="ex" value="on" id="profile-ex" /></td>
                                <td><label for="profile-ex"><span class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, LANG.tooltip_exactprofilesearch, 0, 0, 'q')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">Exact match</span></label></td>
                            </tr></table>
                        </td>
                    </tr>
                    <tr>
                        <td class="padded">Region{$lang.colon}</td>
                        <td class="padded">&nbsp;<select name="rg" onchange="pr_onChangeRegion(this.form, null, null)">
                            <option></option>
                            <option value="us">US &amp; Oceanic</option>
                            <option value="eu" selected="selected">Europe</option>
                        </select>&nbsp;</td>
                        <td class="padded">&nbsp;&nbsp;&nbsp;Realm{$lang.colon}</td>
                        <td class="padded">&nbsp;<select name="sv"><option></option></select><input type="hidden" name="bg" value="blackout" /></td>
                    </tr>
                    <tr>
                    <td class="padded">{$lang.side}{$lang.colon}</td>
                        <td class="padded">&nbsp;<select name="si">
                            <option></option>
                            <option value="1" {if isset($filter.si) && $filter.si == 1}selected{/if}>{$lang.alliance}</option>
                            <option value="2" {if isset($filter.si) && $filter.si == 2}selected{/if}>{$lang.horde}</option>
                        </select>&nbsp;</td>
                        <td class="padded">&nbsp;&nbsp;&nbsp;Level{$lang.colon}</td>
                        <td class="padded">&nbsp;<input type="text" name="minle" maxlength="2" class="smalltextbox" /> - <input type="text" name="maxle" maxlength="2" class="smalltextbox" /></td>
                    </tr>
                </table>

                <div class="clear"></div>

                <div class="padded"></div>
                <input type="submit" value="{$lang.applyFilter}" /><div style="float: right">{$lang.refineSearch}</div>

                </form>
                <div class="pad"></div>
            </div>

            <script type="text/javascript">//<![CDATA[
                // pr_setRegionRealm($WH.ge('fi').firstChild, 'eu', 'Dragonblight');
                // var fi_type = 'guilds';
                fi_init('guilds');
                {if isset($filter.setCr)}{$filter.setCr}{/if}
            //]]></script>

            <div id="lv-guilds" class="listview"></div>
            <script type="text/javascript">//<![CDATA[
                {include file='listviews/profiles.tpl' data=$data.page params=$data.params}
            //]]></script>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}
