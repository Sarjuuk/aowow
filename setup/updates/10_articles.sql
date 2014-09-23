-- type 0 causes trouble with g_pageInfo
UPDATE aowow_articles SET `type` = -1 WHERE `type` = 0;
