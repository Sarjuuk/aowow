        <div id="tab-add-your-comment" style="display: none">
            发表评论时请记住以下几点：
            <ul>
            <li><div>你的评论必须是中文否则可能会被移除。</div></li>
            <li><div>不知道如何发表？看看我们的<a href="?help=commenting-and-you" target="_blank">指南</a>！</div></li>
            <li><div>请发表你的疑问在我们的<a href="?forums">论坛</a> 以获得更快的答复。</div></li>
            <li><div>你在发表前最好先预览下你的评论。</div></li>
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
            <small>你尚未登录，请先<a href="?account=signin">登录</a>或<a href="?account=signup">注册一个账号</a> 以发表你的评论。</small>
<?php
    endif;
?>
            </form>
        </div>
        <div id="tab-submit-a-screenshot" style="display: none">
            你的截图最好是如下几种情形：
            <ul>
            <li><div>游戏内截图比模型查看器生成的更优先。</div></li>
            <li><div>越高的质量越好！</div></li>
            <li><div>请阅读我们的<a href="?help=screenshots-tips-tricks" target="_blank">提示和技巧</a>假如你还没看过的话。</div></li>
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
            <small class="q0">注意：你的截图将在审查后才会出现在站点上。</small>
<?php
    else:
?>
            <form action="/" method="post">
            <input type="file" name="screenshotfile" disabled="disabled" /><br />
<?php
    endif;
    if (!User::$id):
?>
            <small>你尚未登录，请先<a href="?account=signin">登录</a>以提交截图。</small>
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
