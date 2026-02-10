<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Contracts_model extends MY_Model
{
    public $_table_name;
    public $_order_by;
    public $_primary_key;
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function get_rows($where = [])
    {
        $this->select();
        $this->join();
        $this->db->where($where);
        return $this->db->get('tbl_contracts')->result_array();
    }
    
    public function get_by_id($id, $where = [])
    {
        $contract = array();
        $this->select();
        $this->join();
        $client_id = client_id();
        if (!empty($client_id)) {
            $this->db->where('tbl_contracts.client', $client_id);
        }
        $this->db->where('tbl_contracts.contract_id', $id);
        $this->db->where($where);
        $res = $this->db->get('tbl_contracts')->row();
        if (!empty($res)) {
            $contract = $res;
            $contract->attachments = $this->get_contract_attachments('', $contract->contract_id);
        }
        return $contract;
    }
    
    
    public function get_contract_attachments($attachment_id = '', $id = '')
    {
        if (is_numeric($attachment_id)) {
            $this->db->where('files_id', $attachment_id);
            return $this->db->get('tbl_files')->row();
        }
        $this->db->where('module_field_id', $id);
        $this->db->where('module', 'contracts');
        return $this->db->get('tbl_files')->result_array();
    }
    
    
    private function select()
    {
        // select all query from tbl_contracts table and tbl_client table get name as client_name
        // and from tbl_contracts_types table get name as contract_type_name
        // and from tbl_project table get project_name
        $this->db->select('tbl_contracts.*, tbl_client.client_id,tbl_client.name as client_name,
        tbl_contracts_types.name as contract_type_name, tbl_project.project_name');
    }
    
    private function join()
    {
        // join with client table to get client name
        $this->db->join('tbl_client', 'tbl_client.client_id = tbl_contracts.client', 'left');
        // join with contract type table to get contract type name
        $this->db->join('tbl_contracts_types', 'tbl_contracts_types.id = tbl_contracts.contract_type', 'left');
        // join with project table to get project name
        $this->db->join('tbl_project', 'tbl_project.project_id = tbl_contracts.project_id', 'left');
        
    }
    
    public function send_email_template($id, $cc = '', $template = 'subscription_send_to_customer')
    {
        $subscription = $this->get_by_id($id);
        
        $contact = $this->clients_model->get_contact(get_primary_contact_user_id($subscription->clientid));
        
        if (!$contact) {
            return false;
        }
        
        $sent = send_mail_template($template, $subscription, $contact, $cc);
        
        return $sent ? true : false;
    }
    
    
    public function expiring_contracts($user_id = null, $days = 7)
    {
        $diff1 = date('Y-m-d', strtotime('-' . $days . ' days'));
        $diff2 = date('Y-m-d', strtotime('+' . $days . ' days'));
        
        
        $this->db->select('contract_id,subject,client,start_date,end_date');
        if (!empty($user_id)) {
            $this->db->where('added_from', $user_id);
        }
        $this->db->where('end_date IS NOT NULL');
        $this->db->where('trash', 0);
        $this->db->where('parent_contract_id IS NULL');
        $this->db->where('end_date >=', $diff1);
        $this->db->where('end_date <=', $diff2);
        return $this->db->get('tbl_contracts')->result_array();
    }
    
    
    function recent_contracts($days = 7, $user_id = null)
    {
        $diff1 = date('Y-m-d', strtotime('-' . $days . ' days'));
        $diff2 = date('Y-m-d', strtotime('+' . $days . ' days'));
        $where_own = [];
        
        if (!empty($user_id)) {
            $this->db->where('added_from', $user_id);
        }
        
        return total_rows('tbl_contracts', 'date_added BETWEEN "' . $diff1 . '" AND "' . $diff2 . '"  AND trash = 0 AND parent_contract_id IS NULL');
    }
}
