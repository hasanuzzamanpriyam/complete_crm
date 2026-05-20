<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Recruitment_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // ==================== SKILLS MANAGEMENT ====================

    public function get_all_skills($status = null)
    {
        $this->db->from('tbl_recruitment_skills');
        if ($status !== null) {
            $this->db->where('status', $status);
        }
        $this->db->order_by('skill_category', 'ASC');
        $this->db->order_by('skill_name', 'ASC');
        return $this->db->get()->result();
    }

    public function get_skill_by_id($skill_id)
    {
        return $this->db->where('skill_id', $skill_id)->get('tbl_recruitment_skills')->row();
    }

    public function save_skill($data, $id = null)
    {
        if ($id) {
            $this->db->where('skill_id', $id);
            $this->db->update('tbl_recruitment_skills', $data);
            return $id;
        } else {
            $this->db->insert('tbl_recruitment_skills', $data);
            return $this->db->insert_id();
        }
    }

    public function delete_skill($skill_id)
    {
        $this->db->where('skill_id', $skill_id);
        $this->db->delete('tbl_recruitment_skills');
    }

    public function get_skill_categories()
    {
        return $this->db->select('skill_category')
            ->distinct()
            ->where('skill_category IS NOT NULL')
            ->get('tbl_recruitment_skills')
            ->result_array();
    }

    // ==================== JOB SKILLS MAPPING ====================

    public function get_job_skills($job_circular_id)
    {
        $this->db->select('tbl_job_skills.*, tbl_recruitment_skills.skill_name, tbl_recruitment_skills.skill_category');
        $this->db->from('tbl_job_skills');
        $this->db->join('tbl_recruitment_skills', 'tbl_recruitment_skills.skill_id = tbl_job_skills.skill_id', 'left');
        $this->db->where('tbl_job_skills.job_circular_id', $job_circular_id);
        $this->db->order_by('tbl_job_skills.is_mandatory', 'DESC');
        $this->db->order_by('tbl_recruitment_skills.skill_name', 'ASC');
        return $this->db->get()->result();
    }

    public function save_job_skills($job_circular_id, $skills)
    {
        // Delete existing mappings
        $this->db->where('job_circular_id', $job_circular_id);
        $this->db->delete('tbl_job_skills');

        // Insert new mappings
        if (!empty($skills)) {
            $data = [];
            foreach ($skills as $skill) {
                $data[] = [
                    'job_circular_id' => $job_circular_id,
                    'skill_id' => $skill['skill_id'],
                    'is_mandatory' => isset($skill['is_mandatory']) ? $skill['is_mandatory'] : 1
                ];
            }
            $this->db->insert_batch('tbl_job_skills', $data);
        }
    }

    // ==================== ATS SCORING ====================

    public function calculate_ats_score($job_circular_id, $resume_text)
    {
        $this->load->library('ats_parser');
        $required_skills = $this->get_job_skills($job_circular_id);

        if (empty($required_skills) || empty($resume_text)) {
            return [
                'ats_score' => 0.00,
                'matched_skills' => [],
                'missing_skills' => [],
                'skill_match_details' => [],
                'resume_text' => $resume_text
            ];
        }

        return $this->ats_parser->analyze_resume($resume_text, $required_skills);
    }

    public function update_application_ats($job_appliactions_id, $ats_data)
    {
        $update_data = [
            'ats_score' => $ats_data['ats_score'],
            'matched_skills' => json_encode($ats_data['matched_skills']),
            'missing_skills' => json_encode($ats_data['missing_skills']),
            'resume_text' => $ats_data['resume_text'],
            'skill_match_details' => json_encode($ats_data['skill_match_details'])
        ];
        $this->db->where('job_appliactions_id', $job_appliactions_id);
        $this->db->update('tbl_job_appliactions', $update_data);
    }

    // ==================== APPLICATIONS WITH ATS ====================

    public function get_applications_with_ats($job_circular_id = null)
    {
        $this->db->select('tbl_job_appliactions.*, tbl_job_circular.job_title');
        $this->db->from('tbl_job_appliactions');
        $this->db->join('tbl_job_circular', 'tbl_job_circular.job_circular_id = tbl_job_appliactions.job_circular_id', 'left');

        if ($job_circular_id !== null) {
            $this->db->where('tbl_job_appliactions.job_circular_id', $job_circular_id);
        }

        $this->db->order_by('tbl_job_appliactions.ats_score', 'DESC');
        $this->db->order_by('tbl_job_appliactions.apply_date', 'DESC');

        return $this->db->get()->result();
    }

    public function get_application_detail($job_appliactions_id)
    {
        $this->db->select('tbl_job_appliactions.*, tbl_job_circular.*');
        $this->db->from('tbl_job_appliactions');
        $this->db->join('tbl_job_circular', 'tbl_job_circular.job_circular_id = tbl_job_appliactions.job_circular_id', 'left');
        $this->db->where('tbl_job_appliactions.job_appliactions_id', $job_appliactions_id);
        return $this->db->get()->row();
    }

    // ==================== INTERVIEW MANAGEMENT ====================

    public function get_interviews($filters = [])
    {
        $this->db->select('tbl_interviews.*, tbl_job_circular.job_title, tbl_job_appliactions.name as candidate_name, tbl_job_appliactions.email as candidate_email');
        $this->db->from('tbl_interviews');
        $this->db->join('tbl_job_circular', 'tbl_job_circular.job_circular_id = tbl_interviews.job_circular_id', 'left');
        $this->db->join('tbl_job_appliactions', 'tbl_job_appliactions.job_appliactions_id = tbl_interviews.job_appliactions_id', 'left');

        if (!empty($filters['job_circular_id'])) {
            $this->db->where('tbl_interviews.job_circular_id', $filters['job_circular_id']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('tbl_interviews.status', $filters['status']);
        }
        if (!empty($filters['interview_type'])) {
            $this->db->where('tbl_interviews.interview_type', $filters['interview_type']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('tbl_interviews.interview_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('tbl_interviews.interview_date <=', $filters['date_to']);
        }

        $this->db->order_by('tbl_interviews.interview_date', 'ASC');
        $this->db->order_by('tbl_interviews.interview_time', 'ASC');

        return $this->db->get()->result();
    }

    public function get_interview_by_id($interview_id)
    {
        $this->db->select('tbl_interviews.*, tbl_job_circular.job_title, tbl_job_appliactions.name as candidate_name, tbl_job_appliactions.email as candidate_email, tbl_job_appliactions.mobile as candidate_mobile');
        $this->db->from('tbl_interviews');
        $this->db->join('tbl_job_circular', 'tbl_job_circular.job_circular_id = tbl_interviews.job_circular_id', 'left');
        $this->db->join('tbl_job_appliactions', 'tbl_job_appliactions.job_appliactions_id = tbl_interviews.job_appliactions_id', 'left');
        $this->db->where('tbl_interviews.interview_id', $interview_id);
        return $this->db->get()->row();
    }

    public function get_interviews_for_application($job_appliactions_id)
    {
        $this->db->where('job_appliactions_id', $job_appliactions_id);
        $this->db->order_by('interview_date', 'DESC');
        return $this->db->get('tbl_interviews')->result();
    }

    public function save_interview($data, $id = null)
    {
        if ($id) {
            $this->db->where('interview_id', $id);
            $this->db->update('tbl_interviews', $data);
            return $id;
        } else {
            $this->db->insert('tbl_interviews', $data);
            return $this->db->insert_id();
        }
    }

    public function update_interview_status($interview_id, $status, $feedback = null, $rating = null)
    {
        $data = ['status' => $status];
        if ($feedback !== null) $data['feedback'] = $feedback;
        if ($rating !== null) $data['rating'] = $rating;
        $this->db->where('interview_id', $interview_id);
        $this->db->update('tbl_interviews', $data);
    }

    public function mark_interview_email_sent($interview_id)
    {
        $this->db->where('interview_id', $interview_id);
        $this->db->update('tbl_interviews', [
            'email_sent' => 1,
            'email_sent_at' => date('Y-m-d H:i:s')
        ]);
    }

    // ==================== OFFER LETTERS ====================

    public function get_offers($filters = [])
    {
        $this->db->select('tbl_offer_letters.*, tbl_job_circular.job_title, tbl_job_appliactions.name as candidate_name, tbl_job_appliactions.email as candidate_email');
        $this->db->from('tbl_offer_letters');
        $this->db->join('tbl_job_circular', 'tbl_job_circular.job_circular_id = tbl_offer_letters.job_circular_id', 'left');
        $this->db->join('tbl_job_appliactions', 'tbl_job_appliactions.job_appliactions_id = tbl_offer_letters.job_appliactions_id', 'left');

        if (!empty($filters['job_circular_id'])) {
            $this->db->where('tbl_offer_letters.job_circular_id', $filters['job_circular_id']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('tbl_offer_letters.status', $filters['status']);
        }

        $this->db->order_by('tbl_offer_letters.created_at', 'DESC');

        return $this->db->get()->result();
    }

    public function get_offer_by_id($offer_id)
    {
        $this->db->select('tbl_offer_letters.*, tbl_job_circular.*, tbl_job_appliactions.name as candidate_name, tbl_job_appliactions.email as candidate_email, tbl_job_appliactions.mobile as candidate_mobile');
        $this->db->from('tbl_offer_letters');
        $this->db->join('tbl_job_circular', 'tbl_job_circular.job_circular_id = tbl_offer_letters.job_circular_id', 'left');
        $this->db->join('tbl_job_appliactions', 'tbl_job_appliactions.job_appliactions_id = tbl_offer_letters.job_appliactions_id', 'left');
        $this->db->where('tbl_offer_letters.offer_id', $offer_id);
        return $this->db->get()->row();
    }

    public function get_offers_for_application($job_appliactions_id)
    {
        $this->db->where('job_appliactions_id', $job_appliactions_id);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('tbl_offer_letters')->result();
    }

    public function save_offer($data, $id = null)
    {
        if ($id) {
            $this->db->where('offer_id', $id);
            $this->db->update('tbl_offer_letters', $data);
            return $id;
        } else {
            $this->db->insert('tbl_offer_letters', $data);
            return $this->db->insert_id();
        }
    }

    public function update_offer_status($offer_id, $status)
    {
        $data = ['status' => $status];
        if ($status == 'sent') {
            $data['sent_at'] = date('Y-m-d H:i:s');
        }
        if (in_array($status, ['accepted', 'declined'])) {
            $data['responded_at'] = date('Y-m-d H:i:s');
        }
        $this->db->where('offer_id', $offer_id);
        $this->db->update('tbl_offer_letters', $data);
    }

    // ==================== OFFER TEMPLATES ====================

    public function get_offer_templates($active_only = true)
    {
        $this->db->from('tbl_offer_templates');
        if ($active_only) {
            $this->db->where('status', 'active');
        }
        $this->db->order_by('is_default', 'DESC');
        $this->db->order_by('template_name', 'ASC');
        return $this->db->get()->result();
    }

    public function get_offer_template_by_id($template_id)
    {
        return $this->db->where('template_id', $template_id)->get('tbl_offer_templates')->row();
    }

    public function get_default_offer_template()
    {
        return $this->db->where('is_default', 1)->where('status', 'active')->get('tbl_offer_templates')->row();
    }

    public function save_offer_template($data, $id = null)
    {
        if ($id) {
            $this->db->where('template_id', $id);
            $this->db->update('tbl_offer_templates', $data);
            return $id;
        } else {
            $this->db->insert('tbl_offer_templates', $data);
            return $this->db->insert_id();
        }
    }

    public function delete_offer_template($template_id)
    {
        $this->db->where('template_id', $template_id);
        $this->db->delete('tbl_offer_templates');
    }

    // ==================== RECALCULATE ATS FOR ALL APPLICATIONS ====================

    public function recalculate_all_ats_scores($job_circular_id)
    {
        $applications = $this->db->where('job_circular_id', $job_circular_id)
            ->where('resume_text IS NOT NULL')
            ->where('resume_text !=', '')
            ->get('tbl_job_appliactions')
            ->result();

        $count = 0;
        foreach ($applications as $app) {
            $ats_data = $this->calculate_ats_score($job_circular_id, $app->resume_text);
            $this->update_application_ats($app->job_appliactions_id, $ats_data);
            $count++;
        }
        return $count;
    }
}
