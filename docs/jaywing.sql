-- MySQL Script generated by MySQL Workbench
-- Mon Oct 21 19:09:50 2024
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema jaywing
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema jaywing
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `jaywing` DEFAULT CHARACTER SET utf8 ;
USE `jaywing` ;

-- -----------------------------------------------------
-- Table `jaywing`.`User`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `jaywing`.`User` (
  `user_id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(45) NOT NULL,
  `email` VARCHAR(45) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `wings` INT NULL,
  `admin` TINYINT(1) NULL,
  PRIMARY KEY (`user_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `jaywing`.`Class`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `jaywing`.`Class` (
  `class_id` INT NOT NULL AUTO_INCREMENT,
  `class_name` VARCHAR(45) NOT NULL,
  `description` VARCHAR(255) NULL,
  PRIMARY KEY (`class_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `jaywing`.`Enrollment`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `jaywing`.`Enrollment` (
  `enrollment_id` INT NOT NULL AUTO_INCREMENT,
  `class_id` INT NOT NULL,
  `role_in_class` VARCHAR(45) NOT NULL,
  `user_id` INT NOT NULL,
  PRIMARY KEY (`enrollment_id`, `class_id`, `user_id`),
  INDEX `class_enrollment_idx` (`class_id` ASC),
  INDEX `user_enrollment_idx` (`user_id` ASC),
  CONSTRAINT `user_enrollment`
    FOREIGN KEY (`user_id`)
    REFERENCES `jaywing`.`User` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `class_enrollment`
    FOREIGN KEY (`class_id`)
    REFERENCES `jaywing`.`Class` (`class_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `jaywing`.`Event_Type`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `jaywing`.`Event_Type` (
  `event_type_id` INT NOT NULL AUTO_INCREMENT,
  `type_name` VARCHAR(45) NOT NULL,
  `wings` INT NOT NULL,
  PRIMARY KEY (`event_type_id`),
  CONSTRAINT check_wings CHECK (wings IN (100, 200, 300)))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `jaywing`.`Event`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `jaywing`.`Event` (
  `event_id` INT NOT NULL AUTO_INCREMENT,
  `event_name` VARCHAR(45) NOT NULL,
  `start` DATETIME NOT NULL,
  `end` DATETIME NOT NULL,
  `event_type_id` INT NOT NULL,
  `location` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`event_id`, `event_type_id`),
  INDEX `event_event_type_idx` (`event_type_id` ASC),
  CONSTRAINT `event_event_type`
    FOREIGN KEY (`event_type_id`)
    REFERENCES `jaywing`.`Event_Type` (`event_type_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `jaywing`.`Attendance`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `jaywing`.`Attendance` (
  `attendance_id` INT NOT NULL AUTO_INCREMENT,
  `role_in_event` VARCHAR(45) NOT NULL,
  `user_id` INT NOT NULL,
  `event_id` INT NOT NULL,
  PRIMARY KEY (`attendance_id`, `user_id`, `event_id`),
  INDEX `user_enrollment_idx` (`user_id` ASC) ,
  INDEX `event_attendance_idx` (`event_id` ASC) ,
  CONSTRAINT `user_attendance`
    FOREIGN KEY (`user_id`)
    REFERENCES `jaywing`.`User` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `event_attendance`
    FOREIGN KEY (`event_id`)
    REFERENCES `jaywing`.`Event` (`event_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `jaywing`.`Jobs`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `jaywing`.`Jobs` (
  `job_id` INT NOT NULL,
  `class_id` INT NOT NULL,
  `admin_id` INT NOT NULL,
  `description` TEXT(2500) NULL,
  PRIMARY KEY (`job_id`, `class_id`, `admin_id`),
  INDEX `jobs_class_idx` (`class_id` ASC) ,
  INDEX `jobs_user_idx` (`admin_id` ASC) ,
  CONSTRAINT `jobs_class`
    FOREIGN KEY (`class_id`)
    REFERENCES `jaywing`.`Class` (`class_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `jobs_user`
    FOREIGN KEY (`admin_id`)
    REFERENCES `jaywing`.`User` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `jaywing`.`Chat`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `jaywing`.`Chat` (
  `chat_id` INT NOT NULL,
  `chat_name` VARCHAR(45) NULL,
  PRIMARY KEY (`chat_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `jaywing`.`Chat_Roster`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `jaywing`.`Chat_Roster` (
  `chat_roster_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `chat_id` INT NOT NULL,
  PRIMARY KEY (`chat_roster_id`, `user_id`, `chat_id`),
  INDEX `chat_roster_user_idx` (`user_id` ASC) ,
  INDEX `chat_roster_chat_idx` (`chat_id` ASC) ,
  CONSTRAINT `chat_roster_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `jaywing`.`User` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `chat_roster_chat`
    FOREIGN KEY (`chat_id`)
    REFERENCES `jaywing`.`Chat` (`chat_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `jaywing`.`Messages`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `jaywing`.`Messages` (
  `message_id` INT NOT NULL AUTO_INCREMENT,
  `chat_id` INT NOT NULL,
  `content` TEXT(2500) NULL,
  `sender_id` INT NOT NULL,
  PRIMARY KEY (`message_id`, `chat_id`, `sender_id`),
  INDEX `messages_chat_idx` (`chat_id` ASC) ,
  INDEX `messages_user_idx` (`sender_id` ASC) ,
  CONSTRAINT `messages_chat`
    FOREIGN KEY (`chat_id`)
    REFERENCES `jaywing`.`Chat` (`chat_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `messages_user`
    FOREIGN KEY (`sender_id`)
    REFERENCES `jaywing`.`User` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `jaywing`.`Availability`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `jaywing`.`Availability` (
  `availibility_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `weekday` ENUM('MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY','SUNDAY') NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  PRIMARY KEY (`availibility_id`, `user_id`),
  INDEX `user_availability_idx` (`user_id` ASC) ,
  CONSTRAINT `user_availability`
    FOREIGN KEY (`user_id`)
    REFERENCES `jaywing`.`User` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `jaywing`.`Rating`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `jaywing`.`Rating` (
  `rating_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `class_id` INT NOT NULL,
  `rating` TINYINT(5) NOT NULL,
  `tutor_id` INT NOT NULL,
  PRIMARY KEY (`rating_id`, `class_id`),
  INDEX `rating_enrollment_idx` (`class_id` ASC) ,
  CONSTRAINT `rating_enrollment`
    FOREIGN KEY (`class_id`)
    REFERENCES `jaywing`.`Enrollment` (`class_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- Pre-populate with allowed types:
INSERT INTO `jaywing`.`Event_Type` (type_name, wings) VALUES
('DROP_IN', 100),
('TUTORING', 200),
('GROUP', 300);
