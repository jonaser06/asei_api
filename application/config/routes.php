<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

#API LOGIN USER
// $route[''] = '';
$route['login']['POST'] = 'api/LoginUser/login';
$route['register'] ['POST'] = 'api/LoginUser/register';

#Users
$route['user/(:num)/update']['POST']= 'api/User/updateOne/$1';
$route['users/(:any)'] ['GET'] = 'api/User/getRol/$1';
$route['users'] ['GET'] = 'api/User/getAll';
$route['user/(:num)'] ['GET'] = function ($id) {
    return 'api/User/get/'.$id;
};
$route['user/(:num)/delete']['GET'] =  'api/User/delete/$1';


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

##Notification
$route['newNotification'] = 'api/Notification/newNotification';
$route['setNotification'] = 'api/Notification/setNotification';
$route['getNotification'] = 'api/Notification/getNotification';

#PERFILES

$route['api/perfiles'] = 'api/Perfiles';

$route['perfiles']['GET'] = 'api/Privileges';
$route['privileges/(:num)']['GET'] = 'api/Privileges/get/$1';
$route['privileges/(:num)']['POST'] = 'api/Privileges/edit/$1';



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

#cap
$route['learn/(:any)/(:num)/capacitador']['POST'] = 'api/Contenidos/addCap/$1/$2';
$route['capacitadores/(:num)/delete']['GET'] = 'api/Contenidos/removeCap/$1';
#cap
$route['learn/(:any)/(:num)/sesion']['POST'] = 'api/Contenidos/addSession/$1/$2';
$route['sesiones/(:num)/delete']['GET'] = 'api/Contenidos/removeSession/$1';
#learn
$route['learn/new']['POST'] = 'api/Contenidos/insert';
$route['learn/(:any)']['GET'] = 'api/Contenidos/get/$1';
$route['learn/(:any)/(:num)']['GET'] = 'api/Contenidos/getById/$1/$2';
$route['learn/(:any)/(:num)/delete']['GET'] = 'api/Contenidos/delete/$1/$2';
$route['learn/(:any)/(:num)']['POST'] = 'api/Contenidos/update/$1/$2';


$route['certificado/(:num)/(:num)']['GET'] = 'api/Certificados/getById/$1/$2';
$route['certificados/(:num)']['GET'] = 'api/Certificados/get_certificates/$1';
$route['new-certificado/(:num)/(:num)']['POST'] = 'api/Certificados/setCertificate/$1/$2';


$route['testing']['POST'] = 'api/Contenidos/test';

$route['(:any)/files']['POST'] = 'api/Documentos/insert/$1';
$route['tipo-documento/(:num)/files']['GET'] = 'api/Documentos/get_all/$1';
$route['tipo-documento/(:num)/files/(:num)/delete']['GET'] = 'api/Documentos/delete_document/$1/$2';
$route['tipo-documento/(:num)/files/(:num)']['GET'] = 'api/Documentos/get_doc/$1/$2';
$route['tipo-documento/(:num)/files/(:num)/update']['POST'] = 'api/Documentos/update_doc/$1/$2';

$route['tipo-documento']['POST'] = 'api/Documentos/insert_categorie';
$route['tipo-documentos']['GET'] = 'api/Documentos/get_categories';
$route['tipo-documentos/(:num)']['GET'] = 'api/Documentos/get_categorie/$1';
$route['tipo-documentos/(:num)/update']['POST'] = 'api/Documentos/update_categorie/$1';
$route['tipo-documentos/(:num)/delete']['GET'] = 'api/Documentos/delete/$1';


#es
$route['promedio']['GET'] = 'api/Calificaciones/getAllProm'; 
$route['suma']['GET'] = 'api/Calificaciones/getAllSuma'; 
$route['bpromedio/(:num)']['GET'] = 'api/Calificaciones/getByIdProm/$1'; 
$route['bsuma/(:num)']['GET'] = 'api/Calificaciones/getByIdSuma/$1'; 
$route['calificar/(:num)/(:num)']['POST'] = 'api/Calificaciones/setCalificacion/$1/$2'; 
$route['comproved/(:num)/(:num)']['GET'] = 'api/Calificaciones/comproved/$1/$2'; 

#calendario 
$route['calendario']['GET'] = 'api/Notes/calendar';

#certificados












