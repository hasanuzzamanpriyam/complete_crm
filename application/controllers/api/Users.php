<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Users extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Api_auth');
    }

    public function index()
    {
        $this->api_auth->authenticate();

        $this->db->select('tbl_users.user_id, tbl_users.username, tbl_users.email, tbl_users.role_id, tbl_account_details.fullname');
        $this->db->from('tbl_users');
        $this->db->join('tbl_account_details', 'tbl_account_details.user_id = tbl_users.user_id', 'left');
        $this->db->where('tbl_users.activated', 1);
        $this->db->where('tbl_users.banned', 0);
        $users = $this->db->get()->result();

        $result = array_map(function ($u) {
            return [
                'id' => (int)$u->user_id,
                'username' => $u->username,
                'email' => $u->email,
                'role' => $u->role_id == 1 ? 'admin' : ($u->role_id == 3 ? 'manager' : 'employee'),
                'full_name' => $u->fullname ?? $u->username,
                'is_active' => true,
                'created_at' => '',
                'updated_at' => '',
            ];
        }, $users);

        return $this->_respond(200, true, 'OK', ['users' => $result]);
    }

    private function _respond($status_code, $success, $message, $data = null)
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $response = ['success' => $success, 'message' => $message];
        if ($data !== null) {
            $response = array_merge($response, $data);
        }
        $this->output
            ->set_status_header($status_code)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}
