<div class="account-delete-box text">
    <form action="<?=$this->deleteFormTarget;?>" method="POST">
        <h1>删除账户：<?=$this->username;?></h1>
            <p class="account-delete-box-warning">
            <b>警告！</b>此步骤将永久地删除您的账户以及所有个人信息，并且不可恢复！</p>
        <p class="account-delete-box-alternative">
            <b>如果您需要删除账户上的部分信息而不是您的完整账户，请 <a href="javascript:" onclick="ContactTool.show()">联系客服</a> 寻求帮助。</b>
        </p>
        <p class="account-delete-box-right-to-be-forgotten">
            我们非常关心您的数据隐私。如果您确认要行使「被遗忘的权利」并且请求从我们的系统中删除您的个人信息，请点击下面的链接访问我们的企业网站。根据提示来完成删除请求：</p>
        <p class="account-delete-box-confirm">
            <input type="submit" name="proceed" value="继续" />
        </p>
    </form>
</div>
