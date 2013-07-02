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
    'main' => array(
        'name'          => "Nombre",
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
        'forum'         => "[Forum]",
        'days'          => "dias",
        'hours'         => "horas",
        'minutes'       => "minutos",
        'seconds'       => "segundos",
        'millisecs'     => "[milliseconds]",
        'daysAbbr'      => "",  // ???
        'hoursAbbr'     => "h",
        'minutesAbbr'   => "min",
        'secondsAbbr'   => "seg",
        'millisecsAbbr' => "[ms]",

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

        // infobox
        'unavailable'   => "No está disponible a los jugadores",
        'disabled'      => "[Disabled]",
        'disabledHint'  => "[Cannot be attained or completed]",
        'serverside'    => "[Serverside]",
        'serversideHint' => "[These informations are not in the Client and have been provided by sniffing and/or guessing.]",

        // red buttons
        'links'         => "Enlaces",
        'compare'       => "Comparar",
        'view3D'        => "Ver en 3D"
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
        'cooldown'      => "%s de reutilización",
        'itemset'       => "conjunto de objetos",
        'itemsets'      => "Conjuntos de objetos",
        'mechanic'      => "Mecanica",
        'mechAbbr'      => "Mec.",
        'pet'           => "Mascota",
        'pets'          => "Mascotas de cazador",
        'petCalc'       => "Calculadora de mascotas",
        'requires'      => "Requiere %s",
        'requires2'     => "Requiere",
        'reqLevel'      => "Necesitas ser de nivel %s",
        'reqLevelHlm'   => "Necesitas ser de nivel %s",
        'reqSkillLevel' => "Requiere nivel de habilidad",
        'level'         => "Nivel",
        'school'        => "Escuela",
        'spell'         => "hechizo",
        'spells'        => "Hechizos",
        'type'          => "Tipo",
        'valueDelim'    => " - ",
        'zone'          => "zona",
        'zones'         => "Zonas",
        'expansions'    => array("World of Warcraft", "The Burning Crusade", "Wrath of the Lich King"),
        'stats'         => array("Fuerza", "Agilidad", "Aguante", "Intelecto", "Espíritu"),
        'languages'     => array(
            1 => "Orco",        2 => "Darnassiano",     3 => "Taurahe",     6 => "Enánico",         7 => "Lengua común",    8 => "Demoníaco",       9 => "Titánico",        10 => "Thalassiano",
            11 => "Dracónico",  12 => "Kalimag",        13 => "Gnomótico",  14 => "Trol",           33 => "Viscerálico",    35 => "Draenei",        36 => "Zombie",         37 => "Binario gnomo",      38 => "Binario goblin"
        ),
        'gl'            => array(null, "Sublime", "Menor"),
        'si'            => array(-2 => "Horda solamente", -1 => "Alianza solamente", null, "Alianza", "Horda", "Ambos"),
        'resistances'   => array(null, 'Resistencia a lo Sagrado', 'v', 'Resistencia a la Naturaleza', 'Resistencia a la Escarcha', 'Resistencia a las Sombras', 'Resistencia a lo Arcano'),
        'sc'       => array("Física", "Sagrado", "Fuego", "Naturaleza", "Escarcha", "Sombras", "Arcano"),
        'dt'            => array(null, "Magia", "Maldición", "Enfermedad", "Veneno", "Sigilo", "Invisibilidad", null, null, "Enfurecer"),
        'cl'            => array(null, "Guerrero", "Paladín", "Cazador", "Pícaro", "Sacerdote", "Caballero de la Muerte", "Chamán", "Mago", "Brujo", null, "Druida"),
        'ra'            => array(-2 => "Horda", -1 => "Alianza", "Ambos", "Humano", "Orco", "Enano", "Elfo de la noche", "No-muerto", "Tauren", "Gnomo", "Trol  ", null, "Blood Elf", "Elfo de sangre"),
        'rep'           => array("Odiado", "Hostil", "Adverso", "Neutral", "Amistoso", "Honorable", "Reverenciado", "Exaltado"),
        'st'            => array(
            null,               "Forma felina",                 "Árbol de vida",                "Forma de viaje",               "Forma acuática",
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
    'event' => array(
        'category'      => array("Sin categoría", "Vacacionales", "Periódicos", "Jugador contra Jugador")
    ),
    'npc'   => array(
        'rank'          => ['Normal', 'Élite', 'Élite raro', 'Jefe', 'Raro']
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
    ),
    'compare' => array(
        'compare'       => "Herramienta de comparación de objetos",
    ),
    'talent'  => array(
        'talentCalc'    => "Calculadora de talentos",
        'petCalc'       => "Calculadora de mascotas",
        'chooseClass'   => "Escoge una clase",
        'chooseFamily'  => "Escoge una familia de mascota",
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
    'currency' => array(
        'cat'           => array(
            1 => "Miscelánea", 2 => "Jugador contra Jugador", 4 => "Clásico", 21 => "Wrath of the Lich King", 22 => "Mazmorra y banda", 23 => "Burning Crusade", 41 => "Prueba", 3 => "No las uso"
        )
    ),
    'pet'      => array(
        'exotic'        => "Exótica",
        "cat"           => ["Ferocidad", "Tenacidad", "Astucia"]
    ),
    'itemset' => array(
        '_desc'         => "<b>%s</b> es el <b>%s</b>. Contiene %s piezas.",
        '_descTagless'  => "<b>%s</b> es un conjunto de objetos que tiene %s piezas.",
        '_setBonuses'   => "Bonificación de conjunto",
        '_conveyBonus'  => "Tener puestos mas objetos de este conjunto le aplicará una bonificación a tu personaje.",
        '_pieces'       => "piezas",
        '_unavailable'  => "Este conjunto de objetos no está disponible para jugadores.",
        '_tag'          => "Etiqueta",

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
        'powerRunes'    => ["Escarcha", "Profano", "Sangre", "Muerte"],
        'powerTypes'    => array(   // heat => spell 70174
            -2 => "Salud",   -1 => null,   "Maná",     "Ira",     "Enfoque",    "Energía",      "[Happiness]",      "Runa",    "Poder rúnico",
            'AMMOSLOT' => "[Ammo]",        'STEAM' => "[Steam Pressure]",       'WRATH' => "[Wrath]",               'PYRITE' => "[Pyrite]",
            'HEAT' => "[Heat]",            'OOZE' => "[Ooze]",                  'BLOOD_POWER' => "[Blood Power]" // spellname of 72370
        ),
        'relItems'      => array (
            'base'    => "<small>Muestra %s relacionados con <b>%s</b></small>",
            'link'    => " u ",
            'recipes' => "<a href=\"?items=9.%s\">objetos de receta</a>",
            'crafted' => "<a href=\"?items&filter=cr=86;crs=%s\">objetos fabricados</a>"
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
            "Misceláneo",           "Armaduras de tela","Armaduras de cuero",   "Armaduras de malla",           "Armaduras de placas",
            null,                   "Escudos",          "Tratados",             "Ídolos",                       "Tótems",
            "Sigilos"
        ),
        'weaponSubClass' => array(
            "Hachas de una mano",   "Hachas de dos manos","Arcos",              "Armas de fuego",               "Mazas de una mano",
            "Mazas de dos manos",   "Armas de asta",    "Espadas de una mano",  "Espadas de dos manos",         null,
            "Bastones",             null,               null,                   "Armas de puño",                "Misceláneo",
            "Dagas",                "Arrojadizas",      null,                   "Ballestas",                    "Varitas",
            "Cañas de pescar"
        ),
        'subClassMasks'      => array(
            0x02A5F3 => 'Arma cuerpo a cuerpo',         0x0060 => 'Escudo',                         0x04000C => 'Arma de ataque a distancia',   0xA091 => 'Arma cuerpo a cuerpo 1M'
        ),
        'traitShort'    => array(
            'atkpwr'    => "PA",                        'rgdatkpwr' => "PA",                                    'splpwr'    => "PH",
            'arcsplpwr' => "PArc",                      'firsplpwr' => "PFue",                                  'frosplpwr' => "PEsc",
            'holsplpwr' => "PSag",                      'natsplpwr' => "PNat",                                  'shasplpwr' => "PSom",
            'splheal'   => "Sana"
        )
    ),
    'item' => array(
        'armor'         => "%s armadura",
        'block'         => "%s bloqueo",
        'charges'       => "cargas",
        'expend'        => "[expendable]",  // drop this shit
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
        'duration'      => "Duración",
        'realTime'      => "tiempo real",
        'conjured'      => "Objeto mágico",
        'damagePhys'    => "%s Daño",
        'damageMagic'   => "%s %s Daño",
        'speed'         => "Velocidad",
        'sellPrice'     => "Precio de venta",
        'itemLevel'     => "Nivel de objeto",
        'randEnchant'   => "&lt;Encantamiento aleatorio&gt",
        'readClick'     => "&lt;Click derecho para leer&gt",
        'set'           => "Conjunto",
        'socketBonus'   => "Bono de ranura",
        'socket'        => array(
            "Ranura meta",          "Ranura roja",      "Ranura amarilla",          "Ranura azul",            -1 => "Ranura prismática  "
        ),
        'quality'       => array (
            "Pobre",                "Común",            "Poco Común",           "Raro",
            "Épica",                "Legendaria",       "Artefacto",            "Reliquia"
        ),
        'trigger'       => array (
            "Uso: ",                "Equipar: ",        "Probabilidad al acertar: ", null,                      null,
            null,                   null
        ),
        'bonding'       => array (
            "Se liga a la cuenta",                      "Se liga al recogerlo",                                 "Se liga al equiparlo",
            "Se liga al usarlo",                        "[ligados al alma]", /* google :( */                    "Objeto de misión"
        ),
        "bagFamily"     => array(
            "Bolsa",                "Carcaj",           "Bolsa de municiones",      "Bolsa de almas",             "Bolsa de peletería",
            "Bolsa de inscripción", "Bolsa de hierbas", "Bolsa de encantamiento",   "Bolsa de ingeniería",        "Llave",
            "Bolsa de gemas",       "Bolsa de minería"
        ),
        'inventoryType' => array(
            null,                   "Cabeza",           "Cuello",                   "Hombro",                       "Camisa",
            "Pecho",                "Cintura",          "Piernas",                  "Pies",                         "Muñeca",
            "Manos",                "Dedo",             "Abalorio",                 "Una mano",                     "Escudo",
            "A distancia",          "Espalda",          "Dos manos",                "Bolsa",                        "Tabardo",
            "Pecho",                "Mano derecha",     "Mano izquierda",           "Sostener con la mano izquierda", "Proyectiles",
            "Arrojadiza",           "A distancia",      "Carcaj",                   "Reliquia"
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
        'statType'  => array(
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
    'colon'         => ': '
);

?>
