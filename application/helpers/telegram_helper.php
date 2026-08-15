<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('send_telegram_notification')) {
    /**
     * Send a message to a Telegram chat via the configured bot.
     * Fails silently (logs the error) so the ERP request is never broken.
     *
     * @param string $chat_id Recipient chat id (user DM or group). Empty = no-op.
     * @param string $message Message text sent with parse_mode=HTML.
     * @return bool True on success, false on failure.
     */
    function send_telegram_notification($chat_id, $message)
    {
        if (empty($chat_id)) {
            return false;
        }
        $token = config_item('telegram_bot_token');
        if (empty($token)) {
            log_message('error', 'send_telegram_notification: telegram_bot_token is not configured');
            return false;
        }
        if (!function_exists('curl_init')) {
            log_message('error', 'send_telegram_notification: cURL extension is not available');
            return false;
        }

        $url = 'https://api.telegram.org/bot' . $token . '/sendMessage';
        $payload = http_build_query(array(
            'chat_id' => $chat_id,
            'text' => $message,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ));

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
        ));
        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($errno !== 0) {
            log_message('error', 'send_telegram_notification cURL error ' . $errno . ' (chat_id ' . $chat_id . '): ' . $error);
            return false;
        }

        $decoded = json_decode($response, true);
        if (empty($decoded['ok'])) {
            log_message('error', 'send_telegram_notification API error (chat_id ' . $chat_id . '): ' . substr((string)$response, 0, 500));
            return false;
        }
        return true;
    }
}

if (!function_exists('telegram_escape')) {
    /**
     * Escape a value for Telegram HTML parse mode.
     *
     * @param mixed $value
     * @return string
     */
    function telegram_escape($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('telegram_should_send_to_group')) {
    /**
     * Decide whether a message should go to the configured group chat.
     *
     * True for 'all', empty, null, '{}', and any permission value that is not
     * a non-empty JSON object of assigned users.
     *
     * @param mixed $permission Raw permission column value
     * @return bool
     */
    function telegram_should_send_to_group($permission)
    {
        if ($permission === 'all' || $permission === '' || $permission === null || $permission === '{}') {
            return true;
        }
        $assigned = json_decode($permission, true);
        if (!is_array($assigned) || empty($assigned)) {
            return true;
        }
        return false;
    }
}

if (!function_exists('telegram_build_created_message')) {
    /**
     * Build the HTML message for a newly created task or project with full details.
     *
     * @param string $type 'task' or 'project'
     * @param int $id Item id (used for fetching details & link)
     * @param string $name Item title
     * @param string $due_date Due/end date (optional)
     * @return string
     */
    function telegram_build_created_message($type, $id, $name, $due_date = '')
    {
        $is_task = ($type === 'task');
        $label = $is_task ? 'Task' : 'Project';

        $details = array();
        $CI = function_exists('get_instance') ? get_instance() : null;

        if ($is_task && !empty($id) && $CI && isset($CI->db) && method_exists($CI->db, 'where')) {
            try {
                $task = $CI->db->where('task_id', (int)$id)->get('tbl_task')->row();
                if ($task) {
                    if (!empty($task->task_description)) {
                        $details['description'] = trim(strip_tags($task->task_description));
                    }
                    if (!empty($task->task_start_date) && $task->task_start_date !== '0000-00-00') {
                        $details['start_date'] = $task->task_start_date;
                    }
                    if (!empty($task->due_date) && $task->due_date !== '0000-00-00') {
                        $details['due_date'] = $task->due_date;
                    }
                    if (!empty($task->task_status)) {
                        $details['status'] = ucwords(str_replace('_', ' ', $task->task_status));
                    }
                    if (!empty($task->priority)) {
                        $details['priority'] = ucfirst($task->priority);
                    }
                    
                    // Fetch Creator Name
                    if (!empty($task->created_by)) {
                        $creator = $CI->db->select('fullname')->where('user_id', (int)$task->created_by)->get('tbl_account_details')->row();
                        if ($creator && !empty($creator->fullname)) {
                            $details['created_by'] = $creator->fullname;
                        }
                    }

                    // Fetch Project Name
                    if (!empty($task->project_id)) {
                        $project = $CI->db->select('project_name')->where('project_id', (int)$task->project_id)->get('tbl_project')->row();
                        if ($project && !empty($project->project_name)) {
                            $details['project_name'] = $project->project_name;
                        }
                    }

                    // Fetch Assigned Users
                    if (!empty($task->permission)) {
                        if ($task->permission === 'all') {
                            $details['assigned_to'] = 'Everyone';
                        } else {
                            $assigned_map = json_decode($task->permission, true);
                            if (is_array($assigned_map) && !empty($assigned_map)) {
                                $uids = array_map('intval', array_keys($assigned_map));
                                $users = $CI->db->select('fullname')->where_in('user_id', $uids)->get('tbl_account_details')->result();
                                $names = array();
                                foreach ($users as $u) {
                                    if (!empty($u->fullname)) $names[] = $u->fullname;
                                }
                                if (!empty($names)) {
                                    $details['assigned_to'] = implode(', ', $names);
                                }
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                // Ignore DB errors in fallback scenarios
            }
        }

        $link = $is_task
            ? site_url('admin/tasks/details/' . (int)$id)
            : site_url('admin/projects/project_details/' . (int)$id);

        $message = '<b>📢 New ' . $label . ' Created!</b>' . PHP_EOL . PHP_EOL;
        $message .= '<b>' . $label . ' ID:</b> #' . (int)$id . PHP_EOL;
        $message .= '<b>Title:</b> ' . telegram_escape($name) . PHP_EOL;

        if (!empty($details['status'])) {
            $message .= '<b>Status:</b> ' . telegram_escape($details['status']) . PHP_EOL;
        }
        if (!empty($details['priority'])) {
            $message .= '<b>Priority:</b> ' . telegram_escape($details['priority']) . PHP_EOL;
        }
        if (!empty($details['start_date'])) {
            $message .= '<b>Start Date:</b> ' . telegram_escape($details['start_date']) . PHP_EOL;
        }

        $final_due = !empty($details['due_date']) ? $details['due_date'] : $due_date;
        if (!empty($final_due) && $final_due !== '0000-00-00') {
            $message .= '<b>' . ($is_task ? 'Due Date' : 'End Date') . ':</b> ' . telegram_escape($final_due) . PHP_EOL;
        }

        if (!empty($details['project_name'])) {
            $message .= '<b>Project:</b> ' . telegram_escape($details['project_name']) . PHP_EOL;
        }
        if (!empty($details['created_by'])) {
            $message .= '<b>Created By:</b> ' . telegram_escape($details['created_by']) . PHP_EOL;
        }
        if (!empty($details['assigned_to'])) {
            $message .= '<b>Assigned To:</b> ' . telegram_escape($details['assigned_to']) . PHP_EOL;
        }
        if (!empty($details['description'])) {
            $desc = $details['description'];
            if (mb_strlen($desc) > 300) {
                $desc = mb_substr($desc, 0, 300) . '...';
            }
            $message .= '<b>Description:</b> ' . telegram_escape($desc) . PHP_EOL;
        }

        $message .= PHP_EOL . '<b>🔗 Task Link:</b>' . PHP_EOL;
        $message .= '<a href="' . $link . '">' . $link . '</a>';

        return $message;
    }
}

if (!function_exists('telegram_build_deleted_message')) {
    /**
     * Build the HTML message for a deleted task or project. No link (the
     * record no longer exists).
     *
     * @param string $type 'task' or 'project'
     * @param string $name Item title
     * @return string
     */
    function telegram_build_deleted_message($type, $name)
    {
        $label = ($type === 'task') ? 'Task' : 'Project';
        $message = '<b>🗑 ' . $label . ' Deleted!</b>' . PHP_EOL;
        $message .= '<b>Title:</b> ' . telegram_escape($name);
        return $message;
    }
}

if (!function_exists('telegram_deliver')) {
    /**
     * Fan out a message to Telegram recipients based on the permission value.
     *
     * - 'all' / empty / null / '{}' / invalid JSON  => configured group chat
     * - permission JSON                              => DM each assigned user
     *   who has a telegram_chat_id in tbl_users
     *
     * @param string $message Message text (HTML parse mode)
     * @param string $permission Raw permission column value
     * @return bool True if at least one message was accepted by the API
     */
    function telegram_deliver($message, $permission)
    {
        if (telegram_should_send_to_group($permission)) {
            return send_telegram_notification(config_item('telegram_group_id'), $message);
        }

        $assigned = json_decode($permission, true);
        $user_ids = array_map('intval', array_keys($assigned));
        if (empty($user_ids)) {
            return send_telegram_notification(config_item('telegram_group_id'), $message);
        }

        $CI = &get_instance();
        $CI->db->select('user_id, telegram_chat_id');
        $CI->db->where_in('user_id', $user_ids);
        $rows = $CI->db->get('tbl_users')->result();

        $sent = false;
        foreach ($rows as $row) {
            if (!empty($row->telegram_chat_id)) {
                if (send_telegram_notification($row->telegram_chat_id, $message)) {
                    $sent = true;
                }
            }
        }
        return $sent;
    }
}

if (!function_exists('telegram_notify_created')) {
    /**
     * Notify Telegram about a newly created task or project.
     *
     * @param string $type 'task' or 'project'
     * @param int $id Item id
     * @param string $name Item title
     * @param string $due_date Due/end date (optional)
     * @param string $permission Raw permission column value
     * @return bool
     */
    function telegram_notify_created($type, $id, $name, $due_date = '', $permission = '')
    {
        return telegram_deliver(telegram_build_created_message($type, $id, $name, $due_date), $permission);
    }
}

if (!function_exists('telegram_notify_deleted')) {
    /**
     * Notify Telegram about a deleted task or project.
     *
     * @param string $type 'task' or 'project'
     * @param int $id Item id (unused today, kept for API symmetry)
     * @param string $name Item title
     * @param string $permission Raw permission column value
     * @return bool
     */
    function telegram_notify_deleted($type, $id, $name, $permission = '')
    {
        return telegram_deliver(telegram_build_deleted_message($type, $name), $permission);
    }
}

if (!function_exists('telegram_notify_super_admins')) {
    /**
     * Mirror a topbar notification (tbl_notifications row) to every super admin
     * that has a Telegram chat id configured.
     *
     * The message text is built to mirror exactly what the ERP topbar renders
     * for the same row, so the super admin sees the same wording in Telegram.
     *
     * This is best-effort and NEVER breaks the calling request: every Telegram
     * API call is wrapped in a try/catch with an aggressive short timeout, and
     * any failure is logged and swallowed.
     *
     * @param array $data The notification row being inserted into tbl_notifications.
     *                     Expected keys: description, value, link, date, from_user_id, icon.
     * @return bool True if at least one message was accepted by the API.
     */
    function telegram_notify_super_admins($data)
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }
        // Master switch. If the mirror is disabled, do nothing.
        if (config_item('telegram_super_admin_notify') != '1') {
            return false;
        }
        $token = config_item('telegram_bot_token');
        if (empty($token)) {
            log_message('error', 'telegram_notify_super_admins: telegram_bot_token is not configured');
            return false;
        }

        $CI = &get_instance();
        if (!isset($CI->db) || !method_exists($CI->db, 'where')) {
            return false;
        }

        // --- Build the message text, mirroring the topbar rendering ---
        // notifications.php: $description = lang($notification->description, $notification->value);
        // if from_user_id != 0: $description = fullname($from_user_id) . ' - ' . $description;
        $description = '';
        if (!empty($data['description'])) {
            $description = function_exists('lang') ? lang($data['description'], !empty($data['value']) ? $data['value'] : '') : $data['description'];
        }
        if (!empty($data['from_user_id'])) {
            $from_name = function_exists('fullname') ? fullname($data['from_user_id']) : '';
            if (!empty($from_name)) {
                $description = $from_name . ' - ' . $description;
            }
        }

        $message = '<b>🔔 ' . telegram_escape($description) . '</b>' . PHP_EOL;
        if (!empty($data['date']) && function_exists('time_ago')) {
            $message .= '<i>' . telegram_escape(time_ago($data['date'])) . '</i>' . PHP_EOL;
        }
        $link = !empty($data['link']) ? $data['link'] : '';
        if (!empty($link)) {
            // links are stored as a relative site path; make absolute if needed
            if (strpos($link, 'http') !== 0 && function_exists('base_url')) {
                $link = rtrim(base_url(), '/') . '/' . ltrim($link, '/');
            }
            $message .= PHP_EOL . '🔗 <a href="' . telegram_escape($link) . '">' . telegram_escape($link) . '</a>';
        }

        // --- Resolve super admin recipients with a valid chat id ---
        // Super admin = user_id 1 (primary) OR is_super_admin = 1, and activated.
        $rows = $CI->db->select('user_id, telegram_chat_id')
            ->group_start()
                ->where('is_super_admin', 1)
                ->or_where('user_id', 1)
            ->group_end()
            ->where('activated', 1)
            ->where('telegram_chat_id IS NOT NULL', null, false)
            ->where('telegram_chat_id !=', '')
            ->get('tbl_users')
            ->result();

        if (empty($rows)) {
            return false;
        }

        $sent = false;
        foreach ($rows as $row) {
            if (empty($row->telegram_chat_id)) {
                continue;
            }
            try {
                if (send_telegram_notification($row->telegram_chat_id, $message)) {
                    $sent = true;
                }
            } catch (Exception $e) {
                log_message('error', 'telegram_notify_super_admins exception (user ' . $row->user_id . '): ' . $e->getMessage());
            }
        }
        return $sent;
    }
}
