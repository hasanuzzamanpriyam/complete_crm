<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_last_active_ping extends CI_Migration {

    public function up()
    {
        $user_fields = $this->db->field_data('tbl_users');
        $user_field_names = array_map(function ($f) { return $f->name; }, $user_fields);
        if (!in_array('last_active_ping', $user_field_names)) {
            $this->db->query('ALTER TABLE tbl_users ADD COLUMN last_active_ping DATETIME NULL');
        }
        $index_check = $this->db->query("SHOW INDEX FROM tbl_users WHERE Key_name = 'idx_last_active_ping'");
        if ($index_check->num_rows() === 0) {
            $this->db->query('CREATE INDEX idx_last_active_ping ON tbl_users(last_active_ping)');
        }
    }

    public function down()
    {
        $this->db->query('DROP INDEX idx_last_active_ping ON tbl_users');
        $this->db->query('ALTER TABLE tbl_users DROP COLUMN last_active_ping');
    }
}
