        <div id="tab-add-your-comment" style="display: none">
            Denkt bitte an Folgendes, wenn Ihr einen Kommentar schreibt:
            <ul>
            <li><div>Euer Kommentar muss in Deutsch sein, oder er wird entfernt.</div></li>
            <li><div>Nicht sicher, wie Ihr schreiben sollt? Werft einen Blick auf unsere <a href="?help=commenting-and-you" target="_blank">praktische Anleitung</a>!</div></li>
            <li><div>Es ist ratsam, den Kommentar vor dem Einsenden noch einmal auf Richtigkeit zu überprüfen.</div></li>
            <li><div>Stellt Eure Fragen bitte in unseren <a href="?forums">Foren</a>, wenn Ihr eine schnellere Antwort wünscht.</div></li>
            </ul>
<?php
    echo $this->coError ? '            <div class="msg-failure">'.$this->coError."</div>\n            <div class=\"pad\"></div>\n" : '';

    if (User::canComment()):
?>
            <form name="addcomment" action="?comment=add&amp;type=<?=$this->type.'&amp;typeid='.$this->typeId; ?>" method="post" onsubmit="return co_validateForm(this)">
                <div id="funcbox-generic"></div>
                <script type="text/javascript">Listview.funcBox.coEditAppend($('#funcbox-generic'), {body: ''}, 1)</script>
                <div class="pad"></div>
                <input type="submit" value="Absenden"></input>
<?php
    else:
?>
            <form action="/" method="post">
            <div class="comment-edit-body"><textarea class="comment-editbox" rows="10" cols="40" name="commentbody" disabled="disabled"></textarea></div>
<?php
    endif;
    if (!User::$id):
?>
            <small>Ihr seid nicht angemeldet. Bitte <a href="?account=signin">meldet Euch an</a>, oder <a href="?account=signup">registriert Euch</a>, um einen Kommentar einzusenden.</small>
<?php
    endif;
?>
            </form>
        </div>
        <div id="tab-submit-a-screenshot" style="display: none">
            Wählt einfach Euren Screenshot mit dem folgenden Formular aus.
            <ul>
            <li><div>Screenshots aus dem Spiel werden gegenüber Bildern aus dem Modellviewer bevorzugt.</div></li>
            <li><div>Je höher die Qualität, desto besser!</div></li>
            <li><div>Lest Euch unbedingt die <a href="?help=screenshots-tips-tricks" target="_blank">Tipps &amp; Tricks</a> durch, wenn nicht bereits geschehen.</div></li>
            </ul>
<?php
        echo $this->ssError ? '            <div class="msg-failure">'.$this->ssError."</div>\n            <div class=\"pad\"></div>\n" : '';

    if (User::canUploadScreenshot()):
?>
            <form action="?screenshot=add&<?=$this->type.'.'.$this->typeId; ?>" method="post" enctype="multipart/form-data" onsubmit="return ss_validateForm(this)">
            <input type="file" name="screenshotfile" style="width: 35%"/><br />
            <div class="pad2"></div>
            <input type="submit" value="Senden" />
            <div class="pad3"></div>
            <small class="q0">Hinweis: Euer Screenshot muss zunächst zugelassen werden, bevor er auf der Seite erscheint.</small>
<?php
    else:
?>
            <form action="/" method="post">
            <input type="file" name="screenshotfile" disabled="disabled" /><br />
<?php
    endif;
    if (!User::$id):
?>
            <small>Ihr seid nicht angemeldet. Bitte <a href="?account=signin">meldet Euch an</a>, um einen Screenshot einzusenden.</small>
<?php
    endif;
?>
            </form>
        </div>
        <div id="tab-suggest-a-video" style="display: none">
            Gebt einfach die URL des Videos im folgenden Formular ein.
<?php
    if (User::canSuggestVideo()):
?>
            <div class="pad2"></div>
            <form action="?video=add&<?=$this->type.'.'.$this->typeId; ?>" method="post" enctype="multipart/form-data" onsubmit="return vi_validateForm(this)">
            <input type="text" name="videourl" style="width: 35%" /> <small>Unterstützt: nur YouTube</small>
            <div class="pad2"></div>
            <input type="submit" value="Senden" />
            <div class="pad3"></div>
            <small class="q0">Hinweis: Euer Video muss zunächst zugelassen werden, bevor es auf der Seite erscheint.</small>
<?php
    else:
?>
            <form action="/" method="post">
            <input type="text" name="videourl" disabled="disabled" /><br />
<?php
    endif;
    if (!User::$id):
?>
            <small>Ihr seid nicht angemeldet. Bitte <a href="?account=signin">meldet Euch an</a>, um ein Video vorzuschlagen.</small>
<?php
    endif;
?>
            </form>
        </div>
