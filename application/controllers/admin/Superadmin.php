<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Superadmin extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Only super admin can access this controller
        if (!is_super_admin()) {
            redirect('404');
        }

        $this->load->model('user_model');
        $this->load->model('department_model');
    }

    public function index()
    {
        $data['title'] = lang('super_admin_dashboard');

        // System overview stats
        $data['total_users'] = $this->db->count_all_results('tbl_users');
        $data['total_super_admins'] = $this->db->where('is_super_admin', 1)->count_all_results('tbl_users');
        $data['total_admins'] = $this->db->where('role_id', 1)->where('is_super_admin', 0)->count_all_results('tbl_users');
        $data['total_staff'] = $this->db->where('role_id', 3)->count_all_results('tbl_users');
        $data['total_clients'] = $this->db->where('role_id', 2)->count_all_results('tbl_users');

        // Recent audit logs
        $data['recent_logs'] = $this->db->order_by('created_at', 'DESC')->limit(10)->get('tbl_audit_logs')->result();

        // System health
        $data['db_size'] = $this->db->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = 'tic_crm'")->row()->size_mb ?? 0;
        $data['php_version'] = phpversion();
        $data['ci_version'] = CI_VERSION;

        $data['subview'] = $this->load->view('admin/superadmin/dashboard', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }

    public function users()
    {
        $data['title'] = lang('user_management');
        // Fetch menu lookup for permission names
        $data['menu_lookup'] = $this->db
            ->select('menu_id, label AS name')
            ->where('status', 1)
            ->get('tbl_menu')
            ->result_array();
        $data['menu_lookup'] = array_column($data['menu_lookup'], 'name', 'menu_id');

        $data['all_users'] = $this->db
            ->select('tbl_users.*, tbl_account_details.fullname, tbl_account_details.avatar, tbl_designations.designations, tbl_departments.deptname')
            ->from('tbl_users')
            ->join('tbl_account_details', 'tbl_account_details.user_id = tbl_users.user_id', 'left')
            ->join('tbl_designations', 'tbl_designations.designations_id = tbl_account_details.designations_id', 'left')
            ->join('tbl_departments', 'tbl_departments.departments_id = tbl_designations.departments_id', 'left')
            ->order_by('tbl_users.user_id', 'ASC')
            ->get()
            ->result();
        // Attach effective permissions for each user
        foreach ($data['all_users'] as $u) {
            $u->effective_permissions = $this->get_user_effective_permissions($u->user_id);
        }
        $data['subview'] = $this->load->view('admin/superadmin/user_list', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }

    public function toggle_super_admin($user_id)
    {
        $user = $this->db->where('user_id', $user_id)->get('tbl_users')->row();
        if (empty($user)) {
            redirect('admin/superadmin/users');
        }

        $new_val = $user->is_super_admin ? 0 : 1;

        // Prevent revoking super admin status for the primary super admin (ID 1)
        if ($user_id == 1 && $new_val == 0) {
            $type = 'error';
            $message = 'Cannot revoke super admin status for the primary super admin!';
            set_message($type, $message);
            redirect('admin/superadmin/users');
        }

        $this->db->where('user_id', $user_id)->update('tbl_users', array('is_super_admin' => $new_val));

        audit_log(
            $new_val ? 'super_admin_granted' : 'super_admin_revoked',
            'user',
            $user_id,
            array('username' => $user->username, 'new_status' => (bool)$new_val)
        );

        $type = 'success';
        $message = $new_val ? lang('make_super_admin') : lang('remove_super_admin');
        set_message($type, $message . ' ' . $user->username);
        redirect('admin/superadmin/users');
    }

    public function permissions($user_id = null)
    {
        $data['title'] = lang('permission_manager');

        if (empty($user_id)) {
            redirect('admin/superadmin/users');
        }

        $data['selected_user'] = $this->db
            ->select('tbl_users.*, tbl_account_details.fullname')
            ->from('tbl_users')
            ->join('tbl_account_details', 'tbl_account_details.user_id = tbl_users.user_id', 'left')
            ->where('tbl_users.user_id', $user_id)
            ->get()
            ->row();

        if (empty($data['selected_user'])) {
            redirect('admin/superadmin/permissions');
        }

        // Get all menu items (tree structure)
        $data['menu_items'] = $this->db->where('status', 1)->order_by('sort', 'ASC')->get('tbl_menu')->result();

        // Get user's designation-based permissions
        $acc_details = $this->db->where('user_id', $user_id)->get('tbl_account_details')->row();
        $designation_perms = array();
        if (!empty($acc_details->designations_id)) {
            $dp_rows = $this->db->where('designations_id', $acc_details->designations_id)->get('tbl_user_role')->result();
            foreach ($dp_rows as $dp) {
                $designation_perms[$dp->menu_id] = $dp;
            }
        }
        $data['designation_perms'] = $designation_perms;

        // Get per-user permission overrides
        $user_perm_rows = $this->db->where('user_id', $user_id)->get('tbl_user_permissions')->result();
        $user_perms = array();
        foreach ($user_perm_rows as $up) {
            $user_perms[$up->menu_id] = $up;
        }
        $data['user_perms'] = $user_perms;

        // Build menu tree
        $menu_tree = array();
        $parents = array();
        foreach ($data['menu_items'] as $item) {
            $parents[$item->parent][] = $item;
        }
        $data['menu_tree'] = $this->_build_menu_tree(0, $parents);
        $data['parents'] = $parents;

        $data['subview'] = $this->load->view('admin/superadmin/permission_grid', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }

    private function _build_menu_tree($parent, $parents)
    {
        $result = array();
        if (isset($parents[$parent])) {
            foreach ($parents[$parent] as $item) {
                $children = $this->_build_menu_tree($item->menu_id, $parents);
                if (!empty($children)) {
                    $item->children = $children;
                }
                $result[] = $item;
            }
        }
        return $result;
    }

    private function get_user_effective_permissions($user_id)
    {
        // Get designation based permissions
        $acc_details = $this->db->where('user_id', $user_id)->get('tbl_account_details')->row();
        $designation_perms = [];
        if (!empty($acc_details->designations_id)) {
            $dp_rows = $this->db->where('designations_id', $acc_details->designations_id)->get('tbl_user_role')->result();
            foreach ($dp_rows as $dp) {
                $designation_perms[$dp->menu_id] = $dp;
            }
        }

        // Get per‑user overrides
        $user_perm_rows = $this->db->where('user_id', $user_id)->get('tbl_user_permissions')->result();
        $user_perms = [];
        foreach ($user_perm_rows as $up) {
            $user_perms[$up->menu_id] = $up;
        }

        // Merge: overrides take precedence
        $effective = [];
        $all_menu_ids = array_unique(array_merge(array_keys($designation_perms), array_keys($user_perms)));
        foreach ($all_menu_ids as $mid) {
            if (isset($user_perms[$mid])) {
                $effective[$mid] = $user_perms[$mid];
            } elseif (isset($designation_perms[$mid])) {
                $effective[$mid] = $designation_perms[$mid];
            }
        }
        return $effective;
    }



    public function audit_logs()
    {
        $data['title'] = lang('audit_logs');

        // Filters
        $filter_user = $this->input->get('user_id');
        $filter_action = $this->input->get('action');
        $filter_module = $this->input->get('module');
        $filter_from = $this->input->get('from');
        $filter_to = $this->input->get('to');

        $this->db->from('tbl_audit_logs');

        if (!empty($filter_user)) {
            $this->db->where('user_id', $filter_user);
        }
        if (!empty($filter_action)) {
            $this->db->where('action', $filter_action);
        }
        if (!empty($filter_module)) {
            $this->db->where('module', $filter_module);
        }
        if (!empty($filter_from)) {
            $this->db->where('created_at >=', $filter_from . ' 00:00:00');
        }
        if (!empty($filter_to)) {
            $this->db->where('created_at <=', $filter_to . ' 23:59:59');
        }

        $total = $this->db->count_all_results();

        $per_page = 50;
        $offset = $this->input->get('per_page') ? (int)$this->input->get('per_page') : 0;

        $this->db->from('tbl_audit_logs');
        if (!empty($filter_user)) $this->db->where('user_id', $filter_user);
        if (!empty($filter_action)) $this->db->where('action', $filter_action);
        if (!empty($filter_module)) $this->db->where('module', $filter_module);
        if (!empty($filter_from)) $this->db->where('created_at >=', $filter_from . ' 00:00:00');
        if (!empty($filter_to)) $this->db->where('created_at <=', $filter_to . ' 23:59:59');

        $data['logs'] = $this->db->order_by('created_at', 'DESC')->limit($per_page, $offset)->get()->result();

        // Get filter dropdown data
        $data['users_filter'] = $this->db
            ->select('tbl_users.user_id, tbl_users.username, tbl_account_details.fullname')
            ->from('tbl_users')
            ->join('tbl_account_details', 'tbl_account_details.user_id = tbl_users.user_id', 'left')
            ->where('tbl_users.role_id', 1)
            ->get()
            ->result();
        $data['actions_filter'] = $this->db->distinct()->select('action')->from('tbl_audit_logs')->get()->result();
        $data['modules_filter'] = $this->db->distinct()->select('module')->from('tbl_audit_logs')->where('module IS NOT NULL')->get()->result();

        $data['total'] = $total;
        $data['per_page'] = $per_page;
        $data['offset'] = $offset;

        $data['subview'] = $this->load->view('admin/superadmin/audit_logs', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }

    public function settings()
    {
        $data['title'] = lang('super_admin_settings');

        if ($this->input->post()) {
            $updated = false;
            $settings = $this->input->post('settings', true);
            if (!empty($settings) && is_array($settings)) {
                foreach ($settings as $key => $value) {
                    $existing = $this->db->where('config_key', $key)->get('tbl_config')->row();
                    if (!empty($existing)) {
                        $this->db->where('config_key', $key)->update('tbl_config', array('value' => $value));
                    } else {
                        $this->db->insert('tbl_config', array('config_key' => $key, 'value' => $value));
                    }
                    $updated = true;
                }
            }
            if ($updated) {
                set_message('success', lang('settings_updated'));
                audit_log('settings_updated', 'super_admin_settings');
            }
            redirect('admin/superadmin/settings');
        }

        $data['config'] = $this->db->get('tbl_config')->result();

        $data['subview'] = $this->load->view('admin/superadmin/settings', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }

    public function clear_cache()
    {
        $cache_path = APPPATH . 'cache/';
        if (is_dir($cache_path)) {
            $files = glob($cache_path . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
        set_message('success', lang('cache_clear'));
        audit_log('cache_cleared', 'system');
        redirect('admin/superadmin');
    }
}
