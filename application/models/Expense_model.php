<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Expense_model extends CI_Model
{
    private $table = 'expenses';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Insert a new expense
     * 
     * @param array $data Data to be inserted
     * @return int|bool Insert ID on success, FALSE on failure
     */
    public function insert_expense($data)
    {
        if ($this->db->insert($this->table, $data)) {
            return $this->db->insert_id();
        }
        
        return false;
    }

    /**
     * Retrieve all expenses
     * 
     * @return array Array of all expenses
     */
    public function get_all_expenses()
    {
        // Ordering by ID or created_at descending to get the newest first
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get($this->table);
        
        return $query->result_array();
    }

    /**
     * Retrieve an expense by its ID
     * 
     * @param int $id The ID of the expense
     * @return array|null Expense data array, or NULL if not found
     */
    public function get_expense_by_id($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get($this->table);
        
        if ($query->num_rows() > 0) {
            return $query->row_array();
        }
        
        return null;
    }

    /**
     * Delete an expense entirely from the database
     * 
     * @param int $id The specific expense ID
     * @return bool Process success status
     */
    public function delete_expense($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }

    /**
     * Update an existing expense
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update_expense($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }
}
