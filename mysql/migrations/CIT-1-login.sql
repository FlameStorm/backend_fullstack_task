ALTER TABLE `user` CHANGE `password`
    `password` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

UPDATE `user` SET `password` = 'ilonmaskkurilgoroshekyozhbezhalnechuyanozhek' WHERE `id` = 1;
UPDATE `user` SET `password` = 'justasimplepassword' WHERE `id` = 2;

