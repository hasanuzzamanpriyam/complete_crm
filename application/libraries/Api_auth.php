<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Api_auth
{
    protected $ci;
    protected $user = null;
    protected $session = null;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->model('user_model');

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    public function authenticate()
    {
        $token = $this->_extract_bearer_token();

        if (empty($token)) {
            $this->_deny('Authentication required. Provide a Bearer token.', 401);
        }

        $token_hash = hash('sha256', $token);

        $this->ci->db->where('access_token_hash', $token_hash);
        $this->ci->db->where('expires_at >', date('Y-m-d H:i:s'));
        $session = $this->ci->db->get('tbl_user_api_sessions')->row();

        if (empty($session)) {
            $this->_deny('Invalid or expired token', 401);
        }

        $user = $this->ci->db->where('user_id', $session->user_id)->get('tbl_users')->row();

        if (empty($user) || $user->activated != 1 || $user->banned != 0) {
            $this->_deny('User account is inactive or banned', 403);
        }

        $this->user = $user;
        $this->session = $session;
        return $this->user;
    }

    public function authenticate_optional()
    {
        $token = $this->_extract_bearer_token();
        if (empty($token)) {
            return null;
        }

        $token_hash = hash('sha256', $token);

        $this->ci->db->where('access_token_hash', $token_hash);
        $this->ci->db->where('expires_at >', date('Y-m-d H:i:s'));
        $session = $this->ci->db->get('tbl_user_api_sessions')->row();

        if (empty($session)) {
            return null;
        }

        $user = $this->ci->db->where('user_id', $session->user_id)->get('tbl_users')->row();

        if (empty($user) || $user->activated != 1 || $user->banned != 0) {
            return null;
        }

        $this->user = $user;
        $this->session = $session;
        return $this->user;
    }

    public function get_user()
    {
        return $this->user;
    }

    public function get_session()
    {
        return $this->session;
    }

    public function is_super_admin()
    {
        if (empty($this->user)) {
            return false;
        }
        $CI =& $this->ci;

        if ($this->user->user_id == 1) {
            return true;
        }
        if (!empty($this->user->is_super_admin) && $this->user->is_super_admin == 1) {
            return true;
        }
        if ($this->user->role_id == 1) {
            return true;
        }
        return false;
    }

    public function login($username, $password)
    {
        $this->ci->load->model('login_model');

        $user = $this->ci->db
            ->where('username', $username)
            ->where('activated', 1)
            ->where('banned', 0)
            ->get('tbl_users')->row();

        if (empty($user)) {
            return null;
        }

        $password_hash = hash('sha512', $password . config_item('encryption_key'));

        if ($user->password !== $password_hash) {
            return null;
        }

        $this->user = $user;
        return $user;
    }

    private function _extract_bearer_token()
    {
        $header = $this->ci->input->server('HTTP_AUTHORIZATION');

        if (empty($header)) {
            $header = $this->ci->input->server('REDIRECT_HTTP_AUTHORIZATION');
        }

        if (empty($header)) {
            if (function_exists('apache_request_headers')) {
                $h = apache_request_headers();
                $header = $h['Authorization'] ?? $h['authorization'] ?? null;
            } elseif (function_exists('getallheaders')) {
                $h = getallheaders();
                $header = $h['Authorization'] ?? $h['authorization'] ?? null;
            }
        }

        if (empty($header) && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
            $header = $_SERVER['HTTP_AUTHORIZATION'];
        }

        if (!empty($header) && preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
            return trim($m[1]);
        }

        return null;
    }

    private function _deny($message, $http_code = 401)
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        http_response_code($http_code);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}
