<?php
defined('BASEPATH') OR exit('No direct script access allowed');


$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
#API LOGIN USER
$route['login'] = 'api/LoginUser/login';
$route['register'] = 'api/LoginUser/register';

#PERFILES

$route['perfiles/all'] = 'api/Perfiles';





