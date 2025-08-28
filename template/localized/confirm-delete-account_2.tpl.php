<div class="account-delete-box text">
    <form action="<?=$this->deleteFormTarget;?>" method="POST">
        <h1 class="heading-size-1">Veuillez confirmer la suppression du compte</h1>
        <p class="account-delete-box-alternative">
            Une fois que vous avez cliqué sur le bouton ci-dessous, vous ne pourrez plus revenir en arrière, car nous ne pouvons conserver aucune sauvegarde des données que vous nous demandez de supprimer de nos serveurs. Nous ne pourrons pas non plus vous contacter, car votre adresse e-mail sera supprimée de nos données.<br /><br />
            Pour rappel, le « droit à l'oubli » signifie que nous supprimerons toutes les données personnelles liées à votre compte sur tous les sites et services de notre réseau.<br /><br />
            Ces informations incluront, sans s'y limiter :<br />
        </p>
        <p class="account-delete-box-right-to-be-forgotten">
            <ul>
                <li>Votre identité <?=$this->username;?> et l'adresse e-mail associée à cet identifiant</li>
                <li>Votre statut et vos données Premium actuels, si vous êtes membre Premium</li>
                <li>Vos informations de profil et vos préférences</li>
                <li>Toutes les informations et statistiques spécifiques au jeu directement liées à votre identité</li>
                <li>Dans certains cas, le contenu dont vous êtes l'auteur, y compris les commentaires, les guides et les messages sur les forums.</li>
                <li>Notez que les données de jeu liées à vos identités de jeu réapparaîtront lorsque d'autres utilisateurs demanderont des mises à jour, sauf si vous supprimez ces données à la source.</li>
            </ul>
            <label for="confirm" class="quote"><input type="checkbox" class="checkbox" name="confirm" id="confirm"/> Pour finaliser la procédure « Oublier mon compte », cochez la case suivante, puis cliquez sur le bouton ci-dessous.</label>
        </p>
        <p class="account-delete-box-confirm">
            <input type="hidden" name="key" value="<?=$this->key;?>" />
            <input type="submit" class="button" name="submit" value="Oublier définitivement mon compte" />
            <input type="submit" class="button" name="cancel" value="Annuler la procédure « Oublier mon compte »" />
        </p>
    </form>
</div>
