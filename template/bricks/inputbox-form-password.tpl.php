<?php
    namespace Aowow\Template;

    use \Aowow\Lang;
?>

            <div class="pad3"></div>

            <script type="text/javascript">
                function inputBoxValidate(f)
                {
                    var password = f.elements.password;
                    if (password.value.length == 0)
                    {
                        $WH.ge('inputbox-error').innerHTML = LANG.message_enternewpass;
                        password.focus();
                        return false;
                    }
                    if (!g_isNewPasswordValid(password.value))
                    {
                        $WH.ge('inputbox-error').innerHTML = LANG.message_passwordmin;
                        password.focus();
                        return false;
                    }
                    if (f.elements.c_password.value !== password.value)
                    {
                        $WH.ge('inputbox-error').innerHTML = LANG.message_passwordsdonotmatch;
                        f.elements.c_password.focus();
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
                            <td align="right"><?=Lang::account('email').Lang::main('colon'); ?></td>
                            <td><input type="text" name="email" style="width: 10em" /></td>
                        </tr>
                        <tr>
                            <td align="right"><?=Lang::account('newPass'); ?></td>
                            <td><input type="password" name="password" style="width: 10em" /></td>
                        </tr>
                        <tr>
                            <td align="right"><?=Lang::account('passConfirm'); ?></td>
                            <td><input type="password" name="c_password" style="width: 10em" /></td>
                        </tr>
                        <tr>
                            <td align="right" valign="top"></td>
                            <td><input type="submit" name="signup" value="<?=Lang::account('continue'); ?>" /></td>
                        </tr>
                    </table>

                    <input type="hidden" name="key" value="<?=$token ?? ''; ?>" />
                </div>
                <?=$this->csrfField();?>
            </form>

            <script type="text/javascript">document.querySelector('input[name=email]').focus()</script>
