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
     * Build the HTML message for a newly created task or project.
     *
     * @param string $type 'task' or 'project'
     * @param int $id Item id (used for the link)
     * @param string $name Item title
     * @param string $due_date Due/end date (optional)
     * @return string
     */
    function telegram_build_created_message($type, $id, $name, $due_date = '')
    {
        $is_task = ($type === 'task');
        $label = $is_task ? 'Task' : 'Project';

        $message = '<b>📢 New ' . $label . ' Created!</b>' . PHP_EOL;
        $message .= '<b>Title:</b> ' . telegram_escape($name) . PHP_EOL;
        if (!empty($due_date) && $due_date !== '0000-00-00') {
            $message .= '<b>' . ($is_task ? 'Due Date' : 'End Date') . ':</b> ' . telegram_escape($due_date) . PHP_EOL;
        }
        $link = $is_task
            ? site_url('admin/tasks/details/' . (int)$id)
            : site_url('admin/projects/project_details/' . (int)$id);
        $message .= '<b>Link:</b> <a href="' . $link . '">View ' . $label . '</a>';
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
