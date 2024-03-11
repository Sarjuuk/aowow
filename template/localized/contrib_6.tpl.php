        <div id="tab-add-your-comment" style="display: none">
            Ten en cuenta lo siguiente cuando escribas un comentario:
            <ul>
            <li><div>Tu comentario debe ser en español, o será eliminado.</div></li>
            <li><div>¿No estás seguro de cómo escribir? ¡Échale un vistazo a <a href="?help=commenting-and-you" target="_blank">nuestra guía</a>!</div></li>
            <li><div>Por favor, pon tus preguntas en nuestro <a href="?forums">foro</a> para obtener una respuesta más rápida.</div></li>
            <li><div>Deberías corregir tus comentarios antes de enviarlos.</div></li>
            </ul>
<?php
    echo $this->coError ? '            <div class="msg-failure">'.$this->coError."</div>\n            <div class=\"pad\"></div>\n" : '';

    if (User::canComment()):
?>
            <form name="addcomment" action="?comment=add&amp;type=<?=$this->type.'&amp;typeid='.$this->typeId; ?>" method="post" onsubmit="return co_validateForm(this)">
                <div id="funcbox-generic"></div>
                <script type="text/javascript">Listview.funcBox.coEditAppend($('#funcbox-generic'), {body: ''}, 1)</script>
                <div class="pad"></div>
                <input type="submit" value="Enviar"></input>
<?php
    else:
?>
            <form action="/" method="post">
            <div class="comment-edit-body"><textarea class="comment-editbox" rows="10" cols="40" name="commentbody" disabled="disabled"></textarea></div>
<?php
    endif;
    if (!User::$id):
?>
            <small>No has iniciado sesión. Por favor <a href="?account=signin">entra a tu cuenta</a> o <a href="?account=signup">registra una cuenta</a> para añadir tu comentario.</small>
<?php
    endif;
?>
            </form>
        </div>
        <div id="tab-submit-a-screenshot" style="display: none">
            Simplemente busca tu captura de imagen usando el formulario que aparece abajo.
            <ul>
            <li><div>Preferimos imágenes sacadas del juego a capturas de visualizadores de modelos.</div></li>
            <li><div>¡Mientras más calidad mejor!</div></li>
            <li><div>Asegurate de leer las <a href="?help=screenshots-tips-tricks" target="_blank">sugerencias y trucos</a> si no lo has hecho antes.</div></li>
            </ul>
<?php
    echo $this->ssError ? '            <div class="msg-failure">'.$this->ssError."</div>\n            <div class=\"pad\"></div>\n" : '';

    if (User::canUploadScreenshot()):
?>
            <form action="?screenshot=add&<?=$this->type.'.'.$this->typeId; ?>" method="post" enctype="multipart/form-data" onsubmit="return ss_validateForm(this)">
            <input type="file" name="screenshotfile" style="width: 35%"/><br />
            <div class="pad2"></div>
            <input type="submit" value="Enviar" />
            <div class="pad3"></div>
            <small class="q0">Nota: Su captura de imagen deberá ser aprobado antes de aparecer en el sitio.</small>
<?php
    else:
?>
            <form action="/" method="post">
            <input type="file" name="screenshotfile" disabled="disabled" /><br />
<?php
    endif;
    if (!User::$id):
?>
            <small>No has iniciado sesión. <a href="?account=signin">Inicia sesión</a> para enviar una captura de pantalla.</small>
<?php
    endif;
?>
            </form>
        </div>
        <div id="tab-suggest-a-video" style="display: none">
            Símplemente, escribe la URL del vídeo en el formulario.
<?php
    if (User::canSuggestVideo()):
?>
            <div class="pad2"></div>
            <form action="?video=add&<?=$this->type.'.'.$this->typeId; ?>" method="post" enctype="multipart/form-data" onsubmit="return vi_validateForm(this)">
            <input type="text" name="videourl" style="width: 35%" /> <small>Soportado: Sólo YouTube</small>
            <div class="pad2"></div>
            <input type="submit" value="Enviar" />
            <div class="pad3"></div>
            <small class="q0">Nota: Tu vídeo deberá ser aprobado antes de aparecer en el sitio.</small>
<?php
    else:
?>
            <form action="/" method="post">
            <input type="text" name="videourl" disabled="disabled" /><br />
<?php
    endif;
    if (!User::$id):
?>
            <small>No has iniciado sesión. <a href="?account=signin">Inicia sesión</a> para enviar un video.</small>
<?php
    endif;
?>
            </form>
        </div>
