<div class="account-delete-box text">
    <form action="<?=$this->deleteFormTarget;?>" method="POST">
        <h1 class="heading-size-1">请确认删除账户</h1>
        <p class="account-delete-box-alternative">
            一旦您点击下方按钮，将无法撤消，因为我们无法备份您要求我们从服务器中删除的任何数据。我们也无法联系到您，因为我们肯定会从数据中清除您的电子邮件地址。<br /><br />
            提醒一下，“被遗忘权”意味着我们将从我们网络中的所有网站和服务中删除与您的帐户相关的所有个人数据。<br /><br />
            这些信息包括但不限于：<br />
        </p>
        <p class="account-delete-box-right-to-be-forgotten">
            <ul>
                <li>您的身份<?=$this->username;?>，以及与此登录关联的电子邮件地址</li>
                <li>您当前的高级会员状态和数据（如果您是高级会员）</li>
                <li>您的个人资料信息和偏好设置。</li>
                <li>任何与您的身份直接关联的游戏特定信息和统计信息。</li>
                <li>在某些情况下，您撰写的内容，包括评论、指南和论坛帖子。</li>
                <li>请注意，游戏数据已关联除非您从源头删除该数据，否则您的游戏身份信息将在其他用户请求数据更新时重新显示。</li>
            </ul>
            <label for="confirm" class="quote"><input type="checkbox" class="checkbox" name="confirm" id="confirm"/> 要完成“忘记我”流程，请勾选以下方框，然后点击下方按钮。</label>
        </p>
        <p class="account-delete-box-confirm">
            <input type="hidden" name="key" value="<?=$this->key;?>" />
            <input type="submit" class="button" name="submit" value="永久忘记我的账户" />
            <input type="submit" class="button" name="cancel" value="取消“忘记我”流程" />
        </p>
    </form>
</div>
