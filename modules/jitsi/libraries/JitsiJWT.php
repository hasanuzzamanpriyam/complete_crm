<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * JitsiJWT - RS256 JWT Token Generator for Jitsi Meet
 *
 * Generates JWT tokens using RSA private key (RS256 algorithm)
 * for authenticating users with a self-hosted Jitsi server.
 *
 * Uses PHP's built-in openssl_sign() - no external dependencies required.
 */
class JitsiJWT
{
    private $app_id;
    private $private_key;
    private $domain;

    public function __construct($config = [])
    {
        $this->app_id = isset($config['app_id']) ? $config['app_id'] : '';
        $this->private_key = isset($config['private_key']) ? $config['private_key'] : '';
        $this->domain = isset($config['domain']) ? $config['domain'] : '';
    }

    /**
     * Generate a JWT token for a Jitsi meeting participant
     *
     * @param string $room The Jitsi room name
     * @param string $email Participant email
     * @param string $name Participant display name
     * @param bool $is_moderator Whether the user is the meeting host
     * @param int $exp Expiration timestamp (default: 2 hours from now)
     * @return string JWT token
     * @throws Exception If key parsing or signing fails
     */
    public function generateToken($room, $email = '', $name = '', $is_moderator = false, $exp = null)
    {
        if (empty($this->private_key)) {
            throw new Exception('Jitsi private key is not configured.');
        }

        if (empty($this->app_id)) {
            throw new Exception('Jitsi App ID is not configured.');
        }

        if (empty($room)) {
            throw new Exception('Meeting room name is required.');
        }

        $now = time();
        $exp = $exp ?: ($now + 7200);

        // Detect if JaaS (8x8.vc) is being used
        $is_jaas = (strpos($this->domain, '8x8.vc') !== false);

        if ($is_jaas) {
            // JaaS Key ID (kid) and App ID parsing
            // If the configured app_id has a slash, e.g. "vpaas-magic-cookie-xxx/key-id"
            if (strpos($this->app_id, '/') !== false) {
                $parts = explode('/', $this->app_id);
                $app_id = $parts[0];
                $kid = $this->app_id;
            } else {
                $app_id = $this->app_id;
                $kid = $this->app_id;
            }

            $header = [
                'alg' => 'RS256',
                'typ' => 'JWT',
                'kid' => $kid,
            ];

            $payload = [
                'iss' => 'chat',
                'aud' => 'jitsi',
                'sub' => $app_id,
                'room' => $room,
                'exp' => $exp,
                'nbf' => $now - 10,
                'context' => [
                    'user' => [
                        'email' => $email,
                        'name' => $name,
                        'id' => $email ?: $name,
                        'moderator' => $is_moderator ? "true" : "false",
                    ],
                    'features' => [
                        'recording' => 'true',
                        'livestreaming' => 'false',
                        'transcription' => 'false',
                        'outbound-call' => 'false',
                    ],
                ],
            ];
        } else {
            $header = [
                'alg' => 'RS256',
                'typ' => 'JWT',
            ];

            $payload = [
                'context' => [
                    'user' => [
                        'email' => $email,
                        'name' => $name,
                        'id' => $email ?: $name,
                    ],
                ],
                'aud' => $this->app_id,
                'iss' => $this->app_id,
                'sub' => parse_url($this->domain, PHP_URL_HOST) ?: $this->domain,
                'room' => $room,
                'exp' => $exp,
                'nbf' => $now - 60,
            ];

            if ($is_moderator) {
                $payload['moderator'] = true;
            }
        }

        $header_encoded = $this->base64UrlEncode(json_encode($header));
        $payload_encoded = $this->base64UrlEncode(json_encode($payload));

        $signature_input = $header_encoded . '.' . $payload_encoded;

        $signature = '';
        $private_key_resource = openssl_pkey_get_private($this->private_key);

        if ($private_key_resource === false) {
            throw new Exception('Failed to parse Jitsi private key: ' . openssl_error_string());
        }

        $sign_result = openssl_sign($signature_input, $signature, $private_key_resource, OPENSSL_ALGO_SHA256);

        if ($sign_result === false) {
            throw new Exception('Failed to sign JWT token: ' . openssl_error_string());
        }

        if (PHP_VERSION_ID < 80000) {
            openssl_free_key($private_key_resource);
        }

        $signature_encoded = $this->base64UrlEncode($signature);

        return $signature_input . '.' . $signature_encoded;
    }

    /**
     * Build the full Jitsi meeting URL with JWT token
     *
     * @param string $room Meeting room name
     * @param string $email Participant email
     * @param string $name Participant display name
     * @param bool $is_moderator Whether the user is the host
     * @param int $exp Expiration timestamp
     * @return string Full Jitsi URL with JWT
     */
    public function buildMeetingUrl($room, $email = '', $name = '', $is_moderator = false, $exp = null)
    {
        $token = $this->generateToken($room, $email, $name, $is_moderator, $exp);
        $domain = rtrim($this->domain, '/');

        $is_jaas = (strpos($this->domain, '8x8.vc') !== false);
        if ($is_jaas) {
            if (strpos($this->app_id, '/') !== false) {
                $app_id = explode('/', $this->app_id)[0];
            } else {
                $app_id = $this->app_id;
            }
            return $domain . '/' . $app_id . '/' . $room . '?jwt=' . $token;
        }

        return $domain . '/' . $room . '?jwt=' . $token;
    }

    /**
     * Base64 URL encoding (RFC 4648)
     *
     * @param string $data
     * @return string
     */
    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
