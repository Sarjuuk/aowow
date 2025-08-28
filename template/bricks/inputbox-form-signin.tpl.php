<?php
    namespace Aowow\Template;

    use \Aowow\Lang;
?>
            <div class="pad3"></div>

            <script type="text/javascript">
                function inputBoxValidate(f)
                {
                    var _ = f.elements[0];
                    if (_.value.length == 0)
                    {
                        $WH.ge('inputbox-error').innerHTML = LANG.message_enterusername;
                        _.focus();
                        return false;
                    }

                    _ = f.elements[1];
                    if (_.value.length == 0)
                    {
                        $WH.ge('inputbox-error').innerHTML = LANG.message_enterpassword;
                        _.focus();
                        return false;
                    }
                }
            </script>

            <form action="<?=$action ?? '.'; ?>" method="post" onsubmit="return inputBoxValidate(this)">
                <div class="inputbox" style="position: relative">
                    <h1><?=$head ?? ''; ?></h1>
                    <div id="inputbox-error"><?=$error ?? ''; ?></div>

                    <table align="center">
                        <tr>
                            <td align="right"><?=Lang::account('user').Lang::main('colon'); ?></td>
                            <td><input type="text" name="username" value="<?=$username ?? ''; ?>" id="username-generic" style="width: 10em" /></td>
                        </tr>
                        <tr>
                            <td align="right"><?=Lang::account('pass').Lang::main('colon'); ?></td>
                            <td><input type="password" name="password" style="width: 10em" /></td>
                        </tr>
                        <tr>
                            <td align="right" valign="top"><input type="checkbox" name="remember_me" id="remember_me" value="yes"<?=($rememberMe ?? '' ? ' checked="checked"' : ''); ?> /></td>
                            <td>
                                <label for="remember_me"><?=Lang::account('rememberMe'); ?></label>
                                <div class="pad2"></div>
                                <input type="submit" value="<?=Lang::account('signIn'); ?>" />
                            </td>
                        </tr>
                    </table>

<?php if ($hasRecovery): ?>
                    <br />
                    <div style="position: absolute; right: 5px; bottom: 5px; white-space: nowrap;"><?=Lang::account('forgot').Lang::main('colon'); ?><a href="?account=forgot-username"><?=Lang::account('forgotUser'); ?></a> | <a href="?account=forgot-password"><?=Lang::account('forgotPass'); ?></a> | <a href="?account=resend"><?=Lang::account('inputbox', 'head', 'resendMail'); ?></a></div>
<?php endif; ?>
                </div>
            </form>

            <div class="pad3"></div>
<?php if ($this->cfg('ACC_ALLOW_REGISTER')): ?>
            <div style="text-align: center; line-height: 1.5em; font-size: 125%"><?=Lang::account('accCreate'); ?></div>
<?php endif; ?>

            <script type="text/javascript">$WH.ge('username-generic').focus()</script>
            <div class="clear"></div>
