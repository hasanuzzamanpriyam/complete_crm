<?php

class Job_Circular extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('job_circular_model');
        $this->load->model('recruitment_model');
    }

    public function jobs_posted()
    {
        $data['title'] = lang('job_posted_list');
        $data['subview'] = $this->load->view('admin/job_circular/jobs_posted', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function jobs_postedList()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('datatables');
            $this->datatables->table = 'tbl_job_circular';
            $this->datatables->join_table = array('tbl_designations');
            $this->datatables->join_where = array('tbl_designations.designations_id=tbl_job_circular.designations_id');
            $this->datatables->column_order = array('job_title', 'tbl_designations.designations', 'employment_type', 'vacancy_no', 'experience', 'age', 'salary_range', 'posted_date', 'description', 'last_date', 'status');
            $this->datatables->column_search = array('job_title', 'tbl_designations.designations', 'employment_type', 'vacancy_no', 'experience', 'age', 'salary_range', 'posted_date', 'description', 'last_date', 'status');
            $this->datatables->order = array('job_circular_id' => 'desc');

            $fetch_data = $this->datatables->get_datatable_permission();

            $edited = can_action('103', 'edited');
            $deleted = can_action('103', 'deleted');
            $data = array();
            foreach ($fetch_data as $_key => $v_job_post) {
                if (!empty($v_job_post->designations_id)) {
                    $design_info = $this->db->where('designations_id', $v_job_post->designations_id)->get('tbl_designations')->row();
                    if (!empty($design_info)) {
                        $designation = $design_info->designations;
                    } else {
                        $designation = '-';
                    }
                } else {
                    $designation = '-';
                }
                $can_edit = $this->job_circular_model->can_action('tbl_job_circular', 'edit', array('job_circular_id' => $v_job_post->job_circular_id));
                $can_delete = $this->job_circular_model->can_action('tbl_job_circular', 'delete', array('job_circular_id' => $v_job_post->job_circular_id));


                $action = null;
                $sub_array = array();
                $title = null;
                $title .= '<a data-toggle="modal" data-target="#myModal_lg" class="text-info" href="' . base_url() . 'admin/job_circular/view_circular_details/' . $v_job_post->job_circular_id . '">' . $v_job_post->job_title . '</a>';
                $sub_array[] = $title;

                $sub_array[] = '<span class="tags">' . $designation . '</span>';
                $sub_array[] = $v_job_post->vacancy_no;
                $sub_array[] = display_date($v_job_post->last_date);

                $custom_form_table = custom_form_table(14, $v_job_post->job_circular_id);

                if (!empty($custom_form_table)) {
                    foreach ($custom_form_table as $c_label => $v_fields) {
                        $sub_array[] = $v_fields;
                    }
                }
                if ($v_job_post->status == 'unpublished') {
                    $status = '<span class="label label-danger">' . lang('unpublished') . '</span>';
                } else {
                    $status = '<span class="label label-success">' . lang('published') . '</span>';
                }
                $sub_array[] = $status;

                $action .= '<a href="' . base_url() . 'admin/job_circular/jobs_applications/' . $v_job_post->job_circular_id . '" title="' . lang('all_application_for') . '" class="btn btn-purple btn-xs" data-toggle="tooltip"><span
                            class="fa fa-list-alt"></span></a>' . ' ';
                
                $app_count = $this->db->where('job_circular_id', $v_job_post->job_circular_id)->count_all_results('tbl_job_appliactions');
                $action .= '<a href="' . base_url() . 'admin/job_circular/ats_applications/' . $v_job_post->job_circular_id . '" title="' . lang('ats_applications') . '" class="btn btn-info btn-xs" data-toggle="tooltip"><span
                            class="fa fa-bar-chart"></span> ' . ($app_count > 0 ? '<span class="badge" style="background:#fff;color:#31708f;padding:1px 5px;font-size:10px;">' . $app_count . '</span>' : '') . '</a>' . ' ';
                $action .= '<a href="javascript:void(0)" onclick="openJobSkillsModal(' . $v_job_post->job_circular_id . ')" title="' . lang('manage_job_skills') . '" class="btn btn-warning btn-xs" data-toggle="tooltip"><span
                            class="fa fa-tags"></span></a>' . ' ';
                if (!empty($can_edit) && !empty($edited)) {
                    if ($v_job_post->status == 'unpublished') {
                        $action .= btn_publish('admin/job_circular/change_status/published/' . $v_job_post->job_circular_id) . ' ';
                    } else {
                        $action .= btn_unpublish('admin/job_circular/change_status/unpublished/' . $v_job_post->job_circular_id) . ' ';
                    }
                    $action .= '<span data-toggle="tooltip" data-placement="top" title="' . lang('edit') . '"><a href="' . base_url() . 'admin/job_circular/new_jobs_posted/' . $v_job_post->job_circular_id . '"
                       class="btn btn-primary btn-xs" data-toggle="modal" data-placement="top" data-target="#myModal_lg"><i class="fa fa-pencil-square-o"></i></a></span>' . ' ';
                }
                if (!empty($can_delete) && !empty($deleted)) {
                    $action .= ajax_anchor(base_url("admin/job_circular/delete_jobs_posted/" . $v_job_post->job_circular_id), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_" . $_key)) . ' ';
                }
                $action .= '<a target="_blank" href="' . base_url() . 'frontend/circular_details/' . $v_job_post->job_circular_id . '"
                       title="' . lang('view_circular_details') . ' & ' . lang('apply_now') . '" class="btn btn-primary btn-xs" data-placement="top" data-toggle="tooltip"><i class="fa fa-paper-plane"></i></a>' . ' ';

                $sub_array[] = $action;
                $data[] = $sub_array;
            }

            render_table($data);
        } else {
            redirect('admin/dashboard');
        }
    }

    public function new_jobs_posted($id = null)
    {
        $data['title'] = lang('new') . ' ' . lang('jobs_posted');
        if (!empty($id)) {
            $can_edit = $this->job_circular_model->can_action('tbl_job_circular', 'edit', array('job_circular_id' => $id));
            if (!empty($can_edit)) {
                $data['job_posted'] = $this->db->where('job_circular_id', $id)->get('tbl_job_circular')->row();

                if (empty($data['job_posted'])) {
                    // messages for user
                    $type = "error";
                    $message = "Not Found!";
                    set_message($type, $message);
                    redirect('admin/job_circular/jobs_posted');
                }
            }
        }
        // get all department info and designation info
        $data['all_dept_info'] = $this->db->get('tbl_departments')->result();
        // get all department info and designation info
        foreach ($data['all_dept_info'] as $v_dept_info) {
            $data['all_department_info'][] = $this->job_circular_model->get_add_department_by_id($v_dept_info->departments_id);
        }

        $data['assign_user'] = $this->job_circular_model->allowed_user('103');

        // Load all active skills for skill selection
        $data['all_skills'] = $this->db->where('status', 'active')->order_by('skill_category', 'ASC')->order_by('skill_name', 'ASC')->get('tbl_recruitment_skills')->result();

        $data['subview'] = $this->load->view('admin/job_circular/new_jobs_posted', $data, FALSE);
        $this->load->view('admin/_layout_modal_lg', $data); //page load
    }

    public function save_job_posted($id = NULL)
    {
        $created = can_action('103', 'created');
        $edited = can_action('103', 'edited');
        if (!empty($created) || !empty($edited) && !empty($id)) {
            $data = $this->job_circular_model->array_from_post(array('job_title', 'designations_id', 'employment_type', 'vacancy_no', 'experience', 'age', 'salary_range', 'posted_date', 'description', 'last_date', 'status'));
            $designation_info = $this->db->where('designations_id', $data['designations_id'])->get('tbl_designations')->row();
            $permission = $this->input->post('permission', true);
            // update root category
            $where = array('designations_id' => $data['designations_id']);
            // duplicate value check in DB
            if (!empty($id)) { // if id exist in db update data
                $job_circular_id = array('job_circular_id !=' => $id);
            } else { // if id is not exist then set id as null
                $job_circular_id = null;
            }
            if (!empty($permission)) {
                if ($permission == 'everyone') {
                    $assigned = 'all';
                } else {
                    $assigned_to = $this->job_circular_model->array_from_post(array('assigned_to'));
                    if (!empty($assigned_to['assigned_to'])) {
                        foreach ($assigned_to['assigned_to'] as $assign_user) {
                            $assigned[$assign_user] = $this->input->post('action_' . $assign_user, true);
                        }
                    }
                }
                if (!empty($assigned)) {
                    if ($assigned != 'all') {
                        $assigned = json_encode($assigned);
                    }
                } else {
                    $assigned = 'all';
                }
                $data['permission'] = $assigned;
            } else {
                set_message('error', lang('assigned_to') . ' Field is required');
                if (empty($_SERVER['HTTP_REFERER'])) {
                    redirect('admin/job_circular/jobs_posted');
                } else {
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }

            $this->job_circular_model->_table_name = "tbl_job_circular"; // table name
            $this->job_circular_model->_primary_key = "job_circular_id"; // $id
            $return_id = $this->job_circular_model->save($data, $id);

            // Save job skills
            $mandatory_skills = $this->input->post('mandatory_skills', true);
            $preferred_skills = $this->input->post('preferred_skills', true);

            // Delete existing skills for this job
            $this->db->where('job_circular_id', $return_id);
            $this->db->delete('tbl_job_skills');

            // Insert mandatory skills
            if (!empty($mandatory_skills)) {
                foreach ($mandatory_skills as $skill_id) {
                    $this->db->insert('tbl_job_skills', [
                        'job_circular_id' => $return_id,
                        'skill_id' => $skill_id,
                        'is_mandatory' => 1
                    ]);
                }
            }

            // Insert preferred skills
            if (!empty($preferred_skills)) {
                foreach ($preferred_skills as $skill_id) {
                    $this->db->insert('tbl_job_skills', [
                        'job_circular_id' => $return_id,
                        'skill_id' => $skill_id,
                        'is_mandatory' => 0
                    ]);
                }
            }

            if (!empty($id)) {
                $activity = 'activity_update_job_posted';
                $msg = lang('job_posted_information_update');
            } else {
                $activity = 'activity_added_job_posted';
                $msg = lang('job_posted_information_saved');
                $id = $return_id;
            }
            save_custom_field(14, $id);

            // save into activities
            $activities = array(
                'user' => $this->session->userdata('user_id'),
                'module' => 'job_circular',
                'module_field_id' => $id,
                'activity' => $activity,
                'icon' => 'fa-ticket',
                'value1' => $data['job_title'] . '[' . $designation_info->designations . ']',
            );

            // Update into tbl_project
            $this->job_circular_model->_table_name = "tbl_activities"; //table name
            $this->job_circular_model->_primary_key = "activities_id";
            $this->job_circular_model->save($activities);
            // messages for user
            $type = "success";
            $message = $msg;
            set_message($type, $message);
        }
        redirect('admin/job_circular/jobs_posted');
    }

    public function delete_jobs_posted($id)
    {
        $deleted = can_action('103', 'deleted');
        if (!empty($deleted)) {
            // check into tbl_job_allocations
            // if id exist delete this
            $check_existing_data = $this->job_circular_model->check_by(array('job_circular_id' => $id), 'tbl_job_appliactions');
            $job_posted_info = $this->job_circular_model->check_by(array('job_circular_id' => $id), 'tbl_job_circular');

            if (empty($check_existing_data)) {
                // save into activities
                $activities = array(
                    'user' => $this->session->userdata('user_id'),
                    'module' => 'job_circular',
                    'module_field_id' => $id,
                    'activity' => 'activity_delete_job_posted',
                    'icon' => 'fa-ticket',
                    'value1' => $job_posted_info->job_title,
                );
                // Update into tbl_project
                $this->job_circular_model->_table_name = "tbl_activities"; //table name
                $this->job_circular_model->_primary_key = "activities_id";
                $this->job_circular_model->save($activities);

                // delete into tbl_job_circular
                $this->job_circular_model->_table_name = "tbl_job_circular"; // table name
                $this->job_circular_model->_primary_key = "job_circular_id"; // $id
                $this->job_circular_model->delete($id);
                // messages for user
                $type = "success";
                $message = lang('job_posted_information_delete');
            } else {
                $type = "error";
                $message = lang('job_posted_information_used');
            }
            set_message($type, $message);
        }
        redirect('admin/job_circular/jobs_posted');
    }

    public
    function change_status($status, $id)
    {
        $edited = can_action('103', 'edited');
        if (!empty($edited)) {
            // if flag == 1 that means it is published to un pubslished
            // else unpublished to pubslished
            $this->job_circular_model->set_action(array('job_circular_id' => $id), array('status' => $status), 'tbl_job_circular');

            $type = "success";
            $message = lang('job_posted_status_change') . ' ' . $status . ' !';
            set_message($type, $message);
        }
        redirect('admin/job_circular/jobs_posted');
    }

    public
    function view_circular_details($id)
    {
        $data['title'] = lang('view_circular_details');
        $data['job_posted'] = $this->db->where('job_circular_id', $id)->get('tbl_job_circular')->row();
        $data['subview'] = $this->load->view('admin/job_circular/circular_details', $data, FALSE);
        $this->load->view('admin/_layout_modal_lg', $data); //page load
    }

    public
    function jobs_posted_pdf($id)
    {
        $data['job_posted'] = $this->db->where('job_circular_id', $id)->get('tbl_job_circular')->row();

        $this->load->helper('dompdf');
        $view_file = $this->load->view('admin/job_circular/jobs_posted_pdf', $data, true);
        pdf_create($view_file, slug_it(lang('jobs_posted') . '- ' . $data['job_posted']->job_title));
    }

    public
    function jobs_applications($id = null)
    {
        $data['title'] = lang('all_jobs_application');
        // get salary template details
        $data['job_appliactions_id'] = $id;
        $data['subview'] = $this->load->view('admin/job_circular/jobs_applications', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function jobs_applicationsList($id = null)
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('datatables');
            $this->datatables->table = 'tbl_job_appliactions';
            $this->datatables->join_table = array('tbl_job_circular');
            $this->datatables->join_where = array('tbl_job_circular.job_circular_id=tbl_job_appliactions.job_circular_id');
            $this->datatables->select = array('tbl_job_appliactions.*', 'tbl_job_circular.job_title', 'tbl_job_circular.designations_id', 'tbl_job_circular.employment_type');
            $this->datatables->column_order = array('tbl_job_circular.job_title', 'tbl_job_appliactions.name', 'tbl_job_appliactions.email', 'tbl_job_appliactions.mobile', 'tbl_job_appliactions.ats_score', 'tbl_job_appliactions.application_status', 'tbl_job_appliactions.apply_date', 'tbl_job_appliactions.interview_date');
            $this->datatables->column_search = array('tbl_job_circular.job_title', 'tbl_job_appliactions.name', 'tbl_job_appliactions.email', 'tbl_job_appliactions.mobile', 'tbl_job_appliactions.application_status', 'tbl_job_appliactions.apply_date', 'tbl_job_appliactions.interview_date');
            $this->datatables->order = array('tbl_job_appliactions.ats_score' => 'desc', 'tbl_job_appliactions.apply_date' => 'desc');
            $where = null;
            if (!empty($id)) {
                $where = array('tbl_job_appliactions.job_circular_id' => $id);
            }
            $fetch_data = make_datatables($where);
            $edited = can_action('102', 'edited');
            $deleted = can_action('102', 'deleted');
            $data = array();
            $this->load->library('ats_parser');
            foreach ($fetch_data as $_key => $v_job_application) {

                $action = null;
                $sub_array = array();
                $title = null;
                $title .= '<a data-toggle="modal" data-target="#myModal_lg" class="text-info" href="' . base_url() . 'admin/job_circular/view_circular_details/' . $v_job_application->job_circular_id . '">' . $v_job_application->job_title . '</a>';
                $sub_array[] = $title;

                $sub_array[] = '<span class="tags">' . $v_job_application->name . '</span>';
                $sub_array[] = $v_job_application->email;
                $sub_array[] = $v_job_application->mobile;

                // ATS Score column
                $ats_score = isset($v_job_application->ats_score) ? (float) $v_job_application->ats_score : 0;
                $sub_array[] = $this->ats_parser->get_score_badge($ats_score);

                $sub_array[] = display_date($v_job_application->apply_date);

                if ($v_job_application->application_status == 0) {
                    $status = '<span class="label label-warning">' . lang('unread') . '</span>';
                } elseif ($v_job_application->application_status == 1) {
                    $status = '<span class="label label-success">' . lang('approved') . '</span>';
                } elseif ($v_job_application->application_status == 2) {
                    $status = '<span class="label label-primary">' . lang('primary_selected') . '</span>';
                } elseif ($v_job_application->application_status == 3) {
                    $status = '<span class="label label-purple">' . lang('call_for_interview') . '</span>';
                } else {
                    $status = '<span class="label label-danger">' . lang('rejected') . '</span>';
                }
                $sub_array[] = $status;

                $action .= '<a href="' . base_url() . 'admin/job_circular/download_resume/' . $v_job_application->job_appliactions_id . '" title="' . lang('download') . ' ' . lang('resume') . '" class="btn btn-purple btn-xs" data-toggle="tooltip"><span
                            class="fa fa-download"></span></a>' . ' ';
                $action .= '<a href="' . base_url() . 'admin/job_circular/ats_score_detail/' . $v_job_application->job_appliactions_id . '" title="' . lang('ats_score_detail') . '" class="btn btn-info btn-xs" data-toggle="modal" data-target="#myModal"><span
                            class="fa fa-bar-chart"></span></a>' . ' ';
                $action .= '<a href="' . base_url() . 'admin/job_circular/schedule_interview/' . $v_job_application->job_appliactions_id . '" title="' . lang('schedule_interview') . '" class="btn btn-success btn-xs" data-toggle="modal" data-target="#myModal_lg"><span
                            class="fa fa-calendar"></span></a>' . ' ';
                $action .= '<a href="' . base_url() . 'admin/job_circular/create_offer/' . $v_job_application->job_appliactions_id . '" title="' . lang('create_offer') . '" class="btn btn-warning btn-xs" data-toggle="modal" data-target="#myModal_lg"><span
                            class="fa fa-file-text-o"></span></a>' . ' ';
                if (!empty($edited)) {
                    $action .= '<a href="' . base_url() . 'admin/job_circular/change_application_status/' . $v_job_application->job_appliactions_id . '" title="' . lang('change_status') . '" class="btn btn-success btn-xs" data-toggle="modal" data-target="#myModal"><span
                            class="fa fa-pencil-square-o"></span> ' . lang('status') . '</a>' . ' ';
                }
                $action .= '<a href="' . base_url() . 'admin/job_circular/jobs_application_details/' . $v_job_application->job_appliactions_id . '" title="' . lang('view') . '" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#myModal"><span
                            class="fa fa-list-alt"></span></a>' . ' ';
                if (!empty($deleted)) {
                    $action .= ajax_anchor(base_url("admin/job_circular/delete_jobs_application/" . $v_job_application->job_appliactions_id), "<i class='btn btn-xs btn-danger fa fa-trash-o'></i>", array("class" => "", "title" => lang('delete'), "data-fade-out-on-success" => "#table_" . $_key)) . ' ';
                }
                $sub_array[] = $action;
                $data[] = $sub_array;
            }

            render_table($data, $where);
        } else {
            redirect('admin/dashboard');
        }
    }

    public
    function change_application_status($id)
    {
        $flag = $this->input->post('flag', true);
        if (empty($flag)) {
            $data['title'] = lang('change_status');
            // get salary template deatails
            $data['job_application_info'] = $this->db->where('job_appliactions_id', $id)->get('tbl_job_appliactions')->row();

            $data['subview'] = $this->load->view('admin/job_circular/application_status', $data, FALSE);
            $this->load->view('admin/_layout_modal', $data);
        } else {
            // if flag == 1 that means it is published to un pubslished
            // else unpublished to pubslished
            $status = $this->input->post('status', true);
            $where = array('application_status' => $status);
            if ($status == 3) {
                $send_email = $this->input->post('send_email', true);
                $interview_date = $this->input->post('interview_date', true);
                $interview_note = $this->input->post('interview_note', true);
                if (!empty($send_email)) {
                    $where = array('application_status' => $status, 'send_email' => $send_email, 'interview_note' => $interview_note, 'interview_date' => $interview_date);
                    $this->call_for_interview($id, $where);
                }
            }
            $this->job_circular_model->set_action(array('job_appliactions_id' => $id), $where, 'tbl_job_appliactions');
            // messages for user
            $type = "success";
            $message = lang('job_posted_status_change');
            set_message($type, $message);
            redirect('admin/job_circular/jobs_applications');
        }
    }

    function call_for_interview($id, $data)
    {
        $job_application_info = $this->db->where('job_appliactions_id', $id)->get('tbl_job_appliactions')->row();
        if (!empty($job_application_info)) {
            $job_circular_info = $this->db->where('job_circular_id', $job_application_info->job_circular_id)->get('tbl_job_circular')->row();
            $designation = '-';
            if (!empty($job_circular_info->designations_id)) {
                $design_info = $this->db->where('designations_id', $job_circular_info->designations_id)->get('tbl_designations')->row();
                if (!empty($design_info)) {
                    $designation = $design_info->designations;
                }
            }

            $email_template = email_templates(array('email_group' => 'call_for_interview'));

            $message = $email_template->template_body;
            $subject = $email_template->subject;
            $title = str_replace("{NAME}", $job_application_info->name, $message);
            $job_title = str_replace("{JOB_TITLE}", $job_circular_info->job_title, $title);
            $designation = str_replace("{DESIGNATION}", $designation, $job_title);
            $date = str_replace("{DATE}", strftime(config_item('date_format'), strtotime($data['interview_date'])), $designation);
            $interview_note = str_replace("{NOTE}", $data['interview_note'], $date);
            $Link = str_replace("{LINK}", base_url() . 'frontend/circular_details/' . $job_application_info->job_circular_id, $interview_note);
            $message = str_replace("{SITE_NAME}", config_item('company_name'), $Link);
            $data['message'] = $message;
            $message = $this->load->view('email_template', $data, TRUE);

            $params['subject'] = $subject;
            $params['message'] = $message;
            $params['resourceed_file'] = '';

            $params['recipient'] = $job_application_info->email;
            $this->job_circular_model->send_email($params);
        }
        return true;
    }

    public
    function jobs_application_details($id)
    {
        $data['title'] = lang('jobs_application_details');
        // get salary template deatails
        $data['job_application_info'] = $this->job_circular_model->get_job_application_info($id);
        $data['subview'] = $this->load->view('admin/job_circular/jobs_applications_details', $data, FALSE);
        $this->load->view('admin/_layout_modal', $data);
    }

    public
    function view_jobs_application($id)
    {
        $data['title'] = lang('jobs_application_details');
        // get salary template deatails
        $data['job_application_info'] = $this->job_circular_model->get_job_application_info($id);
        $data['subview'] = $this->load->view('admin/job_circular/jobs_applications_details', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public
    function delete_jobs_application($id)
    {
        $jobs_application = $this->job_circular_model->get_job_application_info($id);
        // save into activities
        $activities = array(
            'user' => $this->session->userdata('user_id'),
            'module' => 'job_circular',
            'module_field_id' => $id,
            'activity' => 'activity_delete_job_application',
            'icon' => 'fa-ticket',
            'value1' => $jobs_application->name,
        );
        // Update into tbl_project
        $this->job_circular_model->_table_name = "tbl_activities"; //table name
        $this->job_circular_model->_primary_key = "activities_id";
        $this->job_circular_model->save($activities);

        $this->job_circular_model->_table_name = "tbl_job_appliactions"; // table name
        $this->job_circular_model->_primary_key = "job_appliactions_id"; // $id
        $this->job_circular_model->delete($id);

        // messages for user
        $type = "success";
        $message = lang('deleted_job_application');
        set_message($type, $message);
        redirect('admin/job_circular/jobs_applications');
    }

    public
    function download_resume($id)
    {
        $job_application_info = $this->job_circular_model->get_job_application_info($id);
        if (empty($job_application_info) || empty($job_application_info->resume) || !file_exists($job_application_info->resume)) {
            set_message('error', lang('resume_not_found'));
            redirect($_SERVER['HTTP_REFERER'] ?? 'admin/job_circular/jobs_applications');
        }
        $path = file_get_contents($job_application_info->resume);
        $resume = explode('/', $job_application_info->resume);
        $this->load->helper('download');
        force_download($job_application_info->name . ' - ' . $resume[1], $path);
    }

    // ==================== SKILLS MANAGEMENT ====================

    public function manage_skills()
    {
        $data['title'] = lang('skills_management');
        $data['all_skills'] = $this->recruitment_model->get_all_skills();
        $data['categories'] = $this->recruitment_model->get_skill_categories();
        $data['subview'] = $this->load->view('admin/job_circular/manage_skills', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function save_skill($id = null)
    {
        $data = $this->input->post(null, true);
        $skill_data = [
            'skill_name' => trim($data['skill_name']),
            'skill_category' => !empty($data['skill_category']) ? trim($data['skill_category']) : null,
            'status' => !empty($data['status']) ? 'active' : 'inactive'
        ];

        $saved_id = $this->recruitment_model->save_skill($skill_data, $id);

        if ($this->input->is_ajax_request()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'id' => $saved_id]);
            exit;
        }
        set_message('success', lang('skill_saved'));
        redirect('admin/job_circular/manage_skills');
    }

    public function delete_skill($id)
    {
        $this->recruitment_model->delete_skill($id);
        if ($this->input->is_ajax_request()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
        set_message('success', lang('skill_deleted'));
        redirect('admin/job_circular/manage_skills');
    }

    public function get_skills_ajax()
    {
        $category = $this->input->get('category');
        $skills = $this->recruitment_model->get_all_skills('active');

        if ($category) {
            $skills = array_filter($skills, function($s) use ($category) {
                return $s->skill_category == $category;
            });
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'skills' => $skills]);
        exit;
    }

    // ==================== JOB SKILLS ASSIGNMENT ====================

    public function save_job_skills($job_circular_id)
    {
        if (!$this->input->is_ajax_request()) {
            redirect('admin/job_circular/jobs_posted');
        }

        $post = $this->input->post(null, true);
        $skills = [];

        if (!empty($post['skills'])) {
            foreach ($post['skills'] as $skill_id) {
                $skills[] = [
                    'skill_id' => $skill_id,
                    'is_mandatory' => !empty($post['mandatory_' . $skill_id]) ? 1 : 0
                ];
            }
        }

        $this->recruitment_model->save_job_skills($job_circular_id, $skills);

        // Recalculate ATS scores for existing applications
        $this->recruitment_model->recalculate_all_ats_scores($job_circular_id);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => lang('job_skills_saved')]);
        exit;
    }

    public function get_job_skills_ajax($job_circular_id)
    {
        $skills = $this->recruitment_model->get_job_skills($job_circular_id);
        $all_skills = $this->recruitment_model->get_all_skills('active');

        $assigned_ids = array_map(function($s) { return $s->skill_id; }, $skills);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'assigned' => $skills,
            'all_skills' => $all_skills,
            'assigned_ids' => $assigned_ids
        ]);
        exit;
    }

    public function get_applications_ajax($job_circular_id = null)
    {
        $this->db->select('job_appliactions_id, name, email, ats_score, job_circular_id');
        $this->db->from('tbl_job_appliactions');
        if ($job_circular_id) {
            $this->db->where('job_circular_id', $job_circular_id);
        }
        $this->db->order_by('ats_score', 'DESC');
        $applications = $this->db->get()->result();

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'applications' => $applications]);
        exit;
    }

    // ==================== ATS APPLICATIONS DASHBOARD ====================

    public function ats_applications($job_circular_id = null)
    {
        $data['title'] = lang('ats_applications');
        $data['job_circular_id'] = $job_circular_id;
        $data['applications'] = $this->recruitment_model->get_applications_with_ats($job_circular_id);
        $data['job_skills'] = $job_circular_id ? $this->recruitment_model->get_job_skills($job_circular_id) : [];
        $data['job_circulars'] = $this->db->where('status', 'published')->get('tbl_job_circular')->result();
        $data['subview'] = $this->load->view('admin/job_circular/ats_applications', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function reparse_resume($job_appliactions_id)
    {
        if (!$this->input->is_ajax_request()) {
            redirect('admin/job_circular/jobs_applications');
        }

        $app = $this->recruitment_model->get_application_detail($job_appliactions_id);
        if (empty($app) || empty($app->resume)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => lang('resume_not_found')]);
            exit;
        }

        $resume_text = $this->ats_parser->extract_text($app->resume);
        if (empty($resume_text)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => lang('resume_parse_failed')]);
            exit;
        }

        $ats_data = $this->recruitment_model->calculate_ats_score($app->job_circular_id, $resume_text);
        $this->recruitment_model->update_application_ats($job_appliactions_id, $ats_data);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'ats_score' => $ats_data['ats_score'],
            'matched_skills' => $ats_data['matched_skills'],
            'missing_skills' => $ats_data['missing_skills'],
            'badge' => $this->ats_parser->get_score_badge($ats_data['ats_score'])
        ]);
        exit;
    }

    public function ats_score_detail($job_appliactions_id)
    {
        $data['title'] = lang('ats_score_detail');
        $data['application'] = $this->recruitment_model->get_application_detail($job_appliactions_id);
        $data['job_skills'] = $this->recruitment_model->get_job_skills($data['application']->job_circular_id);

        if (!empty($data['application']->matched_skills)) {
            $data['matched_skills'] = json_decode($data['application']->matched_skills, true);
            if (!is_array($data['matched_skills'])) {
                $data['matched_skills'] = [];
            }
        } else {
            $data['matched_skills'] = [];
        }
        if (!empty($data['application']->missing_skills)) {
            $data['missing_skills'] = json_decode($data['application']->missing_skills, true);
            if (!is_array($data['missing_skills'])) {
                $data['missing_skills'] = [];
            }
        } else {
            $data['missing_skills'] = [];
        }
        if (!empty($data['application']->skill_match_details)) {
            $data['skill_match_details'] = json_decode($data['application']->skill_match_details, true);
            if (!is_array($data['skill_match_details'])) {
                $data['skill_match_details'] = [];
            }
        } else {
            $data['skill_match_details'] = [];
        }

        $data['subview'] = $this->load->view('admin/job_circular/ats_score_detail', $data, FALSE);
        $this->load->view('admin/_layout_modal', $data);
    }

    // ==================== INTERVIEW MANAGEMENT ====================

    public function manage_interviews()
    {
        $data['title'] = lang('interview_schedule');
        $filters = [
            'status' => $this->input->get('status') ?: null,
            'interview_type' => $this->input->get('interview_type') ?: null,
            'date_from' => $this->input->get('date_from') ?: null,
            'date_to' => $this->input->get('date_to') ?: null,
        ];
        $data['interviews'] = $this->recruitment_model->get_interviews($filters);
        $data['job_circulars'] = $this->db->get('tbl_job_circular')->result();
        $data['subview'] = $this->load->view('admin/job_circular/manage_interviews', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function schedule_interview($job_appliactions_id = null)
    {
        $data['title'] = lang('schedule_interview');
        if ($job_appliactions_id) {
            $data['application'] = $this->recruitment_model->get_application_detail($job_appliactions_id);
        }
        $data['job_circulars'] = $this->db->where('status', 'published')->get('tbl_job_circular')->result();
        $data['subview'] = $this->load->view('admin/job_circular/schedule_interview', $data, FALSE);
        $this->load->view('admin/_layout_modal_lg', $data);
    }

    public function save_interview($id = null)
    {
        $data = $this->input->post(null, true);

        $interview_data = [
            'job_appliactions_id' => $data['job_appliactions_id'],
            'job_circular_id' => $data['job_circular_id'],
            'interview_type' => $data['interview_type'],
            'interview_date' => $data['interview_date'],
            'interview_time' => $data['interview_time'],
            'interviewer_name' => $data['interviewer_name'],
            'interviewer_email' => $data['interviewer_email'],
            'meeting_link' => !empty($data['meeting_link']) ? $data['meeting_link'] : null,
            'location_details' => !empty($data['location_details']) ? $data['location_details'] : null,
            'interview_notes' => !empty($data['interview_notes']) ? $data['interview_notes'] : null,
            'created_by' => $this->session->userdata('user_id')
        ];

        $interview_id = $this->recruitment_model->save_interview($interview_data, $id);

        // Send email if requested
        if (!empty($data['send_email'])) {
            $this->send_interview_email($interview_id);
        }

        if ($this->input->is_ajax_request()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'interview_id' => $interview_id]);
            exit;
        }

        set_message('success', lang('interview_saved'));
        redirect('admin/job_circular/manage_interviews');
    }

    public function send_interview_email($interview_id)
    {
        $interview = $this->recruitment_model->get_interview_by_id($interview_id);
        if (empty($interview)) {
            log_message('error', "Interview not found for ID {$interview_id}");
            return false;
        }

        $email_template = email_templates(['email_group' => 'interview_invitation']);
        if (empty($email_template)) {
            log_message('error', "Interview email template not found for interview ID {$interview_id}");
            return false;
        }

        $message = $email_template->template_body;
        $subject = $email_template->subject;

        $type_label = ucfirst(str_replace('_', ' ', $interview->interview_type));
        $meeting_or_location = '';
        if ($interview->interview_type == 'online' && !empty($interview->meeting_link)) {
            $meeting_or_location = '<li><strong>Meeting Link:</strong> <a href="' . $interview->meeting_link . '">' . $interview->meeting_link . '</a></li>';
        } elseif ($interview->interview_type == 'face_to_face' && !empty($interview->location_details)) {
            $meeting_or_location = '<li><strong>Location:</strong> ' . $interview->location_details . '</li>';
        }

        $notes_html = !empty($interview->interview_notes) ? '<p><strong>Additional Notes:</strong> ' . $interview->interview_notes . '</p>' : '';

        $replacements = [
            '{CANDIDATE_NAME}' => $interview->candidate_name,
            '{JOB_TITLE}' => $interview->job_title,
            '{COMPANY_NAME}' => config_item('company_name'),
            '{INTERVIEW_DATE}' => strftime(config_item('date_format'), strtotime($interview->interview_date)),
            '{INTERVIEW_TIME}' => date('g:i A', strtotime($interview->interview_time)),
            '{INTERVIEW_TYPE}' => $type_label,
            '{MEETING_LINK_OR_LOCATION}' => $meeting_or_location,
            '{INTERVIEWER_NAME}' => $interview->interviewer_name,
            '{INTERVIEW_NOTES}' => $notes_html,
            '{SITE_NAME}' => config_item('company_name')
        ];

        foreach ($replacements as $key => $val) {
            $message = str_replace($key, $val, $message);
            $subject = str_replace($key, $val, $subject);
        }

        $params = [
            'recipient' => $interview->candidate_email,
            'subject' => $subject,
            'message' => $message,
            'resourceed_file' => ''
        ];

        $sent = $this->recruitment_model->send_email($params);
        if ($sent) {
            $this->recruitment_model->mark_interview_email_sent($interview_id);
            log_message('info', "Interview email sent successfully to {$interview->candidate_email} for interview ID {$interview_id}");
        } else {
            log_message('error', "Failed to send interview email to {$interview->candidate_email} for interview ID {$interview_id}");
        }
        return $sent;
    }

    public function resend_interview_email($interview_id)
    {
        $interview = $this->recruitment_model->get_interview_by_id($interview_id);
        if (empty($interview)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Interview not found']);
            exit;
        }

        $email_template = email_templates(['email_group' => 'interview_invitation']);
        if (empty($email_template)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Email template not found']);
            exit;
        }

        $message = $email_template->template_body;
        $subject = $email_template->subject;

        $type_label = ucfirst(str_replace('_', ' ', $interview->interview_type));
        $meeting_or_location = '';
        if ($interview->interview_type == 'online' && !empty($interview->meeting_link)) {
            $meeting_or_location = '<li><strong>Meeting Link:</strong> <a href="' . $interview->meeting_link . '">' . $interview->meeting_link . '</a></li>';
        } elseif ($interview->interview_type == 'face_to_face' && !empty($interview->location_details)) {
            $meeting_or_location = '<li><strong>Location:</strong> ' . $interview->location_details . '</li>';
        }

        $notes_html = !empty($interview->interview_notes) ? '<p><strong>Additional Notes:</strong> ' . $interview->interview_notes . '</p>' : '';

        $replacements = [
            '{CANDIDATE_NAME}' => $interview->candidate_name,
            '{JOB_TITLE}' => $interview->job_title,
            '{COMPANY_NAME}' => config_item('company_name'),
            '{INTERVIEW_DATE}' => strftime(config_item('date_format'), strtotime($interview->interview_date)),
            '{INTERVIEW_TIME}' => date('g:i A', strtotime($interview->interview_time)),
            '{INTERVIEW_TYPE}' => $type_label,
            '{MEETING_LINK_OR_LOCATION}' => $meeting_or_location,
            '{INTERVIEWER_NAME}' => $interview->interviewer_name,
            '{INTERVIEW_NOTES}' => $notes_html,
            '{SITE_NAME}' => config_item('company_name')
        ];

        foreach ($replacements as $key => $val) {
            $message = str_replace($key, $val, $message);
            $subject = str_replace($key, $val, $subject);
        }

        $params = [
            'recipient' => $interview->candidate_email,
            'subject' => $subject,
            'message' => $message,
            'resourceed_file' => ''
        ];

        $sent = $this->recruitment_model->send_email($params);
        if ($sent) {
            $this->recruitment_model->mark_interview_email_sent($interview_id);
            log_message('info', "Interview email resent successfully to {$interview->candidate_email} for interview ID {$interview_id}");
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Interview email resent successfully']);
            exit;
        } else {
            log_message('error', "Failed to resend interview email to {$interview->candidate_email} for interview ID {$interview_id}");
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to send email. Check SMTP configuration.']);
            exit;
        }
    }

    public function update_interview_status()
    {
        if (!$this->input->is_ajax_request()) {
            redirect('admin/job_circular/manage_interviews');
        }

        $interview_id = $this->input->post('interview_id');
        $status = $this->input->post('status');
        $feedback = $this->input->post('feedback');
        $rating = $this->input->post('rating');

        $this->recruitment_model->update_interview_status($interview_id, $status, $feedback, $rating);
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function interview_detail($interview_id)
    {
        $data['title'] = lang('interview_detail');
        $data['interview'] = $this->recruitment_model->get_interview_by_id($interview_id);
        $data['subview'] = $this->load->view('admin/job_circular/interview_detail', $data, FALSE);
        $this->load->view('admin/_layout_modal_lg', $data);
    }

    public function delete_interview($interview_id)
    {
        $this->db->where('interview_id', $interview_id);
        $this->db->delete('tbl_interviews');

        if ($this->input->is_ajax_request()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
        set_message('success', lang('interview_deleted'));
        redirect('admin/job_circular/manage_interviews');
    }

    // ==================== OFFER LETTERS ====================

    public function manage_offers()
    {
        $data['title'] = lang('offer_letters');
        $filters = [
            'status' => $this->input->get('status') ?: null,
            'job_circular_id' => $this->input->get('job_circular_id') ?: null,
        ];
        $data['offers'] = $this->recruitment_model->get_offers($filters);
        $data['job_circulars'] = $this->db->get('tbl_job_circular')->result();
        $data['subview'] = $this->load->view('admin/job_circular/manage_offers', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    public function create_offer($job_appliactions_id = null)
    {
        $data['title'] = lang('create_offer');
        $data['templates'] = $this->recruitment_model->get_offer_templates();
        $data['job_circulars'] = $this->db->where('status', 'published')->get('tbl_job_circular')->result();

        if ($job_appliactions_id) {
            $data['application'] = $this->recruitment_model->get_application_detail($job_appliactions_id);
            $default_template = $this->recruitment_model->get_default_offer_template();
            if ($default_template) {
                $data['template_subject'] = $default_template->template_subject;
                $data['template_body'] = $default_template->template_body;
            }
        }

        $data['subview'] = $this->load->view('admin/job_circular/create_offer', $data, FALSE);
        $this->load->view('admin/_layout_modal_lg', $data);
    }

    public function save_offer($id = null)
    {
        $data = $this->input->post(null, true);

        $offer_data = [
            'job_appliactions_id' => $data['job_appliactions_id'],
            'job_circular_id' => $data['job_circular_id'],
            'offer_template_id' => !empty($data['template_id']) ? $data['template_id'] : null,
            'offer_subject' => $data['offer_subject'],
            'offer_body' => $data['offer_body'],
            'salary_offered' => !empty($data['salary_offered']) ? $data['salary_offered'] : null,
            'joining_date' => !empty($data['joining_date']) ? $data['joining_date'] : null,
            'additional_terms' => !empty($data['additional_terms']) ? $data['additional_terms'] : null,
            'created_by' => $this->session->userdata('user_id')
        ];

        $offer_id = $this->recruitment_model->save_offer($offer_data, $id);

        // Send email if requested
        if (!empty($data['send_email'])) {
            $this->send_offer_email($offer_id);
        }

        if ($this->input->is_ajax_request()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'offer_id' => $offer_id]);
            exit;
        }

        set_message('success', lang('offer_saved'));
        redirect('admin/job_circular/manage_offers');
    }

    public function send_offer_email($offer_id)
    {
        $offer = $this->recruitment_model->get_offer_by_id($offer_id);
        if (empty($offer)) return false;

        $designation = '-';
        if (!empty($offer->designations_id)) {
            $design_info = $this->db->where('designations_id', $offer->designations_id)->get('tbl_designations')->row();
            if ($design_info) $designation = $design_info->designations;
        }

        $replacements = [
            '{CANDIDATE_NAME}' => $offer->candidate_name,
            '{JOB_TITLE}' => $offer->job_title,
            '{DESIGNATION}' => $designation,
            '{SALARY}' => $offer->salary_offered ?: 'As discussed',
            '{JOINING_DATE}' => $offer->joining_date ? strftime(config_item('date_format'), strtotime($offer->joining_date)) : 'To be confirmed',
            '{EMPLOYMENT_TYPE}' => lang($offer->employment_type),
            '{COMPANY_NAME}' => config_item('company_name'),
            '{ADDITIONAL_TERMS}' => $offer->additional_terms ?: '',
            '{SITE_NAME}' => config_item('company_name')
        ];

        $subject = $offer->offer_subject;
        $body = $offer->offer_body;

        foreach ($replacements as $key => $val) {
            $subject = str_replace($key, $val, $subject);
            $body = str_replace($key, $val, $body);
        }

        // Update the offer in database with replaced placeholders
        $this->db->where('offer_id', $offer_id);
        $this->db->update('tbl_offer_letters', [
            'offer_subject' => $subject,
            'offer_body' => $body
        ]);

        $params = [
            'recipient' => $offer->candidate_email,
            'subject' => $subject,
            'message' => $body,
            'resourceed_file' => ''
        ];

        $sent = $this->recruitment_model->send_email($params);
        if ($sent) {
            $this->recruitment_model->update_offer_status($offer_id, 'sent');
            log_message('info', "Offer email sent successfully to {$offer->candidate_email} for offer ID {$offer_id}");
        } else {
            log_message('error', "Failed to send offer email to {$offer->candidate_email} for offer ID {$offer_id}");
        }
        return $sent;
    }

    public function resend_offer_email($offer_id)
    {
        $offer = $this->recruitment_model->get_offer_by_id($offer_id);
        if (empty($offer)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Offer not found']);
            exit;
        }

        $designation = '-';
        if (!empty($offer->designations_id)) {
            $design_info = $this->db->where('designations_id', $offer->designations_id)->get('tbl_designations')->row();
            if ($design_info) $designation = $design_info->designations;
        }

        $replacements = [
            '{CANDIDATE_NAME}' => $offer->candidate_name,
            '{JOB_TITLE}' => $offer->job_title,
            '{DESIGNATION}' => $designation,
            '{SALARY}' => $offer->salary_offered ?: 'As discussed',
            '{JOINING_DATE}' => $offer->joining_date ? strftime(config_item('date_format'), strtotime($offer->joining_date)) : 'To be confirmed',
            '{EMPLOYMENT_TYPE}' => lang($offer->employment_type),
            '{COMPANY_NAME}' => config_item('company_name'),
            '{ADDITIONAL_TERMS}' => $offer->additional_terms ?: '',
            '{SITE_NAME}' => config_item('company_name')
        ];

        // Get original template or use stored offer content
        $subject = $offer->offer_subject;
        $body = $offer->offer_body;

        // Check if placeholders still exist in stored content
        $has_placeholders = (strpos($subject, '{') !== false || strpos($body, '{') !== false);
        if ($has_placeholders) {
            foreach ($replacements as $key => $val) {
                $subject = str_replace($key, $val, $subject);
                $body = str_replace($key, $val, $body);
            }
        }

        $params = [
            'recipient' => $offer->candidate_email,
            'subject' => $subject,
            'message' => $body,
            'resourceed_file' => ''
        ];

        $sent = $this->recruitment_model->send_email($params);
        if ($sent) {
            $this->recruitment_model->update_offer_status($offer_id, 'sent');
            log_message('info', "Offer email resent successfully to {$offer->candidate_email} for offer ID {$offer_id}");
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Offer email resent successfully']);
            exit;
        } else {
            log_message('error', "Failed to resend offer email to {$offer->candidate_email} for offer ID {$offer_id}");
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to send email. Check SMTP configuration.']);
            exit;
        }
    }

    public function update_offer_status_ajax()
    {
        if (!$this->input->is_ajax_request()) {
            redirect('admin/job_circular/manage_offers');
        }

        $offer_id = $this->input->post('offer_id');
        $status = $this->input->post('status');

        $this->recruitment_model->update_offer_status($offer_id, $status);
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function offer_detail($offer_id)
    {
        $data['title'] = lang('offer_detail');
        $data['offer'] = $this->recruitment_model->get_offer_by_id($offer_id);
        $data['subview'] = $this->load->view('admin/job_circular/offer_detail', $data, FALSE);
        $this->load->view('admin/_layout_modal_lg', $data);
    }

    public function delete_offer($offer_id)
    {
        $this->db->where('offer_id', $offer_id);
        $this->db->delete('tbl_offer_letters');

        if ($this->input->is_ajax_request()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
        set_message('success', lang('offer_deleted'));
        redirect('admin/job_circular/manage_offers');
    }

    public function get_offer_template_ajax($template_id)
    {
        $template = $this->recruitment_model->get_offer_template_by_id($template_id);
        header('Content-Type: application/json');
        if ($template) {
            echo json_encode(['success' => true, 'subject' => $template->template_subject, 'body' => $template->template_body]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    public function preview_offer()
    {
        if (!$this->input->is_ajax_request()) {
            redirect('admin/job_circular/manage_offers');
        }

        $body = $this->input->post('offer_body');
        $subject = $this->input->post('offer_subject');

        $application_id = $this->input->post('job_appliactions_id');
        if ($application_id) {
            $app = $this->recruitment_model->get_application_detail($application_id);
            if ($app) {
                $designation = '-';
                if (!empty($app->designations_id)) {
                    $design_info = $this->db->where('designations_id', $app->designations_id)->get('tbl_designations')->row();
                    if ($design_info) $designation = $design_info->designations;
                }

                $joining_date = $this->input->post('joining_date');
                $joining_date_timestamp = $joining_date ? strtotime($joining_date) : false;
                $formatted_joining_date = 'To be confirmed';
                if ($joining_date_timestamp !== false) {
                    $format = config_item('date_format');
                    $map = ['%d' => 'd', '%m' => 'm', '%Y' => 'Y', '%y' => 'y', '%H' => 'H', '%M' => 'i', '%S' => 's'];
                    $formatted_joining_date = date(strtr($format, $map), $joining_date_timestamp);
                }

                $replacements = [
                    '{CANDIDATE_NAME}' => $app->name,
                    '{JOB_TITLE}' => $app->job_title,
                    '{DESIGNATION}' => $designation,
                    '{SALARY}' => $this->input->post('salary_offered') ?: 'As discussed',
                    '{JOINING_DATE}' => $formatted_joining_date,
                    '{EMPLOYMENT_TYPE}' => lang($app->employment_type),
                    '{COMPANY_NAME}' => config_item('company_name'),
                    '{ADDITIONAL_TERMS}' => $this->input->post('additional_terms') ?: '',
                    '{SITE_NAME}' => config_item('company_name')
                ];

                foreach ($replacements as $key => $val) {
                    $body = str_replace($key, $val, $body);
                    $subject = str_replace($key, $val, $subject);
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'subject' => $subject, 'body' => $body]);
        exit;
    }
}
