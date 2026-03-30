<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PH_Api_Controller
 * Base controller for all Payment Hub API endpoints.
 * Provides standardized JSON response methods and shared authentication logic.
 * @property CI_Output $output
 * @property CI_Input $input
 * @property CI_Session $session
 */
class PH_Api_Controller extends MY_Controller
{
    /**
     * Standard success response.
     */
    protected function _send_success($data = [], $message = "Success", $code = 200)
    {
        $this->output
            ->set_status_header($code)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'    => 'success',
                'code'      => $code,
                'message'   => $message,
                'data'      => $data,
                'timestamp' => time()
            ]));
    }

    /**
     * Standard error response.
     */
    protected function _send_error($message = "Error", $code = 400, $errors = [])
    {
        $response = [
            'status'    => 'error',
            'code'      => $code,
            'message'   => $message,
            'timestamp' => time()
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        $this->output
            ->set_status_header($code)
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    /**
     * Handle internal exceptions.
     */
    protected function _handle_exception(Exception $e)
    {
        log_message('error', 'API Exception: ' . $e->getMessage());
        return $this->_send_error($e->getMessage(), 500);
    }
}
