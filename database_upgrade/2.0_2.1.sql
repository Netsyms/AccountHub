/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

CREATE TABLE IF NOT EXISTS `userkeys` (
  `uid` INT(11) NOT NULL,
  `key` VARCHAR(100) NOT NULL,
  `created` DATETIME NULL DEFAULT NULL,
  `typeid` INT(11) NOT NULL,
  PRIMARY KEY (`uid`),
  INDEX `fk_userkeys_userkeytypes1_idx` (`typeid` ASC),
  CONSTRAINT `fk_userkeys_accounts1`
    FOREIGN KEY (`uid`)
    REFERENCES `accounthub`.`accounts` (`uid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_userkeys_userkeytypes1`
    FOREIGN KEY (`typeid`)
    REFERENCES `accounthub`.`userkeytypes` (`typeid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `userkeytypes` (
  `typeid` INT(11) NOT NULL,
  `typename` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`typeid`, `typename`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

INSERT INTO `userkeytypes` (`typeid`, `typename`) VALUES (1, 'RSSAtomFeed');
INSERT INTO `userkeytypes` (`typeid`, `typename`) VALUES (2, 'Other');