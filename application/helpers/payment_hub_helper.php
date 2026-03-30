<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('mask_sensitive_data')) {
    /**
     * Masks sensitive keys in an array or JSON string.
     * 
     * @param mixed $data Array or JSON string
     * @param array $sensitive_keys Keys to mask
     * @return mixed Masked data in same format as input
     */
    function mask_sensitive_data($data, $sensitive_keys = ['client_secret', 'token', 'raw_token', 'password', 'key', 'secret', 'signing_secret', 'api_key', 'api_secret', 'bearer_token'])
    {
        if (empty($data)) return $data;

        $is_json = false;
        $decoded = $data;

        if (is_string($data)) {
            $tmp = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $decoded = $tmp;
                $is_json = true;
            }
        }

        if (is_array($decoded)) {
            array_walk_recursive($decoded, function (&$value, $key) use ($sensitive_keys) {
                if (in_array(strtolower($key), $sensitive_keys) && !is_array($value)) {
                    $val_str = (string)$value;
                    if (strlen($val_str) > 8) {
                        $value = substr($val_str, 0, 4) . '****' . substr($val_str, -4);
                    } else {
                        $value = '********';
                    }
                }
            });
        }

        return $is_json ? json_encode($decoded) : $decoded;
    }
}
