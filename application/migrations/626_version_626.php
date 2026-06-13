<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_626 extends CI_Migration {
    public function up(){
        // Add timer_stopped_warnings column to tbl_users
        $fields = array(
            'timer_stopped_warnings' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'null' => FALSE,
                'default' => 0
            )
        );

        if (!$this->db->field_exists('timer_stopped_warnings', 'tbl_users')) {
            $this->dbforge->add_column('tbl_users', $fields);
        }
    }

    public function down(){
        if ($this->db->field_exists('timer_stopped_warnings', 'tbl_users')) {
            $this->dbforge->drop_column('tbl_users', 'timer_stopped_warnings');
        }
    }
}
?>
