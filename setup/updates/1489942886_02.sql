-- sound playlist
UPDATE `aowow_articles` SET `url` = 'sound&playlist' WHERE `type` =  19 AND `typeId` = -1000;
-- not found page
UPDATE `aowow_articles` SET `url` = 'page-not-found' WHERE `type` = -99 AND `typeId` = 0;
-- 'more' pages
UPDATE `aowow_articles` SET `url` = 'aboutus'       WHERE `type` =  -1 AND `typeId` = 0;
UPDATE `aowow_articles` SET `url` = 'whats-new'     WHERE `type` =  -7 AND `typeId` = 0;
UPDATE `aowow_articles` SET `url` = 'tooltips'      WHERE `type` = -10 AND `typeId` = 0;
UPDATE `aowow_articles` SET `url` = 'searchbox'     WHERE `type` = -16 AND `typeId` = 0;
UPDATE `aowow_articles` SET `url` = 'faq'           WHERE `type` =  -3 AND `typeId` = 0;
UPDATE `aowow_articles` SET `url` = 'searchplugins' WHERE `type` =  -8 AND `typeId` = 0;
-- help pages
UPDATE `aowow_articles` SET `url` = 'commenting-and-you'      WHERE `type` = -13 AND `typeId` = 0;
UPDATE `aowow_articles` SET `url` = 'modelviewer'             WHERE `type` = -13 AND `typeId` = 1;
UPDATE `aowow_articles` SET `url` = 'screenshots-tips-tricks' WHERE `type` = -13 AND `typeId` = 2;
UPDATE `aowow_articles` SET `url` = 'stat-weighting'          WHERE `type` = -13 AND `typeId` = 3;
UPDATE `aowow_articles` SET `url` = 'talent-calculator'       WHERE `type` = -13 AND `typeId` = 4;
UPDATE `aowow_articles` SET `url` = 'item-comparison'         WHERE `type` = -13 AND `typeId` = 5;
UPDATE `aowow_articles` SET `url` = 'profiler'                WHERE `type` = -13 AND `typeId` = 6;
UPDATE `aowow_articles` SET `url` = 'markup-guide'            WHERE `type` = -13 AND `typeId` = 7;

UPDATE  `aowow_articles` SET `type` = NULL, `typeId` = NULL WHERE `url` IS NOT NULL;
