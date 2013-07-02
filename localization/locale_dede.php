<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/*
    some translations have yet to be taken from or improved by the use of:
    <path>\World of Warcraft\Data\deDE\patch-deDE-3.MPQ\Interface\FrameXML\GlobalStrings.lua
    like: ITEM_MOD_*, POWER_TYPE_*, ITEM_BIND_*, PVP_RANK_*
*/

$lang = array(
    // page variables
    'main' => array(
        'name'          => "Name",
        'link'          => "Link",
        'signIn'        => "Anmelden",
        'jsError'       => "Stelle bitte sicher, dass JavaScript aktiviert ist.",
        'searchButton'  => "Suche",
        'language'      => "Sprache",
        'numSQL'        => "Anzahl an MySQL-Queries",
        'timeSQL'       => "Zeit für MySQL-Queries",
        'noJScript'     => "<b>Diese Seite macht ausgiebigen Gebrauch von JavaScript.</b><br />Bitte <a href=\"https://www.google.com/support/adsense/bin/answer.py?answer=12654\" target=\"_blank\">aktiviert JavaScript</a> in Eurem Browser.",
        'profiles'      => "Deine Charaktere",
        'pageNotFound'  => "Diese|Dieser|Dieses %s existiert nicht.",        // todo: dämliche Fälle...
        'gender'        => "Geschlecht",
        'sex'           => [null, 'Mann', 'Frau'],
        'quickFacts'    => "Kurzübersicht",
        'screenshots'   => "Screenshots",
        'videos'        => "Videos",
        'side'          => "Seite",
        'related'       => "Weiterführende Informationen",
        'contribute'    => "Beitragen",
        // 'replyingTo'    => "Antwort zu einem Kommentar von",
        'submit'        => "Absenden",
        'cancel'        => "Abbrechen",
        'rewards'       => "Belohnungen",
        'gains'         => "Belohnungen",
        'login'         => "Login",
        'forum'         => "Forum",
        'days'          => "Tage",
        'hours'         => "Stunden",
        'minutes'       => "Minuten",
        'seconds'       => "Sekunden",
        'millisecs'     => "Millisekunden",
        'daysAbbr'      => "T",
        'hoursAbbr'     => "Std.",
        'minutesAbbr'   => "Min.",
        'secondsAbbr'   => "Sek.",
        'millisecsAbbr' => "Ms",

        'n_a'           => "n. v.",

        // err_title = Fehler in AoWoW
        // un_err = Gib bitte deinen Benutzernamen ein
        // pwd_err = Gib bitte dein Passwort ein
        // signin_msg = Gib bitte deinen Accountnamen ein
        // c_pwd = Passwort wiederholen
        // facts = Übersicht
        // This_Object_cant_be_found = Der Standort dieses Objekts ist nicht bekannt.

        // filter
        'extSearch'     => "Erweiterte Suche",
        'addFilter'     => "Weiteren Filter hinzufügen",
        'match'         => "Verwendete Filter",
        'allFilter'     => "Alle Filters",
        'oneFilter'     => "Mindestens einer",
        'applyFilter'   => "Filter anwenden",
        'resetForm'     => "Formular zurücksetzen",
        'refineSearch'  => "Tipp: Präzisiere deine Suche mit Durchsuchen einer <a href=\"javascript:;\" id=\"fi_subcat\">Unterkategorie</a>.",

        // infobox
        'unavailable'   => "Nicht für Spieler verfügbar",
        'disabled'      => "Deaktiviert",
        'disabledHint'  => "Kann nicht erhalten oder abgeschlossen werden.",
        'serverside'    => "Serverseitig",
        'serversideHint' => "Diese Informationen sind nicht im Client enthalten und wurden gesnifft und/oder erraten.",

        // red buttons
        'links'         => "Links",
        'compare'       => "Vergleichen",
        'view3D'        => "3D-Ansicht"
    ),
    'search' => array(
        'search'        => "Suche",
        'foundResult'   => "Suchergebnisse für",
        'noResult'      => "Keine Ergebnisse für",
        'tryAgain'      => "Bitte versucht es mit anderen Suchbegriffen oder überprüft deren Schreibweise.",
    ),
    'game' => array(
        'achievement'   => "Erfolg",
        'achievements'  => "Erfolge",
        'class'         => "Klasse",
        'classes'       => "Klassen",
        'currency'      => "Währung",
        'currencies'    => "Währungen",
        'difficulty'    => "Modus",
        'dispelType'    => "Bannart",
        'duration'      => "Dauer",
        'gameObject'    => "Objekt",
        'gameObjects'   => "Objekte",
        'glyphType'     => "Glyphenart",
        'race'          => "Volk",
        'races'         => "Völker",
        'title'         => "Titel",
        'titles'        => "Titel",
        'eventShort'    => "Ereignis",
        'event'         => "Weltereigniss",
        'events'        => "Weltereignisse",
        'cooldown'      => "%s Abklingzeit",
        'itemset'       => "Ausrüstungsset",
        'itemsets'      => "Ausrüstungssets",
        'mechanic'      => "Auswirkung",
        'mechAbbr'      => "Ausw.",
        'pet'           => "Begleiter",
        'pets'          => "Begleiter",
        'petCalc'       => "Begleiterrechner",
        'requires'      => "Benötigt %s",
        'requires2'     => "Benötigt",
        'reqLevel'      => "Benötigt Stufe %s",
        'reqLevelHlm'   => "Benötigt Stufe %s",
        'reqSkillLevel' => "Benötigte Fertigkeitsstufe",
        'level'         => "Stufe",
        'school'        => "Magieart",
        'spell'         => "Zauber",
        'spells'        => "Zauber",
        'valueDelim'    => " - ",                           // " bis "
        'zone'          => "Zone",
        'zones'         => "Gebiete",
        'expansions'    => array("Classic", "The Burning Crusade", "Wrath of the Lich King"),
        'stats'         => array("Stärke", "Beweglichkeit", "Ausdauer", "Intelligenz", "Willenskraft"),
        'languages'     => array(
            1 => "Orcisch",     2 => "Darnassisch",     3 => "Taurisch",    6 => "Zwergisch",       7 => "Gemeinsprache",   8 => "Dämonisch",       9 => "Titanisch",       10 => "Thalassisch",
            11 => "Drachisch",  12 => "Kalimagisch",    13 => "Gnomisch",   14 => "Trollisch",      33 => "Gossensprache",  35 => "Draeneiisch",    36 => "Zombie",         37 => "Gnomenbinär",        38 => "Goblinbinär"
        ),
        'gl'            => array(null, "Erhebliche", "Geringe"),
        'si'            => array(-2 => "Nur für Horde", -1 => "Nur für Allianz", null, "Allianz", "Horde", "Beide"),
        'resistances'   => array(null, 'Heiligwiderstand', 'Feuerwiderstand', 'Naturwiderstand', 'Frostwiderstand', 'Schattenwiderstand', 'Arkanwiderstand'),
        'sc'            => array("Körperlich", "Heilig", "Feuer", "Natur", "Frost", "Schatten", "Arkan"),
        'dt'            => array(null, "Magie", "Fluch", "Krankheit", "Gift", "Verstohlenheit", "Unsichtbarkeit", null, null, "Wut"),
        'cl'            => array(null, "Krieger", "Paladin", "Jäger", "Schurke", "Priester", "Todesritter", "Schamane", "Magier", "Hexenmeister", null, "Druide"),
        'ra'            => array(-2 => "Horde", -1 => "Allianz", "Beide", "Mensch", "Orc", "Zwerg", "Nachtelf", "Untoter", "Taure", "Gnom", "Troll", null, "Blutelf", "Draenei"),
        'rep'           => array("Hasserfüllt", "Feindselig", "Unfreundlich", "Neutral", "Freundlich", "Wohlwollend", "Respektvoll", "Ehrfürchtig"),
        'st'            => array(
            null,                   "Katzengestalt",                "Baum des Lebens",              "Reisegestalt",                 "Wassergestalt",
            "Bärengestalt",         null,                           null,                           "Terrorbärengestalt",           null,
            null,                   null,                           null,                           "Schattentanz",                 null,
            null,                   "Geisterwolf",                  "Kampfhaltung",                 "Verteidigungshaltung",         "Berserkerhaltung",
            null,                   null,                           "Metamorphosis",                null,                           null,
            null,                   null,                           "Schnelle Fluggestalt",         "Schattengestalt",              "Fluggestalt",
            "Verstohlenheit",       "Mondkingestalt",               "Geist der Erlösung"
        ),
        'me'            => array(
            null,                       "Bezaubert",                "Desorientiert",            "Entwaffnet",               "Abgelenkt",                "Flüchtend",                "Ergriffen",                "Unbeweglich",
            "Befriedet",                "Schweigend",               "Schlafend",                "Verlangsamt",              "Betäubt",                  "Eingefroren",              "Handlungsunfähig",         "Blutend",
            "Heilung",                  "Verwandelt",               "Verbannt",                 "Abgeschirmt",              "Gefesselt",                "Reitend",                  "Verführt",                 "Vertrieben",
            "Entsetzt",                 "Unverwundbar",             "Unterbrochen",             "Benommen",                 "Entdeckung",               "Unverwundbar",             "Kopfnuss",                 "Wütend"
        ),
        'ct'            => array(
            "Nicht kategorisiert",      "Wildtier",                 "Drachkin",                 "Dämon",                    "Elementar",                "Riese",                    "Untoter",                  "Humanoid",
            "Tier",                     "Mechanisch",               "Nicht spezifiziert",       "Totem",                    "Haustier",                 "Gas Wolke"
        ),
        'fa'            => array(
            1 => "Wolf",                2 => "Katze",               3 => "Spinne",              4 => "Bär",                 5 => "Eber",                6 => "Krokilisk",           7 => "Aasvogel",            8 => "Krebs",
            9 => "Gorilla",             11 => "Raptor",             12 => "Weitschreiter",      20 => "Skorpid",            21 => "Schildkröte",        24 => "Fledermaus",         25 => "Hyäne",              26 => "Raubvogel",
            27 => "Windnatter",         30 => "Drachenfalke",       31 => "Felshetzer",         32 => "Sphärenjäger",       33 => "Sporensegler",       34 => "Netherrochen",       35 => "Schlange",           37 => "Motte",
            38 => "Schimäre",           39 => "Teufelssaurier",     41 => "Silithid",           42 => "Wurm",               43 => "Rhinozeros",         44 => "Wespe",              45 => "Kernhund",           46 => "Geisterbestie"
        ),
        'pvpRank'       => array(
            null,                                       "Gefreiter / Späher",                   "Fußknecht / Grunzer",
            "Landsknecht / Waffenträger",               "Feldwebel / Schlachtrufer",            "Fähnrich / Rottenmeister",
            "Leutnant / Steingardist",                  "Hauptmann / Blutgardist",              "Kürassier / Zornbringer",
            "Ritter der Allianz / Klinge der Horde",    "Feldkomandant / Feldherr",             "Rittmeister / Sturmreiter",
            "Marschall / Kriegsherr",                   "Feldmarschall / Kriegsfürst",          "Großmarschall / Oberster Kriegsfürst"
        ),
    ),
    'error' => array(
        'errNotFound'   => "Seite nicht gefunden",
        'errPage'       => "Was? Wie hast du... vergesst es!\n<br>\n<br>\nAnscheinend konnte die von Euch angeforderte Seite nicht gefunden werden. Wenigstens nicht in dieser Dimension.\n<br>\n<br>\nVielleicht lassen einige Justierungen an der\n<span class=\"q4\">\n<ins>[WH-799 Großkonfabulierungsmaschine]</ins>\n</span>\ndie Seite plötzlich wieder auftauchen!\n<div class=\"pad\"></div>\n<div class=\"pad\"></div>\nOder, Ihr könnt es auch \n<a href=\"/?aboutus#contact\">uns melden</a>\n- die Stabilität des WH-799 ist umstritten, und wir möchten gern noch so ein Problem vermeiden...",
        'goStart'       => "Zur <a href=\"index.php\">Titelseite</a> zurückkehren",
        'goForum'       => "<a href=\"?forums&board=1\">Forum</a> für Rückmeldungen",
    ),
    'account' => array(
        'doSignIn'      => "Mit Eurem AoWoW-Konto anmelden",
        'user'          => "Benutzername",
        'pass'          => "Kennwort",
        'rememberMe'    => "Angemeldet bleiben",
        'forgot'        => "Vergessen",
        'accNoneYet'    => "Noch kein Konto",
        'accCreateNow'  => "Jetzt eins erstellen",
        'userNotFound'  => "Ein Konto mit diesem Namen existiert nicht",
        'userBanned'    => "Dieses Konto wurde geschlossen",
        'passMismatch'  => "Die eingegebenen Passwörter stimmen nicht überein",
        'loginsExceeded' => "Die maximale Anzahl an Login-Versuchen von dieser IP wurde überschritten. Bitte versuchen Sie es in %s Minuten noch einmal.",
        'nameInUse'     => "Es existiert bereits ein Konto mit diesem Namen",
        'email'         => "E-Mail-Adresse",
        'unkError'      => "Unbekannter Fehler bei der Accounterstellung",
        'accCreate'     => "Konto erstellen",
        'passConfirm'   => "Passwort bestätigen",
        'signup'        => "Anmelden",
        'requestName'   => "Username Request",
        'resetPass'     => "Password Reset",
        'emailInvalid'  => "Diese E-Mail-Adresse ist ungültig.",
        'emailUnknown'  => "Die E-Mail-Adresse, die Ihr eingegeben habt, ist mit keinem Konto verbunden.<br><br>Falls Ihr die E-Mail-Adresse vergessen habt, mit der Ihr Euer Konto erstellt habt, kontaktiert Ihr bitte feedback@aowow.com für Hilfestellung.",
        'passJustSend'  => "Eine Nachricht mit einem neuen Passwort wurde soeben an %s versandt.",
        'nameJustSend'  => "Eine Nachricht mit Eurem Benutzernamen wurde soeben an %s versandt.",
        'wrongPass'     => "Falsches Passwort",
        'ipAddress'     => "IP-Adresse",
        'lastIP'        => "Letzte bekannte IP",
        'joinDate'      => "Mitglied seit",
        'lastLogin'     => "Letzter Besuch",
        'userGroups'    => "Role",
        'myAccount'     => "Mein Account",
        'editAccount'   => "Benutze die folgenden Formulare um deine Account-Informationen zu aktualisieren",
        'publicDesc'    => "Öffentliche Beschreibung",
        'viewPubDesc'   => "Die Beschreibung in deinem <a href=\"?user=%s\">öffentlichen Profil</a> ansehen",


        // Please_enter_your_confirm_password = Bitte das Passwort bestätigen
        // Please_enter_your_username = Gib bitte deinen Benutzernamen ein
        // Please_enter_your_password = Gib bitte dein Kennwort ein
        // Remember_me_on_this_computer = Auf diesem Computer merken
    ),
    'event' => array(
        'category'      => array("Nicht kategorisiert", "Feiertage", "Wiederkehrend", "Spieler vs. Spieler")
    ),
    'npc'   => array(
        'rank'          => ['Normal', 'Elite', 'Rar Elite', 'Boss', 'Rar']
    ),
    'achievement' => array(
        'criteria'      => "Kriterien",
        'points'        => "Punkte",
        'series'        => "Reihe",
        'outOf'         => "von",
        'criteriaType'  => "Criterium Typ-Id:",
        'itemReward'    => "Ihr bekommt:",
        'titleReward'   => "Euch wird der Titel \"<a href=\"?title=%d\">%s</a>\" verliehen",
        'slain'         => "getötet",
    ),
    'compare' => array(
        'compare'       => "Gegenstandsvergleichswerkzeug",
    ),
    'talent' => array(
        'talentCalc'    => "Talentrechner",
        'petCalc'       => "Begleiterrechner",
        'chooseClass'   => "Wählt eine Klasse",
        'chooseFamily'  => "Wählt eine Tierart",
    ),
    'maps' => array(
        'maps'          => "Karten",
        'linkToThisMap' => "Link zu dieser Karte",
        'clear'         => "Zurücksetzen",
        'EasternKingdoms' => "Östliche Königreiche",
        'Kalimdor'      => "Kalimdor",
        'Outland'       => "Scherbenwelt",
        'Northrend'     => "Nordend",
        'Instances'     => "Instanzen",
        'Dungeons'      => "Dungeons",
        'Raids'         => "Schlachtzüge",
        'More'          => "Weitere",
        'Battlegrounds' => "Schlachtfelder",
        'Miscellaneous' => "Diverse",
        'Azeroth'       => "Azeroth",
        'CosmicMap'     => "Kosmische Karte",
    ),
    'zone' => array(
        // 'zone'          => "Zone",
        // 'zonePartOf'    => "Diese Zone ist Teil der Zone",
        'cat'           => array(
            "Östliche Königreiche",     "Kalimdor",                 "Dungeons",                 "Schlachtzüge",             "Unbenutzt",                null,
            "Schlachtfelder",           null,                       "Scherbenwelt",             "Arenen",                   "Nordend"
        )
    ),
    'quest' => array(
        'level'         => 'Stufe %s',
        'daily'         => 'Täglich',
        'requirements'  => 'Anforderungen'
    ),
    'title' => array(
        'cat'           => array(
            'Allgemein',      'Spieler gegen Spieler',    'Ruf',       'Dungeon & Schlachtzug',     'Quests',       'Berufe',      'Weltereignisse'
        )
    ),
    'currency' => array(
        'cat'           => array(
            1 => "Verschiedenes", 2 => "Spieler gegen Spieler", 4 => "Classic", 21 => "Wrath of the Lich King", 22 => "Dungeon und Schlachtzug", 23 => "Burning Crusade", 41 => "Test", 3 => "Unbenutzt"
        )
    ),
    'pet'      => array(
        'exotic'        => "Exotisch",
        'cat'           => ["Wildheit", "Hartnäckigkeit", "Gerissenheit"]
    ),
    'itemset' => array(
        '_desc'         => "<b>%s</b> ist das <b>%s</b>. Es enthält %s Teile.",
        '_descTagless'  => "<b>%s</b> ist ein Ausrüstungsset, das %s Teile enthält.",
        '_setBonuses'   => "Setboni",
        '_conveyBonus'  => "Das Tragen mehrerer Gegenstände aus diesem Set gewährt Eurem Charakter Boni.",
        '_pieces'       => "Teile",
        '_unavailable'  => "Dieses Ausrüstungsset ist nicht für Spieler verfügbar.",
        '_type'         => "Art",
        '_tag'          => "Tag",

        'notes'         => array(
            null,                                   "Dungeon-Set 1",                            "Dungeon-Set 2",                            "Tier 1 Raid-Set",
            "Tier 2 Raid-Set",                      "Tier 3 Raid-Set",                          "Level 60 PvP-Set (Rar)",                   "Level 60 PvP-Set (Rar,  alt)",
            "Level 60 PvP-Set (Episch)",            "Set der Ruinen von Ahn'Qiraj",             "Set des Tempels von Ahn'Qiraj",            "Set von Zul'Gurub",
            "Tier 4 Raid-Set",                      "Tier 5 Raid-Set",                          "Dungeon-Set 3",                            "Set des Arathibeckens",
            "Level 70 PvP-Set (Rar)",               "Arena-Set Saison 1",                       "Tier 6 Raid-Set",                          "Arena-Set Saison 2",
            "Arena-Set Saison 3",                   "Level 70 PvP-Set 2 (Rar)",                 "Arena-Set Saison 4",                       "Tier 7 Raid-Set",
            "Arena-Set Saison 5",                   "Tier 8 Raid-Set",                          "Arena-Set Saison 6",                       "Tier 9 Raid-Set",
            "Arena-Set Saison 7",                   "Tier 10 Raid-Set",                     "Arena-Set Saison 8"
        ),
        'types'         => array(
            null,               "Stoff",                "Leder",                "Schwere Rüstung",          "Platte",                   "Dolch",                "Ring",
            "Faustwaffe",       "Einhandaxt",           "Einhandstreitkolben",  "Einhandschwert",           "Schmuck",                  "Amulett"
        )
    ),
    'spell' => array(
        '_spellDetails' => "Zauberdetails",
        '_cost'         => "Kosten",
        '_range'        => "Reichweite",
        '_castTime'     => "Zauberzeit",
        '_cooldown'     => "Abklingzeit",
        '_distUnit'     => "Meter",
        '_forms'        => "Gestalten",
        '_aura'         => "Aura",
        '_effect'       => "Effekt",
        '_none'         => "Nichts",
        '_gcd'          => "GCD",
        '_globCD'       => "Globale Abklingzeit",
        '_gcdCategory'  => "GCD-Kategorie",
        '_value'        => "Wert",
        '_radius'       => "Radius",
        '_interval'     => "Interval",
        '_inSlot'       => "im Platz",

        'starter'       => "Basiszauber",
        'trainingCost'  => "Trainingskosten",
        'remaining'     => "Noch %s",
        'untilCanceled' => "bis Abbruch",
        'castIn'        => "Wirken in %s Sek.",
        'instantPhys'   => "Sofort",
        'instantMagic'  => "Spontanzauber",
        'channeled'     => "Kanalisiert",
        'range'         => "%s Meter Reichweite",
        'meleeRange'    => "Nahkampfreichweite",
        'unlimRange'    => "Unbegrenzte Reichweite",
        'reagents'      => "Reagenzien",
        'tools'         => "Extras",
        'home'          => "%lt;Gasthaus&gt;",
        'pctCostOf'     => "vom Grund%s",
        'costPerSec'    => ", plus %s pro Sekunde",
        'costPerLevel'  => ", plus %s pro Stufe",
        'powerRunes'    => ["Frost", "Unheilig", "Blut", "Tod"],
        'powerTypes'    => array(
            -2 => "Gesundheit", -1 => null, "Mana",     "Wut",      "Fokus",    "Energie",      "Zufriedenheit",    "Runen",    "Runenmacht",
            'AMMOSLOT' => "Munition",       'STEAM' => "Dampfdruck",            'WRATH' => "Zorn",                  'PYRITE' => "Pyrit",
            'HEAT' => "Hitze",              'OOZE' => "Schlamm",                'BLOOD_POWER' => "Blutmacht"
        ),
        'relItems'      => array (
            'base'    => "<small>%s im Zusammenhang mit <b>%s</b> anzeigen</small>",
            'link'    => " oder ",
            'recipes' => "<a href=\"?items=9.%s\">Rezeptgegenstände</a>",
            'crafted' => "<a href=\"?items&filter=cr=86;crs=%s\">Hergestellte Gegenstände</a>"
        ),
        'cat'           => array(
              7 => "Klassenfertigkeiten",
            -13 => "Glyphen",
            -11 => array("Sachverstand", 8 => "Rüstung", 6 => "Waffen", 10 => "Sprachen"),
             -4 => "Völkerfertigkeiten",
             -2 => "Talente",
             -6 => "Haustiere",
             -5 => "Reittiere",
             -3 => array(
                "Begleiterfertigkeiten",    782 => "Ghul",              270 => "Allgemein",             213 => "Aasvogel",                  210 => "Bär",                   763 => "Drachenfalke",          211 => "Eber",
                767 => "Felshetzer",        653 => "Fledermaus",        788 => "Geisterbestie",         215 => "Gorilla",                   654 => "Hyäne",                 209 => "Katze",                 787 => "Kernhund",
                214 => "Krebs",             212 => "Krokilisk",         775 => "Motte",                 764 => "Netherrochen",              217 => "Raptor",                655 => "Raubvogel",             786 => "Rhinozeros",
                251 => "Schildkröte",       780 => "Schimäre",          768 => "Schlange",              783 => "Silithid",                  236 => "Skorpid",               766 => "Sphärenjäger",          203 => "Spinne",
                765 => "Sporensegler",      781 => "Teufelssaurier",    218 => "Weitschreiter",         785 => "Wespe",                     656 => "Windnatter",            208 => "Wolf",                  784 => "Wurm",
                204 => "Leerwandler",       205 => "Sukkubus",          189 => "Teufelsjäger",          761 => "Teufelswache",              188 => "Wichtel",
            ),
             -7 => array("Begleitertalente", 410 => "Gerissenheit", 411 => "Wildheit", 409 => "Hartnäckigkeit"),
             11 => array(
                "Berufe",
                171 => "Alchemie",
                164 => array("Schmiedekunst", 9788 => "Rüstungsschmied", 9787 => "Waffenschmied", 17041 => "Axtschmiedemeister", 17040 => "Hammerschmiedemeister", 17039 => "Schwertschmiedemeister"),
                333 => "Verzauberkunst",
                202 => array("Ingenieurskunst", 20219 => "Gnomeningenieurskunst", 20222 => "Gobliningenieurskunst"),
                182 => "Kräuterkunde",
                773 => "Inschriftenkunde",
                755 => "Juwelenschleifen",
                165 => array("Lederverarbeitung", 10656 => "Drachenschuppenlederverarbeitung", 10658 => "Elementarlederverarbeitung", 10660 => "Stammeslederverarbeitung"),
                186 => "Bergbau",
                393 => "Kürschnerei",
                197 => array("Schneiderei", 26798 => "Mondstoffschneiderei", 26801 => "Schattenstoffschneiderei", 26797 => "Zauberfeuerschneiderei"),
            ),
              9 => array("Nebenberufe", 185 => "Kochkunst", 129 => "Erste Hilfe", 356 => "Angeln", 762 => "Reiten"),
             -8 => "NPC-Fähigkeiten",
             -9 => "GM-Fähigkeiten",
              0 => "Nicht kategorisiert"
        ),
        'armorSubClass' => array(
            "Sonstiges",            "Stoffrüstung",     "Lederrüstung",         "Schwere Rüstung",              "Plattenrüstung",
            null,                   "Schilde",          "Buchbände",            "Götzen",                       "Totems",
            "Siegel"
        ),
        'weaponSubClass' => array(
            "Einhandäxte",          "Zweihandäxte",     "Bögen",                "Schusswaffen",                 "Einhandstreitkolben",
            "Zweihandstreitkolben", "Stangenwaffen",    "Einhandschwerter",     "Zweihandschwerter",            null,
            "Stäbe",                null,               null,                   "Faustwaffen",                  "Diverse",
            "Dolche",               "Wurfwaffe",        null,                   "Armbrüste",                    "Zauberstäbe",
            "Angelruten"
        ),
        'subClassMasks' => array(
            0x02A5F3 => 'Nahkampfwaffe',                0x0060 => 'Schild',                         0x04000C => 'Distanzwaffe',                 0xA091 => 'Einhandnahkampfwaffe'
        ),
        'traitShort'    => array(
            'atkpwr'    => "Angr",                      'rgdatkpwr' => "DAngr",                                 'splpwr'    => "ZMacht",
            'arcsplpwr' => "ArkM",                      'firsplpwr' => "FeuM",                                  'frosplpwr' => "FroM",
            'holsplpwr' => "HeiM",                      'natsplpwr' => "NatM",                                  'shasplpwr' => "SchM",
            'splheal'   => "Heil"
        )
    ),
    'item' => array(
        'armor'         => "%s Rüstung",
        'block'         => "%s Blocken",
        'charges'       => "Aufladungen",
        'expend'        => "Verbrauchen",
        'locked'        => "Verschlossen",
        'ratingString'  => "%s&nbsp;@&nbsp;L%s",
        'heroic'        => "Heroisch",
        'unique'        => "Einzigartig",
        'uniqueEquipped'=> "Einzigartig anlegbar",
        'startQuest'    => "Dieser Gegenstand startet eine Quest",
        'bagSlotString' => "%d Platz %s",
        'dps'           => "Schaden pro Sekunde",
        'dps2'          => "Schaden pro Sekunde",
        'addsDps'       => "Adds",
        'fap'           => "Angriffskraft in Tiergestalt",
        'durability'    => "Haltbarkeit",
        'duration'      => "Dauer",
        'realTime'      => "Realzeit",
        'conjured'      => "Herbeigezauberter Gegenstand",
        'damagePhys'    => "%s Schaden",
        'damageMagic'   => "%s %sschaden",
        'speed'         => "Tempo",
        'sellPrice'     => "Verkaufspreis",
        'itemLevel'     => "Gegenstandsstufe",
        'randEnchant'   => "&lt;Zufällige Verzauberung&gt",
        'readClick'     => "&lt;Zum Lesen rechtsklicken&gt",
        'set'           => "Set",
        'socketBonus'   => "Sockelbonus",
        'socket'        => array (
            "Metasockel",           "Roter Sockel",     "Gelber Sockel",        "Blauer Sockel",          -1 => "Prismatischer Sockel"
        ),
        'quality'       => array (
            "Schlecht",             "Verbreitet",       "Selten",               "Rar",
            "Episch",               "Legendär",             "Artefakt",         "Erbstücke",
        ),
        'trigger'       => array (
            "Benutzen: ",           "Anlegen: ",        "Chance bei Treffer: ", null,                           null,
            null,                   null
        ),
        'bonding'       => array (
            "Accountgebunden",                          "Wird beim Aufheben gebunden",                          "Wird beim Anlegen gebunden",
            "Wird bei Benutzung gebunden",              "Seelengebunden",                                       "Questgegenstand"
        ),
        'bagFamily'     => array(
            "Tasche",               "Köcher",           "Munitionsbeutel",      "Seelentasche",                 "Lederertasche",
            "Schreibertasche",      "Kräutertasche",    "Verzauberertasche",    "Ingenieurstasche",             "Schlüssel",
            "Edelsteintasche",      "Bergbautasche"
        ),
        'inventoryType' => array(
            null,                   "Kopf",             "Hals",                 "Schulter",                     "Hemd",
            "Brust",                "Taille",           "Beine",                "Füße",                         "Handgelenke",
            "Hände",                "Finger",           "Schmuck",              "Einhändig",                    "Schildhand",
            "Distanz",              "Rücken",           "Zweihändig",           "Tasche",                       "Wappenrock",
            "Brust",                "Waffenhand",       "Schildhand",           "In der Schildhand geführt",    "Projektil",
            "Wurfwaffe",            "Distanzwaffe",     "Köcher",               "Relikt"
        ),
        'armorSubClass' => array(
            "Sonstiges",            "Stoff",            "Leder",                "Schwere Rüstung",              "Platte",
            null,                   "Schild",           "Buchband",             "Götze",                        "Totem",
            "Sigel"
        ),
        'weaponSubClass' => array(
            "Axt",                  "Axt",              "Bogen",                "Schusswaffe",                  "Streitkolben",
            "Streitkolben",         "Stangenwaffe",     "Schwert",              "Schwert",                      null,
            "Stab",                 null,               null,                   "Faustwaffe",                   "Diverse",
            "Dolche",               "Wurfwaffe",        null,                   "Armbrust",                     "Zauberstab",
            "Angelrute"
        ),
        'projectileSubClass' => array(
            null,                   null,               "Pfeil",                "Kugel",                         null
        ),
        'statType'  => array(
            "Erhöht Euer Mana um %d.",
            "Erhöht Eure Gesundheit um %d.",
            null,
            "Beweglichkeit",
            "Stärke",
            "Intelligenz",
            "Willenskraft",
            "Ausdauer",
            null, null, null, null,
            "Erhöht die Verteidigungswertung um %d.",
            "Erhöht Eure Ausweichwertung um %d.",
            "Erhöht Eure Parierwertung um %d.",
            "Erhöht Eure Blockwertung um %d.",
            "Erhöht Nahkampftrefferwertung um %d.",
            "Erhöht Fernkampftrefferwertung um %d.",
            "Erhöht Zaubertrefferwertung um %d.",
            "Erhöht kritische Nahkampftrefferwertung um %d.",
            "Erhöht kritische Fernkampftrefferwertung um %d.",
            "Erhöht kritische Zaubertrefferwertung um %d.",
            "Erhöht Vermeidungswertung für Nahkampftreffer um +3.",
            "Erhöht Vermeidungswertung für Distanztreffer um %d.",
            "Erhöht Vermeidungswertung für Zaubertreffer um %d.",
            "Erhöht Vermeidungswertung für kritische Nahkampftreffer um %d.",
            "Erhöht Vermeidungswertung für kritische Distanztreffer um %d.",
            "Erhöht Vermeidungswertung für kritische Zaubertreffer um %d.",
            "Erhöht Nahkampftempowertung um %d.",
            "Erhöht Fernkampftempowertung um %d.",
            "Erhöht Zaubertempowertung um %d.",
            "Erhöht Eure Trefferwertung um %d.",
            "Erhöht Eure kritische Trefferwertung um %d.",
            "Erhöht Vermeidungswertung um %d.",
            "Erhöht Vermeidungswertung für kritische Treffer um %d.",
            "Erhöht Eure Abhärtungswertung um %d.",
            "Erhöht Eure Tempowertung um %d.",
            "Erhöht Waffenkundewertung um %d.",
            "Erhöht Angriffskraft um %d.",
            "Erhöht Distanzangriffskraft um %d.",
            "Erhöht die Angriffskraft in Katzen-, Bären-, Terrorbären- und Mondkingestalt um %d.",
            "Erhöht den von Zaubern und Effekten verursachten Schaden um bis zu %d.",
            "Erhöht die von Zaubern und Effekten verursachte Heilung um bis zu %d.",
            "Stellt alle 5 Sek. %d Mana wieder her.",
            "Erhöht Euren Rüstungsdurchschlagwert um %d.",
            "Erhöht die Zaubermacht um %d.",
            "Stellt alle 5 Sek. %d Gesundheit wieder her.",
            "Erhöht den Zauberdurchschlag um %d.",
            "Erhöht Blockwert um %d.",
            "Unbekannter Bonus #%d (%d)",
        )
    ),
    'colon'         => ': '
);

?>
