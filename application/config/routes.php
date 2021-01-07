<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

#API LOGIN USER
$route[''] = '';
$route['login']['POST'] = 'api/LoginUser/login';
$route['register'] ['POST'] = 'api/LoginUser/register';

#Users
$route['users'] ['GET'] = 'api/User/getAll';
$route['user/(:num)'] ['GET'] = function ($id) {
    return 'api/User/get/'.$id;
};



$route['test']  = 'api/Statistics/test';
#Statistics

##chart
$route['newchart']      = 'api/Statistics/newchart';
$route['editchart']     = 'api/Statistics/editchart';
$route['deletechart']   = 'api/Statistics/deletechart';
$route['getchart']['GET'] = 'api/Statistics/getchart';

##bulletin
$route['newbulletin']  = 'api/Statistics/newbulletin';
$route['editbulletin']  = 'api/Statistics/editbulletin';
$route['deletebulletin']  = 'api/Statistics/deletebulletin';
$route['getbulletin']['GET'] = 'api/Statistics/getbulletin';
##indicadores
$route['newIndicador']['POST']  = 'api/Statistics/newIndicador';
$route['editIndicador']  = 'api/Statistics/editIndicador';
$route['deleteIndicador'] = 'api/Statistics/deleteIndicador';
$route['getIndicador']['GET'] = 'api/Statistics/getIndicador';

#PERFILES

$route['api/perfiles'] = 'api/Perfiles';

$route['perfiles']['GET'] = 'api/Privileges';

#UPLOAD

$route['upload'] = 'Upload';



