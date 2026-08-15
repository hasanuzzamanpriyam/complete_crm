<?php

class Tasks_Model extends MY_Model
{

    public $_table_name;
    public $_order_by;
    public $_primary_key;

    function set_progress($id)
    {
        $project_info = $this->check_by(array('project_id' => $id), 'tbl_project');
        if ($project_info->calculate_progress != '0') {
            if ($project_info->calculate_progress == 'through_tasks') {
                $done_task = $this->db->where(array('project_id' => $id, 'task_status' => 'completed'))->get('tbl_task')->result();
                $total_tasks = $this->db->where(array('project_id' => $id))->get('tbl_task')->result();
                $done_task = count($done_task);
                $total_tasks = count($total_tasks);
                $progress = $total_tasks > 0 ? round(($done_task / $total_tasks) * 100) : 0;
                if ($progress > 100) {
                    $progress = 100;
                }
            }
        } else {
            $progress = $project_info->progress;
        }
        if (empty($progress)) {
            $progress = 0;
        }
        if ($progress >= 100) {
            $progress = 100;
            $p_data['project_status'] = 'completed';
        } elseif ($progress < 100 && isset($project_info->project_status) && $project_info->project_status === 'completed') {
            $p_data['project_status'] = 'in_progress';
        }
        $p_data['progress'] = $progress;

        $this->_table_name = "tbl_project"; //table name
        $this->_primary_key = "project_id";
        $this->save($p_data, $id);
        return true;
    }

    function get_task_progress($id)
    {
        $project_info = $this->check_by(array('task_id' => $id), 'tbl_task');
        if ($project_info->task_status == 'completed') {
            $progress = 100;
        } else {
            if (!empty($project_info->calculate_progress) && $project_info->calculate_progress != '0') {
                if ($project_info->calculate_progress == 'through_sub_tasks') {
                    $estimate_hours = $project_info->task_hour;
                    $percentage = $this->get_estime_time($estimate_hours);
                    if ($percentage != 0) {
                        $task_time = $this->task_spent_time_by_id($id);
                        if ($percentage != 0) {
                            $progress = round(($task_time / $percentage) * 100);
                        }
                    }
                } else {
                    $done_task = $this->db->where(array('project_id' => $id, 'task_status' => 'completed'))->get('tbl_task')->result();
                    $total_tasks = $this->db->where(array('project_id' => $id))->get('tbl_task')->result();
                    if (count(array($done_task)) > 0) {
                        $done_task = count(array($done_task));
                    } else {
                        $done_task = 0;
                    }
                    if (count(array($total_tasks)) > 0) {
                        $total_tasks = count(array($total_tasks));
                    } else {
                        $total_tasks = 0;
                    }
                    if ($total_tasks != 0) {
                        $progress = round(($done_task / $total_tasks) * 100);
                    }
                }
            } else {
                $progress = $project_info->task_progress;
            }
            if (empty($progress)) {
                $progress = 0;
            } else {
                if ($progress > 100) {
                    $progress = 100;
                }
            }
        }

        return $progress;
    }

    function set_task_progress($id)
    {
        $project_info = $this->check_by(array('task_id' => $id), 'tbl_task');

        if (!empty($project_info->calculate_progress) && $project_info->calculate_progress != '0') {
            if ($project_info->calculate_progress == 'through_tasks_hours') {
                $task_hour = $project_info->task_hour;
                $percentage = $this->get_estime_time($task_hour);
                $task_time = $this->task_spent_time_by_id($id);
                if ($percentage != 0) {
                    $progress = round(($task_time / $percentage) * 100);
                }
            } else {
                $done_task = $this->db->where(array('project_id' => $id, 'task_status' => 'completed'))->get('tbl_task')->result();
                $total_tasks = $this->db->where(array('project_id' => $id))->get('tbl_task')->result();
                if (count(array($done_task)) > 0) {
                    $done_task = count(array($done_task));
                } else {
                    $done_task = 0;
                }
                if (count(array($total_tasks)) > 0) {
                    $total_tasks = count(array($total_tasks));
                } else {
                    $total_tasks = 0;
                }
                if (empty($total_tasks) || empty($done_task)) {
                    $progress = 0;
                } else {
                    $progress = round(($done_task / $total_tasks) * 100);
                }

                if ($progress > 100) {
                    $progress = 100;
                }
            }
        } else {
            $progress = $project_info->task_progress;
        }
        if (empty($progress)) {
            $progress = 0;
        } else if ($progress >= 100) {
            $progress = 100;
            $t_data['task_status'] = 'completed';
        }
        $t_data['task_progress'] = $progress;

        $this->_table_name = "tbl_task"; //table name
        $this->_primary_key = "task_id";
        $this->save($t_data, $id);
    }


    public function get_statuses()
    {
        $statuses = array(
            array(
                'id' => 1,
                'value' => 'not_started',
                'name' => lang('not_started'),
                'order' => 1,
            ),
            array(
                'id' => 2,
                'value' => 'in_progress',
                'name' => lang('in_progress'),
                'order' => 2,
            ),
            array(
                'id' => 3,
                'value' => 'completed',
                'name' => lang('completed'),
                'order' => 3,
            ),
            array(
                'id' => 4,
                'value' => 'deferred',
                'name' => lang('deferred'),
                'order' => 4,
            ),
            array(
                'id' => 5,
                'value' => 'waiting_for_someone',
                'name' => lang('waiting_for_someone'),
                'order' => 5,
            )
        );
        return $statuses;
    }

    public function get_tasks($filterBy)
    {
        $tasks = array();
        $all_tasks = array_reverse($this->get_permission('tbl_task'));
        if (empty($filterBy)) {
            return $all_tasks;
        } else {
            foreach ($all_tasks as $v_tasks) {
                if ($v_tasks->task_status == $filterBy) {
                    array_push($tasks, $v_tasks);
                }
            }
        }
        return $tasks;
    }
    
    function notify_attachment_email($attachments_id)
    {
        $email_template = email_templates(array('email_group' => 'tasks_attachment'));
        $tasks_comment_info = $this->check_by(array('attachments_id' => $attachments_id), 'tbl_attachments');
        
        $tasks_info = get_row('tbl_task', array('module' => $tasks_comment_info->module, 'module_field_id' => $tasks_comment_info->module_field_id));
        $message = $email_template->template_body;
        
        $subject = $email_template->subject;
        
        $task_name = str_replace("{TASK_NAME}", $tasks_info->task_name, $message);
        $assigned_by = str_replace("{UPLOADED_BY}", ucfirst($this->session->userdata('name')), $task_name);
        $Link = str_replace("{TASK_URL}", base_url() . 'admin/tasks/details/' . $tasks_info->task_id . '/' . $data['active'] = 3, $assigned_by);
        $message = str_replace("{SITE_NAME}", config_item('company_name'), $Link);
        
        $data['message'] = $message;
        $message = $this->load->view('email_template', $data, TRUE);
        
        $params['subject'] = $subject;
        $params['message'] = $message;
        $params['resourceed_file'] = '';
        if (!empty($tasks_info->permission) && $tasks_info->permission != 'all') {
            $user = json_decode($tasks_info->permission);
            foreach ($user as $key => $v_user) {
                $allowed_user[] = $key;
            }
        } else {
            $allowed_user = $this->tasks_model->allowed_user_id('54');
        }
        if (!empty($allowed_user)) {
            foreach ($allowed_user as $v_user) {
                $login_info = $this->tasks_model->check_by(array('user_id' => $v_user), 'tbl_users');
                $params['recipient'] = $login_info->email;
                $this->tasks_model->send_email($params);
                
                if ($v_user != $this->session->userdata('user_id')) {
                    add_notification(array(
                        'to_user_id' => $v_user,
                        'from_user_id' => true,
                        'description' => 'not_uploaded_attachment',
                        'link' => 'admin/tasks/details/' . $tasks_info->task_id . '/3',
                        'value' => lang('task') . ' ' . $tasks_info->task_name,
                    ));
                }
            }
            show_notification($allowed_user);
        }
    }

    public function process_automated_renewal($module, $module_field_id)
    {
        // Step A: exact date task completed
        $completion_date = date('Y-m-d');

        $this->db->trans_start();

        if ($module === 'domain') {
            $this->load->model('domain_model');
            $domain = $this->domain_model->get_domain_by_id($module_field_id);
            if ($domain) {
                // Calculate future based on domain's duration if possible, else 1 year
                $amount = $domain->days ? $domain->days : 1;
                $unit = $domain->time_unit ? $domain->time_unit : 'Years';
                
                $calc_date = new DateTime($completion_date);
                $calc_date->modify("+" . $amount . " " . $unit);
                $future_exp_date = $calc_date->format('Y-m-d');

                // In this CRM, purchase_date is used as the primary Exp Date for Calendar plotting.
                $this->db->where('id', $module_field_id);
                $this->db->update('tbldomains', [
                    'purchase_date' => $future_exp_date,
                    'expiry_date' => date('Y-m-d', strtotime('+1 year', strtotime($future_exp_date))), // Next-next
                    'status' => 'Active'
                ]);


                $start_date = date('Y-m-d');
                $due_date = date('Y-m-d', strtotime($future_exp_date));

                $master_id = $this->get_or_create_master_task('domain', $domain->permission ?? 'all');
                $category_id = $this->get_or_create_server_category();

                $task_data = array(
                    'task_name' => 'Renew Domain: ' . $domain->domain_name,
                    'task_start_date' => $start_date,
                    'due_date' => $due_date,
                    'task_status' => 'not_started',
                    'task_progress' => 0,
                    'module' => 'domain',
                    'module_field_id' => $module_field_id,
                    'permission' => $domain->permission ?? 'all',
                    'sub_task_id' => $master_id,
                    'category_id' => $category_id
                );
                $this->db->insert('tbl_task', $task_data);
            }
        } elseif ($module === 'server_hosting') {
            $this->load->model('hosting_model');
            $hosting = $this->hosting_model->get_hosting_by_id($module_field_id);
            if ($hosting) {
                $amount = $hosting->days ? $hosting->days : 1;
                $unit = $hosting->time_unit ? $hosting->time_unit : 'Years';
                
                $calc_date = new DateTime($completion_date);
                $calc_date->modify("+" . $amount . " " . $unit);
                $future_exp_date = $calc_date->format('Y-m-d');

                $this->db->where('id', $module_field_id);
                $this->db->update('tblserver_hostings', [
                    'purchase_date' => $future_exp_date,
                    'expiry_date' => date('Y-m-d', strtotime('+1 year', strtotime($future_exp_date))),
                    'status' => 'Active'
                ]);

                $start_date = date('Y-m-d');
                $due_date = date('Y-m-d', strtotime($future_exp_date));

                $master_id = $this->get_or_create_master_task('server_hosting', $hosting->permission);
                $category_id = $this->get_or_create_server_category();

                $task_data = array(
                    'task_name' => 'Renew Hosting: ' . $hosting->title,
                    'task_start_date' => $start_date,
                    'due_date' => $due_date,
                    'task_status' => 'not_started',
                    'task_progress' => 0,
                    'module' => 'server_hosting',
                    'module_field_id' => $module_field_id,
                    'permission' => $hosting->permission,
                    'sub_task_id' => $master_id,
                    'category_id' => $category_id
                );
                $this->db->insert('tbl_task', $task_data);
            }
        } elseif ($module === 'billing') {
            $this->load->model('billing_model');
            $billing = $this->billing_model->get($module_field_id, TRUE);
            if ($billing) {
                $amount = !empty($billing->duration) ? (int) $billing->duration : 1;
                $unit = !empty($billing->time_unit) ? $billing->time_unit : 'Years';

                $calc_date = new DateTime($completion_date);
                $calc_date->modify("+" . $amount . " " . $unit);
                $future_exp_date = $calc_date->format('Y-m-d');

                $this->db->where('id', $module_field_id);
                $this->db->update('tbl_billing_orders', [
                    'expiry_date' => $future_exp_date,
                    'status' => 'Active'
                ]);

                $start_date = date('Y-m-d');
                $due_date = date('Y-m-d', strtotime($future_exp_date));

                $master_id = $this->get_or_create_master_task('billing', $billing->permission ?? 'all');
                $category_id = $this->get_or_create_server_category();

                $task_data = array(
                    'task_name' => 'Renew Billing: ' . $billing->label,
                    'task_start_date' => $start_date,
                    'due_date' => $due_date,
                    'task_status' => 'not_started',
                    'task_progress' => 0,
                    'module' => 'billing',
                    'module_field_id' => $module_field_id,
                    'permission' => $billing->permission ?? 'all',
                    'sub_task_id' => $master_id,
                    'category_id' => $category_id
                );
                $this->db->insert('tbl_task', $task_data);
            }
        }

        $this->db->trans_complete();
    }

    public function get_or_create_server_category()
    {
        $category_name = 'Server Management';
        $this->db->where('customer_group', $category_name);
        $this->db->where('type', 'tasks');
        $query = $this->db->get('tbl_customer_group');
        if ($query->num_rows() > 0) {
            return $query->row()->customer_group_id;
        } else {
            $data = array(
                'customer_group' => $category_name,
                'type' => 'tasks'
            );
            $this->db->insert('tbl_customer_group', $data);
            return $this->db->insert_id();
        }
    }

    public function get_or_create_master_task($module, $permission = 'all')
    {
        $name_map = array(
            'domain' => 'Domain Management',
            'server_hosting' => 'Hosting Management',
            'billing' => 'Billing Management'
        );
        $master_task_name = isset($name_map[$module]) ? $name_map[$module] : ucfirst(str_replace('_', ' ', $module)) . ' Management';

        $this->db->where('task_name', $master_task_name);
        $this->db->where('module', 'server_management_master');
        $query = $this->db->get('tbl_task');

        if ($query->num_rows() > 0) {
            return $query->row()->task_id;
        } else {
            $category_id = $this->get_or_create_server_category();
            $data = array(
                'task_name' => $master_task_name,
                'task_description' => 'Master task for all ' . str_replace('_', ' ', $module) . ' related tasks.',
                'task_start_date' => date('Y-m-d'),
                'task_status' => 'not_started',
                'created_by' => $this->session->userdata('user_id'),
                'permission' => $permission,
                'category_id' => $category_id,
                'module' => 'server_management_master'
            );
            $this->db->insert('tbl_task', $data);
            return $this->db->insert_id();
        }
    }

    public function get_or_create_renewal_task($module, $module_field_id)
    {
        $task = $this->db->select('task_id')
            ->where('module', $module)
            ->where('module_field_id', $module_field_id)
            ->where('task_status !=', 'completed')
            ->order_by('task_id', 'DESC')
            ->get('tbl_task')
            ->row();

        if ($task) {
            return $task->task_id;
        }

        $master_id = $this->get_or_create_master_task($module);
        $category_id = $this->get_or_create_server_category();

        if ($module === 'domain') {
            $this->load->model('domain_model');
            $domain = $this->domain_model->get_domain_by_id($module_field_id);
            if ($domain) {
                $task_data = array(
                    'task_name' => 'Renew Domain: ' . $domain->domain_name,
                    'task_start_date' => date('Y-m-d'),
                    'due_date' => $domain->purchase_date,
                    'task_status' => 'not_started',
                    'task_progress' => 0,
                    'module' => 'domain',
                    'module_field_id' => $module_field_id,
                    'permission' => $domain->permission,
                    'sub_task_id' => $master_id,
                    'category_id' => $category_id
                );
                $this->db->insert('tbl_task', $task_data);
                return $this->db->insert_id();
            }
        } elseif ($module === 'server_hosting') {
            $this->load->model('hosting_model');
            $hosting = $this->hosting_model->get_hosting_by_id($module_field_id);
            if ($hosting) {
                $task_data = array(
                    'task_name' => 'Renew Hosting: ' . $hosting->title,
                    'task_start_date' => date('Y-m-d'),
                    'due_date' => $hosting->purchase_date,
                    'task_status' => 'not_started',
                    'task_progress' => 0,
                    'module' => 'server_hosting',
                    'module_field_id' => $module_field_id,
                    'permission' => $hosting->permission,
                    'sub_task_id' => $master_id,
                    'category_id' => $category_id
                );
                $this->db->insert('tbl_task', $task_data);
                return $this->db->insert_id();
            }
        } elseif ($module === 'billing') {
            $this->load->model('billing_model');
            $billing = $this->billing_model->get($module_field_id, TRUE);
            if ($billing) {
                $task_data = array(
                    'task_name' => 'Renew Billing: ' . $billing->label,
                    'task_start_date' => date('Y-m-d'),
                    'due_date' => $billing->expiry_date,
                    'task_status' => 'not_started',
                    'task_progress' => 0,
                    'module' => 'billing',
                    'module_field_id' => $module_field_id,
                    'permission' => $billing->permission,
                    'sub_task_id' => $master_id,
                    'category_id' => $category_id
                );
                $this->db->insert('tbl_task', $task_data);
                return $this->db->insert_id();
            }
        }

        return null;
    }

    public function save($data, $id = NULL)
    {
        return parent::save($data, $id);
    }
}
