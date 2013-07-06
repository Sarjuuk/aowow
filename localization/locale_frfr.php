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
    'main' => array(
        'name'          => "Nom",
        'link'          => "Lien",
        'signIn'        => "S'enregistrer",
        'jsError'       => "S'il vous plait, assurez vous d'avoir le javascript autorisé.",
        'searchButton'  => "Rechercher",
        'language'      => "Langue",
        'numSQL'        => "Nombre de requêtes SQL",
        'timeSQL'       => "Temps d'exécution des requêtes SQL",
        'noJScript'     => "<b>Ce site requiert JavaScript pour fonctionner.</b><br />Veuillez <a href=\"https://www.google.com/support/adsense/bin/answer.py?answer=12654\" target=\"_blank\">activer JavaScript</a> dans votre navigateur.",
        'profiles'      => "Vos personnages",      // translate.google :x
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

        'n_a'           => "n/d",

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
        'unavailable'   => "Non disponible aux joueurs",
        'disabled'      => "[Disabled]",
        'disabledHint'  => "[Cannot be attained or completed]",
        'serverside'    => "[Serverside]",
        'serversideHint' => "[These informations are not in the Client and have been provided by sniffing and/or guessing.]",

        // red buttons
        'links'         => "Liens",
        'compare'       => "Comparer",
        'view3D'        => "Voir en 3D"
    ),
    'search' => array(
        'search'        => "Recherche",
        'foundResult'   => "Résultats de recherche pour",
        'noResult'      => "Aucun résultat pour malordawsne",
        'tryAgain'      => "Veuillez essayer d'autres mots ou vérifiez l'orthographe des termes de recherche.",
    ),
    'game' => array (
        'achievement'   => "haut fait",
        'achievements'  => "Hauts faits",
        'class'         => "classe",
        'classes'       => "Classes",
        'currency'      => "monnaies",
        'currencies'    => "Monnaies",
        'difficulty'    => "Difficulté",
        'dispelType'    => "Type de dissipation",
        'duration'      => "Durée",
        'gameObject'    => "entité",
        'gameObjects'   => "Entités",
        'glyphType'     => "Type de glyphe",
        'race'          => "race",
        'races'         => "Races",
        'title'         => "titre",
        'titles'        => "Titres",
        'eventShort'    => "Évènement",
        'event'         => "Évènement mondial",
        'events'        => "Évènements mondiaux",
        'cooldown'      => "%s de recharge",
        'itemset'       => "ensemble d'objets",
        'itemsets'      => "Ensembles d'objets",
        'mechanic'      => "Mécanique",
        'mechAbbr'      => "Mécan.",
        'pet'           => "Familier",
        'pets'          => "Familiers de chasseur",
        'petCalc'       => "Calculateur de familiers",
        'requires'      => "%s requis",
        'requires2'     => "Requiert",
        'reqLevel'      => "Niveau %s requis",
        'reqLevelHlm'   => "Requiert Niveau %s",
        'reqSkillLevel' => "Niveau de compétence requis",
        'level'         => "Niveau",
        'school'        => "École",
        'spell'         => "sort",
        'spells'        => "Sorts",
        'type'          => "Type",
        'valueDelim'    => " - ",
        'zone'          => "zone",
        'zones'         => "Zones",
        'expansions'    => array("Classique", "The Burning Crusade", "Wrath of the Lich King"),
        'stats'         => array("Force", "Agilité", "Endurance", "Intelligence", "Esprit"),
        'languages'     => array(
            1 => "Orc",         2 => "Darnassien",      3 => "Taurahe",     6 => "Nain",            7 => "Commun",          8 => "Démoniaque",      9 => "Titan",           10 => "Thalassien",
            11 => "Draconique", 12 => "Kalimag",        13 => "Gnome",      14 => "Troll",          33 => "Bas-parler",     35 => "Draeneï",        36 => "Zombie",         37 => "Binaire gnome",      38 => "Binaire gobelin"
        ),
        'gl'            => array(null, "Majeur", "Mineur"),
        'si'            => array(-2 => "Horde seulement", -1 => "Alliance seulement", null, "Alliance", "Horde", "Les deux"),
        'resistances'   => array(null, 'Résistance au Sacré', 'Résistance au Feu', 'Résistance à la Nature', 'Résistance au Givre', 'Résistance à l\'Ombre', 'Résistance aux Arcanes'),
        'dt'            => array(null, "Magie", "Malédiction", "Maladie", "Poison", "Camouflage", "Invisibilité", null, null, "Enrager"),
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
        'me'            => array(
            null,                       "Charmé",                   "Désorienté",               "Désarmé",                  "Distrait",                 "En fuite",                 "Maladroit",                "Immobilisé",
            "Pacifié",                  "Réduit au silence",        "Endormi",                  "Pris au piège",            "Étourdi",                  "Gelé",                     "Stupéfié",                 "Sanguinolent",
            "Soins",                    "Métamorphosé",             "Banni",                    "Protégé",                  "Entravé",                  "Monté",                    "Séduit",                   "Repoussé",
            "Horrifié",                 "Invulnérable",             "Interrompu",               "Hébété",                   "Découverte",               "Invulnérable",             "Assommé",                  "Enragé"
        ),
        'ct'            => array(
            "Non classés",              "Bête",                     "Draconien",                "Démon",                    "Élémentaire",              "Géant",                    "Mort-vivant",              "Humanoïde",
            "Bestiole",                 "Mécanique",                "Non spécifié",             "Totem",                    "Familier pacifique",       "Nuage de gaz"
        ),
        'fa'            => array(
            1 => "Loup",                2 => "Félin",               3 => "Araignée",            4 => "Ours",                5 => "Sanglier",            6 => "Crocilisque",         7 => "Charognard",          8 => "Crabe",
            9 => "Gorille",             11 => "Raptor",             12 => "Haut-trotteur",      20 => "Scorpide",           21 => "Tortue",             24 => "Chauve-souris",      25 => "Hyène",              26 => "Oiseau de proie",
            27 => "Serpent des vents",  30 => "Faucon-dragon",      31 => "Ravageur",           32 => "Traqueur dim.",      33 => "Sporoptère",         34 => "Raie du Néant",      35 => "Serpent",            37 => "Phalène",
            38 => "Chimère",            39 => "Diablosaure",        41 => "Silithide",          42 => "Ver",                43 => "Rhinocéros",         44 => "Guêpe",              45 => "Chien du Magma",     46 => "Esprit de bête"
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
    'npc'   => array(
        'rank'          => ['Standard', 'Élite', 'Élite rare', 'Boss', 'Rare']
    ),
    'achievement' => array(
        'criteria'      => "Critères",
        'points'        => "Points",
        'series'        => "Série",
        'outOf'         => "sur",
        'criteriaType'  => "Criterium Type-Id:",
        'itemReward'    => "Vous recevrez :",
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
        // 'zone'          => "Zone",
        // 'zonePartOf'    => "Cette zone fait partie de la zone",
        'cat'           => array(
            "Royaumes de l'est",        "Kalimdor",                 "Donjons",                  "Raids",                    "Inutilisées",              null,
            "Champs de bataille",       null,                       "Outreterre",               "Arènes",                   "Norfendre"
        )
    ),
    'quest' => array(
        'level'         => 'Niveau %s',
        'daily'         => 'Journalière',
        'requirements'  => 'Conditions'
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
    'pet'      => array(
        'exotic'        => "Exotique",
        "cat"           => ["Férocité", "Tenacité", "Ruse"]
    ),
    'itemset' => array(
        '_desc'         => "<b>%s</b> est le <b>%s</b>. Il contient %s pièces.",
        '_descTagless'  => "<b>%s</b> est un ensemble d'objet qui contient %s pièces.",
        '_setBonuses'   => "Bonus de l'ensemble",
        '_conveyBonus'  => "Plus d'objets de cet ensemble sont équipés, plus votre personnage aura des bonus de caractéristiques.",
        '_pieces'       => "pièces",
        '_unavailable'  => "Cet objet n'est plus disponible aux joueurs.",
        '_tag'          => "Étiquette",

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

        'starter'       => "Sortilège initiaux",
        'trainingCost'  => "Coût d'entraînement",
        'remaining'     => "%s restantes",
        'untilCanceled' => "jusqu’à annulation",
        'castIn'        => "%s s d'incantation",
        'instantPhys'   => "Incantation immédiate",
        'instantMagic'  => "Instantanée",
        'channeled'     => "Canalisée",
        'range'         => "m de portée",
        'meleeRange'    => "Allonge",
        'unlimRange'    => "Portée illimitée",
        'reagents'      => "Composants",
        'tools'         => "Outils",
        'home'          => "%lt;Auberge&gt;",
        'pctCostOf'     => "de la %s de base",
        'costPerSec'    => ", plus %s par seconde",
        'costPerLevel'  => ", plus %s par niveau",
        'powerRunes'    => ["Givre", "Impie", "Sang", "Mort"],
        'powerTypes'    => array(
            -2 => "vie",     -1 => null,    "mana",     "rage",     "focus",    "énergie",      "[Happiness]",      "[Rune]",   "puissance runique",
            'AMMOSLOT' => "[Ammo]",         'STEAM' => "[Steam Pressure]",      'WRATH' => "courroux",              'PYRITE' => "Pyrite",
            'HEAT' => "chaleur",            'OOZE' => "limon",                  'BLOOD_POWER' => "puissance de sang"
        ),
        'relItems'      => array (
            'base'    => "<small>Montre %s reliés à <b>%s</b></small>",
            'link'    => " ou ",
            'recipes' => "les <a href=\"?items=9.%s\">recettes</a>",
            'crafted' => "les <a href=\"?items&filter=cr=86;crs=%s\">objets fabriqués</a>"
        ),
        'cat'           => array(
              7 => "Techniques",
            -13 => "Glyphes",
            -11 => array("Compétences", 8 => "Armure", 10 => "Langues", 6 => "Armes"),
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
             -7 => array("Talents de familiers", 411 => "Ruse", 410 => "Férocité", 409 => "Tenacité"),
             11 => array(
                "Métiers",
                171 => "Alchimie",
                164 => array("Forge", 9788 => "Fabricant d'armures", 9787 => "Fabricant d'armes", 17041 => "Maître fabricant de haches", 17040 => "Maître fabricant de marteaux", 17039 => "Maître fabricant d'épées"),
                333 => "Enchantement",
                202 => array("Ingénierie", 20219 => "Ingénieur gnome", 20222 => "Ingénieur goblin"),
                182 => "Herboristerie",
                773 => "Calligraphie",
                755 => "Joaillerie",
                165 => array("Travail du cuir", 10656 => "Travail du cuir d'écailles de dragon", 10658 => "Travail du cuir élémentaire", 10660 => "Travail du cuir tribal"),
                186 => "Minage",
                393 => "Dépeçage",
                197 => array("Couture", 26798 => "Couture d'étoffe lunaire", 26801 => "Couture de tisse-ombre", 26797 => "Couture du feu-sorcier"),
            ),
              9 => array("Compétences secondaires", 185 => "Cuisine", 129 => "Secourisme", 356 => "Pêche", 762 => "Monte"),
             -9 => "Habilité de MJ",
             -8 => "Habilité de PNJ",
              0 => "Non classés"
        ),
        'armorSubClass' => array(
            "Divers",               "Armures en tissu", "Armures en cuir",      "Armures en mailles",           "Armures en plaques",
            null,                   "Boucliers",        "Librams",              "Idoles",                       "Totems",
            "Cachets"
        ),
        'weaponSubClass' => array(
            "Haches à une main",    "Haches à deux mains", "Arcs",              "Armes à feu",                  "Masses à une main",
            "Masses à deux mains",  "Armes d'hast",     "Epées à une main",     "Epées à deux mains",           null,
            "Bâtons",               null,               null,                   "Armes de pugilat",             "Divers",
            "Dagues",               "Armes de jet",     null,                   "Arbalètes",                    "Baguettes",
            "Cannes à pêche"
        ),
        'subClassMasks'      => array(
            0x02A5F3 => 'Arme de mêlée',                0x0060 => 'Bouclier',                       0x04000C => 'Arme à distance',              0xA091 => 'Arme de mêlée à une main'
        ),
        'traitShort'    => array(
            'atkpwr'    => "PA",                        'rgdatkpwr' => "PAD",                                   'splpwr'    => "PS",
            'arcsplpwr' => "PArc",                      'firsplpwr' => "PFeu",                                  'frosplpwr' => "PGiv",
            'holsplpwr' => "PSac",                      'natsplpwr' => "PNat",                                  'shasplpwr' => "POmb",
            'splheal'   => "Soins"
        )
    ),
    'item' => array(
        'armor'         => "Armure : %s",
        'block'         => "Bloquer : %s",
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
    ),
    'colon'         => ' : '
);

?>
