CREATE TABLE `transaction_log`
(
    `id`                int(10) UNSIGNED  NOT NULL AUTO_INCREMENT,
    `type`              smallint UNSIGNED NOT NULL,
    `user_id`           int(10) UNSIGNED  NOT NULL,
    `initiator_user_id` int(10) UNSIGNED  NOT NULL,
    `amount`            decimal(10, 2)    NOT NULL,
    `likes_amount`      int(11)           NOT NULL DEFAULT 0,
    `time_created`      timestamp         NULL     DEFAULT CURRENT_TIMESTAMP,
    `data`              text              NULL     DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `transaction_log` ADD INDEX (`user_id`);

