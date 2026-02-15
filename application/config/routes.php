<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
$route['default_controller'] = 'login';
$route['404'] = "login/not_found";
$route['career'] = "frontend";
$route['admin/mark_attendance'] = "admin/dashboard/mark_attendance";
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
        }
        else {
            continue;
        }
    }
}




/*
------------------------------------------
api routes
------------------------------------------
*/

$route['api/staff-users'] = 'api/staff_users';
$route['api/clients'] = 'api/clients';
$route['api/jobs-posted-list'] = 'api/jobs_postedList';




