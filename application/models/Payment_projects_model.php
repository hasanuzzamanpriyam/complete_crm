<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Payment_projects_model
 * Manages external projects registered to use the CRM as a payment hub.
 */
class Payment_projects_model extends MY_Model
{
    public $_table_name  = 'tbl_payment_projects';
    public $_primary_key = 'id';
    public $_order_by    = 'id';

    /**
     * Get all projects, optionally filtered by status.
     */
    public function get_all($status = null)
    {
        if ($status) {
            $this->db->where('status', $status);
        }
        $this->db->order_by('id', 'DESC');
        return $this->db->get('tbl_payment_projects')->result();
    }

    /**
     * Get a single project by its primary key.
     */
    public function get_project($id)
    {
        return $this->db->get_where('tbl_payment_projects', ['id' => $id])->row();
    }

    /**
     * Authenticate a project by API key and secret.
     * Returns the project row on success, or FALSE on failure.
     */
    public function authenticate($api_key, $api_secret)
    {
        $project = $this->db->get_where('tbl_payment_projects', [
            'api_key' => $api_key,
            'status'  => 'active',
        ])->row();

        if (empty($project)) {
            return false;
        }

        // Compare secret using a timing-safe check
        if (!hash_equals($project->api_secret, $api_secret)) {
            return false;
        }

        return $project;
    }

    /**
     * Generate a unique API key/secret pair.
     * Returns ['api_key' => ..., 'api_secret' => ...].
     */
    public function generate_credentials()
    {
        return [
            'api_key'    => 'pk_' . bin2hex(random_bytes(16)),
            'api_secret' => 'sk_' . bin2hex(random_bytes(24)),
        ];
    }

    /**
     * Create a new project with auto-generated credentials.
     */
    public function create_project($data)
    {
        $creds = $this->generate_credentials();
        $insert = [
            'project_name' => $data['project_name'],
            'api_key'      => $creds['api_key'],
            'api_secret'   => $creds['api_secret'],
            'callback_url' => isset($data['callback_url']) ? $data['callback_url'] : null,
            'webhook_url'  => isset($data['webhook_url']) ? $data['webhook_url'] : null,
            'status'       => 'active',
            'created_at'   => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('tbl_payment_projects', $insert);
        $new_id = $this->db->insert_id();
        return array_merge(['id' => $new_id], $insert);
    }

    /**
     * Update a project record.
     */
    public function update_project($id, $data)
    {
        $allowed = ['project_name', 'callback_url', 'webhook_url', 'status'];
        $update  = array_intersect_key($data, array_flip($allowed));
        $this->db->where('id', $id)->update('tbl_payment_projects', $update);
        return $this->db->affected_rows();
    }

    /**
     * Regenerate credentials for a project (e.g. after a security incident).
     */
    public function regenerate_credentials($id)
    {
        $creds = $this->generate_credentials();
        $this->db->where('id', $id)->update('tbl_payment_projects', $creds);
        return $creds;
    }

    /**
     * Soft-delete by setting status to inactive.
     */
    public function deactivate($id)
    {
        $this->db->where('id', $id)->update('tbl_payment_projects', ['status' => 'inactive']);
        return $this->db->affected_rows();
    }

    /**
     * Hard delete a project and all its transactions.
     */
    public function delete_project($id)
    {
        $this->db->where('project_id', $id)->delete('tbl_external_transactions');
        $this->db->where('id', $id)->delete('tbl_payment_projects');
        return $this->db->affected_rows();
    }
}
