<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/*
    some translations have yet to be taken from or improved by the use of:
    <path>\World of Warcraft\Data\frFR\patch-frFR-3.MPQ\Interface\FrameXML\GlobalStrings.lua
    like: ITEM_MOD_*, POWER_TYPE_*, ITEM_BIND_*, PVP_RANK_*
*/

$lang = array(
    // page variables
    'timeUnits' => array(
        'sg'            => ["année",  "mois", "semaine",  "jour",  "heure",  "minute",  "seconde",  "milliseconde"],
        'pl'            => ["années", "mois", "semaines", "jours", "heures", "minutes", "secondes", "millisecondes"],
        'ab'            => ["an",     "mo",   "sem",      "jour",  "h",      "min",     "s",        "ms"]
    ),
    'main' => array(
        'name'          => "nom",
        'link'          => "Lien",
        'signIn'        => "Se connecter / S'inscrire",
        'jsError'       => "S'il vous plait, assurez vous d'avoir le javascript autorisé.",
        'language'      => "Langue",
        'feedback'      => "Feedback",
        'numSQL'        => "Nombre de requêtes SQL",
        'timeSQL'       => "Temps d'exécution des requêtes SQL",
        'noJScript'     => "<b>Ce site requiert JavaScript pour fonctionner.</b><br />Veuillez <a href=\"https://www.google.com/support/adsense/bin/answer.py?answer=12654\" target=\"_blank\">activer JavaScript</a> dans votre navigateur.",
        'userProfiles'  => "Vos personnages",      // translate.google :x
        'pageNotFound'  => "Ce %s n'existe pas.",
        'gender'        => "Genre",
        'sex'           => [null, "Homme", "Femme"],
        'players'       => "Joueurs",
        'quickFacts'    => "En bref",
        'screenshots'   => "Captures d'écran",
        'videos'        => "Vidéos",
        'side'          => "Coté",
        'related'       => "Informations connexes",
        'contribute'    => "Contribuer",
        // 'replyingTo'    => "En réponse au commentaire de",
        'submit'        => "Soumettre",
        'cancel'        => "Annuler",
        'rewards'       => "Récompenses",
        'gains'         => "Gains",
        'login'         => "[Login]",
        'forum'         => "Forum",
        'n_a'           => "n/d",
        'siteRep'       => "Réputation",
        'aboutUs'       => "À propos de Aowow",
        'and'           => " et ",
        'or'            => " ou ",
        'back'          => "Redro",
        'reputationTip' => "Points de réputation",
        'byUserTimeAgo' => 'Par <a href="'.HOST_URL.'/?user=%s">%1$s</a> il y a %s',

        // filter
        'extSearch'     => "Recherche avancée",
        'addFilter'     => "Ajouter un autre filtre",
        'match'         => "Critère",
        'allFilter'     => "Tous les filtres",
        'oneFilter'     => "Au moins un",
        'applyFilter'   => "Appliquer le filtre",
        'resetForm'     => "Rétablir",
        'refineSearch'  => "Astuce : Affinez votre recherche en utilisant une <a href=\"javascript:;\" id=\"fi_subcat\">sous-catégorie</a>.",
        'clear'         => "effacer",
        'exactMatch'    => "Concordance exacte",
        '_reqLevel'     => "Niveau requis",

        // infobox
        'unavailable'   => "Non disponible aux joueurs",
        'disabled'      => "Désactivé",
        'disabledHint'  => "Ne peux pas être atteint ou complété",
        'serverside'    => "Côté serveur",
        'serversideHint'=> "Ces informations ne sont pas contenues dans le client et ont été obtenues via sniff ou ont été devinées.",

        // red buttons
        'links'         => "Liens",
        'compare'       => "Comparer",
        'view3D'        => "Voir en 3D",
        'findUpgrades'  => "Trouver des améliorations...",

        // misc Tools
        'errPageTitle'  => "Page non trouvée",
        'nfPageTitle'   => "Erreur",
        'subscribe'     => "S'abonner",
        'mostComments'  => ["Hier", "Derniers %d jours"],
        'utilities'     => array(
            "Derniers ajouts",                      "Derniers articles",                    "Derniers commentaires",                "Dernières captures d'écran",           null,
            "Commentaire sans note",                11 => "Derniers vidéos",                12 => "Le plus de commentaires",        13 => "Captures d'écrans manquantes"
        ),

        // article & infobox
        'englishOnly'   => "Cette page n'est disponible qu'en <b>anglais</b> pour le moment.",

        // calculators
        'preset'        => "Prédéterminée",
        'addWeight'     => "Ajouter un autre facteur",
        'createWS'      => "Créer une échelle de valeurs",
        'jcGemsOnly'    => "Inclure les gemmes de <span%s>joaillier</span>",
        'cappedHint'    => 'Conseil: <a href="javascript:;" onclick="fi_presetDetails();">Enlever</a> un facteur pour les statistiques au maximum tel que le score de touche.',
        'groupBy'       => "Groupé par",
        'gb'            => array(
            ["Aucun", "none"],         ["Emplacement", "slot"],       ["Niveau", "level"],     ["Source", "source"]
        ),
        'compareTool'   => "Outil de comparaison d'objets",
        'talentCalc'    => "Calculateur de Talents",
        'petCalc'       => "Calculateur de familiers",
        'chooseClass'   => "Choisissez une classe",
        'chooseFamily'  => "Choisissez un familier",

        // profiler
        'realm'         => "Royaume",
        'region'        => "Région",
        'viewCharacter' => "Voir Personnage",
        '_cpHead'       => "Profiler de Personnage",
        '_cpHint'       => "Le <b>Profiler de Personnage</b> vous permets de modifier votre personnage, trouver des améliorations d'équipement, vérifier votre score d'équipement et plus!",
        '_cpHelp'       => "Pour débuter, suivez simplement les étapes ci-dessous. Si vous voulez plus d'information, lisez notre <a href=\"?help=profiler\">page d'aide</a> détaillée.",
        '_cpFooter'     => "Si vous voulez une recherche plus raffinée, essayez nos options de <a href=\"?profiles\">recherche avancée</a>. Vous pouvez aussi créer un <a href=\"?profile&amp;new\">nouveau profile personnalisé</a>.",

        // help
        'help'          => "Aide",
        'helpTopics'    => array(
            "Le guide du commentaire",              "Visionneuse 3D",                       "Captures d'écran : Trucs et astuces",  "Échelles de valeurs",
            "Calculateur de talents",               "Comparaison d'objets",                 "Profiler",                             "Markup Guide"
        ),

        // search
        'search'        => "Recherche",
        'searchButton'  => "Rechercher",
        'foundResult'   => "Résultats de recherche pour",
        'noResult'      => "Aucun résultat pour malordawsne",
        'tryAgain'      => "Veuillez essayer d'autres mots ou vérifiez l'orthographe des termes de recherche.",
        'ignoredTerms'  => "Les mots suivants ont été ignorés dans votre recherches : %s",

        // formating
        'colon'         => ' : ',
        'dateFmtShort'  => "Y-m-d",
        'dateFmtLong'   => "Y-m-d à H:i",

        // error
        'intError'      => "[An internal error occured.]",
        'intError2'     => "[An internal error occured. (%s)]",
        'genericError'  => "Une erreur est survenue; Actualisez la page et essayez à nouveau. Si l'erreur persiste, envoyez un email à <a href='#contact'>feedback</a>", # LANG.genericerror
        'bannedRating'  => "Vous avez été banni du score des commentaires.", # LANG.tooltip_banned_rating
        'tooManyVotes'  => "Vous avez voté trop souvent aujourd'hui! Revenez demain.", # LANG.tooltip_too_many_votes
    ),
    'screenshot' => array(
        'submission'    => "Envoi d'une capture d'écran",
        'selectAll'     => "Sélectionner tout",
        'cropHint'      => "Vous pouvez recadrer votre capture d'écran.",
        'displayOn'     => "[Displayed on:[br]%s - [%s=%d]]",
        'caption'       => "[Caption]",
        'charLimit'     => "Optionnel, jusqu'à 200 caractères",
        'thanks'        => array(
            'contrib' => "Merci beaucoup de votre contribution!",
            'goBack'  => '<a href="?%s=%d">ici</a> pour retourner à la page d\'où vous venez.',
            'note'    => "Note : Votre capture d'écran devra être approuvée avant d'apparaître sur le site. Cela peut prendre jusqu'à 72 heures."
        ),
        'error'         => array(
            'unkFormat'   => "Format d'image inconnu.",
            'tooSmall'    => "Votre capture est bien trop petite. (&lt; ".CFG_SCREENSHOT_MIN_SIZE."x".CFG_SCREENSHOT_MIN_SIZE.").",
            'selectSS'    => "Veuillez sélectionner la capture d'écran à envoyer.",
            'notAllowed'  => "Vous n'êtes pas autorisés à exporter des captures d'écran.",
        )
    ),
    'game' => array(
        'achievement'   => "haut fait",
        'achievements'  => "Hauts faits",
        'class'         => "classe",
        'classes'       => "Classes",
        'currency'      => "monnaies",
        'currencies'    => "Monnaies",
        'difficulty'    => "Difficulté",
        'dispelType'    => "Type de dissipation",
        'duration'      => "Durée",
        'emote'         => "emote",
        'emotes'        => "Emotes",
        'enchantment'   => "enchantement",
        'enchantments'  => "Enchantements",
        'object'        => "entité",
        'objects'       => "Entités",
        'glyphType'     => "Type de glyphe",
        'race'          => "race",
        'races'         => "Races",
        'title'         => "titre",
        'titles'        => "Titres",
        'eventShort'    => "Évènement",
        'event'         => "Évènement mondial",
        'events'        => "Évènements mondiaux",
        'faction'       => "faction",
        'factions'      => "Factions",
        'cooldown'      => "%s de recharge",
        'item'          => "objet",
        'items'         => "Objets",
        'itemset'       => "ensemble d'objets",
        'itemsets'      => "Ensembles d'objets",
        'mechanic'      => "Mécanique",
        'mechAbbr'      => "Mécan.",
        'meetingStone'  => "Pierre de rencontre",
        'npc'           => "PNJ",
        'npcs'          => "PNJs",
        'pet'           => "Familier",
        'pets'          => "Familiers de chasseur",
        'profile'       => "",
        'profiles'      => "Profils",
        'quest'         => "quête",
        'quests'        => "Quêtes",
        'requires'      => "%s requis",
        'requires2'     => "Requiert",
        'reqLevel'      => "Niveau %s requis",
        'reqLevelHlm'   => "Requiert Niveau %s",
        'reqSkillLevel' => "Niveau de compétence requis",
        'level'         => "Niveau",
        'school'        => "École",
        'skill'         => "compétence",
        'skills'        => "Compétences",
        'spell'         => "sort",
        'spells'        => "Sorts",
        'type'          => "Type",
        'valueDelim'    => " - ",
        'zone'          => "zone",
        'zones'         => "Zones",

        'pvp'           => "JcJ",
        'honorPoints'   => "Points d'honneur",
        'arenaPoints'   => "Points d'arène",
        'heroClass'     => "Classe de héros",
        'resource'      => "Ressource",
        'resources'     => "Ressources",
        'role'          => "Role",
        'roles'         => "Roles",
        'specs'         => "Specialisations",
        '_roles'        => ["Soigneur", "DPS mêlée", "DPS à distance", "Tank"],

        'phases'        => "Phases",
        'mode'          => "Mode",
        'modes'         => [-1 => "Tout", "Standard / Normal 10", "Héroïque / Normal 25", "10 héroïque", "25 héroïque"],
        'expansions'    => ["Classique", "The Burning Crusade", "Wrath of the Lich King"],
        'stats'         => ["Force", "Agilité", "Endurance", "Intelligence", "Esprit"],
        'sources'       => array(
            "Inconnu",                      "Fabriqué",                     "Butin",                        "JcJ",                          "Quête",                        "Vendeur",
            "Maître",                       "Découverte",                   "Échange d'un code",            "Talent",                       "Débutant",                     "Événement",
            "Haut fait",                    null,                           "Marché Noir",                  "Désenchanté",                  "Pêché",                        "Cueilli",
            "Moulu",                        "Miné",                         "Prospecté",                    "Subtilisé (pickpocket)",       "Ferraillé",                    "Dépecé",
            "Boutique en jeu"
        ),
        'languages'     => array(
             1 => "Orc",                     2 => "Darnassien",              3 => "Taurahe",                 6 => "Nain",                    7 => "Commun",                  8 => "Démoniaque",
             9 => "Titan",                  10 => "Thalassien",             11 => "Draconique",             12 => "Kalimag",                13 => "Gnome",                  14 => "Troll",
            33 => "Bas-parler",             35 => "Draeneï",                36 => "Zombie",                 37 => "Binaire gnome",          38 => "Binaire gobelin"
        ),
        'gl'            => [null, "Majeur", "Mineur"],
        'si'            => [1 => "Alliance", -1 => "Alliance seulement", 2 => "Horde", -2 => "Horde seulement", 3 => "Les deux"],
        'resistances'   => [null, 'Résistance au Sacré', 'Résistance au Feu', 'Résistance à la Nature', 'Résistance au Givre', 'Résistance à l\'Ombre', 'Résistance aux Arcanes'],
        'dt'            => [null, "Magie", "Malédiction", "Maladie", "Poison", "Camouflage", "Invisibilité", null, null, "Enrager"],
        'sc'            => ["Physique", "Sacré", "Feu", "Nature", "Givre", "Ombre", "Arcane"],
        'cl'            => [null, "Guerrier", "Paladin", "Chasseur", "Voleur", "Prêtre", "DeathChevalier de la mort", "Chaman", "Mage", "Démoniste", null, "Druide"],
        'ra'            => [-2 => "Horde", -1 => "Alliance", "Les deux", "Humain", "Orc", "Nain", "Elfe de la nuit", "Mort-vivant", "Tauren", "Gnome", "Troll", null, "Elfe de sang", "Draeneï"],
        'rep'           => ["Détesté", "Hostile", "Inamical", "Neutre", "Amical", "Honoré", "Révéré", "Exalté"],
        'st'            => array(
            "Défaut",                       "Forme de félin",               "Arbre de vie",                 "Forme de voyage",              "Forme aquatique",              "Forme d'ours",
            null,                           null,                           "Forme d'ours redoutable",      null,                           null,                           null,
            null,                           "Danse de l'ombre",             null,                           null,                           "Loup fantôme",                 "Posture de combat",
            "Posture défensive",            "Posture berserker",            null,                           null,                           "Métamorphe",                   null,
            null,                           null,                           null,                           "Forme de vol rapide",          "Forme d'Ombre",                "Forme de vol",
            "Camouflage",                   "Forme de sélénien",            "Esprit de rédemption"
        ),
        'me'            => array(
            null,                           "Charmé",                       "Désorienté",                   "Désarmé",                      "Distrait",                     "En fuite",
            "Maladroit",                    "Immobilisé",                   "Pacifié",                      "Réduit au silence",            "Endormi",                      "Pris au piège",
            "Étourdi",                      "Gelé",                         "Stupéfié",                     "Sanguinolent",                 "Soins",                        "Métamorphosé",
            "Banni",                        "Protégé",                      "Entravé",                      "Monté",                        "Séduit",                       "Repoussé",
            "Horrifié",                     "Invulnérable",                 "Interrompu",                   "Hébété",                       "Découverte",                   "Invulnérable",
            "Assommé",                      "Enragé"
        ),
        'ct'            => array(
            "Non classés",                  "Bête",                         "Draconien",                    "Démon",                        "Élémentaire",                  "Géant",
            "Mort-vivant",                  "Humanoïde",                    "Bestiole",                     "Mécanique",                    "Non spécifié",                 "Totem",
            "Familier pacifique",           "Nuage de gaz"
        ),
        'fa'            => array(
             1 => "Loup",                    2 => "Félin",                   3 => "Araignée",                4 => "Ours",                    5 => "Sanglier",                6 => "Crocilisque",
             7 => "Charognard",              8 => "Crabe",                   9 => "Gorille",                11 => "Raptor",                 12 => "Haut-trotteur",          20 => "Scorpide",
            21 => "Tortue",                 24 => "Chauve-souris",          25 => "Hyène",                  26 => "Oiseau de proie",        27 => "Serpent des vents",      30 => "Faucon-dragon",
            31 => "Ravageur",               32 => "Traqueur dim.",          33 => "Sporoptère",             34 => "Raie du Néant",          35 => "Serpent",                37 => "Phalène",
            38 => "Chimère",                39 => "Diablosaure",            41 => "Silithide",              42 => "Ver",                    43 => "Rhinocéros",             44 => "Guêpe",
            45 => "Chien du Magma",         46 => "Esprit de bête"
        ),
        'pvpRank'       => array(
            null,                                                           "Private / Scout",                                              "Corporal / Grunt",
            "Sergeant / Sergeant",                                          "Master Sergeant / Senior Sergeant",                            "Sergeant Major / First Sergeant",
            "Knight / Stone Guard",                                         "Knight-Lieutenant / Blood Guard",                              "Knight-Captain / Legionnare",
            "Knight-Champion / Centurion",                                  "Lieutenant Commander / Champion",                              "Commander / Lieutenant General",
            "Marshal / General",                                            "Field Marshal / Warlord",                                      "Grand Marshal / High Warlord"
        ),
    ),
    'account' => array(
        'title'         => "Compte Aowow",
        'email'         => "Courriel",
        'continue'      => "Poursuivre",
        'groups'        => array(
            -1 => "None",                   "Testeur",                      "Administrateur",               "Éditeur",                      "Modérateur",                   "Bureaucrate",
            "Développeur",                  "VIP",                          "Bloggeur",                     "Premium",                      "Traducteur",                   "Agent de ventes",
            "Gestionnaire de capture d'écran","Gestionnaire de vidéos"      "Partenaire API",               "En attente"
        ),
        // signIn
        'doSignIn'      => "Connexion à votre compte Aowow",
        'signIn'        => "Connexion",
        'user'          => "Nom d'utilisateur",
        'pass'          => "Mot de passe",
        'rememberMe'    => "Rester connecté",
        'forgot'        => "Oublié",
        'forgotUser'    => "Nom d'utilisateur",
        'forgotPass'    => "Mot de passe",
        'accCreate'     => 'Vous n\'avez pas encore de compte ? <a href="/account=signup">Créez-en un maintenant !</a>',

        // recovery
        'recoverUser'   => "Demande de nom d'utilisateur",
        'recoverPass'   => "Changement de mot de passe : Étape %s de 2",
        'newPass'       => "Nouveau mot de passe",

        // creation
        'register'      => "Enregistrement : Étape %s de 2",
        'passConfirm'   => "Confirmez",

        // dashboard
        'ipAddress'     => "Addresse IP",
        'lastIP'        => "Dernière IP utilisée",
        'myAccount'     => "Mon compte",
        'editAccount'   => "Utilisez les formulaires ci-dessous pour mettre à jour vos informations.",
        'viewPubDesc'   => 'Voyez vos informations publiques dans votre <a href="?user=%s">Profile  Page</a>',

        // bans
        'accBanned'     => "Ce compte a été fermé.",
        'bannedBy'      => "Banni par",
        'ends'          => "Termine le",
        'permanent'     => "Ce bannissement est permanent",
        'reason'        => "Raison",
        'noReason'      => "Aucune raison donnée.",

        // form-text
        'emailInvalid'  => "Cette adresse courriel est invalide.", // message_emailnotvalid
        'emailNotFound' => "L'address email que vous avez entrée n'est pas associée à un compte.<br><br>Si vous avez oublié l'address email avec laquelle vous avez enregistré votre compte".CFG_CONTACT_EMAIL." pour obtenir de l'aide.",
        'createAccSent' => "Un email a été envoyé à <b>%s</b>. Suivez les instructions pour créer votre compte.",
        'recovUserSent' => "Un email a été envoyé à <b>%s</b>. Suivez les instructions pour récupérer votre nom d'utilisateur.",
        'recovPassSent' => "Un email a été envoyé à <b>%s</b>. Suivez les instructions pour réinitialiser votre mot de passe.",
        'accActivated'  => 'Votre compte a été activé.<br>Vous pouvez maintenant <a href="?account=signin&token=%s">vous connecter</a>',
        'userNotFound'  => "Le nom d'utilisateur que vous avez saisi n'éxiste pas.",
        'wrongPass'     => "Ce mot de passe est invalide.",
        // 'accInactive'   => "Ce compte n'a pas encore été activé.",
        'loginExceeded' => "Le nombre maximum de connections depuis cette IP a été dépassé. Essayez de nouevau dans %s.",
        'signupExceeded'=> "Le nombre maximum d'inscriptions depuis cette IP a été dépassé. Essayez de nouveau dans %s.",
        'errNameLength' => "Votre nom d'utilisateur doit faire au moins 4 caractères de long.", // message_usernamemin
        'errNameChars'  => "Votre nom d'utilisateur doit contenir seulement des lettres et des chiffres.", // message_usernamenotvalid
        'errPassLength' => "Votre mot de passe doit faire au moins 6 caractères de long.", // message_passwordmin
        'passMismatch'  => "Les mots de passe que vous avez saisis ne correspondent pas.",
        'nameInUse'     => "Ce nom d'utilisateur est déjà utilisé.",
        'mailInUse'     => "Cette addresse email est déjà liée à un compte.",
        'isRecovering'  => "Ce compte est déjà en train d'être récupéré. Suivez les instruction dans l'email reçu ou attendez %s pour que le token expire.",
        'passCheckFail' => "Les mots de passe ne correspondent pas.", // message_passwordsdonotmatch
        'newPassDiff'   => "Votre nouveau mot de passe doit être différent de l'ancien." // message_newpassdifferent
    ),
    'user' => array(
        'notFound'      => "Utilisateur \"%s\" non trouvé !",
        'removed'       => "(Supprimé)",
        'joinDate'      => "Inscription",
        'lastLogin'     => "Dernière visite",
        'userGroups'    => "Role",
        'consecVisits'  => "Visites consécutives",
        'publicDesc'    => "Description publique",
        'profileTitle'  => "Profil de %s",
        'contributions' => "Contributions",
        'uploads'       => "Envois de données",
        'comments'      => "Commentaires",
        'screenshots'   => "Captures d'écran",
        'videos'        => "Vidéos",
        'posts'         => "Messages sur le forum"
    ),
    'mail' => array(
        'tokenExpires'  => "This token expires in %s.",
        'accConfirm'    => ["Activation de compte",             "Bienvenue sur ".CFG_NAME_SHORT."!\r\n\r\nCliquez sur le lien ci-dessous pour activer votre compte.\r\n\r\n".HOST_URL."?account=signup&token=%s\r\n\r\nSi vous n'avez pas demandé cet email, ignorez le."],
        'recoverUser'   => ["Récupération d'utilisateur",       "Suivez ce lien pour vous connecter.\r\n\r\n".HOST_URL."?account=signin&token=%s\r\n\r\nSi vous n'avez pas demandé cet email, ignorez le."],
        'resetPass'     => ["Réinitialisation du mot de passe", "Suivez ce lien pour réinitialiser votre mot de passe.\r\n\r\n".HOST_URL."?account=forgotpassword&token=%s\r\n\r\nSi vous n'avez pas fait de demande de réinitialisation, ignorez cet email."]
    ),
    'emote' => array(
        'notFound'      => "[This Emote doesn't exist.]",
        'self'          => "Vers vous-même",
        'target'        => "Vers les autres avec une cible",
        'noTarget'      => "Vers les autres sans cible",
        'isAnimated'    => "Utilise une animation",
        'aliases'       => "Alias",
        'noText'        => "Cette émote n'a pas de texte.",
    ),
    'enchantment' => array(
        'details'       => "En détail",
        'activation'    => "Activation",
        'notFound'      => "Cet enchantement n'existe pas.",
        'types'         => array(
            1 => "Sort proc",               3 => "Sort équipé",             7 => "Sort utilisé",            8 => "Châsse prismatique",
            5 => "Statistiques",            2 => "Dégâts d'arme",           6 => "DPS",                     4 => "Défense"
        )
    ),
    'gameObject' => array(
        'notFound'      => "Cette entité n'existe pas.",
        'cat'           => [0 => "Autre", 9 => "Livres", 3 => "Conteneurs", -5 => "Coffres", 25 => "Bancs de poissons", -3 => "Herbes", -4 => "Filons de minerai", -2 => "Quêtes", -6 => "Outils"],
        'type'          => [              9 => "Livre",  3 => "Conteneur",  -5 => "Coffre",  25 => "",                  -3 => "Herbe",  -4 => "Filon de minerai",  -2 => "Quête",  -6 => ""],
        'unkPosition'   => "L'emplacement de cette entité est inconnu.",
        'npcLootPH'     => 'Le <b>%s</b> contient les récompenses du combat contre <a href="?npc=%d">%s</a>. Il apparaît après sa mort.',
        'key'           => "Clé",
        'focus'         => "Focus de sort",
        'focusDesc'     => "Les sorts nécessitant ce focus peuvent être lancés près de cet entité.",
        'trap'          => "Piège",
        'triggeredBy'   => "Déclenché par",
        'capturePoint'  => "Point de capture",
        'foundIn'       => "Cette entité se trouve dans",
        'restock'       => "Se remplit toutes les %s.]"
    ),
    'npc' => array(
        'notFound'      => "Ce PNJ n'existe pas.",
        'classification'=> "Classification",
        'petFamily'     => "Familier",
        'react'         => "Réaction",
        'worth'         => "Vaut",
        'unkPosition'   => "L'emplacement de ce PNJ est inconnu.",
        'difficultyPH'  => "Ce PNJ est un espace réservé pour un autre mode de difficulté.",
        'seat'          => "Siège",
        'accessory'     => "Passager",
        'accessoryFor'  => "Ce PNJ est un passager pour un véhicule.",
        'quotes'        => "Citations",
        'gainsDesc'     => "Après avoir tué ce PNJ vous allez obtenir",
        'repWith'       => "points de réputation avec",
        'stopsAt'       => "arrête à %s",
        'vehicle'       => "Véhicule",
        'stats'         => "Statistiques",
        'melee'         => "de mêlée",
        'ranged'        => "à distance",
        'armor'         => "Armure",
        'foundIn'       => "Ce PNJ se trouve dans",
        'tameable'      => "Domptable (%s)",
        'waypoint'      => "Point de route",
        'wait'          => "Période d'attente",
        'respawnIn'     => "Rentrée en",
        'rank'          => [0 => "Standard", 1 => "Élite", 4 => "Rare", 2 => "Élite rare", 3 =>"Boss"],
        'textRanges'    => [null, "[sent to area]", "[sent to zone]", "[sent to map]", "[sent to world]"],
        'textTypes'     => [null, "crie", "dit", "chuchote"],
        'modes'         => array(
            1 => ["Normal", "Héroïque"],
            2 => ["10-joueurs Normal", "25-joueurs Normal", "10-joueurs Héroïque", "25-joueurs Héroïque"]
        ),
        'cat'           => array(
            "Non classés",              "Bêtes",                    "Draconien",                "Démons",                   "Élémentaires",             "Géants",                   "Mort-vivant",              "Humanoïdes",
            "Bestioles",                "Mécaniques",               "Non spécifié",             "Totems",                   "Familier pacifique",       "Nuages de gaz"
        )
    ),
    'event' => array(
        'notFound'      => "Cet évènement mondial n'existe pas.",
        'start'         => "Début",
        'end'           => "Fin",
        'interval'      => "Intervalle",
        'inProgress'    => "L'évènement est présentement en cours",
        'category'      => ["Non classés", "Vacances", "Récurrent", "Joueur ctr. Joueur"]
    ),
    'achievement' => array(
        'notFound'      => "Ce haut fait n'existe pas.",
        'criteria'      => "Critères",
        'points'        => "Points",
        'series'        => "Série",
        'outOf'         => "sur",
        'criteriaType'  => "Criterium Type-Id : ",
        'itemReward'    => "Vous recevrez",
        'titleReward'   => "Vous devriez recevoir le titre \"<a href=\"?title=%d\">%s</a>\"",
        'slain'         => "tué",
        'reqNumCrt'     => "Nécessite",
        'rfAvailable'   => "Disponibles sur les royaumes : ",
        '_transfer'     => 'Cet haut fait sera converti en <a href="?achievement=%d" class="q%d icontiny tinyspecial" style="background-image: url('.STATIC_URL.'/images/wow/icons/tiny/%s.gif)">%s</a> si vous transférez en <span class="icon-%s">%s</span>.',
    ),
    'chrClass' => array(
        'notFound'      => "Cette classe n'existe pas."
    ),
    'race' => array(
        'notFound'      => "Cette race n'existe pas.",
        'racialLeader'  => "Leader racial",
        'startZone'     => "Zone initiales",
    ),
    'maps' => array(
        'maps'          => "Cartes",
        'linkToThisMap' => "Lien vers cette carte",
        'clear'         => "Effacer",
        'EasternKingdoms' => "Royaumes de l'Est",
        'Kalimdor'      => "Kalimdor",
        'Outland'       => "Outreterre",
        'Northrend'     => "Norfendre",
        'Instances'     => "Instances",
        'Dungeons'      => "Donjons",
        'Raids'         => "Raids",
        'More'          => "Plus",
        'Battlegrounds' => "Champs de bataille",
        'Miscellaneous' => "Divers",
        'Azeroth'       => "Azeroth",
        'CosmicMap'     => "Carte cosmique",
    ),
    'zone' => array(
        'notFound'      => "Cette zone n'existe pas.",
        'attunement'    => ["Accès", "Accès Héroïque"],
        'key'           => ["Clef", "Clef Héroïque"],
        'location'      => "Localisation",
        'raidFaction'   => "Faction de raid",
        'boss'          => "Boss final",
        'reqLevels'     => "Niveaux requis : [tooltip=instancereqlevel_tip]%d[/tooltip], [tooltip=lfgreqlevel_tip]%d[/tooltip]",
        'zonePartOf'    => "Cette zone fait partie de la zone [zone=%d].",
        'autoRez'       => "Résurrection automatique",
        'city'          => "Город",
        'territory'     => "Territoire",
        'instanceType'  => "Type d'instance",
        'hcAvailable'   => "Mode héroïque disponible&nbsp;(%d)",
        'numPlayers'    => "Nombre de joueurs",
        'noMap'         => "Il n'y a aucune carte disponible pour cette zone.",
        'instanceTypes' => ["Zone",     "Transit", "Donjon",   "Raid",       "Champ de bataille", "Donjon",    "Arène", "Raid", "Raid"],
        'territories'   => ["Alliance", "Horde",   "Contesté", "Sanctuaire", "JcJ",               "JcJ Global"],
        'cat'           => array(
            "Royaumes de l'est",        "Kalimdor",                 "Donjons",                  "Raids",                    "Inutilisées",              null,
            "Champs de bataille",       null,                       "Outreterre",               "Arènes",                   "Norfendre"
        )
    ),
    'quest' => array(
        'notFound'      => "Cette quête n'existe pas.",
        '_transfer'     => 'Cette quête sera converti en <a href="?quest=%d" class="q1">%s</a> si vous transférez en <span class="icon-%s">%s</span>.',
        'questLevel'    => "Niveau %s",
        'requirements'  => "Conditions",
        'reqMoney'      => "Argent requis",
        'money'         => "Argent",
        'additionalReq' => "Conditions additionnelles requises pour obtenir cette quête",
        'reqRepWith'    => 'Votre reputation avec <a href="?faction=%d">%s</a> doît être %s %s',
        'reqRepMin'     => "d'au moins",
        'reqRepMax'     => "moins que",
        'progress'      => "Progrès",
        'provided'      => "Fourni",
        'providedItem'  => "Objet fourni",
        'completion'    => "Achèvement",
        'description'   => "Description",
        'playerSlain'   => "Joueurs tués",
        'profession'    => "Métier",
        'timer'         => "Temps",
        'loremaster'    => "Maitre des traditions",
        'suggestedPl'   => "Joueurs suggérés",
        'keepsPvpFlag'  => "Vous garde en mode JvJ",
        'daily'         => "Journalière",
        'weekly'        => "Chaque semaine",
        'monthly'       => "Mensuel",
        'sharable'      => "Partageable",
        'notSharable'   => "Non partageable",
        'repeatable'    => "Répétable",
        'reqQ'          => "Requiert",
        'reqQDesc'      => "Pour avoir cette quête, vous devez avoir completé ces quêtes",
        'reqOneQ'       => "Requiert",
        'reqOneQDesc'   => "Pour avoir accès à cette quête vous devez accomplir une des quêtes suivantes",
        'opensQ'        => "Donne accès aux quêtes",
        'opensQDesc'    => "Terminer cette quête est requis pour commencer ces quetês",
        'closesQ'       => "Empêche l'accès aux quêtes",
        'closesQDesc'   => "Terminer cette quête ferme l'accès aux quêtes",
        'enablesQ'      => "Autorise",
        'enablesQDesc'  => "Quand cette quête est active, vous pouvez obtenir cette quete",
        'enabledByQ'    => "Autorisée par",
        'enabledByQDesc'=> "Vous pouvez faire cette quête seulement quand cette quête est active",
        'gainsDesc'     => "Lors de l'achèvement de cette quête vous gagnerez",
        'theTitle'      => '"%s"',                          // empty on purpose!
        'mailDelivery'  => "Vous recevrez cette lettre%s%s",
        'mailBy'        => ' de <a href="?npc=%d">%s</a>',
        'mailIn'        => " après %s",
        'unavailable'   => "Cette quête est marquée comme obsolète et ne peut être obtenue ou accomplie.",
        'experience'    => "points d'expérience",
        'expConvert'    => "(ou %s si completé au niveau %d)",
        'expConvert2'   => "%s si completé au niveau %d",
        'chooseItems'   => "Vous pourrez choisir une de ces récompenses",
        'receiveItems'  => "Vous recevrez",
        'receiveAlso'   => "Vous recevrez également",
        'spellCast'     => "Vous allez être la cible du sort suivant",
        'spellLearn'    => "Vous apprendrez",
        'bonusTalents'  => "points de talent",
        'spellDisplayed'=> ' (<a href="?spell=%d">%s</a> affichés)',
        'attachment'    => "[Attachment]",
        'questInfo'     => array(
             0 => "Standard",            1 => "Groupe",             21 => "Vie",                41 => "JcJ",                62 => "Raid",               81 => "Donjon",             82 => "Évènement mondial",
            83 => "Légendaire",         84 => "Escorte",            85 => "Héroïque",           88 => "Raid (10)",          89 => "Raid (25)"
        ),
        'cat'           => array(
            0 => array( "Royaumes de l'est",
                  10 => "Bois de la Pénombre",          3430 => "Bois des Chants éternels",       85 => "Clairières de Tirisfal",        267 => "Contreforts de Hautebrande",    279 => "Cratère de Dalaran",
                   1 => "Dun Morogh",                     41 => "Défilé de Deuillevent",        1537 => "Forgefer",                       12 => "Forêt d'Elwynn",                130 => "Forêt des Pins argentés",
                1497 => "Fossoyeuse",                     51 => "Gorge des Vents brûlants",       45 => "Hautes-terres d'Arathi",       1519 => "Hurlevent",                      44 => "Les Carmines",
                  47 => "Les Hinterlands",                11 => "Les Paluns",                   3433 => "Les Terres fantômes",            38 => "Loch Modan",                   3487 => "Lune-d'argent",
                 139 => "Maleterres de l'est",            28 => "Maleterres de l'ouest",        4298 => "Maleterres : l'enclave Écarlate", 8 => "Marais des Chagrins",            40 => "Marche de l'Ouest",
                  25 => "Mont Rochenoire",                36 => "Montagnes d'Alterac",            46 => "Steppes ardentes",                4 => "Terres foudroyées",               3 => "Terres ingrates",
                2257 => "Tram des profondeurs",           33 => "Vallée de Strangleronce",      4080 => "Île de Quel'Danas"
            ),
            1 => array( "Kalimdor",
                  16 => "Azshara",                       618 => "Berceau-de-l'Hiver",            490 => "Cratère d'Un'Goro",            1657 => "Darnassus",                      14 => "Durotar",
                 405 => "Désolace",                      357 => "Féralas",                       361 => "Gangrebois",                   3557 => "L'Exodar",                     1638 => "Les Pitons du Tonnerre",
                 406 => "Les Serres-Rocheuses",           17 => "Les Tarides",                    15 => "Marécage d'Âprefange",          400 => "Mille pointes",                 215 => "Mulgore",
                1637 => "Orgrimmar",                     331 => "Orneval",                       493 => "Reflet-de-Lune",               1216 => "Repaire des Grumegueules",     1377 => "Silithus",
                 148 => "Sombrivage",                    440 => "Tanaris",                       141 => "Teldrassil",                   3524 => "Île de Brume-azur",            3525 => "Île de Brume-sang"
            ),
            8 => array( "Outreterre",
                3519 => "Forêt de Terokkar",            3522 => "Les Tranchantes",              3521 => "Marécage de Zangar",           3518 => "Nagrand",                      3483 => "Péninsule des Flammes infernales",
                3523 => "Raz-de-Néant",                 3703 => "Shattrath",                    3679 => "Skettis",                      3520 => "Vallée d'Ombrelune"
            ),
           10 => array( "Norfendre",
                4742 => "Accostage de Hrothgar",        3711 => "Bassin de Sholazar",           4395 => "Dalaran",                        65 => "Désolation des dragons",        495 => "Fjord Hurlant",
                4024 => "Frimarra",                     4197 => "Joug-d'hiver",                  210 => "La Couronne de glace",          394 => "Les Grisonnes",                  67 => "Les pics Foudroyés",
                3537 => "Toundra Boréenne",               66 => "Zul'Drak"
            ),
            6 => array( "Champs de bataille",
                3358 => "Bassin d'Arathi",               -25 => "Champs de bataille",           3277 => "Goulet des Chanteguerres",     3820 => "L'Œil du cyclone",             4384 => "Rivage des Anciens",
                2597 => "Vallée d'Alterac",             4710 => "Île des Conquérants"
            ),
            4 => array( "Classes",
                 -82 => "Chaman",                       -261 => "Chasseur",                     -372 => "Chevalier de la mort",         -263 => "Druide",                        -61 => "Démoniste",
                 -81 => "Guerrier",                     -161 => "Mage",                         -141 => "Paladin",                      -262 => "Prêtre",                       -162 => "Voleur"
            ),
            2 => array( "Donjons",
                4494 => "Ahn'kahet : l'Ancien royaume", 4277 => "Azjol-Nérub",                   718 => "Cavernes des lamentations",    1196 => "Cime d'Utgarde",               2367 => "Contreforts de Hautebrande d'antan",
                3790 => "Cryptes Auchenaï",              209 => "Donjon d'Ombrecroc",            206 => "Donjon d'Utgarde",             4196 => "Donjon de Drak'Tharon",        3845 => "Donjon de la Tempête",
                4813 => "Fosse de Saron",                721 => "Gnomeregan",                   2437 => "Gouffre de Ragefeu",           1941 => "Grottes du temps",             4416 => "Gundrak",
                2557 => "Hache-tripes",                  491 => "Kraal de Tranchebauge",        3848 => "L'Arcatraz",                   4228 => "L'Oculus",                     4100 => "L'Épuration de Stratholme",
                4723 => "L'épreuve du champion",        3716 => "La Basse-tourbière",           3847 => "La Botanica",                  4809 => "La Forge des âmes",            3713 => "La Fournaise du sang",
                 717 => "La Prison",                    3789 => "Labyrinthe des ombres",        3715 => "Le Caveau de la vapeur",       3849 => "Le Méchanar",                  4120 => "Le Nexus",
                2366 => "Le Noir Marécage",             4415 => "Le fort Pourpre",              1581 => "Les Mortemines",               3714 => "Les Salles brisées",           3717 => "Les enclos aux esclaves",
                4272 => "Les salles de Foudre",         4264 => "Les salles de Pierre",         3791 => "Les salles des Sethekk",       2100 => "Maraudon",                      796 => "Monastère écarlate",
                1583 => "Pic Rochenoire",                719 => "Profondeurs de Brassenoire",   1584 => "Profondeurs de Rochenoire",    3562 => "Remparts des Flammes infernales", 3905 => "Réservoir de Glissecroc",
                4820 => "Salles des Reflets",           2057 => "Scholomance",                   722 => "Souilles de Tranchebauge",     2017 => "Stratholme",                   1477 => "Temple englouti",
                4131 => "Terrasse des Magistères",      3792 => "Tombes-mana",                  1337 => "Uldaman",                      1176 => "Zul'Farrak"
            ),
            5 => array( "Métiers",
                -181 => "Alchimiste",                   -371 => "Calligraphie",                 -304 => "Cuisinier",                    -121 => "Forgeron",                      -24 => "Herboristerie",
                -201 => "Ingénieur",                    -373 => "Joaillerie",                   -101 => "Pêcheur",                      -324 => "Secourisme",                   -264 => "Tailleur",
                -182 => "Travailleur du cuir"
            ),
            3 => array( "Raids",
                3428 => "Ahn'Qiraj",                    4603 => "Caveau d'Archavon",            4812 => "Citadelle de la Couronne de glace", 2717 => "Cœur du Magma",           3845 => "Donjon de la Tempête",
                3457 => "Karazhan",                     4722 => "L'épreuve du croisé",          4500 => "L'Œil de l'éternité",          3836 => "Le repaire de Magtheridon",    4493 => "Le sanctum Obsidien",
                4987 => "Le sanctum Rubis",             3456 => "Naxxramas",                    4075 => "Plateau du Puits de soleil",   3923 => "Repaire de Gruul",             2159 => "Repaire d'Onyxia",
                2677 => "Repaire de l'Aile noire",      3429 => "Ruines d'Ahn'Qiraj",           3606 => "Sommet d'Hyjal",               3959 => "Temple noir",                  4273 => "Ulduar",
                3805 => "Zul'Aman",                     1977 => "Zul'Gurub"
            ),
            9 => array( "Évènements mondiaux",
                -370 => "Fête des Brasseurs",          -1002 => "Semaine des enfants",          -364 => "Foire de Sombrelune",           -41 => "Jour des Morts",              -1003 => "Sanssaint",
                -1005 => "Fête des moissons",           -376 => "De l'amour dans l'air",        -366 => "Fête lunaire",                 -369 => "Solstice d'été",              -1006 => "Nouvel an",
                -375 => "Bienfaits du pèlerin",         -374 => "Jardin des nobles",           -1001 => "Voile d'hiver"
            ),
            7 => array( "Divers",
                -365 => "Guerre d'Ahn'Qiraj",          -1010 => "Chercheur de donjons",           -1 => "Épique",                       -344 => "Légendaire",                   -367 => "Réputation",
                -368 => "Invasion du fléau",            -241 => "Tournoi"),
           -2 => "Non classés"
        )
    ),
    'title' => array(
        'notFound'      => "Ce titre n'existe pas.",
        '_transfer'     => 'Ce titre sera converti en <a href="?title=%d" class="q1">%s</a> si vous transférez en <span class="icon-%s">%s</span>.',
        'cat'           => array(
            "Général",      "Joueur ctr. Joueur",    "Réputation",       "Donjons & raids",     "Quêtes",       "Métiers",      "Évènements mondiaux"
        )
    ),
    'skill' => array(
        'notFound'      => "Cette compétence n'existe pas.",
        'cat'           => array(
            -6 => "Compagnons",         -5 => "Montures",           -4 => "Traits raciaux",     5 => "Caractéristiques",    6 => "Compétences d'armes", 7 => "Compétences de classe", 8 => "Armures utilisables",
             9 => "Compétences secondaires", 10 => "Langues",       11 => "Métiers"
        )
    ),
    'currency' => array(
        'notFound'      => "Cette monnaie n'existe pas.",
        'cap'           => "Maximum total",
        'cat'           => array(
            1 => "Divers", 2 => "JcJ", 4 => "Classique", 21 => "Wrath of the Lich King", 22 => "Raid", 23 => "Burning Crusade", 41 => "Test", 3 => "Inutilisées"
        )
    ),
    'pet'      => array(
        'notFound'      => "Cette famille de familiers n'existe pas.",
        'exotic'        => "Exotique",
        'cat'           => ["Férocité", "Tenacité", "Ruse"]
    ),
    'faction' => array(
        'notFound'      => "Cette faction n'existe pas.",
        'spillover'     => "Partage de réputations",
        'spilloverDesc' => "Gagner de la réputation avec cette faction fourni une réputation proportionnelle avec les factions ci-dessous.",
        'maxStanding'   => "Niveau maximum",
        'quartermaster' => "Intendant",
        'customRewRate' => "Taux de récompense personnalisé",
        '_transfer'     => 'La réputation de cette faction sera convertie en <a href="?faction=%d" class="q1">%s</a> si vous transférez vers <span class="icon-%s">%s</span>.]',
        'cat'           => array(
            1118 => ["Classique", 469 => "Alliance", 169 => "Cartel Gentepression", 67 => "Horde", 891 => "Forces de l'Alliance", 892 => "Forces de la Horde"],
            980  => ["The Burning Crusade", 936 => "Shattrath"],
            1097 => ["Wrath of the Lich King", 1052 => "Expédition de la Horde", 1117 => "Bassin de Sholazar", 1037 => "Avant-garde de l'Alliance"],
            0    => "Autre"
        )
    ),
    'itemset' => array(
        'notFound'      => "Cet ensemble d'objets n'existe pas.",
        '_desc'         => "<b>%s</b> est le <b>%s</b>. Il contient %s pièces.",
        '_descTagless'  => "<b>%s</b> est un ensemble d'objet qui contient %s pièces.",
        '_setBonuses'   => "Bonus de l'ensemble",
        '_conveyBonus'  => "Plus d'objets de cet ensemble sont équipés, plus votre personnage aura des bonus de caractéristiques.",
        '_pieces'       => "pièces",
        '_unavailable'  => "Cet objet n'est plus disponible aux joueurs.",
        '_tag'          => "Étiquette",
        'summary'       => "Résumé",
        'notes'         => array(
            null,                                   "Ensemble de donjon 1",                 "Ensemble de donjon 2",                         "Ensemble de raid palier 1",
            "Ensemble de raid palier 2",            "Ensemble de raid palier 3",            "Ensemble JcJ niveau 60 supérieur",             "Ensemble JcJ niveau 60 supérieur (désuet)",
            "Ensemble JcJ niveau 60 épique",        "Ensemble des ruines d'Ahn'Qiraj",      "Ensemble d'Ahn'Qiraj",                         "Ensemble de Zul'Gurub",
            "Ensemble de raid palier 4",            "Ensemble de raid palier 5",            "Ensemble de donjon 3",                         "Ensemble du bassin d'Arathi",
            "Ensemble JcJ niveau 70 supérieur",     "Ensemble d'arène saison 1",            "Ensemble de raid palier 6",                    "Ensemble d'arène saison 2",
            "Ensemble d'arène saison 3",            "Ensemble JcJ niveau 70 supérieur 2",   "Ensemble d'arène saison 4",                    "Ensemble de raid palier 7",
            "Ensemble d'arène saison 5",            "Ensemble de raid palier 8",            "Ensemble d'arène saison 6",                    "Ensemble de raid palier 9",
            "Ensemble d'arène saison 7",            "Ensemble de raid palier 10",           "Set d'Arena de la Saison 8"
        ),
        'types'         => array(
            null,               "Tissu",                "Cuir",                 "Mailles",                  "Plaques",                  "Dague",                    "Anneau",
            "Arme de pugilat",  "Hache à une main",     "Masse à une main",     "Épée à une main",          "Bijou",                    "Amulette"
        )
    ),
    'spell' => array(
        'notFound'      => "Ce sort n'existe pas.",
        '_spellDetails' => "Détails sur le sort",
        '_cost'         => "Coût",
        '_range'        => "Portée",
        '_castTime'     => "Incantation",
        '_cooldown'     => "Recharge",
        '_distUnit'     => "mètres",
        '_forms'        => "Formes",
        '_aura'         => "Aura",
        '_effect'       => "Effet",
        '_none'         => "Aucun",
        '_gcd'          => "GCD",
        '_globCD'       => "Temps d'attente universel",
        '_gcdCategory'  => "Catégorie GCD",
        '_value'        => "Valeur",
        '_radius'       => "Rayon",
        '_interval'     => "Intervalle",
        '_inSlot'       => "dans l'emplacement",
        '_collapseAll'  => "Replier Tout",
        '_expandAll'    => "Déplier Tout",
        '_transfer'     => 'Cet sort sera converti en <a href="?spell=%d" class="q%d icontiny tinyspecial" style="background-image: url('.STATIC_URL.'/images/wow/icons/tiny/%s.gif)">%s</a> si vous transférez en <span class="icon-%s">%s</span>.',
        'discovered'    => "Appris via une découverte",
        'ppm'           => "%s déclenchements par minute",
        'procChance'    => "Chance",
        'starter'       => "Sortilège initiaux",
        'trainingCost'  => "Coût d'entraînement",
        'remaining'     => "%s restantes",
        'untilCanceled' => "jusqu’à annulation",
        'castIn'        => "%s s d'incantation",
        'instantPhys'   => "Incantation immédiate",
        'instantMagic'  => "Instantanée",
        'channeled'     => "Canalisée",
        'range'         => "%s m de portée",
        'meleeRange'    => "Allonge",
        'unlimRange'    => "Portée illimitée",
        'reagents'      => "Composants",
        'tools'         => "Outils",
        'home'          => "%lt;Auberge&gt;",
        'pctCostOf'     => "de la %s de base",
        'costPerSec'    => ", plus %s par seconde",
        'costPerLevel'  => ", plus %s par niveau",
        'stackGroup'    => "[Stack Group]",
        'linkedWith'    => "[Linked with]",
        '_scaling'      => "[Scaling]",
        'scaling'       => array(
            'directSP' => "+%.2f%% de la puissance des sorts directe",        'directAP' => "+%.2f%% de la puissance d'attaque directe",
            'dotSP'    => "+%.2f%% de la puissance des sorts par tick",       'dotAP'    => "+%.2f%% de la puissance d'attaque par tick"
        ),
        'powerRunes'    => ["Givre", "Impie", "Sang", "Mort"],
        'powerTypes'    => array(
            // conventional
              -2 => "vie",                 0 => "mana",                1 => "rage",                2 => "focus",               3 => "énergie",             4 => "Satisfaction",
               5 => "Runes",               6 => "puissance runique",
            // powerDisplay
              -1 => "Munitions",         -41 => "Pyrite",            -61 => "Pression vapeur",  -101 => "Chaleur",          -121 => "Limon",            -141 => "Puissance de sang",
            -142 => "Courroux"
        ),
        'relItems'      => array(
            'base'    => "<small>Montre %s reliés à <b>%s</b></small>",
            'link'    => " ou ",
            'recipes' => "les <a href=\"?items=9.%s\">recettes</a>",
            'crafted' => "les <a href=\"?items&filter=cr=86;crs=%s;crv=0\">objets fabriqués</a>"
        ),
        'cat'           => array(
              7 => "Techniques",
            -13 => "Glyphes",
            -11 => ["Compétences", 8 => "Armure", 10 => "Langues", 6 => "Armes"],
             -4 => "Traits raciaux",
             -2 => "Talents",
             -6 => "Compagnons",
             -5 => "Montures",
             -3 => array(
                "Habilité de familier",     782 => "Goule",             270 => "Générique",             203 => "Araignée",                  213 => "Charognard",            653 => "Chauve-souris",         787 => "Chien du Magma",
                780 => "Chimère",           214 => "Crabe",             212 => "Crocilisque",           781 => "Diablosaure",               788 => "Esprit de bête",        763 => "Faucon-dragon",         209 => "Félin",
                215 => "Gorille",           785 => "Guêpe",             218 => "Haut-trotteur",         654 => "Hyène",                     208 => "Loup",                  655 => "Oiseau de proie",       210 => "Ours",
                775 => "Phalène",           764 => "Raie du Néant",     217 => "Raptor",                767 => "Ravageur",                  786 => "Rhinocéros",            211 => "Sanglier",              236 => "Scorpide",
                768 => "Serpent",           656 => "Serpent des vents", 783 => "Silithide",             765 => "Sporoptère",                251 => "Tortue",                766 => "Traqueur dim.",         784 => "Ver",
                761 => "Gangregarde",       189 => "Chasseur corrompu", 188 => "Diablotin",             205 => "Succube",                   204 => "Marcheur du Vide"
            ),
             -7 => ["Talents de familiers", 411 => "Ruse", 410 => "Férocité", 409 => "Tenacité"],
             11 => array(
                "Métiers",
                171 => "Alchimie",
                164 => ["Forge", 9788 => "Fabricant d'armures", 9787 => "Fabricant d'armes", 17041 => "Maître fabricant de haches", 17040 => "Maître fabricant de marteaux", 17039 => "Maître fabricant d'épées"],
                333 => "Enchantement",
                202 => ["Ingénierie", 20219 => "Ingénieur gnome", 20222 => "Ingénieur goblin"],
                182 => "Herboristerie",
                773 => "Calligraphie",
                755 => "Joaillerie",
                165 => ["Travail du cuir", 10656 => "Travail du cuir d'écailles de dragon", 10658 => "Travail du cuir élémentaire", 10660 => "Travail du cuir tribal"],
                186 => "Minage",
                393 => "Dépeçage",
                197 => ["Couture", 26798 => "Couture d'étoffe lunaire", 26801 => "Couture de tisse-ombre", 26797 => "Couture du feu-sorcier"],
            ),
              9 => ["Compétences secondaires", 185 => "Cuisine", 129 => "Secourisme", 356 => "Pêche", 762 => "Monte"],
             -9 => "Habilité de MJ",
             -8 => "Habilité de PNJ",
              0 => "Non classés"
        ),
        'armorSubClass' => array(
            "Divers",                               "Armures en tissu",                     "Armures en cuir",                      "Armures en mailles",                   "Armures en plaques",
            null,                                   "Boucliers",                            "Librams",                              "Idoles",                               "Totems",
            "Cachets"
        ),
        'weaponSubClass' => array(
            13 => "Armes de pugilat",               15 => "Dagues",                          7 => "Epées à une main",                0 => "Haches à une main",               4 => "Masses à une main",
             6 => "Armes d'hast",                   10 => "Bâtons",                          8 => "Epées à deux mains",              1 => "Haches à deux mains",             5 => "Masses à deux mains",
            18 => "Arbalètes",                       2 => "Arcs",                            3 => "Armes à feu",                    16 => "Armes de jet",                   19 => "Baguettes",
            20 => "Cannes à pêche",                 14 => "Divers"
        ),
        'subClassMasks'      => array(
            0x02A5F3 => "Arme de mêlée",            0x0060 => "Bouclier",                   0x04000C => "Arme à distance",          0xA091 => "Arme de mêlée à une main"
        ),
        'traitShort'    => array(
            'atkpwr'    => "PA",                    'rgdatkpwr' => "PAD",                   'splpwr'    => "PS",                    'arcsplpwr' => "PArc",                  'firsplpwr' => "PFeu",
            'frosplpwr' => "PGiv",                  'holsplpwr' => "PSac",                  'natsplpwr' => "PNat",                  'shasplpwr' => "POmb",                  'splheal'   => "Soins"
        ),
        'spellModOp'    => array(
            "DAMAGE",                               "DURATION",                             "THREAT",                               "EFFECT1",                              "CHARGES",
            "RANGE",                                "RADIUS",                               "CRITICAL_CHANCE",                      "ALL_EFFECTS",                          "NOT_LOSE_CASTING_TIME",
            "CASTING_TIME",                         "COOLDOWN",                             "EFFECT2",                              "IGNORE_ARMOR",                         "COST",
            "CRIT_DAMAGE_BONUS",                    "RESIST_MISS_CHANCE",                   "JUMP_TARGETS",                         "CHANCE_OF_SUCCESS",                    "ACTIVATION_TIME",
            "DAMAGE_MULTIPLIER",                    "GLOBAL_COOLDOWN",                      "DOT",                                  "EFFECT3",                              "BONUS_MULTIPLIER",
            null,                                   "PROC_PER_MINUTE",                      "VALUE_MULTIPLIER",                     "RESIST_DISPEL_CHANCE",                 "CRIT_DAMAGE_BONUS_2",
            "SPELL_COST_REFUND_ON_FAIL"
        ),
        'combatRating'  => array(
            "WEAPON_SKILL",                         "DEFENSE_SKILL",                        "DODGE",                                "PARRY",                                "BLOCK",
            "HIT_MELEE",                            "HIT_RANGED",                           "HIT_SPELL",                            "CRIT_MELEE",                           "CRIT_RANGED",
            "CRIT_SPELL",                           "HIT_TAKEN_MELEE",                      "HIT_TAKEN_RANGED",                     "HIT_TAKEN_SPELL",                      "CRIT_TAKEN_MELEE",
            "CRIT_TAKEN_RANGED",                    "CRIT_TAKEN_SPELL",                     "HASTE_MELEE",                          "HASTE_RANGED",                         "HASTE_SPELL",
            "WEAPON_SKILL_MAINHAND",                "WEAPON_SKILL_OFFHAND",                 "WEAPON_SKILL_RANGED",                  "EXPERTISE",                            "ARMOR_PENETRATION"
        ),
        'lockType'      => array(
            null,                                   "Crochetage",                           "Herboristerie",                        "Minage",                               "Désarmement de piège",
            "Ouverture",                            "Trésor (DND)",                         "Gemmes elfiques calcifiées (DND)",     "Fermeture",                            "Pose de piège",
            "Ouverture rapide",                     "Fermeture rapide",                     "Ouverture (bricolage)",                "Ouverture (à genoux)",                 "Ouverture (en attaquant)",
            "Gahz'ridienne (DND)",                  "Explosif",                             "Ouverture JcJ",                        "Fermeture JcJ",                        "Pêche",
            "Calligraphie",                         "Ouverture à partir d'un véhicule",
        ),
        'stealthType'   => ["GENERAL", "TRAP"],
        'invisibilityType' => ["GENERAL", 3 => "TRAP", 6 => "DRUNK"]
    ),
    'item' => array(
        'notFound'      => "Cet objet n'existe pas.",
        'armor'         => "Armure : %s",
        'block'         => "Bloquer : %s",
        'charges'       => "Charges",
        'locked'        => "Verrouillé",
        'ratingString'  => "%s&nbsp;@&nbsp;L%s",
        'heroic'        => "Héroïque",
        'unique'        => "Unique",
        'uniqueEquipped'=> "Unique - Equipé",
        'startQuest'    => "Cet objet permet de lancer une quête",
        'bagSlotString' => "%s %d emplacements",
        'dps'           => "dégâts par seconde",
        'dps2'          => "dégâts par seconde",
        'addsDps'       => "Ajoute",
        'fap'           => "puissance d'attaque en combat farouche",
        'durability'    => "Durabilité",
        'realTime'      => "temps réel",
        'conjured'      => "Objet invoqué",
        'damagePhys'    => "Dégâts : %s",
        'damageMagic'   => "%s points de dégâts (%s)",
        'speed'         => "Vitesse",
        'sellPrice'     => "Prix de Vente",
        'itemLevel'     => "Niveau d'objet",
        'randEnchant'   => "&lt;Enchantement aléatoire&gt",
        'readClick'     => "&lt;Clique Droit pour Lire&gt",
        'openClick'     => "&lt;Clic Droit pour Ouvrir&gt",
        'set'           => "Set",
        'partyLoot'     => "Butin de groupe",
        'smartLoot'     => "Butin intelligent",
        'indestructible'=> "Ne peut être détruit",
        'deprecated'    => "Désuet",
        'useInShape'    => "Utilisable lorsque transformé",
        'useInArena'    => "Utilisable en Aréna",
        'refundable'    => "Remboursable",
        'noNeedRoll'    => "Ne peut pas faire un jet de Besoin",
        'atKeyring'     => "Va dans le trousseau de clés",
        'worth'         => "Vaut",
        'consumable'    => "Consommable",
        'nonConsumable' => "Non-consommable",
        'accountWide'   => "Portant sur le compte",
        'millable'      => "Pilable",
        'noEquipCD'     => "Aucun temps de recharge lorsqu'équipé",
        'prospectable'  => "Prospectable",
        'disenchantable'=> "Desencantable",
        'cantDisenchant'=> "Ne peut pas être désenchanté",
        'repairCost'    => "Cout de réparation",
        'tool'          => "Outil",
        'cost'          => "Coût",
        'content'       => "Contenu",
        '_transfer'     => 'Cet objet sera converti en <a href="?item=%d" class="q%d icontiny tinyspecial" style="background-image: url('.STATIC_URL.'/images/wow/icons/tiny/%s.gif)">%s</a> si vous transférez en <span class="icon-%s">%s</span>.',
        '_unavailable'  => "Cet objet n'est pas disponible pour les joueurs.",
        '_rndEnchants'  => "Enchantements aléatoires",
        '_chance'       => "(%s%% de chance)",
        'slot'          => "Emplacement",
        '_quality'      => "Qualité",
        'usableBy'      => "Utilisable par",
        'buyout'        => "Vente immédiate",
        'each'          => "chacun",
        'tabOther'      => "Autre",
        'gems'          => "Gemmes",
        'socketBonus'   => "Bonus de châsse",
        'socket'        => array(
            "Méta-châsse",          "Châsse rouge",     "Châsse jaune",         "Châsse bleue",           -1 => "Châsse prismatique"
        ),
        'gemColors'     => array(                           // *_GEM
            "Méta",                 "rouge(s)",         "jaune(s)",             "bleue(s)"
        ),
        'gemConditions' => array(                           // ENCHANT_CONDITION_*
            2 => ["moins de %d gemme %s", "moins de %d gemmes %s"],
            3 => "plus de gemmes %s que de %s",             // plus de gemmes %s que |2 %s
            5 => ["au moins %d gemme %s", "au moins %d gemmes %s"]
        ),
        'reqRating'     => array(                           // ITEM_REQ_ARENA_RATING*
            "Nécessite une cote d'arène personnelle et en équipe de %d",
            "Nécessite une cote d'arène personnelle et en équipe de %d<br>en arène de 3c3 ou 5c5.",
            "Nécessite une cote d'arène personnelle et en équipe de %d<br>en arène de 5c5."
        ),
        'quality'       => array(
            "Médiocre",             "Classique",        "Bonne",                "Rare",
            "Épique",               "Légendaire",       "Artefact",             "Héritage"
        ),
        'trigger'       => array(
            "Utilise : ",           "Équipé : ",        "Chances quand vous touchez : ", "",                    "",
            "",                     ""
        ),
        'bonding'       => array(
            "Lié au compte",                            "Lié quand ramassé",                                    "Lié quand équipé",
            "Lié quand utilisé",                        "Objet de quête",                                       "Objet de quête"
        ),
        "bagFamily"     => array(
            "Sac",                  "Carquois",         "Giberne",              "Sac d'âmes",                   "Sac de travailleur du cuir",
            "Sac de calligraphie",  "Sac d'herbes",     "Sac d'enchanteur",     "Sac d'ingénieur",              null, /*Clé*/
            "Sac de gemmes",        "Sac de mineur"
        ),
        'inventoryType' => array(
            null,                   "Tête",             "Cou",                  "Épaules",                      "Chemise",
            "Torse",                "Taille",           "Jambes",               "Pieds",                        "Poignets",
            "Mains",                "Doigt",            "Bijou",                "À une main",                   "Main gauche", /*Shield*/
            "À distance",           "Dos",              "Deux mains",           "Sac",                          "Tabard",
            null, /*Robe*/          "Main droite",      "Main gauche",          "Tenu en main gauche",          "Projectile",
            "Armes de jet",         null, /*Ranged2*/   "Carquois",             "Relique"
        ),
        'armorSubClass' => array(
            "Divers",               "Armures en tissu", "Armures en cuir",      "Armures en mailles",           "Armures en plaques",
            null,                   "Bouclier",         "Libram",               "Idole",                        "Totem",
            "Cachet"
        ),
        'weaponSubClass' => array(
            "Hache",                "Hache",            "Arc",                  "Arme à feu",                   "Masse",
            "Masse",                "Armes d'hast",     "Épée",                 "Épée",                         null,
            "Bâton",                null,               null,                   "Arme de pugilat",              "Divers",
            "Dague",                "Armes de jet",     null,                   "Arbalète",                     "Baguette",
            "Canne à pêche"
        ),
        'projectileSubClass' => array(
            null,                   null,               "Flèche",               "Balle",                         null
        ),
        'elixirType'    => [null, "De bataille", "De gardien"],
        'cat'           => array(
             2 => "Armes",                                  // self::$spell['weaponSubClass']
             4 => array("Armure", array(
                 1 => "Armures en tissu",            2 => "Armures en cuir",         3 => "Armures en mailles",      4 => "Armures en plaques",      6 => "Boucliers",               7 => "Librams",
                 8 => "Idoles",                      9 => "Totems",                 10 => "Cachets",                -6 => "Capes",                  -5 => "Accessoires pour main gauche", -8 => "Chemises",
                -7 => "Tabards",                    -3 => "Amulettes",              -2 => "Anneaux",                -4 => "Bijoux",                  0 => "Divers (Armure)",
            )),
             1 => array("Conteneurs", array(
                 0 => "Sacs",                        3 => "Sacs d'enchanteur",       4 => "Sacs d'ingénieur",        5 => "Sacs de gemmes",          2 => "Sacs d'herbes",           8 => "Sacs de calligraphie",
                 7 => "Sacs de travailleur du cuir", 6 => "Sacs de mineur",          1 => "Sacs d'âmes"
            )),
             0 => array("Consommables", array(
                -3 => "Améliorations d'objet temporaires",                           6 => "Améliorations d'objet permanentes",                       2 => ["Élixirs", [1 => "Élixirs de bataille", 2 => "Élixirs du gardien"]],
                 1 => "Potions",                     4 => "Parchemins",              7 => "Bandages",                0 => "Consommables",            3 => "Flacons",                 5 => "Nourriture et boissons",
                 8 => "Autre (Consommables)"
            )),
            16 => array("Glyphes", array(
                 7 => "Glyphes de chaman",           3 => "Glyphes de chasseur",     6 => "Glyphes de chevalier de la mort",                         9 => "Glyphes de démoniste",   11 => "Glyphes de druide",
                 1 => "Glyphes de guerrier",         8 => "Glyphes de mage",         2 => "Glyphes de paladin",      5 => "Glyphes de prêtre",       4 => "Glyphes de voleur"
            )),
             7 => array("Artisanat", array(
                14 => "Enchantements d'armure",      5 => "Tissu",                   3 => "Appareils",              10 => "Élémentaire",            12 => "Enchantement",            2 => "Explosifs",
                 9 => "Herbes",                      4 => "Joaillerie",              6 => "Cuir",                   13 => "Matériaux",               8 => "Viande",                  7 => "Métal et pierre",
                 1 => "Éléments",                   15 => "Enchantements d'arme",   11 => "Autre (Artisanat)"
             )),
             6 => ["Projectiles", [                  2 => "Flèches",                 3 => "Balles"  ]],
            11 => ["Carquois",    [                  2 => "Carquois",                3 => "Gibernes"]],
             9 => array("Recettes", array(
                 0 => "Livres",                      6 => "Recettes d'alchimie",     4 => "Plans de forge",          5 => "Recettes de cuisine",     8 => "Formules d'enchantement", 3 => "Schémas d'ingénierie",
                 7 => "Livres de premiers soins",    9 => "Livres de pêche",        11 => "Techniques de calligraphie",10 => "Dessins de joaillerie",1 => "Patrons de travail du cuir",12 => "Guides de Minage",
                 2 => "Patrons de couture"
            )),
             3 => array("Gemmes", array(
                 6 => "Méta-gemmes",                 0 => "Gemmes rouges",           1 => "Gemmes bleues",           2 => "Gemmes jaunes",           3 => "Gemmes violettes",        4 => "Gemmes vertes",
                 5 => "Gemmes oranges",              8 => "Gemmes prismatiques",     7 => "Gemmes simples"
            )),
            15 => array("Divers", array(
                -2 => "Marques d'armure",            3 => "Évènement",               0 => "Camelote",                1 => "Composants",              5 => "Montures",               -7 => "Montures volantes",
                 2 => "Compagnons",                  4 => "Autre (Divers)"
            )),
            10 => "Monnaies",
            12 => "Quête",
            13 => "Clés",
        ),
        'statType'      => array(
            "Augmente vos points de mana de %d.",
            "Augmente vos points de vie de %d.",
            null,
            "Agilité",
            "Force",
            "Intelligence",
            "Esprit",
            "Endurance",
            null, null, null, null,
            "Augmente le score de défense de %d.",
            "Augmente de %d le score d'esquive.",
            "Augmente de %d le score de parade.",
            "Augmente de %d le score de blocage.",
            "Augmente de %d le score de toucher en mêlée. ",
            "Augmente de %d le score de toucher à distance.",
            "Augmente de %d le score de toucher des sorts.",
            "Augmente de %d le score de coup critique en mêlée.",
            "Augmente de %d le score de coup critique à distance.",
            "Augmente de %d le score de coup critique des sorts.",
            "Augmente de %d le score d'évitement des coups en mêlée.",
            "Augmente de %d le score d'évitement des coups à distance.",
            "Augmente de %d le score d'évitement des coups des sorts.",
            "Augmente de %d le score d'évitement des critiques en mêlée.",
            "Augmente de %d le score d'évitement des critiques à distance.",
            "Augmente de %d le score d'évitement des critiques des sorts.",
            "Augmente de %d le score de hâte en mêlée.",
            "Augmente de %d le score de hâte à distance.",
            "Augmente de %d le score de hâte des sorts.",
            "Augmente votre score de toucher de %d.",
            "Augmente votre score de coup critique de %d.",
            "Augmente de %d le score d'évitement des coups.",
            "Augmente de %d le score d'évitement des critiques.",
            "Augmente votre score de résilience de %d.",
            "Augmente votre score de hâte de %d.",
            "Augmente votre score d'expertise de +%d.",
            "Augmente la puissance d'attaque de %d.",
            "Augmente la puissance d'attaque à distance de %d.",
            "Augmente de %d la puissance d'attaque pour les formes de félin, d'ours, d'ours redoutable et de sélénien uniquement.",
            "Augmente les dégâts des sorts et des effets magiques d'un maximum de %d.",
            "Augmente les soins des sorts et des effets magiques d'un maximum de %d.",
            "Rend %d points de mana toutes les 5 secondes.",
            "Augmente de %d votre score de pénétration d'armure.",
            "Augmente la puissance des sorts de %d.",
            "Rend %d points de vie toutes les 5 s.",
            "Augmente la pénétration des sorts de %d.",
            "Augmente la valeur de blocage de votre bouclier de %d.",
            "Stat Inutilisée #%d (%d)",
        )
    )
);

?>
