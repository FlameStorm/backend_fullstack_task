ALTER TABLE `post` CHANGE `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `comment` CHANGE `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `comment` CHANGE `assign_id` `post_id` INT(10) UNSIGNED NOT NULL;
ALTER TABLE `comment` ADD INDEX (`post_id`);
ALTER TABLE `comment`
    ADD `level` INT NOT NULL DEFAULT '0' AFTER `post_id`,
    ADD parent_id INT UNSIGNED NULL DEFAULT NULL AFTER `level`;

ALTER TABLE `comment`
    ADD `_deleted` TINYINT NOT NULL DEFAULT '0' AFTER `time_updated`;

## Admin user
UPDATE `user` SET `rights` = '-1' WHERE `id` = 1;

