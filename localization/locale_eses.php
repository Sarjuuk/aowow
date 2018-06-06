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
        'ab'            => ["año",  "mes",   "sem",     "",     "h",     "min",     "seg",      "ms"]
    ),
    'main' => array(
        'name'          => "nombre",
        'link'          => "Enlace",
        'signIn'        => "Iniciar sesión / Registrarse",
        'jsError'       => "Por favor, asegúrese de que ha habilitado javascript.",
        'language'      => "lengua",
        'feedback'      => "Feedback",
        'numSQL'        => "Número de consultas de MySQL",
        'timeSQL'       => "El tiempo para las consultas de MySQL",
        'noJScript'     => '<b>Este sitio hace uso intenso de JavaScript.</b><br />Por favor <a href="https://www.google.com/support/adsense/bin/answer.py?answer=12654" target="_blank">habilita JavaScript</a> en tu navegador.',
        'userProfiles'  => "Tus personajes",    // translate.google :x
        'pageNotFound'  => "Este %s no existe.",
        'gender'        => "Género",
        'sex'           => [null, "Hombre", "Mujer"],
        'players'       => "Jugadores",
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
        'login'         => "Ingresar",
        'forum'         => "Foro",
        'n_a'           => "n/d",
        'siteRep'       => "Reputación",
        'yourRepHistory'=> "Tu Historial de Reputación",
        'aboutUs'       => "Sobre Aowow",
        'and'           => " y ",
        'or'            => " o ",
        'back'          => "Atrás",
        'reputationTip' => "Puntos de reputación",
        'byUserTimeAgo' => 'Por <a href="'.HOST_URL.'/?user=%s">%1$s</a> hace %s',
        'help'          => "Ayuda",

        // filter
        'extSearch'     => "Extender búsqueda",
        'addFilter'     => "Añadir otro filtro",
        'match'         => "Aplicar",
        'allFilter'     => "Todos los filtros",
        'oneFilter'     => "Por lo menos uno",
        'applyFilter'   => "Aplicar filtro",
        'resetForm'     => "Reiniciar formulario",
        'refineSearch'  => 'Sugerencia: Refina tu búsqueda llendo a una <a href="javascript:;" id="fi_subcat">subcategoría</a>.',
        'clear'         => "borrar",
        'exactMatch'    => "Coincidencia exacta",
        '_reqLevel'     => "Nivel requerido",

        // infobox
        'unavailable'   => "No está disponible a los jugadores",
        'disabled'      => "Deshabilitado",
        'disabledHint'  => "No puede ser conseguido o completado",
        'serverside'    => "Parte del Servidor",
        'serversideHint'=> "Esta información no se encuentra en el Cliente y ha sido previsto mediante un analizador de paquetes (Sniffing) y/o suponiendo.",

        // red buttons
        'links'         => "Enlaces",
        'compare'       => "Comparar",
        'view3D'        => "Ver en 3D",
        'findUpgrades'  => "Buscar mejoras...",

        // misc Tools
        'errPageTitle'  => "Página no encontrada",
        'nfPageTitle'   => "Error",
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
            ["Ninguno", "none"],         ["Casilla", "slot"],       ["Nivel", "level"],     ["Fuente", "source"]
        ),
        'compareTool'   => "Herramienta de comparación de objetos",
        'talentCalc'    => "Calculadora de talentos",
        'petCalc'       => "Calculadora de mascotas",
        'chooseClass'   => "Escoge una clase",
        'chooseFamily'  => "Escoge una familia de mascota",

        // search
        'search'        => "Búsqueda",
        'searchButton'  => "búsqueda",
        'foundResult'   => "Resultados de busqueda para",
        'noResult'      => "Ningún resultado para",
        'tryAgain'      => "Por favor, introduzca otras palabras claves o verifique el término ingresado.",
        'ignoredTerms'  => "Las siguientes palabras fueron ignoradas en tu búsqueda: %s",

        // formating
        'colon'         => ': ',
        'dateFmtShort'  => "d/m/Y",
        'dateFmtLong'   => "d/m/Y \a \l\a\s H:i",

        // error
        'intError'      => "Un error interno ha ocurrido.",
        'intError2'     => "Un error interno ha ocurrido. (%s)",
        'genericError'  => "Ha ocurrido un error; refresca la página e inténtalo de nuevo. Si el error persiste manda un correo a <a href='#contact'>feedback</a>", # LANG.genericerror
        'bannedRating'  => "Has sido baneado y no podrás valorar comentarios.", # LANG.tooltip_banned_rating
        'tooManyVotes'  => "Has alcanzado el límite diario de votos. Vuelve mañana.", # LANG.tooltip_too_many_votes
        'alreadyReport' => "Ya has reportado esto.", # LANG.ct_resp_error7
        'textTooShort'  => "[Your message is too short.]",
        'cannotComment' => "[You have been banned from writing comments.]",
        'textLength'    => "[Your comment has %d characters and must have at least %d and at most %d characters.]",

        'moreTitles'    => array(
            'reputation'    => "Reputación de la web",
            'whats-new'     => "What's New",
            'searchbox'     => "Caja de búsqueda",
            'tooltips'      => "Tooltips",
            'faq'           => "Preguntas frecuentes",
            'aboutus'       => "[What is AoWoW?]",
            'searchplugins' => "Extensiones de búsqueda",
            'privileges'    => "Privilegios",
            'top-users'     => "Usuarios más populares",
            'help'          => array(
                'commenting-and-you' => "Los comentarios y tú",             'modelviewer'       => "Visualizador de modelos",   'screenshots-tips-tricks' => "Capturas de pantalla: Sugerencias y trucos",
                'stat-weighting'     => "Medición de atributos",            'talent-calculator' => "Calculadora de talentos",   'item-comparison'         => "Comparación de objetos",
                'profiler'           => "Perfiles",                         'markup-guide'      => "Margen de Guia"
            )
        )
    ),
    'profiler' => array(
        'realm'         => "Reino",
        'region'        => "Región",
        'viewCharacter' => "View Character",
        '_cpHint'       => "l <b>Gestor de perfiles</b> te permite editar tu personaje, encontrar mejoras de equipo, comprobar tu gearscore, ¡y más!",
        '_cpHelp'       => "Para comenzar, sigue los pasos abajo indicados. Si quieres más información, revisa nuestra amplia <a href=\"?help=profiler\">página de ayuda</a>.",
        '_cpFooter'     => "Si quieres una búsqueda más refinada, prueba con nuestras opciones de <a href=\"?profiles\">búsqueda avanzada</a>. También puedes crear un <a href=\"?profile&amp;new\">perfil nuevo personalizado</a>.",
        'firstUseTitle' => "%s de %s",
        'complexFilter' => "[Complex filter selected! Search results are limited to cached Characters.]",
        'customProfile' => " ([Custom Profile])",
        'resync'        => "Resincronizar",
        'guildRoster'   => "Lista de miembros de hermandad para &lt;%s&gt",
        'arenaRoster'   => "Personajes del Equipo de Arena para &lt;%s&gt",
        'atCaptain'     => "Capitán de equipo de arena",

        'profiler'      => "Gestor de Perfiles", // Perfiles de Personaje? (character profiler)
        'arenaTeams'    => "Equipos de Arena",
        'guilds'        => "Hermandades",

        'notFound'      => array(
            'guild'     => "[This Guild doesn't exist or is not yet in the database.]",
            'arenateam' => "[This Arena Team doesn't exist or is not yet in the database.]",
            'profile'   => "Este personaje no existe o no está aun en la base de datos.",
        ),
        'dummyNPCs'     => array(
            100001 => "Batalla de naves de guerra",
            200001 => "Bestias de Rasganorte", 200002 => "Campeones de facciones", 200003 => "Gemelas Val'kyr",
            300001 => "Los Cuatro Jinetes",
            400001 => "La Asamblea de Hierro"
        ),
    ),
    'screenshot' => array(
        'submission'    => "Enviar una captura de pantalla",
        'selectAll'     => "Seleccionar todos",
        'cropHint'      => "Puede reducir su imagen e introducir una etiqueta.",
        'displayOn'     => "Mostrado en:[br]%s - [%s=%d]",
        'caption'       => "Anotación",
        'charLimit'     => "Opcional, hasta 200 caracteres",
        'thanks'        => array(
            'contrib' => "¡Muchísimas gracias por tu aportación!",
            'goBack'  => '<a href="?%s=%d">aquí vuelve</a> a la página de la que viniste.',
            'note'    => "Nota: Su captura de imagen tiene que ser aprobada antes de que pueda aparecer en el sitio. Esto puede tomar hasta 72 horas."
        ),
        'error'         => array(
            'unkFormat'   => "Formato de imagen desconocido.",
            'tooSmall'    => "Su captura de pantalla es muy pequeña. (&lt; ".CFG_SCREENSHOT_MIN_SIZE."x".CFG_SCREENSHOT_MIN_SIZE.").",
            'selectSS'    => "Por favor seleccione la captura de pantalla para subir.",
            'notAllowed'  => "¡No estás permitido para subir capturas de pantalla!",
        )
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
        'emote'         => "emoción",
        'emotes'        => "Emociones",
        'enchantment'   => "encantamiento",
        'enchantments'  => "Encantamientos",
        'object'        => "entidad",
        'objects'       => "Entidades",
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
        'icon'          => "icono",
        'icons'         => "Iconos",
        'item'          => "objeto",
        'items'         => "Objetos",
        'itemset'       => "conjunto de objetos",
        'itemsets'      => "Conjuntos de objetos",
        'mechanic'      => "Mecanica",
        'mechAbbr'      => "Mec.",
        'meetingStone'  => "Roca de encuentro",
        'npc'           => "PNJ",
        'npcs'          => "PNJs",
        'pet'           => "Mascota",
        'pets'          => "Mascotas de cazador",
        'profile'       => "",
        'profiles'      => "Perfiles",
        'quest'         => "misión",
        'quests'        => "Misiones",
        'requires'      => "Requiere %s",
        'requires2'     => "Requiere",
        'reqLevel'      => "Necesitas ser de nivel %s",
        'reqSkillLevel' => "Requiere nivel de habilidad",
        'level'         => "Nivel",
        'school'        => "Escuela",
        'skill'         => "habilidad",
        'skills'        => "Habilidades",
        'sound'         => "sonido",
        'sounds'        => "Sonidos",
        'spell'         => "hechizo",
        'spells'        => "Hechizos",
        'type'          => "Tipo",
        'valueDelim'    => " - ",
        'zone'          => "zona",
        'zones'         => "Zonas",

        'pvp'           => "JcJ",
        'honorPoints'   => "Puntos de Honor",
        'arenaPoints'   => "Puntos de arena",
        'heroClass'     => "Clase héroe",
        'resource'      => "Recurso",
        'resources'     => "Recursos",
        'role'          => "Rol",
        'roles'         => "Roles",
        'specs'         => "Especializaciones",
        '_roles'        => ["Sanador", "DPS cuerpo", "DPS a distancia", "Tanque"],

        'phases'        => "Fases",
        'mode'          => "Modo",
        'modes'         => [-1 => "Cualquiera", "Normal / Normal 10", "Heroico / Normal 25", "Heróico 10", "Heróico 25"],
        'expansions'    => ["World of Warcraft", "The Burning Crusade", "Wrath of the Lich King"],
        'stats'         => ["Fuerza", "Agilidad", "Aguante", "Intelecto", "Espíritu"],
        'sources'       => array(
            "Desconocido",                  "Creado",                       "Encontrado",                   "JcJ",                          "Misión",                       "Vendedor",
            "Entrenador",                   "Descubierto",                  "Redención",                    "Talento",                      "Habilidad Inicial",            "Evento",
            "Logro",                        null,                           "Mercado negro",                "Desencantado",                 "Pescado",                      "Recolectado",
            "Molido",                       "Minado",                       "Prospectar",                   "Robado",                       "Rescatado",                    "Despellejado",
            "Tienda del juego"
        ),
        'languages'     => array(
             1 => "Orco",                    2 => "Darnassiano",             3 => "Taurahe",                 6 => "Enánico",                 7 => "Lengua común",            8 => "Demoníaco",
             9 => "Titánico",               10 => "Thalassiano",            11 => "Dracónico",              12 => "Kalimag",                13 => "Gnomótico",              14 => "Trol",
            33 => "Viscerálico",            35 => "Draenei",                36 => "Zombie",                 37 => "Binario gnomo",          38 => "Binario goblin"
        ),
        'gl'            => [null, "Sublime", "Menor"],
        'si'            => [1 => "Alianza", -1 => "Alianza solamente", 2 => "Horda", -2 => "Horda solamente", 3 => "Ambos"],
        'resistances'   => [null, 'Resistencia a lo Sagrado', 'v', 'Resistencia a la Naturaleza', 'Resistencia a la Escarcha', 'Resistencia a las Sombras', 'Resistencia a lo Arcano'],
        'sc'            => ["Física", "Sagrado", "Fuego", "Naturaleza", "Escarcha", "Sombras", "Arcano"],
        'dt'            => [null, "Magia", "Maldición", "Enfermedad", "Veneno", "Sigilo", "Invisibilidad", null, null, "Enfurecer"],
        'cl'            => [null, "Guerrero", "Paladín", "Cazador", "Pícaro", "Sacerdote", "Caballero de la Muerte", "Chamán", "Mago", "Brujo", null, "Druida"],
        'ra'            => [-2 => "Horda", -1 => "Alianza", "Ambos", "Humano", "Orco", "Enano", "Elfo de la noche", "No-muerto", "Tauren", "Gnomo", "Trol  ", null, "Blood Elf", "Elfo de sangre"],
        'rep'           => ["Odiado", "Hostil", "Adverso", "Neutral", "Amistoso", "Honorable", "Reverenciado", "Exaltado"],
        'st'            => array(
            "Defecto",                      "Forma felina",                 "Árbol de vida",                "Forma de viaje",               "Forma acuática",               "Forma de oso",
            "Ambiente",                     "Necrófago",                    "Forma de oso temible",         "Steve's Ghoul",                "Esqueleto Tharon'ja",          "Luna Negra - Prueba de fuerza",
            "BLB Player",                   "Danza de las Sombras",         "Criatura: oso",                "Criatura: felino",             "Lobo fantasmal",               "Actitud de batalla",
            "Actitud defensiva",            "Actitud rabiosa",              "Test",                         "Zombi",                        "Metamorfosis",                 null,
            null,                           "No-muerto",                    "Furia",                        "Forma de vuelo presto",        "Forma de las Sombras",         "Forma de vuelo",
            "Sigilo",                       "Forma de lechúcico lunar",     "Espíritu redentor"
        ),
        'me'            => array(
            null,                           "Embelesado",                   "Desorientado",                 "Desarmado",                    "Distraído",                    "Huyendo",
            "Agarrado",                     "Enraizado",                    "Pacificado",                   "Silenciado",                   "Dormido",                      "Frenado",
            "Aturdido",                     "Congelado",                    "Incapacitado",                 "Sangrando",                    "Sanacíon",                     "Polimorfado",
            "Desterrado",                   "Protegido",                    "Aprisionado",                  "Montado",                      "Seducido",                     "Girado",
            "Horrorizado",                  "Invulnerable",                 "Interrumpido",                 "Atontado",                     "Descubierto",                  "Invulnerable",
            "Aporreado",                    "Iracundo"
        ),
        'ct'            => array(
            "Sin categoría",                "Bestia",                       "Dragonante",                   "Demonio",                      "Elemental",                    "Gigante",
            "No-muerto",                    "Humanoide",                    "Alimaña",                      "Mecánico",                     "Sin especificar",              "Tótem",
            "Mascota mansa",                "Nube de gas"
        ),
        'fa'            => array(
             1 => "Lobo",                    2 => "Felino",                  3 => "Araña",                   4 => "Oso",                     5 => "Jabalí",                  6 => "Crocolisco",
             7 => "Carroñero",               8 => "Cangrejo",                9 => "Gorila",                 11 => "Raptor",                 12 => "Zancaalta",              20 => "Escórpido",
            21 => "Tortuga",                24 => "Murciélago",             25 => "Hiena",                  26 => "Ave rapaz",              27 => "Serpiente alada",        30 => "Dracohalcón",
            31 => "Devastador",             32 => "Acechador deformado",    33 => "Esporiélago",            34 => "Raya abisal",            35 => "Serpiente",              37 => "Palomilla",
            38 => "Quimera",                39 => "Demosaurio",             41 => "Silítido",               42 => "Gusano",                 43 => "Rinoceronte",            44 => "Avispa",
            45 => "Can del Núcleo",         46 => "Bestia espíritu"
        ),
        'pvpRank'       => array(
            null,                                                           "Soldado / Explorador",                                         "Cabo / Bruto",
            "Sargento / Sargento",                                          "Sargento maestro / Sargento jefe",                             "Sargento mayor / Sargento primero",
            "Caballero / Guardian de Piedra",                               "Teniente caballero / Guardia de sangre",                       "Capitán caballero / Legionario",
            "Campeón caballero / Centurion",                                "Teniente coronel / Campeón",                                   "Comandante / Teniente general",
            "Mariscal / General",                                           "Mariscal de campo / Señor de la Guerra",                       "Gran mariscal / Gran Señor de la Guerra"
        ),
    ),
    'account' => array(
        'title'         => "Cuenta de Aowow",
        'email'         => "Dirección de correo electrónico",
        'continue'      => "Continuar",
        'groups'        => array(
            -1 => "Ninguno",                "Probador",                     "Administrador",                "Editor",                       "Moderador",                    "Burócrata",
            "Desarrollador",                "VIP",                          "Bloggor",                      "Premium",                      "Traductor",                    "Agente de ventas",
            "Gestor de Capturas de pantalla","Gestor de vídeos",            "Partner de API",               "Pendiente"
        ),
        // signIn
        'doSignIn'      => "Iniciar sesión con tu cuenta de Aowow",
        'signIn'        => "Iniciar sesión",
        'user'          => "Nombre de usuario",
        'pass'          => "Contraseña",
        'rememberMe'    => "Seguir conectado",
        'forgot'        => "Se me olvidó mi",
        'forgotUser'    => "Nombre de usuario",
        'forgotPass'    => "Contraseña",
        'accCreate '    => '¿No tienes una cuenta? <a href="?account=signup">¡Crea una ahora!</a>',

        // recovery
        'recoverUser'   => "Pedir nombre de usuario",
        'recoverPass'   => "Reiniciar contraseña: Paso %s de 2",
        'newPass'       => "Nueva Contraseña",

        // creation
        'register'      => "Inscripción: Paso %s de 2",
        'passConfirm'   => "Confirmar contraseña",

        // dashboard
        'ipAddress'     => "Dirección IP",
        'lastIP'        => "Última IP usada",
        'myAccount'     => "Mi cuenta",
        'editAccount'   => "Use el formulario siguienta para actualizar la información de la cuenta.",
        'viewPubDesc'   => 'Mira tu descripción pública en tu <a href="?user=%s">Página de perfil</a>',

        // bans
        'accBanned'     => "Esta cuenta fue cerrada.",
        'bannedBy'      => "Suspendida por",
        'ends'          => "Finaliza en",
        'permanent'     => "La restricción es permanente",
        'reason'        => "Razón",
        'noReason'      => "Ningúna razón fue escrita.",

        // form-text
        'emailInvalid'  => "Esa dirección de correo electrónico no es válida.", // message_emailnotvalid
        'emailNotFound' => "El correo electrónico que ingresaste no está asociado con ninguna cuenta.<br><br>Si olvistaste el correo electronico con el que registraste la cuenta, escribe a ".CFG_CONTACT_EMAIL." para asistencia.",
        'createAccSent' => "Un correo fue enviado a <b>%s</b>. Siga las instrucciones para crear su cuenta.",
        'recovUserSent' => "Un correo fue enviado a <b>%s</b>. Siga las instrucciones para recuperar su nombre de usuario.",
        'recovPassSent' => "Un correo fue enviado a <b>%s</b>. Siga las instrucciones para reiniciar su contraseña.",
        'accActivated'  => 'Su cuenta ha sido activada.<br>Ingrese a <a href="?account=signin&token=%s">para ingresar</a>',
        'userNotFound'  => "El usuario que ha ingresado no existe",
        'wrongPass'     => "La contraseña no es valida.",
        // 'accInactive'   => "That account has not yet been confirmed active.",
        'loginExceeded' => "Ha excedido la cantidad de inicios de sesion con esta IP. Por favor intente en %s",
        'signupExceeded'=> "Ha excedido la cantidad de creaciones de cuentas con esta IP. Por favor intente en %s.",
        'errNameLength' => "Tu nombre de usuario tiene que tener por lo menos cuatro caracteres.", // message_usernamemin
        'errNameChars'  => "Tu nombre de usuario solo puede contener números y letras.", // message_usernamenotvalid
        'errPassLength' => "Tu contraseña tiene que tener por lo menos seis caracteres.", // message_passwordmin
        'passMismatch'  => "La contraseña que ingresó no concuerdan.",
        'nameInUse'     => "El nombre de usuario ya se encuentra utilzado",
        'mailInUse'     => "El correo electrónico ya se encuentra registrado a una cuenta",
        'isRecovering'  => "Esta cuenta ya se encuentra en proceso de recuperación. Siga las intrucciones en su correo o espere %s para que el token expire ",
        'passCheckFail' => "Las contraseñas no son iguales.", // message_passwordsdonotmatch
        'newPassDiff'   => "Su nueva contraseña tiene que ser diferente a su contraseña anterior." // message_newpassdifferent
    ),
    'user' => array(
        'notFound'      => "¡No se encontró el usuario \"%s\"!",
        'removed'       => "(Removido)",
        'joinDate'      => "Se unió",
        'lastLogin'     => "Última visita",
        'userGroups'    => "Rol",
        'consecVisits'  => "Visitas consecutivas",
        'publicDesc'    => "Descripción pública",
        'profileTitle'  => "Perfíl de %s",
        'contributions' => "Contribuciones",
        'uploads'       => "Datos enviados",
        'comments'      => "Comentarios",
        'screenshots'   => "Capturas de pantalla",
        'videos'        => "Vídeos",
        'posts'         => "Mensajes en los foros"
    ),
    'mail' => array(
        'tokenExpires'  => "Este token expira en %s",
        'accConfirm'    => ["Confirmación de Cuenta", "Bienvenido a ".CFG_NAME_SHORT."!\r\n\r\nHaga click en el enlace siguiente para activar su cuenta.\r\n\r\n".HOST_URL."?account=signup&token=%s\r\n\r\nSi usted no solicitó este correo, por favor ignorelo."],
        'recoverUser'   => ["Recuperacion de Usuario", "Siga a este enlace para ingresar.\r\n\r\n".HOST_URL."?account=signin&token=%s\r\n\r\nSi usted no solicitó este correo, por favor ignorelo."],
        'resetPass'     => ["Reinicio de Contraseña", "Siga este enlace para reiniciar su contraseña.\r\n\r\n".HOST_URL."?account=forgotpassword&token=%s\r\n\r\nSi usted no solicitó este correo, por favor ignorelo."]
    ),
    'emote' => array(
        'notFound'      => "Este emoticón no existe",
        'self'          => "Para Usted",
        'target'        => "Para otros con un objetivo",
        'noTarget'      => "Para otros sin un objetivo",
        'isAnimated'    => "Usa una animación",
        'aliases'       => "Aliases",
        'noText'        => "Este emoticón no tiene texto",
    ),
    'enchantment' => array(
        'details'       => "Detalles",
        'activation'    => "Activación",
        'notFound'      => "Este encantamiento no existe.",
        'types'         => array(
            1 => "Prob. Hechizo",            3 => "Equipar Hechizo",           7 => "Usar Hechizo",             8 => "Ranura prismática",
            5 => "Atributos",               2 => "Daño de arma",            6 => "DPS",                     4 => "Defensa"
        )
    ),
    'gameObject' => array(
        'notFound'      => "Este entidad no existe.",
        'cat'           => [0 => "Otros", 9 => "Libros", 3 => "Contenedores", -5 => "Cofres", 25 => "Bancos de peces", -3 => "Hierbas", -4 => "Venas de minerales", -2 => "Misiones", -6 => "Herramientas"],
        'type'          => [              9 => "Libro",  3 => "Contenedore",  -5 => "Cofre",  25 => "",                -3 => "Hierba",  -4 => "Filóne de mineral",  -2 => "Misión",   -6 => ""],
        'unkPosition'   => "No se conoce la ubicación de esta entidad.",
        'npcLootPH'     => 'El <b>%s</b> contiene el botín de la pelea contra <a href="?npc=%d">%s</a>. Aparece al morir.',
        'key'           => "Llave",
        'focus'         => "Foco del hechizo",
        'focusDesc'     => "El hechizo que requiere este foco no puede ser lanzado cerca del objeto",
        'trap'          => "Trampa",
        'triggeredBy'   => "Accionado por",
        'capturePoint'  => "Punto de captura",
        'foundIn'       => "Este entidad se puede encontrar en",
        'restock'       => "Se renueva cada %s."
    ),
    'npc' => array(
        'notFound'      => "Este PNJ no existe.",
        'classification'=> "Clasificación",
        'petFamily'     => "Familia de mascota",
        'react'         => "Reacción",
        'worth'         => "Valor",
        'unkPosition'   => "No se conoce la ubicación de este PNJ.",
        'difficultyPH'  => "Este PNJ es un marcador de posición para un modo diferente de",
        'seat'          => "Asiento",
        'accessory'     => "Accesorio",
        'accessoryFor'  => "Esta criatura es una accesorio para vehículo",
        'quotes'        => "Citas",
        'gainsDesc'     => "Tras acabar con este PNJ ganarás",
        'repWith'       => "reputación con",
        'stopsAt'       => "se detiene en %s",
        'vehicle'       => "Vehículo",
        'stats'         => "Estadisticas",
        'melee'         => "Cuerpo a cuerpo",
        'ranged'        => "Ataque a distancia",
        'armor'         => "Armadura",
        'foundIn'       => "Este PNJ se puede encontrar en",
        'tameable'      => "Domesticable (%s)",
        'waypoint'      => "punto de recorrido",
        'wait'          => "Tiempo de espera",
        'respawnIn'     => "Reingreso en",
        'rank'          => [0 => "Normal", 1 => "Élite", 4 => "Raro", 2 => "Élite raro", 3 => "Jefe"],
        'textRanges'    => [null, "Mandar al área", "Mandar a zona", "Mandar al mapa", "Mandar al mundo"],
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
        'notFound'      => "Este evento del mundo no existe.",
        'start'         => "Empieza",
        'end'           => "Termina",
        'interval'      => "Intervalo",
        'inProgress'    => "El evento está en progreso actualmente",
        'category'      => ["Sin categoría", "Vacacionales", "Periódicos", "Jugador contra Jugador"]
    ),
    'achievement' => array(
        'notFound'      => "Este logro no existe.",
        'criteria'      => "Requisitos",
        'points'        => "Puntos",
        'series'        => "Serie",
        'outOf'         => "de",
        'criteriaType'  => "Criterium Type-Id:",
        'itemReward'    => "Recibirás",
        'titleReward'   => 'Deberías obtener el título "<a href="?title=%d">%s</a>"',
        'slain'         => "matado",
        'reqNumCrt'     => "Requiere",
        'rfAvailable'   => "Disponible en reino: ",
        '_transfer'     => 'Este logro será convertido a <a href="?achievement=%d" class="q%d icontiny tinyspecial" style="background-image: url('.STATIC_URL.'/images/wow/icons/tiny/%s.gif)">%s</a> si lo transfieres a la <span class="icon-%s">%s</span>.',
    ),
    'chrClass' => array(
        'notFound'      => "Esta clase no existe."
    ),
    'race' => array(
        'notFound'      => "Esta raza no existe.",
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
    'privileges' => array(
        'main'          => "Aquí, en AoWoW, puedes conseguir <a href=\"?reputation\">reputación</a>. La forma principal de conseguirla es conseguir que tus comentarios sean votados de forma positiva.<br /><br />Así pues, la reputación es algo que mide, más o menos, cúanto has contribuido a la comunidad.<br /><br />Conforme consigues reputación, te ganas la confianza de la comunidad y tendrás privilegios adicionales. Puedes encontrar una lista completa debajo.",
        'privilege'     => "Privilegio",
        'privileges'    => "Privilegios",
        'requiredRep'   => "Reputación requerida",
        'reqPoints'     => "Este privilegio necesita <b>%s</b> puntos de reputación.",
        '_privileges'   => array(
            null,                                   "Escribir comentarios",                         "Escribir enlaces externos",                        null,
            "Sin CAPTCHAs",                         "Los votos de comentario valen más",            null,                                               null,
            null,                                   "Más votos por día",                            "Dar una valoración positiva a comentarios",        "Dar una valoración negativa a comentarios",
            "Escribir respuestas a comentarios",    "Borde: Poco Común",                            "Borde: Raro",                                      "Borde: Épica",
            "Borde: Legendaria",                    "AoWoW Premium"
        )
    ),
    'zone' => array(
        'notFound'      => "Esta zona no existe.",
        'attunement'    => ["Requisito", "Requisito heroica"],
        'key'           => ["Llave", "Llave heroica"],
        'location'      => "Ubicación",
        'raidFaction'   => "Facción de la banda",
        'boss'          => "Jefe Final",
        'reqLevels'     => "Niveles requeridos: [tooltip=instancereqlevel_tip]%d[/tooltip], [tooltip=lfgreqlevel_tip]%d[/tooltip]",
        'zonePartOf'    => "Este campo es parte de la zona [zone=%d].",
        'autoRez'       => "Resurrección automática",
        'city'          => "Ciudad",
        'territory'     => "Territorio",
        'instanceType'  => "Tipo de instancia",
        'hcAvailable'   => "Modo heroico disponible&nbsp;(%di)",
        'numPlayers'    => "Número de jugadores",
        'noMap'         => "No hay mapa disponible para esta zona.",
        'instanceTypes' => ["Zona",    "Tránsito", "Mazmorra",   "Banda",     "Campo de batalla", "Mazmorra",   "Arena", "Banda", "Banda"],
        'territories'   => ["Alianza", "Horda",    "En disputa", "Santuario", "JcJ",              "JcJ abierto"],
        'cat'           => array(
            "Reinos del Este",          "Kalimdor",                 "Mazmorras",                "Bandas",                   "No las uso",               null,
            "Campos de batalla",        null,                       "Terrallende",              "Arenas",                   "Rasganorte"
        )
    ),
    'quest' => array(
        'notFound'      => "Esta misión no existe.",
        '_transfer'     => 'Esta misión será convertido a <a href="?quest=%d" class="q1">%s</a> si lo transfieres a la <span class="icon-%s">%s</span>.',
        'questLevel'    => 'Nivel %s',
        'requirements'  => 'Requisitos',
        'reqMoney'      => 'Dinero necesario',
        'money'         => 'Dinero',
        'additionalReq' => "Requerimientos adicionales para obtener esta misión",
        'reqRepWith'    => 'Tu reputación con <a href="?faction=%d">%s</a> debe ser %s %s',
        'reqRepMin'     => "de al menos",
        'reqRepMax'     => "menor que",
        'progress'      => "Progreso",
        'provided'      => "Provisto",
        'providedItem'  => "Objeto provisto",
        'completion'    => "Terminación",
        'description'   => "Descripción",
        'playerSlain'   => "Jugadores derrotados",
        'profession'    => "Profesión",
        'timer'         => "Tiempo",
        'loremaster'    => "Maestro cultural",
        'suggestedPl'   => "Jugadores sugeridos",
        'keepsPvpFlag'  => "Mantiene el JcJ activado",
        'daily'         => 'Diaria',
        'weekly'        => "Semanal",
        'monthly'       => "Mensual",
        'sharable'      => "Compartible",
        'notSharable'   => "No se puede compartir",
        'repeatable'    => "Repetible",
        'reqQ'          => "Requiere",
        'reqQDesc'      => "Para aceptar esta misión, debes completar esta(s) mision(es)",
        'reqOneQ'       => "Requiere una de",
        'reqOneQDesc'   => "Para aceptar esta misión debes haber completado alguna de estas misiones",
        'opensQ'        => "Desbloquea",
        'opensQDesc'    => "Es necesario completar esta misión para aceptar esa(s) mision(es)",
        'closesQ'       => "Bloquea",
        'closesQDesc'   => "Si completas esta misión, no podras aceptar esta(s) mision(es)",
        'enablesQ'      => "Activa",
        'enablesQDesc'  => "Cuando estas realizando esta misión, podras tambien aceptar esta(s) mision(es)",
        'enabledByQ'    => "Activada por",
        'enabledByQDesc'=> "Para aceptar esta misión debes haber tener activa alguna de estas misiones",
        'gainsDesc'     => "Cuando completes esta misión ganarás",
        'theTitle'      => 'el título "%s"',
        'mailDelivery'  => "Usted recibirá esta carta%s%s",
        'mailBy'        => ' del <a href="?npc=%d">%s</a>',
        'mailIn'        => " después de %s",
        'unavailable'   => "Esta misión fue marcada como obsoleta y no puede ser obtenida o completada.",
        'experience'    => "experiencia",
        'expConvert'    => "(o %s si se completa al nivel %d)",
        'expConvert2'   => "%s si se completa al nivel %d",
        'chooseItems'   => "Podrás elegir una de estas recompensas",
        'receiveItems'  => "Recibirás",
        'receiveAlso'   => "También recibirás",
        'spellCast'     => "Te van a lanzar el siguiente hechizo",
        'spellLearn'    => "Aprenderás",
        'bonusTalents'  => "%d |4punto:puntos; de talento",
        'spellDisplayed'=> ' (mostrando <a href="?spell=%d">%s</a>)',
        'attachment'    => "Adjunto",
        'questInfo'     => array(
             0 => "Normal",              1 => "Élite",              21 => "Vida",               41 => "JcJ",                62 => "Banda",              81 => "Mazmorra",           82 => "Evento del mundo",
            83 => "Legendaria",         84 => "Escolta",            85 => "Heroica",            88 => "Banda (10)",         89 => "Banda (25)"
        ),
        'cat'           => array(
            0 => array( "Reinos del Este",
                    1 => "Dun Morogh",                       3 => "Tierras Inhóspitas",               4 => "Las Tierras Devastadas",           8 => "Pantano de las Penas",             9 => "Valle de Villanorte",
                   10 => "Bosque del Ocaso",                11 => "Los Humedales",                   12 => "Bosque de Elwynn",                25 => "Montaña Roca Negra",              28 => "Tierras de la Peste del Oeste",
                   33 => "Vega de Tuercespina",             36 => "Montañas de Alterac",             38 => "Loch Modan",                      40 => "Páramos de Poniente",             41 => "Paso de la Muerte",
                   44 => "Montañas Crestagrana",            45 => "Tierras Altas de Arathi",         46 => "Las Estepas Ardientes",           47 => "Tierras del Interior",            51 => "La Garganta de Fuego",
                   85 => "Claros de Tirisfal",             130 => "Bosque de Argénteos",            132 => "Valle de Crestanevada",          139 => "Tierras de la Peste del Este",   154 => "Camposanto",
                  267 => "Laderas de Trabalomas",         1497 => "Entrañas",                      1519 => "Ciudad de Ventormenta",         1537 => "Forjaz",                        2257 => "Tranvía Subterráneo",
                 3430 => "Bosque Canción Eterna",         3431 => "Isla del Caminante del Sol",    3433 => "Tierras Fantasma",              3487 => "Ciudad de Lunargenta",          4080 => "Isla de Quel'Danas",
                 4298 => "El Enclave Escarlata"
            ),
            1 => array( "Kalimdor",
                   14 => "Durotar",                         15 => "Marjal Revolcafango",             16 => "Azshara",                         17 => "Los Baldíos",                    141 => "Teldrassil",
                  148 => "Costa Oscura",                   188 => "Cañada Umbría",                  215 => "Mulgore",                        220 => "Mesa de la Nube Roja",           331 => "Vallefresno",
                  357 => "Feralas",                        361 => "Frondavil",                      363 => "Valle de los Retos",             400 => "Las Mil Agujas",                 405 => "Desolace",
                  406 => "Sierra Espolón",                 440 => "Tanaris",                        490 => "Cráter de Un'Goro",              493 => "Claro de la Luna",               618 => "Cuna del Invierno",
                 1377 => "Silithus",                      1637 => "Orgrimmar",                     1638 => "Cima del Trueno",               1657 => "Darnassus",                     1769 => "Bastión Fauces de Madera",
                 3524 => "Isla Bruma Azur",               3525 => "Isla Bruma de Sangre",          3526 => "Valle Ammen",                   3557 => "El Exodar"
            ),
            2 => array( "Mazmorras",
                  206 => "Fortaleza de Utgarde",           209 => "Castillo de Colmillo Oscuro",    491 => "Horado Rajacieno",               717 => "Las Mazmorras",                  718 => "Cuevas de los Lamentos",
                  719 => "Cavernas de Brazanegra",         721 => "Gnomeregan",                     722 => "Zahúrda Rajacieno",              796 => "Monasterio Escarlata",          1176 => "Zul'Farrak",
                 1196 => "Pináculo de Utgarde",           1337 => "Uldaman",                       1417 => "Templo Sumergido",              1581 => "Las Minas de la Muerte",        1583 => "Cumbre de Roca Negra",
                 1584 => "Profundidades de Roca Negra",   1941 => "Cavernas del Tiempo",           2017 => "Stratholme",                    2057 => "Scholomance",                   2100 => "Maraudon",
                 2366 => "La Ciénaga Negra",              2367 => "Antiguas Laderas de Trabalomas",2437 => "Sima Ígnea",                    2557 => "La Masacre",                    3535 => "Ciudadela del Fuego Infernal",
                 3562 => "Murallas del Fuego Infernal",   3688 => "Auchindoun",                    3713 => "El Horno de Sangre",            3714 => "Las Salas Arrasadas",           3715 => "La Cámara de Vapor",
                 3716 => "La Sotiénaga",                  3717 => "Recinto de los Esclavos",       3789 => "Laberinto de las Sombras",      3790 => "Criptas Auchenai",              3791 => "Salas Sethekk",
                 3792 => "Tumbas de Maná",                3842 => "El Castillo de la Tempestad",   3847 => "El Invernáculo",                3848 => "El Arcatraz",                   3849 => "El Mechanar",
                 3905 => "Reserva Colmillo Torcido",      4100 => "La Matanza de Stratholme",      4131 => "Bancal del Magister",           4196 => "Fortaleza de Drak'Tharon",      4228 => "El Oculus",
                 4264 => "Cámaras de Piedra",             4265 => "El Nexo",                       4272 => "Cámaras de Relámpagos",         4277 => "Azjol-Nerub",                   4415 => "El Bastión Violeta",
                 4416 => "Gundrak",                       4494 => "Ahn'kahet: El Antiguo Reino",   4522 => "Ciudadela de la Corona de Hielo",4723 => "Prueba del Campeón",           4809 => "La Forja de Almas",
                 4813 => "Foso de Saron",                 4820 => "Cámaras de Reflexión"
            ),
            3 => array( "Bandas",
                 1977 => "Zul'Gurub",                     2159 => "Guarida de Onyxia",             2677 => "Guarida de Alanegra",           2717 => "Núcleo de Magma",               3428 => "Ahn'Qiraj",
                 3429 => "Ruinas de Ahn'Qiraj",           3456 => "Naxxramas",                     3457 => "Karazhan",                      3606 => "La Cima Hyjal",                 3607 => "Caverna Santuario Serpiente",
                 3805 => "Zul'Aman",                      3836 => "Guarida de Magtheridon",        3845 => "El Castillo de la Tempestad",   3923 => "Guarida de Gruul",              3959 => "Templo Oscuro",
                 4075 => "Meseta de La Fuente del Sol",   4273 => "Ulduar",                        4493 => "El Sagrario Obsidiana",         4500 => "El Ojo de la Eternidad",        4603 => "La Cámara de Archavon",
                 4722 => "Prueba del Cruzado",            4812 => "Ciudadela de la Corona de Hielo",4987 => "El Sagrario Rubí"
            ),
            4 => array( "Clases",
                  -61 => "Brujo",                          -81 => "Guerrero",                       -82 => "Chamán",                        -141 => "Paladín",                       -161 => "Mago",
                 -162 => "Pícaro",                        -261 => "Cazador",                       -262 => "Sacerdote",                     -263 => "Druida",                        -372 => "Caballero de la Muerte"
            ),
            5 => array( "Profesiones",
                  -24 => "Herboristería",                 -101 => "Pesca",                         -121 => "Herrería",                      -181 => "Alquimia",                      -182 => "Peletería",
                 -201 => "Ingeniería",                    -264 => "Sastrería",                     -304 => "Cocina",                        -324 => "Primeros auxilios",             -371 => "Inscripción",
                 -373 => "Joyería"
            ),
            6 => array( "Campos de batalla",
                 2597 => "Valle de Alterac",              3277 => "Garganta Grito de Guerra",      3358 => "Cuenca de Arathi",              3820 => "Ojo de la Tormenta",            4384 => "Playa de los Ancestros",
                 4710 => "Isla de la Conquista",           -25 => "Campos de batalla"
            ),
            7 => array( "Miscelánea",
                   -1 => "Épica",                         -241 => "Torneo",                        -344 => "Legendaria",                    -365 => "Guerra de Ahn'Qiraj",           -367 => "Reputación",
                 -368 => "Invasión",                     -1010 => "Buscador de Mazmorras"
            ),
            8 => array( "Terrallende",
                 3483 => "Península del Fuego Infernal",  3518 => "Nagrand",                       3519 => "Bosque de Terokkar",            3520 => "Valle Sombraluna",              3521 => "Marisma de Zangar",
                 3522 => "Montañas Filospada",            3523 => "Tormenta Abisal",               3679 => "Skettis",                       3703 => "Ciudad de Shattrath"
            ),
            9 => array( "Eventos del mundo",
                  -22 => "Estacional",                     -41 => "Día de los Muertos",            -364 => "Feria de la Luna Negra",        -366 => "Festival Lunar",                -369 => "Verano",
                 -370 => "Fiesta de la cerveza",          -374 => "Jardín Noble",                  -375 => "Generosidad del Peregrino",     -376 => "Amor en el aire",              -1001 => "Festival de Invierno",
                -1002 => "Los Niños",                    -1003 => "Halloween",                    -1005 => "Festival de la cosecha"
            ),
            10 => array( "Rasganorte",
                   65 => "Cementerio de Dragones",          66 => "Zul'Drak",                        67 => "Las Cumbres Tormentosas",        210 => "Corona de Hielo",                394 => "Colinas Pardas",
                  495 => "Fiordo Aquilonal",              3537 => "Tundra Boreal",                 3711 => "Cuenca de Sholazar",            4024 => "Gelidar",                       4197 => "Conquista del Invierno",
                 4395 => "Dalaran",                       4742 => "Desembarco de Hrothgar"
            ),
           -2 => "Sin categoría"
        )
    ),
    'icon'  => array(
        'notFound'      => "Este icono no existe."
    ),
    'title' => array(
        'notFound'      => "Este título no existe.",
        '_transfer'     => 'Este título será convertido a <a href="?title=%d" class="q1">%s</a> si lo transfieres a la <span class="icon-%s">%s</span>.',
        'cat'           => array(
            "General",      "Jugador contra Jugador",    "Reputación",       "Mazmorras y bandas",     "Misiones",       "Profesiones",      "Eventos del mundo"
        )
    ),
    'skill' => array(
        'notFound'      => "Esta habilidad no existe.",
        'cat'           => array(
            -6 => "Compañeros",         -5 => "Monturas",           -4 => "Habilidades de raza", 5 => "Atributos",          6 => "Habilidades con armas", 7 => "Habilidades de clase", 8 => "Armaduras disponibles",
             9 => "Habilidades secundarias", 10 => "Idiomas",       11 => "Profesiones"
        )
    ),
    'currency' => array(
        'notFound'      => "Esta moneda no existe.",
        'cap'           => "Límite total",
        'cat'           => array(
            1 => "Miscelánea", 2 => "Jugador contra Jugador", 4 => "Clásico", 21 => "Wrath of the Lich King", 22 => "Mazmorra y banda", 23 => "Burning Crusade", 41 => "Prueba", 3 => "No las uso"
        )
    ),
    'sound' => array(
        'notFound'      => "Este sonido no existe.",
        'foundIn'       => "Este sonido se puede encontrar en",
        'goToPlaylist'  => "Ir a mi lista de reproducción",
        'music'         => "Música",
        'intro'         => "Música de introducción",
        'ambience'      => "Ambiente",
        'cat'           => array(
            null,              "Spells",            "User Interface", "Footsteps",   "Weapons Impacts", null,      "Weapons Misses", null,            null,         "Pick Up/Put Down",
            "NPC Combat",      null,                "Errors",         "Nature",      "Objects",         null,      "Death",          "NPC Greetings", null,         "Armor",
            "Footstep Splash", "Water (Character)", "Water",          "Tradeskills", "Misc Ambience",   "Doodads", "Spell Fizzle",   "NPC Loops",     "Zone Music", "Emotes",
            "Narration Music", "Narration",         50 => "Zone Ambience", 52 => "Emitters", 53 => "Vehicles", 1000 => "Mi Lista de Reproducción"
        )
    ),
    'pet'      => array(
        'notFound'      => "Esta familia de mascotas no existe.",
        'exotic'        => "Exótica",
        'cat'           => ["Ferocidad", "Tenacidad", "Astucia"],
        'food'          => ["Carne", "Pescado", "Queso", "Pan", "Hongo", "Fruta", "Carne cruda", "Pescado crudo"]
    ),
    'faction' => array(
        'notFound'      => "Esta facción no existe.",
        'spillover'     => "Excedente de reputación",
        'spilloverDesc' => "Ganar reputación con esta facción tambien una proporción ganada con las facciones listadas a continuación.",
        'maxStanding'   => "Posición máxima",
        'quartermaster' => "Intendente",
        'customRewRate' => "Radio de recompenza personalizado",
        '_transfer'     => '[La reputación de esta facción sera convertida a <a href="?faction=%d" class="q1">%s</a> Si te transfieres a <span class="icon-%s">%s</span>.]',
        'cat'           => array(
            1118 => ["Clásicas", 469 => "Alianza", 169 => "Cártel Bonvapor", 67 => "Horda", 891 => "Fuerzas de la Alianza", 892 => "Fuerzas de la Horda"],
            980  => ["The Burning Crusade", 936 => "Ciudad de Shattrath"],
            1097 => ["Wrath of the Lich King", 1052 => "Expedición de la Horda", 1117 => "Cuenca de Sholazar", 1037 => "Vanguardia de la Alianza"],
            0    => "Otros"
        )
    ),
    'itemset' => array(
        'notFound'      => "Este conjunto de objetos no existe.",
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
        'notFound'      => "Este hechizo no existe.",
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
        '_transfer'     => 'Este hechizo será convertido a <a href="?spell=%d" class="q%d icontiny tinyspecial" style="background-image: url('.STATIC_URL.'/images/wow/icons/tiny/%s.gif)">%s</a> si lo transfieres a la <span class="icon-%s">%s</span>.',
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
        'stackGroup'    => "Grupo de aplilamiento",
        'linkedWith'    => "Asociado con",
        '_scaling'      => "Escala",
        'scaling'       => array(
            'directSP' => "+%.2f%% del poder de hechizo al componente directo",        'directAP' => "+%.2f%% del poder de ataque al componente directo",
            'dotSP'    => "+%.2f%% del poder de hechizo por tick",                   'dotAP'    => "+%.2f%% del poder de ataque por tick"
        ),
        'powerRunes'    => ["Sangre", "Profano", "Escarcha", "Muerte"],
        'powerTypes'    => array(
            // conventional
              -2 => "Salud",               0 => "Maná",                1 => "Ira",                 2 => "Enfoque",             3 => "Energía",             4 => "Felicidad",
               5 => "Runa",                6 => "Poder rúnico",
            // powerDisplay
              -1 => "Munición",          -41 => "Pirita",            -61 => "Presión de vapor", -101 => "Calor",            -121 => "Moco",             -141 => "Poder de sangre",
            -142 => "Cólera"
        ),
        'relItems'      => array(
            'base'    => "<small>Muestra %s relacionados con <b>%s</b></small>",
            'link'    => " u ",
            'recipes' => '<a href="?items=9.%s">objetos de receta</a>',
            'crafted' => '<a href="?items&filter=cr=86;crs=%s;crv=0">objetos fabricados</a>'
        ),
        'cat'           => array(
              7 => "Habilidades",
            -13 => "Glifos",
            -11 => ["Habilidades", 6 => "Armas", 8 => "Armadura", 10 => "Lenguas"],
             -4 => "Habilidades de raza",
             -2 => "Talentos",
             -6 => "Compañeros",
             -5 => ["Monturas", 1=> "Monturas terrestres", 2 => "Monturas voladoras", 3 => "Miscelánea"],
             -3 => array(
                "Habilidades de mascota",   782 => "Necrófago",         270 => "Genérico",              766 => "Acechador deformado",       203 => "Araña",                 655 => "Ave rapaz",             785 => "Avispa",
                788 => "Bestia espíritu",   787 => "Can del Núcleo",    214 => "Cangrejo",              213 => "Carroñero",                 212 => "Crocolisco",            781 => "Demosaurio",            767 => "Devastador",
                763 => "Dracohalcón",       236 => "Escórpido",         765 => "Esporiélago",           209 => "Felino",                    215 => "Gorila",                784 => "Gusano",                654 => "Hiena",
                211 => "Jabalí",            208 => "Lobo",              653 => "Murciélago",            210 => "Oso",                       775 => "Palomilla",             780 => "Quimera",               217 => "Raptor",
                764 => "Raya abisal",       786 => "Rinoceronte",       768 => "Serpiente",             656 => "Serpiente alada",           783 => "Silítido",              251 => "Tortuga",               218 => "Zancaalta",
                761 => "Guardia vil",       189 => "Manáfago",          188 => "Diablillo",             205 => "Súcubo",                    204 => "Abisario"
            ),
             -7 => ["Talentos de mascotas", 411 => "Astucia", 410 => "Ferocidad", 409 => "Tenacidad"],
             11 => array(
                "Profesiones",
                171 => "Alquimia",
                164 => ["Herrería", 9788 => "Forjador de armaduras", 9787 => "Forjador de armas", 17041 => "Maestro forjador de hachas", 17040 => "Maestro forjador de mazas", 17039 => "Maestro forjador de espadas"],
                333 => "Encantamiento",
                202 => ["Ingeniería", 20219 => "Ingeniero gnómico", 20222 => "Ingeniero goblin"],
                182 => "Herboristería",
                773 => "Inscripción",
                755 => "Joyería",
                165 => ["Peletería", 10656 => "Peletería de escamas de dragón", 10658 => "Peletería de elemental", 10660 => "Peletería de tribal"],
                186 => "Minería",
                393 => "Desollar",
                197 => ["Sastrería", 26798 => "Sastería de tela lunar primigenia", 26801 => "Sastrería de tejido de sombras", 26797 => "Sastería de fuego de hechizo"],
            ),
              9 => ["Habilidades secundarias", 185 => "Cocina", 129 => "Primeros auxilios", 356 => "Pesca", 762 => "Equitación"],
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
            0x02A5F3 => "Arma cuerpo a cuerpo",     0x0060 => "Escudo",                     0x04000C => "Arma de ataque a distancia",0xA091 => "Arma cuerpo a cuerpo 1M"
        ),
        'traitShort'    => array(
            'atkpwr'    => "PA",                    'rgdatkpwr' => "PA",                    'splpwr'    => "PH",                    'arcsplpwr' => "PArc",                  'firsplpwr' => "PFue",
            'frosplpwr' => "PEsc",                  'holsplpwr' => "PSag",                  'natsplpwr' => "PNat",                  'shasplpwr' => "PSom",                  'splheal'   => "Sana",
            'str'       => "Fue",                   'agi'       => "Agi",                   'sta'       => "Agu",                   'int'       => "Int",                   'spi'       => "Esp"
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
            null,                                   "Forzar cerradura",                     "Herboristería",                        "Minería",                              "Desactivar trampa",
            "Abrir",                                "Tesoro (DND)",                         "Gemas cálcicas elfas (DND)",           "Cerrar",                               "Activar trampa",
            "Apertura rápida",                      "Cerrado rápido",                       "Abrir ajustando",                      "Abrir de rodillas",                    "Abrir atacando",
            "Gahz'ridian (DND)",                    "Reventar",                             "Apertura JcJ",                         "Cierre JcJ",                           "Pescar",
            "Inscripción",                          "Abrir desde vehículo"
        ),
        'stealthType'   => ["GENERAL", "TRAP"],
        'invisibilityType' => ["GENERAL", 3 => "TRAP", 6 => "DRUNK"],
        'unkEffect'     => 'Unknown Effect',
        'effects'       => array(
/*0-5    */ 'None',                     'Instakill',                'School Damage',            'Dummy',                    'Portal Teleport',          'Teleport Units',
/*6+     */ 'Apply Aura',               'Environmental Damage',     'Drain Power',              'Drain Health',             'Heal',                     'Bind',
/*12+    */ 'Portal',                   'Ritual Base',              'Ritual Specialize',        'Ritual Activate Portal',   'Complete Quest',           'Weapon Damage - No School',
/*18+    */ 'Resurrect with % Health',  'Add Extra Attacks',        'Can Dodge',                'Can Evade',                'Can Parry',                'Can Block',
/*24+    */ 'Create Item',              'Can Use Weapon',           'Know Defense Skill',       'Persistent Area Aura',     'Summon',                   'Leap',
/*30+    */ 'Give Power',               'Weapon Damage - %',        'Trigger Missile',          'Open Lock',                'Transform Item',           'Apply Area Aura - Party',
/*36+    */ 'Learn Spell',              'Know Spell Defense',       'Dispel',                   'Learn Language',           'Dual Wield',               'Jump to Target',
/*42+    */ 'Jump Behind Target',       'Teleport Target to Caster','Learn Skill Step',         'Give Honor',               'Spawn',                    'Trade Skill',
/*48+    */ 'Stealth',                  'Detect Stealthed',         'Summon Object',            'Force Critical Hit',       'Guarantee Hit',            'Enchant Item Permanent',
/*54+    */ 'Enchant Item Temporary',   'Tame Creature',            'Summon Pet',               'Learn Spell - Pet',        'Weapon Damage - Flat',     'Open Item & Fast Loot',
/*60+    */ 'Proficiency',              'Send Script Event',        'Burn Power',               'Modify Threat - Flat',     'Trigger Spell',            'Apply Area Aura - Raid',
/*66+    */ 'Create Mana Gem',          'Heal to Full',             'Interrupt Cast',           'Distract',                 'Distract Move',            'Pickpocket',
/*72+    */ 'Far Sight',                'Forget Talents',           'Apply Glyph',              'Heal Mechanical',          'Summon Object - Temporary','Script Effect',
/*78+    */ 'Attack',                   'Abort All Pending Attacks','Add Combo Points',         'Create House',             'Bind Sight',               'Duel',
/*84+    */ 'Stuck',                    'Summon Player',            'Activate Object',          'Siege Damage',             'Repair Building',          'Siege Building Action',
/*90+    */ 'Kill Credit',              'Threat All',               'Enchant Held Item',        'Force Deselect',           'Self Resurrect',           'Skinning',
/*96+    */ 'Charge',                   'Cast Button',              'Knock Back',               'Disenchant',               'Inebriate',                'Feed Pet',
/*102+   */ 'Dismiss Pet',              'Give Reputation',          'Summon Object (Trap)',     'Summon Object (Battle S.)','Summon Object (#3)',       'Summon Object (#4)',
/*108+   */ 'Dispel Mechanic',          'Summon Dead Pet',          'Destroy All Totems',       'Durability Damage - Flat', 'Summon Demon',             'Resurrect with Flat Health',
/*114+   */ 'Taunt',                    'Durability Damage - %',    'Skin Player Corpse (PvP)', 'AoE Resurrect with % Health','Learn Skill',            'Apply Area Aura - Pet',
/*120+   */ 'Teleport to Graveyard',    'Normalized Weapon Damage', null,                       'Take Flight Path',         'Pull Towards',             'Modify Threat - %',
/*126+   */ 'Spell Steal ',             'Prospect',                 'Apply Area Aura - Friend', 'Apply Area Aura - Enemy',  'Redirect Done Threat %',   'Play Sound',
/*132+   */ 'Play Music',               'Unlearn Specialization',   'Kill Credit2',             'Call Pet',                 'Heal for % of Total Health','Give % of Total Power',
/*138+   */ 'Leap Back',                'Abandon Quest',            'Force Cast',               'Force Spell Cast with Value','Trigger Spell with Value','Apply Area Aura - Pet Owner',
/*144+   */ 'Knockback to Dest.',       'Pull Towards Dest.',       'Activate Rune',            'Fail Quest',               null,                       'Charge to Dest',
/*150+   */ 'Start Quest',              'Trigger Spell 2',          'Summon - Refer-A-Friend',  'Create Tamed Pet',         'Discover Flight Path',     'Dual Wield 2H Weapons',
/*156+   */ 'Add Socket to Item',       'Create Tradeskill Item',   'Milling',                  'Rename Pet',               null,                       'Change Talent Spec. Count',
/*162-167*/ 'Activate Talent Spec.',    null,                       'Remove Aura',              null,                       null,                       'Update Player Phase'
        ),
        'unkAura'       => 'Unknown Aura',
        'auras'         => array(
/*0-   */   'None',                                 'Bind Sight',                           'Possess',                              'Periodic Damage - Flat',               'Dummy',
/*5+   */   'Confuse',                              'Charm',                                'Fear',                                 'Periodic Heal',                        'Mod Attack Speed',
            'Mod Threat',                           'Taunt',                                'Stun',                                 'Mod Damage Done - Flat',               'Mod Damage Taken - Flat',
            'Damage Shield',                        'Stealth',                              'Mod Stealth Detection Level',          'Invisibility',                         'Mod Invisibility Detection Level',
            'Regenerate Health - %',                'Regenerate Power - %',                 'Mod Resistance - Flat',                'Periodically Trigger Spell',           'Periodically Give Power',
/*25+  */   'Pacify',                               'Root',                                 'Silence',                              'Reflect Spells',                       'Mod Stat - Flat',
            'Mod Skill - Temporary',                'Increase Run Speed %',                 'Mod Mounted Speed %',                  'Decrease Run Speed %',                 'Mod Maximum Health - Flat',
            'Mod Maximum Power - Flat',             'Shapeshift',                           'Spell Effect Immunity',                'Spell Aura Immunity',                  'Spell School Immunity',
            'Damage Immunity',                      'Dispel Type Immunity',                 'Proc Trigger Spell',                   'Proc Trigger Damage',                  'Track Creatures',
            'Track Resources',                      'Ignore All Gear',                      'Mod Parry %',                          null,                                   'Mod Dodge %',
/*50+  */   'Mod Critical Healing Amount %',        'Mod Block %',                          'Mod Physical Crit Chance',             'Periodically Drain Health',            'Mod Physical Hit Chance',
            'Mod Spell Hit Chance',                 'Transform',                            'Mod Spell Crit Chance',                'Increase Swim Speed %',                'Mod Damage Done Versus Creature',
            'Pacify & Silence',                     'Mod Size %',                           'Periodically Transfer Health',         'Periodic Transfer Power',              'Periodic Drain Power',
            'Mod Spell Haste % (not stacking)',     'Feign Death',                          'Disarm',                               'Stalked',                              'Mod Absorb School Damage',
            'Extra Attacks',                        'Mod Spell School Crit Chance',         'Mod Spell School Power Cost - %',      'Mod Spell School Power Cost - Flat',   'Reflect Spells School From School',
/*75+  */   'Force Language',                       'Far Sight',                            'Mechanic Immunity',                    'Mounted',                              'Mod Damage Done - %',
            'Mod Stat - %',                         'Split Damage - %',                     'Underwater Breathing',                 'Mod Base Resistance - Flat',           'Mod Health Regeneration - Flat',
            'Mod Power Regeneration - Flat',        'Create Item on Death',                 'Mod Damage Taken - %',                 'Mod Health Regeneration - %',          'Periodic Damage - %',
            'Mod Resist Chance',                    'Mod Aggro Range',                      'Prevent Fleeing',                      'Unattackable',                         'Interrupt Power Decay',
            'Ghost',                                'Spell Magnet',                         'Absorb Damage - Mana Shield',          'Mod Skill Value',                      'Mod Attack Power - Flat',
/*100+ */   'Always Show Debuffs',                  'Mod Resistance - %',                   'Mod Melee Attack Power vs Creature',   'Mod Total Threat - Temporary',         'Water Walking',
            'Feather Fall',                         'Levitate / Hover',                     'Add Modifier - Flat',                  'Add Modifier - %',                     'Proc Spell on Target',
            'Mod Power Regeneration - %',           'Intercept % of Attacks Against Target','Override Class Script',                'Mod Ranged Damage Taken - Flat',       'Mod Ranged Damage Taken - %',
            'Mod Healing Taken - Flat',             'Allow % of Health Regen During Combat','Mod Mechanic Resistance',              'Mod Healing Taken - %',                'Share Pet Tracking',
            'Untrackable',                          'Beast Lore',                           'Mod Offhand Damage Done %',            'Mod Target Resistance - Flat',         'Mod Ranged Attack Power - Flat',
/*125+ */   'Mod Melee Damage Taken - Flat',        'Mod Melee Damage Taken - %',           'Mod Attacker Ranged Attack Power',     'Possess Pet',                          'Increase Run Speed % - Stacking',
            'Incerase Mounted Speed % - Stacking',  'Mod Ranged Attack Power vs Creature',  'Mod Maximum Power - %',                'Mod Maximum Health - %',               'Allow % of Mana Regen During Combat',
            'Mod Healing Done - Flat',              'Mod Healing Done - %',                 'Mod Stat - %',                         'Mod Melee Haste %',                    'Force Reputation',
            'Mod Ranged Haste %',                   'Mod Ranged Ammo Haste %',              'Mod Base Resistance - %',              'Mod Resistance - Flat (not stacking)', 'Safe Fall',
            'Increase Pet Talent Points',           'Allow Exotic Pets Taming',             'Mechanic Immunity Mask',               'Retain Combo Points',                  'Reduce Pushback Time %',
/*150+ */   'Mod Shield Block Value - %',           'Track Stealthed',                      'Mod Player Aggro Range',               'Split Damage - Flat',                  'Mod Stealth Level',
            'Mod Underwater Breathing %',           'Mod All Reputation Gained by %',       'Done Pet Damage Multiplier',           'Mod Shield Block Value - Flat',        'No PvP Credit',
            'Mod AoE Avoidance',                    'Mod Health Regen During Combat',       'Mana Burn',                            'Mod Melee Critical Damage %',          null,
            'Mod Attacker Melee Attack Power',      'Mod Melee Attack Power - %',           'Mod Ranged Attack Power - %',          'Mod Damage Done vs Creature',          'Mod Crit Chance vs Creature',
            'Change Object Visibility for Player',  'Mod Run Speed (not stacking)',         'Mod Mounted Speed (not stacking)',     null,                                   'Mod Spell Power by % of Stat',
/*175+ */   'Mod Healing Power by % of Stat',       'Spirit of Redemption',                 'AoE Charm',                            'Mod Debuff Resistance - %',            'Mod Attacker Spell Crit Chance',
            'Mod Spell Power vs Creature',          null,                                   'Mod Resistance by % of Stat',          'Mod Threat % of Critical Hits',        'Mod Attacker Melee Hit Chance',
            'Mod Attacker Ranged Hit Chance',       'Mod Attacker Spell Hit Chance',        'Mod Attacker Melee Crit Chance',       'Mod Attacker Ranged Crit Chance',      'Mod Rating',
            'Mod Reputation Gained %',              'Limit Movement Speed',                 'Mod Attack Speed %',                   'Mod Haste % (gain)',                   'Mod Target School Absorb %',
            'Mod Target School Absorb for Ability', 'Mod Cooldowns',                        'Mod Attacker Crit Chance',             null,                                   'Mod Spell Hit Chance',
/*200+ */   'Mod Kill Experience Gained %',         'Can Fly',                              'Ignore Combat Result',                 'Mod Attacker Melee Crit Damage %',     'Mod Attacker Ranged Crit Damage %',
            'Mod Attacker Spell Crit Damage %',     'Mod Vehicle Flight Speed %',           'Mod Mounted Flight Speed %',           'Mod Flight Speed %',                   'Mod Mounted Flight Speed % (always)',
            'Mod Vehicle Speed % (always)',         'Mod Flight Speed % (not stacking)',    'Mod Ranged Attack Power by % of Stat', 'Mod Rage Generated from Damage Dealt', 'Tamed Pet Passive',
            'Arena Preparation',                    'Mod Spell Haste %',                    'Killing Spree',                        'Mod Ranged Haste %',                   'Mod Mana Regeneration by % of Stat',
            'Mod Combat Rating by % of Stat',       'Ignore Threat',                        null,                                   'Raid Proc from Charge',                null,
/*225+ */   'Raid Proc from Charge with Value',     'Periodic Dummy',                       'Periodically Trigger Spell with Value','Detect Stealth',                       'Mod AoE Damage Taken %',
            'Mod Maximum Health - Flat (no stacking)','Proc Trigger Spell with Value',      'Mod Mechanic Duration %',              'Change other Humanoid Display',        'Mod Mechanic Duration % (not stacking)',
            'Mod Dispel Resistance %',              'Control Vehicle',                      'Mod Spell Power by % of Attack Power', 'Mod Healing Power by % of Attack Power','Mod Size % (not stacking)',
            'Mod Expertise',                        'Force Move Forward',                   'Mod Spell & Healing Power by % of Int','Faction Override',                     'Comprehend Language',
            'Mod Aura Duration by Dispel Type',   'Mod Aura Duration by Dispel Type (not stacking)', 'Clone Caster',                'Mod Combat Result Chance',             'Convert Rune',
/*250+ */   'Mod Maximum Health - Flat (stacking)', 'Mod Enemy Dodge Chance',               'Mod Haste % (loss)',                   'Mod Critical Block Chance',            'Disarm Offhand',
            'Mod Mechanic Damage Taken %',          'No Reagent Cost',                      'Mod Target Resistance by Spell Class', 'Mod Spell Visual',                     'Mod Periodic Healing Taken %',
            'Screen Effect',                        'Phase',                                'Ability Ignore Aurastate',             'Allow Only Ability',                   null,
            null,                                   null,                                   'Cancel Aura Buffer at % of Caster Health','Mod Attack Power by % of Stat',     'Ignore Target Resistance',
            'Ignore Target Resistance for Ability', 'Mod Damage Taken % from Caster',       'Ignore Swing Timer Reset',             'X-Ray',                                'Ability Consume No Ammo',
/*275+ */   'Mod Ability Ignore Shapeshift',        'Mod Mechanic Damage Done %',           'Mod Max Affected Targets',             'Disarm Ranged Weapon',                 'Spawn Effect',
            'Mod Armor Penetration %',              'Mod Honor Gain %',                     'Mod Base Health %',                    'Mod Healing Taken % from Caster',      'Linked Aura',
            'Mod Attack Power by School Resistance','Allow Periodic Ability to Crit',       'Mod Spell Deflect Chance',             'Ignore Hit Direction',                 null,
            'Mod Crit Chance',                      'Mod Quest Experience Gained %',        'Open Stable',                          'Override Spells',                      'Prevent Power Regeneration',
            null,                                   'Set Vehicle Id',                       'Spirit Burst',                         'Strangulate',                          null,
/*300+ */   'Share Damage %',                       'Mod Absorb School Healing',            null,                                   'Mod Damage Done vs Aurastate - %',     'Fake Inebriate',
            'Mod Minimum Speed %',                  null,                                   'Heal Absorb Test',                     'Mod Critical Strike Chance for Caster',null,
            'Mod Pet AoE Damage Avoidance',         null,                                   null,                                   null,                                   'Prevent Ressurection',
/* -316*/   'Underwater Walking',                   'Periodic Haste'
        )
    ),
    'item' => array(
        'notFound'      => "Este objeto no existe.",
        'armor'         => "%s armadura",
        'block'         => "%s bloqueo",
        'charges'       => "%d |4carga:cargas;",
        'locked'        => "Cerrado",
        'ratingString'  => "%s&nbsp;@&nbsp;L%s",
        'heroic'        => "Heroico",
        'startQuest'    => "Este objeto inicia una misión",
        'bagSlotString' => '%2$s de %1$d casillas',
        'fap'           => "poder de ataque feral",
        'durability'    => "Durabilidad %d / %d",
        'realTime'      => "tiempo real",
        'conjured'      => "Objeto mágico",
        'sellPrice'     => "Precio de venta",
        'itemLevel'     => "Nivel de objeto %d",
        'randEnchant'   => "&lt;Encantamiento aleatorio&gt",
        'readClick'     => "&lt;Click derecho para leer&gt",
        'openClick'     => "&lt;Click derecho para abrir&gt",
        'setBonus'      => "(%d) Bonif.: %s",
        'setName'       => "%s (%d/%d)",
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
        '_transfer'     => 'Este objeto será convertido a <a href="?item=%d" class="q%d icontiny tinyspecial" style="background-image: url('.STATIC_URL.'/images/wow/icons/tiny/%s.gif)">%s</a> si lo transfieres a la <span class="icon-%s">%s</span>.',
        '_unavailable'  => "Este objeto no está disponible para los jugadores.",
        '_rndEnchants'  => "Encantamientos aleatorios",
        '_chance'       => "(probabilidad %s%%)",
        'slot'          => "Casilla",
        '_quality'      => "Calidad",
        'usableBy'      => "Usable por",
        'buyout'        => "Precio de venta en subasta",
        'each'          => "cada uno",
        'tabOther'      => "Otros",
        'reqMinLevel'   => "Necesitas ser de nivel %d",
        'reqLevelRange' => "Requiere un nivel entre %d y %d (%s)",
        'unique'        => ["Único",          "Único (%d)", "Único: %s (%d)"         ],
        'uniqueEquipped'=> ["Único-Equipado", null,         "Único-Equipado: %s (%d)"],
        'speed'         => "Veloc.",
        'dps'           => "(%.1f daño por segundo)",
        'damage'        => array(                           // *DAMAGE_TEMPLATE*
                        //  basic,                          basic /w school,                add basic,                  add basic /w school
            "single"    => ["%d Daño",                      "%d %s Daño",                   "+ %d daño",                "+%d %s daños"            ],
            "range"     => ["%d - %d Daño",                 "%d - %d daño de %s",           "+ %d: %d daño",            "+%d - %d daño de %s"     ],
            'ammo'      => ["Añade %g daño por segundo",    "Añade %g %s daño por segundo", "+ %g daño por segundo",    "+ %g %s daño por segundo"]
        ),
        'gems'          => "Gemas",
        'socketBonus'   => "Bono de ranura: %s",
        'socket'        => array(
            "Ranura meta",          "Ranura roja",      "Ranura amarilla",          "Ranura azul",            -1 => "Ranura prismática"
        ),
        'gemColors'     => array(                           // *_GEM
            "meta",                 "roja(s)",          "amarilla(s)",              "azul(es)"
        ),
        'gemConditions' => array(                           // ENCHANT_CONDITION_*
            2 => "menos de %d |4gema:gemas; %s",
            3 => "más gemas %s que gemas %s",
            5 => "al menos %d |4gema:gemas; %s"
        ),
        'reqRating'     => array(                           // ITEM_REQ_ARENA_RATING*
            "Requiere un índice de arena personal y de equipo de %d",
            "Requiere un índice de arena personal y de equipo de %d|nen la rama de 3c3 o de 5c5",
            "Requiere un índice de arena personal y de equipo de %d|nen la rama de 5c5"
        ),
        'quality'       => array(
            "Pobre",                "Común",            "Poco Común",               "Raro",
            "Épica",                "Legendaria",       "Artefacto",                "Reliquia"
        ),
        'trigger'       => array(
            "Uso: ",                "Equipar: ",        "Probabilidad al acertar: ", "",                            "",
            "",                     ""
        ),
        'bonding'       => array(
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
        'cat'           => array(
             2 => "Armas",                                  // self::$spell['weaponSubClass']
             4 => array("Armadura", array(
                 1 => "Armaduras de tela",           2 => "Armaduras de cuero",      3 => "Armaduras de malla",      4 => "Armaduras de placas",     6 => "Escudos",                 7 => "Tratados",
                 8 => "Ídolos",                      9 => "Tótems",                 10 => "Sigilos",                -6 => "Capas",                  -5 => "Cosillas de la mano izquierda",-8 => "Camisas",
                -7 => "Tabardos",                   -3 => "Amuletos",               -2 => "Anillos",                -4 => "Abalorios",               0 => "Misceláneo (Armaduras)",
            )),
             1 => array("Contenedores", array(
                 0 => "Bolsas",                      3 => "Bolsas de encantamiento", 4 => "Bolsas de ingeniería",    5 => "Bolsas de gemas",         2 => "Bolsas de hierbas",       8 => "Bolsas de inscripción",
                 7 => "Bolsas de peletería",         6 => "Bolsas de minería",       1 => "Bolsas de almas"
            )),
             0 => array("Consumibles", array(
                -3 => "Mejoras de objetos temporales",                               6 => "Mejoras de objetos permanentes",                          2 => ["Elixires", [1 => "Elixires de batalla", 2 => "Elixires guardiánes"]],
                 1 => "Pociones",                    4 => "Pergaminos",              7 => "Vendas",                  0 => "Consumibles",             3 => "Frascos",                 5 => "Comidas y bebidas",
                 8 => "Otro (Consumibles)"
            )),
            16 => array("Glifos", array(
                 1 => "Glifos de guerrero",          2 => "Glifos de paladín",       3 => "Glifos de cazador",       4 => "Glifos de pícaro",        5 => "Glifos de sacerdote",     6 => "Glifos de caballero de la muerte",
                 7 => "Glifos de chamán",            8 => "Glifos de mago",          9 => "Glifos de brujo",        11 => "Glifos de druida"
            )),
             7 => array("Objetos comerciables", array(
                14 => "Encantamientos de armaduras", 5 => "Tela",                    3 => "Instrumentos",           10 => "Elemental",              12 => "Encantamiento",           2 => "Explosivos",
                 9 => "Hierbas",                     4 => "Joyería",                 6 => "Cuero",                  13 => "Materiales",              8 => "Carne",                   7 => "Metal y piedra",
                 1 => "Piezas",                     15 => "Encantamientos de armas",11 => "Otro (Objetos comerciables)"
             )),
             6 => ["Proyectiles", [                  2 => "Flechas",                 3 => "Balas"             ]],
            11 => ["Carcajs",     [                  2 => "Carcajs",                 3 => "Bolsas de munición"]],
             9 => array("Recetas", array(
                 0 => "Libros",                      6 => "Recetas de alquimia",     4 => "Diseños de herrería",     5 => "Recetas de cocina",       8 => "Fórmulas de encantamiento",3 => "Esquemas de ingeniería",
                 7 => "Libros de primeros auxilios", 9 => "Libros de pesca",        11 => "Técnicas de Inscripción",10 => "Bocetos de joyería",      1 => "Patrones de peletería",  12 => "Guías de minería",
                 2 => "Patrones de sastrería"
            )),
             3 => array("Gemas", array(
                 6 => "Gemas meta",                  0 => "Gemas rojas",             1 => "Gemas azules",            2 => "Gemas amarillas",         3 => "Gemas moradas",           4 => "Gemas verdes",
                 5 => "Gemas naranjas",              8 => "Gemas centelleantes",     7 => "Gemas simples"
            )),
            15 => array("Miscelánea", array(
                -2 => "Tokens de armadura",          3 => "Fiesta",                  0 => "Chatarras",               1 => "Componentes",             5 => "Monturas",               -7 => "Monturas voladoras",
                 2 => "Compañeros",                  4 => "Otro (Misceláneo)"
            )),
            10 => "Monedas",
            12 => "Misión",
            13 => "Llaves",
        ),
        'statType'      => array(
            "Maná",
            "Salud",
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
            "Mejora tu índice de golpe %d p.",
            "Mejora tu índice de golpe crítico %d p.",
            "Mejora tu índice de evasión %d p.",
            "Mejora tu índice de evasión de golpes críticos %d p.",
            "Mejora tu índice de temple %d p.",
            "Mejora tu índice de celeridad %d p.",
            "Aumenta tu índice de pericia %d p.",
            "Aumenta el poder de ataque %d p.",
            "Aumenta el poder de ataque a distancia %d p.",
            "Aumenta el poder de ataque %d p. solo con las formas de gato, oso, oso temible y lechúcico lunar.",
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
    )
);

?>
