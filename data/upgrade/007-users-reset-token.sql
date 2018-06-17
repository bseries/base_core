ALTER TABLE `users` ADD `reset_token` VARCHAR(250)  NULL  DEFAULT NULL  AFTER `password`;
ALTER TABLE `users` ADD `reset_answer` VARCHAR(250)  NULL  DEFAULT NULL  AFTER `reset_token`;
ALTER TABLE `users` MODIFY COLUMN `reset_token` VARCHAR(250) DEFAULT NULL AFTER `reset_answer`;
ALTER TABLE `users` ADD `auth_token` VARCHAR(250)  NULL  DEFAULT NULL  AFTER `reset_token`;
ALTER TABLE `users` MODIFY COLUMN `auth_token` VARCHAR(250) DEFAULT NULL AFTER `password`;


