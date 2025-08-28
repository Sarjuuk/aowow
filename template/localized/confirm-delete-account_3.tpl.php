<div class="account-delete-box text">
    <form action="<?=$this->deleteFormTarget;?>" method="POST">
        <h1 class="heading-size-1">Bitte Kontolöschung bestätigen</h1>
        <p class="account-delete-box-alternative">
            Sobald Sie die Schaltfläche unten gedrückt haben, können Sie die Löschung nicht mehr rückgängig machen, da wir keine Sicherungskopien der Daten speichern können, die Sie von unseren Servern löschen lassen möchten. Wir können Sie auch nicht kontaktieren, da Ihre E-Mail-Adresse definitiv aus unseren Daten gelöscht wird.<br /><br />
            Zur Erinnerung: Das „Recht auf Vergessenwerden“ bedeutet, dass wir alle personenbezogenen Daten, die mit Ihrem Konto verknüpft sind, auf allen Websites und Diensten unseres Netzwerks entfernen.<br /><br />
            Zu diesen Informationen gehören unter anderem:<br />
        </p>
        <p class="account-delete-box-right-to-be-forgotten">
            <ul>
                <li>Ihre Identität <?=$this->username;?> und die mit diesem Login verknüpfte E-Mail-Adresse</li>
                <li>Ihren aktuellen Premium-Status und die dazugehörigen Daten, falls Sie Premium-Mitglied sind</li>
                <li>Ihre Profilinformationen und -einstellungen</li>
                <li>Alle spielbezogenen Informationen und Statistiken, die direkt mit Ihrer Identität verknüpft sind</li>
                <li>In einigen Fällen von Ihnen verfasste Inhalte, einschließlich Kommentare, Leitfäden und Forenbeiträge.</li>
                <li>Beachten Sie, dass Spieldaten, die mit Ihren Spielidentitäten verknüpft sind, wieder angezeigt werden, wenn andere Nutzer Datenaktualisierungen anfordern, es sei denn, Sie löschen diese Daten an der Quelle.</li>
            </ul>
            <label for="confirm" class="quote"><input type="checkbox" class="checkbox" name="confirm" id="confirm"/> Um den „Vergessen“-Vorgang abzuschließen, aktivieren Sie das folgende Kontrollkästchen und klicken Sie anschließend auf die Schaltfläche unten.</label>
        </p>
        <p class="account-delete-box-confirm">
            <input type="hidden" name="key" value="<?=$this->key;?>" />
            <input type="submit" class="button" name="submit" value="Mein Konto dauerhaft vergessen" />
            <input type="submit" class="button" name="cancel" value="„Vergessen“-Vorgang abbrechen" />
        </p>
    </form>
</div>
