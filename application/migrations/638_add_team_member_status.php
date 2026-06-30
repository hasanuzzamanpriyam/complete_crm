<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_team_member_status extends CI_Migration {

    public function up()
    {
        $member_fields = $this->db->field_data('tbl_team_members');
        $member_field_names = array_map(function ($f) { return $f->name; }, $member_fields);
        if (!in_array('status', $member_field_names)) {
            $this->db->query("ALTER TABLE tbl_team_members
                ADD COLUMN status ENUM('pending','approved','left') NOT NULL DEFAULT 'approved'
                AFTER is_manager");
        }
        if (!in_array('created_at', $member_field_names)) {
            $this->db->query("ALTER TABLE tbl_team_members
                ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
        }
    }

    public function down()
    {
        $this->db->query("ALTER TABLE tbl_team_members
            DROP COLUMN created_at,
            DROP COLUMN status");
    }
}
