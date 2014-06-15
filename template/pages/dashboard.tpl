{include file='header.tpl'}

        <div id="main">
            <div id="main-precontents"></div>
            <div id="main-contents" class="main-contents">
    {if !empty($announcements)}
        {foreach from=$announcements item=item}
            {include file='bricks/announcement.tpl' an=$item}
        {/foreach}
    {/if}
                <script type="text/javascript">var g_pageInfo = {ldelim} username: '{$user.name}' {rdelim}</script>
                <table class="infobox">
                    <tr><th>{$lang.quickFacts}</th></tr>
                    <tr><td>
                        <div id="ci_msg"></div>
                        <div class="infobox-spacer"></div>
                        <ul>
                            <li><div>{$lang.login}: {$user.login}</div></li>
                            <li><div>{$lang.joinDate}{$lang.colon}<span class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, '{'l, G:i:s'|date:$user.joinDate}', 0, 0, 'q')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">{'Y/m/d'|date:$user.joinDate}</span></div></li>
                            <li><div>{$lang.lastLogin}{$lang.colon}<span class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, '{'l, G:i:s'|date:$user.lastLogin}', 0, 0, 'q')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">{'Y/m/d'|date:$user.lastLogin}</span></div></li>
                            <li><div>{$lang.userGroups}{$lang.colon}<span>{$user.roles}</span></div></li>
                            <li><div>{$lang.email}: {$user.email}</div></li>
                            <li><div>{$lang.lastIP}: {$user.lastIP}</span></div></li>
                            <!--<li><div><a href="?item=35200" class="q3 icontiny" style="background-image:url(images/icons/tiny/inv_scroll_04.gif)">Pinter's Bill</a>: ???</div></li>-->
                        </ul>
                    </td></tr>
                </table>

                <div class="text">

                    <h1>{$lang.myAccount}</h1>
{* BANNED POPUP *}
{if !empty($user.banned)}
                    <div class="minibox">
                        <h3 class="q7">[Account banned]</h3>
                        <ul style="text-align:left">
                            <li><div><b>[banner]</b>: {$user.bannedBy}</div></li>
                            <li><div><b>[end]</b>: {$user.unbanDate}</div></li>
                            <li><div><b>[reason]</b>{$lang.colon}<span class="msg-failure">{if isset($user.banReason)}{$user.banReason}{else}[none given.]{/if}</span></div></li>
                        </ul>
                    </div>
{/if}
                    {$lang.editAccount}

                    <ul class="last">
                        <li><div><a href="#community">{$lang.publicDesc}</a></div></li>
                        <li><div><a href="#claim-characters">{$lang.claimChars}</a></div></li>
                        <li><div><a href="#change-avatar">{$lang.changeAvatar}</a></div></li>
                        <li><div><a href="#change-password">{$lang.pass}</a></div></li>
                        <li><div><a href="#change-name">{$lang.pass}</a></div></li>
                        <li><div><a href="#change-email">{$lang.pass}</a></div></li>
                    </ul>

{* USER-PROFILE EDITING *}

                    <h2 id="community">{$lang.publicDesc}</h2>
                        <div class="msg-success" style="display:{if $pdupdate}block{else}none{/if};">{$lang.Your_description_has_been_updated_successfully}.</div>

                        <form action="?account=public-description" name="pd" method="post" onsubmit="return spd(this)">
                        {$lang.viewPublicDesc|sprintf:$user.name}.
                        <div class="pad2"></div>
                        <div id="pd"></div>
                        <script type="text/javascript">//<![CDATA[
                            Listview.funcBox.coEditAppend($WH.ge('pd'), {ldelim}body: '{$user.community|escape:"javascript"}'{rdelim}, 2);
                        //]]></script>
                        <div class="pad"></div>
                        <input type="submit" value="{$lang.submit}" />
                        </form>
{* CLAIM CHARACTERS *}
                    <h2 id="select-character">[Select Character]</h2>
{strip}

{if $user.chars}
                    <table>
    {foreach from=$user.chars item=c}
                        <tr>
                            <td><div class="iconsmall"><ins style="background-image: url(images/icons/small/{$c.icon}.jpg);"></ins><del></del></div></td>
                            <td>
                                {if $c.this}
                                    <b>{$c.name}</b>
                                {else}
                                    <a href="?account=select-character&id={$c.guid}">{$c.name}</a>
                                {/if}
                                &nbsp;
                                {if $c.guild}
                                    <b>&lt;{$c.guild|escape:"html"}&gt;</b>
                                {/if}
                                &nbsp;— {$c.text}
                            </td>
                        </tr>
    {/foreach}
                    </table>
{else}
            [no characters on ths account]
{/if}
            <div class="pad"></div>

{/strip}
{* CHANGE PASSWORD / EMAIL / DISPLAYNAME / AVATAR * }
                    <h2 id="change-password">{$lang.Change_password}</h2>
                    <form action="?account" method="post">
                        <input type="hidden" name="what" value="change-pass" />
{if isset($cpmsg)}
                        <div class="msg-{$cpmsg.class}">{$cpmsg.msg}</div>
{/if}
                        <table cellspacing="5" cellpadding="0" border="0">
                            <tr><td nowrap="nowrap">{$lang.Current_password}{$lang.colon}</td><td><input style="width: 15em" name="old" type="password" value="" /></td></tr>
                            <tr><td nowrap="nowrap">{$lang.New_password}{$lang.colon}</td><td><input style="width: 15em" name="new" type="password" value="" /></td></tr>
                            <tr><td nowrap="nowrap">{$lang.Confirm_new_password}{$lang.colon}</td><td><input style="width: 15em" name="new2" type="password" value="" /></td></tr>
                            <tr><td></td><td><input value="{$lang.submit}" type="submit" /></td></tr>
                        </table>
                    </form>
                </div>

                <div class="clear"></div>
            <div id="related-tabs"></div>
            <div id="lv-generic" class="listview">
            <script type="text/javascript">//<![CDATA[
                var tabsRelated = new Tabs({ldelim}parent: $WH.ge('related-tabs'){rdelim});
                {include file='listviews/character.tpl' data=$user.chars.data    params=$user.chars.params   }
                {include file='listviews/profile.tpl'   data=$user.profiles.data params=$user.profiles.params}

                /* set in header*/ var lv_comments = [{ldelim}id:1191768,type:2,typeId:194390,subject:'Stolen Explorers\' League Document',preview:'Here\'s a link to a map, showing all the locations of the different scrolls....',rating:3,date:'2010/11/27 22:23:22',elapsed:43866456,deleted:0,removed:0,domain:'live'{rdelim},{ldelim}id:1191765,type:2,typeId:194391,subject:'Stolen Explorers\' League Document',preview:'Here\'s a link to a map, showing all the locations of the different scrolls....',rating:5,date:'2010/11/27 22:23:16',elapsed:43866462,deleted:0,removed:0,domain:'live'{rdelim}];
                var lv_screenshots = [], lv_videos = [];
                new Listview({ldelim}template: "commentpreview", id: "comments", name: LANG.tab_comments, tabs: tabsRelated, parent: "lv-generic", onBeforeCreate: Listview.funcBox.beforeUserComments, data: lv_comments{rdelim});
                new Listview({ldelim}template: "screenshot", id: "screenshots", name: LANG.tab_screenshots, tabs: tabsRelated, parent: "lv-generic", data: lv_screenshots{rdelim});
                new Listview({ldelim}template: "video", id: "videos", name: LANG.tab_videos, tabs: tabsRelated, parent: "lv-generic", data: lv_videos{rdelim});
                tabsRelated.flush();
            //]]></script>
            </div>
        </div>
{include file='footer.tpl'}