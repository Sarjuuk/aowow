<div class="account-delete-box text">
    <form action="<?=$this->deleteFormTarget;?>" method="POST">
        <h1>Konto löschen: <?=$this->username;?></h1>
            <p class="account-delete-box-warning">
            <b>WARNUNG!</b> Dieser Prozess ist permanent! Wenn ihr euer Konto und alle eure persönlichen Daten löscht, können sie NICHT wiederhergestellt werden!</p>
        <p class="account-delete-box-alternative">
            <b>Wenn du Hilfe benötigst, um etwas von deinem Konto und nicht dein gesamtes Konto zu löschen, dann wende dich bitte an den <a href="javascript:" onclick="ContactTool.show()">Support</a>.</b>
        </p>
        <p class="account-delete-box-right-to-be-forgotten">
            Eure Daten sind uns nicht egal. Wenn ihr von eurem "Recht auf Vergessenwerden" Gebrauch machen und eure persönlichen Daten aus unserem System entfernen lassen möchtet, dann klickt bitte hier und folgt den Anweisungen auf unserer Unternehmensseite:</p>
        <p class="account-delete-box-confirm">
            <input type="submit" name="proceed" value="Fortfahren" />
        </p>
    </form>
</div>
