-- MySQL Workbench Synchronization
-- Generated: 2017-09-12 22:18
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Daniel

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';


CREATE TABLE IF NOT EXISTS `videos_reported` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `obs` VARCHAR(255) NULL DEFAULT NULL,
  `videos_id` INT(11) NULL,
  `users_id` INT(11) NOT NULL,
  `created` DATETIME NULL DEFAULT NULL,
  `modified` DATETIME NULL DEFAULT NULL,
  `status` CHAR(1) NOT NULL DEFAULT 'a',
  `reported_users_id` INT(11) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_videos_reported_videos_idx` (`videos_id` ASC),
  INDEX `fk_videos_reported_users1_idx` (`users_id` ASC),
  INDEX `fk_videos_reported_users2_idx` (`reported_users_id` ASC),
  CONSTRAINT `fk_videos_reported_users1`
    FOREIGN KEY (`users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_videos_reported_videos`
    FOREIGN KEY (`videos_id`)
    REFERENCES `videos` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_videos_reported_users2`
    FOREIGN KEY (`reported_users_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
