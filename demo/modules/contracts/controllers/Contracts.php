<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Contracts extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('contracts_model');
        $this->load->model('tasks_model');
    }
    
    public function index()
    {
        $data['count_active'] = total_rows('tbl_contracts', array("DATE(end_date) >" => date('Y-m-d'), 'parent_contract_id' => null, 'trash' => 0));
        $data['count_expired'] = total_rows('tbl_contracts', array("DATE(end_date) <" => date('Y-m-d'), 'parent_contract_id' => null, 'trash' => 0));
        $data['expiring'] = $this->contracts_model->expiring_contracts();
        $data['count_recently_created'] = $this->contracts_model->recent_contracts();
        
        $data['title'] = lang('contracts');
        $data['tab'] = lang('contracts');
        $data['active'] = 1;
        if ($this->session->userdata('user_type') == 1 || !empty($is_department_head)) {
            $data['all_client'] = $this->contracts_model->get_result(array(), 'tbl_client');
            $data['cproject_info'] = $this->contracts_model->get_result(array(), 'tbl_project');
            $data['all_contracts_types'] = $this->contracts_model->get_result(array(), 'tbl_contracts_types');
        }
        $data['page_content'] = $this->load->view('manage_contracts', $data, true);
        $data['subview'] = $this->load->view('contracts_subview', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }
    
    public function contractsList($filterBy = null, $search_by = null)
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('datatables');
            $this->datatables->select = 'tbl_contracts.*, tbl_client.name as client_name, tbl_contracts_types.name as contract_type_name, tbl_project.project_name as project_name, ';
            $this->datatables->table = 'tbl_contracts';
            $this->datatables->join_table = array('tbl_client', 'tbl_contracts_types', 'tbl_project');
            $this->datatables->join_where = array('tbl_contracts.client=tbl_client.client_id', 'tbl_contracts.contract_type=tbl_contracts_types.id', 'tbl_contracts.project_id=tbl_project.project_id');
            $main_column = array('contract_id', 'subject', 'client_name', 'project_name', 'contract_type', 'contract_value', 'start_date', 'end_date', 'signature');
            $action_array = array('contract_id');
            $result = array_merge($main_column, $action_array);
            $this->datatables->column_order = $result;
            $this->datatables->column_search = $result;
            $this->datatables->order = array('contract_id' => 'DESC');
            
            $where = array('trash !=' => 1);
            if (!empty($search_by)) {
                if ($search_by == 'by_project') {
                    $where += array('tbl_contracts.project_id' => $filterBy);
                }
                if ($search_by == 'by_type') {
                    $where += array('contract_type' => $filterBy);
                }
                if ($search_by == 'by_client') {
                    $where += array('tbl_contracts.client' => $filterBy);
                }
            } else {
                if ($filterBy == 'expired') {
                    $where += array("DATE(tbl_contracts.end_date) <" => date('Y-m-d'));
                }
                if ($filterBy == 'active') {
                    $where += array("DATE(tbl_contracts.end_date) >" => date('Y-m-d'));
                }
                if ($filterBy == 'expiring') {
                    $where += array("DATE(tbl_contracts.end_date) <" => date('Y-m-d'), "DATE(tbl_contracts.end_date) >" => date('Y-m-d', strtotime('+7 days')));
                }
                // filter by recent created
                if ($filterBy == 'recently_created') {
                    $where += array("DATE(tbl_contracts.date_added) >" => date('Y-m-d', strtotime('-7 days')));
                }
                
                // filter by trash
                if ($filterBy == 'trash') {
                    $where = array('trash' => 1);
                }
            }
            $fetch_data = $this->datatables->get_datatable_permission($where);
            
            $data = array();
            
            $edited = can_action_by_label('contracts', 'edited');
            $deleted = can_action_by_label('contracts', 'deleted');
            foreach ($fetch_data as $_key => $contract) {
                $action = null;
                $sub_array = array();
                $name = null;
                $name .= '<a class="text-info" href="' . base_url() . 'contracts/contracts/details/' . $contract->contract_id . '">' . $contract->subject . '</a>';
                
                $sub_array[] = $name;
                $sub_array[] = $contract->client_name;
                $sub_array[] = $contract->project_name;
                $sub_array[] = $contract->contract_type_name;
                $sub_array[] = $contract->contract_value;
                $sub_array[] = display_date($contract->start_date);
                $sub_array[] = display_date($contract->end_date);
                $sub_array[] = $contract->signed == 1 ? '<span class="text-success">' . lang("signed") . '</span>' : '<span class="text-danger">' . lang("not_signed") . '</span>';
                
                $action .= btn_view('contracts/contract/index/' . $contract->contract_id) . ' ';
                if ($edited) {
                    $action .= btn_edit('admin/contracts/new_contract/' . $contract->contract_id) . ' ';
                }
                if ($deleted) {
                    $action .= ajax_anchor(base_url("admin/contracts/delete/$contract->contract_id"), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_" . $_key)) . ' ';
                }
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
            render_table($data, $where);
        } else {
            redirect('admin/dashboard');
        }
    }
    
    public function contract_types()
    {
        $data['title'] = lang('contract_types');
        $data['active'] = 3;
        $data['module'] = 'contracts';
        $data['contracts_types'] = get_result('tbl_contracts_types');
        $data['page_content'] = $this->load->view('contract_types', $data, true);
        $data['subview'] = $this->load->view('contracts_subview', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }
    
    public function new_contract_type($id = null)
    {
        $data['id'] = $id;
        $data['module'] = 'contracts';
        $data['title'] = lang('new_contract_type');
        $data['active'] = 3;
        $data['type_name'] = '';
        if ($id && is_numeric($id)) {
            $data['contract_type'] = $this->contracts_model->check_by(array('id' => $id), 'tbl_contracts_types');
            if (empty($data['contract_type'])) {
                show_404();
                exit;
            }
            $data['type_name'] = $data['contract_type']->name;
        }
        $data['subview'] = $this->load->view('new_contract_type', $data);
        $this->load->view('admin/_layout_modal', $data);
    }
    
    
    public function delete_contract_type($id)
    {
        $type = 'error';
        $msg = lang('problem_deleting') . ' ' . lang('contract_type');
        
        if ($id && is_numeric($id)) {
            $contract_type = $this->contracts_model->check_by(array('id' => $id), 'tbl_contracts_types');
            if (empty($contract_type)) {
                $msg = lang('problem_deleting') . ' ' . lang('contract_type');
            } else {
                $contract = $this->contracts_model->check_by(array('contract_type' => $id), 'tbl_contracts');
                if (!empty($contract)) {
                    $msg = lang('type_used_not_deleted');
                } else {
                    $this->contracts_model->_table_name = 'tbl_contracts_types';
                    $this->contracts_model->_primary_key = 'id';
                    $this->contracts_model->delete($id);
                    $action = 'activity_delete_contract_type';
                    $activity = array(
                        'user' => $this->session->userdata('user_id'),
                        'module' => 'account',
                        'module_field_id' => $id,
                        'activity' => $action,
                        'icon' => 'fa-circle-o',
                        'value1' => $contract_type->name
                    );
                    $this->contracts_model->_table_name = 'tbl_activities';
                    $this->contracts_model->_primary_key = 'activities_id';
                    $this->contracts_model->save($activity);
                    
                    $type = 'success';
                    $msg = lang('contract_type') . ' ' . lang('deleted');
                }
            }
        }
        echo json_encode(array("status" => $type, 'message' => $msg));
        exit();
    }
    
    
    public function save_new_contract_type($id = null)
    {
        $type = "error";
        $msg = lang('something_is_wrong');
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div>', '</div>');
        $this->form_validation->set_rules('name', lang('name'), 'trim|required');
        
        if ($this->form_validation->run() == true) {
            $ins['name'] = $this->input->post('name');
            $this->contracts_model->_table_name = 'tbl_contracts_types';
            $this->contracts_model->_primary_key = 'id';
            if (!empty($id)) {
                $return_id = $this->contracts_model->save($ins, $id);
            } else {
                $return_id = $id = $this->contracts_model->save($ins);
            }
            
            if (!empty($return_id)) {
                $_POST = array();
                $activity = array(
                    'user' => $this->session->userdata('user_id'),
                    'module' => 'contracts',
                    'module_field_id' => $id,
                    'activity' => ('activity_saved_a_contract_type'),
                    'value1' => $ins['name']
                );
                $this->contracts_model->_table_name = 'tbl_activities';
                $this->contracts_model->_primary_key = 'activities_id';
                $this->contracts_model->save($activity);
                
                $type = "success";
                $msg = lang('contract_type_saved');
                
                $result = array(
                    'id' => $id,
                    'name' => $ins['name'],
                    'status' => $type,
                    'message' => $msg,
                );
                echo json_encode($result);
                exit();
            }
        }
        if (!empty(validation_errors())) {
            $type = "error";
            $msg = '';
            foreach ($this->input->post() as $k => $v) {
                $msg .= form_error($k, '', '</br>');
            }
        }
        set_message($type, $msg);
        redirect($_SERVER['HTTP_REFERER']);
    }
    
    public function new_contract($id = null)
    {
        $data = array();
        $data['title'] = lang('new_contract');;
        $data['active'] = 2;
        
        $data['client_id'] = '';
        $data['project_id'] = '';
        $data['subject'] = '';
        $data['contract_value'] = '';
        $data['contract_type'] = '';
        $data['start_date'] = '';
        $data['end_date'] = '';
        $data['trash'] = '';
        $data['visible_to_client'] = '';
        $data['description'] = '';
        
        if ($id && is_numeric($id)) {
            $data['contract'] = $contract = $this->contracts_model->get_by_id($id);
            if (empty($contract) || $contract->parent_contract_id != null) {
                show_404();
                exit;
            }
            
            if ($contract->signed == '1' || $contract->marked_as_signed == '1' || $contract->parent_contract_id != null) {
                $type = "error";
                $msg = lang('signed_not_edited');
                set_message($type, $msg);
                redirect('admin/contracts');
            }
            
            $data['client_id'] = $contract->client;
            $data['project_id'] = $contract->project_id;
            $data['subject'] = $contract->subject;
            $data['contract_value'] = $contract->contract_value;
            $data['contract_type'] = $contract->contract_type;
            $data['start_date'] = $contract->start_date;
            $data['end_date'] = $contract->end_date;
            $data['trash'] = $contract->trash;
            $data['visible_to_client'] = $contract->visible_to_client;
            $data['description'] = $contract->description;
        }
        
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div>', '</div>');
        $this->form_validation->set_rules('client_id', lang('client'), 'trim|required');
        $this->form_validation->set_rules('subject', lang('subject'), 'trim|required');
        $this->form_validation->set_rules('start_date', lang('start_date'), 'trim|required');
        
        
        if ($this->form_validation->run() == true) {
            $this->save_contract($id);
        }
        
        if (!empty(validation_errors())) {
            $type = "error";
            $msg = '';
            foreach ($this->input->post() as $k => $v) {
                $msg .= form_error($k, '', '</br>');
            }
        }
        
        $data['all_client'] = get_result('tbl_client');
        $data['data'] = $data;
        $data['page_content'] = $this->load->view('new_contract', $data, true);
        $data['subview'] = $this->load->view('contracts_subview', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }
    
    public function details($id)
    {
        $data = array();
        $data['title'] = lang('contract_details');;
        $data['active'] = 'details';
        $data['id'] = $id;
        
        $data['contract'] = $contract = $this->contracts_model->get_by_id($id);
        if (empty($contract)) {
            show_404();
        }
        $data['client_id'] = $contract->client;
        $data['project_id'] = $contract->project_id;
        $data['subject'] = $contract->subject;
        
        
        $data['page_content'] = $this->load->view('details', $data, true);
        $data['subview'] = $this->load->view('details_layout', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }
    
    public function attachments($id)
    {
        $data = array();
        $data['title'] = lang('contract_details');
        $data['active'] = 'attachments';
        $data['contract'] = $this->contracts_model->get_by_id($id);
        if (empty($data['contract'])) {
            show_404();
        }
        $data['module'] = 'contracts';
        $data['id'] = $id;
        $data['page_content'] = $this->load->view('admin/common/attachments', $data, TRUE);
        $data['subview'] = $this->load->view('details_layout', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }
    
    
    public function comments($id)
    {
        $data = array();
        $data['title'] = lang('contract_details');;
        $data['active'] = 'comments';
        $data['contract'] = $contract = $this->contracts_model->get_by_id($id);
        if (empty($contract)) {
            show_404();
        }
        $data['name'] = $contract->subject;
        $data['module'] = 'contracts';
        $data['id'] = $id;
        $data['dropzone'] = true;
        
        $data['page_content'] = $this->load->view('admin/common/comments', $data, true);
        $data['subview'] = $this->load->view('details_layout', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }
    
    public function history($id)
    {
        $data = array();
        $data['title'] = lang('contract_details');;
        $data['active'] = 'history';
        $data['module'] = 'contracts';
        $data['id'] = $id;
        $data['module_field_id'] = $id;
        
        $data['contract'] = $contract = $this->contracts_model->get_by_id($id);
        if (empty($contract)) {
            show_404();
        }
        $data['subject'] = $contract->subject;
        
        $data['page_content'] = $this->load->view('history', $data, true);
        $data['subview'] = $this->load->view('details_layout', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }
    
    
    public function contractsHistory($filterBy = null, $search_by = null)
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('datatables');
            $this->datatables->table = 'tbl_contract_renewals';
            $main_column = array('new_start_date', 'old_start_date', 'new_end_date', 'old_end_date', 'new_value', 'old_value', 'renewed_by', 'date_renewed');
            $action_array = array('contract_renewal_id');
            $result = array_merge($main_column, $action_array);
            $this->datatables->column_order = $result;
            $this->datatables->column_search = $result;
            $this->datatables->order = array('contract_renewal_id' => 'desc');
            $where = array();
            
            $fetch_data = make_datatables($where);
            
            $data = array();
            
            foreach ($fetch_data as $_key => $contractHistory) {
                $action = null;
                
                $sub_array = array();
                $sub_array[] = display_date($contractHistory->new_start_date);
                $sub_array[] = display_date($contractHistory->old_start_date);
                $sub_array[] = display_date($contractHistory->new_end_date);
                $sub_array[] = display_date($contractHistory->old_end_date);
                $sub_array[] = $contractHistory->new_value;
                $sub_array[] = $contractHistory->old_value;
                
                $sub_array[] = display_datetime($contractHistory->date_renewed);
                $sub_array[] = '<span class="label label-success">' . $contractHistory->renewed_by . '</span>';
                
                $action .= ajax_anchor(base_url("admin/contracts/delete_renewal_history/$contractHistory->contract_renewal_id/$contractHistory->contract_id"), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_" . $_key)) . ' ';
                $sub_array[] = $action;
                $data[] = $sub_array;
                
            }
            render_table($data, $where);
        } else {
            redirect('admin/dashboard');
        }
    }
    
    public function renew($id)
    {
        $data['contract'] = $contract = $this->contracts_model->get_by_id($id);
        
        if (empty($contract)) {
            show_404();
        }
        $data['title'] = lang('contract_details');;
        $data['title_suffix'] = $contract->subject;
        $data['active'] = 'history';
        $data['module'] = 'contracts';
        $data['id'] = $id;
        $data['module_field_id'] = $id;
        $data['modal_subview'] = $this->load->view('renew', $data, false);
        $this->load->view('admin/_layout_modal', $data);
    }
    
    public function save_renew($module_field_id)
    {
        $type = "error";
        $msg = lang('something_is_wrong');
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div>', '</div>');
        $this->form_validation->set_rules('start_date', lang('start_date'), 'trim|required');
        
        if ($this->form_validation->run() !== false) {
            
            $keepSignature = $this->input->post('keep_signature', true);
            $contract = $this->contracts_model->get_by_id($module_field_id);
            
            if (!empty($keepSignature)) {
                $data['new_value'] = $contract->contract_value;
            } else {
                $data['new_value'] = $this->input->post('contract_value', true);
            }
            // get new end date with sql format
            $data['new_start_date'] = date('Y-m-d', strtotime($this->input->post('start_date', true)));
            $data['new_end_date'] = date('Y-m-d', strtotime($this->input->post('end_date', true)));
            $data['date_renewed'] = date('Y-m-d H:i:s');
            $data['renewed_by'] = fullname();
            $data['renewed_by_id'] = my_id();
            $data['is_on_old_expiry_notified'] = $contract->is_expiry_notified;
            $data['old_start_date'] = $contract->start_date;
            $data['old_end_date'] = $contract->end_date;
            $data['old_value'] = $contract->contract_value;
            $data['contract_id'] = $contract->contract_id;
            
            $this->contracts_model->_table_name = "tbl_contract_renewals"; // table name
            $this->contracts_model->_primary_key = "contract_renewal_id"; // $id
            $insert_id = $this->contracts_model->save($data);
            
            if (!empty($insert_id)) {
                $_data = [
                    'start_date' => $data['new_start_date'],
                    'contract_value' => $data['new_value'],
                    'is_expiry_notified' => 0,
                ];
                if (isset($data['new_end_date'])) {
                    $_data['end_date'] = $data['new_end_date'];
                }
                $_data['signed'] = 0;
                $_data['signature'] = '';
                $_data['marked_as_signed'] = 0;
                $_data['is_expiry_notified'] = 0;
                $_data['acceptance_firstname'] = '';
                $_data['acceptance_lastname'] = '';
                $_data['acceptance_email'] = '';
                $_data['acceptance_date'] = null;
                $_data['acceptance_ip'] = '';
                $this->contracts_model->_table_name = "tbl_contracts"; // table name
                $this->contracts_model->_primary_key = "contract_id"; // $id
                $id = $this->contracts_model->save($_data, $module_field_id);
                if (!empty($id)) {
                    $type = "success";
                    $msg = lang('contract_renewed');
                } else {
                    // delete the previous entry
                    $this->contracts_model->_table_name = "tbl_contract_renewals"; // table name
                    $this->contracts_model->_primary_key = "contract_renewal_id"; // $id
                    $this->contracts_model->delete($insert_id);
                }
            }
        }
        
        if (!empty(validation_errors())) {
            $type = "error";
            $msg = validation_errors();
        }
        set_message($type, $msg);
        $url = base_url('admin/contracts/history/' . $module_field_id);
        redirect($url);
    }
    
    
    public function tasks($id, $module = 'contracts')
    {
        $data = array();
        $data['title'] = lang('contract_details');;
        $data['active'] = 'tasks';
        $data['module'] = $module;
        $data['contract'] = $contract = $this->contracts_model->get_by_id($id);
        if (empty($contract)) {
            show_404();
        }
        $data['subject'] = $contract->subject;
        $data['id'] = $id;
        $data['module_task_info'] = get_result('tbl_task', array('module' => $module, 'module_field_id' => $id));
        $data['page_content'] = $this->load->view('tasks', $data, true);
        $data['subview'] = $this->load->view('details_layout', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }
    
    public function new_task($id = 'contracts', $module_field_id = null)
    {
        $data = array();
        $data['title'] = lang('new_task');
        $data['active'] = 'new_task';
        if (is_numeric($id)) {
            $data['task_info'] = $this->contracts_model->check_by(array('task_id' => $id), 'tbl_task');
            $data['module'] = $data['task_info']->module;
            $data['module_field_id'] = $data['task_info']->module_field_id;
            $data['contract'] = $contract = $this->contracts_model->get_by_id($data['module_field_id']);
            if (empty($contract)) {
                show_404();
            }
        } else {
            $data['module'] = $id;
            $data['module_field_id'] = $module_field_id;
        }
        $data['id'] = $data['module_field_id'];
        $data['assign_user'] = $this->contracts_model->allowed_user('54');
        
        $data['subject'] = $contract->subject;
        $data['page_content'] = $this->load->view('new_task', $data, true);
        $data['subview'] = $this->load->view('details_layout', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }
    
    public function save_task($id = null)
    {
        $created = can_action('54', 'created');
        $edited = can_action('54', 'edited');
        if (!empty($created) || !empty($edited) && !empty($id)) {
            $data = $this->tasks_model->array_from_post(array(
                'module',
                'module_field_id',
                'task_name',
                'category_id',
                'task_description',
                'task_start_date',
                'due_date',
                'task_progress',
                'calculate_progress',
                'client_visible',
                'task_status',
                'hourly_rate',
                'tags',
                'billable'
            ));
            
            $estimate_hours = $this->input->post('task_hour', true);
            $check_flot = explode('.', $estimate_hours);
            if (!empty($check_flot[0])) {
                if (!empty($check_flot[1])) {
                    $data['task_hour'] = $check_flot[0] . ':' . $check_flot[1];
                } else {
                    $data['task_hour'] = $check_flot[0] . ':00';
                }
            } else {
                $data['task_hour'] = '0:00';
            }
            
            
            if ($data['task_status'] == 'completed') {
                $data['task_progress'] = 100;
            }
            if ($data['task_progress'] == 100) {
                $data['task_status'] = 'completed';
            }
            if (empty($id)) {
                $data['created_by'] = $this->session->userdata('user_id');
            }
            if (empty($data['billable'])) {
                $data['billable'] = 'No';
            }
            if (empty($data['hourly_rate'])) {
                $data['hourly_rate'] = '0';
            }
            $result = 0;
            
            $data['project_id'] = null;
            $data['milestones_id'] = null;
            $data['goal_tracking_id'] = null;
            $data['bug_id'] = null;
            $data['leads_id'] = null;
            $data['opportunities_id'] = null;
            $data['sub_task_id'] = null;
            $data['transactions_id'] = null;
            
            
            $permission = $this->input->post('permission', true);
            if (!empty($permission)) {
                if ($permission == 'everyone') {
                    $assigned = 'all';
                    $assigned_to['assigned_to'] = $this->tasks_model->allowed_user_id('54');
                } else {
                    $assigned_to = $this->tasks_model->array_from_post(array('assigned_to'));
                    if (!empty($assigned_to['assigned_to'])) {
                        foreach ($assigned_to['assigned_to'] as $assign_user) {
                            $assigned[$assign_user] = $this->input->post('action_' . $assign_user, true);
                        }
                    }
                }
                if (!empty($assigned)) {
                    if ($assigned != 'all') {
                        $assigned = json_encode($assigned);
                    }
                } else {
                    $assigned = 'all';
                }
                $data['permission'] = $assigned;
            } else {
                set_message('error', lang('assigned_to') . ' Field is required');
                if (empty($_SERVER['HTTP_REFERER'])) {
                    redirect('admin/tasks/all_task');
                } else {
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
            
            //save data into table.
            $this->tasks_model->_table_name = "tbl_task"; // table name
            $this->tasks_model->_primary_key = "task_id"; // $id
            $id = $this->tasks_model->save($data, $id);
            
            $this->tasks_model->set_task_progress($id);
            
            $u_data['index_no'] = $id;
            $id = $this->tasks_model->save($u_data, $id);
            $u_data['index_no'] = $id;
            $id = $this->tasks_model->save($u_data, $id);
            save_custom_field(3, $id);
            
            if ($assigned == 'all') {
                $assigned_to['assigned_to'] = $this->tasks_model->allowed_user_id('54');
            }
            
            if (!empty($id)) {
                $msg = lang('update_task');
                $activity = 'activity_update_task';
                $id = $id;
                if (!empty($assigned_to['assigned_to'])) {
                    // send update
                    $this->notify_assigned_tasks($assigned_to['assigned_to'], $id, true);
                }
            } else {
                $msg = lang('save_task');
                $activity = 'activity_new_task';
                if (!empty($assigned_to['assigned_to'])) {
                    $this->notify_assigned_tasks($assigned_to['assigned_to'], $id);
                }
            }
            
            $url = 'admin/' . $data['module'] . '/tasks/' . $data['module_field_id'];
            // save into activities
            $activities = array(
                'user' => $this->session->userdata('user_id'),
                'module' => $data['module'],
                'module_field_id' => $id,
                'activity' => $activity,
                'icon' => 'fa-tasks',
                'link' => $url,
                'value1' => $data['task_name'],
            );
            // Update into tbl_project
            $this->tasks_model->_table_name = "tbl_activities"; //table name
            $this->tasks_model->_primary_key = "activities_id";
            $this->tasks_model->save($activities);
            
            if (!empty($data['project_id'])) {
                $this->tasks_model->set_progress($data['project_id']);
            }
            
            $type = "success";
            $message = $msg;
            set_message($type, $message);
            if (!empty($data['module_field_id']) && is_numeric($data['module_field_id'])) {
                redirect($url);
            } else {
                redirect('admin/tasks/details/' . $id);
            }
        } else {
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    
    public function notify_assigned_tasks($users, $task_id, $update = null)
    {
        if (!empty($update)) {
            $email_template = email_templates(array('email_group' => 'tasks_updated'));
            $description = 'not_task_update';
        } else {
            $email_template = email_templates(array('email_group' => 'task_assigned'));
            $description = 'assign_to_you_the_tasks';;
        }
        $tasks_info = $this->tasks_model->check_by(array('task_id' => $task_id), 'tbl_task');
        $message = $email_template->template_body;
        
        $subject = $email_template->subject;
        
        $task_name = str_replace("{TASK_NAME}", $tasks_info->task_name, $message);
    
        $assigned_by = str_replace("{ASSIGNED_BY}", ucfirst($this->session->userdata('name')), $task_name);
        $Link = str_replace("{TASK_URL}", base_url() . 'admin/tasks/details/' . $tasks_info->task_id, $assigned_by);
        $message = str_replace("{SITE_NAME}", config_item('company_name'), $Link);
        
        $data['message'] = $message;
        $message = $this->load->view('email_template', $data, true);
        
        $params['subject'] = $subject;
        $params['message'] = $message;
        $params['resourceed_file'] = '';
        
        foreach ($users as $v_user) {
            $login_info = $this->tasks_model->check_by(array('user_id' => $v_user), 'tbl_users');
            $params['recipient'] = $login_info->email;
            $this->tasks_model->send_email($params);
            if ($v_user != $this->session->userdata('user_id')) {
                add_notification(array(
                    'to_user_id' => $v_user,
                    'from_user_id' => true,
                    'description' => $description,
                    'link' => 'admin/tasks/details/' . $task_id,
                    'value' => lang('task') . ' ' . $tasks_info->task_name,
                ));
            }
        }
        show_notification($users);
    }
    
    public function notes($id, $module = 'contracts')
    {
        $data = array();
        $data['title'] = lang('contract_details');;
        $data['active'] = 'notes';
        $data['id'] = $id;
        $data['module'] = $module;
        $data['module_field_id'] = $id;
        
        $data['contract'] = $contract = $this->contracts_model->get_by_id($id);
        if (empty($contract)) {
            show_404();
        }
        $data['subject'] = $contract->subject;
        $data['module_notes'] = get_result('tbl_leads_notes', array('module' => $module, 'module_field_id' => $id));
        $data['page_content'] = $this->load->view('notes', $data, true);
        $data['subview'] = $this->load->view('details_layout', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }
    
    public function save_notes($leads_id)
    {
        $type = "error";
        $message = lang('some_thing_is_wrong');
        $data = $this->contracts_model->array_from_post(array('notes', 'contacted_indicator', 'module', 'module_field_id'));
        $data['user_id'] = my_id();
        //save data into table.
        $this->contracts_model->_table_name = 'tbl_leads_notes';
        $this->contracts_model->_primary_key = 'notes_id';
        $id = $this->contracts_model->save($data);
        
        if ($id) {
            $url = 'admin/' . $data['module'] . '/notes/' . $data['module_field_id'];
            // save into activities
            $activities = array(
                'user' => $this->session->userdata('user_id'),
                'module' => $data['module'],
                'module_field_id' => $data['module_field_id'],
                'activity' => 'activity_update_notes',
                'icon' => 'fa-folder-open-o',
                'link' => $url,
                'value1' => $data['notes'],
            );
            // Update into tbl_project
            $this->contracts_model->_table_name = "tbl_activities"; //table name
            $this->contracts_model->_primary_key = "activities_id";
            $this->contracts_model->save($activities);
            
            $type = "success";
            $message = lang('update_notes');
        }
        set_message($type, $message);
        redirect($url);
    }
    
    public function delete_notes($notes_id, $leads_id)
    {
        $notes_info = get_row('tbl_leads_notes', array('notes_id' => $notes_id));
        $url = 'admin/' . $notes_info->module . '/notes/' . $notes_info->module_field_id;
        // save into activities
        $activities = array(
            'user' => $this->session->userdata('user_id'),
            'module' => $notes_info->module,
            'module_field_id' => $notes_info->module_field_id,
            'activity' => $notes_info->module . '_notes_deleted',
            'icon' => 'fa-folder-open-o',
            'link' => $url,
            'value1' => $notes_info->notes,
        );
        // Update into tbl_project
        $this->contracts_model->_table_name = "tbl_activities"; //table name
        $this->contracts_model->_primary_key = "activities_id";
        $this->contracts_model->save($activities);
        
        $this->contracts_model->_table_name = 'tbl_leads_notes';
        $this->contracts_model->_primary_key = 'notes_id';
        $this->contracts_model->delete($notes_id);
        
        echo json_encode(array("status" => 'success', 'message' => lang('leads_notes_deleted')));
        exit();
    }
    
    public function templates($module_field_id, $module = 'contracts')
    {
        $data = array();
        $data['contract'] = $contract = $this->contracts_model->get_by_id($module_field_id);
        if (empty($contract)) {
            show_404();
        }
        
        $data['title'] = lang('templates');;
        $data['active'] = 'templates';
        $data['module'] = $module;
        $data['module_field_id'] = $module_field_id;
        $data['id'] = $module_field_id;
        
        $data['subject'] = $contract->subject;
        $data['module_templates_info'] = $this->contracts_model->get_result(array('module' => $module), 'tbl_templates');
        $data['page_content'] = $this->load->view('templates', $data, true);
        $data['subview'] = $this->load->view('details_layout', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }
    
    public function new_template($id, $module_field_id)
    {
        if (is_numeric($id)) {
            $data['template_info'] = $this->contracts_model->check_by(array('template_id' => $id), 'tbl_templates');
            $data['module'] = $data['template_info']->module;
            $data['module_field_id'] = $module_field_id;
        } else {
            $data['module'] = $id;
            $data['module_field_id'] = $module_field_id;
        }
        $data['modal_subview'] = $this->load->view('new_template', $data, false);
        $this->load->view('admin/_layout_modal', $data);
    }
    
    public function save_template($id = null)
    {
        $type = "error";
        $msg = lang('something_is_wrong');
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('', '');
        $this->form_validation->set_rules('template_name', lang('template_name'), 'trim|required');
        $this->form_validation->set_rules('template_content', lang('content'), 'trim|required');
        
        $module_field_id = $this->input->post('module_field_id');
        $data = [
            'module' => $this->input->post('module'),
            'template_name' => $this->input->post('template_name'),
            'template_content' => $this->input->post('template_content'),
            'added_by' => my_id(),
        ];
        
        if ($this->form_validation->run() !== false) {
            if ($id && is_numeric($id)) {
                $template = $this->contracts_model->check_by(array('template_id' => $id), 'tbl_templates');
                if (empty($template)) {
                    exit;
                }
            }
            $this->contracts_model->_table_name = 'tbl_templates';
            $this->contracts_model->_primary_key = 'template_id';
            
            if (!empty($id)) {
                $return_id = $this->contracts_model->save($data, $id);
            } else {
                $return_id = $this->contracts_model->save($data);
            }
            
            if (!empty($id)) {
                $id = $id;
                $action = 'activity_update_template';
                $msg = lang('update_template');
            } else {
                $id = $return_id;
                $action = 'activity_save_template';
                $msg = lang('save_template');
            }
            
            if ($return_id) {
                $activity = array(
                    'user' => $this->session->userdata('user_id'),
                    'module' => 'contracts',
                    'module_field_id' => $id,
                    'activity' => $action,
                    'icon' => 'fa-circle-o',
                    'value1' => $data['name']
                );
                $this->contracts_model->_table_name = 'tbl_activities';
                $this->contracts_model->_primary_key = 'activities_id';
                $this->contracts_model->save($activity);
                $type = 'success';
            }
        }
        
        if (!empty(validation_errors())) {
            $type = "error";
            $msg = '';
            foreach ($this->input->post() as $k => $v) {
                $msg .= form_error($k, '', '</br>');
            }
        }
        
        set_message($type, $msg);
        redirect('admin/' . $data["module"] . '/templates/' . $module_field_id);
    }
    
    public function ins()
    {
        $type = "error";
        $msg = lang('something_is_wrong');
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div>', '</div>');
        $this->form_validation->set_rules('template_id', lang('template_id'), 'trim|required');
        $this->form_validation->set_rules('module', lang('module'), 'trim|required');
        $this->form_validation->set_rules('module_field_id', lang('module_field_id'), 'trim|required');
        
        if ($this->form_validation->run() !== false) {
            $module_field_id = $this->input->post('module_field_id');
            $data = array();
            $data['contract'] = $contract = $this->contracts_model->get_by_id($module_field_id);
            if (empty($contract)) {
                show_404();
                exit;
            }
            
            if ($contract->signed == '1' || $contract->marked_as_signed == '1' || $contract->parent_contract_id != null) {
                $type = "error";
                $msg = 'signed_not_inserted';
            } else {
                $template_id = $this->input->post('template_id');
                
                if ($template_id && is_numeric($template_id)) {
                    $template = $this->contracts_model->check_by(array('template_id' => $template_id), 'tbl_templates');
                    if (!empty($template)) {
                        $udata = [
                            'description' => $template->template_content . '<br>' . $contract->description,
                        ];
                        $module = $this->input->post('module');
                        $this->contracts_model->_table_name = 'tbl_contracts';
                        $this->contracts_model->_primary_key = 'contract_id';
                        $return_id = $this->contracts_model->save($udata, $module_field_id);
                        
                        if (!empty($return_id)) {
                            $_POST = array();
                            $activity = array(
                                'user' => $this->session->userdata('user_id'),
                                'module' => $module,
                                'module_field_id' => $module_field_id,
                                'activity' => ('activity_insert_a_template'),
                                'value1' => $template->template_name
                            );
                            $this->contracts_model->_table_name = 'tbl_activities';
                            $this->contracts_model->_primary_key = 'activities_id';
                            $this->contracts_model->save($activity);
                            
                            $type = "success";
                            $msg = lang('template_inserted');
                        }
                    }
                }
            }
        }
        
        if (!empty(validation_errors())) {
            $type = "error";
            $msg = validation_errors();
        }
        $result = array(
            'status' => $type,
            'message' => $msg,
        );
        echo json_encode($result);
        exit();
    }
    
    public function delete_template($id)
    {
        if ($id && is_numeric($id)) {
            $template = $this->contracts_model->check_by(array('template_id' => $id), 'tbl_templates');
            if (empty($template)) {
                exit;
            }
        }
        $success = $this->db->delete('tbl_templates', array('template_id' => $id));
        if ($success) {
            $action = 'activity_delete_template';
            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'account',
                'module_field_id' => $id,
                'activity' => $action,
                'icon' => 'fa-circle-o',
                'value1' => $template->template_name
            );
            $this->contracts_model->_table_name = 'tbl_activities';
            $this->contracts_model->_primary_key = 'activities_id';
            $this->contracts_model->save($activity);
            
            $type = 'success';
            $msg = lang('template') . ' ' . lang('deleted');
            echo json_encode(array("status" => $type, 'message' => $msg));
            exit();
        } else {
            $type = 'error';
            $msg = lang('problem_deleting') . ' ' . lang('template');
            echo json_encode(array("status" => $type, 'message' => $msg));
        }
    }
    
    public function emails($id)
    {
        $data = array();
        $data['title'] = lang('contract_details');;
        $data['active'] = 'emails';
        $data['id'] = $id;
        
        $data['contract'] = $contract = $this->contracts_model->get_by_id($id);
        if (empty($contract)) {
            show_404();
        }
        $data['subject'] = $contract->subject;
        
        $data['page_content'] = $this->load->view('emails', $data, true);
        $data['subview'] = $this->load->view('details_layout', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }
    
    public function save_contract($id = null)
    {
        if ($this->input->post()) {
            $this->contracts_model->_table_name = 'tbl_contracts';
            $this->contracts_model->_primary_key = 'contract_id';
            
            $data = [
                'client' => $this->input->post('client_id'),
                'project_id' => $this->input->post('project_id') ? $this->input->post('project_id') : 0,
                'subject' => $this->input->post('subject'),
                'contract_value' => $this->input->post('contract_value'),
                'contract_type' => $this->input->post('contract_type'),
                'start_date' => date('Y-m-d', strtotime($this->input->post('start_date', true))),
                'end_date' => $this->input->post('end_date'),
                'description' => nl2br($this->input->post('description')),
                'trash' => 0,
                'visible_to_client' => $this->input->post('visible_to_client'),
                'date_added' => date('Y-m-d H:i:s'),
                'added_from' => my_id(),
            ];
            
            if ($id && is_numeric($id)) {
                $contract = $this->contracts_model->check_by(array('contract_id' => $id), 'tbl_contracts');
                if (empty($contract)) {
                    exit;
                }
            }
            
            if (!empty($id)) {
                $return_id = $this->contracts_model->save($data, $id);
            } else {
                $return_id = $this->contracts_model->save($data);
            }
            
            if (!empty($id)) {
                $id = $id;
                $action = 'activity_update_contract';
                $msg = lang('update_contract');
            } else {
                $id = $return_id;
                $action = 'activity_save_contract';
                $msg = lang('save_contract');
            }
            
            if ($return_id) {
                $activity = array(
                    'user' => $this->session->userdata('user_id'),
                    'module' => 'contracts',
                    'module_field_id' => $id,
                    'activity' => $action,
                    'icon' => 'fa-circle-o',
                    'value1' => $data['name']
                );
                $this->contracts_model->_table_name = 'tbl_activities';
                $this->contracts_model->_primary_key = 'activities_id';
                $this->contracts_model->save($activity);
                
                $type = 'success';
                set_message($type, $msg);
                redirect('admin/contracts');
            }
        }
    }
    
    public function send_email($id)
    {
        $data = array();
        $data['title'] = "Contract PDF";
        $data['contract'] = $contract = $this->contracts_model->get_by_id($id);
        if (empty($contract)) {
            show_404();
            exit;
        }
        $this->load->helper('dompdf');
        $data['subview'] = $this->load->view('contract_letter', $data, true);
        $viewfile = $this->load->view('frontend/_layout_print', $data, true);
        $client = $this->contracts_model->check_by(array('client_id' => $contract->client), 'tbl_client');
        $recipient = $client->email;
        $recipient = 'janeaustine0@gmail.com';
        
        $data['message'] = $viewfile;
        $message = $this->load->view('email_template', $data, true);
        $subject = 'Letter of Contract as to -' . $contract->subject;
        $params = array(
            'recipient' => $recipient,
            'subject' => $subject,
            'message' => $message
        );
        $this->contracts_model->send_email($params);
        
        // Log Activity
        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'contracts',
            'module_field_id' => $id,
            'activity' => ('activity_contract_emailed'),
            'icon' => 'fa fa-circle-o',
            'link' => 'admin/contracts/details/' . $id,
            'value1' => $contract->subject,
        );
        $this->contracts_model->_table_name = 'tbl_activities';
        $this->contracts_model->_primary_key = 'activities_id';
        $this->contracts_model->save($activity);
        // messages for user
        $type = "success";
        $imessage = lang('contract_emaile');
        set_message($type, $imessage);
        redirect('admin/contracts/details/' . $id);
    }
    
    // delete_contract_history
    public function delete_renewal_history($id, $contract_id)
    {
        $is_last = total_rows('tbl_contract_renewals', array('contract_id' => $contract_id));
        $historyInfo = $this->contracts_model->select_data('tbl_contract_renewals', '*', null, array('contract_renewal_id' => $id), ['tbl_contracts' => 'tbl_contract_renewals.contract_id = tbl_contracts.contract_id'], 'row');
        
        if ($is_last == 1) {
            $cdata = [
                'start_date' => $historyInfo->old_start_date,
                'contract_value' => $historyInfo->old_value,
                'isexpirynotified' => $historyInfo->is_on_old_expiry_notified,
            ];
            if ($historyInfo->old_end_date != '0000-00-00') {
                $cdata['end_date'] = $historyInfo->old_end_date;
            }
            $this->contracts_model->_table_name = 'tbl_contracts';
            $this->contracts_model->_primary_key = 'contract_id';
            $this->contracts_model->save($cdata, $contract_id);
        }
        $activity = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'contracts',
            'module_field_id' => $id,
            'activity' => ('activity_delete_contract_history'),
            'icon' => 'fa-circle-o',
            'value1' => $historyInfo->subject
        );
        $this->contracts_model->_table_name = 'tbl_activities';
        $this->contracts_model->_primary_key = 'activities_id';
        $this->contracts_model->save($activity);
        
        
        $this->contracts_model->_table_name = 'tbl_contract_renewals';
        $this->contracts_model->_primary_key = 'contract_renewal_id';
        $this->contracts_model->delete($id);
        $type = "success";
        $message = lang('contract_renewal_deleted');
        echo json_encode(array("status" => $type, "message" => $message));
        exit();
    }
    
    
    public function delete($id)
    {
        $contract = $this->contracts_model->get_by_id($id);
        if (empty($contract)) {
            redirect('admin/contracts');
        }
        
        if ($contract->signed == '1' || $contract->marked_as_signed == '1') {
            $type = "error";
            $msg = lang('signed_not_deleted');
        } elseif (!empty($this->contracts_model->check_by(array('parent_contract_id' => $id), 'tbl_contracts'))) {
            $type = "error";
            $msg = lang('archived_not_deleted');
        }
        // delete tbl_contracts
        $this->contracts_model->_table_name = 'tbl_contracts';
        $this->contracts_model->_primary_key = 'contract_id';
        
        if ($this->contracts_model->delete($id)) {
            $type = 'success';
            $msg = lang('contract') . ' ' . lang('deleted');
            
            $action = 'activity_delete_contract';
            $activity = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'contracts',
                'module_field_id' => $id,
                'activity' => $action,
                'icon' => 'fa-circle-o',
                'value1' => $contract->subject
            );
            
            $this->contracts_model->_table_name = 'tbl_activities';
            $this->contracts_model->_primary_key = 'activities_id';
            $this->contracts_model->save($activity);
            
            
        }
        echo json_encode(array("status" => $type, 'message' => $msg));
        exit();
    }
}
