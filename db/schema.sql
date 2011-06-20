


CREATE TABLE `_version` (
  `look_comment` tinyint(4) NOT NULL
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='0';



CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ip` varchar(15) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`comment_id`)
) DEFAULT CHARSET=utf8;



CREATE TABLE `countries` (
  `country_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`country_id`),
  UNIQUE KEY `name` (`name`)
) DEFAULT CHARSET=utf8;



CREATE TABLE `genres` (
  `genre_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`genre_id`),
  UNIQUE KEY `name` (`name`)
) DEFAULT CHARSET=utf8;



CREATE TABLE `movies` (
  `movie_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `international_name` varchar(128) DEFAULT NULL,
  `year` varchar(20) DEFAULT NULL,
  `info` text,
  `extended_info` text,
  `cover` varchar(255) DEFAULT NULL,
  `trailer` blob,
  `trailer_localized` tinyint(4) DEFAULT NULL,
  `access` enum('public','private') NOT NULL DEFAULT 'public',
  `added_at` datetime NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip` varchar(39) DEFAULT NULL,
  PRIMARY KEY (`movie_id`)
) DEFAULT CHARSET=utf8;



CREATE TABLE `movies_comments` (
  `movie_id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL,
  PRIMARY KEY (`movie_id`,`comment_id`)
) DEFAULT CHARSET=utf8;



CREATE TABLE `movies_countries` (
  `movie_id` int(11) NOT NULL,
  `country_id` int(11) NOT NULL,
  PRIMARY KEY (`movie_id`,`country_id`)
) DEFAULT CHARSET=utf8;



CREATE TABLE `movies_genres` (
  `movie_id` int(11) NOT NULL,
  `genre_id` int(11) NOT NULL,
  PRIMARY KEY (`movie_id`,`genre_id`)
) DEFAULT CHARSET=utf8;



CREATE TABLE `participants` (
  `participant_id` int(11) NOT NULL AUTO_INCREMENT,
  `movie_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  PRIMARY KEY (`participant_id`)
) DEFAULT CHARSET=utf8;



CREATE TABLE `persones` (
  `person_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `international_name` varchar(128) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `born_date` date DEFAULT NULL,
  `born_place` varchar(255) DEFAULT NULL,
  `profile` varchar(255) DEFAULT NULL,
  `info` text,
  `url` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`person_id`),
  UNIQUE KEY `url` (`url`)
) DEFAULT CHARSET=utf8;



CREATE TABLE `ratings` (
  `rating_id` int(11) NOT NULL AUTO_INCREMENT,
  `system` enum('imdb','kinopoisk') NOT NULL,
  `system_uid` int(11) DEFAULT NULL,
  `movie_id` int(11) NOT NULL,
  `count` int(11) DEFAULT NULL,
  `value` float DEFAULT NULL,
  PRIMARY KEY (`rating_id`)
) DEFAULT CHARSET=utf8;



CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `name_hyphenated` varchar(128) DEFAULT NULL,
  `sort` int(11) NOT NULL DEFAULT '99',
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `name` (`name`)
) DEFAULT CHARSET=utf8;



CREATE TABLE `users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(128) NOT NULL,
  `auth_provider_key` varchar(64) NOT NULL,
  `group` enum('user','moder','admin') DEFAULT 'user',
  `ip` varchar(15) DEFAULT NULL,
  `register_at` datetime DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `info` blob,
  `preferences` blob,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_name_2` (`user_name`,`auth_provider_key`),
  KEY `user_name` (`user_name`)
) DEFAULT CHARSET=utf8;



CREATE TABLE `users_keys` (
  `user_name` varchar(64) NOT NULL,
  `password_hash` varchar(32) NOT NULL,
  `register_at` datetime DEFAULT NULL,
  `token` varchar(32) DEFAULT NULL,
  `token_expired_at` datetime DEFAULT NULL,
  PRIMARY KEY (`user_name`)
) DEFAULT CHARSET=utf8;
