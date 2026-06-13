<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Letter_model extends MY_Model
{
    public $_table_name;
    public $_order_by;
    public $_primary_key;

    public function get_employee_details($employee_id)
    {
        $this->db->select('tbl_account_details.*, tbl_users.user_id, tbl_users.role_id');
        $this->db->join('tbl_users', 'tbl_account_details.user_id = tbl_users.user_id', 'left');
        $this->db->where('tbl_account_details.user_id', $employee_id);
        return $this->db->get('tbl_account_details')->row();
    }
}
