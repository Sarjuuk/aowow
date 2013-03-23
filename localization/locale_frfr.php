<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$lang = array(
    // page variables
    'main' => array(
        'link'          => "Lien",
        'signIn'        => "S'enregistrer",
        'jsError'       => "S'il vous plait, assurez vous d'avoir le javascript autorisé.",
        'searchButton'  => "Rechercher",
        'language'      => "Langue",
        'numSQL'        => "Nombre de requêtes SQL",
        'timeSQL'       => "Temps d'exécution des requêtes SQL",
        'noJScript'     => "<b>Ce site requiert JavaScript pour fonctionner.</b><br />Veuillez <a href=\"https://www.google.com/support/adsense/bin/answer.py?answer=12654\" target=\"_blank\">activer JavaScript</a> dans votre navigateur.",
        'profiles'      => "Vos personnages",      // translate.google :x
        'links'         => "Liens",
        'pageNotFound'  => "Ce %s n'existe pas.",
        'gender'        => "Genre",
        'sex'           => [null, 'Homme', 'Femme'],
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
        'forum'         => "[Forum]",
        'days'          => "jours",
        'hours'         => "heures",
        'minutes'       => "minutes",
        'seconds'       => "secondes",
        'millisecs'     => "[milliseconds]",
        'daysAbbr'      => "jour",
        'hoursAbbr'     => "h",
        'minutesAbbr'   => "min",
        'secondsAbbr'   => "s",
        'millisecsAbbr' => "[ms]",
        'name'          => "Nom",

        // filter
        'extSearch'     => "Recherche avancée",
        'addFilter'     => "Ajouter un autre filtre",
        'match'         => "Critère",
        'allFilter'     => "Tous les filtres",
        'oneFilter'     => "Au moins un",
        'applyFilter'   => "Appliquer le filtre",
        'resetForm'     => "Rétablir",
        'refineSearch'  => "Astuce : Affinez votre recherche en utilisant une <a href=\"javascript:;\" id=\"fi_subcat\">sous-catégorie</a>.",

        // infobox
        'disabled'      => "[Disabled]",
        'disabledHint'  => "[Cannot be attained or completed]",
        'serverside'    => "[Serverside]",
        'serversideHint' => "[These informations are not in the Client and have been provided by sniffing and/or guessing.]",
    ),
    'search' => array(
        'search'        => "Recherche",
        'foundResult'   => "Résultats de recherche pour",
        'noResult'      => "Aucun résultat pour malordawsne",
        'tryAgain'      => "Veuillez essayer d'autres mots ou vérifiez l'orthographe des termes de recherche.",
    ),
    'game' => array (
        'class'         => "classe",
        'classes'       => "Classes",
        'currency'      => "monnaies",
        'currencies'    => "Monnaies",
        'races'         => "Races",
        'title'         => "titre",
        'titles'        => "Titres",
        'eventShort'    => "Évènement",
        'event'         => "Évènement mondial",
        'events'        => "Évènements mondiaux",
        'cooldown'      => "%s de recharge",
        'itemset'       => "ensemble d'objets",
        'itemsets'      => "Ensembles d'objets",
        'requires'      => "Requiert",
        'reqLevel'      => "Niveau %s requis",
        'reqLevelHlm'   => "Requiert Niveau %s",
        'valueDelim'    => " - ",
        'si'            => array(-2 => "Horde seulement", -1 => "Alliance seulement", null, "Alliance", "Horde", "Les deux"),
        'resistances'   => array(null, 'Résistance au Sacré', 'Résistance au Feu', 'Résistance à la Nature', 'Résistance au Givre', 'Résistance à l\'Ombre', 'Résistance aux Arcanes'),
        'di'            => array(null, "Magie", "Malédiction", "Maladie", "Poison", "Camouflage", "Invisibilité", null, null, "Enrager"),
        'sc'            => array("Physique", "Sacré", "Feu", "Nature", "Givre", "Ombre", "Arcane"),
        'cl'            => array(null, "Guerrier", "Paladin", "Chasseur", "Voleur", "Prêtre", "DeathChevalier de la mort", "Chaman", "Mage", "Démoniste", null, "Druide"),
        'ra'            => array(-2 => "Horde", -1 => "Alliance", "Les deux", "Humain", "Orc", "Nain", "Elfe de la nuit", "Mort-vivant", "Tauren", "Gnome", "Troll", null, "Elfe de sang", "Draeneï"),
        'rep'           => array("Détesté", "Hostile", "Inamical", "Neutre", "Amical", "Honoré", "Révéré", "Exalté"),
        'st'            => array(
            null,               "Forme de félin",               "Arbre de vie",                 "Forme de voyage",              "Aquatic Form",
            "Forme d'ours",     null,                           null,                           "Forme d'ours redoutable",      null,
            null,               null,                           null,                           "Danse de l'ombre",             null,
            null,               "Ghostwolf",                    "Posture de combat",            "Posture défensive",            "Posture berserker",
            null,               null,                           "Métamorphe",                   null,                           null,
            null,               null,                           "Forme de vol rapide",          "Forme d'Ombre",                "Forme de vol",
            "Camouflage",       "Forme de sélénien",            "Esprit de rédemption"
        ),
        'pvpRank'       => array(
            null,                                       "Private / Scout",                      "Corporal / Grunt",
            "Sergeant / Sergeant",                      "Master Sergeant / Senior Sergeant",    "Sergeant Major / First Sergeant",
            "Knight / Stone Guard",                     "Knight-Lieutenant / Blood Guard",      "Knight-Captain / Legionnare",
            "Knight-Champion / Centurion",              "Lieutenant Commander / Champion",      "Commander / Lieutenant General",
            "Marshal / General",                        "Field Marshal / Warlord",              "Grand Marshal / High Warlord"
        ),
    ),
    'error' => array(
        'errNotFound'   => "Page not found",
        'errPage'       => "What? How did you... nevermind that!\n<br>\n<br>\nIt appears that the page you have requested cannot be found. At least, not in this dimension.\n<br>\n<br>\nPerhaps a few tweaks to the <span class=\"q4\">[WH-799 Major Confabulation Engine]</span> may result in the page suddenly making an appearance!\n<div class=\"pad\"></div>\n<div class=\"pad\"></div>\nOr, you can try \n<a href=\"http://www.wowhead.com/?aboutus#contact\">contacting us</a>\n- the stability of the WH-799 is debatable, and we wouldn't want another accident...",
        'goStart'       => "Return to the <a href=\"index.php\">homepage</a>",
        'goForum'       => "Feedback <a href=\"?forums&board=1\">forum</a>",
    ),
    'account'  => [],
    'event' => array(
        'category'      => array("Non classés", "Vacances", "Récurrent", "Joueur ctr. Joueur")
    ),
    'achievement' => array(
        'achievements'  => "hauts faits",
        'criteria'      => "Critères",
        'achievement'   => "haut fait",
        'points'        => "Points",
        'series'        => "Série",
        'outOf'         => "sur",
        'criteriaType'  => "Criterium Type-Id:",
        'itemReward'    => "Vous recevrez:",
        'titleReward'   => "Vous devriez recevoir le titre \"<a href=\"?title=%d\">%s</a>\"",
        'slain'         => "tué",
    ),
    'compare' => array(
        'compare'       => "Outil de comparaison d'objets",
    ),
    'talent' => array(
        'talentCalc'    => "Calculateur de Talents",
        'petCalc'       => "Calculateur de familiers",
        'chooseClass'   => "Choisissez une classe",
        'chooseFamily'  => "Choisissez un familier",
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
        'zone'          => "Zone",
        'zonePartOf'    => "Cette zone fait partie de la zone",
    ),
    'title' => array(
        'cat'           => array(
            'Général',      'Joueur ctr. Joueur',    'Réputation',       'Donjons & raids',     'Quêtes',       'Métiers',      'Évènements mondiaux'
        )
    ),
    'currency' => array(
        'cat'           => array(
            1 => "Divers", 2 => "JcJ", 4 => "Classique", 21 => "Wrath of the Lich King", 22 => "Raid", 23 => "Burning Crusade", 41 => "Test", 3 => "Inutilisées"
        )
    ),
    'itemset' => array(
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
        'remaining'     => "%s restantes",
        'untilCanceled' => "jusqu’à annulation",
        'castIn'        => "%s s d'incantation",
        'instantPhys'   => "Incantation immédiate",
        'instantMagic'  => "Instantanée",
        'channeled'     => "Canalisée",
        'range'         => "m de portée",
        'meleeRange'    => "Allonge",
        'reagents'      => "Composants",
        'tools'         => "Outils",
        'home'          => "%lt;Auberge&gt;",
        'pctCostOf'     => "de la %s de base",
        'costPerSec'    => ", plus %s par seconde",
        'costPerLevel'  => ", plus %s par niveau",
        'powerTypes'    => array(
            -2 => "vie",     -1 => null,    "mana",     "rage",     "focus",    "énergie",      "[Happiness]",      "[Rune]",   "puissance runique",
            'AMMOSLOT' => "[Ammo]",         'STEAM' => "[Steam Pressure]",      'WRATH' => "courroux",              'PYRITE' => "Pyrite",
            'HEAT' => "chaleur",            'OOZE' => "limon",                  'BLOOD_POWER' => "puissance de sang"
        )
    ),
    'item' => array(
        'armor'         => "Armure :",
        'block'         => "Bloquer :",
        'charges'       => "Charges",
        'expend'        => "expendable",
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
        'duration'      => "Durée",
        'realTime'      => "temps réel",
        'conjured'      => "Objet invoqué",
        'damagePhys'    => "Dégâts : %s",
        'damageMagic'   => "%s points de dégâts (%s)",
        'speed'         => "Vitesse",
        'sellPrice'     => "Prix de Vente",
        'itemLevel'     => "Niveau d'objet",
        'randEnchant'   => "&lt;Enchantement aléatoire&gt",
        'readClick'     => "&lt;Clique Droit pour Lire&gt",
        'set'           => "Set",
        'socketBonus'   => "Bonus de châsse",
        'socket'        => array(
            "Méta-châsse",          "Châsse rouge",     "Châsse jaune",         "Châsse bleue",           -1 => "Châsse prismatique"
        ),
        'quality'       => array (
            "Médiocre",             "Classique",        "Bonne",                "Rare",
            "Épique",               "Légendaire",       "Artefact",             "Héritage"
        ),
        'trigger'       => array (
            "Utilise: ",            "Équipé : ",        "Chances quand vous touchez : ", null,                  null,
            null,                   null
        ),
        'bonding'       => array (
            "Lié au compte",                            "Lié quand ramassé",                                    "Lié quand équipé",
            "Lié quand utilisé",                        "[Soulbound]",                                          "Objet de quête"
        ),
        "bagFamily"     => array(
            "Sac",                  "Carquois",         "Giberne",              "Sac d'âmes",                   "Sac de travailleur du cuir",
            "Sac de calligraphie",  "Sac d'herbes",     "Sac d'enchanteur",     "Sac d'ingénieur",              "Clé",
            "Sac de gemmes",        "Sac de mineur"
        ),
        'inventoryType' => array(
            null,                   "Tête",             "Cou",                  "Épaules",                      "Chemise",
            "Torse",                "Taille",           "Jambes",               "Pieds",                        "Poignets",
            "Mains",                "Doigt",            "Bijou",                "À une main",                   "Main gauche",
            "À distance",           "Dos",              "Deux mains",           "Sac",                          "Tabard",
            "Torse",                "Main droite",      "Main gauche",          "Tenu en main gauche",          "Projectile",
            "Armes de jet",         "À distance",       "Carquois",             "Relique"
        ),
        'armorSubclass' => array(
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
        'statType'  => array(
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
