{if $user.id > 0}
        <div id="tab-add-your-comment" style="display: none">
        
            Denkt bitte an Folgendes, wenn Ihr einen Kommentar schreibt:
            
            <ul>
            <li><div>Euer Kommentar muss in Deutsch sein, oder er wird entfernt.</div></li>
            <li><div>Nicht sicher, wie Ihr schreiben sollt? Werft einen Blick auf unsere <a href="?help=commenting-and-you" target="_blank">praktische Anleitung</a>!</div></li>
            <li><div>Es ist ratsam, den Kommentar vor dem Einsenden noch einmal auf Richtigkeit zu überprüfen.</div></li>
            <li><div>Stellt Eure Fragen bitte in unseren <a href="?forums">Foren</a>, wenn Ihr eine schnellere Antwort wünscht.</div></li>
            </ul>
            
            <form name="addcomment" action="?comment=add&amp;type={$page.type}&amp;typeid={$page.typeId}" method="post" onsubmit="return co_validateForm(this)">
                <div id="replybox-generic" style="display: none">
                    Antwort zu einem Kommentar von <span></span>. &nbsp;<a href="javascript:;" onclick="co_cancelReply()">Abbrechen</a>
                    <div class="pad"></div>
                </div>
                <div id="funcbox-generic"></div>
                <script type="text/javascript">Listview.funcBox.coEditAppend(ge('funcbox-generic'), {ldelim}body: ''{rdelim}, 1)</script>
                <div class="pad"></div>
                <input type="submit" value="Absenden"></input>
                <input type="hidden" name="replyto" value=""></input>
            </form>

        </div>

        <div id="tab-submit-a-screenshot" style="display: none">

            Wählt einfach Euren Screenshot mit dem folgenden Formular aus.

            <ul>
            <li><div>Screenshots aus dem Spiel werden gegenüber Bildern aus dem Modellviewer bevorzugt.</div></li>
            <li><div>Je höher die Qualität, desto besser!</div></li>
            <li><div>Lest Euch unbedingt die <a href="?help=screenshots-tips-tricks" target="_blank">Tipps &amp; Tricks</a> durch, wenn nicht bereits geschehen.</div></li>
            </ul>

            <form action="?screenshot=add&{$page.type}.{$page.typeId}" method="post" enctype="multipart/form-data" onsubmit="return ss_validateForm(this)">

            Datei: <input type="file" name="screenshotfile" style="width: 35%"/><br />
            <div class="pad2"></div>
            Titel: <input type="text" name="screenshotcaption" maxlength="200" /> <small>Optional, bis zu 200 Zeichen</small><br />
            <div class="pad2"></div>
            <input type="submit" value="Senden" />

            <div class="pad3"></div>
            <small class="q0">Hinweis: Euer Screenshot muss zunächst zugelassen werden, bevor er auf der Seite erscheint.</small>

            </form>

        </div>

        <div id="tab-suggest-a-video" style="display: none">

            Gebt einfach die URL des Videos im folgenden Formular ein.

            <div class="pad2"></div>
            <form action="?video=add&{$page.type}.{$page.typeId}" method="post" enctype="multipart/form-data" onsubmit="return vi_validateForm(this)">

            URL: <input type="text" name="videourl" style="width: 35%" /> <small>Unterstützt: nur YouTube</small>
            <div class="pad2"></div>
            Titel: <input type="text" name="videotitle" maxlength="200" /> <small>Optional, bis zu 200 Zeichen</small><br />
            <div class="pad"></div>
            <input type="submit" value="Senden" />

            <div class="pad3"></div>
            <small class="q0">Hinweis: Euer Video muss zunächst zugelassen werden, bevor es auf der Seite erscheint.</small>

            </form>

        </div>
{else}
        <div id="tab-add-your-comment" style="display: none">
        
            Denkt bitte an Folgendes, wenn Ihr einen Kommentar schreibt:
            
            <ul>
            <li><div>Euer Kommentar muss in Deutsch sein, oder er wird entfernt.</div></li>
            <li><div>Nicht sicher, wie Ihr schreiben sollt? Werft einen Blick auf unsere <a href="?help=commenting-and-you" target="_blank">praktische Anleitung</a>!</div></li>
            <li><div>Es ist ratsam, den Kommentar vor dem Einsenden noch einmal auf Richtigkeit zu überprüfen.</div></li>
            <li><div>Stellt Eure Fragen bitte in unseren <a href="?forums">Foren</a>, wenn Ihr eine schnellere Antwort wünscht.</div></li>
            </ul>
            
            <form action="/" method="post">

            <div class="comment-edit-body"><textarea class="comment-editbox" rows="10" cols="40" name="commentbody" disabled="disabled"></textarea></div>
            <small>Ihr seid nicht angemeldet. Bitte <a href="?account=signin">meldet Euch an</a>, oder <a href="?account=signup">registriert Euch</a>, um einen Kommentar einzusenden.</small>

            </form>

        </div>

        <div id="tab-submit-a-screenshot" style="display: none">

            Wählt einfach Euren Screenshot mit dem folgenden Formular aus.

            <ul>
            <li><div>Screenshots aus dem Spiel werden gegenüber Bildern aus dem Modellviewer bevorzugt.</div></li>
            <li><div>Je höher die Qualität, desto besser!</div></li>
            <li><div>Lest Euch unbedingt die <a href="?help=screenshots-tips-tricks" target="_blank">Tipps &amp; Tricks</a> durch, wenn nicht bereits geschehen.</div></li>
            </ul>

            <form action="/" method="post">

            <input type="file" name="screenshotfile" disabled="disabled" /><br />
            <small>Ihr seid nicht angemeldet. Bitte <a href="?account=signin">meldet Euch an</a>, um einen Screenshot einzusenden.</small>

            </form>

        </div>

        <div id="tab-suggest-a-video" style="display: none">

            Gebt einfach die URL des Videos im folgenden Formular ein.

            <div class="pad2"></div>
            <form action="/video=add&amp;13.7" method="post" enctype="multipart/form-data" onsubmit="return vi_validateForm(this)">

            URL: <input type="text" name="videourl" style="width: 35%" /> <small>Unterstützt: nur YouTube</small>
            <div class="pad"></div>
            <input type="submit" value="Senden" />

            <div class="pad3"></div>
            <small class="q0">Hinweis: Euer Video muss zunächst zugelassen werden, bevor es auf der Seite erscheint.</small>

            </form>

        </div>
{/if}
