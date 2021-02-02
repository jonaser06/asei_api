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

$route['upload']['POST']= 'Upload';
$route['user/(:num)/files']['GET'] = 'Upload/get/$1';
$route['files']['GET']= 'Upload/getAll';
$route['user/(:num)/file/(:num)']['DELETE']= 'Upload/delete/$1/$2';
$route['file/(:num)/update']['POST']= 'Upload/edit/$1';

#notes

$route['notes/new']['POST'] = 'api/Notes/insert';
$route['notes/(:num)']['GET'] = 'api/Notes/getById/$1';
$route['notes/(:any)']['GET'] = 'api/Notes/get/$1';
$route['notes/(:num)/update']['POST']= 'api/Notes/update/$1';
// $route['notes/(:num)/delete']['DELETE'] = 'api/Notes/delete/$1';
$route['notes/(:num)/delete']['GET'] = 'api/Notes/delete/$1';

#search Notes
$route['notes/search/(:any)']['GET'] = 'api/Notes/search/$1';

#sections
$route['sections']['GET'] = 'api/Notes/get_sections';

#learn
$route['learn/new']['POST'] = 'api/Contenidos/insert';
$route['learn/(:any)']['GET'] = 'api/Contenidos/get/$1';
$route['learn/(:any)/(:num)']['GET'] = 'api/Contenidos/getById/$1/$2';
$route['learn/(:any)/(:num)']['DELETE'] = 'api/Contenidos/delete/$1/$2';
$route['learn/(:any)/(:num)']['POST'] = 'api/Contenidos/update/$1/$2';




