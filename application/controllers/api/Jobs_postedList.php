<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Jobs_postedList extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->db->select('tbl_job_circular.*, tbl_designations.designations');
        $this->db->from('tbl_job_circular');
        $this->db->join('tbl_designations', 'tbl_designations.designations_id = tbl_job_circular.designations_id', 'left');
        $this->db->where('tbl_job_circular.status', 'published');
        $this->db->order_by('tbl_job_circular.job_circular_id', 'desc');

        $query = $this->db->get();
        $jobs = $query->result();

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode(['jobs' => $jobs]));
    }
}
