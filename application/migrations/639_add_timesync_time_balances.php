<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_timesync_time_balances extends CI_Migration {

    public function up()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS tbl_timesync_time_balances (
            id                  INT AUTO_INCREMENT PRIMARY KEY,
            user_id             INT NOT NULL,
            year_month          VARCHAR(7) NOT NULL,
            status              ENUM('open','frozen') NOT NULL DEFAULT 'open',
            gross_expected_sec  INT NOT NULL DEFAULT 0,
            actual_sec          INT NOT NULL DEFAULT 0,
            consumed_sec        INT NOT NULL DEFAULT 0,
            adjusted_expected_sec INT NOT NULL DEFAULT 0,
            surplus_sec         INT NOT NULL DEFAULT 0,
            shortage_sec        INT NOT NULL DEFAULT 0,
            carryover_in_sec    INT NOT NULL DEFAULT 0,
            carryover_out_sec   INT NOT NULL DEFAULT 0,
            updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uid_ym (user_id, year_month)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");
    }

    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS tbl_timesync_time_balances");
    }
}
