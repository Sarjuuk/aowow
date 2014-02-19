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
    'timeUnits' => array(
        'sg'            => ["Jahr",  "Monat",  "Woche",  "Tag",   "Stunde",  "Minute",  "Sekunde",  "Millisekunde"],
        'pl'            => ["Jahre", "Monate", "Wochen", "Tage",  "Stunden", "Minuten", "Sekunden", "Millisekunden"],
        'ab'            => ["J.",    "M.",     "W.",     "Tag",   "Std.",    "Min.",    "Sek.",     "Ms."],
    ),
    'main' => array(
        'help'          => "Hilfe",
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
        'players'       => "Spieler",
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
        'clear'         => "leeren",
        'exactMatch'    => "Exakt passend",
        '_reqLevel'     => "Mindeststufe",

        // infobox
        'unavailable'   => "Nicht für Spieler verfügbar",
        'disabled'      => "Deaktiviert",
        'disabledHint'  => "Kann nicht erhalten oder abgeschlossen werden.",
        'serverside'    => "Serverseitig",
        'serversideHint' => "Diese Informationen sind nicht im Client enthalten und wurden gesnifft und/oder erraten.",

        // red buttons
        'links'         => "Links",
        'compare'       => "Vergleichen",
        'view3D'        => "3D-Ansicht",
        'findUpgrades'  => "Bessere Gegenstände finden...",

        // miscTools
        'subscribe'     => "Abonnieren",
        'mostComments'  => ["Gestern", "Vergangene %d Tage"],
        'utilities'     => array(
            "Neueste Ergänzungen",                  "Neueste Artikel",                      "Neueste Kommentare",                   "Neueste Screenshots",                  null,
            "Nicht bewertete Kommentare",           11 => "Neueste Videos",                 12 => "Meiste Kommentare",              13 => "Fehlende Screenshots"
        ),

        // article & infobox
        'englishOnly'   => "Diese Seite ist nur in <b>Englisch</b> verfügbar.",

        // calculators
        'preset'        => "Vorlage",
        'addWeight'     => "Weitere Gewichtung hinzufügen",
        'createWS'      => "Gewichtungsverteilung erstellen",
        'jcGemsOnly'    => "<span%s>JS-exklusive</span> Edelsteine einschließen",
        'cappedHint'    => 'Tipp: <a href="javascript:;" onclick="fi_presetDetails();">Entfernt</a> Gewichtungen für gedeckte Werte wie Trefferwertung.',
        'groupBy'       => "Ordnen nach",
        'gb'            => array(
            ['Nichts', 'none'],         ['Platz', 'slot'],      ['Stufe', 'level'],         ['Quelle', 'source']
        ),
        'compareTool'   => "Gegenstandsvergleichswerkzeug",
        'talentCalc'    => "Talentrechner",
        'petCalc'       => "Begleiterrechner",
        'chooseClass'   => "Wählt eine Klasse",
        'chooseFamily'  => "Wählt eine Tierart"
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
        'faction'       => "Fraktion",
        'factions'      => "Fraktionen",
        'cooldown'      => "%s Abklingzeit",
        'item'          => "Gegenstand",
        'items'         => "Gegenstände",
        'itemset'       => "Ausrüstungsset",
        'itemsets'      => "Ausrüstungssets",
        'mechanic'      => "Auswirkung",
        'mechAbbr'      => "Ausw.",
        'meetingStone'  => "Versammlungsstein",
        'npc'           => "NPC",
        'npcs'          => "NPCs",
        'pet'           => "Begleiter",
        'pets'          => "Begleiter",
        'profile'       => "",
        'profiles'      => "Profile",
        'quest'         => "Quest",
        'quests'        => "Quests",
        'requires'      => "Benötigt %s",
        'requires2'     => "Benötigt",
        'reqLevel'      => "Benötigt Stufe %s",
        'reqLevelHlm'   => "Benötigt Stufe %s",
        'reqSkillLevel' => "Benötigte Fertigkeitsstufe",
        'level'         => "Stufe",
        'school'        => "Magieart",
        'skill'         => "Fertigkeit",
        'skills'        => "Fertigkeiten",
        'spell'         => "Zauber",
        'spells'        => "Zauber",
        'type'          => "Art",
        'valueDelim'    => " - ",                           // " bis "
        'zone'          => "Zone",
        'zones'         => "Gebiete",

        'heroClass'     => "Heldenklasse",
        'resource'      => "Ressource",
        'resources'     => "Ressourcen",
        'role'          => "Rolle",
        'roles'         => "Rollen",
        'specs'         => "Spezialisierungen",
        '_roles'        => ['Heiler', 'Nahkampf-DPS', 'Distanz-DPS', 'Tank'],

        'modes'         => ['Normal / Normal 10', 'Heroisch / Normal 25', 'Heroisch 10', 'Heroisch 25'],
        'expansions'    => array("Classic", "The Burning Crusade", "Wrath of the Lich King"),
        'stats'         => array("Stärke", "Beweglichkeit", "Ausdauer", "Intelligenz", "Willenskraft"),
        'languages'     => array(
            1 => "Orcisch",     2 => "Darnassisch",     3 => "Taurisch",    6 => "Zwergisch",       7 => "Gemeinsprache",   8 => "Dämonisch",       9 => "Titanisch",       10 => "Thalassisch",
            11 => "Drachisch",  12 => "Kalimagisch",    13 => "Gnomisch",   14 => "Trollisch",      33 => "Gossensprache",  35 => "Draeneiisch",    36 => "Zombie",         37 => "Gnomenbinär",        38 => "Goblinbinär"
        ),
        'gl'            => array(null, "Erhebliche", "Geringe"),
        'si'            => array(1 => "Allianz", -1 => "Nur für Allianz", 2 => "Horde", -2 => "Nur für Horde", 3 => "Beide"),
        'resistances'   => array(null, 'Heiligwiderstand', 'Feuerwiderstand', 'Naturwiderstand', 'Frostwiderstand', 'Schattenwiderstand', 'Arkanwiderstand'),
        'sc'            => array("Körperlich", "Heilig", "Feuer", "Natur", "Frost", "Schatten", "Arkan"),
        'dt'            => array(null, "Magie", "Fluch", "Krankheit", "Gift", "Verstohlenheit", "Unsichtbarkeit", null, null, "Wut"),
        'cl'            => array(null, "Krieger", "Paladin", "Jäger", "Schurke", "Priester", "Todesritter", "Schamane", "Magier", "Hexenmeister", null, "Druide"),
        'ra'            => array(-2 => "Horde", -1 => "Allianz", "Beide", "Mensch", "Orc", "Zwerg", "Nachtelf", "Untoter", "Taure", "Gnom", "Troll", null, "Blutelf", "Draenei"),
        'rep'           => array("Hasserfüllt", "Feindselig", "Unfreundlich", "Neutral", "Freundlich", "Wohlwollend", "Respektvoll", "Ehrfürchtig"),
        'st'            => array(
            "Vorgabe",              "Katzengestalt",                "Baum des Lebens",              "Reisegestalt",                 "Wassergestalt",
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
            "Tier",                     "Mechanisch",               "Nicht spezifiziert",       "Totem",                    "Haustier",                 "Gaswolke"
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
    ),
    'gameObject' => array(
        'cat'           => [0 => "Anderes", 9 => "Bücher", 3 => "Behälter", -5 => "Truhen", 25 => "Fischschwärme", -3 => "Kräuter", -4 => "Erzadern",     -2 => "Quest", -6 => "Werkzeuge"],
        'type'          => [                9 => "Buch",   3 => "Behälter", -5 => "Truhe",  25 => "",              -3 => "Kraut",   -4 => "Erzvorkommen", -2 => "Quest", -6 => ""],
        'unkPosition'   => "Der Standort dieses Objekts ist nicht bekannt.",
        'key'           => "Schlüssel",
        'focus'         => "Zauberfokus",
        'focusDesc'     => "Zauber, die diesen Fokus benötigen, können an diesem Objekt gewirkt werden.",
        'trap'          => "Falle",
        'triggeredBy'   => "Ausgelöst durch",
        'capturePoint'  => "Eroberungspunkt"
    ),
    'npc'   => array(
        'classification'=> "Einstufung",
        'petFamily'     => "Tierart",
        'react'         => "Reaktion",
        'worth'         => "Wert",
        'unkPosition'   => "Der Aufenthaltsort dieses NPCs ist nicht bekannt.",
        'difficultyPH'  => "Dieser NPC ist ein Platzhalter für einen anderen Modus von",
        'quotes'        => "Zitate",
        'gainsDesc'     => "Nach dem Töten dieses NPCs erhaltet Ihr",
        'repWith'       => "Ruf mit der Fraktion",
        'stopsAt'       => "Stoppt bei %s",
        'vehicle'       => "Fahrzeug",
        'stats'         => "Werte",
        'melee'         => "Nahkampf",
        'ranged'        => "Fernkampf",
        'armor'         => "Rüstung",
        'rank'          => [0 => "Normal", 1 => "Elite", 4 => "Rar", 2 => "Rar Elite", 3 => "Boss"],
        'textTypes'     => [null, "schreit", "sagt", "flüstert"],
        'modes'         => array(
            1 => ["Normal", "Heroisch"],
            2 => ["10-Spieler Normal", "25-Spieler Normal", "10-Spieler Heroisch", "25-Spieler Heroisch"]
        ),
        'cat'           => array(
            "Nicht kategorisiert",      "Wildtiere",                "Drachkin",                 "Dämonen",                  "Elementare",               "Riesen",                   "Untote",                   "Humanoide",
            "Tiere",                    "Mechanisch",               "Nicht spezifiziert",       "Totems",                   "Haustiere",                "Gaswolken"
        )
    ),
    'event' => array(
        'start'         => "Anfang",
        'end'           => "Ende",
        'interval'      => "Intervall",
        'inProgress'    => "Ereignis findet gerade statt",
        'category'      => array("Nicht kategorisiert", "Feiertage", "Wiederkehrend", "Spieler vs. Spieler")
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
        'reqNumCrt'     => "Benötigt"
    ),
    'class' => array(
        'racialLeader'  => "Volksanführer",
        'startZone'     => "Startgebiet",
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
        'questLevel'    => 'Stufe %s',
        'daily'         => 'Täglich',
        'requirements'  => 'Anforderungen',
        'questInfo'     => array(
              0 => 'Normal',             1 => 'Gruppe',             21 => 'Leben',              41 => 'PvP',                62 => 'Schlachtzug',        81 => 'Dungeon',            82 => 'Weltereignis',
             83 => 'Legendär',          84 => 'Eskorte',            85 => 'Heroisch',           88 => 'Schlachtzug (10)',   89 => 'Schlachtzug (25)'
        )
    ),
    'title' => array(
        'cat'           => array(
            'Allgemein',      'Spieler gegen Spieler',    'Ruf',       'Dungeon & Schlachtzug',     'Quests',       'Berufe',      'Weltereignisse'
        )
    ),
    'skill' => array(
        'cat'           => array(
            -6 => 'Haustiere',          -5 => 'Reittiere',          -4 => 'Völkerfertigkeiten', 5 => 'Attribute',           6 => 'Waffenfertigkeiten',  7 => 'Klassenfertigkeiten', 8 => 'Rüstungssachverstand',
             9 => 'Nebenberufe',        10 => 'Sprachen',           11 => 'Berufe'
        )
    ),
    'currency' => array(
        'cap'           => "Obergrenze",
        'cat'           => array(
            1 => "Verschiedenes", 2 => "Spieler gegen Spieler", 4 => "Classic", 21 => "Wrath of the Lich King", 22 => "Dungeon und Schlachtzug", 23 => "Burning Crusade", 41 => "Test", 3 => "Unbenutzt"
        )
    ),
    'pet'      => array(
        'exotic'        => "Exotisch",
        'cat'           => ["Wildheit", "Hartnäckigkeit", "Gerissenheit"]
    ),
    'faction' => array(
        'spillover'     => "Reputationsüberlauf",
        'spilloverDesc' => "Für diese Fraktion erhaltener Ruf wird zusätzlich mit den unten aufgeführten Fraktionen anteilig verrechnet.",
        'maxStanding'   => "Max. Ruf",
        'quartermaster' => "Rüstmeister",
        'cat'           => array(
            1118 => ["Classic", 469 => "Allianz", 169 => "Dampfdruckkartell", 67 => "Horde", 891 => "Streitkräfte der Allianz", 892 => "Streitkräfte der Horde"],
            980  => ["The Burning Crusade", 936 => "Shattrath"],
            1097 => ["Wrath of the Lich King", 1052 => "Expedition der Horde", 1117 => "Sholazarbecken", 1037 => "Vorposten der Allianz"],
            0    => "Sonstige"
        )
    ),
    'itemset' => array(
        '_desc'         => "<b>%s</b> ist das <b>%s</b>. Es enthält %s Teile.",
        '_descTagless'  => "<b>%s</b> ist ein Ausrüstungsset, das %s Teile enthält.",
        '_setBonuses'   => "Setboni",
        '_conveyBonus'  => "Das Tragen mehrerer Gegenstände aus diesem Set gewährt Eurem Charakter Boni.",
        '_pieces'       => "Teile",
        '_unavailable'  => "Dieses Ausrüstungsset ist nicht für Spieler verfügbar.",
        '_tag'          => "Tag",
        'summary'       => "Zusammenfassung",
        'notes'         => array(
            null,                                   "Dungeon-Set 1",                            "Dungeon-Set 2",                                "Tier 1 Raid-Set",
            "Tier 2 Raid-Set",                      "Tier 3 Raid-Set",                          "Level 60 PvP-Set (Rar)",                   "Level 60 PvP-Set (Rar,  alt)",
            "Level 60 PvP-Set (Episch)",            "Set der Ruinen von Ahn'Qiraj",             "Set des Tempels von Ahn'Qiraj",            "Set von Zul'Gurub",
            "Tier 4 Raid-Set",                      "Tier 5 Raid-Set",                          "Dungeon-Set 3",                            "Set des Arathibeckens",
            "Level 70 PvP-Set (Rar)",               "Arena-Set Saison 1",                       "Tier 6 Raid-Set",                          "Arena-Set Saison 2",
            "Arena-Set Saison 3",                   "Level 70 PvP-Set 2 (Rar)",                 "Arena-Set Saison 4",                       "Tier 7 Raid-Set",
            "Arena-Set Saison 5",                   "Tier 8 Raid-Set",                          "Arena-Set Saison 6",                       "Tier 9 Raid-Set",
            "Arena-Set Saison 7",                   "Tier 10 Raid-Set",                         "Arena-Set Saison 8"
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
        '_collapseAll'  => "Alle einklappen",
        '_expandAll'    => "Alle ausklappen",

        'discovered'    => "Durch Geistesblitz erlernt",
        'ppm'           => "%s Auslösungen pro Minute",
        'procChance'    => "Procchance",
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
        '_scaling'      => "Skalierung",
        'scaling'       => array(
            'directSP' => "+%.2f%% der Zaubermacht zum direkten Effekt",         'directAP' => "+%.2f%% der Angriffskraft zum direkten Effekt",
            'dotSP'    => "+%.2f%% der Zaubermacht pro Tick",                    'dotAP'    => "+%.2f%% der Angriffskraft pro Tick"
        ),
        'powerRunes'    => ["Frost", "Unheilig", "Blut", "Tod"],
        'powerTypes'    => array(
            -2 => "Gesundheit", -1 => null, "Mana",     "Wut",      "Fokus",    "Energie",      "Zufriedenheit",    "Runen",    "Runenmacht",
            'AMMOSLOT' => "Munition",       'STEAM' => "Dampfdruck",            'WRATH'       => "Zorn",            'PYRITE' => "Pyrit",
            'HEAT'     => "Hitze",          'OOZE'  => "Schlamm",               'BLOOD_POWER' => "Blutmacht"
        ),
        'relItems'      => array (
            'base'    => "<small>%s im Zusammenhang mit <b>%s</b> anzeigen</small>",
            'link'    => " oder ",
            'recipes' => "<a href=\"?items=9.%s\">Rezeptgegenstände</a>",
            'crafted' => "<a href=\"?items&filter=cr=86;crs=%s;crv=0\">Hergestellte Gegenstände</a>"
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
            "Sonstiges",                            "Stoffrüstung",                         "Lederrüstung",                         "Schwere Rüstung",                      "Plattenrüstung",
            null,                                   "Schilde",                              "Buchbände",                            "Götzen",                               "Totems",
            "Siegel"
        ),
        'weaponSubClass' => array(
            15 => "Dolche",                          0 => "Einhandäxte",                     7 => "Einhandschwerter",               4 => "Einhandstreitkolben",            13 => "Faustwaffen",
             6 => "Stangenwaffen",                  10 => "Stäbe",                           1 => "Zweihandäxte",                   8 => "Zweihandschwerter",               5 => "Zweihandstreitkolben",
            18 => "Armbrüste",                       2 => "Bögen",                           3 => "Schusswaffen",                  16 => "Wurfwaffen",                     19 => "Zauberstäbe",
            20 => "Angelruten",                     14 => "Diverse"
        ),
        'subClassMasks' => array(
            0x02A5F3 => 'Nahkampfwaffe',            0x0060 => 'Schild',                     0x04000C => 'Distanzwaffe',             0xA091 => 'Einhandnahkampfwaffe'
        ),
        'traitShort'    => array(
            'atkpwr'    => "Angr",                  'rgdatkpwr' => "DAngr",                 'splpwr'    => "ZMacht",                'arcsplpwr' => "ArkM",                  'firsplpwr' => "FeuM",
            'frosplpwr' => "FroM",                  'holsplpwr' => "HeiM",                  'natsplpwr' => "NatM",                  'shasplpwr' => "SchM",                  'splheal'   => "Heil"
        ),
        'spellModOp'    => array(
            "Schaden",                              "Dauer",                                "Bedrohung",                            "Effekt 1",                             "Aufladungen",
            "Reichweite",                           "Radius",                               "kritische Trefferchance",              "Alle Effekte",                         "Zauberzeitverlust",
            "Zauberzeit",                           "Abklingzeit",                          "Effekt 2",                             "Ignoriere Rüstung",                    "Kosten",
            "Kritischer Bonusschaden",              "Chance auf Fehlschlag",                "Sprung-Ziele",                         "Chance auf Auslösung",                 "Intervall",
            "Multiplikator (Schaden)",              "Globale Abklingzeit",                  "Schaden über Zeit",                    "Effekt 3",                             "Multiplikator (Bonus)",
            null,                                   "Auslösungen pro Minute",               "Multiplikator (Betrag)",               "Widerstand gegen Bannung",             "kritischer Bonusschaden2",
            "Kostenrückerstattung bei Fehlschlag"
        ),
        'combatRating'  => array(
            "Waffenfertigkeit",                     "Verteidigungsfertigkeit",              "Ausweichen",                           "Parrieren",                            "Blocken",
            "Nahkampftrefferchance",                "Fernkampftrefferchance",               "Zaubertrefferchance",                  "kritische Nahkampftrefferchance",      "kritische Fernkampftrefferchance",
            "kritische Zaubertrefferchance",        "erhaltene Nahkampftreffer",            "erhaltene Fernkampftreffer",           "erhaltene Zaubertreffer",              "erhaltene kritische Nahkampftreffer",
            "erhaltene kritische Fernkampftreffer", "erhaltene kritische Zaubertreffer",    "Nahkampftempo",                        "Fernkampftempo",                       "Zaubertempo",
            "Waffenfertigkeit Haupthand",           "Waffenfertigkeit Nebenhand",           "Waffenfertigkeit Fernkampf",           "Waffenkunde",                          "Rüstungsdurchschlag"
        ),
        'lockType'      => array(
            null,                                   "Schlossknacken",                       "Kräuterkunde",                         "Bergbau",                              "Falle entschärfen",
            "Öffnen",                               "Schatz (DND)",                         "Verkalkte Elfenedelsteine (DND)",      "Schließen",                            "Falle scharf machen",
            "Schnell öffnen",                       "Schnell schließen",                    "Offenes Tüfteln",                      "Offenes Knien",                        "Offenes Angreifen",
            "Gahz'ridian (DND)",                    "Schlagen",                             "PvP öffnen",                           "PvP schließen",                        "Angeln",
            "Inschriftenkunde",                     "Vom Fahrzeug öffnen"
        ),
        'stealthType'   => ["Allgemein", "Falle"],
        'invisibilityType' => ["Allgemein", 3 => "Falle", 6 => "Trunkenheit"]
    ),
    'item' => array(
        'armor'         => "%s Rüstung",
        'block'         => "%s Blocken",
        'charges'       => "Aufladungen",
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
        'realTime'      => "Realzeit",
        'conjured'      => "Herbeigezauberter Gegenstand",
        'damagePhys'    => "%s Schaden",
        'damageMagic'   => "%s %sschaden",
        'speed'         => "Tempo",
        'sellPrice'     => "Verkaufspreis",
        'itemLevel'     => "Gegenstandsstufe",
        'randEnchant'   => "&lt;Zufällige Verzauberung&gt",
        'readClick'     => "&lt;Zum Lesen rechtsklicken&gt",
        'openClick'     => "&lt;Zum Öffnen rechtsklicken&gt",
        'set'           => "Set",
        'partyLoot'     => "Gruppenloot",
        'smartLoot'     => "Intelligente Beuteverteilung",
        'indestructible'=> "Kann nicht zerstört werden",
        'deprecated'    => "Nicht benutzt",
        'useInShape'    => "Benutzbar in Gestaltwandlung",
        'useInArena'    => "Benutzbar in Arenen",
        'refundable'    => "Rückzahlbar",
        'noNeedRoll'    => "Kann nicht für Bedarf werfen",
        'atKeyring'     => "Passt in den Schlüsselbund",
        'worth'         => "Wert",
        'consumable'    => "Verbrauchbar",
        'nonConsumable' => "Nicht verbrauchbar",
        'accountWide'   => "Accountweit",
        'millable'      => "Mahlbar",
        'noEquipCD'     => "Keine Anlegabklingzeit",
        'prospectable'  => "Sondierbar",
        'disenchantable'=> "Kann entzaubert werden",
        'cantDisenchant'=> "Kann nicht entzaubert werden",
        'repairCost'    => "Reparaturkosten",
        'tool'          => "Werkzeug",
        'cost'          => "Preis",
        'content'       => "Inhalt",
        '_transfer'     => 'Dieser Gegenstand wird mit <a href="?item=%d" class="q%d icontiny tinyspecial" style="background-image: url(images/icons/tiny/%s.gif)">%s</a> vertauscht, wenn Ihr zur <span class="%s-icon">%s</span> wechselt.',
        '_unavailable'  => "Dieser Gegenstand ist nicht für Spieler verfügbar.",
        '_rndEnchants'  => "Zufällige Verzauberungen",
        '_chance'       => "(Chance von %s%%)",
        'reqRating'     => "Benötigt eine persönliche Arenawertung und Teamwertung von %d.",
        'slot'          => "Platz",
        '_quality'      => "Qualität",
        'usableBy'      => "Benutzbar von",
        'buyout'        => "Sofortkaufpreis",
        'each'          => "Stück",
        'gems'          => "Edelsteine",
        'socketBonus'   => "Sockelbonus",
        'socket'        => array (
            "Metasockel",           "Roter Sockel",     "Gelber Sockel",        "Blauer Sockel",          -1 => "Prismatischer Sockel"
        ),
        'quality'       => array (
            "Schlecht",             "Verbreitet",       "Selten",               "Rar",
            "Episch",               "Legendär",         "Artefakt",             "Erbstücke",
        ),
        'trigger'       => array (
            "Benutzen: ",           "Anlegen: ",        "Chance bei Treffer: ", null,                           null,
            null,                   null
        ),
        'bonding'       => array (
            "Accountgebunden",                          "Wird beim Aufheben gebunden",                          "Wird beim Anlegen gebunden",
            "Wird bei Benutzung gebunden",              "Questgegenstand",                                      "Questgegenstand"
        ),
        'bagFamily'     => array(
            "Tasche",               "Köcher",           "Munitionsbeutel",      "Seelentasche",                 "Lederertasche",
            "Schreibertasche",      "Kräutertasche",    "Verzauberertasche",    "Ingenieurstasche",             null, /*Schlüssel*/
            "Edelsteintasche",      "Bergbautasche"
        ),
        'inventoryType' => array(
            null,                   "Kopf",             "Hals",                 "Schulter",                     "Hemd",
            "Brust",                "Taille",           "Beine",                "Füße",                         "Handgelenke",
            "Hände",                "Finger",           "Schmuck",              "Einhändig",                    "Schildhand", /*Schild*/
            "Distanz",              "Rücken",           "Zweihändig",           "Tasche",                       "Wappenrock",
            null, /*Robe*/          "Waffenhand",       "Schildhand",           "In der Schildhand geführt",    "Projektil",
            "Wurfwaffe",            null, /*Ranged2*/   "Köcher",               "Relikt"
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
        'elixirType'    => [null, "Kampf", "Wächter"],
        'cat'           => array(
             2 => "Waffen",                                 // self::$spell['weaponSubClass']
             4 => array("Rüstung", array(
                 1 => "Stoffrüstung",                2 => "Lederrüstung",            3 => "Schwere Rüstung",         4 => "Plattenrüstung",          6 => "Schilde",                 7 => "Buchbände",
                 8 => "Götzen",                      9 => "Totems",                 10 => "Siegel",                 -6 => "Umhänge",                -5 => "Nebenhandgegenstände",   -8 => "Hemden",
                -7 => "Wappenröcke",                -3 => "Amulette",               -2 => "Ringe",                  -4 => "Schmuckstücke",           0 => "Verschiedenes (Rüstung)",
            )),
             1 => array("Behälter", array(
                 0 => "Taschen",                     3 => "Verzauberertaschen",      4 => "Ingenieurstaschen",       5 => "Edelsteintaschen",        2 => "Kräutertaschen",          8 => "Schreibertaschen",
                 7 => "Lederertaschen",              6 => "Bergbautaschen",          1 => "Seelentaschen"
            )),
             0 => array("Verbrauchbar", array(
                -3 => "Gegenstandsverzauberungen (Temporäre)",                       6 => "Gegenstandsverzauberungen (Dauerhafte)",                  2 => ["Elixire", [1 => "Kampfelixire", 2 => "Wächterelixire"]],
                 1 => "Tränke",                      4 => "Schriftrollen",           7 => "Verbände",                0 => "Verbrauchbar",            3 => "Fläschchen",              5 => "Essen & Trinken",
                 8 => "Andere (Verbrauchbar)"
            )),
            16 => array("Glyphen", array(
                 1 => "Kriegerglyphen",              2 => "Paladinglyphen",          3 => "Jägerglyphen",            4 => "Schurkenglyphen",         5 => "Priesterglyphen",         6 => "Todesritterglyphen",
                 7 => "Schamanenglyphen",            8 => "Magierglyphen",           9 => "Hexenmeisterglyphen",    11 => "Druidenglyphen"
            )),
             7 => array("Handwerkswaren", array(
                14 => "Rüstungsverzauberungen",      5 => "Stoff",                   3 => "Geräte",                 10 => "Elementar",              12 => "Verzauberkunst",          2 => "Sprengstoff",
                 9 => "Kräuter",                     4 => "Juwelenschleifen",        6 => "Leder",                  13 => "Materialien",             8 => "Fleisch",                 7 => "Metall & Stein",
                 1 => "Teile",                      15 => "Waffenverzauberungen",   11 => "Andere (Handwerkswaren)"
             )),
             6 => ["Projektile", [                   2 => "Pfeile",                  3 => "Kugeln"          ]],
            11 => ["Köcher",     [                   2 => "Köcher",                  3 => "Munitionsbeutel" ]],
             9 => array("Rezepte", array(
                 0 => "Bücher",                      6 => "Alchemierezepte",         4 => "Schmiedekunstpläne",      5 => "Kochrezepte",             8 => "Verzauberkunstformeln",   3 => "Ingenieurschemata",
                 7 => "Erste Hilfe-Bücher",          9 => "Angelbücher",            11 => "Inschriftenkundetechniken",10 => "Juwelenschleifen-Vorlagen",1 => "Lederverarbeitungsmuster",12 => "Bergbauleitfäden",
                 2 => "Schneidereimuster"
            )),
             3 => array("Edelsteine", array(
                 6 => "Meta-Edelsteine",             0 => "Rote Edelsteine",         1 => "Blaue Edelsteine",        2 => "Gelbe Edelsteine",        3 => "Violette Edelsteine",     4 => "Grüne Edelsteine",
                 5 => "Orange Edelsteine",           8 => "Prismatische Edelsteine", 7 => "Einfache Edelsteine"
            )),
            15 => array("Verschiedenes", array(
                -2 => "Rüstungsmarken",              3 => "Feiertag",                0 => "Plunder",                 1 => "Reagenzien",              5 => "Reittiere",              -7 => "Flugtiere",
                 2 => "Haustiere",                   4 => "Andere (Verschiedenes)"
            )),
            10 => "Währung",
            12 => "Quest",
            13 => "schlüssel",
        ),
        'statType'      => array(
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
    'colon'             => ': ',
    'dateFmtShort'      => "d.m.Y",
    'dateFmtLong'       => "d.m.Y \u\m H:i"
);

?>
