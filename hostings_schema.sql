-- Hostings Table Schema for TIC CRM
CREATE TABLE IF NOT EXISTS `tblhostings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `hosting_name` VARCHAR(255) NOT NULL,
  `status` VARCHAR(50) DEFAULT 'Active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default hostings
INSERT INTO `tblhostings` (`hosting_name`, `status`) VALUES
('API Server', 'Active'),
('Production Web Server', 'Active'),
('Development Server', 'Active'),
('Testing Server', 'Active'),
('Backup Server', 'Active');