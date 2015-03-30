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
  `vars` text NOT NULL,
  `fetched_at` double NOT NULL,
  PRIMARY KEY (`topic_id`,`topic_mode`,`forum_id`,`page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tokens` (
  `token` varchar(32) NOT NULL,
  `generated` timestamp NOT NULL,
  `used` boolean NOT NULL,
  PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
