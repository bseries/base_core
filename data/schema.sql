# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: localhost (MySQL 10.0.10-MariaDB-log)
# Datenbank: rainmap
# Erstellungsdauer: 2014-06-03 15:26:45 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

# Export von Tabelle users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `number` varchar(100) NOT NULL DEFAULT '',
  `session_key` varchar(250) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(64) NOT NULL,
  `role` varchar(30) NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `locale` varchar(5) DEFAULT 'de',
  `timezone` varchar(100) NOT NULL DEFAULT 'UTC',
  `is_notified` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `number` (`number`),
  KEY `session_key` (`session_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `users` ADD `country` CHAR(2)  NULL  DEFAULT NULL  AFTER `timezone`;

# Export von Tabelle virtual_users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `virtual_users`;

CREATE TABLE `virtual_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `number` varchar(100) DEFAULT NULL,
  `session_key` varchar(250) NOT NULL DEFAULT '',
  `name` varchar(255) DEFAULT '',
  `email` varchar(100) DEFAULT '',
  `role` varchar(30) NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `locale` varchar(5) DEFAULT 'de',
  `timezone` varchar(100) NOT NULL DEFAULT 'UTC',
  `is_notified` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `number` (`number`),
  KEY `session_key` (`session_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `virtual_users` ADD `country` CHAR(2)  NULL  DEFAULT NULL   AFTER `timezone`;


/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
