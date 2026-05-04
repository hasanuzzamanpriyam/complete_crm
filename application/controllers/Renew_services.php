<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CLI controller to process automatic renewals for hosting and domain services.
 * Run via: php index.php renew_services
 */
class Renew_services extends CI_Controller {
    public function __construct(){
        parent::__construct();
        // Load DB and helper
        $this->load->database();
        $this->load->helper('renewal_helper');
    }

    /**
     * Entry point for CLI execution.
     */
    public function index(){
        if (!$this->input->is_cli_request()) {
            echo "This script must be run from the command line.\n";
            return;
        }
        $this->_process('hosting');
        $this->_process('domains'); // table name for domain records (adjust if different)
        echo "Renewal processing completed.\n";
    }

    /**
     * Process automatic renewals for a given table.
     *
     * @param string $type Either 'hosting' or 'domains'
     */
    private function _process(string $type){
        $table = ($type === 'hosting') ? 'tblserver_hostings' : 'tbldomains';
        // Fetch records flagged for automatic renewal that have expired (or expire today)
        $today = date('Y-m-d');
        $query = $this->db->where('renew', 'automatic')
                          ->where('expiry_date <=', $today)
                          ->get($table);
        foreach ($query->result() as $row){
            // Use current expiry as the base date for next period
            $newExpiry = calculate_new_expiry($row->expiry_date, (int)$row->days, $row->time_unit);
            $data = [
                'expiry_date'      => $newExpiry
            ];
            $this->db->where('id', $row->id)->update($table, $data);
            echo ucfirst($type) . " ID {$row->id} renewed to {$newExpiry}\n";
        }
    }
}
?>