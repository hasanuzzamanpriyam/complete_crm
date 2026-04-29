<?php
class Alter_table extends CI_Controller {
    public function __construct() {
        parent::__construct();
    }
    
    public function index() {
        // Add rate column to tbl_currencies if not exists
        $query = $this->db->query("SHOW COLUMNS FROM tbl_currencies LIKE 'rate'");
        if ($query->num_rows() == 0) {
            $this->db->query("ALTER TABLE tbl_currencies ADD COLUMN rate DECIMAL(10,2) DEFAULT 1.00 AFTER symbol");
            echo "Added rate column to tbl_currencies<br>";
        } else {
            echo "rate column already exists<br>";
        }
        echo "Done!";
    }
}
?>
