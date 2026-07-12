DELETE FROM aowow_announcements WHERE `page` = 'profile&new';
INSERT INTO aowow_announcements (`page`, `name`, `groupMask`, `style`, `mode`, `status`, `text_loc0`, `text_loc2`, `text_loc3`, `text_loc4`, `text_loc6`, `text_loc8`)
    SELECT 'profile&new', `name`, `groupMask`, `style`, `mode`, `status`, `text_loc0`, `text_loc2`, `text_loc3`, `text_loc4`, `text_loc6`, `text_loc8` FROM aowow_announcements WHERE `page` = 'profile';
