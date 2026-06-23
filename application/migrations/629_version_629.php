<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_629 extends CI_Migration {
    public function up() {
        // Make task_id nullable in tbl_desktop_time_entries for free-will timer
        if ($this->db->table_exists('tbl_desktop_time_entries')) {
            $this->db->query("ALTER TABLE `tbl_desktop_time_entries` CHANGE `task_id` `task_id` INT(11) UNSIGNED NULL DEFAULT NULL;");
        }

        // Update version
        $this->db->where('config_key', 'version')->update('tbl_config', ['value' => '6.2.9']);
    }

    public function down() {
        if ($this->db->table_exists('tbl_desktop_time_entries')) {
            $this->db->query("ALTER TABLE `tbl_desktop_time_entries` CHANGE `task_id` `task_id` INT(11) UNSIGNED NOT NULL;");
        }

        $this->db->where('config_key', 'version')->update('tbl_config', ['value' => '6.2.8']);
    }
}
