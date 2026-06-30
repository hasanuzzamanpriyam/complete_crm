<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_last_active_ping extends CI_Migration {

    public function up()
    {
        $this->db->query('ALTER TABLE tbl_users ADD COLUMN last_active_ping DATETIME NULL');
        $this->db->query('CREATE INDEX idx_last_active_ping ON tbl_users(last_active_ping)');
    }

    public function down()
    {
        $this->db->query('DROP INDEX idx_last_active_ping ON tbl_users');
        $this->db->query('ALTER TABLE tbl_users DROP COLUMN last_active_ping');
    }
}
