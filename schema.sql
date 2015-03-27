CREATE TABLE `forums` (
  `forum_id` int(11) unsigned NOT NULL,
  `page` smallint(6) unsigned NOT NULL,
  `vars` text NOT NULL,
  `fetched_at` timestamp NOT NULL,
  PRIMARY KEY (`forum_id`,`page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `topics` (
  `topic_id` int(11) unsigned NOT NULL,
  `topic_mode` tinyint(4) unsigned NOT NULL,
  `forum_id` int(11) unsigned NOT NULL,
  `page` smallint(6) unsigned NOT NULL,
  `vars` text NOT NULL,
  `fetched_at` timestamp NOT NULL,
  PRIMARY KEY (`topic_id`,`topic_mode`,`forum_id`,`page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
