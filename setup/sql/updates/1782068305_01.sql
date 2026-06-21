DELETE FROM `aowow_setup_custom_data` WHERE
    `command` = 'currencies' AND
    `entry` IN (1, 61, 81, 103, 104, 181, 221, 241, 301, 341) AND
    `field` IN ('description_loc0', 'description_loc2', 'description_loc3', 'description_loc4', 'description_loc6', 'description_loc8')
;

INSERT IGNORE INTO `aowow_setup_custom_data` VALUES
/* Currency Token Test Token 4 */
('currencies', 1, 'description_loc0', 'Text that describes this item can be found here.', ''),
('currencies', 1, 'description_loc2', 'Un texte qui décrit l\'objet figure ici.', ''),
('currencies', 1, 'description_loc3', 'Text, der den Gegenstand beschreibt, wird hier angezeigt.', ''),
('currencies', 1, 'description_loc4', 'Text that describes this item can be found here.', ''),
('currencies', 1, 'description_loc6', 'Aquí puede encontrarse el texto que describe a este objeto.', ''),
('currencies', 1, 'description_loc8', 'Здесь находится описание предмета.', ''),
/* Dalaran Jewelcrafter's Token */
('currencies', 61, 'description_loc0', 'Tiffany Cartier\'s shop in Dalaran will gladly accept these tokens for unique jewelcrafting recipes.', ''),
('currencies', 61, 'description_loc2', 'La boutique de Tiffany Kartier, à Dalaran, accepte avec joie ces marques contre des dessins de joaillerie uniques.', ''),
('currencies', 61, 'description_loc3', 'Tiffany Cartiers Geschäft in Dalaran wird diese Symbole im Tausch gegen einzigartige Juweliersrezepte dankend annehmen.', ''),
('currencies', 61, 'description_loc4', '达拉然的蒂凡妮·卡蒂亚会欣然接受这些代币，并用稀有的珠宝加工图鉴来交换。', ''),
('currencies', 61, 'description_loc6', 'La tienda de Tiffany Cartier en Dalaran cambiará gustosamente estos talismanes por recetas de joyería.', ''),
('currencies', 61, 'description_loc8', 'В магазине Тиффани Картье, что в Даларане, вам с радостью обменяют эти знаки на уникальные ювелирные эскизы.', ''),
/* Dalaran Cooking Award */
('currencies', 81, 'description_loc0', 'Visit special cooking vendors in Dalaran and the capital cities to to purchase unusual cooking recipes, spices, and even a fine hat!', ''),
('currencies', 81, 'description_loc2', 'Rendez visite aux marchands de fournitures de cuisine à Dalaran et dans les autres capitales pour acheter des recettes de cuisine spéciales, des épices, et même une superbe toque !', ''),
('currencies', 81, 'description_loc3', 'Besucht besondere Kochhändler in Dalaran und den Hauptstädten, um ungewöhnliche Kochrezepte, Gewürze und sogar eine großartige Mütze zu kaufen!', ''),
('currencies', 81, 'description_loc4', '造访达拉然以及各个主城的特殊烹饪供应商，购买罕见的烹饪配方、香料以及大厨的帽子！', ''),
('currencies', 81, 'description_loc6', 'Visita a los vendedores de cocina especiales de Dalaran y de las capitales para comprar recetas de cocina poco frecuentes, especias, ¡e incluso un bonito gorro!', ''),
('currencies', 81, 'description_loc8', 'Посетите торговцев кулинарными товарами в Даларане и других столицах, чтобы приобрести особые кулинарные рецепты, специи и даже головной убор!', ''),
/* Arena Points */
('currencies', 103, 'description_loc0', 'Arena Points are gained by being victorious in arena combat. You can trade in these arena points for fabulous prizes!', ''),
('currencies', 103, 'description_loc2', 'Les points d\'arène se gagnent en obtenant des victoires dans les combats d\'arène. Vous pouvez les échanger contre des objets fabuleux !', ''),
('currencies', 103, 'description_loc3', 'Arenapunkte erhält man für siegreiche Kämpfe in der Arena. Diese Punkte können gegen fantastische Preise eingetauscht werden!', ''),
('currencies', 103, 'description_loc4', '竞技场点数是通过在竞技场战斗中获胜而赢得的。你可以消费这些点数来购买强大的奖励品！', ''),
('currencies', 103, 'description_loc6', 'Ganas puntos de arena al ganar combates en arenas. ¡Podrás intercambiarlos por fabulosos premios!', ''),
('currencies', 103, 'description_loc8', 'Очки арены присуждаются за победы на арене. Вы можете обменивать эти очки на предметы с удивительными свойствами!', ''),
/* Honor Points */
('currencies', 104, 'description_loc0', 'Honor is gained by killing members of the opposite faction in PvP combat. You can use honor points to purchase special items.', ''),
('currencies', 104, 'description_loc2', 'Les points d\’honneur se gagnent en tuant des membres de la faction opposée en combat JcJ. Vous pouvez dépenser des points d\'honneur pour acheter des objets spéciaux.', ''),
('currencies', 104, 'description_loc3', 'Ehre wird durch das Töten gegnerischer Spieler im PvP-Kampf gewonnen. Ehrenpunkte können genutzt werden, um spezielle Gegenstände zu kaufen.', ''),
('currencies', 104, 'description_loc4', '荣誉是通过在PvP战斗中杀死敌对阵营的成员获得的。你可以使用荣誉点数购买特殊的物品。', ''),
('currencies', 104, 'description_loc6', 'Consigues honor al matar miembros de la facción enemiga en combate JcJ. Puedes comprar objetos especiales con los puntos de honor.', ''),
('currencies', 104, 'description_loc8', 'Сражаясь с персонажами противоположной стороны, вы зарабатываете очки чести. Впоследствии их можно тратить на приобретение уникальных предметов.', ''),
/* Honor Points DEPRECATED2 */
('currencies', 181, 'description_loc0', 'If you can read this, you\'ve found a bug. REPORT IT!', ''),
('currencies', 181, 'description_loc2', 'Si vous lisez ceci, c\'est un bug. SIGNALEZ-LE !', ''),
('currencies', 181, 'description_loc3', 'Wenn Ihr das hier lesen könnt, habt Ihr einen Bug gefunden. MELDET IHN!', ''),
('currencies', 181, 'description_loc4', 'If you can read this, you\'ve found a bug. REPORT IT!', ''),
('currencies', 181, 'description_loc6', 'Si puedes leer esto, has encontrado un error. ¡Informa!', ''),
('currencies', 181, 'description_loc8', 'Если вы видите это сообщение, это значит, что вы обнаружили ошибку. Сообщите о ней!', ''),
/* Champion's Seal */
('currencies', 241, 'description_loc0', 'Awarded for valiant acts in the Crusader\'s Coliseum.', ''),
('currencies', 241, 'description_loc2', 'Obtenu en récompense d\’actes de bravoure au colisée des Croisés.', ''),
('currencies', 241, 'description_loc3', 'Werden für hehre Taten im Kolosseum der Kreuzfahrer verliehen.', ''),
('currencies', 241, 'description_loc4', '表彰你在十字军演武场中展示的武勇。', ''),
('currencies', 241, 'description_loc6', 'Otorgado por las hazañas en el Coliseo de los Cruzados.', ''),
('currencies', 241, 'description_loc8', 'За храбрость, проявленную на турнирах Колизея Авангарда.', ''),
/* Emblem of Triumph - reuse Justice Points (395) */
('currencies', 301, 'description_loc0', 'Earned for defeating dungeon or older raid bosses and used to purchase powerful PvE armor and weapons.', ''),
('currencies', 301, 'description_loc2', 'Obtenus pour avoir réussi un donjon ou vaincus les boss d\'anciens raids, et utilisés pour acheter de puissantes armures et armes de JcE.', ''),
('currencies', 301, 'description_loc3', 'Werden als Belohnung für das Bezwingen von Dungeon- oder älteren Schlachtzugsbossen verdient und können für den Erwerb von mächtigen PvE-Waffen und -Rüstungen verwendet werden.', ''),
('currencies', 301, 'description_loc4', '击败较旧的地下城或团队副本中的首领之后获得的奖励，可用来购买威力强大的PVE护甲和武器。', ''),
('currencies', 301, 'description_loc6', 'Se ganan al derrotar a los jefes de mazmorras o bandas antiguas y se usan para comprar armas y armaduras de JcE poderosas.', ''),
('currencies', 301, 'description_loc8', 'Награда за победы над боссами подземелий и старыми рейдовыми боссами. За эти очки можно покупать мощное оружие и доспехи для PvE-сражений.', ''),
/* Emblem of Frost - reuse Valor Points (396) */
('currencies', 341, 'description_loc0', 'Earned for defeating the most recent raid bosses or completing heroic Dungeon Finder runs and used to purchase the most powerful PvE armor and weapons.', ''),
('currencies', 341, 'description_loc2', 'Obtenus pour avoir vaincu les boss des raids les plus récents ou en faisant des donjons héroïques en utilisant l\'outil Donjons, et utilisés pour acheter les plus puissantes armures et armes de JcE.', ''),
('currencies', 341, 'description_loc3', 'Werden als Belohnung für das Bezwingen der neuesten Schlachtzugsbosse oder für das Abschließen heroischer Dungeons mit dem Dungeonbrowser erworben und können für den Erwerb der mächtigsten PvE-Waffen und -Rüstungen verwendet werden.', ''),
('currencies', 341, 'description_loc4', '击败最新的团队副本首领或打穿随机英雄副本之后获得的奖励，可用来购买最强大的PVE护甲和武器。', ''),
('currencies', 341, 'description_loc6', 'Se ganan al derrotar a los jefes de banda más recientes o al completar heroicas con el buscador de mazmorras y se usan para comprar las armas y armaduras de JcE más poderosas.', ''),
('currencies', 341, 'description_loc8', 'Награда за победы над последними появившимися в игре рейдовыми боссами и за победы в героическом режиме при использовании системы поиска подземелий. За эти очки можно покупать самое мощное оружие и доспехи для PvE-сражений.', '');
