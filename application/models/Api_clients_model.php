<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Api_clients_model
 * Replaces and extends Payment_projects_model.
 * Manages external projects/clients and their credentials.
 */
class Api_clients_model extends MY_Model
{
    public $_table_name  = 'tbl_api_clients';
    public $_primary_key = 'id';
    public $_order_by    = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get a client by client_id.
     */
    public function get_by_client_id($client_id)
    {
        return $this->get_by(['client_id' => $client_id], TRUE);
    }

    /**
     * Authenticate using legacy api_key/secret or client_id/secret.
     */
    public function authenticate($id_or_key, $secret)
    {
        // Try new client_id/secret first
        $client = $this->get_by([
            'client_id' => $id_or_key,
            'client_secret' => $secret,
            'status' => 'active'
        ], TRUE);

        if ($client) return $client;

        // Fallback to legacy api_key (if migration kept it or it's still in the DB)
        return $this->get_by([
            'api_key' => $id_or_key,
            'api_secret' => $secret,
            'status' => 'active'
        ], TRUE);
    }
}
