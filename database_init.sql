START TRANSACTION;

--
-- Create database
--

CREATE DATABASE IF NOT EXISTS `sc1007_db`;

USE `sc1007_db`;

--
-- Create tables for the database
--

CREATE TABLE IF NOT EXISTS `account_info` (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Hashed Password',
  `isLogged` tinyint(1) NOT NULL DEFAULT '0',
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

CREATE TABLE IF NOT EXISTS `player_data` (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) UNSIGNED NOT NULL,
  `puzzle_name` varchar(30) NOT NULL COMMENT 'Name of the puzzle',
  `time` int(11) NULL COMMENT 'Time taken to complete the puzzle',
  `attempts` int(11) UNSIGNED NULL COMMENT 'Number of attempts made to complete the puzzle',
  `solved` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `unique_record` (`user_id`, `puzzle_name`),
  FOREIGN KEY (`user_id`) REFERENCES `account_info`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='Stores a record of player''s puzzle data';

--
-- Create users for the database
--

CREATE USER IF NOT EXISTS 'account_info_user'@'localhost' IDENTIFIED BY 'PLACEHOLDER_PASSWORD';
CREATE USER IF NOT EXISTS 'refresh_tokens_user'@'localhost' IDENTIFIED BY 'PLACEHOLDER_PASSWORD';
CREATE USER IF NOT EXISTS 'player_data_user'@'localhost' IDENTIFIED BY 'PLACEHOLDER_PASSWORD';

--
-- Grant permissions to the users
--

GRANT SELECT, INSERT, UPDATE, DELETE ON `sc1007_db`.`account_info` TO 'account_info_user'@'localhost';
GRANT SELECT, INSERT, DELETE ON `sc1007_db`.`refresh_tokens` TO 'refresh_tokens_user'@'localhost';
GRANT SELECT, INSERT, UPDATE ON `sc1007_db`.`player_data` TO 'player_data_user'@'localhost';

FLUSH PRIVILEGES;

COMMIT;