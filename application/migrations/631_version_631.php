<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_631 extends CI_Migration
{
    public function up()
    {
        $this->db->where('config_key', 'version')->update('tbl_config', array('value' => '6.3.1'));

        $fields = $this->db->field_data('tbl_screenshots');
        $field_names = array_map(function ($f) { return $f->name; }, $fields);

        if (!in_array('keystroke_count', $field_names)) {
            $this->db->query("ALTER TABLE tbl_screenshots ADD COLUMN keystroke_count INT DEFAULT 0");
        }
        if (!in_array('mouse_click_count', $field_names)) {
            $this->db->query("ALTER TABLE tbl_screenshots ADD COLUMN mouse_click_count INT DEFAULT 0");
        }
        if (!in_array('activity_percentage', $field_names)) {
            $this->db->query("ALTER TABLE tbl_screenshots ADD COLUMN activity_percentage DECIMAL(5,2) DEFAULT 0.00");
        }
    }

    public function down()
    {
        $this->db->where('config_key', 'version')->update('tbl_config', array('value' => '6.3.0'));
    }
}
