<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/*
    some translations have yet to be taken from or improved by the use of:
    <path>\World of Warcraft\Data\ruRU\patch-ruRu-3.MPQ\Interface\FrameXML\GlobalStrings.lua
    like: ITEM_MOD_*, POWER_TYPE_*, ITEM_BIND_*, PVP_RANK_*
*/

$lang = array(
    // page variables
    'timeUnits' => array(
        'sg'            => ["год",  "месяц",  "неделя", "день", "час",  "минута", "секунда", "миллисекунда"],
        'pl'            => ["годы", "месяцы", "недели", "дн.",  "часы", "мин",    "секунды", "миллисекундах"],
        'ab'            => ["г.",   "мес.",   "нед.",   "дн",   "ч.",   "мин",    "сек.",    "мс"]
    ),
    'main' => array(
        'name'          => "название",
        'link'          => "Ссылка",
        'signIn'        => "Вход / Регистрация",
        'jsError'       => "Для работы этого сайта необходим JavaScript.",
        'language'      => "Язык",
        'feedback'      => "Отзыв",
        'numSQL'        => "Количество MySQL запросов",
        'timeSQL'       => "Время выполнения MySQL запросов",
        'noJScript'     => '<b>Данный сайт активно использует технологию JavaScript.</b><br />Пожалуйста, <a href="https://www.google.com/support/adsense/bin/answer.py?answer=12654" target="_blank">Включите JavaScript</a> в вашем браузере.',
        'userProfiles'  => "Ваши персонажи",    // translate.google :x
        'pageNotFound'  => "Такое %s не существует.",
        'gender'        => "Пол",
        'sex'           => [null, "Мужчина", "Женщина"],
        'players'       => "Игрокам",
        'quickFacts'    => "Краткая информация",
        'screenshots'   => "Изображения",
        'videos'        => "Видео",
        'side'          => "Сторона",
        'related'       => "Дополнительная информация",
        'contribute'    => "Добавить",
        // 'replyingTo'    => "Ответ на комментарий от",
        'submit'        => "Отправить",
        'cancel'        => "Отмена",
        'rewards'       => "Награды",
        'gains'         => "Бонус",
        'login'         => "[Login]",
        'forum'         => "Форум",
        'n_a'           => "нет",
        'siteRep'       => "Репутация",
        'yourRepHistory'=> "История вашей репутации",
        'aboutUs'       => "О Aowow",
        'and'           => " и ",
        'or'            => " или ",
        'back'          => "Назад",
        'reputationTip' => "Очки репутации",
        'byUserTimeAgo' => 'От <a href="'.HOST_URL.'/?user=%s">%1s</a> %s назад',
        'help'          => "Справка",

        // filter
        'extSearch'     => "Расширенный поиск",
        'addFilter'     => "Добавить другой фильтр",
        'match'         => "Совпадение",
        'allFilter'     => "Все фильтры",
        'oneFilter'     => "Любое совпадение",
        'applyFilter'   => "Применить фильтр",
        'resetForm'     => "Очистить форму",
        'refineSearch'  => 'Совет: Уточните поиск, добавив <a href="javascript:;" id="fi_subcat">подкатегорию</a>.',
        'clear'         => "Очистить",
        'exactMatch'    => "Полное совпадение",
        '_reqLevel'     => "Требуется уровень",

        // infobox
        'unavailable'   => "Недоступно игрокам",
        'disabled'      => "[Disabled]",
        'disabledHint'  => "[Cannot be attained or completed]",
        'serverside'    => "[Serverside]",
        'serversideHint' => "[These informations are not in the Client and have been provided by sniffing and/or guessing.]",

        // red buttons
        'links'         => "Ссылки",
        'compare'       => "Сравнить",
        'view3D'        => "Посмотреть в 3D",
        'findUpgrades'  => "Найти лучше...",

        // misc Tools
        'errPageTitle'  => "Страница не найдена",
        'nfPageTitle'   => "Ошибка",
        'subscribe'     => "Подписаться",
        'mostComments'  => ["Вчера", "Последние %d дней"],
        'utilities'     => array(
            "Последние добавления",                 "Последние статьи",                     "Последние комментарии",                "Последние изображения",                null,
            "Комментарии без оценки",               11 => "Последние видео",                12 => "Популярные комментируемые",      13 => "Без изображений"
        ),

        // article & infobox
        'englishOnly'   => "Эта страница доступна только на <b>английском</b> языке.",

        // calculators
        'preset'        => "Готовая таблица",
        'addWeight'     => "Добавить фильтр значимости",
        'createWS'      => "Отсортировать по значимости",
        'jcGemsOnly'    => "Использовать <span%s>ювелирские</span>",
        'cappedHint'    => 'Подсказка: <a href="javascript:;" onclick="fi_presetDetails();">Удалите</a> характеристики с капом (например, меткость).',
        'groupBy'       => "Группировать",
        'gb'            => array(
            ['Нет', 'none'],         ['Слот', 'slot'],       ['Уровень', 'level'],     ['Источник', 'source']
        ),
        'compareTool'   => "Инструмент сравнения предметов",
        'talentCalc'    => "Расчёт талантов",
        'petCalc'       => "Расчёт умений питомцев",
        'chooseClass'   => "Выберите класс",
        'chooseFamily'  => "Выберите семейство питомцев",

        // profiler
        'realm'         => "Игровой мир",
        'region'        => "Регион",
        'viewCharacter' => "Открыть персонажа",
        '_cpHead'       => "Профили персонажей",
        '_cpHint'       => "<b>Профили персонажей</b> позволяет вам редактировать своего персонажа, находить улучшения предметов и многое другое!",
        '_cpHelp'       => "Чтобы начать использовать профили персонажей, следуйте инструкциям ниже. Если вам потребуется помощь, вы можете обратиться к <a href=\"?help=profiler\">справке</a>.",
        '_cpFooter'     => "Если вам нужен более точный поиск, вы можете использовать <a href=\"?profiles\">дополнительные опции</a>. Также, вы можете создать <a href=\"?profile&amp;new\">новый собственный профиль</a>.",

        // search
        'search'        => "Поиск",
        'searchButton'  => "Поиск",
        'foundResult'   => "Результаты поиска для",
        'noResult'      => "Ничего не найдено для",
        'tryAgain'      => "Пожалуйста, попробуйте другие ключевые слова или проверьте правильность запроса.",
        'ignoredTerms'  => "[Следующие слова были проигнорированы в вашему запросу]: %s",

        // formating
        'colon'         => ": ",
        'dateFmtShort'  => "Y-m-d",
        'dateFmtLong'   => "Y-m-d в H:i",

        // error
        'intError'      => "[An internal error occured.]",
        'intError2'     => "[An internal error occured. (%s)]",
        'genericError'  => "Произошла ошибка; обновите страницу и попробуйте снова. Если ситуация повторяется, отправьте сообщение на <a href='#contact'>feedback</a>", # LANG.genericerror
        'bannedRating'  => "Вам была заблокирована возможность оценивать комментарии.", # LANG.tooltip_banned_rating
        'tooManyVotes'  => "Вы сегодня проголосовали слишком много раз! Вы сможете продолжить завтра.", # LANG.tooltip_too_many_votes

        'moreTitles'    => array(
            'reputation'    => "Репутация на сайте",
            'whats-new'     => "Новости",
            'searchbox'     => "Окно поиска",
            'tooltips'      => "Всплывающие подсказки",
            'faq'           => "[Frequently Asked Questions]",
            'aboutus'       => "[What is AoWoW?]",
            'searchplugins' => "Дополнения для браузеров",
            'privileges'    => "Привилегии",
            'top-users'     => "Лучшие пользователи",
            'help'          => array(
                'commenting-and-you' => "Комментарии и Вы",                 'modelviewer'       => "3D просмотр",               'screenshots-tips-tricks' => "Скриншоты: Секреты мастерства",
                'stat-weighting'     => "Значимость характеристик",         'talent-calculator' => "Расчёт талантов",           'item-comparison'         => "Сравнение предметов",
                'profiler'           => "Профили персонажей",               'markup-guide'      => "Markup Guide"
            )
        )
    ),
    'screenshot' => array(
        'submission'    => "Добавление изображения",
        'selectAll'     => "Выбрать всё",
        'cropHint'      => "Вы можете произвести кадрирование изображения и указать заголовок.",
        'displayOn'     => "[Displayed on:[br]%s - [%s=%d]]",
        'caption'       => "[Caption]",
        'charLimit'     => "Не обязательно, вплоть до 200 знаков",
        'thanks'        => array(
            'contrib' => "Спасибо за ваш вклад!",
            'goBack'  => '<a href="?%s=%d">здесь</a> чтобы перейти к предыдущей странице.',
            'note'    => "Примечание: Перед появлением на сайте, ваше изображение должно быть одобрено. Это может занять до 72 часов."
        ),
        'error'         => array(
            'unkFormat'   => "неизвестный формат изображения.",
            'tooSmall'    => "Изображение слишком маленькое. (&lt; ".CFG_SCREENSHOT_MIN_SIZE."x".CFG_SCREENSHOT_MIN_SIZE.").",
            'selectSS'    => "Выберите изображение для загрузки.",
            'notAllowed'  => "[You are not allowed to upload screenshots!]",
        )
    ),
    'game' => array(
        'achievement'   => "достижение",
        'achievements'  => "Достижения",
        'class'         => "класс",
        'classes'       => "Классы",
        'currency'      => "валюта",
        'currencies'    => "Валюта",
        'difficulty'    => "Сложность",
        'dispelType'    => "Тип рассеивания",
        'duration'      => "Длительность",
        'emote'         => "Эмоция",
        'emotes'        => "Эмоции",
        'enchantment'   => "улучшение",
        'enchantments'  => "Улучшения",
        'object'        => "объект",
        'objects'       => "Объекты",
        'glyphType'     => "Тип символа",
        'race'          => "раса",
        'races'         => "Расы",
        'title'         => "звание",
        'titles'        => "Звания",
        'eventShort'    => "Игровое событие",
        'event'         => "Событие",
        'events'        => "Игровые события",
        'faction'       => "фракция",
        'factions'      => "Фракции",
        'cooldown'      => "Восстановление: %s",
        'icon'          => "иконка",
        'icons'         => "Иконки",
        'item'          => "предмет",
        'items'         => "Предметы",
        'itemset'       => "комплект",
        'itemsets'      => "Комплекты",
        'mechanic'      => "Механика",
        'mechAbbr'      => "Механика",
        'meetingStone'  => "Камень встреч",
        'npc'           => "НИП",
        'npcs'          => "НИП",
        'pet'           => "Питомец",
        'pets'          => "Питомцы охотников",
        'profile'       => "",
        'profiles'      => "Профили",
        'quest'         => "задание",
        'quests'        => "Задания",
        'requires'      => "Требует %s",
        'requires2'     => "Требуется:",
        'reqLevel'      => "Требуется уровень: %s",
        'reqSkillLevel' => "Требуется уровень навыка",
        'level'         => "Уровень",
        'school'        => "Школа",
        'skill'         => "Уровень навыка",
        'skills'        => "Умения",
        'sound'         => "Звук",
        'sounds'        => "Звуки",
        'spell'         => "заклинание",
        'spells'        => "Заклинания",
        'type'          => "Тип",
        'valueDelim'    => " - ",
        'zone'          => "игровая зона",
        'zones'         => "Местности",

        'pvp'           => "PvP",
        'honorPoints'   => "Очки Чести",
        'arenaPoints'   => "Очки арены",
        'heroClass'     => "Героический класс",
        'resource'      => "Ресурс",
        'resources'     => "Ресурсы",
        'role'          => "Роль",
        'roles'         => "Роли",
        'specs'         => "Ветки талантов",
        '_roles'        => ["Лекарь", "Боец ближнего боя", "Боец дальнего боя", "Танк"],

        'phases'        => "Фазы",
        'mode'          => "Режим",
        'modes'         => [-1 => "Все", "Обычный / 10-норм.", "Героический / 25-норм.", "10-героич", "25-героич"],
        'expansions'    => array("World of Warcraft", "The Burning Crusade", "Wrath of the Lich King"),
        'stats'         => array("к силе", "к ловкости", "к выносливости", "к интеллекту", "к духу"),
        'sources'       => array(
            "Неизвестно",                   "Ремесло",                      "Добыча",                       "PvP",                          "Задание",                      "Продавец",
            "Тренер",                       "Открытие",                     "Рекламная акция",              "Талант",                       "Начальное заклинание",         "Мероприятие",
            "Достижение",                   null,                           "Черный Рынок",                 "Распылено",                    "Вылавливается",                "Собрано",
            "[Milled]",                     "Выкапывается",                 "Просеивается",                 "Можно украсть",                "Разобрано",                    "Собирается при снятии шкуры",
            "Внутриигровой магазин"
        ),
        'languages'     => array(
             1 => "орочий",                  2 => "дарнасский",              3 => "таурахэ",                 6 => "дворфийский",             7 => "всеобщий",                8 => "язык демонов",
             9 => "язык титанов",           10 => "талассийский",           11 => "драконий",               12 => "калимаг",                13 => "гномский",               14 => "язык троллей",
            33 => "наречие нежити",         35 => "дренейский",             36 => "наречие зомби",          37 => "машинный гномский",      38 => "машинный гоблинский"
        ),
        'gl'            => array(null, "Большой", "Малый"),
        'si'            => array(1 => "Альянс", -1 => "Альянс только", 2 => "Орда", -2 => "Орда только", null, 3 => "Обе"),
        'resistances'   => array(null, "Сопротивление светлой магии", "Сопротивление огню", "Сопротивление силам природы", "Сопротивление магии льда", "Сопротивление темной магии", "Сопротивление тайной магии"),
        'dt'            => array(null, "Магия", "Проклятие", "Болезнь", "Яд", "Незаметность", "Невидимость", null, null, "Исступление"),
        'sc'            => array("Физический урон", "Свет", "Огонь", "природа", "Лед", "Тьма", "Тайная магия"),
        'cl'            => array(null, "Воин", "Паладин", "Охотник", "Разбойник", "Жрец", "Рыцарь смерти", "Шаман", "Маг", "Чернокнижник", null, "Друид"),
        'ra'            => array(-2 => "Орда", -1 => "Альянс", "Обе", "Человек", "Орк", "Дворф", "Ночной эльф", "Нежить", "Таурен", "Гном", "Тролль", null, "Эльф крови", "Дреней"),
        'rep'           => array("Ненависть", "Враждебность", "Неприязнь", "Равнодушие", "Дружелюбие", "Уважение", "Почтение", "Превознесение"),
        'st'            => array(
            "По-умолчанию",                 "Облик кошки",                  "TОблик Древа жизни",           "Походный облик",               "Водный облик",                 "Облик медведя",
            null,                           null,                           "Облик лютого медведя",         null,                           null,                           null,
            null,                           "Танец теней",                  null,                           null,                           "Призрачный волк",              "Боевая стойка",
            "Оборонительная стойка",        "Стойка берсерка",              null,                           null,                           "Метаморфоза",                  null,
            null,                           null,                           null,                           "Облик стремительной птицы",    "Облик Тьмы",                   "Облик птицы",
            "Незаметность",                 "Облик лунного совуха",         "Дух воздаяния"
        ),
        'me'            => array(
            null,                           "Подчинённый",                  "Дезориентирован",              "Разоружённый",                 "Отвлечён",                     "Убегающий",
            "Неуклюжий",                    "Оплетён",                      "Немота",                       "В покое",                      "Усыплён",                      "Пойманный в ловушку",
            "Оглушен",                      "Замороженный",                 "Бездейственный",               "Кровоточащий",                 "Целительное",                  "Превращён",
            "Изгнан",                       "Ограждён",                     "Скован",                       "Оседлавший",                   "Соблазнён",                    "Обращение",
            "Испуганный",                   "Неуязвимый",                   "Прервано",                     "Замедленный",                  "Открытие",                     "Неуязвимый",
            "Ошеломлён",                    "Исступление"
        ),
        'ct'            => array(
            "Разное",                       "Животное",                     "Дракон",                       "Демон",                        "Элементаль",                   "Великан",
            "Нежить",                       "Гуманоид",                     "Существо",                     "Механизм",                     "Не указано",                   "Тотем",
            "Спутник",                      "Облако газа"
        ),
        'fa'            => array(
             1 => "Волк",                    2 => "Кошка",                   3 => "Паук",                    4 => "Медведь",                 5 => "Вепрь",                   6 => "Кроколиск",
             7 => "Падальщик",               8 => "Краб",                    9 => "Горилла",                11 => "Ящер",                   12 => "Долгоног",               20 => "Скорпид",
            21 => "Черепаха",               24 => "Летучая мышь",           25 => "Гиена",                  26 => "Сова",                   27 => "Крылатый змей",          30 => "Дракондор",
            31 => "Опустошитель",           32 => "Прыгуана",               33 => "Спороскат",              34 => "Скат Пустоты",           35 => "Змей",                   37 => "Мотылек",
            38 => "Химера",                 39 => "Дьявозавр",              41 => "Силитид",                42 => "Червь",                  43 => "Люторог",                44 => "Оса",
            45 => "Гончая Недр",            46 => "Дух зверя"
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
        'title'         => "Учетная запись Aowow",
        'email'         => "Email",
        'continue'      => "Продолжить",
        'groups'        => array(
            -1 => "Нет",                    "Тестер",                       "Администратор",                "Редактор",                     "Модератор",                    "Бюрократ",
            "Разработчик",                  "VIP",                          "Блогер",                       "Учетная запись Премиум",       "Переводчик",                   "Агент по продажам",
            "Менеджер изображений",         "Менеджер видео",               "API партнер",                  "Ожидающее"
        ),
        // signIn
        'doSignIn'      => "Войти в вашу учетную запись Aowow",
        'signIn'        => "Вход",
        'user'          => "Логин",
        'pass'          => "Пароль",
        'rememberMe'    => "Запомнить меня на этом компьютере",
        'forgot'        => "Забыл",
        'forgotUser'    => "Имя пользователя",
        'forgotPass'    => "Пароль",
        'accCreate'     => 'У вас еще нет учетной записи? <a href="?account=signup">Зарегистрируйтесь прямо сейчас!</a>',

        // recovery
        'recoverUser'   => "Запрос имени пользователя",
        'recoverPass'   => "Сброс пароля: Шаг %s из 2",
        'newPass'       => "New Password",

        // creation
        'register'      => "Регистрация: Шаг %s из 2",
        'passConfirm'   => "Повторите пароль",

        // dashboard
        'ipAddress'     => "IP-Adress",
        'lastIP'        => "last used IP",
        'myAccount'     => "My Account",
        'editAccount'   => "Simply use the forms below to update your account information",
        'viewPubDesc'   => 'View your Public Description in your <a href="?user=%s">Profile  Page</a>',

        // bans
        'accBanned'     => "This Account was closed",
        'bannedBy'      => "Banned by",
        'ends'          => "Ends on",
        'permanent'     => "The ban is permanent",
        'reason'        => "Reason",
        'noReason'      => "No reason was given.",

        // form-text
        'emailInvalid'  => "Недопустимый адрес email.", // message_emailnotvalid
        'emailNotFound' => "The email address you entered is not associated with any account.<br><br>If you forgot the email you registered your account with email ".CFG_CONTACT_EMAIL." for assistance.",
        'createAccSent' => "An email was sent to <b>%s</b>. Simply follow the instructions to create your account.",
        'recovUserSent' => "An email was sent to <b>%s</b>. Simply follow the instructions to recover your username.",
        'recovPassSent' => "An email was sent to <b>%s</b>. Simply follow the instructions to reset your password.",
        'accActivated'  => 'Your account has been activated.<br>Proceed to <a href="?account=signin&token=%s">sign in</a>',
        'userNotFound'  => "The username you entered does not exists.",
        'wrongPass'     => "That password is not vaild.",
        // 'accInactive'   => "That account has not yet been confirmed active.",
        'loginExceeded' => "The maximum number of logins from this IP has been exceeded. Please try again in %s.",
        'signupExceeded'=> "The maximum number of signups from this IP has been exceeded. Please try again in %s.",
        'errNameLength' => "Имя пользователя не должно быть короче 4 символов.", // message_usernamemin
        'errNameChars'  => "Имя пользователя может содержать только буквы и цифры.", // message_usernamenotvalid
        'errPassLength' => "Ваш пароль должен состоять минимум из 6 знаков.", // message_passwordmin
        'passMismatch'  => "The passwords you entered do not match.",
        'nameInUse'     => "That username is already taken.",
        'mailInUse'     => "That email is already registered to an account.",
        'isRecovering'  => "This account is already recovering. Follow the instructions in your email or wait %s for the token to expire.",
        'passCheckFail' => "Пароли не совпадают.", // message_passwordsdonotmatch
        'newPassDiff'   => "Прежний и новый пароли не должны совпадать." // message_newpassdifferent
    ),
    'user' => array(
        'notFound'      => "Пользователь \"%s\" не найден!",
        'removed'       => "(Удалено)",
        'joinDate'      => "Зарегистрировался",
        'lastLogin'     => "Последняя активность",
        'userGroups'    => "Роль",
        'consecVisits'  => "Регулярные посещения",
        'publicDesc'    => "Описание",
        'profileTitle'  => "Профиль %s",
        'contributions' => "Вклад",
        'uploads'       => "Данных загружено",
        'comments'      => "Комментарии",
        'screenshots'   => "Скриншоты",
        'videos'        => "Видео",
        'posts'         => "Сообщений на форумах"
    ),
    'mail' => array(
        'tokenExpires'  => "This token expires in %s.",
        'accConfirm'    => ["Account Confirmation", "Welcome to ".CFG_NAME_SHORT."!\r\n\r\nClick the Link below to activate your account.\r\n\r\n".HOST_URL."?account=signup&token=%s\r\n\r\nIf you did not request this mail simply ignore it."],
        'recoverUser'   => ["User Recovery",        "Follow this link to log in.\r\n\r\n".HOST_URL."?account=signin&token=%s\r\n\r\nIf you did not request this mail simply ignore it."],
        'resetPass'     => ["Password Reset",       "Follow this link to reset your password.\r\n\r\n".HOST_URL."?account=forgotpassword&token=%s\r\n\r\nIf you did not request this mail simply ignore it."]
    ),
    'emote' => array(
        'notFound'      => "[This Emote doesn't exist.]",
        'self'          => "[To Yourself]",
        'target'        => "[To others with a target]",
        'noTarget'      => "[To others without a target]",
        'isAnimated'    => "[Uses an animation]",
        'aliases'       => "[Aliases]",
        'noText'        => "[This Emote has no text.]",
    ),
    'enchantment' => array(
        'details'       => "Подробности",
        'activation'    => "Активации",
        'notFound'      => "Такой улучшение не существует.",
        'types'         => array(
            1 => "[Proc Spell]",            3 => "[Equip Spell]",           7 => "[Use Spell]",             8 => "Бесцветное гнездо",
            5 => "Характеристики",          2 => "Урон оружия",             6 => "УВС",                     4 => "Защита"
        )
    ),
    'gameObject' => array(
        'notFound'      => "Такой объект не существует.",
        'cat'           => [0 => "Другое", 9 => "Книги", 3 => "Контейнеры", -5 => "Сундуки", 25 => "Рыболовные лунки",-3 => "Травы",    -4 => "Полезные ископаемые", -2 => "Задания", -6 => "Инструменты"],
        'type'          => [               9 => "Книга", 3 => "Контейнер",  -5 => "Сундук",  25 => "",                -3 => "Растение", -4 => "Полезное ископаемое", -2 => "Задание", -6 => ""],
        'unkPosition'   => "Местонахождение этого объекта неизвестно.",
        'npcLootPH'     => '[The <b>%s</b> contains the loot from the fight against <a href="?npc=%d">%s</a>. It spawns after his death.]',
        'key'           => "Ключ",
        'focus'         => "[Spell Focus]",
        'focusDesc'     => "[Spells requiring this Focus can be cast near this Object]",
        'trap'          => "Ловушки",
        'triggeredBy'   => "Срабатывает от",
        'capturePoint'  => "Точка захвата",
        'foundIn'       => "Этот НИП может быть найден в следующих зонах:",
        'restock'       => "[Restocks every %s.]"
    ),
    'npc' => array(
        'notFound'      => "Такой НИП не существует.",
        'classification'=> "Классификация",
        'petFamily'     => "Семейство питомца",
        'react'         => "Реакция",
        'worth'         => "Деньги",
        'unkPosition'   => "Местоположение этого НИП неизвестно.",
        'difficultyPH'  => "[Этот НИП является прототипом для другого режима]",
        'seat'          => "[Seat]",
        'accessory'     => "[Accessory]",
        'accessoryFor'  => "[This creature is an accessory for vehicle]",
        'quotes'        => "Цитаты",
        'gainsDesc'     => "В награду за убийство этого НИПа вы получите",
        'repWith'       => "репутации с",
        'stopsAt'       => 'останавливается на уровне "%s"',
        'vehicle'       => "Автомобиль",
        'stats'         => "Характеристики",
        'melee'         => "Ближнего боя",
        'ranged'        => "Дальнего боя",
        'armor'         => "Броня",
        'foundIn'       => "Этот объект может быть найден в следующих зонах:",
        'tameable'      => "Можно приручить (%s)",
        'waypoint'      => "Путевой точки",
        'wait'          => "Период ожидания",
        'respawnIn'     => "Reentry in",    /// ..lol?
        'rank'          => [0 => "Обычный", 1 => "Элитный", 4 => "Редкий", 2 => "Редкий элитный", 3 =>"Босс"],
        'textRanges'    => [null, "[sent to area]", "[sent to zone]", "[sent to map]", "[sent to world]"],
        'textTypes'     => [null, "кричит", "говорит", "шепчет"],
        'modes'         => array(
            1 => ["Обычный", "Героический"],
            2 => ["10 нормал.", "25 нормал.", "10 героич.", "25 героич."]
        ),
        'cat'           => array(
            "Разное",                   "Животные",                 "Дракон",                   "Демоны",                   "Элементали",               "Великаны",                 "Нежить",                   "Гуманоиды",
            "Существа",                 "Механизмы",                "Не указано",               "Тотемы",                   "Спутники",                 "Облака газа"
        )
    ),
    'event' => array(
        'notFound'      => "Это игровое событие не существует.",
        'start'         => "Начало",
        'end'           => "Конец",
        'interval'      => "[Interval]",
        'inProgress'    => "Событие активно в данный момент",
        'category'      => array("Разное", "Праздники", "Периодические", "PvP")
    ),
    'achievement' => array(
        'notFound'      => "Такое достижение не существует.",
        'criteria'      => "Критерий",
        'points'        => "Очки",
        'series'        => "Серии",
        'outOf'         => "из",
        'criteriaType'  => "[Criterium Type-Id]:",
        'itemReward'    => "Вы получите",
        'titleReward'   => 'Наградное звание: "<a href="?title=%d">%s</a>"',
        'slain'         => "убито",
        'reqNumCrt'     => "Требуется",
        'rfAvailable'   => "[Available on realm]: ",
        '_transfer'     => 'Этот предмет превратится в <a href="?achievement=%d" class="q%d icontiny tinyspecial" style="background-image: url('.STATIC_URL.'/images/wow/icons/tiny/%s.gif)">%s</a>, если вы перейдете за <span class="icon-%s">%s</span>.',
    ),
    'chrClass' => array(
        'notFound'      => "Такой класс не существует."
    ),
    'race' => array(
        'notFound'      => "Такая раса не существует.",
        'racialLeader'  => "Лидер расы",
        'startZone'     => "Начальная локация",
    ),
    'maps' => array(
        'maps'          => "Карты",
        'linkToThisMap' => "Ссылка на эту карту",
        'clear'         => "Очистить",
        'EasternKingdoms' => "Восточные королевства",
        'Kalimdor'      => "Калимдор",
        'Outland'       => "Запределье",
        'Northrend'     => "Нордскол",
        'Instances'     => "Поземелья и рейды",
        'Dungeons'      => "Подземелья",
        'Raids'         => "Рейды",
        'More'          => "Дополнительно ",
        'Battlegrounds' => "Поля боя",
        'Miscellaneous' => "Разное",
        'Azeroth'       => "Азерот",
        'CosmicMap'     => "Звёздная карта",
    ),
    'privileges' => array(
        'main'          => "Здесь на AoWoW вы можете зарабатывать <a href=\"?reputation\">репутацию</a>. Основной источник получения репутации — увеличение рейтинга ваших комментариев другими пользователями.<br><br>Репутация примерно измеряет количество вашего вклада в сообщество.<br><br>По мере того, как вы зарабатываете репутацию, вы получаете доверие сообщества и особые привилегии. Полный список привилегий расположен ниже.",
        'privilege'     => "Привилегия",
        'privileges'    => "Привилегии",
        'requiredRep'   => "Необходима репутация",
        'reqPoints'     => "Для этой привилегии требуется <b>%s</b> очков репутации.",
        '_privileges'   => array(
            null,                                   "Оставлять комментарии",                        "Оставлять внешние ссылки",                         null,
            "Нет CAPTCHA",                          "Более сильные голоса",                         null,                                               null,
            null,                                   "Больше голосов в день",                        "Голосовать за комментарии",                        "Голосовать против комментариев",
            "Отвечать на комментарии",              "Граница: Необычный",                           "Граница: Редкий",                                  "Граница: Эпический",
            "Граница: Легендарный",                 "AoWoW Premium"
        )
    ),
    'zone' => array(
        'notFound'      => "Такая местность не существует.",
        'attunement'    => ["[Attunement]", "[Heroic attunement]"],
        'key'           => ["[Key]", "[Heroic key]"],
        'location'      => "Местоположение",
        'raidFaction'   => "Фракция рейда",
        'boss'          => "Последний босс",
        'reqLevels'     => "Требуемые уровни: [tooltip=instancereqlevel_tip]%d[/tooltip], [tooltip=lfgreqlevel_tip]%d[/tooltip]",
        'zonePartOf'    => "Эта игровая локация является частью локации [zone=%d].",
        'autoRez'       => "Автоматическое воскрешение",
        'city'          => "Город",
        'territory'     => "Территория",
        'instanceType'  => "Тип подземелья",
        'hcAvailable'   => "Доступен героический режим&nbsp;(%d)",
        'numPlayers'    => "Количество игроков",
        'noMap'         => "Для данной местности нет доступной карты.",
        'instanceTypes' => ["Игровая зона", "Транзит", "Подземелье",   "Рейд",      "Поле боя", "Подземелье", "Арена", "Рейд", "Рейд"],
        'territories'   => ["Альянс",       "Орда",    "Оспариваемая", "Святилище", "PvP",      "Мировое PvP"],
        'cat'           => array(
            "Восточные королевства",    "Калимдор",                 "Подземелья",               "Рейды",                    "Неактивно",                null,
            "Поля боя",                 null,                       "Запределье",               "Арены",                    "Нордскол"
        )
    ),
    'quest' => array(
        'notFound'      => "Такое задание не существует.",
        '_transfer'     => 'Этот предмет превратится в <a href="?quest=%d" class="q1">%s</a>, если вы перейдете за <span class="icon-%s">%s</span>.',
        'questLevel'    => "%s-го уровня",
        'requirements'  => "Требования",
        'reqMoney'      => "Требуется денег",
        'money'         => "Деньги",
        'additionalReq' => "Дополнительные условия для получения данного задания",
        'reqRepWith'    => 'Ваша репутация с <a href="?faction=%d">%s</a> должна быть %s %s',
        'reqRepMin'     => "не менее",
        'reqRepMax'     => "меньше чем",
        'progress'      => "Прогресс",
        'provided'      => "Прилагается",
        'providedItem'  => "Прилагается предмет",
        'completion'    => "Завершение",
        'description'   => "Описание",
        'playerSlain'   => "Убито игроков",
        'profession'    => "Профессия",
        'timer'         => "Таймер",
        'loremaster'    => "Хранитель мудрости",
        'suggestedPl'   => "Рекомендуемое количество игроков",
        'keepsPvpFlag'  => "Включает доступность PvP",
        'daily'         => "Ежедневно",
        'weekly'        => "Раз в неделю",
        'monthly'       => "Ежемесячно",
        'sharable'      => "Раздается",
        'notSharable'   => "Не раздается",
        'repeatable'    => "Повторяемый",
        'reqQ'          => "Требует",
        'reqQDesc'      => "Чтобы получить это задание, вы должны завершить все указанные задания",
        'reqOneQ'       => "Требуется Один из",
        'reqOneQDesc'   => "Чтобы получить это задание, необходимо выполнить одно из следующих заданий",
        'opensQ'        => "Открывает доступ к заданиям",
        'opensQDesc'    => "Выполнение этого задания требует, чтобы эти задания",
        'closesQ'       => "Заканчивает задание",
        'closesQDesc'   => "Завершив этот квест, вы не сможете выполнять эти квесты",
        'enablesQ'      => "Позволяет",
        'enablesQDesc'  => "Кода это задание активно, вы сможете выполнять эти задания",
        'enabledByQ'    => "Включена по",
        'enabledByQDesc'=> "Вы можете получить это задание, только когда эти задания доступны",
        'gainsDesc'     => "По завершении этого задания, вы получите",
        'theTitle'      => '"%s"',                          // empty on purpose!
        'mailDelivery'  => "Вы получите это письмо%s%s",
        'mailBy'        => ' от <a href="?npc=%d">%s</a>',
        'mailIn'        => " через %s",
        'unavailable'   => "пометили это задание как устаревшее — его нельзя получить или выполнить.",
        'experience'    => "опыта",
        'expConvert'    => "(или %s на %d-м уровне)",
        'expConvert2'   => "%s на %d-м уровне",
        'chooseItems'   => "Вам дадут возможность выбрать одну из следующих наград",
        'receiveItems'  => "Вы получите",
        'receiveAlso'   => "Вы также получите",
        'spellCast'     => "Следующее заклинание будет наложено на вас",
        'spellLearn'    => "Вы изучите",
        'bonusTalents'  => "%d |4очко талантов:очка талантов:очков талантов;",
        'spellDisplayed'=> ' (показано: <a href="?spell=%d">%s</a>)',
        'attachment'    => "[Attachment]",
        'questInfo'     => array(
              0 => "Обычный",            1 => "Группа",             21 => "Жизнь",              41 => "PvP",                62 => "Рейд",               81 => "Подземелье",         82 => "Игровое событие",
             83 => "Легенда",           84 => "Сопровождение",      85 => "Героическое",        88 => "Рейд (10)",          89 => "Рейд (25)"
        ),
        'cat'           => array(
            0 => array( "Восточные королевства",
                  36 => "Альтеракские горы",               3 => "Бесплодные земли",               11 => "Болотина",                        8 => "Болото Печали",                  47 => "Внутренние земли",
                 139 => "Восточные Чумные земли",          4 => "Выжженные земли",               279 => "Даларанский кратер",              1 => "Дун Морог",                      28 => "Западные Чумные земли",
                  40 => "Западный Край",                  44 => "Красногорье",                  3430 => "Леса Вечной Песни",              38 => "Лок Модан",                    3487 => "Луносвет",
                  45 => "Нагорье Арати",                4080 => "Остров Кель'Данас",              41 => "Перевал Мертвого Ветра",       1497 => "Подгород",                     2257 => "Подземный поезд",
                 267 => "Предгорья Хилсбрада",          3433 => "Призрачные земли",               46 => "Пылающие степи",                130 => "Серебряный бор",               1537 => "Стальгорн",
                  10 => "Сумеречный лес",                 33 => "Тернистая долина",               85 => "Тирисфальские леса",             51 => "Тлеющее ущелье",                 25 => "Черная гора",
                4298 => "Чумные земли: Анклав Алого ордена",1519 => "Штормград",                  12 => "Элвиннский лес"
            ),
            1 => array( "Калимдор",
                  16 => "Азшара",                       1638 => "Громовой Утес",                1657 => "Дарнас",                         14 => "Дуротар",                       618 => "Зимние Ключи",
                 406 => "Когтистые горы",                490 => "Кратер Ун'Горо",               1216 => "Крепость Древобрюхов",          493 => "Лунная поляна",                 215 => "Мулгор",
                1637 => "Оргриммар",                     361 => "Оскверненный лес",             3525 => "Остров Кровавой Дымки",        3524 => "Остров Лазурной Дымки",         405 => "Пустоши",
                  15 => "Пылевые топи",                 1377 => "Силитус",                        17 => "Степи",                         440 => "Танарис",                       141 => "Тельдрассил",
                 148 => "Темные берега",                 400 => "Тысяча Игл",                    357 => "Фералас",                      3557 => "Экзодар",                       331 => "Ясеневый лес"
            ),
            8 => array( "Запределье",
                3520 => "Долина Призрачной Луны",       3521 => "Зангартопь",                   3519 => "Лес Тероккар",                 3518 => "Награнд",                      3522 => "Острогорье",
                3483 => "Полуостров Адского Пламени",   3523 => "Пустоверть",                   3679 => "Скеттис",                      3703 => "Шаттрат"
            ),
           10 => array( "Нордскол",
                3537 => "Борейская тундра",               67 => "Грозовая Гряда",               4742 => "Лагерь Хротгара",              4395 => "Даларан",                        65 => "Драконий Погост",
                  66 => "Зул'Драк",                      210 => "Ледяная Корона",               3711 => "Низина Шолазар",               4197 => "Озеро Ледяных Оков",            495 => "Ревущий фьорд",
                 394 => "Седые холмы",                  4024 => "Хладарра"
             ),
            6 => array( "Поля боя",
                2597 => "Альтеракская долина",          4384 => "Берег Древних",                3358 => "Низина Арати",                 3820 => "Око Бури",                     4710 => "Остров Завоеваний",
                 -25 => "Поля сражений",                3277 => "Ущелье Песни Войны"
            ),
            4 => array( "Классы",
                 -81 => "Воин",                         -263 => "Друид",                        -262 => "Жрец",                         -161 => "Маг",                          -261 => "Охотник",
                -141 => "Паладин",                      -162 => "Разбойник",                    -372 => "Рыцарь смерти",                 -61 => "Чернокнижник",                  -82 => "Шаман"
            ),
            2 => array( "Подземелья",
                4277 => "Азжол-Неруб",                  4415 => "Аметистовая крепость",         4494 => "Ан'кахет: Старое Королевство", 3848 => "Аркатрац",                     3790 => "Аукенайские гробницы",
                3562 => "Бастионы Адского Пламени",     3847 => "Ботаника",                     1196 => "Вершина Утгард",               1584 => "Глубины Черной горы",           721 => "Гномреган",
                3792 => "Гробницы Маны",                4416 => "Гундрак",                      2557 => "Забытый Город",                4820 => "Залы Отражений",               1477 => "Затонувший храм",
                1176 => "Зул'Фаррак",                   4723 => "Испытание чемпиона",           3845 => "Крепость Бурь",                4196 => "Крепость Драк'Тарон",           209 => "Крепость Темного Клыка",
                 206 => "Крепость Утгард",              4809 => "Кузня Душ",                    3713 => "Кузня Крови",                   722 => "Курганы Иглошкурых",            491 => "Лабиринты Иглошкурых",
                2100 => "Мародон",                      1581 => "Мертвые копи",                 3849 => "Механар",                       796 => "Монастырь Алого ордена",       2057 => "Некроситет",
                4120 => "Нексус",                        719 => "Непроглядная Пучина",          3716 => "Нижетопь",                     2437 => "Огненная пропасть",            4228 => "Окулус",
                4100 => "Очищение Стратхольма",         3715 => "Паровое подземелье",           1941 => "Пещеры Времени",                718 => "Пещеры Стенаний",              1583 => "Пик Черной горы",
                3714 => "Разрушенные залы",             3905 => "Резервуар Кривого Клыка",      3791 => "Сетеккские залы",              2367 => "Старые предгорья Хилсбрада",   2017 => "Стратхольм",
                3789 => "Темный лабиринт",              4131 => "Терраса Магистров",             717 => "Тюрьма",                       3717 => "Узилище",                      1337 => "Ульдаман",
                2366 => "Черные топи",                  4264 => "Чертоги Камня",                4272 => "Чертоги Молний",               4813 => "Яма Сарона"
            ),
            5 => array( "Профессии",
                -181 => "Алхимия",                      -201 => "Инженерное дело",              -182 => "Кожевничество",                -121 => "Кузнечное дело",               -304 => "Кулинария",
                -371 => "Начертание",                   -324 => "Первая помощь",                -264 => "Портняжное дело",              -101 => "Рыбная ловля",                  -24 => "Травничество",
                -373 => "Ювелирное дело"
            ),
            3 => array( "Рейды",
                3923 => "Логово Груула",                3428 => "Ан'Кираж",                     3606 => "Вершина Хиджала",              3805 => "Зул'Аман",                     1977 => "Зул'Гуруб",
                4722 => "Испытание крестоносца",        3457 => "Каражан",                      3845 => "Крепость Бурь",                2677 => "Логово Крыла Тьмы",            3836 => "Логово Магтеридона",
                2159 => "Логово Ониксии",               3456 => "Наксрамас",                    4493 => "Обсидиановое святилище",       2717 => "Огненные Недра",               4500 => "Око Вечности",
                4075 => "Плато Солнечного Колодца",     4987 => "Рубиновое святилище",          3429 => "Руины Ан'Киража",              4603 => "Склеп Аркавона",               4273 => "Ульдуар",
                4812 => "Цитадель Ледяной Короны",      3959 => "Черный храм"
            ),
            9 => array( "Игровые события",
                -370 => "Хмельной фестиваль",          -1002 => "Детская неделя",               -364 => "Ярмарка Новолуния",             -41 => "День Мертвых",                -1003 => "Тыквовин",
               -1005 => "Фестиваль урожая",             -376 => "Любовная лихорадка",           -366 => "Лунный фестиваль",             -369 => "Огненный солнцеворот",        -1006 => "Новый Год",
                -375 => "Пиршество странников",         -374 => "Сад чудес",                   -1001 => "Зимний Покров"
            ),
            7 => array( "Разное",
                -365 => "Ан'киражская война",          -1010 => "Поиск подземелий",               -1 => "Эпический",                    -344 => "Легендарный",                  -367 => "Репутация",
                -368 => "Вторжение",                    -241 => "Турнир"
            ),
           -2 => "Разное"
        )
    ),
    'icon'  => array(
        'notFound'      => "Этой иконки не существует"
    ),
    'title' => array(
        'notFound'      => "Такое звание не существует.",
        '_transfer'     => 'Этот предмет превратится в <a href="?title=%d" class="q1">%s</a>, если вы перейдете за <span class="icon-%s">%s</span>.',
        'cat'           => array(
            'Общее",      "PvP",    "Репутация",       "Подземелья и рейды",     "Задания",       "Профессии",      "Игровые события'
        )
    ),
    'skill' => array(
        'notFound'      => "Этот навык не существует.",
        'cat'           => array(
            -6 => "Спутники",           -5 => "Транспорт",          -4 => "Классовые навыки",   5 => "Характеристики",      6 => "Оружейные навыки",    7 => "Классовые навыки",    8 => "Доспехи",
             9 => "Вторичные навыки",   10 => "Языки",              11 => "Профессии"
        )
    ),
    'currency' => array(
        'notFound'      => "Такая валюта не существует.",
        'cap'           => "Максимум всего",
        'cat'           => array(
            1 => "Разное", 2 => "PvP", 4 => "World of Warcraft", 21 => "Wrath of the Lich King", 22 => "Подземелья и рейды", 23 => "Burning Crusade", 41 => "Test", 3 => "Неактивно"
        )
    ),
    'sound' => array(
        'notFound'      => "Этот звук не существует.",
        'foundIn'       => "Этот Звук может быть найден в следующих зонах:",
        'goToPlaylist'  => "Перейти к плейлисту",
        'music'         => "Музыка",
        'intro'         => "Начальная музыка",
        'ambience'      => "Атмосфера",
        'cat'           => array(
            null,              "Spells",            "User Interface", "Footsteps",   "Weapons Impacts", null,      "Weapons Misses", null,            null,         "Pick Up/Put Down",
            "NPC Combat",      null,                "Errors",         "Nature",      "Objects",         null,      "Death",          "NPC Greetings", null,         "Armor",
            "Footstep Splash", "Water (Character)", "Water",          "Tradeskills", "Misc Ambience",   "Doodads", "Spell Fizzle",   "NPC Loops",     "Zone Music", "Emotes",
            "Narration Music", "Narration",         50 => "Zone Ambience", 52 => "Emitters", 53 => "Vehicles", 1000 => "Мой плейлист"
        )
    ),
    'pet'      => array(
        'notFound'      => "Такой породы питомцев не существует.",
        'exotic'        => "Экзотический",
        'cat'           => ["Свирепость", "Упорство", "Хитрость"],
        'food'          => ["Мясо", "Рыба", "Сыр", "Хлеб", "Грибы", "Фрукты", "Сырое мясо", "Сырая рыба"]
    ),
    'faction' => array(
        'notFound'      => "Такая фракция не существует.",
        'spillover'     => "[Reputation Spillover]",
        'spilloverDesc' => "[Gaining reputation with this faction also yields a proportional gain with the factions listed below.]",
        'maxStanding'   => "Макс Уровень",
        'quartermaster' => "Интендант",
        'customRewRate' => "[Custom Reward Rate]",
        '_transfer'     => '[The reputation with this faction will be converted to <a href="?faction=%d" class="q1">%s</a> if you transfer to <span class="icon-%s">%s</span>.]',
        'cat'           => array(
            1118 => ["World of Warcraft", 469 => "Альянс", 169 => "Картель Хитрая Шестеренка", 67 => "Орда", 891 => "Силы Альянса", 892 => "Силы Орды"],
            980  => ["The Burning Crusade", 936 => "Город Шаттрат"],
            1097 => ["Wrath of the Lich King", 1052 => "Экспедиция Орды", 1117 => "Низина Шолазар", 1037 => "Авангард Альянса"],
            0    => "Другое"
        )
    ),
    'itemset' => array(
        'notFound'      => "Такой комплект не существует.",
        '_desc'         => "<b>%s</b> — <b>%s</b>. Он состоит из %s предметов.",
        '_descTagless'  => "<b>%s</b> — набор из %s предметов.",
        '_setBonuses'   => "Бонус за комплект",
        '_conveyBonus'  => "Ношение большего числа предметов из этого комплекта предоставит бонусы для вашего персонажа.",
        '_pieces'       => "частей",
        '_unavailable'  => "Этот набор предметов не доступен игрокам.",
        '_tag'          => "Тэг",
        'summary'       => "Сводка",
        'notes'         => array(
            null,                                       "Комплект подземелий 1",                "Комплект подземелий 2",                        "Рейдовый комплект Tier 1",
            "Рейдовый комплект Tier 2",                 "Рейдовый комплект Tier 3",             "PvP Комплект для 60 уровня",                   "PvP Комплект для 60 уровня (старая версия)",
            "Эпический PvP Комплект для 60 уровня",     "Комплект из Руин Ан'Киража",           "Комплект из Храма Ан'Киража",                  "Комплект Зул'Гуруба",
            "Рейдовый комплект Tier 4",                 "Рейдовый комплект Tier 5",             "Комплект подземелий 3",                        "Комплект Низин Арати",
            "Редкий PvP Комплект для 70 уровня",        "Комплект Арены 1 сезона",              "Рейдовый комплект Tier 6",                     "Комплект Арены 2 сезона",
            "Комплект Арены 3 сезона",                  "PvP Комплект для 70 уровня 2",         "Комплект Арены 4 сезона",                      "Рейдовый комплект Tier 7",
            "Комплект Арены 5 сезона",                  "Рейдовый комплект Tier 8",             "Комплект Арены 6 сезона",                      "Рейдовый комплект Tier 9",
            "Комплект Арены 7 сезона",                  "Рейдовый комплект Tier 10",            "Комплект Арены 8 сезона"
        ),
        'types'         => array(
            null,               "Ткань",                "Кожа",                 "Кольчуга",                 "Латы",                     "Кинжал",                   "Кольцо",
            "Кистевое оружие",  "Одноручный топор",     "Одноручное дробящее",  "Одноручный меч",           "Аксессуар",                "Амулет"
        )
    ),
    'spell' => array(
        'notFound'      => "Такое заклинание не существует.",
        '_spellDetails' => "Описание заклинания",
        '_cost'         => "Цена",
        '_range'        => "Радиус действия",
        '_castTime'     => "Применение",
        '_cooldown'     => "Восстановление",
        '_distUnit'     => "метров",
        '_forms'        => "Форма",
        '_aura'         => "аура",
        '_effect'       => "Эффект",
        '_none'         => "Нет",
        '_gcd'          => "ГКД",
        '_globCD'       => "Общее время восстановления (GCD)",
        '_gcdCategory'  => "Категория ГКД",
        '_value'        => "Значение",
        '_radius'       => "Радиус действия",
        '_interval'     => "Интервал",
        '_inSlot'       => "в слот",
        '_collapseAll'  => "Свернуть все",
        '_expandAll'    => "Развернуть все",
        '_transfer'     => 'Этот предмет превратится в <a href="?spell=%d" class="q%d icontiny tinyspecial" style="background-image: url('.STATIC_URL.'/images/wow/icons/tiny/%s.gif)">%s</a>, если вы перейдете за <span class="icon-%s">%s</span>.',
        'discovered'    => "Изучается путём освоения местности",
        'ppm'           => "Срабатывает %s раз в минуту",
        'procChance'    => "Шанс срабатывания",
        'starter'       => "Начальное заклинание",
        'trainingCost'  => "Цена обучения",
        'remaining'     => "Осталось: %s",
        'untilCanceled' => "до отмены",
        'castIn'        => "Применение: %s сек.",
        'instantPhys'   => "Мгновенное действие",
        'instantMagic'  => "Мгновенное действие",
        'channeled'     => "Направляемое",
        'range'         => "Радиус действия: %s м",
        'meleeRange'    => "Дистанция ближнего боя",
        'unlimRange'    => "Неограниченное расстояние",
        'reagents'      => "Реагент",
        'tools'         => "Инструменты",
        'home'          => "%lt;Гостиница&gt;",
        'pctCostOf'     => "от базовой %s",
        'costPerSec'    => ", плюс %s в секунду",
        'costPerLevel'  => ", плюс %s за уровень",
        'stackGroup'    => "[Stack Group]",
        'linkedWith'    => "[Linked with]",
        '_scaling'      => "[Scaling]",
        'scaling'       => array(
            'directSP' => "[+%.2f%% of spell power to direct component]",        'directAP' => "[+%.2f%% of attack power to direct component]",
            'dotSP'    => "[+%.2f%% of spell power per tick]",                   'dotAP'    => "[+%.2f%% of attack power per tick]"
        ),
        'powerRunes'    => ["Лед", "Руна льда", "Руна крови", "Смерти"],
        'powerTypes'    => array(
            // conventional
              -2 => "Здоровье",            0 => "Мана",                1 => "Ярость",              2 => "Тонус",               3 => "Энергия",             4 => "Настроение",
               5 => "Руны",                6 => "Руническая сила",
            // powerDisplay
              -1 => "Боеприпасы",        -41 => "Колчедан",          -61 => "Давление пара",    -101 => "Жар",              -121 => "Слизнюк",          -141 => "Сила крови",
            -142 => "Гнев"
        ),
        'relItems'      => array(
            'base'    => "<small>Показать %s, относящиеся к профессии <b>%s</b></small>",
            'link'    => " или ",
            'recipes' => '<a href="?items=9.%s">рецепты</a>',
            'crafted' => '<a href="?items&filter=cr=86;crs=%s;crv=0">производимые предметы</a>'
        ),
        'cat'           => array(
              7 => "Способности",
            -13 => "Символы",
            -11 => array("Умения", 8 => "Броня", 10 => "Языки", 6 => "Оружие"),
             -4 => "Классовые навыки",
             -2 => "Таланты",
             -6 => "Спутники",
             -5 => "Транспорт",
             -3 => array(
                "Способности питомцев",     782 => "Вурдалак",          270 => "Общий",                 211 => "Вепрь",                     208 => "Волк",                  654 => "Гиена",                 787 => "Гончая Недр",
                215 => "Горилла",           218 => "Долгоног",          763 => "Дракондор",             788 => "Дух зверя",                 781 => "Дьявозавр",             768 => "Змей",                  209 => "Кошка",
                214 => "Краб",              212 => "Кроколиск",         656 => "Крылатый змей",         653 => "Летучая мышь",              786 => "Люторог",               210 => "Медведь",               775 => "Мотылек",
                767 => "Опустошитель",      785 => "Оса",               213 => "Падальщик",             203 => "Паук",                      766 => "Прыгуана",              783 => "Силитид",               764 => "Скат Пустоты",
                236 => "Скорпид",           655 => "Сова",              765 => "Спороскат",             780 => "Химера",                    784 => "Червь",                 251 => "Черепаха",              217 => "Ящер",
                761 => "Страж Скверны",     189 => "Охотник Скверны",   188 => "Бес",                   205 => "Суккуб",                    204 => "Демон Бездны"
            ),
             -7 => array("Таланты питомцев", 411 => "Хитрость", 410 => "Свирепость", 409 => "Упорство"),
             11 => array(
                "Профессии",
                171 => "Алхимия",
                164 => array("Кузнечное дело", 9788 => "Школа брони", 9787 => "Школа оружейников", 17041 => "Мастер школы топора", 17040 => "Мастер школы молота", 17039 => "Мастер ковки клинков"),
                333 => "Наложение чар",
                202 => array("Инженерное дело", 20219 => "Гномская механика", 20222 => "Гоблинская механика"),
                182 => "Травничество",
                773 => "Начертание",
                755 => "Ювелирное дело",
                165 => array("Кожевничество", 10656 => "Драконья чешуя", 10658 => "Стихия", 10660 => "Племена"),
                186 => "Горное дело",
                393 => "Снятие шкур",
                197 => array("Портняжное дело", 26798 => "Портняжное дело изначальной луноткани", 26801 => "Портняжное дело тенеткани", 26797 => "Портняжное дело чародейского огня")
            ),
              9 => array("Вторичные навыки", 185 => "Кулинария", 129 => "Первая помощь", 356 => "Рыбная ловля", 762 => "Верховая езда"),
             -9 => "Способности ГМ",
             -8 => "Способности НИП",
              0 => "Разное"
        ),
        'armorSubClass' => array(
            "Разное",                               "Тканевые",                             "Кожаные",                              "Кольчужные",                           "Латные",
            null,                                   "Щиты",                                 "Манускрипты",                          "Идолы",                                "Тотемы",
            "Печати"
        ),
        'weaponSubClass' => array(
            15 => "Кинжалы",                        13 => "Кистевое",                        0 => "Одноручные топоры",               4 => "Одноручное дробящее",             7 => "Одноручные мечи",
             6 => "Древковое",                      10 => "Посохи",                          1 => "Двуручные топоры",                5 => "Двуручное дробящее",              8 => "Двуручные мечи",
             2 => "Луки",                           18 => "Арбалеты",                        3 => "Огнестрельное",                  16 => "Метательное",                    19 => "Жезлы",
            20 => "Удочки",                         14 => "Разное"
        ),
        'subClassMasks' => array(
            0x02A5F3 => "Оружие ближнего боя",      0x0060 => "Щит",                        0x04000C => "Оружие дальнего боя",      0xA091 => "Одноручное оружие ближнего боя"
        ),
        'traitShort'    => array(
            'atkpwr'    => "СА",                    'rgdatkpwr' => "Сил",                   'splpwr'    => "СЗ",                    'arcsplpwr' => "Урон",                  'firsplpwr' => "Урон",
            'frosplpwr' => "Урон",                  'holsplpwr' => "Урон",                  'natsplpwr' => "Урон",                  'shasplpwr' => "Урон",                  'splheal'   => "Исцеление",
            'str'       => "Сила",                  'agi'       => "Ловк",                  'sta'       => "Выно",                  'int'       => "Инт",                   'spi'       => "Дух"
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
            null,                                   "Взлом замков",                         "Травничество",                         "Горное дело",                          "Обезвреживание ловушки",
            "Открытие",                             "Клад (DND)",                           "Эльфийские самоцветы (DND)",           "Закрытие",                             "Установка",
            "Быстрое открытие",                     "Быстрое закрытие",                     "Открытие: механика",                   "Открытие: наклон",                     "Открытие: атака",
            "Газ'рилльское украшение",              "Взрыв",                                "Медленное открытие (PvP)",             "Медленное закрытие (PvP)",             "Рыбная ловля (DND)",
            "Начертание",                           "Открыть на ходу"
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
        'notFound'      => "Такой предмет не существует.",
        'armor'         => "Броня: %s",
        'block'         => "Блок: %s",
        'charges'       => "%d |4заряд:заряда:зарядов;",
        'locked'        => "Заперто",
        'ratingString'  => "%s&nbsp;@&nbsp;L%s",
        'heroic'        => "Героический",
        'startQuest'    => "Этот предмет позволяет получить задание.",
        'bagSlotString' => '%2$s (%1$d |4ячейка:ячейки:ячеек;)',
        'fap'           => "Сила атаки зверя",
        'durability'    => "Прочность: %d / %d",
        'realTime'      => "реальное время",
        'conjured'      => "Сотворенный предмет",
        'sellPrice'     => "Цена продажи",
        'itemLevel'     => "Уровень предмета: %d",
        'randEnchant'   => "&lt;Случайное зачарование&gt",
        'readClick'     => "&lt;Щелкните правой кнопкой мыши, чтобы прочитать.&gt",
        'openClick'     => "&lt;Щелкните правой кнопкой мыши, чтобы открыть.&gt",
        'setBonus'      => "Комплект (%d |4предмет:предмета:предметов;): %s",
        'setName'       => "%s (%d/%d)",
        'partyLoot'     => "Добыча группы",
        'smartLoot'     => "Умное распределение добычи",
        'indestructible'=> "Невозможно выбросить",
        'deprecated'    => "Устарело",
        'useInShape'    => "Используется в формах",
        'useInArena'    => "Используется на аренах",
        'refundable'    => "Подлежит возврату",
        'noNeedRoll'    => 'Нельзя говорить "Мне это нужно"',
        'atKeyring'     => "Может быть помещён в связку для ключей",
        'worth'         => "Деньги",
        'consumable'    => "Расходуется",
        'nonConsumable' => "Не расходуется",
        'accountWide'   => "Привязано к учетной записи",
        'millable'      => "Можно растолочь",
        'noEquipCD'     => "Нет отката при надевании",
        'prospectable'  => "Просеиваемое",
        'disenchantable'=> "Распыляемый",
        'cantDisenchant'=> "Нельзя распылить",
        'repairCost'    => "Цена починки",
        'tool'          => "Инструмент",
        'cost'          => "Цена",
        'content'       => "Материал",
        '_transfer'     => 'Этот предмет превратится в <a href="?item=%d" class="q%d icontiny tinyspecial" style="background-image: url('.STATIC_URL.'/images/wow/icons/tiny/%s.gif)">%s</a>, если вы перейдете за <span class="icon-%s">%s</span>.',
        '_unavailable'  => "Этот предмет не доступен игрокам.",
        '_rndEnchants'  => "Случайные улучшения",
        '_chance'       => "(шанс %s%%)",
        'slot'          => "Слот",
        '_quality'      => "Качество",
        'usableBy'      => "Используется (кем)",
        'buyout'        => "Цена выкупа",
        'each'          => "каждый",
        'tabOther'      => "Другое",
        'reqMinLevel'   => "Требуется уровень: %d",
        'reqLevelRange' => "Требуемый уровень: %d – %d (%d)",
        'unique'        => ["Уникальный",                   "Уникальный (%d)",  "Уникальный: %s (%d)"                       ],
        'uniqueEquipped'=> ["Уникальный использующийся",    null,               "Уникальный использующийся предмет: %s (%d)"],
        'speed'         => "Скорость",
        'dps'           => "(%.1f ед. урона в секунду)",
        'damage'        => array(                           // *DAMAGE_TEMPLATE*
                        //  basic,                              basic /w school,                            add basic,                                      add basic /w school
            'single'    => ["Урон: %d",                         "%d ед. |3-6(%s)",                          "+ %d ед. урона",                               "+%d ед. урона (%s)"            ],
            'range'     => ["Урон: %d - %d",                    "%d - %d ед. |3-6(%s)",                     "+ %d - %d ед. урона",                          "+%d - %d ед. урона (%s)"       ],
            'ammo'      => ["Добавляет %g ед. урона в секунду", "Добавляет %g ед. урона (%s) в секунду",    "+ ед. урона в секунду от боеприпасов (%g)",    "+ %g %s ед. урона в секунду"   ]
        ),
        'gems'          => "Самоцветы",
        'socketBonus'   => "При соответствии цвета",
        'socket'        => array(
            "Особое гнездо",        "Красное гнездо",   "Желтое гнездо",        "Синее гнездо",           -1 => "Бесцветное гнездо"
        ),
        'gemColors'     => array(                           // *_GEM
            "Особый",               "Красный",          "Желтый",               "Синий"
        ),
        'gemConditions' => array(                           // ENCHANT_CONDITION_*      so whats that pipe-code..?
            2 => "меньше, чем %d |4камень:камня:камней; |3-1(%s) цвета",
            3 => "больше |3-7(%s), чем |3-7(%s) камней",
            5 => "хотя бы %d |4камень:камня:камней; |3-1(%s) цвета"
        ),
        'reqRating'     => array(                           // ITEM_REQ_ARENA_RATING*
            "Требуется личный и командный рейтинг на арене не ниже %d",
            "Требуется личный рейтинг и рейтинг команды Арены %d|nв команде 3 на 3 или 5 на 5",
            "Требуется личный рейтинг и рейтинг команды Арены %d|nв команде 5 на 5"
        ),
        'quality'       => array(
            "Низкий",               "Обычный",          "Необычный",            "Редкий",
            "Эпический",            "Легендарный",      "Артефакт",             "Фамильная черта"
        ),
        'trigger'       => array(
            "Использование: ",      "Если на персонаже: ",                      "Возможный эффект при попадании: ",
            "",                     "",                 "",                     ""
        ),
        'bonding'       => array(
            "Привязано к учетной записи",               "Персональный при поднятии",                            "Становится персональным при надевании",
            "Персональный при использовании",           "Предмет, необходимый для задания",                     "Предмет, необходимый для задания"
        ),
        "bagFamily"     => array(
            "Сумка",                "Колчан",           "Подсумок",             "Сумка душ",                    "Сумка кожевника",
            "Сумка начертателя",    "Сумка травника",   "Сумка зачаровывателя", "Сумка инженера",               null, /*Ключ*/
            "Сумка ювелира",        "Сумка шахтера"
        ),
        'inventoryType' => array(
            null,                   "Голова",           "Шея",                  "Плечи",                        "Рубашка",
            "Грудь",                "Пояс",             "Ноги",                 "Ступни",                       "Запястья",
            "Кисти рук",            "Палец",            "Аксессуар",            "Одноручное",                   "Левая рука", /*Shield*/
            "Дальний бой",          "Спина",            "Двуручное",            "Сумка",                        "Гербовая накидка",
            null, /*Грудь*/         "Правая рука",      "Левая рука",           "Левая рука",                   "Боеприпасы",
            "Метательное",          null, /*Спина*/     "Колчан",               "Реликвия"
        ),
        'armorSubClass' => array(
            "Разное",               "Ткань",            "Кожа",                 "Кольчуга",                     "Латы",
            null,                   "Щит",              "Манускрипт",           "Идол",                         "Тотем",
            "Печать"
        ),
        'weaponSubClass' => array(
            "топор",                "топор",            "Лук",                  "Огнестрельное",                "дробящее",
            "дробящее",             "Древковое",        "меч",                  "меч",                          null,
            "Посох",                null,               null,                   "Кистевое оружие",              "Разное",
            "Кинжал",               "Метательное",      null,                   "Арбалет",                      "Жезл",
            "Удочка"
        ),
        'projectileSubClass' => array(
            null,                   null,               "Стрелы",               "Пули",                         null
        ),
        'elixirType'    => [null, "Бой", "Охранный"],
        'cat'           => array(                           // should be ordered by content firts, then alphabeticaly
             2 => "Оружие",                                 // self::$spell['weaponSubClass']
             4 => array("Броня", array(
                 1 => "Тканевые",                    2 => "Кожаные",                 3 => "Кольчужные",              4 => "Латные",                  7 => "Манускрипты",             8 => "Идолы",
                 9 => "Тотемы",                     10 => "Печати",                 -6 => "Плащи",                  -5 => "Левая рука",              6 => "Щиты",                   -8 => "Рубашки",
                -7 => "Гербовые накидки",           -3 => "Ожерелья",               -2 => "Кольца",                 -4 => "Аксессуары",              0 => "Разное (доспехи)",
            )),
             1 => array("Контейнеры", array(
                 0 => "Сумки",                       1 => "Сумки душ",               3 => "Сумки зачаровывателя",    4 => "Сумки инженера",          7 => "Сумки кожевника",         8 => "Сумки начертателя",
                 2 => "Сумки травника",              6 => "Сумки шахтера",           5 => "Сумки ювелира",
            )),
             0 => array("Расходуемые", array(
                 7 => "Бинты",                       5 => "Еда и напитки",           1 => "Зелья",                   0 => "Расходуемые",             4 => "Свитки",                 -3 => "Улучшения (временные)",
                 6 => "Улучшения (постоянные)",      3 => "Фляги",                   2 => ["Эликсиры", [1 => "Боевые", 2 => "Охранные"]],            8 => "Разное (расходуемые)"
            )),
            16 => array("Символы", array(
                 1 => "Символ воина",                2 => "Символ паладина",         3 => "Символ охотника",         4 => "Символ разбойника",       5 => "Символ жреца",            6 => "Символ рыцаря смерти",
                 7 => "Символ шамана",               8 => "Символ мага",             9 => "Символ чернокнижника",   11 => "Символ друида"
            )),
             7 => array("Хозяйственные товары", array(
                14 => "Улучшения брони",             5 => "Ткань",                   3 => "Устройства",             10 => "Элементаль",             12 => "Наложение чар",           2 => "Взрывчатка",
                 9 => "Травы",                       4 => "Ювелирное дело",          6 => "Кожа",                   13 => "Материалы",               8 => "Мясо",                    7 => "Металл и камни",
                 1 => "Детали",                     15 => "Улучшения оружия",       11 => "Разное (хозяйственные товары)"
             )),
             6 => ["Боеприпасы",    [                2 => "Стрелы",                  3 => "Пули"    ]],
            11 => ["Колчаны",       [                3 => "Подсумки",                2 => "Колчаны" ]],
             9 => array("Рецепты", array(
                 0 => "Книги",                       6 => "Рецепты алхимии",         4 => "Кузнечное дело",          5 => "Рецепты кулинарии",       8 => "Зачаровывание",           3 => "Инженерное дело",
                 7 => "Первая помощь",               9 => "Рыбная ловля",           11 => "Технологии Начертания",  10 => "Ювелирное дело",          1 => "Кожевничество",          12 => "Руководства по Шахтерскому делу",
                 2 => "Портняжное дело"
            )),
             3 => array("Самоцветы", array(
                 6 => "Особые",                      0 => "Красные",                 1 => "Синие",                   2 => "Желтые",                  3 => "Фиолетовые",              4 => "Зелёные",
                 5 => "Оранжевые",                   8 => "Радужные",                7 => "Простые"
            )),
            15 => array("Разное", array(
                -2 => "Фрагмент доспехов",           3 => "Праздник",                0 => "Хлам",                    1 => "Реагент",                 5 => "Транспорт",              -7 => "Летающий транспорт",
                 2 => "Спутники",                    4 => "Разное"
            )),
            10 => "Валюта",
            12 => "Задание",
            13 => "Ключи",
        ),
        'statType'      => array(
            "к мане",
            "к здоровью",
            null,
            "к ловкости",
            "к силе",
            "к интеллекту",
            "к духу",
            "к выносливости",
            null, null, null, null,
            "Рейтинг защиты +%d.",
            "Рейтинг уклонения +%d.",
            "Рейтинг парирования +%d.",
            "Рейтинг блокирования щитом +%d.",
            "Рейтинг меткости (оруж. ближ. боя) +%d.",
            "Рейтинг меткости (оруж. дальн. боя) +%d.",
            "Рейтинг меткости (заклинания) +%d.",
            "Рейтинг крит. удара оруж. ближнего боя +%d.",
            "Рейтинг крит. удара оруж. дальнего боя +%d.",
            "Рейтинг критического удара (заклинания) +%d.",
            "Рейтинг уклонения от удара оруж. ближ. боя +%d.",
            "Рейтинг уклонения от удара оруж. дальн. боя +%d.",
            "Рейтинг уклонения от удара (заклинания) +%d.",
            "Рейтинг уклонения от крит. удара оруж. ближнего боя +%d.",
            "Рейтинг уклонения от крит. удара оруж. дистанц. боя +%d.",
            "Рейтинг уклонения от крит. удара (заклинания) +%d.",
            "Рейтинг скорости ближнего боя +%d.",
            "Рейтинг скорости дальнего боя +%d.",
            "Рейтинг скорости боя (заклинания) +%d.",
            "Рейтинг меткости +%d.",
            "Рейтинг критического удара +%d.",
            "Рейтинг уклонения от удара +%d.",
            "Рейтинг уклонения от крит. удара +%d.",
            "Рейтинг устойчивости +%d.",
            "Рейтинг скорости +%d.",
            "Рейтинг мастерства +%d.",
            "Увеличивает силу атаки на %d.",
            "Сила атаки дальнего боя +%d.",
            "Увеличивает силу атаки на %d в облике кошки, медведя, лютого медведя или лунного совуха.",
            "Увеличивает наносимый игроком урон от магических эффектов и заклинаний на %d ед.",
            "Увеличивает целительное действие магических заклинаний и эффектов на %d ед.",
            "Восполнение %d ед. маны раз в 5 секунд.",
            "Повышает рейтинг пробивания брони на %d.",
            "Увеличивает силу заклинаний на %d.",
            "Восполняет %d ед. здоровья каждые 5 секунд.",
            "Увеличивает проникающую способность заклинаний на %d.",
            "Увеличивает показатель блокирования щита на %d.",
            "Unknown Bonus #%d (%d)",
        )
    )
);

?>
