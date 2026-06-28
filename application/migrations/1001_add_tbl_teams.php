<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_tbl_teams extends CI_Migration {

    public function up()
    {
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => FALSE,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => TRUE,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => FALSE,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => FALSE,
            ],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('tbl_teams', TRUE);

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
            'is_manager' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_field('UNIQUE KEY uk_team_user (team_id, user_id)');
        $this->dbforge->create_table('tbl_team_members', TRUE);

        $this->db->query('ALTER TABLE tbl_team_members ADD FOREIGN KEY (team_id) REFERENCES tbl_teams(id) ON DELETE CASCADE');
        $this->db->query('ALTER TABLE tbl_team_members ADD FOREIGN KEY (user_id) REFERENCES tbl_users(user_id) ON DELETE CASCADE');

        $this->dbforge->add_column('tbl_tasks', [
            'team_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
                'after' => 'project_id',
            ],
        ]);
    }

    public function down()
    {
        $this->dbforge->drop_column('tbl_tasks', 'team_id');
        $this->dbforge->drop_table('tbl_team_members');
        $this->dbforge->drop_table('tbl_teams');
    }
}
