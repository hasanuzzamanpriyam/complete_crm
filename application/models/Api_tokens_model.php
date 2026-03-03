<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Api_tokens_model
 *
 * Manages bearer tokens issued to external payment hub projects.
 *
 * Security design:
 *   - Raw token is generated once and shown to the user.  It is NEVER stored.
 *   - We store SHA-256(raw_token) in `token_hash` for lookups.
 *   - We store the first 8 chars as `token_prefix` for display (e.g. "phb_a1b2c3d4...").
 *   - Each token has its own `signing_secret` for HMAC request signing.
 *   - IP whitelist is a JSON-encoded array; NULL means any IP is allowed.
 */
class Api_tokens_model extends MY_Model
{
    public $_table_name  = 'tbl_api_tokens';
    public $_primary_key = 'id';

    // -----------------------------------------------------------------------
    // GENERATION
    // -----------------------------------------------------------------------

    /**
     * Generate a new raw token (never stored) and a signing secret.
     *
     * Returns:
     *   raw_token      — show to user ONCE, never again
     *   token_hash     — SHA-256 of raw_token, stored in DB
     *   token_prefix   — safe display prefix (e.g. "phb_a1b2c3d4")
     *   signing_secret — HMAC secret shared with the external project
     */
    public function generate(): array
    {
        $raw_token     = 'phb_' . bin2hex(random_bytes(30));   // 64-char total
        $signing_secret = bin2hex(random_bytes(32));            // 64-char hex

        return [
            'raw_token'      => $raw_token,
            'token_hash'     => hash('sha256', $raw_token),
            'token_prefix'   => substr($raw_token, 0, 12),
            'signing_secret' => $signing_secret,
        ];
    }

    // -----------------------------------------------------------------------
    // CRUD
    // -----------------------------------------------------------------------

    /**
     * Create a new token for a project.
     *
     * @param  int    $project_id
     * @param  array  $opts  Optional: token_name, ip_whitelist (array), expires_at (Y-m-d H:i:s)
     * @return array  ['id' => ..., 'raw_token' => ..., 'signing_secret' => ...]
     */
    public function create($project_id, $opts = [])
    {
        $gen = $this->generate();

        $ip_whitelist = null;
        if (!empty($opts['ip_whitelist'])) {
            $ips = array_filter(array_map('trim', (array) $opts['ip_whitelist']));
            $ip_whitelist = !empty($ips) ? json_encode(array_values($ips)) : null;
        }

        $row = [
            'project_id'     => $project_id,
            'token_name'     => $opts['token_name']  ?? 'Default Token',
            'token_prefix'   => $gen['token_prefix'],
            'token_hash'     => $gen['token_hash'],
            'signing_secret' => $gen['signing_secret'],
            'ip_whitelist'   => $ip_whitelist,
            'status'         => 'active',
            'expires_at'     => $opts['expires_at']  ?? null,
            'created_at'     => date('Y-m-d H:i:s'),
        ];

        $this->db->insert('tbl_api_tokens', $row);
        $id = $this->db->insert_id();

        return [
            'id'             => $id,
            'raw_token'      => $gen['raw_token'],
            'signing_secret' => $gen['signing_secret'],
            'token_prefix'   => $gen['token_prefix'],
        ];
    }

    /**
     * Authenticate a bearer token.
     *
     * Hashes the raw bearer value, looks up the DB, checks status, expiry,
     * and IP whitelist.  On success, updates last_used_at and returns the
     * full token row (with project data joined).
     *
     * @param  string $raw_token  Raw bearer value from Authorization header.
     * @param  string $client_ip  Caller's IP address.
     * @return mixed  Token row (with project info) on success, FALSE on any failure.
     */
    public function authenticate($raw_token, $client_ip)
    {
        if (empty($raw_token)) {
            return false;
        }

        $hash = hash('sha256', $raw_token);

        $token = $this->db
            ->select('t.*, p.project_name, p.webhook_url, p.callback_url, p.status as project_status')
            ->from('tbl_api_tokens t')
            ->join('tbl_api_clients p', 'p.id = t.project_id', 'inner')
            ->where('t.token_hash', $hash)
            ->where('t.status', 'active')
            ->where('p.status', 'active')
            ->get()
            ->row();

        if (empty($token)) {
            return false;
        }

        // Check expiry
        if (!empty($token->expires_at) && strtotime($token->expires_at) < time()) {
            // Auto-mark expired so future lookups skip immediately
            $this->db->where('id', $token->id)->update('tbl_api_tokens', ['status' => 'revoked']);
            return false;
        }

        // Check IP whitelist
        if (!empty($token->ip_whitelist)) {
            $allowed = json_decode($token->ip_whitelist, true) ?: [];
            if (!empty($allowed) && !in_array($client_ip, $allowed, true)) {
                return false;
            }
        }

        // Update last-used metadata (non-blocking; ignore failure)
        $this->db->where('id', $token->id)->update('tbl_api_tokens', [
            'last_used_at' => date('Y-m-d H:i:s'),
            'last_used_ip' => $client_ip,
        ]);

        return $token;
    }

    /**
     * Verify HMAC-SHA256 signature of an incoming request.
     *
     * External projects sign their requests:
     *   signature = HMAC-SHA256(timestamp + "." + raw_body, signing_secret)
     *
     * @param  string $signing_secret  From the token row.
     * @param  string $raw_body        Raw request body (file_get_contents('php://input')).
     * @param  string $signature       Value of X-Hub-Signature header.
     * @param  string $timestamp       Value of X-Hub-Timestamp header (unix epoch).
     * @param  int    $tolerance_secs  Reject requests older than this (replay protection).
     * @return bool
     */
    public function verify_hmac(
        string $signing_secret,
        string $raw_body,
        string $signature,
        string $timestamp,
        int    $tolerance_secs = 300
    ): bool {
        // Reject stale timestamps (replay attack protection)
        if (abs(time() - (int) $timestamp) > $tolerance_secs) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp . '.' . $raw_body, $signing_secret);
        return hash_equals($expected, $signature);
    }

    // -----------------------------------------------------------------------
    // STATUS / LIFECYCLE
    // -----------------------------------------------------------------------

    /** Get all tokens for a project */
    public function get_by_project(int $project_id): array
    {
        return $this->db
            ->where('project_id', $project_id)
            ->order_by('id', 'DESC')
            ->get('tbl_api_tokens')
            ->result();
    }

    /** Get a single token row */
    public function get($id = NULL, $single = FALSE)
    {
        return $this->db->get_where('tbl_api_tokens', ['id' => $id])->row();
    }

    /** Set status: active | disabled | revoked */
    public function set_status(int $id, string $status): bool
    {
        $valid = ['active', 'disabled', 'revoked'];
        if (!in_array($status, $valid, true)) {
            return false;
        }
        $this->db->where('id', $id)->update('tbl_api_tokens', ['status' => $status]);
        return $this->db->affected_rows() > 0;
    }

    /** Update IP whitelist for a token (pass empty array to clear) */
    public function update_ip_whitelist(int $id, array $ips): void
    {
        $ips = array_filter(array_map('trim', $ips));
        $whitelist = !empty($ips) ? json_encode(array_values($ips)) : null;
        $this->db->where('id', $id)->update('tbl_api_tokens', ['ip_whitelist' => $whitelist]);
    }

    /** Update token metadata (name, ip_whitelist, expires_at) */
    public function update_token(int $id, array $data): void
    {
        $allowed = ['token_name', 'ip_whitelist', 'expires_at'];
        $update  = array_intersect_key($data, array_flip($allowed));
        if (!empty($update)) {
            $this->db->where('id', $id)->update('tbl_api_tokens', $update);
        }
    }

    /** Hard-delete a token */
    public function delete($id)
    {
        $this->db->where('id', $id)->delete('tbl_api_tokens');
    }

    /** Count active tokens per project */
    public function count_active(int $project_id): int
    {
        return (int) $this->db
            ->where('project_id', $project_id)
            ->where('status', 'active')
            ->count_all_results('tbl_api_tokens');
    }

    // -----------------------------------------------------------------------
    // HELPERS
    // -----------------------------------------------------------------------

    /** Parse IP whitelist JSON into a readable string */
    public function format_whitelist(?string $json): string
    {
        if (empty($json)) {
            return 'Any IP';
        }
        $ips = json_decode($json, true) ?: [];
        return !empty($ips) ? implode(', ', $ips) : 'Any IP';
    }
}
