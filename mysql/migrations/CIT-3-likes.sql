CREATE TABLE `post_like` (
     `id` int(10) UNSIGNED NOT NULL,
     `user_id` int(10) UNSIGNED NOT NULL,
     `post_id` int(10) UNSIGNED NOT NULL,
     `amount` int(10) UNSIGNED NOT NULL,
     `time_created` timestamp NOT NULL DEFAULT current_timestamp(),
     PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `post_like`
    ADD KEY `user_id` (`user_id`),
    ADD KEY `post_id` (`post_id`);

ALTER TABLE `post` ADD `likes_count` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `img`;



CREATE TABLE `comment_like`(
    `id` int(10) UNSIGNED NOT NULL,
    `user_id` int(10) UNSIGNED NOT NULL,
    `comment_id` int(10) UNSIGNED NOT NULL,
    `amount` int(10) UNSIGNED NOT NULL,
    `time_created` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `comment_like`
    ADD KEY `user_id` (`user_id`),
    ADD KEY `comment_id` (`comment_id`);

ALTER TABLE `comment` ADD `likes_count` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `text`;

