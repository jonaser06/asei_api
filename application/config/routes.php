<?php
defined('BASEPATH') OR exit('No direct script access allowed');


$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
#API LOGIN USER
$route['login']['POST'] = 'api/LoginUser/login';
$route['register'] ['POST'] = 'api/LoginUser/register';
$route['newchart'] ['POST'] = 'api/Statistics/newchart';

#PERFILES

$route['api/perfiles'] = 'api/Perfiles';





