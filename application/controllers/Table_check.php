<?php
class Table_check extends CI_Controller {
    public function __construct() {
        parent::__construct();
    }
    
    public function index() {
        $db = $this->db;
        
        // Check currency_id
        $query = $db->query("SHOW COLUMNS FROM tblserver_hostings LIKE 'currency_id'");
        if ($query->num_rows() == 0) {
            $db->query("ALTER TABLE tblserver_hostings ADD COLUMN currency_id INT(11) NULL AFTER plan");
            echo "Added currency_id column<br>";
        } else {
            echo "currency_id column exists<br>";
        }
        
        // Check price
        $query = $db->query("SHOW COLUMNS FROM tblserver_hostings LIKE 'price'");
        if ($query->num_rows() == 0) {
            $db->query("ALTER TABLE tblserver_hostings ADD COLUMN price DECIMAL(10,2) NULL AFTER currency_id");
            echo "Added price column<br>";
        } else {
            echo "price column exists<br>";
        }
        
        echo "Done!";
    }
}
?>
