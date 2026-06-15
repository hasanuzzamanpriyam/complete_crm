<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_627 extends CI_Migration {
    public function up() {
        // tbl_user_api_sessions
        if (!$this->db->table_exists('tbl_user_api_sessions')) {
            $this->dbforge->add_field([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
                'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE],
                'access_token_hash' => ['type' => 'VARCHAR', 'constraint' => 64],
                'refresh_token_hash' => ['type' => 'VARCHAR', 'constraint' => 64],
                'expires_at' => ['type' => 'DATETIME'],
                'created_at' => ['type' => 'DATETIME', 'default' => 'CURRENT_TIMESTAMP'],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('access_token_hash');
            $this->dbforge->add_key('user_id');
            $this->dbforge->create_table('tbl_user_api_sessions', TRUE);
        }

        // tbl_desktop_time_entries
        if (!$this->db->table_exists('tbl_desktop_time_entries')) {
            $this->dbforge->add_field([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
                'task_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE],
                'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE],
                'type' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'work'],
                'started_at' => ['type' => 'DATETIME', 'null' => TRUE],
                'paused_at' => ['type' => 'DATETIME', 'null' => TRUE],
                'resumed_at' => ['type' => 'DATETIME', 'null' => TRUE],
                'stopped_at' => ['type' => 'DATETIME', 'null' => TRUE],
                'total_seconds' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'is_running' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'created_at' => ['type' => 'DATETIME', 'default' => 'CURRENT_TIMESTAMP'],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('user_id');
            $this->dbforge->add_key('task_id');
            $this->dbforge->create_table('tbl_desktop_time_entries', TRUE);
        }

        // tbl_screenshots
        if (!$this->db->table_exists('tbl_screenshots')) {
            $this->dbforge->add_field([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
                'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE],
                'task_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => TRUE],
                'time_entry_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => TRUE],
                'file_path' => ['type' => 'VARCHAR', 'constraint' => 500],
                'file_size' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'captured_at' => ['type' => 'DATETIME'],
                'uploaded_at' => ['type' => 'DATETIME', 'default' => 'CURRENT_TIMESTAMP'],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('user_id');
            $this->dbforge->add_key('task_id');
            $this->dbforge->add_key('captured_at');
            $this->dbforge->create_table('tbl_screenshots', TRUE);
        }

        // timesync_demo_mode config
        $config_exists = $this->db->where('config_key', 'timesync_demo_mode')->get('tbl_config')->num_rows();
        if (!$config_exists) {
            $this->db->insert('tbl_config', [
                'config_key' => 'timesync_demo_mode',
                'value' => '1'
            ]);
        }

        // Menu entries
        $parent_exists = $this->db->where('label', 'timesync')->get('tbl_menu')->num_rows();
        if (!$parent_exists) {
            $this->db->insert('tbl_menu', [
                'label' => 'timesync',
                'link' => '',
                'icon' => 'fa fa-clock-o',
                'parent' => 0,
                'sort' => 110,
                'status' => 1,
            ]);
            $parent_id = $this->db->insert_id();

            $children = [
                ['label' => 'timesync_reports', 'link' => 'admin/timesync', 'icon' => 'fa fa-bar-chart', 'parent' => $parent_id, 'sort' => 1, 'status' => 1],
                ['label' => 'timesync_screenshots', 'link' => 'admin/timesync/screenshots', 'icon' => 'fa fa-camera', 'parent' => $parent_id, 'sort' => 2, 'status' => 1],
                ['label' => 'timesync_settings', 'link' => 'admin/timesync/settings', 'icon' => 'fa fa-gear', 'parent' => $parent_id, 'sort' => 3, 'status' => 1],
            ];
            foreach ($children as $child) {
                $this->db->insert('tbl_menu', $child);
            }
        }
    }

    public function down() {
        $this->dbforge->drop_table('tbl_screenshots', TRUE);
        $this->dbforge->drop_table('tbl_desktop_time_entries', TRUE);
        $this->dbforge->drop_table('tbl_user_api_sessions', TRUE);
        $this->db->where('config_key', 'timesync_demo_mode')->delete('tbl_config');
        $this->db->where('label', 'timesync_settings')->delete('tbl_menu');
        $this->db->where('label', 'timesync_screenshots')->delete('tbl_menu');
        $this->db->where('label', 'timesync_reports')->delete('tbl_menu');
        $this->db->where('label', 'timesync')->delete('tbl_menu');
    }
}
