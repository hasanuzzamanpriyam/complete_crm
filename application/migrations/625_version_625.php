<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_625 extends CI_Migration {
    public function up(){
        // Add new columns to tbl_account_details if they don't exist
        $fields = array(
            'probation_start_date' => array(
                'type' => 'DATE',
                'null' => TRUE,
                'default' => NULL
            ),
            'probation_end_date' => array(
                'type' => 'DATE',
                'null' => TRUE,
                'default' => NULL
            ),
            'work_anniversary' => array(
                'type' => 'DATE',
                'null' => TRUE,
                'default' => NULL
            ),
            'permanent_joining_date' => array(
                'type' => 'DATE',
                'null' => TRUE,
                'default' => NULL
            ),
            'telegram_id' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => TRUE,
                'default' => NULL
            ),
            'notice_period_start_date' => array(
                'type' => 'DATE',
                'null' => TRUE,
                'default' => NULL
            ),
            'notice_period_end_date' => array(
                'type' => 'DATE',
                'null' => TRUE,
                'default' => NULL
            ),
            'resignation_date' => array(
                'type' => 'DATE',
                'null' => TRUE,
                'default' => NULL
            ),
            'emergency_contact_name' => array(
                'type' => 'VARCHAR',
                'constraint' => '200',
                'null' => TRUE,
                'default' => NULL
            ),
            'emergency_contact_relationship' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => TRUE,
                'default' => NULL
            ),
            'emergency_contact_number' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => TRUE,
                'default' => NULL
            )
        );

        foreach ($fields as $field_name => $field_def) {
            if (!$this->db->field_exists($field_name, 'tbl_account_details')) {
                $this->dbforge->add_column('tbl_account_details', array($field_name => $field_def));
            }
        }
    }

    public function down(){
        $fields = array(
            'probation_start_date',
            'probation_end_date',
            'work_anniversary',
            'permanent_joining_date',
            'telegram_id',
            'notice_period_start_date',
            'notice_period_end_date',
            'resignation_date',
            'emergency_contact_name',
            'emergency_contact_relationship',
            'emergency_contact_number'
        );

        foreach ($fields as $field_name) {
            if ($this->db->field_exists($field_name, 'tbl_account_details')) {
                $this->dbforge->drop_column('tbl_account_details', $field_name);
            }
        }
    }
}
?>
