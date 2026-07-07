<?php defined('BASEPATH') or exit('No direct script access allowed');

$route['admin/jitsi'] = 'jitsi/index';
$route['admin/jitsi/settings'] = 'jitsi/settings';
$route['admin/jitsi/save_meeting/(:any)'] = 'jitsi/save_meeting/$1';
$route['admin/jitsi/delete_meeting/(:any)'] = 'jitsi/delete_meeting/$1';
$route['admin/jitsi/change_status/(:any)/(:any)'] = 'jitsi/change_status/$1/$2';
$route['admin/jitsi/join/(:any)'] = 'jitsi/join/$1';
$route['admin/jitsi/meetingList'] = 'jitsi/meetingList';
$route['jitsi/meetings'] = 'jitsi/client_meetings';
$route['jitsi/joined/(:any)'] = 'jitsi/joined/$1';
$route['jitsi/join_meeting/(:any)'] = 'jitsi/client_join/$1';
$route['jitsi/share/(:any)'] = 'jitsi/share/$1';
