<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_telegram_integration extends CI_Migration {

    public function up()
    {
        foreach (array('telegram_bot_token', 'telegram_group_id') as $config_key) {
            $exists = $this->db->where('config_key', $config_key)->get('tbl_config');
            if ($exists->num_rows() == 0) {
                $this->db->insert('tbl_config', array('config_key' => $config_key, 'value' => ''));
            }
        }

        if (!$this->db->field_exists('telegram_chat_id', 'tbl_users')) {
            $this->db->query("ALTER TABLE tbl_users ADD COLUMN telegram_chat_id VARCHAR(100) DEFAULT NULL");
        }
    }

    public function down()
    {
        $this->db->query("ALTER TABLE tbl_users DROP COLUMN IF EXISTS telegram_chat_id");
        $this->db->where_in('config_key', array('telegram_bot_token', 'telegram_group_id'))->delete('tbl_config');
    }
}
