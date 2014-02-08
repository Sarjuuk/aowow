<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/*
    some translations have yet to be taken from or improved by the use of:
    <path>\World of Warcraft\Data\esES\patch-esES-3.MPQ\Interface\FrameXML\GlobalStrings.lua
    like: ITEM_MOD_*, POWER_TYPE_*, ITEM_BIND_*, PVP_RANK_*
*/

$lang = array(
    // page variables
    'timeUnits' => array(
        'sg'            => ["año",  "mes",   "semana",  "día",  "hora",  "minuto",  "segundo",  "milisegundo"],
        'pl'            => ["años", "meses", "semanas", "dias", "horas", "minutos", "segundos", "milisegundos"],
        'ab'            => ["año",  "mes",   "sem",     "",     "h",     "min",     "seg",      "ms"],
    ),
    'main' => array(
        'help'          => "Ayuda",
        'name'          => "nombre",
        'link'          => "Enlace",
        'signIn'        => "Iniciar sesión",
        'jsError'       => "Por favor, asegúrese de que ha habilitado javascript.",
        'searchButton'  => "búsqueda",
        'language'      => "lengua",
        'numSQL'        => "Número de consultas de MySQL",
        'timeSQL'       => "El tiempo para las consultas de MySQL",
        'noJScript'     => "<b>Este sitio hace uso intenso de JavaScript.</b><br />Por favor <a href=\"https://www.google.com/support/adsense/bin/answer.py?answer=12654\" target=\"_blank\">habilita JavaScript</a> en tu navegador.",
        'profiles'      => "Tus personajes",    // translate.google :x
        'pageNotFound'  => "Este %s no existe.",
        'gender'        => "Género",
        'sex'           => [null, 'Hombre', 'Mujer'],
        'quickFacts'    => "Notas rápidas",
        'screenshots'   => "Capturas de pantalla",
        'videos'        => "Videos",
        'side'          => "Lado",
        'related'       => "Información relacionada",
        'contribute'    => "Contribuir",
        // 'replyingTo'    => "The answer to a comment from",
        'submit'        => "Enviar",
        'cancel'        => "Cancelar",
        'rewards'       => "Recompensas",
        'gains'         => "Ganancias",
        'login'         => "[Login]",
        'forum'         => "Foro",
        'n_a'           => "n/d",

        // filter
        'extSearch'     => "Extender búsqueda",
        'addFilter'     => "Añadir otro filtro",
        'match'         => "Aplicar",
        'allFilter'     => "Todos los filtros",
        'oneFilter'     => "Por lo menos uno",
        'applyFilter'   => "Aplicar filtro",
        'resetForm'     => "Reiniciar formulario",
        'refineSearch'  => "Sugerencia: Refina tu búsqueda llendo a una <a href=\"javascript:;\" id=\"fi_subcat\">subcategoría</a>.",
        'clear'         => "borrar",
        'exactMatch'    => "Coincidencia exacta",

        // infobox
        'unavailable'   => "No está disponible a los jugadores",
        'disabled'      => "[Disabled]",
        'disabledHint'  => "[Cannot be attained or completed]",
        'serverside'    => "[Serverside]",
        'serversideHint' => "[These informations are not in the Client and have been provided by sniffing and/or guessing.]",

        // red buttons
        'links'         => "Enlaces",
        'compare'       => "Comparar",
        'view3D'        => "Ver en 3D",
        'findUpgrades'  => "Buscar mejoras...",

        // misc Tools
        'subscribe'     => "Suscribirme",
        'mostComments'  => ["Ayer", "Pasados %d días"],
        'utilities'     => array(
            "Últimas adiciones",                    "Últimos artículos",                    "Últimos comentarios",                  "Últimas capturas de pantalla",         null,
            "Comentarios sin valorar",              11 => "Últimos vídeos",                 12 => "Mayoría de comentarios",         13 => "Capturas de pantalla faltantes"
        ),

        // article & infobox
        'englishOnly'   => "Esta página sólo está disponible en <b>inglés</b>.",

        // calculators
        'preset'        => "Predet.",
        'addWeight'     => "Añadir otro factor",
        'createWS'      => "Crear escala de valores",
        'jcGemsOnly'    => "Incluir solo <span%s>gemas de joyería</span>",
        'cappedHint'    => 'Consejo: <a href="javascript:;" onclick="fi_presetDetails();">Elimina</a> escalas para atributos al máximo como el Índice de Golpe.',
        'groupBy'       => "Agrupar por",
        'gb'            => array(
            ['Ninguno', 'none'],         ['Casilla', 'slot'],       ['Nivel', 'level'],     ['Fuente', 'source']
        ),
        'compareTool'   => "Herramienta de comparación de objetos",
        'talentCalc'    => "Calculadora de talentos",
        'petCalc'       => "Calculadora de mascotas",
        'chooseClass'   => "Escoge una clase",
        'chooseFamily'  => "Escoge una familia de mascota"
    ),
    'search' => array(
        'search'        => "Búsqueda",
        'foundResult'   => "Resultados de busqueda para",
        'noResult'      => "Ningún resultado para",
        'tryAgain'      => "Por favor, introduzca otras palabras claves o verifique el término ingresado.",
    ),
    'game' => array(
        'achievement'   => "logro",
        'achievements'  => "Logros",
        'class'         => "clase",
        'classes'       => "Clases",
        'currency'      => "monedas",
        'currencies'    => "Monedas",
        'difficulty'    => "Dificultad",
        'dispelType'    => "Tipo de disipación",
        'duration'      => "Duración",
        'gameObject'    => "entidad",
        'gameObjects'   => "Entidades",
        'glyphType'     => "Tipo de glifo",
        'race'          => "raza",
        'races'         => "Razas",
        'title'         => "título",
        'titles'        => "Títulos",
        'eventShort'    => "Evento",
        'event'         => "Suceso mundial ",
        'events'        => "Eventos del mundo",
        'faction'       => "facción",
        'factions'      => "Facciones",
        'cooldown'      => "%s de reutilización",
        'item'          => "objeto",
        'items'         => "Objetos",
        'itemset'       => "conjunto de objetos",
        'itemsets'      => "Conjuntos de objetos",
        'mechanic'      => "Mecanica",
        'mechAbbr'      => "Mec.",
        'npc'           => "PNJ",
        'npcs'          => "PNJs",
        'pet'           => "Mascota",
        'pets'          => "Mascotas de cazador",
        'profile'       => "",
        'profiles'      => "Perfiles",
        'requires'      => "Requiere %s",
        'requires2'     => "Requiere",
        'reqLevel'      => "Necesitas ser de nivel %s",
        'reqLevelHlm'   => "Necesitas ser de nivel %s",
        'reqSkillLevel' => "Requiere nivel de habilidad",
        'level'         => "Nivel",
        'school'        => "Escuela",
        'skill'         => "habilidad",
        'skills'        => "Habilidades",
        'spell'         => "hechizo",
        'spells'        => "Hechizos",
        'type'          => "Tipo",
        'valueDelim'    => " - ",
        'zone'          => "zona",
        'zones'         => "Zonas",

        'heroClass'     => "Clase héroe",
        'resource'      => "Recurso",
        'resources'     => "Recursos",
        'role'          => "Rol",
        'roles'         => "Roles",
        'specs'         => "Especializaciones",
        '_roles'        => ['Sanador', 'DPS cuerpo', 'DPS a distancia', 'Tanque'],

        'modes'         => ['Normal / Normal 10', 'Heroico / Normal 25', 'Heróico 10', 'Heróico 25'],
        'expansions'    => array("World of Warcraft", "The Burning Crusade", "Wrath of the Lich King"),
        'stats'         => array("Fuerza", "Agilidad", "Aguante", "Intelecto", "Espíritu"),
        'languages'     => array(
            1 => "Orco",        2 => "Darnassiano",     3 => "Taurahe",     6 => "Enánico",         7 => "Lengua común",    8 => "Demoníaco",       9 => "Titánico",        10 => "Thalassiano",
            11 => "Dracónico",  12 => "Kalimag",        13 => "Gnomótico",  14 => "Trol",           33 => "Viscerálico",    35 => "Draenei",        36 => "Zombie",         37 => "Binario gnomo",      38 => "Binario goblin"
        ),
        'gl'            => array(null, "Sublime", "Menor"),
        'si'            => array(1 => "Alianza", -1 => "Alianza solamente", 2 => "Horda", -2 => "Horda solamente", 3 => "Ambos"),
        'resistances'   => array(null, 'Resistencia a lo Sagrado', 'v', 'Resistencia a la Naturaleza', 'Resistencia a la Escarcha', 'Resistencia a las Sombras', 'Resistencia a lo Arcano'),
        'sc'            => array("Física", "Sagrado", "Fuego", "Naturaleza", "Escarcha", "Sombras", "Arcano"),
        'dt'            => array(null, "Magia", "Maldición", "Enfermedad", "Veneno", "Sigilo", "Invisibilidad", null, null, "Enfurecer"),
        'cl'            => array(null, "Guerrero", "Paladín", "Cazador", "Pícaro", "Sacerdote", "Caballero de la Muerte", "Chamán", "Mago", "Brujo", null, "Druida"),
        'ra'            => array(-2 => "Horda", -1 => "Alianza", "Ambos", "Humano", "Orco", "Enano", "Elfo de la noche", "No-muerto", "Tauren", "Gnomo", "Trol  ", null, "Blood Elf", "Elfo de sangre"),
        'rep'           => array("Odiado", "Hostil", "Adverso", "Neutral", "Amistoso", "Honorable", "Reverenciado", "Exaltado"),
        'st'            => array(
            "Defecto",          "Forma felina",                 "Árbol de vida",                "Forma de viaje",               "Forma acuática",
            "Forma de oso",     null,                           null,                           "Forma de oso temible",         null,
            null,               null,                           null,                           "Danza de las Sombras",         null,
            null,               "Lobo fantasmal",               "Actitud de batalla",           "Actitud defensiva",            "Actitud rabiosa",
            null,               null,                           "Metamorfosis",                 null,                           null,
            null,               null,                           "Forma de vuelo presto",        "Forma de las Sombras",         "Forma de vuelo",
            "Sigilo",           "Forma de lechúcico lunar",     "Espíritu redentor"
        ),
        'me'            => array(
            null,                       "Embelesado",               "Desorientado",             "Desarmado",                "Distraído",                "Huyendo",                  "Agarrado",                 "Enraizado",
            "Pacificado",               "Silenciado",               "Dormido",                  "Frenado",                  "Aturdido",                 "Congelado",                "Incapacitado",             "Sangrando",
            "Sanacíon",                 "Polimorfado",              "Desterrado",               "Protegido",                "Aprisionado",              "Montado",                  "Seducido",                 "Girado",
            "Horrorizado",              "Invulnerable",             "Interrumpido",             "Atontado",                 "Descubierto",              "Invulnerable",             "Aporreado",                "Iracundo"
        ),
        'ct'            => array(
            "Sin categoría",            "Bestia",                   "Dragonante",               "Demonio",                  "Elemental",                "Gigante",                  "No-muerto",                "Humanoide",
            "Alimaña",                  "Mecánico",                 "Sin especificar",          "Tótem",                    "Mascota mansa",            "Nube de gas"
        ),
        'fa'            => array(
            1 => "Lobo",                2 => "Felino",              3 => "Araña",               4 => "Oso",                 5 => "Jabalí",              6 => "Crocolisco",          7 => "Carroñero",           8 => "Cangrejo",
            9 => "Gorila",              11 => "Raptor",             12 => "Zancaalta",          20 => "Escórpido",          21 => "Tortuga",            24 => "Murciélago",         25 => "Hiena",              26 => "Ave rapaz",
            27 => "Serpiente alada",    30 => "Dracohalcón",        31 => "Devastador",         32 => "Acechador deformado",33 => "Esporiélago",        34 => "Raya abisal",        35 => "Serpiente",          37 => "Palomilla",
            38 => "Quimera",            39 => "Demosaurio",         41 => "Silítido",           42 => "Gusano",             43 => "Rinoceronte",        44 => "Avispa",             45 => "Can del Núcleo",     46 => "Bestia espíritu"
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
    'npc'   => array(
        'rank'          => [0 => "Normal", 1 => "Élite", 4 => "Raro", 2 => "Élite raro", 3 => "Jefe"],
        'classification'=> "Clasificación",
        'petFamily'     => "Familia de mascota",
        'react'         => "Reacción",
        'worth'         => "Valor",
        'unkPosition'   => "No se conoce la ubicación de este PNJ.",
        'difficultyPH'  => "[Este PNJ es un marcador de posición para un modo diferente de]",
        'quotes'        => "Citas",
        'gainsDesc'     => "Tras acabar con este PNJ ganarás",
        'repWith'       => "reputación con",
        'stopsAt'       => "se detiene en %s",
        'textTypes'     => [null, "grita", "dice", "susurra"],
        'modes'         => array(
            1 => ["Normal", "Heroico"],
            2 => ["10 jugadores Normal", "25 jugadores Normal", "10 jugadores Heroico", "25 jugadores Heroico"]
        ),
        'cat'           => array(
            "Sin categoría",            "Bestia",                   "Dragonante",               "Demonio",                  "Elemental",                "Gigante",                  "No-muerto",                "Humanoide",
            "Alimaña",                  "Mecánico",                 "Sin especificar",          "Tótem",                    "Mascota mansa",            "Nube de gas"
        )
    ),
    'event' => array(
        'start'         => "Empieza",
        'end'           => "Termina",
        'interval'      => "Intervalo",
        'inProgress'    => "El evento está en progreso actualmente",
        'category'      => array("Sin categoría", "Vacacionales", "Periódicos", "Jugador contra Jugador")
    ),
    'achievement' => array(
        'criteria'      => "Requisitos",
        'points'        => "Puntos",
        'series'        => "Serie",
        'outOf'         => "de",
        'criteriaType'  => "Criterium Type-Id:",
        'itemReward'    => "Recibirás:",
        'titleReward'   => "Deberías obtener el título \"<a href=\"?title=%d\">%s</a>\"",
        'slain'         => "matado",
        'reqNumCrt'     => "Requiere"
    ),
    'class' => array(
        'racialLeader'  => "Lider racial",
        'startZone'     => "Zona de inicio",
    ),
    'maps' => array(
        'maps'          => "Mapas",
        'linkToThisMap' => "Enlazar con esta mapa",
        'clear'         => "Borrar",
        'EasternKingdoms' => "Reinos del Este",
        'Kalimdor'      => "Kalimdor",
        'Outland'       => "Terrallende",
        'Northrend'     => "Rasganorte",
        'Instances'     => "Instancias",
        'Dungeons'      => "Mazmorras",
        'Raids'         => "Bandas",
        'More'          => "Más",
        'Battlegrounds' => "Campos de batalla",
        'Miscellaneous' => "Miscelánea",
        'Azeroth'       => "Azeroth",
        'CosmicMap'     => "Mapa cósmico",
    ),
    'zone' => array(
        // 'zone'          => "Zone",
        // 'zonePartOf'    => "Cette zone fait partie de la zone",
        'cat'           => array(
            "Reinos del Este",          "Kalimdor",                 "Mazmorras",                "Bandas",                   "No las uso",               null,
            "Campos de batalla",        null,                       "Terrallende",              "Arenas",                   "Rasganorte"
        )
    ),
    'quest' => array(
        'level'         => 'Nivel %s',
        'daily'         => 'Diaria',
        'requirements'  => 'Requisitos'
    ),
    'title' => array(
        'cat'           => array(
            'General',      'Jugador contra Jugador',    'Reputación',       'Mazmorras y bandas',     'Misiones',       'Profesiones',      'Eventos del mundo'
        )
    ),
    'skill' => array(
        'cat'           => array(
            -6 => 'Compañeros',         -5 => 'Monturas',           -4 => 'Habilidades de raza', 5 => 'Atributos',          6 => 'Habilidades con armas', 7 => 'Habilidades de clase', 8 => 'Armaduras disponibles',
             9 => 'Habilidades secundarias', 10 => 'Idiomas',       11 => 'Profesiones'
        )
    ),
    'currency' => array(
        'cap'           => "Límite total",
        'cat'           => array(
            1 => "Miscelánea", 2 => "Jugador contra Jugador", 4 => "Clásico", 21 => "Wrath of the Lich King", 22 => "Mazmorra y banda", 23 => "Burning Crusade", 41 => "Prueba", 3 => "No las uso"
        )
    ),
    'pet'      => array(
        'exotic'        => "Exótica",
        'cat'           => ["Ferocidad", "Tenacidad", "Astucia"]
    ),
    'faction' => array(
        'spillover'     => "[Reputation Spillover]",
        'spilloverDesc' => "[Gaining Reputation with this faction also yields a proportional gain with the factions listed below.]",
        'maxStanding'   => "Posición máxima",
        'quartermaster' => "Intendente"
    ),
    'itemset' => array(
        '_desc'         => "<b>%s</b> es el <b>%s</b>. Contiene %s piezas.",
        '_descTagless'  => "<b>%s</b> es un conjunto de objetos que tiene %s piezas.",
        '_setBonuses'   => "Bonificación de conjunto",
        '_conveyBonus'  => "Tener puestos mas objetos de este conjunto le aplicará una bonificación a tu personaje.",
        '_pieces'       => "piezas",
        '_unavailable'  => "Este conjunto de objetos no está disponible para jugadores.",
        '_tag'          => "Etiqueta",
        'summary'       => "Resúmen",
        'notes'         => array(
            null,                                   "Set de mazmorra 1",                    "Set de mazmorra 2",                        "Set de banda tier 1",
            "Set de banda tier 2",                  "Set de banda tier 3",                  "Set JcJ nivel 60 superior",                "Set JcJ nivel 60 superior (obsoleto)",
            "Set JcJ nivel 60 épico",               "Set de las Ruinas de Ahn'Qiraj",       "Set del Templo de Ahn'Qiraj",              "Set de Zul'Gurub",
            "Set de banda tier 4",                  "Set de banda tier 5",                  "Set de mazmorra 3",                        "Set de la Cuenca de Arathi",
            "Set JcJ nivel 70 superior",            "Set de la Temporada de Arenas 1",      "Set de banda tier 6",                      "Set de la Temporada de Arenas 2",
            "Set de la Temporada de Arenas 3",      "Set JcJ nivel 70 superior 2",          "Set de la Temporada de Arenas 4",          "Set de banda tier 7",
            "Set de la Temporada de Arenas 5",      "Set de banda tier 8",                  "Set de la Temporada de Arenas 6",          "Set de banda tier 9",
            "Set de la Temporada de Arenas 7",      "Set de banda tier 10",                 "Set de la Temporada de Arenas 8"
        ),
        'types'         => array(
            null,               "Tela",                 "Cuero",                "Malla",                    "Placas",                   "Daga",                     "Anillo",
            "Arma de puño",     "Hacha de uno mano",    "Maza de uno mano",     "Espada de uno mano",       "Abalorio",                 "Amuleto"
        )
    ),
    'spell' => array(
        '_spellDetails' => "Detalles de hechizos",
        '_cost'         => "Costo",
        '_range'        => "Rango",
        '_castTime'     => "Tiempo de lanzamiento",
        '_cooldown'     => "Reutilización",
        '_distUnit'     => "metros",
        '_forms'        => "Formas",
        '_aura'         => "Aura",
        '_effect'       => "Efecto",
        '_none'         => "Ninguno",
        '_gcd'          => "GCD",
        '_globCD'       => "Tiempo global de reutilización",
        '_gcdCategory'  => "Categoría GCD",
        '_value'        => "Valor",
        '_radius'       => "Radio",
        '_interval'     => "Intérvalo",
        '_inSlot'       => "en la casilla",
        '_collapseAll'  => "Contraer todo",
        '_expandAll'    => "Expandier todo",

        'discovered'    => "Aprendido via descubrimiento",
        'ppm'           => "%s procs por minuto",
        'procChance'    => "Probabilidad de que accione",
        'starter'       => "Hechizo inicial",
        'trainingCost'  => "Costo de enseñanza",
        'remaining'     => "%s restantes",
        'untilCanceled' => "hasta que se cancela",
        'castIn'        => "Hechizo de %s seg",
        'instantPhys'   => "Instante",
        'instantMagic'  => "Hechizo instantáneo",
        'channeled'     => "Canalizado",
        'range'         => "Alcance de %s m",
        'meleeRange'    => "Alcance de ataques cuerpo a cuerpo",
        'unlimRange'    => "Rango ilimitado",
        'reagents'      => "Componentes",
        'tools'         => "Herramientas",
        'home'          => "&lt;Posada&gt;",
        'pctCostOf'     => "del %s base",
        'costPerSec'    => ", mas %s por segundo",
        'costPerLevel'  => ", mas %s por nivel",
        '_scaling'      => "[Scaling]",
        'scaling'       => array(
            'directSP' => "[+%.2f%% of spell power to direct component]",        'directAP' => "[+%.2f%% of attack power to direct component]",
            'dotSP'    => "[+%.2f%% of spell power per tick]",                   'dotAP'    => "[+%.2f%% of attack power per tick]"
        ),
        'powerRunes'    => ["Escarcha", "Profano", "Sangre", "Muerte"],
        'powerTypes'    => array(   // heat => spell 70174
            -2 => "Salud",   -1 => null,   "Maná",     "Ira",     "Enfoque",    "Energía",      "[Happiness]",      "Runa",    "Poder rúnico",
            'AMMOSLOT' => "[Ammo]",         'STEAM' => "[Steam Pressure]",      'WRATH'       => "[Wrath]",         'PYRITE' => "[Pyrite]",
            'HEAT'     => "[Heat]",         'OOZE'  => "[Ooze]",                'BLOOD_POWER' => "[Blood Power]" // spellname of 72370
        ),
        'relItems'      => array (
            'base'    => "<small>Muestra %s relacionados con <b>%s</b></small>",
            'link'    => " u ",
            'recipes' => "<a href=\"?items=9.%s\">objetos de receta</a>",
            'crafted' => "<a href=\"?items&filter=cr=86;crs=%s;crv=0\">objetos fabricados</a>"
        ),
        'cat'           => array(
              7 => "Habilidades",
            -13 => "Glifos",
            -11 => array("Habilidades", 6 => "Armas", 8 => "Armadura", 10 => "Lenguas"),
             -4 => "Habilidades de raza",
             -2 => "Talentos",
             -6 => "Compañeros",
             -5 => "Monturas",
             -3 => array(
                "Habilidades de mascota",   782 => "Necrófago",         270 => "Genérico",              766 => "Acechador deformado",       203 => "Araña",                 655 => "Ave rapaz",             785 => "Avispa",
                788 => "Bestia espíritu",   787 => "Can del Núcleo",    214 => "Cangrejo",              213 => "Carroñero",                 212 => "Crocolisco",            781 => "Demosaurio",            767 => "Devastador",
                763 => "Dracohalcón",       236 => "Escórpido",         765 => "Esporiélago",           209 => "Felino",                    215 => "Gorila",                784 => "Gusano",                654 => "Hiena",
                211 => "Jabalí",            208 => "Lobo",              653 => "Murciélago",            210 => "Oso",                       775 => "Palomilla",             780 => "Quimera",               217 => "Raptor",
                764 => "Raya abisal",       786 => "Rinoceronte",       768 => "Serpiente",             656 => "Serpiente alada",           783 => "Silítido",              251 => "Tortuga",               218 => "Zancaalta",
                761 => "Guardia vil",       189 => "Manáfago",          188 => "Diablillo",             205 => "Súcubo",                    204 => "Abisario"
            ),
             -7 => array("Talentos de mascotas", 411 => "Astucia", 410 => "Ferocidad", 409 => "Tenacidad"),
             11 => array(
                "Profesiones",
                171 => "Alquimia",
                164 => array("Herrería", 9788 => "Forjador de armaduras", 9787 => "Forjador de armas", 17041 => "Maestro forjador de hachas", 17040 => "Maestro forjador de mazas", 17039 => "Maestro forjador de espadas"),
                333 => "Encantamiento",
                202 => array("Ingeniería", 20219 => "Ingeniero gnómico", 20222 => "Ingeniero goblin"),
                182 => "Herboristería",
                773 => "Inscripción",
                755 => "Joyería",
                165 => array("Peletería", 10656 => "Peletería de escamas de dragón", 10658 => "Peletería de elemental", 10660 => "Peletería de tribal"),
                186 => "Minería",
                393 => "Desollar",
                197 => array("Sastrería", 26798 => "Sastería de tela lunar primigenia", 26801 => "Sastrería de tejido de sombras", 26797 => "Sastería de fuego de hechizo"),
            ),
              9 => array("Habilidades secundarias", 185 => "Cocina", 129 => "Primeros auxilios", 356 => "Pesca", 762 => "Equitación"),
             -8 => "Habilidades de PNJ",
             -9 => "Habilidades de MJ",
              0 => "Sin categoría"
        ),
        'armorSubClass' => array(
            "Misceláneo",                           "Armaduras de tela",                    "Armaduras de cuero",                   "Armaduras de malla",                   "Armaduras de placas",
            null,                                   "Escudos",                              "Tratados",                             "Ídolos",                               "Tótems",
            "Sigilos"
        ),
        'weaponSubClass' => array(
            13 => "Armas de puño",                  15 => "Dagas",                           0 => "Hachas de una mano",              7 => "Espadas de una mano",             4 => "Mazas de una mano",
             6 => "Armas de asta",                  10 => "Bastones",                        1 => "Hachas de dos manos",             8 => "Espadas de dos manos",            5 => "Mazas de dos manos",
             2 => "Arcos",                           3 => "Armas de fuego",                 16 => "Arrojadizas",                    18 => "Ballestas",                      19 => "Varitas",
            20 => "Cañas de pescar",                14 => "Misceláneo"
        ),
        'subClassMasks'      => array(
            0x02A5F3 => 'Arma cuerpo a cuerpo',     0x0060 => 'Escudo',                     0x04000C => 'Arma de ataque a distancia',0xA091 => 'Arma cuerpo a cuerpo 1M'
        ),
        'traitShort'    => array(
            'atkpwr'    => "PA",                    'rgdatkpwr' => "PA",                    'splpwr'    => "PH",                    'arcsplpwr' => "PArc",                  'firsplpwr' => "PFue",
            'frosplpwr' => "PEsc",                  'holsplpwr' => "PSag",                  'natsplpwr' => "PNat",                  'shasplpwr' => "PSom",                  'splheal'   => "Sana"
        ),
        'spellModOp'    => array(
            'DAMAGE',                               'DURATION',                             'THREAT',                               'EFFECT1',                              'CHARGES',
            'RANGE',                                'RADIUS',                               'CRITICAL_CHANCE',                      'ALL_EFFECTS',                          'NOT_LOSE_CASTING_TIME',
            'CASTING_TIME',                         'COOLDOWN',                             'EFFECT2',                              'IGNORE_ARMOR',                         'COST',
            'CRIT_DAMAGE_BONUS',                    'RESIST_MISS_CHANCE',                   'JUMP_TARGETS',                         'CHANCE_OF_SUCCESS',                    'ACTIVATION_TIME',
            'DAMAGE_MULTIPLIER',                    'GLOBAL_COOLDOWN',                      'DOT',                                  'EFFECT3',                              'BONUS_MULTIPLIER',
            null,                                   'PROC_PER_MINUTE',                      'VALUE_MULTIPLIER',                     'RESIST_DISPEL_CHANCE',                 'CRIT_DAMAGE_BONUS_2',
            'SPELL_COST_REFUND_ON_FAIL'
        ),
        'combatRating'  => array(
            'WEAPON_SKILL',                         'DEFENSE_SKILL',                        'DODGE',                                'PARRY',                                'BLOCK',
            'HIT_MELEE',                            'HIT_RANGED',                           'HIT_SPELL',                            'CRIT_MELEE',                           'CRIT_RANGED',
            'CRIT_SPELL',                           'HIT_TAKEN_MELEE',                      'HIT_TAKEN_RANGED',                     'HIT_TAKEN_SPELL',                      'CRIT_TAKEN_MELEE',
            'CRIT_TAKEN_RANGED',                    'CRIT_TAKEN_SPELL',                     'HASTE_MELEE',                          'HASTE_RANGED',                         'HASTE_SPELL',
            'WEAPON_SKILL_MAINHAND',                'WEAPON_SKILL_OFFHAND',                 'WEAPON_SKILL_RANGED',                  'EXPERTISE',                            'ARMOR_PENETRATION'
        ),
        'lockType'      => array(
            null,                                   "Forzar cerradura",                     "Herboristería",                        "Minería",                              "Desactivar trampa",
            "Abrir",                                "Tesoro (DND)",                         "Gemas cálcicas elfas (DND)",           "Cerrar",                               "Activar trampa",
            "Apertura rápida",                      "Cerrado rápido",                       "Abrir ajustando",                      "Abrir de rodillas",                    "Abrir atacando",
            "Gahz'ridian (DND)",                    "Reventar",                             "Apertura JcJ",                         "Cierre JcJ",                           "Pescar",
            "Inscripción",                          "Abrir desde vehículo"
        ),
        'stealthType'   => ['GENERAL', 'TRAP'],
        'invisibilityType' => ['GENERAL', 3 => 'TRAP', 6 => 'DRUNK']
    ),
    'item' => array(
        'armor'         => "%s armadura",
        'block'         => "%s bloqueo",
        'charges'       => "cargas",
        'locked'        => "Cerrado",
        'ratingString'  => "%s&nbsp;@&nbsp;L%s",
        'heroic'        => "Heroico",
        'unique'        => "Único",
        'uniqueEquipped'=> "Único-Equipado",
        'startQuest'    => "Este objeto inicia una misión",
        'bagSlotString' => "%s de %d casillas",
        'dps'           => "daño por segundo",
        'dps2'          => "daño por segundo",
        'addsDps'       => "Añade",
        'fap'           => "poder de ataque feral",
        'durability'    => "Durabilidad",
        'realTime'      => "tiempo real",
        'conjured'      => "Objeto mágico",
        'damagePhys'    => "%s Daño",
        'damageMagic'   => "%s %s Daño",
        'speed'         => "Velocidad",
        'sellPrice'     => "Precio de venta",
        'itemLevel'     => "Nivel de objeto",
        'randEnchant'   => "&lt;Encantamiento aleatorio&gt",
        'readClick'     => "&lt;Click derecho para leer&gt",
        'openClick'     => "&lt;Click derecho para abrir&gt",
        'set'           => "Conjunto",
        'partyLoot'     => "Despojo de grupo",
        'smartLoot'     => "Botín inteligente",
        'indestructible'=> "No puede ser destruido",
        'deprecated'    => "Depreciado",
        'useInShape'    => "Se puede usar con cambio de forma",
        'useInArena'    => "Se puede usar en arenas",
        'refundable'    => "Se puede devolver",
        'noNeedRoll'    => "No se puede hacer una tirada por Necesidad",
        'atKeyring'     => "Se puede poner en el llavero",
        'worth'         => "Valor",
        'consumable'    => "Consumible",
        'nonConsumable' => "No consumible",
        'accountWide'   => "Ligado a la cuenta",
        'millable'      => "Se puede moler",
        'noEquipCD'     => "No tiene tiempo de reutilización al equipar",
        'prospectable'  => "Prospectable",
        'disenchantable'=> "Desencantable",
        'cantDisenchant'=> "No se puede desencantar",
        'repairCost'    => "Coste de reparación",
        'tool'          => "Herramienta",
        'cost'          => "Coste",
        'content'       => "Contenido",
        '_transfer'     => 'Este objeto será convertido a <a href="?item=%d" class="q%d icontiny tinyspecial" style="background-image: url(images/icons/tiny/%s.gif)">%s</a> si lo transfieres a la <span class="%s-icon">%s</span>.',
        '_unavailable'  => "Este objeto no está disponible para los jugadores.",
        '_rndEnchants'  => "Encantamientos aleatorios",
        '_chance'       => "(probabilidad %s%%)",
        '_reqLevel'     => "Nivel requerido",
        'reqRating'     => "Requiere un índice de arena personal y de equipo de %d",
        'slot'          => "Casilla",
        '_quality'      => "Calidad",
        'usableBy'      => "Usable por",
        'buyout'        => "Precio de venta en subasta",
        'each'          => "cada uno",
        'gems'          => "Gemas",
        'socketBonus'   => "Bono de ranura",
        'socket'        => array(
            "Ranura meta",          "Ranura roja",      "Ranura amarilla",          "Ranura azul",            -1 => "Ranura prismática"
        ),
        'quality'       => array (
            "Pobre",                "Común",            "Poco Común",               "Raro",
            "Épica",                "Legendaria",       "Artefacto",                "Reliquia"
        ),
        'trigger'       => array (
            "Uso: ",                "Equipar: ",        "Probabilidad al acertar: ", null,                          null,
            null,                   null
        ),
        'bonding'       => array (
            "Se liga a la cuenta",                      "Se liga al recogerlo",                                     "Se liga al equiparlo",
            "Se liga al usarlo",                        "Objeto de misión",                                         "Objeto de misión"
        ),
        'bagFamily'     => array(
            "Bolsa",                "Carcaj",           "Bolsa de municiones",      "Bolsa de almas",               "Bolsa de peletería",
            "Bolsa de inscripción", "Bolsa de hierbas", "Bolsa de encantamiento",   "Bolsa de ingeniería",          null, /*Llave*/
            "Bolsa de gemas",       "Bolsa de minería"
        ),
        'inventoryType' => array(
            null,                   "Cabeza",           "Cuello",                   "Hombro",                       "Camisa",
            "Pecho",                "Cintura",          "Piernas",                  "Pies",                         "Muñeca",
            "Manos",                "Dedo",             "Abalorio",                 "Una mano",                     "Mano izquierda", /*Escudo*/
            "A distancia",          "Espalda",          "Dos manos",                "Bolsa",                        "Tabardo",
            null, /*Robe*/          "Mano derecha",     "Mano izquierda",           "Sostener con la mano izquierda", "Proyectiles",
            "Arrojadiza",           null, /*Ranged2*/   "Carcaj",                   "Reliquia"
        ),
        'armorSubClass' => array(
            "Misceláneo",           "Tela",             "Cuero",                    "Malla",                        "Placas",
            null,                   "Escudo",           "Tratado",                  "Ídolo",                        "Tótem",
            "Sigilo"
        ),
        'weaponSubClass' => array(
            "Hacha",                "Hacha",            "Arco",                     "Arma de fuego",                "Maza",
            "Maza",                 "Arma de asta",     "Espada",                   "Espada",                       null,
            "Bastón",               null,               null,                       "Arma de puño",                 "Misceláneo",
            "Daga",                 "Arrojadizas",      null,                       "Ballesta",                     "Varita",
            "Caña de pescar"
        ),
        'projectileSubClass' => array(
            null,                   null,               "Flecha",                   "Bala",                         null
        ),
        'elixirType'    => [null, "Batalla", "Guardián"],
'cat'           => array(       // ordered by content firts, then alphabeticaly
             2 => "Weapons",                                // self::$spell['weaponSubClass']
             4 => array("Armor", array(
                 1 => "Cloth Armor",                 2 => "Leather Armor",           3 => "Mail Armor",              4 => "Plate Armor",             6 => "Shields",                 7 => "Librams",
                 8 => "Idols",                       9 => "Totems",                 10 => "Sigils",                 -6 => "Cloaks",                 -5 => "Off-hand Frills",        -8 => "Shirts",
                -7 => "Tabards",                    -3 => "Amulets",                -2 => "Rings",                  -4 => "Trinkets",                0 => "Miscellaneous (Armor)",
            )),
             1 => array("Containers", array(
                 0 => "Bags",                        3 => "Enchanting Bags",         4 => "Engineering Bags",        5 => "Gem Bags",                2 => "Herb Bags",               8 => "Inscription Bags",
                 7 => "Leatherworking Bags",         6 => "Mining Bags",             1 => "Soul Bags"
            )),
             0 => array("Consumables", array(
                -3 => "Item Enhancements (Temporary)",                               6 => "Item Enhancements (Permanent)",                           2 => ["Elixirs", [1 => "Battle Elixirs", 2 => "Guardian Elixirs"]],
                 1 => "Potions",                     4 => "Scrolls",                 7 => "Bandages",                0 => "Consumables",             3 => "Flasks",                  5 => "Food & Drinks",
                 8 => "Other (Consumables)"
            )),
            16 => array("Glyphs", array(
                 1 => "Warrior Glyphs",              2 => "Paladin Glyphs",          3 => "Hunter Glyphs",           4 => "Rogue Glyphs",            5 => "Priest Glyphs",           6 => "Death Knight Glyphs",
                 7 => "Shaman Glyphs",               8 => "Mage Glyphs",             9 => "Warlock Glyphs",         11 => "Druid Glyphs"
            )),
             7 => array("Trade Goods", array(
                14 => "Armor Enchantments",          5 => "Cloth",                   3 => "Devices",                10 => "Elemental",              12 => "Enchanting",              2 => "Explosives",
                 9 => "Herbs",                       4 => "Jewelcrafting",           6 => "Leather",                13 => "Materials",               8 => "Meat",                    7 => "Metal & Stone",
                 1 => "Parts",                      15 => "Weapon Enchantments",    11 => "Other (Trade Goods)"
             )),
             6 => ["Projectiles", [                  2 => "Arrows",                  3 => "Bullets"     ]],
            11 => ["Quivers",     [                  2 => "Quivers",                 3 => "Ammo Pouches"]],
             9 => array("Recipes", array(
                 0 => "Books",                       6 => "Alchemy Recipes",         4 => "Blacksmithing Plans",     5 => "Cooking Recipes",         8 => "Enchanting Formulae",     3 => "Engineering Schematics",
                 7 => "First Aid Books",             9 => "Fishing Books",          11 => "Inscription Techniques", 10 => "Jewelcrafting Designs",   1 => "Leatherworking Patterns",12 => "Mining Guides",
                 2 => "Tailoring Patterns"
            )),
             3 => array("Gems", array(
                 6 => "Meta Gems",                   0 => "Red Gems",                1 => "Blue Gems",               2 => "Yellow Gems",             3 => "Purple Gems",             4 => "Green Gems",
                 5 => "Orange Gems",                 8 => "Prismatic Gems",          7 => "Simple Gems"
            )),
            15 => array("Miscellaneous", array(
                -2 => "Armor Tokens",                3 => "Holiday",                 0 => "Junk",                    1 => "Reagents",                5 => "Mounts",                 -7 => "Flying Mounts",
                 2 => "Small Pets",                  4 => "Other (Miscellaneous)"
            )),
            10 => "Currency",
            12 => "Quest",
            13 => "Keys",
        ),
        'statType'      => array(
            "Aumenta tu maná %d p.",
            "Aumenta tu salud %d p.",
            null,
            "agilidad",
            "fuerza",
            "intelecto",
            "espíritu",
            "aguante",
            null, null, null, null,
            "Aumenta tu índice de defensa %d p.",
            "Aumenta tu índice de esquivar %d p.",
            "Aumenta tu índice de parada %d p.",
            "Aumenta tu índice de bloqueo con escudo %d p.",
            "Mejora tu índice de golpe cuerpo a cuerpo %d p.",
            "Mejora tu índice de golpe a distancia %d p.",
            "Mejora tu índice de golpe con hechizos %d p.",
            "Mejora tu índice de golpe crítico cuerpo a cuerpo %d p. ",
            "Mejora tu índice de golpe crítico a distancia %d p.",
            "Mejora tu índice de golpe crítico con hechizos %d p.",
            "Mejora tu índice de evasión de golpes cuerpo a cuerpo %d p.",
            "Mejora tu índice de evasión de golpes a distancia %d p.",
            "Mejora tu índice de evasión de golpes con hechizos %d p.",
            "Mejora tu índice de evasión de golpe crítico cuerpo a cuerpo %d p.",
            "Mejora tu índice de evasión de golpe crítico a distancia %d p.",
            "Mejora tu índice de evasión de golpe crítico con hechizos %d p.",
            "Mejora tu índice de celeridad cuerpo a cuerpo %d p.",
            "Mejora tu índice de celeridad a distancia %d p.",
            "Mejora tu índice de celeridad con hechizos %d p.",
            "Aumenta tu índice de golpe %d p.",
            "Aumenta tu índice de golpe crítico %d p.",
            "Mejora tu índice de evasión %d p.",
            "Mejora tu índice de evasión de golpes críticos %d p.",
            "Aumenta tu índice de temple %d p.",
            "Aumenta tu índice de celeridad %d p.",
            "Aumenta tu índice de pericia %d p.",
            "Aumenta el poder de ataque %d p.",
            "Aumenta el poder de ataque a distancia %d p.",
            "Aumenta en %d p. el poder de ataque bajo formas felinas, de oso, de oso temible y de lechúcico lunar.",
            "Aumenta el daño infligido con hechizos y efectos mágicos hasta %d p.",
            "Aumenta la sanación hecha con hechizos y efectos mágicos hasta %d p.",
            "Restaura %d p. de maná cada 5 s.",
            "Aumenta tu índice de penetración de armadura %d p.",
            "Aumenta el poder con hechizos %d p.",
            "Restaura %d p. de salud cada 5 s.",
            "Aumenta la penetración de hechizos %d p.",
            "Aumenta el valor de bloqueo de tu escudo %d p.",
            "Estadística no utilizada #%d (%d)",
        )
    ),
    'colon'             => ': ',
    'dateFmtShort'      => "d/m/Y",
    'dateFmtLong'       => "d/m/Y \a \l\a\s H:i"
);

?>
