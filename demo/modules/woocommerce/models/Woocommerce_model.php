<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Woocommerce_model extends MY_Model
{
    public $_table_name;
    public $_order_by;
    public $_primary_key;
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function is_summary_exist($store_id)
    {
        return (bool)total_rows('tbl_woocommerce_summary', ['store_id' => $store_id]) > 0;
    }
    
    public function add_summary($data, $storeId)
    {
        $data['store_id'] = $storeId;
        $this->db->insert('tbl_woocommerce_summary', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }
    
    public function update_summary($data, $storeId)
    {
        $this->db->where('store_id', $storeId);
        $this->db->update('tbl_woocommerce_summary', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }
    
    public function is_wooproduct_exist($id, $storeId)
    {
        return (bool)total_rows('tbl_woocommerce_products ', ['product_id' => $id, 'store_id' => $storeId]) > 0;
    }
    
    public function cron_products($data, $storeId)
    {
        $data['store_id'] = $storeId;
        $this->db->insert('tbl_woocommerce_products', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }
    
    public function update_product($id, $data, $storeId)
    {
        $this->db->set('name', $data['name']);
        $this->db->set('status', $data['status']);
        $this->db->set('price', $data['regular_price']);
        $this->db->set('short_description', $data['short_description']);
        $this->db->where('product_id', $id);
        $this->db->where('store_id', $storeId);
        $this->db->update('tbl_woocommerce_products');
    }
    
    public function cron_updates($data, $scope, $storeId)
    {
        if (!$scope) {
            return false;
        } else {
            $this->db->where('store_id', $storeId);
            
            if (isset($data['product_id'])) {
                $this->db->where('product_id', $data['product_id']);
            } elseif (isset($data['order_id'])) {
                $this->db->where('order_id', $data['order_id']);
            } elseif (isset($data['woo_customer_id'])) {
                $this->db->where('woo_customer_id', $data['woo_customer_id']);
            } else {
                return;
            }
            $this->db->update('tbl_woocommerce_' . $scope, $data);
            if ($this->db->affected_rows() > 0) {
                return true;
            }
        }
        
        return false;
    }
    
    public function get_summary($storeId)
    {
        $this->db->where('store_id', $storeId);
        return $this->db->get('tbl_woocommerce_summary')->row();
    }
    
    public function get_staff_all($id = '', $where = [])
    {
        if (is_numeric($id)) {
            $this->db->where('user_id', $id);
            $staff = $this->db->get('tbl_users')->row();
            return $staff;
        }
        $this->db->order_by('username', 'desc');
        return $this->db->get('tbl_users')->result_array();
    }
    
    public function get_store_id($store_id)
    {
        $this->db->where('store_id', $store_id);
        return $this->db->get('tbl_woocommerce_stores')->row();
    }
    
    public function staff_stores($user_id)
    {
        $_store_id = "tbl_woocommerce_assigned.store_id";
        $woo_stores = "tbl_woocommerce_stores.store_id";
        $this->db->select($_store_id);
        $this->db->join('tbl_woocommerce_stores', $woo_stores . '=' . $_store_id);
        $this->db->where('user_id', $user_id);
        return $this->db->get('tbl_woocommerce_assigned')->result_array();
    }
    
    public function delete_all_data($id, $scope, $storeId)
    {
        $wh = substr($scope, 0, -1);
        if ($wh == 'customer') {
            $wh = 'woo_' . $wh;
        }
        $this->db->where($wh . '_id', $id);
        $this->db->where('store_id', $storeId);
        $this->db->delete('tbl_woocommerce_' . $scope);
    }
    
    public function is_wooorder_exist($id, $storeId)
    {
        return (bool)total_rows('tbl_woocommerce_orders ', ['order_id' => $id, 'store_id' => $storeId]) > 0;
    }
    
    public function is_woocustomer_exist($id, $storeId)
    {
        return (bool)total_rows('tbl_woocommerce_customers ', ['woo_customer_id' => $id, 'store_id' => $storeId]) > 0;
    }
    
    public function cron_orders($data, $storeId)
    {
        
        $data['store_id'] = $storeId;
        $this->db->insert('tbl_woocommerce_orders', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }
    
    public function cron_customers($data, $storeId)
    {
        $data['store_id'] = $storeId;
        $this->db->insert('tbl_woocommerce_customers', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }
    
    public function update_customer($id, $data, $storeId)
    {
        $this->db->set('first_name', $data['first_name']);
        $this->db->set('last_name', $data['last_name']);
        $this->db->set('email', $data['email']);
        $this->db->set('username', $data['username']);
        $this->db->where('woo_customer_id', $id);
        $this->db->where('store_id', $storeId);
        $this->db->update('tbl_woocommerce_customers');
    }
    
    public function update_order($data, $storeId)
    {
        $order_id = $data['orderId'];
        $status = $data['status'];
        $this->db->set('status', $status);
        $this->db->where('order_id', $order_id);
        $this->db->where('store_id', $storeId);
        $this->db->update('tbl_woocommerce_orders');
    }
    
    public function empty_store($id)
    {
        $this->db->where('store_id', $id);
        $this->db->delete('tbl_woocommerce_products');
        $this->db->where('store_id', $id);
        $this->db->delete('tbl_woocommerce_customers');
        $this->db->where('store_id', $id);
        $this->db->delete('tbl_woocommerce_orders');
        $this->db->where('store_id', $id);
        $this->db->delete('tbl_woocommerce_summary');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }
}
