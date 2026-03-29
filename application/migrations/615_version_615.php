<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_615 extends CI_Migration
{
    public function up()
    {
        // 1. Create expenses table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `expenses` (
                `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `task_name` VARCHAR(255) NOT NULL,
                `description` TEXT NULL DEFAULT NULL,
                `payment_type` ENUM('daily', 'monthly', 'yearly') NOT NULL,
                `last_payment_date` DATE NOT NULL,
                `amount` DECIMAL(10, 2) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_payment_type` (`payment_type`),
                INDEX `idx_last_payment_date` (`last_payment_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 2. Create expense_occurrences table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `expense_occurrences` (
                `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `expense_id` BIGINT UNSIGNED NOT NULL,
                `occurrence_date` DATE NOT NULL,
                `status` ENUM('pending', 'paid') NOT NULL DEFAULT 'pending',
                UNIQUE KEY `unique_expense_occurrence` (`expense_id`, `occurrence_date`),
                CONSTRAINT `fk_expense_occurrences_expense_id` 
                    FOREIGN KEY (`expense_id`) 
                    REFERENCES `expenses`(`id`) 
                    ON DELETE CASCADE 
                    ON UPDATE CASCADE,
                INDEX `idx_status` (`status`),
                INDEX `idx_occurrence_date` (`occurrence_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS `expense_occurrences`;");
        $this->db->query("DROP TABLE IF EXISTS `expenses`;");
    }
}
