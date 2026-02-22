<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Api_routes extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Collect all API routes from main config and modules
     * @return array [ ['uri' => 'api/...', 'destination' => 'api/method'], ... ]
     */
    private function _get_api_routes()
    {
        $routes = array();

        // Parse main application routes
        $main_routes_file = APPPATH . 'config/routes.php';
        if (file_exists($main_routes_file)) {
            $content = file_get_contents($main_routes_file);
            if (preg_match_all("/\\\$route\s*\[\s*['\"]([^'\"]+)['\"]\s*\]\s*=\s*['\"]([^'\"]+)['\"]\s*;/", $content, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $m) {
                    $uri = $m[1];
                    $dest = $m[2];
                    if (strpos($uri, 'api') === 0 || strpos($dest, 'api/') === 0) {
                        $routes[] = array(
                            'uri' => $uri,
                            'destination' => $dest,
                            'source' => 'application',
                        );
                    }
                }
            }
        }

        // Scan modules for route files
        $modules_path = defined('MODULES_PATH') ? MODULES_PATH : APPPATH . '../modules/';
        if (is_dir($modules_path)) {
            $modules = scandir($modules_path);
            foreach ($modules as $module) {
                if ($module === '.' || $module === '..' || !is_dir($modules_path . $module)) {
                    continue;
                }
                $route_file = $modules_path . $module . '/config/routes.php';
                if (file_exists($route_file)) {
                    $content = file_get_contents($route_file);
                    if (preg_match_all("/\\\$route\s*\[\s*['\"]([^'\"]+)['\"]\s*\]\s*=\s*['\"]([^'\"]+)['\"]\s*;/", $content, $matches, PREG_SET_ORDER)) {
                        foreach ($matches as $m) {
                            $uri = $m[1];
                            $dest = $m[2];
                            if (strpos($uri, 'api') === 0 || strpos($dest, 'api/') === 0) {
                                $routes[] = array(
                                    'uri' => $uri,
                                    'destination' => $dest,
                                    'source' => 'module:' . $module,
                                );
                            }
                        }
                    }
                }
            }
        }

        return $routes;
    }

    /**
     * Display API routes list in admin panel
     */
    public function index()
    {
        $data['title'] = 'API Routes';
        $data['api_routes'] = $this->_get_api_routes();
        $data['base_url'] = base_url();
        $data['subview'] = $this->load->view('admin/api_routes/index', $data, true);
        $this->load->view('admin/_layout_main', $data);
    }
}
