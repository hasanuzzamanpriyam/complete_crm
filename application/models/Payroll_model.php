<?php

/**
 * Payroll_Model
 * 
 * Handles Payroll operations: templates, deductions, allowances, salaries, etc.
 */
class Payroll_Model extends MY_Model
{
    public $_table_name;
    public $_order_by;
    public $_primary_key;

    /**
     * Save or update a record
     */
    public function save($data, $id = NULL)
    {
        if (empty($this->_table_name) || empty($this->_primary_key)) {
            log_message('error', 'Model properties not set: _table_name or _primary_key');
            return false;
        }

        if (!empty($id)) {
            $this->db->where($this->_primary_key, $id);
            $this->db->update($this->_table_name, $data);
            return $id;
        } else {
            $this->db->insert($this->_table_name, $data);
            return $this->db->insert_id();
        }
    }

    public function get_department_by_id($departments_id)
    {
        $this->db->select('tbl_departments.deptname, tbl_designations.*');
        $this->db->from('tbl_departments');
        $this->db->join('tbl_designations', 'tbl_departments.departments_id = tbl_designations.departments_id', 'left');
        $this->db->where('tbl_departments.departments_id', $departments_id);
        return $this->db->get()->result();
    }

    public function get_emp_info_by_id($designation_id)
    {
        $this->db->select('tbl_account_details.*, tbl_designations.designations');
        $this->db->from('tbl_account_details');
        $this->db->join('tbl_designations', 'tbl_designations.designations_id = tbl_account_details.designations_id', 'left');
        $this->db->where('tbl_designations.designations_id', $designation_id);
        return $this->db->get()->result();
    }

    public function get_emp_salary_list($id = NULL, $designation_id = NULL)
    {
        $this->db->select('tbl_employee_payroll.*, tbl_account_details.*, tbl_salary_template.*, tbl_hourly_rate.*, tbl_designations.*, tbl_departments.deptname');
        $this->db->from('tbl_employee_payroll');
        $this->db->join('tbl_account_details', 'tbl_employee_payroll.user_id = tbl_account_details.user_id', 'left');
        $this->db->join('tbl_salary_template', 'tbl_employee_payroll.salary_template_id = tbl_salary_template.salary_template_id', 'left');
        $this->db->join('tbl_hourly_rate', 'tbl_employee_payroll.hourly_rate_id = tbl_hourly_rate.hourly_rate_id', 'left');
        $this->db->join('tbl_designations', 'tbl_designations.designations_id = tbl_account_details.designations_id', 'left');
        $this->db->join('tbl_departments', 'tbl_departments.departments_id = tbl_designations.departments_id', 'left');

        if (!empty($designation_id)) {
            $this->db->where('tbl_designations.designations_id', $designation_id);
        }

        if (!empty($id)) {
            $this->db->where('tbl_employee_payroll.user_id', $id);
            return $this->db->get()->row();
        } else {
            if (!empty($_POST['length']) && $_POST['length'] != -1) {
                $this->db->limit($_POST['length'], $_POST['start']);
            }
            return $this->db->get()->result();
        }
    }

    public function get_salary_payment_info($salary_payment_id, $result = NULL, $search_type = null)
    {
        $this->db->select('tbl_salary_payment.*, tbl_account_details.*, tbl_designations.*, tbl_departments.deptname');
        $this->db->from('tbl_salary_payment');
        $this->db->join('tbl_account_details', 'tbl_salary_payment.user_id = tbl_account_details.user_id', 'left');
        $this->db->join('tbl_designations', 'tbl_designations.designations_id = tbl_account_details.designations_id', 'left');
        $this->db->join('tbl_departments', 'tbl_departments.departments_id = tbl_designations.departments_id', 'left');

        if ($search_type === 'employee') {
            $this->db->where('tbl_salary_payment.user_id', $salary_payment_id);
        } elseif ($search_type === 'month') {
            $this->db->where('tbl_salary_payment.payment_month', $salary_payment_id);
        } elseif ($search_type === 'period') {
            $this->db->where('tbl_salary_payment.payment_month >=', $salary_payment_id['start_month']);
            $this->db->where('tbl_salary_payment.payment_month <=', $salary_payment_id['end_month']);
        } else {
            $this->db->where('tbl_salary_payment.salary_payment_id', $salary_payment_id);
        }

        if (!empty($_POST['length']) && $_POST['length'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }

        $query = $this->db->get();
        return !empty($result) ? $query->result() : $query->row();
    }

    public function get_advance_salary_info_by_date($payment_month = NULL, $id = NULL, $user_id = NULL)
    {
        $this->db->select('tbl_advance_salary.*, tbl_account_details.*');
        $this->db->from('tbl_advance_salary');
        $this->db->join('tbl_account_details', 'tbl_account_details.user_id = tbl_advance_salary.user_id', 'left');

        if ($this->session->userdata('user_type') != 1) {
            $this->db->where('tbl_advance_salary.user_id', $this->session->userdata('user_id'));
            $this->db->where('tbl_advance_salary.deduct_month', $payment_month);
            return $this->db->get()->result();
        } elseif (!empty($id)) {
            $this->db->where('tbl_advance_salary.advance_salary_id', $id);
            return $this->db->get()->row();
        } elseif (!empty($user_id)) {
            $this->db->where('tbl_advance_salary.status', '1');
            if (!empty($payment_month)) {
                $this->db->where('tbl_advance_salary.deduct_month', $payment_month);
            }
            $this->db->where('tbl_account_details.user_id', $user_id);
            return $this->db->get()->result();
        } else {
            $this->db->where('tbl_advance_salary.deduct_month', $payment_month);
            return $this->db->get()->result();
        }
    }

    public function view_advance_salary($id = NULL)
    {
        $this->db->select('tbl_advance_salary.*, tbl_account_details.*');
        $this->db->from('tbl_advance_salary');
        $this->db->join('tbl_account_details', 'tbl_account_details.user_id = tbl_advance_salary.user_id', 'left');
        $this->db->where('tbl_advance_salary.advance_salary_id', $id);
        return $this->db->get()->row();
    }

    public function my_advance_salary_info($all = null)
    {
        $this->db->select('tbl_advance_salary.*, tbl_account_details.*');
        $this->db->from('tbl_advance_salary');
        $this->db->join('tbl_account_details', 'tbl_account_details.user_id = tbl_advance_salary.user_id', 'left');
        if (empty($all)) {
            $this->db->where('tbl_advance_salary.user_id', $this->session->userdata('user_id'));
        } else {
            $this->db->order_by('tbl_advance_salary.request_date', 'DESC');
        }

        if (!empty($_POST['length']) && $_POST['length'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }

        return $this->db->get()->result();
    }

    public function get_attendance_info_by_date($start_date, $end_date, $user_id)
    {
        $this->db->select('tbl_attendance.*, tbl_clock.*');
        $this->db->from('tbl_attendance');
        $this->db->join('tbl_clock', 'tbl_clock.attendance_id = tbl_attendance.attendance_id', 'left');
        $this->db->where('tbl_attendance.date_in >=', $start_date);
        $this->db->where('tbl_attendance.date_in <=', $end_date);
        $this->db->where('tbl_attendance.user_id', $user_id);
        $this->db->where('tbl_attendance.attendance_status', 1);
        return $this->db->get()->result();
    }

    public function get_provident_fund_info_by_date($start_date, $end_date, $user_id = null)
    {
        $this->db->select('tbl_salary_payment.*, tbl_salary_payment_deduction.*, tbl_account_details.*');
        $this->db->from('tbl_salary_payment');
        $this->db->join('tbl_salary_payment_deduction', 'tbl_salary_payment_deduction.salary_payment_id = tbl_salary_payment.salary_payment_id', 'left');
        $this->db->join('tbl_account_details', 'tbl_account_details.user_id = tbl_salary_payment.user_id', 'left');
        $this->db->where('tbl_salary_payment.payment_month >=', $start_date);
        $this->db->where('tbl_salary_payment.payment_month <=', $end_date);
        $this->db->where('tbl_salary_payment_deduction.salary_payment_deduction_label', lang('provident_fund'));

        if (!empty($user_id)) {
            $this->db->where('tbl_salary_payment.user_id', $user_id);
        }

        return $this->db->get()->result();
    }
}