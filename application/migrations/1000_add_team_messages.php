<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_team_messages extends CI_Migration {

    public function up()
    {
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
            ],
            'team_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE,
            ],
            'message' => [
                'type' => 'TEXT',
                'null' => FALSE,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => FALSE,
            ],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('team_id');
        $this->dbforge->add_key('created_at');
        $this->dbforge->create_table('tbl_team_messages', TRUE);

        $this->db->query('ALTER TABLE tbl_team_messages ADD FOREIGN KEY (team_id) REFERENCES tbl_teams(id) ON DELETE CASCADE');
        $this->db->query('ALTER TABLE tbl_team_messages ADD FOREIGN KEY (user_id) REFERENCES tbl_users(user_id) ON DELETE CASCADE');
    }

    public function down()
    {
        $this->dbforge->drop_table('tbl_team_messages');
    }
}
