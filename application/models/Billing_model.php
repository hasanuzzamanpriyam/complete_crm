<?php

class Billing_model extends MY_Model
{
    public $_table_name = 'tbl_billing_orders';
    public $_primary_key = 'id';
    public $_order_by = 'id DESC';

    public function get_all_billing()
    {
        return $this->get();
    }

    public function get_billing_by_id($id)
    {
        return $this->get($id, TRUE);
    }

    public function save_billing($data, $id = NULL)
    {
        return $this->save($data, $id);
    }

    public function delete_billing($id)
    {
        return $this->delete($id);
    }
}
