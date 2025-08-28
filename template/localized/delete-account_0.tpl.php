<div class="account-delete-box text">
    <form action="<?=$this->deleteFormTarget;?>" method="POST">
        <h1 class="heading-size-1">Delete Account: <?=$this->username;?></h1>
            <p class="account-delete-box-warning">
            <b>WARNING!</b> This process is permanent! If you choose to delete your account and all your personal information, it CANNOT be recovered!</p>
        <p class="account-delete-box-alternative">
            <b>If you need help deleting something on your account instead of your entire account, please <a href="javascript:" onclick="ContactTool.show()">contact support</a> for help.</b>
        </p>
        <p class="account-delete-box-right-to-be-forgotten">
            We care about your data. If you want to exercise your “right to be forgotten” and request your Personal Data to be removed from our systems, please click here and follow the instructions on our corporate site:</p>
        <p class="account-delete-box-confirm">
            <input type="submit" name="proceed" value="Proceed" />
        </p>
    </form>
</div>
