{include file='header.tpl'}

        <div id="main">
            <div id="main-precontents"></div>
            <div id="main-contents" class="main-contents">
                <div class="pad3"></div>
                <script type="text/javascript">
                    function inputBoxValidate(f)
                    {ldelim}
                        var _ = f.elements[0];
                        if(_.value.length == 0)
                        {ldelim}
                            $WH.ge('inputbox-error').innerHTML = LANG.message_enterusername;
                            _.focus();
                            return false;
                        {rdelim}

                        _ = f.elements[1];
                        if(_.value.length == 0)
                        {ldelim}
                            $WH.ge('inputbox-error').innerHTML = LANG.message_enterpassword;
                            _.focus();
                            return false;
                        {rdelim}

                        _ = f.elements[2];
                        if(_.value.length == 0)
                        {ldelim}
                            $WH.ge('inputbox-error').innerHTML = message_passwordsdonotmatch;
                            _.focus();
                            return false;
                        {rdelim}
                    {rdelim}
                </script>

                <form action="?account=signup_do&amp;next={$next}" method="post" onsubmit="return inputBoxValidate(this)">
                    <div class="inputbox" style="position: relative">
                        <h1>{$lang.accCreate}</h1>
                        <div id="inputbox-error">{if isset($signupError)}{$signupError}{/if}</div>
                        <table align="center">
                            <tr>
                                <td align="right">{$lang.user}{$lang.colon}</td>
                                <td><input type="text" name="username" value="" maxlength="16" id="username-generic" style="width: 10em" /></td>
                            </tr>
                            <tr>
                                <td align="right">{$lang.pass}{$lang.colon}</td>
                                <td><input type="password" name="password" style="width: 10em" /></td>
                            </tr>
                            <tr>
                                <td align="right">{$lang.passConfirm}{$lang.colon}</td>
                                <td><input type="password" name="c_password" style="width: 10em" /></td>
                            </tr>
                            <tr>
                            <tr>
                                <td align="right">{$lang.email}{$lang.colon}</td>
                                <td><input type="text" name="email" style="width: 10em" /></td>
                            </tr>
                                <td align="right" valign="top"><input type="checkbox" name="remember_me" id="remember_me" value="yes" /></td>
                                <td>
                                    <label for="remember_me">{$lang.rememberMe}</label>
                                    <div class="pad2"></div>
                                    <input type="submit" name="signup" value="{$lang.signup}" />
                                </td>
                            </tr>
                        </table>
                    </div>
                </form>

                <script type="text/javascript">$WH.ge('username-generic').focus()</script>
                <div class="clear"></div>
            </div>

        </div>

{include file='footer.tpl'}
