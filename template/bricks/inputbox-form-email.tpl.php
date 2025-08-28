<?php
    namespace Aowow\Template;

    use \Aowow\Lang;
?>
            <div class="pad3"></div>

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

            <form action="<?=$action ?? '.'; ?>" method="post" onsubmit="return inputBoxValidate(this)">
                <div class="inputbox">
                    <h1><?=$head ?? ''; ?></h1>
                    <div id="inputbox-error"><?=$error ?? ''; ?></div>
<?php if ($message ?? ''): ?>
                    <?=$message; ?>
                    <div class="pad2"></div>
<?php endif; ?>
                    <div style="text-align: center">
                        <?=Lang::account('email').Lang::main('colon'); ?><input type="text" name="email" value="" id="email-generic" style="width: 12em" />
                        <div class="pad2"></div>

                        <input type="submit" value="<?=Lang::account('continue'); ?>" />
                    </div>

                </div>
            </form>
            <script type="text/javascript">$WH.ge('email-generic').focus()</script>
