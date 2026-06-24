<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Updates extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    private function _getCurrentVersion(): string
    {
        $file = FCPATH . 'downloads/latest.json';
        if (file_exists($file)) {
            $json = json_decode(file_get_contents($file), true);
            return ltrim($json['version'] ?? '0.1.0', 'v');
        }
        return '0.1.0';
    }

    public function download()
    {
        $platform = $this->input->get('platform');
        $v = $this->_getCurrentVersion();

        $files = [
            'windows'        => "TimeSync_{$v}_x64-setup.exe",
            'windows-msi'    => "TimeSync_{$v}_x64_en-US.msi",
            'windows-update' => "TimeSync_{$v}_x64_en-US.msi.zip",
            'macos'          => "TimeSync_{$v}_x64.dmg",
            'macos-update'   => "TimeSync_{$v}_x64.tar.gz",
            'linux'          => "TimeSync_{$v}_amd64.AppImage",
            'linux-update'   => "TimeSync_{$v}_amd64.AppImage.tar.gz",
            'linux-deb'      => "TimeSync_{$v}_amd64.deb",
        ];

        $file = $files[$platform] ?? null;
        if (!$file) {
            show_404();
            return;
        }

        $path = FCPATH . 'downloads/' . $file;
        if (!file_exists($path)) {
            show_404();
            return;
        }

        $this->load->helper('download');
        force_download($file, file_get_contents($path));
    }

    public function latest()
    {
        $file = FCPATH . 'downloads/latest.json';
        $this->output->set_content_type('application/json');

        if (!file_exists($file)) {
            $this->output->set_status_header(200);
            $version = 'v0.1.0';
            echo json_encode([
                "version" => $version,
                "notes" => "No updates available.",
                "pub_date" => date('c'),
                "platforms" => $this->_buildPlatforms(ltrim($version, 'v')),
            ]);
            return;
        }

        $this->output
            ->set_status_header(200)
            ->set_output(file_get_contents($file));
    }

    public function publish()
    {
        $auth = $this->input->get_request_header('Authorization', true);
        $secret = config_item('timesync_update_secret');
        if (!$secret || $auth !== 'Bearer ' . $secret) {
            $this->output->set_status_header(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        $version = $this->input->post('version');
        if (empty($version)) {
            $this->output->set_status_header(400);
            echo json_encode(['success' => false, 'error' => 'Missing version']);
            return;
        }

        $notes = $this->input->post('notes') ?: 'New release ' . $version;
        $dest = FCPATH . 'downloads/';
        $v = ltrim($version, 'v');

        $mappings = [
            'windows_sig'    => "TimeSync_{$v}_x64_en-US.msi.zip.sig",
            'windows_update' => "TimeSync_{$v}_x64_en-US.msi.zip",
            'macos_sig'      => "TimeSync_{$v}_x64.tar.gz.sig",
            'macos_update'   => "TimeSync_{$v}_x64.tar.gz",
            'linux_sig'      => "TimeSync_{$v}_amd64.AppImage.tar.gz.sig",
            'linux_update'   => "TimeSync_{$v}_amd64.AppImage.tar.gz",
        ];

        foreach ($mappings as $field => $fileName) {
            if (!empty($_FILES[$field]['tmp_name'])) {
                move_uploaded_file($_FILES[$field]['tmp_name'], $dest . $fileName);
            }
        }

        $platforms = [];
        $sigMap = [
            'windows_sig' => 'windows_sig_file',
            'macos_sig'   => 'macos_sig_file',
            'linux_sig'   => 'linux_sig_file',
        ];

        $archiveMap = [
            'windows_update' => 'windows-x86_64',
            'macos_update'   => 'darwin-x86_64',
            'linux_update'   => 'linux-x86_64',
        ];

        foreach ($archiveMap as $field => $platformName) {
            $sigField = str_replace('_update', '_sig', $field);
            $sigFile = $mappings[$sigField] ?? null;
            $archiveFile = $mappings[$field] ?? null;

            if (file_exists($dest . $archiveFile)) {
                $platforms[$platformName] = [
                    'signature' => ($sigFile && file_exists($dest . $sigFile)) ? file_get_contents($dest . $sigFile) : '',
                    'url' => base_url("api/updates/timesync/download?platform=" . str_replace('_update', '-update', $field)),
                ];
            }
        }

        if (empty($platforms)) {
            echo json_encode(['success' => false, 'error' => 'No update archives provided']);
            return;
        }

        // Also add darwin-aarch64 (same as darwin-x86_64 for now)
        if (isset($platforms['darwin-x86_64'])) {
            $platforms['darwin-aarch64'] = $platforms['darwin-x86_64'];
        }

        $json = [
            'version'   => $version,
            'notes'     => $notes,
            'pub_date'  => date('c'),
            'platforms' => $platforms,
        ];

        file_put_contents($dest . 'latest.json', json_encode($json, JSON_PRETTY_PRINT));

        $this->output->set_content_type('application/json');
        echo json_encode(['success' => true, 'version' => $version]);
    }

    private function _buildPlatforms(string $v): array
    {
        return [
            "windows-x86_64" => [
                "signature" => "",
                "url" => base_url("api/updates/timesync/download?platform=windows-update")
            ],
            "darwin-x86_64" => [
                "signature" => "",
                "url" => base_url("api/updates/timesync/download?platform=macos-update")
            ],
            "darwin-aarch64" => [
                "signature" => "",
                "url" => base_url("api/updates/timesync/download?platform=macos-update")
            ],
            "linux-x86_64" => [
                "signature" => "",
                "url" => base_url("api/updates/timesync/download?platform=linux-update")
            ],
        ];
    }
}
