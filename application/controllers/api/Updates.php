<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Updates extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function latest()
    {
        $file = FCPATH . 'downloads/latest.json';
        $this->output->set_content_type('application/json');

        if (!file_exists($file)) {
            $this->output->set_status_header(200);
            echo json_encode([
                "version" => "v0.1.0",
                "notes" => "No updates available.",
                "pub_date" => date('c'),
                "platforms" => [
                    "windows-x86_64" => [
                        "signature" => "",
                        "url" => ""
                    ]
                ]
            ]);
            return;
        }

        $this->output
            ->set_status_header(200)
            ->set_output(file_get_contents($file));
    }
}
