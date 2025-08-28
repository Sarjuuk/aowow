<?php
    namespace Aowow\Template;

    use \Aowow\Lang;

    $this->brick('header');
?>
    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
    $this->brick('announcement');

    $this->brick('pageTemplate');
?>

            <div class="text">
                <h1><?=Lang::account('settings');?></h1>
<?php
// Banned-Minibox
if ($this->bans):
    foreach ($this->bans as $b):
        [$end, $reason, $name] = $b;
?>
                <div style="max-width:300px;" class="minibox minibox-left">
                    <h1 class="q10"><?=Lang::account('accBanned'); ?></h1>
                    <ul style="text-align:left">
                        <li><div><?='<b>'.Lang::account('bannedBy').'</b>'.($name ? '<a href="?user='.$name.'">'.$name.'</a>' : '<span ="q0">&lt;System&gt;</span>');?></div></li>
                        <li><div><?='<b>'.Lang::account('ends').'</b>'.($end ? date(Lang::main('dateFmtLong'), $end) : Lang::account('permanent'));?></div></li>
                        <li><div><?='<b>'.Lang::account('reason').'</b>'.'<span class="msg-failure">'.($reason ?: Lang::account('noReason')).'</span>';?></div></li>
                    </ul>
                </div>
<?php endforeach; ?>
            </div>

<?php else: ?>
                <?=Lang::account('settingsNote');?>

                <div class="pad3"></div>
            </div>

            <div id="tabs-generic"></div>

            <div class="text">
                <div class="tabbed-contents">

                    <div id="tab-general" style="display: none">
                        <form action="?account=update-general-settings" name="um" method="post">
                            <h2 class="first" id="preferences"><?=Lang::account('preferences');?></h2>

<?php if ([$type, $msg] = $this->generalMessage): ?>
                            <div class="box"><div class="msg-<?=($type ? 'success' : 'failure');?>"><?=$msg;?></div></div>
<?php endif; ?>

                            <div style="text-align: left">
                                <h3 class="first"><?=Lang::account('modelviewer');?></h3>
                                <table>
                                    <tr><td><?=Lang::account('mvNote'); ?></td>
                                        <td><select id="modelrace" name="modelrace">
                                            <option></option>
<?=$this->makeOptionsList(Lang::game('ra'), $this->modelrace, 44, fn($v, $k) => $k > 0); ?>
                                        </select>
                                        <select id="modelgender" name="modelgender">
                                            <option></option>
<?=$this->makeOptionsList(Lang::main('sex'), $this->modelgender, 44); ?>
                                        </select>
                                        </td>
                                    </tr>
                                </table>

                                <div class="pad"></div>

                                <h3><?=Lang::account('lists'); ?></h3>
                                <label><input type="checkbox" name="idsInLists"<?=($this->idsInLists ? ' checked="checked"' : '');?> /><?=Lang::account('listsNote'); ?></label>

                                <div class="pad"></div>

                                <h3><?=Lang::account('announcements');?></h3>
                                <button id="purgeannouncements" onclick="this.readonly = true; var button = this; new Ajax('?cookie&amp;purge', { onSuccess: function(xhr) { if(xhr.responseText == '0') { var span = $WH.ge('announcetext'); $WH.ee(span); $WH.ae(span, $WH.ct(LANG.myaccount_purgesuccess)); span.className = 'q2'; } else { alert(LANG.myaccount_purgefailed); } }, onComplete: function() { button.readonly = false; } }); return false;"><?=Lang::account('purge');?></button> <span id="announcetext"><?=Lang::account('annNote');?></span>
                            </div>

                            <div class="pad3"></div>
                            <input value="<?=Lang::main('submit');?>" type="submit" name="do-general-settings-update" />
                        </form>
                    </div>

<?php
    if ($this->cfg('ACC_AUTH_MODE') == AUTH_MODE_SELF):
?>
                    <div id="tab-personal" style="display: none">
                        <h2 class="first" id="change-email-address"><?=Lang::account('email');?></h2>

<?php if ([$type, $msg] = $this->emailMessage): ?>
                        <div class="box"><div class="msg-<?=($type ? 'success' : 'failure');?>"><?=$msg;?></div></div>
<?php endif; ?>

                        <form action="?account=update-email" name="ce" method="post" id="change-email">
                            <table cellspacing="5" cellpadding="0" border="0">
                                <tr><td nowrap="nowrap"><?=Lang::account('curEmail');?></td><td><input disabled="disabled" name="current-email" style="width: 15em" value="<?=$this->curEmail;?>" readonly="readonly" /></td></tr>
                                <tr><td nowrap="nowrap"><?=Lang::account('newEmail');?></td><td><input name="newemail" style="width: 15em" value="" /></td></tr>
                            </table>
                            <div class="pad"></div>
                            <input value="<?=Lang::main('submit');?>" type="submit" name="do-update-email" />
                        </form>

                        <h2 id="change-username"><?=Lang::account('user');?></h2>

<?php if ([$type, $msg] = $this->usernameMessage): ?>
                        <div class="box"><div class="msg-<?=($type ? 'success' : 'failure');?>"><?=$msg;?></div></div>
<?php endif; ?>

                        <div><?=Lang::account('usernameNote', [$this->renameCD]);?></div>
<?php if ($this->activeCD): ?>
                        <div class="msg-failure pad3"><br /><?=Lang::account('activeCD', [$this->activeCD]);?></div>
<?php endif; ?>
                        <form action="?account=update-username" name="ce" method="post" id="change-username">
                            <table cellspacing="5" cellpadding="0" border="0">
                                <tr><td nowrap="nowrap"><?=Lang::account('curName');?></td><td><input disabled="disabled" name="current-username" style="width: 15em" value="<?=$this->curName;?>" readonly="readonly" /></td></tr>
                                <tr><td nowrap="nowrap"><?=Lang::account('newName');?></td><td><input name="newUsername" style="width: 15em" value="" /></td></tr>
                            </table>
                            <div class="pad"></div>
                            <input value="<?=Lang::main('submit');?>" type="submit" name="do-update-username" />
                        </form>

                        <h2 id="change-password"><?=Lang::account('pass');?></h2>

<?php if ([$type, $msg] = $this->passwordMessage): ?>
                        <div class="box"><div class="msg-<?=($type ? 'success' : 'failure');?>"><?=$msg;?></div></div>
<?php endif; ?>

                        <form action="?account=update-password" name="cp" method="post" id="change-password">
                            <table cellspacing="5" cellpadding="0" border="0">
                                <tr><td nowrap="nowrap"><?=Lang::account('curPass');?></td><td colspan="3"><input name="currentPassword" style="width: 15em" type="password" value="" /></td></tr>
                                <tr><td nowrap="nowrap"><?=Lang::account('newPass');?></td><td><input name="newPassword" id="newpass" style="width: 15em" type="password" value="" onkeyup="setTimeout('pm()',10)" /></td><td id="pm1" rowspan="2" valign="middle" style="font-size: 38px"> </td><td id="pm2" rowspan="2" valign="middle" nowrap="nowrap" style="padding-top: 4px"> </td></tr>
                                <tr><td nowrap="nowrap"><?=Lang::account('confNewPass');?></td><td><input name="confirmPassword" id="confirmpass" style="width: 15em" type="password" value="" onkeyup="setTimeout('pm()',10)" /></td></tr>
                                <tr><td nowrap="nowrap"><?=Lang::account('globalLogout');?></td><td><input type="checkbox" name="globalLogout" /></td></tr>
                            </table>
                            <div class="pad"></div>
                            <input value="<?=Lang::main('submit');?>" type="submit" name="do-update-password" />
                            <div style="padding-top:10px; font-size:0.8em"><?=Lang::account('passResetHint');?></div>
                        </form>
                        <script type="text/javascript">pm()</script>

                        <h2><?=Lang::account('accDelete');?></h2>
                        <div><?=Lang::account('accDeleteNote');?></div>
                    </div>

<?php endif; ?>
                    <div id="tab-community" style="display: none">
                        <h2 class="first"><?=Lang::account('userPage');?></h2>

                        <h3 id="public-description"><?=Lang::account('publicDesc');?></h3>

<?php if ([$type, $msg] = $this->communityMessage): ?>
                        <div class="box"><div class="msg-<?=($type ? 'success' : 'failure');?>"><?=$msg;?></div></div>
<?php endif; ?>
                        <form action="?account=update-community-settings" name="community-settings" method="post" onsubmit="return spd(this)/*  && sfs(this) */">
                            <?=Lang::account('publicDescNote', [urlencode($this->user::$username)]);?>

                            <div class="pad2"></div>
                            <div>
                                <div id="description-generic"></div>
                                <script type="text/javascript">//<![CDATA[
                                    Listview.funcBox.coEditAppend($('#description-generic'), <?=$this->json($this->description); ?>, 2);
                                //]]></script>

                                <div class="pad"></div>
                                <input type="submit" value="<?=Lang::main('save');?>" name="do-community-settings-update" />
                            </div>
<?php
/*      signature not used
                            <h2><?=Lang::account('forums');?></h2>
                            <h3 id="forum-signature"><?=Lang::account('signature');?></h3>
                            <?=Lang::account('signatureNote');?>

                            <div class="pad2"></div>
                            <div>
                                <div id="signature-generic"></div>
                                <script type="text/javascript">//<![CDATA[
                                    Listview.funcBox.coEditAppend($('#signature-generic'), <?=$this->json($this->description); ?>, 4);
                                //]]></script>

                                <div class="pad"></div>
                                <input type="submit" value="<?=Lang::main('save');?>" name="do-community-settings-update" />
                            </div>
*/
?>
                        </form>

                        <h3 id="forum-avatar"><?=Lang::account('avatar');?></h3>

<?php if ([$type, $msg] = $this->avatarMessage): ?>
                        <div class="box"><div class="msg-<?=($type ? 'success' : 'failure');?>"><?=$msg;?></div></div>
<?php endif; ?>

                        <form action="?account=forum-avatar" name="fa" method="post" enctype="multipart/form-data" onsubmit="return fa_validateForm(this)">
                            <?=Lang::account('avatarNote');?>

                            <div class="pad2"></div>

                            <input type="radio" name="avatar" value="0" id="avaOpt0" onclick="faChange(0)"<?=($this->avMode == 0 ? ' checked="checked"' : '');?> /> <label for="avaOpt0"><?=Lang::account('none');?></label><br />

                            <input type="radio" name="avatar" value="1" id="avaOpt1" onclick="faChange(1)"<?=($this->avMode == 1 ? ' checked="checked"' : '');?> /> <label for="avaOpt1"><?=Lang::account('avWowIcon');?></label>
                            <div id="avaSel1" style="display: none">
                                <div style="float: left; position: relative; padding: 6px; margin-top: 4px; margin-left: 16px; border-left: 1px solid #404040">
                                    <div id="avaPre1" style="position: absolute; right: -68px; top: -18px"></div>
                                    <?=Lang::account('avIconName');?> <input type="text" name="wowicon" id="wowicon" value="<?=$this->wowicon;?>" maxlength="64" size="35" /><input type="button" value="<?=Lang::account('preview');?>" onclick="spawi()" />
                                    <br />
                                    <div style="max-width: 400px"><small><?=Lang::account('avWowIconNote');?></small></div>
                                </div>
                            </div>
                            <div class="clear"></div>

<?php if ($this->user::isInGroup(U_GROUP_PREMIUM)): ?>
                            <input type="radio" name="avatar" value="2" id="avaOpt2" onclick="faChange(2)"<?=($this->avMode == 2 ? ' checked="checked"' : '');?> /> <label for="avaOpt2"><?=Lang::account('custom');?></label>&nbsp;&nbsp;<span class="premium-feature-icon-small"></span><table id="avaSel2" style="padding: 6px; margin-top: 4px; margin-left: 16px; border-left: 1px solid #404040; height: 85px">
                                <tr><td style="padding: 5px; vertical-align: top; position: relative;">
                                    <select name="customicon" id="customicon" style="min-width: 150px; margin-right: 5px;" onchange="spawj()">
                                        <option><?=Lang::account('uploadAvatar');?></option>
<?=$this->makeOptionsList($this->customicons, $this->customicon, 40); ?>
                                    </select>
                                    <div id="avaPre2" style="position: absolute; right: -68px; top: 0px"></div>
                                </td><td style="padding: 5px; vertical-align: top;">
                                    <div id="iconbrowse">
                                        <input type="file" name="iconfile">
                                        <div class="pad"></div>
                                        <a href="javascript:;" onclick="_.show(3, true);"><?=Lang::account('goToManager');?></a>
                                    </div>
                                </td></tr>
                            </table>
<?php else: ?>
                            <input type="radio" name="avatar" value="2" id="avaOpt2" onclick="faChange(2)" disabled="disabled" /> <label for="avaOpt2" class="q0"><?=Lang::account('custom'); ?></label>&nbsp;&nbsp;<span class="premium-feature-icon-small"></span><table id="avaSel2" style="display:none"></table>
<?php endif; ?>
                            <div class="clear"></div>

                            <script type="text/javascript">
                                faChange(<?=$this->avMode;?>);
                                spawi();
                                <?=($this->user::isPremium() ? 'spawj();' : ''); ?>

                            </script>

                            <div class="pad"></div>
                            <input type="submit" value="<?=Lang::main('submit');?>" />

                        </form>

                    </div>

                    <div id="tab-premium" style="display: none; min-height: 70px;">
                        <h3 class="first"><?=Lang::account('premiumStatus');?></h3>
<?php if (!$this->user::isPremium()): ?>
                        <ul><li><div><?=Lang::account('status').Lang::main('colon').'<b class="q10">'.Lang::account('inactive'); ?></b></div></li></ul>
<?php else: ?>
                        <ul><li><div><?=Lang::account('status').Lang::main('colon').'<b class="q2">'.Lang::account('active'); ?></b></div></li></ul>
                        <h2><?=Lang::account('manageAvatars');?></h2>
                        <div id="avatar-manage" class="listview" style="margin: 0px 10% 0px 25px;"></div>
                        <script type="text/javascript">//<![CDATA[
                            <?=$this->avatarManager; ?>
                        //]]></script>

                        <h2><?=Lang::account('manageBorders');?></h2>
    <?php if ([$type, $msg] = $this->premiumborderMessage): ?>
                            <div class="box"><div class="msg-<?=($type ? 'success' : 'failure');?>"><?=$msg;?></div></div>
    <?php endif; ?>
                        <form action="?account=premium-border" method="POST">
                            <div style="width:500px; padding-left:25px;" class="pad2">
                                <div style="display:flex; justify-content: space-between;" id="ipb-container"></div>
                                <div style="display:flex; justify-content: space-between;" id="pb-container"></div>
                            </div>
                            <input type="submit" value="<?=Lang::main('submit');?>">
                            <div class="pad2"></div>
                        </form>
                        <script type="text/javascript">
                            [2, 1, 0, 4, 3].forEach((i, k) => {
                                let icon  = Icon.createUser(2, <?=$this->customicon;?>, 2, null, i, null, Icon.getPrivilegeBorder(<?=$this->reputation;?>));
                                let div   = $WH.ce('div', {id: 'pb-' + i, style: 'display:inline-block'}, icon);
                                let input = $WH.ce('input', {
                                    type:'radio',
                                    name:'avatarborder',
                                    value: i,
                                    id: 'ipb-' + i,
                                    style: 'width:68px; margin:10px 0px;'
                                });

                                $WH.ae($WH.ge('pb-container'), div);
                                $WH.ae($WH.ge('ipb-container'), input);

                                icon.onclick = ((x, evt) => { $WH.ge('ipb-' + x).click(); }).bind(this, i);
                                if (g_user?.settings?.premiumborder === i)
                                    icon.click();
                            });
                        </script>
                    </div>
<?php endif; ?>
                </div>
            </div>

            <script type="text/javascript">
                var _ = new Tabs({parent: $WH.ge('tabs-generic')});
                _.add('<?=Lang::account('tabGeneral');?>', {id: 'general'});
<?php if ($this->cfg('ACC_AUTH_MODE') == AUTH_MODE_SELF): ?>
                _.add('<?=Lang::account('tabPersonal');?>', {id: 'personal'});
<?php endif; ?>
                _.add('<?=Lang::account('tabCommunity');?>', {id: 'community'});
                _.add('<?=Lang::account('tabPremium');?>', {id: 'premium'});
                _.flush();
            </script>
<?php endif; ?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
