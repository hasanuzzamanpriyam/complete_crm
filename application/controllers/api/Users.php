<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Users extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Api_auth');
    }

    public function promote_to_manager()
    {
        $auth_user = $this->api_auth->authenticate();

        if ((int)$auth_user->role_id !== 1) {
            return $this->_respond(403, false, 'Only admins can promote users to manager');
        }

        $input = json_decode($this->input->raw_input_stream, true);
        $user_id = (int)($input['user_id'] ?? 0);

        if ($user_id <= 0) {
            return $this->_respond(400, false, 'Valid user_id is required');
        }

        $this->db->set('role_id', 3);
        $this->db->where('user_id', $user_id);
        $this->db->where_in('role_id', [2, 3]);
        $this->db->update('tbl_users');

        if ($this->db->affected_rows() === 0) {
            return $this->_respond(404, false, 'User not found or already a manager');
        }

        return $this->_respond(200, true, 'User promoted to manager successfully');
    }

    public function index()
    {
        $auth_user = $this->api_auth->authenticate();
        $scope = $this->input->get('scope', true);

        $this->db->select('tbl_users.user_id, tbl_users.username, tbl_users.email, tbl_users.role_id, tbl_account_details.fullname');
        $this->db->from('tbl_users');
        $this->db->join('tbl_account_details', 'tbl_account_details.user_id = tbl_users.user_id', 'left');
        $this->db->where('tbl_users.activated', 1);
        $this->db->where('tbl_users.banned', 0);
        $this->db->where('tbl_users.role_id !=', 2);

        if ($scope !== 'task_form') {
            $allowed_ids = $this->api_auth->get_authorized_user_ids();
            if (is_array($allowed_ids)) {
                $this->db->where_in('tbl_users.user_id', $allowed_ids);
            }
        }
        $users = $this->db->get()->result();

        $result = array_map(function ($u) {
            return [
                'id' => (int)$u->user_id,
                'username' => $u->username,
                'email' => $u->email,
                'role' => $u->role_id == 1 ? 'admin' : 'employee',
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
