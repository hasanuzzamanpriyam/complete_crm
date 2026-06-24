<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_632 extends CI_Migration
{
    public function up()
    {
        $this->db->where('config_key', 'version')->update('tbl_config', array('value' => '6.3.2'));

        $fields = $this->db->field_data('tbl_screenshots');
        $field_names = array_map(function ($f) { return $f->name; }, $fields);

        if (!in_array('suspicion_score', $field_names)) {
            $this->db->query("ALTER TABLE tbl_screenshots ADD COLUMN suspicion_score INT DEFAULT 0");
        }
        if (!in_array('is_suspicious', $field_names)) {
            $this->db->query("ALTER TABLE tbl_screenshots ADD COLUMN is_suspicious TINYINT(1) DEFAULT 0");
        }
    }

    public function down()
    {
        $this->db->where('config_key', 'version')->update('tbl_config', array('value' => '6.3.1'));
    }
}
