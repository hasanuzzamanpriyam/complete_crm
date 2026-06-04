<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Drop_unused_payment_hub_tables extends CI_Migration {
    public function up() {
        $this->dbforge->drop_table('tbl_hub_payments', TRUE);
        $this->dbforge->drop_table('tbl_api_clients',    TRUE);
        $this->dbforge->drop_table('tbl_api_tokens',    TRUE);
        $this->dbforge->drop_table('tbl_payment_gateways', TRUE);
        $this->dbforge->drop_table('tbl_payment_transactions', TRUE);
        $this->dbforge->drop_table('tbl_payment_logs',  TRUE);
        $this->dbforge->drop_table('tbl_webhook_logs',  TRUE);
    }
    public function down() {
        // No down migration needed
    }
}
?>
