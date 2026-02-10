<?php
/**
 * Description of mailbox_model
 *
 * @author NaYeM
 */
class Mailbox_Model extends MY_Model
{

    public $_table_name;
    public $_order_by;
    public $_primary_key;
    public function get_inbox_message2($folder, $flag = Null, $del_info = NULL)
    {
        $user_id = $this->session->userdata('user_id');
        $user_info = $this->admin_model->check_by(array('user_id' => $user_id), 'tbl_users');
        $smtp_host_name =  $user_info->smtp_host_name;
        $this->db->select('mail_folder, count(*) as mail_num');
        $this->db->from('tbl_inbox');
        $this->db->where('user_id', $user_id);
        $this->db->where('smtp_host_name', $smtp_host_name);

        if (!empty($del_info)) {
            $this->db->where('deleted', 'Yes');
        } else {
            $this->db->where('deleted', 'No');
        }
        if (!empty($flag)) {
            $this->db->where('view_status', '2');
        }
        $this->db->group_by('mail_folder');
        $this->db->order_by('message_time', 'DESC');
        $query_result = $this->db->get();
        $result = $query_result->result();
        $f_res =  array();
        foreach ($result as $v) {
            $f_res[$v->mail_folder] = $v->mail_num;
        }
        return $f_res;
    }

    public function get_inbox_message($email = null, $flag = Null, $del_info = NULL)
    {
        //WHEN favourites = 1 THEN 'favourites'
        $user_id = $this->session->userdata('user_id');
        $this->db->select("mail_folder AS folder, count(*) as mail_num");
        $this->db->from('tbl_inbox');
        $this->db->where('user_id', $user_id);
        $this->db->where('smtp_host_name', 'system');

        if (!empty($del_info)) {
            $this->db->where('deleted', 'Yes');
        } else {
            $this->db->where('deleted', 'No');
        }
        $this->db->where('view_status', 2);
        $this->db->group_by('folder');
        $this->db->order_by('message_time', 'DESC');
        $query_result = $this->db->get();
        $result = $query_result->result();
        $mail =  array();
        $fav_res = $this->db->query("SELECT COUNT(*) AS mail_num  FROM tbl_inbox WHERE user_id = '$user_id' AND   favourites = 1 AND deleted != 'Yes' ")->row();
        if (!empty($fav_res)) {
            $mail['favourites'] = $fav_res->mail_num;
        }
        $del_res = $this->db->query("SELECT COUNT(*) AS mail_num  FROM tbl_inbox WHERE user_id = '$user_id'  AND deleted = 'Yes' ")->row();
        if (!empty($del_res)) {
            $mail['trash'] = $del_res->mail_num;
        }
        foreach ($result as $v) {
            $mail[$v->folder] = $v->mail_num;
        }
        return $mail;
    }

    public function get_sent_message($user_id, $del_info = NULL)
    {
        $this->db->select('*');
        $this->db->from('tbl_sent');
        $this->db->where('user_id', $user_id);
        if (!empty($del_info)) {
            $this->db->where('deleted', 'Yes');
        } else {
            $this->db->where('deleted', 'No');
        }
        $this->db->order_by('message_time', 'DESC');
        $query_result = $this->db->get();
        $result = $query_result->result();
        return $result;
    }

    public function get_draft_message($user_id, $del_info = NULL)
    {
        $this->db->select('*');
        $this->db->from('tbl_draft');
        $this->db->where('user_id', $user_id);
        if (!empty($del_info)) {
            $this->db->where('deleted', 'Yes');
        } else {
            $this->db->where('deleted', 'No');
        }
        $this->db->order_by('message_time', 'DESC');
        $query_result = $this->db->get();
        $result = $query_result->result();
        return $result;
    }

    public function send_mail($params)
    {

        $user_info = MyDetails();
        $username =  $user_info->smtp_username;
        if (empty($username)) {
            $username = $user_info->active_email;
        }
        $config = array();
        if ($user_info->smtp_encription == 'tls') {
            $smtp_port =  '587';
        } elseif ($user_info->smtp_encription == 'ssl') {
            $smtp_port = '465';
        } else {
            $smtp_port = '25';
        }
        $config['wordwrap'] = TRUE;
        $config['mailtype'] = "html";
        $config['charset'] = 'utf-8';
        $config['newline'] = "\r\n";
        $config['crlf'] = "\r\n";
        $config['smtp_timeout'] = '30';

        $config['protocol'] = (!empty($user_info->smtp_email_type) ? $user_info->smtp_email_type : 'smtp');
        $config['smtp_host'] = $user_info->mail_host;
        $config['smtp_port'] = $smtp_port;
        $config['smtp_user'] = trim($username);
        $config['smtp_pass'] = decrypt($user_info->smtp_password);
        $config['smtp_crypto'] = $user_info->smtp_encription;

        $this->load->library('email', $config);
        $this->email->clear(true);
        $this->email->from($user_info->active_email, $user_info->fullname);
        $this->email->to($params['recipient']);
        if (!empty($params['cc'])) {
            $this->email->cc($params['cc']);
        }
        $this->email->subject($params['subject']);
        $this->email->message($params['message']);
        if (!empty($params['resourceed_file'])) {
            foreach ($params['resourceed_file'] as $attachment) {
                $attachment_url = module_dirPath(MAILBOX_MODULE) . 'uploads/' . $attachment;
                $this->email->attach($attachment_url);
            }
        }
        $send = $this->email->send();
        if ($send) {
            return $send;
        } else {
            send_later($params);
            return false;
        }
    }
}
