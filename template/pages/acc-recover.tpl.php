<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">
<?php
    $this->brick('announcement');

    $this->brick('pageTemplate');
?>
            <div class="pad3"></div>
<?php if (!empty($this->text)): ?>
            <div class="inputbox">
                <h1><?=$this->head; ?></h1>
                <div id="inputbox-error"></div>
                <div style="text-align: center; font-size: 110%"><?=$this->text; ?></div>
            </div>
<?php elseif ($this->resetPass): ?>
            <script type="text/javascript">
                function inputBoxValidate(f)
                {
                    _ = f.elements[0];
                    if (_.value.length == 0)
                    {
                        $WH.ge('inputbox-error').innerHTML = LANG.message_enternewpass;
                        _.focus();
                        return false;
                    }
                    else if (_.value.length < 4)
                    {
                        $WH.ge('inputbox-error').innerHTML = LANG.message_passwordmin;
                        _.focus();
                        return false;
                    }

                    _ = f.elements[1];
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

            <form action="?account=signup&amp;next=<?=$this->next . '&amp;token=' . $this->token; ?>" method="post" onsubmit="return inputBoxValidate(this)">
                <div class="inputbox" style="position: relative">
                    <h1><?=$this->head; ?></h1>
                    <div id="inputbox-error"><?=$this->error; ?></div>

                    <table align="center">
                        <tr>
                            <td align="right"><?=Lang::account('email').Lang::main('colon'); ?></td>
                            <td><input type="text" name="email" style="width: 10em" /></td>
                        </tr>
                        <tr>
                            <td align="right"><?=Lang::account('newPass').Lang::main('colon'); ?></td>
                            <td><input type="password" name="password" style="width: 10em" /></td>
                        </tr>
                        <tr>
                            <td align="right"><?=Lang::account('passConfirm').Lang::main('colon'); ?></td>
                            <td><input type="password" name="c_password" style="width: 10em" /></td>
                        </tr>
                        <tr>
                            <td align="right" valign="top"></td>
                            <td><input type="submit" name="signup" value="<?=Lang::account('continue'); ?>" /></td>
                        </tr>
                        <input type="hidden" name="token" value="<?=$this->token; ?>" />
                    </table>

                </div>
            </form>

            <script type="text/javascript">$WH.ge('username-generic').focus()</script>
<?php else: ?>
            <script type="text/javascript">//<![CDATA[
                function inputBoxValidate(f)
                {
                    var e = $('input[name=email]', f);
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
                //]]>
            </script>

            <form action="?account=<?=$this->category[0]; ?>" method="post" onsubmit="return inputBoxValidate(this)">
                <div class="inputbox">
                    <h1><?=$this->head; ?></h1>
                    <div id="inputbox-error"><?=$this->error; ?></div>

                    <div style="text-align: center">
                        <?=Lang::account('email').Lang::main('colon'); ?><input type="text" name="email" value="" id="email-generic" style="width: 12em" />
                        <div class="pad2"></div>

                        <input type="submit" value="<?=Lang::account('continue'); ?>" />
                    </div>

                </div>
            </form>
            <script type="text/javascript">$WH.ge('email-generic').focus()</script>
<?php endif; ?>
            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
