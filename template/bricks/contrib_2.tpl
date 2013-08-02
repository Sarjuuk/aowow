{if $user.id > 0}
        <div id="tab-add-your-comment" style="display: none">

            Gardez à l'esprit les points suivant avant de poster un commentaire :

            <ul>
            <li><div>Votre commentaire doit être en Français ou il sera supprimé.</div></li>
            <li><div>Pas certain de savoir comment écrire un commentaire ? Consultez notre <a href="?help=commenting-and-you" target="_blank">guide pratique</a> !</div></li>
            <li><div>Posez vos questions sur le <a href="?forums">forum</a> afin d'avoir une réponse plus rapide.</div></li>
            <li><div>Il serait avisé de corriger vos fautes avant de soumettre vos commentaires.</div></li>
            </ul>

            <form name="addcomment" action="?comment=add&amp;type={$page.type}&amp;typeid={$page.typeId}" method="post" onsubmit="return co_validateForm(this)">
                <div id="replybox-generic" style="display: none">
                    The answer to a comment from <span></span>. &nbsp;<a href="javascript:;" onclick="co_cancelReply()">Cancel</a>
                    <div class="pad"></div>
                </div>
                <div id="funcbox-generic"></div>
                <script type="text/javascript">Listview.funcBox.coEditAppend($WH.ge('funcbox-generic'), {ldelim}body: ''{rdelim}, 1)</script>
                <div class="pad"></div>
                <input type="submit" value="Submit"></input>
                <input type="hidden" name="replyto" value=""></input>
            </form>

        </div>
        <div id="tab-submit-a-screenshot" style="display: none">

            Sélectionez votre capture en utilisant le formulaire ci-dessous.

            <ul>
            <li><div>Les captures prises dans le jeu sont préférées à celles générées avec un logiciel.</div></li>
            <li><div>Plus la qualité est haute, mieux c'est !</div></li>
            <li><div>Assurez-vous de lire les <a href="?help=screenshots-tips-tricks" target="_blank">trucs et astuces</a> si ce n'est pas déjà fait.</div></li>
            </ul>

            <form action="?screenshot=add&{$page.type}.{$page.typeId}" method="post" enctype="multipart/form-data" onsubmit="return ss_validateForm(this)">

            File: <input type="file" name="screenshotfile" style="width: 35%"/><br />
            <div class="pad2"></div>
            Caption: <input type="text" name="screenshotcaption" maxlength="200" /> <small>Optional, up to 200 characters</small><br />
            <div class="pad2"></div>
            <input type="submit" value="Submit" />

            <div class="pad3"></div>
            <small class="q0">Note: Votre capture d'écran devra être approuvé avant d'apparaitre sur le site.</small>

            </form>

        </div>
        <div id="tab-suggest-a-video" style="display: none">

            Entrez simplement l'URL du vidéo dans le formulaire ci-dessous.

            <div class="pad2"></div>
            <form action="?video=add&{$page.type}.{$page.typeId}" method="post" enctype="multipart/form-data" onsubmit="return vi_validateForm(this)">

            URL: <input type="text" name="videourl" style="width: 35%" /> <small>Supporté: Youtube seulement</small>
            <div class="pad2"></div>
            Title: <input type="text" name="videotitle" maxlength="200" /> <small>Optional, up to 200 characters</small><br />
            <div class="pad"></div>
            <input type="submit" value="Submit" />

            <div class="pad3"></div>
            <small class="q0">Note: Votre vidéo devra être approuvé avant d'apparaitre sur le site.</small>

            </form>

        </div>
{else}
        <div id="tab-add-your-comment" style="display: none">

            Gardez à l'esprit les points suivant avant de poster un commentaire :

            <ul>
            <li><div>Votre commentaire doit être en Français ou il sera supprimé.</div></li>
            <li><div>Pas certain de savoir comment écrire un commentaire ? Consultez notre <a href="?help=commenting-and-you" target="_blank">guide pratique</a> !</div></li>
            <li><div>Posez vos questions sur le <a href="?forums">forum</a> afin d'avoir une réponse plus rapide.</div></li>
            <li><div>Il serait avisé de corriger vos fautes avant de soumettre vos commentaires.</div></li>
            </ul>

            <form action="/" method="post">

            <div class="comment-edit-body"><textarea class="comment-editbox" rows="10" cols="40" name="commentbody" disabled="disabled"></textarea></div>
            <small>Vous n'êtes pas connecté(e). Veuillez vous <a href="?account=signin">connecter</a> ou vous <a href="?account=signup">inscrire</a> pour ajouter votre commentaire.</small>

            </form>

        </div>
        <div id="tab-submit-a-screenshot" style="display: none">

            Sélectionez votre capture en utilisant le formulaire ci-dessous.

            <ul>
            <li><div>Les captures prises dans le jeu sont préférées à celles générées avec un logiciel.</div></li>
            <li><div>Plus la qualité est haute, mieux c'est !</div></li>
            <li><div>Assurez-vous de lire les <a href="?help=screenshots-tips-tricks" target="_blank">trucs et astuces</a> si ce n'est pas déjà fait.</div></li>
            </ul>

            <form action="/" method="post">

            <input type="file" name="screenshotfile" disabled="disabled" /><br />
            <small>Vous n'êtes pas connecté(e). Veuillez vous <a href="?account=siginin">connecter</a> pour envoyer une capture d'écran.</small>

            </form>

        </div>
        <div id="tab-suggest-a-video" style="display: none">

            Entrez simplement l'URL du vidéo dans le formulaire ci-dessous.

            <div class="pad2"></div>
            <form action="/" method="post">

            <input type="text" name="videourl" disabled="disabled" /><br />
            <small>Vous n'êtes pas connecté(e). Veuillez vous <a href="?account=siginin">connecter</a> pour envoyer une vidéo.</small>

            </form>

        </div>
{/if}
