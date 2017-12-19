ALTER TABLE `ebh_systemsettings` ADD COLUMN `subtitle` VARCHAR(255) DEFAULT '' NOT NULL COMMENT '网校副标题';
ALTER TABLE `ebh_systemsettings` ADD COLUMN `refuses_tranger` TINYINT UNSIGNED DEFAULT 0 NOT NULL COMMENT '非网校内部用户禁止进入学习平台';
ALTER TABLE `ebh_systemsettings` ADD COLUMN `mobile_register` TINYINT UNSIGNED DEFAULT 0 NOT NULL COMMENT '新用户注册必须认证手机号';
ALTER TABLE `ebh_systemsettings` ADD COLUMN `interval` INT UNSIGNED DEFAULT 1 NOT NULL COMMENT '同一IP发帖间隔时间(秒)';