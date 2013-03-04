<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$lang = array(
    // page variables
    'main' => array(
        'link'          => "Link",
        'signIn'        => "Anmelden",
        'jsError'       => "Stelle bitte sicher, dass JavaScript aktiviert ist.",
        'searchButton'  => "Suche",
        'language'      => "Sprache",
        'numSQL'        => "Anzahl an MySQL-Queries",
        'timeSQL'       => "Zeit für MySQL-Queries",
        'noJScript'     => "<b>Diese Seite macht ausgiebigen Gebrauch von JavaScript.</b><br />Bitte <a href=\"https://www.google.com/support/adsense/bin/answer.py?answer=12654\" target=\"_blank\">aktiviert JavaScript</a> in Eurem Browser.",
        'profiles'      => "Deine Charaktere",
        'links'         => "Links",
        'pageNotFound'  => "Diese|Dieser|Dieses %s existiert nicht.",        // todo: dämliche Fälle...
        'both'          => "Beide",
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
        'name'          => "Name",
        // err_title = Fehler in AoWoW
        // un_err = Gib bitte deinen Benutzernamen ein
        // pwd_err = Gib bitte dein Passwort ein
        // signin_msg = Gib bitte deinen Accountnamen ein
        // c_pwd = Passwort wiederholen
        // create_filter = Filter erstellen
        // loading = Lädt ...
        // soldby = Verkauft von
        // droppedby = Gedroppt von
        // containedinobject = Enthalten in
        // containedinitem = Enthalten in Item
        // contain = Enthält
        // objectiveof = Ziel von
        // rewardof = Belohnung von
        // facts = Übersicht
        // pickpocketingloot = Gestohlen von
        // prospectedfrom = Sondiert aus
        // canbeplacedin = Kann abgelegt werden in
        // minedfromobject = Abgebaut aus
        // gatheredfromobject = Gesammelt von
        // items = Gegenstände
        // objects = Objekte
        // quests = Quests
        // npcs = NPCs
        // drop = Drop
        // starts = Startet
        // ends = Beendet
        // skinning = Kürschnerei
        // pickpocketing = Taschendiebstahl
        // sells = Verkauft
        // reputationwith = Ruf mit der Fraktion
        // experience = Erfahrung
        // uponcompletionofthisquestyouwillgain = Bei Abschluss dieser Quest erhaltet Ihr
        // reagentfor = Reagenz für
        // skinnedfrom = Gekürschnert von
        // disenchanting = Entzaubern
        // This_Object_cant_be_found = Der Standort dieses Objekts ist nicht bekannt.
        // itemsets = Sets
        // Spells = Zauber
        // Items = Gegenstände
        // Quests = Quests
        // Factions = Fraktionen
        // Item_Sets = Sets
        // Compare = Gegenstandsvergleichswerkzeug
        // NPCs = NPCs
        // Objects = Objekte
        // My_account = Mein Account
        // Comments = Kommentare
        // Latest_Comments = Neuste Kommentare
        // day = Tag.
        // hr = Std.
        // min = Min.
        // sec = Sek.
        // Respawn = Respawn
        // Class = Klasse
        // class = Klasse
        // race = Volk
        // Race = Volk
        // Races = Völker

        'disabled'      => "Deaktiviert",
        'disabledHint'  => "Kann nicht erhalten oder abgeschlossen werden.",
        'serverside'    => "Serverseitig",
        'serversideHint' => "Diese Informationen sind nicht im Client enthalten und wurden durch Sniffing zusammengetragen und/oder erraten.",
    ),
    'search' => array(
        'search'        => "Suche",
        'foundResult'   => "Suchergebnisse für",
        'noResult'      => "Keine Ergebnisse für",
        'tryAgain'      => "Bitte versucht es mit anderen Suchbegriffen oder überprüft deren Schreibweise.",
    ),
    'game' => array(
        'alliance'      => "Allianz",
        'horde'         => "Horde",
        'class'         => "Klasse",
        'classes'       => "Klassen",
        'races'         => "Völker",
        'title'         => "Titel",
        'titles'        => "Titel",
        'eventShort'    => "Ereignis",
        'event'         => "Weltereigniss",
        'events'        => "Weltereignisse",
        'cooldown'      => "%s Cooldown",
        'requires'      => "Benötigt",
        'reqLevel'      => "Benötigt Stufe %s",
        'reqLevelHlm'   => "Benötigt Stufe %s",
        'valueDelim'    => " - ",                           // " bis "
        'resistances'   => array(null, 'Heiligwiderstand', 'Feuerwiderstand', 'Naturwiderstand', 'Frostwiderstand', 'Schattenwiderstand', 'Arkanwiderstand'),
        'sc'            => array("Körperlich", "Heilig", "Feuer", "Natur", "Frost", "Schatten", "Arkan"),
        'di'            => array(null, "Magie", "Fluch", "Krankheit", "Gift", "Verstohlenheit", "Unsichtbarkeit", null, null, "Wut"),
        'cl'            => array("UNK_CL0", "Krieger", "Paladin", "Jäger", "Schurke", "Priester", "Todesritter", "Schamane", "Magier", "Hexenmeister", 'UNK_CL10', "Druide"),
        'ra'            => array(-2 => "Horde", -1 => "Allianz", "Beide", "Mensch", "Orc", "Zwerg", "Nachtelf", "Untoter", "Taure", "Gnom", "Troll", 'UNK_RA9', "Blutelf", "Draenei"),
        'rep'           => array("Hasserfüllt", "Feindselig", "Unfreundlich", "Neutral", "Freundlich", "Wohlwollend", "Respektvoll", "Ehrfürchtig"),
        'st'            => array(
            null,               "Katzengestalt",                "Baum des Lebens",              "Reisegestalt",                 "Wassergestalt",
            "Bärengestalt",     null,                           null,                           "Terrorbärengestalt",           null,
            null,               null,                           null,                           "Schattentanz",                 null,
            null,               "Geisterwolf",                  "Kampfhaltung",                 "Verteidigungshaltung",         "Berserkerhaltung",
            null,               null,                           "Metamorphosis",                null,                           null,
            null,               null,                           "Schnelle Fluggestalt",         "Schattengestalt",              "Fluggestalt",
            "Verstohlenheit",   "Mondkingestalt",               "Geist der Erlösung"
        ),
        'pvpRank'       => array(
            null,                                       "Gefreiter / Späher",                   "Fußknecht / Grunzer",
            "Landsknecht / Waffenträger",               "Feldwebel / Schlachtrufer",            "Fähnrich / Rottenmeister",
            "Leutnant / Steingardist",                  "Hauptmann / Blutgardist",              "Kürassier / Zornbringer",
            "Ritter der Allianz / Klinge der Horde",    "Feldkomandant / Feldherr",             "Rittmeister / Sturmreiter",
            "Marschall / Kriegsherr",                   "Feldmarschall / Kriegsfürst",          "Großmarschall / Oberster Kriegsfürst"
        ),
    ),
    'filter' => array(
        'extSearch'     => "Erweiterte Suche",
        'onlyAlliance'  => "Nur für Allianz",
        'onlyHorde'     => "Nur für Horde",
        'addFilter'     => "Weiteren Filter hinzufügen",
        'match'         => "Verwendete Filter",
        'allFilter'     => "Alle Filters",
        'oneFilter'     => "Mindestens einer",
        'applyFilter'   => "Filter anwenden",
        'resetForm'     => "Formular zurücksetzen",
        'refineSearch'  => "Tipp: Präzisiere deine Suche mit Durchsuchen einer <a href=\"javascript:;\" id=\"fi_subcat\">Unterkategorie</a>.",
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
        'viewPublicDesc' => "Die Beschreibung in deinem <a href=\"?user=%s\">öffetnlichen Profil</a> ansehen",


        // Please_enter_your_confirm_password = Bitte das Passwort bestätigen
        // Please_enter_your_username = Gib bitte deinen Benutzernamen ein
        // Please_enter_your_password = Gib bitte dein Kennwort ein
        // Remember_me_on_this_computer = Auf diesem Computer merken
    ),
    'achievement' => array(
        'achievements'  => "Erfolge",
        'criteria'      => "Kriterien",
        'achievement'   => "Erfolg",
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
        'zone'          => "Zone",
        'zonePartOf'    => "Diese Zone ist Teil der Zone",
    ),
    'title' => array(
        'cat'           => array(
            'Allgemein',      'Spieler gegen Spieler',    'Ruf',       'Dungeon & Schlachtzug',     'Quests',       'Berufe',      'Weltereignisse'
        )
    ),
    'spell' => array(
        'remaining'     => "Noch %s",
        'untilCanceled' => "bis Abbruch",
        'castIn'        => "Wirken in %s Sek.",
        'instantPhys'   => "Sofort",
        'instantMagic'  => "Spontanzauber",
        'channeled'     => "Kanalisiert",
        'range'         => "%s Meter Reichweite",
        'meleeRange'    => "Nahkampfreichweite",
        'reagents'      => "Reagenzien",
        'tools'         => "Extras",
        'home'          => "%lt;Gasthaus&gt;",
        'pctCostOf'     => "vom Grund%s",
        'costPerSec'    => ", plus %s pro Sekunde",
        'costPerLevel'  => ", plus %s pro Stufe",
        'powerTypes'    => array(
            -2 => "Gesundheit", -1 => null, "Mana",     "Wut",      "Fokus",    "Energie",      "Zufriedenheit",    "Runen",    "Runenmacht",
            'AMMOSLOT' => "Munnition",      'STEAM' => "Dampfdruck",            'WRATH' => "Zorn",                  'PYRITE' => "Pyrit",
            'HEAT' => "Hitze",              'OOZE' => "Schlamm",                'BLOOD_POWER' => "Blutmacht"
        )
    ),
    'item' => array(
        'armor'         => "Rüstung",
        'block'         => "Blocken",
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
        'armorSubclass' => array(
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
            "Erhöht die Angriffskraft in Katzen-, Bären- oder Mondkingestalt um %d.",
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
    )
);

?>
