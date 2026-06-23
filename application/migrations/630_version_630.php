<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_630 extends CI_Migration
{
    public function up()
    {
        $this->db->where('config_key', 'version')->update('tbl_config', array('value' => '6.3.0'));
    }

    public function down()
    {
        $this->db->where('config_key', 'version')->update('tbl_config', array('value' => '6.2.0'));
    }
}
