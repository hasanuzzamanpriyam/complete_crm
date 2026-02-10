<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Complaints extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('complaints_model');
        $this->load->model('tasks_model');
        $this->module_name = 'complaints';
    }
    
    public function complaints_state_report()
    {
        $data = array();
        $pathonor_jonno['complaints_state_report_div'] = $this->load->view("complaints_state_report", $data, true);
        echo json_encode($pathonor_jonno);
        exit;
    }
    
    public function index()
    {
        $data['page'] = lang('complaints');
        $data['title'] = lang('complaints');
        $data['active'] = 1;
        if ($this->session->userdata('user_type') == 1 || !empty($is_department_head)) {
            $data['all_client'] = $this->complaints_model->get_result(array(), 'tbl_client');
            $data['all_complaints_types'] = $this->complaints_model->get_result(array(), 'tbl_complaints_types');
        }
        $data['page_content'] = $this->load->view('manage_complaints', $data, TRUE);
        $data['subview'] = $this->load->view('complaints_subview', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }
    
    public function complaintsList($filterBy = null, $search_by = null)
    {
        if ($this->input->is_ajax_request()) {
            $this->complaints_model->fetch_complaints($filterBy, $search_by);
        } else {
            redirect('admin/dashboard');
        }
    }
    
    public function details($id)
    {
        $data['page'] = lang('complaint_details');
        $data['title'] = lang('complaint_details');
        $data['active'] = 'details';
        $data['id'] = $id;
        
        if ($id && is_numeric($id)) {
            $data['tickets_info'] = $this->complaints_model->get_by_id($id);
            if (!empty($data['tickets_info'])) {
                $data['page_content'] = $this->load->view('details', $data, TRUE);
                $data['subview'] = $this->load->view('details_layout', $data, TRUE);
                $this->load->view('admin/_layout_main', $data);
            }
        }
    }
    
    public function save_complaint_reply($id)
    {
        $date = date('Y-m-d H:i:s');
        $status = $this->uri->segment(6);
        if (!empty($status)) {
            $this->complaints_model->set_action(array('tickets_id' => $id), array('status' => $status), 'tbl_complaints');
        }
        $this->complaints_model->set_action(array('tickets_id' => $id), array('last_reply' => $date), 'tbl_complaints');
        
        $rdata['body'] = $this->input->post('body', TRUE);
        $upload_file = array();
        $files = $this->input->post("files", true);
        $target_path = getcwd() . "/uploads/";
        //process the fiiles which has been uploaded by dropzone
        if (!empty($files) && is_array($files)) {
            foreach ($files as $key => $file) {
                if (!empty($file)) {
                    $file_name = $this->input->post('file_name_' . $file, true);
                    $new_file_name = move_temp_file($file_name, $target_path);
                    $file_ext = explode(".", $new_file_name);
                    $is_image = check_image_extension($new_file_name);
                    $size = $this->input->post('file_size_' . $file, true) / 1000;
                    if ($new_file_name) {
                        $up_data = array(
                            "fileName" => $new_file_name,
                            "path" => "uploads/" . $new_file_name,
                            "fullPath" => getcwd() . "/uploads/" . $new_file_name,
                            "ext" => '.' . end($file_ext),
                            "size" => round($size, 2),
                            "is_image" => $is_image,
                        );
                        array_push($upload_file, $up_data);
                    }
                }
            }
        }
        if (!empty($upload_file)) {
            $rdata['attachment'] = json_encode($upload_file);
        }
        $rdata['tickets_id'] = $id;
        $rdata['replierid'] = $this->session->userdata('user_id');
        
        
        $this->complaints_model->_table_name = 'tbl_complaints_replies';
        $this->complaints_model->_primary_key = 'tickets_replies_id';
        $tickets_replies_id = $this->complaints_model->save($rdata);
        if (!empty($tickets_replies_id)) {
            //check this tickets already answer or not
            $ticket_info = $this->db->where(array('tickets_id' => $id, 'status' => 'answered'))->get('tbl_complaints')->row();
            if (empty($ticket_info)) {
                $this->complaints_model->set_action(array('tickets_id' => $id), array('status' => 'answered'), 'tbl_complaints');
            }
            // save into activities
            $activities = array(
                'user' => $this->session->userdata('user_id'),
                'module' => $this->module_name,
                'module_field_id' => $id,
                'activity' => 'activity_reply_complaints',
                'icon' => 'fa-ticket',
                'link' => 'admin/complaints/complaint_details/' . $id,
                'value1' => $rdata['body'],
            );
            // Update into tbl_project
            $this->complaints_model->_table_name = "tbl_activities"; //table name
            $this->complaints_model->_primary_key = "activities_id";
            $this->complaints_model->save($activities);
            $response_data = "";
            $view_data['ticket_replies'] = $this->db->where(array('tbl_complaints_replies' => $tickets_replies_id))->order_by('time', 'DESC')->get('tbl_complaints_replies')->result();
            $response_data = $this->load->view("complaint_reply", $view_data, true);
            echo json_encode(array("status" => 'success', "data" => $response_data, 'message' => lang('complaint_reply_saved')));
            exit();
        } else {
            echo json_encode(array("status" => 'error', 'message' => lang('error_occurred')));
            exit();
        }
    }
    
    
    function save_comments()
    {
        $data['module'] = $this->input->post('module', TRUE);
        $data['module_field_id'] = $this->input->post('module_field_id', TRUE);
        $data['comment'] = $this->input->post('comment', TRUE);
        
        $files = $this->input->post("files", true);
        $target_path = getcwd() . "/uploads/";
        //process the fiiles which has been uploaded by dropzone
        if (!empty($files) && is_array($files)) {
            foreach ($files as $key => $file) {
                if (!empty($file)) {
                    $file_name = $this->input->post('file_name_' . $file, true);
                    $new_file_name = move_temp_file($file_name, $target_path);
                    $file_ext = explode(".", $new_file_name);
                    $is_image = check_image_extension($new_file_name);
                    $size = $this->input->post('file_size_' . $file, true) / 1000;
                    if ($new_file_name) {
                        $up_data[] = array(
                            "fileName" => $new_file_name,
                            "path" => "uploads/" . $new_file_name,
                            "fullPath" => getcwd() . "/uploads/" . $new_file_name,
                            "ext" => '.' . end($file_ext),
                            "size" => round($size, 2),
                            "is_image" => $is_image,
                        );
                        $success = true;
                    } else {
                        $success = false;
                    }
                }
            }
        }
        //process the files which has been submitted manually
        if ($_FILES) {
            $files = $_FILES['manualFiles'];
            if ($files && count($files) > 0) {
                foreach ($files["tmp_name"] as $key => $file) {
                    $temp_file = $file;
                    $file_name = $files["name"][$key];
                    $file_size = $files["size"][$key];
                    $new_file_name = move_temp_file($file_name, $target_path, "", $temp_file);
                    if ($new_file_name) {
                        $file_ext = explode(".", $new_file_name);
                        $is_image = check_image_extension($new_file_name);
                        $up_data[] = array(
                            "fileName" => $new_file_name,
                            "path" => "uploads/" . $new_file_name,
                            "fullPath" => getcwd() . "/uploads/" . $new_file_name,
                            "ext" => '.' . end($file_ext),
                            "size" => round($file_size, 2),
                            "is_image" => $is_image,
                        );
                    }
                }
            }
        }
        if (!empty($up_data)) {
            $data['comments_attachment'] = json_encode($up_data);
        }
        
        $data['user_id'] = $this->session->userdata('user_id');
        
        //save data into table.
        $this->items_model->_table_name = "tbl_task_comment"; // table name
        $this->items_model->_primary_key = "task_comment_id"; // $id
        $comment_id = $this->items_model->save($data);
        
        // save into activities
        $activities = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'leads',
            'module_field_id' => $data['leads_id'],
            'activity' => 'activity_new_leads_comment',
            'icon' => 'fa-rocket',
            'link' => 'admin/leads/leads_details/' . $data['leads_id'] . '/4',
            'value1' => $data['comment'],
        );
        
        // Update into tbl_project
        $this->items_model->_table_name = "tbl_activities"; //table name
        $this->items_model->_primary_key = "activities_id";
        $this->items_model->save($activities);
        
        if (!empty($comment_id)) {
            $response_data = "";
            $view_data['comment_details'] = $this->db->where(array('task_comment_id' => $comment_id))->order_by('comment_datetime', 'DESC')->get('tbl_task_comment')->result();
            $response_data = $this->load->view("admin/leads/comments_list", $view_data, true);
            echo json_encode(array("status" => 'success', "data" => $response_data, 'message' => lang('leads_comment_save')));
            exit();
        } else {
            echo json_encode(array("status" => 'error', 'message' => lang('error_occurred')));
            exit();
        }
    }
    
    public function save_comments_reply($tickets_replies_id)
    {
        $rdata['tickets_id'] = $this->input->post('tickets_id', TRUE);
        $rdata['body'] = $this->input->post('reply_comments', TRUE);
        $rdata['ticket_reply_id'] = $tickets_replies_id;
        
        $rdata['replierid'] = $this->session->userdata('user_id');
        
        $this->complaints_model->_table_name = 'tbl_complaints_replies';
        $this->complaints_model->_primary_key = 'tickets_replies_id';
        $tickets_replies_id = $this->complaints_model->save($rdata);
        if (!empty($tickets_replies_id)) {
            // save into activities
            $activities = array(
                'user' => $this->session->userdata('user_id'),
                'module' => $this->module_name,
                'module_field_id' => $rdata['tickets_id'],
                'activity' => 'activity_reply_tickets',
                'icon' => 'fa-ticket',
                'link' => 'admin/complaints/complaint_details/' . $rdata['tickets_id'],
                'value1' => $rdata['body'],
            );
            // Update into tbl_project
            $this->complaints_model->_table_name = "tbl_activities"; //table name
            $this->complaints_model->_primary_key = "activities_id";
            $this->complaints_model->save($activities);
            
            $response_data = "";
            $view_data['comment_reply_details'] = $this->db->where(array('tickets_replies_id' => $tickets_replies_id))->order_by('time', 'ASC')->get('tbl_complaints_replies')->result();
            $response_data = $this->load->view("comments_reply", $view_data, true);
            echo json_encode(array("status" => 'success', "data" => $response_data, 'message' => lang('complaint_reply_saved')));
            exit();
        } else {
            echo json_encode(array("status" => 'error', 'message' => lang('error_occurred')));
            exit();
        }
    }
    
    
    public
    function change_status($id, $status)
    {
        if ($id && is_numeric($id) && $status) {
            $data['tickets_info'] = $this->complaints_model->check_by(array('tickets_id' => $id), 'tbl_complaints');
            if (!empty($data['tickets_info'])) {
                $data['id'] = $id;
                $data['status'] = $status;
                $data['modal_subview'] = $this->load->view('_modal_change_status', $data, FALSE);
                $this->load->view('admin/_layout_modal', $data);
            }
        } else {
            set_message('error', lang('there_in_no_value'));
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/complaints');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }
    
    
    public function changed_complaint_status($id, $status)
    {
        if ($id && is_numeric($id) && $status) {
            $data['tickets_info'] = $this->complaints_model->check_by(array('tickets_id' => $id), 'tbl_complaints');
            if (!empty($data['tickets_info'])) {
                $date = date('Y-m-d H:i:s');
                if (!empty($status)) {
                    $this->complaints_model->set_action(array('tickets_id' => $id), array('status' => $status), 'tbl_complaints');
                }
                $this->complaints_model->set_action(array('tickets_id' => $id), array('last_reply' => $date), 'tbl_complaints');
                
                $rdata['body'] = $this->input->post('body', TRUE);
                
                $rdata['tickets_id'] = $id;
                $rdata['replierid'] = $this->session->userdata('user_id');
                
                
                $this->complaints_model->_table_name = 'tbl_complaints_replies';
                $this->complaints_model->_primary_key = 'tickets_replies_id';
                $this->complaints_model->save($rdata);
                // save into activities
                $activities = array(
                    'user' => $this->session->userdata('user_id'),
                    'module' => $this->module_name,
                    'module_field_id' => $id,
                    'activity' => 'activity_reply_complaint',
                    'icon' => 'fa-ticket',
                    'link' => 'admin/complaints/complaint_details/' . $id,
                    'value1' => $rdata['body'],
                );
                // Update into tbl_project
                $this->complaints_model->_table_name = "tbl_activities"; //table name
                $this->complaints_model->_primary_key = "activities_id";
                $this->complaints_model->save($activities);
                if (empty($_SERVER['HTTP_REFERER'])) {
                    redirect('admin/complaints');
                } else {
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
        }
    }
    
    public function complaint_types()
    {
        $data['title'] = lang('complaint_types');
        $data['active'] = 3;
        $data['module'] = $this->module_name;
        $data['contracts_types'] = get_result('tbl_complaints_types');
        $data['page_content'] = $this->load->view('complaint_types', $data, TRUE);
        $data['subview'] = $this->load->view('complaints_subview', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }
    
    public function new_complaint_type($id = NULL)
    {
        $data['id'] = $id;
        $data['module'] = $this->module_name;
        $data['title'] = lang('new_complaint_type');
        $data['active'] = 3;
        $data['type_name'] = '';
        if ($id && is_numeric($id)) {
            $data['contract_type'] = $this->complaints_model->check_by(array('id' => $id), 'tbl_complaints_types');
            if (empty($data['contract_type'])) {
                show_404();
                exit;
            }
            $data['type_name'] = $data['contract_type']->name;
        }
        $data['subview'] = $this->load->view('new_complaint_type', $data);
        $this->load->view('admin/_layout_modal', $data);
    }
    
    public function delete_complaint_type($id)
    {
        $type = 'error';
        $msg = lang('problem_deleting') . ' ' . lang('complaint_type');
        
        if ($id && is_numeric($id)) {
            $contract_type = $this->complaints_model->check_by(array('id' => $id), 'tbl_complaints_types');
            if (empty($contract_type)) {
                $msg = lang('problem_deleting') . ' ' . lang('complaint_type');
            } else {
                
                $contract = $this->complaints_model->check_by(array('ticket_sub_type' => $id), 'tbl_complaints');
                
                if (!empty($contract)) {
                    
                    $msg = lang('type_used_not_deleted');
                } else {
                    
                    $success = $this->db->delete('tbl_complaints_types', array('id' => $id));
                    if ($success) {
                        $action = 'activity_delete_complaint_type';
                        $activity = array(
                            'user' => $this->session->userdata('user_id'),
                            'module' => $this->module_name,
                            'module_field_id' => $id,
                            'activity' => $action,
                            'icon' => 'fa-circle-o',
                            'value1' => $contract_type->name
                        );
                        $this->complaints_model->_table_name = 'tbl_activities';
                        $this->complaints_model->_primary_key = 'activities_id';
                        $this->complaints_model->save($activity);
                        
                        $type = 'success';
                        $msg = lang('complaint_type') . ' ' . lang('deleted');
                    }
                }
            }
        }
        echo json_encode(array("status" => $type, 'message' => $msg));
        exit();
    }
    
    public function save_new_complaint_type($id = null)
    {
        $type = "error";
        $msg = lang('something_is_wrong');
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div>', '</div>');
        $this->form_validation->set_rules('name', lang('name'), 'trim|required');
        
        if ($this->form_validation->run() == true) {
            
            $ins['name'] = $this->input->post('name');
            $this->complaints_model->_table_name = 'tbl_complaints_types';
            $this->complaints_model->_primary_key = 'id';
            if (!empty($id)) {
                $return_id = $this->complaints_model->save($ins, $id);
            } else {
                $return_id = $id = $this->complaints_model->save($ins);
            }
            
            if (!empty($return_id)) {
                $_POST = array();
                $activity = array(
                    'user' => $this->session->userdata('user_id'),
                    'module' => $this->module_name,
                    'module_field_id' => $id,
                    'activity' => ('activity_saved_a_complaint_type'),
                    'value1' => $ins['name']
                );
                $this->items_model->_table_name = 'tbl_activities';
                $this->items_model->_primary_key = 'activities_id';
                $this->items_model->save($activity);
                
                $type = "success";
                $msg = lang('complaint_type_saved');
                
                $result = array(
                    'id' => $id,
                    'complaint_type' => $ins['name'],
                    'status' => $type,
                    'message' => $msg,
                );
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
    
    public function new_complaint($id = null)
    {
        $data = array();
        $data['title'] = lang('new_complaint');;
        $data['active'] = 2;
        $data['dropzone'] = true;
        $data['lodged_date'] = '';
        $data['due_date'] = '';
        if ($id && is_numeric($id)) {
            $data['tickets_info'] = $this->complaints_model->get_by_id($id);
            if (empty($data['tickets_info'])) {
                show_404();
                exit;
            } else {
                $data['lodged_date'] = $data['tickets_info']->lodged_date;
                $data['due_date'] = $data['tickets_info']->lodged_date;
            }
        }
        
        $data['all_client'] = $this->complaints_model->get_permission('tbl_client');
        
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div>', '</div>');
        $this->form_validation->set_rules('ticket_code', lang('complaint') . ' ' . lang('code'), 'trim|required');
        $this->form_validation->set_rules('subject', lang('description '), 'trim|required');
        $this->form_validation->set_rules('client', lang('client'), 'trim|required');
        
        if ($this->form_validation->run() == true) {
            $this->save_complaint($id);
        }
        
        if (!empty(validation_errors())) {
            $type = "error";
            $msg = '';
            foreach ($this->input->post() as $k => $v) {
                $msg .= form_error($k, '', '</br>');
            }
        }
        
        $this->items_model->_table_name = 'tbl_client';
        $this->items_model->_order_by = 'client_id';
        $data['all_client'] = $this->items_model->get();
        $data['data'] = $data;
        $data['page_content'] = $this->load->view('new_complaint', $data, TRUE);
        $data['subview'] = $this->load->view('complaints_subview', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }
    
    public function save_complaint($id = NULL)
    {
        $data = $this->complaints_model->array_from_post(array('ticket_code', 'subject', 'ticket_sub_type', 'client', 'body'));
        $data['reporter'] = my_id();
        if (empty($id)) {
            $status = $this->input->post('status', true);
            if (!empty($status)) {
                $data['status'] = $status;
                if ($status == 'index') {
                    $data['status'] = 'open';
                }
            } else {
                $data['status'] = 'open';
            }
        }
        
        $data['lodged_date'] = date('Y-m-d', strtotime($this->input->post('lodged_date', TRUE)));
        $data['due_date'] = date('Y-m-d', strtotime($this->input->post('due_date', TRUE)));
        
        $created = can_action(6, 'created');
        $edited = can_action(6, 'edited');
        
        if (!empty($created) || !empty($edited) && !empty($id)) {
            
            
            $upload_file = array();
            
            $files = $this->input->post("files", true);
            $target_path = getcwd() . "/uploads/";
            //process the fiiles which has been uploaded by dropzone
            if (!empty($files) && is_array($files)) {
                foreach ($files as $key => $file) {
                    if (!empty($file)) {
                        $file_name = $this->input->post('file_name_' . $file, true);
                        $new_file_name = move_temp_file($file_name, $target_path);
                        $file_ext = explode(".", $new_file_name);
                        $is_image = check_image_extension($new_file_name);
                        $size = $this->input->post('file_size_' . $file, true) / 1000;
                        if ($new_file_name) {
                            $up_data = array(
                                "fileName" => $new_file_name,
                                "path" => "uploads/" . $new_file_name,
                                "fullPath" => getcwd() . "/uploads/" . $new_file_name,
                                "ext" => '.' . end($file_ext),
                                "size" => round($size, 2),
                                "is_image" => $is_image,
                            );
                            array_push($upload_file, $up_data);
                        }
                    }
                }
            }
            
            $fileName = $this->input->post('fileName', true);
            $path = $this->input->post('path', true);
            $fullPath = $this->input->post('fullPath', true);
            $size = $this->input->post('size', true);
            $is_image = $this->input->post('is_image', true);
            
            if (!empty($fileName)) {
                foreach ($fileName as $key => $name) {
                    $old['fileName'] = $name;
                    $old['path'] = $path[$key];
                    $old['fullPath'] = $fullPath[$key];
                    $old['size'] = $size[$key];
                    $old['is_image'] = $is_image[$key];
                    
                    array_push($upload_file, $old);
                }
            }
            if (!empty($upload_file)) {
                $data['upload_file'] = json_encode($upload_file);
            } else {
                $data['upload_file'] = null;
            }
            
            
            $this->complaints_model->_table_name = 'tbl_complaints';
            $this->complaints_model->_primary_key = 'tickets_id';
            if (!empty($id)) {
                $this->complaints_model->save($data, $id);
            } else {
                $id = $this->complaints_model->save($data, $id);
            }
            
            
            // save into activities
            $activities = array(
                'user' => $this->session->userdata('user_id'),
                'module' => $this->module_name,
                'module_field_id' => $id,
                'activity' => 'activity_save_complaint',
                'icon' => 'fa-ticket',
                'link' => 'admin/complaints/new_complaint/' . $id,
                'value1' => $data['ticket_code'],
            );
            // Update into tbl_project
            $this->complaints_model->_table_name = "tbl_activities"; //table name
            $this->complaints_model->_primary_key = "activities_id";
            $this->complaints_model->save($activities);
            
            // messages for user
            $type = "success";
            $message = lang('complaint_saved');
            set_message($type, $message);
            if (!empty($data['project_id']) && is_numeric($data['project_id'])) {
                redirect('admin/projects/project_details/' . $data['project_id']);
            } else {
                redirect('admin/complaints/details/' . $id);
            }
        } else {
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/complaints');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }
    
    
    public function new_attachment($id)
    {
        $data['dropzone'] = true;
        $data['module'] = $this->module_name;
        $data['module_field_id'] = $this->items_model->check_by(array('tickets_id' => $id), 'tbl_complaints')->tickets_id;
        $data['modal_subview'] = $this->load->view('new_attachment', $data, FALSE);
        $this->load->view('admin/_layout_modal', $data);
    }
    
    public
    function save_attachment($attachments_id = NULL)
    {
        $data = $this->items_model->array_from_post(array('title', 'description', 'module', 'module_field_id'));
        $data['user_id'] = $this->session->userdata('user_id');
        
        // save and update into tbl_files
        $this->items_model->_table_name = "tbl_attachments"; //table name
        $this->items_model->_primary_key = "attachments_id";
        if (!empty($attachments_id)) {
            $id = $attachments_id;
            $this->items_model->save($data, $id);
            $msg = lang('leads_file_updated');
        } else {
            $id = $this->items_model->save($data);
            $msg = lang('leads_file_added');
        }
        $files = $this->input->post("files", true);
        
        $target_path = getcwd() . "/uploads/";
        //process the fiiles which has been uploaded by dropzone
        if (!empty($files) && is_array($files)) {
            foreach ($files as $key => $file) {
                if (!empty($file)) {
                    $file_name = $this->input->post('file_name_' . $file, true);
                    $new_file_name = move_temp_file($file_name, $target_path);
                    $file_ext = explode(".", $new_file_name);
                    $is_image = check_image_extension($new_file_name);
                    
                    if ($new_file_name) {
                        $up_data = array(
                            "files" => "uploads/" . $new_file_name,
                            "uploaded_path" => getcwd() . "/uploads/" . $new_file_name,
                            "file_name" => $new_file_name,
                            "size" => $this->input->post('file_size_' . $file, true),
                            "ext" => end($file_ext),
                            "is_image" => $is_image,
                            "image_width" => 0,
                            "image_height" => 0,
                            "attachments_id" => $id
                        );
                        $this->items_model->_table_name = "tbl_attachments_files"; // table name
                        $this->items_model->_primary_key = "uploaded_files_id"; // $id
                        $uploaded_files_id = $this->items_model->save($up_data);
                        
                        // saved into comments
                        $comment = $this->input->post('comment_' . $file, true);
                        if (!empty($comment)) {
                            $u_cdata = array(
                                "comment" => $comment,
                                "module" => $data['module'],
                                "module_field_id" => $data['module_field_id'],
                                "user_id" => $this->session->userdata('user_id'),
                                "uploaded_files_id" => $uploaded_files_id,
                            );
                            $this->items_model->_table_name = "tbl_task_comment"; // table name
                            $this->items_model->_primary_key = "task_comment_id"; // $id
                            $this->items_model->save($u_cdata);
                        }
                        $success = true;
                    } else {
                        $success = false;
                    }
                }
            }
        }
        //process the files which has been submitted manually
        if ($_FILES) {
            $files = $_FILES['manualFiles'];
            if ($files && count($files) > 0) {
                $comment = $this->input->post('comment', true);
                foreach ($files["tmp_name"] as $key => $file) {
                    $temp_file = $file;
                    $file_name = $files["name"][$key];
                    $file_size = $files["size"][$key];
                    $new_file_name = move_temp_file($file_name, $target_path, "", $temp_file);
                    if ($new_file_name) {
                        $file_ext = explode(".", $new_file_name);
                        $is_image = check_image_extension($new_file_name);
                        $up_data = array(
                            "files" => "uploads/" . $new_file_name,
                            "uploaded_path" => getcwd() . "/uploads/" . $new_file_name,
                            "file_name" => $new_file_name,
                            "size" => $file_size,
                            "ext" => end($file_ext),
                            "is_image" => $is_image,
                            "image_width" => 0,
                            "image_height" => 0,
                            "attachments_id" => $id
                        );
                        $this->items_model->_table_name = "tbl_attachments_files"; // table name
                        $this->items_model->_primary_key = "uploaded_files_id"; // $id
                        $uploaded_files_id = $this->items_model->save($up_data);
                        
                        // saved into comments
                        if (!empty($comment[$key])) {
                            $u_cdata = array(
                                "comment" => $comment[$key],
                                "module" => $data['module'],
                                "module_field_id" => $data['module_field_id'],
                                "user_id" => $this->session->userdata('user_id'),
                                "uploaded_files_id" => $uploaded_files_id,
                            );
                            $this->items_model->_table_name = "tbl_task_comment"; // table name
                            $this->items_model->_primary_key = "task_comment_id"; // $id
                            $this->items_model->save($u_cdata);
                        }
                    }
                }
            }
        }
        $url = 'admin/' . $data['module'] . '/attachments/' . $data['module_field_id'];
        if ($success) {
            // save into activities
            $activities = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'leads',
                'module_field_id' => $data['leads_id'],
                'activity' => 'activity_new_' . $data['module'] . '_attachment',
                'icon' => 'fa-rocket',
                'link' => $url,
                'value1' => $data['title'],
            );
            // Update into tbl_project
            $this->items_model->_table_name = "tbl_activities"; //table name
            $this->items_model->_primary_key = "activities_id";
            $this->items_model->save($activities);
            if (!empty($notifiedUsers)) {
                foreach ($notifiedUsers as $users) {
                    if ($users != $this->session->userdata('user_id')) {
                        add_notification(array(
                            'to_user_id' => $users,
                            'from_user_id' => true,
                            'description' => 'not_uploaded_attachment',
                            'link' => $url,
                            'value' => lang($data['module']) . ' ' . $data['title'],
                        ));
                    }
                }
                show_notification($notifiedUsers);
            }
            // messages for user
            $type = "success";
            $message = $msg;
        }
        set_message($type, $message);
        redirect($url);
    }
    
    public function attachments($id, $module = 'complaints')
    {
        $data = array();
        $data['title'] = lang('complaints') . ' ' . lang('attachments');
        $data['active'] = 'attachments';
        $data['id'] = $id;
        $data['module_name'] = $this->module_name;
        $data['contract'] = $contract = $this->complaints_model->get_by_id($id);
        if (empty($contract)) {
            show_404();
        }
        $data['subject'] = $contract->subject;
        $data['files_info'] = $this->common_model->get_attach_file($module, $id);
        $data['page_content'] = $this->load->view('admin/common/attachments', $data, TRUE);
        $data['subview'] = $this->load->view('details_layout', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }
    
    
    public function comments($id)
    {
        $data = array();
        $data['title'] = lang('complaints') . ' ' . lang('comments');
        $data['active'] = 'comments';
        $data['module_name'] = $this->module_name;
        $data['id'] = $id;
        $data['dropzone'] = true;
        $data['contract'] = $contract = $this->complaints_model->get_by_id($id);
        $data['module'] = $this->module_name;
        $data['module_field_id'] = $contract->tickets_id;
        if (empty($contract)) {
            show_404();
        }
        $data['comment_details'] = get_result('tbl_task_comment', array('module_field_id' => $id, 'module' => $this->module_name));
        $data['subject'] = $contract->subject;
        $data['page_content'] = $this->load->view('comments', $data, TRUE);
        $data['subview'] = $this->load->view('details_layout', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }
    
    
    public function tasks($id, $module = 'complaints')
    {
        $data = array();
        $data['title'] = lang('complaints') . ' ' . lang('tasks');
        $data['active'] = 'tasks';
        $data['module_name'] = $module = $this->module_name;
        $data['contract'] = $contract = $this->complaints_model->get_by_id($id);
        if (empty($contract)) {
            show_404();
        }
        $data['subject'] = $contract->subject;
        $data['id'] = $id;
        $data['module_task_info'] = get_result('tbl_task', array('module' => $module, 'module_field_id' => $id));
        $data['page_content'] = $this->load->view('tasks', $data, TRUE);
        $data['subview'] = $this->load->view('details_layout', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }
    
    public function new_task($id = 'complaints', $module_field_id = null)
    {
        $data = array();
        $data['title'] = lang('new_task');
        $data['active'] = 'tasks';
        if (is_numeric($id)) {
            $data['task_info'] = $this->complaints_model->check_by(array('task_id' => $id), 'tbl_task');
            $data['module'] = $data['task_info']->module;
            $data['module_field_id'] = $data['task_info']->module_field_id;
            $data['contract'] = $contract = $this->complaints_model->get_by_id($data['module_field_id']);
            if (empty($contract)) {
                show_404();
            }
        } else {
            $data['module'] = $id;
            $data['module_field_id'] = $module_field_id;
        }
        $data['id'] = $data['module_field_id'];
        $data['assign_user'] = $this->complaints_model->allowed_user('54');
        $data['subject'] = $contract->subject;
        $data['page_content'] = $this->load->view('new_task', $data, TRUE);
        $data['subview'] = $this->load->view('details_layout', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }
    
    public
    function save_task($id = NULL)
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
            $data['project_id'] = NULL;
            $data['milestones_id'] = NULL;
            $data['goal_tracking_id'] = NULL;
            $data['bug_id'] = NULL;
            $data['leads_id'] = NULL;
            $data['opportunities_id'] = NULL;
            $data['sub_task_id'] = NULL;
            $data['transactions_id'] = NULL;
            
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
                    $this->notify_assigned_tasks($assigned_to['assigned_to'], $id, TRUE);
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
                redirect('admin/tasks/view_task_details/' . $id);
            }
        } else {
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    
    function notify_assigned_tasks($users, $task_id, $update = NULL)
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
        $Link = str_replace("{TASK_URL}", base_url() . 'admin/tasks/view_task_details/' . $tasks_info->task_id, $assigned_by);
        $message = str_replace("{SITE_NAME}", config_item('company_name'), $Link);
        
        $data['message'] = $message;
        $message = $this->load->view('email_template', $data, TRUE);
        
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
                    'link' => 'admin/tasks/view_task_details/' . $task_id,
                    'value' => lang('task') . ' ' . $tasks_info->task_name,
                ));
            }
        }
        show_notification($users);
    }
    
    public function notes($id, $module = 'complaints')
    {
        $data = array();
        $data['title'] = lang('complaint') . ' ' . lang('details');
        $data['active'] = 'notes';
        $data['id'] = $id;
        $data['module'] = $module;
        $data['module_field_id'] = $id;
        
        $data['contract'] = $contract = $this->complaints_model->get_by_id($id);
        if (empty($contract)) {
            show_404();
        }
        $data['subject'] = $contract->subject;
        $data['module_notes'] = get_result('tbl_leads_notes', array('module' => $module, 'module_field_id' => $id));
        $data['page_content'] = $this->load->view('notes', $data, TRUE);
        $data['subview'] = $this->load->view('details_layout', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }
    
    public function save_notes($leads_id)
    {
        $type = "error";
        $message = lang('some_thing_is_wrong');
        $data = $this->items_model->array_from_post(array('notes', 'contacted_indicator', 'module', 'module_field_id'));
        
        $data['user_id'] = my_id();
        //save data into table.
        $this->items_model->_table_name = 'tbl_leads_notes';
        $this->items_model->_primary_key = 'notes_id';
        $id = $this->items_model->save($data);
        
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
            // Update into tbl_activities
            $this->items_model->_table_name = "tbl_activities"; //table name
            $this->items_model->_primary_key = "activities_id";
            $this->items_model->save($activities);
            
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
        $this->items_model->_table_name = "tbl_activities"; //table name
        $this->items_model->_primary_key = "activities_id";
        $this->items_model->save($activities);
        
        $this->items_model->_table_name = 'tbl_leads_notes';
        $this->items_model->_primary_key = 'notes_id';
        $this->items_model->delete($notes_id);
        
        echo json_encode(array("status" => 'success', 'message' => lang('leads_notes_deleted')));
        exit();
    }
    
    
    public
    function delete($action, $id, $replay_id = NULL)
    {
        if ($action == 'delete_complaint_replay') {
            $comments_info = $this->complaints_model->check_by(array('tickets_replies_id' => $replay_id), 'tbl_complaints_replies');
            if (!empty($comments_info->attachment)) {
                $attachment = json_decode($comments_info->attachment);
                foreach ($attachment as $v_file) {
                    remove_files($v_file->fileName);
                }
            }
            // save into activities
            $activities = array(
                'user' => $this->session->userdata('user_id'),
                'module' => $this->module_name,
                'module_field_id' => $id,
                'activity' => 'activity_complaint_comment_deleted',
                'icon' => 'fa-ticket',
                'link' => 'admin/complaints/complaint_details/' . $id,
                'value1' => $comments_info->body,
            );
            // Update into tbl_activities
            $this->complaints_model->_table_name = "tbl_activities"; //table name
            $this->complaints_model->_primary_key = "activities_id";
            $this->complaints_model->save($activities);
            
            $this->complaints_model->_table_name = 'tbl_complaints_replies';
            $this->complaints_model->delete_multiple(array('ticket_reply_id' => $replay_id));
            
            $this->complaints_model->_table_name = 'tbl_complaints_replies';
            $this->complaints_model->_primary_key = 'tickets_replies_id';
            $this->complaints_model->delete($replay_id);
            
            echo json_encode(array("status" => 'success', 'message' => lang('complaint_reply_deleted')));
            exit();
        }
        if ($action == 'delete_complaint') {
            
            $tik_info = $this->complaints_model->check_by(array('tickets_id' => $id), 'tbl_complaints');
            $deleted = can_action(6, 'deleted');
            if (!empty($deleted)) {
                
                $all_replies_info = $this->db->where(array('tickets_id' => $id))->get('tbl_complaints_replies')->result();
                
                if (!empty($all_replies_info)) {
                    foreach ($all_replies_info as $v_replies) {
                        $attachment = json_decode($v_replies->attachment);
                        foreach ($attachment as $v_file) {
                            remove_files($v_file->fileName);
                        }
                    }
                }
                
                $comments_info = $this->complaints_model->check_by(array('tickets_id' => $id), 'tbl_complaints');
                if (!empty($comments_info->upload_file)) {
                    $attachment = json_decode($comments_info->upload_file);
                    foreach ($attachment as $v_file) {
                        remove_files($v_file->fileName);
                    }
                }
                // save into activities
                $activities = array(
                    'user' => $this->session->userdata('user_id'),
                    'module' => $this->module_name,
                    'module_field_id' => $id,
                    'activity' => 'activity_complaint_deleted',
                    'icon' => 'fa-ticket',
                    'value1' => (!empty($tik_info->ticket_code) ? $tik_info->ticket_code : ''),
                );
                // Update into tbl_activities
                $this->complaints_model->_table_name = "tbl_activities"; //table name
                $this->complaints_model->_primary_key = "activities_id";
                $this->complaints_model->save($activities);
                
                $this->complaints_model->_table_name = 'tbl_complaints_replies';
                $this->complaints_model->delete_multiple(array('tickets_id' => $id));
                
                $this->complaints_model->_table_name = 'tbl_pinaction';
                $this->complaints_model->delete_multiple(array('module_name' => $this->module_name, 'module_id' => $id));
                
                
                $this->complaints_model->_table_name = 'tbl_complaints';
                $this->complaints_model->_primary_key = 'tickets_id';
                $this->complaints_model->delete($id);
                $type = 'success';
                $message = lang('complaint_deleted');
            } else {
                $type = 'error';
                $message = lang('error_occurred');
            }
            if (!empty($replay_id)) {
                return (array("status" => $type, 'message' => $message));
            }
            echo json_encode(array("status" => $type, 'message' => $message));
            exit();
        }
    }
}
