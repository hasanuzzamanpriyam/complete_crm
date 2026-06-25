<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Api_auth');
        $this->load->model('login_model');
    }

    public function login()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['username']) || empty($input['password'])) {
            return $this->_respond(400, false, 'Username and password required');
        }

        $user = $this->api_auth->login($input['username'], $input['password']);

        if (empty($user)) {
            return $this->_respond(401, false, 'Invalid credentials');
        }

        $tokens = $this->_create_session($user->user_id);

        $profile = $this->_user_profile($user);
        $allow_demo = $this->db->where('config_key', 'timesync_demo_mode')->get('tbl_config')->row()->value == '1';

        return $this->_respond(200, true, 'Login successful', [
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
            'user' => $profile,
            'allow_demo' => $allow_demo,
            'expires_in' => 86400,
        ]);
    }

    public function refresh()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['refresh_token'])) {
            return $this->_respond(400, false, 'Refresh token required');
        }

        $refresh_hash = hash('sha256', $input['refresh_token']);

        $this->db->where('refresh_token_hash', $refresh_hash);
        $this->db->where('expires_at >', date('Y-m-d H:i:s'));
        $session = $this->db->get('tbl_user_api_sessions')->row();

        if (empty($session)) {
            return $this->_respond(401, false, 'Invalid or expired refresh token');
        }

        $this->db->where('id', $session->id)->delete('tbl_user_api_sessions');

        $user = $this->db->where('user_id', $session->user_id)->get('tbl_users')->row();
        if (empty($user) || $user->activated != 1) {
            return $this->_respond(403, false, 'Account inactive');
        }

        $tokens = $this->_create_session($user->user_id);
        $profile = $this->_user_profile($user);

        return $this->_respond(200, true, 'Token refreshed', [
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
            'user' => $profile,
            'expires_in' => 86400,
        ]);
    }

    public function logout()
    {
        $this->api_auth->authenticate_optional();
        $session = $this->api_auth->get_session();
        if (!empty($session)) {
            $this->db->where('id', $session->id)->delete('tbl_user_api_sessions');
        }
        return $this->_respond(200, true, 'Logged out');
    }

    public function me()
    {
        $user = $this->api_auth->authenticate();
        $profile = $this->_user_profile($user);
        return $this->_respond(200, true, 'OK', $profile);
    }

    public function register()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['username']) || empty($input['email']) || empty($input['password'])) {
            return $this->_respond(400, false, 'Username, email, and password required');
        }

        $exists = $this->db
            ->where('username', $input['username'])
            ->or_where('email', $input['email'])
            ->get('tbl_users')->num_rows();

        if ($exists) {
            return $this->_respond(409, false, 'Username or email already exists');
        }

        $user_data = [
            'username' => $input['username'],
            'email' => $input['email'],
            'password' => hash('sha512', $input['password'] . config_item('encryption_key')),
            'role_id' => 2,
            'activated' => 1,
            'banned' => 0,
            'last_ip' => $this->input->ip_address(),
            'created' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert('tbl_users', $user_data);
        $user_id = $this->db->insert_id();

        if (!empty($input['full_name'])) {
            $this->db->insert('tbl_account_details', [
                'user_id' => $user_id,
                'fullname' => $input['full_name'],
            ]);
        }

        return $this->_respond(201, true, 'User registered');
    }

    public function health()
    {
        return $this->_respond(200, true, 'ok', [
            'status' => 'ok',
            'version' => '1.0.0',
            'timestamp' => date('c'),
        ]);
    }

    private function _create_session($user_id)
    {
        $access_token = bin2hex(random_bytes(32));
        $refresh_token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', time() + 2592000); // 30 days

        $this->db->insert('tbl_user_api_sessions', [
            'user_id' => $user_id,
            'access_token_hash' => hash('sha256', $access_token),
            'refresh_token_hash' => hash('sha256', $refresh_token),
            'expires_at' => $expires_at,
        ]);

        return [
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'expires_at' => $expires_at,
        ];
    }

    private function _user_profile($user)
    {
        $account = $this->db->where('user_id', $user->user_id)->get('tbl_account_details')->row();

        return [
            'id' => (int)$user->user_id,
            'erp_id' => (int)$user->user_id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role_id == 1 ? 'admin' : ($user->role_id == 3 ? 'manager' : 'employee'),
            'full_name' => $account->fullname ?? $user->username,
            'is_active' => $user->activated == 1,
            'created_at' => $user->created ?? date('Y-m-d H:i:s'),
        ];
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
