CREATE TABLE `forums` (
  `forum_id` int(11) unsigned NOT NULL,
  `slug` varchar(64) NOT NULL,
  `human` varchar(64) NOT NULL,
  PRIMARY KEY (`forum_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `forums_cache` (
  `forum_id` int(11) unsigned NOT NULL,
  `page` smallint(6) unsigned NOT NULL,
  `vars` text NOT NULL,
  `fetched_at` double NOT NULL,
  PRIMARY KEY (`forum_id`,`page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `topics_cache` (
  `topic_id` int(11) unsigned NOT NULL,
  `topic_mode` tinyint(4) unsigned NOT NULL,
  `forum_id` int(11) unsigned NOT NULL,
  `page` smallint(6) unsigned NOT NULL,
  `vars` mediumtext NOT NULL,
  `fetched_at` double NOT NULL,
  PRIMARY KEY (`topic_id`,`topic_mode`,`forum_id`,`page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tokens` (
  `token` varchar(32) NOT NULL,
  `generated` timestamp NOT NULL,
  PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `logs_messages2` (
  `id` int unsigned NOT NULL auto_increment,
  `pseudo` varchar(15),
  `message_id` int unsigned,
  `posted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_topic` tinyint unsigned NOT NULL,
  `forum_id` int unsigned NOT NULL,
  `topic_mode` tinyint unsigned,
  `topic_id` int unsigned,
  `ip` varchar(15) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ip_blacklist` (
  `ip` varchar(15) NOT NULL,
  PRIMARY KEY (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `icstats2` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ts` int DEFAULT NULL,
  `clicks_minus_touchstart` varchar(5000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `logs_requests3` (
  `id` int unsigned NOT NULL auto_increment,
  `logged_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `url` varchar(255) NOT NULL,
  `is_post` tinyint NOT NULL,
  `is_connected` tinyint NOT NULL,
  `timing` smallint NOT NULL,
  `errno` tinyint NOT NULL,
  `ip` varchar(15) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `blacklists` (
  `id` int unsigned NOT NULL auto_increment,
  `person` varchar(32) NOT NULL,
  `blacklist` varchar(5000) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
