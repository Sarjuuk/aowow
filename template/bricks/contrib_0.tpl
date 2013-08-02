{if $user.id > 0}
        <div id="tab-add-your-comment" style="display: none">

            Please keep the following in mind when posting a comment:

            <ul>
            <li><div>Your comment must be in English or it will be removed.</div></li>
            <li><div>Unsure how to post? Check out our <a href="?help=commenting-and-you" target="_blank">handy guide</a>!</div></li>
            <li><div>Please post questions on our <a href="?forums">forums</a> for quicker reply.</div></li>
            <li><div>You might want to proof-read your comments before posting them.</div></li>
            </ul>

            <form name="addcomment" action="?comment=add&amp;type={$page.type}&amp;typeid={$page.typeId}" method="post" onsubmit="return co_validateForm(this)">
                <div id="replybox-generic" style="display: none">
                    The answer to a comment from <span></span>. &nbsp;<a href="javascript:;" onclick="co_cancelReply()">Cancel</a>
                    <div class="pad"></div>
                </div>
                <div id="funcbox-generic"></div>
                <script type="text/javascript">Listview.funcBox.coEditAppend($WH.ge('funcbox-generic'), {ldelim}body: ''{rdelim}, 1)</script>
                <div class="pad"></div>
                <input type="submit" value="Submit"></input>
                <input type="hidden" name="replyto" value=""></input>
            </form>

        </div>
        <div id="tab-submit-a-screenshot" style="display: none">

            Simply browse for your screenshot using the form below.

            <ul>
            <li><div>In-game screenshots are preferred over model-viewer-generated ones.</div></li>
            <li><div>The higher the quality the better!</div></li>
            <li><div>Be sure to read the <a href="?help=screenshots-tips-tricks" target="_blank">tips &amp; tricks</a> if you haven't before.</div></li>
            </ul>

            <form action="?screenshot=add&{$page.type}.{$page.typeId}" method="post" enctype="multipart/form-data" onsubmit="return ss_validateForm(this)">

            File: <input type="file" name="screenshotfile" style="width: 35%"/><br />
            <div class="pad2"></div>
            Caption: <input type="text" name="screenshotcaption" maxlength="200" /> <small>Optional, up to 200 characters</small><br />
            <div class="pad2"></div>
            <input type="submit" value="Submit" />

            <div class="pad3"></div>
            <small class="q0">Note: Your Screenshot will need to be approved before appearing on the site.</small>

            </form>

        </div>
        <div id="tab-suggest-a-video" style="display: none">

            Simply type the URL of the video in the form below.

            <div class="pad2"></div>
            <form action="?video=add&{$page.type}.{$page.typeId}" method="post" enctype="multipart/form-data" onsubmit="return vi_validateForm(this)">

            URL: <input type="text" name="videourl" style="width: 35%" /> <small>Supported: YouTube only</small>
            <div class="pad2"></div>
            Title: <input type="text" name="videotitle" maxlength="200" /> <small>Optional, up to 200 characters</small><br />
            <div class="pad"></div>
            <input type="submit" value="Submit" />

            <div class="pad3"></div>
            <small class="q0">Note: Your video will need to be approved before appearing on the site.</small>

            </form>

        </div>
{else}
        <div id="tab-add-your-comment" style="display: none">

            Please keep the following in mind when posting a comment:

            <ul>
            <li><div>Your comment must be in English or it will be removed.</div></li>
            <li><div>Unsure how to post? Check out our <a href="?help=commenting-and-you" target="_blank">handy guide</a>!</div></li>
            <li><div>Please post questions on our <a href="?forums">forums</a> for quicker reply.</div></li>
            <li><div>You might want to proof-read your comments before posting them.</div></li>
            </ul>

            <form action="/" method="post">

            <div class="comment-edit-body"><textarea class="comment-editbox" rows="10" cols="40" name="commentbody" disabled="disabled"></textarea></div>
            <small>You are not logged in. Please <a href="?account=signin">log in</a> or <a href="?account=signup">register an account</a> to add your comment.</small>

            </form>

        </div>
        <div id="tab-submit-a-screenshot" style="display: none">

            Simply browse for your screenshot using the form below.

            <ul>
            <li><div>In-game screenshots are preferred over model-viewer-generated ones.</div></li>
            <li><div>The higher the quality the better!</div></li>
            <li><div>Be sure to read the <a href="?help=screenshots-tips-tricks" target="_blank">tips &amp; tricks</a> if you haven't before.</div></li>
            </ul>

            <form action="/" method="post">

            <input type="file" name="screenshotfile" disabled="disabled" /><br />
            <small>You are not signed in. Please <a href="?account=signin">sign in</a> to submit a screenshot.</small>

            </form>

        </div>
        <div id="tab-suggest-a-video" style="display: none">

            Simply type the URL of the video in the form below.

            <div class="pad2"></div>
            <form action="/" method="post">

            <input type="text" name="videourl" disabled="disabled" /><br />
            <small>You are not signed in. Please <a href="?account=signin">sign in</a> to submit a video.</small>

            </form>

        </div>
{/if}
