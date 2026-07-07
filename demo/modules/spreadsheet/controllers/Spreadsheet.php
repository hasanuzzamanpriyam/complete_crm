<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Spreadsheet extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('spreadsheet_model');
        if (empty(my_id())) {
            redirect('login');
        }
    }
    
    public function index()
    {
        if (!empty(client_id())) {
            redirect('spreadsheet/manage');
        } else {
            redirect('admin/spreadsheet/manage');
        }
    }
    
    public function manage($tab = '')
    {
        $data['title'] = lang('spreadsheet');
        $data['tab'] = $tab;
        $data['departments'] = get_result('tbl_departments', '', 'array');
        $data['clients'] = get_result('tbl_client', '', 'array');
        $data['client_groups'] = get_result('tbl_customer_group', array('type' => 'client'), 'array');
        $data['staffs'] = get_staff_details('', 'array');
        
        if ($data['tab'] == '') {
            $data['tab'] = 'my_folder';
        }
        if ($data['tab'] == 'my_folder') {
            $data['folder_my_tree'] = $this->spreadsheet_model->tree_my_folder();
        }
        if ($data['tab'] == 'share_folder') {
            $data['folder_my_share_tree'] = $this->spreadsheet_model->tree_my_folder_share();
        }
        $data['subview'] = $this->load->view('manage', $data, TRUE);
        if (!empty(client_id())) {
            $data['breadcrumbs'] = lang('spreadsheet');
            $this->load->view('client/_layout_main', $data);
        } else {
            $this->load->view('admin/_layout_main', $data);
        }
    }
    
    /**
     * Add edit folder
     */
    public function add_edit_folder()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($data['id'] == '') {
                $id = $this->spreadsheet_model->add_folder($data);
                if (is_numeric($id)) {
                    $message = lang('added_successfully');
                    set_message('success', $message);
                } else {
                    $message = lang('added_fail');
                    set_message('warning', $message);
                }
            } else {
                $res = $this->spreadsheet_model->edit_folder($data);
                if ($res == true) {
                    $message = lang('updated_successfully');
                    set_message('success', $message);
                } else {
                    $message = lang('updated_fail');
                    set_message('warning', $message);
                }
            }
            redirect('admin/spreadsheet/manage/my_folder');
        }
    }
    
    /**
     * new file view
     * @param int $parent_id
     * @param int $id
     * @return  view or json
     */
    public function new_file_view($parent_id, $id = "")
    {
        $data = array();
        $data_form = $this->input->post();
        $data['title'] = lang('spreadsheet');
        $data['dataTables'] = true;
        $data['parent_id'] = $parent_id;
        $data['folder'] = $this->spreadsheet_model->get_my_folder_all();
        $data['departments'] = get_result('tbl_departments', '', 'array');
        $data['clients'] = get_result('tbl_client', '', 'array');
        $data['client_groups'] = get_result('tbl_customer_group', array('type' => 'client'), 'array');
        $data['staffs'] = get_staff_details('', 'array');
        if (isset($data_form['id'])) {
            if ($data_form['id'] == "") {
                if ($data_form['id'] == "") {
                    $success = $this->spreadsheet_model->add_file_sheet($data_form);
                    if (is_numeric($success)) {
                        $message = lang('added_successfully');
                        $file_excel = $this->spreadsheet_model->get_file_sheet($success);
                        echo json_encode(['success' => true, 'message' => $message, 'name_excel' => $file_excel->name]);
                        exit();
                    } else {
                        $message = lang('added_fail');
                        echo json_encode(['success' => false, 'message' => $message]);
                        exit();
                    }
                }
            }
            
            if ($data_form['id'] != "") {
                if (isset($data_form['id'])) {
                    if ($data_form['id'] != "") {
                        $data['id'] = $data_form['id'];
                    }
                } else {
                    $data['id'] = $id;
                    $data['file_excel'] = $this->spreadsheet_model->get_file_sheet($data['id']);
                    $data['data_form'] = str_replace('""', '"', $data['file_excel']->data_form);
                }
                if ($data_form && $data_form['id'] != "") {
                    $success = $this->spreadsheet_model->edit_file_sheet($data_form);
                    if ($success == true) {
                        $message = lang('updated_successfully');
                        echo json_encode(['success' => $success, 'message' => $message]);
                        exit();
                    } else {
                        $message = lang('updated_fail');
                        echo json_encode(['success' => $success, 'message' => $message]);
                        exit();
                    }
                }
            }
        }
        
        if ($id != '') {
            $data['id'] = $id;
            $data['file_excel'] = $this->spreadsheet_model->get_file_sheet($data['id']);
            $mystring = $data['file_excel']->data_form;
            $findme = 'images';
            $findme1 = '"color":",';
            $findme2 = '"value2":",';
            $findme3 = ':",';
            $pos = strpos($mystring, $findme);
            $pos1 = strpos($mystring, $findme1);
            $pos2 = strpos($mystring, $findme2);
            $pos3 = strpos($mystring, $findme2);
            if ($pos) {
                $data['data_form'] = str_replace('""', '"', $mystring);
            } else {
                $data['data_form'] = $mystring;
            }
            if ($pos1) {
                $data['data_form'] = str_replace('"color":",', '"color":"",', $mystring);
                $data['data_form'] = str_replace('"value2":",', '"value2":"",', $mystring);
            }
            if ($pos2) {
                $data['data_form'] = str_replace('"value2":",', '"value2":"",', $mystring);
            }
            if ($pos3) {
                $data['data_form'] = str_replace(':",', ':"",', $mystring);
            }
        }
        $data['tree_save'] = json_encode($this->spreadsheet_model->get_folder_tree());
        if (!isset($success)) {
            $data['dataTables'] = true;
            $data['title'] = lang('spreadsheet');
            $data['subview'] = $this->load->view('new_file_view', $data, true);
            if (!empty(client_id())) {
                $data['breadcrumbs'] = lang('spreadsheet');
                $this->load->view('client/_layout_main', $data);
            } else {
                $this->load->view('admin/_layout_main', $data);
            }
        }
    }
    
    /**
     * delete folder file
     * @param int $id
     * @return  json
     */
    public function delete_folder_file($id)
    {
        $success = false;
        $message = lang('deleted_fail');
        if ($id == 1) {
            echo json_encode(['success' => false, 'message' => lang('cannot_deleted _root_directory')]);
            exit();
        } else {
            if ($id != '') {
                $success = $this->spreadsheet_model->delete_folder_file($id);
                $message = lang('deleted');
            }
            echo json_encode(['success' => $success, 'message' => $message]);
            exit();
        }
    }
    
    /**
     * get file sheet
     * @param int $id
     * @return  json
     */
    public function get_file_sheet($id)
    {
        $data = $this->spreadsheet_model->get_file_sheet($id);
        $data_form = $data->data_form;
        $findme = 'images';
        $findme1 = '"color":",';
        $findme2 = '"value2":",';
        $findme3 = ':",';
        $pos = strpos($data_form, $findme);
        $pos1 = strpos($data_form, $findme1);
        $pos2 = strpos($data_form, $findme2);
        $pos3 = strpos($data_form, $findme2);
        if ($pos) {
            $data_form = str_replace('""', '"', $data_form);
        } else {
            $data_form = $data_form;
        }
        
        if ($pos1) {
            $data_form = str_replace('"color":",', '"color":"",', $data_form);
            $data_form = str_replace('"value2":",', '"value2":"",', $data_form);
        }
        
        if ($pos2) {
            $data_form = str_replace('"value2":",', '"value2":"",', $data_form);
        }
        
        if ($pos3) {
            $data_form = str_replace(':",', ':"",', $data_form);
        }
        echo json_encode($data_form);
        exit();
    }
    
    /**
     * get folder zip
     * @param int $id
     * @param string $name
     * @return  json
     */
    public function get_folder_zip($id, $name)
    {
        echo json_encode($this->spreadsheet_model->get_folder_zip($id, $name));
        exit();
    }
    
    /**
     * update share spreadsheet online
     * @return redirect
     */
    public function update_share_spreadsheet()
    {
        $data = $this->input->post();
        $success = $this->spreadsheet_model->update_share($data);
        
        if ($success == true) {
            $message = lang('updated_successfully');
            set_message('success', $message);
        } else {
            $message = lang('updated_fail');
            set_message('warning', $message);
        }
        redirect('admin/spreadsheet/manage/my_folder');
    }
    
    
    /**
     * new file view
     * @param int $parent_id
     * @param int $id
     * @return  view or json
     */
    public function file_view_share($hash = "")
    {
        $data_form = $this->input->post();
        $data['tree_save'] = json_encode($this->spreadsheet_model->get_folder_tree());
        
        if ($hash != "") {
            $share_child = $this->spreadsheet_model->get_share_form_hash($hash);
            $id = $share_child->id_share;
            $file_excel = $this->spreadsheet_model->get_file_sheet($id);
            $data['parent_id'] = $file_excel->parent_id;
            $data['role'] = $share_child->role;
            if (!empty(client_id())) {
                $rid = client_id();
                $sview = 'spreadsheet_client';
                $data['breadcrumbs'] = 'spreadsheet_client';
                $view = 'client';
            } else {
                $rid = my_id();
                $sview = 'spreadsheet_client';
                $view = 'admin';
            }
            if (($share_child->rel_id != $rid)) {
                access_denied('spreadsheet');
            }
        } else {
            $id = "";
            $data['parent_id'] = "";
            $data['role'] = 1;
        }
        
        $data_form = $this->input->post();
        $data['title'] = lang('new_file');
        $data['folder'] = $this->spreadsheet_model->get_my_folder_all();
        if ($data_form || isset($data_form['id'])) {
            if ($data_form['id'] == "") {
                $success = $this->spreadsheet_model->add_file_sheet($data_form);
                if (is_numeric($success)) {
                    $message = lang('added_successfully');
                    $file_excel = $this->spreadsheet_model->get_file_sheet($success);
                    echo json_encode(['success' => true, 'message' => $message, 'name_excel' => $file_excel->name]);
                    exit();
                } else {
                    $message = lang('added_fail');
                    echo json_encode(['success' => false, 'message' => $message]);
                    exit();
                }
            }
        }
        if ($id != "" || isset($data_form['id'])) {
            if (isset($data_form['id']) && $data_form['id'] != "") {
                $data['id'] = $data_form['id'];
            } else {
                $data['id'] = $id;
                $data['file_excel'] = $this->spreadsheet_model->get_file_sheet($data['id']);
                $mystring = $data['file_excel']->data_form;
                $findme = 'images';
                $findme1 = '"color":",';
                $findme2 = '"value2":",';
                $findme3 = ':",';
                $pos = strpos($mystring, $findme);
                $pos1 = strpos($mystring, $findme1);
                $pos2 = strpos($mystring, $findme2);
                $pos3 = strpos($mystring, $findme2);
                if ($pos) {
                    $data['data_form'] = str_replace('""', '"', $mystring);
                } else {
                    $data['data_form'] = $mystring;
                }
                
                if ($pos1) {
                    $data['data_form'] = str_replace('"color":",', '"color":"",', $mystring);
                    $data['data_form'] = str_replace('"value2":",', '"value2":"",', $mystring);
                }
                
                if ($pos2) {
                    $data['data_form'] = str_replace('"value2":",', '"value2":"",', $mystring);
                }
                
                if ($pos3) {
                    $data['data_form'] = str_replace(':",', ':"",', $mystring);
                }
            }
            if ($data_form && $data_form['id'] != "") {
                $success = $this->spreadsheet_model->edit_file_sheet($data_form);
                if ($success == true) {
                    $message = lang('updated_successfully');
                    echo json_encode(['success' => $success, 'message' => $message]);
                    exit();
                } else {
                    $message = lang('updated_fail');
                    echo json_encode(['success' => $success, 'message' => $message]);
                    exit();
                }
            }
        }
        if (!isset($success)) {
            $data['title'] = lang('spreadsheet');
            $data['subview'] = $this->load->view('share_file_view', $data, true);
            if (!empty(client_id())) {
                $data['breadcrumbs'] = lang('spreadsheet');
                $this->load->view('client/_layout_main', $data);
            } else {
                $this->load->view('admin/_layout_main', $data);
            }
        }
    }
    
    /**
     * get hash staff
     * @param int $id
     * @return json
     */
    public function get_hash_staff($id)
    {
        if (!empty(client_id())) {
            $rel_type = 'client';
            $rel_id = client_id();
        } else {
            $rel_type = 'staff';
            $rel_id = my_id();
        }
        $data = $this->spreadsheet_model->get_hash($rel_type, $rel_id, $id);
        echo json_encode($data);
        exit();
    }
    
    /**
     * get hash client
     * @param int $id
     * @return json
     */
    public function get_hash_client($id)
    {
        $rel_id = my_id();
        $rel_type = 'client';
        echo json_encode($this->spreadsheet_model->get_hash($rel_type, $rel_id, $id));
        exit();
    }
    
    
    /**
     * [get_client_all description]
     * @return [type] [description]
     */
    public function get_related_id($id)
    {
        $data = $this->spreadsheet_model->data_related_id($id);
        echo json_encode($data);
        exit();
    }
    
    /**
     * get related
     * @param string $type
     * @return json
     */
    public function get_related($type = '', $selected = '')
    {
        
        if (!empty($type)) {
            $rel_data = get_related_moduleName_by_value($type, null, true);
            if (!empty($rel_data)) {
                // $html_option .= '<option value=""></option>';
                $html_option = '';
                foreach ($rel_data as $key => $value) {
                    $html_option .= '<option value="' . $value->id . '" ' . ($selected == $value->id ? 'selected' : '') . ' >' . $value->name;
                }
            }
        }
        echo json_encode($html_option);
        exit();
    }
    
    public function update_related_spreadsheet()
    {
        $data = $this->input->post();
        $success = $this->spreadsheet_model->update_related($data);
        
        if ($success == true) {
            $message = lang('updated_successfully');
            set_message('success', $message);
        } else {
            $message = lang('updated_fail');
            set_message('warning', $message);
        }
        redirect('admin/spreadsheet/manage/my_folder');
    }
    
    public function get_share_staff_client($id)
    {
        $data = $this->spreadsheet_model->get_share_detail($id);
        $html_staff = "";
        $html_client = "";
        if (count($data['staffs_share']) > 0) {
            foreach ($data['staffs_share'] as $key => $value) {
                $html_staff .= '
              <tr>
                <td>' . $value . '</td>
                <td>' . ($data['staffs_role'][$key] == 1 ? "View" : "Edit") . '</td>
              </tr>
          ';
            }
        }
        
        if (count($data['clients_share']) > 0) {
            foreach ($data['clients_share'] as $key => $value) {
                $html_client .= '
              <tr>
                <td>' . $value . '</td>
                <td>' . ($data['clients_role'][$key] == 1 ? "View" : "Edit") . '</td>
              </tr>
          ';
            }
        }
        echo json_encode(['staffs' => $html_staff, 'clients' => $html_client]);
        exit();
    }
    
    /**
     * [get_my_folder description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function get_my_folder($id)
    {
        $data = $this->spreadsheet_model->get_my_folder($id);
        echo json_encode($data);
        exit();
    }
    
    /**
     * [get_my_folder_get_hash description]
     * @param  [type] $rel_type [description]
     * @param  [type] $rel_id   [description]
     * @param  [type] $id_share [description]
     * @return [type]           [description]
     */
    public function get_my_folder_get_hash($rel_type, $rel_id, $id_share)
    {
        echo json_encode($this->spreadsheet_model->get_hash($rel_type, $rel_id, $id_share));
        exit();
    }
    
    
    /**
     * [droppable_event description]
     * @param  [type] $id        [description]
     * @param  [type] $parent_id [description]
     * @return [type]            [description]
     */
    public function droppable_event($id, $parent_id)
    {
        echo json_encode($this->spreadsheet_model->droppable_event($id, $parent_id));
        exit();
    }
    
    
    /**
     * get hash related
     * @param int $id
     * @return json
     */
    public function get_hash_related($rel_id, $rel_type, $parent_id)
    {
        echo json_encode($this->spreadsheet_model->get_hash_related($rel_type, $rel_id, $parent_id));
        exit();
    }
    
    
    /**
     * file view related
     * @param int $hash
     * @return  view or json
     */
    public function file_view_share_related($hash = "")
    {
        $data_form = $this->input->post();
        $data['tree_save'] = json_encode($this->spreadsheet_model->get_folder_tree());
        
        if ($hash != "") {
            $share_child = $this->spreadsheet_model->get_share_form_hash_related($hash);
            $id = $share_child->parent_id;
            $file_excel = $this->spreadsheet_model->get_file_sheet($id);
            $data['parent_id'] = $file_excel->parent_id;
            $data['role'] = $share_child->role;
        } else {
            $id = "";
            $data['parent_id'] = "";
            $data['role'] = 1;
        }
        
        $data_form = $this->input->post();
        $data['title'] = lang('new_file');
        $data['folder'] = $this->spreadsheet_model->get_my_folder_all();
        if ($data_form || isset($data_form['id'])) {
            if ($data_form['id'] == "") {
                $success = $this->spreadsheet_model->add_file_sheet($data_form);
                if (is_numeric($success)) {
                    $message = lang('added_successfully');
                    $file_excel = $this->spreadsheet_model->get_file_sheet($success);
                    echo json_encode(['success' => true, 'message' => $message, 'name_excel' => $file_excel->name]);
                    exit();
                } else {
                    $message = lang('added_fail');
                    echo json_encode(['success' => false, 'message' => $message]);
                    exit();
                }
            }
        }
        if ($id != "" || isset($data_form['id'])) {
            if (isset($data_form['id'])) {
                if ($data_form['id'] != "") {
                    $data['id'] = $data_form['id'];
                }
            } else {
                $data['id'] = $id;
                $data['file_excel'] = $this->spreadsheet_model->get_file_sheet($data['id']);
                $mystring = $data['file_excel']->data_form;
                $findme = 'images';
                $findme1 = '"color":",';
                $findme2 = '"value2":",';
                $findme3 = ':",';
                $pos = strpos($mystring, $findme);
                $pos1 = strpos($mystring, $findme1);
                $pos2 = strpos($mystring, $findme2);
                $pos3 = strpos($mystring, $findme2);
                if ($pos) {
                    $data['data_form'] = str_replace('""', '"', $mystring);
                } else {
                    $data['data_form'] = $mystring;
                }
                
                if ($pos1) {
                    $data['data_form'] = str_replace('"color":",', '"color":"",', $mystring);
                    $data['data_form'] = str_replace('"value2":",', '"value2":"",', $mystring);
                }
                
                if ($pos2) {
                    $data['data_form'] = str_replace('"value2":",', '"value2":"",', $mystring);
                }
                
                if ($pos3) {
                    $data['data_form'] = str_replace(':",', ':"",', $mystring);
                }
            }
            
            if ($data_form && $data_form['id'] != "") {
                $success = $this->spreadsheet_model->edit_file_sheet($data_form);
                if ($success == true) {
                    $message = lang('updated_successfully');
                    echo json_encode(['success' => $success, 'message' => $message]);
                    exit();
                } else {
                    $message = lang('updated_fail');
                    echo json_encode(['success' => $success, 'message' => $message]);
                    exit();
                }
            }
        }
        if (!isset($success)) {
            $data['dataTables'] = true;
            $data['title'] = lang('spreadsheet');
            $data['subview'] = $this->load->view('share_file_view', $data, true);
            if (!empty(client_id())) {
                $data['breadcrumbs'] = lang('spreadsheet');
                $this->load->view('client/_layout_main', $data);
            } else {
                $this->load->view('admin/_layout_main', $data);
            }
        }
    }
    
    
}
