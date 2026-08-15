<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_ai_pending_action extends CI_Migration {

    public function up()
    {
        $this->db->query("ALTER TABLE tbl_ai_sessions ADD COLUMN pending_action TEXT DEFAULT NULL");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE tbl_ai_sessions DROP COLUMN pending_action");
    }
}
