<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * External_transactions_model
 * Manages payment transactions initiated by external projects via the payment hub.
 */
class External_transactions_model extends MY_Model
{
    public $_table_name  = 'tbl_external_transactions';
    public $_primary_key = 'id';
    public $_order_by    = 'id';

    /**
     * Create a new pending transaction.
     * Returns the newly created ID.
     */
    public function create($data)
    {
        $insert = [
            'project_id'         => $data['project_id'],
            'external_reference' => $data['external_reference'],
            'amount'             => $data['amount'],
            'currency'           => isset($data['currency']) ? $data['currency'] : 'BDT',
            'gateway_name'       => isset($data['gateway_name']) ? $data['gateway_name'] : 'PipraPay',
            'payment_method'     => isset($data['payment_method']) ? $data['payment_method'] : null,
            'status'             => 'pending',
            'created_at'         => date('Y-m-d H:i:s'),
            'updated_at'         => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('tbl_external_transactions', $insert);
        return $this->db->insert_id();
    }

    /**
     * Update transaction status and optional gateway info.
     */
    public function update_status($id, $status, $extra = [])
    {
        $update = array_merge([
            'status'     => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ], $extra);
        $this->db->where('id', $id)->update('tbl_external_transactions', $update);
        return $this->db->affected_rows();
    }

    /**
     * Get a single transaction by ID.
     */
    public function get($id)
    {
        return $this->db
            ->select('et.*, pp.project_name, pp.webhook_url, pp.callback_url')
            ->from('tbl_external_transactions et')
            ->join('tbl_payment_projects pp', 'pp.id = et.project_id', 'left')
            ->where('et.id', $id)
            ->get()
            ->row();
    }

    /**
     * Get transaction by gateway_transaction_id (for callback matching).
     */
    public function get_by_gateway_txn($gateway_txn_id)
    {
        return $this->db
            ->select('et.*, pp.project_name, pp.webhook_url, pp.callback_url')
            ->from('tbl_external_transactions et')
            ->join('tbl_payment_projects pp', 'pp.id = et.project_id', 'left')
            ->where('et.gateway_transaction_id', $gateway_txn_id)
            ->get()
            ->row();
    }

    /**
     * Get all transactions, optionally filtered.
     */
    public function get_all($filters = [])
    {
        $this->db->select('et.*, pp.project_name');
        $this->db->from('tbl_external_transactions et');
        $this->db->join('tbl_payment_projects pp', 'pp.id = et.project_id', 'left');

        if (!empty($filters['project_id'])) {
            $this->db->where('et.project_id', $filters['project_id']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('et.status', $filters['status']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(et.created_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(et.created_at) <=', $filters['date_to']);
        }

        $this->db->order_by('et.id', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Get transaction summary counts for dashboard widget.
     */
    public function get_summary()
    {
        $result = [
            'total'     => 0,
            'success'   => 0,
            'pending'   => 0,
            'failed'    => 0,
            'cancelled' => 0,
            'revenue'   => 0,
        ];

        $rows = $this->db->select('status, COUNT(*) as cnt, SUM(amount) as total_amount')
            ->from('tbl_external_transactions')
            ->group_by('status')
            ->get()
            ->result();

        foreach ($rows as $row) {
            $result['total'] += $row->cnt;
            $result[$row->status] = (int) $row->cnt;
            if ($row->status === 'success') {
                $result['revenue'] = (float) $row->total_amount;
            }
        }

        return $result;
    }

    /**
     * Paginated list for DataTables.
     */
    public function get_paginated($start = 0, $length = 10, $filters = [])
    {
        $this->db->select('et.*, pp.project_name');
        $this->db->from('tbl_external_transactions et');
        $this->db->join('tbl_payment_projects pp', 'pp.id = et.project_id', 'left');

        if (!empty($filters['project_id'])) {
            $this->db->where('et.project_id', $filters['project_id']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('et.status', $filters['status']);
        }

        $this->db->order_by('et.id', 'DESC');
        $this->db->limit($length, $start);
        return $this->db->get()->result();
    }
}
