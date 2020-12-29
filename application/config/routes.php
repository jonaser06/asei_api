<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$routes->options('(:any)', 'LoginUser::options'); //one options method for all routes.

$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
#API LOGIN USER
$route['login']['POST'] = 'api/LoginUser/login';
$route['register'] ['POST'] = 'api/LoginUser/register';

#Statistics

##chart
$route['newchart']['POST']  = 'api/Statistics/newchart';
$route['editchart']['POST'] = 'api/Statistics/editchart';
$route['deletechart']['POST'] = 'api/Statistics/deletechart';

##bulletin
$route['newbulletin']['POST']  = 'api/Statistics/newbulletin';
$route['editbulletin']['POST']  = 'api/Statistics/editbulletin';
$route['deletebulletin']['POST']  = 'api/Statistics/deletebulletin';

#PERFILES

$route['api/perfiles'] = 'api/Perfiles';

#UPLOAD

$route['upload'] = 'Upload';



