ALTER TABLE `users` ADD `is_locked` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '0'  AFTER `is_active`;
