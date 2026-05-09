<?php

class Billing_model extends MY_Model
{
    public $_table_name = 'tbl_billing_orders';
    public $_primary_key = 'id';
    public $_order_by = 'id DESC';

    public function get_all_billing()
    {
        $this->db->select('tbl_billing_orders.id, tbl_billing_orders.label, tbl_billing_orders.type, tbl_billing_orders.value, tbl_billing_orders.currency, tbl_billing_orders.renewal_date, tbl_billing_orders.expiry_date, tbl_billing_orders.renew, tbl_billing_orders.status, tblproviders.provider_name, tbl_client.name as client_name');
        $this->db->from('tbl_billing_orders');
        $this->db->join('tblproviders', 'tbl_billing_orders.provider_id = tblproviders.id', 'left');
        $this->db->join('tbl_client', 'tbl_billing_orders.client_id = tbl_client.client_id', 'left');
        $this->db->order_by('tbl_billing_orders.id', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    public function get_billing_by_id($id)
    {
        return $this->get($id, TRUE);
    }

    public function get_billing_info($id)
    {
        $this->db->select('tbl_billing_orders.id, tbl_billing_orders.label, tbl_billing_orders.type, tbl_billing_orders.value, tbl_billing_orders.currency, tbl_billing_orders.renewal_date, tbl_billing_orders.expiry_date, tbl_billing_orders.renew, tbl_billing_orders.status, tbl_billing_orders.provider_id, tbl_billing_orders.flag, tbl_billing_orders.contact_id, tbl_billing_orders.address, tbl_billing_orders.contact_phone, tbl_billing_orders.contact_email, tbl_billing_orders.registration_date, tbl_billing_orders.buy_date, tbl_billing_orders.duration, tbl_billing_orders.time_unit, tbl_billing_orders.billing_cycle, tbl_billing_orders.last_billed_date, tbl_billing_orders.billing_end_date, tbl_billing_orders.bill_status, tbl_billing_orders.project_id, tbl_billing_orders.client_id, tbl_billing_orders.server_details, tbl_billing_orders.manage, tbl_billing_orders.login_details, tbl_billing_orders.port, tbl_billing_orders.secure_protocol, tbl_billing_orders.enable_expiry_notification, tbl_billing_orders.enable_reminders_weekend, tbl_billing_orders.server_tags, tbl_billing_orders.description, tblproviders.provider_name as provider, tbl_client.name as client_name, tbl_project.project_name, tbl_users.username as contact_person');
        $this->db->from('tbl_billing_orders');
        $this->db->join('tblproviders', 'tbl_billing_orders.provider_id = tblproviders.id', 'left');
        $this->db->join('tbl_client', 'tbl_billing_orders.client_id = tbl_client.client_id', 'left');
        $this->db->join('tbl_project', 'tbl_billing_orders.project_id = tbl_project.project_id', 'left');
        $this->db->join('tbl_users', 'tbl_billing_orders.contact_id = tbl_users.user_id', 'left');
        $this->db->where('tbl_billing_orders.id', $id);
        return $this->db->get()->row();
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
