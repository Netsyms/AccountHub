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
DEFAULT CHARACTER SET = utf8
