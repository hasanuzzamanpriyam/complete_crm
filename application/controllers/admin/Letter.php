<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Letter extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('letter_model');
    }

    public function index()
    {
        redirect('admin/letter/templates');
    }

    /*** TEMPLATE METHODS ***/

    public function templates()
    {
        $data['title'] = 'Letter Templates';
        $data['active'] = 1;
        $data['subview'] = $this->load->view('admin/letter/all_template', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }

    public function view_template($id)
    {
        $user_id = $this->session->userdata('user_id');
        $is_super_admin = $this->session->userdata('is_super_admin');

        $where = array('id' => $id);
        if (empty($is_super_admin)) {
            $where['admin_id'] = $user_id;
        }
        $data['template_info'] = $this->letter_model->check_by($where, 'tbl_letter_templates');
        if (empty($data['template_info'])) {
            set_message('error', 'Template not found');
            redirect('admin/letter/templates');
        }

        $data['title'] = 'View Template';
        $this->load->view('admin/letter/view_template', $data);
    }

    public function templateList()
    {
        if (!$this->input->is_ajax_request()) {
            redirect('admin/dashboard');
        }

        $this->load->model('datatables');
        $this->datatables->table = 'tbl_letter_templates';
        $this->datatables->select = 'tbl_letter_templates.*';
        $main_column = array('tbl_letter_templates.title');
        $action_array = array('tbl_letter_templates.id');
        $result = array_merge($main_column, $action_array);
        $this->datatables->column_order = $result;
        $this->datatables->column_search = $result;
        $this->datatables->order = array('tbl_letter_templates.id' => 'desc');

        $where = array();
        $is_super_admin = $this->session->userdata('is_super_admin');
        if (empty($is_super_admin)) {
            $where = array('tbl_letter_templates.admin_id' => $this->session->userdata('user_id'));
        }

        $fetch_data = make_datatables($where);
        $data = array();

        if (!empty($fetch_data)) {
            foreach ($fetch_data as $key => $v_item) {
                $sub_array = array();
                $sub_array[] = $v_item->title;

                $action = '';
                $action .= btn_edit_modal('admin/letter/create/' . $v_item->id) . ' ';
                $action .= btn_view_modal('admin/letter/view_template/' . $v_item->id) . ' ';
                $action .= ajax_anchor(
                    base_url('admin/letter/delete/' . $v_item->id),
                    "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>",
                    array(
                        'class' => '',
                        'title' => 'Delete',
                        'data-fade-out-on-success' => '#table_' . $key
                    )
                );

                $sub_array[] = $action;
                $data[] = $sub_array;
            }
        }

        render_table($data, $where);
    }

    public function create($id = null)
    {
        $data['title'] = !empty($id) ? 'Edit Template' : 'New Template';

        if (!empty($id)) {
            $is_super_admin = $this->session->userdata('is_super_admin');
            $where = array('id' => $id);
            if (empty($is_super_admin)) {
                $where['admin_id'] = $this->session->userdata('user_id');
            }
            $data['template_info'] = $this->letter_model->check_by($where, 'tbl_letter_templates');
            if (empty($data['template_info'])) {
                set_message('error', 'Template not found');
                redirect('admin/letter/templates');
            }
        }

        $data['template_id'] = $id;
        $data['active'] = 2;

        if ($this->input->is_ajax_request()) {
            $this->load->view('admin/letter/create_template', $data);
            return;
        }

        $data['subview'] = $this->load->view('admin/letter/create_template', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }

    public function save($id = null)
    {
        $this->form_validation->set_rules('title', 'Title', 'required|trim');

        if ($this->form_validation->run() == false) {
            $this->output->set_content_type('application/json')
                         ->set_output(json_encode(array(
                             'status' => 'error',
                             'message' => validation_errors()
                         )));
            return;
        }

        $title = $this->input->post('title', true);
        $description = $this->input->post('description', false);

        $data = array(
            'title' => $title,
            'description' => $description,
            'admin_id' => $this->session->userdata('user_id'),
            'created_by' => $this->session->userdata('user_id')
        );

        $this->letter_model->_table_name = 'tbl_letter_templates';
        $this->letter_model->_primary_key = 'id';

        if (!empty($id)) {
            $is_super_admin = $this->session->userdata('is_super_admin');
            $update_where = array('id' => $id);
            if (empty($is_super_admin)) {
                $update_where['admin_id'] = $this->session->userdata('user_id');
            }
            $existing = $this->letter_model->check_by($update_where, 'tbl_letter_templates');
            if (!empty($existing)) {
                $this->letter_model->save($data, $id);
                $message = 'Template updated successfully';
            } else {
                $this->output->set_content_type('application/json')
                             ->set_output(json_encode(array('status' => 'error', 'message' => 'Template not found')));
                return;
            }
        } else {
            $id = $this->letter_model->save($data);
            $message = 'Template saved successfully';
        }

        if ($this->input->is_ajax_request()) {
            $this->output->set_content_type('application/json')
                         ->set_output(json_encode(array(
                             'status' => 'success',
                             'id' => $id,
                             'message' => $message
                         )));
            return;
        }

        set_message('success', $message);
        redirect('admin/letter/templates');
    }

    public function delete($id)
    {
        $is_super_admin = $this->session->userdata('is_super_admin');
        $where = array('id' => $id);
        if (empty($is_super_admin)) {
            $where['admin_id'] = $this->session->userdata('user_id');
        }

        $this->letter_model->_table_name = 'tbl_letter_templates';
        $this->letter_model->_primary_key = 'id';

        $template = $this->letter_model->check_by($where, 'tbl_letter_templates');
        if (!empty($template)) {
            $this->letter_model->delete($id);
            $this->output->set_output(json_encode(array('status' => 'success', 'message' => 'Template deleted successfully')));
        } else {
            $this->output->set_output(json_encode(array('status' => 'error', 'message' => 'Template not found or access denied')));
        }
    }

    /*** GENERATE METHODS ***/

    public function generate()
    {
        $data['title'] = 'Generated Letters';
        $data['active'] = 1;
        $data['subview'] = $this->load->view('admin/letter/all_generate', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }

    public function getGeneratedList()
    {
        if (!$this->input->is_ajax_request()) {
            redirect('admin/dashboard');
        }

        $user_id = $this->session->userdata('user_id');
        $is_super_admin = $this->session->userdata('is_super_admin');

        $this->load->model('datatables');
        $this->datatables->table = 'tbl_generated_letters';
        $this->datatables->join_table = array('tbl_letter_templates', 'tbl_users', 'tbl_account_details');
        $this->datatables->join_where = array(
            'tbl_generated_letters.template_id=tbl_letter_templates.id',
            'tbl_generated_letters.employee_id=tbl_users.user_id',
            'tbl_generated_letters.employee_id=tbl_account_details.user_id'
        );
        $this->datatables->select = 'tbl_generated_letters.*, tbl_letter_templates.title as template_title, tbl_account_details.fullname as employee_name';

        $main_column = array(
            'tbl_account_details.fullname',
            'tbl_letter_templates.title',
            'tbl_generated_letters.created_at'
        );
        $action_array = array('tbl_generated_letters.id');
        $result = array_merge($main_column, $action_array);
        $this->datatables->column_order = $result;
        $this->datatables->column_search = $result;
        $this->datatables->order = array('tbl_generated_letters.id' => 'desc');

        $where = array();
        if (empty($is_super_admin)) {
            $where['tbl_generated_letters.admin_id'] = $user_id;
        }

        $fetch_data = make_datatables($where);
        $data = array();

        if (!empty($fetch_data)) {
            foreach ($fetch_data as $key => $v_item) {
                $sub_array = array();
                $sub_array[] = $v_item->employee_name;
                $sub_array[] = $v_item->template_title;
                $sub_array[] = date('Y-m-d', strtotime($v_item->created_at));

                $action = '';
                $action .= '<a href="' . base_url('admin/letter/view_generated/' . $v_item->id) . '" class="btn btn-xs btn-primary" title="View"><i class="fa fa-eye"></i></a> ';
                $action .= '<a href="' . base_url('admin/letter/add_generate/' . $v_item->id) . '" class="btn btn-xs btn-primary" title="Edit"><i class="fa fa-pencil-square-o"></i></a> ';
                $action .= '<a href="' . base_url('admin/letter/download_pdf/' . $v_item->id) . '" class="btn btn-xs btn-info" title="Download PDF"><i class="fa fa-download"></i></a> ';
                $action .= ajax_anchor(
                    base_url('admin/letter/delete_generated/' . $v_item->id),
                    "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>",
                    array(
                        'class' => '',
                        'title' => 'Delete',
                        'data-fade-out-on-success' => '#table_' . $key
                    )
                );

                $sub_array[] = $action;
                $data[] = $sub_array;
            }
        }

        render_table($data, $where);
    }

    public function add_generate($id = null)
    {
        $user_id = $this->session->userdata('user_id');
        $is_super_admin = $this->session->userdata('is_super_admin');

        $data['title'] = 'Generate Letter';
        $data['active'] = 2;

        $template_where = array();
        if (empty($is_super_admin)) {
            $template_where['admin_id'] = $user_id;
        }
        $this->letter_model->_table_name = 'tbl_letter_templates';
        $this->letter_model->_primary_key = 'id';
        $data['templates'] = $this->letter_model->get_by($template_where);

    $employee_where = array('tbl_users.role_id' => 3, 'tbl_users.activated' => 1);
        $this->db->select('tbl_users.user_id, tbl_account_details.fullname');
        $this->db->join('tbl_account_details', 'tbl_users.user_id = tbl_account_details.user_id', 'left');
        $data['employees'] = $this->db->where($employee_where)->get('tbl_users')->result();

        $data['letter_info'] = null;
        if (!empty($id)) {
            $where = array('tbl_generated_letters.id' => $id);
            if (empty($is_super_admin)) {
                $where['tbl_generated_letters.admin_id'] = $user_id;
            }
            $data['letter_info'] = $this->letter_model->check_by($where, 'tbl_generated_letters');
        }

        if ($this->input->is_ajax_request()) {
            $this->load->view('admin/letter/add_generate', $data);
            return;
        }

        $data['subview'] = $this->load->view('admin/letter/add_generate', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }

    public function get_template_data()
    {
        if (!$this->input->is_ajax_request()) {
            redirect('admin/dashboard');
        }

        $template_id = $this->input->post('template_id');
        if (empty($template_id)) {
            $this->output->set_content_type('application/json')
                         ->set_output(json_encode(array('status' => 'error', 'message' => 'No template ID')));
            return;
        }

        $template = $this->letter_model->check_by(array('id' => $template_id), 'tbl_letter_templates');
        if (empty($template)) {
            $this->output->set_content_type('application/json')
                         ->set_output(json_encode(array('status' => 'error', 'message' => 'Template not found')));
            return;
        }

        $this->output->set_content_type('application/json')
                     ->set_output(json_encode(array(
                         'status' => 'success',
                         'description' => $template->description
                     )));
    }

    public function get_employee_data()
    {
        if (!$this->input->is_ajax_request()) {
            redirect('admin/dashboard');
        }

        $employee_id = $this->input->post('employee_id');
        if (empty($employee_id)) {
            $this->output->set_content_type('application/json')
                         ->set_output(json_encode(array('status' => 'error', 'message' => 'No employee ID')));
            return;
        }

        $details = $this->letter_model->get_employee_details($employee_id);
        if (empty($details)) {
            $this->output->set_content_type('application/json')
                         ->set_output(json_encode(array('status' => 'error', 'message' => 'Employee not found')));
            return;
        }

        $designation = '';
        $department = '';
        if (!empty($details->designations_id)) {
            $desig = $this->db->where('designations_id', $details->designations_id)->get('tbl_designations')->row();
            if ($desig) {
                $designation = $desig->designations;
                $dept = $this->db->where('departments_id', $desig->departments_id)->get('tbl_departments')->row();
                if ($dept) {
                    $department = $dept->deptname;
                }
            }
        }

        $company_name = config_item('company_name');
        if (empty($company_name)) {
            $company = $this->db->where('config_key', 'company_name')->get('tbl_config')->row();
            $company_name = $company ? $company->value : '';
        }
        $company_address = config_item('company_address');
        if (empty($company_address)) {
            $addr = $this->db->where('config_key', 'company_address')->get('tbl_config')->row();
            $company_address = $addr ? $addr->value : '';
        }
        $company_phone = config_item('company_phone');
        if (empty($company_phone)) {
            $ph = $this->db->where('config_key', 'company_phone')->get('tbl_config')->row();
            $company_phone = $ph ? $ph->value : '';
        }
        $company_email = config_item('company_email');
        if (empty($company_email)) {
            $em = $this->db->where('config_key', 'company_email')->get('tbl_config')->row();
            $company_email = $em ? $em->value : '';
        }

        $variables = array(
            '##CURRENT_DATE##'     => date('Y-m-d'),
            '##CURRENT_YEAR##'     => date('Y'),
            '##EMPLOYEE_NAME##'    => $details->fullname ?: '',
            '##EMPLOYEE_ID##'      => $details->employment_id ?: '',
            '##EMPLOYEE_ADDRESS##' => $details->present_address ?: '',
            '##EMPLOYEE_PHONE##'   => $details->phone ?: '',
            '##JOINING_DATE##'     => $details->joining_date ?: '',
            '##DATE_OF_BIRTH##'    => $details->date_of_birth ?: '',
            '##FATHER_NAME##'      => $details->father_name ?: '',
            '##MOTHER_NAME##'      => $details->mother_name ?: '',
            '##GENDER##'           => $details->gender ?: '',
            '##DESIGNATION##'      => $designation,
            '##DEPARTMENT##'       => $department,
            '##COMPANY_NAME##'     => $company_name,
            '##COMPANY_ADDRESS##'  => $company_address,
            '##COMPANY_PHONE##'    => $company_phone,
            '##COMPANY_EMAIL##'    => $company_email,
            '##CLIENT_NAME##'      => '',
            '##CLIENT_ADDRESS##'   => '',
            '##PROJECT_NAME##'     => '',
            '##PROJECT_ID##'       => '',
            '##TASK_NAME##'        => '',
            '##TASK_ID##'          => '',
        );

        $this->output->set_content_type('application/json')
                     ->set_output(json_encode(array(
                         'status' => 'success',
                         'employee_name' => $details->fullname ?: '',
                         'variables' => $variables
                     )));
    }

    public function save_generated($id = null)
    {
        if (!$this->input->is_ajax_request()) {
            redirect('admin/dashboard');
        }

        $user_id = $this->session->userdata('user_id');
        $is_super_admin = $this->session->userdata('is_super_admin');

        $this->form_validation->set_rules('template_id', 'Template', 'required|trim');
        $this->form_validation->set_rules('employee_id', 'Employee', 'required|trim');

        if ($this->form_validation->run() == false) {
            $this->output->set_content_type('application/json')
                         ->set_output(json_encode(array('status' => 'error', 'message' => validation_errors())));
            return;
        }

        $data = array(
            'admin_id'      => $user_id,
            'template_id'   => $this->input->post('template_id', true),
            'employee_id'   => $this->input->post('employee_id', true),
            'content'       => $this->input->post('content', false),
            'margin_top'    => $this->input->post('margin_top', true) ?: 20,
            'margin_bottom' => $this->input->post('margin_bottom', true) ?: 20,
            'margin_left'   => $this->input->post('margin_left', true) ?: 20,
            'margin_right'  => $this->input->post('margin_right', true) ?: 20,
        );

        $this->letter_model->_table_name = 'tbl_generated_letters';
        $this->letter_model->_primary_key = 'id';

        if (!empty($id)) {
            $where = array('id' => $id);
            if (empty($is_super_admin)) {
                $where['admin_id'] = $user_id;
            }
            $existing = $this->letter_model->check_by($where, 'tbl_generated_letters');
            if (!empty($existing)) {
                $this->letter_model->save($data, $id);
                $this->output->set_content_type('application/json')
                             ->set_output(json_encode(array('status' => 'success', 'message' => 'Letter updated successfully', 'id' => $id)));
            } else {
                $this->output->set_content_type('application/json')
                             ->set_output(json_encode(array('status' => 'error', 'message' => 'Letter not found')));
            }
        } else {
            $new_id = $this->letter_model->save($data);
            $this->output->set_content_type('application/json')
                         ->set_output(json_encode(array('status' => 'success', 'message' => 'Letter saved successfully', 'id' => $new_id)));
        }
    }

    public function view_generated($id)
    {
        $user_id = $this->session->userdata('user_id');
        $is_super_admin = $this->session->userdata('is_super_admin');

        $where = array('tbl_generated_letters.id' => $id);
        if (empty($is_super_admin)) {
            $where['tbl_generated_letters.admin_id'] = $user_id;
        }

        $this->db->select('tbl_generated_letters.*, tbl_account_details.fullname as employee_name, tbl_letter_templates.title as template_title');
        $this->db->join('tbl_account_details', 'tbl_generated_letters.employee_id = tbl_account_details.user_id', 'left');
        $this->db->join('tbl_letter_templates', 'tbl_generated_letters.template_id = tbl_letter_templates.id', 'left');
        $letter = $this->db->where($where)->get('tbl_generated_letters')->row();

        if (empty($letter)) {
            set_message('error', 'Letter not found');
            redirect('admin/letter/generate');
        }

        $data['title'] = 'View Letter - ' . $letter->employee_name;
        $data['letter'] = $letter;

        $data['subview'] = $this->load->view('admin/letter/view_generated', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }

    public function download_pdf($id)
    {
        $user_id = $this->session->userdata('user_id');
        $is_super_admin = $this->session->userdata('is_super_admin');

        $where = array('tbl_generated_letters.id' => $id);
        if (empty($is_super_admin)) {
            $where['tbl_generated_letters.admin_id'] = $user_id;
        }

        $this->db->select('tbl_generated_letters.*, tbl_account_details.fullname as employee_name, tbl_letter_templates.title as template_title');
        $this->db->join('tbl_account_details', 'tbl_generated_letters.employee_id = tbl_account_details.user_id', 'left');
        $this->db->join('tbl_letter_templates', 'tbl_generated_letters.template_id = tbl_letter_templates.id', 'left');
        $letter = $this->db->where($where)->get('tbl_generated_letters')->row();

        if (empty($letter)) {
            set_message('error', 'Letter not found');
            redirect('admin/letter/generate');
        }

        $this->load->helper('dompdf');

        $data['content'] = $letter->content;
        $data['margin_top'] = $letter->margin_top;
        $data['margin_bottom'] = $letter->margin_bottom;
        $data['margin_left'] = $letter->margin_left;
        $data['margin_right'] = $letter->margin_right;

        $html = $this->load->view('admin/letter/generate_pdf', $data, true);
        $filename = slug_it('letter_' . $letter->employee_name . '_' . date('Y-m-d', strtotime($letter->created_at)));

        pdf_create($html, $filename);
    }

    public function delete_generated($id)
    {
        $user_id = $this->session->userdata('user_id');
        $is_super_admin = $this->session->userdata('is_super_admin');

        $where = array('id' => $id);
        if (empty($is_super_admin)) {
            $where['admin_id'] = $user_id;
        }

        $this->letter_model->_table_name = 'tbl_generated_letters';
        $this->letter_model->_primary_key = 'id';

        $letter = $this->letter_model->check_by($where, 'tbl_generated_letters');
        if (!empty($letter)) {
            $this->letter_model->delete($id);
            $this->output->set_output(json_encode(array('status' => 'success', 'message' => 'Letter deleted successfully')));
        } else {
            $this->output->set_output(json_encode(array('status' => 'error', 'message' => 'Letter not found or access denied')));
        }
    }

    /*** PRINT HELPER ***/

    public function print_letter($id)
    {
        $user_id = $this->session->userdata('user_id');
        $is_super_admin = $this->session->userdata('is_super_admin');

        $where = array('tbl_generated_letters.id' => $id);
        if (empty($is_super_admin)) {
            $where['tbl_generated_letters.admin_id'] = $user_id;
        }

        $this->db->select('tbl_generated_letters.*, tbl_account_details.fullname as employee_name');
        $this->db->join('tbl_account_details', 'tbl_generated_letters.employee_id = tbl_account_details.user_id', 'left');
        $letter = $this->db->where($where)->get('tbl_generated_letters')->row();

        if (empty($letter)) {
            set_message('error', 'Letter not found');
            redirect('admin/letter/generate');
        }

        $data['content'] = $letter->content;
        $data['margin_top'] = $letter->margin_top;
        $data['margin_bottom'] = $letter->margin_bottom;
        $data['margin_left'] = $letter->margin_left;
        $data['margin_right'] = $letter->margin_right;

        $this->load->view('admin/letter/generate_pdf', $data);
    }
}
