<?php

/**
 * Description of Admin_Controller
 *
 * @author pc mart ltd
 */
class Admin_Controller extends MY_Controller
{
    private $_current_version;

    function __construct()
    {
        parent::__construct();
        $this->load->model('common_model');
        $this->load->model('admin_model');

        // Check if currently logged in user account is deactivated or banned
        $user_id = $this->session->userdata('user_id');
        if (!empty($user_id)) {
            $user_status = $this->db->select('activated, banned')->where('user_id', $user_id)->get('tbl_users')->row();
            if (empty($user_status) || $user_status->activated == 0 || $user_status->banned == 1) {
                $this->session->sess_destroy();
                $type = 'error';
                $message = 'Your account has been locked. Please contact an administrator.';
                set_message($type, $message);
                redirect('login');
            }
        }

        $this->_current_version = $this->admin_model->get_current_db_version();

        if ($this->admin_model->is_db_upgrade_required($this->_current_version) && !$this->input->post('auto_update', true)) {
            if ($this->input->post('upgrade_database', true)) {
                $this->admin_model->upgrade_database();
            }
            include_once(APPPATH . 'views/admin/settings/db_update_required.php');
            die;
        }
        // Super admin bypasses all permission restrictions
        if (is_super_admin()) {
            $all_menu = get_result('tbl_menu');
            $_SESSION['user_roll'] = $all_menu;
            return;
        }

        if (strpos($this->uri->uri_string(), 'login') === FALSE) {
            $this->session->set_userdata(array(
                'url' => $this->uri->uri_string()
            ));
        }
        //get all navigation data
        $all_menu = get_result('tbl_menu');
        $_SESSION['user_roll'] = $all_menu;
    
        //get user id from session
        $designations_id = $this->session->userdata('designations_id');
        $this->common_model->_table_name = 'tbl_user_role'; //table name
        $this->common_model->_order_by = 'user_role_id';
        // get user navigation by user id
        $user_menu = $this->common_model->select_user_roll($designations_id);
    
        $user_type = $this->session->userdata('user_type');
        if ($user_type != 1) {
            $restricted_link = array();

            // Load user-specific overrides if the table exists
            $user_id = $this->session->userdata('user_id');
            $user_overrides = array();
            if ($this->db->table_exists('tbl_user_permissions') && !empty($user_id)) {
                $overrides = $this->db->where('user_id', $user_id)->get('tbl_user_permissions')->result();
                foreach ($overrides as $o) {
                    $user_overrides[$o->menu_id] = $o;
                }
            }

            $designation_menu_ids = array();
            if (!empty($user_menu)) {
                foreach ($user_menu as $data2) {
                    $designation_menu_ids[] = $data2->menu_id;
                }
            }

            foreach ($all_menu as $data1) {
                $is_allowed = false;
                if (isset($user_overrides[$data1->menu_id])) {
                    // If override exists, check if view permission is enabled
                    if ($user_overrides[$data1->menu_id]->view == 1) {
                        $is_allowed = true;
                    }
                } else {
                    // Fallback to designation menu role
                    if (in_array($data1->menu_id, $designation_menu_ids)) {
                        $is_allowed = true;
                    }
                }

                if ($is_allowed === false) {
                    $restricted_link[] = $data1->link;
                }
            }
            $exception_uris = $restricted_link;
        } else {
            $exception_uris = array();
        }
        $exception_uris = apply_filters('more_exception_uri', $exception_uris);
        $user_flag = $this->session->userdata('user_flag');
        if (!empty($user_flag)) {
            // if ($user_flag != '1') {
            //     $url = $this->session->userdata('url');
            //     redirect($url);
            // }
        } else {
            redirect('locked');
        }
    
        // url segment
        $a = $this->uri->segment(1) . '/' . $this->uri->segment(2);
        if ($a != 'admin/settings') {
            // Build all prefixes of the requested URI
            $segments = array();
            $temp_uri = '';
            for ($i = 1; $i <= $this->uri->total_segments(); $i++) {
                $temp_uri .= $this->uri->segment($i) . '/';
                $segments[] = rtrim($temp_uri, '/');
            }

            // Get all defined menu links in the database to match prefixes
            $all_menu_links = array();
            foreach ($all_menu as $m) {
                if (!empty($m->link)) {
                    $all_menu_links[] = rtrim($m->link, '/');
                }
            }

            $checked = false;
            // Check from longest to shortest prefix to find the closest parent menu
            for ($i = count($segments) - 1; $i >= 0; $i--) {
                $current_prefix = $segments[$i];
                if (in_array($current_prefix, $all_menu_links)) {
                    // Closest defined parent menu found!
                    if (in_array($current_prefix, $exception_uris)) {
                        redirect('404');
                    } else {
                        // Parent menu is allowed, so deep links are automatically allowed
                        $checked = true;
                        break;
                    }
                }
            }

            // Fallback: If no defined menu matches any prefix, check if any prefix is explicitly restricted
            if (!$checked) {
                foreach ($segments as $seg) {
                    if (in_array($seg, $exception_uris)) {
                        redirect('404');
                    }
                }
            }
        }
    }
}
