<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_613 extends CI_Migration
{
    public function up()
    {
        // 1. Rename existing tables for better semantics
        if ($this->db->table_exists('tbl_payment_projects')) {
            $this->dbforge->rename_table('tbl_payment_projects', 'tbl_api_clients');
        }

        if ($this->db->table_exists('tbl_external_transactions')) {
            $this->dbforge->rename_table('tbl_external_transactions', 'tbl_payments');
        }

        // 2. Refactor tbl_api_clients (formerly tbl_payment_projects)
        // Ensure columns match the new spec
        if ($this->db->table_exists('tbl_api_clients')) {
            $fields = [
                'client_id' => [
                    'type' => 'VARCHAR',
                    'constraint' => 64,
                    'after' => 'project_name',
                    'null' => TRUE
                ],
                'client_secret' => [
                    'type' => 'VARCHAR',
                    'constraint' => 128,
                    'after' => 'client_id',
                    'null' => TRUE
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'after' => 'created_at'
                ]
            ];
            $this->dbforge->add_column('tbl_api_clients', $fields);

            // Copy old keys to new if they exist and new are empty
            $this->db->query("UPDATE tbl_api_clients SET client_id = api_key, client_secret = api_secret WHERE client_id IS NULL");
        }

        // 3. Create tbl_payment_gateways
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'gateway_slug' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'unique' => TRUE
            ],
            'config' => [
                'type' => 'TEXT',
                'null' => TRUE
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default' => 'active'
            ],
            'is_default' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0
            ],
            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
            'updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('tbl_payment_gateways');

        // Seed with Piprapay
        $this->db->insert('tbl_payment_gateways', [
            'name' => 'PipraPay',
            'gateway_slug' => 'piprapay',
            'is_default' => 1
        ]);

        // 4. Update tbl_payments (formerly tbl_external_transactions)
        if ($this->db->table_exists('tbl_payments')) {
            $fields = [
                'customer_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => TRUE,
                    'after' => 'currency'
                ],
                'customer_email' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => TRUE,
                    'after' => 'customer_name'
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'after' => 'created_at'
                ]
            ];
            $this->dbforge->add_column('tbl_payments', $fields);
        }

        // 5. Create tbl_payment_transactions
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'payment_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'gateway_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE
            ],
            'gateway_txn_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => TRUE
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'raw_response' => [
                'type' => 'LONGTEXT',
                'null' => TRUE
            ],
            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP'
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('payment_id');
        $this->dbforge->create_table('tbl_payment_transactions');

        // 6. Create tbl_payment_logs
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'payment_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE
            ],
            'log_level' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'default' => 'info'
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'context' => [
                'type' => 'TEXT',
                'null' => TRUE
            ],
            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP'
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('tbl_payment_logs');

        // 7. Create tbl_webhook_logs
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'payment_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE
            ],
            'direction' => [
                'type' => 'ENUM',
                'constraint' => ['incoming', 'outgoing'],
            ],
            'url' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE
            ],
            'payload' => [
                'type' => 'LONGTEXT',
                'null' => TRUE
            ],
            'response_code' => [
                'type' => 'INT',
                'constraint' => 5,
                'null' => TRUE
            ],
            'response_body' => [
                'type' => 'TEXT',
                'null' => TRUE
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['success', 'failed', 'pending'],
                'default' => 'pending'
            ],
            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP'
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('tbl_webhook_logs');

        // 8. Create tbl_refunds
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'payment_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'amount' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'reason' => [
                'type' => 'TEXT',
                'null' => TRUE
            ],
            'gateway_refund_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => TRUE
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP'
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('tbl_refunds');

        // 9. Update tbl_api_tokens foreign key if needed
        // (CI doesn't handle FKs well in dbforge, so we do it via SQL if required, 
        // but since we renamed the table, MySQL should handle it if it was defined, 
        // otherwise we just keep the index).
    }

    public function down()
    {
        $this->dbforge->drop_table('tbl_payment_gateways', TRUE);
        $this->dbforge->drop_table('tbl_payment_transactions', TRUE);
        $this->dbforge->drop_table('tbl_payment_logs', TRUE);
        $this->dbforge->drop_table('tbl_webhook_logs', TRUE);
        $this->dbforge->drop_table('tbl_refunds', TRUE);
        
        // Reverse renames if necessary (usually not done in CRM migrations but for completeness)
        if ($this->db->table_exists('tbl_api_clients')) {
            $this->dbforge->rename_table('tbl_api_clients', 'tbl_payment_projects');
        }
        if ($this->db->table_exists('tbl_payments')) {
            $this->dbforge->rename_table('tbl_payments', 'tbl_external_transactions');
        }
    }
}
