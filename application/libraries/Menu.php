<?php

class Menu
{

    public function dynamicMenu()
    {

        $CI = &get_instance();
        $designations_id = $CI->session->userdata('designations_id');
        $user_type = $CI->session->userdata('user_type');
        if ($user_type != 1 && !is_super_admin()) { // query for employee user role
            $all_active_menus = $CI->db->where('status', 1)->order_by('sort', 'time')->get('tbl_menu')->result();
            $user_menu = array();
            foreach ($all_active_menus as $menu_item) {
                if (can_do($menu_item->menu_id)) {
                    $user_menu[] = $menu_item;
                }
            }
        } else { // get all menu for admin
            $user_menu = $CI->db->where('status', 1)->order_by('sort', 'time')->get('tbl_menu')->result();
        }

        // Add Server Management menu for both admins and authorized staff
        if ($user_type == 1 || is_super_admin() || $this->hasServerManagementAccess($CI->session->userdata('user_id'))) {
            $module = [
                [
                    'menu_id' => '111122222222',
                    'label' => 'modules',
                    'link' => '',
                    'icon' => 'fa fa-plug',
                    'parent' => '0',
                    'sort' => '1000',
                    'time' => date('Y-m-d h:i:s'),
                ],
                [
                    'menu_id' => '1111222222221',
                    'label' => 'my_modules',
                    'link' => 'admin/my_module',
                    'icon' => 'fa fa-bitbucket',
                    'parent' => '111122222222',
                    'sort' => '1',
                    'time' => date('Y-m-d h:i:s'),
                ],
                [
                    'menu_id' => '1111222222222',
                    'label' => 'available_modules',
                    'link' => 'available_modules',
                    'icon' => 'fa fa-plane',
                    'parent' => '111122222222',
                    'sort' => '2',
                    'status' => '1',
                    'time' => date('Y-m-d h:i:s'),
                ],
            ];
            $server_management = [
                [
                    'menu_id' => '111122222223',
                    'label' => 'server_management',
                    'link' => '',
                    'icon' => 'fa fa-server',
                    'parent' => '0',
                    'sort' => '1001',
                    'time' => date('Y-m-d h:i:s'),
                ],
                [
                    'menu_id' => '1111222222231',
                    'label' => 'server_dashboard',
                    'link' => 'admin/server_management',
                    'icon' => 'fa fa-dashboard',
                    'parent' => '111122222223',
                    'sort' => '1',
                    'time' => date('Y-m-d h:i:s'),
                ],
                [
                    'menu_id' => '1111222222232',
                    'label' => 'hosting_management',
                    'link' => 'admin/server_management/hosting',
                    'icon' => 'fa fa-globe',
                    'parent' => '111122222223',
                    'sort' => '2',
                    'time' => date('Y-m-d h:i:s'),
                ],
                [
                    'menu_id' => '1111222222233',
                    'label' => 'domain_management',
                    'link' => 'admin/server_management/domain',
                    'icon' => 'fa fa-sitemap',
                    'parent' => '111122222223',
                    'sort' => '3',
                    'time' => date('Y-m-d h:i:s'),
                ],
                [
                    'menu_id' => '11112222222341',
                    'label' => 'billing_order',
                    'link' => 'admin/server_management/billing',
                    'icon' => 'fa fa-money',
                    'parent' => '111122222223',
                    'sort' => '4',
                    'time' => date('Y-m-d h:i:s'),
                ],
                [
                    'menu_id' => '1111222222234',
                    'label' => 'provider_management',
                    'link' => 'admin/server_management/provider',
                    'icon' => 'fa fa-handshake-o',
                    'parent' => '111122222223',
                    'sort' => '5',
                    'time' => date('Y-m-d h:i:s'),
                ],

            ];
            $module_menu = json_decode(json_encode(array_merge($module, $server_management)));
            $user_menu = array_merge($user_menu, $module_menu);
        }
        // Filter super admin menu items for non-super-admin users
        if (!is_super_admin()) {
            $super_admin_labels = array('super_admin', 'super_admin_dashboard', 'user_permissions', 'audit_logs', 'super_admin_settings');
            $user_menu = array_values(array_filter($user_menu, function($item) use ($super_admin_labels) {
                return !in_array($item->label, $super_admin_labels);
            }));
        }

        $user_menu = apply_filters('sidebar_menu', $user_menu);
        // Create a multidimensional array to conatin a list of items and parents
        $menu = array(
            'items' => array(),
            'parents' => array()
        );

        foreach ($user_menu as $v_menu) {
            $menu['items'][$v_menu->menu_id] = $v_menu;
            $menu['parents'][$v_menu->parent][] = $v_menu->menu_id;
        }

        // Builds the array lists with data from the menu table.
        // Grouping is admin-sidebar only: clientMenu() and the horizontal layout
        // call buildMenu() without the flag and are unchanged.
        $group = $this->hasMenuGroupColumn();
        return $output = $this->buildMenu(0, $menu, null, $group);
    }

    /**
     * The canonical, ordered group map from application/config/menu_groups.php.
     * An empty array means grouping is unavailable and the caller must render
     * the flat menu.
     */
    public function groupMap()
    {
        // CI3's Config::load() loads ONE named file per call, and
        // config/autoload.php does not list this one, so config_item() would
        // return NULL and the sidebar would silently render no headers.
        // $fail_gracefully keeps a missing file non-fatal.
        $CI = &get_instance();
        $CI->load->config('menu_groups', false, true);

        $map = config_item('menu_groups');
        return is_array($map) ? $map : array();
    }

    /**
     * True when tbl_menu actually has the menu_group column. Checked once per
     * request by dynamicMenu() so an unmigrated database degrades to the flat
     * menu instead of fataling.
     */
    public function hasMenuGroupColumn()
    {
        static $has = null;
        if ($has === null) {
            $CI = &get_instance();
            $has = (bool) $CI->db->field_exists('menu_group', 'tbl_menu');
        }
        return $has;
    }

    public function clientMenu()
    {
        $CI = &get_instance();
        $user_id = $CI->session->userdata('user_id');
        $CI->db->select('tbl_client_role.*', FALSE);
        $CI->db->select('tbl_client_menu.*', FALSE);
        $CI->db->from('tbl_client_role');
        $CI->db->join('tbl_client_menu', 'tbl_client_role.menu_id = tbl_client_menu.menu_id', 'left');
        $CI->db->where('tbl_client_role.user_id', $user_id);
        $CI->db->order_by('sort');
        $query_result = $CI->db->get();
        $client_menu = $query_result->result();
        // Create a multidimensional array to conatin a list of items and parents
        $menu = array(
            'items' => array(),
            'parents' => array()
        );
        foreach ($client_menu as $v_menu) {
            $menu['items'][$v_menu->menu_id] = $v_menu;
            $menu['parents'][$v_menu->parent][] = $v_menu->menu_id;
        }
        // Builds the array lists with data from the menu table
        return $output = $this->buildMenu(0, $menu);
    }

    public function buildMenu($parent, $menu, $sub = NULL, $group = false)
    {
        $html = "";
        $ul_class = "";
        if (!empty(config_item('layout-h'))) {
            $ul_class = 'horizontal_menu navbar-nav';
        }
        if (isset($menu['parents'][$parent])) {
            if (!empty($sub)) {
                $html .= "<ul id=" . $sub . " class='nav s-menu sidebar-subnav collapse'><li class=\"sidebar-subnav-header\">" . lang($sub) . "</li>\n";
            } else {
                $html .= "<ul class='nav s-menu $ul_class'>\n";
            }

            // Grouping is top-level only; submenus stay flat because the
            // recursive call does not pass $group.
            $lastGroup = null;
            $groupMap  = ($group && (int) $parent === 0) ? $this->groupMap() : array();
            $itemIds   = $menu['parents'][$parent];

            // tbl_menu.sort interleaves the groups badly — in the live data the
            // top level runs crm, tools, projects, tools, finance, projects, hr,
            // projects, hr, finance ... Emitting a header on every change would
            // produce ~25 headers. So bucket the ids into the canonical group
            // order first, keeping the existing sort order inside each bucket,
            // then emit one header per bucket.
            if (!empty($groupMap)) {
                $buckets = array();
                foreach (array_keys($groupMap) as $key) {
                    $buckets[$key] = array();
                }
                foreach ($itemIds as $itemId) {
                    // Unknown or empty menu_group falls back to 'tools' so a
                    // section is never unlabelled. This is also what places the
                    // synthetic modules / server_management items, which have
                    // no menu_group column value, into Tools & Admin.
                    $g = isset($menu['items'][$itemId]->menu_group)
                        ? (string) $menu['items'][$itemId]->menu_group : '';
                    $g = ($g !== '' && isset($buckets[$g])) ? $g : 'tools';
                    $buckets[$g][] = $itemId;
                }
                $itemIds = array();
                foreach ($buckets as $ids) {
                    foreach ($ids as $id) {
                        $itemIds[] = $id;
                    }
                }
            }

            foreach ($itemIds as $itemId) {
                if (!empty($groupMap)) {
                    $g = isset($menu['items'][$itemId]->menu_group)
                        ? (string) $menu['items'][$itemId]->menu_group : '';
                    $g = ($g !== '' && isset($groupMap[$g])) ? $g : 'tools';
                    if ($g !== $lastGroup) {
                        $html .= '<li class="sidebar-group-header">'
                            . htmlspecialchars($groupMap[$g], ENT_QUOTES, 'UTF-8')
                            . "</li>\n";
                        $lastGroup = $g;
                    }
                }

                $result = $this->active_menu_id($menu['items'][$itemId]->menu_id);
                if ($result) {
                    $active = 'active';
                } else {
                    $active = '';
                }
                if ($menu['items'][$itemId]->link == 'knowledgebase') {
                    $terget = 'target="_blank"';
                } else {
                    $terget = null;
                }
                if (!isset($menu['parents'][$itemId])) { //if condition is false only view menu
                    $html .= "<li class='" . $active . "' >\n  <a $terget title='" . lang($menu['items'][$itemId]->label) . "' href='" . base_url() . $menu['items'][$itemId]->link . "'>\n <em class='" . $menu['items'][$itemId]->icon . "'></em><span>" . lang($menu['items'][$itemId]->label) . "</span></a>\n</li> \n";
                }
                if (isset($menu['parents'][$itemId])) { //if condition is true show with submenu
                    $html .= "<li class='sub-menu " . $active . "'>\n  <a data-toggle='collapse' href='#" . $menu['items'][$itemId]->label . "'> <em class='" . $menu['items'][$itemId]->icon . "'></em><span>" . lang($menu['items'][$itemId]->label) . "</span></a>\n";
                    $html .= self::buildMenu($itemId, $menu, $menu['items'][$itemId]->label);
                    $html .= "</li> \n";
                }
            }
            $html .= "</ul> \n";
        }
        return $html;
    }

    public function active_menu_id($id)
    {
        $CI = &get_instance();
        $activeId = $CI->session->userdata('menu_active_id');
        if (!empty($activeId)) {
            foreach ($activeId as $v_activeId) {
                if ($id == $v_activeId) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    public function hasServerManagementAccess($user_id)
    {
        $CI = &get_instance();
        // Check for any server management related tasks assigned to user
        $CI->db->where('module', 'server_management');
        $CI->db->group_start();
        if ($CI->db->version() >= 8) {
            $sq = '\\b' . ($user_id) . '\\b';
        } else {
            $sq = '[[:<:]]' . ($user_id) . '[[:>:]]';
        }
        $CI->db->where('permission REGEXP', $CI->db->escape($sq), false);
        $CI->db->or_where('permission', 'all');
        $CI->db->group_end();
        if ($CI->db->get('tbl_task')->num_rows() > 0) {
            return true;
        }

        // Check specific server management tables
        $tables = ['tblserver_hostings', 'tbldomains', 'tblproviders', 'tbl_billing_orders'];
        foreach ($tables as $table) {
            if ($CI->db->field_exists('permission', $table)) {
                $CI->db->group_start();
                $CI->db->where('permission REGEXP', $CI->db->escape($sq), false);
                $CI->db->or_where('permission', 'all');
                $CI->db->group_end();
                $CI->db->limit(1);
                if ($CI->db->get($table)->num_rows() > 0) {
                    return true;
                }
            }
        }
        return false;
    }
}
