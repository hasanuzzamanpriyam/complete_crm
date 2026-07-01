<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_1001 extends CI_Migration
{
    public function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        $this->db->query("ALTER TABLE `tbl_screenshots` ADD INDEX `idx_user_captured` (`user_id`, `captured_at`)");
        $this->db->where('config_key', 'version')->update('tbl_config', array('value' => '10.0.1'));
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `tbl_screenshots` DROP INDEX `idx_user_captured`");
        $this->db->where('config_key', 'version')->update('tbl_config', array('value' => '10.0.0'));
    }
}
