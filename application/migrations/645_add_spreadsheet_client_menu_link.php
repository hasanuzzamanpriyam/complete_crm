<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_spreadsheet_client_menu_link extends CI_Migration {

    public function up()
    {
        // tbl_client_menu.menu_id=24 pointed at client/spreadsheet (404 — no
        // such controller). The module resolves from the bare spreadsheet/...
        // segment, which is also what Spreadsheet::index() and the module's
        // admin_url() emit for client sessions. Idempotent data migration.
        $this->db->where('menu_id', 24);
        $this->db->where('label', 'spreadsheet');
        $this->db->where('link', 'client/spreadsheet');
        $this->db->update('tbl_client_menu', array('link' => 'spreadsheet'));
    }

    public function down()
    {
        $this->db->where('menu_id', 24);
        $this->db->where('label', 'spreadsheet');
        $this->db->where('link', 'spreadsheet');
        $this->db->update('tbl_client_menu', array('link' => 'client/spreadsheet'));
    }
}
