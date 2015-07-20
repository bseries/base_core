-- Create syntax for TABLE 'users'
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) NOT NULL DEFAULT '',
  `number` varchar(100) NOT NULL DEFAULT '',
  `session_key` varchar(250) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(64) NOT NULL,
  `auth_token` varchar(250) DEFAULT NULL,
  `reset_answer` varchar(250) DEFAULT NULL,
  `reset_token` varchar(250) DEFAULT NULL,
  `role` varchar(30) NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_locked` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `locale` varchar(5) DEFAULT 'de',
  `timezone` varchar(100) NOT NULL DEFAULT 'UTC',
  `country` char(2) DEFAULT NULL,
  `is_notified` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `number` (`number`),
  KEY `session_key` (`session_key`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'virtual_users'
CREATE TABLE `virtual_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) NOT NULL DEFAULT '',
  `number` varchar(100) DEFAULT NULL,
  `session_key` varchar(250) NOT NULL DEFAULT '',
  `name` varchar(255) DEFAULT '',
  `email` varchar(100) DEFAULT '',
  `role` varchar(30) NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `locale` varchar(5) DEFAULT 'de',
  `timezone` varchar(100) NOT NULL DEFAULT 'UTC',
  `country` char(2) DEFAULT NULL,
  `is_notified` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `number` (`number`),
  KEY `session_key` (`session_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;