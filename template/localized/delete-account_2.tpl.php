<div class="account-delete-box text">
    <form action="<?=$this->deleteFormTarget;?>" method="POST">
        <h1>Supprimer le compte : <?=$this->username;?></h1>
            <p class="account-delete-box-warning">
            <b>ATTENTION !</b> Cette action est définitive ! Si vous choisissez de supprimer votre compte et toutes vos informations personnelles, il NE POURRA PAS être récupéré.</p>
        <p class="account-delete-box-alternative">
            <b>Si vous avez besoin d'aide pour supprimer quelque chose sur votre compte plutôt que votre compte entier, veuillez contacter <a href="javascript:" onclick="ContactTool.show()">l'assistance clientèle</a> afin qu'elle puisse vous y aider.</b>
        </p>
        <p class="account-delete-box-right-to-be-forgotten">
            Vos données sont importantes à nos yeux. Si vous voulez exercer votre droit à l'oubli et demander à ce que vos données personnelles soient supprimées de nos systèmes, veuillez cliquer ici et suivre les instructions sur le site de notre entreprise : </p>
        <p class="account-delete-box-confirm">
            <input type="submit" name="proceed" value="Continuer" />
        </p>
    </form>
</div>
