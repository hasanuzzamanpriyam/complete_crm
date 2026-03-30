<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Payment Hub Admin Controller
 *
 * @property Api_clients_model $api_clients_model
 * @property Payments_model $payments_model
 * @property Api_tokens_model $api_tokens_model
 * @property Payment_gateways_model $payment_gateways_model
 * @property Payment_transactions_model $payment_transactions_model
 * @property Refunds_model $refunds_model
 * @property Payment_logs_model $payment_logs_model
 * @property Webhook_logs_model $webhook_logs_model
 * @property Payment_service $payment_service
 * @property CI_Input $input
 * @property CI_Output $output
 * @property CI_Session $session
 * @property CI_DB_query_builder $db
 */
class Payment_hub extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model([
            'api_clients_model',
            'payments_model',
            'api_tokens_model',
            'payment_gateways_model',
            'payment_transactions_model',
            'payment_logs_model',
            'webhook_logs_model',
            'refunds_model'
        ]);
        $this->load->library('session');
        $this->load->library('payment_service');
    }

    // -----------------------------------------------------------------------
    // DASHBOARD / OVERVIEW
    // -----------------------------------------------------------------------

    /**
     * Main payment hub dashboard with KPI summary.
     */
    public function index()
    {
        $data['title']   = 'Payment Hub Dashboard';
        $filters = [
            'date_from' => $this->input->get('date_from', true),
            'date_to'   => $this->input->get('date_to', true),
        ];
        
        $data['stats']   = $this->payments_model->get_dashboard_stats($filters);
        $data['projects']= $this->api_clients_model->get();
        $data['gateways']= $this->payment_gateways_model->get();
        
        $data['subview'] = $this->load->view('admin/payment_hub/index', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    // -----------------------------------------------------------------------
    // PROJECTS MANAGEMENT
    // -----------------------------------------------------------------------

    /**
     * List all registered external projects.
     */
    public function projects()
    {
        $data['title']    = 'Registered Projects';
        $data['projects'] = $this->api_clients_model->get();
        $data['subview']  = $this->load->view('admin/payment_hub/projects', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    /**
     * Show create / edit form for a project.
     */
    public function project_form($id = null)
    {
        $data['title'] = empty($id) ? 'Add New Project' : 'Edit Project';
        if (!empty($id)) {
            $data['project'] = $this->api_clients_model->get($id);
            if (empty($data['project'])) {
                set_message('error', 'Project not found.');
                redirect('admin/payment_hub/projects');
            }
        }
        $data['subview'] = $this->load->view('admin/payment_hub/project_form', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    /**
     * Save (create or update) a project.
     */
    public function save_project($id = null)
    {
        $form_data = [
            'project_name' => $this->input->post('project_name', true),
            'callback_url' => $this->input->post('callback_url', true),
            'webhook_url'  => $this->input->post('webhook_url', true),
        ];

        if (empty($form_data['project_name'])) {
            set_message('error', 'Project name is required.');
            redirect('admin/payment_hub/project_form/' . $id);
        }

        if (empty($id)) {
            // Generate Client ID & Secret for the new project
            $form_data['client_id'] = 'ph_' . bin2hex(random_bytes(8));
            $form_data['client_secret'] = bin2hex(random_bytes(32));
            $form_data['status'] = 'active';
            $form_data['created_at'] = date('Y-m-d H:i:s');
            
            $new_id = $this->api_clients_model->save($form_data);
            set_message('success', 'Project created. Client ID: <strong>' . $form_data['client_id'] . '</strong>');
        } else {
            $this->api_clients_model->save($form_data, $id);
            set_message('success', 'Project updated successfully.');
        }

        redirect('admin/payment_hub/projects');
    }

    /**
     * Toggle a project's status (active / inactive).
     */
    public function toggle_project($id)
    {
        $project = $this->api_clients_model->get($id);
        if (empty($project)) {
            echo json_encode(['status' => 'error', 'message' => 'Project not found']);
            exit;
        }
        $new_status = ($project->status === 'active') ? 'inactive' : 'active';
        $this->api_clients_model->save(['status' => $new_status], $id);
        echo json_encode(['status' => 'success', 'new_status' => $new_status]);
        exit;
    }

    /**
     * Regenerate client IDs and secrets for a project.
     */
    public function regenerate_credentials($id)
    {
        $new_creds = [
            'client_id'     => 'ph_' . bin2hex(random_bytes(8)),
            'client_secret' => bin2hex(random_bytes(32)),
        ];
        $this->api_clients_model->save($new_creds, $id);
        set_message('success', 'New Client ID: <strong>' . $new_creds['client_id'] . '</strong><br>New Client Secret: <strong>' . $new_creds['client_secret'] . '</strong>');
        redirect('admin/payment_hub/project_detail/' . $id);
    }

    /**
     * View credentials and transaction history for a single project.
     */
    public function project_detail($id)
    {
        $project = $this->api_clients_model->get($id);
        if (empty($project)) {
            set_message('error', 'Project not found.');
            redirect('admin/payment_hub/projects');
        }
        $data['title']        = 'Project: ' . $project->project_name;
        $data['project']      = $project;
        $data['transactions'] = $this->payments_model->get_by(['client_id' => $id]);
        $data['tokens']       = $this->api_tokens_model->get_by_project($id);
        $data['subview']      = $this->load->view('admin/payment_hub/project_detail', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    // -----------------------------------------------------------------------
    // TOKEN MANAGEMENT
    // -----------------------------------------------------------------------

    /**
     * Issue a new bearer token for a project.
     */
    public function issue_token($project_id)
    {
        $project = $this->api_clients_model->get($project_id);
        if (empty($project)) {
            set_message('error', 'Project not found.');
            redirect('admin/payment_hub/projects');
        }

        $token_name = $this->input->post('token_name', true) ?: 'Default Token';
        $expires_at = $this->input->post('expires_at', true) ?: null;

        $result = $this->api_tokens_model->create($project_id, [
            'token_name' => $token_name,
            'expires_at' => $expires_at
        ]);

        // We MUST display the raw_token to the user now because it's not stored.
        // Using session flashdata for structured display in the view.
        $this->session->set_flashdata('token_raw', $result['raw_token']);
        $this->session->set_flashdata('token_secret', $result['signing_secret']);
        
        set_message('success', 'New token issued! Copy it from the informational box below.');
        redirect('admin/payment_hub/project_detail/' . $project_id);
    }

    /**
     * Toggle token status (active/disabled).
     */
    public function toggle_token($token_id)
    {
        $token = $this->api_tokens_model->get($token_id);
        if (empty($token)) {
            echo json_encode(['status' => 'error', 'message' => 'Token not found']);
            exit;
        }

        $new_status = ($token->status === 'active') ? 'disabled' : 'active';
        $this->api_tokens_model->set_status($token_id, $new_status);
        
        set_message('success', 'Token status updated to ' . $new_status);
        redirect('admin/payment_hub/project_detail/' . $token->project_id);
    }

    /**
     * Revoke a token permanently.
     */
    public function revoke_token($token_id)
    {
        $token = $this->api_tokens_model->get($token_id);
        if (empty($token)) {
            set_message('error', 'Token not found.');
            redirect('admin/payment_hub/projects');
        }

        $this->api_tokens_model->set_status($token_id, 'revoked');
        set_message('success', 'Token revoked successfully.');
        redirect('admin/payment_hub/project_detail/' . $token->project_id);
    }

    /**
     * Update IP whitelist for a token.
     */
    public function update_whitelist($token_id)
    {
        $token = $this->api_tokens_model->get($token_id);
        if (empty($token)) {
            set_message('error', 'Token not found.');
            redirect('admin/payment_hub/projects');
        }

        $ips_str = $this->input->post('ip_whitelist', true);
        $ips = array_filter(explode(',', $ips_str));
        $this->api_tokens_model->update_ip_whitelist($token_id, $ips);

        set_message('success', 'IP whitelist updated.');
        redirect('admin/payment_hub/project_detail/' . $token->project_id);
    }

    /**
     * Delete a project (and all its transactions).
     */
    public function delete_project($id)
    {
        $this->db->where('client_id', $id)->delete('tbl_hub_payments');
        $this->api_clients_model->delete($id);
        echo json_encode(['status' => 'success', 'message' => 'Project deleted.']);
        exit;
    }

    // -----------------------------------------------------------------------
    // GATEWAY MANAGEMENT
    // -----------------------------------------------------------------------

    /**
     * List all payment gateways.
     */
    public function gateways()
    {
        $data['title']    = 'Payment Gateways';
        $data['gateways'] = $this->payment_gateways_model->get();
        $data['subview']  = $this->load->view('admin/payment_hub/gateways', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    /**
     * Toggle gateway status.
     */
    public function toggle_gateway($id)
    {
        $gateway = $this->payment_gateways_model->get($id);
        if ($gateway) {
            $new_status = ($gateway->status === 'active') ? 'inactive' : 'active';
            $this->payment_gateways_model->save(['status' => $new_status], $id);
            set_message('success', "Gateway status updated.");
        }
        redirect('admin/payment_hub/gateways');
    }

    /**
     * Set a gateway as system default.
     */
    public function set_default_gateway($id)
    {
        // Set all to non-default first
        $this->db->update('tbl_payment_gateways', ['is_default' => 0]);
        // Set this as default
        $this->payment_gateways_model->save(['is_default' => 1], $id);
        set_message('success', "Default gateway updated.");
        redirect('admin/payment_hub/gateways');
    }

    // -----------------------------------------------------------------------
    // TRANSACTIONS
    // -----------------------------------------------------------------------

    /**
     * View all external transactions with optional filters.
     */
    public function transactions()
    {
        $data['title']        = 'Payment Hub Transactions';
        $data['clients']      = $this->api_clients_model->get();
        $data['gateways']     = $this->payment_gateways_model->get();
        
        $filters = [
            'client_id'   => $this->input->get('client_id', true),
            'gateway_id'  => $this->input->get('gateway_id', true),
            'status'      => $this->input->get('status', true),
            'date_from'   => $this->input->get('date_from', true),
            'date_to'     => $this->input->get('date_to', true),
        ];

        $data['filters']      = $filters;
        $data['transactions'] = $this->payments_model->get_detailed_list($filters); // Need to implement this in model
        $data['subview']      = $this->load->view('admin/payment_hub/transactions', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    /**
     * AJAX: Fetch transactions list for DataTable.
     */
    public function transactionsList()
    {
        if (!$this->input->is_ajax_request()) {
            redirect('admin/dashboard');
        }
        $filters = [
            'client_id'  => $this->input->get('client_id', true),
            'gateway_id' => $this->input->get('gateway_id', true),
            'status'     => $this->input->get('status', true),
        ];

        $start  = (int) $this->input->get('start', true);
        $length = (int) $this->input->get('length', true) ?: 10;
        
        $rows   = $this->payments_model->get_detailed_list($filters, $length, $start);
        $total  = count($this->payments_model->get_detailed_list($filters));

        $data = [];
        foreach ($rows as $t) {
            $status_badge = $this->_status_badge($t->status);
            $data[] = [
                $t->id,
                $t->client_name,
                $t->external_reference,
                display_money($t->amount) . ' ' . $t->currency,
                $t->gateway_name ?: 'N/A',
                $t->id, // Just ID or gateway_txn_id if we want
                $status_badge,
                $t->created_at,
                '<a href="' . base_url('admin/payment_hub/transaction_detail/' . $t->id) . '" class="btn btn-xs btn-info"><i class="fa fa-eye"></i></a>',
            ];
        }

        echo json_encode([
            'draw'            => (int) $this->input->get('draw', true),
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => $data,
        ]);
        exit;
    }

    /**
     * View detail of a single transaction.
     */
    public function transaction_detail($id)
    {
        $txn = $this->payments_model->get_detailed($id);
        if (empty($txn)) {
            set_message('error', 'Transaction not found.');
            redirect('admin/payment_hub/transactions');
        }
        $data['title']        = 'Payment Record #' . $id;
        $data['txn']          = $txn;
        $data['attempts']     = $this->payment_transactions_model->get_by(['payment_id' => $id]);
        $data['logs']         = $this->payment_logs_model->get_by(['payment_id' => $id]);
        $data['webhooks']     = $this->webhook_logs_model->get_by(['payment_id' => $id]);
        
        $data['subview'] = $this->load->view('admin/payment_hub/transaction_detail', $data, TRUE);
        $this->load->view('admin/_layout_main', $data);
    }

    /**
     * Handle refund request from UI.
     */
    public function process_refund($id)
    {
        try {
            $amount = $this->input->post('amount', true) ?: null;
            $reason = $this->input->post('reason', true) ?: 'Refunded from CRM Admin';

            if ($this->payment_service->refund_transaction($id, $amount, $reason)) {
                set_message('success', 'Refund processed successfully.');
            } else {
                set_message('error', 'Refund failed at gateway. Check logs.');
            }
        } catch (Exception $e) {
            set_message('error', $e->getMessage());
        }
        redirect('admin/payment_hub/transaction_detail/' . $id);
    }

    // -----------------------------------------------------------------------
    // HELPERS
    // -----------------------------------------------------------------------

    protected function _status_badge($status)
    {
        $map = [
            'success'   => 'success',
            'pending'   => 'warning',
            'failed'    => 'danger',
            'cancelled' => 'default',
        ];
        $class = $map[$status] ?? 'default';
        return '<span class="label label-' . $class . '">' . ucfirst($status) . '</span>';
    }
}
