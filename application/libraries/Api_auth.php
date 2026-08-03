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

    /**
     * Authenticate for public-facing endpoints (e.g. consultations).
     *
     * Accepts a valid Bearer token (CRM apps) OR a shared API key sent via the
     * X-API-Key header. The key is stored in tbl_config as
     * 'consultation_api_key'. When the key setting is empty the API key path is
     * disabled and only Bearer tokens are accepted.
     *
     * @return object|null Authenticated user for Bearer token calls, null when
     *                     authenticated via API key.
     */
    public function authenticate_consultation()
    {
        $user = $this->authenticate_optional();

        if (!empty($user)) {
            return $user;
        }

        $api_key = $this->_extract_api_key();
        if (empty($api_key)) {
            $this->_deny('Authentication required. Provide a Bearer token or a valid X-API-Key.', 401);
        }

        $row = $this->ci->db->where('config_key', 'consultation_api_key')->get('tbl_config')->row();
        $expected = !empty($row) ? trim($row->value) : '';

        if (empty($expected) || !hash_equals($expected, $api_key)) {
            $this->_deny('Invalid or disabled API key', 401);
        }

        return null;
    }

    public function get_user()
    {
        return $this->user;
    }

    public function get_session()
    {
        return $this->session;
    }

    public function get_allowed_user_ids()
    {
        $user = $this->get_user();
        if (!$user) return [];

        $user_id = (int)$user->user_id;

        if ($this->is_super_admin()) return null;

        if ((int)$user->role_id === 3) {
            $managed = $this->_get_managed_user_ids();
            if ($managed === null) return null;
            $managed[] = $user_id;
            return array_values(array_unique(array_map('intval', $managed)));
        }

        return [$user_id];
    }

    public function get_user_team_ids($user_id = null)
    {
        $user_id = $user_id ?? $this->user->user_id;
        $teams = $this->ci->db->select('team_id')
            ->where('user_id', $user_id)
            ->get('tbl_team_members')
            ->result();
        return array_map(function ($t) {
            return (int)$t->team_id;
        }, $teams);
    }

    public function get_authorized_user_ids($requested_user_id = null)
    {
        $user = $this->get_user();
        if (!$user) return [];
        $user_id = (int)$user->user_id;

        // Tier 1: Super admin sees ALL
        if ($this->is_super_admin()) {
            if ($requested_user_id) return [(int)$requested_user_id];
            return null;
        }

        $authorized = [$user_id]; // everyone sees themselves

        // Tier 2: Team manager -- approved members of teams they manage
        $team_ids = $this->_get_team_managed_user_ids();
        if (!empty($team_ids)) {
            $authorized = array_merge($authorized, $team_ids);
        }

        // Tier 3: Department head -- existing org-chart scoping
        $dept_ids = $this->_get_managed_user_ids();
        if (!empty($dept_ids)) {
            $authorized = array_merge($authorized, $dept_ids);
        }

        $authorized = array_unique(array_map('intval', $authorized));

        // If a specific user_id was requested, validate it
        if ($requested_user_id) {
            $requested = (int)$requested_user_id;
            return in_array($requested, $authorized)
                ? [$requested]
                : [$user_id];
        }

        return array_values($authorized);
    }

    public function get_visible_project_ids($user_ids = null)
    {
        $user = $this->get_user();
        if (!$user) return [];

        if ($user_ids === null) {
            $user_ids = [(int)$user->user_id];
        }
        $user_ids = array_map('intval', (array)$user_ids);

        if ($this->is_super_admin()) {
            return null;
        }

        $patterns = [];
        foreach ($user_ids as $uid) {
            $patterns[] = '"' . $uid . '"';
        }
        $regex = '(' . implode('|', $patterns) . ')';

        $this->ci->db->select('project_id');
        $this->ci->db->from('tbl_project');
        $this->ci->db->group_start();
        $this->ci->db->where('permission', 'all');
        $this->ci->db->or_where_in('created_by', $user_ids);
        $this->ci->db->or_where("permission REGEXP '$regex'", null, false);
        $this->ci->db->group_end();

        $result = $this->ci->db->get()->result();
        $ids = array_map(function ($r) { return (int)$r->project_id; }, $result);

        return array_values(array_unique($ids));
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

    private function _extract_api_key()
    {
        $key = $this->ci->input->server('HTTP_X_API_KEY');

        if (empty($key)) {
            $key = $this->ci->input->server('REDIRECT_HTTP_X_API_KEY');
        }

        if (empty($key)) {
            if (function_exists('apache_request_headers')) {
                $h = apache_request_headers();
                $key = $h['X-API-Key'] ?? $h['x-api-key'] ?? null;
            } elseif (function_exists('getallheaders')) {
                $h = getallheaders();
                $key = $h['X-API-Key'] ?? $h['x-api-key'] ?? null;
            }
        }

        if (empty($key) && !empty($_SERVER['HTTP_X_API_KEY'])) {
            $key = $_SERVER['HTTP_X_API_KEY'];
        }

        return $key;
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

    private function _get_managed_user_ids()
    {
        $user = $this->get_user();
        if (!$user) return [];
        $user_id = (int)$user->user_id;
        $ci = $this->ci;

        $departments = $ci->db
            ->where('department_head_id', $user_id)
            ->get('tbl_departments')
            ->result();

        if (empty($departments)) return [];

        $dept_ids = array_map(function ($d) { return (int)$d->departments_id; }, $departments);

        $designations = $ci->db
            ->where_in('departments_id', $dept_ids)
            ->get('tbl_designations')
            ->result();

        if (empty($designations)) return [];

        $desig_ids = array_map(function ($d) { return (int)$d->designations_id; }, $designations);

        $accounts = $ci->db
            ->select('user_id')
            ->where_in('designations_id', $desig_ids)
            ->get('tbl_account_details')
            ->result();

        return array_map(function ($a) { return (int)$a->user_id; }, $accounts);
    }

    private function _get_team_managed_user_ids()
    {
        $user_id = (int)$this->user->user_id;
        $result = $this->ci->db->select('tm2.user_id')
            ->from('tbl_team_members tm1')
            ->join('tbl_team_members tm2',
                'tm2.team_id = tm1.team_id AND tm2.status = \'approved\'')
            ->where('tm1.user_id', $user_id)
            ->where('tm1.is_manager', 1)
            ->where('tm1.status', 'approved')
            ->get()
            ->result();
        $ids = array_map(function ($r) { return (int)$r->user_id; }, $result);
        return array_values(array_unique($ids));
    }
}
