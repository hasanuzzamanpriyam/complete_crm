<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_610 extends CI_Migration
{
    public function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        $this->db->query("INSERT INTO `tbl_online_payment` (`online_payment_id`, `gateway_name`, `icon`, `field_1`, `field_2`, `field_3`, `field_4`, `field_5`, `link`, `modal`) VALUES 
        (NULL, 'PipraPay', 'piprapay.png', 'piprapay_api_url', 'piprapay_api_key', 'piprapay_api_secret', 'piprapay_merchant_id', '', 'payment/piprapay', 'Yes');");

        $this->db->query("ALTER TABLE `tbl_invoices` ADD `allow_piprapay` ENUM('Yes','No') NULL DEFAULT 'Yes' AFTER `allow_tappayment`;");

        $config_items = array(
            array(
                'config_key' => 'piprapay_enabled',
                'value' => 'FALSE',
                'label' => 'PipraPay Enabled',
                'type' => 'checkbox',
                'options' => '',
                'description' => 'Enable or disable PipraPay payment gateway'
            ),
            array(
                'config_key' => 'piprapay_api_url',
                'value' => 'https://payment.yourdomain.com/api/v1',
                'label' => 'PipraPay API URL',
                'type' => 'text',
                'options' => '',
                'description' => 'PipraPay API endpoint URL'
            ),
            array(
                'config_key' => 'piprapay_api_key',
                'value' => '',
                'label' => 'PipraPay API Key',
                'type' => 'text',
                'options' => '',
                'description' => 'Your PipraPay API key'
            ),
            array(
                'config_key' => 'piprapay_api_secret',
                'value' => '',
                'label' => 'PipraPay API Secret',
                'type' => 'text',
                'options' => '',
                'description' => 'Your PipraPay API secret'
            ),
            array(
                'config_key' => 'piprapay_merchant_id',
                'value' => '',
                'label' => 'PipraPay Merchant ID',
                'type' => 'text',
                'options' => '',
                'description' => 'Your PipraPay merchant ID'
            ),
            array(
                'config_key' => 'piprapay_webhook_secret',
                'value' => '',
                'label' => 'PipraPay Webhook Secret',
                'type' => 'text',
                'options' => '',
                'description' => 'Your PipraPay webhook secret for verifying callbacks'
            ),
            array(
                'config_key' => 'piprapay_test_mode',
                'value' => 'TRUE',
                'label' => 'PipraPay Test Mode',
                'type' => 'checkbox',
                'options' => '',
                'description' => 'Enable test mode for PipraPay'
            ),
            array(
                'config_key' => 'piprapay_default_gateway',
                'value' => 'bkash',
                'label' => 'Default Payment Gateway',
                'type' => 'select',
                'options' => 'bkash,bKash,nagad,Nagad,stripe,Stripe',
                'description' => 'Default gateway for PipraPay payments'
            )
        );

        foreach ($config_items as $item) {
            $this->db->query("INSERT INTO `tbl_config` (`config_key`, `value`, `label`, `type`, `options`, `description`) VALUES 
            ('" . $item['config_key'] . "', '" . $item['value'] . "', '" . $item['label'] . "', '" . $item['type'] . "', '" . $item['options'] . "', '" . $item['description'] . "');");
        }

        $this->db->query("UPDATE `tbl_config` SET `value` = '6.1.0' WHERE `tbl_config`.`config_key` = 'version';");
    }

    public function down()
    {
        $this->db->query("DELETE FROM `tbl_online_payment` WHERE `gateway_name` = 'PipraPay';");

        $config_keys = array(
            'piprapay_enabled',
            'piprapay_api_url',
            'piprapay_api_key',
            'piprapay_api_secret',
            'piprapay_merchant_id',
            'piprapay_webhook_secret',
            'piprapay_test_mode',
            'piprapay_default_gateway'
        );

        foreach ($config_keys as $key) {
            $this->db->query("DELETE FROM `tbl_config` WHERE `config_key` = '" . $key . "';");
        }

        $this->db->query("ALTER TABLE `tbl_invoices` DROP COLUMN `allow_piprapay`;");

        $this->db->query("UPDATE `tbl_config` SET `value` = '6.0.7' WHERE `tbl_config`.`config_key` = 'version';");
    }
}
