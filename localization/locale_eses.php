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

        // profiler
        'realm'         => "Reino",
        'region'        => "Región",
        'viewCharacter' => "Mirar Personaje",
        '_cpHead'       => "Perfiles de Personaje",
        '_cpHint'       => "l <b>Gestor de perfiles</b> te permite editar tu personaje, encontrar mejoras de equipo, comprobar tu gearscore, ¡y más!",
        '_cpHelp'       => "Para comenzar, sigue los pasos abajo indicados. Si quieres más información, revisa nuestra amplia <a href=\"?help=profiler\">página de ayuda</a>.",
        '_cpFooter'     => "Si quieres una búsqueda más refinada, prueba con nuestras opciones de <a href=\"?profiles\">búsqueda avanzada</a>. También puedes crear un <a href=\"?profile&amp;new\">perfil nuevo personalizado</a>.",

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
            null,                           null,                           "Forma de oso temible",         null,                           null,                           null,
            null,                           "Danza de las Sombras",         null,                           null,                           "Lobo fantasmal",               "Actitud de batalla",
            "Actitud defensiva",            "Actitud rabiosa",              null,                           null,                           "Metamorfosis",                 null,
            null,                           null,                           null,                           "Forma de vuelo presto",        "Forma de las Sombras",         "Forma de vuelo",
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
                3430 => "Bosque Canción Eterna",         130 => "Bosque de Argénteos",            12 => "Bosque de Elwynn",               10 => "Bosque del Ocaso",             3487 => "Ciudad de Lunargenta",
                1519 => "Ciudad de Ventormenta",          85 => "Claros de Tirisfal",            279 => "Cráter de Dalaran",               1 => "Dun Morogh",                   1497 => "Entrañas",
                1537 => "Forjaz",                       4080 => "Isla de Quel'Danas",             51 => "La Garganta de Fuego",          267 => "Laderas de Trabalomas",          46 => "Las Estepas Ardientes",
                   4 => "Las Tierras Devastadas",         38 => "Loch Modan",                     11 => "Los Humedales",                  25 => "Montaña Roca Negra",             44 => "Montañas Crestagrana",
                  36 => "Montañas de Alterac",             8 => "Pantano de las Penas",           41 => "Paso de la Muerte",              40 => "Páramos de Poniente",            45 => "Tierras Altas de Arathi",
                3433 => "Tierras Fantasma",                3 => "Tierras Inhóspitas",            139 => "Tierras de la Peste del Este",   28 => "Tierras de la Peste del Oeste",4298 => "Tierras de la Peste: El Enclave Escarlata",
                  47 => "Tierras del Interior",         2257 => "Tranvía Subterráneo",            33 => "Vega de Tuercespina"
            ),
            1 => array( "Kalimdor",
                  16 => "Azshara",                      1216 => "Bastión Fauces de Madera",     1638 => "Cima del Trueno",               493 => "Claro de la Luna",              148 => "Costa Oscura",
                 490 => "Cráter de Un'Goro",             618 => "Cuna del Invierno",            1657 => "Darnassus",                     405 => "Desolace",                       14 => "Durotar",
                3557 => "El Exodar",                     357 => "Feralas",                       361 => "Frondavil",                    3524 => "Isla Bruma Azur",              3525 => "Isla Bruma de Sangre",
                 400 => "Las Mil Agujas",                 17 => "Los Baldíos",                    15 => "Marjal Revolcafango",           215 => "Mulgore",                      1637 => "Orgrimmar",
                 406 => "Sierra Espolón",               1377 => "Silithus",                      440 => "Tanaris",                       141 => "Teldrassil",                    331 => "Vallefresno"
            ),
            8 => array( "Terrallende",
                3519 => "Bosque de Terokkar",           3703 => "Ciudad de Shattrath",          3521 => "Marisma de Zangar",            3522 => "Montañas Filospada",           3518 => "Nagrand",
                3483 => "Península del Fuego Infernal", 3679 => "Skettis",                      3523 => "Tormenta Abisal",              3520 => "Valle Sombraluna"
            ),
           10 => array( "Rasganorte",
                  65 => "Cementerio de Dragones",        394 => "Colinas Pardas",               4197 => "Conquista del Invierno",        210 => "Corona de Hielo",              3711 => "Cuenca de Sholazar",
                4395 => "Dalaran",                      4742 => "Desembarco de Hrothgar",        495 => "Fiordo Aquilonal",             4024 => "Gelidar",                        67 => "Las Cumbres Tormentosas",
                3537 => "Tundra Boreal",                  66 => "Zul'Drak"
            ),
            6 => array( "Campos de batalla",
                 -25 => "Campos de batalla",            3358 => "Cuenca de Arathi",             3277 => "Garganta Grito de Guerra",     4710 => "Isla de la Conquista",         3820 => "Ojo de la Tormenta",
                4384 => "Playa de los Ancestros",       2597 => "Valle de Alterac"
            ),
            4 => array( "Clases",
                 -61 => "Brujo",                        -372 => "Caballero de la Muerte",       -261 => "Cazador",                       -82 => "Chamán",                       -263 => "Druida",
                 -81 => "Guerrero",                     -161 => "Mago",                         -141 => "Paladín",                      -162 => "Pícaro",                       -262 => "Sacerdote"
            ),
            2 => array( "Mazmorras",
                4494 => "Ahn'kahet: El Antiguo Reino",  2367 => "Antiguas Laderas de Trabalomas", 4277 => "Azjol-Nerub",                4131 => "Bancal del Magister",           209 => "Castillo de Colmillo Oscuro",
                 719 => "Cavernas de Brazanegra",       1941 => "Cavernas del Tiempo",          3535 => "Ciudadela del Fuego Infernal", 3790 => "Criptas Auchenai",              718 => "Cuevas de los Lamentos",
                1583 => "Cumbre de Roca Negra",         4264 => "Cámaras de Piedra",            4820 => "Cámaras de Reflexión",         4272 => "Cámaras de Relámpagos",        3848 => "El Arcatraz",
                4415 => "El Bastión Violeta",           3845 => "El Castillo de la Tempestad",  3713 => "El Horno de Sangre",           3847 => "El Invernáculo",               3849 => "El Mechanar",
                4120 => "El Nexo",                      4228 => "El Oculus",                    4196 => "Fortaleza de Drak'Tharon",      206 => "Fortaleza de Utgarde",         4813 => "Foso de Saron",
                 721 => "Gnomeregan",                   4416 => "Gundrak",                       491 => "Horado Rajacieno",             2366 => "La Ciénaga Negra",             3715 => "La Cámara de Vapor",
                4809 => "La Forja de Almas",            2557 => "La Masacre",                   4100 => "La Matanza de Stratholme",     3716 => "La Sotiénaga",                 3789 => "Laberinto de las Sombras",
                 717 => "Las Mazmorras",                1581 => "Las Minas de la Muerte",       3714 => "Las Salas Arrasadas",          2100 => "Maraudon",                      796 => "Monasterio Escarlata",
                3562 => "Murallas del Fuego Infernal",  1196 => "Pináculo de Utgarde",          1584 => "Profundidades de Roca Negra",  4723 => "Prueba del Campeón",           3717 => "Recinto de los Esclavos",
                3905 => "Reserva Colmillo Torcido",     3791 => "Salas Sethekk",                2057 => "Scholomance",                  2437 => "Sima Ígnea",                   2017 => "Stratholme",
                1477 => "Templo Sumergido",             3792 => "Tumbas de Maná",               1337 => "Uldaman",                       722 => "Zahúrda Rajacieno",            1176 => "Zul'Farrak"
            ),
            5 => array( "Profesiones",
                -181 => "Alquimia",                     -304 => "Cocina",                        -24 => "Herboristería",                -121 => "Herrería",                     -201 => "Ingeniería",
                -371 => "Inscripción",                  -373 => "Joyería",                      -182 => "Peletería",                    -101 => "Pesca",                        -324 => "Primeros auxilios",
                -264 => "Sastrería"
            ),
            3 => array( "Bandas",
                3428 => "Ahn'Qiraj",                    3607 => "Caverna Santuario Serpiente",  4812 => "Ciudadela de la Corona de Hielo", 3842 => "El Castillo de la Tempestad", 4500 => "El Ojo de la Eternidad",
                4493 => "El Sagrario Obsidiana",        3959 => "El Templo Oscuro",             2677 => "Guarida de Alanegra",          3923 => "Guarida de Gruul",             3836 => "Guarida de Magtheridon",
                2159 => "Guarida de Onyxia",            3457 => "Karazhan",                     4603 => "La Cámara de Archavon",        3606 => "La Cima Hyjal",                4075 => "Meseta de La Fuente del Sol",
                3456 => "Naxxramas",                    2717 => "Núcleo de Magma",              4722 => "Prueba del Cruzado",           3429 => "Ruinas de Ahn'Qiraj",          4273 => "Ulduar",
                 805 => "Zul'Aman",                     1977 => "Zul'Gurub"
            ),
            9 => array( "Eventos del mundo",
                -370 => "Fiesta de la cerveza",        -1002 => "Los Niños",                    -364 => "Feria de la Luna Negra",        -41 => "Día de los Muertos",          -1003 => "Halloween",
               -1005 => "Festival de la cosecha",       -376 => "Amor en el aire",              -366 => "Festival Lunar",               -369 => "Solsticio",                   -1006 => "Año nuevo",
                -375 => "Generosidad",                  -374 => "Jardín Noble",                -1001 => "Festival de Invierno"
            ),
            7 => array( "Miscelánea",
                -365 => "Guerra de Ahn'Qiraj",         -1010 => "Buscador de Mazmorras",          -1 => "Épica",                        -344 => "Legendaria",                   -367 => "Reputación",
                -368 => "Invasión",                     -241 => "Torneo"
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
        'powerRunes'    => ["Escarcha", "Profano", "Sangre", "Muerte"],
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
             -5 => "Monturas",
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
/*6+     */ 'Apply Aura',               'Environmental Damage',     'Power Drain',              'Health Leech',             'Heal',                     'Bind',
/*12+    */ 'Portal',                   'Ritual Base',              'Ritual Specialize',        'Ritual Activate Portal',   'Quest Complete',           'Weapon Damage NoSchool',
/*18+    */ 'Resurrect',                'Add Extra Attacks',        'Dodge',                    'Evade',                    'Parry',                    'Block',
/*24+    */ 'Create Item',              'Can Use Weapon',           'Defense',                  'Persistent Area Aura',     'Summon',                   'Leap',
/*30+    */ 'Energize',                 'Weapon Damage Percent',    'Trigger Missile',          'Open Lock',                'Summon Change Item',       'Apply Area Aura Party',
/*36+    */ 'Learn Spell',              'Spell Defense',            'Dispel',                   'Language',                 'Dual Wield',               'Jump',
/*42+    */ 'Jump Dest',                'Teleport Units Face Caster','Skill Step',              'Add Honor',                'Spawn',                    'Trade Skill',
/*48+    */ 'Stealth',                  'Detect',                   'Trans Door',               'Force Critical Hit',       'Guarantee Hit',            'Enchant Item Permanent',
/*54+    */ 'Enchant Item Temporary',   'Tame Creature',            'Summon Pet',               'Learn Pet Spell',          'Weapon Damage Flat',       'Create Random Item',
/*60+    */ 'Proficiency',              'Send Event',               'Power Burn',               'Threat',                   'Trigger Spell',            'Apply Area Aura Raid',
/*66+    */ 'Create Mana Gem',          'Heal Max Health',          'Interrupt Cast',           'Distract',                 'Pull',                     'Pickpocket',
/*72+    */ 'Add Farsight',             'Untrain Talents',          'Apply Glyph',              'Heal Mechanical',          'Summon Object Wild',       'Script Effect',
/*78+    */ 'Attack',                   'Sanctuary',                'Add Combo Points',         'Create House',             'Bind Sight',               'Duel',
/*84+    */ 'Stuck',                    'Summon Player',            'Activate Object',          'WMO Damage',               'WMO Repair',               'WMO Change',
/*90+    */ 'Kill Credit',              'Threat All',               'Enchant Held Item',        'Force Deselect',           'Self Resurrect',           'Skinning',
/*96+    */ 'Charge',                   'Cast Button',              'Knock Back',               'Disenchant',               'Inebriate',                'Feed Pet',
/*102+   */ 'Dismiss Pet',              'Reputation',               'Summon Object Slot1',      'Summon Object Slot2',      'Summon Object Slot3',      'Summon Object Slot4',
/*108+   */ 'Dispel Mechanic',          'Summon Dead Pet',          'Destroy All Totems',       'Durability Damage',        'Summon Demon',             'Resurrect Flat',
/*114+   */ 'Attack Me',                'Durability Damage Percent','Skin Player Corpse',       'Spirit Heal',              'Skill',                    'Apply Area Aura Pet',
/*120+   */ 'Teleport Graveyard',       'Weapon Damage Normalized', null,                       'Send Taxi',                'Pull Towards',             'Modify Threat Percent',
/*126+   */ 'Steal Beneficial Buff',    'Prospecting',              'Apply Area Aura Friend',   'Apply Area Aura Enemy',    'Redirect Threat',          'Play Sound',
/*132+   */ 'Play Music',               'Unlearn Specialization',   'Kill Credit2',             'Call Pet',                 'Heal Percent',             'Energize Percent',
/*138+   */ 'Leap Back',                'Clear Quest',              'Force Cast',               'Force Cast With Value',    'Trigger Spell With Value', 'Apply Area Aura Owner',
/*144+   */ 'Knock Back Dest',          'Pull Towards Dest',        'Activate Rune',            'Quest Fail',               null,                       'Charge Dest',
/*150+   */ 'Quest Start',              'Trigger Spell 2',          null,                       'Create Tamed Pet',         'Discover Taxi',            'Dual Wield 2H Weapons',
/*156+   */ 'Enchant Item Prismatic',   'Create Item 2',            'Milling',                  'Allow Rename Pet',         null,                       'Talent Spec Count',
/*162-164*/ 'Talent Spec Select',       null,                       'Remove Aura'
        ),
        'unkAura'       => 'Unknown Aura',
        'auras'         => array(
/*0-   */   'None',                                 'Bind Sight',                           'Mod Possess',                          'Periodic Damage',                      'Dummy',
/*5+   */   'Mod Confuse',                          'Mod Charm',                            'Mod Fear',                             'Periodic Heal',                        'Mod Attack Speed',
            'Mod Threat',                           'Taunt',                                'Stun',                                 'Mod Damage Done Flat',                 'Mod Damage Taken Flat',
            'Damage Shield',                        'Mod Stealth',                          'Mod Stealth Detection',                'Mod Invisibility',                     'Mod Invisibility Detection',
            'Mod Health Percent',                   'Mod Power Percent',                    'Mod Resistance Flat',                  'Periodic Trigger Spell',               'Periodic Energize',
/*25+  */   'Pacify',                               'Root',                                 'Silence',                              'Reflect Spells',                       'Mod Stat Flat',
            'Mod Skill',                            'Mod Increase Speed',                   'Mod Increase Mounted Speed',           'Mod Decrease Speed',                   'Mod Increase Health',
            'Mod Increase Power',                   'Shapeshift',                           'Spell Effect Immunity',                'Spell Aura Immunity',                  'School Immunity',
            'Damage Immunity',                      'Dispel Immunity',                      'Proc Trigger Spell',                   'Proc Trigger Damage',                  'Track Creatures',
            'Track Resources',                      'Mod Parry Skill',                      'Mod Parry Percent',                    null,                                   'Mod Dodge Percent',
/*50+  */    'Mod Critical Healing Amount',          'Mod Block Percent',                    'Mod Physical Crit Percent',            'Periodic Health Leech',                'Mod Hit Chance',
            'Mod Spell Hit Chance',                 'Transform',                            'Mod Spell Crit Chance',                'Mod Increase Swim Speed',              'Mod Damage Done Versus Creature',
            'Pacify Silence',                       'Mod Scale',                            'Periodic Health Funnel',               'Periodic Mana Funnel',                 'Periodic Mana Leech',
            'Mod Casting Speed (not stacking)',     'Feign Death',                          'Disarm',                               'Stalked',                              'School Absorb',
            'Extra Attacks',                        'Mod Spell Crit Chance School',         'Mod Power Cost School Percent',        'Mod Power Cost School Flat',           'Reflect Spells School',
/*75+  */   'Language',                             'Far Sight',                            'Mechanic Immunity',                    'Mounted',                              'Mod Damage Done Percent',
            'Mod Stat Percent',                     'Split Damage Percent',                 'Water Breathing',                      'Mod Base Resistance Flat',             'Mod Health Regeneration',
            'Mod Power Regeneration',               'Channel Death Item',                   'Mod Damage Taken Percent',             'Mod Health Regeneration Percent',      'Periodic Damage Percent',
            'Mod Resist Chance',                    'Mod Detect Range',                     'Prevent Fleeing',                      'Unattackable',                         'Interrupt Regeneration',
            'Ghost',                                'Spell Magnet',                         'Mana Shield',                          'Mod Skill Value',                      'Mod Attack Power',
/*100+ */   'Auras Visible',                        'Mod Resistance Percent',               'Mod Melee Attack Power Versus',        'Mod Total Threat',                     'Water Walk',
            'Feather Fall',                         'Hover',                                'Add Flat Modifier',                    'Add Percent Modifier',                 'Add Target Trigger',
            'Mod Power Regeneration Percent',       'Add Caster Hit Trigger',               'Override Class Scripts',               'Mod Ranged Damage Taken Flat',         'Mod Ranged Damage Taken Percent',
            'Mod Healing',                          'Mod Regeneration During Combat',       'Mod Mechanic Resistance',              'Mod Healing Taken Percent',            'Share Pet Tracking',
            'Untrackable',                          'Empathy',                              'Mod Offhand Damage Percent',           'Mod Target Resistance',                'Mod Ranged Attack Power',
/*125+ */   'Mod Melee Damage Taken Flat',          'Mod Melee Damage Taken Percent',       'Ranged Attack Power Attacker Bonus',   'Possess Pet',                          'Mod Speed Always',
            'Mod Mounted Speed Always',             'Mod Ranged Attack Power Versus',       'Mod Increase Energy Percent',          'Mod Increase Health Percent',          'Mod Mana Regeneration Interrupt',
            'Mod Healing Done Flat',                'Mod Healing Done Percent',             'Mod Total Stat Percentage',            'Mod Melee Haste',                      'Force Reaction',
            'Mod Ranged Haste',                     'Mod Ranged Ammo Haste',                'Mod Base Resistance Percent',          'Mod Resistance Exclusive',             'Safe Fall',
            'Mod Pet Talent Points',                'Allow Tame Pet Type',                  'Mechanic Immunity Mask',               'Retain Combo Points',                  'Reduce Pushback',
/*150+ */   'Mod Shield Blockvalue Percent',        'Track Stealthed',                      'Mod Detected Range',                   'Split Damage Flat',                    'Mod Stealth Level',
            'Mod Water Breathing',                  'Mod Reputation Gain',                  'Pet Damage Multi',                     'Mod Shield Blockvalue',                'No PvP Credit',
            'Mod AoE Avoidance',                    'Mod Health Regeneration In Combat',    'Power Burn Mana',                      'Mod Crit Damage Bonus',                null,
            'Melee Attack Power Attacker Bonus',    'Mod Attack Power Percent',             'Mod Ranged Attack Power Percent',      'Mod Damage Done Versus',               'Mod Crit Percent Versus',
            'Change Model',                         'Mod Speed (not stacking)',             'Mod Mounted Speed (not stacking)',     null,                                   'Mod Spell Damage Of Stat Percent',
/*175+ */   'Mod Spell Healing Of Stat Percent',    'Spirit Of Redemption',                 'AoE Charm',                            'Mod Debuff Resistance',                'Mod Attacker Spell Crit Chance',
            'Mod Spell Damage Versus',              null,                                   'Mod Resistance Of Stat Percent',       'Mod Critical Threat',                  'Mod Attacker Melee Hit Chance',
            'Mod Attacker Ranged Hit Chance',       'Mod Attacker Spell Hit Chance',        'Mod Attacker Melee Crit Chance',       'Mod Attacker Ranged Crit Chance',      'Mod Rating',
            'Mod Faction Reputation Gain',          'Use Normal Movement Speed',            'Mod Melee Ranged Haste',               'Mod Haste',                            'Mod Target Absorb School',
            'Mod Target Ability Absorb School',     'Mod Cooldown',                         'Mod Attacker Spell And Weapon Crit Chance', null,                              'Mod Increases Spell Percent to Hit',
/*200+ */   'Mod XP Percent',                       'Fly',                                  'Ignore Combat Result',                 'Mod Attacker Melee Crit Damage',       'Mod Attacker Ranged Crit Damage',
            'Mod School Crit Damage Taken',         'Mod Increase Vehicle Flight Speed',    'Mod Increase Mounted Flight Speed',    'Mod Increase Flight Speed',            'Mod Mounted Flight Speed Always',
            'Mod Vehicle Speed Always',             'Mod Flight Speed (not stacking)',      'Mod Ranged Attack Power Of Stat Percent', 'Mod Rage from Damage Dealt',        'Tamed Pet Passive',
            'Arena Preparation',                    'Haste Spells',                         'Killing Spree',                        'Haste Ranged',                         'Mod Mana Regeneration from Stat',
            'Mod Rating from Stat',                 'Ignore Threat',                        null,                                   'Raid Proc from Charge',                null,
/*225+ */   'Raid Proc from Charge With Value',     'Periodic Dummy',                       'Periodic Trigger Spell With Value',    'Detect Stealth',                       'Mod AoE Damage Avoidance',
            'Mod Increase Health',                  'Proc Trigger Spell With Value',        'Mod Mechanic Duration',                'Mod Display Model',                    'Mod Mechanic Duration (not stacking)',
            'Mod Dispel Resist',                    'Control Vehicle',                      'Mod Spell Damage Of Attack Power',     'Mod Spell Healing Of Attack Power',    'Mod Scale 2',
            'Mod Expertise',                        'Force Move Forward',                   'Mod Spell Damage from Healing',        'Mod Faction',                          'Comprehend Language',
            'Mod Aura Duration By Dispel',          'Mod Aura Duration By Dispel (not stacking)', 'Clone Caster',                   'Mod Combat Result Chance',             'Convert Rune',
/*250+ */   'Mod Increase Health 2',                'Mod Enemy Dodge',                      'Mod Speed Slow All',                   'Mod Block Crit Chance',                'Mod Disarm Offhand',
            'Mod Mechanic Damage Taken Percent',    'No Reagent Use',                       'Mod Target Resist By Spell Class',     'Mod Spell Visual',                     'Mod HoT Percent',
            'Screen Effect',                        'Phase',                                'Ability Ignore Aurastate',             'Allow Only Ability',                   null,
            null,                                   null,                                   'Mod Immune Aura Apply School',         'Mod Attack Power Of Stat Percent',     'Mod Ignore Target Resist',
            'Mod Ability Ignore Target Resist',     'Mod Damage Taken Percent From Caster', 'Ignore Melee Reset',                   'X Ray',                                'Ability Consume No Ammo',
/*275+ */   'Mod Ignore Shapeshift',                'Mod Mechanic Damage Done Percent',     'Mod Max Affected Targets',             'Mod Disarm Ranged',                    'Initialize Images',
            'Mod Armor Penetration Percent',        'Mod Honor Gain Percent',               'Mod Base Health Percent',              'Mod Healing Received',                 'Linked',
            'Mod Attack Power Of Armor',            'Ability Periodic Crit',                'Deflect Spells',                       'Ignore Hit Direction',                 null,
            'Mod Crit Percent',                     'Mod XP Quest Percent',                 'Open Stable',                          'Override Spells',                      'Prevent Power Regeneration',
            null,                                   'Set Vehicle Id',                       'Block Spell Family',                   'Strangulate',                          null,
/*300+ */   'Share Damage Percent',                 'School Heal Absorb',                   null,                                   'Mod Damage Done Versus Aurastate',     'Mod Fake Inebriate',
            'Mod Minimum Speed',                    null,                                   'Heal Absorb Test',                     'Hunter Trap',                          null,
            'Mod Creature AoE Damage Avoidance',    null,                                   null,                                   null,                                   'Prevent Ressurection',
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
        'socketBonus'   => "Bono de ranura",
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
