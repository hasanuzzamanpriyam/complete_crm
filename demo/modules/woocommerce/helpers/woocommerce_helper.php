<?php defined('BASEPATH') or exit('No direct script access allowed');

function summary($store)
{
    $storeId = $store->store_id;
    $CI = &get_instance();
    $CI->load->model('woocommerce/woocommerce_model', 'wooDB');
    $customers = $CI->woocommerce_module->cronReport('customers/totals');
    $data = [];
    if (is_array($customers)) {
        $data['customers'] = json_encode($customers);
    }
    
    $products = $CI->woocommerce_module->cronReport('products/totals');
    if (is_array($products)) {
        $data['products'] = json_encode($products);
    }
    
    $orders = $CI->woocommerce_module->cronReport('orders/totals');
    if (is_array($orders)) {
        $data['orders'] = json_encode($orders);
    }
    if (!empty($data)) {
        if ($CI->wooDB->is_summary_exist($storeId)) {
            $success = $CI->wooDB->update_summary($data, $storeId);
        } else {
            $success = $CI->wooDB->add_summary($data, $storeId);
        }
    }
}

function woo_update_pageno($field, $npage, $storeId)
{
    $CI = &get_instance();
    $CI->db->set($field, $npage);
    $CI->db->where('store_id', $storeId);
    $CI->db->update('tbl_woocommerce_stores');
}

function checkProducts($page, $store)
{
    $storeId = $store->store_id;
    $CI = &get_instance();
    $CI->load->model('woocommerce/woocommerce_model', 'wooDB');
    $pdata['orderby'] = 'date';
    $pdata['per_page'] = 100;
    $stopPage = $page + 2;
    $pdata['page'] = $page;
    while ($page <= $stopPage) {
        $pdata['page'] = $page;
        
        $products = $CI->woocommerce_module->cron('products', $pdata);
        if (is_array($products)) {
            if (empty($products)) {
                break;
            }
            foreach ($products as $product) {
                $data['product_id'] = $product->id;
                $data['name'] = $product->name;
                $data['sales'] = $product->total_sales;
                $data['price'] = $product->price;
                $data['sku'] = $product->sku;
                $data['status'] = $product->status;
                $data['permalink'] = $product->permalink;
                $data['picture'] = (!empty($product->images)) ? $product->images[0]->src : '';
                $data['category'] = (!empty($product->categories)) ? json_encode($product->categories) : '';
                $data['date_created'] = $product->date_created;
                $data['type'] = $product->type;
                if (!$CI->wooDB->is_wooproduct_exist($data['product_id'], $storeId)) {
                    $CI->wooDB->cron_products($data, $storeId);
                } else {
                    $CI->wooDB->cron_updates($data, 'products', $storeId);
                }
            }
        } else {
            return;
        }
        $page++;
    }
    $npage = $page - 1;
    if ($npage < 1) {
        $npage = 1;
    }
    
    woo_update_pageno('orderPage', $npage, $storeId);
}

function woocommerce_cron()
{
    
    $CI = &get_instance();
    $CI->load->library('woocommerce/woocommerce_module');
    $CI->load->model('woocommerce/woocommerce_model', 'wooDB');
    $CI->load->model('woocommerce/stores_model', 'stm');
    
    $stores = $CI->stm->get_stores();
    
    foreach ($stores as $store) {
        $CI->woocommerce_module->set_store($store);
        
        $Product_no = $store->productPage;
        $Order_no = $store->orderPage;
        $Customer_no = $store->customerPage;
        summary($store);
        checkProducts($Product_no, $store);
        checkCustomers($Customer_no, $store);
        checkOrders($Order_no, $store);
    }
}

function checkCustomers($page, $store)
{
    $storeId = $store->store_id;
    $CI = &get_instance();
    $CI->load->model('woocommerce/woocommerce_model', 'wooDB');
    $pdata['orderby'] = 'registered_date';
    $pdata['per_page'] = 100;
    $stopPage = $page + 2;
    $pdata['page'] = $page;
    while ($page <= $stopPage) {
        $pdata['page'] = $page;
        
        $customers = $CI->woocommerce_module->cron('customers', $pdata);
        if (is_array($customers)) {
            if (empty($customers)) {
                break;
            }
            foreach ($customers as $customer) {
                $data['woo_customer_id'] = $customer->id;
                $data['email'] = $customer->email;
                $data['first_name'] = $customer->first_name;
                $data['last_name'] = $customer->last_name;
                $data['phone'] = $customer->billing->phone;
                $data['role'] = $customer->role;
                $data['username'] = $customer->username;
                $data['avatar_url'] = $customer->avatar_url;
                if (!$CI->wooDB->is_woocustomer_exist($data['woo_customer_id'], $storeId)) {
                    $CI->wooDB->cron_customers($data, $storeId);
                } else {
                    $CI->wooDB->cron_updates($data, 'customers', $storeId);
                }
            }
        } else {
            return;
        }
        $page++;
    }
    $npage = $page - 1;
    if ($npage < 1) {
        $npage = 1;
    }
    
    woo_update_pageno('customerPage', $npage, $storeId);
}

function checkOrders($page, $store)
{
    
    $storeId = $store->store_id;
    $CI = &get_instance();
    $CI->load->model('woocommerce/woocommerce_model', 'wooDB');
    $pdata['orderby'] = 'date';
    $pdata['per_page'] = 100;
    $stopPage = $page + 2;
    
    while ($page <= $stopPage) {
        $pdata['page'] = $page;
        
        $orders = $CI->woocommerce_module->cron('orders', $pdata);
        if (is_array($orders)) {
            if (empty($orders)) {
                break;
            }
            foreach ($orders as $order) {
                $data['order_id'] = $order->id;
                $data['order_number'] = $order->number;
                $data['customer_id'] = $order->customer_id;
                $data['address'] = $order->billing->address_1 . ', ' . $order->billing->city . ', ' . $order->billing->state . ', ' . $order->billing->country;
                $data['phone'] = $order->billing->phone;
                $data['status'] = $order->status;
                $data['currency'] = $order->currency;
                $data['total'] = $order->total;
                $data['date_created'] = $order->date_created;
                $data['date_modified'] = $order->date_modified;
                if (!$CI->wooDB->is_wooorder_exist($data['order_id'], $storeId)) {
                    $CI->wooDB->cron_orders($data, $storeId);
                } else {
                    $CI->wooDB->cron_updates($data, 'orders', $storeId);
                }
            }
        } else {
            return;
        }
        $page++;
    }
    
    $npage = $page - 1;
    if ($npage < 1) {
        $npage = 1;
    }
    
    woo_update_pageno('productPage', $npage, $storeId);
}


function active_store_id($staff_id = '')
{
    $staff_id = (!$staff_id == '') ? $staff_id : my_id();
    $staff = MyDetails($staff_id);
    return $staff->store_id;
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

function _d($date)
{
    $formatted = '';
    
    if ($date == '' || is_null($date) || $date == '0000-00-00') {
        return $formatted;
    }
    
    if (strpos($date, ' ') !== false) {
        return _dt($date);
    }
    
    $format = get_current_date_format();
    $dateTime = new DateTime($date);
    $formatted = $dateTime->format(str_replace('%', '', $format));
    
    return hooks()->apply_filters('after_format_date', $formatted, $date);
}

function set_store($store_id, $staff_id = '')
{
    $staff_id = (!$staff_id == '') ? $store_id : my_id();
    $CI = &get_instance();
    $CI->session->set_userdata(['store_id' => $store_id]);
    
    
    $CI->db->set('store_id', $store_id);
    $CI->db->where('user_id', $staff_id);
    $CI->db->update('tbl_account_details');
}

function get_staff_stores($staff_id = '')
{
    $staff_id = (!$staff_id == '') ? $staff_id : my_id();
    $CI = &get_instance();
    $CI->load->model('woocommwece/woocommerce_model');
    return $CI->woocommerce_model->staff_stores($staff_id);
}