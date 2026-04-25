-- Providers Table Schema for TIC CRM
-- Run this SQL in your database

CREATE TABLE IF NOT EXISTS `tblproviders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `provider_name` VARCHAR(255) NOT NULL,
  `provider_url` VARCHAR(500) NOT NULL,
  `provider_type` VARCHAR(100) NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'Active',
  `description` TEXT,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;