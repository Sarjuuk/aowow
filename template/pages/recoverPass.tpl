{include file='header.tpl'}

		<div id="main">
            <div id="main-precontents"></div>
            <div id="main-contents" class="main-contents">
                <div class="pad3"></div>

                <form action="/account=forgotpassword" method="post" onsubmit="return inputBoxValidate(this)">
                    <div class="inputbox">
                        <h1>[Password Reset: Step 1 of 2]</h1>
                        <div id="inputbox-error"></div>

                        <div style="text-align: center">
                        [Email address]{$lang.colon}<input type="text" name="email" value="" id="nmbv325c32" style="width: 12em" />
                        <div class="pad2"></div>

                        <div id="captcha-reprecated" class="captcha-center"></div>
                        <div class="pad2"></div>

                        <input type="submit" value="Continue" />
                        </div>

                    </div>
                </form>

                <script type="text/javascript">$WH.ge('username-generic').focus()</script>
                <div class="clear"></div>
            </div>

        </div>

{include file='footer.tpl'}
