/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

DROP TABLE IF EXISTS `available_apps`;
DROP TABLE IF EXISTS `apps`;

CREATE TABLE IF NOT EXISTS `userloginkeys` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(255) NOT NULL,
  `expires` DATETIME NULL DEFAULT NULL,
  `uid` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`, `key`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  UNIQUE INDEX `key_UNIQUE` (`key` ASC),
  INDEX `fk_userloginkeys_accounts1_idx` (`uid` ASC),
  CONSTRAINT `fk_userloginkeys_accounts1`
    FOREIGN KEY (`uid`)
    REFERENCES `accounts` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

ALTER TABLE `userloginkeys`
ADD COLUMN `appname` VARCHAR(255) NOT NULL AFTER `uid`;