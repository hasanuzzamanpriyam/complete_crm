<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_consultation_reminders_sent extends CI_Migration {

    public function up()
    {
        $this->db->query("ALTER TABLE tbl_consultation_appointments
            ADD COLUMN reminders_sent VARCHAR(100) DEFAULT NULL AFTER reminder_sent_at");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE tbl_consultation_appointments
            DROP COLUMN IF EXISTS reminders_sent");
    }
}
