<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_team_member_status extends CI_Migration {

    public function up()
    {
        $this->db->query("ALTER TABLE tbl_team_members
            ADD COLUMN status ENUM('pending','approved','left') NOT NULL DEFAULT 'approved'
            AFTER is_manager,
            ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE tbl_team_members
            DROP COLUMN created_at,
            DROP COLUMN status");
    }
}
