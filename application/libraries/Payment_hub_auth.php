<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Payment_hub_auth Library
 *
 * Authenticates incoming requests from external projects to the Payment Hub API.
 *
 * =============================================================================
 * AUTHENTICATION FLOW (all requests)
 * =============================================================================
 *
 * 1. External project includes bearer token in the Authorization header:
 *      Authorization: Bearer phb_a1b2c3d4...
 *
 * 2. Optionally (strongly recommended): HMAC-sign the request body:
 *      X-Hub-Timestamp: 1709452361
 *      X-Hub-Signature: <HMAC-SHA256(timestamp + "." + raw_body, signing_secret)>
 *
 * 3. The auth library:
 *    a. Extracts the bearer token from the header
 *    b. SHA-256 hashes it and looks it up in `tbl_api_tokens`
 *    c. Verifies: token is active, project is active, not expired, IP allowed
 *    d. If signature headers present → verifies HMAC (replay protection ±5 min)
 *    e. Returns the authenticated project context to the controller
 *
 * =============================================================================
 * BACKWARD COMPATIBILITY
 * =============================================================================
 * The old X-API-Key / X-API-Secret header flow is still accepted as a fallback
 * for projects that haven't migrated yet, but callers should migrate to tokens.
 *
 * =============================================================================
 */
class Payment_hub_auth
{
    /** @var \CI_Controller */
    protected $ci;

    /** @var object|null  Authenticated project record after ->authenticate() */
    protected $project = null;

    /** @var object|null  Authenticated token record */
    protected $token = null;

    /** @var bool  Whether HMAC signature was presented and verified */
    protected $hmac_verified = false;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->model('api_tokens_model');
        $this->ci->load->model('payment_projects_model');
    }

    // -----------------------------------------------------------------------
    // PUBLIC API
    // -----------------------------------------------------------------------

    /**
     * Authenticate the current request.
     *
     * On failure, immediately sends a 401 JSON response and halts execution.
     * On success, returns the project object and stores it in $this->project.
     *
     * @param  bool $require_hmac  If true, HMAC signature is mandatory.
     * @return object  Authenticated project row.
     */
    public function authenticate(bool $require_hmac = false): object
    {
        $client_ip = $this->_get_client_ip();

        // 1. Try bearer token (preferred)
        $raw_token = $this->_extract_bearer_token();

        if (!empty($raw_token)) {
            $token_row = $this->ci->api_tokens_model->authenticate($raw_token, $client_ip);

            if (empty($token_row)) {
                $this->_deny('Invalid or expired token', 401);
            }

            $this->token = $token_row;

            // 2. Verify HMAC signature (if provided or required)
            $this->_check_hmac($token_row->signing_secret, $require_hmac);

            // Build a project-like object from the joined token row
            $this->project = $this->_project_from_token($token_row);
            return $this->project;
        }

        // 3. Fallback: legacy X-API-Key / X-API-Secret headers (deprecated)
        $api_key    = $this->ci->input->server('HTTP_X_API_KEY');
        $api_secret = $this->ci->input->server('HTTP_X_API_SECRET');

        if (!empty($api_key) && !empty($api_secret)) {
            $project = $this->ci->payment_projects_model->authenticate($api_key, $api_secret);

            if (empty($project)) {
                $this->_deny('Invalid API credentials', 401);
            }

            $this->project = $project;
            return $this->project;
        }

        // 4. Nothing presented
        $this->_deny('Authentication required. Provide a Bearer token or X-API-Key / X-API-Secret headers.', 401);
    }

    /**
     * Returns the authenticated project after authenticate() has been called.
     */
    public function get_project(): ?object
    {
        return $this->project;
    }

    /**
     * Returns the authenticated token row, or null (legacy key flow).
     */
    public function get_token(): ?object
    {
        return $this->token;
    }

    /**
     * Whether the request carried a verified HMAC signature.
     */
    public function hmac_verified(): bool
    {
        return $this->hmac_verified;
    }

    // -----------------------------------------------------------------------
    // INTERNAL HELPERS
    // -----------------------------------------------------------------------

    /**
     * Extract the raw bearer token from "Authorization: Bearer <token>".
     */
    protected function _extract_bearer_token(): ?string
    {
        $header = $this->ci->input->server('HTTP_AUTHORIZATION');

        if (empty($header)) {
            // Apache sometimes strips Authorization; check apache_request_headers()
            if (function_exists('apache_request_headers')) {
                $h = apache_request_headers();
                $header = $h['Authorization'] ?? $h['authorization'] ?? null;
            }
        }

        if (!empty($header) && preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
            return trim($m[1]);
        }

        // Also allow token in POST body as a fallback (not recommended)
        $body_token = $this->ci->input->post('api_token', true);
        return !empty($body_token) ? $body_token : null;
    }

    /**
     * Verify the X-Hub-Signature HMAC if the headers are present.
     *
     * @param  string $secret        The token's signing_secret.
     * @param  bool   $require_hmac  Abort on missing signature.
     */
    protected function _check_hmac(string $secret, bool $require_hmac): void
    {
        $signature = $this->ci->input->server('HTTP_X_HUB_SIGNATURE');
        $timestamp  = $this->ci->input->server('HTTP_X_HUB_TIMESTAMP');

        if (empty($signature) && empty($timestamp)) {
            if ($require_hmac) {
                $this->_deny('HMAC signature required. Provide X-Hub-Timestamp and X-Hub-Signature headers.', 401);
            }
            return; // Signature not presented — skip verification
        }

        if (empty($signature) || empty($timestamp)) {
            $this->_deny('Both X-Hub-Timestamp and X-Hub-Signature must be provided together.', 400);
        }

        $raw_body = file_get_contents('php://input');

        $valid = $this->ci->api_tokens_model->verify_hmac(
            $secret,
            $raw_body,
            $signature,
            $timestamp
        );

        if (!$valid) {
            $this->_deny('HMAC signature verification failed or timestamp is stale (>5 min).', 401);
        }

        $this->hmac_verified = true;
    }

    /**
     * Build a minimal project-like stdClass from a token row that already has
     * the project columns joined.
     */
    protected function _project_from_token(object $token_row): object
    {
        $obj = new stdClass();
        $obj->id           = $token_row->project_id;
        $obj->project_name = $token_row->project_name;
        $obj->webhook_url  = $token_row->webhook_url  ?? null;
        $obj->callback_url = $token_row->callback_url ?? null;
        $obj->status       = $token_row->project_status;
        return $obj;
    }

    /**
     * Get the real client IP, accounting for trusted proxies.
     */
    protected function _get_client_ip(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'] as $key) {
            $val = $this->ci->input->server($key);
            if (!empty($val)) {
                // X-Forwarded-For may be a comma-separated list
                $ip = trim(explode(',', $val)[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }

    /**
     * Halt with a JSON error response.
     */
    protected function _deny(string $message, int $http_code = 401): void
    {
        $this->ci->output
            ->set_status_header($http_code)
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => false, 'message' => $message]))
            ->_display();
        exit;
    }
}
