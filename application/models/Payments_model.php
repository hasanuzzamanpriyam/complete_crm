<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Payments_model
 * Replaces and extends External_transactions_model.
 * Manages high-level payment records.
 */
class Payments_model extends MY_Model
{
    public $_table_name  = 'tbl_hub_payments';
    public $_primary_key = 'id';
    public $_order_by    = 'id DESC';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create a new payment record.
     */
    public function create_payment($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['status'] = $data['status'] ?? 'pending';
        return $this->save($data);
    }

    /**
     * Get detailed payment with client info.
     */
    public function get_detailed($id)
    {
        $this->db->select('p.*, c.project_name as client_name, c.webhook_url');
        $this->db->from($this->_table_name . ' p');
        $this->db->join('tbl_api_clients c', 'c.id = p.client_id', 'left');
        $this->db->where('p.id', $id);
        return $this->db->get()->row();
    }

    /**
     * Get detailed list of payments with filters.
     */
    public function get_detailed_list($filters = [], $limit = null, $offset = null)
    {
        $this->db->select('p.*, c.project_name as client_name, g.name as gateway_name');
        $this->db->from($this->_table_name . ' p');
        $this->db->join('tbl_api_clients c', 'c.id = p.client_id', 'left');
        $this->db->join('tbl_payment_gateways g', 'g.id = p.gateway_id', 'left');

        if (!empty($filters['client_id'])) $this->db->where('p.client_id', $filters['client_id']);
        if (!empty($filters['gateway_id'])) $this->db->where('p.gateway_id', $filters['gateway_id']);
        if (!empty($filters['status'])) $this->db->where('p.status', $filters['status']);
        if (!empty($filters['date_from'])) $this->db->where('p.created_at >=', $filters['date_from'] . ' 00:00:00');
        if (!empty($filters['date_to'])) $this->db->where('p.created_at <=', $filters['date_to'] . ' 23:59:59');

        if ($limit) $this->db->limit($limit, $offset);
        
        $this->db->order_by('p.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get stats for dashboard.
     */
    public function get_dashboard_stats($filters = [])
    {
        $this->db->select('
            COUNT(id) as total_count,
            SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as success_count,
            SUM(CASE WHEN status = "success" THEN amount ELSE 0 END) as total_volume,
            AVG(CASE WHEN status = "success" THEN amount ELSE 0 END) as avg_success_amount,
            SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_count
        ');
        
        if (!empty($filters['client_id'])) $this->db->where('client_id', $filters['client_id']);
        if (!empty($filters['date_from'])) $this->db->where('created_at >=', $filters['date_from'] . ' 00:00:00');
        if (!empty($filters['date_to'])) $this->db->where('created_at <=', $filters['date_to'] . ' 23:59:59');

        return $this->db->get($this->_table_name)->row();
    }
}
