<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_614 extends CI_Migration
{
    public function up()
    {
        // 1. Add retry columns to tbl_webhook_logs
        if ($this->db->table_exists('tbl_webhook_logs')) {
            $webhook_fields = [
                'retry_count' => [
                    'type' => 'INT',
                    'constraint' => 5,
                    'default' => 0,
                    'after' => 'status'
                ],
                'next_retry_at' => [
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'after' => 'retry_count'
                ],
                'last_error' => [
                    'type' => 'TEXT',
                    'null' => TRUE,
                    'after' => 'next_retry_at'
                ]
            ];
            foreach ($webhook_fields as $field_name => $field_data) {
                if (!$this->db->field_exists($field_name, 'tbl_webhook_logs')) {
                    $this->dbforge->add_column('tbl_webhook_logs', [$field_name => $field_data]);
                }
            }
        }

        // 2. Add audit columns to tbl_payment_logs
        if ($this->db->table_exists('tbl_payment_logs')) {
            $log_fields = [
                'ip_address' => [
                    'type' => 'VARCHAR',
                    'constraint' => 45,
                    'null' => TRUE,
                    'after' => 'context'
                ],
                'user_agent' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => TRUE,
                    'after' => 'ip_address'
                ]
            ];
            foreach ($log_fields as $field_name => $field_data) {
                if (!$this->db->field_exists($field_name, 'tbl_payment_logs')) {
                    $this->dbforge->add_column('tbl_payment_logs', [$field_name => $field_data]);
                }
            }
        }
    }

    public function down()
    {
        if ($this->db->table_exists('tbl_webhook_logs')) {
            $this->dbforge->drop_column('tbl_webhook_logs', 'retry_count');
            $this->dbforge->drop_column('tbl_webhook_logs', 'next_retry_at');
            $this->dbforge->drop_column('tbl_webhook_logs', 'last_error');
        }

        if ($this->db->table_exists('tbl_payment_logs')) {
            $this->dbforge->drop_column('tbl_payment_logs', 'ip_address');
            $this->dbforge->drop_column('tbl_payment_logs', 'user_agent');
        }
    }
}
