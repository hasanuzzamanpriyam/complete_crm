<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Expense_occurrence_model extends CI_Model
{
    private $table = 'expense_occurrences';
    private $expenses_table = 'expenses';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Check if an occurrence already exists for a specific expense and date
     * 
     * @param int $expense_id
     * @param string $date (Y-m-d format)
     * @return bool TRUE if exists, FALSE otherwise
     */
    public function exists($expense_id, $date)
    {
        $this->db->where('expense_id', $expense_id);
        $this->db->where('occurrence_date', $date);
        
        return $this->db->count_all_results($this->table) > 0;
    }

    /**
     * Insert a new occurrence
     * Prevents duplicate insertion based on expense_id and occurrence_date
     * 
     * @param array $data Data to be inserted (must contain expense_id and occurrence_date)
     * @return int|bool Insert ID on success, FALSE on failure or if duplicate exists
     */
    public function insert_occurrence($data)
    {
        // Prevent duplicate (expense_id, occurrence_date)
        if (!empty($data['expense_id']) && !empty($data['occurrence_date'])) {
            if ($this->exists($data['expense_id'], $data['occurrence_date'])) {
                // Already exists, return false to prevent duplicate entry
                return false;
            }
        } else {
            // Missing necessary data to ensure uniqueness
            return false;
        }

        if ($this->db->insert($this->table, $data)) {
            return $this->db->insert_id();
        }
        
        return false;
    }

    /**
     * Retrieve occurrences within a specific date range
     * Includes an inner join with expenses to fetch the task name
     * 
     * @param string $start_date (Y-m-d format)
     * @param string $end_date (Y-m-d format)
     * @return array Results matching the required fields
     */
    public function get_occurrences_by_date_range($start_date, $end_date)
    {
        // Select required fields and alias them as specified
        $this->db->select("
            {$this->expenses_table}.task_name AS title, 
            {$this->table}.occurrence_date AS start, 
            {$this->table}.status
        ");
        
        // Inner join so we only get occurrences that have a matching expense
        $this->db->from($this->table);
        $this->db->join($this->expenses_table, "{$this->expenses_table}.id = {$this->table}.expense_id", 'inner');
        
        // Date range condition
        $this->db->where("{$this->table}.occurrence_date >=", $start_date);
        $this->db->where("{$this->table}.occurrence_date <=", $end_date);
        
        // Return chronologically
        $this->db->order_by("{$this->table}.occurrence_date", 'ASC');

        $query = $this->db->get();
        
        return $query->result_array();
    }

    /**
     * Mark a specific occurrence as paid
     * Upgrades the status and increments the parent's last_payment_date
     * securely maintained within a database transaction.
     * 
     * @param int $id The occurrence ID
     * @return bool TRUE on successful transaction update, FALSE otherwise
     */
    public function mark_as_paid($id)
    {
        // 1. Start MySQL Transaction
        $this->db->trans_start();

        // 2. Fetch the occurrence safely using Query Builder
        $this->db->where('id', $id);
        $occurrence = $this->db->get($this->table)->row();

        if ($occurrence) {
            // 3. Update the occurrence status strictly to 'paid'
            $this->db->where('id', $id);
            $this->db->set('status', 'paid');
            $this->db->update($this->table);

            // 4. Update the parent expense's last_payment_date
            // Crucial: we only push it forward if this occurrence is the latest one
            $this->db->where('id', $occurrence->expense_id);
            
            // Only update if it's pushing the date forward to avoid accidental rollbacks
            $this->db->where("last_payment_date IS NULL OR last_payment_date < '{$occurrence->occurrence_date}'", NULL, FALSE);
            
            $this->db->set('last_payment_date', $occurrence->occurrence_date);
            $this->db->update($this->expenses_table);
        }

        // 5. Commit or Rollback automatically
        $this->db->trans_complete();

        // 6. Return transaction status boolean natively
        return $this->db->trans_status();
    }
}
