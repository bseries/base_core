ALTER TABLE `users` ADD `uuid` VARCHAR(36)  NOT NULL  DEFAULT ''  AFTER `id`;
ALTER TABLE `virtual_users` ADD `uuid` VARCHAR(36)  NOT NULL  DEFAULT ''  AFTER `id`;
