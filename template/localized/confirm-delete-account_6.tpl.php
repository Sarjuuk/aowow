<div class="account-delete-box text">
    <form action="<?=$this->deleteFormTarget;?>" method="POST">
        <h1 class="heading-size-1">Confirmar la eliminación de la cuenta</h1>
        <p class="account-delete-box-alternative">
            Una vez que presione el botón de abajo, no podrá deshacerlo ni volver atrás, ya que no podemos guardar una copia de seguridad de los datos que nos solicita eliminar de nuestros servidores. Tampoco podremos contactarte, ya que tu dirección de correo electrónico será eliminada de nuestros datos.<br /><br />
            Te recordamos que el "derecho al olvido" implica que eliminaremos cualquier dato personal vinculado a tu cuenta en todos los sitios y servicios de nuestra red.<br /><br />
            Esta información incluirá, entre otros:<br />
        </p>
        <p class="account-delete-box-right-to-be-forgotten">
            <ul>
                <li>Tu identidad <?=$this->username;?> y la dirección de correo electrónico asociada a este inicio de sesión</li>
                <li>Tu estado y datos Premium actuales, si eres miembro Premium</li>
                <li>Tu información de perfil y preferencias</li>
                <li>Cualquier información y estadísticas específicas del juego directamente vinculadas a tu identidad</li>
                <li>En algunos casos, contenido de tu autoría, incluyendo comentarios, guías y publicaciones en el foro</li>
                <li>Ten en cuenta que los datos de juego asociados a tus identidades de juego volverán a aparecer cuando otros usuarios soliciten actualizaciones, a menos que los elimines en la fuente.</li>
            </ul>
            <label for="confirm" class="quote"><input type="checkbox" class="checkbox" name="confirm" id="confirm"/> Para finalizar el proceso de "Olvídate de mí", marca la casilla y haz clic en el botón de abajo.</label>
        </p>
        <p class="account-delete-box-confirm">
            <input type="hidden" name="key" value="<?=$this->key;?>" />
            <input type="submit" class="button" name="submit" value="Olvidar mi cuenta permanentemente" />
            <input type="submit" class="button" name="cancel" value="Cancelar el proceso de &quot;Olvidarme&quot;" />
        </p>
    </form>
</div>
