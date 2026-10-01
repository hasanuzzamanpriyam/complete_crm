<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Woocommerce extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('woocommerce_model');
        $this->load->library(WOOCOMMERCE_MODULE . '/woocommerce_module');
    }
    
    public function index()
    {
        $data['title'] = lang('woocommerce');
        $data['active'] = 1;
        $this->load->view('admin/_layout_main', $data);
    }
    
    public function orders()
    {
        $data['title'] = lang('orders');
        $data['kpi'] = array(
            array('cls' => 'cyan', 'icon' => '&#128722;', 'val' => number_format($this->db->count_all('tbl_woocommerce_orders')), 'label' => lang('kpi_total_orders')),
            array('cls' => 'ok', 'icon' => '&#36;', 'val' => '$' . number_format((float) $this->db->query("SELECT COALESCE(SUM(total + 0), 0) AS s FROM tbl_woocommerce_orders WHERE status = 'completed'")->row()->s, 2), 'label' => lang('kpi_revenue_completed')),
            array('cls' => 'amb', 'icon' => '&#8987;', 'val' => number_format($this->db->where_in('status', array('pending', 'processing', 'on-hold'))->count_all_results('tbl_woocommerce_orders')), 'label' => lang('kpi_pending_processing')),
            array('cls' => 'ok', 'icon' => '&#10003;', 'val' => number_format($this->db->where('status', 'completed')->count_all_results('tbl_woocommerce_orders')), 'label' => lang('kpi_completed')),
        );
        $data['woo_stores'] = $this->db->order_by('store_name', 'ASC')->get('tbl_woocommerce_stores')->result();
        $data['subview'] = $this->load->view('woocommerce/orders', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }
    
    public function order($id = null)
    {
        $data['title'] = lang('woocommerce_order');
        $data['orders'] = $this->get_order($id);
        $data['row'] = get_row('tbl_woocommerce_orders', array('order_id' => $id));
        $data['store_name'] = '';
        if (!empty($data['row']->store_id)) {
            $store = $this->woocommerce_model->get_store_id($data['row']->store_id);
            if (!empty($store->store_name)) {
                $data['store_name'] = $store->store_name;
            }
        }
        $data['subview'] = $this->load->view('woocommerce/order', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }
    
    public function get_order($id)
    {
        $order = get_row('tbl_woocommerce_orders', array('order_id' => $id));
        if (!empty($order->store_id)) {
            $store = $this->woocommerce_model->get_store_id($order->store_id);
            $this->woocommerce_module->set_store($store);
            return $this->woocommerce_module->order($id);
        }
        // set mesage
        set_message('error', 'there is issue');
        redirect('admin/woocommerce/orders');
    }
    
    public function customers()
    {
        $data['title'] = lang('customers');
        $data['kpi'] = array(
            array('cls' => 'cyan', 'icon' => '&#128101;', 'val' => number_format($this->db->count_all('tbl_woocommerce_customers')), 'label' => lang('kpi_total_customers')),
            array('cls' => 'ok', 'icon' => '&#10003;', 'val' => number_format(woo_summary_paying()), 'label' => lang('kpi_paying')),
            array('cls' => 'amb', 'icon' => '&#127978;', 'val' => number_format($this->db->count_all('tbl_woocommerce_stores')), 'label' => lang('kpi_active_stores')),
            array('cls' => 'pur', 'icon' => '&#128230;', 'val' => number_format($this->db->count_all('tbl_woocommerce_orders')), 'label' => lang('kpi_orders')),
        );
        $data['woo_stores'] = $this->db->order_by('store_name', 'ASC')->get('tbl_woocommerce_stores')->result();
        $data['subview'] = $this->load->view('woocommerce/customers', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }
    
    public function products()
    {
        $data['title'] = lang('products');
        $data['active'] = 1;
        $data['kpi'] = array(
            array('cls' => 'cyan', 'icon' => '&#128230;', 'val' => number_format($this->db->count_all('tbl_woocommerce_products')), 'label' => lang('kpi_total_products')),
            array('cls' => 'ok', 'icon' => '&#10003;', 'val' => number_format($this->db->where('status', 'publish')->count_all_results('tbl_woocommerce_products')), 'label' => lang('kpi_published')),
            array('cls' => 'amb', 'icon' => '&#9998;', 'val' => number_format($this->db->where('status', 'draft')->count_all_results('tbl_woocommerce_products')), 'label' => lang('kpi_draft')),
            array('cls' => 'pur', 'icon' => '&#36;', 'val' => number_format((float) $this->db->query("SELECT COALESCE(SUM(sales + 0), 0) AS s FROM tbl_woocommerce_products")->row()->s), 'label' => lang('kpi_total_sales')),
        );
        $data['woo_stores'] = $this->db->order_by('store_name', 'ASC')->get('tbl_woocommerce_stores')->result();
        $data['subview'] = $this->load->view('woocommerce/products', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }
    
    public function stores($id = null)
    {
        $data['title'] = lang('stores');
        $data['kpi'] = array(
            array('cls' => 'cyan', 'icon' => '&#127978;', 'val' => number_format($this->db->count_all('tbl_woocommerce_stores')), 'label' => lang('kpi_total_stores')),
            array('cls' => 'ok', 'icon' => '&#128722;', 'val' => number_format(woo_summary_totals('orders')), 'label' => lang('kpi_synced_orders')),
            array('cls' => 'amb', 'icon' => '&#128230;', 'val' => number_format(woo_summary_totals('products')), 'label' => lang('kpi_synced_products')),
            array('cls' => 'pur', 'icon' => '&#128101;', 'val' => number_format(woo_summary_totals('customers')), 'label' => lang('kpi_synced_customers')),
        );
        $data['subview'] = $this->load->view('woocommerce/stores', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }
    
    public function test_connection($storeId)
    {
        $store = get_row('tbl_woocommerce_stores', array('store_id' => $storeId));
        $this->woocommerce_module->set_store($store);
        $response = $this->woocommerce_module->products();
        if (is_array($response)) {
            $result['success'] = true;
            $result['message'] = lang("connect_success");
            echo json_encode($result);
        } else {
            $result['success'] = false;
            $result['message'] = $response;
            echo json_encode($result);
        }
        exit();
    }
    
    public function refresh($store_id)
    {
        $store = get_row('tbl_woocommerce_stores', array('store_id' => $store_id));
        $this->woocommerce_module->set_store($store);
        $Product_no = $store->productPage;
        $Order_no = $store->orderPage;
        $Customer_no = $store->customerPage;
        summary($store);
        checkProducts($Product_no, $store);
        checkCustomers($Customer_no, $store);
        checkOrders($Order_no, $store);
        $result['success'] = true;
        $result['message'] = lang("woocommerce_check_successful");
        echo json_encode($result);
        exit();
    }
    
    public function reset($store_id, $return = null)
    {
        $this->woocommerce_model->empty_store($store_id);
        woo_update_pageno('productPage', 1, $store_id);
        woo_update_pageno('orderPage', 1, $store_id);
        woo_update_pageno('customerPage', 1, $store_id);
        if (!empty($return)) {
            return true;
        } else {
            $result['success'] = true;
            $result['message'] = lang("woocommerce_reset_successful");
            echo json_encode($result);
            exit();
        }
    }
    
    public function storeslist()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('datatables');
            $this->datatables->table = 'tbl_woocommerce_stores';
            // D10: `assignees` is not a column — it broke every stores search
            $this->datatables->column_order = array(
                'tbl_woocommerce_stores.store_name',
                'tbl_woocommerce_stores.store_id',
                'tbl_woocommerce_stores.store_id',
                'tbl_woocommerce_stores.date_created',
                'tbl_woocommerce_stores.store_id',
            );
            $this->datatables->column_search = array(
                'tbl_woocommerce_stores.store_name',
                'tbl_woocommerce_stores.url',
                'tbl_woocommerce_stores.date_created',
                'tbl_woocommerce_stores.store_id',
            );
            $where = array();
            $fetch_data = make_datatables($where);
            $data = array();
            foreach ($fetch_data as $_key => $v_announcements) {
                $e = function ($x) { return htmlspecialchars((string) $x, ENT_QUOTES, 'UTF-8'); };
                $userDetail = join_data('tbl_woocommerce_assigned', 'tbl_account_details.fullname', array('tbl_woocommerce_assigned.store_id' => $v_announcements->store_id), array('tbl_account_details' => 'tbl_account_details.user_id=tbl_woocommerce_assigned.user_id'), 'object');
                $asg = '<div class="assignees">';
                if (!empty($userDetail)) {
                    foreach ($userDetail as $value) {
                        $asg .= '<span class="asg">' . $e($value->fullname) . '</span>';
                    }
                }
                $asg .= '</div>';
                $synced = '<div class="synced">'
                    . '<span><b>' . number_format(woo_store_synced($v_announcements->store_id, 'orders')) . '</b> ' . lang('orders') . '</span>'
                    . '<span><b>' . number_format(woo_store_synced($v_announcements->store_id, 'products')) . '</b> ' . lang('products') . '</span>'
                    . '<span><b>' . number_format(woo_store_synced($v_announcements->store_id, 'customers')) . '</b> ' . lang('customers') . '</span>'
                    . '</div>';
                $action = '<div class="acts">';
                $action .= '<a data-id=' . $v_announcements->store_id . ' href="" onclick="updateWooStore(this)"'
                    . ' class="btn btn-primary btn-xs" title="' . lang('check_update') . '" data-toggle="modal"'
                    . '><span class="fa fa-refresh"></span></a> ';
                $action .= '<a data-id=' . $v_announcements->store_id . ' href="" onclick="wooco_test(this)"'
                    . ' class="btn btn-info btn-xs" title="' . lang('test_connection') . '" data-toggle="modal"'
                    . '><span class="fa fa-play"></span></a> ';
                $action .= '<a href="' . base_url() . 'admin/woocommerce/new_stores/' . $v_announcements->store_id . '"'
                    . ' class="btn btn-primary btn-xs" title="' . lang('edit') . '" data-toggle="modal"'
                    . ' data-target="#myModal_lg"><span class="fa fa-pencil-square-o"></span></a> ';
                $action .= '<a data-id=' . $v_announcements->store_id . ' href="" onclick="woo_reset(this)"'
                    . ' class="btn btn-warning btn-xs" title="' . lang('reset') . '" data-toggle="modal"'
                    . '><span class="fa fa-recycle"></span></a> ';
                $action .= ajax_anchor(base_url('admin/woocommerce/delete_stores/' . $v_announcements->store_id), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_" . $_key)) . ' ';
                $action .= '</div>';
                $sub_array = array();
                $sub_array[] = '<div class="cell-main"><div><div class="nm">' . $e($v_announcements->store_name) . '</div>'
                    . '<div class="sub">' . $e($v_announcements->url) . '</div></div></div>';
                $sub_array[] = $asg;
                $sub_array[] = $synced;
                $sub_array[] = '<span class="mono col-soft">' . display_date($v_announcements->date_created) . '</span>';
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
            render_table($data, $where);
        } else {
            redirect('admin/dashboard');
        }
    }
    
    public function productlist()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('datatables');
            $this->datatables->table = 'tbl_woocommerce_products';
            $this->datatables->join_table = array('tbl_woocommerce_stores');
            $this->datatables->join_where = array('tbl_woocommerce_products.store_id=tbl_woocommerce_stores.store_id');
            $this->datatables->select = 'tbl_woocommerce_products.*,tbl_woocommerce_stores.store_name as store_name';
            $this->datatables->column_order = array(
                'tbl_woocommerce_products.name',
                'tbl_woocommerce_products.price',
                'tbl_woocommerce_products.sales',
                'tbl_woocommerce_products.status',
                'tbl_woocommerce_stores.store_name',
                'tbl_woocommerce_products.id',
            );
            $this->datatables->column_search = array(
                'tbl_woocommerce_products.name',
                'tbl_woocommerce_products.sku',
                'tbl_woocommerce_products.category',
                'tbl_woocommerce_products.status',
                'tbl_woocommerce_products.price',
                'tbl_woocommerce_products.sales',
                'tbl_woocommerce_stores.store_name',
                'tbl_woocommerce_products.id',
            );
            $where = array();
            if (!empty($_POST['woo_status'])) {
                $where['tbl_woocommerce_products.status'] = $_POST['woo_status'];
            }
            if (!empty($_POST['woo_store'])) {
                $where['tbl_woocommerce_products.store_id'] = $_POST['woo_store'];
            }
            $fetch_data = make_datatables($where);
            $data = array();
            foreach ($fetch_data as $_key => $v_announcements) {
                $e = function ($x) { return htmlspecialchars((string) $x, ENT_QUOTES, 'UTF-8'); };
                $sub_array = array();
                $sub_array[] = '<div class="cell-main">'
                    . '<img class="thumb" src="' . $e($v_announcements->picture) . '" alt=""'
                    . ' onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'grid\'">'
                    . '<div class="thumb-fb">' . woo_initials($v_announcements->name) . '</div>'
                    . '<div><div class="nm">' . $e($v_announcements->name) . '</div>'
                    . '<div class="sub">' . $e(woo_product_sub($v_announcements->sku, $v_announcements->category)) . '</div></div>'
                    . '</div>';
                $sub_array[] = '<span class="num">' . display_money($v_announcements->price, '$') . '</span>';
                $sub_array[] = '<span class="num col-soft">' . number_format((int) $v_announcements->sales) . '</span>';
                $sub_array[] = woo_pill($v_announcements->status);
                $sub_array[] = woo_store_tag($v_announcements->store_name);
                $action = '<div class="acts">';
                $action .= '<a target="_blank" href="' . $e($v_announcements->permalink) . '"'
                    . ' class="btn btn-primary btn-xs" title="' . lang('view') . '"><span class="fa fa-list-alt"></span></a> ';
                $action .= '<a href="' . base_url() . 'woocommerce/edit_products/' . $v_announcements->id . '"'
                    . ' class="btn btn-primary btn-xs" title="' . lang('edit') . '" data-toggle="modal"'
                    . ' data-target="#myModal_lg"><span class="fa fa-pencil-square-o"></span></a> ';
                $action .= '<a href="' . base_url() . 'woocommerce/delete/products/' . $v_announcements->product_id . '/' . $v_announcements->id . '"'
                    . ' class="btn btn-danger btn-xs" title="' . lang('delete') . '"><span class="fa fa-trash-o"></span></a>';
                $action .= '</div>';
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
            render_table($data, $where);
        } else {
            redirect('admin/dashboard');
        }
    }
    
    public function customerlist()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('datatables');
            $this->datatables->table = 'tbl_woocommerce_customers';
            $this->datatables->join_table = array('tbl_woocommerce_stores');
            $this->datatables->join_where = array('tbl_woocommerce_customers.store_id=tbl_woocommerce_stores.store_id');
            $this->datatables->select = 'tbl_woocommerce_customers.*,tbl_woocommerce_stores.store_name as store_name';
            // D1: real column is `phone` (phone_number does not exist)
            $this->datatables->column_order = array(
                'tbl_woocommerce_customers.last_name',
                'tbl_woocommerce_customers.email',
                'tbl_woocommerce_customers.phone',
                'tbl_woocommerce_stores.store_name',
                'tbl_woocommerce_customers.id',
                'tbl_woocommerce_customers.id',
            );
            $this->datatables->column_search = array(
                'tbl_woocommerce_customers.username',
                'tbl_woocommerce_customers.first_name',
                'tbl_woocommerce_customers.last_name',
                'tbl_woocommerce_customers.phone',
                'tbl_woocommerce_customers.email',
                'tbl_woocommerce_stores.store_name',
                'tbl_woocommerce_customers.id',
            );
            $where = array();
            if (!empty($_POST['woo_store'])) {
                $where['tbl_woocommerce_customers.store_id'] = $_POST['woo_store'];
            }
            $fetch_data = make_datatables($where);
            $data = array();
            foreach ($fetch_data as $_key => $v_announcements) {
                $e = function ($x) { return htmlspecialchars((string) $x, ENT_QUOTES, 'UTF-8'); };
                $nm = trim($v_announcements->first_name . ' ' . $v_announcements->last_name);
                if (!empty($v_announcements->avatar_url)) {
                    $av = '<img class="av" src="' . $e($v_announcements->avatar_url) . '" alt=""'
                        . ' onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'grid\'">'
                        . '<div class="av-fb">' . woo_initials($nm) . '</div>';
                } else {
                    $av = '<span class="av av-ini">' . woo_initials($nm) . '</span>';
                }
                $ordersCount = (int) $this->db->where('customer_id', $v_announcements->woo_customer_id)
                    ->count_all_results('tbl_woocommerce_orders');
                $sub_array = array();
                $sub_array[] = '<div class="cell-main">' . $av
                    . '<div><div class="nm">' . $e($nm !== '' ? $nm : $v_announcements->username) . '</div>'
                    . '<div class="sub">' . $e($v_announcements->username) . '</div></div></div>';
                $sub_array[] = '<span class="col-soft">' . $e($v_announcements->email) . '</span>';
                $sub_array[] = '<span class="mono col-soft">' . ($v_announcements->phone !== '' && $v_announcements->phone !== null ? $e($v_announcements->phone) : '—') . '</span>';
                $sub_array[] = woo_store_tag($v_announcements->store_name);
                $sub_array[] = '<span class="num">' . number_format($ordersCount) . '</span>';
                $action = '<div class="acts">';
                $action .= '<a href="' . base_url() . 'woocommerce/edit_customer/' . $v_announcements->id . '"'
                    . ' class="btn btn-primary btn-xs" title="' . lang('edit') . '" data-toggle="modal"'
                    . ' data-target="#myModal_lg"><span class="fa fa-pencil-square-o"></span></a> ';
                $action .= '<a href="' . base_url() . 'woocommerce/delete/customers/' . $v_announcements->woo_customer_id . '/' . $v_announcements->id . '"'
                    . ' class="btn btn-danger btn-xs" title="' . lang('delete') . '"><span class="fa fa-trash-o"></span></a>';
                $action .= '</div>';
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
            render_table($data, $where);
        } else {
            redirect('admin/dashboard');
        }
    }
    
    
    public function edit_products($id = null)
    {
        if (!empty($id)) {
            $data['products'] = $this->db->where('id', $id)->get('tbl_woocommerce_products')->row();
            if (empty($data['products'])) {
                $type = "error";
                $message = "No Record Found";
                set_message($type, $message);
                redirect(base_url('woocommerce/edit_products'));
            }
        }
        $data['subview'] = $this->load->view('woocommerce/edit_products', $data, FALSE);
        $this->load->view('admin/_layout_modal_lg', $data);
    }
    
    public function edit_customer($id = null)
    {
        if (!empty($id)) {
            $data['customers'] = $this->db->where('id', $id)->get('tbl_woocommerce_customers')->row();
            if (empty($data['customers'])) {
                $type = "error";
                $message = "No Record Found";
                set_message($type, $message);
                redirect(base_url('woocommerce/edit_products'));
            }
        }
        $data['subview'] = $this->load->view('woocommerce/edit_customer', $data, FALSE);
        $this->load->view('admin/_layout_modal_lg', $data);
    }
    
    public function new_stores($id = null)
    {
        
        $data['title'] = lang('new') . ' ' . lang('stores'); //Page title
        if (!empty($id)) {
            $data['stores'] = get_row('tbl_woocommerce_stores', array('store_id' => $id));
            $data['selectUserId'] = get_any_field('tbl_woocommerce_assigned', array('store_id' => $id), 'user_id');
            if (empty($data['stores'])) {
                $type = "error";
                $message = "No Record Found";
                set_message($type, $message);
                redirect(base_url('admin/woocommerce/new_stores'));
            }
        }
        $data['subview'] = $this->load->view('woocommerce/new_stores', $data, FALSE);
        $this->load->view('admin/_layout_modal_lg', $data);
    }
    
    public function create_stores($id = null)
    {
        $data = array();
        $data['store_name'] = '';
        $data['url'] = '';
        $data['key'] = '';
        $data['secret'] = '';
        if ($id) {
            $rata = $this->db->where('store_id', $id)->get('tbl_woocommerce_stores')->row();
            $data['store_name'] = $rata->store_name;
            $data['url'] = $rata->url;
            $data['key'] = $rata->key;
            $data['secret'] = $rata->secret;
        }
        $this->load->library('form_validation');
        $this->form_validation->set_rules('store_name', 'Store Name', 'trim|required');
        $this->form_validation->set_rules('url', 'Woocommerce URL', 'trim|required');
        $this->form_validation->set_rules('key', 'Woocommerce Key', 'trim|required');
        $this->form_validation->set_rules('secret', 'Woocommerce Secret', 'trim|required');
        if ($this->form_validation->run() == true) {
            
            $data['store_name'] = $this->input->post('store_name');
            $data['url'] = $this->input->post('url', true);
            $data['key'] = $this->input->post('key', true);
            $data['secret'] = $this->input->post('secret', true);
            $user_id = $this->input->post('assignees', true);
            $data['date_created'] = date('Y-m-d H:i:s');
            // update root category
            $where = array('key' => $data['key'], 'secret' => $data['secret']);
            // duplicate value check in DB
            if (!empty($id)) { // if id exist in db update data
                $store_id = array('store_id !=' => $id);
            } else { // if id is not exist then set id as null
                $store_id = null;
            }
            // check whether this input data already exist or not
            $check_account = $this->woocommerce_model->check_update('tbl_woocommerce_stores', $where, $store_id);
            if (!empty($check_account)) { // if input data already exist show error alert
                // massage for user
                $type = 'error';
                $msg = "<strong style='color:#000'>" . $data['key'] . '</strong>  ' . lang('already_exist');
            } else { // save and update query
                
                $this->woocommerce_model->_table_name = 'tbl_woocommerce_stores';
                $this->woocommerce_model->_primary_key = 'store_id';
                if ($id) {
                    $store_id = $this->woocommerce_model->save($data, $id);
                    if ($store_id) {
                        $msg = lang('woocommerce_store_updated');
                        $type = "success";
                    }
                } else {
                    $store_id = $this->woocommerce_model->save($data);
                    if ($store_id) {
                        $msg = lang('woocommerce_store_added');
                        $type = "success";
                    }
                }
                $this->woocommerce_model->_table_name = 'tbl_woocommerce_assigned';
                $this->woocommerce_model->_primary_key = 'id';
                $this->woocommerce_model->delete_multiple(array('store_id' => $store_id));
                if (!empty($user_id)) {
                    foreach ($user_id as $v_user) {
                        $assign_data['user_id'] = $v_user;
                        $assign_data['store_id'] = $store_id;
                        $this->woocommerce_model->save($assign_data);
                    }
                }
            }
        }
        set_message($type, $msg);
        redirect(base_url('woocommerce/stores'));
    }
    
    public function delete_stores($id = NULL)
    {
        if (empty($id)) {
            $type = "error";
            $message = lang('no_record_found');
            set_message($type, $message);
            redirect(base_url('woocommerce/stores'));
        } else {
            // delete tbl_woocommerce_assigned data by store_id
            $this->reset($id, true);
            $this->db->delete('tbl_woocommerce_assigned', array('store_id' => $id));
            $this->db->delete('tbl_woocommerce_stores', array('store_id' => $id));
            $type = "success";
            $message = lang('woocommerce_stores_delete');
            echo json_encode(array("status" => $type, 'message' => $message));
            exit();
        }
    }
    
    public function update($scope)
    {
        $this->load->library('form_validation');
        if ($scope == 'product') {
            $id = $this->input->post('productId');
            $productInfo = get_row('tbl_woocommerce_products', array('product_id' => $id));
            $store = $this->woocommerce_model->get_store_id($productInfo->store_id);
            $store = $this->db->where('store_id', $productInfo->store_id)->get('tbl_woocommerce_stores')->row_object();
            $this->woocommerce_module->set_store($store);
            
            $data = [];
            $data['name'] = $this->input->post('name');
            $data['regular_price'] = $this->input->post('regular_price');
            $data['status'] = $this->input->post('status');
            $data['short_description'] = $this->input->post('short_description');
            $response = $this->woocommerce_module->update($id, $data, $scope);
            if (is_string($response)) {
                set_message('error', lang('failed') . ': ' . $response);
            } else {
                $this->woocommerce_model->update_product($id, $data, $productInfo->store_id);
                $type = "success";
                $message = lang("update_successfully");
                set_message($type, $message);
            }
            redirect(base_url('woocommerce/products'));
        }
        
        if ($scope == 'customer') {
            $id = $this->input->post('custId');
            $data = [];
            $data['username'] = $this->input->post('username');
            $data['email'] = $this->input->post('email');
            $data['first_name'] = $this->input->post('first_name');
            $data['last_name'] = $this->input->post('last_name');
            $customerInfo = get_row('tbl_woocommerce_customers', array('woo_customer_id' => $id));
            $store = $this->woocommerce_model->get_store_id($customerInfo->store_id);
            $this->woocommerce_module->set_store($store);
            
            $response = $this->woocommerce_module->update($id, $data, $scope);
            if (is_string($response)) {
                set_message('error', lang('failed') . ': ' . $response);
            } else {
                $this->woocommerce_model->update_customer($id, $data, $customerInfo->store_id);
                $type = "success";
                $message = lang("update_successfully");
                set_message($type, $message);
            }
            summary($store);
            redirect(base_url('woocommerce/customers'));
        }
    }
    
    public function edit_orders($id = null)
    {
        if (!empty($id)) {
            $data['orders'] = $this->db->where('id', $id)->get('tbl_woocommerce_orders')->row();
            if (empty($data['orders'])) {
                $type = "error";
                $message = "No Record Found";
                set_message($type, $message);
                redirect(base_url('woocommerce/edit_orders'));
            }
        }
        $data['subview'] = $this->load->view('woocommerce/edit_order', $data, FALSE);
        $this->load->view('admin/_layout_modal_lg', $data);
    }
    
    public function update_orders()
    {
        $orderId = $this->input->post('orderId');
        $order = get_row('tbl_woocommerce_orders', array('order_id' => $orderId));
        $store = $this->woocommerce_model->get_store_id($order->store_id);
        $this->woocommerce_module->set_store($store);
        $data = $this->input->post();
        if (count($data) == 3) {
            $update = $this->woocommerce_module->update_order($data);
            if (is_string($update)) {
                set_message('error', lang('Something_went_wrong') . $update);
            } else {
                $this->woocommerce_model->update_order($data, $order->store_id);
                summary($store);
                $type = "success";
                $message = lang("update_successfully");
                set_message($type, $message);
            }
        }
        redirect(base_url('woocommerce/orders'));
    }
    
    
    public function orderslist()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('datatables');
            $this->datatables->table = 'tbl_woocommerce_orders';
            $this->datatables->join_table = array('tbl_woocommerce_customers', 'tbl_woocommerce_stores');
            $this->datatables->join_where = array(
                'tbl_woocommerce_orders.customer_id=tbl_woocommerce_customers.woo_customer_id AND tbl_woocommerce_customers.store_id=tbl_woocommerce_orders.store_id', // D8: scope customer join per store (kills 1913-vs-1496 fan-out)
                'tbl_woocommerce_orders.store_id=tbl_woocommerce_stores.store_id',
            );
            $this->datatables->select = 'tbl_woocommerce_orders.*,tbl_woocommerce_orders.id as orderID,tbl_woocommerce_customers.*,tbl_woocommerce_customers.phone as customer_phone,tbl_woocommerce_stores.store_name as store_name';
            // D9: every column qualified (unqualified `phone` is ambiguous between orders + customers)
            $this->datatables->column_order = array(
                'tbl_woocommerce_orders.order_number',
                'tbl_woocommerce_customers.last_name',
                'tbl_woocommerce_stores.store_name',
                'tbl_woocommerce_orders.total',
                'tbl_woocommerce_orders.status',
                'tbl_woocommerce_orders.date_created',
                'tbl_woocommerce_orders.id',
            );
            $this->datatables->column_search = array(
                'tbl_woocommerce_orders.order_number',
                'tbl_woocommerce_customers.first_name',
                'tbl_woocommerce_customers.last_name',
                'tbl_woocommerce_customers.email',
                'tbl_woocommerce_customers.username',
                'tbl_woocommerce_orders.address',
                'tbl_woocommerce_orders.phone',
                'tbl_woocommerce_orders.status',
                'tbl_woocommerce_orders.total',
                'tbl_woocommerce_orders.date_created',
                'tbl_woocommerce_orders.currency',
                'tbl_woocommerce_stores.store_name',
                'tbl_woocommerce_orders.id',
            );
            $where = array();
            if (!empty($_POST['woo_status'])) {
                $where['tbl_woocommerce_orders.status'] = $_POST['woo_status'];
            }
            if (!empty($_POST['woo_store'])) {
                $where['tbl_woocommerce_orders.store_id'] = $_POST['woo_store'];
            }
            $fetch_data = make_datatables($where);
            $data = array();
            foreach ($fetch_data as $_key => $v_announcements) {
                $e = function ($x) { return htmlspecialchars((string) $x, ENT_QUOTES, 'UTF-8'); };
                $name = trim($v_announcements->first_name . ' ' . $v_announcements->last_name);
                $sub = $v_announcements->email ?: $v_announcements->customer_phone ?: explode(',', $v_announcements->address)[0];
                $sub = trim((string) $sub) !== '' ? $sub : '—';
                $sub_array = array();
                $sub_array[] = '<b class="mono">#' . $e($v_announcements->order_number) . '</b>';
                $sub_array[] = '<div class="cell-main"><div><div class="nm">'
                    . $e($name !== '' ? $name : ($v_announcements->email ?: lang('guest_checkout')))
                    . '</div><div class="sub">' . $e($sub) . '</div></div></div>';
                $sub_array[] = woo_store_tag($v_announcements->store_name);
                $sub_array[] = '<span class="num">' . $e($v_announcements->currency . ' ' . $v_announcements->total) . '</span>';
                $sub_array[] = woo_pill($v_announcements->status);
                $sub_array[] = '<span class="mono col-soft">' . display_date($v_announcements->date_created) . '</span>';
                $action = '<div class="acts">';
                $action .= '<a href="' . base_url() . 'admin/woocommerce/order/' . $v_announcements->order_id . '"'
                    . ' class="btn btn-primary btn-xs" title="' . lang('view') . '"><span class="fa fa-list-alt"></span></a> ';
                $action .= '<a href="' . base_url() . 'admin/woocommerce/edit_orders/' . $v_announcements->orderID . '"'
                    . ' class="btn btn-primary btn-xs" title="' . lang('edit') . '" data-toggle="modal"'
                    . ' data-target="#myModal_lg"><span class="fa fa-pencil-square-o"></span></a> ';
                $action .= '<a href="' . base_url() . 'admin/woocommerce/delete/orders/' . $v_announcements->order_id . "/" . $v_announcements->id . '"'
                    . ' class="btn btn-danger btn-xs" title="' . lang('delete') . '"><span class="fa fa-trash-o"></span></a>';
                $action .= '</div>';
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
            render_table($data, $where);
        } else {
            redirect('admin/dashboard');
        }
    }
    
    public function delete($scope, $product_id, $id)
    {
        $productInfo = get_row('tbl_woocommerce_products', array('product_id' => $product_id));
        
        $store = $this->woocommerce_model->get_store_id($productInfo->store_id);
        $this->woocommerce_module->set_store($store);
        
        $data = $product_id;
        $this->woocommerce_module->delete($data, $scope);
        $this->woocommerce_model->delete_all_data($data, $scope, $productInfo->store_id);
        summary($store);
        $type = "success";
        $message = $scope . ' ' . lang("delete_successfully");
        set_message($type, $message);
        redirect(base_url("woocommerce/{$scope}"));
    }
}
