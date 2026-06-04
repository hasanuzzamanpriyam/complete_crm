<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_1000 extends CI_Migration
{
    public function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        // Create piprapay_transactions ledger for tracking all PipraPay payments
        $this->dbforge->add_field(array(
            'id' => array(
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => TRUE,
                'auto_increment' => TRUE,
            ),
            'invoice_id' => array(
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => TRUE,
                'null'       => FALSE,
            ),
            'transaction_reference' => array(
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => TRUE,
                'comment'    => 'PipraPay pp_id',
            ),
            'amount' => array(
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ),
            'currency' => array(
                'type'       => 'VARCHAR',
                'constraint' => 5,
                'default'    => 'BDT',
            ),
            'payment_gateway_status' => array(
                'type'       => 'ENUM',
                'constraint' => "'initiated','success','failed','cancelled','refunded'",
                'default'    => 'initiated',
            ),
            'raw_response' => array(
                'type' => 'TEXT',
                'null' => TRUE,
                'comment' => 'JSON response from PipraPay API',
            ),
            'created_at' => array(
                'type' => 'DATETIME',
                'null' => TRUE,
            ),
            'updated_at' => array(
                'type' => 'DATETIME',
                'null' => TRUE,
            ),
        ));

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('invoice_id');
        $this->dbforge->add_key('transaction_reference');
        $this->dbforge->create_table('piprapay_transactions', TRUE);

        // Add pp_id column to tbl_invoices if it doesn't exist
        if (!$this->db->field_exists('pp_id', 'tbl_invoices')) {
            $this->db->query("ALTER TABLE `tbl_invoices` ADD `pp_id` VARCHAR(255) NULL DEFAULT NULL AFTER `allow_piprapay`;");
        }

        // Update version
        $this->db->where('config_key', 'version')->update('tbl_config', array('value' => '10.0.0'));
    }

    public function down()
    {
        $this->dbforge->drop_table('piprapay_transactions', TRUE);

        if ($this->db->field_exists('pp_id', 'tbl_invoices')) {
            $this->db->query("ALTER TABLE `tbl_invoices` DROP COLUMN `pp_id`;");
        }

        $this->db->where('config_key', 'version')->update('tbl_config', array('value' => '6.1.0'));
    }
}
