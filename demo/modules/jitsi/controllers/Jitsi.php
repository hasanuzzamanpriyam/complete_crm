<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Jitsi Video Meeting Controller
 *
 * Manages Jitsi meetings with JWT authentication
 */
class Jitsi extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (empty(my_id())) {
            redirect('login');
        }
        $this->load->model('jitsi/Jitsi_model', 'jitsi_model');
    }

    /**
     * Main index - meeting list and create form
     */
    public function index($id = NULL)
    {
        if (!can_action_by_label('jitsi', 'view')) {
            access_denied('jitsi');
        }
        $data['title'] = lang('jitsi');
        if (!empty($id)) {
            if (!can_action_by_label('jitsi', 'edited')) {
                access_denied('jitsi');
            }
            $data['active'] = 2;
            $data['meeting_info'] = $this->db->where('jitsi_meeting_id', $id)->get('tbl_jitsi_meetings')->row();
        } else {
            $data['active'] = 1;
        }
        $data['subview'] = $this->load->view('manage', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    /**
     * Settings page for Jitsi configuration
     */
    public function settings($id = null)
    {
        if ($this->session->userdata('user_type') != 1) {
            access_denied('jitsi');
        }
        $data['active'] = 1;
        $data['title'] = lang('jitsi_settings');

        $input = $this->admin_model->array_from_post([
            'jitsi_domain',
            'jitsi_app_id',
            'jitsi_private_key',
            'jitsi_public_key',
        ]);

        if (!empty($this->input->post())) {
            if (!empty($input['jitsi_domain']) && !empty($input['jitsi_app_id']) && !empty($input['jitsi_private_key'])) {
                foreach ($input as $key => $value) {
                    $save_value = $value;
                    if ($key === 'jitsi_private_key') {
                        if ($value === '*** Key is configured ***') {
                            continue;
                        }
                        $save_value = encrypt($value);
                    }
                    $data = ['value' => $save_value];
                    $this->db->where('config_key', $key)->update('tbl_config', $data);
                    $exists = $this->db->where('config_key', $key)->get('tbl_config');
                    if ($exists->num_rows() == 0) {
                        $this->db->insert('tbl_config', ['config_key' => $key, 'value' => $save_value]);
                    }
                }
                $type = 'success';
                $message = lang('jitsi_config_saved');
            } else {
                $type = 'error';
                $message = lang('all_field_required');
            }
            set_message($type, $message);
            redirect('admin/jitsi');
        }

        $data['subview'] = $this->load->view('settings', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    /**
     * AJAX: Fetch meetings list for DataTable
     */
    public function meetingList()
    {
        if ($this->input->is_ajax_request()) {
            if (!can_action_by_label('jitsi', 'view')) {
                access_denied('jitsi');
            }
            $this->load->model('datatables');
            $this->datatables->table = 'tbl_jitsi_meetings';
            $this->datatables->join_table = ['tbl_account_details'];
            $this->datatables->join_where = ['tbl_account_details.user_id=tbl_jitsi_meetings.host'];
            $this->datatables->column_order = ['topic', 'fullname', 'status', 'meeting_time'];
            $this->datatables->column_search = ['topic', 'fullname', 'status', 'meeting_time'];
            $this->datatables->order = ['jitsi_meeting_id' => 'desc'];

            $where = null;
            $fetch_data = make_datatables($where);

            $data = [];
            $edited = can_action_by_label('jitsi', 'edited');
            $deleted = can_action_by_label('jitsi', 'deleted');

            foreach ($fetch_data as $_key => $v_meeting) {
                $action = null;
                $sub_array = [];
                $sub_array[] = $v_meeting->topic;
                $sub_array[] = display_datetime($v_meeting->meeting_time);
                $sub_array[] = $v_meeting->notes;

                if ($v_meeting->status == 'finished') {
                    $d = 'success';
                } elseif ($v_meeting->status == 'canceled') {
                    $d = 'danger';
                } else {
                    $d = 'primary';
                }

                $change_status = '<div class="btn btn-xs btn-' . $d . '">' . lang($v_meeting->status) . '</div>';
                $ch_url = base_url() . 'admin/jitsi/change_status/' . $v_meeting->jitsi_meeting_id;
                $astatus_info = ['waiting', 'finished', 'canceled'];
                $change_status .= '<div class="btn-group">
                    <button class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu animated zoomIn">';
                foreach ($astatus_info as $v_status) {
                    $change_status .= '<li><a href="' . $ch_url . '/' . $v_status . '">' . lang($v_status) . '</a></li>';
                }
                $change_status .= '</ul></div>';
                $sub_array[] = $change_status;

                $join_btn = '';
                if ($v_meeting->status == 'finished' || $v_meeting->status == 'canceled') {
                    $tooltip_title = ($v_meeting->status == 'finished') ? lang('meeting_ended') : lang('canceled');
                    $join_btn .= '<button class="btn btn-default btn-xs" disabled data-toggle="tooltip" data-placement="top" title="' . $tooltip_title . '"><i class="fa fa-video-camera"></i></button>';
                } else {
                    if ($v_meeting->host == my_id()) {
                        $join_btn .= '<a target="_blank" data-toggle="tooltip" data-placement="top" title="' . lang('start_as_host') . '" class="btn btn-warning btn-xs" href="' . base_url('admin/jitsi/join/' . url_encode($v_meeting->jitsi_meeting_id)) . '"><i class="fa fa-video-camera"></i> </a>';
                    } else {
                        $join_btn .= '<a target="_blank" data-toggle="tooltip" data-placement="top" title="' . lang('join_the_meeting') . '" class="btn btn-warning btn-xs" href="' . base_url('admin/jitsi/joined/' . url_encode($v_meeting->jitsi_meeting_id)) . '"><i class="fa fa-video-camera"></i> </a>';
                    }
                    $share_url = base_url('jitsi/share/' . url_encode($v_meeting->jitsi_meeting_id));
                    $join_btn .= ' <button class="btn btn-info btn-xs copy-meeting-link" data-link="' . $share_url . '" data-toggle="tooltip" data-placement="top" title="Copy Shareable Link" onclick="copyMeetingLink(this, \'' . $share_url . '\')"><i class="fa fa-share-alt"></i></button>';
                }
                $sub_array[] = $join_btn;

                if (!empty($edited) || !empty($deleted)) {
                    if (!empty($edited)) {
                        $action .= btn_edit('admin/jitsi/index/' . $v_meeting->jitsi_meeting_id) . ' ';
                    }
                    if (!empty($deleted)) {
                        $action .= ajax_anchor(base_url("admin/jitsi/delete_meeting/" . $v_meeting->jitsi_meeting_id), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", ["class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_" . $_key]) . ' ';
                    }
                }
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
            render_table($data);
        } else {
            redirect('admin/dashboard');
        }
    }

    /**
     * Save or update a meeting
     */
    public function save_meeting($id = null)
    {
        $created = can_action_by_label('jitsi', 'created');
        $edited = can_action_by_label('jitsi', 'edited');

        if (!empty($created) || (!empty($edited) && !empty($id))) {
            $data = $this->admin_model->array_from_post(['topic', 'meeting_time', 'duration', 'notes', 'host']);
            $data['user_id'] = json_encode($this->input->post('user_id', true));
            $data['client_id'] = json_encode($this->input->post('client_id', true));
            $data['leads_id'] = json_encode($this->input->post('leads_id', true));

            if (empty($id)) {
                $data['meeting_room'] = $this->jitsi_model->generate_room_name();
                $data['date_added'] = date('Y-m-d H:i:s');
                $data['added_from'] = my_id();
                $data['status'] = 'waiting';
            }

            $this->jitsi_model->_table_name = 'tbl_jitsi_meetings';
            $this->jitsi_model->_primary_key = 'jitsi_meeting_id';
            $this->jitsi_model->save($data, $id);

            $new_id = $id ?: $this->db->insert_id();

            $activity = [
                'user' => $this->session->userdata('user_id'),
                'module' => 'jitsi',
                'module_field_id' => $new_id,
                'activity' => 'activity_new_custom_field',
                'value1' => $data['topic'],
            ];

            $this->admin_model->_table_name = 'tbl_activities';
            $this->admin_model->_primary_key = 'activities_id';
            $this->admin_model->save($activity);

            $type = "success";
            $message = lang('meeting_information_saved');
        } else {
            $type = "error";
            $message = lang('something_went_wrong');
        }
        set_message($type, $message);
        if ($type == "success") {
            $this->session->set_flashdata('send_invitation_meeting_id', $new_id);
        }
        redirect('admin/jitsi');
    }

    /**
     * Delete a meeting (AJAX)
     */
    public function delete_meeting($id)
    {
        $deleted = can_action_by_label('jitsi', 'deleted');
        if (!empty($deleted)) {
            $field_info = $this->db->where('jitsi_meeting_id', $id)->get('tbl_jitsi_meetings')->row();

            $activity = [
                'user' => $this->session->userdata('user_id'),
                'module' => 'jitsi',
                'module_field_id' => $id,
                'activity' => 'activity_delete_custom_field',
                'value1' => $field_info->topic,
            ];

            $this->admin_model->_table_name = 'tbl_activities';
            $this->admin_model->_primary_key = 'activities_id';
            $this->admin_model->save($activity);

            $this->jitsi_model->_table_name = 'tbl_jitsi_meetings';
            $this->jitsi_model->_primary_key = 'jitsi_meeting_id';
            $this->jitsi_model->delete($id);

            echo json_encode(["status" => 'success', 'message' => lang('delete_meeting_info')]);
            exit();
        } else {
            echo json_encode(["status" => 'error', 'message' => lang('there_in_no_value')]);
            exit();
        }
    }

    /**
     * Change meeting status
     */
    public function change_status($jitsi_meeting_id, $status)
    {
        if (!can_action_by_label('jitsi', 'edited')) {
            access_denied('jitsi');
        }
        $data['meeting_info'] = get_row('tbl_jitsi_meetings', ['jitsi_meeting_id' => $jitsi_meeting_id]);
        if (!empty($data['meeting_info'])) {
            $rdata['status'] = $status;
            update('tbl_jitsi_meetings', ['jitsi_meeting_id' => $jitsi_meeting_id], $rdata);
            $type = "success";
            $message = lang('meeting_information_updated');
        } else {
            $type = "error";
            $message = lang('something_went_wrong');
        }
        set_message($type, $message);
        redirect('admin/jitsi');
    }

    /**
     * Host joins/starts a meeting - generates JWT and redirects
     */
    public function join($jitsi_meeting_id)
    {
        $jitsi_meeting_id = url_decode($jitsi_meeting_id);
        $meeting_info = get_row('tbl_jitsi_meetings', ['jitsi_meeting_id' => $jitsi_meeting_id]);

        if (empty($meeting_info)) {
            set_message('error', lang('something_went_wrong'));
            redirect('admin/jitsi');
        }

        if ($meeting_info->status == 'finished' || $meeting_info->status == 'canceled') {
            $msg = ($meeting_info->status == 'finished') ? lang('meeting_ended') : lang('canceled');
            set_message('error', $msg);
            redirect('admin/jitsi');
        }

        $host_user = get_row('tbl_users', ['user_id' => $meeting_info->host]);
        $user_email = $host_user->email ?: '';
        $user_name = fullname($meeting_info->host);

        $meeting_time = strtotime($meeting_info->meeting_time);
        $duration_minutes = (int) $meeting_info->duration;
        $exp = $meeting_time + ($duration_minutes * 60) + 3600;

        $meeting_url = build_jitsi_url($meeting_info->meeting_room, $user_email, $user_name, true, $exp);

        if ($meeting_info->host == my_id()) {
            $rdata['status'] = 'waiting';
            $rdata['meeting_start'] = date('Y-m-d H:i');
            update('tbl_jitsi_meetings', ['jitsi_meeting_id' => $jitsi_meeting_id], $rdata);
            
            $data['jitsi_meeting_id'] = $jitsi_meeting_id;
            $data['meeting_url'] = $meeting_url;
            $this->load->view('start_transition', $data);
            return;
        }

        redirect($meeting_url);
    }

    /**
     * Participant joins a meeting - generates JWT and redirects
     */
    public function joined($jitsi_meeting_id = null)
    {
        if (!empty(my_id())) {
            $jitsi_meeting_id = url_decode($jitsi_meeting_id);
            $meeting_info = get_row('tbl_jitsi_meetings', ['jitsi_meeting_id' => $jitsi_meeting_id]);

            if (!empty($meeting_info)) {
                if ($meeting_info->status == 'finished' || $meeting_info->status == 'canceled') {
                    $msg = ($meeting_info->status == 'finished') ? lang('meeting_ended') : lang('canceled');
                    set_message('error', $msg);
                    redirect('admin/jitsi');
                }

                if (!empty(staff())) {
                    $user = json_decode($meeting_info->user_id);
                    $id = my_id();
                } elseif (!empty(client())) {
                    $user = json_decode($meeting_info->client_id);
                    $id = client_id();
                }

                if (!empty($user) && in_array($id, $user)) {
                    $this->participant_join($jitsi_meeting_id);
                } else {
                    set_message('error', 'You do not have permission to join this meeting.');
                    redirect('admin/jitsi');
                }
            }
        }
    }

    /**
     * Send email notifications to invited users when meeting starts
     * Uses async email queue instead of synchronous sending.
     */
    public function send_notify_assign_user($jitsi_meeting_id)
    {
        $meeting_info = get_row('tbl_jitsi_meetings', ['jitsi_meeting_id' => $jitsi_meeting_id]);
        $email_template = email_templates(['email_group' => 'jitsi_meeting_start']);

        if (empty($email_template)) {
            $email_template = get_row('tbl_email_templates', ['email_group' => 'jitsi_meeting_start', 'code' => 'en']);
        }

        if (empty($email_template)) {
            return;
        }

        $message = $email_template->template_body;
        $subject = $email_template->subject;

        $subject = str_replace("{HOST}", fullname($meeting_info->host), $subject);
        $subject = str_replace("{TOPIC}", $meeting_info->topic, $subject);
        $subject = str_replace("{SITE_NAME}", config_item('company_name'), $subject);

        $host_name = str_replace("{HOST}", fullname($meeting_info->host), $message);
        $topic_name = str_replace("{TOPIC}", $meeting_info->topic, $host_name);

        $queued_count = 0;

        $user = json_decode($meeting_info->user_id);
        if (!empty($user) && is_array($user)) {
            foreach ($user as $id) {
                $user_email = get_any_field('tbl_users', ['user_id' => $id], 'email');
                if (empty($user_email)) continue;

                $user_name = fullname($id);
                $meeting_url = base_url('admin/jitsi/joined/' . url_encode($jitsi_meeting_id));
                $final_message = str_replace("{USER}", $user_name, $topic_name);
                $final_message = str_replace("{MEETING_URL}", $meeting_url, $final_message);
                $final_message = str_replace("{SITE_NAME}", config_item('company_name'), $final_message);

                $data['message'] = $final_message;
                $email_body = $this->load->view('email_template', $data, TRUE);

                queue_email($user_email, $subject, $email_body, 'jitsi_meeting_start');
                $queued_count++;

                if ($id != $this->session->userdata('user_id')) {
                    add_notification([
                        'to_user_id' => $id,
                        'from_user_id' => true,
                        'description' => 'video_meeting_started',
                        'link' => base_url('admin/jitsi/joined/' . $jitsi_meeting_id),
                        'value' => $meeting_info->topic,
                    ]);
                    show_notification($user);
                }
            }
        }

        $client = json_decode($meeting_info->client_id);
        if (!empty($client) && is_array($client)) {
            foreach ($client as $client_id) {
                $clientInfo = get_row('tbl_client', ['client_id' => $client_id]);
                $recipient_email = client_contact_email($client_id) ?: $clientInfo->email;
                if (empty($recipient_email)) continue;

                $final_message = str_replace("{USER}", $clientInfo->name, $topic_name);
                $final_message = str_replace("{MEETING_URL}", base_url('jitsi/joined/' . url_encode($jitsi_meeting_id)), $final_message);
                $final_message = str_replace("{SITE_NAME}", config_item('company_name'), $final_message);

                $data['message'] = $final_message;
                $email_body = $this->load->view('email_template', $data, TRUE);

                queue_email($recipient_email, $subject, $email_body, 'jitsi_meeting_start');
                $queued_count++;
            }
        }

        $leads = json_decode($meeting_info->leads_id);
        if (!empty($leads) && is_array($leads)) {
            foreach ($leads as $leads_id) {
                $leadsInfo = get_row('tbl_leads', ['leads_id' => $leads_id]);
                if (empty($leadsInfo->email)) continue;

                $final_message = str_replace("{USER}", $leadsInfo->lead_name, $topic_name);
                $final_message = str_replace("{MEETING_URL}", base_url('jitsi/joined/' . url_encode($jitsi_meeting_id)), $final_message);
                $final_message = str_replace("{SITE_NAME}", config_item('company_name'), $final_message);

                $data['message'] = $final_message;
                $email_body = $this->load->view('email_template', $data, TRUE);

                queue_email($leadsInfo->email, $subject, $email_body, 'jitsi_meeting_start');
                $queued_count++;
            }
        }

        if (function_exists('process_email_queue')) {
            process_email_queue(10);
        }

        return $queued_count;
    }

    /**
     * Send email invitations to invited users when a meeting is created
     * Uses async email queue instead of synchronous sending.
     */
    public function send_meeting_invitation($jitsi_meeting_id)
    {
        $meeting_info = get_row('tbl_jitsi_meetings', ['jitsi_meeting_id' => $jitsi_meeting_id]);
        $email_template = email_templates(['email_group' => 'jitsi_meeting_invitation']);

        if (empty($email_template)) {
            $email_template = get_row('tbl_email_templates', ['email_group' => 'jitsi_meeting_invitation', 'code' => 'en']);
        }

        if (empty($email_template)) {
            return;
        }

        $message = $email_template->template_body;
        $subject = $email_template->subject;

        $subject = str_replace("{HOST}", fullname($meeting_info->host), $subject);
        $subject = str_replace("{TOPIC}", $meeting_info->topic, $subject);
        $subject = str_replace("{SITE_NAME}", config_item('company_name'), $subject);

        $host_name = str_replace("{HOST}", fullname($meeting_info->host), $message);
        $topic_name = str_replace("{TOPIC}", $meeting_info->topic, $host_name);
        $meeting_time = str_replace("{MEETING_TIME}", date('M d, Y - h:i A', strtotime($meeting_info->meeting_time)), $topic_name);
        $duration = str_replace("{DURATION}", $meeting_info->duration, $meeting_time);

        $share_url = base_url('jitsi/share/' . url_encode($jitsi_meeting_id));
        $queued_count = 0;

        $user = json_decode($meeting_info->user_id);
        if (!empty($user) && is_array($user)) {
            foreach ($user as $id) {
                $user_email = get_any_field('tbl_users', ['user_id' => $id], 'email');
                if (empty($user_email)) continue;

                $user_name = fullname($id);
                $final_message = str_replace("{USER}", $user_name, $duration);
                $final_message = str_replace("{MEETING_URL}", $share_url, $final_message);
                $final_message = str_replace("{SITE_NAME}", config_item('company_name'), $final_message);

                $data['message'] = $final_message;
                $email_body = $this->load->view('email_template', $data, TRUE);

                queue_email($user_email, $subject, $email_body, 'jitsi_meeting_invitation');
                $queued_count++;
            }
        }

        $client = json_decode($meeting_info->client_id);
        if (!empty($client) && is_array($client)) {
            foreach ($client as $client_id) {
                $clientInfo = get_row('tbl_client', ['client_id' => $client_id]);
                $recipient_email = client_contact_email($client_id) ?: $clientInfo->email;
                if (empty($recipient_email)) continue;

                $final_message = str_replace("{USER}", $clientInfo->name, $duration);
                $final_message = str_replace("{MEETING_URL}", $share_url, $final_message);
                $final_message = str_replace("{SITE_NAME}", config_item('company_name'), $final_message);

                $data['message'] = $final_message;
                $email_body = $this->load->view('email_template', $data, TRUE);

                queue_email($recipient_email, $subject, $email_body, 'jitsi_meeting_invitation');
                $queued_count++;
            }
        }

        $leads = json_decode($meeting_info->leads_id);
        if (!empty($leads) && is_array($leads)) {
            foreach ($leads as $leads_id) {
                $leadsInfo = get_row('tbl_leads', ['leads_id' => $leads_id]);
                if (empty($leadsInfo->email)) continue;

                $final_message = str_replace("{USER}", $leadsInfo->lead_name, $duration);
                $final_message = str_replace("{MEETING_URL}", $share_url, $final_message);
                $final_message = str_replace("{SITE_NAME}", config_item('company_name'), $final_message);

                $data['message'] = $final_message;
                $email_body = $this->load->view('email_template', $data, TRUE);

                queue_email($leadsInfo->email, $subject, $email_body, 'jitsi_meeting_invitation');
                $queued_count++;
            }
        }

        if (function_exists('process_email_queue')) {
            process_email_queue(10);
        }

        return $queued_count;
    }

    /**
     * Client portal: List meetings for the logged-in client
     */
    public function client_meetings()
    {
        if (!client()) {
            redirect('login');
        }

        $data['title'] = lang('jitsi');
        $data['meetings'] = $this->jitsi_model->get_client_meetings(client_id());
        $data['subview'] = $this->load->view('client_meetings', $data, TRUE);
        $this->load->view('client/_layout_main', $data);
    }

    /**
     * Client joins a meeting
     */
    public function client_join($jitsi_meeting_id)
    {
        $jitsi_meeting_id = url_decode($jitsi_meeting_id);
        $meeting_info = get_row('tbl_jitsi_meetings', ['jitsi_meeting_id' => $jitsi_meeting_id]);

        if (empty($meeting_info)) {
            set_message('error', lang('something_went_wrong'));
            redirect('client/dashboard');
        }

        if ($meeting_info->status == 'finished' || $meeting_info->status == 'canceled') {
            $msg = ($meeting_info->status == 'finished') ? lang('meeting_ended') : lang('canceled');
            set_message('error', $msg);
            redirect('client/dashboard');
        }

        $client_id = client_id();
        $invited_clients = json_decode($meeting_info->client_id, true);

        if (empty($invited_clients) || !in_array($client_id, $invited_clients)) {
            set_message('error', 'You do not have permission to join this meeting.');
            redirect('client/dashboard');
        }

        $clientInfo = get_row('tbl_client', ['client_id' => $client_id]);
        $user_email = client_contact_email($client_id) ?: $clientInfo->email ?: '';
        $user_name = $clientInfo->name ?: 'Client';

        $meeting_time = strtotime($meeting_info->meeting_time);
        $duration_minutes = (int) $meeting_info->duration;
        $exp = $meeting_time + ($duration_minutes * 60) + 3600;

        $meeting_url = build_jitsi_url($meeting_info->meeting_room, $user_email, $user_name, false, $exp);

        redirect($meeting_url);
    }

    /**
     * Internal helper: Generate JWT and build URL for staff participant join
     */
    private function participant_join($jitsi_meeting_id)
    {
        $meeting_info = get_row('tbl_jitsi_meetings', ['jitsi_meeting_id' => $jitsi_meeting_id]);

        $user_email = get_any_field('tbl_users', ['user_id' => my_id()], 'email');
        $user_name = fullname(my_id());

        $meeting_time = strtotime($meeting_info->meeting_time);
        $duration_minutes = (int) $meeting_info->duration;
        $exp = $meeting_time + ($duration_minutes * 60) + 3600;

        $meeting_url = build_jitsi_url($meeting_info->meeting_room, $user_email, $user_name, false, $exp);

        redirect($meeting_url);
    }

    /**
     * Public share link - allows anyone with the link to join the meeting
     */
    public function share($jitsi_meeting_id)
    {
        $jitsi_meeting_id = url_decode($jitsi_meeting_id);
        $meeting_info = get_row('tbl_jitsi_meetings', ['jitsi_meeting_id' => $jitsi_meeting_id]);

        if (empty($meeting_info)) {
            show_error('Meeting not found.', 404);
        }

        // Check if meeting status is finished or canceled
        if ($meeting_info->status == 'finished' || $meeting_info->status == 'canceled') {
            show_error('This meeting has already ended or is canceled.', 403);
        }

        // If the user is logged in (staff or client), bypass name entry and let them join directly
        if (!empty(my_id())) {
            $user_email = get_any_field('tbl_users', ['user_id' => my_id()], 'email');
            $user_name = fullname(my_id());
            
            $meeting_time = strtotime($meeting_info->meeting_time);
            $duration_minutes = (int) $meeting_info->duration;
            $exp = $meeting_time + ($duration_minutes * 60) + 3600;
            
            $meeting_url = build_jitsi_url($meeting_info->meeting_room, $user_email, $user_name, false, $exp);
            redirect($meeting_url);
        }

        // If a guest name was submitted via POST
        if ($this->input->post('guest_name')) {
            $guest_name = strip_tags($this->input->post('guest_name', true));
            if (!empty($guest_name)) {
                $user_email = 'guest_' . time() . '@example.com';
                $meeting_time = strtotime($meeting_info->meeting_time);
                $duration_minutes = (int) $meeting_info->duration;
                $exp = $meeting_time + ($duration_minutes * 60) + 3600;
                
                $meeting_url = build_jitsi_url($meeting_info->meeting_room, $user_email, $guest_name, false, $exp);
                redirect($meeting_url);
            }
        }

        // Otherwise, show a beautiful guest login view
        $data['title'] = $meeting_info->topic;
        $data['meeting_info'] = $meeting_info;
        
        $this->load->view('guest_join', $data);
    }

    /**
     * Send invitations AJAX endpoint
     */
    public function send_invitations_ajax($meeting_id)
    {
        if (!$this->input->is_ajax_request()) {
            show_error('No direct script access allowed', 403);
        }
        
        $this->send_meeting_invitation($meeting_id);
        
        echo json_encode(['status' => 'success', 'message' => 'Invitations sent successfully']);
        exit();
    }

    /**
     * Send start notifications AJAX endpoint
     */
    public function send_start_notifications_ajax($meeting_id)
    {
        if (!$this->input->is_ajax_request()) {
            show_error('No direct script access allowed', 403);
        }
        
        $this->send_notify_assign_user($meeting_id);
        
        echo json_encode(['status' => 'success', 'message' => 'Start notifications sent successfully']);
        exit();
    }
}
