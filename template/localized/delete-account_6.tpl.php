<div class="account-delete-box text">
    <form action="<?=$this->deleteFormTarget;?>" method="POST">
        <h1>Eliminar Cuenta: <?=$this->username;?></h1>
            <p class="account-delete-box-warning">
            <b>CUIDADO!</b> ¡Este proceso es permanente e irreversible! Si eliges eliminar tu cuenta e información personal, NO podrás recuperarla!</p>
        <p class="account-delete-box-alternative">
            <b>Si necesitas ayuda para eliminar algo de tu cuenta en lugar de su totalidad, por favor contacta con nuestro <a href="javascript:" onclick="ContactTool.show()">soporte técnico</a>.</b>
        </p>
        <p class="account-delete-box-right-to-be-forgotten">
            Nos importan tus datos personales. Si quieres ejercer tu "derecho a ser olvidado" y solicitar que tus Datos Personales sean eliminados de nuestros sistemas, por favor haz click aquí y sigue las instrucciones de nuestra página web corporativa:</p>
        <p class="account-delete-box-confirm">
            <input type="submit" name="proceed" value="Procedar" />
        </p>
    </form>
</div>
