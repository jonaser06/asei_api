<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
#API LOGIN USER
$route['login']['POST'] = 'api/LoginUser/login';
$route['register'] ['POST'] = 'api/LoginUser/register';

$route['test']  = 'api/Statistics/test';
#Statistics

##chart
$route['newchart']  = 'api/Statistics/newchart';
$route['editchart'] = 'api/Statistics/editchart';
$route['deletechart'] = 'api/Statistics/deletechart';

##bulletin
$route['newbulletin']  = 'api/Statistics/newbulletin';
$route['editbulletin']  = 'api/Statistics/editbulletin';
$route['deletebulletin']  = 'api/Statistics/deletebulletin';

#PERFILES

$route['api/perfiles'] = 'api/Perfiles';

#UPLOAD

$route['upload'] = 'Upload';



