        <div id="tab-add-your-comment" style="display: none">
            Please keep the following in mind when posting a comment:
            <ul>
            <li><div>Your comment must be in English or it will be removed.</div></li>
            <li><div>Unsure how to post? Check out our <a href="?help=commenting-and-you" target="_blank">handy guide</a>!</div></li>
            <li><div>Please post questions on our <a href="?forums">forums</a> for quicker reply.</div></li>
            <li><div>You might want to proof-read your comments before posting them.</div></li>
            </ul>
<?php
    echo $this->coError ? '            <div class="msg-failure">'.$this->coError."</div>\n            <div class=\"pad\"></div>\n" : '';

    if (User::canComment()):
?>
            <form name="addcomment" action="?comment=add&amp;type=<?=$this->type.'&amp;typeid='.$this->typeId; ?>" method="post" onsubmit="return co_validateForm(this)">
                <div id="funcbox-generic"></div>
                <script type="text/javascript">Listview.funcBox.coEditAppend($('#funcbox-generic'), {body: ''}, 1)</script>
                <div class="pad"></div>
                <input type="submit" value="Submit"></input>
<?php
    else:
?>
            <form action="/" method="post">
            <div class="comment-edit-body"><textarea class="comment-editbox" rows="10" cols="40" name="commentbody" disabled="disabled"></textarea></div>
<?php
    endif;
    if (!User::$id):
?>
            <small>You are not logged in. Please <a href="?account=signin">log in</a> or <a href="?account=signup">register an account</a> to add your comment.</small>
<?php
    endif;
?>
            </form>
        </div>
        <div id="tab-submit-a-screenshot" style="display: none">
            Simply browse for your screenshot using the form below.
            <ul>
            <li><div>In-game screenshots are preferred over model-viewer-generated ones.</div></li>
            <li><div>The higher the quality the better!</div></li>
            <li><div>Be sure to read the <a href="?help=screenshots-tips-tricks" target="_blank">tips &amp; tricks</a> if you haven't before.</div></li>
            </ul>
<?php
    echo $this->ssError ? '            <div class="msg-failure">'.$this->ssError."</div>\n            <div class=\"pad\"></div>\n" : '';

    if (User::canUploadScreenshot()):
?>
            <form action="?screenshot=add&<?=$this->type.'.'.$this->typeId; ?>" method="post" enctype="multipart/form-data" onsubmit="return ss_validateForm(this)">
            <input type="file" name="screenshotfile" style="width: 35%"/><br />
            <div class="pad2"></div>
            <input type="submit" value="Submit" />
            <div class="pad3"></div>
            <small class="q0">Note: Your Screenshot will need to be approved before appearing on the site.</small>
<?php
    else:
?>
            <form action="/" method="post">
            <input type="file" name="screenshotfile" disabled="disabled" /><br />
<?php
    endif;
    if (!User::$id):
?>
            <small>You are not signed in. Please <a href="?account=signin">sign in</a> to submit a screenshot.</small>
<?php
    endif;
?>
            </form>
        </div>
        <div id="tab-suggest-a-video" style="display: none">
            Simply type the URL of the video in the form below.
<?php
    if (User::canSuggestVideo()):
?>
            <div class="pad2"></div>
            <form action="?video=add&<?=$this->type.'.'.$this->typeId; ?>" method="post" enctype="multipart/form-data" onsubmit="return vi_validateForm(this)">
            <input type="text" name="videourl" style="width: 35%" /> <small>Supported: YouTube only</small>
            <div class="pad2"></div>
            <input type="submit" value="Submit" />
            <div class="pad3"></div>
            <small class="q0">Note: Your video will need to be approved before appearing on the site.</small>
<?php
    else:
?>
            <form action="/" method="post">
            <input type="text" name="videourl" disabled="disabled" /><br />
<?php
    endif;
    if (!User::$id):
?>
            <small>You are not signed in. Please <a href="?account=signin">sign in</a> to submit a video.</small>
<?php
    endif;
?>
            </form>
        </div>
