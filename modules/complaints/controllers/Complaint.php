<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Complaint extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->client = client();
        $this->admin = admin();
        if (empty($this->client) && empty($this->admin)) {
            redirect('login');
        }
        
        $this->load->model('complaints_model');
        $this->load->model('tasks_model');
        $this->module_name = 'complaints';
    }
    
    public function index($id)
    {
        $data = array();
        $data['title'] = lang('complaint');
        if ($id && is_numeric($id)) {
            $data['tickets_info'] = $this->complaints_model->get_by_id($id);
            if (!empty($data['tickets_info'])) {
                $data['subview'] = $this->load->view('details', $data, TRUE);
                $this->load->view('frontend/_layout_main', $data);
            } else {
                show_404();
                exit;
            }
        } else {
            show_404();
            exit;
        }
    }
    
    
    public function complaints_list()
    {
        $data['page'] = lang('complaints');
        $data['title'] = lang('complaints');
        $data['breadcrumbs'] = lang('complaints');
        $data['active'] = 1;
        $data['subview'] = $this->load->view('complaints_list', $data, TRUE);
        $this->load->view('client/_layout_main', $data);
    }
    
    public function complaintsList($filterBy = null, $search_by = null)
    {
        if ($this->input->is_ajax_request()) {
            $this->complaints_model->fetch_complaints($filterBy, $search_by);
        } else {
            redirect('client/dashboard');
        }
    }
    
    
    public
    function pdf_contract($id)
    {
        $data = array();
        $data['title'] = "Contract PDF";
        $data['contract'] = $contract = $this->contracts_model->get_by_id($id);
        if (empty($contract)) {
            show_404();
            exit;
        }
        
        $this->load->helper('dompdf');
        $data['subview'] = $this->load->view('contract_letter', $data, TRUE);
        $viewfile = $this->load->view('frontend/_layout_print', $data, TRUE);
        pdf_create($viewfile, slug_it('Contract# ' . $data['contract']->contract_id));
    }
    
    
    public function signature($id = NULL)
    {
        $data['tickets_info'] = $this->complaints_model->check_by(array('tickets_id' => $id), 'tbl_complaints');
        if (empty($data['tickets_info'])) {
            show_404();
            exit;
        }
        $data['title'] = lang('signature');
        $data['active'] = 3;
        $data['id'] = $id;
        $data['subview'] = $this->load->view('compalint_signature', $data);
        $this->load->view('admin/_layout_modal', $data);
    }
    
    public function sign_complaint($id = NULL)
    {
        $data['tickets_info'] = $this->complaints_model->check_by(array('tickets_id' => $id), 'tbl_complaints');
        if (empty($data['tickets_info'])) {
            show_404();
            exit;
        }
        $type = "error";
        $msg = lang('something_is_wrong');
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div>', '</div>');
        $this->form_validation->set_rules('status', lang('status'), 'trim|required');
        $this->form_validation->set_rules('signature', lang('signature'), 'trim|required');
        
        if ($this->form_validation->run() == true) {
            $signature = $this->process_digital_signature_image($this->input->post('signature', false), 'uploads/complaints_signatures', $id . '_signature.png');
            if ($signature !== FALSE) {
                
                $status = $this->input->post('status');
                $udata['status'] = $status;
                
                $client_id = client_id();
                if (!empty($client_id)) {
                    $signer_id = $client_id;
                } else {
                    $signer_id = $this->session->userdata('user_id');
                }
                
                if ($status == 'resolved') {
                    $udata['resolver_id'] = $signer_id;
                    $udata['resolver_signature'] = $signature;
                    $udata['resolver_signature_date'] = date('Y-m-d H:i:s');
                } elseif ($status == 'closed') {
                    $udata['closer_id'] = $signer_id;
                    $udata['closer_signature'] = $signature;
                    $udata['closer_signature_date'] = date('Y-m-d H:i:s');
                }
                
                
                $this->db->where('tickets_id', $id);
                $this->db->update('tbl_complaints', $udata);
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
                    $msg = lang('complaint_signed');
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
    
    
    function process_digital_signature_image($partBase64, $path, $file_name)
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
    
    function _create_upload_path($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0755);
            fopen(rtrim($path, '/') . '/' . 'index.html', 'w');
        }
    }
}
