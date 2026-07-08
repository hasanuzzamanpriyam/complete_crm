<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_633 extends CI_Migration {
    public function up() {
        if ($this->db->table_exists('tbl_desktop_app_usage')) {
            if (!$this->db->field_exists('url', 'tbl_desktop_app_usage')) {
                $this->dbforge->add_column('tbl_desktop_app_usage', [
                    'url' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => TRUE, 'after' => 'window_title'],
                ]);
            }
        }
    }

    public function down() {
        if ($this->db->table_exists('tbl_desktop_app_usage')) {
            if ($this->db->field_exists('url', 'tbl_desktop_app_usage')) {
                $this->dbforge->drop_column('tbl_desktop_app_usage', 'url');
            }
        }
    }
}
