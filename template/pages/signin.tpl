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
                    {rdelim}
                </script>

                <form action="?account=signin_do&amp;next={$next}" method="post" onsubmit="return inputBoxValidate(this)">
                    <div class="inputbox" style="position: relative">
                        <h1>{$lang.doSignIn}</h1>
                        <div id="inputbox-error">{if isset($signinError)}{$signinError}{/if}</div>

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
                                <td align="right" valign="top"><input type="checkbox" name="remember_me" id="remember_me" value="yes" checked="checked" /></td>
                                <td>
                                    <label for="remember_me">{$lang.rememberMe}</label>
                                    <div class="pad2"></div>
                                    <input type="submit" value="{$lang.signIn}" />
                                </td>
                            </tr>
                        </table>
                        <br>
                        <div style="position: absolute; right: 5px; bottom: 5px;">{$lang.forgot}{$lang.colon}<a href="?account=forgotusername">{$lang.user}</a> | <a href="?account=forgotpassword">{$lang.pass}</a></div>
                    </div>
                </form>

                <div class="pad3"></div>
                {if $register}<div style="text-align: center; line-height: 1.5em; font-size: 125%">{$lang.accNoneYet}? <a href="?account=signup">{$lang.accCreateNow}!</a></div>{/if}
                <script type="text/javascript">$WH.ge('username-generic').focus()</script>
                <div class="clear"></div>
            </div>
        </div>

{include file='footer.tpl'}
