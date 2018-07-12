CREATE TABLE IF NOT EXISTS `onetimekeys` (
  `key` VARCHAR(10) NOT NULL,
  `uid` INT(11) NOT NULL,
  `expires` DATETIME NOT NULL,
  INDEX `fk_onetimekeys_accounts1_idx` (`uid` ASC),
  PRIMARY KEY (`key`),
  UNIQUE INDEX `key_UNIQUE` (`key` ASC),
  CONSTRAINT `fk_onetimekeys_accounts1`
    FOREIGN KEY (`uid`)
    REFERENCES `accounthub`.`accounts` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

SET FOREIGN_KEY_CHECKS = 0;
ALTER TABLE `groups`
CHANGE COLUMN `groupid` `groupid` INT(11) NOT NULL AUTO_INCREMENT;
SET FOREIGN_KEY_CHECKS = 1;

ALTER TABLE `accounts`
ADD COLUMN `pin` VARCHAR(10) NULL DEFAULT NULL AFTER `authsecret`;

ALTER TABLE `mobile_codes`
CHANGE COLUMN `code` `code` VARCHAR(45) NOT NULL DEFAULT '',
ADD COLUMN `description` VARCHAR(255) NOT NULL DEFAULT '' AFTER `code`;

INSERT INTO `permissions` (`permid`, `permcode`, `perminfo`) VALUES (400, 'SITEWRITER', 'Manage and edit websites, messages, and analytics');
INSERT INTO `permissions` (`permid`, `permcode`, `perminfo`) VALUES (401, 'SITEWRITER_CONTACT', 'Manage messages sent via website contact forms');
INSERT INTO `permissions` (`permid`, `permcode`, `perminfo`) VALUES (402, 'SITEWRITER_ANALYTICS', 'View website analytics');
INSERT INTO `permissions` (`permid`, `permcode`, `perminfo`) VALUES (403, 'SITEWRITER_EDIT', 'Edit website content');
INSERT INTO `permissions` (`permid`, `permcode`, `perminfo`) VALUES (404, 'SITEWRITER_FILES', 'Manage and upload files');