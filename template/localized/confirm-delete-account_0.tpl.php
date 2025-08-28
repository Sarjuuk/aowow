<div class="account-delete-box text">
    <form action="<?=$this->deleteFormTarget;?>" method="POST">
        <h1 class="heading-size-1">Please Confirm Account Deletion</h1>
        <p class="account-delete-box-alternative">
            Once you press the button below, there is no undo, no turning back, since we can’t keep a backup of any data that you are asking us to delete from our servers. We also won’t be able to contact you, because your email address is definitely something we’ll purge from our data.<br /><br />
            As a reminder, the “right to be forgotten” means that we will be removing any Personal Data linked to your account across all of the sites and services in our network.<br /><br />
            This information will include, but is not limited to:<br />
        </p>
        <p class="account-delete-box-right-to-be-forgotten">
            <ul>
                <li>Your Identity <?=$this->username;?>, and the email address associated with this login</li>
                <li>Your current Premium status and data, should you be a Premium member</li>
                <li>Your profile information and preferences.</li>
                <li>Any game-specific information and statistics directly linked to your Identity.</li>
                <li>In some cases, content that you've authored, including comments, guides and forum posts.</li>
                <li>Note that game data connected to your gaming identities will re-appear when other users request data updates, unless you delete that data at the source.</li>
            </ul>
            <label for="confirm" class="quote"><input type="checkbox" class="checkbox" name="confirm" id="confirm"/> To finalize the Forget Me Process check the following box, and then click the button below.</label>
        </p>
        <p class="account-delete-box-confirm">
            <input type="hidden" name="key" value="<?=$this->key;?>" />
            <input type="submit" class="button" name="submit" value="Permanently Forget My Account" />
            <input type="submit" class="button" name="cancel" value="Cancel Forget Me Process" />
        </p>
    </form>
</div>
