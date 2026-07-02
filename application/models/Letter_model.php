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

    public function get_all_variables($category = null)
    {
        if ($category) {
            $this->db->where('category', $category);
        }
        $this->db->order_by('type', 'ASC');
        $this->db->order_by('label', 'ASC');
        return $this->db->get('tbl_letter_variables')->result();
    }

    public function get_user_variables()
    {
        $this->db->where('type', 'user');
        $this->db->order_by('label', 'ASC');
        return $this->db->get('tbl_letter_variables')->result();
    }

    public function save_variable($data, $id = null)
    {
        $this->_table_name = 'tbl_letter_variables';
        $this->_primary_key = 'id';
        return $this->save($data, $id);
    }

    public function delete_variable($id)
    {
        $this->_table_name = 'tbl_letter_variables';
        $this->_primary_key = 'id';
        $this->delete($id);
    }
}
