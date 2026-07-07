<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Maibox Controller.
 */
class Mailbox extends Admin_Controller
{
    /**
     * Controler __construct function to initialize options.
     */
    public function __construct()
    {
        parent::__construct();
        is_active_module('mailbox');
        if (empty(my_id())) {
            redirect('login');
        }
        require_once(module_dirPath(MAILBOX_MODULE) . 'libraries/Imap_zisco.php');
        
        $this->load->model('mailbox_model');
        $this->profile = $profile = profile();
        if (empty($profile)) {
            redirect('login');
        } else {
            $role = $profile->role_id;
            if ($role == 2) { // check client menu permission
                $client_menu = get_row('tbl_client_role', array('user_id' => $profile->user_id, 'menu_id' => '19'));
                if (empty($client_menu)) {
                    redirect('login');
                }
                $this->view = 'client/';
            } elseif ($role != 1) { // check staff menu permission
                if (!empty($profile->designations_id)) {
                    $user_menu = get_row('tbl_user_role', array('designations_id' => $profile->designations_id, 'menu_id' => '139'));
                    if (empty($user_menu)) {
                        redirect('login');
                    }
                }
                $this->view = 'admin/';
            } else {
                $this->view = 'admin/';
            }
        }
    }
    
    
    /**
     * Go to Mailbox home page.
     *
     * @return view
     */
    public function mail_box_menu($folder = null)
    {
        if ($this->input->is_ajax_request()) {
            $res['mail_box_menu'] = '';
            $data['action'] = $folder;
            $data['menu_active'] = $folder;
            $data['gmailBoxes'] = array();
            $where = array('user_id' => my_id());
            $smtp_menu = get_any_field('tbl_users', $where, 'smtp_menu');
            if (!empty($smtp_menu)) {
                $data['gmailBoxes'] = json_decode($smtp_menu, true);
                if (!empty($data['gmailBoxes'])) {
                    $data['mail_count'] = $this->mailbox_model->get_inbox_message2(null, TRUE);
                    $res['mail_box_menu'] = $this->load->view('mail_box_menu', $data, true);
                }
            }
            echo json_encode($res);
            exit;
        }
        redirect('admin/dashboard');
    }
    
    
    public function index($action = NULL, $id = NULL, $status = NULL)
    {
        $data['title'] = lang('mailbox');
        if (!isset($action)) $action = 'inbox';
        $data['menu_active'] = $action;
        $data['action'] = $action;
        $data['system_mail_count'] = $this->mailbox_model->get_inbox_message($action, TRUE);
        $smtp_syn_time = '';
        if (isset($this->profile->smtp_syn_time)) {
            $smtp_syn_time .= 'Your Remote emails last syncronized ' . time_ago($this->profile->smtp_syn_time);
        }
        $smtp_syn_time .= ' . You may syncronize your Remote emails by clicking here';
        $data['smtp_syn_time'] = $smtp_syn_time;
        $data['folder'] = $action;
        $data['read_mail'] = $this->mailbox_model->check_by(array('inbox_id' => $id), 'tbl_inbox');
        if ($action == 'read_send_mail') {
            $data['menu_active'] = 'sent';
            $data['view'] = 'read_mail';
        } elseif ($action == 'read_draft_mail') {
            $data['menu_active'] = 'draft';
            $data['view'] = 'read_mail';
        } elseif ($action == 'read_inbox_mail') {
            $data['menu_active'] = 'inbox';
            $data['view'] = 'read_mail';
            $data['reply'] = 1;
            $this->mailbox_model->_table_name = 'tbl_inbox';
            $this->mailbox_model->_primary_key = 'inbox_id';
            $updata['view_status'] = '1';
            $this->mailbox_model->save($updata, $id);
        } elseif ($action == 'change_view') {
            $favdata['view_status'] = $status;
            $this->mailbox_model->_primary_key = 'inbox_id';
            $this->mailbox_model->save($favdata, $id);
            redirect('admin/mailbox/index/inbox');
        } elseif ($action == 'added_favourites') {
            $favdata['favourites'] = $status;
            $this->mailbox_model->_primary_key = 'inbox_id';
            $this->mailbox_model->save($favdata, $id);
            redirect('admin/mailbox/index/favourites');
        } elseif ($action == 'compose') {
            $data['dropzone'] = true;
            $data['view'] = 'compose_mail';
            $data['menu_active'] = 'inbox';
            $data['action_type'] = '';
            $profile = profile();
            if ($profile->role_id == 2) {
                $where = array('role_id !=' => '2', 'activated' => '1');
            } else {
                $where = array('activated' => '1');
            }
            $data['get_user_info'] = get_result('tbl_users', $where);
            if (!empty($status)) {
                $data['inbox_info'] = $this->mailbox_model->check_by(array('inbox_id' => $id), 'tbl_inbox');
                $data['action_type'] = 'inbox';
            } elseif (!empty($id)) {
                $this->mailbox_model->_table_name = 'tbl_inbox';
                $this->mailbox_model->_order_by = 'inbox_id';
                $data['get_draft_info'] = $this->mailbox_model->get_by(array('inbox_id' => $id), TRUE);
            }
        } elseif (isset($folder)) {
            $data['menu_active'] = $folder;
            $data['view'] = 'inbox';
        } else {
            $data['menu_active'] = $action;
            $data['view'] = 'local_mails';
        }
        $data['subview'] = $this->load->view('mailbox', $data, TRUE);
        $this->load->view($this->view . '_layout_main', $data);
    }
    
    
    public function mailboxList($folder = null)
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('datatables');
            $this->datatables->table = 'tbl_inbox';
            $this->datatables->column_order = array('subject', 'to', 'from', 'message_body');
            $this->datatables->column_search = array('subject', 'to', 'from', 'message_body');
            $this->datatables->order = array('view_status' => 'desc', 'message_time' => 'desc');
            
            $user_id = $this->session->userdata('user_id');
            $user_info = $this->profile;
            $smtp_host_name = $user_info->smtp_host_name;
            
            $where = array('user_id' => $user_id);
            $where += array('deleted != ' => 'Yes');
            $where += array('mail_folder' => $folder);
            $where += array('smtp_host_name' => $smtp_host_name);
            
            $fetch_data = make_datatables($where);
            $data = array();
            foreach ($fetch_data as $v_inbox_msg) {
                $sub_array = array();
                $fav = '';
                $fav .= '<div class="checkbox c-checkbox" ><label class="needsclick"> <input class="child_present" name="selected_id[]"  value="' . $v_inbox_msg->inbox_id . '" type="checkbox"><span class="fa fa-check"></span></label></div>';
                if ($v_inbox_msg->favourites == 1) {
                    $favs = 0;
                    $fav_icon = 'fa-star';
                } else {
                    $fav_icon = 'fa-star-o';
                    $favs = 1;
                }
                $fav .= '<a href="' . base_url('admin/mailbox/index/added_favourites/' . $v_inbox_msg->inbox_id . '/' . $favs) . '"><i class="fa fa-lg ' . $fav_icon . ' text-yellow"></i></a>';
                $sub_array[] = $fav;
                $subject = (strlen($v_inbox_msg->subject) > 50) ? strip_html_tags(mb_substr($v_inbox_msg->subject, 0, 50)) . '...' : $v_inbox_msg->subject;
                
                $subjects = '<a href="' . base_url('admin/mailbox/index/read_inbox_mail/' . $v_inbox_msg->inbox_id) . '">';
                $subjects .= '<div class="mb-mail-date pull-right">' . time_ago($v_inbox_msg->message_time);
                if (!empty($from_trash)) {
                    $subjects .= ' <a class="btn btn-primary btn-xs" href="' . base_url('admin/mailbox/restore/inbox/') . $v_inbox_msg->inbox_id . '" data-toggle="tooltip" data-placement="top" title="" data-original-title="' . lang('restore') . '"><i class="fa fa-retweet"></i></a>';
                    $subjects .= ' <a class="btn btn-danger btn-xs" href="' . base_url('admin/mailbox/delete_mail/remote_host/deleted/') . $v_inbox_msg->inbox_id . '" data-toggle="tooltip" data-placement="top" title="" data-original-title="Restore" onclick="return confirm(' . lang('alert_delete') . ');"><i class="fa fa-trash"></i></a>';
                }
                $subjects .= '</div>';
                
                $subjects .= '<div class="mb-mail-meta ' . ($v_inbox_msg->view_status == 1 ? ' ' : 'bold') . '"   ><div class="pull-left">
                                                        <div class="mb-mail-subject">' . $subject . '</div>
                                                        <div class="mb-mail-from">';
                $string = (strlen($v_inbox_msg->from) > 50) ? strip_html_tags(mb_substr($v_inbox_msg->from, 0, 50)) . '...' : $v_inbox_msg->from;
                $subjects .= $string;
                
                $subjects .= '</div></div><div class="mb-mail-preview">';
                $body = (strlen($v_inbox_msg->message_body) > 100) ? strip_html_tags(mb_substr($v_inbox_msg->message_body, 0, 100)) . '...' : $v_inbox_msg->message_body;
                $subjects .= $body;
                
                $uploaded_file = ($v_inbox_msg->attach_file);
                if (!empty($uploaded_file)) {
                    $subjects .= '<small class="block"><i class="fa fa-paperclip"></i></small>';
                }
                $subjects .= '</div></div></a>';
                $sub_array[] = $subjects;
                $data[] = $sub_array;
            }
            render_table($data, $where);
        } else {
            redirect('admin/dashboard');
        }
    }
    
    public function inboxList($fav = 'inbox')
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('datatables');
            $this->datatables->table = 'tbl_inbox';
            $this->datatables->column_order = array('subject', 'to', 'from', 'message_body');
            $this->datatables->column_search = array('subject', 'to', 'from', 'message_body');
            $this->datatables->order = array('view_status' => 'desc', 'message_time' => 'desc');
            $user_id = $this->session->userdata('user_id');
            $where = array('user_id' => $user_id);
            $link = 'admin/mailbox/index/read_inbox_mail/';
            if (!empty($fav) && $fav == 'trash') {
                $where += array('deleted' => 'Yes');
                $from_trash = true;
            } else if (!empty($fav) && $fav == 'favourites') {
                $where += array('favourites' => 1, 'deleted !=' => 'Yes');
            } else if (!empty($fav) && $fav == 'inbox') {
                $where += array('mail_folder' => 'inbox', 'deleted !=' => 'Yes', 'smtp_host_name' => 'system');
            } else if (!empty($fav) && $fav == 'draft') {
                $link = 'admin/mailbox/index/compose/';
                $where += array('mail_folder' => 'draft', 'deleted !=' => 'Yes', 'smtp_host_name' => 'system');
            } elseif (!empty($fav) && $fav == 'sent') {
                $where += array('mail_folder' => 'sent', 'deleted !=' => 'Yes', 'smtp_host_name' => 'system');
            }
            $fetch_data = make_datatables($where);
            $data = array();
            foreach ($fetch_data as $v_inbox_msg) {
                $sub_array = array();
                $fav = '';
                $fav .= '<div class="checkbox c-checkbox" ><label class="needsclick"> <input class="child_present" name="selected_id[]"  value="' . $v_inbox_msg->inbox_id . '" type="checkbox"><span class="fa fa-check"></span></label></div>';
                if ($v_inbox_msg->favourites == 1) {
                    $favs = 0;
                    $fav_icon = 'fa-star';
                } else {
                    $fav_icon = 'fa-star-o';
                    $favs = 1;
                }
                $fav .= '<a href="' . base_url('admin/mailbox/index/added_favourites/' . $v_inbox_msg->inbox_id . '/' . $favs) . '"><i class="fa fa-lg ' . $fav_icon . ' text-yellow"></i></a>';
                $sub_array[] = $fav;
                $subject = (strlen($v_inbox_msg->subject) > 50) ? strip_html_tags(mb_substr($v_inbox_msg->subject, 0, 50)) . '...' : $v_inbox_msg->subject;
                
                $subjects = '<a href="' . base_url($link . $v_inbox_msg->inbox_id) . '">';
                $subjects .= '<div class="mb-mail-date pull-right">' . time_ago($v_inbox_msg->message_time);
                if (!empty($from_trash)) {
                    $subjects .= ' <a class="btn btn-primary btn-xs" href="' . base_url('admin/mailbox/restore/remote_host/') . $v_inbox_msg->inbox_id . '" data-toggle="tooltip" data-placement="top" title="" data-original-title="' . lang('restore') . '"><i class="fa fa-retweet"></i></a>';
                    $subjects .= ' <a class="btn btn-danger btn-xs" href="' . base_url('admin/mailbox/delete_mail/remote_host/deleted/') . $v_inbox_msg->inbox_id . '" data-toggle="tooltip" data-placement="top" title="" data-original-title="Restore" onclick="return confirm(' . lang('alert_delete') . ');"><i class="fa fa-trash"></i></a>';
                }
                $subjects .= '</div>';
                
                $subjects .= '<div class="mb-mail-meta ' . ($v_inbox_msg->view_status == 1 ? ' ' : 'bold') . '"   ><div class="pull-left">
                                                        <div class="mb-mail-subject">' . $subject . '</div>
                                                        <div class="mb-mail-from">';
                $string = (strlen($v_inbox_msg->from) > 50) ? strip_html_tags(mb_substr($v_inbox_msg->from, 0, 50)) . '...' : $v_inbox_msg->from;
                $subjects .= $string;
                
                $subjects .= '</div></div><div class="mb-mail-preview">';
                $body = (strlen($v_inbox_msg->message_body) > 100) ? strip_html_tags(mb_substr($v_inbox_msg->message_body, 0, 100)) . '...' : $v_inbox_msg->message_body;
                $subjects .= $body;
                if (!empty($v_inbox_msg->attach_file)) {
                    $uploaded_file = json_decode($v_inbox_msg->attach_file);
                }
                if (!empty($uploaded_file)) {
                    $subjects .= '<small class="block"><i class="fa fa-paperclip"></i></small>';
                }
                $subjects .= '</div></div></a>';
                $sub_array[] = $subjects;
                $sub_array[] = $v_inbox_msg->smtp_host_name;
                $data[] = $sub_array;
            }
            render_table($data, $where);
        } else {
            redirect('admin/dashboard');
        }
    }
    
    
    public function sentList($action = null)
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('datatables');
            $this->datatables->table = 'tbl_inbox';
            $this->datatables->column_order = array('subject', 'to', 'from', 'message_body');
            $this->datatables->column_search = array('subject', 'to', 'from', 'message_body');
            $this->datatables->order = array('inbox_id' => 'desc');
            $where = array('user_id' => my_id());
            if (!empty($action) && $action == 'trash') {
                $where += array('deleted' => 'Yes');
                $from_trash = true;
            } else {
                $where += array('deleted !=' => 'Yes');
            }
            $fetch_data = make_datatables($where);
            $data = array();
            foreach ($fetch_data as $key => $v_inbox_msg) {
                $sub_array = array();
                $fav = '';
                $fav .= '<div class="checkbox c-checkbox" ><label class="needsclick"> <input class="child_present" name="selected_id[]"  value="' . $v_inbox_msg->inbox_id . '" type="checkbox"><span class="fa fa-check"></span></label></div>';
                $sub_array[] = $fav;
                $subject = (strlen($v_inbox_msg->subject) > 50) ? strip_html_tags(mb_substr($v_inbox_msg->subject, 0, 50)) . '...' : $v_inbox_msg->subject;
                
                $subjects = '<a href="' . base_url('admin/mailbox/index/read_send_mail/' . $v_inbox_msg->inbox_id) . '">';
                $subjects .= '<div class="mb-mail-date pull-right">' . time_ago($v_inbox_msg->message_time);
                if (!empty($from_trash)) {
                    $subjects .= ' <a class="btn btn-primary btn-xs" href="' . base_url('admin/mailbox/restore/sent/') . $v_inbox_msg->inbox_id . '" data-toggle="tooltip" data-placement="top" title="" data-original-title="' . lang('restore') . '"><i class="fa fa-retweet"></i></a>';
                    $subjects .= ' <a class="btn btn-danger btn-xs" href="' . base_url('admin/mailbox/delete_mail/sent/deleted/') . $v_inbox_msg->inbox_id . '" data-toggle="tooltip" data-placement="top" title="" data-original-title="alert_delete" onclick="return confirm(' . lang('alert_delete') . ');"><i class="fa fa-trash"></i></a>';
                }
                $subjects .= '</div>';
                
                $subjects .= '<div class="mb-mail-meta"><div class="pull-left">
                                                        <div class="mb-mail-subject">' . $subject . '</div>
                                                        <div class="mb-mail-from">';
                $subjects .= '</div></div><div class="mb-mail-preview">';
                $body = (strlen($v_inbox_msg->message_body) > 100) ? strip_html_tags(mb_substr($v_inbox_msg->message_body, 0, 100)) . '...' : $v_inbox_msg->message_body;
                $subjects .= $body;
                $uploaded_file = json_decode($v_inbox_msg->attach_file);
                if (!empty($uploaded_file)) {
                    $subjects .= '<small class="block"><i class="fa fa-paperclip"></i></small>';
                }
                $subjects .= '</div></div></a>';
                $sub_array[] = $subjects;
                
                $data[] = $sub_array;
            }
            render_table($data, $where);
        } else {
            redirect('admin/dashboard');
        }
    }
    
    
    function mailboxes($imap)
    {
        $mail_Boxes = array();
        $mailBoxes = $imap->getFolders();
        foreach ($mailBoxes as $mb) {
            $res = explode('/', $mb);
            $v1 = $res[0];
            $mail_Boxes[$v1]['main'] = $res[0];
            if (count($res) > 1) {
                $a = $mail_Boxes[$v1]['sub'][$mb] = mb_convert_encoding($res[1], "utf-8", "UTF7-IMAP");
                $search = 'all';
                if (preg_match("/{$search}/i", $a)) {
                    unset($mail_Boxes[$v1]['sub'][$mb]);
                }
            }
        }
        
        if (isset($mail_Boxes['[Gmail]'])) {
            $mail_Boxes['#'] = $mail_Boxes['[Gmail]'];
            unset($mail_Boxes['[Gmail]']);
        }
        $udata = array(
            'smtp_menu' => json_encode($mail_Boxes)
        );
        $user_id = $this->session->userdata('user_id');
        $this->db->where('user_id', $user_id);
        $this->db->update('tbl_users', $udata);
        return $mail_Boxes;
    }
    
    
    function fetch_remote_mails()
    {
        $user_id = $this->session->userdata('user_id');
        $user_info = $this->profile;
        $now = time();
        $imap = $this->imap_connection($user_info);
        if ($imap) {
            $gmailBoxes = $this->mailboxes($imap);
            foreach ($gmailBoxes as $k => $f) {
                if ($k != '#') {
                    $this->fetch_mails_2($imap, $user_info, $k);
                }
                if (isset($f['sub'])) {
                    foreach ($f['sub'] as $k2 => $v2) {
                        $this->fetch_mails_2($imap, $user_info, $k2);
                    }
                }
            }
            $now = time();
            $this->db->query("UPDATE tbl_users SET smtp_syn_time = $now WHERE user_id='$user_id'");
        }
        
        $res['success'] = '1';
        echo json_encode($res);
        exit();
    }
    
    function imap_connection($user_info)
    {
        $mailbox = $user_info->smtp_host_name;
        $username = $user_info->smtp_username;
        if (empty($username)) {
            $username = $user_info->active_email;
        }
        $password = decrypt($user_info->smtp_password);
        $encryption = $user_info->smtp_encription;
        $imap = new Imap_zisco($mailbox, $username, $password, $encryption);
        if ($imap->isConnected() === false) {
            $activity['module'] = lang('mailbox');
            $activity['activity'] = 'failed_to_connect_import_tickets';
            $activity['value1'] = $user_info->username;
            $activity['value2'] = 'fetch_mails';
            activity_log($activity);
        } else {
            return $imap;
        }
        return false;
    }
    
    function fetch_mails_2($imap, $user_info, $folder)
    {
        if (!empty($imap)) {
            if (isset($folder)) {
                $tbl = 'tbl_inbox';
                $imap->selectFolder($folder);
                if ($user_info->smtp_unread_email == 1 && $folder == 'INBOX') {
                    $emails = $imap->getUnreadMessages(true, $folder);
                } else {
                    $emails = $imap->getMessages(true, $folder);
                }
                $counter = 1;
                $idata = array();
                if (!empty($emails)) {
                    foreach ($emails as $email) {
                        $uid = $email['uid'];
                        $mailbox = $user_info->smtp_host_name;
                        $uid_exist = $this->db->query("SELECT mail_uid as uid FROM tbl_inbox WHERE smtp_host_name ='$mailbox' AND mail_folder='$folder' AND mail_uid = '$uid'  LIMIT 1")->row();
                        
                        if (empty($uid_exist)) {
                            $counter = $counter++;
                            $from = $email['from'];
                            $subject = fix_encoding_chars($email['subject']);
                            // Check if empty body
                            if (isset($email['body']) && $email['body'] == '' || !isset($email['body'])) {
                                $email['body'] = 'No message found';
                            }
                            $body = convert_to_body($email['body']);
                            $system_blocked_subjects = [
                                'Mail delivery failed',
                                'failure notice',
                                'Returned mail: see transcript for details',
                                'Undelivered Mail Returned to Sender',
                            ];
                            
                            $subject_blocked = false;
                            foreach ($system_blocked_subjects as $sb) {
                                if (strpos('x' . $subject, $sb) !== false) {
                                    $subject_blocked = true;
                                    break;
                                }
                            }
                            if ($subject_blocked == true) {
                                return;
                            }
                            $to = trim($user_info->active_email);
                            if (!$to) {
                                $message = $mailstatus = 'No active email is found for fetching emails';
                            } else {
                                if ($to == $from) {
                                    $mailstatus = 'block_potential_email';
                                } else {
                                    $idata[$uid]['from_user_id'] = 0;
                                    $idata[$uid]['user_id'] = $user_info->user_id;
                                    $idata[$uid]['mail_uid'] = $email['uid'];
                                    $idata[$uid]['to'] = $to;
                                    $idata[$uid]['from'] = $from;
                                    $idata[$uid]['subject'] = $subject;
                                    $idata[$uid]['message_body'] = fix_encoding_chars($body);
                                    $idata[$uid]['message_time'] = date('Y-m-d H:i:s');
                                    $idata[$uid]['mail_folder'] = $folder;
                                    $idata[$uid]['smtp_host_name'] = $mailbox;
                                    
                                    $up_data = [];
                                    if (isset($email['attachments'])) {
                                        foreach ($email['attachments'] as $key => $attachment) {
                                            $email_attachment = $imap->getAttachment($email['uid'], $key);
                                            $path = module_dirPath(MAILBOX_MODULE) . "uploads/";
                                            $file_name = unique_filename($path, $attachment['name']);
                                            $path = $path . $file_name;
                                            $is_image = check_image_extension($file_name);
                                            $fp = fopen($path, 'w+');
                                            if (fwrite($fp, $email_attachment['content'])) {
                                                $up_data[] = [
                                                    'fileName' => $file_name,
                                                    "path" => $path,
                                                    "is_image" => $is_image,
                                                    "fullPath" => $path . $file_name,
                                                    "size" => $attachment['size'] * 1024,
                                                ];
                                            }
                                            fclose($fp);
                                        }
                                        $idata[$uid]['attach_file'] = json_encode($up_data);
                                    } else {
                                        $idata[$uid]['attach_file'] = '';
                                    }
                                }
                            }
                            if ($counter == 30) {
                                break;
                            }
                        }
                    }
                }
                if (count($idata) > 0) {
                    $suc = $this->db->insert_batch($tbl, $idata);
                    if (!empty($suc)) {
                        foreach ($emails as $email) {
                            if ($user_info->smtp_delete_mail_after_import == 0) {
                                $imap->setUnseenMessage($email['uid']);
                            } else {
                                $imap->deleteMessage($email['uid']);
                            }
                        }
                    }
                    
                    $activities = array(
                        'user' => $this->session->userdata('user_id'),
                        'module' => 'mailbox',
                        'module_field_id' => 0,
                        'activity' => 'activity_fetch_user_emails',
                        'icon' => 'fa-mail',
                        'value1' => key(array_slice($idata, 0, 1, TRUE)),
                        'value2' => key(array_slice($idata, -1, 1, TRUE)),
                    );
                    $this->mailbox_model->_table_name = "tbl_activities"; //table name
                    $this->mailbox_model->_primary_key = "activities_id";
                    $this->mailbox_model->save($activities);
                }
            }
        }
    }
    
    public function send_mail()
    {
        $discard = $this->input->post('discard', TRUE);
        if (!empty($discard)) {
            redirect('admin/mailbox/index/inbox');
        }
        $all_email = $this->input->post('to', TRUE);
        $data = $this->mailbox_model->array_from_post(array('subject', 'message_body', 'cc'));
        $upload_file = array();
        $resourceed_file = array();
        $files = $this->input->post("files", true);
        $target_path = module_dirPath(MAILBOX_MODULE) . "uploads/";
        if (!empty($files) && is_array($files)) {
            foreach ($files as $file) {
                if (!empty($file)) {
                    $file_name = $this->input->post('file_name_' . $file, true);
                    $new_file_name = move_temp_file($file_name, $target_path);
                    $file_ext = explode(".", $new_file_name);
                    $is_image = check_image_extension($new_file_name);
                    $size = $this->input->post('file_size_' . $file, true) / 1000;
                    if ($new_file_name) {
                        $up_data = array(
                            "fileName" => $new_file_name,
                            "path" => "uploads/" . $new_file_name,
                            "fullPath" => $target_path . $new_file_name,
                            "ext" => '.' . end($file_ext),
                            "size" => round($size, 2),
                            "is_image" => $is_image,
                        );
                        array_push($upload_file, $up_data);
                        array_push($resourceed_file, $new_file_name);
                    }
                }
            }
        }
        
        $fileName = $this->input->post('fileName', true);
        $path = $this->input->post('path', true);
        $fullPath = $this->input->post('fullPath', true);
        $size = $this->input->post('size', true);
        $is_image = $this->input->post('is_image', true);
        
        if (!empty($fileName)) {
            foreach ($fileName as $key => $name) {
                $old['fileName'] = $name;
                $old['path'] = $path[$key];
                $old['fullPath'] = $fullPath[$key];
                $old['size'] = $size[$key];
                $old['is_image'] = $is_image[$key];
                
                array_push($upload_file, $old);
                array_push($resourceed_file, $name);
            }
        }
        
        if (!empty($upload_file)) {
            $data['attach_file'] = json_encode($upload_file);
            $idata['attach_file'] = json_encode($upload_file);
        } else {
            $data['attach_file'] = null;
            $idata['attach_file'] = NULL;
        }
        $user_id = $this->session->userdata('user_id');
        $user_info = $this->mailbox_model->check_by(array('user_id' => $user_id), 'tbl_users');
        $subject = $data['subject'];
        $data['user_id'] = $user_id;
        $data['message_time'] = date('Y-m-d H:i:s');
        $draft = $this->input->post('draf', TRUE);
        $data['to'] = serialize($all_email);
        $data['from'] = $user_info->active_email;
        $data['from_user_id'] = $user_id;
        if (!empty($draft)) {
            $data['smtp_host_name'] = 'system';
            $data['mail_folder'] = 'draft';
            $this->mailbox_model->_table_name = 'tbl_inbox';
            $this->mailbox_model->_primary_key = 'inbox_id';
            $this->mailbox_model->save($data);
            redirect('admin/mailbox/index/draft');
        } else {
            $data['mail_folder'] = 'sent';
            $data['smtp_host_name'] = 'system';
            $this->mailbox_model->_table_name = 'tbl_inbox';
            $this->mailbox_model->_primary_key = 'inbox_id';
            $send_id = $this->mailbox_model->save($data);
            
            $rata['read_mail'] = get_row('tbl_inbox', array('inbox_id' => $send_id));
            $message = $this->load->view('send_email', $rata, TRUE);
            
            $params['subject'] = $subject;
            $params['message'] = $message;
            foreach ($all_email as $v_email) {
                $to_user_id = get_any_field('tbl_users', array('email' => $v_email), 'user_id');
                if (!empty($to_user_id)) {
                    $idata['to'] = $v_email;
                    $idata['cc'] = $data['cc'];
                    $idata['from'] = $user_info->active_email;
                    $idata['from_user_id'] = $user_id;
                    $idata['user_id'] = $to_user_id;
                    $idata['subject'] = $data['subject'];
                    $idata['message_body'] = $data['message_body'];
                    $idata['message_time'] = date('Y-m-d H:i:s');
                    $idata['smtp_host_name'] = 'system';
                    $idata['mail_folder'] = 'inbox';
                    $this->mailbox_model->_table_name = 'tbl_inbox';
                    $this->mailbox_model->_primary_key = 'inbox_id';
                    $this->mailbox_model->save($idata);
                }
                $activity = array(
                    'user' => $this->session->userdata('user_id'),
                    'module' => 'mailbox',
                    'module_field_id' => $send_id,
                    'activity' => lang('activity_msg_sent'),
                    'icon' => 'fa-circle-o',
                    'value1' => $v_email
                );
                
                $this->mailbox_model->_table_name = 'tbl_activities';
                $this->mailbox_model->_primary_key = 'activities_id';
                $this->mailbox_model->save($activity);
            }
        }
        $params['recipient'] = implode(",", $all_email);
        $params['cc'] = $data['cc'];
        $params['resourceed_file'] = $resourceed_file;
        $send_email = $this->mailbox_model->send_mail($params);
        
        if ($send_email) {
            $type = "success";
            $message = lang('msg_sent');
            set_message($type, $message);
        } else {
            set_message('error', lang('mail_not_sent'));
            show_error($this->email->print_debugger());
        }
        redirect('admin/mailbox/index/sent');
    }
    
    
    public function settings()
    {
        $user_id = $this->session->userdata('user_id');
        $user_info = $this->admin_model->check_by(array('user_id' => $user_id), 'tbl_users');
        
        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div>', '</div>');
        $this->form_validation->set_rules('smtp_email', 'Email', 'required|valid_email');
        if (empty($user_info->smtp_password)) {
            $this->form_validation->set_rules('smtp_password', 'Password for  Email', 'required');
        }
        $this->form_validation->set_rules('smtp_host_name', 'IMAP Host Name', 'required');
        $this->form_validation->set_rules('mail_host', 'SMTP Host Name', 'required');
        if ($this->input->post()) {
            if ($this->form_validation->run() == true) {
                $data['active_email'] = $this->input->post('smtp_email', true);
                $data['smtp_username'] = $this->input->post('smtp_username', true);
                $password = $this->input->post('smtp_password', false);
                $data['smtp_host_name'] = $this->input->post('smtp_host_name', true);
                $data['mail_host'] = $this->input->post('mail_host', true);
                $data['smtp_encription'] = $this->input->post('smtp_encryption', true);
                $data['smtp_email_type'] = $this->input->post('protocol', true);
                $data['smtp_unread_email'] = $this->input->post('smtp_unread_email', true);
                $data['smtp_delete_mail_after_import'] = $this->input->post('smtp_delete_mail_after_import', true);
                
                if (!empty($password)) {
                    $data['smtp_password'] = encrypt($password);
                } else {
                    $data['smtp_password'] = $user_info->smtp_password;
                }
                if ($data['smtp_encription'] == 'no') {
                    $data['smtp_encription'] = null;
                }
                if (empty($data['smtp_unread_email'])) {
                    $data['smtp_unread_email'] = 0;
                }
                if (empty($data['smtp_delete_mail_after_import'])) {
                    $data['smtp_delete_mail_after_import'] = 0;
                }
                
                if (isset($_POST['test_settings'])) {
                    $res = $this->test_email();
                    $rdata = array();
                    if ($res['type'] == 'success') {
                        $rdata['imap_type'] = 'success';
                        $rdata['imap_msg'] .= lang('imap') . ':  ' . lang('connection_success') . '</br>';
                    } else {
                        $rdata['imap_type'] = 'danger';
                        $rdata['imap_msg'] .= lang('imap') . ':  ' . lang('connection_unsuccess') . '</br>' . $res['msg'];
                    }
                    $res2 = $this->test_smtp_host();
                    if ($res2['type'] == 'success') {
                        $rdata['smtp_type'] = 'success';
                        $rdata['smtp_msg'] = lang('smtp') . ':  ' . lang('connection_success') . '</br>';
                    } else {
                        $rdata['smtp_type'] = 'danger';
                        $rdata['smtp_msg'] = lang('smtp') . ':  ' . lang('connection_unsuccess') . '</br>' . preg_replace('/<(pre)(?:(?!<\/?\1).)*?<\/\1>/s', "", $res2['msg']);;
                    }
                    $rdata['form_error'] = 'yes';
                    $this->session->set_userdata($rdata);
                } else {
                    $this->mailbox_model->_table_name = 'tbl_users';
                    $this->mailbox_model->_primary_key = 'user_id';
                    $suc = $this->mailbox_model->save($data, $user_id);
                    if (!empty($suc)) {
                        $type = "success";
                        $message = lang('user_email_integration');
                        $action = ('activity_user_email_integration');
                        $activity = array(
                            'user' => $this->session->userdata('user_id'),
                            'module' => 'settings',
                            'module_field_id' => $user_id,
                            'activity' => $action,
                            'value1' => $user_info->username,
                            'value2' => $this->input->post('smtp_email'),
                        );
                        $this->mailbox_model->_table_name = 'tbl_activities';
                        $this->mailbox_model->_primary_key = 'activities_id';
                        $this->mailbox_model->save($activity);
                    } else {
                        $type = 'error';
                        $message = 'something wrong';
                    }
                    set_message($type, $message);
                    redirect('admin/mailbox/settings');
                }
            } else {
                $s_data['form_error'] = validation_errors();
                $this->session->set_userdata($s_data);
            }
            redirect('admin/mailbox/settings');
        }
        
        $rata['title'] = lang('mailbox_settings');
        $rata['subview'] = $this->load->view('settings', $rata, TRUE);
        $this->load->view($this->view . '_layout_main', $rata);
    }
    
    public function test_smtp_host()
    {
        $smtp_encription = $this->input->post('smtp_encryption', true);
        $smtp_email = $this->input->post('smtp_email', true);
        $username = $this->input->post('smtp_username', true);
        $mail_host = $this->input->post('mail_host', true);
        $smtp_protocol = $this->input->post('protocol', true);
        $user_id = $this->session->userdata('user_id');
        $user_info = $this->admin_model->check_by(array('user_id' => $user_id), 'tbl_users');
        if (empty($smtp_protocol)) {
            $smtp_protocol = $user_info->smtp_email_type;
        }
        if (empty($smtp_protocol)) {
            $smtp_protocol = 'smtp';
        }
        if (empty($username)) {
            $username = $smtp_email;
        }
        $password = $this->input->post('smtp_password', false);
        if (empty($password)) {
            $password = decrypt($user_info->smtp_password);
        }
        $smtp_port = $this->input->post('smtp_port', true);
        if (empty($smtp_port)) {
            if ($smtp_encription == 'tls') {
                $smtp_port = '587';
            } elseif ($smtp_encription == 'ssl') {
                $smtp_port = '465';
            } else {
                $smtp_port = '25';
            }
        }
        $config['wordwrap'] = TRUE;
        $config['mailtype'] = "html";
        $config['charset'] = 'utf-8';
        $config['newline'] = "\r\n";
        $config['crlf'] = "\r\n";
        $config['smtp_timeout'] = '30';
        
        $config['protocol'] = $smtp_protocol;
        $config['smtp_host'] = $mail_host;
        $config['smtp_port'] = $smtp_port;
        $config['smtp_user'] = trim($smtp_email);
        $config['smtp_pass'] = $password;
        $config['smtp_crypto'] = $smtp_encription;
        
        $this->load->library('email', $config);
        $this->email->from($smtp_email);
        $this->email->to($smtp_email);
        $this->email->subject('For testing your SMTP settings');
        $domain = $_SERVER['HTTP_HOST'];
        $this->email->message("This test mail was sent for testing your SMTP settings from the system : $domain.");
        $send = $this->email->send();
        if ($send) {
            $d['type'] = 'success';
        } else {
            $d['type'] = 'error';
            $d['msg'] = $this->email->print_debugger(array('subject'));
        }
        return $d;
    }
    
    public function test_email($etype = null)
    {
        if (!empty($etype) && $etype == 'Leads') {
            $username = config_item('config_username');
            $password = decrypt(config_item('config_password'));
            $mailbox = config_item('config_host');
            $encryption = config_item('encryption');
        } elseif (!empty($etype) && is_numeric($etype)) {
            $dept_info = get_row('tbl_departments', array('departments_id' => $etype));
            $username = $dept_info->username;
            $password = decrypt($dept_info->password);
            $mailbox = $dept_info->host;
            $encryption = $dept_info->encryption;
        } else {
            $smtp_email = $this->input->post('smtp_email', true);
            $username = $this->input->post('smtp_username', true);
            if (empty($username)) {
                $username = $smtp_email;
            }
            $password = $this->input->post('smtp_password', false);
            if (empty($password)) {
                $user_id = $this->session->userdata('user_id');
                $user_info = $this->admin_model->check_by(array('user_id' => $user_id), 'tbl_users');
                $password = decrypt($user_info->smtp_password);
            }
            
            $mailbox = $this->input->post('smtp_host_name', true);
            $encryption = $this->input->post('smtp_encryption', true);
        }
        
        if (!empty($mailbox)) {
            $imap = new Imap_zisco($mailbox, $username, $password, $encryption);
            if ($imap->isConnected() === false) {
                $d['type'] = 'error';
                $d['msg'] = $imap->getError();
            } else {
                $d['type'] = 'success';
            }
            $imap->close();
            return $d;
        }
    }
    
    public function delete_mail($action, $from_trash = NULL, $v_id = NULL)
    {
        $selected_id = $this->input->post('selected_id', TRUE);
        if (!empty($selected_id)) { // check selected message is empty or not
            
            foreach ($selected_id as $v_id) {
                if ($action == 'trash' || !empty($from_trash)) {
                    $this->mailbox_model->_table_name = 'tbl_inbox';
                    $this->mailbox_model->delete_multiple(array('inbox_id' => $v_id));
                    if ($action == 'inbox' || $action == 'remote_host') {
                        $activities = lang('activity_delete_tash_inbox');
                        if ($action == 'remote_host') {
                            $activities = lang('activity_delete_remote_host_mail');
                        }
                        $inbox_info = $this->mailbox_model->check_by(array('inbox_id' => $v_id), 'tbl_inbox');
                        
                        $activity = array(
                            'user' => $this->session->userdata('user_id'),
                            'module' => 'mailbox',
                            'module_field_id' => $v_id,
                            'activity' => $activities,
                            'icon' => 'fa-circle-o',
                            'value1' => $inbox_info->to
                        );
                        $this->mailbox_model->_table_name = 'tbl_activities';
                        $this->mailbox_model->_primary_key = 'activities_id';
                        $this->mailbox_model->save($activity);
                    } elseif ($action == 'sent') {
                        $activities = lang('activity_delete_tash_sent');
                        $inbox_info = $this->mailbox_model->check_by(array('inbox_id' => $v_id), 'tbl_inbox');
                        $activity = array(
                            'user' => $this->session->userdata('user_id'),
                            'module' => 'mailbox',
                            'module_field_id' => $v_id,
                            'activity' => $activities,
                            'icon' => 'fa-circle-o',
                            'value1' => $inbox_info->to
                        );
                        $this->mailbox_model->_table_name = 'tbl_activities';
                        $this->mailbox_model->_primary_key = 'activities_id';
                        $this->mailbox_model->save($activity);
                    } else {
                        $activities = lang('activity_delete_tash_draft');
                        $inbox_info = $this->mailbox_model->check_by(array('inbox_id' => $v_id), 'tbl_inbox');
                        if (!empty($inbox_info)) {
                            $activity = array(
                                'user' => $this->session->userdata('user_id'),
                                'module' => 'mailbox',
                                'module_field_id' => $v_id,
                                'activity' => $activities,
                                'icon' => 'fa-circle-o',
                                'value1' => $inbox_info->to
                            );
                            $this->mailbox_model->_table_name = 'tbl_activities';
                            $this->mailbox_model->_primary_key = 'activities_id';
                            $this->mailbox_model->save($activity);
                            
                            $this->mailbox_model->_table_name = 'tbl_inbox';
                            $this->mailbox_model->delete_multiple(array('inbox_id' => $v_id));
                        }
                    }
                } else {
                    $value = array('deleted' => 'Yes');
                    if ($action == 'remote_host') {
                        $activities = lang('activity_delete_remote_host_mail');
                    } elseif ($action == 'inbox') {
                        $activities = lang('activity_delete_inbox');
                    } elseif ($action == 'sent') {
                        $activities = lang('activity_delete_sent');
                    } else {
                        $activities = lang('activity_delete_draft');
                    }
                    
                    $this->mailbox_model->set_action(array('inbox_id' => $v_id), $value, 'tbl_inbox');
                    $inbox_info = $this->mailbox_model->check_by(array('inbox_id' => $v_id), 'tbl_inbox');
                    
                    $activity = array(
                        'user' => $this->session->userdata('user_id'),
                        'module' => 'mailbox',
                        'module_field_id' => $v_id,
                        'activity' => $activities,
                        'icon' => 'fa-circle-o',
                        'value1' => $inbox_info->to
                    );
                    $this->mailbox_model->_table_name = 'tbl_activities';
                    $this->mailbox_model->_primary_key = 'activities_id';
                    $this->mailbox_model->save($activity);
                }
            }
            $type = "success";
            $message = lang('delete_msg');
        } elseif (!empty($v_id)) {
            if ($action == 'inbox' || $action == 'remote_host') {
                $activities = lang('activity_delete_tash_inbox');
                if ($action == 'remote_host') {
                    $activities = lang('activity_delete_remote_host_mail');
                }
                $inbox_info = $this->mailbox_model->check_by(array('inbox_id' => $v_id), 'tbl_inbox');
                
                $activity = array(
                    'user' => $this->session->userdata('user_id'),
                    'module' => 'mailbox',
                    'module_field_id' => $v_id,
                    'activity' => $activities,
                    'icon' => 'fa-circle-o',
                    'value1' => $inbox_info->to
                );
                $this->mailbox_model->_table_name = 'tbl_activities';
                $this->mailbox_model->_primary_key = 'activities_id';
                $this->mailbox_model->save($activity);
                
                $this->mailbox_model->_table_name = 'tbl_inbox';
                $this->mailbox_model->delete_multiple(array('inbox_id' => $v_id));
            } elseif ($action == 'sent') {
                $activities = lang('activity_delete_sent');
                $inbox_info = $this->mailbox_model->check_by(array('inbox_id' => $v_id), 'tbl_inbox');
                
                $activity = array(
                    'user' => $this->session->userdata('user_id'),
                    'module' => 'mailbox',
                    'module_field_id' => $v_id,
                    'activity' => $activities,
                    'icon' => 'fa-circle-o',
                    'value1' => $inbox_info->to
                );
                $this->mailbox_model->_table_name = 'tbl_activities';
                $this->mailbox_model->_primary_key = 'activities_id';
                $this->mailbox_model->save($activity);
                
                $this->mailbox_model->_table_name = 'tbl_inbox';
                $this->mailbox_model->delete_multiple(array('inbox_id' => $v_id));
            } else {
                $activities = lang('activity_delete_tash_draft');
                $inbox_info = $this->mailbox_model->check_by(array('inbox_id' => $v_id), 'tbl_inbox');
                $activity = array(
                    'user' => $this->session->userdata('user_id'),
                    'module' => 'mailbox',
                    'module_field_id' => $v_id,
                    'activity' => $activities,
                    'icon' => 'fa-circle-o',
                    'value1' => $inbox_info->to
                );
                $this->mailbox_model->_table_name = 'tbl_activities';
                $this->mailbox_model->_primary_key = 'activities_id';
                $this->mailbox_model->save($activity);
                
                $this->mailbox_model->_table_name = 'tbl_inbox';
                $this->mailbox_model->delete_multiple(array('inbox_id' => $v_id));
            }
            $type = "success";
            $message = lang('delete_msg');
        } else {
            $type = "error";
            $message = lang('select_message');
        }
        set_message($type, $message);
        if ($action == 'inbox') {
            redirect('admin/mailbox/index/inbox');
        } elseif ($action == 'sent') {
            redirect('admin/mailbox/index/sent');
        } else if ($action == 'draft') {
            redirect('admin/mailbox/index/draft');
        } else if ($action == 'favourites') {
            redirect('admin/mailbox/index/favourites');
        } else {
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    
    public function restore($action, $id)
    {
        $value = array('deleted' => 'No');
        if ($action == 'inbox') {
            $this->mailbox_model->set_action(array('inbox_id' => $id), $value, 'tbl_inbox');
        } elseif ($action == 'sent') {
            $this->mailbox_model->set_action(array('inbox_id' => $id), $value, 'tbl_inbox');
        } else {
            $this->mailbox_model->set_action(array('inbox_id' => $id), $value, 'tbl_inbox');
        }
        
        if ($action == 'inbox') {
            redirect('admin/mailbox/index/inbox');
        } elseif ($action == 'sent') {
            redirect('admin/mailbox/index/sent');
        } elseif ($action == 'draft') {
            redirect('admin/mailbox/index/sent');
        } else {
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    
    public function download_file($file)
    {
        $this->load->helper('download');
        if (file_exists(('modules/mailbox/uploads/' . $file))) {
            $down_data = file_get_contents('modules/mailbox/uploads/' . $file); // Read the file's contents
            force_download($file, $down_data);
        } else {
            $type = "error";
            $message = 'Operation Fieled !';
            set_message($type, $message);
            if (empty($_SERVER['HTTP_REFERER'])) {
                redirect('admin/mailbox');
            } else {
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }
}
