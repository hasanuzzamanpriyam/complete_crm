<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Expenses extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Expense_model');
    }

    /**
     * List all Expense Schedules
     */
    public function index()
    {
        $data['title'] = lang('expense_schedules') ?: 'Expense Schedules';
        $data['schedules'] = $this->Expense_model->get_all_expenses();
        
        $data['subview'] = $this->load->view('admin/expenses/list', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    /**
     * Load the Add Schedule form
     */
    public function create()
    {
        $data['title'] = lang('add_schedule') ?: 'Add Schedule';
        $data['subview'] = $this->load->view('admin/expenses/create', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    /**
     * Route handler mapping to Delete a Schedule
     */
    public function delete($id)
    {
        // Safe cascading purge from the core model lookup
        $this->Expense_model->delete_expense($id);
        // Typical CodeIgniter redirect mechanism back to parent tracker list
        redirect('admin/expenses');
    }

    /**
     * Edit an existing expense schedule
     */
    public function edit($id)
    {
        $data['title'] = lang('edit_schedule') ?: 'Edit Schedule';
        $data['expense'] = $this->Expense_model->get_expense_by_id($id);
        
        if (empty($data['expense'])) {
            set_message('error', 'Expense not found!');
            redirect('admin/expenses');
        }

        $data['subview'] = $this->load->view('admin/expenses/edit', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    /**
     * Update an existing expense via AJAX
     */
    public function update_expense($id)
    {
        if (!$this->input->post()) {
            return $this->output->set_status_header(405)->set_output(json_encode(['success' => false, 'message' => 'Invalid method.']));
        }

        $this->load->library('form_validation');
        $this->form_validation->set_rules('task_name', 'Task Name', 'trim|required');
        $this->form_validation->set_rules('payment_type', 'Payment Type', "trim|required|in_list[daily,monthly,'bi-monthly',quarterly,yearly]");
        $this->form_validation->set_rules('last_payment_date', 'Date', 'trim|required|callback_valid_date');
        $this->form_validation->set_rules('amount', 'Amount', 'trim|required|numeric|greater_than[0]');

        if ($this->form_validation->run() === FALSE) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(['success' => false, 'message' => strip_tags(validation_errors())]));
        }

        $data = [
            'task_name'         => $this->input->post('task_name', TRUE),
            'payment_type'      => $this->input->post('payment_type', TRUE),
            'last_payment_date' => $this->input->post('last_payment_date', TRUE),
            'amount'            => $this->input->post('amount', TRUE),
            'description'       => $this->input->post('description', TRUE)
        ];

        if ($this->Expense_model->update_expense($id, $data)) {
            return $this->output->set_content_type('application/json')->set_output(json_encode(['success' => true, 'message' => 'Expense updated successfully.']));
        } else {
            return $this->output->set_content_type('application/json')->set_output(json_encode(['success' => false, 'message' => 'Failed to update.']));
        }
    }

    /**
     * Add a new expense via AJAX
     * 
     * @return void // Outputs JSON directly
     */
    public function add_expense()
    {
        if (!$this->input->post()) {
            return $this->output
                ->set_status_header(405) // Method Not Allowed
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Invalid request method. Only POST is allowed.'
                ]));
        }

        $this->load->library('form_validation');

        $this->form_validation->set_rules('task_name', 'Task Name', 'trim|required');
        $this->form_validation->set_rules('payment_type', 'Payment Type', "trim|required|in_list[daily,monthly,'bi-monthly',quarterly,yearly]");
        $this->form_validation->set_rules('last_payment_date', 'Last Payment Date', 'trim|required|callback_valid_date');
        $this->form_validation->set_rules('amount', 'Amount', 'trim|required|numeric|greater_than[0]');
        $this->form_validation->set_rules('description', 'Description', 'trim');

        if ($this->form_validation->run() === FALSE) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => strip_tags(validation_errors()) 
                ]));
        }

        $data = [
            'task_name'        => $this->input->post('task_name', TRUE),
            'payment_type'     => $this->input->post('payment_type', TRUE),
            'last_payment_date'=> $this->input->post('last_payment_date', TRUE),
            'amount'           => $this->input->post('amount', TRUE),
            'description'      => $this->input->post('description', TRUE)
        ];

        $insert_id = $this->Expense_model->insert_expense($data);

        if ($insert_id) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'message' => 'Expense added successfully.',
                    'expense_id' => $insert_id
                ]));
        } else {
            return $this->output
                ->set_status_header(500) // Internal Server Error
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Failed to add expense to the database. Please try again.'
                ]));
        }
    }

    /**
     * Custom CI validation callback to strictly check Y-m-d date format
     */
    public function valid_date($date)
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        if ($d && $d->format('Y-m-d') === $date) {
            return TRUE;
        }
        
        $this->form_validation->set_message('valid_date', 'The {field} field must be a valid date in YYYY-MM-DD format.');
        return FALSE;
    }
}
