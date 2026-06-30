<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_screenshot_deletion_log extends CI_Migration {

    public function up()
    {
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
            ],
            'screenshot_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => FALSE,
            ],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('deleted_at');
        $this->dbforge->create_table('tbl_screenshot_deletions', TRUE);
    }

    public function down()
    {
        $this->dbforge->drop_table('tbl_screenshot_deletions');
    }
}
