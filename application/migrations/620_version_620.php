<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_620 extends CI_Migration {
    public function up(){
        // Add platform column if it does not exist
        if (!$this->db->field_exists('platform', 'tbl_mettings')) {
            $fields = array(
                'platform' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '20',
                    'null' => FALSE,
                    'default' => 'zoom',
                    'after' => 'location' // place after location column
                )
            );
            $this->dbforge->add_column('tbl_mettings', $fields);
        }
    }
    public function down(){
        if ($this->db->field_exists('platform', 'tbl_mettings')) {
            $this->dbforge->drop_column('tbl_mettings', 'platform');
        }
    }
}
?>