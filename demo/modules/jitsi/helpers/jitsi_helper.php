<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('get_jitsi_config')) {
    /**
     * Get Jitsi configuration from tbl_config
     *
     * @return array
     */
    function get_jitsi_config()
    {
        $CI = &get_instance();
        $config = [
            'domain' => config_item('jitsi_domain') ?: 'https://meet.jit.si',
            'app_id' => config_item('jitsi_app_id') ?: '',
            'private_key' => '',
            'public_key' => config_item('jitsi_public_key') ?: '',
        ];

        $pk = config_item('jitsi_private_key');
        if (!empty($pk)) {
            $config['private_key'] = decrypt($pk);
        }

        return $config;
    }
}

if (!function_exists('generate_jitsi_token')) {
    /**
     * Generate a Jitsi JWT token for a meeting participant
     *
     * @param string $room Meeting room name
     * @param string $email Participant email
     * @param string $name Participant display name
     * @param bool $is_moderator Whether user is host
     * @param int $exp Expiration timestamp
     * @return string JWT token
     */
    function generate_jitsi_token($room, $email = '', $name = '', $is_moderator = false, $exp = null)
    {
        $CI = &get_instance();
        require_once(module_dirPath(JITSI_MODULE) . 'libraries/JitsiJWT.php');

        $config = get_jitsi_config();
        $jwt = new JitsiJWT($config);

        return $jwt->generateToken($room, $email, $name, $is_moderator, $exp);
    }
}

if (!function_exists('build_jitsi_url')) {
    /**
     * Build full Jitsi meeting URL with JWT token
     *
     * @param string $room Meeting room name
     * @param string $email Participant email
     * @param string $name Participant display name
     * @param bool $is_moderator Whether user is host
     * @param int $exp Expiration timestamp
     * @return string Full Jitsi URL
     */
    function build_jitsi_url($room, $email = '', $name = '', $is_moderator = false, $exp = null)
    {
        $CI = &get_instance();
        require_once(module_dirPath(JITSI_MODULE) . 'libraries/JitsiJWT.php');

        $config = get_jitsi_config();
        $jwt = new JitsiJWT($config);

        return $jwt->buildMeetingUrl($room, $email, $name, $is_moderator, $exp);
    }
}

if (!function_exists('is_jitsi_configured')) {
    /**
     * Check if Jitsi is properly configured
     *
     * @return bool
     */
    function is_jitsi_configured()
    {
        $config = get_jitsi_config();
        return !empty($config['domain']) && !empty($config['app_id']) && !empty($config['private_key']);
    }
}
