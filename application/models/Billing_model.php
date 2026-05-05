<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Billing_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->create_table();
        $this->auto_renew_billings();
    }

    private function create_table()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `tbl_billing_orders` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `label` varchar(255) NOT NULL,
          `value` text NOT NULL,
          `type` varchar(50) NOT NULL,
          `currency` varchar(10) DEFAULT NULL,
          `created_at` datetime NOT NULL,
          `updated_at` datetime NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        if (!$this->db->field_exists('currency', 'tbl_billing_orders')) {
            $this->db->query("ALTER TABLE `tbl_billing_orders` ADD `currency` varchar(10) DEFAULT NULL AFTER `type`;");
        }

        if (!$this->db->field_exists('renewal_date', 'tbl_billing_orders')) {
            $this->db->query("ALTER TABLE `tbl_billing_orders` ADD `renewal_date` date DEFAULT NULL;");
        }
        if (!$this->db->field_exists('expiry_date', 'tbl_billing_orders')) {
            $this->db->query("ALTER TABLE `tbl_billing_orders` ADD `expiry_date` date DEFAULT NULL;");
        }
        if (!$this->db->field_exists('duration', 'tbl_billing_orders')) {
            $this->db->query("ALTER TABLE `tbl_billing_orders` ADD `duration` varchar(50) DEFAULT NULL;");
        }
        if (!$this->db->field_exists('time_unit', 'tbl_billing_orders')) {
            $this->db->query("ALTER TABLE `tbl_billing_orders` ADD `time_unit` varchar(20) DEFAULT NULL;");
        }
        if (!$this->db->field_exists('renew', 'tbl_billing_orders')) {
            $this->db->query("ALTER TABLE `tbl_billing_orders` ADD `renew` varchar(20) DEFAULT NULL;");
        }
    }

    public function get_all_billing()
    {
        $this->db->order_by('id', 'DESC');
        return $this->db->get('tbl_billing_orders')->result_array();
    }

    public function save_billing($data)
    {
        if (isset($data['id']) && !empty($data['id'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->where('id', $data['id']);
            $this->db->update('tbl_billing_orders', $data);
            return $data['id'];
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->insert('tbl_billing_orders', $data);
            return $this->db->insert_id();
        }
    }

    public function delete_billing($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('tbl_billing_orders');
    }

    private function auto_renew_billings()
    {
        $today = date('Y-m-d');
        $this->db->where('renew', 'automatic');
        $this->db->where('expiry_date <', $today);
        $this->db->where('expiry_date IS NOT NULL', NULL, FALSE);
        $this->db->where('expiry_date >', '1000-01-01');
        $query = $this->db->get('tbl_billing_orders');
        $expired_items = $query->result_array();

        if (empty($expired_items)) return;

        foreach ($expired_items as $item) {
            $new_renewal_date = $item['expiry_date'];
            $duration = (int)$item['duration'];
            $time_unit = $item['time_unit'];

            if ($duration <= 0) continue;

            try {
                $date = new DateTime($new_renewal_date);
                switch ($time_unit) {
                    case 'Days':
                        $date->modify('+' . $duration . ' days');
                        break;
                    case 'Weeks':
                        $date->modify('+' . ($duration * 7) . ' days');
                        break;
                    case 'Months':
                        $date->modify('+' . $duration . ' months');
                        break;
                    case 'Years':
                        $date->modify('+' . $duration . ' years');
                        break;
                    default:
                        continue 2;
                }
                $new_expiry_date = $date->format('Y-m-d');

                $this->db->where('id', $item['id']);
                $this->db->update('tbl_billing_orders', [
                    'renewal_date' => $new_renewal_date,
                    'expiry_date' => $new_expiry_date,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            } catch (Exception $e) {
                log_message('error', 'Auto-renewal failed for billing item ID ' . $item['id'] . ': ' . $e->getMessage());
            }
        }
    }
}
