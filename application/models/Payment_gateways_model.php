<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Payment_gateways_model
 * Manages supported payment gateways (Piprapay, etc.) and their configurations.
 */
class Payment_gateways_model extends MY_Model
{
    public $_table_name  = 'tbl_payment_gateways';
    public $_primary_key = 'id';
    public $_order_by    = 'is_default DESC, name ASC';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get active gateway by slug.
     */
    public function get_by_slug($slug)
    {
        return $this->get_by(['gateway_slug' => $slug, 'status' => 'active'], TRUE);
    }

    /**
     * Get default gateway.
     */
    public function get_default()
    {
        return $this->get_by(['is_default' => 1, 'status' => 'active'], TRUE);
    }

    /**
     * Get all active gateways.
     */
    public function get_active()
    {
        return $this->get_by(['status' => 'active']);
    }
}
