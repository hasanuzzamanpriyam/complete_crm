<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_616 extends CI_Migration
{
    public function up()
    {
        // Extend expense schedule repeat rates.
        $this->db->query("
            ALTER TABLE `expenses`
            MODIFY `payment_type` ENUM('daily', 'monthly', 'bi-monthly', 'quarterly', 'yearly') NOT NULL
        ");

        // Add payment_type to tasks (so tasks can store the same schedule concept).
        $this->db->query("
            ALTER TABLE `tbl_task`
            ADD `payment_type` ENUM('daily', 'monthly', 'bi-monthly', 'quarterly', 'yearly') NOT NULL DEFAULT 'monthly'
        ");
    }

    public function down()
    {
        // Revert payment_type enum on expenses.
        $this->db->query("
            ALTER TABLE `expenses`
            MODIFY `payment_type` ENUM('daily', 'monthly', 'yearly') NOT NULL
        ");

        // Remove payment_type column from tasks.
        $this->db->query("
            ALTER TABLE `tbl_task`
            DROP COLUMN `payment_type`
        ");
    }
}

