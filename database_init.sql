-- This script is used to initialize the database for the project

START TRANSACTION;

-- Create a new database and tables for the project

CREATE DATABASE IF NOT EXISTS `sc1007_db`;

USE `sc1007_db`;

--
-- Create tables for the database
--

CREATE TABLE IF NOT EXISTS `account_info` (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Hashed Password',
  `isAdmin` boolean NOT NULL DEFAULT 0,
  `isLocked` boolean NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS `refresh_tokens` (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `token` char(64) NOT NULL COMMENT 'SHA-256 Hashed Refresh Token',
  `user_id` mediumint(8) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `account_info`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Create users for the database. Note: The password should be replaced with a secure password
--

CREATE USER 'account_info_user'@'localhost' IDENTIFIED BY 'PLACEHOLDER_PASSWORD';
CREATE USER 'refresh_tokens_user'@'localhost' IDENTIFIED BY 'PLACEHOLDER_PASSWORD';

--
-- Grant permissions to the users
--

GRANT SELECT, INSERT, UPDATE, DELETE ON `sc1007_db`.`account_info` TO 'account_info_user'@'localhost';
GRANT SELECT, INSERT, DELETE ON `sc1007_db`.`refresh_tokens` TO 'refresh_tokens_user'@'localhost';

FLUSH PRIVILEGES;

--
-- Insert default admin user. Note: The password should be replaced with a secure password
-- The password is hashed using the password_hash function in PHP
--

INSERT INTO `account_info` (`username`, `password`, `isAdmin`) VALUES ('admin', 'PLACEHOLDER_HASHED_PASSWORD', 1);

COMMIT;