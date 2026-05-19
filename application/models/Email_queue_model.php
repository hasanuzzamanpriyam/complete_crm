<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Email_queue_model extends MY_Model
{
    protected $_table_name = 'tbl_email_queue';
    protected $_primary_key = 'id';
    protected $_order_by = 'id ASC';

    public function queue_email($recipient, $subject, $message, $source = 'jitsi', $attachments = null)
    {
        $data = [
            'recipient' => $recipient,
            'subject' => $subject,
            'message' => $message,
            'attachments' => $attachments ? json_encode($attachments) : null,
            'source' => $source,
            'status' => 'pending',
            'attempts' => 0,
            'max_attempts' => 3,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert($this->_table_name, $data);
        return $this->db->insert_id();
    }

    public function queue_batch($emails, $source = 'jitsi')
    {
        $batch = [];
        $now = date('Y-m-d H:i:s');

        foreach ($emails as $email) {
            $batch[] = [
                'recipient' => $email['recipient'],
                'subject' => $email['subject'],
                'message' => $email['message'],
                'attachments' => isset($email['attachments']) ? json_encode($email['attachments']) : null,
                'source' => $source,
                'status' => 'pending',
                'attempts' => 0,
                'max_attempts' => 3,
                'created_at' => $now,
            ];
        }

        if (!empty($batch)) {
            $this->db->insert_batch($this->_table_name, $batch);
        }

        return count($batch);
    }

    public function get_pending_emails($limit = 50)
    {
        $this->db->where('status', 'pending');
        $this->db->or_group_start();
        $this->db->where('status', 'failed');
        $this->db->where('attempts <', 'max_attempts', FALSE);
        $this->db->where('next_retry_at <=', date('Y-m-d H:i:s'));
        $this->db->group_end();
        $this->db->order_by('id', 'ASC');
        $this->db->limit($limit);

        return $this->db->get($this->_table_name)->result();
    }

    public function mark_sending($id)
    {
        $this->db->where('id', $id);
        $this->db->update($this->_table_name, [
            'status' => 'sending',
            'attempts' => $this->db->query("SELECT attempts + 1 as next_attempt FROM {$this->_table_name} WHERE id = {$id}")->row()->next_attempt,
        ]);
    }

    public function mark_sent($id)
    {
        $this->db->where('id', $id);
        $this->db->update($this->_table_name, [
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s'),
            'last_error' => null,
        ]);
    }

    public function mark_failed($id, $error, $retry = true)
    {
        $update_data = [
            'status' => 'failed',
            'last_error' => substr($error, 0, 1000),
        ];

        if ($retry) {
            $update_data['next_retry_at'] = date('Y-m-d H:i:s', strtotime('+5 minutes'));
        }

        $this->db->where('id', $id);
        $this->db->update($this->_table_name, $update_data);
    }

    public function get_stats()
    {
        $stats = [
            'pending' => $this->db->where('status', 'pending')->count_all_results($this->_table_name),
            'sending' => $this->db->where('status', 'sending')->count_all_results($this->_table_name),
            'sent' => $this->db->where('status', 'sent')->count_all_results($this->_table_name),
            'failed' => $this->db->where('status', 'failed')->count_all_results($this->_table_name),
        ];

        return $stats;
    }

    public function clear_old_sent($days = 30)
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        $this->db->where('status', 'sent');
        $this->db->where('sent_at <', $cutoff);
        $this->db->delete($this->_table_name);

        return $this->db->affected_rows();
    }
}
