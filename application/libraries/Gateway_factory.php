<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Gateway_factory
 * Instantiates the appropriate payment gateway driver.
 */
class Gateway_factory
{
    protected $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    /**
     * @param object $gateway_row Row from tbl_payment_gateways
     * @return Payment_gateway_interface|null
     */
    public function get_driver($gateway_row)
    {
        if (empty($gateway_row)) return null;

        $slug = $gateway_row->gateway_slug;
        $class_name = ucfirst($slug) . '_gateway';
        $file_path = APPPATH . 'libraries/Payment_gateways/' . $class_name . '.php';

        if (file_exists($file_path)) {
            require_once $file_path;
            $config = !empty($gateway_row->config) ? json_decode($gateway_row->config, true) : [];
            return new $class_name($config);
        }

        log_message('error', "Payment Hub: Gateway driver not found for slug: $slug");
        return null;
    }
}
