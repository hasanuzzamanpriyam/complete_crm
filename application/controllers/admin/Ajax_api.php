<?php
class Ajax_api extends CI_Controller {
    public function __construct() {
        parent::__construct();
    }
    
    public function add_server_type() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        if ($this->input->post()) {
            $name = $this->input->post('name', TRUE);
            if ($name) {
                $data = array(
                    'name' => $name,
                    'created_at' => date('Y-m-d H:i:s')
                );
                if ($this->db->insert('tbl_server_types', $data)) {
                    $response = array(
                        'status' => 'success',
                        'id' => $name,
                        'text' => $name
                    );
                } else {
                    $response = array(
                        'status' => 'error',
                        'message' => 'Database error: ' . $this->db->error()['message']
                    );
                }
            } else {
                $response = array(
                    'status' => 'error',
                    'message' => 'Invalid input'
                );
            }
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
            return;
        }

        $this->load->view('admin/server_management/add_server_type');
    }
    
    public function add_plan() {
        if (!$this->input->post()) {
            $this->load->view('admin/server_management/add_plan');
            return;
        }
        $name = $this->input->post('name', TRUE);
        if ($name) {
            $data = array(
                'name' => $name,
                'created_at' => date('Y-m-d H:i:s')
            );
            if ($this->db->insert('tbl_hosting_plans', $data)) {
                $response = array(
                    'status' => 'success',
                    'id' => $name,
                    'text' => $name
                );
            } else {
                $response = array(
                    'status' => 'error',
                    'message' => 'Database error: ' . $this->db->error()['message']
                );
            }
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'Invalid input'
            );
        }
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
    
    public function add_project() {
        if (!$this->input->post()) {
            $this->load->model('items_model');
            $data['assign_user'] = $this->items_model->allowed_user('57');
            $data['all_customer_group'] = $this->db->where('type', 'projects')->order_by('customer_group_id', 'DESC')->get('tbl_customer_group')->result();
            $data['active'] = 2;
            $data['tab'] = 'projects';
            $this->load->view('admin/projects/create', $data);
            return;
        }
        $name = $this->input->post('project_name', TRUE);
        if ($name) {
            $data = array(
                'project_name' => $name,
                'project_status' => 'started'
            );
            if ($this->db->insert('tbl_project', $data)) {
                $response = array(
                    'status' => 'success',
                    'id' => $this->db->insert_id(),
                    'text' => $name
                );
            } else {
                $response = array(
                    'status' => 'error',
                    'message' => 'Database error: ' . $this->db->error()['message']
                );
            }
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'Invalid input'
            );
        }
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
    
    public function add_client() {
        if (!$this->input->post()) {
            $this->load->model('client_model');
            $this->client_model->_table_name = "tbl_countries";
            $this->client_model->_order_by = "id";
            $data['countries'] = $this->client_model->get();

            $this->client_model->_table_name = 'tbl_currencies';
            $this->client_model->_order_by = 'name';
            $data['currencies'] = $this->client_model->get();

            $data['assign_user'] = $this->client_model->allowed_user('4');
            $data['languages'] = $this->db->where('active', 1)->order_by('name', 'ASC')->get('tbl_languages')->result();
            $data['active'] = 2;
            
            $this->load->view('admin/client/new_client', $data);
            return;
        }
        $name = $this->input->post('name', TRUE);
        $email = $this->input->post('email', TRUE);
        if ($name && $email) {
            $data = array(
                'name' => $name,
                'email' => $email
            );
            if ($this->db->insert('tbl_client', $data)) {
                $response = array(
                    'status' => 'success',
                    'id' => $this->db->insert_id(),
                    'text' => $name
                );
            } else {
                $response = array(
                    'status' => 'error',
                    'message' => 'Database error: ' . $this->db->error()['message']
                );
            }
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'Invalid input'
            );
        }
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function add_currency() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        if ($this->input->post()) {
            $code = $this->input->post('code', TRUE);
            $name = $this->input->post('name', TRUE);
            $symbol = $this->input->post('symbol', TRUE);
            if ($code && $name) {
                $data = array(
                    'name' => $name,
                    'symbol' => $symbol
                );
                // Check if code exists
                $exists = $this->db->where('code', $code)->get('tbl_currencies')->row();
                if ($exists) {
                    // Update existing
                    $this->db->where('code', $code)->update('tbl_currencies', $data);
                    $response = array(
                        'status' => 'success',
                        'id' => $code,
                        'text' => $name . ' (' . $code . ')'
                    );
                } else {
                    // Insert new
                    $data['code'] = $code;
                    if ($this->db->insert('tbl_currencies', $data)) {
                        $response = array(
                            'status' => 'success',
                            'id' => $code,
                            'text' => $name . ' (' . $code . ')'
                        );
                    } else {
                        $response = array(
                            'status' => 'error',
                            'message' => 'Database error: ' . $this->db->error()['message']
                        );
                    }
                }
            } else {
                $response = array(
                    'status' => 'error',
                    'message' => 'Invalid input'
                );
            }
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
            return;
        }

        $this->load->view('admin/server_management/add_currency');
    }
}
?>