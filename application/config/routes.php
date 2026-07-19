<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

// PipraPay routes
$route['payment/piprapay/pay/(:num)']      = 'payment/piprapay/pay/$1';
$route['payment/piprapay/purchase']        = 'payment/piprapay/purchase';
$route['payment/piprapay/callback']        = 'payment/piprapay/callback';
$route['payment/piprapay/success']         = 'payment/piprapay/success';
$route['payment/piprapay/webhook']         = 'payment/piprapay/webhook';
$route['payment/piprapay/refund/(:any)']   = 'payment/piprapay/refund/$1';

// New unified PipraPay gateway routes (decoupled, module-agnostic)
$route['piprapay/initiate/(:num)']         = 'Piprapay_gateway/initiate_payment/$1';
$route['piprapay/callback_success']        = 'Piprapay_gateway/callback_success';
$route['piprapay/callback_cancel']         = 'Piprapay_gateway/callback_cancel';

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
    if (is_dir($modules_path . '/' . $module)) {
        $routes_path = $modules_path . $module . '/config/routes.php';
        if (file_exists($routes_path)) {
            require($routes_path);
        }
    }
}

/* ------------------------------------------ api routes ------------------------------------------ */
$route['api/staff-users'] = 'api/Main/staff_users';
$route['api/clients'] = 'api/Main/clients';
$route['api/jobs-posted-list'] = 'api/Main/jobs_postedList';

/* ------------------------------------------ Payment Hub API routes (v1) ------------------------------------------ */
$route['api/v1/payments/initiate']          = 'api/payments/initiate';
$route['api/v1/payments/callback/(:any)']   = 'api/payments/callback/$1';
$route['api/v1/payments/status/(:any)']     = 'api/payments/status/$1';
$route['api/v1/payments/gateways']          = 'api/payments/gateways';

$route['cronjob/process_email_queue']       = 'cronjob/process_email_queue_manual';
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

$route['admin/timesync/entries'] = 'admin/timesync/entries';
$route['admin/timesync/calendar'] = 'admin/timesync/calendar';
$route['admin/timesync/day_details/(:any)'] = 'admin/timesync/day_details/$1';

$route['admin/tasks/view/(:num)'] = 'admin/tasks/details/$1';

/*
------------------------------------------
TimeSync API routes
------------------------------------------
*/
$route['api/health'] = 'api/auth/health';
$route['api/auth/login'] = 'api/auth/login';
$route['api/auth/refresh'] = 'api/auth/refresh';
$route['api/auth/logout'] = 'api/auth/logout';
$route['api/auth/me'] = 'api/auth/me';
$route['api/auth/register'] = 'api/auth/register';
$route['api/tasks'] = 'api/tasks/index';
$route['api/tasks/(:num)'] = 'api/tasks/index/$1';
$route['api/tasks/comments/recent'] = 'api/tasks/recent_comments';
$route['api/tasks/(:num)/comments'] = 'api/tasks/comments/$1';
$route['api/projects'] = 'api/projects/index';
$route['api/users'] = 'api/users/index';
$route['api/users/promote-to-manager'] = 'api/users/promote_to_manager';
$route['api/time-entries'] = 'api/time_entries/index';
$route['api/time-entries/(:num)'] = 'api/time_entries/index/$1';
$route['api/attendance/check-in'] = 'api/attendance/check_in';
$route['api/attendance/check-out'] = 'api/attendance/check_out';
$route['api/attendance'] = 'api/attendance/index';
$route['api/screenshots'] = 'api/screenshots/index';
$route['api/screenshots/(:num)'] = 'api/screenshots/index/$1';
$route['api/screenshots/(:num)/image'] = 'api/screenshots/index/$1';
$route['api/screenshots/deleted_ids'] = 'api/screenshots/deleted_ids';
$route['api/app-usage'] = 'api/app_usage/index';
$route['api/app-usage/(:num)'] = 'api/app_usage/index/$1';
$route['api/tracker/config'] = 'api/tracker/config';
$route['api/reports/calendar'] = 'api/reports/calendar';
$route['api/reports/dashboard-analytics'] = 'api/reports/dashboard_analytics';
$route['api/reports/app-usage'] = 'api/reports/app_usage';
$route['api/reports/employee-summary'] = 'api/reports/employee_summary';
$route['api/reports/project-summary'] = 'api/reports/project_summary';
$route['api/reports/day-details'] = 'api/reports/day_details';
$route['api/updates/timesync/latest'] = 'api/updates/latest';
$route['api/updates/timesync/download'] = 'api/updates/download';
$route['api/updates/publish'] = 'api/updates/publish';
$route['api/dashboard/live-users'] = 'api/dashboard/live_users';
$route['api/dashboard/summary'] = 'api/dashboard/summary';
$route['api/dashboard/detailed-overview'] = 'api/dashboard/detailed_overview';

/*
------------------------------------------
Payment Hub API routes (v1)
------------------------------------------
*/
$route['api/v1/payments/initiate']          = 'api/payments/initiate';
$route['api/v1/payments/callback/(:any)']   = 'api/payments/callback/$1';
$route['api/v1/payments/status/(:any)']     = 'api/payments/status/$1';
$route['api/v1/payments/gateways']          = 'api/payments/gateways';

$route['api/teams']                              = 'api/teams/index';
$route['api/teams/(:num)/messages']              = 'api/teams/messages/$1';
$route['api/teams/(:num)']                       = 'api/teams/index/$1';
$route['api/teams/(:num)/members']           = 'api/teams/members/$1';
$route['api/teams/(:num)/members/(:num)']    = 'api/teams/members/$1/$2';
$route['api/teams/request_join']             = 'api/teams/request_join';
$route['api/teams/approve_member']           = 'api/teams/approve_member';
$route['api/teams/my-requests']              = 'api/teams/my_requests';
$route['api/teams/my-memberships']           = 'api/teams/my_memberships';
$route['api/teams/pending-requests/(:num)']  = 'api/teams/pending_requests/$1';
$route['api/teams/mentions/mark_read'] = 'api/teams/mark_read';
$route['api/teams/mentions'] = 'api/teams/mentions';
$route['api/teams/managed'] = 'api/teams/managed';

$route['cronjob/process_email_queue']       = 'cronjob/process_email_queue_manual';
$route['api/sync/heartbeat'] = 'api/sync/heartbeat';
