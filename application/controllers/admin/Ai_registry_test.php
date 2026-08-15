<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ai_registry_test extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->load->library('Ai_tools');
        $t = $this->ai_tools;
        $out = array();

        $all = $t->tools();
        $names = array();
        $dupes = array();
        foreach ($all as $def) {
            if (isset($names[$def['name']])) {
                $dupes[] = $def['name'];
            }
            $names[$def['name']] = true;
        }
        $out['total_tools'] = count($all);
        $out['duplicate_names'] = $dupes;

        $out['prompt_default'] = count($t->prompt_tools(''));
        $out['prompt_payroll'] = count($t->prompt_tools('admin > payroll'));
        $out['prompt_accounting'] = count($t->prompt_tools('admin > accounting'));
        $out['prompt_server'] = count($t->prompt_tools('admin > server_management'));

        $reg = $this->_registry_reflect($t);
        $out['registry_count'] = count($reg);
        $out['read_only_tables'] = array_keys(array_filter($reg, function ($e) { return !empty($e['read_only']); }));
        $out['sample_payroll'] = isset($reg['tbl_employee_payroll']) ? $reg['tbl_employee_payroll'] : null;

        $hits = $t->search_registry('salary');
        $out['search_salary'] = array_map(function ($h) { return $h['module']; }, $hits);
        $hits2 = $t->search_registry('accounting');
        $out['search_accounting'] = array_map(function ($h) { return $h['module']; }, $hits2);

        $out['schema_employee_payroll'] = $t->get_tool_schema('create_employee_payroll');
        $out['schema_search_registry'] = $t->get_tool_schema('search_registry');
        $out['schema_get_tool_schema'] = $t->get_tool_schema('get_tool_schema');
        $out['schema_unknown'] = $t->get_tool_schema('create_nonexistent_xyz');

        // Unrestricted check via a real list call
        $out['list_check'] = $t->execute('list_employee_payrolls', array('search' => ''));

        $prompt = $this->_build_prompt_like($t, 'admin > payroll');
        $out['prompt_chars_payroll'] = strlen($prompt);
        $out['prompt_est_tokens_payroll'] = (int) ceil(strlen($prompt) / 4);
        $prompt2 = $this->_build_prompt_like($t, '');
        $out['prompt_chars_default'] = strlen($prompt2);
        $out['prompt_est_tokens_default'] = (int) ceil(strlen($prompt2) / 4);

        // Part B dedicated tools
        $out['schema_create_journal'] = !empty($t->get_tool_schema('create_journal_entry')) ? 'ok' : 'MISSING';
        $out['schema_create_salary_payment'] = !empty($t->get_tool_schema('create_salary_payment')) ? 'ok' : 'MISSING';
        $out['schema_set_config'] = !empty($t->get_tool_schema('set_config')) ? 'ok' : 'MISSING';
        $out['schema_create_backup'] = !empty($t->get_tool_schema('create_backup')) ? 'ok' : 'MISSING';

        $out['set_config_exec'] = $t->execute('set_config', array('key' => 'ai_test_setting', 'value' => 'hello'));
        $out['get_config_exec'] = $t->execute('get_config', array('key' => 'ai_test_setting'));
        $this->db->where('config_key', 'ai_test_setting')->delete('tbl_config');

        $out['journal_validation'] = $t->execute('create_journal_entry', array('date' => '2026-08-15', 'items' => array(
            array('chart_of_account_id' => 1, 'debit' => 100, 'credit' => 0),
            array('chart_of_account_id' => 2, 'debit' => 0, 'credit' => 50),
        )));
        $journal_real = $t->execute('create_journal_entry', array('date' => '2026-08-15', 'reference_no' => 'AI-TEST-JE', 'items' => array(
            array('account_name' => 'Accounts Receivable', 'debit' => 120.50, 'credit' => 0, 'description' => 'test debit line'),
            array('chart_of_account_id' => 3, 'debit' => 0, 'credit' => 120.50, 'description' => 'test credit line'),
        )));
        $out['journal_real'] = $journal_real;
        if (!empty($journal_real['data']['journal_id'])) {
            $jid = (int) $journal_real['data']['journal_id'];
            $out['journal_items_count'] = (int) $this->db->where('journal_id', $jid)->count_all_results('tbl_journal_items');
            $this->db->where('journal_id', $jid)->delete('tbl_journal_items');
            $this->db->where('journal_id', $jid)->delete('tbl_journals');
        }
        $out['salary_validation'] = $t->execute('create_salary_payment', array('user_id' => 999999, 'payment_month' => '2026-08'));
        if (!empty($out['salary_validation']['data']['salary_payment_id'])) {
            $sid = (int) $out['salary_validation']['data']['salary_payment_id'];
            $this->db->where('salary_payment_id', $sid)->delete('tbl_salary_payment_allowance');
            $this->db->where('salary_payment_id', $sid)->delete('tbl_salary_payment_deduction');
            $this->db->where('salary_payment_id', $sid)->delete('tbl_salary_payment');
        }
        $this->db->where('value1', '%999999%')->or_where('activity', 'AI-TEST')->delete('tbl_activities');
        $out['backup_validation'] = $t->execute('delete_backup', array('file' => '../../evil'));
        $backup_real = $t->execute('create_backup', array());
        $out['backup_real'] = $backup_real;
        if (!empty($backup_real['data']['file'])) {
            $out['backup_delete_real'] = $t->execute('delete_backup', array('file' => $backup_real['data']['file']));
        }

        echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function gate()
    {
        $this->load->model('ai_model');
        $this->load->library('Ai_tools');
        $t = $this->ai_tools;
        $out = array();

        // 1. needs_confirmation flags
        $out['needs_confirmation_update_project'] = $t->needs_confirmation('update_project') ? 'yes' : 'NO';
        $out['needs_confirmation_delete_project'] = $t->needs_confirmation('delete_project') ? 'yes' : 'NO';
        $out['needs_confirmation_assign_user']    = $t->needs_confirmation('assign_user') ? 'yes' : 'NO';
        $out['needs_confirmation_list_projects']  = $t->needs_confirmation('list_projects') ? 'yes' : 'NO';

        // 2. describe
        $out['describe_update_project'] = $t->describe('update_project', array('project_id' => 3, 'project_name' => 'TIC Limited'));
        $out['describe_delete_project'] = $t->describe('delete_project', array('project_id' => 3));

        // 3. pending action round trip
        $sid = $this->ai_model->create_session(1, 'admin > dashboard');
        $this->ai_model->set_pending_action($sid, array('tool' => 'update_project', 'args' => array('project_id' => 3, 'project_name' => 'TIC Limited'), 'summary' => 'update the project "TIC Limited"'));

        $p = $this->ai_model->get_pending_action($sid);
        $out['pending_stored'] = !empty($p) ? $p['tool'] : 'NO';

        // "no" path
        $this->ai_model->clear_pending_action($sid);
        $out['cleared_after_no'] = empty($this->ai_model->get_pending_action($sid)) ? 'yes' : 'NO';

        // "yes" path: set again and confirm resolution
        $this->ai_model->set_pending_action($sid, array('tool' => 'update_project', 'args' => array('project_id' => 3, 'project_name' => 'TIC Limited'), 'summary' => 'update the project "TIC Limited"'));
        $resolved = $this->_resolve_pending_like($t, $this->ai_model, $sid, 'yes');
        $out['resolve_yes'] = $resolved;
        $out['pending_after_yes'] = empty($this->ai_model->get_pending_action($sid)) ? 'cleared' : 'STILL_SET';

        $this->ai_model->delete_session($sid);

        echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    private function _resolve_pending_like($t, $ai_model, $sid, $answer)
    {
        $pending = $ai_model->get_pending_action($sid);
        if (empty($pending)) {
            return 'no pending';
        }
        if ($answer === 'yes' || $answer === 'y' || $answer === 'confirm' || $answer === 'proceed') {
            $result = $t->execute($pending['tool'], $pending['args']);
            $ai_model->clear_pending_action($sid);
            return array('executed' => $result['success'], 'message' => $result['message']);
        }
        $ai_model->clear_pending_action($sid);
        return array('executed' => false, 'cancelled' => true);
    }

    public function audit()
    {
        $db_tables = array();
        $rows = $this->db->query("SELECT TABLE_NAME AS t FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE 'tbl%'")->result();
        foreach ($rows as $r) {
            $db_tables[] = $r->t;
        }

        $this->load->library('Ai_tools');
        $t = $this->ai_tools;
        $disc = $t->debug_discovered();
        $probe_disc = array();
        foreach (array('tbl_project', 'tbl_task', 'tbl_priorities', 'tbl_contracts_types') as $p) {
            $probe_disc[$p] = isset($disc[$p]) ? json_encode($disc[$p], JSON_UNESCAPED_SLASHES) : 'NOT_DISCOVERED';
        }
        $ct_tools = array();
        if (isset($disc['tbl_contracts_types'])) {
            foreach ($t->debug_entry_tools($disc['tbl_contracts_types']) as $d) {
                $ct_tools[] = $d['name'];
            }
        }
        $reg = array_fill_keys($t->debug_registry_keys(), true);

        $config = $this->config->item('ai_registry_exclude');
        $excluded = is_array($config) ? array_fill_keys($config, true) : array();
        $config = $this->config->item('ai_registry_read_only');
        $read_only = is_array($config) ? array_fill_keys($config, true) : array();

        $uncovered = array();
        $manual_covered = array();
        $all_tool_names = array();
        foreach ($t->debug_tools() as $def) {
            $all_tool_names[strtolower($def['name'])] = true;
        }
        foreach ($db_tables as $tbl) {
            if (isset($reg[$tbl]) || isset($excluded[$tbl]) || isset($read_only[$tbl])) {
                continue;
            }
            if (isset($disc[$tbl])) {
                $names = array();
                foreach ($t->debug_entry_tools($disc[$tbl]) as $d) {
                    $names[] = strtolower($d['name']);
                }
                $all_manual = true;
                foreach ($names as $n) {
                    if (!isset($all_tool_names[$n])) {
                        $all_manual = false;
                    }
                }
                if ($all_manual && !empty($names)) {
                    $manual_covered[] = $tbl;
                    continue;
                }
            }
            $uncovered[] = $tbl;
        }

        echo json_encode(array(
            'db_table_count' => count($db_tables),
            'registry_keys'  => count($reg),
            'excluded_count' => count($excluded),
            'read_only_count'=> count($read_only),
            'uncovered'      => $uncovered,
            'manual_covered' => $manual_covered,
            'total_tools'    => $t->count_tools(),
            'duplicate_names'=> $t->debug_duplicates(),
            'probe'          => $probe_result,
            'discovered_probe' => $probe_disc,
            'contracts_types_tools' => $ct_tools,
        ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    private function _build_prompt_like($t, $ctx)
    {
        $lines = array();
        foreach ($t->prompt_tools($ctx) as $tool) {
            $lines[] = '- ' . $tool['name'] . ': ' . $tool['description'];
            $lines[] = '  parameters: ' . json_encode($tool['parameters']);
        }
        return implode("\n", $lines);
    }

    private function _registry_reflect($t)
    {
        // No public accessor - mirror registry via search_registry over full alphabet
        $out = array();
        $r = new ReflectionClass($t);
        $m = $r->getMethod('_registry');
        $m->setAccessible(true);
        return $m->invoke($t);
    }
}
