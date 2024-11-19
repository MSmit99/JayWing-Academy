-- MySQL Script generated by MySQL Workbench
-- Tue Nov 19 09:56:43 2024
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

-- Changes for 2.1 version:
  -- Added back Chat_Participant table
  -- Added 2 more attributes to User table
  -- Connected messages back to User table

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- Changes for 2.1 version:
  -- Added back Chat_Participant table
  -- Added 2 more attributes to User table
  -- Connected messages back to User table

-- -----------------------------------------------------
-- Schema jaywing
-- -----------------------------------------------------
DROP SCHEMA IF EXISTS `jaywing` ;

-- -----------------------------------------------------
-- Schema jaywing
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `jaywing` DEFAULT CHARACTER SET utf8 ;
USE `jaywing` ;

-- -----------------------------------------------------
-- Table `jaywing`.`User`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `jaywing`.`User` ;

CREATE TABLE IF NOT EXISTS `jaywing`.`User` (
  `user_id` INT NOT NULL AUTO_INCREMENT,
  `admin` TINYINT(1) NULL,
  `username` VARCHAR(45) NOT NULL,
  `firstName` VARCHAR(45) NOT NULL,
  `lastName` VARCHAR(45) NOT NULL,
  `email` VARCHAR(45) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `wings` INT NULL,
  `publicProfile` TINYINT(1) NULL,
  `Unavailable` TINYINT(1) NULL,
  PRIMARY KEY (`user_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `jaywing`.`Class`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `jaywing`.`Class` ;

CREATE TABLE IF NOT EXISTS `jaywing`.`Class` (
  `class_id` INT NOT NULL AUTO_INCREMENT,
  `className` VARCHAR(45) NOT NULL,
  `courseCode` VARCHAR(7) NULL,
  `classDescription` TEXT(500) NULL,
  PRIMARY KEY (`class_id`),
  UNIQUE INDEX `className_UNIQUE` (`className` ASC),
  UNIQUE INDEX `courseCode_UNIQUE` (`courseCode` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `jaywing`.`Enrollment`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `jaywing`.`Enrollment` ;

CREATE TABLE IF NOT EXISTS `jaywing`.`Enrollment` (
  `enrollment_id` INT NOT NULL AUTO_INCREMENT,
  `class_id` INT NULL,
  `user_id` INT NULL,
  `roleOfClass` VARCHAR(45) NOT NULL,
  `roleDescription` VARCHAR(500) NULL,
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
DROP TABLE IF EXISTS `jaywing`.`Event_Type` ;

CREATE TABLE IF NOT EXISTS `jaywing`.`Event_Type` (
  `event_type_id` INT NOT NULL AUTO_INCREMENT,
  `eventTypeName` VARCHAR(45) NOT NULL,
  `wings` INT NULL,
  PRIMARY KEY (`event_type_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `jaywing`.`Event`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `jaywing`.`Event` ;

CREATE TABLE IF NOT EXISTS `jaywing`.`Event` (
  `event_id` INT NOT NULL AUTO_INCREMENT,
  `type_id` INT NOT NULL,
  `eventName` VARCHAR(45) NOT NULL,
  `eventStartTime` DATETIME NOT NULL,
  `eventEndTime` DATETIME NOT NULL,
  `location` VARCHAR(45) NOT NULL,
  `eventDescription` VARCHAR(500) NULL,
  PRIMARY KEY (`event_id`, `type_id`),
  INDEX `event_type_idx` (`type_id` ASC),
  CONSTRAINT `event_type`
    FOREIGN KEY (`type_id`)
    REFERENCES `jaywing`.`Event_Type` (`event_type_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `jaywing`.`Attendance`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `jaywing`.`Attendance` ;

CREATE TABLE IF NOT EXISTS `jaywing`.`Attendance` (
  `attendance_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NULL,
  `event_id` INT NOT NULL,
  `roleOfEvent` VARCHAR(45) NOT NULL,
  `isCreator` TINYINT(1) NULL,
  PRIMARY KEY (`attendance_id`, `user_id`, `event_id`),
  INDEX `user_enrollment_idx` (`user_id` ASC),
  INDEX `event_attendance_idx` (`event_id` ASC),
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
-- Table `jaywing`.`Chat`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `jaywing`.`Chat` ;

CREATE TABLE IF NOT EXISTS `jaywing`.`Chat` (
  `chat_id` INT NOT NULL,
  `chatName` VARCHAR(45) NOT NULL,
  `chatDescription` VARCHAR(750) NULL,
  PRIMARY KEY (`chat_id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `jaywing`.`Messages`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `jaywing`.`Messages` ;

CREATE TABLE IF NOT EXISTS `jaywing`.`Messages` (
  `message_id` INT NOT NULL AUTO_INCREMENT,
  `chat_id` INT NOT NULL,
  `sender_id` INT NOT NULL,
  `messageContent` VARCHAR(2500) NOT NULL,
  PRIMARY KEY (`message_id`, `chat_id`, `sender_id`),
  INDEX `messages_chat_idx` (`chat_id` ASC),
  INDEX `sender_chat_idx` (`sender_id` ASC),
  CONSTRAINT `messages_chat`
    FOREIGN KEY (`chat_id`)
    REFERENCES `jaywing`.`Chat` (`chat_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `sender_chat`
    FOREIGN KEY (`sender_id`)
    REFERENCES `jaywing`.`User` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `jaywing`.`Availability`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `jaywing`.`Availability` ;

CREATE TABLE IF NOT EXISTS `jaywing`.`Availability` (
  `availability_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NULL,
  `weekday` ENUM('MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY') NOT NULL,
  `start` TIME NOT NULL,
  `end` TIME NOT NULL,
  PRIMARY KEY (`availability_id`, `user_id`),
  INDEX `weekday` (`weekday` ASC),
  CONSTRAINT `user_availability`
    FOREIGN KEY (`user_id`)
    REFERENCES `jaywing`.`User` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `jaywing`.`Person_Rating`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `jaywing`.`Person_Rating` ;

CREATE TABLE IF NOT EXISTS `jaywing`.`Person_Rating` (
  `rating_id` INT NOT NULL,
  `class_id` INT NOT NULL,
  `tutee_id` INT NOT NULL,
  `tutor_id` INT NOT NULL,
  `personRating` TINYINT(5) NOT NULL,
  `userFeedback` TEXT(500) NULL,
  PRIMARY KEY (`rating_id`, `tutor_id`, `tutee_id`, `class_id`),
  INDEX `rating_class` (`class_id` ASC),
  INDEX `rating_tutee` (`tutee_id` ASC),
  INDEX `rating_tutor` (`tutor_id` ASC),
  CONSTRAINT `rating_enrollment`
    FOREIGN KEY (`class_id`)
    REFERENCES `jaywing`.`Enrollment` (`class_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `jaywing`.`Event_Rating`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `jaywing`.`Event_Rating` ;

CREATE TABLE IF NOT EXISTS `jaywing`.`Event_Rating` (
  `rating_id` INT NOT NULL,
  `event_id` INT NOT NULL,
  `rating` TINYINT(5) NOT NULL,
  `eventFeedback` TEXT(500) NULL,
  PRIMARY KEY (`rating_id`, `event_id`),
  INDEX `event_id_idx` (`event_id` ASC),
  CONSTRAINT `event_id`
    FOREIGN KEY (`event_id`)
    REFERENCES `jaywing`.`Event` (`event_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `jaywing`.`Chat_Participant`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `jaywing`.`Chat_Participant` ;

CREATE TABLE IF NOT EXISTS `jaywing`.`Chat_Participant` (
  `participant_id` INT NOT NULL,
  `chat_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `joinedAt` DATETIME NOT NULL,
  PRIMARY KEY (`participant_id`, `chat_id`, `user_id`),
  INDEX `user_id_idx` (`user_id` ASC),
  INDEX `chat_id_idx` (`chat_id` ASC),
  CONSTRAINT `user_id`
    FOREIGN KEY (`user_id`)
    REFERENCES `jaywing`.`User` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `chat_id`
    FOREIGN KEY (`chat_id`)
    REFERENCES `jaywing`.`Chat` (`chat_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

INSERT INTO `jaywing`.`Event_Type`(eventTypeName, wings) VALUES
('DROP IN', 100),
('TUTORING', 200),
('GROUP', 300);
