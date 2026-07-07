<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Complaints_model extends MY_Model
{
    public $_table_name;
    public $_order_by;
    public $_primary_key;
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function get_result($where, $tbl_name)
    {
        $result = array();
        $this->db->select('*');
        $this->db->from($tbl_name);
        $this->db->where($where);
        $query_result = $this->db->get();
        if ($query_result !== FALSE && $query_result->num_rows() > 0) {
            $result = $query_result->result();
        }
        return $result;
    }
    
    private function select()
    {
        $this->db->select('tbl_complaints.*,'
            . 'tbl_complaints_types.name as complaints_type_name,'
            . 'tbl_client.client_id, tbl_client.name as client_name');
    }
    
    private function join()
    {
        $this->db->join('tbl_client', 'tbl_client.client_id=' . 'tbl_complaints.client', 'left');
        $this->db->join('tbl_complaints_types', '' . 'tbl_complaints.ticket_sub_type=tbl_complaints_types.id', 'left');
    }
    
    public function get_by_id($id, $where = [])
    {
        $res = array();
        $this->select();
        $this->join();
        $client_id = client_id();
        if (!empty($client_id)) {
            $this->db->where('tbl_complaints.client', $client_id);
        }
        $this->db->where('tbl_complaints.tickets_id', $id);
        $this->db->where($where);
        $res_1 = $this->db->get('tbl_complaints')->row();
        if (!empty($res_1)) {
            $res = $res_1;
        }
        return $res;
    }
    
    
    public function fetch_complaints($filterBy = null, $search_by = null)
    {
        $this->load->model('datatables');
        $this->datatables->select = ('tbl_complaints.*, tbl_client.name as client_name, tbl_complaints_types.name as complaints_type_name');
        $this->datatables->table = 'tbl_complaints';
        $this->datatables->join_table = array('tbl_client', 'tbl_complaints_types');
        $this->datatables->join_where = array('tbl_client.client_id=tbl_complaints.client', 'tbl_complaints_types.id=tbl_complaints.ticket_sub_type');
        $this->datatables->column_order = array('ticket_code', 'subject', 'reporter', 'against', 'priority', 'status', 'tags', 'tbl_complaints_types.name', 'tbl_client.name');
        $this->datatables->column_search = array('ticket_code', 'subject', 'reporter', 'against', 'priority', 'status', 'tags', 'tbl_complaints_types.name', 'tbl_client.name');
        $this->datatables->order = array('tickets_id' => 'desc');
        $where = null;
        
        $client_id = client_id();
        if (!empty($client_id)) {
            $where = array('tbl_complaints.client' => $client_id);
        }
        if (!empty($search_by)) {
            if ($search_by == 'by_client') {
                $where = array('tbl_complaints.client' => $filterBy);
            }
            if ($search_by == 'by_type') {
                $where = array('tbl_complaints.ticket_sub_type' => $filterBy);
            }
        } else {
            if ($filterBy == 'assigned_to_me') {
                $user_id = $this->session->userdata('user_id');
                $where = $user_id;
            } else if ($filterBy == 'everyone') {
                $where = array('permission' => 'all');
            } elseif (!empty($filterBy)) {
                $where = array('status' => $filterBy);
            }
        }
        // get all complaints
        $fetch_data = $this->datatables->get_datatable_permission($where);;
        $data = array();
        foreach ($fetch_data as $_key => $v_tickets_info) {
            if (!empty($v_tickets_info)) {
                $action = null;
                $sub_array = array();
                $sub_array[] = '<div class="checkbox c-checkbox" ><label class="needsclick"> <input value="' . $v_tickets_info->tickets_id . '" type="checkbox"><span class="fa fa-check"></span></label></div>';
                $ticket_code = null;
                $ticket_code .= $v_tickets_info->ticket_code;
                $sub_array[] = $ticket_code;
                $ticket_subject = null;
                if (!isset($client_id)) {
                    $ticket_subject .= '<a class="text-info" href="' . base_url() . 'admin/complaints/details/' . $v_tickets_info->tickets_id . '">' . $v_tickets_info->subject . '</a>';
                } else {
                    $ticket_subject .= '<a class="text-info" href="' . base_url() . 'complaints/complaint/index/' . $v_tickets_info->tickets_id . '">' . $v_tickets_info->subject . '</a>';
                }
                $sub_array[] = $ticket_subject;
                $sub_array[] = '<span class="tags">' . $v_tickets_info->complaints_type_name . '</span>';
                $sub_array[] = '<span class="tags">' . $v_tickets_info->client_name . '</span>';
                $sub_array[] = display_date($v_tickets_info->lodged_date);
                $sub_array[] = display_date($v_tickets_info->due_date);
                $statusss = null;
                if (!empty($v_tickets_info->status)) {
                    if ($v_tickets_info->status == 'open') {
                        $statusss = "<span class='label label-danger'>" . lang($v_tickets_info->status) . "</span>";
                    } elseif ($v_tickets_info->status == 'resolved') {
                        $statusss = "<span class='label label-success'>" . lang($v_tickets_info->status) . "</span>";
                    } elseif ($v_tickets_info->status == 'closed') {
                        $statusss = "<span class='label label-default'>" . lang($v_tickets_info->status) . "</span>";
                    } elseif ($v_tickets_info->status == 'in_progress') {
                        $statusss = "<span class='label label-primary'>" . lang($v_tickets_info->status) . "</span>";
                    } elseif ($v_tickets_info->status == 'waiting_for_someone') {
                        $statusss = "<span class='label label-warning'>" . lang($v_tickets_info->status) . "</span>";
                    }
                }
                $ch_url = base_url() . 'admin/complaints/change_status/';
                $change_status = '<div class="btn-group">
    <button class="btn btn-xs btn-default dropdown-toggle"
            data-toggle="dropdown">
        <span class="caret"></span></button>
    <ul class="dropdown-menu animated zoomIn">
        <li>
            <a href="' . $ch_url . $v_tickets_info->tickets_id . '/open' . '">' . lang('open') . '</a>
            </li>
        <li>
            <a href="' . $ch_url . $v_tickets_info->tickets_id . '/in_progress' . '">' . lang('in_progress') . '</a>
            </li>
        <li>
            <a href="' . $ch_url . $v_tickets_info->tickets_id . '/waiting_for_someone' . '">' . lang('waiting_for_someone') . '</a>
            </li>
        <li>
            <a href="' . $ch_url . $v_tickets_info->tickets_id . '/resolved' . '">' . lang('resolved') . '</a>
            </li>
        <li>
            <a href="' . $ch_url . $v_tickets_info->tickets_id . '/closed' . '">' . lang('closed') . '</a>
        </li>
    </ul>
</div>';
                if (!isset($client_id)) {
                    $sub_array[] = $statusss . ' ' . $change_status;
                } else {
                    $sub_array[] = $statusss;
                }
                if (!isset($client_id)) {
                    $action .= btn_edit('admin/complaints/new_complaint/' . $v_tickets_info->tickets_id) . ' ';
                    $action .= ajax_anchor(base_url("admin/complaints/delete/delete_complaint/$v_tickets_info->tickets_id"), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_" . $_key)) . ' ';
                }
                $action .= btn_view('complaints/complaint/index/' . $v_tickets_info->tickets_id) . ' ';
                $sub_array[] = $action;
    
                $data[] = $sub_array;
            }
        }
        render_table($data, $where);
    }
}
