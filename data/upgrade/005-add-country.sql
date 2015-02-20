
ALTER TABLE `users` ADD `country` CHAR(2)  NULL  DEFAULT NULL  AFTER `timezone`;
ALTER TABLE `virtual_users` ADD `country` CHAR(2)  NULL  DEFAULT NULL  AFTER `timezone`;
