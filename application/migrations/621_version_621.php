<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_meeting_url_columns extends CI_Migration {
    public function up(){
        if (!$this->db->field_exists('meeting_url', 'tbl_mettings')) {
            $fields = array(
                'meeting_url' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '500',
                    'null' => TRUE,
                    'after' => 'platform'
                )
            );
            $this->dbforge->add_column('tbl_mettings', $fields);
        }
        if (!$this->db->field_exists('meeting_room', 'tbl_mettings')) {
            $fields = array(
                'meeting_room' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '500',
                    'null' => TRUE,
                    'after' => 'meeting_url'
                )
            );
            $this->dbforge->add_column('tbl_mettings', $fields);
        }
    }
    public function down(){
        if ($this->db->field_exists('meeting_url', 'tbl_mettings')) {
            $this->dbforge->drop_column('tbl_mettings', 'meeting_url');
        }
        if ($this->db->field_exists('meeting_room', 'tbl_mettings')) {
            $this->dbforge->drop_column('tbl_mettings', 'meeting_room');
        }
    }
}
?>
