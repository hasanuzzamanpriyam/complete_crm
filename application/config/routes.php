<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
$route['default_controller'] = 'login';
$route['404'] = "login/not_found";
$route['career'] = "frontend";
$route['admin/mark_attendance'] = "admin/dashboard/mark_attendance";
$route['admin/api-routes'] = "admin/api_routes/index";
$route['admin/api-doc'] = "admin/api_doc/index";
$route['knowledgebase'] = "frontend/knowledgebase";
$route['available_modules'] = "admin/my_module/available_modules";
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;


// add route from modules folder
$modules_path = MODULES_PATH;
$modules = scandir($modules_path);
foreach ($modules as $module) {
    if ($module === '.' || $module === '..' || $module == 'api' || $module == 'frontcms')
        continue;
    if (is_dir($modules_path) . '/' . $module) {
        $routes_path = $modules_path . $module . '/config/routes.php';
        if (file_exists($routes_path)) {
            require($routes_path);
        } else {
            continue;
        }
    }
}




/*
------------------------------------------
api routes
------------------------------------------
*/

$route['api/staff-users'] = 'api/Main/staff_users';
$route['api/clients'] = 'api/Main/clients';
$route['api/jobs-posted-list'] = 'api/Main/jobs_postedList';

/*
------------------------------------------
Payment Hub API routes (v1)
------------------------------------------
*/
$route['api/v1/payments/initiate']          = 'api/payments/initiate';
$route['api/v1/payments/callback/(:any)']   = 'api/payments/callback/$1';
$route['api/v1/payments/status/(:any)']     = 'api/payments/status/$1';
$route['api/v1/payments/gateways']          = 'api/payments/gateways';
