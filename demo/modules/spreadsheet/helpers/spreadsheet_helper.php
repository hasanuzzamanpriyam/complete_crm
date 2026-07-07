<?php defined('BASEPATH') or exit('No direct script access allowed');
if(!defined('APP_MODULES_PATH')){
    define('APP_MODULES_PATH', FCPATH . 'modules/');
}
define('SPREAD_ONLINE_MODULE_UPLOAD_FOLDER', module_dir_path(SPREADSHEET_MODULE, 'uploads'));
define('VERSION_SREADSHEET', 105);
define('folder', 'folder');
function module_dir_path($module, $concat = '')
{
    return APP_MODULES_PATH . $module . '/' . $concat;
}


function module_dir_url($module, $segment = '')
{
    return site_url(basename(APP_MODULES_PATH) . '/' . $module . '/' . ltrim($segment, '/'));
}


function spreadsheet_add_head_component()
{
    $url = '';
    if (empty(client_id())) {
        $url = 'admin/';
    }
    echo "<script>
    var admin_url = '" . base_url() . $url . "';
    var module_name = '" . SPREADSHEET_MODULE . "';    
    </script>";
    
    $viewuri = $_SERVER['REQUEST_URI'];
    if (!(strpos($viewuri, '/spreadsheet/manage') === false) || !(strpos($viewuri, 'admin/projects/view') === false)) {
        echo '<link href="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/ludo-jquery-treetable/css/jquery.treetable.css') . '?v=' . VERSION_SREADSHEET . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/ludo-jquery-treetable/css/jquery.treetable.theme.default.css') . '?v=' . VERSION_SREADSHEET . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/ludo-jquery-treetable/css/screen.css') . '?v=' . VERSION_SREADSHEET . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(SPREADSHEET_MODULE, 'assets/css/manage_style.css') . '?v=' . VERSION_SREADSHEET . '"  rel="stylesheet" type="text/css" />';
    }
    
    if (!(strpos($viewuri, '/proposals') === false) || !(strpos($viewuri, 'admin/estimates') === false) || !(strpos($viewuri, 'admin/invoices') === false) || !(strpos($viewuri, 'admin/expenses') === false) || !(strpos($viewuri, 'admin/leads') === false)) {
        echo '<link href="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/ludo-jquery-treetable/css/jquery.treetable.css') . '?v=' . VERSION_SREADSHEET . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/ludo-jquery-treetable/css/jquery.treetable.theme.default.css') . '?v=' . VERSION_SREADSHEET . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/ludo-jquery-treetable/css/screen.css') . '?v=' . VERSION_SREADSHEET . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(SPREADSHEET_MODULE, 'assets/css/custom.css') . '?v=' . VERSION_SREADSHEET . '"  rel="stylesheet" type="text/css" />';
    }
    
    if (!(strpos($viewuri, '/spreadsheet/new_file_view') === false) || !(strpos($viewuri, 'admin/spreadsheet/file_view_share') === false) || !(strpos($viewuri, 'spreadsheet/file_view_share') === false)) {
        echo '<link href="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/ComboTree/style.css') . '?v=' . VERSION_SREADSHEET . '"  rel="stylesheet" type="text/css" />';
        
        echo '<link href="' . module_dir_url(SPREADSHEET_MODULE, 'assets/css/manage.css') . '?v=' . VERSION_SREADSHEET . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/luckysheet/css/iconfont.css') . '?v=' . VERSION_SREADSHEET . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/luckysheet/css/luckysheet.css') . '?v=' . VERSION_SREADSHEET . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/luckysheet/css/plugins.css') . '?v=' . VERSION_SREADSHEET . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/luckysheet/css/pluginsCss.css') . '?v=' . VERSION_SREADSHEET . '"  rel="stylesheet" type="text/css" />';
        
        echo '<link href="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/luckysheet/css/iconCustom.css') . '?v=' . VERSION_SREADSHEET . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/luckysheet/css/luckysheet-cellFormat.css') . '?v=' . VERSION_SREADSHEET . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/luckysheet/css/luckysheet-core.css') . '?v=' . VERSION_SREADSHEET . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/luckysheet/css/luckysheet-print.css') . '?v=' . VERSION_SREADSHEET . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/luckysheet/css/luckysheet-protection.css') . '?v=' . VERSION_SREADSHEET . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/luckysheet/css/luckysheet-zoom.css') . '?v=' . VERSION_SREADSHEET . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/luckysheet/css/chartmix.css') . '?v=' . VERSION_SREADSHEET . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/luckysheet/css/spectrum.min.css') . '?v=' . VERSION_SREADSHEET . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/luckysheet/css/chartmix.css') . '?v=' . VERSION_SREADSHEET . '"  rel="stylesheet" type="text/css" />';
    }
}


function spreadsheet_load_js()
{
    $viewuri = $_SERVER['REQUEST_URI'];
    if (!(strpos($viewuri, '/spreadsheet/manage') === false)) {
        echo '<script>';
        echo 'var download_file = "' . lang('download') . '";';
        echo 'var create_file = "' . lang('create_file') . '";';
        echo 'var create_folder = "' . lang('create_folder') . '";';
        echo 'var edit = "' . lang('edit') . '";';
        echo '</script>';
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/ludo-jquery-treetable/jquery-ui.min.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/ludo-jquery-treetable/jquery.treetable.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/js/manage.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/js/context_menu.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/luckysheet/js/luckysheet.umd.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        
        echo '<script type="module" src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/excel.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/FileSaver.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script  src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/js/exports.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
    }
    if (!(strpos($viewuri, '/projects/view') === false) || !(strpos($viewuri, 'admin/estimates') === false) || !(strpos($viewuri, 'admin/proposals') === false) || !(strpos($viewuri, 'admin/invoices') === false) || !(strpos($viewuri, 'admin/expenses') === false) || !(strpos($viewuri, 'admin/leads') === false)) {
        
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/js/manage.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/ludo-jquery-treetable/jquery-ui.min.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/ludo-jquery-treetable/jquery.treetable.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/js/relate_to.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/luckysheet/js/luckysheet.umd.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script type="module" src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/excel.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/FileSaver.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script  src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/js/exports.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
    }
    
    if (!(strpos($viewuri, '/spreadsheet/new_file_view') === false) || !(strpos($viewuri, 'admin/spreadsheet/file_view_share') === false) || !(strpos($viewuri, 'spreadsheet/file_view_share') === false)) {
        
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/ComboTree/comboTreePlugin.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/ComboTree/icontains.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/luckysheet/js/spectrum.min.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/luckysheet/js/plugin.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/luckysheet/js/luckysheet.umd.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/js/manage.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/luckysheet/js/vue.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/luckysheet/js/vuex.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/luckysheet/js/vuexx.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/luckysheet/js/index.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/luckysheet/js/echarts.min.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/luckysheet/js/chartmix.umd.min.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/FileSaver.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script  src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/excel.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script  src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/js/exports.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/js/upload_file.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
        echo '<script src="' . module_dir_url(SPREADSHEET_MODULE, 'assets/plugins/luckysheet/js/luckyexcel.js') . '?v=' . VERSION_SREADSHEET . '"></script>';
    }
}


function admin_url($url = '')
{
    
    if ($url == '' || $url == '/') {
        if ($url == '/') {
            $url = '';
        }
        
        return base_url() . '/';
    }
    if (!empty(client_id())) {
        return base_url() . $url;
    } else {
        return base_url() . 'admin/' . $url;
    }
}

if ( !function_exists('render_select')){
    function render_select($name, $options, $option_attrs = [], $label = '', $selected = '', $select_attrs = [], $form_group_attr = [], $form_group_class = '', $select_class = '', $include_blank = true)
    {
        $callback_translate = '';
        if (isset($options['callback_translate'])) {
            $callback_translate = $options['callback_translate'];
            unset($options['callback_translate']);
        }
        $select = '';
        $_form_group_attr = '';
        $_select_attrs = '';
        if (!isset($select_attrs['data-width'])) {
            $select_attrs['data-width'] = '100%';
        }
        if (!isset($select_attrs['data-none-selected-text'])) {
            $select_attrs['data-none-selected-text'] = lang('dropdown_non_selected_tex');
        }
        foreach ($select_attrs as $key => $val) {
            // tooltips
            if ($key == 'title') {
                $val = lang($val);
            }
            $_select_attrs .= $key . '=' . '"' . $val . '" ';
        }
        
        $_select_attrs = rtrim($_select_attrs);
        
        $form_group_attr['app-field-wrapper'] = $name;
        foreach ($form_group_attr as $key => $val) {
            // tooltips
            if ($key == 'title') {
                $val = lang($val);
            }
            $_form_group_attr .= $key . '=' . '"' . $val . '" ';
        }
        $_form_group_attr = rtrim($_form_group_attr);
        if (!empty($select_class)) {
            $select_class = ' ' . $select_class;
        }
        if (!empty($form_group_class)) {
            $form_group_class = ' ' . $form_group_class;
        }
        $select .= '<div class="select-placeholder form-group' . $form_group_class . '" ' . $_form_group_attr . '>';
        if ($label != '') {
            $select .= '<label for="' . $name . '" class="control-label">' . lang($label, '', false) . '</label>';
        }
        $select .= '<select id="' . $name . '" name="' . $name . '" class="selectpicker' . $select_class . '" ' . $_select_attrs . ' data-live-search="true">';
        if ($include_blank == true) {
            $select .= '<option value=""></option>';
        }
        foreach ($options as $option) {
            $val = '';
            $_selected = '';
            $key = '';
            if (isset($option[$option_attrs[0]]) && !empty($option[$option_attrs[0]])) {
                $key = $option[$option_attrs[0]];
            }
            if (!is_array($option_attrs[1])) {
                $val = $option[$option_attrs[1]];
            } else {
                foreach ($option_attrs[1] as $_val) {
                    $val .= $option[$_val] . ' ';
                }
            }
            $val = trim($val);
            
            if ($callback_translate != '') {
                if (function_exists($callback_translate) && is_callable($callback_translate)) {
                    $val = call_user_func($callback_translate, $key);
                }
            }
            
            $data_sub_text = '';
            if (!is_array($selected)) {
                if ($selected != '') {
                    if ($selected == $key) {
                        $_selected = ' selected';
                    }
                }
            } else {
                foreach ($selected as $id) {
                    if ($key == $id) {
                        $_selected = ' selected';
                    }
                }
            }
            if (isset($option_attrs[2])) {
                if (strpos($option_attrs[2], ',') !== false) {
                    $sub_text = '';
                    $_temp = explode(',', $option_attrs[2]);
                    foreach ($_temp as $t) {
                        if (isset($option[$t])) {
                            $sub_text .= $option[$t] . ' ';
                        }
                    }
                } else {
                    if (isset($option[$option_attrs[2]])) {
                        $sub_text = $option[$option_attrs[2]];
                    } else {
                        $sub_text = $option_attrs[2];
                    }
                }
                $data_sub_text = ' data-subtext=' . '"' . $sub_text . '"';
            }
            $data_content = '';
            if (isset($option['option_attributes'])) {
                foreach ($option['option_attributes'] as $_opt_attr_key => $_opt_attr_val) {
                    $data_content .= $_opt_attr_key . '=' . '"' . $_opt_attr_val . '"';
                }
                if ($data_content != '') {
                    $data_content = ' ' . $data_content;
                }
            }
            $select .= '<option value="' . $key . '"' . $_selected . $data_content . $data_sub_text . '>' . $val . '</option>';
        }
        $select .= '</select>';
        $select .= '</div>';
        
        return $select;
    }
}


function render_input($name, $label = '', $value = '', $type = 'text', $input_attrs = [], $form_group_attr = [], $form_group_class = '', $input_class = '')
{
    $input = '';
    $_form_group_attr = '';
    $_input_attrs = '';
    foreach ($input_attrs as $key => $val) {
        // tooltips
        if ($key == 'title') {
            $val = lang($val);
        }
        $_input_attrs .= $key . '=' . '"' . $val . '" ';
    }
    
    $_input_attrs = rtrim($_input_attrs);
    
    $form_group_attr['app-field-wrapper'] = $name;
    
    foreach ($form_group_attr as $key => $val) {
        // tooltips
        if ($key == 'title') {
            $val = lang($val);
        }
        $_form_group_attr .= $key . '=' . '"' . $val . '" ';
    }
    
    $_form_group_attr = rtrim($_form_group_attr);
    
    if (!empty($form_group_class)) {
        $form_group_class = ' ' . $form_group_class;
    }
    if (!empty($input_class)) {
        $input_class = ' ' . $input_class;
    }
    $input .= '<div class="form-group' . $form_group_class . '" ' . $_form_group_attr . '>';
    if ($label != '') {
        $input .= '<label for="' . $name . '" class="control-label">' . lang($label, '', false) . '</label>';
    }
    $input .= '<input type="' . $type . '" id="' . $name . '" name="' . $name . '" class="form-control' . $input_class . '" ' . $_input_attrs . ' value="' . set_value($name, $value) . '">';
    $input .= '</div>';
    
    return $input;
}


function get_related_moduleName_by_value($val, $proposal = null, $data_only = FALSE, $row = null)
{
    $CI = &get_instance();
    $CI->load->model('admin_model');
    $where = '';
    if (!empty($row)) {
        $where = array($val . '_id' => $row);
    }
    if ($val == 'project') {
        $all_project_info = $CI->admin_model->get_permission('tbl_project', $where, 'project_id as id, project_name as name');
        if ($data_only) {
            return $all_project_info;
        }
        $HTML = null;
        if ($all_project_info) {
            $HTML .= '<div class="col-sm-5"><select onchange="get_milestone_by_id(this.value)" name="' . $val . '_id" id="related_to"  class="form-control selectpicker m0 " data-live-search="true" >';
            foreach ($all_project_info as $v_project) {
                $HTML .= "<option value='" . $v_project->id . "'>" . $v_project->name . "</option>";
            }
            $HTML .= '</select></div>';
        }
        echo $HTML;
        exit();
    } elseif ($val == 'opportunities') {
        $HTML = null;
        $all_opp_info = $CI->admin_model->get_permission('tbl_opportunities', $where, 'opportunities_id as id, opportunity_name as name');
        if ($data_only) {
            return $all_opp_info;
        }
        if ($all_opp_info) {
            
            $HTML .= '<div class="col-sm-5"><select name="' . $val . '_id" id="related_to"  class="form-control selectpicker m0 " data-live-search="true">';
            foreach ($all_opp_info as $v_opp) {
                $HTML .= "<option value='" . $v_opp->id . "'>" . $v_opp->name . "</option>";
            }
            $HTML .= '</select></div>';
        }
        echo $HTML;
        exit();
    } elseif ($val == 'leads') {
        $all_leads_info = $CI->admin_model->get_permission('tbl_leads', $where, 'leads_id as id, lead_name as name');
        if ($data_only) {
            return $all_leads_info;
        }
        $HTML = null;
        if ($all_leads_info) {
            $HTML .= '<div class="col-sm-5"><select name="' . $val . '_id" id="related_to"  class="form-control selectpicker m0 " data-live-search="true">';
            foreach ($all_leads_info as $v_leads) {
                $HTML .= "<option value='" . $v_leads->id . "'>" . $v_leads->name . "</option>";
            }
            $HTML .= '</select></div>';
            if (!empty($proposal)) {
                $HTML .= '<div class="form-group ml0 mr0 pt-lg" style="margin-top: 35px"><label class="col-lg-3 control-label">' . lang("currency") . '</label><div class="col-lg-7"><select name="currency" class="form-control selectpicker m0 " data-live-search="true">';
                $all_currency = $CI->db->get('tbl_currencies')->result();
                foreach ($all_currency as $v_currency) {
                    $HTML .= "<option " . (config_item('default_currency') == $v_currency->code ? ' selected="selected"' : '') . " value='" . $v_currency->code . "'>" . $v_currency->name . "</option>";
                }
                $HTML .= '</select></div></div>';
            }
        }
        echo $HTML;
        exit();
    } elseif ($val == 'client') {
        $all_client_info = $CI->admin_model->get_permission('tbl_client', $where, 'client_id as id');
        if ($data_only) {
            return $all_client_info;
        }
        $HTML = null;
        if ($all_client_info) {
            $HTML .= '<div class="col-sm-7"><select name="' . $val . '_id" id="related_to"  class="form-control selectpicker m0 " data-live-search="true" required>';
            $HTML .= "<option value=''>" . lang('none') . "</option>";
            foreach ($all_client_info as $v_client) {
                $HTML .= "<option value='" . $v_client->id . "'>" . $v_client->name . "</option>";
            }
            $HTML .= '</select></div>';
        }
        echo $HTML;
        exit();
    } elseif ($val == 'supplier') {
        $all_supplier = $CI->admin_model->get_permission('tbl_suppliers', $where, 'supplier_id as id');
        if ($data_only) {
            return $all_supplier;
        }
        $HTML = null;
        if ($all_supplier) {
            $HTML .= '<div class="col-sm-7"><select  name="' . $val . '_id" id="related_to"  data-live-search="true" class="form-control selectpicker m0 ">';
            $HTML .= "<option value=''>" . lang('none') . "</option>";
            foreach ($all_supplier as $v_supplier) {
                $HTML .= "<option value='" . $v_supplier->id . "'>" . $v_supplier->name . "</option>";
            }
            $HTML .= '</select></div>';
        }
        echo $HTML;
        exit();
    } elseif ($val == 'bug') {
        $all_bugs_info = $CI->admin_model->get_permission('tbl_bug', $where, 'bug_id as id, bug_title as name');
        if ($data_only) {
            return $all_bugs_info;
        }
        $HTML = null;
        if ($all_bugs_info) {
            
            $HTML .= '<div class="col-sm-5"><select name="' . $val . '_id" id="related_to"  class="form-control selectpicker m0 " data-live-search="true">';
            foreach ($all_bugs_info as $v_bugs) {
                $HTML .= "<option value='" . $v_bugs->id . "'>" . $v_bugs->name . "</option>";
            }
            $HTML .= '</select></div>';
        }
        echo $HTML;
        exit();
    } elseif ($val == 'goal') {
        $whered = array('goal_tracking_id' => $row);
        $all_goal_info = $CI->admin_model->get_permission('tbl_goal_tracking', $whered, 'goal_tracking_id as id, subject as name');
        if ($data_only) {
            return $all_goal_info;
        }
        $HTML = null;
        if ($all_goal_info) {
            $HTML .= '<div class="col-sm-5"><select name="' . $val . '_tracking_id" id="related_to"  class="form-control selectpicker m0 " data-live-search="true">';
            foreach ($all_goal_info as $v_goal) {
                $HTML .= "<option value='" . $v_goal->id . "'>" . $v_goal->name . "</option>";
            }
            $HTML .= '</select></div>';
        }
        echo $HTML;
        exit();
    } elseif ($val == 'sub_task') {
        $all_task_info = $CI->admin_model->get_permission('tbl_task', $where, 'task_id as id, task_name as name');
        if ($data_only) {
            return $all_task_info;
        }
        $HTML = null;
        if ($all_task_info) {
            
            $HTML .= '<div class="col-sm-5"><select name="' . $val . '_id" id="related_to"  class="form-control selectpicker m0 " data-live-search="true">';
            foreach ($all_task_info as $v_task) {
                $HTML .= "<option value='" . $v_task->id . "'>" . $v_task->name . "</option>";
            }
            $HTML .= '</select></div>';
        }
        echo $HTML;
        exit();
    } elseif ($val == 'expenses') {
        $all_expenses = $CI->admin_model->get_permission('tbl_transactions', $where, 'transactions_id as id, name');
        if ($data_only) {
            return $all_expenses;
        }
        $HTML = null;
        if ($all_expenses) {
            $val = 'transactions_id';
            $HTML .= '<div class="col-sm-5"><select name="' . $val . '_id" id="related_to"  class="form-control selectpicker m0 " data-live-search="true">';
            foreach ($all_expenses as $expenses) {
                $HTML .= "<option value='" . $expenses->id . "'>" . $expenses->name . (!empty($expenses->reference) ? '#' . $expenses->reference : '') . "</option>";
            }
            $HTML .= '</select></div>';
        }
        echo $HTML;
        exit();
    }
}