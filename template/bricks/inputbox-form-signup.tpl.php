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
                    else if (_.value.length < 4)
                    {
                        $WH.ge('inputbox-error').innerHTML = LANG.message_usernamemin;
                        _.focus();
                        return false;
                    }
                    else if (!g_isUsernameValid(_.value))
                    {
                        $WH.ge('inputbox-error').innerHTML = LANG.message_usernamenotvalid;
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
                    else if (_.value.length < 4)
                    {
                        $WH.ge('inputbox-error').innerHTML = LANG.message_passwordmin;
                        _.focus();
                        return false;
                    }

                    _ = f.elements[2];
                    if (_.value.length == 0 || f.elements[1].value != _.value)
                    {
                        $WH.ge('inputbox-error').innerHTML = LANG.message_passwordsdonotmatch;
                        _.focus();
                        return false;
                    }

                    var e = $('input[name=email]', f)
                    if (e.val().length == 0)
                    {
                        $WH.ge('inputbox-error').innerHTML = LANG.message_enteremail;
                        e.focus();
                        return false;
                    }

                    if (!g_isEmailValid(e.val()))
                    {
                        $WH.ge('inputbox-error').innerHTML = LANG.message_emailnotvalid;
                        e.focus();
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
                            <td><input type="text" name="username" value="<?=$username ?? ''; ?>" maxlength="16" id="username-generic" style="width: 10em" /></td>
                        </tr>
                        <tr>
                            <td align="right"><?=Lang::account('pass').Lang::main('colon'); ?></td>
                            <td><input type="password" name="password" style="width: 10em" /></td>
                        </tr>
                        <tr>
                            <td align="right"><?=Lang::account('passConfirm').Lang::main('colon'); ?></td>
                            <td><input type="password" name="c_password" style="width: 10em" /></td>
                        </tr>
                        <tr>
                        <tr>
                            <td align="right"><?=Lang::account('email').Lang::main('colon'); ?></td>
                            <td><input type="text" name="email" value="<?=$email ?? ''; ?>" style="width: 10em" /></td>
                        </tr>
                            <td align="right" valign="top"><input type="checkbox" name="remember_me" id="remember_me" value="yes"<?=($rememberMe ?? '' ? ' checked="checked"' : ''); ?> /></td>
                            <td>
                                <label for="remember_me"><?=Lang::account('rememberMe'); ?></label>
                                <div class="pad2"></div>
                                <input type="submit" name="signup" value="<?=Lang::account('continue'); ?>" />
                            </td>
                        </tr>
                    </table>

                </div>
            </form>

            <script type="text/javascript">$WH.ge('username-generic').focus()</script>
