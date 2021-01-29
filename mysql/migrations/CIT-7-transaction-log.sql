
ALTER TABLE `transaction_log` ADD `entity_id` INT UNSIGNED NULL DEFAULT NULL AFTER `likes_amount`;

ALTER TABLE `transaction_log` ADD INDEX (`entity_id`);
ALTER TABLE `transaction_log` ADD INDEX (`initiator_user_id`);
ALTER TABLE `transaction_log` ADD INDEX (`time_created`);
