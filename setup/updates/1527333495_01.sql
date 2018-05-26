-- clear synced chars to prevent conflicts
DELETE FROM `aowow_profiler_profiles` WHERE `realmGUID` IS NOT NULL;
-- clear queue
DELETE FROM `aowow_profiler_sync`;
-- update unique index
ALTER TABLE `aowow_profiler_profiles`
    DROP INDEX `realm_realmGUID_name`,
    ADD UNIQUE INDEX `realm_realmGUID` (`realm`, `realmGUID`);
