<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_1002 extends CI_Migration
{
    public function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `tbl_letter_variables` (
            `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `label` VARCHAR(200) NOT NULL,
            `type` ENUM('system','user') NOT NULL DEFAULT 'user',
            `category` VARCHAR(50) NOT NULL DEFAULT 'general',
            `default_value` TEXT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $system_vars = [
            ['CURRENT_DATE',     'Current Date',     'general',  date('Y-m-d')],
            ['CURRENT_YEAR',     'Current Year',     'general',  date('Y')],
            ['EMPLOYEE_NAME',    'Employee Name',    'employee', ''],
            ['EMPLOYEE_ID',      'Employee ID',      'employee', ''],
            ['EMPLOYEE_ADDRESS', 'Employee Address', 'employee', ''],
            ['EMPLOYEE_PHONE',   'Employee Phone',   'employee', ''],
            ['JOINING_DATE',     'Joining Date',     'employee', ''],
            ['DATE_OF_BIRTH',    'Date of Birth',    'employee', ''],
            ['FATHER_NAME',      'Father Name',      'employee', ''],
            ['MOTHER_NAME',      'Mother Name',      'employee', ''],
            ['GENDER',           'Gender',           'employee', ''],
            ['DESIGNATION',      'Designation',      'employee', ''],
            ['DEPARTMENT',       'Department',       'employee', ''],
            ['COMPANY_NAME',     'Company Name',     'company',  ''],
            ['COMPANY_ADDRESS',  'Company Address',  'company',  ''],
            ['COMPANY_PHONE',    'Company Phone',    'company',  ''],
            ['COMPANY_EMAIL',    'Company Email',    'company',  ''],
            ['CLIENT_NAME',      'Client Name',      'general',  ''],
            ['CLIENT_ADDRESS',   'Client Address',   'general',  ''],
            ['PROJECT_NAME',     'Project Name',     'general',  ''],
            ['PROJECT_ID',       'Project ID',       'general',  ''],
            ['TASK_NAME',        'Task Name',        'general',  ''],
            ['TASK_ID',          'Task ID',          'general',  ''],
        ];

        foreach ($system_vars as $var) {
            $this->db->insert('tbl_letter_variables', [
                'name'          => $var[0],
                'label'         => $var[1],
                'type'          => 'system',
                'category'      => $var[2],
                'default_value' => $var[3],
            ]);
        }

        $this->db->where('config_key', 'version')->update('tbl_config', ['value' => '10.0.2']);
    }

    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS `tbl_letter_variables`");
        $this->db->where('config_key', 'version')->update('tbl_config', ['value' => '10.0.1']);
    }
}
