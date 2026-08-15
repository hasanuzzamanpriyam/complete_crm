<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Ai_tools
 *
 * Backend tool execution engine for the AI Assistant.
 *
 * The gateway asks the model to emit tool calls in a text format, the gateway
 * intercepts them and hands every call to execute(). Each handler runs the real
 * CRM backend logic (the same tables / permission format used by the native
 * controllers) and returns a result that is fed back to the model so it can
 * confirm the outcome in plain natural language.
 *
 * The engine is schema-driven: every CRM module that is safe to drive is
 * declared in _manifest() (table, primary key, permission id, editable fields,
 * hooks) and the list/get/create/update/delete tools are generated from it.
 * In addition, the Universal Dynamic Tool Registry auto-discovers every
 * remaining writable table from the live database schema (information_schema),
 * applies metadata overrides from application/config/ai_registry.php, and
 * merges the two sources so every table is reachable. The registry is
 * unrestricted: the AI Engine always acts with Super-admin privileges
 * regardless of the logged-in user's role.
 *
 * Handlers prefixed _tool_<name> take precedence over the generic engine.
 * Because the full tool list is large, the prompt embeds the manual tools plus
 * the tools of the active module only, and the model discovers anything else
 * with the search_registry / get_tool_schema meta-tools.
 */
class Ai_tools {

    private $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    /**
     * Definitions of every tool the model is allowed to call.
     *
     * @return array
     */
    public function tools()
    {
        return array_merge($this->_manual_tools(), $this->_manifest_tools());
    }

    /**
     * Compact set of tools to embed inline in the system prompt.
     *
     * Manual tools + meta-tools are always included; the tools of the module
     * the user is currently working in are included too. Everything else is
     * discovered on demand through search_registry / get_tool_schema so the
     * prompt stays far below the free-tier token budget.
     *
     * @param  string $module_context e.g. "admin > payroll" or "chat"
     * @return array
     */
    public function prompt_tools($module_context = '')
    {
        $tools = $this->_manual_tools();

        $module = $this->_active_module_tables($module_context);
        if (empty($module)) {
            return $tools;
        }

        $registry = $this->_registry();
        foreach ($registry as $table => $entry) {
            if (in_array($table, $module, true)) {
                $tools = array_merge($tools, $this->_entry_tools($entry));
            }
        }

        return $tools;
    }

    /**
     * Search the full registry for modules matching a topic.
     *
     * Returns the matching entries together with their complete tool
     * definitions (name, description, parameters) so the model can act
     * immediately without a second round trip.
     *
     * @param  string $topic
     * @return array
     */
    public function search_registry($topic)
    {
        $topic = trim((string) $topic);
        if ($topic === '') {
            return array();
        }

        $tokens = $this->_search_tokens($topic);
        $registry = $this->_registry();

        $scores = array();
        foreach ($registry as $table => $entry) {
            $haystack = $this->_entry_haystack($entry);
            $score = 0;
            foreach ($tokens as $t) {
                if ($this->_haystack_matches($haystack, $t)) {
                    $score++;
                }
            }
            if ($score > 0) {
                $scores[] = array('table' => $table, 'score' => $score, 'entry' => $entry);
            }
        }

        usort($scores, function ($a, $b) {
            if ($b['score'] !== $a['score']) {
                return $b['score'] - $a['score'];
            }
            return strcmp($a['table'], $b['table']);
        });

        $out = array();
        foreach (array_slice($scores, 0, 8) as $hit) {
            $entry = $hit['entry'];
            $out[] = array(
                'module'   => $hit['table'],
                'singular' => $entry['singular'],
                'plural'   => $entry['plural'],
                'tools'    => $this->_entry_tools($entry),
            );
        }

        return $out;
    }

    /**
     * Return the full definition of a single tool by name.
     *
     * @param  string $tool
     * @return array|null
     */
    public function get_tool_schema($tool)
    {
        $tool = strtolower(trim((string) $tool));
        if ($tool === '') {
            return null;
        }

        foreach ($this->_manual_tools() as $def) {
            if (strtolower($def['name']) === $tool) {
                return $def;
            }
        }

        foreach ($this->_registry() as $entry) {
            foreach ($this->_entry_tools($entry) as $def) {
                if (strtolower($def['name']) === $tool) {
                    return $def;
                }
            }
        }

        return null;
    }

    /**
     * Return the keys of every table currently in the registry (debug).
     *
     * @return array
     */
    public function debug_registry_keys()
    {
        return array_keys($this->_registry());
    }

    /**
     * Return the raw discovered schema entries (debug).
     *
     * @return array
     */
    public function debug_discovered()
    {
        return $this->_discover_schema();
    }

    /**
     * Return the tools generated for a single registry entry (debug).
     *
     * @param  array $entry
     * @return array
     */
    public function debug_entry_tools($entry)
    {
        return $this->_entry_tools($entry);
    }

    /**
     * Return all tool definitions (debug).
     *
     * @return array
     */
    public function debug_tools()
    {
        return $this->tools();
    }

    /**
     * Count total unique tools exposed by the registry (debug).
     *
     * @return int
     */
    public function count_tools()
    {
        return count($this->tools());
    }

    /**
     * Return any tool names that are defined more than once (debug).
     *
     * @return array
     */
    public function debug_duplicates()
    {
        $names = array();
        foreach ($this->tools() as $def) {
            $names[] = strtolower($def['name']);
        }
        $dupes = array();
        $seen  = array();
        foreach ($names as $n) {
            if (isset($seen[$n])) {
                $dupes[$n] = true;
            }
            $seen[$n] = true;
        }
        return array_keys($dupes);
    }

    /**
     * Execute a single tool call.
     *
     * @param  string $tool
     * @param  array  $args
     * @return array  array('success' => bool, 'tool' => string, 'message' => string, 'data' => mixed)
     */
    public function execute($tool, $args = array())
    {
        $tool   = strtolower(trim((string) $tool));
        $args   = is_array($args) ? $args : array();
        $method = '_tool_' . $tool;

        if (method_exists($this, $method)) {
            try {
                $out = $this->{$method}($args);
            } catch (Exception $e) {
                $out = array('success' => false, 'message' => $e->getMessage(), 'data' => null);
            }
            $out['tool'] = $tool;
            return $out;
        }

        $found = $this->_manifest_lookup($tool);
        if (empty($found)) {
            return array('success' => false, 'tool' => $tool, 'message' => 'Unknown tool: ' . $tool, 'data' => null);
        }

        try {
            switch ($found['op']) {
                case 'list':
                    $out = $this->_gen_list($found['entry'], $args);
                    break;
                case 'get':
                    $out = $this->_gen_get($found['entry'], $args);
                    break;
                case 'create':
                    $out = $this->_gen_create($found['entry'], $args);
                    break;
                case 'update':
                    $out = $this->_gen_update($found['entry'], $args);
                    break;
                case 'delete':
                    $out = $this->_gen_delete($found['entry'], $args);
                    break;
                default:
                    $out = array('success' => false, 'message' => 'Unknown tool: ' . $tool, 'data' => null);
            }
        } catch (Exception $e) {
            $out = array('success' => false, 'message' => $e->getMessage(), 'data' => null);
        }
        $out['tool'] = $tool;
        return $out;
    }

    /**
     * Whether a tool call must be confirmed by the user before it is executed.
     * Destructive / state changing tools are always confirmed.
     *
     * @param  string $tool
     * @return bool
     */
    public function needs_confirmation($tool)
    {
        $tool = strtolower(trim((string) $tool));
        if (preg_match('/^(delete|update)_/', $tool)) {
            return true;
        }
        if ($tool === 'assign_user') {
            return true;
        }
        return false;
    }

    /**
     * Build a short human description of an intended tool action, used for the
     * confirmation question shown to the user.
     *
     * @param  string $tool
     * @param  array  $args
     * @return string
     */
    public function describe($tool, $args)
    {
        $tool = strtolower(trim((string) $tool));
        $args = is_array($args) ? $args : array();

        $found = $this->_manifest_lookup($tool);
        if (!empty($found)) {
            $entry  = $found['entry'];
            $label  = $this->_describe_label($entry, $args);
            $opword = $found['op'];
            if ($opword === 'delete') {
                return 'delete the ' . $entry['singular'] . ' "' . $label . '"';
            }
            return $opword . ' the ' . $entry['singular'] . ' "' . $label . '"';
        }

        if ($tool === 'assign_user') {
            return 'assign user "' . (isset($args['user']) ? $args['user'] : '?') . '" to '
                . (isset($args['entity']) ? $args['entity'] : '?') . ' '
                . (isset($args['id']) ? $args['id'] : '?');
        }

        return $tool;
    }

    /* ---------------------------------------------------------------------
     * Manual tools (defined by hand, take precedence over the manifest)
     * ---------------------------------------------------------------- */

    private function _manual_tools()
    {
        return array(
            array(
                'name'        => 'list_users',
                'description' => 'List active staff users in the CRM. Call this first when you need to find the user_id or the exact full name of a user (for example before creating or assigning something).',
                'parameters'  => array(
                    'type'       => 'object',
                    'properties' => array(
                        'query' => array('type' => 'string', 'description' => 'Optional search term matched against the full name, username or email'),
                    ),
                ),
            ),
            array(
                'name'        => 'list_projects',
                'description' => 'List recent projects, optionally filtered by status or a search term.',
                'parameters'  => array(
                    'type'       => 'object',
                    'properties' => array(
                        'status' => array('type' => 'string', 'description' => 'One of: in_progress, started, completed, canceled'),
                        'search' => array('type' => 'string', 'description' => 'Optional search term matched against the project name'),
                    ),
                ),
            ),
            array(
                'name'        => 'get_project',
                'description' => 'Get a single project by id or by name.',
                'parameters'  => array(
                    'type'       => 'object',
                    'properties' => array(
                        'project_id'   => array('type' => 'integer'),
                        'project_name' => array('type' => 'string'),
                    ),
                ),
            ),
            array(
                'name'        => 'create_project',
                'description' => 'Create a new project in the CRM. Returns the new project_id.',
                'parameters'  => array(
                    'type'       => 'object',
                    'properties' => array(
                        'project_name'  => array('type' => 'string', 'description' => 'Name of the project (required)'),
                        'description'   => array('type' => 'string'),
                        'client_id'     => array('type' => 'integer'),
                        'start_date'    => array('type' => 'string', 'description' => 'Date in YYYY-MM-DD format'),
                        'end_date'      => array('type' => 'string', 'description' => 'Date in YYYY-MM-DD format'),
                        'project_status'=> array('type' => 'string', 'description' => 'One of: in_progress, started, completed, canceled'),
                        'project_cost'  => array('type' => 'number'),
                        'hourly_rate'   => array('type' => 'number'),
                        'billing_type'  => array('type' => 'string', 'description' => 'One of: hourly, fixed, hourly_project_cost'),
                        'assign_to'     => array('type' => 'array', 'items' => array('type' => 'string'), 'description' => 'Full names or user_ids of the users to assign'),
                    ),
                    'required'    => array('project_name'),
                ),
            ),
            array(
                'name'        => 'update_project',
                'description' => 'Update fields of an existing project.',
                'parameters'  => array(
                    'type'       => 'object',
                    'properties' => array(
                        'project_id'    => array('type' => 'integer', 'description' => 'Required'),
                        'project_name'  => array('type' => 'string'),
                        'project_status'=> array('type' => 'string', 'description' => 'One of: in_progress, started, completed, canceled'),
                        'progress'      => array('type' => 'integer', 'description' => 'Progress percentage 0-100'),
                        'start_date'    => array('type' => 'string', 'description' => 'YYYY-MM-DD'),
                        'end_date'      => array('type' => 'string', 'description' => 'YYYY-MM-DD'),
                        'project_cost'  => array('type' => 'number'),
                        'description'   => array('type' => 'string'),
                    ),
                    'required'    => array('project_id'),
                ),
            ),
            array(
                'name'        => 'delete_project',
                'description' => 'Delete an existing project and everything linked to it (tasks, bugs, comments, files, milestones). The system will ask the user to confirm first.',
                'parameters'  => array(
                    'type'       => 'object',
                    'properties' => array(
                        'project_id'   => array('type' => 'integer'),
                        'project_name' => array('type' => 'string'),
                    ),
                ),
            ),
            array(
                'name'        => 'list_tasks',
                'description' => 'List recent tasks, optionally filtered by status, project_id or a search term.',
                'parameters'  => array(
                    'type'       => 'object',
                    'properties' => array(
                        'status'     => array('type' => 'string', 'description' => 'One of: not_started, in_progress, completed, pending, blocked, on_hold'),
                        'project_id' => array('type' => 'integer'),
                        'search'     => array('type' => 'string'),
                    ),
                ),
            ),
            array(
                'name'        => 'get_task',
                'description' => 'Get a single task by id or by name.',
                'parameters'  => array(
                    'type'       => 'object',
                    'properties' => array(
                        'task_id'   => array('type' => 'integer'),
                        'task_name' => array('type' => 'string'),
                    ),
                ),
            ),
            array(
                'name'        => 'create_task',
                'description' => 'Create a new task in the CRM. Returns the new task_id.',
                'parameters'  => array(
                    'type'       => 'object',
                    'properties' => array(
                        'task_name'        => array('type' => 'string', 'description' => 'Task name (required)'),
                        'task_description' => array('type' => 'string'),
                        'project_id'       => array('type' => 'integer', 'description' => 'Optional parent project id'),
                        'task_status'      => array('type' => 'string', 'description' => 'One of: not_started, in_progress, completed, pending, blocked, on_hold'),
                        'priority'         => array('type' => 'string', 'description' => 'One of: Low, Medium, High'),
                        'task_start_date'  => array('type' => 'string', 'description' => 'YYYY-MM-DD'),
                        'due_date'         => array('type' => 'string', 'description' => 'YYYY-MM-DD'),
                        'task_hour'        => array('type' => 'string', 'description' => 'Estimated hours, for example 8:00'),
                        'assign_to'        => array('type' => 'array', 'items' => array('type' => 'string'), 'description' => 'Full names or user_ids of the users to assign'),
                    ),
                    'required'    => array('task_name'),
                ),
            ),
            array(
                'name'        => 'update_task',
                'description' => 'Update any field of an existing task (name, status, progress, priority, dates, description, project).',
                'parameters'  => array(
                    'type'       => 'object',
                    'properties' => array(
                        'task_id'          => array('type' => 'integer', 'description' => 'Required'),
                        'task_name'        => array('type' => 'string'),
                        'task_description' => array('type' => 'string'),
                        'task_status'      => array('type' => 'string', 'description' => 'One of: not_started, in_progress, completed, pending, blocked, on_hold'),
                        'task_progress'    => array('type' => 'integer', 'description' => 'Progress percentage 0-100'),
                        'priority'         => array('type' => 'string', 'description' => 'One of: Low, Medium, High'),
                        'task_start_date'  => array('type' => 'string', 'description' => 'YYYY-MM-DD'),
                        'due_date'         => array('type' => 'string', 'description' => 'YYYY-MM-DD'),
                        'project_id'       => array('type' => 'integer'),
                    ),
                    'required'    => array('task_id'),
                ),
            ),
            array(
                'name'        => 'update_task_status',
                'description' => 'Update the status and/or progress of a task.',
                'parameters'  => array(
                    'type'       => 'object',
                    'properties' => array(
                        'task_id'       => array('type' => 'integer', 'description' => 'Required'),
                        'task_status'   => array('type' => 'string', 'description' => 'One of: not_started, in_progress, completed, pending, blocked, on_hold'),
                        'task_progress' => array('type' => 'integer', 'description' => 'Progress percentage 0-100'),
                    ),
                    'required'    => array('task_id'),
                ),
            ),
            array(
                'name'        => 'delete_task',
                'description' => 'Delete an existing task and everything linked to it (sub-tasks, comments, attachments, timers). The system will ask the user to confirm first.',
                'parameters'  => array(
                    'type'       => 'object',
                    'properties' => array(
                        'task_id'   => array('type' => 'integer'),
                        'task_name' => array('type' => 'string'),
                    ),
                ),
            ),
            array(
                'name'        => 'assign_user',
                'description' => 'Assign a user (by full name or user_id) to an existing project or task.',
                'parameters'  => array(
                    'type'       => 'object',
                    'properties' => array(
                        'entity' => array('type' => 'string', 'enum' => array('project', 'task'), 'description' => 'Required: project or task'),
                        'id'     => array('type' => 'integer', 'description' => 'project_id or task_id (required)'),
                        'user'   => array('type' => 'string', 'description' => 'User full name or user_id (required)'),
                    ),
                    'required'    => array('entity', 'id', 'user'),
                ),
            ),
            array(
                'name'        => 'search_registry',
                'description' => 'Search the CRM tool registry for a topic (e.g. "salary", "accounting", "invoices", "leads"). Returns matching modules with their full tool definitions (list_/get_/create_/update_/delete_ tool names, descriptions and parameter schemas). Call this whenever the user asks about anything whose tools are not already listed below.',
                'parameters'  => array(
                    'type'       => 'object',
                    'properties' => array(
                        'topic' => array('type' => 'string', 'description' => 'The topic or module to search for (required)'),
                    ),
                    'required'    => array('topic'),
                ),
            ),
            array(
                'name'        => 'get_tool_schema',
                'description' => 'Return the full JSON parameter schema of a single tool by name (e.g. create_client, update_invoice, delete_employee_payroll). Use when you already know the exact tool name and need its arguments.',
                'parameters'  => array(
                    'type'       => 'object',
                    'properties' => array(
                        'tool' => array('type' => 'string', 'description' => 'The exact tool name (required)'),
                    ),
                    'required'    => array('tool'),
                ),
            ),
            array(
                'name'        => 'create_journal_entry',
                'description' => 'Create a double-entry accounting journal entry with balanced debit/credit line items. Required: a list of items, each with chart_of_account_id (or account name), debit amount or credit amount, and an optional description. The total debit must equal the total credit. Returns the new journal_id.',
                'parameters'  => array(
                    'type'       => 'object',
                    'properties' => array(
                        'date'         => array('type' => 'string', 'description' => 'Entry date YYYY-MM-DD'),
                        'reference_no' => array('type' => 'string', 'description' => 'Optional reference number'),
                        'notes'        => array('type' => 'string', 'description' => 'Optional notes'),
                        'items'        => array(
                            'type'  => 'array',
                            'items' => array(
                                'type'       => 'object',
                                'properties' => array(
                                    'chart_of_account_id' => array('type' => 'integer'),
                                    'account_name'        => array('type' => 'string', 'description' => 'Account name instead of chart_of_account_id'),
                                    'debit'               => array('type' => 'number'),
                                    'credit'              => array('type' => 'number'),
                                    'description'         => array('type' => 'string'),
                                ),
                                'required' => array('debit', 'credit'),
                            ),
                            'description' => 'Required. At least two items; total debit must equal total credit.',
                        ),
                    ),
                    'required'    => array('date', 'items'),
                ),
            ),
            array(
                'name'        => 'create_salary_payment',
                'description' => 'Record a salary payment for a staff user for a payment month. Optional allowances and deductions are stored as child rows. Returns the new salary_payment_id.',
                'parameters'  => array(
                    'type'       => 'object',
                    'properties' => array(
                        'user_id'        => array('type' => 'integer', 'description' => 'User id (required). Use list_users first if unknown.'),
                        'payment_month'  => array('type' => 'string', 'description' => 'Month, e.g. 2026-08'),
                        'payment_type'   => array('type' => 'string', 'description' => 'e.g. Monthly salary'),
                        'paid_date'      => array('type' => 'string', 'description' => 'YYYY-MM-DD'),
                        'deduct_from'    => array('type' => 'integer', 'description' => 'Deduction account id'),
                        'fine_deduction' => array('type' => 'string'),
                        'comments'       => array('type' => 'string'),
                        'allowances'     => array(
                            'type'  => 'array',
                            'items' => array(
                                'type'       => 'object',
                                'properties' => array(
                                    'label' => array('type' => 'string'),
                                    'value' => array('type' => 'string'),
                                ),
                                'required' => array('label'),
                            ),
                        ),
                        'deductions'     => array(
                            'type'  => 'array',
                            'items' => array(
                                'type'       => 'object',
                                'properties' => array(
                                    'label' => array('type' => 'string'),
                                    'value' => array('type' => 'string'),
                                ),
                                'required' => array('label'),
                            ),
                        ),
                    ),
                    'required'    => array('user_id', 'payment_month'),
                ),
            ),
            array(
                'name'        => 'set_config',
                'description' => 'Set a system setting value by its config key (key/value pair stored in the config table). Values are stored as text; use get_config or ask the user for the exact key.',
                'parameters'  => array(
                    'type'       => 'object',
                    'properties' => array(
                        'key'   => array('type' => 'string', 'description' => 'Config key (required)'),
                        'value' => array('type' => 'string', 'description' => 'Value to store (required)'),
                    ),
                    'required'    => array('key', 'value'),
                ),
            ),
            array(
                'name'        => 'get_config',
                'description' => 'Get the current value of a system setting by its config key.',
                'parameters'  => array(
                    'type'       => 'object',
                    'properties' => array(
                        'key' => array('type' => 'string', 'description' => 'Config key (required)'),
                    ),
                    'required'    => array('key'),
                ),
            ),
            array(
                'name'        => 'create_backup',
                'description' => 'Create a full database backup ZIP file in uploads/backup and log the activity. Returns the backup filename.',
                'parameters'  => array(
                    'type'       => 'object',
                    'properties' => array(),
                ),
            ),
            array(
                'name'        => 'delete_backup',
                'description' => 'Delete a database backup ZIP file from uploads/backup. Provide the filename exactly as returned by create_backup or listed on the backup page.',
                'parameters'  => array(
                    'type'       => 'object',
                    'properties' => array(
                        'file' => array('type' => 'string', 'description' => 'Backup filename, e.g. BD-backup_2026-08-15_10-30.zip (required)'),
                    ),
                    'required'    => array('file'),
                ),
            ),
        );
    }

    private function _tool_list_users($args)
    {
        $query = trim(isset($args['query']) ? $args['query'] : '');

        $this->ci->db->select('tbl_users.user_id, tbl_users.username, tbl_users.email, tbl_account_details.fullname, tbl_designations.designations');
        $this->ci->db->from('tbl_users');
        $this->ci->db->join('tbl_account_details', 'tbl_account_details.user_id = tbl_users.user_id', 'left');
        $this->ci->db->join('tbl_designations', 'tbl_designations.designations_id = tbl_account_details.designations_id', 'left');
        $this->ci->db->where('tbl_users.role_id !=', 2);
        $this->ci->db->where('tbl_users.activated', 1);
        $this->ci->db->where('tbl_users.banned', 0);

        if ($query !== '') {
            $this->ci->db->group_start();
            $this->ci->db->like('tbl_account_details.fullname', $query);
            $this->ci->db->or_like('tbl_users.username', $query);
            $this->ci->db->or_like('tbl_users.email', $query);
            $this->ci->db->group_end();
        }

        $rows = $this->ci->db->limit(50)->get()->result();

        $users = array();
        foreach ($rows as $row) {
            $users[] = array(
                'user_id'   => (int) $row->user_id,
                'fullname'  => $row->fullname,
                'username'  => $row->username,
                'email'     => $row->email,
                'designation' => $row->designations,
            );
        }

        if (empty($users)) {
            return array('success' => true, 'message' => 'No staff users found.', 'data' => array());
        }

        return array('success' => true, 'message' => 'Found ' . count($users) . ' staff user(s).', 'data' => $users);
    }

    private function _tool_list_projects($args)
    {
        $status = trim(isset($args['status']) ? $args['status'] : '');
        $search = trim(isset($args['search']) ? $args['search'] : '');

        $this->ci->db->select('project_id, project_name, project_status, progress, client_id, start_date, end_date, created_by, created_time, permission');
        $this->ci->db->from('tbl_project');
        if ($status !== '') {
            $this->ci->db->where('project_status', $status);
        }
        if ($search !== '') {
            $this->ci->db->like('project_name', $search);
        }
        $rows = $this->ci->db->order_by('project_id', 'DESC')->limit(30)->get()->result();

        $projects = array();
        foreach ($rows as $row) {
            if (!$this->_row_allowed($row)) {
                continue;
            }
            $projects[] = array(
                'project_id'    => (int) $row->project_id,
                'project_name'  => $row->project_name,
                'project_status'=> $row->project_status,
                'progress'      => $row->progress,
                'start_date'    => $row->start_date,
                'end_date'      => $row->end_date,
                'created_by'    => (int) $row->created_by,
            );
        }

        if (empty($projects)) {
            return array('success' => true, 'message' => 'No projects found.', 'data' => array());
        }

        return array('success' => true, 'message' => 'Found ' . count($projects) . ' project(s).', 'data' => $projects);
    }

    private function _tool_get_project($args)
    {
        $id   = (int) (isset($args['project_id']) ? $args['project_id'] : 0);
        $name = trim(isset($args['project_name']) ? $args['project_name'] : '');

        if ($id > 0) {
            $row = $this->ci->db->where('project_id', $id)->get('tbl_project')->row();
        } elseif ($name !== '') {
            $row = $this->ci->db->like('project_name', $name)->order_by('project_id', 'DESC')->get('tbl_project')->row();
        } else {
            return array('success' => false, 'message' => 'Provide either project_id or project_name.', 'data' => null);
        }

        if (empty($row)) {
            return array('success' => false, 'message' => 'Project not found.', 'data' => null);
        }
        if (!$this->_row_allowed($row)) {
            return array('success' => false, 'message' => 'You do not have permission to view this project.', 'data' => null);
        }

        return array('success' => true, 'message' => 'Project found.', 'data' => $this->_project_array($row));
    }

    private function _tool_create_project($args)
    {
        $project_name = trim(isset($args['project_name']) ? $args['project_name'] : '');
        if ($project_name === '') {
            return array('success' => false, 'message' => 'A project name is required.', 'data' => null);
        }

        $statuses = array('in_progress', 'started', 'completed', 'canceled');
        $status   = trim(isset($args['project_status']) ? $args['project_status'] : 'in_progress');
        if (!in_array($status, $statuses, true)) {
            $status = 'in_progress';
        }

        $progress = (int) (isset($args['progress']) ? $args['progress'] : 0);
        if ($status === 'completed') {
            $progress = 100;
        }
        if ($progress >= 100) {
            $status = 'completed';
        }

        $start_date = trim(isset($args['start_date']) ? $args['start_date'] : date('Y-m-d'));
        $end_date   = trim(isset($args['end_date']) ? $args['end_date'] : date('Y-m-d', strtotime('+30 days')));
        if (!strtotime($start_date)) {
            $start_date = date('Y-m-d');
        }
        if (!strtotime($end_date)) {
            $end_date = date('Y-m-d', strtotime('+30 days'));
        }

        $assignees = $this->_resolve_users(isset($args['assign_to']) ? $args['assign_to'] : null);

        $data = array(
            'created_by'         => $this->_uid(),
            'project_name'       => $project_name,
            'progress'           => (string) $progress,
            'calculate_progress' => 'true',
            'start_date'         => $start_date,
            'end_date'           => $end_date,
            'project_status'     => $status,
            'description'        => (string) (isset($args['description']) ? $args['description'] : ''),
            'notify_client'      => 'No',
            'project_cost'       => (float) (isset($args['project_cost']) ? $args['project_cost'] : 0),
            'hourly_rate'        => (string) (isset($args['hourly_rate']) ? $args['hourly_rate'] : '0'),
            'billing_type'       => (string) (isset($args['billing_type']) ? $args['billing_type'] : 'fixed'),
            'demo_url'           => '',
            'permission'         => $this->_permission_from_users($assignees, array('edit', 'view')),
        );
        if (isset($args['client_id']) && (int) $args['client_id'] > 0) {
            $data['client_id'] = (int) $args['client_id'];
        }

        $this->ci->db->insert('tbl_project', $data);
        $project_id = (int) $this->ci->db->insert_id();
        if (empty($project_id)) {
            return array('success' => false, 'message' => 'Could not create the project. Database insert failed.', 'data' => null);
        }

        $this->_log_activity('projects', $project_id, 'activity_save_project', 'fa-folder-open-o',
            'admin/projects/project_details/' . $project_id, $project_name);

        $assigned = $this->_assignee_label($assignees);

        return array(
            'success' => true,
            'message' => 'Project "' . $project_name . '" created with id ' . $project_id . '.',
            'data'    => array('project_id' => $project_id, 'project_name' => $project_name, 'project_status' => $status, 'assigned' => $assigned),
        );
    }

    private function _tool_update_project($args)
    {
        $id = (int) (isset($args['project_id']) ? $args['project_id'] : 0);
        if ($id <= 0) {
            return array('success' => false, 'message' => 'A project_id is required.', 'data' => null);
        }
        $exists = $this->ci->db->where('project_id', $id)->get('tbl_project')->row();
        if (empty($exists)) {
            return array('success' => false, 'message' => 'Project ' . $id . ' not found.', 'data' => null);
        }

        $data = array();
        $map  = array('project_name', 'description', 'progress', 'start_date', 'end_date', 'project_status', 'project_cost');

        $statuses = array('in_progress', 'started', 'completed', 'canceled');
        foreach ($map as $field) {
            if (!array_key_exists($field, $args) || $args[$field] === null || $args[$field] === '') {
                continue;
            }
            if ($field === 'project_status') {
                $value = trim($args[$field]);
                if (!in_array($value, $statuses, true)) {
                    continue;
                }
                $data[$field] = $value;
                if ($value === 'completed') {
                    $data['progress'] = '100';
                }
            } elseif ($field === 'progress') {
                $data['progress'] = (string) max(0, min(100, (int) $args[$field]));
                if ((int) $data['progress'] >= 100) {
                    $data['project_status'] = 'completed';
                }
            } elseif ($field === 'project_cost') {
                $data[$field] = (float) $args[$field];
            } else {
                $data[$field] = $args[$field];
            }
        }

        if (empty($data)) {
            return array('success' => false, 'message' => 'Nothing to update.', 'data' => null);
        }

        $this->ci->db->where('project_id', $id)->update('tbl_project', $data);
        $this->_log_activity('projects', $id, 'activity_update_project', 'fa-folder-open-o',
            'admin/projects/project_details/' . $id, $exists->project_name);

        $row = $this->ci->db->where('project_id', $id)->get('tbl_project')->row();

        return array('success' => true, 'message' => 'Project ' . $id . ' updated.', 'data' => $this->_project_array($row));
    }

    private function _tool_delete_project($args)
    {
        $id   = (int) (isset($args['project_id']) ? $args['project_id'] : 0);
        $name = trim(isset($args['project_name']) ? $args['project_name'] : '');

        if ($id > 0) {
            $row = $this->ci->db->where('project_id', $id)->get('tbl_project')->row();
        } elseif ($name !== '') {
            $row = $this->ci->db->like('project_name', $name)->order_by('project_id', 'DESC')->get('tbl_project')->row();
        } else {
            return array('success' => false, 'message' => 'Provide either project_id or project_name.', 'data' => null);
        }

        if (empty($row)) {
            return array('success' => false, 'message' => 'Project not found.', 'data' => null);
        }

        if (!$this->_can_menu('57', 'deleted')) {
            return array('success' => false, 'message' => 'You do not have permission to delete projects.', 'data' => null);
        }

        $this->_delete_project_cascade((int) $row->project_id, $row->project_name);

        return array(
            'success' => true,
            'message' => 'Project "' . $row->project_name . '" (id ' . $row->project_id . ') and all linked data were deleted.',
            'data'    => array('project_id' => (int) $row->project_id, 'deleted' => true),
        );
    }

    private function _tool_list_tasks($args)
    {
        $status = trim(isset($args['status']) ? $args['status'] : '');
        $search = trim(isset($args['search']) ? $args['search'] : '');
        $project_id = (int) (isset($args['project_id']) ? $args['project_id'] : 0);

        $this->ci->db->select('task_id, task_name, project_id, task_status, task_progress, due_date, priority, permission');
        $this->ci->db->from('tbl_task');
        if ($status !== '') {
            $this->ci->db->where('task_status', $status);
        }
        if ($search !== '') {
            $this->ci->db->like('task_name', $search);
        }
        if ($project_id > 0) {
            $this->ci->db->where('project_id', $project_id);
        }
        $rows = $this->ci->db->order_by('task_id', 'DESC')->limit(40)->get()->result();

        $tasks = array();
        foreach ($rows as $row) {
            if (!$this->_row_allowed($row)) {
                continue;
            }
            $tasks[] = array(
                'task_id'       => (int) $row->task_id,
                'task_name'     => $row->task_name,
                'project_id'    => $row->project_id !== null ? (int) $row->project_id : null,
                'task_status'   => $row->task_status,
                'task_progress' => (int) $row->task_progress,
                'due_date'      => $row->due_date,
                'priority'      => $row->priority,
            );
        }

        if (empty($tasks)) {
            return array('success' => true, 'message' => 'No tasks found.', 'data' => array());
        }

        return array('success' => true, 'message' => 'Found ' . count($tasks) . ' task(s).', 'data' => $tasks);
    }

    private function _tool_get_task($args)
    {
        $id   = (int) (isset($args['task_id']) ? $args['task_id'] : 0);
        $name = trim(isset($args['task_name']) ? $args['task_name'] : '');

        if ($id > 0) {
            $row = $this->ci->db->where('task_id', $id)->get('tbl_task')->row();
        } elseif ($name !== '') {
            $row = $this->ci->db->like('task_name', $name)->order_by('task_id', 'DESC')->get('tbl_task')->row();
        } else {
            return array('success' => false, 'message' => 'Provide either task_id or task_name.', 'data' => null);
        }

        if (empty($row)) {
            return array('success' => false, 'message' => 'Task not found.', 'data' => null);
        }
        if (!$this->_row_allowed($row)) {
            return array('success' => false, 'message' => 'You do not have permission to view this task.', 'data' => null);
        }

        return array('success' => true, 'message' => 'Task found.', 'data' => $this->_task_array($row));
    }

    private function _tool_create_task($args)
    {
        $task_name = trim(isset($args['task_name']) ? $args['task_name'] : '');
        if ($task_name === '') {
            return array('success' => false, 'message' => 'A task name is required.', 'data' => null);
        }

        $statuses = array('not_started', 'in_progress', 'completed', 'pending', 'blocked', 'on_hold');
        $status   = trim(isset($args['task_status']) ? $args['task_status'] : 'in_progress');
        if (!in_array($status, $statuses, true)) {
            $status = 'in_progress';
        }

        $progress = (int) (isset($args['task_progress']) ? $args['task_progress'] : 0);
        if ($status === 'completed') {
            $progress = 100;
        }
        if ($progress >= 100) {
            $status = 'completed';
        }

        $assignees = $this->_resolve_users(isset($args['assign_to']) ? $args['assign_to'] : null);

        $data = array(
            'task_name'        => $task_name,
            'task_description' => (string) (isset($args['task_description']) ? $args['task_description'] : ''),
            'task_status'      => $status,
            'task_progress'    => $progress,
            'task_hour'        => (string) (isset($args['task_hour']) ? $args['task_hour'] : '0:00'),
            'billable'         => 'No',
            'payment_type'     => 'monthly',
            'hourly_rate'      => '0',
            'priority'         => (string) (isset($args['priority']) ? $args['priority'] : 'Medium'),
            'client_visible'   => 'No',
            'created_by'       => $this->_uid(),
            'permission'       => $this->_permission_from_users($assignees, array('edit', 'view')),
        );
        if (isset($args['project_id']) && (int) $args['project_id'] > 0) {
            $data['project_id'] = (int) $args['project_id'];
        }
        if (isset($args['task_start_date']) && strtotime($args['task_start_date'])) {
            $data['task_start_date'] = date('Y-m-d', strtotime($args['task_start_date']));
        }
        if (isset($args['due_date']) && strtotime($args['due_date'])) {
            $data['due_date'] = date('Y-m-d', strtotime($args['due_date']));
        }

        $this->ci->db->insert('tbl_task', $data);
        $task_id = (int) $this->ci->db->insert_id();
        if (empty($task_id)) {
            return array('success' => false, 'message' => 'Could not create the task. Database insert failed.', 'data' => null);
        }

        $this->ci->db->where('task_id', $task_id)->update('tbl_task', array('index_no' => $task_id));

        $this->_log_activity('tasks', $task_id, 'activity_new_task', 'fa-tasks',
            'admin/tasks/details/' . $task_id, $task_name);

        $assigned = $this->_assignee_label($assignees);

        return array(
            'success' => true,
            'message' => 'Task "' . $task_name . '" created with id ' . $task_id . '.',
            'data'    => array('task_id' => $task_id, 'task_name' => $task_name, 'task_status' => $status, 'assigned' => $assigned),
        );
    }

    private function _tool_update_task($args)
    {
        $id = (int) (isset($args['task_id']) ? $args['task_id'] : 0);
        if ($id <= 0) {
            return array('success' => false, 'message' => 'A task_id is required.', 'data' => null);
        }
        $exists = $this->ci->db->where('task_id', $id)->get('tbl_task')->row();
        if (empty($exists)) {
            return array('success' => false, 'message' => 'Task ' . $id . ' not found.', 'data' => null);
        }

        $data = array();
        $statuses = array('not_started', 'in_progress', 'completed', 'pending', 'blocked', 'on_hold');

        if (array_key_exists('task_status', $args) && $args['task_status'] !== null && $args['task_status'] !== '') {
            $value = trim($args['task_status']);
            if (in_array($value, $statuses, true)) {
                $data['task_status'] = $value;
                if ($value === 'completed') {
                    $data['task_progress'] = 100;
                }
            }
        }
        if (array_key_exists('task_progress', $args) && $args['task_progress'] !== null && $args['task_progress'] !== '') {
            $data['task_progress'] = max(0, min(100, (int) $args['task_progress']));
            if ((int) $data['task_progress'] >= 100) {
                $data['task_status'] = 'completed';
            }
        }
        foreach (array('task_name', 'task_description', 'priority') as $field) {
            if (array_key_exists($field, $args) && $args[$field] !== null && $args[$field] !== '') {
                $data[$field] = $args[$field];
            }
        }
        foreach (array('task_start_date', 'due_date') as $field) {
            if (array_key_exists($field, $args) && $args[$field] !== null && $args[$field] !== '' && strtotime($args[$field])) {
                $data[$field] = date('Y-m-d', strtotime($args[$field]));
            }
        }
        if (array_key_exists('project_id', $args) && $args['project_id'] !== null && $args['project_id'] !== '') {
            $data['project_id'] = (int) $args['project_id'];
        }

        if (empty($data)) {
            return array('success' => false, 'message' => 'Nothing to update.', 'data' => null);
        }

        $this->ci->db->where('task_id', $id)->update('tbl_task', $data);
        $this->_log_activity('tasks', $id, 'activity_update_task', 'fa-tasks',
            'admin/tasks/details/' . $id, $exists->task_name);

        $row = $this->ci->db->where('task_id', $id)->get('tbl_task')->row();

        return array('success' => true, 'message' => 'Task ' . $id . ' updated.', 'data' => $this->_task_array($row));
    }

    private function _tool_update_task_status($args)
    {
        return $this->_tool_update_task($args);
    }

    private function _tool_delete_task($args)
    {
        $id   = (int) (isset($args['task_id']) ? $args['task_id'] : 0);
        $name = trim(isset($args['task_name']) ? $args['task_name'] : '');

        if ($id > 0) {
            $row = $this->ci->db->where('task_id', $id)->get('tbl_task')->row();
        } elseif ($name !== '') {
            $row = $this->ci->db->like('task_name', $name)->order_by('task_id', 'DESC')->get('tbl_task')->row();
        } else {
            return array('success' => false, 'message' => 'Provide either task_id or task_name.', 'data' => null);
        }

        if (empty($row)) {
            return array('success' => false, 'message' => 'Task not found.', 'data' => null);
        }

        if (!$this->_can_menu('54', 'deleted')) {
            return array('success' => false, 'message' => 'You do not have permission to delete tasks.', 'data' => null);
        }

        $this->_delete_task_cascade((int) $row->task_id, $row->task_name);

        return array(
            'success' => true,
            'message' => 'Task "' . $row->task_name . '" (id ' . $row->task_id . ') and all linked data were deleted.',
            'data'    => array('task_id' => (int) $row->task_id, 'deleted' => true),
        );
    }

    private function _tool_assign_user($args)
    {
        $entity = strtolower(trim(isset($args['entity']) ? $args['entity'] : ''));
        $id     = (int) (isset($args['id']) ? $args['id'] : 0);
        $user   = trim(isset($args['user']) ? $args['user'] : '');

        if (!in_array($entity, array('project', 'task'), true)) {
            return array('success' => false, 'message' => 'Entity must be "project" or "task".', 'data' => null);
        }
        if ($id <= 0) {
            return array('success' => false, 'message' => 'An id is required.', 'data' => null);
        }
        if ($user === '') {
            return array('success' => false, 'message' => 'A user full name or user_id is required.', 'data' => null);
        }

        $user_id = $this->_resolve_user($user);
        if (empty($user_id)) {
            return array('success' => false, 'message' => 'User "' . $user . '" was not found. Use list_users to find a valid user.', 'data' => null);
        }

        if ($entity === 'project') {
            $table   = 'tbl_project';
            $key     = 'project_id';
            $icon    = 'fa-folder-open-o';
            $link    = 'admin/projects/project_details/' . $id;
            $activity= 'activity_update_project';
            $name_field = 'project_name';
        } else {
            $table   = 'tbl_task';
            $key     = 'task_id';
            $icon    = 'fa-tasks';
            $link    = 'admin/tasks/details/' . $id;
            $activity= 'activity_update_task';
            $name_field = 'task_name';
        }

        $row = $this->ci->db->where($key, $id)->get($table)->row();
        if (empty($row)) {
            return array('success' => false, 'message' => ucfirst($entity) . ' ' . $id . ' not found.', 'data' => null);
        }

        $permission = $this->_permission_add($row->permission, array($user_id), array('edit', 'view'));
        $this->ci->db->where($key, $id)->update($table, array('permission' => $permission));

        $this->_log_activity($entity, $id, $activity, $icon, $link, $row->{$name_field});

        $fullname = $this->_fullname($user_id);

        return array(
            'success' => true,
            'message' => 'User ' . $fullname . ' (id ' . $user_id . ') assigned to ' . $entity . ' ' . $id . '.',
            'data'    => array('entity' => $entity, 'id' => $id, 'user_id' => $user_id, 'fullname' => $fullname),
        );
    }

    private function _tool_search_registry($args)
    {
        $topic = trim(isset($args['topic']) ? $args['topic'] : '');
        if ($topic === '') {
            return array('success' => false, 'message' => 'Missing required parameter: topic.', 'data' => null);
        }

        $hits = $this->search_registry($topic);
        if (empty($hits)) {
            return array('success' => true, 'message' => 'No registry modules found for "' . $topic . '".', 'data' => array());
        }

        $lines = array();
        foreach ($hits as $hit) {
            $names = array();
            foreach ($hit['tools'] as $def) {
                $names[] = $def['name'];
            }
            $lines[] = $hit['module'] . ' (' . $hit['singular'] . '): ' . implode(', ', $names);
        }

        return array(
            'success' => true,
            'message' => 'Found ' . count($hits) . ' modules matching "' . $topic . '".',
            'data'    => array('modules' => $hits, 'summary' => $lines),
        );
    }

    private function _tool_get_tool_schema($args)
    {
        $tool = trim(isset($args['tool']) ? $args['tool'] : '');
        if ($tool === '') {
            return array('success' => false, 'message' => 'Missing required parameter: tool.', 'data' => null);
        }

        $def = $this->get_tool_schema($tool);
        if (empty($def)) {
            return array('success' => false, 'message' => 'Unknown tool: ' . $tool . '.', 'data' => null);
        }

        return array('success' => true, 'message' => 'Schema for ' . $def['name'] . '.', 'data' => $def);
    }

    private function _tool_create_journal_entry($args)
    {
        $items = isset($args['items']) && is_array($args['items']) ? $args['items'] : array();
        if (empty($items)) {
            return array('success' => false, 'message' => 'Missing required parameter: items.', 'data' => null);
        }

        $total_debit  = 0;
        $total_credit = 0;
        $rows = array();
        foreach ($items as $item) {
            $account_id = isset($item['chart_of_account_id']) ? (int) $item['chart_of_account_id'] : 0;
            if ($account_id <= 0 && !empty($item['account_name'])) {
                $account = $this->ci->db->like('name', trim($item['account_name']))
                    ->or_like('code', trim($item['account_name']))
                    ->order_by('chart_of_account_id', 'DESC')
                    ->get('tbl_chart_of_accounts')->row();
                if (empty($account)) {
                    return array('success' => false, 'message' => 'Account "' . $item['account_name'] . '" was not found. Use list tools or search_registry to find a valid chart of account.', 'data' => null);
                }
                $account_id = (int) $account->chart_of_account_id;
            }
            if ($account_id <= 0) {
                return array('success' => false, 'message' => 'Each journal item needs chart_of_account_id or account_name.', 'data' => null);
            }
            $debit  = (float) (isset($item['debit']) ? $item['debit'] : 0);
            $credit = (float) (isset($item['credit']) ? $item['credit'] : 0);
            if ($debit <= 0 && $credit <= 0) {
                return array('success' => false, 'message' => 'Each journal item needs a debit or credit amount greater than zero.', 'data' => null);
            }
            $total_debit  += $debit;
            $total_credit += $credit;
            $rows[] = array(
                'journal_id'          => 0,
                'chart_of_account_id' => $account_id,
                'debit'               => $debit,
                'credit'              => $credit,
                'description'         => isset($item['description']) ? trim((string) $item['description']) : '',
            );
        }

        if (abs($total_debit - $total_credit) > 0.001) {
            return array('success' => false, 'message' => 'Total debit (' . number_format($total_debit, 2) . ') must equal total credit (' . number_format($total_credit, 2) . ').', 'data' => null);
        }
        if ($total_debit <= 0) {
            return array('success' => false, 'message' => 'Debit and credit totals must be greater than zero.', 'data' => null);
        }

        $data = array(
            'date'        => $this->_coerce(isset($args['date']) ? $args['date'] : date('Y-m-d'), array('type' => 'date')),
            'reference_no'=> isset($args['reference_no']) ? trim((string) $args['reference_no']) : $this->_ref_code('JV'),
            'notes'       => isset($args['notes']) ? trim((string) $args['notes']) : '',
            'total_debit' => $total_debit,
            'total_credit'=> $total_credit,
            'total_amount'=> $total_debit,
            'status'      => 'open',
            'created_by'  => (int) $this->ci->session->userdata('user_id'),
        );

        $this->ci->db->insert('tbl_journals', $data);
        $journal_id = (int) $this->ci->db->insert_id();
        if (empty($journal_id)) {
            return array('success' => false, 'message' => 'Could not create the journal entry. Database insert failed. ' . $this->_db_error(), 'data' => null);
        }

        foreach ($rows as &$row) {
            $row['journal_id'] = $journal_id;
        }
        unset($row);
        $this->ci->db->insert_batch('tbl_journal_items', $rows);

        $this->_log_activity('journal', $journal_id, 'activity_added_journal_entry', 'fa-circle-o', 'admin/accounting/view_journal_entry/' . $journal_id, $data['reference_no'] . ' - ' . $total_debit . ' - ' . $total_credit . ' - ' . $data['date']);

        return array(
            'success' => true,
            'message' => 'Journal entry "' . $data['reference_no'] . '" created with journal_id ' . $journal_id . ' (' . count($rows) . ' line items, debit ' . number_format($total_debit, 2) . ' = credit ' . number_format($total_credit, 2) . ').',
            'data'    => array('journal_id' => $journal_id, 'reference_no' => $data['reference_no'], 'line_items' => count($rows)),
        );
    }

    private function _tool_create_salary_payment($args)
    {
        $user_id = (int) (isset($args['user_id']) ? $args['user_id'] : 0);
        $month   = trim(isset($args['payment_month']) ? $args['payment_month'] : '');
        if ($user_id <= 0 || $month === '') {
            return array('success' => false, 'message' => 'Missing required parameters: user_id and payment_month.', 'data' => null);
        }

        $data = array(
            'user_id'        => $user_id,
            'payment_month'  => $month,
            'payment_type'   => isset($args['payment_type']) ? trim((string) $args['payment_type']) : 'Monthly Salary',
            'paid_date'      => isset($args['paid_date']) ? $this->_coerce($args['paid_date'], array('type' => 'date')) : date('Y-m-d'),
            'deduct_from'    => isset($args['deduct_from']) ? (int) $args['deduct_from'] : 0,
            'fine_deduction' => isset($args['fine_deduction']) ? trim((string) $args['fine_deduction']) : '',
            'comments'       => isset($args['comments']) ? trim((string) $args['comments']) : '',
        );

        $this->ci->db->insert('tbl_salary_payment', $data);
        $payment_id = (int) $this->ci->db->insert_id();
        if (empty($payment_id)) {
            return array('success' => false, 'message' => 'Could not create the salary payment. Database insert failed. ' . $this->_db_error(), 'data' => null);
        }

        $added = array('allowances' => 0, 'deductions' => 0);
        foreach (array('allowances', 'deductions') as $kind) {
            if (empty($args[$kind]) || !is_array($args[$kind])) {
                continue;
            }
            $table  = ($kind === 'allowances') ? 'tbl_salary_payment_allowance' : 'tbl_salary_payment_deduction';
            $label  = ($kind === 'allowances') ? 'salary_payment_allowance_label' : 'salary_payment_deduction_label';
            $value  = ($kind === 'allowances') ? 'salary_payment_allowance_value' : 'salary_payment_deduction_value';
            $id_col = ($kind === 'allowances') ? 'salary_payment_allowance_id' : 'salary_payment_deduction_id';
            $batch  = array();
            foreach ($args[$kind] as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $batch[] = array(
                    'salary_payment_id' => $payment_id,
                    $label => trim(isset($row['label']) ? (string) $row['label'] : ''),
                    $value => trim(isset($row['value']) ? (string) $row['value'] : ''),
                );
            }
            if (!empty($batch)) {
                $this->ci->db->insert_batch($table, $batch);
                $added[$kind] = count($batch);
            }
        }

        $this->_log_activity('salary_payment', $payment_id, 'activity_salary_payment_saved', 'fa-money', 'admin/payroll/salary_payment_details/' . $payment_id, 'User ' . $user_id . ' - ' . $month);

        return array(
            'success' => true,
            'message' => 'Salary payment for user ' . $user_id . ' (' . $month . ') recorded with salary_payment_id ' . $payment_id . ' (' . $added['allowances'] . ' allowances, ' . $added['deductions'] . ' deductions).',
            'data'    => array('salary_payment_id' => $payment_id, 'user_id' => $user_id, 'payment_month' => $month),
        );
    }

    private function _tool_set_config($args)
    {
        $key   = trim(isset($args['key']) ? $args['key'] : '');
        $value = isset($args['value']) ? (string) $args['value'] : '';
        if ($key === '') {
            return array('success' => false, 'message' => 'Missing required parameter: key.', 'data' => null);
        }

        $existing = $this->ci->db->where('config_key', $key)->get('tbl_config')->row();
        if (!empty($existing)) {
            $this->ci->db->where('config_key', $key)->update('tbl_config', array('value' => $value));
        } else {
            $this->ci->db->insert('tbl_config', array('config_key' => $key, 'value' => $value));
        }

        return array('success' => true, 'message' => 'Setting "' . $key . '" saved.', 'data' => array('key' => $key, 'value' => $value));
    }

    private function _tool_get_config($args)
    {
        $key = trim(isset($args['key']) ? $args['key'] : '');
        if ($key === '') {
            return array('success' => false, 'message' => 'Missing required parameter: key.', 'data' => null);
        }

        $row = $this->ci->db->where('config_key', $key)->get('tbl_config')->row();
        if (empty($row)) {
            return array('success' => false, 'message' => 'Setting "' . $key . '" was not found.', 'data' => null);
        }

        return array('success' => true, 'message' => 'Setting "' . $key . '".', 'data' => array('key' => $key, 'value' => $row->value));
    }

    private function _tool_create_backup($args)
    {
        if (!function_exists('write_file')) {
            $this->ci->load->helper('file');
        }
        $this->ci->load->dbutil();

        $filename = 'BD-backup_' . date('Y-m-d_H-i');
        $prefs    = array('format' => 'zip', 'filename' => $filename);
        $backup   = $this->ci->dbutil->backup($prefs);
        $dir      = FCPATH . 'uploads/backup';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!write_file($dir . '/' . $filename . '.zip', $backup)) {
            return array('success' => false, 'message' => 'Backup could not be written to uploads/backup.', 'data' => null);
        }

        $this->_log_activity('settings', (int) $this->ci->session->userdata('user_id'), 'activity_database_backup', 'fa-database', 'admin/settings/database_backup', $filename);

        return array('success' => true, 'message' => 'Database backup created: ' . $filename . '.zip', 'data' => array('file' => $filename . '.zip', 'path' => 'uploads/backup/' . $filename . '.zip'));
    }

    private function _tool_delete_backup($args)
    {
        $file = trim(isset($args['file']) ? $args['file'] : '');
        if ($file === '' || strpos($file, '/') !== false || strpos($file, '\\') !== false || strpos($file, '..') !== false) {
            return array('success' => false, 'message' => 'Invalid backup filename.', 'data' => null);
        }

        $path = FCPATH . 'uploads/backup/' . $file;
        if (!file_exists($path)) {
            return array('success' => false, 'message' => 'Backup file "' . $file . '" was not found in uploads/backup.', 'data' => null);
        }

        unlink($path);

        $this->_log_activity('settings', (int) $this->ci->session->userdata('user_id'), 'activity_backup_delete_success', 'fa-database', 'admin/settings/database_backup', $file);

        return array('success' => true, 'message' => 'Backup "' . $file . '" deleted.', 'data' => array('file' => $file));
    }

    /* ---------------------------------------------------------------------
     * Module manifest (schema-driven registry)
     *
     * Every entry describes one CRM module. Fields are keyed by the argument
     * name the model should use; 'col' is the real column name. The generic
     * engine turns each entry into list_/get_/create_/update_/delete_ tools.
     * ---------------------------------------------------------------- */

    private function _manifest()
    {
        return array(
            'client' => array(
                'singular'       => 'client',
                'plural'         => 'clients',
                'table'          => 'tbl_client',
                'primary_key'    => 'client_id',
                'label_column'   => 'name',
                'identify_arg'   => 'client_name',
                'permission_id'  => 4,
                'link'           => 'admin/client/manage_client/client_details/',
                'icon'           => 'fa-user',
                'activity_create'=> 'activity_added_new_company',
                'activity_update'=> 'activity_update_company',
                'activity_delete'=> 'activity_client_deleted',
                'search_columns' => array('name', 'email', 'phone'),
                'list_fields'    => array('name', 'email', 'phone', 'city'),
                'defaults'       => array('currency' => 'USD', 'language' => 'english', 'client_status' => 1, 'permission' => 'all'),
                'fields' => array(
                    'client_name'    => array('col' => 'name', 'type' => 'string', 'required' => true, 'description' => 'Company or contact name (required)'),
                    'email'          => array('col' => 'email', 'type' => 'email'),
                    'short_note'     => array('col' => 'short_note', 'type' => 'text'),
                    'website'        => array('col' => 'website', 'type' => 'string'),
                    'phone'          => array('col' => 'phone', 'type' => 'string'),
                    'mobile'         => array('col' => 'mobile', 'type' => 'string'),
                    'fax'            => array('col' => 'fax', 'type' => 'string'),
                    'address'        => array('col' => 'address', 'type' => 'text'),
                    'city'           => array('col' => 'city', 'type' => 'string'),
                    'zipcode'        => array('col' => 'zipcode', 'type' => 'string'),
                    'country'        => array('col' => 'country', 'type' => 'string'),
                    'vat'            => array('col' => 'vat', 'type' => 'string'),
                    'skype_id'       => array('col' => 'skype_id', 'type' => 'string'),
                    'linkedin'       => array('col' => 'linkedin', 'type' => 'string'),
                    'facebook'       => array('col' => 'facebook', 'type' => 'string'),
                    'twitter'        => array('col' => 'twitter', 'type' => 'string'),
                    'language'       => array('col' => 'language', 'type' => 'string'),
                    'currency'       => array('col' => 'currency', 'type' => 'string'),
                    'client_status'  => array('col' => 'client_status', 'type' => 'int', 'values' => array(1, 2), 'description' => '1 = active, 2 = inactive'),
                    'customer_group_id' => array('col' => 'customer_group_id', 'type' => 'int'),
                    'assign_to'      => array('col' => 'permission', 'type' => 'permission'),
                ),
            ),
            'invoice' => array(
                'singular'       => 'invoice',
                'plural'         => 'invoices',
                'table'          => 'tbl_invoices',
                'primary_key'    => 'invoices_id',
                'label_column'   => 'reference_no',
                'identify_arg'   => 'invoice_reference',
                'permission_id'  => 13,
                'link'           => 'admin/invoice/manage_invoice/invoice_details/',
                'icon'           => 'fa-file-text-o',
                'activity_create'=> 'activity_invoice_created',
                'activity_update'=> 'activity_invoice_updated',
                'activity_delete'=> 'activity_invoice_deleted',
                'search_columns' => array('reference_no'),
                'list_fields'    => array('reference_no', 'client_id', 'invoice_date', 'due_date', 'status', 'tax'),
                'defaults'       => array('permission' => 'all'),
                'delete_cascade' => array(
                    array('table' => 'tbl_items',       'where' => array('invoices_id' => 'invoices_id')),
                    array('table' => 'tbl_payments',    'where' => array('invoices_id' => 'invoices_id')),
                    array('table' => 'tbl_reminders',   'where' => array('module' => 'invoice', 'module_id' => 'invoices_id')),
                    array('table' => 'tbl_pinaction',   'where' => array('module_name' => 'invoice', 'module_id' => 'invoices_id')),
                    array('table' => 'tbl_credit_used', 'where' => array('invoices_id' => 'invoices_id')),
                ),
                'before_create'  => '_before_create_invoice',
                'after_create'   => '_after_create_invoice',
                'fields' => array(
                    'client_id'    => array('col' => 'client_id', 'type' => 'int', 'required' => true, 'resolve' => array('table' => 'tbl_client', 'key' => 'client_id', 'label' => 'name', 'name' => 'client'), 'description' => 'Client id or client name (required)'),
                    'project_id'   => array('col' => 'project_id', 'type' => 'int'),
                    'invoice_date' => array('col' => 'invoice_date', 'type' => 'date'),
                    'due_date'     => array('col' => 'due_date', 'type' => 'date'),
                    'status'       => array('col' => 'status', 'type' => 'enum', 'values' => array('Unpaid', 'Paid', 'draft', 'partially_paid', 'Cancelled'), 'description' => 'One of: Unpaid, Paid, draft, partially_paid, Cancelled'),
                    'currency'     => array('col' => 'currency', 'type' => 'string'),
                    'tax'          => array('col' => 'tax', 'type' => 'float'),
                    'notes'        => array('col' => 'notes', 'type' => 'text'),
                    'client_visible'=> array('col' => 'client_visible', 'type' => 'enum', 'values' => array('Yes', 'No')),
                    'discount_type'=> array('col' => 'discount_type', 'type' => 'enum', 'values' => array('none', 'before_tax', 'after_tax')),
                    'discount_percent' => array('col' => 'discount_percent', 'type' => 'int'),
                    'discount_total'   => array('col' => 'discount_total', 'type' => 'float'),
                    'adjustment'       => array('col' => 'adjustment', 'type' => 'float'),
                    'items'        => array('col' => 'items', 'type' => 'json', 'virtual' => true, 'description' => 'Array of line items, each with item_name, quantity, unit_cost, unit, item_desc'),
                    'assign_to'    => array('col' => 'permission', 'type' => 'permission'),
                ),
            ),
            'estimate' => array(
                'singular'       => 'estimate',
                'plural'         => 'estimates',
                'table'          => 'tbl_estimates',
                'primary_key'    => 'estimates_id',
                'label_column'   => 'reference_no',
                'identify_arg'   => 'estimate_reference',
                'permission_id'  => 14,
                'link'           => 'admin/estimates/estimate_details/',
                'icon'           => 'fa-line-chart',
                'activity_create'=> 'activity_estimate_created',
                'activity_update'=> 'activity_estimate_updated',
                'activity_delete'=> 'activity_estimate_deleted',
                'search_columns' => array('reference_no'),
                'list_fields'    => array('reference_no', 'client_id', 'estimate_date', 'due_date', 'status'),
                'defaults'       => array('permission' => 'all'),
                'delete_cascade' => array(
                    array('table' => 'tbl_estimate_items', 'where' => array('estimates_id' => 'estimates_id')),
                    array('table' => 'tbl_reminders',      'where' => array('module' => 'estimate', 'module_id' => 'estimates_id')),
                    array('table' => 'tbl_pinaction',      'where' => array('module_name' => 'estimates', 'module_id' => 'estimates_id')),
                ),
                'before_create'  => '_before_create_estimate',
                'after_create'   => '_after_create_estimate',
                'fields' => array(
                    'client_id'    => array('col' => 'client_id', 'type' => 'int', 'required' => true, 'resolve' => array('table' => 'tbl_client', 'key' => 'client_id', 'label' => 'name', 'name' => 'client'), 'description' => 'Client id or client name (required)'),
                    'project_id'   => array('col' => 'project_id', 'type' => 'int'),
                    'estimate_date'=> array('col' => 'estimate_date', 'type' => 'date'),
                    'due_date'     => array('col' => 'due_date', 'type' => 'date'),
                    'status'       => array('col' => 'status', 'type' => 'enum', 'values' => array('draft', 'sent', 'expired', 'declined', 'accepted', 'pending', 'cancelled')),
                    'currency'     => array('col' => 'currency', 'type' => 'string'),
                    'tax'          => array('col' => 'tax', 'type' => 'int'),
                    'notes'        => array('col' => 'notes', 'type' => 'text'),
                    'client_visible'=> array('col' => 'client_visible', 'type' => 'enum', 'values' => array('Yes', 'No')),
                    'discount_type'=> array('col' => 'discount_type', 'type' => 'enum', 'values' => array('none', 'before_tax', 'after_tax')),
                    'discount_percent' => array('col' => 'discount_percent', 'type' => 'int'),
                    'discount_total'   => array('col' => 'discount_total', 'type' => 'float'),
                    'adjustment'       => array('col' => 'adjustment', 'type' => 'float'),
                    'items'        => array('col' => 'items', 'type' => 'json', 'virtual' => true, 'description' => 'Array of line items, each with item_name, quantity, unit_cost, unit, item_desc'),
                    'assign_to'    => array('col' => 'permission', 'type' => 'permission'),
                ),
            ),
            'proposal' => array(
                'singular'       => 'proposal',
                'plural'         => 'proposals',
                'table'          => 'tbl_proposals',
                'primary_key'    => 'proposals_id',
                'label_column'   => 'reference_no',
                'identify_arg'   => 'proposal_reference',
                'permission_id'  => 140,
                'link'           => 'admin/proposals/proposal_details/',
                'icon'           => 'fa-hand-o-up',
                'activity_create'=> 'activity_proposal_created',
                'activity_update'=> 'activity_proposal_updated',
                'activity_delete'=> 'activity_proposal_deleted',
                'search_columns' => array('reference_no', 'subject'),
                'list_fields'    => array('reference_no', 'subject', 'module', 'module_id', 'proposal_date', 'status'),
                'defaults'       => array('permission' => 'all'),
                'delete_cascade' => array(
                    array('table' => 'tbl_proposals_items', 'where' => array('proposals_id' => 'proposals_id')),
                    array('table' => 'tbl_reminders',       'where' => array('module' => 'proposal', 'module_id' => 'proposals_id')),
                ),
                'before_create'  => '_before_create_proposal',
                'after_create'   => '_after_create_proposal',
                'fields' => array(
                    'subject'      => array('col' => 'subject', 'type' => 'string', 'required' => true, 'description' => 'Subject of the proposal (required)'),
                    'module'       => array('col' => 'module', 'type' => 'enum', 'values' => array('client', 'leads'), 'description' => 'client or leads'),
                    'client_id'    => array('col' => 'module_id', 'type' => 'int', 'resolve' => array('table' => 'tbl_client', 'key' => 'client_id', 'label' => 'name', 'name' => 'client'), 'description' => 'Sets module to client'),
                    'lead_id'      => array('col' => 'module_id', 'type' => 'int', 'resolve' => array('table' => 'tbl_leads', 'key' => 'leads_id', 'label' => 'lead_name', 'name' => 'lead'), 'description' => 'Sets module to leads'),
                    'proposal_date'=> array('col' => 'proposal_date', 'type' => 'date'),
                    'due_date'     => array('col' => 'due_date', 'type' => 'date'),
                    'status'       => array('col' => 'status', 'type' => 'enum', 'values' => array('draft', 'sent', 'open', 'revised', 'declined', 'accepted')),
                    'currency'     => array('col' => 'currency', 'type' => 'string'),
                    'notes'        => array('col' => 'notes', 'type' => 'text'),
                    'discount_type'=> array('col' => 'discount_type', 'type' => 'enum', 'values' => array('none', 'before_tax', 'after_tax')),
                    'discount_percent' => array('col' => 'discount_percent', 'type' => 'int'),
                    'discount_total'   => array('col' => 'discount_total', 'type' => 'float'),
                    'adjustment'       => array('col' => 'adjustment', 'type' => 'float'),
                    'items'        => array('col' => 'items', 'type' => 'json', 'description' => 'Array of line items, each with item_name, quantity, unit_cost, unit, item_desc'),
                    'assign_to'    => array('col' => 'permission', 'type' => 'permission'),
                ),
            ),
            'credit_note' => array(
                'singular'       => 'credit note',
                'plural'         => 'credit notes',
                'table'          => 'tbl_credit_note',
                'primary_key'    => 'credit_note_id',
                'label_column'   => 'reference_no',
                'identify_arg'   => 'credit_note_reference',
                'permission_id'  => 156,
                'link'           => 'admin/credit_note/',
                'icon'           => 'fa-sticky-note-o',
                'activity_create'=> 'activity_credit_note_created',
                'activity_update'=> 'activity_credit_note_updated',
                'activity_delete'=> 'activity_credit_note_deleted',
                'search_columns' => array('reference_no'),
                'list_fields'    => array('reference_no', 'client_id', 'credit_note_date', 'status'),
                'defaults'       => array('permission' => 'all'),
                'delete_cascade' => array(
                    array('table' => 'tbl_credit_note_items', 'where' => array('credit_note_id' => 'credit_note_id')),
                    array('table' => 'tbl_reminders',         'where' => array('module' => 'credit_note', 'module_id' => 'credit_note_id')),
                    array('table' => 'tbl_pinaction',         'where' => array('module_name' => 'credit_note', 'module_id' => 'credit_note_id')),
                ),
                'before_create'  => '_before_create_credit_note',
                'after_create'   => '_after_create_credit_note',
                'fields' => array(
                    'client_id'    => array('col' => 'client_id', 'type' => 'int', 'required' => true, 'resolve' => array('table' => 'tbl_client', 'key' => 'client_id', 'label' => 'name', 'name' => 'client'), 'description' => 'Client id or client name (required)'),
                    'project_id'   => array('col' => 'project_id', 'type' => 'int'),
                    'credit_note_date' => array('col' => 'credit_note_date', 'type' => 'date'),
                    'due_date'     => array('col' => 'due_date', 'type' => 'date'),
                    'status'       => array('col' => 'status', 'type' => 'enum', 'values' => array('open', 'refund', 'void')),
                    'currency'     => array('col' => 'currency', 'type' => 'string'),
                    'notes'        => array('col' => 'notes', 'type' => 'text'),
                    'discount_type'=> array('col' => 'discount_type', 'type' => 'enum', 'values' => array('none', 'before_tax', 'after_tax')),
                    'discount_percent' => array('col' => 'discount_percent', 'type' => 'int'),
                    'discount_total'   => array('col' => 'discount_total', 'type' => 'float'),
                    'adjustment'       => array('col' => 'adjustment', 'type' => 'float'),
                    'items'        => array('col' => 'items', 'type' => 'json', 'virtual' => true, 'description' => 'Array of line items, each with item_name, quantity, unit_cost, unit, item_desc'),
                    'assign_to'    => array('col' => 'permission', 'type' => 'permission'),
                ),
            ),
            'payment' => array(
                'singular'       => 'payment',
                'plural'         => 'payments',
                'table'          => 'tbl_payments',
                'primary_key'    => 'payments_id',
                'label_column'   => null,
                'identify_arg'   => 'payments_id',
                'permission_id'  => 15,
                'link'           => 'admin/transactions/expense_payment/',
                'icon'           => 'fa-money',
                'activity_create'=> 'activity_new_payment',
                'activity_update'=> 'activity_update_payment',
                'activity_delete'=> 'activity_delete_payment',
                'search_columns' => array('trans_id'),
                'list_fields'    => array('trans_id', 'invoices_id', 'amount', 'payment_method', 'payment_date'),
                'defaults'       => array('currency' => 'USD'),
                'before_create'  => '_before_create_payment',
                'after_create'   => '_after_create_payment',
                'fields' => array(
                    'invoices_id'    => array('col' => 'invoices_id', 'type' => 'int', 'required' => true, 'resolve' => array('table' => 'tbl_invoices', 'key' => 'invoices_id', 'label' => 'reference_no', 'name' => 'invoice'), 'description' => 'Invoice id or invoice reference (required)'),
                    'amount'         => array('col' => 'amount', 'type' => 'float', 'required' => true, 'description' => 'Amount paid (required)'),
                    'payment_method' => array('col' => 'payment_method', 'type' => 'string', 'description' => 'e.g. PayPal, Stripe, Bank, Cash'),
                    'payment_date'   => array('col' => 'payment_date', 'type' => 'date'),
                    'notes'          => array('col' => 'notes', 'type' => 'text'),
                    'currency'       => array('col' => 'currency', 'type' => 'string'),
                    'paid_by'        => array('col' => 'paid_by', 'type' => 'int', 'resolve' => array('table' => 'tbl_client', 'key' => 'client_id', 'label' => 'name', 'name' => 'client'), 'description' => 'Client id or client name who paid'),
                ),
            ),
            'tax_rate' => array(
                'singular'       => 'tax rate',
                'plural'         => 'tax rates',
                'table'          => 'tbl_tax_rates',
                'primary_key'    => 'tax_rates_id',
                'label_column'   => 'tax_rate_name',
                'identify_arg'   => 'tax_rate_name',
                'permission_id'  => 16,
                'link'           => 'admin/tax',
                'icon'           => 'fa-pie-chart',
                'activity_create'=> 'activity_tax_added',
                'activity_update'=> 'activity_tax_updated',
                'activity_delete'=> 'activity_tax_deleted',
                'search_columns' => array('tax_rate_name'),
                'list_fields'    => array('tax_rate_name', 'tax_rate_percent'),
                'defaults'       => array('permission' => 'all'),
                'fields' => array(
                    'tax_rate_name'    => array('col' => 'tax_rate_name', 'type' => 'string', 'required' => true, 'description' => 'Name of the tax rate (required)'),
                    'tax_rate_percent' => array('col' => 'tax_rate_percent', 'type' => 'float', 'required' => true, 'description' => 'Percentage value, e.g. 5 for 5% (required)'),
                    'assign_to'        => array('col' => 'permission', 'type' => 'permission'),
                ),
            ),
            'quotation' => array(
                'singular'       => 'quotation',
                'plural'         => 'quotations',
                'table'          => 'tbl_quotations',
                'primary_key'    => 'quotations_id',
                'label_column'   => 'name',
                'identify_arg'   => 'quotation_name',
                'permission_id'  => 22,
                'link'           => 'admin/quotations/quotations_details/',
                'icon'           => 'fa-file-text',
                'activity_create'=> 'activity_save_quotation_form',
                'activity_update'=> 'activity_update_quotation',
                'activity_delete'=> 'activity_delete_quotation',
                'search_columns' => array('name', 'email', 'quotations_form_title'),
                'list_fields'    => array('name', 'email', 'mobile', 'quotations_form_title', 'quotations_amount', 'quotations_status'),
                'defaults'       => array('quotations_form_title' => 'Quotation', 'quotations_status' => 'pending'),
                'before_create'  => '_before_create_quotation',
                'fields' => array(
                    'name'               => array('col' => 'name', 'type' => 'string', 'required' => true, 'description' => 'Customer name (required)'),
                    'email'              => array('col' => 'email', 'type' => 'email'),
                    'mobile'             => array('col' => 'mobile', 'type' => 'string'),
                    'client_id'          => array('col' => 'client_id', 'type' => 'int', 'resolve' => array('table' => 'tbl_client', 'key' => 'client_id', 'label' => 'name', 'name' => 'client')),
                    'quotations_form_title' => array('col' => 'quotations_form_title', 'type' => 'string'),
                    'quotations_amount'  => array('col' => 'quotations_amount', 'type' => 'float'),
                    'quotations_status'  => array('col' => 'quotations_status', 'type' => 'enum', 'values' => array('pending', 'completed')),
                    'notes'              => array('col' => 'notes', 'type' => 'text'),
                    'user_id'            => array('col' => 'user_id', 'type' => 'user_ref', 'create_only' => true, 'system' => true),
                ),
            ),
            'lead' => array(
                'singular'       => 'lead',
                'plural'         => 'leads',
                'table'          => 'tbl_leads',
                'primary_key'    => 'leads_id',
                'label_column'   => 'lead_name',
                'identify_arg'   => 'lead_name',
                'permission_id'  => 55,
                'link'           => 'admin/leads',
                'icon'           => 'fa-tty',
                'activity_create'=> 'activity_new_lead',
                'activity_update'=> 'activity_update_lead',
                'activity_delete'=> 'activity_delete_lead',
                'search_columns' => array('lead_name', 'organization', 'email', 'phone', 'mobile'),
                'list_fields'    => array('lead_name', 'organization', 'phone', 'mobile', 'email', 'lead_status_id', 'lead_source_id'),
                'defaults'       => array('permission' => 'all', 'language' => 'english', 'organization' => '', 'address' => '', 'city' => '', 'contact_name' => '', 'email' => '', 'phone' => '', 'mobile' => '', 'facebook' => '', 'skype' => '', 'twitter' => '', 'notes' => ''),
                'after_create'   => '_after_create_lead',
                'fields' => array(
                    'lead_name'      => array('col' => 'lead_name', 'type' => 'string', 'required' => true, 'description' => 'Lead name (required)'),
                    'organization'   => array('col' => 'organization', 'type' => 'string'),
                    'company_name'   => array('col' => 'company_name', 'type' => 'string'),
                    'email'          => array('col' => 'email', 'type' => 'email'),
                    'phone'          => array('col' => 'phone', 'type' => 'string'),
                    'mobile'         => array('col' => 'mobile', 'type' => 'string'),
                    'address'        => array('col' => 'address', 'type' => 'text'),
                    'city'           => array('col' => 'city', 'type' => 'string'),
                    'state'          => array('col' => 'state', 'type' => 'string'),
                    'country'        => array('col' => 'country', 'type' => 'string'),
                    'contact_name'   => array('col' => 'contact_name', 'type' => 'string'),
                    'title'          => array('col' => 'title', 'type' => 'string'),
                    'facebook'       => array('col' => 'facebook', 'type' => 'string'),
                    'skype'          => array('col' => 'skype', 'type' => 'string'),
                    'twitter'        => array('col' => 'twitter', 'type' => 'string'),
                    'notes'          => array('col' => 'notes', 'type' => 'text'),
                    'language'       => array('col' => 'language', 'type' => 'string'),
                    'lead_status_id' => array('col' => 'lead_status_id', 'type' => 'int'),
                    'lead_source_id' => array('col' => 'lead_source_id', 'type' => 'int'),
                    'tags'           => array('col' => 'tags', 'type' => 'string'),
                    'client_id'      => array('col' => 'client_id', 'type' => 'int', 'resolve' => array('table' => 'tbl_client', 'key' => 'client_id', 'label' => 'name', 'name' => 'client')),
                    'last_contact'   => array('col' => 'last_contact', 'type' => 'date'),
                    'assign_to'      => array('col' => 'permission', 'type' => 'permission'),
                ),
            ),
            'opportunity' => array(
                'singular'       => 'opportunity',
                'plural'         => 'opportunities',
                'table'          => 'tbl_opportunities',
                'primary_key'    => 'opportunities_id',
                'label_column'   => 'opportunity_name',
                'identify_arg'   => 'opportunity_name',
                'permission_id'  => 56,
                'link'           => 'admin/opportunities/opportunity_details/',
                'icon'           => 'fa-bullseye',
                'activity_create'=> 'activity_new_opportunity',
                'activity_update'=> 'activity_update_opportunity',
                'activity_delete'=> 'activity_delete_opportunity',
                'search_columns' => array('opportunity_name', 'next_action'),
                'list_fields'    => array('opportunity_name', 'stages', 'probability', 'expected_revenue', 'close_date', 'next_action'),
                'defaults'       => array('permission' => 'all', 'stages' => 'new', 'probability' => 'High', 'close_date' => '', 'opportunities_state_reason_id' => 0, 'expected_revenue' => '0', 'new_link' => '', 'next_action' => '', 'next_action_date' => ''),
                'fields' => array(
                    'opportunity_name'       => array('col' => 'opportunity_name', 'type' => 'string', 'required' => true, 'description' => 'Opportunity name (required)'),
                    'stages'                 => array('col' => 'stages', 'type' => 'enum', 'values' => array('new', 'qualification', 'proposition', 'won', 'lost', 'dead')),
                    'probability'            => array('col' => 'probability', 'type' => 'string'),
                    'close_date'             => array('col' => 'close_date', 'type' => 'date'),
                    'opportunities_state_reason_id' => array('col' => 'opportunities_state_reason_id', 'type' => 'int'),
                    'expected_revenue'       => array('col' => 'expected_revenue', 'type' => 'float'),
                    'new_link'               => array('col' => 'new_link', 'type' => 'string'),
                    'next_action'            => array('col' => 'next_action', 'type' => 'string'),
                    'next_action_date'       => array('col' => 'next_action_date', 'type' => 'date'),
                    'notes'                  => array('col' => 'notes', 'type' => 'text'),
                    'assign_to'              => array('col' => 'permission', 'type' => 'permission'),
                ),
            ),
            'ticket' => array(
                'singular'       => 'ticket',
                'plural'         => 'tickets',
                'table'          => 'tbl_tickets',
                'primary_key'    => 'tickets_id',
                'label_column'   => 'subject',
                'identify_arg'   => 'ticket_subject',
                'permission_id'  => 6,
                'link'           => 'admin/tickets/',
                'icon'           => 'fa-ticket',
                'activity_create'=> 'activity_save_ticket',
                'activity_update'=> 'activity_update_ticket',
                'activity_delete'=> 'activity_delete_ticket',
                'search_columns' => array('subject', 'ticket_code', 'tags'),
                'list_fields'    => array('ticket_code', 'subject', 'status', 'priority', 'departments_id', 'project_id'),
                'defaults'       => array('permission' => 'all', 'project_id' => 0),
                'before_create'  => '_before_create_ticket',
                'fields' => array(
                    'subject'        => array('col' => 'subject', 'type' => 'string', 'required' => true, 'description' => 'Ticket subject (required)'),
                    'body'           => array('col' => 'body', 'type' => 'text'),
                    'priority'       => array('col' => 'priority', 'type' => 'string'),
                    'status'         => array('col' => 'status', 'type' => 'enum', 'values' => array('open', 'answered', 'closed')),
                    'departments_id' => array('col' => 'departments_id', 'type' => 'int', 'resolve' => array('table' => 'tbl_departments', 'key' => 'departments_id', 'label' => 'deptname', 'name' => 'department')),
                    'project_id'     => array('col' => 'project_id', 'type' => 'int'),
                    'reporter'       => array('col' => 'reporter', 'type' => 'user_ref', 'description' => 'User id or full name who reported it'),
                    'tags'           => array('col' => 'tags', 'type' => 'string'),
                    'assign_to'      => array('col' => 'permission', 'type' => 'permission'),
                ),
            ),
            'bug' => array(
                'singular'       => 'bug',
                'plural'         => 'bugs',
                'table'          => 'tbl_bug',
                'primary_key'    => 'bug_id',
                'label_column'   => 'bug_title',
                'identify_arg'   => 'bug_title',
                'permission_id'  => 58,
                'link'           => 'admin/bug/bug_details/',
                'icon'           => 'fa-bug',
                'activity_create'=> 'activity_bug_added',
                'activity_update'=> 'activity_bug_updated',
                'activity_delete'=> 'activity_bug_deleted',
                'search_columns' => array('bug_title', 'issue_no'),
                'list_fields'    => array('issue_no', 'bug_title', 'bug_status', 'priority', 'severity', 'project_id', 'opportunities_id'),
                'defaults'       => array('permission' => 'all', 'task_id' => 0, 'client_visible' => 'No', 'bug_status' => 'unconfirmed', 'bug_description' => ''),
                'before_create'  => '_before_create_bug',
                'fields' => array(
                    'bug_title'       => array('col' => 'bug_title', 'type' => 'string', 'required' => true, 'description' => 'Bug title (required)'),
                    'bug_description' => array('col' => 'bug_description', 'type' => 'text', 'required' => true, 'description' => 'Description of the bug (required)'),
                    'bug_status'      => array('col' => 'bug_status', 'type' => 'enum', 'values' => array('unconfirmed', 'confirmed', 'in_progress', 'resolved', 'verified')),
                    'issue_no'        => array('col' => 'issue_no', 'type' => 'string'),
                    'project_id'      => array('col' => 'project_id', 'type' => 'int'),
                    'opportunities_id'=> array('col' => 'opportunities_id', 'type' => 'int'),
                    'reproducibility' => array('col' => 'reproducibility', 'type' => 'text'),
                    'priority'        => array('col' => 'priority', 'type' => 'string'),
                    'severity'        => array('col' => 'severity', 'type' => 'string'),
                    'reporter'        => array('col' => 'reporter', 'type' => 'user_ref', 'description' => 'User id or full name who reported it'),
                    'client_visible'  => array('col' => 'client_visible', 'type' => 'enum', 'values' => array('Yes', 'No')),
                    'notes'           => array('col' => 'notes', 'type' => 'text'),
                    'assign_to'       => array('col' => 'permission', 'type' => 'permission'),
                ),
            ),
            'department' => array(
                'singular'       => 'department',
                'plural'         => 'departments',
                'table'          => 'tbl_departments',
                'primary_key'    => 'departments_id',
                'label_column'   => 'deptname',
                'identify_arg'   => 'deptname',
                'permission_id'  => 70,
                'link'           => 'admin/departments',
                'icon'           => 'fa-sitemap',
                'activity_create'=> 'activity_save_department',
                'activity_update'=> 'activity_update_department',
                'activity_delete'=> 'activity_delete_department',
                'search_columns' => array('deptname'),
                'list_fields'    => array('deptname', 'department_head_id', 'email'),
                'dedup'          => array('arg' => 'deptname', 'col' => 'deptname'),
                'fields' => array(
                    'deptname' => array('col' => 'deptname', 'type' => 'string', 'required' => true, 'description' => 'Department name (required)'),
                ),
            ),
            'contract' => array(
                'singular'       => 'contract',
                'plural'         => 'contracts',
                'table'          => 'tbl_contracts',
                'primary_key'    => 'contract_id',
                'label_column'   => 'subject',
                'identify_arg'   => 'contract_subject',
                'permission_id'  => 233,
                'link'           => 'admin/contracts',
                'icon'           => 'fa-file-text-o',
                'activity_create'=> 'activity_save_contract',
                'activity_update'=> 'activity_update_contract',
                'activity_delete'=> 'activity_delete_contract',
                'search_columns' => array('subject', 'description'),
                'list_fields'    => array('subject', 'client', 'contract_value', 'contract_type', 'start_date', 'end_date'),
                'before_create'  => '_before_create_contract',
                'fields' => array(
                    'subject'        => array('col' => 'subject', 'type' => 'string', 'required' => true, 'description' => 'Contract subject (required)'),
                    'client'         => array('col' => 'client', 'type' => 'int', 'required' => true, 'resolve' => array('table' => 'tbl_client', 'key' => 'client_id', 'label' => 'name', 'name' => 'client'), 'description' => 'Client id or client name (required)'),
                    'contract_value' => array('col' => 'contract_value', 'type' => 'float'),
                    'contract_type'  => array('col' => 'contract_type', 'type' => 'int'),
                    'start_date'     => array('col' => 'start_date', 'type' => 'date', 'required' => true, 'description' => 'Start date YYYY-MM-DD (required)'),
                    'end_date'       => array('col' => 'end_date', 'type' => 'date'),
                    'project_id'     => array('col' => 'project_id', 'type' => 'int'),
                    'description'    => array('col' => 'description', 'type' => 'text'),
                    'visible_to_client' => array('col' => 'visible_to_client', 'type' => 'enum', 'values' => array('Yes', 'No')),
                ),
            ),
            'complaint' => array(
                'singular'       => 'complaint',
                'plural'         => 'complaints',
                'table'          => 'tbl_complaints',
                'primary_key'    => 'tickets_id',
                'label_column'   => 'subject',
                'identify_arg'   => 'complaint_subject',
                'permission_id'  => 234,
                'link'           => 'admin/complaints/new_complaint/',
                'icon'           => 'fa-bullhorn',
                'activity_create'=> 'activity_save_complaint',
                'activity_update'=> 'activity_update_complaint',
                'activity_delete'=> 'activity_delete_complaint',
                'search_columns' => array('subject', 'ticket_code'),
                'list_fields'    => array('ticket_code', 'subject', 'status', 'priority', 'client', 'departments_id'),
                'defaults'       => array('permission' => 'all', 'project_id' => 0),
                'before_create'  => '_before_create_complaint',
                'fields' => array(
                    'subject'        => array('col' => 'subject', 'type' => 'string', 'required' => true, 'description' => 'Complaint subject (required)'),
                    'body'           => array('col' => 'body', 'type' => 'text'),
                    'priority'       => array('col' => 'priority', 'type' => 'string'),
                    'status'         => array('col' => 'status', 'type' => 'enum', 'values' => array('open', 'answered', 'closed')),
                    'client'         => array('col' => 'client', 'type' => 'int', 'required' => true, 'resolve' => array('table' => 'tbl_client', 'key' => 'client_id', 'label' => 'name', 'name' => 'client'), 'description' => 'Client id or client name (required)'),
                    'departments_id' => array('col' => 'departments_id', 'type' => 'int', 'resolve' => array('table' => 'tbl_departments', 'key' => 'departments_id', 'label' => 'deptname', 'name' => 'department')),
                    'project_id'     => array('col' => 'project_id', 'type' => 'int'),
                    'ticket_type'    => array('col' => 'ticket_type', 'type' => 'string'),
                    'ticket_sub_type'=> array('col' => 'ticket_sub_type', 'type' => 'string'),
                    'due_date'       => array('col' => 'due_date', 'type' => 'date'),
                    'reporter'       => array('col' => 'reporter', 'type' => 'user_ref', 'description' => 'User id or full name who logged it'),
                    'assign_to'      => array('col' => 'permission', 'type' => 'permission'),
                ),
            ),
            'article' => array(
                'singular'       => 'article',
                'plural'         => 'articles',
                'table'          => 'tbl_knowledgebase',
                'primary_key'    => 'kb_id',
                'label_column'   => 'title',
                'identify_arg'   => 'article_title',
                'permission_id'  => 143,
                'link'           => 'admin/knowledgebase/articles_details/',
                'icon'           => 'fa-file-text-o',
                'activity_create'=> 'activity_save_kb',
                'activity_update'=> 'activity_update_kb',
                'activity_delete'=> 'activity_delete_kb',
                'search_columns' => array('title', 'description'),
                'list_fields'    => array('title', 'slug', 'kb_category_id', 'status'),
                'defaults'       => array('status' => 1),
                'before_create'  => '_before_create_article',
                'fields' => array(
                    'title'          => array('col' => 'title', 'type' => 'string', 'required' => true, 'description' => 'Article title (required)'),
                    'description'    => array('col' => 'description', 'type' => 'text'),
                    'kb_category_id' => array('col' => 'kb_category_id', 'type' => 'int'),
                    'for_all'        => array('col' => 'for_all', 'type' => 'enum', 'values' => array('Yes', 'No')),
                    'status'         => array('col' => 'status', 'type' => 'int', 'values' => array(0, 1)),
                    'assign_to'      => array('col' => 'permission', 'type' => 'permission'),
                ),
            ),
        );
    }

    /**
     * Generate the list/get/create/update/delete tools for every registry entry.
     * The registry merges the hand-written manifest with the auto-discovered
     * database schema, so every writable table in the CRM is covered.
     *
     * @return array
     */
    private function _manifest_tools()
    {
        $tools = array();
        foreach ($this->_registry() as $entry) {
            $tools = array_merge($tools, $this->_entry_tools($entry));
        }
        return $tools;
    }

    /**
     * Generate the 5 generic tools for a single registry entry.
     * Read-only entries only expose list_ / get_.
     *
     * @param  array $entry
     * @return array
     */
    private function _entry_tools($entry)
    {
        $tools     = array();
        $singular  = $entry['singular'];
        $plural    = $entry['plural'];
        $pk        = $entry['primary_key'];
        $read_only = !empty($entry['read_only']);

        $search_props = array(
            'search' => array('type' => 'string', 'description' => 'Optional search term'),
        );
        if (!empty($entry['status_arg']) && !empty($entry['status_column'])) {
            $search_props['status'] = array('type' => 'string', 'description' => 'Optional status filter');
        }

        $tools[] = array(
            'name'        => 'list_' . str_replace(' ', '_', $plural),
            'description' => 'List the latest ' . $plural . ' in the CRM. Use the search term to narrow the results.',
            'parameters'  => array('type' => 'object', 'properties' => $search_props),
        );

        $tools[] = array(
            'name'        => 'get_' . str_replace(' ', '_', $singular),
            'description' => 'Get a single ' . $singular . ' by ' . $pk . ' or by its ' . $entry['label_column'] . '.',
            'parameters'  => array(
                'type'       => 'object',
                'properties' => array(
                    $pk         => array('type' => 'integer'),
                    $entry['identify_arg'] => array('type' => 'string'),
                ),
            ),
        );

        if ($read_only) {
            return $tools;
        }

        $tools[] = array(
            'name'        => 'create_' . str_replace(' ', '_', $singular),
            'description' => 'Create a new ' . $singular . ' in the CRM. Returns the new ' . $pk . '.',
            'parameters'  => $this->_schema_from_fields($entry, 'create'),
        );

        $tools[] = array(
            'name'        => 'update_' . str_replace(' ', '_', $singular),
            'description' => 'Update fields of an existing ' . $singular . '.',
            'parameters'  => $this->_schema_from_fields($entry, 'update'),
        );

        $tools[] = array(
            'name'        => 'delete_' . str_replace(' ', '_', $singular),
            'description' => 'Delete an existing ' . $singular . ' from the CRM. The system will ask the user to confirm first.',
            'parameters'  => array(
                'type'       => 'object',
                'properties' => array(
                    $pk         => array('type' => 'integer'),
                    $entry['identify_arg'] => array('type' => 'string'),
                ),
            ),
        );

        if (!empty($entry['_skip_tools'])) {
            $skip = array_flip($entry['_skip_tools']);
            $tools = array_values(array_filter($tools, function ($def) use ($skip) {
                return !isset($skip[$def['name']]);
            }));
        }

        return $tools;
    }

    /* ---------------------------------------------------------------------
     * Universal Dynamic Tool Registry
     *
     * Merges the hand-written manifest (highest priority) with tables
     * auto-discovered from the live database schema (information_schema).
     * Overrides / exclusions / read-only lists come from
     * application/config/ai_registry.php. The registry is unrestricted: every
     * entry is driven with Super-admin privileges.
     * ---------------------------------------------------------------- */

    /**
     * Merged registry, keyed by table name.
     *
     * @return array
     */
    private function _registry()
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $registry  = array();
        $used      = array();

        // Manual tool names always win.
        foreach ($this->_manual_tools() as $def) {
            $used[$def['name']] = true;
        }

        // Hand-written manifest entries win over discovery for their tables.
        foreach ($this->_manifest() as $entry) {
            $registry[$entry['table']] = $entry;
            foreach ($this->_entry_tools($entry) as $def) {
                $used[$def['name']] = true;
            }
        }

        // Discovered tables fill every gap not already covered by name.
        foreach ($this->_discover_schema() as $table => $entry) {
            if (isset($registry[$table])) {
                continue;
            }
            // Drop only the tool names that collide with an existing tool
            // (e.g. a dedicated manual create_x), keep the rest of the entry.
            $skip = array();
            $keep = array();
            foreach ($this->_entry_tools($entry) as $def) {
                if (isset($used[$def['name']])) {
                    $skip[$def['name']] = true;
                } else {
                    $keep[$def['name']] = true;
                }
            }
            if (empty($keep)) {
                continue;
            }
            foreach (array_keys($keep) as $name) {
                $used[$name] = true;
            }
            $entry['_skip_tools'] = array_keys($skip);
            $registry[$table] = $entry;
        }

        $cache = $registry;
        return $cache;
    }

    /**
     * Auto-discover every writable table from the live database schema.
     *
     * @return array
     */
    private function _discover_schema()
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $this->ci->load->config('ai_registry', false, true);
        $exclude   = (array) $this->ci->config->item('ai_registry_exclude');
        $read_only = (array) $this->ci->config->item('ai_registry_read_only');
        $overrides = (array) $this->ci->config->item('ai_registry_overrides');

        $rows = $this->ci->db->query(
            "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, DATA_TYPE, IS_NULLABLE,
                    COLUMN_DEFAULT, COLUMN_KEY, EXTRA
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
             ORDER BY TABLE_NAME, ORDINAL_POSITION"
        )->result();

        $tables = array();
        foreach ($rows as $row) {
            $tables[$row->TABLE_NAME][] = $row;
        }

        $covered = array();
        foreach ($this->_manifest() as $entry) {
            $covered[$entry['table']] = true;
        }

        $entries = array();
        foreach ($tables as $table => $cols) {
            if (in_array($table, $exclude, true) || isset($covered[$table])) {
                continue;
            }

            $pk       = null;
            $pk_count = 0;
            foreach ($cols as $col) {
                if ($col->COLUMN_KEY === 'PRI') {
                    $pk       = $col->COLUMN_NAME;
                    $pk_count++;
                }
            }
            if ($pk_count !== 1) {
                continue;
            }

            $is_read_only = in_array($table, $read_only, true);
            $base         = $this->_table_base_name($table);
            $label        = $this->_pick_label_column($cols, $pk);

            $entry = array(
                'singular'      => isset($overrides[$table]['singular']) ? $overrides[$table]['singular'] : $this->_singularize($base),
                'plural'        => isset($overrides[$table]['plural']) ? $overrides[$table]['plural'] : $this->_pluralize($base),
                'table'         => $table,
                'primary_key'   => $pk,
                'label_column'  => $label,
                'identify_arg'  => $label,
                'search_columns'=> array($label),
                'list_fields'   => array(),
                'defaults'      => array(),
                'fields'        => array(),
                'read_only'     => $is_read_only,
                'aliases'       => isset($overrides[$table]['aliases']) ? (array) $overrides[$table]['aliases'] : array(),
            );

            if (isset($overrides[$table]['label_column'])) {
                $entry['label_column'] = $overrides[$table]['label_column'];
                $entry['identify_arg'] = $overrides[$table]['label_column'];
            }

            foreach ($cols as $col) {
                $name = $col->COLUMN_NAME;
                if ($name === $pk) {
                    continue;
                }

                $def = array('col' => $name, 'type' => $this->_db_type($col));

                if ($this->_is_system_column($name)) {
                    $def['system'] = true;
                    if ($name === 'permission') {
                        $entry['defaults']['permission'] = 'all';
                    }
                    $entry['fields'][$name] = $def;
                    continue;
                }

                if (($col->DATA_TYPE === 'enum' || $col->DATA_TYPE === 'set') && preg_match('/^enum\((.*)\)$/i', $col->COLUMN_TYPE, $m)) {
                    $def['type']   = 'enum';
                    $def['values'] = $this->_parse_enum_values($m[1]);
                }
                if ($col->IS_NULLABLE === 'NO'
                    && ($col->COLUMN_DEFAULT === null || $col->COLUMN_DEFAULT === '')
                    && strpos($col->EXTRA, 'auto_increment') === false) {
                    $def['required'] = true;
                }
                if (preg_match('/_id$/i', $name)) {
                    $def['_fk'] = $this->_fk_base($name);
                }

                $entry['fields'][$name] = $def;
            }

            $entry['list_fields'] = $this->_pick_list_fields($entry, $cols);

            $entries[$table] = $entry;
        }

        // Second pass: resolve foreign keys against every known entry.
        foreach ($entries as $table => &$entry) {
            foreach ($entry['fields'] as $name => &$def) {
                if (empty($def['_fk'])) {
                    continue;
                }
                $resolve = $this->_resolve_fk($entries, $def['_fk']);
                unset($def['_fk']);
                if ($resolve) {
                    $def['resolve'] = $resolve;
                }
            }
            unset($def);
        }
        unset($entry);

        $cache = $entries;
        return $cache;
    }

    private function _table_base_name($table)
    {
        $base = preg_replace('/^tbl_/', '', $table);
        return str_replace('_', ' ', $base);
    }

    private function _singularize($word)
    {
        if (substr($word, -3) === 'ies') {
            return substr($word, 0, -3) . 'y';
        }
        if (substr($word, -1) === 's' && substr($word, -2) !== 'ss' && substr($word, -2) !== 'us') {
            return substr($word, 0, -1);
        }
        return $word;
    }

    private function _pluralize($word)
    {
        if (substr($word, -1) === 'y') {
            return substr($word, 0, -1) . 'ies';
        }
        if (substr($word, -1) === 's') {
            return $word;
        }
        return $word . 's';
    }

    private function _db_type($col)
    {
        switch ($col->DATA_TYPE) {
            case 'tinyint':
                return ($col->COLUMN_TYPE === 'tinyint(1)') ? 'bool' : 'int';
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'bigint':
                return 'int';
            case 'decimal':
            case 'double':
            case 'float':
                return 'float';
            case 'date':
            case 'datetime':
            case 'timestamp':
                return 'date';
            case 'enum':
            case 'set':
                return 'enum';
            case 'json':
                return 'json';
            default:
                return 'string';
        }
    }

    private function _is_system_column($name)
    {
        if ($name === 'permission') {
            return true;
        }
        if (in_array($name, array('date_created', 'date_updated', 'created_at', 'updated_at', 'last_activity', 'created_by', 'updated_by'), true)) {
            return true;
        }
        if (preg_match('/^(created|updated|added)_(at|on|by|date|datetime)$/i', $name)) {
            return true;
        }
        // Secrets / credentials are never written by the generic engine.
        if (preg_match('/password|secret|token|private_key|api_key|new_email_key|new_password_key|smtp_password/i', $name)) {
            return true;
        }
        return false;
    }

    private function _parse_enum_values($str)
    {
        if (preg_match_all("/'([^']*)'/", $str, $m)) {
            return $m[1];
        }
        return array();
    }

    private function _pick_label_column($cols, $pk)
    {
        $preferred = array('name', 'title', 'subject', 'reference_no', 'reference', 'code', 'fullname', 'employee_name', 'username', 'email');
        $names = array();
        foreach ($cols as $col) {
            if ($col->COLUMN_NAME === $pk || $col->DATA_TYPE === 'blob' || $col->DATA_TYPE === 'json') {
                continue;
            }
            $names[] = $col->COLUMN_NAME;
        }
        foreach ($preferred as $p) {
            if (in_array($p, $names, true)) {
                return $p;
            }
        }
        foreach ($names as $n) {
            if (preg_match('/_name$/i', $n) || preg_match('/name$/i', $n) || preg_match('/_title$/i', $n)) {
                return $n;
            }
        }
        foreach ($names as $n) {
            if (preg_match('/^(varchar|char|text)/', $this->_data_type_of($cols, $n))) {
                return $n;
            }
        }
        return $pk;
    }

    private function _data_type_of($cols, $name)
    {
        foreach ($cols as $col) {
            if ($col->COLUMN_NAME === $name) {
                return $col->DATA_TYPE;
            }
        }
        return 'varchar';
    }

    private function _pick_list_fields($entry, $cols)
    {
        $list = array();
        foreach (array($entry['primary_key'], $entry['label_column']) as $c) {
            if ($c !== '' && !in_array($c, $list, true)) {
                $list[] = $c;
            }
        }
        foreach ($entry['fields'] as $name => $def) {
            if (count($list) >= 5) {
                break;
            }
            if (!empty($def['system']) || in_array($name, $list, true)) {
                continue;
            }
            if (in_array($def['type'], array('json', 'text', 'bool'), true)) {
                continue;
            }
            $list[] = $name;
        }
        return $list;
    }

    private function _fk_base($name)
    {
        $base = preg_replace('/_id$/i', '', $name);
        return strtolower(trim($base));
    }

    private function _resolve_fk($entries, $base)
    {
        $candidates = array(
            'tbl_' . $base,
            'tbl_' . $base . 's',
            'tbl_' . $base . 'es',
        );
        if (substr($base, -1) === 'y') {
            $candidates[] = 'tbl_' . substr($base, 0, -1) . 'ies';
        }
        foreach ($candidates as $candidate) {
            if (!isset($entries[$candidate])) {
                continue;
            }
            $target = $entries[$candidate];
            if (isset($target['label_column']) && $target['label_column'] !== $target['primary_key']) {
                return array(
                    'table' => $candidate,
                    'key'   => $target['primary_key'],
                    'label' => $target['label_column'],
                    'name'  => $target['singular'],
                );
            }
        }
        return null;
    }

    private function _search_tokens($topic)
    {
        $topic = strtolower(trim($topic));
        $topic = preg_replace('/[^a-z0-9\s]/', ' ', $topic);
        $tokens = preg_split('/\s+/', trim($topic));
        $stop   = array('the', 'a', 'an', 'and', 'or', 'of', 'in', 'on', 'for', 'to', 'with', 'all', 'list', 'find', 'my', 'me', 'please', 'show', 'get', 'create', 'new', 'update', 'delete', 'details', 'info', 'information');
        $out = array();
        foreach ($tokens as $t) {
            if ($t !== '' && !in_array($t, $stop, true)) {
                $out[] = $t;
            }
        }
        return $out;
    }

    private function _entry_haystack($entry)
    {
        $hay = $entry['singular'] . ' ' . $entry['plural'] . ' ' . $entry['table'];
        if (!empty($entry['aliases'])) {
            $hay .= ' ' . implode(' ', $entry['aliases']);
        }
        if (!empty($entry['label_column'])) {
            $hay .= ' ' . $entry['label_column'];
        }
        return ' ' . strtolower($hay) . ' ';
    }

    private function _haystack_matches($haystack, $token)
    {
        if ($token === '' || strpos($haystack, $token) !== false) {
            return true;
        }
        // Token is a substring of a word (e.g. "accounting" -> "account")
        foreach (preg_split('/\s+/', trim($haystack)) as $word) {
            if (strlen($word) >= 4 && strpos($token, $word) !== false) {
                return true;
            }
        }
        return false;
    }

    private function _active_module_tables($context)
    {
        $context = strtolower(trim((string) $context));
        if ($context === '') {
            return array();
        }
        if (strpos($context, '>') !== false) {
            $parts   = explode('>', $context);
            $context = trim((string) array_pop($parts));
        }
        $context = preg_replace('/^admin[\s\/]*/', '', $context);
        $context = preg_replace('/[^a-z0-9_]/', '_', $context);

        $this->ci->load->config('ai_registry', false, true);
        $module_map = (array) $this->ci->config->item('ai_registry_module_map');

        if (isset($module_map[$context])) {
            return (array) $module_map[$context];
        }

        $tables = array();
        foreach ($this->_registry() as $table => $entry) {
            if (strpos($this->_entry_haystack($entry), ' ' . $context . ' ') !== false
                || strpos($this->_entry_haystack($entry), '_' . $context) !== false) {
                $tables[] = $table;
            }
        }
        return $tables;
    }

    private function _schema_from_fields($entry, $mode)
    {
        $properties = array();
        $required   = array();

        foreach ($entry['fields'] as $arg => $def) {
            if (!empty($def['system'])) {
                continue;
            }
            if ($mode === 'create') {
                $schema_def = $def;
                if (isset($def['type']) && $def['type'] === 'date' && $arg !== 'due_date' && empty($def['description'])) {
                    $schema_def['description'] = 'Date in YYYY-MM-DD. Use today\'s date unless the user specifies another date.';
                }
                $properties[$arg] = $this->_schema_type($schema_def);
                if (!empty($def['required'])) {
                    $required[] = $arg;
                }
            } else {
                if (!empty($def['create_only']) || !empty($def['virtual'])) {
                    continue;
                }
                $properties[$arg] = $this->_schema_type($def);
            }
        }

        $schema = array(
            'type'       => 'object',
            'properties' => $properties,
        );
        if (!empty($required)) {
            $schema['required'] = $required;
        }
        return $schema;
    }

    private function _schema_type($def)
    {
        $type  = isset($def['type']) ? $def['type'] : 'string';
        $props = array();

        switch ($type) {
            case 'int':
                $props['type'] = 'integer';
                break;
            case 'float':
                $props['type'] = 'number';
                break;
            case 'permission':
                $props['type']  = 'array';
                $props['items'] = array('type' => 'string');
                break;
            case 'json':
                $props['type']  = 'array';
                $props['items'] = array('type' => 'object');
                break;
            case 'date':
                $props['type']        = 'string';
                $props['description'] = 'Date in YYYY-MM-DD format';
                break;
            default:
                $props['type'] = 'string';
                break;
        }

        if (!empty($def['values']) && $type === 'enum') {
            $props['enum'] = array_values($def['values']);
        }
        if (!empty($def['description'])) {
            $props['description'] = $def['description'];
        }
        return $props;
    }

    /* ---------------------------------------------------------------------
     * Generic manifest engine
     * ---------------------------------------------------------------- */

    private function _manifest_lookup($tool)
    {
        $ops = array('list', 'get', 'create', 'update', 'delete');
        foreach ($this->_registry() as $entry) {
            if (!empty($entry['_skip_tools']) && in_array($tool, $entry['_skip_tools'], true)) {
                continue;
            }
            foreach ($ops as $op) {
                $name = $op . '_' . ($op === 'list'
                    ? str_replace(' ', '_', $entry['plural'])
                    : str_replace(' ', '_', $entry['singular']));
                if ($tool === $name) {
                    return array('op' => $op, 'entry' => $entry);
                }
            }
        }
        return null;
    }

    private function _gen_list($entry, $args)
    {
        if (!$this->_can($entry, 'view')) {
            return array('success' => false, 'message' => 'You do not have permission to view ' . $entry['plural'] . '.', 'data' => null);
        }

        $search = trim(isset($args['search']) ? $args['search'] : '');
        if ($search !== '' && !empty($entry['search_columns'])) {
            $this->ci->db->group_start();
            foreach ($entry['search_columns'] as $col) {
                $this->ci->db->or_like($col, $search);
            }
            $this->ci->db->group_end();
        }
        if (!empty($entry['status_column']) && !empty($args['status'])) {
            $this->ci->db->where($entry['status_column'], $args['status']);
        }

        $rows = $this->ci->db->order_by($entry['primary_key'], 'DESC')->limit(30)->get($entry['table'])->result();

        $data = array();
        foreach ($rows as $row) {
            if (!$this->_row_allowed($row)) {
                continue;
            }
            $data[] = $this->_row_array($entry, $row);
        }

        if (empty($data)) {
            return array('success' => true, 'message' => 'No ' . $entry['plural'] . ' found.', 'data' => array());
        }

        return array('success' => true, 'message' => 'Found ' . count($data) . ' ' . $entry['plural'] . '.', 'data' => $data);
    }

    private function _gen_get($entry, $args)
    {
        $row = $this->_find_row($entry, $args);
        if (empty($row)) {
            return array('success' => false, 'message' => ucfirst($entry['singular']) . ' not found.', 'data' => null);
        }
        if (!$this->_row_allowed($row)) {
            return array('success' => false, 'message' => 'You do not have permission to view this ' . $entry['singular'] . '.', 'data' => null);
        }
        return array('success' => true, 'message' => ucfirst($entry['singular']) . ' found.', 'data' => $this->_row_array($entry, $row));
    }

    private function _gen_create($entry, $args)
    {
        if (!$this->_can($entry, 'created')) {
            return array('success' => false, 'message' => 'You do not have permission to create ' . $entry['plural'] . '.', 'data' => null);
        }

        $data = isset($entry['defaults']) ? $entry['defaults'] : array();

        foreach ($entry['fields'] as $arg => $def) {
            if (!empty($def['virtual'])) {
                continue;
            }
            if (!empty($def['create_only']) && !array_key_exists($arg, $args)) {
                continue;
            }
            if (array_key_exists($arg, $args) && $args[$arg] !== null && $args[$arg] !== '') {
                $val = $this->_resolve_value($args[$arg], $def);
                $data[$def['col']] = $this->_coerce($val, $def);
            } elseif (!empty($def['required'])) {
                return array('success' => false, 'message' => 'Missing required field: ' . $arg . '.', 'data' => null);
            }
        }

        // Deduplicate simple entries (e.g. departments).
        if (!empty($entry['dedup'])) {
            $col = $entry['dedup']['col'];
            $arg = $entry['dedup']['arg'];
            if (isset($data[$col]) && $data[$col] !== '') {
                $existing = $this->ci->db->where($col, $data[$col])->get($entry['table'])->row();
                if (!empty($existing)) {
                    return array(
                        'success' => true,
                        'message' => ucfirst($entry['singular']) . ' "' . $data[$col] . '" already exists with ' . $entry['primary_key'] . ' ' . $existing->{$entry['primary_key']} . '.',
                        'data'    => array($entry['primary_key'] => (int) $existing->{$entry['primary_key']}, 'exists' => true),
                    );
                }
            }
        }

        if (!empty($entry['before_create'])) {
            $data = $this->{ $entry['before_create'] }($data, $args);
        }

        if (empty($data)) {
            return array('success' => false, 'message' => 'Nothing to create.', 'data' => null);
        }

        $this->ci->db->insert($entry['table'], $data);
        $id = (int) $this->ci->db->insert_id();
        if (empty($id)) {
            return array('success' => false, 'message' => 'Could not create the ' . $entry['singular'] . '. Database insert failed. ' . $this->_db_error(), 'data' => null);
        }

        if (!empty($entry['after_create'])) {
            $this->{ $entry['after_create'] }($id, $args, $data);
        }

        if (!empty($entry['activity_create'])) {
            $this->_log_activity(
                $entry['singular'],
                $id,
                $entry['activity_create'],
                $entry['icon'],
                $entry['link'] . $id,
                $this->_label($entry, $data)
            );
        }

        return array(
            'success' => true,
            'message' => ucfirst($entry['singular']) . ' "' . $this->_label($entry, $data) . '" created with ' . $entry['primary_key'] . ' ' . $id . '.',
            'data'    => array($entry['primary_key'] => $id, 'label' => $this->_label($entry, $data)),
        );
    }

    private function _gen_update($entry, $args)
    {
        if (!$this->_can($entry, 'edited')) {
            return array('success' => false, 'message' => 'You do not have permission to update ' . $entry['plural'] . '.', 'data' => null);
        }

        $row = $this->_find_row($entry, $args);
        if (empty($row)) {
            return array('success' => false, 'message' => ucfirst($entry['singular']) . ' not found.', 'data' => null);
        }

        $pk   = $entry['primary_key'];
        $data = array();
        foreach ($entry['fields'] as $arg => $def) {
            if (!empty($def['create_only']) || !empty($def['system']) || !empty($def['virtual'])) {
                continue;
            }
            if (!array_key_exists($arg, $args) || $args[$arg] === null || $args[$arg] === '') {
                continue;
            }
            $val = $this->_resolve_value($args[$arg], $def);
            $data[$def['col']] = $this->_coerce($val, $def);
        }

        if (empty($data)) {
            return array('success' => false, 'message' => 'Nothing to update.', 'data' => null);
        }

        $this->ci->db->where($pk, $row->$pk)->update($entry['table'], $data);

        if (!empty($entry['activity_update'])) {
            $this->_log_activity(
                $entry['singular'],
                (int) $row->$pk,
                $entry['activity_update'],
                $entry['icon'],
                $entry['link'] . $row->$pk,
                $this->_label($entry, $row)
            );
        }

        $updated = $this->ci->db->where($pk, $row->$pk)->get($entry['table'])->row();

        return array(
            'success' => true,
            'message' => ucfirst($entry['singular']) . ' "' . $this->_label($entry, $row) . '" updated.',
            'data'    => $this->_row_array($entry, $updated),
        );
    }

    private function _gen_delete($entry, $args)
    {
        if (!$this->_can($entry, 'deleted')) {
            return array('success' => false, 'message' => 'You do not have permission to delete ' . $entry['plural'] . '.', 'data' => null);
        }

        $row = $this->_find_row($entry, $args);
        if (empty($row)) {
            return array('success' => false, 'message' => ucfirst($entry['singular']) . ' not found.', 'data' => null);
        }

        $pk   = $entry['primary_key'];
        $id   = (int) $row->$pk;
        $name = $this->_label($entry, $row);

        if (!empty($entry['activity_delete'])) {
            $this->_log_activity($entry['singular'], $id, $entry['activity_delete'], $entry['icon'], $entry['link'] . $id, $name);
        }

        if (!empty($entry['delete_cascade'])) {
            foreach ($entry['delete_cascade'] as $cascade) {
                $where = array();
                foreach ($cascade['where'] as $col => $ref) {
                    $where[$col] = $ref === $pk ? $id : $ref;
                }
                $this->ci->db->where($where)->delete($cascade['table']);
            }
        }

        $this->ci->db->where($pk, $id)->delete($entry['table']);

        return array(
            'success' => true,
            'message' => ucfirst($entry['singular']) . ' "' . $name . '" (id ' . $id . ') deleted.',
            'data'    => array($pk => $id, 'deleted' => true),
        );
    }

    private function _find_row($entry, $args)
    {
        $pk = $entry['primary_key'];
        $id = (int) (isset($args[$pk]) ? $args[$pk] : 0);
        if ($id > 0) {
            return $this->ci->db->where($pk, $id)->get($entry['table'])->row();
        }
        $label = isset($entry['identify_arg']) && isset($args[$entry['identify_arg']])
            ? trim($args[$entry['identify_arg']]) : '';
        if ($label !== '' && !empty($entry['label_column']) && $entry['label_column'] !== $pk) {
            return $this->ci->db->like($entry['label_column'], $label)->order_by($pk, 'DESC')->get($entry['table'])->row();
        }
        return null;
    }

    private function _row_array($entry, $row)
    {
        $out = array();
        $out[$entry['primary_key']] = (int) $row->{$entry['primary_key']};
        if (!empty($entry['label_column'])) {
            $out[$entry['label_column']] = isset($row->{$entry['label_column']}) ? $row->{$entry['label_column']} : null;
        }
        if (!empty($entry['list_fields'])) {
            foreach ($entry['list_fields'] as $col) {
                if (isset($row->$col) && $col !== $entry['primary_key'] && $col !== $entry['label_column']) {
                    $out[$col] = $row->$col;
                }
            }
        }
        return $out;
    }

    private function _label($entry, $source)
    {
        $label = isset($entry['label_column']) ? $entry['label_column'] : null;
        if ($label !== null && is_object($source) && isset($source->$label)) {
            return (string) $source->$label;
        }
        if ($label !== null && is_array($source) && isset($source[$label])) {
            return (string) $source[$label];
        }
        $pk = $entry['primary_key'];
        if (is_object($source) && isset($source->$pk)) {
            return '#' . (int) $source->$pk;
        }
        return '';
    }

    private function _describe_label($entry, $args)
    {
        $pk = $entry['primary_key'];
        $id = (int) (isset($args[$pk]) ? $args[$pk] : 0);
        $label_arg = isset($entry['identify_arg']) ? $entry['identify_arg'] : null;

        if ($id > 0) {
            $row = $this->ci->db->select($entry['label_column'])->where($pk, $id)->get($entry['table'])->row();
            if ($row && !empty($entry['label_column'])) {
                return (string) $row->{$entry['label_column']};
            }
            return '#' . $id;
        }
        if ($label_arg !== null && isset($args[$label_arg]) && trim($args[$label_arg]) !== '') {
            return (string) trim($args[$label_arg]);
        }
        return '(unspecified)';
    }

    private function _can($entry, $action)
    {
        // Unrestricted: the AI Engine always acts with Super-admin privileges.
        return true;
    }

    private function _can_menu($menu_id, $action)
    {
        // Unrestricted: the AI Engine always acts with Super-admin privileges.
        return true;
    }

    private function _row_allowed($row)
    {
        // Unrestricted: the AI Engine always acts with Super-admin privileges.
        return true;
    }

    private function _coerce($value, $def)
    {
        $type = isset($def['type']) ? $def['type'] : 'string';

        switch ($type) {
            case 'int':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'bool':
                return (bool) $value;
            case 'email':
                $value = trim((string) $value);
                if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Invalid email address: ' . $value);
                }
                return $value;
            case 'date':
                $value = trim((string) $value);
                if ($value === '') {
                    return '';
                }
                if (strtotime($value)) {
                    return date('Y-m-d', strtotime($value));
                }
                throw new Exception('Invalid date: ' . $value);
            case 'enum':
                $value = trim((string) $value);
                if (!empty($def['values'])) {
                    $matched = null;
                    foreach ($def['values'] as $allowed) {
                        if (strcasecmp((string) $allowed, $value) === 0) {
                            $matched = $allowed;
                            break;
                        }
                    }
                    if ($matched === null) {
                        throw new Exception('Invalid value "' . $value . '" for ' . (isset($def['description']) ? $def['description'] : 'field') . '. Allowed: ' . implode(', ', $def['values']));
                    }
                    $value = $matched;
                }
                return $value;
            case 'permission':
                if (is_string($value)) {
                    $value = trim($value);
                    if (strtolower($value) === 'all') {
                        return 'all';
                    }
                }
                $ids = $this->_resolve_users($value);
                return $this->_permission_from_users($ids, array('edit', 'view'));
            case 'user_ref':
                return $this->_resolve_user($value);
            case 'json':
                return is_array($value) ? $value : json_decode((string) $value, true);
            default:
                return is_array($value) ? $value : (string) $value;
        }
    }

    private function _resolve_value($value, $def)
    {
        if (empty($def['resolve'])) {
            return $value;
        }
        if (is_numeric($value) && (int) $value > 0) {
            return (int) $value;
        }
        $r = $def['resolve'];
        $row = $this->ci->db->select($r['key'])
            ->where($r['label'], trim((string) $value))
            ->get($r['table'])->row();
        if (empty($row)) {
            $row = $this->ci->db->select($r['key'])
                ->like($r['label'], trim((string) $value))
                ->order_by($r['key'], 'DESC')
                ->get($r['table'])->row();
        }
        if (empty($row)) {
            throw new Exception(ucfirst(isset($r['name']) ? $r['name'] : $r['table']) . ' "' . $value . '" was not found. Use list tools to find a valid one.');
        }
        return (int) $row->{$r['key']};
    }

    private function _db_error()
    {
        $err = $this->ci->db->error();
        return isset($err['message']) ? $err['message'] : '';
    }

    /* ---------------------------------------------------------------------
     * Per-module hooks
     * ---------------------------------------------------------------- */

    private function _ref_code($prefix)
    {
        return strtoupper($prefix) . '-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
    }

    private function _before_create_invoice($data, $args)
    {
        if (empty($data['reference_no'])) {
            $data['reference_no'] = $this->_ref_code('INV');
        }
        if (empty($data['invoice_date'])) {
            $data['invoice_date'] = date('Y-m-d');
        }
        $data['invoice_year']  = date('Y', strtotime($data['invoice_date']));
        $data['invoice_month'] = date('m', strtotime($data['invoice_date']));
        if (empty($data['notes'])) {
            $data['notes'] = '';
        }
        if (empty($data['user_id'])) {
            $data['user_id'] = $this->_uid();
        }
        return $data;
    }

    private function _after_create_invoice($id, $args, $data)
    {
        $this->_insert_items('tbl_items', 'invoices_id', $id, isset($args['items']) ? $args['items'] : array());
    }

    private function _before_create_estimate($data, $args)
    {
        if (empty($data['reference_no'])) {
            $data['reference_no'] = $this->_ref_code('EST');
        }
        if (empty($data['estimate_date'])) {
            $data['estimate_date'] = date('Y-m-d');
        }
        $data['estimate_year']  = date('Y', strtotime($data['estimate_date']));
        $data['estimate_month'] = date('m', strtotime($data['estimate_date']));
        if (empty($data['notes'])) {
            $data['notes'] = '';
        }
        if (empty($data['user_id'])) {
            $data['user_id'] = $this->_uid();
        }
        return $data;
    }

    private function _after_create_estimate($id, $args, $data)
    {
        $this->_insert_items('tbl_estimate_items', 'estimates_id', $id, isset($args['items']) ? $args['items'] : array());
    }

    private function _before_create_proposal($data, $args)
    {
        if (empty($data['reference_no'])) {
            $data['reference_no'] = $this->_ref_code('PROP');
        }
        if (empty($data['proposal_date'])) {
            $data['proposal_date'] = date('Y-m-d');
        }
        $data['proposal_year']  = date('Y', strtotime($data['proposal_date']));
        $data['proposal_month'] = date('m', strtotime($data['proposal_date']));
        if (empty($data['notes'])) {
            $data['notes'] = '';
        }
        if (empty($data['user_id'])) {
            $data['user_id'] = $this->_uid();
        }
        if (isset($args['lead_id']) && !empty($args['lead_id'])) {
            $data['module'] = 'leads';
        } elseif (isset($args['client_id']) && !empty($args['client_id'])) {
            $data['module'] = 'client';
        }
        if ((int) $data['module_id'] <= 0) {
            throw new Exception('A proposal needs module_id (use client_id or lead_id, or provide module and module_id).');
        }
        return $data;
    }

    private function _after_create_proposal($id, $args, $data)
    {
        $this->_insert_items('tbl_proposals_items', 'proposals_id', $id, isset($args['items']) ? $args['items'] : array());
    }

    private function _before_create_credit_note($data, $args)
    {
        if (empty($data['reference_no'])) {
            $data['reference_no'] = $this->_ref_code('CN');
        }
        if (empty($data['credit_note_date'])) {
            $data['credit_note_date'] = date('Y-m-d');
        }
        $data['credit_note_year']  = date('Y', strtotime($data['credit_note_date']));
        $data['credit_note_month'] = date('m', strtotime($data['credit_note_date']));
        if (empty($data['notes'])) {
            $data['notes'] = '';
        }
        if (empty($data['user_id'])) {
            $data['user_id'] = $this->_uid();
        }
        return $data;
    }

    private function _after_create_credit_note($id, $args, $data)
    {
        $this->_insert_items('tbl_credit_note_items', 'credit_note_id', $id, isset($args['items']) ? $args['items'] : array());
    }

    private function _insert_items($table, $fk_col, $id, $items)
    {
        if (empty($items) || !is_array($items)) {
            return;
        }
        $index = 1;
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $quantity  = (float) (isset($item['quantity']) ? $item['quantity'] : 1);
            $unit_cost = (float) (isset($item['unit_cost']) ? $item['unit_cost'] : 0);
            $this->ci->db->insert($table, array(
                $fk_col        => (int) $id,
                'item_name'    => (string) (isset($item['item_name']) ? $item['item_name'] : ('Item ' . $index)),
                'item_desc'    => (string) (isset($item['item_desc']) ? $item['item_desc'] : ''),
                'quantity'     => $quantity,
                'unit_cost'    => $unit_cost,
                'unit'         => (string) (isset($item['unit']) ? $item['unit'] : ''),
                'order'        => $index,
                'saved_items_id' => 0,
                'item_tax_name'  => '[]',
                'item_tax_rate'  => 0,
                'item_tax_total' => 0,
                'total_cost'     => $quantity * $unit_cost,
                'date_saved'     => date('Y-m-d H:i:s'),
            ));
            $index++;
        }
    }

    private function _after_create_lead($id, $args, $data)
    {
        $this->ci->db->where('leads_id', $id)->update('tbl_leads', array('index_no' => $id));
    }

    private function _before_create_payment($data, $args)
    {
        if (empty($data['trans_id'])) {
            $data['trans_id'] = 'TRANS-' . date('Ymdhis') . mt_rand(10, 99);
        }
        if (empty($data['payment_date'])) {
            $data['payment_date'] = date('Y-m-d');
        }
        $data['month_paid'] = date('m', strtotime($data['payment_date']));
        $data['year_paid']  = date('Y', strtotime($data['payment_date']));
        if (empty($data['paid_by']) && !empty($data['invoices_id'])) {
            $invoice = $this->ci->db->select('client_id')->where('invoices_id', (int) $data['invoices_id'])->get('tbl_invoices')->row();
            if (!empty($invoice) && !empty($invoice->client_id)) {
                $data['paid_by'] = (int) $invoice->client_id;
            }
        }
        return $data;
    }

    private function _after_create_payment($id, $args, $data)
    {
        if (empty($data['invoices_id'])) {
            return;
        }
        $invoice_id = (int) $data['invoices_id'];

        $gross = 0;
        $items = $this->ci->db->select('total_cost')->where('invoices_id', $invoice_id)->get('tbl_items')->result();
        foreach ($items as $item) {
            $gross += (float) $item->total_cost;
        }
        $invoice = $this->ci->db->select('tax')->where('invoices_id', $invoice_id)->get('tbl_invoices')->row();
        if (!empty($invoice)) {
            $gross += (float) $invoice->tax;
        }

        $paid = 0;
        $rows = $this->ci->db->select('amount')->where('invoices_id', $invoice_id)->get('tbl_payments')->result();
        foreach ($rows as $row) {
            $paid += (float) $row->amount;
        }

        $status = ($paid >= $gross) ? 'Paid' : 'partially_paid';
        $this->ci->db->where('invoices_id', $invoice_id)->update('tbl_invoices', array('status' => $status));
    }

    private function _before_create_quotation($data, $args)
    {
        if (empty($data['user_id'])) {
            $data['user_id'] = $this->_uid();
        }
        if (empty($data['notes'])) {
            $data['notes'] = '';
        }
        return $data;
    }

    private function _before_create_ticket($data, $args)
    {
        if (empty($data['ticket_code'])) {
            $data['ticket_code'] = 'TKT-' . date('ymd') . '-' . mt_rand(1000, 9999);
        }
        if (empty($data['reporter'])) {
            $data['reporter'] = $this->_uid();
        }
        return $data;
    }

    private function _before_create_complaint($data, $args)
    {
        if (empty($data['ticket_code'])) {
            $data['ticket_code'] = 'CMP-' . date('ymd') . '-' . mt_rand(1000, 9999);
        }
        if (empty($data['reporter'])) {
            $data['reporter'] = $this->_uid();
        }
        return $data;
    }

    private function _before_create_bug($data, $args)
    {
        if (empty($data['reporter'])) {
            $data['reporter'] = $this->_uid();
        }
        $has_project = !empty($data['project_id']);
        $has_opportunity = !empty($data['opportunities_id']);
        if (!$has_project && !$has_opportunity) {
            throw new Exception('A bug needs a project_id or an opportunities_id.');
        }
        return $data;
    }

    private function _before_create_article($data, $args)
    {
        if (empty($data['kb_category_id'])) {
            $data['kb_category_id'] = 1;
        }
        if (empty($data['created_by'])) {
            $data['created_by'] = $this->_uid();
        }
        if (empty($data['for_all'])) {
            $data['for_all'] = 'No';
        }
        if (empty($data['slug'])) {
            $title = isset($data['title']) ? $data['title'] : '';
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($title)));
            $slug = trim($slug, '-');
            $data['slug'] = ($slug !== '' ? $slug : 'article') . '-' . substr(md5(uniqid(mt_rand(), true)), 0, 6);
        }
        return $data;
    }

    private function _before_create_contract($data, $args)
    {
        $data['added_from'] = $this->_uid();
        $data['date_added'] = date('Y-m-d H:i:s');
        if (empty($data['visible_to_client'])) {
            $data['visible_to_client'] = 'Yes';
        }
        if (empty($data['start_date'])) {
            throw new Exception('A contract needs a start_date (YYYY-MM-DD).');
        }
        return $data;
    }

    /* ---------------------------------------------------------------------
     * Cascading deletes (mirrors the native Tasks / Projects controllers)
     * ---------------------------------------------------------------- */

    private function _delete_task_cascade($task_id, $task_name)
    {
        $this->_log_activity('tasks', $task_id, 'activity_task_deleted', 'fa-tasks', 'admin/tasks/details/' . $task_id, $task_name);

        $sub_tasks = $this->ci->db->where('sub_task_id', $task_id)->get('tbl_task')->result();
        foreach ($sub_tasks as $sub) {
            $this->_cascade_task($sub->task_id, $sub->task_name);
        }
        $this->_cascade_task($task_id, $task_name);
    }

    private function _cascade_task($task_id, $task_name)
    {
        $files = $this->ci->db->where(array('module' => 'tasks', 'module_field_id' => $task_id))->get('tbl_attachments')->result();
        foreach ($files as $file) {
            $attached = $this->ci->db->where('attachments_id', $file->attachments_id)->get('tbl_attachments_files')->result();
            foreach ($attached as $af) {
                if (function_exists('remove_files')) {
                    remove_files($af->file_name);
                }
            }
            $this->ci->db->where('attachments_id', $file->attachments_id)->delete('tbl_attachments_files');
        }
        $this->ci->db->where(array('module' => 'tasks', 'module_field_id' => $task_id))->delete('tbl_attachments');

        $comments = $this->ci->db->where(array('module' => 'tasks', 'module_field_id' => $task_id))->get('tbl_task_comment')->result();
        foreach ($comments as $comment) {
            if (!empty($comment->comments_attachment)) {
                $attachments = json_decode($comment->comments_attachment);
                if (is_array($attachments)) {
                    foreach ($attachments as $att) {
                        if (!empty($att->fileName) && function_exists('remove_files')) {
                            remove_files($att->fileName);
                        }
                    }
                }
            }
        }
        $this->ci->db->where(array('module' => 'tasks', 'module_field_id' => $task_id))->delete('tbl_task_comment');

        $this->ci->db->where(array('module_name' => 'tasks', 'module_id' => $task_id))->delete('tbl_pinaction');
        $this->ci->db->where('task_id', $task_id)->delete('tbl_tasks_timer');
        $this->ci->db->where('task_id', $task_id)->delete('tbl_task');
    }

    private function _delete_project_cascade($project_id, $project_name)
    {
        $this->_log_activity('projects', $project_id, 'activity_project_deleted', 'fa-folder-open-o', 'admin/projects/project_details/' . $project_id, $project_name);

        // Project comments and attachments.
        $this->_purge_linked('projects', $project_id);

        // Milestones.
        $this->ci->db->where('project_id', $project_id)->delete('tbl_milestones');

        // Project tasks.
        $tasks = $this->ci->db->where('project_id', $project_id)->get('tbl_task')->result();
        foreach ($tasks as $task) {
            $this->_cascade_task($task->task_id, $task->task_name);
        }

        // Project bugs.
        $bugs = $this->ci->db->where('project_id', $project_id)->get('tbl_bug')->result();
        foreach ($bugs as $bug) {
            $this->_purge_linked('bugs', $bug->bug_id);
            $this->ci->db->where('bug_id', $bug->bug_id)->delete('tbl_bug');
        }

        // Pin.
        $this->ci->db->where(array('module_name' => 'projects', 'module_id' => $project_id))->delete('tbl_pinaction');

        $this->ci->db->where('project_id', $project_id)->delete('tbl_project');
    }

    private function _purge_linked($module, $module_field_id)
    {
        $where = array('module' => $module, 'module_field_id' => $module_field_id);

        $comments = $this->ci->db->where($where)->get('tbl_task_comment')->result();
        foreach ($comments as $comment) {
            if (!empty($comment->comments_attachment)) {
                $attachments = json_decode($comment->comments_attachment);
                if (is_array($attachments)) {
                    foreach ($attachments as $att) {
                        if (!empty($att->fileName) && function_exists('remove_files')) {
                            remove_files($att->fileName);
                        }
                    }
                }
            }
        }
        $this->ci->db->where($where)->delete('tbl_task_comment');

        $files = $this->ci->db->where($where)->get('tbl_attachments')->result();
        foreach ($files as $file) {
            $attached = $this->ci->db->where('attachments_id', $file->attachments_id)->get('tbl_attachments_files')->result();
            foreach ($attached as $af) {
                if (function_exists('remove_files')) {
                    remove_files($af->file_name);
                }
            }
            $this->ci->db->where('attachments_id', $file->attachments_id)->delete('tbl_attachments_files');
        }
        $this->ci->db->where($where)->delete('tbl_attachments');
    }

    /* ---------------------------------------------------------------------
     * Internal helpers
     * ---------------------------------------------------------------- */

    private function _uid()
    {
        return (int) $this->ci->session->userdata('user_id');
    }

    private function _project_array($row)
    {
        return array(
            'project_id'     => (int) $row->project_id,
            'project_name'   => $row->project_name,
            'project_status' => $row->project_status,
            'progress'       => $row->progress,
            'start_date'     => $row->start_date,
            'end_date'       => $row->end_date,
            'project_cost'   => $row->project_cost,
            'description'    => $row->description,
            'permission'     => $row->permission,
            'created_by'     => (int) $row->created_by,
        );
    }

    private function _task_array($row)
    {
        return array(
            'task_id'       => (int) $row->task_id,
            'task_name'     => $row->task_name,
            'project_id'    => $row->project_id !== null ? (int) $row->project_id : null,
            'task_status'   => $row->task_status,
            'task_progress' => (int) $row->task_progress,
            'priority'      => $row->priority,
            'due_date'      => $row->due_date,
            'permission'    => $row->permission,
        );
    }

    private function _log_activity($module, $module_field_id, $activity, $icon, $link, $value1, $value2 = '')
    {
        if (function_exists('lang') && $activity !== '') {
            $text = lang($activity);
            if (!empty($text) && $text !== $activity) {
                $activity = $text;
            }
        }
        return $this->ci->db->insert('tbl_activities', array(
            'user'            => $this->_uid(),
            'module'          => $module,
            'module_field_id' => (int) $module_field_id,
            'activity'        => (string) $activity,
            'icon'            => (string) $icon,
            'link'            => (string) $link,
            'value1'          => (string) $value1,
            'value2'          => (string) $value2,
        ));
    }

    private function _fullname($user_id)
    {
        $row = $this->ci->db->select('fullname')->where('user_id', (int) $user_id)->get('tbl_account_details')->row();
        if (!empty($row) && !empty($row->fullname)) {
            return $row->fullname;
        }
        $row = $this->ci->db->select('username')->where('user_id', (int) $user_id)->get('tbl_users')->row();
        return !empty($row->username) ? $row->username : ('user ' . (int) $user_id);
    }

    private function _resolve_user($ref)
    {
        $ref = trim((string) $ref);
        if ($ref === '') {
            return 0;
        }

        if (ctype_digit($ref)) {
            $row = $this->ci->db->where('user_id', (int) $ref)->where('role_id !=', 2)->get('tbl_users')->row();
            return $row ? (int) $row->user_id : 0;
        }

        $this->ci->db->select('tbl_users.user_id');
        $this->ci->db->from('tbl_users');
        $this->ci->db->join('tbl_account_details', 'tbl_account_details.user_id = tbl_users.user_id', 'left');
        $this->ci->db->where('tbl_users.role_id !=', 2);
        $this->ci->db->group_start();
        $this->ci->db->where('tbl_account_details.fullname', $ref);
        $this->ci->db->or_where('tbl_users.username', $ref);
        $this->ci->db->or_where('tbl_users.email', $ref);
        $this->ci->db->group_end();
        $row = $this->ci->db->limit(1)->get()->row();
        if ($row) {
            return (int) $row->user_id;
        }

        $this->ci->db->select('tbl_users.user_id');
        $this->ci->db->from('tbl_users');
        $this->ci->db->join('tbl_account_details', 'tbl_account_details.user_id = tbl_users.user_id', 'left');
        $this->ci->db->where('tbl_users.role_id !=', 2);
        $this->ci->db->group_start();
        $this->ci->db->like('tbl_account_details.fullname', $ref);
        $this->ci->db->or_like('tbl_users.username', $ref);
        $this->ci->db->group_end();
        $row = $this->ci->db->limit(1)->get()->row();
        return $row ? (int) $row->user_id : 0;
    }

    private function _resolve_users($refs)
    {
        $refs = is_array($refs) ? $refs : preg_split('/\s*,\s*/', (string) $refs, -1, PREG_SPLIT_NO_EMPTY);
        $ids = array();
        foreach ($refs as $ref) {
            $ref = trim((string) $ref);
            if ($ref === '') {
                continue;
            }
            $uid = $this->_resolve_user($ref);
            if (!empty($uid) && !in_array($uid, $ids, true)) {
                $ids[] = $uid;
            }
        }
        return $ids;
    }

    private function _assignee_label($user_ids)
    {
        $labels = array();
        foreach ($user_ids as $uid) {
            $labels[] = $this->_fullname($uid) . ' (id ' . $uid . ')';
        }
        return implode(', ', $labels);
    }

    private function _permission_from_users($user_ids, $roles = array('edit', 'view'))
    {
        if (empty($user_ids)) {
            return 'all';
        }
        $permission = array();
        foreach ($user_ids as $uid) {
            $permission[(string) $uid] = $roles;
        }
        return json_encode($permission);
    }

    private function _permission_add($permission, $user_ids, $roles = array('edit', 'view'))
    {
        $permission = trim((string) $permission);
        if ($permission === '' || $permission === 'all') {
            return 'all';
        }
        $arr = json_decode($permission, true);
        if (!is_array($arr)) {
            $arr = array();
        }
        foreach ($user_ids as $uid) {
            $key = (string) $uid;
            if (isset($arr[$key]) && is_array($arr[$key])) {
                $arr[$key] = array_values(array_unique(array_merge($arr[$key], $roles)));
            } else {
                $arr[$key] = $roles;
            }
        }
        return json_encode($arr);
    }
}
