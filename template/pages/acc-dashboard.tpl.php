<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
    $this->brick('announcement');

    $this->brick('pageTemplate');

    $this->brick('infobox');
?>

            <script type="text/javascript">var g_pageInfo = { username: '<?=Util::jsEscape($this->gUser['name']); ?>' }</script>

            <div class="text">
                <div id="h1-icon-generic" class="h1-icon"></div>
                <script type="text/javascript">
                    $WH.ge('h1-icon-generic').appendChild(Icon.createUser(<?=(is_numeric(User::$avatar) ? 2 : 1).' , \''.User::$avatar.'\''?>, 1, null, <?=User::isInGroup(U_GROUP_PREMIUM) ? 0 : 2; ?>, false, Icon.getPrivilegeBorder(<?=User::getReputation(); ?>)));
                </script>
                <h1 class="h1-icon"><?=Lang::account('myAccount'); ?></h1>
<?php
// Banned-Minibox
if ($b = $this->banned):
?>
                <div style="max-width:300px;" class="minibox">
                    <h1 class="q10"><?=Lang::account('accBanned'); ?></h1>
                    <ul style="text-align:left">
                        <li><div><?='<b>'.Lang::account('bannedBy').'</b>'.Lang::main('colon').'<a href="?user='.$b['by'][0].'">'.$b['by'][1].'</a>'; ?></div></li>
                        <li><div><?='<b>'.Lang::account('ends').'</b>'.Lang::main('colon').($b['end'] ? date(Lang::main('dateFmtLong'), $b['end']) : Lang::account('permanent')); ?></div></li>
                        <li><div><?='<b>'.Lang::account('reason').'</b>'.Lang::main('colon').'<span class="msg-failure">'.($b['reason'] ?: Lang::account('noReason')).'</span>'; ?></div></li>
                    </ul>
                </div>
<?php
/* todo (sometime else)
else:

// profile editing
echo '                    '.Lang::account('editAccount')."\n";
 ?>

                    <ul class="last">
                        <li><div><a href="#community">{$lang.publicDesc}</a></div></li>
                        <li><div><a href="#claim-characters">{$lang.claimChars}</a></div></li>
                        <li><div><a href="#change-avatar">{$lang.changeAvatar}</a></div></li>
                        <li><div><a href="#change-password">{$lang.pass}</a></div></li>
                        <li><div><a href="#change-name">{$lang.name}</a></div></li>
                        <li><div><a href="#change-email">{$lang.email}</a></div></li>
                    </ul>


                    <h2 id="community">{$lang.publicDesc}</h2>
                        <div class="msg-success" style="display:{if $pdupdate}block{else}none{/if};">{$lang.Your_description_has_been_updated_successfully}.</div>

                        <form action="?account=public-description" name="pd" method="post" onsubmit="return spd(this)">
                        {$lang.viewPublicDesc|sprintf:$user.name}.
                        <div class="pad2"></div>
                        <div id="pd"></div>
                        <script type="text/javascript">//<![CDATA[
                            Listview.funcBox.coEditAppend($WH.ge('pd'), {body: '{$user.community|escape:"javascript"}'}, 2);
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

<?php
    */
    endif;
?>
            </div>
<?php
/*
            <div class="clear"></div>
            <div id="related-tabs"></div>
            <div id="lv-generic" class="listview">
            <script type="text/javascript">//<![CDATA[
                var tabsRelated = new Tabs({parent: $WH.ge('related-tabs')});

    // relevant tabs here

                # set in header
                var lv_comments = [{id:1191765,type:12,typeId:1,subject:'Example Comment',preview:'And here is a little preview for this comment, that is capped after 75 char....',rating:15,date:'2010/11/27 22:23:16',elapsed:43866462,deleted:0,purged:1,domain:'live'}];
                var lv_screenshots = [], lv_videos = [];
                new Listview({template: "commentpreview", id: "comments", name: LANG.tab_comments, tabs: tabsRelated, parent: "lv-generic", onBeforeCreate: Listview.funcBox.beforeUserComments, hiddenCols: ['author'], data: lv_comments});
                new Listview({template: "screenshot", id: "screenshots", name: LANG.tab_screenshots, tabs: tabsRelated, parent: "lv-generic", data: lv_screenshots});
                new Listview({template: "video", id: "videos", name: LANG.tab_videos, tabs: tabsRelated, parent: "lv-generic", data: lv_videos});
                tabsRelated.flush();
            //]]></script>
*/
?>

<?php $this->brick('lvTabs'); ?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
