<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Navigation extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('navigation_model');
        $this->language_files = $this->navigation_model->all_files();
    }

    /**
     * The canonical, ordered group map from application/config/menu_groups.php,
     * or an empty array when grouping is unavailable: the config file is
     * missing, or tbl_menu has no menu_group column yet. The views then render
     * no Group control and save_navigation() skips the column, so a deployment
     * that has not been migrated behaves as it did before groups existed.
     *
     * The autoloaded Menu library already answers both questions and is the
     * single source of truth, so reuse it instead of re-reading the config.
     */
    private function _group_map()
    {
        if (!$this->menu->hasMenuGroupColumn()) {
            return array();
        }
        return $this->menu->groupMap();
    }

    public function index()
    {
        $data['page'] = 'user';
        $data['menu'] = array("navigation_manager" => 1, "manage_navigation" => 1);
        $data['title'] = "Manage Navigation";
        $data['menu_groups'] = $this->_group_map();

        $this->navigation_model->_table_name = "tbl_menu"; //table name
        $this->navigation_model->_order_by = "menu_id";
        $data['nav'] = $this->navigation_model->get();

        $data['subview'] = $this->load->view('admin/menu/manage_navigation', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    /**
     * Add New Navigation
     */
    public function add_navigation($id = NULL)
    {
        $data['title'] = "Add New Navigation";
        $data['page'] = 'user';
        $this->navigation_model->_table_name = "tbl_menu"; //table name
        $this->navigation_model->_order_by = "menu_id";
        if ($id) {
            $data['menu_info'] = $this->navigation_model->get_by(array('menu_id' => $id,), TRUE);
            if (empty($data['menu_info'])) {
                // messages for user
                $type = "error";
                $message = "Sorry no record found!";
                set_message($type, $message);
                redirect('admin/navigation'); //redirect page
            }
        } else {
            $data['menu_info'] = $this->navigation_model->get_new_menuInfo();
        }

        $data['nav'] = $this->navigation_model->get();
        $data['menu_groups'] = $this->_group_map();
        $data['subview'] = $this->load->view('admin/menu/add_navigation', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    /**
     * Save Navigation
     */
    public function save_navigation($id = NULL)
    {
        // Only write the fields the form actually submitted. MY_Model's
        // array_from_post() assigns every listed key unconditionally, so naming a
        // field the form does not post overwrites the stored value with NULL. The
        // form has no `link` input, so saving any menu from the UI silently
        // blanked its link and still reported success.
        $fields = array('label', 'link', 'icon', 'sort');
        $posted = array();
        foreach ($fields as $field) {
            if ($this->input->post($field, true) !== NULL) {
                $posted[] = $field;
            }
        }
        $data = $this->navigation_model->array_from_post($posted);
        $data['parent'] = $this->input->post('parent',true);
        if (empty($data['parent'])) {
            $data['parent'] = 0;
        }

        // Whitelist, never a default: a group that is not a key of the map must
        // not reach the database, and silently coercing it to 'tools' would hide
        // the bug behind plausible data. An absent menu_group (an old form, a
        // bookmarked POST) leaves the stored value alone, and an unavailable map
        // skips the column so an unmigrated deployment still saves.
        //
        // Note the coupling: a submitted EMPTY string is present, not absent, so
        // it takes the reject branch. The select therefore has no blank
        // placeholder option and preselects 'tools' for a new menu. If one is
        // ever added, this guard must also allow '' or every untouched add-form
        // save will be refused.
        $groups = $this->_group_map();
        $menu_group = $this->input->post('menu_group', true);
        if (count($groups) > 0 && $menu_group !== NULL) {
            if (isset($groups[$menu_group])) {
                $data['menu_group'] = $menu_group;
            } else {
                // messages for user
                $type = "error";
                $message = "Invalid Menu Group!";
                set_message($type, $message);
                redirect('admin/navigation'); //redirect page
            }
        }

        $this->navigation_model->_table_name = "tbl_menu"; //table name
        $this->navigation_model->_primary_key = "menu_id"; // $id
        $this->navigation_model->_order_by = "menu_id";
        $this->navigation_model->save($data, $id);

        // messages for user
        $type = "success";
        $message = "Save Navigation Successfully!";
        set_message($type, $message);
        redirect('admin/navigation/add_navigation'); //redirect page
    }

    /**
     * Delete Navigation
     */
    public function delete_navigation($id = NULL)
    {
        $this->navigation_model->_table_name = "tbl_menu"; // table name
        $this->navigation_model->_primary_key = "menu_id"; // $id
        $this->navigation_model->_order_by = "menu_id";
        if ($id) {
            $result = $this->navigation_model->get_by(array('menu_id' => $id,), TRUE);

            if (empty($result)) {
                // messages for user
                $type = "error";
                $message = "Sorry no record for Delete!";
                set_message($type, $message);
                redirect('admin/navigation'); //redirect page
            } else {
                $parent = $this->navigation_model->get_by(array('parent' => $id,), TRUE);

                if (empty($parent)) {
                    $this->navigation_model->delete($id);
                    // messages for user
                    $type = "success";
                    $message = "Delete Successfully!";
                    set_message($type, $message);
                    redirect('admin/navigation'); //redirect page   
                } else {
                    // messages for user
                    $type = "error";
                    $message = "You can not delete, this recored already used!";
                    set_message($type, $message);
                    redirect('admin/navigation'); //redirect page
                }
            }
        }
    }

}
