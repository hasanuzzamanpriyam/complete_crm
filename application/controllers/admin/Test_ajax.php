<?php
class Test_ajax extends CI_Controller {
    public function __construct() {
        parent::__construct();
    }
    
    public function test() {
        if (!$this->input->is_ajax_request()) {
            echo "Not AJAX request";
            return;
        }
        $name = $this->input->post('name', TRUE);
        if ($name) {
            echo json_encode(array('status' => 'success', 'id' => 1, 'text' => $name));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'No name'));
        }
    }
}
?>
