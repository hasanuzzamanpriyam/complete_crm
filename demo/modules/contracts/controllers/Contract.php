<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Contract extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $user_id = $this->session->userdata('user_id');
        $client_id = client_id();
        if (empty($client_id) && empty(admin($user_id))) {
            redirect('login');
        }
        $this->load->model('contracts_model');
    }
    
    public function list()
    {
        $data['title'] = lang('contracts');
        $data['breadcrumbs'] = lang('contracts');
        $data['subview'] = $this->load->view('contracts_client_subview', $data, true);
        $this->load->view('client/_layout_main', $data);
        
    }
    
    public function contractsList()
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
            
            $where = array('visible_to_client' => 'Yes');
            $fetch_data = make_datatables();
            
            $data = array();
            
            foreach ($fetch_data as $_key => $contract) {
                $action = null;
                $sub_array = array();
                $name = null;
                $name .= '<a class="text-info" href="' . base_url() . 'contracts/contract/index/' . $contract->contract_id . '">' . $contract->subject . '</a>';
                
                $sub_array[] = $name;
                $sub_array[] = $contract->contract_type_name;
                $sub_array[] = $contract->contract_value;
                $sub_array[] = display_date($contract->start_date);
                $sub_array[] = display_date($contract->end_date);
                $sub_array[] = $contract->signed == 1 ? '<span class="text-success">' . lang("signed") . '</span>' : '<span class="text-danger">' . lang("not_signed") . '</span>';
                
                $action .= btn_view('contracts/contract/index/' . $contract->contract_id) . ' ';
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
            render_table($data, $where);
        } else {
            redirect('admin/dashboard');
        }
    }
    
    
    public function index($id)
    {
        $data = array();
        $data['title'] = lang('contract');
        $data['active'] = 1;
        $data['contract'] = $contract = $this->contracts_model->get_by_id($id);
        if (empty($contract)) {
            show_404();
            exit();
        }
        $data['subview'] = $this->load->view('contract_letter', $data, true);
        $this->load->view('frontend/_layout_main', $data);
    }
    
    public function pdf_contract($id)
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
        pdf_create($viewfile, slug_it('Contract# ' . $data['contract']->contract_id));
    }
    
    
    public function signature($id = null)
    {
        $data['contract'] = $contract = $this->contracts_model->get_by_id($id);
        if (empty($contract)) {
            show_404();
            exit;
        }
        $data['title'] = lang('signature');
        $data['active'] = 3;
        $data['id'] = $id;
        $data['subview'] = $this->load->view('signature', $data);
        $this->load->view('admin/_layout_modal', $data);
    }
    
    public function sign_contract($id = null)
    {
        $data['contract'] = $contract = $this->contracts_model->get_by_id($id);
        if (empty($contract)) {
            show_404();
            exit;
        }
        $type = "error";
        $msg = lang('something_is_wrong');
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div>', '</div>');
        $this->form_validation->set_rules('first_name', lang('first_name'), 'trim|required');
        $this->form_validation->set_rules('email', lang('email'), 'trim|required');
        $this->form_validation->set_rules('signature', lang('signature'), 'trim|required');
        
        if ($this->form_validation->run() == true) {
            $signature = $this->process_digital_signature_image($this->input->post('signature', false), 'uploads/contracts_signatures', $id . '_signature.png');
            if ($signature !== false) {
                $udata = [
                    'signature' => $signature,
                    'signed' => 1,
                    'acceptance_firstname' => $this->input->post('first_name'),
                    'acceptance_lastname' => $this->input->post('last_name'),
                    'acceptance_email' => $this->input->post('email'),
                    'acceptance_date' => date('Y-m-d H:i:s'),
                    'acceptance_ip' => $this->input->ip_address(),
                ];
                
                $this->db->where('contract_id', $id);
                $this->db->update('tbl_contracts', $udata);
                $return_id = $this->db->affected_rows();
                
                if (!empty($return_id)) {
                    $_POST = array();
                    $activity = array(
                        'user' => $this->session->userdata('user_id'),
                        'module' => 'contracts',
                        'module_field_id' => $id,
                        'activity' => ('activity_signed_a_contract'),
                        'value1' => $udata['acceptance_firstname']
                    );
                    $this->items_model->_table_name = 'tbl_activities';
                    $this->items_model->_primary_key = 'activities_id';
                    $this->items_model->save($activity);
                    
                    $type = "success";
                    $msg = lang('contract_signed');
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
    
    
    public function process_digital_signature_image($partBase64, $path, $file_name)
    {
        $retval = false;
        if (empty($partBase64)) {
            return false;
        }
        
        $this->_create_upload_path($path);
        $filename = unique_filename($path, $file_name);
        $decoded_image = base64_decode($partBase64);
        $retval = false;
        $path = rtrim($path, '/') . '/' . $filename;
        $fp = fopen($path, 'w+');
        if (fwrite($fp, $decoded_image)) {
            $retval = $filename;
        }
        
        fclose($fp);
        
        return $retval;
    }
    
    public function _create_upload_path($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0755);
            fopen(rtrim($path, '/') . '/' . 'index.html', 'w');
        }
    }
}
