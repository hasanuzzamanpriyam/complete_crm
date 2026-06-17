<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_628 extends CI_Migration {
    public function up() {
        if (!$this->db->table_exists('tbl_desktop_app_usage')) {
            $this->dbforge->add_field([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
                'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE],
                'time_entry_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => TRUE],
                'app_name' => ['type' => 'VARCHAR', 'constraint' => 255],
                'window_title' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => TRUE],
                'total_seconds' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'recorded_at' => ['type' => 'DATE'],
                'created_at' => ['type' => 'DATETIME'],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('user_id');
            $this->dbforge->add_key('time_entry_id');
            $this->dbforge->add_key('recorded_at');
            $this->dbforge->create_table('tbl_desktop_app_usage', TRUE);
        }

        // Add usage_report menu child
        $parent = $this->db->where('label', 'timesync')->get('tbl_menu')->row();
        if ($parent) {
            $existing = $this->db
                ->where('parent', $parent->menu_id)
                ->where('label', 'timesync_usage_report')
                ->get('tbl_menu')
                ->num_rows();
            if (!$existing) {
                $this->db->insert('tbl_menu', [
                    'label' => 'timesync_usage_report',
                    'link' => 'admin/timesync/usage',
                    'icon' => 'fa fa-pie-chart',
                    'parent' => $parent->menu_id,
                    'sort' => 4,
                    'status' => 1,
                ]);
            }
        }
    }

    public function down() {
        $this->dbforge->drop_table('tbl_desktop_app_usage', TRUE);
        $this->db->where('label', 'timesync_usage_report')->delete('tbl_menu');
    }
}
