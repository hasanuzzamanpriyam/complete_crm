<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * 	@author : themetic.net
 * 	date	: 21 April, 2015
 * 	Inventory & Invoice Management System
 * 	http://themetic.net
 *  version: 1.0
 */

class User_Model extends MY_Model
{

    public $_table_name;
    public $_order_by;
    public $_primary_key;

    public function select_user_roll_by_id($user_id)
    {
        $this->db->select('tbl_user_role.*', false);
        $this->db->select('tbl_menu.*', false);
        $this->db->from('tbl_user_role');
        $this->db->join('tbl_menu', 'tbl_user_role.menu_id = tbl_menu.menu_id', 'left');
        $this->db->where('tbl_user_role.user_id', $user_id);
        $query_result = $this->db->get();
        $result = $query_result->result();

        return $result;
    }


    public function get_social_media($user_id)
    {
        $this->db->select('facebook_url, instagram_url, x_url, linkedin_url, staff_position');
        $this->db->where('user_id', $user_id);
        $query = $this->db->get('tbl_users');
        return $query->row_array();
    }

    public function update_social_media($user_id, $social_data)
    {
        $allowed_fields = ['facebook_url', 'instagram_url', 'x_url', 'linkedin_url', 'staff_position'];
        $data = array();

        foreach ($social_data as $key => $value) {
            if (in_array($key, $allowed_fields)) {
                $data[$key] = $value;
            }
        }

        if (!empty($data)) {
            // save method requires primary key relative to the model's table
            // user_model extends MY_Model which has save()
            // we need to set table name and primary key for this operation if they differ from default
            // defaults in User_model:
            // public $_table_name;
            // public $_order_by;
            // public $_primary_key;

            // To be safe and ensure it affects tbl_users:
            $original_table = $this->_table_name;
            $original_pk = $this->_primary_key;

            $this->_table_name = 'tbl_users';
            $this->_primary_key = 'user_id';

            $id = $this->save($data, $user_id);

            // Restore original values (though model is likely re-instantiated or these public props are set by controller usually)
            $this->_table_name = $original_table;
            $this->_primary_key = $original_pk;

            return $id;
        }

        return false;
    }

    public function get_new_user()
    {
        $post = new stdClass();
        $post->user_name = '';
        $post->name = '';
        $post->email = '';
        $post->flag = 3;
        $post->employee_login_id = '';

        return $post;
    }

    public function get_user($filterBy = null)
    {
        $users = array();
        $all_users = array_reverse($this->get_permission('tbl_users'));
        if (empty($filterBy)) {
            return $all_users;
        }
        else {
            foreach ($all_users as $v_users) {
                if ($filterBy == 'admin' && $v_users->role_id == 1) {
                    array_push($users, $v_users);
                }
                if ($filterBy == 'client' && $v_users->role_id == 2) {
                    array_push($users, $v_users);
                }
                if ($filterBy == 'staff' && $v_users->role_id == 1) {
                    array_push($users, $v_users);
                }
                if ($filterBy == 'active' && $v_users->activated == 1) {
                    array_push($users, $v_users);
                }
                if ($filterBy == 'deactive' && $v_users->activated == 0) {
                    array_push($users, $v_users);
                }
                if ($filterBy == 'banned' && $v_users->banned == 1) {
                    array_push($users, $v_users);
                }
            }
        }
        return $users;
    }


}
