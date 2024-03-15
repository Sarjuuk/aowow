        <div id="tab-add-your-comment" style="display: none">
            При написании комментария, просим вас не забывать про следующее:
            <ul>
            <li><div>Ваш комментарий должен быть на русском языке, иначе он будет удален.</div></li>
            <li><div>Не уверены как правильно составить комментарий? Тогда прочтите наше <a href="?help=commenting-and-you" target="_blank">замечательное руководство</a>!</div></li>
            <li><div>Задавайте вопросы на наших <a href="?forums">форумах</a>, чтобы получить гарантированный ответ.</div></li>
            <li><div>У вас может возникнуть желание проверить написание своего комментария перед тем, как поместить его на сайт.</div></li>
            </ul>
<?php
    echo $this->coError ? '            <div class="msg-failure">'.$this->coError."</div>\n            <div class=\"pad\"></div>\n" : '';

    if (User::canComment()):
?>
            <form name="addcomment" action="?comment=add&amp;type=<?=$this->type.'&amp;typeid='.$this->typeId; ?>" method="post" onsubmit="return co_validateForm(this)">
                <div id="funcbox-generic"></div>
                <script type="text/javascript">Listview.funcBox.coEditAppend($('#funcbox-generic'), {body: ''}, 1)</script>
                <div class="pad"></div>
                <input type="submit" value="Отправить"></input>
<?php
    else:
?>
            <form action="/" method="post">
            <div class="comment-edit-body"><textarea class="comment-editbox" rows="10" cols="40" name="commentbody" disabled="disabled"></textarea></div>
<?php
    endif;
    if (!User::$id):
?>
            <small>Вы не вошли на сайт. Пожалуйста <a href="?account=signin">войдите</a> или <a href="?account=signup">зарегистрируйтесь</a>, чтобы добавлять комментарии.</small>
<?php
    endif;
?>
            </form>
        </div>
        <div id="tab-submit-a-screenshot" style="display: none">
            Просмотрите изображение, используя форму ниже.
            <ul>
            <li><div>Внутриигровые изображения предпочтительнее изображений, полученных в программах просмотра 3D моделей.</div></li>
            <li><div>Чем выше качество, тем лучше!</div></li>
            <li><div>Если вы ещё не читали, то настоятельно рекомендуем вам прочесть <a href="?help=screenshots-tips-tricks" target="_blank">советы и особенности</a> получения изображений при помощи снимков экрана.</div></li>
            </ul>
<?php
    echo $this->ssError ? '            <div class="msg-failure">'.$this->ssError."</div>\n            <div class=\"pad\"></div>\n" : '';

    if (User::canUploadScreenshot()):
?>
            <form action="?screenshot=add&<?=$this->type.'.'.$this->typeId; ?>" method="post" enctype="multipart/form-data" onsubmit="return ss_validateForm(this)">
            <input type="file" name="screenshotfile" style="width: 35%"/><br />
            <div class="pad2"></div>
            <input type="submit" value="Отправить" />
            <div class="pad3"></div>
            <small class="q0">Примечание: перед тем как появиться на сайте, ваше Скриншот должны быть утверждены.</small>
<?php
    else:
?>
            <form action="/" method="post">
            <input type="file" name="screenshotfile" disabled="disabled" /><br />
<?php
    endif;
    if (!User::$id):
?>
            <small>Вы не вошли на сайт. Пожалуйста <a href="?account=signin">войдите</a>, чтобы отправить скриншот.</small>
<?php
    endif;
?>
            </form>
        </div>
        <div id="tab-suggest-a-video" style="display: none">
            Введите URL видео на YouTube в форму ниже.
<?php
    if (User::canSuggestVideo()):
?>
            <div class="pad2"></div>
            <form action="?video=add&<?=$this->type.'.'.$this->typeId; ?>" method="post" enctype="multipart/form-data" onsubmit="return vi_validateForm(this)">
            <input type="text" name="videourl" style="width: 35%" /> <small>Поддерживается: только YouTube</small>
            <div class="pad2"></div>
            <input type="submit" value="Отправить" />
            <div class="pad3"></div>
            <small class="q0">Примечание: перед тем как появиться на сайте, ваше видео должно быть одобрено.</small>
<?php
    else:
?>
            <form action="/" method="post">
            <input type="text" name="videourl" disabled="disabled" /><br />
<?php
    endif;
    if (!User::$id):
?>
            <small>Вы не вошли на сайт. Пожалуйста <a href="?account=signin">войдите</a>, чтобы отправить видео.</small>
<?php
    endif;
?>
            </form>
        </div>
